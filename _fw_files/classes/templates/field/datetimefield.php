<?php

$form_items=new FormItems();

$field_name_modified=mb_substr($this->field_name,0,-1);
$post=$_POST[$this->model_name];

$datetime=parseDate($inputValue);
if(is_array($datetime)){
	$hour=$datetime['hour'];
	$minute=$datetime['minute'];
}elseif($post[$this->db_column.'_minute']!='' || $post[$this->db_column.'_hour']!=''){
	//если какое-либо из значений даты есть в $_POST, то нужно определить все параметры относительно $_POST
	$minute=$post[$this->db_column.'_minute'];
	$hour=$post[$this->db_column.'_hour'];
}else{
	$minute=date('i');
	$hour=date('H');
}

// minutes
for($i=0;$i<=59;$i++){
	$isSelected=($i==$minute)?'yes':'no';
	$minutesSBoxOptions.=$form_items->option(sprintf("%02s",$i),$i,$isSelected);
}
$minutesSBox=$form_items->sbox($field_name_modified.'_minute]',$minutesSBoxOptions,'class="formItemDateMinute"');

// hours
for($i=0;$i<=23;$i++){
	$isSelected=($i==$hour)?'yes':'no';
	$hoursSBoxOptions.=$form_items->option(sprintf("%02s",$i),$i,$isSelected);
}
$hoursSBox=$form_items->sbox($field_name_modified.'_hour]',$hoursSBoxOptions,'class="formItemDateHour"');

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
$datepicker=$form_items->text($this->field_name, $input_value, 10, 'class="datepicker"');

$result=$hoursSBox.':'.$minutesSBox.' &nbsp; '.$datepicker;

