<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require ('../config.php');
require (dirname(__FILE__)."/admin_common.php");
require_once ('../include/category.inc.php');
require_once ('../include/profiles.inc.php');
require_once ('../include/dynamic_forms.php');


$mode = $_REQUEST['mode'];

JB_admin_header('Admin -> Profile List');

?>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000; "></div>
<b>[Profile List]</b><span style="background-color: <?php if (($_REQUEST['mode']!='EDIT')) { echo "#F2F2F2"; }  ?>; border-style:outset; padding: 5px;"><a href="profileform.php?mode=VIEW">View Form</a></span> <span style="background-color:  <?php if (($_REQUEST['mode']=='EDIT') && ($_REQUEST['NEW_FIELD']=='')) { echo "#FFFFCC"; }  ?>; border-style:outset; padding: 5px;"><a href="profileform.php?mode=EDIT">Edit Fields</a></span> <span style="background-color: <?php if (($_REQUEST['mode']=='EDIT') && ($_REQUEST['NEW_FIELD']!='')) { echo "#FFFFCC"; }  else { echo "#F2F2F2";}?> ; border-style:outset; padding: 5px;"><a href="profileform.php?NEW_FIELD=YES&mode=EDIT">New Field</a></span> &nbsp; &nbsp; <span style="background-color: <?php  echo "#FFFFCC";?> ; border-style:outset; padding: 5px;"><a href="profilelist.php">Profile List</a></span>
	
	<hr>

<?php




if ($_REQUEST['action']=='del') {

	$sql = "DELETE FROM form_lists WHERE column_id='".jb_escape_sql($_REQUEST['column_id'])."' ";
	$result = JB_mysql_query ($sql);
	JB_cache_del_keys_for_form(3);

}

if ($_REQUEST['column_id']!='') {
	$sql = "SELECT * FROM form_lists WHERE column_id='".jb_escape_sql($_REQUEST['column_id'])."' ";
	$result = JB_mysql_query ($sql);
	$col_row = mysql_fetch_array($result, MYSQL_ASSOC);

}


if ($_REQUEST['save_col']!='') {

	if ($_REQUEST['field_id']=='') {
		$error = "Did not select a field ";
	}

	if (!is_numeric($_REQUEST['sort_order'])) {
		$error .= "'Sort order' must be a number. <br>";
	}

	if (!is_numeric($_REQUEST['truncate_length'])) {
		$error .= "'Truncate' must be a number. <br>";
	}



	if (is_numeric($_REQUEST['field_id'])) { // its a dynamic field, we can get the details from the database

		$sql = "SELECT * from form_fields WHERE form_id=3 AND field_id='".jb_escape_sql($_REQUEST['field_id'])."'  ";
		$result = JB_mysql_query ($sql);
		$field_row = mysql_fetch_array($result, MYSQL_ASSOC);

		if (($field_row['field_type']!='TEXT') && ($field_row['field_type']!='TEXTAREA') && ($field_row['field_type']!='EDITOR')) {
			if ($_REQUEST['truncate_length']>0) {
				$error .= "Only text fields, text editor fields and HTML editor fields can be truncated - this field is a ".$field_row['field_type']."<br>";
			}
		}

	} else { // its a static field, details are in include/schema_functions.php

		if ($_REQUEST['truncate_length']>0) {
			$error .= "Only text fields, text editor fields and HTML editor fields can be truncated - this field is a ".$field_row['field_type']."<br>";
		}

		$field_row['field_type']='TEXT'; // default storage type.
		$field_row['field_id'] = $_REQUEST['field_id'];

		// set the template tag for the field
		$fields = JB_schema_get_static_fields(2);
		$field_row['template_tag'] = $fields[$_REQUEST['field_id']]['template_tag'];
		if ($fields[$_REQUEST['field_id']]['field_type']) {
			$field_row['field_type'] = $fields[$_REQUEST['field_id']]['field_type'];
		} else {
			$field_row['field_type']='TEXT'; // default storage type.
		}

	}

	if ($field_row['template_tag']=='') { // need to fix the template tag!

		$field_row['template_tag'] = JB_generate_template_tag(3);

		// update form field

		$sql = "UPDATE form_fields SET `template_tag`='".jb_escape_sql($field_row['template_tag'])."' WHERE form_id=3 AND field_id='".jb_escape_sql($_REQUEST['field_id'])."'";
		JB_mysql_query ($sql);

	}

	if ($_REQUEST['admin_only']=='') {
		$_REQUEST['admin_only']='N';
	}

	if ($_REQUEST['linked']=='') {
		$_REQUEST['linked']='N';
	}


	if ($_REQUEST['column_id']!='') {
		$col_str = '`column_id`,';
		$col_cal = '\''.jb_escape_sql($_REQUEST['column_id']).'\',';
	}

	$sql = "REPLACE INTO form_lists ($col_str `template_tag`, `field_id`, `sort_order`, `field_type`, `form_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `no_wrap`, `is_sortable`) VALUES ($col_cal '".jb_escape_sql($field_row['template_tag'])."', '".jb_escape_sql($field_row['field_id'])."', '".jb_escape_sql($_REQUEST['sort_order'])."', '".jb_escape_sql($field_row['field_type'])."', '3', '".jb_escape_sql($_REQUEST['admin_only'])."', '".jb_escape_sql($_REQUEST['truncate_length'])."', '".jb_escape_sql($_REQUEST['linked'])."',  '".jb_escape_sql($_REQUEST['clean_format'])."', '".jb_escape_sql($_REQUEST['is_bold'])."', '".jb_escape_sql($_REQUEST['no_wrap'])."', '".jb_escape_sql($_REQUEST['is_sortable'])."')";

	
	if ($error=='') {
		$result = JB_mysql_query ($sql) or die (mysql_error().$sql);
		
		JB_cache_del_keys_for_form(3);
		
		$JBMarkup->ok_msg('Column Updated.'); 
	} else {
		$JBMarkup->error_msg("Cannot save due to the following errors:");
		echo $error;

	}

	// load new values

	$sql = "SELECT * FROM form_lists WHERE column_id='".jb_escape_sql($_REQUEST['column_id'])."' ";
	$result = JB_mysql_query ($sql);
	$col_row = mysql_fetch_array($result, MYSQL_ASSOC);

}

?>
<?php
if ($col_row['column_id']!='') {

echo '<a href="profilelist.php">+ Add new column</a>';

}

?>
	<form method="POST" action="profilelist.php">

	<input type="hidden" name="form_id" value="3">
	<input type="hidden" name="column_id" value="<?php echo $col_row['column_id'];?>">
	<table border=1>
	<tr>
	<td colspan="2">
	<?php
if ($col_row['column_id']=='') {
	?>
<b>Add a new column to the list</b>
<?php
	} else {
?>
<b>Edit column</b>

<?php

	}

?>
	</td>
	</tr>
	<tr>
		<td>Column</td>
		<td><select name="field_id" size=4>

		
		
		<?php
		
		JB_field_select_option_list (3, $col_row['field_id']);
		
		?>
			</select></td>
	</tr>

	<?php

	if ($_REQUEST['column_id']=='') { // get the last sort order

		$sql = "SELECT max(sort_order) FROM form_lists WHERE field_id=3 GROUP BY column_id ";
		$result = JB_mysql_query ($sql) or die (mysql_error().$sql);
		$row = mysql_fetch_row($result);
		$sort_order = $row[0];

	}


	?>

	<tr>
		<td>Order</td>
		<td><input type="text" name="sort_order" size="3" value="<?php echo $col_row['sort_order'];?>" >(1=first, 2=2nd, etc.)</td>
	</tr>
	<tr>
		<td>Linked?</td>
		<td> <input <?php if ($col_row['linked']!='Y') echo ' checked '; ?> type="radio" name="linked" value='N'>No  / <input <?php if ($col_row['linked']=='Y') echo ' checked '; ?> type="radio" name="linked" value='Y'> Yes - link to view full record

	</tr>
	<tr>
		<td>Admin Only?</td>
		<td> <input <?php if ($col_row['admin']!='Y') echo ' checked '; ?> type="radio" name="admin_only" value='N'>No  / <input <?php if ($col_row['admin']=='Y') echo ' checked '; ?> type="radio" name="admin_only" value='Y'> Yes

	</tr>
	<tr>
		<td>Clean format?</td>
		<td> <input <?php if ($col_row['clean_format']!='Y') echo ' checked '; ?> type="radio" name="clean_format" value='N'>No  / <input <?php if ($col_row['clean_format']=='Y') echo ' checked '; ?> type="radio" name="clean_format" value='Y'>  Yes - Clean punctuation. Eg. if someone writes A,B,C the system will change to A, B, C

	</tr>
	<tr>
		<td>Is sortable?</td>
		<td> <input <?php if ($col_row['is_sortable']!='Y') echo ' checked '; ?> type="radio" name="is_sortable" value='N'>No  / <input <?php if ($col_row['is_sortable']=='Y') echo ' checked '; ?> type="radio" name="is_sortable" value='Y'> Yes - users can sort the records by this coulum, when clicked.

	</tr>
	<tr>
		<td>Is in Bold?</td>
		<td> <input <?php if ($col_row['is_bold']!='Y') echo ' checked '; ?> type="radio" name="is_bold" value='N'>No  / <input <?php if ($col_row['is_bold']=='Y') echo ' checked '; ?> type="radio" name="is_bold" value='Y'> Yes

	</tr>
	<tr>
		<td>No Wrap?</td>
		<td> <input <?php if ($col_row['no_wrap']!='Y') echo ' checked '; ?> type="radio" name="no_wrap" value='N'>No  / <input <?php if ($col_row['no_wrap']=='Y') echo ' checked '; ?> type="radio" name="no_wrap" value='Y'> Yes

	</tr>
	<tr>
		<td>Truncate (cut) to:</td>
		<td> <input type="text" name="truncate_length" size="2" value='<?php if ($col_row['truncate_length']=='') {$col_row['truncate_length']='0';} echo $col_row['truncate_length'];?>' size=''> characters. (0 = do not truncate)

	</tr>
	<tr>
	<td colspan="2"><input type="submit" name="save_col" value="Save"> </td>
	</tr>


	</table>


	</form>

<hr>
Here are the columns that will appear on the profile list:
<table  id='resumelist' cellspacing="1" cellpadding="5" style="margin: 0 auto; background-color: #d9d9d9; width:99%; border:0px" >
	<?php

	JB_echo_list_head_data_admin(3);

	?>
</table>

<?php

JB_admin_footer();

?>