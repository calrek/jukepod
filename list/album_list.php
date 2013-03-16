// *******************************************************************
//
// ALBUM LIST
//
// *******************************************************************

function album_list() {

	var title = '<?=lang("album_grid_title");?>';
	var alias = "album";

	var ds = new Ext.data.JsonStore({
		url: 'grid/get_list_data.php?type=' + alias,
		id: 'id',
		totalProperty: 'total',
		root: 'data',
		fields: [{
			name: 'albumID'
		}, {
			name: 'name'
		}, {
			name: 'cover'
		}, {
			name: 'artist'
		}, {
			name: 'artistID'
		}, {
			name: 'num_files'
		}, {
			name: 'name_slashes'
		}, {
			name: 'artist_slashes'
		}],
		listeners: {
			datachanged: function() {
				/*console.log(ds);
				console.log(ds.baseParams.fields);
				console.log(ds.sortInfo.direction);
				console.log(ds.sortInfo.field);
				console.log("album_feed.php?query=" + ds.baseParams.query + "&fields=" + ds.baseParams.fields + "&sort=" + ds.sortInfo.field + "&sort_dir=" + ds.sortInfo.direction);
				console.log(cooliris_dont_reload);*/
				if (cooliris_window && !cooliris_dont_reload) {
					cooliris.embed.setFeedURL("album_feed.php?query=" + ds.baseParams.query + "&fields=" + ds.baseParams.fields + "&sort=" + ds.sortInfo.field + "&sort_dir=" + ds.sortInfo.direction);
				}
			}
		},
		sortInfo: {
			field: 'name',
			direction: 'ASC'
		},
		remoteSort: true
	});

	var cm = new Ext.grid.ColumnModel([{
		dataIndex: 'name',
		header: '<?=lang("album");?>',
		renderer: renderAlbum,
		sortable: true
	}, {
		dataIndex: 'artist',
		header: '<?=lang("artist");?>',
		renderer: renderArtist,
		sortable: true
	}]);
	cm.defaultSortable = true;

	var imageTooltip = 'qtip="<b>Album:</b>&nbsp;{name_slashes}<br /><b>Interpret:</b>&nbsp;{artist_slashes}<br /><b>Songs:</b>&nbsp;{num_files}"';

	var giantCover = new Ext.Template('<div class="giantCover"><div class="album_list_template"><div class="coverCotainer">' + '<div class="album_cover_row"><a href="javascript: void(0)" onClick="filter_setting(\'album\',{albumID});"><img ' + imageTooltip + ' border=0 src="{cover}"></a></div>' + '<div class="album_name_row"><a href="javascript: void(0)" onClick="filter_setting(\'album\',{albumID});">{name}</a></div>' + '<div class="album_artist_row"><a href="javascript: void(0)" onClick="filter_setting(\'artist\',{artistID});">{artist}</a></div>' + '</div></div></div>');

	var mediumCover = new Ext.Template('<div class="mediumCover"><div class="album_list_template"><div class="coverCotainer">' + '<div class="album_cover_row"><a href="javascript: void(0)" onClick="filter_setting(\'album\',{albumID});"><img ' + imageTooltip + ' border=0 src="{cover}"></a></div>' + '<div class="album_name_row"><a href="javascript: void(0)" onClick="filter_setting(\'album\',{albumID});">{name}</a></div>' + '<div class="album_artist_row"><a href="javascript: void(0)" onClick="filter_setting(\'artist\',{artistID});">{artist}</a></div>' + '</div></div></div>');

	var tinyCover = new Ext.Template('<div class="tinyCover"><div class="album_list_template"><div class="coverCotainer">' + '<div class="album_cover_row"><a href="javascript: void(0)" onClick="filter_setting(\'album\',{albumID});"><img ' + imageTooltip + ' border=0 src="{cover}"></a></div>' + '<div class="album_name_row"><a href="javascript: void(0)" onClick="filter_setting(\'album\',{albumID});">{name}</a></div>' + '</div></div></div>');

	function changeView(item, checked) {
		var tpl;
		if (checked) {
			if (item.type == 'giant') {
				tpl = giantCover;
				cp.set("album_list_template", "giantCover");
			} else if (item.type == 'medium') {
				tpl = mediumCover;
				cp.set("album_list_template", "mediumCover");
			} else if (item.type == 'tiny') {
				tpl = tinyCover;
				cp.set("album_list_template", "tinyCover");
			} else {
				tpl = null;
				cp.set("album_list_template", 0);
			}

			grid.getView().changeTemplate(tpl);
		}
	}

	function album_name(value, p, record) {
		if (record.get('cover_resized') == 1) var path = "cover/album/resized/" + record.get('albumID') + ".jpg";
		else var path = "img/cover_no_album.gif";

		return String.format('<div class="album_cover_row"><img src="' + path + '"></div><div class="album_name_row">{1}</div><div class="album_artist_row">{2}</div>', record.get('albumID'), record.get('name'), record.get('artist'));
	}

	var tpl_name = cp.get("album_list_template", 0);
	if (tpl_name) var tpl = eval(cp.get("album_list_template", 0));
	else var tpl = null;

	var grid = new Ext.grid.GridPanel({
		id: alias + "_list",
		header: true,
		border: false,
		iconCls: 'icon-album',
		title: title,
		ds: ds,
		cm: cm,
		tbar: [],
		enableHdMenu: false,
		enableColLock: false,
		loadMask: true,
		plugins: [new Ext.ux.grid.Search({
			iconCls: 'icon-zoom',
			searchText: '',
			readonlyIndexes: [],
			disableIndexes: [],
			position: "top",
			minChars: 1,
			autoFocus: true
			// ,menuStyle:'radio'
		})],
		tbar: new Ext.Toolbar({
			items: [{
				tooltip: '<?=lang("change_view",1);?>',
				iconCls: 'icon-view',
				menu: [{
					group: 'view_album',
					checkHandler: changeView,
					checked: tpl_name == "giantCover",
					text: '<?=lang("big_cover",1);?>',
					type: 'giant'
				}, {
					group: 'view_album',
					checkHandler: changeView,
					checked: tpl_name == "mediumCover",
					text: '<?=lang("medium_cover",1);?>',
					type: 'medium'
				}, {
					group: 'view_album',
					checkHandler: changeView,
					checked: tpl_name == "tinyCover",
					text: '<?=lang("small_cover",1);?>',
					type: 'tiny'
				}, {
					group: 'view_album',
					checkHandler: changeView,
					checked: tpl_name == "",
					text: '<?=lang("list",1);?>',
					type: 'list'
				},
				new Ext.menu.Separator(),
				{
					text: 'Cooliris',
					handler: open_cooliris
				}]
			}]
		}),
		forceFit: true,
		trackMouseOver: false,
		autoWidth: true,
		layout: "fit",
		autoScroll: true,
		viewConfig: {
			rowTemplate: tpl,
			forceFit: true
			/*enableRowBody:true,
				showPreview:true,
				getRowClass : function(record, rowIndex, p, store){
						if(this.showPreview){
								p.body = '<p><img src="cover/album/resized/'+record.data.ID+'.jpg"></p>';
								return 'x-grid3-row-expanded';
						}
						return 'x-grid3-row-collapsed';
				}*/
		},
		bbar: new Ext.PagingToolbar({
			store: ds,
			pageSize: <?= ENTRIES_IN_ALBUM_LIST; ?> , displayInfo: false,
			listeners: {
				beforechange: function() {
					cooliris_dont_reload = true;
				},
				change: function() {
					cooliris_dont_reload = false;
				}
			}
		})
	});
	//grid.getColumnModel().setHidden(1, true)
	//Ext.util.Observable.capture(grid, function(e){console.info(e)}); 
	/*grid.addListener({
  'cellclick' : {
  fn : function(grid, rowIndex, columnIndex, event) {
		var record = grid.getStore().getAt(rowIndex);
		var ID = record.get('ID');
		
		filter_setting("album",ID);
  },
  scope : this
  }
  });// end grid.addListener*/

	ds.load({
		params: {
			start: 0,
			limit: <?= ENTRIES_IN_ALBUM_LIST; ?>
		}
	});

	return grid;
}