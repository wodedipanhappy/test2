<?php

/**
 * 用于存储返回值
 * Created by IntelliJ IDEA.
 * User: 陈朱尧
 * Date: 2014/6/299
 * Time: 18:34
 */
class np_result
{
    static $arr_global_error = array('code'=>'','desc'=>'', 'args'=> NULL );

    static $arr_module_error = array();

    /**
     * 设置返回值
     * @param string $str_code  		错误码
     * @param string $string_desc 		措误描述
	 * @param object $mixed_args		错误对象，可不传
     */
    public static function set_last_error($str_code , $string_desc, $mixed_args)
    {
        self::$arr_global_error = array(
            'code' => $str_code ,
            'desc' => $string_desc ,
			'args'=>$mixed_args,
        );
    }
    /**
     * 读取返回值码
     * @return string
     */
    public static function get_last_error_code()
    {
        return self::$arr_global_error['code'];
    }
	
	 /**
     * 读取返回值结构，包括array(code,desc,args);
     * @return string
     */
	public static function get_last_error()
    {
        return self::$arr_global_error;
    }
	
	
	
   
}

function test_np_result(){
    $str_code='1001';
    $str_desc='1001全局错误';
    echo "设置全局的code:{$str_code},desc:{$str_desc} <br>";
    np_result::set_last_error($str_code,$str_desc);
    echo '输出设置的全局编号：'.np_result::get_last_error_code();
    echo '<br>输出设置的全局描述:'.np_result::get_last_error_code();

  
}
//test();