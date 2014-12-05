<?php
/*
 * 功能,通过检查模式读取数组值
 * */
function np_array_get_value_safe( $a, $k )
{
	if( is_array( $a )  )
	{
		if ( isset( $a[$k] ) )
			return $a[$k];
	}

	return "";
}
/*
 * 功能,给一个二维数组,根据KEY重建索引
 * */
function np_array_rekey( $a, $key )
{
	if( is_array( $a ) )
	{
		$result = array();
		foreach( $a as $i )
		{
			$result[$i[$key]] = $i;
		}
		return $result;
	}
	return array();
}
function np_array_random($arr, $num = 1) {
    shuffle($arr);

    $r = array();
    for ($i = 0; $i < $num; $i++) {
        $r[] = $arr[$i];
    }
    return $num == 1 ? $r[0] : $r;
}


function np_array_remove_empty($arr){
	foreach ($arr as $key=>$v){
		if (is_array($v)){
			$arr[$key]=np_array_remove_empty($v);
		}elseif(strlen(trim($v))==0){
			unset($arr[$key]);
		}
	}
	return $arr;
}


/** 检查参数是否合理
 * @param $func 方法名
 * @param $params 参数判断组
 *   $item 参数判断策略
 * 		$item['value']	参数值
 * 		$item['key']	参数KEY
 * 		$item['rule']	判断策略,'|'隔开
 */
function np_check_params_policy($params){
	
		$reason='';
		$bool=true;
		foreach ($params as $item){
			$rules=explode('|',$item['rule']);
			
			foreach ($rules as $rule_item){
				
				switch ($rule_item){
					case 'noempty':
						if (empty($item['value']) && $item['value']!=='0'){
							$bool=FALSE;
							$reason.=$item['key']. ' is not '.$rule_item.'.';
							continue;
						}
					break;
					case 'int':
//					可为空，代表0
						if (!empty($item['value'])){
							if (!is_numeric($item['value']) || strpos($item['value'],".")!==false){
								$bool=FALSE;
								$reason.=$item['key']. ' is not '.$rule_item.'.';
								continue;
							}
						}
					break;
				}
			}
		}
		return array(
			'state'=>$bool,
			'reason'=>$reason
		);
	}
	
/*
 * czy 2014-07-05
 * 这个函数以后不能调来调去了，这个在芒果逻辑中可能是要跳过代理，但在其它项目的，却不是的。
 * 这类函数要有明确的定义，比如你有二个需求，可以建二个函数 np_get_client_ip_proxy_support()代表那怕过了代理，也尽可能用代理传上来的标准地址
*/
function np_get_ip(){
	return np_get_client_ip_proxy_support();
//    if (isset($_SERVER))
//    {
//    	代理过来的IP屏蔽 有安全隐患  BY S67
//      下一步应该是针对包含HTTP_X_FORWARDED_FOR报头信息的请求给予拒绝

//        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
//        {
//            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
//
//            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
//            foreach ($arr AS $ip)
//            {
//                $ip = trim($ip);
//
//                if ($ip != 'unknown')
//                {
//                    $realip = $ip;
//
//                    break;
//                }
//            }
//        }
//        else
/*
 * REMOTE_ADDR 是你的客户端跟你的服务器“握手”时候的IP。如果使用了“匿名代理”，REMOTE_ADDR将显示代理服务器的IP。
HTTP_CLIENT_IP 是代理服务器发送的HTTP头。如果是“超级匿名代理”，则返回none值。同样，REMOTE_ADDR也会被替换为这个代理服务器的IP。
$_SERVER['REMOTE_ADDR']; //访问端（有可能是用户，有可能是代理的）IP
$_SERVER['HTTP_CLIENT_IP']; //代理端的（有可能存在，可伪造）
$_SERVER['HTTP_X_FORWARDED_FOR']; //用户是在哪个IP使用的代理（有可能存在，也可以伪造）
故将REMOTE_ADDR设置为优先判断
 */ 
//        if (isset($_SERVER['REMOTE_ADDR']))
//        {
//            $realip = $_SERVER['REMOTE_ADDR'];
//        }
//        elseif ()
//        {
//            if (isset($_SERVER['HTTP_CLIENT_IP']))
//            {
//                $realip = $_SERVER['HTTP_CLIENT_IP'];
//            }
//            else
//            {
//                $realip = '0.0.0.0';
//            }
//        }
//    }
//    else
//    {
//        if (getenv('HTTP_X_FORWARDED_FOR'))
//        {
//            $realip = getenv('HTTP_X_FORWARDED_FOR');
//        }
//        elseif (getenv('HTTP_CLIENT_IP'))
//        {
//            $realip = getenv('HTTP_CLIENT_IP');
//        }
//        else
//        {
//            $realip = getenv('REMOTE_ADDR');
//        }
//    }
//
//    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
//    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';

//    return $realip;	
}
function np_is_utf8($liehuo_net) {
    if (preg_match("/^([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){1}/", $liehuo_net) == true || preg_match("/([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){1}$/", $liehuo_net) == true || preg_match("/([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){2,}/", $liehuo_net) == true) {
            return true;
    } else {
            return false;
    }
}

//czy 2014-07-05 这么写的代码，一定要特别注明一下，比如为了兼容什么
//s67 2014-07-06 为了兼容nginx与iis没有getallheaders 这个方法
if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
           $headers = '';
       foreach ($_SERVER as $name => $value)
       {
           if (substr($name, 0, 5) == 'HTTP_')
           {
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
//               $headers[str_replace(' ', '-', str_replace('_', ' ', substr($name, 5)))] = $value;
           }
       }
       return $headers;
    }
} 
/**
 * 执行linux命令脚本
 * @param linux脚本命令
 * @return 0为运行成功
 * 			other为失败
 */
function np_exec_shell_command($command){
	$command=escapeshellcmd($command);
	exec($command,$output,$ret);
		
	return $ret;
}
/**
 * 获取终端的IP，如果终端通过了代理，则跳过代理的IP
 */
function np_get_client_ip_proxy_support(){

    if (isset($_SERVER))
    {
    	
    	if (isset($_SERVER['HTTP_CDN_SRC_IP']) && !empty($_SERVER['HTTP_CDN_SRC_IP'])){
    		$ip=$_SERVER['HTTP_CDN_SRC_IP'];
           	$ip = trim($ip);
           	if (np_check_ip_valid($ip)) return $ip;
    	}
    	
    	if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
    		$ips = $_SERVER['HTTP_X_FORWARDED_FOR'];
            $ips = urldecode($ips);
    		$arr = explode(',', $ips);
            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
            foreach ($arr as $ip)
            {
                $ip = trim($ip);

                if ($ip != 'unknown' && !empty($ip))
                {
                    if (np_check_ip_valid($ip)) return $ip;
                }
            }
    	}
    	
    	

        if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']))
        {
           $ip=$_SERVER['HTTP_CLIENT_IP'];
           $ip = trim($ip);
           if (np_check_ip_valid($ip)) return $ip;
        }
       
         if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']))
         {
           	$ip=$_SERVER['REMOTE_ADDR'];
           	$ip = trim($ip);
            if (np_check_ip_valid($ip)) return $ip;
         }
    }
    else
    {
        if (getenv('HTTP_X_FORWARDED_FOR'))
        {
            $ips = getenv('HTTP_X_FORWARDED_FOR');
            $ips = urldecode($ips);
            $arr = explode(',', $ips);

            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
            foreach ($arr as $ip)
            {
                $ip = trim($ip);

                if ($ip != 'unknown' && !empty($ip))
                {
                    if (np_check_ip_valid($ip)) return $ip;
                }
            }
        }
        if (getenv('HTTP_CLIENT_IP'))
        {
            $ip = getenv('HTTP_CLIENT_IP');
            $ip = trim($ip);
            if (np_check_ip_valid($ip)) return $ip;
        }
        if (getenv('REMOTE_ADDR'))
        {
            $ip = getenv('REMOTE_ADDR');
            $ip = trim($ip);
            if (np_check_ip_valid($ip)) return $ip;
        }
    }

    return '0.0.0.0';	
}

function np_check_ip_valid($ip){
	 $state = preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip);
	 if ($state===0 || $state===FALSE)
	 {
	 	return FALSE;
	 }
	 else
	 {
	 	return TRUE;
	 }
	 
}

/**
 * 获取代理的IP，如果终端未通过代理，则返回终端的IP
 * 
 */
function np_get_client_ip_remote_support(){
    if (isset($_SERVER))
    {
    	
    	if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']))
         {
           	$ip=$_SERVER['REMOTE_ADDR'];
           	$ip = trim($ip);
            if (np_check_ip_valid($ip)) return $ip;
         }    	

        if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']))
        {
           $ip=$_SERVER['HTTP_CLIENT_IP'];
           $ip = trim($ip);
           if (np_check_ip_valid($ip)) return $ip;
        }
       
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
    		$ips = $_SERVER['HTTP_X_FORWARDED_FOR'];
            $ips = urldecode($ips);
            $arr = explode(',', $ips);

            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
            foreach ($arr as $ip)
            {
                $ip = trim($ip);

                if ($ip != 'unknown' && !empty($ip))
                {
                    if (np_check_ip_valid($ip)) return $ip;
                }
            }
    	} 
    }
	 else
    {
    	if (getenv('REMOTE_ADDR'))
        {
            $ip = getenv('REMOTE_ADDR');
            $ip = trim($ip);
            if (np_check_ip_valid($ip)) return $ip;
        }
       
        if (getenv('HTTP_CLIENT_IP'))
        {
            $ip = getenv('HTTP_CLIENT_IP');
            $ip = trim($ip);
            if (np_check_ip_valid($ip)) return $ip;
        }
        
        if (getenv('HTTP_X_FORWARDED_FOR'))
        {
            $ips = getenv('HTTP_X_FORWARDED_FOR');
            $ips = urldecode($ips);
            $arr = explode(',', $ips);

            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
            foreach ($arr as $ip)
            {
                $ip = trim($ip);

                if ($ip != 'unknown' && !empty($ip))
                {
                    if (np_check_ip_valid($ip)) return $ip;
                }
            }
        }
    }
    return '0.0.0.0';	
}


?>