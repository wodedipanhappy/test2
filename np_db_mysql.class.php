<?php
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "np_db_class.php";
class np_db_mysql_class extends np_db_class {
	public function __construct($config) {
		//parent::__construct();

		//echo "__construct";
		$this->m_db_type = NP_DB_TYPE_MYSQL;
		$this->m_db_config = $config;

		$this->m_db_debug = array ();

		if (array_key_exists("db_log", $config))
			$this->m_db_log = intval($config["db_log"]);

		//var_dump( $this->m_db_config );
	}
	public function __destruct() {
		//echo "__destruct";
		$this->_close();

		//parent::__destruct();
	}
	private function _free_query_resource() {
		if (is_resource($this->m_query_res)) {
			mysql_free_result($this->m_query_res);
			$this->m_query_res = FALSE;
		}
	}
	private function _close() {
		$this->_free_query_resource();
		if ($this->m_conn != FALSE) {
			mysql_close($this->m_conn);
			$this->m_conn = FALSE;
		}

	}
	public function open() {
		//锟斤拷锟斤拷写蚩木锟斤拷,锟酵凤拷锟斤拷源
		$this->_close();

		//echo ("open" );

		$this->m_conn = @mysql_connect($this->m_db_config["host"], $this->m_db_config["user"], $this->m_db_config["passwd"], TRUE);
		if (!$this->m_conn) {
			if ($this->m_db_log & NP_DB_LOG_F_DUMP_CONN) {
				var_dump(mysql_error());
			}
			return FALSE;
		}

		if (!mysql_select_db($this->m_db_config["db_name"], $this->m_conn)) {
			if ($this->m_db_log & NP_DB_LOG_F_DUMP_CONN) {
				var_dump(mysql_error($this->m_conn));
			}
			return FALSE;
		}

		//锟斤拷锟斤拷为UTF8

		$character_set = "utf8";
		if (array_key_exists("character_set", $this->m_db_config))
			$character_set = $this->m_db_config["character_set"];

		if (strlen($character_set) > 0) {
			mysql_query("set names " . $character_set, $this->m_conn);
			mysql_query("set character_set_client=" . $character_set, $this->m_conn);
			mysql_query("set character_set_results=" . $character_set, $this->m_conn);
		}

		return TRUE;
	}
	public function close() {
		$this->_close();
		return TRUE;
	}

	private function _check_sql($sql) {
		$sql=trim($sql);
		if (isset ($this->m_db_config['mode']) && $this->m_db_config['mode'] == 'READ') {
			if (stripos($sql, 'insert') === 0 || stripos($sql, 'update') === 0 || stripos($sql, 'delete') === 0) {
				$this->error = "read mode not allow insert,update,delelte";
				return FALSE;
			}
		}

		return TRUE;
	}

	public function execute($sql) {
		if (!$this->_check_sql($sql))
			return FALSE;

		$this->_free_query_resource();

		if ($this->m_db_log & NP_DB_LOG_F_DUMP_CONN) {
			var_dump($this->m_conn);
		}
		if ($this->m_db_log & NP_DB_LOG_F_SQL) {
			var_dump($sql);
		}

		if ($this->m_conn == FALSE)
			return FALSE;
		
		$run_time=0;
//		如果是调试模式,则开启时间记录 BYS67 2014-06-24
		if ($this->m_db_log & NP_DB_LOG_SQL_DEBUG_EXPLAIN)
		{
			np_runtime(FALSE);
		}
		
		
		$this->m_query_res = mysql_query($sql, $this->m_conn);
		
		if ($this->m_db_log & NP_DB_LOG_SQL_DEBUG_EXPLAIN)
		{
			$run_time=np_runtime(TRUE);
		}
		
		
		if ($this->m_db_log & NP_DB_LOG_F_DUMP_RES) {
			var_dump($this->m_query_res);
		}

		if ($this->m_query_res === FALSE) {
			$this->m_db_debug[] = array (
				'SQL' => $sql,
				'debug' => $this->last_error_desc(),
				'time'=>$run_time
			);
			return FALSE;
		} else {
			$this->m_db_debug[] = array (
				'SQL' => $sql,
				'debug' => 'OK',
				'time'=>$run_time
			);
			return TRUE;
		}

		//		return TRUE;
	}
	public function query($sql) {
		if (!$this->_check_sql($sql))
			return FALSE;

		$this->_free_query_resource();

		if ($this->m_db_log & NP_DB_LOG_F_DUMP_CONN) {
			var_dump($this->m_conn);
		}
		if ($this->m_db_log & NP_DB_LOG_F_SQL) {
			var_dump($sql);
		}

		if ($this->m_conn === FALSE)
			return FALSE;
		
		$run_time=0;	
		if ($this->m_db_log & NP_DB_LOG_SQL_DEBUG_EXPLAIN)
		{
			np_runtime(FALSE);
		}

		$this->m_query_res = mysql_query($sql, $this->m_conn);
		
		if ($this->m_db_log & NP_DB_LOG_SQL_DEBUG_EXPLAIN)
		{
			$run_time=np_runtime(TRUE);

		}

		if ($this->m_db_log & NP_DB_LOG_F_DUMP_RES) {
			var_dump($this->m_query_res);
		}

		if ($this->m_query_res === FALSE) {
			
			
			$this->m_db_debug[] = array (
				'SQL' => $sql,
				'debug' => $this->last_error_desc(),
				'time'=>$run_time
			);
			return FALSE;
		} else {
			$explain_data='';
			if ($this->m_db_log & NP_DB_LOG_SQL_DEBUG_EXPLAIN)
			{
//			记录索引使用情况
				$explain_data=$this->__get_debug_explain_sql($sql);
			}
			
			$this->m_db_debug[] = array (
				'SQL' => $sql,
				'debug' => 'OK',
				'time'=>$run_time,
				'explain_debug'=>$explain_data
			);
			return TRUE;
		}
		//			return FALSE;

		//		return TRUE;
	}
	public function get_query_result($is_free_after_get = TRUE) {
		if ($this->m_query_res === FALSE)
			return FALSE;

		//通锟斤拷锟斤拷锟斤拷模式
		$result = array ();

		while ($row = mysql_fetch_assoc($this->m_query_res)) {
			$result[] = $row;
		}

		if ($is_free_after_get) {
			$this->_free_query_resource();
		}
		if ($this->m_db_log & NP_DB_LOG_F_DUMP_RESULT) {
			var_dump($result);
		}
		return $result;
	}
	public function last_error_no() {
		if ($this->m_conn == FALSE) {
			return mysql_errno();
		} else {
			return mysql_errno($this->m_conn);
		}
	}
	public function affected_rows() {
		return mysql_affected_rows($this->m_conn);
	}
	public function last_error_desc() {
		if (isset($this->error))
			return $this->error;
		if ($this->m_conn == FALSE) {
			return mysql_error();
		} else {
			return mysql_error($this->m_conn);
		}
	}

	public function get_db_debug() {
		return $this->m_db_debug;
	}
	
	
	private function __get_debug_explain_sql($sql){
		if (stripos(trim($sql),'select')===0){
			$sql = 'explain '.$sql;
			$query_res = mysql_query($sql, $this->m_conn);
			if ($query_res === FALSE) return FALSE;
			
			$result = array ();
	
			while ($row = mysql_fetch_assoc($query_res)) {
				$result[] = $row;
			}
			
			if (is_resource($query_res)) {
				mysql_free_result($query_res);
				unset($query_res);
			}
			return $result;
		}
		return FALSE;
	}
	
	public function get_db_config(){
		return $this->m_db_config;
	}

	private $m_conn = FALSE;
	private $m_query_res = FALSE;
};

//echo "2";
?>