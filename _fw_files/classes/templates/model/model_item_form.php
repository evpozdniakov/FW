<?php

$local=new ModelItemFormHTML($this);
$form_items=new FormItems();

$result='
	<h1>'.$this->__txt_name__.$local->_getAddLink($model_item_data['id']).'</h1>
	<form id="model_'.$this->__name__.'_form" name="model_'.$this->__name__.'_form" action="'.DOMAIN_PATH.$_SERVER['REDIRECT_URL'].'edit/" method="post" enctype="multipart/form-data" accept-charset="'.SITE_ENCODING.'" '.$local->getOnsubmit().'>
		<div>
		'
		.$local->alert_message($errors_report)
		.$form_items->hidden('send','yes')
		.$form_items->hidden('tf',p2v('tf'))
		.$local->getFormFields($model_item_data,$errors_arr)
		.$this->__core__
		.'<div id="controls">'
			.$form_items->untitled(
				$local->controls($model_item_data)
			)
		.'</div>
		</div>
	</form>
	'.
	$local->getJsFunctions()
	.'
';

