<?php

class OrderField extends Field {
	function constantProps(){
		/*
			свойства могут быть неизменяемыми, например у NullBooleanField свойство null=true
			чтобы установить такие свойства, вызываем ->constantProps()
		*/
		$this->type='int';
		$this->maxlength='';
		$this->default='1';
		$this->unsigned=true;
		$this->null=false;
		$this->editable=false;
		$this->blank=true;
	}

	/*function getFormFieldHTMLtag($params_arr){
		/*
			возвращает HTML-код поля формы в форме редактирования элемента модели

			$params_arr - массив значений всех свойств элемента модели
		* /
	}*/

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

	/**
	 * переопределяемый в потомках метод, который возвращает 
	 * инициализирующее значение для данного поля на основе $hash['model_item_init_values'],
	 * для большинства полей это значение равно $hash['model_item_init_values'][$this->db_column]
	 * 
	 * $hash['model_item_init_values'] - инициализирующий массив всех значений элемента модели
	 */
	function getModelItemInitValue($hash){
		$model_item_init_values=$hash['init_values'];
		//пытаемся вытащить id из инициализирующего массива
		$id = isset($model_item_init_values['id']) ? intval($model_item_init_values['id']) : 0;
		if( $id==0 ){
			// происходит процесс добавления элемента модели 
			// вытаскиваем значение последнего элемента из привязанной модели согласно сортировке по $this->db_column
			// нужно помнить, что fieldrel может содержать несколько полей через запятую
			$last_element=ga(array(
				'classname'=>$this->model_name,
				'domain'=>(bool)($this->model_name!='structure'),//для модели structure можно сделать исключение, поскольку она вся является деревом, и данное исключение позволит делать копии структуры для новых доменов
				'fields'=>$this->db_column,//список полей которые нужно вытащить через запятую 'id,name,body'
				'filter'=>$this->getFilterString($model_item_init_values),//строка фильтра типа 'parent=32'
				'order_by'=>'-'.$this->db_column,//строка типа или '-cdate' 'parent, +name' или '__random__' - случайный порядок
				'_slice'=>'0,1',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
			));
			//определяем новое значение для 
			$result=(isset($last_element[0]))?intval($last_element[0][$this->db_column]):0;
			$result+=1;
		}else{
			// происходит процесс изменения элемента
			// проверяем имеется ли у поля параметр $this->fieldrel
			// если поле $this->fieldrel не задано, то возвращаем либо установленное значение, 
			// либо пустую строку
			if( empty($this->fieldrel) ){
				$result = isset($model_item_init_values[$this->db_column]) ? $model_item_init_values[$this->db_column] : '';
			}else{
				//проверяем был ли изменен родитель
				if( $this->checkParentsChanged($model_item_init_values) ){
					//родитель (или родители) изменился
					//находим новое значение для поля
					//вытаскиваем значение последнего элемента из привязанной модели согласно сортировке по $this->db_column
					//нужно помнить, что fieldrel может содержать несколько полей через запятую
					$last_element=ga(array(
						'classname'=>$this->model_name,
						'fields'=>$this->db_column,//список полей которые нужно вытащить через запятую 'id,name,body'
						'filter'=>$this->getFilterString($model_item_init_values),//строка фильтра типа 'parent=32'
						'order_by'=>'-'.$this->db_column,//строка типа или '-cdate' 'parent, +name' или '__random__' - случайный порядок
						'_slice'=>'0,1',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
					));
					//определяем новое значение для 
					$result=1 + (int)$last_element[0][$this->db_column];
				}else{
					//родитель не изменился
					$result=$model_item_init_values[$this->db_column];
				}
			}
		}
		// важно чтобы было возвращено числовое значение или пустая строка, но не NULL
		if( is_null($result) ){
			$result='';
		}

		return $result;
	}

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

	function checkParentsChanged($model_item_init_values){
		$parents_changed=false;
		//находим актуальную запись запросом в БД
		$current_item=gbi($this->model_name,$model_item_init_values['id']);
		$fieldrels=explode(',',$this->fieldrel);
		foreach($fieldrels as $fieldrel){
			$fieldrel=trim($fieldrel);
			if($current_item[$fieldrel]!=$model_item_init_values[$fieldrel]){
				$parents_changed=true;
				break;
			}
		}
		return $parents_changed;
	}

	function getFilterString($model_item_init_values){
		$filter='1';
		if($this->fieldrel!=''){
			$fieldrels=explode(',',$this->fieldrel);
			foreach($fieldrels as $fieldrel){
				$fieldrel=trim($fieldrel);
				$filter.=e5csql(' and `'.$this->model_name.'`.`'.$fieldrel.'`=?',defvar('',$model_item_init_values[$fieldrel]));
			}
		}
		return $filter;
	}
}
