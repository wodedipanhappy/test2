<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR ."np_db_mysql_class.php" ;

function np_db_build_config( $host, $user, $passwd, $db_name,$db_type = NP_DB_TYPE_MYSQL, $db_log = NP_DB_LOG_ERROR,$db_access_weight=1,$db_process_max_limit=500 )
{	
	$config = array();
	$config["host"] = $host;
	$config["user"] = $user;
	$config["passwd"] = $passwd;
	$config["db_name"] = $db_name;	
	
	$config["db_type"] = $db_type;	
	$config["db_log"] = $db_log;	
	
	$config["db_access_weight"]=$db_access_weight;
	$config["db_process_max_limit"]=$db_process_max_limit;
	
	return $config;
}
function np_db_factory_create( $config )
{
	if( !array_key_exists( "db_type", $config ) )
		return NULL;
		
	$db_type = intval( $config["db_type"] );
	//echo "db_type:".$db_type;
	
	if( $db_type ===  NP_DB_TYPE_MYSQL )
	{		
		//echo "np_db_mysql_class create";
		return new np_db_mysql_class( $config );
	}
	
	return NULL;
}

?>