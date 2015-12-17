<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require("../config.php");
$page_title = $label['c_forgot_head']." - ".jb_escape_HTML(JB_SITE_NAME);

JB_template_candidates_outside_header($page_title);

JB_template_candidates_forget_pass_form();

$submit = $_REQUEST['submit'];
$email = $_REQUEST['email'];
function make_password (){

	while (strlen($pass) < 5) { 
	   $pass .= chr(rand (97,122)); 
	  }
	  return $pass;

}

if ($_REQUEST['email'] != '') {


	$sql = "select * from users where `Email`='".jb_escape_sql($email)."'";
	//echo $sql;
	$result=JB_mysql_query($sql);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	if ($row['Email'] != '') {
		
		if ($_SESSION['PASS_SENT_TO']!=$_REQUEST['email']) { // password was not sent.

			//echo "email found";
			$pass = make_password();
			//echo " $pass";
			$md5pass = md5 ($pass);
			$sql = "update `users` SET `Password`='$md5pass' where `ID`='".jb_escape_sql($row['ID'])."'";
			JB_mysql_query($sql) or die(mysql_error().$sql);

			JBPLUG_do_callback('can_new_pass', $pass, $row['Username']); // note for plugin authors: your plugin should store the $pass for the username in your external database. 


			//Here the emailmessage itself is defined, this will be send to your members. Don't forget to set the validation link here.
			$result = JB_get_email_template (3, $_SESSION['LANG']);
			$e_row = mysql_fetch_array($result, MYSQL_ASSOC);
			$EmailMessage = $e_row['EmailText'];
			$from = $e_row['EmailFromAddress'];
			$from_name = $e_row['EmailFromName'];
			$subject = $e_row['EmailSubject'];

			$subject = str_replace ("%MEMBERID%", $Username, $subject);

			$EmailMessage = str_replace ("%FNAME%", $row['FirstName'], $EmailMessage);
			$EmailMessage = str_replace ("%LNAME%", $row['LastName'], $EmailMessage);
			$EmailMessage = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $EmailMessage);
			$EmailMessage = str_replace ("%SITE_NAME%", JB_SITE_NAME, $EmailMessage);
			$EmailMessage = str_replace ("%MEMBERID%", $row['Username'], $EmailMessage);
			$EmailMessage = str_replace ("%PASSWORD%", $pass, $EmailMessage);

			$to = stripslashes($email);
			
			$message = $EmailMessage;

			$email_id = JB_queue_mail($to, jb_get_formatted_name($row['FirstName'], $row['LastName']), $from, $from_name, $subject, $message, '', 3);
			JB_process_mail_queue(1, $email_id);

			$_SESSION['PASS_SENT_TO'] = $_REQUEST['email']; // remember the email it was sent to

		}


		$label["c_forgot_changed"] = str_replace ("%SEND_TO%", $_REQUEST['email'], $label["c_forgot_changed"]);

		echo "<p><center><b>".$label["c_forgot_changed"]." <a href='".JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER."'>".JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER."  </a></b></center></p>";

	} else {

	   echo "<center>".$label["c_forgot_not_found"]."</center>";
	}

}
?>
<center><a href="../index.php"><?php echo $label["c_forgot_continue"];?></a></center>
<?php

JB_template_candidates_outside_footer();

?>