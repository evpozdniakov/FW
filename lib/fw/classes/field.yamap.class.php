<?php

class YamapField extends SubfieldsInterface {
	var $subfields=array(
		'lat'=>array('typelength'=>'varchar(24)','null'=>false),
		'lng'=>array('typelength'=>'varchar(24)','null'=>false),
		'zoom'=>array('typelength'=>'int(11)','null'=>false),
	);

	/*function constantProps(){
		/*
			свойства могут быть неизменяемыми, например у NullBooleanField свойство null=true
			чтобы установить такие свойства, вызываем ->constantProps()
		* /
	}*/

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
	 * инициализирующее значение для данного поля на основе $hash['init_values'],
	 * для большинства полей это значение равно $hash['init_values'][$this->db_column]
   * 
	 * $hash['init_values'] - инициализирующий массив всех значений элемента модели
	 */
	function getModelItemInitValue($hash){
		/*
		если передан $hash['coords'], то происходит добавление координат с помощью ->gmi()
		и нужно убедиться, что строка $hash['coords'] содержит два числа, похожие на широту и долготу
		и затем вернуть их

		если передан $hash['db_values'], то происходит воспроизведение элемента по id
		и нужно вернуть имеющееся в БД значение

		если передан $hash['init_values'], то происходит создание или изменение элемента
		и нужно определить название нового файла, переданного в $_FILES
		
		если передан $hash['_relkey'], то поле является core
		и нужно сделать все то же самое, что и при создании/изменении элемента,
		только нужно иначе работать с $_FILES и $_POST
		*/
		$data=array();
		if( !empty($hash['coords']) ){
			$coords=explode(',',$hash['coords']);
			$lat=floatval(trim($coords[0]));
			$lng=floatval(trim($coords[1]));
			$zoom=intval( defvar(15,trim($coords[2])) );
			if( $lat && $lng && $zoom){
				if( $lat>-90 && $lat<90 && $lng>-180 && $lng<180 && $zoom > 0 && $zoom < 19 ){
					$data[$this->db_column.'_lat']=$lat;
					$data[$this->db_column.'_lng']=$lng;
					$data[$this->db_column.'_zoom']=$zoom;
				}
			}
		}elseif( !empty($hash['init_values']) ){
			$data=$hash['init_values'];
		}elseif( !empty($hash['db_values']) ){
			$data=$hash['db_values'];
		}
		if( !empty($data[$this->db_column.'_lat']) && !empty($data[$this->db_column.'_lng']) ){
			$zoom=defvar(15,$data[$this->db_column.'_zoom']);
			$result=sprintf('%s,%s,%d',$data[$this->db_column.'_lat'],$data[$this->db_column.'_lng'],$zoom);
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

	/**
	 * метод возвращает фрагмент sql-запроса для внесения данных в БД
	 *
	 * строка $init_string может содержать координаты через запятую
	 */
	function getSQLupdate($coords){
		list($lat,$lng,$zoom)=explode(',',$coords);
		//формируем фрагмент запроса
		$result=sprintf('`%s`.`%s_lat`=%s,`%s`.`%s_lng`=%s,`%s`.`%s_zoom`=%s,'
			,$this->model_name
			,$this->db_column
			,mysql_escape_string($lat)
			,$this->model_name
			,$this->db_column
			,mysql_escape_string($lng)
			,$this->model_name
			,$this->db_column
			,mysql_escape_string($zoom)
		);
		// $result='`'.$this->model_name.'`.`'.$this->db_column.'_lat`='.mysql_escape_string($lat).',`'.$this->model_name.'`.`'.$this->db_column.'_lng`='.mysql_escape_string($lng).',';
		return $result;
	}

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
}
