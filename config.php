<?php
if (defined('E_DEPRECATED')) {
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
} else {
	error_reporting(E_ALL ^ E_NOTICE);
}
 

define('JB_SITE_NAME',  'Jamit Job Board 3.6.12');
define('JB_SITE_HEADING',  'Welcome to Jamit Job Board');
define('JB_SITE_DESCRIPTION',  'Welcome to Jamit Job Board. Please configure me by going to Admin->Main Config.');
define('JB_SITE_KEYWORDS', 'jamit job board software');
define('JB_SITE_CONTACT_EMAIL', 'test@example.com');
define('JB_ADMIN_PASSWORD', 'ok');
define('JB_THEME', 'classic');

define('JB_CRON_EMULATION_ENABLED', 'NO');
define('JB_CRON_HTTP_ALLOW', 'YES');
define('JB_CRON_HTTP_USER', '');
define('JB_CRON_HTTP_PASS', '');

define('JB_CACHE_ENABLED', 'NO');
define('JB_USE_SERIALIZE', '');

define('JB_PLUGIN_SWITCH', 'YES');
// Paths and Locations

define('JB_CANDIDATE_FOLDER', 'myjobs/');
define('JB_EMPLOYER_FOLDER', 'employers/');


define('JB_IMG_MAX_WIDTH',  '200');
define('JB_KEEP_ORIGINAL_IMAGES',  'YES');
define('JB_BIG_IMG_MAX_WIDTH',  '1000');
define('JB_IMG_PATH',  'C:/apache/htdocs/test/upload_files/images/');
define('JB_FILE_PATH',  'C:/apache/htdocs/test/upload_files/docs/');
define('JB_IM_PATH',  '');
define('JB_USE_GD_LIBRARY',  'YES');

define('JB_RSS_FEED_PATH',  'C:/apache/htdocs/test/rss.xml');
define('JB_RSS_FEED_LOGO',  'rss_logo.png');

define('JB_NEW_FILE_CHMOD',  0666);
define('JB_NEW_DIR_CHMOD',  0777);



if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) {
//if (true) {
	define('JB_SITE_LOGO_URL',  str_replace('http:', 'https:', 'http://www.jamit.com.au/images/logo.gif'));
	define('JB_FILE_HTTP_PATH',  str_replace('http:', 'https:', 'http://localhost/test/upload_files/docs/'));
	define('JB_BASE_HTTP_PATH',  str_replace('http:', 'https:', 'http://localhost/jamitce/'));
	define('JB_IMG_HTTP_PATH',  str_replace('http:', 'https:', 'http://localhost/test/upload_files/images/'));
	
} else {
	
	define('JB_SITE_LOGO_URL', 'http://www.jamit.com.au/images/logo.gif');
	define('JB_FILE_HTTP_PATH', 'http://localhost/test/upload_files/docs/');
	define('JB_BASE_HTTP_PATH',  'http://localhost/jamitce/');
	define('JB_IMG_HTTP_PATH', 'http://localhost/test/upload_files/images/');
}

define('JB_NAME_FORMAT', 'F L');
// categories

define('JB_CAT_PATH_ONLY_LEAF', 'NO');
define('JB_CAT_RSS_SWITCH', 'YES');
define('JB_SHOW_SUBCATS', '5');
define('JB_CAT_COLS_FP', '1');
define('JB_CAT_COLS', '3');
define('JB_FORMAT_SUB_CATS', 'YES');
define('JB_SUB_CATEGORY_COLS', '1');
define('JB_CAT_NAME_CUTOFF', 'YES');
define('JB_CAT_NAME_CUTOFF_CHARS', '25');
define('JB_INDENT_CATEGORY_LIST', 'YES');
define('JB_CAT_SHOW_OBJ_COUNT', 'YES');
define('JB_MOD_REWRITE_REMOVE_ACCENTS', 'NO');
define('JB_CAT_MOD_REWRITE', 'YES');
define('JB_JOB_MOD_REWRITE', 'YES');
define('JB_PRO_MOD_REWRITE', 'YES');
define('JB_MOD_REWRITE_DIR', 'category/');
define('JB_MOD_REWRITE_JOB_DIR', 'job/%CLASS%/%TITLE%/%DATE%/');
define('JB_MOD_REWRITE_PRO_DIR', 'profile/');
define('JB_JOB_PAGES_MOD_REWRITE', 'YES');
define('JB_MOD_REWRITE_JOB_PAGES_PREFIX', 'page');
// data cleaning
define('JB_STRIP_HTML', 'YES');
define('JB_STRIP_LATIN1', 'NO');
define('JB_BREAK_LONG_WORDS', 'YES');
define('JB_LNG_MAX', '75');
define('JB_CLEAN_STRINGS', 'NO');
define('JB_ALLOWED_EXT', 'doc, docx, pdf, wps, hwp, txt, rtf, wri');
define('JB_ALLOWED_IMG', 'jpg, jpeg, gif, png, bmp');
define('JB_MAX_UPLOAD_BYTES', '3097152');

// features
define('JB_CAN_LANG_ENABLED', 'NO');
define('JB_EMP_LANG_ENABLED', 'NO');
define('JB_MAP_DISABLED', 'GMAP');

define('JB_GMAP_LOCATION', 'http://maps.google.com.au/maps?f=q&source=s_q&hl=en&geocode=&q=usa&sll=-25.335448,135.745076&sspn=36.459955,56.425781&ie=UTF8&hq=&hnear=United+States&ll=37.160317,-95.800781&spn=63.377251,112.851563&z=3');
define('JB_GMAP_LAT', '37.160317');
define('JB_GMAP_LNG', '-95.800781');
define('JB_GMAP_ZOOM', '4');
define('JB_GMAP_SHOW_IF_MAP_EMPTY', '');
define('JB_PIN_IMAGE_FILE', 'pin-yellow2.gif');
define('JB_MAP_IMAGE_FILE', 'map-small.jpg');
define('JB_PREVIEW_RESUME_IMAGE', 'YES');
define('JB_BAD_WORD_FILTER', 'NO');
define('JB_BAD_WORDS', '');
define('JB_ONLINE_APP_ENABLED', 'YES');
define('JB_APP_CHOICE_SWITCH', 'YES');

define('JB_RESUME_REPLY_ENABLED', 'YES');
define('JB_FIELD_BLOCK_APP_SWITCH', 'NO');

define('JB_JOB_ALERTS_ENABLED', 'YES');
define('JB_RESUME_ALERTS_ENABLED', 'YES');

define('JB_JOB_ALERTS_DAYS', '1');

define('JB_RESUME_ALERTS_DAYS', '1');
define('JB_TAF_ENABLED', 'YES');
define('JB_SAVE_JOB_ENABLED', 'YES');
define('JB_SHOW_PREMIUM_LIST', 'YES');
define('JB_DONT_REPEAT_PREMIUM', '');
define('JB_ONLINE_APP_SIGN_IN', 'YES');
define('JB_ONLINE_APP_EMAIL_ADMIN', 'YES');
define('JB_ONLINE_APP_EMAIL_PREMIUM', 'YES');
define('JB_ONLINE_APP_EMAIL_STD', 'YES');
define('JB_ONLINE_APP_REVEAL_PREMIUM', '');
define('JB_ONLINE_APP_REVEAL_STD', '');
define('JB_ONLINE_APP_REVEAL_RESUME', '');
define('JB_TAF_SIGN_IN', '');
define('JB_ANON_RESUME_ENABLED', '');
define('JB_FIELD_BLOCK_SWITCH', 'YES');
define('JB_MEMBER_FIELD_SWITCH', 'NO');
define('JB_MEMBER_FIELD_IGNORE_PREMIUM', 'NO');
define('JB_NEED_SUBSCR_FOR_REQUEST', '');
define('JB_JOB_ALERTS_ACTIVE_DAYS', '30');
define('JB_JOB_ALERTS_ITEMS', '10');
define('JB_RESUME_ALERTS_ACTIVE_DAYS', '30');
define('JB_RESUME_ALERTS_ITEMS', '10');
define('JB_RESUME_ALERTS_SUB_IGNORE', '');
define('JB_CODE_ORDER_BY', '');
// Database
define('JB_MYSQL_HOST', '127.0.0.1');
define('JB_MYSQL_USER', 'root');
define('JB_MYSQL_PASS', 'ok');
define('JB_MYSQL_DB', 'board11');
//date & time
define('JB_DATE_FORMAT', 'Y-M-d');
define('JB_GMT_DIF', '10');

define('JB_SCW_INPUT_SEQ', 'dMY');
define('JB_SCW_DATE_FORMAT', 'dd-MM-YYYY');


define('JB_DATE_INPUT_SEQ', 'YMD');
// Accounts permissions
define('JB_CA_NEEDS_ACTIVATION',  'AUTO');
define('JB_EM_NEEDS_ACTIVATION',  'AUTO');
define('JB_FREE_POST_LIMIT', 'NO');
define('JB_FREE_POST_LIMIT_MAX', '10');
define('JB_BEGIN_PREMIUM_CREDITS', '0');
define('JB_BEGIN_STANDARD_CREDITS', '0');
define('JB_ALLOW_ADMIN_LOGIN', 'NO');


// menu
define('JB_CANDIDATE_MENU_TYPE', 'JS');
define('JB_EMPLOYER_MENU_TYPE', 'JS');
//search form
define('JB_SEARCH_FORM_LAYOUT', 'T');

define('JB_SUBSCRIPTION_FEE_ENABLED', 'NO');
define('JB_POSTING_FEE_ENABLED', 'YES');
define('JB_PREMIUM_AUTO_UPGRADE', '');

define('JB_CANDIDATE_MEMBERSHIP_ENABLED',  'NO');
define('JB_EMPLOYER_MEMBERSHIP_ENABLED',  'NO');
define('JB_PREMIUM_POSTING_FEE_ENABLED', 'YES');
define('JB_INVOICE_ID_START', '100');
define('JB_DEFAULT_PAY_METH', 'PayPal');

// Posts...
define('JB_POSTS_NEED_APPROVAL', 'NO');
define('JB_POSTS_PER_PAGE', '10');
define('JB_POSTS_PER_RSS', '30');
define('JB_PREMIUM_POSTS_PER_PAGE', '5');
define('JB_PREMIUM_POSTS_LIMIT', 'YES');
define('JB_P_POSTS_DISPLAY_DAYS', '90');

define('JB_POSTS_DISPLAY_DAYS', '90');
define('JB_POSTS_DESCRIPTION_CHARS', '150');
define('JB_POSTS_SHOW_DESCRIPTION', 'YES');
define('JB_POSTS_SHOW_JOB_TYPE', 'YES');
define('JB_POSTS_SHOW_POSTED_BY', 'YES');
define('JB_POSTS_SHOW_POSTED_BY_BR', 'YES');
define('POSTS_SHOW_CATEGORY', '');
define('POSTS_SHOW_CATEGORY_BR', '');
define('JB_POSTS_SHOW_DAYS_ELAPSED', 'YES');

define('JB_P_POSTS_SHOW_DAYS_ELAPSED', 'NO');
define('JB_SHOW_PREMIUM_HITS', 'YES');
define('JB_MANAGER_POSTS_PER_PAGE', '50');
define('JB_POSTING_FORM_HEIGHT', '1600');

// Resumes
define('JB_RESUMES_NEED_APPROVAL', 'NO');
define('JB_RESUMES_PER_PAGE', '30');
define('JB_RESUME_REQUEST_SWITCH', 'YES');

// Email

define('JB_USE_MAIL_FUNCTION', 'YES');
define('JB_EMAIL_HOSTNAME', '');
define('JB_EMAIL_SMTP_SERVER', '');
define('JB_EMAIL_POP_SERVER', '');
define('JB_EMAIL_SMTP_USER', '');
define('JB_EMAIL_SMTP_PASS', '');
define('JB_EMAIL_SMTP_AUTH_HOST', '');
define('JB_EMAIL_SMTP_PORT', '0');
define('JB_POP3_PORT', '0');
define('JB_EMAIL_SIG_SWITCH', 'YES');
define('JB_EMAIL_ADMIN_RECEIPT_SWITCH', 'YES');
define('JB_EMAIL_ORDER_COMPLETED_SWITCH', 'YES');
define('JB_EMAIL_MEMBER_EXP_SWITCH', 'YES');
define('JB_EMAIL_SUBSCR_EXP_SWITCH', 'YES');
define('JB_EMAIL_CANDIDATE_RECEIPT_SWITCH', 'YES');
define('JB_EMAIL_DEBUG_SWITCH', 'NO');
define('EMAIL_URL_SHORTEN', 'YES');
define('JB_EMAIL_EMPLOYER_SIGNUP_SWITCH', 'YES');
define('JB_EMAIL_CANDIDATE_SIGNUP_SWITCH', 'YES');
define('JB_EMAIL_EMP_SIGNUP', '');
define('JB_EMAIL_CAN_SIGNUP', '');
define('JB_EMAIL_AT_REPLACE', 'YES');
define('JB_EMAIL_NEW_POST_SWITCH', 'YES');
define('JB_EMAILS_PER_BATCH', '10');
define('JB_EMAILS_MAX_RETRY', '5');
define('JB_EMAILS_ERROR_WAIT', '10');
define('JB_EMAILS_DAYS_KEEP', '90');
define('JB_EMAIL_POP_BEFORE_SMTP', 'NO');
define('JB_EMAIL_SMTP_SSL', 'NO');
define('JB_ENABLED_PLUGINS', '');
define('JB_PLUGIN_CONFIG', 'a:2:{s:9:"IndeedXML";a:19:{s:8:"priority";s:0:"";s:1:"l";s:0:"";s:1:"k";s:3:"php";s:2:"ch";s:5:"jamit";s:2:"id";s:16:"2451470435917521";s:5:"l_tag";s:0:"";s:5:"k_tag";s:0:"";s:1:"s";s:0:"";s:4:"curl";s:1:"N";s:5:"proxy";s:0:"";s:4:"fill";s:1:"S";s:1:"c";s:2:"us";s:1:"f";s:1:"1";s:3:"age";s:2:"30";s:1:"h";s:0:"";s:1:"r";s:2:"25";s:2:"st";s:0:"";s:2:"so";s:4:"date";s:2:"jt";s:0:"";}s:14:"SimplyHiredXML";a:21:{s:8:"priority";s:0:"";s:1:"l";s:0:"";s:1:"k";s:7:"manager";s:2:"id";s:5:"12281";s:5:"l_tag";s:0:"";s:5:"k_tag";s:0:"";s:1:"s";s:0:"";s:4:"curl";s:1:"N";s:5:"proxy";s:0:"";s:4:"fill";s:1:"S";s:1:"c";s:2:"us";s:1:"f";s:1:"1";s:3:"age";s:2:"30";s:3:"rpp";s:0:"";s:1:"h";s:0:"";s:1:"r";s:2:"25";s:2:"st";s:0:"";s:2:"so";s:2:"rd";s:2:"jt";s:0:"";s:4:"ssty";s:1:"3";s:3:"day";s:1:"Y";}}');
define('JB_EMAIL_ADMIN_RESUPDATE_SWITCH', 'YES');
define('JB_EMAIL_ADMIN_NEWORD_SWITCH', 'YES');
define('JB_EMAIL_POST_EXP_SWITCH', 'YES');
define('JB_EMAIL_POST_APPR_SWITCH', 'YES');
define('JB_EMAIL_POST_DISAPP_SWITCH', 'YES');
define('JB_CRON_LIMIT', '');
define('JB_LIST_HOVER_COLOR', '#FEFEED');
define('JB_LIST_BG_COLOR', '#FFFFFF');
define('JB_SET_CUSTOM_ERROR', 'YES');
define('JB_DEMO_MODE', 'NO');
define('JB_MEMCACHE_HOST', '');
define('JB_MEMCACHE_PORT', '');
define('JB_MEMCACHE_COMPRESSED', '');
define('JB_CACHE_DRIVER', 'JBCacheFiles');
define('JB_POSTS_SHOW_JOB_TYPE_BR', '');



	
// backend stuff..


define('JB_SEARCH_CHECK_BOX_LINE_BREAK', '');

$incs = explode(PATH_SEPARATOR, ini_get('include_path'));
foreach ($incs as $inc) {
	if (strpos(strtolower($inc), 'pear')!=true) {
		$new_incs[] = $inc;
	}
}
ini_set('include_path', implode(PATH_SEPARATOR, $new_incs));
function jb_custom_error_handler($errno, $errmsg, $filename, $linenum, $vars) {
	if (($errno <= 4) || ($errno=='sql')) { // Log the fatals & warnings
		$str .= date('r', time());
		$str .= ' | ';
		$str .= $errno.' | ';
		$str .= $errmsg.' | ';
		$str .= 'file: '.$filename.' | ';
		$str .= 'line: '.$linenum.' | ';
		$str .= "<br>\n";
		if (!function_exists('JB_get_cache_dir')) {
			$dir = 'cache/';
		} else {
			$dir = JB_get_cache_dir();
		}
		$filename = $dir.'error_log_'.md5(md5(JB_ADMIN_PASSWORD));
		$fp = fopen($filename, 'a');
		fwrite($fp, $str, strlen($str));
		fclose($fp);

		return true;
	}

	
}
if (JB_SET_CUSTOM_ERROR=='YES') {
	set_error_handler('jb_custom_error_handler');
}
include dirname(__FILE__).'/db.php';

if ($DB_ERROR=='') {
	$dir = dirname(__FILE__);
	require ($dir.'/lang/lang.php');
	require ($dir.'/include/themes.php');
	require ($dir.'/include/lib/mail/email_message.php');
	require ($dir.'/include/lib/mail/smtp_message.php');
	require ($dir.'/include/lib/mail/smtp.php');
	require ($dir.'/include/mail_manager.php');
	require ($dir.'/include/accounting_functions.php');
	require ($dir.'/include/currency_functions.php');
	require ($dir.'/include/dynamic_forms.php');
	require_once ($dir.'/include/plugin_manager.php');
	require ($dir.'/include/invoice_functions.php');
	require ($dir.'/include/functions.php');
	
	if (!get_magic_quotes_gpc()) JB_unfck_gpc();
} elseif (basename($_SERVER['PHP_SELF'])!=='install.php') { 

	$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
	
	$http_url = str_replace ('admin/', '', $http_url);
		
	if (file_exists(dirname(__FILE__).'/admin/install.php')) {
		$http_url = preg_replace ('#/(/admin/)?[^/]+$#', '/admin/install.php', $http_url);
		JB_echo_install_info ($http_url);
		die();
	} elseif (basename($_SERVER['PHP_SELF'])!=='edit_config.php') {
		$http_url = preg_replace ('#/(/admin/)?[^/]+$#', '/admin/edit_config.php', $http_url);
		echo_edit_config_info($http_url);
		die();
	}
	
}


?>