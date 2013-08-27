<?php
include "../config/config.php";
include "../locale/language.php";
include "../inc/database.php";
include "../inc/functions.php";
include "../security.php";

if (! $_SESSION ["permission"] ["edit_own_playlists"] and ! $_SESSION ["permission"] ["edit_all_playlists"])
	die ( $lang ["no_permission"] );

$id = 0;
switch ($_POST ["action"]) {
	case "create" :
		if ($_POST ["name"]) {
			$query = "SELECT ID FROM playlist WHERE name = '" . $_POST ["name"] . "' AND userID = '" . $_SESSION ["userID"] . "'";
			$result = $db->query ( $query );
			if ($db->num_rows ( $result )) {
				$success = "false";
				$message = lang ( "error_playlist_name_not_unique" );
			} else {
				$query = "INSERT INTO playlist (name,userID) VALUES ('" . $_POST ["name"] . "','" . $_SESSION ["userID"] . "')";
				$db->query ( $query );
				$id = $db->insert_id ();
				if ($id)
					$success = "true";
				else {
					$success = "false";
					$message = lang ( "error_creating_new_playlist" );
				}
			}
		} else
			$success = false;
		break;
	case "edit" :
		if ($_POST ["name"]) {
			$query = "SELECT ID FROM playlist WHERE name = '" . $_POST ["name"] . "' AND userID = '" . $_SESSION ["userID"] . "' AND ID != " . $_POST ["id"];
			$result = $db->query ( $query );
			if ($db->num_rows ( $result )) {
				$success = "false";
				$message = lang ( "error_playlist_name_not_unique" );
			} else {
				$query = "UPDATE playlist set name = '" . addslashes ( $_POST ["name"] ) . "' WHERE ID = " . $_POST ["id"];
				if (! $_SESSION ["permission"] ["edit_all_playlists"])
					$query .= " AND userID = '" . $_SESSION ["userID"] . "'";
				$result = $db->query ( $query );
				if ($db->affected_rows ( $result ))
					$success = true;
				else
					$success = "false";
			}
		}
		break;
	case "delete" :
		if ($_POST ["id"]) {
			$query = "DELETE FROM playlist WHERE ID = " . $_POST ["id"];
			if (! $_SESSION ["permission"] ["edit_all_playlists"])
				$query .= " AND userID = '" . $_SESSION ["userID"] . "'";
			echo "/* " . $query . " */";
			$result = $db->query ( $query );
			if ($db->affected_rows ( $result )) {
				$success = true;
				$query = "DELETE FROM playlist_song WHERE playlistID = " . $_POST ["id"];
				echo "/* " . $query . " */";
				$result = $db->query ( $query );
			} else
				$success = "false";
		} else
			$success = "false";
		break;
	case "save_playlist" :
		if ($_POST ["songIDs"] and $_POST ["id"]) {
			$songIDs = str_replace ( "[", "", $_POST ["songIDs"] );
			$songIDs = str_replace ( "]", "", $songIDs );
			$songIDs = str_replace ( "\"", "", $songIDs );
			$songIDs = str_replace ( "\\", "", $songIDs );
			$array = explode ( ",", $songIDs );
			$query = "SELECT ID FROM playlist WHERE ID = " . $_POST ["id"];
			if (! $_SESSION ["permission"] ["edit_all_playlists"])
				$query .= " AND userID = '" . $_SESSION ["userID"] . "'";
			$result = $db->query ( $query );
			if ($db->num_rows ( $result )) {
				$query = "DELETE FROM playlist_song WHERE playlistID = " . $_POST ["id"];
				echo "/* " . $query . " */";
				$result = $db->query ( $query );
				if ($db->affected_rows ( $result ))
					$success = true;
				else
					$success = "false";

				if ($songIDs) {
					for($i = 0; $i < sizeof ( $array ); $i ++) {
						$query = "INSERT INTO playlist_song (playlistID,songID,sort_order) values (" . $_POST ["id"] . "," . $array [$i] . "," . $i . ")";
						echo "/* " . $query . " */";
						$db->query ( $query );
					}
				}
			}
		}
}

?>{"success":<?php echo $success; ?>, "id": <?php echo $id; ?>, "message": "<?php echo $message; ?>"}
