<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

require "../config.php";


include('login_functions.php'); 
require_once('../include/resumes.inc.php');
JB_process_login();
JB_template_employers_header(); 

JB_render_box_top(80, $label['employer_request_details_head']);
$sql = "SELECT * from `users` where `ID`='".jb_escape_sql($_REQUEST['user_id'])."' ";
$result = JB_mysql_query($sql) or die(mysql_error());
$candidate = mysql_fetch_array($result, MYSQL_ASSOC);

$submit = trim($_REQUEST['submit']);
$from = trim($_REQUEST['from']);
$reply_to = JB_clean_str(trim($_REQUEST['reply_to']));
$message = JB_clean_str(trim($_REQUEST['message']));

if (($submit != '')) {

	if ($from == '') {
		$error .= $label["employer_request_details_error_msg1"]."<br>"; 
	} elseif (!JB_validate_mail($reply_to)) {
		$error .= $label["employer_request_details_error_msg3"]."<br> ";
	}

	if ($reply_to == '') {
		$error .= $label["employer_request_details_error_msg2"]."<br>";
	}

	if ((JB_request_was_made($candidate['ID'], $_SESSION['JB_ID'])==false) && ($error == '')) {


		jb_add_new_request($candidate['ID'], $_SESSION['JB_ID'], 'REQUEST', $message);
		
     
		JB_mysql_query($sql) or die (mysql_error());

		$result = JB_mysql_query("SELECT * from `employers` where `ID`='".$_SESSION['JB_ID']."' ") or die(mysql_error());
		$employer = mysql_fetch_array($result, MYSQL_ASSOC);


		$result = JB_get_email_template (4, $_SESSION['LANG']);
		$e_row = mysql_fetch_array($result, MYSQL_ASSOC);
		$EmailMessage = $e_row['EmailText'];
		//$from = $e_row[EmailFromAddress];
		//$from_name = $e_row[EmailFromName];
		$subject = $e_row['EmailSubject'];

		$EmailMessage = str_replace ("%FNAME%", $candidate['FirstName'], $EmailMessage);
		$EmailMessage = str_replace ("%LNAME%", $candidate['LastName'], $EmailMessage);
		$EmailMessage = str_replace ("%EMPLOYER_NAME%", JB_clean_str($_REQUEST['from']), $EmailMessage);
		$EmailMessage = str_replace ("%REPLY_TO%", JB_clean_str($_REQUEST['reply_to']), $EmailMessage);
		$EmailMessage = str_replace ("%PERMIT_LINK%", JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER."permit.php?k=".$key, $EmailMessage);
		$EmailMessage = str_replace ("%SITE_NAME%", JB_SITE_NAME, $EmailMessage);
		$EmailMessage = str_replace ("%MESSAGE%", $_REQUEST['message'], $EmailMessage);

		//echo $EmailMessage;

		$label["employer_request_letter_subject"] = str_replace ("%SITE_NAME%", JB_SITE_NAME , $label["employer_request_letter_subject"]);

		$subject = $e_row['EmailSubject']; //$label["employer_request_letter_subject"];


		$to = $candidate['Email'];
		$reply_to = stripslashes($reply_to);
		$from = stripslashes($from);


		$email_id = JB_queue_mail($to, jb_get_formatted_name($candidate['FirstName'], $candidate['LastName']), $reply_to, $from, $subject, $EmailMessage, '', 4);

		JB_process_mail_queue(1, $email_id);


		$JBMarkup->ok_msg($label['employer_request_sent']);

   }


} 

if (($_REQUEST['user_id'] != '') && ($EmailMessage=='')) {
	$sql = "SELECT * from `employers` where `ID`='".jb_escape_sql($_SESSION['JB_ID'])."' ";
	$result = JB_mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	//echo $sql." ".$row[Email];
	if ($from =='') {
	  $from = $row['CompName'];
	}
	if ($reply_to =='') {
	  $reply_to = $row['Email'];
	}

	if ($error != '' ) {
	  $JBMarkup->error_msg($label["employer_request_details_error"]);
	  echo $error;
	}

	if (JB_request_was_made($candidate['ID'], $_SESSION['JB_ID'])) {

		echo "<div class='request_msg_sent_label'>".$label["resume_display_request_sent"]."</div>";

	} else {
		JB_template_employer_request_form($from, $reply_to);
	}

}
JB_render_box_bottom();

JB_template_employers_footer(); ?>