<link rel="stylesheet" type="text/css" href="extjs/resources/css/ext-all.css" />
<link rel="stylesheet" type="text/css" href="extjs/resources/css/gtheme.css" />
<link rel="stylesheet" type="text/css" href="css/style.css" />
<style type="text/css">
	<?php include("css/icons.css"); ?>
</style>
<script type="text/javascript" src="extjs/adapter/ext/ext-base.js" charset="UTF-8"></script>
<script type="text/javascript" src="extjs/ext-all.js" charset="UTF-8"></script>
<SCRIPT type="text/javascript" src="javascript/Ext.overrides.js"></SCRIPT>

<script type="text/javascript" src="list/lists.php"></script>
<script type="text/javascript" src="javascript/userform.php"></script>
<SCRIPT type="text/javascript" src="javascript/plugins.php"></SCRIPT>
<SCRIPT type="text/javascript" src="javascript/lib.php"></SCRIPT>
<SCRIPT type="text/javascript" src="javascript/player.php" charset="UTF-8"></SCRIPT>

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

	var cp = new Ext.state.CookieProvider();

	Ext.state.Manager.setProvider(cp);
</script>

<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<link rel="icon" href="favicon.ico" type="image/x-icon">
