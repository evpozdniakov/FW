<?php

class _Models extends Model{
	function init_vars(){
		$this->__admin__=array(
			'delete_rel'=>true,//если true, то привязанные модели будут удаляться без предупреждения, как это реализовано в моделях [Админы] и [Модели]
			'ipp'=>99,//items per page - кол-во элементов в левом меню в блоке "страница"
			//'list_display'=>'id,name',//поля, которые будут отдаваться функции __str__()
			//'filter'=>'id>1',//фильтрация элементов в левом меню
			'tf'=>'name,txt_name',//type filter - над левым меню появляется форма-фильтр, подстрока ищется в указанных полях модели
			//'group'=>'fks_id',//группировка, альтернативная постраничной
			'ordering'=>'ordering',//сортировка, где + это asc, - это desc. полей может быть несколько через запятую
			'controls'=>'up,down',//кнопки формы. по умолчанию появляются только "сохранить" и "удалить", остальные нужно объявлять дополнительно
			//'onsubmit'=>'return ver(this.name)',//js-код в атрибуте onsubmit тега <form>
			// 'js'=>'getJsFunctions()',//js-код на странице
			//'deny'=>('add,edit,delete'),//запрет на какое либо из действий, распростаняется в том числе и на суперадмина, например в [Лог авторизации]
		);
	}

	function init_fields(){
		$this->txt_name=new CharField(array('Русское название модели'));
		$this->name=new CharField(array('Наименование класса модели','help_text'=>'маленькие латинские буквы','unique'=>true));
		$this->icon=new ImageField(array('Иконка','blank'=>true,'path'=>'/admin/fw/media/img/','sizes'=>'16/','match'=>'/\.gif$/i'));
		$this->ordering=new OrderField(array('ordering'));
	}

	function __str__($self){
		return sprintf('#%d %s', $self['id'], $self['txt_name']);
	}

	function validate(){
		$err=array();
		//необходимо убедиться, что полученное название модели 
		//состоит из латинских символов (иначе прервать работу скрипта)
		//и преобразовать его к нижнему регистру
		if(!preg_match('/^[0-9a-z_-]+$/i',$this->name)){
			$err['name']=sprintf('имя модели «%s» содержит недопустимые символы',$this->name);
		}
		//нужно убедиться, что таблицы $this->name не существует
		// if( !$GLOBALS['obj_admin']->isASynchroProcess() ){}
		if( !empty($this->id) ){
			$model_name_bak=$GLOBALS['obj_admin']->getModelNameById($this->id);
		}
		if( !$GLOBALS['obj_admin']->isASynchroProcess() ){
			if( empty($this->id) || $model_name_bak!=$this->name ){
				$dbq=new DBQ('show tables like ?',$this->name);
				if($dbq->rows==1){
					$err['name']=sprintf('таблица «%s» уже существует в БД',$this->name);
				}
			}
		}
		return $err;
	}
	
	function beforeChange(){
		$this->name=strtolower($this->name);
	}

	function beforeAdd(){
		//запоминаем модель, чтобы обратиться к ней из _ModelsFields
		$GLOBALS['model_name_bak']=$this->name;
	}

	function beforeEdit(){
		//запоминаем модель, чтобы обратиться к ней из _ModelsFields
		//поскольку поле $this->name может быть изменено пользователем а АИ,
		//то нам следует запомнить старое его значение
		//для этого нужно вытащить элемент модели с id=$this->id
		$GLOBALS['model_name_bak']=$GLOBALS['obj_admin']->getModelNameById($this->id);
	}

	function beforeDelete(){
		//запоминаем модель, чтобы обратиться к ней из _ModelsFields
		//поскольку поле $this->name может быть изменено пользователем а АИ,
		//то нам следует запомнить старое его значение
		//для этого нужно вытащить элемент модели с id=$this->id
		$GLOBALS['model_name_bak']=$GLOBALS['obj_admin']->getModelNameById($this->id);
		//устанавливаем эту глобальную переменную для того, чтобы 
		//в процессе удаления полей модели не дергать генерацию _models.php
		$GLOBALS['deleting_model_id']=$this->id;
	}

	function afterAdd(){
		if( !$GLOBALS['obj_admin']->isASynchroProcess() ){
			//создаем новый файл _models.php в новой папке
			$this->_addModelsPhp();
			//изменяем _settings.php
			$this->_changeSettingsPhp();
			//создаем таблицу новой модели
			$this->_createNewModelTable();
			//кроме того добавляем поля id, ip и mdate в таблицу _modelsfields
			$this->_createLinkToMF($this->id);
			//копируем иконку модели в папку модели
			$this->_copyModelIcon();
		}
	}

	function afterEdit(){
		/*
			все что может измениться на данном этапе - 
			это название модели и текстовое название модели
			поэтому мы будем:
			- изменять _models.php лишь если $this->name!=$GLOBALS['model_name_bak']
			- переименовывать таблицу модели лишь если $this->name!=$GLOBALS['model_name_bak']
			- изменять _settings.php если не синхронизация 
			  (раньше было "изменяем в любом случае", 
			  но такая схема не работала когда мы удаляли лишние модели из файла _settings.php 
				и после включали синхронизацию)
		*/
		if($this->name!=$GLOBALS['model_name_bak']){
			//изменяем файл _models.php
			$this->_editModelsPhp();
			//переименовываем таблицу модели
			$this->_renameModelTable();
		}
		if( !$GLOBALS['obj_admin']->isASynchroProcess() ){
			//изменяем _settings.php
			$this->_changeSettingsPhp();
			//копируем иконку модели в папку модели
			$this->_copyModelIcon();
		}
	}

	function afterDelete(){
		//удаляем _models.php и его папку
		$this->_removeModelsPhp();
		//изменяем _settings.php
		$this->_changeSettingsPhp();
	}

	function synchro(){
		// _logm('synchro starts');
		if($this->__item__){_die('метод synchro() может быть вызыван только объектом модели');}
		/*
		синхронизация должна выполнять следующие функции
		- создавать несуществующие таблицы
		- сообщать о лишних таблицах
		- у существующих таблиц а) модифицировать поля, б) сообщать о лишних полях
		- информацию о моделях и полях заносить в _models и _modelsfields
		- выводить на экран отчет о проделанных действиях
		*/
		//необходимо обеспечить полноценную работу моделям _models и _modelsfields 
		//пытаемся создать таблицу _models
		$this->try2createTable();
		// _logm('after try2createTable');
		//удаляем таблицу _modelsfields и создаем ее заново
		$obj_model=&gmo('_modelsfields');
		$obj_model->dropCreateTable(); unset($obj_model);
		// _logm('after dropCreateTable');
		//изменяем существующие таблицы, создаем НЕсуществующие таблицы, забираем отчет
		$result['tables']=$this->_createModifyModelsTables();
		// _logm('after _createModifyModelsTables');
		//удаляем записи о лишних моделях в таблице _models и 
		$this->_deleteExtraFromModelsTable();
		// _logm('after _deleteExtraFromModelsTable');
		//собираем информацию о лишних таблицах и столбцах
		$result['extra']=$this->_findExtraFieldsTables();
		// _logm('after _findExtraFieldsTables');
		return $result;
	}

	//-----------------------------------------------------------------

	function _createModifyModelsTables(){
		//пытаемся создать несуществующие таблицы моделей
		if(is_array($GLOBALS['INSTALLED_APPS'])){
			foreach($GLOBALS['INSTALLED_APPS'] as $model_name=>$model_txt_name){
				$obj_model=getModelObject($model_name);
				//обновляем структуру таблицы
				$result[$model_name]=$obj_model->changeModelsDBtable();
				//проверяем, имеется ли информация о модели в таблице _models и при необходимости ее добавляем/изменяем
				$this->_updateModelInfo($model_name,$model_txt_name);
				//обновляем информацию о полях модели в таблице _models_fields
				$obj_model=gmo('_modelsfields');
				// _print_r(get_class_methods($obj_model));
				// _print_r($obj_model);
				$obj_model->_updateModelInfo($model_name);
			}
		}
		return $result;
	}

	function _deleteExtraFromModelsTable(){
		//вытаскиваем содержимое таблицы _models
		$models_tables_arr=$this->_getModelsTablesArr();
		//пробегаемся по нему и сравниваем с $GLOBALS['INSTALLED_APPS']
		if(is_array($models_tables_arr)){
			foreach($models_tables_arr as $model_name=>$model_info){
				if(!array_key_exists($model_name,$GLOBALS['INSTALLED_APPS'])){
					$dbq=new DBQ('delete from `@` where id=?',$this->__name__,$model_info['id']);
				}
			}
		}
	}
	
	function _findExtraFieldsTables(){
		//вытаскиваем все имеющиеся таблицы в БД
		$dbq=new DBQ('show tables');
		if($dbq->rows>0){
			// _print_r('$dbq->items',$dbq->items);
			foreach($dbq->items as $table){// _print_r('$table=',$table);
				// foreach($table as $table_name){}//это чтобы вытащить первый элемент массива $table и присвоить его переменной $table_name
				$table_name=first($table);// _print_r('$table_name',$table_name,);
				//сравниваем с $GLOBALS['INSTALLED_APPS']
				if(array_key_exists($table_name,$GLOBALS['INSTALLED_APPS'])){
					//таблица имеется в INSTALLED_APPS, проверяем нет ли у нее лишних полей
					$report=$this->_findExtraFields($table_name);
					if($report!=''){
						$result['columns'][$table_name]=$report;
					}

				//возможно что это вспомогательная таблица для поля ManyToMany
				}elseif( !$this->_itsHelpRelTable($table_name) ){
					//сообщаем о лишней таблице
					$result['tables'][]=$table_name;
				}
			}
		}
		return $result;
	}

	function _itsHelpRelTable($table_name){
		/*
			если это вспомогательная таблица, то она состоит
			из двух полей model1_id и model2_id, 
			и, соответственно у модели 1 должен быть столбец ManyToManyField
		*/
		//отыскиваем название первого (а также второго) поля 
		//чтобы определить название основной модели
		$dbq=new DBQ('show fields from `'.$table_name.'`');
		$key1=$dbq->items[0]['Field'];
		$key2=$dbq->items[1]['Field'];
		//определяем название модели1 (а также модели2)
		$model1_name=mb_substr($key1,0,-3);
		$model2_name=mb_substr($key2,0,-3);
		if(true 
			&& array_key_exists($model1_name,$GLOBALS['INSTALLED_APPS']) 
			&& array_key_exists($model2_name,$GLOBALS['INSTALLED_APPS'])
		){
			//создаем объект модели
			if($model1_name!=''){
				$obj_model=getModelObject($model1_name);
				//пробегаемся по его полям
				foreach(get_object_vars($obj_model) as $field_name=>$obj_field){
					if(is_a($obj_field,'ManyToManyField')){
						if($field_name.'_rel'==$table_name){
							return true;
						}
					}
				}
			}
		}
	}

	function _findExtraFields($model_name){
		$obj_model=getModelObject($model_name);
		//получаем информацию обо всех имеющихся в таблице $model_name полях
		$dbq=new DBQ('show fields from `'.$model_name.'`');
		if($dbq->rows>0){
			foreach($dbq->items as $field_info){//_print_r($field_info);
				$fieldname=$field_info['Field'];
				//проверяем, имеется ли поле $fieldname в модели
				$boll_add_column_info=true;
				$obj_field=$obj_model->$fieldname;
				if(is_object($obj_field) && is_subclass_of($obj_field,'Field') && !isset($obj_field->subfields)){
					$boll_add_column_info=false;
				}elseif($this->_isSubclassOfFileField($obj_model,$fieldname)){
					$boll_add_column_info=false;
				}
				if($boll_add_column_info){
					$result[$fieldname]=$field_info;
				}
			}
		}
		return $result;
	}

	function _isSubclassOfFileField($obj_model,$fieldname){
		//возможно, что текущее поле таблицы является частью поля модели
		//например, поле FileField имеет поля X_name, X_uri, X_ext, X_size, X_upload_date
		//а поле ImageField имеет поля X_name, X_uri, X_width, X_height, X_ext
		$subfields_arr=array(
			'_name',
			'_uri',
			'_ext',
			'_size',
			'_upload_date',
			'_width',
			'_height',
			'_lat',
			'_lng',
			'_zoom',
		);
		$bool=false;
		foreach($subfields_arr as $subfield){
			if($strpos=mb_strpos($fieldname,$subfield)){
				$real_field_name=mb_substr($fieldname,0,$strpos);//_echo($real_field_name);_die('stop');
				if(is_subclass_of($obj_model->$real_field_name,'Field')){
					$field_obj=$obj_model->$real_field_name;
					// _print_r($subfield,$field_obj->subfields);
					if(array_key_exists(mb_substr($subfield,1),$field_obj->subfields)){
						$bool=true;
					}
					break;
				}
				return (bool)is_subclass_of($obj_model->$real_field_name,'Field');
			}
		}
		return $bool;
	}

	function _getModelsTablesArr(){//возвращает содержимое таблицы _models, которое создается единожды, при первом обращении
		if(empty($this->models_tables_arr)){
			$models_tables_arr=ga(array(
				'classname'=>'_models',
				'_slice'=>'0',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
			));
			if(is_array($models_tables_arr)){
				$result=array();
				foreach($models_tables_arr as $item){
					$result[$item['name']]=$item;
				}
				$this->models_tables_arr=$result;
			}else{_die('empty array');}
		}
		return $this->models_tables_arr;
	}

	function _updateModelInfo($model_name,$model_txt_name){
		//вытаскиваем содержимое таблицы _models
		$models_tables_arr=$this->_getModelsTablesArr();
		//проверяем, имеется ли информация о модели в таблице _models
		if( array_key_exists($model_name,$models_tables_arr) ){
			//если $model_txt_name изменился, то сохраняем новый
			$model_item=new _Models($models_tables_arr[$model_name]['id']);
			if($model_item->txt_name!=$model_txt_name){
				$model_item->txt_name=$model_txt_name;
				$err=$model_item->save();
				if( !empty($err) ){
					_print_r($err);
				}
			}
		}else{
			//информация отсутствует, добавляем ее
			$model_item=new _Models(array('txt_name'=>$model_txt_name,'name'=>$model_name));
			$err=$model_item->save();
			if( !empty($err) ){
				_print_r($err);
			}
		}
	}

	function _addModelsPhp(){
		//создаем содержимое _models.php
		$file_new_content=$this->_getNewModelsPhpContent();
		//создаем новую папку 
		if( !file_exists(sprintf('%s/%s',MODELS_DIR,$this->name)) ){
			mkdir(sprintf('%s/%s',MODELS_DIR,$this->name), 0777);
		}
		//создаем в ней новый файл
		fileWrite(sprintf('%s/%s',MODELS_DIR,$this->name), '_models.php', $file_new_content, 0775);
	}

	function _editModelsPhp(){
		//получаем измененное содержимое _models.php
		$file_changed_content=$this->_getModelsPhpChange();
		//переименовываем папку модели
		rename(sprintf('%s/%s',MODELS_DIR,$GLOBALS['model_name_bak']), sprintf('%s/%s',MODELS_DIR,$this->name));
		//записываем в нее файл
		fileWrite(sprintf('%s/%s/',MODELS_DIR,$this->name), '_models.php', $file_changed_content);
	}

	function _getNewModelsPhpContent(){//_print_r($this);_die('end');
		//формируем массив $__admin__
		$__admin__=$this->_get__admin__();
		//формируем функцию init
		$init=$this->_getInit();
		//формируем функцию __str__
		$__str__=$this->_get__str__();
		//формируем содержимое файла целиком
		$result='class '.ucwords($this->name).' extends Model{'.$__admin__.$init.$__str__.'}';
		$result='<?php'."\n\n".$result."\n\n".'';
		return $result;
	}

	function _getModelsPhpChange(){
		//путь к папке с текущим _models.php
		$dir=sprintf('%s/%s/', MODELS_DIR, $GLOBALS['model_name_bak']);
		//считываем содержимое файла
		$_models_php=file2str($dir,'/_models.php');
		//искомая подстрока с объявлением класса
		$class_declaration='class';
		//ее позиция
		$class_declaration_pos=mb_strpos($_models_php,$class_declaration);
		//позиция имени класса
		$class_name_pos=mb_strpos($_models_php,ucwords($GLOBALS['model_name_bak']),$class_declaration_pos);
		if( !$class_name_pos ){_die('не могу найти объявление класса «'.$GLOBALS['model_name_bak'].'» в файле '.$dir.'/_models.php');}
		$start=mb_substr($_models_php,0,$class_name_pos);
		$end=mb_substr($_models_php,$class_name_pos+mb_strlen($GLOBALS['model_name_bak']));
		//формируем содержимое файла целиком
		$result=$start.ucwords($this->name).$end;
		return $result;
	}

	function _changeSettingsPhp(){
		//считываем файл в переменную
		$_settings_php=file2str(MODELS_DIR, '_settings.php');
		//находим позицию объявление массива $GLOBALS['INSTALLED_APPS']
		$installed_apps_declaration='$GLOBALS[\'INSTALLED_APPS';
		$installed_apps_start_pos=mb_strpos($_settings_php,$installed_apps_declaration);
		//если $installed_apps_start_pos == false, то вероятно INSTALLED_APPS находится в двойных кавычках
		if( !$installed_apps_start_pos ){
			$installed_apps_declaration='$GLOBALS["INSTALLED_APPS';
			$installed_apps_start_pos=mb_strpos($_settings_php,$installed_apps_declaration);
		}
		//если $installed_apps_start_pos все еще ==false, то сообщаем об ошибке
		if( !$installed_apps_start_pos ){_die('не найдено объявление «$GLOBALS[\'INSTALLED_APPS»');}
		//находим позицию первого символа круглой скобки сразу после $installed_apps_start_pos
		//это будет открывающая парная скобка
		$open_brace_position=mb_strpos($_settings_php,'(',$installed_apps_start_pos);
		//находим позицию закрывающей парной скобки
		$close_brace_position=$this->_getMatchBracePosition($_settings_php,$open_brace_position);
		if( !$close_brace_position ){_die('не удалсь найти закрывающую парную скобку для массива $GLOBALS[INSTALLED_APPS]');}
		//формируем новое содержимое файла
		$file_new_content=$this->_getSettingsPhpContent($_settings_php,$open_brace_position,$close_brace_position);
		//перезаписываем файл
		fileWrite(MODELS_DIR, '_settings.php', $file_new_content);
	}

	function _renameModelTable(){
		$dbq=new DBQ('alter table `'.$GLOBALS['model_name_bak'].'` rename to `'.$this->name.'`');
	}

	function _getSettingsPhpContent($_settings_php,$open_brace_position,$close_brace_position){
		//содержимое файла до открывающей скобки (включая ее)
		$start=mb_substr($_settings_php,0,$open_brace_position+1);
		//содержимое файла после закрывающей скобки (включая ее)
		$end=mb_substr($_settings_php,$close_brace_position);
		//получаем массив моделей, имеющихся в таблице _models кроме начинающихся на '_'
		$models_arr=getModelObject('_models');
		$models_arr=$models_arr->objects();
		$models_arr=$models_arr->filter('`name` not like "\_%"');
		$models_arr=$models_arr->_slice(0);
		//в цикле формируем содержимое для $GLOBALS['INSTALLED_APPS']
		$result="\n";
		foreach($models_arr as $model){
			$GLOBALS['INSTALLED_APPS'][$model['name']]=$model['txt_name'];
			$result.=sprintf("\t'%s'=>'%s',\n",$model['name'],$model['txt_name']);
			// $result.="\t".'\''.$model['name'].'\'=>\''.$model['txt_name'].'\','."\n";
		}
		//вовзращаем конкатенацию начала, середины и конца
		$result=$start.$result.$end;
		return $result;
	}

	function _createNewModelTable(){
		//создаем объект новой модели
		$obj_model=getModelObject($this->name);
		//создаем таблицу новой модели (с единственным полем)
		$obj_model->try2createTable();
	}

	function _createLinkToMF($item_id){
		//создаем элемент новой модели
		$obj_model=getModelObject($this->name);
		foreach(get_object_vars($obj_model) as $field_name=>$obj_field){
			if(is_subclass_of($obj_field,'Field')){
				//пропускаем поля id, ip, mdate
				if($obj_field->db_column=='id' || $obj_field->db_column=='ip' || $obj_field->db_column=='mdate'){continue;}
				//if($field_name!='id'){_die('почему то в элементе новой модели '.$this->name.' присутствуют другие поля кроме id');}
				$mf_item=new _ModelsFields($obj_field->serializeToMF($item_id));
				$mf_item->save();
			}
		}
	}

	function _copyModelIcon(){
		//если текущая модель является служебной, то обрываем работу метода
		if(mb_substr($this->name,0,1)=='_'){return;}
		if( !empty($_POST['_models']['icon_from_lib']) || !empty($this->icon) ){
			$model_obj=gmo($this->name);
			try2unlink($model_obj->__gif__);
			try2unlink($model_obj->__png__);
		}
		if(!empty($_POST['_models']['icon_from_lib'])){
			$icon_remote_path=$_POST['_models']['icon_from_lib'];
			$icon_contents=file_get_contents($icon_remote_path);
			// сохраняем удаленную иконку
			if( !empty($icon_contents) ){
				$icon_rsrc=fopen($model_obj->__png__, 'w');
				fwrite($icon_rsrc, $icon_contents);
				fclose($icon_rsrc);
			}
		}elseif($this->icon){
			$ext=last(explode('.', $this->icon));
			copy(SITE_DIR.'/admin/fw/media/img/'.$this->icon, sprintf('%s/%s/icon.%s',MODELS_DIR,$this->name,$ext));
		}
	}

	function _removeModelsPhp(){
		//путь к папке с текущим _models.php
		$dir=sprintf('%s/%s', MODELS_DIR, $this->name);
		//убиваем файл
		if( !unlink($dir.'/_models.php') ){_log('не могу удалить файл «'.$dir.'/_models.php»');}
		//и папку
		if( !rmdir($dir) ){_log('не могу удалить папку «'.$dir.'»');}
	}

	//=================================================================
	
	function _get__admin__(){
		$result='
			function init_vars(){
				$this->__admin__=array(
					//\'delete_rel\'=>true,//если true, то привязанные модели будут удаляться без предупреждения, как это реализовано в моделях [Админы] и [Модели]
					//\'ipp\'=>20,//items per page - кол-во элементов в левом меню в блоке "страница"
					//\'list_display\'=>\'id,name\',//поля, которые будут отдаваться функции __str__()
					//\'filter\'=>\'id>1\',//фильтрация элементов в левом меню
					//\'tf\'=>\'name,description\',//type filter - над левым меню появляется форма-фильтр, подстрока ищется в указанных полях модели
					//\'group\'=>\'fks_id\',//группировка, альтернативная постраничной
					//\'ordering\'=>\'-cdate\',//сортировка, где + это asc, - это desc. полей может быть несколько через запятую
					//\'controls\'=>\'save,save_then_add,up,down,delete\',//кнопки формы. по умолчанию появляются только "сохранить" и "удалить", остальные нужно объявлять дополнительно
					//\'onsubmit\'=>\'return ver(this.name)\',//js-код в атрибуте onsubmit тега <form>
					//\'js\'=>\'functions.js или getJsFunctions()\',//ссылка на js-файл в папке модели или php-функция, которая возвращает кусок js-кода
					//\'deny\'=>(\'add,edit,delete\'),//запрет на какое либо из действий, распростаняется в том числе и на суперадмина, например в [Лог авторизации]
				);
			}
		';
		$result=_mb_str_replace("\n\t\t","\n",$result);
		return $result;
	}


	function _getInit(){
		$result='
			function init_fields(){
			}
		';
		$result=_mb_str_replace("\n\t\t","\n",$result);
		return $result;
	}

	function _get__str__(){
		$result='
			function __str__($self){
				return $self[\'id\'];
			}
		';
		$result=_mb_str_replace("\n\t\t","\n",$result);
		return $result;
	}
}
