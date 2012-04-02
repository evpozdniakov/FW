<?php

$local=new ModelItemFormHTMLClient($this,$acc_fields);
$form_items=new FormItems();
$result=$local->getFormFields($model_item_data,$errors_arr);
