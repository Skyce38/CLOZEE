<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit(1);
require __DIR__.'/../app/bootstrap.php';
$columns=query('SELECT TABLE_NAME,COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE,COLUMN_DEFAULT,COLUMN_KEY,EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME,ORDINAL_POSITION')->fetchAll();
$foreign=query('SELECT TABLE_NAME,COLUMN_NAME,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL ORDER BY TABLE_NAME,COLUMN_NAME')->fetchAll();
$unique=query("SELECT TABLE_NAME,INDEX_NAME,GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS COLUMNS_LIST FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND NON_UNIQUE=0 GROUP BY TABLE_NAME,INDEX_NAME ORDER BY TABLE_NAME,INDEX_NAME")->fetchAll();
$owned=array_flip(array_map('table_name',CLOZEE_TABLES));
foreach(['columns','foreign','unique'] as $set)$$set=array_values(array_filter($$set,fn(array $row):bool=>isset($owned[$row['TABLE_NAME']])));
file_put_contents(ROOT.'/storage/schema-metadata.json',json_encode(['columns'=>$columns,'foreign_keys'=>$foreign,'unique_keys'=>$unique],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
$inner=query('SELECT o.order_id,u.full_name AS buyer,s.shop_name,i.product_name_snapshot,i.quantity,i.unit_price FROM {{orders}} o INNER JOIN {{users}} u ON u.user_id=o.buyer_user_id INNER JOIN {{shops}} s ON s.shop_id=o.shop_id INNER JOIN {{order_items}} i ON i.order_id=o.order_id ORDER BY o.order_id,i.order_item_id LIMIT 12')->fetchAll();
$left=query('SELECT s.shop_id,s.shop_name,COUNT(o.order_id) AS order_count FROM {{shops}} s LEFT JOIN {{orders}} o ON o.shop_id=s.shop_id GROUP BY s.shop_id,s.shop_name ORDER BY s.shop_id LIMIT 20')->fetchAll();
file_put_contents(ROOT.'/storage/join-results.json',json_encode(['executed_at'=>gmdate('c'),'database_version'=>query('SELECT VERSION()')->fetchColumn(),'inner_join'=>$inner,'left_join'=>$left],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo count($columns).' columns and '.count($foreign).' foreign keys introspected; JOIN examples executed. No secrets exported.'.PHP_EOL;
