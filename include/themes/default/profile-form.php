<?php

/*
	Profile form: form_id = 3

	Here you can modify the profile form.
	This file is a file php mixed with PHP.
	The php code is located between  < ?php and ? > tags.
	Anything outside these tags is HTML code and can be modified as normal HTML

	The form has 3 modes possible: 'view', 'edit' and 'EDIT'
	
	view - for displaying data, the data that is stored in these fields is displayed, instead
	of the form field

	edit - the form is displayed for editing the data by the users

	EDIT - the form is displayed for editing the fileds by the Admin

	How to display data from the individual fields?

	- Each Field has a 'Template Tag' which is specified when editing the form.
	Use the $DynamicForm->get_template_value($template_tag) method to get the data from the field.
	For example, this will display the name in the profile:

	echo $DynamicForm->get_template_value('PROFILE_BNAME'); 

	The profile form has 3 sections by default.
	To display a section, a call to the display_form_section() method is made. For example, to
	print a section 1 section like this:
	$DynamicForm->display_form_section($mode, 1, $admin);



	*/



	if ($mode == 'edit') {
	?>
		<form method="POST"  action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" name="form1" onsubmit=" form1.savebutton.disabled=true;" enctype="multipart/form-data">
		<input type="hidden" name="mode" value="<?php echo jb_escape_html($mode); ?>">
		<input type="hidden" name="profile_id" size="" value="<?php echo jb_escape_html($DynamicForm->get_value('profile_id')); ?>">
	<?php } ?>

	<table border="0" cellpadding="0" cellspacing="0" class="profile_data"  id="profile"   >
	<?php  if (($error != '' ) && ($mode!='EDIT')) { ?>
	<tr>
		<td valign="top" colspan="2"><?php  echo "<span class='error_msg_label'>".$label['profile_save_error']."</span><br> <b>".$error."</b>";  ?></td>
	</tr>
	<?php } ?>
  <tr>
    <td valign="top" class="profile_data">
	<?php if ($mode == "EDIT") {
					echo "[Section 1]";
				}
		 // section 1
		$DynamicForm->display_form_section($mode, 1, $admin);
	?>
    </td>
    <td class="profile_data"  valign="top" rowSpan="1">
    <?php 
		if ($mode == "EDIT") {
					echo "[Section 2]";
				}
		// section 2
		$DynamicForm->display_form_section($mode, 2, $admin);
	?></td>
  </tr>

  <tr >
    <td class="profile_data" colSpan="2">
		<?php 
	  if ($mode == "EDIT") {
					echo "[Section 3]";
				}
		// section 3
		$DynamicForm->display_form_section($mode, 3, $admin);
		?></td>
	
  </tr>
  <?php
	if ($mode == 'edit') {
	?>
 		<tr><td colspan="2" >
		<input type="hidden" name="save" id="save101" value="">
		
		<input class="form_submit_button" TYPE="SUBMIT"  name="savebutton" value="<?php echo $label['profile_save_button'];?>" onClick="save101.value='1';">
		
		</td></tr>
		</form>
	<?php

	}
  ?>
	</table>
	