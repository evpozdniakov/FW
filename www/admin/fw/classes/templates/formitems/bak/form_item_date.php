<?php

$form_items=new FormItems();

// months
$arrMoths=explode(',','January,February,March,April,May,June,July,August,September,October,November,December');
if($month==''){
	$month=date('n');
}
foreach($arrMoths as $key=>$value){
	$number=$key+1;
	$props=($number==$month)?'selected':'';
	$monthsSBoxOptions.=$form_items->option1($value,$number,$props);
}
$monthsSBox=$form_items->sbox($prefix.'month',$monthsSBoxOptions,'class="formItemMonth"');
// months end

// days
if($day==''){
	$day=date('j');
}
for($i=1;$i<=31;$i++){
	$props=($i==$day)?'selected':'';
	$daysSBoxOptions.=$form_items->option1($i,$i,$props);
}
$daysSBox=$form_items->sbox($prefix.'day',$daysSBoxOptions,'class="formItemDay"');
// days end

$result='
	'.$monthsSBox.' '.$daysSBox.'
';

?>
