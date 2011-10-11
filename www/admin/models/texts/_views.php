<?php

class TextsViews extends Texts{
	var $views_arr=array(
		'content'=>'содержимое страницы',
	);

	function init(){
		$this->texts=$this->getPageTexts();
		smartas('texts',$this->texts);
	}

	function content(){
	}
}
