<?php
require __DIR__.'/../app/bootstrap.php';
$id=filter_var($_GET['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if(!$id)fail(404,'ไม่พบรูปภาพ');
$photo=query('SELECT i.*,p.is_active AS product_active,s.is_active AS shop_active,s.owner_user_id FROM {{product_images}} i JOIN {{products}} p ON p.product_id=i.product_id JOIN {{shops}} s ON s.shop_id=p.shop_id WHERE i.image_id=?',[$id])->fetch();
if(!$photo||(!((int)$photo['product_active']&&(int)$photo['shop_active'])&&(int)(user()['user_id']??0)!==(int)$photo['owner_user_id']))fail(404,'ไม่พบรูปภาพ');
$path=photo_path($photo['storage_name']);
if(!is_file($path))fail(404,'ไม่พบรูปภาพ');
header('Content-Type: '.$photo['mime_type']);
header('Content-Disposition: inline; filename="product-image.'.pathinfo($path,PATHINFO_EXTENSION).'"');
header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'");
header('Content-Length: '.filesize($path));
session_write_close();readfile($path);
