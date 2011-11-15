<?php

/**
 * Compressor::addJs($file_root_path, $compress=true, $charset='')
 */
function smarty_function_addJs($params, $template){
	Compressor::addJs($params['file_root_path'], $params['compress'], $params['charset']);
}
