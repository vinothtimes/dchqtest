<form name="form1" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?page=signup&amp;form=filled" enctype="multipart/form-data">
	
<input type="hidden" value="<?php echo $user_id; ?>" name="user_id">
<input type='hidden' name='action' value='edit' >
<table   border="0" cellSpacing="1" cellPadding="3" class="dynamic_form" id='dynamic_form'>
<tr>
	<td class="dynamic_form_field"><?php echo $label["c_signup_fname"];?><span class="is_required_mark">*</span> </td>
	<td class="dynamic_form_value"><input name="FirstName" value="<?php echo JB_escape_html($DynamicForm->get_value('FirstName'));?>" type="text" id="firstname"></td>
</tr>
<tr>
	<td class="dynamic_form_field"><?php echo $label["c_signup_lname"];?><span class="is_required_mark">*</span> </td>
	<td class="dynamic_form_value"><input name="LastName" value="<?php echo JB_escape_html($DynamicForm->get_value('LastName'));?>" type="text"></td>
</tr>

<?php
	if ($mode == "EDIT") {
		echo "<tr><td colspan='2'>[Section 1 (custom fileds can be defined in this section)]</td></tr>";
	}
	// display custom form, section 1, do not break the table = true
	
	$DynamicForm->display_form_section($mode, 1, $admin, true); 
?>
<tr>
	<td class="dynamic_form_field" height="20">&nbsp; </td>
	<td class="dynamic_form_value" height="20">&nbsp;</td>
</tr>
<?php
if ((!$admin) && ($user_id=='')) {	
?>
	<tr>
		<td class="dynamic_form_field"><?php echo $label["c_signup_memberid"];?><span class="is_required_mark">*</span> </td>
		<td class="dynamic_form_value"><input name="Username" value="<?php echo JB_escape_html($DynamicForm->get_value('Username'));?>" type="text" ><?php echo $label["c_signup_memberid2"];?></td>
	</tr>
	<tr>
		<td class="dynamic_form_field"><?php echo $label["c_signup_password"];?><span class="is_required_mark">*</span></td>
		<td class="dynamic_form_value"><input name="Password" value="<?php echo JB_escape_html($DynamicForm->get_value('Password'));?>" type="password" id="password"></td>
	</tr>
	<tr>
		<td class="dynamic_form_field"><?php echo $label["c_signup_password2"];?><span class="is_required_mark">*</span></td>
		<td class="dynamic_form_value"><input name="Password2" type="password" value="<?php echo JB_escape_html($DynamicForm->get_value('Password2'));?>" id="password2"></td>
	</tr>
	<?php

}

?>
<tr>
	<td class="dynamic_form_field" height="20">&nbsp; </td>
	<td class="dynamic_form_value" height="20">&nbsp;</td>
</tr>
<tr>
	<td class="dynamic_form_field"><?php echo $label["c_signup_email"]; ?><span class="is_required_mark">*</span></td>
	<td class="dynamic_form_value"><input size='35' name="Email" type="text" value="<?php echo jb_escape_html($DynamicForm->get_value('Email'));?>" id="email"></td>
</tr>
<tr>
	<td class="dynamic_form_field"><?php echo $label["c_signup_newsletter"];?> </td>
	<td class="dynamic_form_value">
		<input name="Newsletter" type="radio" value="1" <?php if ($DynamicForm->get_value('Newsletter')=='1') { echo ' checked '; } ?> ><?php echo $label["c_signup_yes"];?>
		<input name="Newsletter" type="radio" value="0" <?php if ($DynamicForm->get_value('Newsletter')=='0') { echo ' checked '; } ?>><?php echo $label["c_signup_no"];?>
	</td>
</tr>
<tr>
	<td class="dynamic_form_field"><?php echo $label["c_signup_alerts"];?> </td>
	<td class="dynamic_form_value">
		<input name="Notification1" type="radio" value="1" <?php if ($DynamicForm->get_value('Notification1')=='1') { echo ' checked '; } ?>><?php echo $label["c_signup_yes"]; ?>
		<input name="Notification1" type="radio" value="0" <?php if ($DynamicForm->get_value('Notification1')=='0') { echo ' checked '; } ?>><?php echo $label["c_signup_no"];?>
	</td>
</tr>
<?php 
if (JB_CAN_LANG_ENABLED=='YES') {?>
	<tr>


	<td class="dynamic_form_field" ><?php echo $label["employer_signup_language"]; ?></td>
	<td class="dynamic_form_value"><select name="lang"  id="lang"  size="2">

		<?php

	$sql = "SELECT lang_code, name FROM lang where is_active='Y' ";
	$result = JB_mysql_query ($sql);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		if ($DynamicForm->get_value('lang') == $row['lang_code']) { $sel =  "selected"; } else {$sel = '';}
		echo "<option value=".jb_escape_html($row['lang_code'])." $sel>".jb_escape_html($row['name'])."</option>";

	}

?>

</select></td>
</tr>
<?php
 


}
	

if ($mode == "EDIT") {
	echo "<tr><td colspan='2'>[Section 2 (custom fileds can be defined in this section)]</td></tr>";
}
// display custom form, section 2, not admin, do not break the table
$DynamicForm->display_form_section($mode, 2, $admin, true); 
	


if ($admin) {

?>
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


<p><input class="form_submit_button" type="submit" name="Submit" value="<?php echo $label["c_signup_submit"];?>">

</p>

</form>