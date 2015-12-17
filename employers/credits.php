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

$cancel = (int) $_REQUEST['cancel'];
$action = $_REQUEST['action'];
$package_id = (int) $_REQUEST['package_id'];


// set fees flag
if ((JB_POSTING_FEE_ENABLED == 'YES') || (JB_PREMIUM_POSTING_FEE_ENABLED == 'YES')) {
		$_FEES_ENABLED = "YES";
}

if ($cancel_id != '' ) {
	JB_void_package_invoice($cancel_id);
}

if ($_FEES_ENABLED == "YES") {
	$posts_num = JB_get_num_posts_remaining($_SESSION['JB_ID']);
	$p_posts_num = JB_get_num_premium_posts_remaining($_SESSION['JB_ID']);

?>

<table style="margin: 0 auto; width:90%; border:0px" cellpadding="10" cellspacing="0"  >

  <tr>
    <td style="width: 50%" valign="top">
	<!-- left col -->
	<?php 
	if ((JB_POSTING_FEE_ENABLED == "YES") && ($posts_num > -1)) {	
		?>
		<h3><?php echo $label['package_std_head']; ?></h3>


		<p class="explanation_note"><?php echo $label['package_std_select'];?><p>

		<?php
		$PLM = JB_get_ListMarkupObject('JBProductListMarkup');
		

		$PLM->set_colspan(3);
		$PLM->open_form('form1', 'order.php?action=post');

		$PLM->list_start('std_posts', 'order_table');

		$PLM->list_head_open();

		$PLM->list_head_cell_open(); echo $label['package_std_option']; $PLM->list_head_cell_close();
		$PLM->list_head_cell_open(); echo $label['package_std_posts']; $PLM->list_head_cell_close();
		$PLM->list_head_cell_open(); echo $label['package_std_price']; $PLM->list_head_cell_close();
	
		$PLM->list_head_close();

		$sql = "SELECT * from packages where premium='N' order by posts_quantity, name  ASC";
		$result = JB_mysql_query ($sql);
		
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

			$PLM->set_values($row);

			$PLM->list_item_open();

	
			$PLM->list_cell_open();

			$PLM->product_selection('package_id', $row['package_id'], $row['name'] );
			
			$PLM->list_cell_close();
			$PLM->list_cell_open();
			//echo $row['posts_quantity'];
			$PLM->data_cell('posts_quantity');
			$PLM->list_cell_close();

			$PLM->list_cell_open();
			$PLM->data_cell('price');
			$PLM->list_cell_close();
	
			$PLM->list_item_close();

		}
		$PLM->list_end();

		$PLM->place_order_button($label['package_std_place_order']);
	
		$PLM->close_form();

	} elseif ($posts_num == -1) {

		echo "<p>".$label['std_post_unlimited']."</p>";

	}

	 
	if ((JB_PREMIUM_POSTING_FEE_ENABLED == "YES") && ($p_posts_num > -1)) {	
		if (JB_POSTING_FEE_ENABLED == "YES") {
			echo '<hr>'; // seperate the standard posts from premium posts
		}

		$PLM = &JB_get_ListMarkupObject('JBProductListMarkup'); 
		
		$PLM->set_colspan(3);
		

		?>

		
		<h3 style=" margin:0px"><?php echo $label['package_prm_head'];?></h3>
		<p class="explanation_note">
		<?php echo $label['package_prm_head2'];?><br>[<a href="" onclick="window.open('adsinfo.php', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=600,height=600,left=50,top=50');return false;"><b><?php echo $label['package_prm_readmore']; ?></b></a>]</p>
		<p class="explanation_note">
		<?php echo $label['package_prm_select']; ?>
		</p>
		<?php
		$PLM->open_form('form1', 'order.php?action=premium_post');
		$PLM->list_start('premium_posts', 'order_table');
		

		$PLM->list_head_open();
		$PLM->list_head_cell_open(); echo $label['package_prm_option']; $PLM->list_head_cell_close();
		$PLM->list_head_cell_open(); echo $label['package_prm_posts']; $PLM->list_head_cell_close();
		$PLM->list_head_cell_open(); echo $label['package_prm_price']; $PLM->list_head_cell_close();

		$PLM->list_head_close();

		$sql = "SELECT * from packages where premium='Y' order by posts_quantity, name ASC  ";
		$result = JB_mysql_query ($sql);	
		$checked = false;
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

			$PLM->set_values($row);

			$PLM->list_item_open();

			$PLM->list_cell_open();
			$PLM->product_selection('package_id', $row['package_id'], $row['name'] );
			$PLM->list_cell_close();

			$PLM->list_cell_open();
			$PLM->data_cell('posts_quantity');
			$PLM->list_cell_close();
			
			$PLM->list_cell_open(); 
			$PLM->data_cell('price');
			$PLM->list_cell_close();
			

			$PLM->list_item_close();

		}
		$PLM->list_end();

		$PLM->place_order_button($label['package_prm_place_order']);
		$PLM->close_form();

	} elseif ($p_posts_num == -1) {
		echo "<p>".$label['prem_post_unlimited']."</p>";

	}

	?>
	</td>
    <td width="50%" valign="top" style="border-width: 1;" >
	
	<!-- right col-->

	
	<?php
	JB_render_box_top(100,  $label['package_credit_balance']);
	if (JB_POSTING_FEE_ENABLED == "YES") {	
				
		echo $label['package_std_remain']; ?> <b><?php if ($posts_num > -1) { echo $posts_num; } else { echo $label['package_remain_ultd']; } ?></b><br>
		<?php
	}
	
	if (JB_PREMIUM_POSTING_FEE_ENABLED == "YES") {	
		echo $label['package_prm_remain'];?> <b><?php if ($p_posts_num > -1) { echo $p_posts_num; } else { echo $label['package_remain_ultd']; } ?></b>
		<?php

	}
	JB_render_box_bottom();
	?>
			
	
	<h3><?php echo $label['package_rcnt_tansactions'];?></h3>
	<?php
	$now = (gmdate("Y-m-d H:i:s"));
	$sql = "SELECT * FROM `package_invoices` WHERE employer_id='".jb_escape_sql($_SESSION['JB_ID'])."' AND  DATE_SUB('$now', INTERVAL 90 DAY) <= `invoice_date`  ORDER BY invoice_date DESC  ";
	$result = JB_mysql_query ($sql);	


	if (mysql_num_rows($result) > 0 ) {

		$OLM = &JB_get_ListMarkupObject('JBOrdersListMarkup'); 

		$OLM->list_start('credit_orders', 'order_table');

		$OLM->list_head_open();
		$OLM->list_head_cell_open(); echo $label['package_trn_date']; $OLM->list_head_cell_close();
		$OLM->list_head_cell_open(); echo $label['package_trn_id']; $OLM->list_head_cell_close();
		$OLM->list_head_cell_open(); echo $label['package_trn_item']; $OLM->list_head_cell_close();
		$OLM->list_head_cell_open(); echo $label['package_trn_status']; $OLM->list_head_cell_close();
		$OLM->list_head_cell_open(); echo $label['package_trn_amount']; $OLM->list_head_cell_close();
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
			
			if (strtolower($row['status'])=='confirmed') {

				$OLM->status_link('payment.php?action=post&amp;invoice_id='.$row['invoice_id'].'&amp;package_id='.$row['package_id'].'&amp;employer_id='.$_SESSION['JB_ID'].'&amp;confirm=yes', $label['package_invoice_awaiting']);
				
			}elseif ($row['status']=='in_cart') {
				$OLM->status_link('order.php?action=post&amp;invoice_id='.$row['invoice_id'].'&amp;package_id='.$row['package_id'].'&amp;employer_id='.$_SESSION['JB_ID'], $label['package_invoice_confirm']);
				
			}elseif ((strtolower($row['status'])=='pending') && ($row['reason']=='jb_credit_advanced')) {
				$OLM->status_link('payment.php?action=post&amp;invoice_id='.$row['invoice_id'].'&amp;package_id='.$row['package_id'].'&amp;employer_id='.$_SESSION['JB_ID'].'&amp;confirm=yes', $label['invoice_stat_pending_unpaid']);
				
			} 
			$OLM->list_cell_close();

			$OLM->list_cell_open(); 
			$OLM->data_cell('amount');
			$OLM->list_cell_close();
			$OLM->list_item_close();
		

		}
		$OLM->list_end();

	} else {

		echo $label['package_trn_no_data'];
		
		
	}
	?>
	</td>
  </tr>
</table>


<?php


} 

JB_template_employers_footer(); 

?>