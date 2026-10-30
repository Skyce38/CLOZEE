<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit(1);
require __DIR__.'/../app/bootstrap.php';
$dir=ROOT.'/storage/photo-fixtures';if(!is_dir($dir))mkdir($dir,0700,true);
$results=[];
function photo_check(bool $ok):void{if(!$ok)throw new RuntimeException('Unexpected result');}
function photo_test(string $name,callable $run):void{global $results;try{$run();$results[]=['check'=>$name,'status'=>'PASS'];echo "PASS $name\n";}catch(Throwable $e){$results[]=['check'=>$name,'status'=>'FAIL','detail'=>$e->getMessage()];echo "FAIL $name: ".$e->getMessage()."\n";}}
function photo_reject(callable $run):void{try{$run();}catch(ValidationException $e){return;}throw new RuntimeException('Expected rejection');}
$jpeg=file_get_contents(ROOT.'/public/assets/images/linen.jpg');
file_put_contents($dir.'/valid.jpg',$jpeg);
file_put_contents($dir.'/payload.jpg',$jpeg."<?php echo 'UNTRUSTED_PHOTO_TRAILER'; ?>");
file_put_contents($dir.'/fake.jpg','<?php echo "not an image";');
file_put_contents($dir.'/fake.png','<svg xmlns="http://www.w3.org/2000/svg"><script>bad()</script></svg>');
file_put_contents($dir.'/broken.jpg',substr($jpeg,0,60));
file_put_contents($dir.'/large.jpg',$jpeg.str_repeat('x',PRODUCT_PHOTO_BYTES));
$exif="Exif\0\0II".pack('vV',42,8).pack('v',1).pack('vvVv',0x0112,3,1,6)."\0\0".pack('V',0);
file_put_contents($dir.'/rotated.jpg',substr($jpeg,0,2)."\xff\xe1".pack('n',strlen($exif)+2).$exif.substr($jpeg,2));
$png=imagecreatetruecolor(1800,900);imagefill($png,0,0,imagecolorallocate($png,220,230,220));imagepng($png,$dir.'/wide.png');unset($png);
$wide=imagecreatetruecolor(6001,1);imagepng($wide,$dir.'/too-wide.png');unset($wide);
$transparent=imagecreatetruecolor(10,10);imagealphablending($transparent,false);imagesavealpha($transparent,true);imagefill($transparent,0,0,imagecolorallocatealpha($transparent,255,255,255,127));imagepng($transparent,$dir.'/transparent.png');imagewebp($transparent,$dir.'/transparent.webp');unset($transparent);
photo_test('optional garment fields remain optional',fn()=>photo_check(product_measurement('')===null&&product_measurement(null)===null));
photo_test('measurements accept 0.1 through 300 cm',fn()=>photo_check(product_measurement('102.5')==='102.5'&&product_measurement('300')==='300'));
photo_test('measurements reject negatives zero precision arrays and overflow',function(){foreach(['-1','0','301','1.01','1e2',[]] as $value)photo_reject(fn()=>product_measurement($value));});
photo_test('valid JPEG decodes and keeps correct type',function()use($dir){$p=decode_product_photo($dir.'/valid.jpg');photo_check($p['mime_type']==='image/jpeg'&&$p['width']>0&&$p['height']>0);});
photo_test('embedded PHP trailer is removed by re-encoding',function()use($dir){$p=decode_product_photo($dir.'/payload.jpg');photo_check(!str_contains($p['bytes'],'UNTRUSTED_PHOTO_TRAILER'));});
photo_test('EXIF orientation is applied and original EXIF is removed',function()use($dir){$original=getimagesize($dir.'/valid.jpg');$p=decode_product_photo($dir.'/rotated.jpg');photo_check($p['width']===$original[1]&&$p['height']===$original[0]&&!str_contains($p['bytes'],"Exif\0\0"));});
photo_test('large dimensions are resized with original aspect ratio',function()use($dir){$p=decode_product_photo($dir.'/wide.png');photo_check($p['width']===1600&&$p['height']===800);});
photo_test('PNG transparency survives re-encoding',function()use($dir){$p=decode_product_photo($dir.'/transparent.png');$im=imagecreatefromstring($p['bytes']);photo_check($p['mime_type']==='image/png'&&((imagecolorat($im,0,0)>>24)&127)===127);});
photo_test('WebP is accepted and re-encoded',function()use($dir){$p=decode_product_photo($dir.'/transparent.webp');photo_check($p['mime_type']==='image/webp'&&$p['width']===10);});
photo_test('PHP file disguised as JPEG is rejected',fn()=>photo_reject(fn()=>decode_product_photo($dir.'/fake.jpg')));
photo_test('SVG disguised as PNG is rejected',fn()=>photo_reject(fn()=>decode_product_photo($dir.'/fake.png')));
photo_test('truncated JPEG is rejected',fn()=>photo_reject(fn()=>decode_product_photo($dir.'/broken.jpg')));
photo_test('oversize file is rejected before decoding',fn()=>photo_reject(fn()=>decode_product_photo($dir.'/large.jpg')));
photo_test('overlarge image dimension is rejected',fn()=>photo_reject(fn()=>decode_product_photo($dir.'/too-wide.png')));
photo_test('arbitrary local path is not accepted as HTTP upload',fn()=>photo_reject(fn()=>stage_product_photos(['name'=>['x.jpg'],'tmp_name'=>[$dir.'/valid.jpg'],'error'=>[0],'size'=>[100]])));
photo_test('malformed upload arrays are rejected',fn()=>photo_reject(fn()=>stage_product_photos(['name'=>'x.jpg'])));
photo_test('stored image name rejects traversal and executable extensions',function(){foreach(['../config/local.php',str_repeat('a',32).'.php'] as $name){try{photo_path($name);}catch(LogicException $e){continue;}throw new RuntimeException('Unsafe path accepted');}});
$failed=count(array_filter($results,fn($r)=>$r['status']==='FAIL'));
file_put_contents(ROOT.'/storage/product-photos-unit-results.json',json_encode(['executed_at'=>gmdate('c'),'php'=>PHP_VERSION,'passed'=>count($results)-$failed,'failed'=>$failed,'results'=>$results],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
exit($failed?1:0);
