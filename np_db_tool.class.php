<?php

class np_db_tool_class {

    /*
     * @param $db  np库DB实例 
     * @param $db_name 数据库名称
     * @return array 数据库表名称，及表的数据行数
     * 如：
     *  array(137) {
		  [0]=>
		  array(2) {
		    ["table_name"]=>
		    string(15) "nns_view_record"
		    ["table_rows"]=>
		    string(4) "4231"
		  }
		  [1]=>
		  array(2) {
		    ["table_name"]=>
		    string(16) "nns_access_count"
		    ["table_rows"]=>
		    string(4) "3629"
		  }
		  ...
     }
     */
    static public function get_table_info($db,$db_name){
    	//
    	set_time_limit(0);
    	if(empty($db_name)) return null;
    	$rs = $db->query('use information_schema;');
    	if(!$rs) return null;
    	//
    	$sql = "select table_name,table_rows,engine,table_collation,data_length,index_length from tables where TABLE_SCHEMA = '$db_name' order by table_rows desc";
    	$rs = $db->query($sql);
    	if(!$rs) return null;
    	$result =  $db->get_query_result( FALSE );
    	if(is_array($result)){
    		$rs = $db->query('use '.$db_name.';');
    		//var_dump($rs);exit;
    		if(!$rs) return null;
    		$ret_data=array();
    		foreach($result as $item){
    			//
    			
    			$sql = "select count(*) as count from ".$item['table_name'] ;
    			$rs = $db->query($sql);
    			if(!$rs) return null;
    			$rs_count =  $db->get_query_result( FALSE );
    			//var_export($rs_count,true);exit; 
    			$item['table_rows'] = $rs_count[0]['count'];
    			
    			$ret_data[] =$item;
    		}
    		return $ret_data;
    	}
    	return null;
    }
    static public function show_master_status($db){
    	$sql = "show master status";
    	$rs = $db->query($sql);
    	if(!$rs) return null;
    	return $db->get_query_result( TRUE );
    }
    static public function show_slave_status($db){
    	$sql = "show slave status";
    	$rs = $db->query($sql);
    	if(!$rs) return null;
    	return $db->get_query_result( TRUE );    	
    }
    static public function show_processlist($db){
    	$sql = "show processlist";
    	$rs = $db->query($sql);
    	if(!$rs) return null;
    	return $db->get_query_result( TRUE );     	
    }
}
?>