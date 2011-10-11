<?php

class FileField extends SubfieldsInterface{
	var $subfields=array(
		'name'=>array('typelength'=>'varchar(64)','null'=>false),
		'uri'=>array('typelength'=>'varchar(255)','null'=>false),
		'ext'=>array('typelength'=>'varchar(4)','null'=>false),
		'size'=>array('typelength'=>'varchar(16)','null'=>false),
		'upload_date'=>array('typelength'=>'date','null'=>true),
	);
	var $path='/';

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
		//определяем массив значений $inputValue, 
		//который будет использован для создание тега поля формы
		$inputValue=array();
		foreach($this->subfields as $key=>$value){
			$inputValue[$key]=$params_arr[$this->db_column.'_'.$key];
		}
		//подключаем файл с шаблоном
		include(SITE_DIR.'/admin/fw/classes/templates/field/'.$this->__name__.'.php');//return 'поле формы со значением <pre>'.e5c($data).'</pre><br>';

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

	function getModelItemInitValue($_relkey_or_arr='',$path_to_file=''){
		/*
			переопределяемый в потомках метод, который возвращает 
			инициализирующее значение для данного поля на основе $model_item_init_values,
			для большинства полей это значение равно $model_item_init_values[$this->db_column]

			$model_item_init_values - инициализирующий массив всех значений элемента модели, 
			как правило, полученный из $_POST
		*/
		/****************************************************************
		переданный в данную функцию $model_item_init_values игнорируется
		ИНОГДА поле может являться core, о чем будет свидетельствовать непустой $_relkey_or_arr
		ДОБАВИЛ в метод второй параметр $path_to_file чтобы появилась возможность загружать
		файлы через консоль

		параметр $_relkey_or_arr может быть передан из функции _validateFieldsDataPresence()
		и тогда это на самом деле будет _input['_relkey']
		****************************************************************/
		if(isset($_FILES[$this->model_name])){
			$files=$_FILES[$this->model_name];
		}
		$post=(isset($_POST[$this->model_name]))?$_POST[$this->model_name]:$_relkey_or_arr;
		if(!is_array($_relkey_or_arr) && $_relkey_or_arr!=''){
			$files=$_FILES[$_relkey_or_arr];
			$post=$_POST[$_relkey_or_arr];
		}
		if( isset($path_to_file) && file_exists($path_to_file) ){
			// если передан путь к файлу, то определяем возвращаемое значение из пути
			$original_file_name=explode(DIRECTORY_SEPARATOR,$path_to_file);
			$original_file_name=last($original_file_name);
			$result=getNewFileName($original_file_name,$this->path);
		}elseif( isset($files) && !empty($files['name'][$this->db_column]) ){
			// инааче определяем возвращаемое значение из $files
			$original_file_name=$files['name'][$this->db_column];
			$result=getNewFileName($original_file_name,$this->path);
		}elseif( isset($post[$this->db_column.'_del']) && $post[$this->db_column.'_del']=='on' && $this->blank ){
			//возможно, что старый файл пожелали удалить, 
			//тогда возвращаем строку "null", а не пустое значение,
			//чтобы класс Model запусил метод getSQLupdate()
			//но делаем это лишь если поле является НЕ обязательным
			$result='null';
		}else{
			//также возможно, что файл уже сохранен, о чем будет свидетельствовать bak-поле
			//тогда возвращаем значение из bak
			if(isset($post[$this->db_column.'_name'])){
				$result=$post[$this->db_column.'_name'];
			}elseif(isset($post[$this->db_column.'_bak'])){
				$result=$post[$this->db_column.'_bak'];
			}else{
				$result='';
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

	/*function getDbColumnDefinition($key){
		/*
			возвращает фрагмент sql-запроса с описанием конкретного поля 
			и является частью таких запросов как "create table", 
			"alter table add column", "alter table change column"

			$key - ключ субполя
		* /
	}*/

	function getSQLupdate($_relkey,$path_to_file=''){
		/**_echo('$_relkey in getSQLupdate: '.$_relkey.'');*/
		/*
			возвращает фрагмент sql-запроса для внесения данных в БД

			$model_item_value - значение поля, которое нужно внести в БД
		*/
		/****************************************************************
		здесь мы должны сохранить файл, определить его размер, расширение и прочие атрибуты
		и сформировать фрагмент sql-запроса на запись всех этих дел
		кроме того, нам нужно удалить старый файл, если он был у элемента 
		или если пользователь зачекал соответствующий чекбокс
		****************************************************************/
		$result='';
		if( !empty($path_to_file) && file_exists($path_to_file) ){
			$original_file_name=explode(DIRECTORY_SEPARATOR,$path_to_file);
			$original_file_name=last($original_file_name);
			$file_to_copy=$path_to_file;
		}elseif( !empty($_relkey) && isset($_FILES[$_relkey]) ){
			$files=$_FILES[$_relkey];
			$post=$_POST[$_relkey];
		}elseif( isset($this->model_name) && isset($_FILES[$this->model_name]) ){
			$files=$_FILES[$this->model_name];
			$post=$_POST[$this->model_name];
		}
		if( isset($files) && !empty($files['name'][$this->db_column]) ){
			//проверяем, загрузился ли файл во временную папку
			if(empty($files['tmp_name'][$this->db_column])){_die(sprintf('файл «%s» не был загружен во временную папку, вероятно из-за ограничения PHP на размер загружаемого файла',$files['name'][$this->db_column]));}
			$original_file_name=$files['name'][$this->db_column];
			$file_to_copy=$files['tmp_name'][$this->db_column];
		}
		if( $original_file_name && $file_to_copy ){
			$f=array();
			// проверяем, имеются ли права на запись в папку $this->path
			if(!is_writable(SITE_DIR.$this->path)){_die(sprintf('папка «%s» не имеет прав на запись',$this->path));}
			//определяем имя нового файла
			$f['name']=getNewFileName($original_file_name,$this->path);
			if(empty($f['name'])){_die('не получилось определить новое название для файла «%s»',$original_file_name);}
			//сохраняем файл
			if( !copy($file_to_copy,SITE_DIR.$this->path.$f['name']) ){_die('не могу сохранить файл '.$this->path.$f['name']);}
			//определяем его параметры
			$f['uri']=$this->path.$f['name'];
			$f['size']=filesize(SITE_DIR.$f['uri']);
			$rpos=mb_strrpos($f['name'],'.');
			$f['ext']=strtolower(mb_substr($f['name'],($rpos+1)));
			if(in_array($f['ext'],explode(',','gif,jpg,png,swf,psd,bmp,tiff,jpc,jp2,jpx,jb2,swc,iff,wbmp,xbm'))){
				list($f['width'],$f['height'])=getimagesize(SITE_DIR.$f['uri']);
			}
			$f['upload_date']=date('Y-m-d');
			//формируем фрагмент запроса
			foreach($this->subfields as $key=>$value){
				$result.='`'.$this->model_name.'`.`'.$this->db_column.'_'.$key.'` = "'.mysql_escape_string($f[$key]).'",';
			}
			// если НЕ происходит процесс копирования
			// то удаляем старый файл
			// делаем это в самом конце, чтобы не повлиять на формирование нового названия файла
			// для того, чтобы оно совпало с названием, сформированным в getModelItemInitValue()
			if(intval(ORIGINAL_ITEM_ID)==0){
				if(isset($post) && $post[$this->db_column.'_bak']!=''){
					$this->_unlinkOldFile($post[$this->db_column.'_bak']);
				}
			}
		}elseif( isset($post) ){
			//возможно, что старый файл пожелали удалить
			if(isset($post[$this->db_column.'_del']) && $post[$this->db_column.'_del']=='on'){
				//удаляем файл с сервера
				$this->_unlinkOldFile($post[$this->db_column.'_bak']);
				//формируем фрагмент запроса
				foreach($this->subfields as $key=>$value){
					//$result.='`'.$this->model_name.'`.`'.$this->db_column.'_'.$key.'` = "",';
					if($value['typelength'] == 'date') {
						// для mysql 5
						$result.='`'.$this->model_name.'`.`'.$this->db_column.'_'.$key.'` = null,';
					}else{
						$result.='`'.$this->model_name.'`.`'.$this->db_column.'_'.$key.'` = "",';
					}
				}
			}elseif(intval(ORIGINAL_ITEM_ID)>0){
				// происходит процесс копирования
				// необходимо вытащить из БД данные по $subfields
				// найти исходный файл, сделать его копию
				// и продублировать $subfields, внеся изменения в name и uri
				$original_item_data=gbi($this->model_name,ORIGINAL_ITEM_ID);
				if(!empty($original_item_data[$this->db_column.'_name'])){
					$new_file_name=getNewFileName($original_item_data[$this->db_column.'_name'],$this->path);
					if( !copy(SITE_DIR.$original_item_data[$this->db_column.'_uri'],SITE_DIR.$this->path.$new_file_name) ){_die('не могу сохранить файл '.$this->path.$new_file_name);}
					foreach($this->subfields as $key=>$value){
						if($key=='name'){
							$value=$new_file_name;
						}elseif($key=='uri'){
							$value=$this->path.$new_file_name;
						}else{
							$value=$original_item_data[$this->db_column.'_'.$key];
						}
						$result.=e5csql('`@`.`@_@`=?,',$this->model_name,$this->db_column,$key,$value);
					}
				}
			}
		}
		return $result;
	}

	/*function getSQLselect(){
		/*
			возвращает sql-фрагмент для запроса данных из БД
		* /
	}*/

	function beforeDelete($params_arr){
		/*
			выполняет действие, 
			предшествующее удалению элемента

			$params_arr - массив значений из БД
		*/
		//путь к файлу
		if($params_arr[$this->db_column]!=''){
			$file_uri=$params_arr[$this->db_column];
		}elseif(isset($params_arr[$this->db_column.'_uri'])){
			_log('странная ситуация - значение $params_arr[$this->db_column] пусто, а значение $params_arr[$this->db_column+_uri] не пусто!');
			$file_uri=$params_arr[$this->db_column.'_uri'];
		}
		if(isset($file_uri)){
			//удаляем файл с сервера
			$this->_unlinkOldFile($file_uri);
		}
	}

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

	/*function performAlterTableAdd($key){
		/*
			выполняем "alter table add column"
			возвращаем sql-запрос
		* /
	}*/

	/*function performAlterTableChange($db_column_bak,$key){
		/*
			выполняем "alter table change"
			возвращаем sql-запрос

			$db_column_bak - правильное название столбца ($this->db_column ИЛИ $GLOBALS['db_column_bak']['old'])
			$key - 
		* /
	}*/

	/*function performAlterTableDrop(){
		/*
			выполняем "alter table drop column" 
			ничего не возвращаем
		* /
	}*/

	/*function checkFieldInfoIsCorrect($db_columns_info,$db_column_bak,$key){
		/*
			каждое поле имеет описание в _models.php 
			и реализацию в таблице модели
			данный метод проверяет их соответствие

			$db_columns_info - результат запроса "show columns from ..."
			$db_column_bak - $this->db_column ИЛИ $GLOBALS['db_column_bak']['old']
			$key - ключ субполя
		* /
	}*/

	//-----------------------------------------------------------------

	// используется в model.class.php
	function _checkMatching($_relkey=''){
		$result=true;
		if($this->match!=''){
			if($_relkey!=''){
				$files=$_FILES[$_relkey];
			}else{
				$files=$_FILES[$this->model_name];
			}
			//проверяем передавался ли файл
			if(!empty($files)){
				$original_file_name=$files['name'][$this->db_column];
				if($original_file_name!=''){
					$result=(bool)preg_match($this->match,$original_file_name);
				}
			}
		}

		return $result;
	}
}


?>