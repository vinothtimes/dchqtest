<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require '../config.php'; //include('../include/functions.php');
require '../include/resumes.inc.php';

JB_template_candidates_header();


$sql = "UPDATE `requests` SET `request_status`='GRANTED' WHERE `key`='".jb_escape_sql($_REQUEST['k'])."' ";
$result = JB_mysql_query($sql) or die (mysql_error());


if (JB_mysql_affected_rows()>0) {
	$label["c_permit_success"] = str_replace ("%BASE_HTTP_PATH%", JB_BASE_HTTP_PATH , $label["c_permit_success"]);
	$label["c_permit_success"] = str_replace ("%SITE_NAME%", JB_SITE_NAME , $label["c_permit_success"]);
	
	$JBMarkup->ok_msg($label["c_permit_success"]);
	
	$sql = "select employer_id, candidate_id FROM `requests` WHERE  `key`='".jb_escape_sql($_REQUEST['k'])."'";
	$result = jb_mysql_query($sql);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	JB_send_request_granted_email($row['candidate_id'], $row['employer_id']);

} else {
	$label["c_permit_weclome"] = str_replace ("%CANDIDATE_FOLDER%", JB_CANDIDATE_FOLDER , $label["c_permit_weclome"]);
	$label["c_permit_weclome"] = str_replace ("%SITE_NAME%", JB_SITE_NAME , $label["c_permit_weclome"]);
	
	echo "<br><p style='text-align:center;font-weight:bold;'>".$label["c_permit_weclome"]."</p>";
}

JB_template_candidates_footer();

?>