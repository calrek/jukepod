<?php
include("config/config.php");
include("locale/language.php");
include("inc/database.php");
include("inc/functions.php");
include("security.php");
header("Content-Type: application/x-javascript; charset=utf-8");

if(0) { ?>
<script type="text/javascript">
<?php }
?>


Ext.onReady(function() {

	Ext.QuickTips.init();

	Ext.SliderTip = Ext.extend(Ext.Tip, {
		minWidth: 10,
		offsets: [0, -10],
		init: function(slider) {
			slider.on('dragstart', this.onSlide, this);
			slider.on('drag', this.onSlide, this);
			slider.on('dragend', this.hide, this);
			slider.on('destroy', this.destroy, this);
		},

		onSlide: function(slider, e, thumb) {
			this.show();
			this.body.update(this.getText(slider));
			this.doAutoWidth();
			this.el.alignTo(thumb.el, 'b-t?', this.offsets);
		},

		getText: function(slider) {
			return slider.getValue();
		}
	});

	var player_bar_tip = new Ext.SliderTip({
		getText: function(slider) {
			return String.format('<b>{0}</b>', renderDuration(Math.round(player_obj.duration * slider.getValue() / 100)));
		}
	});

	player_bar = new Ext.Slider({
		renderTo: 'player',
		id: 'player_bar',
		minValue: 0,
		maxValue: 100,
		value: 0,
		width: 180,
		disabled: true,
		plugins: player_bar_tip,
		listeners: {
			dragstart: function(s, e) {
				player_obj.stop_updating = true;
			},
			dragend: function(s, e) {
				player_obj.seek_percent(s.getValue());
			},
			changecomplete: function(s, v) {
				player_obj.seek_percent(v);
			},
			render: function(s) {
				var parent = document.getElementById("player_bar").firstChild.firstChild;
				var child = document.getElementById("player_bar").firstChild.firstChild.firstChild;
				var loadingbar = document.getElementById("loadingbar");
				parent.insertBefore(loadingbar, child);
			}
		}
	});

	var volume_tip = new Ext.SliderTip({
		getText: function(slider) {
			return String.format('<b>{0}% Volume</b>', slider.getValue());
		}
	});

	player_volume_bar = new Ext.Slider({
		renderTo: 'player_volume',
		minValue: 0,
		maxValue: 100,
		value: 100,
		width: 50,
		vertical: false,
		plugins: volume_tip,
		listeners: {
			change: function(s, v) {
				player_obj.set_volume(v);
			}
		}
	});

	Ext.get('player_bar').applyStyles('cursor:pointer');

	var dir_tree = new Ext.tree.TreePanel({
		useArrows: true,
		title: 'Folders',
		autoScroll: true,
		iconCls: 'icon-folder',
		id: 'dir_treeID',
		animate: true,
		border: false,
		enableDD: false,
		containerScroll: true,
		trackMouseOver: false,
		listeners: {
			'render': function(tp) {
				tp.getSelectionModel().on('selectionchange', function(tree, node) {
					if(!dont_select_tree_node) {
						filter_setting("tree", node.attributes.full_path);
					} else dont_select_tree_node = false;
				})
			}
		},
		dataUrl: 'functions/get_tree.php',
		rootVisible: false,
		layout: 'fit',
		root: {
			text: 'Folders',
			draggable: false,
			id: -1
		}
	});

	player_obj = new player();
	mp3_list_obj = mp3_list();
	play_list_obj = play_list();
	album_list_obj = album_list();
	artist_obj = artist_list();

	var filter_panel = new Ext.Panel({
		region: 'west',
		id: 'filter_panelID',
		header: true,
		title: 'playlist',
		iconCls: 'icon-filter',
		animFloat: false,
		layout: 'accordion',
		items: [play_list_obj, dir_tree, artist_obj,album_list_obj]
	});

	var filter_panelplayer = new Ext.Panel({
		id: 'player_panelID',
		header: true,
		title: 'player',
		height: 225,
		items: [playing_info]

	});

	var viewport = new Ext.Viewport({
		layout : 'border',
		frame:false,
		items : [
			{
				region: 'north',
				height : 75,
				layout : 'vbox',
				layoutConfig: {
					align : 'stretch'
				},
				defaults: {
					frame:true,
					margins : '5 5 0 5',
					flex:1
				},
				items : [{
					html:'<div class="mp3_title"><div class="about"><?php echo USERNAME;?>, <img src="img/silk/icons/user.png" onClick="open_user_form(<?php echo USERID;?>)"> <a href="logout.php"/>Logout</a></div></div>',
					header: true
				}]

			},
			{
				region : 'west',
				split : true,
				width : 400,
				layout : 'vbox',
				layoutConfig: {
					align : 'stretch'
				},
				defaults: {
					frame:true,
					margins : '0 0 5 0',
					flex:1,
				},
				margins : '5 0 5 5',
				items : [filter_panel]
			},
			new Ext.Panel({
				id: 'center_pangelID',
				layout:'border',
				region:'center',
				split: true,
				items:[
					{
						region:'north',
						id:'center_northID',
						layout: 'fit',
						frame:true,
						collapsible: true,
						height: 250,
						margins : '5 5 0 0',
						items: [filter_panelplayer]
					},
					{
						region:'center',
						id:'center_regionID',
						layout: 'fit',
						margins : '5 5 10 0',
						items: [mp3_list_obj]
					},
					{
						region:'south',
						hidden: true,
						border: false,
						id:'center_southID',
						layout: 'fit',
						items: []
					}
				]
			})
		]
	});

	player_obj.prev_button = new Ext.Button({
		iconCls: 'icon-previous',
		tooltip: 'Back',
		id: "player_previous_buttonID",
		handler: function() {
			player_obj.prev_next(-1);
		}
	}).render("control"); // where you want to render
	player_obj.stop_button = new Ext.Button({
		iconCls: 'icon-stop',
		tooltip: 'Stop',
		id: "player_stop_buttonID",
		handler: function() {
			player_obj.stop_playing();
		}
	}).render("control"); // where you want to render
	player_obj.pause_button = new Ext.Button({
		iconCls: 'icon-play',
		tooltip: 'Pause',
		id: "player_pause_buttonID",
		handler: function() {
			player_obj.pause_playlist();
		}
	}).render("control"); // where you want to render
	player_obj.next_button = new Ext.Button({
		iconCls: 'icon-next',
		tooltip: 'Next',
		id: "player_next_buttonID",
		handler: function() {
			player_obj.prev_next(1);
		}
	}).render("control"); // where you want to render
	player_obj.shuffle_button = new Ext.Button({
		iconCls: 'icon-shuffle',
		enableToggle: true,
		tooltip: 'Shuffle',
		id: 'player_shuffle_buttonID',
		handler: function(btn) {
			if(btn.pressed) {
				player_obj.list_random = true;
				player_obj.played_songs = new Array();
			} else {
				player_obj.list_random = false;
				player_obj.played_songs = new Array();
			}
		}
	}).render("player_options"); // where you want to render
	player_obj.extras_button = new Ext.Button({
		iconCls: 'icon-extras',
		tooltip: 'Extras',
		disabled: true,
		id: 'player_extras_buttonID'
	}).render("player_options"); // where you want to render
	player_obj.extras_button.addListener({
		'click': {
			fn: function(btn) {
				var menu = extras_menu(player_obj.current_data, "player");
				menu.show(document.getElementById("player_extras_buttonID"));
			}
		}
	});

	if(cp.get("player_window_show", 0)) undock_player();

	player_obj.set_time_display_mode();

	dir_tree.getRootNode().expand(false);

	soundManager = new SoundManager();
	soundManager.onload = function() {
		player_obj.inited = 1;
	};
	soundManager.url = 'soundmanager/swf/'; // directory where SM2 .SWFs live
	soundManager.debugMode = false;
	soundManager.nullURL = 'soundmanager/demo/jsAMP-preview/data/null.mp3';
	player_obj.last_position = 0;
	soundManager.waitForWindowLoad = true;
	soundManager.consoleOnly = true;

})
<?php if(0) { ?></script><?php }