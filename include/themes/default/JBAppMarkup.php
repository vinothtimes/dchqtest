<?php


class JBAppMarkup extends JBMarkup {

	function JBAppMarkup() {


	}




	###############################
	# apply_iframe.php
	###############################

	

	// already applied to this post

	function already_applied_msg() {
		global $label;
		echo $label["app_already_applied"]."<br>";

	}

	function get_error_line($error_label) {
		
		return $error_label.'<br>';
	}

	// error with application
	function error_msg($msg) {

		global $label;
		parent::error_msg($label['app_error']);
		echo $msg;

	}

	// application was sent
	function app_ok_msg($msg) {
		parent::ok_msg($msg);
	}

	

	// links shown after submitting an application
	// eg. Go to your account / Edit your resume / View your application history
	function links() {
		global $label;
		?><p style="text-align:center"><?php echo $label['app_account_links']; ?></p>
		<?php


	}

	function get_admin_receipt_email_subject($app_name) {
		global $label;
		return $label['app_receipt_subject']." ($app_name)";

	}

	function get_receipt_email_subject($TITLE, $DATE) {
		global $label;
		return $label['app_receipt_subject']." ($TITLE, $DATE)";

	}

	function success_start() {

		global $label;

		?>
		<?php echo $label['app_confirm_title']; ?>
		<table border="0" width="80%">
		<?php


	}


	function success_row($field, $value) {

		?>
		<tr>
			<td><b><?php echo $field; ?></b></td>
			<td><?php echo str_replace("\n", '<br>', jb_escape_html($value)); ?></td>
		</tr>
		<?php

	}


	function success_end() {
		?>
		</table>
		<?php

	}

	

	

}

?>