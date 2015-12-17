<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

/*

The following function uses the `codes` table to compare the
`codes_translations` table

*/


function JB_format_codes_translation_table ($field_id) {
	global $AVAILABLE_LANGS;

	$sql = "SELECT * FROM codes WHERE `field_id`='".jb_escape_sql($field_id)."' ";
	
	$f_result = JB_mysql_query ($sql);
	while ($f_row = mysql_fetch_array($f_result)) { 



		foreach  ($AVAILABLE_LANGS as $key => $val) {

			$sql = "select * FROM codes_translations WHERE `field_id`='".jb_escape_sql($f_row['field_id'])."'  AND `lang`='".jb_escape_sql($key)."' AND `code`='".jb_escape_sql($f_row['code'])."' ";

			$result = JB_mysql_query($sql) or die($sql.mysql_error());
		
			if (mysql_num_rows($result)==0) {
				
				$sql = "REPLACE INTO `codes_translations` (`field_id`, `code`, `lang`, `description`) VALUES ('".jb_escape_sql($f_row['field_id'])."', '".jb_escape_sql($f_row['code'])."', '".jb_escape_sql($key)."', '".jb_escape_sql(addslashes($f_row['description']))."')";
		

				JB_mysql_query($sql) or die (mysql_error());

			}

		}

	}

}

#################################################
# Changes the code id, and updates *all* the records in the database
# with the given field id with the new code_id
function JB_change_code_id ($field_id, $code, $new_code) {

	// find which form the field_id is from

	$sql = "SELECT form_id FROM form_fields where field_id='".jb_escape_sql($field_id)."' ";
	$result = JB_mysql_query($sql) or die(mysql_error().$sql);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$form_id = $row['form_id'];

	$sql = "UPDATE codes SET code='$new_code' where field_id='".jb_escape_sql($field_id)."' and code='".jb_escape_sql($code)."' ";
	$result = JB_mysql_query($sql) or die(mysql_error().$sql);


	$sql = "UPDATE codes_translations SET code='".jb_escape_sql($new_code)."' where field_id='".jb_escape_sql($field_id)."' and code='$code' ";
	$result = JB_mysql_query($sql) or die(mysql_error().$sql);


	switch ($form_id) {

		case '1': // posting form
			$table = 'posts_table';  $id='post_id';
			$sql = "select post_id as ID, `".jb_escape_sql($field_id)."` FROM posts_table WHERE `".jb_escape_sql($field_id)."` LIKE '%".jb_escape_sql($code)."%' ";
			break;
		case '2': // resume form
			$table = 'resumes_table'; $id='resume_id';
			$sql = "select resume_id as ID, `".jb_escape_sql($field_id)."` FROM resumes_table WHERE `".jb_escape_sql($field_id)."` LIKE '%".jb_escape_sql($code)."%' ";
			break;
		case '3': // profile form
			$table = 'profiles_table'; $id='profile_id';
			$sql = "select profile_id as ID, `".jb_escape_sql($field_id)."` FROM profiles_table WHERE `".jb_escape_sql($field_id)."` LIKE '%".jb_escape_sql($code)."%' ";
			break;
		case '4': // advertiser form
			$table = 'employers'; $id='ID';
			$sql = "select ID, `".jb_escape_sql($field_id)."` FROM employers WHERE `".jb_escape_sql($field_id)."` LIKE '%".jb_escape_sql($code)."%' ";
			break;
		case '5': // seeker form
			$table = 'users'; $id='ID';
			$sql = "select ID as ID, `$field_id` FROM users WHERE `$field_id` LIKE '%$code%' ";
			break;

	}

	$result = JB_mysql_query($sql) or die(mysql_error().$sql);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		$new_codes = array();
		$codes = explode(',',$row[$field_id]);


		foreach ($codes as $c) {

			if ($c == $code) {
				
				$new_codes[] = $new_code;
			} else {
				
				$new_codes[] = $c;

			}

		}

		$codes = implode(',', $new_codes);

		$sql = "UPDATE $table SET `$field_id`='".jb_escape_sql($codes)."' WHERE $id = '".jb_escape_sql($row['ID'])."' ";
		JB_mysql_query($sql) or die(mysql_error().$sql);
	

	}

	

	JB_cache_del_keys_for_codes($field_id);




}

//$jb_code_table;

######################################################################

function JB_getCodeDescription ($field_id, $code) {
	
	

	$field_id = (int) $field_id;

	if ($jb_code_table = jb_cache_get('jb_code_table_fid_'.$field_id.'_lang_'.$_SESSION['LANG'])) {
		
		if (isset($jb_code_table[$field_id][$code])) {
			return $jb_code_table[$field_id][$code]; // return the description
		}
	}

	if ($_SESSION['LANG'] != '') {

		$sql = "SELECT `description` FROM `codes_translations` WHERE field_id='".jb_escape_sql($field_id)."' AND `code` = '".jb_escape_sql($code)."' AND lang='".jb_escape_sql($_SESSION['LANG'])."' ";

	} else {
		
		$sql = "SELECT `description` FROM `codes` WHERE field_id='".jb_escape_sql($field_id)."' AND `code` = '".jb_escape_sql($code)."'";
	}
   
   
	$result = JB_mysql_query($sql) or die($sql.mysql_error());
	if ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		if (strlen($row['description'])>0) {
			$jb_code_table[$field_id][$code] = $row['description'];
			jb_cache_set('jb_code_table_fid_'.$field_id.'_lang_'.$_SESSION['LANG'], $jb_code_table);
			return $row['description'];
		} 
		
	} else {
		$jb_code_table[$field_id][$code]=' ';
		jb_cache_set('jb_code_table_fid_'.$field_id.'_lang_'.$_SESSION['LANG'], $jb_code_table);
		return $row['description'];
	}
	

}

###################################################

function JB_insert_code ($field_id, $code, $description) {

   $sql = "SELECT `code` FROM `codes` WHERE field_id='".jb_escape_sql($field_id)."' AND `code` = '".jb_escape_sql($code)."'";
   $result = JB_mysql_query($sql) or die($sql.mysql_error());

   if (mysql_num_rows($result) > 0 ) {
      echo '<font color="#FF0000">';
      echo "CANNOT INSERT a new Code: $code already exists in the database!<p>";
      echo '</font>';
      return;

   }

   $sql = "INSERT INTO `codes` ( `field_id` , `code` , `description` )  VALUES ('".jb_escape_sql($field_id)."', '".jb_escape_sql($code)."', '".jb_escape_sql($description)."')";

    JB_mysql_query($sql) or die($sql.mysql_error());

   if ($_SESSION["LANG"] != '') {

		$sql = "INSERT INTO `codes_translations` ( `field_id` , `code` , `description`, `lang` )  VALUES ('".jb_escape_sql($field_id)."', '".jb_escape_sql($code)."', '".jb_escape_sql($description)."', '".jb_escape_sql($_SESSION['LANG'])."')";

		JB_mysql_query($sql) or die($sql.mysql_error());


   }

   JB_format_codes_translation_table ($field_id);
   
   JB_cache_del_keys_for_codes($field_id);

  

}
################################################################
function JB_modify_code ($field_id, $code, $description) {
   $sql = "UPDATE `codes` SET `description` = '".jb_escape_sql($description)."' ".
          "WHERE `field_id` = '".jb_escape_sql($field_id)."' AND `code` = '".jb_escape_sql($code)."'";
   JB_mysql_query($sql) or die($sql.mysql_error());

   if ($_SESSION["LANG"] != '') {

		$sql = "UPDATE `codes_translations` SET `description` = '".jb_escape_sql($description)."' ".
          "WHERE `field_id` = '".jb_escape_sql($field_id)."' AND `code` = '".jb_escape_sql($code)."' AND `lang`='".jb_escape_sql($_SESSION["LANG"])."' ";
		JB_mysql_query($sql) or die($sql.mysql_error());

   }
   
   JB_cache_del_keys_for_codes($field_id);

   

}
#####################################################
/*
   This is the reverse of function JB_getCodeDescription();
*/
function JB_getCodeFromDescription ($field_id, $description, $lang='') {
	if (!$lang) {
		$lang = JB_get_default_lang();
	}
	$sql = "SELECT `code` FROM `codes_translations` WHERE field_id='".jb_escape_sql($field_id)."' AND `description` = '".jb_escape_sql($description)."' and lang='".jb_escape_sql($lang)."' ";
	
	$result = JB_mysql_query($sql) or die($sql.mysql_error());
	if ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		return $row['code'];
	} else {
		return false;
	}

}

//////////////////////

function JB_is_valid_code($field_id, $code) {
	$sql = "SELECT `code` FROM `codes` WHERE field_id='".jb_escape_sql($field_id)."' AND code='".jb_escape_sql($code)."' ";
	$result = JB_mysql_query($sql) or die($sql.mysql_error());
	if (mysql_num_rows($result)>0) {
		return true;
	} else {
		return false;
	}

}

?>