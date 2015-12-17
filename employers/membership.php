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


######################################################

function membership_plan_list () {

	global $label;

	$PLM = &JB_get_ListMarkupObject('JBProductListMarkup'); // get the sublass of JBListMarkup.php, defined in that template file

	$PLM->list_heading($label['emp_member_header']);
	$PLM->list_sub_heading($label['emp_member_sub_head']);

	$PLM->set_colspan(3);
	$PLM->open_form('form1', 'order.php?action=membership');

	$PLM->list_start('membership_plans', 'order_table');

	$PLM->list_head_open();

	$PLM->list_head_cell_open(); echo $label['emp_member_option']; $PLM->list_head_cell_close();
	$PLM->list_head_cell_open(); echo $label['emp_member_price']; $PLM->list_head_cell_close();
	$PLM->list_head_cell_open(); echo $label['emp_member_descr']; $PLM->list_head_cell_close();
	
	$PLM->list_head_close();
			
	$PLM->list_head_close();
	// employer memberships..
	$sql = "SELECT * from memberships WHERE type='E' order by price ASC, name ";
	$result = JB_mysql_query ($sql);	
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		$PLM->set_values($row);

		$PLM->list_item_open();

	
		$PLM->list_cell_open('nowrap');
		$PLM->product_selection('membership_id', $row['membership_id'], $row['name'] );
		$PLM->list_cell_close();
		
		$PLM->list_cell_open('nowrap');
		$PLM->data_cell('price');
		$PLM->list_cell_close();

		$PLM->list_cell_open('fullwidth');
		$PLM->product_label($row['membership_id']);
		$PLM->list_cell_close();
		
	
		$PLM->list_item_close();
	

	}
	$PLM->list_end();

	$PLM->place_order_button($label['emp_member_placeorder']);
	
	$PLM->close_form();

}

#######################################################
$cancel = $_REQUEST['cancel'];
$action = $_REQUEST['action'];
$membership_id = $_REQUEST['membership_id'];
$view_cart = $_REQUEST['view_cart'];

// set fees flag
if ((JB_EMPLOYER_MEMBERSHIP_ENABLED == 'YES') ) {
		$_FEES_ENABLED = "YES";
}

if ($cancel != '' ) {
	JB_void_membership_invoice($cancel);
}

if ($_FEES_ENABLED == "YES") {

	
	JB_render_box_top(90, $label['emp_member_your']);

	$sql = "SELECT * FROM employers WHERE `ID`='".jb_escape_sql($_SESSION['JB_ID'])."' AND membership_active='Y' ";
	$result = JB_mysql_query ($sql) or die (mysql_error());

	if (mysql_num_rows($result) > 0) {


		$row = jb_get_active_membership_invoice($_SESSION['JB_ID']);

		if (!$row) {
			// membership is enabled, but there is no order
			$row = array(
				'invoice_id' => '-ADMIN',
				'item_name' => 'Membership Enabled by Admin',
				'status' => 'Completed',
				'member_date' => date('Y-m-d'),
				'months_duration' => 0

			);

		}


		if ($row) {

			$SLM = &JB_get_ListMarkupObject('JBMembershipStatusMarkup'); // $SLM = new JBMembershipStatusMarkup();


			
			$SLM->set_values($row);

			$SLM->list_start('active_membership', 'order_table');

			$SLM->list_head_open();

			$SLM->list_cell_open('rowspan:3');

			$SLM->membership_details();

			$SLM->list_cell_close();

			$SLM->list_head_cell_open(); echo $label['emp_member_date']; $SLM->list_head_cell_close();
			$SLM->list_head_cell_open(); echo $label['emp_member_duration']; $SLM->list_head_cell_close();
			$SLM->list_head_cell_open(); echo $label['emp_member_ends']; $SLM->list_head_cell_close();
			$SLM->list_head_cell_open(); echo $label['emp_member_start'];$SLM->list_head_cell_close();
			$SLM->list_head_close();

			$SLM->list_item_open();
			$SLM->list_cell_open(); echo JB_get_formatted_date($row['member_date']); $SLM->list_cell_close();
			$SLM->list_cell_open(); 

			if ($row['months_duration']=='0') {
				echo $label['member_membership_forever'];
			} else {
				
				echo $row['months_duration']; 
					if ($row['months_duration']>1) {

					echo $label['member_months_plural'];
					
				} else {
					echo $label['member_months_singular'];
				}

			}
		
			$SLM->list_cell_close();
			$SLM->list_cell_open();
			if ($row['months_duration']=='0') {
				echo $label['member_membership_not_end'];

			} else {
				echo JB_get_formatted_date($row['member_end']); 
				
			}
			$SLM->list_cell_close();
			$SLM->list_cell_open(); 
			if ($row['status']=='member_cancel') { 
				echo $label['emp_member_cancel']; 
			} else { 
				echo JB_get_invoice_status_label($row['status']); 
			}
			$SLM->list_cell_close();
			$SLM->list_item_close();
			$SLM->list_item_open();
			$SLM->list_cell_open('colspan:4');
			
			if (($row['status']=='Completed') || (($row['reason']=='jb_credit_advanced') && ($row['status']=='Pending'))) {
				echo $label['emp_member_active'];
			}
			$SLM->list_cell_close();
			$SLM->list_item_close();
			$SLM->list_end();

		}

	} else {
		membership_plan_list (); 	
	}
	JB_render_box_bottom();
?>
<p>
<table style="margin: 0 auto; width:90%; border:0px"  cellpadding="10" cellspacing="0"  >
<tr>

<td colspan="2">

</td>

</tr>
  <tr>
    <td width="50%" valign="top">
	<!-- left col -->
	
				
<p>

<?php 
	JB_render_box_top(100, $label['emp_member_statusinf']);
	
	echo $label['e_membership_status_info_list']; ?>
	<?php
	JB_render_box_bottom();
	?></p>
	</td>
    <td width="50%" valign="top">
	
	<!-- right col-->				
	<?php
	JB_render_box_top(100, $label['emp_member_recent']);
	$now = (gmdate("Y-m-d H:i:s"));
	$sql = "SELECT * FROM `membership_invoices` WHERE user_id='".jb_escape_sql($_SESSION['JB_ID'])."' AND user_type='E' AND DATE_SUB('$now', INTERVAL 90 DAY) <= `invoice_date`  ORDER BY invoice_date DESC ";
	$result = JB_mysql_query ($sql);	

	if (mysql_num_rows($result) > 0 ) {

		$OLM = &JB_get_ListMarkupObject('JBOrdersListMarkup'); // new JBOrdersListMarkup();

		$OLM->list_start('membership_orders', 'order_table');

		$OLM->list_head_open();
		
		$OLM->list_head_cell_open(); echo $label['emp_member_date']; $OLM->list_head_cell_close();
		$OLM->list_head_cell_open(); echo $label['emp_member_id']; $OLM->list_head_cell_close();
		$OLM->list_head_cell_open(); echo $label['emp_member_item']; $OLM->list_head_cell_close();
		$OLM->list_head_cell_open(); echo $label['emp_member_stat']; $OLM->list_head_cell_close();
		$OLM->list_head_cell_open(); echo $label['emp_member_amount']; $OLM->list_head_cell_close();

		$OLM->list_head_close();
	
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

			$OLM->set_values($row);

			$OLM->list_item_open();


			$OLM->list_cell_open();
			$OLM->data_cell('invoice_date');
			$OLM->list_cell_close();

			$OLM->list_cell_open(); 
			$OLM->data_cell('invoice_id');
			$OLM->list_cell_close();

			$OLM->list_cell_open();
			$OLM->data_cell('item_name');
			$OLM->list_cell_close();

			$OLM->list_cell_open();
		
			$OLM->data_cell('status');

			if ($row['status']=='Confirmed') {
				$OLM->status_link('payment.php?action=membership&amp;invoice_id='.$row['invoice_id'].'&amp;membership_id='.$row['membership_id'].'&amp;user_id='.$_SESSION['JB_ID'].'&amp;confirm=yes', $label['emp_member_await']);
				
			}
			elseif ($row['status']=='in_cart') {
				$OLM->status_link('order.php?action=membership&amp;invoice_id='.$row['invoice_id'].'&amp;membership_id='.$row['membership_id'].'&amp;user_id='.$_SESSION['JB_ID'].'', $label['emp_member_confirm']);
				
			}

			elseif ((strtolower($row['status'])=='pending') && ($row['reason']=='jb_credit_advanced')) {
				$OLM->status_link('payment.php?action=membership&amp;invoice_id='.$row['invoice_id'].'&amp;membership_id='.$row['membership_id'].'&amp;user_id='.$_SESSION['JB_ID'].'&amp;confirm=yes', $label['invoice_stat_pending_unpaid']);
				
			} 

			JBPLUG_do_callback('emp_membership_order_status', $row);

		
		
			$OLM->list_cell_close();

			$OLM->list_cell_open();
			$OLM->data_cell('amount'); 
			$OLM->list_cell_close();
			
			$OLM->list_item_close();
	

		}
		$OLM->list_end();
	
	} else {

		echo $label['emp_member_nodata'];
		
	}
	JB_render_box_bottom();
	?>
	
	</td>
  </tr>
</table>


<?php


} 

JB_template_employers_footer(); 

?>