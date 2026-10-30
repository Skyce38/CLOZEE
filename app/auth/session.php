<?php
declare(strict_types=1);

function start_session(): void {
    ini_set('session.use_strict_mode','1'); ini_set('session.use_only_cookies','1');
    session_name('clozee_session');
    session_set_cookie_params(['lifetime'=>0,'path'=>url(),'secure'=>(bool)config()['secure_cookies'],'httponly'=>true,'samesite'=>'Lax']);
    session_start();
    if (isset($_SESSION['last_active']) && time() - $_SESSION['last_active'] > 1800) {
        $_SESSION = []; session_regenerate_id(true);
    }
    $_SESSION['last_active'] = time();
}
function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function user(): ?array { return $_SESSION['user'] ?? null; }
function require_user(): array { if (!user()) redirect('login.php'); return user(); }
function login_user(array $row): void {
    session_regenerate_id(true); $_SESSION = ['user'=>['user_id'=>(int)$row['user_id'],'full_name'=>$row['full_name']], 'last_active'=>time(), 'csrf'=>bin2hex(random_bytes(32))];
}
function logout_user(): void {
    $_SESSION=[]; $p=session_get_cookie_params();
    setcookie(session_name(), '', ['expires'=>time()-3600,'path'=>$p['path'],'secure'=>$p['secure'],'httponly'=>true,'samesite'=>'Lax']);
    session_destroy();
}
function register_user(array $data): array {
    $name=text_value($data['full_name']??'',2,120,'ชื่อ');
    $email=mb_strtolower(text_value($data['email']??'',3,190,'อีเมล'),'UTF-8');
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new ValidationException('รูปแบบอีเมลไม่ถูกต้อง');
    $password=$data['password']??'';
    if (!is_string($password)||strlen($password)<10||strlen($password)>72||str_contains($password,"\0")) throw new ValidationException('รหัสผ่านต้องมี 10–72 ไบต์');
    if ($password!==($data['password_confirmation']??'')) throw new ValidationException('รหัสผ่านทั้งสองช่องไม่ตรงกัน');
    try { query('INSERT INTO users(full_name,email,password_hash) VALUES(?,?,?)',[$name,$email,password_hash($password,PASSWORD_DEFAULT)]); }
    catch (PDOException $e) { if (($e->errorInfo[1]??0)===1062) throw new ValidationException('ไม่สามารถสมัครด้วยอีเมลนี้ได้'); throw $e; }
    return ['user_id'=>(int)db()->lastInsertId(),'full_name'=>$name];
}
function authenticate(string $email, string $password, string $ip): array {
    $email=mb_strtolower(trim($email),'UTF-8');
    $bucket=hash('sha256',$ip . '|' . $email);
    $ipBucket=hash('sha256',$ip);
    // Persistent per-account/IP and per-IP limits; no raw IP or password stored.
    $count=query('SELECT COUNT(*) FROM login_attempts WHERE (bucket=? OR ip_bucket=?) AND attempted_at > UTC_TIMESTAMP() - INTERVAL 15 MINUTE',[$bucket,$ipBucket])->fetchColumn();
    $accountCount=query('SELECT COUNT(*) FROM login_attempts WHERE bucket=? AND attempted_at > UTC_TIMESTAMP() - INTERVAL 15 MINUTE',[$bucket])->fetchColumn();
    if ((int)$accountCount>=5 || (int)$count>=40) throw new ValidationException('ลองเข้าสู่ระบบหลายครั้งเกินไป กรุณารอ 15 นาที');
    $row=query('SELECT user_id,full_name,password_hash FROM users WHERE email=?',[$email])->fetch();
    $dummy='$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
    if (!password_verify($password,$row['password_hash']??$dummy)||!$row) {
        query('INSERT INTO login_attempts(bucket,ip_bucket) VALUES(?,?)',[$bucket,$ipBucket]);
        throw new ValidationException('อีเมลหรือรหัสผ่านไม่ถูกต้อง');
    }
    query('DELETE FROM login_attempts WHERE bucket=?',[$bucket]);
    query('DELETE FROM login_attempts WHERE attempted_at < UTC_TIMESTAMP() - INTERVAL 1 DAY');
    if(password_needs_rehash($row['password_hash'],PASSWORD_DEFAULT)) query('UPDATE users SET password_hash=? WHERE user_id=?',[password_hash($password,PASSWORD_DEFAULT),$row['user_id']]);
    return $row;
}
