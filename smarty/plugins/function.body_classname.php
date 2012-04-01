<?php

function smarty_function_body_classname($params, $template){
	if( $GLOBALS['error']==404 ){
		$cls='err404';
	}else{
		$cls=implode(' ',$GLOBALS['BODY_CLASS']);
	}
	return sprintf('class="%s"',$cls);
}