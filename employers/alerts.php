<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

require("../config.php");
require_once ("../include/resumes.inc.php");
$key = $_REQUEST['key'];
$id = (int) $_REQUEST['id'];

$words = $_REQUEST['words'];
$notification = $_REQUEST['notification'];
$email = $_REQUEST['email'];
$submit = $_REQUEST['submit'];

include('login_functions.php'); 
if (($key =='') && ($id =='')) {
   JB_process_login(); 
} else {
	// automatically login with a key.
   $sql = "SELECT * FROM `employers` WHERE `ID`='".jb_escape_sql($id)."' AND Validated='1' ";
   $result = JB_mysql_query($sql) or die(mysql_error());
   $row = mysql_fetch_array($result, MYSQL_ASSOC);

   $key = substr($key, 0,15);
   $comp_key = md5 ($row['Password'].$id);
   $comp_key = substr($comp_key, 0,15);

   if ($key == $comp_key) {

	    /*
	   As of version 2.9.5 we do not log the user in automatically!
	   We only allow them to modify the alerts page, then they have
	   to log in to view other pages

     
      $_SESSION['JB_ID'] = $row['ID'];
	  $_SESSION['JB_FirstName'] = $row['FirstName'];
	  $_SESSION['JB_LastName'] = $row['LastName'];
	  $_SESSION['JB_Username'] = $row['Username'];
	  $_SESSION['Rank'] = $row['Rank'];
	 
      if ($row['lang']!='') {
		$_SESSION['LANG'] = $row['lang'];
	  }
      $_SESSION['JB_Domain'] = "EMPLOYER";
		$now = (gmdate("Y-m-d H:i:s"));
      $sql = "UPDATE `employers` SET `login_date`='$now', `last_request_time`='$now', `logout_date`=0, `login_count`=`login_count`+1 WHERE `ID`='$id' ";
      
      JB_mysql_query($sql);

	  */


   } else {

      JB_process_login(); 

   }

}
if ($_SESSION['JB_ID']!='') {
	$user_id = $_SESSION['JB_ID'];
} elseif (is_numeric($_REQUEST['id'])) {
	$user_id = $_REQUEST['id'];
}
 
 ?>

<?php JB_template_employers_header(); ?>


<?php

JB_render_box_top(80, $label["employer_resume_alerts_head"]);
		
if ($_REQUEST['action'] != '' ) {

	global $resume_tag_to_search;

	foreach ($resume_tag_to_search as $key => $val) {
		$name = $resume_tag_to_search[$key]['field_id'];
		$_Q_STRING[$name] = $_REQUEST[$name];
	}
	$alert_query = serialize($_Q_STRING);

   $now = (gmdate("Y-m-d H:i:s"));
  $sql = "UPDATE `employers` SET  `notification1`='".jb_escape_sql($notification)."', `alert_keywords`='".jb_escape_sql($words)."',  `alert_email`='".jb_escape_sql($email)."', alert_query='".jb_escape_sql(addslashes($alert_query))."' WHERE `ID`='".jb_escape_sql($user_id)."'";
   JB_mysql_query ($sql) or die (mysql_error());
   $success = 1;
} else {
   $sql = "SELECT * FROM `employers` WHERE `ID`='".jb_escape_sql($user_id)."'";
   $result = JB_mysql_query ($sql) or die (mysql_error());
   $row = mysql_fetch_array($result, MYSQL_ASSOC);

   $email=$row['alert_email'];
   if ($email == '') {
      $email=$row['Email'];
   }
   $notification=$row['Notification1'];
   $words=$row['alert_keywords'];

    if ($row['alert_query']!='') {

	   $_Q_STRING = unserialize($row['alert_query']);

	   if (is_array($_Q_STRING)) {
			foreach ($_Q_STRING as $key => $val) {
				$_REQUEST[$key]=$val;
			}
	   }
   }
   
}

if ($success == 1) {

   $JBMarkup->ok_msg($label["employer_resume_alerts_saved"]);
   
}


?>

<div class="explanation_note"><?php echo $label["employer_resume_alerts_intro"];?> </div>
<p>
<form method="post" action="alerts.php" >
<input type="hidden" name="key" value="<?php echo JB_escape_html(JB_clean_str($_REQUEST['key'])); ?>">
<input type="hidden" name="id" value="<?php echo JB_escape_html(JB_clean_str($_REQUEST['id'])); ?>">
<table border="0" cellSpacing="1" cellPadding="3" class="dynamic_form" id='dynamic_form'>

<tr><td class="dynamic_form_field"><?php echo $label["employer_resume_alerts_activate"]; ?></td>
<td class="dynamic_form_value"><input type="radio" name="notification" <?php if ($notification=='1') {echo " checked ";} ?> value="1"><?php echo $label["employer_resume_alerts_yes"]; ?> <input type="radio" name="notification" <?php if ($notification=='0') {echo " checked ";} ?> value="0"><?php echo $label["employer_resume_alerts_no"];?></td>
</tr>
<tr><td colspan="2" class="dynamic_form_value">&nbsp;</td></tr>
<tr><td class="dynamic_form_field"> <?php echo $label["employer_resume_alerts_email"] ?></td>
<td class="dynamic_form_value"><input type="text" size="35" name="email" value="<?php echo $email;?>"></td>
</tr>
<!--
<tr><td><FONT SIZE="2" face='arial' COLOR=""><?php echo $label["employer_resume_alerts_keywords"]; ?></font></td>
<td><input type="text" name="words" size='50' value="<?php echo $words;?>"><FONT SIZE="2" face='arial' COLOR=""><?php echo $label["employer_resume_alerts_keywords_eg"]; ?></font></td>
</tr>
-->
<tr><td colspan="2" class="dynamic_form_2_col_field">

<?php echo $label["employer_resume_alerts_optional"]; ?><br>
<input type="checkbox" name="words" value="Y" <?php if ($words=='Y') { echo " checked "; } ?> > <?php echo $label["employer_resume_alerts_filter_enable"]; ?>
</td></tr>
<tr><td colspan="2" class="dynamic_form_2_col_field"><?php
//$search_form_mode='save';

JB_display_dynamic_search_form (2, 2, 'ALERTS'); ?></td></tr>

<tr><td colspan="2" class="dynamic_form_value"><input class="form_submit_button" type="submit" value="<?php echo $label["employer_resume_alerts_submit"];?>" name="action">
<p><h3>&gt;&gt; <?php echo $label['package_resume_alerts_link']; ?></h3></p>
</td></tr>
</table>

</form>
			

<?php 
JB_render_box_bottom();
?>


<?php JB_template_employers_footer(); ?>