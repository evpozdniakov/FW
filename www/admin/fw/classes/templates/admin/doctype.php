<?php// cssCompressor::addCss('/admin/fw/media/css/defaults.css');if( USE_SHADOWBOX===true ){	Compressor::addCssAlone('/admin/fw/media/lib/shadowbox-3.0.3/shadowbox.css');}// themeif( defined('ADMIN_INTERFACE_THEME') ){	Compressor::addCssAlone('/admin/fw/media/theme/'.ADMIN_INTERFACE_THEME.'/style.css');}else{	Compressor::addCss('/admin/fw/media/css/admin.css');}// user admin cssif( file_exists(SITE_DIR.'/css/admin.css') ){	Compressor::addCss('/css/admin.css');}elseif( file_exists(SITE_DIR.'/media/css/admin.css') ){	Compressor::addCss('/media/css/admin.css');}// jsCompressor::addJs('/admin/fw/media/js/prefixfree.dynamic-dom.min.js',false);Compressor::addJs('/admin/fw/media/js/jquery.js',false);Compressor::addJs('/admin/fw/media/js/jquery.utils.js',true);Compressor::addJs('/admin/fw/media/js/jquery.form.js',true);if( USE_SWFOBJECT===true ){	Compressor::addJs('/admin/fw/media/js/swfobject2.js',false);}if( USE_SHADOWBOX===true ){	Compressor::addJs('/admin/fw/media/lib/shadowbox-3.0.3/shadowbox.js',false);}if( EDIT_MODE===true ){	// ckeditor + AjexFileManager	if( WYSIWYG_EDITOR=='CKEditor' ){		if( !file_exists(SITE_DIR.'/admin/fw/media/lib/ckeditor/ckeditor.js') ){			_die('В качестве WYSIWYG редактора указан CKEditor, необходимо создать симлинк ckeditor -> LIB_DIR/ckeditor и поместить его в /admin/fw/media/lib/');		}elseif( !file_exists(SITE_DIR.'/admin/fw/media/lib/AjexFileManager/ajex.js') ){			_die('В качестве WYSIWYG редактора указан CKEditor, необходимо создать симлинк AjexFileManager -> LIB_DIR/AjexFileManager и поместить его в /admin/fw/media/lib/');		}elseif( !is_dir(SITE_DIR.'/u') ){			_die('В качестве WYSIWYG редактора указан CKEditor, необходимо создать папку /u/ и дать ей права на запись');		}elseif( !is_writable(SITE_DIR.'/u') ){			_die('В качестве WYSIWYG редактора указан CKEditor, необходимо папке /u/ дать права на запись');		}else{			$wysiwyg='				<script type="text/javascript" charset="utf-8" src="'.'/admin/fw/media/lib/ckeditor/ckeditor.js"></script>				<script type="text/javascript" charset="utf-8" src="'.'/admin/fw/media/lib/AjexFileManager/ajex.js"></script>			';		}	}	// jquery UI	Compressor::addCssAlone('/admin/fw/media/lib/jquery-ui-1.8.6.custom/css/admin-theme/jquery-ui-1.8.6.custom.css');	Compressor::addJs('/admin/fw/media/lib/jquery-ui-1.8.6.custom/js/jquery-ui-1.8.6.custom.min.js',false);	Compressor::addJs('/admin/fw/media/lib/jquery-ui-1.8.6.custom/js/jquery.ui.datepicker-ru.js',true);}Compressor::addJs('/admin/fw/media/js/admin.js',true);$result='<!DOCTYPE html><html>	<head>		<title>FW</title>'		. Compressor::outCss('/media/css/')		. Compressor::outJs('/media/js/')		. $wysiwyg		. ((USE_GMAP===true)?'<script type="text/javascript" charset="utf-8" src="http://maps.google.com/maps/api/js?sensor=false&amp;key='.GMAPS_API_KEY.'"></script>':'')		. ((USE_YAMAP===true)?'<script type="text/javascript" charset="utf-8" src="http://api-maps.yandex.ru/1.1/index.xml?loadByRequire=1&amp;key='.MAPS_YANDEX_API_KEY.'"></script>':'')		. '		<!--[if lt IE 9]>			<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>		<![endif]-->';if( defined('ADMIN_INTERFACE_THEME') && file_exists(SITE_DIR.'/admin/fw/media/theme/'.ADMIN_INTERFACE_THEME.'/ie7.css') ){	$result.='					<!--[if IE 7]>				<link rel="stylesheet" href="/admin/fw/media/theme/'.ADMIN_INTERFACE_THEME.'/ie7.css" type="text/css" media="screen" charset="utf-8">			<![endif]-->	';}if( defined('ADMIN_INTERFACE_THEME') && file_exists(SITE_DIR.'/admin/fw/media/theme/'.ADMIN_INTERFACE_THEME.'/ie8.css') ){	$result.='					<!--[if IE 8]>				<link rel="stylesheet" href="/admin/fw/media/theme/'.ADMIN_INTERFACE_THEME.'/ie8.css" type="text/css" media="screen" charset="utf-8">			<![endif]-->	';}$cls=array('admin',DOMAIN);if( !isset($_SESSION['admin_user']) ){	$cls[]='login';}$result.='	</head>	<body class="'.implode(' ',$cls).'">		'.$this->body.'	</body></html>';