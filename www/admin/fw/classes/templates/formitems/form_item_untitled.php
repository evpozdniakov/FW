<?php

$star=($status==1)?$this->star():'';
if($help_text!=''){$help_text='('.$help_text.')';}
if($wrap_cls!=''){$wrap_cls='class="'.$wrap_cls.'"';}

$result='
	<p class="field untitled '.$wrap_cls.'">
		'.$input_tag.' '.$star.' '.$help_text.'<br>
	</p>
';
