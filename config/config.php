<?php

session_start();
@set_time_limit(0);

define('DB_HOST',  "");	// database: host-name
define('DB_USER',  "");					// database: username
define('DB_PASSWORD',  "");		// database: password
define('DB_NAME', "");			// database: name

define('DOMAIN', 'http://' . $_SERVER['HTTP_HOST']);

define('MP3_PATH','H:/cloud/skydrive/media/Singles2012');  // path to MP3-files

define("LANGUAGE","en"); 						// language currently available: "de" (german) and "en" (english)

$_ALBUM_MATCH = array("Folder","front","Front","jpg","JPG"); // filename pattern of album-cover-art

define("COVER_SIZE_X",150);	// width of album-cover-art thumbnail
define("COVER_SIZE_Y",150);	// height of album-cover-art thumbnail

define("COOLIRIS_SIZE_X",630);	// width of Cooliris-album-cover-window
define("COOLIRIS_SIZE_Y",370);	// height of Cooliris-album-cover-window
define("COOLIRIS_ROWS",3);			// number of rows in Cooliris-album-cover-window
define("COOLIRIS_REFLECTIONS","false");			// true/false for showing/hiding reflections in Cooliris (has an impact on performance)

define("DISABLE_COVER_DOWNLOAD",false);	// disables cover download from freecover.net

define("ENTRIES_IN_ALBUM_LIST",30);		// number of entries per page in album list
define("ENTRIES_IN_MP3_LIST",200);			// number of entries per page in mp3 list
define("ENTRIES_IN_ARTIST_LIST",30);	// number of entries per page in artist list
define("ENTRIES_IN_TOP_PLAYLIST",50);	// number of entries in "top"- and "latest"-playlist
define("MIN_CHARS_IN_SEARCH",1);			// minimum characters to type into the sarchbox before a search starts
define("SEARCH_DELAY_MS",600);				// delay in milliseconds after that the search starts

define("GETID3_MYSQL_ENCODING", "UTF-8");
define("GETID3_MYSQL_MD5_DATA",false);

define("LOW_MEMORY_MODE",0);					// set this to 1 if you have problems to play or download bigger files

error_reporting(E_ALL ^ E_NOTICE);
?>
