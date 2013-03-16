<?php
include "config/config.php";
include "locale/language.php";
include "inc/database.php";
include "inc/functions.php";
include "security.php";

$db = new db();

$data = json_decode( stripslashes( $_POST["data"] ), true );

if ( !$data["rating"] ) {
	$query = "DELETE FROM song_rating WHERE songID = ".$data["ID"]." AND userID = ".$_SESSION["userID"];
}
else {
	$query = "SELECT count(*) FROM song_rating WHERE songID = ".$data["ID"]." AND userID = ".$_SESSION["userID"];
	$result = $db->query( $query );
	if ( $db->result( $result, 0, 0 ) ) {
		$query = "UPDATE song_rating SET rating = ".$data["rating"]." WHERE songID = ".$data["ID"]." AND userID = ".$_SESSION["userID"];
	}
	else {
		$query = "INSERT INTO song_rating (userID,songID,rating) VALUES (".$_SESSION["userID"].",".$data["ID"].",".$data["rating"].")";
	}
}
echo "/* QUERY: $query */\n";
$db->query( $query );
$result = array();
if ( $db->affected_rows() )
	$result["success"] = true;
else
	$result["success"] = false;

echo json_encode( $result );
?>
