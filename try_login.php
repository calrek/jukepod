<?php
include "config/config.php";
include "inc/database.php";
include "inc/functions.php";

$db = new db();

$query = "SELECT ID,name,type,access_option,ip FROM user WHERE name = '".addslashes( $_POST["username"] )."' AND password = '".md5( $_POST["password"] )."' AND type != 'guest'";

$result = $db->query( $query );

if ( $db->num_rows( $result ) ) {

	$success = "true";

	$_SESSION = array();

	$_SESSION["userID"] = $db->result( $result, 0, "ID" );

	if ( $_POST["save_login"] )

		setcookie( "Jukepod", serialize( $_SESSION ), time()+24*60*60*365 );

	$_SESSION["ip"] = $db->result( $result, 0, "ip" );

	$success = ip_check();

	$_SESSION["username"] = $db->result( $result, 0, "name" );
	$_SESSION["usertype"] = $db->result( $result, 0, "type" );
	$_SESSION["access_option"] = $db->result( $result, 0, "access_option" );

	$query = "SELECT * FROM user_permissions WHERE userID = ".$_SESSION["userID"];

	$result = $db->query( $query );

	while ( $row = $db->fetch_array( $result ) ) {
		$_SESSION["permission"][$row["name"]] = $value;
	}
} else $success = "false";

?>
{ success: <?=$success;?> }
