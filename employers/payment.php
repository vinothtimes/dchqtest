<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";?>

<?php include('../payment/payment_manager.php'); ?>

<?php include('login_functions.php'); ?>
<?php JB_process_login(); ?>
<?php JB_template_employers_header(); ?>

<?php


if ($_REQUEST['payment_cancel']) {

	
	if ($_REQUEST['product_type']=='P') {

		$_REQUEST['confirm']='';
		$invoice_id = (int) $_REQUEST['invoice_id'];

		$sql = "DELETE FROM package_invoices WHERE  `invoice_id`='".jb_escape_sql($invoice_id)."' AND (`status` = 'in_cart' OR `status`='Confirmed') AND employer_id='".jb_escape_sql($_SESSION['JB_ID'])."' ";
		
		$result = JB_mysql_query($sql) or die ($sql.mysql_error());

		$JBMarkup->ok_msg($label['payment_cancelled']);

	}

	if ($_REQUEST['product_type']=='S') {

		$_REQUEST['confirm']='';
		$invoice_id = (int) $_REQUEST['invoice_id'];

		$sql = "DELETE FROM subscription_invoices WHERE  `invoice_id`='".jb_escape_sql($invoice_id)."' AND (`status` = 'in_cart' OR `status`='Confirmed') AND employer_id='".jb_escape_sql($_SESSION['JB_ID'])."' ";
		
		$result = JB_mysql_query($sql) or die ($sql.mysql_error());

		$JBMarkup->ok_msg($label['payment_sub_cancelled']);

	}

	if ($_REQUEST['product_type']=='M') {

		$_REQUEST['confirm']='';
		$invoice_id = (int) $_REQUEST['invoice_id'];

		$sql = "DELETE FROM membership_invoices WHERE  `invoice_id`='".jb_escape_sql($invoice_id)."' AND (`status` = 'in_cart' OR `status`='Confirmed') AND user_id='".jb_escape_sql($_SESSION['JB_ID'])."' AND user_type='E'";
	
		$result = JB_mysql_query($sql) or die ($sql.mysql_error());
		
		$JBMarkup->ok_msg($label['payment_mem_cancelled']);
	}


}

if ($_REQUEST['pay_method']!='') {

	$_REQUEST['confirm']='';
	echo "<p style='payment_block'>";
	$_PAYMENT_OBJECTS[$_REQUEST['pay_method']]->payment_button($_REQUEST['invoice_id'], $_REQUEST['product_type']);
	echo "</p>";
	
	JB_update_payment_method ($_REQUEST['product_type'], $_REQUEST['invoice_id'], $_REQUEST['pay_method']);


}

if (($_REQUEST['confirm']=='yes') && ($_REQUEST['action']=='post')) {

	

	// confirm the Invoice
	$_REQUEST['product_type']='P';

	$invoice_row = JB_confirm_package_invoice($_REQUEST['invoice_id']);

	echo "<p style='payment_block'>";
	JB_render_box_top(80,  $label['payment_please_select']);
	$product_type = 'P'; # P for Post
	JB_payment_option_list($invoice_row, $product_type);
	JB_render_box_bottom();
	echo "</p>";



}

if (($_REQUEST['confirm']=='yes') && ($_REQUEST['action']=='subscription')) {

	// confirm the Invoice
	$_REQUEST['product_type']='S';
	$invoice_row = JB_confirm_subscription_invoice($_REQUEST['invoice_id']);
	

	echo "<p style='payment_block'>";
	JB_render_box_top(80,  $label['payment_please_select']);
	$product_type = 'S'; # S for subscription
	JB_payment_option_list($invoice_row, $product_type);
	JB_render_box_bottom();
	echo "</p>";



}


if (($_REQUEST['confirm']=='yes') && ($_REQUEST['action']=='membership')) {


	// confirm the Invoice
	$_REQUEST['product_type']='M';
	$invoice_row = JB_confirm_membership_invoice($_REQUEST['invoice_id']);

	echo "<p style='payment_block'>";
	JB_render_box_top(80,  $label['payment_please_select']);
	$product_type = 'M'; # S for Membership
	JB_payment_option_list($invoice_row, $product_type);
	JB_render_box_bottom();
	echo "</p>";



}



JB_template_employers_footer(); 




?>