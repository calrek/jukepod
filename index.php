<?php
header("Content-type:text/html; charset=utf-8");

define("SCRIPT","main");

include("config/config.php");
include("locale/language.php");
include("inc/database.php");
include("inc/functions.php");

include("security.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Jukepod</title>
<?php include ("inc/header.php"); ?>
<script type="text/javascript" src="main_js.php"></script>
</head>

<?php
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : "jp_home.php";
include_once "jp_template.php";
?>