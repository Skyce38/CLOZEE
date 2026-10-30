<?php
declare(strict_types=1);

function own_shop(int $userId, bool $lock = false): ?array {
    return query('SELECT * FROM shops WHERE owner_user_id=?' . ($lock?' FOR UPDATE':''),[$userId])->fetch() ?: null;
}
function save_shop(int $userId, array $input): int {
    $name=text_value($input['shop_name']??'',2,150,'ชื่อร้าน');
    $description=text_value($input['description']??'',0,3000,'รายละเอียดร้าน');
    $pickup=text_value($input['pickup_location']??'',2,255,'จุดนัดรับ');
    $active=isset($input['is_active'])?1:0;
    return transaction(function() use($userId,$name,$description,$pickup,$active) {
        // Serialize concurrent shop creation for a single owner, even before a shop exists.
        query('SELECT user_id FROM users WHERE user_id=? FOR UPDATE',[$userId]);
        $shop=own_shop($userId,true);
        if($shop) { query('UPDATE shops SET shop_name=?,description=?,pickup_location=?,is_active=? WHERE shop_id=? AND owner_user_id=?',[$name,$description,$pickup,$active,$shop['shop_id'],$userId]); return (int)$shop['shop_id']; }
        query('INSERT INTO shops(owner_user_id,shop_name,description,pickup_location,is_active) VALUES(?,?,?,?,?)',[$userId,$name,$description,$pickup,$active]);
        return (int)db()->lastInsertId();
    });
}
