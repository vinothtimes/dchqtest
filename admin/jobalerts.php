<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
define ('NO_HOUSE_KEEPING', true);

if (function_exists('JB_basedirpath')) { // config.php was required by corn
	$dir = JB_basedirpath();
} else {
	$dir = '../'; // running from web
}

if (!defined('JB_SITE_NAME')) { 
	require($dir.'/config.php');	
}

require_once($dir.'/include/posts.inc.php');


ini_set('max_execution_time', 100200);
$VERBOSE = JB_EMAIL_DEBUG_SWITCH;
//$VERBOSE = 'YES';

$DO_SEND = '';
if (defined('JB_JOB_ALERTS_DO_SEND')) { // cron
	$DO_SEND = "YES";
} else {
	require (dirname(__FILE__)."/admin_common.php"); // require Admin login
	if (JB_JOB_ALERTS_ENABLED!='YES') {
		echo 'Resume Alerts feature is not enabled in Admin->Main Config';
		return;
	}
	?>
<h3>Automation Instructions:</h3><br>
This feature is set to run automatically every hour. Please <a href='cron.php'>see here</a> for more details.<br>

<br>Run Manually form Web:<input type="button" value="Queue Emails" onclick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?send_action=send&amp;from_admin=1' " >
<p>
<?php
	if (!isset($_REQUEST['send_action'])) {
		return;
	} else {
		$DO_SEND = "YES";
	}
}

$tag_to_field_id = JB_post_tag_to_field_id_init();


if (JB_JOB_ALERTS_ACTIVE_DAYS=='JB_JOB_ALERTS_ACTIVE_DAYS') {
	$JB_JOB_ALERTS_ACTIVE_DAYS=1;
} else {
	$JB_JOB_ALERTS_ACTIVE_DAYS=JB_JOB_ALERTS_ACTIVE_DAYS;

}

$now = (gmdate("Y-m-d H:i:s"));
$sql = "SELECT alert_email, Email, FirstName, LastName, alert_keywords, alert_query, Password, ID, alert_last_run, lang  FROM `users` WHERE `Notification1`='1' AND `Validated`=1 AND DATE_SUB('$now', INTERVAL '".jb_escape_sql(JB_JOB_ALERTS_DAYS)."' DAY) > alert_last_run AND last_request_time > DATE_SUB('$now', INTERVAL '".jb_escape_sql($JB_JOB_ALERTS_ACTIVE_DAYS)."' DAY) ";
//$sql = "SELECT * FROM `users` limit 1"; // for testing
//$VERBOSE='YES';
$result = JB_mysql_query($sql) or die(mysql_error());
$no_rows = mysql_num_rows($result);
if ($VERBOSE == 'YES') {
	echo "Email Debug:$no_rows users to email";
}
$count = 0;
while ($user_row = mysql_fetch_array($result, MYSQL_ASSOC)) {

	$to_name= JB_get_formatted_name($user_row['FirstName'], $user_row['LastName']);
	if ($user_row['alert_email']!='') { // users can specify a custom email for alers
		$to_address=trim($user_row['alert_email']);
	} else {
		$to_address=trim($user_row['Email']);
	}

	if ($VERBOSE == 'YES') {
		echo "Email Debug: Processing ($to_name &lt;$to_address&gt;<br>";
	}

	if (!JB_validate_mail($to_address)) {
		if ($VERBOSE == 'YES') {
			echo "Email Debug: Invalid email address for: |$to_name| [$to_address] (".$user_row['ID'].") <br>";
		}
		
	} else {
     

		$where_sql = '';

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

				$where_sql = JB_generate_search_sql(1, $_SEARCH_INPUT);
			}


		}

		if (JB_JOB_ALERTS_ITEMS != 'JB_JOB_ALERTS_ITEMS') {
		   $JB_JOB_ALERTS_ITEMS = JB_JOB_ALERTS_ITEMS;

		} else {
			$JB_JOB_ALERTS_ITEMS = 10;
		}
		
		$sql = "SELECT *, DATE_FORMAT(`post_date`, '%d-%b-%Y') AS formatted_date FROM `posts_table` WHERE `post_date` > '".jb_escape_sql($user_row['alert_last_run'])."' AND expired='N' AND `approved`='Y' $where_sql ORDER BY `post_date` DESC LIMIT $JB_JOB_ALERTS_ITEMS";

		$result2 = JB_mysql_query($sql) or die(mysql_error().$sql);
		if ($VERBOSE == 'YES') {
			echo "Email Debug: Jobs found:".mysql_num_rows($result2)."<br>";
		}

		$html_msg_body = '';
		$text_msg_body = '';
		
		################################################################################
		# Build resume list for each user.
		# Old version - keep back for backword compatibility.

		$PForm = &JB_get_DynamicFormObject(1);
		while ($post_row = mysql_fetch_array($result2, MYSQL_ASSOC)) {
			
			$PForm->set_values($post_row); 

			$DATE = JB_get_formatted_date($PForm->get_template_value ("DATE"));
			$POST_MODE = $PForm->get_raw_template_value ("POST_MODE");
			$FORMATTED_DATE = $DATE; // same as $DATE
			$TITLE = $PForm->get_raw_template_value ("TITLE");
			$LOCATION = $PForm->get_raw_template_value ("LOCATION");
			$DESCRIPTION = $PForm->get_raw_template_value ("DESCRIPTION");

			$DESCRIPTION = str_replace ("\n", "&nbsp;", $DESCRIPTION); // ''
			$DESCRIPTION = str_replace ("<br>", " ", $DESCRIPTION); // add spaces
			$DESCRIPTION = str_replace ("</p>", " </p>", $DESCRIPTION); // ''


			$job_alert_list_html .= "<font face='arial' size='2'>$FORMATTED_DATE - <a href='".JB_BASE_HTTP_PATH."index.php?post_id=".$post_row['post_id']."'>".$TITLE."</a></font> (".$LOCATION.")<font face='arial' size='1' color='#808080'> ".substr (strip_tags($DESCRIPTION), 0, 150)."...</font><br>";

			$job_alert_list_text .= "$FORMATTED_DATE : \"$TITLE\" (".$LOCATION.")\r\nLink: ".JB_BASE_HTTP_PATH."index.php?post_id=".$post_row['post_id']." \r\n\r\n";
			$job_alert_list_text = strip_tags($job_alert_list_text);

		}

		

		if (mysql_num_rows($result2) > 0 ) { // if we have anything to send?

			if ($VERBOSE == 'YES') {
				echo "Email Debug: Sending Email to: ".jb_escape_html(JB_get_formatted_name($user_row['FirstName'], $user_row['LastName']))." (".$user_row['ID'].")<br> \n";
			}

			// now send the message.

			# Validation link
			$val = md5 ($user_row['Password'].$user_row['ID']);
			$_clink = JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER."alerts.php?id=".$user_row['ID']."&key=$val";



			############################
			# Prepare the HTML version

			$lang = $user_row['lang'];
			if ($lang=='') {
				$lang = JB_get_default_lang();
			}


			$e_result = JB_get_email_template (8, $lang);

			$e_row = mysql_fetch_array($e_result, MYSQL_ASSOC);
			$EmailMessage = $e_row['EmailText'];
			$from = $e_row['EmailFromAddress'];
			$from_name = $e_row['EmailFromName'];
			$subject = $e_row['EmailSubject'];
			$job_alert_line = $e_row['sub_template'];


			################################################################################
			# Build job list for each user.
			# HTML email
			# For HTML, use get_template_value() - this will escape any HTML unless
			# it came form the Editor filed. Also, it will format the data
			### 
			if ($job_alert_line != '') { // the new way of building the resume lines
				$job_alert_list_html = ''; // discard legacy template
				mysql_data_seek($result2, 0);
				$PForm = &JB_get_DynamicFormObject(1);
				$PForm->set_viewer($user_row['ID']);
				while ($post_row = mysql_fetch_array($result2,MYSQL_ASSOC)) {
					$PForm->set_values($post_row); 
					$temp_html = $job_alert_line; // copy the template
					$DESCRIPTION = $PForm->get_template_value ("DESCRIPTION");

					$DESCRIPTION = str_replace ("\n", "&nbsp;", $DESCRIPTION); // ''
					$DESCRIPTION = str_replace ("<br>", " ", $DESCRIPTION); // add spaces
					$DESCRIPTION = str_replace ("</p>", " </p>", $DESCRIPTION); // ''

					$trunc_str_len ='';
					$DESCRIPTION = JB_truncate_html_str (strip_tags($DESCRIPTION), 150, $trunc_str_len, true);
					

					$temp_html = str_replace("%DESCRIPTION%", $DESCRIPTION, $temp_html);

					$DATE = JB_get_formatted_date($PForm->get_template_value ("DATE"));

					$FORMATTED_DATE = $DATE; //JB_get_formatted_date($DATE);
					$temp_html = str_replace('%FORMATTED_DATE%', $FORMATTED_DATE, $temp_html);
					//$temp_html = str_replace("%POST_ID%", $PForm->get_value('post_id'), $temp_html);
					$temp_html = str_replace('%BASE_HTTP_PATH%', JB_BASE_HTTP_PATH, $temp_html);
					
					// substitute temporary template
					$PForm->reset_fields();
					$PForm->set_viewer($user_row['ID']);
					while ($field = $PForm->next_field()) {
						if (($field['field_type']=='BLANK') || ($field['field_type']=='SEPERATOR'))  {
							continue;
						}
						if (($field['template_tag'] !='') && (strlen($field['field_label'])>0)) {
							$temp_html = str_replace('%'.$field['template_tag'].'%', $PForm->get_template_value($field['template_tag']), $temp_html);
						}
					}
					// append to the list
					$job_alert_list_html .= $temp_html."<br>\n";
				}
			}
			 
			// substitute main template tags

			$EmailMessage = str_replace ("%FNAME%", jb_escape_html($user_row['FirstName']), $EmailMessage);
			$EmailMessage = str_replace ("%LNAME%", jb_escape_html($user_row['LastName']), $EmailMessage);
			$EmailMessage = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $EmailMessage);
			$EmailMessage = str_replace ("%SITE_NAME%", JB_SITE_NAME, $EmailMessage);
			$EmailMessage = str_replace ("%SITE_LOGO_URL%", JB_SITE_LOGO_URL, $EmailMessage);
			$EmailMessage = str_replace ("%JOB_ALERTS%", $job_alert_list_html, $EmailMessage);
			$EmailMessage = str_replace ("%KEYWORDS_LINE%", '', $EmailMessage); // compatibility with older versions
			$EmailMessage = str_replace ("%CANDIDATE_LINK%", $_clink, $EmailMessage);

			$html_message = $EmailMessage;

			############################
			# Prepare the Text version

			$lang = $user_row['lang'];
			if ($lang=='') {
				$lang = JB_get_default_lang();
			}

			$e_result = JB_get_email_template (7, $lang);
			$e_row = mysql_fetch_array($e_result, MYSQL_ASSOC);
			$EmailMessage = $e_row['EmailText'];
			$from = $e_row['EmailFromAddress'];
			$from_name = $e_row['EmailFromName'];
			$subject = $e_row['EmailSubject'];
			$job_alert_line = $e_row['sub_template'];
			// substitute template tags

			################################################################################
			# Build job list for each user.
			# Text email message
			# use JB_get_raw_template)value, then use strip_tags()
			# Data will not be formatted, eg. use JB_get_formatted_date() on DATE
			### 
			if ($job_alert_line != '') { // the new way of building the resume lines
				$job_alert_list_text = ''; // discard legacy template
				mysql_data_seek($result2, 0);
				
				while ($post_row = mysql_fetch_array($result2, MYSQL_ASSOC)) {
					$PForm->set_values($post_row); // we can get data from $PForm->get_template_value()
					$temp_text = $job_alert_line; // copy the template
					$DESCRIPTION = $PForm->get_raw_template_value ("DESCRIPTION");

					// strip html tags from description
					$DESCRIPTION = substr (strip_tags($DESCRIPTION), 0, 150);

					$temp_text = str_replace("%DESCRIPTION%", $DESCRIPTION, $temp_text);
					$DATE = $PForm->get_raw_template_value ("DATE");
					$FORMATTED_DATE = JB_get_formatted_date($DATE);
					$temp_text = str_replace("%FORMATTED_DATE%", $FORMATTED_DATE, $temp_text);
					$temp_text = str_replace("%BASE_HTTP_PATH%", JB_BASE_HTTP_PATH, $temp_text);
					$temp_text = str_replace("%POST_ID%", $PForm->get_value('post_id'), $temp_text);
					// substitute temporary template
					$PForm->reset_fields();
					$PForm->set_viewer($user_row['ID']);
					while ($field = $PForm->next_field()) {
						if (($field['field_type']=='BLANK') || ($field['field_type']=='SEPERATOR'))  {
							continue;
						}
						if (($field['template_tag'] !='') && (strlen($field['field_label'])>0)) {
							$temp_text = str_replace('%'.$field['template_tag'].'%', $PForm->get_raw_template_value($field['template_tag']), $temp_text);
						}
					}
					// append to the list
					$job_alert_list_text .= $temp_text."\n";
				}
			}

			$EmailMessage = str_replace ("%FNAME%", strip_tags($user_row['FirstName']), $EmailMessage);
			$EmailMessage = str_replace ("%LNAME%", strip_tags($user_row['LastName']), $EmailMessage);
			$EmailMessage = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $EmailMessage);
			$EmailMessage = str_replace ("%SITE_NAME%", JB_SITE_NAME, $EmailMessage);
			$EmailMessage = str_replace ("%SITE_LOGO_URL%", JB_SITE_LOGO_URL, $EmailMessage);
			$EmailMessage = str_replace ("%JOB_ALERTS%", $job_alert_list_text, $EmailMessage);
			$EmailMessage = str_replace ("%KEYWORDS_LINE%", $job_alert_list_text, $EmailMessage);
			$EmailMessage = str_replace ("%CANDIDATE_LINK%", $_clink, $EmailMessage);

			// strip all HTML tags.
			$text_message = html_entity_decode ($EmailMessage);
			$text_message = strip_tags($text_message);
			
			if ($DO_SEND == 'YES') {

				// mark the alert as sent..- 
				// update the run.
				$now = (gmdate("Y-m-d H:i:s"));
				$sql = "UPDATE `users` SET `alert_last_run`='$now' WHERE `ID`='".jb_escape_sql($user_row['ID'])."'";
				JB_mysql_query($sql) or die(mysql_error().$sql);

				if (JB_mysql_affected_rows() > 0) {
					JB_queue_mail($to_address, $to_name, $e_row['EmailFromAddress'], $e_row['EmailFromName'], $subject, $text_message, $html_message, $e_row['EmailID']);
				}
			}

		}
		
	} // end IF $msg_body

	if ($VERBOSE == 'YES') {
		echo "<hr>\n";
	} 
   
} // close while loop



if (($_REQUEST['from_admin']) && (strpos($_SERVER['PHP_SELF'], 'admin')!==false)) {
	echo "The execution of the 'admin/jobalerts.php' script completed. You may check the <a href='email_queue.php'>outgoing mail queue</a>";
	$DO_SEND = "YES";
}

?>