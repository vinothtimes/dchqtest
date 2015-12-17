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
***************************************************/

require_once "../config.php";

$_PAYMENT_OBJECTS['NOCHEX'] =  new NOCHEX;
define (IPN_LOGGING, 'Y');

function JB_nc_mail_error($msg) {

	$date = date("D, j M Y H:i:s O"); 
	
	$headers = "From: ". JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Reply-To: ".JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Return-Path: ".JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "X-Mailer: PHP" ."\r\n";
	$headers .= "Date: $date" ."\r\n"; 
	$headers .= "X-Sender-IP: $REMOTE_ADDR" ."\r\n";

	//$entry_line =  "(payal error detected) $msg\r\n "; 
	//$log_fp = @fopen("logs.txt", "a"); 
	//@fputs($log_fp, $entry_line); 
	//@fclose($log_fp);


	@mail(JB_SITE_CONTACT_EMAIL, "Error message from ".JB_SITE_NAME." Jamit nochexAPC script. ", $msg, $headers);

}

function nc_log_entry ($entry_line) {

	if (IPN_LOGGING == 'Y') {

		JB_payment_log_entry_db($entry_line, 'NOCHEX');

	}


}



if (($_POST['transaction_id']!='') ) {

	// check if we can post back to nochex
	if (stristr(ini_get('disable_functions'), "fsockopen")) {
		JB_nc_mail_error ( "<p>fsockopen is disabled on this server, this script can not post information to the nochex server for IPN confirmation.");
		die();
	}

	// read the post from nochex system and add 'cmd'
	$req = 'cmd=_notify-validate';

	foreach ($_POST as $key => $value) {
		
		//if (get_magic_quotes_gpc()) {
		$value = stripslashes($value);
		//}
		$value = urlencode($value);
		$req .= "&$key=$value";
		
	}

	nc_log_entry("Sending this to NOCHEX:".$req);	

	$header .= "POST /nochex.dll/apc/apc HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	$fp = fsockopen ("www.nochex.com", 80, $errno, $errstr, 30);

	$To_email = $_POST['To_email']; 
	$From_email = $_POST['From_email'];
	$transaction_id = $_POST['transaction_id'];
	$txn_id = $transaction_id;
	$transaction_date = $_POST['transaction_date'];
	$payment_date = $transaction_date;
	$order_id = $_POST['order_id'];
	$amount = $_POST['amount'];
	$security_key = $_POST['security_key'];
	$status = $_POST['status'];
	//$txn_type = 'web_accept';
	//$mc_gross = $amount;
	//$mc_currency = "GBP";
	
	$invoice_id = jb_strip_order_id($order_id);
	$product_type = substr($invoice_id, 0, 1);// 'P' or 'S' or 'M'
	$invoice_id = substr($invoice_id, 1);


	if (!$fp) {
	// HTTP ERROR
		$entry_line =  "HTTP ERROR! cannot post back to nochex\r\n "; 
		nc_log_entry($entry_line);
	} else {

		fputs ($fp, $header . $req); // post to nochex


		while (!feof($fp)) {
		$res = fgets ($fp, 1024);

			$entry_line =  "$res"; 
			

			if (strcmp ($res, "AUTHORISED") == 0) {


				
				$VERIFIED = 1;
				$payment_status = 'Completed';

				nc_log_entry($entry_line." We have the following data: payment_status:$payment_status, VERIFIED:$VERIFIED, invoice_id:$invoice_id, txn_id:$txn_id, amount:$amount  ");

				/*
				// check that receiver_email is your Primary nochex email
				if(strcmp(strtolower(NOCHEX_EMAIL), strtolower($To_email))!=0) {
					JB_nc_mail_error ("Possible fraud. Error with receiver_email. ".strtolower(NOCHEX_EMAIL)." != ".strtolower($To_email)."\n");
					nc_log_entry("Possible fraud. Error with receiver_email. ".strtolower(NOCHEX_EMAIL)." != ".strtolower($To_email));
					$VERIFIED = false;	
				} 
				*/

				// check so that transactrion id cannot be reused

				$sql = "SELECT * FROM jb_txn WHERE txn_id='".jb_escape_sql($txn_id)."' and origin='NOCHEX' ";
				$result = JB_mysql_query($sql) or die (mysql_error().$sql); 
				if (mysql_num_rows($result) > 0) {
					JB_nc_mail_error ("Possible fraud. Transaction id: $txn_id is already in the database. \n");
					nc_log_entry("Possible fraud. Transaction id: $txn_id is already in the database.");
					$VERIFIED = false;	

				}
				
				$entry_line =  "verified: $res";
				nc_log_entry($entry_line);
			}
			else if (strcmp ($res, "DECLINED") == 0) {
				nc_log_entry($entry_line);
			// log for manual investigation
				nc_log_entry("Transaction id: $txn_id was DECLINED.");
				$VERIFIED = false;
				$payment_status = 'Denied';
				JBPLUG_do_callback('pay_trn_verification_failed', $invoice_id, $product_type);
				
			}
		}
		fclose ($fp);


		// if VERIFIED=1 process payment
		if ($VERIFIED) { 

			JBPLUG_do_callback('pay_trn_verification_passed', $invoice_id, $product_type);

			switch ($payment_status) {
				
				case "Completed": // Funds successfully transferred
					if ($product_type=='P') {
						JB_complete_package_invoice($invoice_id, 'NOCHEX');
					} elseif ($product_type=='S') {
						JB_complete_subscription_invoice($invoice_id, 'NOCHEX');
					} elseif ($product_type=='M') {
						JB_complete_membership_invoice($invoice_id, 'NOCHEX');
					}
					JBPLUG_do_callback('pay_trn_completed', $invoice_id, $product_type);
					pp_log_entry("Completed NOCHEX: $product_type ".$invoice_id);
					
					JB_debit_transaction($invoice_id, $amount, 'GBP', $txn_id, $reason, 'NOCHEX', $product_type);

					break;
					
				default:
					JBPLUG_do_callback('pay_trn_failed', $invoice_id, $product_type);
					break;
					
			}


		}

	}

}

###########################################################################
# Payment Object



class NOCHEX {

	var $name="NOCHEX";
	var $description="NOCHEX - Credit Card Payments. Accepts British Pounds.";
	var $className="NOCHEX";
	

	function NOCHEX() {

		global $label;
		$this->name=$label['payment_nochex_name'];
		$this->description=$label['payment_nochex_description'];

		if ($this->is_installed()) {

			$sql = "SELECT * FROM jb_config where `key`='NOCHEX__ENABLED' OR `key`='NOCHEX_LOGO_URL' OR `key`='NOCHEX_CANCEL_RETURN_URL' OR `key`='NOCHEX_RETURN_URL' OR `key`='NOCHEX_APC_URL' OR `key`='NOCHEX_BUTTON_URL' OR `key`='NOCHEX_EMAIL' OR `key`='NOCHEX_CURRENCY' OR `key`='NOCHEX_TEST' OR `key`='NOCHEX_CANDIDATE_CANCEL_RETURN_URL' OR `key`='NOCHEX_CANDIDATE_RETURN_URL'  ";
			$result = JB_mysql_query($sql) or die (mysql_error().$sql);

			while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

				define ($row['key'], $row['val']);

			}

			// guess the NOCHEX_CANDIDATE_CANCEL_RETURN_URL // NOCHEX_CANDIDATE_RETURN_URL
			// these two constants were introduced in 2.9.0
			$host = $_SERVER['SERVER_NAME']; // hostname
			$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
			$http_url = explode ("/", $http_url);
			array_pop($http_url); // get rid of filename
			array_pop($http_url); // get rid of /admin
			$http_url = implode ("/", $http_url);

			if (!defined('NOCHEX_CANDIDATE_RETURN_URL')) {
				$url = "http://".$host.$http_url."/".JB_CANDIDATE_FOLDER."thanks.php?m=".$this->className;
				define('NOCHEX_CANDIDATE_RETURN_URL', $url);
			}

			if (!defined('NOCHEX_CANDIDATE_CANCEL_RETURN_URL')) {
				$url = "http://".$host.$http_url."/";
				define('NOCHEX_CANDIDATE_CANCEL_RETURN_URL', $url);
			}

		}

	}

	function get_currency() {

		return NOCHEX_CURRENCY;

	}

 
	function install() {

		$host = $_SERVER['SERVER_NAME']; // hostname
		$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
		$http_url = explode ("/", $http_url);
		array_pop($http_url); // get rid of filename
		array_pop($http_url); // get rid of /admin
		$http_url = implode ("/", $http_url);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_EMAIL', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_ENABLED', 'N')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_LOGO_URL', '')";
		JB_mysql_query($sql);
		
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_CANCEL_RETURN_URL', '')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_RETURN_URL', '')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_APC_URL', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_BUTTON_URL', 'http://support.nochex.com/web/images/cardsboth2.gif')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_CURRENCY', 'GBP')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_CANDIDATE_CANCEL_RETURN_URL', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_CANDIDATE_RETURN_URL', '')";
		JB_mysql_query($sql);

	}

	function uninstall() {

		$sql = "DELETE FROM jb_config where `key`='NOCHEX_EMAIL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='NOCHEX_ENABLED'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='NOCHEX_LOGO_URL'";
		JB_mysql_query($sql);
		//$sql = "REPLACE INTO jb_config (`key`, val, descr) VALUES ('_2CO_PRODUCT_ID', '1', '# Your 2CO seller ID number.')";
		//JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='NOCHEX_CANCEL_RETURN_URL'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='NOCHEX_RETURN_URL'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='NOCHEX_APC_URL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='NOCHEX_BUTTON_URL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='NOCHEX_CURRENCY'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='NOCHEX_CANDIDATE_CANCEL_RETURN_URL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='NOCHEX_CANDIDATE_RETURN_URL'";
		JB_mysql_query($sql);


	}

	function payment_button($invoice_id, $product_type) {

		if (func_num_args() > 1) {
			$product_type = func_get_arg(1);
		}

		if ($product_type == '') {
			$product_type = 'P'; // posting package
		}

		if ($product_type=='P') {
			$order_row = JB_get_product_invoice_row ($invoice_id);
		} elseif ($product_type=='S') {
			$order_row = JB_get_subscription_invoice_row($invoice_id);
		} elseif ($product_type=='M') {
			$order_row = JB_get_membership_invoice_row($invoice_id);
		}

		
		?>
		<form id='payment_button' action="<?php
		
		if (NOCHEX_TEST=='Y') {
			echo "https://www.nochex.com/nochex.dll/apc/testapc";
		} else {
			echo "https://www.nochex.com/nochex.dll/checkout";

		}

		?>" name="form1" method="post" target="_parent">
		  
		<input type="hidden" value="<?php echo trim(NOCHEX_EMAIL); ?>" name="email"/>
		<input type="hidden" value="<?php echo number_format(JB_convert_to_currency($order_row['amount'], $order_row['currency_code'], NOCHEX_CURRENCY), 2, '.', ''); ?>" name="amount"/>
		<input type="hidden" value="<?php echo jb_prefix_order_id($product_type.$order_row['invoice_id']); ?>" name="ordernumber" />
		<input type="hidden" value="<?php echo jb_escape_html($order_row['item_name']); ?>" name="description" />
		<?php if (trim(NOCHEX_LOGO_URL)!='') {?>
			<input type="hidden" value="<?php echo NOCHEX_LOGO_URL; ?>" name="logo" />
		<?php } ?>

		<input type="hidden" value="<?php echo trim(NOCHEX_APC_URL); ?>" name="responderurl"/>

		<?php if ($order_row['user_type']=='C') { ?>
			<input type="hidden" value="<?php echo NOCHEX_CANDIDATE_RETURN_URL; ?>" name="returnurl"/>
		<?php } else { ?>
			<input type="hidden" value="<?php echo NOCHEX_RETURN_URL; ?>" name="returnurl"/>
		<?php } ?>
		<?php if ($order_row['user_type']=='C') { ?>
			<input type="hidden" value="<?php echo NOCHEX_CANDIDATE_CANCEL_RETURN_URL; ?>" name="cancel"/>
		<?php } else { ?>
			<input type="hidden" value="<?php echo NOCHEX_CANCEL_RETURN_URL; ?>" name="cancel"/>
		<?php } ?>
		<p align="center">
		<input target="_parent" type="image" alt="I accept payment using NOCHEX" src="<?php echo NOCHEX_BUTTON_URL; ?>" border="0" name="submit" />

		</P>	  

		</p>
	</form>
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

		if ($_REQUEST['action']=='save') {
		

			$nochex_email = $_REQUEST['nochex_email'];
			$nochex_apc_url = $_REQUEST['nochex_apc_url'];
			$nochex_subscr_apc_url = $_REQUEST['nochex_subscr_apc_url'];
			$nochex_return_url = $_REQUEST['nochex_return_url'];
			$nochex_cancel_return_url = $_REQUEST['nochex_cancel_return_url'];
			$nochex_logo_url = $_REQUEST['nochex_logo_url'];
			$nochex_button_url = $_REQUEST['nochex_button_url'];
			$nochex_currency = $_REQUEST['nochex_currency'];
			$nochex_test = $_REQUEST['nochex_test'];
			$nochex_candidate_cancel_return_url = $_REQUEST['nochex_candidate_cancel_return_url'];
			$nochex_candidate_return_url = $_REQUEST['nochex_candidate_return_url'];
		} else {
			$nochex_email = NOCHEX_EMAIL;
			$nochex_apc_url = NOCHEX_APC_URL;
			$nochex_subscr_apc_url = NOCHEX_SUBSCR_APC_URL;
			$nochex_return_url = NOCHEX_RETURN_URL;
			$nochex_cancel_return_url = NOCHEX_CANCEL_RETURN_URL;
			$nochex_logo_url = NOCHEX_LOGO_URL;
			$nochex_button_url = NOCHEX_BUTTON_URL;
			$nochex_currency = NOCHEX_CURRENCY;
			$nochex_test = NOCHEX_TEST;
			$nochex_candidate_cancel_return_url = NOCHEX_CANDIDATE_CANCEL_RETURN_URL;
			$nochex_candidate_return_url = NOCHEX_CANDIDATE_RETURN_URL;
		}

		$host = $_SERVER['SERVER_NAME']; // hostname
		  $http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
		  $http_url = explode ("/", $http_url);
		  array_pop($http_url); // get rid of filename
		  array_pop($http_url); // get rid of /admin
		  $http_url = implode ("/", $http_url);

		?>
	<p>Note: Please remeber to turn on APC settings for your NOCHEX account. See NOCHEX support for more info. The recommended APC URL is: <b>http://<?php echo $host.$http_url."/payment/nochexAPC.php"; ?></p>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
		 <table border="0" cellpadding="5" cellspacing="2" style="border-style:groove" width="100%" bgcolor="#FFFFFF">

		 
    <tr>
      <td colspan="2"  bgcolor="#e6f2ea">
      <font face="Verdana" size="1"><b>NOCHEX Payment Settings</b><br>(Accepts British Pound)</font></td>
    </tr>
	<tr>
      <td width="20%" bgcolor="#e6f2ea"><font face="Verdana" size="1">NOCHEX Email</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="nochex_email" size="29" value="<?php echo $nochex_email; ?>"></font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">NOCHEX Payment 
      APC URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="nochex_apc_url" size="50" value="<?php echo $nochex_apc_url; ?>"><br>Recommended: <b>http://<?php echo $host.$http_url."/payment/nochexAPC.php"; ?></b></font></td>
    </tr>
<!--
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Test Mode (Y/N)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
       <input type="radio" name="nochex_test" value="Y"  <?php if ($nochex_test=='Y') { echo " checked "; } ?> >Yes <br>
	  <input type="radio" name="nochex_test" value="N"  <?php if ($nochex_test=='N') { echo " checked "; } ?> >No<br></font></td>
    </tr>
-->	
	 
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">NOCHEX 
      Return URL (Employers)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="nochex_return_url" size="50" value="<?php echo $nochex_return_url; ?>"><br>(Recommended: <b>http://<?php echo $host.$http_url."/".JB_EMPLOYER_FOLDER."thanks.php?m=".$this->className; ?></b> )</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">NOCHEX 
      Return URL (Candidates)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="nochex_candidate_return_url" size="50" value="<?php echo $nochex_candidate_return_url; ?>"><br>(Recommended: <b>http://<?php echo $host.$http_url."/".JB_CANDIDATE_FOLDER."thanks.php?m=".$this->className; ?></b> )</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">NOCHEX 
      Cancelled Return URL (Employers)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="nochex_cancel_return_url" size="50" value="<?php echo $nochex_cancel_return_url; ?>"><br>(eg. http://<?php echo $host.$http_url."/".JB_EMPLOYER_FOLDER; ?>)</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">NOCHEX 
      Cancelled Return URL (Candidates)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="nochex_candidate_cancel_return_url" size="50" value="<?php echo $nochex_candidate_cancel_return_url; ?>"><br>(eg. http://<?php echo $host.$http_url."/".JB_CANDIDATE_FOLDER; ?>)</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Your  
      Custom Logo URL (optional)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="nochex_logo_url" size="50" value="<?php echo $nochex_logo_url; ?>"><br>(This should be on a HTTPS server. <b>Leave blank if you want no logo</b>. eg. https://www.example.com/images/mylogo.gif)</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">NOCHEX 
      Checkout button  URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="nochex_button_url" size="50" value="<?php echo $nochex_button_url; ?>"><br>(eg. http://support.nochex.com/web/images/cardsboth2.gif)</font></td>
    </tr>
	
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">NOCHEX Currency</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select  name="nochex_currency" ><option value="GBP">GBP</select></font></td>
    </tr>
	
	 <tr>
	
      <td  bgcolor="#e6f2ea" colspan=2><font face="Verdana" size="1"><input type="submit" value="Save">
	  </td>
	  </tr>
    
  </table>
  <input type="hidden" name="pay" value="<?php echo jb_escape_html($_REQUEST['pay']);?>">
  <input type="hidden" name="action" value="save">


		<?php

	}

	function save_config() {

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_EMAIL', '".jb_escape_sql($_REQUEST['nochex_email'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_LOGO_URL', '".jb_escape_sql($_REQUEST['nochex_logo_url'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_CANCEL_RETURN_URL', '".jb_escape_sql($_REQUEST['nochex_cancel_return_url'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_RETURN_URL', '".jb_escape_sql($_REQUEST['nochex_return_url'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_APC_URL', '".jb_escape_sql($_REQUEST['nochex_apc_url'])."')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_BUTTON_URL', '".jb_escape_sql($_REQUEST['nochex_button_url'])."')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_TEST', '".jb_escape_sql($_REQUEST['nochex_test'])."')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_CANDIDATE_CANCEL_RETURN_URL', '".jb_escape_sql($_REQUEST['nochex_candidate_cancel_return_url'])."')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_CANDIDATE_RETURN_URL', '".jb_escape_sql($_REQUEST['nochex_candidate_return_url'])."')";
		JB_mysql_query($sql);


	}

	// true or false
	function is_enabled() {

		$sql = "SELECT val from jb_config where `key`='NOCHEX_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		if ($row['val']=='Y') {
			return true;

		} else {
			return false;

		}

	}


	function is_installed() {

		$sql = "SELECT val from jb_config where `key`='NOCHEX_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);
		

		if (mysql_num_rows($result)>0) {
			return true;

		} else {
			return false;

		}

	}

	function enable() {

		$sql = "UPDATE jb_config set val='Y' where `key`='NOCHEX_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);


	}

	function disable() {

		$sql = "UPDATE jb_config set val='N' where `key`='NOCHEX_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);

	}

	// process_payment_return() - Called when thanks.php page is accessed after returning from payment gateway
	// - Place affiliate code here.
	// - You can access all the variables returned form the payment gatway through the $_POST array (If the payment gateway returned any variables)
	// - place any other functionality here.

	function process_payment_return() {

		global $label;

		if (strpos(NOCHEX_CANDIDATE_RETURN_URL, $_SERVER['PHP_SELF'])===false) {
			echo $label['employer_payment_processing'];
		} else {
			echo $label['candidate_payment_processing'];

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