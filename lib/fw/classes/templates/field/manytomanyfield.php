<?php

$form_items=new FormItems();

/*
	_print_r($this);
	_print_r($result_elements);
*/

//иногда нужно вместо селектбокса сделать чекбоксы.
//чтобы не вводить дополнительное поле в _modelsfields
//поступим так: будем показывать селектбокс если элементов 
//менее 20, иначе чекбоксы

// отключил селектбоксы совсем, пусть всегда будут чекбоксы

if(is_array($result_elements)){
	$post=p2v($this->model_name);

	$result=$form_items->untitled(
		$form_items->chbox($this->field_name.'_manage_all','выбрать/отменить все записи','','','onclick="A.miez.MTMfieldTakeAll(this)"')
	);

	if(false && count($result_elements)>20){

		//СЕЛЕКТБОКС
		//$options=$form_items->option('выберите из списка:');
		foreach($result_elements as $element){
			$option_value=$element['value'];
			$option_title=$element['title'];
			if(is_array($post[$this->db_column]) && in_array($element['value'],$post[$this->db_column])){
				$isSelected='yes';
			}else{
				$isSelected=$element['selected'];
			}
			$options.=$form_items->option('&nbsp; '.$option_title, $option_value, $isSelected);
		}
		$result.=$form_items->sbox($this->field_name.'[]',$options,'class="formItemSbox" multiple size="15"');

	}else{

		//ЧЕКБОКСЫ
		foreach($result_elements as $element){
			$chbox_value=$element['value'];
			$chbox_title=$element['title'];
			if(is_array($post[$this->db_column]) && in_array($element['value'],$post[$this->db_column])){
				$isChecked='yes';
			}else{
				$isChecked=$element['selected'];
			}
			$result.=$form_items->chbox($this->field_name.'[]',$chbox_title, $isChecked, $chbox_value).'<br>';
		}
	
	}
}

?>
