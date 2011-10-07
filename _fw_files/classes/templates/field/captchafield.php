<?php

if($GLOBALS['path'][1]=='admin'){
	$result='';
}else{
	$form_items=new FormItems();

	$result='
		<span class="captcha">
			'.fetch('_help/captcha.tpl').'
			'.$form_items->text($this->field_name).'
		</span>
	';
}

?>
