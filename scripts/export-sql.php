<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit(1);
require __DIR__.'/../app/bootstrap.php';
$source=$argv[1]??'schema.sql';
echo application_sql_file($source);
