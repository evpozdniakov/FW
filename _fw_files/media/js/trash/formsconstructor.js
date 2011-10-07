/**
*	Валидация форм formsconstructor
*
*/
var forms_js = {
	formsconstructorValidation: function(formsc){
		var ofields = formsc.find('.obligfield');
		var is_valid = true;
		$.each(ofields, function(a,b){
			if(!is_valid)return false;
			var v = forms_js.fieldNotValid(b);
			if(v){
				var capt = '«' + $.trim(b.title) + '» ';
				if(v== -1) alert('Вы не заполнили поле ' + capt)
					else alert('Поле ' + capt + v)
				b.focus();
				is_valid = false
			}
		})
		if(!is_valid)return false;
		// чекбоксы и радио:
		ofields = formsc.find("input:checkbox.formItemChbox.obligfield, input:radio.obligfield")
		var c =[]
		$.map(ofields, function(i){
	    	var n = $(i).attr('name')
	    	if($.inArray(n, c ) == -1) c.push(n)
	    })
		$.each(c, function(a,b){
			if(!is_valid)return false;
			if(!formsc.find("input:checked.obligfield[@name='"+b+"']").size()){
				var ii = formsc.find("input.obligfield[@name='"+b+"']")[0]
				var capt = '«' + ii.title + '»';
				alert('Поле ' + capt + ' - не выбрано значение')
				is_valid = false
				ii.focus()
			}
		})
		return is_valid;
	},
	/**
	* Валидация поля - для пустого возвращает -1, при несоответствии формата строку, а в остальных случаях - false
	*/
	fieldNotValid: function(f){
		var field = $(f); var t_v = $.trim(field.val())
		if(!t_v) {
			if(field.is('.formItemSbox')) return 'выберите значение';
			return -1
		}
		//if(field.is('.formItemText') || field.is('.formItemArea'))
		//	return t_v ? false : -1
		//else 
		if(field.is('.formItemEmail')){
			var re = /^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,7}$/;
           return re.test(t_v) ? false : 'не соответствует формату'
		}
		return false
	}
}
jQuery(document).ready(function(){
	$('div.formsc form').submit(function(){
		return forms_js.formsconstructorValidation($('div.formsc form'))
	})
});
