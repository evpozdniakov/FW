<?php

$GLOBALS['show_syncrho_complete']=true;
Compressor::addJs('/admin/fw/media/js/jquery.js',false);
Compressor::addJs('/admin/fw/media/js/jquery.utils.js',true);

$result='<!DOCTYPE html>
<html>
	<head>
		<title>synchro report</title>
		<style type="text/css">
			.back {color:blue;font-size:3em;line-height:1em;text-decoration:none;font-weight:bold;}
			.create p, .change p {display: none;white-space: pre;padding: 10px 20px;}
			.create p {background: #fef;}
			.create a {color: #939}
			.change p {background: #eef;}
			.change a {color: #339}
			.extracols .tabname {font-size: 120%;}
			.extracols .checkall {font-size: 80%; position: relative; top: -2px; left: 10px;}
			.extracols span {display: none;}
			.extracols a {font-size: 80%; position: relative; top: -2px;}
			.extra label {font-size: 120%; color: #933;}
		</style>'
		. Compressor::outJs('/media/js/')
		. '
		<script type="text/javascript"><!--
			function showHideCreateTable(id){
				showHide(id);
			}

			function showHideColumnsChange(id){
				showHide(id);
			}

			function showHideColumn(id){
				showHide(id,\'inline\');
			}

			function showHide(id){
				var dtype=arguments[1] || \'block\';
				var display=($(\'#\'+id)[0].style.display!=dtype)?dtype:\'none\';
				$(\'#\'+id)[0].style.display=display;
			}

			function checkAllCols(table){
				var form=document.forms[\'extracols_\'+ table];
				for(var i in form.elements){
					if(form.elements[i].type==\'checkbox\'){
						form.elements[i].checked=true;
					}
				}
			}
		//--></script>
	</head>
	<body>
		<p><small><a class="back" href="'.DOMAIN_PATH.'/admin/">&lt;&lt;&nbsp;&lt;&lt;&nbsp;&lt;&lt;&nbsp;&lt;&lt;&nbsp;&lt;&lt;&nbsp;&lt;&lt;&nbsp;</a></small></p>'
		. synchroReportCreatedTables($report)
		. synchroReportCreatedTables($report)
		. synchroReportChangedTables($report)
		. synchroReportExtraColumns($report)
		. synchroReportExtraTables($report)
		. synchroReportComplete()
		. '
	</body>
</html>';

function synchroReportCreatedTables($report){
	if(is_array($report['tables'])){
		foreach($report['tables'] as $table=>$info){
			if($info['created']!=''){
				$result.='<h3><a href="#" onclick="showHideCreateTable(\''.$table.'\');return false">'.$table.'</a></h3>';
				$result.='<p class="create" id="'.$table.'">'.$info['created'].'</p>';
			}
		}
		if($result!=''){
			$result='
				<div class="create">
					<h2>Были созданы таблицы</h2>
					<blockquote>'.$result.'</blockquote>
				</div>
			';
		}
	}
	if($result!=''){
		$GLOBALS['show_syncrho_complete']=false;
	}
	return $result;
}

function synchroReportChangedTables($report){
	if(is_array($report['tables'])){
		foreach($report['tables'] as $table=>$info){
			if(is_array($info['columns']) && count($info['columns'])>0){
				$result.='<h3><a href="#" onclick="showHideColumnsChange(\''.$table.'\');return false">'.$table.'</a></h3><p class="change" id="'.$table.'">';
				foreach($info['columns'] as $field){
					if(is_array($field['change'])){
						foreach($field['change'] as $item){
							$result.=$item.'<br>';
						}
					}elseif(is_array($field['add'])){
						foreach($field['add'] as $item){
							$result.=$item.'<br>';
						}
					}else{
						$result.=$field['add'].$field['change'].'<br>';
					}
				}
				$result.='</p>';
			}
		}
		if($result!=''){
			$result='
				<div class="change">
					<h2>Были изменены таблицы</h2>
					<blockquote>'.$result.'</blockquote>
				</div>
			';
		}
	}
	if($result!=''){
		$GLOBALS['show_syncrho_complete']=false;
	}
	return $result;
}

function synchroReportExtraColumns($report){
	if(is_array($report['extra']['columns'])){
		foreach($report['extra']['columns'] as $table=>$columns){
			$subresult=''; 
			$checkall='';
			if(count($columns)>0){
				foreach($columns as $column=>$info){
					$subresult.='
						<p>
							<input type="checkbox" name="delcol_'.$column.'" value="1" id="delcol_'.$column.'"> 
							<label for="delcol_'.$column.'">'.$column.'</label>
							<a href="#" onclick="showHideColumn(\''.$table.'_'.$column.'\');return false">подробнее</a>
							<span id="'.$table.'_'.$column.'">'.arr2str($info).'</span>
						</p>
					';
				}
				if(count($columns)>1){
					$checkall=' <a href="#" onclick="checkAllCols(\''.$table.'\');return false;" class="checkall">отметить все</a>';
				}
				$subresult='
					<strong class="tabname">'.$table.'</strong>'.$checkall.'<br>
					<form id="extracols_'.$table.'" name="extracols_'.$table.'" action="'.DOMAIN_PATH.'/admin/synchro/extra/" method="post" onsubmit="return confirm(\'Удалить лишние поля из таблицы '.$table.'?\')">
						<input type="hidden" name="table" value="'.$table.'">
						<blockquote>
							'.$subresult.'
							<p><input type="submit" value="Удалить"></p>
						</blockquote>
					</form>
				';
			}
			$result.=$subresult;
		}
		if($result!=''){
			$result='
				<div class="extracols">
					<h2>Поля не имеющие отношения к админке</h2>
					<blockquote>
						'.$result.'
					</blockquote>
				</div>
			';
		}
	}
	if($result!=''){
		$GLOBALS['show_syncrho_complete']=false;
	}
	return $result;
}

function synchroReportExtraTables($report){
	if(is_array($report['extra']['tables'])){
		foreach($report['extra']['tables'] as $table){
			$result.='<p><input type="checkbox" name="del_'.$table.'" value="1" id="del_'.$table.'"> <label for="del_'.$table.'">'.$table.'</label></p>';
		}
		if($result!=''){
			$result='
				<div class="extra">
					<h2>Таблицы не имеющие отношения к админке</h2>
					<blockquote>
						<form id="extra" name="extra" action="'.DOMAIN_PATH.'/admin/synchro/extra/" method="post" onsubmit="return confirm(\'Предупреждаем, что удаляемые данные могут быть связаны с файлами, сохраненными на сервере,\nили с полями других таблиц, используемых на сайте параллельно с админкой.\n\n Вы уверены, что все еще хотите удалить данные безвозвратно?\')">
							'.$result.'
							<p><input type="submit" value="Удалить"></p>
						</form>
					</blockquote>
				</div>
			';
		}
	}
	if($result!=''){
		$GLOBALS['show_syncrho_complete']=false;
	}
	return $result;
}

function synchroReportComplete(){
	if($GLOBALS['show_syncrho_complete']){
		$result='<p style="padding: 40px;"><b style="color:green;">Синхронизация закончена.</b></p>';
	}
	return $result;
}

function arr2str($arr){
	if(is_array($arr)){
		foreach($arr as $key=>$value){
			if(is_int($key) || $value==''){continue;}
			$result.=''.$key.'=>'.$value.', ';
		}
		$result=mb_substr($result,0,-2);
	}
	return $result;
}