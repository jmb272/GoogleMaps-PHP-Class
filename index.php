<?php

include 'googleMaps.php';

// Create Google Map object, initialize with relevant data.
$map = new GoogleMap(array(
	'mapID' => 'google_hq',
	'zoomLevel' => 16,
	'containerID' => 'map_canvas',
	'mapTypeId' => 'roadmap',
	'centerCoords' => GoogleMap::findCoordsByAddr('1600 Amphitheatre Parkway, Mountain View, CA 94043, USA')
));
$map->addMarker(array(
	'addr' => 'center', 
	'title' => 'where i live', 
	'content' => ''
));

?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	
	<title>GoogleMaps PHP Class</title>
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
				'zoomLevel' => 16,
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
	
	<br>

</body>
</html>