<?php

class StructureViews extends Structure{
	var $views_arr=array(
		'sitemap'=>'карта сайта',
	);
 
	function init(){
	}
	
	function gradusnik(){
		if(count($GLOBALS['gradusnik'])>2){
			$gradusnik=array_slice($GLOBALS['gradusnik'],1);
			$url='/';
			foreach($gradusnik as $key=>$item){
				$url.=$item['url'].'/';
				$gradusnik[$key]['url']=$url;
			}
			smartas('gradusnik',$gradusnik);
		}
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
		$parent_id=$this->getStructureRootId();
		$menu1_arr=$this->getChildren($parent_id);
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
		$menu1_arr=$this->getChildren($this->getStructureRootId());
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

	function menu2(){
		$parent_id=$GLOBALS['obj_client']->structure_data[1]['id'];
		if($parent_id){
			$menu2_arr=ga(array(
				'classname'=>'structure',
				'fields'=>'url title alternative',//список полей которые нужно вытащить через запятую 'id,name,body'
				'filter'=>e5csql('parent=? and is_hidden="no" and menu_on="yes"',$parent_id),//строка фильтра типа 'parent=32'
				'order_by'=>'ordering',//строка типа или '-cdate' 'parent, +name', где необязательный "+" это asc, а "-" это desc
				'_slice'=>'0',//строка 'n[,m]' возвращает массив элементов начиная с n (заканчивая m, если m передан)
			));
			if(count($menu2_arr)){
				smartas('menu2_prefix','/'.$GLOBALS['obj_client']->structure_data[1]['url'].'/');
				smartas('menu2_arr',$menu2_arr);
			}
		}
	}
	
	function menuDeep($parent_id_deep=''){
		// $parent_id_deep через слэш: 158/3
		$deep=3;
		if(!empty($parent_id_deep)){
			$explode=explode('/',$parent_id_deep);
			$parent_id=intval($explode[0]);
			if(!empty($explode[1])){
				$deep=$explode[1];
			}
		}else{
			$parent_id=$GLOBALS['obj_client']->structure_data[1]['id'];
		}
		// _print_r($parent_id,$deep);
		$start=$this->getUrlById($parent_id);
		$menu_deep=$this->getDeepRec($parent_id,$start,$deep);
		// _print_r($menu_deep);
		smartas('menu_deep',$menu_deep);
	}
	
	function getDeepRec($id,$start,$deep,$level=1){
		$result=$this->getChildren($id);
		foreach($result as $key=>$item){
			if($item['menu_on']!="yes"){
				unset($result[$key]);
				continue;
			}
			$item_replace=array('title'=>defvar($item['title'],$item['alternative']));
			if(mb_strpos($item['url'],'://')>0){
				$item_replace['http_link']=true;
			}else{
				$item_replace['url']=$start.$item['url'].'/';
			}
			if($level<$deep){
				// _print_r($_SERVER['REQUEST_URI'],$item_replace['url'],mb_strpos($_SERVER['REQUEST_URI'],$item_replace['url']));
				if(mb_strpos($_SERVER['REQUEST_URI'],$item_replace['url'])===0){
					$item_replace['children']=$this->getDeepRec($item['id'],$item_replace['url'],$deep,$level+1);
				}
			}
			$result[$key]=$item_replace;
		}
		return $result;
	}

	function sitemap(){
		$tree=$this->_getRecSiteMap($this->getStructureRootId());
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
