<?php

$result='<input type="radio" name="'.$name.'" value="'.$value.'" '.$checked.' '.$props.' '.$title.' id="'.$name.$value.'_id">';
if($text!=''){
	$result.=' <label for="'.$name.$value.'_id">'.$text.'</label>';
}

$result='<span class="tag radio">'.$result.'</span>';