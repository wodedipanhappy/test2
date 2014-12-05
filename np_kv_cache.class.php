<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR ."np_config.php" ;
/*
 *客户端实现的连接模型
 *当前为memcache这个API可用
 * */
define( "NP_KV_CACHE_TYPE_NULL"			, 	0 );
define( "NP_KV_CACHE_TYPE_MEMCACHE"		, 	1 );
define( "NP_KV_CACHE_TYPE_MEMCACHED"	, 	2 );

/*
 * 数据映射模型,当前为HASH或是ALL二种模式
 * 当前只支持ALL模式
 *
 * */
define ("NP_KV_CACHE_MAP_ALL", 0 );
define ("NP_KV_CACHE_MAP_HASH", 1 );

abstract class np_kv_cache_class
{

	/*
	成功,返回TRUE
	失败,回返FALSE
	*/
	abstract public function open( );
	/*
	成功,返回TRUE
	失败,回返FALSE
	*/
	abstract public function close();
	/*
	 *成功,返回TRUE,	失败,回返FALSE
	 *如果KEY已经存在,则替换;如果不存在,则添加
	 *expire 缓冲失效时长,单位为秒,为0时代表不限时长
	*/
	abstract public function set($key, $value, $expire = 180, $ex_flag = NULL );
	/*
	 *成功,返回读取到的元素值;
	 *失败,返回FALSE,元素不存在,也返回FALSE
	 *
	 */
	abstract public function get( $key );
	/*
	 *成功,返回TRUE;失败,返回FALSE
	 */
	abstract public function delete( $key );
	/*
	 *成功,返回TRUE,	失败,回返FALSE
	 *如果KEY已经存在,则不操作;同时返回FALSE
	 *如果不存在,则添加
	 *
	*/
	abstract public function add($key, $value, $expire = 180, $ex_flag = NULL );
	/*
	 *返回最后一次执行后的错误码
	 */
	abstract public function last_error_desc();
	/*
	 * 返回实现类型
	 * NP_KV_CACHE_TYPE_*,每种实现返回自身实现的类型即可
	 */
	abstract public function get_type();


	protected $m_map = NP_KV_CACHE_MAP_ALL;
	protected $m_config = NULL;

}
?>