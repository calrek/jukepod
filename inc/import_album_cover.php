<?php

class import_album_cover {

  function __construct( $id ) {

    $this->db = new db();

    $options_array = $this->options_array();
    for ( $i = 0;$i<sizeof( $options_array );$i++ ) {
      $key = $options_array[$i];
      if ( array_key_exists( $key, $_GET ) ) {
        $this->options[strtoupper( $key )] = $_GET[$key];
      } else {
        $this->options[strtoupper( $key )] = 0;
      }
    }

    if ( $id )
      $this->root_id = $id;
    else
      $this->root_id = 0;
  }

  function options_array() {

    return array( "no_html_output", "js_output", "in_frame" );
  }

  function process_albums() {

    global $_ALBUM_MATCH;

    $query = "SELECT ID,full_path as path,name FROM album WHERE name != ''";
    if ( $this->root_id ) {
      $query_temp = "SELECT full_path FROM dir WHERE ID = ".$this->root_id;
      $result_temp = $this->db->query( $query_temp );
      if ( $this->db->num_rows( $result_temp ) )
        $query .= " AND full_path LIKE \"".addslashes( $this->db->result( $result_temp, 0, 0 ) )."%\"";
    }

    $result = $this->db->query( $query );
    $num_done = 0;
    $num_added = 0;
    $num_total = $this->db->num_rows( $result );
    while ( $row = $this->db->fetch_array( $result ) ) {
      $num_done++;

      $this->html_output( $num_done."/".$num_total.": ".lang( "cover_looking_for", 0 )." <b>".$row["name"]."</b>" );
      $this->js_output( "parent.pb_album.updateProgress(".( $num_done/$num_total ).", '".$num_done."/".$num_total.": ".lang( "cover_looking_for", 1 )." ".addslashes( $row["name"] )."');" );

      $dh = opendir( MP3_PATH."/".$row["path"] );
      if ( $dh ) {
        $cover_gefunden = 0;
        $entry_array = array();
        while ( false!==( $entry = readdir( $dh ) ) ) {
          if ( $entry!='.'and$entry!='..'and!is_dir( $entry )and$this->extension_ok( $entry ) ) {
            $entry_array[] = $entry;
          }
        }

        for ( $i = 0;$i<sizeof( $_ALBUM_MATCH )and!$cover_gefunden;$i++ ) {
          for ( $j = 0;
            $j<sizeof( $entry_array )and!$cover_gefunden;
            $j++ ) {
            $entry = $entry_array[$j];
            if ( strstr( strtolower( $entry ), strtolower( $_ALBUM_MATCH[$i] ) ) ) {
              $this->html_output( $num_done."/".$num_total.": ".lang( "cover_found_for", 0 )." <b>".$row["name"]."</b>" );
              $this->js_output( "parent.pb_album.updateText( '".$num_done."/".$num_total.": ".lang( "cover_found_for", 1 )." ".addslashes( $row["name"] )."');" );

              $cover_gefunden = 1;

              $query = "UPDATE album SET cover = \"".addslashes( $row["path"] )."/".$entry."\" WHERE ID = ".$row["ID"];
              $this->db->query( $query );

              $resized_image = $this->resizePicture( MP3_PATH."/".$row["path"]."/".$entry, COVER_SIZE_X, COVER_SIZE_Y );

              $num_added++;

              if ( $resized_image ) {
                file_put_contents( "cover/album/resized/".$row["ID"].".jpg", $resized_image );
                $query = "UPDATE album SET cover_resized = '1' WHERE ID = ".$row["ID"];

                /*echo "kopiere von <b>".MP3_PATH."/".$row["path"]."/".$entry."</b> nach <b>cover/album/orig/".$row["ID"].".jpg</b><br>";
                if(copy(MP3_PATH."/".$row["path"]."/".$entry  , "cover/album/orig/".$row["ID"].".jpg"))
                  echo "ok";
                else
                  echo "not ok<br>";*/

                $this->db->query( $query );
              }
            }
          }
        }
      }
    }
    $this->html_output( $num_added." ".lang( "cover_processed", 0 ) );
    $this->js_output( "parent.pb_album.updateProgress(1, '".$num_added." ".lang( "cover_processed", 1 )."');" );
    $this->js_output( "parent.all_done();" );
  }

  function extension_ok( $entry ) {

    $pathinfo = pathinfo( $entry );
    switch ( strtolower( $pathinfo["extension"] ) ) {
    case "jpg":
      return true;
    default:
      return false;
    }
  }

  function resizePicture( $image, $max_width, $max_height ) {

    $image = str_replace( "//", "/", $image );

    if ( !file_exists( $image ) ) {
      return false;
    }

    $size = getimagesize( $image );

    $width = $size[0];
    $height = $size[1];
    $type = $size[2];

    if ( $width>$max_width ) {
      $height = $max_width/$width*$height;
      $width = $max_width;
    }

    if ( $height>$max_height ) {
      $width = $max_height/$height*$width;
      $height = $max_height;
    }

    if ( $type==2 ) {
      $src = imagecreatefromjpeg( $image );
      $im = imagecreatetruecolor( $width, $height );
      ImageCopyResized( $im, $src, 0, 0, 0, 0, $width, $height, $size[0], $size[1] );
      ob_start();
      //Start capturing stdout.
      ImageJPEG( $im );
      //As though you were going to send to the browser.
      $imag = ob_get_contents();
      //Save bit of stdout we want for later.
      ob_end_clean();
      return $imag;
    } else {
      return null;
    }
  }

  function js_output( $js ) {

    if ( $this->options["JS_OUTPUT"] ) {
      echo "<script>".$js."</script>\n";
      ob_flush();
      flush();
    }
  }

  function html_output( $html ) {

    if ( !$this->options["NO_HTML_OUTPUT"] ) {
      echo $html;
      if ( $this->options["IN_FRAME"] )
        echo "<br />\n";
      ob_flush();
      flush();
    }
  }
}

?>
