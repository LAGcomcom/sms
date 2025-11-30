<?php
require_once __DIR__.'/bootstrap.php';
Auth::requireToken();
$id=isset($_GET['id'])?(int)$_GET['id']:0;
if(!$id){ jsonResponse(['error'=>'missing_id'],400); exit; }
if(!$pdo){ jsonResponse(['error'=>'no_db'],500); exit; }
try{
  $stmt=$pdo->prepare("SELECT m.message_id,m.device_id,d.phone_number,m.sender,m.content_cipher,m.content_iv,m.content_tag,m.receive_time FROM messages m JOIN devices d ON d.device_id=m.device_id WHERE m.message_id=? LIMIT 1");
  $stmt->execute([$id]);
  $r=$stmt->fetch();
  if(!$r){ jsonResponse(['error'=>'not_found'],404); exit; }
  $content=Crypto::decrypt($r['content_cipher'],$r['content_iv'],$r['content_tag'],env('APP_KEY'));
  unset($r['content_cipher'],$r['content_iv'],$r['content_tag']);
  $r['content']=$content;
  jsonResponse(['data'=>$r]);
}catch(Throwable $e){
  jsonResponse(['error'=>'db_error'],500);
}
