<?php
//$_SESSION['admin_user']=array('id'=>1,'su'=>true);
//var_dump($_SESSION);
class Admin{
	function autopage(){//показывает страницу в зависимости от URL
		if(p2v('action')=='login'){
			$this->try2login();
		}elseif(p2v('action')=='logout'){
			$this->logout();
		}elseif( !$this->userIsLogged() ){
			//подключаем внешний вид страницы авторизации
			include(FW_DIR.'/classes/templates/admin/login.php');
			$this->body=$result;
			//определяем весь документ
			include(FW_DIR.'/classes/templates/admin/doctype.php');
		}elseif(p2v('action')=='clear_cache'){
			$_cache_obj=&gmo('_cache');
			$_cache_obj->clearAll();
			header('Location: /admin/_cache/');
			exit();
		}elseif(p2v('action')=='DBcreate'){
			//показываем sql для создания таблицы
			$model_name=$GLOBALS['path'][2];
			$obj_model=getModelObject($model_name);
			$results=$obj_model->getSQLcreateTable();
			if(is_array($results)){
				foreach($results as $sql){
					_echo($sql);
				}
			}else{_die('empty $results array');}
			exit();
		}elseif(p2v('action')=='synchro'){
			//делаем синхронизацию БД по конфигурационным файлам _settings.php и _models.php
			$model__models=&gmo('_models');
			$report=$model__models->synchro();
			//подключаем внешний вид для отчета
			include(FW_DIR.'/classes/templates/admin/synchro_report.php');
		}elseif(p2v('action')=='extra'){
			//удаляем лишние таблицы или лишние столбцы в таблицах
			$this->_removeColsOrTables();
			//редиректим на повторную синхронизацию, которая покажет отсутствие лишних таблиц
			hexit('Location: '.DOMAIN_PATH.'/admin/synchro/');
		}elseif(p2v('action')=='modelitemspage'){
			if(!empty($GLOBALS['path'][2])){
				//делаем shortcut имени модели
				$model_name=$GLOBALS['path'][2];
				//создаем экземпляр модели
				$obj_model=getModelObject($model_name);
				//определяем список элементов в модели
				$obj_model->getModelItemsListHTML();
			}
			exit();
		}elseif(p2v('action')=='up'  || p2v('action')=='down'){
			if(!empty($GLOBALS['path'][2])){
				//делаем shortcut имени модели
				$model_name=$GLOBALS['path'][2];
				//создаем элемент модели
				$model_item=gmi($model_name,$_REQUEST[$model_name]['id']);
				//вызываем метод сортировки
				if(p2v('action')=='up'){
					$result=$model_item->moveUp();
				}else{
					$result=$model_item->moveDown();
				}
				echo '{"id":"'.$result.'"}';//возвращаем id элемента, с которым поменялись местами
			}
			exit();
		}else{
			if(p2v('action')=='edit'){
				define('EDIT_MODE',true);
			}
			//формируем страницу в зависимости от URL
			if(!empty($GLOBALS['path'][2])){
				//делаем shortcut имени модели
				$model_name=$GLOBALS['path'][2];
				//создаем экземпляр модели
				$obj_model=getModelObject($model_name);
				//сперва определяем зону редактирования
				//возможно происходит обработка post-запроса, 
				//и по окончании страница будет перезагружена
				$this->model_item_edit_zone=$obj_model->getModelItemEditZone();
				//если страница не была перезагружена, то определяем список элементов в модели
				$this->model_items_list=$obj_model->getModelItemsListHTML();
			}
			//определяем языки и модели
			$this->domains_list=$this->getDomainsListHTML();
			$this->models_list=$this->getModelsListHTML();
			//собираем страницу: определяем содержимое $this->body
			include(FW_DIR.'/classes/templates/admin/3zones.php');
			$this->body=$result;
			//определяем весь документ
			include(FW_DIR.'/classes/templates/admin/doctype.php');
			//в случаях отладки ДБ будем выводить консоль
			if(DEBUG===true && DEBUG_DB===true){
				$GLOBALS['obj_client']=new ClientSide();
				$GLOBALS['obj_client']->consoleDB();
			}
		}
		//выводим на экран
		echo $result;
	}

	function getDomainsListHTML(){
		$structure_items=ga(array(
			'classname'=>'structure',
			'domain'=>false,
			'fields'=>'url',//список полей которые нужно вытащить через запятую 'id,name,body'
			'extra'=>array('where'=>'parent=0'),//массив типа array('select'=>'id,body', 'where'=>'parent>?', 'params'=>$parent)
			'order_by'=>'ordering',//строка типа или '-cdate' 'parent, +name', где необязательный "+" это asc, а "-" это desc
			'_slice'=>'0',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
		));
		if(count($structure_items)>1){
			foreach($structure_items as $item){
				$domains[]=$item['url'];
			}
			//вызываем шаблон вывода списка моделей
			include(FW_DIR.'/classes/templates/admin/domains_list.php');
		}

		if(isset($result)){
			return $result;
		}
	}

	function getModelsListHTML(){//возвращает HTML-код списка моделей
		//иниц. массив с выходными данными
		$final_models_list=array();
		//если юзер имеет право доступа к моделида,
		//то модель попадает в $final_models_list
		$final_models_list=$this->_getUserCanSeeModelsArr();
		//вызываем шаблон вывода списка моделей
		include(FW_DIR.'/classes/templates/admin/models_list.php');
		return $result;
	}

	/**
	 * создает объект модели $model_name
	 * эта функция не должна вызываться напрямую, 
	 * для создания объекта нужно использовать getModelObject() или gmo()
	 */
	function &createModelObj($model_name){//создает объект модели с именем $model_name
		//инлюдим код с описанием класса если описание модели еще не доступно
		if( !class_exists($model_name) ){
			$scriptLocation=sprintf('%s/%s/_models.php', MODELS_DIR, $model_name);
			if( !array_key_exists($model_name,$GLOBALS['INSTALLED_APPS']) ){
				_die('Model is not in MODELS_DIR/_settings.php',$model_name);
			}elseif( !file_exists($scriptLocation) ){
				_die('Cant load',$scriptLocation);
			}else{
				include($scriptLocation);
			}
		}
		//чтобы найти текстовое название модели обращаемся к $GLOBALS['INSTALLED_APPS']
		$model_txt_name=(isset($GLOBALS['INSTALLED_APPS'][$model_name]))?$GLOBALS['INSTALLED_APPS'][$model_name]:$model_name;
		//создаем экземпляр модели и сразу! помещаем его в $GLOBALS['obj_models_arr']
		$GLOBALS['obj_models_arr'][$model_name] =& new $model_name($model_txt_name);//_print_r($GLOBALS['obj_models_arr']);
		//запускаем метод init() новой модели, чтобы инициализировать все поля
		$GLOBALS['obj_models_arr'][$model_name]->init(); 
		//возвращаем
		return $GLOBALS['obj_models_arr'][$model_name];
	}

	function getModelNameById($id){
		foreach($GLOBALS['obj_models_arr'] as $model_name=>$obj_model){
			if(isset($obj_model->registred_id) && $obj_model->registred_id==$id){
				$result=$model_name;
				break;
			}
		}
		if(!isset($result)){
			$all_models=ga(array(
				'classname'=>'_models',
				'fields'=>'id,name',//список полей которые нужно вытащить через запятую 'id,name,body'
				'_slice'=>'0',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
			));
			foreach($all_models as $model){
				//если модель уже есть в $GLOBALS['obj_models_arr'], то добавляем свойство registred_id
				if(isset($GLOBALS['obj_models_arr'][$model['name']]) && !isset($GLOBALS['obj_models_arr'][$model['name']]->registred_id)){
					$GLOBALS['obj_models_arr'][$model['name']]->registred_id=$model['id'];
				}
				//возвращаем название модели
				if($model['id']==$id){
					$result=$model['name'];
				}
			}
		}
		return $result;
	}

	function userIsLogged(){
		$result=false;
		if(isset($_SESSION['admin_user'])){
			if(false
				|| $_SESSION['admin_user']['multidomain']=='yes'
				|| $_SESSION['admin_user']['domain']==DOMAIN_ID
			){
				$result=true;
				//делаем запрос в БД чтобы проверить изменилась или нет учетная запись администратора
				$dbq=new DBQ('select is_changed from _users where id=?',$_SESSION['admin_user']['id']);
				//если изменилась, то обновляем доступы и сбрасываем поле is_changed
				if($dbq->item=='yes'){
					$this->fixAccess();
					$dbq=new DBQ('update _users set is_changed="no" where id=?',$_SESSION['admin_user']['id']);
				}
			}
		}
		return $result;
	}

	function try2login(){
		_log('call try2login, session before');
		_log($_SESSION['admin_user']);
		unset($_SESSION['admin_user']);
		$login=$_POST['login'];
		$password=$_POST['password'];
		$dbq=new DBQ('select id, name, login, su, domain, multidomain from _users where login=? and hash=@(?)',$login,CRYPT_METHOD,$password);
		if($dbq->rows>1){
			_log('found more than 1 row');
			_echo('в БД несколько пользователей с одинаковым именем');
			_print_r($dbq->items);
			exit();
		}elseif($dbq->rows==1){
			_log('found single row',found);
			if(false
				|| $dbq->line['multidomain']=='yes'
				|| $dbq->line['domain']==DOMAIN_ID
			){
				//чистим _php.log если он не сегодняшний
				$phplog_dir='/admin/';
				$phplog_file='_php.log';
				$unix_mtime=filemtime(SITE_DIR.$phplog_dir.$phplog_file);
				if(date('Ymd',$unix_mtime)!=date('Ymd')){
					fileWrite($phplog_dir,$phplog_file,'');
				}
				//сохраняем пользователя в сессию
				$_SESSION['admin_user']=$dbq->items[0];//_print_r($_SESSION['admin_user']);
				_log('set session',$_SESSION['admin_user']);
				$_SESSION['admin_user']['su']=($_SESSION['admin_user']['su']=='yes');
				$al=new _AdminLog(array('action'=>'in','adminlogin'=>$_SESSION['admin_user']['login'],'adminid'=>$_SESSION['admin_user']['id']));
				$al->save();
			}
		}
		if(!empty($_SESSION['admin_user'])){
			$this->fixAccess();
		}
		_log('hexit');
		hexit('Location: '.DOMAIN_PATH.'/admin/'.p2v('uri'));
	}

	function fixAccess(){
		$dbq=new DBQ('show tables like "_access"');
		if($dbq->rows==1){
			//вытаскиваем массив доступов текущего пользователя, 
			//с ключами _models_id
			$access_arr=ga(array(
				'classname'=>'_access',
				'fields'=>'_models_id,add_access,edit_access,delete_access',//список полей которые нужно вытащить через запятую 'id,name,body'
				'filter'=>e5csql('_users_id=?',$_SESSION['admin_user']['id']),//строка фильтра типа 'parent=32'
				'format'=>'_models_id',//название поля (например 'id'), значение которого будет лежать в ключах результирующего массива
				'_slice'=>'0',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
			));
			//получаем форматированный массив моделей
			$models_arr=ga(array(
				'classname'=>'_models',
				'fields'=>'name',//список полей которые нужно вытащить через запятую 'id,name,body'
				'_slice'=>'0',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
			));
			if(is_array($models_arr)){
				foreach($models_arr as $model){
					$this->setUserAccess('add',$model,$access_arr);
					$this->setUserAccess('edit',$model,$access_arr);
					$this->setUserAccess('delete',$model,$access_arr);
				}
			}
		}
	}

	function setUserAccess($type,$model,$access_arr){
		$model_id=$model['id'];
		$model_name=$model['name'];
		$access=(bool)(isset($access_arr) && isset($access_arr[$model_id]) && $access_arr[$model_id][$type.'_access']=='yes');
		$_SESSION['admin_user']['access'][$model_name][$type]=$access;
	}

	function logout(){
		$al=new _AdminLog(array('action'=>'out','adminlogin'=>$_SESSION['admin_user']['login'],'adminid'=>$_SESSION['admin_user']['id']));
		$al->save();
		unset($_SESSION['admin_user']);
		hexit('Location: '.DOMAIN_PATH.'/admin/');
	}

	function isASynchroProcess(){
		return (bool)(p2v('action')=='synchro');
	}

	//=================================================================

	function _getUserCanSeeModelsArr(){
		//пробегаемся по $GLOBALS['INSTALLED_APPS'] 
		if(is_array($GLOBALS['INSTALLED_APPS']) && count($GLOBALS['INSTALLED_APPS'])>0){
			foreach($GLOBALS['INSTALLED_APPS'] as $model_name=>$model_txt_name){
				//пропускаем модель _access, потому что доступ к ней напрямую не нужен даже суперадмину
				if($model_name=='_access'){continue;}
				//создаем экземпляр модели
				$obj_model=getModelObject($model_name);
				//выясняем имеет ли юзер право доступа к модели, 
				//если да то помещаем информацию о модели в массив для темплейта
				if($obj_model->userCanSeeModelInAdmin()){
					$result[$model_name]=$model_txt_name;
				}
			}
		}else{_die('empty INSTALLED_APPS array');}
		return $result;
	}

	function _removeColsOrTables(){
		foreach($_POST as $post_var=>$value){
			if($value==1){
				if(mb_substr($post_var,0,4)=='del_'){
					$table=mb_substr($post_var,4);//_echo($table);
					$dbq=new DBQ('drop table if exists `'.$table.'`');
				}elseif(mb_substr($post_var,0,7)=='delcol_'){
					$table=p2v('table');//_echo($table);
					$column=mb_substr($post_var,7);//_echo($column);
					$dbq=new DBQ('alter table `'.$table.'` drop column `'.$column.'`');
				}
			}
		}
	}
}

