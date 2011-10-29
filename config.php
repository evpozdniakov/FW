<?php



/* ************ НАСТРОЙКИ САЙТА ************ */

/*субдомены*/
// будет ли контент сайта распределятся по доменам
// если false, то все остальные опции — USE_SUBDOMAINS DOMAINS_LIST DEFAULT_DOMAIN HIDE_DEFAULT_DOMAIN — не имеют значения
define('USE_MULTIDOMAINS', false);
// настроен ли сервер на использование субдоменов
// если нет, то многодоменность будет достигнута с помощью префиксов 
// в пути к страницам сайта, вида ~domain~, например http://mysite.com/~msk~/
define('USE_SUBDOMAINS', true);
// список используемых субдоменов (через пробел)
// например, если будут использоваться www.site.ru и sale.site.ru
// то нужно указать 'www sale'
define('DOMAINS_LIST', 'msk spb');
// основной субдомен (субдомен по-умолчанию)
// на него будет происходить редирект если адрес сайта набрали без субдомена
// site.ru -> www.site.ru
define('DEFAULT_DOMAIN', 'msk');
// следует ли скрывать основной субдомен
// чтобы избежать редиректа на основной субдомен при запросе сайта без субдомена
// но в этом случае будет происходить редирект 
// www.site.ru -> site.ru
define('HIDE_DEFAULT_DOMAIN', true);

/*ключ yandex-карт*/
// используем переменную окружения SERVER_NAME чтобы задать разные ключи к картам
// в зависимости от адреса сайта
// (имейте в виду, что в SERVER_NAME не всегда может находиться имя сайта — это зависит от настроек веб-сервера)
if( $_SERVER['SERVER_NAME']=='site.local' ){
	define('MAPS_YANDEX_API_KEY', 'AAAAAAAAABBBBBBBBCCCCCCCCCDDDDDDDDEEEEEEEFFFFFFFFFF');
}elseif( $_SERVER['SERVER_NAME']=='site.ru' ){
	define('MAPS_YANDEX_API_KEY', 'AAAAAAAAABBBBBBBBCCCCCCCCCDDDDDDDDEEEEEEEFFFFFFFFFF');
}

/*почта*/
define('FROM_NAME', 'Вася Пупкин');
define('FROM_EMAIL', 'no-reply@site.ru');
// следует ли записывать параметры отправляемых писем в лог /admin/_fw.log
define('LOG_EMAIL', true);

/*sms*/
define('SMSPILOT_APIKEY', 'AAAAAAAAABBBBBBBBCCCCCCCCCDDDDDDDDEEEEEEEFFFFFFFFFF');
define('SMS_SENDER', 'V.Pupkin');
// следует ли записывать параметры отправляемых sms в лог /admin/_fw.log
define('LOG_SMS', true);



/* ************ СИСТЕМНЫЕ НАСТРОЙКИ ************ */

/*MySQL DB*/
define('DBHOST', 'localhost');
define('DBNAME', 'starter');
define('DBUSER', 'starter');
define('DBPASSWORD', 'starter');
define('DBSETNAMES', 'utf8');

/*кодировка сайта*/
define('SITE_ENCODING', 'UTF-8');

/*шифрование*/
define('CRYPT_METHOD', 'sha1');

/*библиотеки*/
define('LIB_DIR', $_SERVER['DOCUMENT_ROOT'].'/../lib');

/*smarty*/
// путь к библиотеке
define('SMARTY_LIBS', constant('LIB_DIR').'/Smarty-3.0.8/libs');
// путь к директории, в которой лежит папка с шаблонами
define('SMARTY_TPL_DIR', $_SERVER['DOCUMENT_ROOT'].'/../smarty');
// синтаксис тегов шаблонов (left_delimiter и right_delimiter разделены пробелом)
define('SMARTY_SYNTAX', '{ }');



/* ************ АДМИНКА ************ */

/*вшенший вид*/
// тема
// define('ADMIN_INTERFACE_THEME', 'default');

/*тестирование*/
// путь к PHPUnit
// define('PHPUNIT_DIR', '/usr/lib/php');



/* ************ РАБОЧИЙ ПРОЦЕСС ************ */

/*кэширование*/
// включение/выключение режима кэширования страниц сайта
define('USE_CACHE', true);
// папка кэша
define('CACHE_DIR', $_SERVER['DOCUMENT_ROOT'].'/../fwcache');

/*отладка*/
// e-mail администратора (можно указать несколько адресов через пробел)
// сюда будут дублироваться сообщения из логов /admin/*.log
define('ADMIN_EMAIL', 'admin@site.ru');
// режим отладки
// если выключен, то 
// 1. никакие сообщения об ошибках не выводятся на экран
// 2. не работает функция _log()
define('DEBUG', true);
// если режим отладки включен, то можно настроить опции, 
// перечислив их в DEBUG_OPTIONS:
// DB - включить отладку запросов к DB
// DB_SAVE_STACK - при отладке DB сохранять стек вызовов функций
// DB_INCLUDE_RES - при отладке DB накапливать и выводить результаты запросов
// EMAIL_ERROR - дублировать на адрес ADMIN_EMAIL записи, поступающие в логи
// TRACE_ON_DIE - вывести отладку после отработки die()
// DISPLAY_PHP_ERRORS - вывести ошибки php на экран (в любом случае выводятся в /admin/_php.log)
define('DEBUG_OPTIONS', 'DISPLAY_PHP_ERRORS');
