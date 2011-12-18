{*<ul class="expanded">
	<li><i>Автострахование</i></li>
	<li><a href="#">ОСАГО</a></li>
	<li><b>КАСКО</b></li>
	<li><a href="#">КАСКО - лайт</a></li>
	<li><a href="#">КАСКО +</a></li>
	<li><a href="#">Купить полис</a></li>
</ul>
<ul>
	<li><i>Туристам</i></li>
	<li><a href="#">Медицинское страхование выезжающих за рубеж</a></li>
	<li><a href="#">Страхование от невыезда</a></li>
	<li><a href="#">Купить полис</a></li>
</ul>
<ul>
	<li><i>Жизнь и здоровье</i></li>
	<li><a href="#">Добровольное медстрахование</a></li>
	<li><a href="#">Страхование от несчастных случаев</a></li>
	<li><a href="#">Страхование жизни</a></li>
	<li><a href="#">Оставить заявку</a></li>
</ul>
<ul>
	<li>Имущество</li>
	<li><a href="#">Дом или дача</a></li>
	<li><a href="#">Квартира</a></li>
	<li><a href="#">Личные вещи</a></li>
	<li><a href="#">Оставить заявку</a></li>
</ul>*}

{function name="submenuRec" level=0 draw_ul=true}
	{if !empty($data)}
		{foreach from=$data item="item"}
			{capture assign="cls"}{strip}
				{if $level==0}
					{ulba u=$item.url l='' b='expanded' a='expanded'}
				{/if}
			{/strip}{/capture}
			{if $item.is_groupper=='yes'}
				<ul {if !empty($cls)} class="{$cls}" {/if}>
					<li><i>{$item.title}</i></li>
					{submenuRec data=$item.children level=$level+1 draw_ul=false}
				</ul>
			{else}
				{if $draw_ul}<ul {if !empty($cls)} class="{$cls}" {/if}>{/if}
					<li>
						{capture assign="t"}{$item.title}{/capture}
						{capture assign="u"}{$item.url}{/capture}
						{capture assign="l"}<a {if $level==0 && !empty($item.children)} class="extended" {/if} href="{$u}">{$t}</a>{/capture}
						{capture assign="b"}<b {if $level==0 && !empty($item.children)} class="extended" {/if}>{$t}</b>{/capture}
						{capture assign="a"}<a {if $level==0 && !empty($item.children)} class="active extended" {/if} href="{$u}"><b>{$t}</b></a>{/capture}
						{ulba u=$u l=$l b=$b a=$a}
						{submenuRec data=$item.children level=$level+1}
					</li>
				{if $draw_ul}</ul>{/if}
			{/if}
		{/foreach}
	{/if}
{/function}

{submenuRec data=$submenu}