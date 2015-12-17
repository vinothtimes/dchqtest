<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
define ('NO_HOUSE_KEEPING', true);
require ('config.php');


$sql = "SELECT * FROM lang where lang_code='".jb_escape_sql($_REQUEST['code'])."' ";
$result  = JB_mysql_query ($sql) or die(mysql_error());
$row = mysql_fetch_array($result, MYSQL_ASSOC);


header ("Content-type: ".$row['mime_type']);

echo base64_decode( $row['image_data']);


?>
