<?php

$captImg = '<img src="/captcha/'. session_name(). '='. session_id(). '"><br/>';
$result= $captImg. '<input name="'.$name.'" value="'.htmlspecialchars($value).'" maxlength="'.$max.'" '.$props.' '.$title.' class="formItemCapcha obligfield">';


