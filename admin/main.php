<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
@ini_set('memory_limit', '16M');
define ('NO_HOUSE_KEEPING', true);
define ('MAIN_PHP', '1');

define('JB_VERSION', '3.6.13');

require ("../config.php");
require ("admin_common.php");

JB_admin_header('Admin -> Main');





ini_set('max_execution_time', 100200);


function check_connection ($user, $pass,$host) {
	if (!($connection = @mysql_connect("$host","$user", "$pass"))) {
		return false;
	}
	return $connection;
	
}

function check_db ( $db_name, $connection) {
	if (!($db = @mysql_select_db( $db_name,  $connection))){
	 return false;
	}
	return true;
}

if ($conn=check_connection (JB_MYSQL_USER, JB_MYSQL_PASS,JB_MYSQL_HOST)) {
	 if (check_db ( JB_MYSQL_DB, $conn)) {

		 if ($DB_ERROR) {
			  $JBMarkup->error_msg("<b>Database is not installed. [".mysql_error()."]<br>Please go to the installation page: <a href='install.php'>install.php</a></b>");
			  die();

		 }
		
	 } else {
		 $JBMarkup->error_msg("<b>Cannot select database.  ".mysql_error()."<br>Please go to the installation page: <a href='install.php'>install.php</a></b>");
		 die();

	 }
}
else {
	$JBMarkup->error_msg("<b>Cannot connect to database. ".mysql_error()."<br>Please go to the installation page: <a href='install.php'>install.php</a></b>");
	die();

}





require ('upgrade.php');


if ($DB_ERROR == '') {

	$bold1=''; $bold2='';

	#clean employers' expired sessions
	$session_duration = ini_get ("session.gc_maxlifetime");
   if ($session_duration==0) {
	   $session_duration=20*60;
   }

	$now = (gmdate("Y-m-d H:i:s"));
	$sql = "UPDATE `employers` SET `logout_date`='$now' WHERE UNIX_TIMESTAMP(DATE_SUB('$now', INTERVAL $session_duration SECOND)) > UNIX_TIMESTAMP(last_request_time) AND (`logout_date` ='0000-00-00 00:00:00')";
	jb_mysql_query($sql) or die ($sql.mysql_error());
	# clean candidates' expired sessions
	$now = (gmdate("Y-m-d H:i:s"));
	$sql = "UPDATE `users` SET `logout_date`='$now' WHERE UNIX_TIMESTAMP(DATE_SUB('$now', INTERVAL $session_duration SECOND)) > UNIX_TIMESTAMP(last_request_time) AND (`logout_date` ='0000-00-00 00:00:00')";
	jb_mysql_query($sql) or die ($sql.mysql_error());

	$sql = "SELECT count(*) FROM `users`  ";
	$result = jb_mysql_query($sql);
	$users = @array_pop(mysql_fetch_array($result, MYSQL_ASSOC));

	$sql = "SELECT count(*) FROM `users` where login_count > 1 ";
	$result = jb_mysql_query($sql);
	$users_returning = @array_pop(mysql_fetch_array($result, MYSQL_ASSOC));

	$sql = "SELECT count(*) FROM `users` where logout_date='0000-00-00 00:00:00' ";
	$result = jb_mysql_query($sql);
	$users_online = @array_pop(mysql_fetch_array($result, MYSQL_ASSOC));

	$sql = "SELECT count(*) FROM `users` where Validated='0'";
	$result = jb_mysql_query($sql);
	$uv_candidates = @array_pop(mysql_fetch_array($result, MYSQL_ASSOC));

	$sql = "SELECT count(*) FROM `employers`";
	$result = jb_mysql_query($sql);
	$employers = @array_pop(mysql_fetch_array($result, MYSQL_ASSOC));

	$sql = "SELECT count(*) FROM `employers` where login_count > 1 ";
	$result =jb_mysql_query($sql);
	$employers_returning = @array_pop(mysql_fetch_array($result, MYSQL_ASSOC));

	$sql = "SELECT count(*) FROM `employers` where logout_date='0000-00-00 00:00:00' ";
	$result = jb_mysql_query($sql);
	$employers_online = @array_pop(mysql_fetch_array($result, MYSQL_ASSOC));

	$sql = "SELECT count(*) FROM `employers` where Validated='0'";
	$result = jb_mysql_query($sql);
	$uv_employers = @array_pop(mysql_fetch_array($result, MYSQL_ASSOC));

	$sql = "SELECT count(*) FROM `profiles_table`";
	$result = jb_mysql_query($sql);
	$profiles = @array_pop(@mysql_fetch_array($result, MYSQL_ASSOC));

	$sql = "SELECT count(*) FROM `posts_table` where expired='N' ";
	$result = jb_mysql_query($sql);
	$posts = array_pop(@mysql_fetch_array($result, MYSQL_ASSOC));
	if (!does_field_exist('resumes_table', 'approved')) {
		$sql = "ALTER TABLE resumes_table ADD `approved` SET('Y', 'N') NOT NULL DEFAULT 'Y' ";
		 jb_mysql_query($sql);
		 echo "[`approved` added to `resume_tables`]<br>";
		
	}
	$sql = "SELECT count(*) FROM `posts_table` where `approved`='N' and `reason`='' AND expired='N' ";
	$result = jb_mysql_query($sql);
	$waiting = @array_pop(@mysql_fetch_array($result, MYSQL_ASSOC));

	$sql = "SELECT count(*) FROM `resumes_table` where `approved`='N'  ";
	$result = jb_mysql_query($sql);
	$r_waiting =@array_pop(mysql_fetch_array($result, MYSQL_ASSOC));;

	$sql = "SELECT count(*) FROM `posts_table` where `approved`='N' AND `reason`!='' AND expired='N' ";
	$result = jb_mysql_query($sql);
	$not_approved = @array_pop(@mysql_fetch_array($result, MYSQL_ASSOC));;

	$sql = "SELECT count(*) FROM `resumes_table`";
	$result = jb_mysql_query($sql);
	$resume = @array_pop(mysql_fetch_array($result, MYSQL_ASSOC));

	$sql = "SELECT count(*) FROM `subscription_invoices` where status='Confirmed' ";
	$result = jb_mysql_query($sql);
	$subscr = @array_pop(mysql_fetch_array($result, MYSQL_ASSOC));

	$sql = "SELECT count(*) FROM `package_invoices` where status='Confirmed'";
	$result = jb_mysql_query($sql);
	$packages = @array_pop(mysql_fetch_array($result, MYSQL_ASSOC));


	

	?>

	<p align="left"><h3>Main Summary</h3></p>
	<font size='1'>Current Local Time: <?php echo JB_get_local_time(gmdate("Y-m-d H:i:s")); ?></font>
	<table  width="80%" border="0" cellpadding="5" style="border-collapse: collapse" >
	<tr>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px;" bgcolor="#FFFFCC"><?php echo $users;?></td>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px;" bgcolor="#FFFFCC">
	<a href="candidates.php">Candidate 
	Accounts</a> (<?php echo $users_online;?> Currently Online<?php if ($employers > 0) echo ', '. @round(($users_returning/$users)*100,2);?>% returning)</td>
	</tr>
	<tr>
	<td style="border-bottom-style: solid; border-bottom-width: 1px;">
	<?php echo $uv_candidates;?></td>
	<td style="border-bottom-style: solid; border-bottom-width: 1px;">
	<a href="candidates.php?show=NA">Un-validated Candidate Accounts<a></td>
	</tr>
	<tr>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px;" bgcolor="#FFFFCC">
	<?php echo $resume;?></td>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px;" bgcolor="#FFFFCC">
	<a href="resumes.php?show=ALL">Posted Resumes</a></td>
	</tr>
	<tr>
	<td style="border-bottom-style: solid; border-bottom-width: 1px"><?php echo $employers;?></td>
	<td style="border-bottom-style: solid; border-bottom-width: 1px"><a href="employers.php">Employer 
	Accounts</a> (<?php echo $employers_online;?> Currently Online<?php if ($employers > 0) echo ', '. @round(($employers_returning/$employers)*100,2);?>% returning)</td>
	</tr>
	<tr>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px" bgcolor="#FFFFCC">
	<?php echo $uv_employers;?></td>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px" bgcolor="#FFFFCC">
	<a href="employers.php?show=NA">Un-validated Employer Accounts<a></td>
	</tr>
	<tr>
	<td style="border-bottom-style: solid; border-bottom-width: 1px"><?php echo $profiles;?></td>
	<td style="border-bottom-style: solid; border-bottom-width: 1px"><a href="profiles.php">Employer 
	Profiles</td>
	</tr>
	<tr>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px" bgcolor="#FFFFCC">
	<?php echo $posts;?></td>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px" bgcolor="#FFFFCC">
	<a href="posts.php">Jobs Posts</a></td>
	</tr>
	<tr>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px" >
	<?php echo $not_approved;?></td>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px" >
	<a href="posts.php?show=NA">Posts Not Approved</a></td>
	</tr>
	<tr>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px" bgcolor="#FFFFCC">
	<?php if ($waiting > 0) { $bold1="<b>"; $bold2="</b>";} echo $bold1.$waiting.$bold2; ?> </td>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px" bgcolor="#FFFFCC">
	<a href="posts.php?show=WA"><?php echo $bold1; ?>Posts Waiting on Queue<?php echo $bold2; ?></a></td>
	</tr>
	<tr>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px;" >
	<?php $bold1=''; $bold2=''; if ($r_waiting > 0) { $bold1="<b>"; $bold2="</b>";} echo $bold1.$r_waiting.$bold2; ?> </td>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px;" >
	<a href="resumes.php?show=WA"><?php echo $bold1; ?>Resumes Waiting on Queue<?php echo $bold2; ?></a></td>
	</tr>
	<tr>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px;"  bgcolor="#FFFFCC">
	<?php echo $subscr;?></td>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px;" bgcolor="#FFFFCC">
	<a href="subscription_report.php">Confirmed Orders - Subscriptions</a></td>
	</tr>
	<tr>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px;"  >
	<?php echo $packages;?></td>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px;"  >
	<a href="package_report.php">Confirmed Orders - Posting Credit</a></td>
	</tr>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px;"  bgcolor="#FFFFCC">
	<?php 

		echo JB_get_users_online_count();
	
	?></td>
	<td style="border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px;" bgcolor="#FFFFCC">
	<a href='online.php'>Users Online (Including guests)</a></td>
	</tr>
	</table>
<a href="stats.php"><small>[More Stats]</small></a> <?php if (JB_CACHE_ENABLED!='NO') { ?>| <a href="main.php?clear_cache=1"><small>[Refresh Cache]</small></a><?php } ?>
<?php

	JBPLUG_do_callback('admin_main_page', $A = false);

	if (JB_DEMO_MODE=='YES') {

		$JBMarkup->ok_msg('Demo mode enabled');

	}


	//do_upgarde (true);
	if (isset($_REQUEST['do_upgrade']) && $_REQUEST['do_upgrade']=='Y') {
		JB_do_upgrade (true);
		JB_cache_flush();
	}

	if (JB_do_upgrade (false)) {
		echo "<p><input style='font-size: 24px;' type='button' value='Upgrade Database' onclick=\"window.location='".htmlentities($_SERVER['PHP_SELF'])."?do_upgrade=Y'\" ></p>";
	}

} 

JBPLUG_do_callback('admin_main', $A = false);

if ($_REQUEST['clear_cache']) {

	
	if (!function_exists('jb_search_category_tree_for_posts')) {
		require_once(jb_basedirpath().'include/posts.inc.php');
	}
	
	JB_init_category_tables(0);
	JB_update_post_count(); // update the total, eg. number of approved posts, number of expired posts, premium approved, expired & waiting
	JB_build_post_count();
	JB_cache_flush();
	
	
	$JBMarkup->ok_msg('Cache refreshed.');


}

echo "<p>&nbsp</p><div>";

JB_theme_check_compatibility();

if (JB_DEMO_MODE!='YES') {

	if (JB_CRON_EMULATION_ENABLED=='YES') {

		echo '<p><font color="maroon">- Attention: Cron Emulation - The system has detected that you have Cron Emulation enabled. Cron Emulation is intended for testing purposes and not recommended for live sites. Please go to <A href="edit_config.php#cron">Admin-&gt;Main Config</a> and see the Cron options</p></font>';

	}

	if (is_writable(jb_get_config_dir().'config.php')) {
		//echo "- config.php is writeable.<br>";
	} else {
		echo "<p><font color='maroon'>- config.php is not writable. Give write permissions to config.php if you want to save any changes</p></font>";
	}

	if (is_writable("../rss.xml")) {
		//echo "- rss.xml is writeable.";
	} else {
		echo "<p><font color='maroon'>- rss.xml is not writable! rss.xml must have write permissions.</font></p>";

	}

	if (file_exists(JB_basedirpath()."admin/install.php")) {
		echo "<p><font color='maroon'>- Please delete install.php from the admin/ directory once the installation is complete.</font><p>";
	}

	if (strpos(ini_get('disable_functions' ), 'fsockopen') !== false) {
		echo "<p><font color='maroon'>- Error! It appears that the fsockopen() function is disabled on this server. This means that some of the payment modules will not work, including PayPal, as they need fsockopen() to communicate with outside hosts for authentication purposes. </font><p>";
	}



	include (JB_basedirpath().'/payment/payment_manager.php');
	if (isset($_PAYMENT_OBJECTS['authorizeNet']) && $_PAYMENT_OBJECTS['authorizeNet']->is_enabled()) {

		if (!defined('AUTHNET_MD5_HASH')) {
			echo "<p><font color='maroon'>- Important: It looks like you have Authorize.net enabled but not yet fully configured. (The latest version requires you to review your Authorize.net settings in Admin->Payment Modules.) </font><p>";
		} 

	} 

	$sql = "select val from jb_variables WHERE `key`='LAST_HOURLY_RUN'";
	$result = jb_mysql_query($sql);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$last_run_time = $row['val'];

	if ($last_run_time+(24*60*60) < time()) {
		echo "<p><font color='maroon'>- The system is reporting that the last time the <a href='cron.php'>Cron job</a> was run was on ".date('r',$last_run_time)."... Please see the <a href='cron.php'>Cron Info</a> for more infromation about setting up a cron job, or enable Cron Emulation in <a href='edit_config.php' >Main Config</a>. Ignore this message if you had set up your Cron job in the last hour - this message will go away after a successful cron run.</font></p>";

	}

	// check if the directory was protected...

	if (!isset($_SERVER['PHP_AUTH_USER'])) {

		echo "<p><font color='maroon'>- The system is recommending that you should password protect your admin/ directory using the features in your web hosting account (or using .htaccess). The admin/ directory is already password protected, but this is limited to php files. By password protecting the entire admin/ directory, you will restrict access to all of files in this directory.</font></p>";

	}

	if (ini_get('register_globals')==true) {
		echo "<p><font color='maroon'>- Security Warning: The system is has detected that you have <b>Register Globls</b> turned on. It is suggested that the script be run with Register Globals set to Off. See here for more information: http://www.php.net/manual/en/security.globals.php</font></p>";

	}



	
	JB_show_lang_permission_warning();
	

}

echo "</div>";


function does_field_exist($table, $field) {

	global $jb_mysql_link;

	$result = jb_mysql_query("show columns from `".jb_escape_sql($table)."`");
	while ($row = @mysql_fetch_row($result)) {
		
		if ($row[0] == $field) {

			return true;

		}

	}

	return false;

}



if ($DB_ERROR=='') {

	// update JB_POSTS_DISPLAY_DAYS value.

	$sql = "REPLACE INTO jb_config (`key`, `val`) VALUES ('POSTS_DISPLAY_DAYS', '".JB_POSTS_DISPLAY_DAYS."') ";
	jb_mysql_query ($sql);

	JB_merge_language_files();

}

JB_admin_footer();
?>

