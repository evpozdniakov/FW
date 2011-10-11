{if $menu12_arr}
	<ul class="l1">
		{foreach from=$menu12_arr item="menu1" name="sfm1"}
			{capture name="children"}
				{if $menu1.children}
					<div id="menu{$menu1.id}" class="l2">
						<ul>
							{foreach from=$menu1.children item="menu2" name="sfm2"}
								{if $smarty.foreach.sfm2.last}
									{assign var="cls" value="l2 last"}
								{elseif $smarty.foreach.sfm2.first}
									{assign var="cls" value="l2 first"}
								{else}
									{assign var="cls" value="l2"}
								{/if}
								{if !empty($menu2.cls)}
									{capture assign="cls"}{$cls} {$menu2.cls}{/capture}
								{/if}
								{capture name="u"}{$smarty.const.DOMAIN_PATH}{$menu2.url}{/capture}
								{capture name="l"}<li class="{$cls}"><a class="l2" id="m2-{$menu2.id}" href="{$smarty.capture.u}"><span>{$menu2.txt}</span></a></li>{/capture}
								{capture name="b"}<li class="{$cls} active"><b class="l2" id="m2-{$menu2.id}"><span>{$menu2.txt}</span></b></li>{/capture}
								{capture name="a"}<li class="{$cls} active"><a class="l2" id="m2-{$menu2.id}" href="{$smarty.capture.u}"><b><span>{$menu2.txt}</span></b></a></li>{/capture}
								{ulba u=$u l=$l b=$b a=$a}
							{/foreach}
						</ul>
					</div>
				{/if}
			{/capture}

			{if $smarty.foreach.sfm1.first}
				{assign var="cls" value="first"}
			{elseif $smarty.foreach.sfm1.last}
				{assign var="cls" value="last"}
			{else}
				{assign var="cls" value=""}
			{/if}
			{ulba u=$u l=$l b=$b a=$a}
			{capture name="u"}{$smarty.const.DOMAIN_PATH}{$menu1.url}{/capture}
			{capture name="l"}<li class="l1 {$cls}"><a class="l1" id="m1-{$menu1.id}" href="{$smarty.capture.u}"><span>{$menu1.txt}</span></a>{$smarty.capture.children}</li>{/capture}
			{capture name="b"}<li class="l1 active {$cls}"><b class="l1" id="m1-{$menu1.id}"><span>{$menu1.txt}</span></b>{$smarty.capture.children}</li>{/capture}
			{capture name="a"}<li class="l1 active {$cls}"><a class="l1" id="m1-{$menu1.id}" href="{$smarty.capture.u}"><b><span>{$menu1.txt}</span></b></a>{$smarty.capture.children}</li>{/capture}
			{ulba u=$u l=$l b=$b a=$a}
		{/foreach}
	</ul>
{/if}
