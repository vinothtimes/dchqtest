<?php 

###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

include('../config.php');
require (dirname(__FILE__)."/admin_common.php");
JB_admin_header('Admin -> Post');
?>
<iframe width="100%" FRAMEBORDER="0" height="1500" src="post_iframe.php?post_id=<?php echo $_REQUEST['post_id'];?>&amp;type=<?php echo $_REQUEST['type'];?>" ></iframe>

<?php 
JB_admin_footer();
?>