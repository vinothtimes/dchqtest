<?php


###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require ('../config.php');
require (dirname(__FILE__)."/admin_common.php");

require_once ('../include/category.inc.php');

JB_admin_header('Admin -> XML Sitemaps');


?>



<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000; "></div>
<b>[Sitemaps]</b>
	
<hr>	
Your XML Sitemaps URL: <pre><a target="_blank" href="<?php echo JB_BASE_HTTP_PATH.'jb-xml-sitemap.php'?>"><?php echo JB_BASE_HTTP_PATH.'jb-xml-sitemap.php'?><a></pre>
	<hr>
<?php

function JB_init_sitemap_data () {
	$data = array();
	$data['main_priority'] = stripslashes($_REQUEST['main_priority']);
	$data['jobs_priority'] = stripslashes($_REQUEST['jobs_priority']);
	$data['emp_priority'] = stripslashes($_REQUEST['emp_priority']);
	$data['cat_priority'] = stripslashes($_REQUEST['cat_priority']);
	$data['extra_urls'] = stripslashes($_REQUEST['extra_urls']);
	$data['jobs_max'] = stripslashes($_REQUEST['jobs_max']);

	return $data;

}

function JB_save_sitemap_data() {

	$main_priority = stripslashes($_REQUEST['main_priority']);
	$jobs_priority = stripslashes($_REQUEST['jobs_priority']);
	$emp_priority = stripslashes($_REQUEST['emp_priority']);
	$cat_priority = stripslashes($_REQUEST['cat_priority']);
	$extra_urls = stripslashes($_REQUEST['extra_urls']);
	$jobs_max = stripslashes($_REQUEST['jobs_max']);

	$sql = "REPLACE INTO jb_variables (`key`, `val`) VALUES ('SMAP_MAIN_PRIORITY', '".JB_escape_sql($main_priority)."') ";
	JB_mysql_query($sql);
	$sql = "REPLACE INTO jb_variables (`key`, `val`) VALUES ('SMAP_JOBS_PRIORITY', '".JB_escape_sql($jobs_priority)."') ";
	JB_mysql_query($sql);
	$sql = "REPLACE INTO jb_variables (`key`, `val`) VALUES ('SMAP_EMP_PRIORITY', '".JB_escape_sql($emp_priority)."') ";
	JB_mysql_query($sql);
	$sql = "REPLACE INTO jb_variables (`key`, `val`) VALUES ('SMAP_CAT_PRIORITY', '".JB_escape_sql($cat_priority)."') ";
	JB_mysql_query($sql) ;
	$sql = "REPLACE INTO jb_variables (`key`, `val`) VALUES ('SMAP_JOBS_MAX', '".JB_escape_sql($jobs_max)."') ";
	JB_mysql_query($sql) ;

	if ($extra_urls!='') {
	
		$lines = explode ("\n", $extra_urls);

	}

	$sql = "DELETE FROM sitemaps_urls";
	JB_mysql_query($sql);

	if (sizeof ($lines)>0) {

		foreach ($lines as $line) {
			$values = preg_split('#[\s]#', $line);
			if ($values[0]!='') {
				$sql = "REPLACE INTO sitemaps_urls (`url`, `priority`, `changefreq`) VALUES ('".JB_escape_sql($values[0])."', '".JB_escape_sql($values[1])."', '".JB_escape_sql($values[2])."') ";
				//echo $sql.'<br>';
				JB_mysql_query($sql);
			}
		}
	}

}

function JB_load_sitemap_data() {

	$data = array();

	$sql = "SELECT val FROM jb_variables where `key`='SMAP_MAIN_PRIORITY' ";
	$result = JB_mysql_query($sql);
	$row = mysql_fetch_row($result);
	$data['main_priority'] = $row[0];
	if ($data['main_priority']=='') {
		$data['main_priority'] = '0.5';
	}
	$sql = "SELECT val FROM jb_variables where `key`='SMAP_JOBS_PRIORITY' ";
	$result = JB_mysql_query($sql);
	$row = mysql_fetch_row($result);
	$data['jobs_priority'] = $row[0];
		if ($data['jobs_priority']=='') {
		$data['jobs_priority'] = '0.5';
	}
	$sql = "SELECT val FROM jb_variables where `key`='SMAP_JOBS_MAX' ";
	$result = JB_mysql_query($sql);
	$row = mysql_fetch_row($result);
	$data['jobs_max'] = $row[0];
		if ($data['jobs_max']=='') {
		$data['jobs_max'] = '10000';
	}
	$sql = "SELECT val FROM jb_variables where `key`='SMAP_EMP_PRIORITY' ";
	$result = JB_mysql_query($sql);
	$row = mysql_fetch_row($result);
	$data['emp_priority'] = $row[0];
	if ($data['emp_priority']=='') {
		$data['emp_priority'] = '0.5';
	}
	$sql = "SELECT val FROM jb_variables where `key`='SMAP_CAT_PRIORITY' ";
	$result = JB_mysql_query($sql);
	$row = mysql_fetch_row($result);
	$data['cat_priority'] = $row[0];
		if ($data['cat_priority']=='') {
		$data['cat_priority'] = '0.5';
	}

	$sql = "SELECT * FROM sitemaps_urls ";
	$result = JB_mysql_query($sql);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$data['extra_urls'] .= $row['url'].' '.$row['priority'].' '.$row['changefreq']."\n";
	}
	return $data;

}

function JB_validate_sitemap_data() {

	$main_priority = ($_REQUEST['main_priority']);
	$jobs_priority = ($_REQUEST['jobs_priority']);
	$emp_priority = ($_REQUEST['emp_priority']);
	$cat_priority = ($_REQUEST['cat_priority']);
	$extra_urls = ($_REQUEST['extra_urls']);
	$jobs_max = ($_REQUEST['jobs_max']);

	if (($main_priority > 1.0) || ($main_priority < 0.0)) {
		$error .= "- Main Priority out of range, must be from 0.0 to 1.0<br>";
	}

	if (($jobs_priority > 1.0) || ($jobs_priority < 0.0)) {
		$error .= "- Jobs Priority out of range, must be from 0.0 to 1.0<br>";
	}
	if (($emp_priority > 1.0) || ($emp_priority < 0.0)) {
		$error .= "- Employer Priority out of range, must be from 0.0 to 1.0<br>";
	}
	if (($cat_priority > 1.0) || ($cat_priority < 0.0))  {
		$error .= "- Category Priority out of range, must be from 0.0 to 1.0<br>";
	}

	return $error;

}

$data = JB_init_sitemap_data ();

if ($_REQUEST['save']!='') {

	$error = JB_validate_sitemap_data();
	if ($error =='') {
		JB_save_sitemap_data();
	} else {

		$JBMarkup->error_msg('Error Saving Sitemap Settings');
		echo $error;
	}

} else {
	$data = JB_load_sitemap_data();
}

?>
<form method="POST" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>" >	
<table cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" border="0" >
 <tr bgColor="#eaeaea">
<td colspan="2"><b>Sitemaps Settings</b></td>
 </tr>
 	<tr bgColor="#ffffff">
		<td>Main Page</td>
		<td>
		Priority: <input type="text" name="main_priority" value="<?php echo $data['main_priority']; ?>" size="2"> (Decimal from 0.0 to 1.0)</td>
	</tr>
	<tr bgColor="#ffffff">
		<td>Jobs Pages</td>
		<td>Number of URLs to list: <input type="text" name="jobs_max" value="<?php echo $data['jobs_max']; ?>" size="2">
		Priority: <input type="text" name="jobs_priority" value="<?php echo $data['jobs_priority']; ?>" size="2"> (Decimal from 0.0 to 1.0)</td>
	</tr>
	<tr bgColor="#ffffff">
		<td>Employer Profiles</td>
		<td>Priority: <input type="text" name="emp_priority" value="<?php echo $data['emp_priority']; ?>" size="2"> (Decimal from 0.0 to 1.0)</td>
	</tr>
	<tr bgColor="#ffffff">
		<td>Categories</td>
		<td>Priority: <input type="text" name="cat_priority" value="<?php echo $data['cat_priority']; ?>" size="2"> (Decimal from 0.0 to 1.0)</td>
	</tr>
	<tr bgColor="#ffffff">
		<td>Additional URLs</td>
		<td>Enter additional URLs that you want indexed in the following format:<br>
		<i><pre>http://www.example.com/about.htm 0.5 monthly
http://www.example.com/contact.htm 0.4 yearly</pre></i><br> 
		<textarea rows="20" cols="80" name="extra_urls"><?php echo htmlentities($data['extra_urls']); ?></textarea></td>
	</tr>
	<tr bgColor="#ffffff">
		<td colspan="2"><input type="submit" value="Save" name="save"></td>
	</tr>

</table>
</form>
<?php

JB_admin_footer();

?>