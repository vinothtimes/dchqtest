<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
include ("../config.php");
require (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Admin -> View Cover Letter');

$app_id = $_REQUEST['app_id'];

$sql = "SELECT * FROM `applications` where app_id='".jb_escape_sql($app_id)."' ";
$result = JB_mysql_query ($sql) or die (mysql_error().$sql);
$row = mysql_fetch_array($result, MYSQL_ASSOC);

echo JB_escape_html($row['cover_letter']);

?>
<center>
<input type="button" value="Close" onclick="window.close(); return false">
</center>
<?php

JB_admin_footer();

?>