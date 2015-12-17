<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
 require("../config.php");?>

<?php include('login_functions.php'); ?>
<?php JB_process_login(); ?>
<?php JB_template_candidates_header();?>


<?php
JB_render_box_top(80, $label['c_about_head']);
$label['c_about_text'] = str_replace ("%SITE_NAME%", JB_SITE_NAME, $label['c_about_text']);
$label['c_about_text'] = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $label['c_about_text']);
echo $label['c_about_text'];
JB_render_box_bottom();
?>
      
			
<p>&nbsp</p>

<?php JB_template_candidates_footer();?>