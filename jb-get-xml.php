<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
ini_set('max_execution_time', 180);


require ("config.php");
require_once (dirname(__FILE__)."/include/xml_feed_functions.php");

$feed_id = $_REQUEST['feed_id'];

if (!is_numeric($feed_id)) die();

JBXML_generate_xml_feed($feed_id);

?>