<?php

class ManyToManyField extends Field{
	//если модель Users имеет поле ->users_projects=new ManyToManyField()
	//то это предполагает наличие вспомогательной таблицы users_projects_rel
	//содержащей поля users_id и projects_id
	//наличие поля ManyToManyField не превносит изменений в таблицу модели
	//поскольку использует вспомогательную таблицу
	//поэтому такие характеристики как $type, $maxlength и пр. не определяются

	/*function constantProps(){
		/*
			свойства могут быть неизменяемыми, например у NullBooleanField свойство null=true
			чтобы установить такие свойства, вызываем ->constantProps()
		* /
		$this->null=false;
	}*/

	function getFormFieldHTMLtag($params_arr){
		/*
			возвращает HTML-код поля формы в форме редактирования элемента модели

			$params_arr - массив значений всех свойств элемента модели
		*/
		//создаем объект привязанной модели
		$view_template_file=SITE_DIR.'/admin/models/'.$this->model_name.'/'.$this->db_column.'.viewtemplate.php';
		if(file_exists($view_template_file)){
			//подключаем файл со специальным шаблоном
			$form_items=new FormItems();
			include($view_template_file);
		}else{
			$rel_model_name=$this->_getModelrelOrFindIt();//админка основной модели (products для products_usages_rel)
			$obj_rel_model=getModelObject($rel_model_name);
			//получаем список элементов объекта
			$mmanager=$obj_rel_model->objects();
			$rel_model_elements=$mmanager->_slice(0);
			if(is_array($rel_model_elements)){
				foreach($rel_model_elements as $element){
					$subresult['value']=$element['id'];
					$subresult['title']=$obj_rel_model->__str__($element);
					$subresult['selected']=$this->_getIsSelected($params_arr['id'],$element['id']);
					$result_elements[]=$subresult;
				}
			}
			include(LIB_DIR.'/fw/classes/templates/field/'.$this->__name__.'.php');
		}
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
	 * инициализирующее значение для данного поля на основе $hash['model_item_init_values'],
	 * для большинства полей это значение равно $hash['model_item_init_values'][$this->db_column]
	 * 
	 * $hash['model_item_init_values'] - инициализирующий массив всех значений элемента модели
	 */
	function getModelItemInitValue($hash){
		// если передан $hash['get_stored_by_id']
		// значит нужно забрать данные из БД
		if( $hash['get_stored_by_id'] ){
			$dbq=new DBQ('select @ from @ where @=?',$this->_getKey2(), $this->_getRelTableName(), $this->_getKey1(), $hash['get_stored_by_id']);
			if( $dbq->rows ){
				$result=kgi($this->_getKey2(), $dbq->items);
			}
		}else{
			$model_item_init_values=$hash['init_values'];
			if($model_item_init_values[$this->db_column]!=''){
				$result=implode(',',$model_item_init_values[$this->db_column]);
			}
		}
		return $result;
	}

	function getSQLcreate(){
		/*
			возвращает фрагмент sql-запроса create table, 
			который отвечает за описание конкретного поля

			на самом деле для полей ManyToManyField и OneToManyField сделано 
			исключение, и они возвращают код для создания вспомогательной таблицы
		*/
		$tabname=$this->_getRelTableName();
		$key1=$this->_getKey1();
		$key2=$this->_getKey2();
		$result='
			create table `'.$tabname.'`(
				`'.$key1.'` int not null,
				`'.$key2.'` int not null
			)
		';
		$result=trim($result);
		$result=str_replace("\t",'',$result);
		return $result;
	}

	function getDbColumnDefinition(){
		/*
			возвращает фрагмент sql-запроса с описанием конкретного поля 
			и является частью таких запросов как "create table", 
			"alter table add column", "alter table change column"
		*/
		_die('вызван метод ManyToManyField->getDbColumnDefinition(), чего не должно быть');
	}

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

	function beforeDelete($params_arr){
		//как выяснилось, при пакетном удалении может случится вызов метода без параметра $params_arr
		//в этом случае ничего выполнять не нужно, потому что все уже выполнено
		if(!is_array($params_arr)){return;}
		/*
			выполняет действие, 
			предшествующее удалению элемента

			$params_arr - массив значений из БД
		*/

		/*
			нужно пройтись по вспомогательной таблице и
			1. создать объекты привязанной модели и все их удалить
			!
			!
			! вот здесь как выяснилось вопрос очень спорный, поскольку возможна такая ситуация, когда связку нужно удалить, 
			! а вот привязанный объект нужно оставить
			! нужно подумать каким образом определять, нужно ли удалять привязанные элементы, или нет.
			!
			!
			2. удалить строки из вспомогательной таблицы
		*/
		//получаем некоторые вспомогательные данные
		$tabname=$this->_getRelTableName();
		$key1=$this->_getKey1();
		$key2=$this->_getKey2();
		$rel_model_name=$this->_getModelrelOrFindIt();//вызываем метод для модели основной модели (products для products_usages_rel)
		//_log($tabname);_log($key1);_log($key2);_log($rel_model_name);

		//шаг 1
		/*
			$dbq=new DBQ('select * from '.$tabname.' where @=?',$key1,$params_arr['id']);
			if(is_array($dbq->items)){
				//создаем объект модели, привязанной к текущему
				$obj_model_rel=getModelObject($rel_model_name);
				foreach($dbq->items as $item){
					//создаем элемент модели
					$model_item=new $obj_model_rel(array('id'=>$item[$key2]));
					//удаляем его
					$model_item->delete();
				}
			}
		*/

		//шаг 2
		$dbq=new DBQ('delete from '.$tabname.' where @=?',$key1,$params_arr['id']);
	}
	
	function repareDbColumn($db_columns_info){
		/*
			каждое поле имеет описание в _models.php 
			и реализацию в таблице модели
			в зависимости от их соответствия данный метод инициализирует
			добавление, изменение, удаление столбца, 
			или ничего не делает, если соответствие является полным

			$db_columns_info - результат запроса "show columns from ..."
		*/
		_die('вызван метод ManyToManyField->repareDbColumn(), чего не должно быть');
	}

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

	function performAlterTableDrop(){
		/*
			выполняем "alter table drop column" 
			ничего не возвращаем

			в данном случае выполняем удаление вспомогательной таблицы
		*/
		$result=false;
		$dbq=new DBQ('show tables like ?',$this->_getRelTableName());
		if($dbq->rows==0){
			$dbq=new DBQ('drop table '.$this->_getRelTableName());
		}
	}

	/*function checkFieldInfoIsCorrect($db_columns_info,$db_column_name){
		/*
			каждое поле имеет описание в _models.php 
			и реализацию в таблице модели
			данный метод проверяет их соответствие

			$db_columns_info - результат запроса "show columns from ..."
			$db_column_name - $this->db_column ИЛИ $GLOBALS['db_column_bak']['old']
		* /
	}*/

	function try2createTable(){
		$result=false;
		$dbq=new DBQ('show tables like ?',$this->_getRelTableName());
		if($dbq->rows==0){
			$dbq=new DBQ($this->getSQLcreate());
			$result=true;
		}
		return $result;
	}

	function performFieldDataSave($item_id,$init_values){
		//удаляем из таблицы вспомогательной (связующей) таблицы все поля относящиеся к первому столбцу
		$dbq=new DBQ('delete from @ where @=?',$this->_getRelTableName(),$this->_getKey1(),$item_id);
		//добавляем поля заново
		if( !empty($init_values) ){
			if( is_array($init_values) ){
				$data_arr=$init_values;
			}else{
				$data_arr=explode(',',$init_values);
			}
			$fields_data=$this->_getFieldsData($item_id, $data_arr);
			if( !empty($fields_data) ){
				$dbq=new DBQ('insert into @ @ values @', $this->_getRelTableName(), $this->_getFieldsList(), $fields_data);
			}
		}
	}

	//-----------------------------------------------------------------

	function _getRelTableName(){
		// бывают случаи, когда между двумя моделями возможны несколько видов связей типа many2many
		// на данный момент (2011-08-07) название таблицы-связки состоит из названий связываемых моделей
		// для гибкости, необходима возможность управлять названием таблицы связки
		// 
		// старый алгоритм
		/*
			//метод вызыван для основной модели (products для products_usages_rel)
			$table_name=$this->model_name.'_'.$this->_getModelrelOrFindIt().'_rel';
			return $table_name;
		*/
		// новый алгоритм: к названию поля прибавляем _rel
		$table_name=$this->db_column.'_rel';
		return $table_name;
	}
	
	function _getKey1(){
		return $this->model_name.'_id_key1';
	}
	
	function _getKey2(){
		return $this->_getModelrelOrFindIt().'_id_key2';//метод вызыван для основной модели (products для products_usages_rel)
	}
	
	/**
		метод вызывается всегда объектом поля оснвной модели
		(поэтому он не требует параметров)
		на выходе метод отдает название привязанной модели
		которое он получает или прямо из поля $this->modelrel (usages)
		или из названия поля $this->db_column (products_usages)
	*/
	function _getModelrelOrFindIt(){
		if($this->modelrel!=''){
			$result=$this->modelrel;
		}else{
			$strings=explode('_',$this->db_column);
			if(count($strings)!=2){_die('ошибка в ManyToManyField->_getModelrelOrFindIt()');}
			if($this->model_name==$strings[0]){
				$result=$strings[1];
			}else{
				$result=$strings[0];
			}
		}
		//проверяем существует ли реально модель с таким названием
		$obj_model=getModelObject($result);
		if( !is_subclass_of($obj_model,'Model') ){
			_die('Модель «'.$result.'» не найдена. Вероятнее всего неправильно названо поле ManyToMany. (При создании полей типа ManyToMany необходимо назвать поле «model1_model2» или же указывать существующую привязанную модель.)');
		}
		return $result;
	}

	function _getFieldsList(){
		$result='('.$this->_getKey1().', '.$this->_getKey2().')';
		return $result;
	}

	function _getFieldsData($item_id, $data_arr){
		if(is_array($data_arr) && count($data_arr)>0){
			$result='';
			foreach($data_arr as $key=>$value){
				$result.='("'.$item_id.'", "'.$value.'"),';
			}
			$result=mb_substr($result,0,-1);
		}
		return $result;
	}

	function _getIsSelected($key1_id,$key2_id){
		if(empty($key1_id) || empty($key2_id)){
			return 'no';
		}
		//в первый раз делаем запрос в БД и вытаскиваем все записи из таблицы связки
		if(!isset($this->all_relations)){
			$dbq=new DBQ('select @ from @ where @=?',$this->_getKey2(),$this->_getRelTableName(),$this->_getKey1(),$key1_id);
			if($dbq->rows>0){
				$this->all_relations=groupArray($this->_getKey2(),$dbq->items);
				$this->all_relations=array_keys($this->all_relations);
			}
		}
		if(is_array($this->all_relations)){
			$result=in_array($key2_id,$this->all_relations)?'yes':'no';
		}

		return $result;
	}

	/*
		этот метод нужен для того, чтобы вытащить имя привязанной модели
		(он сделан по аналогии с методом класса ForeignKeyField и использовался впервые 
		в модели _ModelsFields для определения свойства modelrel, если оно не было задано)
		можно вызвать метод двумя способами:
		1 - из экземпляра, не передавая параметр, например 
		    $this->getRelModelName()
		2 - как метод статичного класса, передавая параметр $db_column, например 
		    ManyToManyField::getRelModelName($db_column)
		    если $db_column='products_usages' то метод возвратит 'usages'
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
		$db_column=explode('_',$db_column);
		return $db_column[1];
	}
}
