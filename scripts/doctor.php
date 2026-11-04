<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli') { http_response_code(404);exit; }
require __DIR__.'/../app/bootstrap.php';
$ok=version_compare(PHP_VERSION,'8.1.0','>=');
echo 'PHP '.PHP_VERSION.($ok?' OK':' FAIL').PHP_EOL;
foreach(['pdo_mysql','mbstring','session','fileinfo','zlib'] as $ext) { $loaded=extension_loaded($ext);$ok=$ok&&$loaded;echo $ext.($loaded?' OK':' MISSING').PHP_EOL; }
echo 'Image upload limit '.number_format(photo_byte_limit()/1048576,1).' MiB per image; total '.number_format(photo_request_limit()/1048576,1).' MiB'.PHP_EOL;
echo 'Images use database BLOB storage; private upload directory and GD are not required.'.PHP_EOL;
$packet=(int)query('SELECT @@max_allowed_packet')->fetchColumn();$ok=$ok&&$packet>=2097152;
echo 'Database packet size '.$packet.' bytes (at least 2097152 required)'.PHP_EOL;
try { echo 'Database '.query('SELECT VERSION()')->fetchColumn().PHP_EOL;echo 'Application tables '.count(application_tables()).PHP_EOL; }
catch(Throwable $e) { echo "Database connection FAIL (check private configuration)\n";$ok=false; }
echo 'Secure cookies '.(config()['secure_cookies']?'ON':'OFF — local HTTP only').PHP_EOL;
exit($ok?0:1);
