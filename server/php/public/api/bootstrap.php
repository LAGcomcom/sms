<?php
date_default_timezone_set('UTC');
header('Content-Type: application/json');
require_once __DIR__.'/../../lib/Database.php';
require_once __DIR__.'/../../lib/Crypto.php';
require_once __DIR__.'/../../lib/Auth.php';
$pdo=Database::getPdo();
function jsonResponse($data,$code=200){
  http_response_code($code);
  echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}
function parseJsonBody(){
  $raw=file_get_contents('php://input');
  $d=json_decode($raw,true);
  return is_array($d)?$d:[];
}
