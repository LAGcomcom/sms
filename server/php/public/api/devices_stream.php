<?php
require_once __DIR__.'/bootstrap.php';
$claims=Auth::requireToken();
ignore_user_abort(true);
set_time_limit(0);
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');
@ob_end_flush();
@ob_implicit_flush(1);
function push($event,$data){
  echo "event: $event\n";
  echo 'data: '.json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n\n";
}
while(true){
  try{
    if($pdo){
      $stmt=$pdo->query("SELECT device_id,phone_number,last_heartbeat,status FROM devices");
      $rows=$stmt->fetchAll();
      $now=time();
      foreach($rows as &$r){
        $lh=strtotime($r['last_heartbeat']);
        $r['online']=$lh && ($now-$lh)<=30;
      }
      push('devices',$rows);
    }else{
      push('devices',[]);
    }
  }catch(Throwable $e){ push('error','db_error'); }
  echo ": heartbeat\n\n";
  @ob_flush();
  @flush();
  sleep(5);
}
