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




######################################################

function membership_plan_list () {

	global $label;

	global $label;

	$PLM = &JB_get_ListMarkupObject('JBProductListMarkup'); // sublass of JBListMarkup.php, defined in that template file

	$PLM->list_heading($label['c_membership_heading']);
	$PLM->list_sub_heading($label['c_membership_description']);

	$PLM->set_colspan(3);
	$PLM->open_form('form1', 'order.php?action=membership');

	$PLM->list_start('membership_plans', 'order_table');

	$PLM->list_head_open();

	$PLM->list_head_cell_open(); echo $label['c_membership_opt_col']; $PLM->list_head_cell_close();
	$PLM->list_head_cell_open(); echo $label['c_membership_price_col']; $PLM->list_head_cell_close();
	$PLM->list_head_cell_open(); echo $label['c_membership_desc_col']; $PLM->list_head_cell_close();
	
			
	$PLM->list_head_close();
		// employer memberships..
	$result = JB_mysql_query ("SELECT * from memberships WHERE type='C' order by price ASC, name ");	
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

	$PLM->place_order_button($label['c_membership_button_order']);
	
	$PLM->close_form();
	
}



#######################################################
$cancel = $_REQUEST['cancel'];
$action = $_REQUEST['action'];
$membership_id = $_REQUEST['membership_id'];
$view_cart = $_REQUEST['view_cart'];

// set fees flag
if ((JB_CANDIDATE_MEMBERSHIP_ENABLED == 'YES') ) {
		$_FEES_ENABLED = "YES";
}

if ($cancel != '' ) {
	JB_void_membership_invoice($cancel);
}

if ($_FEES_ENABLED == "YES") {


JB_render_box_top(90, $label['c_membership_your_mem']);

$sql = "SELECT * FROM users WHERE `ID`='".jb_escape_sql($_SESSION['JB_ID'])."' AND membership_active='Y' ";
$result = JB_mysql_query ($sql) or die (mysql_error());



if (mysql_num_rows($result) > 0) {

	$row = jb_get_active_membership_invoice($_SESSION['JB_ID'], 'C');

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


		
		$SLM = &JB_get_ListMarkupObject('JBMembershipStatusMarkup');

		$SLM->set_values($row);

		$SLM->list_start('active_membership', 'order_table');

		$SLM->list_head_open();

		$SLM->list_cell_open('rowspan:3');

		$SLM->membership_details();

		$SLM->list_cell_close();

		$SLM->list_head_cell_open(); echo $label['c_membership_history_date']; $SLM->list_head_cell_close();
		$SLM->list_head_cell_open(); echo $label['c_membership_history_duration']; $SLM->list_head_cell_close();
		$SLM->list_head_cell_open(); echo $label['c_membership_history_ends']; $SLM->list_head_cell_close();
		$SLM->list_head_cell_open(); echo $label['c_membership_history_status']; $SLM->list_head_cell_close();
		$SLM->list_head_close();

		$SLM->list_item_open();
		$SLM->list_cell_open(); echo JB_get_formatted_date($row['member_date']); $SLM->list_cell_close();
		$SLM->list_cell_open(); 

		if ($row['months_duration']=='0') {
			echo $label['c_membership_membership_forever'];
		} else {
			
			echo $row['months_duration']; ?> <?php 
				if ($row['months_duration']>1) {

				echo $label['c_membership_months_plural'];
				
			} else {
				echo $label['c_membership_months_singular'];
			}

		}
	
		$SLM->list_cell_close();
		$SLM->list_cell_open();
		if ($row['months_duration']=='0') {
			echo $label['c_membership_membership_not_end'];

		} else {
			echo JB_get_formatted_date($row['member_end']); 
			
		}
		$SLM->list_cell_close();
		$SLM->list_cell_open();
		if ($row['status']=='member_cancel') { 
			echo $label['c_membership_cancelled']; 
		} else { 
			echo JB_get_invoice_status_label($row['status']); 
		} 
		$SLM->list_cell_close();
		$SLM->list_item_close();
		$SLM->list_item_open();
		$SLM->list_cell_open('colspan:4');
		if (($row['status']=='Completed') || (($row['reason']=='jb_credit_advanced') && ($row['status']=='Pending'))) {
			echo $label['c_membership_active'] ;
		}
		$SLM->list_cell_close();
		$SLM->list_item_close();

	} 
	
} else {
	
	membership_plan_list (); 	
}
JB_render_box_bottom();
?>
<p>
<table align="center" border="0" cellpadding="10" cellspacing="0" width="90%" >
<tr>

<td colspan="2">


</td>

</tr>

  <tr>
    <td width="50%" valign="top">
	<!-- left col -->
	
				
<p>

<?php 
	JB_render_box_top(100, $label['c_membership_stausinfo']);
	
	echo $label['e_membership_status_info_list']; ?></p>
	<?php
	JB_render_box_bottom();
	?>
	</td>
    <td width="50%" valign="top">
	
	<!-- right col-->				
	<?php
		JB_render_box_top(100, $label['c_membership_recnt_trn']);
		$now = (gmdate("Y-m-d H:i:s"));
		$sql = "SELECT * FROM `membership_invoices` WHERE user_id='".jb_escape_sql($_SESSION['JB_ID'])."' AND user_type='C' AND DATE_SUB('$now', INTERVAL 90 DAY) <= `invoice_date`  ORDER BY invoice_date DESC ";
		$result = JB_mysql_query ($sql);	

		if (mysql_num_rows($result) > 0 ) {

			$OLM = &JB_get_ListMarkupObject('JBOrdersListMarkup');// new JBOrdersListMarkup();

			$OLM->list_start('membership_orders', 'order_table');

			$OLM->list_head_open();


			$OLM->list_head_cell_open(); echo $label['c_membership_trn_date']; $OLM->list_head_cell_close();
			$OLM->list_head_cell_open(); echo $label['subscription_hist_id']; $OLM->list_head_cell_close();
			$OLM->list_head_cell_open(); echo $label['c_membership_trn_item']; $OLM->list_head_cell_close();
			$OLM->list_head_cell_open(); echo $label['c_membership_trn_status']; $OLM->list_head_cell_close();
			$OLM->list_head_cell_open(); echo $label['c_membership_trn_amount']; $OLM->list_head_cell_close();

	
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
				if ($row['status']=='in_cart') { 

					$OLM->status_link('order.php?action=membership&amp;invoice_id='.$row['invoice_id'].'&amp;membership_id='.$row['membership_id'].'&amp;user_id='.$_SESSION['JB_ID'].'', $label['emp_member_confirm']);
				}
				if ((strtolower($row['status'])=='pending') && ($row['reason']=='jb_credit_advanced')) { 

					$OLM->status_link('payment.php?action=membership&amp;invoice_id='.$row['invoice_id'].'&amp;membership_id='.$row['membership_id'].'&amp;user_id='.$_SESSION['JB_ID'].'&amp;confirm=yes', $label['invoice_stat_pending_unpaid']);
				}

				JBPLUG_do_callback('can_membership_order_status', $row);
	
			
				$OLM->list_cell_close();
		
				$OLM->list_cell_open();
				$OLM->data_cell('amount'); 
				$OLM->list_cell_close();
			
				$OLM->list_item_close();
			}
			$OLM->list_end();
		} else {

			echo $label['c_membership_nodata'];
		
		}
		JB_render_box_bottom();
		?>
	
	</td>
	</tr>
	</table>

	<?php


}

JB_template_candidates_footer();

?>