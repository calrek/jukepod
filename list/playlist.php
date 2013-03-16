function play_list() {

	var alias = "playlist";
	var no_playlist_saving = false;
	var actions = new Ext.ux.grid.RowActions({
		header: '&nbsp;',
		getData: function(value, cell, record, row, col, store) {
			var pl = Ext.getCmp("playlist_selectionID");
			if (pl.selectedIndex >= 0) var userID = pl.store.data.items[pl.selectedIndex].data.userID;
			else var userID = 0;

			if (!( <?php
			if ($_SESSION["permission"]["edit_all_playlists"]) echo "1";
			else echo "0"; ?> || pl.getValue() <= 0 || (userID == <?= $_SESSION["userID"]; ?> && <?php
			if ($_SESSION["permission"]["has_access"]) echo "1";
			else echo "0"; ?> ))) return {
				hideIndexCls: 1
			};
			else return {
				hideIndexCls: false
			};
			//return record.data || {};
		},

		actions: [{
			iconCls: 'icon-delete',
			tooltip: '<?=lang("delete",1);?>',
			hideIndex: 'hideIndexCls'
		}],

		listeners: {
			action: function(grid, record, action, row, col) {
				switch (action) {
				case "icon-delete":
					var store = Ext.getCmp("playlistID").getStore();
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
			name: 'surdoc_url'
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

	var cm = new Ext.grid.ColumnModel([{
		hidden: true,
		dataIndex: 'track',
		sortable: true,
		header: '<?=lang("num",1);?>',
		width: 20
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'filename',
		header: '<?=lang("filename",1);?>'
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'filemtime',
		header: '<?=lang("modified_on",1);?>',
		renderer: Ext.util.Format.dateRenderer('d.m.Y H:i')
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'fileatime',
		header: '<?=lang("added_on",1);?>',
		renderer: Ext.util.Format.dateRenderer('d.m.Y H:i')
	}, {
		dataIndex: 'title',
		sortable: true,
		header: '<?=lang("title",1);?>',
		width: 20
	}, {
		dataIndex: 'artist',
		sortable: true,
		header: '<?=lang("artist",1);?>',
		renderer: renderArtist,
		width: 20
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'album',
		header: '<?=lang("album",1);?>',
		renderer: renderAlbum
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'genre',
		header: '<?=lang("genre",1);?>',
		width: 50
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'comment',
		header: '<?=lang("comment",1);?>',
		width: 50
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'year',
		header: '<?=lang("year",1);?>',
		width: 30
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'filesize',
		header: '<?=lang("filesize",1);?>',
		width: 40,
		renderer: renderFilesize,
		align: 'right'
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'duration',
		header: '<?=lang("duration",1);?>',
		width: 30,
		renderer: GridRenderDuration,
		align: 'right'
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'full_path',
		header: '<?=lang("path",1);?>',
		renderer: renderPath
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'num_plays',
		header: '<?=lang("num_plays",1);?>',
		width: 30,
		renderer: renderNumber,
		align: 'right'
	}, {
		hidden: true,
		sortable: true,
		dataIndex: 'bit_rate',
		header: '<?=lang("bit_rate",1);?>',
		width: 30,
		renderer: renderBitrate,
		align: 'right'
	} <?php
	if (
	$_SESSION["permission"]["edit_all_playlists"]
	OR $_SESSION["permission"]["has_access"]
	OR $_SESSION["permission"]["has_access"]
	OR $_SESSION["permission"]["has_access"]
	OR $_SESSION["permission"]["has_access"]
	OR $_SESSION["permission"]["has_access"]) { ?> , actions <?php
	} ?> ]);
	cm.defaultSortable = true;

	var grid = new Ext.grid.GridPanel({
		height: 250,
		maxHeight: 1000,
		layout: 'fit',
		autoScroll: true,
		ddGroup: 'PlaylistDDGroup',
		enableDragDrop: true,
		id: "playlistID",
		ds: ds,
		cm: cm,
		enableHdMenu: false,
		tbar: [
		new Ext.form.ComboBox({
			fieldLabel: 'Playlist',
			id: 'playlist_selectionID',
			hiddenName: 'ID',
			tpl: '<tpl for="."><div ext:qtip="{tooltip}" class="x-combo-list-item{class_add}">{name}</div></tpl>',
			store: new Ext.data.JsonStore({
				url: 'json.data.php',
				root: 'rows',
				baseParams: {
					tab: 'playlist',
					no_assign: 1,
					addnobody: 1,
					nobody_text: '<?=lang("none",1);?>'
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
			emptyText: '<?=lang("choose_playlist",1);?>',
			selectOnFocus: true,
			width: 160,
			listWidth: 230,
			onSelect: function(record, index) {
				if (this.fireEvent('beforeselect', this, record, index) !== false) {
					var v = this.getValue();
					this.setValue(record.data[this.valueField || this.displayField]);
					this.collapse();
					this.fireEvent('select', this, record, index);
					if (v != this.getValue()) {
						this.fireEvent('change', this, this.getValue(), v);
					}
				}
				var store = Ext.getCmp("playlistID").getStore();
				if (store.lastOptions && store.lastOptions.params) store.lastOptions.params[store.paramNames.start] = 0;
				store.baseParams.playlistID = this.getValue();
				enable_disable_buttons();
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
		}) <?php
		if ($_SESSION["permission"]["has_access"] OR $_SESSION["permission"]["edit_all_playlists"]) { ?> , {
				iconCls: 'icon-playlist-menu',
				// <-- icon
				menu: [{
					id: "playlist_add_buttonID",
					iconCls: 'icon-add',
					text: '<?=lang("create_playlist",1);?>',
					handler: create_playlist
				}, {
					disabled: true,
					id: "playlist_edit_buttonID",
					iconCls: 'icon-edit',
					text: '<?=lang("edit_playlist",1);?>',
					handler: edit_playlist
				}, {
					disabled: true,
					id: "playlist_delete_buttonID",
					iconCls: 'icon-delete',
					text: '<?=lang("delete_playlist",1);?>',
					handler: delete_playlist
				}, {
					disabled: true,
					id: "playlist_clear_buttonID",
					iconCls: 'icon-clear-playlist',
					text: '<?=lang("clear_playlist",1);?>',
					handler: clear_playlist
				}, {
					disabled: true,
					id: "playlist_podcast_buttonID",
					iconCls: 'icon-podcast',
					text: 'Generate Podcast',
					handler: delete_playlist
				}]
			}, {
				id: "Cooliris_buttonID",
				iconCls: 'icon-cooliris',
				handler: open_cooliris
			}, {
				iconCls: 'icon-podcast'
			} <?php
		} else { ?> , {
				iconCls: 'icon-playlist-menu',
				// <-- icon
				menu: [{
					disabled: true,
					id: "playlist_clear_buttonID",
					iconCls: 'icon-clear-playlist',
					text: '<?=lang("clear_playlist",1);?>',
					handler: clear_playlist
				}]
			} <?php
		} ?> ],
		iconCls: 'icon-playlist-menu',
		enableColLock: false,
		loadMask: true,
		plugins: [actions],
		forceFit: true,
		border: false,
		trackMouseOver: false,
		autoWidth: true,
		layout: 'fit',
		viewConfig: {
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
							// code goes here
							// return false to cancel the move
						},
						afterrowmove: function(objThis, oldIndex, newIndex, records) {
							player_obj.save_playlist();
							// code goes here
						},
						beforerowcopy: function(objThis, oldIndex, newIndex, records) {
							//alert("3");
							// code goes here
							// return false to cancel the copy
						},
						afterrowcopy: function(objThis, oldIndex, newIndex, records) {
							//alert("4");
							// code goes here
						}
					}
				});
			}
		}
	});
	//grid.getColumnModel().setHidden(1, true)

	function enable_disable_buttons() {
		var pl = Ext.getCmp("playlist_selectionID");

		if (pl.selectedIndex >= 0) var userID = pl.store.data.items[pl.selectedIndex].data.userID;
		else var userID = 0;

		if (Ext.getCmp("playlistID").store.getTotalCount() && pl.getValue() >= 0) {
			if (Ext.getCmp("playlist_clear_buttonID")) Ext.getCmp("playlist_clear_buttonID").enable();
		} else {
			if (Ext.getCmp("playlist_clear_buttonID")) Ext.getCmp("playlist_clear_buttonID").disable();
		}

		if (pl.getValue() && pl.getValue() > 0 && ( <?php
		if ($_SESSION["permission"]["edit_all_playlists"]) echo "1";
		else echo "0"; ?> || (userID == <?= $_SESSION["userID"]; ?> && <?php
		if ($_SESSION["permission"]["has_access"]) echo "1";
		else echo "0"; ?> ))) {

			if (Ext.getCmp("playlist_edit_buttonID")) Ext.getCmp("playlist_edit_buttonID").enable();

			if (Ext.getCmp("playlist_delete_buttonID")) Ext.getCmp("playlist_delete_buttonID").enable();

			if (Ext.getCmp("playlist_podcast_buttonID")) Ext.getCmp("playlist_podcast_buttonID").enable();

		} else {
			if (Ext.getCmp("playlist_edit_buttonID")) Ext.getCmp("playlist_edit_buttonID").disable();

			if (Ext.getCmp("playlist_delete_buttonID")) Ext.getCmp("playlist_delete_buttonID").disable();

			if (Ext.getCmp("playlist_podcast_buttonID")) Ext.getCmp("playlist_podcast_buttonID").disable();

		}
	}

	function clear_playlist() {
		Ext.Msg.show({
			title: '<?=lang("warning",1);?>',
			msg: '<?=lang("clear_playlist_confirm",1);?>',
			buttons: Ext.Msg.YESNO,
			buttonText: Ext.MessageBox.buttonText.yes = '<?=lang("yes",1);?>',
			buttonText: Ext.MessageBox.buttonText.no = '<?=lang("no",1);?>',
			icon: Ext.MessageBox.QUESTION,
			fn: function(btn) {
				if (btn == "yes") {
					var store = Ext.getCmp("playlistID").getStore().removeAll();
					player_obj.save_playlist();
					if (Ext.getCmp("playlist_clear_buttonID")) Ext.getCmp("playlist_clear_buttonID").disable();
				}
			}
		});
	}

	function delete_playlist() {
		Ext.Msg.show({
			title: '<?=lang("warning",1);?>',
			msg: '<?=lang("delete_playlist_confirm",1);?>',
			buttons: Ext.Msg.YESNO,
			buttonText: Ext.MessageBox.buttonText.yes = '<?=lang("yes",1);?>',
			buttonText: Ext.MessageBox.buttonText.no = '<?=lang("no",1);?>',
			icon: Ext.MessageBox.QUESTION,
			fn: function(btn) {
				if (btn == "yes") {
					Ext.Ajax.request({
						url: 'playlist_handle.php',
						params: {
							id: Ext.getCmp('playlist_selectionID').getValue(),
							action: 'delete'
						},
						waitMsg: '<?=lang("deleting",1);?>',
						success: function(form, action) {
							Ext.Msg.show({
								title: '<?=lang("delete",1);?>',
								msg: '<?=lang("delete_succesfull",1);?>',
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
									if (store.lastOptions && store.lastOptions.params) store.lastOptions.params[store.paramNames.start] = 0;
									store.baseParams.playlistID = 0;
									store.reload();

									enable_disable_buttons();
								}
							})
						},
						failure: function(form, action) {
							Ext.Msg.show({
								title: '<?=lang("error",1);?>',
								msg: '<?=lang("error_deleting_playlist",1);?>',
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
		Ext.MessageBox.prompt('<?=lang("edit_playlist",1);?>', '<?=lang("enter_new_playlist_name",1)?>:', function(btn, text) {
			if (btn == "ok") {
				if (!text) {
					Ext.Msg.show({
						title: '<?=lang("error",1);?>',
						msg: '<?=lang("error_no_playlist_name",1);?>',
						minWidth: 200,
						modal: true,
						icon: Ext.Msg.ERROR,
						buttons: Ext.Msg.OK
					});
				} else {
					id = Ext.getCmp("playlist_selectionID").getValue();
					Ext.Ajax.request({
						url: 'playlist_handle.php',
						params: {
							name: text,
							action: 'edit',
							id: id
						},
						success: function(result, options) {
							if (Ext.util.JSON.decode(result.responseText).success) {
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
									title: '<?=lang("error",1);?>',
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
		Ext.MessageBox.prompt('<?=lang("new_playlist",1);?>', '<?=lang("enter_new_playlist_name",1);?>:', function(btn, text) {
			if (btn == "ok") {
				if (!text) {
					Ext.Msg.show({
						title: '<?=lang("error",1);?>',
						msg: '<?=lang("error_no_playlist_name",1);?>',
						minWidth: 200,
						modal: true,
						icon: Ext.Msg.ERROR,
						buttons: Ext.Msg.OK
					});
				} else {
					Ext.Ajax.request({
						url: 'playlist_handle.php',
						params: {
							name: text,
							action: 'create'
						},
						success: function(result, options) {
							if (Ext.util.JSON.decode(result.responseText).success) {
								Ext.getCmp("playlist_selectionID").disable();
								Ext.getCmp("playlist_selectionID").store.reload({
									callback: function() {
										id = Ext.util.JSON.decode(result.responseText).id;
										Ext.getCmp("playlist_selectionID").setValue(id);
										Ext.getCmp("playlist_selectionID").enable();

										var store = Ext.getCmp("playlistID").getStore();
										if (store.lastOptions && store.lastOptions.params) store.lastOptions.params[store.paramNames.start] = 0;
										store.baseParams.playlistID = id;
										store.reload();

										enable_disable_buttons();
									}
								});
							} else {
								Ext.Msg.show({
									title: '<?=lang("error",1);?>',
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
				//Ext.getCmp("playlistID").selModel.clearSelections();
				//Ext.getCmp("playlistID").selModel.selectRow(rowIndex);
				if (!player_obj.currently_playingID) {
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
				if (!no_playlist_saving) {
					player_obj.save_playlist();
				}
			},
			scope: this
		}
	}); // end grid.addListener
	ds.load();
	return grid;
}