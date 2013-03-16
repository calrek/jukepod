<?php 
include ("../config/config.php");
include ("../locale/language.php");
?>
/*
 * Ext JS Library 2.2.1
 * Copyright(c) 2006-2009, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */

Ext.onReady(function(){

    pb_album = new Ext.ProgressBar({
        id:'pb_albumID',
        width:300,
				cls:'left-align',
        renderTo:'pb_processing_album_div'
    });

	document.getElementById("iframeID").src="album.php?in_frame=1&js_output=1&id=" + album_id;
});

function all_done()
{
Ext.MessageBox.show({
	 title: '<?=lang("scanning_done_title",1);?>',
	 msg: '<?=lang("scanning_done_text",1);?>',
	 buttons: Ext.MessageBox.OK,
	 icon: Ext.MessageBox.INFO
});				
}
