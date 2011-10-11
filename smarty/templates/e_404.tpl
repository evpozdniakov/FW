{extends file="p_body.tpl"}

{block name="content"}
	<div id="contentBox" class="fixW">
		<div class="pad">
			<h1>Ошибка 404</h1>
			<p>Страница не найдена. Возможно, эта страница была удалена, перемещена, или она временно недоступна.</p>
			<p>Попробуйте следующее.</p>
			<ul id="tryList">
				<li>Проверте, правильно ли вы набрали адрес страницы в адресной строке браузера.<br></li>
				{* <li>Воспользуйтесь <a href="/sitemap/">картой сайта</a>.</li> *}
				<li>Откройте <a href="{$smarty.const.DOMAIN_PATH}/">главную страницу</a>, а затем найдите там ссылки на интересующую вас тему.<br></li>
			</ul>
			<script type="text/javascript">
			<!--
			if(history.length>0)
				$('#tryList').append($(document.createElement('li')).
					html('Нажмите кнопку <a href="javascript:history.go(-1)">Назад<\/a>, чтобы вернутся на предыдущую страницу.'));
			//-->
			</script>
		</div>
	</div>
{/block}
