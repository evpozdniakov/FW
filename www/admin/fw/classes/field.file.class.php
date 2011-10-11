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

	/**
	 * переопределяемый в потомках метод, который возвращает 
	 * инициализирующее значение для данного поля на основе $hash['init_values'],
	 * для большинства полей это значение равно $hash['init_values'][$this->db_column]
   * 
	 * $hash['init_values'] - инициализирующий массив всех значений элемента модели
	 */
	function getModelItemInitValue($hash){
		// _log('call getModelItemInitValue',$hash);
		/*
		если передан $hash['path2file'], то происходит добавление локального файла
		и нужно определить и вернуть название нового файла

		если передан $hash['db_values'], то происходит воспроизведение элемента по id
		и нужно вернуть имеющееся в БД значение

		если передан $hash['init_values'], то происходит создание или изменение элемента
		и нужно определить название нового файла, переданного в $_FILES
		
		если передан $hash['_relkey'], то поле является core
		и нужно сделать все то же самое, что и при создании/изменении элемента,
		только нужно иначе работать с $_FILES и $_POST
		*/
		$result='';
		if( !empty($hash['_relkey']) && !empty($_FILES[$hash['_relkey']]) ){
			$files=$_FILES[$hash['_relkey']];
			$post=$_POST[$hash['_relkey']];
		}elseif( !empty($_FILES[$this->model_name]) ){
			$files=$_FILES[$this->model_name];
			// if( isset($_POST[$this->model_name]) ){
				$post=$_POST[$this->model_name];
			// }else{
			// 	$post=$hash['init_values'];
			// }
		}elseif( !empty($hash['path2file']) && file_exists($hash['path2file']) ){
			// возвращаем имя нового файла (каким оно было бы, если файл сохранили)
			$original_file_name=explode(DIRECTORY_SEPARATOR,$hash['path2file']);
			$original_file_name=last($original_file_name);
			// $this->path это параметр поля в котором содержится путь к папке с файлами
			$result=getNewFileName($original_file_name,$this->path);
		}elseif( !empty($hash['db_values']) ){
			// возвращаем имя файла из БД
			$result=$hash['db_values'][$this->db_column.'_name'];
		}
		if( empty($result) ){
			if( isset($files) && !empty($files['name'][$this->db_column]) ){
				// определяем имя нового файла из $files
				$original_file_name=$files['name'][$this->db_column];
				$result=getNewFileName($original_file_name,$this->path);
			}elseif( isset($post[$this->db_column.'_del']) && $post[$this->db_column.'_del']=='on' && $this->blank===true ){
				// возможно, что старый файл пожелали удалить, 
				// тогда возвращаем строку "null", а не пустое значение,
				// чтобы класс Model запусил метод getSQLupdate()
				// но делаем это лишь если поле НЕ является обязательным
				$result='null';
			}elseif( isset($post[$this->db_column.'_bak']) ){
				// происходит изменение элемента без изменения файла
				$result=$post[$this->db_column.'_bak'];
			}else{
				// ничего не возвращаем
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

	/**
	 * метод возвращает фрагмент sql-запроса для внесения данных в БД
	 *
	 * здесь мы должны сохранить файл, определить его размер, расширение и прочие атрибуты
	 * и сформировать фрагмент sql-запроса на запись всех этих дел
	 * кроме того, нам нужно удалить старый файл, если он был у элемента 
	 * или если пользователь зачекал соответствующий чекбокс
	 *
	 * в первую очередь проверяем массив $_FILES
	 * в последнюю очередь проверяем $hash['path2file']
	 * поскольку этот параметр будет иметь корректное значение (полный путь к файлу) 
	 * только в случае добавления файла "вручную"
	 */
	function getSQLupdate($hash){
		$result='';
		if( !empty($hash['_relkey']) && isset($_FILES[$hash['_relkey']]) ){
			$files=$_FILES[$hash['_relkey']];
			$post=$_POST[$hash['_relkey']];
		}elseif( isset($this->model_name) && isset($_FILES[$this->model_name]) ){
			$files=$_FILES[$this->model_name];
			$post=$_POST[$this->model_name];
		}elseif( !empty($hash['path2file']) && file_exists($hash['path2file']) ){
			$original_file_name=explode(DIRECTORY_SEPARATOR,$hash['path2file']);
			$original_file_name=last($original_file_name);
			$file_to_copy=$hash['path2file'];
		}
		if( isset($files) && !empty($files['name'][$this->db_column]) ){
			//проверяем, загрузился ли файл во временную папку
			if(empty($files['tmp_name'][$this->db_column])){
				_log(sprintf('теоретически данная проблема должна выявляться в процессе валидации. файл «%s» не был загружен во временную папку, вероятно из-за ограничения PHP на размер загружаемого файла',$files['name'][$this->db_column]));
			}
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
				$result.=e5csql('`@`.`@_@`=?,',$this->model_name,$this->db_column,$key,$f[$key]);
			}
			// если НЕ происходит процесс копирования
			// то удаляем старый файл
			// делаем это в самом конце, чтобы не повлиять на формирование нового названия файла
			// для того, чтобы оно совпало с названием, сформированным в getModelItemInitValue()
			if( COPY_PROCESS!==true ){
				if(isset($post) && $post[$this->db_column.'_bak']!=''){
					$this->_unlinkOldFile($post[$this->db_column.'_bak']);
				}else{
					$dbq=new DBQ('select @ from @ where id=?',$this->db_column.'_uri',$this->model_name,ORIGINAL_ITEM_ID);
					$this->_unlinkOldFile($dbq->item);
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
			}elseif( COPY_PROCESS===true ){
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

	/**
	 * выполняет действие, 
	 * предшествующее удалению элемента
	 * 
	 * $params_arr - массив значений из БД
	 */
	function beforeDelete($params_arr){
		//путь к файлу
		if( !empty($params_arr[$this->db_column]) ){
			$file_uri=$this->path.$params_arr[$this->db_column];
		}elseif( isset($params_arr[$this->db_column.'_uri']) ){
			_log('странная ситуация - значение $params_arr[$this->db_column] пусто, а значение $params_arr[$this->db_column_uri] не пусто!');
			$file_uri=$params_arr[$this->db_column.'_uri'];
		}
		if( isset($file_uri) ){
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

	/**
	 * метод должен проверить соответствие файла формату, 
	 * заданному в регулярном выражении $this->match
	 * в первую очередь проверяем массив $_FILES
	 * в последнюю очередь проверяем $hash['path2file']
	 * поскольку этот параметр будет иметь корректное значение (полный путь к файлу) 
	 * только в случае добавления файла "вручную"
	 */
	function _checkMatching($hash){
		$bool=true;
		$match=$this->match;
		if( empty($match) && is_a($this,'ImageField') ){
			$match=defvar('/\.(jpg|jpeg|gif|png|swf)$/i',$this->match);
		}
		if( !empty($match) ){
			if( !empty($hash['_relkey']) ){
				$original_file_name=$_FILES[$hash['_relkey']]['name'][$this->db_column];
			}elseif( isset($this->model_name) && isset($_FILES[$this->model_name]) ){
				$original_file_name=$_FILES[$this->model_name]['name'][$this->db_column];
			}elseif( !empty($hash['path2file']) ){
				$original_file_name=explode(DIRECTORY_SEPARATOR,$hash['path2file']);
				$original_file_name=last($original_file_name);
			}
			if( !empty($original_file_name) && $original_file_name!=='null' ){
				$bool=(bool)preg_match($match,$original_file_name);
			}
		}

		return $bool;
	}

	/**
	 * метод должен проверить загрузку файла.
	 * в первую очередь проверяем массив $_FILES
	 * в последнюю очередь проверяем $hash['path2file']
	 * поскольку этот параметр будет иметь корректное значение (полный путь к файлу) 
	 * только в случае добавления файла "вручную"
	 */
	function _checkLoading($hash){
		$bool=true;
		if( !empty($hash['_relkey']) && isset($_FILES[$hash['_relkey']]) ){
			$files=$_FILES[$hash['_relkey']];
		}elseif( isset($this->model_name) && isset($_FILES[$this->model_name]) ){
			$files=$_FILES[$this->model_name];
		}elseif( !empty($hash['path2file']) ){
			if( mb_strpos($hash['path2file'],DIRECTORY_SEPARATOR)!==false && !file_exists($hash['path2file']) ){
				$bool=false;
			}
		}
		if( isset($files) && !empty($files['name'][$this->db_column]) ){
			if( empty($files['tmp_name'][$this->db_column]) ){
				$bool=false;
			}
		}
		return $bool;
	}
}
