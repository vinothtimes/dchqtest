<?php 

###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require("../config.php");
include('login_functions.php'); 

require_once ("../include/resumes.inc.php");
require_once ("../include/profiles.inc.php");

JB_process_login();

$resume_id = JB_get_resume_id ($_SESSION['JB_ID']);
$JBPage = new JBResumePage($resume_id); // this loads and sets the dynamic forms, data, etc
$resume_data = &$JBPage->vars['DynamicForm']->get_values();


JB_template_candidates_header();

JB_display_info_box ($label["c_resume_header"], $label["c_resume_intro"], 80); 


$employer_id = (int) $_REQUEST['employer_id'];

if ($_REQUEST['action'] == 'grant') {

	$sql = "UPDATE `requests` SET `request_status`='GRANTED' WHERE `employer_id`='".jb_escape_sql($employer_id)."' AND candidate_id='".jb_escape_sql($_SESSION['JB_ID'])."' ";
	
	JB_mysql_query($sql) or die(mysql_error());

	JB_send_request_granted_email($_SESSION['JB_ID'], $employer_id);

}

if ($_REQUEST['action'] == 'refuse') {

	$sql = "UPDATE `requests` SET `request_status`='REFUSED' WHERE `employer_id`='".jb_escape_sql($employer_id)."' AND candidate_id='".jb_escape_sql($_SESSION['JB_ID'])."' ";
	JB_mysql_query($sql) or die(mysql_error());

}



if (isset($_REQUEST['delete']) && $_REQUEST['delete']) {

	$sql = "UPDATE `requests` SET `request_status`='REFUSED' WHERE `employer_id`='".jb_escape_sql($employer_id)."' AND candidate_id='".jb_escape_sql($_SESSION['JB_ID'])."' ";
	JB_mysql_query($sql) or die(mysql_error());

	$candidate_id = $_SESSION['JB_ID'];
	foreach ($_REQUEST['employer_ids'] as $employer_id) {

		$sql = "UPDATE`requests` SET `deleted`='Y' WHERE `candidate_id`='".jb_escape_sql($candidate_id)."' AND `employer_id`='".jb_escape_sql($employer_id)."'";
		
		$result = JB_mysql_query ($sql) or die (mysql_error());
		

	}

	$sql = "UPDATE `requests` SET `request_status` = 'REFUSED' WHERE `deleted`='Y' AND `candidate_id`='".jb_escape_sql($candidate_id)."' AND `request_status` = 'REQUEST' ";
	JB_mysql_query ($sql) or die (mysql_error());

}



if ($_REQUEST['change_status'] !='') {

	if (($_REQUEST['status']!='ACT') && ($_REQUEST['status']!='SUS')) {
		die();	
	} else {
		$status = $_REQUEST['status'];
	}

	$sql = "UPDATE `resumes_table` SET `status`='".jb_escape_sql($status)."' WHERE user_id='".jb_escape_sql($_SESSION['JB_ID'])."' AND `resume_id`='".jb_escape_sql($resume_id)."' AND status != '".jb_escape_sql($status)."' ";
	JB_mysql_query($sql) or die(mysql_error());

	$resume_data['status'] = $status;

	if (JB_mysql_affected_rows()>0) {

		//  Update maling list, newsletter status.
		// minus 2 if suspended, plus two if active.
		// This ensures that their email opt-in prefrences are saved
		// when they become suspended, and go back to their original setting
		// when they become active

		if ($status=='SUS') {

			$sql = "UPDATE `users` SET Newsletter=Newsletter-2, `Notification1`=`Notification1`-2, `Notification2`=Notification2-2 WHERE ID='".jb_escape_sql($_SESSION['JB_ID'])."'";
			
			JB_mysql_query($sql) or die(mysql_error());

			if (jb_mysql_affected_rows() > 0) {

				// delete the resume from saved resumes
				// (Assuming that $_REQUEST['resume_id'] was already validated to be
				// owned by the logged in user
				$sql = "DELETE FROM `saved_resumes` WHERE `resume_id`='".jb_escape_sql($_REQUEST['resume_id'])."' ";
				JB_mysql_query($sql) or die(mysql_error()); 

			}
		}

		if ($status=='ACT') {

			$sql = "UPDATE `users` SET `Newsletter`=`Newsletter`+2, `Notification1`=`Notification1`+2, `Notification2`=Notification2+2 where ID='".jb_escape_sql($_SESSION['JB_ID'])."'";
			JB_mysql_query($sql) or die(mysql_error());
			
		}
	}

}





if ($resume_id != '' ) {
	
	if (!$status) $status='ACT';
	?><p><?php 
		JB_render_box_top(80, $label['c_resume_status']);
	if ($_REQUEST['change_status']!='') {
		$JBMarkup->ok_msg($label['edit_status_upd']);
	}

?>
				
	<form method="post" action='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>'>
	<input type="hidden" value="<?php echo htmlentities($resume_id); ?>" name="resume_id">
	<table align="center" border="0"><tr><td>
	<?php echo $label["c_resume_set_to"];?> <input name="status" type="radio" value="ACT" <?php if ($status=='ACT') { echo ' checked ';} ?> > <?php echo $label["c_resume_active"]; ?> | <input name="status" type="radio" value="SUS" <?php if ($status=='SUS') { echo ' checked ';} ?> > <?php echo $label["c_resume_Suspended"];?> &nbsp;<input class="form_submit_button" type="submit" name="change_status" value="OK"  >
	</td>
	</tr>
	</table>
	</form>

	<?php
	JB_render_box_bottom();	

	if ($resume_data['anon']=='Y') { ?>
		<p>
		<?php 
		
		if (JB_RESUME_REQUEST_SWITCH!='NO') {
			JB_render_box_top(80, '');
			$msg = $label["c_resume_note_text"];
			echo $msg;
			JB_display_request_history ($_SESSION['JB_ID']);
			JB_render_box_bottom();
		}

	} 


	
	$JBPage->output();

} else {
	echo "<br>";
	JB_display_info_box ("", "<p align='center'>".$label["c_resume_notfound"]."<br>".$label["c_index_no_resume"]."</p>", 80);
}


JB_template_candidates_footer(); 

?>