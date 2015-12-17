<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
define ('NO_HOUSE_KEEPING', true);
require ("../config.php");
require (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Admin -> Error Log');

if (JB_DEMO_MODE=='YES') {

	$JBMarkup->ok_msg('Demo mode enabled - this section is locked');
	JB_admin_footer();
	die();

}

if (JB_SET_CUSTOM_ERROR!='YES') {
	echo "<p>Note: Custom error logging is disabled. Please go to Main Config and enable it from there if you want to have all errors logged here</p>";
}

?>

<input type="button" value="Refresh" onclick="window.document.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>'"> | <input type="button" value="Clear" onclick="window.document.location='<?php echo $_SERVER['PHP_SELF'];?>?clear=1'"><br>
<?php


$dir = jb_get_cache_dir();

if ($_REQUEST['clear']==1) {
	$filename = JB_get_cache_dir()."error_log_".md5(md5(JB_ADMIN_PASSWORD));
	if (file_exists($filename)) {	
		$handle = fopen($filename, "w");
		fclose($handle);
	}

}


if ($dh = opendir($dir)) {
	while (($file = readdir($dh)) !== false) {
		//echo "filename: $file : filetype: " . filetype($dir . $file) . "\n";
		if ((filetype($dir . $file) === 'file') && (strpos($file, 'error_log')!==false)) {
			$stat = lstat($dir . $file);
			if (($stat[10]+(60*60*60*7)) < time()) { // truncate the error_log file after 7 days...
				@unlink ($dir . $file);
			}
		}
	}
	closedir($dh);
}

$filename = $dir."error_log_".md5(md5(JB_ADMIN_PASSWORD));
if (file_exists($filename)) {
	$handle = fopen($filename, "r");
	$size = filesize($filename);
	if ($size>0) {
		$contents = @fread($handle, $size);
	}
	fclose($handle);
	echo $contents;
}

JB_admin_footer();

?>
