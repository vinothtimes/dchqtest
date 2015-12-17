<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";
require '../include/help_functions.php'; 
include('login_functions.php');
JB_process_login();
JB_template_employers_header(); 

$data = JB_load_help('E');
	   
JB_render_box_top(80, $data['title']);

echo trim($data['message']);
if (!trim($data['message'])) {
	echo "This page can be edited from Admin-&gt;Help Pages";
}
JB_render_box_bottom();

JB_template_employers_footer(); 

?>