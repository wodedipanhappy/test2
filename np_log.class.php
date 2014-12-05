<?php
/*
 * Created on 2012-09-17
 *  日志类
 *  usage：
 *  $log =  new np_log_class();
 *  $log->set_path('./log_path');
 *  $log->write($msg);
 *
 */
class np_log_class {
    public $log_path ='.';
    public function __construct($log_path = null) {
    	$log_path && $this->log_path = $log_path;
    	if(!is_dir($this->log_path))
    	    mkdir($this->log_path,0777,true);
    }
    public function set_path($log_path){
    	$log_path && $this->log_path = $log_path;
    	if(!is_dir($this->log_path))
    	    mkdir($this->log_path,0777,true);
    }
    public function write($msg){
    	$log_path = rtrim($this->log_path,DIRECTORY_SEPARATOR);
    	$log_path .= DIRECTORY_SEPARATOR.date('Ymd').'.txt';
	    @touch($log_path);
	    @chmod($this->log_path,0777);
        //写入日志
		$msg = "[".date('Y-m-d H:i:s')."] ".$msg."\n";
		error_log($msg,3,$log_path);
    }
}
