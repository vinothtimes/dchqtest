<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";
include('login_functions.php');
include('../payment/payment_manager.php');

JB_process_login();
JB_template_employers_header(); 

?>
<h3 style="text-align:center;">
<?php echo $label['e_thanks_payment_return']; ?>
</h3>

<?php

$className = $_REQUEST['m'];
JB_process_payment_return($className);

JB_template_employers_footer();  

?>