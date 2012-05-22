$(document).ready(function(){
	A.init();
})

var A={
	init: function(){
		A.style();
		// type filter
		A.tf.init();
		// model items list
		A.mil.init();
		// model item edit zone
		A.miez.init();
		// model icons
		A.icons.init();
		// login
		A.login.init();
	},
	style: function(){
		// layout
		if($.browser.msie && $.browser.version<=6){
			var $main_td=$(document.createElement('td')).attr({id:'mainTd'}).
				appendTo($(document.createElement('tr')).
					appendTo($(document.createElement('table')).attr({id:'mainTbl'}).
						appendTo('body')));
			$('body').css({position:'absolute',width: '100%',height: '100%'}).children('div').appendTo($main_td);
			$('html').css({position:'static',width: '100%',height: '100%'})
		}
		// controls
		var $controls=$('#controls');
		if( $controls.length ){
			$controls.wrap('<div id="adminControls"/>');
			$('#adminControls').prepend('<div class="border"></div>');
			// if($.evIE6()){ $admin_controls.evPositionFixed({attach:'lb'}) }
		}
		// show items
		var $model_items=$('#modelItemsList div.modelItems');
		if($model_items.length){
			$model_items.show();
		}
	},
	tf: {
		init: function(){
			var $form=$('#typeFilterForm');
			$form.ajaxForm({
				beforeSubmit: function(){$('#ajaxLoader').show()},
				method: 'post',
				dataType: 'json',
				success: function(json){
					$('#modelItemsList div.modelItems').empty().append(json.html);
					$('#ajaxLoader').hide();
					A.tf.decorate($form);
				}
			});
			$form.find('a.reset').bind('click',function(evt){
				evt.preventDefault();
				$form.find('input').val('');
				$form.submit();
			})
		},
		decorate: function($form){
			if( $form.find('input').val() ){
				$form.addClass('decorated');
			}else{
				$form.removeClass('decorated');
				A.mil.init();
			}
		}
	},
	mil: {
		init: function(){
			A.mil.bindPlusMinusClick();
		},
		bindPlusMinusClick: function($parent){
			if(!$parent){
				$parent=$('#modelItemsList div.modelItems');
			}
			if ($parent.length) {
				$parent.find('i.icon').each(function(){
					$(this).bind('click',function(event){
						event.preventDefault();
						A.mil.getToggleChildren($(this));
					})
				})
	  	}
		},
		up: function(model_name,id){
			A.mil.updown(model_name,id,'up');
		},
		down: function(model_name,id){
			A.mil.updown(model_name,id,'down');
		},
		updown: function(model_name,id,action){
			var hash={};
			hash[model_name+'[id]']=id;
			hash['tf']=(document.forms['model_'+model_name+'_form'].elements['tf'].value || '');
			hash.action=action;
			$.getJSON('./?json=1',hash,function(json){
				if(json.id){
					var $current_element_node=$('#'+model_name+id);
					//ищем на странице элемент, с которым поменялись местами
					var $next_element_node=$('#'+model_name+json.id);
					if($next_element_node.length){
						//меняем местами содержимое элементов
						//меняем идентификаторы
						var current_element_inner_html=$current_element_node.html();
						var next_element_inner_html=$next_element_node.html();
						$current_element_node.empty().append(next_element_inner_html).attr('id',model_name+json.id);
						$next_element_node.empty().append(current_element_inner_html).attr('id',model_name+id);
						//если next_element_node находится внутри страницы, которая свернута, то разворачиваем ее
						A.mil.try2expandPage($next_element_node);
					}else{
						//если $next_element_node отсутствует на странице, 
						//то скрываем страницу с которой он ушел (удаляя из нее элементы)
						//и вытаскиваем новую страницу
						A.mil.try2retrivePage($current_element_node,action);
					}
				}
				//скрываем ajax-loader
				$('#ajaxLoader').hide();
			});
			//показываем ajax-loader
			$('#ajaxLoader').show();
		},
		try2expandPage: function($next_element_node){
			//пытаемся найти node plusminus
			try{
				var $a_plus=$next_element_node.parent().prev().children('a.plus');
				if($a_plus.length){
					var $children=A.mil.getChildren($a_plus);
					A.mil.toggleChildren($a_plus,$children);
				}
			}
			catch(e){/*alert(e)*/}
		},
		try2retrivePage: function($current_element_node,action){
			try{
				//находим ссылку a.plus выше или ниже текущей
				$parent_li=$current_element_node.parent().parent().parent();
				$a_plus=((action=='up')?$parent_li.prev():$parent_li.next()).find('div.icon.text a.plus');
				//если такая нашлась
				if($a_plus.length){
					//сворачиваем текущую страницу
					var $a_minus=$parent_li.find('div.icon.text i.icon.minus');
					if($a_minus.length){
						var $children=A.mil.getChildren($a_minus);
						A.mil.toggleChildren($a_minus,$children);
					}
					//и удаляем из нее все элементы
					$current_element_node.parent().remove();
					//выполняем как бы клик по ссылке
					$a_plus.trigger('click');
				}
			}
			catch(e){/*alert(e)*/}
		},
		getChildren: function($node){
			var $children=$node.parent().parent().children('ul');
			return $children;
		},
		getToggleChildren: function($node){
			//определяем, есть ли у элемента детки
			var $children=A.mil.getChildren($node);
			if($children.length){
				//дети присутствуют
				A.mil.toggleChildren($node,$children);
			}else{
				//дети отсутствуют, нужно их получить
				$.getJSON($node.attr('href'),function(json){
					$node.parent().parent().append(json.html);
					$children=$node.parent().parent().children('ul.itemsList').hide();
					A.mil.bindPlusMinusClick($children);
					//вызываем повторно себя же
					A.mil.getToggleChildren($node);
					//скрываем ajax-loader
					$('#ajaxLoader').hide();
				});
				//показываем ajax-loader
				$('#ajaxLoader').show();
			}
			return false;
		},
		toggleChildren: function($node,$children){
			//дети присутствуют, определяем нужно ли их скрыть или показать
			$node.children('span').text(($children[0].offsetHeight)?'+':'-');
			$node.toggleClass('minus').toggleClass('plus');
			if($.browser.msie && $.browser.version<7){
				if(($children[0].offsetHeight)){
					$children.hide('fast');
				}else{
					$children.show('fast');
				}
			}else{
				$children[ $.evIE('slideToggle') ]('fast');
			}
		}
	},
	miez: {
		init: function(){
			var $miez_form=$('#modelItemEditZone form');
			if($miez_form.length){
				A.miez.errors($miez_form);
				A.miez.datepicker($miez_form);
				$miez_form.find('div.core input[name$=]_check_all]').bind('click', function(evt){
					A.miez.checkSameCoreItems(this)
				})
				$miez_form.find('div.core input[name$=[__delete__]]').bind('click', function(evt){
					setTimeout('A.miez.toggleDeleteBtn()',100);
				})
			}
			$('#controls button.delete').bind('click', function(evt){
		        return confirm('Удалить безвозвратно?');
			})
			if(typeof Shadowbox=='object'){
				Shadowbox.init({
					language: 'ru',
					players: ["img","flv"],
					options: {handleOversize:'drag'}
				});
			}
		},
		errors: function($miez_form){
			var $alert_box=$miez_form.find('div.alertBox');
			if($alert_box.length){
				var $clone=$alert_box.clone();
				$clone.find('p.alert').css({marginBottom:'1em'});
				$clone.dialog({modal:true,title:'Внимание!'});
				// var msg=$p_alert.children('span').html().split('<br>').join('\n');
				// alert(msg);
			}
		},
		datepicker: function($miez_form){
			$miez_form.find('input.datepicker').datepicker({howOtherMonths:true});
		},
		checkSameCoreItems: function(chbox){
			// в пределах привязанной модели(!) 
			// пробегаемся по всем чекбоксам с точно таким же [содержимым] атрибута name
			// как у текущего чекбокса и помечаем их (или убираем пометку)
			var $div_core=$(chbox).parents('div.core').eq(0);
			var str_in_brackets=$(chbox).attr('name');
			var pos=[str_in_brackets.indexOf('['),str_in_brackets.indexOf(']')];
			str_in_brackets=str_in_brackets.substr(pos[0], 1+pos[1]-pos[0]);
			$div_core.find('input[name$='+str_in_brackets+']').each(function(i){
				this.checked=chbox.checked;
			})
			if(str_in_brackets=='[__delete__]'){
				A.miez.toggleDeleteBtn();
			}
		},
		toggleDeleteBtn: function(){
			// проверяем остались ли помеченные чекбоксы в любой из привязанных моделей
			var $all_chboxes=$('#modelItemEditZone div.core input[name$=[__delete__]]');
			var show__hide='show';
			$all_chboxes.each(function(i){
				if(this.checked){
					show__hide='hide';
					return false;
				}
			})
			$('#adminControls input[name=delete]')[show__hide]();
		},
		MTMfieldTakeAll: function(manage_all){
			//поле MTM реализовано так, что в форме может появляться либо селектбокс, либо чекбоксы
			//поэтому реализация функции будет состоять из двух частей.

			//определяем название селектбокса или чекбокса, с которым работать
			var box_name=manage_all.name.substr(0,(manage_all.name.length-11))+'[]';//alert(box_name);
			//определяем сам элемент селектбокса или чекбокса
			var box_or_chbox_arr=(manage_all.form).elements[box_name];
			//делаем ветвление логики, в зависимости от типа элемента (селектбокс или чекбокс)
			if(box_or_chbox_arr.tagName=='SELECT'){
				//пробегаемся по опшинам и либо включаем, либо выключаем их
				var sbox=box_or_chbox_arr;
				for(var i=0; i<sbox.options.length; i++){
					sbox.options[i].selected=manage_all.checked;
				}
			}else{
				var chbox_arr=box_or_chbox_arr;
				for(var i=0; i<chbox_arr.length; i++){
					chbox_arr[i].checked=manage_all.checked;
				}
			}
		}
	},
	icons: {
		init: function(){
			var $form=$('#model__models_form');
			if($form.length){
				$.ajax({
					type: 'GET',
					url: 'http://fw.bumagi.net/__scripts/modeliconlib_data.php',
					dataType: 'jsonp',
					data: {},
					jsonp: 'jsonp_callback',
					success: function(data){
						if(data && data.length){
							// создаем скрытое поле
							$('#model__models_form p.icon > span.tag').
								append('<input type="hidden" name="_models[icon_from_lib]"/>');
							// создаем диалог
							A.icons.makeDialog(data);
							// создаем ссылку
							A.icons.makeLink();
						}
					}
				});
				
			}
		},
		makeDialog: function(data){
			var $div=$('<div id="iconsDialogBox"/>').appendTo('body');
			for(var i=0; i<data.length; i++){
				var src='http://fw.bumagi.net'+data[i].icon_uri;
				$div.
					append($(document.createElement('span')).addClass('icon').
						append($(document.createElement('a')).attr({href:'#'}).
							append($(document.createElement('img')).attr({src:src,width:16,height:16}))));
			}
			$div.find('a').bind('click',function(evt){
				evt.preventDefault();
				var src=$(this).children('img').attr('src');
				var $span_tag=$('#model__models_form p.icon > span.tag');
				// скрываем поле для подгрузки иконки и ссылку на диалог
				$span_tag.find('input[name=_models[icon]]').hide();
				$('#openIconDialog').hide();
				// скрываем предыдущую картинку и чекбокс, если есть
				$span_tag.parent().children('span.fileInfo').hide();
				$span_tag.parent().children('span.fileUnlink').hide();
				// помечаем чекбокс, чтобы произошло удаление старой иконки
				if( $('#_models_icon_del_on_id').length ){
					$('#_models_icon_del_on_id')[0].checked=true;
				}
				// добавляем ссылку на иконку в скрытое поле
				$span_tag.find('input[name=_models[icon_from_lib]]')[0].value=src;
				// показываем выбранную иконку и ссылку на ее отмену
				var $img=$(document.createElement('img')).attr({src:src, width:16, height:16}).
					css({verticalAlign:'middle', marginRight:8, border:'2px solid #000'}).appendTo($span_tag);
				$('<a href="#">отменить</a>').appendTo($span_tag).bind('click', function(evt){
						evt.preventDefault();
						$img.remove();
						$(this).remove();
						// показываем все что было скрыто
						$span_tag.find('input[name=_models[icon_from_lib]]')[0].value='';
						$span_tag.find('input[name=_models[icon]]').show();
						$span_tag.parent().children('span.fileInfo').show();
						$span_tag.parent().children('span.fileUnlink').show();
						// помечаем чекбокс, чтобы произошло удаление старой иконки
						if( $('#_models_icon_del_on_id').length ){
							$('#_models_icon_del_on_id')[0].checked=false;
						}
						$('#openIconDialog').show();
					})
				// скрываем диалог
				$div.dialog('close');
			})
			$div.append('<span class="clear"/>')
			$div.dialog({
				autoOpen: false,
				modal: true,
				title: 'Выберите иконку'
			})
		},
		makeLink: function(){
			$('#model__models_form p.icon > span.tag').
				append('<a id="openIconDialog" href="#">выбрать из библиотеки</a>').
				bind('click',function(evt){
					evt.preventDefault();
					$('#iconsDialogBox').dialog('open');
			})
		}
	},
	login: {
		init: function(){
			var $login_form=$('#loginForm');
			if( $login_form.length ){
				$("#loginField")[0].focus();
			}
		}
	}
}
