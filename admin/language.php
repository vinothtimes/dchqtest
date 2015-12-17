<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require ('../config.php');
if (($_REQUEST['action']=='delete') || ($_REQUEST['action']=='deactivate')) {
	setcookie("JB_SAVED_LANG", '', time() - 3600); // destroy the saved lang
}
require ("admin_common.php");
require_once (JB_basedirpath().'include/dynamic_forms.php');
require_once (JB_basedirpath().'include/code_functions.php');
require_once (JB_basedirpath().'include/category.inc.php');
# Copyright 2005-2009 Jamit Software
# http://www.jamit.com/

JB_admin_header('Admin -> Language');


?>


<b>[Languages]</b>
	<span style="background-color: #F2F2F2; border-style:outset; padding: 5px;"><a href="edit_config.php">Main</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="editcats.php">Categories</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="editcodes.php">Codes</a></span>
 <span style="background-color: #FFFFCC; border-style:outset; padding:5px; "><a href="language.php">Languages</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="emailconfig.php">Email Templates</a></span>	
	
<hr>
<?php

if (JB_DEMO_MODE=='YES') {
	$JBMarkup->ok_msg('Demo mode enabled - cannot save changes');
}

function validate_input () {
	if ($_REQUEST['lang_code']=='') {
		$error .= "- Language Code is blank <br>";
	} else {


		if ($action == 'new') {
			$sql  = "SELECT lang_code FROM `lang` WHERE `lang_code`='".jb_escape_sql($_REQUEST['lang_code'])."' ";
			$result = jb_mysql_query($sql) or print( mysql_error());
			if (mysql_num_rows($result) > 0) {
				$error .= "- Language code already exists<br>";
			}
		}

	}
	if ($_REQUEST['lang_filename']=='') {

		$error .= "- No language file selected <br>";

	} else {
		$_REQUEST['lang_filename'] = preg_replace('#[^a-z^0-9^_^-^\.]+#i', '', $_REQUEST['lang_filename']);
	}
	if ($_REQUEST['fckeditor_lang']=='') {

		$error .= "- No FCKEditor language file selected. <br>";

	} else {
		$_REQUEST['fckeditor_lang'] = preg_replace('#[^a-z^0-9^_^-^\.]+#i', '', $_REQUEST['fckeditor_lang']);
	
	}

	if (($_FILES['lang_image']['name']=='') && ($_REQUEST['action']!='edit')) {
		$error .= "- No image uploaded <br>";
	}
	if ($_REQUEST['name']=='') {

		$error .= "- Language name is blank<br>";

	}

	return $error;



}

function get_active_lang_count() {

	$sql = "SELECT count(*) FROM `lang` WHERE is_active='Y' ";
	$result = jb_mysql_query($sql);
	return array_pop(mysql_fetch_row($result));

}

// If there is only one language enabled, set is as default
function correct_default_lang() {

	$sql = "SELECT * FROM `lang` WHERE is_active='Y' AND is_default='Y' ";
	$result = jb_mysql_query($sql);
	if (mysql_num_rows($result)==0) { // nothing active and default?

		// reset default
		$sql = "UPDATE lang SET is_default='N' ";
		JB_mysql_query($sql);

		$sql = "SELECT * FROM `lang` WHERE is_active='Y' LIMIT 1 ";
		$result = jb_mysql_query($sql);
		$row = mysql_fetch_array($result, MYSQL_ASSOC);


		$sql = "UPDATE lang set is_default='Y' where lang_code='".jb_escape_sql($row['lang_code'])."' ";
		JB_mysql_query($sql);
		
	}

}

if ($_REQUEST['action']=='activate') {

	$sql = "UPDATE lang set is_active='Y' where lang_code='".jb_escape_sql($_REQUEST['lang_code'])."' ";
	JB_mysql_query($sql) or die (mysql_error());
	correct_default_lang();
	JB_cache_flush();
	$JBMarkup->ok_msg('Activated.'); 

}

if ($_REQUEST['action']=='deactivate') {

	if (get_active_lang_count()==1) {
		$JBMarkup->error_msg('Cannot deactivate - at least 1 language must be active.'); 
	} else {

		$sql = "UPDATE lang set is_active='N' where lang_code='".jb_escape_sql($_REQUEST['lang_code'])."' ";
		JB_mysql_query($sql) or die (mysql_error());
		correct_default_lang();
		
		JB_cache_flush();

		$JBMarkup->ok_msg('Deactivated.'); 

	}

}

if ($_REQUEST['action']=='default') {

	$sql = "UPDATE lang set is_default='N' ";
	JB_mysql_query($sql) or die (mysql_error());

	$sql = "UPDATE lang set is_default='Y' where lang_code='".jb_escape_sql($_REQUEST['lang_code'])."' ";
	JB_mysql_query($sql) or die (mysql_error());

	$JBMarkup->ok_msg('Default Set.');

}

if ($_REQUEST['action']=='delete') {

	$sql = "DELETE FROM lang WHERE lang_code='".jb_escape_sql($_REQUEST['lang_code'])."' ";
	JB_mysql_query($sql) or die (mysql_error());

	// delete from form field translations

	$sql = "DELETE FROM form_field_translations WHERE lang='".jb_escape_sql($_REQUEST['code'])."' ";
	JB_mysql_query($sql) or die (mysql_error());


	// delete from codes

	$sql = "DELETE FROM codes_translations WHERE lang='".jb_escape_sql($_REQUEST['code'])."' ";
	JB_mysql_query($sql) or die (mysql_error());

	// delete form categories

	$sql = "DELETE FROM `cat_name_translations` WHERE lang='".jb_escape_sql($_REQUEST['code'])."' ";
	JB_mysql_query($sql) or die (mysql_error());

	// delete from email template translations

	$sql = "DELETE FROM `email_template_translations`  WHERE lang='".jb_escape_sql($_REQUEST['code'])."' ";
	JB_mysql_query($sql) or die (mysql_error());

	$JBMarkup->ok_msg('Language Deleted.'); 



}
//JB_format_email_translation_table ();

if ($_REQUEST['submit']!='') {

	$error = validate_input();

	if ($error == '') {

		if ($_FILES['lang_image']['tmp_name'] != '') {
			$data = base64_encode(fread(fopen($_FILES['lang_image']['tmp_name'], "r"),$_FILES['lang_image']['size']));
			$image_file = $_FILES['lang_image']['name'];	
			$mime_type = $_FILES['lang_image']['type'];

			$image_sql = "image_data='".jb_escape_sql($data)."', mime_type='".jb_escape_sql($mime_type)."', lang_image='".jb_escape_sql($image_file)."',";


		}

		if ($_REQUEST['action']=='edit') {

			$sql = "UPDATE `lang` SET name='".jb_escape_sql($_REQUEST['name'])."', $image_sql charset='".jb_escape_sql($_REQUEST['charset'])."', lang_filename='".jb_escape_sql($_REQUEST['lang_filename'])."', fckeditor_lang='".jb_escape_sql($_REQUEST['fckeditor_lang'])."', theme='".jb_escape_sql($_REQUEST['jb_theme'])."' WHERE `lang_code`='".jb_escape_sql($_REQUEST['lang_code'])."' ";

		} else {
		
			$sql = "INSERT INTO `lang` ( `lang_code` , `lang_filename` , `lang_image` , `is_active` , `name` , `image_data`, `mime_type`, `is_default`, `charset`, `fckeditor_lang`, `theme` ) VALUES ('".jb_escape_sql($_REQUEST['lang_code'])."', '".jb_escape_sql($_REQUEST['lang_filename'])."', '".jb_escape_sql($image_file)."', 'Y', '".jb_escape_sql($_REQUEST['name'])."', '".jb_escape_sql($data)."', '".jb_escape_sql($mime_type)."', 'N', '".jb_escape_sql($_REQUEST['charset'])."', '".jb_escape_sql($_REQUEST['fckeditor_lang'])."', '".jb_escape_sql($_REQUEST['jb_theme'])."')";
		}

		JB_mysql_query($sql) or die (mysql_error().$sql);

		// reload available langs..

		global $AVAILABLE_LANGS;
		global $LANG_FILES;
		$sql = "SELECT * FROM lang ";
		$result = JB_mysql_query ($sql) or die (mysql_error());
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$AVAILABLE_LANGS [$row['lang_code']] = $row['name'];
			$LANG_FILES[$row['lang_code']] = $row['lang_filename'];
		}

		// update category translations
		// (copy English to new lang)
		JB_init_category_tables (0);

		// update code translations
		// (copy English to new lang)
		
		$sql = "SELECT * FROM form_fields WHERE `field_type`='RADIO' or `field_type`='CHECK' or `field_type`='MSELECT' or `field_type`='SELECT'  ";
		$result = JB_mysql_query($sql) or die (mysql_error().$sql);
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			JB_format_codes_translation_table ($row['field_id']);
		}

		// update forms
		// (copy English to new lang)

		JB_format_field_translation_table (1);
		JB_format_field_translation_table (2);
		JB_format_field_translation_table (3);
		JB_format_field_translation_table (4);
		JB_format_field_translation_table (5);

		// update email templates

		JB_format_email_translation_table ();

		preg_match('#([a-z0-9_\-]+)#i', stripslashes($_REQUEST['jb_theme']), $m);
		$lang_file = JB_get_theme_dir().$m[1]."/lang/english_default.php";
		if (file_exists($lang_file)) {
			JB_merge_language_files(true);
			echo '<br><br>';
		}

		@touch ("../config.php"); // update config.php timestamp.

		$JBMarkup->ok_msg('Language Updated.'); 

		$_REQUEST['action'] = '';


	} 

}

?>

<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" >
			<tr bgColor="#eaeaea">
				<td><b><font size="2">Language</b></font></td>
				<td><b><font size="2">Code</b></font></td>
				<td><b><font size="2">File</b></font></td>
				<td><b><font size="2">Image</b></font></td>
				<td><b><font size="2">Active</b></font></td>
				<td><b><font size="2">Tool</b></font></td>
			</tr>
<?php
			$result = JB_mysql_query("select * FROM lang ") or die (mysql_error());
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

				?>

				<tr bgcolor="<?php echo ($row['lang_code']==$_REQUEST['code']) ? '#FFFFCC' : '#ffffff'; ?>">

				<td><font size="2"><?php echo $row['name'];?></font></td>
				<td><font size="2"><?php echo $row['lang_code'];?></font></td>
				<td><font size="2"><?php echo $row['lang_filename'];?></font></td>
				<td><font size="2"><img alt="<?php echo $row['lang_code'];?>" src="../lang_image.php?code=<?php echo $row['lang_code'];?>"></font></td>
				<td><font size="2">  <?php if ($row['is_active']=='Y') { ?><IMG SRC="active.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALT=""><?php } else { ?><IMG SRC="notactive.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALT=""><?php } ;?> <?php if ($row[is_active]!='Y') {?> [<a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=activate&amp;lang_code=<?php echo $row['lang_code'];?>">Activate</a>] <?php } if ($row[is_active]=='Y') {?> [<a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=deactivate&amp;lang_code=<?php echo $row['lang_code'];?>">Deactivate</a>] <?php }?>[<a href="<?php echo $_SERVER['PHP_SELF'];?>?action=edit&amp;lang_code=<?php echo $row['lang_code'];?>">Edit</a>] <?php if ($row['is_default']!='Y') {  ?> [<a onclick=" return confirmLink(this, 'Delete, are you sure? (There is no undo! It is better to click cancel now and deactivate this language instead...)') " href="<?php echo $_SERVER['PHP_SELF'];?>?action=delete&amp;lang_code=<?php echo $row['lang_code'];?>">Delete</a>] <?php } ?> 
				[<a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=default&amp;lang_code=<?php echo $row['lang_code'];?>"><?php if ($row['is_default']=='N') { echo "Set Default";  }?></a> <?php if ($row['is_default']=='Y') { echo "Default";} ; ?>]
				</font></td>
				<?php
				$new_window = "onclick=\"window.open('translation_tool.php?target_lang=".$row['lang_code']."', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=yes,resizable=1,width=800,height=500,left = 50,top = 50');return false;\"";	
				
				?>
				<td>
				<font size="2"><a href="" <?php echo $new_window; ?> >Translation / Editing Tool</a>
				
				</td>

				</tr>


				<?php

			}
?>
</table>
<input type="button" value="New Language..." onclick="window.location='language.php?action=new'">
<hr>
<?php

if ($error !='') {
	echo '<div>';
	$JBMarkup->error_msg("<b>ERROR:</b> Cannot save langauge into database.");
	echo $error;
	echo '</div>';

}

function lang_file_options ($sel_value) {

	$dh = opendir ("../lang");
	while (($file = readdir($dh)) !== false) {
		if ($sel_value ==  $file) {
			$sel = " selected ";
		} else {
			$sel = "";
		}
		if (($file != '.') && ($file != '..') && ($file != 'lang.php' ) && ($file != 'english_default.php' ) && ($file != 'index.html' )){
           echo "<option value='$file' $sel>$file</option>\n";
		}
     }
     closedir($dh);



}

function fck_lang_file_options ($sel_val = '') {

	if (isset($_REQUEST['fckeditor_lang'])) {
		$sel_val = $_REQUEST['fckeditor_lang'];
	}

	$dh = opendir (JB_basedirpath()."include/lib/ckeditor/lang");
	while (($file = readdir($dh)) !== false) {
		$arr = explode ('.', $file);
		$ext = array_pop($arr);
		$lang = array_pop($arr);
		if ($_REQUEST['fckeditor_lang'] ==  $lang) {
			$sel = " selected ";
		} else {
			$sel = "";
		}
	
		if ($ext=='js'){
           echo "<option value='$lang' $sel>$file</option>\n";
		}
     }
     closedir($dh);



}

if ($_REQUEST['action']=='edit') {

	$sql = "SELECT * FROM lang WHERE `lang_code`='".jb_escape_sql($_REQUEST['lang_code'])."' ";
	$result = JB_mysql_query($sql) or die (mysql_error());
	$lang_row = mysql_fetch_array($result, MYSQL_ASSOC);
	
	
}


?>

<?php

if ($_REQUEST['action']=='new') {
	echo "<h4>New Language:</h4>";
	//echo "<p>Note: Make sure that you create a file for your new language in the /lang directory.</p>";
	admin_language_form();
}
if ($_REQUEST['action']=='edit') {
	echo "<h4>Edit Language:</h4>";
	admin_language_form($lang_row);
}

?>
<font size="1">
Note: If you have deactivated a language, but it still comes up, then please clear the cookies in the browser.
</font><br>
<font size="1">
Note 2: Jamit Software officially supports the English version. All other language translations are donated by some of our past clients and we do not guarantee their correctness or completeness. If you are using a language other than English, please make sure to use the Translation Tool to edit / proof read the existing language.
</font>
<?php

JB_admin_footer();


########################################################################

function admin_language_form($lang_row = array()) {

	$edit_mode=false;
	$lang_img = '';

	if (sizeof($lang_row)!=0) {
		
		$edit_mode=true;

		$_REQUEST['lang_code'] = $lang_row['lang_code'];
		$_REQUEST['name'] = $lang_row['name'];
		$_REQUEST['jb_theme'] = $lang_row['jb_theme'];
		$_REQUEST['fckeditor_lang'] = $lang_row['fckeditor_lang'];
		$_REQUEST['lang_filename'] = $lang_row['lang_filename'];

		$lang_img = '<img src="'.JB_BASE_HTTP_PATH.'lang_image.php?code='.jb_escape_html($lang_row['lang_code']).'">';
	}

	$disabled = '';
	if ($edit_mode) {
		$disabled = " disabled ";
	}

	?>
<form enctype="multipart/form-data" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF'])?>">
<input type="hidden" value="<?php echo jb_escape_html($_REQUEST['action'])?>" name="action" >
<?php
if ($edit_mode) { ?>
	<input type="hidden" value="<?php echo jb_escape_html($_REQUEST['lang_code'])?>" name="lang_code" >
	<?php
}
?>

<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9">
<tr bgcolor="#ffffff" ><td><font size="2">Language Name:</font></td><td><input size="30" type="text" name="name" value="<?php echo jb_escape_html($_REQUEST['name']); ?>"/> eg. English</td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">Language Code:</font></td><td><input <?php echo $disabled; ?> size="2" type="text" name="lang_code" value="<?php echo jb_escape_html($_REQUEST['lang_code']); ?>"> eg. EN</td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">Language File:</font></td><td><select  name="lang_filename" ><option></option><?php lang_file_options($_REQUEST['lang_filename']); ?></select><small>(To create a new language file: copy the lang/english.php file and change the name of the new file to the name of your language. eg. lang/dutch.php - Give permissions for writing to this file.)</small></td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">Image:</font></td><td><?php echo $lang_img; ?><input size="15" type="file" name="lang_image" value=""> <font size='1'>(Do not select if you want to keep the existing image)</font></td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">FCKEditor language file:</font></td><td><select  type="text" name="fckeditor_lang" ><option></option><?php fck_lang_file_options($_REQUEST['fckeditor_lang']); ?></select></select><font size="2">(The FCKEditor is a HTML editor in the include/lib/fckeditor/ directory. The language files are located in fckeditor/editor/lang/)</a></td>
</tr>
<tr bgcolor="#ffffff"><td><font size="2">Language Theme:</font></td>
<td>
<select name='jb_theme'>
		<option value=''>[Select]</option>
		<?php if ($_REQUEST['jb_theme']=='') { $_REQUEST['jb_theme']=JB_THEME; } JB_theme_option_list($_REQUEST['jb_theme']); ?>
	  </select>
</td>
</tr>
</table>
<input type="submit" name="submit" value="Submit">
</form>

<?php

}

?>