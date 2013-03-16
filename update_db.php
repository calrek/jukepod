<?php
include "config/config.php";
include "inc/database.php";
include "inc/functions.php";

$_SESSION["sql_log"] = array();

$queries = file_get_contents( "database_update.sql" );

$query_array = explode( ";", $queries );

for ( $i=0;$i<sizeof( $query_array );$i++ ) {
	if ( $query_array[$i] ) $db->query( $query_array[$i] );
}

if ( sizeof( $_SESSION["sql_log"] ) ) {
	echo "there were errors: <br>";
	echo show_sql();
}
else
	echo "all_done...";
?>
