<?php
class JWT{
  private static function b64url_encode($data){return rtrim(strtr(base64_encode($data),'+/','-_'),'=');}
  private static function b64url_decode($data){return base64_decode(strtr($data,'-_','+/'));}
  public static function sign($key,$msg){return self::b64url_encode(hash_hmac('sha256',$msg,$key,true));}
  public static function encode($payload,$key){
    $header=['alg'=>'HS256','typ'=>'JWT'];
    $h=self::b64url_encode(json_encode($header));
    $p=self::b64url_encode(json_encode($payload));
    $sig=self::sign($key,$h.'.'.$p);
    return $h.'.'.$p.'.'.$sig;
  }
  public static function decode($jwt,$key){
    $parts=explode('.',$jwt);if(count($parts)!==3) return null;
    list($h,$p,$s)=$parts; $msg=$h.'.'.$p; $sig=self::sign($key,$msg);
    if(!hash_equals($sig,$s)) return null;
    $hdr=json_decode(self::b64url_decode($h),true);
    if(!$hdr||($hdr['alg']??'')!=='HS256') return null;
    $pl=json_decode(self::b64url_decode($p),true);
    if(!$pl) return null;
    $now=time();
    if(isset($pl['exp']) && $pl['exp']<$now) return null;
    return $pl;
  }
}
