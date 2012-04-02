<?php

if( empty($_SERVER['DOCUMENT_ROOT']) ){
	$_SERVER['DOCUMENT_ROOT']=realpath(__dir__.'/../../..');
	$_SERVER['SERVER_NAME']='msk.mysite.com';
	$_SERVER['HTTP_HOST']='msk.mysite.com';
	define('DONT_INCLUDE_CALLEE', true);
	include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/init.php');
}

require_once($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/functions.class.php');

class FunctionsTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * @test
	 * @dataProvider gmoProvider
	 */
	public function gmo($model_name){
		$obj_model1=gmo($model_name);
		$this->assertTrue(is_a($obj_model1, $model_name), "an intstance of $model_name class received");
		$obj_model2=gmo($model_name);
		$this->assertEquals($obj_model1, $obj_model2, 'two objects are equal');
		$random_value=rand(1,9999);
		$obj_model1->__my_test_property__=$random_value;
		$this->assertEquals($obj_model1->__my_test_property__, $obj_model2->__my_test_property__, "method returned back a reference to the same object");
	}
	
	public function gmoProvider(){
		return array(
			array('Structure'),
			array('Texts'),
		);
	}
	
	/**
	 * @test
	 * @dataProvider gmiProvider
	 */
	public function gmi($model_name, $data){
		$model_item=gmi($model_name, $data);
		$this->assertTrue(is_a($model_item, $model_name), "Model item is an instance of $model_name class");
		foreach($data as $key=>$value){
			$this->assertEquals($value, $model_item->$key, 'Property is correct');
		}
	}

	public function gmiProvider(){
		return array(
			array('Structure', array('parent'=>DOMAIN_ID,'title'=>'my test title')),
			array('structure', array('id'=>DOMAIN_ID)),
			array('Texts', array('structure_id'=>DOMAIN_ID,'body'=>'my test title')),
		);
	}
	
	/**
	 * @test
	 * @dataProvider gmvProvider
	 */
	public function gmv($model_name, $views){
		$model_item=Functions::gmv($model_name);
		$this->assertTrue(is_subclass_of($model_item, $model_name), "Model view is subclass of $model_name");
		foreach($views as $method_name){
			$this->assertTrue(method_exists($model_item, $method_name), "Model view has method $method_name");
		}
	}

	public function gmvProvider(){
		return array(
			array('Structure', array('init','gradusnik','pageTitle','titleMetaTags')),
			array('Texts', array('init','content')),
		);
	}
	
	/**
	 * @test
	 * @dataProvider getPathByViewProvider
	 */
	public function getPathByView($view, $domain, $result){
		if( empty($domain) ){
			$path=getPathByView($view);
		}else{
			$path=getPathByView($view, $domain);
		}
		$this->assertEquals($result, $path, "View $view is attached to $path page");
	}
	
	public function getPathByViewProvider(){
		$tests=array();
		$dbq=new DBQ('select view, url from structure where parent=?',DOMAIN_ID);
		foreach($dbq->items as $item){
			$dbq=new DBQ('select count(*) from structure where view=? and domain=?',$item['view'],DOMAIN_ID);
			if( $dbq->item==1 ){
				$tests[]=array($item['view'], '', "/{$item['url']}/");
				$tests[]=array($item['view'], DOMAIN_ID, "/{$item['url']}/");
			}
		}
		return $tests;
	}
	
	/**
	 * @test
	 * @dataProvider getPathByIdProvider
	 */
	public function getPathById($id, $domain, $result){
		if( empty($domain) ){
			$path=getPathById($id);
		}else{
			$path=getPathById($id,$domain);
		}
		$this->assertEquals($result, $path, "Folder $id located at $path");
	}

	public function getPathByIdProvider(){
		$tests=array();
		$tests[]=array(DOMAIN_ID, '', '/');
		$tests[]=array(DOMAIN_ID, DOMAIN_ID, '/');
		$dbq=new DBQ('select id, url from structure where parent=?',DOMAIN_ID);
		foreach($dbq->items as $item){
			$tests[]=array($item['id'], '', DOMAIN_PATH."/{$item['url']}/");
			$tests[]=array($item['id'], DOMAIN_ID, DOMAIN_PATH."/{$item['url']}/");
		}
		return $tests;
	}

	// /**
	//  * @test
	//  * @dataProvider defvarProvider
	//  */
	// public function defvar($default, $value, $result){
	// 	$f=new Functions();
	// 	$value=Functions::defvar($default, $value);
	// 	// $value=call_user_func_array(array('Functions','defvar'), $params);
	// 	$this->assertEquals($result, $value, 'defvar OK');
	// }
	// 
	// public function defvarProvider(){
	// 	return array(
	// 		array(true, true, true),
	// 		array(true, '', true),
	// 		array(true, false, false),
	// 		array(true, 0, 0),
	// 		array(true, 1, 1),
	// 		array(false, false, false),
	// 		array(false, '', false),
	// 		array(false, true, true),
	// 		array(false, 0, 0),
	// 		array(false, 1, 1),
	// 		array(false, 'abc', 'abc'),
	// 		array(0, 1, 1),
	// 		array(0, '', 0),
	// 		array(0, 0, 0),
	// 		array(0, 2, 2),
	// 		array(0, true, true),
	// 		array(0, false, false),
	// 		array(0, 'abc', 'abc'),
	// 		array(1, 1, 1),
	// 		array(1, '', 1),
	// 		array(1, 0, 0),
	// 		array(1, 2, 2),
	// 		array(1, true, true),
	// 		array(1, false, false),
	// 		array(1, 'abc', 'abc'),
	// 		array('abc', 'abc', 'abc'),
	// 		array('abc', 'd', 'd'),
	// 		array('abc', '', 'abc'),
	// 		array('abc', 0, 0),
	// 		array('abc', 1, 1),
	// 		array('abc', true, true),
	// 		array('abc', false, false),
	// 	);
	// }
}