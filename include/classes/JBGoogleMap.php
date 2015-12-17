<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

class JBGoogleMap {

	var $MAP_OBJECTS;

	function JBGoogleMap() {


		require_once(jb_basedirpath().'include/lib/GoogleMap/GSimpleMap.php');

	}

	function get_instance () {

		static $JBGMap;
		if ($JBGMap) {
			return $JBGMap;
		}
		$JBGMap = new JBGoogleMap();
		return  $JBGMap;

	}



	function add_map($field_id, $location, $zoom=6) {

		if (!is_array($location) && (strlen($location)>4)) {
			$coords = GSimpleMap::geoGetCoords($location);
			$this->MAP_OBJECTS[$field_id] = new GSimpleMap('gmap_'.$field_id, $coords['lat'], $coords['lng'], $zoom);
			return $coords;
		} elseif (is_numeric($location['lat']) && is_numeric($location['lng'])) {
			$this->MAP_OBJECTS[$field_id] = new GSimpleMap('gmap_'.$field_id, $location['lat'], $location['lng'], $zoom);
			return $location;
		} else {

			return false;

		}

	}

	function add_marker($field_id, $lat, $lng, $title, $descr, $draggable=false) {
		if (!$this->MAP_OBJECTS[$field_id]) return false;
		
		$options = array('draggable'=>$draggable);
		$this->MAP_OBJECTS[$field_id]->addMarker($lat, $lng, $title, $descr, $options);

	}

	

	function get_header_js() {

		if (is_array($this->MAP_OBJECTS)) {
			$obj = current($this->MAP_OBJECTS);
			if ($obj) {
				return $obj->getHeaderJS();
			}
		}
	}

	// This should go between the <head></head> tags
	function get_map_js($field_id) {
		if (!$this->MAP_OBJECTS[$field_id]) return false;
		return $this->MAP_OBJECTS[$field_id]->getMapJS();

	}

	function get_onload_js($field_id) {
		if (!$this->MAP_OBJECTS[$field_id]) return false;
		return $this->MAP_OBJECTS[$field_id]->getOnLoadFunction().'();';
	}

	function get_map_markup($field_id) {
		if (!$this->MAP_OBJECTS[$field_id]) return false;
		$map_id = 'gmap_'.$field_id;

		?>

	<div id="<?php echo $map_id; ?>" style="width:<?php echo $this->MAP_OBJECTS[$field_id]->width; ?>;height:<?php echo $this->MAP_OBJECTS[$field_id]->height; ?>"></div>
    

	<?php

	}


}



?>