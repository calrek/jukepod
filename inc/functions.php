<?php

function is_leaf($id)
{
	global $db;

	$query = "SELECT count(*) FROM dir WHERE parentID = " . $id;
	$result = $db->query ( $query );
	if ($db->result ( $result, 0, 0 ))
		return false;
	else
		return true;
}
function build_path($id, $path_array = array()) {
	global $db;

	$query = "SELECT name,parentID FROM dir WHERE ID = " . $id;
	$result = $db->query ( $query );
	if ($db->num_rows ( $result )) {
		$path_array [] = $db->result ( $result, 0, "name" );
		$path_array = build_path ( $db->result ( $result, 0, "parentID" ), $path_array );
		return $path_array;
	} else {
		return implode ( "/", array_reverse ( $path_array ) );
	}
}
function file_count($id, $path) {
	global $db;

	$query = "SELECT count(*) FROM song WHERE full_path LIKE '" . ROOT . $path . "%'";
	$result = $db->query ( $query );
	$count = $db->result ( $result, 0, 0 );

	$query = "UPDATE dir SET num_files = " . $count . " WHERE ID = " . $id;
	$db->query ( $query );

	return $count;
}
function mysql_die() {
	echo mysql_error ();
	die ();
}
function show_sql($output_to = "screen", $crlf = "\n", $tab = "\t", $log_file = "log.html") {
	$output = "";
	if (sizeof ( $_SESSION ["sql_log"] )) {
		$output = "<table>" . $crlf;

		for($i = 0; $i < sizeof ( $_SESSION ["sql_log"] ); $i ++) {
			$temp_array = $_SESSION ["sql_log"] [$i];
			switch ($temp_array [1]) {
				case "log" :
					$output .= $tab . "<tr style='color: black'>" . $crlf . $tab . $tab . "<td alivalign='top'>LOG: ";
					break;
				case "warning" :
					$output .= $tab . "<tr style='color: black'>" . $crlf . $tab . $tab . "<td alivalign='top'>WARNING: ";
					break;
				case "error" :
					$output .= $tab . "<tr style='color: red'>" . $crlf . $tab . $tab . "<td valign='top'>ERROR: ";
					break;
				case "query" :
					$output .= $tab . "<tr style='color: green'>" . $crlf . $tab . $tab . "<td valign='top'>QUERY: ";
					break;
				default :
					break;
			}
			$output .= "</td>" . $crlf . $tab . $tab . "<td valign='top'>";
			$output .= $temp_array [0];
			$output .= "</td>" . $crlf . $tab . "</tr>" . $crlf;
		}

		$output .= "</table>" . $crlf;
	}

	if ($output_to and $output_to != "none") {
		if ($output_to == "both" or $output_to == "screen")
			$return_string = $output;

		if ($output_to == "file" or $output_to == "both") {
			$fp = fopen ( $log_file, "a" );
			fwrite ( $fp, $output );
			fclose ( $fp );
		}
	}

	return $return_string;
}
function json_umlaut($var) {
	if (is_array ( $var )) {
		foreach ( array_keys ( $var ) as $key ) {
			$var [$key] = json_umlaut ( $var [$key] );
		}
		return $var;
	} else {
		/*
		 * $var=preg_replace("/䯢,"&auml;",$var); $var=preg_replace("/į","&Auml;",$var); $var=preg_replace("/�/","&ouml;",$var); $var=preg_replace("/֯","&Ouml;",$var); $var=preg_replace("/�/","&uuml;",$var); $var=preg_replace("/ܯ","&Uuml;",$var); $var=preg_replace("/߯","&szlig;",$var);
		 */
		return htmlentities ( $var );
	}
}
function json_umlaut_reverse($var) {
	if (is_array ( $var )) {
		foreach ( array_keys ( $var ) as $key ) {
			$var [$key] = json_umlaut_reverse ( $var [$key] );
		}
		return $var;
	} else {
		/*
		 * $var=str_replace("&auml;","䢬$var); $var=str_replace("&Auml;","Ģ,$var); $var=str_replace("&ouml;","�",$var); $var=str_replace("&Ouml;","֢,$var); $var=str_replace("&uuml;","�",$var); $var=str_replace("&Uuml;","ܢ,$var); $var=str_replace("&szlig;","ߢ,$var);
		 */
		return html_entity_decode ( $var );
	}
}
function json_no_permission() {
	die ( '{"success":false}' );
}
function html_no_permission() {
	global $lang;
	die ( $lang ["no_permission"] );
}
function ip_check() {
	$user_ip = explode ( ".", $_SERVER ['REMOTE_ADDR'] );

	if ($_SESSION ["ip"]) {
		$ip_array = explode ( ",", $_SESSION ["ip"] );
		for($i = 0; $i < sizeof ( $ip_array ); $i ++) {
			$ip_to_test = explode ( ".", $ip_array [$i] );

			$fail = 0;
			for($j = 0; $j < sizeof ( $ip_to_test ); $j ++) {
				if (! ($ip_to_test [$j] == 0 or $ip_to_test [$j] == $user_ip [$j]))
					$fail = 1;
			}
			if (! $fail)
				return "true";
		}
		return "false";
	} else
		return "true";
}

?>
