<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";
include('login_functions.php');

require_once('../include/posts.inc.php'); 
require_once('../include/category.inc.php');

JB_process_login();
JB_template_employers_header(); 

echo JBEmployer::JB_get_special_offer_msg();



if (($_REQUEST['action']=='delete') || $_REQUEST['delete']!='') {

	$_REQUEST['post_id'] = (int) $_REQUEST['post_id'];
	if ($_REQUEST['post_id']) {
		$_REQUEST['posts'][] = $_REQUEST['post_id'];
	}

	if (sizeof($_REQUEST['posts'])>0) {
		$i=0;
		foreach ($_REQUEST['posts'] as $post_id) {
		
			$sql = "SELECT user_id FROM posts_table where post_id='".jb_escape_sql($post_id)."' ";
			$result = JB_mysql_query ($sql) or die (mysql_error());
			$row = mysql_fetch_array($result, MYSQL_ASSOC);
			if ($row['user_id'] == $_SESSION['JB_ID']) { 
				$i++;
				JB_delete_post($post_id);	
			} 
		}

		JB_finalize_post_updates();
		
		$label["employer_manager_deleted_posts"] = str_replace('%COUNT%', $i, $label["employer_manager_deleted_posts"]);
		$JBMarkup->ok_msg($label["employer_manager_deleted_posts"]);		

	} else {
		$JBMarkup->error_msg($label["employer_manager_not_selected_del"]);
	}

}



if ($_REQUEST['expire']!='') {

	
	if (sizeof($_REQUEST['posts'])>0) {
		$i=0;
		foreach ($_REQUEST['posts'] as $post_id) {
			$post_id = (int) $post_id;
			$post_data = JB_load_post_data($post_id);

			if ($post_data['user_id'] == $_SESSION['JB_ID']) { // is it owned by the person who logged in?
				$i++;
				JB_expire_post($post_id);
				JB_update_post_category_count($post_data);
			} 
		}

		JB_finalize_post_updates();
		
		$label['employer_manager_expired_posts'] = str_replace('%COUNT%', $i, $label['employer_manager_expired_posts']);
		$JBMarkup->ok_msg($label["employer_manager_expired_posts"]);

		

	} else {
		$JBMarkup->error_msg($label["employer_manager_not_selected_exp"]);
	}


}

if (isset($_REQUEST['undo_expire'])) {
	
	$post_id = (int) $_REQUEST['post_id'];

	$post_data = JB_load_post_data($post_id);

	if ($post_data['user_id'] == $_SESSION['JB_ID']) { // is it owned by the person who logged in?
		$sql = "UPDATE posts_table SET expired='N' where post_id='".jb_escape_sql($post_id)."' ";
		JB_mysql_query($sql) or $DB_ERROR = mysql_error();
		JB_update_post_category_count($post_data);
	
	} 
	
	JB_finalize_post_updates();
	
	$JBMarkup->ok_msg($label['post_unexpire_ok']);


} 

jbplug_do_callback('post_manager_action', $A=false);

JB_render_box_top(95,  $label['employer_manager_head']);
// set fees flag
if ((JB_POSTING_FEE_ENABLED == 'YES') || (JB_PREMIUM_POSTING_FEE_ENABLED == 'YES')) {
	$_FEES_ENABLED = "YES";
}

###################


JBEmployer::display_credit_status();

JB_render_box_bottom();

?>
				

<div>&nbsp;


<?php 

if (($_REQUEST['offset']==false) || ($_REQUEST['show']=='ONLINE')) {
	JB_render_box_top(95,  $label['employer_manager_online']);
	JB_list_jobs ("EMPLOYER", "ONLINE"); // show employer's online jobs.
	JB_render_box_bottom();
}
?>
				
</div>
<div>&nbsp;

<?php
if (($_REQUEST['offset']==false) || ($_REQUEST['show']=='OFFLINE')) {
	JB_render_box_top(95,  $label['employer_manager_offline']);
	JB_list_jobs ("EMPLOYER", "OFFLINE"); // show employer's offline jobs.
	JB_render_box_bottom();

}
?>
</div>
<?php
JB_template_employers_footer();
?>