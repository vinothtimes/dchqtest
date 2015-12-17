<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

require "../config.php";

$submit = $_REQUEST['submit'];
$email = $_REQUEST['email'];

$page_title = $label["employer_forgot_title"]." - ".jb_escape_HTML(JB_SITE_NAME);

JB_template_employers_outside_header($page_title);

JB_template_employers_forget_pass_form();


function make_password (){

	while (strlen($pass) < 5) { 
	   $pass .= chr(rand (97,122)); 
	  }
	  return $pass;

  
}

if ($_REQUEST['email'] != '') {



$sql = "select * from employers where `Email`='".jb_escape_sql($email)."'";

$result=JB_mysql_query($sql);
$row = mysql_fetch_array($result, MYSQL_ASSOC);

if ($row['Email'] != '') {

   if ($row['Validated']=='0') {
	$label["employer_forgot_error1"] = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL , $label["employer_forgot_error1"]);
      echo "<center>".$label["employer_forgot_error1"]."</center>";

   } else {

	   if ($_SESSION['PASS_SENT_TO']!=$_REQUEST['email']) { // password was not sent.

			
			$pass = make_password();
			
			$md5pass = md5 ($pass);
			$sql = "update `employers` SET `Password`='$md5pass' where `ID`='".jb_escape_sql($row['ID'])."'";
			JB_mysql_query($sql) or die(mysql_error().$sql);

			JBPLUG_do_callback('emp_new_pass', $pass, $row['Username']); // note for plugin authors: your plugin should store the $pass for the username in your external database. 


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

		$label["employer_forgot_success1"] = str_replace ("%BASE_HTTP_PATH%", JB_BASE_HTTP_PATH , $label["employer_forgot_success1"]);
		$label["employer_forgot_success1"] = str_replace ("%EMPLOYER_FOLDER%", JB_EMPLOYER_FOLDER , $label["employer_forgot_success1"]);

		echo "<p><center><b>".$label["employer_forgot_success1"]."</b></center></p>";

	
   }


} else {

   echo "<P align='center'>".$label["employer_forgot_email_notfound"]."</p>";
}

}

?>

<P align="center"><b><a href="../index.php"><?php echo $label["employer_forgot_job_board"];?></a></b></p>

<?php
JB_template_employers_outside_footer();

?>