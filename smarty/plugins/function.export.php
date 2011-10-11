<?php

function smarty_function_export($params, $template){
	echo export($params['var']);
}