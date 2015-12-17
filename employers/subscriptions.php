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



JBEmployer::update_subscription_quota($_SESSION['JB_ID']); // This will update the subscription quotas, if the user is subscribed to the resume database.





######################################################
// subscription_plan_list () - lists all the available subscription plans
function subscription_plan_list () {

	global $label;

	$PLM = &JB_get_ListMarkupObject('JBProductListMarkup');// sublass of JBListMarkup.php, defined in that template file
 

	$PLM->list_heading($label['subscription_head1']);
	$PLM->list_sub_heading($label['subscription_head2']);

	

	$PLM->set_colspan(3);
	$PLM->open_form('form1', 'order.php?action=subscription');

	$PLM->list_start('subscription_plans', 'order_table');

	$PLM->list_head_open();
	$PLM->list_head_cell_open(); echo $label['subscription_option']; $PLM->list_head_cell_close();
	$PLM->list_head_cell_open(); echo $label['subscription_price_']; $PLM->list_head_cell_close();
	$PLM->list_head_cell_open(); echo $label['subscription_description_']; $PLM->list_head_cell_close();
	$PLM->list_head_close();

	$result = JB_mysql_query ("SELECT * from subscriptions order by price ASC, name ");	
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		$PLM->set_values($row);

		$PLM->list_item_open();

		$PLM->list_cell_open('nowrap');

		$PLM->product_selection('subscription_id', $row['subscription_id'], $row['name'] );


		$PLM->list_cell_close();
		
		$PLM->list_cell_open('nowrap');
		$PLM->data_cell('price');
		$PLM->list_cell_close();

		$PLM->list_cell_open('fullwidth');

		$PLM->product_label($row['subscription_id']);
	
		

		$PLM->line_break();
		
		if ($row['can_view_resumes']=='Y') {
			$str = $label['subscr_can_view_resumes'];

			if ($row['views_quota']>0) {
				$str .= $label['subscr_can_view_resumes_q'];
				$str = str_replace('%QUOTA%', $row['views_quota'], $str);
			}
			if (($row['can_view_blocked']=='Y') && (JB_FIELD_BLOCK_SWITCH=='YES')) {
				
				$str .= " ".$label['subscr_can_view_blocked'];
			}
			$PLM->product_tick($str);
		
		}

		if (($row['can_post']=='Y') && (JB_POSTING_FEE_ENABLED=='YES')) {
			if ($row['posts_quota']>0) {
				$str = $label['subscr_can_post_q'];
				$str = str_replace('%QUOTA%', $row['posts_quota'], $str);
			} else {
				$str = $label['subscr_can_post_unlimited'];
			}
			
			$PLM->product_tick($str);

		}

		if (($row['can_post_premium']=='Y') && (JB_PREMIUM_POSTING_FEE_ENABLED=='YES')) {
			if ($row['p_posts_quota']>0) {
				$str = $label['subscr_can_post_pr_q'];
				$str = str_replace('%QUOTA%', $row['p_posts_quota'], $str);
			} else {
				$str = $label['subscr_can_post_unlimited_pr'];
			}

			$PLM->product_tick($str);
			
		}

		$PLM->list_cell_close();
	
		

		$PLM->list_item_close();
	

	}

	$PLM->list_end();

	$PLM->place_order_button($label['subscription_add_to_cart']);
	

	$PLM->close_form();

}



#######################################################
$cancel = $_REQUEST['cancel'];
$action = $_REQUEST['action'];
$subscription_id = $_REQUEST['subscription_id'];
$view_cart = $_REQUEST['view_cart'];

// set fees flag
if ((JB_SUBSCRIPTION_FEE_ENABLED == 'YES') ) {
		$_FEES_ENABLED = "YES";
}

if ($cancel != '' ) {
	JBEmployer::void_subscription_invoice($cancel, $_SESSION['JB_ID']);

}

if ($_FEES_ENABLED == "YES") {


	JB_render_box_top(90, $label['subscription_sub_to_view']);

	$row = JBEmployer::get_active_subscription_invoice($_SESSION['JB_ID']);

	if ($row) {


		//$SLM = new JBSubscriptionStatusMarkup();
		$SLM = &JB_get_ListMarkupObject('JBSubscriptionStatusMarkup');

		$SLM->set_values($row);

		$SLM->list_start('active_subscriptions', 'order_table');

		$SLM->list_head_open();

		$SLM->list_cell_open('rowspan:3');

		$SLM->subscription_details();
		
		if ($row['can_view_resumes']=='Y') {

			$str = $label['subscr_can_view_resumes'];
			if ($row['views_quota']>0) {
				
				$str .= $label['subscr_can_view_resumes_q'];
				$str = str_replace('%QUOTA%', $row['views_quota'], $str);
			} 
			
			if (($row['can_view_blocked']=='Y') && (JB_FIELD_BLOCK_SWITCH=='YES')) {
				
				$str .= " ".$label['subscr_can_view_blocked'];
			}
			
			$SLM->product_tick($str);
		}

		if (($row['can_post']=='Y') && (JB_POSTING_FEE_ENABLED=='YES')) {
			if ($row['posts_quota']>0) {
				$str = $label['subscr_can_post_quota'];
				$str = str_replace('%QUOTA%', $row['posts_quota'], $str);
			} else {
				$str = $label['subscr_can_post_unlimited'];
			}
			$SLM->product_tick($str);

		}

		if (($row['can_post_premium']=='Y') && (JB_PREMIUM_POSTING_FEE_ENABLED=='YES')) {
			if ($row['p_posts_quota']>0) {
				$str = $label['subscr_can_prost_quota'];
				$str = str_replace('%QUOTA%', $row['p_posts_quota'], $str);
			} else {
				$str = $label['subscr_can_post_unlimited_pr'];
			}
			$SLM->product_tick($str);

		}

		 
		$SLM->list_cell_close();
		

		$SLM->list_head_cell_open(); echo $label['subscription_date']; $SLM->list_head_cell_close();
		$SLM->list_head_cell_open(); echo $label['subscription_duration']; $SLM->list_head_cell_close();
		$SLM->list_head_cell_open(); echo $label['subscription_until']; $SLM->list_head_cell_close();
		$SLM->list_head_cell_open(); echo $label['subscription_status']; $SLM->list_head_cell_close();

		$SLM->list_head_close();
		
		$SLM->list_item_open();

		$SLM->list_cell_open(); echo JB_get_formatted_date($row['subscr_date']); $SLM->list_cell_close(); 
		$SLM->list_cell_open(); 
		echo $row['months_duration']; 
	   
		if ($row['months_duration']>1) {
			echo $label['subscription_months_plural'];
		} else {
			echo $label['subscription_months_singular'];
		}
		$SLM->list_cell_close();
		
		$SLM->list_cell_open(); echo JB_get_formatted_date($row['subscr_end']); $SLM->list_cell_close();

		$SLM->list_cell_open(); 
		if ($row['status']=='subscr_cancel') { 
			echo $label['subscription_cancelled']; 
		} else { 
			echo $row['status']; 
		} 
		$SLM->list_cell_close();


		$SLM->list_item_close();

		$SLM->list_item_open();

		$SLM->list_cell_open('colspan:4');
		
			
		if (($row['status']=='Completed') || (($row['reason']=='jb_credit_advanced') && ($row['status']=='Pending'))) {
			
			$SLM->subscription_status_open();

			if ($row['views_quota']>0 || $row['posts_quota']>0 || $row['p_posts_quota']>0) {

				$t = $row['quota_timestamp'];

				// calculate timestamp for 1 month in the future

				$t_next_month = mktime(date('H', $t), date('i', $t), date('s', $t), date('n', $t)+1, date('j', $t), date('Y', $t));

				$label['subscription_quota'] = str_replace('%START_DATE%', date(JB_DATE_FORMAT, $t), $label['subscription_quota']);
				$label['subscription_quota'] = str_replace('%END_DATE%', date(JB_DATE_FORMAT, $t_next_month), $label['subscription_quota']);

				$SLM->subscription_status_line($label['subscription_quota']);
				
			} else {

				$label['subscription_quota_u'] = str_replace('%DATE%',  JB_get_formatted_date($row['subscr_end']), $label['subscription_quota_u']);

				$SLM->subscription_status_line($label['subscription_quota_u']);

			}

			if ($row['views_quota']>0) {

				
				$label['subscription_views_quota'] = str_replace('%QUOTA%', $row['views_quota'], $label['subscription_views_quota']);
				$label['subscription_views_quota'] = str_replace('%TOTAL%', $row['views_quota_tally'], $label['subscription_views_quota']);

				$SLM->subscription_status_line($label['subscription_views_quota']);

			}  elseif ($row['can_view_resumes']=='Y')  { // unlimited (0)

				$SLM->subscription_status_line($label['subscription_views_quota_u']);

			}

			if (($row['posts_quota']>0) && (JB_POSTING_FEE_ENABLED=='YES')) {

		
				$label['subscription_posts_quota'] = str_replace('%QUOTA%', $row['posts_quota'], $label['subscription_posts_quota']);
				$label['subscription_posts_quota'] = str_replace('%TOTAL%', $row['posts_quota_tally'], $label['subscription_posts_quota']);

				$SLM->subscription_status_line($label['subscription_posts_quota']);

			} elseif (($row['can_post']=='Y') && JB_POSTING_FEE_ENABLED=='YES') { // unlimited (0)
				
				$SLM->subscription_status_line($label['subscription_posts_quota_u']);
			}

			if (($row['p_posts_quota']>0) && (JB_PREMIUM_POSTING_FEE_ENABLED=='YES')) {
				
				$label['subscription_p_posts_quota'] = str_replace('%QUOTA%', $row['p_posts_quota'], $label['subscription_p_posts_quota']);
				$label['subscription_p_posts_quota'] = str_replace('%TOTAL%', $row['p_posts_quota_tally'], $label['subscription_p_posts_quota']);

				$SLM->subscription_status_line($label['subscription_p_posts_quota']);

			} elseif (($row['can_post_premium']=='Y') && JB_PREMIUM_POSTING_FEE_ENABLED=='YES') { // unlimited (0)

				
				$SLM->subscription_status_line($label['subscription_p_posts_quota_u']);
			}

			$SLM->subscription_status_close();

		}
		$SLM->list_cell_close();
		$SLM->list_item_close();


		$SLM->list_end();
		
	} else {
		subscription_plan_list(); 	
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

				
	<p><?php 

	JB_render_box_top(100, $label['subscription_status_info']);

	echo $label['subscription_status_info_list']; ?>
	<?php
	JB_render_box_bottom();
	?></p>
	</td>
	<td width="50%" valign="top">

	<!-- right col-->				
	<?php
	JB_render_box_top(100, $label['subscription_recent_trn']);
	

	$invoices = JBEmployer::get_recent_subscription_invoices($_SESSION['JB_ID']);

	if (sizeof($invoices) > 0 ) {

		$OLM = &JB_get_ListMarkupObject('JBOrdersListMarkup'); //new JBOrdersListMarkup();

		$OLM->list_start('subscription_orders', 'order_table');

		$OLM->list_head_open();
		$OLM->list_head_cell_open(); echo $label['subscription_hist_date']; $OLM->list_head_cell_close();
		$OLM->list_head_cell_open(); echo $label['subscription_hist_id']; $OLM->list_head_cell_close();
		$OLM->list_head_cell_open(); echo $label['subscription_hist_item']; $OLM->list_head_cell_close();
		$OLM->list_head_cell_open(); echo $label['subscription_hist_status']; $OLM->list_head_cell_close();
		$OLM->list_head_cell_open(); echo $label['subscription_hist_amount']; $OLM->list_head_cell_close();


		$OLM->list_head_close();

		foreach ($invoices as $row) {

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
				$OLM->status_link('payment.php?action=subscription&amp;invoice_id='.$row['invoice_id'].'&amp;subscription_id='.$row['subscription_id'].'&amp;employer_id='.$_SESSION['JB_ID'].'&amp;confirm=yes', $label['subscription_invoice_awaiting']);
			}

			elseif ($row['status']=='in_cart') { 
				$OLM->status_link('order.php?action=subscription&amp;invoice_id='.$row['invoice_id'].'&amp;subscription_id='.$row['subscription_id'].'&amp;employer_id='.$_SESSION['JB_ID'].'', $label['subscription_invoice_confirm']);
			}

			elseif ((strtolower($row['status'])=='pending') && ($row['reason']=='jb_credit_advanced')) { 
				$OLM->status_link('payment.php?action=subscription&amp;invoice_id='.$row['invoice_id'].'&amp;subscription_id='.$row['subscription_id'].'&amp;employer_id='.$_SESSION['JB_ID'].'&amp;confirm=yes', $label['invoice_stat_pending_unpaid']);
			} 

			JBPLUG_do_callback('can_subscr_order_status', $row);
			
			$OLM->list_cell_close();
			$OLM->list_cell_open();
			$OLM->data_cell('amount'); 
			
			
			$OLM->list_cell_close();

			$OLM->list_item_close();

		}

		$OLM->list_end();

	} else {

		echo '<div class="explanation_note">'.$label['subscription_hist_nodata']."</div>";
		
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