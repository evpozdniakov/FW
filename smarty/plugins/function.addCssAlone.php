<?php

/**
 * Compressor::addCssAlone($file_root_path, $media='screen')
 */
function smarty_function_addCssAlone($params, $template){
	Compressor::addCssAlone($params['file_root_path'], $params['media']);
}
