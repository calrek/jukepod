<?php
session_start();
session_destroy();
$_SESSION = array();
setcookie("mywebjukebox", "", time()+24*60*60*365);

header("location: index.php");
?>
