<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require("../config.php");
require (dirname(__FILE__)."/admin_common.php");


JB_admin_header('Admin -> Set Packages');

?>
<b>[Prices]</b>
	<span style="background-color: #FFFFCC; border-style:outset; padding: 5px;"><a href="set_packages.php">Set Packages</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="set_subscriptions.php">Set Subscriptions</a></span>
<span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="set_memberships.php">Set Memberships</a></span>
<hr>
<small>Packages - When the billing system for Standard Posts and/or Premium Posts is enabled from the Main Config, the employer cannot post a job without first purchasing the required package of credits. If you want to give free credits to a particular employer, you can edit their account from Admin -> Employers.</small>
<h3>Set Packages for Posting Credits</h3>
<?php




$selected_package = $_REQUEST['selected_package'];
$package_label = $_REQUEST['package_label'];
$package_price = (float) $_REQUEST['package_price'];
$package_currency = $_REQUEST['package_currency'];
$package_posts_quantity= (int) $_REQUEST['package_posts_quantity'];
$premium= $_REQUEST['premium'];
$package_description = $_REQUEST['package_description'];
$new_package_label = $_REQUEST['new_package_label'];
$action = $_REQUEST['action'];
$package_edit_action = $_REQUEST['package_edit_action'];
$package_delete_action = $_REQUEST['package_delete_action'];
$package_name = $_REQUEST['package_name']; 


// packages

if (($package_edit_action != '') && ($selected_package!='')) { // "Edit" button must be pressed
	$sql = "UPDATE packages SET name='".jb_escape_sql($package_name)."', price='".jb_escape_sql($package_price)."', posts_quantity='".jb_escape_sql($package_posts_quantity)."', premium='".jb_escape_sql($premium)."', description='".jb_escape_sql($package_description)."', currency_code='".jb_escape_sql($package_currency)."' WHERE `package_id`='".jb_escape_sql($selected_package)."' ";
	JB_mysql_query ($sql) or die (mysql_error());
	$JBMarkup->ok_msg('Changes Saved.');
}

if ($package_delete_action != '') { // "Delete" button must be pressed.
	$sql =  "DELETE FROM packages WHERE package_id  = '".jb_escape_sql($selected_package)."' ";
	JB_mysql_query ($sql) or die (mysql_error());
	$JBMarkup->ok_msg('Package Deleted.');
}

if ($new_package_label != '') {  // the "Add" button was pressed, or Enter is hit. 
	$sql = "INSERT INTO packages (`name`, `price`, `posts_quantity`, `premium`, `currency_code`, `description`) VALUES('".jb_escape_sql($new_package_label)."', '".jb_escape_sql($package_price)."', '".jb_escape_sql($package_posts_quantity)."', '".jb_escape_sql($premium)."', '".jb_escape_sql($package_currency)."', '') ";
	JB_mysql_query ($sql) or die (mysql_error());
	$selected_package = JB_mysql_insert_id();
	$new_package_label = ""; // need to clear it, so we don't pupulate it back to the form
	$_REQUEST['new']="";
	$JBMarkup->ok_msg('New Package Added.');
}



// get the updated values from the database

if ($selected_package != '') {

	$sql = "SELECT * FROM `packages` WHERE `package_id`='".jb_escape_sql($selected_package)."' ";
	$result = JB_mysql_query($sql);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$package_label = $row['name'];
	$package_price = $row['price'];
	$package_description = $row['description'];
	$package_posts_quantity = $row['posts_quantity'];
	$premium = $row['premium'];
	$package_currency = $row['currency_code'];
		
	//echo "package price is: $package_price";

}

$sql = "SELECT * FROM `packages` ";
$result = JB_mysql_query($sql) or die(mysql_error());

?>

<?php 

?>

				

<?php if (mysql_num_rows($result) > 0) { ?>

				<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" >
				<tr bgColor="#eaeaea">
					<td ><span class="style1"><b>ID</b></span></td>
					<td ><span class="style1"><b>Package Name</b></span></td>
					<td><span class="style1"><b>Price</b></style></td>
					<td><span class="style1"><b>Currency</b></style></td>
					<td><b><span class="style1">Qty. Posts</span></b></td>
					<td><b><span class="style1">Premium?</span></b></td>
				</tr>		
				<?php					
					while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
						$mode = "";
						if ($row['package_id']==$selected_package)  {
							$mode = "edit";
						}

						if ($mode == 'edit') {

							echo "<form method='post' ACTION='".htmlentities($_SERVER['PHP_SELF'])."?package_edit_action=YES'>";

						}

						?>
						<tr bgcolor="#ffffff" onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);">
						<td>
							<?php echo $row['package_id'];?>
						</td>
						<td >
							<input onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?selected_package=<?php echo $row['package_id']; ?>'"  <?php if ($row['package_id']==$selected_package)  { echo " checked ";} ?>  type="radio" name="selected_package" value="<?php echo $row['package_id']?>">
							<?php
								if ($mode=='edit' ) {
									echo '<input type="text" name="package_name" value="'.jb_escape_html($row['name']).'">';

							} else { ?>
							<span class="style1"><?php echo jb_escape_html($row['name']); ?></span>
							<?php } ?>
						</td><td>
							<?php
								if ($mode=='edit' ) {
									echo "<input type='text' name='package_price' value='".$row['price']."'>";

							} else { ?>
							<span class="style1"><?php echo JB_format_currency($row['price'], $row['currency_code']); ?></span>
							<?php } ?>
						</td>
						<td>
							<?php
								if ($mode=='edit' ) {
									echo "<select name='package_currency'>";
									JB_currency_option_list($row['currency_code']);
									//echo "<input type='text' name='package_currency' value='".$row['posts_quantity']."'>";
									echo "</select>";

							} else { ?>
							<span class="style1"><?php echo $row['currency_code']; ?></span>
							<?php } ?>

						</td>
						<td>
							<?php
								if ($mode=='edit' ) {
									echo "<input type='text' name='package_posts_quantity' value='".$row['posts_quantity']."'>";

							} else { ?>
							<span class="style1">x<?php echo $row['posts_quantity']; ?></span>
							<?php } ?>
						</td><td>
							<?php
								if ($mode=='edit' ) { 
								?>
								<select type="text"  name="premium" >
									<option value=''></option>
									<option value='Y' <?php if ($row['premium']=='Y') { echo " selected "; } ?>>Y</option>
									<option value='N' <?php if ($row['premium']=='N') { echo " selected "; } ?>>N</option>

								</select>

								<?php

							} else { ?>
							<span class="style1"><?php echo $row['premium']; ?></span>
							<?php } ?>
						<?php

								if ($mode=='edit') {
									
							?>
							
							<tr><td colspan='4'></td>
							<tr><td>
							<input type='submit' value='Update' >
							<input type="button" onclick="if (!confirmLink(this, 'Delete, are you sure?')) { return false;} window.location='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?package_delete_action=YES&selected_package=<?php echo $selected_package;?>' " value="Delete">

							<?php } ?></span>							

						</td>
						</tr>

						<?php

							if ($mode == 'edit') {

								echo "</form>";

							}


					}
				
				
				?>

</td></tr>
				</table>
				</form>
				
				
				
				
<?php } 

?>
<p>
<input type="button" value="New Package..." onclick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?new=y'" >
</p>
<?php

if ($_REQUEST['new']=='y') {

?>
				<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?> " name="form2" >
				<input type="hidden" value="<?php echo $_REQUEST['new']; ?>" name="new">
		<table border="0" cellSpacing="1" cellPadding="3"  bgColor="#d9d9d9" >		
				
				<tr bgcolor='#ffffff'>
<td>
				<span class="style1">New Package Name: </span></td><td><input  type="text" name="new_package_label" value=""> </tr><tr bgcolor='#ffffff'>
				<td>
				<span class="style1">Price:</span></td><td> <input type="text" size="5" name="package_price"  value="" > <font size="2">(Enter a decimal, eg 10.00)</font>
				</td>
				</tr><tr bgcolor='#ffffff'><td>
				<span class="style1">Currency:</span></td><td> <select name="package_currency"  >
				<?php
					JB_currency_option_list ($selected);
				?></select><font size="2"></font></td>
</tr><tr bgcolor='#ffffff'>
				<td>
				<span class="style1">Quantity:</span></td><td> <input type="text" size="5" name="package_posts_quantity"  
				value="" > 
				</td>
				</tr><tr bgcolor='#ffffff'><td>
				<span class="style1">Premium Posts?</span></td><td> <select type="text"  name="premium" >
				<option value=''></option>
				<option value='Y'>Y</option>
				<option value='N'>N</option>		

				</select>
				</td>
				</tr>
				<tr bgcolor='#ffffff'><td colspan="2">
				<input type="submit" value="Add" name="package_edit_action">
				</td></tr>
				</table>
</form>
				

<?php

}

JB_admin_footer();

?>

				
			