<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require("../config.php");

$_SESSION['JB_ID']=''; // log the user off, if logged in
$_SESSION['JB_Domain'] = '';
$_REQUEST['user_id']=''; 
require_once ('../include/candidates.inc.php');
$CandidateForm = &JB_get_DynamicFormObject(5);
$CandidateForm->set_mode('edit');
$page_title = $label['c_signup_title']." - ".JB_SITE_NAME;
JB_template_candidates_outside_header($page_title);

JB_render_box_top(75, $label["c_signup_intro_seeker"]);
$label["c_signup_header"] = str_replace ('%SITE_NAME%', jb_escape_html(JB_SITE_NAME), $label["c_signup_header"]);
?>

<h3 style="text-align:center"><?php echo $label["c_signup_header"]; ?></h3> 
<?php


if ($_REQUEST['form'] != "" ) { // saving
	
	$errors = $CandidateForm->validate();
	if ($errors) { // we have an error
		$mode = "edit";
		$JBMarkup->error_msg($label["c_signup_error9"]);
		echo '<span style="font-weight:bold">'.$CandidateForm->get_error_msg().'</span>';
		$CandidateForm->display_form('edit', false);
	} else {

		$admin = false;

		$user_id = $CandidateForm->save(); // create a new account

		if ($user_id) {

			echo '<div style="text-align:center" width="50%" >';

			if ((JB_CA_NEEDS_ACTIVATION == "AUTO"))  {
			  
				echo '<p style="text-align: center;"><form method="post" action="'. JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER.'login.php?page=edit.php"><input type="hidden" name="username" value="'.jb_escape_html($_REQUEST['Username']).'" > <input type="hidden" name="password" value="'.jb_escape_html($_REQUEST['Password']).'"><input type="submit" value="'.$label['c_signup_continue'].'"></form></p>';

			} else {
				echo '<p style="text-align: center;">'.$label['c_signup_goback'].'</p>';	 
			}

			$label['c_signup_ok'] = str_replace ("%FNAME%", stripslashes($_REQUEST['FirstName']), $label['c_signup_ok']);
			$label['c_signup_ok'] = str_replace ("%LNAME%", stripslashes($_REQUEST['LastName']), $label['c_signup_ok']);
			$label['c_signup_ok'] = str_replace ("%SITE_NAME%", jb_escape_html(JB_SITE_NAME), $label['c_signup_ok']);
			$label['c_signup_ok'] = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $label['c_signup_ok']);

			echo '<P>'.$label['c_signup_ok'].'</P>';
			echo '</div>';
			
		}

	}
} else {
		
	$CandidateForm->display_form('edit', false);
}
	JB_render_box_bottom();	
?>
<p style="text-align: center;">
<a target="_parent" href="<?php echo JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER;?>"><?php echo $label["c_flogin_advertiser"];?></a>
</p>
<?php

JB_template_candidates_outside_footer();

?>
