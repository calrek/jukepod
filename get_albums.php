<?php
include "config/config.php";
include "inc/database.php";
include "inc/functions.php";
include "locale/language.php";

$db = new db();

$query = "SELECT
		album.ID,
		if(album.cover_resized='1',CONCAT('cover/album/resized/',album.ID,'.jpg'),'img/cover_no_album.gif') as cover,
		if(album.name!='',album.name,'[".$lang["unknown"]."]') as name
	FROM
		album,song,artist
	WHERE
		artist.ID = ".$_REQUEST["ID"]."
		AND artist.ID = song.artistID
		AND album.ID = song.albumID
		AND trim(album.name) != ''
	GROUP BY album.ID
	ORDER BY album.name";
$result = $db->query( $query );
echo "<div class='artist_list_template'><div class='album_from_artist'>";
if ( $db->num_rows( $result ) ) {
	while ( $row = $db->fetch_array( $result ) ) {
		echo "
		<div class='coverCotainer'>
			<div onmouseover=\"album_selection_mouse_in(this)\" onmouseout=\"album_selection_mouse_out(this)\" class='album_from_artist_entry' onClick='load_album(".$row["ID"].",this)'>
				<div class='album_cover_row'><img src='".$row["cover"]."'></div>
				<div class='album_name_row'><a href='javascript: void(0)'>".$row["name"]."</a></div>
			</div>
		</div>";
	}
}
else {
	echo "<div class='album_from_artist_nothing'>".lang( "no_albums_found" )."</div>";
}
echo "</div></div>";
?>
