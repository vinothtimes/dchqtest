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

JB_process_login(); // make sure that the candidate is logged in

$offset = (int) $_REQUEST['offset']; // if the applications span more than one page
$post_id = (int) $_REQUEST['post_id']; // if listing application by job posting
$apps = jb_int_array($_REQUEST['apps']); // array of application ids selected

if ($post_id > 0) {
	// Load the data for displaying a job post
	$JBPage = new JBJobPage($post_id); 
}

JB_template_candidates_header();

$ALM = &JB_get_ListMarkupObject('JBAppListMarkup');
$ALM->set_list_mode('CANDIDATE');
// print the Javascript code for confirming button press & selecting checkboxes



if ($_REQUEST['delete'] != '') {
	
	$user_id = (int) $_SESSION['JB_ID'];
	for ($i=0; $i < sizeof($apps); $i++) {

		$sql = "DELETE FROM `applications` WHERE `app_id`='".jb_escape_sql($apps[$i])."' AND `user_id`='".jb_escape_sql($user_id)."'";		
		$result = JB_mysql_query ($sql) or die (mysql_error());

		
	}
	if (sizeof($apps)>0) {
		$JBMarkup->ok_msg($label['c_app_deleted']);
	} else {
		$JBMarkup->error_msg($label['c_app_no_select']);
	}


}

JB_render_box_top(99, $label['c_app_head']);
if ($post_id != '') {

	$JBPage->output('HALF');
	$JBPage->increment_hits();

} else {

	?>
	<p>
	<?php 
	$label["c_app_intro"] = str_replace ("%POSTS_DISPLAY_DAYS%", JB_POSTS_DISPLAY_DAYS , $label["c_app_intro"]);
		echo $label["c_app_intro"];
	?></p>
	<?php
	$now = (gmdate("Y-m-d H:i:s"));
	$sql = "SELECT * FROM applications WHERE  user_id='".jb_escape_sql($_SESSION['JB_ID'])."' AND DATE_SUB('$now',INTERVAL ".jb_escape_sql(JB_POSTS_DISPLAY_DAYS)." DAY) <= `app_date` ORDER BY `app_date` DESC ";

	$result = JB_mysql_query($sql) or die (mysql_error());
	$count = mysql_num_rows($result);
	$records_per_page = 10;
	if ($count > $records_per_page) {
		mysql_data_seek($result, $offset);
	}

	if (mysql_num_rows($result) >0 ) {

		if ($count > $records_per_page)  {
			$ALM->nav_pages_start();
			$nav = JB_nav_pages_struct($result, $q_string, $count, $records_per_page);
			$LINKS = 10;
			JB_render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
			$ALM->nav_pages_end();
		}

		$row['formatted_date'] = JB_get_formatted_date($row['app_date']);
		
		// set the column span (by default, the list is using TABLE)
		$COLSPAN = 6;
		JBPLUG_do_callback('can_apply_list_action_colspan', $COLSPAN); // a plugin can also set the colspan
		$ALM->set_colspan($COLSPAN);
		
		// start the list
		$ALM->open_form();
		$ALM->list_start('joblist'); // name of the css id as the argument
		$ALM->candidate_list_controls();
		
		$ALM->list_head_open();

		$ALM->list_head_candidate_action();
		
		$ALM->list_head_column($label["c_app_date"]);
		$ALM->list_head_column($label["c_app_title"]);
		$ALM->list_head_column($label["c_app_location"]);
		$ALM->list_head_column($label["c_app_advertisor"]);
		$ALM->list_head_column($label["c_app_email"]);

		JBPLUG_do_callback('can_apply_list_header_column', $result); 

		$ALM->list_head_close();

		$i=0;
		while (($row = mysql_fetch_array($result, MYSQL_ASSOC)) && ($i<$records_per_page)) {
			$ALM->set_values($row);
			$i++;

			$ALM->list_item_open('standard');
			
			$ALM->list_data_candidate_action();
			$ALM->list_cell_open(); echo JB_get_formatted_date(JB_get_local_time($row['app_date'])); $ALM->list_cell_close();
			$ALM->list_cell_open(); echo $ALM->get_open_link('apps.php?post_id='.$row['post_id'], $extra_attr);  echo JB_escape_html($row['data1']); echo $ALM->get_close_link(); $ALM->list_cell_close();
			$ALM->list_cell_open();  echo JB_escape_html($row['data2']); $ALM->list_cell_close();	
			$ALM->list_cell_open(); echo JB_escape_html($row['employer_name']); $ALM->list_cell_close();
			$ALM->list_cell_open(); echo JB_escape_html($row['data3']); $ALM->list_cell_close();

			JBPLUG_do_callback('can_apply_list_data_columns', $result);
			
			$ALM->list_item_close();

			// print the cover letter
			$ALM->list_item_open('standard');
			$ALM->cover_letter($label["c_app_cover_letter"]);
			$ALM->list_item_close();

		}

		

		$ALM->list_end();
		$ALM->close_form();
		
		if ($count > $records_per_page)  {
			$ALM->nav_pages_start();
			
			$nav = JB_nav_pages_struct($result, $q_string, $count, $records_per_page);
			$LINKS = 10;
			JB_render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
			$ALM->nav_pages_end();
		}
	} else {
			?>
			<span ><?php echo $label["c_app_no_apps"];?></span>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
		<?php

	}
}

JB_render_box_bottom();

?>
      
<p>&nbsp;</p>

<?php

JB_template_candidates_footer();

?>