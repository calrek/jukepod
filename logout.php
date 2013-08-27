<?php 

session_start();
session_destroy();

$_SESSION = array();
setcookie("jukepod", "", time()+24*60*60*365);

header("location: index.php");
?>
