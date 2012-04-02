<?php

$num=0;
foreach($domains as $domain){
	$num++;
	$u=sprintf('http://%s/admin/', getSiteUrl($domain['url']));
	$t=$domain['title'];
	$cls = ($domain['url']==DOMAIN) ? 'active' : '';
	$result.='
		<span class="'.$cls.'">
			'.ulba(
				$u,
				'<a href="'.$u.'">'.$t.'</a>',
				'<b>'.$t.'</b>',
				'<a href="'.$u.'"><b>'.$t.'</b></a>
			').'
		</span>
	';
}

