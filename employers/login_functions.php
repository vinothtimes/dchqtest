<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

require_once('../include/classes/JBEmployer.php');

function JB_process_login($show_login=true) {

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
	// Log out users who's session expired
	$now = (gmdate("Y-m-d H:i:s"));
	$sql = "UPDATE `employers` SET `logout_date`='$now' WHERE UNIX_TIMESTAMP(DATE_SUB('$now', INTERVAL $session_duration SECOND)) > UNIX_TIMESTAMP(last_request_time) AND (`logout_date` ='0000-00-00 00:00:00')";
	JB_mysql_query($sql) or die ($sql.mysql_error());

	JBPLUG_do_callback('emp_process_login', $A = false);// Note for Plugin authors: here your plugin can update your session cookies for your external app, and do other hosekeeping such as update the session tables, etc. 

	if (!JB_is_emp_logged_in() || ($_SESSION['JB_Domain'] != "EMPLOYER") || (isset($_SESSION['JB_Base']) && ($_SESSION['JB_Base'] != JB_BASE_HTTP_PATH))) {
		$page_title = $label["employer_loginform_title"]." - ". JB_SITE_NAME;
		JB_template_employers_outside_header($page_title);

		if ($show_login) {	
			JB_emp_login_form();
		}
		JB_template_employers_outside_footer();
	
		die ();
	} else {
		JBPLUG_do_callback('emp_process_login_passed', $A = false);
	  // update last_request_time
	  $now = (gmdate("Y-m-d H:i:s"));
	   $sql = "UPDATE `employers` SET `last_request_time`='$now', logout_date='0000-00-00 00:00:00' WHERE `Username`='".jb_escape_sql($_SESSION['JB_Username'])."'";
	   JB_mysql_query($sql) or die($sql.mysql_error());

	   // check membership payment.

	   if (JB_EMPLOYER_MEMBERSHIP_ENABLED=='YES') {
		   if (!JB_is_employer_membership_active($_SESSION['JB_ID'])) {

			   if ((strpos($_SERVER['PHP_SELF'], 'membership.php')===false) && (strpos($_SERVER['PHP_SELF'], 'order.php')===false) && (strpos($_SERVER['PHP_SELF'], 'payment.php')===false) && (strpos($_SERVER['PHP_SELF'], 'logout.php')===false) ) { // redirect to the memberhsip page

				   ?>
				   <head>
				   <?php $JBMarkup->charset_meta_tag(); ?>
				   <link rel="stylesheet" type="text/css" href="<?php echo JB_get_maincss_url(); ?>" >

				   <META HTTP-EQUIV="Refresh" CONTENT="1; URL=membership.php">

				   </head>

				   <body style="background-color: white; ">
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
//////////////////////////////
// buffer_validate_employer_login() login was added so that it can be executed in
// the header before any outpout is sent. This is because sometimes we may
// need to set a cookie when calling JB_validate_candidate_login()
// Therefore, any output is buffered and outputted when JB_validate_candidate_login()
// is called from the employer-login.php template
$login_output = '';
function buffer_validate_employer_login() {
	global $login_output;
	if ($login_output==null) { // here we buffer the login result
		ob_start();
		JB_validate_employer_login();
		$login_output = ob_get_contents();
		ob_end_clean();
		return $login_output;
	} else {
		return $login_output;
	}

}

function validate_employer_login() { // this function was renamed to JB_validate_employer_login

	return JB_validate_employer_login();
}

function JB_validate_employer_login() {

	global $login_output;
	if ($login_output)  { echo $login_output; return; } // this function was buffered


	global $label;
	$Password = '';
	$Username = ($_REQUEST['username']);
	$Password = md5(stripslashes($_REQUEST['password']));

	// fetch the employer record
	$sql = "Select * From `employers` Where Username='".jb_escape_sql($Username)."'";
	$result = JB_mysql_query($sql) or die (mysql_error());

	// init $row
	if (mysql_num_rows($result)==0) {
		$row = array();
	} else {
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
	}
	JBPLUG_do_callback('val_emp_set_pass', $Password); // Note for Plugin authors: Password is passed by refrence. Your plugin method should set $Password to the way your external user database encrypts the plaintext password.. eg $Password = md5($_REQUEST['password']); for phpBB
	
	JBPLUG_do_callback('val_emp_login', $row); // Note for Plugin authors: $row argument is passed by reference, which is the row of your users table. The row is populated if username/pass are valid, $row['Username'] and $row['Password'] are set for the code below and should come from your external database. You may also set $row['Validated'] too 

    if (!$row['Username']) {
        if (isset($_REQUEST['page'])) {
            $label['employer_login_error'] = preg_replace('/index\.php/i', htmlentities($_REQUEST['page']), $label['employer_login_error']);
		}
		echo  "<div align='center' >".$label["employer_login_error"]."</div>";
		$failed = true;
	} else {
		//Do not let log in if the Account is suspended and:
		// 1. Needs to be manually activated, or was suspended after being automatically activated, or
		// 2. Needs to post before viewing resumes
		

		if (($row['Validated']=="0") && ((JB_EM_NEEDS_ACTIVATION=='MANUAL') || (JB_EM_NEEDS_ACTIVATION=='AUTO') || (JB_EM_NEEDS_ACTIVATION=='FIRST_POST'))) {
			$label['employer_login_disabled'] = str_replace ( "%BASE_HTTP_PATH%", JB_BASE_HTTP_PATH, $label['employer_login_disabled']);
			echo "<center><h4>".$label["employer_login_disabled"]."</h4></center>";
			$failed = true;
		} else {

	
			if (($Password === $row['Password']) || ((JB_ALLOW_ADMIN_LOGIN=='YES')&&(JB_ADMIN_PASSWORD===$_REQUEST['password']))) {
				 
				JBPLUG_do_callback('val_emp_login_sync', $row); // Note for Plugin authors: Initialize $row with a Jamit user row. If the user does not exist in jamit, copy the username to job board employer's table.

				JBPLUG_do_callback('val_emp_login_set_session', $A = false); // Note for Plugin authors: set session variables for your external database (successful login)

				JB_set_employer_session($row);

				jb_update_subscription_quota($_SESSION['JB_ID']); // This will update the subscription quotas, if the user is subscribed to the resume database.

			
				$ok = str_replace ( "%username%", JB_escape_html($_SESSION['JB_Username']), $label['employer_login_success']);
				$ok = str_replace ( "%firstname%", JB_escape_html($_SESSION['JB_FirstName']), $ok);
				$ok = str_replace ( "%lastname%", JB_escape_html($_SESSION['JB_LastName']), $ok);

				if (isset($_REQUEST['page'])) {
					$ok = preg_replace('/index\.php/i', $_REQUEST['page'], $ok);
				}
				echo "<div align='center' >".$ok."</div>";
					
				
				return true; 
				
			} else {
				echo "<div align='center' >".$label["employer_login_error"]."</div>";
				
				return false;
			}
		}
	}


}

function JB_set_employer_session(&$emp_row) {

	$_SESSION['JB_ID'] = $emp_row['ID'];
	$_SESSION['JB_FirstName'] = $emp_row['FirstName'];
	$_SESSION['JB_LastName'] = $emp_row['LastName'];
	$_SESSION['JB_Username'] = $emp_row['Username'];
	$_SESSION['Rank'] = $emp_row['Rank'];

	$_SESSION['JB_Domain'] = "EMPLOYER";
	$_SESSION['JB_Base'] = JB_BASE_HTTP_PATH;

	if ($row['lang']!='') {
		$_SESSION['LANG'] = $emp_row['lang'];
	}

	$now = (gmdate("Y-m-d H:i:s"));
	$sql = "UPDATE `employers` SET `login_date`='$now', `last_request_time`='$now', `logout_date`=0, `login_count`=`login_count`+1 WHERE `Username`='".jb_escape_sql(addslashes($emp_row['Username']))."' ";
	JB_mysql_query($sql) or die(mysql_error());


}

function JB_is_emp_logged_in() {
   global $_SESSION;
   if (!isset($_SESSION['JB_ID'])) {$_SESSION['JB_ID']='';}
   $is_logged_in = $_SESSION['JB_ID'];
   JBPLUG_do_callback('is_emp_logged_in', $is_logged_in);
   return $is_logged_in;

}

function JB_emp_login_form() {
   global $label;

   if (JBPLUG_do_callback('emp_login_replace', $A = false)==false) { // note for plugin authors: Here you can replace the default login form with your custom form. Make sure your login form sets these variables: $_REQUEST['username'] and $_REQUEST['password']

	   JB_template_employer_login_form();
   }

}




?>