<?php
/**
 * @author 帅富元 2014-07-15
 * ZIP解压压缩类
 * 注：由于PHP自身所带的ZIP类不支持加密解压压缩的功能，故使用linux命令执行解压压缩
 * 该类仅适用于linux系统
 */
class np_zip {
	/**
	 * 解压ZIP包至固定目录
	 * @param ZIP包地址,需要有PHP执行权限
	 * @param 解压至目录地址，需要有PHP写入权限
	 * @param 解压密码
	 * @return TRUE 解压成功
	 * 			FALSE 解压失败
	 */
	static public function unzip($zip_filename,$unzip_dir,$password=NULL){
		$old=umask(0);
		if (!is_dir($unzip_dir)){
			mkdir($unzip_dir,0777,true);
		}
		
		if (!file_exists($zip_filename)){
			umask($old);
			 return FALSE;
		}
		
		$ext=end(explode('.', $zip_filename));
		$ext=strtolower($ext);
		if ($ext!='zip'){
			umask($old);
			return FALSE;
		}
		
		unset($ext);
		
		$password_command='';
		if (isset($password) && !empty($password)){
			$password_command = ' -P'.$password.' ';
		}
		
		$command='unzip -u '.$password_command.$zip_filename.' -d '.$unzip_dir;
		
		$ret=self::exec_command($command);
		
		if ($ret!=0){
			umask($old);
			return 	FALSE;
		}
		
		$command='chmod -R 777 '.$unzip_dir;
		
		$ret=self::exec_command($command);
		umask($old);
		if ($ret==0){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	/**
	 * 压缩文件至ZIP包
	 * @param 要压缩的目录路径,需要有PHP执行权限
	 * @param 压缩后保存路径，需要有PHP写入权限
	 * @param 解压密码
	 * @return TRUE 解压成功
	 * 			FALSE 解压失败
	 */
	static public function zip($dir,$zip_filename,$password=NULL){
		
		if (!is_dir($dir)){
			return FALSE;
		}
		$old=umask(0);
		
		$dest_path=dirname($zip_filename);
		if (!is_dir($dest_path)){
			mkdir($dest_path,0777,true);
		}
		
		$ext=end(explode('.', $zip_filename));
		$ext=strtolower($ext);
		if ($ext!='zip'){
			umask($old);
			return FALSE;
		}
		
		$password_command='';
		if (isset($password) && !empty($password)){
			$password_command = ' -P'.$password.' ';
		}
		

		$command='zip -rj '.$zip_filename.' '.$dir.$password_command;
		
		$ret=self::exec_command($command);
		if ($ret!=0){
			umask($old);
			return 	FALSE;
		}
		
		$command='chmod -R 777 '.$zip_filename;
		
		$ret=self::exec_command($command);
		umask($old);
		if ($ret==0){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	static public function exec_command($command){
		$command=escapeshellcmd($command);
		exec($command,$output,$ret);
		
		return $ret;
	}
}
?>