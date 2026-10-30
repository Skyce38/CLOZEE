<?php
declare(strict_types=1);
$actor=(int)require_user()['user_id'];$id=(int)($_GET['id']??$_POST['order_id']??0);$error='';$o=visible_order($actor,$id);
if(!$o) fail(404,'ไม่พบคำสั่งซื้อหรือไม่มีสิทธิ์เข้าถึง');
if($_SERVER['REQUEST_METHOD']==='POST') {
    post_guard();
    try { change_order_status($actor,$id,text_value($_POST['status']??'',1,30,'สถานะ'));flash('อัปเดตสถานะแล้ว');redirect('order.php?id='.$id); }
    catch(ValidationException $e) { $error=$e->getMessage();http_response_code(422); }
    $o=visible_order($actor,$id);
}
$items=query('SELECT * FROM {{order_items}} WHERE order_id=? ORDER BY order_item_id',[$id])->fetchAll();
$history=query('SELECT * FROM {{order_status_history}} WHERE order_id=? ORDER BY history_id',[$id])->fetchAll();
$title='คำสั่งซื้อ #'.$id;require ROOT.'/app/templates/header.php'; ?>
<section class="page-heading"><div><a class="back-link" href="<?=h(url((int)$o['owner_user_id']===$actor?'seller/orders.php':'orders.php'))?>">← คำสั่งซื้อทั้งหมด</a><h1>Order #<?=$id?></h1><p><?=h($o['shop_name'])?> · <span class="status <?=h($o['status'])?>"><?=h(STATUS_LABELS[$o['status']])?></span></p></div></section>
<?php if($error): ?><div class="error" role="alert"><?=h($error)?></div><?php endif; ?>
<section class="checkout-layout"><div><div class="table-wrap"><table><thead><tr><th>สินค้า ณ วันที่สั่ง</th><th>จำนวน</th><th>ราคาต่อชิ้น</th><th>รวม</th></tr></thead><tbody><?php foreach($items as $item): ?><tr><td><?=h($item['product_name_snapshot'])?><small>Size <?=h($item['size_label_snapshot'])?></small></td><td><?=$item['quantity']?></td><td><?=money($item['unit_price'])?></td><td><?=money(decimal(cents((string)$item['unit_price'])*(int)$item['quantity']))?></td></tr><?php endforeach; ?></tbody></table></div><section class="panel"><h2><?=$o['fulfillment_method']==='pickup'?'รายละเอียดนัดรับ':'รายละเอียดจัดส่ง'?></h2><p>ผู้ซื้อ: <?=h($o['buyer_name'])?></p><p><?=nl2br(h($o['fulfillment_details']))?></p><?php if($o['buyer_note']): ?><p>หมายเหตุ: <?=nl2br(h($o['buyer_note']))?></p><?php endif; ?><p class="field-help">ไม่มีการชำระเงินออนไลน์ กรุณาตกลงการชำระเงินกับร้านโดยตรง</p></section>
<?php if((int)$o['owner_user_id']===$actor&&next_statuses($o['status'],$o['fulfillment_method'])): ?><form method="post" class="stack panel"><?=csrf_field()?><input type="hidden" name="order_id" value="<?=$id?>"><h2>อัปเดตสถานะโดยร้าน</h2><label>สถานะถัดไป<select name="status"><?php foreach(next_statuses($o['status'],$o['fulfillment_method']) as $next): ?><option value="<?=h($next)?>"><?=h(STATUS_LABELS[$next])?></option><?php endforeach; ?></select></label><p class="field-help">ยกเลิกได้ก่อนพร้อมรับหรือออกจัดส่งเท่านั้น ระบบคืนสต็อกเมื่อยกเลิกหนึ่งครั้ง</p><button class="button" type="submit">บันทึกสถานะ</button></form><?php endif; ?></div>
<aside class="summary"><h2>สรุปคำสั่งซื้อ</h2><div class="summary-line"><span>ยอดสินค้า</span><span><?=money(decimal(cents((string)$o['total_amount'])-cents((string)$o['shipping_fee'])))?></span></div><div class="summary-line"><span>ค่าจัดส่ง</span><span><?=money($o['shipping_fee'])?></span></div><div class="summary-line total"><span>รวม</span><span><?=money($o['total_amount'])?></span></div><h2>ประวัติสถานะ</h2><ol class="order-timeline"><?php foreach($history as $entry): ?><li><?=h(STATUS_LABELS[$entry['to_status']]??$entry['to_status'])?><small><?=h($entry['created_at'])?> UTC</small></li><?php endforeach; ?></ol></aside></section>
<?php require ROOT.'/app/templates/footer.php'; ?>
