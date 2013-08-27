<?php
include "../config/config.php";
include "../inc/database.php";
include "../inc/functions.php";
include "../locale/language.php";

include "../security.php";

if (! $_REQUEST ["text"])
	$text = "name";
else
	$text = $_REQUEST ["text"];

if (! $_REQUEST ["sort"])
	$sort = $text;
else
	$sort = $_REQUEST ["sort"];

if (! $_REQUEST ["tab"])
	$tab = $_REQUEST ["type"];
else
	$tab = $_REQUEST ["tab"];

$data_array = array ();
switch ($tab) {
	case "playlist" :
		if (! $_SESSION ["permission"] ["see_all_playlists"]) {
			$query = "SELECT playlist.ID, playlist.name AS text_to_display,
			if(playlist_song.ID,count(*),0) AS num,
			playlist.userID AS userID
		FROM playlist
		LEFT JOIN playlist_song ON (playlist.ID = playlist_song.playlistID)
		WHERE playlist.userID = " . $_SESSION ["userID"] . "
		GROUP BY playlist.ID
		ORDER BY playlist.name
		";
		} else {
			$query = "SELECT playlist.ID, playlist.name AS text_to_display,
			if(playlist_song.ID,count(*),0) AS num,
			if(user.ID,user.name,'Unbekannt') AS USER,
			playlist.userID AS userID
		FROM playlist
		LEFT JOIN USER ON (playlist.userID = USER.ID)
		LEFT JOIN playlist_song ON (playlist.ID = playlist_song.playlistID)
		GROUP BY playlist.ID
		ORDER BY if(playlist.userID=" . $_SESSION ["userID"] . ",0,1),
			playlist.name
		";
		}
		break;
	default :
		$query = "SELECT ID," . $text . " as text_to_display FROM " . $tab . " order by " . $sort;
		break;
}
echo "/* " . $query . " */\n";

$row_array = array ();

if ($_REQUEST ["addall"]) {
	$pointer = sizeof ( $data_array );
	$data_array [$pointer] ["ID"] = - 1;
	$data_array [$pointer] [$text] = "[alle]";
	
	$row_array [] = "['-1', '[alle]']";
}

if ($_REQUEST ["addnobody"]) {
	if ($_REQUEST ["nobody_text"])
		$nobody_text = $_REQUEST ["nobody_text"];
	else
		$nobody_text = lang ( "nobody" );
	
	$pointer = sizeof ( $data_array );
	$data_array [$pointer] ["ID"] = 0;
	$data_array [$pointer] [$text] = "[" . $nobody_text . "]";
	
	$row_array [] = "[0, '[" . $nobody_text . "]']";
}

if ($_REQUEST ["tab"] == "playlist") {
	$pointer = sizeof ( $data_array );
	$data_array [$pointer] ["ID"] = - 1;
	$data_array [$pointer] [$text] = "[" . lang ( "latest50" ) . "]";
	$data_array [$pointer] ["tooltip"] = lang ( "tooltip_latest50" );
	
	$row_array [] = "[-1, '[" . lang ( "latest50" ) . "]']";
	
	$pointer = sizeof ( $data_array );
	$data_array [$pointer] ["ID"] = - 2;
	$data_array [$pointer] [$text] = "[" . lang ( "top50" ) . "]";
	$data_array [$pointer] ["tooltip"] = lang ( "tooltip_top50" );
	
	$row_array [] = "[-2, '[" . lang ( "top50" ) . "]']";
	
	$pointer = sizeof ( $data_array );
	$data_array [$pointer] ["ID"] = - 3;
	$data_array [$pointer] [$text] = "[" . lang ( "latest_listened50" ) . "]";
	$data_array [$pointer] ["tooltip"] = lang ( "tooltip_latest_listened50" );
	
	$row_array [] = "[-3, '[" . lang ( "latest_listened50" ) . "]']";
	
	$pointer = sizeof ( $data_array );
	$data_array [$pointer] ["ID"] = - 4;
	$data_array [$pointer] [$text] = "[" . lang ( "5stars50" ) . "]";
	$data_array [$pointer] ["tooltip"] = lang ( "tooltip_5stars50" );
	
	$row_array [] = "[-4, '[" . lang ( "5stars50" ) . "]']";
	
	$pointer = sizeof ( $data_array );
	$data_array [$pointer] ["ID"] = - 5;
	$data_array [$pointer] [$text] = "[" . lang ( "4stars50" ) . "]";
	$data_array [$pointer] ["tooltip"] = lang ( "tooltip_4stars50" );
	
	$row_array [] = "[-5, '[" . lang ( "4stars50" ) . "]']";
	
	$pointer = sizeof ( $data_array );
	$data_array [$pointer] ["ID"] = - 6;
	$data_array [$pointer] [$text] = "[" . lang ( "3stars50" ) . "]";
	$data_array [$pointer] ["tooltip"] = lang ( "tooltip_3stars50" );
	
	$row_array [] = "[-6, '[" . lang ( "3stars50" ) . "]']";
}

if ($query) {
	$result = $db->query ( $query );
	while ( $row = $db->fetch_array ( $result ) ) {
		$pointer = sizeof ( $data_array );
		$data_array [$pointer] ["ID"] = $row ["ID"];
		$data_array [$pointer] ["name"] = $row ["text_to_display"];
		if ($_REQUEST ["tab"] == "playlist") {
			if (! $_SESSION ["permission"] ["see_all_playlists"])
				$data_array [$pointer] ["tooltip"] = $row ["num"] . " " . lang ( "files" );
			else
				$data_array [$pointer] ["tooltip"] = $row ["num"] . " " . lang ( "files" ) . "<br />" . lang ( "created_by" ) . " <b>" . $row ["user"] . "</b>";
			
			echo "/* " . $row ["userID"] . " = " . $_SESSION ["userID"] . " */";
			if ($row ["userID"] == $_SESSION ["userID"])
				$class_add = "";
			else
				$class_add = " other-playlist";
			
			$data_array [$pointer] ["class_add"] = $class_add;
			
			$data_array [$pointer] ["userID"] = $row ["userID"];
		}
		
		$row_array [] = "['" . $row ["ID"] . "', '" . addslashes ( $row ["text_to_display"] ) . "']";
	}
} elseif ($list_array) {
	for($i = 0; $i < sizeof ( $list_array ); $i ++) {
		if ($assoz_array) {
			$pointer = sizeof ( $data_array );
			$data_array [$pointer] ["ID"] = key ( $list_array );
			$data_array [$pointer] [$text] = current ( $list_array );
			
			$row_array [] = "['" . key ( $list_array ) . "', '" . addslashes ( current ( $list_array ) ) . "']";
			next ( $list_array );
		} else {
			$pointer = sizeof ( $data_array );
			$data_array [$pointer] ["ID"] = $list_array [$i];
			$data_array [$pointer] [$text] = $list_array [$i];
			
			$row_array [] = "['" . $list_array [$i] . "', '" . addslashes ( $list_array [$i] ) . "']";
		}
	}
}

if (! $_REQUEST ["no_assign"]) {
	echo "Ext.namespace('Ext." . $tab . "');\n";
	
	echo "Ext." . $tab . ".list = [";
	
	echo implode ( ",\n", $row_array );
	
	echo "]";
} else
	echo "{ success: true, recordcount: " . sizeof ( $data_array ) . ", rows: " . json_encode ( $data_array ) . "}";
?>
