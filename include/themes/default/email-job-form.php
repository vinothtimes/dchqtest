<form method="post" name="form1" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" Onsubmit= "form1.sendbutton.disabled=true;">
<input type="hidden" name="post_id" value="<?php echo jb_escape_html($post_id); ?>">
<input type="hidden" name="url"	value="<?php echo JB_job_post_url($post_id, $JobListAttributes, JB_BASE_HTTP_PATH.'index.php');?>" >

<table align="center" border="0" width="98%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin:3px"  >
  <tr>
    <td style="background-color:#808080; color: white; font-weight:bold">
    <?php echo $label['taf_heading']; ?></td>
  </tr>
  <tr>
    <td width="100%"><span style="font-weight:bold"><?php echo $label['taf_url'];?></span><br>
	
    <?php echo JB_BASE_HTTP_PATH; ?>index.php?post_id=<?php echo $post_id;?>
	</td>
</tr>
<tr><td>
	<span style="font-weight:bold"><?php echo $label['taf_input_email']; ?></span>
    <p><input type="text"  size="40" name="your_email" value="<?php echo $your_email; ?>"></p>
    
</td>
</tr>
<tr>
<td>
	<span style="font-weight:bold"><?php echo $label['taf_input_name'];?></span>
    <p><input type="text"  size="40" name="your_name" value="<?php echo $your_name; ?>"></p>
    
	</td>
</tr>
<tr>
	<td>
	<span style="font-weight:bold"><?php echo $label['taf_input_f_email'];?></span>
    <p><input type="text"  size="40" name="to_email" value="<?php echo $to_email; ?>"></p>
    
</td>
</tr>
<tr>
	<td>
	<span style="font-weight:bold"><?php echo $label['taf_input_subject'];?></span>
	<p>
    <input type="text"  size="40" value="<?php $label['taf_default_subject'] = str_replace('%SITE_NAME%', JB_SITE_NAME, $label['taf_default_subject']); echo $label['taf_default_subject']; ?>" name="subject"></p>
    
</td></tr><tr><td>
	<span style="font-weight:bold"><?php echo $label['taf_input_message']; ?></span>
    <p><textarea name="message" rows="5"  cols="40" ><?php echo $message; ?></textarea></p>
    <p>
	<input type="hidden" name="submit" id="submit101" value="">
	<input type="submit" class="form_submit_button" name="sendbutton" onClick="submit101.value='1';" value="<?php echo $label['taf_button_email']; ?>"> &nbsp;&nbsp;&nbsp;<input onclick="window.close(); return false" name="cancel" type="button" value="<?php echo $label['taf_button_cancel']; ?>">
	</p>
    </td>
  </tr>
</table>
</form>