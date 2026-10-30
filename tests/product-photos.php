<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit(1);
require __DIR__.'/../app/bootstrap.php';
$dir=ROOT.'/storage/photo-fixtures';if(!is_dir($dir))mkdir($dir,0700,true);
$results=[];
function photo_check(bool $ok):void{if(!$ok)throw new RuntimeException('Unexpected result');}
function photo_test(string $name,callable $run):void{global $results;try{$run();$results[]=['check'=>$name,'status'=>'PASS'];echo "PASS $name\n";}catch(Throwable $e){$results[]=['check'=>$name,'status'=>'FAIL','detail'=>$e->getMessage()];echo "FAIL $name: ".$e->getMessage()."\n";}}
function photo_reject(callable $run):void{try{$run();}catch(ValidationException $e){return;}throw new RuntimeException('Expected rejection');}
function fixture_png(int $width=20,int $height=10,int $color=6,?string $raw=null):string{
    $stride=$width*($color===6?4:3);
    $raw??=str_repeat("\0".str_repeat($color===6?"\x50\xa0\x70\xff":"\x50\xa0\x70",$width),$height);
    return "\x89PNG\r\n\x1a\n".png_chunk('IHDR',pack('NNCCCCC',$width,$height,8,$color,0,0,0)).png_chunk('IDAT',gzcompress($raw)).png_chunk('IEND','');
}
$png=fixture_png();file_put_contents($dir.'/valid.png',$png);
file_put_contents($dir.'/payload.png',$png."<?php echo 'UNTRUSTED_PHOTO_TRAILER'; ?>");
file_put_contents($dir.'/metadata.png',substr($png,0,33).png_chunk('tEXt','private metadata').substr($png,33));
file_put_contents($dir.'/fake.jpg','<?php echo "not an image";');
file_put_contents($dir.'/fake.png','<svg xmlns="http://www.w3.org/2000/svg"><script>bad()</script></svg>');
file_put_contents($dir.'/broken.jpg',substr($png,0,60));
file_put_contents($dir.'/large.jpg',$png.str_repeat('x',PRODUCT_PHOTO_BYTES));
$jpeg=file_get_contents(ROOT.'/public/assets/images/linen.jpg');file_put_contents($dir.'/valid.jpg',$jpeg);
$exif="Exif\0\0II".pack('vV',42,8).pack('v',1).pack('vvVv',0x0112,3,1,6)."\0\0".pack('V',0);
file_put_contents($dir.'/rotated.jpg',substr($jpeg,0,2)."\xff\xe1".pack('n',strlen($exif)+2).$exif.substr($jpeg,2));
file_put_contents($dir.'/transparent.webp',base64_decode('UklGRkgAAABXRUJQVlA4WAoAAAAQAAAACQAACQAAQUxQSAkAAAABBxAREYiI/gcAVlA4IBgAAAAwAQCdASoKAAoAAUAmJaQAA3AA/vz0AAA='));
file_put_contents($dir.'/wide.png',fixture_png(1800,900));file_put_contents($dir.'/transparent.png',fixture_png(20,10,6,str_repeat("\0".str_repeat("\x50\xa0\x70\0",20),10)));
$badCrc=$png;$badCrc[29]=chr(ord($badCrc[29])^1);file_put_contents($dir.'/crc.png',$badCrc);
file_put_contents($dir.'/filter.png',fixture_png(1,1,6,"\5abcd"));
file_put_contents($dir.'/short.png',fixture_png(20,10,6,"\0short"));
file_put_contents($dir.'/bomb.png',fixture_png(1,1,6,str_repeat('a',2000000)));
file_put_contents($dir.'/animated.png',substr($png,0,33).png_chunk('acTL',pack('NN',2,0)).substr($png,33));
file_put_contents($dir.'/critical.png',substr($png,0,33).png_chunk('ABCD','x').substr($png,33));
file_put_contents($dir.'/duplicate.png',substr($png,0,33).substr($png,8));
photo_test('optional garment fields remain optional',fn()=>photo_check(product_measurement('')===null&&product_measurement(null)===null));
photo_test('measurements accept 0.1 through 300 cm',fn()=>photo_check(product_measurement('0.1')==='0.1'&&product_measurement('300')==='300'));
photo_test('measurements reject negatives zero precision arrays and overflow',function(){foreach(['-1','0','301','1.01','1e2',[]] as $value)photo_reject(fn()=>product_measurement($value));});
photo_test('canonical RGBA PNG accepted without GD or EXIF',function()use($dir){$p=decode_product_photo($dir.'/valid.png');photo_check($p['mime_type']==='image/png'&&$p['width']===20&&$p['height']===10);});
photo_test('RGB PNG accepted',function()use($dir){file_put_contents($dir.'/rgb.png',fixture_png(7,5,2));photo_check(decode_product_photo($dir.'/rgb.png')['width']===7);});
photo_test('embedded PHP trailer is removed',function()use($dir){photo_check(!str_contains(decode_product_photo($dir.'/payload.png')['bytes'],'UNTRUSTED_PHOTO_TRAILER'));});
photo_test('ancillary metadata is removed',function()use($dir){photo_check(!str_contains(decode_product_photo($dir.'/metadata.png')['bytes'],'private metadata'));});
foreach(['fake.jpg','fake.png','broken.jpg','large.jpg','crc.png','filter.png','short.png','bomb.png','animated.png','critical.png','duplicate.png','wide.png','rotated.jpg','valid.jpg'] as $name)photo_test('server rejects '.$name,fn()=>photo_reject(fn()=>decode_product_photo($dir.'/'.$name)));
photo_test('arbitrary local path is not accepted as HTTP upload',fn()=>photo_reject(fn()=>stage_product_photos(['name'=>['x.png'],'tmp_name'=>[$dir.'/valid.png'],'error'=>[0],'size'=>[100]])));
photo_test('malformed upload arrays are rejected',fn()=>photo_reject(fn()=>stage_product_photos(['name'=>'x.png'])));
$failed=count(array_filter($results,fn($r)=>$r['status']==='FAIL'));
file_put_contents(ROOT.'/storage/product-photos-unit-results'.(extension_loaded('gd')?'':'-no-gd').'.json',json_encode(['executed_at'=>gmdate('c'),'php'=>PHP_VERSION,'gd'=>extension_loaded('gd'),'passed'=>count($results)-$failed,'failed'=>$failed,'results'=>$results],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
exit($failed?1:0);
