<?php
/**
 * Ќакопление отладочных сообщений
 * 
 * вывод осуществл€етс€ из debug_db.tpl
 *
 */
class Debug_db{
	var $res = array();
	var $saveStack = false;
	var $ignoresRe=null;
	var $returnCaller=false;
	var $includeRes = false;
	var $url = '';
	
	function Debug_db($saveStack = false, $includeRes = false, $ignoresRe=null, $returnCaller=false){
		$this->saveStack = $saveStack;
		$this->includeRes = $includeRes;
		$this->ignoresRe = $ignoresRe;
		$this->returnCaller = $returnCaller;
		$this->url = LIB_DIR.'debug/debug_db.php';
//		$this->ctrl_get();
		
//		if(empty($GLOBALS['debug_db_item']))
			$GLOBALS['debug_db_item'] = $this;
		return $GLOBALS['debug_db_item'];
	}
	
//	function getInstans($saveStack = false, $includeRes = false, $ignoresRe=null, $returnCaller=false){
	function getInstans(){
//		if(empty($GLOBALS['debug_db_item']))
//			$GLOBALS['debug_db_item'] = new Debug_db($saveStack, $includeRes, $ignoresRe, $returnCaller);
		return $GLOBALS['debug_db_item'];
	}
	
//	function ctrl_get(){
//		if(isset($_GET['ddb_filter'])){
//			session_start();
//			$gf = $_GET['ddb_filter'];
//			foreach ($gf as $k => $v){
//				switch ($k){
//					case 'model':
//						if(empty($_SESSION['ddb']))
//							$_SESSION['ddb'] = array();
//						if(empty($_SESSION['ddb']['filter_model']))
//							$_SESSION['ddb']['filter_model'] = array();
//						$_SESSION['ddb']['filter_model'][$v] = true;
//						continue 2;
//					case 'dmodel':
//						if(empty($_SESSION['ddb']))
//							continue 2;
//						if(empty($_SESSION['ddb']['filter_model']))
//							continue 2;
//						unset($_SESSION['ddb']['filter_model'][$v]);
//						continue 2;
//					case 'cmodel':
//						if(empty($_SESSION['ddb']))
//							continue 2;
//						unset($_SESSION['ddb']['filter_model']);
//						continue 2;
//				}
//			}
//				
//		}
//	}
	
	function isSaveSatck(){
		return $GLOBALS['debug_db_item']->saveStack;
	}
	function isIncludeRes(){
		return $GLOBALS['debug_db_item']->includeRes;
	}
	
	/**
	 * Ёлемент отладки
	 *
	 * @param string $title
	 * @param string $section
	 * @return Debug_point
	 */
	function add($title, $section = ''){
		$this_ = $GLOBALS['debug_db_item'];
		$item = new Debug_point($title);
		if (!array_key_exists($section, $this_->res))
			$this_->res[$section] = array();
		$this_->res[$section][] = $item;
		
		if ($this_->saveStack) {
			$stack = call_user_func_array (
				array($this_, 'debug_backtrace_smart'), 
				array($this_->ignoresRe, $this_->returnCaller)
			);
			// передаЄм масив вызовов пропустив себ€
			$item->setStack(array_slice($stack, 1));
		}
		
		return $item;
	}
	
	 /**
     * array debug_backtrace_smart($ignoresRe=null, $returnCaller=false)
     * 
     * Return stacktrace. Correctly work with call_user_func*
     * (totally skip them correcting caller references).
     * If $returnCaller is true, return only first matched caller,
     * not all stacktrace.
     * 
     * @version 2.03
     */
    function debug_backtrace_smart($ignoresRe=null, $returnCaller=false)
    {
        if (!is_callable($tracer='debug_backtrace')) return array();
        $trace = $tracer();
        
        if ($ignoresRe !== null) $ignoresRe = "/^(?>{$ignoresRe})$/six";
        $smart = array();
        $framesSeen = 0;
        for ($i=0, $n=count($trace); $i<$n; $i++) {
            $t = $trace[$i];
            if (!$t) continue;
                
            // Next frame.
            $next = isset($trace[$i+1])? $trace[$i+1] : null;
            
            // Dummy frame before call_user_func* frames.
            if (!isset($t['file'])) {
                $t['over_function'] = $trace[$i+1]['function'];
                $t = $t + $trace[$i+1];
                $trace[$i+1] = null; // skip call_user_func on next iteration
            }
            
            // Skip myself frame.
            if (++$framesSeen < 2) continue;

            // 'class' and 'function' field of next frame define where
            // this frame function situated. Skip frames for functions
            // situated in ignored places.
            if ($ignoresRe && $next) {
                // Name of function "inside which" frame was generated.
                $frameCaller = (isset($next['class'])? $next['class'].'::' : '') . (isset($next['function'])? $next['function'] : '');
                if (preg_match($ignoresRe, $frameCaller)) continue;
            }
            
            // On each iteration we consider ability to add PREVIOUS frame
            // to $smart stack.
            if ($returnCaller) return $t;
            $smart[] = $t;
        }
        return $smart;
    }
    
    function out(){
    }
}

class Debug_point{
	var $begin;
	var $execTime;
	var $title;
	var $info_arr = array();
	var $stack = '';
	
	function Debug_point($title, $info = null){
		$this->title = $title;
		$this->begin = $this->time();
		if($info)
			$this->addInfo($info);
	}
	
	
	function addInfo($info){
		$this->info_arr[] = $info;
	}
	
	function setStack($stack){
		//$this->stack = array_reverse($stack);
		if(is_array($stack))
			foreach ($stack as $s){
				if($s['args'] ){
					$s['args'] = $this->print_r($s['args'], true);
					//$s['args'] = $t;
				}
			}
		$this->stack = $stack;
	}
	function setRes($res){
		$this->res = $this->print_r($res, true);
	}
	
	function fix($info = null){
		$this->execTime = $this->time() - $this->begin;
		if($info)
			$this->addInfo($info);
	}
	
	/**
	 * “екущее врем€ в микросекундах
	 *
	 * @return float
	 */
	function time(){
		list($msec, $sec) = explode(' ', microtime());
        $now = (float)$sec + (float)$msec;
        return $now;
	}
	
	/**
     *  We need manual custom print_r() to use it in OB handlers
     * (original print_r() cannot work inside OB handler).
     */  
    function print_r($obj, $no_print=0, $level=0)
    {
        if ($level < 10) {
            if (is_array($obj)) {
                $type = "Array[".count($obj)."]"; 
            } elseif (is_object($obj)) {
                $type = "Object";
            } elseif (gettype($obj) == "boolean") {
                $type = $obj? "TRUE" : "FALSE";
            } elseif ($obj === null) {
                $type = "NULL";
            } elseif (is_string($obj)) {
            		$len = strlen($obj);
                	$type = "String[$len]: ". $obj;
            } else {
            	$type = '';
            	if (is_float($obj)) {
             	   $type = "Float";
            	} elseif (is_int($obj)) {
                	$type = "Int";
            	}
                $type .= preg_replace("/\r?\n/", "\\n", $obj);
            }
            $buf = $type;
            if (is_array($obj) || is_object($obj)) { 
                $leftSp = str_repeat("    ", $level+1);
                for (reset($obj); list($k, $v) = each($obj); ) {
                    if ($k === "GLOBALS") continue;
                    $buf .= "\n{$leftSp}[$k] => ".$this->print_r($v, $no_print, $level+1);
                }
            }
        } else {
            $buf = "*RECURSION*";
        }
        $buf = str_replace("\x00", " ", $buf); // PHP5 private methods contain \x00 in names
        if ($no_print) return $buf;
        else echo $buf;
        return null;
    }
}