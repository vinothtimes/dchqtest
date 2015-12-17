<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
ini_set('max_execution_time', 500);
require ('../config.php');

require (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Email Queue');


if ($_REQUEST['action']=='delall') {

	$sql = "SELECT SQL_BUFFER_RESULT mail_id, att1_name,att2_name,att3_name  FROM mail_queue ";
	$result = JB_mysql_query($sql) or die(mysql_error());
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

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
if ($_REQUEST['action']=='delsent') {
	$sql = "SELECT mail_id, att1_name, att2_name, att3_name from mail_queue where `status`='sent' ";
	$result = JB_mysql_query($sql) or die(mysql_error());
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		if ($row['att1_name']!='') {
			unlink($row['att1_name']);
		}

		if ($row['att2_name']!='') {
			unlink($row['att2_name']);
		}

		if ($row['att3_name']!='') {
			unlink($row['att3_name']);
		}

		$sql = "DELETE FROM mail_queue where mail_id='".jb_escape_sql($row['mail_id'])."' ";
		JB_mysql_query($sql) or die(mysql_error());

	}
	
}
if ($_REQUEST['action']=='delerror') {
	$sql = "SELECT mail_id, att1_name, att2_name, att3_name from mail_queue where `status`='error' ";
	$result = JB_mysql_query($sql) or die(mysql_error());
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		if ($row['att1_name']!='') {
			unlink($row['att1_name']);
		}

		if ($row['att2_name']!='') {
			unlink($row['att2_name']);
		}

		if ($row['att3_name']!='') {
			unlink($row['att3_name']);
		}

		$sql = "DELETE FROM mail_queue where mail_id='".jb_escape_sql($row['mail_id'])."' ";
		JB_mysql_query($sql) or die(mysql_error());

	}
	
}
if ($_REQUEST['action']=='resend') {

	$_REQUEST['mail_id'] = (int) $_REQUEST['mail_id'];

	$sql = "UPDATE mail_queue SET status='queued' WHERE mail_id='".jb_escape_sql($_REQUEST['mail_id'])."'";
	JB_mysql_query($sql) or die(mysql_error());

	JB_process_mail_queue(1, $_REQUEST['mail_id']);

}

$JB_EMAILS_PER_BATCH = JB_EMAILS_PER_BATCH;
if ($JB_EMAILS_PER_BATCH=='') {
	$JB_EMAILS_PER_BATCH = 10;
}

if ($_REQUEST['action']=='send') {
	//$sql = "DELETE FROM mail_queue where `status`='sent' ";
	//JB_mysql_query($sql) or die(mysql_error());

	
	JB_process_mail_queue($JB_EMAILS_PER_BATCH);
}

$q_to_add = $_REQUEST['q_to_add'];
$q_to_name = $_REQUEST['q_to_name'];
$q_subj = $_REQUEST['q_subj'];
$q_msg = $_REQUEST['q_msg'];
$q_status = $_REQUEST['q_status'];
$q_type = $_REQUEST['q_type'];
$q_user_id = $_REQUEST['q_user_id'];
$q_user_type = $_REQUEST['q_user_type'];
$search = $_REQUEST['search'];
$q_string = "&q_user_id=$q_user_id&q_user_type=$q_user_type&q_to_add=$q_to_add&q_subj=$q_subj&q_to_name=$q_to_name&q_msg=$q_msg&q_status=$q_status&q_type=$q_type&search=$search";

$sql = "select count(*) as c from mail_queue  ";
$result = JB_mysql_query($sql);
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$total = $row['c'];

$sql = "select count(*) as c from mail_queue where status='queued'  ";
$result = JB_mysql_query($sql);
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$queued = $row['c'];

$sql = "select count(*) as c from mail_queue where status='sent'  ";
$result = JB_mysql_query($sql);
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$sent = $row['c'];

$sql = "select count(*) as c from mail_queue where status='error'  ";
$result = JB_mysql_query($sql);
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$error = $row['c'];

?>
<b><?php echo $total; ?></b> Total Email(s) | 
<b><?php echo $queued; ?></b> Email(s) on Queue | 
<b><?php echo $sent; ?></b> Email(s) Sent | 
<b><?php echo $error; ?></b> Email(s) Failed<br>
<input type='button' value="Refresh" onclick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?'" >
<input type='button' value="Process Queue - (Send <?php echo $JB_EMAILS_PER_BATCH;?>)" onclick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=send<?php echo $q_string; ?>'" > | <input type='button' value="Delete Sent" onclick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=delsent<?php echo $q_string; ?>'" > |  <input type='button' value="Delete Error" onclick="window.location='<?php echo $_SERVER['PHP_SELF'];?>?action=delerror<?php echo $q_string; ?>'" > | <input type='button' value="Delete All" onclick="window.location='<?php echo $_SERVER['PHP_SELF'];?>?action=delall<?php echo $q_string; ?>'" >

<br>

<?php
//$q_string.="&action=$action";

?>

<hr>
Note: Please see <a href="cron.php">this file</a> for details how run the email queue process automatically.
<hr>
<form style="margin: 0" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?search=y" method="post">
         
           <center>
         <table border="0" cellpadding="2" cellspacing="0" style="border-collapse: collapse"  id="AutoNumber2"  width="100%">
  
    <tr>
      <td width="63" bgcolor="#EDF8FC" valign="top">
      <p style="float: right;"><font size="2" face="Arial"><b>To Addr</b></font></td>
      <td width="286" bgcolor="#EDF8FC" valign="top">
      <font face="Arial">
      <input type="text" name="q_to_add" size="39" value="<?php echo jb_escape_html($q_to_add);?>" ></font></td>
      <td width="71" bgcolor="#EDF8FC" valign="top">
      <p style="float: right;"><b><font face="Arial" size="2">To Name</font></b></td>
      <td width="299" bgcolor="#EDF8FC" valign="top">
      
      <input type="text" name="q_to_name" size="28" value="<?php echo jb_escape_html($q_to_name); ?>"></td>
    </tr>
	 <tr>
      <td width="63" bgcolor="#EDF8FC" valign="top">
      <p style="float: right;"><font size="2" face="Arial"><b>Subject</b></font></td>
      <td width="286" bgcolor="#EDF8FC" valign="top">
      <font face="Arial">
      <input type="text" name="q_subj" size="39" value="<?php echo jb_escape_html($q_subj);?>" ></font></td>
      <td width="71" bgcolor="#EDF8FC" valign="top">
      <p style="float: right;"><b><font face="Arial" size="2">Message</font></b></td>
      <td width="299" bgcolor="#EDF8FC" valign="top">
      
      <input type="text" name="q_msg" size="28" value="<?php echo jb_escape_html($q_msg); ?>"></td>
    </tr>
	 <tr>
      <td width="63" bgcolor="#EDF8FC" valign="top">
      <p style="float: right;"><font size="2" face="Arial"><b>Status</b></font></td>
      <td width="286" bgcolor="#EDF8FC" valign="top">
      <font face="Arial">
	  <select name="q_status">
		<option value='' <?php if ($_REQUEST['q_status']==false) { echo ' selected '; } ?>></option>
		<option value='queued' <?php if ($_REQUEST['q_status']=='queued') { echo ' selected '; } ?>>queued</option>
		<option value='error' <?php if ($_REQUEST['q_status']=='error') { echo ' selected '; } ?>>error</option>
		<option value='sent' <?php if ($_REQUEST['q_status']=='sent') { echo ' selected '; } ?>>sent</option>
	  </select>
     </font></td>
      <td width="71" bgcolor="#EDF8FC" valign="top">
      <p style="float: right;"><b><font face="Arial" size="2">Type</font></b></td>
      <td width="299" bgcolor="#EDF8FC" valign="top">
        <select name="q_type">
		<option value='' <?php if ($_REQUEST['q_type']==false) { echo ' selected '; } ?>></option>
		<option value='1' <?php if ($_REQUEST['q_type']=='1') { echo ' selected '; } ?>>1 - Candidate Signup</option>
		<option value='2' <?php if ($_REQUEST['q_type']=='2') { echo ' selected '; } ?>>2 - Employer Signup</option>
		<option value='3' <?php if ($_REQUEST['q_type']=='3') { echo ' selected '; } ?>>3 - Forgot Pass</option>
		<option value='4' <?php if ($_REQUEST['q_type']=='4') { echo ' selected '; } ?>>4 - Request Candidate's details</option>
		<option value='44' <?php if ($_REQUEST['q_type']=='44') { echo ' selected '; } ?>>44 - Request accepted</option>
		<option value='5' <?php if ($_REQUEST['q_type']=='5') { echo ' selected '; } ?>>5 - Resume Alert</option>
		<option value='7' <?php if ($_REQUEST['q_type']=='7') { echo ' selected '; } ?>>7 - Job Alert</option>
		<option value='8' <?php if ($_REQUEST['q_type']=='8') { echo ' selected '; } ?>>8 - Admin to Employer</option>
		<option value='9' <?php if ($_REQUEST['q_type']=='8') { echo ' selected '; } ?>>9 - Admin to Candidate</option>
		<option value='10' <?php if ($_REQUEST['q_type']=='10') { echo ' selected '; } ?>>10 - Application Receipt</option>
		<option value='11' <?php if ($_REQUEST['q_type']=='11') { echo ' selected '; } ?>>11 - Employer to Candidate</option>
		<option value='12' <?php if ($_REQUEST['q_type']=='12') { echo ' selected '; } ?>>12 - Candidate to Employer (Application)</option>
		<option value='22' <?php if ($_REQUEST['q_type']=='22') { echo ' selected '; } ?>>22 - New Post Notification</option>
		<option value='30' <?php if ($_REQUEST['q_type']=='30') { echo ' selected '; } ?>>30 - News Letter</option>
		<option value='44' <?php if ($_REQUEST['q_status']=='44') { echo ' selected '; } ?>>44 - Request Granted to Emp.</option>
		<option value='46' <?php if ($_REQUEST['q_status']=='46') { echo ' selected '; } ?>>46 - Tell A friend</option>
		<option value='60' <?php if ($_REQUEST['q_status']=='60') { echo ' selected '; } ?>>60 - Posts: Confirmed Order (Bank)</option>
		<option value='61' <?php if ($_REQUEST['q_status']=='61') { echo ' selected '; } ?>>61 - Posts: Confirmed Order (Check/Money Order)</option>
		<option value='70' <?php if ($_REQUEST['q_status']=='70') { echo ' selected '; } ?>>70 - Posts: Completed Order - Thank you note</option>
		<option value='80' <?php if ($_REQUEST['q_status']=='80') { echo ' selected '; } ?>>80 - Subscr: Confirmed Order (Bank)</option>
		<option value='81' <?php if ($_REQUEST['q_status']=='81') { echo ' selected '; } ?>>81 - Subscr: Confirmed Order (Check/Money Order)</option>
		<option value='90' <?php if ($_REQUEST['q_status']=='90') { echo ' selected '; } ?>>90 - Subscr: Completed Order - Thank you note</option>
		<option value='130' <?php if ($_REQUEST['q_status']=='130') { echo ' selected '; } ?>>130 - Subscr: Expired</option>
		<option value='100' <?php if ($_REQUEST['q_status']=='100') { echo ' selected '; } ?>>100 - Member: Confirmed Order (Bank)</option>
		<option value='101' <?php if ($_REQUEST['q_status']=='101') { echo ' selected '; } ?>>101 - Member: Confirmed Order (Check/Money Order)</option>
		<option value='110' <?php if ($_REQUEST['q_status']=='110') { echo ' selected '; } ?>>110 - Member: Completed Order - Thank you note</option>
		<option value='120' <?php if ($_REQUEST['q_status']=='120') { echo ' selected '; } ?>>120 - Member: Expired</option>
		<option value='210' <?php if ($_REQUEST['q_status']=='210') { echo ' selected '; } ?>>210 - Job Post Expired</option>
		<option value='220' <?php if ($_REQUEST['q_status']=='220') { echo ' selected '; } ?>>220 - Job Post Approved</option>
		<option value='230' <?php if ($_REQUEST['q_status']=='230') { echo ' selected '; } ?>>230 - Job Post Disapproved</option>
		<option value='310' <?php if ($_REQUEST['q_status']=='310') { echo ' selected '; } ?>>310 - Admin: New Job Post</option>
		<option value='320' <?php if ($_REQUEST['q_status']=='320') { echo ' selected '; } ?>>320 - Admin: New Resume</option>
		<option value='330' <?php if ($_REQUEST['q_status']=='330') { echo ' selected '; } ?>>330 - Admin: New Invoice</option>
		<?php

		jbplug_do_callback('email_q_option_type', $A=false);

		?>

	  </select>
     
	  </td>
    </tr>
	   <tr>
      <td width="731" bgcolor="#EDF8FC" colspan="4">
      <font face="Arial"><b>
      <input type="submit" value="Find Emails" name="B1" style="float: left"><?php if ($search=='y') { ?>&nbsp; </b></font><b>[<font face="Arial"><a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">Start a New Search</a></font>]</b><?php } ?></td>
    </tr>
	</table>
<?php
//to_address to_name message subject template_id status user_id
if ($q_to_add != '') {
	$where_sql .= " `to_address` like '%$q_to_add%' "; 
	$and_sql = 'AND';
}

if ($q_to_name != '') {
	$where_sql .= "  `to_name` like '%$q_to_name%' "; 
	$and_sql = 'AND';
}

if ($q_msg != '') {
	$where_sql .= " $and_sql `message` like '%$q_msg%' "; 
	$and_sql = 'AND';
}

if ($q_subj != '') {
	$where_sql .= " $and_sql `subject` like '%$q_subj%' "; 
	$and_sql = 'AND';
}

if ($q_type != '') {
	$where_sql .= " $and_sql `template_id` like '$q_type' "; 
	$and_sql = 'AND';
}

if ($q_status !='') {
	$where_sql .= " $and_sql `status`='$q_status' ";
	$and_sql = 'AND';
}

if ($q_user_id !='') {
	$where_sql .= " $and_sql `user_id`='$q_user_id' AND `user_type`='$q_user_type' ";
	$and_sql = 'AND';
}
if ($and_sql) {
	$where_sql = " WHERE ".$where_sql;
}
$records_per_page = 40;
$offset = (int) $_REQUEST['offset'];
// this query will use the mail_date index.
$sql = "SELECT * FROM mail_queue  $where_sql order by mail_date DESC LIMIT $offset, $records_per_page  ";

$result = JB_mysql_query ($sql) or die (mysql_error());
//$count = mysql_num_rows($result);
//$row = mysql_fetch_row(jb_mysql_query("SELECT FOUND_ROWS()"));
//$count = $row[0];

$row = mysql_fetch_row(jb_mysql_query("SELECT count(*) FROM mail_queue $where_sql "));//$row[0];
$count = $row[0];
//if ($count > $records_per_page) {

//	mysql_data_seek($result, $_REQUEST['offset']);

//}
if ($count > $records_per_page)  {
	$pages = ceil($count / $records_per_page);
	$cur_page = $_REQUEST['offset'] / $records_per_page;
	$cur_page++;

	echo "<center>";
	?>
	<center><b><?php echo $count; ?> Emails returned (<?php echo $pages;?> pages) </b></center>
	<?php
	echo "Page $cur_page of $pages - ";
	$nav = JB_nav_pages_struct($result, $q_string, $count, $records_per_page);
	$LINKS = 10;
	JB_render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
	echo "</center>";

}

?>

<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" >
			<tr bgColor="#eaeaea">
				<td><b><font size="2">Date</b></font></td>
				<td><b><font size="2">Type</b></font></td>
				<td><b><font size="2">To Addr</b></font></td>
				<td><b><font size="2">To Name</b></font></td>
				<td><b><font size="2">Fr Addr</b></font></td>
				<td><b><font size="2">Fr Name</b></font></td>
				<td><b><font size="2">Subj</b></font></td>
				<td><b><font size="2">Msg</b></font></td>
				<td><b><font size="2">Html Msg</b></font></td>
				<td><b><font size="2">Att</b></font></td>
				<td><b><font size="2">Status</b></font></td>
				<td><b><font size="2">Err</b></font></td>
				<td><b><font size="2">Retry</b></font></td>
				<td><b><font size="2">Action</b></font></td>
			</tr>

<?php


$i=0;
while (($row=mysql_fetch_array($result, MYSQL_ASSOC)) && ($i<$records_per_page)) {

	$i++;

	$new_window = "onclick=\"window.open('show_email.php?mail_id=".$row['mail_id']."', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=yes,resizable=1,width=600,height=600,left = 50,top = 50');return false;\"";	
	

?>

	<tr bgColor="#ffffff">
		<td><font size="1"><?php echo JB_escape_html(JB_get_local_time($row['mail_date'])); ?></font></td>
		<td><font size="1"><?php echo JB_escape_html($row['template_id']); ?></font></td>
		<td><font size="1"><?php echo JB_escape_html($row['to_address']); ?></font></td>
		<td><font size="1"><?php echo JB_escape_html($row['to_name']); ?></font></td>
		<td><font size="1"><?php echo JB_escape_html($row['from_address']); ?></font></td>
		<td><font size="1"><?php if ($row['user_id']!='') { echo '<A href="'.$_SERVER['PHP_SELF'].'?q_user_id='.$row['user_id'].'&q_user_type='.$row['user_type'].'">';} echo JB_escape_html($row['from_name']); if ($row['user_id']!='') { echo '</A>'; }?></font></td>
		<td><font size="1"><?php echo JB_escape_html(substr($row['subject'],0, 7)); ?><a href="" <?php echo $new_window; ?>>...</a></font></td>
		<td><font size="1"><?php echo JB_escape_html(substr($row['message'],0, 7)); ?><a href="" <?php echo $new_window; ?>>...</a></font></td>
		<td><font size="1"><?php echo JB_escape_html(substr($row['html_message'],0,7)); ?><a href="" <?php echo $new_window; ?>>...</a></font></td>
		<td><font size="1"><?php echo JB_escape_html($row['attachments']); ?></font></td>
		<td><font size="2" color="<?php if ($row['status']=='sent') { echo 'green'; } ?>"><?php echo $row['status']; ?></font></td>
		<td><font size="1"><?php echo $row['error_msg']; ?></font></td>
		<td><font size="1"><?php echo $row['retry_count']; ?></font></td>
		<td><b><font size="1"><a href='email_queue.php?action=resend&mail_id=<?php echo $row['mail_id'].$q_string;?>'>Resend</a></b></font></td>
	</tr>

<?php

// 14400 86400
}

?>

</table>
<?php
if ($count > $records_per_page)  {
	$pages = ceil($count / $records_per_page);
	$cur_page = $_REQUEST['offset'] / $records_per_page;
	$cur_page++;

	echo "<center>";
	?>
	
	<?php
	echo "Page $cur_page of $pages - ";
	$nav = JB_nav_pages_struct($result, $q_string, $count, $records_per_page);
	$LINKS = 10;
	JB_render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
	echo "</center>";

}

JB_admin_footer();

?>