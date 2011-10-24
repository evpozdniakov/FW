<?php

if(mb_strpos($props,'class')===false){
	$class='class="buttonFormItem"';
}

$result='<input type="button" name="'.$name.'" value="'.$value.'" '.$props.'>';


