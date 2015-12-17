<?php 

###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require ('../config.php');
require_once ('../include/skill_matrix_functions.php');

require_once (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Admin -> Build Matrix');

?>
<form method="post">
<?php

if ($_REQUEST['submit']!='') {

	$sql = "REPLACE INTO skill_matrix (matrix_id, field_id, row_count) VALUES ('".jb_escape_sql($_REQUEST['field_id'])."', '".jb_escape_sql($_REQUEST['field_id'])."', '".jb_escape_sql($_REQUEST['row_count'])."') ";
	
	JB_mysql_query ($sql) or die (mysql_error());

}

$sql = "Select * from skill_matrix WHERE field_id='".jb_escape_sql($_REQUEST['field_id'])."' "; 
$result = JB_mysql_query ($sql) or die (mysql_error());
$row = mysql_fetch_array($result, MYSQL_ASSOC);

	
?>

	<b>Number of rows:</b> <input type="text" size="3" name="row_count" value="<?php echo $row['row_count']; ?>">
	<input type="hidden" name="matrix_id" value="<?php echo jb_escape_html($row['matrix_id']); ?>">
	<input type="hidden" name="field_id" value="<?php echo jb_escape_html($_REQUEST['field_id']); ?>">
	<input type="submit" name="submit" value="Save Changes"> (Note: The optimal number of rows is 4, more rows might require more CPU for searching)
	</form>

	<p>&nbsp;</p>
<center><input type="button" name="" value="Close" onclick="window.opener.location.reload();window.close()"></center>

<hr>
<h3>Preview of the Skill Matrix:</h3>
<?php
echo @JB_display_matrix ($_REQUEST['field_id']);

JB_admin_footer();

?>
