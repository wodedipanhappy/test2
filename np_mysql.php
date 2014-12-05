<?php
function np_mysql_query( $host, $db, $user, $pwd, $sql )
{
	//echo $sql;
	//echo "np_mysql_query1";
	$link = mysql_connect( $host, $user, $pwd ,TRUE );
	
	if( !$link )
	{
		//echo "np_mysql_query mysql_connect failure";
		return FALSE;
	}
		
	//echo "np_mysql_query2";
	if ( ! @mysql_select_db ( $db, $link ) )
	{
		//echo "np_mysql_query mysql_select_db failure";
		@mysql_close( $link );
		return FALSE;
	}
	
	//echo "np_mysql_query3";
	@mysql_query ( "set names utf8 " ,$link );
	@mysql_query ( "set character_set_client=utf8"  , $link );
	@mysql_query ( "set character_set_results=utf8"  , $link );
	
	//echo "np_mysql_query4";
	$query_res = @mysql_query ( $sql, $link );
	if( !$query_res )
	{
		echo "np_mysql_query mysql_query failure";
		@mysql_close( $link );
		return FALSE;
	}
	
	$result = array ();
	while ( $row = @mysql_fetch_assoc ( $query_res ) ) 
	{
		$result [] = $row;
	}
	
	@mysql_free_result( $query_res );
	$query_res = FALSE;
	@mysql_close( $link );
	//echo "complete";
	return $result;
}
function np_mysql_exec( $host, $db, $user, $pwd, $sql )
{
	//echo $sql;
	//echo "np_mysql_query1";
	$link = mysql_connect( $host, $user, $pwd ,TRUE );
	
	if( !$link )
	{
		//echo "np_mysql_query mysql_connect failure";
		return FALSE;
	}
		
	//echo "np_mysql_query2";
	if ( ! @mysql_select_db ( $db, $link ) )
	{
		//echo "np_mysql_query mysql_select_db failure";
		@mysql_close( $link );
		return FALSE;
	}
	
	//echo "np_mysql_query3";
	@mysql_query ( "set names utf8 "  ,$link );
	@mysql_query ( "set character_set_client=utf8"  ,$link );
	@mysql_query ( "set character_set_results=utf8"  ,$link );
	
	//echo "np_mysql_query4";
	$query_res = @mysql_query ( $sql, $link );
	if( !$query_res )
	{
		echo "np_mysql_query mysql_query failure";
		@mysql_close( $link );
		return FALSE;
	}
	
	
	@mysql_close( $link );
	//echo "complete";
	return $query_res;
}



?>