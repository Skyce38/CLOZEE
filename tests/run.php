<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit(1);
$testName=getenv('CLOZEE_TEST_DB');
if(!$testName||!preg_match('/^[a-zA-Z0-9_]*test[a-zA-Z0-9_]*$/D',$testName)) { fwrite(STDERR,"Set CLOZEE_TEST_DB to a dedicated database with 'test' in its name. No data changed.\n");exit(1); }
putenv('CLOZEE_DB_NAME='.$testName);
require __DIR__.'/../app/bootstrap.php';
$otherTables=query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$otherTables=array_values(array_diff($otherTables,array_map('table_name',CLOZEE_TABLES)));
function other_table_counts(array $names): array {
    $counts=[];
    foreach($names as $name)$counts[$name]=(int)db()->query('SELECT COUNT(*) FROM `'.str_replace('`','``',$name).'`')->fetchColumn();
    return $counts;
}
$otherCounts=other_table_counts($otherTables);
if(!application_tables()) db()->exec(application_sql_file('schema.sql'));
if(count(application_tables())!==count(CLOZEE_TABLES))throw new RuntimeException('Incomplete application schema; refusing tests.');
$results=[];
function check(bool $ok,string $message='Assertion failed'): void { if(!$ok)throw new RuntimeException($message); }
function rejects(callable $fn): void { try {$fn();}catch(ValidationException|PDOException $e){return;}throw new RuntimeException('Expected rejection'); }
function test(string $name,callable $fn): void { global $results;try{$fn();$results[]=['test'=>$name,'status'=>'PASS'];echo "PASS $name\n";}catch(Throwable $e){$results[]=['test'=>$name,'status'=>'FAIL','detail'=>$e->getMessage()];echo "FAIL $name: ".$e->getMessage()."\n";} }
function fixture_user(string $tag): int { $p=bin2hex(random_bytes(16));$u=register_user(['full_name'=>'Test '.$tag,'email'=>$tag.'.'.bin2hex(random_bytes(6)).'@example.test','password'=>$p,'password_confirmation'=>$p]);return (int)$u['user_id']; }
function product_input(string $name='Test linen',int $stock=5): array { return ['product_name'=>$name,'description'=>'Fictional testing garment','category'=>'Shirts','condition_type'=>'secondhand','image_path'=>'linen.jpg','is_active'=>'1','variants'=>[['size_label'=>'M','price'=>'290.00','stock_quantity'=>$stock,'is_active'=>'1']]]; }
function product_edit_input(int $id): array { $p=query('SELECT * FROM {{products}} WHERE product_id=?',[$id])->fetch();$p['variants']=query('SELECT * FROM {{product_variants}} WHERE product_id=? ORDER BY variant_id',[$id])->fetchAll();return $p; }
function parallel_orders(array $requests): array {
    $running=[];
    foreach($requests as [$buyer,$variant,$key]) {
        $process=proc_open([PHP_BINARY,__DIR__.'/worker.php',(string)$buyer,(string)$variant,$key],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
        if(!is_resource($process))throw new RuntimeException('Could not start concurrency worker');
        fclose($pipes[0]);$running[]=[$process,$pipes];
    }
    $out=[];
    foreach($running as [$process,$pipes]) { $raw=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$code=proc_close($process);check($code===0,'Worker failed: '.$err);$out[]=json_decode($raw,true,512,JSON_THROW_ON_ERROR); }
    return $out;
}

test('money uses exact integer cents',function(){check(cents('0.01')===1&&cents('290.90')===29090&&decimal(3010)==='30.10');});
test('explicit table slots resolve and bound text is unchanged',function(){check(resolve_sql('SELECT * FROM {{users}}')==='SELECT * FROM `'.table_name('users').'`');check(query('SELECT ?',['FROM users {{users}}'])->fetchColumn()==='FROM users {{users}}');});
test('unknown table slots are rejected',function(){try{resolve_sql('SELECT * FROM {{unowned_table}}');}catch(LogicException $e){return;}throw new RuntimeException('Unknown table allowed');});
test('reject negative and excessive decimal precision',function(){foreach(['-1','1.001','1e3','NaN'] as $v)rejects(fn()=>cents($v));});
test('pickup and delivery state machines differ',function(){check(next_statuses('preparing','pickup')===['ready_for_pickup','cancelled']);check(next_statuses('preparing','delivery')===['out_for_delivery','cancelled']);check(next_statuses('completed','pickup')===[]);});
$seller=fixture_user('seller');$otherSeller=fixture_user('other');$buyer=fixture_user('buyer');$buyerTwo=fixture_user('buyer2');
$shop=save_shop($seller,['shop_name'=>'Test Shop','description'=>'Demo','pickup_location'=>'Fictional BUU meetup','is_active'=>1]);
$shop2=save_shop($otherSeller,['shop_name'=>'Other Shop','description'=>'Demo','pickup_location'=>'Fictional Bangsaen meetup','is_active'=>1]);
$pid=save_product($seller,null,product_input());$vid=(int)query('SELECT variant_id FROM {{product_variants}} WHERE product_id=?',[$pid])->fetchColumn();
$pid2=save_product($otherSeller,null,product_input('Other shirt'));$vid2=(int)query('SELECT variant_id FROM {{product_variants}} WHERE product_id=?',[$pid2])->fetchColumn();

test('passwords stored as hashes',function()use($buyer){$hash=query('SELECT password_hash FROM {{users}} WHERE user_id=?',[$buyer])->fetchColumn();check(str_starts_with($hash,'$2y$')||str_starts_with($hash,'$argon'));});
test('one shop per owner updates existing shop',function()use($seller,$shop){check(save_shop($seller,['shop_name'=>'Updated Shop','description'=>'Updated','pickup_location'=>'Fictional BUU meetup','is_active'=>1])===$shop);});
test('product CREATE and READ',function()use($pid){check((int)query('SELECT COUNT(*) FROM {{products}} WHERE product_id=?',[$pid])->fetchColumn()===1);});
test('legacy product metadata remains optional',function()use($pid){$r=query('SELECT brand,chest_cm,length_cm,condition_notes FROM {{products}} WHERE product_id=?',[$pid])->fetch();check(count(array_filter($r,fn($value)=>$value!==null))===0);});
test('database CHECK rejects zero garment measurement',function()use($pid){rejects(fn()=>query('UPDATE {{products}} SET chest_cm=0 WHERE product_id=?',[$pid]));});
test('image foreign key rejects orphan record',function(){rejects(fn()=>query('INSERT INTO {{product_images}}(product_id,storage_name,mime_type,byte_size,width,height) VALUES(?,?,?,?,?,?)',[PHP_INT_MAX,bin2hex(random_bytes(16)).'.jpg','image/jpeg',100,10,10]));});
test('invalid negative product price rejected',function()use($seller){$d=product_input();$d['variants'][0]['price']='-1';rejects(fn()=>save_product($seller,null,$d));});
test('duplicate sizes rejected',function()use($seller){$d=product_input();$d['variants'][]=$d['variants'][0];rejects(fn()=>save_product($seller,null,$d));});
test('foreign owner product UPDATE rejected',function()use($otherSeller,$pid){rejects(fn()=>save_product($otherSeller,$pid,product_edit_input($pid)));});
test('foreign owner product DELETE rejected',fn()=>rejects(fn()=>remove_product($otherSeller,$pid,true)));
test('owner product UPDATE persists',function()use($seller,$pid){$d=product_edit_input($pid);$d['product_name']='Edited shirt';save_product($seller,$pid,$d);check(query('SELECT product_name FROM {{products}} WHERE product_id=?',[$pid])->fetchColumn()==='Edited shirt');});
test('unused product physical DELETE works',function()use($seller){$p=save_product($seller,null,product_input('Delete me'));remove_product($seller,$p,true);check(!query('SELECT product_id FROM {{products}} WHERE product_id=?',[$p])->fetch());});
test('foreign key rejects orphan variant',fn()=>rejects(fn()=>query('INSERT INTO {{product_variants}}(product_id,size_label,price,stock_quantity) VALUES(999999999,?,?,?)',['M','1.00',1])));


$input=['fulfillment_method'=>'pickup','fulfillment_details'=>'Fictional test pickup point'];$key=bin2hex(random_bytes(32));$order=place_order($buyer,[$vid=>2],$input,$key);
test('DB CHECK rejects actual zero quantity insert',function()use($order,$vid2){rejects(fn()=>query('INSERT INTO {{order_items}}(order_id,variant_id,product_name_snapshot,size_label_snapshot,quantity,unit_price) VALUES(?,?,?,?,0,?)',[$order,$vid2,'Invalid zero','M','1.00']));});
test('pickup order deducts stock and uses server total',function()use($vid,$order){check((int)query('SELECT stock_quantity FROM {{product_variants}} WHERE variant_id=?',[$vid])->fetchColumn()===3);$o=query('SELECT * FROM {{orders}} WHERE order_id=?',[$order])->fetch();check($o['total_amount']==='580.00'&&$o['shipping_fee']==='0.00');});
test('retry same key returns same order without extra stock deduction',function()use($buyer,$vid,$input,$key,$order){check(place_order($buyer,[$vid=>2],$input,$key)===$order);check((int)query('SELECT stock_quantity FROM {{product_variants}} WHERE variant_id=?',[$vid])->fetchColumn()===3);});
test('same key different request rejected',fn()=>rejects(fn()=>place_order($buyer,[$vid=>1],$input,$key)));
test('mixed shop order rejected with no records',function()use($buyer,$vid,$vid2,$input){$before=(int)query('SELECT COUNT(*) FROM {{orders}}')->fetchColumn();rejects(fn()=>place_order($buyer,[$vid=>1,$vid2=>1],$input,bin2hex(random_bytes(32))));check((int)query('SELECT COUNT(*) FROM {{orders}}')->fetchColumn()===$before);});
test('insufficient stock rolls back order',function()use($buyer,$vid,$input){$before=(int)query('SELECT COUNT(*) FROM {{orders}}')->fetchColumn();rejects(fn()=>place_order($buyer,[$vid=>99],$input,bin2hex(random_bytes(32))));check((int)query('SELECT COUNT(*) FROM {{orders}}')->fetchColumn()===$before);});
test('own shop purchase rejected',fn()=>rejects(fn()=>place_order($seller,[$vid=>1],$input,bin2hex(random_bytes(32)))));
test('stale stock edit rejected after checkout',function()use($seller,$pid,$vid,$buyer,$input){$stale=product_edit_input($pid);place_order($buyer,[$vid=>1],$input,bin2hex(random_bytes(32)));rejects(fn()=>save_product($seller,$pid,$stale));});
test('buyer and seller may read order; stranger may not',function()use($buyer,$seller,$otherSeller,$order){check(visible_order($buyer,$order)!==null&&visible_order($seller,$order)!==null&&visible_order($otherSeller,$order)===null);});
test('buyer may not change seller status',fn()=>rejects(fn()=>change_order_status($buyer,$order,'confirmed')));
test('other seller may not change status',fn()=>rejects(fn()=>change_order_status($otherSeller,$order,'confirmed')));
test('invalid status jump rejected',fn()=>rejects(fn()=>change_order_status($seller,$order,'completed')));
test('correct pickup sequence records status audit',function()use($seller,$order){foreach(['confirmed','preparing','ready_for_pickup','completed'] as $s)change_order_status($seller,$order,$s);check(query('SELECT status FROM {{orders}} WHERE order_id=?',[$order])->fetchColumn()==='completed');check((int)query('SELECT COUNT(*) FROM {{order_status_history}} WHERE order_id=?',[$order])->fetchColumn()===5);});
test('completed order cannot be cancelled',fn()=>rejects(fn()=>change_order_status($seller,$order,'cancelled')));
test('referenced product hard delete rejected',fn()=>rejects(fn()=>remove_product($seller,$pid,true)));
test('product snapshots survive name and price edits',function()use($seller,$pid,$order){$d=product_edit_input($pid);$d['product_name']='Name after sale';$d['variants'][0]['price']='999.00';save_product($seller,$pid,$d);$i=query('SELECT * FROM {{order_items}} WHERE order_id=?',[$order])->fetch();check($i['product_name_snapshot']==='Edited shirt'&&$i['unit_price']==='290.00');});
$delivery=place_order($buyer,[$vid2=>1],['fulfillment_method'=>'delivery','fulfillment_details'=>'Fictional Bangsaen delivery address'],bin2hex(random_bytes(32)));
test('delivery fee calculated server-side',function()use($delivery){$o=query('SELECT * FROM {{orders}} WHERE order_id=?',[$delivery])->fetch();check($o['shipping_fee']===config()['delivery_fee']&&$o['total_amount']==='320.00');});
test('delivery cannot move to pickup status',function()use($otherSeller,$delivery){change_order_status($otherSeller,$delivery,'confirmed');change_order_status($otherSeller,$delivery,'preparing');rejects(fn()=>change_order_status($otherSeller,$delivery,'ready_for_pickup'));});
test('cancellation returns stock exactly once',function()use($otherSeller,$delivery,$vid2){$before=(int)query('SELECT stock_quantity FROM {{product_variants}} WHERE variant_id=?',[$vid2])->fetchColumn();change_order_status($otherSeller,$delivery,'cancelled');check((int)query('SELECT stock_quantity FROM {{product_variants}} WHERE variant_id=?',[$vid2])->fetchColumn()===$before+1);rejects(fn()=>change_order_status($otherSeller,$delivery,'cancelled'));check((int)query('SELECT stock_quantity FROM {{product_variants}} WHERE variant_id=?',[$vid2])->fetchColumn()===$before+1);});
test('deactivated product cannot be ordered',function()use($seller,$pid,$buyer,$vid,$input){remove_product($seller,$pid,false);rejects(fn()=>place_order($buyer,[$vid=>1],$input,bin2hex(random_bytes(32))));check((int)query('SELECT is_active FROM {{products}} WHERE product_id=?',[$pid])->fetchColumn()===0);});
test('deactivated shop cannot receive orders',function()use($otherSeller,$buyer,$vid2,$input){save_shop($otherSeller,['shop_name'=>'Other Shop','pickup_location'=>'Fictional meetup']);rejects(fn()=>place_order($buyer,[$vid2=>1],$input,bin2hex(random_bytes(32))));save_shop($otherSeller,['shop_name'=>'Other Shop','pickup_location'=>'Fictional meetup','is_active'=>1]);});
test('concurrent buyers cannot oversell last item',function()use($otherSeller,$buyer,$buyerTwo){$p=save_product($otherSeller,null,product_input('Last item',1));$v=(int)query('SELECT variant_id FROM {{product_variants}} WHERE product_id=?',[$p])->fetchColumn();$out=parallel_orders([[$buyer,$v,bin2hex(random_bytes(32))],[$buyerTwo,$v,bin2hex(random_bytes(32))]]);check(count(array_filter($out,fn($r)=>$r['ok']))===1);check((int)query('SELECT stock_quantity FROM {{product_variants}} WHERE variant_id=?',[$v])->fetchColumn()===0);});
test('concurrent duplicate checkout creates exactly one order',function()use($otherSeller,$buyer){$p=save_product($otherSeller,null,product_input('Retry item',3));$v=(int)query('SELECT variant_id FROM {{product_variants}} WHERE product_id=?',[$p])->fetchColumn();$key=bin2hex(random_bytes(32));$out=parallel_orders([[$buyer,$v,$key],[$buyer,$v,$key]]);check($out[0]['ok']&&$out[1]['ok']&&$out[0]['id']===$out[1]['id']);check((int)query('SELECT stock_quantity FROM {{product_variants}} WHERE variant_id=?',[$v])->fetchColumn()===2);});
test('LEFT JOIN retains shop with no orders',function()use($seller){$s=fixture_user('zeroorders');$shop=save_shop($s,['shop_name'=>'No orders','pickup_location'=>'Fictional point','is_active'=>1]);$r=query('SELECT s.shop_id,COUNT(o.order_id) AS n FROM {{shops}} s LEFT JOIN {{orders}} o ON o.shop_id=s.shop_id WHERE s.shop_id=? GROUP BY s.shop_id',[$shop])->fetch();check((int)$r['n']===0);});
test('INNER JOIN returns buyer shop and snapshot items',function()use($order){$r=query('SELECT u.full_name,s.shop_name,i.product_name_snapshot FROM {{orders}} o JOIN {{users}} u ON u.user_id=o.buyer_user_id JOIN {{shops}} s ON s.shop_id=o.shop_id JOIN {{order_items}} i ON i.order_id=o.order_id WHERE o.order_id=?',[$order])->fetch();check((bool)$r);});
test('catalog has no duplicate products from variants',function(){ $rows=catalog([],100);check(count(array_unique(array_column($rows,'product_id')))===count($rows)); });

$conversation=start_conversation($buyer,$pid2);
test('chat start returns existing buyer/product thread',function()use($buyer,$pid2,$conversation){check(start_conversation($buyer,$pid2)===$conversation);});
test('shop owner cannot start a buyer thread with self',fn()=>rejects(fn()=>start_conversation($otherSeller,$pid2)));
test('chat sender stored from authenticated actor',function()use($buyer,$conversation){send_message($buyer,$conversation,'Fictional size question');check((int)query('SELECT sender_user_id FROM {{messages}} WHERE conversation_id=? ORDER BY message_id DESC LIMIT 1',[$conversation])->fetchColumn()===$buyer);});
test('unrelated user cannot read or send to conversation',function()use($buyerTwo,$conversation){check(conversation_for($buyerTwo,$conversation)===null);rejects(fn()=>send_message($buyerTwo,$conversation,'forged'));});
test('shop owner replies to authorized conversation',function()use($otherSeller,$conversation){send_message($otherSeller,$conversation,'Fictional seller reply');check((int)query('SELECT COUNT(*) FROM {{messages}} WHERE conversation_id=?',[$conversation])->fetchColumn()===2);});
test('empty or oversized chat text rejected',function()use($buyer,$conversation){rejects(fn()=>send_message($buyer,$conversation,''));rejects(fn()=>send_message($buyer,$conversation,str_repeat('x',2001)));});

test('schema installer refuses nonempty database without changes',function(){
    $before=count(query('SHOW TABLES')->fetchAll());
    $proc=proc_open([PHP_BINARY,ROOT.'/scripts/migrate.php','--empty-database'],[1=>['pipe','w'],2=>['pipe','w']],$pipes);
    $out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);check(proc_close($proc)===1);check(str_contains($err,'not empty'));check(count(query('SHOW TABLES')->fetchAll())===$before);
});
test('demo seeder refuses existing users without changes',function(){
    $before=(int)query('SELECT COUNT(*) FROM {{users}}')->fetchColumn();
    $proc=proc_open([PHP_BINARY,ROOT.'/scripts/seed.php','--demo-only'],[1=>['pipe','w'],2=>['pipe','w']],$pipes);
    $out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);check(proc_close($proc)===1);check(str_contains($err,'users already exist'));check((int)query('SELECT COUNT(*) FROM {{users}}')->fetchColumn()===$before);
});

test('shared installer refuses occupied or empty namespace',function(){
    $before=count(query('SHOW TABLES')->fetchAll());
    $proc=proc_open([PHP_BINARY,ROOT.'/scripts/migrate.php','--empty-namespace'],[1=>['pipe','w'],2=>['pipe','w']],$pipes);
    stream_get_contents($pipes[1]);stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
    check(proc_close($proc)!==0&&count(query('SHOW TABLES')->fetchAll())===$before);
});
test('unrelated tables retain their row counts',function()use($otherTables,$otherCounts){check(other_table_counts($otherTables)===$otherCounts);});
$failed=count(array_filter($results,fn($r)=>$r['status']==='FAIL'));
$report=['executed_at'=>gmdate('c'),'php'=>PHP_VERSION,'database'=>query('SELECT VERSION()')->fetchColumn(),'table_prefix'=>config()['table_prefix'],'suite'=>'domain + real PDO database + multi-process concurrency','passed'=>count($results)-$failed,'failed'=>$failed,'results'=>$results];
$resultPath='storage/test-results'.(config()['table_prefix']!==''?'-'.config()['table_prefix']:'').'.json';
file_put_contents(ROOT.'/'.$resultPath,json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo count($results)." tests, $failed failed. Results: $resultPath\n";exit($failed?1:0);
