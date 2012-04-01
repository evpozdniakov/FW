<?php

function smarty_function_fsize($params, $template){
	return fsize($params['bytes'],$params['lang']);
}