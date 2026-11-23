<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/reviews.php';
require_once __DIR__.'/../includes/content.php';
reviews_start_session();
$method=$_SERVER['REQUEST_METHOD']??'GET';
if($method==='GET') reviews_json(['ok'=>true,'items'=>content_read()['items']]);
if($method!=='POST') reviews_json(['ok'=>false,'error'=>'Метод не поддерживается'],405);
reviews_require_admin(); reviews_check_csrf();
$action=$_POST['action']??'save';
if($action==='save'){
 $raw=(string)($_POST['items']??'');
 $items=json_decode($raw,true);
 if(!is_array($items)||count($items)>300)reviews_json(['ok'=>false,'error'=>'Некорректные данные редактора.'],422);
 $clean=[];
 foreach($items as $item){
  if(!is_array($item))continue;
  $selector=trim((string)($item['selector']??''));
  if($selector===''||strlen($selector)>300)continue;
  $clean[]=['selector'=>$selector,'text'=>substr((string)($item['text']??''),0,5000),'src'=>substr((string)($item['src']??''),0,1000),'hidden'=>!empty($item['hidden'])];
 }
 if(!content_write(['items'=>$clean]))reviews_json(['ok'=>false,'error'=>'Не удалось сохранить настройки сайта.'],500);
 reviews_json(['ok'=>true]);
}
if($action==='upload'){
 if(empty($_FILES['file'])||($_FILES['file']['error']??4)!==0)reviews_json(['ok'=>false,'error'=>'Выберите файл для загрузки.'],422);
 $tmp=$_FILES['file']['tmp_name'];$size=(int)($_FILES['file']['size']??0);
 if($size>15*1024*1024)reviews_json(['ok'=>false,'error'=>'Максимальный размер файла — 15 МБ.'],422);
 $fi=new finfo(FILEINFO_MIME_TYPE);$mime=$fi->file($tmp);
 $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','video/mp4'=>'mp4','video/webm'=>'webm'];
 if(!isset($allowed[$mime]))reviews_json(['ok'=>false,'error'=>'Разрешены изображения JPG/PNG/WebP и видео MP4/WebM.'],422);
 if(str_starts_with($mime,'image/')&&@getimagesize($tmp)===false)reviews_json(['ok'=>false,'error'=>'Файл изображения повреждён.'],422);
 $dir=dirname(__DIR__).'/uploads/site';
 if(!is_dir($dir)&&!mkdir($dir,0755,true)&&!is_dir($dir))reviews_json(['ok'=>false,'error'=>'Не удалось создать папку загрузок.'],500);
 $name=bin2hex(random_bytes(16)).'.'.$allowed[$mime];
 if(!move_uploaded_file($tmp,$dir.'/'.$name))reviews_json(['ok'=>false,'error'=>'Не удалось сохранить файл.'],500);
 reviews_json(['ok'=>true,'url'=>'/uploads/site/'.$name,'type'=>str_starts_with($mime,'video/')?'video':'image']);
}
reviews_json(['ok'=>false,'error'=>'Неизвестная команда.'],400);
