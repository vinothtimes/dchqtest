<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require("../config.php");

 
include('login_functions.php');
JB_process_login();

require_once ("../include/posts.inc.php");
require_once ("../include/category.inc.php");

$show_emp = (int) $_REQUEST['show_emp'];
$post_id = (int) $_REQUEST['post_id'];

if ($post_id > 0) {
	// Load the data for displaying a job post
	$JBPage = new JBJobPage($post_id); 
}

JB_template_candidates_header();

JB_display_dynamic_search_form (1);


if ($show_emp) {

	require_once ("../include/profiles.inc.php");

		
	$PrForm = &JB_get_DynamicFormObject(3);
	$PrForm->load(false, $show_emp);
		
	$company_name = JB_get_employer_company_name($show_emp);
	?>
	<br><P style="text-align:center"> <a href="<?php echo htmlentities($_SERVER['PHP_SELF'])."?$q_string&amp;offset=$offset";?>"><b><?php echo $label['index_employer_jobs'];?></b></a> -> <b><?php echo $JBMarkup->escape($company_name);?></b></p>

	<?php

	
	$PrForm->display_form('view', false);
	

}

if (!$post_id) {

	$list_mode = "ALL";
	JB_list_jobs ($list_mode);

} else {
	

	$JBPage->output('HALF');
	$JBPage->increment_hits();
	
}

JB_template_candidates_footer(); 

?>