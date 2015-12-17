<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
/*


This group of function is used for table maintenance
Functions that should go here:

- Altering or Changing table structure
- Getting table information: column names, table names
- Getting form meta-information, eg reserved columns, essential fields, etc

*/

/*

Get the mata-data for the fields
*/

# Result Type constants:
define ('JB_DB_MAP', 1); // Return the fields exactly as in the database table, exclude 'virtual' fields
define ('JB_FIELD_LIST', 2); // Return the visible column list for the fields which can be present on the column list, include 'virtual' fields but exclude blank, seperator and note field types
// virtual fields: They are fields not in the database, but created and displayed on-the-fly

function &JB_schema_get_fields($form_id, $result_type=JB_FIELD_LIST) {

	$form_id = (int) $form_id;
	$fields = JB_schema_get_static_fields($form_id, $result_type);

	if ($result_type==JB_DB_MAP) {
		$sql_exclude = " AND field_type != 'BLANK' AND field_type !='SEPERATOR' AND field_type !='NOTE' ";
	}
	$sql = "SELECT t1.*, t2.field_label AS NAME FROM `form_fields` as t1, form_field_translations as t2 where t1.field_id = t2.field_id AND t2.lang='".jb_escape_sql($_SESSION['LANG'])."' AND form_id='".jb_escape_sql($form_id)."' $sql_exclude ORDER BY field_sort ";


	$result = JB_mysql_query($sql);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$fields[$row['field_id']]['field_id'] = $row['field_id'];
		$fields[$row['field_id']]['field_type'] = $row['field_type'];
		$fields[$row['field_id']]['field_label'] = $row['NAME'];
		$fields[$row['field_id']]['template_tag'] = $row['template_tag'];
		$fields[$row['field_id']]['is_hidden'] = $row['is_hidden']; // Is hidden from website. Only visibile on the editing form.

		// sometimes, template tag can be blank
		if ($fields[$row['field_id']]['template_tag']=='') {
			$fields[$row['field_id']]['template_tag'] = $form_id.'_'.$row['field_id'];
		}
		
		switch ($form_id) {
			case 1:
				$fields[$row['field_id']]['is_member'] = $row['is_member'];	
				break;
			case 2:
				$fields[$row['field_id']]['is_anon'] = $row['is_anon']; // resumes can have anonymous fiields
				$fields[$row['field_id']]['is_blocked'] = $row['is_blocked'];
				$fields[$row['field_id']]['is_member'] = $row['is_member'];
				break;
			case 3:
				$fields[$row['field_id']]['is_member'] = $row['is_member'];
				break;
			case 4:
				break;
			case 5:
				break;
			default:
				break;
		}
		
		
	}
	
	JBPLUG_do_callback('schema_get_fields', $fields, $form_id, $result_type);
	
	return $fields;
}

function &JB_schema_get_static_fields($form_id, $result_type=JB_FIELD_LIST) {

	/*
	* Special field types: 
	* ID: primary key, int
	* TIME: Stored as a Datetime
	* VIRTUAL: NOT in the DB, but on the column list
	* 
	*/

	global $label;

	switch ($form_id) {

		case 1: // postings

			$fields['post_id'] = array(
				'field_id' => 'post_id',
				'template_tag' => 'POST_ID',
				'field_label' => $label['post_list_field_label_postid'],
				'field_type' => 'ID',

			);
			$fields['post_date'] = array(
				'field_id' => 'post_date',
				'template_tag' => 'DATE',
				'field_label' => $label['post_list_field_label_date'],
				'field_type' => 'TIME'
			);
			$fields['post_mode'] = array(
				'field_id' => 'post_mode', 
				'template_tag' => 'POST_MODE',
				'field_label' => $label['post_list_field_label_postmode']
			);
			$fields['user_id'] = array(
				'field_id' => 'user_id',
				'template_tag' => 'USER_ID',
				'field_label' => $label['post_list_field_label_userid']
			);
			$fields['pin_x'] = array(
				'field_id' => 'pin_x', 
				'template_tag' => 'PIN_X', 
				'field_label' => $label['post_list_field_label_mapx']
			);
			$fields['pin_y'] = array(
				'field_id' => 'pin_y', 
				'template_tag' => 'PIN_Y', 
				'field_label' => $label['post_list_field_label_mapy']
			);
			$fields['approved'] = array(
				'field_id' => 'approved',
				'template_tag' => 'APPROVED', 
				'field_label' => $label['post_list_field_label_appr']
			);
			$fields['applications'] = array(
				'field_id' => 'applications', 
				'template_tag' => 'APPLICATIONS', 
				'field_label' => $label['post_list_field_label_app']
			);
			$fields['hits'] = array(
				'field_id' => 'hits', 
				'template_tag' => 'HITS', 
				'field_label' => $label['post_list_field_label_views']
			);
			$fields['reason'] = array(
				'field_id' => 'reason', 
				'template_tag' => 'REASON', 
				'field_label' => $label['post_list_field_label_nar']
			);
			$fields['guid'] = array(
				'field_id' => 'guid', 
				'template_tag' => 'GUID', 
				'field_label' => $label['post_list_field_label_guid']
			);
			$fields['source'] = array(
				'field_id' => 'source', 
				'template_tag' => 'SOURCE', 
				'field_label' => $label['post_list_field_label_src']
			);
			$fields['cached_summary'] = array(
				'field_id' => 'cached_summary', 
				'template_tag' => 'CACHED_SUMMARY', 
				'field_label' => 'Description Preview'
			);
			$fields['expired'] = array(
				'field_id' => 'expired', 
				'template_tag' => 'EXPIRED', 
				'field_label' => $label['post_list_field_label_expired']
			);
			$fields['app_type'] = array(
				'field_id' => 'app_type', 
				'template_tag' => 'APP_TYPE', 
				'field_label' => $label['post_list_field_label_app_t']
			);
			$fields['app_url'] = array(
				'field_id' => 'app_url', 
				'template_tag' => 'APP_URL', 
				'field_label' => $label['post_list_field_label_app_url']
			);

			if ( $result_type==JB_FIELD_LIST) {
				$fields['summary'] = array(
					'field_id' => 'summary', 
					'template_tag' => 'POST_SUMMARY', 
					'field_label' => $label['post_list_field_label_descr'],
					'field_type' => 'VIRTUAL'
				);
			}


			break;

		case 2: // resume

			$fields['resume_id'] = array(
				'field_id' => 'resume_id', 
				'template_tag' => 'RESUME_ID', 
				'field_label' => $label['employer_resume_resume_id'],
				'field_type' => 'ID',

			);
			// list_on_web is currently unused
			$fields['list_on_web'] = array(
				'field_id' => 'list_on_web', 
				'template_tag' => 'RES_LIST_ON_WEB', 
				'field_label' => $label['employer_resume_list_on_w']
			);
			$fields['resume_date'] = array(
				'field_id' => 'resume_date', 
				'template_tag' => 'DATE', 
				'field_label' => $label["employer_resume_list_date"],
				'field_type' => 'TIME'
			);
			$fields['user_id'] = array(
				'field_id' => 'user_id', 
				'template_tag' => 'USER_ID', 
				'field_label' => $label['employer_resume_user_id']
			);
			$fields['hits'] = array(
				'field_id' => 'hits', 
				'template_tag' => 'RES_HITS', 
				'field_label' => $label['employer_resume_hits']
			);
			$fields['anon'] = array(
				'field_id' => 'anon', 
				'template_tag' => 'RES_ANON', 
				'field_label' => $label['employer_resume_cba']
			);
			$fields['status'] = array(
				'field_id' => 'status', 
				'template_tag' => 'RES_STATUS', 
				'field_label' => $label['employer_resume_status']
			);
			$fields['approved'] = array(
				'field_id' => 'approved', 
				'template_tag' => 'RES_APPROVED', 
				'field_label' => $label['employer_resume_isapproved']
			);
			$fields['expired'] = array(
				'field_id' => 'expired', 
				'template_tag' => 'EXPIRED', 
				'field_label' => 'Expired'
			);
			

			break;

		case 3: // employer's profile

			$fields['profile_id'] = array
				('field_id' => 'profile_id', 
				'template_tag' => 'PROFILE_ID', 
				'field_label' => 'Profile ID',
				'field_type' => 'ID'
			);
			$fields['user_id'] = array(
				'field_id' => 'user_id', 
				'template_tag' => 'USER_ID', 
				'field_label' => 'User ID'
			);
			$fields['profile_date'] = array(
				'field_id' => 'profile_date', 
				'template_tag' => 'DATE', 
				'field_label' => $label['profile_list_date_field_label'],
				'field_type' => 'TIME'
				
			);
			$fields['expired'] = array(
				'field_id' => 'expired', 
				'template_tag' => 'EXPIRED', 
				'field_label' => 'Expired'
			);



			break;

		case 4:
			$fields['ID'] = array(
				'field_id' => 'ID', 
				'template_tag' => 'ID', 
				'field_label' => 'ID',
				'field_type' => 'ID'
			);
			$fields['IP'] = array(
				'field_id' => 'IP', 
				'template_tag' => 'IP', 
				'field_label' => 'I.P. Addr'
			);
			$fields['SignupDate'] = array(
				'field_id' => 'SignupDate', 
				'template_tag' => 'DATE', 
				'field_label' => 'Signup Date',
				'field_type' => 'TIME'
			);
			$fields['FirstName'] = array(
				'field_id' => 'FirstName', 
				'template_tag' => 'FNAME', 
				'field_label' => 'First Name'
			);
			$fields['LastName'] = array(
				'field_id' => 'LastName', 
				'template_tag' => 'LNAME', 
				'field_label' => 'Last Name'
			);
			if ($result_type==JB_FIELD_LIST) {
				
				$fields['Name'] = array(
					'field_id' => 'Name', 
					'template_tag' => 'NAME', 
					'field_label' => 'Name',
					'field_type' => 'VIRTUAL'
				);
			}
			// Rank is currently unused by the system
			$fields['Rank'] = array(
				'field_id' => 'Rank', 
				'template_tag' => 'RANK', 
				'field_label' => 'Rank'
			);
			$fields['Username'] = array(
				'field_id' => 'Username', 
				'template_tag' => 'USERNAME', 
				'field_label' => 'Username'
			);
			$fields['Password'] = array(
				'field_id' => 'Password', 
				'template_tag' => 'PASS', 
				'field_label' => 'Password',
				'field_type' => 'PASS'
			);
			$fields['Email'] = array(
				'field_id' => 'Email', 
				'template_tag' => 'EMAIL', 
				'field_label' => 'Email'
			);
			$fields['Newsletter'] = array(
				'field_id' => 'Newsletter', 
				'template_tag' => 'NEWS', 
				'field_label' => 'Newsletter'
			);
			$fields['Notification1'] = array(
				'field_id' => 'Notification1', 
				'template_tag' => 'ALERTS', 
				'field_label' => 'Alerts'
			);
			// Notification2 is unused by the system
			$fields['Notification2'] = array(
				'field_id' => 'Notification2', 
				'template_tag' => 'NOTIFY2', 
				'field_label' => ''
			);
			// Aboutme is currently unused
			$fields['Aboutme'] = array(
				'field_id' => 'Aboutme', 
				'template_tag' => 'ABOUTME', 
				'field_label' => ''
			);
			$fields['Validated'] = array(
				'field_id' => 'Validated', 
				'template_tag' => 'VALIDATED', 
				'field_label' => 'Validated'
			);
			$fields['CompName'] = array(
				'field_id' => 'CompName', 
				'template_tag' => 'CNAME', 
				'field_label' => 'Company Name'
			);
			$fields['login_date'] = array(
				'field_id' => 'login_date', 
				'template_tag' => 'LDATE', 
				'field_label' => 'Login Date',
				'field_type' => 'TIME'
			);
			$fields['logout_date'] = array(
				'field_id' => 'logout_date', 
				'template_tag' => 'LODATE', 
				'field_label' => 'Logout Date',
				'field_type' => 'TIME'
			);
			$fields['login_count'] = array(
				'field_id' => 'login_count', 
				'template_tag' => 'LCOUNT', 
				'field_label' => 'Login Count'
			);
			$fields['last_request_time'] = array(
				'field_id' => 'last_request_time', 
				'template_tag' => 'LREQUEST', 
				'field_label' => 'Last Request Time',
				'field_type' => 'TIME'
			);
			$fields['lang'] = array(
				'field_id' => 'lang', 
				'template_tag' => 'LANG', 
				'field_label' => 'Language'
			);
			$fields['posts_quota'] = array(
				'field_id' => 'posts_quota', 
				'template_tag' => 'P_QUOTA', 
				'field_label' => 'Posts Quota'
			);
			$fields['p_posts_quota'] = array(
				'field_id' => 'p_posts_quota', 
				'template_tag' => 'P_P_QUOTA', 
				'field_label' => 'P. Posts Quota'
			);
			$fields['views_quota'] = array(
				'field_id' => 'views_quota', 
				'template_tag' => 'VIEWS_QUOTA', 
				'field_label' => 'Views Quota'
			);
			$fields['posts_quota_tally'] = array(
				'field_id' => 'posts_quota_tally', 
				'template_tag' => 'P_QUOTA_TALLY', 
				'field_label' => 'Posts Tally'
			);
			$fields['p_posts_quota_tally'] = array(
				'field_id' => 'p_posts_quota_tally', 
				'template_tag' => 'P_P_TALLY', 
				'field_label' => 'P. Posts Tally'
			);
			$fields['views_quota_tally'] = array(
				'field_id' => 'views_quota_tally', 
				'template_tag' => 'VIEWS_TALLY', 
				'field_label' => 'Views Tally'
			);
			$fields['quota_timestamp'] = array(
				'field_id' => 'quota_timestamp', 
				'template_tag' => 'Q_TIMESTAMP', 
				'field_label' => 'Quota Time',
				'field_type' => 'TIME',
			);
			$fields['alert_keywords'] = array(
				'field_id' => 'alert_keywords', 
				'template_tag' => 'AKEYS', 
				'field_label' => 'Alert Keywords'
			);
			$fields['alert_last_run'] = array(
				'field_id' => 'alert_last_run', 
				'template_tag' => 'ARUN', 
				'field_label' => 'Last Alert Sent',
				'field_type' => 'TIME'
			);
			// Alert email (was not on the original list < 3.5)
			$fields['alert_email'] = array(
				'field_id' => 'alert_email', 
				'template_tag' => 'AEMAIL', 
				'field_label' => 'Alert Email'
			);
			$fields['posts_balance'] = array(
				'field_id' => 'posts_balance', 
				'template_tag' => 'PBAL', 
				'field_label' => 'Posts Balance'
			);

			$fields['premium_posts_balance'] = array(
				'field_id' => 'premium_posts_balance', 
				'template_tag' => 'PPBAL', 
				'field_label' => "Premium Posts Balance"
			);
			$fields['subscription_can_view_resume'] = array(
				'field_id' => 'subscription_can_view_resume', 
				'template_tag' => 'SUBRESUME', 
				'field_label' => 'Sub. to resumes?'
			);
			$fields['subscription_can_premium_post'] = array(
				'field_id' => 'subscription_can_premium_post', 
				'template_tag' => 'SUBPPOST', 
				'field_label' => 'Sub. to Premium P.?'
			);
			$fields['subscription_can_post'] = array(
				'field_id' => 'subscription_can_post', 
				'template_tag' => 'SUBPOST', 
				'field_label' => 'Sub. to Post?'
			);
			$fields['newsletter_last_run'] = array(
				'field_id' => 'newsletter_last_run', 
				'template_tag' => 'NRUN', 
				'field_label' => 'Last Newsletter Sent',
				'field_type' => 'TIME'
			);
			$fields['alert_query'] = array(
				'field_id' => 'alert_query', 
				'template_tag' => 'AQUERY', 
				'field_label' => 'Alert Query'
			);
			$fields['can_view_blocked'] = array(
				'field_id' => 'can_view_blocked', 
				'template_tag' => 'SUBBLOCKED', 
				'field_label' => 'Sub. see Blocked?'
			);
			$fields['membership_active'] = array(
				'field_id' => 'membership_active', 
				'template_tag' => 'MEM_ACTIVE', 
				'field_label' => 'Member'
			);
			$fields['expired'] = array(
				'field_id' => 'expired', 
				'template_tag' => 'EXPIRED', 
				'field_label' => 'Expired'
			);
			if ( $result_type==JB_FIELD_LIST) {
				$fields['has_profile'] = array(
					'field_id' => 'has_profile', 
					'template_tag' => 'HAS_PROFILE', 
					'field_label' => 'Has Profile',
					'field_type' => 'VIRTUAL'
				);
			}
			if ($result_type==JB_FIELD_LIST) {
				$fields['posts'] = array(
					'field_id' => 'posts', 
					'template_tag' => 'POSTS', 
					'field_label' => '# Posts',
					'field_type' => 'VIRTUAL'
				);
			}
			


			break;

		case 5:

			$fields['ID'] = array(
				'field_id' => 'ID', 
				'template_tag' => 'ID', 
				'field_label' => 'ID',
				'field_type' => 'ID'
			);

			$fields['IP'] = array(
				'field_id' => 'IP', 
				'template_tag' => 'IP', 
				'field_label' => 'I.P. Addr'
			);

			$fields['SignupDate'] = array(
				'field_id' => 'SignupDate', 
				'template_tag' => 'DATE', 
				'field_label' => 'Signup Date',
				'field_type' => 'TIME'
			);

			$fields['FirstName'] = array(
				'field_id' => 'FirstName', 
				'template_tag' => 'FNAME', 
				'field_label' => 'First Name'
			);

			$fields['LastName'] = array(
				'field_id' => 'LastName', 
				'template_tag' => 'LNAME', 
				'field_label' => 'Last Name'
			);

			if ( $result_type==JB_FIELD_LIST) {
				$fields['Name'] = array(
					'field_id' => 'Name', 
					'template_tag' => 'NAME', 
					'field_label' => 'Name',
					'field_type' => 'VIRTUAL'
				);
			}

			$fields['Rank'] = array(
				'field_id' => 'Rank', 
				'template_tag' => 'RANK', 
				'field_label' => 'Rank'
			);

			$fields['Username'] = array(
				'field_id' => 'Username', 
				'template_tag' => 'USERNAME', 
				'field_label' => 'Username'
			);

			$fields['Password'] = array(
				'field_id' => 'Password', 
				'template_tag' => 'PASS', 
				'field_label' => 'Password (MD5)',
				'field_type' => 'PASS'
			);


			$fields['Email'] = array(
				'field_id' => 'Email', 
				'template_tag' => 'EMAIL', 
				'field_label' => 'Email'
			);

			$fields['Newsletter'] = array(
				'field_id' => 'Newsletter', 
				'template_tag' => 'NEWS', 
				'field_label' => 'Newsletter'
			);

			$fields['Notification1'] = array(
				'field_id' => 'Notification1', 
				'template_tag' => 'ALERTS', 
				'field_label' => 'Alerts'
			);
			# not used
			$fields['Notification2'] = array(
				'field_id' => 'Notification2', 
				'template_tag' => 'NOTIFY2', 
				'field_label' => ''
			);
			# not used:
			$fields['Aboutme'] = array(
				'field_id' => 'Aboutme', 
				'template_tag' => 'ABOUT', 
				'field_label' => ''
			);

			$fields['Validated'] = array(
				'field_id' => 'Validated', 
				'template_tag' => 'VALIDATED', 
				'field_label' => 'Validated'
			);

			$fields['login_date'] = array(
				'field_id' => 'login_date', 
				'template_tag' => 'LDATE', 
				'field_label' => 'Login Date',
				'field_type' => 'TIME'
			);

			$fields['logout_date'] = array(
				'field_id' => 'logout_date', 
				'template_tag' => 'LODATE', 
				'field_label' => 'Logout Date',
				'field_type' => 'TIME'
			);

			$fields['login_count'] = array(
				'field_id' => 'login_count', 
				'template_tag' => 'LCOUNT', 
				'field_label' => 'Login Count'
			);

			$fields['last_request_time'] = array(
				'field_id' => 'last_request_time', 
				'template_tag' => 'LREQUEST', 
				'field_label' => 'Last Request Time',
				'field_type' => 'TIME'
			);

			$fields['lang'] = array(
				'field_id' => 'lang', 
				'template_tag' => 'LANG', 
				'field_label' => 'Language'
			);

			$fields['alert_keywords'] = array(
				'field_id' => 'alert_keywords', 
				'template_tag' => 'AKEYS', 
				'field_label' => 'Alert Keywords'
			);

			$fields['alert_last_run'] = array(
				'field_id' => 'alert_last_run', 
				'template_tag' => 'ARUN', 
				'field_label' => 'Last Alert Sent'
			);

			$fields['alert_email'] = array(
				'field_id' => 'alert_email', 
				'template_tag' => 'AEMAIL', 
				'field_label' => 'Alert Email'
			);

			$fields['newsletter_last_run'] = array(
				'field_id' => 'newsletter_last_run', 
				'template_tag' => 'NRUN', 
				'field_label' => 'Last Newsletter Sent',
				'field_type' => 'TIME'
			);

			$fields['alert_query'] = array(
				'field_id' => 'alert_query', 
				'template_tag' => 'AQUERY', 
				'field_label' => 'Alert Query'
			);

			$fields['membership_active'] = array(
				'field_id' => 'membership_active', 
				'template_tag' => 'MEM_ACTIVE', 
				'field_label' => 'Member'
			);

			$fields['expired'] = array(
				'field_id' => 'expired', 
				'template_tag' => 'EXPIRED', 
				'field_label' => 'Expired'
			);
			if ( $result_type==JB_FIELD_LIST) {
				$fields['resume_id'] = array(
					'field_id' => 'resume_id', 
					'template_tag' => 'RESUME_ID', 
					'field_label' => 'Resume ID'
				);

			}

			default:
				/*

				For plugin authors:
				Your plugin can set the $fields array with the following:

				$fields['obj_id'] = array(
					'field_id' => 'obj_id', 
					'template_tag' => 'OBJECT_ID', 
					'field_label' => 'OBJECT ID'
				);
				$fields['user_id'] = array(
					'field_id' => 'user_id', 
					'template_tag' => 'USER_ID', 
					'field_label' => 'User ID'
				);
				$fields['object_date'] = array(
					'field_id' => 'object_date', 
					'template_tag' => 'DATE', 
					'field_label' => 'Date',
					'field_type' => 'TIME'
				);
				$fields['expired'] = array(
					'field_id' => 'expired', 
					'template_tag' => 'EXPIRED', 
					'field_label' => 'Expired'
				);

				*/

				JBPLUG_do_callback('schema_get_static_fields', $fields, $form_id);



			break;


	}

	return $fields;

}

/*

Get the actual columns for the given forms table.

*/

function JB_schema_get_columns($form_id) {

	$table_name = JB_get_table_name_by_id($form_id);

	$sql = 'show columns from '.$table_name;
	$result = JB_mysql_query($sql) or die (mysql_error());
	while ($row = mysql_fetch_row($result)) {
		$columns[$row[0]] = $row[0];
	}

	JBPLUG_do_callback('schema_get_columns', $columns, $form_id);


	return $columns;


}
/*
* Change one field in the table
* Either ALTER table ADD or ALTER table DROP
* Automatically alters the table by comparing  the fields
* in the form_fields table to the actual columns of the table
*/
function JB_schema_alter_table($form_id) {

	$fields = &JB_schema_get_fields($form_id, JB_DB_MAP); // JB_DB_MAP will get the 1 to 1 mapping of the database table
	$columns = JB_schema_get_columns($form_id); // actual columns of the table
	$table_name = JB_get_table_name_by_id($form_id);



	/*
	 * Rules:
	 * If exists in both, do nothing
	 * If exists in form but not table, add to table
	 * if NOT exists form, but is in table, remove from table
	*/

	$change = '';
	$sql_list = array();

	foreach ($fields as $key=>$val) {

		if ($change =='') {
			$sql = "ALTER TABLE `$table_name` ";
		}

		# If exists in both, do nothing
		if (($columns[$key] != '') && 
			($fields[$key]['field_id'] != '')) { // do nothing

		}
		# If exists in form but not table, add to table
		if (($columns[$key] == '') && 
			($fields[$key]['field_id'] != '')) { // ADD to table
			if ($i>0) {$sql .= ", ";}
			jb_schema_add_field($table_name, $key, $fields[$key]['field_type'], $fields[$key]['field_label']);
			$change = 'Y';
			$i++;
			
		}

		

	}
	$i=0;


	##
	foreach ($columns as $key=>$val) {

		# If exists in both, do nothing
		if (($columns[$key] != '') && 
			($fields[$key]['field_id'] != '')) { // do nothing
		}
		
		# if NOT exists form, but is in table, 	($columns = columns in table, $fields = fields in the form_fields table)
		

		/*

		the $key can be numeric, eg 92
		or it can be alpha-numeric, eg 92_lat
		fields consisting of multiple columns such as the google map
		can have multiple columns for each field
		92_lat, 92_lng and 92
		(always prefixed with the field_id)
		Here we need to get the field_id from the column name

		*/
		$m = array(); $field_id = null;
		if ((is_numeric($columns[$key]) || (preg_match('#(\d+)_#', $columns[$key], $m)) ) ) { 
			
			if (isset($m[1])) {
				$field_id = $m[1];
			} else {
				$field_id = $key;
			}

			if ($fields[$field_id]['field_id'] == '') {
				// REMOVE from table
				JB_schema_remove_field($table_name, $key);		
				$change = 'Y';
				$i++;
			}
			
		}
	}
	if ($change == 'Y') {
		//JBPLUG_do_callback('schema_alter_table', $sql, $columns, $fields, $form_id); //plugins can alter the sql
		//JB_mysql_query ($sql) or die (mysql_error().$sql);
		JB_cache_del_keys_for_form($form_id);
		//echo $sql;
		return true;
	} else {
		return false;
	}



}

#######################################################################

function JB_schema_change_table($form_id, $field_id, $new_field_type, $field_label) {

	$form_id = (int) $form_id;
	$field_id = (int) $field_id;

	$table_name = JB_get_table_name_by_id($form_id);

	preg_match ('#\d#', mysql_get_server_info(), $m);
	if (($m[0] > 5) && strlen($field_label)>0) { // mysql v5 or higher?
		$comment = "COMMENT '".addslashes($field_label)."'";
	}

	// get the old definition. Delete auxillary fields

	$sql = "SELECT * FROM `form_fields` WHERE `form_id`='".JB_escape_sql($form_id)."' AND `field_id`='".JB_escape_sql($field_id)."' ";
	$result = jb_mysql_query($sql);
	$row = mysql_fetch_array($result);
	
	$old_def = JB_get_definition($row['field_type']); // $old_field_type = $row['field_type']
	
	if (is_array($old_def)) {
		// drop auxillary fields
		foreach ($old_def as $postfix => $data_type) {
			$sql = "ALTER TABLE `$table_name` DROP `".$field_id."_".$postfix."` ";
			JB_mysql_query ($sql);
		}
	} 

	//
	$new_def = JB_get_definition($new_field_type);

	if (is_array($new_def)) { // create auxillary fields

		foreach ($new_def as $postfix => $data_type) {
			$sql = "ALTER TABLE `$table_name` ADD `".$field_id."_".$postfix."` ".$data_type;
			JB_mysql_query ($sql);
		}
	}

	$sql = "ALTER TABLE ".$table_name." CHANGE `".JB_escape_sql($field_id)."` `".JB_escape_sql($field_id)."` ".JB_get_definition($new_field_type)." $comment ";

	JBPLUG_do_callback('schema_change_table', $sql, $form_id, $field_id, $new_field_type, $field_label); //plugins can alter the sql
	JB_mysql_query($sql);
	JB_cache_del_keys_for_form($form_id);
	return true;


}

#######################################################################


function JB_get_definition($field_type) {

	switch ($field_type) {
		case "TEXT":
			return "VARCHAR( 255 ) NOT NULL DEFAULT '' ";
			break;
		case "GMAP":
			//gmap has two auxillary fields
			return array('lat' => 'FLOAT(10,6) NOT NULL', 'lng' => 'FLOAT(10,6) NOT NULL');
			break;
		case "URL":
			return "TEXT NOT NULL ";
			break;
		case "NUMERIC":
		case "CURRENCY":
			return "DECIMAL(11,2) NULL DEFAULT NULL ";
			break;
		case "INTEGER":
			return "INT(11) NULL DEFAULT NULL";
		case "SEPERATOR":
			break;
		case "EDITOR":
			return "TEXT NOT NULL ";
			break;
		case "CATEGORY":
			return "INT(11) NOT NULL DEFAULT 0";
			break;
		case "DATE":
		case "DATE_CAL":
			return "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'";
			break;
		case "FILE":
			return "VARCHAR( 255 ) NOT NULL DEFAULT ''";
			break;			
		case "BLANK":
			break;
		case "NOTE":
			return "VARCHAR( 255 ) NOT NULL DEFAULT ''";
			break;
		case "CHECK":
			return "VARCHAR( 255 ) NOT NULL DEFAULT ''";
			break;
		case "IMAGE":
			return "VARCHAR( 255 ) NOT NULL DEFAULT ''";
			break;
		case "RADIO":
			return "TEXT NOT NULL ";
			break;
		case "SELECT":
			return "VARCHAR( 255 ) NOT NULL DEFAULT ''";
			break;
		case "MSELECT":
			return "TEXT NOT NULL ";
			break;
		case "TEXTAREA":
			return "TEXT NOT NULL ";
			break;
		default:
			$custom_def = '';
			JBPLUG_do_callback('get_custom_field_definition', $custom_def, $field_type);
			if ($custom_def!='') {
				return $custom_def;
			} 
			return "VARCHAR( 255 ) NOT NULL DEFAULT ''";
			break;

	}

}
#######################################################################
# Reserved fields
#######################################################################

function JB_is_reserved_template_tag($str) {

	switch ($str) {

		case "DATE":
			return true;
		case "LOCATION":
			return true;
		case "POSTED_BY":
			return true;
		case "POSTED_BY_ID":
			return true;
		case "POSTED_DATE":
			return true;
		case "POST_MODE":
			return true;
		case "LOCATION":
			return true;
		case "DESCRIPTION":
			return true;
		case "REASON":
			return true;
		case "HITS":
			return true;
		case "JOB_TYPE":
			return true;
		case "CLASS":
			return true;
		case "TITLE":
			return true;
		case "EMAIL":
			return true;
		case "IMAGE":
			return true;
		case "RESUME_EMAIL":
			return true;
		case "RESUME_NAME":
			return true;
		case "APP_URL":
			return true;
		case "APP_TYPE":
			return true;
		case "EXPIRED":
			return true;
		case "CACHED_SUMMARY":
			return true;
		case "GUID":
			return true;
		case "RES_APPROVED":
			return true;
		case "RES_STATUS":
			return true;
		case "RES_ANON":
			return true;
		case "RES_LIST_ON_WEB":
			return true;
		case "RESUME_ID":
			return true;
		case "RES_HITS":
			return true;
		case "RESUME_USER_ID":
			return true;
		case "P_QUOTA":
			return true;
		case "P_P_QUOTA":
			return true;
		case "VIEWS_QUOTA":
			return true;
		case "MEM_ACTIVE":
			return true;
		case "AQUERY":
			return true;
		case "NRUN":
			return true;
		case "SUBPOST":
			return true;
		case "SUBBLOCKED":
			return true;
		case "SUBPPOST":
			return true;
		case "NRUN":
			return true;
		case "PBAL":
			return true;
		case "PPBAL":
			return true;
		case "AKEYS":
			return true;
		case "GUID":
			return true;
		case "P_QUOTA_TALLY":
			return true;
		case "P_P_TALLY":
			return true;
		case "P_QUOTA_TALLY":
			return true;
		case "PROFILE_BNAME":
			return true;
		case "PROFILE_ABOUT":
			return true;
		default:
			$retval = false;
			JBPLUG_register_callback('is_reserved_template_tag', $retval, $str);
			return $retval;


	}


}

##############################

function JB_get_reserved_tag_description($str) {

	switch ($str) {

		case "RES_HITS":
			return 'reserved by the system';
		case "RESUME_ID":
			return 'reserved by the system';
		case "USER_ID":
			return 'reserved by the system';
		case "RES_LIST_ON_WEB":
			return 'reserved by the system';
		case "RES_ANON":
			return 'reserved by the system';
		case "RES_STATUS":
			return 'reserved by the system';
		case "RES_APPROVED":
			return 'reserved by the system';
		case "LOCATION":
			return "Location - Used in:  when generating RSS feeds, email alerts, etc";
		case "POSTED_BY":
			return "Posted By - when generating RSS fields; email alerst; etc.";
		case "DESCRIPTION":
			return "Description - Used in: when generating RSS fields; email alerts; displaying post description";
		case "JOB_TYPE":
			return "Job Type - This field should be a CATEGORY Type. Used in: when listing posts;  email alerts.";	
		case "TITLE":
			return "Title - Used in: 2nd column when listing posts; when generating RSS feeds, email alerts, etc";
		case "EMAIL";
			return "Email - Used in: When applying to a job post online.";
		case "IMAGE";
			return "If image listing feature is turned on in the config, this field needs to be of Image type. If feature enabled, used in: Displaying resume image; listing resume as preview.  ";
		case "RESUME_EMAIL";
			return "Email - Email address for contacting the candidate. ";
		case "RESUME_NAME";
			return "Resume Email - Column 2 when listing resume. ";
		
		default:
			$retval = false;
			JBPLUG_register_callback('get_reserved_tag_description', $retval, $str);
			return $retval;
			

	}


}




#######################################################################
# Table names, primary key namess
#######################################################################

function JB_get_table_id_column($form_id) {

	switch ($form_id) {
		case 1:
			return 'post_id';
		case 2:
			return 'resume_id';
		case 3:
			return 'profile_id';
		case 4:
			return 'ID';
		case 5:
			return 'ID';
		default:
			$id_col = false;
			JBPLUG_register_callback('get_table_id_colum', $id_col, $form_id);
			return $id_col;
	}

}

###############################################


function JB_get_table_name_by_id($form_id) {

	switch ($form_id) {

		case 1:
			return 'posts_table';
		case 2:
			return 'resumes_table';
		case 3:
			return 'profiles_table';
		case 4:
			return 'employers';
		case 5:
			return 'users';
		default:
			$table_name = false;
			JBPLUG_register_callback('get_table_name_by_id', $table_name, $form_id);
			return $table_name;
	}


}
###############################################

function JB_get_form_id_by_table_name($tname) {

	switch ($tname) {
		case "posts_table":
			$form_id = 1;
			break;
		case "resumes_table":
			$form_id = 2;
			break;
		case "profiles_table";
			$form_id = 3;
			break;
		case "users";
			$form_id = 5;
			break;
		case "employers";
			$form_id = 4;
			break;
		default:
			$form_id = false;
			JBPLUG_register_callback('get_form_id_by_table_name', $form_id, $table_name);
			return $form_id;
			
	}
	return $form_id;


}


###############################################

function JB_is_table_unsaved ($tname) {

	// load cols
	$sql = " show columns from `".JB_escape_sql($tname)."` ";
	
	$result = JB_mysql_query($sql) or die (mysql_error());
	while ($row = mysql_fetch_row($result)) {
		if (preg_match("/^\d+$/", $row[0])) {
			
			$cols[$row[0]] = $row[0];
			
		}
	}

	$form_id = JB_get_form_id_by_table_name($tname);

	
	// load fields (do not cache this query!)
	$sql = "SELECT * FROM `form_fields` where form_id='".JB_escape_sql($form_id)."' AND field_type != 'BLANK' AND field_type !='SEPERATOR' AND field_type !='NOTE'  ";
	
	$result = JB_mysql_query($sql) or die (mysql_error());

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$fields[$row['field_id']]=$row['field_id'];
		
	}

	
	// check table

	if (sizeof($cols)>0){
		foreach ($cols as $c) {
			if ($fields[$c]=='') {
				return $c;
			}
		}
	}

	// check fields
	if (sizeof($fields)>0){
		foreach ($fields as $f) {
			if ($cols[$f]=='') {
				return $f;
			}

		}
	}
	return false;
	
}

###############################################

function JB_schema_add_field ($table_name, $field_id, $field_type, $field_label) {

	$add_sql = '';
	JBPLUG_do_callback('add_field', $add_sql, $field_id, $field_type, $field_label);
	if ($add_sql) {
		return $add_sql;
	}
	preg_match ('#\d#', mysql_get_server_info(), $m);
	if (($m[0] > 5) && strlen($field_label)>0) { // mysql v5 or higher? add a comment
		$comment = "COMMENT '".addslashes($field_label)."'";
	}
	$def = JB_get_definition($field_type);
	if (is_array($def)) {
		foreach ($def as $postfix => $data_type) {
			$sql = "ALTER TABLE `$table_name` ADD `".$field_id."_".$postfix."` ".$data_type;
			JB_mysql_query ($sql);
		}
		$sql = "ALTER TABLE `$table_name` ADD `$field_id` INT(11) NOT NULL "; // useful for storing id
		JB_mysql_query ($sql);

	} else {
		$sql = "ALTER TABLE `$table_name` ADD `$field_id` ".$def." $comment ";
		JB_mysql_query ($sql);
	}
	return true;
	

}

###############################################

function JB_schema_remove_field($table_name, $field_id) {
	$field_type = '';
	JBPLUG_do_callback('remove_field', $remove_sql, $field_id);
	if ($remove_sql) {
		return $remove_sql;
	}

	$sql = "ALTER TABLE `$table_name`  DROP  `$field_id` ";

	JB_mysql_query ($sql);
	return;


}

?>