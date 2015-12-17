<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

require_once (dirname(__FILE__)."/category.inc.php");
require_once (dirname(__FILE__)."/code_functions.php");
require_once (dirname(__FILE__).'/lists.inc.php');

global $candidate_tag_to_search;
global $candidate_tag_to_field_id;

// Load the Candidate Signup form object - and instance of JBDynamicForms.php
$CandidateForm = &JB_get_DynamicFormObject(5); 
$candidate_tag_to_search = $CandidateForm->get_tag_to_search();
$candidate_tag_to_field_id = $CandidateForm->get_tag_to_field_id();


JBPLUG_do_callback('candidates_init', $adv_tag_to_search, $adv_tag_to_field_id);

// NOTE: andidates do not have $adv_tag_to_search defined as of v 2.8.x



/*

Function:

JB_generate_candidate_q_string

Description:

Generate the query string after a search is performed. The query string
is used to make sure that the search paramaters are preserved from page
to page.

Used by the Admin (admin/candidates.php)

Returns:

A string which is used to append to URLs

*/

function JB_generate_candidate_q_string () {

	if ($_REQUEST['action']=='search') {

		$q_aday = JB_html_ent_to_utf8($_REQUEST['q_aday']);
		$q_amon = JB_html_ent_to_utf8($_REQUEST['q_amon']);
		$q_ayear = JB_html_ent_to_utf8($_REQUEST['q_ayear']);
		$q_name = JB_html_ent_to_utf8($_REQUEST['q_name']);
		$q_username = JB_html_ent_to_utf8($_REQUEST['q_username']);
		$q_resumes = JB_html_ent_to_utf8($_REQUEST['q_resumes']);
		$q_news = JB_html_ent_to_utf8($_REQUEST['q_news']);
		$q_email = JB_html_ent_to_utf8($_REQUEST['q_email']);

		if (isset($_REQUEST['show'])) {
			$show = '&show='.$_REQUEST['show'];
		}

		$q_string = 
		htmlentities("&action=search&q_name=".urlencode($q_name)."&q_username=".urlencode($q_username)."&q_news=".urlencode($q_news)."&q_resumes=".urlencode($q_resumes)."&q_email=".urlencode($q_email)."&q_aday=".urlencode($q_aday)."&q_amon=".urlencode($q_amon)."&q_ayear=".urlencode($q_ayear).$show);

	}

	JBPLUG_do_callback('generate_can_q_string', $q_string);

	return $q_string;

}

/////////////////////////////////////////////////////
// This function's deprecated since 3.6

function JB_candidate_signup_form_init(&$data) {

	// Load in the prams form the POST / GET input..

	$form_id = 5;

	JB_init_data_from_request($form_id, $data);

	JBPLUG_do_callback('candidate_signup_form_init', $data, $admin);

}

///////////////////////////////////////////////////
// deprecated, instead use this code:
// $CandidateForm = &JB_get_DynamicFormObject(5); 
// $CandidateForm->display_form($mode);
function JB_display_candidate_signup_form ($form_id=5, $mode, $data, $admin) {

	

	if ($admin) {
		$user_id = $_REQUEST['user_id'];
	} else {
		$user_id = $_SESSION['JB_ID'];
	}

	global $error;
	global $label;

	if ($data == '' ) {
		JB_candidate_signup_form_init($data);
	}


	if ($mode=='EDIT') {
		echo "Note: Fields with a black label cannot be removed. You can edit their labels by editing the strings from the 'Langauge' menu. You can also add extra new fields to this form.";
	}

	JB_template_candidate_signup_form($mode, $admin, $user_id);


}

/////////////////////////////////////////////////////////////
/*

Function: 

JB_tag_to_field_id_init_candidate

Description:

Initializes the data structure which holds the form information.
Form information is used by the JBDynamicForm class to display the form,
error checking, and other routines where form information is needed.

This function uses JB_schema_get_fields(5) to get a list of all the fields
in the form. It also caches the structure if cache is enabled

Arguments:

None.

Returns:

An associative array which maps all template tags to field_id's / 
field meta-data.

Here is an example structure

$tag_to_field_id = array (
	'TITLE' => array(
		'field_id'=2,
		'field_type' = 'TEXT',
		'field_label' = 'Title',
		'is_hidden' = ''
		),
	'DESCRIPTION' => array(
		'field_id'=2,
		'field_type' = 'TEXT',
		'field_label' = 'Description',
		'is_hidden' = ''
		)
)

*/

function JB_tag_to_field_id_init_candidate () {
	

	global $label;

	global $candidate_tag_to_field_id;

	if ($candidate_tag_to_field_id = JB_cache_get('tag_to_field_id_5_'.$_SESSION['LANG'])) {
		return $candidate_tag_to_field_id;
	}

	$fields = JB_schema_get_fields(5);
	// the template tag becomes the key
	foreach ($fields as $field) {
		$candidate_tag_to_field_id[$field['template_tag']] = $field;
	}
	JBPLUG_do_callback('tag_to_field_id_init_can', $candidate_tag_to_field_id);

	JB_cache_add('tag_to_field_id_5_'.$_SESSION['LANG'], $candidate_tag_to_field_id);

	return $candidate_tag_to_field_id;



}

#####################################################################
/*

Function

JB_load_candidate_data ($candidate_id)

Description

Loads candidate data from the `users` table. Used by the JBDynamicForm
class. To load a candidate record, it is better to use the JBDynamicForm
like this:

$Form = jb_get_DynamicFormObject(5); // form_id 5 (candidates)
$data = $Form->load(10); // load the employer_id of 10

Arguments

$candidate_id - primary key of candidate's record

Returns

Associative array of column names mapped to their data values.

*/
function JB_load_candidate_data ($candidate_id) {
	
	
	
	$sql = "SELECT * FROM `users` WHERE ID='".jb_escape_sql($candidate_id)."' limit 1 ";
	$result = JB_mysql_query($sql) or die ($sql. mysql_error());
	$data = mysql_fetch_array($result, MYSQL_ASSOC);
	JBPLUG_do_callback('load_candidate_values', $data);
	

	return $data;

}


################################################################
/*

Function:

JB_search_category_for_users

Description:

Generates the WHERE part of the SQL query to search candidates by category.

Arguments:

$cat_id (optional) - catgeory id to search. If false then the function will 
tr to use $_REQUEST['cat']


$field_id (optional) - field id on the form. If false then all category fields
on the form will be searched.


Returns:

A string which can be used in the WHERE part of an SQL query when selecting 
posts


*/



function JB_search_category_for_users($cat_id=false, $field_id=false) {

	if ($cat_id==false) {
		$cat_id = (int) $_REQUEST['cat'];
	}

	if ($field_id!=false) {
		$field_id_sql = "AND field_id='".jb_escape_sql($field_id)."'"; 
	}


	$sql = "select * FROM form_fields WHERE field_type='CATEGORY' AND form_id='5' $field_id_sql";
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
			if (strlen($search_set) < 1000) {
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
					$start = $set[$i]; 
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

########################################################################
# This function is deprecated.
# Instead, auto_increment in MySQL is used.
# 
function JB_generate_candidate_id () {

   $query ="SELECT max(`ID`) FROM `users`";
   $result = JB_mysql_query($query) or die(mysql_error());
   $row = mysql_fetch_row($result);
   $row[0]++;
   return $row[0];

}



################################################################
/*

Function:

JB_insert_candidate_data

Description:

Insert new candidate / update candidate.

if user id is supplied, the account will be updated (username unchanged)
otherwise a new account will be created.
Used by the save() method of the JBDynamicForm calss.

eg.

$Form = jb_get_DynamicFormObject(5); // form_id 5 (candidates)
$Form->save(); // save data submitted by the form generated by $Form->display_form()


Arguments:

boolean $admin - set to true if admin, admin can update things such as credits
and subscription status

int $user_id - the primary key (ID) of the `users` table

Note: if $user_id is not passed as argument, the function will
attempt to get the user_id from $_REQUEST['user_id']

The data to be inserted is fetched from $_REQUEST
Assuming that the following steps were taken before
- $_REQUEST was filtered by jb_clean()
- The data was validated by JB_validate_candidate_data()
- The application layer validated that the user has permissions / ability
to call the function

Returns:

user id of the record inserted

*/

function JB_insert_candidate_data() {

	global $label;

	global $admin;
	

	if ($_REQUEST['user_id'] != '') {

		// update user's account details...
		
		$user_id = JB_update_candidate_account($_REQUEST['user_id'], $admin);

	} else {

	
		$user_id = JB_create_new_candidate_account ();
		
		  
		  
	}

	return $user_id;
	
	
}
###############################################################

/* 

Function:

JB_validate_candidate_data

Description:

Validate the candidate's signup form to create a new account

Arguments:

$form_id - Id of the form (candidates = 4)

Returns:

Returns a string with the error message or false if no error



*/



function JB_validate_candidate_data($form_id) {

	global $label;
	
	$errors = array();

	if ($_REQUEST['user_id']==false) {

		if ($_REQUEST['Username'] ==false) {
			$errors[] = $label["c_signup_error4"];
		} else {

			$result = JB_mysql_query ("SELECT * FROM `users` WHERE `Username`='".jb_escape_sql($_REQUEST['Username'])."' ") or die(mysql_error()."we have error");
			$row=mysql_fetch_array($result, MYSQL_ASSOC) ;
			if ($row['Username'] != '' ) {
				$label['c_signup_error5'] = str_replace ("%USERNAME%", $row['Username'],$label['c_signup_error5']);
				$errors[] = $label['c_signup_error5'];
			} elseif (!preg_match('#^[a-z0-9À-ÿ\-_\.@]+$#Di', $_REQUEST['Username'])) {
				$errors[] = $label['c_signup_error11'];
			}

		}

		if ($_REQUEST['Password'] == false) {
			$errors[] = $label['c_signup_error6'];
		} elseif (strlen(trim($_REQUEST['Password'])) < 6) {
			$errors[] = $label['c_signup_error_pw_too_weak']; 
		}

		if ($_REQUEST['Password2'] == false) {
			$errors[] = $label["c_signup_error7"];
		}

		if ($_REQUEST['Password']!=$_REQUEST['Password2']) {
			$errors[] = $label["c_signup_error1"];
		}


	}

	

	if ($_REQUEST['FirstName'] == false) {
			$errors[] = $label["c_signup_error2"];
	   }
	if ($_REQUEST['LastName'] ==false) {
			$errors[] = $label["c_signup_error3"];
	}

	
	if ($_REQUEST['Email'] == false) {
			$errors[] = $label["c_signup_error8"];
	  } elseif (!JB_validate_mail($_REQUEST['Email'])) {

		 $errors[] = $label["c_signup_error8"];

	 } else {

		 if ($_REQUEST['user_id']==false) {

			$result = JB_mysql_query ("SELECT * from `users` WHERE `Email`='".jb_escape_sql($_REQUEST['Email'])."'") or die(mysql_error());
			$row=mysql_fetch_array($result, MYSQL_ASSOC);

			//validate email ";

			if ($row['Email'] != '') {
				$errors[] = " ".$label["c_signup_error10"] ." ";
			}
		 }


	 }

	 if ($_REQUEST['user_id']!='') {
		if (!is_numeric($_REQUEST['user_id'])) {
			return 'Invalid Input!';
		}
	}

	$_REQUEST['FirstName'] = JB_clean_str($_REQUEST['FirstName']);
	$_REQUEST['LastName'] = JB_clean_str($_REQUEST['LastName']);
	$_REQUEST['Username'] = JB_clean_str($_REQUEST['Username']);
	$_REQUEST['Email'] = JB_clean_str($_REQUEST['Email']);
	$_REQUEST['Newsletter'] = JB_clean_str($_REQUEST['Newsletter']);
	$_REQUEST['Notification1'] = JB_clean_str($_REQUEST['Notification1']);
	$_REQUEST['Notification2'] = JB_clean_str($_REQUEST['Notification2']);
	$_REQUEST['lang'] = JB_clean_str($_REQUEST['lang']);


	JBPLUG_do_callback('valiate_candidate_account', $error);
	
	$error = '';
	if ($error) {
		$list = explode('<br>', $error);
		foreach ($list as $item) {
			$errors[] = $item;
		}
	}
	
	JBPLUG_do_callback('valiate_candidate_account_array', $errors); // added in 3.6.6

	$errors = $errors + JB_validate_form_data(5);

	return $errors;
	
}


########################################################

/* 

Function:

JB_delete_candidate_files

Description:

Deletes all files stored by a record.
Iterates for each IMAGE and FILE field, and deletes the file
stored for that field.
Useful before deleting an candidate record from the database
Used by JB_delete_candidate_data

Arguments:

$id - candidate id (primary key of the `users` table

*/


function JB_delete_candidate_files ($id) {

	$sql = "select * from form_fields where form_id=5 ";
	$result = JB_mysql_query ($sql) or die (mysql_error());

	while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

		$field_id = $row['field_id'];
		$field_type = $row['field_type'];

		if (($field_type == "FILE")) {
			JB_delete_file_from_field_id("users", "ID", $id, $field_id);
		}

		if (($field_type == "IMAGE")){
			JB_delete_image_from_field_id("users", "ID", $id, $field_id);
		}
		
	}


}

####################

/* 

Function:

JB_delete_candidate

Description:

Delete candidate from the database. 
Deletes files and other data such as resume, applications, etc.

Arguments:

$id - candidate id

Returns:

1 if candidate was deleted

*/


function JB_delete_candidate ($id) {

	$sql = "SELECT * FROM `resumes_table` WHERE `user_id`='".jb_escape_sql($id)."'";
	$result = JB_mysql_query($sql) or die (mysql_error().$sql);

	if (mysql_num_rows($result)>0) {
	
		$row = mysql_fetch_array($result, MYSQL_ASSOC);

		JB_delete_resume ($row['resume_id']);

		$sql = "DELETE FROM skill_matrix_data WHERE  `object_id`='".jb_escape_sql($row['resume_id'])."' AND user_id='".jb_escape_sql($id)."' ";
		JB_mysql_query($sql) or die (mysql_error().$sql);


	}

	JB_delete_candidate_files ($id);

	$sql = "DELETE FROM `saved_jobs` WHERE `user_id`='".jb_escape_sql($id)."'";
	JB_mysql_query($sql) or die (mysql_error().$sql);

	$sql = "DELETE FROM `requests` WHERE `candidate_id`='".jb_escape_sql($id)."'";
	JB_mysql_query($sql) or die (mysql_error().$sql);

    $sql = "DELETE FROM `applications` WHERE `user_id`='".jb_escape_sql($id)."'";
	JB_mysql_query($sql) or die (mysql_error().$sql);

	$sql = "DELETE FROM `membership_invoices` WHERE `user_id`='".jb_escape_sql($id)."'";
	JB_mysql_query($sql) or die (mysql_error());

	JBPLUG_do_callback('delete_candidate_account', jb_escape_sql($id));

	$sql = "DELETE FROM `users` WHERE `ID`='".jb_escape_sql($id)."'";
	JB_mysql_query($sql) or die (mysql_error().$sql);
	$affected = jb_mysql_affected_rows();

	return $affected;

}
################################

/* 

Function:

JB_update_candidate_account

Description:

Update candidate account details.

Arguments:

$user_id = primary key (ID) of the users table
$admin - boolean, true if called by Admin

The data is fetched from $_REQUEST using the form fields
generated by JBDynamicFormObject display_form method.

eg.

$Form = &JB_get_DynamicFormObject(4);
$Form->display_form('edit', true);

Returns:

true if row was changed.


*/

function JB_update_candidate_account ($user_id, $admin) {

	if ($_REQUEST['lang']=='') {	
		$_REQUEST['lang'] = JB_get_default_lang();
	}


	// build a list of fields that we want updated
	$assign = array(
		
		'Newsletter' => (int) $_REQUEST['Newsletter'],
		'Notification1' => (int) $_REQUEST['Notification1'],
		'Notification2' => (int) $_REQUEST['Notification2'],
		'FirstName' => $_REQUEST['FirstName'],
		'LastName' => $_REQUEST['LastName'],
		'Email' => $_REQUEST['Email'],
		'lang' => $_REQUEST['lang']
	);

	if ($admin) {
		// append admin only values
		$assign['membership_active'] = $_REQUEST['membership_active'];
	}

	
	$sql = "UPDATE `users` SET ".JB_get_sql_update_values (5, "users", "ID", $user_id, $user_id, $assign)." WHERE ID='".jb_escape_sql($user_id)."'";


    JB_mysql_query($sql) or die ($sql.mysql_error()); 

	
	JBPLUG_do_callback('update_candidate_account', $user_id, $admin);


	return jb_mysql_affected_rows();


}

//////////////////////////////////////////////

/* 

Function:

JB_create_new_candidate_account

Description:

Creates a new candidate account
Input from $_REQUEST
Sends confirmation email (email template 1) if enabled


Arguments:

none

Returns:

ID of the candidate created (primary key)


*/


function JB_create_new_candidate_account () {


	global $label;
	
	if ($_REQUEST['lang']=='') {	
		$_REQUEST['lang'] = JB_get_default_lang();
	}

	
    $validated = 0;
	if (JB_CA_NEEDS_ACTIVATION == "AUTO")  {
	   $validated = 1;
	}

	// when inserting, use $assign to overwrite
	// the values which we do not want to fetch from the $_REQUEST
	// (Assuming that values on $_REQUEST already went through validation)

	$assign = array(
		'Validated' => $validated,
		'SignupDate' =>  gmdate("Y-m-d H:i:s"),
		'IP' => $_SERVER['REMOTE_ADDR'],
		'Newsletter' => (int) $_REQUEST['Newsletter'],
		'Notification1' => (int) $_REQUEST['Notification1'],
		'Notification2' => (int) $_REQUEST['Notification2'],
		'Password' => md5(stripslashes($_REQUEST['Password'])),
		'expired' => 'N'
		
	);


	$sql = "REPLACE INTO `users` ( ".JB_get_sql_insert_fields(5, $assign).") VALUES (   ".JB_get_sql_insert_values(5, "users", "ID", $user_id, '', $assign).") ";


	JB_mysql_query($sql);
	
	$user_id = JB_mysql_insert_id();

	if ($user_id > 0) {
	   
	   JBPLUG_do_callback('create_candidate_account', $user_id);
	 
	} 
	

	// Here the emailmessage itself is defined, this will be send to your members. Don't forget to set the validation link here.

	$result = JB_get_email_template (1, $_SESSION['LANG']);

	$e_row = mysql_fetch_array($result, MYSQL_ASSOC);
	$EmailMessage = $e_row['EmailText'];
	$from = $e_row['EmailFromAddress'];
	$from_name = $e_row['EmailFromName'];
	$subject = $e_row['EmailSubject'];
	 
	$subject = str_replace ("%MEMBERID%", stripslashes($_REQUEST['Username']), $subject);

	$EmailMessage = str_replace ("%FNAME%", stripslashes($_REQUEST['FirstName']), $EmailMessage);
	$EmailMessage = str_replace ("%LNAME%", stripslashes($_REQUEST['LastName']), $EmailMessage);
	$EmailMessage = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $EmailMessage);
	$EmailMessage = str_replace ("%SITE_NAME%", JB_SITE_NAME, $EmailMessage);
	$EmailMessage = str_replace ("%MEMBERID%", stripslashes($_REQUEST['Username']), $EmailMessage);
	$EmailMessage = str_replace ("%PASSWORD%", stripslashes($_REQUEST['Password']), $EmailMessage);
	$EmailMessage = str_replace ("%SITE_URL%", JB_BASE_HTTP_PATH, $EmailMessage);
	
	JBPLUG_do_callback('candidate_signup_email_msg', $EmailMessage, $user_id);
	
	$to = stripslashes($_REQUEST['Email']);

	if (!defined('JB_EMAIL_CAN_SIGNUP')) {
		define ('JB_EMAIL_CAN_SIGNUP', 'YES');
	}
	if (JB_EMAIL_CAN_SIGNUP=='YES') {
		$email_id = JB_queue_mail($to, stripslashes(jb_get_formatted_name(stripslashes($_REQUEST['FirstName']), stripslashes($_REQUEST['LastName']))), $e_row['EmailFromAddress'], $e_row['EmailFromName'], $subject, $EmailMessage, '', 1);
		JB_process_mail_queue(1, $email_id);

	}

	$to = JB_SITE_CONTACT_EMAIL;
   
	if (JB_EMAIL_CANDIDATE_SIGNUP_SWITCH=='YES') {

		$email_id = JB_queue_mail($to, "Admin", JB_SITE_CONTACT_EMAIL, JB_SITE_NAME, $subject, $EmailMessage, '', 2);
		JB_process_mail_queue(1, $email_id);

	}


	return $user_id;

}

?>