<header class="fixW">
	<div class="pad">
		<h4>
			{if $smarty.const.IS_FIRST===true}
				{$structure_data[0].title}
			{else}
				<a href="/">{$structure_data[0].title}</a>
			{/if}
		</h4>
	</div>
</header>
