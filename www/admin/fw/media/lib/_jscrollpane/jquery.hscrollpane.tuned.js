jQuery.hjScrollPane = {
	active : []
};
jQuery.fn.hjScrollPane = function(settings)
{
	settings = jQuery.extend(
		{
			scrollbarHeight : 10,
			scrollbarMargin : 5,
			wheelSpeed : 18,
			showArrows : false,
			arrowSize : 0,
			animateTo : false,
			dragMinWidth : 1,
			dragMaxWidth : 99999,
			animateInterval : 100,
			animateStep: 3,
			maintainPosition: true
		}, settings
	);
	return this.each(
		function()
		{
			var $this = jQuery(this);

			//сравниваем размер scrollHeight и offsetHeight, 
			//продолжаем работу лишь если scrollHeight >= offsetHeight
			//вообще-то сравнение ">=" появилось вметсо ">" благодаря глюку эксплорера
			if($this.get(0).scrollWidth >= $this.get(0).offsetWidth){
				if (jQuery(this).parent().is('.hjScrollPaneContainer')) {
					var currentScrollPosition = settings.maintainPosition ? $this.offset({relativeTo:jQuery(this).parent()[0]}).l : 0;
					var $c = jQuery(this).parent();
					var paneHeight = $c.innerHeight();
					var paneWidth = $c.outerWidth();
					var trackWidth = paneWidth;
					if ($c.unmousewheel) {
						$c.unmousewheel();
					}
					jQuery('>.hjScrollPaneTrack, >.hjScrollArrowLeft, >.hjScrollArrowRight', $c).remove();
					$this.css({'top':0});
				} else {
					var currentScrollPosition = 0;
					this.originalPadding = $this.css('paddingTop') + ' ' + $this.css('paddingRight') + ' ' + $this.css('paddingBottom') + ' ' + $this.css('paddingLeft');
					this.originalSidePaddingTotal = (parseInt($this.css('paddingLeft')) || 0) + (parseInt($this.css('paddingRight')) || 0);
					var paneWidth = $this.innerWidth();
					var paneHeight = $this.innerHeight();
					var trackWidth = paneWidth;

					$this.wrap(
						jQuery('<div></div>').attr(
							{'className':'hjScrollPaneContainer'}
						).css(
							{
								'height':paneHeight+'px', 
								'width':paneWidth+'px'
							}
						)
					);
					// deal with text size changes (if the jquery.em plugin is included)
					// and re-initialise the scrollPane so the track maintains the
					// correct size
					jQuery(document).bind(
						'emchange', 
						function(e, cur, prev)
						{
							$this.hjScrollPane(settings);
						}
					);
				}
				var p = this.originalSidePaddingTotal;
				$this.css(
					{
						'width':'auto',
						'height':paneHeight - settings.scrollbarHeight - settings.scrollbarMargin - p + 'px',
						'paddingBottom':settings.scrollbarMargin + 'px'
					}
				);
				var contentWidth = $this.children().eq(0).outerWidth();
				var percentInView = paneWidth / contentWidth;

				if (percentInView < .99) {
					var $container = $this.parent();
					$container.append(
						jQuery('<div></div>').attr({'className':'hjScrollPaneTrack'}).css({'height':settings.scrollbarHeight+'px'}).append(
							jQuery('<div></div>').attr({'className':'hjScrollPaneDrag'}).css({'height':settings.scrollbarHeight+'px'}).append(
								jQuery('<div></div>').attr({'className':'hjScrollPaneDragLeft'}).css({'height':settings.scrollbarHeight+'px'}),
								jQuery('<div></div>').attr({'className':'hjScrollPaneDragRight'}).css({'height':settings.scrollbarHeight+'px'})
							)
						)
					);
					
					var $track = jQuery('>.hjScrollPaneTrack', $container);
					var $drag = jQuery('>.hjScrollPaneTrack .hjScrollPaneDrag', $container);
					
					if (settings.showArrows) {
						
						var currentArrowButton;
						var currentArrowDirection;
						var currentArrowInterval;
						var currentArrowInc;
						var whileArrowButtonDown = function()
						{
							if (currentArrowInc > 4 || currentArrowInc%4==0) {
								positionDrag(dragPosition + currentArrowDirection * mouseWheelMultiplier);
							}
							currentArrowInc ++;
						};
						var onArrowMouseUp = function(event)
						{
							jQuery('body').unbind('mouseup', onArrowMouseUp);
							currentArrowButton.removeClass('hjScrollActiveArrowButton');
							clearInterval(currentArrowInterval);
							//console.log($(event.target));
							//currentArrowButton.parent().removeClass('hjScrollArrowLeftClicked hjScrollArrowRightClicked');
						};
						var onArrowMouseDown = function() {
							//console.log(direction);
							//currentArrowButton = $(this);
							jQuery('body').bind('mouseup', onArrowMouseUp);
							currentArrowButton.addClass('hjScrollActiveArrowButton');
							currentArrowInc = 0;
							whileArrowButtonDown();
							currentArrowInterval = setInterval(whileArrowButtonDown, 100);
						};
						$container
							.append(
								jQuery('<a></a>')
									.attr({'href':'javascript:;', 'className':'hjScrollArrowLeft'})
									.css({'height':settings.scrollbarHeight+'px'})
									.html('Scroll up')
									.bind('mousedown', function()
									{
										currentArrowButton = jQuery(this);
										currentArrowDirection = -1;
										onArrowMouseDown();
										this.blur();
										return false;
									}),
								jQuery('<a></a>')
									.attr({'href':'javascript:;', 'className':'hjScrollArrowRight'})
									.css({'height':settings.scrollbarHeight+'px'})
									.html('Scroll down')
									.bind('mousedown', function()
									{
										currentArrowButton = jQuery(this);
										currentArrowDirection = 1;
										onArrowMouseDown();
										this.blur();
										return false;
									})
							);
						if (settings.arrowSize) {
							trackWidth = paneWidth - settings.arrowSize - settings.arrowSize;
							$track
								.css({'width': trackWidth+'px', left:settings.arrowSize+'px'})
						} else {
							var leftArrowWidth = jQuery('>.hjScrollArrowLeft', $container).width();
							settings.arrowSize = leftArrowWidth;
							trackWidth = paneWidth - leftArrowWidth - jQuery('>.jScrollArrowRight', $container).width();
							$track
								.css({'width': trackWidth+'px', left:leftArrowWidth+'px'})
						}
					}
					
					var $pane = jQuery(this).css({'position':'absolute', 'overflow':'visible'});
					
					var currentOffset;
					var maxX;
					var mouseWheelMultiplier;
					// store this in a seperate variable so we can keep track more accurately than just updating the css property..
					var dragPosition = 0;
					var dragMiddle = percentInView*paneWidth/2;
					
					// pos function borrowed from tooltip plugin and adapted...
					var getPos = function (event, c) {
						var p = (c == 'X') ? 'Left' : 'Top';
						var res=event['page' + c] || (event['client' + c] + (document.documentElement['scroll' + p] || document.body['scroll' + p])) || 0;
						return res;
					};
					
					var ignoreNativeDrag = function() {	return false; };
					
					var initDrag = function()
					{
						ceaseAnimation();
						currentOffset = $drag.offset(false);
						currentOffset.left -= dragPosition;
						maxX = trackWidth - $drag[0].offsetWidth;
						mouseWheelMultiplier = 2 * settings.wheelSpeed * maxX / contentWidth;
					};
					
					var onStartDrag = function(event)
					{
						initDrag();
						dragMiddle = getPos(event, 'X') - dragPosition - currentOffset.left;
						jQuery('body').bind('mouseup', onStopDrag).bind('mousemove', updateScroll);
						if (jQuery.browser.msie) {
							jQuery('body').bind('dragstart', ignoreNativeDrag).bind('selectstart', ignoreNativeDrag);
						}
						return false;
					};
					var onStopDrag = function()
					{
						jQuery('body').unbind('mouseup', onStopDrag).unbind('mousemove', updateScroll);
						dragMiddle = percentInView*paneWidth/2;
						if (jQuery.browser.msie) {
							jQuery('body').unbind('dragstart', ignoreNativeDrag).unbind('selectstart', ignoreNativeDrag);
						}
					};
					var positionDrag = function(destX)
					{
						destX = destX < 0 ? 0 : (destX > maxX ? maxX : destX);
						dragPosition = destX;

						$drag.css({'left':destX+'px'});
						var p = destX / maxX;
						$pane.css({'left':((paneWidth-contentWidth)*p) + 'px'});
						$this.trigger('scroll');
					};
					var updateScroll = function(e)
					{
						positionDrag(getPos(e, 'X') - currentOffset.left - dragMiddle);
					};
					
					var dragW = Math.max(Math.min(percentInView*(paneWidth-settings.arrowSize*2), settings.dragMaxWidth), settings.dragMinWidth);
					
					$drag.css(
						{'width':dragW+'px'}
					).bind('mousedown', onStartDrag);
					
					var trackScrollInterval;
					var trackScrollInc;
					var trackScrollMousePos;
					var doTrackScroll = function()
					{
						if (trackScrollInc > 8 || trackScrollInc%4==0) {
							positionDrag((dragPosition - ((dragPosition - trackScrollMousePos) / 2)));
						}
						trackScrollInc ++;
					};
					var onStopTrackClick = function()
					{
						clearInterval(trackScrollInterval);
						jQuery('body').unbind('mouseup', onStopTrackClick).unbind('mousemove', onTrackMouseMove);
					};
					var onTrackMouseMove = function(event)
					{
						trackScrollMousePos = getPos(event, 'X') - currentOffset.left - dragMiddle;
					};
					var onTrackClick = function(event)
					{
						initDrag();
						onTrackMouseMove(event);
						trackScrollInc = 0;
						jQuery('body').bind('mouseup', onStopTrackClick).bind('mousemove', onTrackMouseMove);
						trackScrollInterval = setInterval(doTrackScroll, 100);
						doTrackScroll();
					};
					
					$track.bind('mousedown', onTrackClick);
					
					// if the mousewheel plugin has been included then also react to the mousewheel
					if ($container.mousewheel) {
						$container.mousewheel(
							function (event, delta) {
								initDrag();
								ceaseAnimation();
								var d = dragPosition;
								positionDrag(dragPosition - delta * mouseWheelMultiplier);
								var dragOccured = d != dragPosition;
								return !dragOccured;
							},
							false
						);					
					}
					var _animateToPosition;
					var _animateToInterval;
					function animateToPosition()
					{
						var diff = (_animateToPosition - dragPosition) / settings.animateStep;
						if (diff > 1 || diff < -1) {
							positionDrag(dragPosition + diff);
						} else {
							positionDrag(_animateToPosition);
							ceaseAnimation();
						}
					}
					var ceaseAnimation = function()
					{
						if (_animateToInterval) {
							clearInterval(_animateToInterval);
							delete _animateToPosition;
						}
					};
					var scrollTo = function(pos, preventAni)
					{
						if (typeof pos == "string") {
							$e = jQuery(pos, this);
							if (!$e.length) return;
							pos = $e.offset({relativeTo:this}).top;
						}
						ceaseAnimation();
						var destDragPosition = -pos/(paneWidth-contentWidth) * maxX;
						if (!preventAni || settings.animateTo) {
							_animateToPosition = destDragPosition;
							_animateToInterval = setInterval(animateToPosition, settings.animateInterval);
						} else {
							positionDrag(destDragPosition);
						}
					};
					$this[0].scrollTo = scrollTo;
					
					$this[0].scrollBy = function(delta)
					{
						var currentPos = -parseInt($pane.css('left')) || 0;
						scrollTo(currentPos + delta);
					};
					
					initDrag();
					
					scrollTo(-currentScrollPosition, true);
					
					var add_to_active=true;
					for(var i=0; i<jQuery.hjScrollPane.active.length; i++){
						if(jQuery.hjScrollPane.active[i]==$this[0]){
							add_to_active=false;
						}
					}
					if(add_to_active){
						jQuery.hjScrollPane.active.push($this[0]);
					}

				} else {
					$this.css(
						{
							'width':paneWidth+'px',
							'height':paneHeight-this.originalSidePaddingTotal+'px',
							'padding':this.originalPadding
						}
					);
					// remove from active list?
				}
			}
			
			
		}
	)
};

// clean up the scrollTo expandos
jQuery(window)
	.bind('unload', function() {
		var els = jQuery.hjScrollPane.active; 
		for (var i=0; i<els.length; i++) {
			els[i].scrollTo = els[i].scrollBy = null;
		}
	}
);