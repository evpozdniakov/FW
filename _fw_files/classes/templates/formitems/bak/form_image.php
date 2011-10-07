<?php

if($w>0 && $h>0){
	if($delname){
		$align='align="absmiddle"';
		$delbox='<input type="checkbox" name="'.$delname.'" value="on" id="'.$delname.'_id"> <label for="'.$delname.'_id">delete</label>';
	}
	$result='<img src="'.$src.'" width="'.$w.'" height="'.$h.'" border="1" vspace="3" alt="" '.$align.'>'.$delbox.'<br clear="all">';
}

?>
