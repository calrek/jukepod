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
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<title>Jukepod</title>
	<link rel="stylesheet" type="text/css" href="extjs/resources/css/ext-all.css" />
	<link rel="stylesheet" type="text/css" href="extjs/resources/css/gtheme.css" />
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<link rel="icon" href="favicon.ico" type="image/x-icon">
	<style type="text/css">
		<?php include("css/icons.css"); ?>
	</style>
	<script type="text/javascript" src="extjs/adapter/ext/ext-base-debug.js" charset="UTF-8"></script>
	<script type="text/javascript" src="extjs/ext-all-debug-w-comments.js" charset="UTF-8"></script>
	<SCRIPT type="text/javascript" src="javascript/Ext.overrides.js"></SCRIPT>
	
	<script type="text/javascript" src="list/lists.php"></script>
	<SCRIPT type="text/javascript" src="javascript/plugins.php"></SCRIPT>
	
	<script type="text/javascript" src="extjs/src/locale/ext-lang-en.js" charset="UTF-8"></script>
	
	<script type="text/javascript">
		SM2_DEFER = true;
		Ext.BLANK_IMAGE_URL = 'extjs/resources/images/default/s.gif';
	</script>
	<script type="text/javascript" src="soundmanager/script/soundmanager2.js"></script>
	<script type="text/javascript">
		
		function onItemSelected(item) {
			if (item == null) {
			} else {
				filter_setting('album',item.guid);
			}
		}
		
		function play() {
			soundManager.togglePause('my_soundID');
		}
		
		function stop() {
			soundManager.stop("my_soundID");
		}
		
		var cp = new Ext.state.CookieProvider({
			path: "/jpod/",
			expires: new Date(new Date().getTime()+(1000*60*60*24*30)), //30 days
			domain: "localhost"
		});
		
		Ext.state.Manager.setProvider(cp);
		
	</script>

	<script type="text/javascript" src="main_js.php"></script>

</head>

<body onLoad="">
	<?php include_once 'start.php';  ?>
</body>
</html>
