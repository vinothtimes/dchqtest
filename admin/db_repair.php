<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";
require (dirname(__FILE__)."/admin_common.php");
require ("../include/xml_feed_functions.php");

ini_set('max_execution_time', 2000);

JB_admin_header('Admin -> DB Repair');

?>

<b>[Database Tools]</b> 
	<span style="background-color: <?php  echo "#F2F2F2";  ?>; border-style:outset; padding:5px; "><a href="dbtools.php">Indexing</a></span>
	<span style="background-color: <?php  echo "#FFFFCC";  ?>; border-style:outset; padding:5px; "><a href="db_repair.php">Repair</a></span>
<hr>
Here you can check your tables for any errors. <br>
<input type="button" value="Run Check" onclick="window.location='db_repair.php?run=1'">
<table  border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9">
<tr bgColor="#eaeaea">
<td><b>Table Name</b></td>
<td><b>Result</b></td>
</tr>
<?php

$sql = "show tables";
$result = JB_mysql_query($sql);
while ($row = mysql_fetch_array($result)) {

?>
<tr bgcolor='#ffffff'>
<td><?php echo $row[0];?></td>
<td><?php
if ($_REQUEST['run']!='') {
	$sql = "REPAIR TABLE ".jb_escape_sql($row[0])." ";
	$result2 = JB_mysql_query($sql);
	if ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
		echo $row2['Msg_text'];
	}
}
?></td>
</tr>
<?php


}

?>
</table>
<?php

JB_admin_footer();

?>