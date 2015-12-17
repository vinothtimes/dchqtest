<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";

include('login_functions.php');
JB_process_login(); 
JB_template_candidates_header();


JB_render_box_top(80,  $label['candidate_order_confirm']);

if ($_REQUEST['action']=='membership') {

	?>
	<table  width="100%">
	<tr>
	<td>
	<?php
	// check to see if there are unpaid orders of the same subscription_id
	$sql = "select * from membership_invoices where user_id=".jb_escape_sql($_SESSION['JB_ID'])." AND user_type='C' AND membership_id='".jb_escape_sql($_REQUEST['membership_id'])."' AND (status='in_cart'  OR status='Confirmed') ";

	$result = JB_mysql_query($sql);

	if (mysql_num_rows($result)>0) {
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		$invoice_id = $row['invoice_id'];

	}  else { // this is a new order, make a new invoice
		$invoice_id = JB_place_membership_invoice ($_SESSION['JB_ID'], $_REQUEST['membership_id']);
		
		

	}

	if ($invoice_id!==false) {
		// delete other in_cart orders

		$sql = "DELETE FROM membership_invoices WHERE  (`status`='in_cart' )  AND `invoice_id` <> '$invoice_id'  AND user_type = 'C' AND `user_id`='".jb_escape_sql($_SESSION['JB_ID'])."' ";
		$result = JB_mysql_query($sql);

		JB_display_membership_invoice($invoice_id);

		?>


		</td>
		<td width="100%">
		<p align="center">
		<input type="button" class="pay_button" value="<?php echo $label['c_membership_trn_confirm']; ?>" onClick="window.location='payment.php?action=membership&amp;invoice_id=<?php echo $invoice_id;?>&amp;membership_id=<?php echo $row['membership_id'];?>&amp;user_id=<?php echo $_SESSION['JB_ID'];?>&amp;confirm=yes'; this.disabled=true; ">
		</p>
	<?php
	} else {

		$JBMarkup->error_msg($label['can_order_nosel']);

	}

	?>

	</td>
	</tr>
	</table>
	<p>

	<a href="membership.php"><?php echo $label['membership_go_back']; ?></a>
	</p>
	<?php

}

JB_render_box_bottom();


JB_template_candidates_footer();;
?>