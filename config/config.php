<?php

session_start();
@set_time_limit(0);
/*
define('DB_SERVER',  "us-cdbr-azure-west-b.cleardb.com");	// database: host-name
define('DB_USER',  "b1f0872301168c");					// database: username
define('DB_PASSWORD',  "22a3992e");		// database: password
define('DB_NAME', "jukepodDB");			// database: name
*/
define('DB_SERVER',  'localhost');	// database: host-name
define('DB_USER',  'root');					// database: username
define('DB_PASSWORD',  'wamp');		// database: password
define('DB_NAME', 'jb'); // database: name

define('USERID', $_SESSION['userID']);
define('USERNAME', $_SESSION['username']);

define('COPYURL', 'http://copy.com/1QbpwOpOjbMe/Top');

define('DOMAIN', 'http://' . $_SERVER['HTTP_HOST']);

define('MP3_PATH','D:/Copy/Copy/Top');  // path to MP3-files

define("LANGUAGE","en"); 						// language currently available: "de" (german) and "en" (english)

$_ALBUM_MATCH = array("Folder","front","Front","jpg","JPG"); // filename pattern of album-cover-art

define("COVER_SIZE_X",150);	// width of album-cover-art thumbnail
define("COVER_SIZE_Y",150);	// height of album-cover-art thumbnail

define("DISABLE_COVER_DOWNLOAD",false);	// disables cover download from freecover.net

define("ENTRIES_IN_ALBUM_LIST",30);		// number of entries per page in album list
define("ENTRIES_IN_MP3_LIST",100);			// number of entries per page in mp3 list
define("ENTRIES_IN_ARTIST_LIST",30);	// number of entries per page in artist list
define("ENTRIES_IN_TOP_PLAYLIST",50);	// number of entries in "top"- and "latest"-playlist
define("MIN_CHARS_IN_SEARCH",1);			// minimum characters to type into the sarchbox before a search starts
define("SEARCH_DELAY_MS",600);				// delay in milliseconds after that the search starts

define("GETID3_MYSQL_ENCODING", "UTF-8");
define("GETID3_MYSQL_MD5_DATA",false);

define("LOW_MEMORY_MODE",0);					// set this to 1 if you have problems to play or download bigger files

error_reporting(E_ALL ^ E_NOTICE);
?>
