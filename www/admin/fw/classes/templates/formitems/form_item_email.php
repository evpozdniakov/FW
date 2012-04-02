<?php

$result='<input type="email" name="'.$name.'" value="'.htmlspecialchars($value).'" maxlength="'.$max.'" '.$props.' '.$title.' class="'.($is_obligfield?' obligfield':'').'">';

$result='<span class="tag text email">'.$result.'</span>';