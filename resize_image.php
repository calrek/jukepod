<?php
//header("Content-type: image/jpeg");

function createPicture($image,$max_width,$max_height){
        $image = str_replace("//","/",$image);
         
          if (!file_exists($image)) {
          }
         
     
         $size     = GetImageSize($image);
				 
				 print_r($size);
         $width     = $size[0];
         $height = $size[1];
         $type     = $size[2];
     
          if ($width>$max_width) {
              $height = $max_width/$width * $height;
              $width  = $max_width;
          }

          if ($height>$max_height) {
              $width  = $max_height/$height * $width;
              $height = $max_height;
          }
     
     
        if ($type == 2) {
               $src     = imagecreatefromjpeg($image);
               $im         = imagecreatetruecolor($width,$height);
               ImageCopyResized($im,$src,0,0,0,0,$width,$height,$size[0],$size[1]);
               ob_start();                     //Start capturing stdout.
               ImageJPEG($im);                 //As though you were going to send to the browser.
               $imag = ob_get_contents();     //Save bit of stdout we want for later.
               ob_end_clean();    
               return $imag;          
             
         } else {
					 echo "type: ".$type."<br>";
             return null;
         } 
    }
		
//echo createPicture("import_test.php",200,200);
include("config/config.php");
include("getid3/getid3/getid3.php");


$getID3 = new getID3;
$getID3->setOption(array(
	'option_md5_data' => GETID3_MYSQL_MD5_DATA,
	'encoding'        => GETID3_MYSQL_ENCODING,
));

$fileinfo = $getID3->analyze(MP3_PATH."/_Alben/Coldplay - Coldplay Live 2003/01 - Coldplay - Politik (Live).mp3");
$imagedata = $fileinfo["id3v2"]["APIC"][0]["data"];

/*$length = strlen($imagedata);
header('Last-Modified: '.date('r'));
header('Accept-Ranges: bytes');
header('Content-Length: '.$length);
header('Content-Type: image/jpeg');*/
print_r(getimagesize_raw($imagedata));
//ob_end_flush();


$imageData = file_get_contents("test.jpg");
//echo "data: ".$imageData."<br>";
echo "type: ".$imageData."<br>";

function getimagesize_raw($data){
		$cwd = getcwd(); #get current working directory
		$tempfile = tempnam("$cwd/tmp", "temp_image_");#create tempfile and return the path/name (make sure you have created tmp directory under $cwd
		$temphandle = fopen($tempfile, "w");#open for writing
		fwrite($temphandle, $data); #write image to tempfile
		fclose($temphandle);
		$imagesize = getimagesize($tempfile); #get image params from the tempfile
		unlink($tempfile); // this removes the tempfile
		return $imagesize;
}
?>

