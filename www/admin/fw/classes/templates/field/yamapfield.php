<?php

$form_items=new FormItems();
$field_name_modified=mb_substr($this->field_name,0,-1);

// константа USE_YAMAP используется в doctype.php
define('USE_YAMAP',true);

// коориднаты точки на карте помещаем в скрытое поле
$result=array();
$result[]=$form_items->hidden($field_name_modified.'_lat]',$inputValue['lat']);
$result[]=$form_items->hidden($field_name_modified.'_lng]',$inputValue['lng']);
$result[]=$form_items->hidden($field_name_modified.'_zoom]',$inputValue['zoom']);

// массив $map_options содержит параметры отркываемой карты — масштаб и координаты центра
if(isset($params_arr['yamap_options'])){
	$map_options=$params_arr['yamap_options'];
}else{
	$obj_model=&gmo($this->model_name);
	if(!empty($params_arr[$this->db_column.'_lat'])){
		$map_options['lat']=$params_arr[$this->db_column.'_lat'];
		$map_options['lng']=$params_arr[$this->db_column.'_lng'];
		$map_options['zoom']=$params_arr[$this->db_column.'_zoom'];
		$map_options['info']=$obj_model->__str__($params_arr);
	}else{
		// по умолчанию показываем координаты Москвы
		$map_options['lat']='55.75580103347015';
		$map_options['lng']='37.617820501327515';
		$map_options['zoom']=10;
		$map_options['info']='';
	}
}

// id бокса, содержащего карту
$map_box_div_id=$this->model_name.'_'.$this->db_column.'_yandex_maps_id';

// html и js код
$result[]='
	<div id="'.$map_box_div_id.'" style="width:600px;height:600px"></div>
	<script type="text/javascript"><!--
		$().ready(function(){
			YMaps.load(function(){
				var map = new YMaps.Map($("#'.$map_box_div_id.'")[0]);
				map.setCenter(new YMaps.GeoPoint('.$map_options['lng'].', '.$map_options['lat'].'), '.$map_options['zoom'].');
				map.addControl(new YMaps.TypeControl());
				map.addControl(new YMaps.ToolBar());
				map.addControl(new YMaps.Zoom());
				// map.addControl(new YMaps.MiniMap());
				// map.addControl(new YMaps.ScaleLine());
				var searchControl = new YMaps.SearchControl({
				    resultsPerPage: 5,  // Количество объектов на странице
				    useMapBounds: 1     // Объекты, найденные в видимой области карты 
				                        // будут показаны в начале списка
				});
				map.addControl(searchControl);

				var placemark;
				var $form=$("#model_'.$this->model_name.'_form");
				var $input_lat=$form.find("input[name='.$field_name_modified.'_lat]]");
				var $input_lng=$form.find("input[name='.$field_name_modified.'_lng]]");
				var $input_zoom=$form.find("input[name='.$field_name_modified.'_zoom]]");

				var setPlacemark=function(geopoint){
					placemark=new YMaps.Placemark(geopoint, {draggable:true});
					map.addOverlay(placemark);
					YMaps.Events.observe(placemark, placemark.Events.DragEnd, function (plcmrk) {
						resetData(plcmrk._point);
					}, this);
				}

				var resetData=function(gpoint){
					$input_lat[0].value=gpoint.__lat;
					$input_lng[0].value=gpoint.__lng;
					$input_zoom[0].value=map.getZoom();
				}

				var lat=$input_lat[0].value;
				var lng=$input_lng[0].value;
				if( lat && lng ){
					setPlacemark( new YMaps.GeoPoint(lng, lat) );
				}else{
					var map_click_event=YMaps.Events.observe(map, map.Events.Click, function (map, mEvent) {
						setPlacemark( mEvent.getGeoPoint() );
						resetData(mEvent._point);
						map_click_event.cleanup();
					}, this);
				}

				YMaps.Events.observe(map, map.Events.Update, function (map, mEvent) {
					$input_zoom[0].value=map.getZoom();
				}, this);

			});
		})
	//--></script>
';

$result=implode('',$result);