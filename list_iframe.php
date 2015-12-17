<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
define ('NO_HOUSE_KEEPING', true);
// If you move this file, you
// may need change to to the location of your JB config.php, 
// include/posts.inc.php
require (dirname(__FILE__)."/config.php"); 
require_once (dirname(__FILE__)."/include/posts.inc.php");

$LIM = JB_get_JBIframeListMarkupObject();

echo $JBMarkup->get_doctype();

$JBMarkup->markup_open();
$JBMarkup->head_open();

$JBMarkup->charset_meta_tag();
$JBMarkup->no_robots_meta_tag();


$JBMarkup->head_close();

$new = $_REQUEST['new'];
$color = $_REQUEST['color'];
$bgcolor = $_REQUEST['bgcolor'];
$linkcolor = $_REQUEST['linkcolor'];
$limit = $_REQUEST['limit'];

if (!is_numeric($color)) {
   $color = "000000";
}

if (!is_numeric($bgcolor)) {
   $bgcolor = "ffffff";
}
if (!is_numeric($linkcolor)) {
  $linkcolor = "0000ff";
}

if (!is_numeric($limit)) {
   $limit = 5;
}
$new='yes';


$JBMarkup->body_open('style="color: #'.$color.'; background-color:#'.$bgcolor.'; "');

$now = (gmdate("Y-m-d H:i:s"));

$sql = "select *, `post_date` AS DAY, DATE_FORMAT(`post_date`, '%a, %d %b %Y %H:%i:%s $gmt_diff') AS formatted_date from posts_table WHERE  `approved`='Y' AND `expired`='N' ORDER BY `post_date` DESC LIMIT ".jb_escape_sql($limit)."";

$result = JB_mysql_query($sql) or die("here:".mysql_error());

// Change HTML below to custimize the appearnace of the list


$LIM->list_start();

// Heading column names

$LIM->list_head_open();
$LIM->list_head_column('Date');

$LIM->list_head_column('Job Post Title');
$LIM->list_head_close();


$PostingForm = &JB_get_DynamicFormObject(1);


while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	
		
	$PostingForm->set_values($row);
	
	
	$TITLE = $PostingForm->get_template_value("TITLE");
	$DATE = JB_get_formatted_date($PostingForm->get_template_value ("DATE"));

	$LIM->list_item_open();

	$LIM->list_cell_open();
	$LIM->list_cell_data($DATE);
	$LIM->list_cell_close();

	$LIM->list_cell_open();

	$link = $LIM->get_link(JB_BASE_HTTP_PATH.'index.php?post_id='.$row['post_id'], $TITLE, $new, $linkcolor);

	$LIM->list_cell_data($link);
	$LIM->list_cell_close();
	

	$LIM->list_item_close();
}


$LIM->list_end();

$JBMarkup->body_close();
$JBMarkup->markup_close();