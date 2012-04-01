<?php

/**
 * Compressor::outJs($root_dir='/media/js')
 */
function smarty_function_outJs($params, $template){
	echo call_user_func_array(array('Compressor','outJs'), $params);
}
