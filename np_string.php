<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR ."np_config.php" ;
include_once dirname(__FILE__).DIRECTORY_SEPARATOR ."np_time.php" ;


function np_strlen_safe( $s )
{
	if( is_string( $s ) )
	{
		return strlen( $s );
	}
	return 0;
}

/*

*/
function np_c1_encode( $src )
{
	if( !isset( $src) )
		return "";
 	$src_len = strlen($src);

	$dst= "";
	for   ($i=0;$i <$src_len;$i++)
	{
		$dst.=dechex(ord($src[$i]));
	}

return   $dst;

}
function np_c1_decode( $src )
{
	if( !isset( $src) )
			return "";
	$dst= "";
	$src_len = strlen($src);
	for   ($i=0;$i <$src_len-1;$i+=2)
	{
		$dst.=chr( hexdec($src[$i].$src[$i+1]) );
	}
	return   $dst;
}
function np_guid_rand( $something = "rand" )
{
	$result = dechex(  time() );
	$result = $result.dechex( np_millisecond() );

	$a = "";
	if( isset( $_ENV ["COMPUTERNAME"] ) )
		$a .= $_ENV ["COMPUTERNAME"];
	if( isset( $_SERVER ["SERVER_ADDR"] ) )
		$a .= $_SERVER ["SERVER_ADDR"];
	if( isset( $_SERVER ["REMOTE_ADDR"] ) )
		$a .= $_SERVER ["REMOTE_ADDR"];

	//echo $a;

	$a = $a.rand(0,10000);
	$a = $a.rand(0,10000);
	$a = $a.rand(0,10000);
    $a = $a.microtime ();


	$result = $result.md5( $a.$something );
	return substr( $result, 0, 32 );
}
function np_guid_check_valid( $guid )
{
	if( !isset( $guid ) )
		return FALSE;
	if( !is_string( $guid ) )
		return FALSE;

	$len = strlen( $guid );
	if( $len != 32 )
		return FALSE;

	for( $i = 0; $i < $len; ++$i )
	{
		if( ( $guid[$i] >= '0' && $guid[$i] <= '9' )
		|| ( $guid[$i] >= 'a' && $guid[$i] <= 'z' )
		|| ( $guid[$i] >= 'A' && $guid[$i] <= 'Z' ) )
		{
		}
		else
		{
			return FALSE;
		}
	}

	return TRUE;
}
/*
 * 从一个ＵＲＬ请求串，分解出请求中的ＧＥＴ参数
 * */
function np_build_query_from_url_string( $url, $decode = TRUE )
{
	$data = strstr( $url, "?" );
	if( $data === FALSE )
	{
		$data = $url;
	}
	else
	{
		//去掉开始的?号
		if( strlen( $data ) > 0 )
			$data = substr( $data, 1 );
	}

	$array = explode("&", $data);
	$queries = array();
    foreach( $array as $elem)
    {
        if($elem)
        {
        	list($k,$v) = explode("=", $elem);

        	if( $decode )
            	$queries[$k] = urldecode($v);
            else
            	$queries[$k] = $v;
        }
    }
	return $queries;


}
/**
 * 将字串中的对应分割字符，转换为相应字符
 */

function np_build_split_string($str,$split_str,$space=true){
	$split_arr=array(',','，','|','丨','、');
	$str=trim($str,' ');
	$str=preg_replace('/\s{2,}/i',$split_str,$str);
	foreach ($split_arr as $split_item){
		$str=str_replace($split_item,$split_str,$str);
	}
	
	if ($space){
		$str=str_replace(' ',$split_str,$str);
	}
	
	return $str;
}

/**
 * @param $data 字符串
 * @return string 生成url安全的base64字符串
 * @author huayi.cai
 */
function np_base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * @param $data url安全的base64字符串
 * @return string 转会base64原样的字符串
 * @author huayi.cai
 */
function np_base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}
?>