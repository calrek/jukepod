<?php
include ("../config/config.php");
include ("../inc/database.php");

$db = new db ();

$query = "UPDATE song SET last_played = " . time () . ", num_plays = num_plays + 1 WHERE ID = " . $_REQUEST ["ID"];
$db->query ( $query );
if ($db->affected_rows ())
	$success = "true";
else
	$success = "false";
?>
{"success":<?php echo $success; ?>}
