<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require("../config.php");
include('../include/help_functions.php'); 
?>

<?php include('login_functions.php'); ?>
<?php JB_process_login(); ?>
<?php JB_template_candidates_header();?>
 
       <?php 
	   $data = JB_load_help('U');
	   
	   JB_render_box_top(80, $data['title']);
	   
	   echo $data['message'];
	   JB_render_box_bottom();
	   ?>
<p>&nbsp</p>

<?php JB_template_candidates_footer();?>