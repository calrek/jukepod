<?php
include "inc/database.php";

$config_c = file_get_contents( "config/config.php" );

$rows = explode( ";", $config_c );

$config_array = array(
	"DB_SERVER",
	"DB_USER",
	"DB_PASSWD",
	"DB_NAME",
	"MP3_PATH",
	"ALBUM_MATCH",
	"LANGUAGE",
	"COVER_SIZE_X",
	"COVER_SIZE_Y",
	"ENTRIES_IN_ALBUM_LIST",
	"ENTRIES_IN_MP3_LIST",
	"ENTRIES_IN_ARTIST_LIST",
	"ENTRIES_IN_TOP_PLAYLIST",
	"MIN_CHARS_IN_SEARCH",
	"SEARCH_DELAY_MS" );

for ( $i=0;$i<sizeof( $rows );$i++ ) {
	$row = $rows[$i];

	for ( $j=0;$j<sizeof( $config_array );$j++ ) {
		if ( strstr( strtolower( $row ), "define" ) and strstr( $row, $config_array[$j] ) and array_key_exists( $config_array[$j], $_POST ) ) {
			$row = preg_replace( "/(define\(['\"]".$config_array[$j]."['\"][[:space:]]*,[[:space:]]*['\"]{0,1})(.*?)(['\"]{0,1}\))/im", "$1".$_POST[$config_array[$j]]."$3", $row );
		}
	}

	$result[] = $row;
}
echo "/*";

echo implode( ";", $result );

echo "*/";

function check_connection() {
	if ( mysql_connect( $_POST["DB_SERVER"], $_POST["DB_USER"], $_POST["DB_PASSWD"] ) )
		return true;
	else
		return false;
}

function check_databasename() {
	if ( mysql_select_db( $_POST["DB_NAME"] ) )
		return true;
	else
		return false;
}

function check_mp3_path() {
	@$dh = opendir( $from );
	if ( $dh )
		return true;
	else
		return false;
}
?>
