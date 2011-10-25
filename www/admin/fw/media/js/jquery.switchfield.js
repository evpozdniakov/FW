$.fn.extend({
	evSwitchField: function(value){
		this.each(function(){
			var $field=$(this);
			if($field.attr('type')!='password'){
				value=(value || $field.attr('value'));
				$field.evSwitchInputField(value);
			}else{
				value=(value || 'Пароль');
				$field.evSwitchPasswordField(value);
			}
		})
		return this;
	},
	evSwitchInputField: function(value){
		this.each(function(){
			$(this).bind('focus blur',function(event){
				if(event.type=='focus' && $.trim(event.target.value)==value){
					event.target.value='';
					$(event.target).removeClass('helpText')
				}else if(event.type=='blur' && $.trim(event.target.value)==''){
					$(event.target).addClass('helpText')
					event.target.value=value;
				}
			}).trigger('blur');
		})
		return this;
	},
	evSwitchPasswordField: function(value){
		this.each(function(){
			var $field=$(this);
			$field.bind('focus blur',function(event){
				var field_name=this.name;
				if(event.type=='focus' && event.target.value==value){
					var $input_pswd=$(document.createElement('input')).attr({
						type:'password',
						name:field_name,
						maxlength:16
					}).insertAfter(this);
					$input_pswd[0].focus();
					$input_pswd.evSwitchPasswordField(value,true);
					$(this).remove();
				}else if(event.type=='blur' && event.target.value==''){
					var $input_txt=$(document.createElement('input')).attr({
						name:field_name,
						maxlength:16,
						value:value,
						className:'helpText'
					}).insertAfter(this);
					$(this).remove();
					$input_txt.evSwitchPasswordField(value,true);
				}
			});
			if(!arguments[1] && $field.attr('type')=='password'){
				$field.trigger('blur');
			}
		})
		return this;
	}
})