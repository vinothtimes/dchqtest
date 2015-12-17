<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require ("../config.php");
require (dirname(__FILE__)."/admin_common.php");
session_destroy();

JB_admin_header('Admin -> Logout');

?>


You have been logged out. <a href='index.php' target="_parent">Click here to log back in.</a>

<?php

JB_admin_footer();


?>