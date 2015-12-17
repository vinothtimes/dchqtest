<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";
include('login_functions.php'); 
JB_process_login();


include ("../include/profiles.inc.php");

JB_template_employers_header(); 

JB_render_box_top(99, $label['employer_vprofile_title']);

$ProfileForm = &JB_get_DynamicFormObject(3);
$row = $ProfileForm->load(false, $_SESSION['JB_ID']);

if ($row['profile_id'] != '') { // can we display the profile?
	$ProfileForm->load(false, $_SESSION["JB_ID"]);
	$ProfileForm->display_form('view', false);
} else {

	  /*

	  The employer does not yet have a profile, show a note
	  and a link to the edit profile page.

	  */

	  ?>

<h3><?php echo $label["employer_vprofile_noprof"]; ?></h3>
<div class="explanation_note">
<?php echo $label["employer_vprofile_note"]; ?>
<p>
<?php echo $label["employer_vprofile_editlink"]; ?>
</div>
<?php


}
JB_render_box_bottom();					

JB_template_employers_footer();  

?>