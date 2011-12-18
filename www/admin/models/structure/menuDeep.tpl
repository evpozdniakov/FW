{function name="menuDeepRec" level=0}
	{if !empty($data)}
		{foreach from=$data item="item"}
			<ul>
				<li>
					{capture assign="t"}{$item.title}{/capture}
					{capture assign="u"}{$item.url}{/capture}
					{capture assign="l"}<a {if $level==0 && !empty($item.children)} class="extended" {/if} href="{$u}">{$t}</a>{/capture}
					{capture assign="b"}<b {if $level==0 && !empty($item.children)} class="extended" {/if}>{$t}</b>{/capture}
					{capture assign="a"}<a {if $level==0 && !empty($item.children)} class="active extended" {/if} href="{$u}"><b>{$t}</b></a>{/capture}
					{ulba u=$u l=$l b=$b a=$a}
					{menuDeepRec data=$item.children level=$level+1}
				</li>
			</ul>
		{/foreach}
	{/if}
{/function}

{if !empty($menu_deep)}
	{menuDeepRec data=$menu_deep}
{/if}
