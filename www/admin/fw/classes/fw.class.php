<?php

class FW{
	public static function init(){
		define('SITE_DIR', $_SERVER['DOCUMENT_ROOT']);
		define('FW_DIR', SITE_DIR.'/admin/fw');
		define('MODELS_DIR', SITE_DIR.'/admin/models');
		
		// инклюдим конфиг и вспомогательные функции
		include(SITE_DIR.'/../config.php');
		include(FW_DIR.'/functions.php');
		
		// опции отладки
		self::debugInit();
		
		// настраиваем PHP
		self::setPHP();
		
		// настраиваем mbstring
		self::startMbstring();
		
		// если включена настройка magic_qutes_gpc, то очищаем данные
		self::disableMagicQuotesGpc();
		
		// определяем наличие ajax-запроса
		// и при необходимости перекодируем данные из utf-8
		self::ajaxSets();
		
		// подключаемся к БД
		self::connectDB();
		
		//создаем $GLOBALS['path']
		self::createGlobals();
		
		//определяем языковую версию
		self::setDomain();
		
		//стартуем сессии
		self::startSessions();
		
		//пытаемся вернуть закэшированную страницу
		self::tryUseCache();
		
		//инклюдим классы
		self::includeClasses();
		
		//запускаемся
		self::run();
	}

	/**
	 * устанавливает константу DEBUG=false, если она не была задана ранее
	 * устанавливает константы DEBUG_*
	 * при необходимости подкючает класс для отладки запросов к БД
	 */
	public static function debugInit(){
		// если отладка не включена специально, предполагаем что она выключена
		if( !defined('DEBUG') ){
			define('DEBUG', false);
		}

		// устанавливаем константы DEBUG_*
		self::debugInitSetOptions();
		
		// подкючаем класс для отладки запросов к БД
		if(DEBUG_DB===true){
			require_once(LIB_DIR. 'debug/debug_db.php');
			new Debug_db(DEBUG_DB_SAVE_STACK, DEBUG_DB_INCLUDE_RES);
		}
	}
	
	/**
	 * устанавливает опции отладки (константы DEBUG_*) в true или false
	 * в зависимости от настроек в config.php
	 * на входе строка с опциями, которые должны быть установлены в true
	 * остальные должны быть установлены в false
	 */
	public static function debugInitSetOptions(){
		$user_debug_options=_explode(DEBUG_OPTIONS);

		$all_debug_options='DB DB_SAVE_STACK DB_INCLUDE_RES EMAIL_ERROR SMARTY_CONSOLE_ON TRACE_ON_DIE DISPLAY_PHP_ERRORS';
		$all_debug_options=explode(' ',$all_debug_options);

		foreach($all_debug_options as $option_name){
			if( !defined('DEBUG_'.$option_name) ){
				/*
					TODO добавить allowed debug start
				*/
				if( in_array($option_name,$user_debug_options) ){
					$option_value=true;
				}else{
					$option_value=false;
				}
				define('DEBUG_'.$option_name, $option_value);
			}
		}
	}

	/**
	 * конфигурирует PHP, устанавливая следующие опции:
	 * error_reporting
	 * log_errors
	 * display_errors
	 * error_log
	 * track_errors
	 * 
	 * date_default_timezone_set
	 * allow_call_time_pass_reference
	 * 
	 * а также устанавливает локаль в зависимости от кодировки сайта
	 */
	public static function setPHP(){
		// обработка ошибок
		ini_set('error_reporting', (E_ERROR|E_PARSE|E_WARNING|E_CORE_ERROR|E_CORE_WARNING|E_COMPILE_ERROR|E_COMPILE_WARNING|E_USER_ERROR|E_USER_WARNING));
		//ini_set('error_reporting', (E_ALL));
		ini_set('log_errors', 1);
		ini_set('error_log', SITE_DIR.'/admin/_php.log');
		$display_errors = (DEBUG===true && DEBUG_DISPLAY_PHP_ERRORS===true) ? 1 : 0;
		ini_set('display_errors', $display_errors);
		ini_set('track_errors', true);

		// таймзона
		if( function_exists('date_default_timezone_set') ){
			date_default_timezone_set('Europe/Moscow');
		}

		// проверяем наличие SITE_ENCODING
		if( !defined('SITE_ENCODING') ){_die('необходимо задать SITE_ENCODING: "windows-1251", "UTF-8" либо еще что-то');}
	
		//локаль
		if( SITE_ENCODING=='UTF-8' ){
			setlocale(LC_ALL, 'en_US.UTF-8');
		}elseif( SITE_ENCODING=='windows-1251' ){
			setlocale(LC_ALL, 'ru_RU.CP1251', 'rus_RUS.1251');
		}

		// даже не помню что это и для чего
		ini_set('allow_call_time_pass_reference',0);
	}

	/**
	 * настраивает mbstring
	 */
	public static function startMbstring(){
		ini_set('mbstring.language','Neutral');
		ini_set('mbstring.internal_encoding',SITE_ENCODING);
		ini_set('mbstring.http_input','pass');
		ini_set('mbstring.http_output','pass');
		ini_set('mbstring.detect_order','auto');
		ini_set('mbstring.substitute_character','none');
	}

	/**
	 * некоторые провайдеры чтобы защитить сайты включают magic_quotes_gpc
	 * поскольку такой режим несовместим с FW, метод при необходимости 
	 * очищает $_GET, $_POST, $_COOKIE, $_REQUEST
	 */
	public static function disableMagicQuotesGpc(){
		if( get_magic_quotes_gpc() ){

			function undoMagicQuotesRec($array,$topLevel=true){
				$newArray=array();
				foreach($array as $key=>$value){
					if(!$topLevel){
						$key=stripslashes($key);
					}
					if(is_array($value)){
						$newArray[$key]=undoMagicQuotesRec($value,false);
					}else{
						$newArray[$key]=stripslashes($value);
					}
				}
				return $newArray;
			}

			$_GET=undoMagicQuotesRec($_GET);
			$_POST=undoMagicQuotesRec($_POST);
			$_COOKIE=undoMagicQuotesRec($_COOKIE);
			$_REQUEST=undoMagicQuotesRec($_REQUEST);
		}
	}

	/**
	 * метод устанавливает константу IS_AJAX (true если пришел ajax-запрос, иначе false)
	 * а также перекодирует $_GET, $_POST, $_COOKIE, $_REQUEST
	 * из utf-8 в SITE_ENCODING
	 */
	public static function ajaxSets(){
		if($_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest'){
			define('IS_AJAX',true);
		}elseif(p2v('json')==1 && DEBUG===true){
			define('IS_AJAX',true);
		}else{
			define('IS_AJAX',false);
		}
		if(IS_AJAX && strtolower(SITE_ENCODING)!='utf-8'){

			function utf8DecodingRec($arr){
				foreach($arr as $key=>$value){
					if(is_array($value)){
						$value=utf8DecodingRec($value);
					}else{
						$value=iconv('utf-8',SITE_ENCODING,$value);
					}
					$result[$key]=$value;
				}
				return $result;
			}

			$_GET=utf8DecodingRec($_GET);
			$_POST=utf8DecodingRec($_POST);
			$_COOKIE=utf8DecodingRec($_COOKIE);
			$_REQUEST=utf8DecodingRec($_REQUEST);
		}
	}

	/**
	 * если заданы параметры подключения, метод создает соединение с БД
	 * и устанавливает константу USE_DB=true
	 * если же параметры подключения не заданы, то USE_DB=false
	 */
	public static function connectDB(){
		if( defined('DBUSER') && defined('DBNAME') ){
			define('USE_DB', true);
			include(FW_DIR.'/classes/db.class.php');
			$db=new DB();
			$db::connect();
		}else{
			define('USE_DB', false);
		}
	}

	public static function createGlobals(){

		if(!defined('SERVER_NAME')){
			if(!defined('SERVER_NAME')){
				define('SERVER_NAME',$_SERVER['SERVER_NAME']);
			}
			// данный алгоритм не сработал на TEL
			// поскольку $_SERVER['SERVER_NAME']==www2.tel.ru а $_SERVER['HTTP_HOST']==corp.tel.ru
			// if(mb_strlen($_SERVER['HTTP_HOST']) > mb_strlen($_SERVER['SERVER_NAME'])){
			// 	define('SERVER_NAME',$_SERVER['HTTP_HOST']);
			// }else{
			// 	define('SERVER_NAME',$_SERVER['SERVER_NAME']);
			// }
		}
		/*
		чтобы при использовании кэширования невозможно было вызывать 
		повторную генерацию страниц с помощью get-параметров нужно
		1. для страниц, которые подлежат кэшированию, запретить запрашивать урлы с get-параметрами
		2. при создании кэшированной страницы использовать именно запрашиваемый адрес $_SERVER['REQUEST_URI']
		*/
		//проверяем, имеются ли в $_SERVER['REQUEST_URI'] вопросительные знаки
		//_log('$_SERVER[REQUEST_URI]='.$_SERVER['REQUEST_URI']);
		if(mb_strpos($_SERVER['REQUEST_URI'],'?')!==false){
			//на всякий случай запоминаем оригинальный $_SERVER['REQUEST_URI']
			//наличие этой переменной будет означать присутствие get-параметра в запрашиваемой странице
			//и запрет кэширования
			$_SERVER['REQUEST_URI_BAK']=$_SERVER['REQUEST_URI'];
			//переопределяем $_SERVER['REQUEST_URI'], убирая из него get-параметры
			$_SERVER['REQUEST_URI']=mb_substr($_SERVER['REQUEST_URI'],0,mb_strpos($_SERVER['REQUEST_URI'],'?'));
		}
		//в массив $GLOBALS['path'] нам нужно поместить запрашиваемый домен и тот адрес, 
		//который получился в результате преобразований мод-реврайта $_SERVER['REDIRECT_URL']
		$host_and_redirect=SERVER_NAME;
		$_SERVER['REDIRECT_URL']='/'.strip_tags($_GET['__uri__']);//_print_r('redirect='.$_SERVER['REDIRECT_URL']);
		$host_and_redirect.=$_SERVER['REDIRECT_URL'];//_print_r('$host_and_redirect='.$host_and_redirect);
		//убираем заключительный слэш, чтобы не получился последний пустой элемент массива
		if(mb_substr($host_and_redirect,-1)=='/'){$host_and_redirect=mb_substr($host_and_redirect,0,-1);}
		//разбиваем $host_and_redirect по слэшам в глобальный массив $GLOBALS['path']
		$GLOBALS['path']=explode('/',$host_and_redirect);
		//в массив $GLOBALS['vpath'] нам нужно поместить запрашиваемый домен и запрашиваемый путь
		$host_and_request=SERVER_NAME;
		$host_and_request.=$_SERVER['REQUEST_URI'];
		//убираем заключительный слэш, чтобы не получился последний пустой элемент массива
		if(mb_substr($host_and_request,-1)=='/'){$host_and_request=mb_substr($host_and_request,0,-1);}
		//разбиваем $host_and_request по слэшам в глобальный массив $GLOBALS['vpath']
		$GLOBALS['vpath']=explode('/',$host_and_request);
		//определяем принт-версию
		$version=($_GET['verprint'])?'print':'';
		define('VERSION',$version);
	}

	public static function setDomain(){
		if(USE_DB===true){
			//определяем субдомен
			if(USE_SUBDOMAINS===true){
				if(!defined('SUBDOMAINS_LIST') || !defined('DEFAULT_SUBDOMAIN') || !defined('HIDE_DEFAULT_SUBDOMAIN')){
					/*
						TODO проверить этот _die
					*/
					_die('настройте правильно config.php для работы с субдоменами. Должны быть заданы следующие константы:','USE_SUBDOMAINS','SUBDOMAINS_LIST (список субдоменов через запятую)','DEFAULT_SUBDOMAIN','HIDE_DEFAULT_SUBDOMAIN');
				}
				//определяем субдомен из имени сервера
				$subdomains=explode('.',SERVER_NAME);
				$subdomains_list=_explode(SUBDOMAINS_LIST);
				if(in_array($subdomains[0], $subdomains_list)){
					// известно, что запрошенный субдомен относится к списку возможных, запоминаем его
					$found_substr=$subdomains[0];
					// если запрошен дефолтный субдомен и если нужно его скрывать, то делаем редирект
					if($found_substr==DEFAULT_SUBDOMAIN && HIDE_DEFAULT_SUBDOMAIN===true){
						hexit('Location: http://'.removeSubdomain());
					}
				}else{
					// запрашивается сайт без субдомена или с www впереди
					// это означает, что запрашивается дефолтный субдомен
					$found_substr=DEFAULT_SUBDOMAIN;
					// делаем редирект на дефолтный субдомен, если нужно
					if(HIDE_DEFAULT_SUBDOMAIN===false){
						hexit('Location: http://'.DEFAULT_SUBDOMAIN.'.'.SERVER_NAME);
					}
				}
			}elseif(USE_MULTIDOMAINS===true){
				$found_substr=SERVER_NAME;
			}else{
				//если субдомены не используются, то вытаскиваем субдомен из $_GET['__domain__']
				if(!empty($_GET['__domain__'])){
					$found_substr=$_GET['__domain__'];
				}else{
					//если $_GET['__domain__'] не задан, то присваиваем знак процента, 
					//чтобы вытащить первый попавшийся субдомен из БД 
					$found_substr='%';
				}
			}

			if(empty($found_substr)){
				_die('субдомен не был определен');
			}

			$subdomain_exists_in_DB=false;
			if(DEBUG===true){
				$dbq=new DBQ('show tables like "structure"');
				if($dbq->rows==0){_die('таблица "structure" отсутствует в БД');}
			}
			$dbq=new DBQ('select id, url from structure where parent=0 and url like ? order by ordering limit 1',$found_substr);
			if($dbq->rows==1){
				$subdomain_exists_in_DB=true;
				define('DOMAIN_ID',$dbq->line['id']);
				define('DOMAIN',$dbq->line['url']);
				//определяем префикс, например "/en"
				//если p2v('__domain__') не определен, значит язык не был передан, 
				//и значит префикс не определяется (равен пустоте)
				$prefix=empty($_GET['__domain__'])?'':'/~'.$dbq->line['url'].'~';
				define('DOMAIN_PATH',$prefix);
			}//_echo('DOMAIN is '.DOMAIN);_echo('DOMAIN_PATH is '.DOMAIN_PATH);
			if( !$subdomain_exists_in_DB ){
				setCookie('region_custom', '', time()- 3600, '/', removeSubdomain());
				hexit('Location: http://'.removeSubdomain());
				_die('данные для субдомена "'.$found_substr.'" отсутствуют в таблице structure');
			}
			//определяем главную страницу
			define('IS_FIRST',(bool)($_SERVER['REQUEST_URI']==DOMAIN_PATH.'/'));//_log('IS_FIRST set to '.chb(IS_FIRST));
			define('IS_FIRST_AFTER_REDIR',(bool)(count($GLOBALS['path'])==1));
		}
	}

	/**
	 * Выводим закешированую страницу для $_SERVER['REQUEST_URI'] и выполняем exit
	 * При отсутствии закешированой страницы - ничего не делаем
	 * Если страница устарела - чистим кеш (и файл и записи в таблицах _cache и _cache__models_rel)
	 * 
	 * Время жизни пишется первой строкой в файле кеша
	 *
	 */
	public static function tryUseCache(){
		//инклюдим кэш только если он используется
		if(USE_CACHE===true){
			try2useCache();
		}
	}

	public static function startSessions(){
		if(isset($GLOBALS['path'][1]) && $GLOBALS['path'][1]=='admin'){
			// время хранения данных на сервере (сек.)
			ini_set('session.gc_maxlifetime', '86400');
			// время хранения куки у клиента (сек.)
			// ноль — пока не закроют браузер
			ini_set('session.cookie_lifetime', '0');
			// использовать только куки (1 или 0)
			ini_set('session.use_only_cookies', '1');
		}
		// если используются субдомены, то делаем общую сессию для всех субдоменов
		if(USE_SUBDOMAINS===true){
			$cookie_domain='.'.removeSubdomain();
			ini_set('session.cookie_domain',$cookie_domain);
		}
		//стартуем сессию
		session_start();
	}

	public static function includeClasses(){
		include(FW_DIR.'/classes/compressor.class.php');
		if(USE_DB===true){
			include(FW_DIR.'/classes/admin.class.php');
			include(FW_DIR.'/classes/model.class.php');
			include(FW_DIR.'/classes/modelmanager.class.php');
			include(FW_DIR.'/classes/field.class.php');
			include(FW_DIR.'/classes/formitems.class.php');
			//model.cache.class.php и model.models.class.php нам нужно загружать в любом случае, 
			//поскольку функция кэширования может быть включена и выключена в любой момент, 
			//и при этом администратор может работать с контентом,
			//и закэшированные страницы должны своевременно удалятся 
			//ну а модель _Models необходима для корректной работы _Cache
			include(FW_DIR.'/classes/model.cache.class.php'); 
			include(FW_DIR.'/classes/model.models.class.php');
			if(false
				|| !isset($GLOBALS['path'][1])
				|| $GLOBALS['path'][1]!='admin' //для клиентской части
				|| (DEBUG===true && DEBUG_DB===true) //для админки, если включена отладка ДБ
			){
				include(SMARTY_LIBS.'/Smarty.class.php');
				include(FW_DIR.'/classes/clientside.class.php');
			}
		}
	}

	public static function run(){
		//устанавливаем кодировку
		$charset=defined('SITE_ENCODING')?strtolower(SITE_ENCODING):'utf-8';
		header('Content-Type: text/html; charset='.$charset);
		//создаем экземпляр класса Admin (он нам понадобится в любом случае)
		if(USE_DB!==false && !isset($GLOBALS['obj_admin'])){
			$GLOBALS['obj_admin']=new Admin();
		}

		//возможно следует запустить файл на сервере. 
		//определяем название файла (по-умолчанию - index.php)
		$script_location=$_SERVER['REQUEST_URI'];
		if(mb_substr($script_location,-1)=='/'){
			$script_location.='index.php';
		}
		//если установлен флаг DONT_INCLUDE_CALLEE, то прекращаем работу
		if(DONT_INCLUDE_CALLEE===true){return;}
		//проверяем, имеется ли файл на сервере
		if(file_exists(SITE_DIR.$script_location)){
			//файл присутствует
			//если запрашивается файл из специальной папки __<имя папки>, то не запускаем файл
			if(mb_substr($script_location,0,2)=='__'){return;}
			//если файл в админ-зоне, то это может быть только php-файл, и у пользователя должна быть сессия
			if($GLOBALS['path'][1]=='admin' && mb_substr($script_location,-4)=='.php'){
				if( isset($_SESSION['admin_user']) ){
					include(SITE_DIR.$script_location);
					// после того как файл был запущен, прекращаем работу
					exit();
				}
			}else{
				// если FW не используется, то просто подключаем smarty
				if( USE_DB===false ){
					include(SMARTY_LIBS.'/Smarty.class.php');
					include(FW_DIR.'/classes/clientside.class.php');
					$GLOBALS['obj_client']=new ClientSide();
				}
				include(SITE_DIR.$script_location);
				//если файл все-таки был запущен, то прекращаем работу
				exit();
			}
		}else{
			//файл отсутствует
			if(USE_DB===false){
				hstatus(404);
			}else{
				if($GLOBALS['path'][1]!='admin'){
					$GLOBALS['obj_client']=new ClientSide();
					$GLOBALS['obj_client']->init();
					$GLOBALS['obj_client']->runTemplate();
				}else{
					// запускаем метод ->autopage() объекта Admin
					$GLOBALS['obj_admin']->autopage();
				}
			}
			exit();
		}
	}
}
