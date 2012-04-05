$.extend({
	// cookieExpireTime в часах
	setCookie: function(cookieName,cookieContent,cookieExpireTime){
		if(cookieExpireTime>0){
			var expDate=new Date();
			expDate.setTime(expDate.getTime()+cookieExpireTime*1000*60*60);
			var expires=expDate.toGMTString();
			document.cookie=cookieName+"="+escape(cookieContent)+"; path="+escape('/')+"; expires="+expires;
		}else{
			document.cookie=cookieName+"="+escape(cookieContent)+"; path="+escape('/')+"";
		}
	},
	getCookie: function(cookieName){
		var ourCookie=document.cookie;
		if(!ourCookie || ourCookie=="")return "";
			ourCookie=ourCookie.split(";");
		var i=0;
		var Cookie;
		while(i<ourCookie.length){
			Cookie=ourCookie[i].split("=")[0];
			if(Cookie.charAt(0)==" ")
				Cookie=Cookie.substring(1);
			if(Cookie==cookieName){
				return unescape(ourCookie[i].split("=")[1]);
			}
			i++;
		}
		return ""
	},
	evIE: function(command){
		if(!$.browser.msie){return command;}
		switch(command){
			case 'slideToggle':
				return 'toggle';
			case 'slideUp':
				return 'hide';
			case 'slideDown':
				return 'show';
		}
	},
	evIE6: function(){
		return ($.browser.msie && parseFloat($.browser.version)<=6);
	},
	evScrollTop: function(){
		var $body=$('body');
		return Math.max($body[0].scrollTop,$body.parent()[0].scrollTop);
	},
	evScrollLeft: function(){
		var $body=$('body');
		return Math.max($body[0].scrollLeft,$body.parent()[0].scrollLeft);
	},
	evScreenWidth: function(){
		if ($.browser.opera && parseInt($.browser.version)<=9){
			return $(document).width();
		}	else {
			return $('html')[0].clientWidth;
		}
	},
	evScreenHeight: function(){
		if ($.browser.opera && parseInt($.browser.version)<=9){
			return $(document).height();
		}	else {
			return $('html')[0].clientHeight;
		}
	},
	evScrollWidth: function(){
		var $body=$('body');
		return Math.max($body[0].scrollWidth,$body.parent()[0].scrollWidth);
	},
	evScrollHeight: function(){
		var $body=$('body');
		return Math.max($body[0].scrollHeight,$body.parent()[0].scrollHeight);
	},
	// расположен ли элемент в видимой части экрана
	evElementCoords: function(html_elem){
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
	},
	evScreenCoords: function(){
		var width, height;
		if(window.innerWidth){
			width=window.innerWidth;
			height=window.innerHeight;
		}else if(document.documentElement){
			width=document.documentElement.clientWidth;
			height=document.documentElement.clientHeight;
		}else if(document.body){
			width=document.body.clientWidth;
			height=document.body.clientHeight;
		}else{
			alert('error in $.evScreenCoords()');
		}
		return {
			left: $.evScrollLeft(),
			top: $.evScrollTop(),
			width: width,
			height: height
		}
	},
	evValidateEmail: function(input_or_string){
		//проверяем передана ли строка
		if(typeof input_or_string=='string'){
			var email=input_or_string;
		}else{
			//проверяем передан ли тег
			if(typeof input_or_string.value=='string'){
				var email=input_or_string.value;
			}else{
				//проверяем передан ли объект jQuery
				if(typeof input_or_string[0].value=='string'){
					var email=input_or_string[0].value;
				}else{
					return false;
				}
			}
		}
		return (email.search(/^[^@\s]+@[^@\s]+\.[^@\s]{2,}$/)==0);
	},
	evNewin: function(link){
		if(link && link.href){
			var newin=window.open(link.href);
			return false;
		}
	},
	evCSSrule: function(selector, style){
		if( !document.styleSheets.length ){
			$(document.createElement('style')).attr('text/css').appendTo('head');
		}
		var css_rule_text=selector + '{'+ style+ '}';
		try{
			var x=document.styleSheets[0];
			if($.browser.msie){
				x.addRule(selector, style);
			}else{
				x.insertRule(css_rule_text,x.cssRules.length);
			}
		}catch(e){
			if( typeof console=='object' && typeof console.log=='function' ){
				console.log('can\'t insert css rule:\n' + css_rule_text);
			}
		}
	}
});

$.fn.extend({
	evDragDrop: function(hash){
		this.each(function(){
			//придумываем идентификатор объекту, чтобы отличать его от других
			var $target=$(this);
			var random_id='random_id_'+Math.random().toString().substr(2);
			if(!window['C'])C={};
			if(!C['__evDragDrop__'])C.__evDragDrop__=[];
			C.__evDragDrop__[random_id]={};
			// определяем функцию для вызоыва hash.callback
			var callback_trigger_function=function(hash,evt,evt_name,lt){
				if(hash.callback){
					hash.callback({
						event: evt,
						$target: $target,
						type: evt_name,
						left: lt[0],
						top: lt[1]
					})
				}
			}
			//реагируем на нажатие
			$target.mousedown(function(evt){
				evt.preventDefault();
				C.__evDragDrop__[random_id].is_drag=true;
				C.__evDragDrop__[random_id].delta_xy=[
					evt.clientX - $target[0].offsetLeft,
					evt.clientY - $target[0].offsetTop
				];
				// дергаем hash.callback
				callback_trigger_function(hash,evt,'start',[$target[0].offsetLeft,$target[0].offsetTop]);
			});
			//реагируем на drag
			$('body').mousemove(function(evt){
				evt.preventDefault();
				if(C.__evDragDrop__[random_id].is_drag){
					var lt=[
						evt.clientX - C.__evDragDrop__[random_id].delta_xy[0],
						evt.clientY - C.__evDragDrop__[random_id].delta_xy[1],
					];
					//преобразуем новые координаты так, чтобы объект не вылезал за рамки
					if(hash.left){
						if(lt[0]<hash.left[0])lt[0]=hash.left[0];
						if(lt[0]>hash.left[1])lt[0]=hash.left[1];
					}
					if(hash.top){
						if(lt[1]<hash.top[0])lt[1]=hash.top[0];
						if(lt[1]>hash.top[1])lt[1]=hash.top[1];
					}
					//позициорируем
					$target.css({'left':lt[0],'top':lt[1]});
					// дергаем hash.callback
					callback_trigger_function(hash,evt,'drag',lt);
				}
			});
			//реагируем на drop
			$('body').mouseup(function(evt){//привязываем событие к document, потому что нам не важны координаты
				evt.preventDefault();
				if(C.__evDragDrop__[random_id].is_drag){
					//если происходит перетаскивание, то заканчиваем его
					C.__evDragDrop__[random_id].is_drag=false;
					C.__evDragDrop__[random_id].delta_xy=null;
					callback_trigger_function(hash,evt,'drop',[$target[0].offsetLeft,$target[0].offsetTop]);
				}
			});
		})
		return this;
	},
	evElementCoords: function(relative_elem){
		var coords=$.evElementCoords(this[0]);
		if(relative_elem){
			if( typeof relative_elem == 'string' ){
				relative_elem = $(relative_elem)[0];
			}else if( typeof relative_elem.jquery == 'string' ){
				relative_elem = relative_elem[0];
			}
			var relative_coords=$.evElementCoords(relative_elem);
			coords.left-=relative_coords.left;
			coords.top-=relative_coords.top;
		}
		return coords
	},
	evSboxSelect: function(value){
		this.each(function(){
			var sbox=this;
			if(sbox.tagName=='SELECT'){
				var ops=sbox.options;
				for(var i=0; i<ops.length; i++){
					if(ops[i].value==value){
						ops.selectedIndex=i;
						break;
					}
				}
			}
		})
		return this;
	},
	evSboxValue: function(){
		var sbox=this[0];
		var index=sbox.options.selectedIndex;
		var current=sbox.options[index].value;
		return current;
	},
	evPositionFixed: function(hash){
		// hash={attach:'lt|lb|rt|rb', width:'123px', height:'100%', animate:(true|false)}
		this.each(function(){
			if( $.browser.msie && parseInt($.browser.version)<8 ){
				hash=(hash || {});
				hash.attach=(hash.attach || 'lt');
				var $el=$(this).css({position:'absolute'});
				if($el.length){
					var opts=hash;
					if( $el.attr('id') ){
						opts.id=$el.attr('id');
					}else{
						opts.id='random_id_'+Math.random().toString().substr(2);
						$el.attr({id:opts.id});
					}
					if(hash.attach.indexOf('r')>=0){
						try{
							opts.r=$el.css('right');
						}catch(e){
							opts.r=0;
						}
					}else{
						try{
							opts.l=$el.css('left');
						}catch(e){
							opts.l=0;
						}
					}
					if(hash.attach.indexOf('b')>=0){
						try{
							opts.b=$el.css('bottom');
						}catch(e){
							opts.b=0;
						}
					}else{
						try{
							opts.t=$el.css('top');
						}catch(e){
							opts.t=0;
						}
					}
					// alert([opts.t,opts.r,opts.b,opts.l,opts.attach])
					$(window).bind('scroll resize', (function(opts){
						return function(evt){
							opts.$el=$('#'+opts.id);
							if( opts.height ){
								if( isNaN(opts.height) && opts.height.indexOf('%') ){
									var h=opts.$el.parent()[0].offsetHeight * parseInt(opts.height) / 100;
								}else{
									var h=parseInt(opts.height);
								}
							}else{
								var h=opts.$el[0].offsetHeight;
							}

							if( opts.width ){
								if( isNaN(opts.width) && opts.width.indexOf('%') ){
									var w=opts.$el.parent()[0].offsetWidth * parseInt(opts.width) / 100;
								}else{
									var w=parseInt(opts.width);
								}
							}else{
								var w=opts.$el[0].offsetWidth;
							}

							if(opts.attach.indexOf('r')>=0){
								if(opts.r!='auto'){
									var l=$.evScrollLeft() + $.evScreenWidth() - parseInt(opts.r) - opts.$el[0].offsetWidth;
								}
							}else{
								if(opts.l!='auto'){
									var l=$.evScrollLeft() + parseInt(opts.l);
								}
							}
							if(opts.attach.indexOf('b')>=0){
								if(opts.b!='auto'){
									var t=$.evScrollTop() + $.evScreenHeight() - parseInt(opts.b) - opts.$el[0].offsetHeight;
								}
							}else{
								if(opts.t!='auto'){
									var t=$.evScrollTop() + parseInt(opts.t);
								}
							}
							if( opts.animate ){
								var timer=opts.$el.data('resize-scroll-timer');
								if( timer ){
									clearTimeout(timer);
								}
								opts.$el.data=setTimeout('$("#'+opts.id+'").css({height:'+h+', width:'+w+'}).stop().animate({left:'+l+', top:'+t+'},"fast")',200);
							}else{
								opts.$el.css({height:h, width:w, left:l, top:t});
							}
						}
					})(opts)).trigger('resize');
				}
			}
		})
		return this;
	},
	evGMapping: function(){
		this.each(function(i){
			var $element=$(this);
			// создаем массив window.evGMapping_GMap2 со ссылками на карты
			if(i==0 && typeof window['evGMapping_GMap2']=='undefined'){
				window['evGMapping_GMap2']=[];
			}
			// параметры карты по атрибутам элемента
			var gmap2_index=window.evGMapping_GMap2.length;
			var lat__lng=($element.attr('data-coords') || '55.72711, 42.099609');
			var lat=parseFloat(lat__lng.split(',')[0]);
			var lng=parseFloat(lat__lng.split(',')[1]);
			var zoom=parseInt($element.attr('data-zoom') || 4);
			var width=($element.attr('data-width') || '100%');
			if(parseInt(width) == width) width+='px';
			var height=($element.attr('data-height') || 600);
			if(parseInt(height) == height) height+='px';
			// скрываем детей, создаем бокс для карты
			$element.children().hide();
			var $gmap_box = $(document.createElement('div')).addClass('GMapBox').attr({'data-gmap2index':gmap2_index}).width(width).height(height).prependTo($element);
			// создаем карту
			window.evGMapping_GMap2[gmap2_index] = new GMap2($gmap_box[0]);
			window.evGMapping_GMap2[gmap2_index].setCenter(new GLatLng(lat, lng), zoom);
			// управление картой
			window.evGMapping_GMap2[gmap2_index].addControl(new GMapTypeControl());
			window.evGMapping_GMap2[gmap2_index].enableScrollWheelZoom();
			// window.evGMapping_GMap2[gmap2_index].addMapType(G_PHYSICAL_MAP);
			// window.evGMapping_GMap2[gmap2_index].removeMapType(G_HYBRID_MAP);
			window.evGMapping_GMap2[gmap2_index].addControl(new GLargeMapControl3D());
			// нестандартная кнопка со слоем
			$element.children('div.GMcontrol').each(function(){
				var $control_element=$(this);
				// параметры
				var group_title=$control_element.attr('data-title');
				// чтобы создать подкласс элемента GControl, 
				// задаем GControl в качестве объекта-прототипа
				// и реализуем два его метода: initialize и getDefaultPosition
				var GroupControl=function(){}
				GroupControl.prototype = new GControl();
				GroupControl.prototype.initialize = function(map) {
					var width=(parseInt($control_element.attr('data-width')) || 197);
					var $container=$(document.createElement('div')).addClass('GMgroupControl').width(width).
						append($(document.createElement('div')).addClass('brd'));
					var $btn=$(document.createElement('div')).addClass('btn').text(group_title).
						appendTo($container.children('div.brd'));
					if($control_element.children('div.GMgroup').length){
						var $ul=$(document.createElement('ul')).
							appendTo($(document.createElement('div')).addClass('ul').
								appendTo($container.children('div.brd')));
						$control_element.children('div.GMgroup').each(function(){
							var $group=$(this);
							var title=$group.attr('data-title');
							var $a=$(document.createElement('a')).attr({href:'#'}).text(title).
								appendTo($(document.createElement('li')).appendTo($ul));
							// обработчик клика по ссылке
							$a.bind('click',function(evt){
								evt.preventDefault();
								var lat__lng=$group.attr('data-coords');
								var lat=parseFloat(lat__lng.split(',')[0]);
								var lng=parseFloat(lat__lng.split(',')[1]);
								var zoom=parseInt($group.attr('data-zoom'));
								map.setCenter(new GLatLng(lat, lng), zoom);
							})
						})
						// обработчик mouseover mouseout на кнопке группы
						window['evGMapping_hidegroup_function'+i]=function(){$container.removeClass('GMgroupControlActive');}
						$container.bind('mouseover mouseout', function(evt){
							if(evt.type=='mouseover'){
								try{clearTimeout(window['evGMapping_hidegroup_timer'+i])}catch(e){}
								$container.addClass('GMgroupControlActive');
							}else if(evt.type=='mouseout'){
								window['evGMapping_hidegroup_timer'+i]=setTimeout('window.evGMapping_hidegroup_function'+i+'()',100);
							}
						});
						$btn.bind('click',function(){
							$container.toggleClass('GMgroupControlActive');
						})
					}
					map.getContainer().appendChild($container[0]);
				  return $container[0];
				}
				GroupControl.prototype.getDefaultPosition = function() {
					var corner;
					if($control_element.attr('data-right-top')){
						var x__y=$control_element.attr('data-right-top');
						corner=G_ANCHOR_TOP_RIGHT;
					}else if($control_element.attr('data-left-top')){
						var x__y=$control_element.attr('data-left-top');
						corner=G_ANCHOR_TOP_LEFT;
					}else if($control_element.attr('data-left-bottom')){
						var x__y=$control_element.attr('data-left-bottom');
						corner=G_ANCHOR_BOTTOM_LEFT;
					}else if($control_element.attr('data-right-bottom')){
						var x__y=$control_element.attr('data-right-bottom');
						corner=G_ANCHOR_BOTTOM_RIGHT;
					}else{
						var x__y='7,33';
						corner=G_ANCHOR_TOP_RIGHT;
					}
					var x=parseInt(x__y.split(',')[0]);
					var y=parseInt(x__y.split(',')[1]);
					return new GControlPosition(corner, new GSize(x, y));
				}
				// задаем первичные стили
				if(i==0 && typeof window['evGMapping_css_control_done']=='undefined'){
					window['evGMapping_css_control_done']=true;
					$.evCSSrule('div.GMapBox div.GMgroupControl', 'background: white;border: 1px solid black;')
					$.evCSSrule('div.GMapBox div.GMgroupControl div.brd', 'border-style: solid;border-width: 1px;border-color: #fff #b0b0b0 #b0b0b0 #fff;font: 12px "Arial", sans-serif;')
					$.evCSSrule('div.GMapBox div.GMgroupControl div.brd div.btn', 'text-align: center;cursor: pointer;height: 16px;')
					$.evCSSrule('div.GMapBox div.GMgroupControl div.brd div.ul', 'display: none;margin: -1px 4px 4px;padding: 4px 0 2px;border-top: 1px solid #ddd;')
					$.evCSSrule('div.GMapBox div.GMgroupControlActive div.brd div.ul', 'display: block;')
					$.evCSSrule('div.GMapBox div.GMgroupControl div.brd div.ul ul', 'margin: 0 4px 0 8px;padding: 0;list-style-type: disc;font-size: 11px;line-height: 1em;')
					$.evCSSrule('div.GMapBox div.GMgroupControl div.brd div.ul ul', 'overflow: auto;')
					$.evCSSrule('div.GMapBox div.GMgroupControl div.brd div.ul ul li', 'margin: 4px 0;padding: 0;background: none;')
					$.evCSSrule('div.GMapBox div.GMgroupControl div.brd div.ul ul li:before', 'content: "●";font-size: 9px;padding-right: 6px;')
					$.evCSSrule('div.GMapBox div.GMgroupControl div.brd div.ul ul li a', 'color: red;')
				}
				window.evGMapping_GMap2[gmap2_index].addControl(new GroupControl());
			})
			// создаем метод для создания иконок
			var get_icon_function=function($e, base_icon){
				var icon=new GIcon(base_icon || G_DEFAULT_ICON);
				if($e.attr('data-icon-png'))
					icon.image=$e.attr('data-icon-png');
				if($e.attr('data-icon-gif'))
					icon.printImage=$e.attr('data-icon-gif');
				if($e.attr('data-icon-size'))
					icon.iconSize=new GSize(parseInt($e.attr('data-icon-size').split(',')[0]), parseInt($e.attr('data-icon-size').split(',')[1]));
				if($e.attr('data-icon-anchor'))
					icon.iconAnchor=new GPoint(parseInt($e.attr('data-icon-anchor').split(',')[0]), parseInt($e.attr('data-icon-anchor').split(',')[1]));
				if($e.attr('data-icon-map')){
					var map=[];
					for(var j=0; j<$e.attr('data-icon-map').split(',').length; j++)
						map[map.length]=parseInt($e.attr('data-icon-map').split(',')[j]);
					icon.imageMap=map;
				}
				if($e.attr('data-shadow-png'))
					icon.shadow=$e.attr('data-shadow-png');
				if($e.attr('data-shadow-gif'))
					icon.printShadow=$e.attr('data-shadow-gif');
				if($e.attr('data-shadow-size'))
					icon.shadowSize=new GSize(parseInt($e.attr('data-shadow-size').split(',')[0]), parseInt($e.attr('data-shadow-size').split(',')[1]));
				return icon;
			}
			// создаем общую иконку для карты в целом
			var base_icon=get_icon_function($element);
			// кластерер и маркеры
			$element.find('div.GMgroup').each(function(){
				var $group_element=$(this);
				var max_visible_markers = parseInt($group_element.attr('data-max-visible-markers'));
				var min_markers_per_cluster = parseInt($group_element.attr('data-min-markers-per-cluster'));
				var clusterer = false;
				var group_icon=get_icon_function($group_element, base_icon);
				if(max_visible_markers && min_markers_per_cluster){
					try{
						clusterer = new Clusterer(window.evGMapping_GMap2[gmap2_index]);
					}catch(e){
						alert('You need to download and include Clusterer2.js\n\nhttp://www.acme.com/javascript/Clusterer2.js');
					}
					clusterer.SetMaxVisibleMarkers(max_visible_markers);
					clusterer.SetMinMarkersPerCluster(min_markers_per_cluster);
					if($group_element.data()){}
					clusterer.SetIcon(group_icon);
				}
				$group_element.children('div.GMpoint').each(function(){
					var $point_element=$(this);
					var lat__lng=$point_element.attr('data-coords');
					var lat=parseFloat(lat__lng.split(',')[0]);
					var lng=parseFloat(lat__lng.split(',')[1]);
					var point = new GLatLng(lat, lng);
					var point_icon = get_icon_function($point_element, group_icon);
					var marker = new GMarker(point, {icon: point_icon});
					if(clusterer){
						clusterer.AddMarker(marker, 'title');
					}else{
						window.evGMapping_GMap2[gmap2_index].addOverlay(marker);
					}
					var html=$point_element.children('div.htmlInfo').html();
					if(html){
						GEvent.addListener(marker, "click", (function(marker, html){
							return function(){
								marker.openInfoWindowHtml(html);
							}
						})(marker, html));
					}
				})
			});
		})
	},
	evSboxDecorate: function(hash){
		// hash: style skipFirst submitForm delay scroll maxHeight destroy redraw
		this.each(function(j){
			hash=(hash || {})
			var hash_options_str='style:true skipFirst:false submitForm:false delay:false scroll:true maxHeight:999 destroy:false redraw:false';
			for(var i=0; i<hash_options_str.split(' ').length; i++){
				var key__value=hash_options_str.split(' ')[i];
				var key=key__value.split(':')[0];
				var value=key__value.split(':')[1];
				if( value=='true' ){
					value=true;
				}else if( value=='false' ){
					value=false;
				}
				hash[key]=(hash[key] || value);
			}
			var $select=$(this);
			if($select.is('select')){
				if( hash.destroy ){
					var $wrap=$select.parent();
					$select.insertAfter($wrap).show();
					$wrap.remove();
					return;
				}else if( hash.redraw ){
					$select.evSboxDecorate( $select.data('initialHash') );
					return;
				}
				$select.data({initialHash:hash}).hide();
				var sbox_name=($select.attr('name') || '');
				if($select.parent().is('span.sboxDecorated')){
					var $wrap=$select.parent();
					// when redecoration happens, try to remove b.first, a.darr, span.options
					$wrap.children('b.first, a.darr, span.options').remove();
				}else{
					var $wrap=$select.wrap($(document.createElement('span')).addClass('sboxDecorated')).parent();
					if( hash.style ){
						$wrap.css({position:'relative', display:'block'});
					}
					$select.bind('change disable enable', function(evt){
						if( evt.type=='change' ){
							$select.evSboxDecorate({redraw:true});
						}else if( evt.type=='disable' ){
							$select[0].disabled=true;
							$wrap.addClass('disabled');
						}else if( evt.type=='enable' ){
							$select[0].disabled=false;
							$wrap.removeClass('disabled');
						}
					});
				}
				var $opts_scroll=$(document.createElement('span')).addClass('scroll');
				var selected=$select[0].selectedIndex;
				var opts_length=$select.children().length;
				var optionClickListener_=function(value){
					return function(evt){
						evt.preventDefault();
						var index=$select[0].options.selectedIndex;
						var sbox_value=$select[0].options[index].value;
						var $current_opt=$opts_scroll.children('a[rel='+ sbox_value +']').removeClass('active btmActive');
						var current_opt_text=$current_opt.children('b').text();
						$current_opt.empty().text(current_opt_text);
						var $new_opt=$opts_scroll.children('a[rel='+ value +']');
						var new_opt_text=$new_opt.text();
						$new_opt.addClass( $new_opt.is('a.btm')?'active btmActive':'active' ).empty().append($(document.createElement('b')).text(new_opt_text));
						$first.text(new_opt_text);
						var opts=$select[0].options;
						for(var i=0; i<opts.length; i++){
							if(opts[i].value==value){
								opts.selectedIndex=i;
								break;
							}
						}
						$select.trigger('change');
						if( hash.submitForm ){
							$select.parents('form').eq(0).submit();
						}
						if( $.browser.msie ){
							$(document).trigger('click');
						}
					}
				}
				var $first;
				$select.children().each(function(i){
					var option_text=$(this).text();
					var option_value=$(this).val();
					if(i==0){
						$first=$(document.createElement('span')).text(option_text).
							appendTo($(document.createElement('b')).addClass('first').prependTo($wrap));
					}
					if(i>0 || !hash.skipFirst){
						var $item=$(document.createElement('a')).attr({href:'#', rel:option_value}).text(option_text);
						$item.bind('click', optionClickListener_(option_value));
						if(i+1==opts_length){
							$item.addClass('btm');
						}
						if(i==selected){
							$item.empty().append($(document.createElement('b')).text(option_text));
							$item.addClass( $item.is('a.btm')?'active btmActive':'active' );
							$first.text(option_text);
						}
						$opts_scroll.append($item);
					}
				});
				if(typeof window['evSboxDecorateDelayArr']=='undefined'){
					window['evSboxDecorateDelayArr']=[];
				}
				window['evSboxDecorateDelayArr'].push(function(hash,index){
					var wrap_wh=[$wrap[0].offsetWidth,$wrap[0].offsetHeight];
					var $darr=$(document.createElement('a')).attr({href:'#'}).addClass('darr').appendTo($wrap);
					// finally add $opts_scroll
					// if we do it earlier, wrap_wh width and height wont be calculated coorectly
					var $span_options=$(document.createElement('span')).addClass('options').appendTo($wrap);
					$opts_scroll.appendTo($(document.createElement('span')).addClass('ofh').appendTo($span_options));
					// right there we add scrolling arrows
					if( hash.scroll ){
						$(document.createElement('span')).addClass('scrollArr scrollArrUp').appendTo($span_options);
						$(document.createElement('span')).addClass('scrollArr scrollArrDw').appendTo($span_options);
					}
					if( hash.style ){
						$.evCSSrule('span.sboxDecorated a.darr','height: '+wrap_wh[1]+'px;width: '+wrap_wh[0]+'px;');
						$.evCSSrule('span.sboxDecorated span.options','top: '+wrap_wh[1]+'px;');
					}
					// set click listener, it will show and hide options
					$darr.bind('click',function(evt){
						evt.preventDefault();
						evt.stopPropagation();
						if( $(this).parent('span.sboxDecorated').hasClass('disabled') ){return}
						$(this).blur();
						var $current_sbox=$(this).parent();
						var $current_opts=$current_sbox.children('span.options');
						// whether to show or to hide options
						if($current_sbox.children('span.options')[0].offsetHeight>0){
							// lets hide options
							window.evSboxDecorate_hide_function($current_sbox);
						}else{
							// lets show options
							// and hide other shown selectboxes if any
							if($('span.sboxDecoratedActive').length){
								window.evSboxDecorate_hide_function($('span.sboxDecoratedActive'));
							}
							// how much space is available under selectbox
							var darr_coords=$.evElementCoords(this);
							if( hash.scroll ){
								var space_height=$.evScreenHeight()+$.evScrollTop() - (darr_coords.top+darr_coords.height);
								// space before the bottom of the dropdown list end the browser window
								space_height-=darr_coords.height/2;
								space_height=Math.round(space_height);
								// minimal height of the dropdown list
								space_height=Math.max(space_height, 5*darr_coords.height);
								// shortly show then hide the dropdown in order to get its original height
								$current_sbox.addClass('sboxDecoratedActive');
								var original_height=$current_opts.css({visibility:'hidden'}).children()[0].offsetHeight;
								var height=Math.min(space_height, original_height, hash.maxHeight);
								$current_opts.css({visibility:'visible'}).children('span.ofh').css({height:height});
								// add mousemove listener if needed
								if(height<original_height){
									window['evSboxDecorate_scroll_listener']=function(evt){
										var opts_coords=$.evElementCoords(this);
										var layer_x=($.evScrollTop()+evt.clientY) - opts_coords.top;
										window['evSboxDecorate_scroll_k']=2*layer_x/opts_coords.height - 1;
									}
									$current_opts.bind('mousemove',window.evSboxDecorate_scroll_listener);
									var $scroll=$current_opts.children('span.ofh').children('span.scroll').css({top:0});
									window['evSboxDecorate_scroll_info']={
										e: $scroll[0],
										oh: original_height,
										h: height,
										t: 0,
										up: $current_opts.children('span.scrollArrUp')[0],
										dw: $current_opts.children('span.scrollArrDw')[0]
									};
									window['evSboxDecorate_scroll_function']=function(){
										if(window.evSboxDecorate_scroll_k<=0){
											// try to scroll down
											window.evSboxDecorate_scroll_info.t -= 10 * Math.pow(window.evSboxDecorate_scroll_k,3);
											if(window.evSboxDecorate_scroll_info.t > 0){
												window.evSboxDecorate_scroll_info.t=0
											}
										}else if(window.evSboxDecorate_scroll_k>0){
											// try to scroll up
											window.evSboxDecorate_scroll_info.t -= 10 * Math.pow(window.evSboxDecorate_scroll_k,3);
											if(window.evSboxDecorate_scroll_info.t < window.evSboxDecorate_scroll_info.h-window.evSboxDecorate_scroll_info.oh ){
												window.evSboxDecorate_scroll_info.t=window.evSboxDecorate_scroll_info.h-window.evSboxDecorate_scroll_info.oh;
											}
										}
										if(window.evSboxDecorate_scroll_k!=0){
											window.evSboxDecorate_scroll_info.e.style.top=window.evSboxDecorate_scroll_info.t+'px';
											if(window.evSboxDecorate_scroll_info.t<0){
												window.evSboxDecorate_scroll_info.up.style.display='block';
											}else{
												window.evSboxDecorate_scroll_info.up.style.display='none';
											}
											if(window.evSboxDecorate_scroll_info.oh + window.evSboxDecorate_scroll_info.t > window.evSboxDecorate_scroll_info.h ){
												window.evSboxDecorate_scroll_info.dw.style.display='block';
											}else{
												window.evSboxDecorate_scroll_info.dw.style.display='none';
											}
										}
									}
									window['evSboxDecorate_scroll_timer']=setInterval('window.evSboxDecorate_scroll_function()',10);
								}
							}else{
								$current_sbox.addClass('sboxDecoratedActive');
							}
						}
					})
					// set body click listener, it will hide the shown dropdown list
					if(typeof window['evSboxDecorate_document_click_listener']=='undefined'){
						window['evSboxDecorate_document_click_listener']=true;
						$(document).bind('click', function(evt){
							// check if there is any
							var $active_sbox=$('span.sboxDecoratedActive');
							if($active_sbox.length){
								var hide, cancel;
								var $target_sbox=$(evt.target).parents('span.sboxDecorated');
								if($target_sbox.length){
									if($target_sbox!=$active_sbox){
										hide=true;
										cancel=false;
									}else{
										hide=false;
										cancal=false;
									}
								}else{
									hide=true;
									cancel=true;
								}
								if(cancel){
									// prevent default
									evt.preventDefault();
								}
								if(hide){
									// hide selectbox
									window.evSboxDecorate_hide_function($active_sbox);
								}
							}
						})
					}
					if( hash.style ){
						evSboxDecorate_style_function=function(){
							if(typeof window['evSboxDecorate_css_rules']=='undefined'){
								window['evSboxDecorate_css_rules']=true;
								$.evCSSrule('span.sboxDecorated','z-index: 1;');
								$.evCSSrule('span.sboxDecorated b.first','position: relative;z-index: 1;');
								$.evCSSrule('span.sboxDecorated a.darr','position: absolute;z-index: 2;left: 0;top: 0;display: block;');
								$.evCSSrule('span.sboxDecorated span.options','position: absolute;z-index: 1;left: 0;display: none;');
								$.evCSSrule('span.sboxDecorated span.options span.scrollArr','position: absolute;left: 0;display: none;width: 100%;height: 1em;overflow: hidden;background: #fff;text-align: center;font-size: 1em;line-height: 1em;color: #000;');
								$.evCSSrule('span.sboxDecorated span.options span.scrollArrUp','top: 0;');
								$.evCSSrule('span.sboxDecorated span.options span.scrollArrUp:before','content: "∆";');
								$.evCSSrule('span.sboxDecorated span.options span.scrollArrDw','bottom: 0;');
								$.evCSSrule('span.sboxDecorated span.options span.scrollArrDw:before','content: "∇";');
								$.evCSSrule('span.sboxDecorated span.options span.ofh','position: relative;display: block;overflow: hidden;');
								$.evCSSrule('span.sboxDecorated span.options span.ofh span.scroll','position: relative;display: block;');
								$.evCSSrule('span.sboxDecoratedActive','z-index: 2;');
								$.evCSSrule('span.sboxDecoratedActive span.options','display: block;');
								$.evCSSrule('span.sboxDecorated span.options a','position: relative;display: block;');
								$.evCSSrule('span.sboxDecorated span.options b','position: relative;display: block;');
							}
						}
						evSboxDecorate_style_function();
						if($.browser.safari){
							setTimeout('evSboxDecorate_style_function()',10);
						}
					}
					// clear function to release some memory
					window['evSboxDecorateDelayArr'][index]=null;
				})
				window['evSboxDecorate_hide_function']=function($current_sbox){
					$current_sbox.removeClass('sboxDecoratedActive');
					$current_sbox.children('span.options').unbind('mousemove', window.evSboxDecorate_ofh_listener).
						children('span.ofh').css({height:'auto'});
					if(window.evSboxDecorate_scroll_timer){
						window.evSboxDecorate_scroll_k=0;
						clearInterval(window.evSboxDecorate_scroll_timer);
					}
				}
				var index=window.evSboxDecorateDelayArr.length-1;
				if( hash.delay ){
					var hash_str=[];
					for(var key in hash){
						hash_str.push(key+':'+hash[key].toString());
					}
					setTimeout('window.evSboxDecorateDelayArr['+index+']({'+hash_str.join(',')+'},'+index+')',50);
				}else{
					window.evSboxDecorateDelayArr[index](hash,index);
				}
			}
		})
		return this;
	}
});
