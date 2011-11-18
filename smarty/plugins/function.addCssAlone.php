<?php

/**
 * Compressor::addCssAlone($file_root_path, $media='screen')
 */
function smarty_function_addCssAlone($params, $template){
	call_user_func_array(array('Compressor','addCssAlone'), $params);
}
