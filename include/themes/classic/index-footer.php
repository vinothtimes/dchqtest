</div>
<?php JBPLUG_do_callback('index_footer_adcode', $A = false);?></div>
<p class="footer_text">Powered by <a class="footer_text" href="http://www.jamit.com/">Jamit Job Board</a> <b>Questions about this website?</b> Contact 
Us: <a class="footer_text" href="mailto:<?php echo JB_SITE_CONTACT_EMAIL; ?>"><?php echo JB_SITE_CONTACT_EMAIL; ?></a></p>
<?php

if (($_REQUEST['post_id'] != '') && (JB_MAP_DISABLED=="NO")) {
	$pin_y = $prams['pin_y'];
	$pin_x = $prams['pin_x'];
	// echo the javascript to position the pin on the map
	JB_echo_map_pin_position_js ($pin_x, $pin_y);
} 

$JBMarkup->body_close();
$JBMarkup->markup_close();

?>
