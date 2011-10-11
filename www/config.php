<?php

// запрещаем прямой доступ к файлу, чтобы его нельзя было открыть через браузер
// для безопасности, следует его размещать за пределами DOCUMENT_ROOT
if(mb_strpos($_SERVER['REQUEST_URI'],'/config.php')!==false){
	header($_SERVER["SERVER_PROTOCOL"].' 403 Forbidden');
	exit();
}

/*MySQL DB*/
define('DBHOST','localhost');
define('DBNAME','');
define('DBUSER','');
define('DBPASSWORD','');
define('DBSETNAMES','utf8');

/*субдомены*/
// будут ли использоваться субдомены 
// если false, то все остальные опции — SUBDOMAINS_LIST DEFAULT_SUBDOMAIN HIDE_DEFAULT_SUBDOMAIN — не имеют значения
define('USE_SUBDOMAINS',true);
// список используемых субдоменов (через пробел)
// например, если будут использоваться www.site.ru и sale.site.ru
// то нужно указать 'www sale'
define('SUBDOMAINS_LIST','www sale');
// основной субдомен (субдомен по-умолчанию)
// на него будет происходить редирект если адрес сайта набрали без субдомена
// site.ru -> www.site.ru
define('DEFAULT_SUBDOMAIN','www');
// следует ли скрывать основной субдомен
// чтобы избежать редиректа на основной субдомен при запросе сайта без субдомена
// но в этом случае будет происходить редирект 
// www.site.ru -> site.ru
define('HIDE_DEFAULT_SUBDOMAIN',true);

/*ключ yandex-карт*/
// используем переменную окружения SERVER_NAME чтобы задать разные ключи к картам
// в зависимости от адреса сайта
// (имейте в виду, что в SERVER_NAME не всегда может находиться имя сайта — это зависит от настроек веб-сервера)
if( $_SERVER['SERVER_NAME']=='site.local' ){
	define('MAPS_YANDEX_API_KEY','AAAAAAAAABBBBBBBBCCCCCCCCCDDDDDDDDEEEEEEEFFFFFFFFFF');
}elseif( $_SERVER['SERVER_NAME']=='site.ru' ){
	define('MAPS_YANDEX_API_KEY','AAAAAAAAABBBBBBBBCCCCCCCCCDDDDDDDDEEEEEEEFFFFFFFFFF');
}

/*почта*/
// кодировка сайта (windows-1251|UTF-8) нужна для корректной отправки писем
define('SITE_ENCODING','UTF-8');
define('FROM_NAME','Вася Пупкин');
define('FROM_EMAIL','no-reply@site.ru');
// следует ли записывать параметры отправляемых писем в лог /admin/_fw.log
define('LOG_EMAIL',true);

/*sms*/
define('SMSPILOT_APIKEY','AAAAAAAAABBBBBBBBCCCCCCCCCDDDDDDDDEEEEEEEFFFFFFFFFF');
define('SMS_SENDER','V.Pupkin');
// следует ли записывать параметры отправляемых sms в лог /admin/_fw.log
define('LOG_SMS',true);

//шифрование
define('CRYPT_METHOD','sha1');

/*кэширование*/
define('USE_CACHE',false);

/*smarty*/
// путь к библиотеке
define('SMARTY_DIR',$_SERVER['DOCUMENT_ROOT'].'/../__smarty/Smarty-3.0.8/libs/');
// путь к директории, в которой лежит папка с шаблонами
define('TPL_DIR',$_SERVER['DOCUMENT_ROOT'].'/../__smarty/');
// синтаксис тегов шаблонов (left_delimiter и right_delimiter разделены пробелом)
define('SMARTY_SYNTAX', '{ }');

/*библиотеки*/
define('LIB_DIR',$_SERVER['DOCUMENT_ROOT'].'/../__lib/');

/*отладка*/
// e-mail администратора (можно указать несколько адресов через пробел)
// сюда будут дублироваться сообщения из логов /admin/*.log
define('ADMIN_EMAIL','admin@site.ru');
// режим отладки
// если выключен, то 
// 1. никакие сообщения об ошибках не выводятся на экран
// 2. не работает функция _log()
define('DEBUG',true);
// если режим отладки включен, то можно настроить опции:
// DB - включить отладку запросов к DB
// DB_SAVE_STACK - при отладке DB сохранять стек вызовов функций
// DB_INCLUDE_RES - при отладке DB накапливать и выводить результаты запросов
// SMARTY_CONSOLE_ON - включить консоль Smarty
// TRACE_ON_DIE - вывести отладку после отработки die()
// DISPLAY_PHP_ERRORS - вывести ошибки php на экран (в любом случае выводятся в /admin/_php.log)
// START_ON_KEY - начать отладку только после получения $_GET['TRACE_KEY']==DEBUG_TRACE_KEY 
//                отключить отладку можно передав $_GET['TRACE_KEY']==false (другие значения могут иметь специфическое назначение)
//                если опция отсутствует, то управление отладкой (вкл/выкл) осуществляется только через DEBUG
define('DEBUG_OPTIONS', 'DISPLAY_PHP_ERRORS TRACE_ON_DIE');
