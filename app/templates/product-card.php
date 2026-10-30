<article class="product-card"><a class="product-image" href="<?=h(url('product.php?id='.$p['product_id']))?>"><img src="<?=h(url('assets/images/'.(in_array($p['image_path'],IMAGES,true)?$p['image_path']:'linen.jpg')))?>" alt="<?=h($p['product_name'])?>" width="400" height="500" loading="lazy"></a>
<span class="condition"><?=$p['condition_type']==='new'?'New':'Preloved'?></span>
<h2><a href="<?=h(url('product.php?id='.$p['product_id']))?>"><?=h($p['product_name'])?></a></h2><a class="shop-link" href="<?=h(url('shop.php?id='.$p['shop_id']))?>"><?=h($p['shop_name'])?></a>
<p class="price"><?=money($p['min_price'])?></p><p class="muted">Size <?=h($p['sizes'])?><?php if((int)$p['total_stock']===0): ?> · สินค้าหมด<?php endif; ?></p></article>
