<?php

class SubfieldsInterface extends Field {
	var $subfields=array();

	/*function constantProps(){
		/*
			свойства могут быть неизменяемыми, например у NullBooleanField свойство null=true
			чтобы установить такие свойства, вызываем ->constantProps()
		* /
	}*/

	function getFormFieldHTMLtag($params_arr){
		/*
			возвращает HTML-код поля формы в форме редактирования элемента модели

			$params_arr - массив значений всех свойств элемента модели
		*/
		$inputValue=array();
		foreach($this->subfields as $key=>$value){
			$inputValue[$key]=$params_arr[$this->db_column.'_'.$key];
		}
		//подключаем файл с шаблоном
		include(LIB_DIR.'/fw/classes/templates/field/'.$this->__name__.'.php');//return 'поле формы со значением <pre>'.e5c($data).'</pre><br>';

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

	/*function getModelItemInitValue($_relkey_or_arr){
		/*
			переопределяемый в потомках метод, который возвращает 
			инициализирующее значение для данного поля на основе $post, где
			$post=(isset($_POST[$this->model_name]))?$_POST[$this->model_name]:$_relkey_or_arr;
		* /
	}*/

	function getSQLcreate(){
		/*
			возвращает фрагмент sql-запроса create table, 
			который отвечает за описание конкретного поля
		*/
		$result='';
		foreach($this->subfields as $key=>$value){
			$result.='`'.$this->db_column.'_'.$key.'` '.$this->getDbColumnDefinition($key).','."\n";
		}
		return $result;
	}

	function getDbColumnDefinition($key){
		/*
			возвращает фрагмент sql-запроса с описанием конкретного поля 
			и является частью таких запросов как "create table", 
			"alter table add column", "alter table change column"

			$key - ключ субполя
		*/
		$result=$this->subfields[$key]['typelength'].' ';
		$result.=( !$this->subfields[$key]['null'] )?'not null':'';
		return $result;
	}

	function getSQLupdate($_relkey_or_arr){
		/*
			возвращает фрагмент sql-запроса для внесения данных в БД

			значения берутся из $post, где $post=(isset($_POST[$this->model_name]))?$_POST[$this->model_name]:$_relkey_or_arr;
		*/
		$post=(isset($_POST[$this->model_name]))?$_POST[$this->model_name]:$_relkey_or_arr;
		//формируем фрагмент запроса
		$result='';
		foreach($this->subfields as $key=>$value){
			$result.='`'.$this->model_name.'`.`'.$this->db_column.'_'.$key.'` = "'.mysql_escape_string($post[$this->db_column.'_'.$key]).'",';
		}
		return $result;
	}

	function getSQLselect(){
		/*
			возвращает sql-фрагмент для запроса данных из БД
		*/
		$result='';
		foreach($this->subfields as $key=>$value){
			$result.='`'.$this->model_name.'`.`'.$this->db_column.'_'.$key.'`,';
		}
		return $result;
	}

	/*function beforeDelete($params_arr){
		/*
			выполняет действие, 
			предшествующее удалению элемента

			$params_arr - массив значений из БД
		* /
	}*/
	
	function repareDbColumn($db_columns_info){
		/*
			каждое поле имеет описание в _models.php 
			и реализацию в таблице модели
			в зависимости от их соответствия данный метод инициализирует
			добавление, изменение, удаление столбца, 
			или ничего не делает, если соответствие является полным

			$db_columns_info - результат запроса "show columns from ..."
		*/
		//в первую очередь определяем старое имя столбца (ведь оно могло измениться)
		$db_column_bak=$this->_getDbColumnNameBeforeChanges();
		foreach($this->subfields as $key=>$value){
			//если в таблице модели присутствует информация о текущем поле
			if(!empty($db_columns_info[$db_column_bak.'_'.$key])){
				//если поле таблицы не отличается от поля модели
				if( $this->checkFieldInfoIsCorrect($db_columns_info,$db_column_bak,$key) ){
					//то ничего не делаем
				}else{
					//иначе выполняем ALTER TABLE CHANGE
					$result['change'][]=$this->performAlterTableChange($db_column_bak,$key);
				}
			}else{
				//выполняем ALTER TABLE ADD если информация о поле в таблице отсутствует
				$result['add'][]=$this->performAlterTableAdd($key);
			}
		}
		return $result;
	}

	function performAlterTableAdd($key){
		/*
			выполняем "alter table add column"
			возвращаем sql-запрос
		*/
		$dbq=new DBQ('alter table `'.$this->model_name.'` add column `'.$this->db_column.'_'.$key.'` '.$this->getDbColumnDefinition($key));
		return $dbq->query;
	}

	function performAlterTableChange($db_column_bak,$key){
		/*
			выполняем "alter table change"
			возвращаем sql-запрос

			$db_column_bak - правильное название столбца ($this->db_column ИЛИ $GLOBALS['db_column_bak']['old'])
			$key - 
		*/
		$dbq=new DBQ('alter table `'.$this->model_name.'` change column `'.$db_column_bak.'_'.$key.'` `'.$this->db_column.'_'.$key.'` '.$this->getDbColumnDefinition($key));
		return $dbq->query;
	}

	function performAlterTableDrop(){
		/*
			выполняем "alter table drop column" 
			ничего не возвращаем
		*/
		foreach($this->subfields as $key=>$value){
			$dbq=new DBQ('alter table `@` drop column `@`',$this->model_name,$this->db_column.'_'.$key);
		}
	}

	function checkFieldInfoIsCorrect($db_columns_info,$db_column_bak,$key){
		/*
			каждое поле имеет описание в _models.php 
			и реализацию в таблице модели
			данный метод проверяет их соответствие

			$db_columns_info - результат запроса "show columns from ..."
			$db_column_bak - $this->db_column ИЛИ $GLOBALS['db_column_bak']['old']
			$key - ключ субполя
		*/
		$db_column_info=$db_columns_info[$db_column_bak.'_'.$key];
		$result=(bool)(true 
			&& $db_column_info['Field']==$this->db_column.'_'.$key
			&& $db_column_info['Type']==$this->subfields[$key]['typelength']
			&& $db_column_info['Null']==(($this->subfields[$key]['null'])?'YES':'NO')
			&& $db_column_info['Default']==$this->subfields[$key]['default']
		);
		/*
			chb($result);
			chb($db_column_info['Field']==$this->db_column.'_'.$key);
			chb($db_column_info['Type']==$this->subfields[$key]['typelength']);
			chb($db_column_info['Null']==(($this->subfields[$key]['null'])?'YES':''));
			chb($db_column_info['Default']==$this->subfields[$key]['default']);
		*/
		if(!$result){
			_log('RESULT is FALSE', '$this',$this,'$db_column_info',$db_columns_info[$this->db_column],'getSQLcreate(): '.$this->getSQLcreate(),'$db_column_bak:'.$db_column_bak,'$typelength:'.$typelength,'$null:'.$null,'$this->default:'.$this->default);
		}
		return $result;
	}
}
