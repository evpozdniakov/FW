<?php

$form_items=new FormItems();

if(empty($this->choices)){
	$result=$form_items->text($this->field_name, $inputValue, $this->maxlength);
}else{
	if(is_array($this->choices)){
		$options=$form_items->option('выберите из списка:','__null_option__');
		foreach($this->choices as $option_value=>$option_title_str){
			$isSelected=($option_value==$inputValue)?'yes':'no';
			$option_title_arr=explode('~',$option_title_str);
			$option_title=$option_title_arr[0];
			$style=(isset($option_title_arr[1]))
				?'style="background-color: #'.$option_title_arr[1].'"'
				:'';
			$options.=$form_items->option('&nbsp; '.$option_title, $option_value, $isSelected, $style);
		}
	}
	$result=$form_items->sbox($this->field_name, $options, 'class="formItemSbox"');
}

?>
