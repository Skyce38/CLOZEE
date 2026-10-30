<?php
declare(strict_types=1);
$actor=(int)require_user()['user_id'];$title='ตะกร้าของคุณ';$error='';$_SESSION['cart']??=[];
if($_SERVER['REQUEST_METHOD']==='POST') {
    post_guard();
    try {
        $action=option_value($_POST['action']??'',['add','update','remove'],'การจัดการตะกร้า');
        $variant=positive_id($_POST['variant_id']??null);
        if($action==='remove') unset($_SESSION['cart'][$variant]);
        else {
            $qty=integer_value($_POST['quantity']??null,1,99,'จำนวน');
            $v=query('SELECT v.*,p.shop_id,p.is_active AS product_active,s.is_active AS shop_active,s.owner_user_id FROM {{product_variants}} v JOIN {{products}} p ON p.product_id=v.product_id JOIN {{shops}} s ON s.shop_id=p.shop_id WHERE v.variant_id=?',[$variant])->fetch();
            if(!$v||!(int)$v['is_active']||!(int)$v['product_active']||!(int)$v['shop_active']) throw new ValidationException('สินค้านี้ไม่เปิดขายแล้ว');
            if((int)$v['owner_user_id']===$actor) throw new ValidationException('ไม่สามารถซื้อสินค้าจากร้านของตนเองได้');
            foreach($_SESSION['cart'] as $other=>$unused) {
                $shopId=query('SELECT p.shop_id FROM {{product_variants}} v JOIN {{products}} p ON p.product_id=v.product_id WHERE v.variant_id=?',[$other])->fetchColumn();
                if($shopId&&(int)$shopId!==(int)$v['shop_id']) throw new ValidationException('ตะกร้ามีสินค้าจากร้านอื่น กรุณาสั่งซื้อหรือลบสินค้าก่อนเปลี่ยนร้าน');
            }
            $new=$action==='add'?($_SESSION['cart'][$variant]??0)+$qty:$qty;
            if($new>99||$new>(int)$v['stock_quantity']) throw new ValidationException('จำนวนที่เลือกมากกว่าสต็อกที่มี');
            if(!isset($_SESSION['cart'][$variant])&&count($_SESSION['cart'])>=20) throw new ValidationException('ตะกร้ารองรับไม่เกิน 20 รายการ');
            $_SESSION['cart'][$variant]=$new;
        }
        unset($_SESSION['checkout_key']);flash('อัปเดตตะกร้าแล้ว');redirect('cart.php');
    } catch(ValidationException $e) { $error=$e->getMessage();http_response_code(422); }
}
$cartRows=[];$subtotal=0;
foreach($_SESSION['cart'] as $id=>$qty) {
    $row=query('SELECT v.*,p.product_name,p.image_path,s.shop_name FROM {{product_variants}} v JOIN {{products}} p ON p.product_id=v.product_id JOIN {{shops}} s ON s.shop_id=p.shop_id WHERE v.variant_id=?',[$id])->fetch();
    if($row) { $row['quantity']=$qty;$row['line_cents']=cents((string)$row['price'])*$qty;$subtotal+=$row['line_cents'];$cartRows[]=$row; }
}
require ROOT.'/app/templates/header.php'; ?>
<section class="page-heading"><div><h1>Your good finds.</h1><p class="muted">หนึ่งร้านต่อหนึ่งคำสั่งซื้อ เลือกนัดรับหรือจัดส่งในขั้นตอนถัดไป</p></div></section>
<?php if($error): ?><div class="error" role="alert"><?=h($error)?></div><?php endif; ?>
<?php if(!$cartRows): ?><section class="empty"><h2>ตะกร้าของคุณยังว่าง</h2><p>เสื้อผ้าตัวโปรดอาจอยู่ใกล้แค่นี้</p><a class="button" href="<?=h(url('products.php'))?>">เลือกเสื้อผ้า</a></section><?php else: ?>
<section class="checkout-layout"><div class="table-wrap"><table><thead><tr><th>สินค้า</th><th>จำนวน</th><th>รวม</th><th>จัดการ</th></tr></thead><tbody><?php foreach($cartRows as $row): ?><tr><td><div class="table-product"><img src="<?=h(url('assets/images/'.(in_array($row['image_path'],IMAGES,true)?$row['image_path']:'linen.jpg')))?>" alt=""><div><?=h($row['product_name'])?><small><?=h($row['shop_name'])?> · <?=h($row['size_label'])?></small><small><?=money($row['price'])?></small></div></div></td><td><form method="post" class="stack"><?=csrf_field()?><input type="hidden" name="action" value="update"><input type="hidden" name="variant_id" value="<?=$row['variant_id']?>"><label><span class="sr-only">จำนวน <?=h($row['product_name'])?></span><input class="cart-qty" name="quantity" type="number" min="1" max="99" value="<?=$row['quantity']?>" required></label><button class="text-button" type="submit">อัปเดต</button></form></td><td><?=money(decimal($row['line_cents']))?></td><td><form method="post"><?=csrf_field()?><input type="hidden" name="variant_id" value="<?=$row['variant_id']?>"><input type="hidden" name="action" value="remove"><button class="text-button" type="submit">ลบ</button></form></td></tr><?php endforeach; ?></tbody></table></div><aside class="summary"><h2>สรุปตะกร้า</h2><div class="summary-line"><span>ยอดสินค้า</span><span><?=money(decimal($subtotal))?></span></div><p class="field-help">ค่าจัดส่งคิดตามวิธีรับสินค้า นัดรับไม่มีค่าจัดส่ง</p><div class="summary-line total"><span>ยอดสินค้า</span><span><?=money(decimal($subtotal))?></span></div><a class="button" href="<?=h(url('checkout.php'))?>">ดำเนินการสั่งซื้อ</a></aside></section><?php endif; ?>
<?php require ROOT.'/app/templates/footer.php'; ?>
