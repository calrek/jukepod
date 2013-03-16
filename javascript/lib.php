<?php
include("../config/config.php");
include("../locale/language.php");
header("Content-type:text/javascript; charset=utf-8");
?>

var dont_select_tree_node = false;
var last_selected_album_from_artist = false;
var from_cellclick = false;
var youtube_window = false;
var ytplayer_window = false;
var lyrics_window = false;
var cooliris_window = false;
var user_editor = "";
var setup_editor = "";
var player_window = false;
var cooliris_dont_reload = false;
var queryStore = new Array();
var history_position = 0;

function open_youtube_url(url) {

	playerPanel = new Ext.ux.YoutubePlayer({
		playerId: 'ytplayer',
		border: false,
		bgColor: "#000000",
		cls: 'ext-ux-youtubeplayer'
	});

	playerPanel.on('ready', function(panel, player) {
		panel.cueVideoById(url);
	});

	if (ytplayer_window) ytplayer_window.close();
	ytplayer_window = new Ext.Window({
		title: 'YoutubePlayer',
		layout: 'fit',
		id: 'ytplayer_windowID',
		maximizable: false,
		animCollapse: true,
		hideMode: 'visibility',
		collapsible: false,
		resizable: true,
		items: [playerPanel],
		bbar: new Ext.ux.YoutubePlayer.Control({
			player: playerPanel,
			border: true,
			id: 'ycontrol',
			style: 'border:none;'
		}),
		listeners: {
			close: function(p) {
				ytplayer_window = "";
			}
		},
		height: 400,
		width: 500
	});
	ytplayer_window.show();
}

function open_lyrics_url(hid) {
	Ext.getCmp("lyrics_windowID").load({
		url: "get_lyrics.php",
		params: "hid=" + hid,
		text: "<? lang('loading_video',1);?>"
	});
}

function extras_menu(data, from) {
	return new Ext.menu.Menu({ <?php $entry_array_level0 = array();
		$entry_array_level1 = array();
		if ($_SESSION["permission"]["has_access"]) {
			$entry_array_level0[] = "{
				myid: 'youtube-entry',
				iconCls:'icon-youtube',
				text:'".lang("youtube", 1)."'
				}";
		}
		if ($_SESSION["permission"]["has_access"]) {
			$entry_array_level0[] = "{
				myid: 'lyrics-entry',
				iconCls:'icon-lyrics',
				text:'".lang("lyrics", 1)."'
				}";
		}
		if ($_SESSION["permission"]["has_access"]) {
			$entry_array_level1[] = "{
				myid: 'download-entry',
				iconCls:'icon-download',
				text:'".lang("download", 1)."'
				}";
		}
		if ($_SESSION["permission"]["has_access"]) {
			$entry_array_level1[] = "{
				myid: 'edit-entry',
				iconCls:'icon-cd-edit',
				text:'".lang("edit", 1)."'
				}";
		}
		$entry_level0 = implode(",", $entry_array_level0);
		$entry_level1 = implode(",", $entry_array_level1);
		if ($entry_level0 != ""
		AND $entry_level1 != "") $entries = $entry_level0.",new Ext.menu.Separator(),".$entry_level1;
		elseif($entry_level0 != "")
		$entries = $entry_level0;
		elseif($entry_level1 != "")
		$entries = $entry_level1; ?> items: [ <?= $entries ?> ],
		listeners: {
			itemclick: function(item) {
				var node = item.parentMenu.contextNode;
				switch (item.myid) {

				case 'youtube-entry':
					var title = data.title;
					if (data.artist) title = data.artist + " - " + title;
					if (youtube_window) youtube_window.close();
					youtube_window = new Ext.Window({
						layout: 'fit',
						id: 'youtube_windowID',
						title: title,
						width: 510,
						height: 454,
						bodyStyle: 'padding: 5px;',
						resizable: false,
						closeAction: 'close',
						autoScroll: true,
						autoLoad: {
							url: 'youtube_list.php',
							params: 'title=' + encodeURIComponent(data.title) + "&artist=" + encodeURIComponent(data.artist),
						},
						plain: false
					});
					youtube_window.show();
					break;

				case 'lyrics-entry':
					var title = data.title;
					if (data.artist) title = data.artist + " - " + title;
					if (lyrics_window) lyrics_window.close();
					lyrics_window = new Ext.Window({
						layout: 'fit',
						title: title,
						id: 'lyrics_windowID',
						width: 500,
						bodyStyle: 'padding: 5px;',
						height: 500,
						closeAction: 'close',
						autoScroll: true,
						autoLoad: {
							url: 'get_lyrics.php',
							params: 'action=getSong&songID=' + data.ID + "&title=" + encodeURIComponent(data.title) + "&artist=" + encodeURIComponent(data.artist)
						},
						plain: false
					});
					lyrics_window.show();
					break;

				case 'download-entry':
					window.open("download.php?ID=" + data.ID);
					break;

				case 'edit-entry':
					var tag_editor_form = new Ext.form.FormPanel({

						baseParams: {},
						baseCls: 'x-plain',
						labelWidth: 60,
						id: 'tag_editor_formID',
						url: 'save-form.php',
						defaultType: 'textfield',
						items: [{

							fieldLabel: '<?=lang("filename",1);?>',
							name: 'filename',
							anchor: '100%',
							// anchor width by percentage
							allowBlank: false
						}, {
							fieldLabel: '<?=lang("artist",1);?>',
							name: 'artist',
							anchor: '100%' // anchor width by percentage
						}, {
							fieldLabel: '<?=lang("title",1);?>',
							name: 'title',
							anchor: '100%' // anchor width by percentage
						}, {
							fieldLabel: '<?=lang("album",1);?>',
							name: 'album',
							anchor: '100%' // anchor width by percentage
						}, {
							fieldLabel: '<?=lang("genre",1);?>',
							name: 'genre',
							anchor: '100%' // anchor width by percentage
						}, {
							fieldLabel: '<?=lang("year",1);?>',
							name: 'year',
							width: '50' // anchor width by percentage
						}, {
							fieldLabel: '<?=lang("track_nr",1);?>',
							name: 'track',
							width: '50' // anchor width by percentage
						}]
					});

					var tag_editor = new Ext.Window({
						title: '<?=lang("tag_editor",1);?>',
						width: 500,
						height: 270,
						minWidth: 300,
						minHeight: 270,
						layout: 'fit',
						plain: true,
						bodyStyle: 'padding:5px;',
						buttonAlign: 'center',
						items: tag_editor_form,
						buttons: [{

							iconCls: 'icon-accept',
							text: '<?=lang("save",1);?>',
							handler: function()  {

									tag_editor_form.getForm().baseParams.ID = data.ID;
									tag_editor_form.getForm().baseParams.old_filename = htmlspecialchars_decode(data.filename);
									tag_editor_form.getForm().baseParams.full_path = htmlspecialchars_decode(data.full_path);
									tag_editor_form.getForm().baseParams.artistID = data.artistID;
									tag_editor_form.getForm().baseParams.albumID = data.albumID;
									tag_editor_form.getForm().baseParams.year = data.year;
									tag_editor_form.getForm().baseParams.old_artist = htmlspecialchars_decode(data.artist);
									tag_editor_form.getForm().baseParams.old_album = htmlspecialchars_decode(data.album);
									tag_editor_form.getForm().submit({

										url: 'save_tags.php',
										waitMsg: '<?=lang("saving",1);?>',

										success: function(form, action) {

											if (action.result.msg) Ext.Msg.alert('<?=lang("saving",1);?>', action.result.msg);
											if (from == "mp3_list") mp3_list_obj.getStore().reload();
											else if (from == "play_list") playlist_obj.getStore().reload();
											tag_editor.close();
										},
										failure: function(form, action) {

											switch (action.failureType) {

											case Ext.form.Action.CLIENT_INVALID:
												Ext.Msg.alert('<?=lang("error",1);?>', '<?=lang("invalid_values",1);?>');
												break;
											case Ext.form.Action.CONNECT_FAILURE:
												Ext.Msg.alert('<?=lang("error",1);?>', '<?=lang("error_ajax_communication",1);?>');
												break;
											case Ext.form.Action.SERVER_INVALID:
												Ext.Msg.alert('<?=lang("error",1);?>', action.result.msg);
											}
											tag_editor.close();
										}
									})
							}
						}, {
							iconCls: 'icon-cancel',
							text: '<?=lang("cancel",1);?>',
							handler: function() {
								tag_editor.close();
							}
						}]
					});
					tag_editor.show();
					var form = tag_editor_form.getForm();
					form.findField("filename").setValue(htmlspecialchars_decode(data.filename));
					form.findField("artist").setValue(htmlspecialchars_decode(data.artist));
					form.findField("title").setValue(htmlspecialchars_decode(data.title));
					form.findField("year").setValue(htmlspecialchars_decode(data.year));
					form.findField("album").setValue(htmlspecialchars_decode(data.album));
					form.findField("genre").setValue(htmlspecialchars_decode(data.genre));
					break;
				}
			}
		}
	});
}

function open_cooliris() {
	// Some sample html
	if (!cooliris_window) {
		var feed = escape("album_feed.php?query=" + album_obj.getStore().baseParams.query + "&fields=" + album_obj.getStore().baseParams.fields + "&sort=" + album_obj.getStore().sortInfo.field + "&sort_dir=" + album_obj.getStore().sortInfo.direction);
		var html = ['<div style="text-align: center"><object id="o" ', 'classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"', 'width="<?=COOLIRIS_SIZE_X?>"', 'height="<?=COOLIRIS_SIZE_Y?>">', '<param name="movie" value="http://apps.cooliris.com/embed/cooliris.swf" />', '<param name="allowFullScreen" value="true" />', '<param name="allowScriptAccess" value="always" />', '<param name="flashvars" value="feed=' + feed + '&style=light&showSearch=false&showEmbed=false&showChrome=false&numRows=<?=COOLIRIS_ROWS;?>&icons=img&showReflections=<?=COOLIRIS_REFLECTIONS;?>" />', '<embed type="application/x-shockwave-flash"', 'src="http://apps.cooliris.com/embed/cooliris.swf"', 'flashvars="feed=' + feed + '&style=light&showSearch=false&showEmbed=false&showChrome=false&numRows=<?=COOLIRIS_ROWS;?>&icons=img&showReflections=<?=COOLIRIS_REFLECTIONS;?>"', 'width="<?=COOLIRIS_SIZE_X?>"', 'height="<?=COOLIRIS_SIZE_Y?>"', 'allowFullScreen="true"', 'allowScriptAccess="always">', '</embed>', '</object></div>'];
		cooliris_window = new Ext.Window({
			layout: 'fit',
			title: "<?=lang('album_grid_title',1);?>",
			id: 'cooliris_windowID',
			width: <?= (COOLIRIS_SIZE_X + 25) ?> ,
			bodyStyle: 'padding: 5px;',
			height: <?= COOLIRIS_SIZE_Y + 50 ?> ,
			stateful: false,
			resizeable: false,
			closeAction: 'close',
			autoScroll: true,
			html: html.join(''),
			plain: false,
			listeners: {
				close: function(p) {
					cooliris_window = "";
				}
			}
		});
		cooliris_window.show();
	}
}

function streampub(name, path, server, filename, location, artist, title) {

	FB.ui({

		method: 'stream.publish',
		message: 'What you think?',
		attachment: {
			name: name,
			caption: 'Facebook Podcast Jukebox',
			description: (''),
			href: '<? echo DOMAIN."/"; ?>',
			media: [{
				'type': 'mp3',
				'src': 'http://www.box.com/index.php?rm=box_download_shared_file&shared_name=4bmaplz4yi&file_id=f_751852114&rss=1&file=mp3',
				'artist': artist,
				'title': title
			}]
		},
		action_links: [{
			text: 'Jukepod',
			href: '<? echo DOMAIN."/"; ?>'
		}],
		user_message_prompt: 'What you think'
	}, function(response) {

		if (response && response.post_id) {
			Ext.MessageBox.alert('Status', 'Post was published.');
		} else {
			Ext.MessageBox.alert('Status', 'Error publishing post.');
		}
	});

}

function getURL() {

	var p = [];
	p[0] = id;

	this.runFunction('getFileTinyURL', p, 'Get URL <br><br>Please wait...', 'getURLResp();');
	return 0;
}

function cut_array_to_n_elements(data, n) {
	var result = new Array();
	for (i = 0; i < data.length && i < n; i++) {
		result[i] = data[i];
	}
	return result;
}

function filter_setting(from, value) {
	history_position++;
	queryStore = cut_array_to_n_elements(queryStore, history_position);
	var store = Ext.getCmp("mp3_list").getStore();
	delete(store.baseParams["albumID"]);
	delete(store.baseParams["artistID"]);
	delete(store.baseParams["query"]);
	delete(store.baseParams["fields"]);
	delete(store.baseParams["full_text_search"]);
	delete(store.baseParams["full_path"]);
	if (store.lastOptions && store.lastOptions.params) {
		delete(store.lastOptions.params["albumID"]);
		delete(store.lastOptions.params["artistID"]);
		delete(store.lastOptions.params["query"]);
		delete(store.lastOptions.params["fields"]);
		delete(store.lastOptions.params["full_text_search"]);
		delete(store.lastOptions.params["full_path"]);
	}
	if (store.lastOptions && store.lastOptions.params) store.lastOptions.params[store.paramNames.start] = 0;
	if (from == "album") {
		//cm = Ext.getCmp("mp3_list").getColumnModel();
		if (value) {
			store.baseParams.albumID = value;
			/*if(cooliris_window)
				cooliris.embed.selectItemByGUID(value);*/
		} else store.baseParams.albumID = 0;
	} else store.baseParams.albumID = 0;
	if (from == "artist") {
		if (value) store.baseParams.artistID = value;
		else store.baseParams.artistID = 0;
	} else store.baseParams.artistID = 0;
	if (from == "tree") {
		if (value) store.baseParams.full_path = value;
		else store.baseParams.full_path = "";
	} else {
		store.baseParams.full_path = '';
		dont_select_tree_node = true;
		Ext.getCmp("dir_treeID").getRootNode().select();
		//Ext.getCmp("dir_treeID").getSelectionModel().clearSelections();
	}
	if (from != "fulltext_search") {
		Ext.getCmp("main_search_full_text_fieldID").setValue('');
	}
	if (from != "quick_search" && from != "fulltext_search") {
		store.baseParams.fields = "";
		store.baseParams.full_text_search = "";
		store.baseParams.query = "";
		store.reload();
	}
}

function load_album(ID, obj) {
	if (last_selected_album_from_artist) last_selected_album_from_artist.className = 'album_from_artist_entry';
	obj.className = "album_from_artist_entry_selected";
	last_selected_album_from_artist = obj;
	filter_setting("album", ID);
}

function rand(min, max) {
	var argc = arguments.length;
	if (argc == 0) {
		min = 0;
		max = 2147483647;
	} else if (argc == 1) {
		throw new Error('Warning: rand() expects exactly 2 parameters, 1 given');
	}
	return Math.floor(Math.random() * (max - min + 1)) + min;
}

function album_selection_mouse_in(obj) {
	if (obj.className != "album_from_artist_entry_selected") obj.className = 'album_from_artist_entry_marked';
}

function album_selection_mouse_out(obj) {
	if (obj.className != "album_from_artist_entry_selected") obj.className = 'album_from_artist_entry';
}

function htmlspecialchars_decode(string, quote_style) {
	var histogram = {},
		symbol = '',
		tmp_str = '',
		entity = '';
	tmp_str = string.toString();
	if (false === (histogram = get_html_translation_table('HTML_ENTITIES', quote_style))) {
		return false;
	}
	// &amp; must be the last character when decoding!
	delete(histogram['&']);
	histogram['&'] = '&amp;';
	for (symbol in histogram) {
		entity = histogram[symbol];
		tmp_str = tmp_str.split(entity).join(symbol);
	}
	return tmp_str;
}

function get_html_translation_table(table, quote_style) {
	var entities = {},
		histogram = {},
		decimal = 0,
		symbol = '';
	var constMappingTable = {},
		constMappingQuoteStyle = {};
	var useTable = {},
		useQuoteStyle = {};
	useTable = (table ? table.toUpperCase() : 'HTML_SPECIALCHARS');
	useQuoteStyle = (quote_style ? quote_style.toUpperCase() : 'ENT_COMPAT');
	// Translate arguments
	constMappingTable[0] = 'HTML_SPECIALCHARS';
	constMappingTable[1] = 'HTML_ENTITIES';
	constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
	constMappingQuoteStyle[2] = 'ENT_COMPAT';
	constMappingQuoteStyle[3] = 'ENT_QUOTES';
	// Map numbers to strings for compatibilty with PHP constants
	if (!isNaN(useTable)) {
		useTable = constMappingTable[useTable];
	}
	if (!isNaN(useQuoteStyle)) {
		useQuoteStyle = constMappingQuoteStyle[useQuoteStyle];
	}
	if (useTable == 'HTML_SPECIALCHARS') {
		// ascii decimals for better compatibility
		entities['38'] = '&amp;';
		if (useQuoteStyle != 'ENT_NOQUOTES') {
			entities['34'] = '&quot;';
		}
		if (useQuoteStyle == 'ENT_QUOTES') {
			entities['39'] = '&#039;';
		}
		entities['60'] = '&lt;';
		entities['62'] = '&gt;';
	} else if (useTable == 'HTML_ENTITIES') {
		// ascii decimals for better compatibility
		entities['38'] = '&amp;';
		if (useQuoteStyle != 'ENT_NOQUOTES') {
			entities['34'] = '&quot;';
		}
		if (useQuoteStyle == 'ENT_QUOTES') {
			entities['39'] = '&#039;';
		}
		entities['60'] = '&lt;';
		entities['62'] = '&gt;';
		entities['160'] = '&nbsp;';
		entities['161'] = '&iexcl;';
		entities['162'] = '&cent;';
		entities['163'] = '&pound;';
		entities['164'] = '&curren;';
		entities['165'] = '&yen;';
		entities['166'] = '&brvbar;';
		entities['167'] = '&sect;';
		entities['168'] = '&uml;';
		entities['169'] = '&copy;';
		entities['170'] = '&ordf;';
		entities['171'] = '&laquo;';
		entities['172'] = '&not;';
		entities['173'] = '&shy;';
		entities['174'] = '&reg;';
		entities['175'] = '&macr;';
		entities['176'] = '&deg;';
		entities['177'] = '&plusmn;';
		entities['178'] = '&sup2;';
		entities['179'] = '&sup3;';
		entities['180'] = '&acute;';
		entities['181'] = '&micro;';
		entities['182'] = '&para;';
		entities['183'] = '&middot;';
		entities['184'] = '&cedil;';
		entities['185'] = '&sup1;';
		entities['186'] = '&ordm;';
		entities['187'] = '&raquo;';
		entities['188'] = '&frac14;';
		entities['189'] = '&frac12;';
		entities['190'] = '&frac34;';
		entities['191'] = '&iquest;';
		entities['192'] = '&Agrave;';
		entities['193'] = '&Aacute;';
		entities['194'] = '&Acirc;';
		entities['195'] = '&Atilde;';
		entities['196'] = '&Auml;';
		entities['197'] = '&Aring;';
		entities['198'] = '&AElig;';
		entities['199'] = '&Ccedil;';
		entities['200'] = '&Egrave;';
		entities['201'] = '&Eacute;';
		entities['202'] = '&Ecirc;';
		entities['203'] = '&Euml;';
		entities['204'] = '&Igrave;';
		entities['205'] = '&Iacute;';
		entities['206'] = '&Icirc;';
		entities['207'] = '&Iuml;';
		entities['208'] = '&ETH;';
		entities['209'] = '&Ntilde;';
		entities['210'] = '&Ograve;';
		entities['211'] = '&Oacute;';
		entities['212'] = '&Ocirc;';
		entities['213'] = '&Otilde;';
		entities['214'] = '&Ouml;';
		entities['215'] = '&times;';
		entities['216'] = '&Oslash;';
		entities['217'] = '&Ugrave;';
		entities['218'] = '&Uacute;';
		entities['219'] = '&Ucirc;';
		entities['220'] = '&Uuml;';
		entities['221'] = '&Yacute;';
		entities['222'] = '&THORN;';
		entities['223'] = '&szlig;';
		entities['224'] = '&agrave;';
		entities['225'] = '&aacute;';
		entities['226'] = '&acirc;';
		entities['227'] = '&atilde;';
		entities['228'] = '&auml;';
		entities['229'] = '&aring;';
		entities['230'] = '&aelig;';
		entities['231'] = '&ccedil;';
		entities['232'] = '&egrave;';
		entities['233'] = '&eacute;';
		entities['234'] = '&ecirc;';
		entities['235'] = '&euml;';
		entities['236'] = '&igrave;';
		entities['237'] = '&iacute;';
		entities['238'] = '&icirc;';
		entities['239'] = '&iuml;';
		entities['240'] = '&eth;';
		entities['241'] = '&ntilde;';
		entities['242'] = '&ograve;';
		entities['243'] = '&oacute;';
		entities['244'] = '&ocirc;';
		entities['245'] = '&otilde;';
		entities['246'] = '&ouml;';
		entities['247'] = '&divide;';
		entities['248'] = '&oslash;';
		entities['249'] = '&ugrave;';
		entities['250'] = '&uacute;';
		entities['251'] = '&ucirc;';
		entities['252'] = '&uuml;';
		entities['253'] = '&yacute;';
		entities['254'] = '&thorn;';
		entities['255'] = '&yuml;';
	} else {
		throw Error("Table: " + useTable + ' not supported');
		return false;
	}
	// ascii decimals to real symbols
	for (decimal in entities) {
		symbol = String.fromCharCode(decimal);
		histogram[symbol] = entities[decimal];
	}
	return histogram;
}

function renderDuration(value) {
	var minuten = Math.floor(value / 60);
	var sekunden = value - minuten * 60;
	if (sekunden < 10) sekunden = "0" + sekunden;
	return String.format('{0}:{1}', minuten, sekunden);
}

function number_format(value, decimals, dec_point, thousands_sep) {
	decimals = Math.abs(decimals) + 1 ? decimals : 2;
	dec_point = dec_point || '.';
	thousands_sep = thousands_sep || ',';
	var matches = /(-)?(\d+)(\.\d+)?/.exec((isNaN(value) ? 0 : value) + ''); // returns matches[1] as sign, matches[2] as numbers and matches[3] as decimals
	var remainder = matches[2].length > 3 ? matches[2].length % 3 : 0;
	return (matches[1] ? matches[1] : '') + (remainder ? matches[2].substr(0, remainder) + thousands_sep : '') + matches[2].substr(remainder).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep) + (decimals ? dec_point + (+matches[3] || 0).toFixed(decimals).substr(2) : '');
}

function expand_nodes(step, path_array, node) {
	if (step <= (path_array.length - 1)) {
		if (path_array[step] == "") var new_node = node.findChild("my_root", 1);
		else var new_node = node.findChild("name", path_array[step]);
		if (new_node) {
			new_node.expand(false, true, function() {
				step = step + 1;
				if (step <= (path_array.length - 1)) expand_nodes(step, path_array, new_node);
				else {
					dont_select_tree_node = true;
					new_node.select();
				}
			});
		}
	}
}

function open_path(path) {
	var store = Ext.getCmp("mp3_list").getStore();
	if (store.lastOptions && store.lastOptions.params) store.lastOptions.params[store.paramNames.start] = 0;
	if (path) store.baseParams.full_path = path;
	else store.baseParams.full_path = "";
	store.baseParams.albumID = 0;
	store.baseParams.artistID = 0;
	Ext.getCmp("main_search_full_text_fieldID").setValue('');
	store.baseParams.fields = "";
	store.baseParams.query = "";
	store.baseParams.full_text_search = "";
	store.reload({
		callback: function() {
			path = "/" + path;
			var path_array = path.split("/");
			var current_node = Ext.getCmp("dir_treeID").getRootNode();
			expand_nodes(0, path_array, current_node);
		}
	});
}

function addslashes(str) {
	return (str + '').replace(/([\\"'])/g, "\\$1").replace(/\0/g, "\\0");
}

function GridRenderDuration(value, p, record) {
	return renderDuration(value);
}

function renderFilesize(value, p, record) {
	return number_format(value / 1024, 0, ',', '.') + " KB";
}

function renderBitrate(value, p, record) {
	return number_format(value / 1000, 0, ',', '.') + " kbps";
}

function renderNumber(value, p, record) {
	return number_format(value, 0, ',', '.');
}

function renderLink(value, p, record) {
	return String.format('<a target=_blank href="download.php?ID={0}" target=_blank>{1}</a>', record.get('ID'), value);
}

function renderArtist(value, p, record) {
	if (record.get('artistID') != 0) return String.format('<a href="javascript: void(0)" onClick="filter_setting(\'artist\',{0});">{1}</a>', record.get('artistID'), value);
	else return value;
}

function renderAlbum(value, p, record) {
	return String.format('<a href="javascript: void(0)" onClick="filter_setting(\'album\',{0});">{1}</a>', record.get('albumID'), value);
}

function renderPath(value, p, record) {
	return String.format('<a href="javascript: void(0)" onClick="open_path(\'{0}\');">{1}</a>', addslashes(record.get('full_path')), value);
}

function enable_disable_buttons(form_name, enable) {
	if (enable) {
		Ext.getCmp(form_name + 'new_buttonID').enable();
		if (Ext.getCmp(form_name + 'navigationID').getValue() && Ext.getCmp(form_name + 'formID').getForm().reader.jsonData.rows[0].type != "guest" && Ext.getCmp(form_name + 'formID').getForm().reader.jsonData.rows[0].type != "admin") Ext.getCmp(form_name + 'delete_buttonID').enable();
		else Ext.getCmp(form_name + 'delete_buttonID').disable();
		Ext.getCmp(form_name + 'save_buttonID').enable();
	} else {
		Ext.getCmp(form_name + 'new_buttonID').disable();
		Ext.getCmp(form_name + 'delete_buttonID').disable();
		Ext.getCmp(form_name + 'save_buttonID').disable();
	}
}

function open_user_form(userID) {
	if (!user_editor) {
		user_editor = new Ext.Window({
			title: 'User-Editor',
			width: 670,
			height: 370,
			resizable: false,
			layout: 'fit',
			border: false,
			stateful: false,
			plain: true,
			bodyStyle: 'padding:5px;',
			buttonAlign: 'center',
			items: user_form(userID),
			listeners: {
				close: function(p) {
					user_editor = "";
				}
			}
		});
		user_editor.show();
	}
}

function open_setup_form() {
	if (!setup_editor) {
		setup_editor = new Ext.Window({
			title: 'Setup-Editor',
			width: 670,
			height: 370,
			resizable: false,
			layout: 'fit',
			border: false,
			stateful: false,
			plain: true,
			bodyStyle: 'padding:5px;',
			buttonAlign: 'center',
			items: setup_form(),
			listeners: {
				close: function(p) {
					setup_editor = "";
				}
			}
		});
		setup_editor.show();
	}
}

function my_about() {
	Ext.MessageBox.show({
		title: '<?=lang("about_title",1);?>',
		msg: '<?=lang("about_text",1);?>',
		buttons: Ext.MessageBox.OK
	});
}
/**
 *
 *  UTF-8 data encode / decode
 *  http://www.webtoolkit.info/
 *
 **/

function cp1252_to_unicode(c) {
	if (c < 160) {
		if (c == 128) c = 8364;
		else if (c == 129) c = 160; // (Rayo:) #129 using no relevant sign, thus, mapped to the saved-space #160
		else if (c == 130) c = 8218;
		else if (c == 131) c = 402;
		else if (c == 132) c = 8222;
		else if (c == 133) c = 8230;
		else if (c == 134) c = 8224;
		else if (c == 135) c = 8225;
		else if (c == 136) c = 710;
		else if (c == 137) c = 8240;
		else if (c == 138) c = 352;
		else if (c == 139) c = 8249;
		else if (c == 140) c = 338;
		else if (c == 141) c = 160; // (Rayo:) #129 using no relevant sign, thus, mapped to the saved-space #160
		else if (c == 142) c = 381;
		else if (c == 143) c = 160; // (Rayo:) #129 using no relevant sign, thus, mapped to the saved-space #160
		else if (c == 144) c = 160; // (Rayo:) #129 using no relevant sign, thus, mapped to the saved-space #160
		else if (c == 145) c = 8216;
		else if (c == 146) c = 8217;
		else if (c == 147) c = 8220;
		else if (c == 148) c = 8221;
		else if (c == 149) c = 8226;
		else if (c == 150) c = 8211;
		else if (c == 151) c = 8212;
		else if (c == 152) c = 732;
		else if (c == 153) c = 8482;
		else if (c == 154) c = 353;
		else if (c == 155) c = 8250;
		else if (c == 156) c = 339;
		else if (c == 157) c = 160; // (Rayo:) #129 using no relevant sign, thus, mapped to the saved-space #160
		else if (c == 158) c = 382;
		else if (c == 159) c = 376;
	}
	//if
	return c
}
var Utf8 = {
	// public method for url encoding
	encode: function(string) {
		string = string.replace(/\r\n/g, "\n");
		var utftext = "";
		for (var n = 0; n < string.length; n++) {
			var c = string.charCodeAt(n);
			if (c < 128) {
				utftext += String.fromCharCode(c);
			} else if ((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode(cp1252_to_unicode((c >> 6) | 192));
				utftext += String.fromCharCode(cp1252_to_unicode((c & 63) | 128));
			} else {
				//console.log(String.fromCharCode((c >> 12) | 224) + ",",String.fromCharCode(((c >> 6) & 63) | 128) + "," + String.fromCharCode((c & 63) | 128));
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
		}
		return utftext;
	},
	// public method for url decoding
	decode: function(utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;
		while (i < utftext.length) {
			c = utftext.charCodeAt(i);
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			} else if ((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i + 1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			} else {
				c2 = utftext.charCodeAt(i + 1);
				c3 = utftext.charCodeAt(i + 2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
		}
		return string;
	}
}

function in_array(needle, stack) {
	for (var i = 0; i < stack.length; i++) {
		if (stack[i] == needle) return true;
	}
	return false;
}

function undock_player()
{
	var playing_info = document.getElementById("playing_info_content");
	var parent_node = playing_info.parentNode;
	if(!player_window)
	{
		player_window = new Ext.Window({
			layout: 'fit',
			title: "MyWebJukebox-Player",
			width: 412,
			minWidth: 412,
			height: 193,
			minHeight: 193,
			resizable: false,
			stateful: false,
			bodyStyle: 'background: white',
			closeAction:'close',
			autoScroll: true,
			collapsible: true,
			html: "<div id='window_playing_info'></div>",
			plain: false,
			listeners:
			{
				resize: function()
				{
					//soundManager.last_position = 0;
				}
				,
				beforeclose: function(p)
				{
					Ext.getCmp("left_northID").show();
					if(!Ext.getCmp("left_pangelID").isVisible())
					{
						Ext.getCmp("left_pangelID").expand(false);
					}
					var playing_info = document.getElementById("playing_info_content");
					var parent_node = playing_info.parentNode;
					var removed_node = parent_node.removeChild(playing_info);
					document.getElementById("playing_info").appendChild(removed_node);
					player_window = false;
					player_obj.undock_button.show();
					var parent = document.getElementById("playing_info_content");
					var child = document.getElementById("info_cover_row");
					var song_info = document.getElementById("song_info");
					parent.insertBefore(song_info,child);
					cp.set("player_window_show",0);
					Ext.getCmp("left_pangelID").doLayout();
				}
			}
			});
		player_window.show();
		if(player_obj.undock_button)
			player_obj.undock_button.hide();
		var parent = document.getElementById("playing_info_content");
		var child = document.getElementById("song_info");
		var info_cover_row = document.getElementById("info_cover_row");
		parent.insertBefore(info_cover_row,child);
		cp.set("player_window_show",1);
		Ext.getCmp("left_northID").hide();
		Ext.getCmp("left_pangelID").doLayout();
		var removed_node = parent_node.removeChild(playing_info);
		document.getElementById("window_playing_info").appendChild(removed_node);
	}
}

function clear_cache() {
	Ext.Msg.show({
		title : '<?=lang("warning",1);?>',
		msg : '<?=lang("clear_cache_confirm",1);?>',
		buttons : Ext.Msg.YESNO,
		buttonText : Ext.MessageBox.buttonText.yes = '<?=lang("yes",1);?>',
		buttonText : Ext.MessageBox.buttonText.no = '<?=lang("no",1);?>',
		icon : Ext.MessageBox.QUESTION,
		fn : function (btn) {
			if (btn == "yes") {
					Ext.Ajax.request({
						url : 'clear_cache.php',
						params : {},
						waitMsg : '<?=lang("deleting",1);?>',
						success : function (form, action) {
							Ext.Msg.show({
								title : '<?=lang("delete",1);?>',
								msg : '<?=lang("delete_succesfull",1);?>',
								minWidth : 200,
								modal : true,
								icon : Ext.Msg.INFO,
								buttons : Ext.Msg.OK
							});
						},
						failure : function (form, action) {
							Ext.Msg.show({
								title : '<?=lang("error",1);?>',
								msg : '<?=lang("error_clearing_cache",1);?>',
								minWidth : 200,
								modal : true,
								icon : Ext.Msg.ERROR,
								buttons : Ext.Msg.OK
							});
						}
					})
			}
		}
	})
}

function encode64(input) {

	var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	input = escape(input);
	var output = "";
	var chr1, chr2, chr3 = "";
	var enc1, enc2, enc3, enc4 = "";
	var i = 0;

	do {
		chr1 = input.charCodeAt(i++);
		chr2 = input.charCodeAt(i++);
		chr3 = input.charCodeAt(i++);

		enc1 = chr1 >> 2;
		enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
		enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
		enc4 = chr3 & 63;

		if (isNaN(chr2)) {
			enc3 = enc4 = 64;
		} else if (isNaN(chr3)) {
			enc4 = 64;
		}

		output = output + keyStr.charAt(enc1) + keyStr.charAt(enc2) + keyStr.charAt(enc3) + keyStr.charAt(enc4);
		chr1 = chr2 = chr3 = "";
		enc1 = enc2 = enc3 = enc4 = "";
	}
	while (i < input.length);

	return output;
}

function decode64(input) {

	var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	var output = '';
	var chr1, chr2, chr3;
	var enc1, enc2, enc3, enc4;
	var i = 0;

	// remove all characters that are not A-Z, a-z, 0-9, +, /, or =
	input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
	while (i < input.length) {
		enc1 = keyStr.indexOf(input.charAt(i++));
		enc2 = keyStr.indexOf(input.charAt(i++));
		enc3 = keyStr.indexOf(input.charAt(i++));
		enc4 = keyStr.indexOf(input.charAt(i++));

		chr1 = (enc1 << 2) | (enc2 >> 4);
		chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
		chr3 = ((enc3 & 3) << 6) | enc4;

		output += (String.fromCharCode(chr1));

		if (enc3 != 64) {
			output += (String.fromCharCode(chr2));
		}
		if (enc4 != 64) {
			output += (String.fromCharCode(chr3));
		}
	}
	return output;
}