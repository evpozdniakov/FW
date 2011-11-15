<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		{render model="structure" view="titleMetaTags"}
		{addCss file_root_path='/media/css/defaults.css'}
		{addCss file_root_path='/media/css/style.css'}
		{addCss file_root_path='/media/css/print.css' media='print'}
		{outCss}
		{addJs file_root_path='/media/js/jquery.min.js' compress=false}
		{addJs file_root_path='/media/js/client.js'}
		{outJs}
	</head>
	{block name="body"}{/block}
</html>
