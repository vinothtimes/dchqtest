<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
define ('NO_HOUSE_KEEPING', true);


if (function_exists('JB_basedirpath')) { // config.php was required
	$dir = JB_basedirpath();
} else {
	$dir = '../';
}

if (!defined('JB_SITE_NAME')) {
	require($dir.'/config.php');
}

require_once($dir.'/include/resumes.inc.php');
 
ini_set('max_execution_time', 100200);
$DO_SEND = '';
if (defined('JB_RESUME_ALERTS_DO_SEND')) { // cron
	$DO_SEND = "YES";
} else {
	// from Admin
	require (dirname(__FILE__).'/admin_common.php'); // require Admin login

	if (JB_RESUME_ALERTS_ENABLED!='YES') {
		echo 'Resume Alerts feature is not enabled in Admin->Main Config';
		return;
	}
	?>
<h3>Automation Instructions:</h3><br>
This feature is set to run automatically every hour. Please <a href='cron.php'>see here</a> for more details.<br>

<br>Run Manually from Web:<input type="button" value="Queue Emails" onclick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?send_action=send&amp;from_admin=1' " >
<p>
	<?php
	if (!isset($_REQUEST['send_action'])) {
		return;
	} else {
		$DO_SEND = "YES";
	}

}

$VERBOSE = JB_EMAIL_DEBUG_SWITCH;

 
if (JB_RESUME_ALERTS_ACTIVE_DAYS=='JB_RESUME_ALERTS_ACTIVE_DAYS') {
	$JB_RESUME_ALERTS_ACTIVE_DAYS=1;
} else {
	$JB_RESUME_ALERTS_ACTIVE_DAYS=JB_RESUME_ALERTS_ACTIVE_DAYS;
}

$now = (gmdate("Y-m-d H:i:s"));

// if subscriptions are active, send alerts to subscribed employers only.

if (JB_SUBSCRIPTION_FEE_ENABLED=='YES') {
	if (JB_RESUME_ALERTS_SUB_IGNORE!='YES') {
		$subscr_sql = " AND subscription_can_view_resume='Y' ";
	}
} else {
	$subscr_sql = '';
}

$sql = "SELECT * FROM `employers` WHERE `Notification1`='1' AND Validated='1' AND DATE_SUB('$now', INTERVAL '".jb_escape_sql(JB_RESUME_ALERTS_DAYS)."' DAY) > alert_last_run  AND last_request_time > DATE_SUB('$now', INTERVAL '".jb_escape_sql($JB_RESUME_ALERTS_ACTIVE_DAYS)."' DAY) $subscr_sql ";

//$sql = "SELECT * FROM `employers` limit 1"; echo $sql; $VERBOSE = 'YES';// for testing

$result = JB_mysql_query($sql) or die(mysql_error());
$num_rows = mysql_num_rows($result);
if ($VERBOSE=='YES') {
	echo "Email Debug:$num_rows Employers to email<br>\n";
}
$count = 0;
while ($user_row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$to_name=jb_get_formatted_name($user_row['FirstName'], $user_row['LastName']);
	if ($user_row['alert_email']=='') {
		$to_address=trim($user_row['Email']);
	} else {
		$to_address=trim($user_row['alert_email']);
	}


	if ($VERBOSE=='YES') {
		echo "Email Debug: Processing ($to_name $to_address)<br>";
	}

	if (!JB_validate_mail($to_address)) {
		if ($VERBOSE == 'YES') {
			echo "Email Debug: Invalid email address for: |$to_name| [$to_address] (".$user_row['ID'].") <br>\n";
			// to do.. unsubscribe this user
		}
	} else {

		$where_sql = '';
		$html_keywords_line = '';
		$text_keywords_line = '';
		
		if ($user_row['alert_keywords'] == 'Y') { // Alert filter is enabled!
			$_Q_STRING = array();
			if ($user_row['alert_query']!='') {
				$_Q_STRING = unserialize($user_row['alert_query']);

			}
			if (is_array($_Q_STRING)) {
				foreach ($_Q_STRING as $key => $val) {
					$_SEARCH_INPUT[$key]=$val;
				}
				$_SEARCH_INPUT['action']='search';

				$where_sql = JB_generate_search_sql(2, $_SEARCH_INPUT);
			}

		}

		if (JB_JOB_ALERTS_ITEMS != 'JB_JOB_ALERTS_ITEMS') {
			$JB_JOB_ALERTS_ITEMS = JB_JOB_ALERTS_ITEMS;
		} else {
			$JB_JOB_ALERTS_ITEMS = 10;
		}


		$now = (gmdate("Y-m-d H:i:s"));

		// $sql = "SELECT *, DATE_FORMAT(`resume_date`, '%d-%b-%Y') AS formatted_date FROM `resumes_table` where `resume_date` > '$user_row[alert_last_run]' AND DATE_SUB('$now', INTERVAL ".JB_POSTS_DISPLAY_DAYS." DAY) <= `resume_date`  $where_sql ORDER BY `resume_date` DESC LIMIT $JB_JOB_ALERTS_ITEMS";

		if (JB_RESUME_ALERTS_ITEMS != 'JB_RESUME_ALERTS_ITEMS') {
			$JB_RESUME_ALERTS_ITEMS = JB_RESUME_ALERTS_ITEMS;

		} else {
			$JB_RESUME_ALERTS_ITEMS = 10;
		}

		$sql = "Select *, DATE_FORMAT(`resume_date`, '%d-%b-%Y') AS formatted_app_date, t1.user_id AS user_id FROM `resumes_table` AS t1 LEFT JOIN `skill_matrix_data` AS t2 ON t1.resume_id=t2.object_id WHERE `status`='ACT' AND `approved`='Y' AND `resume_date` > '".$user_row['alert_last_run']."' AND DATE_SUB('$now', INTERVAL ".JB_POSTS_DISPLAY_DAYS." DAY) <= `resume_date`  $where_sql group by resume_id ORDER BY `resume_date` DESC LIMIT $JB_RESUME_ALERTS_ITEMS  ";

		$result2 = JB_mysql_query($sql) or die(mysql_error().$sql);

		$html_msg_body = '';
		$text_msg_body = '';

		################################################################################
		# Build resume list for each user.
		# Old version - keep back for backword compatibility.
		
		
		$RForm = &JB_get_DynamicFormObject(2);
		while ($resume_row = mysql_fetch_array($result2, MYSQL_ASSOC)) {
			$RForm->set_values($resume_row); 
			$DATE = $RForm->get_template_value ("DATE");
			$FORMATTED_DATE = JB_get_formatted_date($DATE);

			$NAME = $RForm->get_raw_template_value ("RESUME_NAME");
			$resume_alert_list_html .= "<font face='arial' size='2'>$FORMATTED_DATE - ".strip_tags($NAME)." </font><br>";
			$resume_alert_list_text .= "$FORMATTED_DATE : ".strip_tags($NAME)." \r\n";
		}

		#############################################################################

		if (mysql_num_rows($result2) > 0 ) { // if we have anything to send?
			if ($VERBOSE=='YES') {
				echo "Email Debug: Sending Email to: ".jb_escape_html(jb_get_formatted_name($user_row['FirstName'], $user_row['LastName']))." (".$user_row['ID'].")<br> ";
			}
			
			$val = md5 ($user_row['Password'].$user_row['ID']);
			$employer_link = JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER."alerts.php?id=".$user_row['ID']."&key=$val";

			#### Load in the html alert template
			$lang = $user_row['lang'];
			if ($lang=='') {
				$lang = JB_get_default_lang();
			}
			$e_result = JB_get_email_template (6, $lang); // html alert template

			$e_row = mysql_fetch_array($e_result);
			$EmailMessage = $e_row['EmailText'];
			$from = $e_row['EmailFromAddress'];
			$from_name = $e_row['EmailFromName'];
			$subject = $e_row['EmailSubject'];
			$resume_alert_line = $e_row['sub_template'];

			$val = md5 ($user_row['Password'].$user_row['ID']);
			$_clink= JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER."alerts.php?id=".$user_row['ID']."&key=$val";

			 ################################################################################
			 # Build resume list for each user.
			 # HTML email, use $RForm->get_template_value()
			 ### 
			 if ($resume_alert_line != '') { // the new way of building the resume lines
				$resume_alert_list_html = ''; // template
				mysql_data_seek($result2, 0);
				$RForm = &JB_get_DynamicFormObject(2);
				$RForm->set_viewer($user_row['ID']);

				while ($resume_row = mysql_fetch_array($result2, MYSQL_ASSOC)) {
					
					$RForm->set_values($resume_row); //
					$temp_html = $resume_alert_line; // copy the template
					//$val = substr(md5 ($resume_row['resume_id'].$user_row['Password'].$user_row['ID']), 0, 10);
					$resume_db_link = JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER."search.php?resume_id=".$resume_row['resume_id'];//."&id=".$user_row['ID']."&key=$val";
					$temp_html = str_replace('%RESUME_DB_LINK%', $resume_db_link, $temp_html);

					// load in legacy values
					$DATE = $RForm->get_template_value('DATE');
					$FORMATTED_DATE = JB_get_formatted_date($DATE);

					$temp_html = str_replace("%FORMATTED_DATE%", $FORMATTED_DATE, $temp_html);
					$temp_html = str_replace("%DATE%", $FORMATTED_DATE, $temp_html);
					// substitute temporary template

					$RForm->reset_fields();
					$RForm->set_viewer($user_row['ID']);
					while ($field = $RForm->next_field()) {
						if (($field['field_type']=='BLANK') || ($field['field_type']=='SEPERATOR'))  {
							continue;
						}
						if (($field['template_tag'] !='') && (strlen($field['field_label'])>0)) {
							$temp_html = str_replace('%'.$field['template_tag'].'%', $RForm->get_template_value($field['template_tag']), $temp_html);
						}
					}

					// append to the list
					$resume_alert_list_html .= $temp_html."<br>\n";
				}
			 }
	
			 $EmailMessage = str_replace ("%FNAME%", $user_row['FirstName'], $EmailMessage);
			 $EmailMessage = str_replace ("%LNAME%", $user_row['LastName'], $EmailMessage);
			 $EmailMessage = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $EmailMessage);
			 $EmailMessage = str_replace ("%SITE_NAME%", JB_SITE_NAME, $EmailMessage);
			 $EmailMessage = str_replace ("%SITE_LOGO_URL%", JB_SITE_LOGO_URL, $EmailMessage);

			 $EmailMessage = str_replace ("%RESUME_ALERT%", '', $EmailMessage); // for compatibility with older version

			 $EmailMessage = str_replace ("%RESUME_ALERTS%", $resume_alert_list_html, $EmailMessage);
			 $EmailMessage = str_replace ("%KEYWORDS_LINE%", '', $EmailMessage); // for compatibility with older version
			 $EmailMessage = str_replace ("%EMPLOYER_LINK%", '<a href="'.$_clink.'">'.$_clink.'</a>', $EmailMessage); 

			$html_message = $EmailMessage;

			################

			$lang = $user_row['lang'];
			if ($lang=='') {
				$lang = JB_get_default_lang();
			}

			$e_result = JB_get_email_template (5, $lang);
			$e_row = mysql_fetch_array($e_result);
			$EmailMessage = $e_row['EmailText'];
			$from = $e_row['EmailFromAddress'];
			$from_name = $e_row['EmailFromName'];
			$subject = $e_row['EmailSubject'];
			$resume_alert_line = $e_row['sub_template'];

			 ################################################################################
			 # Build resume list for each user.
			 # Text email
			 # Use jb_get_raw_template_value()
			 # and then call strip_tags()
			 ### 
			 if ($resume_alert_line != '') { // the new way of building the resume lines
				$resume_alert_list_text = '';
				mysql_data_seek($result2, 0);
				$RForm = &JB_get_DynamicFormObject(2);
				$RForm->set_viewer($user_row['ID']);

				while ($resume_row = mysql_fetch_array($result2, MYSQL_ASSOC)) {
					$RForm->set_values($resume_row); // 
					$temp_text = $resume_alert_line; // copy the template
					//$val = substr(md5 ($resume_row['resume_id'].$user_row['Password'].$user_row['ID']), 0,10);
					$resume_db_link = JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER."search.php?resume_id=".$resume_row['resume_id'];//."&id=".$user_row['ID']."&key=$val";
					$temp_text = str_replace('%RESUME_DB_LINK%', $resume_db_link, $temp_text);

					// load in legacy values
					$DATE = $RForm->get_template_value ('DATE');
					$FORMATTED_DATE = JB_get_formatted_date($DATE);
					$temp_text = str_replace("%FORMATTED_DATE%", $FORMATTED_DATE, $temp_text);
					$temp_text = str_replace("%DATE%", $FORMATTED_DATE, $temp_text);
					
					// substitute temporary template

					$RForm->reset_fields();
					$RForm->set_viewer($user_row['ID']);
					while ($field = $RForm->next_field()) {
						if (($field['field_type']=='BLANK') || ($field['field_type']=='SEPERATOR'))  {
							continue;
						}
						if (($field['template_tag'] !='') && (strlen($field['field_label'])>0)) {
							$temp_text = str_replace('%'.$field['template_tag'].'%', $RForm->get_raw_template_value($field['template_tag']), $temp_text);

						}
					}
					// append to the list
					$resume_alert_list_text .= $temp_text."\n";
				}
			 }

			 $EmailMessage = str_replace ("%FNAME%", strip_tags($user_row['FirstName']), $EmailMessage);
			 $EmailMessage = str_replace ("%LNAME%", strip_tags($user_row['LastName']), $EmailMessage);
			 $EmailMessage = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $EmailMessage);
			 $EmailMessage = str_replace ("%SITE_NAME%", JB_SITE_NAME, $EmailMessage);
			 $EmailMessage = str_replace ("%SITE_LOGO_URL%", JB_SITE_LOGO_URL, $EmailMessage);
			 $EmailMessage = str_replace ("%RESUME_ALERTS%", $resume_alert_list_text, $EmailMessage);
			 $EmailMessage = str_replace ("%RESUME_ALERT%", '', $EmailMessage); // for compatibility with older version
			 $EmailMessage = str_replace ("%KEYWORDS_LINE%", $resume_alert_list_text, $EmailMessage); // deprecated, use %RESUME_ALERTS% instead
			 $EmailMessage = str_replace ("%EMPLOYER_LINK%", $_clink, $EmailMessage);

			$text_message = html_entity_decode ($EmailMessage);
			$text_message =  strip_tags($text_message);
			
			// send the sucker...

			if ($DO_SEND == "YES") {
					
				// mark as sent
				$now = (gmdate("Y-m-d H:i:s"));
				$sql = "UPDATE `employers` SET `alert_last_run`='$now' WHERE `ID`='".jb_escape_sql($user_row['ID'])."'";
				JB_mysql_query($sql) or die(mysql_error().$sql);

				if (JB_mysql_affected_rows() > 0) {
					// place on the queue
					JB_queue_mail($to_address, $to_name, $e_row['EmailFromAddress'], $e_row['EmailFromName'], $subject, $text_message, $html_message, $e_row['EmailID']);

				}

				

			}

		} // end IF $msg_body

	} // if valid email

}// close while loop

if (($_REQUEST['from_admin']) && (strpos($_SERVER['PHP_SELF'], 'admin')!==false)) {
	echo "The execution of the 'admin/resumealerts.php' script completed. You may check the <a href='email_queue.php'>outgoing mail queue</a>";
	$DO_SEND = "YES";
}

?>