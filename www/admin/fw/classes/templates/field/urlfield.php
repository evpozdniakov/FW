<?php

$form_items=new FormItems();

$result=$form_items->text($this->field_name, $inputValue, $this->maxlength, ' onblur="$(this).evCorrectURL()"');

?>
