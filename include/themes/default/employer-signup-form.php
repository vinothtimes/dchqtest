<form name="form1" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?page=signup&amp;form=filled&amp;<?php echo $q_string; ?>" enctype="multipart/form-data">
<input type="hidden" value='<?php echo $user_id; ?>' name='user_id'>
<table border="0" cellSpacing="1" cellPadding="3" class="dynamic_form" id='dynamic_form'>
<tr>
	<td  class="dynamic_form_field" ><?php echo $label["employer_signup_first_name"]; ?><span class="is_required_mark">*</span></td>
	<td class="dynamic_form_value"><input class='dynamic_form_text_style' name="FirstName" value="<?php echo JB_escape_html($DynamicForm->get_value('FirstName'));?>" type="text" id="FirstName"></td>
</tr>
<tr>
	<td class="dynamic_form_field" ><?php echo $label["employer_signup_last_name"];?><span class="is_required_mark">*</span> </td>
	<td class="dynamic_form_value"><input class='dynamic_form_text_style' name="LastName" value="<?php echo JB_escape_html($DynamicForm->get_value('LastName'));?>" type="text" id="LastName"></td>
</tr>
<tr>
	<td class="dynamic_form_field" valign="top" ><?php echo $label["employer_signup_business_name"];?> </td>
	<td class="dynamic_form_value"><input class='dynamic_form_text_style' name="CompName" value="<?php echo JB_escape_html($DynamicForm->get_value('CompName'));?>" size="30" type="text" id="compname"><span >(<?php echo $label["employer_signup_business_name2"];?>)</span></td>
</tr>

<?php
	if ($mode == "EDIT") {
		echo "<tr><td colspan='2'>[Section 1 (custom fileds can be defined in this section)]</td></tr>";
	}
	// display custom form, do not break the table 
	
	$DynamicForm->display_form_section($mode, 1, $admin, true); 
?>

<tr>
	<td class="dynamic_form_field" height="20">&nbsp;</td>
	<td class="dynamic_form_value" height="20">&nbsp;</td>
</tr>
<?php
if ((!$admin) && ($user_id=='')) {	// as displayed by signup.php
	?>
	<tr>
		<td class="dynamic_form_field" valign="top" ><?php echo $label["employer_signup_member_id"];?><span class="is_required_mark">*</span> </td>
		<td class="dynamic_form_value"><input class='dynamic_form_text_style'  name="Username" value="<?php echo JB_escape_html($DynamicForm->get_value('Username'));?>" type="text" id="Username"> <?php echo $label["employer_signup_member_id2"];?></td>
	</tr>

	<tr>
		<td class="dynamic_form_field" nowrap ><?php echo $label["employer_signup_password"]; ?><span class="is_required_mark">*</span></td>
		<td class="dynamic_form_value"><input class='dynamic_form_text_style' name="Password" type="password" value="<?php echo JB_escape_html($DynamicForm->get_value('Password'));?>" id="password"></td>
	</tr>
	<tr>
		<td class="dynamic_form_field" ><?php echo $label["employer_signup_password_confirm"];?><span class="is_required_mark">*</span></td>
		<td class="dynamic_form_value"><input class='dynamic_form_text_style' name="Password2" type="password" value="<?php echo JB_escape_html($DynamicForm->get_value('Password2'));?>" id="Password2"></td>
	</tr>
	<?php

} 

if ($admin) {
?>
	<tr>
		<td class="dynamic_form_field" valign="top" >Username</td>
		<td class="dynamic_form_value"><?php echo JB_escape_html($DynamicForm->get_value('Username'));?> (# <?php echo jb_escape_html($DynamicForm->get_value('ID'));?>)</td>
	</tr>

<?php
}

?>
<tr><td colspan="2" class="dynamic_form_2_col_field">&nbsp;</td></tr>
<tr>
	<td class="dynamic_form_field" ><?php echo $label["employer_signup_your_email"];?><span class="is_required_mark">*</span></td>
	<td class="dynamic_form_value"><input class='dynamic_form_text_style' name="Email" type="text" id="email" value="<?php echo JB_escape_html($DynamicForm->get_value('Email')); ?>" size="30"></td>
</tr>
<tr>
	<td class="dynamic_form_field" ><?php echo $label["employer_signup_newsletter"];?> </td>
	<td class="dynamic_form_value">
		<input class="dynamic_form_radio_style" name="Newsletter" type="radio" <?php if ($DynamicForm->get_value('Newsletter')=='1') { echo ' checked '; } ?> value="1" ><span ><?php echo $label["yes_option"];?></span>
		<input class="dynamic_form_radio_style" name="Newsletter" type="radio" <?php if ($DynamicForm->get_value('Newsletter')=='0') { echo ' checked '; } ?> value="0"><span ><?php echo $label["no_option"];?></span>
	</td>
</tr>
<tr>
	<td class="dynamic_form_field" ><?php echo $label["employer_signup_new_resumes"];?> </td>
	<td class="dynamic_form_value">
		<input class="dynamic_form_radio_style" name="Notification1" type="radio" value="1" <?php if ($DynamicForm->get_value('Notification1')=='1') { echo ' checked '; } ?> ><span ><?php echo $label["yes_option"];?></span>
		<input class="dynamic_form_radio_style" name="Notification1" type="radio" value="0" <?php if ($DynamicForm->get_value('Notification1')=='0') { echo ' checked '; } ?> ><span ><?php echo $label["no_option"]; ?></span>
	</td>
</tr>

<?php if (JB_EMP_LANG_ENABLED=='YES') {?>
	<tr>

	<td class="dynamic_form_field" ><?php echo $label["employer_signup_language"]; ?></td>
	<td class="dynamic_form_value"><select name="lang"  id="lang"  size="2">

	<?php

	$sql = "SELECT lang_code, name FROM lang where is_active='Y' ";
	$result = JB_mysql_query ($sql);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		if ($DynamicForm->get_value('lang') == $row['lang_code']) { 
			$sel =  "selected"; 
		} else {$sel = '';}
		echo "<option value=".jb_escape_html($row['lang_code'])." $sel>".jb_escape_html($row['name'])."</option>";
	}

	?>

	</select></td>
	</tr>
<?php }

?>


<?php
	if ($mode == "EDIT") {
		echo "<tr><td colspan='2'>[Section 2 (custom fileds can be defined in this section)]</td></tr>";
	}
	// display custom form, do not break the table
	$DynamicForm->display_form_section($mode, 2, $admin, true); 
?>

<?php

if ($admin) {

	

?>

	<tr bgcolor="#E2E2E2"><td colspan="2">Posting Credits. </td>
	</tr>

	 <tr>
		<td width="25%" valign="top" >Posting Credits: </td>
		<td width="86%"><input name="posts_balance" value="<?php echo jb_escape_html($DynamicForm->get_value('posts_balance'));?>" size="4" type="text" id="posts_balance"></span></td>
	</tr>

	 <tr>
		<td width="25%" valign="top" >Premium Posting Credits: </td>
		<td width="86%"><input name="premium_posts_balance" value="<?php echo jb_escape_html($DynamicForm->get_value('premium_posts_balance'));?>" size="4" type="text" id="premium_posts_balance"></td>
	</tr>

	<tr  bgcolor="#E2E2E2"><td colspan="2">Subscription </td>

	</tr>

	<?php

	$subscr_row = jb_get_active_subscription_invoice($_REQUEST['user_id']); 

	if ($subscr_row==true) { // echo the manage button

		?>

		<tr>
		<td width="25%" ></td>
		<td width="86%">
			<input type="button" value="Modify Subscription" onclick="window.open('subscr_modify.php?invoice_id=<?php echo $subscr_row['invoice_id'];?>&product_type=S', '', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=560,left = 50,top = 50');return false;">
		</td>
	</tr>


		<?php

	} else { // user does not have any active subscription

	?>


	<tr>
		<td width="25%" >Can View Resumes?</td>
		<td width="86%">
			<input name="subscription_can_view_resume" type="radio" value="Y" <?php if ($DynamicForm->get_value('subscription_can_view_resume')=='Y') { echo "checked"; } ?> ><span ><?php echo $label["yes_option"];?></span>
			<input name="subscription_can_view_resume" type="radio" value="N" <?php if ($DynamicForm->get_value('subscription_can_view_resume')=='N') { echo "checked"; } ?>><span ><?php echo $label["no_option"]; ?></span>
		</td>
	</tr>
	
	<tr>
		<td width="25%" >Can Post for free?</td>
		<td width="86%">
			<input name="subscription_can_post" type="radio" value="Y" <?php if ($DynamicForm->get_value('subscription_can_post')=='Y') { echo "checked"; } ?> ><span ><?php echo $label["yes_option"];?></span>
			<input name="subscription_can_post" type="radio" value="N" <?php if ($DynamicForm->get_value('subscription_can_post')=='N') { echo "checked"; } ?>><span ><?php echo $label["no_option"]; ?></span>
		</td>
	</tr>
	<tr>
		<td width="25%" >Can Premium Post for free? </td>
		<td width="86%">
			<input name="subscription_can_premium_post" type="radio" value="Y" <?php if ($DynamicForm->get_value('subscription_can_premium_post')=='Y') { echo "checked"; } ?> ><span ><?php echo $label["yes_option"];?></span>
			<input name="subscription_can_premium_post" type="radio" value="N" <?php if ($DynamicForm->get_value('subscription_can_premium_post')=='N') { echo "checked"; } ?>><span ><?php echo $label["no_option"]; ?></span>
		</td>
	</tr>

	<tr>
		<td width="25%" >Can View Blocked Fields? </td>
		<td width="86%">
			<input name="can_view_blocked" type="radio" value="Y" <?php if ($DynamicForm->get_value('can_view_blocked')=='Y') { echo "checked"; } ?> ><span ><?php echo $label["yes_option"];?></span>
			<input name="can_view_blocked" type="radio" value="N" <?php if ($DynamicForm->get_value('can_view_blocked')=='N') { echo "checked"; } ?>><span ><?php echo $label["no_option"]; ?> (If this feature is turned on in the config, only subscribed users will be able to see these fields on the resumes)</span>
		</td>
	</tr>
	<?php

	} // end if ($subscr_row==false) {

	?>
	<tr  bgcolor="#E2E2E2"><td colspan="2">Membership</td>

	</tr>

	<tr>
		<td width="25%" >Membership Active?</td>
		<td width="86%">
			<input name="membership_active" type="radio" value="Y" <?php if ($DynamicForm->get_value('membership_active')=='Y') { echo "checked"; } ?> ><span ><?php echo $label["yes_option"];?></span>
			<input name="membership_active" type="radio" value="N" <?php if ($DynamicForm->get_value('membership_active')=='N') { echo "checked"; } ?>><span ><?php echo $label["no_option"]; ?></span>
		</td>
	</tr>

<?php


} // admin mode


?>
			
</table>

<p><input type="submit" class="form_submit_button" name="Submit" value="<?php echo $label["employer_signup_submit"]; ?>">
</p>

</form>