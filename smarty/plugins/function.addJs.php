<?php

/**
 * Compressor::addJs($file_root_path, $compress=true, $charset='')
 */
function smarty_function_addJs($params, $template){
	call_user_func_array(array('Compressor','addJs'), $params);
}
