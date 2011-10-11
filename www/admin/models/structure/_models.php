<?php

class Structure extends Model{
	function init_vars(){
		$this->__admin__=array(
			'delete_rel'=>true,//если true, то привязанные модели будут удаляться без предупреждения, как это реализовано в моделях [Админы] и [Модели]
			//'ipp'=>20,//items per page - кол-во элементов в левом меню в блоке "страница"
			//'list_display'=>'id,name',//поля, которые будут отдаваться функции __str__()
			//'filter'=>'id>1',//фильтрация элементов в левом меню
			'tf'=>'title,url',//type filter - над левым меню появляется форма-фильтр, подстрока ищется в указанных полях модели
			'group'=>'parent',//группировка, альтернативная постраничной
			'ordering'=>'ordering',//сортировка, где + это asc, - это desc. полей может быть несколько через запятую
			'controls'=>'save,save_then_add,copy,up,down,delete',//кнопки формы. по умолчанию появляются только "сохранить" и "удалить", остальные нужно объявлять дополнительно
			'onsubmit'=>'if(confirmNewDomain(this.name)){enableURL();}else{return false;}',//js-код в атрибуте onsubmit тега <form>
			'js'=>'getJS()',//ссылка на js-файл в папке модели или php-функция, которая возвращает кусок js-кода
			//'deny'=>('add,edit,delete'),//запрет на какое либо из действий, распростаняется в том числе и на суперадмина, например в [Лог авторизации]
		);
	}

	function init_fields(){
		$this->ordering=new OrderField(array('Порядок','fieldrel'=>'parent'));
		$this->parent=new TreeField(array('Родитель'));
		$this->url=new URLField(array('URL','help_text'=>'допустимы латинские буквы и цифры','unique'=>true,'fieldrel'=>'parent'));
		$this->title=new CharField(array('Название раздела'));
		$this->alternative=new CharField(array('Альтернативное название раздела','blank'=>true,'editable'=>false));
		$this->redirect=new BooleanField(array('Редирект','blank'=>true,'editable'=>false,'choices'=>'перенаправлять пользователей в первый подраздел текущего раздела'));
		$this->titletag=new CharField(array('Тег title','help_text'=>'если не заполнить (рекомендуется), то будет сформирован автоматически','blank'=>true));
		$this->keywords=new CharField(array('ключевые слова для тега meta','blank'=>true));
		$this->description=new CharField(array('описание для тега meta','blank'=>true));
		$this->view=new CharField(array('Обработчик','blank'=>true,'maxlength'=>'32','choices'=>'viewsChoices()'));
		$this->template=new CharField(array('Шаблон','blank'=>true,'maxlength'=>'32','choices'=>'templateChoices()'));
		$this->cachetime=new IntField(array('Время хранения в кэше','help_text'=>'в секундах','blank'=>true));
		$this->menu_on=new BooleanField(array('Показывать в меню','choices'=>'не включать'));
		$this->sitemap_on=new BooleanField(array('Показывать на карте сайта','choices'=>'не включать'));
		$this->is_hidden=new BooleanField(array('Временно скрыть раздел и подразделы','choices'=>'скрыть,показать'));
		$this->domain=new DomainField(array('domain'));
	}

	function __str__($self){
		return $self['title'];
	}

	function getJS(){//стандартная функция модели, нельзя удалять
		$dbq=new DBQ('select parent from structure where id=?',$this->getModelItemIdFromGetQuery());
		$parent=$dbq->item;
		if($dbq->rows==1 && $parent==0){
			//происходит редактирование элемента с parent=0
			//дизэйблим язык у корневого элемента
			$result='document.forms[\'model_structure_form\'].elements[\'structure[url]\'].disabled=true;'."\r\n";
		}
		if($this->getModelItemIdFromGetQuery()==0){
			//происходит создание нового элемента
			//устанавливаем родителя в родителя последнего созданного элемента в текущем языке (если это не 0)
			$dbq=new DBQ('select parent from structure where domain=? order by id desc limit 1',DOMAIN_ID);
			$parent=$dbq->item;
			if($dbq->rows==1 && $parent>0){
				$result.='$(\'select[name=structure\[parent\]]\').evSboxSelect('.$parent.');'."\r\n";
			}
		}
		//если происходит процесс создания нового элемента, то
		//добавляем функцию, которая запрашивает подтверждение на создание нового языка
		$result.='function confirmNewDomain(fname){
			var form=document.forms[fname];
			var ems=form.elements;
			if(ems[\'structure[id]\'].value==\'\' && $(ems[\'structure[parent]\']).evSboxValue()==0){
				return confirm(\'Подтвердите создание новой ветки для нового домена.\');
			}
			return true;
		}';

		//добавляем функцию, которая снимает дизэйбл с урла
		$result.='function enableURL(){
			document.forms[\'model_structure_form\'].elements[\'structure[url]\'].disabled=false;
		}';

		return $result;
	}

	function viewsChoices(){//стандартная функция модели, нельзя удалять
		$result=array();
		if(is_array($GLOBALS['INSTALLED_APPS'])){
			foreach($GLOBALS['INSTALLED_APPS'] as $model=>$model_name){
				$views_obj=gmv($model);
				if(is_array($views_obj->views_arr) && count($views_obj->views_arr)>0){
					foreach($views_obj->views_arr as $method=>$method_name){
						$key=strtolower($views_obj->model_name).':'.$method;
						$value=$key.' ('.$model_name.': '.$method_name.')';
						$result[$key]=$value;
					}
				}
			}
		}

		return $result;
	}

	function templateChoices(){//стандартная функция модели, нельзя удалять
		$scriptLocation=TPL_DIR.'templates/_settings.php';
		if(file_exists($scriptLocation)){include($scriptLocation);}else{die('Cant load '.$scriptLocation);}

		return $GLOBALS['VIEWS'];
	}

	function validate(){//стандартная функция модели, нельзя удалять
		if($this->parent==0){
			if($_SESSION['admin_user']['su']==0){
				$err['parent']='вносить изменение в корневые элементы могут только root-администраторы';
			}elseif($this->id>0 && $this->_getBakParent()!=0){
				$err['parent']='родитель элемента не может быть изменен на корневой';
			}
		}

		return $err;
	}
	
	function beforeChange(){
	}

	function beforeAdd(){//стандартная функция модели, нельзя удалять
		//если мы добавляем корневой элемент, то в поле предустанавливаем ему view и template
		if($this->parent==0){
			$this->template=defvar('p_first',$this->template);
			$this->view=defvar('texts:content',$this->view);
		}
	}

	function beforeDelete(){//стандартная функция модели, нельзя удалять
		if($this->parent==0){
			//если происходит удаление домена, то необходимо пройти по всем моделям
			//и проверить их на наличие поля DomainField
			//и если такое поле есть - удалить все элементы с доменом DOMAIN_ID
			if(is_array($GLOBALS['INSTALLED_APPS'])){
				foreach(array_keys($GLOBALS['INSTALLED_APPS']) as $model_name){
					if($model_name=='structure'){continue;}
					$model_obj=gmo($model_name);
					foreach(get_object_vars($model_obj) as $field){
						if(is_object($field) && is_a($field,'DomainField')){
							//нашли модель у которой есть поле типа DomainField
							//вытаскиваем id всех элементов с текущим доменом
							$items=$model_obj->ga(array(
								'fields'=>'id',//список полей которые нужно вытащить через запятую 'id,name,body'
								'order_by'=>'-id',//строка типа или '-cdate' 'parent, +name' или '__random__' - случайный порядок
								'_slice'=>'0',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
							));
							if(count($items)>0){
								foreach($items as $item){
									$model_item=new $model_name($item['id']);
									$model_item->delete();
								}
							}
						}
					}
				}
			}
		}
		//необходимо удалить всех потомков
		$children=ga(array(
			'classname'=>'structure',
			'fields'=>'id',//список полей которые нужно вытащить через запятую 'id,name,body'
			'filter'=>'parent='.$this->id,//строка фильтра типа 'parent=32'
			'_slice'=>'0',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
		));
		foreach($children as $item){
 			$structure_item=new Structure($item['id']);
			$structure_item->delete();
		}
	}

	function afterAdd(){//стандартная функция модели, нельзя удалять
		if($this->parent==0 && $this->id>0){
			//в первую очередь устанавливаем элементу поле domain равное id
			$dbq=new DBQ('update structure set domain=id where id=?',$this->id);
			//поскольку был добавлен новый домен, то нужно сделать редирект
			if(USE_SUBDOMAINS===true){
				hexit('Location: http://'.$this->url.'.'.removeSubdomain().'/admin/structure/');
			}else{
				hexit('Location: /~'.$this->url.'~/admin/structure/');
			}
		}
	}

	function afterDelete(){//стандартная функция модели, нельзя удалять
		if($this->parent==0){
			hexit('Location: /admin/structure/');
		}
	}
	
	function afterChange(){
		$this->changeDinamicCss();
		// нужно установить поле $model_texts->structure_title чтобы по нему можно было искать
		$dbq=new DBQ('select id from texts where structure_id=?',$this->id);
		if($dbq->rows){
			$dbq=new DBQ('update texts set structure_title=? where id=?',defvar($this->title,$this->alternative),$dbq->items[0]['id']);
		}
	}
	
	/**
	* возвращает id корневого элемента структуры (для текущего домена)
	*/
	function getStructureRootId(){
		if($GLOBALS['obj_client'] && $GLOBALS['obj_client']->structure_data){
			return $GLOBALS['obj_client']->structure_data[0]['id'];
		}else{
			if(!$GLOBALS['structure_root_id']){
				$structure_arr=ga(array(
					'classname'=>'structure',
					'fields'=>'id',//список полей которые нужно вытащить через запятую 'id,name,body'
					'filter'=>'parent=0 and url="'.DOMAIN.'"',//строка фильтра типа 'parent=32'
					'_slice'=>'0,1',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
				));
				if(count($structure_arr)==1){
					$GLOBALS['structure_root_id']=$structure_arr[0]['id'];
				}elseif(count($structure_arr)==0){
					$GLOBALS['structure_root_id']=0;
				}else{
					_die('не найден id самого главного родителя в getStructureRootId()');
				}
			}
			return $GLOBALS['structure_root_id'];
		}
	}

	function getAllPlain(){
		$model_structure=gmo('structure');
		if(!isset($model_structure->all_plain)){
			$model_structure->all_plain=$model_structure->ga(array(
				'domain'=>false,
				'fields'=>'*',//список полей которые нужно вытащить через запятую 'id,name,body'
				'filter'=>e5csql('is_hidden="no"'),//строка фильтра типа 'parent=32'
				//'order_by'=>'parent,ordering',//строка типа или '-cdate' 'parent, +name' или '__random__' - случайный порядок
				'format'=>'id',//название поля (например 'id'), значение которого будет лежать в ключах результирующего массива
				'_slice'=>'0',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
			));
		}
		return $model_structure->all_plain;
	}

	function getAllPlainDeep(){
		$tree=$this->getAllTree();
		$result=$this->getAllPlainDeepRec($tree);
		return $result;
	}

	function getAllPlainDeepRec($tree,$deep=-1){
		$result=array();
		if(count($tree)>0){
			$deep++;
			foreach($tree as $item){
				$children=$this->getAllPlainDeepRec($item['children'],$deep);
				unset($item['children']);
				$item['deep']=$deep;
				$result[]=$item;
				$result=_array_merge($result,$children);
			}
		}
		return $result;
	}

	function getAllTree(){
		$result=$this->getAllPlain();
		while(true){
			$changes=false;
			$parents_ids=groupArray('parent',$result);
			$parents_ids=array_keys($parents_ids);
			foreach($result as $item_id=>$item){
				$parent_id=$result[$item_id]['parent'];
				if(!in_array($item_id,$parents_ids)){
					if(count($item['children'])>0){
						ksort($item['children']);
					}
					if(count($result)>1){
						$changes=true;
						$result[$parent_id]['children'][$item['ordering']]=$item;
						unset($result[$item_id]);
					}
				}
			}
			if(!$changes){
				break;
			}
		}
		if(count($result[1]['children'])>0){
			ksort($result[1]['children']);
		}
		return $result;
	}

	function getThread($id){
		$all_plain=$this->getAllPlain();
		while(true){
			$changes=false;
			$parents_ids=groupArray('parent',$all_plain);
			$parents_ids=array_keys($parents_ids);
			foreach($all_plain as $item_id=>$item){
				$parent_id=$all_plain[$item_id]['parent'];
				if(!in_array($item_id,$parents_ids)){
					if(count($item['children'])>0){
						ksort($item['children']);
					}
					if(count($all_plain)>1){
						$changes=true;
						$all_plain[$parent_id]['children'][$item['ordering']]=$item;
						unset($all_plain[$item_id]);
					}
					if($id==$item_id){
						$result=$item;
						break 2;
					}
				}
			}
			if(!$changes){
				break;
			}
		}
		return $result;
	}

	function getChildren($parent){
		if($parent>0){
			$result=$this->getThread($parent);
			if(count($result['children'])>0){
				$result=$result['children'];
				foreach(array_keys($result) as $key){
					if(isset($result[$key]['children'])){
						unset($result[$key]['children']);
					}
				}
			}else{
				$result=array();
			}
		}else{
			$result=$this->ga(array(
				'domain'=>false,/*не учитываем язык чтобы можно было при создании нового домена получить и скопировать структуру основного раздела */
				'fields'=>'*',//список полей которые нужно вытащить через запятую 'id,name,body'
				'filter'=>e5csql('parent=0'),//строка фильтра типа 'parent=32'
				'order_by'=>'id',//строка типа или '-cdate' 'parent, +name' или '__random__' - случайный порядок
				'_slice'=>'0',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
			));
		}
		return $result;
	}
	
	function getById($id){
		$all_plain=$this->getAllPlain();
		return $all_plain[$id];
	}
	
	function getUrlById($id){
		if(is_numeric($id) && $id>0){
			$url='/'.$this->getUrlByIdRec($id);
		}
		return $url;
	}
	
	function getUrlByIdRec($id){
		$url='';
		$structure_item=$this->getById($id);
		if(is_array($structure_item)){
			if($structure_item['parent']>0 && $structure_item['parent']!=DOMAIN_ID){
				$url=$this->getUrlByIdRec($structure_item['parent']);
			}
			$url.=$structure_item['url'].'/';
		}
		return $url;
	}

	function changeDinamicCss(){
		// $all_plain=$this->getAllPlain();
		// $css='';
		// $this->saveDynamicCss($css);
	}
	
	function _getBakParent(){//стандартная функция модели, нельзя удалять
		$dbq=new DBQ('select parent from structure where id=?',$this->id);
		return $dbq->item;
	}
	
	function _zerizeTreeFieldInModel(){
		$result=false;
		foreach(get_object_vars($this) as $field_name=>$obj_field){
			if(is_a($obj_field,'TreeField')){
				$result=$obj_field;
				break;
			}
		}
		return $result;
	}
}
