<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";
include('login_functions.php'); 
require_once ('../include/candidates.inc.php');
JB_process_login(); 

$CandidateForm = &JB_get_DynamicFormObject(5);
$CandidateForm->set_mode('edit');
JB_template_candidates_header();

if ($_REQUEST['load_defaults']==true) {
	$_REQUEST['user_id'] = $_SESSION['JB_ID'];
	
	$data = $CandidateForm->load($_SESSION['JB_ID']);
	$_REQUEST = array_merge($_REQUEST, $data);
}

JB_render_box_top(80, $label["seeker_ac_intro"]);



echo $label["seeker_ac_note"]; 



if ($_REQUEST['form'] != "" ) { // saving

	$admin = false;
	$errors = $CandidateForm->validate();

	if (($_REQUEST['user_id'] != $_SESSION['JB_ID']) || $errors) {

		$JBMarkup->error_msg($label['employer_save_error']);
		echo "<p>";
		echo $CandidateForm->get_error_msg();
		echo "</p>";
		
		$CandidateForm->display_form('edit', false);

	} else {
	
	
		$employer_id = $CandidateForm->save(); 

		$JBMarkup->ok_msg($label["seeker_ac_updated"]);
		$CandidateForm->display_form('edit', false);
	}


} else {
	
	$mode = "edit";
	$admin = false;
	
	$CandidateForm->load($_SESSION['JB_ID']);
	$CandidateForm->display_form('edit', false);
}




JB_render_box_bottom();
JB_template_candidates_footer();

?>