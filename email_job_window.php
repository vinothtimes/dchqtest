<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

define ('NO_HOUSE_KEEPING', true);
require ("config.php");

if (JB_TAF_ENABLED != 'YES') {
	die('Feature disabled');
}

echo $JBMarkup->get_doctype();

$JBMarkup->markup_open(); //
$JBMarkup->head_open(); 
$JBMarkup->stylesheet_link(JB_get_maincss_url());// <link> to main.css
$JBMarkup->charset_meta_tag();  // character set 
$JBMarkup->no_robots_meta_tag(); // do not follow, do not index


$JBMarkup->head_close(); 

$JBMarkup->body_open('style="background-color:white"');



$submit = JB_clean_str($_REQUEST['submit']);
$post_id = (int) $_REQUEST['post_id'];
$url = JB_clean_str($_REQUEST['url']);

// Assume quotes is always On, we need to strip slashes.

$subject = JB_clean_str(stripslashes($_REQUEST['subject']));
$message = JB_clean_str(stripslashes($_REQUEST['message']));
$your_name = JB_clean_str(stripslashes($_REQUEST['your_name']));
$your_email = JB_clean_str(stripslashes($_REQUEST['your_email']));
$to_email = JB_clean_str(stripslashes($_REQUEST['to_email']));
$to_name = JB_clean_str(stripslashes($_REQUEST['to_name']));

if (strlen(trim($to_name))==0) {
	$to_name = $to_email;
}

if (strlen(trim($your_name))==0) {
	$your_name = JB_get_formatted_name($_SESSION['JB_FirstName'], $_SESSION['JB_LastName']);

}

if (strlen(trim($your_email))==0) { 
	//$your_email = 
	$sql = "SELECT Email from users WHERE ID='".jb_escape_sql($_SESSION['JB_ID'])."'";
	$result = jb_mysql_query($sql);
	if (mysql_num_rows($result)) {
		$your_email = array_pop(mysql_fetch_row($result));
	}
}

if ($submit != '') {

	if ($your_email == '') {
		$error .= $label['taf_email_blank']." <br>";

	} elseif (!JB_validate_mail($your_email)) {
		$error .= $label['taf_email_invalid']."<br>";
	}

	if ($your_name == '') {
		$error .= $label['taf_name_blank']."<br>";

	}

	if ($to_email == '') {
		
		$error .= $label['taf_f_email_blank']."<br>";

	} elseif (!JB_validate_mail($to_email)) {
		$error .= $label['taf_f_email_invalid']."<br>";
	}

	if ($subject == '') {
		$error .= $label['taf_subject_blank']."<br>";

	}

	// new checks to discourage spam

	if (!empty($message) && (strlen($message)>140)) {
		// that's about a paragraph, 3 lines
		$error .= $label['taf_msg_too_long']."<br>";

	}

	if (!empty($subject) && (strlen($subject)>35)) {
		$error .= $label['taf_subj_too_long']."<br>";
	}

	if (strpos($message, '://')!==false) {
		// no URLs allowed
		$error .= $label['taf_no_url']."<br>";
	}





	if ($error == '') {
		// send the sucker.

		$to = $to_email; //$users[$i];
		$from = $your_email; // Enter your email adress here

		$msg = "".
			$label['taf_msg_to']." $to_name <$to_email>\r\n".
			$label['taf_msg_from']." $your_name <$your_email>\r\n\r\n".
			//$label['taf_msg_line']." ".JB_SITE_NAME ."\r\n\r\n".
			str_replace('%SITE_NAME%', JB_SITE_NAME, $label['taf_msg_line'])."\r\n\r\n".	
			$label['taf_msg_link']."\r\n".
			"$url\r\n\r\n".
			$label['taf_msg_comments']."\r\n".
			$message;

		// to discourage spam, include IP of sender:
		$ip = $_SERVER['REMOTE_ADDR'];
		if (!empty($_SERVER['X-FORWARDED-FOR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		$msg .= "---\nX-Sender-IP: ".$ip;

		
		echo $label['taf_sending_email'];

		// anti-spam, we check the email queue, find the last 10 emails in the last 5 minutes
		// then we silently discard if matches our basic checks
		$discard = false;
		$sql = "SELECT * FROM `mail_queue` WHERE `template_id` =46 AND mail_date > DATE_SUB( NOW( ) , INTERVAL 5 MINUTE ) LIMIT 10 ";
		$result = jb_mysql_query($sql);

		if (mysql_num_rows($result)>0) {
			
			$score = 0;
			

			$max_score = 40; // adjust this when adjusting the score rules below
			while ($row = mysql_fetch_array($result)) {
				if ($row['subject']===$subject) { // repeat subject
					$score += 2;
				}
				
				if (strpos($row['message'], $your_name)!==false) { // re-used name
					$score++;
				}
				if (strpos($row['message'], $your_email)!==false) { // re-used email
					$score++;
				}
			}
			$score = $score / $max_score;
			if ($score >= 0.5) {
				$discard = true; // silently discard this message
			}
		
		}


		if (!$discard) {
			$email_id = JB_queue_mail($to, $to_name, JB_SITE_CONTACT_EMAIL, JB_SITE_NAME, $subject, $msg, '', 46);
			JB_process_mail_queue(1, $email_id);
		}


		?>

		 <hr><?php echo $label['taf_email_sent']; ?> <?php echo jb_escape_html($to_email); ?><b/><br>
		 <p style="text-align:center;"><input onclick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF'])."?post_id=$post_id";?>'" type='button' value='<?php echo $label['taf_button_send_again'];?>'><input onclick='window.close(); return false' type="button" value="<?php echo $label['taf_button_close_window'];?>"></p>
		 <?php


	} else {
		$success = false;
		$JBMarkup->error_msg($label['taf_error']);
		echo $error;
	}

}


if (($submit == '') || ($error!='') ) {

	JB_template_email_job();

}
JBPLUG_do_callback('taf_before_body_end', $A = false);

$JBMarkup->body_close(); // </body>
$JBMarkup->markup_close(); // </html>
?>