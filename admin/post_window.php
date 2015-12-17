<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
include('../config.php');
require (dirname(__FILE__)."/admin_common.php");

 
require_once('../include/posts.inc.php');
require_once('../include/category.inc.php');

$post_id = (int) $_REQUEST['post_id'];
$JBPage = new JBJobPage($post_id, $admin=true);

extract($JBPage->get_vars(), EXTR_REFS); // make the $data available

JB_admin_header('Admin -> Post Window');

?>
<h2 style="align:center;">Job Post Preview</h2>
<p style="align:center;"><input type="button" name="" value="Close" onclick="window.close()"></p>

<?php

$JBPage->output('HALF');

?>

<p style="align:center;"><input type="button" name="" value="Close" onclick="window.close()"></p>
<?php


if (($_REQUEST['post_id'] != '') && (JB_MAP_DISABLED=="NO")) {
	$pin_y = $DynamicForm->get_value('pin_y');
	$pin_x = $DynamicForm->get_value('pin_x');

	JB_echo_map_pin_position_js ($pin_x, $pin_y);
} 

JB_admin_footer();

?>