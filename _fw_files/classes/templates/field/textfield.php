<?php

$form_items=new FormItems();

$result=$form_items->area($this->field_name, $inputValue);
if($this->editor && $this->editor!='no'){
	$editor_name='editor_'.mb_substr(_crypt($this->field_name),0,10);
	$height=explode('_',$this->editor);
	if(intval($height[1])>0){
		$height=intval($height[1]);
	}else{
		$height=240;
		$this->editor=$this->editor.'_'.$height;
	}
	$result.='<script type="text/javascript"><!--
		$(document).ready(function(){
			var '.$editor_name.'=CKEDITOR.replace("'.$this->field_name.'",{
				toolbar: "'.$this->editor.'",
				height: "'.$height.'"
			});
			AjexFileManager.init({
				// path: "/__lib/ckeditor/ajexFileManager/",
				// width: "", // Ширина popup, default=1000
				// height: "", // Высота popup, default=660
				// lang: "", // Язык, сейчас есть [ru, en], default=ru
				// connector: "", // default=php
				// contextmenu: true // [true, false], default=true
				returnTo: "ckeditor", 
				editor: '.$editor_name.',
				skin: "light"
			});
		})
	--></script>';

	// это кусок старого кода, в котором имеются значения класса и id для <body>
	// чтобы  style.css, в котором есть привязки к классу и id тега <body> мог показать реальную картинку
	// if(isset($this->__fck_body_id__))
	// $result.='oFCKeditor.BodyId="'.$this->__fck_body_id__.'";';
	// if(isset($this->__fck_body_class__))
	// $result.='oFCKeditor.BodyClass="'.$this->__fck_body_class__.'";';
	// $result.='--></script>';
}
