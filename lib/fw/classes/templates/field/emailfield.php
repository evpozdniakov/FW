<?php

$form_items=new FormItems();

$onblur=($GLOBALS['path'][1]=='admin')?'onblur="if(this.value && !$.evValidateEmail(this)){alert(\'введен некорректный E-mail\')}"':'';

$result=$form_items->text($this->field_name, $inputValue, $this->maxlength, $onblur);

