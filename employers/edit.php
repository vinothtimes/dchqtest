<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
include('../config.php'); 
include('login_functions.php'); 

JB_process_login(); 
include ("../include/profiles.inc.php");

$ProfileForm = &JB_get_DynamicFormObject(3);
$ProfileForm->set_mode('edit');

JB_template_employers_header(); 

JB_render_box_top(99, $label['employer_eprofile_title']);

// get profile id from user_id
$sql = "SELECT profile_id FROM profiles_table where user_id='".jb_escape_sql($_SESSION['JB_ID'])."'";
$result = JB_mysql_query ($sql) or die (mysql_error());

if ($row = mysql_fetch_row($result)) {
	$_REQUEST['profile_id'] = $row[0];
}

if ($_REQUEST['save'] != "" ) { // saving
	
	$errors = $ProfileForm->validate();
	if ($errors) { // we have an error
		display_profile_intro();
		$mode = "edit";
		$ProfileForm->display_form($mode, false);

	} else {
	
		$profile_id = $ProfileForm->save();
		$ProfileForm->load($profile_id);
		$JBMarkup->ok_msg($label["employer_eprofile_saved"]);
		$mode = "view";
		$ProfileForm->display_form($mode, false);
	}
} else {
	
	if ($_REQUEST['profile_id'] != '') {
		$ProfileForm->load($_REQUEST['profile_id']);
	}
	
	display_profile_intro();
	$mode = "edit";
	$ProfileForm->display_form('edit', false);
}

JB_render_box_bottom();				
					
################################################################

function display_profile_intro () {

	global $label; ?>
	<div class="explanation_note">

	<?php echo $label["employer_eprofile_intro"]; ?>
	</div> <?php


}
################################################################

JB_template_employers_footer(); 

?>