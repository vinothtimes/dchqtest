<?php

###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

if (!defined('JB_SITE_NAME')) die();

if (JB_schema_alter_table(5)) {
	$JBMarkup->ok_msg("Database Structure Updated.");
} else {
	$JBMarkup->error_msg("Did not change anything."); 
}


?>