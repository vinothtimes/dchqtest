<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require("../config.php");

require (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Admin -> Whois Online');

?>
<b>[Who's Online]</b>

<span style="background-color: #F2F2F2; border-style:outset; padding: 5px;"><a href="paypal_log.php">Payment Log</a></span>
<span style="background-color: #FFFFCC; border-style:outset; padding: 5px;"><a href="whois_online.php">Who's Online</a></span>

<hr>
<h3>Who's Online?</h3>
<p>
(Users get automatically logged out after <?php echo ini_get ("session.gc_maxlifetime")/60;?> minutes of inactivity.)</p>
<?php

#clean employers' expired sessions
$session_duration = ini_get ("session.gc_maxlifetime");
$now = (gmdate("Y-m-d H:i:s"));
$sql = "UPDATE `employers` SET `logout_date`='$now' WHERE UNIX_TIMESTAMP(DATE_SUB('$now', INTERVAL $session_duration SECOND)) > UNIX_TIMESTAMP(last_request_time) AND (`logout_date` ='0000-00-00 00:00:00')";
JB_mysql_query($sql) or die ($sql.mysql_error());
# clean candidates' expired sessions
$now = (gmdate("Y-m-d H:i:s"));
$sql = "UPDATE `users` SET `logout_date`='$now' WHERE UNIX_TIMESTAMP(DATE_SUB('$now', INTERVAL $session_duration SECOND)) > UNIX_TIMESTAMP(last_request_time) AND (`logout_date` ='0000-00-00 00:00:00')";
JB_mysql_query($sql) or die ($sql.mysql_error());

$sql = "SELECT * FROM `users` where logout_date='0000-00-00 00:00:00' ";
$users_result = JB_mysql_query($sql);
$users_online = mysql_num_rows($users_result);

$sql = "SELECT * FROM `employers` where logout_date='0000-00-00 00:00:00' ";
$employers_result = JB_mysql_query($sql);
$employers_online = mysql_num_rows($employers_result);



?>

Candidates Currently Online: <?php echo $users_online; ?>

<?php

if ($users_online > 0) {

?>

<table cellSpacing="1" cellPadding="3" style="margin: 0 auto; background-color: #d9d9d9; width:100%; border:0px" >

  <tr bgColor="#eaeaea">
	<td><b><font face="Arial" size="2">Username</font></b></td>
    <td><b><font face="Arial" size="2">Name</font></b></td>
    <td><b><font face="Arial" size="2">Email</font></b></td>
	<td><b><font face="Arial" size="2">Logins</font></b></td>
	<td><b><font face="Arial" size="2">Applications</font></b></td>
	<td><b><font face="Arial" size="2">Resume</font></b></td>
         
  </tr>

  <?php

  

  while ($row = mysql_fetch_array($users_result)) {


  ?>

<tr onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);" bgColor="#ffffff">

  <td ><font face="Arial" size="2"><a href="candidates.php?action=edit&user_id=<?php echo $row['ID'];?>"><?php echo JB_escape_html($row['Username']); ?></a></font></td>
    <td><font face="Arial" size="2"><?php echo JB_escape_html(jb_get_formatted_name($row['FirstName'], $row['LastName']));?></font></td>
    <td><font face="Arial" size="2"><?php echo JB_escape_html($row['Email'])?></font></td>
    <td><font face="Arial" size="2"><?php echo $row['login_count']; ?></font></td>
	<td><font face="Arial" size="2"><?php 

	  $sql = "SELECT * FROM `applications` WHERE `user_id`='".jb_escape_sql($row['ID'])."' ";
		$result2 = JB_mysql_query($sql) or die(mysql_error());
		$count = mysql_num_rows($result2);
		if ($count > 0) {
			echo $count."";
		} else {
			echo "N";
		}
	  
  ?></font></td>
	<td><font face="Arial" size="2"><?php

		 $sql = "SELECT * FROM `resumes_table` WHERE `user_id`='".jb_escape_sql($row['ID'])."' ";
		$result3 = JB_mysql_query($sql) or die(mysql_error());
		$count = mysql_num_rows($result3);
		$row3 = mysql_fetch_array($result3);
		if ($count > 0) {
			
			echo "<a href='resumes.php?resume_id=".$row3['resume_id']."'>Yes<a>";
		} else {
			echo "No";
		}
	  
  
	?></font></td>
	
</tr>

  <?php

  }
  


  ?>

  </table>

<?php

}

?>

<hr>




Employers Currently Online: <?php echo $employers_online; ?>

<?php

if ($employers_online > 0) {

?>

<table cellSpacing="1" cellPadding="3" style="margin: 0 auto; background-color: #d9d9d9; width:100%; border:0px" >

  <tr bgColor="#eaeaea">
	<td><b><font face="Arial" size="2">Username</font></b></td>
    <td><b><font face="Arial" size="2">Name</font></b></td>
    
    <td><b><font face="Arial" size="2">Email</font></b></td>
	<td><b><font face="Arial" size="2">Logins</font></b></td>
	<td><b><font face="Arial" size="2">Posts</font></b></td>
         
  </tr>

  <?php

  

  while ($row = mysql_fetch_array($employers_result)) {


  ?>

<tr onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);" bgColor="#ffffff">

  <td ><font face="Arial" size="2"><a href="employers.php?action=edit&user_id=<?php echo $row['ID'];?>"><?php echo JB_escape_html($row['Username']); ?></a></font></td>
    <td><font face="Arial" size="2"><?php echo JB_escape_html(jb_get_formatted_name($row['FirstName'], $row['LastName'])); ?></font></td>
    
    <td><font face="Arial" size="2"><?php echo JB_escape_html($row['Email']); ?></font></td>
	<td><font face="Arial" size="2"><?php echo $row['login_count']; ?></font></td>
	<td><font face="Arial" size="2"><?php 

		$sql = "SELECT * FROM `posts_table` WHERE `user_id`='".jb_escape_sql($row['ID'])."' ";
		$result2 = JB_mysql_query($sql);
		$count = mysql_num_rows($result2);
		if ($count > 0) {
			echo "<a href='posts.php?show_emp=".$row['ID']."'>".$count."</a>";
		} else {
			echo "N";
		}

	
	?></font></td>
</tr>

  <?php

  }
  


  ?>

  </table>

<?php

}

?>
<hr>
<b>[Total Online List, including Guests]</b> 



<?php


 $sql  = "select * FROM jb_sessions order by last_request_time desc";
	
 $result = JB_mysql_query($sql) or die (mysql_error());

?>
(Updated every 1 minute)
<table cellSpacing="1" cellPadding="3" style="margin: 0 auto; background-color: #d9d9d9; width:100%; border:0px" >

  <tr bgColor="#eaeaea">
    <td><b><font face="Arial" size="2">Session Id</font></b></td>
	<td><b><font face="Arial" size="2">Last request time</font></b></td>
	<td><b><font face="Arial" size="2">Guest type</font></b></td>
    <td><b><font face="Arial" size="2">User Id</font></b></td>
    <td><b><font face="Arial" size="2">Remote Address</font></b></td>
	<td><b><font face="Arial" size="2">User-agent</font></b></td>
	<td><b><font face="Arial" size="2">Referer [sic]</font></b></td>
         
  </tr>

  <?php

   while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

	   ?>

	   <tr onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);" bgColor="#ffffff">

<td><font face="Arial" size="2"><?php echo JB_escape_html($row['session_id']); ?></font></td>
<td><font face="Arial" size="2"><?php echo JB_get_local_time($row['last_request_time']); ?></font></td>
<td><font face="Arial" size="2"><?php echo ($row['domain']); ?></font></td>
<td><font face="Arial" size="2"><?php echo ($row['id']); ?></font></td>
<td><font face="Arial" size="2"><?php echo JB_escape_html($row['remote_addr']); ?></font></td>
<td><font face="Arial" size="2"><?php echo JB_escape_html($row['user_agent']); ?></font></td>
<td><font face="Arial" size="2"><?php echo JB_escape_html($row['http_referer']); ?></font></td>
</tr>

	   <?php


   }


?></table>

<?php

JB_admin_footer();
?>