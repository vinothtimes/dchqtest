<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";
require (dirname(__FILE__)."/admin_common.php");



require_once('../include/resumes.inc.php'); 


$JBMarkup->enable_overlib();

$action = $_REQUEST['action'];
$q_name = $_REQUEST['q_name'];
$q_nat = $_REQUEST['q_nat'];
$q_type = $_REQUEST['q_type'];
$q_edu = $_REQUEST['q_edu'];
$q_aday = $_REQUEST['q_aday'];
$q_amon = $_REQUEST['q_amon'];
$q_ayear = $_REQUEST['q_ayear'];
$order_by = $_REQUEST['order_by'];
$ord = $_REQUEST['ord'];
$offset = (int) $_REQUEST['offset'];
$app_id = (int) $_REQUEST['app_id'];
$resume_id = (int) $_REQUEST['resume_id'];

if ($_REQUEST['show'] != '') {
	$show = $_REQUEST['show'];
	$_SESSION['resume_show'] = $show;
}



if ($_REQUEST['show'] =='') {
	$_REQUEST['show'] = $_SESSION['resume_show'];
}

$show = $_REQUEST['show'];

if ($show=='') $show='ALL';

$ResumeForm = &JB_get_DynamicFormObject(2);
$ResumeForm->set_mode('edit');
JB_admin_header('Admin -> Resumes');

?>

<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000; "></div>
<b>[CANDIDATES - RESUMES]</b> <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="candidates.php">List Candidates</a></span>
<span style="background-color: <?php if ($_REQUEST['show']=='ALL') echo "#FFFFCC"; else echo "#F2F2F2"; ?>; border-style:outset; padding: 5px;"><a href="resumes.php?show=ALL">View Resumes</a></span>
<?php
if (JB_RESUMES_NEED_APPROVAL=='NO') {
	?>
	<span style="background-color: <?php if ($_REQUEST['show']=='WA') echo "#FFFFCC"; else echo "#F2F2F2"; ?>; border-style:outset; padding: 5px;"><a href="resumes.php?show=WA">New Resumes Waiting</a></span>
	<?php
	
}
?>
	<hr>
<?php



// Display Category tree code
// do we have a CATEGORY type field? (field_type)

foreach ($ResumeForm->get_tag_to_field_id() as $field) {


	// If it does have a CATEGORY, display the category tree and
	// break out from the loop
	if ($field['field_type']=='CATEGORY') {
				?>
			<div style="padding-bottom:3.5em; text-align:left">
				<div style="float:left; ">
					<span class="category_name"> <?php echo jb_escape_html(JB_getCatName($_REQUEST['cat']));?></span><br>
					<span class="category_path"><?php echo JB_getPath_templated($_REQUEST['cat']);?></span>
				</div>
			<?php
			if (is_numeric($_REQUEST['cat'])) {
			
				?>
				<div style="float: right;">
					<a href="index.php"><a href="search.php"><?php echo $label['c_back2top'];?></a></a>
				</div>
				<?php
			}
			?>
			</div>

		<?php
		$categories = JB_getCatStruct($_REQUEST['cat'], $_SESSION["LANG"], 2);

		JB_display_categories($categories, JB_CAT_COLS);
		break; 
	}

}





if ($_REQUEST['action'] == 'grant') {

	// get user_id for resume
	$sql = "SELECT user_id from resumes_table WHERE resume_id='".jb_escape_sql($_REQUEST['resume_id'])."' ";
	$result = JB_mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$user_id = $row['user_id'];

	$sql = "UPDATE `requests` SET `request_status`='GRANTED' WHERE `employer_id`='".jb_escape_sql($_REQUEST['employer_id'])."' AND candidate_id='".jb_escape_sql($user_id)."' ";
	
	JB_mysql_query($sql) or die(mysql_error());

	JB_send_request_granted_email($user_id, $_REQUEST['employer_id']);

	$JBMarkup->ok_msg('Resume granted.');

}

if ($_REQUEST['action'] == 'refuse') {

	// get user_id for resume
	$sql = "SELECT user_id from resumes_table WHERE resume_id='".jb_escape_sql($_REQUEST['resume_id'])."' ";
	$result = JB_mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$user_id = $row['user_id'];


	$sql = "UPDATE `requests` SET `request_status`='REFUSED' WHERE `employer_id`='".jb_escape_sql($_REQUEST['employer_id'])."' AND candidate_id='".jb_escape_sql($user_id)."' ";
	JB_mysql_query($sql) or die(mysql_error());

	$JBMarkup->ok_msg('Resume refused.');

}

if ($_REQUEST['action'] == 'suspend') {
	$sql = "UPDATE `resumes_table` SET `status`='SUS' WHERE `resume_id`='".jb_escape_sql($_REQUEST['resume_id'])."' ";
	JB_mysql_query($sql) or die(mysql_error());

	// delete the resume from saved resumes
	$sql = "DELETE FROM `saved_resumes` WHERE `resume_id`='".jb_escape_sql($_REQUEST['resume_id'])."' ";
	JB_mysql_query($sql) or die(mysql_error());
}

if ($_REQUEST['action'] == 'activate') {
	$sql = "UPDATE `resumes_table` SET `status`='ACT' WHERE `resume_id`='".jb_escape_sql($_REQUEST['resume_id'])."' ";
	JB_mysql_query($sql) or die(mysql_error());

	$JBMarkup->ok_msg('Resume activated.');
}
if ($_REQUEST['action'] == 'approve') {
	$sql = "UPDATE `resumes_table` SET `approved`='Y' WHERE `resume_id`='".jb_escape_sql($_REQUEST['resume_id'])."' ";
	JB_mysql_query($sql) or die(mysql_error());
	$_REQUEST['resume_id']='';
	$resume_id='';

	$JBMarkup->ok_msg('Resume approved.');
}
if ($_REQUEST['action'] == 'delete') {

	JB_delete_resume ($_REQUEST['resume_id']);
	$resume_id = '';
	$JBMarkup->ok_msg('Resume Deleted.');

}

if ($action == 'search') {
	$q_string = JB_generate_q_string(2); 

} 



if (isset($_REQUEST['disapprove']) && $_REQUEST['disapprove']) {

	if (is_array($_REQUEST['resumes'])) {
		foreach ($_REQUEST['resumes'] as $rid) {
			$sql = "UPDATE `resumes_table` set `approved`='N' WHERE resume_id='".jb_escape_sql($rid)."' LIMIT 1 ";
			jb_mysql_query($sql);
		}
		JB_update_resume_count();
		$JBMarkup->ok_msg('Resume(s) disapproved.');
	}
}

if (isset($_REQUEST['approve']) && $_REQUEST['approve']) {

	if (is_array($_REQUEST['resumes'])) {
		foreach ($_REQUEST['resumes'] as $rid) {
			$sql = "UPDATE `resumes_table` set `approved`='Y' WHERE resume_id='".jb_escape_sql($rid)."' LIMIT 1 ";
			jb_mysql_query($sql);
		}
		JB_update_resume_count();
		$JBMarkup->ok_msg('Resume(s) approved.');
	}
}

if (isset($_REQUEST['delete']) && $_REQUEST['delete']) {

	if (is_array($_REQUEST['resumes'])) {
		foreach ($_REQUEST['resumes'] as $rid) {
			$sql = "DELETE FROM `resumes_table` WHERE resume_id='".jb_escape_sql($rid)."' LIMIT 1 ";
			jb_mysql_query($sql);
		}
		JB_update_resume_count();
		$JBMarkup->ok_msg('Resume(s) deleted.');
	}
}


if ($resume_id != '') {
	
	require_once ("../include/profiles.inc.php");

	if ($_REQUEST['save'] != "" ) { // saving
	
		$errors = $ResumeForm->validate(true);
		if ($errors) {

			JB_resume_admin_controls($_REQUEST);
			$ResumeForm->display_form('edit', true);
			
			$mode = 'view';
			JB_resume_admin_controls($_REQUEST);
			$ResumeForm->display_form('view', true);
		} else {

			$ResumeForm->save(true);
			$JBMarkup->ok_msg('Resume Saved.');

		}
		
	} else {

		$data = $ResumeForm->load($_REQUEST['resume_id']);

		if ($_REQUEST['action'] == 'edit') {
			$mode = "edit";

		} else {
				$mode = "view";
				if (JB_RESUME_REQUEST_SWITCH!='NO') {
				?>
				<p style="text-align:center"><?php
				JB_display_request_history ($data['user_id']);

				?>
				</p>
				<?php
			} 
			
			if ($data['status'] == 'SUS') {
				echo "This resume is suspended and cannot be viewed by employers";

			}
			

		}
		JB_resume_admin_controls($data);

		$ResumeForm->display_form($mode, true);

		

	}

} else {

	JB_display_dynamic_search_form (2);
       
	   ?>
		<div style="float: right;"><font size="2"><a href="get_csv.php?table=resumes_table&amp;form_id=2">Download CSV</a></font> | <font size="2"><a href="resumelist.php">Edit List</a></font></div>
		<div style="clear:all"></div>
	   <?php
	   
	   JB_list_resumes ('ADMIN', $show);
      

 }

JB_admin_footer();

function JB_resume_admin_controls(&$data) {

	?>
	<p><center>
			<input type="button" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=edit&amp;resume_id=<?php echo $_REQUEST['resume_id']; ?>'" value="Edit Resume">  
			<?php if ($data['anon'] == 'Y') { 
			$reveal_window = "onclick=\"window.open('reveal_resume.php?resume_id=".$_REQUEST['resume_id']."', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=750,height=500,left = 50,top = 50');return false;\"";	  
			?>
			<input type="button" <?php echo $reveal_window; ?> value="Reveal to Employer...">
			<?php } ?>
			<?php if ($data['status'] != 'SUS') { ?>
			<input type="button" onClick="if (!confirmLink(this, 'Suspend this resume, are you sure?')) return false; window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=suspend&resume_id=<?php echo $_REQUEST['resume_id']; ?>'" value="Suspend Resume"><?php } else { ?> 
			<input type="button" onClick="if (!confirmLink(this, 'Activate this resume, are you sure?')) return false; window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=activate&resume_id=<?php echo $_REQUEST['resume_id']; ?>'" value="Activate Resume"><?php } ?> 
			<input type="button" onClick="if (!confirmLink(this, 'Delete this resume, are you sure?')) return false; window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=delete&amp;resume_id=<?php echo $_REQUEST['resume_id']; ?>'" value="Delete Resume"></center></p>

<?php

}
?>
