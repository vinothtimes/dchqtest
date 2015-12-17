<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";
require_once ('../include/employers.inc.php');


$_SESSION['JB_ID']=''; // log the user off, if logged in
$_SESSION['JB_Domain'] = '';
$_REQUEST['user_id']='';
$label["employer_signup_heading1"] = str_replace ("%SITE_NAME%", JB_SITE_NAME , $label["employer_signup_heading1"]);

$EmployerForm = &JB_get_DynamicFormObject(4);
$EmployerForm->set_mode('edit');

$page_title = $label["employer_signup_heading1"]." - ". JB_SITE_NAME;
JB_template_employers_outside_header($page_title);

include('login_functions.php'); 

JB_render_box_top(75,  $label["employer_signup_infobox"]);
?>
<h3 style="text-align:center;"><?php echo $label["employer_signup_heading1"]; ?></h3>
<?php
if ($_REQUEST['form'] != "" ) { // saving


	$errors = $EmployerForm->validate();	
	if ($errors) { // we have some signup error(s)
		$mode = "edit";
		$JBMarkup->error_msg($label["employer_signup_error"]);
		echo '<span style="font-weight:bold">'.$EmployerForm->get_error_msg().'</span>';
		$EmployerForm->display_form('edit', false);
	} else {

		$employer_id = $EmployerForm->save(); // create a new account	

		if ($employer_id) {

			$employer_signup_success = str_replace ( "%FirstName%", stripslashes($_REQUEST['FirstName']), $label['employer_signup_success']);
			$employer_signup_success = str_replace ( "%LastName%", stripslashes($_REQUEST['LastName']), $employer_signup_success);
			$employer_signup_success = str_replace ( "%SITE_NAME%", JB_SITE_NAME, $employer_signup_success);
			$employer_signup_success = str_replace ( "%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $employer_signup_success);
	
			echo $employer_signup_success;

			echo '<p align="center" width="50%" >';
			if ((JB_EM_NEEDS_ACTIVATION == "AUTO")||(JB_EM_NEEDS_ACTIVATION == "NO_RESUME")||(JB_EM_NEEDS_ACTIVATION == "FIRST_POST"))  {

				echo '<p style="text-align: center;"><form method="post" action="' . JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER . 'login.php"><input type="hidden" name="username" value="'.$_REQUEST['Username'].'" > <input type="hidden" name="password" value="'.$_REQUEST['Password'].'"><input type="submit" value="'.$label["employer_signup_continue"].'"></form></p>';
			} else {

				echo '<p style="text-align: center;">'.$label['employer_signup_goback'].'</p>';	 
			}

			echo '</p>';

		} 
	}
} else {
	$EmployerForm->display_form('edit', false);
}
	JB_render_box_bottom();	
	?>
	<p style="text-align: center;">
	<a href="<?php echo JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER; ?>"><?php ECHO $label["employer_link_to_jobseeker"]; ?></a>
	</p>
	<?php

JB_template_employers_outside_footer();
?>

