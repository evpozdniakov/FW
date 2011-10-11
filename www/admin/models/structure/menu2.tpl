{if count($menu2_arr)}
	<ul class="submenu">
		{foreach from=$menu2_arr item="menu2_item" name="sfm2"}
			{capture assign="t"}{if $menu2_item.short}{$menu2_item.short}{else}{$menu2_item.title}{/if}{/capture}
			{capture assign="u"}{$smarty.const.DOMAIN_PATH}{$menu2_prefix}{$menu2_item.url}/{/capture}
			{capture assign="l"}<li><a class="menu{$menu2_item.id}" href="{$u}">{$t}</a></li>{/capture}
			{capture assign="b"}<li class="active"><b>{$t}</b></li>{/capture}
			{capture assign="a"}<li><a class="menu{$menu2_item.id}" href="{$u}"><b>{$t}</b></a></li>{/capture}
			{ulba u=$u l=$l b=$b a=$a}
		{/foreach}
	</ul>
{/if}
