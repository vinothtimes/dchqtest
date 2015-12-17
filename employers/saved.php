<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

if (isset($_REQUEST['resume_id']) && is_numeric($_REQUEST['resume_id'])) {
	
	// Refer the client to search.php to display the resume
	header("referer:".$_SERVER['PHP_SELF']); // this will tell the search.php that we came form the saved.php page 
	header('Location: search.php?resume_id='.$_REQUEST['resume_id']);

}


require('../config.php');
include('login_functions.php');

require_once ('../include/category.inc.php');
require_once('../include/resumes.inc.php');



JB_process_login(); 
JB_template_employers_header(); 


if (isset($_REQUEST['delete'])) {

	if (JBEmployer::delete_saved_resumes($_SESSION['JB_ID'], $_REQUEST['resumes'])) {
		echo $JBMarkup->ok_msg($label['emp_saved_deleted']);
	} else {
		echo $JBMarkup->error_msg($label['emp_saved_notselected']);
	}
}


?>

<h3><?php echo $label['emp_saved_heading'];?></h3>


<?php

JB_list_resumes ('SAVED');
JB_template_employers_footer();

?>