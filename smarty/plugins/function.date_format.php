<?php

/**
 * получаем два параметра: $params['d'] и $params['f']
 * $params['d'] - дата в формате mysql
 * $params['f'] - нужный формат
 * в формате используется стандартный php strftime-синтаксис:
 * http://ru.php.net/strftime
 * с одним дополнением: появляются сокращения для русских месяцев:
 * %monthi - месяц в именительном падеже
 * %monthr - месяц в родительном падеже
 */
function smarty_function_date_format($params, $template){
	$date=$params['d'];
	$date=parseDate($date,'object');
	$format=$params['f'];
	$m=date('m',$date);
	$monthi=month($m,'i');
	$monthr=month($m,'r');
	$format=_mb_str_replace('%monthi',$monthi,$format);
	
	$format=_mb_str_replace('%monthr',$monthr,$format);
	echo strftime($format,$date);
}