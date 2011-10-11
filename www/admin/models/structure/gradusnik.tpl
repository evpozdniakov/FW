{if count($gradusnik)>0}
	{foreach from=$gradusnik item="item" name="sfgd"}
		{if $item.url=='/map/battle/' || $item.url=='/map/memorial/' || $item.url=='/map/search/'}
			{assign var="back_url" value="/map/"}
		{else}
			{if $smarty.foreach.sfgd.iteration < count($gradusnik)}
				<a href="{$item.url}">{$item.txt}</a>
				<span class="div">|</span>
			{else}
				<b>{$item.txt}</b>
			{/if}
		
			{if $smarty.foreach.sfgd.iteration + 1 == count($gradusnik)}
				{assign var="back_url" value=$item.url}
			{/if}
		{/if}
	{/foreach}
	
	{if $smarty.const.DOMAIN=='ru'}
		{assign var="t" value="Вернуться"}
	{else}
		{assign var="t" value="Back"}
	{/if}
{/if}
