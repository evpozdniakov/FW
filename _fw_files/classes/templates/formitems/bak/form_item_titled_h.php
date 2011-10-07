<?php

$star=($status==1)?$this->star():'';
if($rem!=''){$rem='('.$rem.')';}

$result='
	<div class="formItemTitledH">
		<div class="formItemTitledTitle" style="width:'.$w.'px;">'.$star.'<b>'.$title.'</b>: '.$rem.'</div>
		<div class="formItemTitledField">'.$body.'</div>
		<br clear="all">
	</div>
';

/*
$result='
	<dl style="margin:10px 0px;">
	<dt style="font-size:12px;margin-left:20px;"><b>'.$title.'</b> '.$star.' '.$rem.'</dt>
	<dd style="margin:0px">'.$body.'</dd>
	</dl>
';

$result='
	<p style="margin:0px 0px;"><b style="font-size:12px;">'.$title.'</b> '.$star.' '.$rem.'</p>
	<blockquote>'.$body.'</blockquote>
';

$result='
	<p style="margin:0px;">
		<b style="font-size:12px;">'.$title.'</b> '.$star.' '.$rem.'<br>
		'.$body.'<br>
	</p>
';

*/

