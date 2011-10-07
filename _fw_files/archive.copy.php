<?php

// php_value upload_max_filesize 150M
// php_value post_max_size 160M
// php_value memory_limit 16M
ini_set('num_files_hint', '0');
// php_value error_reporting 255
// php_value short_open_tag 0

/*
	TODO 
нужно после формирования дампа делать замену
timestamp DEFAULT 'CURRENT_TIMESTAMP' NOT NULL
или
timestamp DEFAULT 'CURRENT_TIMESTAMP' NOT NULL on update CURRENT_TIMESTAMP
на
timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
*/

/*
скрипт /_archive.php делается стандартным
кладется в корень сайта
при обращении к нему нужна админ-su-авторизация
или же не нужна (если нет файлика /config.php)

либо разворачивает файл _archivedata.zip (если нет /config.php)
либо создает его (если есть /config.php) (альтернативное дествие тоже возможно)

СОЗДАНИЕ АРХИВА
делается проверка на права записи в папку
(делается проверка на наличие в config.php констант  DBSETNAMES)
предлагаются опции:
пропускать файлы ._*
копировать папку LIB_DIR
копировать папку SMARTY_DIR
копировать папку img
копировать папку u
создавать ли дамп базы (проверить наличие DBSETNAMES)
*/

//определяем некоторые параметры, необходимые для дальнейшей работы
init();

//проверяем авторизацию
checkAuth();

//запрашиваем у пользователя подтверждение на создание архива или распаковку
confirm();

//запаковываем или распаковываем
zipUnzip();

echo 'end';

function init(){
	if(!defined('SITE_DIR')){
		define('SITE_DIR',$_SERVER['DOCUMENT_ROOT']);
	}

	ini_set('error_reporting', (E_ERROR|E_PARSE|E_WARNING|E_CORE_ERROR|E_CORE_WARNING|E_COMPILE_ERROR|E_COMPILE_WARNING|E_USER_ERROR|E_USER_WARNING));
	ini_set('log_errors', 1);
	ini_set('display_errors', 1);
	ini_set('error_log', SITE_DIR.'/_archive.log');

	define('SITE_EXISTS',file_exists(SITE_DIR.'/config.php'));
	if(session_id()==''){
		session_start();
	}
	if(!empty($_POST)){
		$_SESSION['_archive']=array();
		if(isset($_POST['zip'])){
			$_SESSION['_archive']['action']='zip';
		}elseif(isset($_POST['unzip'])){
			$_SESSION['_archive']['action']='unzip';
		}
	}
}

/**
	* если нет авторизации, скрипт прекращает работу php
	*/
function checkAuth(){
	if(SITE_EXISTS===true){
		//поскольку файл /config.php существует, то вероятно есть и готовый сайт
		//а раз так, то нужна авторизация для запуска скрипта /_archive.php
		if(!isset($_SESSION['admin_user'])){
			die('требуется авторизация');
		}elseif($_SESSION['admin_user']['su']!='yes'){
			die('требуется авторизация суперадмина (отсутсвует сессия $_SESSION[\'admin_user\'][\'su\']=="yes")');
		}
	}else{
		//сайта нет, авторизация не требуется
	}
}

function confirm(){
	if(isset($_SESSION['_archive']['action'])){return;}
	$id=(SITE_EXISTS===true)?'zipBtn':'unzipBtn';
	echo '
		<html>
			<head>
				<title>archive action</title>
				<meta http-equiv="content-type" content="text/html; charset=utf-8"><meta http-equiv="content-type" content="text/html; charset=utf-8">
				<script type="text/javascript">
					function setFocus(){
						document.getElementById("'.$id.'").focus();
					}
				</script>
			</head>
			<body onload="setFocus()">
				<form id="archiveForm" method="post" action="/_archive.php">
					<input type="hidden" name="action" value="zipUnzip">
					<input id="zipBtn" type="submit" name="zip" value="Архивировать">
					<input id="unzipBtn" type="submit" name="unzip" value="Распаковывать">
				</form>
			</body>
		</html>
	';
}

function zipUnzip(){
	if(isset($_SESSION['_archive']) && $_SESSION['_archive']['action']=='zip'){
		zipOptions();
		zip();
	}elseif(isset($_SESSION['_archive']) && $_SESSION['_archive']['action']=='unzip'){
		unzip();
	}
	unset($_SESSION['_archive']);
}

function zipOptions(){
	//проверяем есть ли права на запись в корне
	if(!is_writable(SITE_DIR)){
		die('у корневой папки нет права на запись');
	}
	//проверяем есть ли файл _archivedata.zip и можем ли мы его перезаписать
	if(file_exists(SITE_DIR.'_archivedata.zip')){
		if(!is_writable(SITE_DIR.'_archivedata.zip')){
			die('файл  /_archivedata.zip недоступен для записи');
		}
	}
	//задаем опции для создания архива
	$options=array(
		// 'createDump'=>'создать дамп',
		// 'copySmarty'=>'копировать папку '.SMARTY_DIR,
		// 'copyHidden'=>'копировать скрытые файлы',
	);
	$options_paths=array(
		'copyConfig'=>'/config.php',
		'copyLib'=>'/__lib',
		'copyFW'=>'/admin/fw',
		'copyImg'=>'/img',
		'copyMediaImg'=>'/media/img',
		'copyU'=>'/u',
	);
	foreach($options_paths as $key=>$path){
		if( is_dir(SITE_DIR.$path) ){
			$options[$key]=sprintf('<span class="folder">%s</span>',$path);
		}elseif( file_exists(SITE_DIR.$path) ){
			$options[$key]=sprintf('<span class="file">%s</span>',$path);
		}
	}
	//устанавливам опции, если форма уже отправлена
	if(isset($_POST['action']) && $_POST['action']=='zipOptions'){
		$_SESSION['_archive']['options']=array();
		foreach($options as $key=>$item){
			if(isset($_POST[$key])){
				$_SESSION['_archive']['options'][$key]=$_POST[$key];
				setcookie('_archive_'.$key,$_POST[$key],time()+3600*24*365,'/');
			}else{
				setcookie('_archive_'.$key,'',time()-3600*24,'/');
			}
		}
		return;
	}
	//формируем опции
	echo '
		<html>
			<head>
				<title>archive options</title>
			</head>
			<body>
				<form id="settingsForm" method="post" action="/_archive.php">
					<div>
						<input type="hidden" name="action" value="zipOptions">
						
						<p>По-умолчанию в архив добавляются следующие папки и файлы:</p>
						<ul style="list-style-type: none;line-height: 1.5em">
							<li>.htaccess</li>
							<li>.htpassword</li>
							<li>/__smarty</li>
							<li>/admin/models</li>
							<li>/js (или /media/js)</li>
							<li>/css (или /media/css)</li>
							<li>/robots.txt</li>
							<li>favicon.ico</li>
						</ul>
						<p>Выберите, что нужно архивировать дополнительно:</p>
						<ul style="list-style-type: none;line-height: 1.5em">
	';
	foreach($options as $key=>$title){
		$checked=(isset($_COOKIE['_archive_'.$key]) && $_COOKIE['_archive_'.$key]=='1')?'checked':'';
		echo '<li><input '.$checked.' id="'.$key.'ChboxId" type="checkbox" name="'.$key.'" value="1"> <label for="'.$key.'ChboxId">'.$title.'</label></li>';
	}
	echo '
						</ul>
						<p><input id="zipBtn" type="submit" name="zip" value="Архивировать"></p>
					</div>
				</form>
			</body>
		</html>
	';
}

function zip(){
	if(!isset($_SESSION['_archive']['options'])){return;}
	//подключаем /config.php
	include_once(SITE_DIR.'/config.php');
	//создаем zip-объект
	$filename=SITE_DIR.'/_archivedata.zip';
	//если файл существует, стирем его содержимое
	if(file_exists($filename)){
		//открываем файл на запись
		$f=@fopen($filename,"w+") or _die('Не могу записать файл «'.$filename.'». Скорее всего, нет доступа.');
		//записываем
		fputs($f,'');
		//закрываем файл
		fclose($f);
	}
	$zip=new ZipArchive();
	if( $zip->open($filename,constant('ZIPARCHIVE::OVERWRITE'))!==true ){
		die('cannot open '.$filename);
	}
	//если нужно создаем дамп
	if(isset($_SESSION['_archive']['options']['createDump']) && $_SESSION['_archive']['options']['createDump']=='1'){
		zipDBdump();
	}
	//рекурсивно проходимся по основным разделам сайта и помещаем их в архив
	zipRec($zip,SITE_DIR);
	//выводим статистику и закрываем zip-объект
	echo 'numfiles: '.$zip->numFiles.'<br>';
	echo 'status:'.$zip->status.'<br>';
	$zip->close();
}

function zipRec($zip,$dir){
	$dir.='/';
	echo 'look '.$dir.'<br>';
	echo is_dir($dir).'<br>';
	$options=$_SESSION['_archive']['options'];
	if( is_dir($dir) ){
		$dir_items=scandir($dir);
		// _print_r('$dir_items',$dir_items);
		$rel_dir=mb_substr($dir,mb_strlen(SITE_DIR)+1);
		if( is_array($dir_items) ){
			foreach($dir_items as $item){
				if($item=='.' || $item=='..'){continue;}
				//при копировании файлов из корневой директории действует большее кол-во проверок
				if($dir==SITE_DIR.'/'){
					if(is_dir($dir.$item)){
						if($dir.$item.'/'==LIB_DIR && !isset($options['copyLib'])){continue;}
						if(isset($options['copySmarty']) && $options['copySmarty']=='1'){die('эта опция еще не реализована');}
						if($item=='img' && !isset($options['copyImg'])){continue;}
						if($item=='u' && !isset($options['copyU'])){continue;}
					}else{
						if($item=='config.php' && !isset($options['copyConfig'])){continue;}
					}
					//пропускаем все папки/файлы, которые не являются элементами сайта
					if(true
						&& $item!='.htaccess'
						&& $item!='.htpasswd'
						&& $item!='__cache'
						&& $item!='__lib'
						&& $item!='__smarty'
						&& $item!='_archive.dump.sql'
						&& $item!='_export.sh'
						&& $item!='_i.php'
						&& $item!='_readme'
						&& $item!='admin'
						&& $item!='config.php'
						&& $item!='css'
						&& $item!='favicon.ico'
						&& $item!='img'
						&& $item!='js'
						&& $item!='media'
						&& $item!='robots.txt'
						&& $item!='u'
					){
						//_print_r('continue');
						continue;
					}
				}elseif($dir==SITE_DIR.'/media/'){
					if($item=='img' && !isset($options['copyMediaImg'])){continue;}
				}
				if(is_dir($dir.$item) && $dir.$item.'/'==SITE_DIR.'/admin/fw/' && !isset($options['copyFW'])){continue;}
				if(mb_substr($item,0,1)=='.' && !isset($options['copyHidden']) && $item!='.htaccess' && $item!='.htpasswd'){continue;}

				if(is_dir($dir.$item)){
					zipRec($zip,$dir.$item);
				}else{
					//_print_r('$zip->addFile('.$dir.$item.','.$rel_dir.$item.')');
					$zip->addFile($dir.$item,$rel_dir.$item);
				}
			}
		}
	}
}

function zipDBdump(){
	//проверяем, заданы ли необходимые параметры в конфиге
	if(!defined('SITE_ENCODING')){
		die('константа SITE_ENCODING не задана в /config.php (установите ей значение UTF-8 или WINDOWS-1251)');
	}
	//подклучаемся к БД
	$GLOBALS['__dbc__']=mysql_connect(DBHOST, DBUSER, DBPASSWORD) or _die(mysql_error());
	mysql_select_db(DBNAME) or _die(mysql_error());
	if(defined('DBSETNAMES')){
		mysql_query('SET NAMES '.DBSETNAMES);
	}
	//Use this for plain text and not compressed file
	$dumper = new MySQLDump(DBNAME,SITE_DIR.'/_archive.dump.sql',false,false);
	//Dumps all the database
	$dumper->doDump();
	/*
	//возможно был задан порт
	if(mb_strpos(DBNAME,':')){
		list($dbname,$dbport)=explode(':',DBNAME);
		$setport='--port='.$dbport;
	}else{
		$dbname=DBNAME;
		$setport='';
	}
	$exec='mysqldump -u'.DBUSER.' -p'.DBPASSWORD.' --skip-set-charset --default-character-set='.$charset.' '.$setport.' '.$dbname.' > '.SITE_DIR.'/_archive.dump.sql';
	echo 'пытаюсь создать дамп базы:<br>'.$exec.'<br>';
	echo exec($exec);
	*/
}

function unzip(){
	$in_file=SITE_DIR.'/_archivedata.zip';
	$z = zip_open($in_file) or die("can't open $in_file: $php_errormsg");
	while ($entry = zip_read($z)) {
		
		$entry_name = zip_entry_name($entry);
	
		// check if all files should be unzipped, or the name of
		// this file is on the list of specific files to unzip
		if (true) {
	
			// only proceed if the file is not 0 bytes long
			if (zip_entry_filesize($entry)) {
				$entry_name=SITE_DIR.'/'.$entry_name;
				$dir = dirname($entry_name);
				//echo '$entry_name='.$entry_name.'<br>';
				//echo '$dir='.$dir.'<br>';
	
				// make all necessary directories in the file's path
				if (! is_dir($dir)) { pc_mkdir_parents($dir); }
	
				$file = basename($entry_name);
	
				if (zip_entry_open($z,$entry)) {
					if ($fh = fopen($dir.'/'.$file,'w')) {
						// write the entire file
						fwrite($fh,
								 zip_entry_read($entry,zip_entry_filesize($entry)))
							or error_log("can't write: $php_errormsg");
						fclose($fh) or error_log("can't close: $php_errormsg");
					} else {
						error_log("can't open $dir/$file: $php_errormsg");
					}
					zip_entry_close($entry);
				} else {
					error_log("can't open entry $entry_name: $php_errormsg");
				}
			}
		}
	}
}

function pc_mkdir_parents($d,$umask = 0777) {
	$dirs = array($d);
	$d = dirname($d);
	$last_dirname = '';
	while($last_dirname != $d) { 
		array_unshift($dirs,$d);
		$last_dirname = $d;
		$d = dirname($d);
	}

	foreach ($dirs as $dir) {
		if (! file_exists($dir)) {
			if (! mkdir($dir,$umask)) {
				error_log("Can't make directory: $dir");
				return false;
			}
		} elseif (! is_dir($dir)) {
			error_log("$dir is not a directory");
			return false;
		}
	}
	return true;
}

/**
* Dump MySQL database
*
* Here is an inline example:
* <code>
* $connection = @mysql_connect($dbhost,$dbuser,$dbpsw);
* $dumper = new MySQLDump($dbname,'filename.sql',false,false);
* $dumper->doDump();
* </code>
*
* Special thanks to:
* - Andrea Ingaglio <andrea@coders4fun.com> helping in development of all class code
* - Dylan Pugh for precious advices halfing the size of the output file and for helping in debug
*
* @name    MySQLDump
* @author  Daniele ViganÚ - CreativeFactory.it <daniele.vigano@creativefactory.it>
* @version 2.20 - 02/11/2007
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
class MySQLDump {
	/**
	* @access private
	*/
	var $database = null;

	/**
	* @access private
	*/
	var $compress = false;

	/**
	* @access private
	*/
	var $hexValue = false;

  /**
	* The output filename
	* @access private
	*/
	var $filename = null;

	/**
	* The pointer of the output file
	* @access private
	*/
	var $file = null;

	/**
	* @access private
	*/
	var $isWritten = false;

	/**
	* Class constructor
	* @param string $db The database name
	* @param string $filepath The file where the dump will be written
	* @param boolean $compress It defines if the output file is compress (gzip) or not
	* @param boolean $hexValue It defines if the outup values are base-16 or not
	*/
	function MYSQLDump($db = null, $filepath = 'dump.sql', $compress = false, $hexValue = false){
		$this->compress = $compress;
		if ( !$this->setOutputFile($filepath) )
			return false;
		return $this->setDatabase($db);
	}

	/**
	* Sets the database to work on
	* @param string $db The database name
	*/
	function setDatabase($db){
		$this->database = $db;
		if ( !@mysql_select_db($this->database) )
			return false;
		return true;
  }

	/**
	* Returns the database where the class is working on
	* @return string
	*/
  function getDatabase(){
		return $this->database;
	}

	/**
	* Sets the output file type (It can be made only if the file hasn't been already written)
	* @param boolean $compress If it's true, the output file will be compressed
	*/
	function setCompress($compress){
		if ( $this->isWritten )
			return false;
		$this->compress = $compress;
		$this->openFile($this->filename);
		return true;
  }

	/**
	* Returns if the output file is or not compressed
	* @return boolean
	*/
  function getCompress(){
		return $this->compress;
	}

	/**
	* Sets the output file
	* @param string $filepath The file where the dump will be written
	*/
	function setOutputFile($filepath){
		if ( $this->isWritten )
			return false;
		$this->filename = $filepath;
		$this->file = $this->openFile($this->filename);
		return $this->file;
  }

  /**
	* Returns the output filename
	* @return string
	*/
  function getOutputFile(){
		return $this->filename;
	}

	/**
	* Writes to file the $table's structure
	* @param string $table The table name
	*/
  function getTableStructure($table){
		if ( !$this->setDatabase($this->database) )
			return false;
		// Structure Header
		$structure = "-- \n";
		$structure .= "-- Table structure for table `{$table}` \n";
		$structure .= "-- \n\n";
		// Dump Structure
		$structure .= 'DROP TABLE IF EXISTS `'.$table.'`;'."\n";
		$structure .= "CREATE TABLE `".$table."` (\n";
		$records = @mysql_query('SHOW FIELDS FROM `'.$table.'`');
		if ( @mysql_num_rows($records) == 0 )
			return false;
		while ( $record = mysql_fetch_assoc($records) ) {
			$structure .= '`'.$record['Field'].'` '.$record['Type'];
			if ( !empty($record['Default']) )
				$structure .= ' DEFAULT \''.$record['Default'].'\'';
			if ( @strcmp($record['Null'],'YES') != 0 )
				$structure .= ' NOT NULL';
			if ( !empty($record['Extra']) )
				$structure .= ' '.$record['Extra'];
			$structure .= ",\n";
		}
		$structure = @ereg_replace(",\n$", null, $structure);

		// Save all Column Indexes
		$structure .= $this->getSqlKeysTable($table);
		$structure .= "\n)";

		//Save table engine
		$records = @mysql_query("SHOW TABLE STATUS LIKE '".$table."'");
		if(isset($query)){echo $query;}
		if ( $record = @mysql_fetch_assoc($records) ) {
			if ( !empty($record['Engine']) )
				$structure .= ' ENGINE='.$record['Engine'];
			if ( !empty($record['Auto_increment']) )
				$structure .= ' AUTO_INCREMENT='.$record['Auto_increment'];
		}

		$structure .= ";\n\n-- --------------------------------------------------------\n\n";
		$this->saveToFile($this->file,$structure);
	}

	/**
	* Writes to file the $table's data
	* @param string $table The table name
	* @param boolean $hexValue It defines if the output is base 16 or not
	*/
	function getTableData($table,$hexValue = true) {
		if ( !$this->setDatabase($this->database) )
			return false;
		// Header
		$data = "-- \n";
		$data .= "-- Dumping data for table `$table` \n";
		$data .= "-- \n\n";

		$records = mysql_query('SHOW FIELDS FROM `'.$table.'`');
		$num_fields = @mysql_num_rows($records);
		if ( $num_fields == 0 )
			return false;
		// Field names
		$selectStatement = "SELECT ";
		$insertStatement = "INSERT INTO `$table` (";
		$hexField = array();
		for ($x = 0; $x < $num_fields; $x++) {
			$record = @mysql_fetch_assoc($records);
			if ( ($hexValue) && ($this->isTextValue($record['Type'])) ) {
				$selectStatement .= 'HEX(`'.$record['Field'].'`)';
				$hexField [$x] = true;
			}
			else
				$selectStatement .= '`'.$record['Field'].'`';
			$insertStatement .= '`'.$record['Field'].'`';
			$insertStatement .= ", ";
			$selectStatement .= ", ";
		}
		$insertStatement = @substr($insertStatement,0,-2).') VALUES';
		$selectStatement = @substr($selectStatement,0,-2).' FROM `'.$table.'`';

		$records = @mysql_query($selectStatement);
		$num_rows = @mysql_num_rows($records);
		$num_fields = @mysql_num_fields($records);
		// Dump data
		if ( $num_rows > 0 ) {
			$data .= $insertStatement;
			for ($i = 0; $i < $num_rows; $i++) {
				$record = @mysql_fetch_assoc($records);
				$data .= ' (';
				for ($j = 0; $j < $num_fields; $j++) {
					$field_name = @mysql_field_name($records, $j);
					if ( isset($hexField) && isset($hexField[$j]) && isset($record[$field_name]) && (@strlen($record[$field_name]) > 0) )
						$data .= "0x".$record[$field_name];
					else
						$data .= "'".@str_replace('\"','"',@mysql_escape_string($record[$field_name]))."'";
					$data .= ',';
				}
				$data = @substr($data,0,-1).")";
				$data .= ( $i < ($num_rows-1) ) ? ',' : ';';
				$data .= "\n";
				//if data in greather than 1MB save
				if (strlen($data) > 1048576) {
					$this->saveToFile($this->file,$data);
					$data = '';
				}
			}
			$data .= "\n-- --------------------------------------------------------\n\n";
			$this->saveToFile($this->file,$data);
		}
	}

  /**
	* Writes to file all the selected database tables structure
	* @return boolean
	*/
	function getDatabaseStructure(){
		$records = @mysql_query('SHOW TABLES');
		if ( @mysql_num_rows($records) == 0 )
			return false;
		while ( $record = @mysql_fetch_row($records) ) {
			$this->getTableStructure($record[0]);
		}
		return true;
  }

	/**
	* Writes to file all the selected database tables data
	* @param boolean $hexValue It defines if the output is base-16 or not
	*/
	function getDatabaseData($hexValue = true){
		$records = @mysql_query('SHOW TABLES');
		if ( @mysql_num_rows($records) == 0 )
			return false;
		while ( $record = @mysql_fetch_row($records) ) {
			$this->getTableData($record[0],$hexValue);
		}
  }

	/**
	* Writes to file the selected database dump
	*/
	function doDump() {
		$this->saveToFile($this->file,"SET FOREIGN_KEY_CHECKS = 0;\n\n");
		$this->getDatabaseStructure();
		$this->getDatabaseData($this->hexValue);
		$this->saveToFile($this->file,"SET FOREIGN_KEY_CHECKS = 1;\n\n");
		$this->closeFile($this->file);
		return true;
	}
	
	/**
	* @deprecated Look at the doDump() method
	*/
	function writeDump($filename) {
		if ( !$this->setOutputFile($filename) )
			return false;
		$this->doDump();
    $this->closeFile($this->file);
    return true;
	}

	/**
	* @access private
	*/
	function getSqlKeysTable ($table) {
		$primary = "";
		unset($unique);
		unset($index);
		unset($fulltext);
		$results = mysql_query("SHOW KEYS FROM `{$table}`");
		if ( @mysql_num_rows($results) == 0 )
			return false;
		while($row = mysql_fetch_object($results)) {
			if (($row->Key_name == 'PRIMARY') AND ($row->Index_type == 'BTREE')) {
				if ( $primary == "" )
					$primary = "  PRIMARY KEY  (`{$row->Column_name}`";
				else
					$primary .= ", `{$row->Column_name}`";
			}
			if (($row->Key_name != 'PRIMARY') AND ($row->Non_unique == '0') AND ($row->Index_type == 'BTREE')) {
				if ( !isset($unique) OR !is_array($unique) OR empty($unique[$row->Key_name]) )
					$unique[$row->Key_name] = "  UNIQUE KEY `{$row->Key_name}` (`{$row->Column_name}`";
				else
					$unique[$row->Key_name] .= ", `{$row->Column_name}`";
			}
			if (($row->Key_name != 'PRIMARY') AND ($row->Non_unique == '1') AND ($row->Index_type == 'BTREE')) {
				if ( !isset($index) OR (!is_array($index)) OR ($index[$row->Key_name]=="") )
					$index[$row->Key_name] = "  KEY `{$row->Key_name}` (`{$row->Column_name}`";
				else
					$index[$row->Key_name] .= ", `{$row->Column_name}`";
			}
			if (($row->Key_name != 'PRIMARY') AND ($row->Non_unique == '1') AND ($row->Index_type == 'FULLTEXT')) {
				if ( (!is_array($fulltext)) OR ($fulltext[$row->Key_name]=="") )
					$fulltext[$row->Key_name] = "  FULLTEXT `{$row->Key_name}` (`{$row->Column_name}`";
				else
					$fulltext[$row->Key_name] .= ", `{$row->Column_name}`";
			}
		}
		$sqlKeyStatement = '';
		// generate primary, unique, key and fulltext
		if ( $primary != "" ) {
			$sqlKeyStatement .= ",\n";
			$primary .= ")";
			$sqlKeyStatement .= $primary;
		}
		if (isset($unique) && is_array($unique)) {
			foreach ($unique as $keyName => $keyDef) {
				$sqlKeyStatement .= ",\n";
				$keyDef .= ")";
				$sqlKeyStatement .= $keyDef;

			}
		}
		if (isset($index) && is_array($index)) {
			foreach ($index as $keyName => $keyDef) {
				$sqlKeyStatement .= ",\n";
				$keyDef .= ")";
				$sqlKeyStatement .= $keyDef;
			}
		}
		if (isset($fulltext) && is_array($fulltext)) {
			foreach ($fulltext as $keyName => $keyDef) {
				$sqlKeyStatement .= ",\n";
				$keyDef .= ")";
				$sqlKeyStatement .= $keyDef;
			}
		}
		return $sqlKeyStatement;
	}

  /**
	* @access private
	*/
	function isTextValue($field_type) {
		switch ($field_type) {
			case "tinytext":
			case "text":
			case "mediumtext":
			case "longtext":
			case "binary":
			case "varbinary":
			case "tinyblob":
			case "blob":
			case "mediumblob":
			case "longblob":
				return True;
				break;
			default:
				return False;
		}
	}
	
	/**
	* @access private
	*/
	function openFile($filename) {
		$file = false;
		if ( $this->compress )
			$file = @gzopen($filename, "w9");
		else
			$file = @fopen($filename, "w");
		if(SITE_ENCODING=='UTF-8'){
			$charset='utf8';
		}elseif(SITE_ENCODING=='WINDOWS-1251'){
			$charset='cp1251';
		}else{
			_die('в константе SITE_ENCODING задана неизвестная кодировка «'.SITE_ENCODING.'»');
		}
		$this->saveToFile($file,"SET NAMES $charset;\n\n");
		return $file;
	}

  /**
	* @access private
	*/
	function saveToFile($file, $data) {
		if ( $this->compress )
			@gzwrite($file, $data);
		else
			@fwrite($file, $data);
		$this->isWritten = true;
	}

  /**
	* @access private
	*/
	function closeFile($file) {
		if ( $this->compress )
			@gzclose($file);
		else
			@fclose($file);
	}
}
