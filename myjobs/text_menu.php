<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
?>

<link rel="stylesheet" type="text/css" href="<?php echo JB_get_text_menucss_url(); ?>" >
<div id="menu1" style="display:none"></div>
<DIV style="float: right;"><?php echo $label["employer_menu_logged_in"]; ?> <b><?php echo $_SESSION['JB_Username'];?></b> | <a href="logout.php"><?php echo $label["employer_menu_logout"]; ?></a></DIV>

<?php

require (JB_basedirpath().'include/tabbed_menu_functions.php');

$active_button = JB_tabbed_show_menu($jb_menu) ;

JB_tabbed_show_sub_menu($active_button);

?>