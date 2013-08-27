// *******************************************************************
//
// MP3 LIST
//
// *******************************************************************

function mp3_list() {

	var alias = "mp3";
	var last_filter = "";

	var sort_array = new Array(0);
	var sort_dir_array = new Array(0);

	var actions = new Ext.ux.grid.RowActions({
		header: '&nbsp;',
		actions: [ 
			{
				iconCls: 'icon-extras',
				tooltip: 'Extras'
			}, {
				iconCls: 'icon-add-to-playlist',
				tooltip: 'Add To Playlist'
			} 
		],
		listeners: {
			action: function(grid, record, action, row, col) {
				switch(action) {
				case "icon-extras":
					var menu = extras_menu(record.data, "mp3_list");
					var cell = grid.getView().getCell(row, col);
					menu.show(Ext.get(cell));
					break;
				case "icon-add-to-playlist":
					var pl = Ext.getCmp("playlist_selectionID");

					if(pl.selectedIndex >= 0) var userID = pl.store.data.items[pl.selectedIndex].data.userID;
					else var userID = 0;

					if(pl.getValue() <= 0 || 0 || (userID == <?php echo $_SESSION["userID"];?> && 1 )) {
						var store = Ext.getCmp("playlistID").getStore();
						// Search for duplicates
						var foundItem = store.find('title', record.data.title);

						// if not found
						if(foundItem == -1) {
							store.add(record);
							player_obj.save_playlist();
						}
					} else {
						Ext.MessageBox.show({
							title: 'Error',
							msg: 'No Permission To Add To Playlist',
							buttons: Ext.MessageBox.OK,
							icon: Ext.MessageBox.INFO
						});
					}
					break;
				}
			}
		}
	});

	var proxy = new Ext.data.HttpProxy({
		api: {
			read: 'grid/get_list_data.php?type=' + alias,
			update: 'functions/save_rating.php'
		}
	});

	var writer = new Ext.data.JsonWriter({
		encode: true
	});

	var ds = new Ext.data.Store({
		proxy: proxy,
		writer: writer,
		autoSave: true,
		reader: new Ext.data.JsonReader({
			successProperty: 'success',
			idProperty: 'ID',
			totalProperty: 'total',
			root: 'data'
		}, [{
			name: 'ID'
		}, {
			name: 'filename'
		}, {
			name: 'filemtime',
			type: 'date',
			dateFormat: 'timestamp'
		}, {
			name: 'fileatime',
			type: 'date',
			dateFormat: 'timestamp'
		}, {
			name: 'title'
		}, {
			name: 'artist'
		}, {
			name: 'artistID'
		}, {
			name: 'album'
		}, {
			name: 'albumID'
		}, {
			name: 'genre'
		}, {
			name: 'rating'
		}, {
			name: 'comment'
		}, {
			name: 'year'
		}, {
			name: 'track'
		}, {
			name: 'filesize'
		}, {
			name: 'duration'
		}, {
			name: 'full_path'
		}, {
			name: 'num_downloads'
		}, {
			name: 'num_plays'
		}, {
			name: 'bit_rate'
		}]),
		listeners: {
			load: function() {
				//var p = Ext.getCmp("mp3_list").bottomToolbar.myinit();
				var length = queryStore.length;

				if(history_position >= length) {
					queryStore[length] = new Array();
					queryStore[length]["albumID"] = this.baseParams.albumID;
					queryStore[length]["artistID"] = this.baseParams.artistID;
					queryStore[length]["letter_filter"] = this.baseParams.letter_filter;
					queryStore[length]["field_filter"] = this.baseParams.field_filter;
					queryStore[length]["fields"] = this.baseParams.fields;
					queryStore[length]["query"] = this.baseParams.query;
					queryStore[length]["full_text_search"] = this.baseParams.full_text_search;
					queryStore[length]["limit"] = this.lastOptions.params.limit;
					queryStore[length]["start"] = this.lastOptions.params.start;
				}

				history_button_status();

				player_obj.stop_playlist = true;
			}
		},
		sortInfo: {
			field: 'title',
			direction: 'ASC'
		},
		remoteSort: true
	});

	var rateColumnPlugin = new Ext.ux.grid.RateColumnPlugin({
		zeroSensitivity: 0.25,
		tickSize: 16
	});

	var cm = new Ext.grid.ColumnModel([{
		hidden: true,
		sortable: true,
		dataIndex: 'filename',
		header: '<?php echo lang("filename",1);?>'
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'filemtime',
		header: 'Modified On',
		renderer: Ext.util.Format.dateRenderer('d.m.Y H:i')
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'fileatime',
		header: 'Added On',
		renderer: Ext.util.Format.dateRenderer('d.m.Y H:i')
	}, {
		dataIndex: 'title',
		sortable: true,
		header: 'Title'
	}, {
		dataIndex: 'artist',
		sortable: true,
		header: 'Artist',
		renderer: renderArtist
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'genre',
		header: 'Genre',
		width: 50
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'comment',
		header: 'Comment',
		width: 50
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'year',
		header: 'Year',
		width: 30
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'filesize',
		header: 'Filesize',
		width: 40,
		renderer: renderFilesize,
		align: 'right'
	}, {
		dataIndex: 'duration',
		sortable: true,
		header: 'Duration',
		width: 30,
		renderer: GridRenderDuration,
		align: 'right'
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'num_plays',
		header: 'Num. Of Plays',
		width: 30,
		renderer: renderNumber,
		align: 'right'
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'bit_rate',
		header: 'Bitrate',
		width: 30,
		renderer: renderBitrate,
		align: 'right'
	}, {
		header: 'Rating',
		sortable: true,
		dataIndex: 'rating',
		width: 100,
		renderer: rateColumnPlugin.createRenderer(5)
	} 
	<?php 
	if($_SESSION["permission"]["edit_all_playlists"]
	OR $_SESSION["permission"]["edit_own_playlists"]
	OR $_SESSION["permission"]["youtube"]
	OR $_SESSION["permission"]["lyrics"]
	OR $_SESSION["permission"]["download"]
	OR $_SESSION["permission"]["edit_tags"]) { ?> , actions <?php 
	} 
	?> 
	]);
	cm.defaultSortable = true;

	function history_jump() {
		var store = Ext.getCmp("mp3_list").getStore();

		delete(store.baseParams["albumID"]);
		delete(store.baseParams["artistID"]);
		delete(store.baseParams["letter_filter"]);
		delete(store.baseParams["query"]);
		delete(store.baseParams["fields"]);
		delete(store.baseParams["full_text_search"]);
		delete(store.baseParams["full_path"]);

		if(store.lastOptions && store.lastOptions.params) {
			delete(store.lastOptions.params["albumID"]);
			delete(store.lastOptions.params["artistID"]);
			delete(store.lastOptions.params["letter_filter"]);
			delete(store.lastOptions.params["query"]);
			delete(store.lastOptions.params["fields"]);
			delete(store.lastOptions.params["full_text_search"]);
			delete(store.lastOptions.params["full_path"]);
		}

		store.baseParams.albumID = queryStore[history_position]["albumID"];
		store.baseParams.artistID = queryStore[history_position]["artistID"];
		store.baseParams.letter_filter = queryStore[history_position]["letter_filter"];
		store.baseParams.field_filter = queryStore[history_position]["field_filter"];
		store.baseParams.query = queryStore[history_position]["query"];
		store.baseParams.fields = queryStore[history_position]["fields"];
		store.baseParams.full_text_search = queryStore[history_position]["full_text_search"];

		Ext.getCmp("main_search_full_text_fieldID").setValue(queryStore[history_position]["full_text_search"]);
		Ext.getCmp("pageSizeID").setValue(queryStore[history_position]["limit"]);

		store.lastOptions.params[store.paramNames.limit] = queryStore[history_position]["limit"];
		store.lastOptions.params[store.paramNames.start] = queryStore[history_position]["start"];

		store.reload();
	}

	function history_button_status() {
		if(history_position == 0) Ext.getCmp("history_back").disable();
		else Ext.getCmp("history_back").enable();

		if((queryStore.length - 1) > history_position) Ext.getCmp("history_forward").enable();
		else Ext.getCmp("history_forward").disable();
	}

	function letter_filter(letter) {
		if(Ext.getCmp('button_filter[' + last_filter + ']')) Ext.getCmp('button_filter[' + last_filter + ']').toggle(false);

		if(letter) Ext.getCmp('button_filter[]').toggle(false);

		Ext.getCmp('button_filter[' + letter + ']').toggle(true);
		last_filter = letter;

		filter_setting("letter", letter);
	}

	var filter_store_data = [
		['filename', '<?php echo lang("filename",1);?>'],
		['title', '<?php echo lang("title",1);?>'],
		['artist', '<?php echo lang("artist",1);?>'],
		['album', '<?php echo lang("album",1);?>'],
		['genre', '<?php echo lang("genre",1);?>']
	];
	
	var filter_store = new Ext.data.SimpleStore({
		fields: [{
			name: 'value'
		}, {
			name: 'text'
		}]
	});
	filter_store.loadData(filter_store_data);

	var grid = new Ext.grid.GridPanel({
		ddGroup: 'PlaylistDDGroup',
		enableDragDrop: true,
		border: false,
		id: alias + "_list",
		ds: ds,
		frame:true,
		cm: cm,
		title: 'Song List',
		enableColLock: false,
		loadMask: true,
		enableHdMenu: false,
		tbar: [
		{
			iconCls: 'icon-left',
			id: 'history_back',
			disabled: 'true',
			text: 'Back',
			handler: function() {
				history_position = history_position - 1;
				history_jump();
			}
		}, {
			iconCls: 'icon-right',
			id: 'history_forward',
			disabled: 'true',
			text: 'Forward',
			handler: function() {
				history_position = history_position + 1;
				history_jump();
			}
		}
		],
		plugins: [
			rateColumnPlugin,
			 new Ext.ux.grid.AutoSizeColumns(), 
			 new Ext.ux.grid.Search({
				iconCls: 'icon-zoom',
				readonlyIndexes: [],
				searchText: 'Search',
				checkIndexes: ['title', 'artist'],
				tbPosition: 11,
				no_button: true,
				width: 200,
				disableIndexes: ['ID', 'filename', 'album', 'genre', 'comment', 'year', 'full_path', 'duration', 'filesize', 'filemtime', 'fileatime', 'num_downloads', 'num_plays', 'bit_rate', 'rating'],
				minChars: <?= MIN_CHARS_IN_SEARCH; ?> ,
				myid: 'main_search_full_text_fieldID',
				autoFocus: false
			}), 
			new Ext.ux.grid.GridViewMenuPlugin, 
			actions
		],
		forceFit: true,
		trackMouseOver: true,
		autoWidth: true,
		viewConfig: {
			forceFit: true
		},
		keys: {
			key: 'a',
			ctrl: true,
			stopEvent: true,
			handler: function() {
				grid.getSelectionModel().selectAll();
			}
		},
		bbar: new Ext.PagingToolbar({
			store: ds,
			pageSize: <?php echo  ENTRIES_IN_MP3_LIST; ?> , displayMsg: "<?php echo lang('sum_entries',1);?>",
			displayInfo: true,
			listeners: {
				beforechange: function() {
					history_position++;
					queryStore = cut_array_to_n_elements(queryStore, history_position);
				}
			},
			plugins: [
				new Ext.ux.Andrie.pPageSize({
					beforeText: "Show",
					afterText: "Entries"
				})
			]
		}),
		listeners: {
			sortchange: function(g, sort) {},
			render: function() {
			}
		}
	});
	Ext.apply(grid, {
	});

	grid.addListener({
		'celldblclick': {
			fn: function(grid, rowIndex, columnIndex, event) {
				var record = grid.getStore().getAt(rowIndex);
				var ID = record.get('ID');
				record.data.num_plays = parseInt(record.data.num_plays) + 1;
				record.commit();
				player_obj.new_source("mp3_list");
				player_obj.play_index(rowIndex);
			},
			scope: this
		}
	}); // end grid.addListener
	grid.addListener({
		'cellclick': {
			fn: function(grid, rowIndex, columnIndex, event) {
				if(!player_obj.currently_playingID) {
					Ext.getCmp("playlistID").selModel.clearSelections();
					player_obj.new_source("mp3_list");
				}

			},
			scope: this
		}
	}); // end grid.addListener
	ds.load({
		params: {
			start: 0,
			limit: <?php echo  ENTRIES_IN_MP3_LIST; ?>
		}
	});

	return grid;
}