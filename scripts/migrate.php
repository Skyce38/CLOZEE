<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli') { http_response_code(404);exit; }
require __DIR__.'/../app/bootstrap.php';
if(!in_array('--empty-database', $argv, true)) { fwrite(STDERR,"Usage: php scripts/migrate.php --empty-database\nUses configured database; refuses any existing table.\n");exit(1); }
$tables=query('SHOW TABLES')->fetchAll();
if($tables) { fwrite(STDERR,"Refused: configured database is not empty. No changes made.\n");exit(1); }
db()->exec(file_get_contents(ROOT.'/database/schema.sql'));
echo "Schema imported into empty database. Tables: ".count(query('SHOW TABLES')->fetchAll())."\n";
