<?php

$form_items=new FormItems();

if(is_array($this->choices)){
	$options=$form_items->option('выберите из списка:','__null_option__');
	foreach($this->choices as $option_value=>$option_title_str){
		//однажды возникла такая ситуация, что функция сформировала массив и автоматически выстроила его по возрастанию ключей
		//чтобы такой автосортировки избежать мне понадобилось превратить в строку, добавив к концу двойное подчеркивание
		//и здесь для правильного выделения текущей опции я делаю сравнение сначала просто с числом, а затем с числом+подчеркиванием
		$isSelected=($option_value==$params_arr[$this->db_column] || $option_value==$params_arr[$this->db_column].'__')?'yes':'no';
		$option_title_arr=explode('~',$option_title_str);
		$option_title=$option_title_arr[0];
		$style=($option_title_arr[1]!='')
			?'style="background-color: #'.$option_title_arr[1].'"'
			:'';
		$options.=$form_items->option('&nbsp; '.$option_title, $option_value, $isSelected, $style);
	}
	$result=$form_items->sbox($this->field_name, $options, 'class="formItemSbox"');
}else{
	$result=$form_items->text($this->field_name, $inputValue, $this->maxlength);	
}

