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

$_PAYMENT_OBJECTS['egold'] = new egold;//"paypal";

//define ('IPN_LOGGING', 'Y');


function JB_eg_mail_error($msg) {

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


	@mail(JB_SITE_CONTACT_EMAIL, "Error message from ".JB_SITE_NAME." Jamit egold script. ", $msg, $headers);

}

function eg_log_entry ($entry_line) {

	if (IPN_LOGGING == 'Y') {

		JB_payment_log_entry_db($entry_line, 'egold');

	}


}






###########################################################################
# Payment Object



class egold {

	var $name="E-Gold";
	var $description = 'E-Gold';
	var $className="egold";
	

	function egold() {

		global $label;
		$this->description = $label['payment_egold_description'];
		$this->name= $label['payment_egold_name'];
		if ($this->is_installed()) {

			$sql = "SELECT * FROM jb_config where `key`='EGOLD_ENABLED' OR `key`='EGOLD_PAYMENT_UNITS' OR `key`='EGOLD_PAYEE_ACCOUNT' OR `key`='EGOLD_PAYMENT_METAL_ID' OR `key`='EGOLD_STATUS_URL' OR `key`='EGOLD_PAYMENT_URL' OR `key`='EGOLD_NOPAYMENT_URL' OR `key`='EGOLD_ALTERNATE_PASSPHRASE' OR `key`='EGOLD_CANDIDATE_PAYMENT_URL' OR `key`='EGOLD_CANDIDATE_NOPAYMENT_URL'";
			$result = JB_mysql_query($sql) or die (mysql_error().$sql);

			while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

				define ($row['key'], $row['val']);

			}

			// guess the EGOLD_CANDIDATE_NOPAYMENT_URL and EGOLD_CANDIDATE_PAYMENT_URL
			// these two constants were introduced in 2.9.0
			$host = $_SERVER['SERVER_NAME']; // hostname
			$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
			$http_url = explode ("/", $http_url);
			array_pop($http_url); // get rid of filename
			array_pop($http_url); // get rid of /admin
			$http_url = implode ("/", $http_url);


			if (!defined('EGOLD_CANDIDATE_PAYMENT_URL')) {

				$url = "http://".$host.$http_url."/".JB_CANDIDATE_FOLDER."thanks.php?m=".$this->className;
				define('EGOLD_CANDIDATE_PAYMENT_URL', $url);
			}

			if (!defined('EGOLD_CANDIDATE_NOPAYMENT_URL')) {

				$url = "http://".$host.$http_url."/".JB_CANDIDATE_FOLDER;
				define('EGOLD_CANDIDATE_NOPAYMENT_URL', $url);
			}

			

		}


	}

	var $egold_units = array(
		'USD' => '1',
		'CAD' => '2',
		'FRF' => '33',
		'CHF' => '41',
		'GBP' => '44',
		'DEM' => '49',
		'AUD' => '61',
		'JPY' => '81',
		'EUR' => '85',
		'BEF' => '86',
		'ATS' => '97',
		'GRD' => '88',
		'ESP' => '89',
		'IEP' => '90',
		'ITL' => '91',
		'LUF' => '92',
		'NLG' => '93',
		'PTE' => '94',
		'FIM' => '95',
		'g' => '8888',
		'oz' => '9999'
	);

	function egold_unit_to_currency ($unit_code) {
	
		$temp = array_flip($this->egold_units);
		return $temp[$unit_code];

	}

	function get_currency() {

		return $this->egold_unit_to_currency (EGOLD_PAYMENT_UNITS);

	}


	function install() {

		echo "Install E-gold..<br>";

		$host = $_SERVER['SERVER_NAME']; // hostname
		$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
		$http_url = explode ("/", $http_url);
		array_pop($http_url); // get rid of filename
		array_pop($http_url); // get rid of /admin
		$http_url = implode ("/", $http_url);

	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_ENABLED', 'N')";
		JB_mysql_query($sql);
		
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_PAYMENT_UNITS', 'USD')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_PAYEE_ACCOUNT', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_PAYMENT_METAL_ID', '1')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_ALTERNATE_PASSPHRASE', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_STATUS_URL', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_PAYMENT_URL', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_NOPAYMENT_URL', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_CANDIDATE_NOPAYMENT_URL', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_CANDIDATE_PAYMENT_URL', '')";
		JB_mysql_query($sql);
		
		
	}

	function uninstall() {

		echo "Uninstall egold..<br>";

	
		$sql = "DELETE FROM jb_config where `key`='EGOLD_ENABLED'";
		JB_mysql_query($sql);
		
		$sql = "DELETE FROM jb_config where `key`='EGOLD_PAYMENT_UNITS'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='EGOLD_PAYEE_ACCOUNT'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='EGOLD_STATUS_URL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='EGOLD_PAYMENT_URL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='EGOLD_NOPAYMENT_URL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='EGOLD_PAYMENT_METAL_ID'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='EGOLD_ALTERNATE_PASSPHRASE'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='EGOLD_CANDIDATE_NOPAYMENT_URL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='EGOLD_CANDIDATE_PAYMENT_URL'";
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

<form id="payment_button" action="https://www.e-gold.com/sci_asp/payments.asp" method="post" >
<input type="hidden" name="PAYEE_ACCOUNT" value="<?php echo EGOLD_PAYEE_ACCOUNT; ?>">
<input type="hidden" name="PAYEE_NAME" value="<?php echo JB_escape_html(JB_SITE_NAME); ?>">
<input type="hidden" name="PAYMENT_AMOUNT"  value="<?php echo JB_convert_to_currency($order_row['amount'], $order_row['currency_code'], $this->get_currency() ); ?>">
<input type="hidden" name="PAYMENT_UNITS" value="<?php echo EGOLD_PAYMENT_UNITS; ?>">
<input type="hidden" name="PAYMENT_METAL_ID" value="<?php echo EGOLD_PAYMENT_METAL_ID; ?>">
<input type="hidden" name="PAYMENT_ID" value="<?php echo jb_prefix_order_id($product_type.$order_row['invoice_id']) ?>">
<input type="hidden" name="STATUS_URL" value="<?php echo EGOLD_STATUS_URL; ?>">
<?php if ($order_row['user_type']=='C') { ?>
	<input type="hidden" name="PAYMENT_URL" value="<?php echo EGOLD_CANDIDATE_PAYMENT_URL; ?>">
<?php } else { ?>
	<input type="hidden" name="PAYMENT_URL" value="<?php echo EGOLD_PAYMENT_URL; ?>">
<?php } ?>
<input type="hidden" name="PAYMENT_URL_METHOD" value="POST">
<?php if ($order_row['user_type']=='C') { ?>
	<input type="hidden" name="NOPAYMENT_URL" value="<?php echo EGOLD_CANDIDATE_NOPAYMENT_URL; ?>">
<?php } else { ?>
	<input type="hidden" name="NOPAYMENT_URL" value="<?php echo EGOLD_NOPAYMENT_URL; ?>">
<?php } ?>
<input type="hidden" name="NOPAYMENT_URL_METHOD" value="POST">
<input type="hidden" name="BAGGAGE_FIELDS" value="ORDER_NUM CUST_NUM">

<input type="hidden" name="ORDER_NUM" value="<?php echo $order_row['invoice_id'];?>">
<input type="hidden" name="CUST_NUM" value="<?php  if ($product_type=='M') {  echo $order_row['user_id']; } else { echo $order_row['employer_id']; } ?>">
<center>
<input type="submit" value="<?php echo $label['pay_by_egold_button'];?>">
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

			$egold_payee_account = $_REQUEST['egold_payee_account'];
			$egold_payment_units = $_REQUEST['egold_payment_units'];
			$egold_payment_metal_id = $_REQUEST['egold_payment_metal_id'];
			$egold_status_url = $_REQUEST['egold_status_url'];
			$egold_payment_url = $_REQUEST['egold_payment_url'];
			$egold_nopayment_url = $_REQUEST['egold_nopayment_url'];
			$egold_alternate_passphrase = $_REQUEST['egold_alternate_passphrase'];
			$egold_candidate_nopayment_url = $_REQUEST['egold_candidate_nopayment_url'];
			$egold_candidate_payment_url = $_REQUEST['egold_candidate_payment_url'];
			
		} else {

			$egold_payee_account = EGOLD_PAYEE_ACCOUNT;
			$egold_payment_units = EGOLD_PAYMENT_UNITS;
			$egold_payment_metal_id = EGOLD_PAYMENT_METAL_ID;
			$egold_status_url = EGOLD_STATUS_URL;
			$egold_payment_url = EGOLD_PAYMENT_URL;
			$egold_nopayment_url = EGOLD_NOPAYMENT_URL;
			$egold_alternate_passphrase = EGOLD_ALTERNATE_PASSPHRASE;
			$egold_candidate_nopayment_url = EGOLD_CANDIDATE_NOPAYMENT_URL;
			$egold_candidate_payment_url = EGOLD_CANDIDATE_PAYMENT_URL;
		
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
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Egold 
      Payee Account</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="egold_payee_account" size="33" value="<?php echo $egold_payee_account; ?>"></font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Egold  
      Payment Units</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select name="egold_payment_units"  value="<?php echo $egold_payment_units; ?>"> 
	  <option value="1" <?php if ($egold_payment_units=='1') { echo ' selected ';}  ?> >USD</option>
	  <option value="2" <?php if ($egold_payment_units=='2') { echo ' selected ';}  ?> >CAD</option>
	  <option value="44" <?php if ($egold_payment_units=='44') { echo ' selected ';}  ?> >GBP</option>
	  <option value="61" <?php if ($egold_payment_units=='61') { echo ' selected ';}  ?> >AUD</option>
	  <option value="81" <?php if ($egold_payment_units=='81') { echo ' selected ';}  ?> >JPY</option>
	  <option value="85" <?php if ($egold_payment_units=='44') { echo ' selected ';}  ?> >EUR</option>
	  <option value="8888" <?php if ($egold_payment_units=='8888') { echo ' selected ';}  ?> >Gram (g)</option>
	  <option value="9999" <?php if ($egold_payment_units=='9999') { echo ' selected ';}  ?> >Troy ounce (oz)</option>
	 
	  </select> 
	  </font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Egold  
      Payment Metal</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select name="egold_payment_metal_id"  value="<?php echo $egold_payment_metal_id; ?>"> 
	  <option value="0" <?php if ($egold_payment_metal_id=='0') { echo ' selected ';}  ?> >Buyer's Choice</option>
	  <option value="1" <?php if ($egold_payment_metal_id=='1') { echo ' selected ';}  ?> >Gold</option>
	  <option value="2" <?php if ($egold_payment_metal_id=='2') { echo ' selected ';}  ?> >Silver</option>
	  <option value="3" <?php if ($egold_payment_metal_id=='3') { echo ' selected ';}  ?> >Platinum</option>
	  <option value="4" <?php if ($egold_payment_metal_id=='4') { echo ' selected ';}  ?> >Palladium</option>
	 
	  </select> 
	  </font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">E-Gold 
      Status URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="egold_status_url" size="50" value="<?php echo $egold_status_url; ?>"><br>(recommended: <b>http://<?php echo $host.$http_url."/payment/egold.php"; ?></b> or enter your email address, like this: <b>mailto:<?php echo JB_SITE_CONTACT_EMAIL; ?></b> )</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">E-Gold 
      Payment URL (Employers)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="egold_payment_url" size="33" value="<?php echo $egold_payment_url; ?>"> I.e. 'Thank you page', (recommended: <b>http://<?php echo $host.$http_url."/".JB_EMPLOYER_FOLDER."thanks.php?m=".$this->className; ?></b> ) </font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">E-Gold 
      Payment URL (Candidates)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="egold_candidate_payment_url" size="33" value="<?php echo $egold_candidate_payment_url; ?>"> I.e. 'Thank you page', (recommended: <b>http://<?php echo $host.$http_url."/".JB_CANDIDATE_FOLDER."thanks.php?m=".$this->className; ?></b> ) </font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">E-Gold 
      No payment URL (Employers)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="egold_nopayment_url" size="33" value="<?php echo $egold_nopayment_url; ?>"> I.e. 'Payment cancelled page', (recommended: <b>http://<?php echo $host.$http_url."/".JB_EMPLOYER_FOLDER; ?>)</b>  </font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">E-Gold 
       No payment URL (Candidates)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="egold_candidate_nopayment_url" size="33" value="<?php echo $egold_nopayment_url; ?>"> I.e. 'Payment cancelled page', (recommended: <b>http://<?php echo $host.$http_url."/".JB_CANDIDATE_FOLDER; ?>)</b>  </font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Egold 
      Alternate Passphrase</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="egold_alternate_passphrase" size="33" value="<?php echo $egold_alternate_passphrase; ?>"> (You must set this in your e-gold account. Go to Account Info -> Passphrase -> and enter your 'New Alternate Passphrase' there.) </font></td>
    </tr>
	
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

		$code = $this->egold_unit_to_currency ($_REQUEST['egold_payment_units']);
				
		$rate = JB_get_currency_rate($code);

		if ($rate=='') {

			echo "<font color='red'><b>Note: The selected 'Egold payment unit' is not defined in the system. Please add define this as a currency in the 'Currencies' section or select another payment unit.</b></font>";

		}

	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_PAYEE_ACCOUNT', '".jb_escape_sql($_REQUEST['egold_payee_account'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_PAYMENT_UNITS', '".jb_escape_sql($_REQUEST['egold_payment_units'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_PAYMENT_METAL_ID', '".jb_escape_sql($_REQUEST['egold_payment_metal_id'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_STATUS_URL', '".jb_escape_sql($_REQUEST['egold_status_url'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_PAYMENT_URL', '".jb_escape_sql($_REQUEST['egold_payment_url'])."')";
		JB_mysql_query($sql);	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_NOPAYMENT_URL', '".jb_escape_sql($_REQUEST['egold_nopayment_url'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_ALTERNATE_PASSPHRASE', '".jb_escape_sql($_REQUEST['egold_alternate_passphrase'])."')";
		JB_mysql_query($sql);	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_CANDIDATE_NOPAYMENT_URL', '".jb_escape_sql($_REQUEST['egold_candidate_nopayment_url'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('EGOLD_CANDIDATE_PAYMENT_URL', '".jb_escape_sql($_REQUEST['egold_candidate_payment_url'])."')";
		JB_mysql_query($sql);	

	}

	// true or false
	function is_enabled() {

		$sql = "SELECT val from jb_config where `key`='EGOLD_ENABLED' ";
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

		$sql = "SELECT val from jb_config where `key`='EGOLD_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);
		

		if (mysql_num_rows($result)>0) {
			return true;

		} else {
			return false;

		}

	}

	function enable() {

		$sql = "UPDATE jb_config set val='Y' where `key`='EGOLD_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);


	}

	function disable() {

		$sql = "UPDATE jb_config set val='N' where `key`='EGOLD_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);

	}

	// process_payment_return() - Called when thanks.php page is accessed after returning from payment gateway
	// - Place affiliate code here.
	// - You can access all the variables returned form the payment gatway through the $_POST array (If the payment gateway returned any variables)
	// - place any other functionality here.

	function process_payment_return() {

		global $label;

		if ($_POST['PAYMENT_ID']!='') {

			foreach ($_POST as $key => $value) {
				$req .= "&$key=$value";
			}
			eg_log_entry('e-gold: '.$req);
			
			$alt_hash = strtoupper (md5(EGOLD_ALTERNATE_PASSPHRASE));


			$hash = strtoupper (md5 ($_POST['PAYMENT_ID'].":".$_POST['PAYEE_ACCOUNT'].":".$_POST['PAYMENT_AMOUNT'].":".$_POST['PAYMENT_UNITS'].":".$_POST['PAYMENT_METAL_ID'].":".$_POST['PAYMENT_BATCH_NUM'].":".$_POST['PAYER_ACCOUNT'].":".$alt_hash.":".$_POST['ACTUAL_PAYMENT_OUNCES'].":".$_POST['USD_PER_OUNCE'].":".$_POST['FEEWEIGHT'].":".$_POST['TIMESTAMPGMT']));

			

			$invoice_id = jb_strip_order_id ($_POST['PAYMENT_ID']);
			$product_type = substr($invoice_id, 0, 1);// 'P' or 'S' or 'M'
			$invoice_id = substr($invoice_id, 1);


			if ($hash == $_POST['HANDSHAKE_HASH']) {
				
				JBPLUG_do_callback('pay_trn_verification_passed', $invoice_id, $product_type);

				if ($product_type=='P') {
					JB_complete_package_invoice($invoice_id, 'e-gold');
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
					JB_complete_subscription_invoice($invoice_id, 'e-gold');
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

					JB_complete_membership_invoice($invoice_id, 'e-gold');
					?>
					<center>

					<img src="<?php echo JB_SITE_LOGO_URL; ?>">
					<p><?php echo $label['payment_membership_completed']; ?></p>
					</center>
					<?php

				}
				JBPLUG_do_callback('pay_trn_completed', $invoice_id, $product_type);
				JB_debit_transaction($invoice_id, $_POST['PAYMENT_AMOUNT'], 'USD', $_POST['PAYMENT_ID'], $reason, 'e-gold', $product_type);

			} else {
				JBPLUG_do_callback('pay_trn_verification_failed', $invoice_id, $product_type);
				echo "Invalid signiture. Please contact the webmaster.";


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