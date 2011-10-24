<?php

/*
header('Content-type: text/html; charset=windows-1251');
include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/init.php');
include_once(LIB_DIR.'/xml/easy_xml.php');

$xml=implode('',file('http://www.cbr.ru/scripts/XML_daily.asp'));

if($xml==''){
	die('невозможно получить xml-файл по адресу http://www.cbr.ru/scripts/XML_daily.asp');
}else{
	_echo('исходный xml','h3');
	_print_r(e5c($xml));
	
	_echo('полученный массив','h3');
	$data = XML_unserialize($xml);
	_print_r($data);
}*/
