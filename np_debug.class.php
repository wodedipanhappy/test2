<?php

/**
 * 用于存储调试编号和调试数据信息
 * Created by IntelliJ IDEA.
 * User: 于小涛
 * Date: 2014/5/19
 * Time: 18:34
 */
class np_debug
{
    static $arr_global_error = array('code'=>'','desc'=>'');

    static $arr_module_error = array();

    /**
     * 设置全局的调试数据，这个全局的只会保留一份，多次调用只会保留最后一次的
     *
     * @param string $str_code  调试编号
     * @param string $string_desc 调试描述,可以是任意类型数据
     */
    public static function set_global_error($str_code , $string_desc)
    {
        self::$arr_global_error = array(
            'code' => $str_code ,
            'desc' => $string_desc ,
        );
    }
    /**
     * 获取调试编号，获取的是最后一次设置的全局的编号
     * @return string
     */
    public static function get_global_error_code()
    {
        return self::$arr_global_error['code'];
    }
    /**
     * 获取调试描述，获取的是最后一次设置的全局的描述
     * @return string
     */
    public static function get_global_error_desc()
    {
        return self::$arr_global_error['desc'];
    }
    /**
     * 添加调试，调试数据调用一次会往后叠加
     *
     * @param string $str_code  调试错误编号
     * @param string $str_desc 调试模块描述,可以是任意类型数据
     */
    public static function add_module_error($str_code , $str_desc)
    {
        self::$arr_module_error[] = array(
            'code' => $str_code ,
            'desc' => $str_desc,
        );
    }

    /**
     * 获取全部模块调试信息,返回的是一个二维数组，如果从来没有记录过，返回一个空的一维数组
     * @return array
     */
    public static function get_module_error()
    {
        return self::$arr_module_error;
    }

    /**
     * 添加调试，一个一个往上叠加
     *
     * @param string $str_code  调试模块编号
     * @param string $str_action 调试动作
     * @param string $str_desc 调试模块描述,可以是任意类型数据
     * @param mixed $mixed_other_data 其他数据
     */
    public static function add_module_error_v2($str_code ,$str_action, $str_desc,$mixed_other_data=null)
    {
        self::$arr_module_error[] = array(
            'code' => $str_code,
            'action'=>$str_action,
            'desc' => $str_desc,
            'other_data'=>$mixed_other_data,
        );
    }

    /**
     * 获取全部模块调试信息,返回的是一个二维数组，如果从来没有记录过，返回一个空的一维数组
     * @return array
     */
    public static function get_module_error_v2()
    {
        return self::$arr_module_error;
    }
    /**
     * 添加调试，根据模块添加调试数据
     * @param string $str_module_name 模块名称
     * @param string $str_code  调试模块编号
     * @param string $str_desc 调试模块描述,可以是任意类型数据
     * @param mixed $mixed_args 模块参数
     */
    public static function add_module_error_v3($str_module_name,$str_code , $str_desc,$mixed_args='')
    {
        self::$arr_module_error[] = array(
            'code' => $str_code ,
            'desc' => $str_desc,
            'module_name'=>$str_module_name,
            'args'=>$mixed_args,
        );
    }

    /**
     * 获取指定模块下的所有调试信息
     * @param string $str_module_name 模块名称 如果为空，则返回模块名为空的数据
     *
     * @return array $arr_module_data 返回模块下的数据，
     */
    public static function get_module_error_by_module_name($str_module_name='')
    {
        $arr_module_data=array();
        foreach(self::$arr_module_error as $arr_module_item)
        {
            if($arr_module_item['module_name']==$str_module_name)
            {
                $arr_module_data[]=$str_module_name;
            }
        }
        return $arr_module_data;
    }
}

function test_np_debug(){
    $str_code='1001';
    $str_desc='1001全局错误';
    echo "设置全局的code:{$str_code},desc:{$str_desc} <br>";
    np_debug::set_global_error($str_code,$str_desc);
    echo '输出设置的全局编号：'.np_debug::get_global_error_code();
    echo '<br>输出设置的全局描述:'.np_debug::get_global_error_desc();

    $str_code='1002';
    $str_desc='1002模块错误';
    echo "<br>设置模块错误code:{$str_code},desc:{$str_desc} <br>";
    np_debug::add_module_error($str_code,$str_desc);
    $str_code='1003';
    $str_desc='1003模块错误';
    echo "<br>设置模块错误code:{$str_code},desc:{$str_desc} <br>";
    np_debug::add_module_error($str_code,$str_desc);
    $arr_module_error=np_debug::get_module_error();
    echo '输出所有模块错误<pre>';
    print_r($arr_module_error);
}
//test_np_debug();