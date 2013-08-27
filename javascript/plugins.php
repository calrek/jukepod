<?php  
include("../config/config.php");
include("../locale/language.php");
include("../inc/database.php");
include("../inc/functions.php");
include("../security.php");

header("Content-Type: application/x-javascript; charset=utf-8"); 
include ("lib.js");
include ("player.js");
include ("userform.js");
include ("Ext.ux.grid.Search.js");
include ("Ext.grid.RowExpander.js");
include ("Ext.ux.grid.RowActions.js");
include ("Ext.ux.grid.ExplorerView.js");
include ("GridViewMenuPlugin.js");
include ("pPageSize.js");
include ("Ext.ux.grid.RateColumn.js");
include ("Ext.ux.dd.GridReorderDropTarget.js");
?>
