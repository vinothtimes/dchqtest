<?php 
###########################################################################
# Copyright Jamit Software 2012
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
/*

The template file for the applications form is application-form.php
and the default template lives in include/themes/default/

The way the job board attaches the files of the application is like this:

After the submit button is pressed, the system will validate the input -
the file extensions are checked.
If input is OK, it will pass the applications to the JB_queue_mail() function.

JB_queue_mail() function works like this:

It looks for any attachments uploaded, and moves to upload_files/docs/temp/
this is done by the JB_move_uploaded_attachment() function in include/mail_manager.php

The file name is cleaned from any bad characters and re-named then moved to
the temp directory.

An email record is created to be saved in the database
The path to the attachments is recorded with the email record
Then the email record is placed on the outgoing queue and written to the database.

When its time to send the email, the system will process the emails from the queue. 
(This is the job of the Cron job)

From there, MIME email is generated and the file is attached to the email message. 
The mail is sent out and the attachments are deleted once the email is 
deleted from the queue.


Notes:
- Additional application receipts can be sent both to candidate & Admin
- A CC of the application can be forwarded to Admin
- If not logged in, candidate can log in through this page

*/


ini_set('max_execution_time', 120);
define ('NO_HOUSE_KEEPING', true);
require ("config.php");

require_once (dirname(__FILE__).'/'.JB_CANDIDATE_FOLDER."login_functions.php");
require_once (dirname(__FILE__)."/include/posts.inc.php");
require_once (dirname(__FILE__)."/include/resumes.inc.php");

$APM = JB_get_AppMarkupObject(); // Load the rendering class



if ($_REQUEST['post_id']!='') {
	$_SESSION['app_post_id'] = (int) $_REQUEST['post_id'];
}
$post_id = $_SESSION['app_post_id'];

if ($_REQUEST['username'] != '') {
	// candidate login through apply_iframe.php
	$_REQUEST['silent'] = 'yes'; // silent mode
	JB_validate_candidate_login(htmlentities($_SERVER['PHP_SELF']));
}

if (isset($_SESSION['JB_ID']) && ($_SESSION['JB_Domain']=='CANDIDATE')) {
	$user_id = (int) $_SESSION['JB_ID'];
} else {
	$user_id = null;
}


$JBMarkup->markup_open();
$JBMarkup->head_open();
$JBMarkup->title_meta_tag($label["c_loginform_title"]);
$JBMarkup->no_robots_meta_tag();
$JBMarkup->stylesheet_link(JB_get_maincss_url());
$JBMarkup->charset_meta_tag();


$JBMarkup->head_close();

$JBMarkup->body_open('style="background-color: white;"');


if ((!$user_id) && (JB_ONLINE_APP_SIGN_IN=='YES')) { // is the user logged in??

	// candidate is not logged in, so we show the login form.
	// We tell the login form to submit back to apply_iframe.php
	JB_can_login_form(JB_BASE_HTTP_PATH."apply_iframe.php");


} else {

	// show the application form

	$sql = "SELECT app_id FROM applications WHERE post_id='".jb_escape_sql($post_id)."' AND user_id='".jb_escape_sql($user_id)."' ";
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	if ($row['app_id']!='') {

		$APM->already_applied_msg();
	}

	$PForm = &JB_get_DynamicFormObject(1);
	$PForm->load($post_id);
	$employer_id = $PForm->get_value('user_id');

	$TITLE = $PForm->get_raw_template_value("TITLE");
	$EMAIL = trim($PForm->get_raw_template_value ("EMAIL"));
	$LOCATION = $PForm->get_raw_template_value ("LOCATION");
	$DATE = $PForm->get_raw_template_value ("DATE"); 
	$POSTED_BY = $PForm->get_raw_template_value ("POSTED_BY");
	$POSTED_BY_ID = $PForm->get_raw_template_value ("USER_ID");

	$sql = "SELECT * FROM `employers` WHERE `ID`='".jb_escape_sql($POSTED_BY_ID)."' ";
	$e_result = JB_mysql_query ($sql) or die (mysql_error());
	$e_row = mysql_fetch_array($e_result, MYSQL_ASSOC);

	if (!JB_validate_mail($EMAIL)) {
	
		# email is invalid. Attempt to grab email form employer...
		$EMAIL = trim($e_row['Email']);
	}



	if ($user_id) {

		$sql = "SELECT * FROM `users` WHERE `ID`='".jb_escape_sql($user_id)."' ";
		$result = JB_mysql_query ($sql) or die (mysql_error());
		$user_row = mysql_fetch_array($result, MYSQL_ASSOC);

	}


	if (JB_EMAIL_SIG_SWITCH == "YES") {

	   $sig = "\n\n---\n".$label["app_email_sent_from_sig"].JB_SITE_NAME.", ".JB_BASE_HTTP_PATH."index.php?post_id=".$post_id." ";
	   $sig_html = "<p>---<br>".$label["app_email_sent_from_sig"].JB_SITE_NAME.", ".JB_BASE_HTTP_PATH."index.php?post_id=".$post_id." ";
	}

	$apply = $_REQUEST["apply"];
	$app_letter = $_REQUEST["app_letter"];
	$app_email = $_REQUEST["app_email"];
	$app_name = $_REQUEST["app_name"];
	$app_subject = $_REQUEST["app_subject"];


	if ($apply != '') { // Send Application button pressed

		if ($app_letter == '') {
		   $error .= $APM->get_error_line($label["app_letter_error"]);
		}
		if ($app_email == '') {
		   $error .= $APM->get_error_line($label["app_email_error"]);
		} elseif (!JB_validate_mail($app_email)) {
			$error .= $APM->get_error_line($label["app_email_invalid"]);
		
		}

		if ($app_name== '') {
		   $error .= $APM->get_error_line($label["app_name_error"]);
		}

		if ($_FILES['att1']['name']!='') {
			$all1 = JB_is_filetype_allowed ($_FILES['att1']['name']);
			$all2 = JB_is_imagetype_allowed ($_FILES['att1']['name']);
			if (($all1==false) && ($all2==false)) {
				$label['app_att_not_allowed'] = str_replace("%FILE_NAME%", $_FILES['att1']['name'] , $label['app_att_not_allowed']);
				$error .= $APM->get_error_line($label["app_att_not_allowed"]);
			}

			if (($_FILES['att1']['error'])) {
				$label['app_att_too_big'] = str_replace("%FILE_NAME%", $_FILES['att1']['name'] , $label['app_att_too_big']);
				$error .= $APM->get_error_line($label["app_att_too_big"]);
			}
		}

		if ($_FILES['att2']['name']!='') {
			$all1 = JB_is_filetype_allowed ($_FILES['att2']['name']);
			$all2 = JB_is_imagetype_allowed ($_FILES['att2']['name']);
			if (($all1==false) && ($all2==false)) {
				$label['app_att_not_allowed'] = str_replace("%FILE_NAME%", $_FILES['att2']['name'] , $label['app_att_not_allowed']);
				$error .= $APM->get_error_line($label["app_att_not_allowed"]);
			}

			if (($_FILES['att2']['error'])) {
				$label['app_att_too_big'] = str_replace("%FILE_NAME%", $_FILES['att2']['name'] , $label['app_att_too_big']);
				$error .= $APM->get_error_line($label["app_att_too_big"]);
			}
		}

		if ($_FILES['att3']['name']!='') {
			$all1 = JB_is_filetype_allowed ($_FILES['att3']['name']);
			$all2 = JB_is_imagetype_allowed ($_FILES['att3']['name']);
			if (($all1==false) && ($all2==false)) {
				$label['app_att_not_allowed'] = str_replace("%FILE_NAME%", $_FILES['att3']['name'] , $label['app_att_not_allowed']);
				$error .= $APM->get_error_line($label["app_att_not_allowed"]);
			}

			if (($_FILES['att3']['error'])) {
				$label['app_att_too_big'] = str_replace("%FILE_NAME%", $_FILES['att3']['name'] , $label['app_att_too_big']);
				$error .=  $label['app_att_too_big'].$APM->get_line_break();
			}
		}

		if (!JB_validate_mail($EMAIL)) {
	
			$error = $label['app_employer_email_invalid'].$APM->get_line_break();
			
		}

		$success = false;
		if ($error != '') {
		
			$APM->error_msg($error);

		} else {

			$sql = "UPDATE `posts_table` SET `applications`=`applications`+1 WHERE `post_id`='".jb_escape_sql($post_id)."' ";
			JB_mysql_query ($sql) or die (mysql_error());

			if ($user_id) { // Is the user logged in?

				// get users' resume
				// does the user have a resume?
				$resume_row = array();
				$sql = "SELECT resume_id, `anon` FROM resumes_table WHERE user_id='".jb_escape_sql($user_id)."' AND `status`='ACT' ";
				$result = JB_mysql_query($sql) or die(mysql_error());
				if (mysql_num_rows($result) > 0) {
					$resume_row = mysql_fetch_array($result, MYSQL_ASSOC);
					$is_anon = $resume_row['anon'];
				} else {
					$is_anon = 'N';
				}

				// The user is logged in
				// save application..
				$now = (gmdate("Y-m-d H:i:s"));
				$sql = "INSERT INTO `applications` (`user_id`, `post_id`, `app_date`, `cover_letter`, `employer_id`, `employer_name`, `data1`, `data2`, `data3`) VALUES ( '".jb_escape_sql($user_id)."', '".jb_escape_sql($post_id)."', '".jb_escape_sql($now)."', '".jb_escape_sql($app_letter)."', '".jb_escape_sql($POSTED_BY_ID)."', '".jb_escape_sql(addslashes($POSTED_BY))."', '".jb_escape_sql(addslashes($TITLE))."', '".jb_escape_sql(addslashes($LOCATION))."', '".jb_escape_sql(addslashes($EMAIL))."') ";
				JB_mysql_query ($sql);


				##############
				# Automatically grant permission for employer to view
				// If anonymous fields are enabled

				if ((JB_RESUME_REQUEST_SWITCH=='YES')) {

					if (Jb_is_request_granted($user_id, $PForm->get_value('user_id'))===0) {
						// no request was sent / granted

						if (((JB_ONLINE_APP_REVEAL_PREMIUM=='YES') && ($PForm->get_value('post_mode')=='premium')) ||
						((JB_ONLINE_APP_REVEAL_STD=='YES') && ($PForm->get_value('post_mode')!='premium')) ||
						(JB_ONLINE_APP_REVEAL_RESUME=='YES')
						) {
							// Grant the request automatically - this will unblock candidate's resume details
							// for the user_id of the poster
							if (JB_grant_request ($user_id, $PForm->get_value('user_id'))) {
								
								// send an email to employer to notify them that a request has been granted
								$is_anon = 'N'; // not anonymous
								JB_send_request_granted_email($user_id, $PForm->get_value('user_id'));
							}

						}

					} 


				} else {
					$is_anon = 'N';
				}

			
			}

			// strip slashes from data before sending it by email
			// (Jamit job board adds slashes regardless of PHP config)
			 
			$app_letter = (stripslashes(JB_clean_str($_REQUEST['app_letter'])));
			$app_subject = (stripslashes(JB_clean_str($_REQUEST['app_subject'])));
			$app_name = (stripslashes(JB_clean_str($_REQUEST['app_name'])));
			$to_name = (stripslashes(JB_clean_str($POSTED_BY)));
			$to_address = stripslashes (JB_clean_str($EMAIL));
			
			/*
			*  Trying to guess your e-mail address.
			*  It is better that you change this line to your address explicitly.
			*  $from_address="me@mydomain.com";
			*  $from_name="My Name";
			*/

			$from_address=$app_email;
			$from_name=$app_name;
			$reply_name=$app_name;
			$reply_address=$app_email;
			$text_message=$app_letter;

			// Assign the Application template

			$e_result = JB_get_email_template (12, $_SESSION['LANG']); // html alert template

			if (mysql_num_rows($e_result)>0) {



				// can the employer view the applicant's name and email?
				// first, we put the values in to the form.

				$PForm->set_viewer($employer_id, 'EMPLOYER'); // process field restrictions for employer

				$PForm->set_value('app_name', $app_name);
				$PForm->set_value('app_email', $app_email);
				$PForm->set_value('user_id', $user_id); // this is so it can display the candidate # next to the field restriction message

				// Then we set the options for the values and then use the form
				// object to process the restrictions. The data values stored 
				// in the form will be modified if the fields are restricted.
				
				$field = array(
					'field_id' => 'app_name',
					'is_blocked' => 'Y',
					'is_anon' => $is_anon); // the application name is blocked but not anonymous

				if (JB_FIELD_BLOCK_APP_SWITCH!='YES') {
					$field['is_blocked'] = 'N'; // unblock on applications
				}
				$is_name_restricted = $PForm->process_field_restrictions($field, $employer_id, 'EMPLOYER');
				$field = array(
					'field_id' => 'app_email',
					'is_blocked' => 'Y',
					'is_anon' => $is_anon);
				if (JB_FIELD_BLOCK_APP_SWITCH!='YES') {
					$field['is_blocked'] = 'N'; // unblock on applications
				}
				$is_email_restricted = $PForm->process_field_restrictions($field, $employer_id, 'EMPLOYER');

				$PForm->set_value('user_id', $employer_id); // set back to user id.

				if (sizeof($resume_row) > 0) {

					$anon_q = 'a='.$is_anon;
					$key = substr(md5 ($is_anon.$resume_row['resume_id'].$user_row['Password'].$user_row['ID']), 0,10);
					$key_q = $anon_q.'&resume_id='.$resume_row['resume_id'].'&id='.$user_row['ID'].'&key='.$key;

					$resume_db_link = JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER."search.php?".$key_q;
				} else {
					$resume_db_link = $label["app_resume_notpres"];
				}

				//$app_name = $PForm->get_value('app_name');
				//$app_email = $PForm->get_value('app_email');

				$e_row = mysql_fetch_array($e_result, MYSQL_ASSOC);
				$text_message = $e_row['EmailText'];
				$text_message = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $text_message);
				$text_message = str_replace ("%SITE_NAME%", JB_SITE_NAME, $text_message);
				$text_message = str_replace ("%BASE_HTTP_PATH%", JB_BASE_HTTP_PATH, $text_message);
				$JobListAttributes = new JobListAttributes();
				$JobListAttributes->clear();
				$text_message = str_replace ("%POST_URL%", JB_job_post_url($post_id, $JobListAttributes, JB_BASE_HTTP_PATH.'index.php'), $text_message);
				$text_message = str_replace ("%POSTED_BY%", $POSTED_BY, $text_message);
				$text_message = str_replace ("%EMPLOYER_EMAIL%", $EMAIL, $text_message);
				$text_message = str_replace ("%JOB_TITLE%", $TITLE, $text_message);
				$text_message = str_replace ("%APP_NAME%", $app_name, $text_message);
				$text_message = str_replace ("%APP_EMAIL%", $app_email, $text_message);
				$text_message = str_replace ("%POST_ID%", $post_id, $text_message);
				$text_message = str_replace ("%APP_SUBJECT%", $app_subject, $text_message);
				$text_message = str_replace ("%APP_LETTER%", $app_letter, $text_message);
				$text_message = str_replace ("%APP_ATTACHMENT1%", $_FILES['att1']['name'], $text_message);
				$text_message = str_replace ("%APP_ATTACHMENT2%", $_FILES['att2']['name'], $text_message);
				$text_message = str_replace ("%APP_ATTACHMENT3%", $_FILES['att3']['name'], $text_message);
				$CandidateEmailMessage = $text_message;
				$text_message = str_replace ("%RESUME_DB_LINK%", $resume_db_link , $text_message);

				
				$PForm->reset_fields();
				while ($field = $PForm->next_field()) {

					// substitute template tags form the posting form
					$text_message = str_replace('%'.$field['template_tag'].'%', $PForm->get_template_value($field['template_tag']), $text_message);
					// substitute template tags for the subject
					$app_subject = str_replace('%'.$field['template_tag'].'%', $PForm->get_template_value($field['template_tag']), $app_subject);
				}
				

			} else { // there is no template defined on the system
				$text_message .= $sig; // append the signiture
			}

			$text_message = strip_tags($text_message);

			if ($user_id) { // if the user is logged in, load the users' resume
				$sql = "SELECT resume_id FROM resumes_table WHERE user_id='".jb_escape_sql($user_id)."'";
				$resume_result = JB_mysql_query($sql) or die (mysql_error());
				$resume_row = mysql_fetch_array($resume_result, MYSQL_ASSOC);
				if ($resume_row['resume_id'] !='') {
					
					$resume_data = JB_load_resume_data($resume_row['resume_id']);
				}
			}
			
			if ($PForm->get_value('post_mode')!='premium') { // standard post?
				# SEND THE APP TO EMPLOYER 
				if (JB_ONLINE_APP_EMAIL_STD=='YES') {
					$mail_id = JB_queue_mail($to_address, $to_name, $from_address, $from_name, $app_subject, $text_message, '',  12, true);	
				}
				

			} elseif ($PForm->get_value('post_mode')=='premium') { // premium posts?
				##########################################
				# Send the app to employer
				if (JB_ONLINE_APP_EMAIL_PREMIUM=='YES') {

					$mail_id = JB_queue_mail($to_address, $to_name, $from_address, $from_name, $app_subject, $text_message, '',  12, true);
					
				}
				
			}

			############################################
			# SEND THE app TO ADMIN
			if (JB_ONLINE_APP_EMAIL_ADMIN=='YES') { // email app to admin too?

				JB_queue_mail_cc(addslashes($mail_id), addslashes(JB_SITE_NAME), addslashes(JB_SITE_CONTACT_EMAIL));	
			}
			
			$APM->ok_msg($label['app_sent']);
			
			if ($user_id) {
				$label['app_account_links'] = str_replace ('%CANDIDATE_FOLDER%', JB_CANDIDATE_FOLDER, $label['app_account_links']);

				$APM->links();
			}

			
			// send receipts.
			// prepare message for the Application receipt.
			
			$result = JB_get_email_template (10, $_SESSION['LANG']);
			$e_row = mysql_fetch_array($result, MYSQL_ASSOC);
			$EmailMessage = $e_row['EmailText'];

			$EmailMessage = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $EmailMessage);
			$EmailMessage = str_replace ("%SITE_NAME%", JB_SITE_NAME, $EmailMessage);
			$EmailMessage = str_replace ("%POSTED_BY%", $POSTED_BY, $EmailMessage);			
			$EmailMessage = str_replace ("%POST_URL%", JB_job_post_url($post_id, $JobListAttributes, JB_BASE_HTTP_PATH.'index.php'), $EmailMessage);
			$EmailMessage = str_replace ("%EMPLOYER_EMAIL%", $EMAIL, $EmailMessage);
			$EmailMessage = str_replace ("%JOB_TITLE%", $TITLE, $EmailMessage);
			$EmailMessage = str_replace ("%APP_NAME%", $app_name, $EmailMessage);
			$EmailMessage = str_replace ("%POST_ID%", $post_id, $EmailMessage);
			$EmailMessage = str_replace ("%APP_EMAIL%", $app_email, $EmailMessage);
			$EmailMessage = str_replace ("%APP_SUBJECT%", $app_subject, $EmailMessage);
			$EmailMessage = str_replace ("%APP_LETTER%", $app_letter, $EmailMessage);
			$EmailMessage = str_replace ("%APP_ATTACHMENT1%", $_FILES['att1']['name'], $EmailMessage);
			$EmailMessage = str_replace ("%APP_ATTACHMENT2%", $_FILES['att2']['name'], $EmailMessage);
			$EmailMessage = str_replace ("%APP_ATTACHMENT3%", $_FILES['att3']['name'], $EmailMessage);

			$PForm->set_viewer($employer_id, 'EMPLOYER');
			$PForm->reset_fields();
			while ($field = $PForm->next_field()) {

				// substitute template tags form the posting form
				$EmailMessage = str_replace('%'.$field['template_tag'].'%', $PForm->get_template_value($field['template_tag']), $EmailMessage);
				
			}
			
			$CandidateEmailMessage = $EmailMessage; // take a copy - the $CandidateEmailMessage does not have a link to the resume db
			$EmailMessage = str_replace ("%RESUME_DB_LINK%", "$resume_db_link" , $EmailMessage);

			
			

			if (JB_EMAIL_ADMIN_RECEIPT_SWITCH =="YES") {

				$message = $EmailMessage;
				$message .= $sig;
				$subject = $APM->get_admin_receipt_email_subject($app_name); // $label['app_receipt_subject']." ($app_name)";

				JB_queue_mail(JB_SITE_CONTACT_EMAIL, "Admin", JB_SITE_NAME, JB_SITE_CONTACT_EMAIL, $subject, $message, '',  10);

			}


			if (JB_EMAIL_CANDIDATE_RECEIPT_SWITCH == "YES") {

				$CandidateEmailMessage = str_replace ("%RESUME_DB_LINK%", JB_BASE_HTTP_PATH , $CandidateEmailMessage);

				
				$message = $CandidateEmailMessage;
				$message .= $sig_html;
				$subject = $APM->get_receipt_email_subject($TITLE, $DATE); 

				JB_queue_mail($app_email, $app_name, JB_SITE_CONTACT_EMAIL, JB_SITE_NAME, $subject, $message, '',  10);

			}

			$APM->success_start();

			$APM->success_row($label['app_confirm_name'], $app_name);
			$APM->success_row($label['app_confirm_email'], $app_email);

			$APM->success_row($label['app_confirm_subject'], $app_subject);
			$APM->success_row($label['app_confirm_lettter'], $app_letter);
			$APM->success_row($label['app_confirm_att1'], $_FILES['att1']['name']);
			$APM->success_row($label['app_confirm_att2'], $_FILES['att2']['name']);
			$APM->success_row($label['app_confirm_att3'], $_FILES['att3']['name']);
			
			JBPLUG_do_callback('apply_success_row', $mail_id);

			$APM->success_end();

			$success = 1;
		   
		} 

	} // End Apply button  pressed
	 
	 
	 if (!$success) {
		if ($app_subject == '') {
			
			$app_subject = $label['app_email_subject'];
			$app_subject = str_replace ("%DATE%", $DATE, $app_subject);
			$app_subject = str_replace ("%TITLE%", $TITLE, $app_subject);
			$app_subject = str_replace ("%SITE_NAME%", JB_SITE_NAME, $app_subject);

		}

		if (($user_id != '') && ($error=='')) {

			// retreive old application, so that it can be pre-file;ed

			$app_email = $user_row['Email'];
			$app_name = JB_get_formatted_name($user_row['FirstName'], $user_row['LastName']);

			$sql = "SELECT * FROM applications WHERE `user_id`='".jb_escape_sql($user_id)."'  ORDER BY app_date DESC LIMIT 1 ";

			$app_result = JB_mysql_query($sql);
			$app_row = mysql_fetch_array($app_result, MYSQL_ASSOC);

			$app_letter = $app_row["cover_letter"];

		}

		JB_template_application_form($post_id, $app_name, $app_email, $app_subject, $app_letter, $att1, $att2, $att3);
	
	}

}


$JBMarkup->body_close();
$JBMarkup->markup_close();
?>