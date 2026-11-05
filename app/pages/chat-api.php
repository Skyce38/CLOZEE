<?php
declare(strict_types=1);
function chat_json(int $code,array $data): never {
    http_response_code($code);header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);exit;
}
$current=user();
if(!$current)chat_json(401,['error'=>'กรุณาเข้าสู่ระบบ']);
$actor=(int)$current['user_id'];
$method=$_SERVER['REQUEST_METHOD'];
try{
    if($method==='GET'){
        session_write_close();
        $action=(string)($_GET['action']??'');
        if($action==='threads')chat_json(200,['threads'=>chat_threads($actor)]);
        if($action==='messages'){
            $id=positive_id($_GET['id']??null);
            $thread=conversation_for($actor,$id);
            if(!$thread)chat_json(404,['error'=>'ไม่พบการสนทนาหรือไม่มีสิทธิ์เข้าถึง']);
            $after=filter_var($_GET['after']??0,FILTER_VALIDATE_INT,['options'=>['min_range'=>0,'default'=>0]]);
            chat_json(200,['thread'=>chat_thread_view($actor,$thread),'messages'=>chat_messages($actor,$id,$after)]);
        }
        chat_json(422,['error'=>'คำขอไม่ถูกต้อง']);
    }
    if($method!=='POST')chat_json(405,['error'=>'ไม่รองรับคำขอนี้']);
    if(!hash_equals(csrf_token(),(string)($_POST['csrf']??'')))chat_json(403,['error'=>'แบบฟอร์มหมดอายุ กรุณาโหลดหน้าใหม่']);
    $_SESSION['last_active']=time();
    session_write_close();
    $action=option_value($_POST['action']??'',['start','send'],'การสนทนา');
    if($action==='start')chat_json(200,['conversation_id'=>start_conversation($actor,positive_id($_POST['product_id']??null))]);
    $id=positive_id($_POST['conversation_id']??null);
    send_message($actor,$id,$_POST['message_text']??'');
    chat_json(200,['ok'=>true]);
}catch(ValidationException $e){
    chat_json(422,['error'=>$e->getMessage()]);
}catch(Throwable $e){
    error_log('CLOZEE chat: '.get_class($e).': '.$e->getMessage());
    chat_json(500,['error'=>'ระบบแชทขัดข้อง กรุณาลองอีกครั้ง']);
}
