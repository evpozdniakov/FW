<?php

function smarty_function_unset_session($params, $template){
	unset($_SESSION[$params['name']]);
}