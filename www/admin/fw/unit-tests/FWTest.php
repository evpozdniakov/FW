<?php

if( !empty($_SERVER['DOCUMENT_ROOT']) ){
	define('SITE_DIR', $_SERVER['DOCUMENT_ROOT']);
}else{
	define('SITE_DIR', dirname(__FILE__).'/..');
}
define('FW_DIR', SITE_DIR.'/admin/fw');
require_once(FW_DIR.'/classes/fw.class.php');
include(SITE_DIR.'/../config.php');
include(FW_DIR.'/functions.php');

class FWTest extends PHPUnit_Framework_TestCase {
	protected $fw;
	
	protected function setUp(){
		$this->fw=new FW();
	}
	
	protected function tearDown(){
		$this->fw=NULL;
	}

	public function testDebugInitSetOptions(){
		$fw=new FW();
		$fw::debugInitSetOptions();
		$options=explode(' ','DB DB_SAVE_STACK DB_INCLUDE_RES EMAIL_ERROR SMARTY_CONSOLE_ON TRACE_ON_DIE DISPLAY_PHP_ERRORS');
		foreach($options as $option){
			$this->assertTrue( is_bool(constant('DEBUG_'.$option)) );
		}
	}
	
	public function testSetPHP(){
		$fw=new FW();
		$fw::setPHP();
		$this->assertEquals(constant('SITE_DIR').'/admin/_php.log', ini_get('error_log'));
	}
	
	public function testStartMbstring(){
		$fw=new FW();
		$fw::startMbstring();
		$this->assertEquals('Neutral', ini_get('mbstring.language'));
		$this->assertEquals(constant('SITE_ENCODING'), ini_get('mbstring.internal_encoding'));
		$this->assertEquals('pass', ini_get('mbstring.http_input'));
		$this->assertEquals('pass', ini_get('mbstring.http_output'));
		$this->assertEquals('auto', ini_get('mbstring.detect_order'));
		$this->assertEquals('none', ini_get('mbstring.substitute_character'));
	}
	
	public function testAjaxSets(){
		$fw=new FW();
		$fw::ajaxSets();
		$this->assertTrue( is_bool(constant('IS_AJAX')) );
	}
	
	public function testConnectDB(){
		$fw=new FW();
		$fw::connectDB();
		$this->assertTrue( is_bool(constant('USE_DB')) );
	}
}