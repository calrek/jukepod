<?php
define ( "SCRIPT", "CLEAR_CACHE" );

include "config/config.php";
$_SESSION ["sql_log"] = array ();

include "inc/database.php";
include "inc/functions.php";
include "inc/mp3_tree.php";
include "getid3/getid3/getid3.php";
include "locale/language.php";

include "security.php";

if (! $_SESSION ["permission"] ["read_files"]) {
	if (array_key_exists ( "admin_key", $_GET ) and $_GET ["admin_key"]) {
		$query = "SELECT ID FROM user WHERE type = 'admin' AND password = '" . $_GET ["admin_key"] . "'";
		$result = $db->query ( $query );
		if ($db->num_rows ( $result )) {
			$admin_id = $db->result ( $result, 0, 0 );
			$query = "SELECT value FROM user_permissions WHERE userID = " . $admin_id . " AND value = '1' AND name = 'read_files'";
			$result = $db->query ( $query );
			if (! $db->num_rows ( $result ))
				die ( $lang ["no_permission"] );
		} else {
			die ( $lang ["no_permission"] );
		}
	} else
		die ( $lang ["no_permission"] );
}

$query = "TRUNCATE TABLE cache";
$db->query ( $query );

$query = "TRUNCATE TABLE cache_entry";
$db->query ( $query );
?>
