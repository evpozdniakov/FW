{if !empty($extra)}
	<nav>
		<h5>Дополнительные разделы сайта</h5>
		<ul>
			{foreach from=$extra item="item"}
				<li><a href="{$item.url}">{$item.title}</a></li>
			{/foreach}
		</ul>
	</nav>
	<hr>
{/if}
