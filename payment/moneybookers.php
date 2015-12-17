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

$_PAYMENT_OBJECTS['moneybookers'] = new moneybookers;//"paypal";

define ('IPN_LOGGING', 'Y');


function JB_mb_mail_error($msg) {

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

function mb_log_entry ($entry_line) {

	if (IPN_LOGGING == 'Y') {

		JB_payment_log_entry_db($entry_line, 'moneybookers');

	}


}


###########################################################################
# Payment Object



class moneybookers {

	var $name="moneybookers.com";
	var $description = 'moneybookers.com - Visa & MasterCard payments';
	var $className="moneybookers";
	

	function moneybookers() {

		global $label;
		$this->description = $label['payment_moneybookers_description'];
		$this->name = $label['payment_moneybookers_name'];
		if ($this->is_installed()) {

			$sql = "SELECT * FROM jb_config where `key`='MONEYBOOKERS_ENABLED' OR `key`='MONEYBOOKERS_CURRENCY' OR `key`='MONEYBOOKERS_EMAIL' OR `key`='MONEYBOOKERS_STATUS_URL' OR `key`='MONEYBOOKERS_RETURN_URL' OR `key`='MONEYBOOKERS_CANCEL_URL' OR `key`='MONEYBOOKERS_SECRET_WORD' OR `key`='MONEYBOOKERS_LANGUAGE' OR `key`='MONEYBOOKERS_CANDIDATE_RETURN_URL' OR `key`='MONEYBOOKERS_CANDIDATE_CANCEL_URL' ";
			$result = JB_mysql_query($sql) or die (mysql_error().$sql);

			while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

				define ($row['key'], $row['val']);

			}

			// guess the MONEYBOOKERS_CANDIDATE_RETURN_URL and MONEYBOOKERS_CANDIDATE_CANCEL_URL
			// these two constants were introduced in 2.9.0
			$host = $_SERVER['SERVER_NAME']; // hostname
			$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
			$http_url = explode ("/", $http_url);
			array_pop($http_url); // get rid of filename
			array_pop($http_url); // get rid of /admin
			$http_url = implode ("/", $http_url);


			if (!defined('MONEYBOOKERS_CANDIDATE_RETURN_URL')) {
				$url = "http://".$host.$http_url."/".JB_CANDIDATE_FOLDER."thanks.php?m=".$this->className;
				define('MONEYBOOKERS_CANDIDATE_RETURN_URL', $url);
			}

			if (!defined('MONEYBOOKERS_CANDIDATE_CANCEL_URL')) {

				$url = "http://".$host.$http_url."/".JB_CANDIDATE_FOLDER;
				define('MONEYBOOKERS_CANDIDATE_CANCEL_URL', $url);
			}


			

		}


	}

	function get_currency() {

		return MONEYBOOKERS_CURRENCY;

	}


	function install() {

		echo "Install moneybookers..<br>";

		$host = $_SERVER['SERVER_NAME']; // hostname
		$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
		$http_url = explode ("/", $http_url);
		array_pop($http_url); // get rid of filename
		array_pop($http_url); // get rid of /admin
		$http_url = implode ("/", $http_url);

	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('MONEYBOOKERS_ENABLED', 'N')";
		JB_mysql_query($sql);
		
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('MONEYBOOKERS_CURRENCY', 'USD')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('MONEYBOOKERS_LANGUAGE', 'EN')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('MONEYBOOKERS_EMAIL', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('MONEYBOOKERS_STATUS_URL', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('MONEYBOOKERS_RETURN_URL', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('MONEYBOOKERS_CANCEL_URL', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('MONEYBOOKERS_SECRET_WORD', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('MONEYBOOKERS_CANDIDATE_RETURN_URL', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('MONEYBOOKERS_CANDIDATE_CANCEL_URL', '')";
		JB_mysql_query($sql);
		
	}

	function uninstall() {

		echo "Uninstall Moneybookers..<br>";

	
		$sql = "DELETE FROM jb_config where `key`='MONEYBOOKERS_ENABLED'";
		JB_mysql_query($sql);
		
		$sql = "DELETE FROM jb_config where `key`='MONEYBOOKERS_CURRENCY'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='MONEYBOOKERS_EMAIL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='MONEYBOOKERS_LANGUAGE'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='MONEYBOOKERS_STATUS_URL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='MONEYBOOKERS_RETURN_URL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='MONEYBOOKERS_CANCEL_URL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='MONEYBOOKERS_SECRET_WORD'";
		JB_mysql_query($sql);
		
		$sql = "DELETE FROM jb_config where `key`='MONEYBOOKERS_CANDIDATE_CANCEL_URL'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='MONEYBOOKERS_CANDIDATE_RETURN_URL'";
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

<form id="payment_button" action="https://www.moneybookers.com/app/payment.pl" method="post" >
<input type="hidden" name="pay_to_email" value="<?php echo MONEYBOOKERS_EMAIL; ?>">
<input type="hidden" name="status_url" value="<?php echo MONEYBOOKERS_STATUS_URL; ?>">
<input type="hidden" name="language" value="<?php echo MONEYBOOKERS_LANGUAGE; ?>">
<input type="hidden" name="transaction_id" value="<?php echo jb_prefix_order_id($product_type.$order_row['invoice_id']); ?>">
<input type="hidden" name="amount" value="<?php echo JB_convert_to_currency($order_row['amount'], $order_row['currency_code'], MONEYBOOKERS_CURRENCY); ?>">
<input type="hidden" name="currency" value="<?php echo MONEYBOOKERS_CURRENCY; ?>">
<?php if ($order_row['user_type']=='C') {?>
<input type="hidden" name="cancel_url" value="<?php echo MONEYBOOKERS_CANDIDATE_CANCEL_URL; ?>">
<?php } else { ?>
<input type="hidden" name="cancel_url" value="<?php echo MONEYBOOKERS_CANCEL_URL; ?>">
<?php }?>
<?php if ($order_row['user_type']=='C') {?>
<input type="hidden" name="return_url" value="<?php echo MONEYBOOKERS_CANDIDATE_RETURN_URL; ?>">
<?php } else { ?>
<input type="hidden" name="return_url" value="<?php echo MONEYBOOKERS_RETURN_URL; ?>">
<?php } ?>
<input type="hidden" name="detail1_description" value="<?php echo JB_escape_html(JB_SITE_NAME)." - ".htmlentities($order_row['item_name']); ?>">
<input type="hidden" name="detail1_text" value="<?php echo JB_escape_html(JB_SITE_NAME)." - ".htmlentities($order_row['item_name']); ?>">
<input type="submit" value="<?php echo $label['pay_by_moneybookers_button'];?>">
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

			$moneybookers_email = $_REQUEST['moneybookers_email'];
			$moneybookers_language = $_REQUEST['moneybookers_language'];
			$moneybookers_currency = $_REQUEST['moneybookers_currency'];
			$moneybookers_status_url = $_REQUEST['moneybookers_status_url'];
			$moneybookers_return_url = $_REQUEST['moneybookers_return_url'];
			$moneybookers_cancel_url = $_REQUEST['moneybookers_cancel_url'];
			$moneybookers_secret_word = $_REQUEST['moneybookers_secret_word'];
			$moneybookers_candidate_return_url = $_REQUEST['moneybookers_candidate_return_url'];
			$moneybookers_candidate_cancel_url = $_REQUEST['moneybookers_candidate_cancel_url'];

		} else {

			$moneybookers_email = MONEYBOOKERS_EMAIL;
			$moneybookers_language = MONEYBOOKERS_LANGUAGE;
			$moneybookers_currency = MONEYBOOKERS_CURRENCY;
			$moneybookers_status_url = MONEYBOOKERS_STATUS_URL;
			$moneybookers_return_url = MONEYBOOKERS_RETURN_URL;
			$moneybookers_cancel_url = MONEYBOOKERS_CANCEL_URL;
			$moneybookers_secret_word = MONEYBOOKERS_SECRET_WORD;
			$moneybookers_candidate_return_url = MONEYBOOKERS_CANDIDATE_RETURN_URL;
			$moneybookers_candidate_cancel_url = MONEYBOOKERS_CANDIDATE_CANCEL_URL;
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
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Moneybookers 
      Email</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="moneybookers_email" size="33" value="<?php echo $moneybookers_email; ?>"></font></td>
    </tr>



	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Moneybookers 
      Language</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select name="moneybookers_language"  > 
	 <option value="EN" <?php if ($moneybookers_language=='EN') { echo ' selected ';}  ?> >English</option>
	 <option value="DE" <?php if ($moneybookers_language=='DE') { echo ' selected ';}  ?>>German</option>
	 <option value="ES" <?php if ($moneybookers_language=='ES') { echo ' selected ';}  ?>>Spanish</option>
	 <option value="FR" <?php if ($moneybookers_language=='FR') { echo ' selected ';}  ?>>French</option>
	 <option value="IT" <?php if ($moneybookers_language=='IT') { echo ' selected ';}  ?>>Italian</option>
	 <option value="PL" <?php if ($moneybookers_language=='PL') { echo ' selected ';}  ?>>Polish</option>
	 <option value="RU" <?php if ($moneybookers_language=='RU') { echo ' selected ';}  ?>>Russian</option>
	 <option value="GR" <?php if ($moneybookers_language=='GR') { echo ' selected ';}  ?>>Greek</option>
	 <option value="RO" <?php if ($moneybookers_language=='RO') { echo ' selected ';}  ?>>Romanian</option>
	 <option value="TR" <?php if ($moneybookers_language=='TR') { echo ' selected ';}  ?>>Turkish</option>
	 <option value="CN" <?php if ($moneybookers_language=='CN') { echo ' selected ';}  ?>>Chinese</option>
	  </select> 
	  </font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Moneybookers 
      Currency</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select name="moneybookers_currency"  value="<?php echo $moneybookers_currency; ?>"> 
	  <?php JB_currency_option_list ($moneybookers_currency); ?>
	  </select>(Please select a currency that is supported by Moneybookers. If the currency is not on the list, you may add it under the Configuration section)
	  </font></td>
    </tr>
	
	 
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Moneybookers 
      Status URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="moneybookers_status_url" size="50" value="<?php echo $moneybookers_status_url; ?>"><br>(Recommended: <b>http://<?php echo $host.$http_url."/payment/moneybookers.php"; ?></b> or enter your email address.)</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Moneybookers 
      Return URL (Employers)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="moneybookers_return_url" size="50" value="<?php echo $moneybookers_return_url; ?>"> I.e. 'Thank you page', (Recommended: <b>http://<?php echo $host.$http_url."/".JB_EMPLOYER_FOLDER."thanks.php?m=".$this->className; ?></b> ) </font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Moneybookers 
      Return URL (Candidates)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="moneybookers_candidate_return_url" size="50" value="<?php echo $moneybookers_candidate_return_url; ?>"> I.e. 'Thank you page', (Recommended: <b>http://<?php echo $host.$http_url."/".JB_CANDIDATE_FOLDER."thanks.php?m=".$this->className; ?></b> ) </font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Moneybookers 
      Cancel URL (Employers)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="moneybookers_cancel_url" size="50" value="<?php echo $moneybookers_cancel_url; ?>">  (Recommended: <b>http://<?php echo $host.$http_url."/".JB_EMPLOYER_FOLDER; ?></b> ) </font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Moneybookers 
      Cancel URL (Candidate)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="moneybookers_candidate_cancel_url" size="50" value="<?php echo $moneybookers_candidate_cancel_url; ?>">  (Recommended: <b>http://<?php echo $host.$http_url."/".JB_CANDIDATE_FOLDER; ?></b> ) </font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Moneybookers secret word</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="moneybookers_secret_word" size="33" value="<?php echo $moneybookers_secret_word; ?>"><br>(Note: The secret word MUST be submitted in the 'profile' section in lowercase. If you insert uppercase symbols, they will automatically be converted to lower case. The only restriction on your secret word is the length which must not exceed 10 characters. Non-alphanumeric symbols can be used. If the secret word is not shown in your profile, please contact merchantservices@moneybookers.com)</font></td>
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

	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('MONEYBOOKERS_EMAIL', '".jb_escape_sql($_REQUEST['moneybookers_email'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('MONEYBOOKERS_LANGUAGE', '".jb_escape_sql($_REQUEST['moneybookers_language'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('MONEYBOOKERS_CURRENCY', '".jb_escape_sql($_REQUEST['moneybookers_currency'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('MONEYBOOKERS_STATUS_URL', '".jb_escape_sql($_REQUEST['moneybookers_status_url'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('MONEYBOOKERS_RETURN_URL', '".jb_escape_sql($_REQUEST['moneybookers_return_url'])."')";
		JB_mysql_query($sql);	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('MONEYBOOKERS_CANCEL_URL', '".jb_escape_sql($_REQUEST['moneybookers_cancel_url'])."')";
		JB_mysql_query($sql);	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('MONEYBOOKERS_SECRET_WORD', '".jb_escape_sql($_REQUEST['moneybookers_secret_word'])."')";
		JB_mysql_query($sql);	

	}

	// true or false
	function is_enabled() {

		$sql = "SELECT val from jb_config where `key`='MONEYBOOKERS_ENABLED' ";
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

		$sql = "SELECT val from jb_config where `key`='MONEYBOOKERS_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);
		

		if (mysql_num_rows($result)>0) {
			return true;

		} else {
			return false;

		}

	}

	function enable() {

		$sql = "UPDATE jb_config set val='Y' where `key`='MONEYBOOKERS_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);


	}

	function disable() {

		$sql = "UPDATE jb_config set val='N' where `key`='MONEYBOOKERS_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);

	}

	// process_payment_return() - Called when thanks.php page is accessed after returning from payment gateway
	// - Place affiliate code here.
	// - You can access all the variables returned form the payment gatway through the $_POST array (If the payment gateway returned any variables)
	// - place any other functionality here.

	function process_payment_return() {

		global $label;

		if ($_POST['merchant_id']!='') { 

			$merchant_id = $_POST['merchant_id'];
			$transaction_id = $_POST['transaction_id'];
			$secret = strtoupper (MONEYBOOKERS_SECRET_WORD);
			$mb_amount = $_POST['mb_amount'];
			$mb_currency = $_POST['currency'];
			$status = $_POST['status'];
			$md5sig = $_POST['md5sig'];
			$status = $_POST['Status'];

			foreach ($_POST as $key => $value) {
				$req .= "&$key=$value";
			}
			mb_log_entry('moneybookers: '.$req);

			$working_sig = strtoupper (md5($merchant_id.$transaction_id.$secret.$mb_amount.$mb_currency.$status));
			
			$invoice_id = jb_strip_order_id($transaction_id);
			$product_type = substr($invoice_id, 0, 1);// 'P' or 'S'
			$invoice_id = substr($invoice_id, 1);

			if ($working_sig == $md5sig) {

				JBPLUG_do_callback('pay_trn_verification_passed', $invoice_id, $product_type);

				switch ($status) {

					case "-2": // failed
						$label['payment_return_denied'] = str_replace("%PAYMENT_GW%", $this->name, $label['payment_return_denied']);	
						echo "<p align='center'> ".$label['payment_return_denied']."</p>";
						JBPLUG_do_callback('pay_trn_failed', $invoice_id, $product_type);
						break;
					case "2": // processed
						?>
						
						<?php
						if ($product_type=='P') {
							JB_complete_package_invoice($invoice_id, 'moneybookers.com');
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
							JB_complete_subscription_invoice($invoice_id, 'moneybookers.com');
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

							JB_complete_membership_invoice($invoice_id, 'moneybookers.com');
							?>
							<center>

							<img src="<?php echo JB_SITE_LOGO_URL; ?>">
							<p><?php echo $label['payment_membership_completed']; ?></p>
							</center>
							<?php

						}
						JBPLUG_do_callback('pay_trn_completed', $invoice_id, $product_type);
						JB_debit_transaction($transaction_id, $mb_amount, MONEYBOOKERS_CURRENCY, $_POST['transaction_id'], $reason, 'moneybookers.com', $product_type);
						break;
					case "1": // scheduled (wait for 2 or -2)
						break;
					case "0": // pending
					
						if ($product_type=='P') {
							JB_pend_package_invoice($invoice_id, 'moneybookers.com');
						} elseif ($product_type=='S') {
							JB_pend_subscription_invoice($invoice_id, 'moneybookers.com');
						}
						JBPLUG_do_callback('pay_trn_pending', $invoice_id, $product_type);
						$label['payment_return_pending'] = str_replace("%PAYMENT_GW%", $this->name, $label['payment_return_pending']);	
						?>
						<center>
						<img src="<?php echo JB_SITE_LOGO_URL; ?>">
						<p><?php echo $label['payment_return_pending']; ?></p>
						</center>
						<?php
						break;
					case "-1": // cancelled
						break;

				}


			} else {
				JBPLUG_do_callback('pay_trn_verification_failed', $invoice_id, $product_type);

				echo "Invalid signiture";


			}




		}

		?>
<!--
		<p align="center"> Your order is being processed. To get the status of your order, please go to the <a href="credits.php">Credits Page</a> or <a href="subscriptions.php">Subscription Page</a></p>
-->
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>

		<?php


	}

}



?>