{extends file="p_body.tpl"}

{block name="content"}
	<div id="contents" class="fixW">
		<div class="col colL">
			<div class="pad contentZone">
				{render model="structure" view="pageTitle"}
				{$__CONTENT__}
			</div>
			</div>
		<div class="col colR">
			<div class="pad">
				<p><small>правая колонка</small></p>
			</div>
		</div>
		<div class="clear"></div>
	</div>
{/block}