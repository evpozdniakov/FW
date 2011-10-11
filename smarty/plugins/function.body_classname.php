<?php

function smarty_function_body_classname($params, $template){
	return sprintf('class="%s"',implode(' ',$GLOBALS['BODY_CLASS']));
}