<?php
include("config/config.php");
include("locale/language.php");
include("inc/database.php");
include("inc/functions.php");
include("get_covergoogle.php");
include("security.php");

define("COVER_SIZE_X",150);
define("COVER_SIZE_Y",150);

if($_REQUEST["songID"] AND file_exists("cover/song/orig/".$_REQUEST["songID"].".jpg")){

	$bild = file_get_contents("cover/song/orig/".$_REQUEST["songID"].".jpg");

} elseif ($_REQUEST["songID"]!="" ){

		$regs = images_google($_REQUEST["artist"], $_REQUEST["title"],'1','huge');

		$url = $regs[0]['image_url'];


	if($url){

		$bild = file_get_contents($url);

		if(!file_exists("cover/song/orig/".$_REQUEST["songID"].".jpg") AND $_REQUEST["artist"] and $_REQUEST["title"]) {

			file_put_contents("cover/song/orig/".$_REQUEST["songID"].".jpg", $bild);

			$resized_image = resizePicture("cover/song/orig/".$_REQUEST["songID"].".jpg",COVER_SIZE_X,COVER_SIZE_Y);

			if($resized_image){

				file_put_contents("cover/song/resized/".$_REQUEST["songID"].".jpg",$resized_image);

			}
		}
	}
}

if(!$bild){

	$bild = file_get_contents("img/cover_no_album.gif");

}

$length = strlen($bild);

header('Last-Modified: '.date('r'));
header('Accept-Ranges: bytes');
header('Content-Length: '.$length);
header('Content-Type: image/jpeg');

echo $bild;
?>
