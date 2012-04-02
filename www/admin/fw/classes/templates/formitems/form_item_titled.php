<?php

$star = !$status ? '' : $this->star();

$help_text = empty($help_text) ? '' : '<span class="help_text">'.$extra_cls.'</span>';

$extra_text = empty($extra_text) ? '' : "<em>{$extra_cls}<br></em>";

$title_star_help = empty($title) ? '' : "<b>{$title}</b>: {$star} {$help_text}<br>";


$result='
	<p class="field titled '.$wrap_cls.'">
		'.$extra_text.'
		<span class="title">'.$title_star_help.'</span>
		'.$input_tag.'
	</p>
';
