<?php

/**
 * xml转化为数组,支持命名空间
 * @auth yxt
 * 2014年3月10日9:06:29
 */
class np_xml2array {

    private static $arr_re=array(
        0=>array(
            'ret'=>0,
            'reason'=>'解析失败',
        ),
        1=>array(
            'ret'=>1,
            'reason'=>'成功',
        )
    );
    /**
     * @param string $str_xml xml字符串
     * @return array 转化后的数组array('ret'=>返回编码,'reason'=>编码解释,'data'=>解析后的数组)
     */
    static function getArrayData($str_xml = null) {
        $obj_node = @simplexml_load_string($str_xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
        if ($obj_node === false) {
            $arr_re = self::$arr_re[0];
        } else {
            $arr_re = self::$arr_re[1];
            $arr_re['data']=self::parseNode($obj_node);
        }
        return $arr_re;
    }

    /**
     * 解析SimpleXMLElement节点对象
     * @param SimpleXMLElement $obj_node 需要解析的几点
     * @param array $arr_parent 本次递归返回的数组
     * @param string $str_namespace 命名空间
     * @param bool $bool_recursive 是否是递归
     * @return array
     */
    private static function parseNode($obj_node, &$arr_parent = null, $str_namespace = '', $bool_recursive = false) {
        $arr_namespaces = $obj_node->getNameSpaces(true);
        $str_content = "{$obj_node}";
        $arr_children['name'] = $obj_node->getName();
        if (!$bool_recursive) {
            $tmp = array_keys($obj_node->getNameSpaces(false));
            $arr_children['namespace'] = $tmp[0];
            $arr_children['namespaces'] = $arr_namespaces;
        }
        if ($str_namespace) {
            $arr_children['namespace'] = $str_namespace;
        }
        if ($str_content) {
            $arr_children['content'] = $str_content;
        }
        foreach ($arr_namespaces as $pre => $ns) {
            $_obj_xml_node = $obj_node->children($ns);
            foreach ($_obj_xml_node as $k => $v) {
                self::parseNode($v, $arr_children['children'], $pre, true);
            }
            $_boj_xml_attributes = $obj_node->attributes($ns);
            foreach ($_boj_xml_attributes as $k => $v) {
                $arr_children['attributes'][$k] = "$pre:$v";
            }
        }
        foreach ($obj_node->children() as $k => $v) {
            self::parseNode($v, $arr_children['children'], '', true);
        }
        foreach ($obj_node->attributes() as $k => $v) {
            $arr_children['attributes'][$k] = "$v";
        }
        $arr_parent[] = &$arr_children;
        return $arr_parent[0];
    }

}
