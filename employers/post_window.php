<?php
include('../config.php'); 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

require_once('../include/posts.inc.php');
require_once('../include/category.inc.php');
include('login_functions.php');
JB_process_login(); 


$post_id = (int) $_REQUEST['post_id'];
$JBPage = new JBJobPage($post_id);
extract($JBPage->get_vars(), EXTR_REFS);

echo $JBMarkup->get_doctype();
$JBMarkup->markup_open(); // <html>

$JBMarkup->head_open();

$JBMarkup->stylesheet_link(JB_get_maincss_url());

$JBMarkup->head_close();

$JBMarkup->body_open('style="background-color: white;"');
?>




<h2 style="text-align:center"><?php echo $label["employer_post_window_header"];?></h2>

<p style="text-align:center"><input type="button" name="" value="Close" onclick="window.close()"></p>
<div style="text-align:left">
<?php
 
$JBPage->output('HALF');
		

?>

<p style="text-align:center"><input type="button" name="" value="Close" onclick="window.close()"></p>


<?php
if (JB_MAP_DISABLED=="NO") {

	JB_echo_map_pin_position_js ($DynamicForm->get_value('pin_x'), $DynamicForm->get_value('pin_y'));

}
?>
</div>
<?php

$JBMarkup->body_close();
$JBMarkup->markup_close();
?>