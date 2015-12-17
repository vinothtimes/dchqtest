<?php
# Copyright 2005-2010 Jamit Software
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

$_PAYMENT_OBJECTS['GoogleCheckout'] = new GoogleCheckout;//"GoogleCheckout";



define ('GOOGLE_CHECKOUT_LOGGING', 'Y');

function JB_gc_mail_error($msg) {

	$date = date("D, j M Y H:i:s O"); 
	
	$headers = "From: ". JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Reply-To: ".JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Return-Path: ".JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "X-Mailer: PHP" ."\r\n";
	$headers .= "Date: $date" ."\r\n"; 
	$headers .= "X-Sender-IP: $REMOTE_ADDR" ."\r\n";

	jb_googc_log_entry ($msg);

	@mail(JB_SITE_CONTACT_EMAIL, "Error message from ".JB_SITE_NAME." Jamit GoogleCheckout IPN script. ", $msg, $headers);

}

function jb_googc_log_entry ($entry_line) {

	if (GOOGLE_CHECKOUT_LOGGING == 'Y') {
		JB_payment_log_entry_db ($entry_line, 'GoogleCheckout');	
	}
}





function JB_GoogleCheckoutAPIcall($function_name, &$args) {

	global $_PAYMENT_OBJECTS;

	global $label;

	
	if(strtolower(GOOGLE_CHECKOUT_CART_MODE) == "sandbox") {
        $server_url = "https://sandbox.google.com/checkout/";
      } else {
        $server_url = "https://checkout.google.com/";
      }  

      //$this->schema_url = "http://checkout.google.com/schema/2";
      $base_url = $server_url . "api/checkout/v2/"; 
      $request_url = $base_url . "request/Merchant/" . GOOGLE_CHECKOUT_MERCHANT_ID;
      $report_url = $base_url . "reports/Merchant/" . GOOGLE_CHECKOUT_MERCHANT_ID;
      $merchant_checkout = $base_url . "merchantCheckout/Merchant/" . GOOGLE_CHECKOUT_MERCHANT_ID;


	switch ($function_name) {

		case 'hello':
			// this is a way to ping the Google server.
			
			$url = $request_url;
			$req .= '<hello xmlns="http://checkout.google.com/schema/2"/>';
			// The response should look like:
			// <bye xmlns="http://checkout.google.com/schema/2" serial-number="c567262a-dd13-4084-b8d3-6ccfbbc69d03" />
			
			break;
		case 'checkout-shopping-cart':
			// Post a new order to google. This will result with a link to the payment URL
			// Args: item_name, item_desc, currency, unit_price, item_id, invoice_id

			
			//  OPTIMISTIC
			if ($args['user_type']=='E') {
				$continue_shopping_url = JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER.'thanks.php?m='.$_PAYMENT_OBJECTS['GoogleCheckout']->className.'&order_id='.$args['product_type'].$args['merchant-item-id'];
				$edit_cart_url = JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER;

				// employers have 4 product types
				switch ($args['product_type']) {
					case 'P':
						$edit_cart_url .= 'credits.php';
						break;
					case 'M':
						$edit_cart_url .= 'membership.php';
						break;
					case 'S':
						$edit_cart_url .= 'subscriptions.php';
						break;
					default:
						$edit_cart_url .= 'credits.php';
						break;
				}
				
			} else {
				// Candidates only have 'memberships product' type
				$continue_shopping_url = JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER.'thanks.php?m='.$_PAYMENT_OBJECTS['GoogleCheckout']->className.'&order_id='.$args['product_type'].$args['merchant-item-id'];
				$edit_cart_url = JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER;
				$edit_cart_url .= 'membership.php';
			}

			$args['digital-content-description'] = str_replace('%RETURN_URL%', $continue_shopping_url, $args['digital-content-description']);
			
			$url = $merchant_checkout; //.'/diagnose';
			$req .= '<?xml version="1.0" encoding="UTF-8"?>
			
			<checkout-shopping-cart xmlns="http://checkout.google.com/schema/2">  
			<shopping-cart>
				<items>
					<item>
						<item-name>'.htmlspecialchars($args['item-name']).'</item-name>
						<item-description>'.htmlspecialchars($args['item-description']).'</item-description>
						<unit-price currency="'.htmlspecialchars(GOOGLE_CHECKOUT_CURRENCY).'">'.htmlspecialchars($args['unit-price']).'</unit-price>

						<merchant-item-id>'.htmlspecialchars($args['merchant-item-id']).'</merchant-item-id>
						<merchant-private-item-data>
							<user_type>'.$args['user_type'].'</user_type>
							<user_id>'.$args['user_id'].'</user_id>
							<product_type>'.$args['product_type'].'</product_type>
						</merchant-private-item-data> 
						<digital-content>
							<display-disposition>PESSIMISTIC</display-disposition>
							<description>'.htmlspecialchars($args['digital-content-description']).'</description>
							<email-delivery>false</email-delivery>
						</digital-content>
						
						<quantity>1</quantity>
						
					</item>
				</items>
			</shopping-cart>
			<checkout-flow-support>
				<merchant-checkout-flow-support>
					<continue-shopping-url>'.htmlspecialchars($continue_shopping_url).'</continue-shopping-url>
					<edit-cart-url>'.htmlspecialchars($edit_cart_url).'</edit-cart-url>
				</merchant-checkout-flow-support>
			</checkout-flow-support>
			</checkout-shopping-cart>';

			/*

			A typical response is:

			<checkout-redirect xmlns="http://checkout.google.com/schema/2" serial-number="6e6ed5d3-fdb9-4f49-8d9f-9e8b190ece55">
				<redirect-url>https://sandbox.google.com/checkout/view/buy?o=shoppingcart&amp;shoppingcart=110689039015614</redirect-url>
			</checkout-redirect>

			Send the user to redirect-url


			*/

			break;

		case 'notification-history-request':

			$url = $report_url;

			/*

			Notification types:

			authorization-amount
			charge-amount
			chargeback-amount
			new-order
			order-state-change
			refund-amount
			risk-information

			*/

			$req = '<?xml version="1.0" encoding="UTF-8"?>
					<notification-history-request xmlns="http://checkout.google.com/schema/2">
					<serial-number>'.$args['serial-number'].'</serial-number>
					</notification-history-request>';

			if (isset($args['google-order-number'])) {
				$req = '<?xml version="1.0" encoding="UTF-8"?>
					<notification-history-request xmlns="http://checkout.google.com/schema/2">
						<order-numbers>
							<google-order-number>'.$args['google-order-number'].'</google-order-number>
						</order-numbers>
						<notification-types>
							<notification-type>'.$args['notification-type'].'</notification-type>
						</notification-types>
					</notification-history-request>';
			}
			
			break;

		case 'charge-and-ship-order':
			$url = $request_url;

			$req = '<?xml version="1.0" encoding="UTF-8"?>
					<charge-and-ship-order xmlns="http://checkout.google.com/schema/2" google-order-number="'.$args['google-order-number'].'">
					</charge-and-ship-order>';


	}

	$headers = array();
	$headers[] = "Authorization: Basic ".base64_encode(
	GOOGLE_CHECKOUT_MERCHANT_ID.':'.GOOGLE_CHECKOUT_MERCHANT_KEY);
	$headers[] = "Content-Type: application/xml; charset=UTF-8";
	$headers[] = "Accept: application/xml; charset=UTF-8";
	$headers[] = "User-Agent: Jamit Job Board";
	
	$session = curl_init($url);

	curl_setopt($session, CURLOPT_POST, true);
	curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($session, CURLOPT_POSTFIELDS, $req);
	curl_setopt($session, CURLOPT_HEADER, true);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($session, CURLOPT_COOKIESESSION, TRUE);
	
	$hash = substr(md5(JB_ADMIN_PASSWORD.GOOGLE_CHECKOUT_MERCHANT_KEY), 0, 10);

	curl_setopt($session, CURLOPT_COOKIEFILE, JB_basedirpath().'cache/'.$hash.'cookiefile.txt');
	curl_setopt($session, CURLOPT_COOKIEJAR, JB_basedirpath().'cache/'.$hash.'jarfile.txt');

	//if(!empty($this->certPath) && file_exists($this->certPath)) {
	//	curl_setopt($session, CURLOPT_SSL_VERIFYPEER, true);
	//	curl_setopt($session, CURLOPT_CAINFO, $this->certPath);
	//}
	//else {
	//	curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
	//}
	curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

	//if(is_array($this->proxy) && count($this->proxy)) {
	//	curl_setopt($session, CURLOPT_PROXY, 
	//				$this->proxy['host'] . ":" . $this->proxy['port']);
	//}
	if($timeout != false){
		curl_setopt($session, CURLOPT_TIMEOUT, $timeout);

	}
	// Do the POST and then close the session
	$response = curl_exec($session);
	curl_close ($session);
	$pos = strpos($response, "\r\n\r\n");
	$response = substr($response, $pos);

//jb_googc_log_entry("Request:".$req);
//jb_googc_log_entry("Response:".$response);

	$xml = new SimpleXMLElement(trim($response));
	return $xml;

}


if (isset($_GET['notify'])) { 

	/*

	Here we process notifications.
	Notofications are posted from Google to let us know about the status
	of transactions.

	The notification URL would be to this file itself:
	http://www.example.com/payment/googleCheckout.php?notify

	http://27.32.6.139/working/trunk/payment/googleCheckout.php?notify

	Google Checkout Notification API Docs
	http://code.google.com/apis/checkout/developer/Google_Checkout_XML_API_Notification_API.html

	Typical order flow:

	1. new-order-notification, NEW REVIEWING
	2. order-state-change-notification, NEW CHARGEABLE
	3. risk-information-notification
	4. authorization-amount-notification, NEW CHARGEABLE
	5. <order-state-change-notification, PROCESSING, CHARGEABLE
	6. order-state-change-notification, PROCESSING, CHARGING
	7. order-state-change-notification, PROCESSING, CHARGED
	8. charge-amount-notification, 
	9. order-state-change-notification, DELIVERED, CHARGED


	*/

	//$vars = var_export($_REQUEST['serial-number'], true);
	jb_googc_log_entry("Google to Jamit: There's a notification with the following serial-number:[".$_REQUEST['serial-number']."]");

	$args = array(
			'serial-number' => $_REQUEST['serial-number']	
		);
	$xml = JB_GoogleCheckoutAPIcall('notification-history-request', $args); 

	//$vars = var_export($xml, true);
	//jb_googc_log_entry("got this from google[notification-history-request]:".$vars);

	$notification = $xml->getName();
	$attr = $xml->attributes();
	$serial = (string) $attr['serial-number'];

	$fulfillment_state = $xml->{'order-summary'}->{'fulfillment-order-state'};
	$financial_state = $xml->{'order-summary'}->{'financial-order-state'}; // REVIEWING , CHARGEABLE , CHARGING , CHARGED, PAYMENT_DECLINED , CANCELLED,  CANCELLED_BY_GOOGLE
	$amount = $xml->{'order-summary'}->{'order-total'};
	$attr = ($xml->{'order-summary'}->{'order-total'}->attributes());
	$currency = (string) $attr['currency'];
	$invoice_id = $xml->{'order-summary'}->{'shopping-cart'}->items->item->{'merchant-item-id'};
	$product_type = $xml->{'order-summary'}->{'shopping-cart'}->items->item->{'merchant-private-item-data'}->{'product_type'};
	$txn_id = (string) $xml->{'google-order-number'};

	if ($notification == 'new-order-notification') {

		// New order came!
		// Google says,
		// "You need to wait for an authorization-amount-notification before 
		// you can take the next step to process the order."
		// So here we pend the order

		$txn_id = (string) $xml->{'google-order-number'};
		
		if ($product_type=='P') { // posting plans
			// pend the order in the database. This is so that the user cannot
			// check it out or cancel
			jb_pend_package_invoice($invoice_id, 'GoogleCheckout', $financial_state);	
		} elseif ($product_type=='S') { // subscriptions
			jb_pend_subscription_invoice($invoice_id, 'GoogleCheckout', $financial_state);
		} elseif ($product_type=='M') { // memberships
			jb_pend_membership_invoice($invoice_id, 'GoogleCheckout', $financial_state);
		}

		jb_googc_log_entry("(".$product_type.$invoice_id.") - Jamit sent 'notification-history-request', Google replied with a '$notification' notification and gave the following: serial-number=$serial, google-order-number=$txn_id, fulfillment_state=$fulfillment_state, financial_state=$financial_state, order-total=$amount, currency=$currency, merchant-item-id=$invoice_id, product_type=$product_type. Jamit set the order to Pending. ");
		

		
	} elseif ($notification == 'authorization-amount-notification') {

		/*

		An <authorization-amount-notification> contains information on the credit card authorized amount and the result of the AVS and CVV checks. The <authorization-amount-notification> is sent after Google Checkout attempts to authorize a buyer's credit card for a new order.

		http://code.google.com/apis/checkout/developer/Google_Checkout_XML_API_Notification_API.html#authorization_amount_notification

		*/

		if (($financial_state == 'CHARGED') || ($financial_state == 'CHARGEABLE') || ($financial_state =='CHARING') ) {
			// here we complete the orders
			if ($product_type=='P') {
				jb_complete_package_invoice($invoice_id, 'GoogleCheckout');
			} elseif ($product_type=='S') {
				jb_complete_subscription_invoice($invoice_id, 'GoogleCheckout');
			} elseif ($product_type=='M') {
				jb_complete_membership_invoice($invoice_id, 'GoogleCheckout');
			}

			JB_debit_transaction($invoice_id, $amount, $currency, $txn_id, $fulfillment_state, 'GoogleCheckout', $product_type, $user_id);
		}


		jb_googc_log_entry("(".$product_type.$invoice_id.") - Jamit sent 'notification-history-request', Google replied with a '$notification' notification and gave the following: serial-number=$serial, google-order-number=$txn_id, fulfillment_state=$fulfillment_state, financial_state=$financial_state, order-total=$amount, currency=$currency, merchant-item-id=$invoice_id, product_type=$product_type.  ");

		if ($financial_state == 'CHARGEABLE') {

			// check the notification history to see if our order isn't charging?

			// give it 1 sec delay, just in case order status changed

			sleep(1);

			// now check the order status history to see if things have changed

			$args = array (
				'google-order-number' => $txn_id,
				'notification-type' => 'order-state-change'
			);
			
			$xml2 = JB_GoogleCheckoutAPIcall('notification-history-request', $args); 

			foreach ($xml2->notifications->{'order-state-change-notification'} as $n) {
				$states[] = $n->{'new-financial-order-state'}; // CHARGEABLE, CHARGING, CHARGED
			}

			if (!in_array('CHARGED', $states) && !in_array('CHARGING', $states)) {

				// not being charged or charging?
				// credit card authorization succeeds, checkout sends an authorization-amount-notification and the order moves to the CHARGEABLE financial order state.

				$args = array ('google-order-number' => $txn_id);
				$xml2 = JB_GoogleCheckoutAPIcall('charge-and-ship-order', $args); 
				$state = $xml2->{'charge-amount-notification'}->{'order-summary'}->{'financial-order-state'};

				//$vars = var_export($xml, true);
				//jb_googc_log_entry("got this from google [charge-and-ship-order]:".$vars);
				jb_googc_log_entry("(".$product_type.$invoice_id.") - Jamit sent 'charge-and-ship-order', Google replied: '".$xml2->getName()."' setting the financial-order-state to ".$state.var_export($xml2, true));

			} else {
				jb_googc_log_entry("(".$product_type.$invoice_id.") - It seems like Google charged this order automatically");
			}
	
		}

	} elseif ($notification == 'order-state-change-notification') {

		 // NOTE: These can be triggered by Order Processing API commands
		 // and Google will respond both synchronously as well as sending a notification

		 jb_googc_log_entry("(".$product_type.$invoice_id.") - Jamit sent 'notification-history-request', Google replied with a '$notification' notification ");


	} elseif ($notification == 'charge-amount-notification') {

		jb_googc_log_entry("(".$product_type.$invoice_id.") - Jamit sent 'notification-history-request', Google replied with a '$notification' notification ");

	} elseif ($notification == 'chargeback-amount-notification') {

		$reason_code = 'chargeback';

		if ($product_type=='P') {
			JB_reverse_package_invoice($invoice_id, $reason_code);

		} elseif ($product_type=='S') {
			JB_reverse_subscription_invoice($invoice_id, $reason_code);
		} elseif ($product_type=='M') {
			JB_reverse_membership_invoice($invoice_id, $reason_code);
		}
		
		JB_credit_transaction($invoice_id, $amount, $currency, $txn_id, $reason_code, 'GoogleCheckout', $product_type);


		jb_googc_log_entry("(".$product_type.$invoice_id.") - Jamit sent 'notification-history-request', Google replied with a '$notification' notification ");

	} elseif ($notification == 'refund-amount-notification') {

		$reason_code = 'refund';

		if ($product_type=='P') {
			JB_reverse_package_invoice($invoice_id, $reason_code);
		} elseif ($product_type=='S') {
			JB_reverse_subscription_invoice($invoice_id, $reason_code);
		} elseif ($product_type=='M') {
			JB_reverse_membership_invoice($invoice_id, $reason_code);
		}
		
		JB_credit_transaction($invoice_id, $amount, $currency, $txn_id, $reason_code, 'GoogleCheckout', $product_type);

		jb_googc_log_entry("(".$product_type.$invoice_id.") - Jamit sent 'notification-history-request', Google replied with a '$notification' notification ");

	} elseif ($notification == 'risk-information-notification') {

		jb_googc_log_entry("(".$product_type.$invoice_id.") - Jamit sent 'notification-history-request', Google replied with a '$notification' ");

	} else {

		 jb_googc_log_entry("(".$product_type.$invoice_id.") - Jamit sent 'notification-history-request', Google replied with a '$notification' notification which is unknown to Jamit. serial-number=$serial, google-order-number=$txn_id, fulfillment_state=$fulfillment_state, financial_state=$financial_state, order-total=$amount, currency=$currency, merchant-item-id=$invoice_id, product_type=$product_type.");

	}




	// respond with a 'notification-acknowledgement'
	$args = array ('serial-number' => $serial);
	jb_GoogleCheckout_respond('notification-acknowledgement', $args);



}

function jb_GoogleCheckout_respond($response_type, $args) {

	switch ($response_type) {

		case 'notification-acknowledgement':
			$str = '<?xml version="1.0" encoding="UTF-8"?>
		<notification-acknowledgment xmlns="http://checkout.google.com/schema/2" 
    serial-number="'.$args['serial-number'].'" />';
			
			jb_googc_log_entry("Acknowledged the notification regarding '".$args['serial-number']."' by sending 'notification-acknowledgment' to Google");

			break;
		case '':
			break;

	}

	echo $str;

	


}


###########################################################################
# Payment Object



class GoogleCheckout {

	var $name;
	var $description;
	var $className="GoogleCheckout";
	

	function GoogleCheckout() {

		global $label;


		if (!isset($label['payment_google_name'])) {
			$label['payment_google_name'] = "Google Checkout&trade; ";
			$label['payment_google_descr'] =  "Secure Credit Card Payment with Google Checkout&trade;";
		}

		$this->name=$label['payment_google_name'];
		$this->description=$label['payment_google_descr'];

		if ($this->is_installed()) {

			$sql = "SELECT * FROM jb_config where `key`='GOOGLE_CHECKOUT_ENABLED' OR `key`='GOOGLE_CHECKOUT_MERCHANT_ID' OR `key`='GOOGLE_CHECKOUT_MERCHANT_KEY' OR `key`='GOOGLE_CHECKOUT_CART_MODE' OR `key`='GOOGLE_CHECKOUT_CURRENCY'  ";
			$result = JB_mysql_query($sql) or die (mysql_error().$sql);

			while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {
				define ($row['key'], $row['val']);
			}

		}

	}

	function get_currency() {

		return GOOGLE_CHECKOUT_CURRENCY;

	}


	function install() {


		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('GOOGLE_CHECKOUT_MERCHANT_ID', '455364569475924')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('GOOGLE_CHECKOUT_MERCHANT_KEY', 'LLT6vgsZ5TatrboxoKSZOw')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('GOOGLE_CHECKOUT_CART_MODE', 'sandbox')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('GOOGLE_CHECKOUT_CURRENCY', 'USD')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('GOOGLE_CHECKOUT_ENABLED', 'USD')";
		JB_mysql_query($sql);

		
		
		
	}

	function uninstall() {

		
		$sql = "DELETE FROM jb_config where `key`='GOOGLE_CHECKOUT_MERCHANT_ID'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='GOOGLE_CHECKOUT_MERCHANT_KEY'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='GOOGLE_CHECKOUT_CART_MODE'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='GOOGLE_CHECKOUT_CURRENCY'";
		JB_mysql_query($sql);
		


	}

	

	function payment_button($order_id, $product_type) {

		if ($product_type == '') {
			$product_type = 'P'; // posting package
		}

		global $label;

		if ($product_type=='P') {
			$order_row = JB_get_product_invoice_row ($order_id);
			$item_id = $order_row['package_id'];
		} elseif ($product_type=='S') {
			$order_row = JB_get_subscription_invoice_row($order_id);
			$item_id = $order_row['subscription_id'];
		} elseif ($product_type=='M') {
			$order_row = JB_get_membership_invoice_row($order_id);
			$item_id = $order_row['membership_id'];
		}

		//print_r($order_row);

		if ($order_row['status']!='Confirmed') {

			// Only Confirmed orders can be sent to checkout.

			//echo '<a href=""></a>';
			return;


		}

		if (isset($order_row['employer_id'])) {
			$user_type = 'E';
			$user_id = $order_row['employer_id'];
		} else {
			$user_type = 'C';
			$user_id = $order_row['user_id'];
		}

		$google_msg = $label['payment_google_msg'];
		$google_msg = str_replace('%CONTACT_EMAIL%', JB_SITE_CONTACT_EMAIL, $google_msg);
		

		$args = array(
			'item-name' => $order_row['item_name'],
			'item-description' => JB_SITE_NAME.' - '.$order_row['item_name'].' ('.$product_type.$order_row['invoice_id'].')',
			'digital-content-description' => $google_msg,
			'user_type' => $user_type,
			'user_id' => $user_id,
			'product_type' => $product_type,
			'currency' => GOOGLE_CHECKOUT_CURRENCY,
			'unit-price' => JB_convert_to_currency($order_row['amount'], $order_row['currency_code'], GOOGLE_CHECKOUT_CURRENCY),
			'item_id' => $item_id,
			'product_type' => $product_type,
			'merchant-item-id' => $order_row['invoice_id']

		);
		

		//  http://code.google.com/apis/checkout/developer/Google_Checkout_XML_API_Tag_Reference.html#tag_checkout-shopping-cart
		$res = JB_GoogleCheckoutAPIcall('checkout-shopping-cart', $args);
		//print_r($res);

		jb_googc_log_entry('('.$product_type.$order_row['invoice_id'].') - Jamit sent \'checkout-shopping-cart\', Google replied with the following checkout URL:'.$res->{'redirect-url'});

		?>

		<p style="text-align: center;">

		<a href="<?php echo $res->{'redirect-url'}; ?>">
		<img  src="https://checkout.google.com/buttons/checkout.gif?merchant_id=455364569475924&w=180&h=46&style=white&variant=text&loc=en_US" border="0">
		</a>
		</p>

		<!-- automatically redirect to the cart -->
		<script type="text/javascript">
			function js_redirect_google() {
				window.location='<?php echo  $res->{'redirect-url'}; ?>'
			  }
			  window.onload = js_redirect_google;
		</script>

		<?php

	}

	function config_form() {

		if ($_REQUEST['action']=='save') {
			$google_checkout_merchant_id = $_REQUEST['google_checkout_merchant_id'];
			$google_checkout_merchant_key = $_REQUEST['google_checkout_merchant_key'];
			$google_checkout_cart_mode = $_REQUEST['google_checkout_cart_mode'];
			$google_checkout_currency = $_REQUEST['google_checkout_currency'];

		} else {

			$google_checkout_merchant_id = GOOGLE_CHECKOUT_MERCHANT_ID;
			$google_checkout_merchant_key = GOOGLE_CHECKOUT_MERCHANT_KEY;
			$google_checkout_cart_mode = GOOGLE_CHECKOUT_CART_MODE;
			$google_checkout_currency = GOOGLE_CHECKOUT_CURRENCY;

		}

		?>
		<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
			<table border="0" cellpadding="5" cellspacing="2" style="border-style:groove" width="100%" bgcolor="#FFFFFF">
				<tr>
					<td class="config_form_label" colspan="2">Google Checkout</td>
				</tr>
				<tr>
					<td  class="config_form_label">
						Google Merchant ID
					</td>
					<td class="config_form_field" >
						<input type="text" name="google_checkout_merchant_id" size="33" value="<?php echo htmlentities($google_checkout_merchant_id); ?>">

					</td>
				</tr>
				<tr>
					<td  class="config_form_label">
						Google Merchant Key
					</td>
					<td  class="config_form_field">
						<input type="password" name="google_checkout_merchant_key" size="33" value="<?php echo htmlentities($google_checkout_merchant_key); ?>">Your merchant key is shown on the 'Integration' page in your Google Checkout account.
					</td>
				</tr>
				
				<tr>
					<td class="config_form_label">Cart Mode</td>
					<td class="config_form_field">
					 <select name="google_checkout_cart_mode">
	  <option value="live" <?php if ($google_checkout_cart_mode == 'live' ) { echo " selected ";}  ?> >Live [checkout.google.com]</option>
	  <option value="sandbox" <?php if ($google_checkout_cart_mode == 'sandbox' ) { echo " selected ";}  ?>>Sandbox [sandbox.google.com/checkout]</option>
	  </select> (Please use the sandbox mode for testing. Note: You will also need to update your merchant id/key after changing this setting)
					</td>
				<tr>
					<td class="config_form_label">Checkout  
					Currency</td>
					<td class="config_form_field">
					<select name="google_checkout_currency">
					<option value="USD" <?php if ($google_checkout_currency=='USD') { echo " selected "; }  ?> >USD</option>
					<option value="GBP" <?php if ($google_checkout_currency=='GBP') { echo " selected "; }  ?> >GBP</option>
					</select> (The currency used in the cart must match the currency of the seller account. If the seller account does not support your job board's local currency, then the job board will convert it)
					</td>
				</tr>
				<tr>
					<td class="config_form_label">API Callback URL</td>
					<td class="config_form_field"><b><?php echo JB_BASE_HTTP_PATH; //27.32.6.139/working/trunk ?>payment/googleCheckout.php?notify</b> - Google will send notifications to this URL. Please ensure that this URL can be accessed by Google</td>
				</tr>
				<tr>
					<td class="config_form_label" colspan="2"><input type="submit" value="Save"></td>
				</tr>
				<tr>
					<td  class="config_form_label">
						Test ID/Key
					</td>
					<td  class="config_form_field">(You may wish to test your settings after saving your settings)
						<input type="submit" value="Test" name="test">
						<?php

						if (isset($_REQUEST['test'])) {
							$args = array();
							$xml = JB_GoogleCheckoutAPIcall('hello', $args); 
							//print_r($xml);
							if (is_object($xml)) {

								if ($xml->getName()=='bye') {
									echo '<b>Test Passed! I was able to send a message to Google successfully!</b>';
								} else {

									echo '<b>Test Failed :( Please validate your ID and Key. Also sandbox / live server setting.</b>';
								}

							}
						}

						?>
					</td>
				</tr>
			</table>
			 <input type="hidden" name="pay" value="<?php echo jb_escape_html($_REQUEST['pay']);?>">
			<input type="hidden" name="action" value="save">
		</form>
		<p>
<?php

if (!function_exists('curl_exec')) {

	echo '<p style="color:red;">It looks like your PHP installation does not support cURL. Please contact your hosting administrator to enable the cURL extension, see http://www.php.net/manual/en/curl.installation.php for more details. This module cannot function without cURL.</p>';


} else {

	$v = curl_version();
	if (!isset($v['ssl_version']) || (strpos($v['ssl_version'], 'OpenSSL')===false)) {
		echo '<p style="color:red;">It looks like the cURL extension installed on this system does not support OpenSSL. Please contact your hosting administrator to enable the cURL extension with OpenSSL, or search the wen for more details. cURL with OpenSSL is needed for this payment module to function</p>';
	}

	
}

if (!function_exists('simplexml_load_file')) {

	echo '<p style="color:red;">It looks like your PHP installation does not support the simplexml_load_file() function. Please contact your hosting administrator to enable the SimpleSML extension, see http://www.php.net/manual/en/simplexml.installation.php for more details. SimpleXML is enabled in PHP 5 by default.</p>';

}

?>
		<br>
<h3>Quick Setup Instructions:</h3>
1. Please login to your Google Checkout Seller account (<a href="http://checkout.google.com/sell/" target="_blank">live</a> or <a href="https://sandbox.google.com/checkout/sell/" target="_blank" >sandbox</a>). Then navigate to the Settings-&gt;'Integration' page. Please enter the following settings:<br>
<ul>
<li><b>'My company will only post digitally signed carts'</b> should be checked</li>
<li>'API callback URL', set to:<br><b><?php echo JB_BASE_HTTP_PATH; //27.32.6.139/working/trunk ?>payment/googleCheckout.php?notify</b></li>
<li>Callback contents: Select <b>'Notification Serial Number'</b><br>
<li>API Version: Select <b>version 2.5</b></li>
<li>Un-check 'I am integrating using the Order Processing Tutorial documentation'. It is very important that this setting is <b>NOT checked</b>.</li>
<li>Click 'Save'.</li>
</ul>

</p>
<p>2. Navigate to the 'Preferences' page.</p>
<ul>
<li>Ensure that the following option is selected: '<b>Automatically authorize the buyer's credit card for the full amount of the order.</b>
</li>
<li>Click Save Preferences</li>
</ul>
<p>
Once these are in, you may click the 'Enable' button on your left - the traffic light will go green, indicating that Google Checkout is enabled for your site.
		</p>
<h3>Troubleshooting</h3>
<p>Not sure if the notifications are getting through? Please see the <a href="paypal_log.php?module=GoogleCheckout">payment log</a>. </p>
	<?php


	}



	function save_config() {


	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('GOOGLE_CHECKOUT_MERCHANT_ID', '".jb_escape_sql(trim($_REQUEST['google_checkout_merchant_id']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('GOOGLE_CHECKOUT_MERCHANT_KEY', '".jb_escape_sql(trim($_REQUEST['google_checkout_merchant_key']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('GOOGLE_CHECKOUT_CART_MODE', '".jb_escape_sql(trim($_REQUEST['google_checkout_cart_mode']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('GOOGLE_CHECKOUT_CURRENCY', '".jb_escape_sql(trim($_REQUEST['google_checkout_currency']))."')";
		JB_mysql_query($sql);
		

	
	}

	// true or false
	function is_enabled() {

		$sql = "SELECT val from jb_config where `key`='GOOGLE_CHECKOUT_ENABLED' ";
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

		$sql = "SELECT val from jb_config where `key`='GOOGLE_CHECKOUT_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);
		

		if (mysql_num_rows($result)>0) {
			return true;

		} else {
			return false;

		}

	}

	function enable() {

		$sql = "UPDATE jb_config set val='Y' where `key`='GOOGLE_CHECKOUT_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);


	}

	function disable() {

		$sql = "UPDATE jb_config set val='N' where `key`='GOOGLE_CHECKOUT_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);

	}

	function is_auto_rebill() {
		return false;
	}

	// process_payment_return() - Called when thanks.php page is accessed after returning from payment gateway
	// - Place affiliate code here.
	// - You can access all the variables returned form the payment gatway through the $_POST array (If the payment gateway returned any variables)
	// - place any other functionality here.

	function process_payment_return() {

		global $label;

		$product_type = substr($_REQUEST['order_id'], 0, 1);
		$invoice_id = (int) substr($_REQUEST['order_id'], 1);

		if ($product_type=='P') {
			$sql = "SELECT * FROM `package_invoices` WHERE employer_id='".jb_escape_sql($_SESSION['JB_ID'])."' AND  `invoice_id`='".jb_escape_sql($invoice_id)."'  ";

			$ord_page = 'credits.php';

		} elseif ($product_type=='S') {
			$sql = "SELECT * FROM `subscription_invoices` WHERE employer_id='".jb_escape_sql($_SESSION['JB_ID'])."' AND  `invoice_id`='".jb_escape_sql($invoice_id)."'  ";

			$ord_page = 'subscriptions.php';

		} elseif ($product_type=='M') {



			if ($_SESSION['JB_Domain']=='EMPLOYER') {

				$sql = "SELECT * FROM `membership_invoices` WHERE user_id='".jb_escape_sql($_SESSION['JB_ID'])."' AND  `invoice_id`='".jb_escape_sql($invoice_id)."' AND `user_type`='E'  ";
			} else {
				$sql = "SELECT * FROM `membership_invoices` WHERE user_id='".jb_escape_sql($_SESSION['JB_ID'])."' AND  `invoice_id`='".jb_escape_sql($invoice_id)."' AND `user_type`='C'  ";
			}

			$ord_page = 'membership.php';

		}

		$result = JB_mysql_query ($sql);
		$row = mysql_fetch_array($result, MYSQL_ASSOC);

		if (!mysql_num_rows($result)) {

			echo('invalid order id');

			return;

		}

	

		?>

		<h3><?php echo $label['google_status_heading']; ?></h3>
		<?php
		if ($row['status']=='Completed') {

			$label['payment_google_processed'] = str_replace('%ORD_PAGE%', $ord_page, $label['payment_google_processed']);

		?>
		<p><?php echo $label['payment_google_processed']; ?></p>
		<?php

		} else {
		?>
		<p><?php echo $label['payment_google_pending']; ?></p>
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