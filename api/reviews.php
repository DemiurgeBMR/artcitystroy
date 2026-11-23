<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/reviews.php';
$items=reviews_read_all();
usort($items,static fn($a,$b)=>strcmp((string)($b['created_at']??''),(string)($a['created_at']??'')));
$out=[];
foreach($items as $v)$out[]=['id'=>(string)($v['id']??''),'text'=>(string)($v['text']??''),'created_at'=>(string)($v['created_at']??''),'photos'=>array_values(array_filter($v['photos']??[],'is_string'))];
reviews_json(['ok'=>true,'reviews'=>$out]);
