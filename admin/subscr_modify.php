<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require("../config.php");
require (dirname(__FILE__)."/admin_common.php");


JB_admin_header('Admin -> Modify Subscr.');


if ($_REQUEST['add_month']!='') {
	$invoice_row = JB_get_subscription_invoice_row ($_REQUEST['invoice_id']);

	$t_end = strtotime ($invoice_row['subscr_end']);
	$t_next_month = mktime(date('H', $t_end), date('i', $t_end), date('s', $t_end), date('n', $t_end)+1, date('j', $t_end), date('Y', $t_end));
	$d_next_month = gmdate("Y-m-d H:i:s", $t_next_month);

	$sql = "UPDATE subscription_invoices SET subscr_end='".jb_escape_sql($d_next_month)."' WHERE invoice_id = '".jb_escape_sql($_REQUEST['invoice_id'])."' ";
	
	jb_mysql_query($sql);

	$JBMarkup->ok_msg('Added 1 month');



}
if ($_REQUEST['sub_month']!='') {
	$invoice_row = JB_get_subscription_invoice_row ($_REQUEST['invoice_id']);

	$t_end = strtotime ($invoice_row['subscr_end']);
	$t_next_month = mktime(date('H', $t_end), date('i', $t_end), date('s', $t_end), date('n', $t_end)-1, date('j', $t_end), date('Y', $t_end));
	$d_next_month = gmdate("Y-m-d H:i:s", $t_next_month);

	$sql = "UPDATE subscription_invoices SET subscr_end='".jb_escape_sql($d_next_month)."' WHERE invoice_id = '".jb_escape_sql($_REQUEST['invoice_id'])."' ";
	
	jb_mysql_query($sql);

	$JBMarkup->ok_msg('Subtracted 1 month');

}

if ($_REQUEST['expire']!='') {

	$invoice_row = JB_get_subscription_invoice_row ($_REQUEST['invoice_id']);

	JB_expire_subscription($invoice_row, $send_email=false);

	$JBMarkup->ok_msg('Subscription expired');


}

if ($_REQUEST['reactivate']!='') {

	$invoice_row = JB_get_subscription_invoice_row ($_REQUEST['invoice_id']);

	$now = (gmdate("Y-m-d H:i:s"));
	$sql = "UPDATE subscription_invoices SET `status`='Completed', `processed_date`='$now' WHERE invoice_id='".$_REQUEST['invoice_id']."'";
	$result = JB_mysql_query($sql) or JB_mail_error("[$sql]".mysql_error());

	JB_start_employer_subscription($invoice_row);

	$JBMarkup->ok_msg('Subscription reactivated');

}

if ($_REQUEST['save']!='') {

	$invoice_row = JB_get_subscription_invoice_row ($_REQUEST['invoice_id']);

	if ($_REQUEST['can_view_resumes'] == 'Y') {
		$can_view_resumes = ", can_view_resumes='Y'";
	} else {
		$can_view_resumes = ", can_view_resumes='N'";
	}

	if ($_REQUEST['can_post'] == 'Y') {
		$can_post = ", can_post='Y'";
	} else {
		$can_post = ", can_post='N'";
	}
	if ($_REQUEST['can_post_premium'] == 'Y') {
		$can_post_premium = ", can_post_premium='Y'";
	} else {
		$can_post_premium = ", can_post_premium='N'";
	}
	if ($_REQUEST['can_view_blocked'] == 'Y') {
		$can_view_blocked = ", can_view_blocked='Y'";
	} else {
		$can_view_blocked = ", can_view_blocked='N'";
	}

	if ($_REQUEST['views_quota'] == 'N') {
		$views_quota = ", views_quota='-1'";
	} else {
		$views_quota = ", views_quota='".jb_escape_sql($_REQUEST['views_quota'])."'";
	}

	if ($_REQUEST['posts_quota'] == 'N') {
		$posts_quota = ", posts_quota='-1'";
	} elseif ($_REQUEST['posts_quota']!='') {
		$posts_quota = ", posts_quota='".jb_escape_sql($_REQUEST['posts_quota'])."'";
	}

	if ($_REQUEST['p_posts_quota'] == 'N') {
		$p_posts_quota = ", p_posts_quota='-1'";
	} elseif ($_REQUEST['p_posts_quota']!='') {
		$p_posts_quota = ", p_posts_quota='".jb_escape_sql($_REQUEST['p_posts_quota'])."'";
	}
	

	$sql = "UPDATE subscription_invoices SET  amount='".jb_escape_sql($_REQUEST['amount'])."',  item_name='".jb_escape_sql($_REQUEST['item_name'])."'$can_view_resumes $can_post $can_post_premium, payment_method='".jb_escape_sql($_REQUEST['payment_method'])."' $can_view_blocked $views_quota $p_posts_quota $posts_quota WHERE invoice_id='".$_REQUEST['invoice_id']."'";


	$result = JB_mysql_query($sql) or JB_mail_error("[$sql]".mysql_error());

	// re-load the updated invoice row:

	$invoice_row = JB_get_subscription_invoice_row ($_REQUEST['invoice_id']);

	// update the employer record views_quota, posts_quota, p_posts_quota

	$sql = "UPDATE `employers` SET  subscription_can_premium_post='".jb_escape_sql($invoice_row['can_post_premium'])."', can_view_blocked='".jb_escape_sql($invoice_row['can_view_blocked'])."', subscription_can_view_resume='".jb_escape_sql($invoice_row['can_view_resumes'])."', subscription_can_post='".jb_escape_sql($invoice_row['can_post'])."' $views_quota $p_posts_quota $posts_quota WHERE ID='".jb_escape_sql($invoice_row['employer_id'])."' ";

	$result = JB_mysql_query($sql) or JB_mail_error("[$sql]".mysql_error());

	jb_update_subscription_quota($invoice_row['employer_id']);

	$JBMarkup->ok_msg('Subscription modified');



}





?>

<h3>Modify a Subscription</h3>
<?php

$invoice_row = JB_get_subscription_invoice_row ($_REQUEST['invoice_id']);

	if ($invoice_row['views_quota']==-1) {
		$invoice_row['views_quota']='N';
	}

	if ($invoice_row['posts_quota']==-1) {
		$invoice_row['posts_quota']='N';
	}

	if ($invoice_row['p_posts_quota']==-1) {
		$invoice_row['p_posts_quota']='N';
	}

	if (strtolower($invoice_row['payment_method'])=='paypal' ) {
		$disabled = ' disabled ';
	}
	
	?>
	<form method="post" action="subscr_modify.php" class="dynamic_form" id='dynamic_form'>
	   <table border="0" id="invoice" cellpadding="3"  cellspacing="0">
	  
		<tr> 
		   <td class="dynamic_form_field"><?php echo $label['subscription_invoice_no']; ?></td>
		   <td  class="dynamic_form_value" valign="top">
		   S<?php echo $invoice_row['invoice_id']; ?></td>
		 </tr>
		 <tr>
		   <td  class="dynamic_form_field"><?php echo $label['subscription_invoice_descr'];?></td>
		   <td nowrap class="dynamic_form_value" valign="top">
		   <input type="text" value="<?php echo htmlentities($invoice_row['item_name']); ?>" name="item_name" size="35"><br>

		   <input type="checkbox" name="can_view_resumes" value="Y" <?php if ($invoice_row['can_view_resumes']=='Y') { echo 'checked';}?>> Can View Resumes<br>
		   <?php if (JB_FIELD_BLOCK_SWITCH=='YES') { ?>
		   &nbsp;&nbsp;<input type="checkbox" name="can_view_blocked" value="Y" <?php if ($invoice_row['can_view_blocked']=='Y') { echo 'checked';}?>> Can View Blocked<br>
		   <?php } ?>
		   &nbsp;&nbsp;&nbsp;Quota: <input type="text" name="views_quota" value="<?php echo $invoice_row['views_quota']; ?>" size="2"> <small>(N = unlimited)</small><br>
		   
		   <input type="checkbox" name="can_post" value="Y" <?php if ($invoice_row['can_post']=='Y') { echo 'checked';}?>> Can Post (free)<br>
		   <?php
		   if (JB_free_posting_subscription_exists('S')) {
			?>
		   &nbsp;&nbsp;&nbsp;Quota: <input type="text" name="posts_quota" value="<?php echo $invoice_row['posts_quota']; ?>" size="2"> <small>(N = unlimited)</small><br>
		   <?php

		   }

		   ?>

		   <input type="checkbox" name="can_post_premium" value="Y" <?php if ($invoice_row['can_post_premium']=='Y') { echo 'checked';}?>> Can Premium Post (free)<br>
		   <?php
		   if (JB_free_posting_subscription_exists('S')) {
			?>
		   &nbsp;&nbsp;&nbsp;Quota: <input type="text" name="p_posts_quota" value="<?php echo $invoice_row['p_posts_quota']; ?>" size="2"> <small>(N = unlimited)</small><br>
		    <?php

		   }

		   ?>
		   </td>
		 </tr>
		 <tr>
		   <td class="dynamic_form_field"><?php echo $label['subscription_invoice_quantity']; ?></td>
		   <td class="dynamic_form_value" valign="top">
		   <?php echo $invoice_row['months_duration']; ?> </td>
		 </tr>
		 <tr>
		   <td class="dynamic_form_field">Expires</td>
		   <td class="dynamic_form_value" valign="top">
		   <?php if (strtotime($invoice_row['subscr_end']) >= (time()+5)) { ?>
		   in <?php 
		   
		   $t_start = strtotime(JB_get_local_time($invoice_row['subscr_date']));
		   $t_end = strtotime(JB_get_local_time($invoice_row['subscr_end']));
		   $t_now = strtotime(JB_get_local_time(gmdate('Y-m-d H:i:s')));
		 
		   $elapsed = $t_now - $t_start;
		   $diff = $t_end-$t_start-$elapsed;  
		 
		   $days = floor($diff/86400); 
		   
		   echo $days; ?> days <input <?php echo $disabled; ?> type="button" value="+ 1 Month" onclick="if (!confirmLink(this, 'Add 1 month to this subscription, are you sure? (Any other changes will be lost)')) return false;window.location='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?invoice_id=<?php echo $invoice_row['invoice_id'] ?>&add_month=1'"> 
		   <input type="button"  <?php echo $disabled; ?> value="- 1 Month" onclick="if (!confirmLink(this, 'Subtract 1 month from this subscription, are you sure? (Any other changes will be lost)')) return false; window.location='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?invoice_id=<?php echo $invoice_row['invoice_id'] ?>&sub_month=1'">
		   <?php
		   } else {
			   echo "Expired";
		   }
		   ?>
		   </td>
		 </tr>
		 <tr>
		   <td class="dynamic_form_field"><?php echo $label['subscription_invoice_price'];?>&nbsp; 
		 
		   </td>
		   <td class="dynamic_form_value" valign="top">
		   <input type="amount" name="amount" value="<?php echo $invoice_row['amount']; ?>" size="3"> <?php echo $invoice_row['currency_code']; ?>
		   <?php  //echo JB_convert_to_default_currency_formatted($invoice_row['currency_code'], $invoice_row['amount'], true); ?></td> 
		 </tr>
		  <tr>
		   <td class="dynamic_form_field">Payment Method&nbsp; 
		 
		   </td>
		   <td class="dynamic_form_value" valign="top">
		   <select name="payment_method">
			<option value="">Other</option>

		    <?php
			
			$dir = JB_basedirpath();

			include $dir.'payment/payment_manager.php';

			$_PAYMENT_OBJECTS['Admin'] = new bank;
			$_PAYMENT_OBJECTS['Admin']->name = 'Admin';

			foreach ($_PAYMENT_OBJECTS as $key => $val) {
				if ($invoice_row['payment_method']==$key) {
					$sel = ' selected ';
				} else {
					$sel = '';
				}
				echo '<option '.$sel.' value="'.$key.'">'.$_PAYMENT_OBJECTS[$key]->name.'</option>';

			}
		  
		  ?>

		   </select>

		 
		   <?php  //echo JB_convert_to_default_currency_formatted($invoice_row['currency_code'], $invoice_row['amount'], true); ?></td> 
		 </tr>

		
		 <tr>
		   <td class="dynamic_form_field"><?php echo $label['subscription_invoice_status']; ?>&nbsp; 
		   
		   </td>
		   <td  class="dynamic_form_value" valign="top">
		   <?php //echo JB_get_invoice_status_label($invoice_row['status']);

		   if (($invoice_row['status']=='Completed') || (($invoice_row['status']=='Pending') && ( $invoice_row['reason']='jb_credit_advanced'))) {
			   ?>
			   Active
				<input type="button"  <?php echo $disabled; ?> value="Expire Now" onclick="if (!confirmLink(this, 'Expire this subscription now, are you sure?')) return false; window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?expire=1&invoice_id=<?php echo $invoice_row['invoice_id'].$date_link;?>' ">
			   <?php

		   } else {

			   echo $invoice_row['status'];
			   ?>
			   <input type="button" value="Reactivate" onclick="if (!confirmLink(this, 'Reactivate this subscription, are you sure? (Any other changes will be lost)')) return false;window.location='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?invoice_id=<?php echo $invoice_row['invoice_id'] ?>&reactivate=1'">
			   <?php

		   }
		   
		   ?></td>
		 </tr>	 
	   </table>
	   <?php

	   if (strtolower($invoice_row['payment_method'])=='paypal' ) {

		?>
		<b>PayPal Note</b>: PayPal subscriptions are automated and are set to rebill automatically by PayPal. Therefore, as a safeguard, the system disables the ability to extend or expire subscriptions that were paid using paypal. The only way to modify a subscription is to cancel it via paypal and then add a new subscription. If you would like to to cancel a paypal subscription, please log in to your PayPal account. (If want to modify this order anyway, please change the Payment Method to 'Admin' and save changes)

		<?php

	   }

	   ?>
	   <input type="hidden" value="<?php echo jb_escape_html($_REQUEST['invoice_id']); ?>" name="invoice_id" >
		<br>
		<input name="save" type="submit" value="Save Changes" style="font-size: 13pt">
	   </form>
	   <p>&nbsp;</p>
	   <center><input type="button" name="" value="Close" onclick="window.opener.location.reload();window.close()"></center>
<?php

JB_admin_footer();

?>