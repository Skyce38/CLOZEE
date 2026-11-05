<?php $currentUser=user(); $cartCount=array_sum($_SESSION['cart']??[]); ?>
<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=h($title??'เลือกซื้อ')?> · CLOZEE</title><meta name="description" content="เสื้อผ้าใหม่และมือสองสำหรับ BUU และบางแสน นัดรับใกล้คุณ">
<link rel="icon" href="<?=h(url('assets/favicon.svg'))?>" type="image/svg+xml"><link rel="stylesheet" href="<?=h(url('assets/css/app.css'))?>"><script src="<?=h(url('assets/js/app.js'))?>" defer></script></head>
<body><a class="skip" href="#main">ข้ามไปเนื้อหา</a><div class="shell"><header class="site-header">
<a class="brand" href="<?=h(url())?>">CLOZEE</a><nav aria-label="เมนูหลัก">
<a href="<?=h(url('products.php'))?>">เลือกซื้อ</a><a href="<?=h(url('orders.php'))?>">คำสั่งซื้อของฉัน</a><a href="<?=h(url('seller/dashboard.php'))?>">ร้านของฉัน</a>
</nav><div class="account-nav"><?php if($currentUser): ?>
<a href="<?=h(url('cart.php'))?>">ตะกร้า (<?=$cartCount?>)</a><a href="<?=h(url('messages.php'))?>" data-chat-open>ข้อความ</a><span class="user-name"><?=h($currentUser['full_name'])?></span><form method="post" action="<?=h(url('logout.php'))?>"><?=csrf_field()?><button class="text-button" type="submit">ออกจากระบบ</button></form>
<?php else: ?><a class="button compact" href="<?=h(url('login.php'))?>">เข้าสู่ระบบ</a><?php endif; ?></div></header>
<main id="main"><?php if(!empty($_SESSION['flash'])): $flashType=$_SESSION['flash_type']??'success'; ?><div class="toast toast-<?=h($flashType)?>" role="<?=$flashType==='error'?'alert':'status'?>" data-toast><span class="toast-icon" aria-hidden="true"><?=$flashType==='error'?'!':'✓'?></span><p><?=h($_SESSION['flash'])?></p><button type="button" class="toast-close" data-toast-close aria-label="ปิดข้อความแจ้งเตือน">×</button></div><?php unset($_SESSION['flash'],$_SESSION['flash_type']);endif; ?>
