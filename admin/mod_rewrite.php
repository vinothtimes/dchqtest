<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
@set_time_limit ( 180 );
require("../config.php");
require (dirname(__FILE__)."/admin_common.php");

if (!defined('JB_MOD_REWRITE_DIR')) {
	define ('JB_MOD_REWRITE_DIR', 'category/');
}

JB_admin_header('Admin -> Mod Rewrite');

?>
<h3>Extras: mod_rewrite</h3>
<br>
Settings: <a href="edit_config.php#mod_rewrite">Edit Mod_rewrite settings</a>
<hr>
<p>
This extra configuration feature is for those lucky webmasters who are running this software on an Apache server with the mod_rewrite module enabled. If this feature is enabled, the job board will map some dynamic URLs to appear as if they were static and more meaningful. The theory is that these more meaningful URLs get better search engine rankings. Mod_rewrite can be turned on/off in the Main Config. (Note: Mod_rewrite is a 3rd party application that needs to be installed separately. It is mostly for advanced webmasters, but a lot of information about it can be found on search engines.)
</p><p>
Mod_rewrite Status: <?php if (JB_CAT_MOD_REWRITE=='YES') { echo 'On'; } else { echo "Off";  } ?> <a href="edit_config.php#mod_rewrite">(Change status...)</a></p>
To configure Apache, please insert the following lines in to your .htaccess file:<br>
<?php
$base = JB_BASE_HTTP_PATH;

$base = str_replace ('http://', '', JB_BASE_HTTP_PATH);
$a = array();
$a = explode('/',$base);

array_shift($a); // get rid of the host part

$base = implode("/", $a);
?>
<textarea rows="10" cols="100" style="font-size:8pt; width:100%"><?php



// get rid of the %CLASS% %DATE% tags, etc from the jobs url
$job_dir = preg_replace ('#%.+%/?$#', '', $base.JB_MOD_REWRITE_JOB_DIR );

$mod_str = "
<IfModule mod_rewrite.c>
RewriteEngine on
RewriteRule ^".$base.JB_MOD_REWRITE_DIR."(.+)$ /".$base."index\\.php?cat_name=\$1 [NC,L]
RewriteRule ^".$job_dir."(.+)$ /".$base."index\\.php?post_permalink=1&post_id=\$1&%{QUERY_STRING} [NC,L]
RewriteRule ^".$base.JB_MOD_REWRITE_PRO_DIR."(.+)$ /".$base."index\\.php?show_emp=\$1 [NC,L]
RewriteCond %{QUERY_STRING} .+
RewriteRule ^".$base.JB_MOD_REWRITE_JOB_PAGES_PREFIX."([0-9]+) /".$base."index.php?%{QUERY_STRING}&job_page_link=\$1 [NC,L]
RewriteRule ^".$base.JB_MOD_REWRITE_JOB_PAGES_PREFIX."([0-9]+) /".$base."index\\.php?job_page_link=\$1 [NC,L]
</IfModule>";
echo htmlentities($mod_str);
?></textarea><br>
(The .htaccess file should be located in your html document root)

<p>
<?php

JB_seed_mod_rewrite_for_categories();

function JB_seed_mod_rewrite_for_categories() {

	$sql = "SELECT * FROM `categories` WHERE  `categories`.`form_id`=1 ";
	$result = jb_mysql_query($sql);

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		if (!trim($row['seo_fname'])) {
			
			$fname = JB_utf8_to_html(urldecode(jb_format_url_string($row['category_name'])));
			
			$i=0; $postfix='';

			$sql = "SELECT category_id FROM `categories` WHERE `seo_fname` = '".jb_escape_sql($fname.$postfix.'.html')."' ";
			
			$result2 = jb_mysql_query($sql);
			while (mysql_num_rows($result2)>0) {

				$i++;
				$postfix = '-'.$i;
				
				$sql = "SELECT category_id FROM `categories` WHERE `seo_fname` = '".jb_escape_sql($fname.$postfix.'.html')."' ";
				$result2 = jb_mysql_query($sql);

			}
			
			$fname .= $postfix.'.html';
			

			$sql = "update `categories` set `seo_fname`='".jb_escape_sql($fname)."' where category_id='".$row['category_id']."' ";
			jb_mysql_query($sql);
		}

	}

}

if ($_REQUEST['save']!='') {

	foreach ($_REQUEST as $key=>$val) {

		$parts = explode('_', $key);
		$cat_id = array_pop($parts);
		$var = array_pop($parts);
		if (is_numeric($cat_id)) {

			$sql = "UPDATE categories SET seo_fname='".jb_escape_sql($_REQUEST['file_'.$cat_id])."', seo_title='".jb_escape_sql($_REQUEST['title_'.$cat_id])."', seo_desc='".jb_escape_sql($_REQUEST['desc_'.$cat_id])."', seo_keys='".jb_escape_sql($_REQUEST['keys_'.$cat_id])."' where category_id='".jb_escape_sql($cat_id)."' ";
			
			if ($sql != $old_sql) {
				//echo "$sql<br>";
				JB_mysql_query($sql) or die(mysql_error());
				$old_sql = $sql;
			}

		}

	}

	// update category cache
	JB_cache_del_keys_for_form(1);
	JB_cache_del_keys_for_all_cats(1);
	$JBMarkup->ok_msg('Changes Saved.');

}

?>
<hr>
<h3>mod_rewrite for Categories</h3>
<div style='background-color: #F0FFFF;  border:solid 2px #CCFFFF;margin:10px'>
Important: Please edit the extra fields for the categories below. To go down the category level, click on the parent category link. Some categories may be marked  'ambiguous filename' - please change the file name to a unique file name to avoid clashes. Title, Description and keywords are limited to 255 characters (anything more may clog the search engines!). Click the save button below  to save changes. <b>If Cache is enabled, you will need to refresh your cache by going to Main Config and clicking on Save after saving this form.</b>
</div>

<form method='post' action='mod_rewrite.php'>
<?php
$MODE = "REWRITE";
$JB_CAT_MOD_REWRITE = "YES";
JB_showAllCat($_REQUEST['cat'], 1, 3,  'EN', 1);

?>
<input type='submit' value="Save" name='save' style='font-size: 14pt;' >
</form>
</p>
<?php

JB_admin_footer();

?>