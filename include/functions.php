<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################


include (dirname(__FILE__).'/cache_manager.php');
require (dirname(__FILE__).'/url_writing_functions.php');
require (dirname(__FILE__).'/schema_functions.php');
require (dirname(__FILE__).'/file_functions.php');
require(dirname(__FILE__).'/classes/pages.php');

#######################################################
JB_init();
#######################################################

JB_clean();


function JB_clean() {

	if (defined('JB_IGNORE_INPUT_FILTER')) return; // eg. admin/plugins.php where full HTML and Javascript is allowed for some plugins

	$integer_list = array('resume_id', 'profile_id', 'user_id', 'cat', 'pin_x', 'pin_y', 'membership_id', 'subscription_id', 'invoice_id', 'category_id', 'offset', 'employer_id', 'package_id');
	$identifier_list = array('post_id', 'show_emp', 'mode', 'type', 'pay', 'product_type', 'pay_method', 'confirm', 'action', 'anon', 'table', 'JB_SAVED_LANG', 'lang', 'action', 'p');
	$password_list = array('pass', 'password'); // these can have any text in them


	foreach ($_REQUEST as $key => $val) {

		if ($val=='') continue;

		if (is_array($val)) { // apps[] post_ids[] posts[] resumes[] etc.
			foreach ($val as $item_key => $item_val) {
				$_REQUEST[$key][$item_key] = jb_clean_identifier($item_val);
			}
		} else {

			if (in_array($key, $password_list)) { // admin pass or user login pass
				continue;
			}

			if (in_array($key, $integer_list)) { // Should be integer?
				$_REQUEST[$key] = (int) $val;
			} elseif (in_array($key, $identifier_list)) { // Should be identifier?
				$_REQUEST[$key] = jb_clean_identifier($val);
			} else {
				if (is_numeric($key)) { //numeric keys - assuming dynamic forms
					// dynamic forms  have their own input validation routine.
					// let dynamic forms take care of the rest later.
					$val = JB_scrub_input($val);

				} else {
					// complete filtering for anything else.
					$_REQUEST[$key] = JB_removeEvilTags($val);
				}
			}
		}
	}

}

function jb_clean_identifier($str) {

	// hex values:
	// \x2d			-
	// \xc0-\xff	À-ÿ
	// \x3d			=
	// \x3a			:
	// \x20			Space
	// \x2f			/
	// \x2e			.
	// \x5f			_
	// \x2b			+

	// identifiers should never have > < & ' or "

	return (is_numeric($str) ?
		$str : preg_replace('#[^0-9^A-Z^a-z^\x2D^\xc0-\xff^\x3d^\x3a^\x20^\x2f^\x2e^\x5f^\x2b]+#', '', $str)
		);

}

function JB_clean_str($str) {
	return JB_removeEvilTags($str);
}

######################################################

// ensure that all values in array are integer by casting to int
function jb_int_array($v) {
   return is_array($v) ? array_map('jb_int_array', $v) : (int) $v;
}

// convert to alphabetic string
function jb_alpha($str) {

	// \xc0-\xff	À-ÿ
	return preg_replace('/[^a-z^\xc0-\xff]+/i', '', $str);

}

// convert to alpha numeric str
function jb_alpha_numeric($str) {
	// \x2d			-
	// \x5f			_
	// \x2e			.
	return preg_replace('/[^a-z^0-9^\x5f^\x2d^\x2e]+/i', '', $str);

}

######################################################

function JB_init() {

	

	umask  (0000);

	setlocale ( LC_CTYPE, 'C' ); // downgrade character type locale to the POSIX (C). It would mean functions like strtolower() are only considering characters in the ASCII range. Compatibile with all servers

	if (defined('JB_INIT_COMPLETED')) return;


	if (!defined('JB_NEW_DIR_CHMOD')) {
		define('JB_NEW_DIR_CHMOD', 0777);
	}
	if (!defined('JB_NEW_FILE_CHMOD')) {
		define('JB_NEW_FILE_CHMOD', 0666);
	}
	##########################################
	# Initialize language cookies and variables
	##########################################
	global $label;


	if (!defined('JB_OMIT_SESSION_START')) {
		session_start();
	}

	JB_init_lang_cookie();
	JB_init_lang_vars();
	##########################################

	// set the timezone to GMT, the internal time-zone used by the job board
	if (function_exists('date_default_timezone_set')) {
		// since PHP 5.1
		date_default_timezone_set('GMT');
	} else {
		// older versions
		@ini_set('date.timezone', 'GMT');
		$_SERVER['TZ'] = 'GMT';
	}

	if (!defined('JB_LIST_HOVER_COLOR')) {
		define ('JB_LIST_HOVER_COLOR', '#FEFEED');
	}

	if (!defined('JB_LIST_BG_COLOR')) {
		define ('JB_LIST_BG_COLOR', '#FFFFFF');
	}

	JBPLUG_require_plugins(); // plugins awake

	##########################################
	JB_init_themes();
	##########################################

	JB_do_house_keeping(); // this function gets called at every request!
	

	JBPlug_do_callback('jb_init', $A=false);

	define ('JB_INIT_COMPLETED', true);


}



#---------------------------------------------------------------------
# Written for having magic quotes enabled
#
# This software assumes that magic quotes is enabled.
# If disabled, the software will add quotes to all input automatically
# just as if magic quotes enabled.
#
#---------------------------------------------------------------------
#####################################################
function JB_unfck($v) {
   return is_array($v) ? array_map('JB_unfck', $v) : addslashes(trim($v));
}

######################################################
 function JB_unfck_gpc() {
   $arr = "POST,GET,REQUEST,COOKIE,SERVER,FILES";
   foreach (explode(',',$arr) as $gpc)
   $GLOBALS["_$gpc"] = array_map('JB_unfck', $GLOBALS["_$gpc"]);
}



############################################################
function JB_mail_error($msg) {

	$date = date("D, j M Y H:i:s O");

	$headers = "From: ". JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Reply-To: ".JB_SITE_CONTACT_EMAIL ."\r\n";
	//$headers .= "Return-Path: ".JB_SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "X-Mailer: PHP" ."\r\n";
	$headers .= "Date: $date" ."\r\n";
	$headers .= "X-Sender-IP: $REMOTE_ADDR" ."\r\n";

	$entry_line =  "(Jamit Jobboard payal error detected) $msg\r\n ";
	$log_fp = fopen("logs.txt", "a");
	fputs($log_fp, $entry_line);
	fclose($log_fp);


	mail(JB_SITE_CONTACT_EMAIL, "Error message from ".JB_SITE_NAME." Jamit Paypal IPN script. ", $msg, $headers);

}

##################################################

 function JB_validate_mail($Email) {
 	global $HTTP_HOST;
 	$result = array();
 	$Pattern = "/^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,})$/siD";
 	if (!preg_match($Pattern, $Email)) {

 		return false;
 	}
   /*
 	list ( $Username, $Domain ) = split ("@",$Email);

 	if (my_getmxrr($Domain, $MXHost))  {
 		$ConnectAddress = $MXHost[0];
 	} else {
 		$ConnectAddress = $Domain;
 	}

 	$Connect = @fsockopen($ConnectAddress, 25);
 	if ($Connect) {
 		if (ereg("^220", $Out = fgets($Connect, 1024))) {
 			fputs($Connect, "HELO $HTTP_HOST\r\n");
 			$Out = fgets($Connect, 1024);
 			fputs($Connect, "MAIL FROM: <{$Email}>\r\n");
 			$From = fgets($Connect, 1024);
 			fputs($Connect, "RCPT TO: <{$Email}>\r\n");
 			$To = fgets($Connect, 1024);
 			fputs ($Connect, "QUIT\r\n");
 			fclose($Connect);
 			$result[2] = $Out;
 			$result[3] = $From;
 			$result[4] = $To;
 			$result[5] = $Domain;
 			$result[6] = $MXHost[0];

 			if (!ereg("^250", $From) || !ereg ("^250", $To)) {
 				$result[0] = false;
 				$result[1] = "Server rejected address.\n";
 				return $result;
 			}
 		} else {
 			$result[0] = false;
 			$result[1] = "No response from server.\n";
 			return $result;
 		}
 	}  else {
 		$result[0] = false;
 		$result[1] = "Can not connect E-Mail server.\n";
 		return $result;
 	}
   */


 	return true;
 }



######################################################################

function JB_reverse_strrchr($haystack, $needle)
{
   $pos = strrpos($haystack, $needle);
   if($pos === false) {
       return $haystack;
   }
   return substr($haystack, 0, $pos + 1);
}

######################################################################



function JB_display_available_languages () {

	global $JBMarkup, $lang;

	if (!$av_langs = jb_cache_get('av_langs')) {

		$sql = "SELECT * FROM lang WHERE is_active='Y' ";
		$result = JB_mysql_query($sql);
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$av_langs[] = $row;

		}
		jb_cache_add('av_langs', $av_langs);

	}


	if (sizeof($av_langs)>1) {

		$JBMarkup->available_langs_heading();


		foreach ($av_langs as $row) {
			$JBMarkup->available_langs_item($row['lang_code'], $row['name']);
		}
	}

}
###############################


#########################################



##############################################

function JB_get_email_template ($template_id, $lang) {

	if ($lang=='') {

		if ($_SESSION['LANG']!='') {
			$lang= $_SESSION['LANG'];
		} else {

			$lang="EN";
		}

	}

	$sql = "SELECT * FROM email_template_translations WHERE EmailID='".JB_escape_sql($template_id)."' AND lang='".JB_escape_sql($lang)."' ";
	$result = JB_mysql_query ($sql) or die ( "error: ".$sql.mysql_error());

	if (mysql_num_rows($result)==0) { // maybe the lang was deleted?
		$sql = "SELECT * FROM email_templates WHERE EmailID='".JB_escape_sql($template_id)."' ";
		$result = JB_mysql_query ($sql) or die ( "error: ".$sql.mysql_error());

	}

	return $result;

}



###########################################################

function JB_db_generate_id_fast($field, $table) {
   $query ="SELECT max($field) FROM $table";
   $result = JB_mysql_query($query) or die(mysql_error());
   $row = mysql_fetch_row($result);
   $row[0]++;
   return $row[0];
}
###########################################################

/*

Time conversion / formatting functions

Code Tip:

To localize the date in to the local format:

$local_date = JB_get_local_time($date);

Note: Does not do timezone conversion
$date is the ISO date in YYYY-MM-DD format
$use_time -  the $date also includes time in H:i:s format

To change the GMT Date to Job Board's local time (timezone conversion):

JB_get_local_time($gmdate)

$gmdate is the GMT Date, formatted in ISO Date, YYYY-MM-DD HH:MM:SS


Example to convert a GMT Date form the database to the local time-zone, and then display it in the local format:

echo JB_get_formatted_date(JB_get_local_time('2001-02-22 12:09:04'));


*/
#############################
# Assuming that 4 digit year is given first
# followed by month then date.
# eg 2004-03-22
# $date is the ISO date in YYYY-MM-DD format
# $use_time
function JB_get_formatted_date($date, $use_time=false) {

	/*

Assuming that the date came from the database. If it came from the database, then it should always be in ISO YYYY-MM-DD format where all parts of the date are digits.

If $date is not YYYY-MM-DD then the function was used incorrectly. 

When converting the format, the first problem is that strtotime and date functions have a range from year 1970 to 2038. If the year is out of this range then it defaults to a simple formatting routine which basically swaps around the year, month and day parts to the 'date input sequence' settings in Admin->Main Config.

If the $use_time argument is true then it will also return the time part with the date


	*/

	$year = substr ($date, 0, 4);

	if (($year > 2038) || ($year < 1970)) {  //  out of range to format!
		$month =  substr ($date, 5, 2);
		$day =  substr ($date, 8, 2);
		$sequence = strtoupper(JB_DATE_INPUT_SEQ);
		while ($widget = substr($sequence, 0, 1)) {
			switch ($widget) {
				case 'Y':
					$ret .= $s.$year;
				break;
				case 'M':
					$ret .=  $s.$month;
				break;
				case 'D':
					$ret .=  $s.$day;
				break;
			}
			$s='-';
			$sequence = substr($sequence, 1);
		}
		if ($use_time) {
			return $ret.$sequence.substr($date, 10, 9);
		} else {
			return $ret;
		}

	}
	// else:
	if ($use_time) {
		$JB_DATE_FORMAT = JB_DATE_FORMAT.' H:i:s';
	} else {
		$JB_DATE_FORMAT = JB_DATE_FORMAT;
	}
	$time = strtotime($date);
	return date($JB_DATE_FORMAT, $time);

}
#################################

function JB_get_formatted_time($date) {

	return JB_get_formatted_date($date, true);

}

############################

function JB_get_local_time($gmdate) {


	if ((strpos ($gmdate, 'GMT')===false) && ((strpos ($gmdate, 'UTC')===false)) && ((strpos ($gmdate, '+0000')===false))) { // gmt not found
		$gmdate = $gmdate." GMT";

	}
	$gmtime = strtotime($gmdate);

	if ($gmtime==-1) { // out of range
		preg_match ("/(\d+-\d+-\d+).+/", $gmdate, $m);
		return $m[1];

	} else {

		return gmdate("Y-m-d H:i:s", $gmtime + (3600 * JB_GMT_DIF));
	}

}
#################################

function JB_trim_date($gmdate) {
	preg_match ("/(\d+-\d+-\d+).+/", $gmdate, $m);
	return $m[1];

}


#################################

function JB_get_formatted_name($fname, $lname='', $format='') {

	$fname = trim($fname);
	$lname = trim($lname);

	if (!$lname) return $fname;

	if (!$format) {
		if (defined('JB_NAME_FORMAT')) {
			$format = JB_NAME_FORMAT;
		} else {
			$format = 'L, F';
		}
	}
	$f_pos = strpos($format, 'F');
	$l_pos = strpos($format, 'L');

	$format = strtr($format, array('F' => '%s', 'L' => '%s'));

	if ($f_pos > $l_pos) { 
		// first name is after last name
		$name = sprintf($format, $lname, $fname);
	} else {
		$name = sprintf($format, $fname, $lname);
	}

	return $name;

	
}


###############################################################

function JB_check_for_bad_words ($data) {
	$found_bad = false;

	$data = strtolower($data);

	$bad_words = trim (JB_BAD_WORDS);
	if (strlen($bad_words)==0) return false;

	static $baddies;

	if ($baddies == null) {

		$bad_words = strtolower($bad_words);
		$baddies = preg_split ("/[\s,]+/", $bad_words);

	}

	 foreach ($baddies as $bad) {
		 $bad = trim($bad);
		 if ($bad) {
			 $bad = preg_replace('/([^a-z^0-9])+/i', '\\\$1',$bad); // escape preg characters with a backslash to avoid compilation errors
			 if (preg_match("/\b$bad\b/", $data)) { // match the bad words between the word boundaries
				 $found_bad = true;
				 break;
			 }
		 }
	 }

	 return $found_bad;


}
///////////////////////////

function JB_get_html_strlen($str) {

	while ((preg_match ("/(&#?[0-9A-z]+;|.)/", $s, $maches, PREG_OFFSET_CAPTURE, $offset))) {
		$offset += strlen($maches[0][0]);
		$len++;

	}
	return $len;

}

///////////////////


function JB_break_long_words($input, $with_tags) {
	// new routine, deals with html tags...
	if (defined('JB_LNG_MAX')) {
		$lng_max = JB_LNG_MAX;
	} else {
		$lng_max = 80;
	}

	//$input = stripslashes($input);

	while ($trun_str = JB_truncate_html_str($input, $lng_max, $trunc_str_len, false, $with_tags)) {

		if ($trunc_str_len == $lng_max) { // string was truncated

			if (strrpos ($trun_str, " ")!==false) { // if trun_str has a space?
				$new_str .= $trun_str;

			} else {
				$new_str .= $trun_str." ";
			}

		} else {
			$new_str .= $trun_str;
		}
		$input = substr($input, strlen($trun_str));
	}
	//$new_str = addslashes($new_str);
	return $new_str;

}


#######################################
# function JB_truncate_html_str
# truncate a string encoded with htmlentities eg &nbsp; is counted as 1 character
# Limitation: does not work with well if the string contains html tags.
function JB_truncate_html_str ($s, $MAX_LENGTH, &$trunc_str_len) {

	$trunc_str_len=0;

	if (func_num_args()>3) {
		$add_ellipsis = func_get_arg(3);

	} else {
		$add_ellipsis = true;
	}

	if (func_num_args()>4) {
		$with_tags = func_get_arg(4);

	} else {
		$with_tags = false;
	}

	if ($with_tags){
		$tag_expr = "|<[^>]+?>";

	}

	$offset = 0; $character_count=0;
	# match a character, or characters encoded as html entity
	# treat each match as a single character
	#
	while ((preg_match ('/(&#?[0-9A-z]+;'.$tag_expr.'|.|\n)/', $s, $maches, PREG_OFFSET_CAPTURE, $offset) && ($character_count < $MAX_LENGTH))) {
		$offset += strlen($maches[0][0]);
		$character_count++;
		$str .= $maches[0][0];


	}
	if (($character_count == $MAX_LENGTH)&&($add_ellipsis)) {
		$str = $str."...";
	}
	$trunc_str_len = $character_count;
	return $str;


}

####################################################

function JB_escape_html ($str, $amp=false) {

	/*

	To test this function:

	echo (jb_escape_html('This is a test &amp; and this is ampersand: & two nbsps: [&nbsp;&nbsp;] chinese &#21363;&#21152;&#20837;'));

	You would see two normal ampersands, two blank space between square brackets, Chinese characters.


	*/
	$str = (string) $str;

	//eliminate HTML entities, eg. &amp; &nbsp; - convert to ISO-8859-1
	$str = html_entity_decode($str);

	// allow & followed by #, otherwise replace & with &amp;
	// This allows entities such as &#20837; (chinese)
	// Note that Jamit input validation does not accept
	// characters lower than &#255; and converts them to their
	// proper character
	for ($i=0; $i < strlen($str); $i++) {
		if (($str[$i]=='&') && ($str[$i+1]!='#')) {
			$new_str .= '&amp;';
		} else {
			$new_str .= $str[$i];
		}
	}

	// Convert to HTML entities, ready for output to the browser

	$trans = array(
		"<" => '&lt;',
		">" => '&gt;',
		'"' => '&quot;',
		'(' => '&#40;',
		')' => '&#41;',
		//'&' => '&amp;', // see 'for' loop above
	);

	return strtr($new_str, $trans);

}
##########################################
# This function remove sany control characters, null bytes eg. \0
# and change any characters which are encoded using entities,
# but can be represented in normal a 8 bit char.
# This function removes some undesired elements which can be used to
# craft xss attacks
# This function is called by JB_removeEvilTags($source)
# but should be called
# on any input that comes form the outside and then is displayed later

function JB_scrub_input($str) {

	if (!is_string($str)) {
		return false;
	}

	// The ASCII characters can be printed using html
	// entites. Like this &#X00 or like this &#X00;
	// the can be padded with 0's to too, like this &#X0000A; &#12391;
	// Some browsers can parse the htmlentities as normal tags!
	// Solution: Change these to normal acsii

	// (x)? matches hex, if no x then it is decimal
	if (preg_match_all('/&#(x)?0{0,8}([0-9a-f]+);?/i', $str, $m)) {

		foreach ($m[2] as $key=>$num) {
			if ($m[1][$key]!='') { // its a hex, change to decimal
				$num = hexdec($num);
			}
			if ($num < 0x7E) { // is it ASCII?
				// Someone just tried to sneak in an ASCII character using
				// html entities.
				// Replace the entire match with the standard ASCII character
				$str = str_replace($m[0][$key], chr($num), $str);
			}
		}

	}

	/*

	remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
    this prevents some character re-spacing such as <java\0script>
    note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs

   Source: http://quickwired.com/kallahar/smallprojects/php_xss_filter_function.php

	*/

	$str = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $str);

	return $str;


}


#################################################


/**
 * @return string
 * @param string
 * @desc Strip forbidden tags and delegate tag-source check to JB_removeEvilAttributes()
 */
function JB_removeEvilAttributes($tagSource) {

	$stripAttrib = '/style[\s]+?=|class[\s]+?=|onabort|onactivate|onafterprint|onafterupdate|onbeforeactivate|onbeforecopy|onbeforecut|onbeforedeactivate|onbeforeeditfocus|onbeforepaste|onbeforeprint|onbeforeunload|onbeforeupdate|onblur|onbounce|oncellchange|onchange|onclick|oncontextmenu|oncontrolselect|oncopy|oncut|ondataavailable|ondatasetchanged|ondatasetcomplete|ondblclick|ondeactivate|ondrag|ondragend|ondragenter|ondragleave|ondragover|ondragstart|ondrop|onerror|onerrorupdate|onfilterchange|onfinish|onfocus|onfocusin|onfocusout|onhelp|onkeydown|onkeypress|onkeyup|onlayoutcomplete|onload|onlosecapture|onmousedown|onmouseenter|onmouseleave|onmousemove|onmouseout|onmouseover|onmouseup|onmousewheel|onmove|onmoveend|onmovestart|onpaste|onpropertychange|onreadystatechange|onreset|onresize|onresizeend|onresizestart|onrowenter|onrowexit|onrowsdelete|onrowsinserted|onscroll|onselect|onselectionchange|onselectstart|onstart|onstop|onsubmit|onunload/i';

    $tagSource = preg_replace($stripAttrib, '  ', $tagSource);

     return $tagSource;
}


/**
 * Sanitize input from XSS attacks and filter out unwanted HTML
 *
 */
function JB_removeEvilTags($source) {

	/*

	How to unit test this function:

	1. Download xssAttacks.xml from http://ha.ckers.org/xss.html


	2. use the following code to scan all the attacks, the output
	should be for all echo JB_removeEvilTags($obj->code); to pass.

	$xml = simplexml_load_file('xssAttacks.xml');

	foreach ($xml->attack as $key => $obj) {
		echo JB_removeEvilTags($obj->code);
	}


	*/


	$source = Jb_scrub_input($source);
	$allowedtags = '<H1><B><Br><Br><I><A><Ul><Li><Hr><Blockquote><Img><Span><Div><Font><P><Em><Strong><Center><Div><Table><Td><Tr>';
	$source = Strip_tags($source, $allowedtags);
	return Jb_removeevilattributes($source);


   //return preg_replace('/<(.*?)>/ie', "'<'.JB_removeEvilAttributes('\\1').'>'", $source);
}

##############################################################

function JB_remove_non_latin1_chars($str) {
	// strip out characters that aren't valid in ISO-8859-1 (Also known as 'Latin 1', used in HTML Documents)
	// added \xA0-\xFE for extended adcii
	return preg_replace('/[^\x09\x0A\x0D\x20-\x7F\xC0-\xFF\xA0-\xFE]/', '', $str);

}



########################################################

function JB_to_csv_string($row, $fd=',', $quot='"'){
	# http://www.creativyst.com/Doc/Articles/CSV/CSV01.htm

   $str='';
   foreach ($row as $cell)
   {
     $cell = str_replace($quot, $quot.$quot, $cell);

     if (strchr($cell, $fd) !== FALSE || strchr($cell, $quot) !== FALSE || strchr($cell, "\n") !== FALSE)
     {
         $str .= $quot.$cell.$quot.$fd;
     }
     else
     {
         $str .= $cell.$fd;
     }
   }

   return substr($str, 0, -1)."\n";

}


#########################################

function JB_email_at_replace($str, $mode='view') {


		// replace @ sign with an image, to prevent email harvesting
		if ((JB_EMAIL_AT_REPLACE=="YES") ) {
			$DFM = &JB_get_DynamicFormMarkupObject($mode);
			$str =  str_replace ( "@", $DFM->at_sign_replace(), $str);
		} elseif ((JB_EMAIL_AT_REPLACE=="YES_2") ) {
			// replace at sign with html entities representation for @
			$str =  str_replace ( "@", '&#64;', $str);
		}

		return $str;


}

#########################################
# This function is used to output the data from the EDITOR
# field after the data was fetched form $JBDynamicForm->get_template_value()
# eg. display-post.php template file.
function JB_process_for_html_output ($str) {

	if (!preg_match("/<.*?>/U", $str)) { // Not text mode?
		$str = preg_replace ('/\n/', '<br>', $str);
	}

	if (JB_EMAIL_AT_REPLACE!='NO') {
		// eliminate tags with mailto:
		$str = preg_replace( '@<a href=["|\']mailto:.*["|\'] *>(.*)</a>@Ui', '$1', $str);
		$str = JB_email_at_replace($str);
	}

	return $str;

}


######################################
# Page navigation links - prepare the data
# Produces a data structure for JB_render_nav_pages ()
# And JB_render_postlist_nav_links () functions
#
# For job posts see JB_render_postlist_nav_links() instead
# Assuming that $q_string has urlencoded values

function JB_nav_pages_struct(&$result, $q_string, $count, $REC_PER_PAGE) {

	global $label;
	global $list_mode;

	/*

	This function uses the JBListMarkup class to generate any HTML
	If you want to customize the HTML for the next / previous links,
	you will copy the JBListMarkup class in to your custom theme directory,
	and apply your customizatios there.
	The JBListMarkup class of the default theme is located in:
	include/themes/default/JBListMarkup.php

	*/

	$LM = &JB_get_ListMarkupObject(); // get a reference the ListMarkup Class

	if ($list_mode=='PREMIUM') {
		$q_string .= "&amp;p=1";

	}
	$nav = array();
	$nav['prev'] = '';
	$nav['next'] = '';
	$nav['pages_before'] = '';
	$nav['pages_after'] = '';
	$nav['cur_page'] = '';

	$page = htmlentities($_SERVER['PHP_SELF']);
	$offset = (int) $_REQUEST["offset"];

	if (isset($_REQUEST['show_emp']) && is_integer($_REQUEST['show_emp'])) {
	  $show_emp = "&amp;show_emp=".$_REQUEST['show_emp'];
	}
	if (isset($_REQUEST['cat']) && is_integer($_REQUEST['cat'])) {
	  $show_emp = "&amp;cat=".$_REQUEST['cat'];
	}
	if (isset($_REQUEST['order_by']) && (strlen($_REQUEST['order_by'])>0)) {
	  $order_by = "&amp;order_by=".htmlentities($_REQUEST['order_by'])."&amp;ord=".htmlentities($_REQUEST['ord']);
	}
	if (isset($_REQUEST['show']) && (strlen($_REQUEST['show'])>0)) {
		$show = "&amp;show=".htmlentities($_REQUEST['show']);
	}



	$cur_page = $offset / $REC_PER_PAGE;
	$cur_page++;
	// estimate number of pages.
	$pages = ceil($count / $REC_PER_PAGE);
	if ($pages == 1) {
	   return;
	}
	$off = 0;
	$p=1;
	$prev = $offset-$REC_PER_PAGE;
	$next = $offset+$REC_PER_PAGE;

	if ($prev===0) {
		$prev='';
	}

	if ($prev > -1) {
	    $nav['prev'] =  $LM->get_nav_prev_link($page."?offset=".$prev.$q_string.$show_emp.$cat.$order_by.$show, $label["navigation_prev"]);
		$nav['prev_page'] = $prev;

	}
	for ($i=0; $i < $count; $i=$i+$REC_PER_PAGE) {
	  if ($p == $cur_page) {
		 $nav['cur_page'] = $p;
	  } else {
		  if ($off===0) {
			$off='';
		}
		 if ($nav['cur_page'] !='') {
			 $nav['pages_after'][$p] = $off;
		 } else {
			$nav['pages_before'][$p] = $off;
		 }
	  }
	  $p++;
	  $off = $off + $REC_PER_PAGE;
	}
	if ($next < $count ) {
		$nav['next'] = $LM->get_nav_next_link($page."?offset=".$next.$q_string.$show_emp.$cat.$order_by.$show, $label["navigation_next"]);
		$nav['next_page'] = $next;
	}

	return $nav;
}

#####################################################
/* Page navigation links -


# The following function renders the Prevois/Next/page numbers
# Navigation links when listing results.
#
# eg: <- Prev | 1 | 2 | 3 | 4 | 5 | 6 | 7 | Next ->

# These navigation links are shown for Resume List, Profile List,
# Employer List, Candidate List.
#
# It accepts a data structure generated by JB_nav_pages_struct



	This function uses the JBListMarkup class to generate any HTML
	If you want to customize the HTML for the next / previous links,
	you will copy the JBListMarkup class in to your custom theme directory,
	and apply your customizatios there.
	The JBListMarkup class of the default theme is located in:
	include/themes/default/JBListMarkup.php


# Note:
# For job posts, a localized version of this function exists in posts.inc.php
# This function is depracted for the Job Posts list
# For the Prevois/Next nav links for the job lists,
# see lists.inc.php JobListAttributes
# And see JB_render_postlist_nav_links() in posts.inc.php
# However, all functions use the JBListMarkup class to render the HTML
#
#
*/

function JB_render_nav_pages (&$nav_pages_struct, $LINKS, $q_string='') {

	global $list_mode;
	global $label;
	$LM = &JB_get_ListMarkupObject(); // get a reference to the ListMarkup Class

	$seperator = '';
	if ($list_mode=='PREMIUM') {
		echo $label['post_list_more_sponsored']." ";
		$q_string .= "&amp;p=1";
	}

	$page = htmlentities($_SERVER['PHP_SELF']);
	$offset = (int) $_REQUEST["offset"];

	if (isset($_REQUEST['show_emp']) && is_integer($_REQUEST['show_emp'])) {
		$show_emp = "&amp;show_emp=".$_REQUEST['show_emp'];
	}
	if (isset($_REQUEST['cat']) && is_integer($_REQUEST['cat'])) {
		$show_emp = "&amp;cat=".$_REQUEST['cat'];
	}

	if (isset($_REQUEST['order_by']) && (strlen($_REQUEST['order_by'])>0)) {
	  $order_by = "&amp;order_by=".htmlentities($_REQUEST['order_by'])."&amp;ord=".htmlentities($_REQUEST['ord']);
	}

	if (isset($_REQUEST['show']) && (strlen($_REQUEST['show'])>0)) {
		$show = "&amp;show=".htmlentities($_REQUEST['show']);
	}


	if ($nav_pages_struct['cur_page'] > $LINKS-1) {
		$LINKS = round ($LINKS / 2)*2;
		$NLINKS = $LINKS;
	} else {
		$NLINKS = $LINKS - $nav_pages_struct['cur_page'];
	}

	echo $nav_pages_struct['prev'];

	$b_count = count($nav_pages_struct['pages_before']);
	for ($i = $b_count-$LINKS; $i <= $b_count; $i++) {
		if (isset($nav_pages_struct['pages_before'][$i])) {

			echo $LM->get_nav_numeric_link($page."?offset=".$nav_pages_struct['pages_before'][$i].$q_string.$show_emp.$cat.$order_by.$show, $i);

			$seperator = $LM->get_nav_seperator();
		}
	}
	echo $LM->get_nav_current_page($seperator, $nav_pages_struct['cur_page']);

	if (($nav_pages_struct['pages_after'])!='') {
		$i=0;

		foreach ($nav_pages_struct['pages_after'] as $key => $pa ) {
			$i++;
			if ($i > $NLINKS) {
				break;
			}

			echo $LM->get_nav_numeric_link($page.'?offset='.$pa.$q_string.$show_emp.$cat.$order_by.$show, $key);

		}
	}

	echo $nav_pages_struct['next'];


}


###################################################

function JB_render_box_top($width=100, $heading='', $body_bg_color='#ffffff') {

	JB_template_info_box_top($width, $heading, $body_bg_color);



}
######################################################
function JB_render_box_bottom() {

	JB_template_info_box_bottom();



}
#############################################

function JB_display_info_box ($heading, $body) {

	if (func_num_args() >2) {
		$width = func_get_arg(2);
		//$width = " width=\"".$width."%\" ";

	}

	JB_render_box_top($width, $heading, '');
	?>

	<span >
	<?php echo $body; ?>
	</span>

	<?php
	JB_render_box_bottom();

}
##############################
# For security purposes, this function only converts entities above
# the LATIN-1 table, starting from 0xFF
# This is because it would be possible to encode > or < as &#62; and &#62
function JB_html_ent_to_utf8($str) {
	if (function_exists('mb_decode_numericentity')) {
		$str = mb_decode_numericentity($str, array(0xFF, 0xfffff, 0, 0xfffff), 'UTF-8');
	} else {
		$str = utf8_encode($str);
		$str = preg_replace('/&#(\d{2,5}?);/e', " JB_html_ent_to_utf8_callback('\\1')", $str);
	}
	return $str;
}


function JB_html_ent_to_utf8_callback($char) {
	# callback function for preg_replace() in JB_html_ent_to_utf8()
	$char = intval($char);
	if ($char <= 255) return '&#'.$char.';';
	if ($char < 0x8000) {
		return chr(0xc0 | (0x1f & ($char >> 6))) . chr(0x80 | (0x3f & $char));
	}
	else {
		return chr(0xe0 | (0x0f & ($char >> 12))) . chr(0x80 | (0x3f & ($char >> 6))). chr(0x80 | (0x3f & $char));
	}
}



###########################################################

function jb_numeric_entities($string){
        $mapping_hex = array();
        $mapping_dec = array();

        foreach (get_html_translation_table(HTML_ENTITIES, ENT_QUOTES) as $char => $entity){
            $mapping_hex[html_entity_decode($entity,ENT_QUOTES,"UTF-8")] = '&#x' . strtoupper(dechex(ord(html_entity_decode($entity,ENT_QUOTES)))) . ';';
            $mapping_dec[html_entity_decode($entity,ENT_QUOTES,"UTF-8")] = '&#' . ord(html_entity_decode($entity,ENT_QUOTES)) . ';';
        }
        $string = str_replace(array_values($mapping_hex),array_keys($mapping_hex) , $string);
        $string = str_replace(array_values($mapping_dec),array_keys($mapping_dec) , $string);
        return $string;
    }

###########################################################

function JB_is_filetype_allowed ($file_name) {

	$a = explode(".", $file_name);
	$ext = strtolower(array_pop($a));

	if (JB_ALLOWED_EXT=='ALLOWED_EXT') {
		$JB_ALLOWED_EXT= 'jpg, jpeg, gif, png, doc, pdf, wps, hwp, txt, bmp, rtf, wri';
	} else {
		$JB_ALLOWED_EXT=trim(strtolower(JB_ALLOWED_EXT));
	}

	//$ext_list = explode (',',$JB_ALLOWED_EXT);
	$ext_list =preg_split ("/[\s,]+/", ($JB_ALLOWED_EXT));
	return in_array($ext, $ext_list);


}

###########################################################

function JB_is_imagetype_allowed ($file_name) {

	$a = explode(".",$file_name);
	$ext = strtolower(array_pop($a));

	if (JB_ALLOWED_IMG=='JB_ALLOWED_IMG') {
		$JB_ALLOWED_IMG= 'jpg, jpeg, gif, png, doc, docx, pdf, wps, hwp, txt, bmp, rtf, wri';
	} else {
		$JB_ALLOWED_IMG=trim(strtolower(JB_ALLOWED_IMG));
	}

	//$ext_list = explode (',',$JB_ALLOWED_EXT);
	$ext_list =preg_split ("/[\s,]+/", ($JB_ALLOWED_IMG));
	return in_array($ext, $ext_list);


}

################################################################

function JB_get_users_online_count() {

	$sql = "select count(*) as cnt from jb_sessions";
	$result = @JB_mysql_query($sql);
	$row = @mysql_fetch_row($result);

	return $row[0];



}

################################################################


function JB_save_session() {

	if (session_id()=='') return;

	global $jb_mysql_link;

	$_SESSION['count']=$_SESSION['count']+1;

	if ($_SESSION['HTTP_REFERER']=='') {
		$_SESSION['HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];
	}

	if ($_SESSION['count'] < 2) return; // the 'user' must make at least 2 requests, this keeps a lot of bots out.

	$now = (gmdate("Y-m-d H:i:s"));

	$id = (int) $_SESSION['JB_ID'];
	$domain = (isset($_SESSION['JB_Domain'])) ? $_SESSION['JB_Domain'] : '';

	// update this session


	$sql = "UPDATE `jb_sessions` SET `last_request_time`='$now', `domain`='".JB_escape_sql($domain)."', `id`='".JB_escape_sql($id)."' WHERE `session_id`='".JB_escape_sql(addslashes(session_id()))."' ";

	$result = JB_mysql_query($sql) or $DB_ERROR = mysql_error();

	if (JB_mysql_affected_rows()==0) {

		// this is a new session, insert it

		$sql = "REPLACE INTO `jb_sessions` (`session_id`, `last_request_time`, `domain`, `id`, `remote_addr`, `user_agent`, `http_referer`) VALUES ( '".JB_escape_sql(addslashes(session_id()))."', '$now', '".jb_escape_sql($domain)."', '".jb_escape_sql($id)."', '".JB_escape_sql(addslashes(JB_clean_str($_SERVER['REMOTE_ADDR'])))."', '".JB_escape_sql(addslashes(JB_clean_str(substr($_SERVER['HTTP_USER_AGENT'], 0, 255))))." count:".$_SESSION['count']."', '".JB_escape_sql(addslashes(JB_clean_str(substr($_SESSION['HTTP_REFERER'], 0, 255))))."')";
		 JB_mysql_query($sql) or $DB_ERROR = mysql_error();

	}

	JBPLUG_do_callback('do_session_house_keeping', $A=false); // added 3.6


}

#################################################################

function JB_update_all_sessions() {

	// purge old sessions
	$now = (gmdate("Y-m-d H:i:s"));
	 $session_duration = ini_get ("session.gc_maxlifetime");
   if ($session_duration==0) {

	   $session_duration=20*60;

   }

   $sql = "DELETE FROM `jb_sessions` WHERE UNIX_TIMESTAMP(DATE_SUB('$now', INTERVAL $session_duration SECOND)) > UNIX_TIMESTAMP(last_request_time) ";
   JB_mysql_query($sql) or die ($sql.mysql_error());



}

///////////////////////////////////////////////////

function JB_expire_membership(&$invoice_row, $send_email=true) {

	$now = gmdate("Y-m-d H:i:s");

	$sql = "UPDATE membership_invoices SET `status`='Expired', member_end='$now' WHERE invoice_id='".JB_escape_sql($invoice_row['invoice_id'])."' ";

	JB_mysql_query($sql) or JB_mail_error (mysql_error().$sql);

	JB_stop_membership($invoice_row);

	if ((JB_EMAIL_MEMBER_EXP_SWITCH == 'YES' ) && ($send_email)) {

		if ($invoice_row['user_type']=='E') { // employers
			$sql = "Select * from employers WHERE ID='".JB_escape_sql($invoice_row['user_id'])."'";
		} elseif ($invoice_row['user_type']=='C')   {
			$sql = "Select * from users WHERE ID='".JB_escape_sql($invoice_row['user_id'])."'";
		}
		$result = JB_mysql_query ($sql) or JB_mail_error (mysql_error().$sql);
		$e_row = mysql_fetch_array($result, MYSQL_ASSOC);

		//$invoice_row = JB_get_subscription_invoice_row ($row['invoice_id']); // reload invoice

		$template_r = JB_get_email_template (120, $e_row['lang']);
		$template = mysql_fetch_array($template_r);
		$msg = $template['EmailText'];
		$from = $template['EmailFromAddress'];
		$from_name = $template['EmailFromName'];
		$subject = $template['EmailSubject'];

		$msg = str_replace ("%FNAME%",  $e_row['FirstName'], $msg);
		$msg = str_replace ("%LNAME%", $e_row['LastName'], $msg);
		$msg = str_replace ("%SITE_NAME%", JB_SITE_NAME, $msg);
		$msg = str_replace ("%INVOICE_CODE%", "S".$invoice_row['invoice_id'], $msg);
		$msg = str_replace ("%ITEM_NAME%", $invoice_row['item_name'], $msg);
		$msg = str_replace ("%MEM_START%", JB_get_formatted_time(JB_get_local_time($invoice_row['member_date'])), $msg);
		$msg = str_replace ("%MEM_END%", JB_get_formatted_time(JB_get_local_time($invoice_row['member_end'])), $msg);
		$msg = str_replace ("%MEM_DURATION%", $invoice_row['months_duration'], $msg);

		$msg = str_replace ("%INVOICE_AMOUNT%", JB_convert_to_default_currency_formatted($invoice_row['currency_code'], $invoice_row['amount']), $msg);
		$msg = str_replace ("%PAYMENT_METHOD%", $invoice_row['payment_method'], $msg);

		$msg = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $msg);
		$msg = str_replace ("%SITE_URL%", JB_BASE_HTTP_PATH, $msg);

		$to = $e_row['Email'];
		$to_name = jb_get_formatted_name($e_row['FirstName'], $e_row['LastName']);

		$email_id=JB_queue_mail($to, $to_name, $from, $from_name, $subject, $msg, '', 120);
		JB_process_mail_queue(1, $email_id);

	}


}

///////////////////////////////////////////////////

function JB_expire_subscription(&$invoice_row, $send_email=true) {

	$now = gmdate("Y-m-d H:i:s");

	$sql = "UPDATE subscription_invoices SET `status`='Expired', subscr_end='$now' WHERE invoice_id='".JB_escape_sql($invoice_row['invoice_id'])."' ";
	@JB_mysql_query($sql) or JB_mail_error (mysql_error().$sql);

	$sql = "UPDATE `employers` SET `can_view_blocked`='N', `subscription_can_view_resume`='N', `subscription_can_post`='N', `subscription_can_premium_post`='N', views_quota=0, posts_quota=0, p_posts_quota=0, quota_timestamp=0 WHERE ID='".JB_escape_sql($invoice_row['employer_id'])."' ";
	@JB_mysql_query ($sql) or JB_mail_error(mysql_error().$sql);

	if ((JB_EMAIL_SUBSCR_EXP_SWITCH == 'YES' ) && ($send_email)) {

		$sql = "Select * from employers WHERE ID='".JB_escape_sql($invoice_row['employer_id'])."'";
		$result = JB_mysql_query ($sql) or JB_mail_error (mysql_error().$sql);
		$e_row = mysql_fetch_array($result, MYSQL_ASSOC);


		//$invoice_row = JB_get_subscription_invoice_row ($row['invoice_id']); // reload invoice


		$template_r = JB_get_email_template (130, $e_row['lang']);
		$template = mysql_fetch_array($template_r);
		$msg = $template['EmailText'];
		$from = $template['EmailFromAddress'];
		$from_name = $template['EmailFromName'];
		$subject = $template['EmailSubject'];

		$msg = str_replace ("%FNAME%",  $e_row['FirstName'], $msg);
		$msg = str_replace ("%LNAME%", $e_row['LastName'], $msg);
		$msg = str_replace ("%SITE_NAME%", JB_SITE_NAME, $msg);
		$msg = str_replace ("%INVOICE_CODE%", "S".$invoice_row['invoice_id'], $msg);
		$msg = str_replace ("%ITEM_NAME%", $invoice_row['item_name'], $msg);
		$msg = str_replace ("%SUB_START%", JB_get_formatted_time(JB_get_local_time($invoice_row['subscr_date'])), $msg);
		$msg = str_replace ("%SUB_END%", JB_get_formatted_time(JB_get_local_time($invoice_row['subscr_end'])), $msg);
		$msg = str_replace ("%SUB_DURATION%", $invoice_row['months_duration'], $msg);

		$msg = str_replace ("%INVOICE_AMOUNT%", JB_convert_to_default_currency_formatted($invoice_row['currency_code'], $invoice_row['amount']), $msg);
		$msg = str_replace ("%PAYMENT_METHOD%", $invoice_row['payment_method'], $msg);

		$msg = str_replace ("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $msg);
		$msg = str_replace ("%SITE_URL%", JB_BASE_HTTP_PATH, $msg);

		$to = $e_row['Email'];
		$to_name = jb_get_formatted_name($e_row['FirstName'], $e_row['LastName']);

		$email_id=JB_queue_mail($to, $to_name, $from, $from_name, $subject, $msg, '', 130);
		JB_process_mail_queue(1, $email_id);

	}


}

################################################
function JB_update_employer_subscriptions () {

	// get all the expired invoices
	$now = (gmdate("Y-m-d H:i:s"));

	// +3600 add 1 hour, this ads an additional hour to the subscription so that PayPal IPN and other have a chance to renew the invoice.

	$sql ="SELECT * FROM subscription_invoices WHERE ((`status`='Completed' ) OR ((`status`='Pending') AND `reason`='jb_credit_advanced')) AND  UNIX_TIMESTAMP(`subscr_end`)+3600 < UNIX_TIMESTAMP('$now')";

	$invoice_result = @JB_mysql_query($sql) or JB_mail_error (mysql_error().$sql);
	while ($invoice_row = @mysql_fetch_array($invoice_result)) {
		JB_expire_subscription($invoice_row);
	}



}
#################################################################


function JB_update_memberships () {

	// get all the expired invoices
	$now = (gmdate("Y-m-d H:i:s"));

	// +3600 add 1 hour, this ads an additional hour to the membership so that PayPal IPN and other have a chance to renew the invoice.

	$sql ="SELECT * FROM membership_invoices WHERE ((`status`='Completed' ) OR ((`status`='Pending') AND `reason`='jb_credit_advanced')) AND  (UNIX_TIMESTAMP(`member_end`)+3600 < UNIX_TIMESTAMP('$now')) AND months_duration > 0 ";

	$invoice_result = JB_mysql_query($sql) or JB_mail_error (mysql_error().$sql);
	while ($invoice_row = mysql_fetch_array($invoice_result)) {
		JB_expire_membership($invoice_row);
	}

}

################################################




function JB_do_house_keeping () {

	global $jb_mysql_link;

	if (defined('NO_HOUSE_KEEPING'))  { return; }

	$unix_time = time();

	// get the time of last run housekeep
	$sql = "SELECT * FROM `jb_variables` where `key` = 'LAST_HOUSEKEEP_RUN' ";
	if (!$result = JB_mysql_query($sql)) {
		return false;
	}
	$t_row = @mysql_fetch_array($result, MYSQL_ASSOC);


	// Poor man's lock
	//$sql = "LOCK TABLES `jb_variables` WRITE";
	//JB_mysql_query($sql) 

	$sql = "UPDATE `jb_variables` SET `val`='YES' WHERE `key`='HOUSEKEEP_RUNNING' AND `val`='NO' ";
	$result = @JB_mysql_query($sql) or $DB_ERROR = mysql_error();
	if (JB_mysql_affected_rows()==0) { // it is running in another proccess

		// make sure it cannot be locked for more than 30 secs
		// This is in case the proccess fails inside the lock
		// and does not release it.

		if ($unix_time > $t_row['val']+30) { // 30

			// release the lock
			$sql = "UPDATE `jb_variables` SET `val`='NO' WHERE `key`='HOUSEKEEP_RUNNING' ";
			$result = @JB_mysql_query($sql) or $DB_ERROR = mysql_error();

			// update timestamp
			$sql = "REPLACE INTO jb_variables (`key`, `val`) VALUES ('LAST_HOUSEKEEP_RUN', '$unix_time')  ";
			$result = @JB_mysql_query($sql) or $DB_ERROR = mysql_error();
		}


		return; // this function is already executing in another process.
	}

	///////////////////////////////////////////////////////////
	// Start Critical Section - is only executed in one process at at time
	///////////////////////////////////////////////////////////

	JB_save_session(); // update sessions on every request

	JBPLUG_do_callback('house_keeping_critical_section', $A = false); // added in 3.6.1



	if ($unix_time > $t_row['val']+60) { // did 1 minute elapse since last run? 60

		// do stuff here -

		JBPLUG_do_callback('do_house_keeping', $A = false); // added in 3.5.0

		JB_update_all_sessions();

		// update timestamp
		$sql = "REPLACE INTO jb_variables (`key`, `val`) VALUES ('LAST_HOUSEKEEP_RUN', '$unix_time')  ";
		$result = @JB_mysql_query($sql) or $DB_ERROR = mysql_error();


		if (!defined('NO_HOUSE_KEEPING') && (JB_CRON_EMULATION_ENABLED=='YES')) {
			set_time_limit(40);
			JB_do_cron_job();
		}


	}


	// release the poor man's lock
	$sql = "UPDATE `jb_variables` SET `val`='NO' WHERE `key`='HOUSEKEEP_RUNNING' ";
	JB_mysql_query($sql) or die(mysql_error());

	/////////////////////////////////////////////////////////////////
	// End Critical Section

}
/*

*/

function jb_exec($command, &$out) {

	$command = escapeshellcmd($command);

	$disabled = explode(', ', ini_get('disable_functions'));
	if (!in_array('exec', $disabled)) { // not disabled?
		@exec ($command, $out);
	}

	
}


function JB_do_cron_job() {

	if (is_numeric(JB_CRON_LIMIT)) {

		// check load averge. Do not do cron if load avg is larger than the limit
		JB_exec ("w", $out);
		preg_match('#load average: (\d+\.\d+)#', $out[0], $m);
		$load_av = $m[1];
		if ($load_av > JB_CRON_LIMIT) {
				return false;
		}
	}


	$unix_time = time();


	$dir = JB_basedirpath();

	// process the email queue

	JB_process_mail_queue(JB_EMAILS_PER_BATCH);

	// scan inbox

	if (!function_exists('JB_monitor_mail_box')) {
		require_once($dir."include/mail_monitor_functions.php");
	}
	JB_load_monitor_constants();
	if (MON_ENABLED=='YES') {
		JB_monitor_mail_box();
	}



	// get the time of last HOURLY run
	$sql = "SELECT * FROM `jb_variables` where `key` = 'LAST_HOURLY_RUN' ";
	$result = @JB_mysql_query($sql) or $DB_ERROR = mysql_error();
	$t_row = @mysql_fetch_array($result, MYSQL_ASSOC);

	JBPLUG_do_callback('do_cron_job', $A = false);

	// queue email alerts

	if ($unix_time > $t_row['val']+3600) { // did 1 hour elapse since last run? 3600

		if (JB_JOB_ALERTS_ENABLED=='YES') {
			// need to init jobs tag_to_field
			define ('JB_JOB_ALERTS_DO_SEND', true);
			require ($dir.'admin/jobalerts.php');

		}

		if (JB_RESUME_ALERTS_ENABLED=='YES') {
			// need to init resume tag_to_field
			define ('JB_RESUME_ALERTS_DO_SEND', true);
			require ($dir.'admin/resumealerts.php');

		}


		JB_update_employer_subscriptions ();
		JB_update_memberships ();

		// update timestamp
		$sql = "REPLACE INTO jb_variables (`key`, `val`) VALUES ('LAST_HOURLY_RUN', '$unix_time')  ";
		$result = @JB_mysql_query($sql) or $DB_ERROR = mysql_error();

		if (!function_exists('JB_search_category_tree_for_posts')) {
			require_once ($dir."include/posts.inc.php");
		}

		JB_expire_posts('PREMIUM');
		JB_expire_posts('STANDARD');


		JB_cache_del_keys_for_all_cats(1);
		JB_cache_del_keys_for_all_cats(2);
		JB_cache_del_keys_for_all_cats(3);
		JB_cache_del_keys_for_all_cats(4);
		JB_cache_del_keys_for_all_cats(5);

		//
		JB_update_post_count();
		JB_update_resume_count();
		JB_update_profile_count();
		JB_update_employer_count();
		JB_update_user_count();

		if (EMAIL_URL_SHORTEN=='YES') {
			JB_expire_short_URLs();
		}

		// run the import tool

		JB_process_xml_import();

		JBPLUG_do_callback('do_cron_hourly', $A = false);

	}


}

function JB_process_xml_import() {

	$sql = "SELECT feed_id FROM xml_import_feeds WHERE `cron`='Y' AND `status`='READY' AND `pickup_method`!='POST' ";
	$result = jb_mysql_query($sql);
	if (mysql_num_rows($result)>0) {
		require_once (dirname(__FILE__).'/xml_import_functions.php');
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$importer = new xmlFeedImporter($row['feed_id']);
			$importer->import();
		}
	}
}

////////////
// $timestart must be init before as a global with $timestart=microtime();
function JB_get_time_diff() {

	static $last;

	global $timestart;
	$timeend = microtime();

	$total = number_format(((substr($timeend,0,9)) + (substr($timeend,-10)) - (substr($timestart,0,9)) - (substr($timestart,-10))),4);
	if ($last)
		echo ' ('.($total-$last).') ';
	$last = $total;
	return $total;

}

//////////

function JB_get_post_count($type='') {

	static $post_stats;

	if (!isset($post_stats)) {
		// perhaps it's in the cache?
		$post_stats = jb_cache_get('post_stats');
	}

	if (isset($post_stats[$type])) {
		return $post_stats[$type];
	} elseif (isset($post_stats['AP'])) {
		return $post_stats['AP'];
	}


	switch ($type) {
		case 'AP':
			$sql = "SELECT val FROM jb_variables  WHERE `key`='POST_COUNT_AP' ";
			break;
		case 'PAP':
			$sql = "SELECT val FROM jb_variables  WHERE `key`='POST_COUNT_PAP' ";
			break;
		case 'NA':
			$sql = "SELECT val FROM jb_variables  WHERE `key`='POST_COUNT_NA' ";
			break;
		case 'WA':
			$sql = "SELECT val FROM jb_variables  WHERE `key`='POST_COUNT_WA' ";
			break;
		case 'EX':
			$sql = "SELECT val FROM jb_variables  WHERE `key`='POST_COUNT_EX' ";
			break;
		case 'SAP':
			$sql = "SELECT val FROM jb_variables  WHERE `key`='POST_COUNT_SAP' ";
			break;
		default:
			$sql = "SELECT val FROM jb_variables  WHERE `key`='POST_COUNT_AP' ";
			break;

	}

	$result = JB_mysql_query($sql);

	if (mysql_num_rows($result)>0) {
		$row = mysql_fetch_row($result);
		$post_stats[$type] = $row[0];
		jb_cache_set('post_stats', $post_stats); // update the cache
		return $post_stats[$type];
	} else {
		return null;
	}


}

function JB_get_resume_count($type='') {


	switch ($type) {
		case 'ACT':
			$sql = "SELECT val FROM jb_variables  WHERE `key`='ACT_RESUME_COUNT' ";
			break;
		case 'ALL':
			$sql = "SELECT val FROM jb_variables  WHERE `key`='RESUME_COUNT' ";
			break;
		default:
			$sql = "SELECT val FROM jb_variables  WHERE `key`='RESUME_COUNT' ";
			break;

	}

	JB_mysql_query ($sql);

	$result = JB_mysql_query($sql);

	if (mysql_num_rows($result)>0) {
		$row = mysql_fetch_row($result);
		return $row[0];
	} else {
		return null;
	}

}

function JB_get_profile_count() {

	$sql = "SELECT val FROM jb_variables  WHERE `key`='PROFILE_COUNT' ";
	JB_mysql_query ($sql);
	$result = JB_mysql_query($sql);
	$row = mysql_fetch_row($result);
	return $row[0];

}

function JB_get_employer_count() {
	$sql = "SELECT val FROM jb_variables  WHERE `key`='EMPLOYER_COUNT' ";
	JB_mysql_query ($sql);
	$result = JB_mysql_query($sql);
	$row = mysql_fetch_row($result);
	return $row[0];


}

function JB_get_user_count() {

	$sql = "SELECT val FROM jb_variables  WHERE `key`='USER_COUNT' ";
	JB_mysql_query ($sql);
	$result = JB_mysql_query($sql);
	$row = mysql_fetch_row($result);
	return $row[0];

}

// update counters
/*
string $show
AP - Approved (expired & not expired)
PAP - Approved premium posts, not expired
SAP - Approved, not premium, not expired
NA - Not approved by Admin for a reason, not expired
WA - New posts waiting to be approved (in Admn), not expired
EX - All Expired

*/

function JB_update_post_count() {

	$post_stats = array();


	$now = (gmdate("Y-m-d H:i:s"));
	//Post Count AP - all approved posts
	$sql = "SELECT count(*) FROM posts_table WHERE expired='N' AND approved='Y' ";
	$result = JB_mysql_query($sql) or die( mysql_error());
	$row = mysql_fetch_row($result);
	$pc = $row[0];
	$post_stats['AP'] = $row[0];
	$sql = "REPLACE INTO jb_variables  (`key`, `val`) VALUES ('POST_COUNT_AP','".jb_escape_sql($row[0])."') ";
	$result = JB_mysql_query($sql) or die( mysql_error());
	// non approved NA
	$sql = "SELECT count(*) FROM posts_table WHERE expired='N' AND approved='N'  AND `reason`!=''";
	$result = JB_mysql_query($sql) or die( mysql_error());
	$row = mysql_fetch_row($result);
	$post_stats['NA'] = $row[0];
	$sql = "REPLACE INTO jb_variables  (`key`, `val`) VALUES ('POST_COUNT_NA','".jb_escape_sql($row[0])."') ";
	$result = JB_mysql_query($sql) or die( mysql_error());
	// premium approved PAP
	$sql = "SELECT count(*) FROM posts_table WHERE expired='N' AND post_mode='premium' AND approved='Y' ";
	$result = JB_mysql_query($sql) or die( mysql_error());
	$row = mysql_fetch_row($result);
	$post_stats['PAP'] = $row[0];
	$sql = "REPLACE INTO jb_variables  (`key`, `val`) VALUES ('POST_COUNT_PAP','".jb_escape_sql($row[0])."') ";
	$result = JB_mysql_query($sql) or die( mysql_error());

	// standard approved SAP
	$sql = "SELECT count(*) FROM posts_table WHERE expired='N' AND post_mode!='premium' AND approved='Y' ";
	$result = JB_mysql_query($sql) or die( mysql_error());
	$row = mysql_fetch_row($result);
	$post_stats['SAP'] = $row[0];
	$sql = "REPLACE INTO jb_variables  (`key`, `val`) VALUES ('POST_COUNT_SAP','".jb_escape_sql($row[0])."') ";
	$result = JB_mysql_query($sql) or die( mysql_error());
	// waiting WA
	$sql = "SELECT count(*) FROM posts_table WHERE  expired='N' AND reason='' AND approved='N' ";
	$result = JB_mysql_query($sql) or die( mysql_error());
	$row = mysql_fetch_row($result);
	$post_stats['WA'] = $row[0];
	$sql = "REPLACE INTO jb_variables  (`key`, `val`) VALUES ('POST_COUNT_WA','".jb_escape_sql($row[0])."') ";
	$result = JB_mysql_query($sql) or die( mysql_error());
	// expired EX
	$sql = "SELECT count(*) FROM posts_table WHERE expired='Y' ";
	$result = JB_mysql_query($sql) or die( mysql_error());
	$row = mysql_fetch_row($result);
	$post_stats['EX'] = $row[0];
	$sql = "REPLACE INTO jb_variables  (`key`, `val`) VALUES ('POST_COUNT_EX','".jb_escape_sql($row[0])."') ";
	$result = JB_mysql_query($sql) or die( mysql_error());

	Jb_cache_set('post_stats', $post_stats);

	return $pc;


}

//////////

function JB_update_resume_count() {


	$sql = "SELECT count(*) FROM resumes_table WHERE  approved='Y' AND status='ACT' "; // employers list
	$result = @JB_mysql_query($sql) or die( mysql_error());
	$row = @mysql_fetch_row($result);
	$sql = "REPLACE INTO jb_variables  (`key`, `val`) VALUES ('ACT_RESUME_COUNT','".jb_escape_sql($row[0])."') ";
	$result = @JB_mysql_query($sql) or die( mysql_error());

	$sql = "SELECT count(*) FROM resumes_table   "; // used in admins list
	$result = @JB_mysql_query($sql) or die( mysql_error());
	$row = @mysql_fetch_row($result);
	$sql = "REPLACE INTO jb_variables  (`key`, `val`) VALUES ('RESUME_COUNT','".jb_escape_sql($row[0])."') ";
	$result = @JB_mysql_query($sql) or die( mysql_error());

}

///////////
function JB_update_profile_count() {

	$sql = "SELECT count(*) FROM resumes_table";
	$result = @JB_mysql_query($sql) or die( mysql_error());
	$row = @mysql_fetch_row($result);
	$sql = "REPLACE INTO jb_variables  (`key`, `val`) VALUES ('PROFILE_COUNT','".jb_escape_sql($row[0])."') ";
	$result = @JB_mysql_query($sql) or die( mysql_error());

}
////////////
function JB_update_employer_count() {

	$sql = "SELECT count(*) FROM employers";
	$result = @JB_mysql_query($sql) or die( mysql_error());
	$row = @mysql_fetch_row($result);
	$sql = "REPLACE INTO jb_variables  (`key`, `val`) VALUES ('EMPLOYER_COUNT','".jb_escape_sql($row[0])."') ";
	$result = @JB_mysql_query($sql) or die( mysql_error());

}
/////////
function JB_update_user_count() {

	$sql = "SELECT count(*) FROM users";
	$result = @JB_mysql_query($sql) or die( mysql_error());
	$row = @mysql_fetch_row($result);
	$sql = "REPLACE INTO jb_variables  (`key`, `val`) VALUES ('USER_COUNT','".jb_escape_sql($row[0])."') ";
	$result = @JB_mysql_query($sql) or die( mysql_error());

}

//////////////////
// convert decimal string to a hex string.
function JB_decimal_to_hex($decimal) {
	$hex = dechex($decimal);
	// pad it with zeros
	return sprintf('%04s', $hex );
}

function JB_htmlent_to_hex ($str) {
// convert html Unicode entities to Javascript Unicode entities &#51060 to \u00ED
	return preg_replace ("/&#([0-9A-z]+);/e", "'\\\u'.JB_decimal_to_hex('\\1')" , $str);
}



function JB_js_out_prep($str) {
	$str = addslashes($str);
	$str = JB_htmlent_to_hex($str);
	return $str;
}

/*
Payment log functions
*/

///////////////////////////////////

function JB_payment_log_entry_db($log_entry, $module) {

	$now = (gmdate("Y-m-d H:i:s"));
	$log_entry = trim($log_entry);
	$sql = "REPLACE INTO payment_log (`date`, `module`, `log_entry`) VALUES ('$now', '".jb_escape_sql($module)."', '".JB_escape_sql($log_entry)."' )";
	JB_mysql_query ($sql) or die (mysql_error());

	// delete older than 7 days
	$sql = "DELETE FROM payment_log where DATE_SUB('$now',INTERVAL 7 DAY) >=  `date` ";
	JB_mysql_query ($sql) or die (mysql_error());


}

function JB_payment_log_fetch_db($module) {

	$sql = "SELECT * FROM payment_log WHERE module='".JB_escape_sql($module)."' ";
	$result = JB_mysql_query ($sql) or die (mysql_error());
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$data = $data.$row['date']." - ".$row['module']." - ".$row['log_entry']."\n";
	}
	return $data;

}

function JB_payment_log_clear_db($module) {

	$sql = "DELETE FROM payment_log WHERE module='".JB_escape_sql($module)."' ";
	$result = JB_mysql_query ($sql) or die (mysql_error());

	$sql = "ALTER table payment_log auto_increment = 0; ";
	$result = JB_mysql_query ($sql) or die (mysql_error());


}


###########################################

function JB_ISODate_to_SCWDate($ISODate) {

	if (($ISODate=='') || ($ISODate == '0000-00-00 00:00:00')) return false;

	$temp_date = JB_SCW_DATE_FORMAT;
	preg_match('#(\d+)-(\d+)-(\d+)#', $ISODate, $m); // match 3 digits

	$year=$m[1];
	$month=$m[2];
	$day=$m[3];

	$temp_date = preg_replace("/Y+/i", $year, $temp_date);
	$temp_date = preg_replace("/M+/i", $month, $temp_date);
	$temp_date = preg_replace("/D+/i", $day, $temp_date);


	return $temp_date;


}

function JB_SCWDate_to_ISODate($SCWDate) {

	if ($SCWDate=='') return false;

	$temp_date = JB_SCW_DATE_FORMAT;

	preg_match('#(\d+).(\d+).(\d+)#', $SCWDate, $m); // match 3 digits


	$temp_parts = explode( '-', $temp_date); // tokenize

	$i=1;
	while (sizeof($temp_parts)>0) { // map the digits with the tokens
		$part = array_shift($temp_parts);

		if (preg_match('#d#i', $part)) { // day
			$day = $m[$i]; $i++;
		}
		if (preg_match('#m#i', $part)) { // month
			$month = $m[$i]; $i++;
		}
		if (preg_match('#y#i', $part)) { // year
			$year = $m[$i]; $i++;
		}
	}
	$date = "$year-$month-$day";

	return $date;


}
/////////////////////////////////////////
function elapsedtime($sec){
	$days  = floor($sec / 86400);
	$hrs   = floor(bcmod_wrapper($sec,86400)/3600);
	$mins  = round(bcmod_wrapper(bcmod_wrapper($sec,86400),3600)/60);
	if($days > 0) $tstring = $days . "d, ";
	if($hrs  > 0) $tstring = $tstring . $hrs . "h, ";
	$tstring = "" . $tstring . $mins . "m";
	return $tstring;
}

/////////////////////////////////////////

function bcmod_wrapper( $x, $y ) {
	if (function_exists('bcmod')) {
		return bcmod($x, $y);
	}
	// how many numbers to take at once? carefull not to exceed (int)
	$take = 5;
	$mod = '';

	do {
	   $a = (int)$mod.substr( $x, 0, $take );
	   $x = substr( $x, $take );
	   $mod = $a % $y;
	}
	while ( strlen($x) );

	return (int)$mod;
}

/////////////////////////////////////////


function JB_merge_language_files ($force_update=false) {

	if (JB_DEMO_MODE=='YES') {
		return;
	}

	global $label;

	// load in the main english_default labels
	$source_label = array();
	include_once (jb_get_english_default_dir().'english_default.php'); // the master lang/english_default
	$source_label = array_merge ($source_label, $label); // default english labels
	unset ($label); $label = array();
	$last_mtime = filemtime (jb_get_english_default_dir().'english_default.php');

	// load the english_default.php labels for all themes
	//
	global $JB_LANG_THEMES;
	$themes = $JB_LANG_THEMES;
	if (isset($_REQUEST['jb_theme'])) { // Admin->Main Config, Admin-> Languagess
		if (isset($_REQUEST['lang_code'])) {
			$lang = $_REQUEST['lang_code']; // comes from Admin->Languages
		} else {
			$lang = $_SESSION['LANG'];
		}
		$themes[$lang] = $_REQUEST['jb_theme'];
	}
	$themes = array_unique($themes);
	
	// get the english_default.php for each theme 

	foreach ($themes as $key=>$theme) {
		$theme_path = JB_get_theme_dir().$theme.'/';
	
		if (file_exists($theme_path.'lang/english_default.php')) {

			include ($theme_path.'lang/english_default.php');

			$source_label = array_merge ($source_label, $label); // default english labels
			unset ($label); $label = array();
			$m_time =  filemtime ($theme_path."lang/english_default.php");
			if ($m_time > $last_mtime) {
				$last_mtime = $m_time;
			}

		}

	}

	if ($force_update) {
		$last_mtime = time();
	}

	// Now we should have all the source labels in $source_label and
	// last modification time in $last_mtime

	// Grab all the languages installed
	$sql = "SELECT * FROM lang  ";
	$result = JB_mysql_query ($sql) or die (mysql_error());


	// Now merge the english_default.php strings with the language files

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		// now that we have all the source labels, we can merge them with
		// the langauge file. Any key that is present in the source, but
		// not present

		if (is_writable(jB_get_lang_dir().$row['lang_filename'])) {

			if ($last_mtime > filemtime (jB_get_lang_dir().$row['lang_filename'])) {
				echo "Merging language strings for ".jb_escape_html($row['lang_filename']).".. <br>";
				// Now merge the english defaults with the langauge file
				include (jB_get_lang_dir().$row['lang_filename']); // customized labels
				$dest_label = array_merge($source_label, $label);
				$label = null;
				// write out the new file:

				$out = "<?php\n";
				$out .= "///////////////////////////////////////////////////////////////////////////\n";
				$out .= "// IMPORTANT NOTICE\n";
				$out .= "///////////////////////////////////////////////////////////////////////////\n";
				$out .= "// This file was generated by a script!\n";
				$out .= "// (JB_merge_language_files() function)\n";
				$out .= "// Please do not edit the language files by hand\n";
				$out .= "// - please always use the Language Translation / Editing tool found\n";
				$out .= "// in Admin->Languages\n";
				$out .= "// To add a new phrase for the \$label, please edit english_default.php, and\n";
				$out .= "// then vist Admin->Main Summary where the language files will be\n";
				$out .= "// automatically merged with this file.\n";
				$out .= "///////////////////////////////////////////////////////////////////////////\n";


				foreach ($dest_label as $key=>$val) {
					$val = str_replace("'", "\'", $val );
					$out .= "\$label['$key']='". JB_clean_str($val)."'; \n";
				}
				$out .= "?>\n";
				$handler = fopen (jB_get_lang_dir().$row['lang_filename'], "w");
				fputs ($handler, $out);
				fclose ($handler);

			}

		} else {
			echo "<font color='red'><b>- ".jB_get_lang_dir().$row['lang_filename']." file is not writable. Give write permissions (".decoct(JB_NEW_FILE_CHMOD).") to ".jB_get_lang_dir().$row['lang_filename']." file and then disable & re-enable this plugin</b></font><br>";
		}


	}

	if ($out) echo " Done.<br>";


}


###################################

function JB_show_lang_permission_warning() {

	global $ACT_LANG_FILES; // active language files

	// Check language file permissions

	// compute the $lang_dir

	$lang_dir = JB_get_lang_dir();
	//$lang_dir = $lang_dir."lang/";

	foreach ($ACT_LANG_FILES as $file) {

	///	$fp = @fopen ($lang_dir.$file, 'a');
		if (!is_writable($lang_dir.$file)) {
			echo "<font color='maroon'>- Important: It looks like the language file, <b>$lang_dir$file</b> does not have permissions for writing. Please give this file permissions for writing before enabling any of the plugins, adding new themes or editing the language using the Translation / Editing tool.</font><br>";
		}

	}


}

////////////////////

function jb_get_variable($var) {
	
	$sql = "SELECT * from jb_variables WHERE `key`='".jb_escape_sql($var)."' ";
	$result = jb_mysql_query($sql);
	if ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		return $row['val'];
	}

}



###################################################

/*
A function to convert UTF-8 to HTML entities
Here you can see this function in action
http://www.jamit.com.au/convert.php

*/
function JB_utf8_to_html ($data) {
	if (function_exists('mb_encode_numericentity')) { // use the mbstring functions
		return mb_encode_numericentity ($data, array (0xff, 0xffff, 0, 0xffff), 'UTF-8');
	}
    return preg_replace("/([\\xC0-\\xF7]{1,1}[\\x80-\\xBF]+)/e", '_utf8_to_html("\\1")', $data);
}

function _utf8_to_html ($data) {


    $ret = 0;

    foreach ((str_split(strrev(chr((ord($data{0}) % 252 % 248 % 240 % 224 % 192) + 128) . substr($data, 1)))) as $k => $v)
        $ret += (ord($v) % 128) * pow(64, $k);
	if ($ret<256) return chr($ret); // no need to convert to entities
    return "&#$ret;";
}

if(!function_exists('str_split')) {
	function str_split($string,$string_length=1) {
		if(strlen($string)>$string_length || !$string_length) {
			do {
				$c = strlen($string);
				$parts[] = substr($string,0,$string_length);
				$string = substr($string,$string_length);
			} while($string !== false);
		} else {
			$parts = array($string);
		}
		return $parts;
	}
}


// Change a value in the configuration for config.php
// $config_str - contents of config.php in a single string
// $const_name - name of constant to change
// $const_val - the value to set
// Returns the new $config_str after replacing the constant with a new value
// Returned string can be saved as config.php

function JB_change_config_value($config_str, $const_name, $const_val='') {

	$const_val = str_replace('\\', '\\\\', $const_val); // escape '\'

	$new_config_str = preg_replace ( "#\s*define\('".$const_name."'.+\(?'([^']*)'\)?\);#Uims", "\ndefine('".$const_name."', '".str_replace("'", "\\'", $const_val)."');", $config_str) ; // // s = dot matches all chrs, m = multi line, U = ungreedy, i

	return $new_config_str;


}


function jb_random_string($len = 32, $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_+=\|[{]};:,<.>~') {
	$chars = str_shuffle($chars);
	$last = strlen($chars)-1;
	$str = '';
	//mt_srand($this->make_seed());
	for ($n = 0; $n < $len; $n++) {
		$str .= $chars[mt_rand(0, $last)];
	}
	return $str;
}


?>
