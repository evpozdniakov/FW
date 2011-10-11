<?php

$form_items=new FormItems();
$field_name_modified=mb_substr($this->field_name,0,-1);

$result='';
$result.=$form_items->hidden($field_name_modified.'_lat]',$inputValue['lat']);
$result.=$form_items->hidden($field_name_modified.'_lng]',$inputValue['lng']);
$result.=$form_items->hidden($field_name_modified.'_zoom]',$inputValue['zoom']);

define('USE_GMAP',true);
$map_box_div_id=$this->model_name.'_'.$this->db_column.'_google_maps_id';

if(isset($params_arr['gmap_options'])){
	$map_options=$params_arr['gmap_options'];
}else{
	$obj_model=&gmo($this->model_name);
	if(!empty($params_arr['id'])){
		$map_options['lat']=$params_arr[$this->db_column.'_lat'];
		$map_options['lng']=$params_arr[$this->db_column.'_lng'];
		$map_options['zoom']=$params_arr[$this->db_column.'_zoom'];
		$map_options['info']=$obj_model->__str__($params_arr);
	}else{
		// по умолчанию показываем координаты Москвы
		$map_options['lat']='55.75580103347015';
		$map_options['lng']='37.617820501327515';
		$map_options['zoom']=7;
		$map_options['info']='';
	}
}

$js='
	var form_name="model_'.$this->model_name.'_form";
	var props={
		lat: (document.forms[form_name].elements["'.$field_name_modified.'_lat]"].value || '.$map_options['lat'].'),
		lng: (document.forms[form_name].elements["'.$field_name_modified.'_lng]"].value || '.$map_options['lng'].'),
		zoom: (document.forms[form_name].elements["'.$field_name_modified.'_zoom]"].value || '.$map_options['zoom'].')
	}
	var center = new google.maps.LatLng(props.lat,props.lng);
	var map_options = {
		zoom: parseInt(props.zoom),
		center: center,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	}
	var map = new google.maps.Map($("#'.$map_box_div_id.'")[0], map_options);
	if(!A.google_maps) A.google_maps={};
	A.google_maps["'.$this->db_column.'"]=map;
	
	var addMarker=function(hash){
		var marker = new google.maps.LatLng(hash.lat,hash.lng);
		marker = new google.maps.Marker({
			position: marker,
			map: hash.map,
			draggable: true
		});

		var info_window=new google.maps.InfoWindow({
			content: (hash.info || "")
		});
		info_window.open(hash.map,marker);
		
		google.maps.event.addListener(marker, "dragstart", function (){
			info_window.close();
		});
		google.maps.event.addListener(marker, "dragend", function() {
			var form_name="model_'.$this->model_name.'_form";
			document.forms[form_name].elements["'.$field_name_modified.'_lat]"].value=marker.getPosition().lat();
			document.forms[form_name].elements["'.$field_name_modified.'_lng]"].value=marker.getPosition().lng();
			document.forms[form_name].elements["'.$field_name_modified.'_zoom]"].value=hash.map.getZoom();
			info_window.open(hash.map,marker);
		});
		
		return marker;
	}
';

if(!empty($inputValue['lat']) && !empty($inputValue['lng'])){
	// создаем маркер, который можно перетаскивать
	$js.='
		var marker = addMarker({
			map: map,
			lat: '.$inputValue['lat'].',
			lng: '.$inputValue['lng'].',
			info: "'.e5cjs($map_options['info']).'"
		})
	';
}else{
	// добавляем обработчик клика по карте, клик должен добавить на карту перетаскиваемый марке
	$js.='
		google.maps.event.addListener(map, "click", function(evt) {
			var form_name="model_'.$this->model_name.'_form";
			document.forms[form_name].elements["'.$field_name_modified.'_lat]"].value=evt.latLng.lat();
			document.forms[form_name].elements["'.$field_name_modified.'_lng]"].value=evt.latLng.lng();
			document.forms[form_name].elements["'.$field_name_modified.'_zoom]"].value=map.getZoom();
			var marker = addMarker({
				map: map,
				lat: evt.latLng.lat(),
				lng: evt.latLng.lng()
			})
		})
	';
}

$result.='
	<div id="'.$map_box_div_id.'" style="width:600px;height:600px;"></div>
	<script type="text/javascript" charset="utf-8"><!--
		$(document).ready(function(){
			'.$js.'
		})
	//--></script>
';
