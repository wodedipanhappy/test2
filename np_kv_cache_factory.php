<?php
/*
 * Created on 2012-10-10
 * CZY
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
include_once dirname(__FILE__).DIRECTORY_SEPARATOR ."np_kv_cache.class.php" ;
include_once dirname(__FILE__).DIRECTORY_SEPARATOR ."np_kv_cache_memcache.class.php" ;
include_once dirname(__FILE__).DIRECTORY_SEPARATOR ."np_kv_cache_null.class.php" ;
/*
 *servers,根据不同应用,传入的是不同参数的数组,当前为 {"ip:port","ip:port"}
 *
 * */
function np_kv_cache_build_config( $cache_type, $map_type, $servers )
{
	$config = array();
	$config["map_type"] = $map_type;
	$config["cache_type"] = $cache_type;
	$config["servers"] = $servers;

	return $config;
}
function np_kv_cache_factory_create( $config )
{
	if( !array_key_exists( "cache_type", $config ) )
		return NULL;

	$cache_type = intval( $config["cache_type"] );

	if( $cache_type ===  NP_KV_CACHE_TYPE_MEMCACHE )
	{
		return new np_kv_cache_memcache_class( $config );
	}
	elseif( $cache_type ===  NP_KV_CACHE_TYPE_NULL )
	{
		return new np_kv_cache_null_class( $config );
	}

	return NULL;
}
?>
