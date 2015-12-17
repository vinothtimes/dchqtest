<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require("../config.php");?>
<?php include('login_functions.php'); ?>
<?php JB_process_login(); ?>
<?php JB_template_candidates_header(); ?>
<?php include('../include/motd_functions.php'); ?>

<?php

$sql = "SELECT * FROM `users` WHERE `ID`='".jb_escape_sql($_SESSION['JB_ID'])."'";
$result = JB_mysql_query($sql);
$row = mysql_fetch_array($result, MYSQL_ASSOC);


$label["c_index_greeting"] = str_replace ("%SITE_NAME%", JB_escape_html(JB_SITE_NAME) , $label["c_index_greeting"]);
$label["c_index_greeting"] = str_replace("%USERNAME%", JB_escape_html($_SESSION['JB_Username']), $label["c_index_greeting"]);
$label["c_index_greeting"] = str_replace("%FIRST_NAME%", JB_escape_html($row['FirstName']), $label["c_index_greeting"]);
$label["c_index_greeting"] = str_replace("%LAST_NAME%", JB_escape_html($row['LastName']), $label["c_index_greeting"]);
?>
<h3 style="text-align:center"><?php echo $label["c_index_greeting"]; ?></h3>
<?php

JBPLUG_do_callback('candidates_index_top', $A = false); 

if (JB_display_motd('U', 80)) { echo '<br>';}

$sql = "SELECT `hits`, `status` FROM `resumes_table` WHERE `user_id`='".jb_escape_sql($_SESSION['JB_ID'])."'";
$result = JB_mysql_query($sql);
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$count = $row['hits'];


if ($count != '') {
	if ($row['status']=='ACT') {
		$str = $label["c_index_resume_act"];
	} else {
		$str = $label["c_index_resume_sus"];
	}
	$label["c_index_views"] = str_replace("%COUNT%", $count, $label["c_index_views"]);
	$str = '<p style="text-align:center">'.$str.' '.$label["c_index_views"].'</p>';
	
	JB_display_info_box ($label["c_index_status"], $str, 80);

?>


<?php
}

if (mysql_num_rows($result)==0) {
   JB_display_info_box ("", "<p align='center'>".$label["c_index_no_resume"]."</p>", 80);
}
?>
<p>&nbsp;

					
      <?php JB_render_box_top(80, $label['c_index_menu']);?>
        <p><?php echo $label["c_index_edit"]; ?> </p>
        <p><?php echo $label["c_index_view"]; ?></p>
        <p><?php echo $label["c_index_jobs"]; ?></p>
		<p><?php echo $label["c_index_alerts"]; ?></p>
        <p><?php echo $label["c_index_manage"]; ?></p>
      
			<?php

JB_render_box_bottom();
			?>


<p>&nbsp;</p>


<?php JB_template_candidates_footer(); ?>