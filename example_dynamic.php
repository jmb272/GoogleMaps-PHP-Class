<?php
include 'googlemaps.php';

/* Initialize the map object */
$map = new GoogleMap(array(
    'mapID' => 'my_map',
	'zoomLevel' => 15,
	'containerID' => 'map_container',
    'centerCoords' => array('lat' => '37.422858', 'lng' => '-122.085065'),
));

/**
 * Add a marker to the map.
 * When adding a marker, you must either provide a property address
 * or a latitude (lat) and longitude (lng) on which to centre the map.
 */
$map->addMarker(array(
	'addr' => 'center', 
	'title' => 'Google Headquarters, click me!', 
	'content' => '<h2>Popup window!</h2>',
));

?>	
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title></title>
<!-- Load jQuery -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" ></script>
<!-- Style the map container, most importantly setting its size -->
<style>
div#map_container {
    width:500px;
    height:300px;
    display:block;
}
</style>
<!--
    Load the Google Maps API. If your working locally, just remove YOUR_API_KEY.
    If your code is going online you need to get an API key.
    
    See here: https://developers.google.com/maps/documentation/javascript/tutorial#api_key
-->
<?php GoogleMap::loadAPI(); ?>
<!--
    Generate the map. The createMap function will generate the javascript
    required to create the map and any markers it has.
-->
<?php $map->createMap(true); ?>
</head>
<body>

<!-- A div container where the map will be stored -->
<div id="map_container"></div>

<p>Google Maps PHP Class by <a href="http://blog.james-bailey.com/">James Bailey</a>.</p>

</body>
</html> 