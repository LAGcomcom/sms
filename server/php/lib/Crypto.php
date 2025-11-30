<?php
require_once __DIR__.'/../config/env.php';
class Crypto{
  public static function encrypt($plaintext,$key){
    if(!$key) return ['cipher'=>null,'iv'=>null,'tag'=>null];
    $iv=random_bytes(12);
    $tag='';
    $cipher=openssl_encrypt($plaintext,'aes-256-gcm',$key,OPENSSL_RAW_DATA,$iv,$tag);
    return [
      'cipher'=>base64_encode($cipher),
      'iv'=>base64_encode($iv),
      'tag'=>base64_encode($tag)
    ];
  }
  public static function decrypt($cipher_b64,$iv_b64,$tag_b64,$key){
    if(!$key||!$cipher_b64||!$iv_b64||!$tag_b64) return null;
    $cipher=base64_decode($cipher_b64);
    $iv=base64_decode($iv_b64);
    $tag=base64_decode($tag_b64);
    return openssl_decrypt($cipher,'aes-256-gcm',$key,OPENSSL_RAW_DATA,$iv,$tag);
  }
}
