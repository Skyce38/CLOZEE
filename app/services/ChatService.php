<?php
declare(strict_types=1);

function conversation_for(int $actor,int $id,bool $lock=false): ?array {
    return query('SELECT c.*,s.shop_name,s.owner_user_id,u.full_name AS buyer_name,p.product_name FROM {{conversations}} c JOIN {{shops}} s ON s.shop_id=c.shop_id JOIN {{users}} u ON u.user_id=c.buyer_user_id LEFT JOIN {{products}} p ON p.product_id=c.product_id WHERE c.conversation_id=? AND (c.buyer_user_id=? OR s.owner_user_id=?)'.($lock?' FOR UPDATE':''),[$id,$actor,$actor])->fetch()?:null;
}
function chat_timestamp(string $utc): string { return str_replace(' ','T',$utc).'Z'; }
function chat_thread_view(int $actor,array $thread): array {
    $isOwner=(int)$thread['owner_user_id']===$actor;
    return ['id'=>(int)$thread['conversation_id'],'name'=>$isOwner?$thread['buyer_name']:$thread['shop_name'],'product'=>$thread['product_name']??'สินค้าไม่ได้แสดงแล้ว'];
}
// Latest activity first. A seller only sees a thread once the buyer has sent a message.
function chat_threads(int $actor): array {
    $rows=query('SELECT c.conversation_id,s.shop_name,s.owner_user_id,u.full_name AS buyer_name,p.product_name,COALESCE(c.last_message_at,c.created_at) AS activity_at,m.message_id,m.sender_user_id,m.message_text FROM {{conversations}} c JOIN {{shops}} s ON s.shop_id=c.shop_id JOIN {{users}} u ON u.user_id=c.buyer_user_id LEFT JOIN {{products}} p ON p.product_id=c.product_id LEFT JOIN {{messages}} m ON m.message_id=(SELECT MAX(x.message_id) FROM {{messages}} x WHERE x.conversation_id=c.conversation_id) WHERE c.buyer_user_id=? OR (s.owner_user_id=? AND c.last_message_at IS NOT NULL) ORDER BY activity_at DESC,COALESCE(m.message_id,0) DESC,c.conversation_id DESC LIMIT 100',[$actor,$actor])->fetchAll();
    return array_map(fn(array $r)=>chat_thread_view($actor,$r)+['snippet'=>$r['message_text']===null?'':mb_substr($r['message_text'],0,60,'UTF-8'),'last_id'=>(int)($r['message_id']??0),'last_mine'=>(int)($r['sender_user_id']??0)===$actor,'at'=>chat_timestamp($r['activity_at'])],$rows);
}
function chat_messages(int $actor,int $conversation,int $after=0): array {
    if(!conversation_for($actor,$conversation))throw new ValidationException('ไม่พบการสนทนาหรือไม่มีสิทธิ์เข้าถึง');
    $rows=array_reverse(query('SELECT message_id,sender_user_id,message_text,created_at FROM {{messages}} WHERE conversation_id=? AND message_id>? ORDER BY message_id DESC LIMIT 100',[$conversation,$after])->fetchAll());
    return array_map(fn(array $m)=>['id'=>(int)$m['message_id'],'text'=>$m['message_text'],'mine'=>(int)$m['sender_user_id']===$actor,'at'=>chat_timestamp($m['created_at'])],$rows);
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
