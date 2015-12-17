<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
//require ('../config.php');
require_once (dirname(__FILE__)."/admin_common.php");
# Copyright 2005-2010 Jamit Software
# http://www.jamit.com/

if (JB_schema_alter_table(4)) {
	$JBMarkup->ok_msg("Database Structure Updated.");
} else {
	$JBMarkup->error_msg("Did not change anything."); 
}


?>