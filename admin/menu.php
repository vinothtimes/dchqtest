<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
define ('NO_HOUSE_KEEPING', true);
require ("../config.php");
require (dirname(__FILE__)."/admin_common.php");

echo $JBMarkup->get_admin_doctype();
$JBMarkup->markup_open(); // <html>

$JBMarkup->head_open();
?>


<?php $JBMarkup->charset_meta_tag(); ?>
<style>
a {
	color:#000000;
	text-decoration: none;
		 
}

a:hover {
	color:#3399FF;
}

hr {
	border:#7AB4B8 dashed 1px;
	 color:#6633CC;

}

.menu_group {
	border-top:#7AB4B8 solid 2px;
	background: #fff  url(<?php echo JB_DEFAULT_THEME_URL;?>images/grgrad.gif) repeat-x;
	margin-top: 5px;

}

.icon {
	display: true;
}
</style>
<TITLE> Menu </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<link rel="stylesheet" type="text/css" href="<?php echo JB_get_admin_maincss_url(); ?>" >
<?php

$JBMarkup->head_close();
$JBMarkup->body_open();

?>
<span style="padding: 0px;"><strong>Jamit Job Board</strong> <small><br> v <?php echo jb_get_variable('JB_VERSION') ?  jb_get_variable('JB_VERSION') :  "3.1.1"; ?></small></span><br>
<div>

<b>Admin</b><br>
<!--<img src="icons/icon_home.gif" border="0"><br>-->
- <a href="main.php" target="main">Main Summary</a><br>
</div>
<div class='menu_group'>
<img class='icon' src="icons/icon_employer.gif" border="0"><br>
<b>Employer Admin</b><br>
- <a href="employers.php" target="main">List Employers</a><br>
- <a href="employers.php?show=NA" target="main"> Non-Approved</a><br>
- <a href="profiles.php" target="main"> List Profiles</a><br>
<?php JBPLUG_do_callback('admin_menu_employer', $A = false); ?>
</div>
<div class='menu_group'>
<img class='icon' src="icons/icon_jobseekers.gif" border="0"><br>
<b>Candidate Admin</b><br>
- <a href="candidates.php" target="main">List Candidates</a><br>
- <a href="resumes.php?show=ALL" target="main">List R&#233;sum&#233;s</a><br>
- <a href="apps.php" target="main">Applications</a><br>
<?php JBPLUG_do_callback('admin_menu_candidate', $A = false); ?>
</div>
<div class='menu_group'>
<img class='icon' src="icons/icon_edit1.gif" border="0"><br>
<b>Job Post Admin</b><br>
- <a href="posts.php?show=ALL" target="main">List Posts</a><br>
- <a href="posts.php?show=WA" target="main">New Posts Waiting</a><br>
- <a href="posts.php?show=NA" target="main">Non-Approved Posts</a><br>
- <a href="post_new.php" target="main">Post a Job</a><br>
<?php JBPLUG_do_callback('admin_menu_job_post', $A = false); ?>
</div>
<div class='menu_group'>
<img class='icon' src="icons/icon_money.gif"><br>
<b>Orders</b><br>
- <a href="package_report.php" target="main">Posting Orders</a><br>
- <a href="subscription_report.php" target="main">Subscription Orders</a><br>
- <a href="membership_report.php" target="main">Membership Orders</a><br>
- <a href="transactions.php" target="main">Transactions</a><br>
<?php JBPLUG_do_callback('admin_menu_orders', $A = false); ?>
<small>Manage existing:</small><br>
- <a href="subscriptions.php" target="main">Subscriptions</a><br>
- <a href="memberships.php" target="main">Memberships</a><br>

</div>
<div class='menu_group'>
<img class='icon' src="icons/icon_stats.gif"><br>
<b>Website Reports</b><br>
- <a href="stats.php" target="main">Statistics</a><br>
- <a href="paypal_log.php" target="main">Payment Log</a><br>
- <a href="whois_online.php" target="main">Who's Online</a><br>
<?php JBPLUG_do_callback('admin_menu_reports', $A = false); ?>
</div>
<div class='menu_group'>
<img class='icon' src="icons/icon_creditcard.gif" border="0"><br>
<b>Price Admin</b><br>
- <a href="set_packages.php" target="main">Posting Plans</a><br>
- <a href="set_subscriptions.php" target="main">Subscription Plans</a><br>
- <a href="set_memberships.php" target="main">Memberships</a><br>
- <a href="currency.php" target="main">Currency Rates</a><br>
<?php JBPLUG_do_callback('admin_menu_price', $A = false); ?>
</div>
<div  class='menu_group'>
<img class='icon' src="icons/icon_newsletter.gif" border="0"><br>
<b>Newsletters</b><br>
- <a href="newsletter.php?to=EM" target="main">Create / Send</a><br>
<?php JBPLUG_do_callback('admin_menu_newsletter', $A = false); ?>
</div>

<div class='menu_group'>
<img class='icon' src="icons/icon_lighthouse.gif" border="0"><br>
<b>Outgoing Email</b><br>
- <a href="email_queue.php" target="main">List Queue</a><br>
- <a href="jobalerts.php" target="main">Job Alerts</a><br>
- <a href="resumealerts.php" target="main">Resume Alerts</a><br>
<?php JBPLUG_do_callback('admin_menu_mailqueue', $A = false); ?>
</div>
<div class='menu_group'>
<img class='icon' src="icons/icon_settings.gif" border="0"><br>
<b>Configuration</b><br>
- <a href="edit_config.php" target="main">Main Config...</a><br>
- <a href="payment.php" target="main">Payment Modules</a><br>
- <a href="plugins.php" target="main">Plugins</a><br>
<?php JBPLUG_do_callback('admin_menu_config', $A = false); ?>
<b>Variables:</b><br>
- <a href="editcats.php" target="main">Edit Categories</a><br>
- <a href="editcodes.php" target="main">Edit Codes</a><br>
- <a href="language.php" target="main">Languages</a><br>
- <a href="emailconfig.php" target="main">Email Templates</a><br>
- <a href="motd.php" target="main">MOTD</a><br>
- <a href="help_pages.php" target="main">Help Pages</a><br>
<?php JBPLUG_do_callback('admin_menu_configvars', $A = false); ?>
</div>
<div class='menu_group'>
<img class='icon' src="icons/icon_edit2.gif" border="0"><br>
<b>Customize Forms</b><br>
- <a href="postform.php" target="main">Posting Form</a><br>
- <a href="resumeform.php" target="main">R&#233;sum&#233; Form</a><br>
- <a href="profileform.php" target="main">Profile Form</a><br>
- <a href="signup_employer_form.php" target="main">Employer Signup</a><br>
- <a href="signup_candidate_form.php" target="main">Candidate Signup</a><br>
<?php JBPLUG_do_callback('admin_menu_forms', $A = false); ?>
</div>
<div class='menu_group'>
<img class='icon' src="icons/icon_stats.gif" border="0"><br>
<b>Info</b><br>
- <a href="info.php" target="main">System Info</a><br>
- <a href="cron.php" target="main">Cron Info</a><br>
- <a href="errors.php" target="main">Error Log</a><br>
- <a href="http://www.jamit.com/" target="main">Jamit Home</a><br>
<?php JBPLUG_do_callback('admin_menu_info', $A = false); ?>
</div>
<div class='menu_group'>
<img class='icon' src="icons/icon_history.gif" border="0"><br>
<b>Extras</b><br>
- <a href="xmlimport.php" target="main">XML Import</a><br>
- <a href="xmlfeed.php" target="main">XML Export</a><br>
- <a href="xmlsitemaps.php" target="main">XML Sitemaps</a><br>
- <a href="monitor.php" target="main">Email Monitor</a><br>
- <a href="mod_rewrite.php" target="main">mod_rewrite</a><br>
- <a href="ssl.php" target="main">SSL Advice</a><br>
- <a href="dbtools.php" target="main">Database Tools</a><br>
- <a href="game/" target="main">Fun Game</a><br>
<?php JBPLUG_do_callback('admin_menu_extras', $A = false); ?>
</div>
<div class='menu_group'>
<b>Logout</b><br>
- <a href="logout.php" target="main">Logout</a><br>
</div>
<?php
JBPLUG_do_callback('admin_menu_bot', $A = false);


$JBMarkup->body_close();
$JBMarkup->markup_close();

?>

<!-- 837498832 --> 