<?php

include("config/config.php");
include("inc/database.php");
include("inc/functions.php");

$db = new db();

$node = isset($_REQUEST['node']) ? $_REQUEST['node'] : "";

if( ! $node) {
  $query = "SELECT ID,name FROM playlist ORDER BY name";
  $type = "playlist";
  $is_leaf = false;
  $cls = "folder";
}else {
  $node_array = explode("-", $node);
  $query = "SELECT
							song.ID,song.title,song.artist,song.duration
						FROM
							song,playlist_song
						WHERE
							song.ID = playlist_song.songID
							AND playlist_song.playlistID = " . $node_array[1];
  $type = "files";
  $is_leaf = true;
  $cls = "files";
}

$nodes = array();
$result = $db->query($query);
while($row = $db->fetch_array($result)) {
  if($type == "files")
    $name = $row["artist"] . " - " . $row["title"];
  else
    $name = $row["name"];
  $nodes[] = array('text' => json_umlaut($name), 'id' => $type . "-" . $row["ID"], 'leaf' => $is_leaf, 'cls' => $cls);
}
echo json_encode($nodes);
?>
