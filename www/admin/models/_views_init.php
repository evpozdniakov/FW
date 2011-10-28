<?php

//это не только градусник, но также <title>
if( !isset($GLOBALS['gradusnik']) ){
	$GLOBALS['gradusnik']=array();
	$structure_data=$GLOBALS['obj_client']->structure_data;
	$num=0;
	foreach($structure_data as $item){
		$num++;
		if($num==1 && count($structure_data)>1 && $item['short']){
			$txt=defvar($item['title'],$item['short']);
		}else{
			$txt=$item['title'];
		}
		$GLOBALS['gradusnik'][]=array('url'=>$item['url'],'txt'=>$txt,'titletag'=>$item['titletag'],'description'=>$item['description'],'keywords'=>$item['keywords']);
	}
}

//class для <body>
$GLOBALS['BODY_CLASS']=array();
if(IS_FIRST===true){
	$GLOBALS['BODY_CLASS'][]='first';
}else{
	$GLOBALS['BODY_CLASS'][]='second';
	$GLOBALS['BODY_CLASS'][]=sprintf('page%d',$GLOBALS['obj_client']->structure_data_reverse[0]['id']);
	$GLOBALS['BODY_CLASS'][]=$GLOBALS['path_requested'][1];
}

