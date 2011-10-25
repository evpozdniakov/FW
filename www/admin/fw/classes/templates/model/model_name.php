<?php

if($this->userCanAddModelItems()){
	$add_link='<a id="addNewModelElementLink" href="'.DOMAIN_PATH.'/admin/'.$this->__name__.'/edit/">добавить</a>';
}

$result='
	<h1>'.$this->__txt_name__.$add_link.'</h1>
';

?>