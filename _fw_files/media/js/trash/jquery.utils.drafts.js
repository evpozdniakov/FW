evCaret: function(begin,end) {
	if (this.length == 0) return;
	if (typeof begin == 'number') {
		end = (typeof end == 'number') ? end : begin;
		return this.each(function() {
			if (this.setSelectionRange) {
				this.focus();
				this.setSelectionRange(begin, end);
			} else if (this.createTextRange) {
				var range = this.createTextRange();
				range.collapse(true);
				range.moveEnd('character', end);
				range.moveStart('character', begin);
				range.select();
			}
		});
	} else {
		if (this[0].setSelectionRange) {
			begin = this[0].selectionStart;
			end = this[0].selectionEnd;
		} else if (document.selection && document.selection.createRange) {
			var range = document.selection.createRange();
			begin = 0 - range.duplicate().moveStart('character', -100000);
			end = begin + range.text.length;
		}
		return { begin: begin, end: end };
	}
},
evMask: function(re){
	re=re.toString();
	re=re.substr(1,re.length-2);
	console.log('re',re);
	var re_arr=[];
	var current_char, mode, buffer;
	for(var i=0; i<re.length; i++){
		current_char=re.substr(i,1);
		if(current_char=='['){
			var contents=re.substr(i+1, re.indexOf(']',i)-i-1);
			i+=contents.length+1;
			re_arr.push('['+contents+']');
		}else if(current_char=='\\'){
			i+=1;
			re_arr.push('\\'+re.substr(i,1));
		}else if(current_char=='{'){
			var multi=parseInt(re.substr(i+1));
			i+=multi.toString().length+1
			var last_re=re_arr[re_arr.length-1];
			for(var j=1; j<multi; j++)
				re_arr.push(last_re);
		}else if(current_char=='?' || current_char=='+' || current_char=='*'){
			re_arr[re_arr.length-1]+=current_char;
		}else{
			re_arr.push(current_char);
		}
	}
	console.log('re_arr',re_arr);
	this.each(function(){
		$(this).bind('keydown mousedown', function(evt){
			$(this).attr({'data-value':this.value, 'data-caret':$(this).evCaret().begin});
		})
		$(this).bind('keyup mouseup', function(evt){
			var final_value=($(this).attr('data-value') || "");
			console.log('re_arr.length',re_arr.length);
			for(var i=0; i<re_arr.length; i++){
				console.log('i='+i);
				var re='';
				for(var j=0; j<=i; j++){
					re+=re_arr[j];
				}
				re=new RegExp(re);
				console.log('re',re.toString());
				console.log('math',this.value.match(re));
				if( !this.value.match(re) ){
					this.value=final_value;
					$(this).evCaret($(this).attr('data-caret'));
					console.log('break');
					break;
				}else{
					final_value=this.value.substr(0,i+1);
					console.log('final_value',final_value);
				}
			}
		})
	})
}
