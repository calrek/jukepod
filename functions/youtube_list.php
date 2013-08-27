<?php
include "../config/config.php";
include "../locale/language.php";

include "../inc/database.php";
include "../inc/functions.php";
include "../security.php";

if (! $_SESSION ["permission"] ["youtube"])
	html_no_permission ();

require_once 'Zend/Loader.php'; // the Zend dir must be in your include_path
Zend_Loader::loadClass ( 'Zend_Gdata_YouTube' );

searchAndPrint ( $_REQUEST ["artist"] . " " . $_REQUEST ["title"] . " video" );
function searchAndPrint($searchTerms = "") {
	$yt = new Zend_Gdata_YouTube ();
	$yt->setMajorProtocolVersion ( 2 );
	$query = $yt->newVideoQuery ();
	$query->setOrderBy ( 'relevance' );
	// $query->setCategory('music');
	$query->setSafeSearch ( 'none' );
	$query->setMaxResults ( 10 );
	$query->setVideoQuery ( $searchTerms );
	
	// Note that we need to pass the version number to the query URL function
	// to ensure backward compatibility with version 1 of the API.
	$videoFeed = $yt->getVideoFeed ( $query->getQueryUrl ( 2 ) );
	printVideoFeed ( $videoFeed );
	/*
	 * foreach ($videoFeed as $videoEntry) { ?> <object width="425" height="344"> <param name="movie" value="http://www.youtube.com/v/<?php echo $videoEntry->getVideoId();?>&hl=de&fs=1&rel=0&autoplay=1"></param> <param name="allowFullScreen" value="false"></param> <param name="allowscriptaccess" value="always"></param> <embed src="http://www.youtube.com/v/<?php echo $videoEntry->getVideoId();?>&hl=de&fs=1&rel=0&autoplay=1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="false" width="425" height="344"></embed> </object> <?php }
	 */
}
function printVideoFeed($videoFeed) {
	echo "<div class='youtube_container'>";
	$i = 0;
	if (sizeof ( $videoFeed )) {
		foreach ( $videoFeed as $videoEntry ) {
			$i ++;
			printVideoEntry ( $videoEntry );
		}
	}
	if (! $i) {
		echo "<div class='no_results'>" . lang ( "no_videos_found" ) . "</div>";
	}
	echo "</div>";
}
function printVideoEntry($videoEntry) {
	echo "<div class='youtube_video_container'>";
	echo "<div class='left'>";
	echo "<div class='image'><img src='http://i.ytimg.com/vi/" . $videoEntry->getVideoId () . "/default.jpg'></div>";
	echo "</div>";
	echo "<div class='right'>";
	echo "<div class='title'><a onClick=\"open_youtube_url('" . $videoEntry->getVideoId () . "')\" href='javascript: void(0)'>" . ($videoEntry->getVideoTitle ()) . "</a></div>";
	$desc = $videoEntry->getVideoDescription ();
	if (strlen ( $desc ) > 50)
		$desc = substr ( $desc, 0, 47 ) . "...";
	echo "<div class='description'>" . $desc . "</div>";
	$rating_array = $videoEntry->getVideoRatingInfo ();
	if ($rating_array ["numRaters"])
		echo "<div class='rating'>" . str_repeat ( "<img src='img/silk/icons/star.png'>", round ( $rating_array ["average"] ) ) . "</div>";
	echo "<div class='views'>" . number_format ( $videoEntry->getVideoViewCount (), 0, ',', '.' ) . " " . lang ( "views" ) . "</div>";
	echo "</div>";
	echo "</div>";
	
	// the videoEntry object contains many helper functions
	// that access the underlying mediaGroup object
	/*
	 * echo 'Video: ' . $videoEntry->getVideoTitle() . "\n"; echo 'Video ID: ' . $videoEntry->getVideoId() . "\n"; echo 'Updated: ' . $videoEntry->getUpdated() . "\n"; echo 'Description: ' . $videoEntry->getVideoDescription() . "\n"; echo 'Category: ' . $videoEntry->getVideoCategory() . "\n"; echo 'Tags: ' . implode(", ", $videoEntry->getVideoTags()) . "\n"; echo 'Watch page: ' . $videoEntry->getVideoWatchPageUrl() . "\n"; echo 'Flash Player Url: ' . $videoEntry->getFlashPlayerUrl() . "\n"; echo 'Duration: ' . $videoEntry->getVideoDuration() . "\n"; echo 'View count: ' . $videoEntry->getVideoViewCount() . "\n"; echo 'Rating: ' . $videoEntry->getVideoRatingInfo() . "\n"; echo 'Geo Location: ' . $videoEntry->getVideoGeoLocation() . "\n"; echo 'Recorded on: ' . $videoEntry->getVideoRecorded() . "\n"; // see the paragraph above this function for more information on the // 'mediaGroup' object. in the following code, we use the mediaGroup // object directly to retrieve its 'Mobile RSTP link' child foreach ($videoEntry->mediaGroup->content as $content) { if ($content->type === "video/3gpp") { echo 'Mobile RTSP link: ' . $content->url . "\n"; } } echo "Thumbnails:\n"; $videoThumbnails = $videoEntry->getVideoThumbnails(); foreach($videoThumbnails as $videoThumbnail) { echo $videoThumbnail['time'] . ' - ' . $videoThumbnail['url']; echo ' height=' . $videoThumbnail['height']; echo ' width=' . $videoThumbnail['width'] . "\n"; }
	 */
}
?>
