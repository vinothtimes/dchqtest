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
require_once ('../include/posts.inc.php');

if (!isset($_REQUEST['mode'])) {
	$_REQUEST['mode'] = 'VIEW';
}
$mode = $_REQUEST['mode'];
$PostingForm = &JB_get_DynamicFormObject(1);
$PostingForm->set_mode($mode);
JB_admin_header('Admin -> Posting Form');

?>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000; "></div>
<b>[Posting Form]</b><span style="background-color: <?php if (($_REQUEST['mode']!='EDIT')) { echo "#FFFFCC"; }  ?>; border-style:outset; padding: 5px;"><a href="postform.php?mode=VIEW">View Form</a></span> <span style="background-color: <?php if (($_REQUEST['mode']=='EDIT') && ($_REQUEST['NEW_FIELD']=='')) { echo "#FFFFCC"; }  ?>; border-style:outset; padding: 5px;"><a href="postform.php?mode=EDIT">Edit Fields</a></span> <span style="background-color: <?php if (($_REQUEST['mode']=='EDIT') && ($_REQUEST['NEW_FIELD']!='')) { echo "#FFFFCC"; }  ?>; border-style:outset; padding: 5px;"><a href="postform.php?NEW_FIELD=YES&mode=EDIT">New Field</a></span> &nbsp; &nbsp; <span style="background-color: <?php  echo "#F2F2F2";?> ; border-style:outset; padding: 5px;"><a href="postlist.php">Job List</a></span> 
	
	<hr>


<?php

global $ACT_LANG_FILES;
	echo "Current Language: [".$_SESSION["LANG"]."] Select language:";

?>

<form name="lang_form">
<input type="hidden" name="field_id" value="<?php echo htmlentities($field_id); ?>">
<input type="hidden" name="mode" value="<?php echo htmlentities($mode); ?>">
<select name='lang' onChange="document.lang_form.submit()">
<?php
foreach ($ACT_LANG_FILES as $key => $val) {
	$sel = '';
	if ($key==$_SESSION["LANG"]) { $sel = " selected ";}
	echo "<option $sel value='".$key."'>".$AVAILABLE_LANGS [$key]."</option>";

}

?>

</select>
</form>

<?php



if ( ($_REQUEST['NEW_FIELD']=='YES')) {
	$NEW_FIELD= 'YES';
} else {
	$NEW_FIELD = 'NO';
}

if ($_REQUEST['save'] != '') {

	
	$error = JB_validate_field_form ();
	
	if ($error == '') {
		
		$id = JB_save_field ($error, $NEW_FIELD);
		$mode = 'EDIT';
		
		JB_cache_del_keys_for_form(1);
		JB_format_field_translation_table (1);
		
		
		$JBMarkup->ok_msg('Changes Saved.'); 
		$NEW_FIELD = "NO";
		$_REQUEST['field_id'] = $id;
		
	} else {
		$JBMarkup->error_msg('ERROR!');
		echo $error;
		
	}

}



if ($_REQUEST['delete'] != '') {

	echo "Deleting...";

	$sql = "SELECT * FROM form_fields WHERE form_id=1 and field_id='".jb_escape_sql($_REQUEST['field_id'])."'";
	$result = JB_mysql_query ($sql);

	$row = mysql_fetch_array($result, MYSQL_ASSOC) or die(mysql_error());

	if (JB_is_reserved_template_tag($row['template_tag'])) {

		$JBMarkup->error_msg("<b>Cannot Delete:</b> This field contains a reserved 'Template Tag' and is needed by the system. Click on the 'R' icon next to the field for more information. Instead of deleting, please rename this field / change the type / move up or down.");

	} else {

		JB_delete_field($_REQUEST['field_id']);
		JB_cache_del_keys_for_form(1);
		echo "OK!"; $_REQUEST['field_id']= "";
		

	}

}
if (JB_is_table_unsaved ("posts_table")) {

//echo "<br>Note: This form's database structure was not updated yet. <b>Please click 'Save Changes' button to update the database structure.</b>";

	require (dirname(__FILE__).'/build_posts_table.php');
	JB_cache_del_keys_for_form(1);

}

//$mode = "EDIT";
?>
<table >

<tr>
	<td valign="top">
	<?php
	JB_build_sort_fields (1, 1);
	JB_build_sort_fields (1, 2);
	JB_build_sort_fields (1, 3);

	if ($_REQUEST['action']=='move_up') {

		JB_move_field_up(1, $_REQUEST['field_id']);
		JB_cache_del_keys_for_form(1);

	}

	if ($_REQUEST['action']=='move_down') {

		JB_move_field_down(1, $_REQUEST['field_id']);
		JB_cache_del_keys_for_form(1);

	}

	if ($NEW_FIELD=='NO') {
		echo '<i>Note: The Admin is always using the default theme. If you customized your form template, changes will not show here unless you update the default template too.</i>';
		
		$PostingForm->display_form($mode, true);
	
	}?>

	</td>
	<td valign="top">

	<?php if ((($mode=='EDIT') && ($_REQUEST['field_id']!='')) || ($NEW_FIELD=='YES')) JB_field_form($NEW_FIELD, $data, 1); ?>

	</td>

</tr>

</table>
<?php


if ($mode=='EDIT') {

	?>

	<IMG SRC="reserved.gif" WIDTH="13" HEIGHT="13" BORDER="0" ALT=""> - This field is reserved by the system, and cannot be deleted. You can however, change the field type / field name, and most other parameters.

	<?php


}

if ($mode!='EDIT') {

	echo "<hr>- Preview of the search form<br><br>";

	JB_display_dynamic_search_form (1, 2, 'PREVIEW');

}

?>
<script type="text/javascript">
		window.setTimeout ("window.scrollTo(0,0);",500);
		</script>

<?php

JB_admin_footer();

?>