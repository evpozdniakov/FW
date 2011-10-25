<?php

$result='
	<h3>Невозможно удалить элемент, поскольку он привязан к другим:</h3>
	'
	.implode('<br>',$linked_model_items_arr)
	.'
';

?>