<?php
declare(strict_types=1);

// Logical names stay stable; a private configuration can isolate this app in a shared DB.
const CLOZEE_TABLES = ['users','shops','products','product_variants','orders','order_items',
    'conversations','messages','order_status_history','login_attempts','product_images'];

function table_name(string $logical): string {
    if(!in_array($logical,CLOZEE_TABLES,true)) throw new LogicException('Unknown application table');
    $prefix=config()['table_prefix'];
    if(!is_string($prefix)||($prefix!==''&&!preg_match('/^[a-z][a-z0-9_]{0,23}$/D',$prefix)))
        throw new LogicException('Invalid table prefix configuration');
    return $prefix.$logical;
}

function resolve_sql(string $sql): string {
    return preg_replace_callback('/\{\{([a-z_]+)\}\}/',fn(array $m):string=>'`'.table_name($m[1]).'`',$sql);
}

function application_tables(): array {
    $names=array_map(fn(string $name):string=>table_name($name),CLOZEE_TABLES);
    return query('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('.implode(',',array_fill(0,count($names),'?')).') ORDER BY TABLE_NAME',$names)->fetchAll(PDO::FETCH_COLUMN);
}

function application_sql_file(string $file): string {
    if(!in_array($file,['schema.sql','seed.sql','demo-joins.sql','migrations/001-product-photos.sql','migrations/002-database-product-photos.sql'],true))throw new LogicException('Unknown SQL source');
    $sql=file_get_contents(ROOT.'/database/'.$file);
    // SQL sources are trusted repository files. Rewrite explicit table declarations/references,
    // never bound data values. Runtime queries use explicit {{logical_name}} slots instead.
    $names=implode('|',array_map(fn(string $s):string=>preg_quote($s,'/'),CLOZEE_TABLES));
    $sql=preg_replace_callback('/\b(CREATE\s+TABLE|ALTER\s+TABLE|REFERENCES|INSERT\s+INTO|FROM|JOIN)\s+('.$names.')\b/i',
        fn(array $m):string=>$m[1].' `'.table_name(strtolower($m[2])).'`',$sql);
    $prefix=config()['table_prefix'];
    if($prefix!=='') {
        $sql=preg_replace_callback('/\bCONSTRAINT\s+([a-zA-Z0-9_]+)\b/',
            fn(array $m):string=>'CONSTRAINT `'.$prefix.$m[1].'`',$sql);
        $sql=str_replace('Import ONLY into a new, dedicated empty database.','Import ONLY into an approved database with this application namespace unused.',$sql);
        $sql=str_replace('Select/create a dedicated database in phpMyAdmin first, then import this file.','Select the approved assigned database in phpMyAdmin; preserve all unrelated tables.',$sql);
        $sql="-- Generated application table prefix: $prefix\n".$sql;
    }
    return $sql;
}
