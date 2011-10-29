<?php /* Smarty version Smarty-3.0.8, created on 2011-10-29 21:56:58
         compiled from "/Users/evgenypozdnyakov/Sites/starter_local/www/../smarty/templates/e_console_db.tpl" */ ?>
<?php /*%%SmartyHeaderCode:20046399754eac3e6a82d437-14169501%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'a185b7f9e8ddc4cfd74150f89b9ba0bc19f41fc5' => 
    array (
      0 => '/Users/evgenypozdnyakov/Sites/starter_local/www/../smarty/templates/e_console_db.tpl',
      1 => 1318423560,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '20046399754eac3e6a82d437-14169501',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!is_callable('smarty_function_export')) include '/Users/evgenypozdnyakov/Sites/starter_local/www/../smarty/plugins/function.export.php';
if (!is_callable('smarty_modifier_escape')) include '/Users/evgenypozdnyakov/git/FW/lib/Smarty-3.0.8/libs/plugins/modifier.escape.php';
?><?php ob_start(); ?>
	<?php if ($_smarty_tpl->getVariable('debug_db_arr')->value){?>
		<?php if (($_smarty_tpl->getVariable('debug_db_stack_ctrl_show')->value||$_smarty_tpl->getVariable('debug_db_ctrl_res')->value)){?>
		<ul style="background-color:#eee">
			<?php if ($_smarty_tpl->getVariable('debug_db_stack_ctrl_show')->value){?>
			<li>Stack: 
				<a href="#" onclick="$('#debugDbBox div.item div.stack').show();return false">Show all</a>
				<a href="#" onclick="$('#debugDbBox div.item div.stack').hide();return false">Hide all</a>
				<a href="#" onclick="$('#debugDbBox div.item div.stack').toggle();return false">Reverse all</a>
			</li>
			<li>File: 
				<a href="#" onclick="$('#debugDbBox div.item div.stack span.file').show();return false">Show all</a>
				<a href="#" onclick="$('#debugDbBox div.item div.stack span.file').hide();return false">Hide all</a>
				<a href="#" onclick="$('#debugDbBox div.item div.stack span.file').toggle();return false">Reverse all</a>
			</li>
			<li>Args: 
				<a href="#" onclick="$('#debugDbBox div.item div.stack div.args').show();return false">Show all</a>
				<a href="#" onclick="$('#debugDbBox div.item div.stack div.args').hide();return false">Hide all</a>
				<a href="#" onclick="$('#debugDbBox div.item div.stack div.args').toggle();return false">Reverse all</a>
			</li>
			<?php }?>
			<?php if ($_smarty_tpl->getVariable('debug_db_ctrl_res')->value){?>
			<li>Result: 
				<a href="#" onclick="$('#debugDbBox div.item div.result').show();return false">Show all</a>
				<a href="#" onclick="$('#debugDbBox div.item div.result').hide();return false">Hide all</a>
				<a href="#" onclick="$('#debugDbBox div.item div.result').toggle();return false">Reverse all</a>
			</li>
			<?php }?>
		</ul>
		<?php }?>
		<div>
			Всего: <?php echo $_smarty_tpl->getVariable('debug_db_count')->value;?>

		</div>
		<div id="debugDbBox">
			<?php  $_smarty_tpl->tpl_vars["section"] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('debug_db_arr')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars["section"]->key => $_smarty_tpl->tpl_vars["section"]->value){
?>
				<div>
					<h1><?php echo $_smarty_tpl->getVariable('section')->value['name'];?>
</h1>
					<?php  $_smarty_tpl->tpl_vars["item"] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('section')->value['items']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars["item"]->key => $_smarty_tpl->tpl_vars["item"]->value){
?>
						<div class="item">
							<p>
								<span class="time"><?php echo $_smarty_tpl->getVariable('item')->value['execTime'];?>
</span>
								<span class="title"><?php echo $_smarty_tpl->getVariable('item')->value['title'];?>
</span>
							</p>
							<?php if ($_smarty_tpl->getVariable('item')->value['stack']){?>
								<a href="#" onclick="$(this).parent().children('.stack').toggle(); return false;" title="Скрыть/Показать">Stack:</a>
								<div class="stack">
									<a href="#" onclick="$(this).parent().find('.file').toggle(); return false;" title="Скрыть/Показать">Файлы</a>
									<?php  $_smarty_tpl->tpl_vars["stack_item"] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('item')->value['stack']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars["stack_item"]->key => $_smarty_tpl->tpl_vars["stack_item"]->value){
?>
										<div class="stack_item">
											<?php if ($_smarty_tpl->getVariable('stack_item')->value['class']){?>
												<span class="stack_item_class"><?php echo ($_smarty_tpl->getVariable('stack_item')->value['class']).($_smarty_tpl->getVariable('stack_item')->value['type']);?>
</span>
											<?php }?>
											
											<?php if ($_smarty_tpl->getVariable('stack_item')->value['args']){?>
												<?php $_smarty_tpl->tpl_vars["is_args"] = new Smarty_variable("<a href=\"#\" onclick=\""."$"."(this).parent().parent().find('.args').toggle();return false\" title=\"Аргументы показать/скрыть\">...</a>", null, null);?>
											<?php }else{ ?>
												<?php $_smarty_tpl->tpl_vars["is_args"] = new Smarty_variable('', null, null);?>
											<?php }?>
											<span class="stack_item_func"><?php echo $_smarty_tpl->getVariable('stack_item')->value['function'];?>
(<?php echo $_smarty_tpl->getVariable('is_args')->value;?>
)</span>
											<span class="file"><?php echo (($_smarty_tpl->getVariable('stack_item')->value['file']).(" ")).($_smarty_tpl->getVariable('stack_item')->value['line']);?>
</span>
										
											<?php if ($_smarty_tpl->getVariable('stack_item')->value['args']){?>
												<div class="args" >
													<?php  $_smarty_tpl->tpl_vars["arg_item"] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('stack_item')->value['args']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars["arg_item"]->key => $_smarty_tpl->tpl_vars["arg_item"]->value){
?>
														<?php if (is_array($_smarty_tpl->getVariable('arg_item')->value)||is_object($_smarty_tpl->getVariable('arg_item')->value)){?>
															<pre><?php echo smarty_function_export(array('var'=>$_smarty_tpl->getVariable('art_item')->value),$_smarty_tpl);?>
</pre>
														<?php }else{ ?>
															<pre><?php echo smarty_modifier_escape($_smarty_tpl->getVariable('arg_item')->value);?>
</pre>
														<?php }?>
													<?php }} ?>
												</div>
											<?php }?>
										
										</div>
									<?php }} ?>
								</div>
							<?php }?>
							<?php if ($_smarty_tpl->getVariable('item')->value['res']){?>
								<a href="#" onclick="$(this).parent().children('.result').toggle(); return false;" title="Скрыть/Показать">Results:</a>
								<div class="result">
									<pre><?php echo $_smarty_tpl->getVariable('item')->value['res'];?>
</pre>
								</div>
							<?php }?>
						</div>
					<?php }} ?>
				</div>
			<?php }} ?>
		</div>
	<?php }?>
<?php  $_smarty_tpl->assign("db_debug_data", ob_get_contents()); Smarty::$_smarty_vars['capture']['default']=ob_get_clean();?>

<script type="text/javascript">
	var code='';
	code+='<!DOCTYPE html>';
	code+='<html>';
	code+='	<head>';
	code+='		<title> Debug DB Console<\/title>';
	code+='		<link rel="stylesheet" type="text\/css" href="\/admin\/fw\/media\/css\/dbconsole.css">';
	code+='		<script type=\"text\/javascript\" src=\"\/admin\/fw\/media\/js\/jquery.js\"><\/script>';
	//code+='		<script type=\"text\/javascript\" src=\"http:\/\/dev.jquery.com\/view\/trunk\/plugins\/ui\/ui.sortable.js\"><\/script>';
	code+='	<\/head>';
	code+='	<body>';
	code+='		<?php echo smarty_modifier_escape($_smarty_tpl->getVariable('db_debug_data')->value,"javascript");?>
';
	code+='	<\/body>';
	code+='<\/html>';
	window.db_console_window=window.open('','consoleDB','width=680,height=600,resizable,scrollbars=yes');
	window.db_console_window.document.write(code);
	window.db_console_window.document.close();
	window.db_console_window.focus();
</script>
