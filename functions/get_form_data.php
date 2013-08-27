<?php
include "../config/config.php";
include "../locale/language.php";
include "../inc/database.php";
include "../inc/functions.php";

$db = new db ();

switch ($_GET ["tab"]) {
	case "user" :
		$query = "SELECT ID,name,ip,type,access_option FROM user WHERE ID = '" . $_GET ["id"] . "'";
		echo "/* QUERY: $query */";
		$fields_array = array (
				'name',
				'ip',
				'password',
				'password2',
				'type',
				'access_option' 
		);
		break;
}

$field_definition_array = array ();
for($i = 0; $i < sizeof ( $fields_array ); $i ++) {
	$field_definition_array [] = "{name: '" . $fields_array [$i] . "'}";
}

$results_row_array = array ();
$result = $db->query ( $query );
$num_results = $db->num_rows ( $result );
if ($num_results) {
	while ( $row = $db->fetch_array ( $result ) ) {
		$result_field_array = array ();
		$result_field_array ["ID"] = $_GET ["id"];
		for($i = 0; $i < sizeof ( $fields_array ); $i ++) {
			$value = $row [$fields_array [$i]];
			if (is_numeric ( $value ) and ! $value)
				$value = "";
			$result_field_array [$fields_array [$i]] = $value;
		}
		
		switch ($_GET ["tab"]) {
			case "user" :
				$query = "SELECT name,value FROM user_permissions WHERE userID = '" . $row ["ID"] . "'";
				$permissions = $db->query ( $query );
				while ( $permission_row = $db->fetch_array ( $permissions ) ) {
					$result_field_array ["permission_" . $permission_row ["name"]] = true;
					$field_definition_array [] = "{name: 'permission_" . $permission_row ["name"] . "'}";
				}
				break;
		}
		
		$results_row_array [] = $result_field_array;
	}
}
?>
{
	'metaData': {
		totalProperty: 'results',
		root: 'rows',
		id: 'ID',
		fields: [ <?php echo implode( ",", $field_definition_array );?> ]
	 },
	'results': <?php echo $num_results;?>, 'rows':
		<?php echo json_encode( $results_row_array ); ?>
}
