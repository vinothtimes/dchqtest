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


JB_admin_header('Admin -> Transactions');
?>


<h3>Transactions</h3>
<p>
The transaction log helps you manage the money transfers. Note: Refunds are processed with PayPal or the payment gateway that was used. If you issued a refund that does not automatically report refunds to the script, you can issue your refunds here. To issue a refund, <b>please always process the refund with your payment processor first</b>, and then return to here to confirm that the refund was processed / record.
</p>

<?php

 

if ($_REQUEST['action']=='refund') {

	$t_id = $_REQUEST['transaction_id'];

	#$sql = "SELECT * from jb_txn, orders, users where jb_txn.invoice_id=orders.invoice_id AND orders.user_id=users.ID and jb_txn.transaction_id=$t_id";

	$sql = "SELECT * from jb_txn where transaction_id='".jb_escape_sql($t_id)."'";

	$result = JB_mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	
	if ($row['status']!='Completed') {
		// check that there's no other refund...
		$sql = "SELECT * FROM jb_txn where txn_id='".jb_escape_sql($row['txn_id'])."' AND type='CREDIT' ";
		$r = JB_mysql_query($sql) or die(mysql_error());
		if (mysql_num_rows($r)==0) {
			// do the refund
			if ($row['product_type']=='P') {
				JB_reverse_package_invoice($row['invoice_id'], 'refund');
			} elseif ($row['product_type']=='S') {
				JB_reverse_subscription_invoice($row['invoice_id'], 'refund');
			} elseif ($row['product_type']=='M') {
				JB_reverse_membership_invoice($row['invoice_id'], 'refund');
			}

			JB_credit_transaction($row['invoice_id'], $row['amount'], $row['currency'], $row['txn_id'], 'Refund', 'Admin', $row['product_type']);

			$JBMarkup->ok_msg('Refund completed.');

		} else {

			echo "<b>Error: A refund was already found on this system for this order..</b><br>";

		}


	} else {

		//echo $row['status'];

		
		echo "<b>Error: The system can only refund orders that are completed, please cancel the order first</b><br>";

	}


	// can only refund completed orders..



}



// calculate the balance
$sql = "SELECT SUM(amount) as mysum, type, currency from jb_txn group by type, currency";

$result = JB_mysql_query($sql) or die(mysql_error());

while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

	if ($row['type']=='CREDIT') {
		$credits = $credits + @JB_convert_to_default_currency($row['currency'],$row['mysum']);

	}

	if ($row['type']=='DEBIT') {
		$debits = $debits + @JB_convert_to_default_currency($row['currency'],$row['mysum']);

	}

}

$bal = $debits-$credits;

$local_time = strtotime((gmdate("Y-m-d H:i:s")));
preg_match("#(\d+)-(\d+)-(\d+)#", (gmdate("Y-m-d H:i:s")), $local_m);
/*
$local_time = strtotime(JB_get_local_time(gmdate("Y-m-d H:i:s")));
preg_match("#(\d+)-(\d+)-(\d+)#", JB_get_local_time(gmdate("Y-m-d H:i:s")), $local_m);
*/
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


<form method="GET">
From y/m/d: 


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

 To y/m/d: 

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
<input type="submit" name="select_date" value="Go"> &nbsp; &nbsp; &nbsp;
 <input type="button" name="select_date" value="Reset" onclick='window.location="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" '>
</form><p>
<?php

$three_months_ago = mktime(0, 0, 0, date('d'), date('m')-3, date("Y"));
$q_from_day = 1;// (int)date ("d", $three_months_ago);
$q_from_month = (int)date ("m", $three_months_ago);
$q_from_year = (int)date ("Y", $three_months_ago);

?>
<p>
Balance: <?php echo $bal; ?><br>
</p>
<table cellSpacing="1" cellPadding="3" style="margin: 0 auto; background-color: #d9d9d9; width:100%; border:0px" >

<tr bgcolor="#eaeaea" >
<td>
<font face="arial" size="2"><b>Date</b></font>
</td>
<td>
<font face="arial" size="2"><b>Order ID</b></font>
</td>
<td>
<font face="arial" size="2"><b>Origin</b></font>
</td>
<td>
<font face="arial" size="2"><b>Reason / Status</b></font>
</td>
<td>
<font face="arial" size="2"><b>Amount</b></font>
</td>
<td>
<font face="arial" size="2"><b>Txn. Type</b></font>
</td>
<td>
<font face="arial" size="2"><b>Product Type</b></font>
</td>
<td>
<font face="arial" size="2"><b>Action</b></font>
</td>
</tr>

<?php
		$from_date = $_REQUEST['from_year']."-".$_REQUEST['from_month']."-".$_REQUEST['from_day']." 00:00:00";
		$to_date = $_REQUEST['to_year']."-".$_REQUEST['to_month']."-".$_REQUEST['to_day']." 23:59:59";

		$where_date = " (`date` >= '$from_date' AND `date` <= '$to_date' ) ";

#$sql = "SELECT * from jb_txn, orders, users where $where_date AND jb_txn.invoice_id=orders.invoice_id AND orders.user_id=users.ID order by jb_txn.date desc ";

$sql = "SELECT * from jb_txn  where $where_date  order by `date` DESC ";

$result = JB_mysql_query($sql) or die(mysql_error());

while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

?>
	<tr bgcolor="#ffffff" >
	<td>
	<font face="arial" size="1"><?php echo $row['date'];?></font>
	</td>
	<td>
	<font face="arial" size="1"><?php echo $row['invoice_id'];?> </font>
	</td>
	<td>
	<font face="arial" size="1"><?php echo $row['origin'];?></font>
	</td>
	<td>
	<font face="arial" size="1"><?php echo $row['reason'];?></font>
	</td>
	<td>
	<font face="arial" size="1"><?php echo @JB_convert_to_default_currency($row['currency'], $row['amount']);?></font>
	</td>
	<td>
	<font face="arial" size="1"><?php if ($row['type']=='DEBIT') { echo '<font color="green">';} else { echo '<font color="red">';} echo $row['type'].'</font>';?></font>
	</td>
	<td>
	<font face="arial" size="1"><?php echo $row['product_type'];?></font>
	</td>
	<td>
	<font face="arial" size="1"><?php if ( $row['type']=='DEBIT') {;?><input type="button" value="Refunded" onclick="if (!confirmLink(this, 'Refund, are you sure??')) return false;window.location='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=refund&transaction_id=<?php echo $row['transaction_id'];?>'; " ><?php }?></font>
	</td>
	</tr>
<?php

}

?>

</table>

<?php

JB_admin_footer();
?>


