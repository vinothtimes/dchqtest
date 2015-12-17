<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

require ("../config.php");

include('login_functions.php');
JB_process_login(); 

JBPLUG_do_callback('can_logout', $A = false);

$now = (gmdate("Y-m-d H:i:s"));

$sql = "UPDATE `users` SET `logout_date`='$now' WHERE `Username`='".jb_escape_sql($_SESSION['JB_Username'])."'";

JB_mysql_query($sql) or die (mysql_error());

// clear the session table
$sql = "DELETE FROM `jb_sessions` WHERE session_id='".jb_escape_sql(session_id())."' ";
JB_mysql_query($sql) or die ($sql.mysql_error());

unset($_SESSION['JB_ID']);
unset($_SESSION['JB_Domain']);
unset($_SESSION['JB_FirstName']);
unset($_SESSION['JB_LastName']);
unset($_SESSION['JB_Username']);
unset($_SESSION['Rank']);
unset($_SESSION['JB_Base']);

JB_template_candidates_outside_header($page_title);
echo '<p style="text-align:center">'.$label["c_logout_msg"].'</p>';
JB_template_candidates_outside_footer();

?>