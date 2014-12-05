<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR ."np_kv_cache.class.php" ;
class np_kv_cache_memcache_class extends np_kv_cache_class
{
	public function __construct( $config )
	{
		$this->m_config = $config;

		if( array_key_exists( "map_type", $config ) )
			$this->m_map = intval( $config["map_type"] );
	}
	public function __destruct()
	{
		$this->_close();
	}
	public function open( )
	{
		$this->_close();

		if( !array_key_exists( "servers", $this->m_config ) )
			return _return_false_and_error("config not found servers");

		$this->m_mc = new Memcache;
		if( $this->m_mc == NULL )
			return _return_false_and_error("err new Memcache");

		//
		$servers = $this->m_config["servers"];
		foreach( $servers as $server )
		{
			$arg = explode( ':', $server );
			if( count( $arg ) > 1 )
			{
				if( !$this->m_mc->addServer( $arg[0], $arg[1] ) )
				{
					$this->_set_last_error_desc( "err add server".$server );
				}
			}
			else
			{
				//默认为11211端口
				if( !$this->m_mc->addServer( $arg[0], 11211 ) )
				{
					$this->_set_last_error_desc( "err add server".$server );
				}
			}
		}
		return TRUE;

	}
	public function close()
	{
		$this->_close();
	}
	public function set($key, $value, $expire = 3600, $ex_flag = NULL )
	{
		if( $this->m_mc == NULL )
			return $this->_return_false_and_error( "err mc is null" );

		return $this->m_mc->set( $key, $value, 0,$expire );
	}
	public function get( $key )
	{
		if( $this->m_mc == NULL )
			return $this->_return_false_and_error( "err mc is null" );

		return $this->m_mc->get( $key );
	}
	public function delete( $key )
	{
		if( $this->m_mc == NULL )
			return $this->_return_false_and_error( "err mc is null" );

		return $this->m_mc->delete( $key );
	}
	public function add($key, $value, $expire = 0, $ex_flag = NULL )
	{
		if( $this->m_mc == NULL )
			return $this->_return_false_and_error( "err mc is null" );

		return $this->m_mc->add( $key, $value, 0,$expire );

	}
	public function last_error_desc()
	{
		return $this->m_last_error_desc;
	}
	public function get_type()
	{
		return NP_KV_CACHE_TYPE_MEMCACHE;
	}
	public function get_cache_keys()
	{
	    $list = array();
	    $allSlabs = $this->m_mc->getExtendedStats('slabs');
	    foreach($allSlabs as $server => $slabs) {
	        foreach($slabs AS $slabId => $slabMeta) {
	            $cdump = $this->m_mc->getExtendedStats('cachedump',(int)$slabId);
                foreach($cdump AS $keys => $arrVal) {
                    if (!is_array($arrVal)) continue;
                    foreach($arrVal AS $k => $v) {
                        $list[] =$k;
                    }
                }
	         }
	    }
	    return $list;
	}
	private function _close()
	{
		if( $this->m_mc != NULL )
		{
			$this->m_mc->close();
			$this->m_mc = NULL;
		}
		return TRUE;
	}
	private function _set_last_error_desc( $desc )
	{
		$this->m_last_error_desc = $desc;
	}
	private function _return_false_and_error( $desc )
	{
		$this->m_last_error_desc = $desc;
		return FALSE;
	}

	private $m_mc  = NULL;
	private $m_last_error_desc = "";
}
?>