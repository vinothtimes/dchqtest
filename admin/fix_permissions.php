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


JB_admin_header('Admin -> Fix Permissions');

if (JB_DEMO_MODE=='YES') { 
	echo ' Demo mode, this function is disabled'; 
	JB_admin_footer();
	die();
}

?>

<h3>Fix Permissions</h3>

<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
<p>The following script will iterate through your directories (including cache/ and upload_files/ and set the permissions according to the following:<br>
- files: chmod <b><?php echo decoct(JB_NEW_FILE_CHMOD); ?></b><br>
- directories: chmod <b><?php echo decoct(JB_NEW_DIR_CHMOD); ?></b><br>
- config.php will be omitted.<br>
</p>

<input type="submit" value="Start" name="start">
</form>
<?php


if ($_REQUEST['start']) {
	echo str_repeat(" ", 256)."<pre>"; flush();
	echo 'working...<br>';flush();

	chmod (jb_basedirpath().'rss.xml', JB_NEW_FILE_CHMOD); echo "chmod ".decoct(JB_NEW_FILE_CHMOD)." rss.xml\n";

	jb_fix_perms_recursive(jb_basedirpath().'cache/');
	jb_fix_perms_recursive(jb_basedirpath().'upload_files/');
	
	echo 'Done. You may close this window now';

	echo '</pre>';




}

///////////////////////

function jb_fix_perms_recursive($dir) {

	//static $file_list;

	if (is_dir($dir) && (strpos($dir, '.svn')===false)) {
		if (chmod ($dir . $file, JB_NEW_DIR_CHMOD)) {
			echo "chmod ".decoct(JB_NEW_DIR_CHMOD)." ".$dir . $file."\n";
			flush();
		} else {
			echo 'Seems like the system cannot set the permissions for this dir: '.$dir . $file.'<br> Please try setting permissions from the command line or using FTP'."\n";
			flush();
			
		}
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				//echo "$dir . $file<br>";
				if (filetype($dir . $file) == 'file') {

					//$file_list[] = $dir . $file;
					if (($file !== 'index.html') && ($file !== '.htaccess') && ($file !== 'dl.php') ) {
						if (chmod ($dir . $file, JB_NEW_FILE_CHMOD)) { 
							echo "chmod ".decoct(JB_NEW_FILE_CHMOD)." ".$dir . $file."\n";
							flush();
						} else {
							echo 'Seems like the system cannot set the permissions for this file: '.$dir . $file.'<br> Please try setting permissions from the command line or using FTP'."\n";
							flush();
							
						}
					}

				} elseif ((filetype($dir . $file) == 'dir') && ($file != '.') && ($file != '..')) {
					jb_fix_perms_recursive($dir . $file.'/');
				}
				//echo "filename: $file : filetype: " . filetype($dir . $file) . "\n";
			}
			closedir($dh);
		}
	}



}

JB_admin_footer();

?>