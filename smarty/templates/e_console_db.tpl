{capture assign="db_debug_data"}
	{if $debug_db_arr}
		{if ($debug_db_stack_ctrl_show or $debug_db_ctrl_res)}
		<ul style="background-color:#eee">
			{if $debug_db_stack_ctrl_show}
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
			{/if}
			{if $debug_db_ctrl_res}
			<li>Result: 
				<a href="#" onclick="$('#debugDbBox div.item div.result').show();return false">Show all</a>
				<a href="#" onclick="$('#debugDbBox div.item div.result').hide();return false">Hide all</a>
				<a href="#" onclick="$('#debugDbBox div.item div.result').toggle();return false">Reverse all</a>
			</li>
			{/if}
		</ul>
		{/if}
		<div>
			Всего: {$debug_db_count}
		</div>
		<div id="debugDbBox">
			{foreach from=$debug_db_arr item="section" name="sections"}
				<div>
					<h1>{$section.name}</h1>
					{foreach from=$section.items item="item" name="items"}
						<div class="item">
							<p>
								<span class="time">{$item.execTime}</span>
								<span class="title">{$item.title}</span>
							</p>
							{if $item.stack}
								<a href="#" onclick="$(this).parent().children('.stack').toggle(); return false;" title="Скрыть/Показать">Stack:</a>
								<div class="stack">
									<a href="#" onclick="$(this).parent().find('.file').toggle(); return false;" title="Скрыть/Показать">Файлы</a>
									{foreach from=$item.stack item="stack_item" name="stack_items"}
										<div class="stack_item">
											{if $stack_item.class}
												<span class="stack_item_class">{$stack_item.class|cat:$stack_item.type}</span>
											{/if}
											
											{if $stack_item.args}
												{assign var="is_args" value="<a href=\"#\" onclick=\"$(this).parent().parent().find('.args').toggle();return false\" title=\"Аргументы показать/скрыть\">...</a>"}
											{else}
												{assign var="is_args" value=""}
											{/if}
											<span class="stack_item_func">{$stack_item.function}({$is_args})</span>
											<span class="file">{$stack_item.file|cat:" "|cat:$stack_item.line}</span>
										
											{if $stack_item.args}
												<div class="args" >
													{foreach from=$stack_item.args item="arg_item" name="arg_items"}
														{*php}
															$arg_item=$GLOBALS['obj_client']->getVar('arg_item');
															echo '<pre>'.(is_array($arg_item) || is_object($arg_item))?export($arg_item):e5c($arg_item).'</pre>';
														{/php*}
														{if is_array($arg_item) || is_object($arg_item)}
															<pre>{export var=$art_item}</pre>
														{else}
															<pre>{$arg_item|escape}</pre>
														{/if}
													{/foreach}
												</div>
											{/if}
										
										</div>
									{/foreach}
								</div>
							{/if}
							{if $item.res}
								<a href="#" onclick="$(this).parent().children('.result').toggle(); return false;" title="Скрыть/Показать">Results:</a>
								<div class="result">
									<pre>{$item.res}</pre>
								</div>
							{/if}
						</div>
					{/foreach}
				</div>
			{/foreach}
		</div>
	{/if}
{/capture}

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
	code+='		{$db_debug_data|escape:"javascript"}';
	code+='	<\/body>';
	code+='<\/html>';
	window.db_console_window=window.open('','consoleDB','width=680,height=600,resizable,scrollbars=yes');
	window.db_console_window.document.write(code);
	window.db_console_window.document.close();
	window.db_console_window.focus();
</script>
