<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$email='sergejacenko765@gmail.com';fwrite(STDOUT,"Создание пароля администратора для ".$email."\nВведите пароль (не менее 12 символов): ");
if(DIRECTORY_SEPARATOR==='/')system('stty -echo');$password=trim((string)fgets(STDIN));if(DIRECTORY_SEPARATOR==='/')system('stty echo');fwrite(STDOUT,"\n");
if(strlen($password)<12){fwrite(STDERR,"Нужно минимум 12 символов.\n");exit(1);}
$dir=dirname(__DIR__).'/private';if(!is_dir($dir)&&!mkdir($dir,0750,true)&&!is_dir($dir)){fwrite(STDERR,"Не удалось создать private.\n");exit(1);}
$config="<?php\nreturn ".var_export(['email'=>$email,'password_hash'=>password_hash($password,PASSWORD_DEFAULT)],true).";\n";
if(file_put_contents($dir.'/admin.php',$config,LOCK_EX)===false){fwrite(STDERR,"Не удалось записать настройки.\n");exit(1);}@chmod($dir.'/admin.php',0600);
fwrite(STDOUT,"Готово. Пароль сохранён только в виде хеша вне public_html.\n");
