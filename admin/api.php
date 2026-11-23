<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/reviews.php';
reviews_start_session();
$m=$_SERVER['REQUEST_METHOD']??'GET'; $a=$_GET['action']??$_POST['action']??'';
if($m==='POST'&&$a==='login'){
$c=reviews_config(); $email=strtolower(trim((string)($_POST['email']??''))); $pass=(string)($_POST['password']??''); $now=time();
if(!isset($_SESSION['attempts']))$_SESSION['attempts']=[]; $_SESSION['attempts']=array_values(array_filter($_SESSION['attempts'],static fn($t)=>$t>$now-900));
if(count($_SESSION['attempts'])>=8)reviews_json(['ok'=>false,'error'=>'Слишком много попыток. Подождите 15 минут.'],429);
$ok=!empty($c['email'])&&!empty($c['password_hash'])&&hash_equals(strtolower((string)$c['email']),$email)&&password_verify($pass,(string)$c['password_hash']);
if(!$ok){$_SESSION['attempts'][]=$now;reviews_json(['ok'=>false,'error'=>'Неверная почта или пароль.'],401);}
session_regenerate_id(true);$_SESSION['admin_ok']=true;$_SESSION['csrf']=bin2hex(random_bytes(32));$_SESSION['attempts']=[];reviews_json(['ok'=>true,'csrf'=>$_SESSION['csrf']]);
}
if($m==='GET'&&$a==='status')reviews_json(['ok'=>true,'authenticated'=>!empty($_SESSION['admin_ok']),'csrf'=>$_SESSION['csrf']??null,'configured'=>!empty(reviews_config()['password_hash'])]);
if($m==='POST'&&$a==='logout'){reviews_require_admin();reviews_check_csrf();$_SESSION=[];session_destroy();reviews_json(['ok'=>true]);}
if($m!=='POST')reviews_json(['ok'=>false,'error'=>'Метод не поддерживается'],405);
reviews_require_admin();reviews_check_csrf();$items=reviews_read_all();
try{
if($a==='save'){
$id=trim((string)($_POST['id']??''));$text=trim(strip_tags((string)($_POST['text']??'')));if(function_exists('mb_substr'))$text=mb_substr($text,0,5000);else $text=substr($text,0,5000);
$idx=-1;foreach($items as $i=>$v)if(($v['id']??'')===$id&&$id!==''){$idx=$i;break;}
if($idx<0){$id=bin2hex(random_bytes(12));$v=['id'=>$id,'text'=>$text,'photos'=>[],'created_at'=>date(DATE_ATOM)];}else{$v=$items[$idx];$v['text']=$text;$v['updated_at']=date(DATE_ATOM);}
if(isset($_FILES['photos']))$v['photos']=array_merge($v['photos']??[],reviews_upload_photos($_FILES['photos']));if(count($v['photos'])>30)reviews_json(['ok'=>false,'error'=>'В одном отзыве максимум 30 фотографий.'],422);if($text===''&&empty($v['photos']))reviews_json(['ok'=>false,'error'=>'Добавьте текст или хотя бы одну фотографию.'],422);
if($idx<0)$items[]=$v;else $items[$idx]=$v;if(!reviews_write_all($items))throw new RuntimeException('Не удалось сохранить отзывы.');reviews_json(['ok'=>true]);
}
if($a==='delete'){$id=(string)($_POST['id']??'');foreach($items as $v)if(($v['id']??'')===$id)foreach(($v['photos']??[]) as $p)reviews_delete_image((string)$p);$items=array_values(array_filter($items,static fn($v)=>($v['id']??'')!==$id));if(!reviews_write_all($items))throw new RuntimeException('Не удалось сохранить изменения.');reviews_json(['ok'=>true]);}
if($a==='delete_photo'){$id=(string)($_POST['id']??'');$photo=(string)($_POST['photo']??'');foreach($items as &$v)if(($v['id']??'')===$id){$v['photos']=array_values(array_filter($v['photos']??[],static fn($p)=>$p!==$photo));reviews_delete_image($photo);}unset($v);if(!reviews_write_all($items))throw new RuntimeException('Не удалось сохранить изменения.');reviews_json(['ok'=>true]);}
reviews_json(['ok'=>false,'error'=>'Неизвестная команда.'],400);
}catch(Throwable $e){reviews_json(['ok'=>false,'error'=>$e->getMessage()],400);}
