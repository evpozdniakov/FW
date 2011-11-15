<?php

/**
 * Compressor::outJs($root_dir='/media/js')
 */
function smarty_function_outJs($params, $template){
	Compressor::outJs($params['root_dir']);
}
