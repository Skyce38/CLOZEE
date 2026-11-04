<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit(1);
require __DIR__.'/../app/bootstrap.php';
if(!in_array(config()['db_host'],['127.0.0.1','localhost'],true)||!preg_match('/^clozee_(local|test|demo)/D',config()['db_name']))exit(1);
echo query('SELECT COUNT(*) FROM {{product_images}}')->fetchColumn();
