<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require('../config.php');

include('login_functions.php');
JB_process_login(); 

require_once ("../include/posts.inc.php");
require_once ("../include/category.inc.php");

$post_id = (int) $_REQUEST['post_id'];
$cat_id = (int) $_REQUEST['cat'];
$show_emp = (int) $_REQUEST['show_emp'];

if ($post_id > 0) {
	// Load the data for displaying a job post
	$JBPage = new JBJobPage($post_id); 
}

JB_template_candidates_header();

$JB_CAT_COLS = 3;

?>
<div style="padding-bottom:3.5em; text-align:left">
	<div style="float:left; ">
	<span class="category_name"><?php echo $label['root_category_link']; ?> <?php echo jb_escape_html(JB_getCatName($cat_id));?></span><br>
	<span class="category_path"><?php echo JB_getPath_templated($cat_id);?></span>
	</div>
	
		<div style="float: right;">
		<a href="index.php"><a href="browse.php"><?php echo $label['c_back2top'];?></a></a>
		<?php

		if (JB_CAT_RSS_SWITCH=='YES') {

			$cat_name = trim(JB_getCatName($cat_id));

			if ($cat_name) {
		?>
				<p>

				<a href="<?php echo JB_BASE_HTTP_PATH."rss.php?cat=".jb_escape_html($cat_id); ?>"><img alt="RSS" src="<?php echo JB_THEME_URL.'images/rss_cat.png'?>" border="0" ></a> <?php
				
				$label['rss_subscribe'] = str_replace ('%RSS_LINK%', JB_BASE_HTTP_PATH."rss.php?cat=".jb_escape_html($cat_id), $label['rss_subscribe']);
				$label['rss_subscribe'] = str_replace ('%CATEGORY_NAME%', jb_escape_html($cat_name), $label['rss_subscribe']);
				echo $label['rss_subscribe'];

				
				?>
				</p>
				<?php
			}
		}

		?>
		</div>
		<?php
	
	?>
</div>
<div class="category_index" >

<?php

$categories = JB_getCatStruct($cat_id, $_SESSION["LANG"], 1);
JB_display_categories($categories, $JB_CAT_COLS);

?>

</div>


<?php


if ($show_emp != '') {

	require_once ("../include/profiles.inc.php");

	
	$ProfileForm = &JB_get_DynamicFormObject(3);
	$ProfileForm->load(false, $show_emp); // in this case, the profile is fetched by user_id
	
	
	$company_name = JB_get_employer_company_name($show_emp);
	?>
	<br><P style="text-align:center"> <a href="<?php echo htmlentities($_SERVER['PHP_SELF'])."?$q_string&amp;offset=$offset";?>"><b><?php echo $label['index_employer_jobs'];?></b></a> -&gt; <b><?php echo $JBMarkup->escape($company_name);?></b></p>

	<?php

	
	$ProfileForm->display_form('view', false);
	

	
}
if ($post_id=='') {
	if ($cat_id!='') {
		$list_mode = "BY_CATEGORY";
	} else {
		$list_mode = "ALL";
	}
	
	JB_list_jobs ($list_mode);
} else {
	
	 
	$JBPage->output('HALF');
	$JBPage->increment_hits();

}

JB_template_candidates_footer();

?>