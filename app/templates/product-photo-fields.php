<?php $photos=$id?product_images($id):[]; ?>
<section class="panel photo-editor" data-photo-editor data-max-files="4" data-max-bytes="<?=photo_byte_limit()?>" data-max-total="<?=photo_request_limit()?>">
<h2>รูปสินค้า</h2><p class="field-help">เพิ่มได้สูงสุด 4 รูป รองรับ JPG, PNG, WebP รูปละไม่เกิน <?=number_format(photo_byte_limit()/1048576,1)?> MB เลือกรูปหลักที่ต้องการให้แสดงบนหน้าร้าน</p>
<div class="photo-grid" data-saved-photos>
<?php foreach($photos as $index=>$photo): $photoId=(int)$photo['image_id']; ?>
<div class="photo-edit-card" data-saved-photo><input type="hidden" name="image_order[]" value="<?=$photoId?>"><img src="<?=h(url('image.php?id='.$photoId))?>" alt="รูปสินค้าเดิม <?=($index+1)?>">
<label class="checkbox"><input type="radio" name="cover_image" value="saved:<?=$photoId?>" <?=$index===0?'checked':''?>>ใช้เป็นภาพหลัก</label>
<label class="checkbox"><input type="checkbox" name="remove_images[]" value="<?=$photoId?>">นำรูปนี้ออก</label></div>
<?php endforeach; ?></div>
<label class="photo-upload">เพิ่มรูปสินค้า<input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple data-photo-input></label>
<p class="field-help" data-photo-message role="status">เลือกภาพด้านหน้า ด้านหลัง ป้ายไซซ์ หรือตำหนิ หากบันทึกไม่สำเร็จให้เลือกรูปใหม่อีกครั้ง</p>
<div class="photo-grid" data-photo-previews></div>
</section>
