<?php
/**
 * @author yxt
 */
 include_once dirname(__FILE__).DIRECTORY_SEPARATOR ."np_db_class.php" ;
class np_db_pdo_class extends np_db_class{
	//PDO连接对象
	private	$m_conn = FALSE;
	//PDOstatement对象
	private $stmt = FALSE;
	//返回结果集
	private $result = FALSE;
	//错误码
	private $eCode=null;
	//错误信息
	private $eInfo=null;
	public function __construct( $config )
	{
		$this->m_db_type = NP_DB_TYPE_PDO;
		$this->m_db_config = $config;

		if( array_key_exists( "db_log", $config ) )
			$this->m_db_log = intval( $config["db_log"] );
	}
	public function __destruct()
	{
		$this->close();
	}
	/**
	 * 打开数据库连接
	 * 成功,返回TRUE
	 * 失败,回返FALSE
	*/
	public function open(){
		if (empty($this->m_conn)) {
			try {
				$dsn = "mysql:host=" . $this->m_db_config["host"] . ";dbname=" .  $this->m_db_config["db_name"];
				$pdo = new PDO($dsn, $this->m_db_config["user"] , $this->m_db_config["passwd"]);
				$pdo->exec('set names utf8');
				$pdo->setAttribute(PDO :: ATTR_ERRMODE, PDO :: ERRMODE_EXCEPTION);
				$this->m_conn = $pdo;
				return $pdo;
			} catch (PDOException $e) {
				$this->eCode=$e->getCode();
				$this->eInfo=$e->getMessage();
			}
		} else {
			return $this->m_conn;
		}
	}
	/*
	成功,返回TRUE
	失败,回返FALSE
	*/
	public function close(){
		$this->m_conn=null;
	}
	/**
	 * @param string $sql
	 * @return int $num 返回受影响的行数
	*/
	public function execute( $sql ){
		try{
			$num=$this->m_conn->exec($sql);
			return $num;
		} catch (PDOException $e) {
			$this->eCode=$e->getCode();
			$this->eInfo=$e->getMessage();
			}
	}
	/**
	 * @param string $sql
	 * @param array $data
	 * @return bool $this->result
	*/
	public function query( $sql,$data=array()){
		if( $this->m_db_log & NP_DB_LOG_F_DUMP_CONN ){
			var_dump( $this->m_conn );
		}
		if( $this->m_db_log & NP_DB_LOG_F_SQL ){
			var_dump( $sql );
		}
		if( $this->m_conn === FALSE )
			return FALSE;
		try{
			$pdo = $this->m_conn;
			$this->stmt = $pdo->prepare($sql);
			$this->result=$this->stmt->execute($data);
			return $this->result;
		} catch (PDOException $e){
			$this->eCode=$e->getCode();
			$this->eInfo=$e->getMessage();
		}
	}
	/**
	 * @param bool $is_free_after_get，判断读取完之后是否释放结果集，pdo为自动释放
	 * @param array $result
	*/
	public function get_query_result( $is_free_after_get = TRUE ){
		if( $this->result === FALSE )
			return FALSE;
		$result = array ();
		$result =$this->stmt->fetchAll(PDO::FETCH_ASSOC);
		if( $this->m_db_log & NP_DB_LOG_F_DUMP_RESULT ){
			var_dump( $result );
		}
		return $result;
	}
	/*
	读取最近一次错误ID
	*/
	public function last_error_no($errtype="pdo"){
		if(!empty($this->eCode))
			return $this->eCode;
		if($errtype=="pdo"){
			return $this->m_conn->errorCode();
		}else if($errtype=="stmt"){
			return $this->stmt->errorCode();
		}
	}
	/*
	读取最近一次错误描述
	*/
	public function last_error_desc($errtype="pdo"){
		if(!empty($this->eInfo))
			return $this->eInfo;
		if($errtype==="pdo"){
			return $this->m_conn->errorInfo();
		}else if($errtype==="stmt"){
			return $this->stmt->errorInfo();
		}
	}
	/**
	 * 获取最后插入sql的主键id
	 */
	 public function get_last_insert_id () {
		return $this->m_conn->lastInsertId();
	}
}
?>