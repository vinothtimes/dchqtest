<?php

/* 

A simple Google Maps API
Copyright Jamit Software LTD 2010

The purpose of this class is to abstract the Google Maps API v3 

language list http://spreadsheets.google.com/pub?key=p9pdwsai2hDMsLkXsoM05KQ&gid=1

*/



class GSimpleMap {


	var $map_id;
	var $width='100%';
	var $height='100%';

	var $js_code = array();
	var $markers = array();
	var $coords = array();
	var $zoom;

	function GSimpleMap($map_id, $lat, $lng, $zoom, $options = array()) {
		$this->map_id = $map_id;
		$this->coords['lat'] = $lat;
		$this->coords['lng'] = $lng;
		$this->zoom=$zoom;
		
	}

	/*

	Add a marker to the map, address will be looked up using Google's
	Geocode API

	*/

	function addMarkerByAddress($address, $title, $description, $options = array()) {

		$coords = $this->geoGetCoords($address);
		return $this->addMarker($coords['lat'], $coords['lng'], $title, $description, $options);


	}

	function addMarker($lat, $lng, $title, $description, $options = array()) {

		$this->markers[] = array('lat'=>$lat, 'lng'=>$lng, 'title'=>$title, 'description'=>$description, 'options'=>$options);
		return (array('lat'=>$lat, 'lng'=>$lng));

	}

	/*

	Get the HTML code that needs to be placed between the <head> tags in a HTML doc.

	*/

	function getHeaderJs() {

		return '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
		';

	}

	/*

	Get the Javascript code to display the map.
	The markers will also be added to the map. If the option
	$marker['options']['draggable'] is set, then the first marker will
	be be draggable and listeners for the click and mouseup events are added. 
	If the event is fired, then the the map will center itself and also it will
	send the lat and lng parameters to the HTML form.

	(Note: when the map is draggable, then we assume that the map is in an
	IFRAME.)
	*/

	function getMapJS() {

		$script = '
	<script type="text/javascript">

	function init_map_'.$this->map_id.'() {
		var myLatlng = new google.maps.LatLng('.$this->coords['lat'].', '.$this->coords['lng'].');
		var myOptions = {
			zoom: '.$this->zoom.',
			center: myLatlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			scrollwheel: false
			
		}
		var map = new google.maps.Map(document.getElementById("'.$this->map_id.'"), myOptions);
		';

		
		foreach ($this->markers as $marker) {

			$script .='
		var marker = new google.maps.Marker({
			position: myLatlng, 
				map: map,
				title:"'.$marker['title'].'",';

				
				if ($marker['options']['draggable']) {
					$script .= '
					draggable: true,';
				}

				$script .= '
				bouncy: true
		});
		
		';

			if ($marker['options']['draggable']) {

			// listen to some events
				$script .= '			
		google.maps.event.addListener(marker, \'mouseup\', function() {
			var temp = window.parent.document.getElementById(\'input_'.$this->map_id.'_lat\');
			temp.value= marker.getPosition().lat();
			var temp = window.parent.document.getElementById(\'input_'.$this->map_id.'_lng\');
			temp.value= marker.getPosition().lng();
			var temp = window.parent.document.getElementById(\'input_'.$this->map_id.'_zoom\');
			temp.value= map.getZoom();
			map.panTo(marker.getPosition());
		});
		google.maps.event.addListener(map, \'click\', function(event) {
			marker.setPosition(event.latLng);
			map.panTo(event.latLng);
			var temp = window.parent.document.getElementById(\'input_'.$this->map_id.'_lat\');
			temp.value= marker.getPosition().lat();
			var temp = window.parent.document.getElementById(\'input_'.$this->map_id.'_lng\');
			temp.value= marker.getPosition().lng();
			var temp = window.parent.document.getElementById(\'input_'.$this->map_id.'_zoom\');
			temp.value= map.getZoom();


		});';
		
			}

		
	
		}

		$script .= '
	}
	</script>
	';

		return $script;

	}

	function getOnLoadFunction() {

		return 'init_map_'.$this->map_id;

	}


	//////////////////////////////////////


	 /**
     * Nice simple little function, needs json though!
     */

	 // php 5:  static function geoGetCoords($address,$depth=0) {
     function geoGetCoords($address,$depth=0) {
       
		$url = 'http://maps.google.com/maps/api/geocode/json?sensor=false&address='.rawurlencode($address) ;
		//echo $url;

		$result = false;
		if($result = self::fetchURL($url)) {
			$result_parts = json_decode($result);
			if($result_parts->status!="OK"){
				return false;
			}
			$coords['lat'] = $result_parts->results[0]->geometry->location->lat;
			$coords['lng'] = $result_parts->results[0]->geometry->location->lng;
		}
               
        return $coords;       
    }

	 function fetchURL($url) {

        return file_get_contents($url);

    }

}

?>