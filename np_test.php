<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR ."np_string.php" ;
include_once dirname(__FILE__).DIRECTORY_SEPARATOR ."np_time.php" ;

//var_dump( np_build_query_from_url_string( NULL, TRUE) );
var_dump( np_time_from_iso( "20120102T030405" ) );
echo time();
echo ";";
echo np_time_from_iso( "20120102T030405" );
echo ";";
echo strftime( "%Y-%m-%d %H:%M:%S",np_time_from_iso( "20120102T030405" ));


?>