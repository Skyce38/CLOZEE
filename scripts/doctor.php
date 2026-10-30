<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli') { http_response_code(404);exit; }
require __DIR__.'/../app/bootstrap.php';
$ok=version_compare(PHP_VERSION,'8.1.0','>=');
echo 'PHP '.PHP_VERSION.($ok?' OK':' FAIL').PHP_EOL;
foreach(['pdo_mysql','mbstring','session'] as $ext) { $loaded=extension_loaded($ext);$ok=$ok&&$loaded;echo $ext.($loaded?' OK':' MISSING').PHP_EOL; }
try { echo 'Database '.query('SELECT VERSION()')->fetchColumn().PHP_EOL;echo 'Tables '.count(query('SHOW TABLES')->fetchAll()).PHP_EOL; }
catch(Throwable $e) { echo "Database connection FAIL (check private configuration)\n";$ok=false; }
echo 'Secure cookies '.(config()['secure_cookies']?'ON':'OFF — local HTTP only').PHP_EOL;
exit($ok?0:1);
