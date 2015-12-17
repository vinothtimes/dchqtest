<?php
// Copyright Jamit Software LTD 2010

/*

The Iframe is used for marking the location on the map when the form is in
editing mode.

Iframe is not used for displaying the map

*/
define ('NO_HOUSE_KEEPING', true);

$dir = dirname(__FILE__);
$dir = explode (DIRECTORY_SEPARATOR, $dir);
array_pop($dir);
array_pop($dir);
array_pop($dir);
$dir = implode('/', $dir);

require ($dir.'/config.php');

require_once(JB_basedirpath().'include/classes/JBGoogleMap.php');

$GMAP = JBGoogleMap::get_instance(); 

$map_id = (int) $_REQUEST['map_id'];
$form_id = (int) $_REQUEST['form_id'];

if (is_numeric($_REQUEST['lat']) && is_numeric($_REQUEST['lng']) && ($_REQUEST['lat']!=0)) {

	if (is_numeric($_REQUEST['z'])) {
		$zoom = (int) $_REQUEST['z'];
	} else {
		$zoom = JB_GMAP_ZOOM;
	}
	
	$coords = $GMAP->add_map($map_id, array('lat'=>$_REQUEST['lat'], 'lng'=>$_REQUEST['lng']), $zoom);
	$lat = $_REQUEST['lat']; $lng = $_REQUEST['lng'];
} else {
	// use the default lat and lng
	$coords = $GMAP->add_map($map_id, array('lat'=>JB_GMAP_LAT, 'lng'=>JB_GMAP_LNG), JB_GMAP_ZOOM);
	$lat = JB_GMAP_LAT; $lng = JB_GMAP_LNG;

}
$GMAP->add_marker($map_id, $lat, $lng, 'Please move me!', '', true);
JBPlug_do_callback('gmap_iframe', $GMAP, $map_id, $form_id); // plugin authors can do stuff on the $GMAP object here

//$extra_str .= $GMAP->get_header_js();
//$extra_str .= $GMAP->get_map_js($map_id);
//$JBMarkup->append_extra_markup('header', $extra_str);
$JBMarkup->set_handler('header', $GMAP, 'get_header_js');
$JBMarkup->set_handler('header', $GMAP, 'get_map_js', $map_id);

//$extra_str = '';	
//$extra_str .= $GMAP->get_onload_js($map_id);
//$JBMarkup->append_extra_markup('onload_function', $extra_str);
$JBMarkup->set_handler('onload_function', $GMAP, 'get_onload_js', $map_id);

$JBMarkup->markup_open();
$JBMarkup->head_open();
$JBMarkup->title_meta_tag('Google Map');
$JBMarkup->no_robots_meta_tag();
//$JBMarkup->stylesheet_link(JB_get_maincss_url());
$JBMarkup->charset_meta_tag();


$JBMarkup->head_close();

$JBMarkup->body_open('style="background-color: white;"');

echo $GMAP->get_map_markup($map_id);

$JBMarkup->body_close();
$JBMarkup->markup_close();


?>