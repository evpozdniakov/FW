<?php

if( empty($_SERVER['DOCUMENT_ROOT']) ){
	$_SERVER['DOCUMENT_ROOT']=realpath(__dir__.'/../../..');
	$_SERVER['SERVER_NAME']='msk.mysite.com';
	$_SERVER['HTTP_HOST']='msk.mysite.com';
}
require_once($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/fw.class.php');

class FWTest extends PHPUnit_Framework_TestCase {
	protected function setUp(){
	}
	
	protected function tearDown(){
	}
	
	/**
	 * @test
	 */
	public function setDIRs(){
		FW::setDIRs();
		$this->assertTrue( defined('SITE_DIR'), 'SITE_DIR defined' );
		$this->assertEquals( $_SERVER['DOCUMENT_ROOT'], constant('SITE_DIR'), 'SITE_DIR==DOCUMENT_ROOT' );
		$this->assertTrue( defined('FW_DIR'), 'FW_DIR defined' );
		$this->assertTrue( defined('MODELS_DIR'), 'MODELS_DIR defined' );
	}

	/**
	 * @test
	 */
	public function includeSitecfgAndBasefunc(){
		FW::includeSitecfgAndBasefunc();
		$this->assertTrue( defined('USE_SUBDOMAINS'), 'USE_SUBDOMAINS defined, so config.php is loaded' );
		$this->assertTrue( function_exists('gmo'), 'gmo is function, so functions.php is loaded' );
	}
	
	/**
	 * @test
	 * @dataProvider getDebugOptionValuesProvider
	 */
	public function getDebugOptionValues($debug_options_str, $result){
		$debug_option_values=FW::getDebugOptionValues($debug_options_str);
		$this->assertEquals($result, $debug_option_values);
	}
	
	public function getDebugOptionValuesProvider(){
		return array(
			array('DB DB_SAVE_STACK DB_INCLUDE_RES EMAIL_ERROR SMARTY_CONSOLE_ON TRACE_ON_DIE DISPLAY_PHP_ERRORS', array('DEBUG_DB'=>true, 'DEBUG_DB_SAVE_STACK'=>true, 'DEBUG_DB_INCLUDE_RES'=>true, 'DEBUG_EMAIL_ERROR'=>true, 'DEBUG_SMARTY_CONSOLE_ON'=>true, 'DEBUG_TRACE_ON_DIE'=>true, 'DEBUG_DISPLAY_PHP_ERRORS'=>true)),
			array('EMAIL_ERROR SMARTY_CONSOLE_ON TRACE_ON_DIE DISPLAY_PHP_ERRORS', array('DEBUG_DB'=>false, 'DEBUG_DB_SAVE_STACK'=>false, 'DEBUG_DB_INCLUDE_RES'=>false, 'DEBUG_EMAIL_ERROR'=>true, 'DEBUG_SMARTY_CONSOLE_ON'=>true, 'DEBUG_TRACE_ON_DIE'=>true, 'DEBUG_DISPLAY_PHP_ERRORS'=>true)),
			array('DB DB_SAVE_STACK DB_INCLUDE_RES EMAIL_ERROR', array('DEBUG_DB'=>true, 'DEBUG_DB_SAVE_STACK'=>true, 'DEBUG_DB_INCLUDE_RES'=>true, 'DEBUG_EMAIL_ERROR'=>true, 'DEBUG_SMARTY_CONSOLE_ON'=>false, 'DEBUG_TRACE_ON_DIE'=>false, 'DEBUG_DISPLAY_PHP_ERRORS'=>false)),
			array('', array('DEBUG_DB'=>false, 'DEBUG_DB_SAVE_STACK'=>false, 'DEBUG_DB_INCLUDE_RES'=>false, 'DEBUG_EMAIL_ERROR'=>false, 'DEBUG_SMARTY_CONSOLE_ON'=>false, 'DEBUG_TRACE_ON_DIE'=>false, 'DEBUG_DISPLAY_PHP_ERRORS'=>false)),
		);
	}
	
	/**
	 * @test
	 */
	public function setPHP(){
		FW::setPHP();
		$this->assertEquals(constant('SITE_DIR').'/admin/_php.log', ini_get('error_log'), 'PHP config var "error_log" is set correctly');
	}
	
	/**
	 * @test
	 */
	public function startMbstring(){
		FW::startMbstring();
		$this->assertEquals('neutral', strtolower(ini_get('mbstring.language')), 'PHP config var "mbstring.language" is set to "neutral"');
		$this->assertTrue(defined('SITE_ENCODING'), 'SITE_ENCODING is defined');
		$this->assertEquals(constant('SITE_ENCODING'), ini_get('mbstring.internal_encoding'), 'PHP config var "mbstring.internal_encoding" is equal to SITE_ENCODING');
		$this->assertEquals('pass', ini_get('mbstring.http_input'), 'PHP config var "mbstring.http_input" is set to "pass"');
		$this->assertEquals('pass', ini_get('mbstring.http_output'), 'PHP config var "mbstring.http_output" is set to "pass"');
		$this->assertEquals('auto', ini_get('mbstring.detect_order'), 'PHP config var "mbstring.detect_order" is set to "auto"');
		$this->assertEquals('none', ini_get('mbstring.substitute_character'), 'PHP config var "mbstring.substitute_character" is set to "pass"');
	}
	
	/**
	 * @test
	 */
	public function ajaxSets(){
		FW::ajaxSets();
		$this->assertTrue( defined('IS_AJAX'), 'IS_AJAX is defined' );
		$this->assertTrue( is_bool(constant('IS_AJAX')), 'IS_AJAX is bool' );
	}
	
	/**
	 * @test
	 */
	public function connectDB(){
		FW::connectDB();
		$this->assertTrue( defined('USE_DB'), 'USE_DB is defined' );
		$this->assertTrue( is_bool(constant('USE_DB')), 'USE_DB is bool' );
	}
	
	/**
	 * @test
	 */
	public function setServerName(){
		FW::setServerName();
		$this->assertTrue( defined('SERVER_NAME'), 'SERVER_NAME is defined' );
		$this->assertEquals( $_SERVER['SERVER_NAME'], constant('SERVER_NAME'), 'SERVER_NAME is correct' );
	}
	
	/**
	 * @test
	 * @dataProvider fixServerRequestUriProvider
	 */
	public function fixServerRequestUri($request_uri, $fixed_request_uri, $request_uri_original){
		$_SERVER['REQUEST_URI']=$request_uri;
		$_SERVER['REQUEST_URI_ORIGINAL']=NULL;
		FW::fixServerRequestUri();
		$this->assertEquals( $request_uri_original, $_SERVER['REQUEST_URI_ORIGINAL'], '$_SERVER[REQUEST_URI_ORIGINAL] is correct' );
		$this->assertEquals( $fixed_request_uri, $_SERVER['REQUEST_URI'], '$_SERVER[REQUEST_URI] is correct' );
	}
	
	public function fixServerRequestUriProvider(){
		return array(
			array('/a/b/c/d/', '/a/b/c/d/', NULL),
			array('/a/b/c/d/?efgh', '/a/b/c/d/', '/a/b/c/d/?efgh'),
			array('/a/b/c/<script>alert(1)</script>/d/', '/a/b/c/alert(1)/d/', NULL),
			array('/a/b/c/<script>alert(1)</script>/d/?efgh', '/a/b/c/alert(1)/d/', '/a/b/c/alert(1)/d/?efgh'),
		);
	}
	
	/**
	 * @test
	 * @dataProvider setGlobalsPathRequestedProvider
	 */
	public function setGlobalsPathRequested($request_uri, $result){
		$_SERVER['REQUEST_URI']=$request_uri;
		FW::setGlobalsPathRequested();
		$this->assertEquals($result, $GLOBALS['path_requested'], '$GLOBALS[path_requested] is correct');
	}
	
	public function setGlobalsPathRequestedProvider(){
		return array(
			array('/my/favorite/page', array($_SERVER['SERVER_NAME'], 'my', 'favorite', 'page')),
			array('/one/two/three.html', array($_SERVER['SERVER_NAME'], 'one', 'two', 'three.html')),
			array('/abc/def/ghi/', array($_SERVER['SERVER_NAME'], 'abc', 'def', 'ghi')),
			array('/abc/def/ghi/jkl/', array($_SERVER['SERVER_NAME'], 'abc', 'def', 'ghi', 'jkl')),
			array('/abc/def/', array($_SERVER['SERVER_NAME'], 'abc', 'def')),
			array('/abc/', array($_SERVER['SERVER_NAME'], 'abc')),
			array('/', array($_SERVER['SERVER_NAME'])),
		);
	}
	
	/**
	 * @test
	 * @dataProvider fixServerRedirectUrlProvider
	 */
	public function fixServerRedirectUrl($__uri__, $result){
		$_GET['__uri__']=$__uri__;
		FW::fixServerRedirectUrl();
		$this->assertEquals( $result, $_SERVER['REDIRECT_URL'], '$_SERVER[REDIRECT_URL] is correect' );
	}
	
	public function fixServerRedirectUrlProvider(){
		return array(
			array('a/b/c/d/', '/a/b/c/d/'),
			array('a/b/c/<script>alert(1)</script>/d/', '/a/b/c/alert(1)/d/'),
		);
	}
	
	/**
	 * @test
	 * @dataProvider setGlobalsPathProvider
	 */
	public function setGlobalsPath($redirect_url, $result){
		$_SERVER['REDIRECT_URL']=$redirect_url;
		FW::setGlobalsPath();
		$this->assertEquals($result, $GLOBALS['path'], '$GLOBALS[path] is correct');
	}
	
	public function setGlobalsPathProvider(){
		return array(
			array('/my/favorite/page', array($_SERVER['SERVER_NAME'], 'my', 'favorite', 'page')),
			array('/one/two/three.html', array($_SERVER['SERVER_NAME'], 'one', 'two', 'three.html')),
			array('/abc/def/ghi/', array($_SERVER['SERVER_NAME'], 'abc', 'def', 'ghi')),
			array('/abc/def/ghi/jkl/', array($_SERVER['SERVER_NAME'], 'abc', 'def', 'ghi', 'jkl')),
			array('/abc/def/', array($_SERVER['SERVER_NAME'], 'abc', 'def')),
			array('/abc/', array($_SERVER['SERVER_NAME'], 'abc')),
			array('/', array($_SERVER['SERVER_NAME'])),
		);
	}
	
	/**
	 * @test
	 */
	public function setPrintVersion(){
		$_GET['__print_version__']=1;
		FW::setPrintVersion();
		$this->assertTrue(defined('PRINT_VERSION'), 'PRINT_VERSION is defined');
		$this->assertTrue(constant('PRINT_VERSION'), 'PRINT_VERSION is set correctly');
	}
	
	/**
	 * @test
	 * @dataProvider getDomainValueProvider
	 */
	public function getDomainValue($use_db, $use_multidomains, $server_name, $use_subdomains, $domains_list, $default_domain, $hide_default_domain, $__domain__, $result){
		$domain=FW::getDomainValue($use_db, $use_multidomains, $server_name, $use_subdomains, $domains_list, $default_domain, $hide_default_domain, $__domain__);
		$this->assertEquals($result, $domain);
	}
	
	function getDomainValueProvider(){
		// $use_db, $use_multidomains, $server_name, $use_subdomains, $domains_list, $default_domain, $hide_default_domain, $__domain__
		return array(
			array(true, true, 'msk.meetafora.ru', true, 'msk spb', 'msk', false, '', 'msk'),
			array(true, true, 'spb.meetafora.ru', true, 'msk spb', 'msk', false, '', 'spb'),
			array(true, true, 'meetafora.ru', true, 'msk spb', 'msk', true, '', 'msk'),
			array(true, true, 'meetafora.ru', true, 'msk spb', 'spb', true, '', 'spb'),
			array(true, true, 'meetafora.ru', false, 'msk spb', 'msk', false, 'spb', 'spb'),
			array(true, true, 'meetafora.ru', false, 'msk spb', 'msk', true, '', 'msk'),
		);
	}
	
	/**
	 * @test
	 * @dataProvider getDefaultDomainRedirectionProvider
	 */
	public function getDefaultDomainRedirection($use_db, $use_multidomains, $current_domain, $server_name, $request_uri, $use_subdomains, $default_domain, $hide_default_domain, $result){
		$redirection=FW::getDefaultDomainRedirection($use_db, $use_multidomains, $current_domain, $server_name, $request_uri, $use_subdomains, $default_domain, $hide_default_domain);
		$this->assertEquals($result, $redirection);
	}
	
	public function getDefaultDomainRedirectionProvider(){
		// $use_db, $use_multidomains, $current_domain, $server_name, $request_uri, $use_subdomains, $default_domain, $hide_default_domain
		return array(
			array(false, true, 'msk', 'msk.meetafora.ru', '/', true, 'msk', true, ''),
			array(true, false, 'msk', 'msk.meetafora.ru', '/', true, 'msk', true, ''),
			array(false, false, 'msk', 'msk.meetafora.ru', '/', true, 'msk', true, ''),
			array(true, true, 'msk', 'msk.meetafora.ru', '/', true, 'msk', true, 'meetafora.ru/'),
			array(true, true, 'msk', 'msk.meetafora.ru', '/abc/', true, 'msk', true, 'meetafora.ru/abc/'),
			array(true, true, 'msk', 'msk.meetafora.ru', '/', true, 'msk', false, ''),
			array(true, true, 'msk', 'msk.meetafora.ru', '/d/e/f/', true, 'msk', false, ''),
			array(true, true, 'msk', 'meetafora.ru', '/', true, 'msk', true, ''),
			array(true, true, 'msk', 'meetafora.ru', '/abcd.html', true, 'msk', true, ''),
			array(true, true, 'msk', 'meetafora.ru', '/', true, 'msk', false, 'msk.meetafora.ru/'),
			array(true, true, 'msk', 'meetafora.ru', '/abc/d.html', true, 'msk', false, 'msk.meetafora.ru/abc/d.html'),
			array(true, true, 'spb', 'spb.meetafora.ru', '/', true, 'msk', true, ''),
			array(true, true, 'spb', 'spb.meetafora.ru', '/abc/', true, 'msk', true, ''),
			array(true, true, 'spb', 'spb.meetafora.ru', '/', true, 'msk', false, ''),
			array(true, true, 'spb', 'spb.meetafora.ru', '/def.html', true, 'msk', false, ''),
			array(false, true, 'spb', 'spb.meetafora.ru', '/def.html', true, 'msk', false, ''),
			array(true, false, 'spb', 'spb.meetafora.ru', '/def.html', true, 'msk', false, ''),
			array(false, false, 'spb', 'spb.meetafora.ru', '/def.html', true, 'msk', false, ''),
		);
	}
	
	/**
	 * @test
	 */
	public function setDomainId(){
		FW::setDomainId();
		$this->assertTrue(defined('DOMAIN'), 'DOMAIN is defined');
		$this->assertTrue(defined('DOMAIN_ID'), 'DOMAIN_ID is defined');
	}
	
	/**
	 * @test
	 * @dataProvider getDomainPathValueProvider
	 */
	public function getDomainPathValue($use_multidomains, $use_subdomains, $domain, $result){
		FW::getDomainPathValue($use_multidomains, $use_subdomains, $domain);
	}
	
	public function getDomainPathValueProvider(){
		return array(
			array(true, true, 'msk', ''),
			array(true, false, 'msk', '/~msk~'),
			array(false, true, 'msk', ''),
			array(false, false, 'msk', ''),
		);
	}
	
	/**
	 * @test
	 * @dataProvider getIsFirstValueProvider
	 */
	public function getIsFirstValue($use_multidomains, $use_subdomains, $hide_default_domain, $domain_path, $globals_path_requested, $result){
		$is_first=FW::getIsFirstValue($use_multidomains, $use_subdomains, $hide_default_domain, $domain_path, $globals_path_requested);
		$this->assertEquals($result, $is_first, 'IS_FIRST is correct');
	}
	
	public function getIsFirstValueProvider(){
		// $use_multidomains, $use_subdomains, $hide_default_domain, $domain_path, $globals_path_requested, $result
		return array(
			array(true, true, true, '', array('mysite.com','asdf','lkj.html'), false),
			array(true, true, true, '', array('mysite.com','asdf','wert'), false),
			array(true, true, true, '', array('mysite.com'), true),
			array(true, false, true, '/~msk~', array('mysite.com'), true),
			array(true, false, true, '/~msk~', array('mysite.com','test'), false),
			array(true, false, false, '/~msk~', array('mysite.com','~msk~'), true),
			array(true, false, false, '/~msk~', array('mysite.com','~msk~','test'), false),
		);
	}
	
	// function startSession(){
	// 	$GLOBALS['path']=array('','admin');
	// 	// string $name [, string $value [, int $expire = 0 [, string $path [, string $domain [, bool $secure = false [, bool $httponly = false ]]]]]]
	// 	setcookie('FWSID', '', time()-3600*24, '/', removeSubdomain());
	// 	// $this->assertFalse( isset($_COOKIE['FWSID']), 'session FWSID not set');
	// 	FW::startSession();
	// 	// $this->assertTrue( isset($_COOKIE['FWSID']), 'session FWSID is set' );
	// }
	
	/**
	 * @test
	 * @dataProvider getPHPfileFromUrlProvider
	 */
	public function getPHPfileFromUrl($site_dir, $globals_path, $result){
		list($file_name, $file_root_path, $file_full_path)=FW::getPHPfileFromUrl($site_dir, $globals_path);
		$this->assertEquals($result['file_name'], $file_name, '$file_name is correct');
		$this->assertEquals($result['file_root_path'], $file_root_path, '$file_root_path is correct');
		$this->assertEquals($result['file_full_path'], $file_full_path, '$file_full_path is correct');
	}
	
	public function getPHPfileFromUrlProvider(){
		return array(
			array( '/Users/ev/Sites/starter_local/www', array('mysite.com'), array('file_name'=>'index.php', 'file_root_path'=>'/index.php', 'file_full_path'=>'/Users/ev/Sites/starter_local/www/index.php') ),
			array( '/Users/ev/Sites/starter_local/www', array('mysite.com', 'about'), array('file_name'=>'index.php', 'file_root_path'=>'/about/index.php', 'file_full_path'=>'/Users/ev/Sites/starter_local/www/about/index.php') ),
			array( '/Users/ev/Sites/starter_local/www', array('mysite.com', 'test.php'), array('file_name'=>'test.php', 'file_root_path'=>'/test.php', 'file_full_path'=>'/Users/ev/Sites/starter_local/www/test.php') ),
			array( '/Users/ev/Sites/starter_local/www', array('mysite.com', 'about', 'test.php'), array('file_name'=>'test.php', 'file_root_path'=>'/about/test.php', 'file_full_path'=>'/Users/ev/Sites/starter_local/www/about/test.php') ),
			array( '/Users/ev/Sites/starter_local/www', array('mysite.com', 'this.is.my.test.php'), array('file_name'=>'this.is.my.test.php', 'file_root_path'=>'/this.is.my.test.php', 'file_full_path'=>'/Users/ev/Sites/starter_local/www/this.is.my.test.php') ),
			array( '/Users/ev/Sites/starter_local/www', array('mysite.com', 'this', 'is.my.test'), array('file_name'=>'index.php', 'file_root_path'=>'/this/is.my.test/index.php', 'file_full_path'=>'/Users/ev/Sites/starter_local/www/this/is.my.test/index.php') ),
			array( '/Users/ev/Sites/starter_local/www', array('mysite.com', 'simple', 'plain.html'), array('file_name'=>'index.php', 'file_root_path'=>'/simple/plain.html/index.php', 'file_full_path'=>'/Users/ev/Sites/starter_local/www/simple/plain.html/index.php') ),
		);
	}
}