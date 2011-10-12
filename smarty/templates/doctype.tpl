<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		{render model="structure" view="titleMetaTags"}
		{$css_files=[
			'/media/css/defaults.css'
			,'/media/css/style.css'
		]}
		{compress css_files=$css_files}
	</head>
	{block name="body"}{/block}
</html>
