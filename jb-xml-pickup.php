<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
@set_time_limit ( 180 ); // 150 seconds, 3 min

require ('config.php');

require_once ('include/xml_import_functions.php');

/* 

Here is some example PHP code for posting an XML file
to this script (jb-xml-pickup.php) :

$req = $xml_data;

$host = '127.0.0.1';

$header .= "POST /JamitJobBoard-3.2.0/jb-xml-pickup.php?feed_id=1&key=test HTTP/1.1\r\n";
$header .= "Host: $host\n";
$header .= "Content-Type: text/xml; charset=UTF-8\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

$fp = fsockopen ($host, 80, $errno, $errstr, 30);

fputs ($fp, $header . $req); // post

*/



if (!is_numeric($_REQUEST['feed_id'])) {
	die('feed_id parameter not present');
} else {
	$feed_id = $_REQUEST['feed_id'];
}

$importer = new xmlFeedImporter($feed_id);

if ($importer->feed_row['pickup_method']=='POST') {

	
	$hosts = array();
	$hosts = explode(',', $importer->feed_row['ip_allow']);
	$allowed = false;
	
	if (sizeof($hosts)>0) {
		foreach ($hosts as $host) {
			if (strtoupper($host)=='ALL') { // all hosts
				$allowed = true;
			}
			if ((strtolower($host)=='localhost') && 
				($_SERVER['REMOTE_ADDR']=='127.0.0.1')) {
				$allowed = true;
			}
			if ($host==$_SERVER['REMOTE_ADDR']) {
				$allowed = true;
			}
		}

		if (!$allowed) {
			$importer->set_import_error('Blocked access from: '.$_SERVER['REMOTE_ADDR']);
			die ('Access is restricted form your IP. Please contact '.JB_SITE_CONTACT_EMAIL);
		}
	}

	$importer->import();
}

?>