<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
$timestart = microtime();

require "../config.php";
# Copyright 2005-2009 Jamit Software
# http://www.jamit.com/
require (dirname(__FILE__)."/admin_common.php");
ini_set('max_execution_time', 100200);


JB_admin_header('Admin -> Newsletter');

?>
<b>[Newsletters]</b> <span style="background-color: #FFFFCC; border-style:outset; padding:5px; "><a href="newsletter.php">Create / Send</a></span>

<hr>

<?php 

$to = $_REQUEST['to'];

if ($to=="CA") { 
	$who = "Candidates";
	$sql = "SELECT * from `users` WHERE `Newsletter`=1";
	$result = JB_mysql_query($sql);
	$count = mysql_num_rows($result);
} else {
	$who = "Employers";
	$sql = "SELECT * from `employers` WHERE `Newsletter`=1";
	$result = JB_mysql_query($sql);
	$count = mysql_num_rows($result);
}

$action = $_REQUEST['action'];

if ($action == "send") { // send button was pressed

	echo "Sending Email.. <br>";

	$sql = "SELECT * from `newsletters` WHERE `status`=0"; // get newsletters that not completed sending
	$letter_result = JB_mysql_query($sql) or die (mysql_error());
	echo mysql_num_rows($letter_result)." lists to process";
	while ($letter_row = mysql_fetch_array($letter_result, MYSQL_ASSOC)) {

		if ($letter_row["to"] == "CA") {
			$table = "`users`";
		}

		if ($letter_row["to"] == "EM") {
			$table = "`employers`";
		}

		// get the users to send
		$sql = "SELECT * FROM $table WHERE `Newsletter`='1' AND '".jb_escape_sql($letter_row['create_time'])."' > `newsletter_last_run` ";

		$result = JB_mysql_query($sql) or die ($sql.mysql_error());

		echo "Processing new list<br><br>";
		echo mysql_num_rows($result)." emails to send in this list to:".$letter_row['to']."<br>";

		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

			echo "Sending to: ".JB_get_formatted_name($row['FirstName'], $row['LastName'])." <br>";
			if (JB_validate_mail($row['Email'])) {

				// send the sucker

				 $msg = str_replace ( "%name%", JB_get_formatted_name($row['FirstName'], $row['LastName']), $letter_row['message']);
				 $msg = str_replace ( "%username%", $row['Username'], $msg);
				 $msg = str_replace ( "%email%", $row['Email'], $msg);

			
				 $subject = ($letter_row['subject']);

				$msg = str_replace ( "%CANDIDATE_LINK%", JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER."alerts.php?id=".$row['ID']."&key=$val", $msg);
				$msg = str_replace ( "%EMPLOYER_LINK%", JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER."alerts.php?id=".$row['ID']."&key=$val", $msg);
				
				
				 $to = $row['Email'];
				 
				 $from = JB_SITE_CONTACT_EMAIL; // Enter your email adress here
				
				JB_queue_mail($to, JB_get_formatted_name($row['FirstName'], $row['LastName']), $from, JB_SITE_NAME, $subject, $msg, '',  30);

				echo "<hr>Email to:". jb_escape_html($row['Email'])." placed on queue<br>";

				$now = (gmdate("Y-m-d H:i:s"));
				$sql = "UPDATE $table SET `newsletter_last_run`='$now' WHERE `ID`='".jb_escape_sql($row['ID'])."' ";
				JB_mysql_query($sql) or die (mysql_error());

				

			} else {

				echo "Invalid email ".jb_escape_html($row['Email'])."<br>";

			}

			
		}

		/*
		// run the above query again to see if we processed all the recipients.
		$sql = "SELECT * FROM $table WHERE `Newsletter`='1' AND '".$letter_row['create_time']."' > `newsletter_last_run` ";
		$result_check = JB_mysql_query($sql) or die(mysql_error());
		if (mysql_num_rows($result_check)==0) {
			// newsletter sent!
			$sql = "UPDATE `newsletters` SET `status`=1 WHERE `letter_id`='".$letter_row[letter_id]."' ";
			JB_mysql_query($sql) or die(mysql_error());
		}
		*/

		// set status to sent.

		$sql = "UPDATE `newsletters` SET `status`=1 WHERE `letter_id`='".jb_escape_sql($letter_row['letter_id'])."' ";
		JB_mysql_query($sql) or die(mysql_error());

		

	}


}

if ($action == "delete") {
	$letter_id = $_REQUEST['letter_id'];
	$sql = "DELETE FROM `newsletters` WHERE `letter_id`='".jb_escape_sql($letter_id)."'";
	JB_mysql_query($sql) or die (mysql_error());

}

if ($action == "save") {

	$letter_id = $_REQUEST['letter_id'];
	$to = $_REQUEST['to'];
	$subject = ($_REQUEST['subject']);
	$message = ($_REQUEST['message']);
	

	if ($to == '') {
		$error .= "* To is blank.<br>";

	}
	if ($subject == '') {
		$error .= "* Subject is blank.<br>";

	}
	if ($message == '') {
		$error .= "* Message is blank.<br>";

	}

	if ($letter_id == '') {
		$now = (gmdate("Y-m-d H:i:s"));
		$sql = "INSERT INTO `newsletters` (`to` , `subject` , `message` , `create_time` , `status` ) VALUES ('".jb_escape_sql($to)."', '".jb_escape_sql($subject)."', '".jb_escape_sql($message)."', '$now', '0' )";

		//echo "[".$sql."]";
		
		
	} else {
		$sql = "UPDATE `newsletters` SET `to`='".jb_escape_sql($to)."', `subject`='".jb_escape_sql($subject)."', `message`='".jb_escape_sql($message)."' WHERE `letter_id`='".jb_escape_sql($letter_id)."'";
		//echo $sql;
		
	}

	if ($error == '') {

		JB_mysql_query ($sql) or die (mysql_error());

		echo "Message Saved.<br>";
	} else {
		echo "<b>ERROR, CANNOT SAVE BECAUSE</b>:<br>$error";
		$action = "edit";

	}

} 

if ($_REQUEST['view_id']) {
	
	$sql = "SELECT * FROM `newsletters` WHERE `letter_id`='".jb_escape_sql($_REQUEST['view_id'])."'";
	$result  = JB_mysql_query($sql) or die (mysql_error());

	if ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		
		echo "Letter To: <pre>".$row['to']."</pre><br>";
		echo "Subject: <pre>".$row['subject']."</pre><br>";
		echo "Message:<br><pre>".$row['message']."</pre>";

	}

}

if (($action != "edit" ) && ($action != "new" )) {

	$sql = "SELECT * from `newsletters` order by `create_time`";
	$result = JB_mysql_query($sql) or die(mysql_error());
	

	if (mysql_num_rows($result) == 0) {

		echo "You have no newsletters on file. Press 'New Letter' to create an new newsletter.<br>";

	} else {

?>



<table cellSpacing="1" cellPadding="3" style="margin: 0 auto; background-color: #d9d9d9; width:100%; border:0px" >
<tr bgColor="#eaeaea">
		<td><font face="Arial" size="2"><b>Created</b></font></td>
		<td><font face="Arial" size="2"><b>To</b></font></td>
		<td><font face="Arial" size="2"><b>Subject</b></font></td>
		<td><font face="Arial" size="2"><b>Status</b></font></td>
		
		<td></td>
	</tr>
<?php

while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

?>
	<tr bgcolor="<?php echo ($row['letter_id']==$_REQUEST['view_id']) ? '#FFFFCC' : '#ffffff'; ?>">
		<td><font size="2"><?php echo $row['create_time'];?></font></td>
		<td><font size="2"><?php echo $row['to'];?></font></td>
		<td><font size="2"><a href="newsletter.php?view_id=<?php echo $row['letter_id']; ?>"><?php echo $row['subject'];?></a></font></td>
		<td><font size="2"><?php if ($row['status']==0) { echo "Not Sent";} else { echo 'Sent';} ?></font></td>
		<td> <input type="button" <?php if ($row['status']==1) echo ' disabled '; ?> value="Edit" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=edit&letter_id=<?php echo $row['letter_id']; ?>'"><input  type="button" value="Delete" onClick="if (!confirmLink(this, 'Delete, are you sure?')) return false; window.location='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=delete&amp;letter_id=<?php echo $row['letter_id']; ?>'"></td>
	</tr>

<?php
}


?>

	</table>
	<?php


	} 

	$sql = "SELECT * from `newsletters` WHERE `status`='0' ";
	$result_un= JB_mysql_query($sql) or die(mysql_error());
	$unsent_newsletters = mysql_num_rows($result_un);

	if ($unsent_newsletters <= 0 )  {
	?>

		<input type="button" name="" value="New Letter" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=new'"> 

	<?php

	}



?>

		<input type="button" value="Send Emails" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=send&letter_id=<?php echo $row['letter_id']; ?>'">
		<?php

} // end if action

if (($action == "edit") || (($action == "new") && ($unsent_newsletters == 0))) {

	if ($action=='edit') {
		echo "Note: Editing a mailing list will not reset the recipient list.";
	}


	if ($_REQUEST['letter_id'] != '') {
		$sql = "SELECT * from `newsletters` WHERE `letter_id`='".jb_escape_sql($_REQUEST['letter_id'])."' ";
		$result = JB_mysql_query($sql) or die($sql.mysql_error());
		$row = mysql_fetch_array($result, MYSQL_ASSOC);

		$to = $row['to'];
		$message = $row['message'];
		$subject = $row['subject'];

		
	} else {

		$subject = stripslashes($subject);
		$message = stripslashes($message);

	}

?>

<h2>Newsletter Editor</h2>
<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=save">
To:<br><select name="to" size="2">
   <option value="EM" <?php if ($to=="EM") { echo " selected "; } ?>>EMPLOYERS</option>
   <option value="CA" <?php if ($to=="CA") { echo " selected "; } ?>>CANDIDATES</option>
</select><br>
Subject:<br><input type="text" name="subject" size="60" value="<?php echo JB_escape_html($subject);?>"><br>
Message:<br>
<textarea name="message" rows="20" cols=80><?php echo JB_escape_html($message);?></textarea><br>
Note: You can use the following variables:<br>
%name% - Account holder's first name and last name<br>
%username% - login username of the account holder<br>
%email% - email address of the account holder<br>
<input type="hidden" name="letter_id" value="<?php echo jb_escape_html($_REQUEST['letter_id']); ?>">
<input type="submit" value="Save Newsletter" name="submit">
</form>

<?php 



} else {
	if (($action == 'new') && ($unsent_newsletters > 0)) {
	echo "<p>ERROR: Cannot Create a new letter. Only ONE unsent letter is allowed!</p>";
	}

}
$timeend = microtime();
$diff = number_format(((substr($timeend,0,9)) + (substr($timeend,-10)) - (substr($timestart,0,9)) - (substr($timestart,-10))),4);
//echo "<small>$diff s </small>";

JB_admin_footer();

?>