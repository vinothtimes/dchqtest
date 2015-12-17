<?php 

require_once "../config.php";
include_once ('../include/accounting_functions.php');
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
define ('_2CO_LOGGING', 'Y');
$_PAYMENT_OBJECTS['_2CO'] =  new _2CO;

function JB__2co_mail_error($msg) {

	$date = date("D, j M Y H:i:s O"); 
	
	$headers = "From: ". JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Reply-To: ".JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Return-Path: ".JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "X-Mailer: PHP" ."\r\n";
	$headers .= "Date: $date" ."\r\n"; 
	$headers .= "X-Sender-IP: $REMOTE_ADDR" ."\r\n";

	$entry_line =  "(payal error detected) $msg\r\n "; 
	$log_fp = @fopen("logs.txt", "a"); 
	@fputs($log_fp, $entry_line); 
	@fclose($log_fp);

	@mail(JB_SITE_CONTACT_EMAIL, "Error message from ".JB_SITE_NAME." 2Checkout script. ", $msg, $headers);

}

function _2co_log_entry ($entry_line) {

	if (_2CO_LOGGING == 'Y') {

		JB_payment_log_entry_db($entry_line, '_2CO');

	}


}

function _2co_format_number($str,$decimal_places='2',$decimal_padding="0"){
       /* firstly format number and shorten any extra decimal places */
       /* Note this will round off the number pre-format $str if you dont want this fucntionality */
       $str          =  number_format($str,$decimal_places,'.','');    // will return 12345.67
       $number      = explode('.',$str);
       $number[1]    = (isset($number[1]))?$number[1]:''; // to fix the PHP Notice error if str does not contain a decimal placing.
       $decimal    = str_pad($number[1],$decimal_places,$decimal_padding);
       return (float) $number[0].'.'.$decimal;
}



if (!empty($_REQUEST['md5_hash'])) { 

	// verify hash

	// UPPERCASE(MD5_ENCRYPTED(sale_id + vendor_id + invoice_id + Secret Word + _2CO_SECRET_WORD));

	 $h = strtoupper(md5($_REQUEST['sale_id']._2CO_SID.$_REQUEST['invoice_id']._2CO_SECRET_WORD));

	 if ($h === $_REQUEST['md5_hash']) {

		 // hash verified!
			$cart_order_id = $_REQUEST['vendor_order_id'];
			$cart_order_id = jb_strip_order_id($cart_order_id);
			$product_type = substr($cart_order_id, 0, 1);// 'P' or 'S' or 'M'
			$cart_order_id = substr($cart_order_id, 1);

		 if ($_REQUEST['invoice_status'] == 'approved') {

			
			if ($product_type=='P') {
				JB_complete_package_invoice($cart_order_id, '2co');
			} elseif ($product_type=='S') {
				JB_complete_subscription_invoice($cart_order_id, '2co');
			} elseif ($product_type=='M') {
				JB_complete_membership_invoice($invoice_id, '2co');
			}

		 } elseif ($_REQUEST['invoice_status'] == 'approved') {

			 if ($product_type=='P') {
				JB_pend_package_invoice($cart_order_id, '2co', '');
			} elseif ($product_type=='S') {
				JB_pend_subscription_invoice($cart_order_id, '2co', '');
			} elseif ($product_type=='M') {
				JB_cpend_membership_invoice($invoice_id, '2co', '');
			}

		 }

	 }

	
	//$_PAYMENT_OBJECTS['_2CO']->process_payment_return();
	
	//die();

/*
	array (
  'auth_exp' =&gt; 'N/A',
  'bill_city' =&gt; 'Columbus',
  'bill_country' =&gt; 'USA',
  'bill_postal_code' =&gt; '43228',
  'bill_state' =&gt; 'Ohio',
  'bill_street_address' =&gt; '1785 Acme Road',
  'bill_street_address2' =&gt; 'Apt 2',
  'cust_currency' =&gt; 'USD',
  'customer_email' =&gt; 'JohnDoe@2co.com',
  'customer_first_name' =&gt; 'Raphael',
  'customer_ip' =&gt; '192.168.2.37',
  'customer_ip_country' =&gt; 'USA',
  'customer_last_name' =&gt; 'Doe',
  'customer_name' =&gt; 'Raphael Doe',
  'customer_phone' =&gt; '6149212450',
  'fraud_status' =&gt; 'pass',
  'invoice_cust_amount' =&gt; '9.99',
  'invoice_id' =&gt; '100006320',
  'invoice_list_amount' =&gt; '9.99',
  'invoice_status' =&gt; 'approved',
  'invoice_usd_amount' =&gt; '10.25',
  'item_count' =&gt; '1',
  'item_cust_amount_1' =&gt; '10.25',
  'item_duration_1' =&gt; '1',
  'item_id_1' =&gt; '4562',
  'item_list_amount_1' =&gt; '9.99',
  'item_name_1' =&gt; 'New Account Signup',
  'item_rec_date_next_1' =&gt; 'N/A',
  'item_rec_install_billed_1' =&gt; '0',
  'item_rec_list_amount_1' =&gt; 'N/A',
  'item_rec_status_1' =&gt; 'N/A',
  'item_recurrence_1' =&gt; 'N/A',
  'item_type_1' =&gt; 'N/A',
  'item_usd_amount_1' =&gt; '49.00',
  'key_count' =&gt; '56',
  'list_currency' =&gt; 'USD',
  'md5_hash' =&gt; 'F9E2731C0F3F567D18D8647F6EA8D8DB',
  'message_description' =&gt; 'Test Message',
  'message_id' =&gt; '1',
  'message_type' =&gt; 'FRAUD_STATUS_CHANGED',
  'payment_type' =&gt; 'paypal',
  'recurring' =&gt; '0',
  'sale_date_placed' =&gt; '2004-03-1619:23:45',
  'sale_id' =&gt; '100006310',
  'ship_city' =&gt; 'Columbus',
  'ship_country' =&gt; 'USA',
  'ship_name' =&gt; 'Janie Doe',
  'ship_postal_code' =&gt; '43235',
  'ship_state' =&gt; 'OH',
  'ship_status' =&gt; 'shipped',
  'ship_street_address' =&gt; '134 Acme Lane',
  'ship_street_address2' =&gt; 'apt 3D',
  'ship_tracking_number' =&gt; '2323424234234-234-234',
  'timestamp' =&gt; '2009-04-0319:48:58',
  'vendor_id' =&gt; '1007',
  'vendor_order_id' =&gt; '1008OA',
)

*/

}


###########################################################################
# Payment Object



class _2CO {

	//global $label;

	var $name;
	var $description;
	var $className="_2CO";

	function _2co() {

		global $label;
		$this->name=$label['payment_2co_name'];
		$this->description=$label['payment_2co_descr'];

		if ($this->is_installed()) {


			$sql = "SELECT * FROM jb_config where `key`='_2CO_ENABLED' OR `key`='_2CO_SID' OR `key`='_2CO_DEMO' OR `key`='_2CO_SECRET_WORD' OR `key`='_2CO_PAYMENT_ROUTINE' or `key`='_2CO_X_RECEIPT_LINK_URL' or `key`='_2CO_CANDIDATE_X_RECEIPT_LINK_URL' ";
			$result = JB_mysql_query($sql);

			while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

				define ($row['key'], $row['val']);

			}

			define ('_2CO_CURRENCY', 'USD'); // USD by default

			// guess the _2CO_CANDIDATE_X_RECEIPT_LINK_URL
			// these two constants were introduced in 2.9.0
			$host = $_SERVER['SERVER_NAME']; // hostname
			$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
			$http_url = explode ("/", $http_url);
			array_pop($http_url); // get rid of filename
			array_pop($http_url); // get rid of /admin
			$http_url = implode ("/", $http_url);

			if (!defined('_2CO_CANDIDATE_X_RECEIPT_LINK_URL')) {

				$url = "http://".$host.$http_url."/".JB_CANDIDATE_FOLDER."thanks.php?m=".$this->className;
				define('_2CO_CANDIDATE_X_RECEIPT_LINK_URL', $url);
			}


		}

	}

	function get_currency() {

		return 'USD';

	}


	function install() {

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('_2CO_ENABLED', 'N')";
		JB_mysql_query($sql);

		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('_2CO_SID', '')";
		JB_mysql_query($sql);
		//$sql = "REPLACE INTO jb_config (`key`, val, descr) VALUES ('_2CO_PRODUCT_ID', '1', '# Your 2CO seller ID number.')";
		//JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('_2CO_DEMO', 'Y')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('_2CO_SECRET_WORD', '')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('_2CO_PAYMENT_ROUTINE', 'https://www2.2checkout.com/2co/buyer/purchase')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('_2CO_X_RECEIPT_LINK_URL', '')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('_2CO_CANDIDATE_X_RECEIPT_LINK_URL', '')";
		JB_mysql_query($sql);
		

		

	}

	function uninstall() {

		$sql = "DELETE FROM jb_config where `key`='_2CO_ENABLED'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='_2CO_SID'";
		JB_mysql_query($sql);
		//$sql = "REPLACE INTO jb_config (`key`, val, descr) VALUES ('_2CO_PRODUCT_ID', '1', '# Your 2CO seller ID number.')";
		//JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='_2CO_DEMO'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='_2CO_SECRET_WORD'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='_2CO_PAYMENT_ROUTINE'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='_2CO_X_RECEIPT_LINK_URL'";
		JB_mysql_query($sql);
		$sql = "DELETE FROM jb_config where `key`='_2CO_CANDIDATE_X_RECEIPT_LINK_URL'";
		JB_mysql_query($sql);
		

		


	}

	function payment_button($order_id, $product_type) {

		if (func_num_args() > 1) {
			$product_type = func_get_arg(1);
		}

		if ($product_type == '') {
			$product_type = 'P'; // posting package
		}  

		// else product type of 'S' is a subscription.

		global $label;

		if ($product_type=='P') {
			$order_row = JB_get_product_invoice_row ($order_id);
			$prod_id = $order_row['package_id'];
		} elseif ($product_type=='S') {
			$order_row = JB_get_subscription_invoice_row($order_id);
			$prod_id = $order_row['subscription_id'];
		} elseif ($product_type=='M') {
			$order_row = JB_get_membership_invoice_row($order_id);
			$prod_id = $order_row['membership_id'];
		}


		?>

		<center>
		<form id="payment_button" name="_2coform" action="<?php echo _2CO_PAYMENT_ROUTINE; ?>" method="post">
		
		<?php
		/*
		Optional parameters 
		sh_cost - Shipping and handling cost, if any in your current currency. 
		c_name or c_name_[:digit] - Required for new product creation. Name of new product limited to 128 characters. 
		c_description or c_description_[:digit] - Required for new product creation. 
		Short description of the product, limited to 255 characters.
		Longer description will be stored in the 2Co product database 
		as long description, and will not show up on checkout pages. 
		c_price or c_price_[:digit] - Required for new product creation.
		Price of the product in your current currency.
		Numbers and decimal points only. Maximum value 999999.99 
		c_tangible or c_tangible_[:digit] - Y or y indicates as tangible or physical product
		N or n indicates an e-good or a service. 
		*/
		
		if ($order_row['user_type']=='C') { // Is user type a Candiadte? ?> 
			<input type="hidden" name="x_receipt_link_url" value="<?php echo _2CO_CANDIDATE_X_RECEIPT_LINK_URL; ?>">
			<input type="hidden" name="return_url" value="<?php echo _2CO_CANDIDATE_X_RECEIPT_LINK_URL; ?>">
			<?php
		} else {
			?>
			<input type="hidden" name="x_receipt_link_url" value="<?php echo _2CO_X_RECEIPT_LINK_URL; ?>">
			<input type="hidden" name="return_url" value="<?php echo _2CO_X_RECEIPT_LINK_URL; ?>">
			<?php 	
		} 
		
		?>
		
		<input type="hidden" name="demo" value="<?php echo _2CO_DEMO; ?>">
		<input type="hidden" name="sid" value="<?php echo _2CO_SID; ?>">

		<input type="hidden" name="total" value="<?php echo JB_convert_to_currency($order_row['amount'], $order_row['currency_code'], 'USD');?>">
		<input type="hidden" name="cart_order_id" value="<?php echo jb_prefix_order_id($product_type.$order_row['invoice_id']);?>">
	
		
		<input type="hidden" name="fixed" value="N">
		<input type="hidden" name="c_name" value="<?php echo htmlentities(JB_SITE_NAME); ?> - #<?php  echo htmlentities($product_type.$prod_id); ?>">
		<input type="hidden" name="c_description" value="<?php echo htmlentities($order_row['item_name']); ?>">
		<input type="hidden" name="c_price" value="<?php echo  JB_convert_to_currency($order_row['amount'], $order_row['currency_code'], 'USD'); ?>">
		<input type="hidden" name="c_tangible" value="N">
		<!-- New parameters -->
		 
		<input type="hidden" name="c_prod" value="<?php echo htmlentities($product_type.$prod_id); ?>">
		<input type="hidden" name="id_type" value="1">
		
		<input type="submit" value="<?php echo $label['payment_2co_submit_butt'];?>"><br>


		</form>
		</center>
		<center>
		
		<img border='0' onclick="document._2coform.submit();" src="http://www.2checkout.com/images/overview/btns/21.jpg">
		
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

		if ($_REQUEST['action']=='save') {
			$_2co_sid = $_REQUEST['_2co_sid'];
			$_2co_payment_routine = $_REQUEST['_2co_payment_routine'];
			$_2co_demo = $_REQUEST['_2co_demo'];
			$_2co_secret_word = $_REQUEST['_2co_secret_word'];
			$_2co_x_receipt_link_url = $_REQUEST['_2co_x_receipt_link_url'];
			$_2co_candidate_x_receipt_link_url = $_REQUEST['_2co_candidate_x_receipt_link_url'];
			
		} else {
			$_2co_sid = _2CO_SID;
			$_2co_payment_routine = _2CO_PAYMENT_ROUTINE;
			$_2co_demo = _2CO_DEMO;
			$_2co_secret_word = _2CO_SECRET_WORD;
			$_2co_x_receipt_link_url = _2CO_X_RECEIPT_LINK_URL;
			$_2co_candidate_x_receipt_link_url = _2CO_CANDIDATE_X_RECEIPT_LINK_URL;
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
      <td colspan="2"  bgcolor="#e6f2ea">
      <font face="Verdana" size="1"><b>2Chekout Payment Settings</b><br>
	  Note: The script requires a C20 version 2 account.<br>
	  C2O allows only 1 account per website, so if you do not have a C2O account for this website, you will need to register a new C2O account to use this payment option.<br>
	  When configuring 2CO, please log in to your vendor account, and then go to Account->Site Managment.<br>
	  Demo Settings: Set to 'on' if you want to test your 2CO installation. Set to 'off' if you want to go live<br>
	  Direct Return: Set to 'immediately returned to my website'<br>
	  Secret word: Enter a secret word of your choice<br>
	  URLs:<br>
	  If you encounter settings for 'Approved URL', 'Pending URL', then you can leave these blank. The job board will send the correct URLs to 2CO with each transaction. <br>
	  In case you need to set these manually, the return URLs for Employer's products is: <b>http://<?php echo $host.$http_url."/".JB_EMPLOYER_FOLDER."thanks.php?m=".$this->className; ?></b><br> 
	  Candidates: <b>http://<?php echo $host.$http_url."/".JB_CANDIDATE_FOLDER."thanks.php?m=".$this->className; ?></b><br> 
	  
	</font></td>

    </tr>
	<tr>
      <td width="20%" bgcolor="#e6f2ea"><font face="Verdana" size="1">2Chekout Vendor ID</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="_2co_sid" size="29" value="<?php echo $_2co_sid; ?>"> (This ID is displayed at the top of the page, inside your 2CO account)</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">2CO Payment routine</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="_2co_payment_routine" size="50" value="<?php echo $_2co_payment_routine; ?>"><br>Recommended: <b>https://www.2checkout.com/2co/buyer/purchase</b></font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">2Chekout receipt link URL (Employers).</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="_2co_x_receipt_link_url" size="50" value="<?php echo $_2co_x_receipt_link_url; ?>"><br> (Enter the return URL here. The return URL for should be: <b>http://<?php echo $host.$http_url."/".JB_EMPLOYER_FOLDER."thanks.php?m=".$this->className; ?></b>  <br>The 'URLs' set in the Account->Site Managment section your 2CO account will be ignored, and this setting will be used instead.)</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">2Chekout receipt link URL (Candidates).</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="_2co_candidate_x_receipt_link_url" size="50" value="<?php echo $_2co_candidate_x_receipt_link_url; ?>"><br> (Recommended: <b>http://<?php echo $host.$http_url."/".JB_CANDIDATE_FOLDER."thanks.php?m=".$this->className; ?></b>  <br>Same as above, but for candidates)</font></td>
    </tr>
	
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Demo Mode (Y/N)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
       <input type="radio" name="_2co_demo" value="Y"  <?php if ($_2co_demo=='Y') { echo " checked "; } ?> >Yes <br>
	  <input type="radio" name="_2co_demo" value="N"  <?php if ($_2co_demo=='N') { echo " checked "; } ?> >No<br>(Demo mode is used to test your 2CO installation. Set to 'No' once your site is live)</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">2CO 
      Secret Word</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="_2co_secret_word" size="50" value="<?php echo $_2co_secret_word; ?>"><br>(This is the secret word that is set on the Account->Site Managment page in your 2CO account)</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">2Checkout Currency is passed in as USD </font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select disabled name="_2co_currency" >
	  <!--
	  2co supported currencies:
Australian Dollar (AUD) 
Canadian Dollar (CAD) 
Swiss Franc (CHF) 
Danish Krone (DKK) 
Euro (EUR) 
British Pound (GBP) 
Hong Kong Dollar (HKD) 
Japanese Yen (JPY) 
Norwegian Krone (NOK) 
New Zealand Dollar (NZD) 
Swedish Krona (SEK) 
U.S. Dollar (USD)

	  -->
	  		<option value="USD" <?php define('_2CO_CURRENCY','USD'); if (_2CO_CURRENCY=='USD') { echo " selected "; }  ?> >USD</option>
		<option value="AUD" <?php if (_2CO_CURRENCY=='AUD') { echo " selected "; }  ?> >AUD</option>
		<option value="EUR" <?php if (_2CO_CURRENCY=='EUR') { echo " selected "; }  ?> >EUR</option>
		<option value="USD" selected <?php if (_2CO_CURRENCY=='USD') { echo " selected "; }  ?> >USD</option>
		<option value="CAD" <?php if (_2CO_CURRENCY=='CAD') { echo " selected "; }  ?> >CAD</option>
		<option value="JPY" <?php if (_2CO_CURRENCY=='JPY') { echo " selected "; }  ?> >JPY</option>
		<option value="GBP" <?php if (_2CO_CURRENCY=='GBP') { echo " selected "; }  ?> >GBP</option>
	  
	  </select>(Disabled - Users select their preferred currency at chekout)</font></td>
    </tr>
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

		
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('_2CO_SID', '".$_REQUEST['_2co_sid']."')";
		JB_mysql_query($sql) or die(mysql_error().$sql);
		
		//$sql = "REPLACE INTO jb_config (`key`, val, descr) VALUES ('_2CO_PRODUCT_ID', '1', '# Your 2CO seller ID number.')";
		//JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('_2CO_DEMO', '".jb_escape_sql($_REQUEST['_2co_demo'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('_2CO_SECRET_WORD', '".jb_escape_sql($_REQUEST['_2co_secret_word'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('_2CO_PAYMENT_ROUTINE', '".jb_escape_sql($_REQUEST['_2co_payment_routine'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('_2CO_X_RECEIPT_LINK_URL', '".jb_escape_sql($_REQUEST['_2co_x_receipt_link_url'])."')";
		JB_mysql_query($sql);
		$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('_2CO_CANDIDATE_X_RECEIPT_LINK_URL', '".jb_escape_sql($_REQUEST['_2co_candidate_x_receipt_link_url'])."')";
		JB_mysql_query($sql);

		


	}

	// true or false
	function is_enabled() {

		$sql = "SELECT val from jb_config where `key`='_2CO_ENABLED' ";
		$result = JB_mysql_query($sql);
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		if ($row['val']=='Y') {
			return true;

		} else {
			return false;

		}

	}


	function is_installed() {

		$sql = "SELECT val from jb_config where `key`='_2CO_ENABLED' ";
		$result = JB_mysql_query($sql);

		if (mysql_num_rows($result)>0) {
			return true;

		} else {
			return false;

		}

	}

	function enable() {

		$sql = "UPDATE jb_config set val='Y' where `key`='_2CO_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);


	}

	function disable() {

		$sql = "UPDATE jb_config set val='N' where `key`='_2CO_ENABLED' ";
		$result = JB_mysql_query($sql) or die(mysql_error().$sql);

	}

	// process_payment_return() - Called when thanks.php page is accessed after returning from payment gateway
	// - Place affiliate code here.
	// - You can access all the variables returned form the payment gatway through the $_POST array (If the payment gateway returned any variables)
	// - place any other functionality here.

	function process_payment_return() {

		global $label;

		if ($_REQUEST['key']!='') { 

			$order_number = $_REQUEST['order_number'];
			//$order_number = _2CO_SID."-".$order_number;
			//.Demo mode:The order number used to create the Hash is forced to equal 1. This designates that the order is a demo order.
			if (_2CO_DEMO=='Y') {
				$hash_order_number = 1;
			} else {
				$hash_order_number = $order_number;
			}
			$card_holder_name = $_REQUEST['card_holder_name'];
			$street_address = $_REQUEST['street_address'];
			$city = $_REQUEST['city'];
			$state = $_REQUEST['state'];
			$zip = $_REQUEST['zip'];
			$country = $_REQUEST['country'];
			$email = $_REQUEST['email'];
			$phone = $_REQUEST['phone'];
			$credit_card_processed = $_REQUEST['credit_card_processed']; // Y = successfull. K = pending
			$total = $_REQUEST['total'];
			$product_id = $_REQUEST['product_id']; // c2o product id
			$quantity = $_REQUEST['quantity']; // quantity
			$merchant_product_id = $_REQUEST['merchant_product_id']; //
			$cart_order_id = $_REQUEST['cart_order_id'];
			$cart_order_id = jb_strip_order_id($cart_order_id);
			$product_type = substr($cart_order_id, 0, 1);// 'P' or 'S' or 'M'
			$cart_order_id = substr($cart_order_id, 1);

			$product_description = $_REQUEST['product_description'];
			$x_MD5_Hash = strtolower ( $_REQUEST['key']);  // md5 (secret word + vendor number + order number + total)
			
		
			foreach ($_REQUEST as $key => $val) {
				$req .= "&".$key."=".$val;
			}
			_2co_log_entry ("2checkout: ".$req);

			// process order

			$_2CO = new _2CO(); // load in the constants..

			
			// md5 (secret word + vendor number + order number + total)
			$md5_str = _2CO_SECRET_WORD . _2CO_SID . $hash_order_number  . $total;
			$hash = md5 ($md5_str);


			if (strcmp($hash, $x_MD5_Hash )==0) {

				JBPLUG_do_callback('pay_trn_verification_passed', $cart_order_id, $product_type);

				if ($credit_card_processed=='Y') {
					# Credit card processed OK
					if ($product_type=='P') {
						JB_complete_package_invoice($cart_order_id, '2co');
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
						JB_complete_subscription_invoice($cart_order_id, '2co');
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

						JB_complete_membership_invoice($cart_order_id, '2co');
						?>
						<center>

						<img src="<?php echo JB_SITE_LOGO_URL; ?>">
						<p><?php echo $label['payment_membership_completed']; ?></p>
						</center>
						<?php

					}
					JBPLUG_do_callback('pay_trn_passed', $cart_order_id, $product_type);
					JB_debit_transaction($cart_order_id, $total, 'USD', $order_number, $reason, '2co', $product_type);
					

				} elseif ($credit_card_processed=='K') {
					# credit card pending
					if ($product_type=='P') {
						JB_pend_package_invoice ($cart_order_id, '2co', '');
						
						
					} elseif ($product_type=='S') {
						JB_pend_subscription_invoice ($cart_order_id, '2co', '');
						
					} elseif ($product_type=='M') {
						JB_pend_membership_invoice ($cart_order_id, '2co', '');
					}
					JBPLUG_do_callback('pay_trn_pending', $cart_order_id, $product_type);
					$label['payment_return_pending'] = str_replace("%PAYMENT_GW%", $this->name, $label['payment_return_pending']);	
					?>
					<center>
					<img src="<?php echo JB_SITE_LOGO_URL; ?>">
					<p><?php echo $label['payment_return_pending']; ?></p>
					</center>
					<?php
					
					
				}
				

			} else {

				JBPLUG_do_callback('pay_trn_verification_failed', $cart_order_id, $product_type);

				echo "Invalid.";
				echo "Invalid. Was this a demo transaction?"."Has does not match...: [$hash] != [$x_MD5_Hash] (original string: ".$md5_str.") ";
				JB__2co_mail_error ( "Has does not match...: [$hash] != [$x_MD5_Hash] (original string: ".$md5_str.") ");

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
