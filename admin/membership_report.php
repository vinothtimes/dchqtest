<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

require("../config.php");
require (dirname(__FILE__)."/admin_common.php");
//require("../include/accounting_functions.php");

define('DEFAULT_CURR', JB_get_default_currency());

if ($_SESSION['USER_TYPE']=='') {
	$_SESSION['USER_TYPE'] = 'E'; // employers by default

}

if ($_REQUEST['USER_TYPE']!='') {
	$_SESSION['USER_TYPE'] = $_REQUEST['USER_TYPE'];

}

JB_admin_header('Admin -> Membership Report');

?>
<b>[Membership Orders]</b> <span style="background-color: #F2F2F2; border-style:outset; padding: 5px;"><a href="package_report.php">Posting Orders</a></span> <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="subscription_report.php">Subscription Orders</a></span> <span style="background-color: #FFFFCC; border-style:outset; padding:5px; "><a href="membership_report.php">Membership Orders</a></span>
	


<hr>


<br>
<input type="button" value="New Invoice" onclick="window.location='membership_report.php?new=1'">

<?php

if ($_REQUEST['new']!='') {

	//echo "<p>New Invoice";

	if ($_REQUEST['go']!='') {

		if ($_REQUEST['user_id']=='') {
			$error = "<p>Error: User account not selected...</p>";
		}

		if ($_REQUEST['membership_id']=='') {
			$error = "<p>Error: Membership not selected...</p>";
		}

		if ($error != '') {
			echo $error;
		} else {

			JB_place_membership_invoice ($_REQUEST['user_id'], $_REQUEST['membership_id']);
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

	<td> <b>Userr:</b> </td>
	<td colspan="2">
	<select name="user_id">
	<option value="">[Select..]</option>
	<?php
	if ($_SESSION['USER_TYPE']=='E') {
		$sql = "select * from employers order by Username";
	} else {
		$sql = "select * from users order by Username";
	}
	$result = JB_mysql_query($sql);
	while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

		if ($_SESSION['USER_TYPE']=='E') { // employers?
			echo '<option value="'.$row['ID'].'">'.JB_escape_html($row['Username']).' ('.JB_escape_html(substr($row['CompName'],0,25)).')</option>';
		} else {
			echo '<option value="'.$row['ID'].'">'.JB_escape_html($row['Username']).' ('.JB_escape_html(substr($row['Email'],0,25)).')</option>';
		}



	}
	?>
	</select>
	</td>

	</tr>

	<tr><td bgcolor="#E9E9E9"><b>Option</b></td><td bgcolor="#E9E9E9"><b>Duration</b></td><td bgcolor="#E9E9E9"><b>Price</b></td>
	<?php 
	if ($_SESSION['USER_TYPE']=='E') { // employers?
		$mem_type = " WHERE type='E' ";
	} else {
		$mem_type = " WHERE type='C' ";
	}
	$sql = "SELECT * from memberships $mem_type order by type, name  ASC";
	$result = JB_mysql_query ($sql) or die(mysql_error());	
	//echo $sql;
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

	?>
		<tr><td bgcolor="#FFFFFF">
			<input type="radio" name='membership_id' <?php if ($_REQUEST['membership_id']==$row['membership_id']) { echo " checked ";}  ?> value="<?php echo $row['membership_id']; ?>" ><?php echo $row['name']; ?>
		</td><td bgcolor="#FFFFFF">
			<?php echo $row['months']; ?>
		</td><td bgcolor="#FFFFFF">
			<?php 

				echo JB_convert_to_default_currency_formatted($row['currency_code'], $row['price'], true); 
		 ?>
		</td></tr>
	<?php 

	}
	?>
	</table>
	<input class="form_submit_button" type="submit" value="Place Order">
	</form>

	<?php

		echo "</p>";

	}


}

?>


<?php
if ($_REQUEST['action']=='confirm') {
	//$sql = "update membership_invoices set status='Confirmed' where invoice_id='".$_REQUEST['invoice_id']."'";
	//JB_mysql_query($sql) or die(mysql_error()) ;
	JB_confirm_membership_invoice($_REQUEST['invoice_id']);
	$JBMarkup->ok_msg('Order Confirmed');
}
if ($_REQUEST['action']=='complete') {
	$invoice_row = JB_get_membership_invoice_row ($_REQUEST['invoice_id']);

	if (isset($invoice_row['employer_id'])) {
		$txn_prefix = 'E';
	} else {
		$txn_prefix = 'P';
	}
	
	JB_complete_membership_invoice($_REQUEST['invoice_id'], 'Admin');

	JB_debit_transaction($_REQUEST['invoice_id'], $invoice_row['amount'], $invoice_row['currency_code'], $txn_prefix.'M'.$_REQUEST['invoice_id'], $reason_code, 'Admin', 'M');
	$JBMarkup->ok_msg('Order Completed');
}


if ($_REQUEST['action']=='void') {

	JB_void_membership_invoice($_REQUEST['invoice_id']);
	$JBMarkup->ok_msg('Order set to Void');
	
}

if ($_REQUEST['action']=='cancel') {

	JB_cancel_membership_invoice($_REQUEST['invoice_id']);
	$JBMarkup->ok_msg('Order Cancelled');

}

if ($_REQUEST['clean_up']!='') {
	$sql = "DELETE FROM membership_invoices WHERE status='Void' ";
	JB_mysql_query($sql) or die(mysql_error()) ;
	$JBMarkup->ok_msg('Deleted all Void orders');

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

<h3>Membership Orders (<?php if ($_SESSION['USER_TYPE']=='E') { echo 'Employers';} else {echo 'Users';} ?>) </h3>
<form method="GET">
From d/m/y: 
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
 To d/m/y: 
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
<input type="submit" name="select_date" value="Go"> &nbsp; &nbsp; &nbsp;
 <input type="button" name="select_date" value="Reset" onclick='window.location="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" '>
</form><p>
<?php


$three_months_ago = mktime(0, 0, 0, date('m')-3, date('d'),date("Y"));

$q_from_day = date ("d", $three_months_ago);
$q_from_month = date ("m", $three_months_ago);
$q_from_year = date ("Y", $three_months_ago);

?>
<p>
 Quick Reports: By Status (last 3 months): <a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?from_day=<?php echo $q_from_day;?>&from_month=<?php echo $q_from_month;?>&from_year=<?php echo $q_from_year;?>&select_date=1&status=completed">Completed</a>, <a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?from_day=<?php echo $q_from_day;?>&from_month=<?php echo $q_from_month;?>&from_year=<?php echo $q_from_year;?>&select_date=1&status=confirmed">Confirmed</a>, <a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?from_day=<?php echo $q_from_day;?>&from_month=<?php echo $q_from_month;?>&from_year=<?php echo $q_from_year;?>&select_date=1&status=void">Void</a>, <a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?from_day=<?php echo $q_from_day;?>&from_month=<?php echo $q_from_month;?>&from_year=<?php echo $q_from_year;?>&select_date=1&status=in_cart">In Cart</a>, <a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?from_day=<?php echo $q_from_day;?>&from_month=<?php echo $q_from_month;?>&from_year=<?php echo $q_from_year;?>&select_date=1&status=all">All</a>
</p>
<p>
<form method='post' action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
... or Username / Order ID: <input size='10' type="text" name="search_str" value="<?php echo JB_escape_html($_REQUEST['search_str']);?>"><input type='submit' value='GO' name='search_it'>
</form>
</p>
<div style="float: right;">
<font size="2"><a href="get_csv.php?table=membership_invoices">Download CSV</a></font> 
</div>

<font color="maroon"><b>Select User Type:</b></font> 
<span style="border: 2px solid #C0C0C0; background-color: <?php if ($_SESSION['USER_TYPE']=='C') { echo '#FFFFCC'; } else { echo '#EEEEEE';}  ?>; border-bottom: 0px;"><input onclick="window.location='membership_report.php?USER_TYPE=C'" id="CM1" type='radio' value='C' name='USER_TYPE' <?php if ($_SESSION['USER_TYPE']=='C') {echo 'checked'; } ?> > <label for="CM1">Candidate Memberships</label></span> &nbsp; <span  style="border: 2px solid #C0C0C0; background-color: <?php if ($_SESSION['USER_TYPE']=='E') { echo '#FFFFCC'; } else { echo '#EEEEEE';}  ?>; border-bottom: 0px;"><input onclick="window.location='membership_report.php?USER_TYPE=E'" id="EM1" type='radio' value='E' name='USER_TYPE' <?php if ($_SESSION['USER_TYPE']=='E') {echo 'checked'; } ?> > <label for="EM1">Employer Memberships</label></span><br>
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


	$where_date = "WHERE (invoice_date >= $from_date AND invoice_date <= $to_date ) ";

	if (($_REQUEST['status']!='') && ($_REQUEST['status']!='all')) {
		$where_status = " AND status='".jb_escape_sql($_REQUEST['status'])."' ";
	}

	if ($_SESSION['USER_TYPE']=='E') { // employers?
		$user_type = " AND user_type='E' ";
	} else {
		$user_type = " AND user_type='C' ";
	}

	if ($_REQUEST['search_it']!='') {
		
		if ($_SESSION['USER_TYPE']=='E') {
			$sql = "select ID from `employers` WHERE Username ='".jb_escape_sql($_REQUEST['search_str'])."' ";
		} else {
			$sql = "select ID from `users` WHERE Username ='".jb_escape_sql($_REQUEST['search_str'])."' ";
		}

		

		$result = JB_mysql_query($sql);
		if (mysql_num_rows($result)>0) {
			$row = mysql_fetch_array($result, MYSQL_ASSOC);
			$sql  = "select * FROM membership_invoices WHERE user_id='".$row['ID']."' $user_type ORDER BY invoice_date DESC";
		} else {
			$_REQUEST['search_str'] = preg_replace ('/[^0-9]/','', $_REQUEST['search_str']);
			$sql  = "select * FROM membership_invoices WHERE invoice_id='".$_REQUEST['search_str']."' $user_type ORDER BY invoice_date DESC";

		}

		
		
	} else {
		$sql  = "select * FROM membership_invoices $where_date $where_status $user_type ORDER BY invoice_date DESC";

	}

	//echo $sql;
	
	$result = JB_mysql_query($sql) or die (mysql_error());


	if (mysql_num_rows($result) > 0) {

		?>

		<table  cellSpacing="1" cellPadding="3" style="margin: 0 auto; background-color: #d9d9d9; width:100%; border:0px" >

		<tr bgColor="#eaeaea">
			<td><b><font face="Arial" size="2">Order Date</font></b></td>
			<td><b><font face="Arial" size="2">Order ID</font></b></td>
			<td><b><font face="Arial" size="2">Client Name</font></b></td>
			<td><b><font face="Arial" size="2">Item Name</font></b></td>
			<td><b><font face="Arial" size="2">Member Date</font></b></td>
			<td><b><font face="Arial" size="2">Member End</font></b></td>
			<td><b><font face="Arial" size="2">Status</font></b></td>
			<td><b><font face="Arial" size="2">Pmt Meth.</font></b></td>
			<td><b><font face="Arial" size="2">Amount</font></b></td>
			<td><b><font face="Arial" size="2">Total</font></b></td>	 
		</tr>

		<?php
			
		// status can be Expired, Void, Pending, 

		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			switch ($row['status']) {

				case "Expired":
					$balance += @JB_convert_to_currency($row['amount'], $row['currency_code'], DEFAULT_CURR,$row['currency_rate']);
					$show_balance = $balance;
				break;
				case "Void":
					$show_balance = "...";
				break;
				case "Completed":
					$balance += @JB_convert_to_currency($row['amount'], $row['currency_code'], DEFAULT_CURR,$row['currency_rate']);
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
				break;
				
			}

		?>
			<tr onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);" bgColor="#ffffff">

			<td><font face="Arial" size="2"><?php echo JB_get_local_time($row['invoice_date']); ?></font></td>
			<td><font face="Arial" size="2"><a href="#" onclick="
			window.open('invoice.php?invoice_id=<?php echo $row['invoice_id'];?>&product_type=M', '', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=650,height=560,left = 50,top = 50');return false;"> 
			<?php echo "M".$row['invoice_id']; ?></a></font></td>
			<td ><font face="Arial" size="2"><?php 
			if ($_SESSION['USER_TYPE']=='E') { // employers?   
				?><a href="employers.php?action=edit&user_id=<?php echo $row['user_id'];?>">
			<?php }  else {?>
				<a href="candidates.php?action=edit&user_id=<?php echo jb_escape_sql($row['user_id']);?>">
			<?php }

			if ($_SESSION['USER_TYPE']=='E') { // employers?  
				$sql2 = "select * from employers where `ID`='".jb_escape_sql($row['user_id'])."' ";
			} else {
				$sql2 = "select * from users where `ID`='".jb_escape_sql($row['user_id'])."' ";
			}

			

			$result2 = JB_mysql_query($sql2) or die ($sql2.mysql_error());
			$row2 = mysql_fetch_array($result2);
			echo JB_escape_html(JB_get_formatted_name($row2['FirstName'], $row2['LastName']))." (".jb_escape_html($row2['Username']).")";


			?></a></font><?php echo ' <small>(u:'.JB_escape_html($row2['Username']).')</small>';?></td>
			<td><font face="Arial" size="2"><?php echo $row['item_name']; ?></font></td>
			<td><font face="Arial" size="1"><?php if ($row['member_date']=='0000-00-00 00:00:00') { echo ''; } else { echo JB_get_local_time($row['member_date']); }?></font></td>
			<td><font face="Arial" size="1"><?php if ($row['months_duration']=='0') echo 'Never'; else echo JB_get_local_time($row['member_end']); ?></font></td>
			<td><font face="Arial" size="2"><?php echo $row['status']; 
			if ($row['status']=='Completed') {

				$time = strtotime($row['invoice_date']." +0000");
				$time = $time + (60*60*24); // plus 1 day

				preg_match("#(\d+)-(\d+)-(\d+)#", $row['invoice_date'], $m);
				echo '<sup><a href="transactions.php?from_day='.$m[3].'&from_month='.$m[2].'&from_year='.$m[1].'&to_day='.gmdate('d', $time).'&to_month='.gmdate('m', $time).'&to_year='.gmdate('Y', $time).'">?<a></sup>';
				
			}?>
			<?php

			if ($row['status']=='in_cart') {
				?>
				<input type="button" style="font-size: 9px;" value="Confirm" onclick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=confirm&invoice_id=<?php echo $row['invoice_id'].$date_link;?>' "> / <input type="button" style="font-size: 9px;" value="Cancel" onclick="if (!confirmLink(this, 'Cancel this Order, are you sure?')) return false; window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=cancel&invoice_id=<?php echo $row['invoice_id'].$date_link;?>' ">
				<?php
			}

			if ((strtolower($row['status'])=='confirmed') || (strtolower($row['status'])=='pending')) {
			?>
				<br>
				<input type="button" style="font-size: 9px;" value="Complete" onclick="if (!confirmLink(this, 'Payment from <?php echo JB_js_out_prep(jb_escape_html(JB_get_formatted_name($row2['FirstName'], $row2['LastName'])));?> to be completed. Order for <?php echo @JB_convert_to_currency($row['amount'], $row['currency_code'], DEFAULT_CURR, $row['currency_rate']); //echo JB_format_currency( $row['amount'],  $row['currency_code']);  ?> will be credited to their account.\n ** Are you sure? **')) return false; window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=complete&invoice_id=<?php echo $row['invoice_id'].$date_link;?>' "> / <input type="button" style="font-size: 9px;" value="Cancel" onclick="if (!confirmLink(this, 'Cancel this Order, are you sure?')) return false; window.location='<?php echo $_SERVER['PHP_SELF'];?>?action=cancel&invoice_id=<?php echo $row['invoice_id'].$date_link;?>' ">
			<?php
			} elseif ($row['status']=='Cancelled') {

				?>
				<input type="button" style="font-size: 9px;" value="Void" onclick="if (!confirmLink(this, 'Cancel this Order, are you sure?')) return false; window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=void&invoice_id=<?php echo $row['invoice_id'].$date_link;?>' ">

				<input type="button" style="font-size: 9px;" value="Confirm" onclick="if (!confirmLink(this, 'Set status to \'Confirm\', are you sure?')) return false; window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=confirm&invoice_id=<?php echo $row['invoice_id'].$date_link;?>' ">

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
			<input type="submit" name="clean_up" onclick="if (!confirmLink(this, 'Delete all Void, are you sure?')) return false; " value="Delete all Void">
		</form>


		<?php


	} else {
		echo "No Orders found.";
	}

	echo "<hr>";

} 



?>
<h3>Status Information</h3>
<p><b>Completed</b><i> = The user completed making a payment.</i></p>
<p><b>Confirmed</b> = <i>The user has confirmed the order, but not yet made a payment.</i></p>
<p><b>Void</b><i> = The user cancelled the order before making a payment.</i></p>
<p><b>Refunded</b> = <i>This transaction was refunded.</i></p>
<p><b>Reversed</b> = <i>This transaction was reversed by PayPal</i></p>
<p><b>Failed</b><i> = This transaction failed. PayPal did not accept your 
payment.</i></p>
<p><b>Denied</b><i> = This transaction was denied by <?php echo jb_escape_html(JB_SITE_NAME); ?>. </i></p>
<?php

JB_admin_footer();
?>