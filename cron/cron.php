<?php

$dir = dirname(__FILE__);
$dir = explode (DIRECTORY_SEPARATOR, $dir);
$blank = array_pop($dir);
$dir = implode('/', $dir);

define ('JB_OMIT_SESSION_START', true);
require($dir.'/config.php');

if (strlen($_SERVER['SERVER_NAME'])>0) { // CGI env vars declared; assumed HTTP

	if (!defined('JB_CRON_HTTP_ALLOW')) define ('JB_CRON_HTTP_ALLOW', 'YES');
	if (!defined('JB_CRON_HTTP_USER')) define ('JB_CRON_HTTP_USER', '');
	if (!defined('JB_CRON_HTTP_PASS')) define ('JB_CRON_HTTP_PASS', '');

	if (JB_CRON_HTTP_ALLOW=='YES') {
		if (strlen(JB_CRON_HTTP_USER)>0) { // requires user/pass?
			if (!isset($_SERVER['PHP_AUTH_USER'])) {
				header('WWW-Authenticate: Basic realm="Jamit Cron"');
				header('HTTP/1.0 401 Unauthorized');
				echo 'Access Denied. Please see the user/pass on the Admin->Edit Config page, \'Cron Settings\'.';
				exit;
			} else {		
				if (($_SERVER['PHP_AUTH_USER']!==JB_CRON_HTTP_USER) && ($_SERVER['PHP_AUTH_PW']!==JB_CRON_HTTP_PASS))  {
					header('WWW-Authenticate: Basic realm="Jamit Cron"');
					header('HTTP/1.0 401 Unauthorized');
					die ('Login Failed. Please see the user/pass on the Admin->Edit Config page, \'Cron Settings\'');
				}
			}
		}
	} else {
		die('This script cannot be executed from the web browser. Please see settings in Admin-&gt;Edit Config, also Admin-&gt;Cron Info for more details');
	}

}

@set_time_limit ( 180 ); // (180 seconds, 3 min max)

if (JB_CRON_EMULATION_ENABLED!='YES') {
	JB_do_cron_job();
}


?>