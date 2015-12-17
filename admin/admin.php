<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require('../config.php');
//echo $JBMarkup->get_admin_doctype();
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">'."\n";
$JBMarkup->markup_open(); // <html>
$JBMarkup->head_open(); // open the <HEAD> part
?>

<title>Admin - <?php  echo jb_escape_html(JB_SITE_NAME); ?> - Jamit Job Board </title>
<meta http-equiv="Content-Type" content="text/html;">
<?php
$JBMarkup->head_close();
?>

<frameset cols="150,*" rows="*" border="2" framespacing="0" frameborder="yes">
  <frame src="menu.php" name="nav" marginwidth="3" marginheight="3" scrolling="auto">
  <frame src="main.php" name="main" marginwidth="10" marginheight="10" scrolling="auto">
</frameset>

<noframes>
	<body bgcolor="#FFFFFF" text="#000000">
		<p>Sorry, your browser doesn't seem to support frames</p>
	</body>
</noframes>
<?php
$JBMarkup->markup_close(); // </html>
?>