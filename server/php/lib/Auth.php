<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/JWT.php';
class Auth{
  public static function requireToken(){
    $hdr=isset($_SERVER['HTTP_AUTHORIZATION'])?$_SERVER['HTTP_AUTHORIZATION']:'';
    $token=null;
    if(stripos($hdr,'Bearer ')===0){
      $token=trim(substr($hdr,7));
    }
    $expected=env('APP_TOKEN');
    $key=env('APP_KEY');
    $ok=false; $claims=null;
    if($expected && $token===$expected){
      $ok=true;
    }elseif($key){
      $claims=JWT::decode($token,$key);
      $ok=$claims!==null;
    }
    if(!$ok){
      http_response_code(401);
      header('Content-Type: application/json');
      echo json_encode(['error'=>'unauthorized']);
      exit;
    }
    return $claims;
  }
}
