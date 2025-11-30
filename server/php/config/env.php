<?php
function load_env($path){
  $env=[];
  if(file_exists($path)){
    foreach(file($path, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line){
      if(strpos(ltrim($line),'#')===0) continue;
      $pos=strpos($line,'=');
      if($pos===false) continue;
      $key=trim(substr($line,0,$pos));
      $val=trim(substr($line,$pos+1));
      $env[$key]=$val;
    }
  }
  return $env;
}
$_ENV_ARRAY=load_env(__DIR__.'/../.env');
function env($key,$default=null){
  global $_ENV_ARRAY;
  return array_key_exists($key,$_ENV_ARRAY)?$_ENV_ARRAY[$key]:$default;
}
