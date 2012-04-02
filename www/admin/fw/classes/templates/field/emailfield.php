<?php

$form_items=new FormItems();

$onblur=( IS_ADMIN===true )?'onblur="if(this.value && !$.evValidateEmail(this)){alert(\'введен некорректный E-mail\')}"':'';

$result=$form_items->text($this->field_name, $inputValue, $this->maxlength, $onblur);

