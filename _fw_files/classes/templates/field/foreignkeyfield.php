<?php

$form_items=new FormItems();

$options=$form_items->option('выберите из списка:','__null_option__');
if(is_array($this->choices)){
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
}elseif(is_array($foreign_data_pairs)){
	foreach($foreign_data_pairs as $pair_arr){
		$option_value=$pair_arr['value'];
		$option_text=$pair_arr['text'];
		$isSelected=($params_arr[$this->db_column]==$option_value)?'yes':'no';
		$options.=$form_items->option('&nbsp; '.$option_text, $option_value, $isSelected);
	}
}

$result=$form_items->sbox($this->field_name, $options, 'class="foreignKey"');
	
?>
