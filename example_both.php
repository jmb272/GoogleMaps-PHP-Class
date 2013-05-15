<?php

include 'googleMaps.php';

// Create Google Map object, initialize with relevant data.
$map = new GoogleMap(array(
	// Give this object its own, unique map ID. This is required, especially if multiple maps are used.
	'mapID' => 'google_hq',
	// Set the zoom level, the higher the number the more zoomed out the map will initialize as.
	'zoomLevel' => 15,	
	// The ID of the html element container of the map.
	'containerID' => 'map_canvas',
	// The type of map you wish to display. roadmap (default), satellite, hybrid or terrain.
	'mapTypeId' => 'roadmap',
	// The coordinates the map should centre on.
	//'centerCoords' => GoogleMap::findCoordsByAddr('1600 Amphitheatre Parkway, Mountain View, CA 94043, USA')
	'centerCoords' => array('lat' => '37.422858', 'lng' => '-122.085065'),
));

/**
 * Add a marker to the map.
 * When adding a marker, you must either provide a property address
 * or a latitude (lat) and longitude (lng) on which to centre the map.
 */
$map->addMarker(array(
	'addr' => 'center', 
	'title' => 'Google Headquarters', 
	'content' => '',
	'color' => 'blue'
));

?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>GoogleMaps PHP Class by James Bailey</title>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
	<?php GoogleMap::loadAPI(); ?>
	<script>
		$(document).ready(function() {
			$('.static_map').remove();
		});
	</script>
	<?php $map->createMap(true); ?>
</head>
<body>

	<div id="map_canvas" style="width:500px;height:300px;display:block;border:2px solid #ccc;">
		<?php 
			// create static map incase of no javascript.
			$staticMap = new GoogleMap_Static(array(
				'size' => '500x300',
				'zoomLevel' => 15,
				'mapTypeId' => 'roadmap',
				'centerCoords' => $map->centerCoords,
				'imgAttrs' => array(
					'class' => 'static_map'
				)
			));
			
			$staticMap->addMarker(array('addr' => 'center', 'color' => 'red', 'label' => 'H'));
			$staticMap->createMap();
		?>
	</div>
	<!-- #map_canvas -->
	
	<br><br>
	
	<p>Google Maps PHP Class by <a href="http://blog.james-bailey.com/">James Bailey</a>.</p>

</body>
</html>