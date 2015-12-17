<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
##############################################################
# Write the url to the job post
# Is able to write a SEO URL if the feature is enabled
# $post_id = job post id
# ListAttributes


function JB_job_post_url($post_id, $JobListAttributes='', $self = '') {
	$post_id = (int) $post_id;
	
	// If the link is shown from a job listing page
	// Then some additional attributes are appended to the
	// url's query string. JobListAttributes object encapsulates
	// the additional query string attributes
	if (!$JobListAttributes) {
		$JobListAttributes = new JobListAttributes();
	}

	if ((JB_JOB_MOD_REWRITE == 'YES') && (!$JobListAttributes->is_internal_page()))  {
		
		$parts = explode('/', JB_MOD_REWRITE_JOB_DIR);
		$slash = '';
		$PForm = JB_get_DynamicFormObject(1);

		// Process the search friendly link according to the 
		// template specified in the main config
		// Substitute template tags with their values;
		// Eg. job/%DATE%/ - where the %DATE% will be changed to the date when the job was posted.
		foreach ($parts as $part) {
			if (strpos($part, '%', 0)!==false) {
				// remove the leading and trailing chars % 
				$template_tag = substr($part, 1, strlen($part)-2);
				// get the value of the template tag

				$part = $PForm->get_raw_template_value($template_tag); 
				if ($PForm->get_template_tag_attribute($template_tag, 'field_type')=='TIME') {
					$time = strtotime($part);
					$part = date('Y-m-d', $time); // ISO date for URLs
	
				} else {
					$part = jb_format_url_string($part);
				}
			}

			$dir .= $slash.$part;

			$slash = "/";

		}

		return htmlentities(JB_BASE_HTTP_PATH).$dir.$post_id.$JobListAttributes->get_query_string('?');
	} else {

		// no mod_rewrite
		if ($self=='') { // for rss feeds $self would not be blank
			$self = $_SERVER['PHP_SELF'];
		}
		return htmlentities($self).'?post_id='.$post_id.$JobListAttributes->get_query_string('&amp;');
	}


}

####################################
# Write the URL to the employer's profile page
# themes/default/index-employer.php

function JB_emp_profile_url($employer_id, $JobListAttributes='', $self = '') {
	
	$employer_id = (int) $employer_id;

	if (!$JobListAttributes) {
		// If the link is shown from a job listing page
		// Then some additional attributes are appended to the
		// url's query string. JobListAttributes object encapsulates
		// the additional query string attributes
		$JobListAttributes = new JobListAttributes();
	}

	if ((JB_PRO_MOD_REWRITE == 'YES') && (!$JobListAttributes->is_internal_page()))  {
		return  htmlentities(JB_BASE_HTTP_PATH).JB_MOD_REWRITE_PRO_DIR.$employer_id;
		
	} else {

		// no mod_rewrite
		if ($self=='') { // for rss feeds $self would not be blank
			$self = $_SERVER['PHP_SELF'];
		}
		
		return htmlentities($self).'?show_emp='.$employer_id;
	}


}

####################################
# Write the URL to the category page
# themes/default/index-employer.php



#######################################
if (!defined('JB_MOD_REWRITE_DIR')) {
	define ('JB_MOD_REWRITE_DIR', 'category/');
}
$JB_CAT_MOD_REWRITE = JB_CAT_MOD_REWRITE;

function JB_cat_url_write($cat, $name, $fname='', $self = '') {
	$cat = (int) $cat;
	global $JB_CAT_MOD_REWRITE;
	if ($JB_CAT_MOD_REWRITE=='YES') {

		// do not rewrite for any of the internal pages
		if ((strpos($_SERVER['PHP_SELF'], 'browse.php')!==false) || (strpos($_SERVER['PHP_SELF'], 'search.php')!==false) || (strpos($_SERVER['PHP_SELF'], 'resume.php')!==false) || (strpos($_SERVER['PHP_SELF'], 'resumes.php')!==false) || (strpos($_SERVER['PHP_SELF'], 'posts.php')!==false)) {
			// do not rewrite url when in candidate's area or when in Admin
			return htmlentities($_SERVER['PHP_SELF'])."?cat=".$cat;
		}
		if ($fname=='') { // If a custom file name (slug) was not given, 
						  // then Morph the category name in to a file name

			$fname = jb_format_url_string($name);
			$fname .= '.html';


		} else {
			$fname = urlencode(JB_html_ent_to_utf8(strtolower($fname)));

		}

		return JB_BASE_HTTP_PATH.JB_MOD_REWRITE_DIR.$fname;

	} else {

		// no mod_rewrite

		
		if ($self=='') { // for rss feeds $self would not be blank
			// init $self
			// if viewing form a plugin page, fix up links to point to index.php
			$self = str_replace('p.php', 'index.php', $_SERVER['PHP_SELF']);
			$self = str_replace ('//', '/', $self);
		}
		
		return htmlentities($self)."?cat=$cat";

	}

}

################################################################

// Write page1 page1 '<- Previous' and 'Next ->' links for the Job List
function JB_job_result_page_url($offset, $posts_per_page, $is_premium=false, &$JobListAttributes, $self = '') {

	global $PREMIUM_LIST, $JOB_LIST_PAGE;

	if (!$JobListAttributes) {
		// If the link is shown from a job listing page
		// Then some additional attributes are appended to the
		// url's query string. JobListAttributes object encapsulates
		// the additional query string attributes
		$JobListAttributes = new JobListAttributes();
	}
	
	$page = ($offset / $posts_per_page) +1;
	$page_name = htmlentities($_SERVER['PHP_SELF']);

	if (($_REQUEST['show_emp']!='') && ($page==1)) {
		//it should go back to the 'listing jobs by advertiser' main page
		//clear the list attributes
		
		$blankJobListAttributes = new JobListAttributes();
		$blankJobListAttributes->clear();
		return JB_emp_profile_url($_REQUEST['show_emp'], $blankJobListAttributes);

	} elseif (($page==1) && ($JOB_LIST_PAGE || $PREMIUM_LIST )) {
		// If first page is a premium list or job list, 
		// then 1st page link will go back to the home page
		$page_name = str_replace ('index.php', '', $page_name);
		return $page_name;
	} else {
		if ((JB_JOB_PAGES_MOD_REWRITE == 'YES') && ($is_premium==false) && (!$JobListAttributes->is_internal_page())) {
			return htmlentities(JB_BASE_HTTP_PATH).JB_MOD_REWRITE_JOB_PAGES_PREFIX.$page.$JobListAttributes->get_nav_query_string('?');
		} else {
			return htmlentities($page_name).'?offset='.$offset.$JobListAttributes->get_nav_query_string('&amp;');
		}

	}

}

 

################################################################

# Because the categories do not have the IDs encoded in them
# The ID needs to be fetched form the database given the category
# name
# Assumes $cat_name is utf encoded
function JB_get_cat_id_from_url($cat_name, $form_id=1) {
	
	$cat_name = JB_utf8_to_html($cat_name);
	// first, serach the category for a direct seo_fname hit
	$sql = "select category_id from categories where seo_fname='".jb_escape_sql($cat_name)."' AND form_id='".jb_escape_sql($form_id)."' ";
	//$sql = "select category_id from categories where seo_fname='secrétaireàparis.htm' AND form_id='1'";


	$result = JB_mysql_query($sql);

	if (mysql_num_rows($result) == 1) { // we have a match!
		$row = mysql_fetch_row($result);
		return $row[0]; // return category_id
	}
	// no match, generate it...
	$sql = get_cat_rewrite_sql($cat_name, $form_id);

	$result = JB_mysql_query($sql);
	if (mysql_num_rows($result)!=1) { // the url is ambiguous or wrong
		return false;
	}
	$row = mysql_fetch_row($result);
	return $row[0]; // return category_id
}

# build the sql query to match a pattern for the $cat_name if the seo_fname is not present

function get_cat_rewrite_sql($cat_name, $form_id=1) {

	/*

	How to test this:
	- Go to Admin->Edit Categories, change the category name to include non-English characters
	- Go to Admin->Mod rewrite, ensure that the 'Path / File:' setting is blank and hit save

	*/
	
	//$cat_name = JB_utf8_to_html($cat_name);
	$cat_name = utf8_decode($cat_name);
	$cat_name = str_replace('-', '%', $cat_name);
	$cat_name = str_replace(".html", "", $cat_name);

	
	$sql = "select category_id from categories where category_name like '".jb_escape_sql($cat_name)."%' AND form_id='".jb_escape_sql($form_id)."' ";

	return $sql;
}

##################################################
# Process job post permalink to get the post_id
# Working form the value in $_POST['post_id']
# And also pupulate any additional variables on to
# the $_REQUEST global
# include/posts.inc.php needs to be included
# before calling this function

function JB_process_job_post_permalink() {

	//global $post_tag_to_field_id; # intialized in include/posts.inc.php
	//global $post_tag_to_search; # intialized in include/posts.inc.php

	// The last digits digit of the directory path should be the post id.
	// match the last digits
	preg_match ('#\d+$#D', $_REQUEST['post_id'], $m);
	// If the post_id is not present then force the job board in
	// to doing a search.
	//
	// The idea is to parse the JB_MOD_REWRITE_JOB_DIR string
	// which may contain additional url elements
	// These url elements are the template tags which are mapped to
	// the columns in the posts_table
	// The following code simply converts these template tags
	// in to the corresponding column names, and then places
	// the column names with their corresponding value on 
	// the request $_REQUEST array to force a search.


	
	$m[0] = (int) $m[0]; 
	if ($m[0] > 0) { 
		$_REQUEST['post_id'] = $m[0];
	} else { 
		
		// No post_id found in the url. Force the job board to do a search

		// This is a nice little feature
		// which takes the slug stored in post_id, breaks up the slug in to
		// an array of data items.
		// Then the JB_MOD_REWRITE_JOB_DIR is broken down in to an array of 
		// template tags. 
		// These two arrays are then combined to fill the $_REQUEST input
		// as if the user performed a search. The job board will then be
		// forced to execute a search.

		// Limitation: Only template tags which are also on the search form 
		// will be searched. Or you can uncomment the line with
		// $PForm->add_tag_to_search() below.

		$PForm = JB_get_DynamicFormObject(1);


		$dir = $_REQUEST['post_id'];
		$parts = explode('/', $dir);
		$i=0;

		foreach ($parts as $part) {
			// decode utf8 to html entities
			$data[$i] =  (JB_utf8_to_html($part));
			$i++;
		}
		
		
		$parts = explode('/', JB_MOD_REWRITE_JOB_DIR);
		
		$i=0;
		$fields = array();
		foreach ($parts as $part) {
			if (!$data[$i]) {
				continue;
			}
			if (strpos($part, '%', 0)!==false) {
				$template_tag = substr($part, 1, strlen($part)-2);
				
				// get the column name in the table and add to $fields array
			
				$field_id = $PForm->get_template_tag_attribute($template_tag, 'field_id');
				$field_type = $PForm->get_template_tag_attribute($template_tag, 'field_type');

				//$PForm->add_tag_to_search($template_tag, array ('field_id' => $field_id, 'field_type' => $field_type));
				
				// - The codes for the Categories and radio buttons values need to
				// looked up

				//echo "fieldid: $field_id, field type: $field_type $template_tag : ".$data[$i].'<br>';
				switch ($field_type) {
					case 'CATEGORY':
						$data[$i] = str_replace ('-', '%', $data[$i]); // change to sql wild-card
						$sql ="SELECT *  FROM `cat_name_translations` WHERE `lang` = '".jb_escape_sql($_SESSION['LANG'])."' AND `category_name` LIKE '".jb_escape_sql($data[$i])."'";
						
						$result = jb_mysql_query($sql);
						$row = mysql_fetch_array($result, MYSQL_ASSOC);
						$data[$i] = $row['category_id'];
						break;
					case 'SELECT':
					case 'RADIO':
						$data[$i] = str_replace ('-', '%', $data[$i]); // change to sql wild-card
						$sql = "SELECT code FROM code_translations WHERE description LIKE '".jb_escape_sql($data[$i])."' and field_id='$field_id' AND `lang`='".jb_escape_sql($_SESSION['LANG'])."' ";
						
						$result = jb_mysql_query($sql);
						$row = mysql_fetch_array($result, MYSQL_ASSOC);
						$data[$i] = $row['code'];
						break;
					case 'TIME':
						//$data[$i] = JB_get_formatted_date($part);
						break;
				}
				// Add column name and value on to the $_REQUEST
				// These will then become input for the search.
				$_REQUEST[$field_id] = $data[$i];
				$i++;
			}

		} 
		

		// force in to search mode
		$_REQUEST['action'] = 'search'; 
		$_REQUEST['search'] = 'Find'; 
		$_REQUEST['post_id'] = ''; // clear the post_id

	}
	
	return $_REQUEST['post_id'];


}

################################

function JB_process_emp_permalink () {

	// nothing fancy, just get the number from the emp_id

	preg_match ('#^\d+#', $_REQUEST['show_emp'], $m);
	$_REQUEST['show_emp'] = $m[0];


}


################################
# Other
################################


# JB_get_go_back_link()
# This will print the URL for 'Go back to the Job List' in display-post.php
# And also the URL for 'Go Back to the Search Results' for employers/search.php

function JB_get_go_back_link() {

	$link = false;
	JBPLUG_do_callback('get_go_back_link', $link);
	if ($link!==false) return $link;

	// if referer contains the job board's URL
	if (strpos($_SERVER['HTTP_REFERER'], JB_BASE_HTTP_PATH)!==false) {

		return $_SERVER['HTTP_REFERER'];

	} else {
		// return the self
		return htmlentities($_SERVER['PHP_SELF']);
	}

}

#######################################################


function jb_format_url_string($str) {

	// \xc0-\xff	À-ÿ
	$str = preg_replace("#[^a-z^0-9^\xc0-\xff^\-^&^\#^;]+#i", '-', $str);			
	$str = strtolower($str);
	// convert to UTF
	$str = JB_html_ent_to_utf8($str); // this will convert html entities in to UTF-8 encoded characters

	/*

	Note to developers of multi-lingual sites:

	When viewing the HTML source, you may notice that the URLs are
	encoded with hex values. For example:

	http://example.com/job/%C3%A1ruter%C3%ADt%C5%91-%C3%BCzletk%C3%B6t%C5%91/2010-03-16/19284

	This is the standard method for encoding URLs for the browser.
	This is the standard expected by all major search engines.
	Since URLs do not allow UTF-8 (and have many restrictions on
	what Latin characters can be used), they must be encoded. See http://www.eskimo.com/~bloo/indexdot/html/topics/urlencoding.htm
	
	This applies for all UTF-8 characters in the URL.

	Some webmasters may like to remove accent characters.

	*/

	if (JB_MOD_REWRITE_REMOVE_ACCENTS=='YES') {
		$str = JB_remove_accents($str);
	}

	// Fix up dashes.
	// Sometimes there can be a sequence of dashes appearing, eg health--community-

	//remove &amp; as & chars are not url safe
	$str = str_replace('&', '-', $str); // str_replace is utf-8 safe
	//remove sharps as # are not url safe
	$str = str_replace('#', '-', $str); //

	if (function_exists('mb_substr')) {

		if ($str[mb_strlen($str)-1]=='-') {
			$str = mb_substr($str, 0, $str[mb_strlen($str)]-1); // chop off the last - character
		}
		// remove leading hyphens /^-/
		if ($str[0]=='-') {
			$str = mb_substr($str, 1); // chop ff the starting - character
		}

	} else {
	
		// remove trailing hyphens /-$/
		//$str = preg_replace("/-$/", "", $str);
		if ($str[strlen($str)-1]=='-') {
			$str = substr($str, 0, $str[strlen($str)]-1); // chop off the last - character
		}
		// remove leading hyphens /^-/
		//$str = preg_replace("/^-/", "", $str);
		if ($str[0]=='-') {
			$str = substr($str, 1); // chop ff the starting - character
		}
	
	}
	// remove double hyphens
	$str = str_replace("--", "", $str);

	return urlencode($str);


}

/////
// Can be used to remove accents from URL's (if needed)
function JB_remove_accents($utf8_str) { 

	$chars = array(
    // Decompositions for Latin-1 Supplement
    chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
    chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
    chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
    chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
    chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
    chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
    chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
    chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
    chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
    chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
    chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
    chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
    chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
    chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
    chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
    chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
    chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
    chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
    chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
    chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
    chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
    chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
    chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
    chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
    chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
    chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
    chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
    chr(195).chr(191) => 'y',
    // Decompositions for Latin Extended-A
    chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
    chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
    chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
    chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
    chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
    chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
    chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
    chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
    chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
    chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
    chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
    chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
    chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
    chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
    chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
    chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
    chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
    chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
    chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
    chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
    chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
    chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
    chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
    chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
    chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
    chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
    chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
    chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
    chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
    chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
    chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
    chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
    chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
    chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
    chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
    chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
    chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
    chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
    chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
    chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
    chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
    chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
    chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
    chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
    chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
    chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
    chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
    chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
    chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
    chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
    chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
    chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
    chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
    chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
    chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
    chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
    chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
    chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
    chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
    chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
    chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
    chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
    chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
    chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
    );
	
	return strtr($utf8_str, $chars);

}
?>