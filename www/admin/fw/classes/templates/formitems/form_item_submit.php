<?php

$icon = (mb_strpos($props,'icon')===false) ? '' : '<span class="icon"></span>';
$result="<button {$props} type=\"submit\" name=\"{$name}\" value=\"{$value}\">{$icon}{$value}</button>";
