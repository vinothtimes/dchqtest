<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require ("../config.php");

$post_id = (int) $_REQUEST['post_id'];
$key = jb_alpha_numeric($_REQUEST['key']);
$approve_post = jb_alpha_numeric($_REQUEST['approve_post']);
$disapprove_post = jb_alpha_numeric($_REQUEST['disapprove_post']);
$reason = ($_REQUEST['reason']);

if ($post_id > 0) {
	$JBPage = new JBJobPage($post_id, $admin=true); 
}

global $JBMarkup;
echo $JBMarkup->get_admin_doctype();
$JBMarkup->markup_open();

$JBMarkup->head_open();

$JBMarkup->title_meta_tag($title);
$JBMarkup->stylesheet_link(JB_get_admin_maincss_url());
$JBMarkup->charset_meta_tag();
$JBMarkup->head_close();
$JBMarkup->body_open();

if ($post_id != '') {

	$comp_key = md5($post_id.JB_ADMIN_PASSWORD);

	if ($comp_key === $key) {
		require_once('../include/posts.inc.php');
		if ($approve_post!='') {

			$PForm = &JB_get_DynamicFormObject(1);

			$sql = "UPDATE `posts_table` SET `approved`='Y', `reason`='' WHERE `post_id`='".jb_escape_sql($post_id)."'";
			JB_mysql_query($sql) or die (mysql_error());
			$JBMarkup->ok_msg("Job Post #".jb_escape_html($post_id)." approved!");

			JB_finalize_post_updates();

			$PForm->load($post_id);
			JB_update_post_category_count($PForm->get_values());

			// send out the email to the employer

		
			if (JB_EMAIL_POST_APPR_SWITCH == "YES") {

				// send approval notification email to employer

				$TITLE = ($PForm->get_raw_template_value ("TITLE"));
				$DATE = JB_get_formatted_date($PForm->get_template_value ("DATE"));
				$POSTED_BY_ID = $PForm->get_value('user_id');

				// get the employer
				$sql = "SELECT * FROM employers WHERE ID='".jb_escape_sql($POSTED_BY_ID)."' ";

				$emp_result = jb_mysql_query($sql);
				$emp_row = mysql_fetch_array($emp_result);

				// get the email template
				$template_result = JB_get_email_template (220, $emp_row['lang']); 
				$t_row = mysql_fetch_array($template_result);

				$to_address = $emp_row['Email'];
				$to_name = jb_get_formatted_name($emp_row['FirstName'], $emp_row['LastName']);
				$subject = $t_row['EmailSubject'];
				$message = $t_row['EmailText'];
				$from_name = $t_row['EmailFromName'];
				$from_address = $t_row['EmailFromAddress'];

				/*substitute the vars

				%LNAME% - last name of the user
				%FNAME% - first name of the user
				%SITE_NAME% - name of your website
				%SITE_URL% - URL to your site
				%SITE_CONTACT_EMAIL% - contact email to your site.
				%POST_TITLE% - The title of the post
				%POST_DATE% - The date of the post
				%POST_URL% - The URL of the post
				
				*/

				$message = str_replace("%LNAME%", $emp_row['LastName'], $message);
				$message = str_replace("%FNAME%", $emp_row['FirstName'], $message);
				$message = str_replace("%SITE_NAME%", JB_SITE_NAME, $message);
				$message = str_replace("%SITE_URL%", JB_BASE_HTTP_PATH, $message);
				$message = str_replace("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $message);
				$message = str_replace("%POST_TITLE%", $TITLE, $message);
				$message = str_replace("%POST_DATE%", $DATE, $message);
				$message = str_replace("%POST_URL%", JB_BASE_HTTP_PATH."index.php?post_id=".$PForm->get_value('post_id'), $message);
				

				// Place the email on the queue!

				JB_queue_mail($to_address, $to_name, $from_address, $from_name, $subject, $message, '', 220);

			}

			echo "<hr>";

		}

		if ($disapprove_post!='') {

			if ($reason == '') {

				echo "Please specify a reason<hr>";

			} else {

				$sql = "UPDATE `posts_table` SET `approved`='N', `reason`='".jb_escape_sql($reason)."' WHERE `post_id`='".jb_escape_sql($post_id)."'";
				JB_mysql_query($sql) or die (mysql_error().$sql);
				$JBMarkup->ok_msg("Job Post #".jb_escape_html($post_id)." disapproved!");
				
				$PForm = &JB_get_DynamicFormObject(1);
				$PForm->load($post_id);
				JB_update_post_category_count($PForm->get_values());

				JB_finalize_post_updates();

				if (JB_EMAIL_POST_DISAPP_SWITCH == "YES")  {

					// send out the disapproval notification to the employer

					$TITLE = ($PForm->get_raw_template_value ("TITLE"));
					$DATE = JB_get_formatted_date($PForm->get_template_value ("DATE"));
					$POSTED_BY_ID = $PForm->get_value('user_id');

					// get the employer
					$sql = "SELECT * FROM employers WHERE ID='".jb_escape_sql($POSTED_BY_ID)."' ";

					$emp_result = jb_mysql_query($sql);
					$emp_row = mysql_fetch_array($emp_result);

					// get the email template
					$template_result = JB_get_email_template (230, $emp_row['lang']); 
					$t_row = mysql_fetch_array($template_result);

					$to_address = $emp_row['Email'];
					$to_name = jb_get_formatted_name($emp_row['FirstName'], $emp_row['LastName']);
					$subject = $t_row['EmailSubject'];
					$message = $t_row['EmailText'];
					$from_name = $t_row['EmailFromName'];
					$from_address = $t_row['EmailFromAddress'];

					/*substitute the vars

					%LNAME% - last name of the user
					%FNAME% - first name of the user
					%SITE_NAME% - name of your website
					%SITE_URL% - URL to your site
					%SITE_CONTACT_EMAIL% - contact email to your site.
					%POST_TITLE% - The title of the post
					%POST_DATE% - The date of the post
					%REASON% - The reason for the disapproval
					
					*/

					$message = str_replace("%LNAME%", $emp_row['LastName'], $message);
					$message = str_replace("%FNAME%", $emp_row['FirstName'], $message);
					$message = str_replace("%SITE_NAME%", JB_SITE_NAME, $message);
					$message = str_replace("%SITE_URL%", JB_BASE_HTTP_PATH, $message);
					$message = str_replace("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $message);
					$message = str_replace("%POST_TITLE%", $TITLE, $message);
					$message = str_replace("%POST_DATE%", $DATE, $message);
					$message = str_replace("%REASON%", stripslashes($reason), $message);
					

					// Place the email on the queue!

					JB_queue_mail($to_address, $to_name, $from_address, $from_name, $subject, $message, '', 230);

				}

				echo "<hr>";


			}


		} 

		if ($_REQUEST['delete_post']!='') {

			JB_delete_post($post_id);
			JB_finalize_post_updates();
			$JBMarkup->ok_msg('Job Post #'.$post_id.' deleted.');
			
			echo "<hr>";

		}

		?>
		<h2>Remote Admin - Job Post #<?php echo htmlentities($post_id); ?></h2>
		<form action="<?php echo htmlentities($_REQUEST['PHP_SELF']);?>" method="POST">
		<input type="hidden" name="post_id" value="<?php echo htmlentities($post_id); ?>">
		<input type="hidden" name="key" value="<?php echo htmlentities($key); ?>">
		<input type="submit" value="Approve" name='approve_post'><br>
		<input type="submit" value="Disapprove" name='disapprove_post'> Reason:<input name='reason' type="text" ><br><br>
		<input type="submit" value="Delete" name='delete_post'><br>
		</form>
		<hr>

		<?php
		
		$JBPage->output('HALF');

	}

}


if ($_REQUEST['resume_id'] != '') {

	$comp_key = md5($_REQUEST['resume_id'].JB_ADMIN_PASSWORD);

	if ($comp_key === $key) {

		require_once('../include/resumes.inc.php');

		if ($_REQUEST['approve_resume']!='') {

			$sql = "UPDATE `resumes_table` SET `approved`='Y' WHERE `resume_id`='".jb_escape_sql($_REQUEST['resume_id'])."' ";
			JB_mysql_query($sql) or die(mysql_error());
			$JBMarkup->ok_msg('Resume Approved.');
			echo "<hr>";

		}

		

		if ($_REQUEST['delete_resume']!='') {

			JB_delete_resume ($_REQUEST['resume_id']);			
			$JBMarkup->ok_msg('Resume Deleted.');
			echo "<hr>";

		}

		?>
		<h2>Remote Admin - Resume #<?php echo jb_escape_html($_REQUEST['resume_id']);?></h2>
		<form action="<?php echo htmlentities($_REQUEST['PHP_SELF']);?>" method="POST">
		<input type="hidden" name="resume_id" value="<?php echo htmlentities($_REQUEST['resume_id']); ?>">
		<input type="hidden" name="key" value="<?php echo htmlentities($key); ?>">
		<input type="submit" value="Approve" name='approve_resume'><br>
		<br>
		<input type="submit" value="Delete" name='delete_resume'><br>
		</form>
		<hr>

		<?php

		$RForm = &JB_get_DynamicFormObject(2);
		$RForm->load($_REQUEST['resume_id']);

		if (sizeof($RForm->get_values())!=0) {
		
			$RForm->display_form('view', true);

		} else {

			echo "This resume does not exist on the system.";

		}

	}

}


$JBMarkup->body_close();
$JBMarkup->markup_close();

?>