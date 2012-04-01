<?php

function smarty_function_showclientform($params, $template){
	// call_user_func_array('showclientform', $params);
	showclientform($params['model_name'], $params['acc_fields'], $params['native_data'], $params['errors_arr']);
}