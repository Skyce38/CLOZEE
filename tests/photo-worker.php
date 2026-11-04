<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit(1);
require __DIR__.'/../app/bootstrap.php';
if(!str_contains(config()['db_name'],'test'))exit(1);
$id=positive_id($argv[1]??null);$bytes="\0\xffPNG-test";
try{
    transaction(function()use($id,$bytes){
        query('SELECT product_id FROM {{products}} WHERE product_id=? FOR UPDATE',[$id])->fetch();
        sync_product_photos($id,[],[['storage_name'=>bin2hex(random_bytes(16)).'.png','mime_type'=>'image/png','byte_size'=>strlen($bytes),'width'=>1,'height'=>1,'image_data'=>$bytes]]);
    });echo 'created';
}catch(ValidationException $e){echo 'limited';}
