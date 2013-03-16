function mp3_list() {
 //var title = '<?=lang("mp3_grid_title",1);?>';
	var title = '<div class="mp3_title"><div class="icon"><img src="img/silk/icons/music.png"></div><div id="title" class="title"><?=lang("mp3_grid_title",1);?></div><div class="about"><?=lang("hello",1);?> <? echo $_SESSION["username"];?>, <a href="<?
	if($_SESSION["usertype"]=="guest") 
		echo "login"; 
	else 
		echo "logout";
	?>.php"><?
	if($_SESSION["usertype"]=="guest") 
		echo $lang["login"]; 
	else echo $lang["logout"];
	?></a>&nbsp;<?
	if($_SESSION["permission"]["useradmin"])
	{
		?><img src="img/silk/icons/user.png" onClick="open_user_form(<?=$_SESSION['userID'];?>)">&nbsp;<?
	}
	?><img src="img/silk/icons/help.png" onClick="my_about()"></div></div>';
  
	var alias = "mp3";
	var last_filter = "";
	
  var last_filter = "";
  var sort_array = new Array(0);
  var sort_dir_array = new Array(0);
  var actions = new Ext.ux.grid.RowActions({
    header: '&nbsp;',
    actions: [ <?php
    if (
    $_SESSION["permission"]["has_access"]
    OR $_SESSION["permission"]["has_access"]
    OR $_SESSION["permission"]["has_access"]
    OR $_SESSION["permission"]["has_access"]) { ?> {
        iconCls: 'icon-extras',
        tooltip: '<?=lang("extras",1);?>'
      } <?php
    }
    if ($_SESSION["permission"]["has_access"] || $_SESSION["permission"]["has_access"]) {
      if (
      $_SESSION["permission"]["has_access"]
      OR $_SESSION["permission"]["has_access"]
      OR $_SESSION["permission"]["has_access"]
      OR $_SESSION["permission"]["has_access"]) echo ","; ?> {
        iconCls: 'icon-add-to-playlist',
        tooltip: '<?=lang("add_to_playlist",1);?>'
      } <?php
    } ?> ],
    listeners: {
      action: function(grid, record, action, row, col) {
        switch (action) {
        case "icon-extras":
          var menu = extras_menu(record.data, "mp3_list");
          var cell = grid.getView().getCell(row, col);
          menu.show(Ext.get(cell));
          break;
        case "icon-add-to-playlist":
          var pl = Ext.getCmp("playlist_selectionID");
          if (pl.selectedIndex >= 0) var userID = pl.store.data.items[pl.selectedIndex].data.userID;
          else var userID = 0;
          if (pl.getValue() <= 0 || <?php
          if ($_SESSION["permission"]["has_access"]) echo "1";
          else echo "0"; ?> || (userID == <?= $_SESSION["userID"]; ?> && <?php
          if ($_SESSION["permission"]["has_access"]) echo "1";
          else echo "0"; ?> )) {
            var store = Ext.getCmp("playlistID").getStore();
            // Search for duplicates
            var foundItem = store.find('title', record.data.title);
            // if not found
            if (foundItem == -1) {
              store.add(record);
              player_obj.save_playlist();
            }
          } else {
            Ext.MessageBox.show({
              title: '<?=lang("error",1);?>',
              msg: '<?=lang("no_permission_to_add_to_playlist",1);?>',
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
      update: 'save_rating.php'
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
      name: 'bit_rate'
    }]),
    listeners: {
      load: function() {
        var length = queryStore.length;
        if (history_position >= length) {
          queryStore[length] = new Array();
          queryStore[length]["albumID"] = this.baseParams.albumID;
          queryStore[length]["artistID"] = this.baseParams.artistID;
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
  var cm = new Ext.grid.ColumnModel([{
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
    header: '<?=lang("title",1);?>'
  }, {
    dataIndex: 'artist',
    sortable: true,
    header: '<?=lang("artist",1);?>',
    renderer: renderArtist
  }, {
    hidden: true,
    dataIndex: 'album',
    sortable: true,
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
    dataIndex: 'track',
    sortable: true,
    header: '<?=lang("num",1);?>',
    width: 20
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
    dataIndex: 'duration',
    sortable: true,
    header: '<?=lang("duration",1);?>',
    width: 30,
    renderer: GridRenderDuration,
    align: 'right'
  }, {
    hidden: true,
    dataIndex: 'full_path',
    sortable: true,
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
  if ($_SESSION["permission"]["has_access"]
  OR $_SESSION["permission"]["has_access"]
  OR $_SESSION["permission"]["has_access"]
  OR $_SESSION["permission"]["has_access"]
  OR $_SESSION["permission"]["has_access"]
  OR $_SESSION["permission"]["has_access"]) { ?> , actions <?php
  } ?> ]);
  cm.defaultSortable = true;

  function history_jump() {
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
    store.baseParams.albumID = queryStore[history_position]["albumID"];
    store.baseParams.artistID = queryStore[history_position]["artistID"];
    store.baseParams.field_filter = queryStore[history_position]["field_filter"];
    store.baseParams.query = queryStore[history_position]["query"];
    store.baseParams.fields = queryStore[history_position]["fields"];
    store.baseParams.full_text_search = queryStore[history_position]["full_text_search"];
    Ext.getCmp("main_search_full_text_fieldID").setValue(queryStore[history_position]["full_text_search"]);
    store.lastOptions.params[store.paramNames.limit] = queryStore[history_position]["limit"];
    store.lastOptions.params[store.paramNames.start] = queryStore[history_position]["start"];
    store.reload();
  }

  function history_button_status() {
    if (history_position == 0) Ext.getCmp("history_back").disable();
    else Ext.getCmp("history_back").enable();
    if ((queryStore.length - 1) > history_position) Ext.getCmp("history_forward").enable();
    else Ext.getCmp("history_forward").disable();
  }

  var filter_store_data = [
    ['filename', '<?=lang("filename",1);?>'],
    ['title', '<?=lang("title",1);?>'],
    ['artist', '<?=lang("artist",1);?>'],
    ['album', '<?=lang("album",1);?>'],
    ['genre', '<?=lang("genre",1);?>']
  ];
  // create the data store
  var filter_store = new Ext.data.SimpleStore({
    fields: [{
      name: 'value'
    }, {
      name: 'text'
    }]
  });
  filter_store.loadData(filter_store_data);

  var grid = new Ext.grid.GridPanel({
    title: title,
    ddGroup: 'PlaylistDDGroup',
    enableDragDrop: true,
    border: false,
    id: alias + "_list",
    ds: ds,
    cm: cm,
    enableColLock: false,
    loadMask: true,
    enableHdMenu: false,
    tbar: [{
      iconCls: 'icon-left',
      id: 'history_back',
      disabled: 'true',
      text: '<?=lang("back",1);?>',
      handler: function() {
        history_position = history_position - 1;
        history_jump();
      }
    }, {
      iconCls: 'icon-right',
      id: 'history_forward',
      disabled: 'true',
      text: '<?=lang("forward",1);?>',
      handler: function() {
        history_position = history_position + 1;
        history_jump();
      }
    }],
    height: 1000,
    plugins: [
    new Ext.ux.grid.Search({
      iconCls: 'icon-zoom',
      readonlyIndexes: [],
      searchText: 'Search',
      tbPosition: 11,
      position: top,
      paramNames: {
        fields: 'fields',
        query: 'full_text_search',
        from: 'fulltext'
      },
      no_button: true,
      disableIndexes: ['filename', 'filemtime', 'fileatime', 'artistID', 'album', 'albumID', 'genre', 'rating', 'comment', 'year', 'track', 'filesize', 'duration', 'full_path', 'num_downloads', 'num_plays', 'bit_rate'],
      minChars: 4,
      myid: 'main_search_full_text_fieldID',
      autoFocus: true
    }), new Ext.ux.PanelResizer({
      minHeight: 100
    }), actions],

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
      pageSize: <?= ENTRIES_IN_MP3_LIST; ?> , displayMsg: "<?=lang('sum_entries',1);?>",
      displayInfo: false,
      plugins: new Ext.ux.SlidingPager(),
      listeners: {
        beforechange: function() {
          history_position++;
          queryStore = cut_array_to_n_elements(queryStore, history_position);
        }
      }
    }),

    listeners: {
      sortchange: function(g, sort) {},
      render: function() {}
    }
  });

  grid.getColumnModel().setHidden(1, true)
  Ext.apply(grid, {});
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
        //Ext.getCmp("mp3_list").selModel.clearSelections();
        //Ext.getCmp("mp3_list").selModel.selectRow(rowIndex);
        if (!player_obj.currently_playingID) {
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
      limit: <?= ENTRIES_IN_MP3_LIST; ?>
    }
  });

  return grid;
}