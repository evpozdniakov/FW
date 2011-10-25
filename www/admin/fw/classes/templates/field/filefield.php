<?php

$form_items=new FormItems();

$field_name_modified=mb_substr($this->field_name,0,-1);

if($inputValue['uri']!=''){
	$file_info_html='
		<span class="fileInfo">
			<a href="'.DOMAIN_PATH.$inputValue['uri'].'">'.$inputValue['name'].'</a> 
			<small>('.$inputValue['ext'].'-файл, '.$inputValue['size'].', загружен '.$inputValue['upload_date'].')</small>
			'
			.$form_items->hidden($field_name_modified.'_bak]',$inputValue['uri'])
			.'
		</span>
	';
	if($this->blank){
		$file_info_html.='
			<span class="fileUnlink">
				'
				.$form_items->chbox($field_name_modified.'_del]','удалить файл')
				.'
			</span>
		';
	}
}

$result=$file_info_html.$form_items->file($this->field_name);
