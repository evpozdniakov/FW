<?php

/**
 * Compressor::addCss($file_root_path, $compress=true, $media='screen')
 */
function smarty_function_addCss($params, $template){
	Compressor::addCss($params['file_root_path'], $params['compress'], $params['media']);
}
