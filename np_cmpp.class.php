<?php

//登录
define("CMPP_CONNECT", 0x00000001);
//短信提交
define("CMPP_SUBMIT", 0x00000004);
//版本号
define("SP_VERSION", 12);
define("SEQUENCE_ID", 2);

class np_cmpp
{
    //网关地址
    static $sp_host = "211.138.155.249";
    //网关端口
    static $sp_port = "8855";
    //网关认证账户
    static $source_addr = "913175";
    //网关认证密码
    static $sp_password = "400hjl9fP";
    //短信服务号
    static $sp_source_id = "10658209";
    //短信资费(单位为分)
    static $fee_code = "1";

    /**
     * @param $socket_obj
     * @param string $mobile 手机号码
     * @param $text   短消息内容 长度为140个字节
     * @author huayi.cai
     * 向网关提交短信，现在暂时支持单点发送
     */
    private static function do_send($socket_obj, $mobile, $text, $pk_total = 1, $pk_number = 1, $tp_udhi = 0)
    {
        $msg_id = "";
        //$pk_total = 1;
        //$pk_number = 1;
        $registered_delivery = 1;
        $msg_level = 0;
        $service_id = "mgtv_12345";
        $fee_userType = "2"; //计费类型
        $fee_terminal_id = str_repeat("\0", 21);
        $tp_pid = 0;
        //$tp_udhi = 0;
        //消息格式
        $msg_fmt = dechex(8); //十进制转16进制
        $msg_src = self::$source_addr;
        $fee_type = "01";
        $fee_code = self::$fee_code;
        $valid_time = date("YmdHis");
        $at_time = "";
        $src_id = self::$sp_source_id;
        $destUsr_tl = 1;
        $dest_terminal_id = $mobile; //必须是福建移动手机号码

        $msg_content = $text;
        $msg_length = strlen($msg_content);
        $reserve = str_repeat("\0", 8);


        $message_body = pack("a8", $msg_id);
        $message_body .= pack("h", $pk_total);
        $message_body .= pack("h", $pk_number);
        $message_body .= pack("h", $registered_delivery);
        $message_body .= pack("h", $msg_level);
        $message_body .= pack("a10", $service_id);
        $message_body .= pack("h", $fee_userType);
        $message_body .= pack("a21", $fee_terminal_id);
        $message_body .= pack("h", $tp_pid);
        $message_body .= pack("h", $tp_udhi);
        $message_body .= pack("h", $msg_fmt);
        $message_body .= pack("a6", $msg_src);
        $message_body .= pack("a2", $fee_type);
        $message_body .= pack("a6", $fee_code);
        $message_body .= pack("a17", $valid_time);
        $message_body .= pack("a17", $at_time);
        $message_body .= pack("a21", $src_id);
        $message_body .= pack("h", $destUsr_tl);
        $message_body .= pack("a21", $dest_terminal_id);
        //超长短信此字段为1，普通短信此字段为0
        if ($tp_udhi == 0)
        {
            $message_body .= pack("H*", dechex($msg_length));
            $message_body .= $msg_content;
        }
        else
        {
            //长短信协议头 使用 6位协议头格式：05 00 03 XX MM NN
            $message_body .= pack("H*", dechex($msg_length + 6));
            $tp_udhi_header = pack("hhhHhh", 0x05, 0x00, 0x03, 0x0A, dechex($pk_total), dechex($pk_number));
            $message_body .= $tp_udhi_header . $msg_content;
        }
        $message_body .= pack("a8", $reserve);

        //生成消息头
        $body_len = strlen($message_body);
        $message_header = self::build_message_header(CMPP_SUBMIT, $body_len);
        $message = $message_header . $message_body;

        //发送;
        socket_write($socket_obj, $message) or die("Write failed\n");
        $response = "";
        $response = socket_read($socket_obj, 1024, PHP_BINARY_READ);

        if ($response)
        {
            //去掉消息头
            $response_body = substr($response, 12);
            //$result = unpack("a8Msg_Id/HResult", $response_body);
            $msg_id = unpack("H*", substr($response_body, 0, 8));
            $status = unpack("H*", substr($response_body, 8, 1));

            $result['msg_id'] = $msg_id[1];
            $result['status'] = $status[1];
        }

    }

    /**
     * @param $socket
     * @return bool
     * @author huayi.cai
     * 登录短信网关
     */
    static private function connect_ismg($socket)
    {
        //消息体
        $timestamp = date("mdHis");
        $authenticator_source = md5(self::$source_addr . str_repeat("\0", 9) . self::$sp_password . $timestamp, true);

        $message_body = pack("a6", self::$source_addr) . pack("a16", $authenticator_source) . pack("H", SP_VERSION) . pack("N", (int)$timestamp);
        //生成消息头
        $message_header = self::build_message_header(CMPP_CONNECT, strlen($message_body));

        $message = $message_header . $message_body;
        $connection = socket_connect($socket, self::$sp_host, self::$sp_port) or die("Could not connet server\n");    //  连接
        socket_write($socket, $message) or die("Write failed\n"); // 数据传送 向服务器发送消息

        $response = socket_read($socket, 1024, PHP_BINARY_READ);
        if ($response)
        {
            //去掉消息头
            $response_body = substr($response, 12);
            //$result = unpack("Nstatus/H16authcode/Nversion", $response_body);
            $status = unpack("H*", substr($response_body, 0, 1));
            $authenticator_ismg = unpack("H*", substr($response_body, 1, 16));
            $version = unpack("H*", substr($response_body, 17));


            $result['status'] = hexdec($status[1]);
            $result['authenticator_ismg'] = $authenticator_ismg[1];
            $result['version'] = $version[1];
            if ($result['status'] == 0)
            {
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param $command_id 命令ID
     * @param $body_length 消息体长度
     * @return string 消息头信息
     * @author huayi.cai
     * 生成cmpp消息头
     */
    static private function build_message_header($command_id, $body_length)
    {
        $message_total = $body_length + 12;
        //消息头
        $message_header = pack("NNN", $message_total, $command_id, SEQUENCE_ID);

        return $message_header;
    }

    /**
     * @param $str
     * @param string $encode
     * @return string
     * @author huayi.cai
     * 把短消息内容转换成ucs2编码
     */
    public static function convert_to_ucs2($str, $encode = "UTF-8")
    {
        return iconv("utf-8", "UCS-2BE", $str);
    }

    /**
     * @param $mobile
     * @param $text
     * @author huayi.cai
     * 向移动用户发送短消息
     */
    public static function  send_sms($mobile, $text)
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        //登录梦网
        $ret = self::connect_ismg($socket);
        if ($ret === true)
        {
            //发送短信
            self::send_to_ismg($socket, $mobile, $text);
        }
        socket_close($socket);

    }

    /**
     * @param $config
     * @return bool false表示配置不正确
     * @author huayi.cai
     * 设置短信网关相关配置信息
     */
    public static function set_sms_config($config)
    {
        if (!is_array($config))
        {
            return false;
        }
        //没传这些值 就使用福建移动的默认值,但是福建移动绑定了ip的，所以不能在其他ip发送成功
        self::$sp_host = isset($config['host']) ? $config['host'] : self::$sp_host;
        self::$sp_port = isset($config['port']) ? $config['port'] : self::$sp_port;
        self::$source_addr = isset($config['account']) ? $config['account'] : self::$source_addr;
        self::$sp_password = isset($config['password']) ? $config['password'] : self::$sp_password;
        self::$sp_source_id = isset($config['source_id']) ? $config['source_id'] : self::$sp_source_id;
        //资费
        self::$fee_code = isset($config['fee_code']) ? $config['fee_code'] : self::$fee_code;
        return true;
    }

    /**
     * @param $socket
     * @param string $mobile
     * @param $text
     * @author huayi.cai
     * 提交短信到网关，根据短信长度自动按普通短信格式发送还是按超长短信发送
     */
    private static function send_to_ismg($socket, $mobile = "15060138819", $text)
    {
        $text = self::convert_to_ucs2($text);
        $text_len = strlen($text);
        //一条短消息只支持140个字节
        $max_len = 140;
        //拆分成多少条可发送的短消息条数
        if ($text_len <= 140)
        {
            $message_count = 1;
        }
        else
        {
            $message_count = ceil($text_len / ($max_len - 6));
        }

        if ($message_count == 1)
        {
            //普通短消息发送
            $tp_udhi = 0;
            $pk_total = 1;
            $pk_number = 1;
            self::do_send($socket, $mobile, $text, $pk_total, $pk_number, $tp_udhi);
        }
        else
        {
            //超长消息发送,手机会自动显示为一条 短消息
            for ($i = 0; $i < $message_count; $i++)
            {
                $tp_udhi = 1;
                $pk_total = $message_count;
                $pk_number = $i + 1;
                //tp_udhi信息头为6个字节，所以这里用减6
                $offset = $i * ($max_len - 6);
                $max_len = $max_len - 6;

                $sub_message = substr($text, $offset, $max_len);
                self::do_send($socket, $mobile, $sub_message, $pk_total, $pk_number, $tp_udhi);
            }
        }
    }
}

?>
