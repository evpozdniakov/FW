<?php

foreach($model_item_data as $item){
	$result.='<b>'.$item['txt_name'].'</b>: '.defvar('<i>без ответа</i>',$item['value']).'<br>';
}

?>