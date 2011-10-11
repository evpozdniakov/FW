<?php

$tmpFormItems=new FormItems();

// hour
if($hour==''){
	$hour=date('H');
}
for($i=0;$i<=23;$i++){
	$i=($i<10)?'0'.$i:$i;
	$props=($i==$hour)?'selected':'';
	$hoursSBoxOptions.=$tmpFormItems->option1($i,$i,$props);
}
$hoursSBox=$tmpFormItems->sbox($prefix.'hour',$hoursSBoxOptions,'style="width:50px;"');
// hour end

// minute
if($minute==''){
	$minute=date('i');
}
for($i=0;$i<=50;$i+=10){
	$i=($i<10)?'0'.$i:$i;
	$props=($i==$minute)?'selected':'';
	$minutesSBoxOptions.=$tmpFormItems->option1($i,$i,$props);
}
$minutesSBox=$tmpFormItems->sbox($prefix.'minute',$minutesSBoxOptions,'style="width:50px;"');
// minute end

/*
// minute
for($i=0;$i<=11;$i++){
	$j=$i*5;
	$j=($j<10)?'0'.$j:$j;
	$props=($j==$minute)?'selected':'';
	$minutesSBoxOptions.=$tmpFormItems->option1($j,$j,$props);
}
$minutesSBox=$tmpFormItems->sbox($prefix.'minute',$minutesSBoxOptions,'style="width:50px;"');
// minute end
*/

$result='
	'.$hoursSBox.'.'.$minutesSBox.'
';

?>
