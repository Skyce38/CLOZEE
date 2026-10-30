<?php $currentUser=user(); $cartCount=array_sum($_SESSION['cart']??[]); ?>
<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=h($title??'Explore')?> · CLOZEE</title><meta name="description" content="เสื้อผ้าใหม่และมือสองสำหรับ BUU และบางแสน นัดรับใกล้คุณ">
<link rel="icon" href="<?=h(url('assets/favicon.svg'))?>" type="image/svg+xml"><link rel="stylesheet" href="<?=h(url('assets/css/app.css'))?>"><script src="<?=h(url('assets/js/app.js'))?>" defer></script></head>
<body><a class="skip" href="#main">ข้ามไปเนื้อหา</a><div class="shell"><header class="site-header">
<a class="brand" href="<?=h(url())?>">CLOZEE</a><nav aria-label="เมนูหลัก">
<a href="<?=h(url('products.php'))?>">Explore</a><a href="<?=h(url('orders.php'))?>">My orders</a><a href="<?=h(url('seller/dashboard.php'))?>">Sell with us</a>
</nav><div class="account-nav"><?php if($currentUser): ?>
<a href="<?=h(url('cart.php'))?>">ตะกร้า (<?=$cartCount?>)</a><a href="<?=h(url('messages.php'))?>">ข้อความ</a><span class="user-name"><?=h($currentUser['full_name'])?></span><form method="post" action="<?=h(url('logout.php'))?>"><?=csrf_field()?><button class="text-button" type="submit">ออกจากระบบ</button></form>
<?php else: ?><a class="button compact" href="<?=h(url('login.php'))?>">Log in</a><?php endif; ?></div></header>
<main id="main"><?php if(!empty($_SESSION['flash'])): ?><div class="notice" role="status"><?=h($_SESSION['flash'])?></div><?php unset($_SESSION['flash']);endif; ?>
