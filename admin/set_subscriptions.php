<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require ("../config.php");
require (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Admin -> Set Subscriptions');


?>

<b>[Prices]</b> 
	<span style="background-color: #F2F2F2; border-style:outset; padding: 5px;"><a href="set_packages.php">Set Packages</a></span>
<span style="background-color: #FFFFCC; border-style:outset; padding:5px; "><a href="set_subscriptions.php">Set Subscriptions</a></span>
<span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="set_memberships.php">Set Memberships</a></span>
<hr>
<small>Subscriptions - Employer cannot view the resumes (CVs) until a fee is paid. The subscription is counted by months. Subscriptions can be enabled/disabled in the Main Config. See also the 'Blocked Fields' option in Main Config. If you want to give a free subscription to an Employer, go to Admin->Subscription orders, click New Invoice, Place, Confirm and Complete the order. </small>
<h3>Set Subscriptions</h3>
<?php



$selected_subscription = $_REQUEST['selected_subscription'];
$subscription_label = $_REQUEST['subscription_label'];
$subscription_price = (float) $_REQUEST['subscription_price'];
$subscription_currency = $_REQUEST['subscription_currency'];
$new_subscription_label = $_REQUEST['new_subscription_label'];
$subscription_duration= (int) $_REQUEST['subscription_duration'];
$subscription_description = $_REQUEST['subscription_description'];
$subscription_can_view_resumes = $_REQUEST['subscription_can_view_resumes'];
$subscription_can_post = $_REQUEST['subscription_can_post'];
$subscription_can_post_premium = $_REQUEST['subscription_can_post_premium'];
$subscription_can_view_blocked = $_REQUEST['subscription_can_view_blocked'];
$action = $_REQUEST['action'];
$description = $_REQUEST['description'];

$subscription_edit_action = $_REQUEST['subscription_edit_action'];
$subscription_detele_action = $_REQUEST['subscription_detele_action'];
$posts_quota =  $_REQUEST['posts_quota'];
$p_posts_quota = $_REQUEST['p_posts_quota'];
$views_quota = $_REQUEST['views_quota'];

$views_quota = (strtoupper($_REQUEST['views_quota'])=='N') ? -1 : (int) $_REQUEST['views_quota'];
$p_posts_quota = (strtoupper($_REQUEST['p_posts_quota'])=='N') ? -1 : (int) $_REQUEST['p_posts_quota'];
$posts_quota = (strtoupper($_REQUEST['posts_quota'])=='N') ? -1 : (int) $_REQUEST['posts_quota'];

$views_quota = ($_REQUEST['views_quota']==='') ? -1 : (int) $views_quota;
$p_posts_quota = ($_REQUEST['p_posts_quota']==='') ? -1 : (int) $p_posts_quota;
$posts_quota = ($_REQUEST['posts_quota']==='') ? -1 : (int) $posts_quota;


// Combos

if (($subscription_edit_action != '') && ($selected_subscription!='')) { // "Edit" button must be pressed
	$sql = "UPDATE subscriptions SET name='".jb_escape_sql($subscription_label)."', price='".jb_escape_sql($subscription_price)."', months_duration='".jb_escape_sql($subscription_duration)."', `description`='".jb_escape_sql($subscription_description)."', `can_view_resumes`='".jb_escape_sql($subscription_can_view_resumes)."', can_post='".jb_escape_sql($subscription_can_post)."', `can_post_premium`='".jb_escape_sql($subscription_can_post_premium)."', `currency_code`='".jb_escape_sql($subscription_currency)."', can_view_blocked='".jb_escape_sql($subscription_can_view_blocked)."', `description`='".jb_escape_sql($description)."', `posts_quota`='".jb_escape_sql($posts_quota)."', `p_posts_quota`='".jb_escape_sql($p_posts_quota)."', `views_quota`='".jb_escape_sql($views_quota)."' WHERE `subscription_id`='".jb_escape_sql($selected_subscription)."' ";

	JB_mysql_query ($sql) or die (mysql_error());
	$JBMarkup->ok_msg('Changes Saved.');
	$selected_subscription = '';
}

if ($subscription_detele_action != '') { // "Delete" button must be pressed.

	// are there any active subscriptions for this subscription plan?


	$sql = "SELECT subscription_id FROM subscription_invoices WHERE subscription_id='".jb_escape_sql($selected_subscription)."' AND ((`status`='Completed' ) OR ((`status`='Pending') AND `reason`='jb_credit_advanced'))";

	$result = jb_mysql_query($sql);

	if (mysql_num_rows($result)>0) {
		$JBMarkup->error_msg('Subscription plan cannot be deleted. The system found that there are some active subscription(s) which for this subscription plan. Please modify these subscription(s) in Admin-&gt;Subscriptions so that they are not active, and try to delete them here again');
	} else {

		$sql =  "DELETE FROM subscriptions WHERE subscription_id  = '".jb_escape_sql($selected_subscription)."' ";

		JB_mysql_query ($sql) or die (mysql_error());
		$JBMarkup->ok_msg('Subscription Plan Deleted.');
	}
}

if ($new_subscription_label != '') {  // the "Add" button was pressed, or Enter is hit. 
	$sql = "INSERT INTO subscriptions (`name`, `price`, `months_duration`, `can_post`, `can_view_resumes` , `can_post_premium`, `currency_code`, `can_view_blocked`, `description`, posts_quota, p_posts_quota, views_quota) VALUES('".jb_escape_sql($new_subscription_label)."', '".jb_escape_sql($subscription_price)."', '".jb_escape_sql($subscription_duration)."',  '".jb_escape_sql($subscription_can_post)."', '".jb_escape_sql($subscription_can_view_resumes)."' , '".jb_escape_sql($subscription_can_post_premium)."', '".jb_escape_sql($subscription_currency)."' , '".jb_escape_sql($subscription_can_view_blocked)."', '".jb_escape_sql($description)."', '".jb_escape_sql($posts_quota)."', '".jb_escape_sql($p_posts_quota)."', '".jb_escape_sql($views_quota)."') ";
	//echo $sql."<br>";
	JB_mysql_query ($sql) or die (mysql_error());
	$selected_subscription = JB_mysql_insert_id();
	$new_subscription_label = ""; // need to clear it, so we don't pupulate it back to the form
	$_REQUEST['new']="";
	$JBMarkup->ok_msg('Subscription Plan Updated.');
}


if ($selected_subscription != '') {

	$sql = "SELECT * FROM `subscriptions`  WHERE `subscription_id`='".jb_escape_sql($selected_subscription)."' ";
	$result = JB_mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$subscription_label = $row['name'];
	$subscription_price = $row['price'];
	$subscription_currency = $row['currency_code'];
	$subscription_description = $row['description'];
	$subscription_duration = $row['months_duration'];
	$subscription_can_post = $row['can_post'];
	$subscription_can_post_premium = $row['can_post_premium'];
	$subscription_can_view_resumes = $row['can_view_resumes'];
	$subscription_can_view_blocked = $row['can_view_blocked'];
	$description = $row['description'];
	$posts_quota = $row['posts_quota'];
	$p_posts_quota = $row['p_posts_quota'];
	$views_quota = $row['views_quota'];

	if ($row['views_quota']<1) {
		$views_quota = 'N';
	}

	if ($row['p_posts_quota']<1) {
		$p_posts_quota = 'N';
	}

	if ($row['posts_quota']<1) {
		$posts_quota = 'N';
	}
}

?>


<?php 

$sql = "SELECT * FROM `subscriptions` order by price ";
$result = JB_mysql_query($sql) or die(mysql_error());

?>

				

<?php if (mysql_num_rows($result) > 0) { ?>
				<table border="0" cellSpacing="1" cellPadding="3"  bgColor="#d9d9d9" >
				<tr bgColor="#eaeaea">
				<td ><span class="style1"><b>ID</b></span></td>
				<td >
					<b><span class="style1">Subscription Name</span></b>
				</td><td>
					<b><span class="style1">Price</span></b>
				</td><td>
					<b><span class="style1">Currency</span></b>
				</td><td>
					<b><span class="style1">Duration (months)</span></b>
				</td><td>
					<b><span class="style1">can post free</span></b>
				</td><td>
					<b><span class="style1">can post premium free</span></b>
				</td><td>
					<b><span class="style1">view resumes</span></b>
				</td><td>
					<b><span class="style1">view blocked fields</span></b>
				</td></tr>
				<?php

					
					while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

						if ($color == "") {
							$color = "#EFEFEF";
						} else {
							$color = "";

						}

						$mode = "";

						if ($row['subscription_id']==$selected_subscription)  {
							$mode = "edit";
						}

						if ($mode == 'edit') {

							echo "<form method='post' ACTION='".htmlentities($_SERVER['PHP_SELF'])."?subscription_edit_action=YES'>";

						}

						$color="#ffffff";

						?>
						<tr onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);" bgColor="#ffffff" bgcolor="#ffffff">
						<td><?php echo $row['subscription_id']; ?></td>
						<td nowrap>
							<input type="radio" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?selected_subscription=<?php echo $row['subscription_id']; ?>'" name="selected_subscription"  <?php if ($row['subscription_id']==$selected_subscription)  { echo " checked ";} ?> value="<?php echo $row['subscription_id'];?>">
							<span class="style1">
								<?php 

															
								if ($mode=='edit' ) { 
									echo "<input size='30' type='text' name='subscription_label' value='".jb_escape_html($row['name'])."'> <br>";
									echo "Description:<br><textarea rows='3' cols='30' type='text' name='description' >".$row['description']."</textarea> <br>";
									
									
								} else {
							
									echo jb_escape_html($row['name'])."<br>".jb_escape_html($row['description']);
									
								
								}?></span>
						</td><td valign="top">
							<span class="style1">
								<?php 
							
								if ($mode=='edit' ) {
									echo "<input size='3' type='text' name='subscription_price' value='".$row['price']."'>";
									
								} else {
									echo JB_format_currency($row['price'], $row['currency_code']);
									
								}?></span>
						</td>
						<td valign="top">
							<?php
								if ($mode=='edit' ) {
									echo "<select name='subscription_currency'>";
									JB_currency_option_list($row['currency_code']);
									//echo "<input type='text' name='package_currency' value='".$row['posts_quantity']."'>";
									echo "</select>";

							} else { ?>
							<span class="style1"><?php echo $row['currency_code']; ?></span>
							<?php } ?>

						</td>
						<td valign="top">
							<span class="style1">
								<?php 
							
								if ($mode=='edit' ) {
									echo "<input size='3' type='text' name='subscription_duration' value='".$row['months_duration']."'>";
									
								} else {
									echo $row['months_duration']. " months";
									
								}?>
								
						</td><td valign="top">
							<span class="style1">
								<?php 
							
								if ($mode=='edit' ) {
									//echo "<input size='2' type='text' name='' value='".$row[months_duration]."'>";
									?>

									<select   name="subscription_can_post"   >

									<option value=''></option>
				<option value='Y' <?php if ($row['can_post']=='Y') { echo " selected "; } ?> >Y</option>
				<option value='N' <?php if ($row['can_post']=='N') { echo " selected "; } ?>>N</option>
				</select><br>
				Quota:<br><input type="text" name="posts_quota" size="2" value="<?php echo $posts_quota; ?>"><br>(N = No quota)

									<?php
									
								} else {
									echo $row['can_post'];
									if ($row['can_post']=='Y' && $row['posts_quota'] > 0) {
										echo " (".$row['posts_quota']."/month) ";
									}
									
								}?>
								</span>
						</td><td valign="top">
							<span class="style1">
								<?php 
							
								if ($mode=='edit' ) {
									//echo "<input size='2' type='text' name='' value='".$row[months_duration]."'>";
									?>

								 <select type="text"  name="subscription_can_post_premium"   >

									<option value=''></option>
				<option value='Y' <?php if ($row['can_post_premium']=='Y') { echo " selected "; } ?> >Y</option>
				<option value='N' <?php if ($row['can_post_premium']=='N') { echo " selected "; } ?>>N</option>
				</select><br>Quota:<br>
				<input type="text" name="p_posts_quota" size="2" value="<?php echo $p_posts_quota; ?>"><br>(N = No quota)

									<?php
									
								} else {
									echo $row['can_post_premium'];
									if ($row['can_post_premium']=='Y' && $row['p_posts_quota'] > 0) {
										echo " (".$row['p_posts_quota']."/month) ";
									}
									
								} ?>
								</span>
						</td><td valign="top">
							<span class="style4">
							<?php
							if ($mode=='edit' ) {
									//echo "<input size='2' type='text' name='' value='".$row[months_duration]."'>";
									?>

								 <select type="text"  name="subscription_can_view_resumes"   >

									<option value=''></option>
				<option value='Y' <?php if ($row['can_view_resumes']=='Y') { echo " selected "; } ?> >Y</option>
				<option value='N' <?php if ($row['can_view_resumes']=='N') { echo " selected "; } ?>>N</option>
				</select><br>Quota:<br>
				<input type="text" name="views_quota" size="2" value="<?php echo $views_quota; ?>"><br>(N= No quota)

									<?php
									
								} else {
									echo $row['can_view_resumes'];
									if ($row['can_view_resumes']=='Y' && $row['views_quota'] > 0) {
										echo " (".$row['views_quota']."/month) ";
									}
									
								}?>
								
						</td>
						<td valign="top">
							<span class="style4">
							<?php
							if ($mode=='edit' ) {
									//echo "<input size='2' type='text' name='' value='".$row[months_duration]."'>";
									?>

								 <select type="text"  name="subscription_can_view_blocked"   >

									<option value=''></option>
				<option value='Y' <?php if ($row['can_view_blocked']=='Y') { echo " selected "; } ?> >Y</option>
				<option value='N' <?php if ($row['can_view_blocked']=='N') { echo " selected "; } ?>>N</option>
				</select>

									<?php
									
								} else {
									echo $row['can_view_blocked'];
									
								}?>
								
						</td>
						
						</tr>
							<span class="style4">
							<?php

								if ($mode=='edit') {
									
							?>
							
							<tr><td colspan='7'>
							<input type='submit' value='Update' >
							<input type="button" onclick="if (!confirmLink(this, 'Delete, are you sure?')) { return false;} window.location='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?subscription_detele_action=YES&selected_subscription=<?php echo $selected_subscription;?>' " value="Delete">

							<?php } ?>
								</span>

						</td>
						</tr>

						<?php

							if ($mode == 'edit') {
								echo "</form>";

							}

					}
				
				?>
			
				</form>
				</table>
				

<?php }
?>

<p>
<input type="button" value="New Subscription Plan..." onclick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?new=y'" >
</p>

<?php

if ($mode=='edit' ) {

	echo "Note: All Quota settings are per month, and indicate how much can used up per month. After each month, the quotas get reset. Updating quota settings for a plan will not affect already subscribed employers until the next month. A quota can be a positive number or the letter N if you want to have an unlimited quota.";


}

if ($_REQUEST['new']=='y') {

?>
				<p>Please enter a new subscription plan:<br>
				<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?> " name="form2" >
				<input type="hidden" value="<?php echo $_REQUEST['new']; ?>" name="new">
<table border="0" cellSpacing="1" cellPadding="3"  bgColor="#d9d9d9" >
<tr bgcolor='#ffffff'><td>
				<span class="style1">Subscription Name:</span></td><td> <input type="text" name="new_subscription_label"  value=""> </td>
</tr>
<tr bgcolor='#ffffff'><td>
				<span class="style1">Description:</span></td><td> <input type="text" name="description"  value=""> </td>
</tr><tr bgcolor='#ffffff'><td>
				<span class="style1">Price:</span></td><td> <input type="text" size="3" name="subscription_price"  value="" ><font size="2">(enter a decimal value, eg 29.99)</font></td>
</tr><tr bgcolor='#ffffff'><td>
				<span class="style1">Currency:</span></td><td> <select name="subscription_currency"  >
				<?php
					JB_currency_option_list ($selected);
				?></select><font size="2"></font></td>
</tr><tr bgcolor='#ffffff'>	<td>
				<span class="style1">Duration:</span></td> <td><input type="text" size="3" name="subscription_duration"  value="" ><font size="2">(In months. Enter an integer, eg 6)</font></td>
</tr><tr bgcolor='#ffffff'><td>
				<span class="style1">Can Post for free?
				</span></td><td> <select type="text"  name="subscription_can_post"   >
				<option value=''></option>
				<option value='Y'>Y</option>
				<option value='N'>N</option>
				</select><br>
				Monthly Free posts quota:<input size="2" type="text" name="posts_quota"> (N = no quota)
				</td>
</tr><tr bgcolor='#ffffff'><td>
				<span class="style1">Can Premium Post for free?</span> </td><td><select type="text"  name="subscription_can_post_premium"  >
				<option value=''></option>
				<option value='Y' >Y</option>
				<option value='N'>N</option>
				</select><br>
				Monthly Free posts quota:<input size="2" type="text" name="p_posts_quota"> (N = no quota)
				</td>
</tr><tr bgcolor='#ffffff'><td>
				<span class="style1">Can view resumes:</span> </td><td><select type="text" name="subscription_can_view_resumes"  >
				<option value=''></option>
				<option value='Y' selected >Y</option>
				<option value='N' >N</option>
				</select>
				<br>
				Monthly views quota:<input size="2" type="text" name="views_quota"> (N = no quota)
				</td>
</tr>
<tr bgcolor='#ffffff'><td>
				<span class="style1">Can view blocked fields:</span> </td><td><select type="text" name="subscription_can_view_blocked"  >
				<option value=''></option>
				<option value='Y' selected >Y</option>
				<option value='N' >N</option>
				</select>
				</td>
</tr>
<tr bgcolor='#ffffff'><td colspan="2">
				
				 <input type="submit" value="Add" name="subscription_add_action">
</td></tr><tr>
</table>

<?php

}

JB_admin_footer();


?>
				
				
			