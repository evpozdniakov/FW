<?php

function smarty_function_postfix($params, $template){
	return postfix($params['num'],$params['str1'],$params['str2'],$params['str5']);
}