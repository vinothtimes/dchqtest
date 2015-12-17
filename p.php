<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
// this file is for executing the plugins

define ('NO_HOUSE_KEEPING', true);
require ("config.php");

JBPLUG_do_callback('home_plugin_main', $A = false); 


?>