<?php

$star=($status==1)?$this->star():'';
if($help_text!=''){$help_text='<span class="help_text">('.$help_text.')</span>';}
if($wrap_cls!=''){$wrap_cls='class="'.$wrap_cls.'"';}
if($extra_text!=''){$extra_text='<em>'.$extra_cls.'<br></em>';}
if($title!=''){$title='<b>'.$title.'</b>: '.$star.' '.$help_text.'<br>';}

$result='
	<p '.$wrap_cls.'>
		'.$extra_text.'
		<span class="title">'.$title.'</span>
		'.$input_tag.'
	</p>
';
