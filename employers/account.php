<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";
include('login_functions.php');
require_once ('../include/employers.inc.php');
JB_process_login();

$EmployerForm = &JB_get_DynamicFormObject(4);
$EmployerForm->set_mode('edit');
JB_template_employers_header(); 


if ($_REQUEST['load_defaults']==true) {
	// 'login intergration' plugins redirect to this page if the name is blank
	$_REQUEST['user_id'] = $_SESSION['JB_ID'];
	$data = $EmployerForm->load();
	$_REQUEST = array_merge($_REQUEST, $data);
}

JB_render_box_top('80', $label["employer_ac_intro"]);

if ($_REQUEST['form'] != "" ) { // saving

	$admin= false;

	$errors = $EmployerForm->validate();

	if (($_REQUEST['user_id'] != $_SESSION['JB_ID']) || ($errors)) {

		$JBMarkup->error_msg($label["employer_save_error"]);
		echo "<p>";
		echo $EmployerForm->get_error_msg();
		echo "</p>";
		$EmployerForm->display_form('edit', false);

	} else {
	
		$employer_id = $EmployerForm->save();
	
		$EmployerForm->load($_SESSION['JB_ID']);
		
		$JBMarkup->ok_msg($label["employer_ac_updated"]);
		$EmployerForm->display_form('edit', false);
	}


} else {
	$mode = "edit";
	$EmployerForm->load($_SESSION['JB_ID']);
	$EmployerForm->display_form('edit', false);
}



JB_render_box_bottom();
JB_template_employers_footer(); 

?>