<?php

if(mb_strpos($props,'class')===false){
	$class='class="buttonFormItem"';
}

$result="<button {$props} type=\"button\" name=\"{$name}\" value=\"{$value}\"><span class=\"icon\"></span>{$value}</button>";
