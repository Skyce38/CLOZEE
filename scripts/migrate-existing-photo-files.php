<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit(1);
require __DIR__.'/../app/bootstrap.php';
if(!in_array('--apply-local-files',$argv,true)){fwrite(STDERR,"Local old photo schema only. Back up DB/files, then pass --apply-local-files. Not for Angsila's ten-table deployment.\n");exit(1);}
$table=table_name('product_images');
$exists=query("SELECT IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME='image_data'",[config()['db_name'],$table])->fetchColumn();
if($exists==='NO'){
    $checks=(int)query("SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=? AND TABLE_NAME=? AND CONSTRAINT_NAME IN (?,?)",[config()['db_name'],$table,config()['table_prefix'].'ck_product_images_bytes',config()['table_prefix'].'ck_product_images_dimensions'])->fetchColumn();
    if($checks!==2)throw new RuntimeException('Partial constraint state; review before continuing');
    echo "Database image storage already installed; no changes.\n";exit;
}
$rows=query('SELECT * FROM {{product_images}} ORDER BY image_id')->fetchAll();$prepared=[];
foreach($rows as $row){
    if(!preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/D',$row['storage_name']))throw new RuntimeException('Unexpected old filename');
    $path=ROOT.'/storage/uploads/products/'.$row['storage_name'];
    if(!is_file($path))throw new RuntimeException('Missing old photo; stop without schema change');
    $bytes=file_get_contents($path);$info=@getimagesizefromstring($bytes);
    if(strlen($bytes)!==(int)$row['byte_size']||!$info||$info['mime']!==$row['mime_type']||$info[0]!=(int)$row['width']||$info[1]!=(int)$row['height'])throw new RuntimeException('Old photo metadata mismatch');
    $prepared[$row['image_id']]=$bytes;
}
if($exists===false)db()->exec('ALTER TABLE `'.$table.'` ADD COLUMN image_data MEDIUMBLOB NULL AFTER byte_size');
transaction(function()use($prepared){foreach($prepared as $id=>$bytes)query('UPDATE {{product_images}} SET image_data=? WHERE image_id=?',[$bytes,$id]);});
if((int)query('SELECT COUNT(*) FROM {{product_images}} WHERE image_data IS NULL OR OCTET_LENGTH(image_data)<>byte_size')->fetchColumn()!==0)throw new RuntimeException('Image verification failed; originals preserved');
db()->exec('ALTER TABLE `'.$table.'` MODIFY COLUMN image_data MEDIUMBLOB NOT NULL');
db()->exec('ALTER TABLE `'.$table.'` ADD CONSTRAINT `'.config()['table_prefix'].'ck_product_images_bytes` CHECK (byte_size > 0 AND byte_size <= 1572864 AND OCTET_LENGTH(image_data) = byte_size), ADD CONSTRAINT `'.config()['table_prefix'].'ck_product_images_dimensions` CHECK (width BETWEEN 1 AND 1600 AND height BETWEEN 1 AND 1600)');
echo count($prepared)." old photos copied and verified in database. Original files preserved.\n";
