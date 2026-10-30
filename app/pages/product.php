<?php
declare(strict_types=1);
$id=filter_var($_GET['id']??null,FILTER_VALIDATE_INT);if(!$id) fail(404,'ไม่พบสินค้า');
$p=query('SELECT p.*,s.shop_name,s.owner_user_id,s.pickup_location FROM products p JOIN shops s ON s.shop_id=p.shop_id WHERE p.product_id=? AND p.is_active=1 AND s.is_active=1',[$id])->fetch();
if(!$p) fail(404,'ไม่พบสินค้าหรือสินค้าปิดขายแล้ว');
$variants=query('SELECT * FROM product_variants WHERE product_id=? AND is_active=1 ORDER BY variant_id',[$id])->fetchAll();
$title=$p['product_name'];require ROOT.'/app/templates/header.php'; ?>
<a class="back-link" href="<?=h(url('products.php'))?>">← Explore</a><section class="detail-layout"><div class="detail-photo"><img src="<?=h(url('assets/images/'.(in_array($p['image_path'],IMAGES,true)?$p['image_path']:'linen.jpg')))?>" alt="<?=h($p['product_name'])?>"></div><div class="detail-copy"><span class="condition"><?=$p['condition_type']==='new'?'New':'Preloved'?></span><h1><?=h($p['product_name'])?></h1><a class="underlined" href="<?=h(url('shop.php?id='.$p['shop_id']))?>"><?=h($p['shop_name'])?></a><p class="description"><?=nl2br(h($p['description']))?></p>
<form action="<?=h(url('cart.php'))?>" method="post" class="stack"><?=csrf_field()?><input type="hidden" name="action" value="add"><label>เลือกไซซ์และราคา<select name="variant_id" required><option value="">เลือกไซซ์</option><?php foreach($variants as $v): ?><option value="<?=$v['variant_id']?>" <?=(int)$v['stock_quantity']===0?'disabled':''?>><?=h($v['size_label'])?> — <?=money($v['price'])?> · คงเหลือ <?=$v['stock_quantity']?></option><?php endforeach; ?></select></label><label>จำนวน<input type="number" name="quantity" value="1" min="1" max="99" required></label><button class="button" type="submit">เพิ่มลงตะกร้า</button></form>
<form method="post" action="<?=h(url('messages.php'))?>" class="chat-start"><?=csrf_field()?><input type="hidden" name="action" value="start"><input type="hidden" name="product_id" value="<?=$p['product_id']?>"><button class="button secondary" type="submit">สอบถามไซซ์กับร้าน</button></form>
<div class="delivery-note"><h2>ใกล้กัน รับง่าย</h2><p>นัดรับ: <?=h($p['pickup_location'])?></p><p>จัดส่งในพื้นที่บางแสน <?=money(config()['delivery_fee'])?> / คำสั่งซื้อ</p><p class="muted">ชำระเงินกับร้านโดยตรง ระบบนี้บันทึกคำสั่งซื้อและสถานะเท่านั้น</p></div></div></section>
<?php require ROOT.'/app/templates/footer.php'; ?>
