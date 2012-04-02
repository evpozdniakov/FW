<?php

if( !empty($final_models_list) ){
	foreach($final_models_list as $model_name=>$model_txt_name){
		$gif='/admin/models/'.$model_name.'/icon.gif';
		$png='/admin/models/'.$model_name.'/icon.png';
		if( file_exists(SITE_DIR.$png) ){
			$icon=sprintf('<span class="icon iconPNG" style="background-image: url(%s)">&nbsp;</span>', $png);
		}elseif( file_exists(SITE_DIR.$gif) ){
			$icon=sprintf('<span class="icon iconGIF" style="background-image: url(%s)">&nbsp;</span>', $gif);
		}else{
			$icon='<span class="icon">&nbsp;</span>';
		}
		$attr='href="'.DOMAIN_PATH.'/admin/'.$model_name.'/"';
		$result.=ulba(
			DOMAIN_PATH.'/admin/'.$model_name.'/',
			'<li>'.$icon.'<a '.$attr.'>'.$model_txt_name.'</a></li>',
			'<li class="active">'.$icon.'<b>'.$model_txt_name.'</b></li>',
			'<li class="active">'.$icon.'<a '.$attr.'><b>'.$model_txt_name.'</b></a></li>'
		);
	}
}
if($_SESSION['admin_user']['su']){
	$result.='<li><a href="'.DOMAIN_PATH.'/admin/synchro/">synchro</a></li>';
}
$result.='<li><a href="'.DOMAIN_PATH.'/admin/logout.html">logout</a></li>';
$result='<ul>'.$result.'</ul>';
