<?php
@include "config/config.php";

$config_array = array(
	"DB_SERVER",
	"DB_USER",
	"DB_PASSWD",
	"DB_NAME",
	"MP3_PATH",
	"ALBUM_MATCH",
	"LANGUAGE",
	"COVER_SIZE_X",
	"COVER_SIZE_Y",
	"ENTRIES_IN_ALBUM_LIST",
	"ENTRIES_IN_MP3_LIST",
	"ENTRIES_IN_ARTIST_LIST",
	"ENTRIES_IN_TOP_PLAYLIST",
	"MIN_CHARS_IN_SEARCH",
	"SEARCH_DELAY_MS" );

@$album_match_imploded = implode( ",", $_ALBUM_MATCH );

for ( $i=0;$i<sizeof( $config_array );$i++ ) {
	if ( defined( $config_array[$i] ) or $config_array[$i]=="ALBUM_MATCH" ) {
		$fields_array[] = "{name: '".$config_array[$i]."'}";
		if ( $config_array[$i]=="DB_PASSWD" )
			$value_array[] = "\"".$config_array[$i]."\":\"".str_repeat( "*", strlen( constant( $config_array[$i] ) ) )."\"";
		elseif ( $config_array[$i]=="ALBUM_MATCH" )
			$value_array[] = "\"".$config_array[$i]."\":\"".$album_match_imploded."\"";
		else
			$value_array[] = "\"".$config_array[$i]."\":\"".constant( $config_array[$i] )."\"";
	}
}
?>
{
  'metaData':	{
		totalProperty: 'results',
    root: 'rows',
    fields:
			[
				<?=implode(",",$fields_array); ?>
			]
   },
  'results': 1,
	'rows':
		[
			{
			<?=implode(",",$value_array); ?>
			}
		]
}
