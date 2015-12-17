<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

# Initialize quota variables when a subscription is started or ended.
function jb_update_subscription_quota($employer_id) {

	$status = JB_get_employer_subscription_status($employer_id);

	if ($status != 'Active') { // reset all quota variables
		$sql = "UPDATE employers SET views_quota=0, posts_quota=0, p_posts_quota=0, views_quota_tally=0, posts_quota_tally=0, p_posts_quota_tally=0 , quota_timestamp='0' WHERE ID='".jb_escape_sql($employer_id)."' ";

		JB_mysql_query ($sql);

		return; // the employer must have an active subscription

	}

	$sql = "SELECT * FROM employers where ID='".jb_escape_sql($employer_id)."'";
	$result = JB_mysql_query($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	$t = $row['quota_timestamp'];

	// calculate timestamp for 1 month in the future

	$t_next_month = mktime(date('H', $t), date('i', $t), date('s', $t), date('n', $t)+1, date('j', $t), date('Y', $t));


	$now = time();

	// $time_diff is the amount of seconds remaining in the subscription
	$time_diff = $t_next_month - $now;


	
	if ($time_diff < 0) { // update timestamp & reset quotas

		// get the subscription // , t2.views_quota as VQ, t2.posts_quota AS PQ, t2.p_posts_quota AS PPQ
		$sql = "SELECT * FROM subscription_invoices as t1, subscriptions as t2 WHERE t1.subscription_id = t2.subscription_id AND t1.employer_id='".jb_escape_sql($employer_id)."' AND  ((t1.status='Completed' ) OR ((t1.status='Pending') AND t1.reason='jb_credit_advanced')) ORDER BY t1.invoice_date DESC LIMIT 1"; 

		$result = JB_mysql_query ($sql) or die (mysql_error());
		$sub_row = mysql_fetch_array($result, MYSQL_ASSOC);
		
		//Update the employer's quota and tally
		$sql = "UPDATE employers SET views_quota='".jb_escape_sql($sub_row['views_quota'])."', posts_quota='".jb_escape_sql($sub_row['posts_quota'])."', p_posts_quota='".jb_escape_sql($sub_row['p_posts_quota'])."', views_quota_tally='0', posts_quota_tally='0', p_posts_quota_tally='0', quota_timestamp='".$now."' WHERE ID='".jb_escape_sql($employer_id)."' ";

		JB_mysql_query ($sql);

	}


}



############################################################

############################################################

function JB_deduct_posting_credit($employer_id) {

	$subscr_row = jb_get_active_subscription_invoice($employer_id);

	$quota_debited = false;

	// $invoice_row['can_post']=='Y' means can post for free
	// $subscr_row['posts_quota'] > means that a quota is set for the month

	if (($subscr_row['can_post']=='Y') && ($subscr_row['posts_quota']>0)) {
		// update tally of free posts
		$sql = "UPDATE employers SET posts_quota_tally=posts_quota_tally+1 WHERE posts_quota > 0 AND ID='".jb_escape_sql($employer_id)."' AND posts_quota_tally < posts_quota ";
		$result = jb_mysql_query($sql);
		$quota_debited = JB_mysql_affected_rows();
	}

	if ($quota_debited == false) {

		$ac_sql = "UPDATE employers SET `posts_balance`= `posts_balance`-1 WHERE ID='".jb_escape_sql($employer_id)."' AND posts_balance > 0";
		JB_mysql_query ($ac_sql) or die(mysql_error().$sql);

	}


}

function JB_deduct_p_posting_credit($employer_id) {

	$subscr_row = jb_get_active_subscription_invoice($employer_id);

	$quota_debited = false;

	if (($subscr_row['can_post_premium']=='Y') && ($subscr_row['p_posts_quota']>0)) {
		// update the tally of free premium posts
		$sql = "UPDATE employers SET p_posts_quota_tally=p_posts_quota_tally+1 WHERE p_posts_quota > 0 AND ID='".jb_escape_sql($employer_id)."' AND p_posts_quota_tally < p_posts_quota ";
		$result = jb_mysql_query($sql);
		$quota_debited = JB_mysql_affected_rows();
	}

	if ($quota_debited == false) {

		$ac_sql = "UPDATE employers SET `premium_posts_balance`= `premium_posts_balance`-1 WHERE ID='".jb_escape_sql($employer_id)."' AND premium_posts_balance > 0";
		JB_mysql_query ($ac_sql) or die(mysql_error().$sql);

	}


}


// returns the false if it cannot decrement anymore (The value is 0)
function JB_increment_views_tally($id) {

	$sql = " UPDATE employers SET views_quota_tally=views_quota_tally+1 WHERE views_quota > 0 AND ID='".jb_escape_sql($id)."' AND views_quota_tally < views_quota ";
	$result = jb_mysql_query($sql);

	return JB_mysql_affected_rows();


}





############################################################


function JB_get_num_posts_remaining($employer_id) {
	# Returns number of posting credits remaining
	# Returns -1 if unlimited, 0 if none

	$sql = "SELECT * FROM employers where ID='".jb_escape_sql($employer_id)."'";
	$result = JB_mysql_query($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	if (JB_is_privileged_user($employer_id, 'normal')) { 
		return -1;
	}

	$posts = $row['posts_balance'];
	if (JB_SUBSCRIPTION_FEE_ENABLED=='YES') { // get the latest subscription
		$subscr_row = jb_get_active_subscription_invoice($employer_id);
		
		if ($subscr_row['can_post']=='Y') {
			if ($subscr_row['posts_quota']<1) { // no quota
				// subscriptions - can_post, can_post_premium (subscription_invoices)
				return -1; // can post unlimited posts.
			}
			// add the posts allowed by the subscription
			if ($subscr_row) {
				$posts = $posts + ($subscr_row['posts_quota'] - $subscr_row['posts_quota_tally']);
			}
		}
	}

	return $posts;


}
########################################################
# Returns number of premium credits remaining
# Returns -1 if unlimited, 0 if none
function JB_get_num_premium_posts_remaining($employer_id) {

	$sql = "SELECT * FROM employers where ID='".jb_escape_sql($employer_id)."'";
	$result = JB_mysql_query($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	if (JB_is_privileged_user($employer_id, 'premium')) {
		return -1;
	}

	$posts = $row['premium_posts_balance'];
	if (JB_SUBSCRIPTION_FEE_ENABLED=='YES') { // get the latest subscription
		$subscr_row = jb_get_active_subscription_invoice($employer_id);
		// add the posts allowed by the subscription
		
		if ($subscr_row['can_post_premium']=='Y') {
			if ($subscr_row['p_posts_quota']<1) {
				return -1; // can post unlimited premium posts.
			}
			// add the posts allowed by the subscription
			if ($subscr_row) {
				$posts = $posts + ($subscr_row['p_posts_quota'] - $subscr_row['p_posts_quota_tally']);
			}	
		}
	}

	return $posts;
	
}


########################################
# Get the can_view_blocked status from the employer's record
# The can_view_blocked status determines if the employer can view
# blocked fields if subscriptions are enabled.

function JB_get_employer_view_block_status($employer_id) {

	static $status;
	static $cached_employer_id;

	if (isset($status) && ($cached_employer_id==$employer_id)) {
		return $status;
	}
	$cached_employer_id = $employer_id;

	$status = 'N';


	
	///////////////////////////////

	if (JB_SUBSCRIPTION_FEE_ENABLED!='YES') {	
		// subscriptions are not enabled, all employers can view blocked fields
		$status = 'N';
		return 'Y';
	}
	$sql = "SELECT can_view_blocked FROM employers WHERE `ID`='".jb_escape_sql($employer_id)."'";
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	if ($row['can_view_blocked']=='Y') {
		$status = $row['can_view_blocked'];
		return $status;
		
	}
	return $status;
	
	
}
####################################################

function JB_get_member_view_status($user_id, $user_domain='EMPLOYER') {
	static $member_view_status;
	static $cached_user_id;
	static $cached_user_domain;
	if (isset($member_view_status) && ($cached_user_id==$user_id) && ($cached_user_domain==$user_domain)) {
		return $member_view_status;
	}
	$cached_user_id = $user_id;
	$cached_user_domain = $user_domain;

	$member_view_status = 'N';
	if ($_SESSION['JB_ID'] != '') { // Is user logged in?
		$member_view_status = 'Y'; // do not block, DEFAULT

		if ((JB_CANDIDATE_MEMBERSHIP_ENABLED=='YES') && ($user_domain=='CANDIDATE')) {
			
			if (JB_is_candidate_membership_active($user_id)) {
				$member_view_status = 'Y'; // do not block
			} else {
				$member_view_status = 'N'; // block it
			}
		}
		
		if ((JB_EMPLOYER_MEMBERSHIP_ENABLED=='YES') && ($user_domain=='EMPLOYER')) {
			if (JB_is_employer_membership_active($user_id) ) {
				$member_view_status = 'Y';
			} else {
				$member_view_status = 'N';
			}
		}
	}
	return $member_view_status;

}


// Returns 'Active' if the current subscription is active, otherwise returns the current status
// as in the database.

function JB_get_employer_subscription_status($employer_id) {

	$sql = "SELECT * FROM subscription_invoices WHERE  employer_id='".jb_escape_sql($employer_id)."' AND (status='Pending' OR status='Completed') ORDER BY invoice_date DESC LIMIT 1"; 
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	if ( ($row['status']=='Completed') || (($row['status']=='Pending') && ($row['reason']=='jb_credit_advanced'))) {
		return 'Active';

	} else {
		return $row['status'];
	}
	


	

}



##############################

function JB_is_employer_membership_active($employer_id) {

	$sql = "SELECT membership_active FROM employers where ID='".jb_escape_sql($employer_id)."' ";
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	if ($row['membership_active']=='Y')  {
		return true;
	} else {
		return false;
	}



}


###############################

function JB_is_candidate_membership_active($user_id) {

	$sql = "SELECT membership_active FROM users where ID='".jb_escape_sql($user_id)."' ";
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	if ($row['membership_active']=='Y')  {
		return true;
	} else {
		return false;
	}



}




#######################################
# Check the user's post status to be able to view resumes.
# returns true or false
# replaces JB_get_can_post_stat()
// $type can be 'resume' ('premium' or 'normal' -> $_REQUEST['post_mode'])
function JB_is_privileged_user($employer_id, $type) {

	$row = false;

	if (JB_SUBSCRIPTION_FEE_ENABLED=='YES') { // check subscription quotas

		$row = jb_get_active_subscription_invoice($employer_id);
		// This user is subscribed
		// Therefore cannot be privileged... return false
		
		if ($row) return false;
	}

	
	$sql = "SELECT * FROM `employers` WHERE ID='".jb_escape_sql($employer_id)."'";
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	if ($type=='resume') {

		if ($row['subscription_can_view_resume']=='Y') { // subscribed to view resumes posts
			$PRIVILEGED = true;
		}
		
	} elseif ($type=='premium') { // premium

		if ($row['subscription_can_premium_post']=='Y') { // subscribed to view resumes posts
			$PRIVILEGED = true;
		}

	} elseif (JB_POSTING_FEE_ENABLED=='YES') { // standard posts

		if ($row['subscription_can_post']=='Y') { // subscribed to view resumes posts
			$PRIVILEGED = true;
		}


	}

	

	return $PRIVILEGED;

}


##############################################################################
# returns true if a plan with free posting credits exists / false 
# $type can be S or P (S = standard, P = Premium)
function JB_free_posting_subscription_exists($type='S') {

	if ($type=='S') {
		$sql = "SELECT subscription_id FROM subscriptions WHERE can_post='Y' ";
	} else {
		$sql = "SELECT subscription_id FROM subscriptions WHERE can_post_premium='Y' ";
	}
	$result = jb_mysql_query($sql);
	if (mysql_num_rows($result)>0) {
		return true;
	}
	return false;


}

################################################################################

?>