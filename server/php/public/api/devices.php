<?php
require_once __DIR__.'/bootstrap.php';
Auth::requireToken();
if(!$pdo){
  jsonResponse(['data'=>[]]);
  exit;
}
try{
  $stmt=$pdo->query("SELECT device_id,phone_number,last_heartbeat,status FROM devices");
  $rows=$stmt->fetchAll();
  $now=time();
  foreach($rows as &$r){
    $lh=strtotime($r['last_heartbeat']);
    $r['online']=$lh && ($now-$lh)<=30;
  }
  jsonResponse(['data'=>$rows]);
}catch(Throwable $e){
  jsonResponse(['error'=>'db_error','data'=>[]],500);
}
