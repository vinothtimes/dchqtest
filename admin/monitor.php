<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
@set_time_limit ( 180 );
require("../config.php");
require (dirname(__FILE__)."/admin_common.php");
require_once("../include/mail_monitor_functions.php");

JB_admin_header('Admin -> Email Monitor');

if (JB_DEMO_MODE=='YES') {
	$JBMarkup->ok_msg('Demo mode is enabled, some features disabled');
}
?>
<h3>Email Monitor</h3>
<p>
This tool will scan your email box for any bounced (undelivered) emails and it will unsubscribe the email if the email matches a user who is on your job board. For each match, it will delete the bounced email from your mail box, unsubscribe them form the alerts & newsletter, then report the result on this page. 
</p>

<?php

if ($_REQUEST['save']!='') {

	$sql = "REPLACE INTO jb_config (`key`, `val`) VALUES('".MON_ENABLED."', '".jb_escape_sql($_REQUEST['mon_enabled'])."')  ";
	$result = JB_mysql_query($sql) or die(mysql_error($result));
	$sql = "REPLACE INTO jb_config (`key`, `val`) VALUES('".MON_EMAIL_POP_USER."', '".jb_escape_sql($_REQUEST['mon_email_pop_user'])."')  ";
	$result = JB_mysql_query($sql) or die(mysql_error($result));
	$sql = "REPLACE INTO jb_config (`key`, `val`) VALUES('".MON_EMAIL_POP_SERVER."', '".jb_escape_sql($_REQUEST['mon_email_pop_server'])."')  ";
	$result = JB_mysql_query($sql) or die(mysql_error($result));
	$sql = "REPLACE INTO jb_config (`key`, `val`) VALUES('".MON_EMAIL_POP_PASS."', '".jb_escape_sql($_REQUEST['mon_email_pop_pass'])."')  ";
	$result = JB_mysql_query($sql) or die(mysql_error($result));
	$sql = "REPLACE INTO jb_config (`key`, `val`) VALUES('".MON_POP3_PORT."', '".jb_escape_sql($_REQUEST['mon_pop3_port'])."')  ";
	$result = JB_mysql_query($sql) or die(mysql_error($result));
	$sql = "REPLACE INTO jb_config (`key`, `val`) VALUES('".MON_LOG_DAYS."', '".jb_escape_sql($_REQUEST['mon_log_days'])."')  ";
	$result = JB_mysql_query($sql) or die(mysql_error($result));
	$sql = "REPLACE INTO jb_config (`key`, `val`) VALUES('".MON_DEL_DAYS."', '".jb_escape_sql($_REQUEST['mon_del_days'])."')  ";
	$result = JB_mysql_query($sql) or die(mysql_error($result));

	$JBMarkup->ok_msg('Changes Saved.');
	
}


JB_load_monitor_constants();

if ($_REQUEST['scan']!='') {
	if ((MON_ENABLED=='YES') && (JB_DEMO_MODE!='YES')) {
		JB_monitor_mail_box();
	}
}

if (!defined('MON_POP3_PORT')) define('MON_POP3_PORT', 110);
if (!defined('MON_EMAIL_POP_USER')) define('MON_EMAIL_POP_USER', JB_EMAIL_SMTP_USER);
if (!defined('MON_EMAIL_POP_SERVER')) define('MON_EMAIL_POP_SERVER', JB_EMAIL_POP_SERVER);
if (!defined('MON_EMAIL_POP_PASS')) define('MON_EMAIL_POP_PASS', JB_EMAIL_SMTP_PASS);
if (!defined('MON_LOG_DAYS')) define('MON_LOG_DAYS', 30);
if (!defined('MON_DEL_DAYS')) define('MON_DEL_DAYS', 30);

?>
 <form method="post" action='monitor.php'>
  <p>Important - <font color="red" size="4"><b>*</b></font> indicates a mandatory field. </p>
  <table border="0" cellpadding="5" cellspacing="2" style="border-style:groove" width="100%" bgcolor="#FFFFFF">
    <tr>
      <td  colspan="2" width="360" bgcolor="#e6f2ea">
      <p ><font face="Verdana" size="1"><b>Email Settings</b></font></td>
    </tr>
	
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">POP3 Server address</font></td>
      <td  bgcolor="#e6f2ea">
      <input type="text" name="mon_email_pop_server" size="33" value="<?php echo MON_EMAIL_POP_SERVER; ?>"><font color="red" size="4"><b>*</b></font><br><font face="Verdana" size="1">Eg. mail.example.com - usually the same as SMTP server</font></td>
    </tr>
     <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">POP3 Username</font></td>
      <td  bgcolor="#e6f2ea">
      <input type="text" name="mon_email_pop_user" size="33" value="<?php echo MON_EMAIL_POP_USER; ?>"><font color="red" size="4"><b>*</b></font><font face="Verdana" size="1"></font><br><font face="Verdana" size="1">Eg. myemail@example.com or myemail+example.com</font></td>
    </tr>
     <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">POP3 Password</font></td>
      <td  bgcolor="#e6f2ea">
      <input type="password" name="mon_email_pop_pass"  size="33" value="<?php echo MON_EMAIL_POP_PASS; ?>"><font color="red" size="4"><b>*</b></font><font face="Verdana" size="1"></font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">POP3 Port</font></td>
      <td  bgcolor="#e6f2ea">
      <input type="text" name="mon_pop3_port" size="10" value="<?php echo MON_POP3_PORT; ?>"><font face="Verdana" size="1">(Leave blank to default to 110)</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Monitor Log</font></td>
      <td  bgcolor="#e6f2ea"> Remove entries from log after 
      <input type="text" name="mon_log_days" size="5" value="<?php echo MON_LOG_DAYS; ?>"><font face="Verdana" size="1"> days (0 = never)</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Delete old messages</font></td>
      <td  bgcolor="#e6f2ea"> <font face="Verdana" size="1">Delete old messages messages after</font>
      <input type="text" name="mon_del_days" size="5" value="<?php echo MON_DEL_DAYS; ?>"><font face="Verdana" size="1"> days (0 = never). It is recommened that the inbox is kept small for better performance</font></td>
    </tr>
	
	<tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Monitor Enabled?</font></td>
      <td  bgcolor="#e6f2ea"><font size="1" face="Verdana">
     <input type="radio" name="mon_enabled" value="YES"  <?php if (MON_ENABLED=='YES') { echo " checked "; } ?> >Yes - the mailbox will be scanned every time the cron job<br>
	  <input type="radio" name="mon_enabled" value="NO"  <?php if (MON_ENABLED=='NO') { echo " checked "; } ?> >No
	 </font></td>
    </tr>
    </table>
	<input type="submit" name="save" value='Save Settings'>
</form>
<input type="button" style='font-size: 9pt' value="Run Scan Manually - Test" onclick='window.location="monitor.php?scan=do" '>

<?php
JB_mon_list_log ();

JB_admin_footer();

?>
