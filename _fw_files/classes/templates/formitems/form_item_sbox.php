<?php

//здесь нельзя прописывать class="formItemSbox", 
//потому что в админке при этом боксы даты и времени 
//становятся слишком широкими
$result='
	<select name="'.$name.'" '.$props.' '.$title.'>'
		.$options
	.'</select>	
';

$result='<span class="tag sbox">'.$result.'</span>';
