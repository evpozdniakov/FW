<?php

$num=0;
foreach($domains as $domain){
	$num++;
	$u=sprintf('http://%s/admin/', getSiteUrl($domain));
	$domain=strtoupper($domain);
	$result.='
		<strong>
			'.ulba(
				$u,
				'<a href="'.$u.'">'.$domain.'</a>',
				'<b>'.$domain.'</b>',
				'<a href="'.$u.'"><b>'.$domain.'</b></a>
			').'
		</strong>
	';
}
$result.='<div class="clear"></div>';
