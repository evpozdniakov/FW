<?php

if( empty($_SERVER['DOCUMENT_ROOT']) ){
	$_SERVER['DOCUMENT_ROOT']=realpath(__dir__.'/../../..');
	$_SERVER['SERVER_NAME']='msk.mysite.com';
	$_SERVER['HTTP_HOST']='msk.mysite.com';
	define('DONT_INCLUDE_CALLEE', true);
	include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/init.php');
}

require_once($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/compressor.class.php');

class CompressorTest extends PHPUnit_Framework_TestCase {
	protected function setUp(){
		if( !is_dir(SITE_DIR.'/media/css') || !is_writable(SITE_DIR.'/media/css') ){
			throw new Exception('Folder /media/css/ not exist or not writable');
		}else{
			for ($i=1; $i <= 5; $i++) {
				$file_full_path=sprintf('%s/media/css/test%d.css', SITE_DIR, $i);
				$f=@fopen($file_full_path,"w+");
				fputs($f,sprintf('/*test%d.css*/', $i));
				fclose($f);
				$filemtime = time() - $i*1000;
				// echo $filemtime." test\r\n";
				touch($file_full_path, $filemtime);
			}
			for ($i=1; $i <= 4; $i++) {
				$file_full_path=sprintf('%s/media/css/_cmprsd.abc%d.css', SITE_DIR, $i);
				$f=@fopen($file_full_path,"w+");
				fputs($f,sprintf('/*_cmprsd.abc%d.css*/', $i));
				fclose($f);
				$filemtime = time() - $i*400;
				// echo $filemtime." compressed\r\n";
				touch($file_full_path, $filemtime);
			}
		}

		if( !is_dir(SITE_DIR.'/media/js') || !is_writable(SITE_DIR.'/media/js') ){
			throw new Exception('Folder /media/js/ not exist or not writable');
		}else{
			for ($i=1; $i <= 5; $i++) {
				$file_full_path=sprintf('%s/media/js/test%d.js', SITE_DIR, $i);
				$f=@fopen($file_full_path,"w+");
				fputs($f,sprintf('/*test%d.js*/', $i));
				fclose($f);
				$filemtime = time() - $i*1000;
				// echo $filemtime." test\r\n";
				touch($file_full_path, $filemtime);
			}
			for ($i=1; $i <= 4; $i++) {
				$file_full_path=sprintf('%s/media/js/_cmprsd.abc%d.js', SITE_DIR, $i);
				$f=@fopen($file_full_path,"w+");
				fputs($f,sprintf('/*_cmprsd.abc%d.js*/', $i));
				fclose($f);
				$filemtime = time() - $i*400;
				// echo $filemtime." compressed\r\n";
				touch($file_full_path, $filemtime);
			}
		}
	}
	
	protected function tearDown(){
		Compressor::$css_arr=array();
		for ($i=1; $i <= 5; $i++) { 
			if( file_exists(SITE_DIR.'/media/css/test'.$i.'.css') ){
				unlink(SITE_DIR.'/media/css/test'.$i.'.css');
			}
		}
		for ($i=1; $i <= 4; $i++) { 
			if( file_exists(SITE_DIR.'/media/css/_cmprsd.abc'.$i.'.css') ){
				unlink(SITE_DIR.'/media/css/_cmprsd.abc'.$i.'.css');
			}
		}

		Compressor::$js_arr=array();
		for ($i=1; $i <= 5; $i++) { 
			if( file_exists(SITE_DIR.'/media/js/test'.$i.'.js') ){
				unlink(SITE_DIR.'/media/js/test'.$i.'.js');
			}
		}
		for ($i=1; $i <= 4; $i++) { 
			if( file_exists(SITE_DIR.'/media/js/_cmprsd.abc'.$i.'.js') ){
				unlink(SITE_DIR.'/media/js/_cmprsd.abc'.$i.'.js');
			}
		}
	}

	/**
	 * @test
	 */
	public function addCss(){
		Compressor::addCss('/media/css/test1.css');
		Compressor::addCss('/media/css/test2.css', true);
		Compressor::addCss('/media/css/test3.css', false);
		Compressor::addCss('/media/css/test4.css', true, 'print');
		Compressor::addCss('/media/css/test5.css', false, 'print');
		$this->assertEquals(2, count(Compressor::$css_arr), 'correct media types count in Compressor::$css_arr');
		$this->assertEquals(3, count(Compressor::$css_arr['screen']), 'correct files count in Compressor::$css_arr[screen]');
		$this->assertEquals(2, count(Compressor::$css_arr['print']), 'correct files count in Compressor::$css_arr[print]');
		$first_element=current(Compressor::$css_arr['screen']);
		$this->assertEquals(true, $first_element['compress'], 'First element compress==true');
		$this->assertEquals(null, $first_element['alone'], 'First element alone is null');
		$this->assertEquals('/media/css/test1.css', $first_element['file_root_path'], 'First element path is correct');
		next(Compressor::$css_arr['screen']);
		$second_element=current(Compressor::$css_arr['screen']);
		$this->assertEquals(true, $second_element['compress'], 'Second element compress==true');
		$this->assertEquals(null, $second_element['alone'], 'Second element alone is null');
		$this->assertEquals('/media/css/test2.css', $second_element['file_root_path'], 'Second element path is correct');
	}

	/**
	 * @test
	 */
	public function addJs(){
		Compressor::addJs('/media/js/test1.js');
		Compressor::addJs('/media/js/test2.js', false);
		Compressor::addJs('/media/js/test3.js', true);
		$this->assertEquals(3, count(Compressor::$js_arr), 'correct files count in Compressor::$js_arr');
		$first_element=current(Compressor::$js_arr);
		$this->assertEquals(true, $first_element['compress'], 'First element compress==true');
		$this->assertEquals(null, $first_element['alone'], 'First element alone is null');
		$this->assertEquals('/media/js/test1.js', $first_element['file_root_path'], 'First element path is correct');
		next(Compressor::$js_arr);
		$second_element=current(Compressor::$js_arr);
		$this->assertEquals(false, $second_element['compress'], 'Second element compress==false');
		$this->assertEquals(null, $second_element['alone'], 'Second element alone is null');
		$this->assertEquals('/media/js/test2.js', $second_element['file_root_path'], 'Second element path is correct');
	}
	
	/**
	 * @test
	 */
	public function addCssAlone(){
		Compressor::addCssAlone('/media/css/test1.css');
		Compressor::addCssAlone('/media/css/test2.css', 'print');
		Compressor::addCssAlone('/media/css/test3.css', 'print');
		$this->assertEquals(2, count(Compressor::$css_arr), 'correct media types count in Compressor::$css_arr');
		$this->assertEquals(1, count(Compressor::$css_arr['screen']), 'correct files count in Compressor::$css_arr[screen]');
		$this->assertEquals(2, count(Compressor::$css_arr['print']), 'correct files count in Compressor::$css_arr[print]');
		$first_element=current(Compressor::$css_arr['screen']);
		$this->assertEquals(true, $first_element['alone'], 'First element alone==true');
		$this->assertEquals(null, $first_element['compress'], 'First element compress is null');
		$this->assertEquals('/media/css/test1.css', $first_element['file_root_path'], 'First element path is correct');
		$second_element=current(Compressor::$css_arr['print']);
		$this->assertEquals(true, $second_element['alone'], 'Second element alone==true');
		$this->assertEquals(null, $second_element['compress'], 'Second element compress is null');
		$this->assertEquals('/media/css/test2.css', $second_element['file_root_path'], 'Second element path is correct');
	}
	
	/**
	 * @test
	 */
	public function addJsAlone(){
		Compressor::addJsAlone('/media/js/test1.js');
		Compressor::addJsAlone('/media/js/test2.js', 'ISO-8859-1');
		Compressor::addJsAlone('/media/js/test3.js');
		$this->assertEquals(3, count(Compressor::$js_arr), 'correct files count in Compressor::$js_arr');
		$first_element=current(Compressor::$js_arr);
		$this->assertEquals(true, $first_element['alone'], 'First element alone==true');
		$this->assertEquals(null, $first_element['compress'], 'First element compress is null');
		$this->assertEquals('/media/js/test1.js', $first_element['file_root_path'], 'First element path is correct');
		$this->assertEquals('', $first_element['charset'], 'First element has correct charset');
		next(Compressor::$js_arr);
		$second_element=current(Compressor::$js_arr);
		$this->assertEquals(true, $second_element['alone'], 'Second element alone==true');
		$this->assertEquals(null, $second_element['compress'], 'Second element compress is null');
		$this->assertEquals('/media/js/test2.js', $second_element['file_root_path'], 'Second element path is correct');
		$this->assertEquals('ISO-8859-1', $second_element['charset'], 'Second element has correct charset');
	}

	/**
	 * @test
	 * @dataProvider getOutCssProvider
	 */
	public function getOutCss($files, $css_out_root_dir, $debug, $result){
		Compressor::$css_arr=$files;
		Compressor::$css_out_root_dir=$css_out_root_dir;
		$out=Compressor::getOutCss($debug);
		$this->assertEquals($result, $out, 'out css files array is correct');
		foreach($out as $item){
			if( mb_strpos($item['file_root_path'],'_cmprsd.')>0){
				if( file_exists(SITE_DIR.$item['file_root_path']) ){
					unlink(SITE_DIR.$item['file_root_path']);
				}
			}
		}
	}
	
	public function getOutCssProvider(){
		$files1=array('screen'=>array(array('file_root_path'=>'/media/css/test1.css', 'alone'=>true)));
		$out1=array(array('file_root_path'=>'/media/css/test1.css', 'media'=>'screen'));
		$files2=array(
			'screen'=>array(array('file_root_path'=>'/media/css/test1.css', 'alone'=>true)),
			'print'=>array(array('file_root_path'=>'/media/css/test2.css', 'alone'=>true)),
		);
		$out2=array(
			array('file_root_path'=>'/media/css/test1.css', 'media'=>'screen'),
			array('file_root_path'=>'/media/css/test2.css', 'media'=>'print'),
		);
		$files3=array(
			'screen'=>array(array('file_root_path'=>'/media/css/test1.css', 'alone'=>true), array('file_root_path'=>'/media/css/test3.css', 'compress'=>false)),
			'print'=>array(array('file_root_path'=>'/media/css/test2.css', 'alone'=>true)),
		);
		$css_out_root_dir3='/media/css';
		$out3=array(
			array('file_root_path'=>'/media/css/test1.css', 'media'=>'screen'),
			array('file_root_path'=>'/media/css/test3.css', 'media'=>'screen'),
			array('file_root_path'=>'/media/css/test2.css', 'media'=>'print'),
		);
		$out3c=array(
			array('file_root_path'=>$css_out_root_dir3.'/'.Compressor::getCompressedFileName($files3['screen'], 'css'), 'media'=>'screen'),
			array('file_root_path'=>'/media/css/test1.css', 'media'=>'screen'),
			array('file_root_path'=>'/media/css/test2.css', 'media'=>'print'),
		);
		$files4=array(
			'screen'=>array(array('file_root_path'=>'/media/css/test1.css', 'alone'=>true), array('file_root_path'=>'/media/css/test2.css', 'compress'=>false), array('file_root_path'=>'/media/css/test3.css', 'compress'=>true), array('file_root_path'=>'/media/css/test4.css', 'compress'=>true)),
			'print'=>array(array('file_root_path'=>'/media/css/test1.css', 'alone'=>true), array('file_root_path'=>'/media/css/test2.css', 'compress'=>true), array('file_root_path'=>'/media/css/test3.css', 'comperss'=>false)),
		);
		$css_out_root_dir4='/media';
		$out4=array(
			array('file_root_path'=>'/media/css/test1.css', 'media'=>'screen'),
			array('file_root_path'=>'/media/css/test2.css', 'media'=>'screen'),
			array('file_root_path'=>'/media/css/test3.css', 'media'=>'screen'),
			array('file_root_path'=>'/media/css/test4.css', 'media'=>'screen'),
			array('file_root_path'=>'/media/css/test1.css', 'media'=>'print'),
			array('file_root_path'=>'/media/css/test2.css', 'media'=>'print'),
			array('file_root_path'=>'/media/css/test3.css', 'media'=>'print'),
		);
		$out4c=array(
			array('file_root_path'=>$css_out_root_dir4.'/'.Compressor::getCompressedFileName($files4['screen'], 'css'), 'media'=>'screen'),
			array('file_root_path'=>'/media/css/test1.css', 'media'=>'screen'),
			array('file_root_path'=>$css_out_root_dir4.'/'.Compressor::getCompressedFileName($files4['print'], 'css'), 'media'=>'print'),
			array('file_root_path'=>'/media/css/test1.css', 'media'=>'print'),
		);
		return array(
			array($files1, '/media/css/', true, $out1),
			array($files1, '/media/css/', false, $out1),
			array($files1, '/media/css', true, $out1),
			array($files1, '/media/css', false, $out1),
			array($files1, '', true, $out1),
			array($files1, '', false, $out1),
			array($files2, '/media/css/', true, $out2),
			array($files2, '/media/css/', false, $out2),
			array($files2, '/media/css', true, $out2),
			array($files2, '/media/css', false, $out2),
			array($files2, '', true, $out2),
			array($files2, '', false, $out2),
			array($files3, $css_out_root_dir3, true, $out3),
			array($files3, $css_out_root_dir3, false, $out3c),
			array($files4, $css_out_root_dir4, true, $out4),
			array($files4, $css_out_root_dir4, false, $out4c),
		);
	}

	/**
	 * @test
	 * @dataProvider getOutJsProvider
	 */
	public function getOutJs($files, $js_out_root_dir, $debug, $result){
		Compressor::$js_arr=$files;
		Compressor::$js_out_root_dir=$js_out_root_dir;
		$out=Compressor::getOutJs($debug, SITE_ENCODING);
		$this->assertEquals($result, $out, 'out js files array is correct');
		foreach($out as $item){
			if( mb_strpos($item['file_root_path'],'_cmprsd.')>0){
				if( file_exists(SITE_DIR.$item['file_root_path']) ){
					unlink(SITE_DIR.$item['file_root_path']);
				}
			}
		}
	}
	
	public function getOutJsProvider(){
		$files1=array(
			array('file_root_path'=>'/media/js/test1.js', 'alone'=>true),
			array('file_root_path'=>'/media/js/test2.js', 'alone'=>true, 'charset'=>'utf-8'),
			array('file_root_path'=>'/media/js/test3.js', 'alone'=>true, 'charset'=>'UTF-8'),
			array('file_root_path'=>'/media/js/test4.js', 'alone'=>true, 'charset'=>'ISO-8859-1'),
		);
		$out1=array(
			array('file_root_path'=>'/media/js/test1.js', 'charset'=>SITE_ENCODING),
			array('file_root_path'=>'/media/js/test2.js', 'charset'=>'utf-8'),
			array('file_root_path'=>'/media/js/test3.js', 'charset'=>'UTF-8'),
			array('file_root_path'=>'/media/js/test4.js', 'charset'=>'ISO-8859-1'),
		);
		$files2=array(
			array('file_root_path'=>'/media/js/test1.js', 'alone'=>true),
			array('file_root_path'=>'/media/js/test2.js', 'alone'=>true, 'charset'=>'ISO-8859-1'),
			array('file_root_path'=>'/media/js/test3.js', 'compress'=>true),
			array('file_root_path'=>'/media/js/test4.js', 'compress'=>true, 'charset'=>'utf-8'),
			array('file_root_path'=>'/media/js/test5.js', 'compress'=>true, 'charset'=>'UTF-8'),
		);
		$js_out_root_dir2='/media/js';
		$out2=array(
			array('file_root_path'=>'/media/js/test1.js', 'charset'=>SITE_ENCODING),
			array('file_root_path'=>'/media/js/test2.js', 'charset'=>'ISO-8859-1'),
			array('file_root_path'=>'/media/js/test3.js', 'charset'=>SITE_ENCODING),
			array('file_root_path'=>'/media/js/test4.js', 'charset'=>'utf-8'),
			array('file_root_path'=>'/media/js/test5.js', 'charset'=>'UTF-8'),
		);
		$out2c=array(
			array('file_root_path'=>$js_out_root_dir2.'/'.Compressor::getCompressedFileName($files2, 'js'), 'charset'=>SITE_ENCODING),
			array('file_root_path'=>'/media/js/test1.js', 'charset'=>SITE_ENCODING),
			array('file_root_path'=>'/media/js/test2.js', 'charset'=>'ISO-8859-1'),
		);
		$files3=array(
			array('file_root_path'=>'/media/js/test1.js', 'alone'=>true), 
			array('file_root_path'=>'/media/js/test2.js', 'compress'=>false),
			array('file_root_path'=>'/media/js/test3.js', 'compress'=>false, 'charset'=>'ISO-8859-1'),
			array('file_root_path'=>'/media/js/test4.js', 'compress'=>true),
			array('file_root_path'=>'/media/js/test5.js', 'compress'=>true, 'charset'=>'ISO-8859-1'),
		);
		$js_out_root_dir3='/media/js';
		$out3=array(
			array('file_root_path'=>'/media/js/test1.js', 'charset'=>SITE_ENCODING),
			array('file_root_path'=>'/media/js/test2.js', 'charset'=>SITE_ENCODING),
			array('file_root_path'=>'/media/js/test3.js', 'charset'=>'ISO-8859-1'),
			array('file_root_path'=>'/media/js/test4.js', 'charset'=>SITE_ENCODING),
			array('file_root_path'=>'/media/js/test5.js', 'charset'=>'ISO-8859-1'),
		);
		$out3c=array(
			array('file_root_path'=>$js_out_root_dir3.'/'.Compressor::getCompressedFileName($files3, 'js'), 'charset'=>SITE_ENCODING),
			array('file_root_path'=>'/media/js/test1.js', 'charset'=>SITE_ENCODING),
		);
		return array(
			array($files1, '/media/js/', true, $out1),
			array($files1, '/media/js/', false, $out1),
			array($files1, '/media/js', true, $out1),
			array($files1, '/media/js', false, $out1),
			array($files1, '', true, $out1),
			array($files1, '', false, $out1),
			array($files2, $js_out_root_dir2, true, $out2),
			array($files2, $js_out_root_dir2, false, $out2c),
			array($files3, $js_out_root_dir3, true, $out3),
			array($files3, $js_out_root_dir3, false, $out3c),
		);
	}
	
	/**
	 * @test
	 * @dataProvider getCompressedFileNameProvider
	 */
	public function getCompressedFileName($files_with_alones, $pure_files, $type){
		$compressed_name=Compressor::getCompressedFileName($files_with_alones, $type);
		$control_name=Compressor::getCompressedFileName($pure_files, $type);
		if( !empty($pure_files) ){
			$this->assertStringStartsWith('_cmprsd.', $compressed_name, 'Compressed file name starts with _cmprsd');
			$this->assertStringEndsWith('.'.$type, $compressed_name, 'Compressed file name ends with .'.$type);
			$this->assertEquals(($type=='css'?20:19), mb_strlen($compressed_name), 'Compressed file name length is correct');
			$this->assertEquals($compressed_name, $control_name, 'Compressed file names are equal with control name');
		}else{
			$this->assertEmpty($compressed_name, 'Compressed file name is empty');
		}
	}
	
	public function getCompressedFileNameProvider(){
		$files1=array(array('file_root_path'=>'/media/css/test1.css', 'compress'=>true), array('file_root_path'=>'/media/css/test1.css', 'compress'=>true));
		$files1c=array(array('file_root_path'=>'/media/css/test1.css', 'compress'=>true), array('file_root_path'=>'/media/css/test1.css', 'compress'=>true));
		$files2=array(array('file_root_path'=>'/media/css/test1.css', 'compress'=>false), array('file_root_path'=>'/media/css/test1.css', 'alone'=>true));
		$files2c=array(array('file_root_path'=>'/media/css/test1.css', 'compress'=>false));
		$files3=array(array('file_root_path'=>'/media/css/test1.css', 'compress'=>true), array('file_root_path'=>'/media/css/test2.css', 'compress'=>false), array('file_root_path'=>'/media/css/test3.css', 'alone'=>true));
		$files3c=array(array('file_root_path'=>'/media/css/test1.css', 'compress'=>true), array('file_root_path'=>'/media/css/test2.css', 'compress'=>false));
		$files4=array(array('file_root_path'=>'/media/css/test1.css', 'alone'=>true), array('file_root_path'=>'/media/css/test2.css', 'compress'=>false), array('file_root_path'=>'/media/css/test3.css', 'alone'=>true), array('file_root_path'=>'/media/css/test4.css', 'compress'=>false));
		$files4c=array(array('file_root_path'=>'/media/css/test2.css', 'compress'=>false), array('file_root_path'=>'/media/css/test4.css', 'compress'=>false));
		$files5=array(array('file_root_path'=>'/media/css/test1.css', 'alone'=>true), array('file_root_path'=>'/media/css/test2.css', 'alone'=>true), array('file_root_path'=>'/media/css/test3.css', 'alone'=>true), array('file_root_path'=>'/media/css/test4.css', 'alone'=>true));
		$files5c=array();

		$files11=array(array('file_root_path'=>'/media/js/test1.js', 'compress'=>true), array('file_root_path'=>'/media/js/test1.js', 'compress'=>true));
		$files11c=array(array('file_root_path'=>'/media/js/test1.js', 'compress'=>true), array('file_root_path'=>'/media/js/test1.js', 'compress'=>true));
		$files12=array(array('file_root_path'=>'/media/js/test1.js', 'compress'=>false), array('file_root_path'=>'/media/js/test1.js', 'alone'=>true));
		$files12c=array(array('file_root_path'=>'/media/js/test1.js', 'compress'=>false));
		$files13=array(array('file_root_path'=>'/media/js/test1.js', 'compress'=>true), array('file_root_path'=>'/media/js/test2.js', 'compress'=>false), array('file_root_path'=>'/media/js/test3.js', 'alone'=>true));
		$files13c=array(array('file_root_path'=>'/media/js/test1.js', 'compress'=>true), array('file_root_path'=>'/media/js/test2.js', 'compress'=>false));
		$files14=array(array('file_root_path'=>'/media/js/test1.js', 'alone'=>true), array('file_root_path'=>'/media/js/test2.js', 'compress'=>false), array('file_root_path'=>'/media/js/test3.js', 'alone'=>true), array('file_root_path'=>'/media/js/test4.js', 'compress'=>false));
		$files14c=array(array('file_root_path'=>'/media/js/test2.js', 'compress'=>false), array('file_root_path'=>'/media/js/test4.js', 'compress'=>false));
		$files15=array(array('file_root_path'=>'/media/js/test1.js', 'alone'=>true), array('file_root_path'=>'/media/js/test2.js', 'alone'=>true), array('file_root_path'=>'/media/js/test3.js', 'alone'=>true), array('file_root_path'=>'/media/js/test4.js', 'alone'=>true));
		$files15c=array();

		return array(
			array($files1, $files1c, 'css'),
			array($files2, $files2c, 'css'),
			array($files3, $files3c, 'css'),
			array($files4, $files4c, 'css'),
			array($files5, $files5c, 'css'),
			array($files11, $files11c, 'js'),
			array($files12, $files12c, 'js'),
			array($files13, $files13c, 'js'),
			array($files14, $files14c, 'js'),
			array($files15, $files15c, 'js'),
		);
	}
	
	/**
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionCode 1007
	 */
	public function getCompressedFileName2(){
		$files_with_alones=array(array('file_root_path'=>'/media/css/test1.css', 'compress'=>true), array('file_root_path'=>'/media/css/test1.css', 'compress'=>true));
		Compressor::getCompressedFileName($files_with_alones, 'mp3');
	}
	
	/**
	 * @test
	 * @dataProvider fileMissingOrOutdateProvider
	 */
	public function fileMissingOrOutdate($compressed_file_root_path, $files, $result){
		$bool=Compressor::fileMissingOrOutdate($compressed_file_root_path, $files);
		$this->assertEquals($result, $bool, 'compressed file actuality determened');
	}
	
	public function fileMissingOrOutdateProvider(){
		$files1=array(array('file_root_path'=>'/media/css/test1.css'),array('file_root_path'=>'/media/css/test2.css'),array('file_root_path'=>'/media/css/test3.css'),array('file_root_path'=>'/media/css/test4.css'),array('file_root_path'=>'/media/css/test5.css'));
		$files2=array(array('file_root_path'=>'/media/js/test1.js'),array('file_root_path'=>'/media/js/test2.js'),array('file_root_path'=>'/media/js/test3.js'),array('file_root_path'=>'/media/js/test4.js'),array('file_root_path'=>'/media/js/test5.js'));
		return array(
			array('/media/css/_cmprsd.abc1.css', $files1, false),
			array('/media/css/_cmprsd.abc2.css', $files1, false),
			array('/media/css/_cmprsd.abc3.css', $files1, true),
			array('/media/css/_cmprsd.abc4.css', $files1, true),
			array('/media/css/_cmprsd.abc5.css', $files1, true),
			array('/media/css/_cmprsd.abc6.css', $files1, true),
			array('/media/js/_cmprsd.abc1.js', $files2, false),
			array('/media/js/_cmprsd.abc2.js', $files2, false),
			array('/media/js/_cmprsd.abc3.js', $files2, true),
			array('/media/js/_cmprsd.abc4.js', $files2, true),
			array('/media/js/_cmprsd.abc5.js', $files2, true),
			array('/media/js/_cmprsd.abc6.js', $files2, true),
		);
	}
	
	/**
	 * @test
	 * @dataProvider getCompressedContentsProvider
	 */
	public function getCompressedContents($files, $result, $type){
		$compressed_contents=Compressor::getCompressedContents($files, $type, SITE_ENCODING);
		$this->assertEquals($result, $compressed_contents, 'Compressed contents is correct');
	}
	
	public function getCompressedContentsProvider(){
		$files1=array(array('file_root_path'=>'/media/css/test1.css', 'compress'=>true));
		$contents1='';
		$files2=array(array('file_root_path'=>'/media/css/test1.css', 'compress'=>false));
		$contents2='/*test1.css*/';
		$files3=array(array('file_root_path'=>'/media/css/test1.css', 'compress'=>true), array('file_root_path'=>'/media/css/test2.css', 'compress'=>false), array('file_root_path'=>'/media/css/test3.css', 'compress'=>true));
		$contents3='/*test2.css*/';
		$files4=array(array('file_root_path'=>'/media/css/test1.css', 'compress'=>true), array('file_root_path'=>'/media/css/test2.css', 'compress'=>false), array('file_root_path'=>'/media/css/test3.css', 'compress'=>true), array('file_root_path'=>'/media/css/test4.css', 'compress'=>false));
		$contents4='/*test2.css*//*test4.css*/';
		$files5=array(array('file_root_path'=>'/media/css/test1.css', 'alone'=>true), array('file_root_path'=>'/media/css/test2.css', 'alone'=>true), array('file_root_path'=>'/media/css/test3.css', 'compress'=>true), array('file_root_path'=>'/media/css/test4.css', 'compress'=>false));
		$contents5='/*test4.css*/';
		$files6=array(array('file_root_path'=>'/media/css/test1.css', 'alone'=>true));
		$contents6='';

		$files7=array( array('file_root_path'=>'/media/js/test1.js', 'compress'=>false) );
		$contents7='/*test1.js*/';
		$files8=array( array('file_root_path'=>'/media/js/test1.js', 'compress'=>false),array('file_root_path'=>'/media/js/test2.js', 'compress'=>true) );
		$contents8='/*test1.js*/';
		$files9=array( array('file_root_path'=>'/media/js/test1.js', 'compress'=>false),array('file_root_path'=>'/media/js/test2.js', 'compress'=>false) );
		$contents9='/*test1.js*//*test2.js*/';
		$files10=array( array('file_root_path'=>'/media/js/test1.js', 'alone'=>true),array('file_root_path'=>'/media/js/test2.js', 'compress'=>false) );
		$contents10='/*test2.js*/';
		
		return array(
			array($files1, $contents1, 'css'),
			array($files2, $contents2, 'css'),
			array($files3, $contents3, 'css'),
			array($files4, $contents4, 'css'),
			array($files5, $contents5, 'css'),
			array($files6, $contents6, 'css'),
			array($files7, $contents7, 'js'),
			array($files8, $contents8, 'js'),
			array($files9, $contents9, 'js'),
			array($files10, $contents10, 'js'),
		);
	}

	/**
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionCode 1007
	 */
	public function getCompressedContents2(){
		$files=array(array('file_root_path'=>'/media/css/test1.css', 'compress'=>true));
		Compressor::getCompressedContents($files, 'txt', SITE_ENCODING);
	}
	
	/**
	 * @test
	 * @dataProvider getCssLinkProvider
	 */
	public function getCssLink($path_with_filemtime, $media, $site_encoding, $html_version, $result){
		$link_tag=Compressor::getCssLink($path_with_filemtime, $media, $site_encoding, $html_version);
		$this->assertEquals($result, $link_tag, 'tag <link> is correct');
	}
	
	public function getCssLinkProvider(){
		return array(
			array('/media/css/stye.css', 'screen', 'UTF-8', 4, '<link rel="stylesheet" href="/media/css/stye.css" type="text/css" charset="UTF-8">'),
			array('/media/css/stye.css', 'print', 'UTF-8', 4, '<link rel="stylesheet" href="/media/css/stye.css" type="text/css" media="print" charset="UTF-8">'),
			array('/media/css/stye.css', 'screen', 'WINDOWS-1251', 4, '<link rel="stylesheet" href="/media/css/stye.css" type="text/css" charset="WINDOWS-1251">'),
			array('/media/css/stye.css', 'print', 'WINDOWS-1251', 4, '<link rel="stylesheet" href="/media/css/stye.css" type="text/css" media="print" charset="WINDOWS-1251">'),
			array('/media/css/stye.css', 'screen', 'UTF-8', 5, '<link rel="stylesheet" href="/media/css/stye.css">'),
			array('/media/css/stye.css', 'print', 'UTF-8', 5, '<link rel="stylesheet" href="/media/css/stye.css" media="print">'),
			array('/media/css/stye.css', 'screen', 'WINDOWS-1251', 5, '<link rel="stylesheet" href="/media/css/stye.css">'),
			array('/media/css/stye.css', 'print', 'WINDOWS-1251', 5, '<link rel="stylesheet" href="/media/css/stye.css" media="print">'),
		);
	}
	
	/**
	 * @test
	 * @dataProvider getJsLinkProvider
	 */
	public function getJsLink($path_with_filemtime, $charset, $html_version, $result){
		$link_tag=Compressor::getJsLink($path_with_filemtime, $charset, $html_version);
		$this->assertEquals($result, $link_tag, 'tag <script...> is correct');
	}
	
	public function getJsLinkProvider(){
		return array(
			array('/media/js/client.js', 'UTF-8', 4, '<script type="text/javascript" src="/media/js/client.js" charset="UTF-8"></script>'),
			array('/media/js/client.js', 'WINDOWS-1251', 4, '<script type="text/javascript" src="/media/js/client.js" charset="WINDOWS-1251"></script>'),
			array('/media/js/client.js', 'UTF-8', 5, '<script src="/media/js/client.js" charset="UTF-8"></script>'),
			array('/media/js/client.js', 'WINDOWS-1251', 5, '<script src="/media/js/client.js" charset="WINDOWS-1251"></script>'),
		);
	}
	
	/**
	 * @test
	 * @dataProvider outCssProvider
	 */
	public function outCss($root_dir, $result){
		ob_start();
		Compressor::outCss($root_dir);
		ob_end_clean();
		$this->assertEquals($result, Compressor::$css_out_root_dir, 'Compressor::$css_out_root_dir is correct');
	}
	
	public function outCssProvider(){
		return array(
			array('', '/media/css'),
			array('/', ''),
			array('/media', '/media'),
			array('/media/', '/media'),
			array('/media/css', '/media/css'),
			array('/media/css/', '/media/css'),
		);
	}
	
	/**
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionCode 1004
	 */
	public function outCss2(){
		Compressor::outCss('/abcde/0897/ASDF');
	}
	
	/**
	 * @test
	 * @dataProvider outJsProvider
	 */
	public function outJs($root_dir, $result){
		ob_start();
		Compressor::outJs($root_dir);
		ob_end_clean();
		$this->assertEquals($result, Compressor::$js_out_root_dir, 'Compressor::$js_out_root_dir is correct');
	}
	
	public function outJsProvider(){
		return array(
			array('', '/media/js'),
			array('/', ''),
			array('/media', '/media'),
			array('/media/', '/media'),
			array('/media/js', '/media/js'),
			array('/media/js/', '/media/js'),
		);
	}
	
	/**
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionCode 1004
	 */
	public function outJs2(){
		Compressor::outJs('/abcde/0897/ASDF');
	}

	/**
	 * @test
	 * @dataProvider getRootDirProvider
	 */
	public function getRootDir($given_root_dir, $result){
		$root_dir=Compressor::getRootDir($given_root_dir);
		$this->assertEquals($result, $root_dir, '$root_dir is correct');
	}
	
	public function getRootDirProvider(){
		return array(
			array('/', ''),
			array('/media', '/media'),
			array('/media/', '/media'),
		);
	}

	/**
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionCode 1004
	 */
	public function getRootDir2(){
		Compressor::getRootDir('/one/two/three/four/five');
	}

	/**
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionCode 1005
	 */
	public function getRootDir3(){
		Compressor::getRootDir('');
	}

	/**
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionCode 1006
	 */
	public function getRootDir4(){
		Compressor::getRootDir('/media//');
	}

	/**
	 * @test
	 * @dataProvider getRootFilePathWithMtimeProvider
	 */
	public function getRootFilePathWithMtime($given_file_root_path, $has_mtime){
		$path_with_filemtime=Compressor::getRootFilePathWithMtime($given_file_root_path);
		if( $has_mtime ){
			$this->assertEquals(mb_strlen($given_file_root_path) + 11, mb_strlen($path_with_filemtime), 'result path 11 chars longer than given path');
		}else{
			$this->assertEquals($given_file_root_path, $path_with_filemtime, 'given file path matches the result path');
		}
	}
	
	/**
	 * @test
	 */
	public function getRootFilePathWithMtimeProvider(){
		return array(
			array('/robots.txt', false),
			array('/media/css/style.css', true),
			array('/media/css/style.css', true),
			array('/media/js/client.js', true),
			array('/media/img/void.gif', true),
			array('/media/lib/readme.txt', true),
			array('/admin/fw/functions.php', false),
			array('/admin/fw/init.php', false),
			array('/admin/fw/media/css/admin.css', true),
			array('/admin/fw/media/css/dbconsole.css', true),
			array('/admin/fw/media/js/admin.js', true),
			array('/admin/fw/phpinfo.php', false),
		);
	}
	
	/**
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionCode 1001
	 */
	public function getRootFilePathWithMtime2(){
		Compressor::getRootFilePathWithMtime('/not_existed_file.txt');
	}
	
	/**
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionCode 1002
	 */
	public function getRootFilePathWithMtime3(){
		Compressor::getRootFilePathWithMtime('/');
	}
}
