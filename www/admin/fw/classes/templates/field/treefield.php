<?php

//_print_r($elements_tree);
$form_items=new FormItems();

$options=$form_items->option('корень',0);
$options.=fieldTreeGetRecOptions($elements_tree,$params_arr,$this->db_column);

$result=$form_items->sbox($this->field_name, $options, 'class="foreignKey"');

function fieldTreeGetRecOptions($arr,$params_arr,$db_column,$level=0){
	if(is_array($arr)){
		$form_items=new FormItems();
		$level++;
		foreach($arr as $elem){
			$option_value=$elem['value'];
			$option_text=$elem['text'];
			$isSelected=($params_arr[$db_column]==$elem['value'])?'yes':'no';
			/*
			_echo($params_arr[$db_column]);
			_echo($elem['value']);
			_echo($isSelected);
			*/
			$result.=$form_items->option(str_repeat('&nbsp;',$level*4).$option_text, $option_value, $isSelected);
			$result.=fieldTreeGetRecOptions($elem['__children__'],$params_arr,$db_column,$level);
		}
	}
	return $result;
}
	
?>
