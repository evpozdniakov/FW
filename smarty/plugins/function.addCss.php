<?php

/**
 * Compressor::addCss($file_root_path, $compress=true, $media='screen')
 */
function smarty_function_addCss($params, $template){
	call_user_func_array(array('Compressor','addCss'), $params);
}
