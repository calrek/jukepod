<?php

if (!function_exists('str_getcsv')) {

    function str_getcsv($input, $delimiter = ",", $enclosure = '"', $escape = "\\") {

        $MBs = 1 * 1024 * 1024;
        $fp = fopen("php://temp/maxmemory:$MBs", 'r+');
        fputs($fp, $input);
        rewind($fp);

        $data = fgetcsv($fp, 1000, $delimiter, $enclosure); //  $escape only got added in 5.3.0

        fclose($fp);
        return $data;

    }

}

function images_google($artist, $title, $num = 3, $size = 'huge') {

	if (empty($artist)) return false;

		$url = 'http://www.google.com/search?tbm=isch&hl=en&source=lnt&q='.urlencode($artist).((!empty($title)) ? '+'.urlencode($title): '').'+cover&hl=en&gbv=2&tbs=isz:m,itp:photo&source=lnt';

	if (empty($title)) { # ARTIST

		#$url .= '&imgsz=huge|xxlarge';
		$url .= '&imgtype=face';

	} else {

		$url .= '&imgsz=large|xlarge';
	}

	$result = false;

	if ($html = file_get_contents($url)) {

		$split = preg_split('/"\/imgres/is', $html);

		if (!empty($split) && sizeof($split) >= $num) {

			array_shift($split);

			for($i=0; $i < $num; $i++) {

				$parts = str_getcsv($split[$i]);

				$modpart = preg_match('#([a-z0-9-._~%]ttp:[a-z0-9\-._~%!$&\'()*+,;=:@/]+)jpg#i', $parts[0], $urlpart);

				if (is_array($parts)) {

					$result[] = array(
						'image_url' => $urlpart[0],
						'image_src' => $url,
						'thumbnail_url' => $parts[14].'?q=tbn:'.$parts[2],
					);
				}
			}
		}
	}
	return $result;
}

function resizePicture($image,$max_width,$max_height) {

        $image = str_replace("//","/",$image);

        if (!file_exists($image)) {
			return false;
        }

		$size	= GetImageSize($image);

		$width	= $size[0];
		$height	= $size[1];
		$type	= $size[2];

		if ($width>$max_width) {

			$height = $max_width/$width * $height;
			$width  = $max_width;

		}

          if ($height>$max_height) {

              $width  = $max_height/$height * $width;
              $height = $max_height;

          }

        if ($type == 2) {

			$src = imagecreatefromjpeg($image);
			$im = imagecreatetruecolor($width,$height);
			ImageCopyResized($im,$src,0,0,0,0,$width,$height,$size[0],$size[1]);
			ob_start(); //Start capturing stdout.
			ImageJPEG($im); //As though you were going to send to the browser.
			$imag = ob_get_contents();     //Save bit of stdout we want for later.
			ob_end_clean();
			return $imag;

        } else {

            return null;

        }
    }
?>