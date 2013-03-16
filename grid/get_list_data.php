<?php

include("../config/config.php");
include("../inc/database.php");
include("../inc/functions.php");
include("../locale/language.php");
include("../security.php");
include("inc/functions.php");

$query = build_query($_REQUEST["type"]);

$rs  = $db->query ($query["data"]) or mysql_die();

if($query["cacheID"])
	$total = $query["cache_num_results"];
else {

	$total = $db->query ($query["count"]) or mysql_die();
	if ($query["num_rows"])
		$total = $db->num_rows($total);
	else 
		$total = $db->result($total, 0, 0);
}

echo build_json($rs,$total,$query);

?>
