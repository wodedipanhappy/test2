<?php
/**
 *redis封装类 
 * codereview 2014-7-5 陈朱尧
 * 相关的小的逻辑问题较多，很多代码没有好好考虑
 * modify xsong 2014-7-7
 * 
 * 
 *
 *用法 ：
  $config = array(
  'host'=>'127.0.0.1',
  'port'=>6379
  );
  $redis = new np_redis_class($config);
  $redis->open();
 */
class np_redis_class {
    private $redis = null;
    /**
     * @param $config array('host'=>'127.0.0.1', 'port'=>6379)
     */
    public function __construct($config = array())
    {
    	$this->redis = new Redis();
    	$this->_config = $config;
    }
    public function close()
    {
    	if($this->redis ){
    		$this->redis->close();
			//czy 2014-7-5 即然close了，就要=NULL;如果能复用，则不需要清空
			$this->redis=null;
    	}
    }
	
	/*
	 * 返回值问题，一般成功返回TRUE，失败返回FALSE
	*/
    public function open()
    {
	    if(empty($this->_config['host']) ||  empty($this->_config['port']) )
	    {
	    	return false;
	    }
	    if($this->redis==null)
	    {
	    	return false;
	    }
		//czy 2014-7-5 要返回检查条件吧
    	$result = $this->redis->connect($this->_config['host'],$this->_config['port']);
    	if(!$result)
    	{
    		return false;
    	}
    	if(isset($this->_config['passwd']))
    	{
			//czy 2014-7-5 要返回检查条件吧
    		$result =$this->redis->auth($this->_config['passwd']);
	    	if(!$result)
	    	{
	    		return false;
	    	}			
    	}
    	return $this->redis;
    }
    public function set($k, $v)
    {
		//czy 2014-7-5 返回值？如果redis不可重用，可能被close，还要检查redis变量是不是空
		if($this->redis==null)
		{
			return false;
		}
    	return $this->redis->set($k, $v);
    }
    public function get($k)
    {
		//czy 2014-7-5 返回值？如果redis不可重用，可能被close，还要检查redis变量是不是空
		if($this->redis==null)
		{
			return false;
		}		
    	return $this->redis->get($k);
    }
    public function delete($k)
    {
		//czy 2014-7-5 返回值？如果redis不可重用，可能被close，还要检查redis变量是不是空
		if($this->redis==null)
		{
			return false;
		}		
    	return $this->redis->delete($k);
    }
    /**
     * 如果键不存在，才设置
     */
    public function setnx($k, $v)
    {
		//czy 2014-7-5 返回值？如果redis不可重用，可能被close，还要检查redis变量是不是空
		if($this->redis==null)
		{
			return false;
		}		
    	return $this->redis->setnx($k, $v);
    }
    public function exists($k)
    {
		//czy 2014-7-5 返回值？如果redis不可重用，可能被close，还要检查redis变量是不是空
    	return $this->redis->exists($k);
    }
    /**
     * 数字递增
     */
    public function incr($k)
    {
		if($this->redis==null)
		{
			return false;
		}    	
    	return $this->redis->incr($k);
    }
    /**
     * 数字递减
	 * 要把函数说得很明白，比如$k不存在，
     */    
    public function decr($k)
    {
		//czy 2014-7-5 参数检查
    	return $this->redis->decr($k);
    }    
    /**
     * 取得一个或多个键的值
     */
    public function get_multiple( $keys = array())
    {
		if($this->redis==null)
		{
			return false;
		}    	
    	return $this->redis->getMultiple($keys);
    }
    /**
     * 将一个或多个值 value 插入到列表 key 的表头
	 * //czy 2014-7-5 返回值 说明写错了，我查文档是列表头？且没有说是字符串类型啊
     */
	public function lpush($k , $v)
	{
		if($this->redis==null)
		{
			return false;
		}		
	    return $this->redis->lpush($k , $v);
	}
	/**
	 * 将一个或多个值 value 插入到列表 key 的表尾(最右边)。
	 */
	public function rpush($k , $v)
	{
		if($this->redis==null)
		{
			return false;
		}		
	    return $this->redis->rpush($k , $v);
	}	
	/**
	 * 返回并移除列表的第一个元素
	 */
    public function lpop($k)
    {
		if($this->redis==null)
		{
			return false;
		}    	
    	return $this->redis->lpop($k);
    }
    /**
     * 返回并移除列表的最后一个元素
     */
    public function rpop($k)
    {
		if($this->redis==null)
		{
			return false;
		}    	
    	return $this->redis->rpop($k);
    }   
    /**
     * 列表的阻塞式(blocking)弹出原语。
     * 当给定列表内没有任何元素可供弹出的时候，连接将被 BRPOP 命令阻塞，直到等待超时或发现可弹出元素为止。
     * 用法
	 * //czy 2014-7-5 说明呢，龙其是这种阻塞式原语
     * $redis->brPop(array('key1', 'key2'), 10); 
     */
    public function brpop($keys = array(),$time_out=10)
    {
		if($this->redis==null)
		{
			return false;
		}    	
    	return $this->redis->brpop($keys,$time_out);
    }
    /**
     * 返回指定键存储在列表中指定的元素。
     *  0第一个元素，1第二个… -1最后一个元素，
     * -2的倒数第二…错误的索引或键不指向列表则返回FALSE
     * @param $k index
     * $redis->lget("test",3)
     */
    public function lget($k,$index) 
    {
		if($this->redis==null)
		{
			return false;
		}    	
    	return $this->redis->lget($k, $index);
    }
    public function lsize($k)
    {
    	return $this->redis->lsize($k);
    }
    /**
     * 为列表指定的索引赋新的值,若不存在该索引返回false.
     */
    public function lset($k , $index,$v)
    {
		if($this->redis==null)
		{
			return false;
		}    	
    	return $this->redis->lset($k , $index, $v);
    }    
    /**
     * 返回在该区域中的指定键列表中开始到结束存储的指定元素，
	 * //czy 2014-7-5 返回值 如果元数不足，错位等，如何返回
     * lGetRange(key, start, end)。0第一个元素，1第二个元素… -1最后一个元素，-2的倒数第二…
     */
    public function  lgetrange($k , $begin_index, $end_index)
    {
		if($this->redis==null)
		{
			return false;
		}    	
    	return $this->redis->lgetrange($k , $begin_index, $end_index);
    }
    /**
     * redis有非常多的操作方法，只封装了一部分
     * 拿着这个对象就可以直接调用redis自身方法 
     */
    public function get_redis()
    {
    	return $this->redis;
    }

    /**
     * @param $hash_key
     * @param $key
     * @param string $value
     * @return bool|int
     * @author huayi.cai
     *增加 key 指定的哈希集中指定字段的数值。
     * 如果 key 不存在，会创建一个新的哈希集并与 key 关联。
     * 如果字段不存在，则字段的值在该操作执行前被设置为 0
     */
    public function hIncrBy($hash_key, $key, $value = "")
    {
        //hash名称和里面的key不能为空
        if(empty($hash_key) || empty($key))
        {
            return false;
        }
        return $this->redis->hIncrBy($hash_key, $key, $value);
    }

    /**
     * @param $hash_key
     * @param $key
     * @param $value
     * @return bool|int
     * 设置 key 指定的哈希集中指定字段的值。
     * 该命令将重写所有在哈希集中存在的字段。
     * 如果 key 指定的哈希集不存在，会创建一个新的哈希集并与 key 关联
     * @author huayi.cai
     */
    public function hSet($hash_key, $key, $value)
    {
        //hash名称和里面的key不能为空
        if(empty($hash_key) || empty($key))
        {
            return false;
        }
        return $this->redis->hSet($hash_key, $key, $value);
    }

    /**
     * @param $hash_key
     * @param $key
     * @return bool
     * 返回字段是否是 key 指定的哈希集中存在的字段。
     * @author huayi.cai
     */
    public function hExists($hash_key, $key)
    {
        //hash名称和里面的key不能为空
        if(empty($hash_key) || empty($key))
        {
            return false;
        }
        return $this->redis->hExists($hash_key, $key);
    }

    /**
     * @param $hash_key
     * @return array|bool
     * @author huayi.cai
     * 返回 key 指定的哈希集中所有字段的名字。
     */
    public function hKeys($hash_key)
    {
        if(empty($hash_key))
        {
            return false;
        }
        return $this->redis->hKeys($hash_key);
    }

    /**
     * @param $hash_key
     * @param $key
     * @return bool|string
     * @author huayi.cai
     * 返回字段是否是 key 指定的哈希集中存在的字段。
     */
    public function hGet($hash_key, $key)
    {
        //hash名称和里面的key不能为空
        if(empty($hash_key) || empty($key))
        {
            return false;
        }
        return $this->redis->hGet($hash_key, $key);
    }

    /**
     * @param $hash_key
     * @param $key
     * @return bool|int
     * @author huayi.cai
     * 从 key 指定的哈希集中移除指定的域。
     * 在哈希集中不存在的域将被忽略。
     * 如果 key 指定的哈希集不存在，它将被认为是一个空的哈希集，该命令将返回0。
     */
    public function hDel($hash_key, $key)
    {
        //hash名称和里面的key不能为空
        if(empty($hash_key) || empty($key))
        {
            return false;
        }
        return $this->redis->hDel($hash_key, $key);
    }

    /**
     * @param $list_key
     * @author huayi.cai
     * 返回存储在 key 里的list的长度。
     * 如果 key 不存在，那么就被看作是空list，并且返回长度为 0。
     * 当存储在 key 里的值不是一个list的话，会返回error。
     */
    public function lLen($list_key = "")
    {
        return $this->redis->lLen($list_key);
    }

    /**
     * @param $list_key
     * @param $start
     * @param $end
     * @return array|bool
     * @author huayi.cai
     * 返回存储在 key 的列表里指定范围内的元素。
     * start 和 end 偏移量都是基于0的下标，即list的第一个元素下标是0（list的表头），第二个元素下标是1，以此类推。
     * 偏移量也可以是负数，表示偏移量是从list尾部开始计数。 例如， -1 表示列表的最后一个元素，-2 是倒数第二个，以此类推。
     */
    public function lRange($list_key, $start, $end)
    {
        if(empty($list_key) || empty($start) || empty($end))
        {
            return false;
        }
        return $this->redis->lRange($list_key, $start, $end);
    }
}
?>