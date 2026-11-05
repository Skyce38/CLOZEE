<?php
declare(strict_types=1);

function own_shop(int $userId, bool $lock = false): ?array {
    return query('SELECT * FROM {{shops}} WHERE owner_user_id=?' . ($lock?' FOR UPDATE':''),[$userId])->fetch() ?: null;
}
// Permanently removes a shop without order history, including its products, images and chats.
function delete_shop(int $userId, mixed $confirmName): string {
    $typed=text_value($confirmName,1,150,'ชื่อร้านเพื่อยืนยัน');
    return transaction(function() use($userId,$typed) {
        query('SELECT user_id FROM {{users}} WHERE user_id=? FOR UPDATE',[$userId]);
        $shop=own_shop($userId,true);
        if(!$shop) throw new ValidationException('ไม่พบร้านหรือไม่มีสิทธิ์จัดการร้านนี้');
        if($typed!==$shop['shop_name']) throw new ValidationException('ชื่อร้านที่พิมพ์ไม่ตรงกัน ระบบยังไม่ได้ลบร้าน');
        $id=(int)$shop['shop_id'];
        $orders=(int)query('SELECT COUNT(*) FROM {{orders}} WHERE shop_id=?',[$id])->fetchColumn();
        if($orders>0) throw new ValidationException('ร้านนี้มีประวัติคำสั่งซื้อ จึงลบถาวรไม่ได้ เพื่อเก็บประวัติของผู้ซื้อ หากไม่ขายแล้วให้เอาเครื่องหมาย “เปิดขายสินค้าในร้าน” ออก แล้วกดบันทึก');
        query('DELETE m FROM {{messages}} m JOIN {{conversations}} c ON c.conversation_id=m.conversation_id WHERE c.shop_id=?',[$id]);
        query('DELETE FROM {{conversations}} WHERE shop_id=?',[$id]);
        query('DELETE v FROM {{product_variants}} v JOIN {{products}} p ON p.product_id=v.product_id WHERE p.shop_id=?',[$id]);
        query('DELETE FROM {{products}} WHERE shop_id=?',[$id]);
        query('DELETE FROM {{shops}} WHERE shop_id=? AND owner_user_id=?',[$id,$userId]);
        return (string)$shop['shop_name'];
    });
}
function save_shop(int $userId, array $input): int {
    $name=text_value($input['shop_name']??'',2,150,'ชื่อร้าน');
    $description=text_value($input['description']??'',0,3000,'รายละเอียดร้าน');
    $pickup=text_value($input['pickup_location']??'',2,255,'จุดนัดรับ');
    $active=isset($input['is_active'])?1:0;
    return transaction(function() use($userId,$name,$description,$pickup,$active) {
        // Serialize concurrent shop creation for a single owner, even before a shop exists.
        query('SELECT user_id FROM {{users}} WHERE user_id=? FOR UPDATE',[$userId]);
        $shop=own_shop($userId,true);
        if($shop) { query('UPDATE {{shops}} SET shop_name=?,description=?,pickup_location=?,is_active=? WHERE shop_id=? AND owner_user_id=?',[$name,$description,$pickup,$active,$shop['shop_id'],$userId]); return (int)$shop['shop_id']; }
        query('INSERT INTO {{shops}}(owner_user_id,shop_name,description,pickup_location,is_active) VALUES(?,?,?,?,?)',[$userId,$name,$description,$pickup,$active]);
        return (int)db()->lastInsertId();
    });
}
