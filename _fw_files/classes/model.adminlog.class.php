<?php

class _AdminLog extends Model{
	function init_vars(){
		$this->__admin__=array(
			//'delete_rel'=>true,//если true, то привязанные модели будут удаляться без предупреждения, как это реализовано в моделях [Админы] и [Модели]
			//'ipp'=>20,//items per page - кол-во элементов в левом меню в блоке "страница"
			//'list_display'=>'id,adminid,adminlogin',//поля, которые будут отдаваться функции __str__()
			//'filter'=>'id>1',//фильтрация элементов в левом меню
			//'tf'=>'mdate',//type filter - над левым меню появляется форма-фильтр, подстрока ищется в указанных полях модели
			'group'=>'mdate',//группировка, альтернативная постраничной
			//'ordering'=>'-cdate',//сортировка, где + это asc, - это desc. полей может быть несколько через запятую
			//'controls'=>'save,save_then_add,up,down,delete',//кнопки формы. по умолчанию появляются только "сохранить" и "удалить", остальные нужно объявлять дополнительно
			//'onsubmit'=>'return ver(this.name)',//js-код в атрибуте onsubmit тега <form>
			//'js'=>'getJsFunctions()',//js-код на странице
			'deny'=>('add,edit,delete'),//запрет на какое либо из действий, распростаняется в том числе и на суперадмина, например в [Лог авторизации]
		);
	}

	function init_fields(){
		$this->action=new CharField(array('Действие','maxlength'=>'3','choices'=>'actionChoices()','editor'=>'no'));
		$this->adminlogin=new CharField(array('Логин','maxlength'=>'16'));
		$this->adminid=new IntField(array('ID'));
		$this->mdate=new TimestampField(array('Дата и время авторизации','editable'=>true));
	}

	function __str__($self){
		// return ''.$self['adminlogin'].' '.parseDate($self['mdate'],'d M').' '.$self['action'].'';
		return ''.$self['adminlogin'].' '.$self['action'].'';
	}

	function actionChoices(){
		return array(
			'in'=>'login',
			'out'=>'logout',
		);
	}
}

?>