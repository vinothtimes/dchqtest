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
JB_template_employers_header(); 
JB_render_box_top(80,  $label['employer_credits_order_confirm']);


if (($_REQUEST['action']=='post') || ($_REQUEST['action']=='premium_post')) {

	?>
	<table  width="100%" >
	<tr>
		<td>
		<?php

		// check to see if there are in_cart orders of the same package_id

		$sql = "select * from package_invoices where employer_id='".jb_escape_sql($_SESSION['JB_ID'])."' AND package_id='".jb_escape_sql($_REQUEST['package_id'])."' AND (status='in_cart' ) ";

		$result = JB_mysql_query($sql) or die (mysql_error());

		if (mysql_num_rows($result)>0) {

			$row = mysql_fetch_array($result, MYSQL_ASSOC);
			$invoice_id = $row['invoice_id'];

		}  else { // this is a new order, make a new invoice
			$invoice_id = JB_place_package_invoice ($_SESSION['JB_ID'], $_REQUEST['package_id']);

		}

		if ($invoice_id!==false) {
			
		
			// delete other selected orders

			$sql = "DELETE FROM package_invoices WHERE  (`status`='in_cart' ) AND `invoice_id` != '$invoice_id' AND employer_id='".jb_escape_sql($_SESSION['JB_ID'])."' ";
			//echo $sql;
			$result = JB_mysql_query($sql) or die ($sql.mysql_error());


			$sql = "select * from packages WHERE package_id='".jb_escape_sql($_REQUEST['package_id'])."'   ";
			$result = JB_mysql_query($sql) or die (mysql_error());
			$row = mysql_fetch_array($result, MYSQL_ASSOC);

			JB_display_package_invoice($invoice_id);

			?>

			</td>
			<td width="50%">
			<p>
			<input type="button" class="confirm_order_button" value="<?php echo $label['package_invoice_confirm']; ?>" onClick="window.location='payment.php?action=post&invoice_id=<?php echo jb_escape_html($invoice_id);?>&package_id=<?php echo $row['package_id'];?>&employer_id=<?php echo $_SESSION['JB_ID'];?>&confirm=yes'; this.disabled=true; ">
			</p>

			<?php

		} else {

			$JBMarkup->error_msg($label['emp_plan_order_nosel']);
		}

		?>
	</td>
		
	</tr>
	</table>

   <p><a href="credits.php"><?php echo $label['package_cart_goback']; ?></A></p>

<?php


} elseif ($_REQUEST['action']=='subscription') {

	?>
	

	<table width="100%" >
	<tr>
	<td width="50%">
	<?php

	// check to see if there are in_cart orders of the same subscription_id

	$sql = "select * from subscription_invoices where employer_id=".jb_escape_sql($_SESSION['JB_ID'])." AND subscription_id='".jb_escape_sql($_REQUEST['subscription_id'])."' AND (status='in_cart' ) ";

	
	$result = JB_mysql_query($sql) or die (mysql_error());

	if (mysql_num_rows($result)>0) {

		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		$invoice_id = $row['invoice_id'];

	}  else { // this is a new order, make a new invoice
		$invoice_id = JB_place_subscription_invoice ($_SESSION['JB_ID'], $_REQUEST['subscription_id']);

	}

	if ($invoice_id!==false) {
		// delete other in_cart orders

		$sql = "DELETE FROM subscription_invoices WHERE  (`status`='in_cart' )  AND `invoice_id` != '$invoice_id'  AND `employer_id`='".jb_escape_sql($_SESSION['JB_ID'])."' ";
		$result = JB_mysql_query($sql) or die ($sql.mysql_error());


		$sql = "select * from subscriptions WHERE subscription_id='".jb_escape_sql($_REQUEST['subscription_id'])."' ";
		$result = JB_mysql_query($sql) or die (mysql_error());
		$row = mysql_fetch_array($result, MYSQL_ASSOC);

		JB_display_subscription_invoice($invoice_id);

		?> &nbsp;</td>

		<td width="50%">
			<p align="center">
			<input type="button" class="confirm_order_button" value="<?php echo $label['subscription_invoice_confirm']; ?>" onClick="window.location='payment.php?action=subscription&invoice_id=<?php echo jb_escape_html($invoice_id);?>&subscription_id=<?php echo $row['subscription_id'];?>&employer_id=<?php echo $_SESSION['JB_ID'];?>&confirm=yes'; this.disabled=true; ">
		</p>
		<?php

	} else {

		$JBMarkup->error_msg($label['emp_sub_order_nosel']);
	}

	?>
	</td>
	</tr>
	</table>
	<p>
	<a href="subscriptions.php"><?php echo $label['subscription_go_back']; ?></A>
	</p>

	<?php


} elseif ($_REQUEST['action']=='membership') {

	?>
	<table  width="100%">
	<tr>
	<td>
	<?php

	
	// check to see if there are unpaid orders of the same subscription_id

	$sql = "select * from membership_invoices where user_id=".jb_escape_sql($_SESSION['JB_ID'])." AND user_type='E' AND membership_id='".jb_escape_sql($_REQUEST['membership_id'])."' AND (status='in_cart'  ) ";

	
	$result = JB_mysql_query($sql) or die (mysql_error());

	if (mysql_num_rows($result)>0) {

		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		$invoice_id = $row['invoice_id'];

	}  else { // this is a new order, make a new invoice
		$invoice_id = JB_place_membership_invoice ($_SESSION['JB_ID'], $_REQUEST['membership_id']);

	}

	if ($invoice_id!==false) {

		// delete other selected orders

		$sql = "DELETE FROM membership_invoices WHERE  (`status`='in_cart' )  AND `invoice_id` != '$invoice_id'  AND user_type = 'E' AND `user_id`='".jb_escape_sql($_SESSION['JB_ID'])."' ";
		$result = JB_mysql_query($sql) or die ($sql.mysql_error());



		JB_display_membership_invoice($invoice_id);

		?>


		</td>

		<td width="100%">
		<center>
		<input type="button" class="confirm_order_button" value="<?php echo $label['emp_member_confirm']; ?>" onClick="window.location='payment.php?action=membership&invoice_id=<?php echo jb_escape_html($invoice_id);?>&membership_id=<?php echo $row['membership_id'];?>&user_id=<?php echo $_SESSION['JB_ID'];?>&confirm=yes'; this.disabled=true; ">
		</center>

		
	<?php

	} else {

		$JBMarkup->error_msg($label['emp_mem_order_nosel']);
	}

	?>
	</td>
	</tr>
	</table>

	<p>

	<a href="membership.php"><?php echo $label['membership_go_back']; ?></A>
	</p>

	<?php


}

JB_render_box_bottom();


JB_template_employers_footer();
?>