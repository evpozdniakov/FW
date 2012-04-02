<?php

$result=sprintf('
	<div style="position:absolute;left:50%%;top:50%%;width:1px;height:1px;">
		<div style="position:absolute;top:-43px;left:-80px;margin:0 auto;text-align:center;" class="contentZone">
			<form id="login" name="login" action="%s" method="post">
				<div>
					<input type="hidden" name="send" value="yes">
					<input type="hidden" name="action" value="login">
				</div>
				<p><input id="loginField" type="text" name="login" value="'.e5c($_POST['login']).'"></p>
				<p><input type="password" name="password"></p>
				<p><input type="submit" value="OK"></p>
			</form>
		</div>
	</div>
	<script type="text/javascript">
	<!--
	$("#loginField")[0].focus();
	$("body").css({background:"#fff"});
	//-->
	</script>
',e5c(DOMAIN_PATH.$_SERVER['REDIRECT_URL']));
