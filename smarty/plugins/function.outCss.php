<?php

/**
 * Compressor::outCss($root_dir='/media/js')
 */
function smarty_function_outCss($params, $template){
	call_user_func_array(array('Compressor','outCss'), $params);
}
