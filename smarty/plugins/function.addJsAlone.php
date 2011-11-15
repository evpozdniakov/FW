<?php

/**
 * Compressor::addJsAlone($file_root_path,$charset='')
 */
function smarty_function_addJsAlone($params, $template){
	Compressor::addJsAlone($params['file_root_path'], $params['charset']);
}
