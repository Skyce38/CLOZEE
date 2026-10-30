<?php
declare(strict_types=1);
$register=($authMode??'login')==='register';$title=$register?'สมัครสมาชิก':'เข้าสู่ระบบ';$error='';
if(user()) redirect('products.php');
if($_SERVER['REQUEST_METHOD']==='POST') {
    post_guard();
    try {
        if($register) $row=register_user($_POST);
        else {
            $email=text_value($_POST['email']??'',3,190,'อีเมล');
            $password=$_POST['password']??'';
            if(!is_string($password)||strlen($password)>1024) throw new ValidationException('อีเมลหรือรหัสผ่านไม่ถูกต้อง');
            $row=authenticate($email,$password,$_SERVER['REMOTE_ADDR']??'unknown');
        }
        login_user($row);flash($register?'ยินดีต้อนรับสู่ CLOZEE':'เข้าสู่ระบบแล้ว');redirect('products.php');
    } catch(ValidationException $e) { $error=$e->getMessage();http_response_code(422); }
}
require ROOT.'/app/templates/header.php'; ?>
<section class="auth-layout"><div class="auth-art"><img src="<?=h(url('assets/images/hero.jpg'))?>" alt="เสื้อผ้าตัวโปรดสำหรับทุกวัน"><div><h1>Your next<br>favorite find.</h1><p>เสื้อผ้าที่ใช่ ใกล้กว่าที่คิด</p></div></div><div class="auth-form"><a class="back-link" href="<?=h(url())?>">← กลับหน้าร้าน</a><h2><?=$register?'เริ่มต้นตู้เสื้อผ้าใหม่':'ยินดีต้อนรับกลับมา'?></h2><p class="muted"><?=$register?'ซื้อหรือเปิดร้านได้ในบัญชีเดียว':'เข้าสู่ระบบเพื่อซื้อสินค้าและจัดการร้านของคุณ'?></p>
<?php if($error): ?><div class="error" role="alert"><?=h($error)?></div><?php endif; ?>
<form method="post" class="stack"><?=csrf_field()?>
<?php if($register): ?><label>ชื่อที่แสดง<input name="full_name" autocomplete="name" minlength="2" maxlength="120" required value="<?=h(is_string($_POST['full_name']??null)?$_POST['full_name']:'')?>"></label><?php endif; ?>
<label>อีเมล<input type="email" name="email" autocomplete="email" maxlength="190" required value="<?=h(is_string($_POST['email']??null)?$_POST['email']:'')?>"></label>
<label>รหัสผ่าน<input type="password" name="password" autocomplete="<?=$register?'new-password':'current-password'?>" required <?=$register?'minlength="10" maxlength="72"':''?>></label>
<?php if($register): ?><p class="field-help">อย่างน้อย 10 ตัวอักษร และไม่เกิน 72 ไบต์ ไม่มีระบบชำระเงินออนไลน์ในรุ่นนี้</p><label>ยืนยันรหัสผ่าน<input type="password" name="password_confirmation" autocomplete="new-password" required></label><?php endif; ?>
<button class="button" type="submit"><?=$register?'สมัครสมาชิก':'เข้าสู่ระบบ'?></button></form>
<p><?=$register?'มีบัญชีอยู่แล้ว?':'ยังไม่มีบัญชี?'?> <a class="underlined" href="<?=h(url($register?'login.php':'register.php'))?>"><?=$register?'เข้าสู่ระบบ':'สมัครสมาชิก'?></a></p></div></section>
<?php require ROOT.'/app/templates/footer.php'; ?>
