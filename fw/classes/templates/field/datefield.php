<?php

$form_items=new FormItems();
$post=$_POST[$this->model_name];
// datepicker
$input_value='';
if( parseDate($inputValue) ){
	$input_value=parseDate($inputValue,'d.m.Y');
}else{
	$date_arr=explode('.',$post[$this->db_column]);
	if( is_array($date_arr) && count($date_arr)==3 ){
		$day=$date_arr[0];
		$month=$date_arr[1];
		$year=$date_arr[2];
		$input_value=sprintf('%02d.%02d.%d',$day,$month,$year);
	}
}
$result=$form_items->text($this->field_name, $input_value, 10, 'class="datepicker"');
