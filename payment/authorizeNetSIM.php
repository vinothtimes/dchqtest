<?php
# Copyright 2005-2009 Jamit Software
# http://www.jamit.com/

/**************************************************
*
* On how to create your own payment modules, please see:
*
* https://www.jamit.com.au/support/index.php?_m=knowledgebase&_a=viewarticle&kbarticleid=4
*
* This file can also be used as an example / starting point
* for your own modules.
*
* 
*
***************************************************/


require_once "../config.php";


// Development guide: http://developer.authorize.net/guides/SIM/
define ('IPN_LOGGING', 'Y');


function JB_authnet_mail_error($msg) {

	$date = date("D, j M Y H:i:s O"); 
	
	$headers = "From: ". JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Reply-To: ".JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Return-Path: ".JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "X-Mailer: PHP" ."\r\n";
	$headers .= "Date: $date" ."\r\n"; 
	$headers .= "X-Sender-IP: $REMOTE_ADDR" ."\r\n";

	$entry_line =  "(ccavenue error detected) $msg\r\n "; 
	$log_fp = @fopen("logs.txt", "a"); 
	@fputs($log_fp, $entry_line); 
	@fclose($log_fp);


	@mail(JB_SITE_CONTACT_EMAIL, "Error message from ".JB_SITE_NAME." Jamit authorize.net script. ", $msg, $headers);

}

function authnet_log_entry ($entry_line) {

	if (IPN_LOGGING == 'Y') {

		JB_payment_log_entry_db($entry_line, 'authorizeNet');

	}


}

function authnet_hmac ($key, $data) {
   // RFC 2104 HMAC implementation for php.
   // Creates an md5 HMAC.
   // Eliminates the need to install mhash to compute a HMAC
   // Hacked by Lance Rushing
   $b = 64; // byte length for md5
   if (strlen($key) > $b) {
       $key = pack("H*",md5($key));
   }
   $key  = str_pad($key, $b, chr(0x00));
   $ipad = str_pad('', $b, chr(0x36));
   $opad = str_pad('', $b, chr(0x5c));
   $k_ipad = $key ^ $ipad ;
   $k_opad = $key ^ $opad;
   return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
}


// compute HMAC-MD5
// Uses PHP mhash extension. Pl sure to enable the extension
//function authnet_hmac ($key, $data) {
//	return (bin2hex (authnet_mhash(AUTHNET_MHASH_MD5, $data, $key)));
//}

// Calculate and return fingerprint
// Use when you need control on the HTML output
function authnet_CalculateFP ($loginid, $x_tran_key, $amount, $sequence, $tstamp, $currency = "") {

	// $txnkey, $loginid . "^" . $sequence . "^" . $tstamp . "^" . $amount . "^" . $currency
	return (authnet_hmac ($x_tran_key, $loginid . "^" . $sequence . "^" . $tstamp . "^" . $amount . "^" . $currency));
}







###########################################################################
# Payment Object



class authorizeNet {

	var $name;
	var $description;
	var $className='authorizeNet';
	

	function authorizeNet() {
		global $label;
		$this->description = $label['payment_authnet_description'];
		$this->name = $label['payment_authnet_name'] ;
	
		if ($this->is_installed()) {


			$sql = "SELECT * FROM jb_config where `key`='AUTHNET_LOGIN_ID' OR `key`='AUTHNET_CURRENCY' OR `key`='AUTHNET_TEST_MODE' OR `key`='AUTHNET_X_RELAY_URL' OR `key`='AUTHNET_X_RECEIPT_LINK_METHOD' OR `key`='AUTHNET_X_RECEIPT_LINK_URL' OR `key`='AUTHNET_X_RECEIPT_LINK_TEXT' OR `key`='AUTHNET_X_TRAN_KEY' OR `key`='AUTHNET_X_BACKGROUND_URL' OR `key`='AUTHNET_X_COLOR_LINK' OR `key`='AUTHNET_X_COLOR_TEXT' OR `key`='AUTHNET_X_LOGO_URL' OR `key`='AUTHNET_X_COLOR_BACKGROUND' OR `key`='AUTHNET_X_HEADER_HTML_PAYMENT_FORM' OR `key`='AUTHNET_X_FOOTER_HTML_PAYMENT_FORM' or `key`='AUTHNET_CANDIDATE_X_RECEIPT_LINK_URL' OR `key`='AUTHNET_MD5_HASH' ";
			$result = JB_mysql_query($sql);

			while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

				define ($row['key'], $row['val']);

			}

			// guess the AUTHNET_CANDIDATE_X_RECEIPT_LINK_URL
			// these two constants were introduced in 2.9.0
			$host = $_SERVER['SERVER_NAME']; // hostname
			$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
			$http_url = explode ("/", $http_url);
			array_pop($http_url); // get rid of filename
			array_pop($http_url); // get rid of /admin
			$http_url = implode ("/", $http_url);


			if (!defined('AUTHNET_CANDIDATE_X_RECEIPT_LINK_URL')) {

				$url = "http://".$host.$http_url."/".JB_CANDIDATE_FOLDER;
				define('AUTHNET_CANDIDATE_X_RECEIPT_LINK_URL', $url);
			}

			

		}


	}

	function get_currency() {

		return AUTHNET_CURRENCY;

	}


	function install() {

		echo "Install Authorize.net ..<br>";

		$host = $_SERVER['SERVER_NAME']; // hostname
		$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
		$http_url = explode ("/", $http_url);
		array_pop($http_url); // get rid of filename
		array_pop($http_url); // get rid of /admin
		$http_url = implode ("/", $http_url);

		

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_MD5_HASH', '')";
		JB_mysql_query($sql);

	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_ENABLED', 'N')";
		JB_mysql_query($sql);
		
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_CURRENCY', 'USD')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_TEST_MODE', 'NO')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_LOGIN_ID', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_RELAY_URL', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_RECEIPT_LINK_URL', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_RECEIPT_LINK_METHOD', 'POST"."')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_RECEIPT_LINK_TEXT', '".jb_escape_sql(addslashes(JB_SITE_NAME))."')";
		JB_mysql_query($sql);


		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_TRAN_KEY', '')";
		JB_mysql_query($sql);


		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_LOGO_URL', '".jb_escape_sql(addslashes(JB_SITE_LOGO_URL))."')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_BACKGROUND_URL', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_COLOR_BACKGROUND', '#FFFFFF')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_COLOR_LINK', '#0000FF')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_COLOR_TEXT', '#000000')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_HEADER_HTML_PAYMENT_FORM', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_FOOTER_HTML_PAYMENT_FORM', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_CANDIDATE_X_RECEIPT_LINK_URL', '')";
		JB_mysql_query($sql);



		
		
	}

	function uninstall() {

		echo "Uninstall Authorize.net ..<br>";

	

		$sql = "DELETE FROM jb_config where `key`='AUTHNET_MD5_HASH'";
		JB_mysql_query($sql);


		$sql = "DELETE FROM jb_config where `key`='AUTHNET_ENABLED'";
		JB_mysql_query($sql);
		
		$sql = "DELETE FROM jb_config where `key`='AUTHNET_LOGIN_ID'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='AUTHNET_CURRENCY'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='AUTHNET_TEST_MODE'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='AUTHNET_X_RELAY_URL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='AUTHNET_X_RECEIPT_LINK_METHOD'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='AUTHNET_X_RECEIPT_LINK_URL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='AUTHNET_X_RECEIPT_LINK_TEXT'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='AUTHNET_X_TRAN_KEY'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='AUTHNET_X_BACKGROUND_URL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='AUTHNET_X_LOGO_URL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='AUTHNET_X_COLOR_BACKGROUND'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='AUTHNET_X_COLOR_LINK'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='AUTHNET_X_COLOR_TEXT'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='AUTHNET_X_FOOTER_HTML_PAYMENT_FORM'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='AUTHNET_X_HEADER_HTML_PAYMENT_FORM'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='AUTHNET_CANDIDATE_X_RECEIPT_LINK_URL'";
		JB_mysql_query($sql);
		
		
	}

	function payment_button($order_id, $product_type) {

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

		?>
		<center>
		<?php
		

		if (AUTHNET_TEST_MODE == 'YES') {
			//
			//https://test.authorize.net/gateway/transact.dll
		?>
		
			<FORM id="payment_button" action="https://certification.authorize.net/gateway/transact.dll" method="POST">
		
		<?php } else { ?>

			<FORM id="payment_button" action="https://secure.authorize.net/gateway/transact.dll" method="POST"> 
		
		<?php

		}

		
		$loginid = AUTHNET_LOGIN_ID;
		$x_tran_key = AUTHNET_X_TRAN_KEY;
		$x_Amount = $order_row['amount'];

		$amount = JB_convert_to_currency($x_Amount, $order_row['currency_code'], AUTHNET_CURRENCY) ;

		$amount = number_format($amount, 2, '.', '');

		// Seed random number for security and better randomness.

		srand(time());
		$sequence = rand(1, 1000);
		
		$tstamp = time ();

		$fingerprint = authnet_CalculateFP (AUTHNET_LOGIN_ID, AUTHNET_X_TRAN_KEY, $amount, $sequence, $tstamp, AUTHNET_CURRENCY);

		authnet_log_entry('Generated payment button with the following fingerprint'.$fingerprint);
		
		//echo "trans key: ".$x_tran_key.", login: $loginid seq: $sequence time: $tstamp amount $amount currency:".AUTHNET_CURRENCY." fprint $fingerprint<br>";

		echo ('<input type="hidden" name="x_fp_sequence" value="' . $sequence . '">' );
		echo ('<input type="hidden" name="x_fp_timestamp" value="' . $tstamp . '">' );
		echo ('<input type="hidden" name="x_fp_hash" value="' . strtoupper($fingerprint) . '">' );

		// Insert rest of the form elements similiar to the legacy weblink integration
		//echo ("<input type=\"hidden\" name=\"x_description\" value=\"" . $x_Description . "\">\n" );
		echo ("<input type=\"hidden\" name=\"x_login\" value=\"" . $loginid . "\">\n");
		echo ("<input type=\"hidden\" name=\"x_amount\" value=\"" . $amount . "\">\n");

		// *** IF YOU ARE PASSING CURRENCY CODE uncomment the line below *****
		echo ("<input type=\"hidden\" name=\"x_currency_code\" value=\"" . AUTHNET_CURRENCY . "\">\n");

		?>
<!--
		<INPUT type="hidden" name="x_background_url" value="<?php echo AUTHNET_X_BACKGROUND_URL;?>">
		<INPUT type="hidden" name="x_logo_url" value="<?php echo AUTHNET_X_LOGO_URL;?>">
		<INPUT type="hidden" name="x_color_background" value="<?php echo AUTHNET_X_COLOR_BACKGROUND;?>">
		<INPUT type="hidden" name="x_color_link" value="<?php echo AUTHNET_X_COLOR_LINK;?>">
		<INPUT type="hidden" name="x_color_text" value="<?php echo AUTHNET_X_COLOR_TEXT;?>">

		<INPUT type="hidden" name="x_receipt_link_method" value="<?php echo AUTHNET_X_RECEIPT_LINK_METHOD;?>">
		<?php if ($order_row['user_type']=='C') { // Is user type a Candiadte? ?> 
			<INPUT type="hidden" name="x_receipt_link_url" value="<?php echo AUTHNET_CANDIDATE_X_RECEIPT_LINK_URL;?>">
		<?php } else { ?>
			<INPUT type="hidden" name="x_receipt_link_url" value="<?php echo jb_escape_html(AUTHNET_X_RECEIPT_LINK_URL);?>">
		<?php } ?>
		<INPUT type="hidden" name="x_receipt_link_text" value="<?php echo jb_escape_html(AUTHNET_X_RECEIPT_LINK_TEXT);?>">

		
		<INPUT type="hidden" name="x_header_html_payment_form" value="<?php echo htmlentities(AUTHNET_X_HEADER_HTML_PAYMENT_FORM);?>">
		<INPUT type="hidden" name="x_footer_html_payment_form" value="<?php echo htmlentities(AUTHNET_X_FOOTER_HTML_PAYMENT_FORM);?>">
-->
		<INPUT type="hidden" name="x_cust_id" value="<?php 
			if ($product_type=='M') { // membership invoice
				echo $order_row['user_id'];
			} else { 
				echo $order_row['employer_id'];
			}?>">

		
		<INPUT type="hidden" name="x_relay_response" value="TRUE">
		<INPUT type="hidden" name="x_relay_url" value="<?php echo AUTHNET_X_RELAY_URL; ?>">
	
		<INPUT type="hidden" name="x_invoice_num" value="<?php echo jb_prefix_order_id($product_type.$order_row['invoice_id']);?>">
		<INPUT type="hidden" name="x_description" value="<?php echo JB_escape_html(JB_SITE_NAME);?>">
		
		<INPUT type="hidden" name="x_show_form" value="PAYMENT_FORM">
		<?php if (AUTHNET_TEST_MODE == 'YES') { ?>
			<INPUT type="hidden" name="x_test_request" value="TRUE">
		<?php } else { ?>
			<INPUT type="hidden" name="x_test_request" value="FALSE">
		<?php } ?>
		<INPUT type="submit" value="<?php echo $label['pay_by_authnet_button']; ?>">
		</FORM>
</center>
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

	function config_form() {
#AUTHNET_CANDIDATE_X_RECEIPT_LINK_URL and AUTHNET_CANDIDATE_X_RELAY_URL
		//echo "Note: The Authorize.net module is currently in beta in this version<br>";

		if ($_REQUEST['action']=='save') {

			$authnet_login_id = $_REQUEST['authnet_login_id'];
			$authnet_currency = $_REQUEST['authnet_currency'];
			$authnet_test_mode = $_REQUEST['authnet_test_mode'];
			$authnet_x_relay_url = $_REQUEST['authnet_x_relay_url'];
			$authnet_x_receipt_link_method = $_REQUEST['authnet_x_receipt_link_method'];
			$authnet_x_receipt_link_url = $_REQUEST['authnet_x_receipt_link_url'];
			$authnet_x_receipt_link_text = $_REQUEST['authnet_x_receipt_link_text'];
			$authnet_x_tran_key = $_REQUEST['authnet_x_tran_key'];
			$authnet_x_background_url = $_REQUEST['authnet_x_background_url'];
			$authnet_x_logo_url = $_REQUEST['authnet_x_logo_url'];
			$authnet_x_color_background = $_REQUEST['authnet_x_color_background'];
			$authnet_x_color_link = $_REQUEST['authnet_x_color_link'];
			$authnet_x_color_text = $_REQUEST['authnet_x_color_text'];
			$authnet_x_header_html_payment_form = $_REQUEST['authnet_x_header_html_payment_form'];
			$authnet_x_footer_html_payment_form = $_REQUEST['authnet_x_footer_html_payment_form'];
			$authnet_candidate_x_receipt_link_url = $_REQUEST['authnet_candidate_x_receipt_link_url'];
			$authnet_md5_hash = $_REQUEST['authnet_md5_hash'];
			//$authnet_candidate_x_relay_url = $_REQUEST['authnet_candidate_x_relay_url'];
		} else {

			$authnet_login_id = AUTHNET_LOGIN_ID;
			$authnet_currency = AUTHNET_CURRENCY;
			$authnet_test_mode = AUTHNET_TEST_MODE;
			$authnet_x_relay_url = AUTHNET_X_RELAY_URL;
			$authnet_x_receipt_link_method = AUTHNET_X_RECEIPT_LINK_METHOD;
			$authnet_x_receipt_link_url = AUTHNET_X_RECEIPT_LINK_URL;
			$authnet_x_receipt_link_text = AUTHNET_X_RECEIPT_LINK_TEXT;
			$authnet_x_tran_key = AUTHNET_X_TRAN_KEY;
			$authnet_x_background_url = AUTHNET_X_BACKGROUND_URL;
			$authnet_x_logo_url = AUTHNET_X_LOGO_URL;
			$authnet_x_color_background = AUTHNET_X_COLOR_BACKGROUND;
			$authnet_x_color_link = AUTHNET_X_COLOR_LINK;
			$authnet_x_color_text = AUTHNET_X_COLOR_TEXT;
			$authnet_x_header_html_payment_form = AUTHNET_X_HEADER_HTML_PAYMENT_FORM;
			$authnet_x_footer_html_payment_form = AUTHNET_X_FOOTER_HTML_PAYMENT_FORM;
			$authnet_candidate_x_receipt_link_url = AUTHNET_CANDIDATE_X_RECEIPT_LINK_URL;
			$authnet_md5_hash = AUTHNET_MD5_HASH;
			//$authnet_candidate_x_relay_url = AUTHNET_CANDIDATE_X_RELAY_URL;
		}

		$host = $_SERVER['SERVER_NAME']; // hostname
		  $http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
		  $http_url = explode ("/", $http_url);
		  array_pop($http_url); // get rid of filename
		  array_pop($http_url); // get rid of /admin
		  $http_url = implode ("/", $http_url);

		?>
		<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
		<table border="0" cellpadding="5" cellspacing="2" style="border-style:groove" width="100%" bgcolor="#FFFFFF">
   <tr>
	<td colspan="2">
	Welcome to the SIM Authorize.net payment module<br>
	Additional instructions for your Authorize.net account can be found here:<br>
	<a href="http://www.authorize.net/support/Merchant/default.htm" target="_blank">http://www.authorize.net/support/Merchant/default.htm (opens in a new window)</a><br>
	- See Section Three, 'Server Intergration Method (SIM)
	</td>

   </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Authorize.Net 
      Login ID</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_login_id" size="33" value="<?php echo $authnet_login_id; ?>"></font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Test Mode (Y/N)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
       <input type="radio" name="authnet_test_mode" value="YES"  <?php if ($authnet_test_mode=='YES') { echo " checked "; } ?> >Yes <br>
	  <input type="radio" name="authnet_test_mode" value="NO"  <?php if ($authnet_test_mode=='NO') { echo " checked "; } ?> >No<br></font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Authorize.Net 
      Currency</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select name="authnet_currency"  value="<?php echo $authnet_currency; ?>">
	  <!-- <option value="USD" >USD</option>-->
	  <?php JB_currency_option_list ($authnet_currency); ?>
	  </select>(Please select a currency that is supported by Authorize.Net. If the currency is not on this list, you may add it under the Configuration section)
	  </font></td>
    </tr>

	<?php

	if (trim($authnet_x_relay_url)=='') {
		$authnet_x_relay_url = 'http://'.$host.$http_url."/payment/authorizeNetSIM.php";
	}
	?>
	 
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Authorize.Net 
      Relay Response URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_x_relay_url" size="80" value="<?php echo $authnet_x_relay_url; ?>"><br>(Recommended: <b>http://<?php echo $host.$http_url."/payment/authorizeNetSIM.php"; ?>) </b><br>Note: This URL must be configured in the Merchant Interface.<br>
	  Important: For displaying the results of the transcation to the customer, Authorize.net suggests that you either use the 'Hosted Receipt Page' method or the 'Relay Response' method, but not both. Jamit uses the 'Relay Response' method to process the transaction and output a response back to Authorize.net, please configure your Authorize.net account to only use that method.<br>
	  
<p>
	  To configure Relay Response for your transactions:<br>
   1. Log into the Merchant Interface at https://secure.authorize.net<br>
   2. Click Settings under Account in the main menu on the left<br>
   3. Click Relay Response in the Transaction Format Settings section<br>
   4. Enter the URL that you have entered above on this form<br>
   5. Click Submit<br>
   </p>
   <b>Authorize.net will not work if you are testing from a firewalled LAN / localhost - this is because Authorize.net needs to POST back to this URL to complete the transaction</b>
 </font></td>
    </tr>
<!--
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Authorize.Net 
      Receipt Link Method</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select type="text" name="authnet_x_receipt_link_method"  value="<?php echo $authnet_x_receipt_link_method; ?>">
	  <option value="POST">POST (recommended)</option>
	  <option value="GET">GET</option>
	  <option value="LINK">LINK (hyperlink)</option>

	  </select>
	  (What way to return to the Jamit script.)</font></td>
    </tr>
-->
	<?php
		if (trim($authnet_x_receipt_link_url)=='') {
			'http://'.$authnet_x_receipt_link_url = $host.$http_url."/".JB_EMPLOYER_FOLDER."thanks.php?m=".$this->className;
		}

	?>
<!--
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Authorize.Net 
      Receipt link URL (Employers)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_x_receipt_link_url" size="50" value="<?php echo $authnet_x_receipt_link_url; ?>"><br>(Recommended: <b>http://<?php echo $host.$http_url."/".JB_EMPLOYER_FOLDER."thanks.php?m=".$this->className; ?> </b><br>Note: This URL must be configured in the Merchant Interface)</font></td>
    </tr>
-->
	<?php
		if (trim($authnet_candidate_x_receipt_link_url)=='') {
			'http://'.$authnet_candidate_x_receipt_link_url = $host.$http_url."/".JB_CANDIDATE_FOLDER."thanks.php?m=".$this->className;
		}

	?>
<!--
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Authorize.Net 
      Receipt link URL (Candidates)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_candidate_x_receipt_link_url" size="50" value="<?php echo $authnet_candidate_x_receipt_link_url; ?>"><br>(Recommended: <b>http://<?php echo $host.$http_url."/".JB_CANDIDATE_FOLDER."thanks.php?m=".$this->className; ?> </b> )</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Authorize.Net 
      Receipt link Text</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_x_receipt_link_text" size="50" value="<?php echo $authnet_x_receipt_link_text; ?>"><br>(Anchor text for the Receipt link URL - where customers return back to the Job board)</font></td>
    </tr>
-->

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Authorize.net Transaction Key</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="password" name="authnet_x_tran_key" size="50" value="<?php echo $authnet_x_tran_key; ?>"><br>(Note: 1. Log in to the Merchant Interface, 2. Select 'Settings' from the Main Menu, 3. Click on the Obtain Transaction Key in the Security section, 4. Type in the answer to your secret question, 5. Click Submit, 6. The transaction key is returned by the Merchant Interface.
	   )</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Authorize.net MD5 Hash Value</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="password" name="authnet_md5_hash" size="50" value="<?php echo $authnet_md5_hash; ?>"><br>Note:
	  To configure an MD5 Hash value for your account:<br>

   1. Log into the Merchant Interface at https://secure.authorize.net<br>
   2. Click Settings under Account in the main menu on the left<br>
   3. Click MD5-Hash in the Security Settings section<br>
   4. Enter any random value to use for your MD5 Hash Value. Enter the value again to confirm<br>
   5. Click Submit<br>
   Please note that the MD5 Hash value is not displayed on the screen once submitted. You will need to update this setting here any time you change the value.<br>
	  </font></td>
    </tr>
<!--
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Logo URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_x_logo_url" size="50" value="<?php echo $authnet_x_logo_url; ?>"><br>(Logo on the Payment form & Receipt Page, eg http://www.example.com/test.gif)</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Background Image URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_x_background_url" size="50" value="<?php echo $authnet_x_background_url; ?>"><br>(Background image on the Payment form & Receipt Page)</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Background color</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_x_color_background" size="50" value="<?php echo $authnet_x_color_background; ?>"><br>(Background Color of the Payment form & Receipt Page, any HTML color or hex code, eg #FFFFFF)</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Link color</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_x_color_link" size="50" value="<?php echo $authnet_x_color_link; ?>"><br>(Logo on the Payment form & Receipt Page)</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Payment form: Header HTML</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <textarea name="authnet_x_header_html_payment_form" ><?php echo $authnet_x_header_html_payment_form; ?></textarea><br>(The text submitted in this field will be dispalyed as the header on the Payment Form)</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Payment form: Footer HTML</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <textarea name="authnet_x_footer_html_payment_form" ><?php echo $authnet_x_footer_html_payment_form; ?></textarea><br>(The text submitted in this field will be dispalyed as the footer on the Payment Form)</font></td>
    </tr>
	<!--
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Payment Form: Footer HTML</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <textarea  name="authnet_x_footer_html_payment_form" size="50"> <?php echo $authnet_x_header_footer_payment_form; ?></textarea><br>(The text submitted in this field will be dispalyed as the footer on the Payment Form)</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Text color</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_x_color_text" size="50" value="<?php echo $authnet_x_color_text; ?>"><br>(Logo on the Payment form & Receipt Page, any HTML color or hex code, eg #FFFFFF)</font></td>
    </tr>
	-->
	 <tr>
	
      <td  bgcolor="#e6f2ea" colspan=2><font face="Verdana" size="1"><input type="submit" value="Save">
	  </td>
	  </tr>
    
  </table>
  <input type="hidden" name="pay" value="<?php echo jb_escape_html($_REQUEST['pay']);?>">
  <input type="hidden" name="action" value="save">
  </form>

  <?php

		

	}

	function save_config() {

	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_MD5_HASH', '".jb_escape_sql(trim($_REQUEST['authnet_md5_hash']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_LOGIN_ID', '".jb_escape_sql(trim($_REQUEST['authnet_login_id']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_CURRENCY', '".jb_escape_sql(trim($_REQUEST['authnet_currency']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_TEST_MODE', '".jb_escape_sql(trim($_REQUEST['authnet_test_mode']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_RELAY_URL', '".jb_escape_sql(trim($_REQUEST['authnet_x_relay_url']))."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_RECEIPT_LINK_METHOD', '".jb_escape_sql(trim($_REQUEST['authnet_x_receipt_link_method']))."')";
		JB_mysql_query($sql);	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_RECEIPT_LINK_URL', '".jb_escape_sql(trim($_REQUEST['authnet_x_receipt_link_url']))."')";
		JB_mysql_query($sql);	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_RECEIPT_LINK_TEXT', '".jb_escape_sql(trim($_REQUEST['authnet_x_receipt_link_text']))."')";
		JB_mysql_query($sql);	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_TRAN_KEY', '".jb_escape_sql(trim($_REQUEST['authnet_x_tran_key']))."')";
		JB_mysql_query($sql);	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_BACKGROUND_URL', '".jb_escape_sql(trim($_REQUEST['authnet_x_background_url']))."')";
		JB_mysql_query($sql);	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_COLOR_BACKGROUND', '".jb_escape_sql(trim($_REQUEST['authnet_x_color_background']))."')";
		JB_mysql_query($sql);	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_COLOR_LINK', '".jb_escape_sql(trim($_REQUEST['authnet_x_color_link']))."')";
		JB_mysql_query($sql);	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_COLOR_TEXT', '".jb_escape_sql(trim($_REQUEST['authnet_x_color_text']))."')";
		JB_mysql_query($sql);	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_LOGO_URL', '".jb_escape_sql(trim($_REQUEST['authnet_x_logo_url']))."')";
		JB_mysql_query($sql);	

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_HEADER_HTML_PAYMENT_FORM', '".jb_escape_sql(trim($_REQUEST['authnet_x_header_html_payment_form']))."')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_X_FOOTER_HTML_PAYMENT_FORM', '".jb_escape_sql(trim($_REQUEST['authnet_x_footer_html_payment_form']))."')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('AUTHNET_CANDIDATE_X_RECEIPT_LINK_URL', '".jb_escape_sql(trim($_REQUEST['authnet_candidate_x_receipt_link_url']))."')";
		JB_mysql_query($sql);
		

	}

	// true or false
	function is_enabled() {

		$sql = "SELECT val from jb_config where `key`='AUTHNET_ENABLED' ";
		$result = JB_mysql_query($sql);
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		if ($row['val']=='Y') {
			return true;

		} else {
			return false;

		}

	}

	// true or false
	function is_installed() {

		$sql = "SELECT val from jb_config where `key`='AUTHNET_ENABLED' ";
		$result = JB_mysql_query($sql);
		

		if (mysql_num_rows($result)>0) {
			return true;

		} else {
			return false;

		}

	}

	function enable() {

		$sql = "UPDATE jb_config set val='Y' where `key`='AUTHNET_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);


	}

	function disable() {

		$sql = "UPDATE jb_config set val='N' where `key`='AUTHNET_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);

	}

	// process_payment_return() - Called when thanks.php page is accessed after returning from payment gateway
	// - Place affiliate code here.
	// - You can access all the variables returned form the payment gatway through the $_REQUEST array (If the payment gateway returned any variables)
	// - place any other functionality here.

	function process_payment_return() {

		global $label;

		/*
Note: This should not be executed in the normal course of events.
Instead, the code at the top is executed to verify / complete the transaction
The code below will check previous transactions to make sure it is not
processed twice.

		*/

		if ($_REQUEST['x_response_code']!='') { 

			foreach ($_REQUEST as $key => $value) {
				$req .= "&$key=$value";
			}
			

			$invoice_id = jb_strip_order_id ($_REQUEST['x_invoice_num']);
			$product_type = substr($invoice_id, 0, 1);// 'P' or 'S'
			$invoice_id = substr($invoice_id, 1);

			$myhash = strtoupper (md5 ( AUTHNET_MD5_HASH.AUTHNET_LOGIN_ID.$_REQUEST['x_trans_id'].$_REQUEST['x_amount'] ));

			authnet_log_entry('x_response_code:  '.$_REQUEST['x_response_code'].' myhash '.$myhash.' md5_hash '.$_REQUEST['x_MD5_Hash'].' invoice_id'.$invoice_id.' product_type'.$product_type);

			if ($_REQUEST['x_MD5_Hash']==$myhash) {

				authnet_log_entry('authorize.net: (gateway) '.$req); // log the request

				switch ($_REQUEST['x_response_code']) {

					case "1": // approved

						// check for the transaction
						$sql = "SELECT * FROM jb_txn WHERE txn_id='".jb_escape_sql($_REQUEST['x_trans_id'])."' and `origin`='AuthorizeNet' ";
						$result = JB_mysql_query($sql) or die (mysql_error()); 
						if (mysql_num_rows($result)> 0) { 
							authnet_log_entry($_REQUEST['x_trans_id']." already in the database, possibly entered in by the Relay Responce IR: System)");
						
						} else {

							echo "<p align='center'> ".$label['payment_authnet_completed']."</p>";

							if ($product_type=='P') {

								JB_complete_package_invoice($invoice_id, 'AuthorizeNet');
								
								?>
								<center>

								<img src="<?php echo JB_SITE_LOGO_URL; ?>">
								<?php
								$label['payment_posts_completed2'] = str_replace("%URL%", JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER, $label['payment_posts_completed2']);	
								?>
								<p><?php echo $label['payment_posts_completed2']; ?></p>
								</center>
								<?php
							} elseif ($product_type=='S') {
								JB_complete_subscription_invoice($invoice_id, 'AuthorizeNet');
								?>
								<center>

								<img src="<?php echo JB_SITE_LOGO_URL; ?>">
								<?php
								$label['payment_subscription_completed2'] = str_replace("%URL%", JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER, $label['payment_subscription_completed2']);	
								?>
								<p><?php echo $label['payment_subscription_completed2']; ?></p>
								</center>
								<?php
							} elseif ($product_type=='M') {

									JB_complete_membership_invoice($invoice_id, 'AuthorizeNet');
									?>
									<center>

									<img src="<?php echo JB_SITE_LOGO_URL; ?>">
									<p><?php echo $label['payment_membership_completed']; ?></p>
									</center>
									<?php

							}
							
							JB_debit_transaction($invoice_id, $_REQUEST['x_amount'], AUTHNET_CURRENCY, $_REQUEST['x_trans_id'], $reason, 'AuthorizeNet', $product_type);

						}
						//JB_debit_transaction($invoice_id, $_POST['x_amount'], 'USD', $_POST['x_trans_id'], $reason, 'AuthorizeNet', $product_type);
						break;
					case "2": // declined
						$label['payment_return_denied'] = str_replace("%PAYMENT_GW%", $this->name, $label['payment_return_denied']);	
						echo "<p align='center'> ".$label['payment_return_denied']."</p>";
						break;
					case "3": // Error
						echo "<p align='center'>".$label['payment_return_error']."</p>";
						break;
					default:
						echo "<p align='center'>".$label['payment_return_error']."</p>";
						break;
				}

			} else {
				authnet_log_entry( "Authorize.net: Invalid signiture (pls verify your md5 hash value)");

			}
			
		}

		?>

		
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>

		<?php


	}

}

$_PAYMENT_OBJECTS['authorizeNet'] = new authorizeNet;



##########################################################################
# Relay return / gateway response


/*
Relay Response

A Relay Response configuration indicates to the payment gateway that you would like to receive the transaction response and use it to create a custom receipt page for display to the customer.
*/


if ($_REQUEST['x_response_code']!='') { 


	$_REQUEST['m'] = 'authorizeNet'; // let thanks.php know that it was called from authorizeNetSim

	foreach ($_REQUEST as $key => $value) {
		$req .= "&$key=$value";
	}

	authnet_log_entry('This is what was posted by Authorize.net: '.$req);

	

	// determine the product type and invoice id
	$invoice_id = jb_strip_order_id ($_REQUEST['x_invoice_num']);
	$product_type = substr($invoice_id, 0, 1);// 'P' or 'S' or 'M'
	$invoice_id = substr($invoice_id, 1);


	// membership
	if ($product_type=='M') {
		$invoice_row = JB_get_membership_invoice_row ($invoice_id);
	}

	
	
	ob_start();

	if ($invoice_row['user_type']=='C') { // candidates

		// This is a clone of the myjobs/thanks.php page

		chdir(JB_basedirpath().JB_CANDIDATE_FOLDER);

		JB_template_candidates_header();

		?>

		<h3><center><?php 

		echo $label['e_thanks_payment_return']; ?>

		</center></h3>

		<?php


		//process_payment_return('authorizeNet');
		$_PAYMENT_OBJECTS['authorizeNet']->process_payment_return();

		JB_template_candidates_footer();

		
		// Add a base tag to point to the candidates section
		$c = ob_get_contents();
		$c = preg_replace('/<HEAD>/i', '<HEAD><base href="'.JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER.'" >', $c);

		
	} else {

		chdir(JB_basedirpath().JB_EMPLOYER_FOLDER);

		// This is a clone of the employers/thanks.php page
		JB_template_employers_header();


		?>
		<h3><center><?php 

		echo $label['e_thanks_payment_return']; ?>

		</center></h3>
		<?php

		
		$_PAYMENT_OBJECTS['authorizeNet']->process_payment_return();
	
		JB_template_employers_footer();

	

		// Add a base tag to point to the candidates section
		$c = ob_get_contents();
		$c = preg_replace('/<HEAD>/i', '<head><base href="'.JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER.'" >', $c);
		
	}
	ob_end_clean();
	
	echo $c; // output the buffered thanks.php page

	
} else {

	foreach ($_REQUEST as $key => $value) {
		$req .= "&$key=$value";
	}

	//authnet_log_entry('Debug info: ['.base64_encode($req).']');


}
?>