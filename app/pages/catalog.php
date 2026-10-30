<?php
declare(strict_types=1);
$title='Explore';
$page=max(1,min(10000,(int)($_GET['page']??1)));
$filters=[];
foreach(['q','condition','category','size'] as $field) $filters[$field]=is_string($_GET[$field]??null)?mb_substr($_GET[$field],0,120):'';
$rows=catalog($filters,25,($page-1)*24);$more=count($rows)>24;$rows=array_slice($rows,0,24);
require ROOT.'/app/templates/header.php';
?>
<section class="editorial"><div><h1>Good clothes.<br>Closer to you<span>.</span></h1><p>New &amp; preloved finds around BUU and Bangsaen.</p></div><img src="<?=h(url('assets/images/hero.jpg'))?>" alt="เสื้อเชิ้ตลินินและยีนส์ โทนธรรมชาติ" width="900" height="600" fetchpriority="high"></section>
<form class="filters" method="get" action="<?=h(url('products.php'))?>" aria-label="ค้นหาเสื้อผ้า">
<label class="search"><span class="sr-only">ค้นหาเสื้อผ้าหรือร้าน</span><input type="search" name="q" value="<?=h($filters['q'])?>" placeholder="Search for clothes, shops, or keywords…" maxlength="120"></label>
<fieldset class="condition-options"><legend class="sr-only">สภาพสินค้า</legend><?php foreach([''=>'All','secondhand'=>'Preloved','new'=>'New'] as $v=>$label): ?><label><input type="radio" name="condition" value="<?=h($v)?>" <?=$filters['condition']===$v?'checked':''?>><span><?=h($label)?></span></label><?php endforeach; ?></fieldset>
<label><span class="sr-only">หมวดหมู่</span><select name="category"><option value="">Category</option><?php foreach(CATEGORIES as $c): ?><option <?=$filters['category']===$c?'selected':''?>><?=h($c)?></option><?php endforeach; ?></select></label>
<label><span class="sr-only">ไซซ์</span><select name="size"><option value="">Size</option><?php foreach(['XS','S','M','L','XL','XXL','FREE'] as $size): ?><option <?=$filters['size']===$size?'selected':''?>><?=h($size)?></option><?php endforeach; ?></select></label><button type="submit" class="button filter-submit">ค้นหา</button>
</form>
<section aria-label="รายการเสื้อผ้า" class="product-grid"><?php foreach($rows as $p) require ROOT.'/app/templates/product-card.php'; ?></section>
<?php if(!$rows): ?><div class="empty"><h2>ยังไม่พบเสื้อผ้าที่ค้นหา</h2><p>ลองเปลี่ยนคำค้นหรือเลือกเงื่อนไขอื่น</p><a class="button secondary" href="<?=h(url('products.php'))?>">ดูทั้งหมด</a></div><?php endif; ?>
<?php if($page>1||$more): ?><nav class="pagination" aria-label="หน้ารายการ"><?php if($page>1): ?><a class="button secondary" href="<?=h(url('products.php?'.http_build_query(array_merge($filters,['page'=>$page-1]))))?>">ก่อนหน้า</a><?php endif; ?><span>หน้า <?=$page?></span><?php if($more): ?><a class="button secondary" href="<?=h(url('products.php?'.http_build_query(array_merge($filters,['page'=>$page+1]))))?>">ถัดไป</a><?php endif; ?></nav><?php endif; ?>
<?php require ROOT.'/app/templates/footer.php'; ?>
