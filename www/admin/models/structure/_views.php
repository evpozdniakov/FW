<?php

class StructureViews extends Structure{
	var $views_arr=array(
		'sitemap'=>'карта сайта',
	);
 
	function init(){
	}

	function menuMain(){
		// получаем основные разделы
		$menu=$this->ga(array(
			'fields'=>'title alternative url',//список полей которые нужно вытащить через запятую 'id,name,body'
			'filter'=>e5csql('is_hidden="no" and menu_on="yes" and parent=?',DOMAIN_ID),//строка фильтра типа 'parent=32'
			'order_by'=>'ordering',//строка типа или '-cdate' 'parent, +name' или '__random__' - случайный порядок
			'_slice'=>array(0),//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
		));
		// меняем пути и имена, а также добавляем подразделы
		foreach($menu as $key=>$item){
			$item['title']=defvar($item['title'],$item['alternative']);
			$item['url']=sprintf('/%s/', $item['url']);
			$item['children']=array();
			$children1=$this->ga(array(
				'fields'=>'title alternative url is_groupper',//список полей которые нужно вытащить через запятую 'id,name,body'
				'filter'=>e5csql('is_hidden="no" and menu_on="yes" and parent=?',$item['id']),//строка фильтра типа 'parent=32'
				'order_by'=>'ordering',//строка типа или '-cdate' 'parent, +name' или '__random__' - случайный порядок
				'_slice'=>array(0),//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
			));
			// меняем пути и имена у детей, а также
			// добавляем третий уровень для пунктов, которые являются группираторами
			$num=0;
			foreach($children1 as $key2=>$item2){
				$col14=$num%4;
				$num++;
				$item2['title']=defvar($item2['title'],$item2['alternative']);
				$item2['url']=sprintf('%s%s/', $item['url'], $item2['url']);
				$item['children'][$col14][]=$item2;
				if( $item2['is_groupper']=='yes' ){
					$item2_children=$this->ga(array(
						'fields'=>'title alternative url',//список полей которые нужно вытащить через запятую 'id,name,body'
						'filter'=>e5csql('is_hidden="no" and menu_on="yes" and parent=?',$item2['id']),//строка фильтра типа 'parent=32'
						'order_by'=>'ordering',//строка типа или '-cdate' 'parent, +name' или '__random__' - случайный порядок
						'_slice'=>array(0),//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
					));
					// помещаем детей группираторов прямо вслед за группираторами
					foreach($item2_children as $item3){
						$item3['title']=defvar($item3['title'],$item3['alternative']);
						$item3['url']=sprintf('%s%s/', $item2['url'], $item3['url']);
						$item['children'][$col14][]=$item3;
					}
				}
			}
			// наконец, обновляем $item в $menu
			// и делим детей четыре части по количеству колонок
			$menu[$key]=$item;
		}
		// делим конечный массив $menu на четыре части по количеству колонок
		// и отдаем Smarty
		// _log($menu);
		if( !empty($menu) ){
			smartas('menu',$menu);
		}
	}

	function submenu(){
		$parent_id=$GLOBALS['obj_client']->structure_data[1]['id'];
		if($parent_id){
			$submenu=ga(array(
				'classname'=>'structure',
				'fields'=>'url title alternative is_groupper',//список полей которые нужно вытащить через запятую 'id,name,body'
				'filter'=>e5csql('parent=? and is_hidden="no" and menu_on="yes"',$parent_id),//строка фильтра типа 'parent=32'
				'order_by'=>'ordering',//строка типа или '-cdate' 'parent, +name', где необязательный "+" это asc, а "-" это desc
				'_slice'=>'0',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
			));
			// _print_r('$submenu',$submenu);
			if( !empty($submenu) ){
				foreach($submenu as $key=>$item){
					if( !empty($item['alternative']) ){
						$item['title']=$item['alternative'];
					}
					if(mb_strpos($item['url'],'://')>0){
						$item['http_link']=true;
						$item['url']=$item['url'];
					}else{
						$item['url']=sprintf('/%s/%s/', $GLOBALS['obj_client']->structure_data[1]['url'], $item['url']);
					}
					if( $item['is_groupper']=='yes' ){
						$children=$this->getChildren($item['id']);
						if( !empty($children) ){
							$item['children']=array();
							foreach($children as $child){
								if( $child['menu_on']=='yes' && $child['is_hidden']=='no' ){
									$subitem=array(
										'title'=>defvar($child['title'],$child['alternative']),
										'alternative'=>$child['alternative'],
										'url'=>$child['url'],
									);
									if(mb_strpos($subitem['url'],'://')>0){
										$subitem['http_link']=true;
									}else{
										$subitem['url']=sprintf('%s%s/', $item['url'], $subitem['url']);
									}
									$item['children'][]=$subitem;
								}
							}
						}
					}
					$submenu[$key]=$item;
				}
				// _print_r($submenu);
				smartas('submenu',$submenu);
			}
		}
	}
	
	function gradusnik(){
		// $gradusnik=array_slice($GLOBALS['gradusnik'],1);
		$gradusnik=$GLOBALS['gradusnik'];
		$url='';
		foreach($gradusnik as $key=>$item){
			$url.=$item['url'].'/';
			$gradusnik[$key]['url']=$url;
		}
		smartas('gradusnik',$gradusnik);
	}
	
	function titleMetaTags(){
		$num=0;
		$gradusnik=$GLOBALS['gradusnik'];
		if(count($gradusnik)){
			foreach($gradusnik as $item){
				$num++;
				if($num>1){
					$title_tag.=' / ';
				}
				$title_tag.=defvar($item['url'],$item['txt']);
			}
			unset($gradusnik);
			if($GLOBALS['error']==404){
				$title='Ошибка 404';
			}else{
				$title_tag=defvar($title_tag,$item['titletag']);
			}
			smartas('title_tag',$title_tag);

			if($item['description']!=''){
				$meta_description=$item['description'];
				smartas('meta_description',$meta_description);
			}

			if($item['keywords']!=''){
				$meta_keywords=$item['keywords'];
				smartas('meta_keywords',$meta_keywords);
			}
		}
	}

	function pageTitle(){
		if(PAGE_TITLE_SKIP!==true){
			if($GLOBALS['error']=='404'){
				$title='Ошибка 404 : Страница не найдена';
			}elseif(isset($GLOBALS['page_title'])){
				$title=$GLOBALS['page_title'];
			}else{
				$last_item=last($GLOBALS['gradusnik']);
				$title=$last_item['txt'];
			}
			smartas('title',$title);
			define('PAGE_TITLE_SKIP',true);
		}
	}

	function menu0(){
		$menu0_arr=$this->getChildren(0);
		foreach($menu0_arr as $key=>$item){
			if($item['menu_on']!='yes'){
				unset($menu0_arr[$key]);
			}
		}
		smartas('remove_subdomain',removeSubdomain());
		smartas('menu0_arr',$menu0_arr);
	}

	function menu1(){
		$menu1_arr=$this->getChildren( DOMAIN_ID );
		// _print_r($menu1_arr);
		foreach($menu1_arr as $key=>$item){
			if($item['menu_on']=='yes'){
				if( mb_strpos($item['url'],'://')>0 ){
					// оставляем как есть
				}elseif($item['redirect']=='yes'){
					$menu2_arr=$this->getChildren($item['id']);
					$menu1_arr[$key]['url']=DOMAIN_PATH.sprintf('/%s/%s/',$item['url'],$menu2_arr[1]['url']);
				}else{
					$menu1_arr[$key]['url']=DOMAIN_PATH.sprintf('/%s/',$item['url']);
				}
			}else{
				unset($menu1_arr[$key]);
			}
		}
		smartas('menu1_arr',$menu1_arr);
	}

	function menu12(){
		$menu1_arr=$this->getChildren( DOMAIN_ID );
		$num1=0;
		foreach($menu1_arr as $key1=>$item){
			if( $item['menu_on']!='yes' ){
				unset($menu1_arr[$key1]);
			}else{
				$num1++;
				unset($menu1);
				$menu1['id']=$item['id'];
				$menu1['txt']=defvar($item['title'],$item['alternative']);
				$menu1['url']=(mb_strpos($item['url'],'http://')===0)?$item['url']:DOMAIN_PATH.'/'.$item['url'].'/';
				$menu1['cls']=(mb_strpos($item['url'],'http://')===0)?'':$item['url'];
				$menu1['active']=(bool)($item['url']==$GLOBALS['path'][1]);
				$menu1['zindex']=1000-$num1;
				$children=$this->getChildren($item['id']);
				$num2=0;
				foreach($children as $key2=>$child){
					if( $child['menu_on']!='yes' ){
						unset($children[$key2]);
					}else{
						$num2++;
						$menu2['txt']=defvar($child['title'],$child['alternative']);
						$menu2['url']=(mb_strpos($child['url'],'http://')===0)?$child['url']:$menu1['url'].$child['url'].'/';
						if($menu1['active'] && $num2==1){
							$redirect=$menu2['url'];
						}
						$menu2['id']=$child['id'];
						$menu2['cls']='';
						$menu2['zindex']=2000-$num2;
						$menu1['children'][]=$menu2;
						if( $child['submenu_on']=='yes' ){
							$subchildren=$this->getChildren($child['id']);
							foreach($subchildren as $subchild){
								if( $subchild['menu_on']=='yes' ){
									$num2++;
									$menu2['txt']=defvar($subchild['title'],$subchild['alternative']);
									$menu2['url']=(mb_strpos($subchild['url'],'http://')===0)?$subchild['url']:$menu1['url'].$child['url'].'/'.$subchild['url'].'/';
									if($menu1['active'] && $num2==1){
										$redirect=$menu2['url'];
									}
									$menu2['id']=$subchild['id'];
									$menu2['cls']='l3';
									$menu2['zindex']=2000-$num2;
									$menu1['children'][]=$menu2;
								}
							}
						}
					}
				}
				/*
					//именно сейчас нужно сделать редирект из папок уровня 1 в первую папку уровня 2 
					//конечно если выбран один из разделов меню (menu_on=="yes")
					$structure_data=$GLOBALS['obj_client']->structure_data;
					if($structure_data[1]['id']>0 && $structure_data[2]['id']=='' && $redirect!=''){
						hexit('Location: '.$redirect);
					}
				*/
				$menu12_arr[]=$menu1;
			}
		}
		//_print_r($menu12_arr);
		smartas('menu12_arr',$menu12_arr);
	}

	function extramenu(){
		$root_children=$this->getChildren( DOMAIN_ID );
		$extra=array();
		foreach($root_children as $item){
			if( $item['extra_on']=='yes' && $item['is_hidden']=='no' ){
				$extraitem=array(
					'title'=>defvar($item['title'],$item['alternative']),
					'alternative'=>$item['alternative'],
					'url'=>$item['url'],
				);
				if(mb_strpos($extraitem['url'],'://')>0){
					$extraitem['http_link']=true;
				}else{
					$extraitem['url']=sprintf('/%s/', $extraitem['url']);
				}
				$extra[]=$extraitem;
			}
		}
		
		smartas('extra',$extra);
	}

	function menuDeep($parent_deep_type=''){
		// $parent_id_deep через слэш: 158/3/[all|current]
		// задаем параметры по-умолчанию
		$parent=$GLOBALS['obj_client']->structure_data[1]['id'];
		$deep=3;
		$type='current';

		if(!empty($parent_deep_type)){
			$explode=explode('/',$parent_deep_type);
			if(!empty($explode[0])){
				$parent=intval($explode[0]);
			}
			if(!empty($explode[1])){
				$deep=intval($explode[1]);
			}
			if(!empty($explode[2])){
				$type=$explode[2];
			}
		}
		// _print_r('$parent,$deep,$type',$parent,$deep,$type);
		$start=$this->getUrlById($parent);
		$menu_deep=$this->getDeepRec($parent,$start,$deep,$type);
		// _print_r($menu_deep);
		smartas('menu_deep',$menu_deep);
	}
	
	function getDeepRec($id,$start,$deep,$type,$level=1){
		$result=array();
		$children=$this->getChildren($id);
		foreach($children as $item){
			if( $item['menu_on']=='yes' ){
				$result_item=array(
					'title'=>defvar($item['title'],$item['alternative']),
				);
				if(mb_strpos($item['url'],'://')>0){
					$result_item['http_link']=true;
				}else{
					$result_item['url']=$start.$item['url'].'/';
				}
				if($level<$deep){
					// _print_r($_SERVER['REQUEST_URI'],$result_item['url'],mb_strpos($_SERVER['REQUEST_URI'],$result_item['url']));
					if($type=='all' || mb_strpos($_SERVER['REQUEST_URI'],$result_item['url'])===0){
						$result_item['children']=$this->getDeepRec($item['id'],$result_item['url'],$deep,$type,$level+1);
					}
				}
				$result[]=$result_item;
			}
		}
		return $result;
	}

	function sitemap(){
		$tree=$this->_getRecSiteMap( DOMAIN_ID );
		$tree=$this->_getRecSiteMapHTML($tree);
		smartas('tree',$tree);
	}

	function _getRecSiteMap($parent,$start='/'){
		$items=$this->ga(array(
			'fields'=>'url title alternative',//список полей которые нужно вытащить через запятую 'id,name,body'
			'filter'=>e5csql('parent=? and is_hidden="no" and sitemap_on="yes"',$parent),//строка фильтра типа 'parent=32'
			//'rel'=>e5csql(''),//условие 'usages_id=32' где usages это модель связанная ManyToMany с текущей
			//'extra'=>'',//массив типа array('select'=>'id,body', 'where'=>'parent>?', 'params'=>$parent)
			//'format'=>'',//название поля (например 'id'), значение которого будет лежать в ключах результирующего массива
			'order_by'=>'ordering',//строка типа или '-cdate' 'parent, +name' или '__random__' - случайный порядок
			'_slice'=>'0',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
		));
		if(count($items)>0){
			$result=array();
			foreach($items as $item){
				$url=$start.$item['url'].'/';
				$title=defvar($item['title'],$item['alternative']);
				if($item['id']==78){
					$model_portfolio=gmo('portfolio');
					$children=$model_portfolio->sitemap();
				}else{
					$children=$this->_getRecSiteMap($item['id'],$url);
				}
				$result[]=array(
					'url'=>$url,
					'title'=>$title,
					'children'=>$children
				);
			}
		}

		return $result;
	}
	
	function _getRecSiteMapHTML($arr){
		$result='';
		if(count($arr)>0){
			$result.='<ul>';
			foreach($arr as $key=>$item){
				$result.='<li>';
				$result.='<a href="'.$item['url'].'">'.$item['title'].'</a>';
				$result.=$this->_getRecSiteMapHTML($item['children']);
				$result.='</li>';
			}
			$result.='</ul>';
		}
		return $result;
	}
}
