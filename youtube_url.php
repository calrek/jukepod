<?php
include("config/config.php");
include("locale/language.php");
include("inc/database.php");
include("inc/functions.php");
include("security.php");

if(!$_SESSION["permission"]["youtube"])
	html_no_permission();
?>

<table width=100% height=100%>
<tr><td align="center">
	<object width="425" height="344">
		<param name="movie" value="http://www.youtube.com/v/<?=$_REQUEST["url"];?>&hl=de&fs=1&rel=0&autoplay=1"></param>
		<param name="allowFullScreen" value="false"></param>
		<param name="allowscriptaccess" value="always"></param>
		<embed src="http://www.youtube.com/v/<?=$_REQUEST["url"];?>&hl=de&fs=1&rel=0&autoplay=1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="false" width="425" height="344"></embed>
	</object>
</td></tr></table>
<!--</div>-->
