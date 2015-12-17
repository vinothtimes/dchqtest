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

JBPLUG_do_callback('emp_logout', $A = false);


$now = (gmdate("Y-m-d H:i:s"));
$sql = "UPDATE `employers` SET `logout_date`='$now' WHERE `Username`='".jb_escape_sql($_SESSION['JB_Username'])."'";
      //echo $sql;
 JB_mysql_query($sql);
      
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

$page_title = JB_SITE_NAME;

JB_template_employers_outside_header($page_title);

?><h3 style="text-align: center;"><?php echo $label['employer_logout_ok']; ?></h3> 

<p style="text-align: center;">
<a href="<?php echo jb_escape_html(JB_BASE_HTTP_PATH); ?>"><?php 
	  $label["employer_logout_home"] = str_replace ("%SITE_NAME%", jb_escape_html(JB_SITE_NAME) , $label["employer_logout_home"]);
	  echo $label['employer_logout_home']; ?></a>

</p>
<?php
JB_template_employers_outside_footer();
?>