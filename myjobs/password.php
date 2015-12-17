<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require("../config.php");

include('login_functions.php'); 

JB_process_login();

$password = (isset($_REQUEST['password'])) ? $_REQUEST['password'] : '';
$newpass  = (isset($_REQUEST['newpass'])) ? $_REQUEST['newpass'] : ''; 
$newpass2 = (isset($_REQUEST['newpass2'])) ? $_REQUEST['newpass2'] : ''; 
$submit   = (isset($_REQUEST['submit'])) ? $_REQUEST['submit'] : '';

JB_template_candidates_header();


JB_render_box_top(80, $label['c_menu_pass_header']);

if ($submit != '' ) {

	$mdpass = md5(stripslashes($password));
	$sql = "SELECT * FROM `users` WHERE `Username`='".jb_escape_sql($_SESSION['JB_Username'])."'";

	$result = JB_mysql_query ($sql);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	if ($mdpass != $row['Password']) {
		$error .= $label["c_menu_pass_incorrect"]."<br>";
	} elseif ($password == '') {
		$error .= $label["c_menu_pass_oldblank"]."<br>";
	}

	JBPLUG_do_callback('val_can_old_pass', $error, $password, $_SESSION['JB_Username']);

	if ($newpass == '') {
		$error .= $label["c_menu_pass_new_blank"]."<br>";
	} elseif (strlen(trim($newpass)) < 6) {
		$error .= $label['c_signup_error_pw_too_weak']; 
	}

	if ($newpass2 == '') {
		$error .= $label["c_menu_pass_new_blank2"]."<br>";
	}


	if ($newpass != $newpass2) {
		$error .= $label["c_menu_pass_notmatch"]."<br>";
	}

	if ($error != '' ) {
		$JBMarkup->error_msg($label["c_pass_change_error"]);
		echo "<b>".$error."</b>";


	} else {

		$newmdpass = md5 (stripslashes($newpass));
		JB_mysql_query ("UPDATE `users` SET `Password`='$newmdpass' WHERE `Username`='".jb_escape_sql($_SESSION['JB_Username'])."' LIMIT 1 ") or die (mysql_error());

		$success = 1;

		JBPLUG_do_callback('can_new_pass', $newpass, $_SESSION['JB_Username']);


	}

}

if ($success == 1) {
   $JBMarkup->ok_msg($label["c_menu_pass_ok"]);
   $password='';
   $newpass='';
   $newpass2='';

}


?>


<?php echo $label["c_menu_pass_intro"];?>
<p>
<?php echo $label["c_menu_pass_intro2"];?>
<p>
<form method="post" action="password.php">

<table border="0" cellSpacing="1" cellPadding="3" class="dynamic_form" id='dynamic_form'>
<tr><td class="dynamic_form_field"><?php echo $label["c_menu_pass_oldpass"];?></td>
<td class="dynamic_form_value"><input type="password" name="password" value="<?php echo JB_escape_html($password);?>"></td>
</tr>
<tr><td colspan="2" class="dynamic_form_value">&nbsp</td></tr>
<tr><td class="dynamic_form_field"><?php echo $label["c_menu_pass_newpass"];?></td>
<td class="dynamic_form_value"><input type="password" name="newpass" value="<?php echo JB_escape_html($newpass);?>"></td>
</tr>
<tr><td class="dynamic_form_field"><?php echo $label["c_menu_pass_newpass2"];?></td>
<td class="dynamic_form_value"><input type="password" name="newpass2" value="<?php echo JB_escape_html($newpass2);?>"></td>
</tr>
<tr><td colspan="2" class="dynamic_form_value"><input class="form_submit_button" type="submit" value="<?php echo $label["c_menu_pass_submit"]; ?>" name="submit"></td></tr>
</table>

</form>

<?php 

JB_render_box_bottom();
JB_template_candidates_footer();

?>