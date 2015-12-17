<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
define ('NO_HOUSE_KEEPING', true);
@ini_set('memory_limit', '16M');

require "../config.php";
require (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Admin -> Main Config', 'main_config');

?>
<b>[Main Configuration]</b>

 <span style="background-color: #FFFFCC; border-style:outset; padding: 5px;"><a href="edit_config.php">Main</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="editcats.php">Categories</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="editcodes.php">Codes</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="language.php">Languages</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="emailconfig.php">Email Templates</a></span>	
<hr>

<?php

$success = false;

function stripslashes_deep($value) {
    $value = is_array($value) ?
                array_map('stripslashes_deep', $value) :
                stripslashes($value);

    return $value;
}
if ($_REQUEST['save'] != '') {

	
	$_REQUEST = stripslashes_deep($_REQUEST);

	$success = false;

	// validate

	$errors = array();

	$_REQUEST['posts_display_days'] = (int) $_REQUEST['posts_display_days'];
	if ($_REQUEST['posts_display_days']<1) {
		$errors[] = "Posts - 'How 
		  many days until expired': Value must be larger than zero";
	}

	$_REQUEST['p_posts_display_days'] = (int) $_REQUEST['p_posts_display_days'];
	if ($_REQUEST['p_posts_display_days']<1) {
		$errors[] = "Posts - 'Expire premium posts after': Value must be larger than zero";
	}

	$_REQUEST['premium_posts_per_page'] = (int) $_REQUEST['premium_posts_per_page'];
	if ($_REQUEST['premium_posts_per_page']<1) {
		$errors[] = "Posts - 'Premium posts per page': Value must be larger than zero";
	}

	$_REQUEST['img_max_width'] = (int) $_REQUEST['img_max_width'];
	if ($_REQUEST['img_max_width']<1) {
		$errors[] = "Paths and Locations - 'Maximum width of scaled thumbnail images': Value must be larger than zero";
	}

	$_REQUEST['job_alerts_days'] = (int) $_REQUEST['job_alerts_days'];
	if ($_REQUEST['job_alerts_days']<1) {
		$errors[] = "Job Alerts for candidates - 'With an interval of': Value must be larger than zero";
	}

	$_REQUEST['job_alerts_active_days'] = (int) $_REQUEST['job_alerts_active_days'];
	if ($_REQUEST['job_alerts_active_days']<1) {
		$errors[] = "Job Alerts for candidates - 'Send to users who were active within the last': Value must be larger than zero";
	}

	$_REQUEST['job_alerts_items'] = (int) $_REQUEST['job_alerts_items'];
	if ($_REQUEST['job_alerts_items']<1) {
		$errors[] = "Job Alerts for candidates - 'List a maximum of': Value must be larger than zero";
	}

	$_REQUEST['emails_per_batch'] = (int) $_REQUEST['emails_per_batch'];
	if ($_REQUEST['emails_per_batch']<1) {
		$errors[] = "Outgoing email queue - 'Emails per batch': Value must be larger than zero";
	}

	$_REQUEST['emails_days_keep'] = (int) $_REQUEST['emails_days_keep'];
	

	$_REQUEST['manager_posts_per_page'] = (int) $_REQUEST['manager_posts_per_page'];
	if ($_REQUEST['manager_posts_per_page']<1) {
		$errors[] = "Posts - 'Posts per page': Value must be larger than zero";
	}

	

	// validate JB_POSTS_DISPLAY_DAYS

	if (sizeof($errors)==0)  {
	

		$config_str = jb_get_config_code();

		#####################################################
		# Write the config.php file
		#####################################################
		
		if ($_SESSION['ADMIN']==true) {

			if (JB_DEMO_MODE!='YES') {

				$file = @fopen ("../config.php", "w");
				if (@fwrite($file, $config_str, strlen($config_str))) {
					?><p class="ok_msg_label">Configuration Saved! <a href="edit_config.php">Continue</a>.<p>
					<?php


					$success = true;
					
				} else {
					?>
				<p class="error_msg_label">Could not save the configuration. Please make sure that config.php is writable.<p>
				<?php
					
				}
			} 
		}
		######################################################


	} else {

		?>
		<p class="error_msg_label">Could not save the configuration. Some errors were detected:<p>
		<p style="font-weight:bold">
		<?php
		foreach ($errors as $error) {
			echo $error.'<br>';
		}
		?>
		</p>
		<?php

	}


}



if ($success) {

	$_SESSION['JB_THEME'] = $_REQUEST['jb_theme'];

	JB_init_category_tables(0);
	JB_cache_flush();
	JBPLUG_clear_cache();
	JB_compute_cat_has_child();

	// update counters
		
	JB_update_post_count();
	JB_update_resume_count();
	JB_update_profile_count();
	JB_update_employer_count();
	JB_update_user_count();
	
	JB_merge_language_files(true);



}



if (!$success) {

	jb_main_config_form();

}

function jb_main_config_form() {

	global $JBMarkup;

	?>

	Options on this page affect the running of the entire website, including the paths, database setting, look-and-feel, business logic, and more.<p>
	Note: <i>Please make sure that config.php has permissions for writing when editing this form.</i><br>
	<?php

	if (JB_DEMO_MODE!='YES') {
		echo "<p>";
		if (is_writable("../config.php")) {
			echo "- config.php is writeable.<br>";
		} else {
			echo "- Note: config.php is not writable. Give write permissions to config.php if you want to save the changes<br>";
		}

		if (is_writable("../rss.xml")) {
			echo "- rss.xml is writeable.";
		} else {
			echo "- rss.xml is not writable! rss.xml must have write permissions.<br>";

		}
		echo '</p>';

	} else {

		$JBMarkup->ok_msg('Demo mode is enabled - configuration cannot be saved');

	}

	?>

	<p>
	Jump to: <a href="#paths">Paths and Locations</a> | <a href="#mysql">MySQL Settings</a> | <a href="#cache">Cache Settings</a> | <a href="#cron">Cron Settings</a> | <a href="#features">Optional Features</a> | <a href="#date">Localization - Time and Date</a> | <a href="#cats">Categories</a> | <a href="#mod_rewrite">Mod Rewrite</a> | <a href="#clean">Data Cleaning</a> | <a href="#ac">Accounts and Permissions</a> | <a href="#menu">Menu Options</a> | <a href="#search">Search Options</a> | <a href="#anon">Anonymous fields and Request System</a> | <a href="#mem">Membership Fields</a> | <a href="#blocked">Blocked Fields</a> | <a href="#billing">Billing System</a> | <a href="#posts">Posts</a> | <a href="#resumes">Resumes</a> | <a href="#themes">Theme Settings</a> | <a href="#plugins">Plugin Settings</a> | <a href="#email">Email Settings</a> | <a href="#errors">Errors and Warnings</a>
	</p>

	<form method="POST" name="form1" action="edit_config.php">
		<?php 
		if (!defined('JB_CODE_ORDER_BY')) define('JB_CODE_ORDER_BY', 'BY_CODE'); // BY_NAME or BY_CODE ?>
		<input type="hidden" name="jb_code_order_by" value="<?php echo htmlentities($_REQUEST['jb_code_order_by']); ?>">
	  <p>&nbsp;</p>
	  <table  cellpadding="5" cellspacing="2" class="config_form">
		<tr>
		  <td colspan="2" class="config_form_heading" >
		  Website - Board's Name &amp; Headings
		  </td>
		</tr>
		<tr>
		  <td width="20%" class="config_form_label">Site Name</td>
		  <td class="config_form_field">
		  <input type="text" name="site_name" size="29" value="<?php echo (jb_escape_html(JB_SITE_NAME)); ?>"></td>
		</tr>
		<tr>
		  <td class="config_form_label">Site Heading</td>
		  <td class="config_form_field">
		  <input type="text" name="site_heading" size="49" value="<?php echo (jb_escape_html(JB_SITE_HEADING)); ?>">(shown in the &lt;HEAD&gt; tag)</td>
		</tr>
		 <tr>
		  <td class="config_form_label">Site Description</td>
		  <td class="config_form_field">
		  <textarea  name="site_description" cols="35" rows="3"><?php echo (jb_escape_html(JB_SITE_DESCRIPTION)); ?></textarea></td>
		</tr>
		<tr>
		  <td class="config_form_label">Site Keywords</td>
		  <td class="config_form_field">
		  <textarea  name="site_keywords" cols="35" rows="3"><?php echo (jb_escape_html(JB_SITE_KEYWORDS)); ?></textarea></td>
		</tr>
		<tr>
		  <td class="config_form_label">Site Logo URL</td>
		  <td class="config_form_field">
		  <input type="text" name="site_logo_url" size="49" value="<?php echo (htmlentities(JB_SITE_LOGO_URL)); ?>"><br>(http://www.example.com/images/logo.gif) Note: This logo is used on the signup and login screens. To change the logo and other graphics of the site you will need to <a href="http://www.jamit.com/docs.htm">create a custom theme</a>.</td>
		</tr>
		<tr>
		  <td class="config_form_label">Site Contact Email</td>
		  <td class="config_form_field">
		  <input type="text" name="site_contact_email" size="49" value="<?php echo (htmlentities(JB_SITE_CONTACT_EMAIL)); ?>"></td>
		</tr>
		 <tr>
		 <?php

		 if (JB_ADMIN_PASSWORD == 'JB_ADMIN_PASSWORD') {
			$JB_ADMIN_PASSWORD = 'ok';
		} else {
			$JB_ADMIN_PASSWORD = JB_ADMIN_PASSWORD;
		}

		if (JB_DEMO_MODE=='YES') {
			$JB_ADMIN_PASSWORD = '********';
		}

		 ?>
		  <td class="config_form_label">Administrator's Password</td>
		  <td class="config_form_field">
		  <input type="password" name="admin_password" size="49" value="<?php echo (htmlentities($JB_ADMIN_PASSWORD)); ?>"></td>
		</tr>
	  </table>
	  <?php 
	 

	  $host = $_SERVER['SERVER_NAME']; // hostname
	  $http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
	  $http_url = explode ("/", $http_url);
	  array_pop($http_url); // get rid of filename
	  array_pop($http_url); // get rid of /admin
	  $http_url = implode ("/", $http_url);
	
	 $file_path = JB_basedirpath();

	 if (JB_DEMO_MODE=='YES') { 
		 $file_path = '[DEMO MODE]'; 
	  } 
	  
	  ?>
	  <p>&nbsp;</p>
	  <a name="paths"></a>
	  <table cellpadding="5" cellspacing="2" class="config_form">
		<tr>
		  <td colspan="2" class="config_form_heading">
		  Paths and Locations
		  </td>
		</tr>
		<tr>
		  <td width="20%" class="config_form_label">Board's HTTP URL (address)</td>
		  <td class="config_form_field">
		  <input type="text" name="base_http_path" size="49" value="<?php echo htmlentities(str_replace('https','http', JB_BASE_HTTP_PATH)); ?>"><br>Suggested: <b>http://<?php echo $host.$http_url."/"; ?></b></td>
		</tr>
	   
		 <tr>
		  <td class="config_form_label">Candidate's folder name</td>
		  <td class="config_form_field">
		  <input type="text" name="candidate_folder" size="49" value="<?php echo htmlentities(JB_CANDIDATE_FOLDER); ?>" ><br>(eg. myjobs/ - If you change the default name, please remember to rename this directory using FTP or the command line)</td>
		</tr>
		 <tr>
		  <td class="config_form_label">Advertiser's folder name</td>
		  <td class="config_form_field">
		  <input type="text" name="employer_folder" size="49" value="<?php echo htmlentities(JB_EMPLOYER_FOLDER); ?>" ><br>(eg. employers/ - If you change the default name, please remember to rename this directory using FTP or the command line)</td>
		</tr>
		<tr>
		  <td class="config_form_label">Images 
		  Path</td>
		  <td class="config_form_field">
		  <input type="text" name="img_path" size="49" value="<?php if (JB_DEMO_MODE=='YES') { echo '[DEMO MODE]'; } else { echo htmlentities(JB_IMG_PATH); } ?>"><br>Recommended: <b><?php echo $file_path."upload_files/images/</b>";if (!file_exists(JB_IMG_PATH)) { echo "<br><span style='color:red'>Warning:</span> ".JB_IMG_PATH." does not exist"; } elseif (!is_writable(JB_IMG_PATH)) { echo "<br><span style='color:red'>Warning:</span> ".JB_IMG_PATH." is not writable. Please give it permission to be written."; } if (!file_exists(JB_IMG_PATH."thumbs/")) { echo "<br><span style='color:red'>Warning:</span> ".JB_IMG_PATH."thumbs/"." does not exist"; } elseif (!is_writable(JB_IMG_PATH."thumbs/")) { echo "<br><span style='color:red'>Warning:</span> ".JB_IMG_PATH."thumbs/"." is not writable. Please give it permission to be written."; } ?></td>
		</tr>
		<tr>
		  <td class="config_form_label">Images 
		  URL</td>
		  <td class="config_form_field">
		  <input type="text" name="img_http_path" size="49" value="<?php echo htmlentities(str_replace('https','http',JB_IMG_HTTP_PATH)); ?>"><br>Suggested: <b>http://<?php echo $host.$http_url."/upload_files/images/"; ?></b></td>
		</tr>
		<tr>
		  <td class="config_form_label">Files 
		  Path</td>
		  <td class="config_form_field">
		  <input type="text" name="file_path" size="49" value="<?php if (JB_DEMO_MODE=='YES') { echo '[DEMO MODE]'; } else { echo htmlentities(JB_FILE_PATH); } ?>"><br>Suggested: <b><?php echo $file_path."upload_files/docs/</b>"; if (!file_exists(JB_FILE_PATH)) { echo "<br><span style='color:red'>Warning:</span> ".JB_FILE_PATH." does not exist"; } elseif (!is_writable(JB_FILE_PATH)) { echo "<br><span style='color:red'>Warning:</span> ".JB_FILE_PATH." is not writable. Please give it permission to be written."; } ?></td>
		</tr>
		 <tr>
		  <td class="config_form_label">Files 
		  URL</td>
		  <td class="config_form_field">
		  <input type="text" name="file_http_path" size="49" value="<?php echo htmlentities(str_replace('https','http',JB_FILE_HTTP_PATH)); ?>"><br>Suggested: <b>http://<?php echo $host.$http_url."/upload_files/docs/"; ?></b></td>
		</tr>
		<tr>
		  <td class="config_form_label">Maximum width of scaled thumbnail images</td>
		  <td class="config_form_field">
		   <input type="text" size='3' name='img_max_width' value=<?php echo jb_escape_html(JB_IMG_MAX_WIDTH); ?> > (in pixels)</td>
		</tr>
		<tr>
		  <td class="config_form_label" width="20%"> 
		   Keep original images?</td>
		  <td class="config_form_field">
		   <input type="radio" name="jb_keep_original_images" size="49" value="YES" <?php if (JB_KEEP_ORIGINAL_IMAGES=='YES') { echo " checked "; } ?> >Yes - keep the originals after scailing them
			<br>
			<?php

			if (!defined('JB_BIG_IMG_MAX_WIDTH')) {
				define ('JB_BIG_IMG_MAX_WIDTH', 1000);
			}

			?>
		  
		   Maximum width of original images: <input type="text" size='3' name='big_img_max_width' value=<?php echo jb_escape_html(JB_BIG_IMG_MAX_WIDTH); ?> > (in pixels)
		   <br><input type="radio" name="jb_keep_original_images" value="NO" <?php if (JB_KEEP_ORIGINAL_IMAGES!='YES') { echo " checked "; } ?> >No - delete the originals
		  
		   </td>
		</tr>

		<td class="config_form_label" valign="top">Image Scaling Method</td>
		  <td class="config_form_field">
		  <?php
			if (function_exists('imagecreate')) {


		  ?> Using the GD Library to resize images in to thumbnails<?php

		} else {

			?>
			<span style="color:maroon">It looks like the GD Library is not installed on your system. Please ask your hosting administrator to enable the GD Library for PHP.</span>
			<?php

		}

	?>
	<input type="hidden" name="use_gd_library" value="YES">
		

		
		<tr>
		  <td class="config_form_label">Path & Filename to 
		  RSS Feed XML file</td>
		  <td class="config_form_field">
		  <input type="text" name="rss_feed_path" size="49" value="<?php if (JB_DEMO_MODE=='YES') { echo '[DEMO MODE]'; } else { echo htmlentities(JB_RSS_FEED_PATH); } ?>"><br>Suggested: <b><?php echo $file_path."rss.xml </b> "; if (!file_exists(JB_RSS_FEED_PATH)) { echo "<br><span style='color:red'>Warning:</span> ".JB_RSS_FEED_PATH." does not exist"; } elseif (!is_writable(JB_RSS_FEED_PATH)) { echo "<br><span style='color:red'>Warning:</span> ".JB_RSS_FEED_PATH." is not writable. Please give it permission to be written."; }?></td>
		</tr>
		<?php
		if ((JB_RSS_FEED_LOGO=='') || (JB_RSS_FEED_LOGO=='http://www.jamit.com.au/images/rss-logo.gif')) {

			$JB_RSS_FEED_LOGO = JB_IMG_HTTP_PATH."rss_logo.png";

		 } else {

			 $JB_RSS_FEED_LOGO = JB_RSS_FEED_LOGO;

		 }
		
		
		?>
		 <tr>
		  <td class="config_form_label"> 
		  RSS Feed Logo URL</td>
		  <td class="config_form_field">
		  <input type="text" name="rss_feed_logo" size="49" value="<?php echo htmlentities($JB_RSS_FEED_LOGO); ?>"><br>(Enter the full URL of the logo, eg. <b><?php echo JB_BASE_HTTP_PATH; ?>upload_files/images/rsslogo.gif</b> . The maximum width & height are: 144 wide x 400 high. The logo for the RSS feed should be placed somewhere inside the installation directory of the script)<?php if (!JB_resolve_document_path(JB_RSS_FEED_LOGO)) { echo "<br><span style='color:red'>Warning:</span> Cannot find ".$JB_RSS_FEED_LOGO." on the local server."; } ?></td>
		</tr>
<?php


if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {  // only for windows...

	

?>

		<tr>
		  <td class="config_form_label">File &amp; Directory Permissions</td>
		  <td class="config_form_field">
		  
		  chmod <select name="jb_new_file_chmod">
		  <option label="444" value="0444" <?php if (JB_NEW_FILE_CHMOD==0444) echo ' selected="selected" '; ?>>600</option>
			<option label="600" value="0600" <?php if (JB_NEW_FILE_CHMOD==0600) echo ' selected="selected" '; ?>>600</option>
			<option label="640" value="0640" <?php if (JB_NEW_FILE_CHMOD==0640) echo ' selected="selected" '; ?>>640</option>
			<option label="644" value="0644" <?php if (JB_NEW_FILE_CHMOD==0644) echo ' selected="selected" '; ?>>644</option>
			<option label="660" value="0660" <?php if (JB_NEW_FILE_CHMOD==0660) echo ' selected="selected" '; ?>>660</option>
			<option label="664" value="0664" <?php if (JB_NEW_FILE_CHMOD==0664) echo ' selected="selected" '; ?>>664</option>
			<option label="666" value="0666" <?php if (JB_NEW_FILE_CHMOD==0666) echo ' selected="selected" '; ?>>666</option>
		  </select> for new files
		  <br>
		  
		chmod <select name="jb_new_dir_chmod">
			<option label="0555" value="0555" <?php if (JB_NEW_DIR_CHMOD==0555) echo ' selected="selected" '; ?>>700</option>
			<option label="750" value="0750" <?php if (JB_NEW_DIR_CHMOD==0750) echo ' selected="selected" '; ?>>750</option>
			<option label="755" value="0755" <?php if (JB_NEW_DIR_CHMOD==0755) echo ' selected="selected" '; ?>>755</option>
			<option label="770" value="0770" <?php if (JB_NEW_DIR_CHMOD==0770) echo ' selected="selected" '; ?>>770</option>
			<option label="775" value="0775" <?php if (JB_NEW_DIR_CHMOD==0775) echo ' selected="selected" '; ?>>775</option>
			<option label="777" value="0777" <?php if (JB_NEW_DIR_CHMOD==0777) echo ' selected="selected" '; ?>>777</option>
		</select>
		  for new directories<br>

		 <?php

//			 $new_window = "onclick=\"test_permissions_window(); return false;\"";

			 ?>

		 <input <?php if (JB_DEMO_MODE=='YES') { echo ' disabled '; } ?> type="button" value="Suggest Best Permissions" onclick="suggest_permissions_window(); return false;"><br>
		 <input <?php if (JB_DEMO_MODE=='YES') { echo ' disabled '; } ?> type="button" value="Fix Permissions" onclick="fix_permissions_window(); return false;">
		
	
		<br></td></tr>

		
<?php
	
}

?>

		<tr>
		  <td class="config_form_label">Test file &amp; Directory Permissions</td>
		  <td class="config_form_field">
		   <input <?php if (JB_DEMO_MODE=='YES') { echo ' disabled '; } ?> type="button" value="Test Permissions" onclick="window.open('suggest_permissions.php?test=1', '', 'toolbar=no, scrollbars=yes, location=no, statusbar=no, menubar=no, resizable=1, width=400, height=300, left = 50, top = 50'); return false;">
Click to see if all the permissions are correct so that the job board can manage the files properly.
		  </td>

		
	  </table>
	  <p>&nbsp;</p>
	  <a name="mysql"></a>
	  <table cellpadding="5" cellspacing="2" class="config_form">
		<tr>
		  <td colspan="2"  class="config_form_heading">
		  Mysql Settings
		  <?php

		  if ($DB_ERROR != '') {

			  ?><br>There is a problem with your database settings: <span style='color:red'><?php

			echo $DB_ERROR;
			?></span><?php 
				

		  }
		  
		  ?></td>
		</tr>
		<tr>
		  <td width="20%" class="config_form_label">Mysql 
		  Database Username</td>
		  <td class="config_form_field">
		  <input type="text" name="jb_mysql_user" size="29" value="<?php if (JB_DEMO_MODE=='YES') { echo '[DEMO MODE]'; } else { echo jb_escape_html(JB_MYSQL_USER);} ?>"></td>
		</tr>
		 <tr>
		  <td class="config_form_label">Mysql 
		  Database Password</td>
		  <td class="config_form_field">
		  <input type="password" name="jb_mysql_pass" size="29" value="<?php  if (JB_DEMO_MODE=='YES') { echo '********'; } else { echo jb_escape_html(JB_MYSQL_PASS); } ?>"></td>
		</tr>
		<tr>
		  <td class="config_form_label">Mysql 
		  Database Name</td>
		  <td class="config_form_field">
		  <input type="text" name="jb_mysql_db" size="29" value="<?php if (JB_DEMO_MODE=='YES') { echo '[DEMO MODE]'; } else { echo jb_escape_html(JB_MYSQL_DB); } ?>"></td>
		</tr>
		<tr>
		  <td class="config_form_label">Mysql 
		  Server Hostname</td>
		  <td class="config_form_field">
		  <input type="text" name="jb_mysql_host" size="29" value="<?php if (JB_DEMO_MODE=='YES') { echo '[DEMO MODE]'; } else { echo jb_escape_html(JB_MYSQL_HOST); } ?>"></td>
		</tr>
	  </table>
	   <p>&nbsp;</p>
	   <a name="cache"></a>
		<table cellpadding="5" cellspacing="2" class="config_form">
		<tr>
		  <td colspan="2" class="config_form_heading">
		  Cache Settings</td>
		</tr>
		<tr>
		  <td class="config_form_label" width="20%"> 
		   Enable Cache?</td>
		  <td class="config_form_field">

		  <input type="radio" name="jb_cache_enabled" value="YES" <?php if (JB_CACHE_ENABLED=='YES') { echo " checked "; } ?> >Yes (enabled) - If turned on, then this feature will help increase performance. Please select below what caching method to use:
		  <ul style="list-style:none">
		  <li>
		  <?php

		if (!defined('JB_CACHE_DRIVER')) {
			define ('JB_CACHE_DRIVER', 'JBCacheFiles');
		}

		$cache_objects = JB_get_cache_objects();
		foreach ($cache_objects as $obj) {
			$obj->config_radio();
		}


		?>
		</li>
		</ul>
		<input type="radio" name="jb_cache_enabled" value="NO" <?php if (JB_CACHE_ENABLED=='NO') { echo " checked "; } ?> >No (disabled)
		   
		   
		   </td>
		</tr>
		</table>
		<p>&nbsp;</p>
		<a name="cron"></a>
		<table cellpadding="5" cellspacing="2" class="config_form">
		<tr>
		  <td colspan="2" class="config_form_heading">
		  Cron Settings. (<a href="cron.php">See here for more info about these options.</a>)</td>
		</tr>
		<?php


		if (!defined('JB_CRON_EMULATION_ENABLED')) {
			define ('JB_CRON_EMULATION_ENABLED', 'NO');
		}
		
		?>
		<tr>
		  <td class="config_form_label" width="20%"> 
		   Enable Cron Emulation?</td>
		  <td class="config_form_field">
		   <input type="radio" name="cron_emulation_enabled" size="49" value="YES" <?php if (JB_CRON_EMULATION_ENABLED=='YES') { echo " checked "; } ?> >Yes (Set to 'Yes' only if your web host does not support Cron / scheduling of tasks. Cron Emulation is intended for testing purposes and not recommended for live sites. Please see <a href="cron.php">Admin-&gt;Cron Info</a> for more details about how to setup a cron job)
		   <br><input type="radio" name="cron_emulation_enabled" value="NO" <?php if (JB_CRON_EMULATION_ENABLED!='YES') { echo " checked "; } ?> >No (Turn off cron emulation. Please see <a href="cron.php">Admin-&gt;Cron Info</a> for more details about how to setup a cron job, including what to do if your hosting account does not support Cron jobs)</td>
		</tr>
		<tr>
		  <td class="config_form_label" width="20%"> 
		   Allow Cron to be executed via web?</td>
		  <td class="config_form_field">
		  <?php

			if (!defined('JB_CRON_HTTP_ALLOW')) {
				define('JB_CRON_HTTP_ALLOW', 'YES');
			}

			?>
			<i>Note: The following settings are ignored if Cron Emulation is enabled</i><br>
		   <input type="radio" name="cron_http_allow" size="49" value="YES" <?php if (JB_CRON_HTTP_ALLOW=='YES') { echo " checked "; } ?> >Yes (It will be possible to run the cron job by loading this page: <i><?php echo JB_BASE_HTTP_PATH.'cron/cron.php';?></i>)
		   <br><i>Optional:</i> If 'Yes', require authentication - user: <input type="text" size="10" name="cron_http_user" value="<?php if (!defined('JB_CRON_HTTP_USER')) define('JB_CRON_HTTP_USER', ''); if (!defined('JB_CRON_HTTP_PASS')) define('JB_CRON_HTTP_PASS', ''); echo jb_escape_html(JB_CRON_HTTP_USER); ?>"> pass: <input type="password" size="10" name="cron_http_pass" value="<?php echo jb_escape_html(JB_CRON_HTTP_PASS); ?>"><br>
		   <?php if (strlen(JB_CRON_HTTP_USER)>0) { ?>user/pass is set, please use the following URL: <b><?php echo str_replace('://', '://'.JB_CRON_HTTP_USER.':'.JB_CRON_HTTP_PASS.'@', JB_BASE_HTTP_PATH).'cron/cron.php';?></b><br> <?php } ?>
		   <input type="radio" name="cron_http_allow" value="NO" <?php if (JB_CRON_HTTP_ALLOW!='YES') { echo " checked "; } ?> >No (Do not allow.)
		   </td>
		</tr>
		<?php
		
		$disabled = explode(', ', ini_get('disable_functions'));
		if (!in_array('exec', $disabled)) {
			@JB_exec ("w", $out);
		}
		if (preg_match('#load average: (\d+\.\d+)#', $out[0], $m)) {



		?>
		<tr>
		  <td width="20%" class="config_form_field"> 
		   Server Load Limit for Cron</td>
		  <td class="config_form_field">
		  Do not run Cron if the server load average is over: <select name="jb_cron_limit">
		  <option value="1.0" <?php if (JB_CRON_LIMIT=='1.0') { echo " selected ";} ?> >1.0</option value="1.0">
		  <option value="2.0" <?php if (JB_CRON_LIMIT=='2.0') { echo " selected ";} ?> >2.0</option>
		  <option value="3.0" <?php if (JB_CRON_LIMIT=='3.0') { echo " selected ";} ?> >3.0</option>
		  <option value="4.0" <?php if (JB_CRON_LIMIT=='4.0') { echo " selected ";} ?> >4.0</option>
		  <option value="10.0" <?php if (JB_CRON_LIMIT=='10.0') { echo " selected ";} ?> >10.0</option>
		  <option value="" <?php if ((JB_CRON_LIMIT=='') || !defined('JB_CRON_LIMIT')) { echo " selected ";} ?> >No Limit</option>
		  </select> (See about <a href="http://en.wikipedia.org/wiki/Load_(computing)" target="_new">Load Average</a> in Wikipedia)
		   </td>
		</tr>
		<?php

		}

		?>
		</table>

		<p>&nbsp;</p>
		<a name="features"></a>
	  <table cellpadding="5" cellspacing="2" class="config_form">
		<tr>
		  <td colspan="2" class="config_form_heading">
		  <p>Features - Disable / Enable Optional Features.</td>
		</tr>
		<tr>
		  <td class="config_form_label" width="20%"> 
		  Enable Map?</td>
		  <td class="config_form_field">
		  <input type="radio" name="map_disabled" size="49" value="YES" <?php if (JB_MAP_DISABLED=='YES') { echo " checked "; } ?> >Disabled (Do not show)<br>
		  <input type="radio" name="map_disabled" value="NO" <?php if (JB_MAP_DISABLED!='YES') { echo " checked "; } ?> >Yes (Enable the original map system)<br> 
		  <input type="radio" name="map_disabled" size="49" value="GMAP" <?php if (JB_MAP_DISABLED=='GMAP') { echo " checked "; } ?> >Google Maps - This option will enable the ability to add a Google Map field in to the forms<br><br>
		   <input type="hidden" name="pin_image_file" size="49" value="<?php echo htmlentities(JB_PIN_IMAGE_FILE); ?>">
		   <b>Google Map Setup - Google Maps Version 3 API</b><br>
		   <?php

		if (!defined('JB_GMAP_LOCATION')) {
		  define ('JB_GMAP_LOCATION', 'http://maps.google.com/maps?showlabs=1&ie=UTF8&ll=38.891033,-93.427734&spn=33.74472,73.740234&t=h&z=4');

		}

		if (!defined('JB_GMAP_ZOOM')) {
		  define ('JB_GMAP_ZOOM', '6');

		}	

		if (!defined('JB_GMAP_LANG')) {
		  define ('JB_GMAP_LANG', 'en');

		}

		if (!defined('JB_GMAP_SHOW_IF_MAP_EMPTY')) {
		  define ('JB_GMAP_SHOW_IF_MAP_EMPTY', 'NO');

		}

		
		  
		  

		  ?>
		   Default Map Location URL: <br><input style="font-size: 9px" type="text" name="gmap_location" size="160" value="<?php echo htmlentities(JB_GMAP_LOCATION); ?>"> (Go to <a href="http://maps.google.com/" target="_blank">http://maps.google.com</a> - move the map to a desired location, click the 'Link' top-right, copy the link and paste it here)<br>
		   
		   Default Zoom Level: <input type="text" name="gmap_zoom" size="1" value="<?php echo htmlentities(JB_GMAP_ZOOM); ?>"> (Please enter a number. eg. 6)<br>
		   <input type="checkbox" value="Y" <?php if (JB_GMAP_SHOW_IF_MAP_EMPTY=='YES') echo ' checked '; ?> name="gmap_show_if_map_empty">Show Map if no location selected<br>
		  <!-- Map Language: <input type="text" name="gmap_lang" size="1" value="<?php echo htmlentities(JB_GMAP_LANG); ?>"> (Eg. en, see the full list of <a target="_blank" href="http://spreadsheets.google.com/pub?key=p9pdwsai2hDMsLkXsoM05KQ&gid=1">languages suppoted by Google Maps</a>)
		  -->
		  <br>
			<input type="hidden" name="map_image_file" size="49" value="<?php echo htmlentities(JB_MAP_IMAGE_FILE); ?>">
			<hr>
			<i>If using the original map system: To change the default map images you must create your own theme. See include/themes/README.txt for info about how to create your own theme. The images are in the theme's images/ directory called <?php echo JB_PIN_IMAGE_FILE;?> and <?php echo JB_MAP_IMAGE_FILE; ?>. This feature will be phased out in the future in favor of a Google Maps plugin</i>
			<input type="hidden" name="gmap_lat" value="<?php echo htmlentities(JB_GMAP_LAT); ?>">
			<input type="hidden" name="gmap_lng" value="<?php echo htmlentities(JB_GMAP_LNG); ?>">
<?php

		$file_name = 'posting-form.php';

		$this_theme = JB_THEME;
		if ($config = file_get_contents(jb_basedirpath().'config.php')) {
			preg_match('/JB_THEME\', \'(.+?)\'/i', $config, $m);
			if (isset($m[1])) {
				$this_theme = $m[1];
			}
		}

		if (file_exists(jb_basedirpath().'include/themes/'.$this_theme.'/'.$file_name)) {
			$path = jb_basedirpath().'include/themes/'.$this_theme.'/'.$file_name;
			$contents = file_get_contents($path);
		} else {
			$path = jb_basedirpath().'include/themes/'.JB_DEFAULT_THEME.'/'.$file_name;
			$contents = file_get_contents($path);
		}	

		if (!preg_match('#\$DynamicForm->display_form_section\(\$mode, 4, \$admin\);#i', $contents)) {
			echo '<div style="background-color:white; border:orange solid 2px; padding:5px; width:80%">- Template incompatibility detected for Google Maps. <br>Please edit <b>'.$path.'</b> and replace the following code<br>
			'.highlight_string('
			if (JB_MAP_DISABLED != \'YES\' ) {
				', true).'
			
			<br><br>
			with this PHP code: <br><br>'.highlight_string('
			if (JB_MAP_DISABLED ==\'GMAP\') {
				echo \'<td valign="top">\';
				if ($mode == "EDIT") {
					echo "[Section 4]";
				}
				$DynamicForm->display_form_section($mode, 4, $admin);
				echo \'</td>\';
			}
			elseif (JB_MAP_DISABLED != \'YES\' ) {
				', true).'</div><br>';
		}

?>
		  </td>
		</tr>
		<tr>
		  <td class="config_form_label" width="20%"> 
		   Enable 'Mouseover' Preview of images on Resume list?</td>
		  <td class="config_form_field">
		   <input type="radio" name="preview_resume_image" size="49" value="YES" <?php if (JB_PREVIEW_RESUME_IMAGE=='YES') { echo " checked "; } ?> >Yes (show them - when listing resumes, a preview thumbnail will show when mouse poniter is over a name)<br><input type="radio" name="preview_resume_image" value="NO" <?php if (JB_PREVIEW_RESUME_IMAGE!='YES') { echo " checked "; } ?> >No (disabled)</td>
		</tr>
		<tr>
		  <td class="config_form_label" width="20%"> 
		   Enable multiple languages for employers?</td>
		  <td class="config_form_field">
		  <input type="radio" name="emp_lang_enabled" size="49" value="YES" <?php if (JB_EMP_LANG_ENABLED=='YES') { echo " checked "; } ?> >Yes (Can change language)<br> <input type="radio" name="emp_lang_enabled" value="NO" <?php if (JB_EMP_LANG_ENABLED!='YES') { echo " checked "; } ?> >No (Only default language) </td>
		</tr>
		<tr>
		  <td class="config_form_label">
		   Enable multiple languages for candidates?</td>
		  <td class="config_form_field">
		  <input type="radio" name="can_lang_enabled" size="49" value="YES" <?php if (JB_CAN_LANG_ENABLED=='YES') { echo " checked "; } ?> >Yes (Can change language)<br><input type="radio" name="can_lang_enabled" value="NO" <?php if (JB_CAN_LANG_ENABLED!='YES') { echo " checked "; } ?> >No (Only default language)</td>
		</tr>
		<tr>
		  <td class="config_form_label"> 
		   Enable Job Alerts for candidates?</td>
		  <td class="config_form_field">
		  <input type="radio" name="job_alerts_enabled" size="49" value="YES" <?php if (JB_JOB_ALERTS_ENABLED=='YES') { echo " checked "; } ?> >Yes (Enabled. With an interval of <input type="text" name='job_alerts_days' value="<?php  echo jb_escape_html(JB_JOB_ALERTS_DAYS); ?>" size="2"> day(s) between sending dates)<br>
		  &nbsp;&nbsp;&nbsp;&nbsp;- Send to users who were active within the last <input size='1' type='text'  name='job_alerts_active_days' value='<?php echo jb_escape_html(JB_JOB_ALERTS_ACTIVE_DAYS); ?>'> days<br>
		  &nbsp;&nbsp;&nbsp;&nbsp;- List a maximum of <input type='text' size='1'  name='job_alerts_items' value='<?php echo jb_escape_html(JB_JOB_ALERTS_ITEMS); ?>'> items on the email<br>
		  <input type="radio" name="job_alerts_enabled" value="NO" <?php if (JB_JOB_ALERTS_ENABLED!='YES') { echo " checked "; } ?> >No (Disabled)</td>
		</tr>
		<tr>
		  <td class="config_form_label"> 
		   Enable R&#233;sum&#233;s Alerts for employers?</td>
		  <td class="config_form_field">
		  <input type="radio" name="resume_alerts_enabled" size="49" value="YES" <?php if (JB_RESUME_ALERTS_ENABLED=='YES') { echo " checked "; } ?> >Yes (Enabled. With an interval of <input type="text" name='resume_alerts_days' value="<?php echo jb_escape_html(JB_RESUME_ALERTS_DAYS); ?>" size="2"> day(s) between sending dates)<br>
		  &nbsp;&nbsp;&nbsp;&nbsp;- Send to users who were active within the last <input size='1' type='text'  name='resume_alerts_active_days' value='<?php echo jb_escape_html(JB_RESUME_ALERTS_ACTIVE_DAYS); ?>'> days<br>
		  &nbsp;&nbsp;&nbsp;&nbsp;- List a maximum of <input type='text' size='1'  name='resume_alerts_items' value='<?php echo jb_escape_html(JB_RESUME_ALERTS_ITEMS); ?>'> items on the email<br>
		  <input type="checkbox" value="YES" name="jb_resume_alerts_sub_ignore" <?php if (JB_RESUME_ALERTS_SUB_IGNORE=="YES") { echo " checked "; } ?> > Ignore subscription status (if not checked then send only to subscribed employers).<br>
		  <input type="radio" name="resume_alerts_enabled" value="NO" <?php if (JB_RESUME_ALERTS_ENABLED!='YES') { echo " checked "; } ?> >No (Disabled)</td>
		</tr>

		<tr>
		  <td class="config_form_label" width="20%"> 
		   Enable Online Applications?</td>
		  <td  class="config_form_field">
		  <table border=0>
		  <tr>
			<td class="config_form_field">
				<input type="radio" name="online_app_enabled" size="49" value="YES" <?php if (JB_ONLINE_APP_ENABLED=='YES') { echo " checked "; } ?> >Yes (Enabled)
			</td>
		   <td>
				<table border=0>
					<tr>
						<td class="config_form_field">
						<input type="checkbox" value="YES" name="online_app_sign_in" <?php if (JB_ONLINE_APP_SIGN_IN=="YES") { echo " checked "; } ?> > Candidate needs to be logged on in do applications.
						</td>
					</tr>
					<tr>
						<td class="config_form_field">
						<input type="checkbox" value="YES" name="online_app_email_admin" <?php if (JB_ONLINE_APP_EMAIL_ADMIN=="YES") { echo " checked "; 	} ?> > Email the Application to Administrator
						</td>
					</tr>
					<tr>
						<td class="config_form_field"><input type="checkbox" value="YES" name="online_app_email_premium" <?php if (JB_ONLINE_APP_EMAIL_PREMIUM=="YES") { echo " checked "; 	} ?> > Email the Application to Employer, if applying to <b>Premium</b> post
						</td>
					</tr>
					<tr>
						<td class="config_form_field"><input type="checkbox" value="YES" name="online_app_email_std" <?php if (JB_ONLINE_APP_EMAIL_STD=="YES") { echo " checked "; 	} ?> > Email the Application to Employer, if applying to <b>Standard</b> post
						</td>
					</tr>
					<tr>
						<td class="config_form_field">
						<hr>The following 3 options should only be enabled when the candidate has to log in to do applications:<br>
						<input type="checkbox" value="YES" name="online_app_reveal_premium" <?php if (JB_ONLINE_APP_REVEAL_PREMIUM=="YES") { echo " checked "; 	} ?> > Reveal candidate's 'anonymous' fields to Employer, if applying to <b>Premium</b> post (note: If the application email is sent to employer, they will be able to see the candidates contact details regardless of this option)
						</td>
					</tr>
					<tr>
						<td class="config_form_field">
						<input type="checkbox" value="YES" name="online_app_reveal_std" <?php if (JB_ONLINE_APP_REVEAL_STD=="YES") { echo " checked "; 	} ?> > Reveal candidate's 'anonymous' fields to Employer, if applying to <b>Standard</b> post (note: If the application email is sent to employer, they will be able to see the candidates contact details regardless of this option)
						</td>
					</tr>
					<tr>
						<td class="config_form_field">
						<input type="checkbox" value="YES" name="online_app_reveal_resume" <?php if (JB_ONLINE_APP_REVEAL_RESUME=="YES") { echo " checked "; 	} ?> > Reveal candidate's online resume to the Employer, even if employer is not subscribed to view the resumes. (The employer will be able to access the resume through a special link received in the Application email.)
					</td>
					</tr>
					<tr>
						<td class="config_form_field">
						<hr><input type="checkbox" value="YES" name="jb_app_choice_switch" <?php if (JB_APP_CHOICE_SWITCH=="YES") { echo " checked "; 	} ?> > Allow the employer to choose how this application is sent (Via the website / via custom URL / turn off)
						</td>
					</tr>
					</table>
				</td>
			</tr>
			</table>
				<br><input type="radio" name="online_app_enabled" value="NO" <?php if (JB_ONLINE_APP_ENABLED!='YES') { echo " checked "; } ?> >No (Disabled)
			</td>
		</tr>
		
		<tr>
		  <td class="config_form_label" width="20%"> 
		   Enable 'Tell a Friend about a Job' feature?</td>
		  <td class="config_form_field">
		   <input type="radio" name="taf_enabled" size="49" value="YES" <?php if (JB_TAF_ENABLED=='YES') { echo " checked "; } ?> >Yes (Enabled, <input type="checkbox" value="YES" name="taf_sign_in" <?php if (JB_TAF_SIGN_IN=="YES") { echo " checked "; 	} ?> > Candidate needs to be logged on to use this feature.)<br><input type="radio" name="taf_enabled" value="NO" <?php if (JB_TAF_ENABLED!='YES') { echo " checked "; } ?> >No (Disabled)</td>
		</tr>
		<tr>
		  <td class="config_form_label" width="20%"> 
		   Enable 'Save Job' feature?</td>
		  <td class="config_form_field">
		   <input type="radio" name="save_job_enabled" size="49" value="YES" <?php if (JB_SAVE_JOB_ENABLED=='YES') { echo " checked "; } ?> >Yes (Enabled)<br><input type="radio" name="save_job_enabled" value="NO" <?php if (JB_SAVE_JOB_ENABLED!='YES') { echo " checked "; } ?> >No (Disabled)</td>
		</tr>
		<tr>
		<?php

		if (!defined('JB_SHOW_PREMIUM_LIST')) {
			define('JB_SHOW_PREMIUM_LIST', 'YES');
		}

		?>
		  <td class="config_form_label"  width="20%"> 
		   Enable 'Premium Posts' list at the top of the home page?</td>
		  <td class="config_form_field">
		   <input type="radio" name="jb_show_premium_list" size="49" value="YES" <?php if (JB_SHOW_PREMIUM_LIST=='YES') { echo " checked "; } ?> >Yes (Enabled) - <input type="checkbox" name="jb_dont_repeat_premium" value="YES" <?php if (JB_DONT_REPEAT_PREMIUM=='YES') echo ' checked ';?>> Do not repeat showing premium posts on the non-premium list<br><input type="radio" name="jb_show_premium_list" value="NO" <?php if (JB_SHOW_PREMIUM_LIST!='YES') { echo " checked "; } ?> >No (Disabled)</td>
		</tr>
		
		</table>

	  <p>&nbsp;</p>
	  <a name="date"></a>
	  <table class="config_form" cellpadding="5" cellspacing="2" >
		<tr>
		  <td colspan="2" class="config_form_heading">
		  Localization - Time and Date</td>
		</tr>
		<tr>
		  <td width="20%" class="config_form_label" >Display Date Format</td>
		  <td class="config_form_field">
		  <input type="text" name="date_format" size="49" value="<?php echo htmlentities(JB_DATE_FORMAT); ?>"><br>(see http://www.php.net/date for formatting info. Determines how the date is formatted when it is displayed)</td>
		</tr>
		<?php

		

		if (!defined('JB_DATE_INPUT_SEQ')) {
			define ('JB_DATE_INPUT_SEQ', 'YMD');
		}
		
		
		?>
		<tr>
		  <td width="20%" class="config_form_label">Input Date Sequence</td>
		  <td class="config_form_field" >
		   <input type="text" name="date_input_seq" size="49" value="<?php echo jb_escape_html(JB_DATE_INPUT_SEQ); ?>"> Eg. YMD for the international date standard (ISO 8601). The sequence should always contain one D, one M and one Y only, in any order. This will determine the order in the way the date is <b>inputted</b>. (You should not use any dashes for this setting, only letters Y, M, D case-sensitive!)
		  </td>
		 </tr>
		<tr>
		  <td width="20%" class="config_form_label">GMT Difference</td>
		  <td class="config_form_field">
		  <select name="gmt_dif" value="<?php echo htmlentities(JB_GMT_DIF); ?>">
		  <option value="-12" <?php if (JB_GMT_DIF=='-12.00') { echo " selected "; } ?> >-12:00</option>
		  <option value="-11" <?php if (JB_GMT_DIF=='-11.00') { echo " selected "; } ?> >-11:00</option>
		  <option value="-10" <?php if (JB_GMT_DIF=='-10.00') { echo " selected "; } ?> >-10:00</option>
		  <option value="-9" <?php if (JB_GMT_DIF=='-9.00') { echo " selected "; } ?> >-9:00</option>
		  <option value="-8" <?php if (JB_GMT_DIF=='-8.00') { echo " selected "; } ?> >-8:00</option>
		  <option value="-7" <?php if (JB_GMT_DIF=='-7.00') { echo " selected "; } ?> >-7:00</option>
		  <option value="-6" <?php if (JB_GMT_DIF=='-6.00') { echo " selected "; } ?> >-6:00</option>
		  <option value="-5" <?php if (JB_GMT_DIF=='-5.00') { echo " selected "; } ?> >-5:00</option>
		  <option value="-4" <?php if (JB_GMT_DIF=='-4.00') { echo " selected "; } ?> >-4:00</option>
		  <option value="-3.5" <?php if (JB_GMT_DIF=='-3.5') { echo " selected "; } ?> >-3:30</option>
		  <option value="-3" <?php if (JB_GMT_DIF=='-3.00') { echo " selected "; } ?> >-3:00</option>
		  <option value="-2" <?php if (JB_GMT_DIF=='-2.00') { echo " selected "; } ?> >-2:00</option>
		  <option value="-1" <?php if (JB_GMT_DIF=='-1.00') { echo " selected "; } ?> >-1:00</option>
		  <option value="0" <?php if (JB_GMT_DIF=='0') { echo " selected "; } ?> >0:00</option>
		  <option value="1" <?php if (JB_GMT_DIF=='1.00') { echo " selected "; } ?> >+1:00</option>
		  <option value="2" <?php if (JB_GMT_DIF=='2.00') { echo " selected "; } ?> >+2:00</option>
		  <option value="3" <?php if (JB_GMT_DIF=='3.00') { echo " selected "; } ?> >+3:00</option>
		  <option value="3.5" <?php if (JB_GMT_DIF=='3.50') { echo " selected "; } ?> >+3:30</option>
		  <option value="4" <?php if (JB_GMT_DIF=='4.00') { echo " selected "; } ?> >+4:00</option>
		  <option value="4.5" <?php if (JB_GMT_DIF=='4.5') { echo " selected "; } ?> >+4:30</option>
		  <option value="5" <?php if (JB_GMT_DIF=='5.00') { echo " selected "; } ?> >+5:00</option>
		  <option value="5.5" <?php if (JB_GMT_DIF=='5.50') { echo " selected "; } ?> >+5:30</option>
		  <option value="5.75" <?php if (JB_GMT_DIF=='5.75') { echo " selected "; } ?> >+5:45</option>
		  <option value="6" <?php if (JB_GMT_DIF=='6.00') { echo " selected "; } ?> >+6:00</option>
		  <option value="6.5" <?php if (JB_GMT_DIF=='6.5') { echo " selected "; } ?> >+6:30</option>
		  <option value="7" <?php if (JB_GMT_DIF=='7.00') { echo " selected "; } ?> >+7:00</option>
		  <option value="8" <?php if (JB_GMT_DIF=='8.00') { echo " selected "; } ?> >+8:00</option>
		  <option value="9" <?php if (JB_GMT_DIF=='9.00') { echo " selected "; } ?> >+9:00</option>
		  <option value="9.5" <?php if (JB_GMT_DIF=='9.5') { echo " selected "; } ?> >+9:30</option>
		  <option value="10" <?php if (JB_GMT_DIF=='10.00') { echo " selected "; } ?> >+10:00</option>
		  <option value="11" <?php if (JB_GMT_DIF=='11.00') { echo " selected "; } ?> >+11:00</option>
		  <option value="12" <?php if (JB_GMT_DIF=='12.00') { echo " selected "; } ?> >+12:00</option>
		  <option value="13" <?php if (JB_GMT_DIF=='13.00') { echo " selected "; } ?> >+13:00</option>

		  </select> from GMT
		  <br></td>
		</tr>
		<tr>
		<td class="config_form_heading"  colspan="2">SCW Settings (Simple Calendar Widget - pop-up calendar field type). See include/lib/scw/scw_js.php for more setting options, including CSS definitions.</td>
		</tr>
		<?php

		if (!defined('JB_SCW_DATE_FORMAT')) {
			define ('JB_SCW_DATE_FORMAT', 'YYYY-MM-DD');
			
		}

		if (!defined('JB_SCW_INPUT_SEQ')) {
			define ('JB_SCW_INPUT_SEQ', 'YMD');
		}
		
		
		?>
		<tr>
		  <td width="20%" class="config_form_label" >SCW Date Display Format</td>
		  <td class="config_form_field">
		   <input type="text" name="scw_date_format" size="49" value="<?php echo jb_escape_html(JB_SCW_DATE_FORMAT); ?>"> Eg. YYYY-MM-DD for the international standard (ISO 8601). Use double letters for zero filling, Eg. for September, MM will display as 09 and M will display as 9. 
		  </td>
		 </tr>
		
		<tr>
		  <td width="20%" class="config_form_label">SCW Date Input Sequence</td>
		  <td class="config_form_field">
		   <input type="text" name="scw_input_seq" size="49" value="<?php echo jb_escape_html(JB_SCW_INPUT_SEQ); ?>"> Eg. YMD for the international date standard (ISO 8601). The sequence should always contain one D, one M and one Y only, in any order.
		  </td>
		 </tr>
<?php
		if (!defined('JB_NAME_FORMAT')) {
			define ('JB_NAME_FORMAT', 'F L');	
		}
		?>
		 <tr>
		  <td width="20%" class="config_form_label">Name Display Format</td>
		  <td class="config_form_field">
		   <input type="text" name="jb_name_format" size="4" value="<?php echo jb_escape_html(JB_NAME_FORMAT); ?>"> Enter the name format for how the system should display names. F = First Name, L = Last Name. Eg. L, F will display the last name, followed by a comma, followed by the first name. ie. Smith, John.
		  </td>
		 </tr>

		</taBLE>
	 <p>&nbsp;</p>
	<a name="cats"></a>
	  <table class="config_form" cellpadding="5" cellspacing="2" >
		<tr>
		  <td colspan="2" class="config_form_heading">
		 Categories</td>
		</tr>
		<tr>
		  <td width="20%" class="config_form_label">Max Sub-categories to show:</td>
		  <td class="config_form_field">
		  <input type="text" name="show_subcats" size="3" value="<?php echo jb_escape_html(JB_SHOW_SUBCATS); ?>"> (Enter a number, eg. 5 - can be a zero)</td>
		</tr>
		<tr>
		  <td class="config_form_label">How many columns on front page</td>
		  <td class="config_form_field">
		  <input type="text" name="cat_cols_fp" size="3" value="<?php echo jb_escape_html(JB_CAT_COLS_FP); ?>"> (Enter a number, eg. 1)</td>
		</tr>
		<tr>
		  <td class="config_form_label">How many columns on category page</td>
		  <td class="config_form_field">
		  <input type="text" name="cat_cols" size="3" value="<?php echo jb_escape_html(JB_CAT_COLS); ?>"> (Enter a number, eg. 3)</td>
		</tr>
		<tr>
		  <td class="config_form_label" width="20%"> 
		   Format sub-categories into tables (on the front page)?</td>
		  <td class="config_form_field">
		   <input type="radio" name="format_sub_cats" size="49" value="YES" <?php if (JB_FORMAT_SUB_CATS=='YES') { echo " checked "; } ?> >Yes (Format sub-categories nicely into tables, using <input type="text" name="sub_category_cols" value="<?php echo jb_escape_html(JB_SUB_CATEGORY_COLS); ?>" size="2"> columns per row. <br><input type="radio" name="format_sub_cats" value="NO" <?php if (JB_FORMAT_SUB_CATS=='NO') { echo " checked "; } ?> >No (Keep them as compact as possible!)</td>
		</tr>

		<tr>
		  <td class="config_form_label">Cut-off (trim) category names?</td>
		  <td class="config_form_field">
		  <table>
		  <tr><td class="config_form_field">
		  <input type="radio" name="cat_name_cutoff" value="YES"  <?php if (JB_CAT_NAME_CUTOFF=='YES') { echo " checked "; } ?> >Yes
		  </td><td class="config_form_field">
		  [Cut off the name to a maximum of <input type="text" name="cat_name_cutoff_chars" size="3" value="<?php echo jb_escape_html(JB_CAT_NAME_CUTOFF_CHARS); ?>" > characters]</td>
		  </tr><tr>
		  <td>
		  <input type="radio" name="cat_name_cutoff" value="NO"  <?php if (JB_CAT_NAME_CUTOFF=='NO') { echo " checked "; } ?> >No
		  </td><td></td></tr></table>
		  </td>
		</tr>
		<tr>
		  <td width="20%" class="config_form_label">Indent sub-categories, show on the selection list?</td>
		  <td class="config_form_field">
		  <input type="radio" name="indent_category_list" value="YES"  <?php if (JB_INDENT_CATEGORY_LIST=='YES') { echo " checked "; } ?> >Yes<br>
		  <input type="radio" name="indent_category_list" value="NO"  <?php if (JB_INDENT_CATEGORY_LIST=='NO') { echo " checked "; } ?> >No<br>
		  
		  </td>
		  </tr>
		  <tr>
		  <td width="20%" class="config_form_label">Show category counters next to category names?</td>
		  <td class="config_form_field">
		  <input type="radio" name="cat_show_obj_count" value="YES"  <?php if (JB_CAT_SHOW_OBJ_COUNT=='YES') { echo " checked "; } ?> >Yes<br>
		  <input type="radio" name="cat_show_obj_count" value="NO"  <?php if (JB_CAT_SHOW_OBJ_COUNT=='NO') { echo " checked "; } ?> >No<br>
		  
		  </td>
		  </tr>

		  <tr>
		  <td width="20%" class="config_form_label">Show RSS subscription link?</td>
		  <td class="config_form_field">
		  <input type="radio" name="jb_cat_rss_switch" value="YES"  <?php if (JB_CAT_RSS_SWITCH=='YES') { echo " checked "; } ?> >Yes<br>
		  <input type="radio" name="jb_cat_rss_switch" value="NO"  <?php if (JB_CAT_RSS_SWITCH=='NO') { echo " checked "; } ?> >No<br>
		  
		  </td>
		  </tr>


		  <tr>
		  <td width="20%" class="config_form_label">Display category style - when viewing category fields on the dynamic form</td>
		  <td class="config_form_field">
		  <input type="radio" name="jb_cat_path_only_leaf" value="YES"  <?php if (JB_CAT_PATH_ONLY_LEAF=='YES') { echo " checked "; } ?> > - Only the category name, eg 'Sydney'<br>
		  <input type="radio" name="jb_cat_path_only_leaf" value="NO"  <?php if (JB_CAT_PATH_ONLY_LEAF=='NO') { echo " checked "; } ?> > - The category name and all the parent categories eg. 'Location -> Australia -> Syndey' (This is called 'Breadcrumb' navigation)<br>
		  
		  </td>
		  </tr>

		
	   </table>
	 <p>&nbsp;</p>

	<a name="mod_rewrite"></a>
	 <table class="config_form" cellpadding="5" cellspacing="2" >
	 <tr>
		<td class="config_form_heading" colspan="2">Mod_Rewrite (Advanced, see 'Extras' section in the menu for more info)<a name="mod_rewrite"></a></td>
		</tr>

		
		 <tr>
		  <td width="20%" class="config_form_label">Category URL Mod_rewrite Switch</td>
		  <td class="config_form_field">
		  <input type="radio" name="cat_mod_rewrite" value="YES"  <?php if (JB_CAT_MOD_REWRITE=='YES') { echo " checked "; } ?> >On - Please <A href='mod_rewrite.php'>see here</a> before enabling this option!<br>
		  <input type="radio" name="cat_mod_rewrite" value="NO"  <?php if (JB_CAT_MOD_REWRITE=='NO') { echo " checked "; } ?> >Off<br>
		  
		  </td>
		  </tr>
		  <tr>
		  <td class="config_form_label">Category Directory</td>
		  <td class="config_form_field">
		 <?php
		if (!defined('JB_MOD_REWRITE_DIR')) {
			define ('JB_MOD_REWRITE_DIR', 'category/');
		}
		 ?>
		  
		 <input type="text" name="mod_rewrite_dir" size="30" value="<?php echo jb_escape_html(JB_MOD_REWRITE_DIR); ?>" >  - Default is: <b>category/</b> (Always no slashes at the front, and a forward slash at the end)
		  

		  </td>
		 <tr>
		  <td width="20%" class="config_form_label">Job Post URL Mod_rewrite Switch</td>
		  <td class="config_form_field">
		  <input type="radio" name="job_mod_rewrite" value="YES"  <?php if (JB_JOB_MOD_REWRITE=='YES') { echo " checked "; } ?> >On - Please <A href='mod_rewrite.php'>see here</a> before enabling this option!<br>
		  <input type="radio" name="job_mod_rewrite" value="NO"  <?php if (JB_JOB_MOD_REWRITE=='NO') { echo " checked "; } ?> >Off<br>
	 
		  </td>
		 
		</tr>
		 <tr>
		  <td class="config_form_label">Job Posting Directory</td>
		  <td class="config_form_field">
		 <?php
		if (!defined('JB_MOD_REWRITE_JOB_DIR')) {
			define ('JB_MOD_REWRITE_JOB_DIR', 'job/');
		}
		 ?>
		  
		 <input type="text" name="mod_rewrite_job_dir" size="60" value="<?php echo jb_escape_html(JB_MOD_REWRITE_JOB_DIR); ?>" >  - Default is: <b>job/</b> You can use any template tag. Eg. job/%DATE%/ - where the %DATE% will be changed to the date when the job was posted. Another example would be job/%DATE%/%CLASS%/ (Always no slashes at the front, and a forward slash at the end. %CLASS% is the default classification field)
		  

		  </td>
		</tr>

		 <tr>
		  <td width="20%" class="config_form_label">Employer's Profile URL Mod_rewrite Switch</td>
		  <td class="config_form_field">
		  <input type="radio" name="pro_mod_rewrite" value="YES"  <?php if (JB_PRO_MOD_REWRITE=='YES') { echo " checked "; } ?> >On - Please <A href='mod_rewrite.php'>see here</a> before enabling this option!<br>
		  <input type="radio" name="pro_mod_rewrite" value="NO"  <?php if (JB_PRO_MOD_REWRITE=='NO') { echo " checked "; } ?> >Off<br>
	 
		  </td>
		 </tr>

		 <tr>
		  <td  class="config_form_label">Employer's Profile Directory</td>
		  <td  class="config_form_field">
		 <?php
		if (!defined('JB_MOD_REWRITE_PRO_DIR')) {
			define ('JB_MOD_REWRITE_PRO_DIR', 'profile/');
		}
		 ?>
		  
		 <input type="text" name="mod_rewrite_pro_dir" size="30" value="<?php echo jb_escape_html(JB_MOD_REWRITE_PRO_DIR); ?>" >  - Default is: <b>profile/</b> (Always no slashes at the front, and a forward slash at the end)
		  

		  </td>
		</tr>
		 <tr>
		  <td width="20%" class="config_form_label">Job list, Page Numbers URLs Mod_rewrite Switch</td>
		  <td class="config_form_field">
		  <input type="radio" name="job_pages_mod_rewrite" value="YES"  <?php if (JB_JOB_PAGES_MOD_REWRITE=='YES') { echo " checked "; } ?> >On - Please <A href='mod_rewrite.php'>see here</a> before enabling this option!<br>
		  <input type="radio" name="job_pages_mod_rewrite" value="NO"  <?php if (JB_JOB_PAGES_MOD_REWRITE=='NO') { echo " checked "; } ?> >Off<br>
	 
		  </td>
		 </tr>
			 <tr>
		  <td class="config_form_label">Page Number URL prefix</td>
		  <td class="config_form_field">
		 <?php
		if (!defined('JB_MOD_REWRITE_JOB_PAGES_PREFIX')) {
			define ('JB_MOD_REWRITE_JOB_PAGES_PREFIX', 'page');
		}
		 ?>
		  
		 <input type="text" name="mod_rewrite_job_pages_prefix" size="30" value="<?php echo jb_escape_html(JB_MOD_REWRITE_JOB_PAGES_PREFIX); ?>" >  - Default is: <b>page</b> (This will be show as<br> http://www.example.com/page1<br> http://www.example.com/page2<br> http://www.example.com/page3<br> etc)
		  

		  </td>
		</tr>
		<tr>
		  <td width="20%" class="config_form_label">Remove accents from URLs</td>
		  <td class="config_form_field">
		  <input type="radio" name="mod_rewrite_remove_accents" value="YES"  <?php if (JB_MOD_REWRITE_REMOVE_ACCENTS=='YES') { echo " checked "; } ?> >Yes - This will remove accents in URLs such as à, á, â to a, a, a. Please note that for categories you may need to remove accents manually. Please go to Admin->mod_rewrite to configure your category URLs<br>
		  <input type="radio" name="mod_rewrite_remove_accents" value="NO"  <?php if (JB_MOD_REWRITE_REMOVE_ACCENTS=='NO') { echo " checked "; } ?> >No<br>
		  
		  </td>
		  </tr>
	   </table>

	<p>&nbsp;</p>
<a name="clean"></a>
	  <table class="config_form" cellpadding="5" cellspacing="2" >
		<tr>
		  <td colspan="2" class="config_form_heading">
		  Data Cleaning
		  </td>
		</tr>
	   <tr>
		  <td class="config_form_label" width="20%"> 
		   Strip unwanted HTML tags from Text-Editor and HTML-Editor fields?</td>
		  <td class="config_form_field">
		   <input type="radio" name="strip_html" size="49" value="YES" <?php if (JB_STRIP_HTML=='YES') { echo " checked "; } ?> >Yes (This will strip dangerous attributes such as javascript, and leave only the good data. Note, since v3.6, this setting is always set to Yes.)<br><input type="radio" disabled name="strip_html" value="NO" <?php if (JB_STRIP_HTML!='YES') { echo " checked "; } ?> >No</td>
		</tr>
		<tr>
		  <td class="config_form_label" width="20%"> 
		   Break long words?</td>
		  <td class="config_form_field">
		  <?php

		  if (!defined('JB_BREAK_LONG_WORDS')) {
			  define ('JB_BREAK_LONG_WORDS', 'YES');

		  }

		  if (!defined('JB_LNG_MAX')) {
			  define ('JB_LNG_MAX', '80');

		  }
		  
		  ?>
		   <input type="radio" name="break_long_words" size="49" value="YES" <?php if (JB_BREAK_LONG_WORDS=='YES') { echo " checked "; } ?> >Yes - and maximum word length is <input type='text' name='lng_max' value='<?php echo jb_escape_html(JB_LNG_MAX); ?>' size="2" > characters. (This will add a space between words that are longer than the limit - to make sure that users do not enter input which destroys the layout of the site. eg. consecutive ===='s).<br><input type="radio" name="break_long_words" value="NO" <?php if (JB_BREAK_LONG_WORDS!='YES') { echo " checked "; } ?> >No</td>
		</tr>
	  <tr>
		  <td class="config_form_label" width="20%"> 
		   Strip characters that aren't valid in ISO-8859-1?</td>
		  <td class="config_form_field">
		   <input type="radio" name="strip_latin1" size="49" value="YES" <?php if (JB_STRIP_LATIN1=='YES') { echo " checked "; } ?> >Yes (Enabled. Choose this option if you expect your data to output in the standard HTML character encoding)<br><input type="radio" name="strip_latin1" value="NO" <?php if (JB_STRIP_LATIN1!='YES') { echo " checked "; } ?> >No (Disabled)</td>
		</tr>
		<tr>
		  <td class="config_form_label" width="20%"> 
		   Trim white-space from strings?</td>
		  <td class="config_form_field">
		   <input type="radio" name="clean_strings" size="49" value="YES" <?php if (JB_CLEAN_STRINGS=='YES') { echo " checked "; } ?> >Yes (Enabled. This will trim any un-needed white-space from inputed data)<br><input type="radio" name="clean_strings" value="NO" <?php if (JB_CLEAN_STRINGS!='YES') { echo " checked "; } ?> >No (Disabled)</td>
		</tr>
	   <tr>
		  <td class="config_form_label" width="20%"> 
		   Enable Profanity word Filter?</td>
		  <td class="config_form_field">
		  
		  <input type="radio" name="bad_word_filter" size="49" value="YES" <?php if (JB_BAD_WORD_FILTER=='YES') { echo " checked "; } ?> >Yes (Enabled. You can setup your profanity word list below)<br><input type="radio" name="bad_word_filter" value="NO" <?php if (JB_BAD_WORD_FILTER!='YES') { echo " checked "; } ?> >No (Disabled) <br>
		  Enter the bad words that you would like to filter, seperated by commas:<br>
		  <textarea name='bad_words' rows="4" cols="50"><?php echo JB_BAD_WORDS; ?></textarea>
		  </td>
		</tr>
		
		<tr>
		  <td class="config_form_label" width="20%"> 
		   File Uploads - Allowed extensions:</td>
		  <td class="config_form_field">
		  <br>
		  Enter the file types that you would like to allow, seperated by commas:<br>
		  <textarea name='allowed_ext' rows="4" cols="50"><?php if (JB_ALLOWED_EXT=='JB_ALLOWED_EXT') { $JB_ALLOWED_EXT= 'doc, docx, pdf, wps, hwp, txt, rtf, wri, zip, rar, jpeg, jpg, gif, bmp';} else { $JB_ALLOWED_EXT=JB_ALLOWED_EXT;}   echo $JB_ALLOWED_EXT; ?></textarea><br>
		  eg: doc, docx, pdf, wps, hwp, txt, rtf, wri, zip, rar, jpeg, jpg, gif, bmp
		  
		  </td>
		</tr>
		<tr>
		  <td class="config_form_label" width="20%">
		   Image Uploads - Allowed extensions:</td>
		  <td class="config_form_field">
		  <br>
		  Enter the image types that you would like to allow, seperated by commas:<br>
		  <textarea name='allowed_img' rows="4" cols="50"><?php if (JB_ALLOWED_IMG=='JB_ALLOWED_IMG') { $JB_ALLOWED_IMG= 'jpg, jpeg, gif, png, bmp';} else { $JB_ALLOWED_IMG=JB_ALLOWED_IMG;}   echo $JB_ALLOWED_IMG; ?></textarea><br>
		  eg: jpg, jpeg, gif, png, bmp
		  
		  </td>
		</tr>
		<tr>
		  <td class="config_form_label" width="20%"> 
		   File Uploads - Maximum Size:</td>
		  <td class="config_form_field">
			<input type="text" value="<?php if (JB_MAX_UPLOAD_BYTES=='JB_MAX_UPLOAD_BYTES') { $JB_MAX_UPLOAD_BYTES= '1048576';} else { $JB_MAX_UPLOAD_BYTES=JB_MAX_UPLOAD_BYTES;} echo jb_escape_html($JB_MAX_UPLOAD_BYTES); ?>" name="max_upload_bytes"> (Enter the max size in bytes. 1048576 bytes = 1MB)
		  
		  </td>
	   </table>
		<p>&nbsp;</p>
		<a name="ac"></a>
	  <table class="config_form" cellpadding="5" cellspacing="2" >
		<tr>
		  <td colspan="2" valign="top" class="config_form_heading">
		  Accounts &amp; Permissions</td>
		</tr>
		<tr>
		  <td width="20%" valign="top" class="config_form_label">
		  Candidate's accounts need to be Activated?</td>
		  <td  valign="top" class="config_form_field">
		  
		  
		  <input disabled type="radio" name="ca_needs_activation" value="SELF" <?php if (JB_CA_NEEDS_ACTIVATION=='SELF') { echo " checked "; } ?> ><!--Yes - self (NYI)--><br>
		  <input type="radio" name="ca_needs_activation" value="MANUAL" <?php if (JB_CA_NEEDS_ACTIVATION=='MANUAL') { echo " checked "; } ?> >Yes - manually<br>
		  <input type="radio" name="ca_needs_activation" value="AUTO" <?php if (JB_CA_NEEDS_ACTIVATION=='AUTO') { echo " checked "; } ?> >No</td>
		</tr>
		<tr>
		  <td  valign="top" class="config_form_label">
		  Employer accounts need to be activated?</td>
		  <td  valign="top" class="config_form_field">
		  
		  
		  <input type="radio" name="em_needs_activation" value="MANUAL" <?php if (JB_EM_NEEDS_ACTIVATION=='MANUAL') { echo " checked "; } ?> >Yes - Employers need to be manually activated before they can log in<br>
		  <input type="radio"  name="em_needs_activation" value="NO_RESUME" <?php if (JB_EM_NEEDS_ACTIVATION=='NO_RESUME') { echo " checked "; } ?> >Yes - Employers need to be manually activated before they can view resumes for free (If subscriptions are disabled)<br>
		  <input type="radio"  name="em_needs_activation" value="FIRST_POST" <?php if (JB_EM_NEEDS_ACTIVATION=='FIRST_POST') { echo " checked "; } ?> >No - Activate automatically, but need to post an ad before viewing resumes (If subscriptions are disabled)<br>
		  <input type="radio" name="em_needs_activation" value="AUTO" <?php if (JB_EM_NEEDS_ACTIVATION=='AUTO') { echo " checked "; } ?>>No - all accounts are active by default<br>
		  
		  
		  
		  </td>
		</tr>
		
		<tr>
		  <td  valign="top" class="config_form_label">
		  Impose a limit on how many free Standard posts an employer can have at one time?</td>
		  <td  valign="top" class="config_form_field">
		  
		  
		  <input type="radio" name="free_post_limit" value="YES"  <?php if (JB_FREE_POST_LIMIT=='YES') { echo " checked "; } ?> >Yes, with a maximum of
		  <input type="text" name="free_post_limit_max" size="3" value="<?php echo jb_escape_html(JB_FREE_POST_LIMIT_MAX); ?>"> posts. (Works only if billing is disabled for standard posts)<br>
		  <input type="radio" name="free_post_limit" value="NO"  <?php if (JB_FREE_POST_LIMIT=='NO') { echo " checked "; } ?> >No, unlimited</td>
		</tr>
		<tr>
		  <td class="config_form_label">Employers begin with how many free credit points for Premium posts?</td>
		  <td class="config_form_field">
		  <input type="text" name="begin_premium_credits" size="3" value="<?php echo jb_escape_html(JB_BEGIN_PREMIUM_CREDITS); ?>"> (Enter a number, eg. 5)</td>
		</tr>
		<tr>
		  <td class="config_form_label">Employers begin with how many free credit points for Standard posts?</td>
		  <td class="config_form_field">
		  <input type="text" name="begin_standard_credits" size="3" value="<?php echo jb_escape_html(JB_BEGIN_STANDARD_CREDITS); ?>"> (Enter a number, eg. 5)</td>
		</tr>
		 <tr>
		  <td  valign="top" class="config_form_label">
		  Allow the Admin to login to employer's and user's accounts using the admin password?</td>
		  <td  valign="top" class="config_form_field">
		  
		  
			  <input type="radio" name="allow_admin_login" value="YES"  <?php if (JB_ALLOW_ADMIN_LOGIN=='YES') { echo " checked "; } ?> >Yes<br>
		  <input type="radio" name="allow_admin_login" value="NO"  <?php if (JB_ALLOW_ADMIN_LOGIN=='NO') { echo " checked "; } ?> >No</td>
		</tr>
		
	  </table>
	  <p>&nbsp;</p>
	  <a name="menu"></a>
	  <table class="config_form" cellpadding="5" cellspacing="2" >
		<tr>
		  <td colspan="2" class="config_form_heading">
		  Menu Options</td>
		</tr>
	   <?php
	   
	   if (!defined('JB_CANDIDATE_MENU_TYPE')) {
		   define ('JB_CANDIDATE_MENU_TYPE', 'JS');
	   }

	   if (!defined('JB_EMPLOYER_MENU_TYPE')) {
		   define ('JB_EMPLOYER_MENU_TYPE', 'JS');
	   }
	 
	   ?>
		<tr>   
		  <td width="20%" class="config_form_label">Candidate's Menu</td>
		  <td class="config_form_field">
		  <input type="radio" name="candidate_menu_type" value="JS"  <?php if (JB_CANDIDATE_MENU_TYPE=='JS') { echo " checked "; } ?> >Javascript Menu. (Pull-down menus)<br>
		  <br>
		  <input type="radio" name="candidate_menu_type" value="TXT"  <?php if (JB_CANDIDATE_MENU_TYPE=='TXT') { echo " checked "; } ?> >Text Based Menu. (Tabs)
		  
		  </td>
		</tr>

		<tr>   
		  <td width="20%" class="config_form_label">Employer's Menu</td>
		  <td class="config_form_field">
		  <input type="radio" name="employer_menu_type" value="JS"  <?php if (JB_EMPLOYER_MENU_TYPE=='JS') { echo " checked "; } ?> >Javascript Menu. (Pull-down menus)<br>
		  <br>
		  <input type="radio" name="employer_menu_type" value="TXT"  <?php if (JB_EMPLOYER_MENU_TYPE=='TXT') { echo " checked "; } ?> >Text Based Menu. (Tabs)
		  
		  </td>
		</tr>
		
		 </table>

		 <p>&nbsp;</p>
	  <a name="search"></a>
	  <table class="config_form" cellpadding="5" cellspacing="2" >
		<tr>
		  <td colspan="2" class="config_form_heading">
		  Search Form Options</td>
		</tr>
	   <?php
	   
	   if (!defined('JB_SEARCH_FORM_LAYOUT')) {
		   define ('JB_SEARCH_FORM_LAYOUT', 'T');
	   }

	   ?>
		<tr>   
		  <td width="20%" class="config_form_label">Search Form Layout</td>
		  <td class="config_form_field">
		  <input type="radio" name="search_form_layout" value="T"  <?php if (JB_SEARCH_FORM_LAYOUT=='T') { echo " checked "; } ?> >Classic. (Table layout)<br>
		  <br>
		  <input type="radio" name="search_form_layout" value="TL"  <?php if (JB_SEARCH_FORM_LAYOUT=='TL') { echo " checked "; } ?> >Compact. (Tableless layout. For customizations, please see comments inside include/themes/default/JBSearchFormTLMarkup.php)
		  
		  </td>
		</tr>

		
		
		</table>

		 <p>&nbsp;</p>
		 <a name="anon"></a>
		 <table class="config_form" cellpadding="5" cellspacing="2" >
		<tr>
		  <td colspan="2" class="config_form_heading">
		  Anonymous fields and Request System</td>
		</tr>
	   
		   <tr>
		   
		  <td width="20%" class="config_form_label">Enable Resume's 'anonymous fields' and Request System</td>
		  <td class="config_form_field">
		 
		  <input type="radio" name="resume_request_switch" value="YES"  <?php if (JB_RESUME_REQUEST_SWITCH=='YES') { echo " checked "; } ?> >Yes, mask anonymous fields. Employers will be able to send a request to the candidate, which the Candidate will Grant or Deny. If granted, Employer will be able to see the anonymous fields. IMPORTANT: You will need to specify which fields are "Anonymous", go to Admin->Resume Form, click the 'Edit Fields' tab, and then click on the fields which you want to make 'Anonymous'<br>
		  &nbsp;&nbsp;&nbsp; <input type="checkbox" value="YES" <?php if (JB_NEED_SUBSCR_FOR_REQUEST =='YES') { echo " checked "; } ?>  name="need_subscr_for_request">Limit request sending - Subscribed Employers only.<br>
		  <input type="radio" name="resume_request_switch" value="NO"  <?php if (JB_RESUME_REQUEST_SWITCH=='NO') { echo " checked "; } ?> >No, do not mask anonymous fields. Candidates cannot set their resume to 'anonymous'
		  
		  </td>
		</tr>
		
		 </table>
		 <p>&nbsp;</p>
		 <a name="mem"></a>
		 <table class="config_form" cellpadding="5" cellspacing="2">
		<tr>
		  <td colspan="2" class="config_form_heading">
		  Membership fields</td>
		</tr>
	   
		   <tr>
		   
		  <td width="20%" class="config_form_label">Enable 'Members Only' fields</td>
		  <td class="config_form_field">
		 
		  <input type="radio" name="member_field_switch" value="YES"  <?php if (JB_MEMBER_FIELD_SWITCH=='YES') { echo " checked "; } ?> >Yes. Only signed-in users will be able to view fields that are marked "Member's Only" on the job posting form. If membership billing is enabled, they will need to have an active membership to view these fields. (Note: Search Engines cannot index these fields if this option is enabled.) IMPORTANT: You will need to specify which fields are "Members Only", go to Admin->Posting Form, click the 'Edit Fields' tab, and then click on the fields which you want to make 'Members Only'<br>
		  &nbsp;&nbsp;&nbsp; <br>
		  <input type="radio" name="member_field_switch" value="NO"  <?php if (JB_MEMBER_FIELD_SWITCH=='NO') { echo " checked "; } ?> >No.
		  
		  </td>
		</tr>

		<tr><?php
			

			?>
		  <td width="20%" class="config_form_label"> Ignore 'Members Only' fields for Premium Posts?</td>
		  <td class="config_form_field">
		  <input type="radio" name="jb_member_field_ignore_premium" value="YES"  <?php if (JB_MEMBER_FIELD_IGNORE_PREMIUM=='YES') { echo " checked "; } ?> > Yes.<br>
		  <input type="radio" name="jb_member_field_ignore_premium" value="NO"  <?php if (JB_MEMBER_FIELD_IGNORE_PREMIUM=='NO') { echo " checked "; } ?> > No. If 'Members Only' fields are enabled, then they will also be enabled for premium posts too (default)<br>
		 </td>
		 </tr>
		
		 </table>
		 <p>&nbsp;</p>
		 <a name="blocked"></a>
	  <table class="config_form" cellpadding="5" cellspacing="2" >
		<tr>
		  <td colspan="2" class="config_form_heading">
		 Blocked Fields</td>
		</tr>
	   
		   <tr>
		  <td width="20%" class="config_form_label">Enable Blocked Fields System</td>
		  <td class="config_form_field">
		  
		  <input type="radio" name="field_block_switch" value="YES"  <?php if (JB_FIELD_BLOCK_SWITCH=='YES') { echo " checked "; } ?> >Yes, fields that are blockable will be marked as 'blocked' to non-subscribers and not blocked to Subscribers (depending on subscription status) on the resume form. Important: If this option is set to Yes, please go to <a href="resumeform.php">Admin->Resume Form</a>, click 'Edit Fields' tab, and click on the fields that you would like blocked for non-subsribers. If you do not make any fields subject to blocking then everyone will be able to see the resumes in full. This feature works only if resume subscription billing is enabled.<br>
		  <br>
		  <input type="radio" name="field_block_switch" value="NO"  <?php if (JB_FIELD_BLOCK_SWITCH=='NO') { echo " checked "; } ?> >No, Turn off this feature.
		  
		  <div style="margin-left: 25px;">
		  <table>
		  <tr>
		  <td class="config_form_label" width="20%"> 
		   Allow Employers to directly reply to resumes via the site?</td>
		  <td class="config_form_field">
		   <input type="radio" name="resume_reply_enabled" size="49" value="YES" <?php if (JB_RESUME_REPLY_ENABLED=='YES') { echo " checked "; } ?> >Yes (Allow)<br><input type="radio" name="resume_reply_enabled" value="NO" <?php if (JB_RESUME_REPLY_ENABLED!='YES') { echo " checked "; } ?> >No (Disabled)</td>
		</tr>
		

		  </table>
		  </div>

		  </td>
		</tr>
		<tr><?php
			if (!defined('JB_FIELD_BLOCK_APP_SWITCH')) {
				define('JB_FIELD_BLOCK_APP_SWITCH', 'NO');
			}

			?>
		  <td width="20%" class="config_form_label">Block fields on the employer's Application list?</td>
		  <td class="config_form_field">
		  <input type="radio" name="field_block_app_switch" value="YES"  <?php if (JB_FIELD_BLOCK_APP_SWITCH=='YES') { echo " checked "; } ?> > Yes - only employers subscribed to the resume database will be able to see blocked fields on the application list.<br>
		  <input type="radio" name="field_block_app_switch" value="NO"  <?php if (JB_FIELD_BLOCK_APP_SWITCH=='NO') { echo " checked "; } ?> > No - Do not block the fields on the application list - so that non-subscribed employers can see the applications that they received. (default)<br>
		 </td>
		 </tr>

		 </table>
		  <p>&nbsp;</p>
		  <a name="billing"></a>
	  <table class="config_form" cellpadding="5" cellspacing="2" >
		<tr>
		  <td colspan="2" class="config_form_heading">
		  Billing System - Enable / Disable
		  </td>
		</tr>
		<tr>
		  <td width="20%" class="config_form_label">Resume Database - Enable 
		   subscription billing?</td>
		  <td class="config_form_field">
		  <input type="radio" name="subscription_fee_enabled" value="YES"  <?php if (JB_SUBSCRIPTION_FEE_ENABLED=='YES') { echo " checked "; } ?> >Yes<br>
		  <input type="radio" name="subscription_fee_enabled" value="NO"  <?php if (JB_SUBSCRIPTION_FEE_ENABLED=='NO') { echo " checked "; } ?> >No (Allow employers to browse resumes for free)<br>Note: You can configure your prices from the 'Price Admin' menu on the left.</td>
		</tr>
		<tr>
		  <td class="config_form_label">Standard Posts - Enable 
		  Billing?</td>
		  <td class="config_form_field">
		  <input type="radio" name="posting_fee_enabled" value="YES"  <?php if (JB_POSTING_FEE_ENABLED=='YES') { echo " checked "; } ?> >Yes<br>
		  <input type="radio" name="posting_fee_enabled" value="NO"  <?php if (JB_POSTING_FEE_ENABLED=='NO') { echo " checked "; } ?> >No (Allow all employers to post for free)<br>Note: You can configure your prices from the 'Price Admin' menu on the left.<br>
		  <input onclick="if (this.checked) document.form1.premium_posting_fee_enabled[1].checked=true;" type="checkbox" name="premium_auto_upgrade" value="YES"  <?php if (JB_PREMIUM_AUTO_UPGRADE=='YES') { echo " checked "; } ?> >Automatically upgrade all standard posts to premium. Note: Premium Posting gets disabled when this option is checked)
		  </td>
		</tr>
		<tr>
		  <td class="config_form_label">Premium Posts - Enable 
		  Billing?</td>
		  <td class="config_form_field">
		  <input type="radio" name="premium_posting_fee_enabled" value="YES"  <?php if (JB_PREMIUM_POSTING_FEE_ENABLED=='YES') { echo " checked "; } ?> >Yes<br>
		  <input type="radio" name="premium_posting_fee_enabled" value="NO"  <?php if (JB_PREMIUM_POSTING_FEE_ENABLED=='NO') { echo " checked "; } ?> >No (Disable all posting premium posting features)<br>Note: You can configure your prices from the 'Price Admin' menu on the left.</td>
		</tr>
		<tr>
		  <td class="config_form_label">Enable 
		  Billing for Candidate's Membership?</td>
		  <td  class="config_form_field">
		  <input type="radio" name="candidate_membership_enabled" value="YES"  <?php if (JB_CANDIDATE_MEMBERSHIP_ENABLED=='YES') { echo " checked "; } ?> >Yes<br>
		  <input type="radio" name="candidate_membership_enabled" value="NO"  <?php if (JB_CANDIDATE_MEMBERSHIP_ENABLED=='NO') { echo " checked "; } ?> >No (Do not charge candidates a signup fee / membership)<br>Note: You can configure your prices from the 'Price Admin' menu on the left.</td>
		</tr>
		<tr>
		  <td class="config_form_label">Enable 
		  Billing for Employer's Membership?</td>
		  <td class="config_form_field">
		  <input type="radio" name="employer_membership_enabled" value="YES"  <?php if (JB_EMPLOYER_MEMBERSHIP_ENABLED=='YES') { echo " checked "; } ?> >Yes<br>
		  <input type="radio" name="employer_membership_enabled" value="NO"  <?php if (JB_EMPLOYER_MEMBERSHIP_ENABLED=='NO') { echo " checked "; } ?> >No (Do not charge employers a signup fee / membership fee)<br>Note: You can configure your prices from the 'Price Admin' menu on the left.</td>
		</tr>
		<tr>
		  <td class="config_form_label">Invoice I.D. to start from:</td>
		  <td class="config_form_field">
		  <input type="text" name="invoice_id_start" size="5" value="<?php 
		  if (!defined('')){
			define ('JB_INVOICE_ID_START', 100);
		 }
		  echo JB_INVOICE_ID_START; ?>" > (Serial identification number assigned to each invoice. Enter a number to start with, eg 100)</td>
		</tr>
		<tr>
		  <td class="config_form_label">Default Payment Method</td>
		  <td class="config_form_field">
		  <select name='jb_default_pay_meth'>
		  <?php
			$dir = dirname(__FILE__);
			$dir = explode (DIRECTORY_SEPARATOR, $dir);
			$blank = array_pop($dir);
			$dir = implode('/', $dir);

			include $dir.'/payment/payment_manager.php';

			foreach ($_PAYMENT_OBJECTS as $key => $val) {
				if (JB_DEFAULT_PAY_METH==$key) {
					$sel = ' selected ';
				} else {
					$sel = '';
				}
				echo '<option '.$sel.' value="'.$key.'">'.$_PAYMENT_OBJECTS[$key]->name.'</option>';

			}
		  
		  ?>
		  </select>
		 
		 </td>
		</tr>
	</table>


	 <p>&nbsp;</p>
	
	<p>&nbsp;</p>
	<a name="posts"></a>
	  <table class="config_form" cellpadding="5" cellspacing="2">
		<tr>
		  <td colspan="2" class="config_form_heading">
		  Posts</td>
		</tr>
		<tr>
		  <td width="20%" class="config_form_label">Posts 
		  need approval?</td>
		  <td  class="config_form_field">
		  <input type="radio" name="posts_need_approval" value="YES"  <?php if (JB_POSTS_NEED_APPROVAL=='YES') { echo " checked "; } ?> >Yes<br>
		  <input type="radio" name="posts_need_approval" value="NO"  <?php if (JB_POSTS_NEED_APPROVAL=='NO') { echo " checked "; } ?> >No<br>
		  <input type="radio" name="posts_need_approval" value="NOT_SUBSCRIBERS"  <?php if (JB_POSTS_NEED_APPROVAL=='NOT_SUBSCRIBERS') { echo " checked "; } ?> >No for subscribed employers / paid posts
		  </td>
		</tr>
		<tr>
		  <td class="config_form_label">Number 
		  of jobs displayed per page</td>
		  <td class="config_form_field">
		  <input type="text" name="posts_per_page" size="10" value="<?php echo jb_escape_html(JB_POSTS_PER_PAGE); ?>" ></td>
		</tr>

		<?php
		
		if (!defined('JB_POSTS_PER_RSS')) {
			define ('JB_POSTS_PER_RSS', JB_POSTS_PER_PAGE);
		}
		
		?>

		<tr>
		  <td class="config_form_label">Number 
		  of jobs included in the RSS feed</td>
		  <td class="config_form_field">
		  <input type="text" name="posts_per_rss" size="10" value="<?php echo jb_escape_html(JB_POSTS_PER_RSS); ?>" ></td>
		</tr>
	  
		<tr>
		  <td class="config_form_label">How 
		  many days until expired?</td>
		  <td class="config_form_field">
		  <input type="text" name="posts_display_days" size="10" value="<?php echo jb_escape_html(JB_POSTS_DISPLAY_DAYS); ?>" > days</td>
		</tr>

		<tr>
		<td class="config_form_heading" colspan="2">Job Post Summary - <span style="font-weight: normal;">'Job Title' column on the job list <br>Note: Please ensure to have a 'description summary' column in your Job List. You can edit the columns in Admin->Posting Form, 'Job List' tab. It should be there by default.</span>
</td>
		</tr>

		<tr>
		  <td class="config_form_label">Show Description Preview in Posts List headings?</td>
		  <td class="config_form_field">
		  <table>
		  <tr><td>
		  <input type="radio" name="posts_show_description" value="YES"  <?php if (JB_POSTS_SHOW_DESCRIPTION=='YES') { echo " checked "; } ?> >Yes
		  </td><td class="config_form_field">
		  [Cut off after <input type="text" name="posts_description_chars" size="3" value="<?php echo jb_escape_html(JB_POSTS_DESCRIPTION_CHARS); ?>" > characters]</td>
		  </tr><tr>
		  <td>
		  <input type="radio" name="posts_show_description" value="NO"  <?php if (JB_POSTS_SHOW_DESCRIPTION=='NO') { echo " checked "; } ?> >No
		  </td><td></td></tr></table>
		  </td>
		</tr>

		<tr>
		  <td class="config_form_label">Show 'Posted By' in Post List heading?</td>
		  <td class="config_form_field">
		  <input type="radio" name="posts_show_posted_by" value="YES"  <?php if (JB_POSTS_SHOW_POSTED_BY=='YES') { echo " checked "; } ?> >Yes (<input type="checkbox" value="YES" name="posts_show_posted_by_br" <?php if (JB_POSTS_SHOW_POSTED_BY_BR=="YES") { echo " checked "; } ?> > Insert on a seperate line.)<br>
		  <input type="radio" name="posts_show_posted_by" value="NO"  <?php if (JB_POSTS_SHOW_POSTED_BY=='NO') { echo " checked "; } ?> >No</td>
		</tr>
		<tr>

		  <td class="config_form_label">Show Category in Posts List headings?</td>
		  <td class="config_form_field">
		  <input type="radio" name="posts_show_job_type" value="YES"  <?php if (JB_POSTS_SHOW_JOB_TYPE=='YES') { echo " checked "; } ?> >Yes (<input type="checkbox" value="YES" name="posts_show_job_type_br" <?php if (JB_POSTS_SHOW_JOB_TYPE_BR=="YES") { echo " checked "; } ?> > Insert on a seperate line.)<br>
		  <input type="radio" name="posts_show_job_type" value="NO"  <?php if (JB_POSTS_SHOW_JOB_TYPE=='NO') { echo " checked "; } ?> >No</td>
		</tr>
		<tr>
		  <td class="config_form_label">Show the Day, and how many days elapsed (Group posts by day)?</td>
		  <td class="config_form_field">
		  <input type="radio" name="posts_show_days_elapsed" value="YES"  <?php if (JB_POSTS_SHOW_DAYS_ELAPSED=='YES') { echo " checked "; } ?> >Yes<br>
		  <input type="radio" name="posts_show_days_elapsed" value="NO"  <?php if (JB_POSTS_SHOW_DAYS_ELAPSED=='NO') { echo " checked "; } ?> >No</td>
		</tr>
		
		
		<tr>
		<td class="config_form_heading" colspan="2"><b>Premium Posts</b></td>
		</tr>
	<?php
		if (!defined('JB_P_POSTS_DISPLAY_DAYS')) {
			define ('JB_P_POSTS_DISPLAY_DAYS', JB_POSTS_DISPLAY_DAYS);

		}



	?>
		 <tr>
		  <td class="config_form_label">How 
		  many days until expired?</td>
		  <td class="config_form_field">
		  Expire premium posts after <input type="text" name="p_posts_display_days" size="5" value="<?php echo jb_escape_html(JB_P_POSTS_DISPLAY_DAYS); ?>" > days</td>
		</tr>

		  <tr>
		  <td class="config_form_label">Premium Posts per page</td>
		  <td class="config_form_field">
		 
		 
		  <input type="radio" name="premium_posts_limit" value="YES"  <?php if (JB_PREMIUM_POSTS_LIMIT=='YES') { echo " checked "; } ?> >Yes, show <input type="text" name="premium_posts_per_page" size="5" value="<?php echo JB_PREMIUM_POSTS_PER_PAGE; ?>" > Premium posts per page
		  <br>
		  <input type="radio" name="premium_posts_limit" value="NO"  <?php if (JB_PREMIUM_POSTS_LIMIT=='NO') { echo " checked "; } ?> >Unlimited

		  </td>
		</tr>
		 <tr>
		  <td class="config_form_label">Group Premium posts by day of week?</td>
		  <td class="config_form_field">
		 
		 
		  <input type="radio" name="p_posts_show_days_elapsed" value="YES"  <?php if (JB_P_POSTS_SHOW_DAYS_ELAPSED=='YES') { echo " checked "; } ?> >Yes
		  <br>
		  <input type="radio" name="p_posts_show_days_elapsed" value="NO"  <?php if (JB_P_POSTS_SHOW_DAYS_ELAPSED=='NO') { echo " checked "; } ?> >No

		  </td>
		</tr>
		 <tr>
		  <td class="config_form_label">Display number of views column for premium posts only</td>
		  <td class="config_form_field">
		 
		 
		  <input type="radio" name="show_premium_hits" value="YES"  <?php if (JB_SHOW_PREMIUM_HITS=='YES') { echo " checked "; } ?> >Yes - ...adds a 'views' column on top any list settings (Posting Form / Edit List)
		  <br>
		  <input type="radio" name="show_premium_hits" value="NO"  <?php if (JB_SHOW_PREMIUM_HITS=='NO') { echo " checked "; } ?> >No

		  </td>
		</tr>

		<tr>
		<td class="config_form_heading" colspan="2"><b>Post Manager (Employer Section)</b></td>
		</tr>
		  <tr>
		  <td class="config_form_label">Show Posts per page</td>
		  <td class="config_form_field">
		 <?php
		if (!defined('JB_MANAGER_POSTS_PER_PAGE')) {
			define ('JB_MANAGER_POSTS_PER_PAGE', 20);
		}

		 ?>
		  
		 <input type="text" name="manager_posts_per_page" size="5" value="<?php echo htmlentities(JB_MANAGER_POSTS_PER_PAGE); ?>" > Posts per page
		  

		  </td>
		</tr>


		<tr>
		<td class="config_form_heading" colspan="2"><b>Posting From (Employer Section)</b></td>
		</tr>
		  <tr>
		  <td class="config_form_label">Form Height</td>
		  <td class="config_form_field">
		 <?php
		if (!defined('JB_POSTING_FORM_HEIGHT')) {
			define ('JB_POSTING_FORM_HEIGHT', 1600);
		}

		 ?>
		  
		 <input type="text" name="posting_form_height" size="5" value="<?php echo htmlentities(JB_POSTING_FORM_HEIGHT); ?>" > Height of the posting form (in pixels)
		  

		  </td>
		</tr>

		
		
	  </table>
	  <p>&nbsp;</p>
	  <a name="resumes"></a>
	   <table class="config_form" cellpadding="5" cellspacing="2" >
		<tr>
		  <td colspan="2" class="config_form_heading">
		  Resumes</td>
		</tr>
		<tr>
		  <td width="20%" class="config_form_label">Resumes 
		  need approval?</td>
		  <td class="config_form_field">
		  <input type="radio" name="resumes_need_approval" value="YES"  <?php if (JB_RESUMES_NEED_APPROVAL=='YES') { echo " checked "; } ?> >Yes<br>
		  <input type="radio" name="resumes_need_approval" value="NO"  <?php if (JB_RESUMES_NEED_APPROVAL=='NO') { echo " checked "; } ?> >No<br>
		  
		  </td>
		  </tr>
		  <tr>
		  <td class="config_form_label">Resumes Per page</td>
		  <td class="config_form_field">
		 <?php
		if (!defined('JB_RESUMES_PER_PAGE')) {
			define ('JB_RESUMES_PER_PAGE', 30);
		}

		 ?>
		  
		 Show <input type="text" name="resumes_per_page" size="5" value="<?php echo jb_escape_html(JB_RESUMES_PER_PAGE); ?>" > resumes per page
		  

		  </td>
		</tr>
		 </table>

	  <p>&nbsp;</p>
	  <a name="themes"></a>
	  <table class="config_form" cellpadding="5" cellspacing="2" >
		<tr >
		  <td colspan="2" class="config_form_heading">
		  Theme Settings</td>
		</tr>
		 <tr>
		  <td width="20%" class="config_form_label">Select theme</td>
		  <td class="config_form_field">
		  <select name='jb_theme'>
			<option value=''>[Select]</option>
			<?php JB_theme_option_list(JB_THEME); ?>
		  </select> (The theme selected here will be for <b><?php echo $AVAILABLE_LANGS[$_SESSION['LANG']]; ?></b>. NEW! Now you can select a separate theme for each language! Go To <a href="language.php">Admin->Language</a>.
		  </td>
		</tr>
		<?php
		
		if (!defined(JB_LIST_HOVER_COLOR)) {
			define ('JB_LIST_HOVER_COLOR', '#FEFEED');
		}

		if (!defined(JB_LIST_BG_COLOR)) {
			define ('JB_LIST_BG_COLOR', '#FFFFFF');
		}
		
		?>
		 <tr>
		  <td width="20%" class="config_form_label">Mouseover effect: Background color</td>
		  <td class="config_form_field">
		  Background color:
		  <input type="text" name="jb_list_bg_color" size="7" value="<?php echo jb_escape_html(JB_LIST_BG_COLOR); ?>">(eg. #FFFFFF)<br>
		  
		  mouse-over color:
		  <input type="text" name="jb_list_hover_color" size="7" value="<?php echo jb_escape_html(JB_LIST_HOVER_COLOR); ?>">(eg. #FEFEED)<br></td>
		</tr>
		
	</table>
	 <p>&nbsp;</p>
	 <a name="plugins"></a>
	  <table class="config_form" cellpadding="5" cellspacing="2" >
		<tr >
		  <td colspan="2" class="config_form_heading">
		  Plugin Settings</td>
		</tr>
		<tr>
		  <td width="20%" class="config_form_label">Enable Plugins?</td>
		  <td class="config_form_field">
		  <input type="radio" name="jb_plugin_switch" value="YES" <?php if (JB_PLUGIN_SWITCH=='YES') { echo " checked "; } ?> >Yes<br>
		  <input type="radio" name="jb_plugin_switch" value="NO" <?php if (JB_PLUGIN_SWITCH=='NO') { echo " checked "; } ?> >No<br>
		  
		  </td>
		  </tr>
	</table>
	<input type="hidden" name='jb_enabled_plugins' value="<?php echo htmlentities(JB_ENABLED_PLUGINS); ?>">
	<input type="hidden" name='jb_plugin_config' value="<?php echo htmlentities(JB_PLUGIN_CONFIG); ?>">
	  <p>Important - <span class="is_required_mark">*</span> indicates a mandatory field if you are using SMTP. See here for common  SMTP problems: <a href="http://www.jamit.com.au/support/index.php?_m=knowledgebase&_a=view&parentcategoryid=6&pcid=1&nav=0,1" target="_blank">[Knowledge Base]</a></p>
	  <a name="email"></a>
	  <table class="config_form" cellpadding="5" cellspacing="2" >
		<tr>
		  <td  colspan="2" width="360" class="config_form_heading">
		  Email Settings</td>
		</tr>
		<tr>
		  <td class="config_form_label">Use SMTP for sending email</td>
		  <td class="config_form_field">
		 <input type="radio" name="use_mail_function" value="NO"  <?php if (JB_USE_MAIL_FUNCTION=='NO') { echo " checked "; } ?> >Yes - use SMTP<br>
		  <input type="radio" name="use_mail_function" value="YES"  <?php if (JB_USE_MAIL_FUNCTION=='YES') { echo " checked "; } ?> >No - Email will be sent through the PHP mail() function. You do not need to fill in your SMTP settings.
		 </td>
		</tr>
		 <tr>
		  <td width="20%" class="config_form_label">Hostname (of your HTTP server)</td>
		  <td class="config_form_field">
		  <input type="text" name="email_hostname" size="33" value="<?php echo jb_escape_html(JB_EMAIL_HOSTNAME); ?>"><span class="is_required_mark">*</span><br>(Most likely this is: <b><?php echo $host;?></b>)</td>
		</tr>
		 <tr>
		  <td class="config_form_label">SMTP Server address</td>
		  <td class="config_form_field">
		  <input type="text" name="email_smtp_server" size="33" value="<?php echo jb_escape_html(JB_EMAIL_SMTP_SERVER); ?>"><span class="is_required_mark">*</span><br>Eg. mail.example.com</td>
		</tr>
		<tr>
		  <td class="config_form_label">POP3 Server address</td>
		  <td class="config_form_field">
		  <input type="text" name="email_pop_server" size="33" value="<?php echo jb_escape_html(JB_EMAIL_POP_SERVER); ?>"><span class="is_required_mark">*</span><br>Eg. mail.example.com - usually the same as SMTP server</td>
		</tr>
		 <tr>
		  <td class="config_form_label">SMTP/POP3 Username</td>
		  <td class="config_form_field">
		  <input type="text" name="email_smtp_user" size="33" value="<?php echo jb_escape_html(JB_EMAIL_SMTP_USER); ?>"><span class="is_required_mark">*</span></td>
		</tr>
		 <tr>
		  <td class="config_form_label">SMTP/POP3 Password</td>
		  <td class="config_form_field">
		  <input type="password" name="email_smtp_pass"  size="33" value="<?php echo jb_escape_html(JB_EMAIL_SMTP_PASS); ?>"><span class="is_required_mark">*</span></td>
		</tr>
		<tr>
		  <td class="config_form_label">SMTP Authentication Hostname</td>
		  <td class="config_form_field">
		  <input type="text" name="email_smtp_auth_host" size="33" value="<?php echo jb_escape_html(JB_EMAIL_SMTP_AUTH_HOST); ?>"><span class="is_required_mark">*</span>(This is usually the same as your SMTP Server address)</td>
		</tr>
		<tr>
		  <td class="config_form_label">SMTP Port</td>
		  <td class="config_form_field">
		  <input type="text" name="email_smtp_port" size="33" value="<?php if (!defined('JB_EMAIL_SMTP_PORT')) { define ('JB_EMAIL_SMTP_PORT', 25); } echo jb_escape_html(JB_EMAIL_SMTP_PORT); ?>"><span class="is_required_mark">*</span>(Usually it's 25)</td>
		</tr>
		<tr>
		  <td class="config_form_label">POP3 Port</td>
		  <td class="config_form_field">
		  <input type="text" name="pop3_port" size="33" value="<?php echo jb_escape_html(JB_POP3_PORT); ?>">(Leave blank to default to 110)</td>
		</tr>
		<tr>
		  <td class="config_form_label">My SMTP server uses the POP-before-SMTP mechanism</td>
		  <td class="config_form_field">
		 <input type="radio" name="email_pop_before_smtp" value="YES"  <?php if (JB_EMAIL_POP_BEFORE_SMTP=='YES') { echo " checked "; } ?> >Yes<br>
		  <input type="radio" name="email_pop_before_smtp" value="NO"  <?php if (JB_EMAIL_POP_BEFORE_SMTP=='NO') { echo " checked "; } ?> >No - Default setting, correct 99% of cases
		 </td>
		</tr>

		<tr>
		  <td class="config_form_label">Use SSL?</td>
		  <td class="config_form_field">
		 <input type="radio" name="jb_email_smtp_ssl" value="YES"  <?php if (JB_EMAIL_SMTP_SSL=='YES') { echo " checked "; } ?> >Yes - SSL may be needed for some servers, for example GMail. Requires OpenSSL extension enabled in PHP<br>
		  <input type="radio" name="jb_email_smtp_ssl" value="NO"  <?php if (JB_EMAIL_SMTP_SSL!='YES') { echo " checked "; } ?> >No - Default setting, correct 99% of cases
		 </td>
		</tr>

		
		<tr>
		
		<?php
		
		
		$new_window = "onclick=\"test_email_window(); return false;\"";

		?>
			<td class="config_form_field" colspan="2"><input type="button" style="font-size: 11px;" <?php echo $new_window; ?> value="Test POP Connection..."></td>
		</tr>
		<tr>
		  <td class="config_form_label">Include a Signiture in Emails?</td>
		  <td class="config_form_field">
		  
		  
		  <input type="radio" name="email_sig_switch" value="YES"  <?php if (JB_EMAIL_SIG_SWITCH=='YES') { echo " checked "; } ?> >Yes (This will include a link to this site, at the bottom of the email)<br>
		  <input type="radio" name="email_sig_switch" value="NO"  <?php if (JB_EMAIL_SIG_SWITCH=='NO') { echo " checked "; } ?> >No
		  </td>
		</tr>

		
		<tr>
		  <td class="config_form_label">Send these emails to Admin</td>
		  <td class="config_form_field">
		  
		  
		  <table>

			<tr>
				<td class="config_form_field">
				<input type="checkbox" name="email_admin_receipt_switch" value="YES" <?php if (JB_EMAIL_ADMIN_RECEIPT_SWITCH=='YES') { echo " checked "; } ?>> Application Receipts
				</td>
				<td class="config_form_field">
				<input type="checkbox" name="email_employer_signup_switch" value="YES" <?php if (JB_EMAIL_EMPLOYER_SIGNUP_SWITCH=='YES') { echo " checked "; } ?>> New Employer Sign Up
				</td>
				<td class="config_form_field">
				<input type="checkbox" name="jb_email_candidate_signup_switch" value="YES" <?php if (JB_EMAIL_CANDIDATE_SIGNUP_SWITCH=='YES') { echo " checked "; } ?>> New Candidate Sign Up
				</td>
				<td>
				</td>
			</tr>

			<tr>
				<td class="config_form_field">
				<input type="checkbox" name="email_new_post_switch" value="YES" <?php if (JB_EMAIL_NEW_POST_SWITCH=='YES') { echo " checked "; } ?>> New Job is Posted
				</td>
				<td class="config_form_field">
				<input type="checkbox" name="jb_email_admin_resupdate_switch" value="YES" <?php if (JB_EMAIL_ADMIN_RESUPDATE_SWITCH=='YES') { echo " checked "; } ?>> A resume is posted / updated
				</td>
				<td class="config_form_field">
				<input type="checkbox" name="jb_email_admin_neword_switch" value="YES" <?php if (JB_EMAIL_ADMIN_NEWORD_SWITCH=='YES') { echo " checked "; } ?>>  A new order is placed
				</td>
				<td>
				</td>
			</tr>

		  </table>
		  
		  
		  </td>
		</tr>



		<tr>
		  <td class="config_form_label">Send these emails to customers (Employers/Candidates)</td>
		  <td class="config_form_field">
		  
		  
		  <table>

			<tr>
				<td class="config_form_field">
				<input type="checkbox" name="email_order_completed_switch" value="YES" <?php if (JB_EMAIL_ORDER_COMPLETED_SWITCH=='YES') { echo " checked "; } ?>> Order is Completed
				</td>
				<td class="config_form_field">
				<input type="checkbox" name="email_member_exp_switch" value="YES" <?php if (JB_EMAIL_MEMBER_EXP_SWITCH=='YES') { echo " checked "; } ?>> Membership had expired
				</td>
				<td>
				
				</td>
				<td>
				</td>
			</tr>

			

		  </table>

		  <tr>
		  <td class="config_form_label">Send these emails to Employers</td>
		  <td class="config_form_field">
		  
		  
		  <table>

			<tr>
				<td class="config_form_field">
				<input type="checkbox" name="email_subscr_exp_switch" value="YES" <?php if (JB_EMAIL_SUBSCR_EXP_SWITCH=='YES') { echo " checked "; } ?>> Subscription to view resumes had expired
				</td>
				<td class="config_form_field">
				<input type="checkbox" name="jb_email_post_exp_switch" value="YES" <?php if (JB_EMAIL_POST_EXP_SWITCH=='YES') { echo " checked "; } ?>> Job post had expired
				</td>
				<td class="config_form_field">
				<input type="checkbox" name="jb_email_post_appr_switch" value="YES" <?php if (JB_EMAIL_POST_APPR_SWITCH=='YES') { echo " checked "; } ?>> Job post was approved
				</td>
				<td>
				</td>
			</tr>


			<tr>
				<td class="config_form_field">
				<input type="checkbox" name="jb_email_post_appr_switch" value="YES" <?php if (JB_EMAIL_POST_APPR_SWITCH=='YES') { echo " checked "; } ?>> Job post had been approved?
				</td>
				<td class="config_form_field">
				<input type="checkbox" name="jb_email_post_disapp_switch" value="YES" <?php if (JB_EMAIL_POST_DISAPP_SWITCH=='YES') { echo " checked "; } ?>> job post had been disapproved?
				</td>
				<td class="config_form_field">
				<input type="checkbox" name="jb_email_emp_signup" value="YES" <?php if (JB_EMAIL_EMP_SIGNUP=='YES') { echo " checked "; } ?>> Signup Confirmation
				</td>
				<td>
				</td>
			</tr>

		  </table>
		  
		  </td>
		</tr>


		 <tr>
		  <td class="config_form_label">Send these emails to Candidates</td>
		  <td class="config_form_field">
		  
		  
		  <table>

			<tr>
				<td>
				<input type="checkbox" name="email_candidate_receipt_switch" value="YES" <?php if (JB_EMAIL_CANDIDATE_RECEIPT_SWITCH=='YES') { echo " checked "; } ?>> Application Receipts
				</td>
				<td>
				<input type="checkbox" name="jb_email_can_signup" value="YES" <?php if (JB_EMAIL_CAN_SIGNUP=='YES') { echo " checked "; } ?>> Signup Confirmation
				</td>
				<td>
				&nbsp;
				</td>
				<td>
				</td>
			</tr>


		

			

		  </table>
		  
		  
		  </td>
		</tr>


		<tr>
		  <td class="config_form_label">Shorten URLs</td>
		  <td class="config_form_field">
		  <input type="radio" name="email_url_shorten" value="YES"  <?php if (EMAIL_URL_SHORTEN=='YES') { echo " checked "; } ?> >Yes - This will shoren all URLs in all outgoing emails. Eg, http://www.test.com/employers/alerts.php?key=ad4adfah5c537 will be reduced to something like http://www.test.com/su.php?ref=9a510f3a - This prevents the URLs from being split into multiple lines, as some email systems are limited to only 80 characters. A trade-off is that a little more CPU + RAM is required when sending out emails<br>
		  &nbsp;&nbsp;&nbsp;Expire short URLs after x days. 
		  <input type="radio" name="email_url_shorten" value="NO"  <?php if (EMAIL_URL_SHORTEN=='NO') { echo " checked "; } ?> >No<br>
		  </td>
		</tr>
		 <tr>
		  <td  class="config_form_label">Email Debug Mode</td>
		  <td  class="config_form_field">
		  <input type="radio" name="email_debug_switch" value="YES"  <?php if (JB_EMAIL_DEBUG_SWITCH=='YES') { echo " checked "; } ?> >Yes - This will show detailed messages when sending email so you can trouble shoot email problems. Set to No when live.<br>
		  <input type="radio" name="email_debug_switch" value="NO"  <?php if (JB_EMAIL_DEBUG_SWITCH=='NO') { echo " checked "; } ?> >No<br>
		  </td>
		</tr>
		 </tr>
		 <tr>
		  <td class="config_form_label">Replace @ sign when displaying email addresses? (This helps prevent spammers from harvesting email addresses)</td>
		  <td  class="config_form_field">
		  <input type="radio" name="email_at_replace" value="YES"  <?php if (JB_EMAIL_AT_REPLACE=='YES') { echo " checked "; } ?> >Yes - replace @ with <IMG SRC="<?php echo JB_THEME_URL; ?>images/at.gif" WIDTH="13" HEIGHT="9" BORDER="0" ALT=""> (an image) <br>
		  <input type="radio" name="email_at_replace" value="YES_2"  <?php if (JB_EMAIL_AT_REPLACE=='YES_2') { echo " checked "; } ?> >Yes - replace @ with <b>&amp;#<?php echo ord('@')?></b>; (HTML encoded @ sign. Works well for copy & paste, but could not be as safe as the above option) <br>
		  <input type="radio" name="email_at_replace" value="NO"  <?php if (JB_EMAIL_AT_REPLACE=='NO') { echo " checked "; } ?> >No<br>
		  </td>
		</tr>
		 <tr>
		  <td class="config_form_label">Outgoing email queue</td>
		  <td class="config_form_field">
		  All emails are placed on a queue before they are sent. This is useful for a number of reasons: 1. It does not bog down your server if you are sending a lot of email because email sending is throttled. 2. If your server has a limit for how many emails it can send per hour, you can tweak the settings below so that the job board stays inside this limit. 3. You can review each email that was sent, emails can be kept for your records for the specified amount of days. (Note: If you want to avoid throttling and send all at once, you can set the 'send x emails per batch' setting to a very high number - this is not recommended for most users. Also, sometimes a temporary error can occur because the server had reached the a limit or for another reason. In that case, the job board will try to resend the email x amount of times before giving up.)<br>
		  Send <input type="text" name="emails_per_batch" size="3" value="<?php echo jb_escape_html(JB_EMAILS_PER_BATCH); ?>">emails per batch (enter a number > 0. Please keep this setting conservative - sending too many emails per batch can slow down the server and cause stability problems. If your cron is set to run every 5 minutes, 40 emails per batch would be 480 emails per hour.)<br>
		  On error, retry <input type="text" name="emails_max_retry" size="3" value="<?php echo jb_escape_html(JB_EMAILS_MAX_RETRY); ?>"> times before giving up. (recommened: 5)<br>
		  On error, wait at least <input type="text" name="emails_error_wait" size="3" value="<?php echo jb_escape_html(JB_EMAILS_ERROR_WAIT); ?>">minutes before retry. (10 minutes recommended)<br>
		  Keep sent emails for <input type="text" name="emails_days_keep" size="3" value="<?php  if ((JB_EMAILS_DAYS_KEEP=='JB_EMAILS_DAYS_KEEP')) { define (JB_EMAILS_DAYS_KEEP, '0'); } echo jb_escape_html(JB_EMAILS_DAYS_KEEP); ?>">days. (0 = keep forever)<br> 
		  </td>
		</tr>
		</table>
		<p>&nbsp;</p>
		<a name="errors"></a>
		<table class="config_form" cellpadding="5" cellspacing="2">
		<tr>
		  <td colspan="2" class="config_form_heading">
		  Errors and Warnings</td>
		</tr>
		<tr>
		  <td class="config_form_label" width="20%"> 
		   Use a custom error handler?</td>
		  <td class="config_form_field">
		   <input type="radio" name="jb_set_custom_error" size="49" value="YES" <?php if (JB_SET_CUSTOM_ERROR=='YES') { echo " checked "; } ?> >Yes - Error messages will never be displayed to the screen, and will be logged in to a custom file instead (see 'System Info' form the menu to view the contents of this file)
		   <br><input type="radio" name="jb_set_custom_error" value="NO" <?php if (JB_SET_CUSTOM_ERROR!='YES') { echo " checked "; } ?> >No (Use the default setting for your server)</td>
		</tr>
		</table>
		
	  <p>
	  <input type="submit" value="Save Configuration" name="save"></p>
	</form>

<?php

} // end the jb_main_config_form() function

?>
<p>&nbsp;</p>
</body>
<?php

function jb_get_config_definitions() {

	$input = $_REQUEST;

	// do not allow evil tags
	foreach ($input as $key=>$val) {
		$val = str_replace('\\', '\\\\', $input[$key]); // escape any '\' characters 
		$input[$key] = str_replace('\'', "\\'", trim(JB_clean_str($val))); // escape ' characters
		
	}
	// clean the sensitive settings
	$input['jb_cache_driver'] = preg_replace('/[^a-z^0-9^-^_]+/i', '', $input['jb_cache_driver']);
	$input['jb_theme'] = preg_replace('/[^a-z^0-9^-^_]+/i', '', $input['jb_theme']);
	$input['jb_default_pay_meth'] = preg_replace('/[^a-z^0-9^-^_]+/i', '', $input['jb_default_pay_meth']);

	// process the lat and lng from a google map link
	// eg http://maps.google.com/maps?showlabs=1&ie=UTF8&ll=38.891033,-93.427734&spn=33.74472,73.740234&t=h&z=4

	if (preg_match('/&ll=(-?[0-9\.\+\-]+),(-?[0-9\.\+\-]+)?&/', $input['gmap_location'], $m)) {
		$input['gmap_lat']=$m[1];
		$input['gmap_lng']=$m[2];
	}

 
	if (!$input['jb_new_file_chmod']) {
		$input['jb_new_file_chmod'] = '0666';
	}
	if (!$input['jb_new_dir_chmod']) {
		$input['jb_new_dir_chmod'] = '0777';
	}
	if (!defined('JB_DEMO_MODE')) {
		define ('JB_DEMO_MODE', 'NO');
	}
	$str = " 

define('JB_SITE_NAME',  '".$input['site_name']."');
define('JB_SITE_HEADING',  '".$input['site_heading']."');
define('JB_SITE_DESCRIPTION',  '".$input['site_description']."');
define('JB_SITE_KEYWORDS', '".$input['site_keywords']."');
define('JB_SITE_CONTACT_EMAIL', '".$input['site_contact_email']."');
define('JB_ADMIN_PASSWORD', '".$input['admin_password']."');
define('JB_THEME', '".$input['jb_theme']."');

define('JB_CRON_EMULATION_ENABLED', '".$input['cron_emulation_enabled']."');
define('JB_CRON_HTTP_ALLOW', '".$input['cron_http_allow']."');
define('JB_CRON_HTTP_USER', '".$input['cron_http_user']."');
define('JB_CRON_HTTP_PASS', '".$input['cron_http_pass']."');

define('JB_CACHE_ENABLED', '".$input['jb_cache_enabled']."');
define('JB_USE_SERIALIZE', '".$input['use_serialize']."');

define('JB_PLUGIN_SWITCH', '".$input['jb_plugin_switch']."');
// Paths and Locations

define('JB_CANDIDATE_FOLDER', '".$input['candidate_folder']."');
define('JB_EMPLOYER_FOLDER', '".$input['employer_folder']."');


define('JB_IMG_MAX_WIDTH',  '".intval($input['img_max_width'])."');
define('JB_KEEP_ORIGINAL_IMAGES',  '".$input['jb_keep_original_images']."');
define('JB_BIG_IMG_MAX_WIDTH',  '".intval($input['big_img_max_width'])."');
define('JB_IMG_PATH',  '".$input['img_path']."');
define('JB_FILE_PATH',  '".$input['file_path']."');
define('JB_IM_PATH',  '".$input['im_path']."');
define('JB_USE_GD_LIBRARY',  '".$input['use_gd_library']."');

define('JB_RSS_FEED_PATH',  '".$input['rss_feed_path']."');
define('JB_RSS_FEED_LOGO',  '".$input['rss_feed_logo']."');

define('JB_NEW_FILE_CHMOD',  ".$input['jb_new_file_chmod'].");
define('JB_NEW_DIR_CHMOD',  ".$input['jb_new_dir_chmod'].");



if (isset(\$_SERVER['HTTPS']) && !empty(\$_SERVER['HTTPS']) && (strtolower(\$_SERVER['HTTPS']) != 'off')) {
//if (true) {
	define('JB_SITE_LOGO_URL',  str_replace('http:', 'https:', '".$input['site_logo_url']."'));
	define('JB_FILE_HTTP_PATH',  str_replace('http:', 'https:', '".$input['file_http_path']."'));
	define('JB_BASE_HTTP_PATH',  str_replace('http:', 'https:', '".$input['base_http_path']."'));
	define('JB_IMG_HTTP_PATH',  str_replace('http:', 'https:', '".$input['img_http_path']."'));
	
} else {
	
	define('JB_SITE_LOGO_URL', '".$input['site_logo_url']."');
	define('JB_FILE_HTTP_PATH', '".$input['file_http_path']."');
	define('JB_BASE_HTTP_PATH',  '".$input['base_http_path']."');
	define('JB_IMG_HTTP_PATH', '".$input['img_http_path']."');
}

define('JB_NAME_FORMAT', '".$input['jb_name_format']."');
// categories

define('JB_CAT_PATH_ONLY_LEAF', '".$input['jb_cat_path_only_leaf']."');
define('JB_CAT_RSS_SWITCH', '".$input['jb_cat_rss_switch']."');
define('JB_SHOW_SUBCATS', '".intval($input['show_subcats'])."');
define('JB_CAT_COLS_FP', '".intval($input['cat_cols_fp'])."');
define('JB_CAT_COLS', '".intval($input['cat_cols'])."');
define('JB_FORMAT_SUB_CATS', '".$input['format_sub_cats']."');
define('JB_SUB_CATEGORY_COLS', '".intval($input['sub_category_cols'])."');
define('JB_CAT_NAME_CUTOFF', '".$input['cat_name_cutoff']."');
define('JB_CAT_NAME_CUTOFF_CHARS', '".$input['cat_name_cutoff_chars']."');
define('JB_INDENT_CATEGORY_LIST', '".$input['indent_category_list']."');
define('JB_CAT_SHOW_OBJ_COUNT', '".$input['cat_show_obj_count']."');
define('JB_MOD_REWRITE_REMOVE_ACCENTS', '".$input['mod_rewrite_remove_accents']."');
define('JB_CAT_MOD_REWRITE', '".$input['cat_mod_rewrite']."');
define('JB_JOB_MOD_REWRITE', '".$input['job_mod_rewrite']."');
define('JB_PRO_MOD_REWRITE', '".$input['pro_mod_rewrite']."');
define('JB_MOD_REWRITE_DIR', '".$input['mod_rewrite_dir']."');
define('JB_MOD_REWRITE_JOB_DIR', '".$input['mod_rewrite_job_dir']."');
define('JB_MOD_REWRITE_PRO_DIR', '".$input['mod_rewrite_pro_dir']."');
define('JB_JOB_PAGES_MOD_REWRITE', '".$input['job_pages_mod_rewrite']."');
define('JB_MOD_REWRITE_JOB_PAGES_PREFIX', '".$input['mod_rewrite_job_pages_prefix']."');
// data cleaning
define('JB_STRIP_HTML', 'YES');
define('JB_STRIP_LATIN1', '".$input['strip_latin1']."');
define('JB_BREAK_LONG_WORDS', '".$input['break_long_words']."');
define('JB_LNG_MAX', '".intval($input['lng_max'])."');
define('JB_CLEAN_STRINGS', '".$input['clean_strings']."');
define('JB_ALLOWED_EXT', '".trim($input['allowed_ext'])."');
define('JB_ALLOWED_IMG', '".trim($input['allowed_img'])."');
define('JB_MAX_UPLOAD_BYTES', '".intval($input['max_upload_bytes'])."');

// features
define('JB_CAN_LANG_ENABLED', '".$input['can_lang_enabled']."');
define('JB_EMP_LANG_ENABLED', '".$input['emp_lang_enabled']."');
define('JB_MAP_DISABLED', '".$input['map_disabled']."');

define('JB_GMAP_LOCATION', '".$input['gmap_location']."');
define('JB_GMAP_LAT', '".$input['gmap_lat']."');
define('JB_GMAP_LNG', '".$input['gmap_lng']."');
define('JB_GMAP_ZOOM', '".$input['gmap_zoom']."');
define('JB_GMAP_SHOW_IF_MAP_EMPTY', '".$input['gmap_show_if_map_empty']."');
define('JB_PIN_IMAGE_FILE', '".$input['pin_image_file']."');
define('JB_MAP_IMAGE_FILE', '".$input['map_image_file']."');
define('JB_PREVIEW_RESUME_IMAGE', '".$input['preview_resume_image']."');
define('JB_BAD_WORD_FILTER', '".$input['bad_word_filter']."');
define('JB_BAD_WORDS', '".trim($input['bad_words'])."');
define('JB_ONLINE_APP_ENABLED', '".$input['online_app_enabled']."');
define('JB_APP_CHOICE_SWITCH', '".$input['jb_app_choice_switch']."');

define('JB_RESUME_REPLY_ENABLED', '".$input['resume_reply_enabled']."');
define('JB_FIELD_BLOCK_APP_SWITCH', '".$input['field_block_app_switch']."');

define('JB_JOB_ALERTS_ENABLED', '".$input['job_alerts_enabled']."');
define('JB_RESUME_ALERTS_ENABLED', '".$input['resume_alerts_enabled']."');

define('JB_JOB_ALERTS_DAYS', '".intval($input['job_alerts_days'])."');

define('JB_RESUME_ALERTS_DAYS', '".intval($input['resume_alerts_days'])."');
define('JB_TAF_ENABLED', '".$input['taf_enabled']."');
define('JB_SAVE_JOB_ENABLED', '".$input['save_job_enabled']."');
define('JB_SHOW_PREMIUM_LIST', '".$input['jb_show_premium_list']."');
define('JB_DONT_REPEAT_PREMIUM', '".$input['jb_dont_repeat_premium']."');
define('JB_ONLINE_APP_SIGN_IN', '".$input['online_app_sign_in']."');
define('JB_ONLINE_APP_EMAIL_ADMIN', '".$input['online_app_email_admin']."');
define('JB_ONLINE_APP_EMAIL_PREMIUM', '".$input['online_app_email_premium']."');
define('JB_ONLINE_APP_EMAIL_STD', '".$input['online_app_email_std']."');
define('JB_ONLINE_APP_REVEAL_PREMIUM', '".$input['online_app_reveal_premium']."');
define('JB_ONLINE_APP_REVEAL_STD', '".$input['online_app_reveal_std']."');
define('JB_ONLINE_APP_REVEAL_RESUME', '".$input['online_app_reveal_resume']."');
define('JB_TAF_SIGN_IN', '".$input['taf_sign_in']."');
define('JB_ANON_RESUME_ENABLED', '".$input['anon_resume_enabled']."');
define('JB_FIELD_BLOCK_SWITCH', '".$input['field_block_switch']."');
define('JB_MEMBER_FIELD_SWITCH', '".$input['member_field_switch']."');
define('JB_MEMBER_FIELD_IGNORE_PREMIUM', '".$input['jb_member_field_ignore_premium']."');
define('JB_NEED_SUBSCR_FOR_REQUEST', '".$input['need_subscr_for_request']."');
define('JB_JOB_ALERTS_ACTIVE_DAYS', '".intval($input['job_alerts_active_days'])."');
define('JB_JOB_ALERTS_ITEMS', '".intval($input['job_alerts_items'])."');
define('JB_RESUME_ALERTS_ACTIVE_DAYS', '".intval($input['resume_alerts_active_days'])."');
define('JB_RESUME_ALERTS_ITEMS', '".intval($input['resume_alerts_items'])."');
define('JB_RESUME_ALERTS_SUB_IGNORE', '".$input['jb_resume_alerts_sub_ignore']."');
define('JB_CODE_ORDER_BY', '".$input['jb_code_order_by']."');
// Database
define('JB_MYSQL_HOST', '".$input['jb_mysql_host']."');
define('JB_MYSQL_USER', '".$input['jb_mysql_user']."');
define('JB_MYSQL_PASS', '".$input['jb_mysql_pass']."');
define('JB_MYSQL_DB', '".$input['jb_mysql_db']."');
//date & time
define('JB_DATE_FORMAT', '".$input['date_format']."');
define('JB_GMT_DIF', '".$input['gmt_dif']."');

define('JB_SCW_INPUT_SEQ', '".$input['scw_input_seq']."');
define('JB_SCW_DATE_FORMAT', '".$input['scw_date_format']."');


define('JB_DATE_INPUT_SEQ', '".$input['date_input_seq']."');
// Accounts permissions
define('JB_CA_NEEDS_ACTIVATION',  '".$input['ca_needs_activation']."');
define('JB_EM_NEEDS_ACTIVATION',  '".$input['em_needs_activation']."');
define('JB_FREE_POST_LIMIT', '".$input['free_post_limit']."');
define('JB_FREE_POST_LIMIT_MAX', '".intval($input['free_post_limit_max'])."');
define('JB_BEGIN_PREMIUM_CREDITS', '".intval($input['begin_premium_credits'])."');
define('JB_BEGIN_STANDARD_CREDITS', '".intval($input['begin_standard_credits'])."');
define('JB_ALLOW_ADMIN_LOGIN', '".$input['allow_admin_login']."');


// menu
define('JB_CANDIDATE_MENU_TYPE', '".$input['candidate_menu_type']."');
define('JB_EMPLOYER_MENU_TYPE', '".$input['employer_menu_type']."');
//search form
define('JB_SEARCH_FORM_LAYOUT', '".$input['search_form_layout']."');

define('JB_SUBSCRIPTION_FEE_ENABLED', '".$input['subscription_fee_enabled']."');
define('JB_POSTING_FEE_ENABLED', '".$input['posting_fee_enabled']."');
define('JB_PREMIUM_AUTO_UPGRADE', '".$input['premium_auto_upgrade']."');

define('JB_CANDIDATE_MEMBERSHIP_ENABLED',  '".$input['candidate_membership_enabled']."');
define('JB_EMPLOYER_MEMBERSHIP_ENABLED',  '".$input['employer_membership_enabled']."');
define('JB_PREMIUM_POSTING_FEE_ENABLED', '".$input['premium_posting_fee_enabled']."');
define('JB_INVOICE_ID_START', '".intval($input['invoice_id_start'])."');
define('JB_DEFAULT_PAY_METH', '".$input['jb_default_pay_meth']."');

// Posts...
define('JB_POSTS_NEED_APPROVAL', '".$input['posts_need_approval']."');
define('JB_POSTS_PER_PAGE', '".intval($input['posts_per_page'])."');
define('JB_POSTS_PER_RSS', '".intval($input['posts_per_rss'])."');
define('JB_PREMIUM_POSTS_PER_PAGE', '".intval($input['premium_posts_per_page'])."');
define('JB_PREMIUM_POSTS_LIMIT', '".$input['premium_posts_limit']."');
define('JB_P_POSTS_DISPLAY_DAYS', '".intval($input['p_posts_display_days'])."');

define('JB_POSTS_DISPLAY_DAYS', '".intval($input['posts_display_days'])."');
define('JB_POSTS_DESCRIPTION_CHARS', '".intval($input['posts_description_chars'])."');
define('JB_POSTS_SHOW_DESCRIPTION', '".$input['posts_show_description']."');
define('JB_POSTS_SHOW_JOB_TYPE', '".$input['posts_show_job_type']."');
define('JB_POSTS_SHOW_POSTED_BY', '".$input['posts_show_posted_by']."');
define('JB_POSTS_SHOW_POSTED_BY_BR', '".$input['posts_show_posted_by_br']."');
define('POSTS_SHOW_CATEGORY', '".$input['posts_show_category']."');
define('POSTS_SHOW_CATEGORY_BR', '".$input['posts_show_category_br']."');
define('JB_POSTS_SHOW_DAYS_ELAPSED', '".$input['posts_show_days_elapsed']."');

define('JB_P_POSTS_SHOW_DAYS_ELAPSED', '".$input['p_posts_show_days_elapsed']."');
define('JB_SHOW_PREMIUM_HITS', '".$input['show_premium_hits']."');
define('JB_MANAGER_POSTS_PER_PAGE', '".intval($input['manager_posts_per_page'])."');
define('JB_POSTING_FORM_HEIGHT', '".intval($input['posting_form_height'])."');

// Resumes
define('JB_RESUMES_NEED_APPROVAL', '".$input['resumes_need_approval']."');
define('JB_RESUMES_PER_PAGE', '".intval($input['resumes_per_page'])."');
define('JB_RESUME_REQUEST_SWITCH', '".$input['resume_request_switch']."');

// Email

define('JB_USE_MAIL_FUNCTION', '".$input['use_mail_function']."');
define('JB_EMAIL_HOSTNAME', '".$input['email_hostname']."');
define('JB_EMAIL_SMTP_SERVER', '".$input['email_smtp_server']."');
define('JB_EMAIL_POP_SERVER', '".$input['email_pop_server']."');
define('JB_EMAIL_SMTP_USER', '".$input['email_smtp_user']."');
define('JB_EMAIL_SMTP_PASS', '".$input['email_smtp_pass']."');
define('JB_EMAIL_SMTP_AUTH_HOST', '".$input['email_smtp_auth_host']."');
define('JB_EMAIL_SMTP_PORT', '".intval($input['email_smtp_port'])."');
define('JB_POP3_PORT', '".intval($input['pop3_port'])."');
define('JB_EMAIL_SIG_SWITCH', '".$input['email_sig_switch']."');
define('JB_EMAIL_ADMIN_RECEIPT_SWITCH', '".$input['email_admin_receipt_switch']."');
define('JB_EMAIL_ORDER_COMPLETED_SWITCH', '".$input['email_order_completed_switch']."');
define('JB_EMAIL_MEMBER_EXP_SWITCH', '".$input['email_member_exp_switch']."');
define('JB_EMAIL_SUBSCR_EXP_SWITCH', '".$input['email_subscr_exp_switch']."');
define('JB_EMAIL_CANDIDATE_RECEIPT_SWITCH', '".$input['email_candidate_receipt_switch']."');
define('JB_EMAIL_DEBUG_SWITCH', '".$input['email_debug_switch']."');
define('EMAIL_URL_SHORTEN', '".$input['email_url_shorten']."');
define('JB_EMAIL_EMPLOYER_SIGNUP_SWITCH', '".$input['email_employer_signup_switch']."');
define('JB_EMAIL_CANDIDATE_SIGNUP_SWITCH', '".$input['jb_email_candidate_signup_switch']."');
define('JB_EMAIL_EMP_SIGNUP', '".$input['jb_email_emp_signup']."');
define('JB_EMAIL_CAN_SIGNUP', '".$input['jb_email_can_signup']."');
define('JB_EMAIL_AT_REPLACE', '".$input['email_at_replace']."');
define('JB_EMAIL_NEW_POST_SWITCH', '".$input['email_new_post_switch']."');
define('JB_EMAILS_PER_BATCH', '".intval($input['emails_per_batch'])."');
define('JB_EMAILS_MAX_RETRY', '".intval($input['emails_max_retry'])."');
define('JB_EMAILS_ERROR_WAIT', '".intval($input['emails_error_wait'])."');
define('JB_EMAILS_DAYS_KEEP', '".intval($input['emails_days_keep'])."');
define('JB_EMAIL_POP_BEFORE_SMTP', '".$input['email_pop_before_smtp']."');
define('JB_EMAIL_SMTP_SSL', '".$input['jb_email_smtp_ssl']."');
define('JB_ENABLED_PLUGINS', '".$input['jb_enabled_plugins']."');
define('JB_PLUGIN_CONFIG', '" . str_replace('\'', '\\\'', JB_PLUGIN_CONFIG) . "');
define('JB_EMAIL_ADMIN_RESUPDATE_SWITCH', '".$input['jb_email_admin_resupdate_switch']."');
define('JB_EMAIL_ADMIN_NEWORD_SWITCH', '".$input['jb_email_admin_neword_switch']."');
define('JB_EMAIL_POST_EXP_SWITCH', '".$input['jb_email_post_exp_switch']."');
define('JB_EMAIL_POST_APPR_SWITCH', '".$input['jb_email_post_appr_switch']."');
define('JB_EMAIL_POST_DISAPP_SWITCH', '".$input['jb_email_post_disapp_switch']."');
define('JB_CRON_LIMIT', '".$input['jb_cron_limit']."');
define('JB_LIST_HOVER_COLOR', '".$input['jb_list_hover_color']."');
define('JB_LIST_BG_COLOR', '".$input['jb_list_bg_color']."');
define('JB_SET_CUSTOM_ERROR', '".$input['jb_set_custom_error']."');
define('JB_DEMO_MODE', '".JB_DEMO_MODE."');
define('JB_MEMCACHE_HOST', '".$input['jb_memcache_host']."');
define('JB_MEMCACHE_PORT', '".$input['jb_memcache_port']."');
define('JB_MEMCACHE_COMPRESSED', '".$input['jb_memcache_compressed']."');
define('JB_CACHE_DRIVER', '".$input['jb_cache_driver']."');
define('JB_POSTS_SHOW_JOB_TYPE_BR', '".$input['posts_show_job_type_br']."');



	";

	JBPLUG_do_callback('set_edit_config_str', $str); //A plugin can modify the $val

	return $str;


}



function jb_get_config_code() {

	$str = "<?php
if (defined('E_DEPRECATED')) {
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
} else {
	error_reporting(E_ALL ^ E_NOTICE);
}
";

	$str .= jb_get_config_definitions();

	$str .= "
// backend stuff..


define('JB_SEARCH_CHECK_BOX_LINE_BREAK', '');

\$incs = explode(PATH_SEPARATOR, ini_get('include_path'));
foreach (\$incs as \$inc) {
	if (strpos(strtolower(\$inc), 'pear')!=true) {
		\$new_incs[] = \$inc;
	}
}
ini_set('include_path', implode(PATH_SEPARATOR, \$new_incs));
function jb_custom_error_handler(\$errno, \$errmsg, \$filename, \$linenum, \$vars) {
	if ((\$errno <= 4) || (\$errno=='sql')) { // Log the fatals & warnings
		\$str .= date('r', time());
		\$str .= ' | ';
		\$str .= \$errno.' | ';
		\$str .= \$errmsg.' | ';
		\$str .= 'file: '.\$filename.' | ';
		\$str .= 'line: '.\$linenum.' | ';
		\$str .= \"<br>\\n\";
		if (!function_exists('JB_get_cache_dir')) {
			\$dir = 'cache/';
		} else {
			\$dir = JB_get_cache_dir();
		}
		\$filename = \$dir.'error_log_'.md5(md5(JB_ADMIN_PASSWORD));
		\$fp = fopen(\$filename, 'a');
		fwrite(\$fp, \$str, strlen(\$str));
		fclose(\$fp);

		return true;
	}

	
}
if (JB_SET_CUSTOM_ERROR=='YES') {
	set_error_handler('jb_custom_error_handler');
}
include dirname(__FILE__).'/db.php';

if (\$DB_ERROR=='') {
	\$dir = dirname(__FILE__);
	require (\$dir.'/lang/lang.php');
	require (\$dir.'/include/themes.php');
	require (\$dir.'/include/lib/mail/email_message.php');
	require (\$dir.'/include/lib/mail/smtp_message.php');
	require (\$dir.'/include/lib/mail/smtp.php');
	require (\$dir.'/include/mail_manager.php');
	require (\$dir.'/include/accounting_functions.php');
	require (\$dir.'/include/currency_functions.php');
	require (\$dir.'/include/dynamic_forms.php');
	require_once (\$dir.'/include/plugin_manager.php');
	require (\$dir.'/include/invoice_functions.php');
	require (\$dir.'/include/functions.php');
	
	if (!get_magic_quotes_gpc()) JB_unfck_gpc();
} elseif (basename(\$_SERVER['PHP_SELF'])!=='install.php') { 

	\$http_url = \$_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
	
	\$http_url = str_replace ('admin/', '', \$http_url);
		
	if (file_exists(dirname(__FILE__).'/admin/install.php')) {
		\$http_url = preg_replace ('#/(/admin/)?[^/]+$#', '/admin/install.php', \$http_url);
		JB_echo_install_info (\$http_url);
		die();
	} elseif (basename(\$_SERVER['PHP_SELF'])!=='edit_config.php') {
		\$http_url = preg_replace ('#/(/admin/)?[^/]+$#', '/admin/edit_config.php', \$http_url);
		echo_edit_config_info(\$http_url);
		die();
	}
	
}


?>";

	return $str;


}

JB_admin_footer();

?>