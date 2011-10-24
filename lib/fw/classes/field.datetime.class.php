<?php

class DateTimeField extends DateField{
	var $null=true;

	function constantProps(){
		$this->type='datetime';
		$this->maxlength='';
	}

	/**
	 * переопределяемый в потомках метод, который возвращает 
	 * инициализирующее значение для данного поля на основе $hash['model_item_init_values'],
	 * для большинства полей это значение равно $hash['model_item_init_values'][$this->db_column]
	 * 
	 * $hash['model_item_init_values'] - инициализирующий массив всех значений элемента модели
	 */
	function getModelItemInitValue($hash){
		$model_item_init_values=$hash['init_values'];
		/*
			переопределяемый в потомках метод, который возвращает 
			инициализирующее значение для данного поля на основе $model_item_init_values,
			для большинства полей это значение равно $model_item_init_values[$this->db_column]

			$model_item_init_values - инициализирующий массив всех значений элемента модели, 
			как правило, полученный из $_POST
		*/
		
		if(!empty($model_item_init_values[$this->db_column]) && parseDate($model_item_init_values[$this->db_column])!=false){
			// возможно, что для этого поля задается инициализирующее значение
			$result=$model_item_init_values[$this->db_column];
		}elseif($this->default=='now()' && $model_item_init_values['id']==0){
			// возможно, что для этого поля по умолчанию выставлено "now()"
			$result=date('Y-m-d H:i:s');
		}else{
			// иначе получаем значение даты из input.datepicker
			$hour=$model_item_init_values[$this->db_column.'_hour'];
			$minute=$model_item_init_values[$this->db_column.'_minute'];
			// и даты из input.datepicker
			$date_arr=explode('.',$model_item_init_values[$this->db_column]);
			$day=$date_arr[0];
			$month=$date_arr[1];
			$year=$date_arr[2];
			//проверяем полученную дату на корректность
			if(checkdate((int)$month,(int)$day,(int)$year)){
				$result=''.$year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':00';
			}
		}
		return $result;
	}
}

?>