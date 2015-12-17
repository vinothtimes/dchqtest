<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

$dir = JB_basedirpath();
require ($dir."include/lib/mail/pop3.php");


/*

Mail Delivery System / Sorry your message to eoybralt26@yahoo.com cannot be delivered

Mail Delivery System / mailbox unavailable

Mail Delivery System / This is a permanent error

postmaster * / Delivery to the following recipients failed.

postmaster * / Unable to deliver message

Mail Delivery Subsystem / The following addresses had permanent fatal errors

MAILER-DAEMON / This is a permanent error

*/

function JB_mon_extract_emails($text) {



	if (preg_match_all("/[a-z0-9\._-]+@[a-z0-9\._-]+/i", $text, $matches)) {

		return $matches[0];
	}
	return false;
	

}

############################
# extract the email address that failed.
function JB_mon_extract_recipient($text) {

	// X-Failed-Recipients:
	if (preg_match('/X-Failed-Recipients:.+<(.+)>/', $text, $m)) {
		return $m[1];
	} 

	// X-Failed-Recipients:
	if (preg_match('/X-Failed-Recipients: (.+)/', $text, $m)) {
		return $m[1];
	} 

	// Final-Recipient: rfc822;<ultrasmooth101@yahoo dot com>

	if (preg_match('/Final-Recipient:.+<(.+)>/', $text, $m)) {
		return $m[1];
	} 

	///Final-Recipient: RFC822; comzirak@empal.com
	if (preg_match('/Final-Recipient:.+; (.+)/', $text, $m)) {
		return $m[1];
	}

	// Final-Recipient: rfc822;jaden05@paran.com

	if (preg_match('/Final-Recipient:.+;(.+)/', $text, $m)) {
		return $m[1];
	} 

	return false;


}

############################
# extract the email address that failed.
function JB_mon_extract_from_name($header_text) {

	// From: Mail Delivery System <Mailer-Daemon@server.jobboardhosting.com>
	if (preg_match('/From: (.+) <.+>/', $header_text, $m)) {
		return $m[1];
	} 


	return false;


}



##############################

function JB_match_mon_from_pattern($text) {

	$from_patterns = 'Mail Delivery System|postmaster|MAILER-DAEMON|Mail Delivery Subsystem';
	return preg_match ("/($from_patterns)/", $text);

}

##############################

function JB_match_mon_body_pattern($text) {

	$body_patterns = 'Sorry your message to .+ cannot be delivered|mailbox unavailable|This is a permanent error|Delivery to the following recipients failed|permanent fatal errors|This is a permanent error|Unable to deliver message';
	return preg_match ("/($body_patterns)/", $text);

}


##############################

function JB_mon_match_user_email($email) {
	// search resume and users table
	$email = addslashes($email);
	$sql = "select * from users where `Email`='".jb_escape_sql($email)."' or alert_email='".jb_escape_sql($email)."' ";
	$result = JB_mysql_query($sql) or die (mysql_error());
	if ($c=mysql_num_rows($result)) {
		return true;
	}
	return false;
	


}

##########################

function JB_mon_match_employer_email($email) {
	// search employers and posts_table

	// search resume and users table
	$email = addslashes($email);
	$sql = "select * from employers where `Email`='".jb_escape_sql($email)."' or alert_email='".jb_escape_sql($email)."' ";
	$result = JB_mysql_query($sql) or die (mysql_error());
	if ($c=mysql_num_rows($result)) {
		return true;

	}
	return false;

}

################################

function JB_mon_unsubscribe_employer($email) {

	$email = addslashes($email);
	$sql = "update users set Newsletter='0', Notification1='0', Notification2='0' Where Email='".jb_escape_sql($email)."' ";
	$result = JB_mysql_query($sql) or die (mysql_error());


}

################################

function JB_mon_unsubscribe_user($email) {
	$email = addslashes($email);
	$sql = "update users set Newsletter='0', Notification1='0', Notification2='0' Where Email='".jb_escape_sql($email)."' ";
	$result = JB_mysql_query($sql) or die (mysql_error());


}

#####################################

function JB_mon_append_log($email, $type) {
	
	$now = (gmdate("Y-m-d H:i:s"));
	$sql = "INSERT INTO `mail_monitor_log` ( `date` , `email` , `user_type` ) VALUES ('$now', '".jb_escape_sql($email)."', '".jb_escape_sql($type)."' );";
	JB_mysql_query($sql);


}
######################################

function JB_mon_list_log () {

	// first, delete from log more then 30 days old
	$now = (gmdate("Y-m-d H:i:s"));
	$sql = "DELETE FROM mail_monitor_log WHERE DATE_SUB('$now', INTERVAL 30 DAY) > `date` ";
	$result = JB_mysql_query($sql);

	$sql = "SELECT * from mail_monitor_log order by `date` DESC ";
	$result = JB_mysql_query($sql);
	if (mysql_num_rows($result)>0) {

		?>
		<h3>Bounced Email Log</h3>
		<?php echo mysql_num_rows($result)." bounced emails processed. "; ?>
		<table>
		<tr>
			<td>Seq.</td>
			<td>Date</td>
			<td>Email</td>
			<td>User Type</td>
		</tr>
		<?php
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			?>
			<tr>
				<td><?php echo $row['log_id']; ?></td>
				<td><?php echo $row['date']; ?></td>
				<td><?php echo $row['email']; ?></td>
				<td><?php echo $row['user_type']; ?></td>
			</tr>
			<?php	

		}
		?>
		</table>
		<?php
	}

	?>

	<?php


}


######################################

function JB_monitor_mail_box() {

	$pop3=new pop3_class;
	$pop3->hostname=MON_EMAIL_POP_SERVER;      /* POP 3 server host name              */
	$pop3->port=MON_POP3_PORT;     /* POP 3 server host port              */
	$user=MON_EMAIL_POP_USER;                /* Authentication user name            */
	$password=MON_EMAIL_POP_PASS;            /* Authentication password             */
	
	$pop3->realm="";                        /* Authentication realm or domain      */
	$pop3->workstation="";                  /* Workstation for NTLM authentication */
	$apop=0;                                /* Use APOP authentication             */
	$pop3->authentication_mechanism="USER"; /* SASL authentication mechanism       */
	$pop3->debug=0;                         /* Output debug information            */
	$pop3->html_debug=0;                    /* Debug information is in HTML        */
	if ($_REQUEST['scan']!='') {
		echo "opening Pop Connection";
	}
	if(($error=$pop3->Open())=="")
	{
		
		if(($error=$pop3->Login($user,$password,$apop))=="")
		{
			
			if(($error=$pop3->Statistics($messages,$size))=="")
			{
				if ($_REQUEST['scan']!='') {
					echo "<PRE>There are $messages messages in the mail box with a total of $size bytes.</PRE>\n";
					echo "<h3>Pop3 connection was successful.</h3>";
				}
				
				
				$result=$pop3->ListMessages("",0);
				if(GetType($result)=="array")
				{
					
					$result=$pop3->ListMessages('',1);// list all, unique
					
					if(is_array($result))
					{
						for(Reset($result),$message=0;$message<count($result);Next($result),$message++) {
							
						
							if(($error=$pop3->RetrieveMessage(key($result),$headers,$body,-1))=="")
							{
								
								$head_txt='';
								$body_txt='';
								
								for($line=0;$line<count($headers);$line++) {
									$head_txt .= $headers[$line]."\n"; 
								}
								
								
								preg_match ('#Delivery-date: (.+)?\n#i', $head_txt, $m);
								$ts = strtotime($m[1]);
								if (intval(MON_DEL_DAYS)>0) {
									if ((time()-$ts) > (60*60*24*MON_DEL_DAYS)) { // more than 30 days

										if(($error=$pop3->DeleteMessage(key($result)))=="") {
											
											continue;
										}
									}

								}
								
								
								for($line=0;$line<count($body);$line++) {
									$body_txt .= $body[$line]."\n";
								}
								
								$from_name = JB_mon_extract_from_name($head_txt);
								if ($failed_rec = JB_mon_extract_recipient($head_txt.$body_txt)) {
									if (JB_match_mon_from_pattern($from_name) && JB_match_mon_body_pattern($body_txt)) {
										
										if (JB_mon_match_user_email($failed_rec)) {
											$user_id = JB_mon_unsubscribe_user($failed_rec);
											if(($error=$pop3->DeleteMessage(key($result)))=="") {
												
												
												JB_mon_append_log($failed_rec, 'C');
											}
										}
										if (JB_mon_match_employer_email($failed_rec)) {
											$user_id = JB_mon_unsubscribe_employer($failed_rec);
											if(($error=$pop3->DeleteMessage(key($result)))=="") {
												//echo "dlete ".key($result)."<br>";
							
												JB_mon_append_log($failed_rec, 'E');
											}
										}
									}
								}
								
								
							}
						
							
						}
						if($error=="" && ($error=$pop3->Close())=="") {
								//echo "<PRE>Disconnected from the POP3 server &quot;".$pop3->hostname."&quot;.</PRE>\n";
						}
					}
					else
						$error=$result;
				}
				else
					$error=$result;

					
			}
			
		}
	}
	

	// proces mail_monitor_log
	$now = (gmdate("Y-m-d H:i:s"));
	if (intval(MON_LOG_DAYS)>0) {
		$sql = "DELETE FROM mail_monitor_log WHERE DATE_SUB('$now', INTERVAL ".MON_LOG_DAYS." DAY) >= `date` ";
		JB_mysql_query($sql);

	}
	
	

}


##########################

// load the saved constants

function JB_load_monitor_constants() {

	$sql = "SELECT * from jb_config WHERE `key`='MON_ENABLED' OR `key`='MON_POP3_PORT' OR `key`='MON_EMAIL_POP_USER' OR `key`='MON_EMAIL_POP_SERVER' or `key`='MON_EMAIL_POP_PASS' OR `key`='MON_LOG_DAYS' OR `key`='MON_DEL_DAYS' "; 
	$result = JB_mysql_query($sql) or die(mysql_error($result));

	while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

		define ($row['key'], $row['val']);
	}

}


########################



?>