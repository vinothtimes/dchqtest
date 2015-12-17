<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################


function JB_process_login() {
	
	global $label;
	global $JBMarkup;

	if (!isset($_REQUEST['page'])) { // this us used to forward the user to the relevant page after login
		$q_str = ''; $amp = '';
        
		foreach ($_GET as $key=>$val) {
		   
            if (!is_array($val)) {
                $q_str .= $amp.$key.'='.urlencode($val);
            }
			$amp = '&';
		}
		$_REQUEST['page'] = $_SERVER['PHP_SELF'].'?'.$q_str;
	}

	$session_duration = ini_get ("session.gc_maxlifetime");
	if ($session_duration==false) {
		$session_duration=20*60;
	}
	// general house-keeping to end all sessions longer than session.gc_maxlifetime
	$now = (gmdate("Y-m-d H:i:s"));
	$sql = "UPDATE `users` SET `logout_date`='$now' WHERE UNIX_TIMESTAMP(DATE_SUB('$now', INTERVAL $session_duration SECOND)) > UNIX_TIMESTAMP(last_request_time) AND (`logout_date` ='0000-00-00 00:00:00')";
	JB_mysql_query($sql) or die ($sql.mysql_error());
	JBPLUG_do_callback('can_process_login', $A = false);// Note for Plugin authors: here your plugin can update your session cookies for your external app, and do other hosekeeping such as update the session tables, etc

	if (!JB_is_can_logged_in() || ($_SESSION['JB_Domain'] != "CANDIDATE") || (isset($_SESSION['JB_Base']) && ($_SESSION['JB_Base'] != JB_BASE_HTTP_PATH))) {

		
		$page_title =  $label["c_loginform_title"]." - ".JB_SITE_NAME;
		JB_template_candidates_outside_header($page_title);
		JB_can_login_form();
		JB_template_candidates_outside_footer();       
        die ();
	} else {
		JBPLUG_do_callback('can_process_login_passed', $A = false);
		// user is logged in
		$now = (gmdate("Y-m-d H:i:s"));
		$sql = "UPDATE `users` SET `last_request_time`='$now', logout_date='0000-00-00 00:00:00' WHERE `Username`='".jb_escape_sql(addslashes($_SESSION['JB_Username']))."'";

		JB_mysql_query($sql) or die($sql.mysql_error());

		// check membership payment.

		
		if (JB_CANDIDATE_MEMBERSHIP_ENABLED=='YES') {
		   if (!JB_is_candidate_membership_active($_SESSION['JB_ID'])) {

			   if ((strpos($_SERVER['PHP_SELF'], 'membership.php')===false) && (strpos($_SERVER['PHP_SELF'], 'order.php')===false) && (strpos($_SERVER['PHP_SELF'], 'payment.php')===false) && (strpos($_SERVER['PHP_SELF'], 'logout.php')===false)) { // redirect to the memberhsip page

				   ?>
				   <head>
				   <?php $JBMarkup->charset_meta_tag(); ?>
				   <link rel="stylesheet" type="text/css" href="<?php echo JB_get_maincss_url();?>" >
				   <META HTTP-EQUIV="Refresh" CONTENT="1; URL=membership.php">
				   </head>
				   <body style="background-color:white">
				   <p>&nbsp;</p>

				   <?php echo $label['membership_please_wait']; ?>

				   </body>


				   <?php

				  die();

			   }

		   }
	   }

   }


}

function JB_is_can_logged_in() {
   //global $_SESSION;
   if (!isset($_SESSION['JB_ID'])) {$_SESSION['JB_ID']='';}
   $is_logged_in = $_SESSION['JB_ID'];
   JBPLUG_do_callback('is_can_logged_in', $is_logged_in);
   return $is_logged_in;

}
################################################

function JB_can_login_form($action='') {
	global $label;
	
	if (JBPLUG_do_callback('can_login_replace', $A = false)==false) {
		JB_template_candidate_login_form($action);
	}

  
}

//////////////////////////////
// validate_candidate_login() login was added so that it can be executed in
// the header before any outpout is sent. This is because sometimes we may
// need to set a cookie when calling validate_candidate_login()
// Therefore, any output is buffered and outputted when validate_candidate_login()
// is called from the candidate-login.php template
$login_output;
function buffer_validate_candidate_login($login_page='') {
	global $login_output;
	if ($login_output==null) {
		ob_start();
		JB_validate_candidate_login($login_page='');
		$login_output = ob_get_contents();
		ob_end_clean();
		return $login_output;
	} else {
		return $login_output;
	}

}

function JB_set_candidate_session(&$candidate_row) {

	$_SESSION['JB_ID'] = $candidate_row['ID'];
				
	$_SESSION['JB_FirstName'] = $candidate_row['FirstName'];
	$_SESSION['JB_LastName'] = $candidate_row['LastName'];
	$_SESSION['JB_Username'] = $candidate_row['Username'];
	$_SESSION['Rank'] = $candidate_row['Rank'];

	if ($candidate_row['lang']!='') {
		$_SESSION['LANG'] = $candidate_row['lang'];
	}
	$_SESSION['JB_Domain'] = "CANDIDATE";
	$_SESSION['JB_Base'] = JB_BASE_HTTP_PATH;

	$now = (gmdate("Y-m-d H:i:s"));
	$sql = "UPDATE `users` SET `login_date`='$now', `last_request_time`='$now', `logout_date`=0, `login_count`=`login_count`+1 WHERE `Username`='".jb_escape_sql(addslashes($candidate_row['Username']))."' ";
	JB_mysql_query($sql) or die(mysql_error());

}

function JB_validate_candidate_login($login_page='') {
	return validate_candidate_login($login_page);
}

function validate_candidate_login($login_page='') {
	
	global $login_output;
	if ($login_output)  { echo $login_output; return; } // this function was buffered

	if ($login_page=='') {
		$login_page = JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER."index.php";
	}
	
	global $label;

	$Username = ($_REQUEST['username']);
	$Password = md5(stripslashes($_REQUEST['password']));
	$sql = "Select * From users Where Username='".jb_escape_sql($Username)."'";
	$result = JB_mysql_query($sql);
	
	// init $row
	if (mysql_num_rows($result)==0) {
		$row = array();
	} else {
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
	}
	JBPLUG_do_callback('val_can_set_pass', $Password); // Note for Plugin authors: Password is passed by refrence. Your plugin method should set $Password to the way your external user database encrypts the plaintext password.. eg $Password = md5($_REQUEST['password']); for phpBB

	JBPLUG_do_callback('val_can_login', $row); // Note for Plugin authors: $row argument is passed by reference, which is the row of your users table. The row is populated if username/pass are valid, $row['Username'] and $row['Password'] are set for the code below and should come from your external database. You may also set $row['Validated'] too  

	if ((!$row['Username']) && ($_REQUEST['silent']=='')) {

		$label["c_login_invalid_msg"] = str_replace('%LOGIN_PAGE%', $login_page, $label["c_login_invalid_msg"]);
		$label["c_login_invalid_msg"] = str_replace('%FORGOT_PAGE%',JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER."forgot.php",$label["c_login_invalid_msg"]);
		$label["c_login_invalid_msg"] = str_replace('%SIGNUP_PAGE%',JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER."signup.php",$label["c_login_invalid_msg"]);

		echo '<p style="text-align:center; ">'.$label["c_login_invalid_msg"]."</p>";
	} else {
		if ($row['Validated']=="0") {
			$label["c_login_notvalidated"] = str_replace('%BASE_HTTP_PATH%', JB_BASE_HTTP_PATH,  $label["c_login_notvalidated"]);
			echo '<p style="text-align:center; ">'.$label["c_login_notvalidated"].'</p>';
		} else {
			if (($Password === $row['Password']) || ((JB_ALLOW_ADMIN_LOGIN=='YES')&&(JB_ADMIN_PASSWORD===$_REQUEST['password']))) {

				JBPLUG_do_callback('val_can_login_sync', $row); // Note for Plugin authors: Initialize $row with a Jamit user row. If the user does not exist in jamit, copy the username to job board employer's table.

				JBPLUG_do_callback('val_can_login_set_session', $row); // Note for Plugin authors: set session variables for your external database (successful login)

				JB_set_candidate_session($row); // set session for the candidate

				
				$label['c_login_welcome'] = str_replace ("%FNAME%", JB_escape_html($_SESSION['JB_FirstName']), ($label['c_login_welcome']));
				$label['c_login_welcome'] = str_replace ("%LNAME%", JB_escape_html($_SESSION['JB_LastName']), ($label['c_login_welcome']));
				$label['c_login_welcome'] = str_replace ("%USERNAME%", JB_escape_html($_SESSION['JB_Username']), ($label['c_login_welcome']));

				if (isset($_REQUEST['page'])) {
					$label['c_login_welcome'] = preg_replace('/index\.php/i', htmlentities($_REQUEST['page']), $label['c_login_welcome']);
				}
		
				if ($_REQUEST['silent']=='') {
					echo '<p style="text-align:center; ">'.$label["c_login_welcome"].'</p>';
				}
			} else {
			 
				$label["c_login_invalid_msg"] = str_replace('%LOGIN_PAGE%', htmlentities($login_page), $label["c_login_invalid_msg"]);
				$label["c_login_invalid_msg"] = str_replace('%FORGOT_PAGE%',JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER."forgot.php",$label["c_login_invalid_msg"]);
				$label["c_login_invalid_msg"] = str_replace('%SIGNUP_PAGE%',JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER."signup.php",$label["c_login_invalid_msg"]);
                if (strpos($login_page, 'apply_iframe.php')!==false) {
                    $label["c_login_invalid_msg"] = str_replace('_parent', '_self', $label["c_login_invalid_msg"]);
                }
				echo '<div style="text-align:center;">'.$label["c_login_invalid_msg"].'</div>';
			}
		}
	}
}
?>