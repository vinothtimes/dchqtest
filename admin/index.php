<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
define ('MAIN_PHP', '1');

require("../config.php");
require (dirname(__FILE__)."/admin_common.php");
echo $JBMarkup->get_admin_doctype();
$JBMarkup->markup_open(); // <html>
$JBMarkup->head_open();
?>

<META HTTP-EQUIV="REFRESH" CONTENT="0; URL=admin.php">
<?php
$JBMarkup->head_close();
$JBMarkup->body_open();
?>
<a href="admin.php">Click here to continue.</a>
<?php
$JBMarkup->body_close();
$JBMarkup->markup_close();

?>