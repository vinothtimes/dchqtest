<?php
# Copyright 2005-2009 Jamit Software
# http://www.jamit.com/



/* 

This function sets up the language used by the JB
- It sets a cookie to store the language
- Loads the $label hash table from the language files
- sets the other language related globals used by the job board

*/
function JB_init_lang_vars() {

	//global $jb_mysql_link;
	global $label;

	global $AVAILABLE_LANGS;
	global $ACT_LANG_FILES; // active languages
	global $LANG_FILES; // all language files
	global $FCK_LANG_FILES; // FCK editor language
	global $JB_LANG_THEMES;


	$AVAILABLE_LANGS=array();
	$ACT_LANG_FILES=array(); // active languages
	$LANG_FILES=array(); // all language files
	$FCK_LANG_FILES=array(); // FCK editor language
	$JB_LANG_THEMES=array();

	if ($lang_vars = jb_cache_get('lang_vars')) {
		$AVAILABLE_LANGS=$lang_vars['AVAILABLE_LANGS'];
		$ACT_LANG_FILES=$lang_vars['ACT_LANG_FILES'];
		$LANG_FILES=$lang_vars['LANG_FILES'];
		$FCK_LANG_FILES=$lang_vars['FCK_LANG_FILES'];
		$JB_LANG_THEMES=$lang_vars['JB_LANG_THEMES'];
		$lang_vars = null;
	} else {

		$sql = "SELECT * FROM lang ";
		if ($result = jb_mysql_query ($sql)) {

			
			// load languages into array.. map the language code to the filename
			// if mapping didn't work, default to english..


			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

				$lang_code = strtoupper($row['lang_code']);
				$AVAILABLE_LANGS [$lang_code] = $row['name'];
				$LANG_FILES[$lang_code] = $row['lang_filename'];
				$FCK_LANG_FILES[$lang_code] = $row['fckeditor_lang'];
				if ($row['is_active']=='Y') {
					$ACT_LANG_FILES[$lang_code] = $row['lang_filename'];
				} 
				
				$JB_LANG_THEMES[$lang_code] = $row['theme'];
			}

			$lang_vars['AVAILABLE_LANGS'] = $AVAILABLE_LANGS;
			$lang_vars['ACT_LANG_FILES'] = $ACT_LANG_FILES;
			$lang_vars['LANG_FILES'] = $LANG_FILES;
			$lang_vars['FCK_LANG_FILES'] = $FCK_LANG_FILES;
			$lang_vars['JB_LANG_THEMES'] = $JB_LANG_THEMES;

			jb_cache_add('lang_vars', $lang_vars);

		} else {
			$DB_ERROR = mysql_error();
		}

		

	}

	
	if (isset($_REQUEST['jb_theme']) && $_SESSION['ADMIN']) { // do this if the theme is being changed in Admin
		preg_match('#([a-z0-9_\-]+)#i', stripslashes($_REQUEST['jb_theme']), $m);
		$lang = (isset($_SESSION['LANG'])) ?  $_SESSION['LANG'] : 'EN';
		$sql = "UPDATE `lang` SET `theme`='".jb_escape_sql($m[1])."' WHERE lang_code='".jb_escape_sql($lang)."' ";
		jb_mysql_query($sql);
		jb_cache_delete('lang_vars');
		
	}

	
	if (!$DB_ERROR) {

		// the $label array is used to store all the language strings loaded from
		// the language file.
		if (!isset($label)) {
			$label = array();

			if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.$LANG_FILES[$_SESSION["LANG"]])) {
		
				// load the currently selected language
				include (dirname(__FILE__).DIRECTORY_SEPARATOR.$LANG_FILES[$_SESSION["LANG"]]);
				
			} else {
				// default to eng
				include (dirname(__FILE__).DIRECTORY_SEPARATOR."english.php");
			}
		}

	} 


}

function JB_init_lang_cookie() {

	//global $jb_mysql_link;

	if ((isset($_REQUEST['lang'])) && ($_REQUEST['lang']!='')) {
		$_REQUEST['lang'] = preg_replace('/[^a-z^-^_]+/i', '', $_REQUEST['lang']); // sanitize
		$sql = "SELECT * FROM lang WHERE `lang_code`='".jb_escape_sql($_REQUEST['lang'])."'";
		
		$result = jb_mysql_query($sql) or die (mysql_error());

		if (mysql_num_rows($result)>0) {
			$_SESSION["LANG"] = strtoupper($_REQUEST["lang"]);
			// save the requested language
			setcookie("JB_SAVED_LANG", strtoupper($_REQUEST["lang"]), 2147483647, '/');
			
		} else {

			$sql = "SELECT * FROM lang WHERE `is_default`='Y'";
			$result = jb_mysql_query($sql) or die (mysql_error());
			$row = mysql_fetch_array($result, MYSQL_ASSOC);
			$_SESSION["LANG"] = strtoupper($row["lang_code"]);
			// save the requested language
			setcookie("JB_SAVED_LANG", strtoupper($row["lang_code"]), 2147483647, '/');
			echo "Invalid language. Reverting to default language.";
		}
	} elseif (!isset($_SESSION["LANG"])) {
		// get the default language, or saved language

		if ($_COOKIE['JB_SAVED_LANG']!='') {
			$lang = preg_replace('/[^a-z^-^_]+/i', '', $_COOKIE['JB_SAVED_LANG']); // sanitize
			$_SESSION["LANG"] = strtoupper($lang);

		} else {
			$jb_default_lang = JB_get_default_lang();
			if ($jb_default_lang) {
				$_SESSION["LANG"] = strtoupper($jb_default_lang);
			} else {
				$_SESSION["LANG"] = 'EN';

			}
		}
		
	}


}

function JB_get_default_lang() {
	static $default_lang;
	//global $jb_mysql_link;
	if (!isset($default_lang)) {
		$sql = "SELECT lang_code FROM lang WHERE `is_default`='Y' ";
		//$result = JB_mysql_query ($sql) or die (mysql_error());
		$result = jb_mysql_query ($sql);
		$row = @mysql_fetch_array($result, MYSQL_ASSOC);
		$default_lang = $row['lang_code'];
		if (!$default_lang) {
			$default_lang = 'EN';
		}
		return $default_lang;
	} else {
		return $default_lang;
	}
	
}

?>