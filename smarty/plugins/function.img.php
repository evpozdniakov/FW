<?php

function smarty_function_img($params, $template){
	return sprintf(
		'<img src="%s" width="%d" height="%d" alt="">'
		,$params['a'][$params['f'].'_uri']
		,defvar($params['a'][$params['f'].'_width'],$params['w'])
		,defvar($params['a'][$params['f'].'_height'],$params['h'])
	);
}