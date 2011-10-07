<?php

class FormItems{
	function star(){
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_star.php');
		return $result;
	}

	function title($title, $status=0){
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_title.php');
		return $result;
	}

	function titled($title_or_arr, $input_tag='', $status=0, $help_text='', $extra_text='',$wrap_cls=''){
		if(is_array($title_or_arr)){
			$title=$title_or_arr['title'];
			$input_tag=$title_or_arr['input_tag'];
			$status=$title_or_arr['status'];
			$help_text=$title_or_arr['help_text'];
			$extra_text=$title_or_arr['extra_text'];
			$wrap_cls=$title_or_arr['wrap_cls'];
		}else{
			$title=$title_or_arr;
		}
		
		if($GLOBALS['path'][1]!='admin'){
			include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_titled_client.php');
		}else{
			include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_titled.php');
		}
		return $result;
	}

	function core($body, $status=0, $extra_class='', $extra_text=''){
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_core.php');
		return $result;
	}

	function untitled($tag_or_arr, $status=0, $help_text=''){
		if(is_array($tag_or_arr)){
			$input_tag=$tag_or_arr['input_tag'];
			$status=$tag_or_arr['status'];
			$help_text=$tag_or_arr['help_text'];
			$wrap_cls=$tag_or_arr['wrap_cls'];
		}else{
			$input_tag=$tag_or_arr;
		}
		if($GLOBALS['path'][1]!='admin'){
			include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_untitled_client.php');
		}else{
			include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_untitled.php');
		}
		return $result;
	}

	function hidden($name, $value){
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_hidden.php');
		return $result;
	}

	function text($name, $value='', $max=255, $props='', $is_obligfield=false, $title='' ){
		$title = $title ? 'title="'. $title. '"' : '';
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_text.php');
		return $result;
	}

	function email($name, $value='', $max=255, $props='', $is_obligfield=false, $title=''){
		$title = $title ? 'title="'. $title. '"' : '';
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_email.php');
		return $result;
	}

	function area($name, $value='', $rows=5, $props='', $is_obligfield=false, $title=''){
		$title = $title ? 'title="'. $title. '"' : '';
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_area.php');
		return $result;
	}

	function file($field_name,$file_info=''){
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_file.php');
		return $result;
	}

	function image($src, $w, $h, $delname){
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_image.php');
		return $result;
	}

	//function captcha($src, $w, $h, $delname){
	//function captcha(){
	function captcha($name, $value='', $max=255, $props='', $is_obligfield=true, $title=''){
		$title = $title ? 'title="'. $title. '"' : '';
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_captcha.php');
		return $result;
	}

	function sbox($name, $options, $props='', $title=''){
		$title = $title ? 'title="'. $title. '"' : '';
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_sbox.php');
		return $result;
	}

	function option($title, $value='', $isSelected='no', $props=''){
		$selected=($isSelected=='yes')?'selected':'';
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_option.php');
		return $result;
	}

	function date($prefix='_', $day, $month, $year){
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_date.php');
		return $result;
	}

	function time($prefix='_', $hour='', $minute=''){
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_time.php');
		return $result;
	}

	function chbox($name, $text='', $isChecked='no', $value='on', $props='', $is_obligfield=false, $title=''){
		$title = $title ? 'title="'. $title. '"' : '';
		$checked=($isChecked=='yes')?'checked':'';
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_chbox.php');
		return $result;
	}

	function radio($name, $text='', $isChecked='no', $value='on', $props='', $title=''){
		$title = $title ? 'title="'. $title. '"' : '';
		$checked=($isChecked=='yes')?'checked':'';
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_radio.php');
		return $result;
	}

	function password($name, $value='', $max=255, $props=''){
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_password.php');
		return $result;
	}

	function button($value, $name='', $props=''){
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_button.php');
		return $result;
	}

	function submit($value='Submit', $name='', $props=''){
		include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/templates/formitems/form_item_submit.php');
		return $result;
	}
}


?>