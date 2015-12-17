<?php
// Copyright 2010 Jamit Software Limited
// 


// include all the payment modules

$_PAYMENT_OBJECTS = array();

$dh = opendir (dirname(__FILE__));
$file='';
while (($file = readdir($dh)) !== false) {
	
	if (($file != '.') && ($file != '..') && ($file != 'test.php') && (strpos($file, ".php")>0) && ($file != "payment_manager.php") && $DB_ERROR==''){
	   // echo "$file<br>\n";
	   include (dirname(__FILE__).DIRECTORY_SEPARATOR.$file);

	}
 }
closedir($dh);
if ($_REQUEST['action']== 'save') {

	$obj = $_PAYMENT_OBJECTS[$_REQUEST['pay']];
	$obj->save_config();
	
}

if ($_REQUEST['action']== 'install') {

	$obj = $_PAYMENT_OBJECTS[$_REQUEST['pay']];
	$obj->install();
	// reload object
	$_PAYMENT_OBJECTS[$_REQUEST['pay']] = new $_REQUEST['pay'];


}

if ($_REQUEST['action']== 'uninstall') {

	$obj = $_PAYMENT_OBJECTS[$_REQUEST['pay']];
	$obj->uninstall();

}

if ($_REQUEST['action']== 'enable') {

	$obj = $_PAYMENT_OBJECTS[$_REQUEST['pay']];
	$obj->enable();


}


if ($_REQUEST['action']== 'disable') {

	$obj = $_PAYMENT_OBJECTS[$_REQUEST['pay']];
	$obj->disable();


}


// Used by admin/payments.php

function JB_list_avalable_payments () {



	global $_PAYMENT_OBJECTS;

	?>

	<script type="text/javascript">

	function confirmLink(theLink, theConfirmMsg) {

       if (theConfirmMsg == '' || typeof(window.opera) != 'undefined') {
           return true;
       }

       var is_confirmed = confirm(theConfirmMsg + '\n');
       if (is_confirmed) {
           theLink.href += '&is_js_confirmed=1';
       }

       return is_confirmed;
	}
	</script>
	<table border="0">
	<tr><td  valign="top">
	<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" width="400" >
			<tr bgColor="#eaeaea">
				<td><b><font size="2">Payment Module</b></font></td>
				<td><b><font size="2">Description</b></font></td>
				<td><b><font size="2">Status</b></font></td>
				<td><b><font size="2">&nbsp;</b></font></td>
				
			</tr>
	<?php

	$enabled_flag = false;

	foreach ($_PAYMENT_OBJECTS as $obj_key => $obj) {
		

		?>

		<tr <?php if ($obj_key==$_REQUEST['pay'])  { echo ' bgColor="#FFFF99" ';} else echo ' bgColor="#ffffff" '; ?>  onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);">
			<td><font size="2"><a href="<?php echo $_SERVER['PHP_SELF'];?>?pay=<?php echo $obj_key;?>"><?php echo $obj->name; ?></a></font></td>
			<td><font size="2"><?php echo $obj->description; ?></font></td>
			<td><font size="2"><?php

				if (!$obj->is_installed()) {
					echo "<font color='red'>Not Installed</font>";

				} else {

					if ($obj->is_enabled()) {
						echo "<font color='green'><IMG SRC='../admin/active.gif' WIDTH='16' HEIGHT='16' BORDER='0' ALT='Enabled'></font>";
						$enabled_flag = true;

					} else {
						echo "<font color='red'><IMG SRC='../admin/notactive.gif' WIDTH='16' HEIGHT='16' BORDER='0' ALT='Not Enabled'></font>";

					}

				}
			
			?></font></td>
			<td nowrap><font size="2"><?php

			if ($obj_key==$_REQUEST['pay']) {
				if (!$obj->is_installed()) {
					echo "<input type='button' style='font-size: 10px;' value='Install' onclick=\"if (!confirmLink(this, 'Install, are you sure?')) return false;window.location='".$_SERVER['PHP_SELF']."?pay=".$obj_key."&action=install'\">";

				} else {

					if ($obj->is_enabled()) {
					//	echo "Enabled";
						echo "<input type='button' style='font-size: 10px;' value='Disable' onclick=\"if (!confirmLink(this, 'Disable, are you sure?')) return false;window.location='".$_SERVER['PHP_SELF']."?pay=".$obj_key."&action=disable'\">";

					} else {
						//echo "Not Enabled";
						echo "<input style='font-size: 10px;' type='button' value='Enable' onclick=\"if (!confirmLink(this, 'Enable, are you sure?')) return false; window.location='".$_SERVER['PHP_SELF']."?pay=".$obj_key."&action=enable'\">";

					}

					echo " &nbsp; <input style='font-size: 10px;' type='button' value='Uninstall' onclick=\" if (!confirmLink(this, 'Uninstall, are you sure?')) return false; window.location='".$_SERVER['PHP_SELF']."?pay=".$obj_key."&action=uninstall'\">";

				}


				if ($obj->is_installed()) {
					//	$obj->config_form();
				}


			} else {

				if ($obj->is_installed()) {
					echo "<input style='font-size: 10px;' type='button' value='Configure' onclick=\"window.location='".$_SERVER['PHP_SELF']."?pay=".$obj_key."'\">";
				} else {

					echo "<input type='button' style='font-size: 10px;' value='Install' onclick=\"if (!confirmLink(this, 'Install, are you sure?')) return false;window.location='".$_SERVER['PHP_SELF']."?pay=".$obj_key."&action=install'\">";



				}


			}
				
			
			?></font></td>
				
		</tr>
		
		<?php

	}

	?>
	</table>

	</td>
		<td valign="top">
		<?php
			
			if ($_REQUEST['pay']!='') {
			
				if ($_PAYMENT_OBJECTS[$_REQUEST['pay']]->is_installed()) {
					$_PAYMENT_OBJECTS[$_REQUEST['pay']]->config_form();
				}	

			}
				
				?>
		</td>
		</tr>
		</table>
	<?php

	
	return $enabled_flag;

}


#######################################
// used by employers/payment.php and myjobs/payment.php

function JB_payment_option_list($invoice_row, $product_type) {

	global $_PAYMENT_OBJECTS;
	global $label;

	$PLM = JB_get_ListMarkupObject('JBPaymentOptionListMarkup');

	$invoice_row['product_type'] = $product_type;
	$PLM->set_invoice_row($invoice_row);

	$PLM->open_form('pay_select');
	$PLM->list_start('payment_list', 'order_table');

	$PLM->list_head_open();
	$PLM->list_head_cell_open(); $PLM->list_head_cell_close();
	$PLM->list_head_cell_open(); echo $label['payment_mab_name']; $PLM->list_head_cell_close();
	$PLM->list_head_cell_open(); echo $label['payment_man_descr']; $PLM->list_head_cell_close();
	
	$PLM->list_head_close();

	$enabled_flag;
	foreach ($_PAYMENT_OBJECTS as $obj_key => $obj) {

		$PLM->set_values($obj);

		if ($obj->is_enabled()){
			$PLM->list_item_open();

			$PLM->list_cell_open(); $PLM->radio_button(); $PLM->list_cell_close();
			$PLM->list_cell_open(); $PLM->data_cell('name'); $PLM->list_cell_close();
			$PLM->list_cell_open(); $PLM->data_cell('description'); $PLM->list_cell_close();
			
			$PLM->list_item_close();
			$enabled_flag = 1;
		}
	}
	if (!$enabled_flag) echo '<span style="color:red">It appears that there are no payment modules enabled. Please see Admin-&gt;Payment Modules</span>';

	$invoice_id = $invoice_row['invoice_id'];

	JBPLUG_do_callback('payment_option_list', $invoice_id, $product_type);

	$PLM->selection_row_open();
	$PLM->select_button();

	if (($invoice_row['status']=='in_cart') || (strtolower($invoice_row['status'])=='confirmed')) {
		$PLM->cancel_button($invoice_id, $product_type);
	}
	$PLM->selection_row_close();
	$PLM->list_end();
	$PLM->close_form();

}

####################################
# Called from thanks.php page.
# The call is then delegated to the specific class process_payment_return() function

function JB_process_payment_return($className) {

	global $_PAYMENT_OBJECTS;

	$obj = $_PAYMENT_OBJECTS[$className];


	if (isset($obj)) {

		$obj->process_payment_return();

	} else {
		echo "Warning: payment_manager.php detected that the return URL is incorrect for this payment method.";

	}



}


?>