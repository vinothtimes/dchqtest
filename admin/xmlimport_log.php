<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";
require (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Admin -> XML Import Log');


?>
<b>[XML Import]</b> 
	<span style="background-color:#F2F2F2; border-style:outset; padding:5px; "><a href="xmlimport.php">Import Setup</a></span> 
	<span style="background-color:#FFFFCC; border-style:outset; padding:5px; "><a href="xmlimport_log.php">Import Log</a></span>
	<span style="background-color:#F2F2F2; border-style:outset; padding: 5px;"><a href="xmlimporthelp.php">Import Help</a></span>
	<hr>

<h3>Import Log</h3>
<?php

$s = md5(JB_SITE_NAME);
$filename = JB_get_cache_dir().'import_log_'.$s.'.txt';


if ($_REQUEST['clear_log']==true) {

	if (file_exists($filename)) {
		unlink ($filename);
	}
}


// READ THE LOG
if (file_exists($filename)) {

	$size = filesize($filename);
	
	$bytes_read=0;
	
	if ($size > 0) {
		$fp = fopen ($filename, 'r');
		echo '<textarea rows="25" cols="100" style="width:99%">';
		while (!feof($fp)) {
			$log = fread($fp, 1024);
			$bytes_read+=1024;
			echo htmlentities($log);
			if ($bytes_read > 1048576) {
				echo 'The file is larger than 1MB and too big to fit here, you may open the file with a text editor instead: '.$filename.' ';
				break;
			}
		}
		
		echo $log;
		echo '</textarea>';
		fclose($fp);
	} else {
		$log = "The log is empty";
	}

} else {
	$log = "The import log is clear";
}


?>
<input type="button" value="Clear Log" onclick="window.location='xmlimport_log.php?clear_log=1'"><br>

<?php

JB_admin_footer();

?>
