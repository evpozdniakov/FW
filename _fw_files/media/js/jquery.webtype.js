/*

Usage ways:

- by providing selector
$.webtype('#contentBox p');

- by providing html element(s)
$.webtype(document.getElementsByTagName('p'));

- by providing jquery object
$.webtype($('#contentBox p'));

- by using jquery method
$('#contentBox p').webtype();

ATTANTION!
You should use it before binding any event listeners. Otherwise all listeners might be dropped off.

*/

$.extend({
	webtype: function($__html__selector){
		if(!$__html__selector){
			// if there are no parametrs, consider the context is *.webtype
			$__html__selector=$('*.webtype');
		}else if(typeof $__html__selector['jquery']=='undefined'){
			// $__html__selector is html element(s) or selector, transform to jquery
			$__html__selector=$($__html__selector);
		}
		$__html__selector.webtype();
	}
})

$.fn.extend({
	webtype: function(){
		var webtype_={
			text: null,
			tags: [],
			comments: [],
			run: function(text){
				this.text = text;
				this.parseTags();
				this.prepareText();
				this.parseText();
				this.parseBackTags();
				return this.text;
			},
			parseTags: function(){
				// remove <span class="nobr">...</span>
				var re = /<span class="nobr">([^\s<>]+)<\/span>/i;
				this.text=this.text.replace(re, "$1");
				// hide start-tags
				var re = /<[a-z!\?\/!%]+[^>]*[ "']+[^>]*>/i;
				for(var i=0; this.text.search(re) >= 0; i++){
					this.tags[i]=this.text.match(re);
					this.text=this.text.replace(re, '<~>');
				}
				// hide comments
				var re = /<\!\-\-[^>]*\-\->/;
				for(var i=0; this.text.search(re) >= 0; i++){
					this.comments[i]=this.text.match(re);
					this.text=this.text.replace(re, '<!>');
				}
			},
			prepareText: function(){
				// remove \r
				var re = /\r/g;	this.text = this.text.replace(re, '');
				// replace all types of quotes to "
				re = /[«»„“”]/g;	this.text = this.text.replace(re, '"');
				re = /&quot;|&ldquo;|&rdquo;/g;	this.text = this.text.replace(re, '"');
				if(this.text.search(/&#[0-9]/)>=0){
					re = /&#34;|&#034;|&#0034;|&#147;|&#0147;|&#8220;|&#148;|&#0148;|&#8221;/g;	this.text = this.text.replace(re, '"');
				}
				// replace all types of apostrophes to '
				re = /[‘’]/g;	this.text = this.text.replace(re, "'");
				re = /&apos;|&lsquo;|&rsquo;|&sbquo;/g;	this.text = this.text.replace(re, "'");
				if(this.text.search(/&#[0-9]/)>=0){
					re = /&#39;|&#039;|&#0039;|&#145;|&#0145;|&#8216;|&#146;|&#0146;|&#8217;|&#130;|&#0130;|&#8218;/g;	this.text = this.text.replace(re, "'");
				}
				// replace &nbsp; and &#160; to spaces
				re = /&nbsp;| /g;	this.text = this.text.replace(re, " ");
				if(this.text.search(/&#160;/)>=0){
					re = /&#160;/g;	this.text = this.text.replace(re, ' ');
				}
				// replace all types of dashes to -
				re = /—|&mdash;|&ndash;/g; this.text = this.text.replace(re, " - ");
				// remove spaces at the ends of the lines
				re = /[ \t]+\n/g;	this.text = this.text.replace(re, "\n");
				// remove extra spaces (gard tabs)
				re = /( +)/g;	this.text = this.text.replace(re, " "); 
			},
			parseText: function(){
				// put &laquo;...&raquo; instead of quotes if there are no other quotes inside
				var re = /\"([^\"\s]+|[^\" ][^\"]*[^\"\s])\"/g; this.text = this.text.replace(re, "&laquo;$1&raquo;"); 
				// put external quotes and internal quotes
				var tmpArray = [];
				re = /"([^"\s]+|[^"\s][^"]*[^"\s])"/; 
				while(this.text.search(re) >= 0){
					tmpArray = this.text.match(re);
					tmpArray[1] = tmpArray[1].replace(/&laquo;/g, "&bdquo;");
					tmpArray[1] = tmpArray[1].replace(/&raquo;/g, "&ldquo;");
					this.text = this.text.replace(re, "&laquo;" + tmpArray[1] + "&raquo;");
				}
				// put dashes
				re = /^- |(\s)- /g; this.text = this.text.replace(re, "$1&mdash; ");
				// closing apostrophes
				re = /([^\s])\'/g; this.text = this.text.replace(re, "$1&rsquo;");
				// opening apostrophes
				re = /\'/g; this.text = this.text.replace(re, "&lsquo;");
				// numerals
				re = /([0-9]+) ([а-яa-z0-9])/gi; this.text = this.text.replace(re, "$1&nbsp;$2");
				// initials twise: for firstnames and fathernames
				re = /(^|[^а-яА-Яa-zA-Z])([А-ЯA-Z])\. ?([А-ЯA-Z])/g; this.text = this.text.replace(re, "$1$2.&nbsp;$3"); this.text = this.text.replace(re, "$1$2.&nbsp;$3");
				// complex words
				re = /([^\s<>]+-[^\s<>]+)/g; this.text = this.text.replace(re, '<span style="white-space:nowrap">$1</span>');
				// roman
				re = /([mlxvi]+) ([а-яa-z])/gi; this.text = this.text.replace(re, "$1&nbsp;$2");
				// prepositions
				var prepositions='а|без|в|во|да|для|до|еще|за|и|из|их|или|к|ко|как|меж|между|на|над|не|ни|но|о|об|от|перед|передо|по|под|пред|предо|при|про|с|со|там|то|у|уж';
				tmpArray = prepositions.split('|');
				for(var i=0; i<tmpArray.length; i++){
					re = new RegExp('(^|[^а-я])(' + tmpArray[i] + ') ', 'gi');
					this.text = this.text.replace(re, "$1$2&nbsp;");
				}
				// postpositions
				var postpositions='же|ли|ль|бы|б';
				tmpArray = postpositions.split('|');
				for(var i=0; i<tmpArray.length; i++){
					re = new RegExp(' (' + tmpArray[i] + ')([^а-я]|$)', 'gi');
					this.text = this.text.replace(re, "&nbsp;$1$2");
				}
				// pairs to replace
				var changes='€◊&euro;|§◊&sect;|\\(c\\)◊&copy;|©◊&copy;|\\(r\\)◊&reg;|®◊&reg;|°◊&deg;|…◊&hellip;|\\.\\.\\.◊&hellip;|&#133;◊&hellip;|•◊&bull;| --◊&nbsp;&mdash;| —◊&nbsp;&mdash;| -◊&nbsp;&mdash;| –◊&nbsp;&mdash;|&nbsp;--◊&nbsp;&mdash;|&nbsp;—◊&nbsp;&mdash;|&nbsp;-◊&nbsp;&mdash;|&nbsp;–◊&nbsp;&mdash;| &mdash;◊&nbsp;&mdash;|­◊|\\(tm\\)◊&trade;|™◊&trade;|№ ◊&#8470;&nbsp;|±◊&plusmn;|¶◊&para;|т\\.д\\.◊т.д.|т\\. д\\.◊т.д.|т\\.д\\.◊т.д.|т\\. д\\.◊т.д.|т\\.п\\.◊т.п.|т\\. п\\.◊т.п.|л\\.с\\.◊л.с.|л\\. с\\.◊л.с.|тел\\.: ◊тел.:&nbsp;|факс: ◊факс:&nbsp;|им\\. ◊им.&nbsp;|г\\. ◊г.&nbsp;|ул\\. ◊ул.&nbsp;|пер\\. ◊пер.&nbsp;|пос\\. ◊пос.&nbsp;|с\\. ◊с.&nbsp;';
				tmpArray = changes.split('|');
				for(var i=0; i<tmpArray.length; i++){
					tmp = tmpArray[i].split('◊');
					re = new RegExp(tmp[0], 'gi');
					this.text = this.text.replace(re, tmp[1]);
				}
			},
			parseBackTags: function(){
				var re = /<~>/;
				for(var i=0; i<this.tags.length; i++){
					this.text=this.text.replace(re, this.tags[i]);
				}
				var re = /<!>/;
				for(var i=0; i<this.tags.length; i++){
					this.text=this.text.replace(re, this.comments[i]);
				}
			}
		}
		this.each(function(){
			this.innerHTML=webtype_.run(this.innerHTML);
		})
	}
})