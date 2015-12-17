<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require("../config.php");
require (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Admin -> Online');

?>
<b>[Online Guest List]</b> 

<hr>

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