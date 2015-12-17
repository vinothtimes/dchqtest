<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

require_once (dirname(__FILE__).'/code_functions.php');
require_once (dirname(__FILE__).'/category.inc.php');
require_once (dirname(__FILE__).'/lists.inc.php');

global $resume_tag_to_field_id; // deprecated since 3.6
global $resume_tag_to_search; // deprecated since 3.6


// Load the Resume form object - and instance of JBDynamicForms.php
$ResumeForm = &JB_get_DynamicFormObject(2); 
$resume_tag_to_search = $ResumeForm->get_tag_to_search();
$resume_tag_to_field_id = $ResumeForm->get_tag_to_field_id();

JBPLUG_do_callback('resumes_init', $resume_tag_to_search, $resume_tag_to_field_id);



#####################################

function JB_resume_tag_to_field_id_init () {

	global $resume_tag_to_field_id; // deprecated since 3.6
	if ($resume_tag_to_field_id = JB_cache_get('tag_to_field_id_2_'.$_SESSION['LANG'])) {
		return $resume_tag_to_field_id;
	}
	$fields = JB_schema_get_fields(2);
	// the template tag becomes the key

		
	foreach ($fields as $field) {
		$resume_tag_to_field_id[$field['template_tag']] = $field;
	}


	JBPLUG_do_callback('resume_tag_to_field_id_init', $resume_tag_to_field_id);
	JB_cache_set('tag_to_field_id_2_'.$_SESSION['LANG'], $resume_tag_to_field_id);
	return $resume_tag_to_field_id;

}

######################################################################

function JB_load_resume_data ($resume_id) {
	$ResumeForm = &JB_get_DynamicFormObject(2);

	$sql = "SELECT * FROM `resumes_table` WHERE resume_id='".jb_escape_sql($resume_id)."' ";
	$result = JB_mysql_query($sql) or die ($sql. mysql_error());

	if ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		
		// Load the skill matrix (if exists)
		$sql = "SELECT * FROM form_fields WHERE form_id=2 AND field_type = 'SKILL_MATRIX' ";
		$result = JB_mysql_query($sql);
		if (mysql_num_rows($result)>0) {
			while ($fields = mysql_fetch_array($result, MYSQL_ASSOC)) {
			
				JB_load_skill_matrix_data($fields['field_id'], $resume_id, $row);
			}
		}
		
	}
	
	JBPLUG_do_callback('load_resume_values', $row);
	
	return $row;


}

##############################

function JB_init_resume_values(&$data) {

	$form_id = 2;
	if (!is_numeric($_REQUEST['user_id'])) {
		$_REQUEST['user_id']=$_SESSION['JB_ID'];
	} 
	JB_init_data_from_request($form_id, $data);
	JBPLUG_do_callback('init_resume_values', $data);

}

############################################
# This function is deprecated, use instead:
# $ResumeForm = &JB_get_DynamicFormObject(2);
# $ResumeForm->display_form($mode, false);
function JB_display_resume_form ($form_id=2, $mode, &$passed_data, $admin) {

	global $error;
	global $label;
	

	if ($passed_data == '' ) {
		JB_init_resume_values($passed_data);
	}
	
	JB_template_resume_form($mode, $admin);

}



###########################################################################

function JB_list_resumes ($list_mode, $show='') {

	global $resume_tag_to_field_id;
	global $tag_to_search;
	
	global $label; // languages array

	$LM = &JB_get_ResumeListMarkupObject(); // load the ListMarkup Class
	$LM->set_list_mode($list_mode);
	$LM->set_show($show);

	if ($list_mode=='ADMIN') {
		$admin=true;
	}
	
	###########################################
	# initialize
	# 

	if (!defined('JB_RESUMES_PER_PAGE')) {
		$resumes_per_page = 30;
	} else {
		$resumes_per_page = JB_RESUMES_PER_PAGE;
	}
    
	$order = jb_alpha_numeric($_REQUEST['order_by']);

	if ($_REQUEST['ord']=='asc') {
		$ord = 'ASC';
	} elseif ($_REQUEST['ord']=='desc') {
		$ord = 'DESC';
	} else {
		$ord = 'DESC'; // sort descending by default
	}

	if (($order == '') || (!JB_is_field_valid($order, 2))) {
		// by default, order by the post_date
		$order = " `resume_date` ";           
	} else {
		$order = " `".jb_escape_sql($order)."` ";
	}


   $offset = (int) $_REQUEST['offset'];
   if ($offset<0) {
		$offset = abs($offset);
	}

   if ($offset == '') {
		$offset=0;
	}

	// build the search query string

	global $action;

   // process search result
	if ($_REQUEST['action'] == 'search') {
		$q_string = JB_generate_q_string(2);	   
		$where_sql = JB_generate_search_sql(2);
	}
	
	$cat = (int) $_REQUEST['cat'];
	if ($cat != '') {
		$cat="&amp;cat=$cat";
		$cat_sql = JB_search_category_tree_for_resumes();
	}
	

	if ($admin) {
		$where_status = " `status` != 'x' ";
	} else {
		$where_status = " `status`='ACT' ";

	}

	
	$approved = "";

	if ($show=='WA') {  // Admin is true, WA will show posts waiting to be approved
		$where_sql= " AND approved='N' ";
	} else {
		$approved = "t1.approved='Y' AND ";
	}

	#####################3
	# Set the LIMIT part of the sql query

	$limit_sql = "LIMIT ".jb_escape_sql($offset).",".jb_escape_sql($resumes_per_page)." ";

	##################################
	# How to get the resume count
	# If not searching by category, then 
	if (($where_sql=='') && ($cat_sql=='')) { 
		if ($admin) { // showing all resumes, active, not approved and suspended
			$resume_count = JB_get_resume_count('ALL');
		} else { // showing active & approved
			$resume_count = JB_get_resume_count('ACT');
		} 
		if ($resume_count===null) {
			$calc_found_rows_sql = 'SQL_CALC_FOUND_ROWS';
		}

	} else {
		$calc_found_rows_sql = 'SQL_CALC_FOUND_ROWS';
	}
	
	if ($list_mode=='SAVED') {

		$order = 'save_date';
		$calc_found_rows_sql = 'SQL_CALC_FOUND_ROWS';
		$sql = "SELECT $calc_found_rows_sql * FROM `saved_resumes` AS t1
		LEFT JOIN `resumes_table` as t2 on t2.resume_id=t1.resume_id
		 WHERE t1.user_id='".jb_escape_sql($_SESSION['JB_ID'])."'   ORDER BY $order $ord $limit_sql";

	
	} elseif ($tag_to_search['smx_exists']) {
		// a skill matrix exists.. use the JOIN version of the query (Slower)
		// Using a LEFT JOIN because we want to have null values if no data for skill_matrix_data
		$sql = "Select $calc_found_rows_sql *, t1.user_id AS user_id FROM `resumes_table` AS t1 LEFT JOIN `skill_matrix_data` AS t2 ON t1.resume_id=t2.object_id WHERE $approved  $where_status $where_sql $cat_sql group by t1.resume_id ORDER BY $order $ord $appr_order $limit_sql ";

	} else {
		$sql = "Select $calc_found_rows_sql  * FROM `resumes_table`  as t1 WHERE   $approved $where_status $where_sql $cat_sql  ORDER BY  $order $ord  $limit_sql ";
	}

	$result = JB_mysql_query($sql) or die (mysql_error());
	############
	# get the count if not initialized
	# Ask MySQL to get the number of rows from the last query
	if ($calc_found_rows_sql) {
		# Even though the last query had a LIMIT clause
		$row = mysql_fetch_row(jb_mysql_query("SELECT FOUND_ROWS()"));
		$resume_count = $row[0]; 
	}

	if ($resume_count > 0 ) {
		
		// estimate number of pages.
		$pages = ceil($resume_count / $resumes_per_page);

		if ($pages == 1) {
		   // only one page - no need to show page navigation links
		   
		} else {

			$pages = ceil($resume_count / $resumes_per_page);
			$cur_page = $offset / $resumes_per_page;
			$cur_page++;

			$LM->nav_pages_start();
			//echo "Page $cur_page of $pages - ";
			$label["navigation_page"] =  str_replace ("%CUR_PAGE%", $cur_page, $label["navigation_page"]);
			$label["navigation_page"] =  str_replace ("%PAGES%", $pages, $label["navigation_page"]);
			$LM->nav_pages_status();
			$nav = JB_nav_pages_struct($result, $q_string, $resume_count, $resumes_per_page);
			$LINKS = 10;
			JB_render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
			$LM->nav_pages_end();

		}

		// How many columns? (the hits column does not count here...)
		ob_start(); // buffer the output, so that we can calculate the colspan.
		$colspan = JB_echo_list_head_data(2, $admin); // output the header columns
		$list_head_data = ob_get_contents();
		ob_end_clean();
		JBPLUG_do_callback('resume_list_set_colspan', $colspan); // set the colspan value
		$LM->set_colspan($colspan);

		if (($list_mode=='EMPLOYER') || ($list_mode=='ADMIN') || ($list_mode=='SAVED')) {
			$LM->open_form();
		}
		
		$LM->list_start();

		if ($list_mode=='ADMIN') {			
			// controls (approve button / disapprove button)
			$LM->admin_list_controls();	
		} elseif ($list_mode=='EMPLOYER') {
			$LM->employer_list_controls();
		} elseif ($list_mode=='SAVED') {
			$LM->saved_list_controls();
		}

		#######################################
		# Open the list heading section
		$LM->list_head_open();
		if ($list_mode=='ADMIN') {
			 $LM->list_head_admin_action(); 
			 JBPLUG_do_callback('resume_list_head_admin_action', $A = false);
		} elseif ($list_mode=='EMPLOYER') {
			$LM->list_head_employer_action();
		} elseif ($list_mode=='SAVED') {
			$LM->list_head_saved_action();
		}
		JBPLUG_do_callback('resume_list_head_user_action', $A = false);

		#######################################

		echo $list_head_data;

		#######################################
		# Close the list heading section

		$LM->list_head_close();
		
		$i=0;
		JBPLUG_do_callback('resume_list_pre_fill', $i, $admin); //A plugin can list its own records before, and adjust the $i
	
		while (($row = mysql_fetch_array($result, MYSQL_ASSOC)) && ($i < $resumes_per_page)) {
			$LM->set_values($row);
			JBPLUG_do_callback('resume_list_set_data', $row, $i, $list_mode); // A plugin can modify the prams
			$i++;
			
			if ($admin) { // If Administrator, then can view private details.
				$row['anon'] = 'N';
			}

			$LM->list_item_open($admin);

			
			if ($list_mode == 'ADMIN') {
				$LM->list_data_admin_action();
				JBPLUG_do_callback('resume_list_data_admin_action', $LM);
			} elseif ($list_mode == 'SAVED') {
				$LM->list_data_saved_action();
			} elseif ($list_mode == 'EMPLOYER') {
				$LM->list_data_employer_action();
			}
			JBPLUG_do_callback('resume_list_data_user_action', $LM);
			JB_echo_resume_list_data($admin);
			$LM->list_item_close();			 
		}

		JBPLUG_do_callback('resume_list_back_fill', $i, $admin); // A plugin can list its own records after

		$LM->list_end();
		if (($list_mode=='EMPLOYER') || ($list_mode=='ADMIN')) {
			$LM->close_form();
		}
		$LM->nav_pages_start();
		JB_render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
		$LM->nav_pages_end();
   
   } else {
	   $LM->no_resumes();
      
   }
}


###################################

function JB_request_was_made($candidate_id, $employer_id) {
   $sql = "select * from `requests` where `candidate_id`='".jb_escape_sql($candidate_id)."' AND `employer_id`='".jb_escape_sql($employer_id)."' AND `deleted`='N' LIMIT 1 ";

   $result = JB_mysql_query($sql);

   if (mysql_num_rows($result) > 0 ) {
      return true;
   }
   return false;

}

#################################################################
/*

Check the request_status table for the status of the request
Returns: true if request was granted, false if not granted,
0 if no request was mase

*/
function JB_is_request_granted($user_id, $employer_id) {

	static $is_granted;
	if (isset($is_granted[$user_id.'_'.$employer_id])) {
		return $is_granted[$user_id.'_'.$employer_id];
	}

	$sql = "select request_status from `requests` where `candidate_id`='".jb_escape_sql($user_id)."' AND `employer_id`='".jb_escape_sql($employer_id)."' ";

	$result = JB_mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$row = mysql_fetch_array($result, MYSQL_ASSOC);

		if ($row['request_status'] == 'GRANTED') {
			$is_granted[$user_id.'_'.$employer_id] = true;
			return true;
		} elseif ($row['request_status'] == 'REQUEST') {
			$is_granted[$user_id.'_'.$employer_id] = false;
			return false;
		} else {
			$is_granted[$user_id.'_'.$employer_id] = false;
			return false;
		}

	} 

	$is_granted[$user_id.'_'.$employer_id] = 0;
	return 0;

}
##############################################################
function JB_grant_request ($candidate_id, $employer_id) {
	$now = (gmdate("Y-m-d H:i:s"));

	$sql = "UPDATE `requests` SET request_status='GRANTED', request_date='".$now."' WHERE candidate_id='".jb_escape_sql($candidate_id)."' AND employer_id='".jb_escape_sql($employer_id)."'  ";

	JB_mysql_query($sql) or die (mysql_error());

	if (jb_mysql_affected_rows()==0) {

		$sql = "SELECT request_id FROM `requests` WHERE candidate_id='".jb_escape_sql($candidate_id)."' AND employer_id='".jb_escape_sql($employer_id)."'";

		jb_add_new_request($candidate_id, $employer_id, 'GRANTED');
	}

}

#############################################################

function jb_add_new_request($candidate_id, $employer_id, $request_status='REQUEST', $request_message='') {

	$key = md5($employer_id, $candidate_id);
	$now = (gmdate("Y-m-d H:i:s"));
	$key = md5($_SESSION['JB_ID']. $_REQUEST['user_id']); // employer_id and candidate_id make the key
	$key = substr($key, 0, 8);

	$sql = "REPLACE INTO `requests` (candidate_id, employer_id, request_status, request_date, request_message) VALUES ('".jb_escape_sql($candidate_id)."', '".jb_escape_sql($employer_id)."', '".jb_escape_sql($request_status)."', '$now', '".jb_escape_sql($request_message)."') ";
	
	JB_mysql_query($sql) or die (mysql_error());


}
#############################################################

function JB_display_request_history ($user_id) {

	global $label, $JBMarkup;

	$RLM = &JB_get_ListMarkupObject('JBRequestListMarkup');

	$sql = "SELECT * FROM requests WHERE candidate_id='".jb_escape_sql($user_id)."' AND `deleted`='N' "; 
	$result = JB_mysql_query($sql);

	if (mysql_num_rows($result) ==0) {

	} else {

		$RLM->open_form('request_form');
		$RLM->set_colspan(5);
		$RLM->list_start('request_list', 'request_history');

		$RLM->list_controls();

		$RLM->list_head_open();
		$RLM->list_head_action('employer_ids'); 
		$RLM->list_head_cell_open(); echo $label['request_history_date']; $RLM->list_head_cell_close();
		$RLM->list_head_cell_open(); echo $label['request_history_employer']; $RLM->list_head_cell_close();
		$RLM->list_head_cell_open(); echo $label['request_history_has_permission']; $RLM->list_head_cell_close();
		$RLM->list_head_cell_open(); echo $label['request_history_permission']; $RLM->list_head_cell_close();
		$RLM->list_head_close();

		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

			$RLM->set_values($row);

			$RLM->list_item_open();
			
			$RLM->list_data_action('employer_ids', $row['employer_id']);
  
  
 
			$RLM->list_cell_open(); $RLM->data_cell('request_date'); $RLM->list_cell_close();
			$RLM->list_cell_open(); $RLM->data_cell('employer_id'); $RLM->list_cell_close();
			$RLM->list_cell_open();

			if ($row['request_status'] == "REQUEST" ) {
				$RLM->requested_status();
			} elseif ($row['request_status'] == "GRANTED" ) {
				$RLM->granted_status();
			}elseif ($row['request_status'] == "REFUSED" ) {
				$RLM->refused_status();
			} else {
				$RLM->refused_status();
			}
			
			$RLM->list_cell_close();

			$RLM->list_cell_open();
			$RLM->grant_button();
			$RLM->refuse_button($row['employer_id']);
			$RLM->list_cell_close();
			
			$RLM->list_item_close();
	
		}

		$RLM->list_end();
		$RLM->close_form();
	}

}
########################################################
function JB_delete_resume_files ($resume_id) {


	$sql = "select * from form_fields where form_id=2 ";
	$result = JB_mysql_query ($sql) or die (mysql_error().$sql);

	while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

		$field_id = $row['field_id'];
		$field_type = $row['field_type'];

		if (($field_type == "FILE")) {
			JB_delete_file_from_field_id("resumes_table", "resume_id", $resume_id, $field_id);
		}

		if (($field_type == "IMAGE")){
			JB_delete_image_from_field_id("resumes_table", "resume_id", $resume_id, $field_id);
		}
		
	}


}

####################
function JB_generate_resume_id () {

   $query ="SELECT max(`resume_id`) FROM `resumes_table";
   $result = JB_mysql_query($query) or die(mysql_error());
   $row = mysql_fetch_row($result);
   $row[0]++;
   return $row[0];

}

################################################################

function JB_insert_resume_data() {


	if (func_num_args() > 0) {
		$admin = func_get_arg(0); // admin mode.
	}
	$list_on_web = 'Y';
	$_REQUEST['anon'] = jb_alpha_numeric($_REQUEST['anon']);
	$status = "ACT";
	$approved= 'Y';

	if ($admin==true) {
		$sql = "select user_id from `resumes_table` WHERE resume_id='".jb_escape_sql($_REQUEST['resume_id'])."'";
		$result = JB_mysql_query ($sql) or die(mysql_error());
		$row = @mysql_fetch_array($result, MYSQL_ASSOC);
		$user_id = $row['user_id'];
	} else {
		$user_id = (int) $_SESSION['JB_ID'];
	}

	if ((JB_RESUMES_NEED_APPROVAL=='YES') && (!$admin)) {
		$approved='N';
	}

	if ($_REQUEST['resume_id'] == false) {
		$assign = array(	
			'list_on_web' => 'Y',
			'resume_date' => gmdate("Y-m-d H:i:s"),
			'user_id' => $user_id,
			'approved' => $approved,
			'anon' => jb_alpha_numeric($_REQUEST['anon']),
			'status' => 'ACT',
			'expired' => 'N'
		);
		$sql = "REPLACE INTO `resumes_table` ( ".JB_get_sql_insert_fields(2, $assign).") VALUES (".JB_get_sql_insert_values(2, "resumes_table", "resume_id", $resume_id, $user_id, $assign).") "; // JB_get_sql_insert_values() escapes the sql values

		$action = "Inserted new resume.";

	} else {
		
		$resume_id = (int) $_REQUEST['resume_id'];
		$now = (gmdate("Y-m-d H:i:s"));
		$assign = array(	
			'resume_date' => gmdate("Y-m-d H:i:s"),
			'anon' => jb_alpha_numeric($_REQUEST['anon']),
			'approved' => $approved
		);
		$sql = "UPDATE `resumes_table` SET  ".JB_get_sql_update_values (2, "resumes_table", "resume_id", $_REQUEST['resume_id'], $user_id, $assign)." WHERE resume_id='".jb_escape_sql($resume_id)."' and user_id='".jb_escape_sql($user_id)."' "; // JB_get_sql_update_values() // escapes the sql values
		
		//$action = "Updated existing resume";
	}
	JB_mysql_query ($sql) or die("[$sql]".mysql_error());

	if ($resume_id == false) {
		$resume_id = JB_mysql_insert_id();
	}
	
	$RForm = &JB_get_DynamicFormObject(2);
	$data = $RForm->load($resume_id);
		
	
	$data['resume_id'] = $resume_id;

	
	JB_build_resume_count(0);

	JBPLUG_do_callback('insert_resume_data', $data);

	if (JB_EMAIL_ADMIN_RESUPDATE_SWITCH == 'YES') { // send notification email to Admin
		$resume_tag_to_field_id = &$RForm->get_tag_to_field_id();

		$RESUME_SUMMARY = $action."\r\n";

		$sql = "SELECT * from form_lists WHERE form_id=2 ORDER BY sort_order ";
		$result = JB_mysql_query($sql);
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$label = $field_field_label = $resume_tag_to_field_id[$row['template_tag']]['field_label'];
			$RESUME_SUMMARY .= $label." - ".$RForm->get_raw_template_value($row['template_tag'], $admin)."\r\n";
		}

		// get the email template
		$template_result = JB_get_email_template(320, 'EN'); 
		$t_row = mysql_fetch_array($template_result);

		$to_address = JB_SITE_CONTACT_EMAIL;
		$to_name = JB_SITE_NAME;
		$subject = $t_row['EmailSubject'];
		$message = $t_row['EmailText'];
		$from_name = $t_row['EmailFromName'];
		$from_address = $t_row['EmailFromAddress'];
		$subject = str_replace("%SITE_NAME%", JB_SITE_NAME, $subject);
		$message = str_replace("%RESUME_SUMMARY%", $RESUME_SUMMARY, $message);
		$message = str_replace("%ADMIN_LINK%", JB_BASE_HTTP_PATH."admin/ra.php?resume_id=".$resume_id."&key=".md5($resume_id.JB_ADMIN_PASSWORD), $message);
		$message = str_replace("%SITE_NAME%", JB_SITE_NAME, $message);
		$message = strip_tags($message);

		JB_queue_mail($to_address, $to_name, $from_address, $from_name, $subject, $message, '', 320);


	}

	return $resume_id;
}
###############################################################
function JB_validate_resume_data($form_id) {

	global $label;
	
	$errors = array();

	// Make sure they are numeric
	if ($_REQUEST['resume_id']!='') {
		if (!is_numeric($_REQUEST['resume_id'])) {
			return 'Invalid Input!';
		}
	}
	// Make sure they are numeric
	if ($_REQUEST['user_id']!='') {
		if (!is_numeric($_REQUEST['user_id'])) {
			return 'Invalid Input!';
		}
	}
	$_REQUEST['list_on_web'] = JB_clean_str($_REQUEST['list_on_web']);
	$_REQUEST['resume_date'] = JB_clean_str($_REQUEST['resume_date']);
	$_REQUEST['anon'] = JB_clean_str($_REQUEST['anon']);
	$_REQUEST['approved'] = JB_clean_str($_REQUEST['approved']);

	JBPLUG_do_callback('validate_resume_data_array', $errors); // added in 3.6.6 to replace validate_resume_data. $errors is a list of reasons why the form cannot be saved

	$error = false;
	JBPLUG_do_callback('validate_resume_data', $error); // deprecated, use validate_resume_data_array instead
	if ($error) {
		$list = explode('<br>', $error); // in the old version, $error was just a string separated by <br>'s
		foreach ($list as $item) {
			$errors[] = $item;
		}
	}

	$errors = $errors + JB_validate_form_data(2);

	return $errors;
}

################################################################

function JB_delete_resume ($resume_id) {

	JB_delete_resume_files ($resume_id);

	$sql = "DELETE FROM `resumes_table` WHERE `resume_id`='".jb_escape_sql($resume_id)."'";
	JB_mysql_query($sql) or die (mysql_error().$sql);

	// delete the resume from saved resumes
	$sql = "DELETE FROM `saved_resumes` WHERE `resume_id`='".jb_escape_sql($_REQUEST['resume_id'])."' ";
	JB_mysql_query($sql) or die(mysql_error());


	JBPLUG_do_callback('delete_resume', $resume_id);

}



#####################################################

function JB_search_category_tree_for_resumes($cat_id=false, $field_id=false) {

	if ($cat_id==false) {
		$cat_id = (int) $_REQUEST['cat'];
	}

	if ($field_id!=false) {
		$field_id_sql = "AND field_id='".jb_escape_sql($field_id)."'"; 
	}


	$sql = "select * FROM form_fields WHERE field_type='CATEGORY' AND form_id='2' $field_id_sql";
	$result = JB_mysql_query ($sql) or die (mysql_error());

	$sql = "select search_set FROM categories WHERE category_id='".jb_escape_sql($cat_id)."' ";
	$result2 = JB_mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result2);
	
	// initialize $search_set
	if ($row['search_set']!='') {
		$search_set = $cat_id.','.$row['search_set'];
	} else {
		$search_set = $cat_id;
	}
	$i=0;

	
	if (mysql_num_rows($result) >0) {

		$or ='';
		while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

			
			$range_or = '';
			$set = array();
			if (strlen($search_set) < 250) {
				// Use IN() operator
				$where_cat .= " $or `".$row['field_id']."` IN ($search_set) ";
				$or = 'OR';

			} else {
				// When there are thousands of categories, the search_set
				// could be huge.
				// So here attept to compress the $search_set
				// The following code will convert the $search_set, eg 1,2,3,4,6,7,8,9
				// in to ranges to make it smaller like this 1-4,5-9 and put it
				// in to an SQL query with comparison operators instead of
				// using the IN() operator

				$set = explode (',', $search_set);
				sort($set, SORT_NUMERIC);
				for ($i=0; $i < sizeof ($set); $i++) {
					$start = $set[$i]; // 6
					//$end = $set[$i];
					for ($j=$i+1; $j < sizeof ($set) ; $j++) {
						// advance the array index $j if the sequnce 
						// is +1	
						if (($set[$j-1]) != $set[$j]-1) { // is it in sequence
							$end = $set[$j-1];
							break;
						}
						$i++;
						$end = $set[$i];	
					}
					if ($end=='') {
						$end = $set[$i];
					}
					if (($start != $end) && ($end != '')) {
						$where_range .= " $range_or  ((`".$row['field_id']."` >= $start) AND (`".$row['field_id']."` <= $end)) ";
					} elseif ($start!='') {
						$where_range .= " $range_or  (`".$row['field_id']."` = $start ) ";
					}
					$start='';$end='';
					$range_or = "OR";
				}

				$where_cat .= " $or $where_range  ";
				$where_range='';
				$or = 'OR';
			}
		}

	}


	if ($where_cat=='') {
		return " AND 1=2 ";
	}

	if ($search_set=='') {
		return "";
	}

	return " AND ($where_cat) ";
	

}




##################
function JB_get_resume_id ($user_id) {

	$sql = "select resume_id from resumes_table WHERE user_id='".jb_escape_sql($user_id)."' ";
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$row= mysql_fetch_array($result, MYSQL_ASSOC) ;
	
	return $row['resume_id'];

}

###################################

function JB_is_user_resume_anonymous($user_id) {

	$sql = "select anon from resumes_table WHERE user_id='".jb_escape_sql($user_id)."' ";
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$row= mysql_fetch_array($result, MYSQL_ASSOC) ;
	if ($row['anon']=='Y') {
		return true;
	} else {
		return false;
	}


}

##################################

function JB_send_request_granted_email($candidate_id, $employer_id) {

	$user_id = (int) $candidate_id;
	$employer_id = (int) $employer_id;
	$sql = "SELECT FirstName, LastName, Password FROM users where `ID`='".jb_escape_sql($candidate_id)."' ";
	$result = jb_mysql_query($sql);
	$candidate_row = mysql_fetch_array($result, MYSQL_ASSOC);
	$sql = "SELECT FirstName, LastName, Email FROM employers where `ID`='".jb_escape_sql($employer_id)."' ";
	$result = jb_mysql_query($sql);
	$employer_row = mysql_fetch_array($result, MYSQL_ASSOC);

	// get the resume db link
	$sql = "SELECT resume_id FROM resumes_table WHERE user_id='".jb_escape_sql($candidate_id)."' AND `status`='ACT' ";
	$result = JB_mysql_query($sql) or die(mysql_error());
	

	if (mysql_num_rows($result) > 0) {
		$resume_row = mysql_fetch_array($result, MYSQL_ASSOC);
		$val = substr(md5 ($resume_row['resume_id'].$candidate_row['Password'].$candidate_id), 0,10);
		$resume_db_link = JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER."search.php?resume_id=".urlencode($resume_row['resume_id'])."&id=".urlencode($candidate_id)."&key=$val";
	} else {
		return false; // resume does not exist anymore...
	}

	$result = JB_get_email_template (44, $_SESSION['LANG']);
	$e_row = mysql_fetch_array($result, MYSQL_ASSOC);
	$text_message = $e_row['EmailText'];
	$from = $e_row['EmailFromAddress'];
	$from_name = $e_row['EmailFromName'];
	$subject = $e_row['EmailSubject'];

	$candidate_name = JB_get_formatted_name($candidate_row['FirstName'], $candidate_row['LastName']);
	$employer_name = JB_get_formatted_name($employer_row['FirstName'], $employer_row['LastName']);

	$to_name = $employer_name;
	$to_address = $employer_row['Email'];

	$text_message = str_replace ("%SITE_NAME%", JB_SITE_NAME, $text_message);
	$text_message = str_replace ("%SITE_URL%", JB_BASE_HTTP_PATH, $text_message);
	$text_message = str_replace ("%RESUME_DB_LINK%", $resume_db_link , $text_message);
	$text_message = str_replace ("%CAN_NAME%", $candidate_name , $text_message);
	$text_message = str_replace ("%EMP_NAME%", $employer_name , $text_message);

	$subject = str_replace ("%CAN_NAME%", $candidate_name , $subject);
	$subject = str_replace ("%SITE_NAME%", JB_SITE_NAME, $subject);

	JB_queue_mail($to_address, $to_name, $e_row['EmailFromAddress'], $e_row['EmailFromName'], $subject, $text_message, $html_message, $e_row['EmailID']);


}


?>