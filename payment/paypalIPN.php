<?php
# Copyright 2005-2009 Jamit Software
# http://www.jamit.com/
/*
/**************************************************
*
* On how to create your own payment modules, please see:
*
* https://www.jamit.com.au/support/index.php?_m=knowledgebase&_a=viewarticle&kbarticleid=4
*
* This file can also be used as an example / starting point
* for your own modules.
*
***************************************************/

require_once "../config.php";
include_once ('../include/accounting_functions.php');
$_PAYMENT_OBJECTS['PayPal'] = new PayPal;//"paypal";

define ('JB_PAYPAL_SUBSCR_DURATION', 'M');

define ('PAYPAL_IPN_LOGGING', 'Y');

function JB_pp_mail_error($msg) {

	$date = date("D, j M Y H:i:s O"); 
	
	$headers = "From: ". JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Reply-To: ".JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Return-Path: ".JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "X-Mailer: PHP" ."\r\n";
	$headers .= "Date: $date" ."\r\n"; 
	$headers .= "X-Sender-IP: $REMOTE_ADDR" ."\r\n";

	pp_log_entry ($msg);

	@mail(JB_SITE_CONTACT_EMAIL, "Error message from ".JB_SITE_NAME." Jamit Paypal IPN script. ", $msg, $headers);

}

function pp_log_entry ($entry_line) {

	if (PAYPAL_IPN_LOGGING == 'Y') {

		JB_payment_log_entry_db ($entry_line, 'PayPal');

		/*
		$entry_line =  "$entry_line\r\n"; 
		$log_fp = @fopen("logs.txt", "a"); 
		@fputs($log_fp, $entry_line); 
		@fclose($log_fp);
		*/
	}
}


function pp_subscr_manual_approve($invoice_id) {

	global $label;


	$sql = "UPDATE `subscription_invoices` set status='Pending',  reason='".jb_escape_sql('reviewing')."', `payment_method`='PayPal' WHERE invoice_id='".jb_escape_sql($invoice_id)."' ";
	JB_mysql_query ($sql) or JB_pp_mail_error(mysql_error());

	$invoice_row = JB_get_subscription_invoice_row ($invoice_id);


	$sql = "Select * FROM employers WHERE ID='".jb_escape_sql($invoice_row['employer_id'])."'";
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$e_row = mysql_fetch_array($result, MYSQL_ASSOC);

	$email = $label['paypal_subscr_manual_review'];
	$email = str_replace('%NAME%', $e_row['FirstName'].' '.$e_row['LastName'], $email);
	$email = str_replace('%SITE_NAME%', JB_SITE_NAME, $email);
	$email = str_replace('%SITE_EMAIL%', JB_SITE_CONTACT_EMAIL, $email);


	$email = str_replace('%INVOICE_ID%', $invoice_id, $email);

	$email_id=JB_queue_mail($e_row['Email'], $e_row['FirstName'].' '.$e_row['LastName'], JB_SITE_CONTACT_EMAIL, JB_SITE_NAME, $label['paypal_subscr_manual_sbj'], $email, '', 677);
	JB_process_mail_queue(1, $email_id);

	// copy to Admin
	$email = $label['paypal_subscr_manual_admin'].$email;
	$email_id=JB_queue_mail(JB_SITE_CONTACT_EMAIL, 'Admin', JB_SITE_CONTACT_EMAIL, JB_SITE_NAME, $label['paypal_subscr_manual_sbj'], $email, '', 678);
	JB_process_mail_queue(1, $email_id);



}


if (($_POST['txn_id']!='') && ($_REQUEST['m']=='')) { 

	if (PAYPAL_USE_CURL!='YES') {

		// check if we can post back to paypal
		if (stristr(ini_get('disable_functions'), "fsockopen")) {
			JB_pp_mail_error ( "<p>fsockopen is disabled on this server, this script can not post information to the PayPal server for IPN confirmation. You can try to set the PayPal module to use cURL instead");
			die();
		}

	}
	
	// read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-validate';

	$result ='';

	foreach ($_POST as $key => $value) {
		
		//if (get_magic_quotes_gpc()) {
		$value = stripslashes($value);
		//}
		$value = urlencode($value);
		$req .= "&$key=$value";
		
	}

	pp_log_entry("Sending to PayPal IPN:".$req);

	if (PAYPAL_USE_CURL=='YES') {

		// Use curl to post back to PayPAl
		// and put the result in a file.
		// open the file using $fp

		$URL = "https://".PAYPAL_SERVER."/cgi-bin/webscr";

		$ch = curl_init();

		if (PAYPAL_CURL_PROXY!='') {
			
			
			curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
			curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			curl_setopt ($ch, CURLOPT_PROXY, PAYPAL_CURL_PROXY);
		}
		

		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ($ch, CURLOPT_URL, $URL);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt ($ch, CURLOPT_POST, TRUE);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		pp_log_entry("IPN - Sending POST to paypal via CURL ");

		$result = curl_exec ($ch);

		pp_log_entry("IPN - Got this back: ".$result);
		pp_log_entry(curl_error($ch));

		$filename = JB_get_cache_dir().md5(time().PAYPAL_AUTH_TOKEN).'IPN.paypal';
		$fp = fopen($filename, 'w');
		fwrite($fp, $result, strlen($result));

		// open for reading
		$fp = fclose($fp);
		$fp = fopen($filename, 'r');

		curl_close ($ch);



	} else {

		// post back to PayPal system to validate
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$fp = fsockopen (PAYPAL_SERVER, 80, $errno, $errstr, 30);

	}

	// assign posted variables to local variables
	$item_name = $_POST['item_name'];
	$item_number = $_POST['item_number'];
	$payment_status = $_POST['payment_status'];
	$mc_gross = $_POST['mc_gross'];
	$mc_currency = $_POST['mc_currency'];
	$payment_type = $_POST['payment_type'];
	$pending_reason = $_POST['pending_reason'];
	$reason_code = $_POST['reason_code'];
	$payment_date = $_POST['payment_date'];
	$txn_id = $_POST['txn_id'];
	$parent_txn_id = $_POST['parent_txn_id'];
	$txn_type = $_POST['txn_type'];
	$receiver_email = $_POST['receiver_email'];
	$payer_email = $_POST['payer_email'];

	$invoice_id = $_POST['invoice'];
	// invoice_id is prefixed with a code when sent to Paypal, so remove prefix

	$invoice_id = jb_strip_order_id($invoice_id);
	$product_type = substr($invoice_id, 0, 1);// 'P' or 'S'
	$invoice_id = substr($invoice_id, 1);

	$business = $_POST['business'];
	$employer_id = $_POST['custom']; // employer_id

	//pp_log_entry("invoice id:".$invoice_id);
	if (!$fp) {
	// HTTP ERROR
		$entry_line =  "HTTP ERROR! cannot post back to PayPal [$errno, $errstr] \r\n "; 
		pp_log_entry($entry_line);
		JB_pp_mail_error($entry_line);
	} else {

		if (PAYPAL_USE_CURL!='YES') {
			fputs ($fp, $header . $req); // post to paypal
		}

		while (!feof($fp)) {
		$res = fgets ($fp, 1024);

			if ((strcmp ($res, "VERIFIED") == 0) || (strpos($result,'VERIFIED')!==false )) {
				pp_log_entry("IPN - Notification verfified!\n");
				$VERIFIED = 1;
				
				// check that receiver_email is your Primary PayPal email
				// check that receiver_email is your Primary PayPal email
				if(strcmp(strtolower(PAYPAL_EMAIL), strtolower($business))!=0) {

					if(strcmp(strtolower(PAYPAL_EMAIL), strtolower($receiver_email))!=0) {
						pp_mail_error ("Possible fraud. Error with receiver_email. ".strtolower(PAYPAL_EMAIL)." != ".strtolower($receiver_email)."\n");
						pp_log_entry("IPN - Possible fraud. Error with receiver_email. ".strtolower(PAYPAL_EMAIL)." != ".strtolower($receiver_email));
						$VERIFIED = false;	
					}
				} 

				// check so that transactrion id cannot be reused

				$sql = "SELECT * FROM jb_txn WHERE txn_id='".jb_escape_sql($txn_id)."' AND origin='PayPal' ";
				$result = JB_mysql_query($sql) or die (mysql_error()); 
				if (mysql_num_rows($result)> 0) { 
					pp_log_entry("transaction already processed ".$txn_id);

					die();
						

				}
			
				
				$entry_line =  "verified: $res";
				pp_log_entry("IPN - ".$entry_line);
			}
			elseif (strcmp ($res, "INVALID") == 0) {
				pp_log_entry('INVALID');
			// log for manual investigation
				$VERIFIED = false;
				JBPLUG_do_callback('pay_trn_verification_failed', $invoice_id, $product_type);

				
			}
		}
		fclose ($fp);
		if (PAYPAL_USE_CURL=='YES') {
			unlink ($filename);

		}

		pp_log_entry("IPN - process payment\n");

		// if VERIFIED=1 process payment
		if ($VERIFIED) {

			pp_log_entry("txn_type is $txn_type\n");
			
			JBPLUG_do_callback('pay_trn_verification_passed', $invoice_id, $product_type);

			if ($txn_type=='subscr_signup') {

			}

			if ($txn_type=='subscr_cancel') {
				JBPLUG_do_callback('pay_trn_subscr_cancelled', $invoice_id, $product_type);
			}

			if ($txn_type=='subscr_modify') {

			}

			if ($txn_type=='subscr_payment') { // process re-bills

				if ($product_type=='S') { 
					// check to see if invoice was already paid, if paid then start a new invoice
					$sql = "SELECT * FROM jb_txn WHERE invoice_id='".jb_escape_sql($invoice_id)."' and product_type='S' AND origin='PayPal' ";
					$result = JB_mysql_query($sql) or JB_pp_mail_error(mysql_error.":".$sql);


					if (mysql_num_rows($result)>0) { // paid & transaction exists. Start a new invoice.
						$invoice_row = JB_get_subscription_invoice_row($invoice_id);
						// force an expiration of the old invoice
						JB_expire_subscription($invoice_row, $send_email=false);
						// clone the old invoice, using old invoice id
						// $invoice_row['invoice_id'] == $old_invoice_id
						$invoice_id = JB_place_subscription_invoice_clone ($invoice_row['invoice_id']);
						// confirm the new invoice clone
						JB_confirm_subscription_invoice($invoice_id);
						pp_log_entry("Placed & confirmed invoice $invoice_id");

						
					} 

					JB_complete_subscription_invoice($invoice_id, 'PayPal');
					JB_debit_transaction($invoice_id, $mc_gross, $mc_currency, $txn_id, $reason_code, "PayPal", $product_type, $_REQUEST['subscr_id']);

				} elseif  ($product_type=='M') { 
					// check to see if invoice was already paid, if paid then start a new invoice
					$sql = "SELECT * FROM jb_txn WHERE invoice_id='$invoice_id' and product_type='M' AND origin='PayPal'  ";
					$result = JB_mysql_query($sql) or JB_pp_mail_error(mysql_error.":".$sql);

					if (mysql_num_rows($result)>0) { // paid & transaction exists. Start a new invoice by cloning the old one.
						$invoice_row = JB_get_membership_invoice_row($invoice_id);
						// force an expiration of the old invoice
						JB_expire_membership($invoice_row, $send_email=false);
						$invoice_id = JB_place_membership_invoice_clone ( $invoice_row['invoice_id']);
						// confirm the new invoice clone
						JB_confirm_membership_invoice($invoice_id);
						pp_log_entry("Placed & confirmed invoice $invoice_id");
					} 

					JB_complete_membership_invoice($invoice_id, 'PayPal');
					JB_debit_transaction($invoice_id, $mc_gross, $mc_currency, $txn_id, $reason_code, "PayPal", $product_type, $_REQUEST['subscr_id']);
				}

				JBPLUG_do_callback('pay_trn_subscr_completed', $invoice_id, $product_type);

			}

			if ($txn_type=='subscr_failed') {

			}

			if ($txn_type=='subscr_eot') {

				if ($product_type=='S') { // subscriptions to view resume
					$invoice_row = JB_get_subscription_invoice_row($invoice_id);
					JB_expire_subscription($invoice_row);

				} if ($product_type=='M') { // Membership
					$invoice_row = JB_get_membership_invoice_row($invoice_id);
					JB_expire_membership($invoice_id);

				}

			}

			if ($txn_type=='subscr_signup') {

			}

			
			if (($txn_type=='web_accept') || ($txn_type=='')) { // transaction came from a button or straight from paypal



				switch ($payment_status) {
					case "Canceled_Reversal":
						
						break;
					case "Completed":
						if ($product_type=='P') {
							pp_log_entry("complete package".$invoice_id);
							JB_complete_package_invoice($invoice_id, 'PayPal');
							
						} elseif ($product_type=='S') {
						
							if (PAYPAL_MANUAL_APPROVE=='Y') {
								pp_subscr_manual_approve($invoice_id);
							} else {
								JB_complete_subscription_invoice($invoice_id, 'PayPal');
							}
							
						} elseif ($product_type=='M') {
							JB_complete_membership_invoice($invoice_id, 'PayPal');
						}
						JBPLUG_do_callback('pay_trn_completed', $invoice_id, $product_type);
						JB_debit_transaction($invoice_id, $mc_gross, $mc_currency, $txn_id, $reason_code, 'PayPal', $product_type, $_REQUEST['subscr_id']);
						break;
					case "Denied":
						// denied by merchant
						$label['payment_return_denied'] = str_replace("%PAYMENT_GW%", $this->name, $label['payment_return_denied']);	
						JBPLUG_do_callback('pay_trn_failed', $invoice_id, $product_type);
						pp_log_entry ("Transaction was denied by merchant. \n");
						break;
					case "Failed":
						// only happens when payment is from customers' bank account
						JBPLUG_do_callback('pay_trn_failed', $invoice_id, $product_type);

						pp_log_entry ("Transaction Failed. (only happens when payment is from customers' bank account)  \n");
						
						break;
					case "Pending":
						if ($product_type=='P') {
							JB_pend_package_invoice($invoice_id, 'PayPal', $pending_reason);
						} elseif ($product_type=='S') {
							JB_pend_subscription_invoice($invoice_id, 'PayPal', $pending_reason);
						} elseif ($product_type=='S') {
							JB_pend_membership_invoice($invoice_id, 'PayPal', $pending_reason);
						}
						JBPLUG_do_callback('pay_trn_verification_pending', $invoice_id, $product_type);

						$label['payment_return_pending'] = str_replace("%PAYMENT_GW%", $this->name, $label['payment_return_pending']);	
						
						// pending_reason : 'address', 'echeck', 'intl', 'multi_currency', 'unilateral', 'upgrade', 'verify', 'other'

						pp_log_entry('pending reason: '.$pending_reason);
					
						break;
					case "Refunded":

						if ($product_type=='P') {
							JB_reverse_package_invoice($invoice_id, $reason_code);
							// reason_code : 'buyer_complaint', 'chargeback', 'guarantee', 'refund', 'other'
						} elseif ($product_type=='S') {
							JB_reverse_subscription_invoice($invoice_id, $reason_code);
						} elseif ($product_type=='M') {
							JB_reverse_membership_invoice($invoice_id, $reason_code);
						}
						JBPLUG_do_callback('pay_trn_verification_reversed', $invoice_id, $product_type);
						JB_credit_transaction($invoice_id, $mc_gross, $mc_currency, $txn_id, $reason_code, 'PayPal', $product_type);
						pp_log_entry('refunded reason: '.$reason_code);
		
						break;
					case "Reversed":
						if ($product_type=='P') {
							JB_reverse_package_invoice($invoice_id, $reason_code);
							// reason_code : 'buyer_complaint', 'chargeback', 'guarantee', 'refund', 'other'
						} elseif ($product_type=='S') {
							JB_reverse_subscription_invoice($invoice_id, $reason_code);
						} elseif ($product_type=='M') {
							JB_reverse_membership_invoice($invoice_id, $reason_code);
						}
						JBPLUG_do_callback('pay_trn_verification_reversed', $invoice_id, $product_type);

						JB_credit_transaction($invoice_id, $mc_gross, $mc_currency, $txn_id, $reason_code, 'PayPal', $product_type);
						
						break;
					default:
						break;
						
				}
			}


		} else {

			JBPLUG_do_callback('pay_trn_verification_failed', $invoice_id, $product_type);

		}

	}
}


############################################################################


###########################################################################
# Payment Object



class PayPal {

	var $name;
	var $description;
	var $className="PayPal";
	

	function PayPal() {

		global $label;

		$this->name=$label['payment_paypal_name'];
		$this->description=$label['payment_paypal_descr'];

		if ($this->is_installed()) {


			$sql = "SELECT * FROM jb_config where `key`='PAYPAL_ENABLED' OR `key`='PAYPAL_EMAIL' OR `key`='PAYPAL_CURRENCY' OR `key`='PAYPAL_BUTTON_URL' OR `key`='PAYPAL_IPN_URL' OR `key`='PAYPAL_RETURN_URL' OR `key`='PAYPAL_CANCEL_RETURN_URL' OR `key`='PAYPAL_PAGE_STYLE' OR `key`='PAYPAL_SERVER' OR `key`='PAYPAL_AUTH_TOKEN' or `key`='PAYPAL_SUBSCR_BUTTON_URL' OR `key`='PAYPAL_CANDIDATE_RETURN_URL' OR `key`='PAYPAL_CANDIDATE_CANCEL_RETURN_URL' OR `key`='PAYPAL_USE_CURL' OR `key`='PAYPAL_CURL_PROXY' OR `key`='PAYPAL_AUTO_REBILL' OR `key`='PAYPAL_MANUAL_APPROVE' ";
			$result = JB_mysql_query($sql) or die (mysql_error().$sql);

			while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {
				define ($row['key'], $row['val']);
			}

			// guess the PAYPAL_CANDIDATE_RETURN_URL and PAYPAL_CANDIDATE_CANCEL_RETURN_URL
			// these two constants were introduced in 2.9.0
			$host = $_SERVER['SERVER_NAME']; // hostname
			$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
			$http_url = explode ("/", $http_url);
			array_pop($http_url); // get rid of filename
			array_pop($http_url); // get rid of /admin
			$http_url = implode ("/", $http_url);

			if (!defined('PAYPAL_CANDIDATE_RETURN_URL')) {

				$url = "http://".$host.$http_url."/".JB_CANDIDATE_FOLDER."thanks.php?m=".$this->className;
				define('PAYPAL_CANDIDATE_RETURN_URL', $url);
			}

			if (!defined('PAYPAL_CANDIDATE_CANCEL_RETURN_URL')) {
				$url = "http://".$host.$http_url."/".JB_CANDIDATE_FOLDER;

				define('PAYPAL_CANDIDATE_CANCEL_RETURN_URL', $url);
				
			}

			if (!defined('PAYPAL_USE_CURL')) {
				define('PAYPAL_CANDIDATE_CANCEL_RETURN_URL', 'NO');
			}

			if (!defined('PAYPAL_CURL_PROXY')) {
				define('PAYPAL_CURL_PROXY', '');
				
			}

			if (!defined('PAYPAL_AUTO_REBILL')) {
				define('PAYPAL_AUTO_REBILL', 'YES'); // re-bill by default
				
			}

			

			

			
			

		}


	}

	function get_currency() {

		return PAYPAL_CURRENCY;

	}


	function install() {

	
	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_ENABLED', 'N')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_EMAIL', '')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_CURRENCY', 'USD')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_BUTTON_URL', 'https://www.paypal.com/en_US/i/btn/x-click-but6.gif')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_SUBSCR_BUTTON_URL', 'https://www.paypal.com/en_US/i/btn/x-click-butcc-subscribe.gif')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_RETURN_URL', '')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_IPN_URL', '')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_CANCEL_RETURN_URL', '')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_PAGE_STYLE', 'default')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_SERVER', 'www.paypal.com')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_AUTH_TOKEN', '')";
		JB_mysql_query($sql);
		// candidate's membership - payment returns
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_CANDIDATE_RETURN_URL', '')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_CANDIDATE_CANCEL_RETURN_URL', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_USE_CURL', 'NO')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_CURL_PROXY', '')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_AUTO_REBILL', 'YES')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_MANUAL_APPROVE', 'NO')";
		JB_mysql_query($sql);

		
		

		
	}

	function uninstall() {

		
		$sql = "DELETE FROM jb_config where `key`='PAYPAL_ENABLED'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='PAYPAL_EMAIL'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='PAYPAL_CURRENCY'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='PAYPAL_BUTTON_URL'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='PAYPAL_SUBSCR_BUTTON_URL'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='PAYPAL_IPN_URL'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='PAYPAL_RETURN_URL'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='PAYPAL_CANCEL_RETURN_URL'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='PAYPAL_PAGE_STYLE'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='PAYPAL_SERVER'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='PAYPAL_AUTH_TOKEN'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='PAYPAL_CANDIDATE_RETURN_URL'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='PAYPAL_CANDIDATE_CANCEL_RETURN_URL'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='PAYPAL_USE_CURL'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='PAYPAL_CURL_PROXY'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='PAYPAL_AUTO_REBILL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='PAYPAL_MANUAL_APPROVE'";
		JB_mysql_query($sql);


		
		

	}

	/*

	Paypal is able to process subscriptions. 


	*/

	function payment_button($order_id, $product_type) {

		$order_row = array();

		if (func_num_args() > 1) {
			$product_type = func_get_arg(1);
		}

		if ($product_type == '') {
			$product_type = 'P'; // posting package
		}

		global $label;

		if ($product_type=='P') {
			$order_row = JB_get_product_invoice_row ($order_id);
		} elseif ($product_type=='S') {
			$order_row = JB_get_subscription_invoice_row($order_id);
		} elseif ($product_type=='M') {
			$order_row = JB_get_membership_invoice_row($order_id);
		}

		if ($product_type == 'S') {  // subscription payments.

			if (PAYPAL_AUTO_REBILL=='YES') {
				$this->paypal_subscr_button($order_row, $product_type);
			} else {
				// just a normal payment button
				$this->paypal_button($order_row, $product_type);
			}

		} elseif ($product_type == 'P') {  // posting credits

			
			$this->paypal_button($order_row, $product_type);

	
		} elseif (($product_type == 'M') && ($order_row['months_duration'] > 0)) {  // membership payment subscription button
			if (PAYPAL_AUTO_REBILL=='YES') {
				$this->paypal_subscr_button($order_row, $product_type);
			} else {
				// just a normal payment button
				$this->paypal_button($order_row, $product_type);
			}

		} elseif (($product_type == 'M') && ($order_row['months_duration'] == '0')) { // membership payment button

			$this->paypal_button($order_row, $product_type);
		}
		?>
		<!-- automatically submit the payment button -->
		<script type="text/javascript">
			function js_submit_payment() {
			var form = document.getElementById('payment_button');
				  form.submit();
			  }
			  window.onload = js_submit_payment;
		</script>

		<?php

	}

	
	// payment button for employers subscriptions, employer's membership
	// and candidate's memberships

	function paypal_subscr_button($order_row, $product_type) {

		

		global $label;
		?>

		<center>
			<form id='payment_button' action="https://<?php echo PAYPAL_SERVER; ?>/cgi-bin/webscr" name="form1" method="post" target="_parent">
			<input type="image" src="<?php echo PAYPAL_SUBSCR_BUTTON_URL; ?>" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
			<input type="hidden" name="cmd" value="_xclick-subscriptions">
			<input type="hidden" name="business" value="<?php echo PAYPAL_EMAIL; ?>">
			<input type="hidden" value="<?php echo PAYPAL_IPN_URL; ?>" name="notify_url">
			<input type="hidden" value="<?php echo JB_escape_html(JB_SITE_NAME); ?> - <?php echo htmlentities($order_row['item_name']);?>" name="item_name">
			<?php
			if ($product_type=='M')  { // membership
			?>
				<input type="hidden" value="<?php echo $order_row['membership_id']; ?>" name="item_number">
			<?php
			} else {
				// its a subscription to the resume db
			?>
				<input type="hidden" value="<?php echo $order_row['subscription_id']; ?>" name="item_number">
			<?php
			}
			?>
			<input type="hidden" value="<?php echo jb_prefix_order_id($product_type.$order_row['invoice_id']);?>" name="invoice" />
			<input type="hidden" value="<?php echo PAYPAL_PAGE_STYLE; ?>" name="page_style">
			<input type="hidden" name="no_shipping" value="1">
			<?php if ($order_row['user_type']=='C') { // candidate ?>
				<input type="hidden" value="<?php echo PAYPAL_CANDIDATE_RETURN_URL; ?>" name="return">
			<?php } else {  // employer ?>
				<input type="hidden" value="<?php echo PAYPAL_RETURN_URL; ?>" name="return">	
			<?php } ?>
			<input type="hidden" value="2" name="rm">
			<?php if ($order_row['user_type']=='C') { // candidate ?>
				<input type="hidden" value="<?php echo PAYPAL_CANDIDATE_CANCEL_RETURN_URL; ?>" name="cancel_return">
			<?php } else {  // employer ?>
				<input type="hidden" value="<?php echo PAYPAL_CANCEL_RETURN_URL; ?>" name="cancel_return">
				
			<?php } ?>
			<input type="hidden" name="no_note" value="1">
			<?php
			if ($product_type=='M')  {
			?>
				<input type="hidden" value="<?php echo $order_row['user_id'] ?>" name="custom">
			<?php
			} else {
				// its a subscription to the resume db
			?>
				<input type="hidden" value="<?php echo $order_row['employer_id'] ?>" name="custom">
			<?php
			}

			?>
			
			<input type="hidden" value="<?php echo PAYPAL_CURRENCY; ?>" name="currency_code">
			<input type="hidden" name="a3" value="<?php echo JB_convert_to_currency($order_row['amount'], $order_row['currency_code'], PAYPAL_CURRENCY); ?>">
			<input type="hidden" name="p3" value="<?php echo $order_row['months_duration']; ?>">
			<input type="hidden" name="t3" value="<?php echo JB_PAYPAL_SUBSCR_DURATION; ?>">
			<input type="hidden" name="src" value="1">
			<input type="hidden" name="sra" value="1">
			
			</form>
			</center>

			<?php


	}

	function paypal_button($order_row, $product_type) {

		global $label;

		?>

			<center><b><?php echo $label['payment_paypal_head']; ?></b>
			<form id='payment_button' action="https://<?php echo PAYPAL_SERVER; ?>/cgi-bin/webscr" name="form1" method="post" target="_parent">
			<center><?php echo $label['payment_paypal_accepts']; ?></center>
			  <input type="hidden" value="_xclick" name="cmd">
			  <input type="hidden" value="<?php echo PAYPAL_EMAIL; ?>" name="business">
			  <input type="hidden" value="<?php echo PAYPAL_IPN_URL; ?>" name="notify_url">
			  <input type="hidden" value="<?php echo JB_escape_html(JB_SITE_NAME); ?> - <?php echo $order_row['item_name'];?>" name="item_name">
			  <?php if ($order_row['user_type']=='C') { // candidate ?>
			  <input type="hidden" value="<?php echo PAYPAL_CANDIDATE_RETURN_URL; ?>" name="return">
			  <?php } else { // employer ?>
			  <input type="hidden" value="<?php echo PAYPAL_RETURN_URL; ?>" name="return">
			  <?php } ?>
			  <?php if ($order_row['user_type']=='C') { // candidate ?>
			  <input type="hidden" value="<?php echo PAYPAL_CANDIDATE_CANCEL_RETURN_URL; ?>" name="cancel_return">
			  <?php } else { // employer ?>
			  <input type="hidden" value="<?php echo PAYPAL_CANCEL_RETURN_URL; ?>" name="cancel_return">
			  <?php } ?>
			  <input type="hidden" value="<?php echo jb_prefix_order_id($product_type.$order_row['invoice_id']);?>" name="invoice" >
			  <input type="hidden" value="<?php echo JB_convert_to_currency($order_row['amount'], $order_row['currency_code'], PAYPAL_CURRENCY); ?>" name="amount">
			  <input type="hidden" value="<?php echo $order_row['invoice_id'];?>" name="item_number">
			 <?php
				if ($order_row['user_id']>0)  {
				?>
				<input type="hidden" value="<?php echo $order_row['user_id'] ?>" name="custom">
			<?php
			} else {
				// its made by employer
			?>
				<input type="hidden" value="<?php echo $order_row['employer_id'] ?>" name="custom">
			<?php
			}
			?>
			  <input type="hidden" value="<?php echo PAYPAL_PAGE_STYLE;?>" name="page_style">
			 
			  <input type="hidden" value="1" name="no_shipping"/>
			  <input type="hidden" value="1" name="no_note"/>
			  <input type="hidden" value="<?php echo PAYPAL_CURRENCY;?>" name="currency_code">
			  <p align="center">
			  <input target="_parent" type="image" alt="<?php echo $label['payment_paypal_bttn_alt']; ?>" src="<?php echo PAYPAL_BUTTON_URL; ?>" border="0" name="submit" >
			  </p>
			</form>
			</center>

		<?php


	}

	function config_form() {

		if ($_REQUEST['action']=='save') {

			$paypal_email = $_REQUEST['paypal_email'];
			$paypal_server = $_REQUEST['paypal_server'];
			$paypal_ipn_url = $_REQUEST['paypal_ipn_url'];
			$paypal_return_url = $_REQUEST['paypal_return_url'];
			$paypal_cancel_return_url = $_REQUEST['paypal_cancel_return_url'];
			$paypal_page_style = $_REQUEST['paypal_page_style'];
			$paypal_currency = $_REQUEST['paypal_currency'];
			$paypal_button_url = $_REQUEST['paypal_button_url'];
			$paypal_email_confirm = $_REQUEST['paypal_email_confirm'];
			$paypal_auth_token = $_REQUEST['paypal_auth_token'];
			$paypal_subscr_button_url = $_REQUEST['paypal_subscr_button_url'];
			$paypal_candidate_return_url = $_REQUEST['paypal_candidate_return_url'];
			$paypal_candidate_cancel_return_url = $_REQUEST['paypal_candidate_cancel_return_url'];
			$paypal_use_curl = $_REQUEST['paypal_use_curl'];
			$paypal_curl_proxy = $_REQUEST['paypal_curl_proxy'];
			$paypal_auto_rebill = $_REQUEST['paypal_auto_rebill'];
			$paypal_manual_approve = $_REQUEST['paypal_manual_approve'];
			

		} else {

			$paypal_email = PAYPAL_EMAIL;
			$paypal_server = PAYPAL_SERVER;
			$paypal_ipn_url = PAYPAL_IPN_URL;
			$paypal_return_url = PAYPAL_RETURN_URL;
			$paypal_cancel_return_url = PAYPAL_CANCEL_RETURN_URL;
			$paypal_page_style = PAYPAL_PAGE_STYLE;
			$paypal_currency = PAYPAL_CURRENCY;
			$paypal_button_url = PAYPAL_BUTTON_URL;
			$paypal_email_confirm = PAYPAL_EMAIL_CONFIRM;
			$paypal_auth_token = PAYPAL_AUTH_TOKEN;
			$paypal_subscr_button_url = PAYPAL_SUBSCR_BUTTON_URL;
			$paypal_candidate_return_url = PAYPAL_CANDIDATE_RETURN_URL;
			$paypal_candidate_cancel_return_url = PAYPAL_CANDIDATE_CANCEL_RETURN_URL;
			$paypal_use_curl = PAYPAL_USE_CURL;
			$paypal_curl_proxy = PAYPAL_CURL_PROXY;
			$paypal_auto_rebill  = PAYPAL_AUTO_REBILL;
			$paypal_manual_approve = PAYPAL_MANUAL_APPROVE;

		}

		$host = $_SERVER['SERVER_NAME']; // hostname
		  $http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
		  $http_url = explode ("/", $http_url);
		  array_pop($http_url); // get rid of filename
		  array_pop($http_url); // get rid of /admin
		  $http_url = implode ("/", $http_url);

		?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
		<table border="0" cellpadding="5" cellspacing="2" style="border-style:groove" width="100%" bgcolor="#FFFFFF">
    <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">PayPal 
      Email address</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_email" size="33" value="<?php echo $paypal_email; ?>">Note: Ensure that IPN is enabled for this PayPal account. Also, ensure that 'Auto Return' and 'Payment Data Transfer' are turned on, located on the Profile -> 'Website Payment Preferences' page in your PayPal account. Do NOT set 'Block Non-encrypted Website Payment' to On, leave it Off</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Paypal 
      Identity token</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_auth_token" size="50" value="<?php echo $paypal_auth_token; ?>"><br><font face="Verdana" size="1">Required for PDT (Payment Data Transfer). You can find the Identity token under Profile -> 'Website Payment Preferences' page in your PayPal account </font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">PayPal 
      Server host</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select name="paypal_server">
	  <option value="www.paypal.com" <?php if ($paypal_server == 'www.paypal.com' ) { echo " selected ";}  ?> >PayPal [www.paypal.com]</option>
	  <option value="www.sandbox.paypal.com" <?php if ($paypal_server == 'www.sandbox.paypal.com' ) { echo " selected ";}  ?>>PayPal Sand Box [www.sandbox.paypal.com]</option>
	  </select> Note: If you want to test the paypal IPN functions, you can set the host to PayPal's sand-box server. Set to www.paypal.com once your website goes live)
	  </font></td>
    </tr>
	 
	 <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Paypal 
      IPN URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_ipn_url" size="50" value="<?php echo $paypal_ipn_url; ?>"><br>Recommended: <b>http://<?php echo $host.$http_url."/payment/paypalIPN.php"; ?></font></td>
    </tr>
	 
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Paypal 
      Return URL (Employers)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_return_url" size="50" value="<?php echo $paypal_return_url; ?>"><br>(recommended: <b>http://<?php echo $host.$http_url."/".JB_EMPLOYER_FOLDER."thanks.php?m=".$this->className; ?></b> Note: This URL should also be entered as the 'Return URL' on the 'Website Payment Preferences in your PayPal account)</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Paypal 
      Cancelled Return URL (Employers)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_cancel_return_url" size="50" value="<?php echo $paypal_cancel_return_url; ?>"><br>(recommended: <b>http://<?php echo $host.$http_url."/".JB_EMPLOYER_FOLDER; ?></b> )</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Paypal 
      Return URL (Candidates)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_candidate_return_url" size="50" value="<?php echo $paypal_candidate_return_url; ?>"><br>(recommended: <b>http://<?php echo $host.$http_url."/".JB_CANDIDATE_FOLDER."thanks.php?m=".$this->className; ?></b> </font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Paypal 
      Cancelled Return URL (Candidates)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_candidate_cancel_return_url" size="50" value="<?php echo $paypal_candidate_cancel_return_url; ?>"><br>(recommended: <b>http://<?php echo $host.$http_url."/".JB_CANDIDATE_FOLDER; ?></b> )</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Paypal 
      Page Style</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_page_style" size="50" value="<?php echo $paypal_page_style; ?>"><br>(Your PayPal account's page style. Defined in your paypal account's options.)</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Paypal 
      Currency</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
	  <select name="paypal_currency">
		<option value="USD" <?php if ($paypal_currency=='USD') { echo " selected "; }  ?> >USD</option>
		<option value="AUD" <?php if ($paypal_currency=='AUD') { echo " selected "; }  ?> >AUD</option>
		<option value="EUR" <?php if ($paypal_currency=='EUR') { echo " selected "; }  ?> >EUR</option>
		<option value="CAD" <?php if ($paypal_currency=='CAD') { echo " selected "; }  ?> >CAD</option>
		<option value="JPY" <?php if ($paypal_currency=='JPY') { echo " selected "; }  ?> >JPY</option>
		<option value="GBP" <?php if ($paypal_currency=='GBP') { echo " selected "; }  ?> >GBP</option>
		<option value="CZK" <?php if ($paypal_currency=='CZK') { echo " selected "; }  ?> >CZK</option>
		<option value="HUF" <?php if ($paypal_currency=='HUF') { echo " selected "; }  ?> >HUF</option>
		<option value="NOK" <?php if ($paypal_currency=='NOK') { echo " selected "; }  ?> >NOK</option>
		<option value="PLN" <?php if ($paypal_currency=='PLN') { echo " selected "; }  ?> >PLN</option>
		<option value="DKK" <?php if ($paypal_currency=='DKK') { echo " selected "; }  ?> >DKK</option>
		<option value="SEK" <?php if ($paypal_currency=='SEK') { echo " selected "; }  ?> >SEK</option>
		<option value="CHF" <?php if ($paypal_currency=='CHF') { echo " selected "; }  ?> >CHF</option>
		<option value="SGD" <?php if ($paypal_currency=='SGD') { echo " selected "; }  ?> >SGD</option>
		<option value="HKD" <?php if ($paypal_currency=='HKD') { echo " selected "; }  ?> >HKD</option>
		<option value="NIS" <?php if ($paypal_currency=='NIS') { echo " selected "; }  ?> >NIS</option>
		<option value="MXN" <?php if ($paypal_currency=='MXN') { echo " selected "; }  ?> >MXN</option>
		<option value="NZD" <?php if ($paypal_currency=='NZD') { echo " selected "; }  ?> >NZD</option>
	  </select> (PayPal currently accepts 17 currencies, and the local currency amount, if not supported, will be converted during checkout)
     </td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Paypal 
      Button Image URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_button_url" size="50" value="<?php echo $paypal_button_url; ?>"><br></font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Paypal 
      Subscription Button Image URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_subscr_button_url" size="50" value="<?php echo $paypal_subscr_button_url; ?>"><br></font></td>
    </tr>

	<tr>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">Auto re-bill (Y/N)</font></td>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">
       
	  <input type="radio" name="paypal_auto_rebill" value="YES"  <?php if ($paypal_auto_rebill=='YES') { echo " checked "; } ?> >Yes - memberships and subscriptions will be automatically re-billed by PayPal<br>
	  <input type="radio" name="paypal_auto_rebill" value="NO"  <?php if ($paypal_auto_rebill=='NO') { echo " checked "; } ?> >No</font></td>
    </tr>

	<tr>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">Subscriptions: Manual Approval?</font></td>
      <td bgcolor="#e6f2ea">
      
	   <input type="radio" name="paypal_manual_approve" value="NO"  <?php if ($paypal_manual_approve!='YES') { echo " checked "; } ?> ><font face="Verdana" size="1">No - Access is granted to the Resume DB automatically</font>  <br>
	  <input type="radio" name="paypal_manual_approve" value="YES"  <?php if ($paypal_manual_approve=='YES') { echo " checked "; } ?> ><font face="Verdana" size="1">Yes - Administrator will need to review and complete each subscription payment by going to Admin-&gt;Subscription Orders. </font> 
	  </td>
    </tr>


	

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Use cURL (Y/N)</font></td>
      <td  bgcolor="#e6f2ea">
       <br>
	  <input type="radio" name="paypal_use_curl" value="NO"  <?php if ($paypal_use_curl=='NO') { echo " checked "; } ?> ><font face="Verdana" size="1">No - Normally this option is best</font><br>
	  <input type="radio" name="paypal_use_curl" value="YES"  <?php if ($paypal_use_curl=='YES') { echo " checked "; } ?> ><font face="Verdana" size="1">Yes - If your hosting company blocked fsockopen() and has cURL, then use this option</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">cURL 
      Proxy URL</font></td>
      <td  bgcolor="#e6f2ea">
      <input type="text" name="paypal_curl_proxy" size="50" value="<?php echo $paypal_curl_proxy; ?>"><font face="Verdana" size="1">Leave blank if your server does not need one. Contact your hosting company if you are not sure about which option to use. For GoDaddy it is: http://proxy.shr.secureserver.net:3128</font><br></td>
    </tr>
	
	 <tr>
      <td  bgcolor="#e6f2ea" colspan=2><font face="Verdana" size="1"><input type="submit" value="Save">
	  </td>
	  </tr>
  </table>
  <input type="hidden" name="pay" value="<?php echo jb_escape_html($_REQUEST['pay']);?>">
  <input type="hidden" name="action" value="save">
  </form>
  <p>
<font color="blue" size="2">Important Note for PayPal Subscription: If your user purchases a Subscription or monthly Membership, then at the end of the term <b>PayPal will re-bill them automatically</b> and the job board will re-new the subscription automatically. If your customer wants to cancel the automatic re-billing, then they should log in to their PayPal account and cancel it from there. They can do this at any time. Also, if your customer wants to upgrade or downgrade a subscription, they should log in to their PayPal account and cancel the subscription and then wait until it expires on the job board system. After the subscription expires, they can re-order a bigger or smaller plan. It is recommended that you specify these rules in your terms and conditions and/or the edit your order confirmation email templates with these rules, etc.</font>
  </p>
  <p ><font color="blue" size="2">
  Note 2: If your customer wants to upgrade / downgrade immediately, then ask them to cancel the subscription via their PayPal account and then go to Subscription Reports and click the question next to the 'Completed' status. There you will see a 'Refund' button where you can immediately terminate their subscription. Your customer would then be able to create a new order from their job board account.
  </font></p>
   <p ><font color="blue" size="2">
  Note 3: In the event of immediate refunds, then you must refund the transaction in your PayPal account and cancel the subscription there. The subscription status on the job board should change automatically.
  </font>
  </p>
  <p ><font color="blue" size="2">
  Note 4: In some cases, the order may fail to re-bill. PayPal will then attempt to re-bill a few times before giving up. If you want paypal to stop the re-attempts, log in to your PayPal account and cancel the subscription manually.
  </font>
  <p ><font color="blue" size="2">
  Note 5: In the job board, there are two products which are subject to the PayPal Subscriptions: <i>Subscription to the resume database</i> and <i>Memberships</i>. Although in the PayPal system both of these are considered as subscriptions, the job board uses two different terms to differentiate the products. Also, memberships plans with a one-off fee do not get sent as PayPal subscriptions, but the usual PayPal payments - so a customer is not rebilled for one-off Membership payments.
  </font>
  </p>

  <?php

		

	}

	function save_config() {

	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_EMAIL', '".jb_escape_sql(trim($_REQUEST['paypal_email']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_CURRENCY', '".jb_escape_sql(trim($_REQUEST['paypal_currency']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_BUTTON_URL', '".jb_escape_sql(trim($_REQUEST['paypal_button_url']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_IPN_URL', '".jb_escape_sql(trim($_REQUEST['paypal_ipn_url']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_RETURN_URL', '".jb_escape_sql(trim($_REQUEST['paypal_return_url']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_CANCEL_RETURN_URL', '".jb_escape_sql(trim($_REQUEST['paypal_cancel_return_url']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_PAGE_STYLE', '".jb_escape_sql(trim($_REQUEST['paypal_page_style']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_SERVER', '".jb_escape_sql(trim($_REQUEST['paypal_server']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_AUTH_TOKEN', '".jb_escape_sql(trim($_REQUEST['paypal_auth_token']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_SUBSCR_BUTTON_URL', '".jb_escape_sql(trim($_REQUEST['paypal_subscr_button_url']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_CANDIDATE_RETURN_URL', '".jb_escape_sql(trim($_REQUEST['paypal_candidate_return_url']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_CANDIDATE_CANCEL_RETURN_URL', '".jb_escape_sql(trim($_REQUEST['paypal_candidate_cancel_return_url']))."')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_USE_CURL', '".jb_escape_sql(trim($_REQUEST['paypal_use_curl']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_CURL_PROXY', '".jb_escape_sql(trim($_REQUEST['paypal_curl_proxy']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_AUTO_REBILL', '".jb_escape_sql(trim($_REQUEST['paypal_auto_rebill']))."')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_MANUAL_APPROVE', '".jb_escape_sql(trim($_REQUEST['paypal_manual_approve']))."')";
		JB_mysql_query($sql);


		

	
	}

	// true or false
	function is_enabled() {

		$sql = "SELECT val from jb_config where `key`='PAYPAL_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		if ($row['val']=='Y') {
			return true;

		} else {
			return false;

		}

	}

	// true or false
	function is_installed() {

		$sql = "SELECT val from jb_config where `key`='PAYPAL_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);
		

		if (mysql_num_rows($result)>0) {
			return true;

		} else {
			return false;

		}

	}

	function enable() {

		$sql = "UPDATE jb_config set val='Y' where `key`='PAYPAL_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);


	}

	function disable() {

		$sql = "UPDATE jb_config set val='N' where `key`='PAYPAL_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);

	}

	function is_auto_rebill() {
		return true;
	}

	// process_payment_return() - Called when thanks.php page is accessed after returning from payment gateway
	// - Place affiliate code here.
	// - You can access all the variables returned form the payment gatway through the $_POST array (If the payment gateway returned any variables)
	// - place any other functionality here.

	function process_payment_return() {

		global $label;

		

		if (PAYPAL_USE_CURL=='YES') {

			// cannot use cURL for PTD, use for IPN only
			//return false; 

		}
		////////////
		// Paypal
		###########################

		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-synch';

		$tx_token = $_GET['tx'];
		$auth_token = PAYPAL_AUTH_TOKEN;
		$req .= "&tx=$tx_token&at=$auth_token";
		$result = '';

		if (PAYPAL_USE_CURL=='YES') {

			// Use curl to post back to PayPAl
			// and put the result in a file.
			// open the file using $fp

			pp_log_entry('PDD - Using cURL to POST to PayPal');

			$URL = "https://".PAYPAL_SERVER."/cgi-bin/webscr";
			$ch = curl_init();

			if (PAYPAL_CURL_PROXY!='') {
				curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
				curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
				curl_setopt ($ch, CURLOPT_PROXY, PAYPAL_CURL_PROXY);
			}


			//$req = "username=test&password=test";

			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt ($ch, CURLOPT_URL, $URL);
			curl_setopt ($ch, CURLOPT_TIMEOUT, 20);
			curl_setopt ($ch, CURLOPT_POST, TRUE);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $req);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			pp_log_entry('PDT posting to PayPal and using cURL: '.$result);

			$result = curl_exec ($ch);
			pp_log_entry(curl_error($ch));

			curl_close ($ch);

			pp_log_entry('PDD -Got this from result from cURL: '.$result);

			$filename = JB_get_cache_dir().md5(time().PAYPAL_AUTH_TOKEN).'PDT.paypal';
			$fp = fopen($filename, 'w');
			fwrite($fp, $result, strlen($result));
			$fp = fclose($fp);

			// open for reading
			
			$fp = fopen($filename, 'r');



		} else {

			// post back to PayPal system to validate
			$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
			$fp = fsockopen (PAYPAL_SERVER, 80, $errno, $errstr, 30);
			// If possible, securely post back to paypal using HTTPS
			// Your PHP server will need to be SSL enabled
			// $fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);

		}
		
		if (!$fp) {
			// HTTP ERROR
			//echo  "fp error";
			pp_log_entry('PDD -Could not open socket / file');
		} else {
			if (PAYPAL_USE_CURL!='YES') {
				fputs ($fp, $header . $req); // post to paypal
			}
			// read the body data 
			$res = '';
			$headerdone = false;
			while (!feof($fp)) {
				$line = fgets ($fp, 1024);
				
				if (strcmp($line, "\r\n") == 0) {
					// read the header
					$headerdone = true;
				} else if ($headerdone) {
					// header has been read. now read the contents
					$res .= $line;
				}
			}
		}
		fclose ($fp);
		if (PAYPAL_USE_CURL=='YES') {
			unlink ($filename);
		}

		

		// parse the data
		$lines = explode("\n", $res);
		$keyarray = array();
		if ((strcmp ($lines[0], "SUCCESS") == 0) || (strpos($result,'SUCCESS')!==false )) {
			pp_log_entry('PDD - Notification verfified');
			for ($i=1; $i<count($lines);$i++){
				list($key,$val) = explode("=", $lines[$i]);
				$keyarray[urldecode($key)] = urldecode($val);
			}
			//print_r($_REQUEST);
			// check the payment_status is Completed
			// check that txn_id has not been previously processed
			// check that receiver_email is your Primary PayPal email
			// check that payment_amount/payment_currency are correct
			// process payment
			$firstname = $keyarray['first_name'];
			$lastname = $keyarray['last_name'];
			$itemname = $keyarray['item_name'];
			$amount = $keyarray['payment_gross'];
			$txn_id = $keyarray['txn_id'];
			$mc_gross = $keyarray['mc_gross'];
			$mc_currency = $keyarray['mc_currency'];
			$payer_email = $keyarray['payer_email'];
			$payment_status = $keyarray['payment_status'];

			$invoice_id = jb_strip_order_id($keyarray['invoice']);
			$product_type = substr($invoice_id, 0, 1);// 'P' or 'S'
			$invoice_id = substr($invoice_id, 1);

			JBPLUG_do_callback('pay_trn_verification_passed', $invoice_id, $product_type);

			if ($payment_status=='Completed') {

				if ($product_type == 'P') {
					?>
					<center>

					<img src="<?php echo JB_SITE_LOGO_URL; ?>">
					<?php
					$label['payment_posts_completed2'] = str_replace("%URL%", JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER.'manager.php', $label['payment_posts_completed2']);	
					?>
					<p><?php echo $label['payment_posts_completed2']; ?></p>
					</center>
					<?php

				} elseif ($product_type == 'S') { 

					?>
					<center>
					<img src="<?php echo JB_SITE_LOGO_URL; ?>">
					<?php
					$label['payment_subscription_completed2'] = str_replace("%URL%", JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER.'search.php', $label['payment_subscription_completed2']);	
					?>
					<p><?php echo $label['payment_subscription_completed2']; ?></p>
					</center>
					<?php


				} elseif ($product_type == 'M') { // membership completed

					?>
					<center>
					<img src="<?php echo JB_SITE_LOGO_URL; ?>">
					<?php
					//$label['payment_membership_completed'] = str_replace("%URL%", JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER, $label['payment_membership_completed']);	
					?>
					<p><?php echo $label['payment_membership_completed']; ?></p>
					</center>
					<?php


				}

				$sql = "SELECT * FROM jb_txn WHERE txn_id='".jb_escape_sql($txn_id)."' and origin='PayPal' ";
				
				$result = JB_mysql_query($sql) or die (mysql_error()); 
				if (mysql_num_rows($result)> 0) { 
					// already in the database, possibly entered in by the IPN System
					
				
				} else {

					if ($product_type == 'P') {
						
						JB_complete_package_invoice($invoice_id, 'PayPal');	
						
					} elseif ($product_type == 'S') {

						if (PAYPAL_MANUAL_APPROVE=='Y') {
							pp_subscr_manual_approve($invoice_id);
						} else {
							JB_complete_subscription_invoice($invoice_id, 'PayPal');
						}
						
						
					} elseif ($product_type == 'M') {
						JB_complete_membership_invoice($invoice_id, 'PayPal');
						
					}
					
					JB_debit_transaction($invoice_id, $mc_gross, $mc_currency, $txn_id, $reason_code, 'PayPal', $product_type, $_REQUEST['subscr_id']);

				}
				
				JBPLUG_do_callback('pay_trn_completed', $invoice_id, $product_type);

			} elseif ($payment_status=='Pending') {
				if ($product_type=='P') {
						JB_pend_package_invoice ($invoice_id, 'PayPal', $reason_code);
						
					} elseif ($product_type=='S') {
						JB_pend_subscription_invoice ($invoice_id, 'PayPal', $reason_code);
						
					} elseif ($product_type=='M') {
						JB_pend_membership_invoice ($invoice_id, 'PayPal', $reason_code);
						
					}
					JBPLUG_do_callback('pay_trn_pending', $invoice_id, $product_type);
					$label['payment_return_pending'] = str_replace("%PAYMENT_GW%", $this->name, $label['payment_return_pending']);	

					?>
					<center>
					<img src="<?php echo JB_SITE_LOGO_URL; ?>">
					<p><?php echo $label['payment_return_pending']; ?></p>
					</center>
					<?php

			} else {

				echo "Payment status: $payment_status";


			}
		}
		else if (strcmp ($lines[0], "FAIL") == 0) {
			// log for manual investigation

			pp_log_entry('PDD - verification failed');

			JBPLUG_do_callback('pay_trn_verification_failed', $invoice_id, $product_type);

			echo $label['paypal_ipn_fail'];
			?>

			

			<?php
		}

		?>

		
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>

		<?php


	}

}



?>