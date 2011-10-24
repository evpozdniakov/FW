<?php

$form_items=new FormItems();

$choices=explode(',',$this->choices);

$null_text=($choices[2])?'&nbsp; '.$choices[2]:'выберите из списка:';
$options=$form_items->option( $null_text ,'', (($inputValue=='')?'yes':'no'));
$options.=$form_items->option('&nbsp; '.$choices[0], 'yes', (($inputValue=='yes')?'yes':'no'));
$options.=$form_items->option('&nbsp; '.$choices[1], 'no', (($inputValue=='no')?'yes':'no'));

$result=$form_items->sbox($this->field_name, $options, 'class="formItemSbox"');
