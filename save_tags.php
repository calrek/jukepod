<?php
include("config/config.php");
include("locale/language.php");
include("inc/database.php");
include("inc/functions.php");
include("security.php");

if(!$_SESSION["permission"]["edit_tags"])
	json_no_permission();

$db = new db();
$TaggingFormat = 'UTF-8';

require_once('getid3/getid3/getid3.php');
// Initialize getID3 engine
$getID3 = new getID3;
$getID3->setOption(array('encoding'=>$TaggingFormat));

require_once('getid3/getid3/write.php');
// Initialize getID3 tag-writing module
$tagwriter = new getid3_writetags;
//$tagwriter->filename       = '/path/to/file.mp3';
$tagwriter->filename       = MP3_PATH."/".stripslashes(($_POST["full_path"]))."/".stripslashes(($_POST["old_filename"]));
$tagwriter->tagformats     = array('id3v1', 'id3v2.4');

// set various options (optional)
$tagwriter->overwrite_tags = true;
$tagwriter->tag_encoding   = $TaggingFormat;
//$tagwriter->remove_other_tags = true;

// populate data array
$TagData['title'][]   = stripslashes($_POST["title"]);
$TagData['artist'][]  = stripslashes($_POST["artist"]);
$TagData['album'][]   = stripslashes($_POST["album"]);
$TagData['genre'][]   = stripslashes($_POST["genre"]);
$TagData['track'][]   = stripslashes($_POST["track"]);
$TagData['year'][]   = stripslashes($_POST["year"]);

echo "/* ";
print_r($TagData);
echo " */";

$tagwriter->tag_data = $TagData;

// write tags
if ($tagwriter->WriteTags()) {
	$success = "true";
	$query_part = "title = '".$_POST["title"]."'";
	$query_part .= ", artist = '".$_POST["artist"]."'";
	$query_part .= ", album = '".$_POST["album"]."'";
	$query_part .= ", genre = '".$_POST["genre"]."'";
	$query_part .= ", track = '".$_POST["track"]."'";
	$query_part .= ", year = '".$_POST["year"]."'";
	
	if($_POST["old_filename"]!=$_POST["filename"])
	{
		if(check_filename(stripslashes($_POST["filename"])))
		{
			echo "/* old_path: ".MP3_PATH."/".stripslashes($_POST["full_path"])."/".stripslashes($_POST["old_filename"])."*/";
			echo "/* new_path: ".MP3_PATH."/".stripslashes($_POST["full_path"])."/".stripslashes($_POST["filename"])."*/";
			$rename_success = rename(MP3_PATH."/".stripslashes($_POST["full_path"])."/".stripslashes($_POST["old_filename"]),MP3_PATH."/".stripslashes($_POST["full_path"])."/".stripslashes($_POST["filename"]));
		}
	
		if($rename_success)
		{
			$query_part .= ", filename = '".$_POST["filename"]."'"; 
		}
		else
		{
			$msg = lang("error_renaming_file");
		}
	}
	
	if($_POST["old_artist"]!=$_POST["artist"])
	{
		$artistID = get_tagID("artist",$_POST["artist"]);
		$query_part .= ", artistID = ".$artistID;
	}
	if($_POST["old_album"]!=$_POST["album"])
	{
		$albumID = get_tagID("album",$_POST["album"]);
		$query_part .= ", albumID = ".$albumID;
	}
	
	$query = "SELECT albumID,artistID FROM song WHERE ID = ".$_POST["ID"];
	$result = $db->query($query);
	$old_values = $db->fetch_array($result);
	
	$query = "UPDATE song SET ".$query_part." WHERE ID = ".$_POST["ID"];
	echo "/* ".$query." */";
	$db->query($query);
	
	if($_POST["old_album"]!=$_POST["album"])
	{
		$query = "UPDATE album SET album.num_files = (SELECT count(*) FROM song WHERE album.ID = song.albumID group by album.ID) WHERE ID = '".$albumID."' OR ID = '".$old_values["albumID"]."'";
		echo "/* ".$query." */";
		$result = $db->query($query);
		$query = "DELETE from album WHERE num_files = 0 AND (ID = '".$albumID."' OR ID = '".$old_values["albumID"]."')";
		echo "/* ".$query." */";
		$result = $db->query($query);
	}
		
	if($_POST["old_artist"]!=$_POST["artist"])
	{
		$query = "UPDATE artist SET artist.num_files = (SELECT count(*) FROM song WHERE artist.ID = song.artistID group by artist.ID) WHERE ID = '".$artistID."' OR ID = '".$old_values["artistID"]."'";
		echo "/* ".$query." */";
		$result = $db->query($query);
		$query = "DELETE from artist WHERE num_files = 0 AND (ID = '".$artistID."' OR ID = '".$old_values["artistID"]."')";
		echo "/* ".$query." */";
		$result = $db->query($query);
	}

	if (!empty($tagwriter->warnings)) {
		$msg = implode('<br><br>', $tagwriter->warnings);
	}
} else {
	$success = "false";
	$msg = implode('<br><br>', $tagwriter->errors);
}

function check_filename($filename)
{
	$invailid_chars = array("\\", "/", ":", "*", "?", "\"", "<", ">", "|");
	
	if(!$filename) return false;
	
	for($i=0;$i<sizeof($invailid_chars);$i++)
	{
		if(strpos($filename,$invalid_chars[$i])) return false;
	}
	return true;
}

function get_tagID($tag,$name)
{
	global $db;
	
	$query = "SELECT 
							ID 
						FROM 
							".$tag."
						WHERE 
							name = '".addslashes($name)."'";
	$result = $db->query($query);
	if (!$db->num_rows($result))
	{
		$query = "INSERT INTO
								".$tag."
								(name)
								VALUES
								('".addslashes($name)."')";
		$db->query($query);
		$id = $db->insert_id();
	}
	else
		$id = $db->result($result,0,0);

	return $id;
}
?>
{
    success: <?=$success;?>,
    msg: '<?=$msg;?>'
}
