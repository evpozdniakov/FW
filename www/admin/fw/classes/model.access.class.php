<?php

class _Access extends Model{
	function init_vars(){
		$this->__admin__=array(
			//'delete_rel'=>true,//если true, то привязанные модели будут удаляться без предупреждения, как это реализовано в моделях [Админы] и [Модели]
			//'ipp'=>20,//items per page - кол-во элементов в левом меню в блоке "страница"
			//'list_display'=>'id,name',//поля, которые будут отдаваться функции __str__()
			//'filter'=>'id>1',//фильтрация элементов в левом меню
			//'tf'=>'name,description',//type filter - над левым меню появляется форма-фильтр, подстрока ищется в указанных полях модели
			//'group'=>'fks_id',//группировка, альтернативная постраничной
			'ordering'=>'_models_id',//сортировка, где + это asc, - это desc. полей может быть несколько через запятую
			//'controls'=>'save,save_then_add,up,down,delete',//кнопки формы. по умолчанию появляются только "сохранить" и "удалить", остальные нужно объявлять дополнительно
			//'onsubmit'=>'return ver(this.name)',//js-код в атрибуте onsubmit тега <form>
			//'js'=>'getJsFunctions()',//js-код на странице
			//'deny'=>('add,edit,delete'),//запрет на какое либо из действий, распростаняется в том числе и на суперадмина, например в [Лог авторизации]
		);
	}

	function init_fields(){
		$this->_models_id=new IntField(array('Модель','maxlength'=>'11'));
		$this->_users_id=new ForeignKeyField(array('Пользователь','maxlength'=>'11'));
		$this->add_access=new BooleanField(array('Добавление','default'=>'no','core'=>true,'blank'=>true));
		$this->edit_access=new BooleanField(array('Редактирование','default'=>'no','core'=>true,'blank'=>true));
		$this->delete_access=new BooleanField(array('Удаление','default'=>'no','core'=>true,'blank'=>true));
	}

	function __str__($self){
		$obj_model=ga(array(
			'classname'=>'_models',
			'fields'=>'txt_name',//список полей которые нужно вытащить через запятую 'id,name,body'
			'_get'=>$self['_models_id'],//число=id элемента, возвратит массив
		));
		return $obj_model['txt_name'];
	}
}

?>
