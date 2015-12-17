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

$_PAYMENT_OBJECTS['bank'] =  new bank;
define ('IPN_LOGGING', 'Y');

function JB_b_mail_error($msg) {

	$date = date("D, j M Y H:i:s O"); 
	
	$headers = "From: ". JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Reply-To: ".JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Return-Path: ".JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "X-Mailer: PHP" ."\r\n";
	$headers .= "Date: $date" ."\r\n"; 
	$headers .= "X-Sender-IP: $REMOTE_ADDR" ."\r\n";

	@mail(JB_SITE_CONTACT_EMAIL, "Error message from ".JB_SITE_NAME." Jamit bank payment mod. ", $msg, $headers);

}

function b_log_entry ($entry_line) {

	if (IPN_LOGGING == 'Y') {

		JB_payment_log_entry_db($entry_line, 'bank');
	}


}



###########################################################################
# Payment Object


class bank {

	var $name="Bank";
	var $description="Wire Transfer - Funds transfer to a bank account.";
	var $className="bank";
	

	function bank() {

		global $label;
		$this->name=$label['payment_bank_name'];
		$this->description=$label['payment_bank_descr'];

		if ($this->is_installed()) {

			$sql = "SELECT * FROM jb_config where `key`='BANK_ENABLED' OR `key`='BANK_NAME' OR `key`='BANK_ADDRESS' OR `key`='BANK_ACCOUNT_NAME' or `key`='BANK_BRANCH_NUMBER' OR `key`='BANK_ACCOUNT_NUMBER' OR `key`='BANK_SWIFT' OR `key`='BANK_ENABLED' OR `key`='BANK_CURRENCY' OR  `key`='BANK_EMAIL_CONFIRM' OR `key`='BANK_ADVANCE_CREDIT' OR `key`='BANK_TAX_RATE' OR `key`='BANK_ADD_TAX' ";
			$result = JB_mysql_query($sql);

			while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {
				define ($row['key'], $row['val']);
			}

		}

	}

	function get_currency() {

		return BANK_CURRENCY;

	}


	function install() {

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_ENABLED', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_CURRENCY', 'USD')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_NAME', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_ADDRESS', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_ACCOUNT_NAME', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_ACCOUNT_NUMBER', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_BRANCH_NUMBER', '')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_SWIFT', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_EMAIL_CONFIRM', '')";
		JB_mysql_query($sql);
		
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_ADVANCE_CREDIT', 'N')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_TAX_RATE', '0.0')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_ADD_TAX', 'N')";
		JB_mysql_query($sql);

	

	}

	function uninstall() {

		$sql = "DELETE FROM jb_config where `key`='BANK_ENABLED'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='BANK_NAME'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='BANK_ADDRESS'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='BANK_ACCOUNT_NAME'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='BANK_ACCOUNT_NUMBER'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='BANK_SWIFT'";
		JB_mysql_query($sql);


		$sql = "DELETE FROM jb_config where `key`='BANK_CURRENCY'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='BANK_EMAIL_CONFIRM'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='BANK_ADVANCE_CREDIT'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='BANK_TAX_RATE'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='BANK_ADD_TAX'";
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

			$sql = "SELECT * from package_invoices where invoice_id='".jb_escape_sql($order_id)."'";

		} elseif ($product_type=='S') {
			$sql = "SELECT * from subscription_invoices where invoice_id='".jb_escape_sql($order_id)."'";

		} elseif ($product_type=='M') {
			$sql = "SELECT * from membership_invoices where invoice_id='".jb_escape_sql($order_id)."'";
		}
		$result = JB_mysql_query($sql);
		$order_row = mysql_fetch_array($result, MYSQL_ASSOC);

		if ($order_row['user_type'] == 'C' ) { // candidate
			$payment_link = JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER."thanks.php?m=".$this->className."&order_id=".jb_prefix_order_id($product_type.$order_row['invoice_id'])."&amp;nhezk5=3";

		} else {
			$payment_link = JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER."thanks.php?m=".$this->className."&order_id=".jb_prefix_order_id($product_type.$order_row['invoice_id'])."&amp;nhezk5=3";

		}
	
				
		?>
		<center>
		<form id="payment_button" method="post" action="<?php echo $payment_link; ?>">
		<input type="button" value="<?php echo $label['payment_bank_button']; ?>" onclick="window.location='<?php echo $payment_link ?>'">
		</form>
		<!-- automatically submit the payment button -->
		<script type="text/javascript">
			function js_submit_payment() {
			var form = document.getElementById('payment_button');
				  form.submit();
			  }
			  window.onload = js_submit_payment;
		</script>

		</center>

			

	<?php

	}

	function config_form() {

		if ($_REQUEST['action']=='save') {
		
			$bank_name = $_REQUEST['bank_name'];
			$bank_address = $_REQUEST['bank_address'];
			$bank_account_name = $_REQUEST['bank_account_name'];
			$bank_account_number = $_REQUEST['bank_account_number'];
			$bank_branch_number = $_REQUEST['bank_branch_number'];
			$bank_swift = $_REQUEST['bank_swift'];
			$bank_currency = $_REQUEST['bank_currency'];
			$bank_email_confirm = $_REQUEST['bank_email_confirm'];
			$bank_advance_credit = $_REQUEST['bank_advance_credit'];
			$bank_tax_rate = $_REQUEST['bank_tax_rate'];
			$bank_add_tax = $_REQUEST['bank_add_tax'];
			
		} else {
			$bank_name = BANK_NAME;
			$bank_address = BANK_ADDRESS;
			$bank_account_name = BANK_ACCOUNT_NAME;
			$bank_account_number = BANK_ACCOUNT_NUMBER;
			$bank_branch_number = BANK_BRANCH_NUMBER;
			$bank_swift = BANK_SWIFT;
			$bank_currency = BANK_CURRENCY;
			$bank_email_confirm = BANK_EMAIL_CONFIRM;
			$bank_advance_credit = BANK_ADVANCE_CREDIT;
			$bank_tax_rate = BANK_TAX_RATE;
			$bank_add_tax = BANK_ADD_TAX;
			
		
		}

		
		?>
<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
		 <table border="0" cellpadding="5" cellspacing="2" style="border-style:groove" width="100%" bgcolor="#FFFFFF">

		 
     <tr>
      <td colspan="2"  bgcolor="#e6f2ea">
      <font face="Verdana" size="1"><b>Bank Payment Settings</b><br>(If you leave any field field blank, then it will not show up on the checkout)</font></td>
    </tr>
    <tr>
      <td width="20%" bgcolor="#e6f2ea"><font face="Verdana" size="1">Bank Name</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="bank_name" size="29" value="<?php echo $bank_name; ?>"></font></td>
    </tr>
	 <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Bank Address</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="bank_address" size="29" value="<?php echo $bank_address; ?>"></font></td>
    </tr>
	 <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Bank Account Name</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="bank_account_name" size="29" value="<?php echo $bank_account_name; ?>"></font></td>
    </tr>
	
    <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Bank Account Number</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="bank_account_number" size="29" value="<?php echo $bank_account_number; ?>"></font></td>
    </tr>
    <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Bank Branch Number</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="bank_branch_number" size="29" value="<?php echo $bank_branch_number; ?>"></font></td>
    </tr>
	 <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">SWIFT Code</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="bank_swift" size="29" value="<?php echo $bank_swift; ?>"></font></td>
    </tr>
	
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Bank Account Currency</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select  name="bank_currency" ><?php echo JB_currency_option_list ($bank_currency); ?></select></font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Send confirmation email</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
       <input type="radio" name="bank_email_confirm" value="YES"  <?php if ($bank_email_confirm=='YES') { echo " checked "; } ?> >Yes - Send confirmation email (invoice) before payment is made<br>
	  <input type="radio" name="bank_email_confirm" value="NO"  <?php if ($bank_email_confirm=='NO') { echo " checked "; } ?> >No<br></font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Advance Credits</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
       <input type="radio" name="bank_advance_credit" value="YES"  <?php if ($bank_advance_credit=='YES') { echo " checked "; } ?> >Yes - credit the account with posting credits before payment is made<br>
	  <input type="radio" name="bank_advance_credit" value="NO"  <?php if ($bank_advance_credit=='NO') { echo " checked "; } ?> >No<br></font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Tax Rate</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="bank_tax_rate" size="3" value="<?php echo $bank_tax_rate; ?>"> (Optional... enter a decimal, eg 0.1<br>Use %INVOICE_TAX% on the bank email template which will display the tax amount)<br> <input type="checkbox" name="bank_add_tax" value="Y" <?php if ($bank_add_tax=='Y') { echo ' checked '; } ?> > Add the tax to the amount? Do not check if prices already include tax.</font></td>
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

	
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_NAME', '".jb_escape_sql($_REQUEST['bank_name'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_ADDRESS', '".jb_escape_sql($_REQUEST['bank_address'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_ACCOUNT_NAME', '".jb_escape_sql($_REQUEST['bank_account_name'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_BRANCH_NUMBER', '".jb_escape_sql($_REQUEST['bank_branch_number'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_ACCOUNT_NUMBER', '".jb_escape_sql($_REQUEST['bank_account_number'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_CURRENCY', '".jb_escape_sql($_REQUEST['bank_currency'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_SWIFT', '".jb_escape_sql($_REQUEST['bank_swift'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_EMAIL_CONFIRM', '".jb_escape_sql($_REQUEST['bank_email_confirm'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_ADVANCE_CREDIT', '".jb_escape_sql($_REQUEST['bank_advance_credit'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_TAX_RATE', '".jb_escape_sql($_REQUEST['bank_tax_rate'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_ADD_TAX', '".jb_escape_sql($_REQUEST['bank_add_tax'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);

		

		


	}

	// true or false
	function is_enabled() {

		$sql = "SELECT val from jb_config where `key`='BANK_ENABLED' ";
		$result = JB_mysql_query($sql);
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		if ($row['val']=='Y') {
			return true;

		} else {
			return false;

		}

	}


	function is_installed() {

		$sql = "SELECT val from jb_config where `key`='BANK_ENABLED' ";
		$result = JB_mysql_query($sql);
		

		if (mysql_num_rows($result)>0) {
			return true;

		} else {
			return false;

		}

	}

	function enable() {

		$sql = "UPDATE jb_config set val='Y' where `key`='BANK_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);


	}

	function disable() {

		$sql = "UPDATE jb_config set val='N' where `key`='BANK_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);

	}

	// process_payment_return() - Called when thanks.php page is accessed after returning from payment gateway
	// - Place affiliate code here.
	// - You can access all the variables returned form the payment gatway through the $_POST array (If the payment gateway returned any variables)
	// - place any other functionality here.

	function process_payment_return() {

		global $label;

		if (($_REQUEST['order_id']!='') && ($_REQUEST['nhezk5']!='')) {

			$invoice_id = jb_strip_order_id ($_REQUEST['order_id']);
			$product_type = substr($invoice_id, 0, 1);// 'P' or 'S'
			$invoice_id = substr($invoice_id, 1);


			if ($_SESSION['JB_ID']=='') {

				echo "Error: You must be logged in to view this page";
				JBPLUG_do_callback('pay_trn_verification_failed', $invoice_id, $product_type);

			} else {

				JBPLUG_do_callback('pay_trn_verification_passed', $invoice_id, $product_type);


				?>
				
				<div style='background-color: #ffffff; border-color:#C0C0C0; border-style:solid;padding:10px'>
				<p align="center"><center>
				<?php

				if ($product_type=='P') {
					$sql = "SELECT * from package_invoices where invoice_id='".jb_escape_sql($invoice_id)."' and employer_id='".jb_escape_sql($_SESSION['JB_ID'])."'";
				} elseif ($product_type=='S') {
					$sql = "SELECT * from subscription_invoices where invoice_id='".jb_escape_sql($invoice_id)."' and employer_id='".jb_escape_sql($_SESSION['JB_ID'])."'";
				} elseif ($product_type=='M') {
					$sql = "SELECT * from membership_invoices where invoice_id='".jb_escape_sql($invoice_id)."' and user_id='".jb_escape_sql($_SESSION['JB_ID'])."'";
				}

				

				$result = JB_mysql_query($sql) or die(mysql_error().$sql);
				$order_row = mysql_fetch_array($result, MYSQL_ASSOC);

				if (BANK_TAX_RATE>0) {
					
					if (BANK_ADD_TAX!='Y') {
						// taxes included in prices
						// work out the tax paid in the amount
						$tax = $order_row['amount'] - ($order_row['amount'] / (1.00 + BANK_TAX_RATE));
					} else {
						$tax = $order_row['amount'] * BANK_TAX_RATE;
						$order_row['amount'] = $order_row['amount'] + $tax;

						// update the amount on the invoice row

						if (!does_field_exist("package_invoices", "invoice_tax")) {
		
							$sql = "ALTER TABLE `package_invoices` ADD `invoice_tax` FLOAT NOT NULL DEFAULT '0';";
							mysql_query($sql) or die ($sql.mysql_error());
							$sql = "ALTER TABLE `subscription_invoices` ADD `invoice_tax` FLOAT NOT NULL DEFAULT '0';";
							mysql_query($sql) or die ($sql.mysql_error());
							$sql = "ALTER TABLE `membership_invoices` ADD `invoice_tax` FLOAT NOT NULL DEFAULT '0';";
							mysql_query($sql) or die ($sql.mysql_error());

						}

						if ($product_type=='P') {
							$sql = "UPDATE package_invoices SET invoice_tax='".(0-$tax)."', amount = amount + '".jb_escape_sql($order_row['amount'])."' WHERE invoice_id='".jb_escape_sql($invoice_id)."' and employer_id='".jb_escape_sql($_SESSION['JB_ID'])."' AND invoice_tax != '' ";
						} elseif ($product_type=='S') {
							$sql = "UPDATE subscription_invoices SET invoice_tax='".(0-$tax)."', amount = amount + '".jb_escape_sql($order_row['amount'])."' WHERE invoice_id='".jb_escape_sql($invoice_id)."' and employer_id='".jb_escape_sql($_SESSION['JB_ID'])."' AND invoice_tax != ''";
						} elseif ($product_type=='M') {
							$sql = "UPDATE membership_invoices SET invoice_tax='".(0-$tax)."', amount = amount + '".jb_escape_sql($order_row['amount'])."' WHERE invoice_id='".jb_escape_sql($invoice_id)."' and user_id='".jb_escape_sql($_SESSION['JB_ID'])."' AND invoice_tax != ''";
						}

						
					}
				}


				if ((BANK_ADVANCE_CREDIT=='YES') && (strtolower($order_row['status'])!='pending')) {
					// place the order as 'pending' and advance the credits

					if ($product_type=='P') { // posting credits

						$order_row = JB_pend_package_invoice($invoice_id, $payment_method='bank', $pending_reason='jb_credit_advanced');

						// credit the points to the customer's account

						JB_add_posting_credits($order_row);

					}

					if ($product_type=='S') { // subscription to view resumes
						
						$order_row = JB_pend_subscription_invoice($invoice_id, $payment_method='bank', $pending_reason='jb_credit_advanced');
						
						JB_start_employer_subscription($order_row);
						
					}

					if ($product_type=='M') { // membership
						
						$order_row = JB_pend_membership_invoice($invoice_id, $payment_method='bank', $pending_reason='jb_credit_advanced');

						JB_start_membership($order_row);
						
					}

					JBPLUG_do_callback('pay_trn_pending', $invoice_id, $product_type);

				}

				$bank_amount = JB_convert_to_currency($order_row['amount'], $order_row['currency_code'], BANK_CURRENCY);
				$bank_amount = JB_format_currency($bank_amount, BANK_CURRENCY, true);

				

				$label['payment_bank_heading'] = str_replace ("%INVOICE_AMOUNT%", $bank_amount, $label['payment_bank_heading']);
				$label['payment_bank_note'] = str_replace ("%CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $label['payment_bank_note']);
				$label['payment_bank_note'] = str_replace ("%INVOICE_CODE%", $product_type.$invoice_id, $label['payment_bank_note']);

				$label['payment_bank_tax'] = str_replace ("%INVOICE_TAX%",  JB_format_currency($tax, BANK_CURRENCY, true), $label['payment_bank_tax']);

				


				if (JB_get_default_currency()  != BANK_CURRENCY) {	
					echo JB_convert_to_default_currency_formatted($order_row['currency_code'], $order_row['amount'])." = ".$bank_amount;
					echo "<br>";
				}
				
				
				?>
				
				<table width="70%"><tr><td>
				<?php if (BANK_TAX_RATE>0) { ?>
				<b><?php echo $label['payment_bank_tax'];?></b><br>
				<?php } ?>
				<b><?php echo $label['payment_bank_heading'];?></b><br>
				
				<?php if ( BANK_NAME != '') { ?>
				<b><?php echo $label['payment_bank_name'];?></b> <?php echo BANK_NAME; ?><br>
				<?php }  ?>
				<?php if ( BANK_ADDRESS != '') { ?>
				<b><?php echo $label['payment_bank_addr'];?></b> <?php echo BANK_ADDRESS; ?><br>
				<?php }  ?>
				<?php if ( BANK_ACCOUNT_NAME != '') { ?>
				<b><?php echo $label['payment_bank_ac_name'];?></b> <?php echo BANK_ACCOUNT_NAME; ?><br>
				<?php }  ?>
				<?php if ( BANK_ACCOUNT_NUMBER != '') { ?>
				<b><?php echo $label['payment_bank_ac_number'];?></b> <?php echo BANK_ACCOUNT_NUMBER; ?><br>
				<?php }  ?>
				<?php if ( BANK_BRANCH_NUMBER != '') { ?>
				<b><?php echo $label['payment_bank_branch_number'];?></b> <?php echo BANK_BRANCH_NUMBER; ?><br>
				<?php }  ?>
				<?php if ( BANK_SWIFT != '') { ?>

				<b><?php echo $label['payment_bank_swift']; ?></b> <?php echo BANK_SWIFT; ?><br>

				<?php }
				
				
				?>
				<?php echo $label['payment_bank_note'];?>
				</td></tr>
				</table>
				
				</p>
				</center>
				
				</div>
				<?php

				if ($order_row['user_type'] =='C') {
					$sql = "Select * from users where ID='".jb_escape_sql($_SESSION['JB_ID'])."'";
				} else {
					$sql = "Select * from employers where ID='".jb_escape_sql($_SESSION['JB_ID'])."'";
				}

				$result = JB_mysql_query ($sql) or die (mysql_error());
				$e_row = mysql_fetch_array($result, MYSQL_ASSOC);

				if ($product_type=='P') {
			 
					$template_r = JB_get_email_template (60, $_SESSION['LANG']);
					$template = mysql_fetch_array($template_r);
					$msg = $template['EmailText'];																
					$from = $template['EmailFromAddress'];
					$from_name = $template['EmailFromName'];
					$subject = $template['EmailSubject'];
					$to = $e_row['Email'];
					$to_name = jb_get_formatted_name($e_row['FirstName'], $e_row['LastName']);

					

					$msg = str_replace ("%INVOICE_TAX%", JB_format_currency($tax, BANK_CURRENCY, true), $msg);

					$msg = str_replace ("%FNAME%",  $e_row['FirstName'], $msg);
					$msg = str_replace ("%LNAME%", $e_row['LastName'], $msg);
					$msg = str_replace ("%SITE_NAME%", JB_SITE_NAME, $msg);
					$msg = str_replace ("%INVOICE_CODE%", "P".$order_row['invoice_id'], $msg);
					$msg = str_replace ("%QUANTITY%", $order_row['posts_quantity'], $msg);
					$msg = str_replace ("%ITEM_NAME%", $order_row['item_name'], $msg);
					$msg = str_replace ("%INVOICE_AMOUNT%", JB_convert_to_default_currency_formatted($order_row['currency_code'], $order_row['amount']), $msg);
					$msg = str_replace ("%BANK_NAME%", BANK_NAME, $msg);
					$msg = str_replace ("%BANK_ADDRESS%", BANK_ADDRESS, $msg);
					$msg = str_replace ("%BANK_AC_SWIFT%", BANK_SWIFT, $msg);
					$msg = str_replace ("%BANK_AC_CURRENCY%", BANK_CURRENCY, $msg);
					$msg = str_replace ("%BANK_AC_BRANCH%", BANK_AC_BRANCH, $msg);
					$msg = str_replace ("%AC_NAME%", BANK_ACCOUNT_NAME, $msg);
					$msg = str_replace ("%AC_NUMBER%", BANK_ACCOUNT_NUMBER, $msg);
					$msg = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $msg);
					$msg = str_replace ("%SITE_URL%", JB_BASE_HTTP_PATH, $msg);

					
		
					if (BANK_EMAIL_CONFIRM=='YES') {
						$email_id=JB_queue_mail($to, $to_name, $from, $from_name, $subject, $msg, '', 60);
						JB_process_mail_queue(1, $email_id);
					}

				} elseif ($product_type=='S') { // subscription invoice confirmed (id 80)

					$template_r = JB_get_email_template (80, $_SESSION['LANG']);
					$template = mysql_fetch_array($template_r);
					$msg = $template['EmailText'];
					$from = $template['EmailFromAddress'];
					$from_name = $template['EmailFromName'];
					$subject = $template['EmailSubject'];
					$to = $e_row['Email'];
					$to_name = jb_get_formatted_name($e_row['FirstName'], $e_row['LastName']);

					$msg = str_replace ("%INVOICE_TAX%", JB_format_currency($tax, BANK_CURRENCY, true), $msg);


					$msg = str_replace ("%FNAME%",  $e_row['FirstName'], $msg);
					$msg = str_replace ("%LNAME%", $e_row['LastName'], $msg);
					$msg = str_replace ("%SITE_NAME%", JB_SITE_NAME, $msg);
					$msg = str_replace ("%INVOICE_CODE%", "S".$order_row['invoice_id'], $msg);
	
					$msg = str_replace ("%QUANTITY%", $order_row['posts_quantity'], $msg);
					$msg = str_replace ("%ITEM_NAME%", $order_row['item_name'], $msg);
					$msg = str_replace ("%INVOICE_AMOUNT%", JB_convert_to_default_currency_formatted($order_row['currency_code'], $order_row['amount']), $msg);
					$msg = str_replace ("%BANK_NAME%", BANK_NAME, $msg);
					$msg = str_replace ("%BANK_ADDRESS%", BANK_ADDRESS, $msg);
					$msg = str_replace ("%BANK_AC_SWIFT%", BANK_SWIFT, $msg);
					$msg = str_replace ("%BANK_AC_CURRENCY%", BANK_CURRENCY, $msg);
					$msg = str_replace ("%BANK_AC_BRANCH%", BANK_AC_BRANCH, $msg);
					$msg = str_replace ("%AC_NAME%", BANK_ACCOUNT_NAME, $msg);
					$msg = str_replace ("%AC_NUMBER%", BANK_ACCOUNT_NUMBER, $msg);
					$msg = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $msg);
					$msg = str_replace ("%SUB_DURATION%", $order_row['months_duration'], $msg);

					

					if (BANK_EMAIL_CONFIRM=='YES') {
						$email_id=JB_queue_mail($to, $to_name, $from, $from_name, $subject, $msg, '', 80);
						JB_process_mail_queue(1, $email_id);
					}


				} elseif ($product_type=='M') { // membership invoice confirmed (id 100)
					$template_r = JB_get_email_template (100, $_SESSION['LANG']);
					$template = mysql_fetch_array($template_r);
					$msg = $template['EmailText'];
					$from = $template['EmailFromAddress'];
					$from_name = $template['EmailFromName'];
					$subject = $template['EmailSubject'];
					$to = $e_row['Email'];
					$to_name = jb_get_formatted_name($e_row['FirstName'], $e_row['LastName']);

					$msg = str_replace ("%INVOICE_TAX%", JB_format_currency($tax, BANK_CURRENCY, true), $msg);

					$msg = str_replace ("%FNAME%",  $e_row['FirstName'], $msg);
					$msg = str_replace ("%LNAME%", $e_row['LastName'], $msg);
					$msg = str_replace ("%SITE_NAME%", JB_SITE_NAME, $msg);
					$msg = str_replace ("%INVOICE_CODE%", "M".$order_row['invoice_id'], $msg);
					$msg = str_replace ("%INVOICE_CODE%", "M".$order_row['invoice_id'], $msg);
					
					$msg = str_replace ("%ITEM_NAME%", $order_row['item_name'], $msg);
					$msg = str_replace ("%INVOICE_AMOUNT%", JB_convert_to_default_currency_formatted($order_row['currency_code'], $order_row['amount']), $msg);
					$msg = str_replace ("%BANK_NAME%", BANK_NAME, $msg);
					$msg = str_replace ("%BANK_ADDRESS%", BANK_ADDRESS, $msg);
					$msg = str_replace ("%BANK_AC_SWIFT%", BANK_SWIFT, $msg);
					$msg = str_replace ("%BANK_AC_CURRENCY%", BANK_CURRENCY, $msg);
					$msg = str_replace ("%BANK_AC_BRANCH%", BANK_AC_BRANCH, $msg);
					$msg = str_replace ("%AC_NAME%", BANK_ACCOUNT_NAME, $msg);
					$msg = str_replace ("%AC_NUMBER%", BANK_ACCOUNT_NUMBER, $msg);
					$msg = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $msg);
					$msg = str_replace ("%SITE_URL%", JB_BASE_HTTP_PATH, $msg);
					

					if ($order_row['months_duration']=='0') {
						$order_row['months_duration'] = $label['member_not_expire'];
					}
					$msg = str_replace ("%MEM_DURATION%", $order_row['months_duration'], $msg);

					if (BANK_EMAIL_CONFIRM=='YES') {
						$email_id=JB_queue_mail($to, $to_name, $from, $from_name, $subject, $msg, '', 100);
						JB_process_mail_queue(1, $email_id);
					}

				}

				JB_update_payment_method ($product_type, $order_row['invoice_id'], "bank");


			}


		} else {
			JBPLUG_do_callback('pay_trn_verification_failed', $invoice_id, $product_type);

		}


	}



}
?>