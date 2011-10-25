<?php

if(mb_strpos($props,'class')===false){
	$class='class="formItemSbmt"';
}

$result='<input type="submit" name="'.$name.'" value="'.$value.'" '.$props.' '.$class.'>';


