{extends file="doctype.tpl"}

{block name="body"}
	<body {body_classname}>
		<div id="mainBox">
			{include file='header.tpl'}
			{block name="content"}{$__CONTENT__}{/block}
		</div>
		{include file='footer.tpl'}
	</body>
{/block}
