<?php
class mp3_tree {
	function __construct($id) {
		$this->db = new db ();

		if (! $id) {
			$this->root = MP3_PATH;
			$this->parentID = 0;
		} else {
			$query = "SELECT full_path,parentID FROM dir WHERE ID = " . $id;
			$result = $this->db->query ( $query );
			if ($this->db->result ( $result, 0, "full_path" )) {
				$this->root = MP3_PATH . "/" . $this->db->result ( $result, 0, "full_path" );
				$this->import_path = $this->db->result ( $result, 0, "full_path" );
				$this->rootID = $this->db->result ( $result, 0, "parentID" );
			} else {
				$this->root = MP3_PATH;
				$this->rootID = 0;
				$this->import_path = "";
			}
		}

		$options_array = $this->options_array ();
		for($i = 0; $i < sizeof ( $options_array ); $i ++) {
			$key = $options_array [$i];
			if (array_key_exists ( $key, $_GET )) {
				$this->options [strtoupper ( $key )] = $_GET [$key];
			} else {
				$this->options [strtoupper ( $key )] = 0;
			}
		}

		if (! $this->options ["SCAN_METHOD"])
			$this->options ["SCAN_METHOD"] = "ALL";

		if ($this->options ["RESET_DB"]) {
			$query = "TRUNCATE table song";
			$result = $this->db->query ( $query );

			$query = "TRUNCATE table dir";
			$result = $this->db->query ( $query );

			$query = "TRUNCATE table album";
			$result = $this->db->query ( $query );

			$query = "TRUNCATE table artist";
			$result = $this->db->query ( $query );

			$query = "TRUNCATE table playlist_song";
			$result = $this->db->query ( $query );
		}

		$this->files_done = 0;
		$this->new_files = 0;
		$this->dirs_done = 0;

		$this->tag ["album"] = array ();
		$this->tag ["artist"] = array ();

		$this->getID3 = new getID3 ();
		$this->getID3->setOption ( array (
				'option_md5_data' => GETID3_MYSQL_MD5_DATA,
				'encoding' => GETID3_MYSQL_ENCODING
		) );
	}
	function options_array() {
		return array (
				"reset_db",
				"no_html_output",
				"js_output",
				"in_frame",
				"scan_method"
		);
	}
	function set_status($status) {
		$result = $this->db->query ( "SELECT status FROM import_status" );
		if ($this->db->num_rows ( $result ))
			$query = "UPDATE import_status SET status = '" . $status . "',date = '" . date ( "Y-m-d H:i:s", time () ) . "'";
		else
			$query = "INSERT INTO import_status (status,date) VALUES ('" . $status . "','" . date ( "Y-m-d H:i:s", time () ) . "')";
		$this->db->query ( $query );
		return true;
	}
	function get_status() {
		$query = "SELECT * FROM import_status";
		$result = $this->db->query ( $query );
		return $this->db->fetch_array ( $result );
	}
	function set_var($name, $value) {
		$this->$name = $value;
	}
	function js_output($js) {
		if ($this->options ["JS_OUTPUT"]) {
			echo "<script>" . $js . "</script>\n";
			ob_flush ();
			flush ();
		}
	}
	function html_output($html) {
		if (! $this->options ["NO_HTML_OUTPUT"]) {
			echo $html;
			if ($this->options ["IN_FRAME"])
				echo "<br />\n";
			ob_flush ();
			flush ();
		}
	}
	function process_tree() {
		$this->js_output ( "parent.pb_scanning.wait({ interval:200,increment:15 });" );
		// $query = "UPDATE song SET new = '0'";
		// $this->db->query($query);
		$query = "UPDATE song SET found = '0'";
		if ($this->import_path)
			$query .= " where full_path LIKE '" . $this->import_path . "%'";
		$this->db->query ( $query );
		$count = $this->count_files ( $this->root, $this->rootID );
		$query = "SELECT count(*) FROM song WHERE new = '1'";
		$result = $this->db->query ( $query );
		$this->new_files = $this->db->result ( $result, 0, 0 );
		$this->js_output ( "parent.pb_scanning.reset();" );
		$this->js_output ( "parent.pb_scanning.updateProgress(1);" );
		$this->read_tags ();
		$this->js_output ( "parent.pb_fields.wait({ interval:200,increment:15 });" );
		$this->finish ();
		$this->js_output ( "parent.pb_fields.reset();" );
		$this->js_output ( "parent.pb_fields.updateProgress(1);" );
		$this->js_output ( "parent.all_done();" );
	}
	function count_files($from, $parentID = 0) {
		$num_files = 0;
		$num_dirs = 0;

		if ($parentID == $this->rootID) {
			$result_array = $this->get_dirID ( $from, $parentID );
			$parentID = $result_array ["dirID"];
		}

		@$dh = opendir ( $from );
		if ($dh) {
			while ( false !== ($entry = readdir ( $dh )) ) {
				if ($entry != '.' and $entry != '..') {
					$path = $from . '/' . $entry;
					if (is_dir ( $path ) and $this->options ["SCAN_METHOD"] != "SCAN_ONLY_FILES_IN_CURRENT_DIR") {
						$num_dirs ++;
						$this->num_dirs_total ++;
						$result_array = $this->get_dirID ( $path, $parentID );
						$id = $result_array ["dirID"];
						$new = $result_array ["new"];
						if ($new or $this->options ["SCAN_METHOD"] != "SCAN_ONLY_NEW") {
							$count_array = $this->count_files ( $path, $id );
							$num_files += $count_array ["num_files"];
							$num_dirs += $count_array ["num_dirs"];
							// $this->num_dirs_total += $count_array["num_dirs"];
						}
					} elseif ($this->extension_ok ( $entry )) {
						$num_files ++;
						$this->check_file ( $entry, $from, $parentID );
						$this->num_files_total ++;

						$this->js_output ( "parent.pb_scanning.updateText('" . number_format ( $this->num_files_total, 0, ',', '.' ) . " " . lang ( "files_found", 1 ) . "')" );
						$this->html_output ( number_format ( $this->num_files_total, 0, ',', '.' ) . " " . lang ( "files_found", 1 ) );
					}
				}
			}
		}
		return array (
				"num_files" => $num_files,
				"num_dirs" => $num_dirs
		);
	}
	function convert_tags($fileinfo) {
		$tag_array = array (
				"artist",
				"title",
				"genre",
				"track",
				"album",
				"year",
				"year",
				"comment"
		);

		for($i = 0; $i < sizeof ( $tag_array ); $i ++) {
			if ($tag_array [$i] == "track") {
				$id3v1 = trim ( $fileinfo ["tags"] ["id3v1"] ["track"] [0] );
				$id3v2 = trim ( $fileinfo ["tags"] ["id3v2"] ["track_number"] [0] );
			} elseif ($tag_array [$i] == "comment") {
				$id3v1 = trim ( $fileinfo ["tags"] ["id3v1"] ["comment"] [0] );
				$id3v2 = trim ( $fileinfo ["tags"] ["id3v2"] ["comments"] [0] );
			} else {
				$id3v1 = trim ( $fileinfo ["tags"] ["id3v1"] [$tag_array [$i]] [0] );
				$id3v2 = trim ( $fileinfo ["tags"] ["id3v2"] [$tag_array [$i]] [0] );
			}

			if ($id3v1 and $id3v2) {
				if (strlen ( $id3v2 ) > strlen ( $id3v1 ))
					$fileinfo [$tag_array [$i]] = $id3v2;
				else
					$fileinfo [$tag_array [$i]] = $id3v1;
			} elseif ($id3v2 and ! $id3v1)
				$fileinfo [$tag_array [$i]] = $id3v2;
			elseif ($id3v1 and ! $id3v2)
				$fileinfo [$tag_array [$i]] = $id3v1;
		}
		return $fileinfo;
	}
	function cut_full_path($path) {
		if ($path == MP3_PATH)
			return "";
		else
			return substr ( $path, (strlen ( $path ) - strlen ( MP3_PATH . "/" )) * - 1 );
	}
	function get_dirID($from, $parentID) {
		if (! $parentID)
			$dir = "";
		else {
			$path_array = explode ( "/", $from );
			$dir = $path_array [sizeof ( $path_array ) - 1];
		}

		$query = "SELECT
								ID
							FROM
								dir
							WHERE
								name = '" . addslashes ( $dir ) . "'
								AND parentID = '" . $parentID . "'";
		$result = $this->db->query ( $query );
		if (! mysql_num_rows ( $result )) {
			$query = "INSERT INTO
									dir
									(name,full_path,parentID)
									VALUES
									(
									'" . addslashes ( $dir ) . "',
									'" . $this->cut_full_path ( addslashes ( $from ) ) . "',
									'" . $parentID . "')";
			$this->db->query ( $query );
			$dirID = $this->db->insert_id ();
			$new = 1;
		} else {
			$dirID = $this->db->result ( $result, 0, 0 );
			$new = 0;
		}

		return array (
				"new" => $new,
				"dirID" => $dirID
		);
	}
	function get_tagID($tag, $name, $full_path = "") {
		global $db;
		if (! $full_path)
			$full_path = 0;

		if ($this->tag [$tag] [$name] [$full_path])
			$id = $this->tag [$tag] [$name] [$full_path];
		else {
			$query = "SELECT
									ID
								FROM
									" . $tag . "
								WHERE
									name = '" . addslashes ( $name ) . "'";
			if ($full_path and $name)
				$query .= " AND full_path = '" . addslashes ( $full_path ) . "'";
			$result = $this->db->query ( $query );
			if (! $this->db->num_rows ( $result )) {
				if ($full_path and $name) {
					$query = "INSERT INTO
										" . $tag . "
										(name,full_path)
										VALUES
										('" . addslashes ( $name ) . "','" . addslashes ( $full_path ) . "')";
				} else {
					$query = "INSERT INTO
											" . $tag . "
											(name)
											VALUES
											('" . addslashes ( $name ) . "')";
				}
				$this->db->query ( $query );
				$id = $this->db->insert_id ();
			} else {
				$id = $this->db->result ( $result, 0, 0 );
			}
		}
		$this->tag [$tag] [$name] [$full_path] = $id;

		return $id;
	}
	function update_song($fileinfo, $id, $full_path) {
		global $db;

		$albumID = $this->get_tagID ( "album", $fileinfo ["album"], $full_path );
		$artistID = $this->get_tagID ( "artist", $fileinfo ["artist"] );

		$query = "UPDATE
								song
							SET
								title = '" . addslashes ( $fileinfo ["title"] ) . "',
								artistID = '" . $artistID . "',
								albumID = '" . $albumID . "',
								new = '0',
								artist = '" . addslashes ( $fileinfo ["artist"] ) . "',
								album = '" . addslashes ( $fileinfo ["album"] ) . "',
								genre = '" . addslashes ( $fileinfo ["genre"] ) . "',
								track = '" . addslashes ( $fileinfo ["track"] ) . "',
								year = '" . addslashes ( $fileinfo ["year"] ) . "',
								comment = '" . addslashes ( $fileinfo ["comment"] ) . "',
								bit_rate = '" . addslashes ( $fileinfo ["bitrate"] ) . "',
								filesize = '" . $fileinfo ["filesize"] . "',
								filemtime = '" . $fileinfo ["filemtime"] . "',
								duration = '" . $fileinfo ["playtime_seconds"] . "'
							WHERE
								ID = " . $id;
		$this->db->query ( $query );

		return $id;
	}
	function check_file($entry, $path, $parentID) {
		$query = "SELECT ID,filemtime FROM song WHERE dirID = " . $parentID . " AND filename = '" . addslashes ( $entry ) . "'";
		$result = $this->db->query ( $query );
		if ($this->db->num_rows ( $result )) {
			$filemtime_from_db = $this->db->result ( $result, 0, "filemtime" );
			$id = $this->db->result ( $result, 0, "ID" );
			$filemtime = filemtime ( $path . "/" . $entry );
			if ($filemtime > $filemtime_from_db) {
				$query = "UPDATE song SET new = '1',found='1' WHERE ID = " . $id;
				$this->db->query ( $query );
			} else {
				$query = "UPDATE song SET new = '0',found='1' WHERE ID = " . $id;
				$this->db->query ( $query );
			}
			return $id;
		} else {
			$query = "INSERT INTO
									song
									(
									full_path,
									filename,
									filemtime,
									fileatime,
									dirID)
								VALUES
									(
									'" . $this->cut_full_path ( addslashes ( $path ) ) . "',
									'" . addslashes ( $entry ) . "',
									'" . filemtime ( $path ) . "',
									'" . time () . "',
									'" . $parentID . "'
									)";
			$this->db->query ( $query );
			$id = $this->db->insert_id ();
			return $id;
		}
	}
	function read_tags() {
		$query = "SELECT ID,filename,full_path FROM song WHERE new = '1'";
		$result = $this->db->query ( $query );
		if ($this->db->num_rows ( $result )) {
			while ( $row = $this->db->fetch_array ( $result ) ) {
				$fileinfo = $this->getID3->analyze ( MP3_PATH . "/" . $row ["full_path"] . "/" . $row ["filename"] );
				$fileinfo = $this->convert_tags ( $fileinfo );
				$fileinfo ["filemtime"] = filemtime ( MP3_PATH . "/" . $row ["full_path"] . "/" . $row ["filename"] );
				$this->update_song ( $fileinfo, $row ["ID"], $row ["full_path"] );
				$this->report_progress ( $row ["filename"] );
			}
		} else {
			$this->html_output ( lang ( "no_files_to_read", 0 ) );
			$this->js_output ( "parent.pb_file.updateProgress( 1, '" . lang ( "no_files_to_read", 1 ) . "', true );" );
		}
	}
	function report_progress($current_obj) {
		$this->files_done ++;
		$this->html_output ( lang ( "files", 1 ) . ": " . $this->nf ( $this->files_done ) . "/" . $this->nf ( $this->new_files ) . ", " . lang ( "currently_processing", 1 ) . ": <b>" . addslashes ( $current_obj ) . "</b>" );
		$this->js_output ( "parent.pb_file.updateProgress( " . ($this->files_done / $this->new_files) . ", '" . $this->nf ( $this->files_done ) . "/" . $this->nf ( $this->new_files ) . ": " . addslashes ( $current_obj ) . "', true );" );
	}
	function nf($value) {
		return number_format ( $value, 0, ',', '.' );
	}
	function extension_ok($entry) {
		$pathinfo = pathinfo ( $entry );
		switch (strtolower ( $pathinfo ["extension"] )) {
			case "mp3" :
				return true;
			default :
				return false;
		}
	}
	function finish() {
		$query = "UPDATE dir SET num_files_total = 0";
		$result = $this->db->query ( $query );

		if ($this->options ["SCAN_METHOD"] == "ALL") {
			$query = "DELETE from song WHERE found = '0'";
			if ($this->import_path)
				$query .= " AND full_path LIKE '" . $this->import_path . "%'";
			$result = $this->db->query ( $query );
		}

		$query = "UPDATE dir SET num_files = (SELECT if(count(*),count(*),0) FROM song WHERE song.dirID = dir.ID)";
		$result = $this->db->query ( $query );

		$query = "SELECT ID FROM dir";
		$result = $this->db->query ( $query );
		while ( $row = $this->db->fetch_array ( $result ) ) {
			$num_dirs = $this->count_dirs ( $row ["ID"] );
			$num_files_total = $this->count_total_files ( $row ["ID"] );

			$query = "UPDATE dir SET num_dirs = '" . $num_dirs . "', num_files_total = '" . $num_files_total . "' where ID = " . $row ["ID"];
			$this->db->query ( $query );
		}
		$query = "DELETE from dir WHERE num_files_total = 0";
		$result = $this->db->query ( $query );

		$query = "DELETE song.* FROM song LEFT JOIN dir ON (song.dirID = dir.ID) where dir.ID IS NULL";
		$result = $this->db->query ( $query );

		$query = "UPDATE album SET num_files = 0";
		$result = $this->db->query ( $query );
		$query = "UPDATE album SET album.num_files = (SELECT count(*) FROM song WHERE album.ID = song.albumID group by album.ID)";
		$result = $this->db->query ( $query );
		$query = "DELETE from album WHERE num_files = 0";
		$result = $this->db->query ( $query );

		$query = "UPDATE artist SET num_files = 0";
		$result = $this->db->query ( $query );
		$query = "UPDATE artist SET artist.num_files = (SELECT count(*) FROM song WHERE artist.ID = song.artistID group by artist.ID)";
		$result = $this->db->query ( $query );
		$query = "DELETE from artist WHERE num_files = 0";
		$result = $this->db->query ( $query );

		$query = "SELECT ID FROM album";
		$result = $this->db->query ( $query );
		while ( $row = $this->db->fetch_array ( $result ) ) {
			$query = "SELECT
			if( min( song.artist ) = max( song.artist ) , min( song.artist ) , '' ) as artist,
			if( min( song.artistID ) = max( song.artistID ) , min( song.artistID ) , '0' ) as artistID
			FROM album, song
			WHERE song.albumID = album.ID
			AND albumID = " . $row ["ID"] . "
			GROUP BY album.ID";
			$result_temp = $this->db->query ( $query );
			$artist = trim ( $this->db->result ( $result_temp, 0, "artist" ) );
			$artistID = $this->db->result ( $result_temp, 0, "artistID" );

			if ($artist)
				$query = "UPDATE album SET artist = '" . addslashes ( $artist ) . "',artistID = '" . $artistID . "' WHERE ID = " . $row ["ID"];
			else
				$query = "UPDATE album SET artist = '',artistID = '' WHERE ID = " . $row ["ID"];

			$this->db->query ( $query );
		}
	}
	function count_dirs($id) {
		$query = "SELECT count(*) FROM dir WHERE parentID = " . $id;
		$num_result = $this->db->query ( $query );
		return $this->db->result ( $num_result, 0, 0 );
	}
	function count_total_files($id) {
		$query = "SELECT num_files FROM dir WHERE ID = " . $id;
		$num_result = $this->db->query ( $query );
		$num_files = $this->db->result ( $num_result, 0, 0 );

		$query = "SELECT ID FROM dir WHERE parentID = " . $id;
		$result = $this->db->query ( $query );
		while ( $row = $this->db->fetch_array ( $result ) ) {
			$num_files += $this->count_total_files ( $row ["ID"] );
		}

		return $num_files;
	}
}
?>
