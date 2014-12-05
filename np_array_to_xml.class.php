<?php
/*
 *codereview 陈朱尧 2014-7-5
 *相关问题已经在代码注释中，这个类中的东西是啥意思啊，都看不明白
 *
*/
class np_array_to_xml{
    static $default_xml_encoding = 'utf-8'; //xml编码
    static $default_xml_version = '1.0'; //xml版本
    static $default_arr_attribute = 'NL_XML_ATTRIBUTE'; //数组里标注是属性节点的key
    static $default_arr_item_key = 'NL_XML_ITEM_KEY'; //数组里标注是数组节点的上标
    static $default_arr_item_node_name='i';
    static $default_new_line = "\r"; //XML换行符
	
	
	/*
	 * czy 2014-7-5 入参检查，这个基础的都没有
	 * 入参含义，返回值
	 * 命名，不要取 $array 这样直接使用系统保留名字的东西出来
	*/
	/**
	 * 将数组转换为XML
	 * @param 数组对象
	 * @param 根节点名称，默认为root
	 * @param 如果为索引数组对象，则每个item的节点名，默认为i
	 * @return XML字符串
	 */
    static public function array_to_xml($arr,$root_name='root',$array_item_node_name='item') {
        $encoding = self::$default_xml_encoding;
        $new_line = self::$default_new_line;
        $version = self::$default_xml_version;
        self::$default_arr_item_node_name = $array_item_node_name;
        $xml = '<?xml version="' . $version . '" encoding="' . $encoding . '" ?>' . $new_line;
        $xml.="<{$root_name}>";
        $xml.=@self::create_xml($arr);
        $xml.="</{$root_name}>";
        return $xml;
    }
	
	/*
	 * czy 2014-7-5
	 *写一个参数，返回的东西出来，不然这个函数没人知道怎么玩
	*/
	/**
	 * 根据数组返回XML字符串
	 * @param 数组对象
	 * @return XML字符串
	 */
    static private function create_xml($data) {
        $xml = '';
        $attribute_name = self::$default_arr_attribute;
//        $new_line = self::$default_new_line;
        foreach ($data as $key => $value) {
		
//        	过滤默认的$KEY $key==0时会等于任何除数字外的字符
			//czy 2014-7-5 这个过滤按什么标准在进行？看不懂

        	if (!is_numeric($key) && ($key==self::$default_arr_attribute || $key==self::$default_arr_item_key)){
        		continue;
        	}
        	
            $attribute = '';
            if (is_array($value)) {
                if (isset($value[$attribute_name])) {
                    $attribute = self::create_xml_attribute($value[$attribute_name]);
                    unset($value[$attribute_name]);
                }
                $text = self::create_xml($value);
            } else {
                $text = $value;
            }
			
			
            $key = str_replace(array(' '), '', $key); //去除key里空白

            if ( !is_array($value) )
            {
				//czy 2014-7-5 这一行有意义？text如果本来是空，还是空啊，还是说为了排除text=NULL这类的条件
				// strlen(NULL) 会怎么样？
				//s67 2014-07-06 strlen(NULL)==0 这里为了排除NULL
//                $text =strlen($text)==0 ? '' : $text;
				$text = $text===NULL ? '' : $text;
                
				//czy 2014-7-5 增加一下比如 . 之类我们常用，但不需要转义的字符。
                if (!preg_match('/[A-Za-z_0-9\.\-\:\/\\\\]+/i',$text)){
                	$text="<![CDATA[$text]]>";
                }
                
            }

//            	$KEY为数字时
				//czy 2014-7-5 XML本身是不是可以数字当KEY？
//				s67 2014-07-06 XML本身不支持数字当node_name
            	if (is_numeric($key)){
                    if (is_array($value))
                    {
                        $item_key = $value[self::$default_arr_item_key];
                    }


            		if (empty($item_key))
					{
						//czy 2014-7-5 不能用这个来工作，你不能假定外面不传，默认个i以达到n39的功能，这个外部必须传，
						//NP是基础库，写的时候不是为了LOGIC，协议做任何假定
            			$key=self::$default_arr_item_node_name;
            		}else{
            			unset($value[self::$default_arr_item_key]);
            			$key=$item_key;
            		}
            	}


            $xml .="<$key$attribute>$text</$key>";
                
        }
        return $xml;
    }

    /**
     * 创建属性节点
     * @param type $arr
     */
    static private function create_xml_attribute($arr) {
        $xml = ' '; //初始属性 需要包含一个空格
        foreach ($arr as $key => $value) {
            $xml .= "$key=\"$value\" ";
        }
        return $xml;
    }
    
    
    
	private $root_node = null;
	private $root_node_name = 'root';
	private static $attr_node = '@attributes'; // 属性节点定义
	private $dom = null;
	
	function __construct($root=null,$version='1.0', $encoding='utf-8'){
		
		$this->dom = new DOMDocument($version, $encoding);
		if($root != null){
			$this->root_node = $this->dom->createElement($root);
		}
		else{
			$this->root_node = $this->dom->createElement($this->root_node_name);
		}
		
		$this->root_node->setAttribute('name', 'test');
		$this->dom->appendChild($this->root_node);
	}
	
	public function convert($array, $version='1.0', $encoding='utf-8'){
		
		if(!is_array($array)){
			return $this->dom->saveXML();
		}
		$this->root_node = $this->traverse_array($array, $this->root_node);
		
		return $this->dom->saveXML();
	}
	
	// 创建一个节点
	private function create_xml_node($node_name){
		return $this->dom->createElement($node_name);
	}
	
	// 设置节点属性
	private function set_node_attr($node, $attr=array()){
		if(!is_array($attr)) return $node;
		
		foreach ($attr as $k=>$v){
//			if(is_array($v)) continue;
			$node->setAttribute($k, $v);
		}
		return $node;
	}
	
	// 循环数组创建节点
	private function traverse_array($arr, $parent_node, $parent_key=''){
		if(!is_array($arr)){
			return $parent_node;
		}
		foreach ($arr as $key=>$item){
			
			if(intval($key) === $key){ // 非数字键 此时应往下层传递你级节点键名
				$child_node = $this->create_xml_node($parent_key);
				$child_node = $this->traverse_array($item, $child_node, $parent_key);
				$parent_node->appendChild( $child_node );
				
			}else if($key === self::$attr_node){
				$parent_node = $this->set_node_attr($parent_node, $item);
				
			}else{
				// 先判定 下层元素键名是否为数字,如是则不创建子节点
				if(!isset($item[0])){
					$child_node = $this->create_xml_node($key);
					$child_node = $this->traverse_array($item, $child_node, $key);
					$parent_node->appendChild( $child_node );
				}else{
					$parent_node = $this->traverse_array($item, $parent_node, $key);
				}
			}
		}
		
		return $parent_node;
	}
}

