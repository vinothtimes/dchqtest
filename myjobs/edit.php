<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
ini_set ('session.gc_maxlifetime', 60*60*1); // 1 hour

require("../config.php");
include('login_functions.php');
JB_process_login();
require_once ("../include/resumes.inc.php");
$ResumeForm = &JB_get_DynamicFormObject(2);
$ResumeForm->set_mode('edit');
JB_template_candidates_header();

// Get the user's resume_id and status
// Use the JB_SESSION id to ensure that the resume is owned by the user
// and $resume_id is valid.

$sql = "SELECT resume_id, status FROM resumes_table where user_id='".jb_escape_sql($_SESSION['JB_ID'])."'";
$result = JB_mysql_query ($sql) or die (mysql_error());

if ($row = mysql_fetch_row($result)) {
	$resume_id = $row[0];
	$status = $row[1];
	$_REQUEST['resume_id'] = $resume_id; // ensure that it is valid
}


################################3
# Change the status of the resume
# ACT = Active
# SUS = Suspended

if ($_REQUEST['change_status'] !='') {

	if (($_REQUEST['status']!='ACT') && ($_REQUEST['status']!='SUS')) {
		die();	
	}
	$sql = "UPDATE `resumes_table` SET `status`='".jb_escape_sql($_REQUEST['status'])."' WHERE user_id='".jb_escape_sql($_SESSION['JB_ID'])."' AND `resume_id`='".jb_escape_sql($resume_id)."' AND status <> '".jb_escape_sql($_REQUEST['status'])."' ";

    JB_mysql_query($sql) or die(mysql_error());

	if (JB_mysql_affected_rows()>0) {

		//  Update maling list, newsletter status.
		// minus 2 if suspended, plus two if active.
		// This ensures that their email opt-in prefrences are saved
		// when they become suspended, and go back to their original setting
		// when they become active

		if ($_REQUEST['status']=='SUS') {

			$sql = "UPDATE `users` SET Newsletter=Newsletter-2, `Notification1`=`Notification1`-2, `Notification2`=Notification2-2 WHERE ID='".jb_escape_sql($_SESSION['JB_ID'])."'";
			
			JB_mysql_query($sql) or die(mysql_error());

			// delete the resume from saved resumes
			// (Assuming that $resume_id was already validated to be
			// owned by the logged in user (See code above)
			$sql = "DELETE FROM `saved_resumes` WHERE `resume_id`='".jb_escape_sql($resume_id)."' ";
			JB_mysql_query($sql) or die(mysql_error()); 

			$ResumeForm->set_value('status', 'SUS');
			$status = 'SUS';
		}

		if ($_REQUEST['status']=='ACT') {

			$sql = "UPDATE `users` SET `Newsletter`=`Newsletter`+2, `Notification1`=`Notification1`+2, `Notification2`=Notification2+2 where ID='".jb_escape_sql($_SESSION['JB_ID'])."'";
			JB_mysql_query($sql) or die(mysql_error());

			$ResumeForm->set_value('status', 'ACT');
			$status = 'SUS';
			
		}

	}

	
}

##########################
# Display the Suspend / Approve control panel

if (is_numeric($resume_id)) {
	?>
	<p></p>
	<?php JB_render_box_top(80, $label['c_resume_status']); 
		if ($_REQUEST['change_status']!='') {
		$JBMarkup->ok_msg($label['edit_status_upd']);
	}
	
	?>				
	<form method="post" action='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>'>
	<input type="hidden" value="<?php echo htmlentities($resume_id); ?>" name="resume_id">
	<table align="center" border="0"><tr><td>
	<?php echo $label["c_resume_set_to"];?> <input name="status" type="radio" value="ACT" <?php if ($status=='ACT') { echo ' checked ';} ?> > <?php echo $label["c_resume_active"]; ?> | <input name="status" type="radio" value="SUS" <?php if ($status=='SUS') { echo ' checked ';} ?> > <?php echo $label["c_resume_Suspended"];?> &nbsp;<input class="form_submit_button" type="submit" name="change_status" value="OK"  >
	</td>
	</tr></table>
	</form>
	  
	<?php
	JB_render_box_bottom();	
	?><p></p><?php

}

JB_render_box_top(99,  $label['c_edit_intro2']);

if ($_REQUEST['save'] == "" ) {
	echo $label["c_edit_intro"];
}

####################
# Save the resume
			
if ($_REQUEST['save'] != "" ) { // saving
	
	$errors = $ResumeForm->validate(); //JB_validate_resume_data(2);
	if ($errors) { // display the form again on error
		$ResumeForm->display_form('edit', false);
	} else {
		$resume_id = $ResumeForm->save();
		$ResumeForm->load($resume_id);
		$JBMarkup->ok_msg($label["c_edit_saved"]);
		$ResumeForm->display_form('edit', false);
	}
	
} else {
	
	# Display the form to edit a resume
	if ($resume_id != '') {
		$ResumeForm->load($resume_id);
	}
	$ResumeForm->display_form('edit', false);
	
}

JB_render_box_bottom();
JB_template_candidates_footer();?>