<?php

$form_items=new FormItems();

$field_name_modified=mb_substr($this->field_name,0,-1);
$post=$_POST[$this->model_name];

$datetime=parseDate($inputValue);
if(is_array($datetime)){
	$year=$datetime['year'];
	$month=$datetime['month'];
	$day=$datetime['day'];
	$hour=$datetime['hour'];
	$minute=$datetime['minute'];
	$second=$datetime['second'];
}

$result='<b>'.sprintf('%d',$day).' '.month($month,'r').' '.$hour.' : '.$minute.' : '.$second.'</b> ('.$year.' г.)<br>';

?>
