/*

Usage ways:

- by providing selector
$.parseTable('#contentBox table');

- by providing html element(s)
$.evWebtype(document.getElementsByTagName('table'));

- by providing jquery object
$.evWebtype($('#contentBox table'));

- by using jquery method
$('#contentBox table').parseTable();

*/

$.extend({
	parseTable: function($__html__selector){
		if(!$__html__selector){
			// if there are no parametrs, consider the context is *.parseTable
			$__html__selector=$('*.parseTable');
		}else if(typeof $__html__selector['jquery']=='undefined'){
			// $__html__selector is html element(s) or selector, transform to jquery
			$__html__selector=$($__html__selector);
		}
		$__html__selector.parseTable();
	}
})

$.fn.extend({
	parseTable: function(){
		this.each(function(){
			var $table=$(this);
			if($table.is('table')){
				// 1. 
				// исследуем таблицу $table, определяем ее ширину и высоту,
				// а также создаем матрицу таблицы — одномерный массив, 
				// каждый элемент которого есть число ячеек в СТОЛБЦЕ
				var matrix=[];//матрица ячеек
				var line=1;//текущая строка
				var mxc=1;//max colspan
				var mxr=1;//max rowspan
				$('tr',$table).each(function(){//перебираем строки
					var cells=$('th,td',this).get();//получаем массив ячеек строки
					var index=0;//текущий столбец
					for(var i=0;i<cells.length;i++){//перебираем ячейки
						if(line==1)mxr=(mxr<rowspan)?rowspan:mxr;//отыскиваем максимальный rowspan в первой строке, чтобы знать высоту заголовка страницы
						var rowspan=( parseInt($(cells[i]).attr('rowspan')) || 1 );//устанавливаем rowspan ячейки или 1
						var colspan=( parseInt($(cells[i]).attr('colspan')) || 1 );//устанавливаем colspan ячейки или 1
						for(var j=0;j<colspan;j++){//перебираем colspan
							while((matrix[index] || 0)>=line)index++;//двигаемся по матрице вправо пока не найдем незанятую ячейку
							if(index==0)mxc=(mxc<colspan)?colspan:mxc;//отыскиваем максимальный colspan в первой колонке, чтобы знать ширину заголовка таблицы
							matrix[index]=(matrix[index] || 0) + rowspan;//заполняем матрицу ячейками
							index++;
						}
					}
					line++;
				})
				var matrix_height=0;//определяем высоту таблицы
				for(var i=0;i<matrix.length;i++)matrix_height=(matrix[i]>matrix_height)?matrix[i]:matrix_height;
				var matrix_width=matrix.length;//определяем ширину таблицы
				// 2.
				// устанавливаем ячейкам необходимые атрибуты на основе полученных о таблице сведений
				// механизмом перебора строк и ячеек аналогичен 1, поэтому часть комментариев опущена
				var matrix=[];
				var line=1;
				$('tr',$table).each(function(){
					var cells=$('th,td',this).get();
					var index=0;
					for(var i=0;i<cells.length;i++){
						var rowspan=( parseInt($(cells[i]).attr('rowspan')) || 1 );
						var colspan=( parseInt($(cells[i]).attr('colspan')) || 1 );
						$(this).addClass('row'+line);
						$(this).addClass((line%2==0)?'even':'odd');
						$(cells[i]).addClass('col'+(index+1));
						for(var j=0;j<colspan;j++){
							while((matrix[index] || 0)>=line)index++;
							matrix[index]=(matrix[index] || 0) + rowspan;
							if(line==1)$(cells[i]).addClass('top');//всем верхним ячейкам добавляем класс "top"
							if((index+1)==matrix_width)$(cells[i]).addClass('right');//всем правым "right"
							if(matrix[index]==matrix_height)$(cells[i]).addClass('bottom');//всем нижним "bottom"
							if(index==0)$(cells[i]).addClass('left');//всем левым ячейкам добавляем класс "left"
							if(matrix[index]<=mxr)$(cells[i]).addClass('headrow');//всем ячейкам в составе горизонтального (или верхнего) заголовка таблицы добавляем класс "headrow"
							if((index+colspan)<=mxc)$(cells[i]).addClass('headcol');//всем ячейкам в составе вертикального (или левого) заголовка таблицы - класс "headrow"
							if(colspan>1)$(cells[i]).addClass('wide wide'+colspan);// всем ячейкам с атрибутом colspan добавляем классы wide и wideN
							if(rowspan>1)$(cells[i]).addClass('high high'+rowspan);// всем ячейкам с атрибутом rowspan добавляем классы high и highN
							index++;
						}
					}
					line++;
				})
			}
		})
		return this;
	}
})