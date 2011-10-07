<?php

$local=new ModelItemFormHTMLClient($this,$acc_fields);
$form_items=new FormItems();

$result='
		'
		/* скрытое поле не всегда обычно добавляется вручную 
		.$form_items->hidden('send','yes')*/
		.$local->getFormFields($model_item_data,$errors_arr)
		.'
';

?>