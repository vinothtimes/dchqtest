<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require("../config.php");
require (dirname(__FILE__)."/admin_common.php");

define('DEFAULT_CURR', JB_get_default_currency());

JB_admin_header('Admin -> Subscription Report');

?>
<b>[Subscription Orders]</b> <span style="background-color: #F2F2F2; border-style:outset; padding: 5px;"><a href="package_report.php">Posting Orders</a></span> <span style="background-color: #FFFFCC; border-style:outset; padding:5px; "><a href="subscription_report.php">Subscription Orders</a></span> <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="membership_report.php">Membership Orders</a></span>
	

<hr>
<input type="button" value="New Invoice" onclick="window.location='subscription_report.php?new=1'">

<?php

if ($_REQUEST['new']!='') {

	//echo "<p>New Invoice";

	if ($_REQUEST['go']!='') {

		if ($_REQUEST['employer_id']=='') {

			$error = "<p><b>Error:</b> Employer account not selected...</p>";

		}

		if ($_REQUEST['subscription_id']=='') {

			$error = "<p><b>Error:</b> Subscription not selected...</p>";

		}

		if ($error != '') {

			echo $error;

		} else {

			
			JB_place_subscription_invoice ($_REQUEST['employer_id'], $_REQUEST['subscription_id']);

			$_REQUEST['new']='';
			$JBMarkup->ok_msg('New invoice added.');

		}

	}

	if ($_REQUEST['new']!='') {

	?>

	<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=post" >
	<input type="hidden" name="new" value="<?php echo jb_escape_html($_REQUEST['new']);?>">
	<input type="hidden" name="go" value="2">
	<table border="0"  cellSpacing="1" cellPadding="5" bgColor="#d9d9d9">

	<tr>

	<td> <b>Employer:</b> </td>
	<td colspan="2">
	<select name="employer_id">
	<option value="">[Select..]</option>
	<?php
	$sql = "select * from employers order by Username";
	$result = JB_mysql_query($sql);
	while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

		echo '<option value="'.$row['ID'].'">'.JB_escape_html($row['Username']).' ('.JB_escape_html(substr($row['CompName'],0,28)).')</option>';



	}
	?>
	</select>
	</td>

	</tr>

	<tr>
		<td bgcolor="#E9E9E9"><b><?php echo $label['subscription_option'];?></b></td>
		<td bgcolor="#E9E9E9"><b><?php echo $label['subscription_price_']; ?></b></td>
		<td bgcolor="#E9E9E9"><b><?php echo $label['subscription_description_']; ?></b></td>
	<?php 
	$result = JB_mysql_query ("SELECT * from subscriptions order by price ASC, name ");	
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

	?>
		<tr><td bgcolor="#FFFFFF" nowrap>
			<input id="s<?php echo $row['subscription_id']; ?>" <?php if ($checked==false) {echo 'checked'; $checked=true;} ?> type="radio" name='subscription_id' value="<?php echo $row['subscription_id']; ?>" ><label for="s<?php echo $row['subscription_id']; ?>"> <?php echo $row['name']; ?> </label>
		</td>
		<td bgcolor="#FFFFFF" nowrap>
		<?php echo JB_convert_to_default_currency_formatted($row['currency_code'], $row['price'], true); ?>
		</td>
		<td nowrap bgcolor="#FFFFFF" width="100%">
		<?php echo $row['description'];?><br>
		<?php
		
		if ($row['can_view_resumes']=='Y') {
			echo '<IMG SRC="'.JB_THEME_URL.'images/tick.gif" WIDTH="17" HEIGHT="16" BORDER="0" ALT=""> '.$label['subscr_can_view_resumes'];
			
			if (($row['can_view_blocked']=='Y') && (JB_FIELD_BLOCK_SWITCH=='YES')) {
				
				echo " ".$label['subscr_can_view_blocked'];
			}
			echo "<br>";
		}

		if ($row['can_post']=='Y') {
			echo '<IMG SRC="'.JB_THEME_URL.'images/tick.gif" WIDTH="17" HEIGHT="16" BORDER="0" ALT=""> '.$label['subscr_can_post_unlimited']."<br>";

		}

		if ($row['can_post_premium']=='Y') {
			echo '<IMG SRC="'.JB_THEME_URL.'images/tick.gif" WIDTH="17" HEIGHT="16" BORDER="0" ALT=""> '.$label['subscr_can_post_unlimited_pr']."<br>";

		}
	
		?>
		</td>
		</tr>
	<?php 
	

	}
	?>

	</table>
	<input class="form_submit_button" type="submit" value="<?php echo $label['package_std_place_order']; ?>">
	</form>

	<?php

		echo "</p>";

	}


}

?>
<?php

if ($_REQUEST['action']=='complete') {
	
	$invoice_row = JB_get_subscription_invoice_row ($_REQUEST['invoice_id']);
	
	if (isset($invoice_row['employer_id'])) {
		$txn_prefix = 'E';
	} else {
		$txn_prefix = 'P';
	}
	
	JB_complete_subscription_invoice($_REQUEST['invoice_id'], '');
	JB_debit_transaction($_REQUEST['invoice_id'], $invoice_row['amount'], $invoice_row['currency_code'], $txn_prefix."S".$invoice_row['invoice_id'], '', 'Admin', 'S');

	$JBMarkup->ok_msg('Order completed, user subscription activated!');
	
}

if ($_REQUEST['action']=='confirm') {
	echo "confirmed: ".$_REQUEST['invoice_id'];
	JB_confirm_subscription_invoice($_REQUEST['invoice_id']);
	$JBMarkup->ok_msg('Order Confirmed');

}

if ($_REQUEST['action']=='cancel') {
	JB_cancel_subscription_invoice($_REQUEST['invoice_id']);
	$JBMarkup->ok_msg('Order Cancelled');

}


if ($_REQUEST['action']=='void') {
	JB_void_subscription_invoice ($_REQUEST['invoice_id']);
	$JBMarkup->ok_msg('Order set to void');

}

if ($_REQUEST['clean_up']!='') {
	$sql = "DELETE FROM subscription_invoices WHERE status='Void' ";
	JB_mysql_query($sql) or die(mysql_error()) ;
	$JBMarkup->ok_msg('Deleted all void orders');

}
$local_time = strtotime((gmdate("Y-m-d H:i:s")));
preg_match("#(\d+)-(\d+)-(\d+)#", (gmdate("Y-m-d H:i:s")), $local_m);

$prev_time = $local_time - (60*60*24*30); // minus 30 days

if ($_REQUEST['from_day']=='') {
	$_REQUEST['from_day']=date('d', $prev_time);

}
if ($_REQUEST['from_month']=='') {
	$_REQUEST['from_month'] = date("m", $prev_time);

}
if ($_REQUEST['from_year']=='') {
	$_REQUEST['from_year'] = date('Y', $prev_time);
}

if ($_REQUEST['to_day']=='') {
	
	$_REQUEST['to_day'] =  $local_m[3];
	
}
if ($_REQUEST['to_month']=='') {
	
	$_REQUEST['to_month'] = $local_m[2];
}
if ($_REQUEST['to_year']=='') {
	$_REQUEST['to_year'] = $local_m[1];

}
?>

<h3>Subscription Orders</h3>
<form method="GET">
From: d/m/y:
<select name="from_day" >
<option value=''> </option>
<?php
for ($i=1; $i <= 31; $i++) {
	if ($_REQUEST['from_day'] == $i) {
		$sel = " selected ";
	} else {
		$sel = " ";
	}
	echo "<option value='$i' $sel >$i</option>";
}
?>
</select>

<select name="from_month" >
<option value=''> </option>
<?php
for ($i=1; $i <= 12; $i++) {
	if ($_REQUEST['from_month'] == $i) {
		$sel = " selected ";
	} else {
		$sel = " ";
	}
	echo "<option value='$i' $sel >$i</option>";
}
?>
</select>

<select name="from_year" >
<option value=''> </option>
<?php
for ($i=2005; $i <= date("Y"); $i++) {
	if ($_REQUEST['from_year'] == $i) {
		$sel = " selected ";
	} else {
		$sel = " ";
	}
	echo "<option value='$i' $sel>$i</option>";
}
?>
</select>
 To: d/m/y: 
<select name="to_day" >
<option value=''> </option>
<?php
for ($i=1; $i <= 31; $i++) {
	if ($_REQUEST['to_day'] == $i) {
		$sel = " selected ";
	} else {
		$sel = " ";
	}
	echo "<option value='$i' $sel >$i</option>";
}
?>
</select>

<select name="to_month">
<option value=''> </option>
<?php
for ($i=1; $i <= 12; $i++) {
	if ($_REQUEST['to_month'] == $i) {
		$sel = " selected ";
	} else {
		$sel = " ";
	}
	echo "<option value='$i' $sel >$i</option>";
}
?>
</select>

<select name="to_year" >
<option value=''> </option>
<?php
for ($i=2005; $i <= date("Y"); $i++) {
	if ($_REQUEST['to_year'] == $i) {
		$sel = " selected ";
	} else {
		$sel = " ";
	}
	echo "<option value='$i' $sel>$i</option>";
}

if ($_REQUEST['select_date']!='') {

	$date_link=

		"&from_day=".$_REQUEST['from_day'].
		"&from_month=".$_REQUEST['from_month'].
		"&from_year=".$_REQUEST['from_year'].
		"&to_day=".$_REQUEST['to_day'].
		"&to_month=".$_REQUEST['to_month'].
		"&to_year=".$_REQUEST['to_year'].
			"&status=".$_REQUEST['status'].
		"&select_date=1";
}
?>
</select>
<input type="submit" name="select_date" value="Go">&nbsp;&nbsp;&nbsp;
 <input type="button" name="select_date" value="Reset" onclick='window.location="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" '>
</form>
<?php

$three_months_ago = mktime(0, 0, 0, date('m')-3, date('d'),date("Y"));
$q_from_day = 1;// (int)date ("d", $three_months_ago);
$q_from_month = (int)date ("m", $three_months_ago);
$q_from_year = (int)date ("Y", $three_months_ago);

?>
<p>
Quick Reports: By Status (last 3 months): <a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?from_day=<?php echo $q_from_day;?>&from_month=<?php echo $q_from_month;?>&from_year=<?php echo $q_from_year;?>&select_date=1&status=completed">Completed</a>, <a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?from_day=<?php echo $q_from_day;?>&from_month=<?php echo $q_from_month;?>&from_year=<?php echo $q_from_year;?>&select_date=1&status=confirmed">Confirmed</a>, <a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?from_day=<?php echo $q_from_day;?>&from_month=<?php echo $q_from_month;?>&from_year=<?php echo $q_from_year;?>&select_date=1&status=void">Void</a>, <a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?from_day=<?php echo $q_from_day;?>&from_month=<?php echo $q_from_month;?>&from_year=<?php echo $q_from_year;?>&select_date=1&status=in_cart">In Cart</a>, <a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?from_day=<?php echo $q_from_day;?>&from_month=<?php echo $q_from_month;?>&from_year=<?php echo $q_from_year;?>&select_date=1&status=all">All</a>
</p>
<form method='post' action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
... or Employer Username / Order ID: <input size='10' type="text" name="search_str" value="<?php echo JB_escape_html($_REQUEST['search_str']);?>"><input type='submit' value='GO' name='search_it'>
<div style="float: right;">
<font size="2"><a href="get_csv.php?table=subscription_invoices">Download CSV</a></font> 
</div>
<?php

// convert form local time to GMT
function gmstrtotime ($s)
{
    $t = strtotime($s);
    $zone = intval(JB_GMT_DIF)/100;
    $t += $zone*60*60;
    return $t;
}

if ($_REQUEST['show']=="") {
	//if ($_REQUEST['select_date']!='') {
	if (JB_GMT_DIF > 0) {
		$plus = "+";
	}

	$from_time = gmstrtotime($_REQUEST['from_year']."-".$_REQUEST['from_month']."-".$_REQUEST['from_day']." 00:00:00");

	$to_time = gmstrtotime($_REQUEST['to_year']."-".$_REQUEST['to_month']."-".$_REQUEST['to_day']." 23:59:59");

	$from_date = "'".(gmdate('Y-m-d H:i:s',$from_time))."'";
	$to_date = "'".(gmdate('Y-m-d H:i:s',$to_time))."'";

/*

In an ideal world, MySQL's CONVERT_TZ work best, but goDaddy does not support them because they use outdated software...
*/

/*
	$from_date = "CONVERT_TZ('".$_REQUEST['from_year']."-".$_REQUEST['from_month']."-".$_REQUEST['from_day']." 00:00:00', '".$plus.JB_GMT_DIF.":00', '+00:00')" ;
	$to_date = "CONVERT_TZ('".$_REQUEST['to_year']."-".$_REQUEST['to_month']."-".$_REQUEST['to_day']." 23:59:59', '".$plus.JB_GMT_DIF.":00', '+00:00')" ;
*/

	$where_date = "WHERE (invoice_date >= $from_date AND invoice_date <= $to_date ) ";
	//}

	if (($_REQUEST['status']!='') && ($_REQUEST['status']!='all')) {
		$where_status = " AND status='".jb_escape_sql($_REQUEST['status'])."' ";
	}

	if ($_REQUEST['search_it']!='') {
		$sql = "select ID from employers WHERE Username ='".jb_escape_sql($_REQUEST['search_str'])."' ";
		$result = JB_mysql_query($sql);
		if (mysql_num_rows($result)>0) {
			$row = mysql_fetch_array($result, MYSQL_ASSOC);
			$sql  = "select * FROM subscription_invoices WHERE employer_id='".jb_escape_sql($row['ID'])."' ORDER BY invoice_date DESC";
		} else {
			$_REQUEST['search_str'] = preg_replace ('/[^0-9]/','', $_REQUEST['search_str']);
			$sql  = "select * FROM subscription_invoices WHERE invoice_id='".jb_escape_sql($_REQUEST['search_str'])."' ORDER BY invoice_date DESC";

		}

		
		
	} else {

	$sql  = "select * FROM subscription_invoices $where_date $where_status ORDER BY invoice_date DESC ";

	}
	$result = JB_mysql_query($sql) or die (mysql_error());

if (mysql_num_rows($result) > 0) {
?>

<table cellSpacing="1" cellPadding="3" style="margin: 0 auto; background-color: #d9d9d9; width:100%; border:0px" >

  <tr bgColor="#eaeaea">
    <td><b><font face="Arial" size="2">Order Date</font></b></td>
	<td><b><font face="Arial" size="2">Order ID</font></b></td>
	<td><b><font face="Arial" size="2">Client Name</font></b></td>
    <td><b><font face="Arial" size="2">Item Name</font></b></td>
    <td><b><font face="Arial" size="2">Subscr Date</font></b></td>
	<td><b><font face="Arial" size="2">Subscr End</font></b></td>
    <td><b><font face="Arial" size="2">Status</font></b></td>
	<td><b><font face="Arial" size="2">Pmt Meth.</font></b></td>
	<td><b><font face="Arial" size="2">Amount</font></b></td>
	<td><b><font face="Arial" size="2">Balance</font></b></td>
         
  </tr>

  <?php

	  // status can be Expired, Void, Pending, 

 

  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

	  switch ($row['status']) {

		case "Expired":
			  $balance += @JB_convert_to_currency($row['amount'], $row['currency_code'], DEFAULT_CURR, $row['currency_rate']);
			  $show_balance = $balance;
			  break;
		  case "Void":
			  $show_balance = "...";
			  break;
		  case "Completed":
			  $balance += @JB_convert_to_currency($row['amount'], $row['currency_code'], DEFAULT_CURR, $row['currency_rate']);
			  $show_balance = $balance;
			  break;
		  case "Pending":
			  $show_balance = "...";
			  break;
		  case "Cancelled":
			  $show_balance = "...";
			  break;
		  case "Reversed":
			 $show_balance = "...";
			  break;
		  case "Canceled_Reversal":
			  $balance += @JB_convert_to_currency($row['amount'], $row['currency_code'], DEFAULT_CURR, $row['currency_rate']);
			  $show_balance = $balance;
			  break;
		  case "Failed":
			 $show_balance = "...";
			  break;
		  case "Denied":
			  $show_balance = "...";
			  break;
		  default:
			  $show_balance = "...";
		
	  }


  ?>

<tr onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);" bgColor="#ffffff">

<td><font face="Arial" size="2"><?php echo JB_get_local_time($row['invoice_date']); ?></font></td>
 <td><font face="Arial" size="2"><a href="#" onclick="
   window.open('invoice.php?invoice_id=<?php echo $row['invoice_id'];?>&product_type=S', '', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=560,left = 50,top = 50');return false;"> 
   <?php echo "S".$row['invoice_id']; ?></a></font></td>
  <td ><font face="Arial" size="2"><a href="employers.php?action=edit&user_id=<?php echo $row['employer_id'];?>"><?php 

	  $sql2 = "select * from employers where `ID`='".$row['employer_id']."' ";
	  $result2 = JB_mysql_query($sql2) or die ($sql2.mysql_error());
	  $row2 = mysql_fetch_array($result2);
	  echo JB_escape_html(jb_get_formatted_name($row2['FirstName'], $row2['LastName']).'  ');
	  
  
	 ?></a><?php echo '<small>(u:'.JB_escape_html($row2['Username']).')</small>'; ?></font></td>
	
    <td><font face="Arial" size="2"><?php echo $row['item_name']; ?></font></td>
    <td><font face="Arial" size="1"><?php if ($row['subscr_date']=='0000-00-00 00:00:00') { echo ''; } else { echo JB_get_local_time($row['subscr_date']); }?></font></td>
    <td><font face="Arial" size="1"><?php if ($row['subscr_end']=='0000-00-00 00:00:00') { echo ''; } else { echo JB_get_local_time($row['subscr_end']); } ?></font></td>
	<td><font face="Arial" size="2"><?php echo $row['status']; 
	if ($row['status']=='Completed') {

		$time = strtotime($row['invoice_date']." +0000");
		$time = $time + (60*60*24); // plus 1 day

		preg_match("#(\d+)-(\d+)-(\d+)#", $row['invoice_date'], $m);
		echo '<sup><a href="transactions.php?from_day='.$m[3].'&from_month='.$m[2].'&from_year='.$m[1].'&to_day='.gmdate('d', $time).'&to_month='.gmdate('m', $time).'&to_year='.gmdate('Y', $time).'">?<a></sup>';
		
	}
	
	?>
	<?php

	 if ($row['status']=='in_cart') {

		?>
		<input type="button" style="font-size: 9px;" value="Confirm" onclick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=confirm&invoice_id=<?php echo $row['invoice_id'].$date_link;?>' "> / <input type="button" style="font-size: 9px;" value="Cancel" onclick="if (!confirmLink(this, 'Cancel this Order, are you sure?')) return false; window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=cancel&invoice_id=<?php echo $row['invoice_id'].$date_link;?>' ">
<?php
	}

	if ((strtolower($row['status'])=='confirmed') ||  (strtolower($row['status'])=='pending')){
	?>
	<br>
		<input type="button" style="font-size: 9px;" value="Complete" onclick="if (!confirmLink(this, 'Payment from <?php echo JB_js_out_prep(JB_escape_html(jb_get_formatted_name($row2['FirstName'], $row2['LastName']))); ?> to be completed. Order for <?php  echo @JB_convert_to_currency($row['amount'], $row['currency_code'], DEFAULT_CURR); //echo CURRENCY_SIGN.$row['amount']; ?> will be credited to their account.\n ** Are you sure? **')) return false; window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=complete&invoice_id=<?php echo $row['invoice_id'].$date_link;?>' "> / <input type="button" style="font-size: 9px;" value="Cancel" onclick="if (!confirmLink(this, 'Cancel this Order, are you sure?')) return false; window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=cancel&invoice_id=<?php echo $row['invoice_id'].$date_link;?>' ">
	<?php
	} elseif (($row['status']=='Cancelled')) {

	?>
		<input type="button" style="font-size: 9px;" value="Void" onclick="if (!confirmLink(this, 'Void this Order, are you sure?')) return false; window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=void&invoice_id=<?php echo $row['invoice_id'].$date_link;?>' ">

		<input type="button" style="font-size: 9px;" value="Confirm" onclick="if (!confirmLink(this, 'Set status to \'Confirm\', are you sure?')) return false; window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=confirm&invoice_id=<?php echo $row['invoice_id'].$date_link;?>' ">
	<br>
	<?php

		}
	?>
	
	</font></td>
	<td><font face="Arial" size="2"><?php echo $row['payment_method']; ?></font></td>
	<td><font face="Arial" size="2"><?php echo @JB_convert_to_currency($row['amount'], $row['currency_code'], DEFAULT_CURR, $row['currency_rate']); ?></font></td>
	<td><b><font face="Arial" size="2"><?php echo $show_balance; ?></font></b></td>
	
</tr>

  <?php

  }
  
  ?>

  </table>

  <h3>Total Balance: <?php echo JB_format_currency($balance, DEFAULT_CURR); ?></h3>
  <form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF'])."?".$date_link; ?>" >
		<input type="submit" name="clean_up" value="Delete all void" onclick="if (!confirmLink(this, 'Delete all Void, are you sure?')) return false; ">
  </form>
  <?php

}

echo "<hr>";

}

if ($show=="Completed") {


}

if ($show=="Pending") {


}

if ($show=="Other") {


}

echo "<p>Status Info (as shown to the client) :</p>";
echo  $label['subscription_status_info_list'];

JB_admin_footer();

?>