<?php

require_once(LIB_DIR.'/compressor/cssmin.php');
require_once(LIB_DIR.'/compressor/jsmin.php');

class Compressor{
	var $css_arr=array();
	var $js_arr=array();

	function addCss($file,$media='screen',$compress=true){
		if( file_exists(SITE_DIR.$file) ){
			$fullpath=realpath(SITE_DIR.$file);
		}elseif( file_exists($file) ){
			$fullpath=realpath($file);
		}else{
			_print_r('файл не найден ',$file);
		}
		if( !empty($fullpath) ){
			// файлы будут распределены по типам media
			// создаем пустой массив для каждого медиатипа
			if( !array_key_exists($media, $this->css_arr) ){
				$this->css_arr[$media]=array();
			}
			$hash=_crypt($fullpath);
			if( array_key_exists($hash,$this->css_arr[$media]) ){
				_print_r('файл добавлен повторно ',$fullpath);
			}else{
				$this->css_arr[$media][$hash]=array('file'=>$file,'fullpath'=>$fullpath,'compress'=>$compress,'mtime'=>filemtime($fullpath));
			}
		}
	}

	function addJs($file,$compress=true){
		if( file_exists(SITE_DIR.$file) ){
			$fullpath=realpath(SITE_DIR.$file);
		}elseif( file_exists($file) ){
			$fullpath=realpath($file);
		}else{
			_print_r('файл не найден ',$file);
		}
		if( !empty($fullpath) ){
			$hash=_crypt($fullpath);
			if( array_key_exists($hash,$this->js_arr) ){
				_print_r('файл добавлен повторно ',$fullpath);
			}else{
				$this->js_arr[$hash]=array('file'=>$file,'fullpath'=>$fullpath,'compress'=>$compress,'mtime'=>filemtime($fullpath));
			}
		}
	}
	
	function getOutCss($dir){
		$result='';
		if( !empty($this->css_arr) ){
			if( DEBUG===true ){
				foreach($this->css_arr as $media=>$files){
					foreach($files as $item){
						// определяем, находится ли файл внутри DOCUMENT_ROOT
						// если да, то помещаем ссылку на файл, добавляя время создания файла
						// иначе копируем файл, добавляя к имени файла нижнее подчеркивание 
						// и хэш из полного пути
						if( mb_strpos($item['fullpath'], SITE_DIR)===0 ){
							// добавляем к имени файла дату его создания
							// если он находится в папке /media/ или /admin/media/
							$root_relative_path=$this->getRootFilePathWithMtime($item['fullpath']);
						}else{
							$root_relative_path=sprintf('%s_%s.%s.css', $dir, basename($item['fullpath'],'.css'), mb_substr(_crypt($item['fullpath'].filemtime($item['fullpath'])),0,8) );
							if( !file_exists(SITE_DIR.$root_relative_path) ){
								file_put_contents(SITE_DIR.$root_relative_path, file_get_contents($item['fullpath']));
							}
						}
						$result.=sprintf('<link rel="stylesheet" type="text/css" href="%s" media="%s">',$root_relative_path,$media)."\n";
					}
				}
			}else{
				// поскольку на каждой странице может быть свой набор файлов
				// со своими параметрами компрессии
				// необходимо привязать название конечного файла к этому набору
				// поэтому хэш набора на основе его сериализации
				$compressed_fnames=array();
				foreach($this->css_arr as $media=>$files){
					sort($files);
					$files=serialize($files);
					$compressed_fnames[$media]=sprintf('_%s.%s.css',$media,mb_substr(_crypt($files),0,16));
				}
				// определяем последнюю дату изменения в каждом из медиатипов
				foreach($this->css_arr as $media=>$files){
					$this->css_arr[$media]['mtime']=0;
					foreach($files as $item){
						if( $item['mtime'] > $this->css_arr[$media]['mtime'] ){
							$this->css_arr[$media]['mtime']=$item['mtime'];
						}
					}
				}
				// определяем не устарел ли существующий файл
				foreach($this->css_arr as $media=>$files){
					$path_to_compressed_file=SITE_DIR.$dir.$compressed_fnames[$media];
					if( !file_exists($path_to_compressed_file) || filemtime($path_to_compressed_file) < $this->css_arr[$media]['mtime'] ){
						$css='';
						foreach($files as $key=>$item){
							if( is_array($item) ){
								if( $item['compress'] ){
									$css.=cssmin::minify(file_get_contents($item['fullpath']));
								}else{
									$css.=file_get_contents($item['fullpath']);
								}
							}
						}
						file_put_contents($path_to_compressed_file, $css);
					}
					$result.=sprintf('<link rel="stylesheet" type="text/css" href="%s%s" media="%s">', $dir, $compressed_fnames[$media], $media)."\n";
				}
			}
		}
		return $result;
	}

	function OutCss($dir='/css/'){
		echo $this->getOutCss($dir);
	}

	function getOutJs($dir){
		$result='';
		if( !empty($this->js_arr) ){
			if( DEBUG===true ){
				foreach($this->js_arr as $item){
					// определяем, находится ли файл внутри DOCUMENT_ROOT
					// если да, то помещаем ссылку на файл, добавляя время создания файла
					// иначе копируем файл, добавляя к имени файла нижнее подчеркивание 
					// и хэш из полного пути
					if( mb_strpos($item['fullpath'], SITE_DIR)===0 ){
						// добавляем к имени файла дату его создания
						// если он находится в папке /media/ или /admin/media/
						$root_relative_path=$this->getRootFilePathWithMtime($item['fullpath']);
					}else{
						$root_relative_path=sprintf('%s_%s.%s.js', $dir, basename($item['fullpath'],'.js'), mb_substr(_crypt($item['fullpath'],filemtime($item['fullpath'])),0,8) );
						if( !file_exists(SITE_DIR.$root_relative_path) ){
							file_put_contents(SITE_DIR.$root_relative_path, file_get_contents($item['fullpath']));
						}
					}
					$result.=sprintf('<script type="text/javascript" src="%s" charset="utf-8"></script>',$root_relative_path)."\n";
				}
			}else{
				// поскольку на каждой странице может быть свой набор файлов
				// со своими параметрами компрессии
				// необходимо привязать название конечного файла к этому набору
				// поэтому хэш набора на основе его сериализации
				$js_arr_copy=$this->js_arr;
				sort($js_arr_copy);
				$js_arr_copy=serialize($js_arr_copy);
				$compressed_fname=sprintf('_%s.js',mb_substr(_crypt($js_arr_copy),0,20));
				unset($js_arr_copy);
				// определяем последнюю дату изменения среди всех файлов
				$mtime=0;
				foreach($this->js_arr as $item){
					if( $item['mtime'] > $mtime ){
						$mtime=$item['mtime'];
					}
				}
				// определяем не устарел ли существующий файл
				$path_to_compressed_file=SITE_DIR.$dir.$compressed_fname;
				if( !file_exists($path_to_compressed_file) || filemtime($path_to_compressed_file) < $mtime ){
					$js='';
					foreach($this->js_arr as $item){
						if( $item['compress'] ){
							$js.=JSMin::minify( file_get_contents($item['fullpath']) );
						}else{
							$js.=file_get_contents($item['fullpath']);
						}
					}
					file_put_contents($path_to_compressed_file, $js);
				}
				$result.=sprintf('<script type="text/javascript" src="%s%s" charset="utf-8"></script>', $dir, $compressed_fname)."\n";
			}
		}
		return $result;
	}

	function OutJs($dir='/js/'){
		echo $this->getOutJs($dir);
	}
	
	function getRootFilePathWithMtime($fullpath){
		// _print_r('call filemtime ',$fullpath);
		// добавляем дату последнего изменения только если файл находится 
		// в папке /media/ или /admin/media/
		// потому что для этих папок действует нужный RewriteRule
		$file_dir=dirname($fullpath);
		$root_dir=mb_substr($file_dir,mb_strlen(SITE_DIR));
		$file_name=basename($fullpath);
		if( mb_strpos($fullpath, SITE_DIR.'/media/')===0 || mb_strpos($fullpath, SITE_DIR.'/admin/media/')===0 ){
			$file_name_arr=explode('.',$file_name);
			$ext=last($file_name_arr);
			$prefix=implode('.',array_slice($file_name_arr,0,-1));
			$result=sprintf('%s%s.%s.%s', $dir, $prefix, filemtime($fullpath), $ext);
		}else{
			$result=$root_dir.$file_name;
		}
		return $result;
	}
}