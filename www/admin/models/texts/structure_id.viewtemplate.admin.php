<?php
/*
шаблон нестандартного поля формы
должен СОЗДАТЬ НОВУЮ переменную $result и вернуть ее

входные данные
$this - объект поля
$this->field_name - атрибут name
$inputValue - значение текущего элемента !!! ($params_arr - данные элемента модели)
$form_items - экземпляр класса FormItems

приемы
$this->getFormFieldHTMLtag($params_arr) - возвращает стандартное поле формы
*/

$result=$form_items->sbox($this->field_name, adminModelsTextsStructureIdRec($params_arr));

function adminModelsTextsStructureIdRec($params_arr,$id='',$level=1){
	$result='';
	$form_items=new FormItems();
	if(empty($id)){
		$id=DOMAIN_ID;
		$result=$form_items->option('выберите из списка:');
	}
	$model_structure=gmo('structure');
	$item=$model_structure->gbi($id);
	if(is_array($item)){
		$str=str_repeat('&nbsp;',2*$level).$model_structure->__str__($item);
		$is_selected=(bool)($params_arr['structure_id']==$item['id']);
		$result.=$form_items->option($str,$item['id'],$is_selected);
		$children=$model_structure->ga(array(
			'fields'=>'*',//список полей которые нужно вытащить через запятую 'id,name,body'
			'filter'=>e5csql('parent=?',$item['id']),//строка фильтра типа 'parent=32'
			'order_by'=>'ordering',//строка типа или '-cdate' 'parent, +name' или '__random__' - случайный порядок
			'_slice'=>'0',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
		));
		if(count($children)){
			$level++;
			foreach($children as $item){
				$result.=adminModelsTextsStructureIdRec($params_arr,$item['id'],$level);
			}
		}
	}
	return $result;
}
