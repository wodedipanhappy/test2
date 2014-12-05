<?php
/*
 *codereview 陈朱尧 2014-7-5
 *相关问题已经在代码注释中，这个类中的东西是啥意思啊，都看不明白
 *
*/
class np_mlang_class{
	private $ini_lang = array();
	private $ini_path = null;
	private $ini_content = array();
	private $ini_parser = null;
	private $ini_status = 0;
	private $web_path = null;
	private $module = null;
	
	function __construct($web_path){
		$this->web_path = $web_path;
	}
	
	//czy 2014-7-5 不要把函数写到构成函数前去了
	public function set_module($module)
	{
		$this->module = $module;
	}
	
    /**
     * 加载多语言文件
     * @param $lang_path=array(
     * 			'zh_CN'=>'/nn_cms/nn_cms_manager/languages/zh_CN/lang_zh_CN.ini',
     *			'en_US'=>'/nn_cms/nn_cms_manager/languages/en_US/lang_en_US.ini',
     * )
     * @param $lang_arr = array(
     * 			'en_US',
     * 			'zh_CN',
     * ) 排序、优先读取顺序
     * 
     * return  -1、加载失败 0、成功 1、部分配置加载失败 4、$lang_path不能为空 5、$lang_arr不能为空
     */
    public function load($lang_path,$lang_order){
    	if(empty($lang_path)){
    		$this->ini_status = '4';
    	}
    	if(empty($lang_order)){
    		$this->ini_status = '5';
    	}
    	$this->ini_path = $lang_path;
    	$this->ini_lang = $lang_order;
    	//加载配置文件
    	$bool = true;
    	foreach($lang_order as $key=>$lang){
    		//语言配置文件路径组装
    		$path = $lang_path[$lang];
    		//$path = dirname(dirname(__FILE__)).$path;
    		if($this->module)
    		{
    			$arr = explode('/',$path);
                $pos = count($arr)-1;
                $module_path = str_replace('lang_'.$lang.'.ini', 'lang_'.$this->module.'_'.$lang.'.ini',$path);
                $module_path = $this->web_path.$module_path;
                if(file_exists($module_path))
                {
	    			//解析ini文件 
	    			$ini_arr = $this->parser_file($module_path);
	    			//var_dump($ini_arr);exit;
	    			$this->ini_content[$lang.'_'.$this->module] = $ini_arr;
                }
    		}
    		
    		$path = $this->web_path.$path;
			//全部失败或者部分配置文件加载失败
    		if(file_exists($path)){
    			//解析ini文件 
    			$ini_arr = $this->parser_file($path);
    			
    			$this->ini_content[$lang] = $ini_arr;
				//czy 2014-7-5 这是什么意思
    			$bool = false;
    		}else{
				//czy 2014-7-5 这个bool变量只会进true逻辑啊
    			if($bool){
    				$this->ini_status = '-1';
    			}else{
    				$this->ini_status = '1';
    			}
    		}

    	}
    	
    	return $this->ini_status;
    	
    }
    
    
    /**
	 *  生成ini文件
	 * @param $lang_path ='/nn_cms/nn_cms_manager/language/'
	 * @param $current_lang = "zh_CN"
	 * @param $ini_data  array  
	 * 如:$ini_data = array(
	 * 				'key'=>'value',    key 支持中文
	 * 				'add'  =>'添加',
	 * 				'edit' =>'编辑',	
	 * 				)
	 * 	@return true/false		true 成功、flase 失败								
	 */
	public function set_lang($lang_path,$current_lang,$ini_data){
	
	}
	
	
	/**
	 * 根据key获取对应值
	 * @param $key 支持多个key 以"|"分隔，如：key1|key2|key3...
	 * @param $lang 指定语言
	 */
	public function get_lang($keys, $lang=null){
		//如果$lang为空，按照语言优先级读取指定语言值
		$key_arr = explode('|',$keys);
		$bool = true;
		$return_str = false;
		
		$lang_array = $this->ini_lang;
		if( !empty($lang) && in_array($lang, $this->ini_lang) ){
			$lang_array = array($lang);
		}
		foreach($key_arr as $key){
		
			//czy 2014-7-5 这种变量没有办法知道用处啊。
			$bool = true;
			foreach($lang_array as $lan){
				if($this->module && isset($this->ini_content[$lan.'_'.$this->module]))
				{
                    
					$ini_arr = isset($this->ini_content[$lan.'_'.$this->module])?$this->ini_content[$lan.'_'.$this->module]:'';
					if(is_array($ini_arr) && isset($ini_arr[$key])){
						if($lan == 'en_US'){
							$return_str .=  ' '.$ini_arr[$key];
						}else{
							$return_str .=  $ini_arr[$key];
						}
						
						$bool = false;
						break;
					}
				}
				
				$ini_arr = $this->ini_content[$lan];
				if(is_array($ini_arr) && isset($ini_arr[$key])){
					if($lan == 'en_US'){
						$return_str .=  ' '.$ini_arr[$key];
					}else{
						$return_str .=  $ini_arr[$key];
					}
					
					$bool = false;
					break;
				}
			}
			if($bool){
				$this->status = '3';
				$return_str .= $key.':key_not find';
				$bool = false;
			}
		}
		return $return_str;
	}
    /**
     * 解析ini文件
     * @param $path
     */
    public function parser_file($path){
    	//$ini_content = file_get_contents($path);
     	//$ini_content = self::clear_bom($ini_content);
    	//return parse_ini_string($ini_content,true);
    	return parse_ini_file($path,true);
    }
    
    /**
     * 用于读取ini文件时清理 bom格式
     * @param $contents
     */
	static private function clear_bom($contents){
		$charset[1] = substr($contents, 0, 1);
		$charset[2] = substr($contents, 1, 1);
		$charset[3] = substr($contents, 2, 1);
		if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) {
				return substr($contents, 3);
		}
		return $contents;
	}
	/**
	 * 保存ini文件
	 * @param $filename
	 * @param $data
	 */
	static private function write_file($filename, $data) {
		$ret = false;
		if($filenum = fopen($filename, "wb")){
			$ret = fwrite($filenum, $data);
			fclose($filenum);
		}

        return $ret;
	}
	/**
	 * 获取状态码
	 */
	public function get_last_error(){
		if($this->status == '3'){
			return 'key_not find';
		}
	}
}

?>