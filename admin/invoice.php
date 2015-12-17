<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

require("../config.php");
require (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Admin -> View Invoice');



if ($_REQUEST['invoice_id']!='') {

	if ($_REQUEST['product_type']=='P') {
		JB_display_package_invoice($_REQUEST['invoice_id']);
	} elseif ($_REQUEST['product_type']=='M')  {
		JB_display_membership_invoice($_REQUEST['invoice_id']);
	} elseif (($_REQUEST['product_type']=='S')) {
		JB_display_subscription_invoice($_REQUEST['invoice_id']);
	}

	$sql = "select * from jb_txn where invoice_id='".jb_escape_sql($_REQUEST['invoice_id'])."' AND product_type='".jb_escape_sql($_REQUEST['product_type'])."'";

	$result = JB_mysql_query($sql) or die($sql.mysql_error());

	if (mysql_num_rows($result) > 0) {
?>
Transactions:
<table cellSpacing="1" cellPadding="3" style="margin: 0 auto; background-color: #d9d9d9; width:100%; border:0px" >

  <tr bgColor="#eaeaea">
    <td><b><font face="Arial" size="2">Seq. ID</font></b></td>
	<td><b><font face="Arial" size="2">Date</font></b></td>
    <td><b><font face="Arial" size="2">Invoice ID</font></b></td>
    <td><b><font face="Arial" size="2">Type</font></b></td>
	<td><b><font face="Arial" size="2">Amount</font></b></td>
    <td><b><font face="Arial" size="2">Currency</font></b></td>
	<td><b><font face="Arial" size="2">Transaction ID</font></b></td>
	<td><b><font face="Arial" size="2">Reason</font></b></td>
	<td><b><font face="Arial" size="2">Method</font></b></td>
	<td><b><font face="Arial" size="2">Product Type</font></b></td> 
  </tr>

  <?php

	  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {


		  ?>

		  <tr onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);" bgColor="#ffffff">

		  <td><font face="Arial" size="2"><?php echo $row['transaction_id']; ?></font></td>
		  <td><font face="Arial" size="2"><?php echo $row['date']; ?></font></td>
		  <td><font face="Arial" size="2"><?php echo $row['invoice_id']; ?></font></td>
		  <td><font face="Arial" size="2"><?php echo $row['type']; ?></font></td>
		  <td><font face="Arial" size="2"><?php echo $row['amount']; ?></font></td>
		  <td><font face="Arial" size="2"><?php echo $row['currency']; ?></font></td>
		  <td><font face="Arial" size="2"><?php echo $row['txn_id']; ?></font></td>
		  <td><font face="Arial" size="2"><?php echo $row['reason']; ?></font></td>
		  <td><font face="Arial" size="2"><?php echo $row['origin']; ?></font></td>
		  <td><font face="Arial" size="2"><?php echo $row['product_type']; ?></font></td>

		  <tr>

		  

		  <?php

	  }


} else {

	echo "No recorded transactions for this invoice";
}

}

JB_admin_footer();
?>
