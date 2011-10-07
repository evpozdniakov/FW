<?php

//_log($models_elements_tree);

$local=new ModelItemsTreeListHTML($this);

if(IS_AJAX){
	$tf=(p2v('tf'))
		?',"tf":"'.e5cjs(p2v('tf')).'"'
		:'';
	$result='{"html":"'.e5cjs($local->getRecModelItemsTree($models_elements_tree)).'"'.$tf.'}';
}else{
	$result='
		<script type="text/javascript">
			$.evCSSrule("#modelItems","display:none;");
		</script>
		<div class="offset">
			'.$this->getModelItemsFilter().'
			<div id="modelItems">
				'.$local->getRecModelItemsTree($models_elements_tree).'
			</div>
		</div>
	';
}

class ModelItemsTreeListHTML{
	function ModelItemsTreeListHTML($papa){
		$this->papa=$papa;
		$this->current_model_item_id=$this->papa->getModelItemIdFromGetQuery();
	}

	function getRecModelItemsTree($arr){
		$result='';
		if(is_array($arr) && !empty($arr)){
			$blocks='';
			foreach($arr as $id=>$item){
				//получаем html-код деток
				$children_code=(isset($item['children']))?$this->getRecModelItemsTree($item['children']):'';
				//получаем html-код ссылки
				$link_code=$this->getLink($item);
				//получаем родителя и деток в блоке
				$block=$this->getBlock($id,$item,$link_code,$children_code);
				$blocks.=$block;
			}
			$result='<ul class="itemsList">'.$blocks.'</ul>';
		}
		return $result;
	}

	function getLink($item){
		$type=is_array($item['type'])?$item['type']['element']:$item['type'];
		$url=is_array($item['url'])?$item['url']['element']:$item['url'];
		$class='class="li '.(isset($item['attr'])?$item['attr']:'').'"';

		if($type=='element_current'){
			$link='<span '.$class.'><b>'.$item['title'].'</b></span>';

		}elseif($type=='element'){
			$link='<span '.$class.'><a href="'.DOMAIN_PATH.$url.'">'.$item['title'].'</a></span>';

		}elseif($item['type']=='folder_empty'){
			$link='<span class="empty">'.$item['title'].'</span>';

		}else{
			$link='<span class="li">'.$item['title'].'</span>';
		}

		return $link;
	}

	function getBlock($id,$item,$link_code,$children_code){
		//_print_r('$item,$link_code,$children_code',$item,$link_code,e5c($children_code));
		$type=is_array($item['type'])?$item['type']['folder']:$item['type'];
		$url=is_array($item['url'])?$item['url']['folder']:$item['url'];
		if($type=='folder_closed' || $type=='folder_opened'){
			$href=DOMAIN_PATH.$url;
			if($type=='folder_closed'){//закрытая папка [+]
				$cls='plus';
				$plus_minus='+';
			}else{//открытая папка [-]
				$cls='minus';
				$plus_minus='-';
			}
			$block='
				<div class="children">
					<div class="nobullet">
						<a class="'.$cls.'" href="'.$href.'"><span>'.$plus_minus.'</span></a>
						'.$link_code.'
					</div>
					'.$children_code.'
				</div>
			';
		}else{
			$block=$link_code;
		}
		$type=is_array($item['type'])?$item['type']['element']:$item['type'];
		if($type=='element' || $type=='element_current'){
			$id_string=' id="'.$GLOBALS['path'][2].$id.'"';
		}else{
			$id_string='';
		}
		$block='<li'.$id_string.'>'.$block.'</li>';

		return $block;
	}
}

