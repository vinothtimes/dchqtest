<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
define ('JB_PERIOD_DURATION', 'MONTH');

function JB_generate_product_invoice_id() {
	return JB_generate_invoice_id ('P');
}
function JB_generate_subscription_invoice_id() {
	return JB_generate_invoice_id ('S');
}
function JB_generate_membership_invoice_id() {
	return JB_generate_invoice_id ('M');
}
######################################

function JB_generate_invoice_id ($product_type) {

	if ($product_type=='P') {
		$t = 'package_invoices';
	} elseif ($product_type=='S') {
		$t = 'subscription_invoices';
	} elseif ($product_type=='M') { // membership
		$t = 'membership_invoices';
	}

   $query ="SELECT max(`invoice_id`) FROM `$t`";
   $result = JB_mysql_query($query) or die(mysql_error());
   $row = mysql_fetch_row($result);
   $row[0]++;
  
   if (defined('JB_INVOICE_ID_START')) {
	
	   if ($row[0] < JB_INVOICE_ID_START) {
			$row[0] += JB_INVOICE_ID_START; 
	   }
	}
	
	return $row[0];

}


function jb_prefix_order_id($order_id) {
	return strtoupper(substr(md5(JB_SITE_NAME), 1, 3).$order_id);
}

##########################

function jb_strip_order_id($order_id) {
	return substr($order_id, 3);
}


############################

function JB_get_product_invoice_row ($invoice_id) {

	$result = JB_mysql_query ("SELECT * FROM package_invoices WHERE invoice_id='$invoice_id' ") or JB_mail_error(mysql_error());
	$row = mysql_fetch_array ($result, MYSQL_ASSOC);
	return $row;

}

################################################

function JB_get_subscription_invoice_row ($invoice_id) {

	$result = JB_mysql_query ("SELECT * FROM subscription_invoices WHERE invoice_id='$invoice_id' ") or JB_mail_error(mysql_error());
	$row = mysql_fetch_array ($result, MYSQL_ASSOC);
	return $row;

}

################################################

function JB_get_membership_invoice_row ($invoice_id) {

	$result = JB_mysql_query ("SELECT * FROM membership_invoices WHERE invoice_id='".jb_escape_sql($invoice_id)."' ") or JB_mail_error(mysql_error());
	$row = mysql_fetch_array ($result, MYSQL_ASSOC);
	return $row;

}

############################################################
# Get the active subscribtion invoice for the employer
# Returns the $row of the invoice and also employer data including the current
# quta details
function jb_get_active_subscription_invoice($employer_id) {

	$sql = "SELECT *, t2.views_quota as V_QUOTA FROM subscription_invoices as t1, employers as t2 WHERE t1.employer_id=t2.ID AND t1.employer_id='".jb_escape_sql($employer_id)."' AND ((t1.status='Completed' ) OR ((t1.status='Pending') AND t1.reason='jb_credit_advanced')) order by t1.subscr_date DESC LIMIT 1 ";
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	return $row;


}

##################################################

function jb_get_active_membership_invoice($user_id, $user_type='E') {

	$sql = "SELECT * FROM membership_invoices WHERE user_id='".jb_escape_sql($user_id)."' AND ((`status`='Completed' ) OR ((`status`='Pending') AND `reason`='jb_credit_advanced')) and user_type='".jb_escape_sql($user_type)."' order by `member_date` DESC LIMIT 1";
	$result = JB_mysql_query ($sql);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	return $row;

}

##################################################

function JB_update_payment_method ($product_type, $invoice_id, $method) {

	if ($product_type=='S') {
			$table = "subscription_invoices";
		} elseif ($product_type=='P') {
			$table = "package_invoices";
		} elseif ($product_type=='M') {
			$table = "membership_invoices";
		}
		$sql = "UPDATE $table SET payment_method='".jb_escape_sql($method)."' WHERE invoice_id='".jb_escape_sql($invoice_id)."'";
		JB_mysql_query($sql) or die (mysql_error());
	
}

########################################################
function JB_place_package_invoice ($employer_id, $package_id) {
	$status = 'in_cart';
	$id = JB_generate_product_invoice_id();
	$sql = "SELECT * FROM packages WHERE `package_id`='".jb_escape_sql($package_id)."' ";
	$result = JB_mysql_query($sql) or die (mysql_error());
	if (mysql_num_rows($result)==0) {
		return false; // no such package
	}
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$now = (gmdate("Y-m-d H:i:s"));
	$currency_rate = JB_get_currency_rate($row['currency_code']);
	if (!$row['currency_code']) {
		$row['currency_code'] = 'USD';
		$currency_rate = 1;
	}
	$sql = "INSERT INTO `package_invoices` ( `invoice_id` , `invoice_date` , `processed_date` , `status` , `employer_id` , `package_id`, `posts_quantity`, `premium`, `amount`, `item_name`, `currency_rate`, `currency_code`, `reason` )  VALUES ('$id', '$now', NULL , '$status', '$employer_id', '$package_id', '".jb_escape_sql($row['posts_quantity'])."', '".jb_escape_sql($row['premium'])."', '".jb_escape_sql($row['price'])."', '".jb_escape_sql(addslashes($row['name']))."', '".jb_escape_sql($currency_rate)."', '".jb_escape_sql($row['currency_code'])."', '' )";
	$result = JB_mysql_query($sql) or die (mysql_error());
	$invoice_id = JB_mysql_insert_id();

	JB_send_admin_new_invoice_alert('P', $invoice_id);

	return $invoice_id;

}

#############################################################
function JB_place_subscription_invoice ($employer_id, $subscription_id) {
	$status = 'in_cart';
	$id = JB_generate_subscription_invoice_id();
	$sql = "SELECT * FROM subscriptions WHERE `subscription_id`='".jb_escape_sql($subscription_id)."' ";
	$result = JB_mysql_query($sql) or die ($sql.mysql_error());
	if (mysql_num_rows($result)==0) {
		return false; // no such subscription
	}
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$now = (gmdate("Y-m-d H:i:s"));
	$currency_rate = JB_get_currency_rate($row['currency_code']);
	if (!$row['currency_code']) {
		$row['currency_code'] = 'USD';
		$currency_rate = 1;
	}
	$sql = "INSERT INTO `subscription_invoices` ( `invoice_id` , `invoice_date` , `processed_date` , `status` , `employer_id` , `subscription_id` , `months_duration` , `amount` , `item_name` , `can_view_resumes` , `can_post` , `can_post_premium`, `can_view_blocked`, `currency_rate`, `currency_code`, `posts_quota`, `p_posts_quota`, `views_quota`, `reason` ) VALUES ('$id', '$now', NULL , '$status', '".jb_escape_sql($employer_id)."', '".jb_escape_sql($subscription_id)."', '".jb_escape_sql($row['months_duration'])."', '".jb_escape_sql($row['price'])."', '".jb_escape_sql(addslashes($row['name']))."', '".jb_escape_sql($row['can_view_resumes'])."', '".jb_escape_sql($row['can_post'])."', '".jb_escape_sql($row['can_post_premium'])."', '".jb_escape_sql($row['can_view_blocked'])."', '".jb_escape_sql($currency_rate)."', '".jb_escape_sql($row['currency_code'])."', '".jb_escape_sql($row['posts_quota'])."', '".jb_escape_sql($row['p_posts_quota'])."', '".jb_escape_sql($row['views_quota'])."', '')";
	
	$result = JB_mysql_query($sql) or die ($sql.mysql_error());
	$invoice_id = JB_mysql_insert_id();
	JB_send_admin_new_invoice_alert('S', $invoice_id);
	return $invoice_id;

}

#############################################################


# clone subscription, used for automatically recurring payments
# eg. paypal
function JB_place_subscription_invoice_clone ($original_invoice_id) {
	$status = 'in_cart';
	$id = JB_generate_subscription_invoice_id();
	$sql = "SELECT * FROM subscription_invoices WHERE `invoice_id`='".jb_escape_sql($original_invoice_id)."' ";
	$result = JB_mysql_query($sql) or die ($sql.mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$now = (gmdate("Y-m-d H:i:s"));

	$currency_rate = JB_get_currency_rate($row['currency_code']);
	if (!$row['currency_code']) {
		$row['currency_code'] = 'USD';
		$currency_rate = 1;
	}

	$sql = "INSERT INTO `subscription_invoices` ( `invoice_id` , `invoice_date` , `processed_date` , `status` , `employer_id` , `subscription_id` , `months_duration` , `amount` , `item_name` , `can_view_resumes` , `can_post` , `can_post_premium`, `can_view_blocked`, `currency_rate`, `currency_code`, `posts_quota`, `p_posts_quota`, `views_quota`, `payment_method`, `reason` ) VALUES ('$id', '$now', NULL , '$status', '".jb_escape_sql($row['employer_id'])."', '".jb_escape_sql($row['subscription_id'])."', '".jb_escape_sql($row['months_duration'])."', '".jb_escape_sql($row['amount'])."', '".jb_escape_sql(addslashes($row['item_name']))."', '".jb_escape_sql($row['can_view_resumes'])."', '".jb_escape_sql($row['can_post'])."', '".jb_escape_sql($row['can_post_premium'])."', '".jb_escape_sql($row['can_view_blocked'])."', '".jb_escape_sql($currency_rate)."', '".jb_escape_sql($row['currency_code'])."', '".jb_escape_sql($row['posts_quota'])."', '".jb_escape_sql($row['p_posts_quota'])."', '".jb_escape_sql($row['views_quota'])."', '".jb_escape_sql(addslashes($row['payment_method']))."', '')";
	
	$result = JB_mysql_query($sql) or die ($sql.mysql_error());
	$invoice_id = JB_mysql_insert_id();
	JB_send_admin_new_invoice_alert('S', $invoice_id);
	return $invoice_id;

}

#############################################################
# Note: 
# In the membership_invoice table, the months_duration column is
# simply called `months`...
# also memberships.price -> membership_invoices.amount

function JB_place_membership_invoice ($user_id, $membership_id) {
	$status = 'in_cart';
	$id = JB_generate_membership_invoice_id();
	$sql = "SELECT * FROM memberships WHERE `membership_id`='".jb_escape_sql($membership_id)."' ";
	$result = JB_mysql_query($sql) or die ($sql.mysql_error());
	if (mysql_num_rows($result)==0) {
		return false; // no such membership
	}
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$now = (gmdate("Y-m-d H:i:s"));
	$currency_rate = JB_get_currency_rate($row['currency_code']);
	if (!$row['currency_code']) {
		$row['currency_code'] = 'USD';
		$currency_rate = 1;
	}
	$sql = "INSERT INTO `membership_invoices` ( `invoice_id` , `invoice_date` , `processed_date`, `status` , `user_type` , `user_id` , `membership_id` , `months_duration` , `amount` , `currency_code` , `currency_rate` , `item_name`, `reason`, `member_date`, `member_end`, `payment_method` ) VALUES ('$id', '$now', NULL, '$status', '".jb_escape_sql($row['type'])."', '".jb_escape_sql($user_id)."', '".jb_escape_sql($membership_id)."', '".jb_escape_sql($row['months'])."', '".jb_escape_sql($row['price'])."', '".jb_escape_sql($row['currency_code'])."', '".jb_escape_sql($currency_rate)."', '".jb_escape_sql(addslashes($row['name']))."', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '')";
	
	$result = JB_mysql_query($sql) or die ($sql.mysql_error());
	$invoice_id = JB_mysql_insert_id();
	JB_send_admin_new_invoice_alert('M', $invoice_id);
	return $invoice_id;

}

############################################################
# clone membership, used for automatically recurring payments
# eg. paypal

function JB_place_membership_invoice_clone ($old_invoice_id) {
	$status = 'in_cart';
	$id = JB_generate_membership_invoice_id();
	$sql = "SELECT * FROM membership_invoices WHERE `invoice_id`='".jb_escape_sql($old_invoice_id)."' ";
	$result = JB_mysql_query($sql) or die ($sql.mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$now = (gmdate("Y-m-d H:i:s"));
	$currency_rate = JB_get_currency_rate($row['currency_code']);
	if (!$row['currency_code']) {
		$row['currency_code'] = 'USD';
		$currency_rate = 1;
	}
	$sql = "INSERT INTO `membership_invoices` ( `invoice_id` , `invoice_date` , `processed_date` , `status` , `user_type` , `user_id` , `membership_id` , `months_duration` , `amount` , `currency_code` , `currency_rate` , `item_name`, `payment_method`, `reason`, `member_date`, `member_end` ) VALUES ('$id', '$now', NULL, '".jb_escape_sql($status)."', '".jb_escape_sql($row['user_type'])."', '".jb_escape_sql($row['user_id'])."', '".jb_escape_sql($row['membership_id'])."', '".jb_escape_sql($row['months_duration'])."', '".jb_escape_sql($row['amount'])."', '".jb_escape_sql($row['currency_code'])."', '".jb_escape_sql($currency_rate)."', '".jb_escape_sql(addslashes($row['item_name']))."', '".jb_escape_sql(addslashes($row['payment_method']))."', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00')";
	
	$result = JB_mysql_query($sql) or die ($sql.mysql_error());
	$invoice_id = JB_mysql_insert_id();
	JB_send_admin_new_invoice_alert('M', $invoice_id);
	return $invoice_id;

}

/****

Order statuses:

in_cart - The order is in the cart
Confirmed - Other was confirmed by user
Completed - The transaction completed and user has been credited
Cancelled - Confirmation was cancelled. Orders cannot be cancelled once completed
Expired - Completed orders can be expired
Void - Expired or Cancelled orders become deleted


*/



################################################


function JB_confirm_package_invoice($invoice_id) {

	$invoice_row = JB_get_product_invoice_row ($invoice_id);

	if (($invoice_row['status']!='Confirmed') && ($invoice_row['status']!='Pending')) {

		$sql = "UPDATE package_invoices SET `status`='Confirmed' WHERE invoice_id='".jb_escape_sql($invoice_id)."' ";
		$result = JB_mysql_query($sql) or die (mysql_error());
		if (JB_mysql_affected_rows()>0) {
			$invoice_row['status'] = 'Confirmed';
		}
	}

	
	return $invoice_row;


}
##############################################

function JB_confirm_subscription_invoice($invoice_id) {

	$invoice_row = JB_get_subscription_invoice_row ($invoice_id);

	if (($invoice_row['status']!='Confirmed') && ($invoice_row['status']!='Pending')) {

		$sql = "UPDATE subscription_invoices SET `status`='Confirmed', reason='' WHERE invoice_id='".jb_escape_sql($invoice_id)."' ";
		$result = JB_mysql_query($sql) or die (mysql_error());
		if (JB_mysql_affected_rows()>0) {
			$invoice_row['status'] = 'Confirmed';
		}
	}
	
	return $invoice_row;


}

#################################################

function JB_confirm_membership_invoice($invoice_id) {

	$invoice_row = JB_get_membership_invoice_row ($invoice_id);

	if (($invoice_row['status']!='Confirmed') && ($invoice_row['status']!='Pending')) {

		$sql = "UPDATE membership_invoices SET `status`='Confirmed', reason='' WHERE invoice_id='".jb_escape_sql($invoice_id)."' ";
		$result = JB_mysql_query($sql) or die (mysql_error());
		if (JB_mysql_affected_rows()>0) {
			$invoice_row['status'] = 'Confirmed';
		}
	}
	
	return $invoice_row;


}

##################################################

function JB_add_posting_credits(&$invoice_row) {
	if ($invoice_row['premium']=='Y') {
		$field = 'premium_posts_balance';
	} else {
		$field = 'posts_balance';
	}

	$sql = "UPDATE `employers` SET $field=$field+".jb_escape_sql($invoice_row['posts_quantity'])." WHERE ID='".jb_escape_sql($invoice_row['employer_id'])."' ";
	JB_mysql_query ($sql) or JB_mail_error("[$sql]".mysql_error());

}

//////////////////////////////////////////////////////////////

function JB_subtract_posting_credits(&$invoice_row) {
	if ($invoice_row['premium']=='Y') {
		$field = 'premium_posts_balance';
	} else {
		$field = 'posts_balance';
	}

	$sql = "UPDATE `employers` SET $field=$field-".jb_escape_sql($invoice_row['posts_quantity'])." WHERE ID='".jb_escape_sql($invoice_row['employer_id'])."' ";
	JB_mysql_query ($sql) or JB_mail_error("[$sql]".mysql_error());

	// cannot have a negative balance, set to 0 instead!
	$sql = "UPDATE `employers` SET $field=0 WHERE ID='".jb_escape_sql($invoice_row['employer_id'])."' AND $field < 0 ";
	JB_mysql_query ($sql) or JB_mail_error("[$sql]".mysql_error());

}

##################################################

function JB_start_employer_subscription(&$invoice_row) {

	$now = (gmdate("Y-m-d H:i:s")); // qwerty
	$sql = "UPDATE subscription_invoices SET  `processed_date`='$now', `subscr_date`='$now', subscr_end=DATE_ADD('$now', INTERVAL ".jb_escape_sql($invoice_row['months_duration'])."  ".JB_PERIOD_DURATION.") WHERE invoice_id='".jb_escape_sql($invoice_row['invoice_id'])."'";
	$result = JB_mysql_query($sql) or JB_mail_error("[$sql]".mysql_error());
	
	$sql = "UPDATE `employers` SET  subscription_can_premium_post='".jb_escape_sql($invoice_row['can_post_premium'])."', can_view_blocked='".jb_escape_sql($invoice_row['can_view_blocked'])."', subscription_can_view_resume='".jb_escape_sql($invoice_row['can_view_resumes'])."', subscription_can_post='".jb_escape_sql($invoice_row['can_post'])."'  WHERE ID='".jb_escape_sql($invoice_row['employer_id'])."' ";
	

	JB_mysql_query ($sql) or JB_mail_error("[$sql]".mysql_error());

	jb_update_subscription_quota($invoice_row['employer_id']);
	
}
#######################################################
function JB_stop_employer_subscription(&$invoice_row) {

	$now =  (gmdate("Y-m-d H:i:s")); // strawberry fields forever
	$sql = "UPDATE subscription_invoices SET `status`='Stopped', `processed_date`='$now', subscr_end='$now' WHERE invoice_id='".jb_escape_sql($invoice_row['invoice_id'])."'";
	$result = JB_mysql_query($sql) or JB_mail_error("[$sql]".mysql_error());
	
	$sql = "UPDATE `employers` SET  subscription_can_premium_post='N', can_view_blocked='N', subscription_can_view_resume='N', subscription_can_post='N', views_quota=0, posts_quota=0, p_posts_quota=0, views_quota_tally=0, posts_quota_tally=0, p_posts_quota_tally=0, quota_timestamp=0  WHERE ID='".jb_escape_sql($invoice_row['employer_id'])."' ";
	
	JB_mysql_query ($sql) or JB_mail_error("[$sql]".mysql_error());


}

##################################################

function JB_start_membership(&$invoice_row) {

		if (!is_numeric($invoice_row['invoice_id'])) {
			return false;
		}

		$now = (gmdate("Y-m-d H:i:s")); // qwerty
		$date_add = '';

		if ($invoice_row['months_duration']=='0') {
			$date_add = '';
		} else {
			$date_add = " , member_end=DATE_ADD('$now', INTERVAL ".jb_escape_sql($invoice_row['months_duration'])."  ".JB_PERIOD_DURATION.") ";

		}
		
		// activate the memberships:

		if ($invoice_row['user_type']=='E') { // employers
			$sql = "UPDATE `employers` SET  membership_active = 'Y'   WHERE ID='".jb_escape_sql($invoice_row['user_id'])."' ";
			JB_mysql_query ($sql) or JB_mail_error("[$sql]".mysql_error());
		} elseif ($invoice_row['user_type']=='C') { // candidates
			$sql = "UPDATE `users` SET  membership_active = 'Y' WHERE ID='".jb_escape_sql($invoice_row['user_id'])."' ";
			JB_mysql_query ($sql) or JB_mail_error("[$sql]".mysql_error());
		}

		$sql = "UPDATE `membership_invoices` SET `member_date`='$now' $date_add  WHERE invoice_id='".jb_escape_sql($invoice_row['invoice_id'])."' ";
		JB_mysql_query ($sql) or JB_mail_error("[$sql]".mysql_error());

	
	
}
#######################################################
function JB_stop_membership(&$invoice_row) {

	if ($invoice_row['user_type']=='E') { // employers
			$sql = "UPDATE `employers` SET  membership_active = 'N' WHERE ID='".jb_escape_sql($invoice_row['user_id'])."' ";
			JB_mysql_query ($sql) or JB_mail_error("[$sql]".mysql_error());
		} elseif ($invoice_row['user_type']=='C') { // candidates
			$sql = "UPDATE `users` SET  membership_active = 'N' WHERE ID='".jb_escape_sql($invoice_row['user_id'])."' ";
			
			JB_mysql_query ($sql) or JB_mail_error("[$sql]".mysql_error());
		}

	

}

##################################################




##################################################
# Complete the order
# Add credits
# [Pending | Confirmed] -> Completed
function JB_complete_package_invoice($invoice_id, $payment_method='') {
	
	$invoice_row = JB_get_product_invoice_row ($invoice_id);

	if ($payment_method=='') {
		$payment_method = $invoice_row['payment_method'];
	}

	if ((strtolower($invoice_row['status'])=='confirmed') || (strtolower($invoice_row['status'])=='pending')) {
		$now = (gmdate("Y-m-d H:i:s"));
		$sql = "UPDATE package_invoices SET `status`='Completed', `payment_method`='".jb_escape_sql($payment_method)."', `processed_date`='$now' WHERE invoice_id='".jb_escape_sql($invoice_id)."'";
		$result = JB_mysql_query($sql)or JB_mail_error("[$sql]".mysql_error());

		if ($invoice_row['reason']!='jb_credit_advanced') { // bank and check modules have the option to advance credits. If the credit was given in advance, then this invoice would have a jb_credit_advanced reason

			JB_add_posting_credits($invoice_row);
		}

		if (JB_EMAIL_ORDER_COMPLETED_SWITCH == 'YES') { // send conformation.

			// get the user's record to send to

			
			$sql = "Select * from employers WHERE ID='".jb_escape_sql($invoice_row['employer_id'])."'";
			$result = JB_mysql_query ($sql) or die (mysql_error());
			$e_row = mysql_fetch_array($result, MYSQL_ASSOC);

				
			$template_r = JB_get_email_template (70, $e_row['lang']);
			$template = mysql_fetch_array($template_r);
			$msg = $template['EmailText'];
			$from = $template['EmailFromAddress'];
			$from_name = $template['EmailFromName'];
			$subject = $template['EmailSubject'];


			$msg = str_replace ("%LNAME%",  $e_row['LastName'], $msg);
			$msg = str_replace ("%FNAME%", $e_row['FirstName'], $msg);
			$msg = str_replace ("%SITE_NAME%", JB_SITE_NAME, $msg);
			$msg = str_replace ("%INVOICE_CODE%", "P".$invoice_row['invoice_id'], $msg);
			$msg = str_replace ("%ITEM_NAME%", $invoice_row['item_name'], $msg);
			$msg = str_replace ("%QUANTITY%", $invoice_row['posts_quantity'], $msg);
			$msg = str_replace ("%INVOICE_AMOUNT%", JB_convert_to_default_currency_formatted($invoice_row['currency_code'], $invoice_row['amount']), $msg);
			$msg = str_replace ("%PAYMENT_METHOD%", $payment_method, $msg);

			$msg = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $msg);
			$msg = str_replace ("%SITE_URL%", JB_BASE_HTTP_PATH, $msg);
			

			preg_match ('#%INVOICE_TAX=\[(.+?)\]%#', $msg, $m);
			$tax_rate = $m[1];
			$invoice_tax = $invoice_row['amount'] - ($invoice_row['amount'] / (1.00 + $tax_rate));
			$invoice_tax = JB_convert_to_default_currency_formatted($invoice_row['currency_code'], $invoice_tax);
			$msg = str_replace ($m[0], $invoice_tax, $msg);


			$msg = str_replace ('$DATE%', jb_get_formatted_date(jb_get_local_time(date('Y-M-d'))), $msg);
			

			$to = $e_row['Email'];
			$to_name = JB_get_formatted_name($e_row['FirstName'], $e_row['LastName']);


			$email_id=JB_queue_mail($to, $to_name, $from, $from_name, $subject, $msg, '', 70);
			JB_process_mail_queue(1, $email_id);

		}
		

	}

}



##################################################
# Complete the order
# Activate the subscription
# [Pending | Confirmed] -> Completed
function JB_complete_subscription_invoice($invoice_id, $payment_method) {

	
	
	$invoice_row = JB_get_subscription_invoice_row ($invoice_id);

	if ($payment_method=='') {
		$payment_method = $invoice_row['payment_method'];
	}

	if (($invoice_row['status']=='Confirmed') || ($invoice_row['status']=='Pending')) {

		$now = (gmdate("Y-m-d H:i:s"));
		$sql = "UPDATE subscription_invoices SET `status`='Completed', `payment_method`='".jb_escape_sql($payment_method)."', `processed_date`='$now' WHERE invoice_id='".jb_escape_sql($invoice_id)."'";
		$result = JB_mysql_query($sql)or JB_mail_error("[$sql]".mysql_error());

		if ($invoice_row['reason']!='jb_credit_advanced') { // bank and check modules have the option to advance credits. If the credit was given in advance, then this invoice would have a jb_credit_advanced reason

			JB_start_employer_subscription($invoice_row);

		}

		
		

		if (JB_EMAIL_ORDER_COMPLETED_SWITCH == 'YES') { // send conformation.

			// get the user's record to send to

			
			$sql = "Select * from employers WHERE ID='".jb_escape_sql($invoice_row['employer_id'])."'";
			$result = JB_mysql_query ($sql) or die (mysql_error());
			$e_row = mysql_fetch_array($result, MYSQL_ASSOC);

			
			$invoice_row = JB_get_subscription_invoice_row ($invoice_id); // reload invoice

				
			$template_r = JB_get_email_template (90, $e_row['lang']);
			$template = mysql_fetch_array($template_r);
			$msg = $template['EmailText'];
			$from = $template['EmailFromAddress'];
			$from_name = $template['EmailFromName'];
			$subject = $template['EmailSubject'];

			$msg = str_replace ("%FNAME%",  $e_row['FirstName'], $msg);
			$msg = str_replace ("%LNAME%", $e_row['LastName'], $msg);
			$msg = str_replace ("%SITE_NAME%", JB_SITE_NAME, $msg);
			$msg = str_replace ("%INVOICE_CODE%", "S".$invoice_row['invoice_id'], $msg);
			$msg = str_replace ("%ITEM_NAME%", $invoice_row['item_name'], $msg);
			$msg = str_replace ("%SUB_START%", JB_get_formatted_time(JB_get_local_time($invoice_row['subscr_date'])), $msg);
			$msg = str_replace ("%SUB_END%", JB_get_formatted_time(JB_get_local_time($invoice_row['subscr_end'])), $msg);
			$msg = str_replace ("%SUB_DURATION%", $invoice_row['months_duration'], $msg);
			
			$msg = str_replace ("%INVOICE_AMOUNT%", JB_convert_to_default_currency_formatted($invoice_row['currency_code'], $invoice_row['amount']), $msg);
			$msg = str_replace ("%PAYMENT_METHOD%", $payment_method, $msg);

			$msg = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $msg);
			$msg = str_replace ("%SITE_URL%", JB_BASE_HTTP_PATH, $msg);

			preg_match ('#%INVOICE_TAX=\[(.+?)\]%#', $msg, $m);
			$tax_rate = $m[1];
			$invoice_tax = $invoice_row['amount'] - ($invoice_row['amount'] / (1.00 + $tax_rate));
			$invoice_tax = JB_convert_to_default_currency_formatted($invoice_row['currency_code'], $invoice_tax);
			$msg = str_replace ($m[0], $invoice_tax, $msg);

			$to = $e_row['Email'];
			$to_name = JB_get_formatted_name($e_row['FirstName'], $e_row['LastName']);
			
			$email_id=JB_queue_mail($to, $to_name, $from, $from_name, $subject, $msg, '', 90);
			JB_process_mail_queue(1, $email_id);
					

		}

	}

}

##################################################
# Complete the membership order
# Activate the membership
# [Pending | Confirmed] -> Completed
function JB_complete_membership_invoice($invoice_id, $payment_method) {

	global $label;

	$now = (gmdate("Y-m-d H:i:s")); // qwerty

	if ($payment_method=='') {
		$payment_method = $invoice_row['payment_method'];
	}
	
	$invoice_row = JB_get_membership_invoice_row ($invoice_id);

	if ($payment_method=='') {
		$payment_method = $invoice_row['payment_method'];
	}

	if (($invoice_row['status']=='Confirmed') || ($invoice_row['status']=='Pending')) {
		
		
		if ($invoice_row['reason']!='jb_credit_advanced') { // bank and check modules have the option to advance membership before payment is received. If the credit was given in advance, then this invoice would have a jb_payment_deferred status

			JB_start_membership($invoice_row); 

		}

		
		$sql = "UPDATE membership_invoices SET `status`='Completed', `payment_method`='".jb_escape_sql($payment_method)."', `processed_date`='$now' WHERE invoice_id='".jb_escape_sql($invoice_id)."'";
		$result = JB_mysql_query($sql)or JB_mail_error("[$sql]".mysql_error());

		if (JB_EMAIL_ORDER_COMPLETED_SWITCH == 'YES') { // send conformation.

			// get the user's record to send to

			if ($invoice_row['user_type'] =='C') { // user's membership?
				$sql = "Select * from users WHERE ID='".jb_escape_sql($invoice_row['user_id'])."'";
			} else {
				$sql = "Select * from employers WHERE ID='".jb_escape_sql($invoice_row['user_id'])."'";
			}

			$result = JB_mysql_query ($sql) or die (mysql_error());
			$e_row = mysql_fetch_array($result, MYSQL_ASSOC);

			
			$invoice_row = JB_get_membership_invoice_row ($invoice_id); // reload invoice

			$template_r = JB_get_email_template (110, $e_row['lang']);
			$template = mysql_fetch_array($template_r);
			$msg = $template['EmailText'];
			$from = $template['EmailFromAddress'];
			$from_name = $template['EmailFromName'];
			$subject = $template['EmailSubject'];

			$msg = str_replace ("%FNAME%",  $e_row['FirstName'], $msg);
			$msg = str_replace ("%LNAME%", $e_row['LastName'], $msg);
			$msg = str_replace ("%SITE_NAME%", JB_SITE_NAME, $msg);
			$msg = str_replace ("%INVOICE_CODE%", "M".$invoice_row['invoice_id'], $msg);
			$msg = str_replace ("%ITEM_NAME%", $invoice_row['item_name'], $msg);
			$msg = str_replace ("%MEM_START%", JB_get_formatted_time(JB_get_local_time($invoice_row['member_date'])), $msg);
			
			if ($invoice_row['months_duration']=='0') {
				$invoice_row['member_end'] = $label['member_not_expire'];
				$invoice_row['months_duration']=$label['member_not_expire'];
			}
			$msg = str_replace ("%MEM_END%", JB_get_formatted_time($invoice_row['member_end']), $msg);
			$msg = str_replace ("%MEM_DURATION%", $invoice_row['months_duration'], $msg);
			
			$msg = str_replace ("%INVOICE_AMOUNT%", JB_convert_to_default_currency_formatted($invoice_row['currency_code'], $invoice_row['amount']), $msg);
			$msg = str_replace ("%PAYMENT_METHOD%", $payment_method, $msg);

			$msg = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $msg);
			$msg = str_replace ("%SITE_URL%", JB_BASE_HTTP_PATH, $msg);

			preg_match ('#%INVOICE_TAX=\[(.+?)\]%#', $msg, $m);
			$tax_rate = $m[1];
			$invoice_tax = $invoice_row['amount'] - ($invoice_row['amount'] / (1.00 + $tax_rate));
			$invoice_tax = JB_convert_to_default_currency_formatted($invoice_row['currency_code'], $invoice_tax);
			$msg = str_replace ($m[0], $invoice_tax, $msg);

			$to = $e_row['Email'];
			$to_name = JB_get_formatted_name($e_row['FirstName'], $e_row['LastName']);
			


			$email_id=JB_queue_mail($to, $to_name, $from, $from_name, $subject, $msg, '', 110);
			JB_process_mail_queue(1, $email_id);
					 

		}
		
	}

}

####################################################
# Pend the order - awaiting payment.
# Do not add or remove credits
# Confirmed -> Pending
function JB_pend_package_invoice($invoice_id, $payment_method, $pending_reason) {

	$invoice_row = JB_get_product_invoice_row ($invoice_id);

	if ($invoice_row['status']=='Confirmed') {
		$sql = "UPDATE `package_invoices` set status='Pending', payment_method='".jb_escape_sql($payment_method)."', reason='".jb_escape_sql($pending_reason)."' WHERE invoice_id='".jb_escape_sql($invoice_id)."' ";
		JB_mysql_query ($sql) or JB_pp_mail_error(mysql_error());

	}

	$invoice_row['status'] = 'Pending';
	$invoice_row['payment_method'] = $payment_method;
	$invoice_row['reason'] = $pending_reason;

	return $invoice_row;


}

####################################################
# Pend the order - awaiting payment.
# Do not adjust the subscription
# Confirmed -> Pending
function JB_pend_subscription_invoice($invoice_id, $payment_method, $pending_reason) {

	$invoice_row = JB_get_subscription_invoice_row ($invoice_id);

	if ($invoice_row['status']=='Confirmed') {
		$sql = "UPDATE `subscription_invoices` set status='Pending', payment_method='".jb_escape_sql($payment_method)."', reason='".jb_escape_sql($pending_reason)."' WHERE invoice_id='".jb_escape_sql($invoice_id)."' ";
		JB_mysql_query ($sql) or JB_pp_mail_error(mysql_error());

	}
	if (JB_mysql_affected_rows() > 0) {
		$invoice_row['status'] = 'Pending';
		$invoice_row['payment_method'] = $payment_method;
		$invoice_row['reason'] = $pending_reason;
	}

	return $invoice_row;

}

# Pend the membership order - awaiting payment.
# Do not adjust the membership
# Confirmed -> Pending
function JB_pend_membership_invoice($invoice_id, $payment_method, $pending_reason) {

	$invoice_row = JB_get_membership_invoice_row ($invoice_id);

	if ($invoice_row['status']=='Confirmed') {
		$sql = "UPDATE `membership_invoices` set status='Pending', payment_method='".jb_escape_sql($payment_method)."', reason='".jb_escape_sql($pending_reason)."' WHERE invoice_id='".jb_escape_sql($invoice_id)."' ";
		JB_mysql_query ($sql) or JB_pp_mail_error(mysql_error());

	}
	if (JB_mysql_affected_rows() > 0) {
		$invoice_row['status'] = 'Pending';
		$invoice_row['payment_method'] = $payment_method;
		$invoice_row['reason'] = $pending_reason;
	}

	return $invoice_row;

}

#####################################################
# Cancel the order, only orders that were not completed can be cancelled!
# Do not add or remove credits
# in_cart -> Cancelled
# Confirmed -> Cancelled
function JB_cancel_package_invoice($invoice_id) {
	$invoice_row = JB_get_product_invoice_row ($invoice_id);
	if (($invoice_row['status']=='Confirmed') || ($invoice_row['status']=='in_cart') || (strtolower($invoice_row['status'])=='pending')) {

		if ($invoice_row['reason']=='jb_credit_advanced') { // credit was advanced for this order, therefore we need to reverse the credits!
			JB_subtract_posting_credits($invoice_row);
		}

		$sql = "UPDATE package_invoices SET `status`='Cancelled', `reason`='' WHERE invoice_id='".jb_escape_sql($invoice_id)."'";
		$result = JB_mysql_query($sql)or JB_mail_error("[$sql]".mysql_error());
	}



}

#####################################################
# Cancel the order, only orders that were not completed can be cancelled!
# Do not adjust the subscription
# in_cart -> Cancelled
# Confirmed -> Cancelled
function JB_cancel_subscription_invoice($invoice_id) {
	$invoice_row = JB_get_subscription_invoice_row ($invoice_id); 
	if (($invoice_row['status']=='Confirmed') || ($invoice_row['status']=='in_cart') || (strtolower($invoice_row['status'])=='pending')) {
		

		if ($invoice_row['reason']=='jb_credit_advanced') { // subscription was advanced for this order, therefore we need to reverse the credits!

			JB_stop_employer_subscription($invoice_row);

		}

		$sql = "UPDATE subscription_invoices SET `status`='Cancelled', reason='' WHERE invoice_id='".jb_escape_sql($invoice_id)."'";
		$result = JB_mysql_query($sql)or JB_mail_error("[$sql]".mysql_error());

	}


}

#####################################################
# Cancel the order, only orders that were not completed can be cancelled!
# Do not adjust the membership
# in_cart -> Cancelled
# Confirmed -> Cancelled
function JB_cancel_membership_invoice($invoice_id) {
	$invoice_row = JB_get_membership_invoice_row ($invoice_id);
	if (($invoice_row['status']=='Confirmed') || ($invoice_row['status']=='in_cart') || (strtolower($invoice_row['status'])=='pending')) {

		if ($invoice_row['reason']=='jb_credit_advanced') { // membership was advanced for this order, therefore we need to stop it!
			//JB_subtract_posting_credits($invoice_row);
			JB_stop_membership($invoice_row);
		}

		$sql = "UPDATE membership_invoices SET `status`='Cancelled', `reason`='' WHERE invoice_id='".jb_escape_sql($invoice_id)."'";
		$result = JB_mysql_query($sql)or JB_mail_error("[$sql]".mysql_error());
	}

}



######################################################
# Cancel & Deduct credits
# minus the transaction
# Completed -> Reversed
function JB_reverse_package_invoice($invoice_id, $reason) {
	$invoice_row = JB_get_product_invoice_row ($invoice_id);

	if ($invoice_row['status']=='Completed') {

		$sql = "UPDATE package_invoices SET `status`='Reversed', reason='".jb_escape_sql($reason)."' WHERE invoice_id='".jb_escape_sql($invoice_id)."'";
		$result = JB_mysql_query($sql)or JB_mail_error("[$sql]".mysql_error());

		if ($invoice_row['premium']=='Y') {
			$field = 'premium_posts_balance';
		} else {
			$field = 'posts_balance';
		}
		
		$sql = "UPDATE `employers` set $field=$field-".jb_escape_sql($invoice_row['posts_quantity'])." WHERE ID='".jb_escape_sql($invoice_row['employer_id'])."' AND $field > 0 ";
		JB_mysql_query ($sql) or JB_mail_error(mysql_error());
		
		
	}

}

######################################################
# Reverse and cancel subscription
# minus the transaction
# Completed -> Reversed
function JB_reverse_subscription_invoice($invoice_id, $reason) {
	$invoice_row = JB_get_subscription_invoice_row ($invoice_id);

	if ($invoice_row['status']=='Completed') {

		$sql = "UPDATE subscription_invoices SET `status`='Reversed', reason='".jb_escape_sql($reason)."' WHERE invoice_id='".jb_escape_sql($invoice_id)."'";
		$result = JB_mysql_query($sql)or JB_mail_error("[$sql]".mysql_error());

		$sql = "UPDATE `employers` set `subscription_can_view_resume`='N', `subscription_can_post`='N', `subscription_can_premium_post`='N', can_view_blocked='N', views_quota=0, posts_quota=0, p_posts_quota=0, views_quota_tally=0, posts_quota_tally=0, p_posts_quota_tally=0, quota_timestamp=0  WHERE ID='".jb_escape_sql($invoice_row['employer_id'])."' ";
		JB_mysql_query ($sql) or JB_mail_error(mysql_error().$sql);

		
	}

}
######################################################
# Reverse and cancel membership
# minus the transaction
# Completed -> Reversed
function JB_reverse_membership_invoice($invoice_id, $reason) {
	$invoice_row = JB_get_membership_invoice_row ($invoice_id);

	if ($invoice_row['status']=='Completed') {

		$sql = "UPDATE membership_invoices SET `status`='Reversed', reason='".jb_escape_sql($reason)."' WHERE invoice_id='".jb_escape_sql($invoice_id)."'";
		$result = JB_mysql_query($sql)or JB_mail_error("[$sql]".mysql_error());

		// Deactivate the memberships:

		if ($invoice_row['user_type']=='E') { // employers
			$sql = "UPDATE `employers` SET  membership_active = 'N' WHERE ID='".jb_escape_sql($invoice_row['user_id'])."' ";
			JB_mysql_query ($sql) or JB_mail_error("[$sql]".mysql_error());
		} elseif ($invoice_row['user_type']=='C') { // candidates
			$sql = "UPDATE `users` SET  membership_active = 'N' WHERE ID='".jb_escape_sql($invoice_row['user_id'])."' ";
			JB_mysql_query ($sql) or JB_mail_error("[$sql]".mysql_error());
		}

		JB_stop_membership($invoice_row);

		
	}

}

# Delete
# Cancelled -> Void
################################################
function JB_void_package_invoice ($invoice_id) {
	$invoice_row = JB_get_product_invoice_row ($invoice_id);
	$sql = "UPDATE `package_invoices` set status='Void' WHERE invoice_id='".jb_escape_sql($invoice_id)."' ";
	JB_mysql_query ($sql) or JB_pp_mail_error(mysql_error());
}

# Delete
# Cancelled -> Void
################################################
function JB_void_subscription_invoice ($invoice_id, $employer_id=false) {
	$invoice_row = JB_get_subscription_invoice_row ($invoice_id);
	$sql = "UPDATE `subscription_invoices` set status='Void' WHERE invoice_id='".jb_escape_sql($invoice_id)."' ";
	if ($employer_id) {
		$sql .= 'AND `employer_id`='.jb_escape_sql($employer_id);
	}
	JB_mysql_query ($sql) or JB_pp_mail_error(mysql_error());
}

# Delete
# Cancelled -> Void
################################################
function JB_void_membership_invoice ($invoice_id) {
	$invoice_row = JB_get_membership_invoice_row ($invoice_id);
	$sql = "UPDATE `membership_invoices` set status='Void' WHERE invoice_id='".jb_escape_sql($invoice_id)."' ";
	JB_mysql_query ($sql) or JB_pp_mail_error(mysql_error());
}


####################################################

function JB_display_package_invoice ($invoice_id) {

	global $label;

	$invoice_row = JB_get_product_invoice_row ($invoice_id);
	?>

	   <table border="0" id="invoice" cellpadding="3"  cellspacing="0">
		<tr>
		   <td class="field"><?php echo $label['package_invoice_no']; ?></td>
		   <td class="value" valign="top">
		   P<?php echo $invoice_row['invoice_id']; ?></td>
		 </tr>
		 <tr>
		   <td class="field"><?php echo $label['package_invoice_desr'];?></td>
		   <td class="value" valign="top">
		   <?php echo $invoice_row['item_name']; ?></td>
		 </tr>
		 <tr>
		   <td class="field"><?php echo $label['package_invoice_quantity']; ?></td>
		   <td class="value" valign="top">
		   <?php echo $invoice_row['posts_quantity']; ?></td>
		 </tr>
		 <tr>
		   <td class="field"><?php echo $label['package_invoice_price'];?>&nbsp; 
		   </td>
		   <td class="value" valign="top">
		   <?php  echo JB_convert_to_default_currency_formatted($invoice_row['currency_code'], $invoice_row['amount'], true); ?></td>
		 </tr>

		  <tr>
		   <td class="field"><?php echo $label['package_invoice_p_type']; ?>&nbsp;
		   </td>
		   <td class="value" valign="top">
		  
		   <?php
			 
			if ($invoice_row['premium'] == 'Y') {
				echo $label['package_invoice_pr_posts'];
			} else {
				echo $label['package_invoice_std_posts'];
			}
		  ?>
			</td>
		 </tr>
		
		 <tr>
		   <td class="field"><?php echo $label['package_invoice_status']; ?>&nbsp;
		   </td>
		   <td class="value" valign="top">
		   <?php  echo JB_get_invoice_status_label($invoice_row['status']); ?>
		   </td>
		 </tr>
		  <?php

			   JBPLUG_do_callback('display_package_invoice', $invoice_row);

		 ?>
	   </table>
	   <?php

}

############################################
// Display the invoice on the next screen after
// it was placed in the cart

function JB_display_subscription_invoice ($invoice_id) {

	global $label;

	$invoice_row = JB_get_subscription_invoice_row ($invoice_id);
	
	?>

	
	   <table border="0" id="invoice" cellpadding="3"  cellspacing="0">
	  
		<tr> 
		   <td class="field"><?php echo $label['subscription_invoice_no']; ?></td>
		   <td  class="value" valign="top">
		   S<?php echo $invoice_row['invoice_id']; ?></td>
		 </tr>
		 <tr>
		   <td  class="field"><?php echo $label['subscription_invoice_descr'];?></td>
		   <td nowrap class="value" valign="top">
		  <?php
	

			echo $invoice_row['item_name']."<br>"; 
			
		if ($invoice_row['can_view_resumes']=='Y') {
			echo '<IMG SRC="'.JB_THEME_URL.'images/tick.gif" WIDTH="17" HEIGHT="16" BORDER="0" ALT=""> '.$label['subscr_can_view_resumes'];
			if ($invoice_row['views_quota']>0) {
				$str = $label['subscr_can_view_resumes_q'];
				$str = str_replace('%QUOTA%', $invoice_row['views_quota'], $str);
				echo $str;
			}
			if (($invoice_row['can_view_blocked']=='Y') && (JB_FIELD_BLOCK_SWITCH=='YES')) {
				echo " ".$label['subscr_can_view_blocked'];
			}
			echo "<br>";
		}

		if (($invoice_row['can_post']=='Y') && (JB_POSTING_FEE_ENABLED=='YES')){
			if ($invoice_row['posts_quota']>0) {
				$str = $label['subscr_can_post_q'];
				$str = str_replace('%QUOTA%', $invoice_row['posts_quota'], $str);
			} else {
				$str = $label['subscr_can_post_unlimited'];
			}
			echo '<IMG SRC="'.JB_THEME_URL.'images/tick.gif" WIDTH="17" HEIGHT="16" BORDER="0" ALT=""> '.$str."<br>";

		}

		if (($invoice_row['can_post_premium']=='Y') && (JB_PREMIUM_POSTING_FEE_ENABLED=='YES')) {
			if ($invoice_row['p_posts_quota']>0) {
				$str = $label['subscr_can_post_pr_q'];
				$str = str_replace('%QUOTA%', $invoice_row['p_posts_quota'], $str);
			} else {
				$str = $label['subscr_can_post_unlimited_pr'];
			}
			echo '<IMG SRC="'.JB_THEME_URL.'images/tick.gif" WIDTH="17" HEIGHT="16" BORDER="0" ALT=""> '.$str."<br>";

		}
			
			?></td>
		 </tr>
		 <tr>
		   <td class="field"><?php echo $label['subscription_invoice_quantity']; ?></td>
		   <td class="value" valign="top">
		   <?php echo $invoice_row['months_duration']; ?></td>
		 </tr>
		 <tr>
		   <td class="field"><?php echo $label['subscription_invoice_price'];?>&nbsp; 
		 
		   </td>
		   <td class="value" valign="top">
		   <?php  echo JB_convert_to_default_currency_formatted($invoice_row['currency_code'], $invoice_row['amount'], true); ?></td> 
		 </tr>

		
		 <tr>
		   <td class="field"><?php echo $label['subscription_invoice_status']; ?>&nbsp; 
		   
		   </td>
		   <td  class="value" valign="top">
		   <?php echo JB_get_invoice_status_label($invoice_row['status']); ?></td>
		 </tr>
		 <?php

			   JBPLUG_do_callback('display_subscription_invoice', $invoice_row);

		 ?>
	   </table>
	   <?php

}

############################################

function JB_display_membership_invoice ($invoice_id) {

	global $label;

	$invoice_row = JB_get_membership_invoice_row ($invoice_id);
	
	?>

	
	   <table border="0" id="invoice" cellpadding="3"  cellspacing="0">
	  
		<tr> 
		   <td class="field"><?php echo $label['member_order_id']; ?></td>
		   <td class="value" valign="top">
		   M<?php echo $invoice_row['invoice_id']; ?></td>
		 </tr>
		 <tr>
		   <td class="field"><?php echo $label['member_ord_descr'];?></td>
		   <td nowrap class="value" valign="top">
		   <?php
	

			echo $invoice_row['item_name']."<br>"; 

			
			
			?></td>
		 </tr>
		 <tr>
		   <td class="field"><?php echo $label['member_duration'] ?></td>
		   <td class="value" valign="top">
		  <?php if ($invoice_row['months_duration']==0) { echo $label['member_unlimited']; } else { echo $invoice_row['months_duration']; } ?></td>
		 </tr>
		 <tr>
		   <td class="field"><?php echo $label['member_price'];?>&nbsp; 
		  
		   </td>
		   <td  class="value" valign="top">
		   <?php  echo JB_convert_to_default_currency_formatted($invoice_row['currency_code'], $invoice_row['amount'], true); ?></td> 
		 </tr>

		
		 <tr>
		   <td class="field"><?php echo $label['member_status']; ?>&nbsp; 
		   
		   </td>
		   <td class="value" valign="top">
		  <?php  echo JB_get_invoice_status_label($invoice_row['status']); ?></td>
		 </tr>
		  <?php
			   JBPLUG_do_callback('display_membership_invoice', $invoice_row);
		 ?>
	   </table>
	   <?php

}

####################################

function JB_get_invoice_status_label($status) {

	global $label;

	switch (strtolower($status)) {

		case "in_cart":
			return $label['invoice_status_in_cart'];
			break;
		case "confirmed":
			return $label['invoice_status_confirmed'];
			break;
		case "completed":
			return $label['invoice_status_completed'];
			break;
		case "cancelled":
			return $label['invoice_status_cancelled'];
			break;
		case "pending":
			return $label['invoice_status_pending'];
			break;
		case "reversed":
			return $label['invoice_status_reserved'];
			break;
		case "expired":
			return $label['invoice_status_expired'];
			break;
		case "void":
			return $label['invoice_status_void'] ;
			break;

	}


}

#################################################
/*

Type:  CREDIT (subtract)

$txn_id = transaction id from 3rd party payment system

$reson = any reason such as chargeback, refund etc..

$origin = paypal, stormpay, admin, etc

$order_id = the corresponding order id.

*/

function JB_credit_transaction($invoice_id, $amount, $currency, $txn_id, $reason, $origin, $product_type) {

	$type = "CREDIT";

	$date = (gmdate("Y-m-d H:i:s"));

	$sql = "SELECT * FROM jb_txn where txn_id='".jb_escape_sql($txn_id)."' and `type`='CREDIT' and origin='".jb_escape_sql($origin)."'";
	$result = JB_mysql_query($sql) or die(mysql_error($sql));
	if (mysql_num_rows($result)!=0) {
		echo 'already credit';
		return; // there already is a credit for this txn_id
	}

// check to make sure that there is a debit for this transaction

	$sql = "SELECT * FROM jb_txn where txn_id='".jb_escape_sql($txn_id)."' and `type`='DEBIT' "; // and origin='$origin'

	$result = JB_mysql_query($sql) or die(mysql_error($sql));
	if (mysql_num_rows($result)>0) {

		

		$sql = "INSERT INTO `jb_txn` (`date` , `invoice_id` , `type` , `amount` , `currency` , `txn_id` , `reason` , `origin`, `product_type`, `reference` ) VALUES ('$date' , '".jb_escape_sql($invoice_id)."', '".jb_escape_sql($type)."', '".jb_escape_sql($amount)."', '".jb_escape_sql($currency)."', '".jb_escape_sql($txn_id)."', '".jb_escape_sql($reason)."', '".jb_escape_sql($origin)."', '".jb_escape_sql($product_type)."', '' )";


		$result = JB_mysql_query ($sql) or die (mysql_error());
	}


}
#################################################
/*

Type: DEBIT (add)

$txn_id = transaction id from 3rd party payment system

$reson = any reason such as chargeback, refund etc..

$origin = paypal, stormpay, admin, etc

$order_id = the corresponding order id.

*/

function JB_debit_transaction($invoice_id, $amount, $currency, $txn_id, $reason, $origin, $product_type, $reference='') {

	
	$type = "DEBIT";
	$date = (gmdate("Y-m-d H:i:s"));
// check to make sure that there is no debit for this transaction already

	$sql = "SELECT * FROM jb_txn where txn_id='$txn_id' and `type`='DEBIT' and origin='$origin' ";

	$result = JB_mysql_query($sql) or die(mysql_error().$sql);
	if (mysql_fetch_array($result, MYSQL_ASSOC)==0) {
		$sql = "INSERT INTO `jb_txn` (`date` , `invoice_id` , `type` , `amount` , `currency` , `txn_id` , `reason` , `origin`, `product_type`, `reference` ) VALUES ('".$date."' , '".jb_escape_sql($invoice_id)."', '".jb_escape_sql($type)."', '".jb_escape_sql($amount)."', '".jb_escape_sql($currency)."', '".jb_escape_sql($txn_id)."', '".jb_escape_sql($reason)."', '".jb_escape_sql($origin)."', '".jb_escape_sql($product_type)."', '".jb_escape_sql($reference)."')";

		$result = JB_mysql_query ($sql) or die (mysql_error().$sql);
	}


}
##################################################

function JB_send_admin_new_invoice_alert($invoice_type, $invoice_id) {

	// get the invoice data
	switch ($invoice_type) {

		case 'P':
			$sql = "SELECT * FROM package_invoices WHERE invoice_id='".jb_escape_sql($invoice_id)."' ";
			break;
		case 'M':
			$sql = "SELECT * FROM membership_invoices WHERE invoice_id='".jb_escape_sql($invoice_id)."' ";
			break;
		case 'S':
			$sql = "SELECT * FROM subscription_invoices WHERE invoice_id='".jb_escape_sql($invoice_id)."' ";
			break;
	}
	$result = jb_mysql_query($sql);
	$invoice_row = mysql_fetch_array($result, MYSQL_ASSOC);

	if ($invoice_type == 'M') { // memberships
		$user_id = $invoice_row['user_id'];
	} else { // postings, subscriptions
		$user_id = $invoice_row['employer_id'];
	}

	// get the user data
	if ($invoice_row['user_type'] =='C') { // memberships can have 'C' for Candidates
		$sql = "Select * from users where ID='".jb_escape_sql($user_id)."'";
	} else {
		$sql = "Select * from employers where ID='".jb_escape_sql($user_id)."'";
	}
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$user_row = mysql_fetch_array($result, MYSQL_ASSOC);


	$template_r = JB_get_email_template (330, $_SESSION['LANG']);
	$template = mysql_fetch_array($template_r);
	$msg = $template['EmailText'];
	$from = $template['EmailFromAddress'];
	$from_name = $template['EmailFromName'];
	$subject = $template['EmailSubject'];
	$to = JB_SITE_CONTACT_EMAIL;
	$to_name = JB_SITE_NAME;
	$subject = str_replace ("%SITE_NAME%", JB_SITE_NAME, $subject);
	$msg = str_replace ("%LNAME%",  $user_row['FirstName'], $msg);
	$msg = str_replace ("%FNAME%", $user_row['LastName'], $msg);
	$msg = str_replace ("%USER%", $user_row['Username'], $msg);
	$msg = str_replace ("%SITE_NAME%", JB_SITE_NAME, $msg);
	$msg = str_replace ("%INVOICE_CODE%", $invoice_type.$invoice_row['invoice_id'], $msg);
	$msg = str_replace ("%ITEM_NAME%", $invoice_row['item_name'], $msg);
	$msg = str_replace ("%INVOICE_AMOUNT%", JB_convert_to_default_currency_formatted($invoice_row['currency_code'], $invoice_row['amount']), $msg);
	
	$msg = str_replace ("%ADMIN_LINK%", JB_BASE_HTTP_PATH."admin/", $msg);
	$msg = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $msg);
	$msg = str_replace ("%SITE_URL%", JB_BASE_HTTP_PATH, $msg);


	if (JB_EMAIL_ADMIN_NEWORD_SWITCH=='YES') {
		$email_id=JB_queue_mail(JB_SITE_CONTACT_EMAIL, JB_SITE_NAME, $from, $from_name, $subject, $msg, '', 330);
		JB_process_mail_queue(1, $email_id);
	}


}

?>