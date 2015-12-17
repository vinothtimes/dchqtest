<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
 require "../config.php";
 include('login_functions.php');
 JB_process_login();
 JB_template_employers_header(); 

JB_render_box_top(80,  $label['employer_pass_title']);

$password = (isset($_REQUEST['password'])) ? $_REQUEST['password'] : '';
$newpass  = (isset($_REQUEST['newpass'])) ? $_REQUEST['newpass'] : ''; 
$newpass2 = (isset($_REQUEST['newpass2'])) ? $_REQUEST['newpass2'] : ''; 
$submit   = (isset($_REQUEST['submit'])) ? $_REQUEST['submit'] : '';

if ($_REQUEST['submit'] != '' ) {

   $mdpass = md5(stripslashes($password));

	$sql = "SELECT * FROM `employers` WHERE `Username`='".jb_escape_sql($_SESSION['JB_Username'])."'";
   $result  = JB_mysql_query ($sql) or die(mysql_error());
   $row = mysql_fetch_array($result, MYSQL_ASSOC);

  
   if ($password == '') {
      $error .= $label["employer_pass_error_old_pass_blank"]."<br>"; //"Your Old Password is blank!<br>";
   } elseif
	  ($mdpass != $row['Password']) {
      $error .= $label["employer_pass_error_old_pass_incorrect"]."<br>"; //"Your Old Password is incorrect!<br>";
   }
   JBPLUG_do_callback('val_emp_old_pass', $error, $password, $_SESSION['JB_Username']);


   if ($newpass == '') {
      $error .= $label["employer_pass_error_new_pass_blank"]."<br>";//"Your New Password is blank!<br>";
   } elseif (strlen(trim($newpass)) < 6) {
		$error .= $label['employer_signup_error_pw_too_weak']; 
	}

   if ($newpass2 == '') {
      $error .= $label["employer_pass_error_new_conf_pass_blank"]."<br>";//"Your Confirmed New Password is blank!<br>";
   }


   if ($newpass != stripslashes($newpass2)) {
      $error .= $label["employer_pass_change_pass_not_match"]."<br>";//"Your New Password doesn't match with the Confirmed New Password!<br>";
   }

   if ($error != '' ) {
	   $JBMarkup->error_msg($label["employer_pass_error"]);
	   echo "<br><b>".$error."</b>";
		echo "<br>";
	} else {

	   $newmdpass = md5 ($newpass);
	   JB_mysql_query ("UPDATE `employers` SET `Password`='$newmdpass' WHERE `Username`='".$_SESSION['JB_Username']."' LIMIT 1 ") or die (mysql_error());

	   $success = 1;

	   JBPLUG_do_callback('emp_new_pass', $newpass, $_SESSION['JB_Username']);


	}

}

if ($success == 1) {
   $JBMarkup->ok_msg($label["employer_pass_change_success"]);
}

else {


	echo '<div class="explanation_note" >'.$label["employer_pass_note"]."</div>";
}
?>


<p>
<form method="post" action="password.php">

<table border="0" cellSpacing="1" cellPadding="3" class="dynamic_form" id='dynamic_form'>
<tr><td class="dynamic_form_field"><?php echo $label["employer_pass_old_pass_label"];?></td>
<td class="dynamic_form_value"><input type="password" name="password" value="<?php echo JB_escape_html($password);?>"></td>
</tr>
<tr><td colspan="2" class="dynamic_form_value">&nbsp</td></tr>
<tr><td class="dynamic_form_field"><?php echo $label["employer_pass_new_pass_label"]; ?></td>
<td class="dynamic_form_value"><input type="password" name="newpass" value="<?php echo JB_escape_html($newpass);?>"></td>
</tr>
<tr><td class="dynamic_form_field"><?php echo $label["employer_pass_new_pass_confirm_label"]; ?></td>
<td class="dynamic_form_value"><input type="password" name="newpass2" value="<?php echo JB_escape_html($newpass2);?>"></td>
</tr>
<tr><td colspan="2" class="dynamic_form_value"><input class="form_submit_button" type="submit" value="<?php echo $label["employer_pass_button_label"]; ?>" name="submit"></td></tr>
</table>

</form>

<?php 
JB_render_box_bottom();

?>


<?php





?>

<?php JB_template_employers_footer(); ?>