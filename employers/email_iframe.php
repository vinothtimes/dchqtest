<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################


define ('NO_HOUSE_KEEPING', true);
require ("../config.php");
include('login_functions.php');
JB_process_login(false); 
echo $JBMarkup->get_doctype();
$JBMarkup->markup_open();
$JBMarkup->head_open(); 
$JBMarkup->charset_meta_tag(); 
$JBMarkup->no_robots_meta_tag();
$JBMarkup->stylesheet_link(JB_get_maincss_url());
$JBMarkup->head_close();
$JBMarkup->body_open('style="background-color:white; background-image: none;"');

require_once ("../include/resumes.inc.php");
$resume_id = (int) $_REQUEST["resume_id"];
$RForm = JB_get_DynamicFormObject(2);
$data = $RForm->load($resume_id);


$sql = "SELECT * FROM `users` WHERE `ID`='".jb_escape_sql($data['user_id'])."' ";
$c_result = JB_mysql_query ($sql) or die (mysql_error());
$c_row = mysql_fetch_array($c_result);


$CANDIDATE_EMAIL = trim($RForm->get_raw_template_value ("RESUME_EMAIL"));
if (!JB_validate_mail($CANDIDATE_EMAIL)) {
	$CANDIDATE_EMAIL = (trim($c_row['Email']));
	# email is invalid. Attempt to grab email form employer...
}

$CANDIDATE_NAME = jb_get_formatted_name($c_row['FirstName'], $c_row['LastName']);
if (trim($CANDIDATE_NAME)=='') {
	$CANDIDATE_NAME = (trim($RForm->get_raw_template_value ("RESUME_NAME")));
}


$sql = "SELECT * FROM `employers` WHERE `ID`='".jb_escape_sql($_SESSION['JB_ID'])."' ";
$e_result = JB_mysql_query ($sql) or die (mysql_error());
$e_row = mysql_fetch_array($e_result);
$EMPLOYER_EMAIL = trim(strip_tags($e_row['Email']));
if (trim($e_row['CompName'])!='') {
	$EMPLOYER_NAME = $e_row['CompName'];	
} else {
	$EMPLOYER_NAME = jb_get_formatted_name($e_row['FirstName'], $e_row['LastName']);
}
$EMPLOYER_NAME = trim(strip_tags($EMPLOYER_NAME)); // just to make sure.

if (JB_EMAIL_SIG_SWITCH == "YES") {

   $sig = "\n\n---\n".$label["em_email_sent_from_sig"].JB_SITE_NAME;
   $sig_html = "<p>---<br>".$label["em_email_sent_from_sig"].JB_SITE_NAME;
}


$apply = $_REQUEST['apply'];
$email_letter = JB_clean_str(trim($_REQUEST['email_letter']));
$c_email = JB_clean_str(trim($_REQUEST['c_email']));
$c_name = JB_clean_str(trim($_REQUEST['c_name']));
$email_subject = JB_clean_str(trim($_REQUEST['email_subject']));
 
$success = false;

if ($apply != '') {

	if ($email_letter == '') {
	   $error .= $label['em_letter_error']."<br>"; 
	}
	if ($c_email == '') {
	   $error .= $label['em_email_error']."<br>"; 
	} elseif (!JB_validate_mail($c_email)) {
		$error .= $label['em_email_invalid']."<br>"; 

	}

	if ($c_name== '') {
	   $error .= $label["em_name_error"]."<br>"; 
	}


	if ($error != '') {
		$JBMarkup->error_msg($label['em_error']);
		echo "<br>".$error;

	} else {

		// strip slashes from data before sending it by email
		// (Jamit job board adds slashes regardless of PHP config)
		 
		$email_letter = stripslashes($email_letter);
		$email_subject = stripslashes($email_subject);
		$c_name = stripslashes($c_name);
		$c_email = stripslashes($c_email);
		$to_name = $c_name;
		$to_address = $c_email;

		// load and assign the template

		$t_result = JB_get_email_template (11, $_SESSION['LANG']); // load the template
		$t_row = mysql_fetch_array($t_result, MYSQL_ASSOC);

		$msg = $t_row['EmailText'];
		$msg = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $msg);
		$msg = str_replace ("%SITE_NAME%", JB_SITE_NAME, $msg);
		$msg = str_replace ("%SITE_URL%", JB_BASE_HTTP_PATH, $msg);
		$msg = str_replace ("%MESSAGE%", $email_letter, $msg);
		$msg = str_replace ("%SENDER_IP%", $_SERVER['REMOTE_ADDR'], $msg);
		$msg = str_replace ("%USER_ID%", $_SESSION['JB_ID'], $msg);
		$msg = str_replace ("%EMPLOYER_NAME%", $EMPLOYER_NAME, $msg);

		$email_id = JB_queue_mail($to_address, $to_name, $EMPLOYER_EMAIL, $EMPLOYER_NAME, $email_subject, $msg, '', 11);
		JB_process_mail_queue(1, $email_id);

		$JBMarkup->ok_msg($label['email_sent_ok']);

		echo $label['em_confirm_title']; 
		?>

		<table border="0" width="80%">
		<tr><td><b><?php echo $label['em_confirm_name'];?> </b></td><td><?php echo JB_escape_html($c_name);?></td></tr>
		<tr><td><b><?php echo $label['em_confirm_email']; ?></b></td><td><?php echo JB_escape_html($c_email);?></td></tr>
		<tr><td><b><?php echo $label['em_confirm_subject']; ?></b></td><td><?php echo JB_escape_html($email_subject);?><td></tr>
		<tr><td><b><?php echo $label['em_confirm_lettter']; ?></b></td><td><?php echo JB_escape_html($email_letter);?></td></tr>

		</table>

		<?php

		$success = 1;
	}
}  
 
 if ($success != 1) {
	if ($email_subject == '') {

		$email_subject = $label['em_email_subject'];
		$DATE = JB_get_local_time(date('r'));
		$DATE = JB_get_formatted_date($DATE);
		$email_subject = str_replace ("%DATE%", $DATE, $email_subject);
		$email_subject = str_replace ("%TITLE%", $TITLE, $email_subject);
		$email_subject = str_replace ("%SITE_NAME%", JB_SITE_NAME, $email_subject);
	}

	if ($_REQUEST['step'] != '') {
		// seed the form with pre-popluated data.

		$c_email = $CANDIDATE_EMAIL; //$user_row[Email];
		$c_name = $CANDIDATE_NAME;
		if ($c_name=='') {
			$c_name = jb_get_formatted_name($user_row['FirstName'], $user_row['LastName']);  
		}
	}
	JB_template_employer_email_form($post_id, $c_name, $c_email, $email_subject, $email_letter);

 }

$JBMarkup->body_close();
$JBMarkup->markup_close();
?>