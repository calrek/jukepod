<?php

header('Content-type: text/xml');

include("config/config.php");
include("locale/language.php");

include("inc/database.php");
include("inc/functions.php");
include("security.php");
?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<?php
		if($_REQUEST["sort_dir"])
			$dir = $_REQUEST["sort_dir"];
		else
			$dir = "ASC";

		if($_REQUEST["sort"]=="ID")
			$sort = "if(artistID!=0,0,1),artist ".$dir.",ID ".$dir;

		else
			$sort = "ID ".$dir;

		$query = "SELECT ID,filename,full_path,artistID,title,artist FROM song WHERE ID != '' ";

		if($_REQUEST["query"] and $_REQUEST["query"]!="undefined") {

			$fields = substr($_REQUEST["fields"],1,strlen($_REQUEST["fields"])-2);
			$fields_array = explode(",",$fields);

			for($i=0;$i<sizeof($fields_array);$i++) {

				$field = str_replace("\\\"","",$fields_array[$i]);
				$where_query[] = $field." LIKE '%".$_REQUEST["query"]."%'";
			}

			$query .= " AND (".implode(" OR ",$where_query).")";
		}

		$query .= " ORDER BY ".$sort;
		$result = $db->query($query);

		while ($row = $db->fetch_array($result)) {

			if(!$row["cover_resized"]) {

				$link = DOMAIN."/cover/song/orig/".$row["ID"].".jpg";
				$thumb = DOMAIN."/cover/song/resized/".$row["ID"].".jpg";

			} else {

				$link = DOMAIN."img/cover_no_album.gif";
				$thumb = DOMAIN."img/cover_no_album.gif";

			}

			if(!$row["artist"])
				$artist = $lang["various"];

			else
				$artist = $row["artist"];

			$file = str_replace ( array ( '&', '"', "'", '<', '>', '?' ), array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&apos;' ), $row["filename"] );
		?>
		<item>
		    <title><?=htmlspecialchars($row["filename"]);?></title>
		    <link><?=$link;?></link>
			<guid><?=$row["ID"];?></guid>
		    <media:thumbnail url="<?=$link;?>"/>
		   <media:content url='<?php echo DOMAIN; ?>/jwplayer/player.swf?file=<?=SERVER."/".$row["full_path"]."/".$file;?>' type='application/x-shockwave-flash' width='300' height='300' />
		</item>
		 <?php
		}
		?>
    <atom:link rel="vorherige" href="album_feed.php?page=<?=$previous_page;?>" />
    <atom:link rel="naechste" href="album_feed.php?page=<?=$next;?>" />
	</channel>
</rss>

