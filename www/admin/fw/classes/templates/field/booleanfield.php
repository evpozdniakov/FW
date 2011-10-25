<?php

$form_items=new FormItems();

$result=$form_items->chbox(
	$this->field_name, 
	$this->txt_name,
	(($inputValue=='yes' || ($inputValue=='' && $this->default=='yes'))?'yes':'no'), 
	'yes'
);

?>
