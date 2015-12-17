<?php
/*

Used by an employer to request candidate to unblock their anymous fields 
(email_iframe.php)

$from, $reply_to

*/

?>
<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
	<input type="hidden" name="user_id" value="<?php echo jb_escape_html($_REQUEST['user_id']); ?>">
	<table align="center"class="email_form_table"  id='request_form_table' cellSpacing="1" cellPadding="3">
		<tr><td  class="field_label"><?php echo $label["employer_request_details_to"]; ?></td><td class="field_data"><b>#<?php echo jb_escape_html($_REQUEST['user_id']); ?></b></td></tr>
		<tr><td class="field_label"><?php echo $label["employer_request_details_from"];?></td><td class="field_data"><input style="width: 100%" size="40" type='text' name='from' value='<?php echo jb_escape_html($from); ?>'></td></tr>
		<tr><td class="field_label"><?php echo $label["employer_request_details_reply"]; ?></td><td class="field_data"><input style="width: 100%" size="40" type='text' name='reply_to' value='<?php echo jb_escape_html($reply_to); ?>'></td></tr>

		<tr><td class="field_data" colspan="2"><?php echo $label["employer_request_details_msg"];?></td></tr>
		<tr><td class="field_data" colspan="2"><textarea style="width: 100%" name="message" rows="10" cols="40"><?php echo jb_escape_html($_REQUEST['message']);?></textarea></td></tr>
		<tr><td class="field_data" colspan="2"><input class="form_submit_button" type="submit" name="submit" value="<?php echo $label["employer_request_send_button"]; ?>"></td></tr>
	</table>
</form>