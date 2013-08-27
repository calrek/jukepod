function play_list() {

	var alias = "playlist";

	var no_playlist_saving = false;

	var actions = new Ext.ux.grid.RowActions({

		header: '&nbsp;',

		getData: function(value, cell, record, row, col, store) {

			var pl = Ext.getCmp("playlist_selectionID");

			if(pl.selectedIndex >= 0) var userID = pl.store.data.items[pl.selectedIndex].data.userID;

			else var userID = 0;

			if(!( 0 || pl.getValue() <= 0 || (userID == <?php echo  $_SESSION["userID"]; ?> && 1 ))) return {
				hideIndexCls: 1
			};
			else return {
				hideIndexCls: false
			};
			//return record.data || {};
		},
		actions: [
			{
				iconCls: 'icon-delete',
				tooltip: 'Delete',
				hideIndex: 'hideIndexCls'
			}
		],
		listeners: {
			action: function(grid, record, action, row, col) {
				switch(action) {
				case "icon-extras":
					var menu = extras_menu(record.data, "playlist");
					var cell = grid.getView().getCell(row, col);
					menu.show(Ext.get(cell));
					break;
				case "icon-delete":
					var store = Ext.getCmp("playlistID").getStore();
					if(record.data.ID == player_obj.currently_playingID && player_obj.source == "playlist"){
						if(store.data.length==1)
							player_obj.stop_playing();
						else
							player_obj.prev_next(1);
					}
					store.remove(record);
					enable_disable_buttons();
					player_obj.save_playlist();
				}
			}
		}
	});

	var ds = new Ext.data.JsonStore({

		url: 'grid/get_list_data.php?type=' + alias,

		baseParams: {
			playlistID: -1
		},

		id: 'id',
		totalProperty: 'total',
		root: 'data',
		fields: [{
			name: 'ID'
		}, {
			name: 'cover_url'
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
			name: 'sort_order'
		}],
		listeners: {
			load: function() {
				player_obj.stop_playlist = true;
			}
		}
	});

	var cm = new Ext.grid.ColumnModel([
		{
			sortable: true,
			dataIndex: 'cover_url',
			header: 'Cover',
			renderer: renderCover,
			align: 'left'
		},{
			dataIndex: 'track',
			hidden: true,
			sortable: true,
			header: 'Track',
		}, {
			hidden: true,
			sortable: true,
			dataIndex: 'filename',
			header: 'Filename'
		}, {
			dataIndex: 'title',
			sortable: true,
			header: 'Title'
		}, {
			dataIndex: 'artist',
			sortable: true,
			header: 'Artist',
			renderer: renderArtist
		},
		actions
	]);

	cm.defaultSortable = true;

	var resultTpl = new Ext.XTemplate(
	'<tpl for="."></tpl>'
	);

	var grid = new Ext.grid.GridPanel({

		ddGroup: 'PlaylistDDGroup',
		enableDragDrop: true,
		id: "playlistID",
		ds: ds,
		cm: cm,
		enableHdMenu: false,
		tbar:  [
			new Ext.form.ComboBox({
			fieldLabel: 'Playlist',
			id: 'playlist_selectionID',
			hiddenName: 'ID',
			tpl: '<tpl for="."><div ext:qtip="{tooltip}" class="x-combo-list-item{class_add}">{name}</div></tpl>',
			store: new Ext.data.JsonStore({
				url: 'functions/json.data.php',
				root: 'rows',
				baseParams: {
					tab: 'playlist',
					no_assign: 1
				},
				fields: ['ID', 'name', 'tooltip', 'class_add', 'userID'],
				autoLoad: true,
				listeners: {
					load: function() {
						Ext.getCmp("playlist_selectionID").setValue(0);
					}
				}
			}),
			valueField: 'ID',
			displayField: 'name',
			forceSelection: true,
			typeAhead: true,
			mode: 'local',
			triggerAction: 'all',
			emptyText: 'Choose Playlist',
			selectOnFocus: true,
			width: 160,
			listWidth: 230,
			onSelect: function(record, index) {
				if(this.fireEvent('beforeselect', this, record, index) !== false) {
					var v = this.getValue();
					this.setValue(record.data[this.valueField || this.displayField]);
					this.collapse();
					this.fireEvent('select', this, record, index);
					if(v != this.getValue()) {
						this.fireEvent('change', this, this.getValue(), v);
					}
				}
				var store = Ext.getCmp("playlistID").getStore();
				if(store.lastOptions && store.lastOptions.params) store.lastOptions.params[store.paramNames.start] = 0;
				store.baseParams.playlistID = this.getValue();
				no_playlist_saving = true;
				store.setDefaultSort("sort_order", "ASC");
				grid.getView().updateHeaderSortState();
				grid.getView().updateHeaders();
				store.reload({
					callback: function() {
						enable_disable_buttons();
					}
				});
				no_playlist_saving = false;
			}
		}) , {
				iconCls: 'icon-playlist-menu',
				menu: [
					{
						id: "playlist_add_buttonID",
						iconCls: 'icon-add',
						text: 'Create Playlist',
						handler: create_playlist
					}, {
						disabled: true,
						id: "playlist_edit_buttonID",
						iconCls: 'icon-edit',
						text: 'Edit Playlist',
						handler: edit_playlist
					}, {
						disabled: true,
						id: "playlist_delete_buttonID",
						iconCls: 'icon-delete',
						text: 'Delete Playlist',
						handler: delete_playlist
					}, {
						disabled: true,
						id: "playlist_clear_buttonID",
						iconCls: 'icon-clear-playlist',
						text: 'Clear Playlist',
						handler: clear_playlist
					}
				]
			}
		]
		,
		enableColLock: false,
		loadMask: true,
		plugins: [new Ext.ux.grid.GridViewMenuPlugin, actions],
		forceFit: true,
		border: false,
		trackMouseOver: false,
		iconCls: 'icon-playlist',
		title: "Playlist",
		autoWidth: true,
		layout: 'fit',
		viewConfig: {
			//rowTemplate: resultTpl,
			forceFit: true
		},
		listeners: {
			sortchange: function(g, sort) {
				var store = Ext.getCmp("playlistID").getStore();
				store.baseParams.last_sort_field = sort.field;
				store.baseParams.last_sort_dir = sort.direction;
			},
			render: function(g) {
				var ddrow = new Ext.ux.dd.GridReorderDropTarget(g, {
					copy: false,
					listeners: {
						beforerowmove: function(objThis, oldIndex, newIndex, records) {
						},
						afterrowmove: function(objThis, oldIndex, newIndex, records) {
							player_obj.save_playlist();
						},
						beforerowcopy: function(objThis, oldIndex, newIndex, records) {
						},
						afterrowcopy: function(objThis, oldIndex, newIndex, records) {
						}
					}
				});
			}
		}
	});

	function enable_disable_buttons() {

		var pl = Ext.getCmp("playlist_selectionID");

		if(pl.selectedIndex >= 0)

		var userID = pl.store.data.items[pl.selectedIndex].data.userID;

		else var userID = 0;

		if(Ext.getCmp("playlistID").store.getTotalCount() && pl.getValue() >= 0) {
			if(Ext.getCmp("playlist_clear_buttonID")) Ext.getCmp("playlist_clear_buttonID").enable();
		} else {
			if(Ext.getCmp("playlist_clear_buttonID")) Ext.getCmp("playlist_clear_buttonID").disable();
		}

		if(pl.getValue() && pl.getValue() > 0 && ( 0 || (userID == <?php echo  $_SESSION["userID"]; ?> && 1 ))) {
			if(Ext.getCmp("playlist_edit_buttonID")) Ext.getCmp("playlist_edit_buttonID").enable();
			if(Ext.getCmp("playlist_delete_buttonID")) Ext.getCmp("playlist_delete_buttonID").enable();
		} else {
			if(Ext.getCmp("playlist_edit_buttonID")) Ext.getCmp("playlist_edit_buttonID").disable();
			if(Ext.getCmp("playlist_delete_buttonID")) Ext.getCmp("playlist_delete_buttonID").disable();
		}
	}

	function clear_playlist() {
		Ext.Msg.show({
			title: 'Warning',
			msg: 'Confirm Clear Playlist',
			buttons: Ext.Msg.YESNO,
			buttonText: Ext.MessageBox.buttonText.yes = 'Yes',
			buttonText: Ext.MessageBox.buttonText.no = 'No',
			icon: Ext.MessageBox.QUESTION,
			fn: function(btn) {
				if(btn == "yes") {
					var store = Ext.getCmp("playlistID").getStore().removeAll();
					player_obj.save_playlist();
					if(Ext.getCmp("playlist_clear_buttonID")) Ext.getCmp("playlist_clear_buttonID").disable();
				}
			}
		});
	}

	function delete_playlist() {
		Ext.Msg.show({
			title: 'Warning',
			msg: 'Confirm Delete Playlist',
			buttons: Ext.Msg.YESNO,
			buttonText: Ext.MessageBox.buttonText.yes = 'Yes',
			buttonText: Ext.MessageBox.buttonText.no = 'No',
			icon: Ext.MessageBox.QUESTION,
			fn: function(btn) {
				if(btn == "yes") {
					Ext.Ajax.request({
						url: 'functions/playlist_handle.php',
						params: {
							id: Ext.getCmp('playlist_selectionID').getValue(),
							action: 'delete'
						},
						waitMsg: 'Deleting...',
						success: function(form, action) {
							Ext.Msg.show({
								title: 'Delete',
								msg: 'Delete Complete',
								minWidth: 200,
								modal: true,
								icon: Ext.Msg.INFO,
								buttons: Ext.Msg.OK
							});
							Ext.getCmp('playlist_selectionID').disable();
							Ext.getCmp('playlist_selectionID').store.reload({
								callback: function() {
									Ext.getCmp("playlist_selectionID").setValue(0);
									Ext.getCmp('playlist_selectionID').enable();

									var store = Ext.getCmp("playlistID").getStore();
									if(store.lastOptions && store.lastOptions.params) store.lastOptions.params[store.paramNames.start] = 0;
									store.baseParams.playlistID = 0;
									store.reload();

									enable_disable_buttons();
								}
							})
						},
						failure: function(form, action) {
							Ext.Msg.show({
								title: 'Error',
								msg: 'Error Deleting Playlist',
								minWidth: 200,
								modal: true,
								icon: Ext.Msg.ERROR,
								buttons: Ext.Msg.OK
							});
						}
					})
				}
			}
		})
	}

	function edit_playlist() {
		Ext.MessageBox.prompt('Edit Playlist', 'Enter New Name:', function(btn, text) {
			if(btn == "ok") {
				if(!text) {
					Ext.Msg.show({
						title: 'Error',
						msg: 'Enter Playlist Name',
						minWidth: 200,
						modal: true,
						icon: Ext.Msg.ERROR,
						buttons: Ext.Msg.OK
					});
				} else {
					id = Ext.getCmp("playlist_selectionID").getValue();
					Ext.Ajax.request({
						url: 'functions/playlist_handle.php',
						params: {
							name: text,
							action: 'edit',
							id: id
						},
						success: function(result, options) {
							if(Ext.util.JSON.decode(result.responseText).success) {
								Ext.getCmp("playlist_selectionID").disable();
								Ext.getCmp("playlist_selectionID").store.reload({
									callback: function() {
										Ext.getCmp("playlist_selectionID").setValue(id);
										Ext.getCmp("playlist_selectionID").enable();
										enable_disable_buttons();
									}
								});
							} else {
								Ext.Msg.show({
									title: 'Error',
									msg: Ext.util.JSON.decode(result.responseText).message,
									minWidth: 200,
									modal: true,
									icon: Ext.Msg.ERROR,
									buttons: Ext.Msg.OK
								});
							}
						},
						failure: function(result, options) {
							alert(result.responseText);
						}
					});
				}
			}
		}, '', false, Ext.getCmp("playlist_selectionID").getRawValue());
	}

	function create_playlist() {
		Ext.MessageBox.prompt('New Playlist', 'Enter Playlit Name:', function(btn, text) {
			if(btn == "ok") {
				if(!text) {
					Ext.Msg.show({
						title: 'Error',
						msg: 'Enter Playlist Name',
						minWidth: 200,
						modal: true,
						icon: Ext.Msg.ERROR,
						buttons: Ext.Msg.OK
					});
				} else {
					Ext.Ajax.request({
						url: 'functions/playlist_handle.php',
						params: {
							name: text,
							action: 'create'
						},
						success: function(result, options) {
							if(Ext.util.JSON.decode(result.responseText).success) {
								Ext.getCmp("playlist_selectionID").disable();
								Ext.getCmp("playlist_selectionID").store.reload({
									callback: function() {
										id = Ext.util.JSON.decode(result.responseText).id;
										Ext.getCmp("playlist_selectionID").setValue(id);
										Ext.getCmp("playlist_selectionID").enable();

										var store = Ext.getCmp("playlistID").getStore();
										if(store.lastOptions && store.lastOptions.params) store.lastOptions.params[store.paramNames.start] = 0;
										store.baseParams.playlistID = id;
										store.reload();

										enable_disable_buttons();
									}
								});
							} else {
								Ext.Msg.show({
									title: 'Error',
									msg: Ext.util.JSON.decode(result.responseText).message,
									minWidth: 200,
									modal: true,
									icon: Ext.Msg.ERROR,
									buttons: Ext.Msg.OK
								});
							}
						},
						failure: function(result, options) {
							alert(result.responseText);
						}
					});
				}
			}
		});
	}

	grid.addListener({
		'celldblclick': {
			fn: function(grid, rowIndex, columnIndex, event) {
				var record = grid.getStore().getAt(rowIndex);
				var ID = record.get('ID');
				record.data.num_plays = parseInt(record.data.num_plays) + 1;
				record.commit();
				player_obj.new_source("playlist");
				player_obj.play_index(rowIndex);
			},
			scope: this
		}
	}); // end grid.addListener

	grid.addListener({
		'cellclick': {
			fn: function(grid, rowIndex, columnIndex, event) {
				if(!player_obj.currently_playingID) {
					Ext.getCmp("mp3_list").selModel.clearSelections();
					player_obj.new_source("playlist");
				}

			},
			scope: this
		}
	}); // end grid.addListener

	grid.addListener({
		'sortchange': {
			fn: function(grid, rowIndex, columnIndex, event) {
				if(!no_playlist_saving) {
					player_obj.save_playlist();
				}
			},
			scope: this
		}
	}); // end grid.addListener

	ds.load();

	return grid;
}