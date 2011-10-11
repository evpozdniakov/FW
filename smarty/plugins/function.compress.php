<?php

function smarty_function_compress($params, $template){
	$compressor=new Compressor();
	if( isset($params['css_files']) ){
		foreach($params['css_files'] as $css){
			$compressor->addCss($css);
		}
		$compressor->outCss(defvar('/media/css/',$params['css_drop']));
	}
	if( isset($params['js_files']) ){
		foreach($params['js_files'] as $js){
			$compressor->addJs($js);
		}
		$compressor->outJs('/media/js/',$params['js_drop']);
	}
}