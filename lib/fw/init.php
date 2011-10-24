<?php

//инклюдим конфиг и вспомогательные функции
include(LIB_DIR.'/fw/functions.php');

//дебаг
_debugInit();

//настраиваем PHP
_setPHP();

//настраиваем mbstring
_startMbstring();

//если включен magic_qutes_gpc, то очищаем данные после него
_magicQutesGpcDecoding();

//определяем наличие ajax-запроса
_isAjax();

//в случае ajax-запроса перекодируем из utf-8
//нужно включать лишь в том случае, когда сайт создан в кодировке cp1251
_utf8Decoding();

// подключаемся к БД
_connectDB();

//создаем $GLOBALS['path']
_createGlobals();

//определяем языковую версию
_setDomain();

//стартуем сессии
_startSessions();

//пытаемся вернуть закэшированную страницу
_tryUseCache();

//инклюдим классы
_includeClasses();

//запускаемся
_run();

//-------------------------------------------------------------------


function _debugInit(){
	if(!defined('DEBUG')){define('DEBUG', false);}

	$allow_debug_start=false;//негативно предопределяем $allow_debug_start
	if(DEBUG===true){
		//сбрасываем счетчик microtime default
		_microtime('default',true);
		if(defined('DEBUG_OPTIONS') && mb_strpos(DEBUG_OPTIONS,'START_ON_KEY')!==false){
			//определяем в зависимости от куки и от $_GET['TRACE_KEY']
			$allow_debug_start=_debugInitCheckTraceKey();
		}else{
			//известно, что определен DEBUG и не определен START_ON_KEY, 
			//значит мы сразу разрешаем отладку, не дожидаясь прихода $_GET['TRACE_KEY']
			$allow_debug_start=true;
		}
	}
	
	/*
		в этом месте мы берем все ключи, которые переданы в DEBUG_OPTIONS
		и образуем новые константы с преддобавлением DEBUG_
		например если DEBUG_OPTIONS='DB,DB_SAVE_STACK,DB_INCLUDE_RES,SMARTY_CONSOLE_ON', 
		то будут получены такие константы: DEBUG_DB, DEBUG_DB_SAVE_STACK и т.д.
	*/
	$all_debug_options='DB,DB_SAVE_STACK,DB_INCLUDE_RES,EMAIL_ERROR,SMARTY_CONSOLE_ON,TRACE_ON_DIE,DISPLAY_PHP_ERRORS';//кроме START_ON_KEY который используется выше для определения $allow_debug_start
	$all_debug_options=explode(',',$all_debug_options);
	if(defined('DEBUG_OPTIONS')){
		$local_debug_options=explode(',',trim(DEBUG_OPTIONS));
	}else{
		$local_debug_options=array();//присваиваем пустой массив, если DEBUG_OPTIONS не задан
	}
	foreach($all_debug_options as $constant_name){
		if($allow_debug_start && array_search($constant_name,$local_debug_options)!==false){
			$constant_value=true;
		}else{
			$constant_value=false;
		}
		define('DEBUG_'.$constant_name, $constant_value);
	}
	
	ini_set('error_reporting', (E_ERROR|E_PARSE|E_WARNING|E_CORE_ERROR|E_CORE_WARNING|E_COMPILE_ERROR|E_COMPILE_WARNING|E_USER_ERROR|E_USER_WARNING));
	//ini_set('error_reporting', (E_ALL));
	ini_set('log_errors', 1);
	ini_set('display_errors', ((DEBUG===true && DEBUG_DISPLAY_PHP_ERRORS===true)?1:0));
	ini_set('error_log', SITE_DIR.'/admin/_php.log');
	ini_set('track_errors', true);

	if(DEBUG_DB===true){
		require_once(LIB_DIR. 'debug/debug_db.php');
		new Debug_db(DEBUG_DB_SAVE_STACK, DEBUG_DB_INCLUDE_RES);
	}
}

function _debugInitCheckTraceKey(){
	if(!defined('DEBUG_TRACE_KEY'))
		return false;

	$cookie_value = md5(constant('DEBUG_TRACE_KEY').$_SERVER['HTTP_X_REAL_IP']);
	if(isset($_GET['TRACE_KEY'])){
		$trace_key = $_GET['TRACE_KEY'];
		if($trace_key == DEBUG_TRACE_KEY) { // Ставим кукис-флаг отладки
			$allow_debug_start = true;
			setcookie('DEBUG_TRACE_KEY', $cookie_value);
		}
		else{								// Сносим кукис-флаг отладки
			$allow_debug_start = false; 
			setcookie('DEBUG_TRACE_KEY', $cookie_value, time() - 3600*24);
		}
	}
	else {
		$exists_cookie = isset($_COOKIE['DEBUG_TRACE_KEY']) ? $_COOKIE['DEBUG_TRACE_KEY'] : false;
		$allow_debug_start = ($exists_cookie == $cookie_value) ? true : false;
	}

	return $allow_debug_start;
}

function _setPHP(){
	//таймзон
	if(function_exists('date_default_timezone_set')){
		date_default_timezone_set('Europe/Moscow');
	}
	//проверяем наличие конфигурации
	if(!defined('SITE_ENCODING')){_die('необходимо задать SITE_ENCODING: "windows-1251", "UTF-8" либо еще что-то');}
	//локаль
	//TODO здесь нужно добавить установку локали для других кодировок
	if(SITE_ENCODING=='UTF-8'){
		setlocale(LC_ALL, 'en_US.UTF-8');
	}elseif(SITE_ENCODING=='windows-1251'){
		setlocale(LC_ALL, 'ru_RU.CP1251', 'rus_RUS.1251');
	}
	ini_set('allow_call_time_pass_reference',0);
}

function _startMbstring(){
	ini_set('mbstring.language','Neutral');
	ini_set('mbstring.internal_encoding',SITE_ENCODING);
	ini_set('mbstring.http_input','pass');
	ini_set('mbstring.http_output','pass');
	ini_set('mbstring.encoding_translation','Off');
	ini_set('mbstring.detect_order','auto');
	ini_set('mbstring.substitute_character','none');
	ini_set('mbstring.func_overload',7);
}

function _magicQutesGpcDecoding(){
	ini_set('magic_quotes_gpc',0);
	if(get_magic_quotes_gpc()){
		function undoMagicQuotes($array,$topLevel=true){
			$newArray=array();
			foreach($array as $key=>$value){
				if(!$topLevel){
					$key=stripslashes($key);
				}
				if(is_array($value)){
					$newArray[$key]=undoMagicQuotes($value,false);
				}else{
					$newArray[$key]=stripslashes($value);
				}
			}
			return $newArray;
		}
		
		$_GET=undoMagicQuotes($_GET);
		$_POST=undoMagicQuotes($_POST);
		$_COOKIE=undoMagicQuotes($_COOKIE);
		$_REQUEST=undoMagicQuotes($_REQUEST);
	}
}

function _isAjax(){
	if($_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest'){
		define('IS_AJAX',true);
	}elseif(p2v('json')==1 && DEBUG===true){
		define('IS_AJAX',true);
	}else{
		define('IS_AJAX',false);
	}
}

function _utf8Decoding(){
	if(IS_AJAX && strtolower(SITE_ENCODING)!='utf-8'){
		$_POST=_utf8DecodingRec($_POST);
		$_GET=_utf8DecodingRec($_GET);
		$_REQUEST=_utf8DecodingRec($_REQUEST);
	}
}

function _utf8DecodingRec($arr){
	foreach($arr as $key=>$value){
		if(is_array($value)){
			$value=_utf8DecodingRec($value);
		}else{
			$value=iconv('utf-8',SITE_ENCODING,$value);
		}
		$result[$key]=$value;
	}

	return $result;
}

function _connectDB(){
	if(USE_FW===false){return;}
	include(LIB_DIR.'/fw/classes/db.class.php');
	$db=new DB();
	$db->connect();
}

function _createGlobals(){
	
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

function _setDomain(){
	if(USE_FW===false){return;}
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

/**
 * Выводим закешированую страницу для $_SERVER['REQUEST_URI'] и выполняем exit
 * При отсутствии закешированой страницы - ничего не делаем
 * Если страница устарела - чистим кеш (и файл и записи в таблицах _cache и _cache__models_rel)
 * 
 * Время жизни пишется первой строкой в файле кеша
 *
 */
function _tryUseCache(){
	//инклюдим кэш только если он используется
	if(USE_CACHE===true){
		try2useCache();
	}
}

function _startSessions(){
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

function _includeClasses(){
	include(LIB_DIR.'/fw/classes/compressor.class.php');
	if(USE_FW===false){return;}
	include(LIB_DIR.'/fw/classes/admin.class.php');
	include(LIB_DIR.'/fw/classes/model.class.php');
	include(LIB_DIR.'/fw/classes/modelmanager.class.php');
	include(LIB_DIR.'/fw/classes/field.class.php');
	include(LIB_DIR.'/fw/classes/formitems.class.php');
	//model.cache.class.php и model.models.class.php нам нужно загружать в любом случае, 
	//поскольку функция кэширования может быть включена и выключена в любой момент, 
	//и при этом администратор может работать с контентом,
	//и закэшированные страницы должны своевременно удалятся 
	//ну а модель _Models необходима для корректной работы _Cache
	include(LIB_DIR.'/fw/classes/model.cache.class.php'); 
	include(LIB_DIR.'/fw/classes/model.models.class.php');
	if(false
		|| !isset($GLOBALS['path'][1])
		|| $GLOBALS['path'][1]!='admin' //для клиентской части
		|| (DEBUG===true && DEBUG_DB===true) //для админки, если включена отладка ДБ
	){
		include(SMARTY_LIBS.'/Smarty.class.php');
		include(LIB_DIR.'/fw/classes/clientside.class.php');
	}
}

function _run(){
	//устанавливаем кодировку
	$charset=defined('SITE_ENCODING')?strtolower(SITE_ENCODING):'utf-8';
	header('Content-Type: text/html; charset='.$charset);
	//создаем экземпляр класса Admin (он нам понадобится в любом случае)
	if(USE_FW!==false && !isset($GLOBALS['obj_admin'])){
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
			if( USE_FW===false ){
				include(SMARTY_LIBS.'/Smarty.class.php');
				include(LIB_DIR.'/fw/classes/clientside.class.php');
				$GLOBALS['obj_client']=new ClientSide();
			}
			include(SITE_DIR.$script_location);
			//если файл все-таки был запущен, то прекращаем работу
			exit();
		}
	}else{
		//файл отсутствует
		if(USE_FW===false){
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

// exit(); 
// здесь нельзя делать exit(), потому что иначе не получится включить init.php в начало другого файла
// это бывает нужно тогда, когда php-файл находится в специальной папке __<имя папки> и не может быть запущен 
// а также может быть включен флаг DONT_INCLUDE_CALLEE, что тоже запрещает вызов php-скрипта из init.php

