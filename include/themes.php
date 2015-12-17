<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################



function JB_init_themes() {

	global $JBMarkup, $label;

	global $JB_LANG_THEMES; // set in lang/lang.php

	define ('JB_DEFAULT_THEME', 'default');
	define ('SCW_INCLUDE', 'Y');

	if (!defined('JB_THEME')) {
		define ('JB_THEME',  JB_DEFAULT_THEME);
	}

	if (isset($JB_LANG_THEMES[$_SESSION['LANG']])) {
		// Set the theme that was set in the session
		$JB_THEME = $JB_LANG_THEMES[$_SESSION['LANG']];
	} else {
		$JB_THEME = JB_THEME;
	}


	if (JB_DEMO_MODE=='YES') {
		// setting the theme using GET (overwrite the above setting)
		if (isset($_GET['set_theme'])) {
			preg_match('#([a-z0-9_\-]+)#i', stripslashes($_GET['set_theme']), $m);
			if (file_exists(JB_get_theme_dir().$m[1]."/")) {
				$_SESSION['JB_THEME'] = $m[1]; // allow to change the theme by  appending ?set_theme=theme_name to the URL .
			}
		}
		if (!isset($_SESSION['JB_THEME'])) {
			$_SESSION['JB_THEME'] = $JB_THEME;
		}
		$JB_THEME = $_SESSION['JB_THEME'];
	}

	if (!defined('JB_ADMIN_THEME')) {
		define ('JB_ADMIN_THEME', JB_DEFAULT_THEME);
	}
	if (strpos($_SERVER['PHP_SELF'],'/admin')!==false) {
		$JB_THEME = JB_ADMIN_THEME; // admin/ uses default theme
	}

	JBPlug_do_callback('set_theme', $JB_THEME); // added in 3.7

	define ('JB_THEME_PATH', JB_get_theme_dir().$JB_THEME.'/');
	define ('JB_THEME_URL', JB_BASE_HTTP_PATH.'include/themes/'.$JB_THEME.'/');

	define ('JB_DEFAULT_THEME_PATH',  dirname(__FILE__).'/themes/'.JB_DEFAULT_THEME.'/');
	define ('JB_DEFAULT_THEME_URL',  JB_BASE_HTTP_PATH.'include/themes/'.JB_DEFAULT_THEME.'/');

	 // Load Global markup class
	$JBMarkup = JB_get_MarkupObject();

	JBPlug_do_callback('init_themes', $JBMarkup, $JB_THEME);

	


}

###################################################

function JB_theme_check_compatibility() {

	$files = array('index-header.php', 'candidates-header.php', 'candidates-outside-header.php', 'employers-header.php', 'employers-outside-header.php');

	foreach ($files as $file_name) {
		if (file_exists(dirname(__FILE__).'/themes/'.JB_THEME.'/'.$file_name)) {
			$path = dirname(__FILE__).'/themes/'.JB_THEME.'/'.$file_name;
			$contents = file_get_contents($path);
		} else {
			$path = dirname(__FILE__).'/themes/'.JB_DEFAULT_THEME.'/'.$file_name;
			$contents = file_get_contents($path);
		}	
		
		if (!preg_match('#\$JBMarkup->head_open\(\)#i', $contents) && (preg_match('#<head>#i', $contents))) {
		// if (preg_match('#<head>#i', $contents)) {
			echo '<span style="color:maroon">- Template incompatibility detected. Please edit '.$path.' and replace the &lt;head&gt; tag with this PHP code: $JBMarkup->head_open(); </span><br>';
		}
	}

	//  $JBMarkup->body_close();

	$files = array('index-footer.php', 'candidates-footer.php', 'candidates-outside-footer.php', 'employers-footer.php', 'employers-outside-footer.php');

	foreach ($files as $file_name) {
		if (file_exists(dirname(__FILE__).'/themes/'.JB_THEME.'/'.$file_name)) {
			$path = dirname(__FILE__).'/themes/'.JB_THEME.'/'.$file_name;
			$contents = file_get_contents($path);
		} else {
			$path = dirname(__FILE__).'/themes/'.JB_DEFAULT_THEME.'/'.$file_name;
			$contents = file_get_contents($path);
		}	
		if (!preg_match('#\$JBMarkup->body_close\(\)#i', $contents) && (preg_match('#</body>#i', $contents))) {
		//if (preg_match('#</body>#i', $contents)) {
			echo '<span style="color:maroon">- Template incompatibility detected. Please edit '.$path.' and replace the &lt;/body&gt; tag with this PHP code: $JBMarkup->body_close(); Note: It may be best to take a new copy of '.dirname(__FILE__).'/themes/'.JB_DEFAULT_THEME.'/'.$file_name.' file and re-customize it.</span><br>';
		}
	}
	##############################################
	# Save resume feature
	$files = array('employer-menu.php');

	foreach ($files as $file_name) {
		if (file_exists(dirname(__FILE__).'/themes/'.JB_THEME.'/'.$file_name)) {
			$path = dirname(__FILE__).'/themes/'.JB_THEME.'/'.$file_name;
			$contents = file_get_contents($path);
		} else {
			$path = dirname(__FILE__).'/themes/'.JB_DEFAULT_THEME.'/'.$file_name;
			$contents = file_get_contents($path);
		}	
		
		if (strpos($contents, 'saved.php')===false) {
			echo '<span style="color:maroon">- Template incompatibility detected. Please edit '.$path.' and complete the following change:<br>';

			echo '
			<pre>
FILE

'.$path.'

FIND THE CODE

0 => 
  array (
    \'label\' => $label["employer_menu_browse_resumes"],
    \'link\' => \'search.php\',
    \'image\' => \'\',
    
  ),

ADD on a line BELOW

4 => 
  array (
    \'label\' => $label["employer_menu_saved_resumes"],
    \'link\' => \'saved.php\',
    \'image\' => \'\',
    
  ),
</pre><br>';

			echo 'Note: It may be best to take a new copy of '.dirname(__FILE__).'/themes/'.JB_DEFAULT_THEME.'/'.$file_name.' file and re-customize it. This change will add support for the new \'Save Resumes\' feature.</span><br>';
		}
	}




}

###################################################

function JB_theme_option_list($selected) {
	$dir = JB_get_theme_dir();
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				//echo "filename: $file : filetype: " . filetype($dir . $file) . "\n";
				if ((filetype($dir . $file)=='dir') && ($file!='.') && ($file!='..')) {
					if ($file==$selected) {
						$sel = " selected ";
					} else {
						$sel = "";
					}
					echo "<option $sel value=\"".$file."\">".$file."</option>";

				}
			}
			closedir($dh);
		}
	}



}

###################################################

# Used by the translation tool, reads the
# theme's english_default.php file and appends it to the $source_code
function jb_theme_append_english_default_source(&$source_code) {

	global $JB_LANG_THEMES;
	$themes = array_unique($JB_LANG_THEMES);

	if (!in_array(JB_THEME, $themes)) {
		$themes['EN']=JB_THEME;
	}

	foreach ($themes as $key=>$theme) {
		$theme_path = JB_get_theme_dir().$theme.'/';
		//echo $theme_path.'lang/english_default.php <br>';
		if (file_exists($theme_path.'lang/english_default.php')) {
			$handle = fopen($theme_path.'lang/english_default.php', "rb");
			while ($buffer= fgets($handle, 4096)) {
				if (preg_match ('#\$label\[.([a-z0-9_]+).\].*#i', $buffer, $m)) {
					$source_code[$m[1]] = $buffer;
				}
			}
			fclose($handle);

		}
		

	}

	/*
//print_r($source_code);
	//die();

	return;

	

	$file = JB_THEME_PATH."lang/english_default.php";
	
	if (file_exists($file)) {
		$handle = fopen($file, "rb");
		while ($buffer= fgets($handle, 4096)) {
			if (preg_match ('#\$label\[.([a-z0-9_]+).\].*#i', $buffer, $m)) {
				$source_code[$m[1]] = $buffer;
			}
		}
	}

	*/


}

###################################################

function JB_theme_append_english_default_labels() {
	global $label;
	// include label form current theme

	global $JB_LANG_THEMES;
	$themes = array_unique($JB_LANG_THEMES);

	if (!in_array(JB_THEME, $themes)) {
		$themes['EN']=JB_THEME;
	}

	foreach ($themes as $key=>$theme) {
		$theme_path = JB_get_theme_dir().$theme.'/';
		//echo $theme_path.'lang/english_default.php <br>';
		if (file_exists($theme_path.'lang/english_default.php')) {
			require ($theme_path.'lang/english_default.php');
		}
	}

	return;

	/*

	$theme_lang = JB_THEME_PATH."lang/english_default.php";

	if (file_exists($theme_lang)) {
		require ($theme_lang);
	}

	*/

}

###################################################
function jb_get_common_js_url() {

	if (file_exists(JB_THEME_PATH."js/common.js")) {
		return JB_THEME_URL."js/common.js";
	} else {
		return JB_DEFAULT_THEME_URL."js/common.js";
	}
}

function jb_get_map_img_url() {

	if (file_exists(JB_THEME_PATH."images/map-small.jpg")) {
		return JB_THEME_URL."images/map-small.jpg";
	} else {
		return JB_DEFAULT_THEME_URL."images/map-small.jpg";
	}
}

function jb_get_map_img_path() {
	if (file_exists(JB_THEME_PATH."images/map-small.jpg")) {
		return JB_THEME_PATH."images/map-small.jpg";
	} else {
		return JB_DEFAULT_THEME_PATH."images/map-small.jpg";
	}

}

function jb_get_pin_img_url() {

	if (file_exists(JB_THEME_PATH."images/pin.gif")) {
		return JB_THEME_URL."images/pin.gif";
	} else {
		return JB_DEFAULT_THEME_URL."images/pin.gif";
	}


}

function jb_get_pin_img_path() {

	if (file_exists(JB_THEME_PATH."images/pin.gif")) {
		return JB_THEME_PATH."images/pin.gif";
	} else {
		return JB_DEFAULT_THEME_PATH."images/pin.gif";
	}


}

function jb_get_candidates_menu_path() {

	if (file_exists(JB_THEME_PATH."candidate-menu.php")) {
		return JB_THEME_PATH."candidate-menu.php";
	} else {
		return JB_DEFAULT_THEME_PATH."candidate-menu.php";
	}


}

############################################
# Javascript libraries

# string $rel: The relative path, eg, 'admin/' 
# If rel is given, then the src is returned as a relative path
# If not given, then returned as absolute
function jb_get_JQuery_src($rel=false) {

	static $src;
	if (isset($src)) return $src;

	jbplug_do_callback('get_jquery_src', $src);
	if (strlen($src)>0) return $src;

	$base = 'include/lib/jquery/jquery.js';

	if ($rel) {
		$src = JB_get_relative_path($base);
		return $src;
	} else {
		$src = JB_BASE_HTTP_PATH.$base;
		return $src;
	}

}

/*

CKEditor, added in v3.6.6

*/
function jb_get_CK_js_base($rel=false) {

	static $src;
	if (isset($src)) return $src;

	jbplug_do_callback('get_ck_src', $src);
	if (strlen($src)>0) return $src;

	$base = 'include/lib/ckeditor/';

	if ($rel) {
		
		$src = JB_get_relative_path($base);
		return $src;
		
	} else {
		$src = JB_BASE_HTTP_PATH.$base;
		return $src;
	}

}

function jb_get_FCK_js_base($rel=false) {

	static $src;
	if (isset($src)) return $src;

	jbplug_do_callback('get_fck_src', $src);
	if (strlen($src)>0) return $src;

	$base = 'include/lib/fckeditor/';

	if ($rel) {
		
		$src = JB_get_relative_path($base);
		return $src;
		
	} else {
		$src = JB_BASE_HTTP_PATH.$base;
		return $src;
	}

}

function jb_get_SCW_js_src($rel=false) {

	static $src;
	if (isset($src)) return $src;

	jbplug_do_callback('get_SCW_js_src', $src);
	if (strlen($src)>0) return $src;

	$base = 'include/lib/scw/scw_js.php';

	if ($rel) {
		$src = JB_get_relative_path($base);
		return $src;
	} else {
		$src = JB_BASE_HTTP_PATH.$base;
		return $src;
	}

}

function jb_get_WZ_dragdrop_js_src($rel=false) {

	static $src;
	if (isset($src)) return $src;

	jbplug_do_callback('get_WZ_dragdrop_js_src', $src);
	if (strlen($src)>0) return $src;

	$base = 'include/lib/wz_dragdrop/wz_dragdrop.js';

	if ($rel) {
		$src = JB_get_relative_path($base);
		return $src;
	} else {
		$src = JB_BASE_HTTP_PATH.$base;
		return $src;
	}

}

function jb_get_overlib_js_src($rel=false) {

	static $src;
	if (isset($src)) return $src;

	jbplug_do_callback('get_overlib_js_src', $src);
	if (strlen($src)>0) return $src;

	$base = 'include/lib/overlib/overlib.js';

	if ($rel) {
		$src = JB_get_relative_path($base);
		return $src;
	} else {
		$src = JB_BASE_HTTP_PATH.$base;
		return $src;
	}

}

function jb_get_menu_js_src($rel=false) {

	static $src;
	if (isset($src)) return $src;

	jbplug_do_callback('get_menu_js_src', $src);
	if (strlen($src)>0) return $src;

	$base = 'include/lib/menu/';

	if ($rel) {
		$src = JB_get_relative_path($base);
		return $src;
	} else {
		$src = JB_BASE_HTTP_PATH.$base;
		return $src;
	}

}




##############################################

function jb_get_employers_menu_path() {

	if (file_exists(JB_THEME_PATH."employer-menu.php")) {
		return JB_THEME_PATH."employer-menu.php";
	} else {
		return JB_DEFAULT_THEME_PATH."employer-menu.php";
	}


}
########################################
# Markup Classes
########################################



// The base markup class loader
function &JB_get_MarkupObject() {

	static $JBMarkup;  

	if (isset($JBMarkup)) return $JBMarkup;
	if (file_exists(JB_THEME_PATH.'JBMarkup.php')) {
		include (JB_THEME_PATH.'JBMarkup.php');
	} else {
		include (JB_DEFAULT_THEME_PATH.'JBMarkup.php');
	}
	$JBMarkup = new JBMarkup();
	
	return $JBMarkup;
}


function &JB_get_CategoryMarkupObject() {


	static $CategoryMarkup;  

	if (isset($CategoryMarkup)) return $CategoryMarkup;

	if (file_exists(JB_THEME_PATH."JBCategoryMarkup.php")) {
		include (JB_THEME_PATH."JBCategoryMarkup.php");
	} else {
		include (JB_DEFAULT_THEME_PATH."JBCategoryMarkup.php");
	}

	$CategoryMarkup = new JBCategoryMarkup();

	return $CategoryMarkup;


}
##############################################

function &JB_get_ListMarkupObject($arg='JBListMarkup') {

	static $ListMarkup;  

	
	if (is_numeric($arg)) { // $arg is a form_id
		
		switch ($arg) {

			case 1:
				$class_name = 'JBPostListMarkup';
				break;
			case 2:
				$class_name = 'JBResumeListMarkup';
				break;
			default:
				$class_name = 'JBListMarkup';
				
				break;
		}
	} else {
		$class_name = $arg;
	}

	JBPLUG_do_callback('set_ListMarkup_class_name', $class_name); // plugin authors can change the name of the class, and have their plugin subclass JBListMarkup and then set the $class_name to their sub class.


	if (isset($ListMarkup[$class_name])) return $ListMarkup[$class_name];


	if (file_exists(JB_THEME_PATH."JBListMarkup.php")) {
		include_once (JB_THEME_PATH."JBListMarkup.php");
	} else {
		include_once (JB_DEFAULT_THEME_PATH."JBListMarkup.php");
	}

	switch ($class_name) {

		case 'JBPostListMarkup':
			$ListMarkup[$class_name] = JB_get_PostListMarkupObject(); 
			break;
		case 'JBResumeListMarkup':
			$ListMarkup[$class_name] = JB_get_ResumeListMarkupObject();
			break;
		case 'JBAppListMarkup': 
			$ListMarkup[$class_name] = JB_get_AppListMarkupObject();
			break;
		case 'JBIframeListMarkup':
			$ListMarkup[$class_name] = JB_get_IframeListMarkupObject();
			break;
		case 'JBListMarkup':
			$ListMarkup[$class_name] = new JBListMarkup();
			break;
		case 'JBRequestListMarkup':
			$ListMarkup[$class_name] = new JBRequestListMarkup();
			break;
		case 'JBProductListMarkup':
			$ListMarkup[$class_name] = new JBProductListMarkup();
			break;
		case 'JBOrdersListMarkup':
			$ListMarkup[$class_name] = new JBOrdersListMarkup();
			break;
		case 'JBMembershipStatusMarkup':
			$ListMarkup[$class_name] = new JBMembershipStatusMarkup();
			break;
		case 'JBSubscriptionStatusMarkup':
			$ListMarkup[$class_name] = new JBSubscriptionStatusMarkup();
			break;
		case 'JBPaymentOptionListMarkup':
			$ListMarkup[$class_name] = new JBPaymentOptionListMarkup();
		default:
			JBPLUG_do_callback('get_ListMarkupObject', $ListMarkup, $class_name);
			if (!isset($ListMarkup[$class_name])) {
				$ListMarkup[$class_name] = new JBListMarkup();
			}
			break;

	}

	return $ListMarkup[$class_name];


}

########################################


function &JB_get_ResumeListMarkupObject() { 

	static $ResumeListMarkup;  
	if (isset($ResumeListMarkup)) return $ResumeListMarkup;

	// make sure that the parent is included
	if (file_exists(JB_THEME_PATH."JBListMarkup.php")) {
		include_once (JB_THEME_PATH."JBListMarkup.php");
	} else {
		include_once (JB_DEFAULT_THEME_PATH."JBListMarkup.php");
	}

	if (file_exists(JB_THEME_PATH."JBResumeListMarkup.php")) {
		include (JB_THEME_PATH."JBResumeListMarkup.php");
	} else {
		include (JB_DEFAULT_THEME_PATH."JBResumeListMarkup.php");
	}

	$ResumeListMarkup = new JBResumeListMarkup();

	return $ResumeListMarkup;


}

########################################


function &JB_get_PostListMarkupClass() { // alias for JB_get_ResumeListMarkupObject()
	return JB_get_PostListMarkupObject();
}

########################################

function &JB_get_PostListMarkupObject() {

	static $PostListMarkup; 
	
	if (isset($PostListMarkup)) return $PostListMarkup;

	// make sure that the parent is included
	if (file_exists(JB_THEME_PATH."JBListMarkup.php")) {
		include_once (JB_THEME_PATH."JBListMarkup.php");
	} else {
		include_once (JB_DEFAULT_THEME_PATH."JBListMarkup.php");
	}

	if (file_exists(JB_THEME_PATH."JBPostListMarkup.php")) {
		include (JB_THEME_PATH."JBPostListMarkup.php");
	} else {
		include (JB_DEFAULT_THEME_PATH."JBPostListMarkup.php");
	}
	$PostListMarkup = new JBPostListMarkup();
	return $PostListMarkup;


}

########################################


function &JB_get_JBIframeListMarkupObject() {

	static $IframeListMarkup; 
	
	if (isset($IframeListMarkup)) return $IframeListMarkup;

	// make sure that the parent is included
	if (file_exists(JB_THEME_PATH."JBListMarkup.php")) {
		include_once (JB_THEME_PATH."JBListMarkup.php");
	} else {
		include_once (JB_DEFAULT_THEME_PATH."JBListMarkup.php");
	}

	if (file_exists(JB_THEME_PATH."JBIframeListMarkup.php")) {
		include (JB_THEME_PATH."JBIframeListMarkup.php");
	} else {
		include (JB_DEFAULT_THEME_PATH."JBIframeListMarkup.php");
	}
	$JBIframeListMarkup = new JBIframeListMarkup();
	return $JBIframeListMarkup;


}

########################################

 
function &JB_get_SearchFormMarkupObject($form_id, $COLS=2) {

	static $SearchFormMarkup;

	if (isset($SearchFormMarkup[$form_id])) return $SearchFormMarkup[$form_id];

	if (file_exists(JB_THEME_PATH."JBSearchFormMarkup.php")) {
		include (JB_THEME_PATH."JBSearchFormMarkup.php");
	} else {
		include (JB_DEFAULT_THEME_PATH."JBSearchFormMarkup.php");
	}

	if (JB_SEARCH_FORM_LAYOUT=='TL') { // TL is Table Less layout

		// load in the sub-class
		if (file_exists(JB_THEME_PATH."JBSearchFormTLMarkup.php")) {
			include (JB_THEME_PATH."JBSearchFormTLMarkup.php");
		} else {
			include (JB_DEFAULT_THEME_PATH."JBSearchFormTLMarkup.php");
		}
		$SearchFormMarkup[$form_id] = new JBSearchFormTLMarkup($form_id, $COLS);

	} else {

		$SearchFormMarkup[$form_id] = new JBSearchFormMarkup($form_id, $COLS);

	}

	return $SearchFormMarkup[$form_id];


}
########################################


 
function &JB_get_DynamicFormMarkupObject($mode='view', $form_id=1) {

	static $DynamicFormMarkup;

	if (isset($DynamicFormMarkup[$form_id])) return $DynamicFormMarkup[$form_id];

	if (file_exists(JB_THEME_PATH.'JBDynamicFormMarkup.php')) {
		include (JB_THEME_PATH.'JBDynamicFormMarkup.php');
	} else {
		include (JB_DEFAULT_THEME_PATH.'JBDynamicFormMarkup.php');
	}

	$DynamicFormMarkup[$form_id] = new JBDynamicFormMarkup($mode, $form_id);

	return $DynamicFormMarkup[$form_id];


}
########################################

 
function &JB_get_AppMarkupObject() {

	static $JBAppMarkup;

	if (isset($JBAppMarkup)) return $JBAppMarkup;

	if (file_exists(JB_THEME_PATH."JBAppMarkup.php")) {
		include (JB_THEME_PATH."JBAppMarkup.php");
	} else {
		include (JB_DEFAULT_THEME_PATH."JBAppMarkup.php");
	}

	$JBAppMarkup = new JBAppMarkup();

	return $JBAppMarkup;
}

########################################
 
function &JB_get_AppListMarkupObject() {

	static $JBAppListMarkup;

	if (isset($JBAppListMarkup)) return $JBAppListMarkup;

	// make sure that the parent is included
	if (file_exists(JB_THEME_PATH."JBListMarkup.php")) {
		include_once (JB_THEME_PATH."JBListMarkup.php");
	} else {
		include_once (JB_DEFAULT_THEME_PATH."JBListMarkup.php");
	}

	if (file_exists(JB_THEME_PATH."JBAppListMarkup.php")) {
		include (JB_THEME_PATH."JBAppListMarkup.php");
	} else {
		include (JB_DEFAULT_THEME_PATH."JBAppListMarkup.php");
	}

	$JBAppListMarkup = new JBAppListMarkup();

	return $JBAppListMarkup;
}

########################################
function &get_JBMenuMarkup_object($type='') {

	static $JBMenuMarkup;

	if (isset($JBMenuMarkup[$type])) return $JBMenuMarkup[$type];

	if ($type == 'TABBED') {

		if (file_exists(JB_THEME_PATH."JBTabbedMenuMarkup.php")) {
			include_once (JB_THEME_PATH."JBTabbedMenuMarkup.php");
		} else {
			include_once (JB_DEFAULT_THEME_PATH."JBTabbedMenuMarkup.php");
		}

		$JBMenuMarkup[$type] = new JBTabbedMenuMarkup();

	} else {

		if (file_exists(JB_THEME_PATH."JBMenuMarkup.php")) {
			include_once (JB_THEME_PATH."JBMenuMarkup.php");
		} else {
			include_once (JB_DEFAULT_THEME_PATH."JBMenuMarkup.php");
		}

		$JBMenuMarkup[$type] = new JBMenuMarkup();

	}

	return $JBMenuMarkup[$type];
}



///////////////////////////
# Template functions for the theme

function JB_template_index_header() {
	global $label; global $JBMarkup;
	JBPLUG_do_callback('index_before_header', $A = false);
	if (file_exists(JB_THEME_PATH.'index-header.php')) {
		require(JB_THEME_PATH.'index-header.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'index-header.php');
	}
	JBPLUG_do_callback('index_after_header', $A = false);


}

#############################################
# Meata tags for the index header.
# must be called between the <head></head> tags
function JB_echo_index_meta_tags() {
	global $label;
	global $TITLE;
	global $JBMarkup;
	global $JBPage;


	/* How to customize the meta tags for individual pages?
	 * See include/classes/pages.php
	 * Since 3.6.8 these are implemented inside the specialized classes 
	 * in pages.php
	 * Each class has a custom header_tags() method 
	 * This method is registered with the $JBMarkup object, and the $JBMarkup
	 * ojects calls back the method when the header is rendered.
	 */

	if (!is_object($JBPage)) { 
		
		// If the page that is being displayed is not one
		// of the pages found in include/classes/pages.php
		// then perform the following default routine
	
		// here plugins can set their own title, description & keywords
		$my_TITLE = ''; $my_DESCRIPTION = ''; $my_KEYWORDS='';
		JBPLUG_do_callback('index_set_meta_title', $my_TITLE);
		JBPLUG_do_callback('index_set_meta_descr', $my_DESCRIPTION);
		JBPLUG_do_callback('index_set_meta_kwords', $my_KEYWORDS);	

		// output now

		if ($my_TITLE) {
			$JBMarkup->title_meta_tag($my_TITLE);
		}
		if ($my_DESCRIPTION) {
			$JBMarkup->meta_tag('description', $my_DESCRIPTION);
		}
		if ($my_KEYWORDS) {
			$JBMarkup->meta_tag('keywords', $my_KEYWORDS);
		}
	}

	JBPLUG_do_callback('index_extra_meta_tags', $A = false);

}




####################
function JB_template_index_footer() {
	global $label, $JBMarkup, $DynamicForm;

	if (is_object($DynamicForm)) {
		$prams = &$DynamicForm->get_values(); // older code compatibility, init $prams
	}

	JBPLUG_do_callback('index_before_footer', $A = false);
	if (file_exists(JB_THEME_PATH.'index-footer.php')) {
		require(JB_THEME_PATH.'index-footer.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'index-footer.php');
	}
	JBPLUG_do_callback('index_after_footer', $A = false);

}

####################

function JB_template_index_home() {
	global $label, $JBMarkup; 
	$label["candidate_join_now_link"] = str_replace ("%CANDIDATE_FOLDER%", JB_CANDIDATE_FOLDER , $label["candidate_join_now_link"]);
	$label["post_resume_link"] = str_replace ("%CANDIDATE_FOLDER%", JB_CANDIDATE_FOLDER , $label["post_resume_link"]);
	$label["post_resume_link"] = str_replace ("%SITE_NAME%", jb_escape_html(JB_SITE_NAME) , $label["post_resume_link"]);
	JBPLUG_do_callback('index_before_home', $A = false);
	if (file_exists(JB_THEME_PATH.'index-home.php')) {
		require(JB_THEME_PATH.'index-home.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'index-home.php');
	}
	JBPLUG_do_callback('index_after_home', $A = false);

}
####################
function JB_template_index_search_result() {
	global $label, $JBMarkup, $JBPage;
	if (is_object($JBPage)) {
		extract($JBPage->get_vars(), EXTR_REFS);
	}
	JBPLUG_do_callback('index_before_search_result', $A = false);
	if (file_exists(JB_THEME_PATH.'index-search-result.php')) {
		require(JB_THEME_PATH.'index-search-result.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'index-search-result.php');
	}
	JBPLUG_do_callback('index_after_search_result', $A = false);

}
####################
function JB_template_index_premium_list() {
	global $label, $JBMarkup, $JBPage;
	if (is_object($JBPage)) {
		extract($JBPage->get_vars(), EXTR_REFS);
	}
	JBPLUG_do_callback('index_before_premium_list', $A = false);
	if (file_exists(JB_THEME_PATH.'index-premium-list.php')) {
		require(JB_THEME_PATH.'index-premium-list.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'index-premium-list.php');
	}
	JBPLUG_do_callback('index_after_premium_list', $A = false);

}
####################
function JB_template_index_employer() {

	global $label, $JBMarkup, $JBPage;

	// this is where we extract the variables from the page to be used in the template
	if (is_object($JBPage)) {
		extract($JBPage->get_vars(), EXTR_REFS);
		$prams = &$DynamicForm->get_values(); // older code compatibility, init $prams with posting data
	}

	JBPLUG_do_callback('index_before_employer', $A = false);
	if (file_exists(JB_THEME_PATH.'index-employer.php')) {
		require(JB_THEME_PATH.'index-employer.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'index-employer.php');
	}
	JBPLUG_do_callback('index_after_employer', $A = false);

}

####################
function JB_template_index_sidebar() {
	global $label, $JBMarkup;

	JBPLUG_do_callback('index_before_sidebar', $A = false);
	if (file_exists(JB_THEME_PATH.'index-sidebar.php')) {
		require(JB_THEME_PATH.'index-sidebar.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'index-sidebar.php');
	}
	JBPLUG_do_callback('index_after_sidebar', $A = false);
	

}

####################

function JB_template_category_list_box($JB_CAT_COLS = JB_CAT_COLS_FP) {
	global $label, $JBMarkup;
	JBPLUG_do_callback('index_before_category_list_box', $A = false);
	if (file_exists(JB_THEME_PATH.'category-list-box.php')) {
		require(JB_THEME_PATH.'category-list-box.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'category-list-box.php');
	}
	JBPLUG_do_callback('index_after_category_list_box', $A = false);


}

####################

function JB_template_index_category($JB_CAT_COLS = JB_CAT_COLS) {

	global $label, $JBMarkup, $JBPage;

	// this is where we extract the variables from the page to be used in the template
	if (is_object($JBPage)) {
		extract($JBPage->get_vars(), EXTR_REFS);
	}

	$label['go_to_site_home'] = str_replace ("%SITE_NAME%", jb_escape_html(JB_SITE_NAME), $label['go_to_site_home']);
	JBPLUG_do_callback('index_before_category', $A = false);

	
	$label['rss_subscribe'] = str_replace ('%RSS_LINK%', JB_BASE_HTTP_PATH."rss.php?cat=".jb_escape_html($_REQUEST['cat']), $label['rss_subscribe']);
	$label['rss_subscribe'] = str_replace ('%CATEGORY_NAME%', jb_escape_html($CAT_NAME), $label['rss_subscribe']);

	if (file_exists(JB_THEME_PATH.'index-category.php')) {
		require(JB_THEME_PATH.'index-category.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'index-category.php');
	}
	JBPLUG_do_callback('index_after_category', $A = false);


}
####################

function JB_template_display_post($display_mode='FULL') {
	global $label;
	global $JBMarkup;
	global $JBPage;
	

    if (is_object($JBPage)) {
		// this is where we extract the variables from the page to be used in the template
		extract($JBPage->get_vars(), EXTR_REFS);
		$prams = &$DynamicForm->get_values(); // older code compatibility, init $prams
	}


	// Javascript for the map pin 
	if (JB_MAP_DISABLED!='YES') {
		?>
		<script type="text/javascript" src="<?php echo jb_get_WZ_dragdrop_js_src(); ?>"></script>
		<?php
		if (JB_MAP_DISABLED!='YES') {
		?>
		<script type="text/javascript">
		// re-calculate the map position
		dd.recalc();
		</script>
		<?php
		} 
		
	}

	if (($APP==true) && (!$JBMarkup->head_opened)) { 
		// This is here so that older templates (before 3.6) are compatible with 3.6+
		// This block was moved to JBMarkup.php
		// !$JBMarkup->head_opened means that the <head> was not opened yet.
		// Latest verion of the default theme uses code from $JBMarkup->head_open()
		?>
		<script type="text/javascript">
		function showDIV(obj, source, bool) {
			obj.setAttribute("style", "display: none", 0);
			if (bool == false) {
			  
			//obj.style.visibility = "hidden";
			document.getElementById (source).innerHTML=document.getElementById('app_form').innerHTML;
			document.getElementById ('app_form').innerHTML=document.getElementById('app_form_blank').innerHTML;
			}
			else {
			 
			  obj.innerHTML =
			  document.getElementById(source).innerHTML;
			  obj.setAttribute("style", "display: block", 0);

			 
			}
			<?php
			// php code
			if (JB_MAP_DISABLED!='YES') {
			?>
			// re-calculate the map position!
			dd.recalc();
			<?php
			} // end php if
			?>

			return bool;

		}
		</script>
	<?php } 
		
	JBPLUG_do_callback('display_post_before', $A = false);
	if (file_exists(JB_THEME_PATH.'display-post.php')) {
		require(JB_THEME_PATH.'display-post.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'display-post.php');
	}
	JBPLUG_do_callback('display_post_after', $A = false);


}
####################
function JB_get_maincss_url() {
	
	if (file_exists(JB_THEME_PATH.'main.css')) {
		
		return JB_THEME_URL.'main.css';
	} else {
		return JB_DEFAULT_THEME_URL.'main.css';
	}

}

####################
function JB_get_admin_maincss_url() {
	if (file_exists(JB_THEME_PATH.'main-admin.css')) {
		return JB_THEME_URL.'main-admin.css';
	} else {
		return JB_DEFAULT_THEME_URL.'main-admin.css';
	}

}
#####################
function JB_get_menucss_url() {

	if (file_exists(JB_THEME_PATH.'js-menu.css')) {
		return JB_THEME_URL.'js-menu.css';
	} else {
		return JB_DEFAULT_THEME_URL.'js-menu.css';
	}


}
#####################

function JB_get_text_menucss_url() {

	if (file_exists(JB_THEME_PATH.'text-menu.css')) {
		return JB_THEME_URL.'text-menu.css';
	} else {
		return JB_DEFAULT_THEME_URL.'text-menu.css';
	}


}

#####################

function JB_get_employerscss_url() {

	if (file_exists(JB_THEME_PATH.'employers.css')) {
		return JB_THEME_URL.'employers.css';
	} else {
		return JB_DEFAULT_THEME_URL.'employers.css';
	}


}

function JB_get_candidatescss_url() {

	if (file_exists(JB_THEME_PATH.'candidates.css')) {
		return JB_THEME_URL.'candidates.css';
	} else {
		return JB_DEFAULT_THEME_URL.'candidates.css';
	}


}



##########################
# Generates javascipt that positions the pin on the map
function JB_echo_map_pin_position_js ($pin_x, $pin_y) {

	
	$pin_z = $pin_x+$pin_y;
	if (!$pin_z) return;
	$map_size = getimagesize(jb_get_map_img_path());
	$pin_size = getimagesize(jb_get_pin_img_path());
	
	$right = ($map_size[0]-$pin_size[0])-$pin_x; // map_x - pin_x
	$bottom = $map_size[1]-$pin_y;
	if ($pin_y == '' ) {
	   $pin_y=0;
	}
	if ($pin_x == '' ) {
	   $pin_x=0;
	}
	?>
	<img border="1" name="pin" alt="pin" src="<?php echo jb_get_pin_img_url(); ?>" <?php $size=getimagesize(jb_get_pin_img_path()) ?> width="<?php echo $size[0]; ?>" height="<?php echo $size[1]; ?>">

	<script type="text/javascript">
	
	SET_DHTML("pin"+MAXOFFLEFT+<?php echo $pin_x+$pin_size[0]; ?>+MAXOFFRIGHT+<?php echo $right;?>+MAXOFFBOTTOM+<?php echo $bottom;?>+MAXOFFTOP+<?php echo $pin_y; ?>+CURSOR_HAND+NO_DRAG,"map"+NO_DRAG);

	<?php
	if ($pin_x != '') {
	   echo "dd.elements.pin.moveTo(dd.elements.map.x+$pin_x, dd.elements.map.y+$pin_y); ";
	} else {
	?>
		dd.elements.pin.moveTo(dd.elements.map.x, dd.elements.map.y); 
	<?php } ?>
	dd.elements.pin.setZ(dd.elements.pin.z+1); 
	dd.elements.map.addChild("pin"); 
	
	</script>
	<?php


}

###################

function JB_template_candidates_header() {
	global $label, $JBMarkup;

	$JBMarkup->menu_type = JB_CANDIDATE_MENU_TYPE; // can be JS or TXT

	JBPLUG_do_callback('candidates_header_before', $A = false);
	if (file_exists(JB_THEME_PATH.'candidates-header.php')) {
		require(JB_THEME_PATH.'candidates-header.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'candidates-header.php');
	}
	JBPLUG_do_callback('candidates_header_after', $A = false);


}

######################

function JB_template_candidates_footer() {
	global $label, $JBMarkup, $JBPage;

	if (is_object($JBPage)) {
		extract($JBPage->get_vars(), EXTR_REFS);
		$prams = &$DynamicForm->get_values(); // older code compatibility, init $prams
	}

	

	JBPLUG_do_callback('candidates_footer_before', $A = false);
	if (file_exists(JB_THEME_PATH.'candidates-footer.php')) {
		require(JB_THEME_PATH.'candidates-footer.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'candidates-footer.php');
	}
	JBPLUG_do_callback('candidates_footer_after', $A = false);


}

##########################

function JB_template_candidates_outside_header($page_title) {
	global $label, $JBMarkup;
	
	$JBMarkup->menu_type = JB_CANDIDATE_MENU_TYPE; // can be JS or TXT

	JBPLUG_do_callback('candidates_outside_header_before', $A = false);
	if (file_exists(JB_THEME_PATH.'candidates-outside-header.php')) {
		require(JB_THEME_PATH.'candidates-outside-header.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'candidates-outside-header.php');
	}
	JBPLUG_do_callback('candidates_outside_header_after', $A = false);


}

######################

function JB_template_candidates_outside_footer() {
	global $label, $JBMarkup;
	JBPLUG_do_callback('candidates_outside_footer_before', $A = false);
	if (file_exists(JB_THEME_PATH.'candidates-outside-footer.php')) {
		require(JB_THEME_PATH.'candidates-outside-footer.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'candidates-outside-footer.php');
	}
	JBPLUG_do_callback('candidates_outside_footer_after', $A = false);


}

###########################

function JB_template_employers_header() {
	global $label, $JBMarkup;

	$JBMarkup->menu_type = JB_EMPLOYER_MENU_TYPE; // can be JS or TXT

	JBPLUG_do_callback('employers_header_before', $A = false);
	if (file_exists(JB_THEME_PATH.'employers-header.php')) {
		require(JB_THEME_PATH.'employers-header.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'employers-header.php');
	}
	JBPLUG_do_callback('employers_header_after', $A = false);
}

###########################

function JB_template_employers_footer() {
	global $label, $JBMarkup;
	
	JBPLUG_do_callback('employers_footer_before', $A = false);
	if (file_exists(JB_THEME_PATH.'employers-footer.php')) {
		require(JB_THEME_PATH.'employers-footer.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'employers-footer.php');
	}
	JBPLUG_do_callback('employers_footer_after', $A = false);


}
##########################

function JB_template_employers_outside_header($page_title) {
	global $label, $JBMarkup;

	$JBMarkup->menu_type = JB_EMPLOYER_MENU_TYPE; // can be JS or TXT

	JBPLUG_do_callback('employers_outside_header_before', $A = false);
	if (file_exists(JB_THEME_PATH.'employers-outside-header.php')) {
		require(JB_THEME_PATH.'employers-outside-header.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'employers-outside-header.php');
	}
	JBPLUG_do_callback('employers_outside_header_after', $A = false);


}

######################

function JB_template_employers_outside_footer() {
	global $label, $JBMarkup;
	JBPLUG_do_callback('employers_outside_footer_before', $A = false);
	if (file_exists(JB_THEME_PATH.'employers-outside-footer.php')) {
		require(JB_THEME_PATH.'employers-outside-footer.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'employers-outside-footer.php');
	}
	JBPLUG_do_callback('employers_outside_footer_after', $A = false);

}

######################

function JB_template_info_box_top($width, $heading, $body_bg_color) {
	global $label, $JBMarkup;
	$content = $heading;
	JBPLUG_do_callback('info_box_top_before', $A = false);
	if (file_exists(JB_THEME_PATH.'info-box-top.php')) {
		require(JB_THEME_PATH.'info-box-top.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'info-box-top.php');
	}
	JBPLUG_do_callback('info_box_top_after', $A = false);
}

#######################

function JB_template_info_box_bottom() {
	global $label, $JBMarkup;
	JBPLUG_do_callback('info_box_bot_before', $A = false);
	if (file_exists(JB_THEME_PATH.'info-box-bottom.php')) {
		require(JB_THEME_PATH.'info-box-bottom.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'info-box-bottom.php');
	}
	JBPLUG_do_callback('info_box_bot_after', $A = false);

}

#######################

function JB_template_posting_form($mode, $admin) {
	global $label, $JBMarkup;

	$_REQUEST['post_id'] = (int) $_REQUEST['post_id'];
	$DynamicForm = &JB_get_DynamicFormObject(1);
	$prams = &$DynamicForm->get_values(); // older code compatibility, init $prams
	$error = $DynamicForm->get_error_msg();
	JBPLUG_do_callback('posting_form_before', $A = false);
	if (file_exists(JB_THEME_PATH.'posting-form.php')) {
		require(JB_THEME_PATH.'posting-form.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'posting-form.php');
	}
	JBPLUG_do_callback('posting_form_after', $A = false);

}

#######################

function JB_template_resume_form(&$mode, $admin) {
	global $label, $JBMarkup;
	$_REQUEST['resume_id'] = (int) $_REQUEST['resume_id'];
	$DynamicForm = &JB_get_DynamicFormObject(2);
	$prams = &$DynamicForm->get_values(); // older code compatibility, init $prams
	$error = $DynamicForm->get_error_msg();
	JBPLUG_do_callback('resume_form_before', $A = false);
	if (file_exists(JB_THEME_PATH.'resume-form.php')) {
		require(JB_THEME_PATH.'resume-form.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'resume-form.php');
	}
	JBPLUG_do_callback('resume_form_after', $A = false);

}

#######################

function JB_template_profile_form(&$mode, $admin) {
	global $label, $JBMarkup;
	$_REQUEST['resume_id'] = (int) $_REQUEST['profile_id'];
	$DynamicForm = &JB_get_DynamicFormObject(3);
	$prams = &$DynamicForm->get_values(); // older code compatibility, init $prams
	$error = $DynamicForm->get_error_msg();
	JBPLUG_do_callback('profile_form_before', $A = false);
	if (file_exists(JB_THEME_PATH.'profile-form.php')) {
		require(JB_THEME_PATH.'profile-form.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'profile-form.php');
	}
	JBPLUG_do_callback('profile_form_after', $A = false);
}

#######################

function JB_template_employer_signup_form($mode, $admin, $user_id) {
	global $label, $q_string, $JBMarkup;
	$DynamicForm = &JB_get_DynamicFormObject(4);
	$error = $DynamicForm->get_error_msg();
	$prams = &$DynamicForm->get_values(); // older code compatibility, init $prams
	JBPLUG_do_callback('emp_signup_form_before', $A = false);
	if (file_exists(JB_THEME_PATH.'employer-signup-form.php')) {
		require(JB_THEME_PATH.'employer-signup-form.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'employer-signup-form.php');
	}
	JBPLUG_do_callback('emp_signup_form_after', $A = false);

}

##########################

function JB_template_candidate_signup_form($mode, $admin, $user_id) {
	global $label, $q_string, $JBMarkup;
	$DynamicForm = &JB_get_DynamicFormObject(5);
	$error = $DynamicForm->get_error_msg();
	$prams = &$DynamicForm->get_values(); // older code compatibility, init $prams
	JBPLUG_do_callback('can_signup_form_before', $A = false);
	if (file_exists(JB_THEME_PATH.'candidate-signup-form.php')) {
		require(JB_THEME_PATH.'candidate-signup-form.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'candidate-signup-form.php');
	}
	JBPLUG_do_callback('can_signup_form_after', $A = false);

}

##########################

function JB_template_application_form($post_id, $app_name, $app_email, $app_subject, $app_letter, $att1, $att2, $att3) {
	global $label, $error, $JBMarkup;


	// Need to access the job posting data? It has already been loaded
	// in to memory. Here is an example forhow to do it:
	// $DynamicForm = &JB_get_DynamicFormObject(1);
	// print_r($DynamicForm->get_values());
	

	JBPLUG_do_callback('app_form_before', $A = false);

	$member_ignore_premium = false;
	if (JB_MEMBER_FIELD_SWITCH=='YES') {
		if (JB_MEMBER_FIELD_IGNORE_PREMIUM=='YES') { // ignore membership only field and allow applications
			$PForm = &JB_get_DynamicFormObject(1);
			if ($PForm->get_value('post_mode') == 'premium') {
				$member_ignore_premium=true; 
			}
		}
	}

	// if membership fields are blocked, and if membership billing for candidates is YES
	// and membership not acive
    // (The membership fields are blocked when JB_MEMBER_FIELD_SWITCH=='YES')
	// 
	if ((!$member_ignore_premium) && (JB_ONLINE_APP_SIGN_IN=='YES') && (JB_CANDIDATE_MEMBERSHIP_ENABLED=='YES') && (!JB_is_candidate_membership_active($_SESSION['JB_ID']))) {
		
		$label['app_member_only'] = str_replace ('%MEMBERSHIP_URL%', JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER.'membership.php', $label['app_member_only']);

		$JBMarkup->ok_msg($label['app_member_only']);
		
	} else {

		$app_name = stripslashes($app_name);
		$app_email = stripslashes($app_email);
		$app_subject = stripslashes($app_subject);
		$app_letter = stripslashes($app_letter);

		if (file_exists(JB_THEME_PATH.'application-form.php')) {
			require(JB_THEME_PATH.'application-form.php');
		} else {
			require(JB_DEFAULT_THEME_PATH.'application-form.php');
		}
	}
	JBPLUG_do_callback('app_form_after', $A = false);

}

##########################

function JB_template_employer_request_form($from, $reply_to) {
	global $label, $error, $JBMarkup;
	JBPLUG_do_callback('request_form_before', $A = false);
	if (file_exists(JB_THEME_PATH.'employer-request-form.php')) {
		require(JB_THEME_PATH.'employer-request-form.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'employer-request-form.php');
	}
	JBPLUG_do_callback('request_form_after', $A = false);

}

################################

function JB_template_employer_email_form($post_id, $c_name, $c_email, $email_subject, $email_letter) {
	global $label, $error, $JBMarkup;
	JBPLUG_do_callback('email_form_before', $A = false);
	if (file_exists(JB_THEME_PATH.'employer-email-form.php')) {
		require(JB_THEME_PATH.'employer-email-form.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'employer-email-form.php');
	}
	JBPLUG_do_callback('email_form_after', $A = false);

}

################################

function JB_template_employer_login_form() {
	global $label, $JBMarkup;
	JBPLUG_do_callback('emp_login_form_before', $A = false);
	if (JBPLUG_do_callback('emp_login_form_replace', $A = false)==false) {
		if (file_exists(JB_THEME_PATH.'employer-login-form.php')) {
			require(JB_THEME_PATH.'employer-login-form.php');
		} else {
			require(JB_DEFAULT_THEME_PATH.'employer-login-form.php');
		}
	}
	JBPLUG_do_callback('emp_login_form_after', $A = false);

}

################################


function JB_template_employer_login() {
	global $label, $JBMarkup;
	JBPLUG_do_callback('emp_login_before', $A = false);
	if (JBPLUG_do_callback('emp_login_replace', $A = false)==false) {
		if (file_exists(JB_THEME_PATH.'employer-login.php')) {
			require(JB_THEME_PATH.'employer-login.php');
		} else {
			require(JB_DEFAULT_THEME_PATH.'employer-login.php');
		}
	}
	JBPLUG_do_callback('emp_login_after', $A = false);

}

################################

function JB_template_candidate_login_form($action) {
	global $label, $JBMarkup;
	$label["app_please_log_in"] = str_replace("%CANDIDATE_FOLDER%",JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER, $label["app_please_log_in"]);
	JBPLUG_do_callback('can_login_form_before', $A = false);
	if (JBPLUG_do_callback('can_login_form_replace', $A = false)==false) {
		if (file_exists(JB_THEME_PATH.'candidate-login-form.php')) {
			require(JB_THEME_PATH.'candidate-login-form.php');
		} else {
			require(JB_DEFAULT_THEME_PATH.'candidate-login-form.php');
		}
	}
	JBPLUG_do_callback('can_login_form_after', $A = false);

}

##########################################################

function JB_template_candidate_login() {
	global $label, $JBMarkup;
	JBPLUG_do_callback('can_login_before', $A = false);
	if (JBPLUG_do_callback('can_login_replace', $A = false)==false) {
		if (file_exists(JB_THEME_PATH.'candidate-login.php')) {
			require(JB_THEME_PATH.'candidate-login.php');
		} else {
			require(JB_DEFAULT_THEME_PATH.'candidate-login.php');
		}
	}
	JBPLUG_do_callback('can_login_after', $A = false);

}

###############################
/*
The 'tell a friend' form
*/
function JB_template_email_job() {

	global $label, $error, $post_id, $your_email, $your_name, $to_email, $message, $to_email,  $JBMarkup;
	JBPLUG_do_callback('email_job_before', $A = false);

	require_once (dirname(__FILE__)."/posts.inc.php");

	$JobListAttributes = new JobListAttributes();
	$JobListAttributes->clear();


	if (file_exists(JB_THEME_PATH.'email-job-form.php')) {
		require(JB_THEME_PATH.'email-job-form.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'email-job-form.php');
	}
	JBPLUG_do_callback('email_job_after', $A = false);



}

function JB_template_employers_forget_pass_form() {

	global $label, $submit, $email, $JBMarkup;
	JBPLUG_do_callback('emp_forget_pass_form_before', $A = false);
	if (file_exists(JB_THEME_PATH.'employers-forget-pass-form.php')) {
		require(JB_THEME_PATH.'employers-forget-pass-form.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'employers-forget-pass-form.php');
	}
	JBPLUG_do_callback('emp_forget_pass_form_after', $A = false);



}

function JB_template_candidates_forget_pass_form() {

	global $label, $error, $post_id, $your_email, $your_name, $to_email, $message, $JBMarkup;
	JBPLUG_do_callback('can_forget_pass_form_before', $A = false);
	if (file_exists(JB_THEME_PATH.'candidates-forget-pass-form.php')) {
		require(JB_THEME_PATH.'candidates-forget-pass-form.php');
	} else {
		require(JB_DEFAULT_THEME_PATH.'candidates-forget-pass-form.php');
	}
	JBPLUG_do_callback('can_forget_pass_form_after', $A = false);



}



?>