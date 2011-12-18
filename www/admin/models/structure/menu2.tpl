{if count($data)}
	<ul class="submenu">
		{foreach from=$data item="item"}
			{capture assign="t"}{$item.title}{/capture}
			{capture assign="u"}{$url_prefix}{$item.url}/{/capture}
			{capture assign="l"}<li><a href="{$u}">{$t}</a></li>{/capture}
			{capture assign="b"}<b>{$t}</b>{/capture}
			{capture assign="a"}<li><a href="{$u}"><b>{$t}</b></a></li>{/capture}
			{ulba u=$u l=$l b=$b a=$a}
		{/foreach}
	</ul>
{/if}
