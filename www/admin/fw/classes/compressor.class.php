<?php

/*
	TODO сделать загрузку cssmin.php jsmin.php только при необходимости
	если DEBUG===false то делать сборку в один файл и давать ему имя _cmprsd.23409as0df.js(css)
	иначе все файлы вызываются отдельно, 
	причем не копируются в указанную папку, 
	а вызываются из своих мест с добавлением filemtime
	(если находятся в папках, обрабатываемых RewriteRule)
	
	наверное даже лучше сделать отдельный флаг, который будет указывать, 
	нужно ли добавлять файл в сборку или его нужно всегда оставлять на своем месте
*/

class Compressor{
	public static $css_arr=array();
	public static $css_out_root_dir;
	public static $js_arr=array();
	public static $js_out_root_dir;
	
	/**
	 * метод получает путь к css-файлу от корня $file_root_path и параметры
	 * $compress=true/false нужно ли сжимать содержимое
	 * $media=screen/print/etc какой тип файла имеет
	 * предполагается, что такой файл будет объединен с другими файлами и слит в один общий файл
	 * 
	 * метод формирует массив, данные из которого будут использованы в self::outCss()
	 * 
	 * если нужно подключить css-файл, который может ссылаться на относительные картинки
	 * то следует воспользоваться методом self::addCssAlone()
	 * иначе код файла будет помещен в другую папку, и связи с картинками могут потеряться
	 */
	public static function addCss($file_root_path, $compress=true, $media='screen'){
		if( !file_exists(SITE_DIR.$file_root_path) ){
			throw new Exception(sprintf('file not found: %s',$file_root_path), 1001);
		}elseif( is_dir(SITE_DIR.$file_root_path) ){
			throw new Exception(sprintf('directory given (file expected): %s',$file_root_path), 1002);
		}else{
			// файлы будут распределены по типам media
			// создаем пустой массив для каждого медиатипа
			if( !array_key_exists($media, self::$css_arr) ){
				self::$css_arr[$media]=array();
			}
			// проверяем, нет ли такого файла в массиве
			$hash=_crypt($file_root_path);
			if( array_key_exists($hash, self::$css_arr[$media]) ){
				throw new Exception(sprintf('file duplicated: %s',$file_root_path), 1003);
			}else{
				self::$css_arr[$media][$hash]=array('file_root_path'=>$file_root_path,'compress'=>$compress);
			}
		}
	}

	/**
	 * метод получает путь к js-файлу от корня $file_root_path и параметры
	 * $compress=true/false нужно ли сжимать содержимое
	 * $charset кодировку файла
	 * предполагается, что такой файл будет объединен с другими файлами и слит в один общий файл
	 * 
	 * метод формирует массив, данные из которого будут использованы в self::outJs()
	 * 
	 * если нужно подключить js-файл, который может ссылаться на другие ресурсы в своей папке
	 * то следует воспользоваться методом self::addJsAlone()
	 * иначе код файла будет помещен в другую папку, и связи могут потеряться
	 */
	public static function addJs($file_root_path, $compress=true, $charset=''){
		if( !file_exists(SITE_DIR.$file_root_path) ){
			throw new Exception(sprintf('file not found: %s',$file_root_path), 1001);
		}elseif( is_dir(SITE_DIR.$file_root_path) ){
			throw new Exception(sprintf('directory given (file expected): %s',$file_root_path), 1002);
		}else{
			// проверяем, нет ли такого файла в массиве
			$hash=_crypt($file_root_path);
			if( array_key_exists($hash, self::$js_arr) ){
				throw new Exception(sprintf('file duplicated: %s',$file_root_path), 1003);
			}else{
				self::$js_arr[$hash]=array('file_root_path'=>$file_root_path,'compress'=>$compress,'charset'=>$charset);
			}
		}
	}
	
	/**
	 * метод получает путь к файлу от корня $file_root_path и параметр
	 * $media=screen/print/etc какой тип файла имеет
	 * в отличие от метода self::addCss(), данный файл не будет объединен с другими
	 * а будет добавлен отдельной ссылкой <link...>
	 * это нужно для того, чтобы можно было подключить файл стилей, 
	 * который может ссылаться на относительные картинки
	 */
	public static function addCssAlone($file_root_path, $media='screen'){
		if( !file_exists(SITE_DIR.$file_root_path) ){
			throw new Exception(sprintf('file not found: %s',$file_root_path), 1001);
		}elseif( is_dir(SITE_DIR.$file_root_path) ){
			throw new Exception(sprintf('directory given (file expected): %s',$file_root_path), 1002);
		}else{
			// файлы будут распределены по типам media
			// создаем пустой массив для каждого медиатипа
			if( !array_key_exists($media, self::$css_arr) ){
				self::$css_arr[$media]=array();
			}
			// проверяем, нет ли такого файла в массиве
			$hash=_crypt($file_root_path);
			if( array_key_exists($hash, self::$css_arr[$media]) ){
				throw new Exception(sprintf('file duplicated: %s',$file_root_path), 1003);
			}else{
				self::$css_arr[$media][$hash]=array('file_root_path'=>$file_root_path,'alone'=>true);
			}
		}
	}
	
	/**
	 * метод получает путь к файлу от корня $file_root_path и параметр
	 * в отличие от метода self::addJs(), данный файл не будет объединен с другими
	 * а будет добавлен отдельной ссылкой <script...>
	 * это нужно для того, чтобы можно было подключить js-файл, 
	 * который является часть библиотеки и может ссылаться на другие ресурсы
	 */
	public static function addJsAlone($file_root_path,$charset=''){
		if( !file_exists(SITE_DIR.$file_root_path) ){
			throw new Exception(sprintf('file not found: %s',$file_root_path), 1001);
		}elseif( is_dir(SITE_DIR.$file_root_path) ){
			throw new Exception(sprintf('directory given (file expected): %s',$file_root_path), 1002);
		}else{
			// проверяем, нет ли такого файла в массиве
			$hash=_crypt($file_root_path);
			if( array_key_exists($hash, self::$js_arr) ){
				throw new Exception(sprintf('file duplicated: %s',$file_root_path), 1003);
			}else{
				self::$js_arr[$hash]=array('file_root_path'=>$file_root_path,'alone'=>true,'charset'=>$charset);
			}
		}
	}
	
	/**
	 * метод возвращает массив, каждый элемент которого содержит данные по файлу:
	 * тип медиа и ссылку на файл от корня
	 * файлы расположены по порядку вызова, все что остается — сформировать ссылки
	 * на файлы с помощью self::getCssLink
	 * 
	 * в зависимости от ситуации метод или создает сжатый файл, или обновляет, 
	 * или оставляет без изменений
	 * (в режиме отладки всегда возвращаются отдельные ссылки на каждый из файлов)
	 */
	public static function getOutCss($debug){
		$out=array();
		foreach(self::$css_arr as $media=>$files){
			// сначала выводим сжатый файл
			if( $debug!==true ){
				// определяем имя сжатого файла (оно должно основываться на хэше 
				// сериализованного массива $files, за исключением файлов alone) 
				// и путь к нему от корня
				$compressed_file_name=self::getCompressedFileName($files, 'css');
				if( !empty($compressed_file_name) ){
					$compressed_file_root_path=sprintf('%s/%s', self::$css_out_root_dir, $compressed_file_name);
					// проверяем, имеется ли файл с таким названием и является ли он актуальным
					$compressed_file_missing_or_outdate=self::fileMissingOrOutdate($compressed_file_root_path, $files);
					if( $compressed_file_missing_or_outdate ){
						// создаем файл заново (или переписываем существующий)
						$f=@fopen(SITE_DIR.$compressed_file_root_path,"w+");
						// получаем содержимое файла
						$compressed_contents=self::getCompressedContents($files, 'css', SITE_ENCODING);
						fputs($f,$compressed_contents);
						fclose($f);
					}
					$out[]=array('media'=>$media, 'file_root_path'=>$compressed_file_root_path);
				}
			}
			// затем выводим остальные файлы
			foreach($files as $file){
				if( $debug===true || array_key_exists('alone', $file) ){
					$out[]=array('media'=>$media, 'file_root_path'=>$file['file_root_path']);
				}
			}
		}
		
		return $out;
	}
	
	/**
	 * метод возвращает массив, каждый элемент которого содержит данные по файлу:
	 * ссылку на файл от корня и кодировку
	 * файлы расположены по порядку вызова, все что остается — сформировать ссылки
	 * на файлы с помощью self::getJsLink
	 * 
	 * в зависимости от ситуации метод или создает сжатый файл, или обновляет, 
	 * или оставляет его без изменений
	 * (в режиме отладки всегда возвращаются отдельные ссылки на каждый из файлов)
	 */
	public static function getOutJs($debug, $site_encoding){
		$out=array();
		// сначала выводим сжатый файл
		if( $debug!==true ){
			// определяем имя сжатого файла (оно должно основываться на хэше 
			// сериализованного массива self::$js_arr, за исключением файлов alone) 
			// и путь к нему от корня
			$compressed_file_name=self::getCompressedFileName(self::$js_arr, 'js');
			if( !empty($compressed_file_name) ){
				$compressed_file_root_path=sprintf('%s/%s', self::$js_out_root_dir, $compressed_file_name);
				// проверяем, имеется ли файл с таким названием и является ли он актуальным
				$compressed_file_missing_or_outdate=self::fileMissingOrOutdate($compressed_file_root_path, self::$js_arr);
				if( $compressed_file_missing_or_outdate ){
					// создаем файл заново (или переписываем существующий)
					$f=@fopen(SITE_DIR.$compressed_file_root_path,"w+");
					// получаем содержимое файла
					$compressed_contents=self::getCompressedContents(self::$js_arr, 'js', $site_encoding);
					fputs($f,$compressed_contents);
					fclose($f);
				}
				$out[]=array('file_root_path'=>$compressed_file_root_path, 'charset'=>$site_encoding);
			}
		}
		// затем выводим остальные файлы
		foreach(self::$js_arr as $file){
			if( $debug===true || array_key_exists('alone', $file) ){
				$charset=defvar($site_encoding,$file['charset']);
				$out[]=array('file_root_path'=>$file['file_root_path'], 'charset'=>$charset);
			}
		}
		
		return $out;
	}
	
	/**
	 * метод получает массив файлов и их тип, убирает из него файлы с флагом alone
	 * и возвращает имя сжатого файла, основанное на хэше сериализованного массива
	 */
	public static function getCompressedFileName($files, $type){
		if( $type!='css' && $type!='js' ){
			throw new Exception('Wrong type received (expected js or css):'.$type, 1007);
		}else{
			$file_name='';
			$pure_files=array();
			foreach($files as $key=>$file){
				if( array_key_exists('compress', $file) ){
					$pure_files[]=$file;
				}
			}
			if( !empty($pure_files) ){
				$hash=_crypt( serialize($pure_files) );
				$file_name=sprintf('_cmprsd.%s.%s', mb_substr($hash,0,8), $type);
			}
		}
		
		return $file_name;
	}
	
	/**
	 * метод получает путь от корня к общему файлу и список файлов для объединения/сжатия
	 * задача метода определить наличие файла и его актуальность
	 * (файл будет актуальным, если его filemtime больше чем наибольший filemtime среди файлов из списка)
	 */
	public static function fileMissingOrOutdate($compressed_file_root_path, $files){
		$bool=true;
		if( file_exists(SITE_DIR.$compressed_file_root_path) ){
			$max_filemtime=0;
			foreach($files as $file){
				$max_filemtime=max($max_filemtime, filemtime(SITE_DIR.$file['file_root_path']));
			}
			$compressed_filemtime=filemtime(SITE_DIR.$compressed_file_root_path);
			if( $compressed_filemtime > $max_filemtime ){
				$bool=false;
			}
		}
		return $bool;
	}
	
	/**
	 * метод получает массив файлов
	 * читает их содержимое
	 * и помещает в один общий листинг, сжимая код при необходимости
	 * для js-файлов необходимо производить перекодировку в SITE_ENCODING
	 * если их кодировка не совпадает с кодировкой сайта
	 */
	public static function getCompressedContents($files, $type, $site_encoding){
		$contents='';
		foreach($files as $file){
			if( array_key_exists('compress', $file) ){
				if( $file['compress'] ){
					if( $type=='css' ){
						require_once(LIB_DIR.'/compressor/cssmin.php');
						$contents.=cssmin::minify( file_get_contents(SITE_DIR.$file['file_root_path']) );
					}elseif( $type=='js' ){
						require_once(LIB_DIR.'/compressor/jsmin.php');
						$js_file_contents=file_get_contents(SITE_DIR.$file['file_root_path']);
						if( !empty($file['charset']) && mb_strtolower($file['charset'])!=mb_strtolower($site_encoding) ){
							$js_file_contents=iconv($site_encoding, $file['charset'], $js_file_contents);
						}
						$contents.=JSMin::minify( $js_file_contents );
					}else{
						throw new Exception('Wrong type received (expected js or css):'.$type, 1007);
					}
				}else{
					$contents.=file_get_contents(SITE_DIR.$file['file_root_path']);
				}
			}
		}
		return $contents;
	}
	
	/**
	 * метод получает данные о файле (путь к файлу с filemtime и тип медиа)
	 * а также настройки системы (кодировку и версию HTML)
	 * и возвращает корректную ссылку <link ...> на подключение файла
	 */
	public static function getCssLink($path_with_filemtime, $media, $site_encoding, $html_version){
		$media_attr='';
		if( !empty($media) && $media!='screen' ){
			$media_attr=sprintf(' media="%s"',$media);
		}
		if( $html_version==5 ){
			$result=sprintf('<link rel="stylesheet" href="%s"%s>', $path_with_filemtime, $media_attr);
		}else{
			$result=sprintf('<link rel="stylesheet" href="%s" type="text/css"%s charset="%s">', $path_with_filemtime, $media_attr, $site_encoding);
		}

		return $result;
	}
	
	/**
	 * метод получает путь к файлу с filemtime и его кодировку
	 * а также настройки системы (кодировку и версию HTML)
	 * и возвращает корректную ссылку <script ...> на подключение файла
	 */
	public static function getJsLink($path_with_filemtime, $charset, $html_version){
		if( $html_version==5 ){
			$result=sprintf('<script src="%s" charset="%s"></script>', $path_with_filemtime, $charset);
		}else{
			$result=sprintf('<script type="text/javascript" src="%s" charset="%s"></script>', $path_with_filemtime, $charset);
		}

		return $result;
	}
	
	/**
	 * метод получает путь к директории для размещения сжатого файла
	 * проверяет этот путь, помещает корректный путь в self::$css_out_root_dir
	 * запускает self::getOutCss() и получает от него массив файлов для вызова
	 * и наконец формирует для каждого ссылку вызова и выводит через echo
	 */
	public static function outCss($root_dir='/media/css'){
		self::$css_out_root_dir=self::getRootDir( $root_dir );

		$out=self::getOutCss(DEBUG);
		self::$css_arr=array();

		$result=array();
		foreach($out as $file){
			$path_with_filemtime=self::getRootFilePathWithMtime($file['file_root_path']);
			$result[]=self::getCssLink($path_with_filemtime, $file['media'], SITE_ENCODING, HTML_VERSION);
		}
		return implode("\r\n",$result);
	}
	
	/**
	 * метод получает путь к директории для размещения сжатого файла
	 * проверяет этот путь, помещает корректный путь в self::$js_out_root_dir
	 * запускает self::getOutJs() и получает от него массив файлов для вызова
	 * и наконец формирует для каждого ссылку вызова и выводит через echo
	 */
	public static function outJs($root_dir='/media/js'){
		self::$js_out_root_dir=self::getRootDir( $root_dir );

		$out=self::getOutJs(DEBUG, SITE_ENCODING);
		self::$js_arr=array();

		$result=array();
		foreach($out as $file){
			$path_with_filemtime=self::getRootFilePathWithMtime($file['file_root_path']);
			$result[]=self::getJsLink($path_with_filemtime, $file['charset'], HTML_VERSION);
		}
		return implode("\r\n",$result);
	}

	/**
	 * метод проверяет полученный путь на существование
	 * и удаляет конечный слэш 
	 */
	public static function getRootDir($root_dir){
		if( !file_exists(SITE_DIR.$root_dir) ){
			throw new Exception('Directory not found: '.$dir, 1004);
		}elseif( mb_substr($root_dir,0,1)!='/' ){
			throw new Exception('Root directory must start with “/”: '.$dir, 1005);
		}elseif( mb_strpos($root_dir,'//')!==false ){
			throw new Exception('Root directory must not have “//”: '.$dir, 1006);
		}else{
			if( mb_substr($root_dir,-1)=='/' ){
				$root_dir=mb_substr($root_dir,0,-1);
			}
		}
		
		return $root_dir;
	}
	
	/**
	 * метод получает путь к файлу от корня
	 * и возвращает тот же путь с добавлением даты последнего изменения файла
	 * (но лишь в случае, если файл находится в одной из папок, для которой
	 * действует соответствующиее правило RewriteRule)
	 */
	public static function getRootFilePathWithMtime($file_root_path){
		if( !file_exists(SITE_DIR.$file_root_path) ){
			throw new Exception(sprintf('file not found: %s',$file_root_path), 1001);
		}elseif( is_dir(SITE_DIR.$file_root_path) ){
			throw new Exception(sprintf('directory given (file expected): %s',$file_root_path), 1002);
		}else{
			$file_full_path=SITE_DIR.$file_root_path;
			$file_dir=dirname($file_full_path);
			$root_dir=mb_substr($file_dir,mb_strlen(SITE_DIR));
			$file_name=basename($file_full_path);
			$root_dir_arr=explode('/', $root_dir);
			// RewriteRule ^(media|js|css|img|u|xml|swf)/(.*)\.[0-9]{10}\.(js|css|swf|xml)$ 	/$1/$2.$3 	[L]
			if( in_array($root_dir_arr[1], explode('|','media|js|css|img|u|xml|swf')) || mb_strpos($root_dir, '/admin/fw/media/')===0 ){
				$file_name_arr=explode('.',$file_name);
				$ext=last($file_name_arr);
				$prefix=implode('.',array_slice($file_name_arr,0,-1));
				$result=sprintf('%s/%s.%s.%s', $root_dir, $prefix, filemtime($file_full_path), $ext);
			}else{
				$result=$file_root_path;
			}
		}
		return $result;
	}
}