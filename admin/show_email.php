<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require ("../config.php");
require (dirname(__FILE__)."/admin_common.php");

$sql ="SELECT * FROM mail_queue where mail_id='".jb_escape_sql($_REQUEST['mail_id'])."'";
$result = JB_mysql_query($sql);
$row = mysql_fetch_array($result, MYSQL_ASSOC);

JB_admin_header('Admin -> Show Email');


?>
<table border="1" id="table1" width="600">
	<tr>
		<td width="118">Template ID:</td>
		<td width="322"><?php echo $row['template_id']; ?></td>
	</tr>
	<tr>
		<td width="118">To Name:</td>
		<td width="322"><?php echo $row['to_name']; ?></td>
	</tr>
	<tr>
		<td width="118">To Address:</td>
		<td width="322"><?php echo JB_escape_html($row['to_address']); ?></td>
	</tr>
	<tr>
		<td width="118">From Name:</td>
		<td width="322"><?php echo JB_escape_html($row['from_name']); ?></td>
	</tr>
	<tr>
		<td width="118">From Address:</td>
		<td width="322"><?php echo JB_escape_html($row['from_address']); ?></td>
	</tr>
	<tr>
		<td width="118">Subject:</td>
		<td width="322"><?php echo JB_escape_html($row['subject']); ?></td>
	</tr>
	<tr>
		<td width="118">Message (text)</td>
		<td width="322"></td>
	</tr>
	<tr>
		<td colspan="2"><pre><?php echo JB_escape_html($row['message']); ?></pre></td>
	</tr>
	<tr>
		<td width="118">Message (HTML)</td>
		<td width="322"></td>
	</tr>
	<tr>
		<td colspan="2"><?php echo JB_clean_str($row['html_message']); ?></td>
	</tr>
	<tr>
		<td width="118">Attachments</td>
		<td width="322"><?php echo JB_escape_html($row['att1_name']); ?><br>
		<?php echo JB_escape_html($row['att2_name']); ?><br>
		<?php echo JB_escape_html($row['att3_name']); ?><br>
		</td>
	</tr>
</table>

<?php

JB_admin_footer();

?>

