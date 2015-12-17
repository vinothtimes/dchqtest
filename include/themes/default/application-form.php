<form method="POST" action="apply_iframe.php" enctype="multipart/form-data">
<input type="hidden" name="post_id" value="<?php echo $post_id; ?>">	

	<table class="app_form_table"  id='app_form_table' cellSpacing="1" cellPadding="3">

		<?php
		JBPLUG_do_callback('app_form_top', $post_id);	
		?>
	
	  <tr>
		<td class="field_label"><b>
		<?php echo $label['app_input_name']; ?></b></td>
		<td class="field_data">
		<input type="text" name="app_name" style="width:100%" value="<?php echo JB_escape_html($app_name); ?>"></td>
	  </tr>
		<tr>
		<td class="field_label"><b>
		<?php echo $label['app_input_email']; ?></b></td>
		<td class="field_data">
		<input type="text" name="app_email" style="width:100%" value="<?php echo JB_escape_html($app_email); ?>"></td>
	  </tr>
	  <tr>
		<td class="field_label"><b><?php echo $label['app_input_subject']; ?></b></td>
		<td class="field_data">
		<input type="text" name="app_subject" style="width:100%"  value="<?php echo JB_escape_html ($app_subject); ?>"></td>
	  </tr>
	  <tr>
		<td  colspan="2" class="field_data"><b><?php echo $label['app_input_letter']; ?></b></td>
	  </tr>
	  <tr>
		<td colspan="2" class="field_data" >
		<textarea rows="9" style="width: 100%" name="app_letter" cols="20"><?php echo JB_escape_html ($app_letter); ?></textarea></td>
	  </tr>
	  <tr>
		<td class="field_label"><b><?php echo $label['app_input_att1']; ?></b></td>
		<td class="field_data">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo JB_MAX_UPLOAD_BYTES; ?>">
		<input type="file" name="att1" size="20" value="<?php echo jb_escape_html($att1); ?>"><font size="2"> 
		<?php echo $label['app_input_optional']; ?></td>
	  </tr>
	  <tr>
		<td class="field_label"><b><?php echo $label['app_input_att2']; ?></b></td>
		<td class="field_data">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo JB_MAX_UPLOAD_BYTES; ?>">
		<input type="file" name="att2" size="20" value="<?php echo jb_escape_html($att2); ?>"><font size="2"> 
		<?php echo $label['app_input_optional']; ?></td>
	  </tr>
	  <tr>
		<td class="field_label"><b><?php echo $label['app_input_att3']; ?></b></td>
		<td class="field_data">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo JB_MAX_UPLOAD_BYTES; ?>">
		<input type="file" name="att3" size="20" value="<?php echo jb_escape_html($att3); ?>"><font size="2"> 
		<?php echo $label['app_input_optional']; ?></td>
	  </tr>
	  <tr>
		<td  colspan="2" class="field_data">
	<input type="submit" class="form_submit_button" value="<?php echo $label['app_send_button']; ?>" name="apply"></td>
	  </tr>
	  <?php
		JBPLUG_do_callback('app_form_bottom', $post_id);	
		?>
	</table>
</form>