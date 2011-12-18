{if count($gradusnik)>0}
	<nav id="gradusnik">
		<h5>Навигация до текущей страницы</h5>
		<p>
			<small>
				{foreach from=$gradusnik item="item" name="sfgd"}
					{if $smarty.foreach.sfgd.iteration < count($gradusnik)}
						<a href="{$item.url}">{$item.txt}</a> &mdash;
					{else}
						<b>{$item.txt}</b>
					{/if}
				{/foreach}
			</small>
		</p>
	</nav>
{/if}
