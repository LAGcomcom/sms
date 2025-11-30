<?php
require_once __DIR__.'/bootstrap.php';
Auth::requireToken();
$b=parseJsonBody();
$device_id=isset($b['device_id'])?$b['device_id']:null;
$phone=isset($b['phone_number'])?$b['phone_number']:null;
$ts=isset($b['timestamp'])?$b['timestamp']:time();
if(!$device_id||!$phone){
  jsonResponse(['error'=>'missing_fields'],400);
  exit;
}
$now=date('Y-m-d H:i:s',$ts);
try{
  if($pdo){
    $sql="INSERT INTO devices(device_id,phone_number,last_heartbeat,status) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE phone_number=VALUES(phone_number), last_heartbeat=VALUES(last_heartbeat), status=VALUES(status)";
    $stmt=$pdo->prepare($sql);
    $stmt->execute([$device_id,$phone,$now,'online']);
  }
  jsonResponse(['status'=>'ok','server_time'=>date('c')]);
}catch(Throwable $e){
  jsonResponse(['error'=>'db_error'],500);
}
