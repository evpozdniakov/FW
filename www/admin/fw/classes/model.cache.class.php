<?php
/**
 * Кеш
 * 
 * Кеш включается в config.php:
 * define('USE_CACHE', true);
 * define('CACHE_DIR', $_SERVER['DOCUMENT_ROOT'].'/../fwcache');
 * 
 * Вывод кешированой страницы осуществляется функцией _tryUseCache() в init.php
 * При попадании в кеш выполнение скрипта прерывается
 * В противном случае класс кеша инклудится в _includeClasses() - init.php
 * 
 * Список моделей использованых при построении страницы self::$page_creating_models_list
 * строится в ClientSide::render(), ClientSide::_autoRender() и ModelManager::ModelManager() вызовом _Cache::addModel($model_name)
 *
 * Для контроля целосности данных используем уникальный ключ по именам файлов в _cache
 * ALTER TABLE _cache ADD UNIQUE INDEX (f);
 * и уникальный индекс пар кеш-модель в _cache__models_rel
 * ALTER TABLE _cache__models_rel ADD UNIQUE INDEX (_cache_id_key1, _models_id_key2);
 *
 * 
 */

if(DEBUG===true){
	// в режиме отладки проверяем наличие индексов (необходимо для корректного продолжения 
	// работы кеша после удаления файлов кеша без синхронизации с базой)
	// это не автоматическое создание индексов, а лишь сообщение об их отсутствии
	// $_cache_obj=&gmo('_cache');
	// $_cache_obj->constraint();
}

class _Cache extends Model{
	var $page_creating_models_list=array(); //список моделей использованых при построении страницы
	var $modelnames_and_ids=null;

	function init_vars(){
		$this->__admin__=array(
			'delete_rel'=>true,//если true, то привязанные модели будут удаляться без предупреждения, как это реализовано в моделях [Админы] и [Модели]
			//'ipp'=>20,//items per page - кол-во элементов в левом меню в блоке "страница"
			//'list_display'=>'id,name',//поля, которые будут отдаваться функции __str__()
			//'filter'=>'id>1',//фильтрация элементов в левом меню
			//'tf'=>'name,description',//type filter - над левым меню появляется форма-фильтр, подстрока ищется в указанных полях модели
			//'group'=>'fks_id',//группировка, альтернативная постраничной
			//'ordering'=>'-cdate',//сортировка, где + это asc, - это desc. полей может быть несколько через запятую
			//'controls'=>'save,save_then_add,up,down,delete',//кнопки формы. по умолчанию появляются только "сохранить" и "удалить", остальные нужно объявлять дополнительно
			//'onsubmit'=>'return ver(this.name)',//js-код в атрибуте onsubmit тега <form>
			//'js'=>'getJsFunctions()',//js-код на странице
			'deny'=>('add,edit'),//запрет на какое либо из действий, распростаняется в том числе и на суперадмина, например в [Лог авторизации]
		);
	}

	function init_fields(){
		$this->f=new CharField(array('Имя файла','help_text'=>'с расширением','maxlength'=>'64','unique'=>true));
		$this->u=new CharField(array('URL страницы','blank'=>true));
		$this->e=new IntField(array('Expired time','blank'=>true,'help_text'=>'в формате Unix'));
		$this->v=new CharField(array('View','blank'=>true,'команда создания блока'));
		$this->_cache__models=new ManyToManyField(array('Связка с моделями','modelrel'=>'_models','blank'=>true));
	}

	function __str__($self){
		return defvar($self['u'],$self['v']);
	}

	function getModelNameHTML(){
		// Добавляем ссылку на очистку кеша
		return '<h1>'.$this->__txt_name__.'<a href="/admin/clear_cache/">очистить кеш</a></h1>';
	}

	function beforeDelete(){
		$cache_item=gbi('_cache',$this->id);
		//удаляем записи из таблицы связки
		$dbq=new DBQ(
			'delete from _cache__models_rel where _cache_id_key1=?'
			,$cache_item['id']
		);
		//удаляем файл
		if( file_exists(CACHE_DIR.'/'.$cache_item['f']) ){
			unlink(CACHE_DIR.'/'.$cache_item['f']);
		}
	}

	//=========================================================

	/**
	 * Сохраняем страницу в кеше и отмечаем использованые модели
	 * 
	 * Первой строкой сохраняемого файла пишется время жизни
	 * 
	 * Список имён используеных моделей - self::$page_creating_models_list
	 * строится в ClientSide::render(), ClientSide::_autoRender() и ModelManager::ModelManager() вызовом _Cache::addModel($model_name)
	 *
	 * @param string $text
	 */
	function try2cache($text, $type='html'){
		//общие условия
		if(!generalUsecacheConditions()){return;}

		//на какой срок разрешено кэшировать данную страницу
		$cachetime=(int)$GLOBALS['obj_client']->structure_data_reverse[0]['cachetime'];
		if($cachetime==0){return;}

		//шифруем адрес запрашиваемой страницы
		$cache_file_url=$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$cache_file_name=sprintf('%s.%s', _crypt($cache_file_url), (IS_AJAX===true)?'ajax':'http');

		//если в БД оставалась запись о файле с таким же именем - удаляем ее
		//одновременно удаляются и записи-связки из таблицы _cache__models_rel
		$this->ga(array(
			'filter'=>e5csql('f=?',$cache_file_name),//строка фильтра типа 'parent=32'
			'_delete'=>true,//строка '[n[,m]]' или true. удалит элементы.
		));

		//вычичляем время хранения файла, создаем содержимое и сохраняем файл
		$expired=(int)date('U')+$cachetime;
		$cache_file_content=sprintf("%s-%s\n%s", $expired, $type, $text);
		fileWrite(CACHE_DIR,$cache_file_name,$cache_file_content);

		//сохраняем запись о файле в БД, чтобы была возможность выборочно удалить закэшированные файлы
		$data=array(
			'f'=>$cache_file_name,
			'u'=>$cache_file_url,
			'e'=>$expired,
		);
		$_cache_item=gmi('_cache',$data);
		$errors=$_cache_item->save();
		if(count($errors)){
			_die($_cache_item->errReport($errors));
		}elseif(count($this->page_creating_models_list)){
			//сохраняем связь файла с моделями
			foreach(array_keys($this->page_creating_models_list) as $model_id){
				$dbq=new DBQ(
					'insert into _cache__models_rel (_cache_id_key1, _models_id_key2) values (?, ?)'
					,$_cache_item->id
					,$model_id
				);
			}
		}
	}

	// логика этого метода реализована как отдельная функция в functions.php
	// потому что она должна быть доступна с минимальными затратами ресурсов
	// то есть без загрузки лишних файлов, классов и пр.
	// function try2useCache(){}
	
	// метод для кэширования отдельных блоков
	function try2cacheBlock($view,$html){
		//общие условия
		if(!generalUsecacheConditions()){return;}

		// шифруем $view чтобы поулчить имя файла, который будет содержать код блока
		$cache_file_name=_crypt($view);

		// если в БД оставалась запись о файле с таким же именем - удаляем ее
		// одновременно удаляются и записи-связки из таблицы _cache__models_rel
		$this->ga(array(
			'filter'=>e5csql('f=?',$cache_file_name),//строка фильтра типа 'parent=32'
			'_delete'=>true,//строка '[n[,m]]' или true. удалит элементы.
		));

		//вычичляем время хранения файла, создаем содержимое и сохраняем файл
		$cache_file_content=sprintf("%s\n%s", $view, trim($html));
		$model_name=mb_substr($view,0,mb_strpos($view,'->'));
		if( !is_dir(CACHE_DIR.'/'.$model_name) ){
			mkdir(CACHE_DIR.'/'.$model_name,0777);
		}
		fileWrite(CACHE_DIR.'/'.$model_name,$cache_file_name,$cache_file_content);

		//сохраняем запись о файле в БД, чтобы была возможность выборочно удалить закэшированные файлы
		$data=array(
			'f'=>$cache_file_name,
			'v'=>$view,
		);
		$_cache_item=gmi('_cache',$data);
		$errors=$_cache_item->save();
		if(count($errors)){
			_die($_cache_item->errReport($errors));
		}elseif(count($this->page_creating_models_list)){
			//сохраняем связь файла с моделями
			foreach(array_keys($this->page_creating_models_list) as $model_id){
				$dbq=new DBQ(
					'insert into _cache__models_rel (_cache_id_key1, _models_id_key2) values (?, ?)'
					,$_cache_item->id
					,$model_id
				);
			}
		}
	}

	function try2useCacheBlock($view){
		// в данном случае важно вернуть false если кэш не включен
		if(!generalUsecacheConditions()){return false;}

		$cache_file_name=_crypt($view);
		//проверяем кеш на диске
		$model_name=mb_substr($view,0,mb_strpos($view,'->'));
		$cache_file_content=file2str(CACHE_DIR.'/'.$model_name, $cache_file_name);
		if($cache_file_content){
			$line_break_position=mb_strpos($cache_file_content,"\n");
			$view=mb_substr($cache_file_content,0,$line_break_position);
			$html=mb_substr($cache_file_content,$line_break_position+1);
			return $html;
		}else{
			return false;
		}
	}
	
	/**
	 * Добавить используюмую модель в список
	 *
	 * @param string $model_name
	 * 
	 * @static 
	 */
	function addModel($model_name){
		//общие условия
		if(!generalUsecacheConditions()){return;}

		//получаем массив из id моделей и их имен
		$this->setModelnamesAndIds();
		//получаем id полученной в параметре модели
		$model_id=$this->modelnames_and_ids[strtolower($model_name)]['id'];

		//нет ли уже такой записи в массиве?
		if(!array_key_exists($model_id, $this->page_creating_models_list)){
			//добавляем в $this->page_creating_models_list массив, где
			//ключом является id модели в таблице _models, а значением - имя модели
			$this->page_creating_models_list[$model_id]=$model_name;
		}
	}

	function setModelnamesAndIds(){
		// _log('$this->modelnames_and_ids before',$this->modelnames_and_ids);
		//получаем массив из id моделей и их имен (ключи массива - имена моделей)
		if(!isset($this->modelnames_and_ids)){
			$this->modelnames_and_ids=ga(array(
				'classname'=>'_models',
				'fields'=>'id,name',
				'filter'=>e5csql('`name` not like "\_%"'),//строка фильтра типа 'parent=32'
				'format'=>'name',//название поля (например 'id'), значение которого будет лежать в ключах результирующего массива
				'_slice'=>'0',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
			));
		}
		// _log('$this->modelnames_and_ids after',$this->modelnames_and_ids);
		return $this->modelnames_and_ids;
	}

	/**
	 * Удаляем файлы кеша и записи в базе при изменении модели или данных
	 * 
	 * Вызывается в
	 * Model::save() - model.class.php (Изменение модели)
	 * Model::delete() - model.class.php (Изменение модели)
	 *
	 * @param string $model_name
	 */
	function clearModelCachedPages($model_name){
		//удаление закэшированных документов не зависит от статуса опции кэширования (USE_CACHE)
		//но зависит от модели - модель не должна быть специальной
		if(mb_substr($model_name,0,1)=='_'){return;}

		//получаем массив из id моделей и их имен
		$this->setModelnamesAndIds();
		//получаем id полученной в параметре модели
		$model_id=$this->modelnames_and_ids[strtolower($model_name)]['id'];
		//на всякий случай делаем такую проверку
		if(!$model_id){_die('не определился $model_id в _Cache->clearModelCachedPages() - СДЕЛАЙТЕ СИНХРОНИЗАЦИЮ, это должно помочь');}
		//вытаскиваем id и имена файлов из таблицы _cache
		$dbq=new DBQ('
			select c.id
			from _cache__models_rel cmr, _cache c
			where cmr._models_id_key2=? and c.id=cmr._cache_id_key1
		'
			,$model_id
		);
		if( is_array($dbq->items) ){
			//удаляем файлы
			foreach($dbq->items as $item){
				//здесь нужно создать элементы и удалить их
				$_cache_item=gmi('_cache',$item['id']);
				$_cache_item->delete();
			}
		}
	}

	/**
	* Чистим кеш полностью
	*/
	function clearAll(){
		//метод предназначен только для суперадмина
		if($_SESSION['admin_user']['su']!='yes'){return;}

		//проверяем на всякий случай правильно ли задан CACHE_DIR
		if( !is_dir(CACHE_DIR) ){_die('Константа CACHE_DIR ('.CACHE_DIR.') не указывает на каталог');}

		//проходим по каталогу CACHE_DIR и удаляем все файлы
		if( $dir_src=opendir(CACHE_DIR) ){
			while( ($file_name=readdir($dir_src))!==false ){
				if( is_file(CACHE_DIR.'/'.$file_name) ){
					unlink(CACHE_DIR.'/'.$file_name);
				}
			}
			closedir($dir_src);
		}
		//чистим таблицы _cache, _cache__models_rel
		$dbq=new DBQ('truncate _cache');
		$dbq=new DBQ('truncate _cache__models_rel');
	}
	
	//пока не очень ясно предназначение данной функции
	//поэтому я ее временно закомментировал evpozdniakov@211208
	/**
	 * Проверяет наличие идексов у таблиц _cache и _cache__models_rel
	 * Возвращает описание ошибки или пустую строку
	 *
	 * @return string
	 *
	 */
	function constraint(){
		$res_str = array();
		
		// анализируем ключи для _cache
		$dbq=new DBQ('SHOW INDEX FROM _cache');
		
		$ind_c_p = $ind_c_f = false;
		foreach ($dbq->items as $item){
			if(($item['Key_name'] == 'PRIMARY') && ($item['Column_name'] == 'id'))
				$ind_c_p = true;
			if(($item['Key_name'] == 'f') && !$item['Non_unique'])
				$ind_c_f = true;
		}

		if(!$ind_c_p){
			$res_str[] = 'У таблицы _cache отсутствует первичный ключ по полю id';
		}
		if(!$ind_c_f){
			$res_str[] = 'У таблицы _cache отсутствует уникальный ключ по полю f';
			$res_str[] = 'Команда создания ключа для _cache по полю f: ALTER TABLE _cache ADD UNIQUE INDEX (f);';
		}
			
		// анализируем ключи для _cache__models_rel
		$dbq=new DBQ('SHOW INDEX FROM _cache__models_rel');
		
		$ind_cmr_1 = $ind_cmr_2 = false;
		foreach ($dbq->items as $item){
			if(($item['Key_name'] == '_cache_id_key1') && ($item['Column_name'] == '_cache_id_key1') && !$item['Non_unique'])
				$ind_cmr_1 = true;
			if(($item['Key_name'] == '_cache_id_key1') && ($item['Column_name'] == '_models_id_key2') && !$item['Non_unique'])
				$ind_cmr_2 = true;
		}
		if(!$ind_cmr_1 or !$ind_cmr_2){
			if(!$ind_cmr_1)
				$res_str[] = 'У таблицы _cache__models_rel отсутствует уникальный ключ по полю _cache_id_key1';
			if(!$ind_cmr_2)
				$res_str[] = 'У таблицы _cache__models_rel отсутствует уникальный ключ по полю _models_id_key2';
				
			$res_str[] = 'Команда создания ключа для _cache__models_rel: ALTER TABLE _cache__models_rel ADD UNIQUE INDEX (_cache_id_key1, _models_id_key2);';
		}
		
		if($res_str){
			_print_r($res_str);
		}
	}
}

