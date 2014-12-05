<?php
/*
 * Created on 2013-1-28
 * S67
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 * codereview 2014-7-5 陈朱尧
 */
 class nns_ftp{
 	public $server_params;
 	public $connect_no;
 	public $errors = array();
 	
 	public function __construct($addr='',$user_name='',$user_pass='',$port='21'){
 		$this->server_params['addr']=$addr;
 		$this->server_params['user_name']=$user_name;
 		$this->server_params['user_pass']=$user_pass;
 		$this->server_params['port']=$port;
 	}
 	public function error()
 	{
 		return $this->errors;
 	}
 	/**
 	 * 连接FTP
 	 * @param int $time 网络超时时间
 	 * @return TRUE 连接成功
 	 * 			FALSE 连接失败
 	 */
 	public function connect($time=10,$passive=TRUE){
 		$this->connect_no=ftp_connect($this->server_params['addr'],$this->server_params['port'],$time);
 		if ($this->connect_no===FALSE) 
 		{
 			$this->errors[] = 'ftp_connect连接失败['.$this->server_params['addr'].':'.$this->server_params['port'].']';
 			return FALSE;
 		}
 		
 		$is_login = ftp_login(
 			$this->connect_no,
 			$this->server_params['user_name'],
 			$this->server_params['user_pass']
 		);
 		if(!$is_login)
 		{
 			$this->errors[] = 'ftp_login登录失败['.$this->server_params['addr'].':'.$this->server_params['port'].']';
 		}
 		$p = ftp_pasv($this->connect_no,$passive);
 		if( !$p )
 		{
 			$this->errors[] = 'ftp_pasv被动模式设置失败['.$this->server_params['addr'].':'.$this->server_params['port'].']';
 		} 		
 		return $is_login;
 	}
 	/**
 	 * 从FTP下载文件到本地
 	 * @param 要下载的文件地址
 	 * @param 下载文件保存地址  注意：本地文件目录必须存在
 	 * @return TRUE 下载成功
 	 * 			FALSE 下载失败
 	 */
 	public function get($remote_file,$source_file){
 		$ftp_get =  ftp_get(
 		$this->connect_no,  
 		$source_file, 
 		$remote_file, 
 		FTP_BINARY);
 		if( !$ftp_get )
 		{
			//czy 2014-7-5 以后不要这样写了，错误了，就要把错误码返回出来。desc中可以这样写。
 			$this->errors[] = 'ftp_get下载文件失败['.$this->server_params['addr'].':'.$this->server_params['port'].']';
 		} 	
 		return $ftp_get;
 	}
 	
 	/**
 	 * FTP上传文件
 	 * @param String 远程的文件地址
 	 * @param string 远程文件名
 	 * @param String 要上传的文件地址
 	 * @return TRUE 连接成功
 	 * 			FALSE 连接失败
 	 */
 	public function up($remote_path,$remote_file,$source_file){
 		if (!$this->mk_dir($remote_path)) 
 		{
 			$this->errors[] = 'mk_dir失败['.$this->server_params['addr'].':'.$this->server_params['port'].']';
 			return FALSE;
 		}
 		$ch=@ftp_chdir($this->connect_no,$remote_path);
 		if( !$ch )
 		{
 			$this->errors[] = 'ftp_chdir失败['.$this->server_params['addr'].':'.$this->server_params['port'].']';
 		} 		
 		$p = ftp_pasv($this->connect_no,true);
 		if( !$p )
 		{
 			$this->errors[] = 'ftp_pasv被动模式设置失败['.$this->server_params['addr'].':'.$this->server_params['port'].']';
 		}  		
 		$ftp_put = ftp_put(
 		$this->connect_no, 
 		$remote_file, 
 		$source_file, 
 		FTP_BINARY
 		);
 		if( !$ftp_put )
 		{
 			$this->errors[] = 'ftp_put上传文件失败['.$this->server_params['addr'].':'.$this->server_params['port'].']';
 		}
 		return  $ftp_put;		
 	}
	
	/**
	 * FTP 删除
	 * $params String 远程文件地址
	 * 
	 */
	public function delete($remote_file)
	{
		if(ftp_delete($this->connect_no, $remote_file))
		{
			return true;
		}
		return false;
	}

 	private function mk_dir($remote_path,$mode=0777){
 		$remote_path=rtrim($remote_path,"/");
 		$dir=explode("/", $remote_path);
		$path="";
		$ret = true;
		for ($i=0;$i<count($dir);$i++)
		{
			$path.="/".$dir[$i];
			
			if(!@ftp_chdir($this->connect_no,$path))
			{
			@ftp_chdir($this->connect_no,"/");
				if(!@ftp_mkdir($this->connect_no,$path))
				{
					$ret=false;
					break;
				} else {
					@ftp_chmod($this->connect_no, $mode, $path);
				}
			}
		}
		return $ret;
 	}
 	
 	public function __destruct(){
 		if ($this->connect_no)
 		ftp_close($this->connect_no);
 	}
 	
 }
?>