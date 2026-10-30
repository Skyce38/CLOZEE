<?php
declare(strict_types=1);

final class ValidationException extends RuntimeException {}

function text_value(mixed $value, int $min, int $max, string $label): string
{
    if (!is_string($value)) throw new ValidationException($label . 'ไม่ถูกต้อง');
    $value = trim($value);
    $length = mb_strlen($value, 'UTF-8');
    if ($length < $min || $length > $max || str_contains($value, "\0")) throw new ValidationException($label . "ต้องมี {$min}–{$max} ตัวอักษร");
    return $value;
}
function positive_id(mixed $value): int {
    $n = filter_var($value, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
    if ($n === false) throw new ValidationException('รหัสข้อมูลไม่ถูกต้อง');
    return $n;
}
function integer_value(mixed $value, int $min, int $max, string $label): int {
    $n = filter_var($value, FILTER_VALIDATE_INT, ['options'=>['min_range'=>$min,'max_range'=>$max]]);
    if ($n === false) throw new ValidationException($label . 'ไม่ถูกต้อง');
    return $n;
}
function cents(mixed $value): int {
    if (!is_string($value) && !is_int($value)) throw new ValidationException('ราคาไม่ถูกต้อง');
    if (!preg_match('/^(0|[1-9][0-9]{0,6})(?:\.([0-9]{1,2}))?$/D', (string)$value, $m)) throw new ValidationException('ราคาต้องไม่ติดลบและมีทศนิยมไม่เกิน 2 หลัก');
    return (int)$m[1]*100 + (int)str_pad($m[2]??'', 2, '0');
}
function decimal(int $cents): string { return intdiv($cents,100) . '.' . str_pad((string)($cents%100),2,'0',STR_PAD_LEFT); }
function money(mixed $decimal): string { return number_format(cents((string)$decimal)/100, 2) . ' ฿'; }
function option_value(mixed $value, array $allowed, string $label): string {
    if (!is_string($value) || !in_array($value,$allowed,true)) throw new ValidationException($label . 'ไม่ถูกต้อง');
    return $value;
}
const CATEGORIES = ['Shirts','Denim','T-shirts','Knitwear','Dresses','Trousers','Other'];
const IMAGES = ['linen.jpg','denim.jpg','tee.jpg','knit.jpg'];
const STATUS_LABELS = ['pending'=>'รอยืนยัน','confirmed'=>'ยืนยันแล้ว','preparing'=>'กำลังเตรียม','ready_for_pickup'=>'พร้อมนัดรับ','out_for_delivery'=>'กำลังจัดส่ง','completed'=>'สำเร็จ','cancelled'=>'ยกเลิก'];
function next_statuses(string $status, string $method): array {
    $map = ['pending'=>['confirmed','cancelled'],'confirmed'=>['preparing','cancelled'],
            'preparing'=>[$method === 'pickup' ? 'ready_for_pickup' : 'out_for_delivery','cancelled'],
            'ready_for_pickup'=>['completed'],'out_for_delivery'=>['completed'],'completed'=>[],'cancelled'=>[]];
    return $map[$status] ?? [];
}
