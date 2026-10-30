<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli') { http_response_code(404);exit; }
require __DIR__.'/../app/bootstrap.php';
if(!in_array('--demo-only', $argv, true)) { fwrite(STDERR,"Usage: php scripts/seed.php --demo-only\nCreates fictional demo accounts; never run on production.\n");exit(1); }
if((int)query('SELECT COUNT(*) FROM {{users}}')->fetchColumn()!==0) { fwrite(STDERR,"Refused: users already exist. No data changed.\n");exit(1); }
db()->exec(application_sql_file('seed.sql'));
$credentials=[];
transaction(function() use (&$credentials) {
    foreach(query('SELECT user_id,email FROM {{users}} ORDER BY user_id')->fetchAll() as $u) {
        $password=bin2hex(random_bytes(10)).'Aa!';
        query('UPDATE {{users}} SET password_hash=? WHERE user_id=?',[password_hash($password,PASSWORD_DEFAULT),$u['user_id']]);
        $credentials[]=['email'=>$u['email'],'password'=>$password];
    }
});
$file=ROOT.'/storage/demo-accounts'.(config()['table_prefix']!==''?'-'.config()['table_prefix']:'').'.json';
file_put_contents($file,json_encode($credentials,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
@chmod($file,0600);
echo "Seeded fictional users, 3 shops, 4 products, 10 variants.\nRandom credentials stored privately under storage (not served or tracked).\n";
