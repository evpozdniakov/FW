{if $menu1_arr}
	<ul>
		{foreach from=$menu1_arr item="menu_item" }
			{assign var="u" value=$menu_item.url}
			{capture assign="l"}<li class="id{$menu_item.id}"><a href="{$u}"><span>{$menu_item.title}</span></a></li>{/capture}
			{capture assign="b"}<li class="id{$menu_item.id} active"><b><span>{$menu_item.title}</span></b></li>{/capture}
			{capture assign="a"}<li class="id{$menu_item.id} active"><b><a href="{$u}"><span>{$menu_item.title}</span></a></b></li>{/capture}
			{ulba u=$u l=$l b=$b a=$a}
		{/foreach}
	</ul>
{/if}
