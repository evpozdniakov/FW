<?php

function smarty_function_ulba($params, $template){
	return ulba($params['u'],$params['l'],$params['b'],$params['a']);
}