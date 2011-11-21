<?php

class FlvField extends FileField{
	var $subfields=array(
		'name'=>array('typelength'=>'varchar(64)','null'=>false),
		'uri'=>array('typelength'=>'varchar(255)','null'=>false),
		'width'=>array('typelength'=>'varchar(4)','null'=>false),
		'height'=>array('typelength'=>'varchar(4)','null'=>false),
		'size'=>array('typelength'=>'varchar(16)','null'=>false),
		'upload_date'=>array('typelength'=>'date','null'=>true),
	);

	
	/*
	сделал единый универсальный метод в filed.file.class.php 
	evpozdniakov@230410
	function getSQLupdate($_relkey){
		/*
		здесь мы должны сохранить файл, расширение и прочие атрибуты
		и сформировать фрагмент sql-запроса на запись всех этих дел
		кроме того, нам нужно удалить старый файл, если он был у элемента или если пользователь
		зачекал соответствующий чекбокс
		* /

		if($_relkey!=''){
			$files=$_FILES[$_relkey];
			$post=$_POST[$_relkey];
		}else{
			$files=$_FILES[$this->model_name];
			$post=$_POST[$this->model_name];
		}
		$f=array();
		if(is_array($files) && $files['name'][$this->db_column]!=''){
			$original_file_name=$files['name'][$this->db_column];
			//определяем имя нового файла
			$f['name']=getNewFileName($original_file_name);
			//если имя определено, то значит и все остальные параметры будут определены
			if($f['name']==''){_die('не получилось определить новое название для изображения «'.$original_file_name.'»');}
			//сохраняем файл
			//_print_r($files);_echo($files['tmp_name'][$this->db_column]);_echo($_SERVER['DOCUMENT_ROOT'].$this->path.$f['name']);
			if( !copy($files['tmp_name'][$this->db_column],$_SERVER['DOCUMENT_ROOT'].$this->path.$f['name']) ){_die('не могу сохранить изображение, возможно из-за ограничения по размеру');}
			//определяем его параметры
			$f['uri']=$this->path.$f['name'];
			$f['size']=filesize($_SERVER['DOCUMENT_ROOT'].$f['uri']);
			list($f['width'],$f['height'])=getimagesize($_SERVER['DOCUMENT_ROOT'].$f['uri']);
			$f['upload_date']=date('Y-m-d');
			//формируем фрагмент запроса
			$result='';
			foreach($this->subfields as $key=>$value){
				$result.='`'.$this->model_name.'`.`'.$this->db_column.'_'.$key.'` = "'.mysql_escape_string($f[$key]).'",';
			}
			//удаляем старый файл, если был
			//делаем это в самом конце, чтобы не повлиять на формирование нового названия файла
			//для того, чтобы оно совпало с названием, сформированным в getModelItemInitValue()
			if($post[$this->db_column.'_bak']!=''){
				$this->_unlinkOldFile($post[$this->db_column.'_bak']);
			}
		}else{
			//возможно, что старый файл пожелали удалить
			if($post[$this->db_column.'_del']=='on'){
				//удаляем файл с сервера
				$this->_unlinkOldFile($post[$this->db_column.'_bak']);
				//формируем фрагмент запроса
				$result='';
				foreach($this->subfields as $key=>$value){
					$result.='`'.$this->model_name.'`.`'.$this->db_column.'_'.$key.'` = "",';
				}
			}
		}
		return $result;
	}*/

	function getFormFieldHTMLWrap($input_tag,$error_arr){
		if($this->help_text==''){
			$obj_model=gmo($this->model_name);
			$this->help_text=''.$obj_model->_expectedImageSizes($this->sizes);
		}
		$result=parent::getFormFieldHTMLWrap($input_tag,$error_arr);

		return $result;
	}

	//-----------------------------------------------------------------

	// используется в model.class.php
	function _checkMatching($_relkey=''){
		$result=true;
		//определяем $match: если он не задан специально, то для ImageField мы определяем
		//следующее правило - /\.(jpg|jpeg|gif|png)$/i
		$match=defvar('/\.flv$/i',$this->match);
		if($_relkey!=''){
			$files=$_FILES[$_relkey];
		}else{
			$files=$_FILES[$this->model_name];
		}
		//проверяем передавался ли файл
		if(!empty($files)){
			$original_file_name=$files['name'][$this->db_column];
			if($original_file_name!=''){
				$result=(bool)preg_match($match,$original_file_name);
			}
		}

		return $result;
	}
}


?>