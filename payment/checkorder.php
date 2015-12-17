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

$_PAYMENT_OBJECTS['check'] =  new check;

define ('IPN_LOGGING', 'Y');

function JB_ch_mail_error($msg) {

	$date = date("D, j M Y H:i:s O"); 
	
	$headers = "From: ". JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Reply-To: ".JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Return-Path: ".JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "X-Mailer: PHP" ."\r\n";
	$headers .= "Date: $date" ."\r\n"; 
	$headers .= "X-Sender-IP: $REMOTE_ADDR" ."\r\n";

	@mail(JB_SITE_CONTACT_EMAIL, "Error message from ".JB_SITE_NAME." Jamit check payment mod. ", $msg, $headers);

}

function ch_log_entry ($entry_line) {

	if (IPN_LOGGING == 'Y') {

		JB_payment_log_entry_db($entry_line, 'check');

	}

}



###########################################################################
# Payment Object


class check {

	var $name="Check / Money Order";
	var $description="Mail funds by Check / Money Order.";
	var $className="check";
	

	function check() {

		global $label;
		$this->name=$label['payment_check_name'];
		$this->description=$label['payment_check_descr'];

		if ($this->is_installed()) {

			$sql = "SELECT * FROM jb_config where `key`='CHECK_ENABLED' OR `key`='CHECK_PAYABLE' OR `key`='CHECK_ADDRESS'  OR `key`='CHECK_CURRENCY' OR `key`='CHECK_EMAIL_CONFIRM' OR  `key`='CHECK_ADVANCE_CREDIT' or `key`='CHECK_TAX_RATE' or `key`='CHECK_ADD_TAX' ";
			$result = JB_mysql_query($sql) or die (mysql_error().$sql);

			while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {
				define ($row['key'], $row['val']);
			}

		}

	}

	function get_currency() {

		return CHECK_CURRENCY;

	}


	function install() {

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_ENABLED', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_CURRENCY', 'USD')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_PAYABLE', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_ADDRESS', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_EMAIL_CONFIRM', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_ADVANCE_CREDIT', 'NO')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_TAX_RATE', '')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_ADD_TAX', 'N')";
		JB_mysql_query($sql);


	}

	function uninstall() {

		$sql = "DELETE FROM jb_config where `key`='CHECK_ENABLED'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='CHECK_CURRENCY'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='CHECK_PAYABLE'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='CHECK_ADDRESS'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='CHECK_EMAIL_CONFIRM'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='CHECK_ADVANCE_CREDIT'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='CHECK_TAX_RATE'";
		JB_mysql_query($sql);

		$sql = "DELETE FROM jb_config where `key`='CHECK_ADD_TAX'";
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

		} elseif($product_type=='S') {
			$sql = "SELECT * from subscription_invoices where invoice_id='".jb_escape_sql($order_id)."'";

		} elseif($product_type=='M') {
			$sql = "SELECT * from membership_invoices where invoice_id='".jb_escape_sql($order_id)."'";

		}

		$result = JB_mysql_query($sql) or die(mysql_error().$sql);
		$order_row = mysql_fetch_array($result, MYSQL_ASSOC);

		if ($order_row['user_type'] == 'C' ) { // candidate
			$payment_link = JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER."thanks.php?m=".$this->className."&order_id=".jb_prefix_order_id($product_type.$order_row['invoice_id'])."&amp;nhezk5=3";

		} else {
			$payment_link = JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER."thanks.php?m=".$this->className."&order_id=".jb_prefix_order_id($product_type.$order_row['invoice_id'])."&amp;nhezk5=3";

		}
	
				
		?>
		<center>
		
		
		<form id="payment_button" method="post" action="<?php echo $payment_link; ?>">
		<input type="button" value="<?php echo $label['payment_check_button']; ?>" onclick="window.location='<?php echo $payment_link; ?>'">
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
		
			$check_enabled = $_REQUEST['check_enabled'];
			$check_currency = $_REQUEST['check_currency'];
			$check_payable = $_REQUEST['check_payable'];
			$check_address = $_REQUEST['check_address'];
			$check_email_confirm = $_REQUEST['check_email_confirm'];
			$check_advance_credit = $_REQUEST['check_advance_credit'];
			$check_tax_rate = $_REQUEST['check_tax_rate'];
			$check_add_tax = $_REQUEST['check_add_tax'];
			
		} else {
			$check_enabled = CHECK_ENABLED;
			$check_currency = CHECK_CURRENCY;
			$check_payable = CHECK_PAYABLE;
			$check_address = CHECK_ADDRESS;
			$check_email_confirm = CHECK_EMAIL_CONFIRM;
			$check_advance_credit = CHECK_ADVANCE_CREDIT;
			$check_tax_rate = CHECK_TAX_RATE;
			if (!is_numeric($check_tax_rate)) {
				$check_tax_rate = '';
			}
			$check_add_tax = CHECK_ADD_TAX;
			
		}

		
		?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
		 <table border="0" cellpadding="5" cellspacing="2" style="border-style:groove" width="100%" bgcolor="#FFFFFF">

     <tr>
      <td colspan="2"  bgcolor="#e6f2ea">
      <font face="Verdana" size="1"><b>Check Payment Settings</b><br>(If you leave any field field blank, then it will not show up on the checkout)</font></td>
    </tr>
    <tr>
      <td width="20%" bgcolor="#e6f2ea"><font face="Verdana" size="1">Payable to Name</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="check_payable" size="29" value="<?php echo $check_payable; ?>"></font></td>
    </tr>
	 <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Payable to Address</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <textarea name="check_address" rows="4"><?php echo $check_address; ?></textarea></font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Check Currency</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select  name="check_currency" ><?php echo JB_currency_option_list ($check_currency); ?></select></font></td>
    </tr>

     <tr>
	 <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Send confirmation email</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
       <input type="radio" name="check_email_confirm" value="YES"  <?php if ($check_email_confirm=='YES') { echo " checked "; } ?> >Yes - Send confirmation email before payment is made<br>
	  <input type="radio" name="check_email_confirm" value="NO"  <?php if ($check_email_confirm=='NO') { echo " checked "; } ?> >No<br></font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Advance Credits</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
       <input type="radio" name="check_advance_credit" value="YES"  <?php if ($check_advance_credit=='YES') { echo " checked "; } ?> >Yes - credit the account with posting credits before payment is made<br>
	  <input type="radio" name="check_advance_credit" value="NO"  <?php if ($check_advance_credit=='NO') { echo " checked "; } ?> >No<br></font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Tax Rate</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="check_tax_rate" size="3" value="<?php echo $check_tax_rate; ?>"> (Optional... enter a decimal, eg 0.1<br>Use %INVOICE_TAX% on the bank email template which will display the tax amount)<br> <input type="checkbox" name="check_add_tax" value="Y" <?php if ($check_add_tax=='Y') { echo ' checked '; } ?> > Add the tax to the amount? Do not check if prices already include tax.</font></td>
    </tr>
	
      <td  bgcolor="#e6f2ea" colspan=2><font face="Verdana" size="1"><input type="submit" value="Save"></font>
	  </td>
	  </tr>
  </table>
  <input type="hidden" name="pay" value="<?php echo jb_escape_html($_REQUEST['pay']);?>">
  <input type="hidden" name="action" value="save">
  
</form>

		<?php

	}

	function save_config() {

		
		

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_NAME', '".jb_escape_sql($_REQUEST['check_name'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_PAYABLE', '".jb_escape_sql($_REQUEST['check_payable'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_ADDRESS', '".jb_escape_sql($_REQUEST['check_address'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_CURRENCY', '".jb_escape_sql($_REQUEST['check_currency'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);
		
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_EMAIL_CONFIRM', '".jb_escape_sql($_REQUEST['check_email_confirm'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_ADVANCE_CREDIT', '".jb_escape_sql($_REQUEST['check_advance_credit'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_TAX_RATE', '".jb_escape_sql($_REQUEST['check_tax_rate'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_ADD_TAX', '".jb_escape_sql($_REQUEST['check_add_tax'])."')";
		JB_mysql_query($sql) or die (mysql_error().$sql);

	

	}

	// true or false
	function is_enabled() {

		$sql = "SELECT val from jb_config where `key`='CHECK_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		if ($row['val']=='Y') {
			return true;

		} else {
			return false;

		}

	}


	function is_installed() {

		$sql = "SELECT val from jb_config where `key`='CHECK_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);
		

		if (mysql_num_rows($result)>0) {
			return true;

		} else {
			return false;

		}

	}

	function enable() {

		$sql = "UPDATE jb_config set val='Y' where `key`='CHECK_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);


	}

	function disable() {

		$sql = "UPDATE jb_config set val='N' where `key`='CHECK_ENABLED' ";
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

				JBPLUG_do_callback('pay_trn_verification_failed', $invoice_id, $product_type);

				echo "Error: You must be logged in to view this page";


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

				if (CHECK_TAX_RATE>0) {
					
					if (CHECK_ADD_TAX!='Y') {
						// work out the tax paid in the amount
						$tax = $order_row['amount'] - ($order_row['amount'] / (1.00 + CHECK_TAX_RATE));
						
					} else {
						$tax = $order_row['amount'] * CHECK_TAX_RATE;
						$order_row['amount'] = $order_row['amount'] + $tax;
					}
					
					
				}

				if ((CHECK_ADVANCE_CREDIT=='YES') && (strtolower($order_row['status'])!='pending')) {
					// place the order as 'pending' and advance the credits

					if ($product_type=='P') { // posting credits

						$order_row = JB_pend_package_invoice($invoice_id, $payment_method='check', $pending_reason='jb_credit_advanced');

						// credit the points to the customer's account
						JB_add_posting_credits($order_row);

					}

					if ($product_type=='S') { // subscription to view resumes
						
						$order_row = JB_pend_subscription_invoice($invoice_id, $payment_method='check', $pending_reason='jb_credit_advanced');

						JB_start_employer_subscription($order_row);

					}

					if ($product_type=='M') { // membership
						
						$order_row = JB_pend_membership_invoice($invoice_id, $payment_method='check', $pending_reason='jb_credit_advanced');

						JB_start_membership($order_row);
						
					}

					JBPLUG_do_callback('pay_trn_pending', $invoice_id, $product_type);


				}
			
				$check_amount = JB_convert_to_currency($order_row['amount'], $order_row['currency_code'], CHECK_CURRENCY);
				$check_amount = JB_format_currency($check_amount, CHECK_CURRENCY, true);

				$label['payment_check_sub_head'] = str_replace ("%INVOICE_CODE%", $product_type.$_REQUEST['order_id'], $label['payment_check_sub_head']);
				
				echo $label['payment_check_sub_head'];
			


				$label['payment_check_heading'] = str_replace ("%INVOICE_AMOUNT%", $check_amount, $label['payment_check_heading']);

				
				$label['payment_check_tax'] = str_replace ("%INVOICE_TAX%", JB_format_currency($tax, CHECK_CURRENCY, true), $label['payment_check_tax']);	

				if (JB_get_default_currency()  != CHECK_CURRENCY) {	
					echo JB_convert_to_default_currency_formatted($order_row['currency_code'], $order_row['amount'])." = ".$check_amount;
					echo "<br>";
				}?>
				
				<table width="70%"><tr><td>
				<?php if (CHECK_TAX_RATE>0) { ?>
				<b><?php echo $label['payment_check_tax'];?></b><br>
				<?php } ?>
				<b><?php echo $label['payment_check_heading'];?></b><br>
				<?php if ( CHECK_NAME != '') { ?>
				<b><?php echo $label['payment_check_payable'];?></b><pre><?php echo CHECK_PAYABLE; ?></pre><br>
				<?php }  ?>
				<?php if ( CHECK_ADDRESS != '') { ?>
				<b><?php echo $label['payment_check_address'];?></b><pre><?php echo CHECK_ADDRESS; ?></pre><br>
				<?php }  ?>
				<?php /*if ( CHECK_ACCOUNT_NAME != '') { ?>
				<b><?php echo $label['payment_check_currency'];?></b><pre><?php echo CHECK_CURRENCY; ?></pre><br>
				<?php } */  ?>
				
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

				if ($product_type=='S') {

					$template_r = JB_get_email_template (81, $_SESSION['LANG']);
					$template = mysql_fetch_array($template_r);
					$msg = $template['EmailText'];
					$from = $template['EmailFromAddress'];
					$from_name = $template['EmailFromName'];
					$subject = $template['EmailSubject'];

					$msg = str_replace ("%INVOICE_TAX%", JB_format_currency($tax, CHECK_CURRENCY, true), $msg);


					$msg = str_replace ("%FNAME%",  $e_row['FirstName'], $msg);
					$msg = str_replace ("%LNAME%", $e_row['LastName'], $msg);
					$msg = str_replace ("%SITE_NAME%", JB_SITE_NAME, $msg);
					$msg = str_replace ("%INVOICE_CODE%", "S".$order_row['invoice_id'], $msg);
					$msg = str_replace ("%ITEM_NAME%", $order_row['item_name'], $msg);
					$msg = str_replace ("%SUB_DURATION%", $order_row['months_duration'], $msg);
					$msg = str_replace ("%INVOICE_AMOUNT%", JB_convert_to_default_currency_formatted($order_row['currency_code'], $order_row['amount']), $msg);
					$msg = str_replace ("%PAYEE_NAME%", CHECK_PAYABLE, $msg);
					$msg = str_replace ("%PAYEE_ADDRESS%", CHECK_ADDRESS, $msg);
					$msg = str_replace ("%CHECK_CURRENCY%", CHECK_CURRENCY, $msg);
					$msg = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $msg);
					$msg = str_replace ("%SITE_URL%", JB_BASE_HTTP_PATH, $msg);
		//echo $msg;
					 $to = $e_row['Email'];
					 $to_name = jb_get_formatted_name($e_row['FirstName'], $e_row['LastName']);

					 if (CHECK_EMAIL_CONFIRM=='YES') {
						$email_id=JB_queue_mail($to, $to_name, $from, $from_name, $subject, $msg, '', 81);
						JB_process_mail_queue(1, $email_id);
					 }

				} elseif ($product_type=='M') {
					$template_r = JB_get_email_template (101, $_SESSION['LANG']);
					$template = mysql_fetch_array($template_r);
					$msg = $template['EmailText'];
					$from = $template['EmailFromAddress'];
					$from_name = $template['EmailFromName'];
					$subject = $template['EmailSubject'];

					$msg = str_replace ("%INVOICE_TAX%", JB_format_currency($tax, CHECK_CURRENCY, true), $msg);

					$msg = str_replace ("%FNAME%",  $e_row['FirstName'], $msg);
					$msg = str_replace ("%LNAME%", $e_row['LastName'], $msg);
					$msg = str_replace ("%SITE_NAME%", JB_SITE_NAME, $msg);
					$msg = str_replace ("%INVOICE_CODE%", "S".$order_row['invoice_id'], $msg);
					$msg = str_replace ("%ITEM_NAME%", $order_row['item_name'], $msg);
					if ($order_row['months_duration']=='0') {
						$order_row['months_duration'] = $label['member_not_expire'];
					}
					$msg = str_replace ("%MEM_DURATION%", $order_row['months_duration'], $msg);
					$msg = str_replace ("%INVOICE_AMOUNT%", JB_convert_to_default_currency_formatted($order_row['currency_code'], $order_row['amount']), $msg);
					$msg = str_replace ("%PAYEE_NAME%", CHECK_PAYABLE, $msg);
					$msg = str_replace ("%PAYEE_ADDRESS%", CHECK_ADDRESS, $msg);
					$msg = str_replace ("%CHECK_CURRENCY%", CHECK_CURRENCY, $msg);
					$msg = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $msg);
					$msg = str_replace ("%SITE_URL%", JB_BASE_HTTP_PATH, $msg);
					//echo $msg;
					 $to = $e_row['Email'];
					 $to_name = jb_get_formatted_name($e_row['FirstName'], $e_row['LastName']);

					 if (CHECK_EMAIL_CONFIRM=='YES') {
						 $email_id=JB_queue_mail($to, $to_name, $from, $from_name, $subject, $msg, '', 101);
						 JB_process_mail_queue(1, $email_id);
					 }

				} elseif ($product_type=='P') {


					$template_r = JB_get_email_template (61, $_SESSION['LANG']);
					$template = mysql_fetch_array($template_r);
					$msg = $template['EmailText'];
					$from = $template['EmailFromAddress'];
					$from_name = $template['EmailFromName'];
					$subject = $template['EmailSubject'];

					

					$msg = str_replace ("%INVOICE_TAX%", JB_format_currency($tax, CHECK_CURRENCY, true), $msg);

					$msg = str_replace ("%FNAME%",  $e_row['FirstName'], $msg);
					$msg = str_replace ("%LNAME%", $e_row['LastName'], $msg);
					$msg = str_replace ("%SITE_NAME%", JB_SITE_NAME, $msg);
					$msg = str_replace ("%INVOICE_CODE%", "P".$order_row['invoice_id'], $msg);
					$msg = str_replace ("%ITEM_NAME%", $order_row['item_name'], $msg);
					$msg = str_replace ("%QUANTITY%", $order_row['posts_quantity'], $msg);
					$msg = str_replace ("%INVOICE_AMOUNT%", JB_convert_to_default_currency_formatted($order_row['currency_code'], $order_row['amount']), $msg);
					$msg = str_replace ("%PAYEE_NAME%", CHECK_PAYABLE, $msg);
					$msg = str_replace ("%PAYEE_ADDRESS%", CHECK_ADDRESS, $msg);
					$msg = str_replace ("%CHECK_CURRENCY%", CHECK_CURRENCY, $msg);
					$msg = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $msg);
					$msg = str_replace ("%SITE_URL%", JB_BASE_HTTP_PATH, $msg);
		//echo $msg;
					 $to = $e_row['Email'];
					 $to_name = jb_get_formatted_name($e_row['FirstName'], $e_row['LastName']);

					 if (CHECK_EMAIL_CONFIRM=='YES') {
						$email_id=JB_queue_mail($to, $to_name, $from, $from_name, $subject, $msg, '', 61);
						JB_process_mail_queue(1, $email_id);
					 }
					
				}

				JB_update_payment_method ($product_type, $order_row['invoice_id'], "check");


			} 


		} else {
				JBPLUG_do_callback('pay_trn_verification_failed', $invoice_id, $product_type);
		}


	}



}
?>