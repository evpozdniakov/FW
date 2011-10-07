<?php

class _ModelsFields extends Model{
	function init_vars(){
		$this->__admin__=array(
			//'delete_rel'=>true,//если true, то привязанные модели будут удаляться без предупреждения, как это реализовано в моделях [Админы] и [Модели]
			'ipp'=>99,//items per page - кол-во элементов в левом меню в блоке "страница"
			//'list_display'=>'id,name',//поля, которые будут отдаваться функции __str__()
			//'filter'=>'id>1',//фильтрация элементов в левом меню
			'tf'=>'_models_id,txt_name',//type filter - над левым меню появляется форма-фильтр, подстрока ищется в указанных полях модели
			'group'=>'_models_id',//группировка, альтернативная постраничной
			'ordering'=>'ordering',//сортировка, где + это asc, - это desc. полей может быть несколько через запятую
			'controls'=>'up,down',//кнопки формы. по умолчанию появляются только "сохранить" и "удалить", остальные нужно объявлять дополнительно
			//'onsubmit'=>'return ver(this.name)',//js-код в атрибуте onsubmit тега <form>
			//'js'=>'getJsFunctions()',//js-код на странице
			//'deny'=>('add,edit,delete'),//запрет на какое либо из действий, распростаняется в том числе и на суперадмина, например в [Лог авторизации]
		);
	}

	function init_fields(){ 
		$this->_models_id=new ForeignKeyField(array('Модель','num_in_admin'=>5,'modelrel'=>'_models',));
		$this->txt_name=new CharField(array('Поле формы','core'=>true));
		$this->help_text=new CharField(array('Комментарий','blank'=>true,'core'=>true));
		$this->db_column=new CharField(array('Поле таблицы','core'=>true,'unique'=>true,'fieldrel'=>'_models_id'));
		$this->_name=new CharField(array('Тип','choices'=>'fieldsChoices()','core'=>true));
		
		$this->blank=new BooleanField(array('blank','blank'=>true,'core'=>true));
		$this->editable=new BooleanField(array('editable','blank'=>true,'core'=>true,'default'=>'yes'));
		$this->viewable=new BooleanField(array('viewable','blank'=>true,'core'=>true));
		$this->allowtags=new BooleanField(array('allow tags','blank'=>true,'core'=>true));

		$this->maxlength=new IntField(array('Длина','blank'=>true,'null'=>true,'core'=>true));
		$this->choices=new CharField(array('choices','blank'=>true,'core'=>true));
		$this->editor=new CharField(array('editor','blank'=>true,'choices'=>'editorTypes()','maxlength'=>16,'core'=>true));

		$this->path=new CharField(array('path','blank'=>true,'maxlength'=>200,'core'=>true));
		$this->match=new CharField(array('match','blank'=>true,'maxlength'=>64,'core'=>true));
		$this->sizes=new CharField(array('sizes','blank'=>true,'maxlength'=>64,'core'=>true));

		$this->unique=new BooleanField(array('unique','blank'=>true,'core'=>true));
		$this->fieldrel=new CharField(array('fieldrel','blank'=>true,'maxlength'=>24,'core'=>true));
		
		$this->core=new BooleanField(array('core','blank'=>true,'core'=>true));
		$this->num_in_admin=new CharField(array('num_in_admin','blank'=>true,'maxlength'=>5,'core'=>true));
		$this->modelrel=new CharField(array('modelrel','blank'=>true,'maxlength'=>24,'core'=>true));

		$this->null=new BooleanField(array('null','blank'=>true,'core'=>true));
		$this->default=new CharField(array('default','blank'=>true,'maxlength'=>100,'core'=>true));
		$this->ordering=new OrderField(array('ordering','fieldrel'=>'_models_id'));
		/*
		информацию о новом поле нужно занести в 
			Fields->serializeToMF()
			_ModelsFields->_resetModelObjectFields
		если добавляется поле типа true|false, 
		то поправить _ModelsFields->_getOneModelFields()
		*/
	}

	function __str__($self){
		return $self['txt_name'];
	}

	function fieldsChoices(){
		return array(
			'foreignkeyfield'=>'ForeignKeyField ~fcc',
			'manytomanyfield'=>'ManyToManyField ~fcc',

			'treefield'=>'TreeField ~ebb',
			'orderfield'=>'OrderField ~ebb',

			'domainfield'=>'DomainField ~fc9',

			'datefield'=>'DateField ~ffb',
			'datetimefield'=>'DateTimeField ~ffb',
			'timestampfield'=>'TimestampField ~ffb',

			'intfield'=>'IntField ~cfc',
			'floatfield'=>'FloatField ~cfc',
			'charfield'=>'CharField ~cfc',
			'textfield'=>'TextField ~cfc',

			'urlfield'=>'URLField ~beb',
			'emailfield'=>'EmailField ~beb',

			'imagefield'=>'ImageField ~bff',
			'flvfield'=>'FlvField ~bff',
			'filefield'=>'FileField ~bff',

			'booleanfield'=>'BooleanField ~ccf',
			'nullbooleanfield'=>'NullBooleanField ~ccf',
			
			'passwordfield'=>'PasswordField ~fbf',
			'ipfield'=>'IPField ~fbf',
			'captchafield'=>'CaptchaField ~fbf',
			'gmapfield'=>'GmapField ~fbf',
			'yamapfield'=>'YamapField ~fbf',
		);
	}

	function beforeAdd(){
		$this->setDefaultFieldValue();
	}

	function beforeEdit(){
		$this->setDefaultFieldValue();
		//в переменную класса $this->db_column_bak получаем предыдущее название поля
		$db_column_bak=gbi($this->__name__,$this->id);
		$this->db_column_bak=$db_column_bak['db_column'];//_echo('$this->db_column_bak:'.$this->db_column_bak);
	}
	
	function beforeDelete(){
		//после удаления поля пусть админка развернется на нужной модели
		$_SESSION['open_id']=$this->_models_id;
	}

	function beforeChange(){
		/*
		_echo('1===========');
		$model_name=$this->_getModelName();
		$obj_model=getModelObject($model_name);
		_print_r($obj_model);
		_echo('1===========');
		*/
	}

	function afterEdit(){
		//возможна такая ситуация, когда поле $this->db_column изменится
		//этот случай нужно отследить
		$GLOBALS['db_column_bak']=($this->db_column!=$this->db_column_bak)
			?array('new'=>$this->db_column,'old'=>$this->db_column_bak)
			:'';
		//_print_r($GLOBALS['db_column_bak']);
	}

	function afterDelete(){
		//из таблицы модели $model_name нужно удалить столбцы, связанные с полем $this->db_column
		$model_name=$this->_getModelName();
		if(empty($model_name)){_die('$model_name не определен');}
		//создаем объект модели
		$obj_model=getModelObject($model_name);
		//получаем объект поля
		$field_name=$this->db_column;
		$obj_field=$obj_model->$field_name;
		//запускаем метод поля ->dropDbColumn()
		if(is_object($obj_field) && is_subclass_of($obj_field,'Field') && ($field_name!='id' || $field_name!='ip' || $field_name!='mdate')){
			$obj_field->performAlterTableDrop();
		}
	}

	function afterChange($up__down=false){
		//ничего не делаем, если синхронизация
		if($GLOBALS['obj_admin']->isASynchroProcess()){return;}
		//ничего не делаем, если происходит удаление модели
		if(isset($GLOBALS['deleting_model_id']) && $GLOBALS['deleting_model_id']==$this->_models_id){return;}
		//определяем имя модели чье поле изменяется
		$model_name=$this->_getModelName();
		if(empty($model_name)){_die('$model_name не определен');}
		//пропускаем спецмодели
		if(mb_substr($model_name,0,1)!='_'){
			//1. меняем содержимое соответствующего _models.php
			$this->_editModelsPhp($model_name);//_die('after change _models.php');
			//2. переопределяем поля модели
			$this->_resetModelObjectFields($model_name);
			//3. изменяем таблицы (если не происходит простое перемещение поля)
			if( !$up__down ){
				$obj_model=getModelObject($model_name);
				$obj_model->changeModelsDBtable();
			}
		}
	}

	function setDefaultFieldValue(){
		// _log('----------------here setDefaultFieldValue------------------');
		$field_name=$this->_name;
		// _log('$this->_input',$this->_input);
		$obj_field=new $field_name($this->_input);
		// _log('$obj_field',$obj_field);
		foreach(get_object_vars($obj_field) as $prop_name=>$prop_value){
			// _log('$prop_name=>$prop_value',$prop_name,$prop_value);
			if($prop_name=='__name__'){continue;}
			//проверяю, есть ли ключ $prop_name у объекта $this
			if(array_key_exists($prop_name,$this)){
				//проверяю, не является ли значение по этому ключу Установленным
				//если эту проверку убрать, то перестает работать up-down в модели "Поля"
				if($this->$prop_name=='' && $prop_value!='__null_option__'){
					$this->$prop_name=$prop_value;
				}
			}
			// _log('finally $this->$prop_name='.$this->$prop_name);
		}
		//добавляю новое задание: полям типа ManyToMany и ForeignKey определить установить modelrel, 
		//если оно не было заполнено пользователем
		if(empty($this->modelrel)){
			if($this->_name=='foreignkeyfield'){
				$this->modelrel=ForeignKeyField::getRelModelName($this->db_column);
			}elseif($this->_name=='manytomanyfield'){
				$this->modelrel=ManyToManyField::getRelModelName($this->db_column);
			}
		}
	}

	function editorTypes(){
		$result=array(
			'full_360'=>'full 360px',
			'full'=>'full 240px',
			'full_120'=>'full 120px',
			'medium_360'=>'medium 360px',
			'medium'=>'medium 240px',
			'medium_120'=>'medium 120px',
			'compact_360'=>'compact 360px',
			'compact'=>'compact 240px',
			'compact_120'=>'compact 120px',
			'flash_360'=>'flash 360px',
			'flash'=>'flash 240px',
			'flash_120'=>'flash 120px',
		);
		return $result;
	}

	//-----------------------------------------------------------------

	function _editModelsPhp($model_name){
		//получаем измененное содержимое _models.php
		$file_changed_content=$this->_getModelsPhpChange($model_name);
		//_echo(e5c($file_changed_content));_die('stop');
		//записываем новое содержимое
		fileWrite('/admin/models/'.$model_name.'/','_models.php',$file_changed_content);
	}

	function _getModelsPhpChange($model_name){
		//путь к папке с текущим _models.php
		$dir='/admin/models/'.$model_name.'/';
		//считываем содержимое файла
		$_models_php=file2str($dir,'_models.php');
		//искомая подстрока с объявлением функции init_fields
		$init_declaration='init_fields()';
		//ее позиция
		$init_declaration_pos=mb_strpos($_models_php,$init_declaration);
		//позиция открывающей фигурной скобки
		$open_brace_position=mb_strpos($_models_php,'{',$init_declaration_pos);
		//находим позицию закрывающей парной фигурной скобки
		$close_brace_position=$this->_getMatchBracePosition($_models_php,$open_brace_position);
		if( !$close_brace_position ){_die('не удалсь найти закрывающую парную скобку для функции init_fields()');}
		//формируем части файла
		//содержимое файла до открывающей скобки (включая ее)
		$start=mb_substr($_models_php,0,$open_brace_position+1);
		//содержимое файла после закрывающей скобки (включая ее)
		$end=mb_substr($_models_php,$close_brace_position);
		//список полей
		$fields=$this->_getOneModelFields();
		//формируем содержимое файла целиком
		$result=$start.$fields.$end;
		return $result;
	}

	function _getOneModelFields(){
		//получаем объект _ModelsFields
		$obj_model=getModelObject('_modelsfields');
		//получаем список полей, привязанных к текущей модели
		$this->fields_arr=ga(array(
			'classname'=>'_modelsfields',
			'fields'=>'*',//список полей которые нужно вытащить через запятую 'id,name,body'
			'filter'=>e5csql('_models_id=?',$this->_models_id),//строка фильтра типа 'parent=32'
			'_slice'=>'0',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
		));
		//на данныый момент в $this->fields_arr содержатся записи из таблицы '_modelsfields'
		//но для записи в init() файла _models.php нам нужны данные из метода serializeToMF()
		//получаем их
		$fields_arr_modified=$this->_getOneModelSerializedFields();
		$result='';
		if(is_array($fields_arr_modified)){
			$result="\n";
			foreach($fields_arr_modified as $field_props){//_print_r($field);
				//делаем отбивку в две табуляции
				$result.="\t\t";
				//записываем основные состовляющие поля db_column _name и txt_name
				$result.='$this->'.$field_props['db_column'].'=new '.$this->_getFieldClassName($field_props['_name']);
				$result.='(array(\''.$field_props['txt_name'].'\',';
				//остальные составляющие добавляем в цикле
				if(is_array($field_props)){
					//_print_r($field_props);
					foreach($field_props as $prop_name=>$prop_value){
						//НО пропускаем id, _models_id, _name, db_column, ordering, ip, mdate и txt_name
						if(false
							|| $prop_name=='id' 
							|| $prop_name=='_models_id' 
							|| $prop_name=='_name' 
							|| $prop_name=='db_column'
							|| $prop_name=='txt_name' 
							|| $prop_name=='mdate' 
							|| $prop_name=='ip' 
							|| $prop_name=='ordering' 
						){continue;}
						switch($prop_name){
							case 'blank':
							case 'null':
							case 'editable':
							case 'viewable':
							case 'core':
							case 'unique':
							case 'allowtags':
								$bool_string=($prop_value=='yes')?'true':'false';
								$result.='\''.$prop_name.'\'=>'.$bool_string.',';
								break;
							case 'num_in_admin':
								$bool_or_int=($prop_value=='false')?'false':$prop_value;
								$result.='\''.$prop_name.'\'=>'.$bool_or_int.',';
								break;
							default:
								$result.='\''.$prop_name.'\'=>\''.$prop_value.'\',';
								break;
						}
					}
				}
				$result=mb_substr($result,0,-1);
				$result.='));'."\n";
			}
			$result.="\t";
		}
		return $result;
	}

	function _getOneModelSerializedFields(){
		$result='';
		if(is_array($this->fields_arr)){//_print_r($this->fields_arr);_echo('===========');
			foreach($this->fields_arr as $field){
				if($field['_name']!=''){
					if(false
						|| $field['db_column']=='id'
						|| $field['db_column']=='ip'
						|| $field['db_column']=='mdate'
					){
						continue;
					}
					//создаем пустой объект поля, который понадобится нам для дефолтных значений
					$class_name=$this->_getFieldClassName($field['_name']);
					$empty_obj_field=new $class_name();//_print_r($empty_obj_field);
					//получаем массив $empty_field, аналогичный массиву $field, но содержащий только дефолтные значения
					$empty_field=$empty_obj_field->serializeToMF(0);//_echo('$empty_field');_print_r($empty_field);_echo('$field');_print_r($field);
					//перебираем характеристики поля $field
					if(is_array($field)){
						$subresult=array();
						foreach($field as $key=>$value){
							//если значение характеристики не пусто и отличается от дефолтного, то помещаем ее в $subresult
							if(!empty($value)){
								if(!isset($empty_field[$key]) || $empty_field[$key]!=$value){
									$subresult[$key]=$value;
								}elseif($key=='_name'){//поле _name добавляем как есть, оно всегда совпадает с дефолтным
									$subresult[$key]=$value;
								}
							}
						}
						$result[]=$subresult;
					}else{_die('empty array $field in _getOneModelSerializedFields()');}
				}
			}//_print_r($result);_die('');
		}
		return $result;
	}

	function _resetModelObjectFields($model_name){
		//удаляем из объекта модели все ссылки на поля
		$obj_model=getModelObject($model_name);
		foreach(get_object_vars($obj_model) as $field_name=>$obj_field){
			if(is_object($obj_field) && is_subclass_of($obj_field,'Field')){
				//глобально удаляем из объекта модели поле $field_name
				unset($obj_model->$field_name);
			}
		}
		if(is_array($this->fields_arr)){
			foreach($this->fields_arr as $field){
				if($field['_name']!=''){
					//пропускаем поля id, ip, mdate потому что в init_fields() они не указываются
					if($field['db_column']=='id' || $field['db_column']=='ip' || $field['db_column']=='mdate'){continue;}
					$field_name=$field['db_column'];
					$field_class_name=$this->_getFieldClassName($field['_name']);
					$field_init_arr=array();
					if(is_array($field)){
						foreach($field as $prop_name=>$prop_value){
							switch($prop_name){
								case 'blank':
								case 'null':
								case 'viewable':
								case 'core':
								case 'unique':
								case 'allowtags':
									$field_init_arr[$prop_name]=(bool)($prop_value=='yes');
									break;
								default:
									$field_init_arr[$prop_name]=$prop_value;
									break;
							}
						}
					}
				}
				$field_object=new $field_class_name($field_init_arr);
				//продолжение конструктора, установка некоторых свойств
				$field_object->init($model_name,$field_name);
				//привязываем к модели новое поле
				$obj_model->$field_name=$field_object;
			}
		}
	}

	function _updateModelInfo($model_name){//функция проверяет насколько актуальна информация о полях модели и при необходимости ее обновляет
		//_echo('here $model_name='.$model_name);
		//_echo('model_name:'.$model_name);
		//вытаскиваем id модели из таблицы _models
		$obj_model=&gmo('_models');
		$model_id=$obj_model->objects();
		$model_id=$model_id->fields('id');
		$model_id=$model_id->filter('name="'.$model_name.'"');
		$model_id=$model_id->_slice(0);
		$model_id=$model_id[0]['id'];
		//_echo('$model_id:'.$model_id);
		//удаляем из _modelsfields всю информацию о полях модели $model_name
		$model_manager=$this->objects();
		$model_manager=$model_manager->filter('_models_id="'.$model_id.'"');
		$model_manager=$model_manager->_delete();
		//вытаскиваем информацию о полях, описанных в _models.php модели $model_name
		$obj_model=&gmo($model_name);
		//пробегаемся по полям
		//_print_r($obj_model);_die('stop');
		foreach(get_object_vars($obj_model) as $field_name=>$obj_field){
			if(is_object($obj_field) && is_subclass_of($obj_field,'Field')){//_echo('$field_name:'.$field_name);
				//пропускаем поля id, ip, mdate
				if($obj_field->db_column=='id' || $obj_field->db_column=='ip' || $obj_field->db_column=='mdate'){continue;}
				//добавляем в _modelsfields информацию о текущем поле
				$mf_item=new _ModelsFields($obj_field->serializeToMF($model_id));
				$mf_item->save();
			}
		}
	}

	function _getModelName(){
		/*
			определяем имя модели чье поле удаляется
			либо берем его из $GLOBALS['model_name_bak'],
			либо вытаскиваем из $GLOBALS['obj_models_arr'] поле registred_id
			а если в $GLOBALS['obj_models_arr'] нет такой информации, 
			то делаем запрос к _models и создаем такую информацию
		*/
		if(!isset($this->model_name)){
			if(isset($GLOBALS['model_name_bak'])){
				$this->model_name=$GLOBALS['model_name_bak'];
			}else{
				$this->model_name=$GLOBALS['obj_admin']->getModelNameById($this->_models_id);
			}
		}
		
		return $this->model_name;
	}

	function _getFieldClassName($class_name){
		$fields_choices=$this->fieldsChoices();
		$class_name=$fields_choices[$class_name];
		$class_name=explode('~',$class_name);
		$class_name=$class_name[0];
		$class_name=trim($class_name);
		return $class_name;
	}
}
