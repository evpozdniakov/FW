<?php

class DB{
	function DB(){
	}

	function connect(){
		if(!array_key_exists('dbc',$GLOBALS) || !is_resource($GLOBALS['__dbc__'])){
			$GLOBALS['__dbc__']=mysql_connect(DBHOST, DBUSER, DBPASSWORD) or _die(mysql_error());
			mysql_select_db(DBNAME) or _die(mysql_error());
			if(defined('DBSETNAMES')){
				mysql_query('SET NAMES '.DBSETNAMES);
			}
		}
	}

	function query($args){
		//если подключение еще не установлено, делаем это
		if(!isset($GLOBALS['__dbc__']) || !is_resource($GLOBALS['__dbc__'])){
			$this->connect();
		}
		if(is_string($args)){
			$args=func_get_args();// Получаем все аргументы функции.
		}
		$this->query=e5csql($args); // Формируем запрос по шаблону.
		
		if(DEBUG_DB===true){
			$debug_item = $GLOBALS['debug_db_item']->add($this->query, 'DB->query');
			$this->fulfil();
			$debug_item->fix();
			if($GLOBALS['debug_db_item']->isIncludeRes()){
				$debug_item->setRes($this->items);
			}
		}
		else $this->fulfil();
	}

	function fulfil(){
		$this->items=array();
		$results=mysql_query($this->query);
		if($results===false){
			$this->error=mysql_error($GLOBALS['__dbc__']);
			$this->error="\r\n".'>'.date('ymd H:i')."\t".'"'.$this->query.'"<-'.$this->error;
			$log=file2str('/admin/','_sql.log');
			$log.=$this->error;
			if(DEBUG_EMAIL_ERROR===true && defined(ADMIN_EMAIL) && validateEmail(ADMIN_EMAIL)){
				htmlmail(ADMIN_EMAIL,'project_log SQL '.$_SERVER['SERVER_NAME'],$message);
			}
			fileWrite('/admin/','_sql.log',$log);
			if(DEBUG===true){
				_die($this->error);
			}
		}elseif(is_resource($results)){
			//while($row=mysql_fetch_array($results,MYSQL_BOTH)){
			while($row=mysql_fetch_assoc($results)){
				$this->items[]=$row;
			}
			$this->rows=mysql_num_rows($results);
			if($this->rows==1){
				$this->line=$this->items[0];
				if(count($this->items[0])==1){
					$this->item=implode('',$this->items[0]);
				}
			}
		}else{
			$this->affected=mysql_affected_rows();
		}
	}

	function getItem($name){//echo $name;
		if($this->rows>0){
			foreach($this->items as $items){
				$result=$items[$name];
			}
		}
		return $result;
	}

	function getItems($str){
		$str=str_replace(' ', '', $str); //на всякий избавляемся от пробелов
		$arr=explode(",", $str);
		foreach($this->items as $items){
			foreach($arr as $arr_item){
				$result[$arr_item]=$items[$arr_item];
			}
		}
		return $result;
	}

	function close(){//echo '[disconect '.$GLOBALS['__dbc__'].']';
		if(isset($GLOBALS['__dbc__']) && is_resource($GLOBALS['__dbc__'])){
			mysql_close($GLOBALS['__dbc__']);
			unset($GLOBALS['__dbc__']);
		}
	}

	function upDown($tabname, $id, $move, $condition=''){
		$this->query('select order_list from '.$tabname.' where id=?',$id);
		$order_list=$this->getItem('order_list');
		$condition=($condition!='')?$condition:'1';
		if($move=='up'){
			$order_type='desc';
			$comparation='<=';
		}else{
			$order_type='asc';
			$comparation='>=';
		}
		$cur=-1;
		$this->query('
			select
				id, order_list
			from
				'.$tabname.'
			where
				'.$condition.'
				and order_list'.$comparation.$order_list.'
			order by
				order_list '.$order_type.'
			limit
				2
		');
		if($this->rows==2){
			foreach($this->items as $items){
				$cur++;
				$arr['id'][$cur]=$items['id'];
				$arr['order_list'][$cur]=$items['order_list'];
			}
			$this->query('update '.$tabname.' set order_list=? where id=?',$arr['order_list'][1],$arr['id'][0]);
			$this->query('update '.$tabname.' set order_list=? where id=?',$arr['order_list'][0],$arr['id'][1]);
		}
	}

	function reorder($tabname, $condition=''){
		$condition=($condition!='')?$condition:'1';
		$this->query('select id from '.$tabname.' where '.$condition.' order by order_list');
		$num=0;
		if(is_array($this->items)){
			foreach($this->items as $items){
				$num++;
				$dbq=new DBQ('update '.$tabname.' set order_list=? where id=?',$num,$items['id']);
			}
		}
	}

	function lastId(){
		$this->query('select last_insert_id()');
		if($this->rows==1){
			$result=$this->item;
		}else{
			_die('error in DB->lastId()');
		}
		return $result;
	}
}

class DBQ extends DB{
	function DBQ(){
		$args=func_get_args();
		$this->query($args);
	}
}

