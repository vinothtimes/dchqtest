<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
function JB_add_mail_attachments(&$email_message, &$mail_row) {

	if ($mail_row['att1_name'] != '') {

	  $attachment1=array(
		 "FileName"=> $mail_row['att1_name'],
		 "Name"=> JB_html_ent_to_utf8(basename($mail_row['att1_name'])),
		 "Content-Type"=>"automatic/name",
		 "Disposition"=>"attachment"
	  );
	  $email_message->AddFilePart($attachment1);
	  
	}
	if ($mail_row['att2_name'] != '') {

	  $attachment2=array(
		 "FileName"=> $mail_row['att2_name'],
		 "Name"=> JB_html_ent_to_utf8(basename($mail_row['att2_name'])),
		 "Content-Type"=>"automatic/name",
		 "Disposition"=>"attachment"
	  );
	  $email_message->AddFilePart($attachment2);
	  
	}

	if ($mail_row['att3_name'] != '') {
	 
	  $attachment3=array(
		 "FileName"=> $mail_row['att3_name'],
		 "Name"=> JB_html_ent_to_utf8(basename($mail_row['att3_name'])),
		 "Content-Type"=>"automatic/name",
		 "Disposition"=>"attachment"
	  );
	  $email_message->AddFilePart($attachment3);
	  
	}

	return $email_message;

}

##########################################################################

function JB_move_uploaded_attachment ($mail_id, $att_file, $from_name) {

	$mail_id = (int) $mail_id;

	$att_tmp = $_FILES[$att_file]['tmp_name'];
	$temp= explode('.',$_FILES[$att_file]['name']);
	$ext = array_pop($temp);

	if (!file_exists(JB_FILE_PATH."temp/")) {
		mkdir(JB_FILE_PATH."temp/", JB_NEW_DIR_CHMOD);
		//chmod(JB_FILE_PATH."temp/", JB_NEW_DIR_CHMOD);  
	}

	if (strpos(strtoupper(PHP_OS), 'WIN')!==false) { 
		// sometimes the dir can have double slashes on Win, remove 'em
		$att_tmp = str_replace ('\\\\', '\\', $att_tmp);
	}

	// strip out non-alphanumeric characters from from_name
	$from_name = preg_replace ('/[^a-z^0-9^&^;^.^#]+/i', "", $from_name);
	$from_name = JB_clean_str($from_name);
	$ext = preg_replace ('/[^a-z^0-9]+/i', "", $ext);

	$new_name = JB_FILE_PATH."temp/$from_name".$mail_id."$att_file.".$ext;
	

	if ( move_uploaded_file ($att_tmp, $new_name)) {
		chmod($new_name, JB_NEW_FILE_CHMOD);

	}  else {
		//echo htmlentities('Could not move the image form the temp directory.  (FROM: '.$_FILES[$field_id]['tmp_name'].' ->> TO: '.$uploadfile.') ').PHP_OS."<br>\n";

		switch ($_FILES[$field_id]["error"]) {

	
			case UPLOAD_ERR_OK:
			   break;
			case UPLOAD_ERR_INI_SIZE:
			   jb_custom_error_handler('upload', "The uploaded file exceeds the upload_max_filesize directive (".ini_get("upload_max_filesize").") in php.ini.", __FILE__, __LINE__, $vars);
			   break;
			case UPLOAD_ERR_FORM_SIZE:
			   jb_custom_error_handler('upload', "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.", __FILE__, 0, $vars);
			   break;
			case UPLOAD_ERR_PARTIAL:
			   jb_custom_error_handler('upload', "The uploaded file was only partially uploaded.", __FILE__, 0, $vars);
			   break;
			case UPLOAD_ERR_NO_FILE:
			   jb_custom_error_handler('upload', "No file was uploaded.", __FILE__, __LINE__, $vars);
			   break;
			case UPLOAD_ERR_NO_TMP_DIR:
			   jb_custom_error_handler('upload', "Missing a temporary folder.", __FILE__, __LINE__, $vars);
			   break;
			case UPLOAD_ERR_CANT_WRITE:
			   jb_custom_error_handler('upload', "Failed to write file to disk", __FILE__, __LINE__, $vars);
			   break;
			default:
			   jb_custom_error_handler('upload', "Unknown File Error", __FILE__, __LINE__, $vars);
		}
	}
	

	return $new_name;


}

function JB_q_mail_error($s) {

	mail(JB_SITE_CONTACT_EMAIL, JB_SITE_NAME.'email q error', $s."\n");


}

#################################################
# queue a 'carbon copy' of an email 
function JB_queue_mail_cc($mail_id, $to_name, $to_address) {

	$sql = "select * from mail_queue where mail_id='".jb_escape_sql($mail_id)."'";
	$result = JB_mysql_query($sql) or die(mysql_error());
	$row=mysql_fetch_array($result, MYSQL_ASSOC);


	$attachments=$row['attachments'];
	
	$now = (gmdate("Y-m-d H:i:s"));

	$sql = "INSERT INTO mail_queue (mail_date, to_address, to_name, from_address, from_name, subject, message, html_message, attachments, status, error_msg, retry_count, template_id, date_stamp, att1_name, att2_name, att3_name) VALUES('$now', '".jb_escape_sql(addslashes($to_address))."', '".jb_escape_sql(addslashes($to_name))."', '".jb_escape_sql(addslashes($row['from_address']))."', '".jb_escape_sql(addslashes($row['from_name']))."', '".jb_escape_sql(addslashes($row['subject']))."', '".jb_escape_sql(addslashes($row['message']))."', '".jb_escape_sql(addslashes($row['html_message']))."', '".jb_escape_sql(addslashes($row['attachments']))."', 'queued', '', 0, '".jb_escape_sql(addslashes($row['template_id']))."', '$now', '".jb_escape_sql(addslashes($row['att1_name']))."', '".jb_escape_sql(addslashes($row['att2_name']))."', '".jb_escape_sql(addslashes($row['att3_name']))."')";

	JB_mysql_query ($sql) or JB_q_mail_error (mysql_error().$sql);

	$mail_id = JB_mysql_insert_id();



	return $mail_id;

}

#################################################


function JB_queue_mail($to_address, $to_name, $from_address, $from_name, $subject, $message, $html_message, $template_id, $att=false) {

	$to_address=trim($to_address);
	$to_name=trim($to_name);
	$from_address=trim($from_address);
	$from_name=trim($from_name);
	$subject=trim($subject);
	$message=trim($message);
	$html_message=trim($html_message);

	if (EMAIL_URL_SHORTEN=='YES') {
		$message = JB_change_urls_to_short($message);
		$html_message = JB_change_urls_to_short($html_message);
	}

	// legacy addslashes() - this will be removed in the future!

	$to_address=addslashes($to_address);
	$to_name=addslashes($to_name);
	$from_address=addslashes($from_address);
	$from_name=addslashes($from_name);
	$subject=addslashes($subject);
	$message=addslashes($message);
	$html_message=addslashes($html_message);

	$now = (gmdate("Y-m-d H:i:s"));

	$attachments = 'N';
	
	$user_type = ($_SESSION['JB_Domain']) ? "'".jb_escape_sql($_SESSION['JB_Domain'])."'" : 'NULL';
	$user_id = ($_SESSION['JB_ID']) ? "'".jb_escape_sql($_SESSION['JB_ID'])."'" : 'NULL';

	$sql = "INSERT INTO mail_queue (mail_date, to_address, to_name, from_address, from_name, subject, message, html_message, attachments, status, error_msg, retry_count, template_id, date_stamp, user_id, user_type) VALUES('".$now."', '".jb_escape_sql($to_address)."', '".jb_escape_sql($to_name)."', '".jb_escape_sql($from_address)."', '".jb_escape_sql($from_name)."', '".jb_escape_sql($subject)."', '".jb_escape_sql($message)."', '".jb_escape_sql($html_message)."', '".jb_escape_sql($attachments)."', 'queued', '', 0, '".jb_escape_sql($template_id)."', '".$now."', ".$user_id.", ".$user_type.")"; // 2005 copyr1ght jam1t softwar3 

	JB_mysql_query ($sql) or JB_q_mail_error (mysql_error().$sql);

	$mail_id = JB_mysql_insert_id();

	if ($att) { 

		if ($_FILES['att1']['name']!='') {

			$filename = JB_move_uploaded_attachment ($mail_id, 'att1', $from_name);
			$sql = "UPDATE mail_queue SET attachments='Y', att1_name='".jb_escape_sql($filename)."' WHERE mail_id='".jb_escape_sql($mail_id)."' ";
			JB_mysql_query ($sql) or JB_q_mail_error (mysql_error().$sql);

		}
		
		if ($_FILES['att2']['name']!='') {
			$filename = JB_move_uploaded_attachment ($mail_id, 'att2', $from_name);
			$sql = "UPDATE mail_queue SET attachments='Y', att2_name='".jb_escape_sql($filename)."' WHERE mail_id='".jb_escape_sql($mail_id)."' ";
			JB_mysql_query ($sql) or JB_q_mail_error (mysql_error().$sql);
		}
		
		if ($_FILES['att3']['name']!='') {
			$filename = JB_move_uploaded_attachment ($mail_id, 'att3', $from_name);
			$sql = "UPDATE mail_queue SET attachments='Y', att3_name='".jb_escape_sql($filename)."' WHERE mail_id='".jb_escape_sql($mail_id)."' ";
			JB_mysql_query ($sql) or JB_q_mail_error (mysql_error().$sql);
		}

	}
	return $mail_id;



}

############################

function JB_do_pop_before_smtp() {

	return;

	$now = (gmdate("Y-m-d H:i:s"));
	$unix_time = time();

	// get the time of pop
	$sql = "SELECT * FROM `jb_variables` where `key` = 'LAST_MAIL_POP' ";
	$result = @JB_mysql_query($sql) or $DB_ERROR = mysql_error();
	$t_row = @mysql_fetch_array($result, MYSQL_ASSOC);

	$twenty_min = 60 * 20;

	if ($unix_time > $t_row['val']+$twenty_min) { // do the POP if 20 minutes elapsed.

		
		$dir = JB_basedirpath();

		require ($dir."mail/pop3.php");

		$pop3=new pop3_class;
		$pop3->hostname=JB_EMAIL_POP_SERVER;      /* POP 3 server host name              */
		$pop3->port=JB_POP3_PORT;     /* POP 3 server host port              */
		$user=JB_EMAIL_SMTP_USER;                /* Authentication user name            */
		$password=JB_EMAIL_SMTP_PASS;           /* Authentication password             */
		$pop3->realm="";                        /* Authentication realm or domain      */
		$pop3->workstation="";                  /* Workstation for NTLM authentication */
		$apop=0;                                /* Use APOP authentication             */
		$pop3->authentication_mechanism="USER"; /* SASL authentication mechanism       */
		$pop3->debug=0;                         /* Output debug information            */
		$pop3->html_debug=0;                    /* Debug information is in HTML        */

		if(($error=$pop3->Open())=="") {
			
			if(($error=$pop3->Login($user,$password,$apop))=="") {
				
				if(($error=$pop3->Statistics($messages,$size))=="") {

				}
			}
		}

		$sql = "REPLACE INTO jb_variables (`key`, `val`) VALUES ('LAST_MAIL_POP', '$unix_time')  ";
		$result = @JB_mysql_query($sql) or $DB_ERROR = mysql_error();

	} 





}

############################

function JB_process_mail_queue($send_count=1) {

	$now = (gmdate("Y-m-d H:i:s"));
	$unix_time = time();

	global $jb_mysql_link;

	// get the time of last run
	$sql = "SELECT * FROM `jb_variables` where `key` = 'LAST_MAIL_QUEUE_RUN' ";
	$result = @JB_mysql_query($sql) or $DB_ERROR = mysql_error();
	$t_row = @mysql_fetch_array($result, MYSQL_ASSOC);

	if ($DB_ERROR!='') return $DB_ERROR;

	// Poor man's lock (making sure that this function is a Singleton)
	$sql = "UPDATE `jb_variables` SET `val`='YES' WHERE `key`='MAIL_QUEUE_RUNNING' AND `val`='NO' ";
	$result = JB_mysql_query($sql) or $DB_ERROR = mysql_error();
	if (JB_mysql_affected_rows()==0) {

		// make sure it cannot be locked for more than 30 secs 
		// This is in case the proccess fails inside the lock
		// and does not release it.

		if ($unix_time > $t_row['val']+30) {
			// release the lock
			
			$sql = "UPDATE `jb_variables` SET `val`='NO' WHERE `key`='MAIL_QUEUE_RUNNING' ";
			$result = @JB_mysql_query($sql) or $DB_ERROR = mysql_error();

			// update timestamp
			$sql = "REPLACE INTO jb_variables (`key`, `val`) VALUES ('LAST_MAIL_QUEUE_RUN', '$unix_time')  ";
			$result = @JB_mysql_query($sql) or $DB_ERROR = mysql_error();
		}


		return; // this function is already executing in another process.
	}

	///////////////////////////////////////////////////////////
	// Start Critical Section - is only executed in one process at at time
	///////////////////////////////////////////////////////////

	if ($unix_time > $t_row['val']+5) { // did 5 seconds elapse since last run?


		if (JB_EMAIL_POP_BEFORE_SMTP=='YES') {
			JB_do_pop_before_smtp();
		}


		if (func_num_args()>1) {
			$mail_id = func_get_arg(1);
			$and_mail_id = " AND mail_id=".jb_escape_sql($mail_id)." ";
		}

		

		$JB_EMAILS_MAX_RETRY = (int) JB_EMAILS_MAX_RETRY;
		if ($JB_EMAILS_MAX_RETRY=='') {
			$JB_EMAILS_MAX_RETRY = 5;
		}

		$JB_EMAILS_ERROR_WAIT = (int) JB_EMAILS_ERROR_WAIT;
		if ($JB_EMAILS_ERROR_WAIT=='') {
			$JB_EMAILS_ERROR_WAIT = 10;
		}

		$JB_EMAILS_PER_BATCH = (int) JB_EMAILS_PER_BATCH;
		if (!$JB_EMAILS_PER_BATCH) {
			$JB_EMAILS_PER_BATCH = 5;
		}

		// The following query is using index composite1
		// ALTER TABLE mail_queue ADD INDEX `composite1` (`status`, `retry_count`)
		// We need to double the $JB_EMAILS_PER_BATCH for the LIMIT
		// This is because not all mails fetched by the query are sent
		// since emails with status='queued' and 0 > retry_count <= x need to
		// wait for $JB_EMAILS_ERROR_WAIT seconds
		//


		if ($JB_EMAILS_MAX_RETRY > 0) {
			$retry_count = " AND retry_count <= ".jb_escape_sql($JB_EMAILS_MAX_RETRY);
		}
		$sql = "SELECT * from mail_queue where (status='queued' OR status='error')  $retry_count  $and_mail_id  LIMIT ".($JB_EMAILS_PER_BATCH*2)." ";


		$result = JB_mysql_query ($sql) or JB_q_mail_error (mysql_error().$sql);
		while (($row = mysql_fetch_array($result, MYSQL_ASSOC))&&($send_count > 0)) {
			$time_stamp = strtotime($row['date_stamp']." GMT");
			$now = strtotime(gmdate("Y-m-d H:i:s"));
			$wait = $JB_EMAILS_ERROR_WAIT * 60;
		
			if (((($now - $wait) > $time_stamp) && ($row['status']=='error')) || ($row['status']=='queued')) {
				$send_count--;

				$error = JB_send_email($row);
			}
		}


		// delete old stuff

		if ((JB_EMAILS_DAYS_KEEP=='JB_EMAILS_DAYS_KEEP')) { define (JB_EMAILS_DAYS_KEEP, '0'); }

		if (JB_EMAILS_DAYS_KEEP>0) {

			$now = (gmdate("Y-m-d H:i:s"));

			$sql = "SELECT mail_id, att1_name, att2_name, att3_name from mail_queue where status='sent' AND DATE_SUB('$now',INTERVAL ".JB_EMAILS_DAYS_KEEP." DAY) >= date_stamp  ";

			$result = JB_mysql_query ($sql) or die(mysql_error());

			while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

				if (($row['att1_name']!='') && (file_exists($row['att1_name']))) {
					unlink($row['att1_name']);
				}

				if (($row['att2_name']!='') && (file_exists($row['att2_name']))) {
					unlink($row['att2_name']);
				}

				if (($row['att3_name']!='') && (file_exists($row['att3_name']))) {
					unlink($row['att3_name']);
				}

				$sql = "DELETE FROM mail_queue where mail_id='".jb_escape_sql($row['mail_id'])."' ";
				JB_mysql_query($sql) or die(mysql_error());



			}

		}

		// update timestamp
		$unix_time = time();
		$sql = "REPLACE INTO jb_variables (`key`, `val`) VALUES ('LAST_MAIL_QUEUE_RUN', '$unix_time')  ";
		$result = @JB_mysql_query($sql) or $DB_ERROR = mysql_error();

	}

	// release the poor man's lock
	$sql = "UPDATE `jb_variables` SET `val`='NO' WHERE `key`='MAIL_QUEUE_RUNNING' ";
	@JB_mysql_query($sql) or die(mysql_error());


}


############################

// $mail_row ->full email row from the database
function JB_send_email($mail_row) {

	$to_name = JB_html_ent_to_utf8($mail_row['to_name']);
	$to_address = $mail_row['to_address'];
	$from_name = JB_html_ent_to_utf8($mail_row['from_name']);
	$from_address = $mail_row['from_address'];
	$subject = JB_html_ent_to_utf8($mail_row['subject']);
	$message = JB_html_ent_to_utf8($mail_row['message']);
	$html_message = JB_html_ent_to_utf8($mail_row['html_message']);


	if (JB_USE_MAIL_FUNCTION == 'YES') {
		
		$email_message= new email_message_class;

		if (JB_EMAIL_DEBUG_SWITCH=='YES') {
			echo 'Email Debug: Using the mail() function...<br>';
		}
	} else { // use SMTP

		
		$dir = JB_basedirpath();

		if (!class_exists("sasl_client_class")) {
			require_once($dir."include/lib/mail/sasl/sasl.php");
		}

		$email_message= new smtp_message_class;

		$email_message->localhost=JB_EMAIL_HOSTNAME;
		$email_message->smtp_host=JB_EMAIL_SMTP_SERVER;
		$email_message->smtp_direct_delivery=0;
		$email_message->smtp_exclude_address="";
		$email_message->smtp_user=JB_EMAIL_SMTP_USER;
		$email_message->smtp_realm="";
		$email_message->smtp_password=JB_EMAIL_SMTP_PASS;

		if (defined('JB_EMAIL_SMTP_PORT')) {
			if (!is_numeric(JB_EMAIL_SMTP_PORT)) {
				$email_message->smtp_port = 25;
			} else {
				$email_message->smtp_port = JB_EMAIL_SMTP_PORT;
			}
		}
		$email_message->authentication_mechanism = 'USER'; // SASL authentication

		if (JB_EMAIL_SMTP_SSL=='YES') {
			$email_message->smtp_ssl=1;
		} else {
			$email_message->smtp_ssl=0;
		}

		if (JB_EMAIL_POP_BEFORE_SMTP == 'YES') {
			$email_message->smtp_pop3_auth_host=JB_EMAIL_SMTP_AUTH_HOST;
			
		} else {

			$email_message->smtp_pop3_auth_host="";
		}

		if (JB_EMAIL_DEBUG_SWITCH=='YES') {
			$email_message->smtp_debug=1;
		} else {
			 $email_message->smtp_debug=0;
		}
	   
		$email_message->smtp_html_debug=0;

		if (JB_EMAIL_DEBUG_SWITCH=='YES') {
			echo 'Email Debug: using SMTP server...<br>';
		}

	}

	

	$reply_address=$mail_row['from_address'];
	
	$error_delivery_name=JB_SITE_NAME;
	$error_delivery_address=JB_SITE_CONTACT_EMAIL;

	JBPLUG_do_callback('set_error_delivery_name', $error_delivery_name); // added in 3.6
	JBPLUG_do_callback('set_error_delivery_address', $error_delivery_address); // added in 3.6
	
	
	$email_message->default_charset='UTF-8';
	$email_message->SetEncodedEmailHeader("To",$to_address,$to_name);
	$email_message->SetEncodedEmailHeader("From",$from_address,$from_name);
	$email_message->SetEncodedEmailHeader("Reply-To",$reply_address,$reply_name);
/*
	Set the Return-Path header to define the envelope sender address to which bounced messages are delivered.
	If you are using Windows, you need to use the smtp_message_class to set the return-path address.
*/
	
	
	// Cannot set in safe-mode or under Windows...

	if (function_exists("ini_get")
			&& !ini_get("safe_mode")
			&& (strpos(strtoupper (PHP_OS), 'WIN')===false)) {
		$email_message->SetHeader("Return-Path", $error_delivery_address);
	}
	if (strpos(strtoupper (PHP_OS), 'WIN')!==false) { // windows
		ini_set('sendmail_from', JB_SITE_CONTACT_EMAIL);

	}
	//}
	
	
	if (($mail_row['template_id']==5) || ($mail_row['template_id']==6) || ($mail_row['template_id']==7) || ($mail_row['template_id']==8) || ($mail_row['template_id']==30)) { // job alerts, resume alerts, newsletter are bulk mails
		$email_message->SetHeader("Precedence", 'bulk');
	}


	$email_message->SetEncodedEmailHeader("Errors-To",$error_delivery_address,$error_delivery_name);
	$email_message->SetEncodedHeader("Subject",$subject);
	

	if ($html_message=='') { // ONLY TEXT
		
		$email_message->AddQuotedPrintableTextPart($email_message->WrapText($message));
	}else {
		
		$email_message->CreateQuotedPrintableHTMLPart($html_message,"",$html_part);
		//$text_message="This is an HTML message. Please use an HTML capable mail program to read this message.";
		$email_message->CreateQuotedPrintableTextPart($email_message->WrapText($message),"",$text_part);

		$alternative_parts=array(
			$text_part,
			$html_part
		);
		$email_message->AddAlternativeMultipart($alternative_parts);

	}

	if ($mail_row['attachments']=='Y') {
		JB_add_mail_attachments($email_message, $mail_row);
	}

	JBPLUG_do_callback('set_mail_message', $email_message); // plugins can do additional operations on the $email_message, added in 3.6

	$error=$email_message->Send();


	if(strcmp($error,"")) {
		
		$now = gmdate("Y-m-d H:i:s");

		$sql = "UPDATE mail_queue SET status='error', retry_count=retry_count+1,  error_msg='".jb_escape_sql(addslashes($error))."', `date_stamp`='$now' WHERE mail_id=".jb_escape_sql($mail_row['mail_id']);
		
		JB_mysql_query($sql) or JB_q_mail_error(mysql_error().$sql);



	} else {

		// note: on some servers (ie GoDaddy, connection to server can be
		// lost, so re-connect by pinging

		jb_mysql_ping();
        
        
		$now = gmdate("Y-m-d H:i:s");

		$sql = "UPDATE mail_queue SET status='sent', `date_stamp`='$now' WHERE mail_id='".jb_escape_sql($mail_row['mail_id'])."'";
		JB_mysql_query($sql, $jb_mysql_link) or JB_q_mail_error(mysql_error().$sql);

	}

	
}

//////////////////////////////////////////////////////

/*

Short URL for mail

*/

function JB_change_urls_to_short($text) {

	preg_match_all('#[a-z]+://[^\s"\'\\\^<]+#i', $text, $m);
	$patterns = array();
	$replacements = array();
	$sizes = array();

	$list = $m[0];

	// shoren the URLs
	
	foreach ($list as $url) {
		$expires = false;
		if (strpos($url, 'admin')!==false) {
			$expires=true;
		}

		if (strlen($url) >= 70) { // only shorten URLs that are 70 chars or grater in length
			$patterns[] = $url;
			$sizes[] = strlen($url);
			$replacements[] = JB_short_URL($url, $expires);
		}
	}

	// sort form longest url to shortest, this makes the str_replace greedy
	// and ensures that all urls are replaced correctly
	array_multisort($sizes, SORT_DESC, $patterns, $replacements);

	// replace all the URLs with the shortened URLs
	return str_replace($patterns, $replacements, $text);


}


// look up the $long_url
// if does not exist, create a new short url
// else return the existing short url
function JB_short_URL($long_url, $expires=false) {

	//$sql = "SELECT form jb_short_urls where hash='$new_hash' ";

	if ($expires==true) {
		$expires = 'Y';
	} else {
		$expires = 'N';
	}
	$long_url = trim ($long_url);
	$sql = "SELECT url, hash FROM short_urls WHERE url='".jb_escape_sql($long_url)."'";
	
	$result = JB_mysql_query ($sql) or die (mysql_error());
	//$row = mysql_fetch_array($result, MYSQL_ASSOC);
	if (mysql_num_rows($result)==0) {

		$hash = JB_hash_short_URL($long_url);

		$sql = "INSERT INTO short_urls (url, date, hash, expires, hits) VALUES ('".jb_escape_sql(addslashes($long_url))."', NOW(), '".jb_escape_sql($hash)."', '".jb_escape_sql($expires)."', '0') ";
		JB_mysql_query ($sql) or die (mysql_error());


	} else {
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		$hash = $row['hash'];

	}

	
	

	return JB_BASE_HTTP_PATH."su.php?h=$hash";



}

// look up the database to get the long url form the short url
function JB_get_long_URL($hash) {

	$sql = "SELECT url FROM short_urls WHERE hash='".jb_escape_sql($hash)."'";
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return $row['url'];


}

// redirect the urser to the long URL
function JB_redirect_short_url() {
	$long_url = JB_get_long_URL($_REQUEST['h']);
	if ($long_url=='') {
		$long_url = JB_BASE_HTTP_PATH;
	}
	header("Location: $long_url");

}
function JB_hash_short_URL($str) {
	return substr(md5(time().$str), 0, 8);
}

function JB_expire_short_URLs() {

	// expire urls which were not accessed within the last 90 days
	$now = (gmdate("Y-m-d H:i:s"));
	$sql = "DELETE FROM short_urls WHERE DATE_SUB('$now', INTERVAL 90 DAY) > `date` ";
	jb_mysql_query($sql);

}


####################################################################


# Email templates language translations,..,

function JB_format_email_translation_table () {
	global $AVAILABLE_LANGS;

	$sql = "select * from email_templates ";
	$f_result = JB_mysql_query ($sql) or die (mysql_error());
	
	while ($f_row = mysql_fetch_array($f_result, MYSQL_ASSOC)) { 

		foreach  ($AVAILABLE_LANGS as $key => $val) {

			#$sql = "SELECT t2.field_id, t2.field_label AS FLABEL, lang FROM form_field_translations as t1, form_fields as t2 WHERE t2.field_id=t1.field_id AND t2.field_id=".$f_row['field_id']." AND lang='$key' ";

			$sql = "SELECT * FROM email_template_translations as t1, email_templates as t2 WHERE t2.EmailID=t1.EmailID AND  t2.EmailID=".jb_escape_sql($f_row['EmailID'])." AND lang='".jb_escape_sql($key)."' ";
			//echo $sql."<br>";
			$result = JB_mysql_query($sql) or die($sql.mysql_error());
			//$row = mysql_fetch_row($result);
			if (mysql_num_rows($result)==0) {
				//$cat_row = JB_get_category($cat);
				$sql = "REPLACE INTO `email_template_translations` (`EmailID`, `lang`, `EmailText`, `EmailFromAddress`, `EmailFromName`, EmailSubject, sub_template) VALUES ('".jb_escape_sql($f_row['EmailID'])."', '".jb_escape_sql($key)."', '".jb_escape_sql(addslashes($f_row['EmailText']))."', '".jb_escape_sql(addslashes($f_row['EmailFromAddress']))."', '".jb_escape_sql(addslashes($f_row['EmailFromName']))."', '".jb_escape_sql(addslashes($f_row['EmailSubject']))."', '".jb_escape_sql(addslashes($f_row['sub_template']))."')";

				
				JB_mysql_query($sql) or die (mysql_error());

			}
			
		

		}

	}

}


?>