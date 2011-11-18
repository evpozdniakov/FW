<?php

// получаем содержимое файла functions.php
$functions=file_get_contents(FW_DIR.'/functions.php');

// убираем декларацию php
$functions=str_replace('<?php','',$functions);
$functions=str_replace('<?','',$functions);
$functions=str_replace('?>','',$functions);

// проверяем, имеется ли декларация php или ее завершение
if( mb_strpos($functions, '<?php')!==false ){
	throw new Exception('cannot remove php declaration from functions.php', 1001);
}elseif( mb_strpos($functions, '?>')!==false ){
	throw new Exception('cannot remove end of php declaration from functions.php', 1002);
}

eval('

	class Functions{

		'.$functions.'

	}

');
