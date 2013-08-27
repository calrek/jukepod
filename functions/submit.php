<?php
include "../config/config.php";
include "../locale/language.php";
include "../inc/database.php";
include "../inc/functions.php";
include "../security.php";
function is_unique($tab, $field) {
	global $db;
	
	$query = "SELECT ID FROM " . $tab . " WHERE " . $field . " = '" . addslashes ( $_POST [$field] ) . "'";
	if ($_POST ["ID"])
		$query .= " AND ID != '" . $_POST ["ID"] . "'";
	
	$result = $db->query ( $query );
	if ($db->num_rows ( $result ))
		return false;
	else
		return true;
}

if (! $_POST ["tab"])
	$tab = $_POST ["submit_type"];
else
	$tab = $_POST ["tab"];

$error_array = array ();
$result = array ();
switch ($_POST ["action"]) {
	case "save" :
		$field_array = array ();
		switch ($_POST ["submit_type"]) {
			case "user" :
				if (! $_SESSION ["permission"] ["useradmin"])
					json_no_permission ();
				
				$query = "SELECT ID FROM user WHERE type = 'admin'";
				$temp = $db->query ( $query );
				$adminID = $db->result ( $temp, 0, 0 );
				
				$query = "SELECT ID FROM user WHERE type = 'guest'";
				$temp = $db->query ( $query );
				$guestID = $db->result ( $temp, 0, 0 );
				
				// error check
				if ($_POST ["ID"] != $guestID and $_POST ["ID"] != $adminID) {
					if (! is_unique ( $tab, "name" ))
						$result ["errors"] ["name"] = $lang ["username_already_exists"];
					$field_array ["name"] = array ();
				}
				
				if (($_POST ["password"] or $_POST ["password2"]) and $_POST ["password"] != $_POST ["password2"])
					$result ["errors"] ["password"] = $lang ["passwords_dont_match"];
				elseif ((! $_POST ["password"] or ! $_POST ["password2"]) and ! $_POST ["ID"])
					$result ["errors"] ["password"] = $lang ["password_missing"];
				
				$field_array ["password"] = array (
						"type" => "password" 
				);
				$field_array ["ip"] = array ();
				$field_array ["access_option"] = array ();
				break;
		}
		
		if (sizeof ( $result ["errors"] ))
			$result ["success"] = false;
		else {
			reset ( $field_array );
			for($i = 0; $i < sizeof ( $field_array ); $i ++) {
				$value = $_POST [key ( $field_array )];
				if (($field_array [key ( $field_array )] ["type"] == "password" and $value) or $field_array [key ( $field_array )] ["type"] != "password") {
					if ($field_array [key ( $field_array )] ["float"]) {
						$value = str_replace ( ",", ".", $value );
					}
					
					if ($field_array [key ( $field_array )] ["p2m_date"]) {
						$date_array = explode ( ".", $value );
						$value = $date_array [2] . "-" . $date_array [1] . "-" . $date_array [0];
					}
					
					if ($field_array [key ( $field_array )] ["type"] == "password") {
						$value = md5 ( $value );
					}
					
					$query_fields [] = key ( $field_array );
					$query_values [] = "'" . addslashes ( $value ) . "'";
					$value_array [key ( $field_array )] = $value;
					$query_update [] = key ( $field_array ) . " = '" . addslashes ( $value ) . "'";
				}
				next ( $field_array );
			}
			
			if ($_POST ["ID"]) {
				// update
				$query = "UPDATE " . $tab . " SET " . implode ( ", ", $query_update ) . " WHERE ID = " . $_POST ["ID"];
				echo "/* " . $query . " */\n";
				$db->query ( $query );
				// $affected_rows = formgen_sql::affected_rows();
				$affected_rows = 1;
				$result ["id"] = $_POST ["ID"];
			} else {
				// insert
				$query = "INSERT INTO " . $tab . " (" . implode ( ", ", $query_fields ) . ") VALUES (" . implode ( ", ", $query_values ) . ")";
				echo "/* " . $query . " */\n";
				$db->query ( $query );
				$affected_rows = $db->affected_rows ();
				$result ["id"] = $db->insert_id ();
			}
			
			// zusätzliche Felder speichern
			switch ($_POST ["submit_type"]) {
				case "user" :
					$prefix = "permission_";
					
					$query = "DELETE FROM user_permissions WHERE userID = " . $result ["id"] . " AND (userID != " . $adminID . " OR (name != 'has_access' AND name != 'useradmin'))";
					$db->query ( $query );
					
					reset ( $_POST );
					for($i = 0; $i < sizeof ( $_POST ); $i ++) {
						$key = key ( $_POST );
						if (substr ( $key, 0, strlen ( $prefix ) ) == $prefix) {
							$query = "INSERT INTO user_permissions (name,value,userID) VALUES ('" . substr ( $key, strlen ( $prefix ) ) . "',1," . $result ["id"] . ")";
							echo "/* " . $query . " */\n";
							$db->query ( $query );
						}
						next ( $_POST );
					}
					break;
			}
			
			if ($affected_rows)
				$result ["success"] = true;
			else
				$result ["success"] = false;
		}
		break;
	case "delete" :
		$query = "DELETE FROM " . $tab . " WHERE ID = " . $_POST ["ID"];
		$db->query ( $query );
		$affected_rows = $db->affected_rows ();
		
		if ($affected_rows)
			$result ["success"] = true;
		else
			$result ["success"] = false;
		break;
}
echo "/* QUERY: " . $query . " */\n";
echo json_encode ( $result );
?>
