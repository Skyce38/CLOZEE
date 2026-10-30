<?php
declare(strict_types=1);

const PRODUCT_PHOTO_LIMIT = 4;
const PRODUCT_PHOTO_BYTES = 1572864;
const PRODUCT_PHOTO_PIXELS = 12000000;

function ini_bytes(string $value): int {
    $value=trim($value);if($value===''||$value==='0'||$value==='-1')return PHP_INT_MAX;
    return (int)((float)$value * match(strtolower(substr($value,-1))){'g'=>1073741824,'m'=>1048576,'k'=>1024,default=>1});
}
function photo_byte_limit(): int { return min(PRODUCT_PHOTO_BYTES,ini_bytes((string)ini_get('upload_max_filesize'))); }
function photo_request_limit(): int { return min(PRODUCT_PHOTO_LIMIT*photo_byte_limit(),max(0,ini_bytes((string)ini_get('post_max_size'))-65536)); }
function product_images(int $id): array { return query('SELECT * FROM {{product_images}} WHERE product_id=? ORDER BY sort_order,image_id',[$id])->fetchAll(); }
function product_image_url(array $product): string {
    if(!empty($product['cover_image_id']))return url('image.php?id='.(int)$product['cover_image_id']);
    return url('assets/images/'.(in_array($product['image_path']??'',IMAGES,true)?$product['image_path']:'linen.jpg'));
}
function photo_path(string $name): string {
    if(!preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/D',$name))throw new LogicException('Invalid stored image name');
    return ROOT.'/storage/uploads/products/'.$name;
}
function discard_photo(string $name): void {
    $path=photo_path($name);if(is_file($path)&&!@unlink($path))error_log('CLOZEE: unused product image could not be removed');
}
function decode_product_photo(string $file): array {
    $size=filesize($file);
    if($size===false||$size<1||$size>photo_byte_limit())throw new ValidationException('รูปแต่ละไฟล์ต้องไม่เกิน '.number_format(photo_byte_limit()/1048576,1).' MB');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file);
    $types=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    $info=@getimagesize($file);
    if(!isset($types[$mime])||!$info||($info['mime']??'')!==$mime)throw new ValidationException('รองรับเฉพาะภาพ JPG, PNG และ WebP ที่เปิดอ่านได้');
    if($info[0]<1||$info[1]<1||$info[0]>6000||$info[1]>6000||$info[0]*$info[1]>PRODUCT_PHOTO_PIXELS)throw new ValidationException('ภาพต้องไม่เกิน 6,000 พิกเซลต่อด้านและ 12 ล้านพิกเซล');
    if(!extension_loaded('gd')||($mime==='image/jpeg'&&!function_exists('exif_read_data')))throw new ValidationException('ระบบอัปโหลดภาพยังไม่พร้อม กรุณาติดต่อผู้ดูแล');
    $image=@imagecreatefromstring((string)file_get_contents($file));
    if(!$image)throw new ValidationException('ไฟล์ภาพไม่สมบูรณ์ กรุณาเลือกรูปใหม่');
    if($mime==='image/jpeg'){
        $exif=@exif_read_data($file);$orientation=is_array($exif)?(int)($exif['Orientation']??1):1;
        if(in_array($orientation,[2,5,7],true))imageflip($image,IMG_FLIP_HORIZONTAL);
        if($orientation===4)imageflip($image,IMG_FLIP_VERTICAL);
        $angle=match($orientation){3=>180,5=>90,6=>-90,7=>-90,8=>90,default=>0};
        if($angle)$image=imagerotate($image,$angle,0);
    }
    $scale=min(1,1600/max(imagesx($image),imagesy($image)));
    if($scale<1)$image=imagescale($image,max(1,(int)round(imagesx($image)*$scale)),max(1,(int)round(imagesy($image)*$scale)));
    if(!$image)throw new ValidationException('ปรับขนาดภาพไม่ได้ กรุณาเลือกรูปใหม่');
    imagesavealpha($image,true);
    ob_start();
    try{$ok=match($mime){'image/jpeg'=>imagejpeg($image,null,85),'image/png'=>imagepng($image,null,6),'image/webp'=>imagewebp($image,null,85)};$bytes=(string)ob_get_contents();}
    finally{ob_end_clean();}
    if(!$ok||$bytes===''||strlen($bytes)>PRODUCT_PHOTO_BYTES)throw new ValidationException('ไฟล์หลังปรับภาพมีขนาดใหญ่เกินไป กรุณาใช้ภาพขนาดเล็กลง');
    return ['bytes'=>$bytes,'mime_type'=>$mime,'width'=>imagesx($image),'height'=>imagesy($image),'extension'=>$types[$mime]];
}
function stage_product_photos(array $files): array {
    if(!$files)return [];
    foreach(['name','tmp_name','error','size'] as $key)if(!isset($files[$key])||!is_array($files[$key]))throw new ValidationException('ข้อมูลไฟล์ภาพไม่ถูกต้อง');
    if(count($files['error'])>PRODUCT_PHOTO_LIMIT)throw new ValidationException('เพิ่มรูปใหม่ได้ครั้งละไม่เกิน 4 รูป');
    $prepared=[];$total=0;
    try{
        foreach($files['error'] as $key=>$error){
            if(!is_int($error))throw new ValidationException('ข้อมูลไฟล์ภาพไม่ถูกต้อง');
            if($error===UPLOAD_ERR_NO_FILE)continue;
            if($error!==UPLOAD_ERR_OK)throw new ValidationException('อัปโหลดรูปไม่สำเร็จ กรุณาตรวจขนาดไฟล์แล้วเลือกใหม่');
            $tmp=$files['tmp_name'][$key]??null;
            if(!is_string($tmp)||!is_uploaded_file($tmp))throw new ValidationException('ไม่พบไฟล์อัปโหลดที่ถูกต้อง');
            $total+=(int)filesize($tmp);if($total>photo_request_limit())throw new ValidationException('ไฟล์ภาพรวมมีขนาดใหญ่เกินไป');
            $decoded=decode_product_photo($tmp);
            $name=bin2hex(random_bytes(16)).'.'.$decoded['extension'];$path=photo_path($name);$dir=dirname($path);
            if(!is_dir($dir)&&!@mkdir($dir,0700,true)&&!is_dir($dir))throw new ValidationException('พื้นที่เก็บภาพยังไม่พร้อม กรุณาติดต่อผู้ดูแล');
            $stream=@fopen($path,'xb');if(!$stream)throw new ValidationException('บันทึกภาพไม่ได้ กรุณาติดต่อผู้ดูแล');
            $prepared[]=['storage_name'=>$name,'mime_type'=>$decoded['mime_type'],'width'=>$decoded['width'],'height'=>$decoded['height'],'byte_size'=>strlen($decoded['bytes'])];
            try{if(fwrite($stream,$decoded['bytes'])!==strlen($decoded['bytes']))throw new RuntimeException('Image write incomplete');}finally{fclose($stream);}
            @chmod($path,0600);
        }
        return $prepared;
    }catch(Throwable $e){foreach($prepared as $photo)discard_photo($photo['storage_name']);throw $e;}
}
function sync_product_photos(int $id,array $input,array $new): array {
    $old=product_images($id);$byId=array_column($old,null,'image_id');
    $removed=$input['remove_images']??[];$order=$input['image_order']??[];
    foreach([$removed,$order] as $list)if(!is_array($list)||count($list)>4)throw new ValidationException('รายการรูปภาพไม่ถูกต้อง');
    $removed=array_map('positive_id',$removed);$order=array_map('positive_id',$order);
    if(count(array_unique($removed))!==count($removed)||count(array_unique($order))!==count($order)||array_diff(array_merge($removed,$order),array_keys($byId)))throw new ValidationException('รูปภาพไม่ได้อยู่ในสินค้านี้');
    $ids=array_values(array_diff(array_merge($order,array_diff(array_keys($byId),$order)),$removed));
    if(count($ids)+count($new)>4)throw new ValidationException('สินค้าหนึ่งรายการมีรูปได้ไม่เกิน 4 รูป');
    $photos=[];foreach($ids as $photoId)$photos['saved:'.$photoId]=$byId[$photoId];
    foreach($new as $index=>$photo){query('INSERT INTO {{product_images}}(product_id,storage_name,mime_type,byte_size,width,height,sort_order) VALUES(?,?,?,?,?,?,0)',[$id,$photo['storage_name'],$photo['mime_type'],$photo['byte_size'],$photo['width'],$photo['height']]);$photo['image_id']=(int)db()->lastInsertId();$photos['new:'.$index]=$photo;}
    $cover=$input['cover_image']??'';
    if(!is_string($cover))throw new ValidationException('รูปหลักไม่ถูกต้อง');
    if($cover!==''&&!isset($photos[$cover])&&!in_array($cover,array_map(fn($n)=>'saved:'.$n,$removed),true))throw new ValidationException('รูปหลักไม่ได้อยู่ในสินค้านี้');
    if(isset($photos[$cover]))$photos=[$cover=>$photos[$cover]]+$photos;
    $retired=[];
    foreach($removed as $photoId){query('DELETE FROM {{product_images}} WHERE image_id=? AND product_id=?',[$photoId,$id]);$retired[]=$byId[$photoId]['storage_name'];}
    foreach(array_values($photos) as $index=>$photo)query('UPDATE {{product_images}} SET sort_order=? WHERE image_id=? AND product_id=?',[$index,$photo['image_id'],$id]);
    return $retired;
}
