<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		{render model="structure" view="titleMetaTags"}
		{$css_files=[
			 '/media/css/defaults.css'
			,'/media/css/style.css'
		]}
		{$js_files=[
			 '/media/js/jquery.min.js'
			,'/media/js/jquery.form.js'
			,'/media/js/client.js'
			,'/media/js/cfg.js'
		]}
		{$js_files[]='/media/lib/jquery-ui/js/jquery-ui-1.8.16.custom.min.js'}
		{$js_files[]='/media/lib/jquery-ui/js/jquery.ui.datepicker-ru.js'}
		{if $smarty.const.USE_JCROP===true}
			{$css_files[]='/media/lib/tapmodo-Jcrop-5e58bc9/css/jquery.Jcrop.css'}
			{$js_files[]='/media/lib/tapmodo-Jcrop-5e58bc9/js/jquery.Jcrop.min.js'}
		{/if}
		{compress css_files=$css_files js_files=$js_files}
		{if $smarty.const.USE_YAMAP===true && $smarty.const.DISABLE_YAMAP!==true}
			<script type="text/javascript" charset="utf-8" src="http://api-maps.yandex.ru/1.1/index.xml?loadByRequire=1&amp;key={$smarty.const.MAPS_YANDEX_API_KEY}"></script>
		{/if}
		<!--[if lt IE 9]>
			<script src="/media/js/html5.js"></script>
			<script src="/media/js/innershiv.js"></script>
		<![endif]-->
		<script type="text/javascript"><!--
			{if $smarty.session.system_message}
				$().ready(function(){
					C.utils.systemMessage({ html:'<p>{$smarty.session.system_message|escape:"javascript"}</p>' });
				})
				{unset_session name="system_message"}
			{/if}
			var MEETINGS_START_COUNT={$smarty.const.MEETINGS_START_COUNT};
			var MEETINGS_MORE_COUNT={$smarty.const.MEETINGS_MORE_COUNT};
		//--></script>
		
    <script type="text/javascript" src="http://userapi.com/js/api/openapi.js?34"></script>
    <script type="text/javascript" src="http://connect.facebook.net/ru_RU/all.js"></script>
    <script type="text/javascript" src="http://cdn.connect.mail.ru/js/loader.js"></script>

	<script type="text/javascript" src="http://userapi.com/js/api/openapi.js?34"></script>

	<script type="text/javascript">
			VK.init( { apiId: 2625660, onlyWidgets: true } );
	</script>
    
    <script type="text/javascript" src="https://apis.google.com/js/plusone.js">
  		{ lang: 'ru' }
	</script>

    
    
	</head>
	{block name="body"}default body block{/block}
</html>
