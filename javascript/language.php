<?php 
include ("../config/config.php");
include ("../locale/language.php");
?>
Ext.grid.GridFilters.prototype.filtersText = '<?=lang("filter");?>';
Ext.grid.filter.BooleanFilter.prototype.yesText = '<?=lang("yes");?>';
Ext.grid.filter.BooleanFilter.prototype.noText = '<?=lang("no");?>';
Ext.grid.filter.DateFilter.prototype.beforeText = '<?=lang("before_date");?>';
Ext.grid.filter.DateFilter.prototype.afterText = '<?=lang("after_date");?>';
Ext.grid.filter.DateFilter.prototype.onText = '<?=lang("on_date");?>'; 
