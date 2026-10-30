<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli') { http_response_code(404);exit; }
require __DIR__.'/../app/bootstrap.php';
$shared=in_array('--empty-namespace',$argv,true);
if(!$shared&&!in_array('--empty-database',$argv,true)) { fwrite(STDERR,"Usage: php scripts/migrate.php --empty-database OR --empty-namespace\nShared mode requires a nonempty table_prefix and an unused namespace.\n");exit(1); }
$tables=query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
if($shared) {
    $prefix=config()['table_prefix'];table_name('users'); // Validate before inspecting or creating.
    if($prefix==='') { fwrite(STDERR,"Refused: empty-namespace requires a nonempty table prefix.\n");exit(1); }
    if(array_filter($tables,fn(string $name):bool=>str_starts_with($name,$prefix))) { fwrite(STDERR,"Refused: application namespace already exists. No changes made.\n");exit(1); }
}elseif($tables) { fwrite(STDERR,"Refused: configured database is not empty. No changes made.\n");exit(1); }
db()->exec(application_sql_file('schema.sql'));
echo "Schema imported. Application tables: ".count(application_tables())."; other tables preserved.\n";
