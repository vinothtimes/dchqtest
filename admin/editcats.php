<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require_once ('../config.php');
require (dirname(__FILE__)."/admin_common.php");
require_once ('../include/category.inc.php');
# Copyright 2005-2009 Jamit Software
# http://www.jamit.com/


if ($_REQUEST['form_id'] != '') {
	$_SESSION['form_id'] = $_REQUEST['form_id'];
}
if ($_SESSION['form_id']=='') {
	$_SESSION['form_id'] =1;
}
$add = $_REQUEST['add'];

$action = $_REQUEST['action'];
$edit = $_REQUEST['edit'];
$category_id = $_REQUEST['category_id'];
$new_name = $_REQUEST['new_name'];
$allow_records = $_REQUEST['allow_records'];


JB_admin_header('Admin -> Edit Categories');

?>
<b>[Edit Categories]</b>

	<span style="background-color: #F2F2F2; border-style:outset; padding: 5px;"><a href="edit_config.php">Main</a></span>
 <span style="background-color: #FFFFCC; border-style:outset; padding:5px; "><a href="editcats.php">Categories</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="editcodes.php">Codes</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="language.php">Languages</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="emailconfig.php">Email Templates</a></span>	

	
	
<hr>
<h3>Select which categories to edit:</h3>
<span style="background-color: <?php if ($_SESSION['form_id']==1) echo "#FFFFCC"; else echo "#F2F2F2"; ?>; border-style:outset; padding:5px; "><a href="editcats.php?form_id=1">Job Categories</a></span>
  <span style="background-color: <?php if ($_SESSION['form_id']==2) echo "#FFFFCC"; else echo "#F2F2F2"; ?>; border-style:outset; padding:5px; "><a href="editcats.php?form_id=2">Resume Categories</a></span>
   <span style="background-color: <?php if ($_SESSION['form_id']==3) echo "#FFFFCC"; else echo "#F2F2F2"; ?>; border-style:outset; padding:5px; "><a href="editcats.php?form_id=3">Profile Categories</a></span>
    <span style="background-color: <?php if ($_SESSION['form_id']==4) echo "#FFFFCC"; else echo "#F2F2F2"; ?>; border-style:outset; padding:5px; "><a href="editcats.php?form_id=4">Employer Categories</a></span>
	 <span style="background-color: <?php if ($_SESSION['form_id']==5) echo "#FFFFCC"; else echo "#F2F2F2"; ?>; border-style:outset; padding:5px; "><a href="editcats.php?form_id=5">Candidate Categories</a></span>
   <hr>
<?php
	global $ACT_LANG_FILES;
	echo "Current Language: [".$_SESSION["LANG"]."] Select language:";

?>
<form name="lang_form">
<input type="hidden" name="cat" value="<?php echo jb_escape_html($_REQUEST['cat']); ?>">
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
<div >
<hr>
<input type="button" name="process" value="Process Categories" onclick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=process'">
<?php



if ($_REQUEST['cat']==false) {
   $_REQUEST['cat']=0;
}
$new_cat = $_REQUEST['new_cat'];
if ($new_cat != '') {
  
   if (strlen($new_cat)>0) {
		if ($_REQUEST['allow_records']=='ON') {
			$allow_records='Y';
		} else {
			$allow_records='N';
		}
		// split each category on line:

		$cats = preg_split('#\n#', $new_cat);

		foreach ($cats as $cat_name) {
			JB_add_cat($cat_name, $_REQUEST['cat'], $_SESSION['form_id'], $allow_records);
		}
		JB_compute_cat_has_child();

		JB_init_category_tables (0);

		
		JB_cache_del_keys_for_all_cats(1);
		JB_cache_del_keys_for_all_cats(2);
		JB_cache_del_keys_for_all_cats(3);
		JB_cache_del_keys_for_all_cats(4);
		JB_cache_del_keys_for_all_cats(5);
		JB_cache_del_keys_for_cat_options();
		
		

		$JBMarkup->ok_msg('Category Saved.');
   } 
   else {
	   $JBMarkup->error_msg('category name was left blank. Please retry.');
   }   
   
}

if ($action=='edit') {

	//if ($_SESSION["LANG"] == "EN") {

	if ($_REQUEST['allow_records']=='ON') {
		$allow_records='Y';
	} else {
		$allow_records='N';
	}

	$_REQUEST['list_order'] = (int) $_REQUEST['list_order'];

	if ($_SESSION["LANG"]=='EN') {
		$change_default_name_sql = ", category_name='".jb_escape_sql($new_name)."' ";
	}

	$sql = "update categories set allow_records='".jb_escape_sql($allow_records)."', list_order='".jb_escape_sql($_REQUEST['list_order'])."' $change_default_name_sql Where category_id='".jb_escape_sql($category_id)."' ";
	$result = JB_mysql_query($sql) or die (mysql_error());
	
	// update language

	$sql = "REPLACE INTO `cat_name_translations` (`category_id`, `lang`, `category_name`) VALUES (".jb_escape_sql($category_id).", '".jb_escape_sql($_SESSION["LANG"])."', '".jb_escape_sql($new_name)."')";
	$result = JB_mysql_query($sql) or die (mysql_error());
	JB_compute_cat_has_child();
	
	JB_cache_del_keys_for_all_cats(1);
	JB_cache_del_keys_for_all_cats(2);
	JB_cache_del_keys_for_all_cats(3);
	JB_cache_del_keys_for_all_cats(4);
	JB_cache_del_keys_for_all_cats(5);
	JB_cache_del_keys_for_cat_options();
		
	

	$JBMarkup->ok_msg('Category Saved.');


}

if ($action == 'del') {

	$_REQUEST['cat'] = JB_getCatParent($_REQUEST['category_id']); // so that we come back to parent..
	
	if (($obj_count = JB_del_cat_recursive ($_REQUEST['category_id'])) < 0) {
		$obj_count = -$obj_count;
		$JBMarkup->error_msg("<b>Error:</b></font> Cannot delete this category: It looks like you have ".$obj_count." record(s) in this category! <a href='".htmlentities($_SERVER['PHP_SELF'])."?action=del&category_id=".jb_escape_html($_REQUEST['category_id'])."&confirm=yes'>Click Here to delete anyway.</a>");
	}
	JB_compute_cat_has_child();
	if ($_REQUEST['save']!='') {
		
		
		JB_cache_del_keys_for_all_cats(1);
		JB_cache_del_keys_for_all_cats(2);
		JB_cache_del_keys_for_all_cats(3);
		JB_cache_del_keys_for_all_cats(4);
		JB_cache_del_keys_for_all_cats(5);
		JB_cache_del_keys_for_cat_options();
		
	}
	
	$JBMarkup->ok_msg('Category Deleted.');
	
}

if ($_REQUEST['action']=='process') {

	JB_init_category_tables (0);
	if ($_REQUEST['save']!='') {
		
		
		JB_cache_del_keys_for_all_cats(1);
		JB_cache_del_keys_for_all_cats(2);
		JB_cache_del_keys_for_all_cats(3);
		JB_cache_del_keys_for_all_cats(4);
		JB_cache_del_keys_for_all_cats(5);
		JB_cache_del_keys_for_cat_options();
		
	}
	JB_compute_cat_has_child();
	$JBMarkup->ok_msg('Category Cache Updated.');

}


echo "<div align='left'><h3><a href=".htmlentities($_SERVER['PHP_SELF']).">|</a>-&gt; ".JB_getPath_templated($_REQUEST['cat'], htmlentities($_SERVER['PHP_SELF']), $_SESSION['form_id'])." ";

if ($_REQUEST['cat'] != 0) {
	$MODE="ADMIN";
?>
<a onClick="return confirmLink(this, 'Delete this category, are you sure? (This will also delete all sub-categories in this category)') " href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=del&category_id=<?php echo $_REQUEST['cat']?>"><IMG src='delete.gif' width='16' height='16' border='0' alt='Delete'></a> 
<a href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?edit=<?php echo $_REQUEST['cat'];?>&cat=<?php echo $_REQUEST['cat']; ?>">
   <IMG alt="edit" src="edit.gif" width="16" height="16" border="0" alt="Edit">
   </a>
<?php
}
?>
</h3>
</div>



<?php


if ($edit == '') {
   //echo "<tr>";
   JB_add_new_cat_form($_REQUEST['cat']);
   //echo"</tr>";
   echo "<hr>";

}
if ($edit != '') {

	$row = JB_get_category($edit);

	?>
<P>
	<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=edit" method="post">
	<table border="0">
		<tr>
			<td>
				<font size="2">Edit Category Name:</font>
			</td>
			<td>
				<input type="text" name="new_name" size="35" value="<?php echo jb_escape_html($row['NAME']); ?>">
			</td>
		</tr>
		<tr><td colspan="2">
				<input type="checkbox" value="ON" name="allow_records" id="id01" <?php if ($row['allow_records']=='Y') {echo " checked ";} ?>> <label for="id01"><font size="2">Allow records to be added to this category.</font></label>
				<br>
				<font size="2">List order <input type='text' name='list_order' size="2" value="<?php echo $row['list_order']; ?>" > (optional: enter an ordinal number to list in special order. 1=first)</font>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="hidden" name="category_id" value="<?php echo $row['category_id']; ?>">
				<input type="hidden" name="cat"  value="<?php echo $row['parent_category_id']; ?>">
	
				<input type="hidden" name="action" value="edit">
				<input type="submit" value="Save">
			</td>
		</tr>
	</table>
	</form>
	</p>
	<hr>
	<p>&nbsp</p>
<table cellspacing="1" border="1"  align="left" width="100%">
	<?php

}

$MODE = "ADMIN";
JB_showAllCat($_REQUEST['cat'], 1, 3,  $_SESSION["LANG"], $_SESSION['form_id']);


?>
</table>
<?php

?>

</div>

<?php

JB_admin_footer();

?>
