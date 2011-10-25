<?php

$form_items=new FormItems();

$field_name_modified=mb_substr($this->field_name,0,-1);

if( !empty($inputValue['uri']) ){
	$width=($inputValue['width']<=150)?$inputValue['width']:150;
	if($inputValue['ext']=='swf'){
		if( !defined('USE_SWFOBJECT') ){
			define('USE_SWFOBJECT',true);
		}
		$image__flashbox='<span id="'.$this->db_column.'FlashBox">&nbsp;</span><br>';
		$flash_init_script='
			<script type="text/javascript">
			<!--
				swfobject.embedSWF("'.DOMAIN_PATH.$inputValue['uri'].'", "'.$this->db_column.'FlashBox", "'.$inputValue['width'].'", "'.$inputValue['height'].'", "8.0.0","",{}, {},{});
			//-->
			</script>
		';
	}else{
		if( !defined('USE_SHADOWBOX') ){
			define('USE_SHADOWBOX',true);
		}
		$image__flashbox='<a href="'.DOMAIN_PATH.$inputValue['uri'].'" rel="shadowbox"><img src="'.$inputValue['uri'].'" width="'.$width.'" style="border:2px solid #F98315;" alt=""><br></a>';
		$flash_init_script='';
	}
	$file_info_html='
		<span class="fileInfo">
			'.$image__flashbox.'
			<small>('.$inputValue['ext'].'-файл, '.$inputValue['width'].'x'.$inputValue['height'].')</small>
			'
			.$form_items->hidden($field_name_modified.'_bak]',$inputValue['uri'])
			.'
		</span>
	';
	$file_info_html.=$flash_init_script;
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

$result=$file_info_html.$form_items->file($this->field_name,$inputValue);
	
?>
