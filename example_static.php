<?php
include 'googlemaps.php';

/* Initialize static map object. */
$static_map = new GoogleMap_Static(array(
	'size' => '500x300',
	'zoomLevel' => 15,
	'centerCoords' => array('lat' => '37.422858', 'lng' => '-122.085065'),
	'imgAttrs' => array('class' => 'static_map')
));

/* Add a marker in the centre of the map. */
$static_map->addMarker(array(
	'addr' => 'center',
	'color' => 'red',
	'label' => 'G'
));

?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>GoogleMaps PHP Class by James Bailey</title>
</head>
<body>

<?php 
/**
 * Create the map image element.
 */
$static_map->createMap(); 

/**
 * You could also get the image URL and display the image yourself.
 *
 * $image_url = $static_map->createMap(true);
 * <img src="$image_url" alt="">
 */
?>

<p>Google Maps PHP Class by <a href="http://blog.james-bailey.com/">James Bailey</a>.</p>

</body>
</html>