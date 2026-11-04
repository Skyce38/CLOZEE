<?php
declare(strict_types=1);

const PRODUCT_PHOTO_LIMIT = 4;
const PRODUCT_PHOTO_BYTES = 1572864;

function ini_bytes(string $value): int {
    $value=trim($value);if($value===''||$value==='0'||$value==='-1')return PHP_INT_MAX;
    return (int)((float)$value * match(strtolower(substr($value,-1))){'g'=>1073741824,'m'=>1048576,'k'=>1024,default=>1});
}
function photo_byte_limit(): int { return min(PRODUCT_PHOTO_BYTES,ini_bytes((string)ini_get('upload_max_filesize'))); }
function photo_request_limit(): int { return min(PRODUCT_PHOTO_LIMIT*photo_byte_limit(),max(0,ini_bytes((string)ini_get('post_max_size'))-65536)); }
function product_images(int $id): array { return query('SELECT image_id,product_id,storage_name,mime_type,byte_size,width,height,sort_order,created_at FROM {{product_images}} WHERE product_id=? ORDER BY sort_order,image_id',[$id])->fetchAll(); }
function product_image_url(array $product): string {
    if(!empty($product['cover_image_id']))return url('image.php?id='.(int)$product['cover_image_id']);
    return url('assets/images/'.(in_array($product['image_path']??'',IMAGES,true)?$product['image_path']:'linen.jpg'));
}
// PNG subset emitted by browser canvas: static, 8-bit RGB/RGBA, non-interlaced.
// CRC, bounded inflate, exact scanline size and filters are checked independently.
function png_chunk(string $type,string $data): string {
    return pack('N',strlen($data)).$type.$data.hash('crc32b',$type.$data,true);
}
function decode_product_photo(string $file): array {
    $size=filesize($file);
    if($size===false||$size<1||$size>photo_byte_limit())throw new ValidationException('รูปหลังปรับต้องไม่เกิน '.number_format(photo_byte_limit()/1048576,1).' MB');
    $bytes=file_get_contents($file);
    if((new finfo(FILEINFO_MIME_TYPE))->buffer($bytes)!=='image/png'||substr($bytes,0,8)!=="\x89PNG\r\n\x1a\n")throw new ValidationException('กรุณาเลือกรูปผ่านฟอร์มเพื่อให้ระบบปรับภาพเป็น PNG ก่อนส่ง');
    $offset=8;$header=null;$compressed='';$ended=false;$closed=false;$chunks=0;
    while($offset+12<=strlen($bytes)){
        if(++$chunks>2048)throw new ValidationException('โครงสร้างภาพซับซ้อนเกินกำหนด');
        $length=unpack('N',substr($bytes,$offset,4))[1];$type=substr($bytes,$offset+4,4);
        if($length>strlen($bytes)-$offset-12||!preg_match('/^[A-Za-z]{4}$/D',$type))throw new ValidationException('ไฟล์ภาพไม่สมบูรณ์');
        $data=substr($bytes,$offset+8,$length);$crc=substr($bytes,$offset+8+$length,4);$offset+=12+$length;
        if(!hash_equals(hash('crc32b',$type.$data,true),$crc))throw new ValidationException('ข้อมูลภาพเสียหาย');
        if($header===null&&$type!=='IHDR')throw new ValidationException('ไม่พบส่วนหัวภาพ');
        if($type==='IHDR'){
            if($header!==null||$length!==13)throw new ValidationException('ส่วนหัวภาพไม่ถูกต้อง');
            $header=$data;$info=unpack('Nwidth/Nheight/Cdepth/Ccolor/Ccompression/Cfilter/Cinterlace',$header);
            if($info['width']<1||$info['height']<1||max($info['width'],$info['height'])>1600||$info['depth']!==8||!in_array($info['color'],[2,6],true)||$info['compression']!==0||$info['filter']!==0||$info['interlace']!==0)throw new ValidationException('กรุณาใช้ภาพที่ปรับผ่านฟอร์ม ขนาดไม่เกิน 1,600 พิกเซล');
        }elseif($type==='IDAT'){
            if($closed)throw new ValidationException('ลำดับข้อมูลภาพไม่ถูกต้อง');$compressed.=$data;
        }elseif($type==='IEND'){
            if($length!==0||$compressed==='')throw new ValidationException('ไฟล์ภาพไม่สมบูรณ์');$ended=true;break;
        }else{
            if($compressed!=='')$closed=true;
            if(ctype_upper($type[0])||in_array($type,['acTL','fcTL','fdAT'],true))throw new ValidationException('รองรับภาพนิ่งที่ปรับผ่านฟอร์มเท่านั้น');
        }
    }
    if(!$ended||$header===null)throw new ValidationException('ไฟล์ภาพไม่สมบูรณ์');
    $stride=1+$info['width']*($info['color']===6?4:3);$expected=$stride*$info['height'];
    $raw=@gzuncompress($compressed,$expected+1);
    if($raw===false||strlen($raw)!==$expected)throw new ValidationException('ข้อมูลพิกเซลภาพไม่สมบูรณ์');
    for($row=0;$row<$info['height'];$row++)if(ord($raw[$row*$stride])>4)throw new ValidationException('ตัวกรองภาพไม่ถูกต้อง');
    // Rebuild only necessary chunks; removes EXIF/text/animation/trailing payloads.
    $clean="\x89PNG\r\n\x1a\n".png_chunk('IHDR',$header).png_chunk('IDAT',gzcompress($raw,6)).png_chunk('IEND','');
    if(strlen($clean)>photo_byte_limit())throw new ValidationException('ภาพหลังปรับยังใหญ่เกินกำหนด กรุณาเลือกภาพเล็กลง');
    return ['bytes'=>$clean,'mime_type'=>'image/png','width'=>$info['width'],'height'=>$info['height'],'extension'=>'png'];
}
function stage_product_photos(array $files): array {
    if(!$files)return [];
    foreach(['name','tmp_name','error','size'] as $key)if(!isset($files[$key])||!is_array($files[$key]))throw new ValidationException('ข้อมูลไฟล์ภาพไม่ถูกต้อง');
    if(count($files['error'])>PRODUCT_PHOTO_LIMIT)throw new ValidationException('เพิ่มรูปใหม่ได้ครั้งละไม่เกิน 4 รูป');
    $prepared=[];$total=0;
    foreach($files['error'] as $key=>$error){
        if(!is_int($error))throw new ValidationException('ข้อมูลไฟล์ภาพไม่ถูกต้อง');
        if($error===UPLOAD_ERR_NO_FILE)continue;
        if($error!==UPLOAD_ERR_OK)throw new ValidationException('อัปโหลดรูปไม่สำเร็จ กรุณาตรวจขนาดไฟล์แล้วเลือกใหม่');
        $tmp=$files['tmp_name'][$key]??null;
        if(!is_string($tmp)||!is_uploaded_file($tmp))throw new ValidationException('ไม่พบไฟล์อัปโหลดที่ถูกต้อง');
        $total+=(int)filesize($tmp);if($total>photo_request_limit())throw new ValidationException('ไฟล์ภาพรวมมีขนาดใหญ่เกินไป');
        $decoded=decode_product_photo($tmp);
        $prepared[]=['storage_name'=>bin2hex(random_bytes(16)).'.png','mime_type'=>'image/png','width'=>$decoded['width'],'height'=>$decoded['height'],'byte_size'=>strlen($decoded['bytes']),'image_data'=>$decoded['bytes']];
    }
    return $prepared;
}
function sync_product_photos(int $id,array $input,array $new): void {
    $old=product_images($id);$byId=array_column($old,null,'image_id');
    $removed=$input['remove_images']??[];$order=$input['image_order']??[];
    foreach([$removed,$order] as $list)if(!is_array($list)||count($list)>4)throw new ValidationException('รายการรูปภาพไม่ถูกต้อง');
    $removed=array_map('positive_id',$removed);$order=array_map('positive_id',$order);
    if(count(array_unique($removed))!==count($removed)||count(array_unique($order))!==count($order)||array_diff(array_merge($removed,$order),array_keys($byId)))throw new ValidationException('รูปภาพไม่ได้อยู่ในสินค้านี้');
    $ids=array_values(array_diff(array_merge($order,array_diff(array_keys($byId),$order)),$removed));
    if(count($ids)+count($new)>4)throw new ValidationException('สินค้าหนึ่งรายการมีรูปได้ไม่เกิน 4 รูป');
    $photos=[];foreach($ids as $photoId)$photos['saved:'.$photoId]=$byId[$photoId];
    foreach($new as $index=>$photo){query('INSERT INTO {{product_images}}(product_id,storage_name,mime_type,byte_size,width,height,image_data,sort_order) VALUES(?,?,?,?,?,?,?,0)',[$id,$photo['storage_name'],$photo['mime_type'],$photo['byte_size'],$photo['width'],$photo['height'],$photo['image_data']]);$photo['image_id']=(int)db()->lastInsertId();$photos['new:'.$index]=$photo;}
    $cover=$input['cover_image']??'';
    if(!is_string($cover))throw new ValidationException('รูปหลักไม่ถูกต้อง');
    if($cover!==''&&!isset($photos[$cover])&&!in_array($cover,array_map(fn($n)=>'saved:'.$n,$removed),true))throw new ValidationException('รูปหลักไม่ได้อยู่ในสินค้านี้');
    if(isset($photos[$cover]))$photos=[$cover=>$photos[$cover]]+$photos;
    foreach($removed as $photoId){query('DELETE FROM {{product_images}} WHERE image_id=? AND product_id=?',[$photoId,$id]);}
    foreach(array_values($photos) as $index=>$photo)query('UPDATE {{product_images}} SET sort_order=? WHERE image_id=? AND product_id=?',[$index,$photo['image_id'],$id]);
}
