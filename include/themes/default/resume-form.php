<?php
/*
	Resume form: form_id = 2

	Here you can modify the resume form.
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
	Use the $DynamicForm->get_template_value($template_tag, $form_id) method to get the data from the field.
	For example, this will display the email of the resume:

	echo $DynamicForm->get_template_value('EMAIL', 2); 

	The resume form has 3 sections by default.
	To display a section, a call to the display_form_section() method is made. For example, to
	print a section 1 section like this:

	$DynamicForm->display_form_section($mode, 3, $admin);

	How to make all resumes anonymous

	Replace this checkbox

	<input type="checkbox" value="Y" name="anon" ?php if($DynamicForm->get_value('anon')=='Y') { echo ' checked ';} ? >

	With:

	<input type="hidden" value="Y" name="anon"  >



	*/
	
	

	if ($mode != 'view') { // editing mode, output the start of the <form> and hidden fields

		?>
		<form method="POST" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" name="form1" onsubmit=" form1.savebutton.disabled=true;" enctype="multipart/form-data">
		<input type="hidden" name="mode" size="" value="<?php echo jb_escape_html($mode); ?>">
		<input type="hidden" name="resume_id" size="" value="<?php echo jb_escape_html($DynamicForm->get_value('resume_id')); ?>">
		<input type="hidden" name="user_id" size="" value="<?php echo jb_escape_html($DynamicForm->get_value('user_id')); ?>">
		<?php
	}

	// begin the Resume Form
	?>

	<table border="0" cellSpacing="1" cellPadding="5" class="resume_data" >
	<tr><td colspan="2" >
	<?php 
	
	// checkbox for job seeker when editing the resume
	if (($mode!='view') && (JB_RESUME_REQUEST_SWITCH!='NO') ) {
		
		?>
		<input type="checkbox" value="Y" name="anon" <?php if($DynamicForm->get_value('anon')=='Y') { echo ' checked ';} ?> > <?php echo $label["resume_priv_notice"]; ?>
		<?php
	}

	
?>
	</td></tr>
	<?php  
	// check for error, and display an error message is there is an error when saving
	if (($error != '' ) && ($mode!='EDIT')) { ?>
	<tr>
		<td colspan="2"><?php  echo "<span class='error_msg_label'>".$label["resume_save_error"]."</span><br> ".$error."";  ?></td>
	</tr>
	<?php } ?>
  <tr>
    <td  valign="top" >
	<?php 
		 if ($mode == "EDIT") { 
					echo "[Section 1]";
		}
		// JB_display_form function will display the form from the database
		$DynamicForm->display_form_section($mode, 1, $admin);
	?>

    </td>

	<?php
	// check if there are any fields in section 2
	$sql = "SELECT * FROM form_fields WHERE form_id=2 AND section=2 ";
	$result = JB_mysql_query ($sql);
	if (mysql_num_rows($result)>0) {
		// display section 2
	
	?>
    <td valign="top"  rowspan="2">
    <?php 
		if ($mode == "EDIT") {
			echo "[Section 2]";
		}
		$DynamicForm->display_form_section($mode, 2, $admin);
	?></td>

	<?php } ?>
  </tr>

  <tr>
    <td>
		<?php 
		if ($mode == "EDIT") {
			echo "[Section 3]";
		}
		// display section 3
		$DynamicForm->display_form_section($mode, 3, $admin);
		?></td>
  </tr>
  <?php 
  // save button (if in edit mode)
	if ($mode=='edit') { ?>
 		<tr><td colspan="2" bgcolor="#ffffff">
		<input type="hidden" name="save" id="save101" value="">
		<input class="form_submit_button" TYPE="SUBMIT"  name="savebutton" value="<?php echo $label['resume_save_button']; ?>" onClick="save101.value='1';">
		</td></tr>
			
	<?php } ?>
		
	</table>

	<?php if ($mode != 'view') { ?>
	</form>
	<?php } ?>
	