<?php

function smarty_function_render($params, $template){
	if( !isset($params['params']) ){
		$params['params']='';
	}
	return $GLOBALS['obj_client']->render(sprintf('%s->%s()',$params['model'],$params['view']), $params['params']);
}