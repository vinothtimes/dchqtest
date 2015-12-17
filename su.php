<?php
###########################################################################
# Copyright Jamit Software 2012
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
# surl = short URL
# This is used for redirecting short URLs that are in emails
require (dirname(__FILE__)."/config.php");
if (isset($_REQUEST['h'])) {
	$sql = "UPDATE short_urls SET hits=hits+1 WHERE hash='".jb_escape_sql($_REQUEST['h'])."' ";
	jb_mysql_query($sql);
	jb_redirect_short_url($_REQUEST['ref']);
	exit;
}



?>