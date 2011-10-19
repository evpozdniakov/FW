$(document).ready(function(){
	if($.browser.msie && $.browser.version<=7){
		var $main_td=$(document.createElement('td')).attr({id:'mainTd'}).
			appendTo($(document.createElement('tr')).
				appendTo($(document.createElement('table')).attr({id:'mainTbl'}).
					appendTo('body')));
		$('body').css({position:'absolute',width: '100%',height: '100%'}).children('div').appendTo($main_td);
		$('html').css({position:'static',width: '100%',height: '100%'})
	}
})
