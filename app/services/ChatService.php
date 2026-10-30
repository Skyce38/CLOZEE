<?php
declare(strict_types=1);

function conversation_for(int $actor,int $id,bool $lock=false): ?array {
    return query('SELECT c.*,s.shop_name,s.owner_user_id,u.full_name AS buyer_name,p.product_name FROM {{conversations}} c JOIN {{shops}} s ON s.shop_id=c.shop_id JOIN {{users}} u ON u.user_id=c.buyer_user_id LEFT JOIN {{products}} p ON p.product_id=c.product_id WHERE c.conversation_id=? AND (c.buyer_user_id=? OR s.owner_user_id=?)'.($lock?' FOR UPDATE':''),[$id,$actor,$actor])->fetch()?:null;
}
function start_conversation(int $buyer,int $product): int {
    return transaction(function()use($buyer,$product){
        query('SELECT user_id FROM {{users}} WHERE user_id=? FOR UPDATE',[$buyer]);
        $p=query('SELECT p.product_id,p.shop_id,s.owner_user_id FROM {{products}} p JOIN {{shops}} s ON s.shop_id=p.shop_id WHERE p.product_id=? AND p.is_active=1 AND s.is_active=1',[$product])->fetch();
        if(!$p||(int)$p['owner_user_id']===$buyer)throw new ValidationException('ไม่สามารถเริ่มสนทนาสำหรับสินค้านี้ได้');
        $prior=query('SELECT conversation_id FROM {{conversations}} WHERE buyer_user_id=? AND shop_id=? AND product_id=? ORDER BY conversation_id LIMIT 1',[$buyer,$p['shop_id'],$product])->fetchColumn();
        if($prior)return (int)$prior;
        query('INSERT INTO {{conversations}}(buyer_user_id,shop_id,product_id) VALUES(?,?,?)',[$buyer,$p['shop_id'],$product]);
        return (int)db()->lastInsertId();
    });
}
function send_message(int $actor,int $conversation,mixed $message): void {
    $text=text_value($message,1,2000,'ข้อความ');
    transaction(function()use($actor,$conversation,$text){
        if(!conversation_for($actor,$conversation,true))throw new ValidationException('ไม่พบการสนทนาหรือไม่มีสิทธิ์เข้าถึง');
        query('INSERT INTO {{messages}}(conversation_id,sender_user_id,message_text) VALUES(?,?,?)',[$conversation,$actor,$text]);
        query('UPDATE {{conversations}} SET last_message_at=UTC_TIMESTAMP() WHERE conversation_id=?',[$conversation]);
    });
}
