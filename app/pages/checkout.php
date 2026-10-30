<?php
declare(strict_types=1);
$actor=(int)require_user()['user_id'];$title='ยืนยันคำสั่งซื้อ';$error='';
if($_SERVER['REQUEST_METHOD']==='POST') {
    post_guard();
    try {
        $key=is_string($_POST['checkout_key']??null)?$_POST['checkout_key']:'';
        $cart=$_SESSION['checkout_attempts'][$key]??null;
        if(!is_array($cart)) throw new ValidationException('checkout หมดอายุ กรุณากลับไปที่ตะกร้า');
        $order=place_order($actor,$cart,$_POST,$key);
        if(($_SESSION['cart']??[])===$cart) $_SESSION['cart']=[];
        unset($_SESSION['checkout_key']);flash('บันทึกคำสั่งซื้อแล้ว กรุณาติดต่อร้านเพื่อชำระเงินและนัดรับ');redirect('order.php?id='.$order);
    } catch(ValidationException $e) { $error=$e->getMessage();http_response_code(422); }
}
if(empty($_SESSION['cart'])) { if($error)flash($error);redirect('cart.php'); }
$key=$_SESSION['checkout_key']??=bin2hex(random_bytes(32));
$_SESSION['checkout_attempts']??=[];$_SESSION['checkout_attempts'][$key]=$_SESSION['cart'];
while(count($_SESSION['checkout_attempts'])>5)array_shift($_SESSION['checkout_attempts']);
$rows=[];$sum=0;$shopName='';
foreach($_SESSION['cart'] as $id=>$qty) {
    $row=query('SELECT v.*,p.product_name,s.shop_name,s.pickup_location FROM product_variants v JOIN products p ON p.product_id=v.product_id JOIN shops s ON s.shop_id=p.shop_id WHERE v.variant_id=?',[$id])->fetch();
    if(!$row) { flash('สินค้าบางรายการไม่มีแล้ว กรุณาปรับตะกร้า');redirect('cart.php'); }
    $row['quantity']=$qty;$sum+=cents((string)$row['price'])*$qty;$rows[]=$row;$shopName=$row['shop_name'];
}
require ROOT.'/app/templates/header.php'; ?>
<section class="page-heading"><div><h1>ใกล้กัน รับง่าย.</h1><p class="muted">คำสั่งซื้อจาก <?=h($shopName)?> · ไม่มีการชำระเงินออนไลน์</p></div></section>
<?php if($error): ?><div class="error" role="alert"><?=h($error)?></div><?php endif; ?>
<section class="checkout-layout"><form method="post" class="stack panel"><?=csrf_field()?><input type="hidden" name="checkout_key" value="<?=h($key)?>"><label>วิธีรับสินค้า<select name="fulfillment_method"><option value="pickup" <?=($_POST['fulfillment_method']??'')==='pickup'?'selected':''?>>นัดรับ — ไม่มีค่าจัดส่ง</option><option value="delivery" <?=($_POST['fulfillment_method']??'')==='delivery'?'selected':''?>>จัดส่งในบางแสน — <?=money(config()['delivery_fee'])?></option></select></label><label>สถานที่รับสินค้า / ที่อยู่และข้อมูลติดต่อ<textarea name="fulfillment_details" required minlength="5" maxlength="2000" placeholder="ระบุจุดนัดรับหรือที่อยู่ พร้อมช่องทางติดต่อที่ต้องการให้ร้านใช้"><?=h(is_string($_POST['fulfillment_details']??null)?$_POST['fulfillment_details']:'')?></textarea></label><p class="field-help">จุดนัดรับของร้าน: <?=h($rows[0]['pickup_location'])?> ข้อมูลนี้เห็นเฉพาะคุณและเจ้าของร้าน</p><label>หมายเหตุถึงร้าน<textarea name="buyer_note" maxlength="1000"><?=h(is_string($_POST['buyer_note']??null)?$_POST['buyer_note']:'')?></textarea></label><button class="button" type="submit">ยืนยันคำสั่งซื้อ</button><p class="field-help">ระบบจะตรวจราคาและสต็อกล่าสุดอีกครั้ง การนัดรับและจัดส่งดำเนินการโดยร้านโดยตรง</p></form><aside class="summary"><h2>เสื้อผ้าที่เลือก</h2><?php foreach($rows as $row): ?><div class="summary-line"><span><?=h($row['product_name'])?> <small><?=h($row['size_label'])?> × <?=$row['quantity']?></small></span><span><?=money(decimal(cents((string)$row['price'])*$row['quantity']))?></span></div><?php endforeach; ?><div class="summary-line total"><span>ยอดสินค้า</span><span><?=money(decimal($sum))?></span></div><p class="field-help">นัดรับรวม <?=money(decimal($sum))?><br>จัดส่งรวม <?=money(decimal($sum+cents((string)config()['delivery_fee'])))?></p></aside></section>
<?php require ROOT.'/app/templates/footer.php'; ?>
