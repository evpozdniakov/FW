<?php

$star=($status==1)?$this->star():'';
if($extra_class!=''){$extra_class='class="'.$extra_class.'"';}
if($extra_text!=''){$extra_text='<em>'.$extra_text.'<br></em>';}

$result='
	<td class="formItemCore">
		<span '.$extra_class.'>
			'.$extra_text.'
			'.$body.'
		</span>
	</td>
';


