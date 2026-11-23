<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/reviews.php';
require_once __DIR__.'/../includes/content.php';
reviews_start_session();
$method=$_SERVER['REQUEST_METHOD']??'GET';
if($method==='GET') reviews_json(['ok'=>true,'content'=>content_read()]);
if($method!=='POST') reviews_json(['ok'=>false,'error'=>'Метод не поддерживается'],405);
reviews_require_admin(); reviews_check_csrf();
$action=$_POST['action']??'save';
if($action==='save'){
 $raw=(string)($_POST['content']??'');$v=json_decode($raw,true);
 if(!is_array($v)||strlen($raw)>1500000)reviews_json(['ok'=>false,'error'=>'Некорректные данные редактора.'],422);
 $clean=['fields'=>[]];
 foreach(($v['fields']??[]) as $k=>$value){if(!is_string($k)||!preg_match('/^[a-z0-9_]{1,60}$/',$k))continue;$clean['fields'][$k]=mb_substr((string)$value,0,5000);}
 foreach(['products','steps','photos','videos','checks','audience'] as $key){
  $clean[$key]=[];
  foreach(array_slice(is_array($v[$key]??null)?$v[$key]:[],0,250) as $item){
   if(is_string($item)){$clean[$key][]=mb_substr($item,0,500);continue;}
   if(!is_array($item))continue;$row=[];
   foreach($item as $k=>$value){if(!is_string($k)||!preg_match('/^[a-z0-9_]{1,40}$/',$k))continue;$row[$k]=mb_substr((string)$value,0,3000);}
   $clean[$key][]=$row;
  }
 }
 if(!content_write($clean))reviews_json(['ok'=>false,'error'=>'Не удалось сохранить настройки сайта.'],500);
 reviews_json(['ok'=>true]);
}
if($action==='upload'){
 if(empty($_FILES['file'])||($_FILES['file']['error']??4)!==0)reviews_json(['ok'=>false,'error'=>'Выберите файл для загрузки.'],422);
 $tmp=$_FILES['file']['tmp_name'];$size=(int)($_FILES['file']['size']??0);
 if($size>15*1024*1024)reviews_json(['ok'=>false,'error'=>'Максимальный размер файла — 15 МБ.'],422);
 $fi=new finfo(FILEINFO_MIME_TYPE);$mime=$fi->file($tmp);
 $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','video/mp4'=>'mp4','video/webm'=>'webm'];
 if(!isset($allowed[$mime]))reviews_json(['ok'=>false,'error'=>'Разрешены JPG/PNG/WebP и MP4/WebM.'],422);
 if(str_starts_with($mime,'image/')&&@getimagesize($tmp)===false)reviews_json(['ok'=>false,'error'=>'Файл изображения повреждён.'],422);
 $dir=dirname(__DIR__).'/uploads/site';
 if(!is_dir($dir)&&!mkdir($dir,0755,true)&&!is_dir($dir))reviews_json(['ok'=>false,'error'=>'Не удалось создать папку загрузок.'],500);
 $name=bin2hex(random_bytes(16)).'.'.$allowed[$mime];
 if(!move_uploaded_file($tmp,$dir.'/'.$name))reviews_json(['ok'=>false,'error'=>'Не удалось сохранить файл.'],500);
 reviews_json(['ok'=>true,'url'=>'/uploads/site/'.$name,'type'=>str_starts_with($mime,'video/')?'video':'image']);
}
reviews_json(['ok'=>false,'error'=>'Неизвестная команда.'],400);
