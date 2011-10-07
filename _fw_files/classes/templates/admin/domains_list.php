<?php

$num=0;
if(USE_SUBDOMAINS===true){
	$postfix=removeSubdomain();
}
foreach($domains as $domain){
	$num++;
	if(USE_SUBDOMAINS===true){
		if(HIDE_DEFAULT_SUBDOMAIN===true && $domain==DEFAULT_SUBDOMAIN){
			$u='http://'.$postfix.'/admin/';
		}else{
			$u='http://'.$domain.'.'.$postfix.'/admin/';
		}
	}elseif(USE_MULTIDOMAINS===true){
		$u='http://'.$domain.'/admin/';
	}else{
		$prefix=($num==1)?'':'/~'.$domain.'~';
		$u=$prefix.'/admin/';
	}
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
