$.extend({
	elementCoords: function($__html__selector){
		if(typeof $__html__selector['jquery']=='undefined'){
			// $__html__selector is html element(s) or selector, transform to jquery
			$__html__selector=$($__html__selector);
		}
		var html_elem=$__html__selector[0];

		if(html_elem.offsetTop || html_elem.offsetParent){
			var left=html_elem.offsetLeft, top=html_elem.offsetTop;
			var offset_parent=html_elem.offsetParent;
			while(true){
				if(!offset_parent || offset_parent.tagName=='BODY'){break;}
				left+=offset_parent.offsetLeft;
				top+=offset_parent.offsetTop;
				offset_parent=offset_parent.offsetParent;
			}
			return {
				left: left,
				top: top,
				width: html_elem.offsetWidth,
				height: html_elem.offsetHeight
			}
		}
	}
})

$.fn.extend({
	elementCoords: function(relative_elem){
		var coords=$.elementCoords(this[0]);
		if(relative_elem){
			if( typeof relative_elem == 'string' ){
				relative_elem = $(relative_elem)[0];
			}else if( typeof relative_elem.jquery == 'string' ){
				relative_elem = relative_elem[0];
			}
			var relative_coords=$.elementCoords(relative_elem);
			coords.left-=relative_coords.left;
			coords.top-=relative_coords.top;
		}
		return coords;
	}
})