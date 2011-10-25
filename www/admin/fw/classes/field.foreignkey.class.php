<?php

class ForeignKeyField extends Field{
	var $default='0';

	function constantProps(){
		/*
			свойства могут быть неизменяемыми, например у NullBooleanField свойство null=true
			чтобы установить такие свойства, вызываем ->constantProps()
		*/
		$this->type='int';
		$this->maxlength='';
		$this->default='0';
		$this->unsigned=true;
		$this->null=false;
	}

	function getFormFieldHTMLtag($params_arr){
		/*
			возвращает HTML-код поля формы в форме редактирования элемента модели

			$params_arr - массив значений всех свойств элемента модели
		*/
		//определяем название привязанной модели
		$foreign_model_name=$this->getRelModelName();//_echo($foreign_model_name);
		if(empty($foreign_model_name)){_die('не определилось название привязанной модели в ForeignKeyField->getFormFieldHTMLtag()'."\n\n".'модель '.$this->model_name."\n".'название поля $this->db_column='.$this->db_column."\n".'привязанная модель $this->model_rel='.$this->model_rel);}
		//получаем объект привязанной модели
		$obj_foreign_model=getModelObject($foreign_model_name);
		//получаем данные из привязанной модели
		$foreign_data=ga(array(
			'classname'=>$foreign_model_name,
			'filter'=>$obj_foreign_model->__admin__['filter'],//строка фильтра типа 'parent=32'
			'order_by'=>$obj_foreign_model->__admin__['ordering'],//строка типа или '-cdate' 'parent, +name' или '__random__' - случайный порядок
			'_slice'=>'0',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
		));
		//получаем данные в виде пар id=>__str__()
		if(is_array($foreign_data)){
			$foreign_data_pairs=array();
			foreach($foreign_data as $model_item){
				$foreign_data_pairs[]=array('value'=>$model_item['id'],'text'=>$obj_foreign_model->__str__($model_item));
			}//_print_r($foreign_data_pairs);
		}
		//подключаем файл с шаблоном
		include(LIB_DIR.'/fw/classes/templates/field/'.$this->__name__.'.php');
		//return 'поле формы со значением <pre>'.e5c($data).'</pre><br>';

		return $result;
	}

	/*function getFormFieldHTMLError($error_bool){
		/*
			возвращает массив с классом и текстом ошибки

			$error_bool - true если поле было не заполнено, или заполнено с ошибкой
		* /
	}*/

	/*function getFormFieldHTMLth($error_arr){
		/*
			возвращает тег <th> заголовка колонки для режима core

			$error_arr - массив с классом и текстом ошибки
		* /
	}*/

	/*function getFormFieldHTMLWrap($input_tag,$error_arr){
		/*
			возвращает тег поля формы завернутый в <p> или <td>, в зависимости от $this->mode

			$input_tag - тег поля формы
			$error_arr - массив с классом и текстом ошибки
		* /
	}*/

	/*function getModelItemInitValue($model_item_init_values){
		/*
			переопределяемый в потомках метод, который возвращает 
			инициализирующее значение для данного поля на основе $model_item_init_values,
			для большинства полей это значение равно $model_item_init_values[$this->db_column]

			$model_item_init_values - инициализирующий массив всех значений элемента модели, 
			как правило, полученный из $_POST
		* /
	}*/

	/*function getSQLcreate(){
		/*
			возвращает фрагмент sql-запроса create table, 
			который отвечает за описание конкретного поля
		* /
	}*/

	/*function getDbColumnDefinition(){
		/*
			возвращает фрагмент sql-запроса с описанием конкретного поля 
			и является частью таких запросов как "create table", 
			"alter table add column", "alter table change column"
		* /
	}*/

	/*function getSQLupdate($model_item_value){
		/*
			возвращает фрагмент sql-запроса для внесения данных в БД

			$model_item_value - значение поля, которое нужно внести в БД
		* /
	}*/

	/*function getSQLselect(){
		/*
			возвращает sql-фрагмент для запроса данных из БД
		* /
	}*/

	/*function beforeDelete($params_arr){
		/*
			выполняет действие, 
			предшествующее удалению элемента

			$params_arr - массив значений из БД
		* /
	}*/
	
	/*function repareDbColumn($db_columns_info){
		/*
			каждое поле имеет описание в _models.php 
			и реализацию в таблице модели
			в зависимости от их соответствия данный метод инициализирует
			добавление, изменение, удаление столбца, 
			или ничего не делает, если соответствие является полным

			$db_columns_info - результат запроса "show columns from ..."
		* /
	}*/

	/*function performAlterTableAdd(){
		/*
			выполняем "alter table add column"
			возвращаем sql-запрос
		* /
	}*/

	/*function performAlterTableChange($db_column_name){
		/*
			выполняем "alter table change"
			возвращаем sql-запрос

			$db_column_name - $this->db_column ИЛИ $GLOBALS['db_column_bak']['old']
		* /
	}*/

	/*function performAlterTableDrop(){
		/*
			выполняем "alter table drop column" 
			ничего не возвращаем
		* /
	}*/

	/*function checkFieldInfoIsCorrect($db_columns_info,$db_column_name){
		/*
			каждое поле имеет описание в _models.php 
			и реализацию в таблице модели
			данный метод проверяет их соответствие

			$db_columns_info - результат запроса "show columns from ..."
			$db_column_name - $this->db_column ИЛИ $GLOBALS['db_column_bak']['old']
		* /
	}*/

	/*
		этот метод нужен для того, чтобы вытащить имя привязанной модели
		можно вызвать метод двумя способами:
		1 - из экземпляра, не передавая параметр, например 
		    $this->getRelModelName()
		2 - как метод статичного класса, передавая параметр $db_column, например 
		    ForeignKeyField::getRelModelName($db_column)
		    если $db_column='usages_id' то метод возвратит 'usages'
	*/
	function getRelModelName($db_column=''){
		if($db_column==''){
			//метод вызыван экземпляром
			if(empty($this->modelrel)){
				$db_column=$this->db_column;
			}else{
				return $this->modelrel;
			}
		}
		return mb_substr($db_column,0,-3);
	}
}


