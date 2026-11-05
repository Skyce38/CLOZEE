<div class="chat-widget" data-chat-widget data-api="<?=h(url('chat-api.php'))?>" data-csrf="<?=h(csrf_token())?>" data-user="<?=(int)user()['user_id']?>" hidden>
<section class="chat-panel" data-chat-panel role="dialog" aria-label="แชท" hidden>
<header class="chat-panel-head"><button type="button" class="chat-icon-button" data-chat-back aria-label="กลับไปรายการแชท" hidden>←</button><div class="chat-panel-title"><strong data-chat-title>แชท</strong><small data-chat-subtitle></small></div><button type="button" class="chat-icon-button" data-chat-close aria-label="ปิดแชท">×</button></header>
<div class="chat-view" data-chat-list-view>
<label class="chat-sort">เรียงแชท<select data-chat-sort><option value="newest">แชทใหม่สุด</option><option value="oldest">แชทเก่าสุด</option></select></label>
<ul class="chat-threads" data-chat-threads></ul>
<p class="chat-empty" data-chat-empty hidden>ยังไม่มีแชท เริ่มได้จากปุ่มสอบถามไซซ์ในหน้าสินค้า</p>
</div>
<div class="chat-view" data-chat-thread-view hidden>
<div class="chat-log" data-chat-log aria-label="ข้อความในการสนทนา" aria-live="polite"></div>
<p class="chat-error" data-chat-error role="alert" hidden></p>
<form class="chat-form" data-chat-form><label class="sr-only" for="chat-input">ข้อความถึงคู่สนทนา</label><textarea id="chat-input" data-chat-input maxlength="2000" rows="2" required placeholder="พิมพ์ข้อความ…"></textarea><button class="button" type="submit">ส่งข้อความ</button></form>
<p class="field-help chat-hint">อย่าส่งรหัสผ่านหรือข้อมูลการชำระเงิน</p>
</div>
</section>
<button type="button" class="chat-fab" data-chat-toggle aria-label="เปิดแชท" aria-expanded="false"><svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8 8 0 0 1-8 8H4l2.2-3.3A8 8 0 1 1 21 12z"/></svg><span class="chat-badge" data-chat-badge hidden></span></button>
</div>
