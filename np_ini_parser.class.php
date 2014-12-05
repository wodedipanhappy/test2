<?php
/**
 * INI配置文件解析类
 * 功能：
 * 1.支持给一个字符串来解析，或是给一个文件名来解析。
 * 2.支持导出到文件或者导出为字符串。
 * 3.读写+session功能。如：a['section']['param']
 * 4.支持UTF8的BOM头，读取的时候有，写文件时保留。
 *
 * Created on 2012-8-18
 * @author xsong
 */
class np_ini_parser_class {
    public $ini_content;
    public function __construct() {
    }
    /**
     * return array
     */
    public  function get_all_section(){
    	return $this->parser_ini(true);
    }
    public function get_section($section_name=null){
    	$ini_arr = $this->parser_ini(true);
    	if(empty($section_name)) return $ini_arr;
    	if(is_array($ini_arr) && isset($ini_arr[$section_name])) return $ini_arr[$section_name];
    	return false;
    }
    public function get_item_value($key){
        $ini_arr = $this->parser_ini();
    	if(is_array($ini_arr) && isset($ini_arr[$key])) return $ini_arr[$key];
        return false;
    }
    public function load_file($src_file){
    	if(!file_exists($src_file)) return false;
    	$this->ini_content = file_get_contents($src_file);
    }
    public function load_string($ini_str){
    	if(empty($ini_str)) return false;
    	$this->ini_content = $ini_str;
    }
    /**
     * 导出ini到文件或字符串
     * 如果指定$desc_file，则导出到文件
     * 返回ini字符串
     */
    public function save_ini($desc_file=null){
    	if($desc_file)
    	    self::write_data($desc_file,$this->ini_content);

        return $this->ini_content;
    }

    public function parser_ini($process_sections=false){
    	if(empty($this->ini_content)) return false;
     	$ini_content = self::clearBom($this->ini_content);
    	return parse_ini_string($ini_content,$process_sections);
    }
	static private function clearBom($contents){
		$charset[1] = substr($contents, 0, 1);
		$charset[2] = substr($contents, 1, 1);
		$charset[3] = substr($contents, 2, 1);
		if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) {
				return substr($contents, 3);
		}
		return $contents;
	}
	static private function write_data($filename, $data) {
		$ret = false;

		if($filenum = fopen($filename, "wb")){
			$ret = fwrite($filenum, $data);
			fclose($filenum);
		}

        return $ret;
	}
}

//end of the file:np_ini_parser.class.php