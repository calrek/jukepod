<?php
define( "no_login_check", true );

include "formgen/formgen.php";

function error_on_query( $query ) {

	die( "ERROR: ".mysql_error()." ON ".$query );
}

$data_array = json_decode( $_POST["data"], true );

if ( $data_array["data"] ) {
	for ( $i=0;$i<sizeof( $data_array["data"] );$i++ ) {
		$row = $data_array["data"][$i];
		switch ( $row["type"] ) {
		case "u":
			$query = "UPDATE ".$_REQUEST["tab"]." SET ".$row["data"][0]["name"]." = '".$row["data"][0]["new"]."' WHERE ID = ".$row["ID"];
			formgen_sql::query( $query ) or error_on_query( $query );
			break;
		case "d":
			$query = "DELETE FROM ".$_REQUEST["tab"]." WHERE ID = ".$row["ID"];
			formgen_sql::query( $query ) or error_on_query( $query );
			break;
		case "c":
			$query_part = array();
			for ( $j=0;$j<sizeof( $row["data"] );$j++ ) {
				if ( key( $row["data"] )!="ID" )
					$query_part[] = key( $row["data"] )." = '".addslashes( current( $row["data"] ) )."'";
				next( $row["data"] );
			}
			$query = "REPLACE ".$_REQUEST["tab"]." SET ".implode( ", ", $query_part );
			formgen_sql::query( $query ) or error_on_query( $query );
			break;
		}
	}
	echo "SUCCESS";
}
else {
	echo "SUCCESS";
}
?>
