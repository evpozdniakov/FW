<?php

class Field{
	//свойства класса
		var $model_name;//название модели к которой относится экземпляр класса
		var $__name__;//название класса к которому принадлежит экземпляр
		var $txt_name;//название поля формы
		var $help_text;//комментарий в скобках
		var $db_column;//название поля таблицы в БД
		var $field_name;//то что помещается в name: <input name="...">
		var $_input;//входящий массив аргументов
		var $status;//строка = 0|1 в зависимости от обязательности поля
		var $mode;//может быть =core

		//определенные (при добавлении других нужно дописывать _ModelsFields->_getOneModelFields())
		var $editor=false;//использовать ли визуальный редактор
		var $null=false;
		var $blank=false;//является ли необязательным
		var $editable=true;//позволять ли редактировать
		var $viewable=false;//показывать ли в форме (если нельзя редактировать)
		var $allowtags=false;//разрешать ли иметь теги
		var $core=false;//должно ли поле редактироваться в модели, к которой привязано
		//не определенные
		var $type=null;
		var $maxlength=null;
		var $default=null;
		var $match=null;//рег. выражение являющееся маской для загружаемого файла
		var $sizes=null;//допустимые размеры картинки
		var $unsigned=null;
 		var $extra=null;//типа auto_increment
		var $subfields=null;//список дополнительных полей в БД
		var $path=null;//путь к папке для хранения файлов
		var $choices=null;//варианты ответов для полей типа Boolean
		var $view_template=null;//функция определяющая внешний вид поля формы
		var $fieldrel=null;//строка с перечисленными полями, к которым привязывается текущее поле (например для поля типа OrderField или для любого поля с флагом unique=true)
		var $modelrel=null;//строка с названием привязанной модели для полей типа ForeignKeyField или ManyToManyField
		var $unique=null;//является ли поле уникальным
		var $num_in_admin=null;//если поле имеет привязанную модель, то сколько новых линий появляется в форме
	
	function Field($input_arr=''){
		/*
			конструктор состоит из двух частей
			первая часть устанавливает свойства, описанные в _models.php
			вторая часть - метод init() - устанавливает свойства db_column и model_name
			эти свойства также определяются в _models.php как имя класса и имя объекта поля,
			но их инициализация вынесена в отдельный метод чтобы избежать дублирования кода 
			в _models.php
			метод init() вызывается из объекта модели сразу после new

			$input_arr - инициализирующий массив
		*/
		//запомниаем имя класса поля
		$this->__name__=strtolower(get_class($this));
		//сохраняем входящий массив
		$this->_input=$input_arr;
		//сохраняем информацию о свойствах поля
		$this->_saveFieldProps($input_arr);
		//устанавливаем неизменяемые свойства
		$this->constantProps();
	}

	function init($model_name,$db_column){
		/*
			продолжение конструктора, установка некоторых свойств

			$model_name - латинское название родительской модели
			$db_column - название поля ДБ
		*/
		$this->model_name=$model_name;//название родительской модели
		$this->db_column=$db_column;//название поля таблицы в БД
		if( !$GLOBALS['obj_admin']->isASynchroProcess()){
			$this->field_name=$this->model_name.'['.$this->db_column.']';//название поля формы
			$this->status=($this->blank)?'0':'1';//флаг обязательности поля
			$this->choices=$this->_getChoicesArr();
		}
	}

	function getModelItemInitValueCommon($model_item_init_values,$model_item_db_values=''){
		/*
			то как это было сделано раньше - слишком сложно.
			теперь будет все просто.
			если в $model_item_init_values отсутствует поле id, то учитываем только $model_item_init_values
			если в $model_item_init_values присутствует только id, то учитываем только $model_item_db_values
			в остальных случаях отдаем $model_item_init_values, а если он пуст, то $model_item_db_values
			с одним маленьким исключением, которое касается обнуления существуюего значения
		*/
		if(!isset($model_item_init_values['id'])){
			$result=$this->getModelItemInitValue(array('init_values'=>$model_item_init_values));
		}elseif( $model_item_init_values['id']>0 && count($model_item_init_values)==1 ){
			// _print_r('this',$this);
			if( is_subclass_of($this,'SubfieldsInterface') ){
				// передаю весь массив $model_item_db_values, чтобы метод мог вернуть нужное значение
				$result=$this->getModelItemInitValue(array('db_values'=>$model_item_db_values));
			}elseif( is_a($this,'ManyToManyField') ){
				$result=$this->getModelItemInitValue(array('get_stored_by_id'=>$model_item_init_values['id']));
			}else{
				$result=$model_item_db_values[$this->db_column];
			}
		}else{
			// здесь возникает тот случай, когда элемент изменяется
			$data=array('init_values'=>$model_item_init_values,'db_values'=>$model_item_db_values);
			if( is_a($this,'FileField') || is_subclass_of($this,'FileField') ){
				if( is_string($model_item_init_values[$this->db_column]) && !empty($model_item_init_values[$this->db_column]) ){
					$data['path2file']=$model_item_init_values[$this->db_column];
				}
			}elseif( is_a($this,'YamapField') || is_a($this,'GmapField') ){
				if( is_string($model_item_init_values[$this->db_column]) && !empty($model_item_init_values[$this->db_column]) ){
					$data['coords']=$model_item_init_values[$this->db_column];
				}
			}elseif( is_a($this,'ManyToManyField') ){
				// если происходит сохранение элемента через форму на сайте
				// и если в форме отсутствуют некоторые поля, то в случае с ManyToManyField
				// необходимо получить значения из БД
				if( empty($data['init_values'][$this->db_column]) && empty($data['db_values'][$this->db_column]) ){
					if( EDIT_MODE===true && $this->editable ){
						// исключение: изменение данных в админ-интерфейсе,
						// причем само поле является редактируемым
						// в этом случае мы не забираем данные из БД, а сохраняем пустые значения
					}else{
						$data=array('get_stored_by_id'=>$model_item_init_values['id']);
					}
				}
			}
			$new_value=$this->getModelItemInitValue($data);
			// если имеется новое НЕПУСТОЕ значение, нужно вернуть его
			if( $new_value!=='' ){
				$result=$new_value;
			// если новое значение пусто, то возможно его захотели обнулить
			// то есть нужно проверить наличие ключа $this->db_column в инит-массиве
			// наличие ключа скажет о желании пользователя обнулить поле
			}elseif( array_key_exists($this->db_column,$model_item_init_values) ){
				$result='';
			//в последнем случае нужно вернуть предыдущее значение
			}else{
				$result=isset($model_item_db_values[$this->db_column])?$model_item_db_values[$this->db_column]:'';
			}
			$result=trim($result);
		}

		//возможно значение образовано первым <option> и в этом случае сбрасываем его
		if($result=='__null_option__'){
			$result='';
		}
		return $result;
	}

	function serializeToMF($_models_id){
		/*
			на основе значений текущего элемента объекта поля 
			возвращает массив значений, который определяет новый элемент модели _Modelsfields

			$_models_id - id родительской модели в таблице _models
		*/
		$result=array();
		$result['_models_id']=$_models_id;
		$result['txt_name']=defvar($this->db_column,$this->txt_name);
		$result['help_text']=$this->help_text;
		$result['db_column']=$this->db_column;
		$result['_name']=$this->__name__;
		$result['maxlength']=($this->maxlength>0)?$this->maxlength:'';
		$result['default']=$this->default;
		$result['fieldrel']=$this->fieldrel;
		$result['modelrel']=$this->modelrel;
		$result['path']=$this->path;
		$result['match']=$this->match;
		$result['sizes']=$this->sizes;
		if(isset($this->_input['choices'])){
			$result['choices']=$this->_input['choices'];
		}
		$result['editor']=($this->editor!='__null_option__')?$this->editor:'';
		$result['blank']=($this->blank)?'yes':'no';
		$result['null']=($this->null)?'yes':'no';
		$result['editable']=($this->editable)?'yes':'no';
		$result['viewable']=($this->viewable)?'yes':'no';
		$result['allowtags']=($this->allowtags)?'yes':'no';
		$result['core']=($this->core)?'yes':'no';
		$result['unique']=($this->unique)?'yes':'no';
		$result['num_in_admin']=($this->num_in_admin===false)?'false':$this->num_in_admin;

		return $result;
	}

	function getFormFieldHTMLCommon($params_arr,$error_bool,$mode_string=''){
		/*
			возвращает HTML-код поля формы "в обертке" из тегов типа <p> или <td>
			в зависимостри от $this->mode, кроме того, может вернуть массив 
			в котором вторым элементом будет заголовок для столбца полей, 
			редактируемых в режиме "core"

			$params_arr - массив значений всех свойств элемента модели
			$error_bool - true если поле было не заполнено, или заполнено с ошибкой
			$mode_string - editable|viewable - переопределение собственных значений поля
		*/
		$this->txt_name=e5c($this->txt_name);
		$this->help_text=e5c($this->help_text);
		//определяем тег или простое значение поля
		if( (empty($mode_string) && $this->editable===true) or (!empty($mode_string) && $mode_string=='editable') ){
			$input_tag_or_value=$this->getFieldTagOrViewTemplate($params_arr);
		}elseif( (empty($mode_string) && $this->viewable===true) or (!empty($mode_string) && $mode_string=='viewable') ){
			$input_tag_or_value=$this->getModelItemInitValueCommon($params_arr);
		}
		//определяем текст и класс ошибки 
		$error_arr=$this->getFormFieldHTMLError($error_bool);
		//определяем тег <th> заголовка колонки для core
		$th=$this->getFormFieldHTMLth($error_arr);
		//определяем обертку 
		$result=$this->getFormFieldHTMLWrap($input_tag_or_value,$error_arr);
		//при необходимости отдаем не строку а массив
		if($th!=''){
			$result=array($result,$th);
		}//_echo('here getFormFieldHTMLWrap result. Field obj is '.$this->__name__);if(is_array($result)){_echo('it s array');foreach($result as $items){_echo(e5c($items));}}else{_echo('it s string: '.e5c($result));}

		return $result;
	}

	//НАСЛЕДУЕМЫЕ СВОЙСТВА
	function constantProps(){
		/*
			свойства могут быть неизменяемыми, например у NullBooleanField свойство null=true
			чтобы установить такие свойства, вызываем ->constantProps()
		*/
	}

	function getFieldTagOrViewTemplate($params_arr){
		//проверяем не переопределен ли стандартный шаблон
		$view_template_file=sprintf('%s/%s/%s.viewtemplate.php', MODELS_DIR, $this->model_name, $this->db_column);
		if(file_exists($view_template_file)){
			//определяем значение, которое появится в поле формы
			$inputValue=$params_arr[$this->db_column];
			//подключаем файл со специальным шаблоном
			$form_items=new FormItems();
			include($view_template_file);
		}else{
			$model_obj=gmo($this->model_name);
			$viewtemplate_method=$this->db_column.'Viewtemplate';
			if( method_exists($model_obj, $viewtemplate_method) ){
				$result=$model_obj->$viewtemplate_method($this, $params_arr);
			}else{
				$result=$this->getFormFieldHTMLtag($params_arr);
			}
		}

		return $result;
	}

	function getFormFieldHTMLtag($params_arr){
		/*
			возвращает HTML-код поля формы в форме редактирования элемента модели

			$params_arr - массив значений всех свойств элемента модели
		*/
		//определяем значение, которое появится в поле формы
		$inputValue=$params_arr[$this->db_column];
		//подключаем файл со стандартным шаблоном
		include(FW_DIR.'/classes/templates/field/'.$this->__name__.'.php');//return 'поле формы со значением <pre>'.e5c($data).'</pre><br>';

		return $result;
	}

	function getFormFieldHTMLError($error_bool){
		/*
			возвращает массив с классом и текстом ошибки

			$error_bool - true если поле было не заполнено, или заполнено с ошибкой
		*/
		$result=array();
		if($error_bool===true){
			//никакое сообщение пользователю не выводим,
			//потому что не придумал пока какое будет сообщение
			//и нужно ли оно вооообще
			//вместо сообщения у поля формы должна появиться ярко-красная рамка 
			//или что-то в этом роде
			$message='';
			$class='error';
			$result=array('class'=>'error','message'=>'');
		}
		return $result;
	}

	function getFormFieldHTMLth($error_arr){
		/*
			возвращает тег <th> заголовка колонки для режима core

			$error_arr - массив с классом и текстом ошибки
		*/
		if($this->mode=='core' && $this->line==1){
			$form_items=new FormItems();
			$class=isset($error_arr['class'])?$error_arr['class']:'';
			$message=isset($error_arr['message'])?$error_arr['message']:'';
			$header=$form_items->titled($this->txt_name, '', $this->status, $this->help_text, $class, $message);
			$header='<th>'.$header.'</th>';
		}
		if(isset($header)){
			return $header;
		}
	}

	function getFormFieldHTMLWrap($input_tag,$error_arr){
		/*
			возвращает тег поля формы завернутый в <p> или <td>, в зависимости от $this->mode

			$input_tag - тег поля формы
			$error_arr - массив с классом и текстом ошибки
		*/
		if(trim($input_tag)!=''){
			$form_items=new FormItems();
			if($this->mode=='core'){
				$result=$form_items->core(
					$input_tag,
					$this->status,
					(isset($error_arr['class'])?$error_arr['class']:''),
					(isset($error_arr['message'])?$error_arr['message']:'')
				);
			}elseif($this->__name__=='booleanfield' && $this->editable!==false){
				$result=$form_items->untitled(array(
					'input_tag'=>$input_tag,
					'status'=>$this->status,
					'help_text'=>$this->help_text,
					'wrap_cls'=>$this->db_column
				));
			}else{
				$status = ($this->editable===true) ? $this->status : 0;
				// _print_r($this->txt_name,'view',$this->viewable,'edit',$this->editable,'status',$this->status,'$status',$status);
				$result=$form_items->titled(array(
					'title'=>$this->txt_name,
					'input_tag'=>$input_tag,
					'status'=>$status,
					'help_text'=>$this->help_text,
					'extra_text'=>(isset($error_arr['message'])?$error_arr['message']:''),
					'wrap_cls'=>$this->db_column.' '.(isset($error_arr['class'])?$error_arr['class']:''),
				));
			}
		}

		return $result;
	}

	function getSQLcreate(){
		/*
			возвращает фрагмент sql-запроса create table, 
			который отвечает за описание конкретного поля
		*/
		$null=($this->null)?'null':'not null';
		$result='`'.$this->db_column.'` '.$this->getDbColumnDefinition().','."\n";//_echo('res:'.$result);

		return $result;
	}

	/**
	 * переопределяемый в потомках метод, который возвращает 
	 * инициализирующее значение для данного поля на основе $hash['model_item_init_values'],
	 * для большинства полей это значение равно $hash['model_item_init_values'][$this->db_column]
   * 
	 * $hash['model_item_init_values'] - инициализирующий массив всех значений элемента модели
	 */
	function getModelItemInitValue($hash){
		$model_item_init_values=$hash['init_values'];
		if(isset($model_item_init_values['id']) && $model_item_init_values['id']>0){
			$result=(isset($model_item_init_values[$this->db_column]))?$model_item_init_values[$this->db_column]:'';
		}elseif(isset($model_item_init_values[$this->db_column])){
			$result=$model_item_init_values[$this->db_column];
		}elseif(!$this->null){
			$result=$this->default;
		}else{
			$result='';
		}
		
		return $result;
	}

	function getDbColumnDefinition(){
		/*
			возвращает фрагмент sql-запроса с описанием конкретного поля 
			и является частью таких запросов как "create table", 
			"alter table add column", "alter table change column"
		*/
		$result[]=$this->_getSQLcolumnType();
		$result[]=$this->_getSQLcolumnDefault();
		$result[]=( !$this->null )?'not null':'';
		$result[]=$this->extra;

		$result=implode(' ',$result);
		$result=trim($result);//_echo('res:'.$result);

		return $result;
	}

	function getSQLupdate($model_item_value){
		// _log('getSQLupdate in Field Class',$model_item_value);
		/*
			возвращает фрагмент sql-запроса для внесения данных в БД

			$model_item_value - значение поля, которое нужно внести в БД
		*/
		if($model_item_value=='' && $this->null){
			$result=e5csql('`@`.`@` = null,',$this->model_name,$this->db_column);
		}else{
			$result=e5csql('`@`.`@` = ?,',$this->model_name,$this->db_column,defvar('',$model_item_value));
		} 
		
		return $result;
	}

	function getSQLselect(){
		/*
			возвращает sql-фрагмент для запроса данных из БД
		*/
		return '`'.$this->model_name.'`.`'.$this->db_column.'`,';
	}

	function beforeDelete($params_arr){
		/*
			выполняет действие, 
			предшествующее удалению элемента

			$params_arr - массив значений из БД
		*/
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
		//в первую очередь определяем старое имя столбца (ведь оно могло измениться)
		$db_column_bak=$this->_getDbColumnNameBeforeChanges();
		//если информация о поле имеется в таблице
		if(isset($db_columns_info[$db_column_bak])){
			//если поле таблицы не отличается от поля модели
			if( $this->checkFieldInfoIsCorrect($db_columns_info,$db_column_bak) ){
				//то ничего не делаем
			}else{
				//иначе выполняем ALTER TABLE CHANGE
				$result['change']=$this->performAlterTableChange($db_column_bak);
			}
		}else{
			//выполняем ALTER TABLE ADD если информация о поле в таблице отсутствует
			$result['add']=$this->performAlterTableAdd();
		}
		return $result;
	}

	function performAlterTableAdd(){
		/*
			выполняем "alter table add column"
			возвращаем sql-запрос
		*/
		$dbq=new DBQ('alter table `'.$this->model_name.'` add column `'.$this->db_column.'` '.$this->getDbColumnDefinition());
		return $dbq->query;
	}

	function performAlterTableChange($db_column_bak){
		/*
			выполняем "alter table change"
			возвращаем sql-запрос

			$db_column_bak - $this->db_column ИЛИ $GLOBALS['db_column_bak']['old']
		*/
		$dbq=new DBQ('alter table `'.$this->model_name.'` change column `'.$db_column_bak.'` `'.$this->db_column.'` '.$this->getDbColumnDefinition());
		return $dbq->query;
	}

	function performAlterTableDrop(){
		/*
			выполняем "alter table drop column" 
			ничего не возвращаем
		*/
		$dbq=new DBQ('alter table `'.$this->model_name.'` drop column `'.$this->db_column.'`');
	}

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
			}elseif($db_column_info['Type']=='timestamp'){
				$result=true;
			}elseif($this->__name__=='IntField' || $this->__name__=='FloatField'){
				if($db_column_info['Default']==0 && $this->default==''){
					$result=true;
				}
			}
		}else{
			_print_r('here field.class.php');
			_print_r('$db_column_info',$db_column_info);
			_print_r('$typelength',$typelength);
			_print_r('null',$null);
			_print_r(($db_column_info['Field']==$this->db_column),($db_column_info['Type']==$typelength),($db_column_info['Null']==$null));
		}
		if(!$result && p2v('action')=='synchro'){
			_print_r('Default='.$db_column_info['Default'],'$this->default='.$this->default);
			_print_r('RESULT is FALSE (Field class)', '$this',$this,'$db_column_info',$db_columns_info[$this->db_column],'getSQLcreate(): '.$this->getSQLcreate(),'$db_column_bak:'.$db_column_bak,'$typelength:'.$typelength,'$null:'.$null,'$this->default:'.$this->default);
		}
		// _logm('after checkFieldInfoIsCorrect()');
		return $result;
	}

	//=================================================================

	function _getHTML($inputValue){
		include(FW_DIR.'/classes/templates/field/'.$this->className.'.php');
		return $result;
	}

	function _unlinkOldFile($file_uri){
		//для получения пути к папке мы не берем $this->path,
		//потому что эта переменная может меняться, 
		//путь к папке вычисляем из $file_uri
		$file_path_arr=explode('/',$file_uri);//разбиваем путь к файлу на массив подстрок
		$file_name=last($file_path_arr);//последний элемент массива это и есть название файла
		$file_path=mb_substr($file_uri,0,-1*mb_strlen($file_name));//путь к папке
		if(!try2unlink($file_path,$file_name)){_die('не могу удалить файл «'.$path.$file_name.'»');}
	}

	function _getSQLcolumnType(){
		//_print_r($this);
		$maxlength='';
		$type=defvar('undefined',$this->type);
		if($this->type=='varchar' || $this->type=='char'){
			$maxlength='('.$this->maxlength.')';
		}elseif($this->type=='int'){
			$maxlength='(11)';
		}
		$unsigned=($this->unsigned)?' unsigned':'';
		$result=$type.$maxlength.$unsigned;//_echo('result from _getSQLcolumnType:'.$result);
		return $result;
	}

	function _getDbColumnNameBeforeChanges(){
		$result=$this->db_column;
		if( !$GLOBALS['obj_admin']->isASynchroProcess() ){
			if(isset($GLOBALS['db_column_bak']) && $GLOBALS['db_column_bak']['new']==$this->db_column){
				$result=$GLOBALS['db_column_bak']['old'];
			}
		}
		return $result;
	}

	function _saveFieldProps($input_arr){
		if(is_array($input_arr)){
			foreach($input_arr as $key=>$value){
				if($key=='0'){
					//если первое поле не имеет ключа, то это название столбца
					$this->txt_name=$value;
				}elseif(isset($value)){
					//раньше была такая строчка, но она неправильно прописывала изменения 
					//в _models.php - там появлялись 'editor'=>' '
					//поэтому проверку на !='__null_option__' я перенес в MoldelsFields->setDefaultFieldValue()
					//$this->$key=($value!='__null_option__')?$value:' ';
					$this->$key=$value;
				}
			}
		}
	}

	function _getSQLcolumnDefault(){
		$result='';
		if(is_a($this,'DateField') || is_a($this,'DateTimeField') || is_a($this,'TimestampField')){return;}
		if(isset($this->default) && $this->default!=''){
			$result='default "'.$this->default.'"';
		}elseif($this->null===true){//если NULL, то вставляем без кавычек
			$result='default NULL';
		}
		return $result;
	}

	function _getChoicesArr(){
		$result=$this->choices;
		if(is_string($this->choices) && mb_substr($this->choices,-2)=='()'){
			//если choices определены как, например, getViewsList(), то нужно 
			//получить массив данных, запустив соответствующий метод родительской модели
			$parent_model=getModelObject($this->model_name);
			$method_name=mb_substr($this->choices,0,-2);
			$result=$parent_model->$method_name();
		}
		return $result;
	}
}

//===================================================================
//===================================================================
//===================================================================

class PrimaryKeyField extends Field{
	function constantProps(){
		$this->type='int';
		$this->maxlength='';
		$this->unsigned=true;
		$this->null=false;
		$this->key='primary';
		$this->extra='auto_increment';
		$this->blank=true;
	}

	function getFormFieldHTMLWrap($input_tag,$error_arr){
		/*
			возвращает тег поля формы завернутый в <p> или <td>, в зависимости от $this->mode

			$input_tag - тег поля формы
			$error_arr - массив с классом и текстом ошибки
		*/
		return $input_tag;
	}
}

class CharField extends Field{
	var $maxlength='255';

	function constantProps(){
		/*
			свойства могут быть неизменяемыми, например у NullBooleanField свойство null=true
			чтобы установить такие свойства, вызываем ->constantProps()
		* /
		if($this->db_column=='madeby'){
			_log('isset($this->maxlentgh)='.chb(isset($this->maxlength)));
			_log('empty($this->maxlentgh)='.chb(empty($this->maxlength))); 
			_log('(int)$this->maxlength='.(int)$this->maxlength);
			_log('intval($this->maxlength)='.intval($this->maxlength));
		}
		*/
		if(!empty($this->maxlength)){
			$this->maxlength=(int)$this->maxlength;
		}else{
			$this->maxlength=255;
		}
		$this->type=($this->maxlength>3)?'varchar':'char';
		if($this->maxlength>255){
			$this->maxlength=255;
		}elseif($this->maxlength<1){
			$this->maxlength=1;
		}
	}
}

class PasswordField extends Field{
	var $maxlength='25';

	function constantProps(){
		$this->type='varchar';
		if($this->maxlength>255){
			$this->maxlength=255;
		}else if($this->maxlength<3){
			$this->maxlength=3;
		}
	}
}

class IntField extends Field{
	var $default='0';

	function constantProps(){
		$this->type='int';
		$this->maxlength='';
	}

	function getSQLupdate($model_item_value){
		/*
		_echo('=============');
		_print_r($model_item_value);
		_echo('=============');
		/*
			возвращает фрагмент sql-запроса для внесения данных в БД

			$model_item_value - значение поля, которое нужно внести в БД
		*/
		if($model_item_value=='' && $this->null){
			$result=e5csql('`@`.`@` = null,',$this->model_name,$this->db_column);
		}else{
			$result=e5csql('`@`.`@` = ?,',$this->model_name,$this->db_column,(int)$model_item_value);
		} 
		
		return $result;
	}
}

class FloatField extends Field{
	var $default='0';

	function constantProps(){
		$this->type='float';
		$this->maxlength='';
	}

	function getSQLupdate($model_item_value){
		/*
		_echo('=============');
		_print_r($model_item_value);
		_echo('=============');
		/*
			возвращает фрагмент sql-запроса для внесения данных в БД

			$model_item_value - значение поля, которое нужно внести в БД
		*/
		if($model_item_value=='' && $this->null){
			$result=e5csql('`@`.`@` = null,',$this->model_name,$this->db_column);
		}else{
			$result=e5csql('`@`.`@` = ?,',$this->model_name,$this->db_column,(float)$model_item_value);
		} 
		
		return $result;
	}
}

class DoubleField extends Field{
	var $default='0';

	function constantProps(){
		$this->type='double';
		$this->maxlength='';
	}

	function getSQLupdate($model_item_value){
		/*
		_echo('=============');
		_print_r($model_item_value);
		_echo('=============');
		/*
			возвращает фрагмент sql-запроса для внесения данных в БД

			$model_item_value - значение поля, которое нужно внести в БД
		*/
		if($model_item_value=='' && $this->null){
			$result=e5csql('`@`.`@` = null,',$this->model_name,$this->db_column);
		}else{
			$result=e5csql('`@`.`@` = ?,',$this->model_name,$this->db_column,(double)$model_item_value);
		} 
		
		return $result;
	}
}

class TextField extends Field{
	function constantProps(){
		$this->type='mediumtext';
		$this->maxlength='';
	}
	
	function getFormFieldHTMLtag($params_arr){
		$this->__fck_body_id__='fckeditor';
		if(is_array($params_arr) && array_key_exists('structure_id',$params_arr)){
			$this->__fck_body_class__='id'.$params_arr['structure_id'];
		}
		return parent::getFormFieldHTMLtag($params_arr);
	}

	/**
	 * переопределяемый в потомках метод, который возвращает 
	 * инициализирующее значение для данного поля на основе $hash['model_item_init_values'],
	 * для большинства полей это значение равно $hash['model_item_init_values'][$this->db_column]
   * 
	 * $hash['model_item_init_values'] - инициализирующий массив всех значений элемента модели
	 */
	function getModelItemInitValue($hash){
		$model_item_init_values=$hash['init_values'];
		$result=$model_item_init_values[$this->db_column];
		if( $this->editor && $this->allowtags=='yes' ){
			$result=preg_replace('/<p>\s*\&nbsp;<\/p>/', '', $result);
			$result=preg_replace('/<div>\s*\&nbsp;<\/div>/', '', $result);
		}
		return $result;
	}
}

class URLField extends Field{
	var $maxlength='255';

	function constantProps(){
		$this->type='varchar';
		if($this->maxlength>255){
			$this->maxlength=255;
		}else if($this->maxlength<3){
			$this->maxlength=75;
		}
	}

	function getModelItemInitValue($hash){
		$model_item_init_values=$hash['init_values'];
		//в том случае, если пользователь указал ссылку на сайт вроде www.site.com
		//и при этом забыл указать http://
		//то необходимо это сделать

		$url=trim($model_item_init_values[$this->db_column]);
		//с адресом все ОК если:
		if(false 
			|| $url=='' //или еще ничего не набрали
			|| mb_strpos($url,'://')>0 //или http:// уже набрали
			|| mb_strpos($url,'.')===false //или пока не набрали ни одной точки
			|| mb_strpos($url,'mailto:')!==false //или это mailto
		){
			//ничего не делаем
		}else{
			// добавляем mailto: если набрали валидный e-mail
			if(validateEmail($url)){
				$url='mailto:'.$url;
			}else{
				//добавляем "http://" если:
				if(true
					&& mb_strpos($url,'://')===false // "http://" еще не набрали
					&& preg_match('/\.(ru|su|net|com|org|biz|info|tv)/',$url)>0 //в адресе присутствует какой-то из основных доменов 1 уровня
				){
					$url='http://'.$url;
				}
			}
		}
		//убираем конечный слэш, если:
		if(true
			&& mb_substr($url,-1)=='/' //конечный слэш имеется
			&& mb_strpos($url,'://')>0 //в адресе имеется http://
			&& preg_match_all('/\//',$url,$null)==3 //нет других слэшей кроме последнего и двух рядом в http://
		){
			$url=mb_substr($url,0,-1);
		}
		return $url;
	}
}

class EmailField extends Field{
	var $maxlength='75';

	function constantProps(){
		if(!empty($this->maxlength)){
			$this->maxlength=(int)$this->maxlength;
		}else{
			$this->maxlength=75;
		}
		$this->type='varchar';
		if($this->maxlength>255){
			$this->maxlength=255;
		}else if($this->maxlength<3){
			$this->maxlength=3;
		}
	}
}

class BooleanField extends Field{
	var $default='no';
	var $choices='да,нет';

	function constantProps(){
		$this->type="enum('yes','no')";
		$this->maxlength='';
	}

	function getFormFieldHTMLth($error_arr){
		/*
			возвращает тег <th> заголовка колонки для режима core

			$error_arr - массив с классом и текстом ошибки
		*/
		$form_items=new FormItems();
		if($this->mode=='core' && $this->line==1){
			$header=$form_items->chbox(''.$this->field_name.'_check_all',$this->txt_name,'','','onclick="A.miez.checkSameCoreItems(this)"');
			$header=$form_items->title($header);
			$header='<th>'.$header.'</th>';
		}
		if(isset($header)){
			return $header;
		}
	}

	function getModelItemInitValue($hash){
		$model_item_init_values=$hash['init_values'];
		/*
			переопределяемый в потомках метод, который возвращает 
			инициализирующее значение для данного поля на основе $model_item_init_values,
			для большинства полей это значение равно $model_item_init_values[$this->db_column]

			$model_item_init_values - инициализирующий массив всех значений элемента модели
		*/

		//пытаюсь упростить логику.
		//если значение $model_item_init_values[$this->db_column] не пусто, то возвращаем его
		if(isset($model_item_init_values[$this->db_column]) && $model_item_init_values[$this->db_column]=='yes'){
			$result='yes';
		}elseif(isset($model_item_init_values[$this->db_column]) && $model_item_init_values[$this->db_column]=='no'){
			$result='no';
		}else{
			/*
				если же значение не передано, то нам нужно догадаться, 
				откуда взяты данные для текущего элемента: отправлены ли они REQUEST-ом, или же созданы программно
				если данные переданы REQUEST-ом, то в случае, если данное поле имеет атрибут editable==true, 
				то мы возвращаем 'no', если же атрибут editable==false и при этом id>0 (происходит создание элемента), 
				то возвращаем значение по умолчанию, иначе (editable==false && id=0) ничего не возвращаем, чтобы в методе
				getModelItemInitValueCommon() было бы использовано предыдущее значение
				если же данные не были переданы REQUEST-ом, то возвращаем значение по-умолчаню
			*/

			//единственная трудность - определить переданы ли данные REQUEST-ом или нет.
			//попытаемся сделать это, учтитывая все возможные ситуации
			if(false
				|| !isset($_REQUEST[$this->model_name])
				|| is_array($_REQUEST[$this->model_name]) //происходит создание/редактирование элемента модели
				|| !isset($_REQUEST[$this->model_name.'_new1'])
				|| is_array($_REQUEST[$this->model_name.'_new1']) //происходит создание/редактирование элемента модели в случае когда она привязана к другой модели (массив $_REQUEST[$this->model_name.'_new1'] будет заполнени благодаря полю $_REQUEST[$this->model_name.'_new1']['hidden'], которое всегда имеет значение)
			){
				if($this->editable){
					$result='no';
				}elseif((int)$model_item_init_values['id']==0){
					$result=$this->default;
				}
			}else{
				$result=$this->default;
			}
		}
		return $result;
	}
}

class NullBooleanField extends Field{
	var $default='';
	var $choices='да,нет,не известно';

	function constantProps(){
		$this->type="enum('yes','no')";
		$this->maxlength='';
		$this->null=true;
	}
}

class DomainField extends Field{

	function constantProps(){
		$this->type='int';
		$this->maxlength='';
		$this->editable=false;
		$this->viewable=false;
		$this->blank=true;
		$this->null=false;
	}

	function getModelItemInitValue($hash){
		$model_item_init_values=$hash['init_values'];
		//нужно сделать так, чтобы это поле можно было при необходимости переопределять
		if($model_item_init_values[$this->db_column]!=''){
			$result=$model_item_init_values[$this->db_column];
		}else{
			$result=DOMAIN_ID;
		}

		return $result;
	}
}

class TimestampField extends Field{
	//это поле будет полностью автономно, то есть 
	//вообще не будет участвовать ни в синхронизации, ни в определении значения
	var $editable=false;
	var $viewable=false;
	var $blank=true;
	
	function constantProps(){
		$this->type='timestamp';
		$this->maxlength='';
		$this->null=false;
	}

	//возвращаем пустоту, чтобы mysql самостоятельно прописал в поле текущую дату и время
	function getSQLupdate(){
		return;
	}
}

class IPField extends Field{

	function constantProps(){
		$this->type='varchar';
		$this->maxlength='15';
		$this->blank=true;
		$this->editable=false;
		$this->viewable=false;
	}

	function getModelItemInitValue(){
		return $_SERVER['HTTP_X_REAL_IP'];
	}
}

include(FW_DIR.'/classes/interface.subfields.class.php');
include(FW_DIR.'/classes/field.tree.class.php');
include(FW_DIR.'/classes/field.foreignkey.class.php');
include(FW_DIR.'/classes/field.order.class.php');
include(FW_DIR.'/classes/field.date.class.php');
include(FW_DIR.'/classes/field.datetime.class.php');
include(FW_DIR.'/classes/field.file.class.php');
include(FW_DIR.'/classes/field.image.class.php');
include(FW_DIR.'/classes/field.flv.class.php');
include(FW_DIR.'/classes/field.manytomany.class.php');
include(FW_DIR.'/classes/field.captcha.class.php');
include(FW_DIR.'/classes/field.gmap.class.php');
include(FW_DIR.'/classes/field.yamap.class.php');
