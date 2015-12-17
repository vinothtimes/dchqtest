<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################


$timestart = microtime(); // used for analyzing the script's speed
//ob_start(); // buffer on

require (dirname(__FILE__)."/config.php");
require_once (dirname(__FILE__)."/include/posts.inc.php");


/*

index.php is the main page. It displays the home page.
When an employer link is clicked, it displays the employers profile and all jobs posted by that employer
When a category is clicked, it displays a categoy sub-tree and all jobs posted to that branch.
When a user does a search for the posts, or uses the <-Prev Next-> links
See the JBPages class in include/classes/pages.php for more info

Note - All language strings are available in the $label array. To edit the language strings,
Please use the Language Editing tool in the Admin (Languages page)


*/


###################
# Mod Rewrite
// process mod_rewrite for categories

if (isset($_REQUEST['cat_name']) && ($_REQUEST['cat_name']!='')) {
	
	$_REQUEST['cat'] = JB_get_cat_id_from_url($_REQUEST['cat_name']);
}

// process mod_rewrite for job posts
if (isset($_REQUEST['post_permalink'])) {
	JB_process_job_post_permalink();
}

// process mod_rewrite for employer profiles

if ((JB_PRO_MOD_REWRITE=='YES') && (isset($_REQUEST['show_emp']))) {
	JB_process_emp_permalink();
}

// proces urls with page numbers
if (isset($_REQUEST['job_page_link'])) {
	$_REQUEST['offset'] = (JB_POSTS_PER_PAGE*$_REQUEST['job_page_link'])-JB_POSTS_PER_PAGE;
}


# End Mod Rewrite
#####################

$JBPage = JB_page_init(); // calling this function will set the globals:
			//$SEARCH_PAGE, $EMPLOYER_PAGE, $CATEGORY_PAGE, $PREMIUM_LIST, 
			//$JOB_LIST_PAGE, $JB_HOME_PAGE, $JOB_PAGE

JB_template_index_header();


$JBPage->output();


JB_template_index_footer();



$timeend = microtime();
$diff = JB_get_time_diff();
//echo "<br><br><small><small>script generation took $diff s </small></small>";
//echo "<small>".JB_get_time_diff()." s </small>";
# used to analyze the scripts speed
//echo "queries:".$jb_query_c;
//ob_end_flush();

?>