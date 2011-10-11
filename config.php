<?php

// запрещаем прямой доступ к файлу
if(mb_strpos($_SERVER['REQUEST_URI'],'/config.php')!==false){
	header($_SERVER["SERVER_PROTOCOL"].' 403 Forbidden');
	exit();
}

//DB
define('DBHOST','localhost');
define('DBNAME','');
define('DBUSER','');
define('DBPASSWORD','');
define('DBSETNAMES','utf8');

//субдомены
define('USE_SUBDOMAINS',true);
define('SUBDOMAINS_LIST','msk spb');
define('DEFAULT_SUBDOMAIN','msk');
define('HIDE_DEFAULT_SUBDOMAIN',true);

// ключ yandex-карт
if( $_SERVER['SERVER_NAME']=='site.local' ){
	define('MAPS_YANDEX_API_KEY','123098y41902hrp91hp9hr99r1-29834912y3-48234');
}elseif( $_SERVER['SERVER_NAME']=='site.ru' ){
	define('MAPS_YANDEX_API_KEY','090-190r7qwe0987rq0w9e87r90qw9876er98qw6e98r769871623948672');
}

//почта
define('SITE_ENCODING','UTF-8');//кодировка сайта (windows-1251|UTF-8) нужна для корректной отправки писем
define('FROM_NAME','Вася Пупкин');
define('FROM_EMAIL','no-reply@site.ru');
define('LOG_EMAIL',true);

// sms
define('SMSPILOT_APIKEY','12-034-129U3-0R10UR0QW9UE0RQ04JHR0QWHE0RQ0W');
define('SMS_SENDER','V.Pupkin');
define('LOG_SMS',true);


//шифрование
define('CRYPT_METHOD','sha1');

//кэширование
// define('USE_CACHE',false);
// define('AJAX_CACHE_ON',true);
// define('CACHE_DIR',$_SERVER['DOCUMENT_ROOT'].'/__cache/');

//smarty
define('SMARTY_DIR',$_SERVER['DOCUMENT_ROOT'].'/../__smarty/Smarty-3.0.8/libs/');
// define('SMARTY_DIR',$_SERVER['DOCUMENT_ROOT'].'/../__smarty/Smarty-2.6.26/libs/');
define('TPL_DIR',$_SERVER['DOCUMENT_ROOT'].'/../__smarty/');
define('SMARTY_SYNTAX', '{ }');

//библиотеки
define('LIB_DIR',$_SERVER['DOCUMENT_ROOT'].'/../__lib/');


//отладка
// define('ADMIN_EMAIL','admin@site.ru');
define('DEBUG',true);
// define('DEBUG_TRACE_KEY','1');
// define('DEBUG_OPTIONS', 'DISPLAY_PHP_ERRORS,TRACE_ON_DIE,DB,DB_SAVE_STACK,DB_INCLUDE_RES');
define('DEBUG_OPTIONS', 'DISPLAY_PHP_ERRORS,TRACE_ON_DIE');
/**
 * !defined('DEBUG') or !DEBUG запрещают любую отладку
 * Доступные опции отладки (DEBUG_OPTIONS - ключи отделённые запятыми):
 * DB - включить отладку запросов к DB
 * DB_SAVE_STACK - при отладке DB сохранять стек вызовов функций
 * DB_INCLUDE_RES - при отладке DB накапливать и выводить результаты запросов
 * SMARTY_CONSOLE_ON - включить консоль Smarty
 * TRACE_ON_DIE - вывести отладку после отработки die()
 * DISPLAY_PHP_ERRORS - вывести ошибки php на экран (в любом случае выводятся в /admin/_php.log)
 * START_ON_KEY - начать отладку только после получения $_GET['TRACE_KEY']==DEBUG_TRACE_KEY 
 *                отключить отладку можно передав $_GET['TRACE_KEY']==false (другие значения могут иметь специфическое назначение)
 *                если опция отсутствует, то управление отладкой (вкл/выкл) осуществляется только через DEBUG
 */
