<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
/*


- Functions in this file are used by the JBDynamicForm class in include/clases/
- For the api, please see employer_functions.php

*/

require_once (dirname(__FILE__)."/category.inc.php");
require_once (dirname(__FILE__)."/code_functions.php");
require_once (dirname(__FILE__).'/lists.inc.php');

global $adv_tag_to_search;
global $adv_tag_to_field_id;


// Load the Employer Signup form object - and instance of JBDynamicForms.php
$EmployerForm = &JB_get_DynamicFormObject(4); 
$adv_tag_to_search = $EmployerForm->get_tag_to_search();
$adv_tag_to_field_id = $EmployerForm->get_tag_to_field_id();


JBPLUG_do_callback('employers_init', $adv_tag_to_search, $adv_tag_to_field_id);

// NOTE: Employers do not have $adv_tag_to_search defined as of v 2.8.x

############################################################


/*

Function:

JB_generate_emp_q_string

Description:

Generate the query string after a search is performed. The query string
is used to make sure that the search paramaters are preserved from page
to page.

Used by the Admin (admin/employers.php)

Returns:

A string which is used to append to URLs

*/

function JB_generate_emp_q_string () {

	if ($_REQUEST['action']=='search') {
		$q_aday = urlencode(JB_html_ent_to_utf8($_REQUEST['q_aday']));
		$q_amon = urlencode(JB_html_ent_to_utf8($_REQUEST['q_amon']));
		$q_ayear = urlencode(JB_html_ent_to_utf8($_REQUEST['q_ayear']));
		$q_name = urlencode(JB_html_ent_to_utf8($_REQUEST['q_name']));
		$q_username = urlencode(JB_html_ent_to_utf8($_REQUEST['q_username']));
		$q_resumes = urlencode(JB_html_ent_to_utf8($_REQUEST['q_resumes']));
		$q_news = urlencode(JB_html_ent_to_utf8($_REQUEST['q_news']));
		$q_email = urlencode(JB_html_ent_to_utf8($_REQUEST['q_email']));
		$q_company = urlencode(JB_html_ent_to_utf8($_REQUEST['q_company'])); 
		if (isset($_REQUEST['show'])) {
			$show = '&show='.urlencode($_REQUEST['show']);
		}
		$q_string = htmlentities('&action=search&q_name='.$q_name.'&q_username='.$q_username.'&q_news='.$q_news.'&q_resumes='.$q_resumes.'&q_email='.$q_email.'&q_aday='.$q_aday.'&q_amon='.$q_amon.'&q_ayear='.$q_ayear.'&q_company='.$q_company.$show);
	}
	JBPLUG_do_callback('generate_emp_q_string', $q_string);
	return $q_string;


}

###################################################
# This function is deprecated since 3.6
function JB_employer_signup_form_init (&$data, $admin) {

	$form_id = 4;

	$data['lang'] = stripslashes($_REQUEST['lang']);
	$data['CompName'] = stripslashes($_REQUEST['CompName']);
	$data['Aboutme'] = stripslashes($_REQUEST['Aboutme']);
	$data['Notification2'] = stripslashes($_REQUEST['Notification2']);
	$data['Notification1'] = stripslashes($_REQUEST['Notification1']);
	$data['Newsletter'] = stripslashes($_REQUEST['Newsletter']);
	$data['Email'] = stripslashes($_REQUEST['Email']);
	$data['Password'] = stripslashes($_REQUEST['Password']);
	$data['Password2'] = stripslashes($_REQUEST['Password2']);
	$data['Username'] = stripslashes($_REQUEST['Username']);
	$data['LastName'] = stripslashes($_REQUEST['LastName']);
	$data['FirstName'] = stripslashes($_REQUEST['FirstName']);

	if ($admin) {

		$data['subscription_can_premium_post'] = stripslashes($_REQUEST['subscription_can_premium_post']);
		$data['subscription_can_post'] = stripslashes($_REQUEST['subscription_can_post']);
		$data['subscription_can_view_resume'] = stripslashes($_REQUEST['subscription_can_view_resume']);
		$data['premium_posts_balance'] = stripslashes($_REQUEST['premium_posts_balance']);
		$data['posts_balance'] = stripslashes($_REQUEST['posts_balance']);
		$data['can_view_blocked'] = stripslashes($_REQUEST['can_view_blocked']);
		$data['posts_quota'] = stripslashes($_REQUEST['posts_quota']);
		$data['p_posts_quota'] = stripslashes($_REQUEST['p_posts_quota']);
		$data['views_quota'] = stripslashes($_REQUEST['views_quota']);
		$data['posts_quota_tally'] = stripslashes($_REQUEST['posts_quota_tally']);
		$data['p_posts_quota_tally'] = stripslashes($_REQUEST['p_posts_quota_tally']);
		$data['views_quota_tally'] = stripslashes($_REQUEST['views_quota_tally']);
		$data['quota_timestamp_tally'] = stripslashes($_REQUEST['quota_timestamp_tally']);

	}
	
	JB_init_data_from_request($form_id, $data);

	JBPLUG_do_callback('employer_signup_form_init', $data, $admin);

}

///////////////////////////////////////////////////
// deprecated since 3.6, instead use this code:
// $EmployerForm = &JB_get_DynamicFormObject(4); 
// $EmployerForm->display_form($mode);
function JB_display_signup_form ($form_id=4, $mode, $data, $admin) {
	if ($admin) {
		$user_id = $_REQUEST['user_id'];
	} else {
		$user_id = $_SESSION['JB_ID'];
	}
	global $error;
	global $label;
	if ($data == '' ) {
		// Load in the prams form the POST / GET input..
		JB_employer_signup_form_init ($data, $admin);
	}
	if ($mode=='EDIT') {
		echo "Note: Fields with a black label cannot be removed. You can edit their labels by editing the strings from the 'Langauge' menu. You can also add extra new fields to this form.";
	}
	JB_template_employer_signup_form($mode, $admin, $user_id);
}

/////////////////////////////////////////////////////////////
/*

Function: 

JB_tag_to_field_id_init_emp

Description:

Initializes the data structure which holds the form information.
Form information is used by the JBDynamicForm class to display the form,
error checking, and other routines where form information is needed.

This function uses JB_schema_get_fields(4) to get a list of all the fields
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
function JB_tag_to_field_id_init_emp () {
	
	global $adv_tag_to_field_id;
	global $label;
	if ($adv_tag_to_field_id = JB_cache_get('tag_to_field_id_4_'.$_SESSION['LANG'])) {
		return $adv_tag_to_field_id;
	}
	$fields = JB_schema_get_fields(4);
	
	// the template tag becomes the key
	foreach ($fields as $field) {
		$adv_tag_to_field_id[$field['template_tag']] = $field;
	}
	JBPLUG_do_callback('tag_to_field_id_init_emp', $adv_tag_to_field_id);
	JB_cache_set('tag_to_field_id_4_'.$_SESSION['LANG'], $adv_tag_to_field_id);
	return $adv_tag_to_field_id;

}

#####################################################################
/*

Function

JB_load_employer_data ($employer_id)

Description

Loads employer data from the `employers` table. Used by the JBDynamicForm
class. To load an employer record, it is better to use the JBDynamicForm
like this:

$Form = jb_get_DynamicFormObject(4); // form_id 4 (employers)
$data = $Form->load(5); // load the employer_id of 5

Arguments

$employer_id - primary key of employer record

Returns

Associative array of column names mapped to their data values.

*/
function JB_load_employer_data ($employer_id) {
	

	$sql = "SELECT * FROM `employers` WHERE ID='".jb_escape_sql($employer_id)."' limit 1 ";

	$result = JB_mysql_query($sql);
	$data = mysql_fetch_array($result, MYSQL_ASSOC);

	JBPLUG_do_callback('load_employer_values', $data);
	
	return $data;
}


################################################################

/*

Function:

JB_search_category_for_employers

Description:

Generates the WHERE part of the SQL query to search posts by category.

Arguments:

$cat_id (optional) - catgeory id to search. If false then the function will 
tr to use $_REQUEST['cat']


$field_id (optional) - field id on the form. If false then all category


Returns:

$field_id (optional) - field id on the form. If false then all category fields
on the form will be searched.


*/

function JB_search_category_for_employers($cat_id=false, $field_id=false) {

	if ($cat_id==false) {
		$cat_id = (int) $_REQUEST['cat'];
	}

	if ($field_id!=false) {
		$field_id_sql = "AND field_id='".jb_escape_sql($field_id)."'"; 
	}


	$sql = "select * FROM form_fields WHERE field_type='CATEGORY' AND form_id='4' $field_id_sql";
	$result = JB_mysql_query ($sql) or die (mysql_error());

	// The search set contains the set of parent category ids.
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

function JB_generate_employer_id () {

   $query ="SELECT max(`ID`) FROM `employers`";
   $result = JB_mysql_query($query) or die(mysql_error());
   $row = mysql_fetch_row($result);
   $row[0]++;
   return $row[0];

}



################################################################
/*

Function:

JB_insert_employer_data

Description:

Insert new employer / update employer.

if user id is supplied, the account will be updated (username unchanged)
otherwise a new account will be created.
Used by the save() method of the JBDynamicForm calss.

eg.

$Form = jb_get_DynamicFormObject(4); // form_id 4 (employers)
$Form->save(); // save data submitted by the form generated by $Form->display_form()


Arguments:

boolean $admin - set to true if admin, admin can update things such as credits
and subscription status

int $user_id - the primary key (ID) of the employers table

Note: if $user_id is not passed as argument, the function will
attempt to get the user_id from $_REQUEST['user_id']

The data to be inserted is fetched from $_REQUEST
Assuming that the following steps were taken before
- $_REQUEST was filtered by jb_clean()
- The data was validated by JB_validate_employer_data()
- The application layer validated that the user has permissions / ability
to call the function

Returns:

employer id of the record inserted

*/


function JB_insert_employer_data($admin, $user_id=null) {

	global $label;

	if (!$user_id) {
		$user_id = (int) $_REQUEST['user_id'];
	}
 
	if ($user_id != '') {
		// update user's account details...
		$employer_id = JB_update_employer_account($user_id, $admin);
	} else {
		
		$employer_id = JB_create_new_employer_account ();
		return $employer_id;
	}
	
}

###############################################################

/* 

Function:

JB_validate_employer_data

Description:

Validate the employer's signup form to create a new account

Arguments:

$form_id - Id of the form (employers = 4)

Returns:

Returns a string with the error message or false if no error



*/



function JB_validate_employer_data($form_id) {

	global $label;

	$errors = array();

	if ($_REQUEST['FirstName']==false ) {
		$errors[] = $label['employer_signup_error_name'];
	}
	if ($_REQUEST['LastName']==false) {
		$errors[] = $label['employer_signup_error_ln'];
	}

	if ($_REQUEST['user_id']==false) {

		if ($_REQUEST['Password']!=$_REQUEST['Password2']) {
			$errors[] = $label['employer_signup_error_pmatch'];
		}

		if ($_REQUEST['Username'] ==false) {
			$errors[] = $label["employer_signup_error_user"];
		} else {
			$sql = "SELECT * FROM `employers` WHERE `Username`='".jb_escape_sql($_REQUEST['Username'])."' ";
			
			$result = JB_mysql_query ($sql) or die(mysql_error().$sql);
			
			$row=mysql_fetch_array($result, MYSQL_ASSOC) ;
			if ($row['Username'] != false ) {
				$errors[] = str_replace ( '%username%', jb_escape_html($_REQUEST['Username']), $label['employer_signup_error_inuse']);
			}  elseif (!preg_match('#^[a-z0-9À-ÿ\-_\.@]+$#Di', $_REQUEST['Username'])) {
				$errors[] = $label['employer_signup_error_uname'];
			}

		}
		
		if ($_REQUEST['Password'] ==false) {

			$errors[] = $label["employer_signup_error_p"];

		} elseif (strlen(trim($_REQUEST['Password'])) < 6) {
			$errors[] = $label['employer_signup_error_pw_too_weak']; 
		}

		if ($_REQUEST['Password2']==false) {

			$errors[] = $label['employer_signup_error_p2'];
		}
	}

	if ($_REQUEST['Email']==false) {
		$errors[] = $label["employer_signup_error_email"];
	} elseif (!JB_validate_mail($_REQUEST['Email'])) {

		 $errors[] = $label['employer_signup_error_invemail'];

	 } else {

		if ($_REQUEST['user_id']==false) {
			// for new account signups, make sure the email does not already exist
			$result = JB_mysql_query ("SELECT * from `employers` WHERE `Email`='".jb_escape_sql($_REQUEST['Email'])."'") or die(mysql_error());
			$row=mysql_fetch_array($result, MYSQL_ASSOC);

		}

		if ($row['Email'] != false) {
			$errors[] = $label['employer_signup_email_in_use'];
		} 
	}

	
	if ($_REQUEST['user_id']!=false) {
		if (!is_numeric($_REQUEST['user_id'])) {
			return 'Invalid Input!';
		}
	}


	$_REQUEST['FirstName'] = JB_clean_str($_REQUEST['FirstName']);
	$_REQUEST['LastName'] = JB_clean_str($_REQUEST['LastName']);
	$_REQUEST['CompName'] = JB_clean_str($_REQUEST['CompName']);
	$_REQUEST['Username'] = JB_clean_str($_REQUEST['Username']);
	$_REQUEST['Email'] = JB_clean_str($_REQUEST['Email']);
	$_REQUEST['Newsletter'] = JB_clean_str($_REQUEST['Newsletter']);
	$_REQUEST['Notification1'] = JB_clean_str($_REQUEST['Notification1']);
	$_REQUEST['Notification2'] = JB_clean_str($_REQUEST['Notification2']);
	$_REQUEST['lang'] = JB_clean_str($_REQUEST['lang']);

	
	$error = '';
	JBPLUG_do_callback('valiate_employer_account', $error);

	if ($error) {
		$list = explode('<br>', $error);
		foreach ($list as $item) {
			$errors[] = $item;
		}
	}

	JBPLUG_do_callback('valiate_employer_account_array', $errors); // added in 3.6.6 ($errors is a list)

	$errors = $errors + JB_validate_form_data(4);

	return $errors;
	
}


########################################################

/* 

Function:

JB_delete_employer_files

Description:

Deletes all files stored by a record.
Iterates for each IMAGE and FILE field, and deletes the file
stored for that field.
Useful before deleting an employer record from the database
Used by JB_delete_employer_data

Arguments:

$id - employer id (primary key of the `employers` table

*/

function JB_delete_employer_files ($id) {

	$sql = "select * from form_fields where form_id=4 ";
	$result = JB_mysql_query ($sql) or die (mysql_error());

	while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

		$field_id = $row['field_id'];
		$field_type = $row['field_type'];

		if (($field_type == "FILE")) {
			
			JB_delete_file_from_field_id("employers", "ID", $id, $field_id);
			
		}

		if (($field_type == "IMAGE")){
			
			JB_delete_image_from_field_id("employers", "ID", $id, $field_id);
			
		}
		
	}


}

####################

/* 

Function:

JB_delete_employer

Description:

Delete employer from the database.

Arguments:

$id - employer id

Returns:

1 if employer was deleted

*/

function JB_delete_employer ($id) {

	$sql = "SELECT * from `profiles_table` WHERE `user_id`='".jb_escape_sql($id)."'";
	$result = JB_mysql_query($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	// delete profile, if exists
	if ($row['profile_id']!='') {
	   JB_delete_profile ($row['profile_id']);
	}

	// get all the posts and delete them
	$sql = "SELECT * from `posts_table` WHERE `user_id`='".jb_escape_sql($id)."'";
	$result = JB_mysql_query($sql) or die (mysql_error());
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		JB_delete_post($row['post_id']);
		$sql = "DELETE FROM `saved_jobs` WHERE `post_id`='".jb_escape_sql($row['post_id'])."'";
		JB_mysql_query($sql) or die (mysql_error());
	}
	JB_finalize_post_updates();

	JB_delete_employer_files ($id);

	// delete requests..

	$sql = "DELETE FROM `requests` WHERE `employer_id`='".jb_escape_sql($id)."'";
	JB_mysql_query($sql) or die (mysql_error());

	// delete invoices 

	$sql = "DELETE FROM `package_invoices` WHERE `employer_id`='".jb_escape_sql($id)."'";
	JB_mysql_query($sql) or die (mysql_error());

	$sql = "DELETE FROM `subscription_invoices` WHERE `employer_id`='".jb_escape_sql($id)."'";
	JB_mysql_query($sql) or die (mysql_error());

	$sql = "DELETE FROM `membership_invoices` WHERE `user_id`='".jb_escape_sql($id)."'";
	JB_mysql_query($sql) or die (mysql_error());

	$sql = "DELETE FROM `applications` WHERE `employer_id`='".jb_escape_sql($id)."'";
	JB_mysql_query($sql) or die (mysql_error());

	// finally, delete the employer account.

	$sql = "DELETE FROM `employers` WHERE `ID`='".jb_escape_sql($id)."'";
	JB_mysql_query($sql) or die (mysql_error());

	$affected = jb_mysql_affected_rows();

	JBPLUG_do_callback('delete_employer_account', $id);

	return $affected;



}
################################

/* 

Function:

JB_update_employer_account

Description:

Update employer account details.

Arguments:

$user_id = primary key (ID) of the employers table
$admin - boolean, true if called by Admin

The data is fetched from $_REQUEST using the form fields
generated by JBDynamicFormObject display_form method.

eg.

$EmployerForm = &JB_get_DynamicFormObject(4);
$EmployerForm->display_form('edit', true);

Returns:

true if row was changed.


*/

function JB_update_employer_account ($user_id, $admin) {

	
	
	// Notice that password is not updated here
	$assign = array(
		'Newsletter' => (int) $_REQUEST['Newsletter'],
		'Notification1' => (int) $_REQUEST['Notification1'],
		'Notification2' => (int) $_REQUEST['Notification2'],
		'FirstName' => $_REQUEST['FirstName'],
		'LastName' => $_REQUEST['LastName'],
		'CompName' => $_REQUEST['CompName'],
		'Email' => $_REQUEST['Email'],
		'lang' => $_REQUEST['lang']
	);

	if ($admin) {

		// append admin only values		
		$assign['membership_active'] = $_REQUEST['membership_active'];
		$assign['posts_balance'] = $_REQUEST['posts_balance'];
		$assign['premium_posts_balance'] = $_REQUEST['premium_posts_balance'];
		$assign['subscription_can_view_resume'] = $_REQUEST['subscription_can_view_resume'];
		$assign['subscription_can_premium_post'] = $_REQUEST['subscription_can_premium_post'];
		$assign['subscription_can_post'] = $_REQUEST['subscription_can_post'];
		$assign['can_view_blocked'] = $_REQUEST['can_view_blocked'];

	}


	$sql = "UPDATE `employers` SET ".JB_get_sql_update_values (4, "employers", "ID", $user_id, $user_id, $assign)." WHERE ID='".jb_escape_sql($user_id)."'";

    JB_mysql_query($sql) or die ($sql.mysql_error()); 

	JBPLUG_do_callback('update_employer_account', $user_id, $admin);

	return jb_mysql_affected_rows();


}

//////////////////////////////////////////////

/* 

Function:

JB_create_new_employer

Description:

Creates a new employer account
Input from $_REQUEST
Sends confirmation email (email template 2) if enabled
also sends a copy of the email to admin if enabled


Arguments:

none

Returns:

ID of the employer created (primary key)


*/


function JB_create_new_employer_account () {

	if ($_REQUEST['lang']=='') {	
		$_REQUEST['lang'] = JB_get_default_lang();
	}

   global $label;
   global $jb_mysql_link;
  
   $validated = 0;

   if ((JB_EM_NEEDS_ACTIVATION == "AUTO") || (JB_EM_NEEDS_ACTIVATION == "FIRST_POST") )  {
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
		'posts_balance' => JB_BEGIN_STANDARD_CREDITS,
		'premium_posts_balance' => JB_BEGIN_PREMIUM_CREDITS,
		'Password' => md5(stripslashes($_REQUEST['Password'])),
		'expired' => 'N'
	);

	$sql = "REPLACE INTO `employers` (".JB_get_sql_insert_fields(4, $assign).") VALUES (".JB_get_sql_insert_values(4, "employers", "ID", $employer_id, '', $assign).") ";
 

    $result = JB_mysql_query($sql); 
	$employer_id = JB_mysql_insert_id();
   

    if ($employer_id > 0) {
       
	   JBPLUG_do_callback('create_employer_account', $employer_id);
     
    } 

	$result = JB_get_email_template (2, $_SESSION['LANG']);
	$e_row = mysql_fetch_array($result, MYSQL_ASSOC);

	$subject = str_replace ("%MEMBERID%", stripslashes($_REQUEST['Username']), $e_row['EmailSubject']);

	$EmailMessage = str_replace ("%FNAME%", stripslashes($_REQUEST['FirstName']), $e_row['EmailText']);
	$EmailMessage = str_replace ("%LNAME%", stripslashes($_REQUEST['LastName']), $EmailMessage);
	$EmailMessage = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $EmailMessage);
	$EmailMessage = str_replace ("%SITE_NAME%", JB_SITE_NAME, $EmailMessage);
	$EmailMessage = str_replace ("%MEMBERID%", stripslashes($_REQUEST['Username']), $EmailMessage);
	$EmailMessage = str_replace ("%PASSWORD%", stripslashes($_REQUEST['Password']), $EmailMessage);
	$EmailMessage = str_replace ("%SITE_URL%", JB_BASE_HTTP_PATH, $EmailMessage);


	JBPLUG_do_callback('employer_signup_email_msg', $EmailMessage, $employer_id);

	if (!defined('JB_EMAIL_EMP_SIGNUP')) {
		define ('JB_EMAIL_EMP_SIGNUP', 'YES');
	}
	if (JB_EMAIL_EMP_SIGNUP=='YES') {
		$email_id = JB_queue_mail(stripslashes($_REQUEST['Email']), jb_get_formatted_name(stripslashes($_REQUEST['FirstName']), stripslashes($_REQUEST['LastName'])), $e_row['EmailFromAddress'], $e_row['EmailFromName'], $subject, $EmailMessage, '', 2);

		JB_process_mail_queue(1, $email_id);

	}

	$to = JB_SITE_CONTACT_EMAIL;

	if (JB_EMAIL_EMPLOYER_SIGNUP_SWITCH=='YES') {

		$email_id = JB_queue_mail($to, "Admin", JB_SITE_CONTACT_EMAIL, JB_SITE_NAME, $subject, $EmailMessage, '', 2);
		JB_process_mail_queue(1, $email_id);

	}

	return $employer_id;

}

################################################

/* 

Function:

JB_get_employer_lang

Description:

Gets the currently selected language of the employer

Arguments:

$user_id - the ID column of the employers table

Returns:

Returns a string of the language code, eg. EN

*/


function JB_get_employer_lang($user_id) {

	$sql = "SELECT lang form employers WHERE user_id='".jb_escape_sql($user_id)."' ";
	$result = jb_mysql_query($sql);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return $row['lang'];


}


######################################



?>