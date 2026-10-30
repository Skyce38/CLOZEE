<?php
declare(strict_types=1);

function place_order(int $buyer, array $cart, array $input, string $key): int {
    if(!preg_match('/^[a-f0-9]{64}$/D',$key)) throw new ValidationException('รหัส checkout ไม่ถูกต้อง');
    if(!$cart||count($cart)>20) throw new ValidationException('ตะกร้าต้องมี 1–20 รายการ');
    $items=[];
    foreach($cart as $id=>$qty) $items[positive_id($id)]=integer_value($qty,1,99,'จำนวน');
    ksort($items,SORT_NUMERIC);
    $method=option_value($input['fulfillment_method']??'',['pickup','delivery'],'วิธีรับสินค้า');
    $details=text_value($input['fulfillment_details']??'',5,2000,'สถานที่และรายละเอียดรับสินค้า');
    $note=text_value($input['buyer_note']??'',0,1000,'หมายเหตุ');
    $fingerprint=hash('sha256',json_encode([$items,$method,$details,$note],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
    return transaction(function() use($buyer,$items,$method,$details,$note,$key,$fingerprint) {
        // Lock the buyer to serialize retries of the same idempotency key.
        query('SELECT user_id FROM {{users}} WHERE user_id=? FOR UPDATE',[$buyer]);
        $prior=query('SELECT order_id,request_hash FROM {{orders}} WHERE buyer_user_id=? AND checkout_key=?',[$buyer,$key])->fetch();
        if($prior) {
            if(!hash_equals($prior['request_hash'],$fingerprint)) throw new ValidationException('รหัส checkout ถูกใช้กับคำสั่งซื้ออื่นแล้ว');
            return (int)$prior['order_id'];
        }
        $ids=array_keys($items);$marks=implode(',',array_fill(0,count($ids),'?'));
        // Find parent IDs, then lock shop -> products -> variants, consistently with seller edits.
        $parents=query("SELECT v.variant_id,p.product_id,p.shop_id FROM {{product_variants}} v JOIN {{products}} p ON p.product_id=v.product_id WHERE v.variant_id IN ($marks)",$ids)->fetchAll();
        if(count($parents)!==count($ids)) throw new ValidationException('ไม่พบสินค้าบางรายการ');
        $shops=array_unique(array_column($parents,'shop_id'));
        if(count($shops)!==1) throw new ValidationException('หนึ่งคำสั่งซื้อต้องเป็นสินค้าจากร้านเดียวกัน');
        $shop=query('SELECT * FROM {{shops}} WHERE shop_id=? FOR UPDATE',[(int)reset($shops)])->fetch();
        if(!$shop||!(int)$shop['is_active']) throw new ValidationException('ร้านนี้ยังไม่เปิดขาย');
        if((int)$shop['owner_user_id']===$buyer) throw new ValidationException('ไม่สามารถสั่งซื้อสินค้าจากร้านของตนเองได้');
        $productIds=array_unique(array_column($parents,'product_id'));sort($productIds,SORT_NUMERIC);
        $productMap=[];
        foreach($productIds as $pid) {
            $p=query('SELECT * FROM {{products}} WHERE product_id=? FOR UPDATE',[$pid])->fetch();
            if(!$p||!(int)$p['is_active']||(int)$p['shop_id']!==(int)$shop['shop_id']) throw new ValidationException('สินค้าบางรายการปิดขายแล้ว');
            $productMap[$pid]=$p;
        }
        $variants=[];$sum=0;
        foreach($items as $id=>$qty) {
            $v=query('SELECT * FROM {{product_variants}} WHERE variant_id=? FOR UPDATE',[$id])->fetch();
            if(!$v||!(int)$v['is_active']||!isset($productMap[$v['product_id']])||(int)$v['stock_quantity']<$qty) throw new ValidationException('สต็อกไม่เพียงพอ กรุณาตรวจตะกร้าอีกครั้ง');
            $sum+=cents((string)$v['price'])*$qty; $variants[$id]=$v;
        }
        $fee=$method==='delivery'?cents((string)config()['delivery_fee']):0;
        if($sum+$fee>999999999) throw new ValidationException('ยอดคำสั่งซื้อเกินขอบเขตที่ระบบรองรับ');
        query('INSERT INTO {{orders}}(buyer_user_id,shop_id,fulfillment_method,fulfillment_details,shipping_fee,total_amount,buyer_note,checkout_key,request_hash) VALUES(?,?,?,?,?,?,?,?,?)',[$buyer,$shop['shop_id'],$method,$details,decimal($fee),decimal($sum+$fee),$note,$key,$fingerprint]);
        $order=(int)db()->lastInsertId();
        foreach($items as $id=>$qty) {
            $v=$variants[$id];$p=$productMap[$v['product_id']];
            query('INSERT INTO {{order_items}}(order_id,variant_id,product_name_snapshot,size_label_snapshot,quantity,unit_price) VALUES(?,?,?,?,?,?)',[$order,$id,$p['product_name'],$v['size_label'],$qty,$v['price']]);
            $changed=query('UPDATE {{product_variants}} SET stock_quantity=stock_quantity-?,version=version+1 WHERE variant_id=? AND stock_quantity>=?',[$qty,$id,$qty])->rowCount();
            if($changed!==1) throw new ValidationException('สต็อกเปลี่ยนแล้ว กรุณาลองใหม่');
        }
        query('INSERT INTO {{order_status_history}}(order_id,actor_user_id,from_status,to_status) VALUES(?,?,NULL,?)',[$order,$buyer,'pending']);
        return $order;
    });
}
function change_order_status(int $seller, int $id, string $next): void {
    transaction(function() use($seller,$id,$next) {
        $order=query('SELECT o.*,s.owner_user_id FROM {{orders}} o JOIN {{shops}} s ON s.shop_id=o.shop_id WHERE o.order_id=? FOR UPDATE',[$id])->fetch();
        if(!$order||(int)$order['owner_user_id']!==$seller) throw new ValidationException('ไม่พบคำสั่งซื้อหรือไม่มีสิทธิ์จัดการ');
        if(!in_array($next,next_statuses($order['status'],$order['fulfillment_method']),true)) throw new ValidationException('ไม่สามารถเปลี่ยนสถานะตามลำดับนี้ได้');
        if($next==='cancelled') {
            $items=query('SELECT variant_id,quantity FROM {{order_items}} WHERE order_id=? ORDER BY variant_id',[$id])->fetchAll();
            foreach($items as $item) {
                $v=query('SELECT stock_quantity FROM {{product_variants}} WHERE variant_id=? FOR UPDATE',[$item['variant_id']])->fetch();
                if((int)$v['stock_quantity']+(int)$item['quantity']>999999) throw new ValidationException('คืนสต็อกเกินขีดจำกัด กรุณาปรับสต็อกก่อน');
                query('UPDATE {{product_variants}} SET stock_quantity=stock_quantity+?,version=version+1 WHERE variant_id=?',[$item['quantity'],$item['variant_id']]);
            }
        }
        query('UPDATE {{orders}} SET status=? WHERE order_id=?',[$next,$id]);
        query('INSERT INTO {{order_status_history}}(order_id,actor_user_id,from_status,to_status) VALUES(?,?,?,?)',[$id,$seller,$order['status'],$next]);
    });
}
function visible_order(int $actor, int $id): ?array {
    return query('SELECT o.*,s.shop_name,s.owner_user_id,s.pickup_location,u.full_name AS buyer_name FROM {{orders}} o JOIN {{shops}} s ON s.shop_id=o.shop_id JOIN {{users}} u ON u.user_id=o.buyer_user_id WHERE o.order_id=? AND (o.buyer_user_id=? OR s.owner_user_id=?)',[$id,$actor,$actor])->fetch() ?: null;
}
