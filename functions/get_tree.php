<?php
include "../config/config.php";
include "../inc/database.php";
include "../inc/functions.php";

$db = new db ();

$node = isset ( $_REQUEST ['node'] ) ? $_REQUEST ['node'] : "";

if ($node == - 1)
	$node = 0;
	/*
 * { $query = "SELECT ID FROM path WHERE parentID = 0 ORDER BY name"; $result = $db->query($query); $node = $db->result($result,0,0); }
 */

$query = "SELECT ID,name,num_files,num_dirs,num_files_total,full_path,parentID FROM dir WHERE parentID = " . $node . " ORDER BY name";
$nodes = array ();
$result = $db->query ( $query );

while ( $row = $db->fetch_array ( $result ) ) {
	
	$num_files = $row ["num_files"];
	
	$num_files_total = $row ["num_files_total"];
	
	if ($row ["num_dirs"])
		$is_leaf = 0;
	else
		$is_leaf = 1;
	
	if ($row ["parentID"])
		$my_root = 0;
	else {
		$my_root = 1;
		$row ["name"] = "MP3";
	}
	
	if ($num_files_total)
		$nodes [] = array (
				'name' => $row ["name"],
				'text' => $row ["name"] . " (" . number_format ( $num_files_total, 0, ',', '.' ) . ")",
				'full_path' => $row ["full_path"],
				'id' => $row ["ID"],
				'leaf' => $is_leaf,
				'cls' => 'folder',
				'my_root' => $my_root 
		);
}
echo json_encode ( $nodes );
?>
