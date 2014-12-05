<?php

/*
 * create 2014-06-30 帅富元
 * 
 * codeview 2014-06-31 向松
 * 问题已标注于代码中
 * 
 * codeview 2014-07-01 向松
 * 问题已修复，当前无问题
 *
 * codeview 2014-07-05 陈朱尧
 * 增加了新检测的问题，另外把公有函数放前面，这样这个接口看的时候，大家容易定位
 */

include_once 'np_db_factory.php';
class np_db_check {

	const DB_CHECK_VERSION = 'v1.0';
	
	//DB检测脚本失效时间
	//2014-07-05 czy
	//建议失效时间改成90秒，这样如果有MYSQL某个机器当了，也能检测出来，并且可以使用，否则当了，但检查用了30秒，这个检查结果又失效了
	const DB_CHECK_INI_EXPIRATION_TIME = 90;
	
	
//	DB读模式
	const DB_TYPE_WRITE='w';
//	DB写模式
	const DB_TYPE_READ='r';
//	0 为都用写数据库
	const DB_MODE_ALWAYS_WRITE=0;
//	1 读写分离 读数据库挂掉了用写数据库
	const DB_MODE_READ_WRITE_SEPARATE=1;
//	2 读写不分离 当前都用写数据库 写数据库挂了用读数据库
	const DB_MODE_WRITE_FRIST=2;
//	30 都用读数据库，并读数据库按权重随机选择
	const DB_MODE_ALWAYS_READ_RANDOM=30;
//	31 都用读数据库，顺序优先
	const DB_MODE_ALWAYS_READ_SEQUENCE=31;
	
	/**
	 * 记录DB服务器检查状态 
	 * @param 状态
	 * array(
	 * 	0=>array("host"=>服务器IP,"down"=>是否当机 1为当机0为未当,"process"=>当前进程数)
	 * )
	 * @param 记录INI文件路径
	 * @param 记录INI文件名，不包含后缀名
	 * @return TRUE 写入成功
	 * 			FALSE 写入失败
	 */
	private static function __record_db_status($status, $path, $ini_file_name) {
		if (!is_dir($path)) {
			self :: __create_dir($path);
		}

		$date = date('Y-m-d H:i:s');

		$ini_info = self :: DB_CHECK_VERSION . "\n";
		$ini_info .= "{$date}\n";
		$ini_info .= "[HOST],[TYPE],[ISDOWN],[PROCESS]\n";
		foreach ($status as $status_item) {
			$host = $status_item['host'];
			$down = $status_item['down'];
			$process = $status_item['process'];
			$type = $status_item['type'];
			$ini_info .= "{$host},{$type},{$down},{$process}\n";
		}

		$file_name = self :: __get_file_full_path($path, $ini_file_name);

		return self :: __write_to_ini($ini_info, $file_name);
	}
	
	private static function __get_file_full_path($path, $ini_file_name) {
		$path = rtrim($path, '/');

		$file_name = $path . '/' . $ini_file_name . '.ini';

		return $file_name;
	}

	private static function __write_to_ini($contents, $file_name) {
		$fp = fopen($file_name, "w+");
		
		if (!$fp) return FALSE;
		
		$is_ok = fwrite($fp, $contents);
		
		fclose($fp);
		//czy 2014-07-05 close了的资源，养成关闭的习惯
		$fp = NULL;
		
		if ($is_ok === FALSE) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	private static function __create_dir($path) {
		mkdir($path, 0777, true);
		chmod($path, 0777);
	}
	/**
	 * 获取写库配置
	 * @param 写库配置组
	 * @param 读库配置组
	 * @param 数据库负载模式 
	 *  0 为都用写数据库
		1 读写分离 读数据库挂掉了用写数据库
		2 读写不分离 当前都用写数据库 写数据库挂了用读数据库
		30 都用读数据库，并读数据库随机选择
		31 都用读数据库，顺序优先
	 * @return NULL 没有可用配置
	 * 			config 可用数据库配置
	 */
	public static function get_write_db_config($write_configs, $read_configs, $db_mode, $path, $ini_file_name) {

		return self :: __get_write_db_config_by_db_mode($write_configs, $read_configs, $db_mode,$path, $ini_file_name);
	}
	/**
	 * 获取读库配置
	 * @param 写库配置组
	 * @param 读库配置组
	 * @param 数据库负载模式 
	 *  0 为都用写数据库
		1 读写分离 读数据库挂掉了用写数据库
		2 读写不分离 当前都用写数据库 写数据库挂了用读数据库
		30 都用读数据库，并读数据库随机选择
		31 都用读数据库，顺序优先
	 * @return NULL 没有可用配置
	 * 			config 可用数据库配置
	 */
	public static function get_read_db_config($write_configs, $read_configs, $db_mode, $path, $ini_file_name) {
		$configs = self :: __check_db_by_ini($read_configs, $path, $ini_file_name,self::DB_TYPE_READ);
		return self :: __get_read_db_config_by_db_mode($write_configs, $configs, $db_mode,$path, $ini_file_name);
	}
	
	/**
	 * 通过INI文件筛选正确的数据库配置
	 * @param 配置数组
	 * @param INI文件路径
	 * @param INI文件名
	 * @param 读或写类型 DB_TYPE_WRITE | DB_TYPE_READ
	 * @return config配置
	 * 
	 */
	private static function __check_db_by_ini($configs, $path, $ini_file_name,$write_or_read) {
		$file_name = self :: __get_file_full_path($path, $ini_file_name);

		if (!file_exists($file_name))
			return $configs;

		$handle = fopen($file_name, "r");

		if ($handle === FALSE)
			return $configs;
		
		$size = filesize ($file_name);
		$content=fread($handle,$size);
		
		if (empty($content)){
			return $configs;
		}
		$lines = explode("\n",$content);
				
//		$lines = array ();
//		while (!feof($handle)) {
//			$lines[] = fgets($handle);
//		}
		fclose($handle);
		unset ($handle);

		$version = $lines[0];
		$date = $lines[1];
		
		//czy 2014-07-05 文件已经超时
		if (time() - strtotime($date) > self :: DB_CHECK_INI_EXPIRATION_TIME) {
			return $configs;
		}
		
		
//		将INI检查结果封装成KEY-VALUE
//		KEY为  IP|读写模式
		$status = array();
		foreach ($lines as $key=>$line){
			if (($key < 3) || empty($line))  continue;
			$st = explode(',', $line);
			$key = $st[0]."|".$st[1];
			$status[$key]=$st;
		}

		
		$new_configs = array ();
		foreach ($configs as $config) {
			
			$key=$config['host'].'|'.$write_or_read;
			
			//若检查没有对应的服务器IP，证明INI文件有错，不进行任何筛选直接返回
			if (!isset($status[$key])) 
				return $configs;
			
			
			if ($status[$key][2] == 0 && (int) $status[$key][3] < $config['db_process_max_limit']) {
				$new_configs[] = $config;
			}
		}
	
		unset ($configs);
		unset ($lines);
		return $new_configs;
	}

	private static function __get_write_db_config_by_db_mode($write_configs, $read_configs, $db_mode,$path, $ini_file_name) {
		$c = NULL;
		switch ($db_mode) {
			case self::DB_MODE_ALWAYS_WRITE :
				if (count($write_configs) > 0) {
					$c = $write_configs[0];
					$c['mode'] = 'WRITE';
				}
				break;
			case self::DB_MODE_READ_WRITE_SEPARATE :
				if (count($write_configs) > 0) {
					$c = $write_configs[0];
					$c['mode'] = 'WRITE';
				}
				break;
			//写数据库挂了用读数据库，但不准写入，只可以查看
			case self::DB_MODE_WRITE_FRIST :
				if (count($write_configs) > 0) {
					$c = $write_configs[0];
					$c['mode'] = 'WRITE';
				} else {
					if (count($read_configs) > 0) {
						$c = $read_configs[0];
						$c['mode'] = 'READ';
					}
				}
				break;
			case self::DB_MODE_ALWAYS_READ_RANDOM :
				if (count($read_configs) > 0) {
					$c = self::__get_random_db_config_by_weight($read_configs);
					$c['mode'] = 'READ';
				} else {
					$c = NULL;
				}
				break;

			case self::DB_MODE_ALWAYS_READ_SEQUENCE :
				if (count($read_configs) > 0) {
					$c = $read_configs[0];
					$c['mode'] = 'READ';
				} else {
					$c = NULL;
				}
				break;
		}

		return $c;
	}

	private static function __get_read_db_config_by_db_mode($write_configs, $read_configs, $db_mode,$path, $ini_file_name) {
		$c = NULL;
		switch ($db_mode) {
			case self::DB_MODE_ALWAYS_WRITE :
				$c = self::get_write_db_config($write_configs, $read_configs,$db_mode,$path, $ini_file_name);
				break;
			case self::DB_MODE_READ_WRITE_SEPARATE :
				if (count($read_configs) > 0) {

					$c = self :: __get_random_db_config_by_weight($read_configs);
				} else {
					$c =  self::get_write_db_config($write_configs, $read_configs,$db_mode,$path, $ini_file_name);
				}

				break;
			case self::DB_MODE_WRITE_FRIST :
				
				if (count($write_configs) > 0){
					$write_configs = self :: __check_db_by_ini($write_configs, $path, $ini_file_name,self::DB_TYPE_WRITE);
					$c = self :: get_write_db_config($write_configs, $read_configs,$db_mode,$path, $ini_file_name);
				}
				
				if ($c === NULL) {
					if (count($read_configs) > 0) {
						$c = $read_configs[0];
						//czy 2014-07-05 read_configs中没有标记mode=read，需要补上，否则外部如果一个用错，就会向读库写入数据
//						s67 2014-07-06 在该函数最后一行已经标记，
//						因为这里是获取读库配置，所以不管最终返回是写库还是读库都应该是标记为读模式
					} else {
						$c = NULL;
					}
				}
				break;
			case self::DB_MODE_ALWAYS_READ_RANDOM :
				if (count($read_configs) > 0) {
					$c = self :: __get_random_db_config_by_weight($read_configs);
				} else {
					$c = NULL;
				}
				break;

			case self::DB_MODE_ALWAYS_READ_SEQUENCE :
				if (count($read_configs) > 0) {
					$c = $read_configs[0];
				} else {
					$c = NULL;
				}
				break;
		}
		if (is_array($c)) $c['mode'] = 'READ';
		return $c;
	}

	private static function __get_random_db_config_by_weight($configs) {
		$rand_total_num=0;
		foreach ($configs as $key=>$config){
			$rand_total_num += (int)$config['db_access_weight'];
			$configs[$key]['rand']=$rand_total_num;
		}
		
		$rand = rand(1, $rand_total_num);
		
		foreach ($configs as $config){
			if ($config['rand'] >=$rand){
				unset($config['rand']);
				return $config;
			}
		}
		
		unset($configs[0]['rand']);
		
		return $configs[0];
		
	}
	
	
	
	/**
	 * @param DB CONFIG
	 * @return 
	 * $return['down']= 0为正常，1为异常
	 * $return['process']= 进程数
	 */
	private static function __check_db_is_ok($config,$sql=''){
		$db = np_db_factory_create($config);
		$bool = $db->open();
		
		if (!$bool) {
			return array("down"=>1,"process"=>0);
		}
		
		//czy 2014-07-05 这个地方执行了什么SQL，不建议执行，如果这个SQL卡住，整个检测工具就挂死了，
		//如果这样，整个安全保护就没有了
//		if (!empty($sql)){
//			
//			$re = $db->query($sql);
//			if ($re===FALSE) 
//				return array("down"=>1,"process"=>0);
//			
//			$re = $db->get_query_result(TRUE);
//			
//			if ($re===FALSE) 
//				return array("down"=>1,"process"=>0);
//		}
		
		
		$command = 'SELECT count(1) as num FROM INFORMATION_SCHEMA.PROCESSLIST WHERE COMMAND <> "sleep"';
		$re = $db->query($command);
		
		if ($re===FALSE) return array("down"=>1,"process"=>0);
		
		$re = $db->get_query_result(TRUE);
	
		$db->close();
		$db = NULL;

		if ($re===FALSE) return array("down"=>1,"process"=>0);
		
		return array("down"=>0,"process"=>$re[0]['num']);;
	}
	
	/**
	 * 
	 * 数据库检查函数
	 * @param 要检查的写数据库配置
	 * 格式:
			$config["host"] = IP地址:port端口
			$config["user"] = 用户名
			$config["passwd"] = 密码
			$config["db_name"] = 数据库名		
			$config["db_log"] = 数据库操作调试参数 NP_DB_LOG_NULL | NP_DB_LOG_ERROR | NP_DB_LOG_ALL | NP_DB_LOG_SQL_DEBUG_EXPLAIN		
			$config["db_access_weight"]=数据库分配权重
			$config["db_process_max_limit"]=数据库连接进程数上限
			
	 * @param 要检查的读数据库配置
	 * 格式:
			$config["host"] = IP地址:port端口
			$config["user"] = 用户名
			$config["passwd"] = 密码
			$config["db_name"] = 数据库名		
			$config["db_log"] = 数据库操作调试参数 NP_DB_LOG_NULL | NP_DB_LOG_ERROR | NP_DB_LOG_ALL | NP_DB_LOG_SQL_DEBUG_EXPLAIN		
			$config["db_access_weight"]=数据库分配权重
			$config["db_process_max_limit"]=数据库连接进程数上限
			
	 * @param INI文件路径
	 * @param INI文件名
	 * @param 为了防止进程堵死，可以传一个快速查询的SQL来检测，数据库查询是否堵死，若查询失败将判断为当机
	 * @return
	 * 格式：
	 * $return["state"]=写入INI成功为TRUE 写入INI失败为FALSE
	 * $return["data"]=检查的状态结果
	 * $return["data"][0]["host"]= IP地址:port端口
	 * $return["data"][0]["down"]= 是否当机 0为正常 1为不正常
	 * $return["data"][0]["type"]= 读写类型  np_db_check::DB_TYPE_WRITE | np_db_check::DB_TYPE_READ
	 * $return["data"][0]["process"]= 当前数据库执行进程数
	 */
	public static function check_multiple_db_config($write_configs,$read_configs,$path,$ini_file_name,$sql=''){
		
		if (!is_array($write_configs)) 
			$write_configs=array();
		if (!is_array($read_configs)) 
			$read_configs=array();
		
		$result=array();
		
		foreach ($write_configs as $key=>$config){
			$re=self::__check_db_is_ok($config,$sql);		
			
			$result[]=array(
				'host'=>$config['host'],
				'down'=>$re['down'],
				'type'=>self::DB_TYPE_WRITE,
				'process'=>$re['process'],
				);
		}
		
		foreach ($read_configs as $key=>$config){
			$re=self::__check_db_is_ok($config,$sql);
			
			$result[]=array(
				'host'=>$config['host'],
				'down'=>$re['down'],
				'type'=>self::DB_TYPE_READ,
				'process'=>$re['process'],
				);
		}
		
		//czy 2014-07-05 这一行有啥用？不明白，一般不要试图修改入参
//		s67 2014-07-06 这里是销毁对象
//		unset($a)  等价于  $a=NULL
		unset($write_configs,$read_configs);
		
		$re = self::__record_db_status($result,$path,$ini_file_name);
	
		return array(
			'state'=>$re,
			'data'=>$result
		);
	}

}
?>
