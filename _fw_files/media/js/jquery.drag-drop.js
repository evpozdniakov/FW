// hash.callback — вызывается при всех событиях start, drag, drop
// hash.handle - часть, за которую можно перетащить
// hash.dragClass - класс, который добавляется перетаскиваемому блоку
// hash.opacity - непрозрачность при перетаскивании
// hash.left & hash.top - не помню

$.fn.extend({
	dragDrop: function(hash){
		this.each(function(){
			//придумываем идентификатор объекту, чтобы отличать его от других
			var $target=$(this);
			var random_id='random_id_'+Math.random().toString().substr(2);
			if(!window['C'])C={};
			if(!C['__dragDrop__'])C.__dragDrop__=[];
			C.__dragDrop__[random_id]={};
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
			if(hash.handle){
				var $handle=$target.find(hash.handle);
			}else{
				var $handle=$target;
			}
			$handle.mousedown(function(evt){
				evt.preventDefault();
				C.__dragDrop__[random_id].is_drag=true;
				C.__dragDrop__[random_id].delta_xy=[
					evt.clientX - $target[0].offsetLeft,
					evt.clientY - $target[0].offsetTop
				];
				var dragClass=(hash.dragClass || 'drag');
				var opacity=(hash.opacity || 1);
				$target.addClass(dragClass).css({opacity:opacity});
				// дергаем hash.callback
				callback_trigger_function(hash,evt,'start',[$target[0].offsetLeft,$target[0].offsetTop]);
			});
			//реагируем на drag
			$('body').mousemove(function(evt){
				evt.preventDefault();
				if(C.__dragDrop__[random_id].is_drag){
					var lt=[
						evt.clientX - C.__dragDrop__[random_id].delta_xy[0],
						evt.clientY - C.__dragDrop__[random_id].delta_xy[1],
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
				if(C.__dragDrop__[random_id].is_drag){
					//если происходит перетаскивание, то заканчиваем его
					C.__dragDrop__[random_id].is_drag=false;
					C.__dragDrop__[random_id].delta_xy=null;
					var dragClass=(hash.dragClass || 'drag');
					$target.removeClass(dragClass).css({opacity:1});
					callback_trigger_function(hash,evt,'drop',[$target[0].offsetLeft,$target[0].offsetTop]);
				}
			});
		})
		return this;
	}
})