<?php
/*
 *不使用cache,这是一个cahce的空操作实现类.
 *
 */
include_once dirname(__FILE__).DIRECTORY_SEPARATOR ."np_kv_cache.class.php" ;
class np_kv_cache_null_class extends np_kv_cache_class
{
	public function __construct( $config )
	{
		$this->m_config = $config;

	}
	public function __destruct()
	{

	}
	public function open( )
	{
		return FALSE;
	}
	public function close()
	{
		return FALSE;
	}
	public function set($key, $value, $expire = 0, $ex_flag = NULL )
	{
		return FALSE;
	}
	public function get( $key )
	{
		return FALSE;
	}
	public function delete( $key )
	{
		return FALSE;
	}
	public function add($key, $value, $expire = 0, $ex_flag = NULL )
	{
		return FALSE;

	}
	public function last_error_desc()
	{
		return "null";
	}
	public function get_type()
	{
		return NP_KV_CACHE_TYPE_NULL;
	}

}
?>