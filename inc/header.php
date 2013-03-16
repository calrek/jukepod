<?php
define( "extjs_version", "3.1" );
?>
<link rel="stylesheet" type="text/css" href="extjs/resources/css/ext-all.css" />

<link rel="alternate" href="album_feed.php" type="application/rss+xml" title="" id="gallery" />
<?php
switch ( $_GET["skin"] ) {

case "gray":
	echo '<link rel="stylesheet" type="text/css" href="extjs/resources/css/xtheme-gray.css" />';
	$skin_image_folder = "gray";
	break;
case "black":
	echo '<link rel="stylesheet" type="text/css" href="extjs/resources/css/xtheme-black.css" />';
	$skin_image_folder = "black";
	break;
default:
	$skin_image_folder = "default";
}
?>
<style type="text/css">
	<?php include "css/style.php"; ?>
	<?php include "css/icons.css"; ?>
</style>
<link rel="stylesheet" type="text/css" href="css/youtubeplayer.css" />
<script type="text/javascript" src="extjs/adapter/ext/ext-base.js" charset="UTF-8"></script>
<script type="text/javascript" src="extjs/ext-all.js" charset="UTF-8"></script>
<SCRIPT type="text/javascript" src="javascript/Ext.overrides.js"></SCRIPT>

<script type="text/javascript" src="list/lists.php"></script>
<script type="text/javascript" src="javascript/userform.php"></script>
<SCRIPT type="text/javascript" src="javascript/plugins.php"></SCRIPT>
<SCRIPT type="text/javascript" src="javascript/lib.php"></SCRIPT>
<SCRIPT type="text/javascript" src="javascript/player.php" charset="UTF-8"></SCRIPT>
<script type="text/javascript" src="extjs/src/locale/ext-lang-<? if($_SESSION["lang"]) echo $_SESSION["lang"]; else echo LANGUAGE;?>.js" charset="UTF-8"></script>

<script type="text/javascript">
	SM2_DEFER = true;
	Ext.BLANK_IMAGE_URL = 'extjs/resources/images/default/s.gif';
</script>
<script type="text/javascript" src="soundmanager/script/soundmanager2.js"></script>
<script type="text/javascript">

	function onItemSelected(item) {
			if (item == null) {
					// nothing selected
			} else {
					//cooliris_window.close();
					filter_setting('album',item.guid);
			}
	}

	var cooliris = {
			onEmbedInitialized : function() {
					cooliris.embed.setCallbacks({
							select: onItemSelected
					});
			}
	};

	function play() {
		soundManager.togglePause('my_soundID');
	}

	function stop() {
		soundManager.stop("my_soundID");
	}

	var cp = new Ext.state.CookieProvider();
	Ext.state.Manager.setProvider(cp);
</script>

<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<link rel="icon" href="favicon.ico" type="image/x-icon">
