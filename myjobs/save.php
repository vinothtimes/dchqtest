<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require("../config.php");
require_once('../include/posts.inc.php'); 
require_once ("../include/category.inc.php");

include('login_functions.php');

JB_process_login();

$post_id = (int) $_REQUEST['post_id'];
$employer_id = (int) $_REQUEST['show_emp'];

if ($post_id > 0) {
	// Load the data for displaying a job post
	$JBPage = new JBJobPage($post_id); 
}



JB_template_candidates_header(); 


$post_id = (int) $_REQUEST['post_id'];
$action = jb_alpha($_REQUEST['action']);
$posts = jb_int_array($_REQUEST['posts']);



if ($_REQUEST['delete'] != "") {
	
	$user_id = $_SESSION['JB_ID'];
	for ($i=0; $i < sizeof($posts); $i++) {

		$sql = "DELETE FROM `saved_jobs` WHERE `post_id`='".jb_escape_sql($posts[$i])."' AND `user_id`='".jb_escape_sql($user_id)."'";
		$result = JB_mysql_query ($sql) or die (mysql_error());

	}
	$label['save_job_deleted'] = str_replace('%COUNT%', jb_mysql_affected_rows(), $label['save_job_deleted']);

	$JBMarkup->ok_msg($label['save_job_deleted']);


}

JB_render_box_top(99, $label['c_save_my_jobs']);

if (($_SESSION['SAVE'] != '') || ($action=='save')) {

	if ($_SESSION['SAVE'] !='' ) {$post_id = $_SESSION['SAVE'];}
	$now = (gmdate("Y-m-d H:i:s"));
	$sql = "REPLACE INTO `saved_jobs` (`user_id`, `post_id`, `save_date`) VALUES ('".$_SESSION['JB_ID']."','".jb_escape_sql($post_id)."', '$now') ";
	JB_mysql_query ($sql) or die (mysql_error());
	$_SESSION['SAVE'] = '';

	$label["c_save_postid"] = str_replace ("%POST_ID%", $post_id, $label["c_save_postid"]);
	
	$JBMarkup->ok_msg($label["c_save_postid"]);
	

}



	if ($employer_id) {

		require_once ("../include/profiles.inc.php");

			
		$PrForm = &JB_get_DynamicFormObject(3);
		$PrForm->load(false, $show_emp);
			
		$company_name = JB_get_employer_company_name($employer_id);
		?>
		<br><P style="text-align:center"> <a href="<?php echo htmlentities($_SERVER['PHP_SELF'])."?$q_string&amp;offset=$offset";?>"><b><?php echo $label['index_employer_jobs'];?></b></a> -> <b><?php echo $JBMarkup->escape($company_name);?></b></p>

		<?php

		
		$PrForm->display_form('view', false);
		
		JB_list_jobs ('BY_EMPLOYER');

	}


elseif (($post_id > 0) && ($action != "save")) {

	
	$JBPage->output('HALF');
	$JBPage->increment_hits();

} else {

	?>
		<p><?php echo $label["c_save_intro"]; ?></p>
	<?php
	
	$count = JB_list_jobs ("SAVED");

	if ($count == 0) {
		?>
		<span><?php echo $label["c_save_notfound"];?></span>
		<?php
	}

}
JB_render_box_bottom();

JB_template_candidates_footer();

?>