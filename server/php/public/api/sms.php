<?php
require_once __DIR__.'/bootstrap.php';
Auth::requireToken();
$b=parseJsonBody();
$device_id=isset($b['device_id'])?$b['device_id']:null;
$phone=isset($b['phone_number'])?$b['phone_number']:null;
$sender=isset($b['sender'])?$b['sender']:null;
$content=isset($b['content'])?$b['content']:null;
$rt=isset($b['receive_time'])?$b['receive_time']:time();
if(!$device_id||!$phone||!$sender||!$content){
  jsonResponse(['error'=>'missing_fields'],400);
  exit;
}
$enc=Crypto::encrypt($content,env('APP_KEY'));
$receive_at=date('Y-m-d H:i:s',$rt);
$id=null;
try{
  if($pdo){
    $sql="INSERT INTO messages(device_id,sender,content_cipher,content_iv,content_tag,receive_time) VALUES(?,?,?,?,?,?)";
    $stmt=$pdo->prepare($sql);
    $stmt->execute([$device_id,$sender,$enc['cipher'],$enc['iv'],$enc['tag'],$receive_at]);
    $id=$pdo->lastInsertId();
  }
  jsonResponse(['message_id'=>$id?:0]);
}catch(Throwable $e){
  jsonResponse(['error'=>'db_error'],500);
}
