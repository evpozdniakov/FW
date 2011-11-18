<?php

if( empty($_SERVER['DOCUMENT_ROOT']) ){
	$_SERVER['DOCUMENT_ROOT']=realpath(__dir__.'/../../..');
	$_SERVER['SERVER_NAME']='msk.mysite.com';
	$_SERVER['HTTP_HOST']='msk.mysite.com';
	define('DONT_INCLUDE_CALLEE', true);
	include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/init.php');
}

require_once($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/functions.class.php');

class CompressorTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * @test
	 * @dataProvider getModelObjectProvider
	 */
	public function getModelObject($model_name){
		$obj_model1=getModelObject($model_name);
		$this->assertTrue(is_a($obj_model1, $model_name), "an intstance of $model_name class received");
		$obj_model2=getModelObject($model_name);
		$random_value=rand(1,9);
		$obj_model1->__my_test_property__=$random_value;
		$this->assertEquals($obj_model1->__my_test_property__, $obj_model2->__my_test_property__, "method returned back true reference to a single object");
	}
	
	public function getModelObjectProvider(){
		return array(
			array('Structure'),
		);
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