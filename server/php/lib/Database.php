<?php
require_once __DIR__.'/../config/env.php';
class Database{
  public static function getPdo(){
    $host=env('DB_HOST');
    $port=env('DB_PORT','3306');
    $db=env('DB_NAME');
    $user=env('DB_USER');
    $pass=env('DB_PASSWORD');
    if($host&&$db&&$user&&$pass){
      try{
        $dsn="mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
        $pdo=new PDO($dsn,$user,$pass,[
          PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES=>false
        ]);
        return $pdo;
      }catch(Throwable $e){
      }
    }
    $dir=__DIR__.'/../runtime';
    if(!is_dir($dir)) @mkdir($dir,0777,true);
    $path=$dir.'/app.sqlite';
    $pdo=new PDO('sqlite:'.$path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
    self::initSqlite($pdo);
    return $pdo;
  }
  private static function initSqlite($pdo){
    $pdo->exec("CREATE TABLE IF NOT EXISTS devices (
      device_id TEXT PRIMARY KEY,
      phone_number TEXT UNIQUE,
      last_heartbeat TEXT,
      status TEXT
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
      message_id INTEGER PRIMARY KEY AUTOINCREMENT,
      device_id TEXT NOT NULL,
      sender TEXT NOT NULL,
      content_cipher TEXT NOT NULL,
      content_iv TEXT NOT NULL,
      content_tag TEXT NOT NULL,
      receive_time TEXT NOT NULL
    )");
  }
}
