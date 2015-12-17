<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require("../config.php");
require (dirname(__FILE__)."/admin_common.php");
if ($_SESSION['member_status_type']=='') {
	
	$_SESSION['member_status_type'] = 'A';
}

if ($_REQUEST['type']!='') {
	$_SESSION['member_status_type'] = $_REQUEST['type'];
}

if ($_SESSION['USER_TYPE']=='') {
	$_SESSION['USER_TYPE'] = 'E'; // employers by default
}

if ($_REQUEST['USER_TYPE']!='') {
	$_SESSION['USER_TYPE'] = $_REQUEST['USER_TYPE'];

}
define('DEFAULT_CURR', JB_get_default_currency());

JB_admin_header('Admin -> Membership Plans');

?>
<b>[Manage Memberships]</b> <span style="background-color: <?php if ($_SESSION['member_status_type']=='A') { echo '#FFFFCC'; } else { echo '#F2F2F2'; } ?>; border-style:outset; padding: 5px;"><a href="memberships.php?type=A">Active</a></span> <span style="background-color: <?php if ($_SESSION['member_status_type']=='E') { echo '#FFFFCC'; } else { echo '#F2F2F2'; } ?>; border-style:outset; padding:5px; "><a href="memberships.php?type=E">Expired</a></span>
<hr>

<font color="maroon"><b>Select User Type:</b></font> 
<span style="border: 2px solid #C0C0C0; background-color: <?php if ($_SESSION['USER_TYPE']=='C') { echo '#FFFFCC'; } else { echo '#EEEEEE';}  ?>; border-bottom: 0px;"><input onclick="window.location='memberships.php?USER_TYPE=C'" id="CM1" type='radio' value='C' name='USER_TYPE' <?php if ($_SESSION['USER_TYPE']=='C') {echo 'checked'; } ?> > <label for="CM1">Candidate Memberships</label></span> &nbsp; <span  style="border: 2px solid #C0C0C0; background-color: <?php if ($_SESSION['USER_TYPE']=='E') { echo '#FFFFCC'; } else { echo '#EEEEEE';}  ?>; border-bottom: 0px;"><input onclick="window.location='memberships.php?USER_TYPE=E'" id="EM1" type='radio' value='E' name='USER_TYPE' <?php if ($_SESSION['USER_TYPE']=='E') {echo 'checked'; } ?> > <label for="EM1">Employer Memberships</label></span><br>

<?php

//$sql = "select * from membership_invoices "; AND t1.employer_id='".$employer_id."'
if ($_SESSION['member_status_type']=='A') {

	$sql = "SELECT *, t1.member_end AS S_END, t1.member_date AS S_DATE FROM membership_invoices as t1, memberships as t2 WHERE t1.membership_id = t2.membership_id  AND  user_type='".jb_escape_sql($_SESSION['USER_TYPE'])."' AND ((t1.status='Completed' ) OR ((t1.status='Pending') AND t1.reason='jb_credit_advanced')) ORDER BY t1.member_end ASC "; 

} else {
	$sql = "SELECT *, t1.member_end AS S_END, t1.member_date AS S_DATE FROM membership_invoices as t1, memberships as t2 WHERE t1.membership_id = t2.membership_id  AND user_type='".jb_escape_sql($_SESSION['USER_TYPE'])."' AND ((t1.status='Expired' ) OR (t1.status='Reversed') ) ORDER BY t1.member_end ASC "; 

}
//echo $sql;
$result = JB_mysql_query ($sql) or die (mysql_error());

$count = mysql_num_rows($result);

$records_per_page = 40;

if ($count > $records_per_page) {
	mysql_data_seek($result, $_REQUEST['offset']);
}

if ($count > 0) {

	if ($count > $records_per_page)  {

		$pages = ceil($count / $records_per_page);
		$cur_page = $_REQUEST['offset'] / $records_per_page;
		$cur_page++;

		echo "<center>";
		?>
		<center><b><?php echo $count; ?> Memberships Returned (<?php echo $pages;?> pages) </b></center>
		<?php
		echo "Page $cur_page of $pages - ";
		$nav = JB_nav_pages_struct($result, $q_string, $count, $records_per_page);
		$LINKS = 10;
		JB_render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
		echo "</center>";

	}

?>

	<table width="100%" cellSpacing="1" cellPadding="3" style="margin: 0 auto;" bgColor="#d9d9d9" border="0">

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
			<td><b><font face="Arial" size="2"></font></b></td>
			 
		</tr>
<?php
	while (($row = mysql_fetch_array($result, MYSQL_ASSOC)) && ($i < $records_per_page)) {
			$i++;

			?>

		<tr onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);" bgColor="#ffffff">

			<td><font face="Arial" size="2"><?php echo JB_get_local_time($row['invoice_date']); ?></font></td>
			<td><font face="Arial" size="2"><a href="#" onclick="window.open('invoice.php?invoice_id=<?php echo $row['invoice_id'];?>&product_type=M', '', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=560,left = 50,top = 50');return false;"> <?php echo "M".$row['invoice_id']; ?></a></font></td>
			<td ><font face="Arial" size="2"><?php 

			if ($row['user_type']=='C') {
				$sql2 = "select * from users where `ID`='".$row['user_id']."' ";
				?>
				<a href="candidates.php?action=edit&user_id=<?php echo $row['user_id'];?>">
				<?php
			} else {
				$sql2 = "select * from employers where `ID`='".$row['user_id']."' ";
				?>
				<a href="employers.php?action=edit&user_id=<?php echo $row['user_id'];?>">
				<?php
			}
			$result2 = JB_mysql_query($sql2) or die ($sql2.mysql_error());
			$row2 = mysql_fetch_array($result2);
			echo JB_get_formatted_name($row2['FirstName'], $row2['LastName']);

			$t_start = strtotime(JB_get_local_time($row['S_DATE']));
			$t_end = strtotime(JB_get_local_time($row['member_end']));
			$diff = $t_end-$t_start;  
			$days = floor($diff/86400); 
			
			?></a><?php echo '<small>(u:'.JB_escape_html($row2['Username']).' ['.$row['user_type'].'])</small>'; ?></font></td>
	
			<td><font face="Arial" size="2"><?php echo $row['item_name']; ?></font></td>
			<td><font face="Arial" size="1"><?php echo JB_get_local_time($row['S_DATE']); ?></font></td>
			<td><font face="Arial" size="1"><?php if ($row['months_duration']==0) { echo 'Never';} else { echo JB_get_local_time($row['member_end']); echo " ($days days)"; } ?></font></td>
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

				<input type="button" style="font-size: 9px;" value="Confirm" onclick="window.location='<?php echo htmlentities('membership_report.php');?>?action=confirm&invoice_id=<?php echo $row['invoice_id'].$date_link;?>' "> / <input type="button" style="font-size: 9px;" value="Cancel" onclick="if (!confirmLink(this, 'Cancel this Order, are you sure?')) return false; window.location='<?php echo 'membership_report.php';?>?action=cancel&invoice_id=<?php echo $row['invoice_id'].$date_link;?>' ">
				<?php
			}

			if ((strtolower($row['status'])=='confirmed') ||  (strtolower($row['status'])=='pending')){
			?>
			<br>
				<input type="button" style="font-size: 9px;" value="Complete" onclick="if (!confirmLink(this, 'Payment from <?php echo JB_js_out_prep(jb_escape_html(JB_get_formatted_name($row2['FirstName'], $row2['LastName']))); ?> to be completed. Order for <?php  echo @JB_convert_to_currency($row['amount'], $row['currency_code'], DEFAULT_CURR); //echo CURRENCY_SIGN.$row['amount']; ?> will be credited to their account.\n ** Are you sure? **')) return false; window.location='<?php echo htmlentities('membership_report.php');?>?action=complete&invoice_id=<?php echo $row['invoice_id'].$date_link;?>' "> / <input type="button" style="font-size: 9px;" value="Cancel" onclick="if (!confirmLink(this, 'Cancel this Order, are you sure?')) return false; window.location='<?php echo htmlentities('membership_report.php');?>?action=cancel&invoice_id=<?php echo $row['invoice_id'].$date_link;?>' ">
			<?php
			} elseif (($row['status']=='Cancelled')) {

	?>
				<input type="button" style="font-size: 9px;" value="Void" onclick="if (!confirmLink(this, 'Void this Order, are you sure?')) return false; window.location='<?php echo htmlentities('membership_report.php');?>?action=void&invoice_id=<?php echo $row['invoice_id'].$date_link;?>' ">

				<input type="button" style="font-size: 9px;" value="Confirm" onclick="if (!confirmLink(this, 'Set status to \'Confirm\', are you sure?')) return false; window.location='<?php echo htmlentities('membership_report.php');?>?action=confirm&invoice_id=<?php echo $row['invoice_id'].$date_link;?>' ">
			<br>
			<?php

			}
			?>
	
			</font></td>
			<td><font face="Arial" size="2"><?php echo $row['payment_method']; ?></font></td>
			<td><font face="Arial" size="2"><?php echo JB_convert_to_currency($row['amount'], $row['currency_code'], DEFAULT_CURR, $row['currency_rate']); ?></font></td>
			<td><input type="button" value="Modify" onclick="window.open('member_modify.php?invoice_id=<?php echo $row['invoice_id'];?>&product_type=M', '', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=560,left = 50,top = 50');return false;"><b><font face="Arial" size="2"></font></b></td>
	
		</tr>
	<?php
		
		
	}
	?>
	</TABLE>
	<?php

} else {

	echo "No records found";

}

JB_admin_footer();

?>