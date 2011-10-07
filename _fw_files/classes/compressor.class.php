<?php

require_once(LIB_DIR.'/compressor/cssmin.php');
require_once(LIB_DIR.'/compressor/jsmin.php');

class Compressor{
	var $css_arr=array();
	var $js_arr=array();

	function addCss($file,$media='screen',$compress=true){
		if( !file_exists(SITE_DIR.$file) ){
			_print_r('файл не найден '.$file);
		}else{
			if( !array_key_exists($media, $this->css_arr) ){
				$this->css_arr[$media]=array();
			}
			$hash=_crypt($file);
			if( array_key_exists($hash,$this->css_arr[$media]) ){
				_print_r('файл добавлен повторно '.$file);
			}else{
				$this->css_arr[$media][$hash]=array('file'=>$file,'compress'=>$compress,'mtime'=>filemtime(SITE_DIR.$file));
			}
		}
	}

	function addJs($file,$compress=true){
		if( !file_exists(SITE_DIR.$file) ){
			_print_r('файл не найден '.$file);
		}else{
			$hash=_crypt($file);
			if( array_key_exists($hash,$this->js_arr) ){
				_print_r('файл добавлен повторно '.$file);
			}else{
				$this->js_arr[$hash]=array('file'=>$file,'compress'=>$compress,'mtime'=>filemtime(SITE_DIR.$file));
			}
		}
	}
	
	function getOutCss($dir){
		$result='';
		if( !empty($this->css_arr) ){
			if( DEBUG===true ){
				foreach($this->css_arr as $media=>$files){
					$this->css_arr[$media]['mtime']=0;
					foreach($files as $item){
						if( mb_strpos($item['file'],'/__lib/')===false ){
							$file_path=fileLastModified($item['file']);
						}else{
							$file_path=$item['file'];
						}
						$result.=sprintf('<link rel="stylesheet" type="text/css" href="%s" media="%s">',$file_path,$media)."\n";
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
									$css.=cssmin::minify(file_get_contents(SITE_DIR.$item['file']));
								}else{
									$css.=file_get_contents(SITE_DIR.$item['file']);
								}
							}
						}
						file_put_contents($path_to_compressed_file, $css);
					}
					$result.=sprintf('<link rel="stylesheet" type="text/css" href="%s" media="%s">',fileLastModified($dir.$compressed_fnames[$media]),$media)."\n";
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
					if( mb_strpos($item['file'],'/__lib/')===false ){
						$file_path=fileLastModified($item['file']);
					}else{
						$file_path=$item['file'];
					}
					$result.=sprintf('<script type="text/javascript" src="%s" charset="utf-8"></script>',$file_path)."\n";
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
							$js.=JSMin::minify(file_get_contents(SITE_DIR.$item['file']));
						}else{
							$js.=file_get_contents(SITE_DIR.$item['file']);
						}
					}
					file_put_contents($path_to_compressed_file, $js);
				}
				$result.=sprintf('<script type="text/javascript" src="%s" charset="utf-8"></script>',fileLastModified($dir.$compressed_fname))."\n";
			}
		}
		return $result;
	}

	function OutJs($dir='/js/'){
		echo $this->getOutJs($dir);
	}
}