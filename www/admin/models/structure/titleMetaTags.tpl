{if $title_tag}
	<title>{$title_tag|strip_tags}</title>
{/if}
{if $meta_description}
	<meta name="description" content="{$meta_description|escape:"html"}">
{/if}
{if $meta_keywords}
	<meta name="keywords" content="{$meta_keywords|escape:"html"}">
{/if}
