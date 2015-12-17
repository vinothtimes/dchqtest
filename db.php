<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################


########################################################

$jb_mysql_link = jb_mysql_connect();


function jb_mysql_connect() {

	static $link;

	if (isset($link)) return $link;

	global $DB_ERROR;
	$DB_ERROR = '';
	$dbhost = JB_MYSQL_HOST;
	$dbusername = JB_MYSQL_USER;
	$dbpassword = JB_MYSQL_PASS;
	$database_name = JB_MYSQL_DB;

	if ($dbhost=='') {
		$dbhost = $_REQUEST['jb_db_host']; 
	}
	if ($dbusername=='') {
		$dbusername = $_REQUEST['jb_db_user']; 
	}
	if ($dbpassword=='') {
		$dbpassword = $_REQUEST['jb_db_pass']; 
	}
	if ($database_name=='') {
		$database_name = $_REQUEST['jb_db_name']; 
	}

	$link = @mysql_connect($dbhost, $dbusername, $dbpassword, true)
		or $DB_ERROR = "Couldn't connect to server.";
	if ($DB_ERROR=='') {	
		@mysql_select_db("$database_name", $link)
			
		or $DB_ERROR = "Couldn't select database.";

	}
	return $link;

}

$jb_query_c=0;
// mysql_query() wrapper
function JB_mysql_query($sql, $explain=false) {
	//$explain = true;
	
	global $jb_query_c; // query counter
	$jb_query_c++;
	
	$jb_mysql_link = jb_mysql_connect();
	

	$result = mysql_query($sql, $jb_mysql_link) or JB_echo_db_error('JB_mysql_query('.$sql.') - '.mysql_error($jb_mysql_link));

	
	if ($explain) {
		echo htmlentities("SQL:".$sql);
		if (preg_match('/SELECT/i',$sql)) {
			
			
			$sql = 'EXPLAIN '.$sql;
			$exp_r = mysql_query($sql, $jb_mysql_link) or print (mysql_error($jb_mysql_link)).'<br>';
			if (mysql_num_rows($exp_r)>0) {
				while ($expr_row = mysql_fetch_array($exp_r, MYSQL_ASSOC)) {
					echo "<pre>";print_r($expr_row);echo "</pre>";
				}
			}
		}
	
	}

	return $result;
}

function JB_mysql_insert_id() {
	$jb_mysql_link = jb_mysql_connect();
	return mysql_insert_id($jb_mysql_link);

}

function JB_mysql_affected_rows() {
	$jb_mysql_link = jb_mysql_connect();
	return mysql_affected_rows($jb_mysql_link);

}

function JB_escape_sql($str, $link=false) {

	$jb_mysql_link = jb_mysql_connect();
	if (!$link) {
		$link = &$jb_mysql_link;
	}

	return mysql_real_escape_string(stripslashes($str), $link);

}

function jb_mysql_ping() {
    $jb_mysql_link = jb_mysql_connect();
    return mysql_ping($jb_mysql_link);
}

function JB_echo_db_error($error) {

	if (defined('JB_HIDE_MYSQL_ERRORS') && JB_HIDE_MYSQL_ERRORS) {
		return;
	}

	if (strpos($error, 'show columns from')!==false) {
		// this is a diagnostic query, should still continue on error.
		return;
	}

	$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
	$http_url = str_replace ('admin/', '', $http_url);

	if (strpos($error, "doesn't exist")!==false) { // looks like the database was not installed
		
		if (file_exists(dirname(__FILE__).'/admin/install.php')) {
			$http_url = preg_replace ('#/(/admin/)?[^/]+$#', '/admin/install.php',$http_url);
			JB_echo_install_info ($http_url, $error);
			die();
		} elseif (basename($_SERVER['PHP_SELF'])!=='edit_config.php') {
			$http_url = preg_replace ('#/(/admin/)?[^/]+$#', '/admin/edit_config.php',$http_url);
			echo_edit_config_info($http_url, $error);
			die();
		}

	} else {

		if (JB_SET_CUSTOM_ERROR=='YES') {
			ob_start();
			$trace = debug_backtrace();
			var_dump($trace['1']);
			$trace = ob_get_contents();
			ob_end_clean();
			$req = var_export($_REQUEST, true);

			if (function_exists('jb_escape_html')) {

				jb_custom_error_handler('sql', jb_escape_html($error."\n".$trace."\n".$req), __FILE__, 0, $vars);
			} else {

				jb_custom_error_handler('sql', htmlentities($error."\n".$trace."\n".$req), __FILE__, 0, $vars);

			}
		} else {
			if (function_exists('jb_escape_html')) {
				echo jb_escape_html($error);
			} else {
				echo htmlentities($error);
			}
		}

	}


}

function JB_echo_install_info ($http_url, $error='') {

	echo "<B>It looks like your installation is not completed yet. Please run the <a href='".htmlentities($http_url)."'>installation script</a> to install the Jamit Job Board.</b>";
	echo '<p>Or it might be that an enabled plugin cannot query the database because the table it needs is missing... so try to go to admin/edit_config.php and disable all the plugins. Need Help? See the documentation here: <a href="http://www.jamit.com/docs.htm">http://www.jamit.com/docs.htm</a> or try the support system here <a href="http://www.jamit.com/support/">http://www.jamit.com/support/</a></p>';
	if ($error!='') {
		echo "(System said: ".htmlentities($error).") file:<b>".basename($_SERVER['PHP_SELF']).'</b>';
		//echo '<pre>';print_r(debug_backtrace());echo '</pre>';
	}


}

function echo_edit_config_info ($http_url, $error='') {

	global $DB_ERROR;

	echo "<B>It looks like there was a database error. (".htmlentities($DB_ERROR)." ".htmlentities($error).") - Please inspect your MySQL settings in the config.php file, or report this error to the webmaster: ".JB_SITE_CONTACT_EMAIL."</b>";

	echo '<p>Need Help? See the documentation here: <a href="http://www.jamit.com/docs.htm">http://www.jamit.com/docs.htm</a> or try the support system here <a href="http://www.jamit.com/support/">http://www.jamit.com/support/</a></p>';


}
?>