<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

require "../config.php";
require (dirname(__FILE__)."/admin_common.php");
require_once ("../include/code_functions.php");
require "../include/profiles.inc.php";

JB_admin_header('Admin -> Employer Profiles');
?>

<b>[EMPLOYER PROFILES]</b> <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="employers.php">List Employers</a></span>
	<span style="background-color: #F2F2F2; border-style:outset; padding: 5px;"><a href="employers.php?show=NA">Non-Validated Employers</a></span>
	<span style="background-color: #FFFFCC; border-style:outset; padding: 5px;"><a href="profiles.php">Employer Profiles</a></span>
	<hr>

<?php

JB_display_dynamic_search_form (3);

// Display Category tree code
// do we have a CATEGORY type field? (field_type)
$cat_exists=false;
foreach ($profile_tag_to_field_id as $field) {

	// If it does have a CATEGORY, display the category tree and
	// break out from the loop
	if ($field['field_type']=='CATEGORY') {
		$cat_exists = true;
		$categories = JB_getCatStruct($_REQUEST['cat'], $_SESSION["LANG"], 3);

		JB_display_categories($categories, JB_CAT_COLS);
		break; 
	}

}


?>
<div style="float: right;">
<font size="2"><a href="get_csv.php?table=profiles_table&amp;form_id=3">Download CSV</a></font>
</div>
<?php

if ($action == 'search') {
	$q_string = JB_generate_q_string(3); 
}

$admin=true;

$ProfileForm = &JB_get_DynamicFormObject(3);
$ProfileForm->set_mode('edit');

if ($_REQUEST['save'] != "" ) { // saving

	$errors = $ProfileForm->validate();
	if ($errors) { // we have an error
		$mode = "edit";
		$ProfileForm->display_form('edit', true);

	} else {
		$mode = "edit";
		$profile_id = $ProfileForm->save(true);
		$JBMarkup->ok_msg('Profile Saved');
		JB_list_profiles ($admin, $_REQUEST['order_by'], $_REQUEST['offset']);
	}
} elseif ($_REQUEST['action']=='edit') {
	echo "<a style='text-align:center;'><b><a href='".htmlentities(JB_get_go_back_link())."'>".$label["resume_display_go_back"]."</a></b><p>";
	$mode = 'edit';
	
	$ProfileForm->load($_REQUEST['profile_id']);
	$ProfileForm->display_form('edit', true);

} elseif ($_REQUEST['action']=='delete') {

	JB_delete_profile ($_REQUEST['profile_id']);
	$JBMarkup->ok_msg('Profile Deleted');


	JB_list_profiles ($admin, $_REQUEST['order_by'], $_REQUEST['offset']);
	

} elseif ($_REQUEST['profile_id']!='') {
	
	
	$ProfileForm->load($_REQUEST['profile_id']);
	$ProfileForm->display_form('view', true); //JB_display_profile_form (3, $mode, $data, true);

} else {

	
	
	JB_list_profiles ($admin, $_REQUEST['order_by'], $_REQUEST['offset']);
}

JB_admin_footer();

?>
