<?php

$maxlength='';
if(intval($max)>0){
	$maxlength=sprintf('maxlength="%d"',$max);
}
$obligfield='';
if($is_obligfield){
	$obligfield='obligfield';
}
$result=sprintf('<span class="tag text"><input type="text" name="%s" value="%s" %s %s %s class="formItemText %s"></span>', $name, e5c($value), $maxlength, $props, $title, $obligfield);
