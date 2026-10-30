<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit(1);
require __DIR__.'/../app/bootstrap.php';
if(!str_contains(config()['db_name'],'test'))exit(2);
try {
    $id=place_order((int)$argv[1],[(int)$argv[2]=>1],['fulfillment_method'=>'pickup','fulfillment_details'=>'Fictional test pickup point'], $argv[3]);
    echo json_encode(['ok'=>true,'id'=>$id]);
}catch(ValidationException $e){echo json_encode(['ok'=>false,'error'=>'validation']);}
