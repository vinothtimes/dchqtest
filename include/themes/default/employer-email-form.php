<?php
/*

Email form used by employer to send email to a candidate
(email_iframe.php)

$post_id, $c_name, $c_email, $email_subject, $email_letter

*/

?>
<form method="POST" action="" enctype="multipart/form-data">
    <input type="hidden" name="resume_id" value="<?php echo jb_escape_html($resume_id); ?>">    
       <table class="email_form_table"  id='email_form_table' cellSpacing="1" cellPadding="3">
        
          <tr>
            <td valign="top" class="field_label">
            <?php echo $label['em_input_name']; ?></td>
            <td class="field_data">
            <input type="hidden" name="c_name" size="40" style="width: 100%" value="<?php echo JB_escape_html($c_name); ?>"><?php echo JB_escape_html($c_name); ?></td>
          </tr>
		   <tr>
            <td valign="top" class="field_label">
            <?php echo $label['em_input_email']; ?></td>
            <td class="field_data">
            <input type="hidden" name="c_email" size="40" style="width: 100%" value="<?php echo JB_escape_html($c_email); ?>"><?php echo JB_escape_html($c_email); ?></td>
          </tr>
          <tr>
            <td valign="top" class="field_label"><?php echo $label['em_input_subject']; ?></td>
            <td class="field_data">
            <input type="text" name="email_subject" size="40" style="width: 100%" value="<?php echo (JB_escape_html ($email_subject)); ?>"></td>
          </tr>
          <tr>
            <td colspan="2" class="field_data">
            <textarea rows="9" style="width: 100%" name="email_letter" cols="20"><?php echo (JB_escape_html ($email_letter)); ?></textarea></td>
          </tr>
		 
            <td colspan="2" class="field_data">
        <input type="submit" class="form_submit_button" value="<?php echo $label['em_send_button']; ?>" name="apply"></td>
          </tr>
        </table>
    </form>