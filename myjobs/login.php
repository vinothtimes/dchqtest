<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################


require("../config.php");

$page = $_REQUEST['page'];
require ("login_functions.php");

// a call to validate_candidate_login() is buffered
// because JB_template_candidate_login sends out the
// header information

buffer_validate_candidate_login();

JB_template_candidate_login();

?>