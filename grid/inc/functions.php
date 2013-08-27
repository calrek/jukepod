<?php

function build_query($type) {

	global $lang;

	// $where = build_where($query["filter_names"]);
	$query = array ();
	$query ["where"] = "";

	switch ($type) {

		case "album" :
			$query ["fields"] = "album.ID,
			album.ID as albumID,
			if(album.name!='',album.name,'[" . $lang ["unknown"] . "]') as name,
			if(album.cover_resized='1',CONCAT('cover/album/resized/',album.ID,'.jpg'),'img/cover_no_album.gif') as cover,
			if(album.artistID,album.artist,'[" . $lang ["various"] . "]') as artist,
			album.artistID,
			album.num_files,
			replace(if(album.name!='',album.name,'[" . $lang ["unknown"] . "]'),'\\\"','&quot;') as name_slashes,
			replace(if(album.artistID,album.artist,'[" . $lang ["various"] . "]'),'\\\"','&quot;') as artist_slashes
			";

			$query ["from"] = "album";
			$query ["where"] = " AND num_files > 0 AND name!=''";
			break;
		case "artist" :
			$query ["fields"] = "artist.ID, CONCAT(if(artist.name!='',artist.name,'[" . $lang ["unknown"] . "]'),' (',CAST(artist.num_files AS CHAR),')') as name";
			$query ["from"] = "artist";
			$query ["where"] = " AND num_files > 0";
			break;
		case "playlist" :
			$query ["no_cache"] = 1;
			if ($_REQUEST ["playlistID"] >= 0) {
				$query ["fields"] = "song.ID,
				song.cover_url,
				song.filename,
				song.filemtime,
				song.fileatime,
				song.title,
				song.artist,
				song.artistID,
				song.album,
				song.albumID,
				song.genre,
				(
				SELECT rating
				FROM song_rating
				WHERE song.ID = song_rating.songID
				AND song_rating.userID = " . $_SESSION ["userID"] . "
				) as rating,
				song.comment,
				song.year,
				song.track,
				song.filesize,
				song.duration,
				song.full_path,
				song.num_downloads,
				song.num_plays,
				song.bit_rate,
				playlist_song.sort_order
				";

				$query ["from"] = "playlist, playlist_song, song";
				$query ["data_table"] = "song";
				$query ["where"] = " AND playlist.ID = playlist_song.playlistID AND playlist_song.songID = song.ID AND playlist.ID = " . $_REQUEST ["playlistID"] . " ORDER BY playlist_song.sort_order";
			} else {
				switch ($_REQUEST ["playlistID"]) {
					case "-1" :
						$playlist = "latest";
						$orderby = "fileatime desc";
						break;
					case "-2" :
						$playlist = "top";
						$orderby = "num_plays desc";
						break;
					case "-3" :
						$playlist = "played";
						$orderby = "last_played desc";
						break;
					case "-4" :
					case "-5" :
					case "-6" :
						// 5-Sterne, >= 4-Sterine, >= 3-Sterne
						$playlist = "best";
						$orderby = "num_plays desc";
						break;
				}
				$query ["fields"] = "song.ID,
				song.cover_url,
				song.filename,
				song.filemtime,
				song.fileatime,
				song.title,
				song.artist,
				song.artistID,
				song.album,
				song.albumID,
				song.genre,
				(
				SELECT rating
				FROM song_rating
				WHERE song.ID = song_rating.songID
				AND song_rating.userID = " . $_SESSION ["userID"] . "
				) as rating,
				song.comment,
				song.year,
				song.track,
				song.filesize,
				song.duration,
				song.full_path,
				song.num_downloads,
				song.num_plays,
				song.bit_rate,
				0 as sort_sorder
				";

				$query ["from"] = "song";
				$query ["data_table"] = "song";

				switch ($_REQUEST ["playlistID"]) {
					case - 3 :
						$query ["where"] = " AND last_played != 0 ORDER BY " . $orderby . " LIMIT " . ENTRIES_IN_TOP_PLAYLIST;
						break;
					case - 4 :
						$query ["where"] = " AND (
						SELECT rating
						FROM song_rating
						WHERE song.ID = song_rating.songID
						AND song_rating.userID = " . $_SESSION ["userID"] . "
						) = 5 ORDER BY " . $orderby . " LIMIT " . ENTRIES_IN_TOP_PLAYLIST;
						break;
					case - 5 :
						$query ["where"] = " AND (
						SELECT rating
						FROM song_rating
						WHERE song.ID = song_rating.songID
						AND song_rating.userID = " . $_SESSION ["userID"] . "
						) >= 4 ORDER BY " . $orderby . " LIMIT " . ENTRIES_IN_TOP_PLAYLIST;
						break;
					case - 6 :
						$query ["where"] = " AND (
						SELECT rating
						FROM song_rating
						WHERE song.ID = song_rating.songID
						AND song_rating.userID = " . $_SESSION ["userID"] . "
						) >= 3 ORDER BY " . $orderby . " LIMIT " . ENTRIES_IN_TOP_PLAYLIST;
						break;
					default :
						$query ["where"] = " ORDER BY " . $orderby . " LIMIT " . ENTRIES_IN_TOP_PLAYLIST;
						break;
				}
			}
			break;
		case "mp3" :
			$query ["fields"] = "song.ID,
			song.cover_url,
			song.filename,
			song.filemtime,
			song.fileatime,
			song.title,
			song.artist,
			song.artistID,
			song.album,
			song.albumID,
			song.genre,
			(
			SELECT rating
			FROM song_rating
			WHERE song.ID = song_rating.songID
			AND song_rating.userID = " . $_SESSION ["userID"] . "
			) as rating,
			song.comment,
			song.year,
			song.track,
			song.filesize,
			song.duration,
			song.full_path,
			song.num_downloads,
			song.num_plays,
			song.bit_rate
			";

			$query ["from"] = "song";
			$query ["data_table"] = "song";

			if ($_REQUEST ["field_filter"] && $_REQUEST ["letter_filter"]) {
				if ($_REQUEST ["letter_filter"] == "#")
					$query ["where"] .= " AND ASCII(" . $_REQUEST ["field_filter"] . ") >=48 AND ASCII(" . $_REQUEST ["field_filter"] . ") <= 57";
				elseif ($_REQUEST ["letter_filter"] == "!")
					$query ["where"] .= " AND
				!(
				(ASCII(" . $_REQUEST ["field_filter"] . ") >=48 AND ASCII(" . $_REQUEST ["field_filter"] . ") <= 57)
				OR
				(ASCII(" . $_REQUEST ["field_filter"] . ") >=65 AND ASCII(" . $_REQUEST ["field_filter"] . ") <= 90)
				OR
				(ASCII(" . $_REQUEST ["field_filter"] . ") >=97 AND ASCII(" . $_REQUEST ["field_filter"] . ") <= 122)
				)";
				else
					$query ["where"] .= " AND " . $_REQUEST ["field_filter"] . " LIKE '" . $_REQUEST ["letter_filter"] . "%'";
			}
			if ($_REQUEST ["full_path"]) {
				$query ["where"] .= " AND full_path LIKE \"" . ($_REQUEST ["full_path"]) . "%\"";
			}

			if ($_REQUEST ["albumID"])
				$query ["where"] .= " AND albumID = '" . $_REQUEST ["albumID"] . "'";

			if ($_REQUEST ["artistID"])
				$query ["where"] .= " AND artistID = '" . $_REQUEST ["artistID"] . "'";

				// $query["count"] = "SELECT COUNT(ID) FROM song WHERE ".$where;
			break;
	}

	$query = check_cache ( $query );

	return $query;
}
function check_cache($query) {
	global $db;

	// echo "/* start: ";
	// print_r($query);
	// echo "*/";

	$where = build_where () . $query ["where"];

	$query ["count"] = "SELECT count(*) FROM " . $query ["from"] . " WHERE " . $where;

	$sortnlimit = finalize_query ( $query ["secondary_sort"] );

	$query ["data"] = "SELECT " . $query ["fields"] . " FROM " . $query ["from"] . " WHERE " . $where . $sortnlimit;

	if (strstr ( $where, "rating" ) or strstr ( $sortnlimit, "rating" ))
		$query ["no_cache"] = 1;

	if (! $query ["no_cache"]) {
		$q = "SELECT ID,num_total FROM cache WHERE query = '" . md5 ( $query ["data"] ) . "'";
		$result = $db->query ( $q ) or mysql_die ();

		if ($db->num_rows ( $result )) {
			// echo "/* yes */";
			$query ["cache_num_results"] = $db->result ( $result, 0, "num_total" );
			$query ["cacheID"] = $db->result ( $result, 0, "ID" );

			if (! $query ["data_table"])
				$query ["data_table"] = $query ["from"];

			$query ["data"] = "SELECT " . $query ["fields"] . " FROM " . "cache_entry," . $query ["from"] . " WHERE
			cache_entry.rowID = " . $query ["data_table"] . ".ID AND cache_entry.cacheID = " . $query ["cacheID"] . finalize_query ( $query ["secondary_sort"], 1 );
		} else {
			// echo "/* no */";
		}
	}

	// echo "/* returning: ";
	// print_r($query);
	// echo "*/";

	return $query;

	/*
	 * { $query = "SELECT songID FROM cache_song WHERE cacheID = '".$db->result($result,0,0)."'"; $result = $db->query ($query) or mysql_die(); }
	 */
}
function build_where($filter_name = array()) {
	$filter = $_REQUEST ["filter"];

	$where = " 0 = 0 ";
	if (is_array ( $filter )) {
		for($i = 0; $i < count ( $filter ); $i ++) {
			$field = $filter [$i] ['field'];
			if (array_key_exists ( $field, $filter_name ))
				$field = $filter_name [$field];

			switch ($filter [$i] ['data'] ['type']) {
				case 'string' :
					$qs .= " AND " . $field . " LIKE \"%" . ($filter [$i] ['data'] ['value']) . "%\"";
					break;
				case 'list' :
					if (strstr ( $filter [$i] ['data'] ['value'], ',' )) {
						$fi = explode ( ',', $filter [$i] ['data'] ['value'] );
						for($q = 0; $q < count ( $fi ); $q ++) {
							$fi [$q] = "'" . $fi [$q] . "'";
						}
						$filter [$i] ['data'] ['value'] = implode ( ',', $fi );
						$qs .= " AND " . $field . " IN (" . $filter [$i] ['data'] ['value'] . ")";
					} else {
						$qs .= " AND " . $field . " = \"" . ($filter [$i] ['data'] ['value']) . "\"";
					}
					break;
				case 'boolean' :
					$qs .= " AND " . $field . " = " . ($filter [$i] ['data'] ['value']);
					break;
				case 'numeric' :
					switch ($filter [$i] ['data'] ['comparison']) {
						case 'eq' :
							$qs .= " AND " . $field . " = " . $filter [$i] ['data'] ['value'];
							break;
						case 'lt' :
							$qs .= " AND " . $field . " < " . $filter [$i] ['data'] ['value'];
							break;
						case 'gt' :
							$qs .= " AND " . $field . " > " . $filter [$i] ['data'] ['value'];
							break;
					}
					break;
				case 'date' :
					switch ($filter [$i] ['data'] ['comparison']) {
						case 'eq' :
							$qs .= " AND " . $field . " = '" . date ( 'Y-m-d', strtotime ( $filter [$i] ['data'] ['value'] ) ) . "'";
							break;
						case 'lt' :
							$qs .= " AND " . $field . " < '" . date ( 'Y-m-d', strtotime ( $filter [$i] ['data'] ['value'] ) ) . "'";
							break;
						case 'gt' :
							$qs .= " AND " . $field . " > '" . date ( 'Y-m-d', strtotime ( $filter [$i] ['data'] ['value'] ) ) . "'";
							break;
					}
					break;
			}
		}
		$where .= $qs;
	}

	if ($_REQUEST ["fields"] and $_REQUEST ["query"]) {
		$fields_array = json_decode ( stripslashes ( $_REQUEST ["fields"] ) );
		$query_part = array ();
		for($i = 0; $i < sizeof ( $fields_array ); $i ++) {
			$field = $fields_array [$i];
			if (array_key_exists ( $field, $filter_name ))
				$field = $filter_name [$field];

			$query_part [] = $field . " LIKE \"%" . ($_REQUEST ["query"]) . "%\"";
		}
		$where .= " AND (" . implode ( " OR ", $query_part ) . ")";
	}
	if ($_REQUEST ["full_text_search"]) {
		$where .= " AND MATCH(title,artist,album) AGAINST (\"" . $_REQUEST ["full_text_search"] . "\")";
	}

	return $where;
}

function finalize_query($secondary_sort, $no_limit = 0) {
	$start = ($_REQUEST ["start"] == null) ? 0 : $_REQUEST ["start"];
	$count = ($_REQUEST ["limit"] == null) ? 0 : $_REQUEST ["limit"];
	$sort = ($_REQUEST ["sort"] == null) ? "" : $_REQUEST ["sort"];
	$dir = ($_REQUEST ["dir"] == "desc" or $_REQUEST ["dir"] == "DESC") ? "DESC" : "";

	if ($sort == "track")
		$sort = "LPAD(track,10,'0')";

	if ($sort == "rating")
		$sort = "(
		SELECT rating
		FROM song_rating
		WHERE song.ID = song_rating.songID
		AND song_rating.userID = " . $_SESSION ["userID"] . "
		)";

	if ($sort != $_SESSION [$_REQUEST ["type"]] ["last_sort_field"]) {
		$_SESSION [$_REQUEST ["type"]] ["secondary_sort"] = $_SESSION [$_REQUEST ["type"]] ["last_sort_field"];
		$_SESSION [$_REQUEST ["type"]] ["secondary_sort_dir"] = $_SESSION [$_REQUEST ["type"]] ["last_sort_dir"];
	}

	if ($sort != "") {
		if ($sort == "artist" and $_REQUEST ["type"] == "album")
			$end .= " ORDER BY if(artistID!=0,0,1),artist " . $dir . ",name " . $dir;
		else
			$end .= " ORDER BY " . $sort . " " . $dir;

		if ($_SESSION [$_REQUEST ["type"]] ["secondary_sort"])
			$end .= ", " . $_SESSION [$_REQUEST ["type"]] ["secondary_sort"] . " " . $_SESSION [$_REQUEST ["type"]] ["secondary_sort_dir"];
	}
	if ($count and ! $no_limit)
		$end .= " LIMIT " . $start . "," . $count;

	$_SESSION [$_REQUEST ["type"]] ["last_sort_field"] = $sort;
	$_SESSION [$_REQUEST ["type"]] ["last_sort_dir"] = $dir;

	return $end;
}

function build_json($rs, $total, $query) {

	global $db;

	$arr = array ();

	$cache_query_part = array ();
	while ( $obj = $db->fetch_array ( $rs ) ) {
		$arr [] = $obj;
		$cache_query_part [] = "(*cacheID*," . $obj ["ID"] . ")";
		$num ++;
	}

	if (sizeof ( $cache_query_part ) and ! $query ["cacheID"] and ! $query ["no_cache"]) {
		$q = "INSERT INTO cache (query,num,num_total) VALUES ('" . md5 ( $query ["data"] ) . "'," . $num . "," . $total . ")";
		// echo "/* cache_query: ".$q." */";
		$db->query ( $q );
		$cacheID = $db->insert_id ();

		$q = "INSERT INTO cache_entry (cacheID,rowID) VALUES " . str_replace ( "*cacheID*", $cacheID, implode ( ",", $cache_query_part ) );
		// echo "/* cache_query: ".$q." */";
		$db->query ( $q );
	}

	return '{"total":"' . $total . '","data":' . json_encode ( $arr ) . '}';
}
?>
