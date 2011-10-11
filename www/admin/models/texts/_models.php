<?php

class Texts extends Model{
	function init_vars(){
		$this->__admin__=array(
			//'delete_rel'=>true,//если true, то привязанные модели будут удаляться без предупреждения, как это реализовано в моделях [Админы] и [Модели]
			//'ipp'=>20,//items per page - кол-во элементов в левом меню в блоке "страница"
			//'list_display'=>'id,name',//поля, которые будут отдаваться функции __str__()
			//'filter'=>'id>1',//фильтрация элементов в левом меню
			'tf'=>'structure_title',//type filter - над левым меню появляется форма-фильтр, подстрока ищется в указанных полях модели
			'group'=>'structure_id',//группировка, альтернативная постраничной
			//'ordering'=>'-cdate',//сортировка, где + это asc, - это desc. полей может быть несколько через запятую
			'controls'=>'save_then_add',//кнопки формы. по умолчанию появляются только "сохранить" и "удалить", остальные нужно объявлять дополнительно
			//'onsubmit'=>'return ver(this.name)',//js-код в атрибуте onsubmit тега <form>
			'js'=>'getJS()',//js-код на странице
			//'deny'=>('add,edit,delete'),//запрет на какое либо из действий, распростаняется в том числе и на суперадмина, например в [Лог авторизации]
		);
	}

	function init_fields(){
		$this->structure_id=new ForeignKeyField(array('Раздел','modelrel'=>'structure'));
		$this->label=new CharField(array('Ярлык','help_text'=>'латиница','maxlength'=>'32','choices'=>'labelChoices()','match'=>'/^[a-zA-Z_-]+$/','unique'=>true,'fieldrel'=>'structure_id'));
		$this->body=new TextField(array('Текст/код','blank'=>true,'allowtags'=>true,'editor'=>'medium_360'));
		$this->structure_title=new CharField(array('structure_title','blank'=>true,'editable'=>false));
		$this->domain=new DomainField(array('domain'));
	}

	function __str__($self){
		$labels=$this->labelChoices();
		if(IS_AJAX===true && p2v('tf')!=''){
			return sprintf('%s (%s)',$self['structure_title'],$labels[$self['label']]);
		}else{
			return $labels[$self['label']];
			// return $self['structure_title'];
		}
	}

	function labelChoices(){
		return array(
			'main'=>'основной текст',
			// 'lcol'=>'текст в левой колонке',
		);
	}
	
	function beforeChange(){
		// нужно установить поле $this->structure_title чтобы по нему можно было искать
		$model_structure=gmo('structure');
		$this->structure_title=$model_structure->getById($this->structure_id);
		$this->structure_title=defvar($this->structure_title['title'],$this->structure_title['alternative']);
	}

	function getJS(){
		if($this->getModelItemIdFromGetQuery()==0){
			//происходит создание нового элемента
			//устанавливаем раздел как у последнего созданного элемента в текущем языке
			$texts_item=$this->ga(array(
				'fields'=>'structure_id',//список полей которые нужно вытащить через запятую 'id,name,body'
				'order_by'=>'-id',//строка типа или '-cdate' 'parent, +name' или '__random__' - случайный порядок
				'_slice'=>'0,1',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
			));
			$latest_structure_id=$texts_item[0]['structure_id'];
			$result.='$(\'select[name=texts\[structure_id\]]\').evSboxSelect('.$latest_structure_id.');'."\r\n";
		}

		return $result;
	}
	
	function getPageTexts(){
		$structure_id=$GLOBALS['obj_client']->structure_data_reverse[0]['id'];
		return $this->getPageTextsByStructureId($structure_id);
	}

	function getPageTextsByStructureId($structure_id){
		$model_texts=gmo('texts');
		$reference='__texts_by_structure_'.$structure_id.'__';
		if(!isset($model_texts->$reference)){
			$texts=$model_texts->ga(array(
				'filter'=>e5csql('structure_id=?',$structure_id),//строка фильтра типа 'parent=32'
				'format'=>'label',//название поля (например 'id'), значение которого будет лежать в ключах результирующего массива
				'_slice'=>'0',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
			));
			foreach($texts as $key=>$item){
				$texts[$key]=trim( $model_texts->renderIncludes($item['body']) );
				$texts[$key.'_id']=$item['id'];
			}
			$model_texts->$reference=$texts;
		}
		return $model_texts->$reference;
	}
	
	function renderIncludes($main){
		ob_start();
		while(true){
			$start=mb_strpos($main,'render[');
			$end=mb_strpos($main,']',$start+$offset);
			if($start===false || $end===false || $end < $start){
				break;
			}else{
				//выводим все что до инъекции
				//если инъекция находится внутри <p>, то убираем открывающий тег
				//echo e5c(mb_substr($main,$start-3,3));
				if(mb_substr($main,$start-3,3)=='<p>'){
					echo mb_substr($main,0,$start-3);
				}else{
					echo mb_substr($main,0,$start);
				}
	
				//определяем и выводим инъекцию
				$start+=7;
				$render=mb_substr($main,$start,($end-$start));
				$render=str_replace(':','->',$render);
				$render=str_replace('-&gt;','->',$render);
				$GLOBALS['obj_client']->render($render);
	
				//пересчитываем $main
				//если инъекция находится внутри <p>, то убираем закрывающий тег
				if(mb_substr($main,$end+1,4)=='</p>'){
					$main=mb_substr($main,$end+5);
				}else{
					$main=mb_substr($main,$end+1);
				}
			}
		}
		echo $main;
		$result=ob_get_contents();
		ob_end_clean();
	
		//выводим остаточный текст
		return $result;
	}
}

