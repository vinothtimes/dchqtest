<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
set_time_limit ( 60*15 );

if (function_exists('apache_setenv')) {
	apache_setenv('no-gzip', 1);
}
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);

require "../config.php";
require (dirname(__FILE__)."/admin_common.php");
require_once ("../include/xml_import_functions.php");

JB_admin_header('XML Import Iframe', 'xmlimport_iframe');
?>


<div id='status' style="position: absolute; left:0px; top:0px; background-color:red; color: white; font-weight: bold; font-size: 12pt;">
Importing...
</div>
<pre>
<?php


//$sql = "DELETE FROM posts_table WHERE `guid` != '' ";
//jb_mysql_query($sql);

$feed_id = (int) $_REQUEST['feed_id'];

$importer = new xmlFeedImporter($feed_id);

if ($importer->feed_row['pickup_method']!='POST') {
	
	$importer->verbose=true;
	$importer->import();
	echo "Done.\n";

}


?>
</pre>
<?php

JB_admin_footer();

?>