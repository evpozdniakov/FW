<?php

//_echo($num);
//_print_r($item_data_arr_or_int);
//_print_r($core_fields_arr);
$form_items=new FormItems();

$item_id_or_new=(is_array($item_data_arr_or_int))
	?$item_data_arr_or_int['id']
	:'new'.$item_data_arr_or_int;
$result='';
$theaders='<td></td>';
foreach($this as $key=>$obj_field){
	//перебираем экземпляры класса Field
	if(is_object($obj_field) && is_subclass_of($obj_field,'Field')){//_echo($key);
		if( is_int(array_search($key,$core_fields_arr)) ){
			//вызываем метод, который возвращает html-код поля
			if($obj_field->editable){
				$obj_field->mode='core';
				$obj_field->line=$num;
				$obj_field->field_name=$obj_field->model_name.'_'.$item_id_or_new.'['.$obj_field->db_column.']';
				$error_bool=(isset($errors_arr) && isset($errors_arr[$key]))?$errors_arr[$key]:false;
				$result_arr_or_str=$obj_field->getFormFieldHTMLCommon($item_data_arr_or_int,$error_bool);
				if(is_array($result_arr_or_str)){
					$result.=$result_arr_or_str[0];
					$theaders.=$result_arr_or_str[1];
				}else{
					$result.=$result_arr_or_str;
				}
			}
		}
	}
}
if($result!=''){
	$class=($num%2==0)?'even':'odd';
	if($num==1){
		$class.=" first";
	}
	$line_name=(is_array($item_data_arr_or_int))?$this->__str__($item_data_arr_or_int):'';
	// $hiddenfield='<input type="hidden" name="'.$this->__name__.'_'.$item_id_or_new.'[hidden]" value="1">';
	// $result='<td><b>'.$line_name.'</b>'.$hiddenfield.'</td>'.$result;
	$result='<td><b>'.$line_name.'</b></td>'.$result;
	$delete=$form_items->chbox($obj_field->model_name.'_'.$item_id_or_new.'[__delete__]_check_all');
	$delete=$form_items->title($delete);
	$delete='<th>'.$delete.'</th>';
	if($num==1){
		$theaders='<tr>'.$delete.$theaders.'</tr>';
	}
	$delete='';
	if(is_array($item_data_arr_or_int)){
		$delete=$form_items->chbox($obj_field->model_name.'_'.$item_id_or_new.'[__delete__]');
		$delete='<td class="delete">'.$delete.'</td>';
	}else{
		$delete='<td></td>';
	}
	$result=$theaders.'<tr class="'.$class.'">'.$delete.$result.'</tr>';
}

return $result;

?>