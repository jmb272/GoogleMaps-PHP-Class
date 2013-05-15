<?php
/**
 * GoogleMaps classes
 * ------------------------------------------------
 * Author: James Bailey
 * Website: http://blog.james-bailey.com/google-maps-php-classes/
 * Version: 1.0
 * License: GPL
 */

/**
 * AbstractGoogleMap class
 */
abstract class AbstractGoogleMap
{	
	// Unique ID.
	public $mapID = false;
	public static $mapIDs = array();

	// Google Maps API.
	public $sensor = false;
	
	// Latitude & Longitude of map center.
	public $centerCoords = array();
	
	// Map Interface.
	public $scaleControl = true;
	public $streetViewControl = false;
	public $zoomLevel = 6;
	public $zoomControl = true;
	public $mapTypeControl = true;
	public $mapTypeId = 'roadmap';
	
	// Map Markers.
	public $markers = array();
	
	// Map Icons.
	public $icons = array();

	/**
	 * Class constructor.
	 *
	 * Set class fields upon instantiation.
	 *
	 * @param array $data
	 */
	public function __construct($data=array()) 
	{
		if (is_array($data) && !empty($data)) {
			foreach ($data as $key => $value) {
				$this->$key = $value;
			}
		}
		
		if (!$this->mapID || empty($this->mapID)) {
			// Generate unique map identifer incase of multiple maps.
			$this->mapID = substr(md5(uniqid('', true)), 0, 8);
		}
		
		if (!in_array($this->mapID, self::$mapIDs)) {
			self::$mapIDs[] = $this->mapID;	
		} else {
			trigger_error("Map already exists with ID '{$this->mapID}'");
		}
	}
	
	/**
	 * Add a map marker.
	 * 
	 * Takes in an associative array of map parameters.
	 * At the very least, a latitude and longitude or an address
	 * should be provided.
	 *
	 * @param array $params
	 * @return bool|string
	 */
	final public function addMarker($params = array())
	{
		// no params passed.
		if (!is_array($params) || empty($params)) { return false; }
	
		$valid_params = array('addr', 'lat', 'lng', 'title', 'content', 'color', 'label', 'image', 'animation');
		
		// set marker data.
		$data = array();
		foreach ($params as $name => $value) {
			if (in_array($name, $valid_params)) {
				$data[$name] = $value;
			}
		}
		
		// no address or coordinates have been provided.
		if (!isset($data['addr']) && (!isset($data['lat']) && !isset($data['lng']))) {
			return false;
		} else {
			// an address has been provided, get coordinates. 
			if (isset($data['addr'])) {
				if ($data['addr'] == 'center') {
					$coords = $this->centerCoords;
				} else {
					$coords = self::findCoordsByAddr($data['addr']);
				}
				
				// coordinates for address found.
				if (is_array($coords)) {
					$data = array_merge($data, $coords);
				}
			}
		}
		
		// unique marker id.
		$id = substr(md5($data['lat'].$data['lng']), 0, 8);
		
		// marker already exists on this map?
		if (isset($this->markers[$id])) { return false; }
		
		$this->markers[$id] = $data;
		
		return $id;
	}
	
	/**
	 * Checks whether $type is a valid google map type.
	 *
	 * @param string $type
	 * @return bool
	 */
	final public function isValidMapType($type)
	{
		$validMapTypes = array('roadmap', 'satellite', 'hybrid', 'terrain');
		
		return in_array($type, $validMapTypes);
	}
	
	/**
	 * Load the Google Maps API.
	 *
	 * @param string $key
	 * @param bool $sensor
	 * @return void
	 */
	final static public function loadAPI($key='', $sensor=false)
	{
		$url = 'http://maps.google.com/maps/api/js?sensor='.(!$sensor ? 'false' : 'true');
		if (!empty($key)) {
			$url .= '&amp;key='.$key;
		}
		
		echo '<script src="'.$url.'"></script>';
	}
	
	/**
	 * Find the latitude and longitude for an address.
	 *
	 * @static
	 * @param string $address
	 * @return array|bool
	 */
	final static public function findCoordsByAddr($address)
	{
		$url = 'http://maps.googleapis.com/maps/api/geocode/xml?sensor=true&address='.$address;
		$xml = simplexml_load_file($url);
		
		// Query failed.
		if ($xml->status != 'OK') { return false; }
		
		// Return coords.
		return (array) $xml->result->geometry->location;
	}
	
	abstract public function createMap();

} // End of AbstractGoogleMap class.

/**
 * GoogleMap class
 */
class GoogleMap extends AbstractGoogleMap
{
	// Container div.
	public $containerID = '';
	
	/**
	 * Display the map.
	 *
	 * @param bool $autoload If true, the map is loaded automatically using jQuery.
	 * @return bool|string
	 */
	public function createMap($autoload=false)
	{
		$centerCoords = (!empty($this->centerCoords)) ? $this->centerCoords : false;
		$containerID = (!empty($this->containerID)) ? $this->containerID : false;
		$mapID = str_replace(array(' ','-'), '_', $this->mapID);
		$markers = $this->markers;
		$icons = $this->icons;
		
		// One or more required fields not set.
		if (!$centerCoords || !$containerID || !$mapID) { return false; }
		
		// Valid map type?
		if (!$this->isValidMapType($this->mapTypeId)) { return false; }
		
		// Generate marker javascript.
		$markerJS = '';
		$infoWindowCount = 0;
		foreach ($markers as $id => $markerData)
		{
			extract($markerData, EXTR_PREFIX_ALL, 'marker');
			
			$markerID = 'marker_'.$mapID.'_'.$id;
			
			$markerJS .= "
				var $markerID = new google.maps.Marker({
					".(isset($marker_title) ? "title: '$marker_title'," : '')."
					".(isset($marker_image) ? "icon: '$marker_image'," : '')."
					".(isset($marker_color) ? "color: '$marker_color', " : '')."
					".(isset($marker_animation) ? "animation: google.maps.Animation.".strtoupper($marker_animation).", " : '')."
					position: new google.maps.LatLng($marker_lat,$marker_lng),
					map: map
				});
			";
			
			// Add info window to marker.
			if (isset($marker_content)) 
			{
				$infoWindowCount++;
				
				$infoWindowID = 'info_window_'.$markerID.'_'.$infoWindowCount;
				$infoWindow = $marker_content;
				
				$markerJS .= '
					var '.$infoWindowID.' = new google.maps.InfoWindow({
						content: \''.$infoWindow.'\'
					});
					google.maps.event.addListener('.$markerID.', \'click\', function() {
						'.$infoWindowID.'.open(map, '.$markerID.');
					});
				';
			}
		}
		
		// Create map loader function.
		echo '
			<script>
			function init_map_'.$mapID.'()
			{
				var point = new google.maps.LatLng('.$centerCoords['lat'].','.$centerCoords['lng'].');
				var options = {
					zoom: '.$this->zoomLevel.',
					center: point,
					scaleControl: '.($this->scaleControl ? 'true' : 'false').',
					streetViewControl: '.($this->streetViewControl ? 'true' : 'false').',
					zoomControl: '.($this->zoomControl ? 'true' : 'false').',
					mapTypeControl: '.($this->mapTypeControl ? 'true' : 'false').',
					mapTypeId: google.maps.MapTypeId.'.strtoupper($this->mapTypeId).'
				};
				
				var map = new google.maps.Map(document.getElementById(\''.$this->containerID.'\'), options);
				
				'.$markerJS.'
			}
		';
		
		// Autoload map with jquery if available.
		if ($autoload) {
			echo '
				if (typeof jQuery != \'undefined\') {
					$(document).ready(function() {
						init_map_'.$mapID.'();
					});
				}
			';
		}
		
		echo '</script>';
		
		// Return the map loader function name.
		return 'init_map_'.$mapID;
	}
	
} // End of GoogleMap class.

/**
 * GoogleMap_Static class.
 */
class GoogleMap_Static extends AbstractGoogleMap
{
	public $width = false;
	public $height = false;
	public $size = false;
	public $format = false;
	public $scale = 1;
	public $region = false;
	public $imgAttrs = array();

	/**
	 * Create a static google map.
	 *
	 * @param bool $returnURL If true, the maps URL is returned instead of the map being displayed.
	 * @return bool|string
	 */
	public function createMap($returnURL=false) 
	{
		$centerCoords = (!empty($this->centerCoords)) ? $this->centerCoords : false;
	
		// Get map size.
		if ($this->width && $this->height) {
			$this->size = $this->width.'x'.$this->height;
		} else {
			// No map size given.
			if (!$this->size) {
				return false;
			}
		}
		
		// Center coordinates not specified.
		if (!$centerCoords) { return false; }
	
		$url = 'http://maps.googleapis.com/maps/api/staticmap';
		
		$url .= '?center='.$this->centerCoords['lat'].','.$this->centerCoords['lng'];
		$url .= '&zoom='.$this->zoomLevel;
		$url .= '&size='.$this->size;
		$url .= '&scale='.$this->scale;
		$url .= '&sensor='.($this->sensor ? 'true' : 'false');
		
		// Valid image format?
		$validFormats = array('gif', 'jpeg', 'png', 'png8', 'png32');
		if ($this->format && in_array($this->format, $validFormats)) {
			$url .= '&format='.$this->format;
		}
		
		// Valid map type?
		if (!$this->isValidMapType($this->mapTypeId)) { 
			return false; 
		}
		
		$url .= '&maptype='.$this->mapTypeId;
		
		// Markers?
		if (is_array($this->markers) && !empty($this->markers)) 
		{
			$markers_str = '&markers=';
			
			foreach ($this->markers as $id => $marker_data) 
			{
				if (isset($marker_data['image'])) 
				{
					$markers_str .= 'icon:'.$marker_data['image'].'|';
				} 
				else {					
					if (isset($marker_data['color'])) {
						$markers_str .= 'color:'.$marker_data['color'].'|';
					}
					if (isset($marker_data['label'])) {
						$markers_str .= 'label:'.$marker_data['label'].'|';
					}
				}
				
				$markers_str .=	$marker_data['lat'].','.$marker_data['lng'].'|';
			}
			
			$url .= $markers_str;
		}
	
		// Display map image.
		if (!$returnURL) 
		{
			// IMG attributes.
			$attrs = '';
			if (is_array($this->imgAttrs) && !empty($this->imgAttrs)) {
				foreach ($this->imgAttrs as $name => $value) {
					$attrs .= " $name=\"$value\"";
				}
			}
		
			echo '<img src="'.$url.'" alt=""'.$attrs.' />';
			
			return true;
		}
		
		// Return map URL.
		return $url;
	}
	
} // End of GoogleMap_Static.

?>