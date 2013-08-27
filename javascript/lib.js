var dont_select_tree_node = false;
var last_selected_album_from_artist = false;
var from_cellclick = false;
var youtube_window = false;
var lyrics_window = false;
var user_editor = "";
var setup_editor = "";
var player_window = false;
var queryStore = new Array();
var history_position = 0;

function extras_menu(data, from) {
	return new Ext.menu.Menu({
		items : [{
					myid : 'youtube-entry',
					iconCls : 'icon-youtube',
					text : 'Youtube'
				}, {
					myid : 'lyrics-entry',
					iconCls : 'icon-lyrics',
					text : 'Lyrics'
				}, {
					myid : 'download-entry',
					iconCls : 'icon-download',
					text : 'Download'
				}],
		listeners : {
			itemclick : function(item) {
				var node = item.parentMenu.contextNode;
				switch (item.myid) {
					case 'youtube-entry' :
						var title = data.title;
						if (data.artist)
							title = data.artist + " - " + title;
						if (youtube_window)
							youtube_window.close();
						youtube_window = new Ext.Window({
							layout : 'fit',
							tbar : [{
								iconCls : 'icon-left',
								id : 'youtube_back',
								disabled : 'true',
								text : 'Back',
								handler : function() {
									Ext.getCmp("youtube_forward").enable();
									Ext.getCmp("youtube_windowID").body
											.update(youtube_cache_page1);
									Ext.getCmp("youtube_back").disable();
								}
							}, {
								iconCls : 'icon-right',
								id : 'youtube_forward',
								disabled : 'true',
								text : 'Forward',
								handler : function() {
									Ext.getCmp("youtube_back").enable();
									Ext.getCmp("youtube_windowID").body
											.update(youtube_cache_page2);
									Ext.getCmp("youtube_forward").disable();
								}
							}],
							id : 'youtube_windowID',
							title : title,
							width : 510,
							height : 454,
							bodyStyle : 'padding: 5px;',
							resizable : false,
							closeAction : 'close',
							autoScroll : true,
							autoLoad : {
								url : 'functions/youtube_list.php',
								params : 'title='
										+ encodeURIComponent(data.title)
										+ "&artist="
										+ encodeURIComponent(data.artist),
								callback : function(el, success, response,
										options) {
									youtube_cache_page1 = response.responseText;
								}
							},
							plain : false
						});
						youtube_window.show();
						break;
					case 'lyrics-entry' :
						var title = data.title;
						if (data.artist)
							title = data.artist + " - " + title;
						if (lyrics_window)
							lyrics_window.close();
						lyrics_window = new Ext.Window({
									layout : 'fit',
									title : title,
									id : 'lyrics_windowID',
									width : 500,
									bodyStyle : 'padding: 5px;',
									height : 500,
									closeAction : 'close',
									autoScroll : true,
									autoLoad : {
										url : 'functions/get_lyrics.php',
										params : 'action=getSong&title='
												+ encodeURIComponent(data.title)
												+ "&artist="
												+ encodeURIComponent(data.artist)
									},
									plain : false
								});
						lyrics_window.show();
						break;
					case 'download-entry' :
						window.open("functions/download.php?ID=" + data.ID);
						break;
				}
			}
		}
	});
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
	delete(store.baseParams["letter_filter"]);
	delete(store.baseParams["query"]);
	delete(store.baseParams["fields"]);
	delete(store.baseParams["full_text_search"]);
	delete(store.baseParams["full_path"]);

	if (store.lastOptions && store.lastOptions.params) {
		delete(store.lastOptions.params["albumID"]);
		delete(store.lastOptions.params["artistID"]);
		delete(store.lastOptions.params["letter_filter"]);
		delete(store.lastOptions.params["query"]);
		delete(store.lastOptions.params["fields"]);
		delete(store.lastOptions.params["full_text_search"]);
		delete(store.lastOptions.params["full_path"]);
	}

	if (store.lastOptions && store.lastOptions.params)
		store.lastOptions.params[store.paramNames.start] = 0;

	if (from == "album") {
		// cm = Ext.getCmp("mp3_list").getColumnModel();
		if (value) {
			store.baseParams.albumID = value;
		} else
			store.baseParams.albumID = 0;
	} else
		store.baseParams.albumID = 0;

	if (from == "artist") {
		if (value)
			store.baseParams.artistID = value;
		else
			store.baseParams.artistID = 0;
	} else
		store.baseParams.artistID = 0;

	if (from == "tree") {
		if (value)
			store.baseParams.full_path = value;
		else
			store.baseParams.full_path = "";
	} else {
		store.baseParams.full_path = '';
		dont_select_tree_node = true;
		Ext.getCmp("dir_treeID").getRootNode().select();
		// Ext.getCmp("dir_treeID").getSelectionModel().clearSelections();
	}

	if (from != "quick_search") {
		Ext.getCmp("main_search_full_text_fieldID").setValue('');
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

	if (last_selected_album_from_artist)
		last_selected_album_from_artist.className = 'album_from_artist_entry';

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
	if (obj.className != "album_from_artist_entry_selected")
		obj.className = 'album_from_artist_entry_marked';
}

function album_selection_mouse_out(obj) {
	if (obj.className != "album_from_artist_entry_selected")
		obj.className = 'album_from_artist_entry';
}

function htmlspecialchars_decode(string, quote_style) {

	var histogram = {}, symbol = '', tmp_str = '', entity = '';
	tmp_str = string.toString();

	if (false === (histogram = get_html_translation_table('HTML_ENTITIES',
			quote_style))) {
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

	var entities = {}, histogram = {}, decimal = 0, symbol = '';
	var constMappingTable = {}, constMappingQuoteStyle = {};
	var useTable = {}, useQuoteStyle = {};

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
	if (sekunden < 10)
		sekunden = "0" + sekunden;
	return String.format('{0}:{1}', minuten, sekunden);
}

function number_format(value, decimals, dec_point, thousands_sep) {
	decimals = Math.abs(decimals) + 1 ? decimals : 2;
	dec_point = dec_point || '.';
	thousands_sep = thousands_sep || ',';
	var matches = /(-)?(\d+)(\.\d+)?/.exec((isNaN(value) ? 0 : value) + ''); // returns
																				// matches[1]
																				// as
																				// sign,
																				// matches[2]
																				// as
																				// numbers
																				// and
																				// matches[3]
																				// as
																				// decimals
	var remainder = matches[2].length > 3 ? matches[2].length % 3 : 0;
	return (matches[1] ? matches[1] : '')
			+ (remainder ? matches[2].substr(0, remainder) + thousands_sep : '')
			+ matches[2].substr(remainder).replace(/(\d{3})(?=\d)/g,
					"$1" + thousands_sep)
			+ (decimals ? dec_point
					+ (+matches[3] || 0).toFixed(decimals).substr(2) : '');
}

function expand_nodes(step, path_array, node) {
	if (step <= (path_array.length - 1)) {
		if (path_array[step] == "")
			var new_node = node.findChild("my_root", 1);
		else
			var new_node = node.findChild("name", path_array[step]);
		if (new_node) {
			new_node.expand(false, true, function() {
						step = step + 1;
						if (step <= (path_array.length - 1))
							expand_nodes(step, path_array, new_node);
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
	if (store.lastOptions && store.lastOptions.params)
		store.lastOptions.params[store.paramNames.start] = 0;
	if (path)
		store.baseParams.full_path = path;
	else
		store.baseParams.full_path = "";

	store.baseParams.albumID = 0;
	store.baseParams.artistID = 0;

	var letter = store.baseParams.letter_filter;
	if (letter)
		Ext.getCmp('button_filter[' + letter + ']').toggle(false);
	Ext.getCmp('button_filter[]').toggle(true);
	store.baseParams.letter_filter = '';
	mp3_list_obj.last_filter = '';

	Ext.getCmp("main_search_fieldID").setValue('');
	Ext.getCmp("main_search_full_text_fieldID").setValue('');
	store.baseParams.fields = "";
	store.baseParams.query = "";
	store.baseParams.full_text_search = "";

	store.reload({
				callback : function() {
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

function renderCover(value, p, record) {
	if (record.get('cover_url') != 0)
		return String.format('<img src="{0}s72/"/>', record.get('cover_url'),
				value);
	else
		return value;
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
	return String
			.format(
					'<a target=_blank href="functions/download.php?ID={0}" target=_blank>{1}</a>',
					record.get('ID'), value);
}

function renderArtist(value, p, record) {
	if (record.get('artistID') != 0)
		return String
				.format(
						'<a href="javascript: void(0)" onClick="filter_setting(\'artist\',{0});">{1}</a>',
						record.get('artistID'), value);
	else
		return value;
}

function renderAlbum(value, p, record) {
	return String
			.format(
					'<a href="javascript: void(0)" onClick="filter_setting(\'album\',{0});">{1}</a>',
					record.get('albumID'), value);
}

function renderPath(value, p, record) {
	return String
			.format(
					'<a href="javascript: void(0)" onClick="open_path(\'{0}\');">{1}</a>',
					addslashes(record.get('full_path')), value);
}

function enable_disable_buttons(form_name, enable) {
	if (enable) {
		Ext.getCmp(form_name + 'new_buttonID').enable();

		if (Ext.getCmp(form_name + 'navigationID').getValue()
				&& Ext.getCmp(form_name + 'formID').getForm().reader.jsonData.rows[0].type != "guest"
				&& Ext.getCmp(form_name + 'formID').getForm().reader.jsonData.rows[0].type != "admin")
			Ext.getCmp(form_name + 'delete_buttonID').enable();
		else
			Ext.getCmp(form_name + 'delete_buttonID').disable();

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
					title : 'User-Editor',
					width : 670,
					height : 370,
					resizable : false,
					layout : 'fit',
					border : false,
					stateful : false,
					plain : true,
					bodyStyle : 'padding:5px;',
					buttonAlign : 'center',
					items : user_form(userID),
					listeners : {
						close : function(p) {
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
					title : 'Setup-Editor',
					width : 670,
					height : 370,
					resizable : false,
					layout : 'fit',
					border : false,
					stateful : false,
					plain : true,
					bodyStyle : 'padding:5px;',
					buttonAlign : 'center',
					items : setup_form(),
					listeners : {
						close : function(p) {
							setup_editor = "";
						}
					}
				});

		setup_editor.show();
	}
}

function my_about() {
	Ext.MessageBox.show({
				title : 'About',
				msg : 'Jukepod',
				buttons : Ext.MessageBox.OK
			});
}

function cp1252_to_unicode(c) {
	if (c < 160) {
		if (c == 128)
			c = 8364;
		else if (c == 129)
			c = 160; // (Rayo:) #129 using no relevant sign, thus, mapped to
						// the saved-space #160
		else if (c == 130)
			c = 8218;
		else if (c == 131)
			c = 402;
		else if (c == 132)
			c = 8222;
		else if (c == 133)
			c = 8230;
		else if (c == 134)
			c = 8224;
		else if (c == 135)
			c = 8225;
		else if (c == 136)
			c = 710;
		else if (c == 137)
			c = 8240;
		else if (c == 138)
			c = 352;
		else if (c == 139)
			c = 8249;
		else if (c == 140)
			c = 338;
		else if (c == 141)
			c = 160; // (Rayo:) #129 using no relevant sign, thus, mapped to
						// the saved-space #160
		else if (c == 142)
			c = 381;
		else if (c == 143)
			c = 160; // (Rayo:) #129 using no relevant sign, thus, mapped to
						// the saved-space #160
		else if (c == 144)
			c = 160; // (Rayo:) #129 using no relevant sign, thus, mapped to
						// the saved-space #160
		else if (c == 145)
			c = 8216;
		else if (c == 146)
			c = 8217;
		else if (c == 147)
			c = 8220;
		else if (c == 148)
			c = 8221;
		else if (c == 149)
			c = 8226;
		else if (c == 150)
			c = 8211;
		else if (c == 151)
			c = 8212;
		else if (c == 152)
			c = 732;
		else if (c == 153)
			c = 8482;
		else if (c == 154)
			c = 353;
		else if (c == 155)
			c = 8250;
		else if (c == 156)
			c = 339;
		else if (c == 157)
			c = 160; // (Rayo:) #129 using no relevant sign, thus, mapped to
						// the saved-space #160
		else if (c == 158)
			c = 382;
		else if (c == 159)
			c = 376;
	} // if
	return c
}

var Utf8 = {
	// public method for url encoding
	encode : function(string) {
		string = string.replace(/\r\n/g, "\n");
		var utftext = "";
		for (var n = 0; n < string.length; n++) {
			var c = string.charCodeAt(n);
			if (c < 128) {
				utftext += String.fromCharCode(c);
			} else if ((c > 127) && (c < 2048)) {
				utftext += String
						.fromCharCode(cp1252_to_unicode((c >> 6) | 192));
				utftext += String
						.fromCharCode(cp1252_to_unicode((c & 63) | 128));
			} else {
				// console.log(String.fromCharCode((c >> 12) | 224) +
				// ",",String.fromCharCode(((c >> 6) & 63) | 128) + "," +
				// String.fromCharCode((c & 63) | 128));
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
		}
		return utftext;
	},
	// public method for url decoding
	decode : function(utftext) {
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
				string += String.fromCharCode(((c & 15) << 12)
						| ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
		}
		return string;
	}
}

function in_array(needle, stack) {
	for (var i = 0; i < stack.length; i++) {
		if (stack[i] == needle)
			return true;
	}
	return false;
}

function undock_player() {

	var playing_info = document.getElementById("playing_info_content");
	var parent_node = playing_info.parentNode;

	if (!player_window) {

		player_window = new Ext.Window({
					layout : 'fit',
					title : "Jukepod-Player",
					width : 412,
					minWidth : 412,
					height : 193,
					minHeight : 193,
					resizable : false,
					stateful : false,
					bodyStyle : 'background: white',
					closeAction : 'close',
					autoScroll : true,
					collapsible : true,
					html : "<div id='window_playing_info'></div>",
					plain : false,
					listeners : {
						beforeclose : function(p) {
							Ext.getCmp("left_northID").show();
							if (!Ext.getCmp("left_pangelID").isVisible()) {
								Ext.getCmp("left_pangelID").expand(false);
							}
							var playing_info = document
									.getElementById("playing_info_content");
							var parent_node = playing_info.parentNode;

							var removed_node = parent_node
									.removeChild(playing_info);

							document.getElementById("playing_info")
									.appendChild(removed_node);
							player_window = false;
							player_obj.undock_button.false();

							var parent = document
									.getElementById("playing_info_content");
							var child = document
									.getElementById("info_cover_row");

							var song_info = document
									.getElementById("song_info");

							parent.insertBefore(song_info, child);

							cp.set("player_window_show", 0);
							Ext.getCmp("left_pangelID").doLayout();
						}
					}
				});
		player_window.show();

		if (player_obj.undock_button)
			player_obj.undock_button.hide();

		var parent = document.getElementById("playing_info_content");
		var child = document.getElementById("song_info");

		var info_cover_row = document.getElementById("info_cover_row");

		parent.insertBefore(info_cover_row, child);

		cp.set("player_window_show", 0);

		Ext.getCmp("left_northID").hide();
		Ext.getCmp("left_pangelID").doLayout();

		var removed_node = parent_node.removeChild(playing_info);

		document.getElementById("window_playing_info")
				.appendChild(removed_node);
	}
}