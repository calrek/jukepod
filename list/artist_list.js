function artist_list() {

	var title = 'Artist';
	var alias = "artist";

	var ds = new Ext.data.JsonStore({
		url: 'grid/get_list_data.php?type=' + alias,
		id: 'id',
		totalProperty: 'total',
		root: 'data',
		fields: [{
			name: 'ID'
		}, {
			name: 'name'
		}, {
			name: 'loaded'
		}],
		sortInfo: {
			field: 'name',
			direction: 'ASC'
		},
		remoteSort: true
	});

	function changeView(item, checked) {
		var tpl;
		if(checked) {
			if(item.type == 'giant') {
				grid.removeClass("mediumCover");
				grid.removeClass("tinyCover");
				grid.removeClass("noCover");
				grid.addClass("giantCover");
				cp.set("artist_list_template", "giantCover");
			} else if(item.type == 'medium') {
				grid.addClass("mediumCover");
				grid.removeClass("tinyCover");
				grid.removeClass("noCover");
				grid.removeClass("giantCover");
				cp.set("artist_list_template", "mediumCover");
			} else if(item.type == 'tiny') {
				grid.removeClass("mediumCover");
				grid.addClass("tinyCover");
				grid.removeClass("noCover");
				grid.removeClass("giantCover");
				cp.set("artist_list_template", "tinyCover");
			} else {
				grid.removeClass("mediumCover");
				grid.removeClass("tinyCover");
				grid.addClass("noCover");
				grid.removeClass("giantCover");
				cp.set("artist_list_template", "noCover");
			}
		}
	}

	var expander = new Ext.grid.RowExpander({
		remoteDataMethod: function(record, index) {
			//grid.selModel.deselectRow(index);
			if(!record.get("loaded")) {
				Ext.get("filter_panelID").mask('Loading Album');
				Ext.Ajax.request({
					url: 'functions/get_albums.php?ID=' + record.get("ID"),
					success: function(xhr, options) {
						record.set("loaded", true);
						record.set("alben", xhr.responseText);
						record.commit();
						document.getElementById('remData' + index).innerHTML = xhr.responseText;
						Ext.get("filter_panelID").unmask();
					}
				});
			} else document.getElementById('remData' + index).innerHTML = record.get("alben");
		}
	});

	var cm = new Ext.grid.ColumnModel([
	expander,
	{
		dataIndex: 'name',
		sortable: true,
		menuDisabled: true,
		header: 'Name'
	}]);
	cm.defaultSortable = true;

	var tpl_name = cp.get("artist_list_template", "giantCover");

	var grid = new Ext.grid.GridPanel({
		id: alias + "_list",
		title: title,
		header: true,
		border: false,
		iconCls: 'icon-artist',
		cls: tpl_name,
		ds: ds,
		cm: cm,
		tbar: new Ext.Toolbar({
			items: [{
				tooltip: 'Change View',
				iconCls: 'icon-view',
				menu: [{
					group: 'view',
					checkHandler: changeView,
					checked: tpl_name == "giantCover",
					text: 'Big Cover',
					type: 'giant'
				}, {
					group: 'view',
					checkHandler: changeView,
					checked: tpl_name == "mediumCover",
					text: 'Medium Cover',
					type: 'medium'
				}, {
					group: 'view',
					checkHandler: changeView,
					checked: tpl_name == "tinyCover",
					text: 'Tiny Cover',
					type: 'tiny'
				}, {
					group: 'view',
					checkHandler: changeView,
					checked: tpl_name == "noCover",
					text: 'List',
					type: 'list'
				}]
			}]
		}),
		enableColLock: false,
		loadMask: true,
		plugins: [expander, new Ext.ux.grid.Search({
			iconCls: 'icon-zoom',
			searchText: '<img src="img/silk/icons/magnifier.png">',
			readonlyIndexes: [],
			disableIndexes: [],
			no_button: true,
			position: "top",
			minChars: 1,
			autoFocus: true
			// ,menuStyle:'radio'
		})],
		forceFit: true,
		trackMouseOver: false,
		autoWidth: true,
		layout: 'fit',
		autoScroll: true,
		viewConfig: {
			forceFit: true
		},
		listeners: {
			'beforerowselect': function() {
				if(from_cellclick) return true;
				else return false;
			}
		},
		bbar: new Ext.PagingToolbar({
			store: ds,
			pageSize: 50,
            displayInfo: false
		})
	});
	//grid.getColumnModel().setHidden(1, true)
	grid.addListener({
		'cellclick': {
			fn: function(grid, rowIndex, columnIndex, event) {
				if(columnIndex) {
					from_cellclick = true;
					grid.selModel.selectRow(rowIndex);
					from_cellclick = false

					if(last_selected_album_from_artist) last_selected_album_from_artist.className = 'album_from_artist_entry';

					var record = grid.getStore().getAt(rowIndex);
					var ID = record.get('ID');

					filter_setting("artist", ID);
				}
			},
			scope: this
		}
	}); // end grid.addListener
	ds.load({
		params: {
			start: 0,
			limit: 50
		}
	});

	return grid;
}