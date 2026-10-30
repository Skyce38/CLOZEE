<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit(1);
$name=getenv('CLOZEE_TEST_DB');
if(!$name||!preg_match('/^[a-zA-Z0-9_]*test[a-zA-Z0-9_]*$/D',$name))exit(1);
putenv('CLOZEE_DB_NAME='.$name);putenv('CLOZEE_TABLE_PREFIX=upgrade_'.bin2hex(random_bytes(3)).'_');
require __DIR__.'/../app/bootstrap.php';
$oldNames=array_slice(CLOZEE_TABLES,0,10);$results=[];
function migration_check(bool $ok,string $name):void{global $results;$results[]=['check'=>$name,'status'=>$ok?'PASS':'FAIL'];if(!$ok)throw new RuntimeException($name);echo 'PASS '.$name.PHP_EOL;}
$fixture=file_get_contents(__DIR__.'/fixtures/schema-v1.sql');
$pattern=implode('|',$oldNames);
$fixture=preg_replace_callback('/\b(CREATE\s+TABLE|REFERENCES)\s+('.$pattern.')\b/i',fn($m)=>$m[1].' `'.table_name($m[2]).'`',$fixture);
$fixture=preg_replace_callback('/\bCONSTRAINT\s+([a-zA-Z0-9_]+)\b/',fn($m)=>'CONSTRAINT `'.config()['table_prefix'].$m[1].'`',$fixture);
db()->exec($fixture);db()->exec(application_sql_file('seed.sql'));
place_order(3,[1=>1],['fulfillment_method'=>'pickup','fulfillment_details'=>'Fictional migration pickup'],bin2hex(random_bytes(32)));
$before=[];foreach($oldNames as $table)$before[$table]=query('SELECT * FROM {{'.$table.'}}')->fetchAll();
$unrelated=[];foreach(query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table)if(!in_array($table,array_map('table_name',CLOZEE_TABLES),true))$unrelated[$table]=(int)db()->query('SELECT COUNT(*) FROM `'.$table.'`')->fetchColumn();
db()->exec(application_sql_file('migrations/002-database-product-photos.sql'));
foreach($before as $table=>$rows){$after=query('SELECT * FROM {{'.$table.'}}')->fetchAll();if($table==='products')$after=array_map(fn($r)=>array_intersect_key($r,array_flip(array_keys($rows[0]))),$after);migration_check($after===$rows,'upgrade preserves '.$table.' rows and values');}
$column=query("SELECT COLUMN_TYPE,IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME='image_data'",[config()['db_name'],table_name('product_images')])->fetch();
migration_check($column['COLUMN_TYPE']==='mediumblob'&&$column['IS_NULLABLE']==='NO','upgrade installs non-null MEDIUMBLOB');
migration_check(count(product_images(1))===0,'old products do not invent uploaded images');
foreach($unrelated as $table=>$count)if((int)db()->query('SELECT COUNT(*) FROM `'.$table.'`')->fetchColumn()!==$count)throw new RuntimeException('Unrelated table changed');
migration_check(true,'all unrelated table row counts preserved');
$process=proc_open([PHP_BINARY,ROOT.'/scripts/migrate-product-photos.php','--apply'],[1=>['pipe','w'],2=>['pipe','w']],$pipes);$message=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);migration_check(proc_close($process)===0&&str_contains($message,'already applied'),'repeat migration is a no-op');
file_put_contents(ROOT.'/storage/photo-migration-results.json',json_encode(['executed_at'=>gmdate('c'),'scope'=>'isolated old ten-table schema fixture from cd842c6','passed'=>count($results),'failed'=>0,'results'=>$results],JSON_PRETTY_PRINT));
echo "Temporary test namespace retained for inspection. No production database used.\n";
