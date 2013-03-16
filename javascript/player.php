<?php 
include("../config/config.php");
include("../locale/language.php");
include("../inc/database.php");
include("../inc/functions.php");
include("../security.php");

header("Content-type:text/javascript; charset=utf-8");

?>

function player() {

	this.currently_playing = 0;
	this.currently_playingID = 0;
	this.current_play_type = "";
	this.stop_playlist = false;
	this.play_random = false;
	this.source = "mp3_list";
	this.sourceID = "mp3_list";
	this.otherID = "playlistID";
	this.inited = 0;
	this.last_position = 0;
	this.mySound = false;
	this.time_display_mode = 1;
	this.volume = 100;
	this.stop_updating = false;
	this.current_data = false;
	this.played_songs = new Array();

	this.play_playlist = function(source) {
		this.stop_playlist = false;
		this.currently_playing = 0;
		this.play_step(source, 0);
	}

	this.play = function() {
		this.stop_playlist = false;

		var store = Ext.getCmp(this.sourceID).getStore();

		if (store.data.items.length) {
			var first_selected = Ext.getCmp(sourceID).selModel.getSelected();
			var start_index = Ext.getCmp(sourceID).getStore().indexOf(first_selected);
			this.play_index(start_index);
		}
	}

	this.play_index = function(index) {
		var store = Ext.getCmp(this.sourceID).getStore();
		this.currently_playingID = store.data.items[index].data.ID;
		this.current_data = store.data.items[index].data;

		Ext.getCmp(this.sourceID).selModel.clearSelections();
		Ext.getCmp(this.sourceID).selModel.selectRow(index);
		Ext.getCmp(this.otherID).selModel.clearSelections();

		this.play_song();
	}

	this.new_source = function(source) {
		this.source = source;
		switch (source) {
		case "playlist":
			this.sourceID = "playlistID";
			this.otherID = "mp3_list";
			break;
		case "mp3_list":
			this.sourceID = "mp3_list";
			this.otherID = "playlistID";
			break;
		}
	}

	this.prev_next = function(dir) {
		var store = Ext.getCmp(this.sourceID).getStore();
		var next_index = 0;

		if (this.currently_playingID) {
			if (this.list_random) {
				var available_songs = new Array();
				// welche songs stehen zur Verf�gung, die noch nicht gespielt wurden?
				for (var i = 0; i < store.data.items.length; i++) {
					if (!this.played_songs[store.data.items[i].data.ID]) {
						available_songs[available_songs.length] = store.data.items[i].data.ID;
					}
				}

				if (available_songs.length) {
					num = rand(0, available_songs.length - 1);
					next_index = this.search_index(available_songs[num]);
				} else {
					next_index = rand(0, store.data.length - 1);
					this.played_songs = new Array();
				}
			} else {
				var current_index = this.search_index(this.currently_playingID);
				if (current_index != -1) {
					if (dir > 0) {
						if ((current_index + 1) < store.data.items.length) next_index = current_index + 1;
						else next_index = 0;
					} else {
						if (current_index > 0) next_index = current_index - 1;
						else next_index = store.data.items.length - 1;
					}
				} else next_index = 0;
			}
		} else next_index = 0;

		this.play_index(next_index);
	}

	this.search_index = function(ID) {
		if (this.sourceID) {
			var store = Ext.getCmp(this.sourceID).getStore();

			for (var i = 0; i < store.data.items.length; i++) {
				if (store.data.items[i].data.ID == ID) {
					return i;
				}
			}
		}
		return -1;
	}

	this.play_step = function(source, dir) {
		this.stop_playlist = false;
		if (!source) source = this.source;

		this.source = source;
		switch (source) {
		case "playlist":
			var sourceID = "playlistID";
			var otherID = "mp3_list";
			break;
		case "mp3_list":
			var sourceID = "mp3_list";
			var otherID = "playlistID";
			break;
		}
		var store = Ext.getCmp(sourceID).getStore();

		if (store.data.length) {
			if (this.list_random) {
				this.currently_playing = rand(0, store.data.length - 1);
			} else {
				if (this.current_play_type != source) {
					first_selected = Ext.getCmp(sourceID).selModel.getSelected();
					if (first_selected) {
						this.currently_playing = Ext.getCmp(sourceID).getStore().indexOf(first_selected);
					} else {
						if (dir > 0) this.currently_playing = 0;
						else if (dir < 0) this.currently_playing = store.data.length - 1;
					}
				} else {
					if (!dir) {
						first_selected = Ext.getCmp(sourceID).selModel.getSelected();
						if (first_selected) this.currently_playing = Ext.getCmp(sourceID).getStore().indexOf(first_selected);
						else this.currently_playing = 0;
					} else this.currently_playing = this.currently_playing + dir;
				}

				this.current_play_type = source;

				if (this.currently_playing > store.data.length - 1) this.currently_playing = 0;

				if (this.currently_playing < 0) this.currently_playing = store.data.length - 1;

			}
			var data = store.data.items[this.currently_playing].data;

			Ext.getCmp(sourceID).selModel.clearSelections();
			Ext.getCmp(sourceID).selModel.selectRow(this.currently_playing);
			Ext.getCmp(otherID).selModel.clearSelections();
			this.play_song(data);
		}
	}

	this.save_playlist = function() {
		var storeArray = [];
		var count = 0;
		var serializedStore = false;
		if (Ext.getCmp("playlist_selectionID").getValue() > 0) {
			var pl = Ext.getCmp("playlist_selectionID");
			if (pl.selectedIndex >= 0) {
				var userID = pl.store.data.items[pl.selectedIndex].data.userID;
				if (!( <?php
				if ($_SESSION["permission"]["edit_all_playlists"]) echo "1";
				else echo "0"; ?> || (userID == <?= $_SESSION["userID"]; ?> && <?php
				if ($_SESSION["permission"]["has_access"]) echo "1";
				else echo "0"; ?> ))) return false;
			}

			if (!( <?php
			if ($_SESSION["permission"]["edit_all_playlists"]) echo "1";
			else echo "0"; ?> || (userID == <?= $_SESSION["userID"]; ?> && <?php
			if ($_SESSION["permission"]["has_access"]) echo "1";
			else echo "0"; ?> ))) return false;

			var store = Ext.getCmp("playlistID").getStore();
			for (i = 0; i < store.data.length; i++) {
				storeArray[count++] = store.data.items[i].data.ID;
			}
			serializedStore = Ext.encode(storeArray);
			Ext.getCmp("playlistID").getEl().mask("<?=lang('saving',1);?>");
			Ext.Ajax.request({
				url: 'playlist_handle.php',
				params: {
					id: Ext.getCmp('playlist_selectionID').getValue(),
					action: 'save_playlist',
					songIDs: serializedStore
				},
				success: function(form, action) {
					/*var store = Ext.getCmp("playlistID").getStore();
						if(store.lastOptions && store.lastOptions.params)
							store.lastOptions.params[store.paramNames.start] = 0;
						store.reload();*/
					Ext.getCmp("playlistID").getEl().unmask();
				},
				failure: function(form, action) {
					Ext.Msg.show({
						title: '<?=lang("error",1);?>',
						msg: '<?=lang("error_saving_playlist",1);?>',
						minWidth: 200,
						modal: true,
						icon: Ext.Msg.ERROR,
						buttons: Ext.Msg.OK
					});
					Ext.getCmp("playlistID").getEl().unmask();
				}
			})
		}
	}

	this.seek_percent = function(percent) {
		if (this.mySound) {
			soundManager.setPosition("my_soundID", Math.round(player_obj.duration * percent / 100 * 1000));
			player_obj.stop_updating = false;
		}
	}

	this.set_volume = function(percent) {
		this.volume = percent;
		soundManager.setVolume("my_soundID", percent);
	}

	this.pause_playlist = function(from) {
		if (this.mySound) {
			player_bar.enable();
			soundManager.setVolume("my_soundID", this.volume);
			soundManager.togglePause('my_soundID');
			if (this.mySound.paused) this.pause_button.setIconClass("icon-play");
			else this.pause_button.setIconClass("icon-pause");
		} else {
			this.play_index(0);
		}
	}

	this.stop_playing = function() {
		soundManager.stop('my_soundID');
		soundManager.unload('my_soundID');
		this.pause_button.setIconClass("icon-play");
		player_bar.setValue(0);
		player_bar.disable();
		this.digits(0);
		document.getElementById("ladebalken").style.width = "0px";
	}

	this.digits = function(seconds) {
		if (this.time_display_mode < 0) {
			sign = -1;
			seconds = parseInt(this.duration) - parseInt(seconds);
		} else sign = 1;

		var sec_1;
		var sec_2;
		var min_1;
		var min_2;
		var min_3;

		var minutes = Math.floor(seconds / 60);
		var seconds = seconds - minutes * 60;

		if (minutes < 10) min_2 = 0;
		else min_2 = Math.floor(minutes / 10);

		min_1 = minutes - min_2 * 10;

		if (seconds < 10) sec_2 = 0;
		else sec_2 = Math.floor(seconds / 10);

		sec_1 = seconds - sec_2 * 10;

		if (sign < 0) min_3 = 11;
		else min_3 = 10;

		this.set_digit("sec_1", sec_1);
		this.set_digit("sec_2", sec_2);
		this.set_digit("min_1", min_1);
		this.set_digit("min_2", min_2);
		this.set_digit("min_3", min_3);
	}

	this.toggle_time_display_mode = function() {
		if (this.time_display_mode < 0) this.time_display_mode = 1;
		else this.time_display_mode = -1;

		cp.set("time_display_mode", this.time_display_mode);

		this.digits(Math.round(this.mySound.position / 1000));
	}

	this.set_time_display_mode = function() {
		this.time_display_mode = cp.get("time_display_mode", 1);

		this.digits(Math.round(this.mySound.position / 1000));
	}

	this.set_digit = function(id, value) {
		if (isNaN(value)) value = 0;
		document.getElementById(id).style.backgroundPosition = value * -9 + "px 0px";
	}

	this.play_song = function() {
		if (this.inited == 0) {
			this.inited = 1;
			soundManager.onload = function() {
				player_obj.play_song();
			}
			soundManager.beginDelayedInit();
			return false;
		} else if (this.current_data) {
			data = this.current_data;
			this.extras_button.enable();

			this.played_songs[data.ID] = 1;

			var artist = htmlspecialchars_decode(data.artist);
			var title = htmlspecialchars_decode(data.title);
			var album = htmlspecialchars_decode(data.album);
			var track = htmlspecialchars_decode(data.track);
			var time = renderDuration(htmlspecialchars_decode(data.duration));
			var bit_rate = number_format(data.bit_rate / 1000, 0, ',', '.') + " kbps";
			var filename = (data.filename);
			var surdoc_url = htmlspecialchars_decode(data.surdoc_url);
			var full_path = (data.full_path);
			var currently_playing_array = new Array();
			var url = "";

			this.stop_playlist = false;
			this.duration = data.duration;

			if (data.artistID && artist) document.getElementById("info_artist").innerHTML = artist;
			else document.getElementById("info_artist").innerHTML = "-";

			if (title) document.getElementById("info_title").innerHTML = title;
			else document.getElementById("info_title").innerHTML = "-";

			if (data.albumID && album) document.getElementById("info_album").innerHTML = album;
			else document.getElementById("info_album").innerHTML = "-";

			if (full_path) document.getElementById("info_path").innerHTML = "<a href=\"javascript: void(0)\" onClick=\"open_path('" + data.full_path + "');\">" + full_path + "</a>";
			else document.getElementById("info_path").innerHTML = "-";

			if (bit_rate) document.getElementById("info_bitrate").innerHTML = "";
			else document.getElementById("info_bitrate").innerHTML = "-";

			if (time) document.getElementById("info_time").innerHTML = time;
			else document.getElementById("info_time").innerHTML = "-";

			if (track) document.getElementById("info_track").innerHTML = track;
			else document.getElementById("info_track").innerHTML = "-";

			var window_title = "";

			if (htmlspecialchars_decode(artist)) window_title = htmlspecialchars_decode(artist);

			if (htmlspecialchars_decode(artist) && htmlspecialchars_decode(title)) window_title = window_title + " - ";

			if (htmlspecialchars_decode(title)) window_title = window_title + htmlspecialchars_decode(title)

			if (window_title != "") document.title = window_title;
			else document.title = "<?=$lang['unknown_song'];?>";

			<?php
			if ($_SESSION["access_option"] == "direct") { 
			?> 
			url = surdoc_url;

				Ext.Ajax.request({
					url: 'num_plays.php',
					params: {
						ID: data.ID
					}
				});
			<?php
			} else {
			?> url = "stream.php?ID=" + data.ID + '&title=' + window_title; <?php
			}
			?>

			player_bar.setValue(0);

			if (this.mySound) soundManager.destroySound('my_soundID');

			document.getElementById("ladebalken").style.width = "0px";

			document.getElementById("info_cover").src = "get_cover.php?&songID=" + data.ID + "&artist=" + encodeURIComponent(artist) + "&title=" + encodeURIComponent(title);

			this.mySound = soundManager.createSound({
				id: 'my_soundID',
				url: Utf8.encode(url),
				volume: this.volume,
				onfinish: function() {
					player_obj.prev_next(1);
				},
				whileplaying: function() {
					if (Math.round(this.position / 1000) != this.last_position && !player_obj.stop_updating) {
						document.getElementById("debug").innerHTML = this.last_position;
						player_bar.setValue(Math.round(this.position / player_obj.duration / 1000 * 100));
						player_obj.digits(Math.round(this.position / 1000));
						this.last_position = Math.round(this.position / 1000);
					}
				},
				whileloading: function() {
					max_width = 178;
					document.getElementById("ladebalken").style.width = Math.round(this.bytesLoaded / this.bytesTotal * max_width) + "px";
				}
				// onload: [ event handler function object ],
				// other options here,
			});
			player_bar.enable();
			this.mySound.play();
			this.pause_button.setIconClass("icon-pause");
			this.set_volume(this.volume);

			//flash_player.sendEvent("PLAY","true");
			//mp3_list_obj.setTitle(currently_playing_array.join(", "));
			/*EP_loadMP3("ep_player","<location>" + (convert_path(path)) + "</location><creator>" + artist + "</creator><title>" + title + "</title>");
			EP_play("ep_player");*/
			/*Ext.Ajax.request({
				 url: 'num_plays.php',
				 params: { ID: data.ID }
				});*/
		}
	}
}