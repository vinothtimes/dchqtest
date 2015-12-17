<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require("../config.php");
require (dirname(__FILE__)."/admin_common.php");

$PAYMENT_PATH = JB_basedirpath().'/payment/';

include ($PAYMENT_PATH.'payment_manager.php');

JB_admin_header('Admin -> Payment Modules');

if (!JB_list_avalable_payments ()) {

	echo '<font color="maroon"><b>Reminder: Please ensure that at least one payment method is enabled so that the traffic light icon goes green! :)</b></font>';

}

JB_admin_footer();
?>