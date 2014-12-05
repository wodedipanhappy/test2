<?php
/**
 * 日志处理类
 * 注意：
 * 1、如果间隔时间未到，但是文件如果超出了属性int_gt_max_log_file_size倍数的文件规定的大小则不会将日志写入文件
 * User: yxt
 * Date: 2014/5/12
 * Time: 9:19
 * codereview 2014-7-5 陈朱尧 
 * 这是非常基础的类，修改的时候请注意
 */

class np_log_v2
{
    private $str_log_dir = ''; //日志目录
    private $str_log_level = "info"; //log等级：info,error,null(表示不记录任何日志)
    public $arr_log_msg = array('info' => array(), 'error' => array()); //log信息
    public $arr_log_config = array(); //log配置
    public $level_error = 'error';
    public $level_info = 'info';
    private $str_log_ext='txt';//日志后缀
    private $int_current_time=0;//本次程序执行第一次调用配置的时间戳
    private $int_gt_max_log_file_size_rate=2;//当前文件大于规定的文件多少倍则不写入
    private $bool_init=true;//表示初始化是否成功，如果初始化不成功是不会写入日志的
    private $arr_log_write_file=array(//保存当前写入各个等级的日志文件名，用于做缓存这样一个完整的请求下来就只会去生成一次文件
        'error'=>'',
        'info'=>'',
    );

    /**
     * 设置日志目录，参数不能够为空
     * @param string $str_dir 日志存放目录
     * @return boolean 返回是否设置成功
     */
    public function set_dir($str_dir)
    {
        if (empty($str_dir))
        {
            $this->bool_init=false;
            return false;
        }
        $bool_dir_exists = true;
        if (!is_dir($str_dir))
        {
            $bool_dir_exists = @mkdir($str_dir, 0777, true);
        }
        if(!$bool_dir_exists)
        {
			//czy 2014/7/5 这个值默认是成功，这样的行为是很怪的，如果外面跳过了set_dir,直接调info，却会检查到init=true,等于件条检查没有用。
			//这是一个比较麻烦的状态，建议目录设了，就认为是成功了，level不设，可以构成一个默为不输出
            $this->bool_init=false;
        }else{
            $this->str_log_dir = rtrim($str_dir, '/').'/';
        }
        return $bool_dir_exists;
    }

    /**
     * 设置当前的等级
     * @param $str_current_level，info，error，null(不写任何日志),如果是null在日志初始化失败，以后的任何日志都不予保存
     */
    public function set_current_level($str_current_level)
    {
		//入参要检查，是不是info,error,null,NULL 这4个，其它值根本不需要向下走逻辑，直接可以返回失败
		
        if($str_current_level=='null'||$str_current_level==null)
        {
			//czy 2014/7/5 不建议改初始化失败，修改输出等级，这个模式只是设置为输出等级为null，相当于不输出。
            $this->bool_init=false;
            return false;
        }
        $this->str_log_level = strtolower($str_current_level);
        return true;
    }
    /**
     * log等级设置，用于配置info和error各自等级的配置
     * @param string $str_level log类型只能够是类属性：level_error、level_info
     * @param ing $int_file_size 每个日志文件的大小 单位是（M）,0表示不限制,如果传递<0的参数默认改为100M
     * @param string $str_file_prefix 该类型目录下的日志文件的前缀，不能够为空,如果带了.和_会自动删除掉
     * @param ing $int_interval_time 日志生成的时间间隔，0表示不限制，默认5分钟，如果参数<0则改为5分钟
     * @return boolean 返回设置是否成功
     */
    public function set_level_config($str_level, $int_file_size, $str_file_prefix, $int_interval_time = 300)
    {
        $this->int_current_time=time();
        if ($str_level != $this->level_error && $str_level != $this->level_info)
        {
            $this->bool_init=false;
            return false;
        }
        if (empty($str_file_prefix))
        {
            $this->bool_init=false;
            return false;
        }
		//czy 2014/7/5 比如第一次，调set_level_config=null,下一行再调=info,也不工作了 
		//不建议把一个bool_init又当成init，又当成level等级的关闭。
		
        //取消掉前缀中带有的.和_
        $str_file_prefix=str_replace('.','',$str_file_prefix);
        $str_file_prefix=str_replace('_','',$str_file_prefix);
        $this->arr_log_config[$str_level]['int_file_size'] = $int_file_size<0?100:$int_file_size;
        $this->arr_log_config[$str_level]['str_file_prefix'] = $str_file_prefix . '_'; //给前缀添加下划线
        $this->arr_log_config[$str_level]['int_valid_time'] = $int_interval_time<=0?300:$int_interval_time;
        return true;
    }
    /**
     * 保存info信息
     * @param mix $mix_info log信息，可以是任意类型，数组和对象多会被var_export来存储
     */
    public function info($mix_info)
    {
        //判断是否初始化成功
        if(!$this->bool_init)
        {
            return false;
        }
        //如果当前日志等级不是info则不做保存
        if ($this->str_log_level != 'info')
        {
            return false;
        }
        if (is_array($mix_info) || is_object($mix_info))
        {
            $mix_info = var_export($mix_info, true);
        }
        $_int_mtime = microtime(true);
        $_arr_mtime = explode('.', $_int_mtime);
        $_arr_mtime[1]=str_pad($_arr_mtime[1],4,'0',STR_PAD_LEFT);
        $this->arr_log_msg['info'][] = "[" . date("Y-m-d H:i:s") . '.' . $_arr_mtime[1] . "][info]" . $mix_info;
		
		//czy 2014/7/5
		return true;
    }
    /**
     * 保存error信息
     * @param mix $mix_info log信息，可以是任意类型，数组和对象多会被var_export来存储
     */
    public function error($mix_info)
    {
        //判断是否初始化成功
        if(!$this->bool_init)
        {
            return false;
        }
		
		//当前其它状态检查，如果不满足条件，不要向下走了，比如arr_log_msg为空
		
        if (is_array($mix_info) || is_object($mix_info))
        {
            $mix_info = var_export($mix_info, true);
        }
        $_int_mtime = microtime(true);
        $_arr_mtime = explode('.', $_int_mtime);
        $_arr_mtime[1]=str_pad($_arr_mtime[1],4,'0',STR_PAD_LEFT);
        $str_msg = "[" . date("Y-m-d H:i:s") . '.' . $_arr_mtime[1] . "][error]" . $mix_info;
		
		//czy 2014/7/5  arr_log_msg有可能没有成功初始化，
        $this->arr_log_msg['error'][] = $str_msg;
        //如果当前的等级是info则info也保存一份
        if ($this->str_log_level == 'info')
        {
            $this->arr_log_msg['info'][] = $str_msg;
        }
		
		//czy 2014/7/5
		return true;
    }

    /**
     * 将日志写入文件，讲之前掉用info和error保存的日志写入到文件
     */
    public function save()
    {
        //如果日志初始化失败则不准许写入
        if(!$this->bool_init)
        {
            return false;
        }
		
		//czy 2014/7/5  arr_log_msg
		
        foreach ($this->arr_log_msg as $str_level => $arr_log_info)
        {
            //如果日志数据为空不保存
            if (empty($arr_log_info))
            {
                continue;
            }
            //获取需要写入日志的文件
            $str_log_file=$this->_get_write_file($str_level);
            if($str_log_file==false)
            {
                continue;
            }
            //写日志
            $this->_write($str_level,$str_log_file, $arr_log_info);
            //清理日志数据
            $this->_clean($str_level);
        }
    }

    /**
     * 根据等级获取，写入该等级日志的文件
     * @param $str_level
     */
    private function _get_write_file($str_level)
    {
        //如果之前没有缓存需要写入的日志文件，则根据当前的时间创建一个
        if(empty($this->arr_log_write_file[$str_level])){
            $str_write_file = $this->_create_log_file($str_level);
            $this->arr_log_write_file[$str_level] = $str_write_file;
        }
        return $this->arr_log_write_file[$str_level];
//        $str_level_dir=$this->str_log_dir.$str_level;//当前等级目录
//        if(!is_dir($str_level_dir)){
//            $bool_mkdir=@mkdir($str_level_dir);
//            if(!$bool_mkdir){
//                return false;
//            }
//        }
//        $handle = @opendir($str_level_dir);
//        if(empty($handle))
//        {
//            return false;
//        }
//        $arr_file = array();
//        $str_current_write_log_file=$this->arr_log_config[$str_level]['str_file_prefix'].'0000-00-00 00:00:00.0.'.$this->str_log_ext;//当前需要写入的文件
//        $arr_current_write_log_file=array();
//        while (false !== ($name = readdir($handle)))
//        {
//            //循环目录，排除不是当前前缀的文件和规定日志后缀的文件
//            if ($name == '.' || $name == '..' || strpos($name, $this->arr_log_config[$str_level]['str_file_prefix']) !== 0||(strrpos($name,$this->str_log_ext)!==(strlen($name)-strlen($this->str_log_ext))))
//            {
//                continue;
//            }
//            $arr_current_log_file=$this->_explode_log_file($name);
//            $arr_current_write_log_file=$this->_explode_log_file($str_current_write_log_file);
//            if(($arr_current_write_log_file['create_time']<$arr_current_log_file['create_time'])||(($arr_current_write_log_file['create_time']==$arr_current_log_file['create_time'])&&($arr_current_write_log_file['num']<$arr_current_log_file['num']))){
//                $str_current_write_log_file=$name;
//                $arr_current_write_log_file=$arr_current_log_file;
//            }
//            $arr_file[]=$name;
//        }
//        closedir($handle);
//        $int_count=count($arr_file);
//        //如果一个日志文件都没有创建一个新的返回
//        if($int_count==0){
//            $str_current_write_log_file=$this->_create_log_file($str_level);
//        }else{//如果获取日志文件成功，则需要判断是否过期，和是否超出了规定大小的2倍
//            $str_current_write_log_file=$this->str_log_dir.$str_level.'/'.$str_current_write_log_file;
//            //如果时间超出，则创建一个新的
//            $str_current_date=$this->_create_log_create_time($this->int_current_time,$this->arr_log_config[$str_level]['int_valid_time']);
//            if($arr_current_write_log_file['create_time']<$str_current_date){
//                $str_current_write_log_file=$this->_create_log_file($str_level);
//            }else {
//                $int_file_size = filesize($str_current_write_log_file) / (1024 * 1024);
//                //如果大小超出返回false
//                if ($this->arr_log_config[$str_level]['int_file_size'] > 0 && $int_file_size > ($this->arr_log_config[$str_level]['int_file_size'] * $this->int_gt_max_log_file_size)) {
//                    return false;
//                }
//            }
//        }
//        return $str_current_write_log_file;
    }

    /**
     * 分割文件
     * @param string $str_log_file 文件名
     * @return array $arr_log_file 返回分割之后的文件数据
     */
//    private function _explode_log_file($str_log_file){
//        $str_file_name = basename($str_log_file);
//        $_arr_file_explode = explode('.', $str_file_name);
//        $arr_return['ext'] = array_pop($_arr_file_explode);
//        $_arr_file_explode_time = explode('_', $_arr_file_explode[0]);
//        $arr_return['prefix'] = $_arr_file_explode_time[0] . '_';
//        $arr_return['create_time'] = $_arr_file_explode_time[1];
//        $arr_return['num'] = array_pop($_arr_file_explode);
//        return $arr_return;
//    }
    /**
     * 根据日志等级，创建一个日志文件名
     * @param string $str_level 日志等级
     * @param string $int_num 当前编号默认0,表示该时间段第一次创建
     * @param string string $str_level_file_name 返回完整文件名
     */
    private function _create_log_file($str_level,$int_num=0)
    {
        if(!is_dir($this->str_log_dir.$str_level.'/')){
            $bool_mkdir=@mkdir($this->str_log_dir.$str_level.'/', 0777,true);
            if($bool_mkdir==false){
                return false;
            }
        }
        $int_time=$this->int_current_time - ($this->int_current_time % $this->arr_log_config[$str_level]['int_valid_time']);
        $str_date_time=date("Ymd", $int_time).'T'.date("His",$int_time);
        $str_level_file_name=$this->str_log_dir.$str_level.'/'.$this->arr_log_config[$str_level]['str_file_prefix'].$str_date_time.'.'.$int_num.'.'.$this->str_log_ext;
        return $str_level_file_name;
    }

    /**
     * 清理对应等级的日志
     * @param $str_level
     */
    private function _clean($str_level)
    {
        $this->arr_log_msg[$str_level] = array();
    }
    /**
     * 写入日志,写入的时候会判断文件是否超出大小
     * @param string $str_level 日志等级
     * @param string $str_file_name 写入的文件名
     * @param array $arr_log_msg 需要写入的日志信息,一维数组
     * @return int $int_write 返回写入文件的字节数，如果写入失败返回false
     */
    public function _write($str_level,$str_file_name, $arr_log_msg)
    {

        if(file_exists($str_file_name))
        {
            $int_file_size = filesize($str_file_name) / (1024 * 1024);
            //如果大小超出返回false
            if ($this->arr_log_config[$str_level]['int_file_size'] > 0 && ($int_file_size > ($this->arr_log_config[$str_level]['int_file_size'] * $this->int_gt_max_log_file_size_rate)))
            {
                return false;
            }
        }
        $str_msg = '';
        foreach ($arr_log_msg as $str_log)
        {
            $str_msg .= $str_log . PHP_EOL;
        }
        //这里需要做容错，打开失败返回false
        $handle_file = @fopen($str_file_name, "a+");
		//czy 2014/7/5 即然明确返回false为失败，就应检查===false,而不是empty
        if(empty($handle_file))
        {
            return false;
        }
        $int_write=@fwrite($handle_file, $str_msg);
        @fclose($handle_file);
        return $int_write;
    }
}