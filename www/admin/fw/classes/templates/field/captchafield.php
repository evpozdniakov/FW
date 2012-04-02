<?php

if( IS_ADMIN===true ){
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
