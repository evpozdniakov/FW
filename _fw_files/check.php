<?php

include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/admin/fw/functions.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/admin/models/_settings.php');

//проверяем включен ли дебаг
	if(DEBUG!==true){
		echo 'Необходимо включить функцию отладки: в файле config.php установить константе DEBUG значение true';
	}

//список файлов, которые должны быть открыты на запись
	$writable_ff=array(
		TPL_DIR.'templates_c/',
		$_SERVER['DOCUMENT_ROOT'].'/admin/_fw.log',
		$_SERVER['DOCUMENT_ROOT'].'/admin/_php.log',
		$_SERVER['DOCUMENT_ROOT'].'/admin/_sql.log',
	);
	//список папок, которые должны быть открыты на запись со своими подпапками
	$writable_ff=_checkAddFoldersAndFilesIntoArray($writable_ff,$_SERVER['DOCUMENT_ROOT'].'/admin/models/','tpl gif');
	// $writable_ff=_checkAddFoldersAndFilesIntoArray($writable_ff,$_SERVER['DOCUMENT_ROOT'].'/css/');
	$writable_ff=_checkAddFoldersAndFilesIntoArray($writable_ff,$_SERVER['DOCUMENT_ROOT'].'/media/css/');
	// $writable_ff=_checkAddFoldersIntoArray($writable_ff,$_SERVER['DOCUMENT_ROOT'].'/img/');
	$writable_ff=_checkAddFoldersAndFilesIntoArray($writable_ff,$_SERVER['DOCUMENT_ROOT'].'/media/img/');
	$writable_ff=_checkAddFoldersIntoArray($writable_ff,$_SERVER['DOCUMENT_ROOT'].'/u/');
	if(USE_CACHE===true){
		$writable_ff=_checkAddFoldersIntoArray($writable_ff,CACHE_DIR);
	}
	
	_checkRights($writable_ff,'is_writable');

//список файлов и папок, которые должны быть открыты на чтение
	$readable_ff=array(
		// $_SERVER['DOCUMENT_ROOT'].'/js/',
		$_SERVER['DOCUMENT_ROOT'].'/media/js/',
		$_SERVER['DOCUMENT_ROOT'].'/favicon.ico',
		$_SERVER['DOCUMENT_ROOT'].'/robots.txt',
		$_SERVER['DOCUMENT_ROOT'].'/admin/_fw.log',
		$_SERVER['DOCUMENT_ROOT'].'/admin/_php.log',
		$_SERVER['DOCUMENT_ROOT'].'/admin/_sql.log',
	);

	$readable_ff=_checkAddFoldersAndFilesIntoArray($readable_ff,$_SERVER['DOCUMENT_ROOT'].'/admin/models/','tpl gif');
	$readable_ff=_checkAddFoldersIntoArray($readable_ff,LIB_DIR);
	$readable_ff=_checkAddFoldersIntoArray($readable_ff,TPL_DIR.'templates/');
	
	_checkRights($readable_ff,'is_readable');

//запрет на исполнение скриптов в папке /u/
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/u/_test_execute.php')){
		fileWrite('/u/','_test_execute.php','<?php echo "test"."execute"; ?>');
	}
	_echo('Проверяем исполняются ли php-файлы в папке /u/','h3');
	$result=@implode("",@file('http://'.$_SERVER['SERVER_NAME'].'/u/_test_execute.php'));
	if(mb_strpos($result,'testexecute')===false){
		_echo('php-файлы в папке /u/ не являются исполняемыми','ok');
	}else{
		_echo('php-файлы в папке /u/ являются исполняемыми','alert');
		_echo('#необходимо создать файл /u/.htaccess следующего содержания:'.'<br>'.'RemoveHandler .php .phtml .php3'.'<br>'.'AddType application/x-httpd-php-source .php .phtml .php3');
	}
	

function _checkAddFoldersIntoArray($arr,$dir){
	$arr[]=$dir;
	$dir_items=_scandir($dir);
	foreach($dir_items as $item){
		if(mb_substr($item,0,1)=='.'){continue;}
		if(is_dir($dir.$item)){
			$arr=_checkAddFoldersIntoArray($arr,$dir.$item.'/');
		}
	}
	return $arr;
}

function _checkAddFoldersAndFilesIntoArray($arr,$dir,$skip_file_types=''){
	$arr[]=$dir;
	$dir_items=_scandir($dir);
	foreach($dir_items as $item){
		if(mb_substr($item,0,1)=='.'){continue;}
		if(is_dir($dir.$item)){
			$arr=_checkAddFoldersAndFilesIntoArray($arr,$dir.$item.'/',$skip_file_types);
		}else{
			$skip=false;
			if(!empty($skip_file_types)){
				foreach(explode(' ',$skip_file_types) as $ext){
					if($ext==mb_substr($item,-1*mb_strlen($ext))){
						$skip=true;
						break;
					}
				}
			}
			if(!$skip){
				$arr[]=$dir.$item;
			}
		}
	}
	return $arr;
}

function _checkRights($arr,$rights){
	switch($rights){
		case 'is_writable':
			_echo('Проверяем права на запись','h3');
			break;
		case 'is_readable':
			_echo('Проверяем права на чтение','h3');
			break;
	}

	foreach($arr as $item){
		switch($rights){
			case 'is_writable':
				$type=(is_writable($item))?'ok':'alert';
				break;
			case 'is_readable':
				$type=(is_readable($item))?'ok':'alert';
				break;
		}
		_echo($item."\n",$type);
	}
}
