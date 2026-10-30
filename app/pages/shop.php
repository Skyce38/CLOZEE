<?php
declare(strict_types=1);
$id=(int)($_GET['id']??0);$shop=query('SELECT shop_id,shop_name,description,pickup_location FROM {{shops}} WHERE shop_id=? AND is_active=1',[$id])->fetch();
if(!$shop) fail(404,'ไม่พบร้าน');
$title=$shop['shop_name'];$products=catalog(['shop_id'=>$id],100);require ROOT.'/app/templates/header.php'; ?>
<section class="page-heading"><div><p class="muted">Local wardrobe · BUU / Bangsaen</p><h1><?=h($shop['shop_name'])?></h1><p><?=nl2br(h($shop['description']))?></p><p>จุดนัดรับ: <?=h($shop['pickup_location'])?></p></div></section><section class="product-grid"><?php foreach($products as $p) require ROOT.'/app/templates/product-card.php'; ?></section><?php if(!$products): ?><p class="empty">ร้านนี้ยังไม่มีสินค้าที่เปิดขาย</p><?php endif; ?>
<?php require ROOT.'/app/templates/footer.php'; ?>
