<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
 
require("../config.php");
require_once ("../include/posts.inc.php");
$key = $_REQUEST['key'];
$id = (int) $_REQUEST['id'];
$action  = $_REQUEST['action'];
$notification = $_REQUEST['notification'];
$words = $_REQUEST['words'];
$email = $_REQUEST['email'];

include('login_functions.php'); 
if (($_REQUEST['key'] =='') && ($_REQUEST['id'] =='')) {
   JB_process_login(); 
} else {
   $sql = "SELECT * FROM `users` WHERE `ID`='".jb_escape_sql($id)."'";
   $result = JB_mysql_query($sql);
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
      $_SESSION['JB_Domain'] = "CANDIDATE";
	  $now = (gmdate("Y-m-d H:i:s"));
      JB_mysql_query("UPDATE `users` SET `login_count`=`login_count`+1, login_date ='$now' WHERE `user_id`=".$row['ID']." ");
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

JB_template_candidates_header();

JB_render_box_top(80, $label['c_alert_head']);
if ($action != '' ) {

   $now = (gmdate("Y-m-d H:i:s"));

	global $post_tag_to_search;
	foreach ($post_tag_to_search as $key => $val) {
		$name = $post_tag_to_search[$key]['field_id'];
		$_Q_STRING[$name] = $_REQUEST[$name];
	}
	$alert_query = serialize($_Q_STRING);

  $sql = "UPDATE `users` SET `notification1`='".jb_escape_sql($notification)."', `alert_keywords`='".jb_escape_sql($words)."', `alert_query`='".jb_escape_sql(addslashes($alert_query))."',  `alert_email`='".jb_escape_sql($email)."' WHERE `ID`='".jb_escape_sql($user_id)."'";
   JB_mysql_query ($sql) or die (mysql_error());


   $success = 1;
} else {
   $sql = "SELECT * FROM `users` WHERE `ID`='".jb_escape_sql($user_id)."'";
   $result = JB_mysql_query ($sql) or die (mysql_error());
   $row = mysql_fetch_array($result, MYSQL_ASSOC);

   $email=$row['alert_email'];
   if ($email == '') {
      $email=$row['Email'];
   }
   $notification=$row['Notification1'];
   $words = $row['alert_keywords'];

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

   $JBMarkup->ok_msg($label['c_alert_saved']);
   
}


?>

<?php echo $label["c_alert_head2"]; ?>
<p>
<?php echo $label["c_alert_intro"];?> 
</p>
<form method="post" action="alerts.php">
<input type="hidden" name="key" value="<?php echo JB_escape_html(JB_clean_str($_REQUEST['key'])); ?>">
<input type="hidden" name="id" value="<?php echo JB_escape_html(JB_clean_str($_REQUEST['id'])); ?>">
<table border="0" cellSpacing="1" cellPadding="3" class="dynamic_form" id='dynamic_form'>

<tr><td class="dynamic_form_field"><?php echo $label["c_alert_receive"];?></td>
<td class="dynamic_form_value"><input type="radio" name="notification" <?php if ($notification=='1') {echo " checked ";} ?> value="1"><?php echo $label["c_alert_yes"];?> <input type="radio" name="notification" <?php if ($notification=='0') {echo " checked ";} ?> value="0"><?php echo $label["c_alert_no"]?></td>
</tr>
<tr><td colspan="2" class="dynamic_form_value">&nbsp</td></tr>
<tr><td class="dynamic_form_field"><?php echo $label["c_alert_email"];?></td>
<td class="dynamic_form_value"><input type="text" size="35" name="email" value="<?php echo JB_escape_html($email);?>"></td>
</tr>

<tr><td colspan="2" class="dynamic_form_value">

<?php echo $label["c_alert_optional"]; ?><br>
<input type="checkbox" name="words" value="Y" <?php if ($words=='Y') { echo " checked "; } ?> > <?php echo $label["c_alert_filter_enable"]; ?>
</td></tr>
<tr><td colspan="2" class="dynamic_form_value"><?php


JB_display_dynamic_search_form (1, 2, 'ALERTS'); ?></td></tr>

<tr><td colspan="2" class="dynamic_form_value">
<input class="form_submit_button" type="submit" value="<?php echo $label['c_alert_submit_button'];?>" name="action"></td></tr>

</table>

</form>

<?php


JB_render_box_bottom();


JB_template_candidates_footer();?>