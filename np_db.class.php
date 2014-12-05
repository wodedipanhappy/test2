<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR ."np_config.php" ;
define( "NP_DB_TYPE_NULL", 0 );
define( "NP_DB_TYPE_MYSQL"	, 	1 );
define( "NP_DB_TYPE_ORACLE"	, 	2 );
define( "NP_DB_TYPE_MSSQL", 	3 );

//输出标记

define( "NP_DB_LOG_F_SQL",  		0x00000001 );		//输出执行的SQL语句
define( "NP_DB_LOG_F_DUMP_RESULT", 	0x00000002 );		//输出执行结果
define( "NP_DB_LOG_F_DUMP_RES", 	0x00000004 );		//输出执行RES
define( "NP_DB_LOG_F_DUMP_CONN", 	0x00000008 );		//输出执行的连接
define( "NP_DB_LOG_F_DUMP_SQL_EXPLAIN", 	0x00000010 );	

define( "NP_DB_LOG_NULL",  	0 );
define( "NP_DB_LOG_ERROR",  		NP_DB_LOG_F_SQL|NP_DB_LOG_F_DUMP_RESULT );
define( "NP_DB_LOG_ALL",  			NP_DB_LOG_F_SQL|NP_DB_LOG_F_DUMP_RESULT|NP_DB_LOG_F_DUMP_CONN|NP_DB_LOG_F_DUMP_RES );
define( "NP_DB_LOG_SQL_DEBUG_EXPLAIN", NP_DB_LOG_F_DUMP_SQL_EXPLAIN);


/**
 * 新增$db_debug记录运行SQL状态
 * 格式为:array(
 *    'SQL'=>sql命令
 *    'debug'=>sql运行情况,
 * 	  'time'=>debug模式下为运行时间,
 *    'explain_debug'=> debug模式下为索引查询结果
 * )
 */
abstract class np_db_class{
	protected $m_db_type = NP_DB_TYPE_MYSQL;
	protected $m_db_log = NP_DB_LOG_ERROR;
	protected $m_db_config = NULL;	
	protected $m_db_debug=NULL;

	/*
	成功,返回TRUE
	失败,回返FALSE
	*/
	abstract public function open();
	/*
	成功,返回TRUE
	失败,回返FALSE
	*/
	abstract public function close();
	/*
	成功,返回TRUE
	失败,回返FALSE
	*/
	abstract public function execute( $sql );
	/*
	成功,返回TRUE
	失败,回返FALSE
	*/
	abstract public function query( $sql );
	/*
	读取完结果后,是不是直接释放原有数据,这样能节省内存,提高执行效率
	返回结果数组
	*/
	abstract public function get_query_result( $is_free_after_get = TRUE );
	/*
	读取最近一次错误ID
	*/
	abstract public function last_error_no();
	/*
	读取最近一次错误描述
	*/
	abstract public function last_error_desc();
	/*
	读取当前DB所有SQL信息
	*/
	abstract public function get_db_debug();
	
	/*
	读取当前DB所有CONFIG信息
	*/
	abstract public function get_db_config();
};

?>