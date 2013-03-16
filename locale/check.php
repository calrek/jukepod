<?php
include "en.php";

$en = $lang;

@$dh = opendir( "." );
if ( $dh ) {
	while ( false !== ( $entry = readdir( $dh ) ) ) {
		if ( $entry != '.' and $entry != '..' and $entry != 'language.php' and $entry != "en.php" and $entry != "check.php" and !is_dir( $entry ) ) {
			echo "<b>File ".$entry."</b><br />";
			$lang = array();
			include $entry;
			reset( $en );
			$found = 0;
			for ( $i=0;$i<sizeof( $en );$i++ ) {
				$key = key( $en );
				$current = current( $en );

				if ( !array_key_exists( $key, $lang ) ) {
					if ( !$found )
						echo "missing strings:<br />";
					echo "\$lang[\"".$key."\"] = \"".addslashes( $current )."\"<br />";
					$found++;
				}
				next( $en );
			}
			echo $found." strings are missing";
			echo "<hr>";
		}
	}
}
?>
