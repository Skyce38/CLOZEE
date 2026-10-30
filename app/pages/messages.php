<?php
declare(strict_types=1);
$actor=(int)require_user()['user_id'];$title='ข้อความ';$error='';$id=(int)($_GET['id']??0);
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_guard();
    try{
        $action=option_value($_POST['action']??'',['start','send'],'การสนทนา');
        if($action==='start')$id=start_conversation($actor,positive_id($_POST['product_id']??null));
        else send_message($actor,positive_id($_POST['conversation_id']??null),$_POST['message_text']??'');
        redirect('messages.php?id='.($action==='start'?$id:positive_id($_POST['conversation_id'])));
    }catch(ValidationException $e){$error=$e->getMessage();http_response_code(422);}
}
$thread=$id?conversation_for($actor,$id):null;if($id&&!$thread)fail(404,'ไม่พบการสนทนาหรือไม่มีสิทธิ์เข้าถึง');
$threads=query('SELECT c.*,s.shop_name,u.full_name AS buyer_name,s.owner_user_id FROM conversations c JOIN shops s ON s.shop_id=c.shop_id JOIN users u ON u.user_id=c.buyer_user_id WHERE c.buyer_user_id=? OR s.owner_user_id=? ORDER BY COALESCE(c.last_message_at,c.created_at) DESC,c.conversation_id DESC LIMIT 100',[$actor,$actor])->fetchAll();
$messages=$thread?array_reverse(query('SELECT * FROM messages WHERE conversation_id=? ORDER BY message_id DESC LIMIT 100',[$id])->fetchAll()):[];
require ROOT.'/app/templates/header.php'; ?>
<section class="page-heading"><div><h1>A little conversation.</h1><p class="muted">ถามไซซ์หรือสภาพเสื้อผ้ากับร้านโดยตรง ข้อความเห็นเฉพาะผู้ร่วมสนทนา</p></div></section>
<?php if($error):?><div class="error" role="alert"><?=h($error)?></div><?php endif;?>
<section class="chat-layout"><aside class="thread-list" aria-label="การสนทนาของคุณ"><?php foreach($threads as $t):?><a href="<?=h(url('messages.php?id='.$t['conversation_id']))?>"><strong><?=h((int)$t['owner_user_id']===$actor?$t['buyer_name']:$t['shop_name'])?></strong><small class="muted"> · #<?=$t['conversation_id']?></small></a><?php endforeach;?><?php if(!$threads):?><p class="muted">เริ่มการสนทนาได้จากหน้าสินค้า</p><?php endif;?></aside><div><?php if($thread):?><h2><?=h((int)$thread['owner_user_id']===$actor?$thread['buyer_name']:$thread['shop_name'])?></h2><p class="muted"><?=h($thread['product_name']??'สินค้าเดิมไม่ได้แสดงแล้ว')?></p><p class="field-help">แสดง 100 ข้อความล่าสุด กด <a class="underlined" href="<?=h(url('messages.php?id='.$id))?>">รีเฟรช</a> เพื่อดูข้อความใหม่ · เวลา UTC</p><div class="chat-messages" aria-label="ข้อความในการสนทนา"><?php foreach($messages as $m):?><div class="message <?=(int)$m['sender_user_id']===$actor?'mine':''?>"><?=h($m['message_text'])?><small><?=(int)$m['sender_user_id']===$actor?'คุณ':'คู่สนทนา'?> · <?=h($m['created_at'])?> UTC</small></div><?php endforeach;?><?php if(!$messages):?><p class="muted">ยังไม่มีข้อความ ลองทักถามร้านเรื่องไซซ์ได้เลย</p><?php endif;?></div><form class="stack chat-compose" method="post"><?=csrf_field()?><input type="hidden" name="action" value="send"><input type="hidden" name="conversation_id" value="<?=$id?>"><label>ข้อความถึงคู่สนทนา<textarea name="message_text" maxlength="2000" required></textarea></label><button class="button" type="submit">ส่งข้อความ</button><p class="field-help">อย่าส่งรหัสผ่านหรือข้อมูลการชำระเงิน แชทนี้รองรับข้อความล้วน</p></form><?php else:?><div class="empty"><h2>เลือกการสนทนา</h2><p>หรือเปิดหน้าสินค้าเพื่อสอบถามผู้ขาย</p><a class="button secondary" href="<?=h(url('products.php'))?>">Explore</a></div><?php endif;?></div></section>
<?php require ROOT.'/app/templates/footer.php'; ?>
