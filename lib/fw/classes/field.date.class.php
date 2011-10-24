<?php

class DateField extends Field{
	var $null=true;

	function constantProps(){
		/*
			свойства могут быть неизменяемыми, например у NullBooleanField свойство null=true
			чтобы установить такие свойства, вызываем ->constantProps()
		*/
		$this->type='date';
		$this->maxlength='';
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

	/*function getFormFieldHTMLWrap($input_tag,$error_arr){
		/*
			возвращает тег поля формы завернутый в <p> или <td>, в зависимости от $this->mode

			$input_tag - тег поля формы
			$error_arr - массив с классом и текстом ошибки
		* /
	}*/

	/*function getFormFieldHTMLWrap($input_tag,$errorMessage){
		/*
			возвращает тег поля формы завернутый в <p> или <td>, в зависимости от $this->mode
			кромет того, может вернуть массив в котором вторым элементом будет $header
			- то есть заголовок для столбца полей, редактируемых в режиме "core"

			$input_tag - тег поля формы
			$errorMessage - true если поле было не заполнено, или заполнено с ошибкой
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
		if(!empty($model_item_init_values[$this->db_column]) && parseDate($model_item_init_values[$this->db_column])!==false){
			// возможно, что для этого поля задается инициализирующее значение
			$result=$model_item_init_values[$this->db_column];
		}elseif($model_item_init_values['id']==0 && $this->default=='now()'){
			// возможно, что для этого поля по умолчанию выставлено "now()"
			$result=date('Y-m-d');
		}else{
			//иначе получаем значение даты из input.datepicker
			$date_arr=explode('.',$model_item_init_values[$this->db_column]);
			$day=$date_arr[0];
			$month=$date_arr[1];
			$year=$date_arr[2];
			//проверяем полученную дату на корректность
			if(checkdate((int)$month,(int)$day,(int)$year)){
				$result=''.$year.'-'.$month.'-'.$day.'';
			}
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

	/*function beforeDelete(){
		/*
			выполняет действие, 
			предшествующее удалению элемента
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

	function checkFieldInfoIsCorrect($db_columns_info,$db_column_bak){
		/*
			каждое поле имеет описание в _models.php 
			и реализацию в таблице модели
			данный метод проверяет их соответствие

			$db_columns_info - результат запроса "show columns from ..."
			$db_column_bak - $this->db_column ИЛИ $GLOBALS['db_column_bak']['old']
		*/
		$db_column_info=$db_columns_info[$db_column_bak];
		//предполагаем худшее
		$result=false;
		// проверяем название поля, его тип и длину, а также ключ NULL
		$typelength=$this->_getSQLcolumnType();
		$null=(($this->null)?'YES':'NO');
		if(true 
			&& $db_column_info['Field']==$this->db_column
			&& $db_column_info['Type']==$typelength
			&& $db_column_info['Null']==$null
		){
			// проверяем значение по-умолчанию
			if($db_column_info['Default']==$this->default){
				$result=true;
			}elseif($db_column_info['Default']=='' && $this->default=='now()'){
				$result=true;
			}
		}else{
			_print_r('here filed.date.class.php');
			_print_r('$db_column_info',$db_column_info);
			_print_r('$typelength',$typelength);
			_print_r('null',$null);
			_print_r(($db_column_info['Field']==$this->db_column),($db_column_info['Type']==$typelength),($db_column_info['Null']==$null));
		}
		if(!$result && p2v('action')=='synchro'){
			_print_r('Default='.$db_column_info['Default'],'$this->default='.$this->default);
			_print_r('RESULT is FALSE (Field class)', '$this',$this,'$db_column_info',$db_columns_info[$this->db_column],'getSQLcreate(): '.$this->getSQLcreate(),'$db_column_bak:'.$db_column_bak,'$typelength:'.$typelength,'$null:'.$null,'$this->default:'.$this->default);
		}
		return $result;
	}
}
