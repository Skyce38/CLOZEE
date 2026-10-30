<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit(1);
require __DIR__.'/../app/bootstrap.php';
$sql=application_sql_file('migrations/001-product-photos.sql');
if(in_array('--print',$argv,true)){echo $sql;exit;}
if(!in_array('--apply',$argv,true)){fwrite(STDERR,"Usage: php scripts/migrate-product-photos.php --print OR --apply\n");exit(1);}
$columns=query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME IN ('brand','chest_cm','length_cm','condition_notes')",[config()['db_name'],table_name('products')])->fetchAll(PDO::FETCH_COLUMN);
$imageTable=in_array(table_name('product_images'),application_tables(),true);
if(count($columns)===4&&$imageTable){echo "Product photos migration already applied. No changes.\n";exit;}
if($columns||$imageTable){fwrite(STDERR,"Partial migration detected. Stop and review the schema; no automatic repair attempted.\n");exit(1);}
foreach(['products','orders'] as $name)$before[$name]=(int)query('SELECT COUNT(*) FROM {{'.$name.'}}')->fetchColumn();
db()->exec($sql);
foreach($before as $name=>$count)if((int)query('SELECT COUNT(*) FROM {{'.$name.'}}')->fetchColumn()!==$count)throw new RuntimeException('Row count changed; review migration.');
echo "Added product details and image table. Product/order row counts preserved.\n";
