<?php

$result='
	<div class="rb50">
		<form id="loginForm" class="adminForm" name="login" action="'.e5c(DOMAIN_PATH.$_SERVER['REDIRECT_URL']).'" method="post">
			<div><input type="hidden" name="send" value="yes"><input type="hidden" name="action" value="login"></div>
			<div class="pad">
				<p class="titled">
					<span class="title">Логин<br></span>
					<span class="tag text"><input id="loginField" type="text" name="login" value="'.e5c($_POST['login']).'" autocomplete="off"></span>
				</p>
				<p class="titled">
					<span class="title">Пароль<br></span>
					<span class="tag pswd"><input type="password" name="password"></span>
				</p>
				<p class="titled">
					<span class="title"><br></span>
					<span class="tag sbmt"><button type="submit" value="submit">Авторизоваться</button></span>
				</p>
			</div>
		</form>
	</div>
';
