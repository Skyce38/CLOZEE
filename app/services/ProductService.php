<?php
declare(strict_types=1);

function product_data(array $input): array {
    $variants=[]; $seen=[];
    $rows=$input['variants']??[];
    if(!is_array($rows)||count($rows)<1||count($rows)>12) throw new ValidationException('สินค้าต้องมี 1–12 ไซซ์');
    foreach($rows as $row) {
        if(!is_array($row)) throw new ValidationException('ข้อมูลไซซ์ไม่ถูกต้อง');
        $size=mb_strtoupper(text_value($row['size_label']??'',1,50,'ไซซ์'),'UTF-8');
        if(isset($seen[$size])) throw new ValidationException('ไซซ์ซ้ำกัน กรุณารวมเป็นรายการเดียว');
        $seen[$size]=true;
        $variants[]=['id'=>empty($row['variant_id'])?null:positive_id($row['variant_id']), 'size'=>$size,
            'price'=>decimal(cents($row['price']??'')), 'stock'=>integer_value($row['stock_quantity']??'',0,999999,'จำนวนสต็อก'),
            'version'=>empty($row['version'])?0:positive_id($row['version']), 'active'=>isset($row['is_active'])?1:0];
    }
    $image=option_value($input['image_path']??'linen.jpg',IMAGES,'รูปสินค้า');
    return ['name'=>text_value($input['product_name']??'',2,180,'ชื่อสินค้า'),
        'description'=>text_value($input['description']??'',0,5000,'รายละเอียด'),
        'category'=>option_value($input['category']??'',CATEGORIES,'หมวดหมู่'),
        'condition'=>option_value($input['condition_type']??'',['new','secondhand'],'สภาพสินค้า'),
        'brand'=>text_value($input['brand']??'',0,100,'แบรนด์'),
        'chest'=>product_measurement($input['chest_cm']??''),
        'length'=>product_measurement($input['length_cm']??''),
        'condition_notes'=>text_value($input['condition_notes']??'',0,1000,'สภาพและตำหนิ'),
        'image'=>$image,'active'=>isset($input['is_active'])?1:0,'variants'=>$variants];
}
function product_measurement(mixed $value): ?string {
    if($value===null||$value==='')return null;
    if(!is_string($value)&&!is_int($value))throw new ValidationException('ขนาดเสื้อผ้าไม่ถูกต้อง');
    if(!preg_match('/^(?:[1-9][0-9]{0,2}|0)(?:\.[0-9])?$/D',(string)$value)||(float)$value<=0||(float)$value>300)throw new ValidationException('ขนาดเสื้อผ้าต้องมากกว่า 0 และไม่เกิน 300 ซม. ทศนิยมไม่เกิน 1 ตำแหน่ง');
    return (string)$value;
}
function save_product(int $actor, ?int $id, array $input,array $files=[]): int {
    $d=product_data($input);
    $own=own_shop($actor);
    if(!$own||($id&&!query('SELECT product_id FROM {{products}} WHERE product_id=? AND shop_id=?',[$id,$own['shop_id']])->fetchColumn()))throw new ValidationException('ไม่พบสินค้าหรือไม่มีสิทธิ์แก้ไข');
    $photos=stage_product_photos($files);
    $result=transaction(function() use($actor,$id,$d,$input,$photos) {
        $shop=own_shop($actor,true);
        if(!$shop) throw new ValidationException('กรุณาสร้างร้านก่อนเพิ่มสินค้า');
        if($id) {
            $p=query('SELECT * FROM {{products}} WHERE product_id=? AND shop_id=? FOR UPDATE',[$id,$shop['shop_id']])->fetch();
            if(!$p) throw new ValidationException('ไม่พบสินค้าหรือไม่มีสิทธิ์แก้ไข');
            $old=query('SELECT * FROM {{product_variants}} WHERE product_id=? ORDER BY variant_id FOR UPDATE',[$id])->fetchAll();
            $oldById=array_column($old,null,'variant_id'); $kept=[];
            foreach($d['variants'] as $v) {
                if($v['id']) {
                    $existing=$oldById[$v['id']]??null;
                    if(!$existing || isset($kept[$v['id']])) throw new ValidationException('ไซซ์ไม่ได้อยู่ในสินค้านี้');
                    if((int)$existing['version']!==$v['version']) throw new ValidationException('สต็อกหรือข้อมูลเปลี่ยนแล้ว กรุณาโหลดหน้าใหม่ก่อนบันทึก');
                    // Existing size labels are stable; add a new variant instead of swapping unique labels.
                    if($existing['size_label']!==$v['size']) throw new ValidationException('ไซซ์เดิมเปลี่ยนชื่อไม่ได้ กรุณาเพิ่มไซซ์ใหม่และปิดไซซ์เดิม');
                    $kept[$v['id']]=true;
                }
            }
            if(count($kept)!==count($old)) throw new ValidationException('กรุณาเก็บไซซ์เดิมไว้และปิดขายแทนการลบ');
            query('UPDATE {{products}} SET product_name=?,description=?,category=?,condition_type=?,image_path=?,is_active=? WHERE product_id=? AND shop_id=?',[$d['name'],$d['description'],$d['category'],$d['condition'],$d['image'],$d['active'],$id,$shop['shop_id']]);
        } else {
            foreach($d['variants'] as $v) if($v['id']) throw new ValidationException('สินค้าใหม่ต้องใช้ไซซ์ใหม่');
            query('INSERT INTO {{products}}(shop_id,product_name,description,category,condition_type,image_path,is_active) VALUES(?,?,?,?,?,?,?)',[$shop['shop_id'],$d['name'],$d['description'],$d['category'],$d['condition'],$d['image'],$d['active']]);
            $id=(int)db()->lastInsertId();
        }
        foreach($d['variants'] as $v) {
            if($v['id']) query('UPDATE {{product_variants}} SET price=?,stock_quantity=?,is_active=?,version=version+1 WHERE variant_id=? AND product_id=?',[$v['price'],$v['stock'],$v['active'],$v['id'],$id]);
            else query('INSERT INTO {{product_variants}}(product_id,size_label,price,stock_quantity,is_active) VALUES(?,?,?,?,?)',[$id,$v['size'],$v['price'],$v['stock'],$v['active']]);
        }
        query('UPDATE {{products}} SET brand=?,chest_cm=?,length_cm=?,condition_notes=? WHERE product_id=?',[$d['brand']===''?null:$d['brand'],$d['chest'],$d['length'],$d['condition_notes']===''?null:$d['condition_notes'],$id]);
        sync_product_photos($id,$input,$photos);
        return $id;
    });
    return $result;
}
function remove_product(int $actor, int $id, bool $hard): void {
    transaction(function() use($actor,$id,$hard) {
        $shop=own_shop($actor,true);
        if(!$shop) throw new ValidationException('ไม่มีสิทธิ์จัดการร้านนี้');
        $p=query('SELECT product_id FROM {{products}} WHERE product_id=? AND shop_id=? FOR UPDATE',[$id,$shop['shop_id']])->fetch();
        if(!$p) throw new ValidationException('ไม่พบสินค้าหรือไม่มีสิทธิ์จัดการ');
        if(!$hard) { query('UPDATE {{products}} SET is_active=0 WHERE product_id=?',[$id]); return []; }
        query('SELECT variant_id FROM {{product_variants}} WHERE product_id=? ORDER BY variant_id FOR UPDATE',[$id])->fetchAll();
        $used=query('SELECT COUNT(*) FROM {{order_items}} oi JOIN {{product_variants}} v ON v.variant_id=oi.variant_id WHERE v.product_id=?',[$id])->fetchColumn();
        if((int)$used>0) throw new ValidationException('สินค้านี้มีประวัติคำสั่งซื้อ ใช้ปิดขายแทนการลบถาวร');
        query('DELETE FROM {{product_variants}} WHERE product_id=?',[$id]);
        query('DELETE FROM {{products}} WHERE product_id=? AND shop_id=?',[$id,$shop['shop_id']]);
        return [];
    });
}
function catalog(array $filters, int $limit=24, int $offset=0): array {
    $where=['p.is_active=1','s.is_active=1','v.is_active=1']; $params=[];
    if(!empty($filters['condition'])&&in_array($filters['condition'],['new','secondhand'],true)) { $where[]='p.condition_type=?';$params[]=$filters['condition']; }
    if(!empty($filters['category'])) { $where[]='p.category=?';$params[]=substr((string)$filters['category'],0,80); }
    if(!empty($filters['q'])) { $where[]='(p.product_name LIKE ? OR s.shop_name LIKE ?)';$term='%'.mb_substr((string)$filters['q'],0,120).'%';array_push($params,$term,$term); }
    if(!empty($filters['shop_id'])) { $where[]='p.shop_id=?';$params[]=(int)$filters['shop_id']; }
    if(!empty($filters['size'])) { $where[]='v.size_label=?';$params[]=mb_substr((string)$filters['size'],0,50); }
    $sql='SELECT p.*,s.shop_name,(SELECT pi.image_id FROM {{product_images}} pi WHERE pi.product_id=p.product_id ORDER BY pi.sort_order,pi.image_id LIMIT 1) AS cover_image_id,MIN(v.price) AS min_price,SUM(v.stock_quantity) AS total_stock,GROUP_CONCAT(v.size_label ORDER BY v.variant_id SEPARATOR \' · \') AS sizes FROM {{products}} p JOIN {{shops}} s ON s.shop_id=p.shop_id JOIN {{product_variants}} v ON v.product_id=p.product_id WHERE '.implode(' AND ',$where).' GROUP BY p.product_id,s.shop_id ORDER BY p.product_id LIMIT '.max(1,min($limit,100)).' OFFSET '.max(0,$offset);
    return query($sql,$params)->fetchAll();
}
