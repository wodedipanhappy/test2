<?php

/*
 *codereview 陈朱尧 2014-7-5
 *相关问题已经在代码注释中，这个类中的东西是啥意思啊，都看不明白
 *
*/

class np_xml_to_array
{

    private static $same_node = 'Name'; // 同级属性键值定义 key
    private static $same_node_a = 'Value'; // 同级属性键值定义 value
    /**
     * 去掉CDATA便于建立数组
     * @param $xml XML数据
     * @return string  返回去掉CDATA，并转换特殊字符后的XML数据
     */
    static public function uncdata($xml)
    {
        preg_match_all("/>\s*<!\[CDATA\[(.*)?\]\]>\s*</", $xml, $preg);
        $replace = $preg[1];
        $search = $preg[0];
        if (is_array($replace))
        {
            foreach ($replace as $key => $value)
            {
                if (!empty($replace[$key]))
                {
                    $replace[$key] = htmlspecialchars($replace[$key]);
                }
                $replace[$key] = ">" . $replace[$key] . "<";
            }
//            var_dump($search); var_dump($replace);die;
            $new_xml = str_replace($search, $replace, $xml);
//            echo $new_xml;die;
        }
        else
        {
            $new_xml = $xml;
        }

        return $new_xml;
    }

    static function parse2($xml)
    {
        $xml = self::uncdata($xml);
        $xmlObj = simplexml_load_string($xml);
//        echo $xml;
//        echo json_encode($xmlObj);
        $jsonArray = json_decode(json_encode($xmlObj), true);
        $jsonArray = self::remove_empty_array($jsonArray);
        return $jsonArray;
//        return self::same_node_up(self::check_child($jsonArray));
    }

    static function remove_empty_array($arr)
    {
        foreach ($arr as $key => $v)
        {
            if (is_array($v))
            {
                if (empty($v))
                {
                    $arr[$key] = "";
                }
                else
                {
                    $arr[$key] = self::remove_empty_array($v);
                }

            }
            elseif (strlen(trim($v)) == 0)
            {
                unset($arr[$key]);
            }
        }
        return $arr;
    }


    /*
    *czy 2014-7-5 这个解析看不懂
    */
    static function parse($xml)
    {
        $xml = self::uncdata($xml);
        $xmlObj = simplexml_load_string($xml);
        $jsonArray = json_decode(json_encode($xmlObj), true);
//        return $jsonArray;
        return self::same_node_up(self::check_child($jsonArray));
    }

    private static function check_child($jsonArray)
    {
        if (is_array($jsonArray))
        {
            $jsonArray = self::convert_attribute($jsonArray);

            foreach ($jsonArray as $key => $v)
            {
                if (is_array($v))
                {
                    $jsonArray[$key] = self::check_child($v);
                }
            }
        }
        return $jsonArray;
    }

    private static function convert_attribute($xml_to_json_array)
    {
        if (is_array($xml_to_json_array))
        {

            foreach ($xml_to_json_array as $key => $v)
            {
                if ($key === '@attributes')
                {
                    if (is_array($v))
                    {
                        foreach ($v as $k => $_v)
                        {
                            $xml_to_json_array[$k] = $_v;
                        }
                    }

                    unset($xml_to_json_array[$key]);
                }
            }
        }
        return $xml_to_json_array;
    }

    // 同级节点上浮
    private static function same_node_up($arr)
    {
        if (!is_array($arr))
        {
            return $arr;
        }

        $key_arr = array_keys($arr);
        if (!is_int($key_arr[0]))
        {
            foreach ($arr as $k => $v)
            {
                $arr[$k] = self::same_node_up($v);
            }
        }
        else
        {
            foreach ($arr as $k => $v)
            {
                if (!isset($v[self::$same_node]) && !isset($v[self::$same_node_a]))
                {
                    $arr[$k] = self::same_node_up($v);
                }
                else
                {
                    if (!isset($arr[$v[self::$same_node]]))
                    {
                        $arr[$v[self::$same_node]] = $v[self::$same_node_a];
                    }
                    else
                    {
                        $arr[$v[self::$same_node]] .= ',' . $v[self::$same_node_a];
                    }
                    unset($arr[$k]);
                }
            }
        }

        return $arr;
    }
}

