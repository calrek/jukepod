<?php

$db = new db ();

if (! $_SESSION ["userID"] and $_COOKIE ["jukepod"])
	$_SESSION = unserialize ( stripslashes ( $_COOKIE ["jukepod"] ) );

if (! $_SESSION ["userID"]) {
	$query = "SELECT ID FROM user WHERE type = 'guest'";
	$result = $db->query ( $query );
	if ($db->num_rows ( $result ))
		$_SESSION ["userID"] = $db->result ( $result, 0, "ID" );
}

if ($_SESSION ["userID"]) {
	$query = "SELECT name,type,access_option,ip FROM user WHERE ID = '" . $_SESSION ["userID"] . "'";
	$result = $db->query ( $query );
	if ($db->num_rows ( $result )) {
		$_SESSION ["username"] = $db->result ( $result, 0, "name" );
		$_SESSION ["usertype"] = $db->result ( $result, 0, "type" );
		$_SESSION ["access_option"] = $db->result ( $result, 0, "access_option" );
		$_SESSION ["ip"] = $db->result ( $result, 0, "ip" );
	}

	$query = "SELECT * FROM user_permissions WHERE userID = " . $_SESSION ["userID"];
	$result = $db->query ( $query );
	$_SESSION ["permission"] = array ();
	while ( $row = $db->fetch_array ( $result ) ) {
		$_SESSION ["permission"] [$row ["name"]] = $row ["value"];
	}
}

if (! (defined ( "SCRIPT" ) and SCRIPT == "IMPORT" and $_GET ["admin_key"])) {
	if (! $_SESSION ["userID"] or ! $_SESSION ["permission"] ["has_access"]) {
		if (defined ( "SCRIPT" ) and SCRIPT == "main") {
			header ( "location: login.php" );
			die ();
		} else
			die ();
	}
}
?>
