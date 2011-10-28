<?php

class FW{
	public static function init(){
		// устанавливаем константы *_DIR
		self::setDIRs();

		// инклюдим конфиг и основные функции
		self::includeSitecfgAndBasefunc();

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
		
		// устанавливаем SERVER_NAME
		self::setServerName();
		
		// удаляем get-параметры из $_SERVER['REQUEST_URI']
		self::fixServerRequestUri();
		
		// создаем $GLOBALS['path_requested']
		self::setGlobalsPathRequested();

		// создаем $_SERVER['REDIRECT_URL']
		self::fixServerRedirectUrl();

		// создаем $GLOBALS['path']
		self::setGlobalsPath();
		
		// определяем принт-версию
		self::setPrintVersion();
		
		// определяем DOMAIN
		self::setDomain();
		
		// при необходимости делаем редирект на домен по-умолчанию
		self::defaultDomainRedirect();
		
		// определяем DOMAIN_ID
		self::setDomainId();
		
		// определяем DOMAIN_PATH
		self::setDomainPath();
		
		// устанавливаем IS_FIRST
		self::setIsFirst();
		
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
	 * устанавливает пути к корню сайта, файлам FW и моделям
	 * в константы SITE_DIR, FW_DIR, MODELS_DIR
	 */
	public static function setDIRs(){
		if( !defined('SITE_DIR') ){
			define('SITE_DIR', $_SERVER['DOCUMENT_ROOT']);
		}
		if( !defined('FW_DIR') ){
			define('FW_DIR', SITE_DIR.'/admin/fw');
		}
		if( !defined('MODELS_DIR') ){
			define('MODELS_DIR', SITE_DIR.'/admin/models');
		}
	}
	
	/**
	 * подключает конфиг config.php и вспомогательные функции functions.php
	 */
	public static function includeSitecfgAndBasefunc(){
		include_once(SITE_DIR.'/../config.php');
		include_once(FW_DIR.'/functions.php');
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

		// устанавливаем значения для констант DEBUG_*
		// делаем эту функцию немного сложнее, чтобы можно было ее протестировать
		$debug_option_values=self::getDebugOptionValues(DEBUG_OPTIONS);
		foreach($debug_option_values as $option_name=>$bool){
			if( !defined($option_name) ){
				define($option_name, $bool);
			}
		}
		
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
	public static function getDebugOptionValues($debug_options){
		$result=array();
		// используем _explode() для разбиения $debug_options
		// давая возможность записать эту константу в конфиге на свое усмотрение
		$user_debug_options=_explode($debug_options);

		$all_debug_options='DB DB_SAVE_STACK DB_INCLUDE_RES EMAIL_ERROR SMARTY_CONSOLE_ON TRACE_ON_DIE DISPLAY_PHP_ERRORS';
		$all_debug_options=explode(' ',$all_debug_options);

		foreach($all_debug_options as $option){
			if( in_array($option,$user_debug_options) ){
				$bool=true;
			}else{
				$bool=false;
			}
			$result['DEBUG_'.$option]=$bool;
		}
		return $result;
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
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest'){
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
		$use_db=false;
		if( defined('DBUSER') && defined('DBNAME') ){
			$use_db=true;
			include_once(FW_DIR.'/classes/db.class.php');
			$db=new DB();
			$db::connect();
		}
		define('USE_DB', $use_db);
	}
	
	/**
	 * устанавливает SERVER_NAME на основе переменных окружения
	 * $_SERVER['SERVER_NAME'] и $_SERVER['HTTP_HOST']
	 * если они не идентичны, выдается сообщение об ошибке
	 */
	public static function setServerName(){
		// возможно константу уже определили в config.php
		if( !defined('SERVER_NAME') ){
			$server_name=$_SERVER['SERVER_NAME'];
			// $_SERVER['HTTP_HOST'] может содержать двоеточие и номер порта (msk.meetafora.ru:80)
			$http_host=$_SERVER['HTTP_HOST'];
			if( $pos=mb_strpos($http_host,':')>0 ){
				$http_host=mb_substr($http_host,0,$pos);
			}
			// считаем что мы нашли SERVER_NAME если $server_name и $http_host идентичны
			// иначе просим пользователя задать SERVER_NAME в конфиге
			if( $server_name==$http_host ){
				define('SERVER_NAME', $server_name);
			}else{
				_die(sprintf('Не удалось корректно определить SERVER_NAME по "%s" и "%s". Пожалуйста задайте константу вручную в config.php', $_SERVER['SERVER_NAME'], $_SERVER['HTTP_HOST']));
			}
		}
	}

	/**
	 * при использовании кэширования злоумышленники могут забить кэш, делая
	 * запросы к одной и той же странице с разными get-парамтрами
	 * чтобы этого избежать, нужно удалить get-параметры из $_SERVER['REQUEST_URI']
	 * и запомнить его предыдущее значение
	 */
	public static function fixServerRequestUri(){
		// очищаем $_SERVER['REQUEST_URI'] от тегов, которые могли
		// добавить злоумышленники (например <script>...</script>)
		$_SERVER['REQUEST_URI']=strip_tags($_SERVER['REQUEST_URI']);
		// отбрасываем get-параметры
		if( $pos=mb_strpos($_SERVER['REQUEST_URI'],'?') ){
			$_SERVER['REQUEST_URI_ORIGINAL']=$_SERVER['REQUEST_URI'];
			$_SERVER['REQUEST_URI']=mb_substr($_SERVER['REQUEST_URI'],0,$pos);
		}
	}

	/**
	 * метод берет путь запрашиваемый путь ($_SERVER['REQUEST_URI'])
	 * разбивает его по слэшам и помещает в массив $GLOBALS['path_requested']
	 * (причем в нулевом элементе массива содержится SERVER_NAME)
	 */
	public static function setGlobalsPathRequested(){
		$server_name__request_uri=SERVER_NAME.$_SERVER['REQUEST_URI'];
		// избавляемся от слэша на конце, чтобы избежать пустого элемента в массиве
		if( mb_substr($server_name__request_uri, -1)=='/' ){
			$server_name__request_uri=mb_substr($server_name__request_uri,0,-1);
		}
		// получаем массив
		$GLOBALS['path_requested']=explode('/', $server_name__request_uri);
	}
	
	/**
	 * на основе $_GET['__uri__'] создаем $_SERVER['REDIRECT_URL'],
	 * который является результатом перенаправлений RewriteRule и пр.
	 * в большинстве случае $_SERVER['REDIRECT_URL'] 
	 * будет совпадать с $_SERVER['REQUEST_URI']
	 */
	public static function fixServerRedirectUrl(){
		// очищаем $_GET['__uri__'] от тегов, которые могли
		// добавить злоумышленники (например <script>...</script>)
		$_GET['__uri__']=strip_tags($_GET['__uri__']);
		// добавляем лидирующий слэш
		$_SERVER['REDIRECT_URL']='/'.$_GET['__uri__'];
	}
	
	/**
	 * метод берет путь, получаемый после редиректов (RewriteRule и пр.)
	 * (как раз этот путь содержится в $_SERVER['REDIRECT_URL'])
	 * разбивает его по слэшам и помещает в массив $GLOBALS['path']
	 * (причем в нулевом элементе массива содержится SERVER_NAME)
	 */
	public static function setGlobalsPath(){
		$server_name__redirect_url=SERVER_NAME.$_SERVER['REDIRECT_URL'];
		// избавляемся от слэша на конце, чтобы избежать пустого элемента в массиве
		if( mb_substr($server_name__redirect_url, -1)=='/' ){
			$server_name__redirect_url=mb_substr($server_name__redirect_url,0,-1);
		}
		// получаем массив
		$GLOBALS['path']=explode('/', $server_name__redirect_url);
	}

	/**
	 * определяем принт-версию на основе $_GET['verprint']
	 * (согласно правилу редиректа в корневом .htaccess 
	 * для перехода в режим принт-версии достаточно к адресу
	 * любой страницы добавить "?print", например:
	 * http://www.mysite.com/my/page/ — адрес страницы
	 * http://www.mysite.com/my/page/?print — адрес ее принт-версии)
	 */
	public static function setPrintVersion(){
		$bool=(bool)($_GET['__print_version__']==1);
		define('PRINT_VERSION',$bool);
	}
	
	/**
	 * устанавливает DOMAIN на основе запрашиваемого URL и config.php
	 * 
	 * в случае USE_MULTIDOMAINS===false, установка DOMAIN откладывается
	 * поскольку для этого понадобится сделать лишний запрос к БД
	 */
	public static function setDomain(){
		$domain=self::getDomainValue(USE_DB, USE_MULTIDOMAINS, SERVER_NAME, USE_SUBDOMAINS, DOMAINS_LIST, DEFAULT_DOMAIN, HIDE_DEFAULT_DOMAIN, $_GET['__domain__']);
		if( !defined('DOMAIN') && !empty($domain) ){
			define('DOMAIN',$domain);
		}
	}
	
	/**
	 * определяем домен, с учетом которого будут делаться запросы к БД
	 * данный метод сделан немного сложнее, чтобы его можно было тестировать
	 */
	public static function getDomainValue($use_db, $use_multidomains, $server_name, $use_subdomains, $domains_list, $default_domain, $hide_default_domain, $__domain__){
		$domain='';
		if( $use_db===true && $use_multidomains===true ){
			if( $use_subdomains===true ){
				// определяем домен из имени сервера
				$server_name_arr=explode('.',$server_name);
				// применяем _explode() чтобы дать возможность пользователю
				// указать любой разделитель для DOMAINS_LIST
				$domains_list_arr=_explode($domains_list);
				if( in_array($server_name_arr[0], $domains_list_arr) ){
					// известно, что запрошенный субдомен относится к списку возможных, запоминаем его
					$domain=$server_name_arr[0];
				}elseif( $hide_default_domain===true ){
					// запрашивается дефолтный субдомен
					$domain=$default_domain;
				}else{
					// возникла ошибка: домен не может быть определен корректно
					_die(sprintf('домен не может быть определен корректно, поскольку субдомен "%s" не входит в список возможных:', $server_name_arr[0]), $domains_list);
				}
			}else{
				if( !empty($__domain__) ){
					$domain=$__domain__;
				}elseif( $hide_default_domain===true ){
					$domain=$default_domain;
				}else{
					// возникла ошибка: домен не может быть определен корректно
					_die(sprintf('домен не может быть определен корректно, поскольку $_GET[__domain__]="%s" не входит в список возможных:', $__domain__), $domains_list);
				}
			}
		}
		
		return $domain;
	}

	/**
	 * если запрашивается домен по-умолчанию и если URL конфликтует с настройкой HIDE_DEFAULT_DOMAIN
	 * то делаем редирект на нужный доменный адрес, сохраняя запрашиваемый путь
	 * 
	 */
	public static function defaultDomainRedirect(){
		$redirection=self::getDefaultDomainRedirection(USE_DB, USE_MULTIDOMAINS, DOMAIN, SERVER_NAME, $_SERVER['REQUEST_URI'], USE_SUBDOMAINS, DEFAULT_DOMAIN, HIDE_DEFAULT_DOMAIN);
		if( !empty($redirection) ){
			hexit('Location: '.$redirection);
		}
	}
	
	/**
	 * метод определяет, нужно ли делать редирект на другую страницу
	 * редирект нужно делать в случае, если текущий домен является доменом по-умолчанию
	 * и если он конфликтует с настройкой HIDE_DEFAULT_DOMAIN
	 * т.е. редирект производится 
	 * 1. если HIDE_DEFAULT_DOMAIN==true и домен присутствует в адресе
	 * 2. если HIDE_DEFAULT_DOMAIN==false и домен отсутствует в адресе
	 * 
	 */
	public static function getDefaultDomainRedirection($use_db, $use_multidomains, $current_domain, $server_name, $URL, $use_subdomains, $default_domain, $hide_default_domain){
		$redirection='';
		if( $use_db===true && $use_multidomains===true ){
			if( $current_domain==$default_domain ){
				if( $use_subdomains===true ){
					$server_name_arr=explode('.',$server_name);
					if( $server_name_arr[0]==$current_domain && $hide_default_domain===true ){
						$server_name_arr=array_slice($server_name_arr, 1);
						$redirection=sprintf('%s%s', implode('.',$server_name_arr), $URL);
					}elseif( $server_name_arr[0]!=$current_domain && $hide_default_domain===false ){
						$redirection=sprintf('%s.%s%s', $current_domain, $server_name, $URL);
					}
				}else{
					$url_arr=explode('/', $URL);
					if( $url_arr[1]=='~'.$current_domain.'~' && $hide_default_domain===true ){
						$url_arr=array_slice($url_arr,2);
						$redirection=sprintf('%s/%s', $server_name, implode('/',$url_arr));
					}elseif( $url_arr[1]!='~'.$current_domain.'~' && $hide_default_domain===false ){
						$redirection=sprintf('%s/~%s~%s', $server_name, $current_domain, $URL);
					}
				}
			}
		}
		
		return $redirection;
	}

	/**
	 * метод устанавливает DOMAIN_ID (и DOMAIN если он не был установлен ранее)
	 * если USE_MULTIDOMAINS===true нужно вытащить из БД корневой раздел соответствующего домен
	 * иначе просто корневой раздел
	 */
	public static function setDomainId(){
		list($domain, $domain_id)=self::getDomainIdValue(USE_DB, USE_MULTIDOMAINS, DOMAIN);
		define('DOMAIN_ID', $domain_id);
		if( !defined('DOMAIN') ){
			define('DOMAIN', $domain);
		}
	}
	
	public static function getDomainIdValue($use_db, $use_multidomains, $domain){
		$domain_id=0;
		$domain='';
		if( $use_db===true ){
			if( $use_multidomains===false ){
				$dbq=new DBQ('select id, url from structure where parent=0 and url like "%"');
			}else{
				$dbq=new DBQ('select id, url from structure where parent=0 and url like ?', $domain);
			}
			if( $dbq->rows==0 ){
				_die('данные для домена "'.$domain.'" отсутствуют в таблице structure');
			}elseif( $dbq->rows > 1 ){
				_die('в таблице structure есть лишние данные для домена "'.$domain.'"');
			}else{
				$domain=$dbq->line['url'];
				$domain_id=$dbq->line['id'];
			}
		}
		
		return array($domain, $domain_id);
	}
	
	/**
	 * метод устанавливает DOMAIN_PATH в случае когда USE_SUBDOMAINS==false
	 */
	public static function setDomainPath(){
		$domain_path=self::getDomainPathValue(USE_MULTIDOMAINS, USE_SUBDOMAINS, DOMAIN);
		define('DOMAIN_PATH', $domain_path);
	}
	
	public static function getDomainPathValue($use_multidomains, $use_subdomains, $domain){
		$domain_path='';
		if( $use_multidomains===true && $use_subdomains===false ){
			$domain_path=sprintf('/~%s~',$domain);
		}
		return $domain_path;
	}

	public static function setIsFirst(){
		$bool=self::getIsFirstValue(USE_MULTIDOMAINS, USE_SUBDOMAINS, HIDE_DEFAULT_DOMAIN, DOMAIN_PATH, $GLOBALS['path_requested']);
		define('IS_FIRST', $bool);
	}
	
	/**
	 * метод определяет, запрашивается ли главная страница
	 * в простом случае достаточно взглянуть на $GLOBALS['path_requested']
	 * если он содержит всего 1 элемент, то значит запрашивается главная страница
	 * но возможен также вариант, когда используются мультидомены без субдоменов
	 * тогда главная страница может выглядеть как-то так /~msk~/
	 * 
	 */
	public static function getIsFirstValue($use_multidomains, $use_subdomains, $hide_default_domain, $domain_path, $globals_path_requested){
		$bool=false;
		if( $use_multidomains===true && $use_subdomains===false ){
			if( $hide_default_domain===true && count($globals_path_requested)==1 ){
				$bool=true;
			}elseif($hide_default_domain===false && count($globals_path_requested)==2 && $domain_path=='/'.$globals_path_requested[1]){
				$bool=true;
			}
		}elseif( count($globals_path_requested)==1 ){
			$bool=true;
		}
		
		return $bool;
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

	public static function includeClasses(){
		include_once(FW_DIR.'/classes/compressor.class.php');
		if(USE_DB===true){
			include_once(FW_DIR.'/classes/admin.class.php');
			include_once(FW_DIR.'/classes/model.class.php');
			include_once(FW_DIR.'/classes/modelmanager.class.php');
			include_once(FW_DIR.'/classes/field.class.php');
			include_once(FW_DIR.'/classes/formitems.class.php');
			//model.cache.class.php и model.models.class.php нам нужно загружать в любом случае, 
			//поскольку функция кэширования может быть включена и выключена в любой момент, 
			//и при этом администратор может работать с контентом,
			//и закэшированные страницы должны своевременно удалятся 
			//ну а модель _Models необходима для корректной работы _Cache
			include_once(FW_DIR.'/classes/model.cache.class.php'); 
			include_once(FW_DIR.'/classes/model.models.class.php');
			if(false
				|| !isset($GLOBALS['path'][1])
				|| $GLOBALS['path'][1]!='admin' //для клиентской части
				|| (DEBUG===true && DEBUG_DB===true) //для админки, если включена отладка ДБ
			){
				include_once(SMARTY_LIBS.'/Smarty.class.php');
				include_once(FW_DIR.'/classes/clientside.class.php');
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
					include_once(SITE_DIR.$script_location);
					// после того как файл был запущен, прекращаем работу
					exit();
				}
			}else{
				// если FW не используется, то просто подключаем smarty
				if( USE_DB===false ){
					include_once(SMARTY_LIBS.'/Smarty.class.php');
					include_once(FW_DIR.'/classes/clientside.class.php');
					$GLOBALS['obj_client']=new ClientSide();
				}
				include_once(SITE_DIR.$script_location);
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
