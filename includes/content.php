<?php
declare(strict_types=1);
function content_private_dir(): string { return dirname(__DIR__, 2).'/private'; }
function content_file(): string { return content_private_dir().'/site-content.json'; }
function content_read(): array {
 $f=content_file();
 if(!is_file($f)) return ['configured'=>false,'fields'=>[],'hidden'=>[]];
 $v=json_decode((string)file_get_contents($f),true);
 if(!is_array($v))return ['configured'=>false,'fields'=>[],'hidden'=>[]];
 $v['configured']=true;
 foreach(['fields','hidden','products','steps','photos','videos','checks','audience'] as $k)if(!isset($v[$k])||!is_array($v[$k]))$v[$k]=[];
 return $v;
}
function content_write(array $data): bool {
 $d=content_private_dir();
 if(!is_dir($d)&&!mkdir($d,0750,true)&&!is_dir($d))return false;
 $json=json_encode($data,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_INVALID_UTF8_SUBSTITUTE);
 if($json===false)return false;
 $tmp=content_file().'.tmp';
 if(file_put_contents($tmp,$json,LOCK_EX)===false)return false;
 @chmod($tmp,0600);
 return rename($tmp,content_file());
}
