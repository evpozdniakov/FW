<?php

include($_SERVER['DOCUMENT_ROOT'].'/admin/fw/classes/fw.class.php');
$fw=new FW();
$fw::init();
// exit(); 
// здесь нельзя делать exit(), потому что иначе не получится включить init.php в начало другого файла
// это бывает нужно тогда, когда php-файл находится в специальной папке __<имя папки> и не может быть запущен 
// а также может быть включен флаг DONT_INCLUDE_CALLEE, что тоже запрещает вызов php-скрипта из init.php

