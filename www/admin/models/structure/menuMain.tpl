{if !empty($menu)}
	<ul class="fixW">
		{foreach from=$menu item="item"}
			<li>
				{capture assign="extended"}{if !empty($item['children'])}extended{/if}{/capture}
				{capture assign="active"}{ulba u=$item.url l='' b='active' a='active'}{/capture}
				<a class="{$extended} {$active}" href="{$item.url}">{$item.title}</a>
				{if !empty($item['children'])}
					<div class="submenu">
						<div class="fixW">
							{foreach from=$item['children'] item="pair"}
								<ul>
									{foreach from=$pair item="subitem"}
										<li>
											{if $subitem.is_groupper=='yes'}
												<i>{$subitem.title}</i>
											{else}
												{capture assign="t"}{$subitem.title}{/capture}
												{capture assign="u"}{$subitem.url}{/capture}
												{capture assign="l"}<a href="{$subitem.url}">{$subitem.title}</a>{/capture}
												{capture assign="b"}<b>{$subitem.title}</b>{/capture}
												{capture assign="a"}<a href="{$subitem.url}"><b>{$subitem.title}</b></a>{/capture}
												{ulba u=$u l=$l b=$b a=$a}
											{/if}
										</li>
									{/foreach}
								</ul>
							{/foreach}
						</div>
					</div>
				{/if}
			</li>
		{/foreach}
	</ul>
{/if}

{*<ul class="fixW">
	<li>
		<a class="extended active" href="#">Частным лицам</a>
		<div class="submenu">
			<div class="fixW">
				<ul>
					<li><i>Автострахование</i></li>
					<li><a href="#">ОСАГО</a></li>
					<li><a href="#">КАСКО</a></li>
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
				</ul>
			</div>
		</div>
	</li>
	<li>
		<a class="extended" href="#">Корпоративным клиентам</a>
	</li>
	<li>
		<a href="#">Акции и скидки</a>
	</li>
	<li>
		<a href="#">Вопросы и ответы</a>
	</li>
	<li>
		<a href="#">Контакты</a>
	</li>
</ul>*}
