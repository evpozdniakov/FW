<?php

$result='
	<div id="mainBox">
		<div id="header">
			<div id="logo">'.(isset($GLOBALS['path'][2])?'<a href="/admin/"></a>':'').'</div>
			<div id="ajaxLoader"></div>
			<div id="domainsList">
				<h1>Система администрирования сайта</h1>
				<p>'.$this->domains_list.'</p>
				<div class="clear"></div>
			</div>
		</div>
		<div id="modelsList">'.$this->models_list.'</div>
		<div id="itemsListAndEditZone">
			<div id="modelItemsList"><div class="pad">'.$this->model_items_list.'</div><div class="pad">'.$GLOBALS['result2'].'</div></div>
			<div id="modelItemEditZone"><div class="pad">'.$this->model_item_edit_zone.'</div></div>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
	</div>
	<div id="minW"></div>
	';
?>

