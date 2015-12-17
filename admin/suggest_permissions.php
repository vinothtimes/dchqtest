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


JB_admin_header('Admin -> Permission Test');

if (JB_DEMO_MODE=='YES') { 
	echo ' Demo mode, this function is disabled'; 
	JB_admin_footer();
	die();
}


function jb_test_dir_perms($dir='cache/') {

	echo '<li>';
	echo "$dir directory - ";

	if (@touch('../'.$dir.'permissions_test')) {
		echo '<span style="color:green">writable</span><br>';
	} else {
		echo '<span style="color:maroon">not writable (please check your '.$dir.' directory settings)</span><br>';
	}
	// can we delete it?

	if (@unlink('../'.$dir.'permissions_test')) {
		echo '<span style="color:green">Your '.$dir.' seems to be OK!</span>';
	} else {
		echo '<span style="color:maroon">Looks like there was a problem with deleting a file from '.$dir.'. Please check the permissions</span><br>';
	}
	echo '</li>';


}

if ($_REQUEST['test']) {

	// config.php
	
	echo '<p style="margin:10%;"><ul>';

	echo '<li>';
	echo "config.php - ";

	if (is_writable('../config.php')) {
		echo '<span style="color:green">writable (OK)</span><br>';
	} else {
		echo '<span style="color:maroon">not writable (cannot save changes to Main Config)</span>';
	}
	echo '</li>';

	// rss.xml
	echo '<li>';
	echo "rss.xml - ";

	if (is_writable('../rss.xml')) {
		echo '<span style="color:green">writable (OK)</span><br>';
	} else {
		echo '<span style="color:maroon">not writable (cannot generate RSS file)</span>';
	}
	echo '</li>';

	// can make a file in cache/?

	jb_test_dir_perms('cache/');

	// can make a file in upload_files/docs/ ?

	jb_test_dir_perms('upload_files/docs/');
	
	// can make a file in upload_files/docs/temp/?

	jb_test_dir_perms('upload_files/docs/temp/');

	// can make a file in upload_files/images/?

	jb_test_dir_perms('upload_files/images/');

	// can make a file in upload_files/images/thumbs/?

	jb_test_dir_perms('upload_files/images/thumbs/');

	echo '</ul>(Did not test files in the lang/ directory)</p>';


} else {


	$owner = JB_discover_new_file_owner(true);

	if ($owner) {
		echo "The file owner is: $owner<br>
		The suggested permissions are:<br>";
		if ($owner=='nobody') {
			echo '<b>chmod 666</b> for files, <b>chmod 777</b> for directories<br><p><i>Note: If your server is a shared server, it may not be secure to have these permissions - it would be better to move to a dedicated server or VPS hosting account which is isolated form all other users. Each server is different - please confirm with your hosting company documentation to make sure that these statements are correct</i></p>';
		} else {
			echo 'You could probably try 444 for files, chmod 555 for directories. If that does not work, you can try 644 for files and 755 for directories. Also ensure that config.php, rss.xml the upload_files/ and cache/ directories (and files within them) also have these permissions set. Each server is different - please confirm with your hosting company documentation to make sure that these statements are correct.<br>';
		}
	}

}

function JB_discover_new_file_owner($verbose=false) {

	$owner = '';

	$temp = JB_get_cache_dir().'owner_test.tmp';

	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { 
		if ($verbose) echo "Does not work on windows";
		return false;
	} 
	if (file_exists($temp)) {
		if (!unlink ($temp)) {
			if ($verbose) echo "There was an error with deleting $temp - please remove this file via FTP<br>";
		}
	}

	if (touch ($temp)) {

		$disabled = explode(', ', ini_get('disable_functions'));
		if (in_array('exec', $disabled)) {
			return 'exec no permitted on this server';
		}

		JB_exec ('ls -o '.$temp, $output);
		
		$parts = preg_split('/[\s]+/', $output[0]);
		
		array_shift($parts); // these are the file permssions
		// the next one should be owner
		foreach ($parts as $part) {

			if ((strlen($part) > 2) && (preg_match('/[a-z0-9]{3}/i', $part))) {

				$owner = $part;
				break;
			}

		}

		unlink ($temp);

		if (!$owner) {

			echo $owner = 'nobody';

		}
		return $owner;
		


	} else {
		if ($verbose) echo 'cannot create file: '.$temp.' Please give the following directory permissions for writing: <br>';
	}

		

}

JB_admin_footer();

?>