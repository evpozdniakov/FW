<?php

$result='<textarea name="'.$name.'" rows="'.$rows.'" cols="10" '.$props.' '.$title.' class="'.($is_obligfield?'obligfield':'').'">'.htmlspecialchars($value).'</textarea>';

$result='<span class="tag area">'.$result.'</span>';