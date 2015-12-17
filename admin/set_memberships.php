<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require("../config.php");
require (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Admin -> Set Memberships');


?>


<b>[Prices]</b> 
	<span style="background-color: #F2F2F2; border-style:outset; padding: 5px;"><a href="set_packages.php">Set Packages</a></span>
<span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="set_subscriptions.php">Set Subscriptions</a></span>
<span style="background-color: #FFFFCC; border-style:outset; padding:5px; "><a href="set_memberships.php">Set Memberships</a></span>
<hr>
<small>Memberships - The user (Candidate or Employer) cannot do anything until a fee is paid. Can charge a one-off fee or periodically counted by months. When enabled, this billing system takes precedence over the other billing systems, and if a user logs in, the system will redirect them to the payment page before they can proceed further. Memberships can be enabled/disabled in the Main Config. See also the 'Membership Fields' option in Main Config. If you want to give a free subscription to an Employer, go to Admin->Membership Orders, click New Invoice, Place, Confirm and Complete the order.</small>
<h3>Set Memberships</h3>


<?php


function list_membership_options() {


	$sql = "SELECT * FROM `memberships` order by type,months asc ";
	$result = JB_mysql_query($sql) or die(mysql_error());

	
	if (mysql_num_rows($result) > 0) { ?>

		<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" >
			<tr bgColor="#eaeaea">
				<td ><span class="style1"><b>ID</b></span></td>
				<td><span><b>Membership Description</b></span></td>
				<td><span ><b>Price</b></style></td>
				<td><span ><b>Currency</b></style></td>
				<td><b><span >Months</span></b></td>
				<td><b><span >User Type</span></b></td>
				<td><b><span >Action</span></b></td>
			</tr>
			<?php

			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

				?>

				<tr bgcolor="<?php echo ($row['membership_id']==$_REQUEST['membership_id']) ? '#FFFFCC' : '#ffffff'; ?>">
				<td><?php echo $row['membership_id']; ?></td>
				<td><span><?php echo $row['name']; ?></span></td>
				<td><span ><?php echo $row['price']; ?></style></td>
				<td><span ><?php echo $row['currency_code']; ?></style></td>
				<td><span ><?php if ($row['months']=='0') { echo 'Forever';} else { echo $row['months']; } ?></span></td>
				<td><span ><?php  if ($row['type']=='C')  { echo 'Candidate'; } else { echo 'Employer';} ?></span></td>
				<td><span ><A href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?edit=1&membership_id=<?php echo $row['membership_id']; ?>">Edit</a> | <A href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?del=1&membership_id=<?php echo $row['membership_id']; ?>" onclick="if (!confirmLink(this, 'Delete, are you sure?')) { return false;}">Delete</span></td>
			</tr>

			<?php

			}

			?>
		</table>
				


	<?php

	}


}


if ($_REQUEST['del']!='') {

	$sql = "SELECT membership_id FROM membership_invoices WHERE membership_id='".jb_escape_sql($_REQUEST['membership_id'])."' AND ((`status`='Completed' ) OR ((`status`='Pending') AND `reason`='jb_credit_advanced'))";

	$result = jb_mysql_query($sql);
	if (mysql_num_rows($result)>0) {
		$JBMarkup->error_msg('Membership plan cannot be deleted. The system found that there are some active membership(s) which for this membership plan. Please modify these membership(s) in Admin-&gt;Memberships so that they are not active, and try to delete them here again');
	} else {

		$sql = "DELETE FROM memberships where membership_id='".jb_escape_sql($_REQUEST['membership_id'])."' ";
		JB_mysql_query ($sql) or die (mysql_error());
		$JBMarkup->ok_msg('Membership plan deleted.');
	}

}



if ($_REQUEST['submit']!='') {

	if ($_REQUEST['name']=='') {
		$error .=  '- Name is blank<br>';

	}

	if ($_REQUEST['price']=='') {
		$error .=  '- Price is blank<br>';

	}

	if (!is_numeric($_REQUEST['price'])) {
		$error .=  '- Price must be a number<br>';

	}

	if ($_REQUEST['currency_code']=='') {
		$error .=  '- Currency not selected<br>';

	}

	if (!is_numeric($_REQUEST['months'])) {
		$error .=  '- Months must be a number<br>';

	}

	if ($_REQUEST['type']=='') {
		$error .=  '- Type not selected<br>';

	}

	if ($error != '') {

		$JBMarkup->error_msg("<b>Cannot save due to the following errors:</b>");
		echo $error;

		$_REQUEST['name'] = stripslashes($_REQUEST['name']);

	} else {

		// save

		if ($_REQUEST['membership_id'] == '') {

			$sql = "INSERT INTO `memberships` ( `name` , `price` , `currency_code` , `months` , `type` ) VALUES ( '".jb_escape_sql($_REQUEST['name'])."', '".jb_escape_sql($_REQUEST['price'])."', '".jb_escape_sql($_REQUEST['currency_code'])."', '".jb_escape_sql($_REQUEST['months'])."', '".jb_escape_sql($_REQUEST['type'])."');";

		} else {

			$sql = "UPDATE `memberships` set name='".jb_escape_sql($_REQUEST['name'])."', price='".jb_escape_sql($_REQUEST['price'])."', currency_code='".jb_escape_sql($_REQUEST['currency_code'])."', months='".jb_escape_sql($_REQUEST['months'])."', type='".jb_escape_sql($_REQUEST['type'])."' WHERE membership_id='".jb_escape_sql($_REQUEST['membership_id'])."'  ";

			


		}

		JB_mysql_query($sql) or die (mysql_error().$sql);

		$_REQUEST['new'] = '';
		$_REQUEST['edit'] = '';

		$JBMarkup->ok_msg('Membership Saved.');

		list_membership_options();

	}




} else {

	list_membership_options();


}

if (($_REQUEST['new']=='yes' ) || ($_REQUEST['edit']==true )) {

	if (($_REQUEST['membership_id']!='') && ($error=='')) {

		$sql = "select * from memberships WHERE membership_id='".jb_escape_sql($_REQUEST['membership_id'])."' ";
		$result = JB_mysql_query ($sql) or die (mysql_error());

		$row = mysql_fetch_array($result, MYSQL_ASSOC);

		$_REQUEST['name'] = $row['name'];
		$_REQUEST['price'] = $row['price'];
		$_REQUEST['currency_code'] = $row['currency_code'];
		$_REQUEST['months'] = $row['months'];
		$_REQUEST['type'] = $row['type'];



	}

	?>
	<p>Please enter a new Membership plan:<br>
	<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?> " name="form2" >
	<input type="hidden" value="<?php echo jb_escape_html($_REQUEST['new']); ?>" name="new">
	<input type="hidden" value="<?php echo jb_escape_html($_REQUEST['edit']); ?>" name="edit">
	<input type="hidden" value="<?php echo jb_escape_html($_REQUEST['membership_id']);?>" name="membership_id">
	<table border="0" cellSpacing="1" cellPadding="3"  bgColor="#d9d9d9" >
	<tr bgcolor='#ffffff'>
		<td><b>Membership Description: </b></td>
		<td><input type="text" name="name" size='40'  value="<?php echo JB_escape_html($_REQUEST['name']);?>"><br>(meaning name, eg. '$12.00 One-time Membership Fee', or '$5.00 12 Month Membership fee' etc.) </td>
	</tr><tr bgcolor='#ffffff'>
		<td><b>Price: </b></td>
		<td><input type="text" name="price" size='5'  value="<?php echo jb_escape_html($_REQUEST['price']);?>"><br>(enter a decimal, eg. 12.00)</td>
	</tr><tr bgcolor='#ffffff'>
		<td><b>Currency: </b></td>
		<td> <select name="currency_code"  >
		<?php
		JB_currency_option_list ($_REQUEST['currency_code']);
		?></select></td>
	</tr><tr bgcolor='#ffffff'>
		<td><b>Months (Duration): </b></td>
		<td><input type="text" name="months" size=4 value="<?php echo jb_escape_html($_REQUEST['months']);?>"> (Enter a number. 0=forever)</td>
	</tr><tr bgcolor='#ffffff'>
		<td><b>User Type: </b></td>
		<td><input type="radio" name="type" value='E' <?php if ($_REQUEST['type']=='E') { echo ' checked'; } ?>> Employer<br><input type="radio" name="type" value='C' <?php if ($_REQUEST['type']=='C') { echo ' checked'; } ?>> Candidate</td>
	</tr>
	</table>
	<input type="submit" name="submit" value="Submit">
	</form>

<?php

} else {

?>

<input type="button" value='Add New Membership..' onclick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?new=yes'" >

<?php
}

JB_admin_footer();


?>