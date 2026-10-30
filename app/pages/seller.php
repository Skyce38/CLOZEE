<?php
declare(strict_types=1);
$actor=(int)require_user()['user_id'];$shop=own_shop($actor);$error='';$mode=$sellerMode??'dashboard';$title='จัดการร้าน';
if($_SERVER['REQUEST_METHOD']==='POST') {
    post_guard();
    try {
        if($mode==='shop') { save_shop($actor,$_POST);flash('บันทึกข้อมูลร้านแล้ว');redirect('seller/shop.php'); }
        if($mode==='edit') {
            $id=empty($_POST['product_id'])?null:positive_id($_POST['product_id']);
            save_product($actor,$id,$_POST);flash('บันทึกสินค้าแล้ว');redirect('seller/products.php');
        }
        if($mode==='products') {
            remove_product($actor,positive_id($_POST['product_id']??null),($_POST['action']??'')==='delete');
            flash(($_POST['action']??'')==='delete'?'ลบสินค้าแล้ว':'ปิดขายสินค้าแล้ว');redirect('seller/products.php');
        }
    } catch(ValidationException $e) { $error=$e->getMessage();http_response_code(422); }
}
if($mode==='edit'&&!$shop) { flash('สร้างร้านของคุณก่อนเพิ่มสินค้า');redirect('seller/shop.php'); }
$editing=null;$variantRows=[];
if($mode==='edit') {
    $id=(int)($_GET['id']??$_POST['product_id']??0);
    if($id) {
        $editing=query('SELECT * FROM products WHERE product_id=? AND shop_id=?',[$id,$shop['shop_id']])->fetch();
        if(!$editing) fail(403,'ไม่พบสินค้าหรือไม่มีสิทธิ์แก้ไข');
        $variantRows=query('SELECT * FROM product_variants WHERE product_id=? ORDER BY variant_id',[$id])->fetchAll();
    }
    if($_SERVER['REQUEST_METHOD']==='POST') {
        $editing=array_merge($editing?:[],$_POST);
        $variantRows=is_array($_POST['variants']??null)?$_POST['variants']:[];
    }
    if(!$variantRows)$variantRows=[['size_label'=>'M','price'=>'','stock_quantity'=>1,'is_active'=>1]];
}
require ROOT.'/app/templates/header.php';require ROOT.'/app/templates/seller-nav.php';
if($error): ?><div class="error" role="alert"><?=h($error)?></div><?php endif;
if($mode==='dashboard'): ?>
<section class="page-heading"><div><h1>Your local shop.</h1><p class="muted">พื้นที่เล็ก ๆ สำหรับเสื้อผ้าที่คุณอยากส่งต่อ</p></div><a class="button" href="<?=h(url($shop?'seller/product-edit.php':'seller/shop.php'))?>"><?=$shop?'+ เพิ่มสินค้า':'เปิดร้านของคุณ'?></a></section>
<?php if($shop): ?><section class="panel"><h2><?=h($shop['shop_name'])?></h2><p><?=h($shop['pickup_location'])?></p><p class="muted"><?=(int)$shop['is_active']?'ร้านเปิดขาย':'ร้านปิดขายชั่วคราว'?></p><div class="actions"><a class="button secondary" href="<?=h(url('seller/products.php'))?>">จัดการสินค้า</a><a class="button secondary" href="<?=h(url('seller/orders.php'))?>">ดูคำสั่งซื้อ</a><a class="underlined" href="<?=h(url('shop.php?id='.$shop['shop_id']))?>">ดูหน้าร้าน</a></div></section><?php else: ?><div class="empty"><h2>เริ่มต้นร้านของคุณบน CLOZEE</h2><p>หนึ่งบัญชีซื้อสินค้าและเปิดร้านได้หนึ่งร้าน</p></div><?php endif;
elseif($mode==='shop'):
$form=($_SERVER['REQUEST_METHOD']==='POST')?$_POST:($shop??['is_active'=>1]); ?>
<section class="page-heading"><div><h1>ข้อมูลร้านของคุณ</h1><p class="muted">นัดรับหรือจัดส่งในพื้นที่ BUU และบางแสน</p></div></section><form method="post" class="stack form-width panel"><?=csrf_field()?><label>ชื่อร้าน<input name="shop_name" required maxlength="150" value="<?=h($form['shop_name']??'')?>"></label><label>แนะนำร้าน<textarea name="description" maxlength="3000"><?=h($form['description']??'')?></textarea></label><label>จุดนัดรับ<input name="pickup_location" required maxlength="255" value="<?=h($form['pickup_location']??'')?>"></label><label class="checkbox"><input type="checkbox" name="is_active" value="1" <?=!empty($form['is_active'])?'checked':''?>>เปิดขายสินค้าในร้าน</label><button class="button" type="submit">บันทึกข้อมูลร้าน</button><p class="field-help">การปิดร้านจะซ่อนสินค้าและหยุดรับคำสั่งซื้อใหม่ ประวัติเดิมยังอยู่</p></form>
<?php elseif($mode==='products'): ?>
<section class="page-heading"><div><h1>สินค้าและสต็อก</h1><p class="muted">จัดการเฉพาะสินค้าของร้านคุณ</p></div><a class="button" href="<?=h(url('seller/product-edit.php'))?>">+ เพิ่มสินค้า</a></section>
<?php if(!$shop): ?><div class="empty"><p>กรุณาสร้างร้านก่อน</p><a class="button" href="<?=h(url('seller/shop.php'))?>">สร้างร้าน</a></div><?php else:
$rows=query('SELECT p.*,MIN(v.price) AS min_price,SUM(v.stock_quantity) AS stock FROM products p LEFT JOIN product_variants v ON v.product_id=p.product_id WHERE p.shop_id=? GROUP BY p.product_id ORDER BY p.product_id DESC',[$shop['shop_id']])->fetchAll(); ?>
<div class="table-wrap"><table><thead><tr><th>สินค้า</th><th>ราคาเริ่มต้น</th><th>สต็อกทุกไซซ์</th><th>สถานะ</th><th>จัดการ</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><div class="table-product"><img src="<?=h(url('assets/images/'.(in_array($row['image_path'],IMAGES,true)?$row['image_path']:'linen.jpg')))?>" alt=""><div><?=h($row['product_name'])?><small><?=h($row['category'])?></small></div></div></td><td><?=money($row['min_price']??'0.00')?></td><td><?=(int)$row['stock']?></td><td><span class="status"><?=(int)$row['is_active']?'เปิดขาย':'ปิดขาย'?></span></td><td><div class="actions"><a class="button secondary" href="<?=h(url('seller/product-edit.php?id='.$row['product_id']))?>">แก้ไข</a><form method="post"><?=csrf_field()?><input type="hidden" name="product_id" value="<?=$row['product_id']?>"><input type="hidden" name="action" value="deactivate"><button class="button secondary" type="submit">ปิดขาย</button></form><form method="post" data-confirm="ลบสินค้าถาวร? ทำได้เฉพาะสินค้าที่ยังไม่มีประวัติคำสั่งซื้อ"><?=csrf_field()?><input type="hidden" name="product_id" value="<?=$row['product_id']?>"><input type="hidden" name="action" value="delete"><button class="button danger" type="submit">ลบถาวร</button></form></div></td></tr><?php endforeach; ?></tbody></table></div><?php if(!$rows): ?><p class="empty">ยังไม่มีสินค้า เริ่มเพิ่มเสื้อผ้าชิ้นแรกของคุณ</p><?php endif;endif; ?>
<?php elseif($mode==='edit'): ?>
<section class="page-heading"><div><h1><?=$id?'แก้ไขสินค้า':'เพิ่มสินค้าใหม่'?></h1><p class="muted">ไซซ์ ราคา และสต็อกแยกต่อแบบสินค้า</p></div></section><form method="post" class="stack" autocomplete="off"><?=csrf_field()?><input type="hidden" name="product_id" value="<?=$id?:''?>"><section class="panel form-grid"><label>ชื่อสินค้า<input name="product_name" required maxlength="180" value="<?=h($editing['product_name']??'')?>"></label><label>หมวดหมู่<select name="category"><?php foreach(CATEGORIES as $c): ?><option <?=($editing['category']??'')===$c?'selected':''?>><?=h($c)?></option><?php endforeach; ?></select></label><label>สภาพสินค้า<select name="condition_type"><option value="secondhand" <?=($editing['condition_type']??'')==='secondhand'?'selected':''?>>Preloved · มือสอง</option><option value="new" <?=($editing['condition_type']??'')==='new'?'selected':''?>>New · ใหม่</option></select></label><label>ภาพหลักสาธิต<select name="image_path"><?php foreach(IMAGES as $image): ?><option <?=($editing['image_path']??'')===$image?'selected':''?>><?=h($image)?></option><?php endforeach; ?></select></label><label class="full">รายละเอียด<textarea name="description" maxlength="5000"><?=h($editing['description']??'')?></textarea></label><label class="checkbox full"><input type="checkbox" name="is_active" value="1" <?=($editing===null||!empty($editing['is_active']))?'checked':''?>>เปิดขายสินค้า</label></section><section class="panel"><h2>ไซซ์และสต็อก</h2><p class="field-help">ไซซ์เดิมเปลี่ยนชื่อไม่ได้ ปิดไซซ์ที่ไม่ขายแทนการลบ หากมีคำสั่งซื้อระหว่างแก้ไข ระบบจะให้โหลดข้อมูลล่าสุด</p><div data-variants><?php foreach($variantRows as $i=>$v): ?><div class="variant-row"><input type="hidden" name="variants[<?=$i?>][variant_id]" value="<?=h($v['variant_id']??'')?>"><input type="hidden" name="variants[<?=$i?>][version]" value="<?=h($v['version']??'')?>"><label>ไซซ์<input name="variants[<?=$i?>][size_label]" maxlength="50" required value="<?=h($v['size_label']??'')?>" <?=!empty($v['variant_id'])?'readonly':''?>></label><label>ราคา (บาท)<input type="number" step="0.01" min="0" max="9999999.99" name="variants[<?=$i?>][price]" value="<?=h($v['price']??'')?>" required></label><label>สต็อก<input type="number" min="0" max="999999" name="variants[<?=$i?>][stock_quantity]" value="<?=h($v['stock_quantity']??0)?>" required></label><label class="checkbox"><input type="checkbox" name="variants[<?=$i?>][is_active]" value="1" <?=!empty($v['is_active'])?'checked':''?>>ขายไซซ์นี้</label></div><?php endforeach; ?></div><button type="button" class="button secondary" data-add-variant>+ เพิ่มไซซ์</button></section><div class="row-actions"><a class="underlined" href="<?=h(url('seller/products.php'))?>">ยกเลิก</a><button class="button" type="submit">บันทึกสินค้า</button></div></form>
<template id="variant-template"><div class="variant-row"><label>ไซซ์<input name="variants[__INDEX__][size_label]" required maxlength="50"></label><label>ราคา (บาท)<input type="number" name="variants[__INDEX__][price]" min="0" max="9999999.99" step="0.01" required></label><label>สต็อก<input type="number" name="variants[__INDEX__][stock_quantity]" min="0" max="999999" value="1" required></label><label class="checkbox"><input type="checkbox" name="variants[__INDEX__][is_active]" checked value="1">ขายไซซ์นี้</label></div></template>
<?php endif;require ROOT.'/app/templates/footer.php'; ?>
