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

$_PAYMENT_OBJECTS['ccAvenue'] = new ccAvenue;//"paypal";

define ('IPN_LOGGING', 'Y');


function JB_cc_mail_error($msg) {

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

	@mail(JB_SITE_CONTACT_EMAIL, "Error message from ".JB_SITE_NAME." Jamit ccAvenue script. ", $msg, $headers);

}

function cc_log_entry ($entry_line) {

	if (IPN_LOGGING == 'Y') {
		JB_payment_log_entry_db($entry_line, 'ccAvenue');
	}
}


function cc_getchecksum($MerchantId,$Amount,$OrderId ,$URL,$WorkingKey)
{
	$str ="$MerchantId|$OrderId|$Amount|$URL|$WorkingKey";
	$adler = 1;
	$adler = cc_adler32($adler,$str);
	return $adler;
}

function cc_verifychecksum($MerchantId,$OrderId,$Amount,$AuthDesc,$CheckSum,$WorkingKey)
{
	$str = "$MerchantId|$OrderId|$Amount|$AuthDesc|$WorkingKey";
	$adler = 1;
	$adler = cc_adler32($adler,$str);
	
	if($adler == $CheckSum)
		return "true" ;
	else
		return "false" ;
}

function cc_adler32($adler , $str)
{
	$BASE =  65521 ;

	$s1 = $adler & 0xffff ;
	$s2 = ($adler >> 16) & 0xffff;
	for($i = 0 ; $i < strlen($str) ; $i++)
	{
		$s1 = ($s1 + Ord($str[$i])) % $BASE ;
		$s2 = ($s2 + $s1) % $BASE ;
			//echo "s1 : $s1 <BR> s2 : $s2 <BR>";

	}
	return cc_leftshift($s2 , 16) + $s1;
}

function cc_leftshift($str , $num)
{

	$str = DecBin($str);

	for( $i = 0 ; $i < (64 - strlen($str)) ; $i++)
		$str = "0".$str ;

	for($i = 0 ; $i < $num ; $i++) 
	{
		$str = $str."0";
		$str = substr($str , 1 ) ;
		//echo "str : $str <BR>";
	}
	return cc_cdec($str) ;
}

function cc_cdec($num)
{

	for ($n = 0 ; $n < strlen($num) ; $n++)
	{
	   $temp = $num[$n] ;
	   $dec =  $dec + $temp*pow(2 , strlen($num) - $n - 1);
	}

	return $dec;
}


#####################################################################################


###########################################################################
# Payment Object



class ccAvenue {

	var $name="ccAvenue";
	var $description="ccAvenue Secure Credit Card Payment";
	var $className="ccAvenue";
	

	function ccAvenue() {

		if ($this->is_installed()) {

			global $label;
			$this->name=$label['payment_ccavenue_name'];
			$this->description=$label['payment_ccavenue_descr'];


			$sql = "SELECT * FROM jb_config where `key`='CCAVENUE_ENABLED' OR `key`='CCAVENUE_CURRENCY' OR `key`='CCAVENUE_MERCHANT_ID' OR `key`='CCAVENUE_REDIRECT_URL' OR `key`='CCAVENUE_CANDIDATE_REDIRECT_URL' OR `key`='CCAVENUE_WORKING_KEY'";
			$result = JB_mysql_query($sql) or die (mysql_error().$sql);

			while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

				define ($row['key'], $row['val']);

			}

			// guess the CCAVENUE_CANDIDATE_REDIRECT_URL 
			// these two constants were introduced in 2.9.0
			$host = $_SERVER['SERVER_NAME']; // hostname
			$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
			$http_url = explode ("/", $http_url);
			array_pop($http_url); // get rid of filename
			array_pop($http_url); // get rid of /admin
			$http_url = implode ("/", $http_url);

			if (!defined('CCAVENUE_CANDIDATE_REDIRECT_URL')) {

				$url = "http://".$host.$http_url."/".JB_CANDIDATE_FOLDER."thanks.php?m=".$this->className;
				define('CCAVENUE_CANDIDATE_REDIRECT_URL', $url);
			}

		}


	}

	function get_currency() {

		return CCAVENUE_CURRENCY;

	}


	function install() {

		echo "Installed ccAvenue..<br>";

		$host = $_SERVER['SERVER_NAME']; // hostname
		$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
		$http_url = explode ("/", $http_url);
		array_pop($http_url); // get rid of filename
		array_pop($http_url); // get rid of /admin
		$http_url = implode ("/", $http_url);

	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CCAVENUE_ENABLED', 'N')";
		JB_mysql_query($sql);
		
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CCAVENUE_CURRENCY', 'USD')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CCAVENUE_MERCHANT_ID', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CCAVENUE_REDIRECT_URL', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CCAVENUE_WORKING_KEY', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CCAVENUE_CANDIDATE_REDIRECT_URL', '')";
		JB_mysql_query($sql);
		
		
	}

	function uninstall() {

		echo "Uninstalled CC Avenue..<br>";

	
		$sql = "DELETE FROM jb_config where `key`='CCAVENUE_ENABLED'";
		JB_mysql_query($sql);
		
		$sql = "DELETE FROM jb_config where `key`='CCAVENUE_CURRENCY'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='CCAVENUE_MERCHANT_ID'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='CCAVENUE_REDIRECT_URL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='CCAVENUE_WORKING_KEY'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='CCAVENUE_CANDIDATE_REDIRECT_URL'";
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
		

		$Checksum = cc_getCheckSum(CCAVENUE_MERCHANT_ID, JB_convert_to_currency($order_row['amount'], $order_row['currency_code'], CCAVENUE_CURRENCY), jb_prefix_order_id($product_type.$order_row['invoice_id']) , CCAVENUE_REDIRECT_URL, CCAVENUE_WORKING_KEY);

		?>

		<form id="payment_button" method="post" action="https://www.ccavenue.com/shopzone/cc_details.jsp">
		<input type=hidden name=Merchant_Id value="<?php echo CCAVENUE_MERCHANT_ID; ?>">
		<input type=hidden name=Amount value="<?php echo JB_convert_to_currency($order_row['amount'], $order_row['currency_code'], CCAVENUE_CURRENCY); ?>">
		<input type=hidden name=Order_Id value="<?php echo jb_prefix_order_id($product_type.$order_row['invoice_id']);?>">
		<!--<input type=hidden name=ActionID value="TXN">-->
		<?php if ($order_row['user_type']=='C') { ?>
		
			<input type='hidden' name='Redirect_Url' value="<?php echo CCAVENUE_CANDIDATE_REDIRECT_URL; ?>">
		<?php } else { ?>
			<input type=hidden name=Redirect_Url value="<?php echo CCAVENUE_REDIRECT_URL; ?>">
		<?php } ?>

		<input type=hidden name=Checksum value="<?php echo $Checksum; ?>">
		
		<input type="hidden" name="Merchant_Param" value="<?php echo $order_id; ?>"> 
		<center>
		<INPUT TYPE="submit" value="<?php echo $label['pay_by_ccavenue_button'];?>">
		</center>
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

			$ccavenue_merchant_id = $_REQUEST['ccavenue_merchant_id'];
			$ccavenue_currency = $_REQUEST['ccavenue_currency'];
			$ccavenue_redirect_url = $_REQUEST['ccavenue_redirect_url'];
			$ccavenue_working_key = $_REQUEST['ccavenue_working_key'];
			$ccavenue_candidate_redirect_url = $_REQUEST['ccavenue_candidate_redirect_url'];

		} else {

			$ccavenue_merchant_id = CCAVENUE_MERCHANT_ID;
			$ccavenue_currency = CCAVENUE_CURRENCY;
			$ccavenue_redirect_url = CCAVENUE_REDIRECT_URL;
			$ccavenue_working_key = CCAVENUE_WORKING_KEY;
			$ccavenue_candidate_redirect_url = CCAVENUE_CANDIDATE_REDIRECT_URL;

		}

		$host = $_SERVER['SERVER_NAME']; // hostname
		$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
		$http_url = explode ("/", $http_url);
		array_pop($http_url); // get rid of filename
		array_pop($http_url); // get rid of /admin
		$http_url = implode ("/", $http_url);

		?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
		<table border="0" cellpadding="5" cellspacing="2" style="border-style:groove"  width="100%" bgcolor="#FFFFFF">
    <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">CCAvenue 
      Merchant ID</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="ccavenue_merchant_id" size="33" value="<?php echo $ccavenue_merchant_id; ?>"></font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">CC Avenue 
      Currency</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select name="ccavenue_currency"  value="<?php echo $ccavenue_currency; ?>"> 
	  <?php JB_currency_option_list ($ccavenue_currency); ?>
	  </select>(Please select a currency that is supported by CCAvenue, ie. USD)
	  </font></td>
    </tr>
	
	 
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">CC Avenue 
      Redirect URL (Employers)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="ccavenue_redirect_url" size="50" value="<?php echo $ccavenue_redirect_url; ?>"><br>(recommended: <b>http://<?php echo $host.$http_url."/".JB_EMPLOYER_FOLDER."thanks.php?m=".$this->className; ?></b> )</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">CC Avenue 
      Redirect URL (Candidates)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="ccavenue_candidate_redirect_url" size="50" value="<?php echo $ccavenue_candidate_redirect_url; ?>"><br>(recommended: <b>http://<?php echo $host.$http_url."/".JB_CANDIDATE_FOLDER."thanks.php?m=".$this->className; ?></b> )</font></td>
    </tr>
	
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">CC Avenue 
      Working Key</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="ccavenue_working_key" size="50" value="<?php echo $ccavenue_working_key; ?>"><br>(This is set in your ccavenue account)</font></td>
    </tr>
	<!--
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">CC Avenue 
      Button Image URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_button_url" size="50" value="<?php echo $paypal_button_url; ?>"><br></font></td>
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

	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CCAVENUE_MERCHANT_ID', '".jb_escape_sql($_REQUEST['ccavenue_merchant_id'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CCAVENUE_CURRENCY', '".jb_escape_sql($_REQUEST['ccavenue_currency'])."')";
		JB_mysql_query($sql);
		
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CCAVENUE_REDIRECT_URL', '".jb_escape_sql($_REQUEST['ccavenue_redirect_url'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CCAVENUE_WORKING_KEY', '".jb_escape_sql($_REQUEST['ccavenue_working_key'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CCAVENUE_CANDIDATE_REDIRECT_URL', '".jb_escape_sql($_REQUEST['ccavenue_candidate_redirect_url'])."')";
		JB_mysql_query($sql);
		

	}

	// true or false
	function is_enabled() {

		$sql = "SELECT val from jb_config where `key`='CCAVENUE_ENABLED' ";
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

		$sql = "SELECT val from jb_config where `key`='CCAVENUE_ENABLED' ";
		$result = JB_mysql_query($sql);
		

		if (mysql_num_rows($result)>0) {
			return true;

		} else {
			return false;

		}

	}

	function enable() {

		$sql = "UPDATE jb_config set val='Y' where `key`='CCAVENUE_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);


	}

	function disable() {

		$sql = "UPDATE jb_config set val='N' where `key`='CCAVENUE_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);

	}

	// process_payment_return() - Called when thanks.php page is accessed after returning from payment gateway
	// - Place affiliate code here.
	// - You can access all the variables returned form the payment gatway through the $_POST array (If the payment gateway returned any variables)
	// - place any other functionality here.

	function process_payment_return() {

		global $label;

		if ($_POST['Merchant_Id']!='') { 

			foreach ($_POST as $key => $value) {
				$req .= "&$key=$value";
			}
			cc_log_entry('ccAvenue:'.$req);
			
			$Checksum = cc_verifychecksum($_POST['Merchant_Id'], $_POST['Order_Id'] , $_POST['Amount'], $_POST['AuthDesc'], $_POST['Checksum'], CCAVENUE_WORKING_KEY);

			$label['payment_ccave_go_back'] = str_replace ("%ADV_LINK%", JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER, $label['payment_ccave_go_back']);
			
			$invoice_id = jb_strip_order_id($_POST['Order_Id']);
			$product_type = substr($invoice_id, 0, 1);// 'P' or 'S'
			$invoice_id = substr($invoice_id, 1);

			if ($Checksum=="true") {
				JBPLUG_do_callback('pay_trn_verification_passed', $invoice_id, $product_type);
			} else {
				JBPLUG_do_callback('pay_trn_verification_failed', $invoice_id, $product_type);
			}

			if($Checksum=="true" && $_POST['AuthDesc']=="Y") {
				
				if ($product_type=='P') {
					JB_complete_package_invoice($invoice_id, 'ccAvenue');
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
					JB_complete_subscription_invoice($invoice_id, 'ccAvenue');
					?>
					<center>

					<img src="<?php echo JB_SITE_LOGO_URL; ?>">
					<?php
					$label['payment_subscription_completed2'] = str_replace("%URL%", JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER, $label['payment_subscription_completed2']);	
					?>
					<p><?php echo $label['payment_subscription_completed2']; ?></p>
					</center>
					<?php
				}  elseif ($product_type=='M') {

					JB_complete_membership_invoice($invoice_id, 'ccAvenue');
					?>
					<center>

					<img src="<?php echo JB_SITE_LOGO_URL; ?>">
					<p><?php echo $label['payment_membership_completed']; ?></p>
					</center>
					<?php

				}

				JBPLUG_do_callback('pay_trn_completed', $invoice_id, $product_type);

				JB_debit_transaction($invoice_id, $_POST['Amount'], CCAVENUE_CURRENCY, $_POST['Order_Id'], $reason, 'ccAvenue', $product_type);
				
			
				//Here you need to put in the routines for a successful 
				//transaction such as sending an email to customer,
				//setting database status, informing logistics etc etc
			}
			else if($Checksum=="true" && $_POST['AuthDesc']=="B")
			{

				if ($product_type=='P') {
					JB_pend_package_invoice($invoice_id, 'ccAvenue', $_POST['AuthDesc']);
				} elseif ($product_type=='S') {
					JB_pend_subscription_invoice($invoice_id, 'ccAvenue', $_POST['AuthDesc']);
				} elseif ($product_type=='M') {
					JB_pend_membership_invoice($invoice_id, 'ccAvenue', $_POST['AuthDesc']);
				}
				JBPLUG_do_callback('pay_trn_pending', $invoice_id, $product_type);
				$label['payment_return_pending'] = str_replace("%PAYMENT_GW%", $this->name, $label['payment_return_pending']);	

				?>
				<center>
				<img src="<?php echo JB_SITE_LOGO_URL; ?>">
				<p><?php echo $label['payment_return_pending']; ?></p>
				</center>
				<?php

		
				
				//Here you need to put in the routines/e-mail for a  "Batch Processing" order
				//This is only if payment for this transaction has been made by an American Express Card
				//since American Express authorisation status is available only after 5-6 hours by mail from ccavenue and at the "View Pending Orders"
			}
			else if($Checksum=="true" && $_POST['AuthDesc']=="N")
			{
				JBPLUG_do_callback('pay_trn_failed', $invoice_id, $product_type);
				$label['payment_return_denied'] = str_replace("%PAYMENT_GW%", $this->name, $label['payment_return_denied']);
				echo $label["payment_return_denied"];
				
				//Here you need to put in the routines for a failed
				//transaction such as sending an email to customer
				//setting database status etc etc

				?>


				<?php
			}
			else
			{
				echo "<br>Security Error. Illegal access detected";
				
				//Here you need to simply ignore this and dont need
				//to perform any operation in this condition
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



?>