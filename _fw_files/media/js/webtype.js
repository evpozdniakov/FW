var evWebtype={
	text: null,
	lang: null,
	br_status: null,
	link_status: null,
	nobr_status: null,
	tags: [],
	noreplaces: [],
	
	run: function(text,opts){
		evWebtype.text = text;
		evWebtype.lang = evWebtype.detectLang();
		evWebtype.br_status = opts.br_status;
		evWebtype.link_status = opts.link_status;
		evWebtype.nobr_status = opts.nobr_status;
		evWebtype.glyphs_status = opts.glyphs_status;
		evWebtype.parseNoreplaces();
		evWebtype.parseTags();
		evWebtype.prepareText();
		evWebtype.parseText();
		evWebtype.parseBr();
		evWebtype.parseLink();
		evWebtype.parseBackTags();
		evWebtype.parseBackNoreplaces();
		return evWebtype.text;
	},
	detectLang: function(){
		var differance = 0;
		while(Math.abs(differance)<=100){
			var simbol_index = Math.round(Math.random() * evWebtype.text.length);
			var simbol = evWebtype.text.substr(simbol_index, 1);
			if(simbol.search(/[a-z]/i)==0){
				differance -=10;
			}else{
				if(simbol.search(/[а-я]/i)==0){
					differance +=10;
				}else{
					differance +=3;
				}
			}
		}
		if(differance<0){
			var result = 'eng';
		}else{
			var result = 'rus';
		}
		return result;
	},
	parseNoreplaces: function(){
		// не помню для чего это...
		var re = /<([^a-z\?\/!%])/gi; evWebtype.text=evWebtype.text.replace(re, '&lt;$1'); // заменяю одиночные символы < на &lt;
		re = /<noreplace>/gi; evWebtype.text=evWebtype.text.replace(re, 'ґ');
		re = /<\/noreplace>/gi; evWebtype.text=evWebtype.text.replace(re, 'Ґ');

		var tmpArray = [];
		re = /ґ([^Ґ]+)Ґ/;
		for(var i=0; evWebtype.text.search(re) >= 0; i++){
			tmpArray = evWebtype.text.match(re);
			evWebtype.noreplaces[i] = tmpArray[1];
			evWebtype.text=evWebtype.text.replace(re, '<#>');
			// status=evWebtype.noreplaces.join('|');
		}
	},
	parseTags: function(){
		// убираем <span class="nobr">...</span>
		var re = /<span class="nobr">([^\s<>]+)<\/span>/i;
		evWebtype.text=evWebtype.text.replace(re, "$1");
		// прячем открывающие теги, чтобы их содержимое не форматировалось
		var re = /<[a-z!\?\/!%]+[^>]*[ "']+[^>]*>/i;
		for(var i=0; evWebtype.text.search(re) >= 0; i++){
			evWebtype.tags[i]=evWebtype.text.match(re);
			evWebtype.text=evWebtype.text.replace(re, '<~>');
			// status=evWebtype.tags.join('|');
		}
	},
	prepareText: function(){
		// удаляем все \r
		var re = /\r/g;	evWebtype.text = evWebtype.text.replace(re, '');
		// удаляем все <nobr> и </nobr>
		if(evWebtype.nobr_status){
			re = /<\/?nobr>/g;	evWebtype.text = evWebtype.text.replace(re, '');
		}
		// заменяем любые виды кавычек на "
		re = /[«»„“”]/g;	evWebtype.text = evWebtype.text.replace(re, '"');
		re = /&quot;|&ldquo;|&rdquo;/g;	evWebtype.text = evWebtype.text.replace(re, '"');
		if(evWebtype.text.search(/&#[0-9]/)>=0){
			re = /&#34;|&#034;|&#0034;|&#147;|&#0147;|&#8220;|&#148;|&#0148;|&#8221;/g;	evWebtype.text = evWebtype.text.replace(re, '"');
		}
		// заменяем разного рода апострофы на '
		re = /[‘’]/g;	evWebtype.text = evWebtype.text.replace(re, "'");
		re = /&apos;|&lsquo;|&rsquo;|&sbquo;/g;	evWebtype.text = evWebtype.text.replace(re, "'");
		if(evWebtype.text.search(/&#[0-9]/)>=0){
			re = /&#39;|&#039;|&#0039;|&#145;|&#0145;|&#8216;|&#146;|&#0146;|&#8217;|&#130;|&#0130;|&#8218;/g;	evWebtype.text = evWebtype.text.replace(re, "'");
		}
		// умышленно расставленные &nbsp^; и &#160; заменяем на пробелы
		re = /&nbsp;| /g;	evWebtype.text = evWebtype.text.replace(re, " ");
		if(evWebtype.text.search(/&#160;/)>=0){
			re = /&#160;/g;	evWebtype.text = evWebtype.text.replace(re, ' ');
		}
		// расставленные тире меняем на дефис и отбиваем пробелами (на случай, если имеем дело с английской версией)
		re = /—|&mdash;|&ndash;/g; evWebtype.text = evWebtype.text.replace(re, " - ");
		// удаляем пробелы на концах строк
		re = /[ \t]+\n/g;	evWebtype.text = evWebtype.text.replace(re, "\n");
		// удаляем лишние пробелы, но знаки табуляции оставляем (для сохранения форматирования)
		re = /( +)/g;	evWebtype.text = evWebtype.text.replace(re, " "); 
	},
	parseText: function(){
		// пары для замены
		var changes='€◊&euro;|§◊&sect;|\\(c\\)◊&copy;|©◊&copy;|\\(r\\)◊&reg;|®◊&reg;|°◊&deg;|…◊&hellip;|\\.\\.\\.◊&hellip;|&#133;◊&hellip;|•◊&bull;| --◊&nbsp;&mdash;| —◊&nbsp;&mdash;| -◊&nbsp;&mdash;| –◊&nbsp;&mdash;|&nbsp;--◊&nbsp;&mdash;|&nbsp;—◊&nbsp;&mdash;|&nbsp;-◊&nbsp;&mdash;|&nbsp;–◊&nbsp;&mdash;| &mdash;◊&nbsp;&mdash;|­◊|\\(tm\\)◊&trade;|™◊&trade;|№ ◊&#8470;&nbsp;|±◊&plusmn;|¶◊&para;|т\\.д\\.◊т.д.|т\\. д\\.◊т.д.|т\\.д\\.◊т.д.|т\\. д\\.◊т.д.|т\\.п\\.◊т.п.|т\\. п\\.◊т.п.|л\\.с\\.◊л.с.|л\\. с\\.◊л.с.|тел\\.: ◊тел.:&nbsp;|факс: ◊факс:&nbsp;|им\\. ◊им.&nbsp;|г\\. ◊г.&nbsp;|ул\\. ◊ул.&nbsp;|пер\\. ◊пер.&nbsp;|пос\\. ◊пос.&nbsp;|с\\. ◊с.&nbsp;';
		if(evWebtype.lang=='rus'){
			// ОБРАБОТКА РУССКОГО ТЕКСТА
			// ставим &laquo;...&raquo; вместо кавычек, внутри которых нет других кавычек
			var re = /\"([^\"\s]+|[^\" ][^\"]*[^\"\s])\"/g; evWebtype.text = evWebtype.text.replace(re, "&laquo;$1&raquo;"); 
			// ставим внешние кавычки - елочки и заменяем &laquo;...&raquo; на внутренние кавычки - лапки
			var tmpArray = [];
			re = /"([^"\s]+|[^"\s][^"]*[^"\s])"/; 
			while(evWebtype.text.search(re) >= 0){
				tmpArray = evWebtype.text.match(re);
				tmpArray[1] = tmpArray[1].replace(/&laquo;/g, "&bdquo;");
				tmpArray[1] = tmpArray[1].replace(/&raquo;/g, "&ldquo;");
				evWebtype.text = evWebtype.text.replace(re, "&laquo;" + tmpArray[1] + "&raquo;");
			}
			// ставим тире
			re = /^- |(\s)- /g; evWebtype.text = evWebtype.text.replace(re, "$1&mdash; ");
			// часть обработки, общая для обеих языковых версий
			evWebtype.parseTextAddition(); 
			// привязываем предлоги
			var predlogi='а|без|в|во|да|для|до|еще|за|и|из|их|или|к|ко|как|меж|между|на|над|не|ни|но|о|об|от|перед|передо|по|под|пред|предо|при|про|с|со|там|то|у|уж';
			tmpArray = predlogi.split('|');
			for(var i=0; i<tmpArray.length; i++){
				re = new RegExp('(^|[^а-я])(' + tmpArray[i] + ') ', 'gi');
				evWebtype.text = evWebtype.text.replace(re, "$1$2&nbsp;");
			}
			// привязываем послелоги
			var poslelogi='же|ли|ль|бы|б';
			tmpArray = poslelogi.split('|');
			for(var i=0; i<tmpArray.length; i++){
				re = new RegExp(' (' + tmpArray[i] + ')([^а-я]|$)', 'gi');
				evWebtype.text = evWebtype.text.replace(re, "&nbsp;$1$2");
			}
			// производим простые замены
			tmpArray = changes.split('|');
			for(var i=0; i<tmpArray.length; i++){
				tmp = tmpArray[i].split('◊');
				re = new RegExp(tmp[0], 'gi');
				evWebtype.text = evWebtype.text.replace(re, tmp[1]);
			}
		}else{
			// ОБРАБОТКА АНГЛИЙСКОГО ТЕКСТА
			// &ldquo;...&rdquo; расставляем вместо кавычек, внутри которых нет других кавычек
			var re = /\"([^\"\s]+|[^\" ][^\"]*[^\"\s])\"/g; evWebtype.text = evWebtype.text.replace(re, "&ldquo;$1&rdquo;");
			// проставляем внешние кавычки - английские лапки и заменяем &ldquo;...&rdquo; на внутренние кавычки - апострофы
			var tmpArray = [];
			re = /"([^"\s]+|[^"\s][^"]*[^"\s])"/;
			while(evWebtype.text.search(re) >= 0){
				tmpArray = evWebtype.text.match(re);
				tmpArray[1] = tmpArray[1].replace(/&ldquo;/g, "&lsquo;");
				tmpArray[1] = tmpArray[1].replace(/&rdquo;/g, "&rsquo;");
				evWebtype.text = evWebtype.text.replace(re, "&ldquo;" + tmpArray[1] + "&rdquo;");
			}
			// тире
			re = /^- |\s- /g; evWebtype.text = evWebtype.text.replace(re, "&mdash;");
			// часть обработки, общая для обеих языковых версий
			evWebtype.parseTextAddition();
			// привязываем предлоги
			var prepositions='a◊a|an◊an|the◊the|about◊about|above◊above|across◊across|after◊after|against◊against|along◊along|among◊among|at◊at|before◊before|behind◊behind|below◊below|beside◊beside|besides◊besides|between◊between|beyond◊beyond|by◊by|down◊down|during◊during|except◊except|for◊for|from◊from|in◊in|inside◊inside|into◊into|of◊of|off◊off|on◊on|opon◊opon|out◊out|out of◊out&nbsp;of|outside◊outside|over◊over|past◊past|round◊round|around◊around|since◊since|through◊through|till◊till|untill◊untill|to◊to|towards◊towards|under◊under|up◊up|with◊with|within◊within|without◊without|according to◊according&nbsp;to|apart from◊apart&nbsp;from|as to◊as&nbsp;to|as for◊as&nbsp;for|because of◊because&nbsp;of|but for◊but&nbsp;for|by means of◊by&nbsp;means&nbsp;of|in accordance with◊in&nbsp;accordance&nbsp;with|in addition to◊in&nbsp;addition&nbsp;to|in case of◊in&nbsp;case&nbsp;of|as compared with◊as&nbsp;compared&nbsp;with|in comparison with◊in&nbsp;comparison&nbsp;with|in confrmity with◊in&nbsp;confrmity&nbsp;with|in consequence of◊in&nbsp;consequence&nbsp;of|in favour of◊in&nbsp;favour&nbsp;of|in front of◊in&nbsp;front&nbsp;of|in spite of◊in&nbsp;spite&nbsp;of|instead of◊instead&nbsp;of|in the event of|in view of◊in&nbsp;view&nbsp;of|owing to◊owing&nbsp;to|on behalf of◊on&nbsp;behalf&nbsp;of|in the name of|subject to◊subject&nbsp;to|thanks to◊thanks&nbsp;to|with a view to|with regard to◊with&nbsp;regard&nbsp;to|in regard to◊in&nbsp;regard&nbsp;to|with respect to◊with&nbsp;respect&nbsp;to|in respect to◊in&nbsp;respect&nbsp;to|after◊after|and◊and|as though◊as&nbsp;though|as well as◊as&nbsp;well&nbsp;as|as long as◊as&nbsp;long&nbsp;as|as soon as◊as&nbsp;soon&nbsp;as|as◊as|both◊both|but also◊but&nbsp;also|but◊but|because◊because|either◊either|for◊for|in order that◊in&nbsp;order&nbsp;that|if◊if|lest◊lest|not only◊not&nbsp;only|not◊not|on condition that◊on&nbsp;condition&nbsp;that|on condition◊on&nbsp;condition|or◊or|provided that◊provided&nbsp;that|provided◊provided|providing that◊providing&nbsp;that|providing◊providing|supposing that◊supposing&nbsp;that|supposing◊supposing|seeing that◊seeing&nbsp;that|seeing◊seeing|so◊so|such◊such|that◊that|while◊while|whether◊whether|unless◊unless';
			tmpArray = prepositions.split('|');
			for(var i=0; i<tmpArray.length; i++){
				var tmp = tmpArray[i].split('◊');
				re = new RegExp('(^|[^a-z])(' + tmp[0] + ') ', 'gi');
				evWebtype.text = evWebtype.text.replace(re, "$1$2&nbsp;");
			}
			// производим простые замены
			tmpArray = changes.split('|'); 
			for(var i=0; i<tmpArray.length; i++){
				var tmp = tmpArray[i].split('◊');
				re = new RegExp(tmp[0], 'gi');
				evWebtype.text = evWebtype.text.replace(re, tmp[1]);
			}
		}
		// меняем entities на glyphs
		if(evWebtype.glyphs_status){
			changes='nbsp◊ |quot◊"|apos◊\'|iexcl◊¡|cent◊¢|pound◊£|curren◊¤|yen◊¥|brvbar◊¦|sect◊§|uml◊¨|copy◊©|ordf◊ª|laquo◊«|not◊¬|reg◊®|macr◊¯|deg◊°|plusmn◊±|sup2◊²|sup3◊³|acute◊´|micro◊µ|para◊¶|middot◊·|cedil◊¸|sup1◊¹|ordm◊º|raquo◊»|times◊×|divide◊÷|circ◊ˆ|tilde◊˜|ndash◊–|mdash◊—|lsquo◊‘|rsquo◊’|sbquo◊‚|ldquo◊“|rdquo◊”|bdquo◊„|dagger◊†|Dagger◊‡|bull◊•|hellip◊…|permil◊‰|prime◊′|Prime◊″|lsaquo◊‹|rsaquo◊›|oline◊‾|frasl◊⁄|euro◊€|image◊ℑ|weierp◊℘|real◊ℜ|trade◊™|alefsym◊ℵ|larr◊←|uarr◊↑|rarr◊→|darr◊↓|harr◊↔|crarr◊↵|lArr◊⇐|uArr◊⇑|rArr◊⇒|dArr◊⇓|hArr◊⇔';
			tmpArray = changes.split('|');
			for(var i=0; i<tmpArray.length; i++){
				tmp = tmpArray[i].split('◊');
				re = new RegExp('&'+tmp[0]+';', 'gi');
				evWebtype.text = evWebtype.text.replace(re, tmp[1]);
			}
		}
	},
	parseTextAddition: function(){
		// закрывающий апостроф
		var re = /([^\s])\'/g; evWebtype.text = evWebtype.text.replace(re, "$1&rsquo;");
		// оставшиеся апострофы - открывающие
		re = /\'/g; evWebtype.text = evWebtype.text.replace(re, "&lsquo;");
		// привязка числительных
		re = /([0-9]+) ([а-яa-z0-9])/gi; evWebtype.text = evWebtype.text.replace(re, "$1&nbsp;$2");
		// инициалы (А.С.Пушкин -> А.&nbsp;С.&nbsp;Пушкин)
		re = /(^|[^а-яА-Яa-zA-Z])([А-ЯA-Z])\. ?([А-ЯA-Z])/g;
		// выполняется дважы: для имени
		evWebtype.text = evWebtype.text.replace(re, "$1$2.&nbsp;$3");
		// для отчества
		evWebtype.text = evWebtype.text.replace(re, "$1$2.&nbsp;$3");
		// красно-желтый -> <span class="nobr">красно-желтый</span>
		if(evWebtype.nobr_status){
			re = /([^\s<>]+-[^\s<>]+)/g; evWebtype.text = evWebtype.text.replace(re, "<span class=\"nobr\">$1</span>");
		}
		// привязка римкских
		re = /([mlxvi]+) ([а-яa-z])/gi; evWebtype.text = evWebtype.text.replace(re, "$1&nbsp;$2");
	},
	parseBr: function(){
		if(evWebtype.br_status){
			// один перевод строки заменяем на <br>
			evWebtype.text = evWebtype.text.replace(/([^\n])\n([^\n])/g, '$1<br>\n$2');
			//заключаем весь текст в тег <p>
			evWebtype.text = '<p>' + evWebtype.text + '</p>'; 
			// три и более концов строк меняем на два, абзацы заключаем в тег <p>
			evWebtype.text = evWebtype.text.replace(/\n(\n)+/g, '</p>\n\n<p>');
		}
	},
	parseLink: function(){
		if(evWebtype.link_status){
			// проставляем ссылки c www
			var re = /(^|[^\/])(www\.[^\s<>]+)/gi; evWebtype.text = evWebtype.text.replace(re, '$1<a href="http://$2">$2</a>');
			// проставляем ссылки c http://
			re = /(^|[^"])http:\/\/([^\s<>]+\.[^\s<>]+)/gi; evWebtype.text = evWebtype.text.replace(re, '$1<a href="http://$2">$2</a>');
			// проставляем mailto
			re = /([^\s<>]+@[^\s<>]+\.[a-z]{2,})/gi; evWebtype.text = evWebtype.text.replace(re, '<a href="mailto:$1">$1</a>');
		}
	},
	parseBackTags: function(){
		var re = /<~>/;
		for(var i=0; i<evWebtype.tags.length; i++){
			evWebtype.text=evWebtype.text.replace(re, evWebtype.tags[i]);
		}
	},
	parseBackNoreplaces: function(){
		var re = /<#>/;
		for(var i=0; i<evWebtype.noreplaces.length; i++){
			evWebtype.text=evWebtype.text.replace(re, evWebtype.noreplaces[i]);
		}
	}
}





