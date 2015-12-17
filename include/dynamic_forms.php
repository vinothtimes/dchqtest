<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

require_once (dirname(__FILE__).'/classes/JBDynamicForm.php');
require_once (dirname(__FILE__).'/classes/JBDynamicSearchForm.php');
require_once (dirname(__FILE__).'/skill_matrix_functions.php');
require_once (dirname(__FILE__)."/code_functions.php");
require_once (dirname(__FILE__)."/category.inc.php");

# Globals
$admin = false;



function &JB_get_DynamicFormObject($form_id, $context_id=null) { 

	static $form_obj = array(); 


	
	// the following workaround ensures that plugins do not cache the object
	// instead, a new object is created. This ensures that plugins have
	// their own DynamicFormObject in their private context, avoiding 
	// changes to the one used by the job board
	// It works by tracing the function calls back to see
	// if the call originated from a plugin

	if (!$context_id) {
		$back_trace = debug_backtrace(); 
		$i=0; // keep count of iterations
		foreach ($back_trace as $call) {
			$i++;
			if ($i>3) { // 3 iterations max
				break;
			} 
			if ((strpos($call['file'], '/include/plugins/') !== false) ||
			(strpos($call['file'], '\\include\\plugins\\') !== false)) {
				$context_id = $call['file'];
			
			}
		}
	}

	if (!$context_id) {
		$context_id = 'global';
	}

	if (isset($form_obj[$context_id][$form_id])) {
		return $form_obj[$context_id][$form_id];
	} else {
		$form_obj[$context_id][$form_id] = new JBDynamicForm($form_id);
		return $form_obj[$context_id][$form_id];
	}


}

function &getDynamicSearchFormObject($form_id) {

	static $search_obj = array(); 

	if (isset($search_obj[$form_id])) {
		return $search_obj[$form_id];
	} else {
		$search_obj[$form_id] = new JBDynamicSearchForm($form_id);
		return $search_obj[$form_id];
	}

}


function JB_format_field_translation_table ($form_id) {
	global $AVAILABLE_LANGS;

	$sql = "SELECT * FROM form_fields WHERE `form_id`='".JB_escape_sql($form_id)."'";
	$f_result = JB_mysql_query ($sql) or die ($sql.mysql_error());
	while ($f_row = mysql_fetch_array($f_result, MYSQL_ASSOC)) { 

		foreach  ($AVAILABLE_LANGS as $key => $val) {

			$sql = "SELECT t2.field_id, t2.field_label AS FLABEL, lang FROM form_field_translations as t1, form_fields as t2 WHERE t2.field_id=t1.field_id AND t2.field_id='".JB_escape_sql($f_row['field_id'])."' AND lang='".JB_escape_sql($key)."' ";
			
			$result = JB_mysql_query($sql) or die($sql.mysql_error());
			
			if (mysql_num_rows($result)==0) {
				
				$sql = "rEPLACE INTO `form_field_translations` (`field_id`, `lang`, `field_label`, `error_message`, `field_comment`) VALUES ('".JB_escape_sql($f_row['field_id'])."', '".JB_escape_sql($key)."', '".JB_escape_sql(addslashes($f_row['field_label']))."', '".JB_escape_sql(addslashes($f_row['error_message']))."', '".JB_escape_sql(addslashes($f_row['field_comment']))."')";
				
				JB_mysql_query($sql) or die (mysql_error().$sql);

			}

		}

	}

}

########################################

function JB_get_template_field_id ($tmpl, $form_id) {
	$DynamicForm = &JB_get_DynamicFormObject($form_id);
	$tag_to_field_id = &$DynamicForm->get_tag_to_field_id();
	return $tag_to_field_id[$tmpl]['field_id'];

}
/*

JB_get_raw_template_value does not escape returned value for html
ouput, and value is not formatted, but returned just as it came
from the database.

*/
function JB_get_raw_template_value ($tmpl, $form_id, $admin=false) {
	return JB_get_template_value ($tmpl, $form_id, $admin, true);
}
########################################
# Returns the value for the template tag. The returned value is ready for
# output to the browser (including special formatting for each field type), 
# unless the $raw option is true, then the raw
# value will be returned. 
# 
# Arguments: 
# $tmlp = template tag
# $form_id = eg. 1=posting form, 2=resume, 3=profile, 4=employer, 5=candidate
# $raw = boolean, if true, the raw value will be returned
#
# Deprecated - This function has been deprecated and will be removed
# from future versions. Please use the get_template_value() method
# from the JBDynamicFormObject instead.
#
#
#
#

function JB_get_template_value ($tmpl, $form_id, $admin=false, $raw=false) {
	$DynamicForm = &JB_get_DynamicFormObject($form_id);

	global $prams;
	$DynamicForm->set_values($prams); // older code compatibility
	return $DynamicForm->get_template_value($tmpl, $admin, $raw);
	return $val;
}
##########################################

function JB_get_template_field_label ($tmpl, $form_id) {
	$DynamicForm = &JB_get_DynamicFormObject($form_id);
	return $DynamicForm->get_template_field_label($tmpl);
}

##########################################################################
/*
 The JB_generate_q_string() function builds a query string consisting
 of all the CGI parameters that were passed after submitting a search form.
 The function returns a query string which is appended to the end of URLs
 so that the fields in the search form are preserved for the next screen.
 The function goes through all the search fields in the $tag_to_search
 structure and builds the query string from the data received in the
 $_REQUEST array. First, &amp;action=search is added to let the job
 board know to execute a search, and then the remaining parameters are
 appened.

 Notes:
 - & characters are encoded html entities &amp;)

 - The function caches the query string in a static var so that it does
 not need to re-build the string with each call.

*/
function JB_generate_q_string($form_id) {

	if ($_REQUEST['action']==false) {
		return false;

	} else {
		$SearchFormObj = &getDynamicSearchFormObject($form_id);
		return $SearchFormObj->get_q_string();
	}

	
}

##############################################################

function JB_echo_order_arrows($row) {

	echo '<div align="left" style="margin: 0"><table align="left" border="0" cellpadding="0" cellspacing="0"><tr><td ><a href="?mode=EDIT&action=move_up&field_id='.$row['field_id'].'&field_sort='.$row['field_sort'].'&section='.$row['section'].'"><IMG SRC="sortup.gif" WIDTH="9" align="top" HEIGHT="13" BORDER="0" ALT="Move Up"></td></tr><tr><td><a href="?mode=EDIT&action=move_down&field_id='.$row['field_id'].'&field_sort='.$row['field_sort'].'&section='.$row['section'].'"><IMG SRC="sortdown.gif" WIDTH="9" HEIGHT="13" BORDER="0" ALT="Move Down"></a></td></tr></table></div>';


}


################################################################
# Display a section of a form.
# Mode can be 'view', 'edit' or 'EDIT'
# 'view' is for viewing the form's data
# 'edit' is for adding/updating data
# 'EDIT' is for editing the form fields via Admin
# $data is an array of all the form's data
# $section is an integer which specifies the section of the form
# a section is usually rendered as a new table.
# $admin is a boolean which specifies 
# whenever the form is viewed by Admin or not.
# The function can take a 6th argumnet - if true then a new <table>
# tag will not be inserted.

// deprecated
function JB_display_form ($form_id, $mode, &$data, $section, $admin) {
	if (func_num_args() > 5) {
		$dont_break_container = func_get_arg(5);
	}
	$DynamicForm = &JB_get_DynamicFormObject($form_id);
	
	$DynamicForm->display_form_section ($mode,  $section, $admin, $dont_break_container);

}

/*

Returns true if the field has some kind of restriction
ie. Is blocked, is anonymous or is member's only
Will modify the $data value with the restriction message

$data - array, the raw values from the database
$row - array, the row from the form_fields table, holding meta-data of the field
$mode - string, eitheir 'view', 'edit' or 'EDIT'. 'EDIT' is for Admin only
$admin - boolean
$viewer_user_id - the user id viewing this field
$viewer_domain - the viewer domain, EMPLOYER or CANDIDATE

*/
function JB_process_field_restrictions(&$data, &$row, $mode='view', $admin=false, $viewer_user_id=false, $viewer_domain=false) {

	if ($admin) return false; // not restricted for admin
	
	global $label;

	$is_restricted = false; 

	if (!$viewer_user_id) {
		if (isset($_SESSION['JB_ID']) && ($_SESSION['JB_ID'])) {
			$viewer_user_id = $_SESSION['JB_ID']; 
		} 
	}

	if (!$viewer_domain) {
		if (isset($_SESSION['JB_Domain']) && ($_SESSION['JB_Domain'])) {
			$viewer_domain = $_SESSION['JB_Domain'];
		}
	}

	
	

	$DFM = &JB_get_DynamicFormMarkupObject($mode); //  HTML formatting

	// there is no need to restrict fields which are empty
	if (strlen(trim($data[$row['field_id']]))===0) {
		return false;
	}

	################################################################
	# Block Anonymous fields
	# These filds can be on the resume only.

	if (JB_RESUME_REQUEST_SWITCH=='YES') {

		if (($data['anon'] =='Y')  && ($mode=='view') && (($viewer_domain=='EMPLOYER') ) ) {
			
			if (($row['is_anon']=='Y') && (JB_is_request_granted($data['user_id'], $viewer_user_id)!==true)  ) {
				// replace with a 'this field is anonymous' note
				if ($row['field_type']=='IMAGE') {
					$data[$row['field_id']] = $DFM->get_image_anonymous_note($data['user_id']);
				} else {
					$data[$row['field_id']] = $DFM->get_anonymous_note($data['user_id']);
				}
				$is_restricted = true;
				//return true;
			}
		}
	}

	#########################
	# Block Blocked fields
	# This is for the resume only, for the logged in employer

	if ((JB_FIELD_BLOCK_SWITCH=='YES') && ($row['is_blocked']=='Y')) { 

		global $key_test_passed;
		if (($mode=='view') && ($viewer_domain=='EMPLOYER') && !$key_test_passed) {
			
			$subscr_block_status = JB_get_employer_view_block_status($viewer_user_id);

			if (($subscr_block_status=='N')) {
	
				// replace with a 'this field is blocked' note
				$data[$row['field_id']] = $DFM->get_blocked_note($data['user_id']);		
				$is_restricted = true;
				//return true;
			}
		}
	}

	
	#########################
	# fields that are marked "Member's Only"

	if (JB_MEMBER_FIELD_SWITCH=='YES') {

		$member_view_status = JB_get_member_view_status($viewer_user_id, $viewer_domain);
		
		if (($row['is_member']=='Y') && ($member_view_status=='N')) {
			
			if (($data['post_mode'] == 'premium') && (JB_MEMBER_FIELD_IGNORE_PREMIUM=='YES')) { 
				// ignore for premium posts	
			} else {
				$data[$row['field_id']] = $DFM->get_membership_note();
				$is_restricted = true;
			}
		}
		
	}

    # For plugin authors: You can block / restrict your own fields by using a plugin
	# See the comments at the top of this function for a description of the parameters.
	#
	# The idea is that your plugin would modify the apropriate $data[$field_id] values with the 
	# blocked message instead of the field data, depending on the settings in $row, $mode and $admin
	
	$original_value = $data[$row['field_id']]; // store the original value to compare after return from the plugin
	JBPLUG_do_callback('process_field_restrictions', $data, $row, $mode, $admin);
	if (strcmp($original_value, $data[$row['field_id']])!==0) {
		$is_restricted = true;
		//return true; // return true if the value was changed (changed by a plugin in this case)
	}
	

	return $is_restricted;

}


###############################################################
function JB_delete_field ($field_id) {
	
	$field_id = (int) $field_id;

	$sql = "SELECT * FROM form_fields WHERE  field_id='".JB_escape_sql($field_id)."'";
	$result = JB_mysql_query ($sql) or die(mysql_error().$sql);
	$row = mysql_fetch_array($result, MYSQL_ASSOC) ;

	// delete codes
	if (($row['field_type']=='CHECK') || ($row['field_type']=='RADIO') || ($row['field_type']=='MSELECT')) {
		$sql = "DELETE FROM codes where field_id='".JB_escape_sql($field_id)."' ";
		$result = JB_mysql_query ($sql) or die(mysql_error().$sql);

	}
	// delete the field and any translations
	$sql = "DELETE FROM `form_fields` WHERE field_id='".JB_escape_sql($field_id)."' ";
	JB_mysql_query($sql) or die (mysql_error());

	$sql = "DELETE FROM `form_field_translations` WHERE field_id='".JB_escape_sql($field_id)."' ";
	JB_mysql_query($sql) or die (mysql_error());

	$sql = "DELETE FROM `form_lists` WHERE field_id='".JB_escape_sql($field_id)."'  ";
	JB_mysql_query($sql) or die (mysql_error());

	JBPLUG_do_callback('delete_dynamic_field', $field_id);

	$_REQUEST['mode'] = 'EDIT'; // interface stays in edit mode


}

###############################################################
function JB_save_field($error, $NEW_FIELD) {

	

	$_REQUEST['field_sort'] = (int) $_REQUEST['field_sort'];
	$_REQUEST['field_width'] = (int) $_REQUEST['field_width'];
	$_REQUEST['field_height'] = (int) $_REQUEST['field_height'];
	$_REQUEST['list_sort_order'] = (int) $_REQUEST['list_sort_order'];
	$_REQUEST['category_init_id'] = (int) $_REQUEST['category_init_id'];
	$_REQUEST['search_sort_order'] = (int) $_REQUEST['search_sort_order'];
	$_REQUEST['cat_multiple_rows'] = (int) $_REQUEST['cat_multiple_rows'];

	if ($_REQUEST['field_type']=='GMAP') {

		if (!$_REQUEST['field_width']) {
			$_REQUEST['field_width'] = 300;
		}
		if (!$_REQUEST['field_height']) {
			$_REQUEST['field_height'] = 400;
		}

	}

	if ($_REQUEST['field_type']=='EDITOR') {

	}
	
	if ($NEW_FIELD == "YES") {

		$sql = "INSERT INTO `form_fields` ( `form_id`  , `reg_expr` , `field_label` , `field_type` , `field_sort` , `is_required` , `display_in_list` , `error_message` , `field_init`, `field_width`, `field_height`, `is_in_search`, `list_sort_order`, `search_sort_order`, `template_tag`, `section`, `is_hidden`, `is_anon`, `field_comment`, `category_init_id`, `is_cat_multiple`, `cat_multiple_rows`, `is_blocked`, `multiple_sel_all`, `is_member`) VALUES ('".JB_escape_sql($_REQUEST['form_id'])."',  '".JB_escape_sql($_REQUEST['reg_expr'])."', '".JB_escape_sql($_REQUEST['field_label'])."', '".JB_escape_sql($_REQUEST['field_type'])."', '".JB_escape_sql($_REQUEST['field_sort'])."', '".JB_escape_sql($_REQUEST['is_required'])."', '".JB_escape_sql($_REQUEST['display_in_list'])."', '".JB_escape_sql($_REQUEST['error_message'])."', '".JB_escape_sql($_REQUEST['field_init'])."', '".JB_escape_sql($_REQUEST['field_width'])."', '".JB_escape_sql($_REQUEST['field_height'])."', '".JB_escape_sql($_REQUEST['is_in_search'])."', '".JB_escape_sql($_REQUEST['list_sort_order'])."', '".JB_escape_sql($_REQUEST['search_sort_order'])."', '".JB_escape_sql($_REQUEST['template_tag'])."', '".JB_escape_sql($_REQUEST['section'])."', '".JB_escape_sql($_REQUEST['is_hidden'])."', '".JB_escape_sql($_REQUEST['is_blcoked'])."', '".JB_escape_sql($_REQUEST['field_comment'])."', '".JB_escape_sql($_REQUEST['category_init_id'])."', '".JB_escape_sql($_REQUEST['is_cat_multiple'])."', '".JB_escape_sql($_REQUEST['cat_multiple_rows'])."', '".JB_escape_sql($_REQUEST['is_blocked'])."', '".JB_escape_sql($_REQUEST['multiple_sel_all'])."', '".JB_escape_sql($_REQUEST['is_member'])."' )";

		
	} else {

		//if ($_SESSION["LANG"] == "EN") {

			$sql = "SELECT * FROM form_fields WHERE field_id='".JB_escape_sql($_REQUEST['field_id'])."' ";
			$result = JB_mysql_query ($sql) or die(mysql_error().$sql);
			$row = mysql_fetch_array($result, MYSQL_ASSOC);

			

			if ((JB_is_reserved_template_tag($_REQUEST['template_tag'])) && (true)) {
				$tt = ""; // do not update template tag

				
			} elseif ($_REQUEST['template_tag']!='') {
				$tt = "`template_tag` = '".JB_escape_sql($_REQUEST['template_tag'])."',";
				
			}

			$sql = "UPDATE `form_fields` SET ".
				"`reg_expr` = '".JB_escape_sql($_REQUEST['reg_expr'])."',".
				"`field_label` = '".JB_escape_sql($_REQUEST['field_label'])."',".
				"`field_type` = '".JB_escape_sql($_REQUEST['field_type'])."',".
				
				"`field_init` = '".JB_escape_sql($_REQUEST['field_init'])."',".
				"`is_required` = '".JB_escape_sql($_REQUEST['is_required'])."',".
				"`field_width` = '".JB_escape_sql($_REQUEST['field_width'])."',".
				"`field_height` = '".JB_escape_sql($_REQUEST['field_height'])."',".
				"`is_in_search` = '".JB_escape_sql($_REQUEST['is_in_search'])."',".
			
				"`search_sort_order` = '".JB_escape_sql($_REQUEST['search_sort_order'])."',".
				"`section` = '".JB_escape_sql($_REQUEST['section'])."',".
				$tt.
				"`error_message` = '".JB_escape_sql($_REQUEST['error_message'])."',".
				"`is_hidden` = '".JB_escape_sql($_REQUEST['is_hidden'])."', ".
				"`is_anon` = '".JB_escape_sql($_REQUEST['is_anon'])."', ".
				"`is_cat_multiple` = '".JB_escape_sql($_REQUEST['is_cat_multiple'])."', ".
				"`cat_multiple_rows` = '".JB_escape_sql($_REQUEST['cat_multiple_rows'])."', ".
				"`field_comment` = '".JB_escape_sql($_REQUEST['field_comment'])."', ".
					"`multiple_sel_all` = '".JB_escape_sql($_REQUEST['multiple_sel_all'])."', ".
				"`is_blocked` = '".JB_escape_sql($_REQUEST['is_blocked'])."', ".
					"`is_prefill` = '".JB_escape_sql($_REQUEST['is_prefill'])."', ". 
					"`is_member` = '".JB_escape_sql($_REQUEST['is_member'])."', ".
					
				"category_init_id = '".JB_escape_sql($_REQUEST['category_init_id'])."' ".
				"WHERE `field_id` = '".JB_escape_sql($_REQUEST['field_id'])."'  ;";

		
		// update template tag on the form_lists 

		if ($_REQUEST['template_tag']!='') { // sometimes template tag can be blank (reserved tags)

			$sql_tt = "UPDATE form_lists SET `template_tag`='".JB_escape_sql($_REQUEST['template_tag'])."' WHERE `field_id`='".JB_escape_sql($_REQUEST['field_id'])."'";
			JB_mysql_query ($sql_tt) or die ($sql.mysql_error());
		}

		

	}


	// Do the SQL query, UPDATE or INSERT

	
	JB_mysql_query($sql) or die ($sql.mysql_error());



	if ($_REQUEST['field_id']==false) {
		$_REQUEST['field_id'] = jb_mysql_insert_id();
	}

	// update translations
	$label = $_REQUEST['field_label'];
	$sql_fft = "RePLACE INTO `form_field_translations` (`field_id`, `lang`, `field_label`, `error_message`, `field_comment`) VALUES ('".JB_escape_sql($_REQUEST['field_id'])."', '".JB_escape_sql($_SESSION["LANG"])."', '".JB_escape_sql($label)."', '".JB_escape_sql($_REQUEST['error_message'])."', '".JB_escape_sql($_REQUEST['field_comment'])."' )";
	JB_mysql_query ($sql_fft) or die ($sql.mysql_error());



	if (($_REQUEST['field_type']=='RADIO') || ($_REQUEST['field_type']=='CHECK') || ($_REQUEST['field_type']=='MSELECT') || ($_REQUEST['field_type']=='SELECT')) {
		//echo 'formatting field..<br>';
		if ($NEW_FIELD=='YES') {
			$_REQUEST['field_id'] = JB_mysql_insert_id();
		}
		JB_format_codes_translation_table ($_REQUEST['field_id']);
	}

	if ($NEW_FIELD=='YES') {
		$field_id = JB_mysql_insert_id();

	} else {
		$field_id = $_REQUEST['field_id'];
	}

	JB_cache_del_keys_for_form($_REQUEST['form_id']);
	

	$_REQUEST['mode'] = 'EDIT'; 
	global $NEW_FIELD;
	$_REQUEST['NEW_FIELD'] = 'NO';
	
	return $field_id;



}
###############################################################
# Admins can only access this function
function JB_validate_field_form () {

	$_REQUEST['form_id'] = (int) $_REQUEST['form_id'];

	if (JB_CLEAN_STRINGS=='YES') { // trim all fields... ?
		foreach ($_REQUEST as $key=>$val) {
			$_REQUEST[$key] =  trim($val);
		}

	}

	

	if ($_REQUEST['field_type'] == false) {
		$error .= "<FONT SIZE='' COLOR='#000000'><b>- Type of field is not selected.</B></FONT><br>";
	}

	if (($_REQUEST['field_type'] == 'CATEGORY') && ($_REQUEST['category_init_id']==false)) {
		$error .= "<FONT SIZE='' COLOR='#000000'><b> ".$_REQUEST['field_label']." (#".$_REQUEST['field_id'].") - Need to specify the initial category if the field type is a Category. (Paramaters)</B></FONT><br>";
	}


	if (($_REQUEST['is_required'] != false) && ($_REQUEST['reg_expr'] == false)) {
		$error .= "<FONT SIZE='' COLOR='#000000'><b>- The field is required, but 'Type of Check' was not selected.</B></FONT><br>";
	}

	if (($_REQUEST['is_required'] != false) && ($_REQUEST['error_message'] == false)) {
		$error .= "<FONT SIZE='' COLOR='#000000'><b>- The field is required, but 'Error message' was not filled in.</B></FONT><br>";
	}

	if (JB_is_reserved_template_tag($_REQUEST['template_tag'])) {

		$error .= "<FONT SIZE='' COLOR='#000000'><b>- Template Tag name is reserved by the system. Please choose a different template tag name.</B></FONT><br>";
		$_REQUEST['template_tag'] = "";

	}

	if (($_REQUEST['template_tag'] == false) && (!JB_is_reserved_field($_REQUEST['field_id']))) {
			$error .= "<FONT SIZE='' COLOR='#000000'><b>- Template Tag is blank.</B></FONT><br>";
	} 
	
	if ($_REQUEST['template_tag']!='') {

		// check template tag for duplicates...

		if ($_REQUEST['field_id']!='') {
			$f_id_sql = "AND field_id != '".jb_escape_sql($_REQUEST['field_id'])."' ";
		}

		$sql = "select field_id from form_fields where template_tag='".JB_escape_sql($_REQUEST['template_tag'])."' and form_id='".JB_escape_sql($_REQUEST['form_id'])."' $f_id_sql  ";
		//echo $sql;
		$result = JB_mysql_query($sql)or die ($sql.mysql_error());
		if (mysql_num_rows($result)>0) {
			$error .= "<FONT SIZE='' COLOR='#000000'><b>- Template Tag is already in use. Please try a different name.</B></FONT><br>";
		}

		$f_id_sql = '';

	}


	if ($_REQUEST['field_id']!='') {
		$sql = "SELECT * FROM form_fields WHERE field_id='".JB_escape_sql($_REQUEST['field_id'])."' ";
		$result = JB_mysql_query ($sql) or die(mysql_error());
		$row = mysql_fetch_array($result, MYSQL_ASSOC);

		if (JB_get_definition($row['field_type']) !== JB_get_definition($_REQUEST['field_type'])) { // only change the table structure if column type is different
			
			$error .= "<FONT SIZE='' COLOR='#000000'><b>- Cannot change this field type to '".htmlentities($_REQUEST['field_type'])."' because database types are incompatible. If you would like to continue anyway, please check the check box field below the 'Save' button.</b>";

			$_REQUEST['allow_anyway'] = 'true';

			if ($_REQUEST['do_alter'] != '') {

				if (!JB_schema_change_table($_REQUEST['form_id'], $_REQUEST['field_id'], $_REQUEST['field_type'], $row['field_label'])) {
					$error = "Something went wrong... Please check your error log";

				} else {
					$_REQUEST['allow_anyway'] = '';
					$error = "";
					$_REQUEST['do_alter'] = "";
				}
				
			}

		}

	}

	JBPLUG_do_callback('validate_dynamic_field', $error);

	return $error;


}

##############################################################

# Shortens a string so that it will fit in to the database.
# Uses the $field_type to determine the max size of the field
# Assuming $value is a string, Latin-1 encoded with non-Latin 1 encoded as
# html entities.
function jb_fit_to_db_size($field_type, $value) {

	if (!is_string($value)) return $value; // only works on strings.

	$def = JB_get_definition($field_type);
	if (is_array($def)) return;
	$def = strtoupper($def);
	if (strpos($def, 'CHAR')!==false) {

		// extract the size

		preg_match ('/\d+/', $def, $m);

		if (is_numeric($size = $m[0])) { // get the first match
			
			$temp_str = substr($value, 0, 255);
			if (preg_match('/&#?\d*?$/', $temp_str, $m, PREG_OFFSET_CAPTURE)) {
				$offset = $m[0][1];
				$temp_str = substr($value, 0, $offset);
			}
			$value = $temp_str;
		}



	}

	
	return $value;

	
}

##############################################################

function JB_validate_form_data($form_id) {

	$DynamicForm = &JB_get_DynamicFormObject($form_id);
	return $DynamicForm->JB_validate_form_data(); 
}

###############################################################

function JB_init_data_from_request($form_id, &$data ) {

	$DynamicForm = &JB_get_DynamicFormObject($form_id);
	return $DynamicForm->init_data_from_request($data); 


}
###############################################################
function JB_field_form($NEW_FIELD, $data, $form_id) {

	if ((($_REQUEST['save'] == false) &&($_REQUEST['field_id']!=false)) && ($data['error'] == false)) {

		// load in the values

	

		$sql = "SELECT *, t2.field_comment AS FCOMMENT, t2.field_label AS LABEL, t2.error_message AS ERRMSG FROM form_fields AS t1, form_field_translations AS t2 WHERE t1.field_id=t2.field_id AND lang='".JB_escape_sql($_SESSION['LANG'])."' AND t1.field_id='".JB_escape_sql($_REQUEST['field_id'])."'";
	
		$result = JB_mysql_query($sql) or die(mysql_error());
		$row = mysql_fetch_array($result, MYSQL_ASSOC);

		$row['field_comment'] = $row['FCOMMENT'];
		$row['field_label'] = $row['LABEL'];
		$row['error_message'] = $row['ERRMSG'];

	} else {
		$row['field_id'] = $_REQUEST['field_id'];
		$row['form_id'] = $_REQUEST['form_id'];
		$row['field_label'] = $_REQUEST['field_label'];
		$row['field_sort'] = $_REQUEST['field_sort'];
		$row['field_type'] = $_REQUEST['field_type'];
		$row['is_required'] = $_REQUEST['is_required'];
		$row['display_in_list'] = $_REQUEST['display_in_list'];
		$row['reg_expr'] = $_REQUEST['reg_expr'];
		$row['error_message'] = $_REQUEST['error_message'];
		$row['field_init'] = $_REQUEST['field_init'];
		$row['field_width'] = $_REQUEST['field_width']; 
		$row['field_height'] = $_REQUEST['field_height'];
		$row['is_in_search'] = $_REQUEST['is_in_search'];
		$row['template_tag'] = $_REQUEST['template_tag'];
		$row['section'] = $_REQUEST['section'];
		$row['list_sort_order'] = $_REQUEST['list_sort_order'];
		$row['search_sort_order'] = $_REQUEST['search_sort_order'];
		$row['field_comment'] = $_REQUEST['field_comment'];
		$row['is_hidden'] = $_REQUEST['is_hidden'];
		$row['is_anon'] = $_REQUEST['is_anon'];
		$row['is_blocked'] = $_REQUEST['is_blocked'];
		$row['is_prefill'] = $_REQUEST['is_prefill'];
		$row['is_member'] = $_REQUEST['is_member'];
		$row['multiple_sel_all'] = $_REQUEST['multiple_sel_all'];
		$row['category_init_id'] = $_REQUEST['category_init_id'];
		$row['is_cat_multiple'] = $_REQUEST['is_cat_multiple'];
		$row['cat_multiple_rows'] = $_REQUEST['cat_multiple_rows'];

		//if (get_magic_quotes_gpc()) { // grrr! assuming mc is always on
			$row['field_label'] = stripslashes($row['field_label']);
			$row['field_comment'] = stripslashes($row['field_comment']);
			$row['error_message'] = stripslashes($row['error_message']);
			$row['template_tag'] = stripslashes($row['template_tag']);
			$row['field_init'] = stripslashes($row['field_init']);

		//}
	
	}

	JBPLUG_do_callback('init_field_form_values', $row, $form_id);


?>
	<?php

		if ($row['template_tag']=='') {

			
			// try to get template tag from the database (It could be blank because it was reserved)

			$sql = "SELECT * FROM form_fields AS t1, form_field_translations AS t2 WHERE t1.field_id=t2.field_id AND lang='".JB_escape_sql($_SESSION['LANG'])."' AND t1.field_id='".JB_escape_sql($_REQUEST['field_id'])."'";
		
			$temp_result = JB_mysql_query($sql) or die(mysql_error());
			$temp_row = mysql_fetch_array($temp_result, MYSQL_ASSOC);

			$row['template_tag'] = $temp_row['template_tag'];


		}

		if (JB_is_reserved_template_tag($row['template_tag'])) {
			$disabled = " disabled ";

		}

		
		
	?>

<form method="POST" name="form2" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post" >
<input type="hidden" name="form_id" value="<?php echo jb_escape_html($form_id);?>">
<input type="hidden" name="NEW_FIELD" value="<?php echo jb_escape_html($_REQUEST['NEW_FIELD']); ?>">
<input type="hidden" name="field_id" value="<?php echo jb_escape_html($row['field_id']); ?>">
<input type="hidden" name="mode" value="<?php echo jb_escape_html($_REQUEST['mode']); ?>">
<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" >
  <tr>
    <td colspan="2"><?php if ($NEW_FIELD=='NO') { echo "<font face='Arial' size='2'><b>[EDIT FIELD]</b></font>"; } else { echo "<font face='Arial' size='2'><b>[ADD NEW FIELD]</b></font>";} ?><br><input class="form_submit_button" type="submit" value="Save" name="save"><?php if ($NEW_FIELD=='NO') { ?> <input type="submit"  value="Delete" name="delete" onClick="return confirmLink(this, 'Delete this field, are you sure?')"><?php }

	if ($_REQUEST['allow_anyway'] != '') {

		echo "<br><input type='checkbox' name='do_alter'><font color='red'>Change the field's Database Type</font> (This will delete any previous data stored in the field)";


	}
	?></td>
  </tr>
  <tr bgColor="#ffffff">
    <td><font face="Arial" size="2"><b>Field label</b></font></td>
    <td>
  <input type="text" name="field_label" size="27" value="<?php echo jb_escape_html($row['field_label']);?>" ></td>
  </tr>
  <tr bgcolor="#ffffff">
    <td><font face="Arial" size="2" ><b>Type<font color="#ff0000"><b>*</b></font></b></font></td>
    <td><select size="5" name="field_type" >
   <option value="BLANK" <?php if ($row['field_type']=='BLANK') { echo " selected ";} ?>>Blank Space</option>
   <option value="CATEGORY" <?php if ($row['field_type']=='CATEGORY') { echo " selected ";} ?> >Category</option>
   <option value="CHECK" <?php if ($row['field_type']=='CHECK') { echo " selected ";} ?>>Check Boxes</option> 
   <option value="CURRENCY" <?php if ($row['field_type']=='CURRENCY') { echo " selected ";} ?>>Currency</option>
	<option value="DATE" <?php if ($row['field_type']=='DATE') { echo " selected ";} ?>>Date</option>
	<option value="DATE_CAL" <?php if ($row['field_type']=='DATE_CAL') { echo " selected ";} ?>>Date - Calendar</option>
	<option value="FILE" <?php if ($row['field_type']=='FILE') { echo " selected ";} ?>>File</option>
	<option value="GMAP" <?php if ($row['field_type']=='GMAP') { echo " selected ";} ?>>Google Map</option>
	
	<option value="IMAGE" <?php if ($row['field_type']=='IMAGE') { echo " selected ";} ?>>Image</option>
	<option value="INTEGER" <?php if ($row['field_type']=='INTEGER') { echo " selected ";} ?>>Integer</option>
	<?php

		//if ($form_id==1) {  // HTML editor is for job posts only.
		
	?>
	 <option value="EDITOR" <?php if ($row['field_type']=='EDITOR') { echo " selected ";} ?> >HTML Editor</option>
	 <?php

	//	}

		?>
	<option value="MSELECT" <?php if ($row['field_type']=='MSELECT') { echo " selected ";} ?>>Multiple Select</option>
	<option value="NOTE" <?php if ($row['field_type']=='NOTE') { echo " selected ";} ?>>Note</option>
	<option value="NUMERIC" <?php if ($row['field_type']=='NUMERIC') { echo " selected ";} ?>>Numeric</option>
	<option value="RADIO" <?php if ($row['field_type']=='RADIO') { echo " selected ";} ?>>Radio Buttons</option>
	<option value="SEPERATOR" <?php if ($row['field_type']=='SEPERATOR') { echo " selected ";} ?> >Seperator</option>
	<option value="SELECT" <?php if ($row['field_type']=='SELECT') { echo " selected ";} ?>>Single Select</option>
	<?php

		if ($form_id==2) {  // skill matrix is for resumes only.
		
	?>
	<option value="SKILL_MATRIX" <?php if ($row['field_type']=='SKILL_MATRIX') { echo " selected ";} ?>>Skill Matrix</option>

	<?php

		}

	?>
	<option value="TEXTAREA" <?php if ($row['field_type']=='TEXTAREA') { echo " selected ";} ?> >Text Editor</option>
	 <option value="TEXT" <?php if ($row['field_type']=='TEXT') { echo " selected ";} ?>  >Text Field</option>
	 <option value="URL" <?php if ($row['field_type']=='URL') { echo " selected ";} ?>  >URL</option>
	 <option value="YOUTUBE" <?php if ($row['field_type']=='YOUTUBE') { echo " selected ";} ?>  >YouTube Video</option>
	 <?php echo JBPLUG_do_callback('echo_field_type_option', $row, $form_id); ?>
    </select></td>
  </tr>
  <tr bgcolor="#ffffff">
    <td><font face="Arial" size="2"><b>Initial Value</b></font></td>
    <td>
  <input type="text" name="field_init" value="<?php echo jb_escape_html($row['field_init']);?>" size="3"><font size='2'> (Default value for text fields, can be left blank.) </font></td>
  </tr>
  <!-- tr bgcolor="#ffffff">
    <td><font face="Arial" size="2"><b>Sort order<font color="#ff0000"><b>*</b></font></b></font></td>
    <td>
  <input type="text" name="field_sort" value="<?php echo $row[field_sort];?>" size="3"><font size='2'> (1=first, 2=2nd, etc) </font></td>
  </tr-->
  <tr bgcolor="#ffffff">
    <td><font face="Arial" size="2"><b>Section<font color="#ff0000"><b>*</b></font></b></font></td>
    <td>
	<select name="section">
		<option value='1' <?php if ($row['section']=='1') {echo " selected "; }?> >1</option>
		<option value='2' <?php if ($row['section']=='2') {echo " selected "; }?>>2</option>
		<?php 
		if ($form_id < 4) {
		?>
		<option value='3' <?php if ($row['section']=='3') {echo " selected "; }?>>3</option>

		<?php

		}

		if (($form_id===1) && (JB_MAP_DISABLED=='GMAP')) {
		
		?>
		<option value='4' <?php if ($row['section']=='4') {echo " selected "; }?>>4 (Map)</option>

		<?php

		}


		?>
	</select>
   </td>
  </tr>
  <tr bgColor="#eaeaea">
    <td colspan="2">Validation (only required fields are validated)</td>
  </tr>
  <tr bgcolor="#ffffff">
    <td><font face="Arial" size="2"><b>Is Required?</b></font></td>
    <td><input type="checkbox" name="is_required" value="Y" <?php if ($row['is_required']=='Y') { echo " checked ";} ?>></td>
  </tr>
  <tr bgcolor="#ffffff">
    <td><font face="Arial" size="2"><b>Type of check</b></font></td>
    <td>
	<select name="reg_expr">
	<option value="" <?php if ($row['reg_expr']=='') { echo " selected "; } ?>>[Select]</option>
	<option value="not_empty" <?php if ($row['reg_expr']=='not_empty') { echo " selected "; } ?> >Must not be empty</option>
	<option value="email" <?php if ($row['reg_expr']=='email') { echo " selected "; } ?> >Valid Email</option>
	<option value="date" <?php if ($row['reg_expr']=='date') { echo " selected "; } ?> >Valid Date</option>
	<option value="numeric" <?php if ($row['reg_expr']=='numeric')  { echo " selected "; } ?> >Must be numeric</option>
	</select>
 </td>
  </tr>
  <tr bgcolor="#ffffff">
    <td><font face="Arial" size="2"><b>Error message</b></font></td>
    <td>
  <input type="text" name="error_message" size="27" value="<?php echo jb_escape_html($row['error_message']);?>">(The reason for the error. Eg: <i>was not filled in</i> or <i>was invalid</i> for email.)</td>
  </tr>
  <tr bgColor="#eaeaea">
    <td colspan="2">Display</td>
  </tr>
  <!-- tr bgcolor="#ffffff">
    <td><font face="Arial" size="2"><b>Display in list?</b></font></td>
    <td><input type="checkbox" name="display_in_list" value="Y" <?php if ($row[display_in_list]=='Y') { echo " checked ";} ?>  >
	<font face="Arial" size="2">Column Order:</font><input type="text" name="list_sort_order" value="<?php echo $row[list_sort_order];?>" size="2"></td>
  </tr -->
  <tr bgcolor="#ffffff">
    <td><font face="Arial" size="2"><b>Is on search form?</b></font></td>
    <td><?php if ($row['field_type'] == 'GMAP') { echo 'The Google maps field does not support search in this version'; } else { ?><input type="checkbox" name="is_in_search" value="Y" <?php if ($row['is_in_search']=='Y') { echo " checked ";} ?>  >
	<font face="Arial" size="2">Sort Order:</font><input type="text" name="search_sort_order" value="<?php echo $row['search_sort_order'];?>" size="2">(1=first)<?php	  
  }
  ?>
  </td>
  </tr>
  <tr bgcolor="#ffffff">
    <td><font face="Arial" size="2"><b>Template Tag <font color="#ff0000"><b>*</b></font></b></font></td>
    <td>

  <input type="text" name="template_tag" <?php echo $disabled; ?> size="20" value="<?php echo jb_escape_html($row['template_tag']);?>"> (a unique identifier for this field)</td>
  </tr>
  <tr bgColor="#eaeaea">
    <td colspan="2">Parameters</td>
  </tr>
  <tr bgcolor="#ffffff">
    <td><font face="Arial" size="2"><b>Width</b></font></td>
    <td>
  <input type="text" name="field_width" size="3" value="<?php echo jb_escape_html($row['field_width']);?>"></td>
  </tr>
  <tr bgcolor="#ffffff">
    <td><font face="Arial" size="2"><b>Height</b></font></td>
    <td>
  <input type="text" name="field_height" size="3" value="<?php echo jb_escape_html($row['field_height']);?>"><font size='2'>(for textareas or multiple selects)</font></td>
  </tr>
 
  <tr bgcolor="#ffffff">
    <td><font face="Arial" size="2"><b>Is hidden from website?</b></font>
  </td>
    <td><input type="checkbox" name="is_hidden" <?php if ($row['is_hidden']=='Y') { echo " checked ";} ?> value="Y"><font size='2'>Is hidden from website. Only visibile on the editing form (and to Admins).</font></td>
  </tr>
  <?php if ($form_id==2) { // only resumes ?>
  <tr bgcolor="#ffffff">
    <td><font face="Arial" size="2"><b>Can be anonymous?</b></font>
  </td>
    <td><input type="checkbox" name="is_anon" <?php if ($row['is_anon']=='Y') { echo " checked ";} ?> value="Y"><font size='2'>(Can be anonymous on resumes. If this feature is enabled, users can hide this field and reveal after responding to Employer's request.)</font></td>
  </tr>
  <?php } ?>
  <?php if ($form_id==2) { // only resumes ?>
  <tr bgcolor="#ffffff">
    <td><font face="Arial" size="2"><b>Is blocked?</b></font>
  </td>
    <td><input type="checkbox" name="is_blocked" <?php if ($row['is_blocked']=='Y') { echo " checked ";} ?> value="Y"><font size='2'>(Can be subjected to blocking. A field is un-blocked for users who are subscribed to the resume DB or received an application from a corresponding candidate. Blocking options are set in Main Config.  )</font></td>
  </tr>
  <?php } ?>
	<tr bgcolor="#ffffff">
    <td><font face="Arial" size="2"><b>Is Members Only?</b></font>
  </td>
    <td><input type="checkbox" name="is_member" <?php if ($row['is_member']=='Y') { echo " checked ";} ?> value="Y"><font size='2'>(Can be subjected to Membership. Membership options are set in Main Config.  )</font></td>
  </tr>
  
  <?php if ($form_id==1) { // only job posts can be pre-filled ?>
   <tr bgcolor="#ffffff">
    <td><font face="Arial" size="2"><b>Pre-fill?</b></font>
  </td>
    <td><input type="checkbox" name="is_prefill" <?php if ($row['is_prefill']=='Y') { echo " checked ";} ?> value="Y"><font size='2'>(Attempt to pre-fill the field with data from the previous record)</font></td>
  </tr>
   <?php } ?>
   <tr bgcolor="#ffffff">
    <td><font face="Arial" size="2"><b>Field Comment</b></font>
  </td>
    <td><input type="text" name="field_comment" value="<?php echo jb_escape_html($row['field_comment']); ?>"><font size='2'>(Comment to be displayed next to the field, like the one you are reading now.)</font></td>
  </tr>
   <tr bgcolor="#ffffff">
    <td><font face="Arial" size="2"><b>Category</b></font></td>
    <td>
	<input type="button" onclick="window.open('selectcat_window.php?field_id=<?php echo $row['field_id'];?>&form_id=<?php echo $form_id; ?>', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=500,height=500,left = 50,top = 50');return false;" value="Select Category..." >

  <input type="hidden" name="category_init_id"  value="<?php echo jb_escape_html($row['category_init_id']);?>" size="3"><font size='2'> (If field is a category, select the initial category) </font>
  Currently Selected:<br><input type="text" disabled name="category_init_name"  value="<?php echo  jb_escape_html(JB_getCatName($row['category_init_id']))." (#".jb_escape_html($row['category_init_id']).")" ;?> " size="30"><br>
  <input type="checkbox" name="is_cat_multiple" <?php if ($row['is_cat_multiple']=='Y') { echo " checked ";} ?> value="Y"><font size='2'>Multiple Categories can be selected when searching, with <input type="text" value="<?php echo $row['cat_multiple_rows'];?>" size='1' name='cat_multiple_rows'> rows showing on the search form.</font> And, <input type="checkbox" name="multiple_sel_all" <?php if ($row['multiple_sel_all']=='Y') { echo " checked ";} ?> value="Y"> the first option selects all. 
  </td>
  </tr>
</table>
<input class="form_submit_button" type="submit" value="Save" name="save">
</form>

<?php

}

#######################################################
# deprecated, see JBDynamicFormLayut class instead 
function JB_form_text_field (&$field_name, &$field_value, &$width) {
	return 'JB_form_text_field() is deprecated';
	return '<input class="dynamic_form_text_style" type="text" AUTOCOMPLETE="ON" name="'.$field_name.'" value="'.(JB_escape_html($field_value)).'" size="'.$width.'" >';
	
}

#######################################################
# deprecated, see JBDynamicFormMarkup class instead
function JB_form_file_field ($field_name, $field_value) {
	return 'JB_form_file_field is deprecated';
	#return '<input type="hidden" name="MAX_FILE_SIZE" value="'.JB_MAX_UPLOAD_BYTES.'"><input class="dynamic_form_text_style" type="file" name="'.$field_name.'"   >';

	
}

#######################################################
# deprecated by JBDynamicFormMarkup class
function JB_form_image_field ($field_name, $field_value) {
	return 'JB_form_image_field is deprecated';
	#return '<input type="hidden" name="MAX_FILE_SIZE" value="'.JB_MAX_UPLOAD_BYTES.'"><input class="dynamic_form_text_style" type="file" name="'.$field_name.'"  size="'.$width.'" >';

	
}

###########################################################
# deprecated by JBDynamicFormMarkup class
function JB_form_editor_field ($field_name, $field_value, $width, $height) {
	return 'JB_form_editor_field is deprecated';
/*
	require_once(JB_basedirpath()."include/lib/fckeditor/fckeditor.php") ;

	ob_start();

	if (!$height) {
		$height = 22;
	}

	// JB_BASE_HTTP_PATH

	$oFCKeditor = "oFCKeditor".$field_name;

	$$oFCKeditor = new FCKeditor($field_name) ;
	$$oFCKeditor->BasePath	= '../fckeditor/' ;
	$$oFCKeditor->ToolbarSet = 'Basic';
	$$oFCKeditor->Value		= $field_value ;
	$$oFCKeditor->Height = $height*15;
	$$oFCKeditor->Width = "100%";
	$$oFCKeditor->Create() ;
	$html = ob_get_contents();
	ob_end_clean();
	return $html;
	*/

	
}
###########################################################

function JB_form_textarea_field ($field_name, $field_value, $width, $height) {
	return 'JB_form_textarea_field() is deprecated';
	//return '<TEXTAREA  name="'.$field_name.'" cols="'.$width.'" rows="'.$height.'">'.(JB_escape_html($field_value)).'</TEXTAREA>';

	
}



#######################################################################

function JB_form_date_field ($field_name, $day, $month, $year) {

	if (func_num_args()>4) {
		$class = func_get_arg(4);
	}

	if (!defined('JB_DATE_INPUT_SEQ')) {
		define ('JB_DATE_INPUT_SEQ', 'YMD');
	}

	$DFM = &JB_get_DynamicFormMarkupObject();

	$sequence = JB_DATE_INPUT_SEQ;
	
	global $label;

	$DFM->date_field_open();

	while ($widget = substr($sequence, 0, 1)) {

		switch ($widget) {
			case 'Y':
				$DFM->date_year($year, $field_name, $class);
				break;

			case 'M':
				$DFM->date_month($month, $field_name, $class);
				break;
		
			case 'D':
				$DFM->date_day($day, $field_name, $class);
				break;
		}
		$sequence = substr($sequence, 1);

	}
	$DFM->date_field_close();

}



#######################################################################


function JB_category_select_field ($field_name, $category_id, $selected) {
	global $label;

	$DFM = &JB_get_DynamicFormMarkupObject(); // render using JBDynamicFormLayout class

	$DFM->category_select_open($field_name);
	$DFM->category_first_option();
	JB_category_option_list($category_id, $selected, $DFM); 
	$DFM->category_select_close();


}
################################################################

function JB_form_select_field ($field_id, $selected) {

	global $label;

	$DFM = &JB_get_DynamicFormMarkupObject();

	if (JB_CODE_ORDER_BY=='BY_NAME') {
		$order_by = 'description';
	} else {
		$order_by = 'code';
	}

	if ($_SESSION['LANG'] !='') {

		$sql = "SELECT * FROM `codes_translations` WHERE `field_id`='".JB_escape_sql($field_id)."' and lang='".JB_escape_sql($_SESSION['LANG'])."' order by ".$order_by;
		
	} else {
		$sql = "SELECT * FROM `codes` WHERE `field_id`='".JB_escape_sql($field_id)."' order by ".$order_by;
	}

	$result = JB_mysql_query ($sql) or die (mysql_error());
	$DFM->single_select_open($field_id);
	$DFM->single_select_first_option();
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$checked = ($row['code']==$selected)? ' selected ': '';
		$DFM->single_select_option($row, $checked);
	}
	$DFM->single_select_close();

}

################################################################

function JB_form_radio_field ($field_id, $selected) {

	$DFM = &JB_get_DynamicFormMarkupObject();

	if (JB_CODE_ORDER_BY=='BY_NAME') {
		$order_by = 'description';
	} else {
		$order_by = 'code';
	}
	if ($_SESSION['LANG'] !='') {
		$sql = "SELECT * FROM `codes_translations` WHERE `field_id`='".JB_escape_sql($field_id)."' and lang='".JB_escape_sql($_SESSION['LANG'])."' order by ".$order_by;	
	} else {
		$sql = "SELECT * FROM `codes` WHERE `field_id`='".JB_escape_sql($field_id)."' order by ".$order_by;
	}
	$result = JB_mysql_query ($sql) or die (mysql_error());
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$checked = ($row['code']==$selected)? ' checked ':'';
		$DFM->radio_button($row, $checked);
	}

}

################################################################

function JB_form_checkbox_field ($field_id, $selected, $mode) {

	$DFM = &JB_get_DynamicFormMarkupObject($mode);

	if (JB_CODE_ORDER_BY=='BY_NAME') {
		$order_by = 'description';
	} else {
		$order_by = 'code';
	}

	if ($_SESSION['LANG'] !='') {

		$sql = "SELECT * FROM `codes_translations` WHERE `field_id`='".JB_escape_sql($field_id)."' and lang='".JB_escape_sql($_SESSION['LANG'])."' order by ".$order_by;
		
	} else {
		$sql = "SELECT * FROM `codes` WHERE `field_id`='".JB_escape_sql($field_id)."' order by ".$order_by;
	}
	
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$checked_codes = explode (",", $selected);

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		if (in_array($row['code'], $checked_codes)) {
			$checked = " checked ";
		} else {
			$checked = "";
		}

		if (($mode=='view') && ($checked != ''))  {
			//$disabled = " disabled  ";
			echo $comma.$row['description'];
			$comma = ", ";
		} elseif (($mode!='view')) {
			$disabled = "";

			$DFM->checkbox($row, $checked, $disabled);

		}


	}

}

################################################################

function JB_form_mselect_field ($field_id, $selected, $size, $mode) {

	$DFM = &JB_get_DynamicFormMarkupObject($mode);

	if (JB_CODE_ORDER_BY=='BY_NAME') {
		$order_by = 'description';
	} else {
		$order_by = 'code';
	}

	

	$selected_codes = explode (",", $selected);

	if ($mode == 'view') {

		require_once (dirname(__FILE__)."/code_functions.php");
		foreach ($selected_codes as $code) {
			echo $comma.JB_getCodeDescription($field_id, $code);
			$comma=', ';

		}

	} else {
		// load in the options and display them
		if (!$codes_list = jb_cache_get('codes_list_fid_'.$field_id.'_ord_'.$order_by.'_lang_'.$_SESSION['LANG'])) {

			if ($_SESSION['LANG'] !='') {
			
				$sql = "SELECT * FROM `codes_translations` WHERE `field_id`='".JB_escape_sql($field_id)."' and lang='".JB_escape_sql($_SESSION['LANG'])."' order by '".JB_escape_sql($order_by)."'";
				
			} else {
				$sql = "SELECT * FROM `codes` WHERE `field_id`='".JB_escape_sql($field_id)."' order by '".JB_escape_sql($order_by)."'";
			}

			$result = JB_mysql_query ($sql) or die (mysql_error());
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
				$codes_list[] = $row;
			}
			jb_cache_set('codes_list_fid_'.$field_id.'_ord_'.$order_by.'_lang_'.$_SESSION['LANG'], $codes_list);

		}


		$DFM->multiple_select_open($field_id, $size);

		foreach ($codes_list as $row) {

			if (in_array($row['code'], $selected_codes)) {
				$checked = " selected ";
			} else {
				$checked = "";
			}

			if ($mode=='view')  {
				//$disabled = " disabled  ";
			} else {
				$disabled = "";
			}
			$DFM->multiple_select_option($row, $checked);		
		}
		$DFM->multiple_select_close();

	}

}

/*

Generate the option list array to be used to render a category option list.

This is the new and improved function that uses less SQL queries
by using the categories.has_child field

It also 

- supports indenting for higher depths
- fixes the bug with sorting by name

This function is cached since a recursive algo is used

*/

function JB_generate_category_option_list($category_id, $selected, &$options_arr, &$Markup) {

	global $mode;
	static $depth;
	static $path;

	if (is_null($Markup)) {
		// use JBDynamicFormMarkup.php template class
		// (otherwise it will use the one passed as $Markup)
		$Markup = &JB_get_DynamicFormMarkupObject();
	}

	$depth++;

	if ($path===null) { $path=array(); }
	

	// Not cached. Compute options using this recursive function

	

	if ($_SESSION['LANG'] == '') {
		
		$sql = "SELECT * FROM categories WHERE parent_category_id='".JB_escape_sql($category_id)."'  ORDER by list_order, category_name ";
	} else {
		$sql = "SELECT *, t2.category_name as NAME FROM categories as t1, cat_name_translations as t2 WHERE t1.category_id=t2.category_id AND t1.parent_category_id='".JB_escape_sql($category_id)."' AND t2.lang='".JB_escape_sql($_SESSION['LANG'])."'  ORDER by t1.list_order, t2.category_name  ";

	}

	$result = jb_mysql_query($sql);

	if (mysql_num_rows($result)>0) {

		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		
			if ($mode=='edit') {
				$row['search_set'] = $row['category_id'];
			}

			

			// Generate The option data

			$name='';

			if ((JB_INDENT_CATEGORY_LIST=='YES') && ($depth>1)) {
				for ($i=0; $i<$depth; $i++) {
					$name .= $Markup->get_category_option_space();//'&nbsp;&nbsp;';
				}
				$name .= $Markup->get_category_option_branch();//'|--&nbsp;';
			} else {
			
				foreach ($path as $val) {
					$gt_html_ent = $Markup->get_category_option_arrow();//' -&gt; ';
					$name = $name.$val.$gt_html_ent;
				}
			}
			$name = $name.$row['NAME'];

			// changed so that value is the category id
			// this is because search_set became too long
			//$options_arr['value'][] = $row['search_set'];
			$options_arr['value'][] = $row['category_id'];

			

			// set the name
		

			$options_arr['a'][] = $row['allow_records'];
			if (($row['allow_records']=='Y') || ($mode!='edit')) { 
				$options_arr['name'][] = $name;
			} elseif ($mode == 'edit') {
				$options_arr['name'][] = '['.$name.' '.$label['cat_option_choose_another'].']';
			}

			$options_arr['depth'][] = $depth;

			if ($row['has_child']=='Y') {
				$path[]=$row['NAME'];
				JB_generate_category_option_list($row['category_id'], $selected, $options_arr, $obj=null);
				$cat_options[$_SESSION['LANG']] = $options_arr;
			}

			

		}
	}
	array_pop($path);
	$depth--;

}



################################################################
function JB_category_option_list($category_id, $selected, $Markup=null) { // &$Markup=null

	if ($Markup==null) {
		$Markup = &JB_get_DynamicFormMarkupObject();
		$all_enabled=false;
	} elseif (get_class($Markup) == 'JBDynamicSearchForm') {
		$all_enabled=true; // when on the search form, all options can be selected
		
	}

	$cache_key = 'cat_options_fid_'.$Markup->form_id.'_cid_'.$category_id.'_class_'.get_class($Markup).'_lang_'.$_SESSION['LANG'];
	if (!$options_arr=JB_cache_get($cache_key)) {
		JB_generate_category_option_list($category_id, $selected, $options_arr, $Markup);
		JB_cache_add($cache_key, $options_arr);
	}

	$is_array = is_array($selected);
	
	for ($i=0; $i < sizeof($options_arr['name']); $i++) {
		$sel = '';
		if ($is_array) {
			if (in_array($options_arr['value'][$i], $selected)) {
				$sel = ' selected ';
			}
		} elseif ($options_arr['value'][$i] == $selected) {
			$sel = ' selected ';
		}
		if ($all_enabled) {
			// allow selection of all categories - eg. search form
			$options_arr['a'][$i] = 'Y';
		}
		$Markup->category_select_option($options_arr['value'][$i], $options_arr['name'][$i], $sel, $options_arr['a'][$i], $options_arr['depth'][$i]);
	}

}

################################################################
# same as above, but ignores AND allow_records='Y'
/*
this functions is only called from the Admin when editing a form to select a category
it is sames as above but fetches all the records..

*/
function JB_category_option_list2($category_id, $selected) {

	if (func_num_args()>2) {
		
		$form_id = func_get_arg(2);
		$form_id_sql = " AND `form_id`='".JB_escape_sql($form_id)."' ";
	}

	global $depth, $cat_names;
	if ($depth =='') $depth=0;
	if ($_SESSION['LANG'] == '') {
		
		$query = "SELECT * FROM categories WHERE category_id='".JB_escape_sql($category_id)."' $form_id_sql ORDER by list_order, category_name  ";
	} else {
		$query = "SELECT *, t2.category_name as NAME FROM categories as t1, cat_name_translations as t2 WHERE t1.category_id=t2.category_id AND t1.category_id='".JB_escape_sql($category_id)."' AND t2.lang='".JB_escape_sql($_SESSION['LANG'])."' $form_id_sql ORDER by list_order, t2.category_name ";

	}
	

	$result = JB_mysql_query ($query) or die(mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$cat_names[$depth] = $row['NAME'];
	if ($depth != 0) {
		if ($row['category_id']==$selected) {
			$sel = " selected ";
		}
		
		echo "<option ".$sel." value='".$row['category_id']."'>";
		for ($i=0; $i < count ($cat_names); $i++) {
			if ($i>0) {
				$j = " -> ";
			}
			echo $j.($cat_names[$i]);
		}
		echo "</option>";
		
	}

	$query ="SELECT * FROM categories WHERE parent_category_id='".JB_escape_sql($category_id)."' $form_id_sql ORDER by list_order, category_name ";
	$result = JB_mysql_query ($query) or die(mysql_error());

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		$depth++;		
		JB_category_option_list2($row['category_id'], $selected);
		unset($cat_names[$depth]);
		$depth--;
	}

}


########################################################
# This is a wrapper for JBDynamicForm class's method get_get_sql_insert_fields
# Please details see in include/classes/JBDynamicForm.php
# For plugins, please use the JBDynamicForm class directly
# This function may be deprecated in the future

function JB_get_sql_insert_fields ($form_id) {

	$DynamicForm = &JB_get_DynamicFormObject($form_id);
	return $DynamicForm->get_sql_insert_fields($form_id);

}

################################################################
# This is a wrapper for JBDynamicForm class's method get_sql_insert_values
# Please details see in include/classes/JBDynamicForm.php
# For plugins, please use the JBDynamicForm class directly
# This function may be deprecated in the future

function JB_get_sql_insert_values ($form_id, $table_name, $primary_key_name, $primary_key_id, $user_id, $assign=false) {

	$DynamicForm = &JB_get_DynamicFormObject($form_id);
	return $DynamicForm->get_sql_insert_values ($table_name, $primary_key_name, $primary_key_id, $user_id, $assign);

}

################################################################
# This is a wrapper for JBDynamicForm class's method get_sql_update_values
# Please details see in include/classes/JBDynamicForm.php
# For plugins, please use the JBDynamicForm class directly
# This function may be deprecated in the future

function JB_get_sql_update_values($form_id, $table_name, $primary_key_name, $primary_key_id, $user_id, $assign=false) {

	$DynamicForm = &JB_get_DynamicFormObject($form_id);
	return $DynamicForm->get_sql_update_values ($table_name, $primary_key_name, $primary_key_id, $user_id, $assign);

}

##########################################################################
# Load in the search values.
# (deprecated as of 3.6)
function JB_tag_to_search_init ($form_id) {
	global $tag_to_search;

	$DynamicForm = &JB_get_DynamicFormObject($form_id);
	$tag_to_search = $DynamicForm->get_tag_to_search();

	return $tag_to_search;

}



//////////// get the already initalized structure
function JB_get_tag_to_field_id($form_id) {

	//global $tag_to_search; post_tag_to_field_id
	global $post_tag_to_field_id; // for compatibility with older plugins

	$DynamicForm = &JB_get_DynamicFormObject($form_id);
	$post_tag_to_field_id = $DynamicForm->get_tag_to_field_id();
	return $post_tag_to_field_id;


}

#################################################################
/*
Displays a search form.
Arguments: 
form_id - the id of the form, eg 1=job post, 2 = resume, 3 = profile
$NO_COLS =number of columns, a number between 1 and 5
$search_form_mode - how to render the form, eg 'ALERTS' will render the form without any <FORM> tags 
'PREVIEW' - preview form in Admin
*/
function JB_display_dynamic_search_form ($form_id, $NO_COLS=2, $search_form_mode=null) {


	$SearchFormObj = &getDynamicSearchFormObject($form_id);

	// we can cache these search form on the home page,
	// employer's resume search
	// candidate's job browse / search
	if (!$search_form_mode && ((strpos($_SERVER['PHP_SELF'], 'index.php')!==false) ||
		(strpos($_SERVER['PHP_SELF'], JB_EMPLOYER_FOLDER.'search.php')!==false) ||
		(strpos($_SERVER['PHP_SELF'], JB_CANDIDATE_FOLDER.'browse.php')!==false) ||
		(strpos($_SERVER['PHP_SELF'], JB_EMPLOYER_FOLDER.'search.php')!==false)) &&
		$_REQUEST['action']!='search') { 
		// serve the cached version
		if (!$search_form = jb_cache_get('search_form_'.$form_id.'_cols_'.$NO_COLS.'_'.$_SESSION['LANG'])) {
			// cache miss, generate the search form, add to cache
			ob_start();
			$SearchFormObj->display_dynamic_search_form($NO_COLS);
			$search_form = ob_get_contents();
			ob_end_clean();
			jb_cache_add('search_form_'.$form_id.'_cols_'.$NO_COLS.'_'.$_SESSION['LANG'], $search_form);
		}
		// output the search form
		echo $search_form;

	} else {
		// generate & serve the search form
		$SearchFormObj->display_dynamic_search_form($NO_COLS, $search_form_mode);


	}

	


}




##################################################3

function JB_generate_search_sql($form_id, $_SEARCH_INPUT=null) {

	$SearchFormObj = &getDynamicSearchFormObject($form_id);

	if (is_array($_SEARCH_INPUT)) {
		return $SearchFormObj->generate_search_sql($_SEARCH_INPUT);
	} else {
		return $SearchFormObj->generate_search_sql();
	}

 
}

##################################################3


function JB_is_field_valid($field_id, $form_id) {
	
	$ttf = jb_get_tag_to_field_id($form_id);
	foreach ($ttf as $field) {
		
		if ($field['field_id']==$field_id) {
			return true;
		}
	}
	return false;
}

#############################################
function JB_is_reserved_field ($field_id) {

	if ($field_id==false) {
		return $field_id;
	}

	$sql = "SELECT * from `form_fields` WHERE field_id='".JB_escape_sql($field_id)."' ";
	$result = JB_mysql_query($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	if (JB_is_reserved_template_tag($row['template_tag'])) {	
		return true;
	}

	return false;

}


################################################

function JB_build_sort_fields ($form_id, $section) {

	$sql =  "SELECT * FROM form_fields where `form_id`='".JB_escape_sql($form_id)."' and section='".JB_escape_sql($section)."' order by  field_sort ASC ";
	$result = JB_mysql_query($sql) or die (mysql_error());
	$order = 1;
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		
		$sql = "UPDATE form_fields SET `field_sort`='".JB_escape_sql($order)."' WHERE form_id='".JB_escape_sql($form_id)."' AND field_id='".JB_escape_sql($row['field_id'])."' ";
		
		JB_mysql_query($sql) or die (mysql_error());
		$order++;

	}


}

########################################

function JB_move_field_up($form_id, $field_id) {

	$field = JB_get_field ($form_id, $field_id);

	$section = $field['section'];

	# get current order
	$now_order = $field['field_sort']; //JB_get_field_order ($form_id, $field_id);
	$new_order = $now_order - 1;

	if ($new_order==0) {
		return; // already the top field
	}

	// top goes to bottom
	$sql = "UPDATE form_fields SET `field_sort`=field_sort+1 WHERE form_id='".JB_escape_sql($form_id)."' AND field_sort='".JB_escape_sql($new_order)."' AND `section`='".JB_escape_sql($section)."' ";
	JB_mysql_query($sql) or die (mysql_error().$sql);
	

	// field_id moves up
	$sql = "UPDATE form_fields SET `field_sort`='".JB_escape_sql($new_order)."' WHERE form_id='".JB_escape_sql($form_id)."' AND field_id='".JB_escape_sql($field_id)."' ";
	JB_mysql_query($sql) or die (mysql_error().$sql);
	

}


########################################

function JB_move_field_down($form_id, $field_id) {

	$field = JB_get_field ($form_id, $field_id);

	$section = $field['section'];

	# get current order
	$now_order = $field['field_sort']; //JB_get_field_order ($form_id, $field_id);
	$new_order = $now_order + 1;

	$sql = "SELECT max(field_sort) as the_max from form_fields where form_id='".JB_escape_sql($form_id)."' AND section='".JB_escape_sql($section)."'  ";
	$result = JB_mysql_query($sql) or die (mysql_error().$sql);
		
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	//echo "the max:".$row['the_max']." new oreer".$new_order;
	if ($new_order > $row['the_max']) {
		
		return; //already at the bottom
	}

	// bottom goes to top
	$sql = "UPDATE form_fields SET `field_sort`=field_sort-1 WHERE form_id='".JB_escape_sql($form_id)."' AND field_sort='".JB_escape_sql($new_order)."' AND `section`='".JB_escape_sql($section)."' ";
	//echo $sql."<br>";
	JB_mysql_query($sql) or die (mysql_error().$sql);

	// field_id moves up
	$sql = "UPDATE form_fields SET `field_sort`='".JB_escape_sql($new_order)."' WHERE form_id='".JB_escape_sql($form_id)."' AND field_id='".JB_escape_sql($field_id)."' ";
	//echo $sql."<br>";
	JB_mysql_query($sql) or die (mysql_error().$sql);
	
}

#############################################

function JB_get_field_order ($form_id, $field_id) {

		$sql =  "SELECT * from form_fields where `form_id`='".JB_escape_sql($form_id)."' AND field_id='".JB_escape_sql($field_id)."' ";
		$result = JB_mysql_query($sql) or die (mysql_error());
		$row = mysql_fetch_array ($result, MYSQL_ASSOC);
		return $row['field_sort']; 


}

############################################

function JB_get_field ($form_id, $field_id) {

		$sql =  "SELECT * from form_fields where `form_id`='".JB_escape_sql($form_id)."' AND field_id='".JB_escape_sql($field_id)."' ";
		$result = JB_mysql_query($sql) or die (mysql_error());
		return mysql_fetch_array ($result, MYSQL_ASSOC);
		
}


######################################

function JB_generate_template_tag($form_id) { // generate a random template tag. This help to fix older versions of the JB where some fields did not have a template tag...

	// generate a tag.
	$template_tag = '';
	while (strlen($template_tag) < 4) { 
	   $template_tag .= chr(rand (97,122)); 
	}

	$unique = false;

	$sql = "select field_id from form_fields where template_tag='".JB_escape_sql($template_tag)."' and form_id='".JB_escape_sql($form_id)."' ";
	$result = JB_mysql_query($sql)or die ($sql.mysql_error());
	if (mysql_num_rows($result)==0) {
		$unique = true;
	}


	// check if it is unique

	if ($unique) {

		return $template_tag;


	} else {
		return JB_generate_template_tag($form_id); // try again
	}


}

###############################


function JB_fix_form_field_translations() {

	$sql = "DELETE from form_fields WHERE (form_id=4 OR form_id=5) AND section=3 ";
	JB_mysql_query($sql)or die ($sql.mysql_error());

	$sql = "SELECT field_id from form_field_translations";
	$result = JB_mysql_query($sql)or die ($sql.mysql_error());
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$sql = "SELECT field_id from form_fields";
		$result2 = JB_mysql_query($sql)or die ($sql.mysql_error());
		if (mysql_num_rows($result2)==0) {
			$sql = "DELETE FORM form_field_translations WHERE field_id=".JB_escape_sql($row['field_id']);
			JB_mysql_query($sql)or die ($sql.mysql_error());

		}

	}

}


#######################################




?>