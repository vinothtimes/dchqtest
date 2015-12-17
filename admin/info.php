<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require("../config.php");
require ('admin_common.php');


JB_admin_header('Admin -> Server Info');

if (JB_DEMO_MODE=='YES') {

	$JBMarkup->ok_msg('Demo mode enabled - this section is locked');

} else {
	?>


	<h3>System info</h3>
	You have PHP version: <?php echo phpversion(); ?><br><br>

	<br>
	Your path to your admin directory: <?php echo str_replace('\\', '/', getcwd());?>/
	<hr>

	<b>Logging:</b> <a href="errors.php">View the Error Log of the last few PHP error and warning messages.</a><br>

	<?php



	


	$status = explode('  ', mysql_stat());
	echo "<h3>MySQL Stats</h3><pre>";
	print_r($status);
	echo "</pre>";
	phpinfo();

}

JB_admin_footer();

?>
