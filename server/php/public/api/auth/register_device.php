<?php
require_once __DIR__.'/../../bootstrap.php';
$b=parseJsonBody();
$device_id=isset($b['device_id'])?$b['device_id']:null;
$phone=isset($b['phone_number'])?$b['phone_number']:null;
if(!$device_id||!$phone){ jsonResponse(['error'=>'missing_fields'],400); exit; }
$now=time();
$exp=$now+3600;
$payload=['sub'=>$device_id,'phone'=>$phone,'iat'=>$now,'exp'=>$exp];
$token=JWT::encode($payload,env('APP_KEY'));
try{
  if($pdo){
    $stmt=$pdo->prepare("INSERT INTO devices(device_id,phone_number,last_heartbeat,status) VALUES(?,?,?,?) ON CONFLICT(device_id) DO UPDATE SET phone_number=excluded.phone_number");
    // MySQL兼容：如果是MySQL，这条会失败；因此容错处理
    try{$stmt->execute([$device_id,$phone,date('Y-m-d H:i:s',$now),'offline']);}
    catch(Throwable $e){
      $stmt=$pdo->prepare("INSERT INTO devices(device_id,phone_number,last_heartbeat,status) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE phone_number=VALUES(phone_number)");
      $stmt->execute([$device_id,$phone,date('Y-m-d H:i:s',$now),'offline']);
    }
  }
}catch(Throwable $e){ }
jsonResponse(['token'=>$token,'expires_in'=>3600]);
