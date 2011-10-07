<?php

class _Users extends Model{
	function init_vars(){
		$this->__admin__=array(
			'delete_rel'=>true,//если true, то привязанные модели будут удаляться без предупреждения, как это реализовано в моделях [Админы] и [Модели]
			//'ipp'=>20,//items per page - кол-во элементов в левом меню в блоке "страница"
			//'list_display'=>'id,name',//поля, которые будут отдаваться функции __str__()
			'filter'=>'su!="yes"',//фильтрация элементов в левом меню
			//'tf'=>'name,description',//type filter - над левым меню появляется форма-фильтр, подстрока ищется в указанных полях модели
			//'group'=>'fks_id',//группировка, альтернативная постраничной
			//'ordering'=>'-cdate',//сортировка, где + это asc, - это desc. полей может быть несколько через запятую
			//'controls'=>'save,save_then_add,up,down,delete',//кнопки формы. по умолчанию появляются только "сохранить" и "удалить", остальные нужно объявлять дополнительно
			'onsubmit'=>'return ver(this.name)',//js-код в атрибуте onsubmit тега <form>
			'js'=>'getJsFunctions()',//js-код на странице
			//'deny'=>('add,edit,delete'),//запрет на какое либо из действий, распростаняется в том числе и на суперадмина, например в [Лог авторизации]
		);
	}
  
	function init_fields(){
		$this->name=new CharField(array('Имя','blank'=>true,'null'=>false,'editable'=>true,'maxlength'=>'64'));
		$this->login=new CharField(array('Логин','blank'=>false,'null'=>false,'editable'=>true,'maxlength'=>'16','unique'=>true,'fieldrel'=>'domain'));
		$this->hash=new CharField(array('Хэш','blank'=>true,'null'=>false,'editable'=>false,'maxlength'=>'64'));
		$this->password1=new PasswordField(array('Пароль','blank'=>true,'null'=>false,'editable'=>true,'maxlength'=>'16'));
		$this->password2=new PasswordField(array('Повторите пароль','blank'=>true,'null'=>false,'editable'=>true,'maxlength'=>'16'));
		$this->su=new BooleanField(array('Суперюзер','editable'=>false,'default'=>'no','blank'=>true));
		$this->multidomain=new BooleanField(array('Доступ ко всем субдоменам','editable'=>true,'default'=>'no','blank'=>true));
		$this->is_changed=new BooleanField(array('Изменены права доступа','editable'=>false,'default'=>'no','blank'=>true));
		$this->domain=new DomainField(array('domain','editable'=>false,'blank'=>true));
		$this->mdate=new TimestampField(array('mdate'));
		$this->ip=new IPField(array('ip'));
	}

	function __str__($self){
		return defvar($self['login'],$self['name']);
	}

	function userCanAddModelItems(){return ($_SESSION['admin_user']['su']);}
	function userCanEditModelItems(){return ($_SESSION['admin_user']['su']);}
	function userCanDeleteModelItems(){return ($_SESSION['admin_user']['su']);}

	function beforeAdd(){
		$this->createHashEmptyPasswords();
	}

	function beforeEdit(){
		$this->checkModelsList();
		if($this->password1!=''){
			$this->createHashEmptyPasswords();
		}
		//устанавливаем поле is_changed
		$this->is_changed='yes';
		//устанавливаем прежнее значение поля multidomain
		$dbq=new DBQ('select multidomain from _users where id=?',$this->id);
		$this->multidomain=$dbq->item;
	}

	function beforeDelete(){
		if($this->su=='yes'){
			_die('нельзя удалять суперюзера');
		}
	}
	
	function validate(){
		if($this->id==0 && preg_match('/^[^а-яА-Я]{2,16}$/',$this->password1)==0){
			$err['password1']='пароль 1 не соответствует шаблону /^[^а-яА-Я]{2,16}$/';
		}
		if($this->password1!=$this->password2){
			$err['password2']='пароли 1 и 2 не совпадают';
		}
		return $err;
	}

	function checkModelsList(){
		//для всех пользователей кроме su проверяем не изменился ли список моделей, 
		//в случае необходимости вносим изменения в _access
		if($this->su=='no'){
			//получаем список моделей
			$obj_model=getModelObject('_models');
			$models_arr=$obj_model->objects();
			$models_arr=$models_arr->format();
			$models_arr=$models_arr->filter('name not like "\_%"');
			$models_arr=$models_arr->_slice(0);//_print_r($models_arr);
			//получаем список доступов текущего пользователя
			$obj_model=getModelObject('_access');
			$access_arr=$obj_model->objects();
			$access_arr=$access_arr->filter('_users_id='.$this->id);
			$access_arr=$access_arr->_slice(0);
			if(count($models_arr)<count($access_arr)){
				//убираем доступы, если модели были убраны
				$this->removeAccess($access_arr,$models_arr);
			}
			if(count($models_arr)>count($access_arr)){
				//добавляем доступы, если модели появились новые
				$this->addAccess($access_arr,$models_arr);
			}
		}
	}

	function createHashEmptyPasswords(){
		$this->hash=_crypt($this->password1);
		unset($this->password1);
		unset($this->password2);
	}

	function afterAdd(){
		/*
			тут мы должны создать несколько объектов модели _Access
			- ровно столько, сколько имеется моделей
			но сначала нужно удалить из _Access записи предыдущего пользователя
			которые могли сохраниться при копировании базы для дургого проекта
		*/
		ga(array(
			'classname'=>'_access',
			'filter'=>e5csql('_users_id=?',$this->id),//строка фильтра типа 'parent=32'
			'_delete'=>true,//строка '[n[,m]]' или true. удалит элементы.
		));
		$models_arr=ga(array(
			'classname'=>'_models',
			'filter'=>'name not like "\_%"',
			'order_by'=>'ordering',
			'_slice'=>'0',
		));
		foreach($models_arr as $model){
			$rel_model_item=new _Access(array(
				'_models_id'=>$model['id'],
				'_users_id'=>$this->id,
				'add_access'=>'no',
				'edit_access'=>'no',
				'delete_access'=>'no',
			));
			$rel_model_item->save();
		}
	}

	function afterEdit(){
		//здесь бесполезно вызывать ->fixAccess(), поскольку он
		//меняет права текущему пользвателю, то есть суперюзеру
		//$GLOBALS['obj_admin']->fixAccess();
	}

	function removeAccess($access_arr,$models_arr){
		//убираем лишние доступы, если модели были убраны
		if(is_array($access_arr)){
			foreach($access_arr as $access){
				if( !array_key_exists($access['_models_id'],$models_arr) ){
					$access_item=new _Access($access['id']);
					$access_item->delete();
				}
			}
		}
	}

	function addAccess($access_arr,$models_arr){
		if(is_array($models_arr)){
			foreach($models_arr as $model){
				if(is_array($access_arr)){
					$model_access_exists=false;
					foreach($access_arr as $access){
						if( $access['_models_id']==$model['id'] ){
							$model_access_exists=true;
							continue;
						}
					}
					if( !$model_access_exists ){
						//добавляем access
						$rel_model_item=new _Access(array(
							'_models_id'=>$model['id'],
							'_users_id'=>$this->id,
							'add_access'=>'no',
							'edit_access'=>'no',
							'delete_access'=>'no',
						));
						$rel_model_item->save();
					}
				}
			}
		}
	}

	function getJsFunctions(){
		$result='
			function ver(fname){
				var form=document.forms[fname];
				if( !form.elements["'.$this->__name__.'[id]"] || !form.elements["'.$this->__name__.'[password1]"] || !form.elements["'.$this->__name__.'[password2]"] ){
					alert("Поля '.$this->__name__.'[id], '.$this->__name__.'[password1] и '.$this->__name__.'[password2] отсутствуют в форме");
					return false;
				}
				if( form.elements["'.$this->__name__.'[password1]"].value || form.elements["'.$this->__name__.'[password2]"].value ){
					if( !form.elements["'.$this->__name__.'[password1]"].value.match(/^[^а-яА-Я]{2,16}$/) ){
						alert("Пароль не соответствует формату /^[^а-яА-Я]{2,16}$/")
						return false;
					}
					if( form.elements["'.$this->__name__.'[password1]"].value!=form.elements["'.$this->__name__.'[password2]"].value){
						alert("Па«роли 1 и 2 не совпадают")
						return false;
					}
				}

				return true;
			}
		';
		if($this->getModelItemIdFromGetQuery()>0){
			$result.='document.forms[\'model__users_form\'].elements[\'_users[multidomain]\'].disabled=true;';
		}else{
			$result.='document.forms[\'model__users_form\'].elements[\'_users[multidomain]\'].checked=false;';
		}
		return $result;
	}
}

