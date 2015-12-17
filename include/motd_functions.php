<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
function JB_save_motd($type, $title, $message, $display) {

	if (($type!='E') && ($type!='U')) {return false; }

	
	$time = gmdate("Y-m-d H:i:s");

	$sql = "REPLACE INTO `motd` (`motd_type`, `motd_lang`, `motd_message`, `motd_title`, `motd_date_updated`) VALUES ('$type', '".jb_escape_sql($_SESSION['LANG'])."', '".jb_escape_sql($message)."', '".jb_escape_sql($title)."', '$time') ";
	JB_mysql_query($sql) or die(mysql_error());



	$sql = "REPLACE INTO jb_variables (`key`, `val`) VALUES('MOTD_".jb_escape_sql("$type")."_DISPLAY', '".jb_escape_sql($display)."') ";
	JB_mysql_query($sql) or die(mysql_error());



}

function JB_load_motd($type) {
	if (($type!='E') && ($type!='U')) {return false; }

	$data = array();

	$sql = "SELECT * FROM `motd` where `motd_type` = '".jb_escape_sql($type)."' AND `motd_lang`='".jb_escape_sql($_SESSION['LANG'])."' ";
	$result = @JB_mysql_query($sql) or $DB_ERROR = mysql_error();
	$row = @mysql_fetch_array($result, MYSQL_ASSOC);
	$data['title']=$row['motd_title'];

	
	$data['message']=$row['motd_message'];

	
	$data['updated']=$row['motd_date_updated'];

	$sql = "SELECT * FROM `jb_variables` where `key` = 'MOTD_".jb_escape_sql($type)."_DISPLAY' ";
	$result = @JB_mysql_query($sql) or $DB_ERROR = mysql_error();
	$row = @mysql_fetch_array($result, MYSQL_ASSOC);
	$data['display']=$row['val'];

	return $data;


}

function JB_display_motd($type, $width=100) {
	if (($type!='E') && ($type!='U')) {return false; }
	$data = JB_load_motd($type);
	if ($data['display']=='YES') {
		JB_render_box_top($width,  $data['title']);
		echo $data['message'];
		JB_render_box_bottom();
		return true;
	}
	return false;


}

?>