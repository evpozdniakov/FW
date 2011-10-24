<?php

$id=$name.$value.'_id';
$id=str_replace('[','_',$id);
$id=str_replace(']','_',$id);

$result='<input type="checkbox" name="'.$name.'" value="'.$value.'" '.$checked.' '.$props.' '.$title.' id="'.$id.'" class="'.($is_obligfield?'obligfield':'').'">';
if($text!=''){
	$result.=' <label for="'.$id.'">'.$text.'</label> ';
}

$result='<span class="tag chbox">'.$result.'</span>';