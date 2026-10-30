<?php
// Copy to local.php OUTSIDE public/. Never commit local.php.
return [
    'db_host' => '127.0.0.1',
    'db_port' => 3306,
    'db_name' => 'clozee',
    'db_user' => 'clozee',
    'db_pass' => '', // Configure privately; environment variables override this file.
    'table_prefix' => '', // Set clozee_ to coexist with existing course/lab tables.
    'base_path' => '', // e.g. /~username/clozee ; no trailing slash
    'secure_cookies' => true, // false ONLY on local HTTP development
    'delivery_fee' => '30.00',
];
