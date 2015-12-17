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
JB_template_employers_header(); 



require_once('../include/resumes.inc.php');

$ALM = JB_get_AppListMarkupObject();
$PForm = &JB_get_DynamicFormObject(1);

$ALM->set_list_mode('EMPLOYER');


$post_id = (int) $_REQUEST['post_id'];
$action = $_REQUEST['action'];
$apps = $_REQUEST['apps'];

 
if ($_REQUEST['delete']) {
	
	$employer_id = (int) $_SESSION['JB_ID'];
	for ($i=0; $i < sizeof($apps); $i++) {

		$sql = "DELETE FROM `applications` WHERE `app_id`='".jb_escape_sql($apps[$i])."' AND `employer_id`='".jb_escape_sql($employer_id)."'";
		
		$result = JB_mysql_query ($sql) or die (mysql_error());

	}

	if (sizeof($apps)>0) {
		$JBMarkup->ok_msg($label['emp_app_deleted']);
	} else {
		$JBMarkup->error_msg($label['emp_app_no_select']);
	}

}


JB_render_box_top(99, $label['emp_app_head']);



?>
<p>

<?php 

$offset = (int) $_REQUEST['offset'];
$records_per_page = 20;

if ($post_id) {

	$where_sql = "employer_id='".jb_escape_sql($_SESSION['JB_ID'])."' AND post_id='".jb_escape_sql($post_id)."'";
	$sql = "SELECT * FROM applications WHERE $where_sql ORDER BY `app_date` DESC LIMIT $offset, $records_per_page";

} else {

	$where_sql = "employer_id='".jb_escape_sql($_SESSION['JB_ID'])."'";

	// order by posts and date
	$sql = "SELECT * FROM applications  WHERE $where_sql ORDER BY post_id DESC, `app_date` DESC LIMIT $offset, $records_per_page";

}



JBPLUG_do_callback('employer_apps_sql', $sql);

$result = JB_mysql_query($sql) or die (mysql_error());
$count = array_pop(mysql_fetch_row(jb_mysql_query("SELECT count(*) FROM applications WHERE $where_sql ")));

if (mysql_num_rows($result) > 0 ) {

	if ($post_id) {

		$data = $PForm->load($post_id);
		$PLM = &JB_get_PostListMarkupObject();
		$PLM->set_values($data);
		$TITLE = $PForm->get_template_value('TITLE');
		$DATE = JB_get_formatted_date($PForm->get_template_value('DATE'));

		
		if ($count == 1) {
			$str = $label['emp_app_post_title_singular'];
		} else {
			$str = $label['emp_app_post_title_plural'];
		}


		$str = str_replace('%TITLE%', '<a href="" '.$PLM->get_new_window_js().' >'.jb_escape_html($TITLE).'</a>', $str);
		$str = str_replace('%DATE%', jb_escape_html($DATE), $str);
		$str = str_replace('%COUNT%', $count, $str);

		echo "<p>$str</p>";
		echo "<p>".$label['emp_app_list_by_post_heading']."</p>";

	}

	$offset = $_REQUEST['offset']; // global veriable used by nav bar

	$result = JB_mysql_query($sql) or die (mysql_error());

	$nav = JB_nav_pages_struct($result, $q_string, $count, $records_per_page);
	$LINKS = 10;
	
	$ALM->nav_pages_start();
	JB_render_nav_pages($nav, $LINKS, $q_string);
	
	$ALM->nav_pages_end();


	$row['formatted_date'] = JB_get_formatted_date($row['app_date']);

	$COLSPAN = 5;
	JBPLUG_do_callback('emp_apply_list_action_colspan', $COLSPAN); // a plugin can also set the colspan
	$ALM->set_colspan($COLSPAN);

	$ALM->open_form('form1'); 

	$ALM->list_start('joblist', 'list');
	$ALM->employer_list_controls();

	$ALM->list_head_open();

	$ALM->list_head_employer_action('apps');
	
	$ALM->list_head_cell_open(); echo $label["emp_app_date"]; $ALM->list_head_cell_close();
	$ALM->list_head_cell_open(); echo $label["emp_app_title"]; $ALM->list_head_cell_close();
	$ALM->list_head_cell_open(); echo $label["emp_app_name"]; $ALM->list_head_cell_close();
	$ALM->list_head_cell_open(); echo $label["emp_app_email"]; $ALM->list_head_cell_close();
	$ALM->list_head_close();
	
	

	while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

		$ALM->set_values($row);
		$i++;

		$ALM->list_item_open('standard');
		

		$ALM->list_data_employer_action();
		$ALM->list_cell_open(); echo JB_get_formatted_date(JB_get_local_time($row['app_date'])); $ALM->list_cell_close();
		$ALM->list_cell_open(); echo $ALM->get_open_link('apps.php?post_id='.$row['post_id'], $extra_attr);  echo JB_escape_html($row['data1']); echo $ALM->get_close_link(); $ALM->list_cell_close();
		$ALM->list_cell_open();
	
		$sql2 = "SELECT * FROM users where ID='".jb_escape_sql($row['user_id'])."'";
		$result2 = JB_mysql_query ($sql2) or die (mysql_error());
		$candidate_row = mysql_fetch_array($result2);

		$sql3 = "SELECT * FROM resumes_table where user_id='".jb_escape_sql($row['user_id'])."'";
		$result3 = JB_mysql_query ($sql3) or die (mysql_error());
		$resume_row = mysql_fetch_array($result3);

		$sql4 = "SELECT * FROM posts_table where post_id='".jb_escape_sql($row['post_id'])."'";
		$result4 = JB_mysql_query ($sql4) or die (mysql_error());
		$post_row = mysql_fetch_array($result4);

		
		$candidate_row['FormattedName'] = jb_escape_html(jb_get_formatted_name($candidate_row['FirstName'], $candidate_row['LastName']));
		$candidate_row['user_id'] = $candidate_row['ID'];

		// 'anon' If Y, then resume is anonumous and fields are restricted.
		// Here use $PForm to process the field restrictions
		$PForm->set_value('anon', $resume_row['anon']);

		if ($resume_row['anon']=='Y') {
			if ((JB_ONLINE_APP_REVEAL_PREMIUM=='YES') && ($post_row['post_mode']=='premium')) {
				$PForm->set_value('anon',  'N'); // can show anonymous fields
			}
			if ((JB_ONLINE_APP_REVEAL_STD=='YES') && ($post_row['post_mode']!='premium')) {
				$PForm->set_value('anon',  'N'); // can show anonymous fields
			}
			if ((JB_ONLINE_APP_REVEAL_RESUME=='YES') && ($post_row['post_mode']!='premium')) {
				$PForm->set_value('anon',  'N'); // can show anonymous fields
			}
			if (JB_is_request_granted($resume_row['user_id'], $_SESSION['JB_ID'])===false) {
				// In this situation, the user sepcifically disallowed the employer
				// form viewing their resume. (When applying for a job, the employer
				// will be automatically allowed to view anonymous fields if any 
				// of the above (three) config options are set to YES)
				$PForm->set_value('anon',  'Y'); // cannot view resumes
			}
		}
		
		// Here we process the field restrictions for
		// the name and email field.
		// These fields can be anonymous (hidden) and blocked (subscription only)
		// We use the dynamic form object for this task.
		// First we set the data, and the call  process_field_restrictions()

		$PForm->set_value('row2_FormattedName', $candidate_row['FormattedName']);
		$PForm->set_value('row2_Email', $candidate_row['Email']);
		$PForm->set_value('user_id', $candidate_row['ID']);
		$field = array(
			'field_id' => 'row2_FormattedName',
			'is_blocked' => 'Y',
			'is_anon' => 'Y');
		if (JB_FIELD_BLOCK_APP_SWITCH!='YES') {
			$field['is_blocked'] = 'N'; // unblock on applications
		}
		$is_name_restricted = $PForm->process_field_restrictions($field);
		$field = array(
			'field_id' => 'row2_Email',
			'is_blocked' => 'Y',
			'is_anon' => 'Y');
		if (JB_FIELD_BLOCK_APP_SWITCH!='YES') {
			$field['is_blocked'] = 'N'; // unblock on applications
		}
		$is_email_restricted = $PForm->process_field_restrictions($field);
		$candidate_row['FormattedName'] = $PForm->get_value('row2_FormattedName');
		$candidate_row['Email'] = $PForm->get_value('row2_Email');


		if ($resume_row['resume_id']) {

			$anon_q = 'a='.$PForm->get_value('anon');
			$key = substr(md5 ($PForm->get_value('anon').$resume_row['resume_id'].$candidate_row['Password'].$candidate_row['ID']), 0,10);
			$key_q = $anon_q.'&amp;resume_id='.$resume_row['resume_id'].'&amp;id='.$candidate_row['ID'].'&amp;key='.$key;
			
			echo $ALM->get_open_link("search.php?$key_q", $extra_attr).$candidate_row['FormattedName'].$ALM->get_close_link(); 		
		}
		else {
			$ALM->print_formatted_app_name($candidate_row['FormattedName']); 
		}
		$ALM->list_cell_close();	
		
		$ALM->list_cell_open(); echo $candidate_row['Email']; $ALM->list_cell_close();
		$ALM->list_item_close();

		$ALM->list_item_open('standard');
		
		$ALM->cover_letter($label["emp_app_cover_letter"]);
		
		$ALM->list_item_close();
		

	}

	
	if ($i>10) {
		//$ALM->employer_list_controls();
	}

	$ALM->list_end();
	$ALM->close_form();

	$nav = JB_nav_pages_struct($result, $q_string, $count, $records_per_page);
	$LINKS = 10;
	$ALM->nav_pages_start();
	JB_render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
	$ALM->nav_pages_start();
} else {
		?>
<span ><?php echo $label["emp_app_no_apps"];?></span>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
	<?php

}

JB_render_box_bottom();

JB_template_employers_footer();

?>