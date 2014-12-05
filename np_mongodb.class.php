<?php
/**
 * codereview 陈朱尧 20140704
 * mongodb封装类
 * 用法：
 *  $config = array(
 *     'host'   => '127.0.0.1',
 *     'port'   => '27017',
 *     'db_name'=> 'nn_stat',
 *     'user'   => 'root',
 *     'passwd' => 'root',
 *
 *  );
 *  $mongodb = new np_mongodb_class($config);
 *  $mongodb->open();
 * ; *
 */
class np_mongodb_class
{
    private $mongo = null;
    private $db = null;
    private $collection = null;

    /**
     * @param $config array(
     * 'host'   => '127.0.0.1',
     * 'port'   => '27017',
     * 'db_name'=> 'nn_stat',
     * 'user'   => 'root',
     * 'passwd' => 'root',
     * 'opption'=> null
     * )
     */
    public function __construct($config = array())
    {
        $this->_config = $config;
    }

    public function close()
    {
        if ($this->mongo)
        {
            $this->mongo->close();
        }
    }

    public function open()
    {
        $config = $this->_config;
        if ($config['host'] == '' || $config['port'] == '' || $config['db_name'] == '') 
        {
        	return false;
        }		
		//czy 20140705 if语句需要明确的换行出来。 不建议给默认值127的机器，没有就是open 出错。返回null
/*        if ($config['host'] == '') 
			$config['host'] = '127.0.0.1';
        if ($config['port'] == '') 
			$config['port'] = '27017';*/
        if (!isset($config['option'])) 
        {
        	$config['option'] = array('connect' => true);
        }
			
			
        $server = 'mongodb://' . $config['host'] . ':' . $config['port'];
        //$this->mongo = new MongoClient($server, $config['option']);
        //mongodb扩展，新旧版本兼容
        if (class_exists('MongoClient'))
        {
        	$this->mongo = new MongoClient($server, $config['option']);  
        }
        else
        {
        	$this->mongo = new Mongo($server, $config['option']);  
        }          
		//czy 20140705 参数检查统一到入口最前面，不要写到哪，检查到哪，另外不建议设这种默认值，出错了更麻烦
/*		if ($config['db_name'] == '') 
			$config['db_name'] = 'test';*/
			
		//czy 20140705 要先检查this->mongo 有没有创建成功啊，如果没有成功，就是返回失败了，而不是接着向下调
		if($this->mongo == null)
		{
			return false;
		}
        
		$this->db = $this->mongo->selectDB($config['db_name']);
		if($this->db == null)
		{
			return false;
		}		
		
		//czy 20140705 现实中user, passwd 这个库是不是可以为空也能用的，如果可以的，就是不需要检查的
        if (isset($config['user']) && isset($config['passwd']) && $config['user'] != '' && $config['passwd'] != '')
        {
			//czy 20140705 db 这个变量是不是有了，如果前面失败了？
            $this->db->authenticate($config['user'], $config['passwd']);
        }
        return $this->mongo;
    }

    /**
     * 选择一个集合，相当于选择一个数据表
     * @param string $collection 集合名称
     */
    public function select_collection($collection)
    {
        return $this->collection = $this->db->selectCollection($collection);
    }

    /**
	 * czy 20140705描述一下返回值，什么是成功，别人不需要再去了解collection insert 的返回值，封装就是为了隔离
     * 新增数据
     * @param array $data 需要新增的数据 例如：array('title' => '1000', 'username' => 'xcxx')
     * @param array $option 参数 --czy 20140705 这个有什么用
     * 可选参数：
     * array(
     * 'fsync' =>Boolean, defaults to FALSE.Forces the insert to be synced to disk before returning success. 
     *           If TRUE, an acknowledged insert is implied and will override setting w to 0.
     * 'j'     => Boolean, defaults to FALSE. Forces the write operation to block until it is synced to the journal on disk. 
     *           If TRUE, an acknowledged write is implied and this option will override setting "w" to 0.
     * 'w'     => See WriteConcerns. The default value for MongoClient is 1.
       'timeout'=>
     * )
     * @return boolean 出错返回false
     * )
     * 
     * 
     */
    public function insert($data, $option = array())
    {
		//czy 20140705 collection 是否有值？要先检查
        if($this->collection == null)
        {
        	return false;
        }		
        $result =  $this->collection->insert($data, $option);
        if(is_array($result) && !empty($result['err']) )
        {
        	return false;
        }
        else
        {
        	return $result;
        }
    }

    /**
	 * czy 20140705描述一下返回值，什么是成功，别人不需要再去了解collection insert 的返回值，封装就是为了隔离
     * 批量新增数据
     * @param array $data 需要新增的数据 例如：array(0=>array('title' => '1000', 'username' => 'xcxx'))
     * @param array $option 参数
     * @return boolean 出错返回false
     */
    public function batch_insert($data, $option = array())
    {
		//czy 20140705 collection 是否有值？要先检查
        if($this->collection == null)
        {
        	return false;
        }		
        $result = $this->collection->batchInsert($data, $option);
        if(is_array($result) && !empty($result['err']) )
        {
        	return false;
        }
        else
        {
        	return $result;
        }        
    }

    /**
	 * czy 20140705描述一下返回值，什么是成功，别人不需要再去了解collection insert 的返回值，封装就是为了隔离
     * 保存数据，如果已经存在在库中，则更新，不存在，则新增
     * @param array $data 需要新增的数据 例如：array(0=>array('title' => '1000', 'username' => 'xcxx'))
     * @param array $option 参数
     */
    public function save($data, $option = array())
    {
		//czy 20140705 collection 是否有值？要先检查
        if($this->collection == null)
        {
        	return false;
        }		
        return $this->collection->save($data, $option);
    }

    /**
     * 根据条件移除
     * @param array $query 条件 例如：array(('title' => '1000'))
     * @param array $option 参数
     */
    public function remove($query, $option = array())
    {
		//czy 20140705 collection 是否有值？要先检查
        if($this->collection == null)
        {
        	return false;
        }		
        return $this->collection->remove($query, $option);
    }

    /**
     * 根据条件更新数据
     * @param array $query 条件 例如：array(('title' => '1000'))
     * @param array $data 需要更新的数据 例如：array(0=>array('title' => '1000', 'username' => 'xcxx'))
     * @param array $option 参数
     */
    public function update($query, $data, $option = array())
    {
		//czy 20140705 collection 是否有值？要先检查
        if($this->collection == null)
        {
        	return false;
        }		
		//入参检查，是不是query不可能为空？还是说有全更新功能？data值会如何？要防止一条错误，把数据清没了。
        return $this->collection->update($query, $data, $option);
    }

    /**
     * 根据条件查找一条数据
     * @param array $query 条件 例如：array(('title' => '1000'))
     * @param array $fields 参数
     */
    public function find_one($query, $fields = array())
    {
		//czy 20140705 collection 是否有值？要先检查
        if($this->collection == null)
        {
        	return false;
        }		
		//返回值？
        return $this->collection->findOne($query, $fields);
    }

    /**
     * 根据条件查找多条数据
     * @param array $query 查询条件
     * @param array $sort 排序条件 array('age' => -1, 'username' => 1)
     * @param int $limit 页面
     * @param int $limit 查询到的数据条数
     * @param array $fields返回的字段
     */
    public function find($query = array(), $sort = array(), $skip = 0, $limit = 0, $fields = array())
    {
		//czy 20140705 collection 是否有值？要先检查
        if($this->collection == null)
        {
        	return false;
        }		
        $cursor = $this->collection->find($query, $fields);
		//czy 20140705 cursor 是否有值？要先检查
		//czy 20140705 返回值，skip,limit sort 是不是有返回值？
        if ($sort) $cursor->sort($sort);
        if ($skip) $cursor->skip($skip);
        if ($limit) $cursor->limit($limit);
        return iterator_to_array($cursor);
    }

    /**
     * 数据统计
     */
    public function count($query)
    {
		//czy 20140705 collection 是否有值？要先检查
        if($this->collection == null)
        {
        	return false;
        }		
        return $this->collection->count($query);
    }

    /**
     * 错误信息
     */
    public function error()
    {
		//czy 20140705 db 是否有值？要先检查
        if($this->db == null)
        {
        	return false;
        }		
        return $this->db->lastError();
    }

    /**
	 * czy 20140705 统一使名风格get_collection
     * 获取集合对象
     */
    public function get_collection()
    {
        return $this->collection;
    }

    /**
	 * czy 20140705 统一使名风格get_db
     * 获取DB对象
     */
    public function get_db()
    {
        return $this->db;
    }

	/**
	 * czy 20140705 这是什么功能？没有说明，入参出参？
	 * 聚合函数,可以实现类似sql group功能
	 */
    public function aggregate(array $pipeline)
    {
        return $this->collection->aggregate($pipeline);
    }
}

?>