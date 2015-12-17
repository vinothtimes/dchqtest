<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
include('../config.php'); 
include('login_functions.php'); 
include_once('../include/posts.inc.php'); 
JB_process_login(); 
JB_template_employers_header();?>

<?php
if (is_numeric($_REQUEST['repost'])) {
	$repost = '&repost_id='.$_REQUEST['post_id'];
	$_REQUEST['post_id'] = '';
} 

	// removed this form IFRAME: onfocus="menu1.hideAll()"

if (!defined('JB_POSTING_FORM_HEIGHT')) {

	define('JB_POSTING_FORM_HEIGHT', 1600);
}
?>

<iframe width="100%" FRAMEBORDER="0" id="post_form" height="<?php echo JB_POSTING_FORM_HEIGHT;?>" src="post_iframe.php?post_id=<?php echo $_REQUEST['post_id'];?>&amp;type=<?php echo $_REQUEST['type'].$repost ;?>" ></iframe>


  

<?php JB_template_employers_footer(); ?>