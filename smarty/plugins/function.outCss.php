<?php

/**
 * Compressor::outCss($root_dir='/media/js')
 */
function smarty_function_outCss($params, $template){
	Compressor::outCss($params['root_dir']);
}
