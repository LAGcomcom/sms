<?php
require_once __DIR__.'/bootstrap.php';
Auth::requireToken();
$phone=isset($_GET['phone'])?$_GET['phone']:null;
$from=isset($_GET['from'])?$_GET['from']:null;
$to=isset($_GET['to'])?$_GET['to']:null;
$limit=isset($_GET['limit'])?(int)$_GET['limit']:50;
$detail=isset($_GET['detail'])?$_GET['detail']=='1':false;
if(!$pdo){
  jsonResponse(['data'=>[]]);
  exit;
}
try{
  $conds=[];$params=[];
  if($phone){
    $conds[]='d.phone_number = ?';
    $params[]=$phone;
  }
  if($from){
    $conds[]='m.receive_time >= ?';
    $params[]=$from;
  }
  if($to){
    $conds[]='m.receive_time <= ?';
    $params[]=$to;
  }
  $where=$conds?('WHERE '.implode(' AND ',$conds)):'';
  $sql="SELECT m.message_id,m.device_id,d.phone_number,m.sender,m.content_cipher,m.content_iv,m.content_tag,m.receive_time FROM messages m JOIN devices d ON d.device_id=m.device_id $where ORDER BY m.receive_time DESC LIMIT ?";
  $params[]=$limit;
  $stmt=$pdo->prepare($sql);
  $stmt->execute($params);
  $rows=$stmt->fetchAll();
  if($detail){
    $k=env('APP_KEY');
    foreach($rows as &$r){
      $r['content']=Crypto::decrypt($r['content_cipher'],$r['content_iv'],$r['content_tag'],$k);
    }
  }
  foreach($rows as &$r){
    unset($r['content_cipher'],$r['content_iv'],$r['content_tag']);
  }
  jsonResponse(['data'=>$rows]);
}catch(Throwable $e){
  jsonResponse(['error'=>'db_error','data'=>[]],500);
}
