<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
define ('JB_HIDE_MYSQL_ERRORS', true);
if (defined('E_DEPRECATED')) {
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
} else {
	error_reporting(E_ALL ^ E_NOTICE);
}
ini_set('memory_limit', '16M');

define ('NO_HOUSE_KEEPING', true);

ini_set('max_execution_time', 100200);
if ($_REQUEST['setup']!='') {
	save_db_config();
} 

require ('../config.php');

$can_connect = false;

// test connection
$conn = @mysql_connect(JB_MYSQL_HOST, JB_MYSQL_USER, JB_MYSQL_PASS) or $error="Cannot Connect";
if ($conn) {
	if (!@mysql_select_db(JB_MYSQL_DB, $conn)) {
		$error="1";
		$can_connect = false;
		 echo "<p><font color='red'><b>Cannot select database.  ".mysql_error()."</b></font></p>";
	} else {

		// connection is fine

		$can_connect = true;
		$error=''; // no error as yet

		// check if database was installed..
		$sql = "SELECT ID FROM `users` limit 1";
		$result = jb_mysql_query($sql) or $error=mysql_error();
		
	}
} elseif ($_REQUEST['jb_db_host']!='') {
	echo "<p><font color='red'><b>Cannot connect to database. ".mysql_error()."</b></font></p>";

}

if ($_REQUEST['install']!='')  {
		install_db (JB_MYSQL_HOST, MYSQL_DB, JB_MYSQL_USER, JB_MYSQL_PASS);
}

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

function does_field_exist($table, $field) {
	global $jb_mysql_link;
	$result = jb_mysql_query("show columns from `".jb_escape_sql($table)."`");
	while ($row = @mysql_fetch_row($result)) {
		//echo $row[0]." ";
		if ($row[0] == $field) {

			return true;

		}

	}

	return false;

}


if ($can_connect) {

		// check if the database is installed

	if (does_field_exist("employers", "can_view_blocked")) {
		echo "Your database is already installed. <font color='red'>Please remove the admin/install.php file from your system.</a></font> If you need to change your settings then please see the Main Config in the <a href='index.php'>Admin section.</a>";
	} else {

		echo "<p>&nbsp;</p><b>I have successfully connected to the database!</b><br>The system will now setup your database tables. Click 'Install Database' to continue.";
		?>

			<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
			<table>
			<tr><td>
			</td><td><input type="hidden" name="jb_db_host" value="<?php echo htmlentities(JB_MYSQL_HOST);?>"></td>
			</tr>
			<tr><td>
			</td><td><input type="hidden" name="jb_db_name" value="<?php echo htmlentities(JB_MYSQL_DB);?>"></td>
			</tr>
			<tr><td>
			</td><td><input type="hidden" name="jb_db_user" value="<?php echo htmlentities(JB_MYSQL_USER);?>"></td>
			</tr><tr><td>
			</td><td><input type="hidden" name="jb_db_pass" value="<?php echo htmlentities(JB_MYSQL_PASS);?>"></td>
			</tr>
			<tr><td colspan="2">
			<input type="Submit" name="install" value="Install Database" ><br>
			</td>
			</tr>
			</table>
			</form>

			<?php

	}


} else {

	echo "<h3>Welcome to Jamit Job Board.</h3> Thank you for choosing our product. <p>";
	?>
<b>Job Board Setup</b><br>
Please fill in the required information carefully.
<?php

		if (!is_writeable("../config.php")) {
			echo "<br><font color='red'><b>Warning:</b> config.php is not writeable. It must have write permissions for installation to succeed.</font>";
		
		}

	if ($_REQUEST['jb_db_host']=='') {
		$_REQUEST['jb_db_host'] = JB_MYSQL_HOST;
	}

	if ($_REQUEST['jb_db_name']=='') {
		$_REQUEST['jb_db_name']= JB_MYSQL_DB;
	}

	if ($_REQUEST['jb_db_user']=='') {
		$_REQUEST['jb_db_user']= JB_MYSQL_USER;
	}

	if ($_REQUEST['jb_db_pass']=='') {
		$_REQUEST['jb_db_pass']= JB_MYSQL_PASS;
	}

	$slash = '/'; # Unix directory seperator




	$host = $_SERVER['SERVER_NAME']; // hostname
	$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
	$http_url = explode ('/', $http_url);
	array_pop($http_url); // get rid of filename
	array_pop($http_url); // get rid of /admin
	$http_url = implode ('/', $http_url);
	
	$file_path = __FILE__; // eg e:/apache/htdocs/ojo/admin/edit_config.php
	
	$file_path = explode (DIRECTORY_SEPARATOR, $file_path);
	array_pop($file_path); // get rid of filename
	array_pop($file_path); // get rid of /admin
	$file_path = implode ($slash, $file_path);
	



 if (JB_BASE_HTTP_PATH=='') {
	$JB_BASE_HTTP_PATH = "http://".$host.$http_url."/";

 } else {
	$JB_BASE_HTTP_PATH = JB_BASE_HTTP_PATH;
 }
 if (JB_IMG_PATH=='') {
	$JB_IMG_PATH = $file_path.$slash."upload_files".$slash."images".$slash;

 } else {
	$JB_IMG_PATH = JB_IMG_PATH;
 }

 if (JB_IMG_HTTP_PATH=='') {
	$JB_IMG_HTTP_PATH = "http://".$host.$http_url."/"."upload_files"."/"."images"."/";

 } else {
	$JB_IMG_HTTP_PATH = JB_IMG_HTTP_PATH;
 }

 if (JB_FILE_PATH=='') {
	$JB_FILE_PATH = $file_path.$slash."upload_files".$slash."docs".$slash;
 } else {
	$JB_FILE_PATH = JB_FILE_PATH;
 }

 if (JB_FILE_HTTP_PATH=='') {
	$JB_FILE_HTTP_PATH = "http://".$host.$http_url."/upload_files/docs/";

 } else {
	$JB_FILE_HTTP_PATH = JB_FILE_HTTP_PATH;
 }

 if (JB_RSS_FEED_PATH=='') {
	$JB_RSS_FEED_PATH = $file_path.$slash."rss.xml";
 } else {
	$JB_RSS_FEED_PATH = JB_RSS_FEED_PATH;
 }

// echo JB_RSS_FEED_PATH;


echo "<p>";



if (strpos(strtoupper(PHP_OS), 'WIN')===false) { 
if (is_writable("../rss.xml")) {
	echo "- rss.xml file is writeable. (OK)<br>";
} else {
	echo "- rss.xml file is not writable. Give write permissions (666) to rss.xml <br>";
}
if (is_writable("../config.php")) {
	echo "- config.php is writeable. (OK)<br>";
} else {
	echo "- Note: config.php is not writable. Give write permissions (666) to config.php if you want to save the changes<br>";
}

if (is_writable("../lang/english.php")) {
	echo "- lang/english.php file is writeable. (OK)<br>";
} else {
	echo "- lang/english.php file is not writable. Give write permissions (666) to lang/english.php <br>";
}

if (is_writable("../cache/")) {
	echo "- cache/ directory is writeable. (OK)<br>";
} else {
	echo "- cache/ directory is not writable. Give write permissions (777) to cache/ directory<br>";
}

if (is_writable("../upload_files/docs/")) {
	echo "- upload_files/docs/ directory is writeable. (OK)<br>";
} else {
	echo "- upload_files/docs/ directory is not writable. Give write permissions (777) to upload_files/docs/ directory<br>";
}
//require ('../config.php');
if (is_writable("../upload_files/docs/temp/")) {
	echo "- upload_files/docs/temp/ directory is writeable. (OK)<br>";
} else {
	echo "- upload_files/docs/temp/ is not writable. Give write permissions (777) to upload_files/docs/temp/ directory<br>";
}

if (is_writable("../upload_files/images/")) {
	echo "- upload_files/images/ directory is writeable. (OK)<br>";
} else {
	echo "- upload_files/images/ directory is not writable. Give write permissions (777) to upload_files/images/ directory<br>";
}

if (is_writable("../upload_files/images/thumbs/")) {
	echo "- upload_files/images/thumbs/ directory is writeable. (OK)<br>";
} else {
	echo "- upload_files/images/thumbs/ directory is not writable. Give write permissions (777) to upload_files/images/thumbs/ directory<br>";
}

if (function_exists('ftp_connect()')) {

	

?>
<p>
Set permissions using FTP:<br>
<form method="post" action="install.php">
FTP Host: <input type="text" name="ftp_host" value="localhost"><br>
User: <input type="text" name="ftp_user" value="<?php echo htmlentities($_REQUEST['ftp_user']);?>"><br>
Pass: <input type="text" name="ftp_pass" value="<?php echo htmlentities($_REQUEST['ftp_pass']);?>"><br>
<input type="submit"  name="set_ftp" value="GO"><br>
</p>
<?php

}

} else {

?>
<b>Please make sure that the script can write to the following files</b><br>
rss.xml<br>
config.php<br>
lang/english.php<br>
<b>...and the following directories:</b><br>
cache/<br>
upload_files/docs/<br>
upload_files/docs/temp/<br>
upload_files/images/<br>
upload_files/images/thumbs/<br>

<?php

}





echo "</p>";
?>
<form method="post">
  <table border="0" cellpadding="5" cellspacing="2" style="border-style:groove" width="100%" bgcolor="#FFFFFF">
  <tr>
      <td colspan="2" bgcolor="#e6f2ea">
      <p ><font face="Verdana" size="1"><b>MySQL Database Server</b><br></font></td>
    </tr>
<tr> <td width="20%" bgcolor="#e6f2ea"><font face="Verdana" size="1">
Database Host:</font></td><td bgcolor="#e6f2ea"><font face="Verdana" size="1"><input type="text" name="jb_db_host" value="<?php echo htmlentities($_REQUEST['jb_db_host'])?>"></font></td>
</tr>
<tr> <td width="20%" bgcolor="#e6f2ea"><font face="Verdana" size="1">
Database Name:</font></td><td bgcolor="#e6f2ea"><font face="Verdana" size="1"><input type="text" name="jb_db_name" value="<?php echo htmlentities($_REQUEST['jb_db_name']);?>"></font></td>
</tr>
<tr> <td width="20%" bgcolor="#e6f2ea"><font face="Verdana" size="1">
Database Username:</font></td><td bgcolor="#e6f2ea"><font face="Verdana" size="1"><input type="text" name="jb_db_user" value="<?php echo htmlentities($_REQUEST['jb_db_user'])?>"></font></td>
</tr><tr> <td width="20%" bgcolor="#e6f2ea"><font face="Verdana" size="1">
Database Password:</font></td><td bgcolor="#e6f2ea"><font face="Verdana" size="1"><input type="text" name="jb_db_pass" value="<?php echo htmlentities($_REQUEST['jb_db_pass'])?>"></font></td>
</tr>



<?php





?>



    <tr>
      <td colspan="2" bgcolor="#e6f2ea">
      <p ><font face="Verdana" size="1"><b>Paths and Locations</b><br></font></td>
    </tr>
    <tr>
      <td width="20%" bgcolor="#e6f2ea"><font face="Verdana" size="1">Site's HTTP URL (address)</font></td>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="base_http_path" size="70" value="<?php echo htmlentities($JB_BASE_HTTP_PATH); ?>"><br>Suggested: <b>http://<?php echo $host.$http_url."/"; ?></b></font></td>
    </tr>
  

	<tr>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">Images 
      Path</font></td>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="img_path" size="70" value="<?php echo htmlentities($JB_IMG_PATH); ?>"><br>Suggested: <b><?php echo $file_path.$slash."upload_files".$slash."images".$slash."</b>";if (!file_exists($JB_IMG_PATH)) { echo "<br><font color='red'>Warning:</font> ".$JB_IMG_PATH." does not exist"; } elseif (!is_writable($JB_IMG_PATH)) { echo "<br><font color='red'>Warning:</font> ".$JB_IMG_PATH." is not writable. Please give it permission to be written."; } if (!file_exists($JB_IMG_PATH."thumbs".$slash)) { echo "<br><font color='red'>Warning:</font> ".$JB_IMG_PATH."thumbs".$slash." does not exist"; } elseif (!is_writable($JB_IMG_PATH."thumbs".$slash)) { echo "<br><font color='red'>Warning:</font> ".$JB_IMG_PATH."thumbs".$slash." is not writable. Please give it permission to be written."; } ?></font></td>
    </tr>
	<tr>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">Images 
      URL</font></td>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="img_http_path" size="70" value="<?php echo htmlentities($JB_IMG_HTTP_PATH); ?>"><br>Suggested: <b><?php echo "http://".$host.$http_url."/upload_files/images/"; ?></b></font></td>
    </tr>
    <tr>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">Files 
      Path</font></td>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="file_path" size="70" value="<?php echo htmlentities($JB_FILE_PATH); ?>"><br>Suggested: <b><?php echo $file_path."/upload_files/docs/</b>"; if (!file_exists($JB_FILE_PATH)) { echo "<br><font color='red'>Warning:</font> ".$JB_FILE_PATH." does not exist"; } elseif (!is_writable($JB_FILE_PATH)) { echo "<br><font color='red'>Warning:</font> ".$JB_FILE_PATH." is not writable. Please give it permission to be written."; } ?></font></td>
    </tr>
	 <tr>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">Files 
      URL</font></td>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="file_http_path" size="70" value="<?php echo htmlentities($JB_FILE_HTTP_PATH); ?>"><br>Suggested: <b><?php echo "http://".$host.$http_url."/upload_files/docs/"; ?></b></font></td>
    </tr>
	<input type="hidden" name="im_path" size="49" value="<?php echo htmlentities(JB_IM_PATH); ?>">
	<input type="hidden" size='3' name='img_max_width' value=<?php echo JB_IMG_MAX_WIDTH; ?> >

    <tr>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">Path & Filename to 
      RSS Feed XML file</font></td>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="rss_feed_path" size="49" value="<?php echo htmlentities($JB_RSS_FEED_PATH); ?>"><br>Suggested: <b><?php echo $file_path."/rss.xml </b> "; if (!file_exists($JB_RSS_FEED_PATH)) { echo "<br><font color='red'>Warning:</font> ".$JB_RSS_FEED_PATH." does not exist"; } elseif (!is_writable($JB_RSS_FEED_PATH)) { echo "<br><font color='red'>Warning:</font> ".$JB_RSS_FEED_PATH." is not writable. Please give it permission to be written."; }?></font></td>
    </tr>
	
	<tr>
	<td colspan="2">
	<font face="Verdana" size="1">
NOTES<br>
 - 'Images Path', 'Files Path' are the full path names on the server, <font color="red">including a slash at the end</font><br>
 - The Site's HTTP URL must include a<font color="red"> slash at the end</font><br>
 - Use the Suggested settings unless you are sure otherwise<br>
 Also, don't forget to delete install.php from the server after installation. <br>
 </font>
	</td>

	</tr>
	<tr><td colspan="2">
<input type="Submit" name="setup" value="Continue" ><br>
</td>
</tr>
</table>

</form>


<?php

}


###################################
function query_parser($q){
  
   $queries = preg_split("/;;;/", $q);


   return $queries;
}
#############################################
function multiple_query($q){
	global $jb_mysql_link;
   $queries=query_parser($q);
   $n=count($queries);
   $results=array();

   for($i=0;$i<$n;$i++)
       $results[$i]=array(
           jb_mysql_query($queries[$i]),
           mysql_errno(),
           mysql_error(),
			$queries[$i]
       );

   return $results;
}

##################################################

function save_db_config() {
//echo "Saving config...";
	$filename = '../config.php';
	
	$contents = file_get_contents($filename);
	
	$handle  = fopen($filename, "w");

	
	$contents = preg_replace ( "#define\('JB_MYSQL_HOST'.*[^\\\]'\)+;#", "define('JB_MYSQL_HOST', '".str_replace("\\", '\\\\', trim($_REQUEST['jb_db_host']))."');", $contents) ;
	
	$contents = preg_replace ( "#define\('JB_MYSQL_USER'.*[^\\\]'\)+;#", "define('JB_MYSQL_USER', '".str_replace("\\", '\\\\', trim($_REQUEST['jb_db_user']))."');", $contents) ;
	
	$contents = preg_replace ( "#define\('JB_MYSQL_PASS'.*[^\\\]'\)+;#", "define('JB_MYSQL_PASS', '". str_replace("\\", '\\\\', trim($_REQUEST['jb_db_pass']))."');", $contents) ;
	
	$contents = preg_replace ( "#define\('JB_MYSQL_DB'.*[^\\\]'\)+;#", "define('JB_MYSQL_DB', '". str_replace("\\", '\\\\', trim($_REQUEST['jb_db_name']))."');", $contents) ;

	$contents = preg_replace ( "#define\('JB_BASE_HTTP_PATH'.*[^\\\]'\)+;#", "define('JB_BASE_HTTP_PATH', '". str_replace("\\", '\\\\', trim($_REQUEST['base_http_path']))."');", $contents) ;

	$contents = preg_replace ( "#define\('JB_RSS_FEED_PATH'.*[^\\\]'\)+;#", "define('JB_RSS_FEED_PATH', '". str_replace("\\", '\\\\', trim($_REQUEST['rss_feed_path']))."');", $contents) ;

	$contents = preg_replace ( "#define\('JB_IMG_PATH'.*[^\\\]'\)+;#", "define('JB_IMG_PATH', '". str_replace("\\", '\\\\', trim($_REQUEST['img_path']))."');", $contents) ;

	$contents = preg_replace ( "#define\('JB_FILE_PATH'.*[^\\\]'\)+;#", "define('JB_FILE_PATH', '". str_replace("\\", '\\\\', trim($_REQUEST['file_path']))."');", $contents) ;

	$contents = preg_replace ( "#define\('JB_IM_PATH'.*[^\\\]'\)+;#", "define('JB_IM_PATH', '". str_replace("\\", '\\\\', trim($_REQUEST['im_path']))."');", $contents) ;

	$contents = preg_replace ( "#define\('JB_IMG_HTTP_PATH'.*[^\\\]'\)+;#", "define('JB_IMG_HTTP_PATH', '". str_replace("\\", '\\\\', trim($_REQUEST['img_http_path']))."');", $contents) ;

	$contents = preg_replace ( "#define\('JB_FILE_HTTP_PATH'.*[^\\\]'\)+;#", "define('JB_FILE_HTTP_PATH', '". str_replace("\\", '\\\\', trim($_REQUEST['file_http_path']))."');", $contents) ;

	$contents = preg_replace ( "#define\('JB_IMG_MAX_WIDTH'.*[^\\\]'\)+;#", "define('JB_IMG_MAX_WIDTH', '". str_replace("\\", '\\\\', trim($_REQUEST['img_max_width']))."');", $contents) ;

	fwrite($handle , $contents, strlen($contents));

	fclose ($handle);
	

}

###############################################################

function install_db ($jb_db_host, $jb_db_name, $jb_db_user, $jb_db_pass) {


$sql = "

CREATE TABLE `applications` (
  `app_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `post_id` int(11) NOT NULL default '0',
  `app_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `cover_letter` text NOT NULL,
  `employer_id` int(11) NOT NULL default '0',
  `employer_name` varchar(255) NOT NULL default '',
  `data1` varchar(255) NOT NULL default '',
  `data2` varchar(255) NOT NULL default '',
  `data3` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`app_id`)
)  AUTO_INCREMENT=1 ;;;


CREATE TABLE `cat_name_translations` (
  `category_id` int(11) NOT NULL default '0',
  `lang` char(2) NOT NULL default '',
  `category_name` text NOT NULL,
  PRIMARY KEY  (`category_id`,`lang`),
  KEY `category_id` (`category_id`)
) ;;;

 

INSERT INTO `cat_name_translations` VALUES (0, '', '');;;
INSERT INTO `cat_name_translations` VALUES (0, 'CN', '');;;
INSERT INTO `cat_name_translations` VALUES (0, 'EN', '');;;
INSERT INTO `cat_name_translations` VALUES (0, 'ES', '');;;
INSERT INTO `cat_name_translations` VALUES (0, 'KO', '');;;
INSERT INTO `cat_name_translations` VALUES (0, 'PL', '');;;
INSERT INTO `cat_name_translations` VALUES (1, '', 'Location');;;
INSERT INTO `cat_name_translations` VALUES (1, 'CN', 'Location');;;
INSERT INTO `cat_name_translations` VALUES (1, 'EN', 'Location');;;
INSERT INTO `cat_name_translations` VALUES (1, 'ES', 'Location');;;
INSERT INTO `cat_name_translations` VALUES (1, 'KO', '&#50948;&#52824;');;;
INSERT INTO `cat_name_translations` VALUES (1, 'PL', 'Location');;;
INSERT INTO `cat_name_translations` VALUES (20, '', 'Job Type');;;
INSERT INTO `cat_name_translations` VALUES (20, 'CN', 'Job Type');;;
INSERT INTO `cat_name_translations` VALUES (20, 'EN', 'Job Type');;;
INSERT INTO `cat_name_translations` VALUES (20, 'ES', 'Job Type');;;
INSERT INTO `cat_name_translations` VALUES (20, 'KO', 'Job Type');;;
INSERT INTO `cat_name_translations` VALUES (20, 'PL', 'Job Type');;;
INSERT INTO `cat_name_translations` VALUES (21, '', 'Part-time');;;
INSERT INTO `cat_name_translations` VALUES (21, 'CN', 'Part-time');;;
INSERT INTO `cat_name_translations` VALUES (21, 'EN', 'Part-time');;;
INSERT INTO `cat_name_translations` VALUES (21, 'ES', 'Part-time');;;
INSERT INTO `cat_name_translations` VALUES (21, 'KO', 'Part-time');;;
INSERT INTO `cat_name_translations` VALUES (21, 'PL', 'Part-time');;;
INSERT INTO `cat_name_translations` VALUES (22, '', 'Full-time');;;
INSERT INTO `cat_name_translations` VALUES (22, 'CN', 'Full-time');;;
INSERT INTO `cat_name_translations` VALUES (22, 'EN', 'Full-time');;;
INSERT INTO `cat_name_translations` VALUES (22, 'ES', 'Full-time');;;
INSERT INTO `cat_name_translations` VALUES (22, 'KO', 'Full-time');;;
INSERT INTO `cat_name_translations` VALUES (22, 'PL', 'Full-time');;;
INSERT INTO `cat_name_translations` VALUES (23, '', 'Job Classification');;;
INSERT INTO `cat_name_translations` VALUES (23, 'CN', 'Job Classification');;;
INSERT INTO `cat_name_translations` VALUES (23, 'EN', 'Job Classification');;;
INSERT INTO `cat_name_translations` VALUES (23, 'ES', 'Job Classification');;;
INSERT INTO `cat_name_translations` VALUES (23, 'KO', 'Job Classification');;;
INSERT INTO `cat_name_translations` VALUES (23, 'PL', 'Job Classification');;;
INSERT INTO `cat_name_translations` VALUES (26, '', 'Part-time & Full-time');;;
INSERT INTO `cat_name_translations` VALUES (26, 'CN', 'Part-time & Full-time');;;
INSERT INTO `cat_name_translations` VALUES (26, 'EN', 'Part-time & Full-time');;;
INSERT INTO `cat_name_translations` VALUES (26, 'ES', 'Part-time & Full-time');;;
INSERT INTO `cat_name_translations` VALUES (26, 'KO', 'Part-time & Full-time');;;
INSERT INTO `cat_name_translations` VALUES (26, 'PL', 'Part-time & Full-time');;;
INSERT INTO `cat_name_translations` VALUES (34, 'CN', 'Nationalit');;;
INSERT INTO `cat_name_translations` VALUES (34, 'EN', 'Nationalit');;;
INSERT INTO `cat_name_translations` VALUES (34, 'ES', 'Nationalit');;;
INSERT INTO `cat_name_translations` VALUES (34, 'KO', '&#54620;&#44397;');;;
INSERT INTO `cat_name_translations` VALUES (34, 'PL', 'Nationalit');;;
INSERT INTO `cat_name_translations` VALUES (35, 'CN', 'American');;;
INSERT INTO `cat_name_translations` VALUES (35, 'EN', 'American');;;
INSERT INTO `cat_name_translations` VALUES (35, 'ES', 'American');;;
INSERT INTO `cat_name_translations` VALUES (35, 'KO', 'American');;;
INSERT INTO `cat_name_translations` VALUES (35, 'PL', 'American');;;
INSERT INTO `cat_name_translations` VALUES (36, 'CN', 'Canadian');;;
INSERT INTO `cat_name_translations` VALUES (36, 'EN', 'Canadian');;;
INSERT INTO `cat_name_translations` VALUES (36, 'ES', 'Canadian');;;
INSERT INTO `cat_name_translations` VALUES (36, 'KO', 'Canadian');;;
INSERT INTO `cat_name_translations` VALUES (36, 'PL', 'Canadian');;;
INSERT INTO `cat_name_translations` VALUES (37, 'CN', 'Australian');;;
INSERT INTO `cat_name_translations` VALUES (37, 'EN', 'Australian');;;
INSERT INTO `cat_name_translations` VALUES (37, 'ES', 'Australian');;;
INSERT INTO `cat_name_translations` VALUES (37, 'KO', 'Australian');;;
INSERT INTO `cat_name_translations` VALUES (37, 'PL', 'Australian');;;
INSERT INTO `cat_name_translations` VALUES (38, 'CN', 'New Zealander');;;
INSERT INTO `cat_name_translations` VALUES (38, 'EN', 'New Zealander');;;
INSERT INTO `cat_name_translations` VALUES (38, 'ES', 'New Zealander');;;
INSERT INTO `cat_name_translations` VALUES (38, 'KO', 'New Zealander');;;
INSERT INTO `cat_name_translations` VALUES (38, 'PL', 'New Zealander');;;
INSERT INTO `cat_name_translations` VALUES (39, 'CN', 'South African');;;
INSERT INTO `cat_name_translations` VALUES (39, 'EN', 'South African');;;
INSERT INTO `cat_name_translations` VALUES (39, 'ES', 'South African');;;
INSERT INTO `cat_name_translations` VALUES (39, 'KO', 'South African');;;
INSERT INTO `cat_name_translations` VALUES (39, 'PL', 'South African');;;
INSERT INTO `cat_name_translations` VALUES (40, 'CN', 'English');;;
INSERT INTO `cat_name_translations` VALUES (40, 'EN', 'English');;;
INSERT INTO `cat_name_translations` VALUES (40, 'ES', 'English');;;
INSERT INTO `cat_name_translations` VALUES (40, 'KO', 'English');;;
INSERT INTO `cat_name_translations` VALUES (40, 'PL', 'English');;;
INSERT INTO `cat_name_translations` VALUES (41, 'CN', 'Irish');;;
INSERT INTO `cat_name_translations` VALUES (41, 'EN', 'Irish');;;
INSERT INTO `cat_name_translations` VALUES (41, 'ES', 'Irish');;;
INSERT INTO `cat_name_translations` VALUES (41, 'KO', 'Irish');;;
INSERT INTO `cat_name_translations` VALUES (41, 'PL', 'Irish');;;
INSERT INTO `cat_name_translations` VALUES (42, 'CN', 'Other');;;
INSERT INTO `cat_name_translations` VALUES (42, 'EN', 'Other');;;
INSERT INTO `cat_name_translations` VALUES (42, 'ES', 'Other');;;
INSERT INTO `cat_name_translations` VALUES (42, 'KO', 'Other');;;
INSERT INTO `cat_name_translations` VALUES (42, 'PL', 'Other');;;
INSERT INTO `cat_name_translations` VALUES (65, 'CN', 'New York');;;
INSERT INTO `cat_name_translations` VALUES (65, 'EN', 'New York City');;;
INSERT INTO `cat_name_translations` VALUES (65, 'ES', 'New York');;;
INSERT INTO `cat_name_translations` VALUES (65, 'KO', 'New York');;;
INSERT INTO `cat_name_translations` VALUES (65, 'PL', 'New York');;;
INSERT INTO `cat_name_translations` VALUES (66, 'CN', 'Dallas');;;
INSERT INTO `cat_name_translations` VALUES (66, 'EN', 'Dallas');;;
INSERT INTO `cat_name_translations` VALUES (66, 'ES', 'Dallas');;;
INSERT INTO `cat_name_translations` VALUES (66, 'KO', 'Dallas');;;
INSERT INTO `cat_name_translations` VALUES (66, 'PL', 'Dallas');;;
INSERT INTO `cat_name_translations` VALUES (67, 'CN', 'Atlanta');;;
INSERT INTO `cat_name_translations` VALUES (67, 'EN', 'Atlanta');;;
INSERT INTO `cat_name_translations` VALUES (67, 'ES', 'Atlanta');;;
INSERT INTO `cat_name_translations` VALUES (67, 'KO', 'Atlanta');;;
INSERT INTO `cat_name_translations` VALUES (67, 'PL', 'Atlanta');;;
INSERT INTO `cat_name_translations` VALUES (68, 'CN', 'Denver');;;
INSERT INTO `cat_name_translations` VALUES (68, 'EN', 'Denver');;;
INSERT INTO `cat_name_translations` VALUES (68, 'ES', 'Denver');;;
INSERT INTO `cat_name_translations` VALUES (68, 'KO', 'Denver');;;
INSERT INTO `cat_name_translations` VALUES (68, 'PL', 'Denver');;;
INSERT INTO `cat_name_translations` VALUES (69, 'CN', 'Detroit');;;
INSERT INTO `cat_name_translations` VALUES (69, 'EN', 'Detroit');;;
INSERT INTO `cat_name_translations` VALUES (69, 'ES', 'Detroit');;;
INSERT INTO `cat_name_translations` VALUES (69, 'KO', 'Detroit');;;
INSERT INTO `cat_name_translations` VALUES (69, 'PL', 'Detroit');;;
INSERT INTO `cat_name_translations` VALUES (70, 'CN', 'Honolulu');;;
INSERT INTO `cat_name_translations` VALUES (70, 'EN', 'Honolulu');;;
INSERT INTO `cat_name_translations` VALUES (70, 'ES', 'Honolulu');;;
INSERT INTO `cat_name_translations` VALUES (70, 'KO', 'Honolulu');;;
INSERT INTO `cat_name_translations` VALUES (70, 'PL', 'Honolulu');;;
INSERT INTO `cat_name_translations` VALUES (71, 'CN', 'Las Vegas');;;
INSERT INTO `cat_name_translations` VALUES (71, 'EN', 'Las Vegas');;;
INSERT INTO `cat_name_translations` VALUES (71, 'ES', 'Las Vegas');;;
INSERT INTO `cat_name_translations` VALUES (71, 'KO', 'Las Vegas');;;
INSERT INTO `cat_name_translations` VALUES (71, 'PL', 'Las Vegas');;;
INSERT INTO `cat_name_translations` VALUES (72, 'CN', 'Kansas City');;;
INSERT INTO `cat_name_translations` VALUES (72, 'EN', 'Kansas City');;;
INSERT INTO `cat_name_translations` VALUES (72, 'ES', 'Kansas City');;;
INSERT INTO `cat_name_translations` VALUES (72, 'KO', 'Kansas City');;;
INSERT INTO `cat_name_translations` VALUES (72, 'PL', 'Kansas City');;;
INSERT INTO `cat_name_translations` VALUES (73, 'CN', 'San Francisco');;;
INSERT INTO `cat_name_translations` VALUES (73, 'EN', 'San Francisco');;;
INSERT INTO `cat_name_translations` VALUES (73, 'ES', 'San Francisco');;;
INSERT INTO `cat_name_translations` VALUES (73, 'KO', 'San Francisco');;;
INSERT INTO `cat_name_translations` VALUES (73, 'PL', 'San Francisco');;;
INSERT INTO `cat_name_translations` VALUES (74, 'CN', 'Miami');;;
INSERT INTO `cat_name_translations` VALUES (74, 'EN', 'Miami');;;
INSERT INTO `cat_name_translations` VALUES (74, 'ES', 'Miami');;;
INSERT INTO `cat_name_translations` VALUES (74, 'KO', 'Miami');;;
INSERT INTO `cat_name_translations` VALUES (74, 'PL', 'Miami');;;
INSERT INTO `cat_name_translations` VALUES (75, 'CN', 'Boston');;;
INSERT INTO `cat_name_translations` VALUES (75, 'EN', 'Boston');;;
INSERT INTO `cat_name_translations` VALUES (75, 'ES', 'Boston');;;
INSERT INTO `cat_name_translations` VALUES (75, 'KO', 'Boston');;;
INSERT INTO `cat_name_translations` VALUES (75, 'PL', 'Boston');;;
INSERT INTO `cat_name_translations` VALUES (76, 'CN', 'Los Angeles');;;
INSERT INTO `cat_name_translations` VALUES (76, 'EN', 'Los Angeles');;;
INSERT INTO `cat_name_translations` VALUES (76, 'ES', 'Los Angeles');;;
INSERT INTO `cat_name_translations` VALUES (76, 'KO', 'Los Angeles');;;
INSERT INTO `cat_name_translations` VALUES (76, 'PL', 'Los Angeles');;;
INSERT INTO `cat_name_translations` VALUES (77, 'CN', 'Washington, D.C.');;;
INSERT INTO `cat_name_translations` VALUES (77, 'EN', 'Washington, D.C.');;;
INSERT INTO `cat_name_translations` VALUES (77, 'ES', 'Washington, D.C.');;;
INSERT INTO `cat_name_translations` VALUES (77, 'KO', 'Washington, D.C.');;;
INSERT INTO `cat_name_translations` VALUES (77, 'PL', 'Washington, D.C.');;;
INSERT INTO `cat_name_translations` VALUES (78, 'CN', 'Education & Training');;;
INSERT INTO `cat_name_translations` VALUES (78, 'EN', 'Education & Training');;;
INSERT INTO `cat_name_translations` VALUES (78, 'ES', 'Education & Training');;;
INSERT INTO `cat_name_translations` VALUES (78, 'KO', 'Education & Training');;;
INSERT INTO `cat_name_translations` VALUES (78, 'PL', 'Education & Training');;;
INSERT INTO `cat_name_translations` VALUES (79, 'CN', 'Manufacturing/Operations');;;
INSERT INTO `cat_name_translations` VALUES (79, 'EN', 'Manufacturing/Operations');;;
INSERT INTO `cat_name_translations` VALUES (79, 'ES', 'Manufacturing/Operations');;;
INSERT INTO `cat_name_translations` VALUES (79, 'KO', 'Manufacturing/Operations');;;
INSERT INTO `cat_name_translations` VALUES (79, 'PL', 'Manufacturing/Operations');;;
INSERT INTO `cat_name_translations` VALUES (80, 'CN', 'Retail');;;
INSERT INTO `cat_name_translations` VALUES (80, 'EN', 'Retail');;;
INSERT INTO `cat_name_translations` VALUES (80, 'ES', 'Retail');;;
INSERT INTO `cat_name_translations` VALUES (80, 'KO', 'Retail');;;
INSERT INTO `cat_name_translations` VALUES (80, 'PL', 'Retail');;;
INSERT INTO `cat_name_translations` VALUES (81, 'CN', 'Healthcare & Community');;;
INSERT INTO `cat_name_translations` VALUES (81, 'EN', 'Healthcare & Community');;;
INSERT INTO `cat_name_translations` VALUES (81, 'ES', 'Healthcare & Community');;;
INSERT INTO `cat_name_translations` VALUES (81, 'KO', 'Healthcare & Community');;;
INSERT INTO `cat_name_translations` VALUES (81, 'PL', 'Healthcare & Community');;;
INSERT INTO `cat_name_translations` VALUES (82, 'CN', 'Accounting');;;
INSERT INTO `cat_name_translations` VALUES (82, 'EN', 'Accounting');;;
INSERT INTO `cat_name_translations` VALUES (82, 'ES', 'Accounting');;;
INSERT INTO `cat_name_translations` VALUES (82, 'KO', 'Accounting');;;
INSERT INTO `cat_name_translations` VALUES (82, 'PL', 'Accounting');;;
INSERT INTO `cat_name_translations` VALUES (83, 'CN', 'I.T. & T.');;;
INSERT INTO `cat_name_translations` VALUES (83, 'EN', 'I.T. & T.');;;
INSERT INTO `cat_name_translations` VALUES (83, 'ES', 'I.T. & T.');;;
INSERT INTO `cat_name_translations` VALUES (83, 'KO', 'I.T. & T. ');;;
INSERT INTO `cat_name_translations` VALUES (83, 'PL', 'I.T. & T.');;;
INSERT INTO `cat_name_translations` VALUES (84, 'CN', 'Sales & Marketing');;;
INSERT INTO `cat_name_translations` VALUES (84, 'EN', 'Sales & Marketing');;;
INSERT INTO `cat_name_translations` VALUES (84, 'ES', 'Sales & Marketing');;;
INSERT INTO `cat_name_translations` VALUES (84, 'KO', 'Sales & Marketing');;;
INSERT INTO `cat_name_translations` VALUES (84, 'PL', 'Sales & Marketing');;;
INSERT INTO `cat_name_translations` VALUES (85, 'CN', 'Legal');;;
INSERT INTO `cat_name_translations` VALUES (85, 'EN', 'Legal');;;
INSERT INTO `cat_name_translations` VALUES (85, 'ES', 'Legal');;;
INSERT INTO `cat_name_translations` VALUES (85, 'KO', 'Legal ');;;
INSERT INTO `cat_name_translations` VALUES (85, 'PL', 'Legal');;;
INSERT INTO `cat_name_translations` VALUES (86, 'CN', 'Hospitality & Tourism');;;
INSERT INTO `cat_name_translations` VALUES (86, 'EN', 'Hospitality & Tourism');;;
INSERT INTO `cat_name_translations` VALUES (86, 'ES', 'Hospitality & Tourism');;;
INSERT INTO `cat_name_translations` VALUES (86, 'KO', 'Hospitality & Tourism');;;
INSERT INTO `cat_name_translations` VALUES (86, 'PL', 'Hospitality & Tourism');;;
INSERT INTO `cat_name_translations` VALUES (87, 'CN', 'Engineering');;;
INSERT INTO `cat_name_translations` VALUES (87, 'EN', 'Engineering');;;
INSERT INTO `cat_name_translations` VALUES (87, 'ES', 'Engineering');;;
INSERT INTO `cat_name_translations` VALUES (87, 'KO', 'Engineering');;;
INSERT INTO `cat_name_translations` VALUES (87, 'PL', 'Engineering');;;
INSERT INTO `cat_name_translations` VALUES (88, 'CN', 'Administration');;;
INSERT INTO `cat_name_translations` VALUES (88, 'EN', 'Administration');;;
INSERT INTO `cat_name_translations` VALUES (88, 'ES', 'Administration');;;
INSERT INTO `cat_name_translations` VALUES (88, 'KO', 'Administration ');;;
INSERT INTO `cat_name_translations` VALUES (88, 'PL', 'Administration');;;
INSERT INTO `cat_name_translations` VALUES (89, 'CN', 'Construction');;;
INSERT INTO `cat_name_translations` VALUES (89, 'EN', 'Construction');;;
INSERT INTO `cat_name_translations` VALUES (89, 'ES', 'Construction');;;
INSERT INTO `cat_name_translations` VALUES (89, 'KO', 'Construction ');;;
INSERT INTO `cat_name_translations` VALUES (89, 'PL', 'Construction');;;

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL default '0',
  `category_name` varchar(255) NOT NULL default '',
  `parent_category_id` int(11) NOT NULL default '0',
  `obj_count` int(11) NOT NULL default '0',
  `form_id` int(11) NOT NULL default '0',
  `allow_records` set('Y','N') NOT NULL default 'Y',
  `list_order` smallint(6) NOT NULL default '1',
  `search_set` text NOT NULL,
  `seo_fname` varchar(100) default NULL,
  `seo_title` varchar(255) default NULL,
  `seo_desc` varchar(255) default NULL,
  `seo_keys` varchar(255) default NULL,
  `has_child` SET( 'Y', 'N' ) NULL,
  PRIMARY KEY  (`category_id`),
  KEY `parent_category_id` (`parent_category_id`),
  KEY `seo_fname` (`seo_fname`)
) ;;;
 

INSERT INTO `categories` VALUES (1, 'Location', 0, 1, 1, 'Y', 1, '1,67,75,66,68,69,70,72,71,76,74,65,73,77','','','','', NULL);;;
INSERT INTO `categories` VALUES (20, 'Job Type', 0, 1, 1, 'N', 1, '20,22,21,26','','','','', NULL);;;
INSERT INTO `categories` VALUES (21, 'Part-time', 20, 0, 1, 'Y', 1, '21','','','','', NULL);;;
INSERT INTO `categories` VALUES (22, 'Full-time', 20, 1, 1, 'Y', 1, '22','','','','', NULL);;;
INSERT INTO `categories` VALUES (23, 'Job Classification', 0, 1, 1, 'Y', 1, '23,82,88,89,78,87,81,86,83,85,79,80,84,90,91','','','','', NULL);;;
INSERT INTO `categories` VALUES (26, 'Part-time & Full-time', 20, 0, 1, 'Y', 1, '26','','','','', NULL);;;
INSERT INTO `categories` VALUES (34, 'Nationalit', 0, 0, 2, 'Y', 1, '34,35,37,36,40,41,38,42,39','','','','', NULL);;;
INSERT INTO `categories` VALUES (35, 'American', 34, 0, 2, 'Y', 1, '35','','','','', NULL);;;
INSERT INTO `categories` VALUES (36, 'Canadian', 34, 0, 2, 'Y', 1, '36','','','','', NULL);;;
INSERT INTO `categories` VALUES (37, 'Australian', 34, 0, 2, 'Y', 1, '37','','','','', NULL);;;
INSERT INTO `categories` VALUES (38, 'New Zealander', 34, 0, 2, 'Y', 1, '38','','','','', NULL);;;
INSERT INTO `categories` VALUES (39, 'South African', 34, 0, 2, 'Y', 1, '39','','','','', NULL);;;
INSERT INTO `categories` VALUES (40, 'English', 34, 0, 2, 'Y', 1, '40','','','','', NULL);;;
INSERT INTO `categories` VALUES (41, 'Irish', 34, 0, 2, 'Y', 1, '41','','','','', NULL);;;
INSERT INTO `categories` VALUES (42, 'Other', 34, 0, 2, 'Y', 1, '42','','','','', NULL);;;
INSERT INTO `categories` VALUES (65, 'New York', 1, 0, 1, 'Y', 1, '65','','','','', NULL);;;
INSERT INTO `categories` VALUES (66, 'Dallas', 1, 0, 1, 'Y', 1, '66','','','','', NULL);;;
INSERT INTO `categories` VALUES (67, 'Atlanta', 1, 1, 1, 'Y', 1, '67','','','','', NULL);;;
INSERT INTO `categories` VALUES (68, 'Denver', 1, 0, 1, 'Y', 1, '68','','','','', NULL);;;
INSERT INTO `categories` VALUES (69, 'Detroit', 1, 0, 1, 'Y', 1, '69','','','','', NULL);;;
INSERT INTO `categories` VALUES (70, 'Honolulu', 1, 0, 1, 'Y', 1, '70','','','','', NULL);;;
INSERT INTO `categories` VALUES (71, 'Las Vegas', 1, 0, 1, 'Y', 1, '71','','','','', NULL);;;
INSERT INTO `categories` VALUES (72, 'Kansas City', 1, 0, 1, 'Y', 1, '72','','','','', NULL);;;
INSERT INTO `categories` VALUES (73, 'San Francisco', 1, 0, 1, 'Y', 1, '73','','','','', NULL);;;
INSERT INTO `categories` VALUES (74, 'Miami', 1, 0, 1, 'Y', 1, '74','','','','', NULL);;;
INSERT INTO `categories` VALUES (75, 'Boston', 1, 0, 1, 'Y', 1, '75','','','','', NULL);;;
INSERT INTO `categories` VALUES (76, 'Los Angeles', 1, 0, 1, 'Y', 1, '76','','','','', NULL);;;
INSERT INTO `categories` VALUES (77, 'Washington, D.C.', 1, 0, 1, 'Y', 1, '77','','','','', NULL);;;
INSERT INTO `categories` VALUES (78, 'Education & Training', 23, 0, 1, 'Y', 1, '78','','','','', NULL);;;
INSERT INTO `categories` VALUES (79, 'Manufacturing/Operations', 23, 0, 1, 'Y', 1, '79','','','','', NULL);;;
INSERT INTO `categories` VALUES (80, 'Retail', 23, 0, 1, 'Y', 1, '80','','','','', NULL);;;
INSERT INTO `categories` VALUES (81, 'Healthcare & Community', 23, 0, 1, 'Y', 1, '81','','','','', NULL);;;
INSERT INTO `categories` VALUES (82, 'Accounting', 23, 1, 1, 'Y', 1, '82','','','','', NULL);;;
INSERT INTO `categories` VALUES (83, 'I.T. & T.', 23, 0, 1, 'Y', 1, '83','','','','', NULL);;;
INSERT INTO `categories` VALUES (84, 'Sales & Marketing', 23, 0, 1, 'Y', 1, '84','','','','', NULL);;;
INSERT INTO `categories` VALUES (85, 'Legal', 23, 0, 1, 'Y', 1, '85','','','','', NULL);;;
INSERT INTO `categories` VALUES (86, 'Hospitality & Tourism', 23, 0, 1, 'Y', 1, '86','','','','', NULL);;;
INSERT INTO `categories` VALUES (87, 'Engineering', 23, 0, 1, 'Y', 1, '87','','','','', NULL);;;
INSERT INTO `categories` VALUES (88, 'Administration', 23, 0, 1, 'Y', 1, '88','','','','', NULL);;;
INSERT INTO `categories` VALUES (89, 'Construction', 23, 0, 1, 'Y', 1, '89','','','','', NULL);;;


CREATE TABLE `codes` (
  `field_id` varchar(30) NOT NULL default '',
  `code` varchar(5) NOT NULL default '',
  `description` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`field_id`,`code`)
) ;;;

 

INSERT INTO `codes` VALUES ('37', '0', '0-2 Yr');;;
INSERT INTO `codes` VALUES ('37', '11+', '11+ Yr');;;
INSERT INTO `codes` VALUES ('37', '36', '3-6 Yr');;;
INSERT INTO `codes` VALUES ('37', '710', '7-10 Yr');;;
INSERT INTO `codes` VALUES ('46', 'AA', 'AAAA');;;
INSERT INTO `codes` VALUES ('46', 'BB', 'BBBB');;;
INSERT INTO `codes` VALUES ('46', 'CC', 'CCCC');;;
INSERT INTO `codes` VALUES ('46', 'DD', 'DDDD');;;
INSERT INTO `codes` VALUES ('47', 'A', 'codea');;;
INSERT INTO `codes` VALUES ('47', 'B', 'codeb');;;
INSERT INTO `codes` VALUES ('47', 'C', 'codec');;;
INSERT INTO `codes` VALUES ('48', 'O', 'One');;;
INSERT INTO `codes` VALUES ('48', 'T', 'Two');;;
INSERT INTO `codes` VALUES ('48', 'Th', 'Three');;;
INSERT INTO `codes` VALUES ('55', 'BA', 'Bachelor''s');;;
INSERT INTO `codes` VALUES ('55', 'Col', 'Student - College');;;
INSERT INTO `codes` VALUES ('55', 'Dip', 'Diploma');;;
INSERT INTO `codes` VALUES ('55', 'Doc', 'Doctorate (Other)');;;
INSERT INTO `codes` VALUES ('55', 'HI', 'High School');;;
INSERT INTO `codes` VALUES ('55', 'JD', 'JD');;;
INSERT INTO `codes` VALUES ('55', 'Law', 'Student - Law School');;;
INSERT INTO `codes` VALUES ('55', 'MA', 'Master''s');;;
INSERT INTO `codes` VALUES ('55', 'MBA', 'MBA');;;
INSERT INTO `codes` VALUES ('55', 'Med', 'Student - Med School');;;
INSERT INTO `codes` VALUES ('55', 'PhD', 'PhD');;;
INSERT INTO `codes` VALUES ('55', 'PhDc', 'PhD Candidate');;;
INSERT INTO `codes` VALUES ('55', 'PhDmd', 'MD-PhD');;;
INSERT INTO `codes` VALUES ('67', 'G', 'Government institution');;;
INSERT INTO `codes` VALUES ('67', 'I', 'Individual');;;
INSERT INTO `codes` VALUES ('67', 'P', 'Private Organization');;;
INSERT INTO `codes` VALUES ('67', 'R', 'Recruitment / Consulting');;;

 

CREATE TABLE `codes_translations` (
  `field_id` int(11) NOT NULL default '0',
  `code` varchar(10) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `lang` char(2) NOT NULL default '',
  PRIMARY KEY  (`field_id`,`code`,`lang`)
) ;;;



INSERT INTO `codes_translations` VALUES (37, '0', '0-2 Yr', '');;;
INSERT INTO `codes_translations` VALUES (37, '0', '0-2 Yr', 'CN');;;
INSERT INTO `codes_translations` VALUES (37, '0', '0-2 Yr', 'EN');;;
INSERT INTO `codes_translations` VALUES (37, '0', '0-2 Yr', 'ES');;;
INSERT INTO `codes_translations` VALUES (37, '0', '0-2 Yr', 'KO');;;
INSERT INTO `codes_translations` VALUES (37, '0', '0-2 Yr', 'PL');;;
INSERT INTO `codes_translations` VALUES (37, '11+', '11+ Yr', '');;;
INSERT INTO `codes_translations` VALUES (37, '11+', '11+ Yr', 'CN');;;
INSERT INTO `codes_translations` VALUES (37, '11+', '11+ Yr', 'EN');;;
INSERT INTO `codes_translations` VALUES (37, '11+', '11+ Yr', 'ES');;;
INSERT INTO `codes_translations` VALUES (37, '11+', '11+ Yr', 'KO');;;
INSERT INTO `codes_translations` VALUES (37, '11+', '11+ Yr', 'PL');;;
INSERT INTO `codes_translations` VALUES (37, '36', '3-6 Yr', '');;;
INSERT INTO `codes_translations` VALUES (37, '36', '3-6 Yr', 'CN');;;
INSERT INTO `codes_translations` VALUES (37, '36', '3-6 Yr', 'EN');;;
INSERT INTO `codes_translations` VALUES (37, '36', '3-6 Yr', 'ES');;;
INSERT INTO `codes_translations` VALUES (37, '36', '3-6 Yr', 'KO');;;
INSERT INTO `codes_translations` VALUES (37, '36', '3-6 Yr', 'PL');;;
INSERT INTO `codes_translations` VALUES (37, '710', '7-10 Yr', '');;;
INSERT INTO `codes_translations` VALUES (37, '710', '7-10 Yr', 'CN');;;
INSERT INTO `codes_translations` VALUES (37, '710', '7-10 Yr', 'EN');;;
INSERT INTO `codes_translations` VALUES (37, '710', '7-10 Yr', 'ES');;;
INSERT INTO `codes_translations` VALUES (37, '710', '7-10 Yr', 'KO');;;
INSERT INTO `codes_translations` VALUES (37, '710', '7-10 Yr', 'PL');;;
INSERT INTO `codes_translations` VALUES (46, 'AA', 'AAAA', '');;;
INSERT INTO `codes_translations` VALUES (46, 'AA', 'AAAA', 'CN');;;
INSERT INTO `codes_translations` VALUES (46, 'AA', 'AAAA', 'EN');;;
INSERT INTO `codes_translations` VALUES (46, 'AA', 'AAAA', 'ES');;;
INSERT INTO `codes_translations` VALUES (46, 'AA', 'AAAA', 'KO');;;
INSERT INTO `codes_translations` VALUES (46, 'AA', 'AAAA', 'PL');;;
INSERT INTO `codes_translations` VALUES (46, 'BB', 'BBBB', '');;;
INSERT INTO `codes_translations` VALUES (46, 'BB', 'BBBB', 'CN');;;
INSERT INTO `codes_translations` VALUES (46, 'BB', 'BBBB', 'EN');;;
INSERT INTO `codes_translations` VALUES (46, 'BB', 'BBBB', 'ES');;;
INSERT INTO `codes_translations` VALUES (46, 'BB', 'BBBB', 'KO');;;
INSERT INTO `codes_translations` VALUES (46, 'BB', 'BBBB', 'PL');;;
INSERT INTO `codes_translations` VALUES (46, 'CC', 'CCCC', '');;;
INSERT INTO `codes_translations` VALUES (46, 'CC', 'CCCC', 'CN');;;
INSERT INTO `codes_translations` VALUES (46, 'CC', 'CCCC', 'EN');;;
INSERT INTO `codes_translations` VALUES (46, 'CC', 'CCCC', 'ES');;;
INSERT INTO `codes_translations` VALUES (46, 'CC', 'CCCC', 'KO');;;
INSERT INTO `codes_translations` VALUES (46, 'CC', 'CCCC', 'PL');;;
INSERT INTO `codes_translations` VALUES (46, 'DD', 'DDDD', '');;;
INSERT INTO `codes_translations` VALUES (46, 'DD', 'DDDD', 'CN');;;
INSERT INTO `codes_translations` VALUES (46, 'DD', 'DDDD', 'EN');;;
INSERT INTO `codes_translations` VALUES (46, 'DD', 'DDDD', 'ES');;;
INSERT INTO `codes_translations` VALUES (46, 'DD', 'DDDD', 'KO');;;
INSERT INTO `codes_translations` VALUES (46, 'DD', 'DDDD', 'PL');;;
INSERT INTO `codes_translations` VALUES (47, 'A', 'codea', '');;;
INSERT INTO `codes_translations` VALUES (47, 'A', 'codea', 'CN');;;
INSERT INTO `codes_translations` VALUES (47, 'A', 'codea', 'EN');;;
INSERT INTO `codes_translations` VALUES (47, 'A', 'codea', 'ES');;;
INSERT INTO `codes_translations` VALUES (47, 'A', 'codea', 'KO');;;
INSERT INTO `codes_translations` VALUES (47, 'A', 'codea', 'PL');;;
INSERT INTO `codes_translations` VALUES (47, 'B', 'codeb', '');;;
INSERT INTO `codes_translations` VALUES (47, 'B', 'codeb', 'CN');;;
INSERT INTO `codes_translations` VALUES (47, 'B', 'codeb', 'EN');;;
INSERT INTO `codes_translations` VALUES (47, 'B', 'codeb', 'ES');;;
INSERT INTO `codes_translations` VALUES (47, 'B', 'codeb', 'KO');;;
INSERT INTO `codes_translations` VALUES (47, 'B', 'codeb', 'PL');;;
INSERT INTO `codes_translations` VALUES (47, 'C', 'codec', '');;;
INSERT INTO `codes_translations` VALUES (47, 'C', 'codec', 'CN');;;
INSERT INTO `codes_translations` VALUES (47, 'C', 'codec', 'EN');;;
INSERT INTO `codes_translations` VALUES (47, 'C', 'codec', 'ES');;;
INSERT INTO `codes_translations` VALUES (47, 'C', 'codec', 'KO');;;
INSERT INTO `codes_translations` VALUES (47, 'C', 'codec', 'PL');;;
INSERT INTO `codes_translations` VALUES (48, 'O', 'One', '');;;
INSERT INTO `codes_translations` VALUES (48, 'O', 'One', 'CN');;;
INSERT INTO `codes_translations` VALUES (48, 'O', 'One', 'EN');;;
INSERT INTO `codes_translations` VALUES (48, 'O', 'One', 'ES');;;
INSERT INTO `codes_translations` VALUES (48, 'O', 'One', 'KO');;;
INSERT INTO `codes_translations` VALUES (48, 'O', 'One', 'PL');;;
INSERT INTO `codes_translations` VALUES (48, 'T', 'Two', '');;;
INSERT INTO `codes_translations` VALUES (48, 'T', 'Two', 'CN');;;
INSERT INTO `codes_translations` VALUES (48, 'T', 'Two', 'EN');;;
INSERT INTO `codes_translations` VALUES (48, 'T', 'Two', 'ES');;;
INSERT INTO `codes_translations` VALUES (48, 'T', 'Two', 'KO');;;
INSERT INTO `codes_translations` VALUES (48, 'T', 'Two', 'PL');;;
INSERT INTO `codes_translations` VALUES (48, 'Th', 'Three', '');;;
INSERT INTO `codes_translations` VALUES (48, 'Th', 'Three', 'CN');;;
INSERT INTO `codes_translations` VALUES (48, 'Th', 'Three', 'EN');;;
INSERT INTO `codes_translations` VALUES (48, 'Th', 'Three', 'ES');;;
INSERT INTO `codes_translations` VALUES (48, 'Th', 'Three', 'KO');;;
INSERT INTO `codes_translations` VALUES (48, 'Th', 'Three', 'PL');;;
INSERT INTO `codes_translations` VALUES (55, 'BA', 'Bachelor''s', '');;;
INSERT INTO `codes_translations` VALUES (55, 'BA', 'Bachelor''s', 'CN');;;
INSERT INTO `codes_translations` VALUES (55, 'BA', 'Bachelor''s', 'EN');;;
INSERT INTO `codes_translations` VALUES (55, 'BA', 'Bachelor''s', 'ES');;;
INSERT INTO `codes_translations` VALUES (55, 'BA', 'Bachelor''s', 'KO');;;
INSERT INTO `codes_translations` VALUES (55, 'BA', 'Bachelor''s', 'PL');;;
INSERT INTO `codes_translations` VALUES (55, 'Col', 'Student - College', '');;;
INSERT INTO `codes_translations` VALUES (55, 'Col', 'Student - College', 'CN');;;
INSERT INTO `codes_translations` VALUES (55, 'Col', 'Student - College', 'EN');;;
INSERT INTO `codes_translations` VALUES (55, 'Col', 'Student - College', 'ES');;;
INSERT INTO `codes_translations` VALUES (55, 'Col', 'Student - College', 'KO');;;
INSERT INTO `codes_translations` VALUES (55, 'Col', 'Student - College', 'PL');;;
INSERT INTO `codes_translations` VALUES (55, 'Dip', 'Diploma', '');;;
INSERT INTO `codes_translations` VALUES (55, 'Dip', 'Diploma', 'CN');;;
INSERT INTO `codes_translations` VALUES (55, 'Dip', 'Diploma', 'EN');;;
INSERT INTO `codes_translations` VALUES (55, 'Dip', 'Diploma', 'ES');;;
INSERT INTO `codes_translations` VALUES (55, 'Dip', 'Diploma', 'KO');;;
INSERT INTO `codes_translations` VALUES (55, 'Dip', 'Diploma', 'PL');;;
INSERT INTO `codes_translations` VALUES (55, 'Doc', 'Doctorate (Other)', '');;;
INSERT INTO `codes_translations` VALUES (55, 'Doc', 'Doctorate (Other)', 'CN');;;
INSERT INTO `codes_translations` VALUES (55, 'Doc', 'Doctorate (Other)', 'EN');;;
INSERT INTO `codes_translations` VALUES (55, 'Doc', 'Doctorate (Other)', 'ES');;;
INSERT INTO `codes_translations` VALUES (55, 'Doc', 'Doctorate (Other)', 'KO');;;
INSERT INTO `codes_translations` VALUES (55, 'Doc', 'Doctorate (Other)', 'PL');;;
INSERT INTO `codes_translations` VALUES (55, 'HI', 'High School', '');;;
INSERT INTO `codes_translations` VALUES (55, 'HI', 'High School', 'CN');;;
INSERT INTO `codes_translations` VALUES (55, 'HI', 'High School', 'EN');;;
INSERT INTO `codes_translations` VALUES (55, 'HI', 'High School', 'ES');;;
INSERT INTO `codes_translations` VALUES (55, 'HI', 'High School', 'KO');;;
INSERT INTO `codes_translations` VALUES (55, 'HI', 'High School', 'PL');;;
INSERT INTO `codes_translations` VALUES (55, 'JD', 'JD', '');;;
INSERT INTO `codes_translations` VALUES (55, 'JD', 'JD', 'CN');;;
INSERT INTO `codes_translations` VALUES (55, 'JD', 'JD', 'EN');;;
INSERT INTO `codes_translations` VALUES (55, 'JD', 'JD', 'ES');;;
INSERT INTO `codes_translations` VALUES (55, 'JD', 'JD', 'KO');;;
INSERT INTO `codes_translations` VALUES (55, 'JD', 'JD', 'PL');;;
INSERT INTO `codes_translations` VALUES (55, 'Law', 'Student - Law School', '');;;
INSERT INTO `codes_translations` VALUES (55, 'Law', 'Student - Law School', 'CN');;;
INSERT INTO `codes_translations` VALUES (55, 'Law', 'Student - Law School', 'EN');;;
INSERT INTO `codes_translations` VALUES (55, 'Law', 'Student - Law School', 'ES');;;
INSERT INTO `codes_translations` VALUES (55, 'Law', 'Student - Law School', 'KO');;;
INSERT INTO `codes_translations` VALUES (55, 'Law', 'Student - Law School', 'PL');;;
INSERT INTO `codes_translations` VALUES (55, 'MA', 'Master''s', '');;;
INSERT INTO `codes_translations` VALUES (55, 'MA', 'Master''s', 'CN');;;
INSERT INTO `codes_translations` VALUES (55, 'MA', 'Master''s', 'EN');;;
INSERT INTO `codes_translations` VALUES (55, 'MA', 'Master''s', 'ES');;;
INSERT INTO `codes_translations` VALUES (55, 'MA', 'Master''s', 'KO');;;
INSERT INTO `codes_translations` VALUES (55, 'MA', 'Master''s', 'PL');;;
INSERT INTO `codes_translations` VALUES (55, 'MBA', 'MBA', '');;;
INSERT INTO `codes_translations` VALUES (55, 'MBA', 'MBA', 'CN');;;
INSERT INTO `codes_translations` VALUES (55, 'MBA', 'MBA', 'EN');;;
INSERT INTO `codes_translations` VALUES (55, 'MBA', 'MBA', 'ES');;;
INSERT INTO `codes_translations` VALUES (55, 'MBA', 'MBA', 'KO');;;
INSERT INTO `codes_translations` VALUES (55, 'MBA', 'MBA', 'PL');;;
INSERT INTO `codes_translations` VALUES (55, 'Med', 'Student - Med School', '');;;
INSERT INTO `codes_translations` VALUES (55, 'Med', 'Student - Med School', 'CN');;;
INSERT INTO `codes_translations` VALUES (55, 'Med', 'Student - Med School', 'EN');;;
INSERT INTO `codes_translations` VALUES (55, 'Med', 'Student - Med School', 'ES');;;
INSERT INTO `codes_translations` VALUES (55, 'Med', 'Student - Med School', 'KO');;;
INSERT INTO `codes_translations` VALUES (55, 'Med', 'Student - Med School', 'PL');;;
INSERT INTO `codes_translations` VALUES (55, 'PhD', 'PhD', '');;;
INSERT INTO `codes_translations` VALUES (55, 'PhD', 'PhD', 'CN');;;
INSERT INTO `codes_translations` VALUES (55, 'PhD', 'PhD', 'EN');;;
INSERT INTO `codes_translations` VALUES (55, 'PhD', 'PhD', 'ES');;;
INSERT INTO `codes_translations` VALUES (55, 'PhD', 'PhD', 'KO');;;
INSERT INTO `codes_translations` VALUES (55, 'PhD', 'PhD', 'PL');;;
INSERT INTO `codes_translations` VALUES (55, 'PhDc', 'PhD Candidate', '');;;
INSERT INTO `codes_translations` VALUES (55, 'PhDc', 'PhD Candidate', 'CN');;;
INSERT INTO `codes_translations` VALUES (55, 'PhDc', 'PhD Candidate', 'EN');;;
INSERT INTO `codes_translations` VALUES (55, 'PhDc', 'PhD Candidate', 'ES');;;
INSERT INTO `codes_translations` VALUES (55, 'PhDc', 'PhD Candidate', 'KO');;;
INSERT INTO `codes_translations` VALUES (55, 'PhDc', 'PhD Candidate', 'PL');;;
INSERT INTO `codes_translations` VALUES (55, 'PhDmd', 'MD-PhD', '');;;
INSERT INTO `codes_translations` VALUES (55, 'PhDmd', 'MD-PhD', 'CN');;;
INSERT INTO `codes_translations` VALUES (55, 'PhDmd', 'MD-PhD', 'EN');;;
INSERT INTO `codes_translations` VALUES (55, 'PhDmd', 'MD-PhD', 'ES');;;
INSERT INTO `codes_translations` VALUES (55, 'PhDmd', 'MD-PhD', 'KO');;;
INSERT INTO `codes_translations` VALUES (55, 'PhDmd', 'MD-PhD', 'PL');;;
INSERT INTO `codes_translations` VALUES (67, 'G', 'Government institution', '');;;
INSERT INTO `codes_translations` VALUES (67, 'G', 'Government institution', 'CN');;;
INSERT INTO `codes_translations` VALUES (67, 'G', 'Government institution', 'EN');;;
INSERT INTO `codes_translations` VALUES (67, 'G', 'Government institution', 'ES');;;
INSERT INTO `codes_translations` VALUES (67, 'G', 'Government institution', 'KO');;;
INSERT INTO `codes_translations` VALUES (67, 'G', 'Government institution', 'PL');;;
INSERT INTO `codes_translations` VALUES (67, 'I', 'Individual', '');;;
INSERT INTO `codes_translations` VALUES (67, 'I', 'Individual', 'CN');;;
INSERT INTO `codes_translations` VALUES (67, 'I', 'Individual', 'EN');;;
INSERT INTO `codes_translations` VALUES (67, 'I', 'Individual', 'ES');;;
INSERT INTO `codes_translations` VALUES (67, 'I', 'Individual &#50668;&#44592;', 'KO');;;
INSERT INTO `codes_translations` VALUES (67, 'I', 'Individual', 'PL');;;
INSERT INTO `codes_translations` VALUES (67, 'P', 'Private Organization', '');;;
INSERT INTO `codes_translations` VALUES (67, 'P', 'Private Organization', 'CN');;;
INSERT INTO `codes_translations` VALUES (67, 'P', 'Private Organization', 'EN');;;
INSERT INTO `codes_translations` VALUES (67, 'P', 'Private Organization', 'ES');;;
INSERT INTO `codes_translations` VALUES (67, 'P', 'Private Organization', 'KO');;;
INSERT INTO `codes_translations` VALUES (67, 'P', 'Private Organization', 'PL');;;
INSERT INTO `codes_translations` VALUES (67, 'R', 'Recruitment / Consulting', '');;;
INSERT INTO `codes_translations` VALUES (67, 'R', 'Recruitment / Consulting', 'CN');;;
INSERT INTO `codes_translations` VALUES (67, 'R', 'Recruitment / Consulting', 'EN');;;
INSERT INTO `codes_translations` VALUES (67, 'R', 'Recruitment / Consulting', 'ES');;;
INSERT INTO `codes_translations` VALUES (67, 'R', 'Recruitment / Consulting', 'KO');;;
INSERT INTO `codes_translations` VALUES (67, 'R', 'Recruitment / Consulting', 'PL');;;


CREATE TABLE `currencies` (
  `code` char(3) NOT NULL default '',
  `name` varchar(50) NOT NULL default '',
  `rate` decimal(10,4) NOT NULL default '1.0000',
  `is_default` set('Y','N') NOT NULL default 'N',
  `sign` varchar(8) NOT NULL default '',
  `decimal_places` smallint(6) NOT NULL default '0',
  `decimal_point` char(3) NOT NULL default '',
  `thousands_sep` char(3) NOT NULL default '',
  PRIMARY KEY  (`code`)
) ;;;


INSERT INTO `currencies` VALUES ('AUD', 'Australian Dollar', 1.3228, 'N', '$', 2, '.', ',');;;
INSERT INTO `currencies` VALUES ('CAD', 'Canadian Dollar', 1.1998, 'N', '$', 2, '.', ',');;;
INSERT INTO `currencies` VALUES ('EUR', 'Euro', 0.8138, 'N', '&#8364;', 2, '.', ',');;;
INSERT INTO `currencies` VALUES ('GBP', 'British Pound', 0.5555, 'N', '&pound;', 2, '.', ',');;;
INSERT INTO `currencies` VALUES ('JPY', 'Japanese Yen', 110.1950, 'N', '&yen;', 0, '.', ',');;;
INSERT INTO `currencies` VALUES ('KRW', 'Korean Won', 1028.8000, 'N', '&#8361;', 0, '.', ',');;;
INSERT INTO `currencies` VALUES ('USD', 'U.S. Dollar', 1.0000, 'Y', '$', 2, '.', ',');;;

 

CREATE TABLE `email_template_translations` (
  `EmailID` int(11) NOT NULL default '0',
  `lang` varchar(10) NOT NULL default '',
  `EmailText` text NOT NULL,
  `EmailFromAddress` varchar(255) NOT NULL default '',
  `EmailSubject` varchar(255) NOT NULL default '',
  `EmailFromName` varchar(255) NOT NULL default '',
  `sub_template` text NOT NULL,
  PRIMARY KEY  (`EmailID`,`lang`)
) ;;;



INSERT INTO `email_template_translations` VALUES (1, 'CN', 'Dear %FNAME% %LNAME%,\r\n\r\nYou have successfully signed up to %SITE_NAME%.\r\n\r\nIf you ever encounter any problems, bugs or just have \r\nany questions or suggestions, feel free to contact \r\nus: %SITE_CONTACT_EMAIL%;\r\n\r\n\r\nYou have registered with the following details - \r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD% \r\n\r\n', 'test@example.com', 'Successfully Signed Up as %MEMBERID%', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (1, 'EN', 'Dear %FNAME% %LNAME%,\r\n\r\nYou have successfully signed up to %SITE_NAME%.\r\n\r\nIf you ever encounter any problems, bugs or just have \r\nany questions or suggestions, feel free to contact \r\nus: %SITE_CONTACT_EMAIL%;\r\n\r\n\r\nYou have registered with the following details - \r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD% \r\n\r\n\r\n', 'test@example.com', 'Successfully Signed Up as %MEMBERID%', 'Example', '');;;
INSERT INTO `email_template_translations` VALUES (1, 'ES', 'Dear %FNAME% %LNAME%,\r\n\r\nYou have successfully signed up to %SITE_NAME%.\r\n\r\nIf you ever encounter any problems, bugs or just have \r\nany questions or suggestions, feel free to contact \r\nus: %SITE_CONTACT_EMAIL%;\r\n\r\n\r\nYou have registered with the following details - \r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD% \r\n\r\n', 'test@example.com', 'Successfully Signed Up as %MEMBERID%', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (1, 'KO', 'Dear %FNAME% %LNAME%,\r\n\r\nYou have successfully signed up to %SITE_NAME%.\r\n\r\nIf you ever encounter any problems, bugs or just have \r\nany questions or suggestions, feel free to contact \r\nus: %SITE_CONTACT_EMAIL%;\r\n\r\n\r\nYou have registered with the following details - \r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD% \r\n\r\n', 'test@example.com', 'Successfully Signed Up as %MEMBERID%', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (1, 'PL', 'Dear %FNAME% %LNAME%,\r\n\r\nYou have successfully signed up to %SITE_NAME%.\r\n\r\nIf you ever encounter any problems, bugs or just have \r\nany questions or suggestions, feel free to contact \r\nus: %SITE_CONTACT_EMAIL%;\r\n\r\n\r\nYou have registered with the following details - \r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD% \r\n\r\n', 'test@example.com', 'Successfully Signed Up as %MEMBERID%', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (2, 'CN', 'Dear %FNAME% %LNAME%,\r\n\r\nYou have successfully signed for a %SITE_NAME% Employer''s Account. \r\nYou have registered with the following details - \r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD% \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have any questions / problems. \r\n\r\nKind Regards,\r\n\r\nJob Board team.', 'test@example.com', 'Successfully Signed Up as %MEMBERID%', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (2, 'EN', 'Dear %FNAME% %LNAME%,\r\n\r\nYou have successfully signed for a %SITE_NAME% Employer''s Account. \r\nYou have registered with the following details - \r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD% \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have any questions / problems. \r\n\r\nKind Regards,\r\n\r\nWebmaster.', 'test@example.com', 'Successfully Signed Up as %MEMBERID%', 'Example', '');;;
INSERT INTO `email_template_translations` VALUES (2, 'ES', 'Dear %FNAME% %LNAME%,\r\n\r\nYou have successfully signed for a %SITE_NAME% Employer''s Account. \r\nYou have registered with the following details - \r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD% \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have any questions / problems. \r\n\r\nKind Regards,\r\n\r\nJob Board team.', 'test@example.com', 'Successfully Signed Up as %MEMBERID%', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (2, 'KO', 'Dear %FNAME% %LNAME%,\r\n\r\nYou have successfully signed for a %SITE_NAME% Employer''s Account. Your account is not yet enabled. Your account will be manually reviewed and approved by %SITE_NAME%. We will inform you of the result soon. \r\nYou have registered with the following details - \r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD% \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have any questions / problems. \r\n', 'test@example.com', 'Successfully Signed Up as %MEMBERID%', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (2, 'PL', 'Dear %FNAME% %LNAME%,\r\n\r\nYou have successfully signed for a %SITE_NAME% Employer''s Account. \r\nYou have registered with the following details - \r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD% \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have any questions / problems. \r\n\r\nKind Regards,\r\n\r\nJob Board team.', 'test@example.com', 'Successfully Signed Up as %MEMBERID%', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (3, 'CN', 'Dear %FNAME% %LNAME%,\r\n\r\nYour %SITE_NAME% password has been reset!\r\n\r\nHere is your new password:\r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD%\r\n\r\nYou can sign into your account here: http://www.example.com \r\n\r\nRegards,\r\n\r\nWebmaster,\r\nJob Board!\r\n', 'test@example.com', 'Reset Password', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (3, 'EN', 'Dear %FNAME% %LNAME%,\r\n\r\nYour %SITE_NAME% password has been reset!\r\n\r\nHere is your new password:\r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD%\r\n\r\nYou can sign into your account here: http://www.example.com/ \r\n\r\nKind Regards,\r\n\r\nWebmaster', 'test@example.com', 'Reset Password', 'Example', '');;;
INSERT INTO `email_template_translations` VALUES (3, 'ES', 'Dear %FNAME% %LNAME%,\r\n\r\nYour %SITE_NAME% password has been reset!\r\n\r\nHere is your new password:\r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD%\r\n\r\nYou can sign into your account here: http://www.example.com \r\n\r\nRegards,\r\n\r\nWebmaster,\r\nJob Board!\r\n', 'test@example.com', 'Reset Password', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (3, 'KO', 'Dear %FNAME% %LNAME%,\r\n\r\nYour %SITE_NAME% password has been reset!\r\n\r\nHere is your new password:\r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD%\r\n\r\nYou can sign into your account here: http://www.example.com \r\n\r\nRegards,\r\n\r\nWebmaster,\r\nJob Board!\r\n', 'test@example.com', 'Reset Password', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (3, 'PL', 'Dear %FNAME% %LNAME%,\r\n\r\nYour %SITE_NAME% password has been reset!\r\n\r\nHere is your new password:\r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD%\r\n\r\nYou can sign into your account here: http://www.example.com \r\n\r\nRegards,\r\n\r\nWebmaster,\r\nJob Board!\r\n', 'test@example.com', 'Reset Password', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (4, 'CN', 'Dear %FNAME% %LNAME%,\r\n\r\nAn employer on %SITE_NAME% has requested for your contact details!\r\nHere are the details of the request:\r\n\r\nEmployer Name: %EMPLOYER_NAME%\r\nReply-to Email Address: %REPLY_TO%\r\n%MESSAGE%\r\n\r\nYou may reveal your contact details to this employer by simply visiting the following link: %PERMIT_LINK%\r\n\r\nYou may also contact the employer directly by replying to this email!\r\n\r\n\r\nBest Regards,\r\n\r\nTeam %SITE_NAME%', 'test@example.com', 'Request for your contact details by an employer', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (4, 'EN', 'Dear %FNAME% %LNAME%,\r\n\r\nAn employer on %SITE_NAME% has requested for your contact details!\r\nHere are the details of the request:\r\n\r\nEmployer Name: %EMPLOYER_NAME%\r\nReply-to Email Address: %REPLY_TO%\r\n%MESSAGE%\r\n\r\nYou may reveal your contact details to this employer by simply visiting the following link: %PERMIT_LINK%\r\n\r\nYou may also contact the employer directly by replying to this email!\r\n\r\n\r\nBest Regards,\r\n\r\nTeam %SITE_NAME%', 'test@example.com', 'Request for your contact details by an employer', 'Example', '');;;
INSERT INTO `email_template_translations` VALUES (4, 'ES', 'Dear %FNAME% %LNAME%,\r\n\r\nAn employer on %SITE_NAME% has requested for your contact details!\r\nHere are the details of the request:\r\n\r\nEmployer Name: %EMPLOYER_NAME%\r\nReply-to Email Address: %REPLY_TO%\r\n%MESSAGE%\r\n\r\nYou may reveal your contact details to this employer by simply visiting the following link: %PERMIT_LINK%\r\n\r\nYou may also contact the employer directly by replying to this email!\r\n\r\n\r\nBest Regards,\r\n\r\nTeam %SITE_NAME%', 'test@example.com', 'Request for your contact details by an employer', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (4, 'KO', 'Dear %FNAME% %LNAME%,\r\n\r\nAn employer on %SITE_NAME% has requested for your contact details!\r\nHere are the details of the request:\r\n\r\nEmployer Name: %EMPLOYER_NAME%\r\nReply-to Email Address: %REPLY_TO%\r\n%MESSAGE%\r\n\r\nYou may reveal your contact details to this employer by simply visiting the following link: %PERMIT_LINK%\r\n\r\nYou may also contact the employer directly by replying to this email!\r\n\r\n\r\nBest Regards,\r\n\r\nTeam %SITE_NAME%', 'test@example.com', 'Request for your contact details by an employer', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (4, 'PL', 'Dear %FNAME% %LNAME%,\r\n\r\nAn employer on %SITE_NAME% has requested for your contact details!\r\nHere are the details of the request:\r\n\r\nEmployer Name: %EMPLOYER_NAME%\r\nReply-to Email Address: %REPLY_TO%\r\n%MESSAGE%\r\n\r\nYou may reveal your contact details to this employer by simply visiting the following link: %PERMIT_LINK%\r\n\r\nYou may also contact the employer directly by replying to this email!\r\n\r\n\r\nBest Regards,\r\n\r\nTeam %SITE_NAME%', 'test@example.com', 'Request for your contact details by an employer', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (5, 'CN', 'Your %SITE_NAME% Daily Resume Alert!\r\n\r\nDear %FNAME% %LNAME%,\r\n\r\nYour Resume Alert on %SITE_NAME% has returned the following alert for you today:\r\n\r\n%RESUME_ALERTS% \r\n\r\n\r\n\r\n%SITE_NAME% Team %SITE_CONTACT_EMAIL%\r\n- If you want to View these resumes,or maintain your Daily Resume Alerts, \r\nplease visit this link:\r\n%EMPLOYER_LINK% \r\n', 'test@example.com', 'Daily C.V. Alert', 'Job Board', '%DATE% : %RESUME_NAME% (%RESUME_COL4%)');;;
INSERT INTO `email_template_translations` VALUES (5, 'EN', 'Your %SITE_NAME% Daily Resume Alert!\r\n\r\nDear %FNAME% %LNAME%,\r\n\r\nYour Resume Alert on %SITE_NAME% has returned the following alert for you today:\r\n\r\n%RESUME_ALERTS% \r\n\r\n\r\n\r\n%SITE_NAME% Team %SITE_CONTACT_EMAIL%\r\n- If you want to View these resumes,or maintain your Daily Resume Alerts, \r\nplease visit this link:\r\n%EMPLOYER_LINK% \r\n', 'test@example.com', 'Daily C.V. Alert', 'Example', '%DATE% : %RESUME_NAME% (%NATIONALITY%)');;;
INSERT INTO `email_template_translations` VALUES (5, 'ES', 'Your %SITE_NAME% Daily Resume Alert!\r\n\r\nDear %FNAME% %LNAME%,\r\n\r\nYour Resume Alert on %SITE_NAME% has returned the following alert for you today:\r\n\r\n%RESUME_ALERTS% \r\n\r\n\r\n\r\n%SITE_NAME% Team %SITE_CONTACT_EMAIL%\r\n- If you want to View these resumes,or maintain your Daily Resume Alerts, \r\nplease visit this link:\r\n%EMPLOYER_LINK% \r\n', 'test@example.com', 'Daily C.V. Alert', 'Job Board', '%DATE% : %RESUME_NAME% (%RESUME_COL4%)');;;
INSERT INTO `email_template_translations` VALUES (5, 'KO', 'Your %SITE_NAME% Daily Resume Alert!\r\n\r\nDear %FNAME% %LNAME%,\r\n\r\nYour Resume Alert on %SITE_NAME% has returned the following alert for you today:\r\n\r\n%RESUME_ALERTS% \r\n\r\n\r\n\r\n%SITE_NAME% Team %SITE_CONTACT_EMAIL%\r\n- If you want to View these resumes,or maintain your Daily Resume Alerts, \r\nplease visit this link:\r\n%EMPLOYER_LINK% \r\n', 'test@example.com', 'Daily C.V. Alert', 'Job Board', '%DATE% : %RESUME_NAME% (%RESUME_COL4%)');;;
INSERT INTO `email_template_translations` VALUES (5, 'PL', 'Your %SITE_NAME% Daily Resume Alert!\r\n\r\nDear %FNAME% %LNAME%,\r\n\r\nYour Resume Alert on %SITE_NAME% has returned the following alert for you today:\r\n\r\n%RESUME_ALERTS% \r\n\r\n\r\n\r\n%SITE_NAME% Team %SITE_CONTACT_EMAIL%\r\n- If you want to View these resumes,or maintain your Daily Resume Alerts, \r\nplease visit this link:\r\n%EMPLOYER_LINK% \r\n', 'test@example.com', 'Daily C.V. Alert', 'Job Board', '%DATE% : %RESUME_NAME% (%RESUME_COL4%)');;;
INSERT INTO `email_template_translations` VALUES (6, 'CN', '<h2><font face=''arial''>Your %SITE_NAME% Daily Resume Alert!</font></h2><p>\r\n<p><font size=''2'' face=''arial''>Dear %FNAME% %LNAME%,</font></p>\r\n<font face=''arial'' size=''2''>Your Resume Alert on %SITE_NAME% has returned the following alert for you today:</font><p>%RESUME_ALERTS%\r\n</p>\r\n<p>\r\n<p><font size=''2'' face=''arial''><b></b></font></p><p>\r\n<font face=\"arial\" size=\"2\">%SITE_NAME% Team %SITE_CONTACT_EMAIL%</font></p><p>\r\n<font face=\"arial\" size=\"2\">- If you want to View these resumes, or maintain your Daily Resume Alerts, please visit this link: %EMPLOYER_LINK%</a>\r\n</font></p>', 'test@example.com', '', 'Job Board', '<font face=''arial'' size=''2''>%DATE% - %RESUME_NAME% (%RESUME_COL4%)</font>');;;
INSERT INTO `email_template_translations` VALUES (6, 'EN', '<h2><font face=''arial''>Your %SITE_NAME% Daily Resume Alert!</font></h2><p>\r\n<p><font size=''2'' face=''arial''>Dear %FNAME% %LNAME%,</font></p>\r\n<font face=''arial'' size=''2''>Your Resume Alert on %SITE_NAME% has returned the following alert for you today:</font><p>%RESUME_ALERTS%\r\n</p>\r\n<p>\r\n<p><font size=''2'' face=''arial''><b></b></font></p><p>\r\n<font face=\"arial\" size=\"2\">%SITE_NAME% Team %SITE_CONTACT_EMAIL%</font></p><p>\r\n<font face=\"arial\" size=\"2\">- If you want to View these resumes, or maintain your Daily Resume Alerts, please visit this link: %EMPLOYER_LINK%</a>\r\n</font></p>\r\n', 'test@example.com', 'Resume Alert', 'Example', '<font face=''arial'' size=''2''>%DATE% - %RESUME_NAME% </font>');;;
INSERT INTO `email_template_translations` VALUES (6, 'ES', '<h2><font face=''arial''>Your %SITE_NAME% Daily Resume Alert!</font></h2><p>\r\n<p><font size=''2'' face=''arial''>Dear %FNAME% %LNAME%,</font></p>\r\n<font face=''arial'' size=''2''>Your Resume Alert on %SITE_NAME% has returned the following alert for you today:</font><p>%RESUME_ALERTS%\r\n</p>\r\n<p>\r\n<p><font size=''2'' face=''arial''><b></b></font></p><p>\r\n<font face=\"arial\" size=\"2\">%SITE_NAME% Team %SITE_CONTACT_EMAIL%</font></p><p>\r\n<font face=\"arial\" size=\"2\">- If you want to View these resumes, or maintain your Daily Resume Alerts, please visit this link: %EMPLOYER_LINK%</a>\r\n</font></p>', 'test@example.com', '', 'Job Board', '<font face=''arial'' size=''2''>%DATE% - %RESUME_NAME% (%RESUME_COL4%)</font>');;;
INSERT INTO `email_template_translations` VALUES (6, 'KO', '<h2><font face=''arial''>Your %SITE_NAME% Daily Resume Alert!</font></h2><p>\r\n<p><font size=''2'' face=''arial''>Dear %FNAME% %LNAME%,</font></p>\r\n<font face=''arial'' size=''2''>Your Resume Alert on %SITE_NAME% has returned the following alert for you today:</font><p>%RESUME_ALERTS%\r\n</p>\r\n<p>\r\n<p><font size=''2'' face=''arial''><b></b></font></p><p>\r\n<font face=\"arial\" size=\"2\">%SITE_NAME% Team %SITE_CONTACT_EMAIL%</font></p><p>\r\n<font face=\"arial\" size=\"2\">- If you want to View these resumes, or maintain your Daily Resume Alerts, please visit this link: %EMPLOYER_LINK%</a>\r\n</font></p>\r\n', 'test@example.com', '', 'Job Board', '<font face=''arial'' size=''2''>%DATE% - %RESUME_NAME% (%RESUME_COL4%)</font>');;;
INSERT INTO `email_template_translations` VALUES (6, 'PL', '<h2><font face=''arial''>Your %SITE_NAME% Daily Resume Alert!</font></h2><p>\r\n<p><font size=''2'' face=''arial''>Dear %FNAME% %LNAME%,</font></p>\r\n<font face=''arial'' size=''2''>Your Resume Alert on %SITE_NAME% has returned the following alert for you today:</font><p>%RESUME_ALERTS%\r\n</p>\r\n<p>\r\n<p><font size=''2'' face=''arial''><b></b></font></p><p>\r\n<font face=\"arial\" size=\"2\">%SITE_NAME% Team %SITE_CONTACT_EMAIL%</font></p><p>\r\n<font face=\"arial\" size=\"2\">- If you want to View these resumes, or maintain your Daily Resume Alerts, please visit this link: %EMPLOYER_LINK%</a>\r\n</font></p>', 'test@example.com', '', 'Job Board', '<font face=''arial'' size=''2''>%DATE% - %RESUME_NAME% (%RESUME_COL4%)</font>');;;
INSERT INTO `email_template_translations` VALUES (7, 'CN', 'Your SITE_NAME Daily Job Alert\r\n\r\nDear %FNAME% %LNAME%\r\n\r\nYour Daily Job Alert on %SITE_NAME% has returned the following alert for you today:\r\n\r\n%JOB_ALERTS% \r\n\r\n\r\n \r\n\r\n%SITE_NAME% Team test@example.com\r\n\r\n%SITE_CONTACT_EMAIL%\r\n- If you want to Cancel, or Edit your Daily Job Alerts, please visit this link: %CANDIDATE_LINK%\r\n', 'test@example.com', 'Job Alert', 'Job Board', '%FORMATTED_DATE% : %TITLE% (%LOCATION%)\r\nLink: %BASE_HTTP_PATH%index.php?post_id=%POST_ID%');;;
INSERT INTO `email_template_translations` VALUES (7, 'EN', 'Your %SITE_NAME% Daily Job Alert\r\n\r\nDear %FNAME% %LNAME%\r\n\r\nYour Daily Job Alert on %SITE_NAME% has returned the following alert for you today:\r\n\r\n%JOB_ALERTS% \r\n\r\n\r\n \r\n\r\n%SITE_NAME% Team test@example.com\r\n\r\n%SITE_CONTACT_EMAIL%\r\n- If you want to Cancel, or Edit your Daily Job Alerts, please visit this link: %CANDIDATE_LINK%\r\n', 'test@example.com', 'Job Alert', 'Example', '%FORMATTED_DATE% : %TITLE% (%LOCATION%)\r\nLink: %BASE_HTTP_PATH%index.php?post_id=%POST_ID%');;;
INSERT INTO `email_template_translations` VALUES (7, 'ES', 'Your SITE_NAME Daily Job Alert\r\n\r\nDear %FNAME% %LNAME%\r\n\r\nYour Daily Job Alert on %SITE_NAME% has returned the following alert for you today:\r\n\r\n%JOB_ALERTS% \r\n\r\n\r\n \r\n\r\n%SITE_NAME% Team test@example.com\r\n\r\n%SITE_CONTACT_EMAIL%\r\n- If you want to Cancel, or Edit your Daily Job Alerts, please visit this link: %CANDIDATE_LINK%\r\n', 'test@example.com', 'Job Alert', 'Job Board', '%FORMATTED_DATE% : %TITLE% (%LOCATION%)\r\nLink: %BASE_HTTP_PATH%index.php?post_id=%POST_ID%');;;
INSERT INTO `email_template_translations` VALUES (7, 'KO', 'Your SITE_NAME Daily Job Alert\r\n\r\nDear %FNAME% %LNAME%\r\n\r\nYour Daily Job Alert on %SITE_NAME% has returned the following alert for you today:\r\n\r\n%JOB_ALERTS% \r\n\r\n\r\n \r\n\r\n%SITE_NAME% Team test@example.com\r\n\r\n%SITE_CONTACT_EMAIL%\r\n- If you want to Cancel, or Edit your Daily Job Alerts, please visit this link: %CANDIDATE_LINK%\r\n', 'test@example.com', 'Job Alert', 'Job Board', '%FORMATTED_DATE% : %TITLE% (%LOCATION%)\r\nLink: %BASE_HTTP_PATH%index.php?post_id=%POST_ID%');;;
INSERT INTO `email_template_translations` VALUES (7, 'PL', 'Your SITE_NAME Daily Job Alert\r\n\r\nDear %FNAME% %LNAME%\r\n\r\nYour Daily Job Alert on %SITE_NAME% has returned the following alert for you today:\r\n\r\n%JOB_ALERTS% \r\n\r\n\r\n \r\n\r\n%SITE_NAME% Team test@example.com\r\n\r\n%SITE_CONTACT_EMAIL%\r\n- If you want to Cancel, or Edit your Daily Job Alerts, please visit this link: %CANDIDATE_LINK%\r\n', 'test@example.com', 'Job Alert', 'Job Board', '%FORMATTED_DATE% : %TITLE% (%LOCATION%)\r\nLink: %BASE_HTTP_PATH%index.php?post_id=%POST_ID%');;;
INSERT INTO `email_template_translations` VALUES (8, 'CN', '<img src=\"%SITE_LOGO_URL%\"><br><h2><font face=''arial''>Your %SITE_NAME% Daily Job Alert</font></h2>\r\n\r\n<p><font size=''2'' face=''arial''>Dear %FNAME% %LNAME%</font></p>\r\n\r\n<font face=''arial'' size=''2''>Your Daily Job Alert on %SITE_NAME% has returned the following alert for you today:</font><p></p>\r\n\r\n<p>\r\n%JOB_ALERTS%\r\n</p>\r\n<p>\r\n<b></b>\r\n</p>\r\n<p><font face=''arial'' size=''2''>%SITE_NAME% Team test@example.com<br>%SITE_CONTACT_EMAIL%</font></p>\r\n<p><font face=''arial'' size=''2''>- If you want to Cancel, or Edit your Daily Job Alerts, please visit this link: <a href=\"%CANDIDATE_LINK%\">%CANDIDATE_LINK%</a></font></p>', 'test@example.com', 'Job Alert', 'Job Board', '<font face=''arial'' size=''2''>%FORMATTED_DATE% - <a href=''%BASE_HTTP_PATH%index.php?post_id=%POST_ID%''>%TITLE%</a></font> (%LOCATION%) <font face=''arial'' size=''1'' color=''#808080''>%DESCRIPTION%</font>');;;
INSERT INTO `email_template_translations` VALUES (8, 'EN', '<img src=\"%SITE_LOGO_URL%\"><br><h2><font face=''arial''>Your %SITE_NAME% Daily Job Alert</font></h2>\r\n\r\n<p><font size=''2'' face=''arial''>Dear %FNAME% %LNAME%</font></p>\r\n\r\n<font face=''arial'' size=''2''>Your Daily Job Alert on %SITE_NAME% has returned the following alert for you today:</font><p></p>\r\n\r\n<p>\r\n%JOB_ALERTS%\r\n</p>\r\n<p>\r\n<b></b>\r\n</p>\r\n<p><font face=''arial'' size=''2''>%SITE_NAME% Team test@example.com<br>%SITE_CONTACT_EMAIL%</font></p>\r\n<p><font face=''arial'' size=''2''>- If you want to Cancel, or Edit your Daily Job Alerts, please visit this link: <a href=\"%CANDIDATE_LINK%\">%CANDIDATE_LINK%</a></font></p>', 'test@example.com', 'Job Alert', 'Example', '<font face=''arial'' size=''2''>%FORMATTED_DATE% - <a href=''%BASE_HTTP_PATH%index.php?post_id=%POST_ID%''>%TITLE%</a></font> (%LOCATION%) <font face=''arial'' size=''1'' color=''#808080''>%DESCRIPTION%</font>');;;
INSERT INTO `email_template_translations` VALUES (8, 'ES', '<img src=\"%SITE_LOGO_URL%\"><br><h2><font face=''arial''>Your %SITE_NAME% Daily Job Alert</font></h2>\r\n\r\n<p><font size=''2'' face=''arial''>Dear %FNAME% %LNAME%</font></p>\r\n\r\n<font face=''arial'' size=''2''>Your Daily Job Alert on %SITE_NAME% has returned the following alert for you today:</font><p></p>\r\n\r\n<p>\r\n%JOB_ALERTS%\r\n</p>\r\n<p>\r\n<b></b>\r\n</p>\r\n<p><font face=''arial'' size=''2''>%SITE_NAME% Team test@example.com<br>%SITE_CONTACT_EMAIL%</font></p>\r\n<p><font face=''arial'' size=''2''>- If you want to Cancel, or Edit your Daily Job Alerts, please visit this link: <a href=\"%CANDIDATE_LINK%\">%CANDIDATE_LINK%</a></font></p>', 'test@example.com', 'Job Alert', 'Job Board', '<font face=''arial'' size=''2''>%FORMATTED_DATE% - <a href=''%BASE_HTTP_PATH%index.php?post_id=%POST_ID%''>%TITLE%</a></font> (%LOCATION%) <font face=''arial'' size=''1'' color=''#808080''>%DESCRIPTION%</font>');;;
INSERT INTO `email_template_translations` VALUES (8, 'KO', '<img src=\"%SITE_LOGO_URL%\"><br><h2><font face=''arial''>Your %SITE_NAME% Daily Job Alert</font></h2>\r\n\r\n<p><font size=''2'' face=''arial''>Dear %FNAME% %LNAME%</font></p>\r\n\r\n<font face=''arial'' size=''2''>Your Daily Job Alert on %SITE_NAME% has returned the following alert for you today:</font><p></p>\r\n\r\n<p>\r\n%JOB_ALERTS%\r\n</p>\r\n<p>\r\n<b></b>\r\n</p>\r\n<p><font face=''arial'' size=''2''>%SITE_NAME% Team test@example.com<br>%SITE_CONTACT_EMAIL%</font></p>\r\n<p><font face=''arial'' size=''2''>- If you want to Cancel, or Edit your Daily Job Alerts, please visit this link: <a href=\"%CANDIDATE_LINK%\">%CANDIDATE_LINK%</a></font></p>', 'test@example.com', 'Job Alert', 'Job Board', '<font face=''arial'' size=''2''>%FORMATTED_DATE% - <a href=''%BASE_HTTP_PATH%index.php?post_id=%POST_ID%''>%TITLE%</a></font> (%LOCATION%) <font face=''arial'' size=''1'' color=''#808080''>%DESCRIPTION%</font>');;;
INSERT INTO `email_template_translations` VALUES (8, 'PL', '<img src=\"%SITE_LOGO_URL%\"><br><h2><font face=''arial''>Your %SITE_NAME% Daily Job Alert</font></h2>\r\n\r\n<p><font size=''2'' face=''arial''>Dear %FNAME% %LNAME%</font></p>\r\n\r\n<font face=''arial'' size=''2''>Your Daily Job Alert on %SITE_NAME% has returned the following alert for you today:</font><p></p>\r\n\r\n<p>\r\n%JOB_ALERTS%\r\n</p>\r\n<p>\r\n<b></b>\r\n</p>\r\n<p><font face=''arial'' size=''2''>%SITE_NAME% Team test@example.com<br>%SITE_CONTACT_EMAIL%</font></p>\r\n<p><font face=''arial'' size=''2''>- If you want to Cancel, or Edit your Daily Job Alerts, please visit this link: <a href=\"%CANDIDATE_LINK%\">%CANDIDATE_LINK%</a></font></p>', 'test@example.com', 'Job Alert', 'Job Board', '<font face=''arial'' size=''2''>%FORMATTED_DATE% - <a href=''%BASE_HTTP_PATH%index.php?post_id=%POST_ID%''>%TITLE%</a></font> (%LOCATION%) <font face=''arial'' size=''1'' color=''#808080''>%DESCRIPTION%</font>');;;
INSERT INTO `email_template_translations` VALUES (10, 'CN', 'Application Sent to: %POSTED_BY% (%EMPLOYER_EMAIL%)\r\n\r\nJob Post Titled:  \r\n - %JOB_TITLE%\r\nApplicant:\r\n - %APP_NAME% (%APP_EMAIL%)\r\nsubject:\r\n - %APP_SUBJECT%\r\nLetter:\r\n%APP_LETTER%\r\nAttachments:\r\n%APP_ATTACHMENT1%\r\n%APP_ATTACHMENT2%\r\n%APP_ATTACHMENT3%', 'test@example.com', 'Application Confirmation', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (10, 'EN', 'Application Sent to: %POSTED_BY% (%EMPLOYER_EMAIL%)\r\n\r\nJob Post Titled:  \r\n - %JOB_TITLE%\r\nApplicant:\r\n - %APP_NAME% (%APP_EMAIL%)\r\nsubject:\r\n - %APP_SUBJECT%\r\nLetter:\r\n%APP_LETTER%\r\nAttachments:\r\n%APP_ATTACHMENT1%\r\n%APP_ATTACHMENT2%\r\n%APP_ATTACHMENT3%', 'test@example.com', 'Application Confirmation', 'Example', '');;;
INSERT INTO `email_template_translations` VALUES (10, 'ES', 'Application Sent to: %POSTED_BY% (%EMPLOYER_EMAIL%)\r\n\r\nJob Post Titled:  \r\n - %JOB_TITLE%\r\nApplicant:\r\n - %APP_NAME% (%APP_EMAIL%)\r\nsubject:\r\n - %APP_SUBJECT%\r\nLetter:\r\n%APP_LETTER%\r\nAttachments:\r\n%APP_ATTACHMENT1%\r\n%APP_ATTACHMENT2%\r\n%APP_ATTACHMENT3%', 'test@example.com', 'Application Confirmation', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (10, 'KO', 'Application Sent to: %POSTED_BY% (%EMPLOYER_EMAIL%)\r\n\r\nJob Post Titled:  \r\n - %JOB_TITLE%\r\nApplicant:\r\n - %APP_NAME% (%APP_EMAIL%)\r\nsubject:\r\n - %APP_SUBJECT%\r\nLetter:\r\n%APP_LETTER%\r\nAttachments:\r\n%APP_ATTACHMENT1%\r\n%APP_ATTACHMENT2%\r\n%APP_ATTACHMENT3%', 'test@example.com', 'Application Confirmation', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (10, 'PL', 'Application Sent to: %POSTED_BY% (%EMPLOYER_EMAIL%)\r\n\r\nJob Post Titled:  \r\n - %JOB_TITLE%\r\nApplicant:\r\n - %APP_NAME% (%APP_EMAIL%)\r\nsubject:\r\n - %APP_SUBJECT%\r\nLetter:\r\n%APP_LETTER%\r\nAttachments:\r\n%APP_ATTACHMENT1%\r\n%APP_ATTACHMENT2%\r\n%APP_ATTACHMENT3%', 'test@example.com', 'Application Confirmation', 'Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (60, 'CN', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Bank Deposit\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n        Bank Address: %BANK_ADDRESS%\r\n        SWIFT CODE: %BANK_AC_SWIFT%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\ntest@example.com with the following \r\nOrder Number: %INVOICE_CODE% to help us speed up the process. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'test@example.com', 'Order confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (60, 'EN', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Bank Deposit\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n        Bank Address: %BANK_ADDRESS%\r\n        SWIFT CODE: %BANK_AC_SWIFT%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\ntest@example.com with the following \r\nOrder Number: %INVOICE_CODE% to help us speed up the process. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'test@example.com', 'Order confirmed', 'Example', '');;;
INSERT INTO `email_template_translations` VALUES (60, 'ES', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Bank Deposit\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n        Bank Address: %BANK_ADDRESS%\r\n        SWIFT CODE: %BANK_AC_SWIFT%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\ntest@example.com with the following \r\nOrder Number: %INVOICE_CODE% to help us speed up the process. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'test@example.com', 'Order confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (60, 'KO', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Bank Deposit\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n        Bank Address: %BANK_ADDRESS%\r\n        SWIFT CODE: %BANK_AC_SWIFT%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\ntest@example.com with the following \r\nOrder Number: %INVOICE_CODE% to help us speed up the process. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'test@example.com', 'Order confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (60, 'PL', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Bank Deposit\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n        Bank Address: %BANK_ADDRESS%\r\n        SWIFT CODE: %BANK_AC_SWIFT%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\ntest@example.com with the following \r\nOrder Number: %INVOICE_CODE% to help us speed up the process. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'test@example.com', 'Order confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (61, 'CN', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n\r\nPlease send Check / Money Order to:\r\n	Name: %PAYEE_NAME%\r\n        Address: %PAYEE_ADDRESS%\r\n        Amount: %INVOICE_AMOUNT%\r\n        Currency: %CHECK_CURRENCY%\r\n\r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'test@example.com', 'Confirmed Order', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (61, 'EN', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n\r\nPlease send Check / Money Order to:\r\n	Name: %PAYEE_NAME%\r\n        Address: %PAYEE_ADDRESS%\r\n        Amount: %INVOICE_AMOUNT%\r\n        Currency: %CHECK_CURRENCY%\r\n\r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'test@example.com', 'Confirmed Order', 'Example', '');;;
INSERT INTO `email_template_translations` VALUES (61, 'ES', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n\r\nPlease send Check / Money Order to:\r\n	Name: %PAYEE_NAME%\r\n        Address: %PAYEE_ADDRESS%\r\n        Amount: %INVOICE_AMOUNT%\r\n        Currency: %CHECK_CURRENCY%\r\n\r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'test@example.com', 'Confirmed Order', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (61, 'KO', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n\r\nPlease send Check / Money Order to:\r\n	Name: %PAYEE_NAME%\r\n        Address: %PAYEE_ADDRESS%\r\n        Amount: %INVOICE_AMOUNT%\r\n        Currency: %CHECK_CURRENCY%\r\n\r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'test@example.com', 'Confirmed Order', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (61, 'PL', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n\r\nPlease send Check / Money Order to:\r\n	Name: %PAYEE_NAME%\r\n        Address: %PAYEE_ADDRESS%\r\n        Amount: %INVOICE_AMOUNT%\r\n        Currency: %CHECK_CURRENCY%\r\n\r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'test@example.com', 'Confirmed Order', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (330, 'EN', 'A new order was placed on %SITE_NAME% by %USER%!\r\n\r\nTo manage, see here:\r\n%ADMIN_LINK%\r\n\r\n==================================\r\n\r\nOrder by: %LNAME%, %FNAME%\r\nUsername: %USER%\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nItem: %ITEM_NAME%\r\nOrder ID: #%INVOICE_CODE%\r\nPrice: %INVOICE_AMOUNT%\r\n', 'test@test.com', 'A New order was placed on %SITE_NAME%', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (320, 'EN', 'A Resume resume was posted to / updated on %SITE_NAME%\r\n\r\nAdmin Link: \r\n%ADMIN_LINK%\r\n\r\n%RESUME_SUMMARY%\r\n\r\n\r\n', 'example@example.com', 'A Resume was saved on %SITE_NAME%', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (310, 'EN', 'A new Post was posted to %SITE_NAME%\r\n\r\nAdmin Link: %ADMIN_LINK%\r\n\r\nTitle:\r\n%POST_TITLE%\r\nBy:\r\n%POSTED_BY%\r\nDate:\r\n%DATE%\r\nDescription:\r\n%POST_DESCRIPTION%\r\n', 'test@test.com', 'A new Post was posted to %SITE_NAME%', 'Jamit Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (210, 'EN', 'Dear %FNAME% %LNAME%,\r\n\r\nWe would like to notify you that the following post had expired on %SITE_NAME%:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n\r\nStatistics:\r\n%VIEWS% views\r\n%APPS% Applications\r\n\r\nThis job post will no longer be visible in the job listings. You may log in to your employer''s account to view or re-post this job, or post a new job at any time.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Job post expired', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (220, 'EN', 'Dear %FNAME% %LNAME%,\r\n\r\nThank you for posting your job to %SITE_NAME%!\r\n\r\nWe have just approved the following job to be listed on our site:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n%POST_URL%\r\n\r\n\r\nThis job post will now become visible on the job listings. You may log in to your employer''s account to view or edit this job at any time.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Your job posting was Approved!', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (230, 'EN', 'Dear %FNAME% %LNAME%,\r\n\r\nThank you for posting your job to %SITE_NAME%.\r\n\r\nHowever, after reviewing your job post, we have decided to disapprove it.\r\n\r\nThe following job post was disapproved:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n\r\nReason for disapproval: %REASON%\r\n\r\nYou may log in to your employer''s account to edit this job so that we may review it again.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Your job posting was disapproved', 'Jamit Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (12, 'CN', '%APP_LETTER% \r\n\r\nThis email was sent from %SITE_NAME% %BASE_HTTP_PATH%\r\nOnline Resume Link: \r\n%RESUME_DB_LINK%\r\n', '', '', '', '');;;
INSERT INTO `email_template_translations` VALUES (12, 'EN', '%APP_LETTER% \r\n\r\nThis email was sent from %SITE_NAME% %BASE_HTTP_PATH%\r\nOnline Resume Link: \r\n%RESUME_DB_LINK%\r\n', '', '', '', '');;;
INSERT INTO `email_template_translations` VALUES (12, 'ES', '%APP_LETTER% \r\n\r\nThis email was sent from %SITE_NAME% %BASE_HTTP_PATH%\r\nOnline Resume Link: \r\n%RESUME_DB_LINK%\r\n', '', '', '', '');;;
INSERT INTO `email_template_translations` VALUES (12, 'KO', '%APP_LETTER% \r\n\r\nThis email was sent from %SITE_NAME% %BASE_HTTP_PATH%\r\nOnline Resume Link: \r\n%RESUME_DB_LINK%\r\n', '', '', '', '');;;
INSERT INTO `email_template_translations` VALUES (12, 'PL', '%APP_LETTER% \r\n\r\nThis email was sent from %SITE_NAME% %BASE_HTTP_PATH%\r\nOnline Resume Link: \r\n%RESUME_DB_LINK%\r\n', '', '', '', '');;;
INSERT INTO `email_template_translations` VALUES (12, 'FR', '%APP_LETTER% \r\n\r\nThis email was sent from %SITE_NAME% %BASE_HTTP_PATH%\r\nOnline Resume Link: \r\n%RESUME_DB_LINK%\r\n', '', '', '', '');;;
INSERT INTO `email_template_translations` VALUES (11, 'CN', '%MESSAGE%\r\n\r\n\r\n\r\n\r\n%SITE_URL%\r\n\r\nThis message was sent by somebody using the \r\nweb-email service provided by %SITE_NAME%.\r\n\r\nName: %EMPLOYER_NAME%\r\nSender''s User ID: %USER_ID%\r\nSender IP: %SENDER_IP%\r\n', 'example@example.com', '', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (11, 'EN', '%MESSAGE%\r\n\r\n\r\n\r\n\r\n%SITE_URL%\r\n\r\nThis message was sent by somebody using the \r\nweb-email service provided by %SITE_NAME%.\r\n\r\nName: %EMPLOYER_NAME%\r\nSender''s User ID: %USER_ID%\r\nSender IP: %SENDER_IP%\r\n', 'example@example.com', '', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (11, 'ES', '%MESSAGE%\r\n\r\n\r\n\r\n\r\n%SITE_URL%\r\n\r\nThis message was sent by somebody using the \r\nweb-email service provided by %SITE_NAME%.\r\n\r\nName: %EMPLOYER_NAME%\r\nSender''s User ID: %USER_ID%\r\nSender IP: %SENDER_IP%\r\n', 'example@example.com', '', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (11, 'KO', '%MESSAGE%\r\n\r\n\r\n\r\n\r\n%SITE_URL%\r\n\r\nThis message was sent by somebody using the \r\nweb-email service provided by %SITE_NAME%.\r\n\r\nName: %EMPLOYER_NAME%\r\nSender''s User ID: %USER_ID%\r\nSender IP: %SENDER_IP%\r\n', 'example@example.com', '', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (11, 'PL', '%MESSAGE%\r\n\r\n\r\n\r\n\r\n%SITE_URL%\r\n\r\nThis message was sent by somebody using the \r\nweb-email service provided by %SITE_NAME%.\r\n\r\nName: %EMPLOYER_NAME%\r\nSender''s User ID: %USER_ID%\r\nSender IP: %SENDER_IP%\r\n', 'example@example.com', '', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (11, 'FR', '%MESSAGE%\r\n\r\n\r\n\r\n\r\n%SITE_URL%\r\n\r\nThis message was sent by somebody using the \r\nweb-email service provided by %SITE_NAME%.\r\n\r\nName: %EMPLOYER_NAME%\r\nSender''s User ID: %USER_ID%\r\nSender IP: %SENDER_IP%\r\n', 'example@example.com', '', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (1, 'FR', 'Dear %FNAME% %LNAME%,\r\n\r\nYou have successfully signed up to %SITE_NAME%.\r\n\r\nIf you ever encounter any problems, bugs or just have \r\nany questions or suggestions, feel free to contact \r\nus: %SITE_CONTACT_EMAIL%;\r\n\r\n\r\nYou have registered with the following details - \r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD% \r\n\r\nhttp://www.example.com\r\n\r\n&#54620;&#44397;&#50612;', 'test@example.com', 'Successfully Signed Up as %MEMBERID%', 'Example', '');;;
INSERT INTO `email_template_translations` VALUES (2, 'FR', 'Dear %FNAME% %LNAME%,\r\n\r\nYou have successfully signed for a %SITE_NAME% Employer''s Account. \r\nYou have registered with the following details - \r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD% \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have any questions / problems. \r\n\r\nKind Regards,\r\n\r\nWebmaster.', 'test@example.com', 'Successfully Signed Up as %MEMBERID%', 'Example', '');;;
INSERT INTO `email_template_translations` VALUES (3, 'FR', 'Dear %FNAME% %LNAME%,\r\n\r\nYour %SITE_NAME% password has been reset!\r\n\r\nHere is your new password:\r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD%\r\n\r\nYou can sign into your account here: http://www.example.com/ \r\n\r\nKind Regards,\r\n\r\nWebmaster', 'test@example.com', 'Reset Password', 'Example', '');;;
INSERT INTO `email_template_translations` VALUES (4, 'FR', 'Dear %FNAME% %LNAME%,\r\n\r\nAn employer on %SITE_NAME% has requested for your contact details!\r\nHere are the details of the request:\r\n\r\nEmployer Name: %EMPLOYER_NAME%\r\nReply-to Email Address: %REPLY_TO%\r\n%MESSAGE%\r\n\r\nYou may reveal your contact details to this employer by simply visiting the following link: %PERMIT_LINK%\r\n\r\nYou may also contact the employer directly by replying to this email!\r\n\r\n\r\nBest Regards,\r\n\r\nTeam %SITE_NAME%', 'test@example.com', 'Request for your contact details by an employer', 'Example', '');;;
INSERT INTO `email_template_translations` VALUES (5, 'FR', 'Your %SITE_NAME% Daily Resume Alert!\r\n\r\nDear %FNAME% %LNAME%,\r\n\r\nYour Resume Alert on %SITE_NAME% has returned the following alert for you today:\r\n\r\n%RESUME_ALERTS% \r\n\r\n\r\n\r\n%SITE_NAME% Team %SITE_CONTACT_EMAIL%\r\n- If you want to View these resumes,or maintain your Daily Resume Alerts, \r\nplease visit this link:\r\n%EMPLOYER_LINK% \r\n', 'test@example.com', 'Daily C.V. Alert', 'Example', '%DATE% : %RESUME_NAME% (%NATIONALITY%)');;;
INSERT INTO `email_template_translations` VALUES (6, 'FR', '<h2><font face=''arial''>Your %SITE_NAME% Daily Resume Alert!</font></h2><p>\r\n<p><font size=''2'' face=''arial''>Dear %FNAME% %LNAME%,</font></p>\r\n<font face=''arial'' size=''2''>Your Resume Alert on %SITE_NAME% has returned the following alert for you today:</font><p>%RESUME_ALERTS%\r\n</p>\r\n<p>\r\n<p><font size=''2'' face=''arial''><b></b></font></p><p>\r\n<font face=\"arial\" size=\"2\">%SITE_NAME% Team %SITE_CONTACT_EMAIL%</font></p><p>\r\n<font face=\"arial\" size=\"2\">- If you want to View these resumes, or maintain your Daily Resume Alerts, please visit this link: %EMPLOYER_LINK%</a>\r\n</font></p>\r\n', 'test@example.com', 'Resume Alert', 'Example', '<font face=''arial'' size=''2''>%DATE% - %RESUME_NAME% </font>');;;
INSERT INTO `email_template_translations` VALUES (7, 'FR', 'Your SITE_NAME Daily Job Alert\r\n\r\nDear %FNAME% %LNAME%\r\n\r\nYour Daily Job Alert on %SITE_NAME% has returned the following alert for you today:\r\n\r\n%JOB_ALERTS% \r\n\r\n\r\n \r\n\r\n%SITE_NAME% Team test@example.com\r\n\r\n%SITE_CONTACT_EMAIL%\r\n- If you want to Cancel, or Edit your Daily Job Alerts, please visit this link: %CANDIDATE_LINK%\r\n', 'test@example.com', 'Job Alert', 'Job Board', '%FORMATTED_DATE% : %TITLE% (%LOCATION%)\r\nLink: %BASE_HTTP_PATH%index.php?post_id=%POST_ID%');;;
INSERT INTO `email_template_translations` VALUES (8, 'FR', '<img src=\"%SITE_LOGO_URL%\"><br><h2><font face=''arial''>Your %SITE_NAME% Daily Job Alert</font></h2>\r\n\r\n<p><font size=''2'' face=''arial''>Dear %FNAME% %LNAME%</font></p>\r\n\r\n<font face=''arial'' size=''2''>Your Daily Job Alert on %SITE_NAME% has returned the following alert for you today:</font><p></p>\r\n\r\n<p>\r\n%JOB_ALERTS%\r\n</p>\r\n<p>\r\n<b></b>\r\n</p>\r\n<p><font face=''arial'' size=''2''>%SITE_NAME% Team test@example.com<br>%SITE_CONTACT_EMAIL%</font></p>\r\n<p><font face=''arial'' size=''2''>- If you want to Cancel, or Edit your Daily Job Alerts, please visit this link: <a href=\"%CANDIDATE_LINK%\">%CANDIDATE_LINK%</a></font></p>', 'test@example.com', 'Job Alert', 'Example', '<font face=''arial'' size=''2''>%FORMATTED_DATE% - <a href=''%BASE_HTTP_PATH%index.php?post_id=%POST_ID%''>%TITLE%</a></font> (%LOCATION%) <font face=''arial'' size=''1'' color=''#808080''>%DESCRIPTION%</font>');;;
INSERT INTO `email_template_translations` VALUES (10, 'FR', 'Application Sent to: %POSTED_BY% (%EMPLOYER_EMAIL%)\r\n\r\nJob Post Titled:  \r\n - %JOB_TITLE%\r\nApplicant:\r\n - %APP_NAME% (%APP_EMAIL%)\r\nsubject:\r\n - %APP_SUBJECT%\r\nLetter:\r\n%APP_LETTER%\r\nAttachments:\r\n%APP_ATTACHMENT1%\r\n%APP_ATTACHMENT2%\r\n%APP_ATTACHMENT3%', 'test@example.com', 'Application Confirmation', 'Example', '');;;
INSERT INTO `email_template_translations` VALUES (60, 'FR', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nItem Name: %ITEM_NAME%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Bank Deposit\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'webmaster@hiteacher.com', 'Order Confirmed', 'Hi Teacher', '');;;
INSERT INTO `email_template_translations` VALUES (61, 'FR', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nItem Name: %ITEM_NAME%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Order Confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (70, 'CN', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have credited your order to your account, and you may now use your available balance to post your job advertisement(s) to %SITE_NAME%.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nItem Name: %ITEM_NAME%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Completed\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your balance and order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Posts'' -> ''Posting Credits''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Order completed!', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (70, 'EN', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have credited your order to your account, and you may now use your available balance to post your job advertisement(s) to %SITE_NAME%.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nItem Name: %ITEM_NAME%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Completed\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your balance and order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Posts'' -> ''Posting Credits''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Order completed!', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (70, 'ES', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have credited your order to your account, and you may now use your available balance to post your job advertisement(s) to %SITE_NAME%.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nItem Name: %ITEM_NAME%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Completed\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your balance and order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Posts'' -> ''Posting Credits''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Order completed!', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (70, 'KO', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have credited your order to your account, and you may now use your available balance to post your job advertisement(s) to %SITE_NAME%.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nItem Name: %ITEM_NAME%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Completed\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your balance and order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Posts'' -> ''Posting Credits''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Order completed!', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (70, 'PL', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have credited your order to your account, and you may now use your available balance to post your job advertisement(s) to %SITE_NAME%.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nItem Name: %ITEM_NAME%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Completed\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your balance and order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Posts'' -> ''Posting Credits''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Order completed!', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (70, 'FR', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have credited your order to your account, and you may now use your available balance to post your job advertisement(s) to %SITE_NAME%.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nItem Name: %ITEM_NAME%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Completed\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your balance and order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Posts'' -> ''Posting Credits''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Order completed!', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (90, 'CN', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour subscription payment on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have activated your subscription, and you may now log in to your account\r\nto access the resume database.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nStart Date: %SUB_START%\r\nEnd Date: %SUB_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your subscription order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Resumes'' -> ''Subscriptions''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Subscription now active!', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (90, 'EN', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour subscription payment on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have activated your subscription, and you may now log in to your account\r\nto access the resume database.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nStart Date: %SUB_START%\r\nEnd Date: %SUB_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your subscription order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Resumes'' -> ''Subscriptions''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Subscription now active!', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (90, 'ES', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour subscription payment on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have activated your subscription, and you may now log in to your account\r\nto access the resume database.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nStart Date: %SUB_START%\r\nEnd Date: %SUB_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your subscription order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Resumes'' -> ''Subscriptions''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Subscription now active!', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (90, 'KO', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour subscription payment on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have activated your subscription, and you may now log in to your account\r\nto access the resume database.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nStart Date: %SUB_START%\r\nEnd Date: %SUB_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your subscription order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Resumes'' -> ''Subscriptions''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Subscription now active!', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (90, 'PL', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour subscription payment on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have activated your subscription, and you may now log in to your account\r\nto access the resume database.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nStart Date: %SUB_START%\r\nEnd Date: %SUB_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your subscription order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Resumes'' -> ''Subscriptions''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Subscription now active!', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (90, 'FR', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour subscription payment on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have activated your subscription, and you may now log in to your account\r\nto access the resume database.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nStart Date: %SUB_START%\r\nEnd Date: %SUB_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your subscription order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Resumes'' -> ''Subscriptions''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Subscription now active!', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (80, 'CN', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour subscription on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: Bank\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n', 'example@example.com', 'Subscription order confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (80, 'EN', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour subscription on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: Bank\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n', 'example@example.com', 'Subscription order confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (80, 'ES', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour subscription on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: Bank\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n', 'example@example.com', 'Subscription order confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (80, 'KO', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour subscription on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: Bank\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n', 'example@example.com', 'Subscription order confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (80, 'PL', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour subscription on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: Bank\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n', 'example@example.com', 'Subscription order confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (80, 'FR', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour subscription on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: Bank\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n', 'example@example.com', 'Subscription order confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (81, 'CN', 'Dear %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email', 'example@example.com', 'Subscription order confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (81, 'EN', 'Dear %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email', 'example@example.com', 'Subscription order confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (81, 'ES', 'Dear %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email', 'example@example.com', 'Subscription order confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (81, 'KO', 'Dear %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email', 'example@example.com', 'Subscription order confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (81, 'PL', 'Dear %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email', 'example@example.com', 'Subscription order confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (81, 'FR', 'Dear %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email', 'example@example.com', 'Subscription order confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (120, 'CN', 'Dear  %LNAME%, %FNAME%\r\n\r\nThis email is sent to notify you that your membership to %SITE_NAME% has expired.\r\n\r\nWe thank you for your patronage during your membership time, and we hope\r\nthat we can continue to serve you as our member in the future.\r\n\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nStart Date: %MEM_START%\r\nEnd Date: %MEM_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Expired\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your membership order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Membership Details''.\r\n\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Membership Expired', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (120, 'EN', 'Dear  %LNAME%, %FNAME%\r\n\r\nThis email is sent to notify you that your membership to %SITE_NAME% has expired.\r\n\r\nWe thank you for your patronage during your membership time, and we hope\r\nthat we can continue to serve you as our member in the future.\r\n\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nStart Date: %MEM_START%\r\nEnd Date: %MEM_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Expired\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your membership order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Membership Details''.\r\n\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Membership Expired', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (120, 'ES', 'Dear  %LNAME%, %FNAME%\r\n\r\nThis email is sent to notify you that your membership to %SITE_NAME% has expired.\r\n\r\nWe thank you for your patronage during your membership time, and we hope\r\nthat we can continue to serve you as our member in the future.\r\n\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nStart Date: %MEM_START%\r\nEnd Date: %MEM_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Expired\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your membership order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Membership Details''.\r\n\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Membership Expired', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (120, 'KO', 'Dear  %LNAME%, %FNAME%\r\n\r\nThis email is sent to notify you that your membership to %SITE_NAME% has expired.\r\n\r\nWe thank you for your patronage during your membership time, and we hope\r\nthat we can continue to serve you as our member in the future.\r\n\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nStart Date: %MEM_START%\r\nEnd Date: %MEM_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Expired\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your membership order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Membership Details''.\r\n\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Membership Expired', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (120, 'PL', 'Dear  %LNAME%, %FNAME%\r\n\r\nThis email is sent to notify you that your membership to %SITE_NAME% has expired.\r\n\r\nWe thank you for your patronage during your membership time, and we hope\r\nthat we can continue to serve you as our member in the future.\r\n\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nStart Date: %MEM_START%\r\nEnd Date: %MEM_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Expired\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your membership order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Membership Details''.\r\n\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Membership Expired', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (120, 'FR', 'Dear  %LNAME%, %FNAME%\r\n\r\nThis email is sent to notify you that your membership to %SITE_NAME% has expired.\r\n\r\nWe thank you for your patronage during your membership time, and we hope\r\nthat we can continue to serve you as our member in the future.\r\n\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nStart Date: %MEM_START%\r\nEnd Date: %MEM_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Expired\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your membership order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Membership Details''.\r\n\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Membership Expired', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (100, 'CN', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour membership on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Item: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Bank\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n', 'example@example.com', 'Membership Order Confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (100, 'EN', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour membership on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Item: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Bank\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n', 'example@example.com', 'Membership Order Confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (100, 'ES', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour membership on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Item: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Bank\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n', 'example@example.com', 'Membership Order Confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (100, 'KO', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour membership on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Item: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Bank\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n', 'example@example.com', 'Membership Order Confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (100, 'PL', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour membership on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Item: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Bank\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n', 'example@example.com', 'Membership Order Confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (100, 'FR', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour membership on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Item: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Bank\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n', 'example@example.com', 'Membership Order Confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (101, 'CN', 'Dear %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email', 'example@example.com', 'Membership Order Confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (101, 'EN', 'Dear %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email', 'example@example.com', 'Membership Order Confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (101, 'ES', 'Dear %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email', 'example@example.com', 'Membership Order Confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (101, 'KO', 'Dear %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email', 'example@example.com', 'Membership Order Confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (101, 'PL', 'Dear %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email', 'example@example.com', 'Membership Order Confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (101, 'FR', 'Dear %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email', 'example@example.com', 'Membership Order Confirmed', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (110, 'CN', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour membership payment on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have activated your membership, and we welcome you as our new member. \r\nHere are your membership payment details:\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nStart Date: %MEM_START%\r\nEnd Date: %MEM_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your membership order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Membership Details''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Membership Activated', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (110, 'EN', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour membership payment on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have activated your membership, and we welcome you as our new member. \r\nHere are your membership payment details:\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nStart Date: %MEM_START%\r\nEnd Date: %MEM_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your membership order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Membership Details''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Membership Activated', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (110, 'ES', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour membership payment on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have activated your membership, and we welcome you as our new member. \r\nHere are your membership payment details:\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nStart Date: %MEM_START%\r\nEnd Date: %MEM_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your membership order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Membership Details''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Membership Activated', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (110, 'KO', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour membership payment on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have activated your membership, and we welcome you as our new member. \r\nHere are your membership payment details:\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nStart Date: %MEM_START%\r\nEnd Date: %MEM_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your membership order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Membership Details''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Membership Activated', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (110, 'PL', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour membership payment on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have activated your membership, and we welcome you as our new member. \r\nHere are your membership payment details:\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nStart Date: %MEM_START%\r\nEnd Date: %MEM_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your membership order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Membership Details''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Membership Activated', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (110, 'FR', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour membership payment on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have activated your membership, and we welcome you as our new member. \r\nHere are your membership payment details:\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nStart Date: %MEM_START%\r\nEnd Date: %MEM_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your membership order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Membership Details''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Membership Activated', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (130, 'CN', 'Dear  %LNAME%, %FNAME%\r\n\r\nThis email is sent to notify you that your subscription to %SITE_NAME% has expired.\r\n\r\nWe thank you for your patronage during your subscription time, and we hope\r\nthat we can continue to serve you as our subscriber in the future.\r\n\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nStart Date: %SUB_START%\r\nEnd Date: %SUB_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Expired\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your subscription order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Subscription''.\r\n\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Subscription Expired', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (130, 'EN', 'Dear  %LNAME%, %FNAME%\r\n\r\nThis email is sent to notify you that your subscription to %SITE_NAME% has expired.\r\n\r\nWe thank you for your patronage during your subscription time, and we hope\r\nthat we can continue to serve you as our subscriber in the future.\r\n\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nStart Date: %SUB_START%\r\nEnd Date: %SUB_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Expired\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your subscription order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Subscription''.\r\n\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Subscription Expired', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (130, 'ES', 'Dear  %LNAME%, %FNAME%\r\n\r\nThis email is sent to notify you that your subscription to %SITE_NAME% has expired.\r\n\r\nWe thank you for your patronage during your subscription time, and we hope\r\nthat we can continue to serve you as our subscriber in the future.\r\n\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nStart Date: %SUB_START%\r\nEnd Date: %SUB_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Expired\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your subscription order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Subscription''.\r\n\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Subscription Expired', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (130, 'KO', 'Dear  %LNAME%, %FNAME%\r\n\r\nThis email is sent to notify you that your subscription to %SITE_NAME% has expired.\r\n\r\nWe thank you for your patronage during your subscription time, and we hope\r\nthat we can continue to serve you as our subscriber in the future.\r\n\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nStart Date: %SUB_START%\r\nEnd Date: %SUB_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Expired\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your subscription order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Subscription''.\r\n\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Subscription Expired', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (130, 'PL', 'Dear  %LNAME%, %FNAME%\r\n\r\nThis email is sent to notify you that your subscription to %SITE_NAME% has expired.\r\n\r\nWe thank you for your patronage during your subscription time, and we hope\r\nthat we can continue to serve you as our subscriber in the future.\r\n\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nStart Date: %SUB_START%\r\nEnd Date: %SUB_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Expired\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your subscription order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Subscription''.\r\n\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Subscription Expired', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (130, 'FR', 'Dear  %LNAME%, %FNAME%\r\n\r\nThis email is sent to notify you that your subscription to %SITE_NAME% has expired.\r\n\r\nWe thank you for your patronage during your subscription time, and we hope\r\nthat we can continue to serve you as our subscriber in the future.\r\n\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nStart Date: %SUB_START%\r\nEnd Date: %SUB_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Expired\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your subscription order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Subscription''.\r\n\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Subscription Expired', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (330, 'CN', 'A new order was placed on %SITE_NAME% by %USER%!\r\n\r\nTo manage, see here:\r\n%ADMIN_LINK%\r\n\r\n==================================\r\n\r\nOrder by: %LNAME%, %FNAME%\r\nUsername: %USER%\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nItem: %ITEM_NAME%\r\nOrder ID: #%INVOICE_CODE%\r\nPrice: %INVOICE_AMOUNT%\r\n', 'test@test.com', 'A New order was placed on %SITE_NAME%', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (330, 'ES', 'A new order was placed on %SITE_NAME% by %USER%!\r\n\r\nTo manage, see here:\r\n%ADMIN_LINK%\r\n\r\n==================================\r\n\r\nOrder by: %LNAME%, %FNAME%\r\nUsername: %USER%\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nItem: %ITEM_NAME%\r\nOrder ID: #%INVOICE_CODE%\r\nPrice: %INVOICE_AMOUNT%\r\n', 'test@test.com', 'A New order was placed on %SITE_NAME%', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (330, 'KO', 'A new order was placed on %SITE_NAME% by %USER%!\r\n\r\nTo manage, see here:\r\n%ADMIN_LINK%\r\n\r\n==================================\r\n\r\nOrder by: %LNAME%, %FNAME%\r\nUsername: %USER%\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nItem: %ITEM_NAME%\r\nOrder ID: #%INVOICE_CODE%\r\nPrice: %INVOICE_AMOUNT%\r\n', 'test@test.com', 'A New order was placed on %SITE_NAME%', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (330, 'PL', 'A new order was placed on %SITE_NAME% by %USER%!\r\n\r\nTo manage, see here:\r\n%ADMIN_LINK%\r\n\r\n==================================\r\n\r\nOrder by: %LNAME%, %FNAME%\r\nUsername: %USER%\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nItem: %ITEM_NAME%\r\nOrder ID: #%INVOICE_CODE%\r\nPrice: %INVOICE_AMOUNT%\r\n', 'test@test.com', 'A New order was placed on %SITE_NAME%', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (330, 'FR', 'A new order was placed on %SITE_NAME% by %USER%!\r\n\r\nTo manage, see here:\r\n%ADMIN_LINK%\r\n\r\n==================================\r\n\r\nOrder by: %LNAME%, %FNAME%\r\nUsername: %USER%\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nItem: %ITEM_NAME%\r\nOrder ID: #%INVOICE_CODE%\r\nPrice: %INVOICE_AMOUNT%\r\n', 'test@test.com', 'A New order was placed on %SITE_NAME%', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (320, 'CN', 'A Resume resume was posted to / updated on %SITE_NAME%\r\n\r\nAdmin Link: \r\n%ADMIN_LINK%\r\n\r\n%RESUME_SUMMARY%\r\n\r\n\r\n', 'example@example.com', 'A Resume was saved on %SITE_NAME%', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (320, 'ES', 'A Resume resume was posted to / updated on %SITE_NAME%\r\n\r\nAdmin Link: \r\n%ADMIN_LINK%\r\n\r\n%RESUME_SUMMARY%\r\n\r\n\r\n', 'example@example.com', 'A Resume was saved on %SITE_NAME%', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (320, 'KO', 'A Resume resume was posted to / updated on %SITE_NAME%\r\n\r\nAdmin Link: \r\n%ADMIN_LINK%\r\n\r\n%RESUME_SUMMARY%\r\n\r\n\r\n', 'example@example.com', 'A Resume was saved on %SITE_NAME%', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (320, 'PL', 'A Resume resume was posted to / updated on %SITE_NAME%\r\n\r\nAdmin Link: \r\n%ADMIN_LINK%\r\n\r\n%RESUME_SUMMARY%\r\n\r\n\r\n', 'example@example.com', 'A Resume was saved on %SITE_NAME%', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (320, 'FR', 'A Resume resume was posted to / updated on %SITE_NAME%\r\n\r\nAdmin Link: \r\n%ADMIN_LINK%\r\n\r\n%RESUME_SUMMARY%\r\n\r\n\r\n', 'example@example.com', 'A Resume was saved on %SITE_NAME%', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (310, 'CN', 'A new Post was posted to %SITE_NAME%\r\n\r\nAdmin Link: %ADMIN_LINK%\r\n\r\nTitle:\r\n%POST_TITLE%\r\nBy:\r\n%POSTED_BY%\r\nDate:\r\n%DATE%\r\nDescription:\r\n%POST_DESCRIPTION%\r\n', 'test@test.com', 'A new Post was posted to %SITE_NAME%', 'Jamit Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (310, 'ES', 'A new Post was posted to %SITE_NAME%\r\n\r\nAdmin Link: %ADMIN_LINK%\r\n\r\nTitle:\r\n%POST_TITLE%\r\nBy:\r\n%POSTED_BY%\r\nDate:\r\n%DATE%\r\nDescription:\r\n%POST_DESCRIPTION%\r\n', 'test@test.com', 'A new Post was posted to %SITE_NAME%', 'Jamit Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (310, 'KO', 'A new Post was posted to %SITE_NAME%\r\n\r\nAdmin Link: %ADMIN_LINK%\r\n\r\nTitle:\r\n%POST_TITLE%\r\nBy:\r\n%POSTED_BY%\r\nDate:\r\n%DATE%\r\nDescription:\r\n%POST_DESCRIPTION%\r\n', 'test@test.com', 'A new Post was posted to %SITE_NAME%', 'Jamit Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (310, 'PL', 'A new Post was posted to %SITE_NAME%\r\n\r\nAdmin Link: %ADMIN_LINK%\r\n\r\nTitle:\r\n%POST_TITLE%\r\nBy:\r\n%POSTED_BY%\r\nDate:\r\n%DATE%\r\nDescription:\r\n%POST_DESCRIPTION%\r\n', 'test@test.com', 'A new Post was posted to %SITE_NAME%', 'Jamit Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (310, 'FR', 'A new Post was posted to %SITE_NAME%\r\n\r\nAdmin Link: %ADMIN_LINK%\r\n\r\nTitle:\r\n%POST_TITLE%\r\nBy:\r\n%POSTED_BY%\r\nDate:\r\n%DATE%\r\nDescription:\r\n%POST_DESCRIPTION%\r\n', 'test@test.com', 'A new Post was posted to %SITE_NAME%', 'Jamit Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (210, 'CN', 'Dear %FNAME% %LNAME%,\r\n\r\nWe would like to notify you that the following post had expired on %SITE_NAME%:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n\r\nStatistics:\r\n%VIEWS% views\r\n%APPS% Applications\r\n\r\nThis job post will no longer be visible in the job listings. You may log in to your employer''s account to view or re-post this job, or post a new job at any time.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Job post expired', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (210, 'ES', 'Dear %FNAME% %LNAME%,\r\n\r\nWe would like to notify you that the following post had expired on %SITE_NAME%:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n\r\nStatistics:\r\n%VIEWS% views\r\n%APPS% Applications\r\n\r\nThis job post will no longer be visible in the job listings. You may log in to your employer''s account to view or re-post this job, or post a new job at any time.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Job post expired', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (210, 'KO', 'Dear %FNAME% %LNAME%,\r\n\r\nWe would like to notify you that the following post had expired on %SITE_NAME%:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n\r\nStatistics:\r\n%VIEWS% views\r\n%APPS% Applications\r\n\r\nThis job post will no longer be visible in the job listings. You may log in to your employer''s account to view or re-post this job, or post a new job at any time.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Job post expired', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (210, 'PL', 'Dear %FNAME% %LNAME%,\r\n\r\nWe would like to notify you that the following post had expired on %SITE_NAME%:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n\r\nStatistics:\r\n%VIEWS% views\r\n%APPS% Applications\r\n\r\nThis job post will no longer be visible in the job listings. You may log in to your employer''s account to view or re-post this job, or post a new job at any time.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Job post expired', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (210, 'FR', 'Dear %FNAME% %LNAME%,\r\n\r\nWe would like to notify you that the following post had expired on %SITE_NAME%:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n\r\nStatistics:\r\n%VIEWS% views\r\n%APPS% Applications\r\n\r\nThis job post will no longer be visible in the job listings. You may log in to your employer''s account to view or re-post this job, or post a new job at any time.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Job post expired', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (220, 'CN', 'Dear %FNAME% %LNAME%,\r\n\r\nThank you for posting your job to %SITE_NAME%!\r\n\r\nWe have just approved the following job to be listed on our site:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n%POST_URL%\r\n\r\n\r\nThis job post will now become visible on the job listings. You may log in to your employer''s account to view or edit this job at any time.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Your job posting was Approved!', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (220, 'ES', 'Dear %FNAME% %LNAME%,\r\n\r\nThank you for posting your job to %SITE_NAME%!\r\n\r\nWe have just approved the following job to be listed on our site:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n%POST_URL%\r\n\r\n\r\nThis job post will now become visible on the job listings. You may log in to your employer''s account to view or edit this job at any time.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Your job posting was Approved!', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (220, 'KO', 'Dear %FNAME% %LNAME%,\r\n\r\nThank you for posting your job to %SITE_NAME%!\r\n\r\nWe have just approved the following job to be listed on our site:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n%POST_URL%\r\n\r\n\r\nThis job post will now become visible on the job listings. You may log in to your employer''s account to view or edit this job at any time.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Your job posting was Approved!', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (220, 'PL', 'Dear %FNAME% %LNAME%,\r\n\r\nThank you for posting your job to %SITE_NAME%!\r\n\r\nWe have just approved the following job to be listed on our site:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n%POST_URL%\r\n\r\n\r\nThis job post will now become visible on the job listings. You may log in to your employer''s account to view or edit this job at any time.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Your job posting was Approved!', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (220, 'FR', 'Dear %FNAME% %LNAME%,\r\n\r\nThank you for posting your job to %SITE_NAME%!\r\n\r\nWe have just approved the following job to be listed on our site:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n%POST_URL%\r\n\r\n\r\nThis job post will now become visible on the job listings. You may log in to your employer''s account to view or edit this job at any time.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Your job posting was Approved!', 'Jamit Demo', '');;;
INSERT INTO `email_template_translations` VALUES (230, 'CN', 'Dear %FNAME% %LNAME%,\r\n\r\nThank you for posting your job to %SITE_NAME%.\r\n\r\nHowever, after reviewing your job post, we have decided to disapprove it.\r\n\r\nThe following job post was disapproved:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n\r\nReason for disapproval: %REASON%\r\n\r\nYou may log in to your employer''s account to edit this job so that we may review it again.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Your job posting was disapproved', 'Jamit Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (230, 'ES', 'Dear %FNAME% %LNAME%,\r\n\r\nThank you for posting your job to %SITE_NAME%.\r\n\r\nHowever, after reviewing your job post, we have decided to disapprove it.\r\n\r\nThe following job post was disapproved:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n\r\nReason for disapproval: %REASON%\r\n\r\nYou may log in to your employer''s account to edit this job so that we may review it again.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Your job posting was disapproved', 'Jamit Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (230, 'KO', 'Dear %FNAME% %LNAME%,\r\n\r\nThank you for posting your job to %SITE_NAME%.\r\n\r\nHowever, after reviewing your job post, we have decided to disapprove it.\r\n\r\nThe following job post was disapproved:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n\r\nReason for disapproval: %REASON%\r\n\r\nYou may log in to your employer''s account to edit this job so that we may review it again.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Your job posting was disapproved', 'Jamit Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (230, 'PL', 'Dear %FNAME% %LNAME%,\r\n\r\nThank you for posting your job to %SITE_NAME%.\r\n\r\nHowever, after reviewing your job post, we have decided to disapprove it.\r\n\r\nThe following job post was disapproved:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n\r\nReason for disapproval: %REASON%\r\n\r\nYou may log in to your employer''s account to edit this job so that we may review it again.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Your job posting was disapproved', 'Jamit Job Board', '');;;
INSERT INTO `email_template_translations` VALUES (230, 'FR', 'Dear %FNAME% %LNAME%,\r\n\r\nThank you for posting your job to %SITE_NAME%.\r\n\r\nHowever, after reviewing your job post, we have decided to disapprove it.\r\n\r\nThe following job post was disapproved:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n\r\nReason for disapproval: %REASON%\r\n\r\nYou may log in to your employer''s account to edit this job so that we may review it again.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Your job posting was disapproved', 'Jamit Job Board', '');;;


INSERT INTO `email_template_translations` (`EmailID`, `lang`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (44,  'EN', 'Hello %EMP_NAME%\r\n\r\n%CAN_NAME% has granted you access to their online resume on %SITE_NAME%!\r\n\r\nTo view this resume, please see this link:\r\n\r\nResume link: %RESUME_DB_LINK%\r\n\r\nThanks,\r\n\r\n%SITE_NAME%\r\n%SITE_URL%\r\n-------------\r\n\r\n', 'example@example.com', '%CAN_NAME% granted you access to their resume on %SITE_NAME% ', 'Jamit Demo', '');;;

INSERT INTO `email_template_translations` (`EmailID`, `lang`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (44,  'FR', 'Hello %EMP_NAME%\r\n\r\n%CAN_NAME% has granted you access to their online resume on %SITE_NAME%!\r\n\r\nTo view this resume, please see this link:\r\n\r\nResume link: %RESUME_DB_LINK%\r\n\r\nThanks,\r\n\r\n%SITE_NAME%\r\n%SITE_URL%\r\n-------------\r\n\r\n', 'example@example.com', '%CAN_NAME% granted you access to their resume on %SITE_NAME% ', 'Jamit Demo', '');;;

INSERT INTO `email_template_translations` (`EmailID`, `lang`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (44,  'KO', 'Hello %EMP_NAME%\r\n\r\n%CAN_NAME% has granted you access to their online resume on %SITE_NAME%!\r\n\r\nTo view this resume, please see this link:\r\n\r\nResume link: %RESUME_DB_LINK%\r\n\r\nThanks,\r\n\r\n%SITE_NAME%\r\n%SITE_URL%\r\n-------------\r\n\r\n', 'example@example.com', '%CAN_NAME% granted you access to their resume on %SITE_NAME% ', 'Jamit Demo', '');;;

INSERT INTO `email_template_translations` (`EmailID`, `lang`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (44, 'ES', 'Hello %EMP_NAME%\r\n\r\n%CAN_NAME% has granted you access to their online resume on %SITE_NAME%!\r\n\r\nTo view this resume, please see this link:\r\n\r\nResume link: %RESUME_DB_LINK%\r\n\r\nThanks,\r\n\r\n%SITE_NAME%\r\n%SITE_URL%\r\n-------------\r\n\r\n', 'example@example.com', '%CAN_NAME% granted you access to their resume on %SITE_NAME% ', 'Jamit Demo', '');;;

INSERT INTO `email_template_translations` (`EmailID`, `lang`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (44, 'PL', 'Hello %EMP_NAME%\r\n\r\n%CAN_NAME% has granted you access to their online resume on %SITE_NAME%!\r\n\r\nTo view this resume, please see this link:\r\n\r\nResume link: %RESUME_DB_LINK%\r\n\r\nThanks,\r\n\r\n%SITE_NAME%\r\n%SITE_URL%\r\n-------------\r\n\r\n', 'example@example.com', '%CAN_NAME% granted you access to their resume on %SITE_NAME% ', 'Jamit Demo', '');;;

INSERT INTO `email_template_translations` (`EmailID`, `lang`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (44, 'CN', 'Hello %EMP_NAME%\r\n\r\n%CAN_NAME% has granted you access to their online resume on %SITE_NAME%!\r\n\r\nTo view this resume, please see this link:\r\n\r\nResume link: %RESUME_DB_LINK%\r\n\r\nThanks,\r\n\r\n%SITE_NAME%\r\n%SITE_URL%\r\n-------------\r\n\r\n', 'example@example.com', '%CAN_NAME% granted you access to their resume on %SITE_NAME% ', 'Jamit Demo', '');;;


CREATE TABLE `email_templates` (
  `EmailText` text NOT NULL,
  `EmailFromAddress` varchar(255) NOT NULL default '',
  `EmailFromName` varchar(255) NOT NULL default '',
  `EmailSubject` varchar(255) NOT NULL default '',
  `EmailID` int(11) NOT NULL default '0',
  `sub_template` text NOT NULL,
  PRIMARY KEY  (`EmailID`)
) ;;;

";

$sql .= "
 

INSERT INTO `email_templates` VALUES ('Dear %FNAME% %LNAME%,\r\n\r\nYou have successfully signed up to %SITE_NAME%.\r\n\r\nIf you ever encounter any problems, bugs or just have \r\nany questions or suggestions, feel free to contact \r\nus: %SITE_CONTACT_EMAIL%;\r\n\r\n\r\nYou have registered with the following details - \r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD% \r\n\r\nhttp://www.example.com\r\n\r\n&#54620;&#44397;&#50612;', 'test@example.com', 'Example', 'Successfully Signed Up as %MEMBERID%', 1, '');;;
INSERT INTO `email_templates` VALUES ('Dear %FNAME% %LNAME%,\r\n\r\nYou have successfully signed for a %SITE_NAME% Employer''s Account. \r\nYou have registered with the following details - \r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD% \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have any questions / problems. \r\n\r\nKind Regards,\r\n\r\nWebmaster.', 'test@example.com', 'Example', 'Successfully Signed Up as %MEMBERID%', 2, '');;;
INSERT INTO `email_templates` VALUES ('Dear %FNAME% %LNAME%,\r\n\r\nYour %SITE_NAME% password has been reset!\r\n\r\nHere is your new password:\r\n\r\nMember ID: %MEMBERID%\r\nPassword: %PASSWORD%\r\n\r\nYou can sign into your account here: http://www.example.com/ \r\n\r\nKind Regards,\r\n\r\nWebmaster', 'test@example.com', 'Example', 'Reset Password', 3, '');;;
INSERT INTO `email_templates` VALUES ('Dear %FNAME% %LNAME%,\r\n\r\nAn employer on %SITE_NAME% has requested for your contact details!\r\nHere are the details of the request:\r\n\r\nEmployer Name: %EMPLOYER_NAME%\r\nReply-to Email Address: %REPLY_TO%\r\n%MESSAGE%\r\n\r\nYou may reveal your contact details to this employer by simply visiting the following link: %PERMIT_LINK%\r\n\r\nYou may also contact the employer directly by replying to this email!\r\n\r\n\r\nBest Regards,\r\n\r\nTeam %SITE_NAME%', 'test@example.com', 'Example', 'Request for your contact details by an employer', 4, '');;;
INSERT INTO `email_templates` VALUES ('Your %SITE_NAME% Daily Resume Alert!\r\n\r\nDear %FNAME% %LNAME%,\r\n\r\nYour Resume Alert on %SITE_NAME% has returned the following alert for you today:\r\n\r\n%RESUME_ALERTS% \r\n\r\n\r\n\r\n%SITE_NAME% Team %SITE_CONTACT_EMAIL%\r\n- If you want to View these resumes,or maintain your Daily Resume Alerts, \r\nplease visit this link:\r\n%EMPLOYER_LINK% \r\n', 'test@example.com', 'Example', 'Daily C.V. Alert', 5, '%DATE% : %RESUME_NAME% (%NATIONALITY%)');;;
INSERT INTO `email_templates` VALUES ('<h2><font face=''arial''>Your %SITE_NAME% Daily Resume Alert!</font></h2><p>\r\n<p><font size=''2'' face=''arial''>Dear %FNAME% %LNAME%,</font></p>\r\n<font face=''arial'' size=''2''>Your Resume Alert on %SITE_NAME% has returned the following alert for you today:</font><p>%RESUME_ALERTS%\r\n</p>\r\n<p>\r\n<p><font size=''2'' face=''arial''><b></b></font></p><p>\r\n<font face=\"arial\" size=\"2\">%SITE_NAME% Team %SITE_CONTACT_EMAIL%</font></p><p>\r\n<font face=\"arial\" size=\"2\">- If you want to View these resumes, or maintain your Daily Resume Alerts, please visit this link: %EMPLOYER_LINK%</a>\r\n</font></p>\r\n', 'test@example.com', 'Example', 'Resume Alert', 6, '<font face=''arial'' size=''2''>%DATE% - %RESUME_NAME% </font>');;;
INSERT INTO `email_templates` VALUES ('Your %SITE_NAME% Daily Job Alert\r\n\r\nDear %FNAME% %LNAME%\r\n\r\nYour Daily Job Alert on %SITE_NAME% has returned the following alert for you today:\r\n\r\n%JOB_ALERTS% \r\n\r\n\r\n \r\n\r\n%SITE_NAME% Team test@example.com\r\n\r\n%SITE_CONTACT_EMAIL%\r\n- If you want to Cancel, or Edit your Daily Job Alerts, please visit this link: %CANDIDATE_LINK%\r\n', 'test@example.com', 'Example', 'Job Alert', 7, '');;;
INSERT INTO `email_templates` VALUES ('<img src=\"%SITE_LOGO_URL%\"><br><h2><font face=''arial''>Your %SITE_NAME% Daily Job Alert</font></h2>\r\n\r\n<p><font size=''2'' face=''arial''>Dear %FNAME% %LNAME%</font></p>\r\n\r\n<font face=''arial'' size=''2''>Your Daily Job Alert on %SITE_NAME% has returned the following alert for you today:</font><p></p>\r\n\r\n<p>\r\n%JOB_ALERTS%\r\n</p>\r\n<p>\r\n<b></b>\r\n</p>\r\n<p><font face=''arial'' size=''2''>%SITE_NAME% Team test@example.com<br>%SITE_CONTACT_EMAIL%</font></p>\r\n<p><font face=''arial'' size=''2''>- If you want to Cancel, or Edit your Daily Job Alerts, please visit this link: <a href=\"%CANDIDATE_LINK%\">%CANDIDATE_LINK%</a></font></p>', 'test@example.com', 'Example', 'Job Alert', 8, '');;;
INSERT INTO `email_templates` VALUES ('Application Sent to: %POSTED_BY% (%EMPLOYER_EMAIL%)\r\n\r\nJob Post Titled:  \r\n - %JOB_TITLE%\r\nApplicant:\r\n - %APP_NAME% (%APP_EMAIL%)\r\nsubject:\r\n - %APP_SUBJECT%\r\nLetter:\r\n%APP_LETTER%\r\nAttachments:\r\n%APP_ATTACHMENT1%\r\n%APP_ATTACHMENT2%\r\n%APP_ATTACHMENT3%', 'test@example.com', 'Example', 'Application Confirmation', 10, '');;;
INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (60, 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nItem Name: %ITEM_NAME%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Bank Deposit\r\n--------------------------\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'webmaster@hiteacher.com', 'Order Confirmed', 'Hi Teacher', '');;;
INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (61, 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nItem Name: %ITEM_NAME%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n--------------------------\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Order Confirmed', 'Jamit Demo', '');;;





INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (70, 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have credited your order to your account, and you may now use your available balance to post your job advertisement(s) to %SITE_NAME%.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nItem Name: %ITEM_NAME%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Completed\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n--------------------------\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your balance and order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Posts'' -> ''Posting Credits''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Order completed!', 'Jamit Demo', '');;;

INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (90, 'Dear  %LNAME%, %FNAME%\r\n\r\nYour subscription payment on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have activated your subscription, and you may now log in to your account\r\nto access the resume database.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nStart Date: %SUB_START%\r\nEnd Date: %SUB_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n--------------------------\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your subscription order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Resumes'' -> ''Subscriptions''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Subscription now active!', 'Jamit Demo', '');;;

INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (80, 'Dear  %LNAME%, %FNAME%\r\n\r\nYour subscription on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: Bank\r\n--------------------------\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n', 'example@example.com', 'Subscription order confirmed', 'Jamit Demo', '');;;

INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (81, 'Dear %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n--------------------------\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email', 'example@example.com', 'Subscription order confirmed', 'Jamit Demo', '');;;

INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (120, 'Dear  %LNAME%, %FNAME%\r\n\r\nThis email is sent to notify you that your membership to %SITE_NAME% has expired.\r\n\r\nWe thank you for your patronage during your membership time, and we hope\r\nthat we can continue to serve you as our member in the future.\r\n\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nStart Date: %MEM_START%\r\nEnd Date: %MEM_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Expired\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n--------------------------\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your membership order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Membership Details''.\r\n\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Membership Expired', 'Jamit Demo', '');;;

INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (100, 'Dear  %LNAME%, %FNAME%\r\n\r\nYour membership on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Item: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Bank\r\n--------------------------\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n', 'example@example.com', 'Membership Order Confirmed', 'Jamit Demo', '');;;

INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (101, 'Dear %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n--------------------------\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email', 'example@example.com', 'Membership Order Confirmed', 'Jamit Demo', '');;;

INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (110, 'Dear  %LNAME%, %FNAME%\r\n\r\nYour membership payment on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have activated your membership, and we welcome you as our new member. \r\nHere are your membership payment details:\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nStart Date: %MEM_START%\r\nEnd Date: %MEM_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n--------------------------\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your membership order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Membership Details''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Membership Activated', 'Jamit Demo', '');;;

INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (130, 'Dear  %LNAME%, %FNAME%\r\n\r\nThis email is sent to notify you that your subscription to %SITE_NAME% has expired.\r\n\r\nWe thank you for your patronage during your subscription time, and we hope\r\nthat we can continue to serve you as our subscriber in the future.\r\n\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nStart Date: %SUB_START%\r\nEnd Date: %SUB_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Expired\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n--------------------------\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your subscription order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Subscription''.\r\n\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Subscription Expired', 'Jamit Demo', '');;;


INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (330, 'A new order was placed on %SITE_NAME% by %USER%!\r\n\r\nTo manage, see here:\r\n%ADMIN_LINK%\r\n\r\n==================================\r\n\r\nOrder by: %LNAME%, %FNAME%\r\nUsername: %USER%\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nItem: %ITEM_NAME%\r\nOrder ID: #%INVOICE_CODE%\r\nPrice: %INVOICE_AMOUNT%\r\n', 'test@test.com', 'A New order was placed on %SITE_NAME%', 'Jamit Demo', '');;;
		

INSERT INTO `email_templates` (`EmailID`,  `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (320,  'A Resume resume was posted to / updated on %SITE_NAME%\r\n\r\nAdmin Link: \r\n%ADMIN_LINK%\r\n\r\n%RESUME_SUMMARY%\r\n\r\n\r\n', 'example@example.com', 'A Resume was saved on %SITE_NAME%', 'Jamit Demo', '');;;
		

INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (310, 'A new Post was posted to %SITE_NAME%\r\n\r\nAdmin Link: %ADMIN_LINK%\r\n\r\nTitle:\r\n%POST_TITLE%\r\nBy:\r\n%POSTED_BY%\r\nDate:\r\n%DATE%\r\nDescription:\r\n%POST_DESCRIPTION%\r\n', 'test@test.com', 'A new Post was posted to %SITE_NAME%', 'Jamit Job Board', '');;;
		

INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (210, 'Dear %FNAME% %LNAME%,\r\n\r\nWe would like to notify you that the following post had expired on %SITE_NAME%:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n\r\nStatistics:\r\n%VIEWS% views\r\n%APPS% Applications\r\n\r\nThis job post will no longer be visible in the job listings. You may log in to your employer''s account to view or re-post this job, or post a new job at any time.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Job post expired', 'Jamit Demo', '');;;
		

INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (220, 'Dear %FNAME% %LNAME%,\r\n\r\nThank you for posting your job to %SITE_NAME%!\r\n\r\nWe have just approved the following job to be listed on our site:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n%POST_URL%\r\n\r\n\r\nThis job post will now become visible on the job listings. You may log in to your employer''s account to view or edit this job at any time.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Your job posting was Approved!', 'Jamit Demo', '');;;
	

INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (230, 'Dear %FNAME% %LNAME%,\r\n\r\nThank you for posting your job to %SITE_NAME%.\r\n\r\nHowever, after reviewing your job post, we have decided to disapprove it.\r\n\r\nThe following job post was disapproved:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n\r\nReason for disapproval: %REASON%\r\n\r\nYou may log in to your employer''s account to edit this job so that we may review it again.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Your job posting was disapproved', 'Jamit Job Board', '');;;

INSERT INTO `email_templates` ( `EmailText` , `EmailFromAddress` , `EmailFromName` , `EmailSubject` , `EmailID` , `sub_template` )VALUES ('%APP_LETTER% \r\n\r\n----------------------------------- \r\nThis email was sent from %SITE_NAME% %BASE_HTTP_PATH%\r\nOnline Resume Link: \r\n%RESUME_DB_LINK%\r\n', '', '', '', '12', '');;;

INSERT INTO `email_templates` (`EmailID`,  `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (11,  '%MESSAGE%\r\n\r\n\r\n\r\n\r\n------------------------\r\n%SITE_URL%\r\n\r\nThis message was sent by somebody using the \r\nweb-email service provided by %SITE_NAME%.\r\n\r\nName: %EMPLOYER_NAME%\r\nSender\'s User ID: %USER_ID%\r\nSender IP: %SENDER_IP%\r\n', 'example@example.com', '', 'Jamit Demo', '');;;


INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (44, 'Hello %EMP_NAME%\r\n\r\n%CAN_NAME% has granted you access to their online resume on %SITE_NAME%!\r\n\r\nTo view this resume, please see this link:\r\n\r\nResume link: %RESUME_DB_LINK%\r\n\r\nThanks,\r\n\r\n%SITE_NAME%\r\n%SITE_URL%\r\n-------------\r\n\r\n', 'example@example.com', '%CAN_NAME% granted you access to their resume on %SITE_NAME% ', 'Jamit Demo', '');;;

CREATE TABLE `employers` (
  `ID` int(11) NOT NULL auto_increment,
  `IP` varchar(50) NOT NULL default '',
  `SignupDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `FirstName` varchar(50) NOT NULL default '',
  `LastName` varchar(50) NOT NULL default '',
  `Rank` int(11) NOT NULL default '1',
  `Username` varchar(50) NOT NULL default '',
  `Password` varchar(50) NOT NULL default '',
  `Email` varchar(255) NOT NULL default '',
  `Newsletter` int(11) NOT NULL default '1',
  `Notification1` int(11) NOT NULL default '0',
  `Notification2` int(11) NOT NULL default '0',
  `Aboutme` longtext NOT NULL,
  `Validated` int(11) NOT NULL default '0',
  `CompName` varchar(255) NOT NULL default '',
  `login_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `logout_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `login_count` int(11) NOT NULL default '0',
  `last_request_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `lang` char(3) NOT NULL default '',
  `alert_last_run` datetime NOT NULL default '0000-00-00 00:00:00',
  `alert_email` varchar(255) NOT NULL default '',
  `posts_balance` int(11) NOT NULL default '0',
  `premium_posts_balance` int(11) NOT NULL default '0',
  `subscription_can_view_resume` set('Y','N') NOT NULL default 'N',
  `subscription_can_premium_post` set('Y','N') NOT NULL default 'N',
  `subscription_can_post` set('Y','N') NOT NULL default 'N',
  `newsletter_last_run` datetime NOT NULL default '0000-00-00 00:00:00',
  `alert_query` text NOT NULL,
  `can_view_blocked` SET( 'Y', 'N' ) NOT NULL default 'N',
  `alert_keywords` varchar(255) NOT NULL default '', 
  `membership_active` CHAR(1) NOT NULL default 'N',
  `expired` SET ('Y','N') NOT NULL default 'N',
  `views_quota` INT NOT NULL DEFAULT '0', 
  `p_posts_quota` INT NOT NULL DEFAULT '0', 
  `posts_quota` INT NOT NULL DEFAULT '0',
  `views_quota_tally` INT NOT NULL DEFAULT '0', 
  `p_posts_quota_tally` INT NOT NULL DEFAULT '0', 
  `posts_quota_tally` INT NOT NULL DEFAULT '0',
  `quota_timestamp` INT NOT NULL DEFAULT '0',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Username` (`Username`)
)  AUTO_INCREMENT=2 ;;;



INSERT INTO `employers` VALUES (1, '127.0.0.1', '2006-04-05 04:25:37', 'Test', 'Account', 1, 'test', '098f6bcd4621d373cade4e832627b4f6', 'test@example.com', 0, 0, 0, '', 1, 'Jamit Test Account', '2006-04-05 04:25:39', '0000-00-00 00:00:00', 1, '2006-04-14 13:45:31', '', '0000-00-00 00:00:00', '', 5, 5, 'N', 'N', 'N', '0000-00-00 00:00:00', '', 'N', '', 'N', 'N', 0, 0, 0, 0,0,0, 0);;;

 

CREATE TABLE `form_field_translations` (
  `field_id` int(11) NOT NULL default '0',
  `lang` char(2) NOT NULL default '',
  `field_label` text NOT NULL,
  `error_message` varchar(255) NOT NULL default '',
  `field_comment` text NOT NULL,
  PRIMARY KEY  (`field_id`,`lang`),
  KEY `field_id` (`field_id`)
) ;;;



INSERT INTO `form_field_translations` VALUES (2, 'CN', 'Job Title', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (2, 'EN', 'Job Title', 'was not filled in', '(enter a descriptive title for your ad)');;;
INSERT INTO `form_field_translations` VALUES (2, 'ES', 'Job Title', 'Was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (2, 'KO', 'Job Title', '&#50630;&#45796;', '');;;
INSERT INTO `form_field_translations` VALUES (2, 'PL', 'Job Title', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (3, 'CN', 'Job Details', '', '');;;
INSERT INTO `form_field_translations` VALUES (3, 'EN', 'Post Details', 'in english', '');;;
INSERT INTO `form_field_translations` VALUES (3, 'ES', 'Job Details', '', '');;;
INSERT INTO `form_field_translations` VALUES (3, 'KO', '&#12631;&#54840;&#54973;k', '', '');;;
INSERT INTO `form_field_translations` VALUES (3, 'PL', 'Job Details', '', '');;;
INSERT INTO `form_field_translations` VALUES (5, 'CN', 'Description', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (5, 'EN', 'Description', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (5, 'ES', 'Description', 'Was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (5, 'KO', 'Ad text', '', '');;;
INSERT INTO `form_field_translations` VALUES (5, 'PL', 'Description', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (6, 'CN', 'Classification', 'Was not selected', '(Posts in the wrong category will be removed)');;;
INSERT INTO `form_field_translations` VALUES (6, 'EN', 'Classification', 'Was not selected', '(Posts in the wrong category will be removed)');;;
INSERT INTO `form_field_translations` VALUES (6, 'ES', 'Classification', 'Was not filled in', '(Posts in the wrong category will be removed)');;;
INSERT INTO `form_field_translations` VALUES (6, 'KO', 'Classification', '', '');;;
INSERT INTO `form_field_translations` VALUES (6, 'PL', 'Classification', 'Was not selected', '(Posts in the wrong category will be removed)');;;
INSERT INTO `form_field_translations` VALUES (7, 'CN', 'Start Date', '', '');;;
INSERT INTO `form_field_translations` VALUES (7, 'EN', 'Start Date', '', '');;;
INSERT INTO `form_field_translations` VALUES (7, 'ES', 'Start Date', '', '');;;
INSERT INTO `form_field_translations` VALUES (7, 'KO', 'Start Date', '', '');;;
INSERT INTO `form_field_translations` VALUES (7, 'PL', 'Start Date', '', '');;;
INSERT INTO `form_field_translations` VALUES (8, 'CN', 'Posted By', 'was not filled in', '(Your school or company name)');;;
INSERT INTO `form_field_translations` VALUES (8, 'EN', 'Posted By', 'was not filled in', '(Your name, or company name)');;;
INSERT INTO `form_field_translations` VALUES (8, 'ES', 'Posted By', 'Was not filled in', '(Your business or company name)');;;
INSERT INTO `form_field_translations` VALUES (8, 'KO', 'Posted By', '', '');;;
INSERT INTO `form_field_translations` VALUES (8, 'PL', 'Posted By', 'was not filled in', '(Your business name)');;;
INSERT INTO `form_field_translations` VALUES (9, 'CN', 'Contract Length', '', '');;;
INSERT INTO `form_field_translations` VALUES (9, 'EN', 'Job Function', '', 'eg. teacher, nurse, sales manager etc.');;;
INSERT INTO `form_field_translations` VALUES (9, 'ES', 'Contract Length', '', '');;;
INSERT INTO `form_field_translations` VALUES (9, 'KO', 'Contract Length', '', '');;;
INSERT INTO `form_field_translations` VALUES (9, 'PL', 'Contract Length', '', '');;;
INSERT INTO `form_field_translations` VALUES (10, 'CN', 'Salary', '', '');;;
INSERT INTO `form_field_translations` VALUES (10, 'EN', 'Salary', '', '');;;
INSERT INTO `form_field_translations` VALUES (10, 'ES', 'Salary', '', '');;;
INSERT INTO `form_field_translations` VALUES (10, 'KO', 'Salary', '', '');;;
INSERT INTO `form_field_translations` VALUES (10, 'PL', 'Salary', '', '');;;
INSERT INTO `form_field_translations` VALUES (11, 'CN', 'Cell Phone', '', '');;;
INSERT INTO `form_field_translations` VALUES (11, 'EN', 'Cell Phone', '', '');;;
INSERT INTO `form_field_translations` VALUES (11, 'ES', 'Cell Phone', '', '');;;
INSERT INTO `form_field_translations` VALUES (11, 'KO', 'Cell Phone', '', '');;;
INSERT INTO `form_field_translations` VALUES (11, 'PL', 'Cell Phone', '', '');;;
INSERT INTO `form_field_translations` VALUES (12, 'CN', 'Email', 'was invalid.', '');;;
INSERT INTO `form_field_translations` VALUES (12, 'EN', 'Email', 'was invalid.', '');;;
INSERT INTO `form_field_translations` VALUES (12, 'ES', 'Email', 'Was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (12, 'KO', 'Email', '', '');;;
INSERT INTO `form_field_translations` VALUES (12, 'PL', 'Email', 'was invalid.', '');;;
INSERT INTO `form_field_translations` VALUES (13, 'CN', 'Location', 'Was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (13, 'EN', 'Location', 'Was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (13, 'ES', 'Location', 'Was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (13, 'KO', 'Location', '', '');;;
INSERT INTO `form_field_translations` VALUES (13, 'PL', 'Location', 'Was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (14, 'CN', 'Job Type', 'Was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (14, 'EN', 'Job Type', 'Was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (14, 'ES', 'Job Type', 'Was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (14, 'KO', 'Job Type', '', '');;;
INSERT INTO `form_field_translations` VALUES (14, 'PL', 'Job Type', 'Was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (15, 'CN', 'Location', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (15, 'EN', 'Location', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (15, 'ES', 'Location', '', '');;;
INSERT INTO `form_field_translations` VALUES (15, 'KO', 'Location', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (15, 'PL', 'Location', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (16, 'CN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (16, 'EN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (16, 'ES', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (16, 'KO', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (16, 'PL', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (17, 'CN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (17, 'EN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (17, 'ES', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (17, 'KO', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (17, 'PL', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (19, 'CN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (19, 'EN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (19, 'ES', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (19, 'KO', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (19, 'PL', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (20, 'CN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (20, 'EN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (20, 'ES', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (20, 'KO', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (20, 'PL', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (21, 'CN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (21, 'EN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (21, 'ES', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (21, 'KO', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (21, 'PL', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (22, 'CN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (22, 'EN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (22, 'ES', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (22, 'KO', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (22, 'PL', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (23, 'CN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (23, 'EN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (23, 'ES', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (23, 'KO', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (23, 'PL', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (24, 'CN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (24, 'EN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (24, 'ES', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (24, 'KO', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (24, 'PL', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (25, 'CN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (25, 'EN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (25, 'ES', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (25, 'KO', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (25, 'PL', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (28, 'CN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (28, 'EN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (28, 'ES', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (28, 'KO', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (28, 'PL', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (32, 'CN', 'Deadline', '', '(enter full year: yyyy)');;;
INSERT INTO `form_field_translations` VALUES (32, 'EN', 'Deadline', '', '(enter full year: yyyy)');;;
INSERT INTO `form_field_translations` VALUES (32, 'ES', 'Deadline', '', '(enter full year: yyyy)');;;
INSERT INTO `form_field_translations` VALUES (32, 'KO', 'Deadline', '', '');;;
INSERT INTO `form_field_translations` VALUES (32, 'PL', 'Deadline', '', '(enter full year: yyyy)');;;
INSERT INTO `form_field_translations` VALUES (34, 'CN', 'Category', '', '');;;
INSERT INTO `form_field_translations` VALUES (34, 'EN', 'Category', '', '');;;
INSERT INTO `form_field_translations` VALUES (34, 'ES', 'Category', '', '');;;
INSERT INTO `form_field_translations` VALUES (34, 'KO', 'Category', '', '');;;
INSERT INTO `form_field_translations` VALUES (34, 'PL', 'Category', '', '');;;
INSERT INTO `form_field_translations` VALUES (36, 'CN', 'Name', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (36, 'EN', 'Name', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (36, 'ES', 'Name', 'was not filled in.', '');;;
INSERT INTO `form_field_translations` VALUES (36, 'KO', 'Name', '', '');;;
INSERT INTO `form_field_translations` VALUES (36, 'PL', 'Name', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (37, 'CN', 'Gender', 'was not filled in.', '');;;
INSERT INTO `form_field_translations` VALUES (37, 'EN', 'Work Experience', 'was not filled in.', '');;;
INSERT INTO `form_field_translations` VALUES (37, 'ES', 'Gender', 'was not filled in.', '');;;
INSERT INTO `form_field_translations` VALUES (37, 'KO', 'Gender', '', '');;;
INSERT INTO `form_field_translations` VALUES (37, 'PL', 'Gender', 'was not filled in.', '');;;
INSERT INTO `form_field_translations` VALUES (38, 'CN', 'D.O.B.', 'is blank', '');;;
INSERT INTO `form_field_translations` VALUES (38, 'EN', 'D.O.B.', 'is blank', '');;;
INSERT INTO `form_field_translations` VALUES (38, 'ES', 'D.O.B.', 'is blank', '');;;
INSERT INTO `form_field_translations` VALUES (38, 'KO', 'D.O.B.', '', '');;;
INSERT INTO `form_field_translations` VALUES (38, 'PL', 'D.O.B.', 'is blank', '');;;
INSERT INTO `form_field_translations` VALUES (39, 'CN', 'Nationality', 'was not filled in.', '');;;
INSERT INTO `form_field_translations` VALUES (39, 'EN', 'Nationality', 'was not filled in.', '');;;
INSERT INTO `form_field_translations` VALUES (39, 'ES', 'Nationality', 'was not filled in.', '');;;
INSERT INTO `form_field_translations` VALUES (39, 'KO', 'Nationality', '', '');;;
INSERT INTO `form_field_translations` VALUES (39, 'PL', 'Nationality', 'was not filled in.', '');;;
INSERT INTO `form_field_translations` VALUES (40, 'CN', 'Email', '', '');;;
INSERT INTO `form_field_translations` VALUES (40, 'EN', 'Email', '', '');;;
INSERT INTO `form_field_translations` VALUES (40, 'ES', 'Email', '', '');;;
INSERT INTO `form_field_translations` VALUES (40, 'KO', 'Email', '', '');;;
INSERT INTO `form_field_translations` VALUES (40, 'PL', 'Email', '', '');;;
INSERT INTO `form_field_translations` VALUES (41, 'CN', 'Cell Phone No.', '', '');;;
INSERT INTO `form_field_translations` VALUES (41, 'EN', 'Cell Phone No.', '', '');;;
INSERT INTO `form_field_translations` VALUES (41, 'ES', 'Cell Phone No.', '', '');;;
INSERT INTO `form_field_translations` VALUES (41, 'KO', 'Cell Phone No.', '', '');;;
INSERT INTO `form_field_translations` VALUES (41, 'PL', 'Cell Phone No.', '', '');;;
INSERT INTO `form_field_translations` VALUES (42, 'CN', 'Photo', '', '');;;
INSERT INTO `form_field_translations` VALUES (42, 'EN', 'Photo', '', '');;;
INSERT INTO `form_field_translations` VALUES (42, 'ES', 'Photo', '', '');;;
INSERT INTO `form_field_translations` VALUES (42, 'KO', 'Photo', '', '');;;
INSERT INTO `form_field_translations` VALUES (42, 'PL', 'Photo', '', '');;;
INSERT INTO `form_field_translations` VALUES (43, 'CN', 'Telephone', '', '');;;
INSERT INTO `form_field_translations` VALUES (43, 'EN', 'Telephone', '', '');;;
INSERT INTO `form_field_translations` VALUES (43, 'ES', 'Telephone', '', '');;;
INSERT INTO `form_field_translations` VALUES (43, 'KO', 'Telephone', '', '');;;
INSERT INTO `form_field_translations` VALUES (43, 'PL', 'Telephone', '', '');;;
INSERT INTO `form_field_translations` VALUES (44, 'CN', '[Current Residential Address]', '', '');;;
INSERT INTO `form_field_translations` VALUES (44, 'EN', '[Current Residential Address]', '', '');;;
INSERT INTO `form_field_translations` VALUES (44, 'ES', '[Current Residential Address]', '', '');;;
INSERT INTO `form_field_translations` VALUES (44, 'KO', '[Current Residential Address]', '', '');;;
INSERT INTO `form_field_translations` VALUES (44, 'PL', '[Current Residential Address]', '', '');;;
INSERT INTO `form_field_translations` VALUES (45, 'CN', 'Street Address', '', '');;;
INSERT INTO `form_field_translations` VALUES (45, 'EN', 'Street Address', '', '');;;
INSERT INTO `form_field_translations` VALUES (45, 'ES', 'Street Address', '', '');;;
INSERT INTO `form_field_translations` VALUES (45, 'KO', 'Street Address', '', '');;;
INSERT INTO `form_field_translations` VALUES (45, 'PL', 'Street Address', '', '');;;
INSERT INTO `form_field_translations` VALUES (46, 'CN', 'City / Town', 'was not filled in.', '');;;
INSERT INTO `form_field_translations` VALUES (46, 'EN', 'City / Town', 'was not filled in.', '');;;
INSERT INTO `form_field_translations` VALUES (46, 'ES', 'City / Town', 'was not filled in.', '');;;
INSERT INTO `form_field_translations` VALUES (46, 'KO', 'City / Town', '', '');;;
INSERT INTO `form_field_translations` VALUES (46, 'PL', 'City / Town', 'was not filled in.', '');;;
INSERT INTO `form_field_translations` VALUES (47, 'CN', 'Province / State', '', '');;;
INSERT INTO `form_field_translations` VALUES (47, 'EN', 'Province / State', 'Province', '');;;
INSERT INTO `form_field_translations` VALUES (47, 'ES', 'Province / State', '', '');;;
INSERT INTO `form_field_translations` VALUES (47, 'KO', 'Province / State', '', '');;;
INSERT INTO `form_field_translations` VALUES (47, 'PL', 'Province / State', '', '');;;
INSERT INTO `form_field_translations` VALUES (48, 'CN', 'Zip / Post Code', '', '');;;
INSERT INTO `form_field_translations` VALUES (48, 'EN', 'Zip / Post Code', '', '');;;
INSERT INTO `form_field_translations` VALUES (48, 'ES', 'Zip / Post Code', '', '');;;
INSERT INTO `form_field_translations` VALUES (48, 'KO', 'Zip / Post Code', '', '');;;
INSERT INTO `form_field_translations` VALUES (48, 'PL', 'Zip / Post Code', '', '');;;
INSERT INTO `form_field_translations` VALUES (49, 'CN', 'Country (currently in)', 'was not filled in.', '');;;
INSERT INTO `form_field_translations` VALUES (49, 'EN', 'Country (currently in)', 'was not filled in.', '');;;
INSERT INTO `form_field_translations` VALUES (49, 'ES', 'Country (currently in)', 'was not filled in.', '');;;
INSERT INTO `form_field_translations` VALUES (49, 'KO', 'Country (currently in)', '', '');;;
INSERT INTO `form_field_translations` VALUES (49, 'PL', 'Country (currently in)', 'was not filled in.', '');;;
INSERT INTO `form_field_translations` VALUES (50, 'CN', '[Education & Experience]', '', '');;;
INSERT INTO `form_field_translations` VALUES (50, 'EN', '[Education & Experience]', '', '');;;
INSERT INTO `form_field_translations` VALUES (50, 'ES', '[Education & Experience]', '', '');;;
INSERT INTO `form_field_translations` VALUES (50, 'KO', '[Education & Experience]', '', '');;;
INSERT INTO `form_field_translations` VALUES (50, 'PL', '[Education & Experience]', '', '');;;
INSERT INTO `form_field_translations` VALUES (51, 'CN', 'Education Summary', '', '');;;
INSERT INTO `form_field_translations` VALUES (51, 'EN', 'Education Summary', '', '');;;
INSERT INTO `form_field_translations` VALUES (51, 'ES', 'Education Summary', '', '');;;
INSERT INTO `form_field_translations` VALUES (51, 'KO', 'Education Summary', '', '');;;
INSERT INTO `form_field_translations` VALUES (51, 'PL', 'Education Summary', '', '');;;
INSERT INTO `form_field_translations` VALUES (52, 'CN', 'Work Experience', '', '');;;
INSERT INTO `form_field_translations` VALUES (52, 'EN', 'Work Experience', '', '');;;
INSERT INTO `form_field_translations` VALUES (52, 'ES', 'Work Experience', '', '');;;
INSERT INTO `form_field_translations` VALUES (52, 'KO', 'Work Experience', '', '');;;
INSERT INTO `form_field_translations` VALUES (52, 'PL', 'Work Experience', '', '');;;
INSERT INTO `form_field_translations` VALUES (53, 'CN', '[Availability & Preferences]', '', '');;;
INSERT INTO `form_field_translations` VALUES (53, 'EN', '[Availability & Preferences]', '', '');;;
INSERT INTO `form_field_translations` VALUES (53, 'ES', '[Availability & Preferences]', '', '');;;
INSERT INTO `form_field_translations` VALUES (53, 'KO', '[Availability & Preferences]', '', '');;;
INSERT INTO `form_field_translations` VALUES (53, 'PL', '[Availability & Preferences]', '', '');;;
INSERT INTO `form_field_translations` VALUES (54, 'CN', 'Available to Start', 'Date is invalid / incomplete.', '');;;
INSERT INTO `form_field_translations` VALUES (54, 'EN', 'Available to Start', 'Date is invalid / incomplete.', '');;;
INSERT INTO `form_field_translations` VALUES (54, 'ES', 'Available to Start', 'Date is invalid / incomplete.', '');;;
INSERT INTO `form_field_translations` VALUES (54, 'KO', 'Available to Start', '', '');;;
INSERT INTO `form_field_translations` VALUES (54, 'PL', 'Available to Start', 'Date is invalid / incomplete.', '');;;
INSERT INTO `form_field_translations` VALUES (55, 'CN', 'Job Type', '', '');;;
INSERT INTO `form_field_translations` VALUES (55, 'EN', 'Highest Education', '', '');;;
INSERT INTO `form_field_translations` VALUES (55, 'ES', 'Job Type', '', '');;;
INSERT INTO `form_field_translations` VALUES (55, 'KO', 'Job Type', '', '');;;
INSERT INTO `form_field_translations` VALUES (55, 'PL', 'Job Type', '', '');;;
INSERT INTO `form_field_translations` VALUES (57, 'CN', 'Salary Range', '', '');;;
INSERT INTO `form_field_translations` VALUES (57, 'EN', 'Salary Range', '', '');;;
INSERT INTO `form_field_translations` VALUES (57, 'ES', 'Salary Range', '', '');;;
INSERT INTO `form_field_translations` VALUES (57, 'KO', 'Salary Range', '', '');;;
INSERT INTO `form_field_translations` VALUES (57, 'PL', 'Salary Range', '', '');;;
INSERT INTO `form_field_translations` VALUES (58, 'CN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (58, 'EN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (58, 'ES', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (58, 'KO', 'Notes / Self Introduction', '', '');;;
INSERT INTO `form_field_translations` VALUES (58, 'PL', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (59, 'CN', 'Location Preference', '', '');;;
INSERT INTO `form_field_translations` VALUES (59, 'EN', 'Location Preference', '', '');;;
INSERT INTO `form_field_translations` VALUES (59, 'ES', 'Location Preference', '', '');;;
INSERT INTO `form_field_translations` VALUES (59, 'KO', 'Location Preference', '', '');;;
INSERT INTO `form_field_translations` VALUES (59, 'PL', 'Location Preference', '', '');;;
INSERT INTO `form_field_translations` VALUES (60, 'CN', 'Positions Interested In?', '', '');;;
INSERT INTO `form_field_translations` VALUES (60, 'EN', 'Positions Interested In?', '', '');;;
INSERT INTO `form_field_translations` VALUES (60, 'ES', 'Positions Interested In?', '', '');;;
INSERT INTO `form_field_translations` VALUES (60, 'KO', 'Positions Interested In?', '', '');;;
INSERT INTO `form_field_translations` VALUES (60, 'PL', 'Positions Interested In?', '', '');;;
INSERT INTO `form_field_translations` VALUES (63, 'CN', 'Additional Notes', '', '');;;
INSERT INTO `form_field_translations` VALUES (63, 'EN', 'Additional Notes', '', '');;;
INSERT INTO `form_field_translations` VALUES (63, 'ES', 'Additional Notes', '', '');;;
INSERT INTO `form_field_translations` VALUES (63, 'KO', 'Additional Notes', '', '');;;
INSERT INTO `form_field_translations` VALUES (63, 'PL', 'Additional Notes', '', '');;;
INSERT INTO `form_field_translations` VALUES (65, 'CN', 'Business Name', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (65, 'EN', 'Business Name', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (65, 'ES', 'Business Name', '', '');;;
INSERT INTO `form_field_translations` VALUES (65, 'KO', 'Business Name', '', '');;;
INSERT INTO `form_field_translations` VALUES (65, 'PL', 'Business Name', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (66, 'CN', 'Logo / Photo', '', '');;;
INSERT INTO `form_field_translations` VALUES (66, 'EN', 'Logo / Photo', '', '');;;
INSERT INTO `form_field_translations` VALUES (66, 'ES', 'Logo / Photo', '', '');;;
INSERT INTO `form_field_translations` VALUES (66, 'KO', 'Logo / Photo', '', '');;;
INSERT INTO `form_field_translations` VALUES (66, 'PL', 'Logo / Photo', '', '');;;
INSERT INTO `form_field_translations` VALUES (67, 'CN', 'Your Business Type', '', '');;;
INSERT INTO `form_field_translations` VALUES (67, 'EN', 'Your Business Type', '', '');;;
INSERT INTO `form_field_translations` VALUES (67, 'ES', 'Your Business Type', '', '');;;
INSERT INTO `form_field_translations` VALUES (67, 'KO', 'Your business', '', '');;;
INSERT INTO `form_field_translations` VALUES (67, 'PL', 'Your Business Type', '', '');;;
INSERT INTO `form_field_translations` VALUES (68, 'CN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (68, 'EN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (68, 'ES', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (68, 'KO', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (68, 'PL', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (69, 'CN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (69, 'EN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (69, 'ES', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (69, 'KO', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (69, 'PL', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (70, 'CN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (70, 'EN', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (70, 'ES', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (70, 'KO', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (70, 'PL', '', '', '');;;
INSERT INTO `form_field_translations` VALUES (71, 'CN', 'Contact Details', '', '');;;
INSERT INTO `form_field_translations` VALUES (71, 'EN', 'Contact Details', '', '');;;
INSERT INTO `form_field_translations` VALUES (71, 'ES', 'Contact Details', '', '');;;
INSERT INTO `form_field_translations` VALUES (71, 'KO', 'Contact Details', '', '');;;
INSERT INTO `form_field_translations` VALUES (71, 'PL', 'Contact Details', '', '');;;
INSERT INTO `form_field_translations` VALUES (72, 'CN', 'Contact Name', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (72, 'EN', 'Contact Name', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (72, 'ES', 'Contact Name', '', '');;;
INSERT INTO `form_field_translations` VALUES (72, 'KO', 'Contact Name', '', '');;;
INSERT INTO `form_field_translations` VALUES (72, 'PL', 'Contact Name', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (73, 'CN', 'Position (Director / Supervisor / etc)', '', '');;;
INSERT INTO `form_field_translations` VALUES (73, 'EN', 'Position (Director / Supervisor / etc)', '', '');;;
INSERT INTO `form_field_translations` VALUES (73, 'ES', 'Position (Director / Supervisor / etc)', '', '');;;
INSERT INTO `form_field_translations` VALUES (73, 'KO', 'Position (Director / Supervisor / etc)', '', '');;;
INSERT INTO `form_field_translations` VALUES (73, 'PL', 'Position (Director / Supervisor / etc)', '', '');;;
INSERT INTO `form_field_translations` VALUES (74, 'CN', 'Website URL', '', '');;;
INSERT INTO `form_field_translations` VALUES (74, 'EN', 'Website URL', '', '');;;
INSERT INTO `form_field_translations` VALUES (74, 'ES', 'Website URL', '', '');;;
INSERT INTO `form_field_translations` VALUES (74, 'KO', 'Website URL', '', '');;;
INSERT INTO `form_field_translations` VALUES (74, 'PL', 'Website URL', '', '');;;
INSERT INTO `form_field_translations` VALUES (75, 'CN', 'Email', 'was invalid', '');;;
INSERT INTO `form_field_translations` VALUES (75, 'EN', 'Email', 'was invalid', '');;;
INSERT INTO `form_field_translations` VALUES (75, 'ES', 'Email', '', '');;;
INSERT INTO `form_field_translations` VALUES (75, 'KO', 'Email', '', '');;;
INSERT INTO `form_field_translations` VALUES (75, 'PL', 'Email', 'was invalid', '');;;
INSERT INTO `form_field_translations` VALUES (76, 'CN', 'Telephone', '', '');;;
INSERT INTO `form_field_translations` VALUES (76, 'EN', 'Telephone', '', '');;;
INSERT INTO `form_field_translations` VALUES (76, 'ES', 'Telephone', '', '');;;
INSERT INTO `form_field_translations` VALUES (76, 'KO', 'Telephone', '', '');;;
INSERT INTO `form_field_translations` VALUES (76, 'PL', 'Telephone', '', '');;;
INSERT INTO `form_field_translations` VALUES (77, 'CN', 'Cell phone', '', '');;;
INSERT INTO `form_field_translations` VALUES (77, 'EN', 'Cell phone', '', '');;;
INSERT INTO `form_field_translations` VALUES (77, 'ES', 'Cell phone', '', '');;;
INSERT INTO `form_field_translations` VALUES (77, 'KO', 'Cell phone', '', '');;;
INSERT INTO `form_field_translations` VALUES (77, 'PL', 'Cell phone', '', '');;;
INSERT INTO `form_field_translations` VALUES (78, 'CN', 'Office Address', '', '');;;
INSERT INTO `form_field_translations` VALUES (78, 'EN', 'Office Address', '', '');;;
INSERT INTO `form_field_translations` VALUES (78, 'ES', 'Office Address', '', '');;;
INSERT INTO `form_field_translations` VALUES (78, 'KO', 'Office Address', '', '');;;
INSERT INTO `form_field_translations` VALUES (78, 'PL', 'Office Address', '', '');;;
INSERT INTO `form_field_translations` VALUES (79, 'CN', 'Street Address', '', '');;;
INSERT INTO `form_field_translations` VALUES (79, 'EN', 'Street Address', '', '');;;
INSERT INTO `form_field_translations` VALUES (79, 'ES', 'Street Address', '', '');;;
INSERT INTO `form_field_translations` VALUES (79, 'KO', 'Street Address', '', '');;;
INSERT INTO `form_field_translations` VALUES (79, 'PL', 'Street Address', '', '');;;
INSERT INTO `form_field_translations` VALUES (80, 'CN', 'City / Town', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (80, 'EN', 'City / Town', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (80, 'ES', 'City / Town', '', '');;;
INSERT INTO `form_field_translations` VALUES (80, 'KO', 'City', '', '');;;
INSERT INTO `form_field_translations` VALUES (80, 'PL', 'City / Town', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (81, 'CN', 'Province/ State', '', '');;;
INSERT INTO `form_field_translations` VALUES (81, 'EN', 'Province/ State', '', '');;;
INSERT INTO `form_field_translations` VALUES (81, 'ES', 'Province/ State', '', '');;;
INSERT INTO `form_field_translations` VALUES (81, 'KO', 'Province/ State', '', '');;;
INSERT INTO `form_field_translations` VALUES (81, 'PL', 'Province/ State', '', '');;;
INSERT INTO `form_field_translations` VALUES (82, 'CN', 'ZIP / Post Code', '', '');;;
INSERT INTO `form_field_translations` VALUES (82, 'EN', 'ZIP / Post Code', '', '');;;
INSERT INTO `form_field_translations` VALUES (82, 'ES', 'ZIP / Post Code', '', '');;;
INSERT INTO `form_field_translations` VALUES (82, 'KO', 'ZIP / Post Code', '', '');;;
INSERT INTO `form_field_translations` VALUES (82, 'PL', 'ZIP / Post Code', '', '');;;
INSERT INTO `form_field_translations` VALUES (83, 'CN', 'Country', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (83, 'EN', 'Country', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (83, 'ES', 'Country', '', '');;;
INSERT INTO `form_field_translations` VALUES (83, 'KO', 'Country', '', '');;;
INSERT INTO `form_field_translations` VALUES (83, 'PL', 'Country', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (84, 'CN', 'About', '', '');;;
INSERT INTO `form_field_translations` VALUES (84, 'EN', 'About', '', '');;;
INSERT INTO `form_field_translations` VALUES (84, 'ES', 'About', '', '');;;
INSERT INTO `form_field_translations` VALUES (84, 'KO', 'About', '', '');;;
INSERT INTO `form_field_translations` VALUES (84, 'PL', 'About', '', '');;;
INSERT INTO `form_field_translations` VALUES (85, 'CN', 'Write a short introduction about your organisation / Include details about your business', '', '');;;
INSERT INTO `form_field_translations` VALUES (85, 'EN', 'Write a short introduction about your organisation / Include details about your business', '', '');;;
INSERT INTO `form_field_translations` VALUES (85, 'ES', 'Write a short introduction about your organisation / Include details about your business', '', '');;;
INSERT INTO `form_field_translations` VALUES (85, 'KO', 'Write a short introduction about your organisation. Include details about your business', '', '');;;
INSERT INTO `form_field_translations` VALUES (85, 'PL', 'Write a short introduction about your organisation / Include details about your business', '', '');;;
INSERT INTO `form_field_translations` VALUES (86, 'CN', 'About', '', '');;;
INSERT INTO `form_field_translations` VALUES (86, 'EN', 'About', '', '');;;
INSERT INTO `form_field_translations` VALUES (86, 'ES', 'About', '', '');;;
INSERT INTO `form_field_translations` VALUES (86, 'KO', 'About', '', '');;;
INSERT INTO `form_field_translations` VALUES (86, 'PL', 'About', '', '');;;
INSERT INTO `form_field_translations` VALUES (89, 'CN', 'Candidate Details', 'error msg', '');;;
INSERT INTO `form_field_translations` VALUES (89, 'EN', 'Candidate Details', 'error msg', '');;;
INSERT INTO `form_field_translations` VALUES (89, 'ES', 'Candidate Details', 'error msg', '');;;
INSERT INTO `form_field_translations` VALUES (89, 'KO', 'Candidate Details', 'error msg', '');;;
INSERT INTO `form_field_translations` VALUES (89, 'PL', 'Candidate Details', 'error msg', '');;;
INSERT INTO `form_field_translations` VALUES (90, 'CN', 'file', '', '');;;
INSERT INTO `form_field_translations` VALUES (90, 'EN', 'file', '', '');;;
INSERT INTO `form_field_translations` VALUES (90, 'ES', 'file', '', '');;;
INSERT INTO `form_field_translations` VALUES (90, 'KO', 'file', '', '');;;
INSERT INTO `form_field_translations` VALUES (90, 'PL', 'file', '', '');;;
INSERT INTO `form_field_translations` VALUES (91, 'CN', 'upload resume', '', '');;;
INSERT INTO `form_field_translations` VALUES (91, 'EN', 'upload resume', '', '');;;
INSERT INTO `form_field_translations` VALUES (91, 'ES', 'upload resume', '', '');;;
INSERT INTO `form_field_translations` VALUES (91, 'KO', 'upload resume', '', '');;;
INSERT INTO `form_field_translations` VALUES (91, 'PL', 'upload resume', '', '');;;



CREATE TABLE `form_fields` (
  `form_id` int(11) NOT NULL default '0',
  `field_id` int(11) NOT NULL auto_increment,
  `section` tinyint(4) NOT NULL default '1',
  `reg_expr` varchar(255) NOT NULL default '',
  `field_label` varchar(255) NOT NULL default '-noname-',
  `field_type` varchar(255) NOT NULL default 'TEXT',
  `field_sort` tinyint(4) NOT NULL default '0',
  `is_required` set('Y','N') NOT NULL default 'N',
  `display_in_list` set('Y','N') NOT NULL default 'N',
  `is_in_search` set('Y','N') NOT NULL default 'N',
  `error_message` varchar(255) NOT NULL default '',
  `field_init` varchar(255) NOT NULL default '',
  `field_width` smallint(6) NOT NULL default '20',
  `field_height` smallint(6) NOT NULL default '0',
  `list_sort_order` smallint(6) NOT NULL default '0',
  `search_sort_order` tinyint(4) NOT NULL default '0',
  `template_tag` varchar(255) NOT NULL default '',
  `is_hidden` char(1) NOT NULL default '',
  `is_anon` char(1) NOT NULL default '',
  `field_comment` text NOT NULL,
  `category_init_id` int(11) NOT NULL default '0',
  `is_cat_multiple` set('Y','N') NOT NULL default 'N',
  `cat_multiple_rows` tinyint(4) NOT NULL default '1',
  `is_blocked` char(1) NOT NULL default 'N',
  `multiple_sel_all` char(1) NOT NULL default 'N',
  `is_prefill` char(1) NOT NULL default 'N',
	`is_member` char(1) NOT NULL default 'N',
  PRIMARY KEY  (`field_id`)
)  AUTO_INCREMENT=92 ;;;

 

INSERT INTO `form_fields` VALUES (1, 2, 1, 'not_empty', 'Job Title', 'TEXT', 1, 'Y', '', 'Y', 'was not filled in', '', 60, 0, 0, 1, 'TITLE', '', '', '(enter a descriptive title for your ad)', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (1, 3, 1, '', 'Job Details', 'SEPERATOR', 2, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (1, 5, 3, 'not_empty', 'Description', 'EDITOR', 5, 'Y', 'Y', 'Y', 'was not filled in', '', 62, 22, 4, 3, 'DESCRIPTION', 'Y', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (1, 6, 3, 'not_empty', 'Classification', 'CATEGORY', 4, 'Y', '', '', 'Was not selected', '23', 20, 0, 0, 0, 'CLASS', '', '', '(Posts in the wrong category will be removed)', 23, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (1, 7, 2, '', 'Start Date', 'TEXT', 3, '', '', '', '', '', 20, 0, 0, 0, 'START_DATE', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (1, 8, 2, 'not_empty', 'Posted By', 'TEXT', 1, 'Y', '', '', 'was not filled in', '', 30, 0, 0, 0, 'POSTED_BY', '', '', '(Your business or company name)', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (1, 9, 2, '', 'Job Function', 'TEXT', 2, '', '', '', '', '', 20, 0, 0, 0, 'JOB_FUNCTION', '', '', 'eg. teacher, nurse, sales manager etc.', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (1, 10, 2, '', 'Salary', 'TEXT', 4, '', '', '', '', '', 20, 0, 0, 0, 'SALARY', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (1, 11, 2, '', 'Cell Phone', 'TEXT', 5, '', '', '', '', '', 20, 0, 0, 0, 'CELL_PHONE', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (1, 12, 2, 'email', 'Email', 'TEXT', 6, 'Y', 'Y', '', 'was invalid.', '', 30, 0, 0, 0, 'EMAIL', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (1, 13, 3, 'not_empty', 'Location', 'CATEGORY', 2, 'Y', '', '', 'Was not filled in', '1', 20, 0, 0, 0, 'LOCATION_CAT', '', '', '', 1, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (1, 14, 3, 'not_empty', 'Job Type', 'CATEGORY', 3, 'Y', '', 'Y', 'Was not filled in', '20', 0, 0, 0, 3, 'JOB_TYPE', '', '', '', 20, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (1, 15, 2, 'not_empty', 'Location', 'TEXT', 7, 'Y', '', 'Y', 'was not filled in', '', 0, 0, 0, 2, 'LOCATION', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (1, 16, 2, '', '', 'BLANK', 8, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (1, 17, 2, '', '', 'BLANK', 9, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (1, 19, 2, '', '', 'BLANK', 11, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (1, 20, 2, '', '', 'BLANK', 12, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (1, 21, 2, '', '', 'BLANK', 13, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (1, 22, 2, '', '', 'BLANK', 14, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (1, 23, 2, '', '', 'BLANK', 15, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (1, 24, 2, '', '', 'BLANK', 16, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (1, 25, 2, '', '', 'BLANK', 17, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (1, 28, 2, '', '', 'BLANK', 18, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (1, 32, 2, '', 'Deadline', 'DATE', 10, '', '', '', '', '', 0, 0, 0, 0, 'DEADLINE', '', '', '(enter full year: yyyy)', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (1, 34, 3, '', 'Category', 'SEPERATOR', 1, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (2, 36, 1, 'not_empty', 'Name', 'TEXT', 2, 'Y', '', '', 'was not filled in', '', 30, 0, 0, 0, 'RESUME_NAME', '', 'Y', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (2, 37, 3, 'not_empty', 'Work Experience', 'SELECT', 11, 'Y', 'Y', '', 'was not filled in.', '', 0, 0, 0, 0, 'WORK_EXP_YEARS', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (2, 38, 1, '', 'D.O.B.', 'DATE', 3, '', '', '', 'is blank', '', 0, 0, 0, 0, 'DOB', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (2, 39, 1, 'not_empty', 'Nationality', 'TEXT', 4, 'Y', 'Y', 'Y', 'was not filled in.', '', 0, 0, 0, 2, 'NATIONALITY', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (2, 40, 1, '', 'Email', 'TEXT', 5, '', '', '', '', '', 0, 0, 0, 0, 'RESUME_EMAIL', '', 'Y', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (2, 41, 1, '', 'Cell Phone No.', 'TEXT', 6, '', '', '', '', '', 0, 0, 0, 0, 'CELL_PHONE', '', 'Y', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (2, 42, 2, '', 'Photo', 'IMAGE', 2, '', '', '', '', '', 0, 0, 0, 0, 'IMAGE', '', 'Y', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (2, 43, 1, '', 'Telephone', 'TEXT', 7, '', '', '', '', '', 0, 0, 0, 0, 'TELEPHONE', '', 'Y', '', 34, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (2, 44, 3, '', '[Current Residential Address]', 'SEPERATOR', 1, '', '', '', '', '', 0, 0, 0, 0, 'CRADDR', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (2, 45, 3, '', 'Street Address', 'TEXTAREA', 2, '', '', '', '', '', 30, 2, 0, 0, 'STREET_ADDR', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (2, 46, 3, 'not_empty', 'City / Town', 'TEXT', 3, 'Y', '', '', 'was not filled in.', '', 0, 0, 0, 0, 'CITY_OR_TOWN', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (2, 47, 3, '', 'Province / State', 'TEXT', 4, '', '', '', 'Province', '', 0, 0, 0, 0, 'Province', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (2, 48, 3, '', 'Zip / Post Code', 'TEXT', 5, '', '', '', '', '', 0, 0, 0, 0, 'ZIP_CODE', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (2, 49, 3, 'not_empty', 'Country (currently in)', 'TEXT', 6, 'Y', '', 'Y', 'was not filled in.', '', 0, 0, 0, 6, 'CURRENT_COUNTRY', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (2, 50, 3, '', '[Education & Experience]', 'SEPERATOR', 7, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (2, 51, 3, '', 'Education Summary', 'TEXTAREA', 9, '', '', 'Y', '', '', 40, 8, 0, 4, 'EDUCATION_SUM', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (2, 52, 3, '', 'Work Experience', 'TEXTAREA', 10, '', '', '', '', '', 40, 8, 0, 0, 'EXPR', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (2, 53, 3, '', '[Availability & Preferences]', 'SEPERATOR', 12, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (2, 54, 3, 'not_empty', 'Available to Start', 'DATE', 13, 'Y', 'Y', 'Y', 'Date is invalid / incomplete.', '', 0, 0, 0, 5, 'AVAIL_TO_START', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (2, 55, 3, 'not_empty', 'Highest Education', 'SELECT', 8, '', 'Y', 'Y', '', '20', 0, 0, 0, 3, 'HIGHEST_EDU', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (2, 57, 3, '', 'Salary Range', 'TEXT', 14, '', '', '', '', '', 0, 0, 0, 0, 'SALARY', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (2, 58, 3, '', '', 'TEXTAREA', 18, '', '', '', '', '', 40, 8, 0, 0, 'RESUME_NOTES', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (2, 59, 3, '', 'Location Preference', 'TEXTAREA', 15, '', '', '', '', '', 35, 3, 0, 0, 'LOCATION_PREF', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (2, 60, 3, '', 'Positions Interested In?', 'TEXTAREA', 16, '', '', '', '', '', 35, 3, 0, 0, 'POS_INTERESTED', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (2, 63, 3, '', 'Additional Notes', 'SEPERATOR', 17, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (3, 65, 1, 'not_empty', 'Business Name', 'TEXT', 1, 'Y', 'Y', 'Y', 'was not filled in', '', 40, 0, 0, 0, 'PROFILE_BNAME', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (3, 66, 2, '', 'Logo / Photo', 'IMAGE', 1, '', '', '', '', '', 0, 0, 0, 0, 'LOGO', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (3, 67, 1, 'not_empty', 'Your Business Type', 'RADIO', 2, '', 'Y', '', '', '', 0, 0, 0, 0, 'PROFILE_BTYPE', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (3, 68, 1, '', '', 'BLANK', 3, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (3, 69, 1, '', '', 'BLANK', 4, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (3, 70, 1, '', '', 'BLANK', 5, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (3, 71, 3, '', 'Contact Details', 'SEPERATOR', 1, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (3, 72, 3, 'not_empty', 'Contact Name', 'TEXT', 2, 'Y', 'Y', 'Y', 'was not filled in', '', 40, 0, 0, 0, 'PROFILE_CNAME', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (3, 73, 3, '', 'Position (Director / Supervisor / etc)', 'TEXT', 3, '', '', '', '', '', 40, 0, 0, 0, 'PROFILE_POS', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (3, 74, 3, 'not_empty', 'Website URL', 'TEXT', 4, '', 'Y', '', '', '', 40, 0, 0, 0, 'PROFILE_WEBURL', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (3, 75, 3, 'email', 'Email', 'TEXT', 5, 'Y', 'Y', 'Y', 'was invalid', '', 40, 0, 0, 0, 'PROFILE_EMAIL', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (3, 76, 3, '', 'Telephone', 'TEXT', 6, '', '', '', '', '', 40, 0, 0, 0, 'PROFILE_TEL', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (3, 77, 3, '', 'Cell phone', 'TEXT', 7, '', '', '', '', '', 40, 0, 0, 0, 'PROFILE_CELL', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (3, 78, 3, '', 'Office Address', 'SEPERATOR', 8, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (3, 79, 3, '', 'Street Address', 'TEXTAREA', 9, '', '', '', '', '', 30, 2, 0, 0, 'PROFILE_ADDR', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (3, 80, 3, 'not_empty', 'City / Town', 'TEXT', 10, 'Y', '', '', 'was not filled in', '', 40, 0, 0, 0, 'PROFILE_CITY', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (3, 81, 3, '', 'Province/ State', 'TEXT', 11, '', '', '', '', '', 40, 0, 0, 0, 'PROFILE_STATE', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (3, 82, 3, '', 'ZIP / Post Code', 'TEXT', 12, '', '', '', '', '', 6, 0, 0, 0, 'PROFILE_ZIP', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (3, 83, 3, 'not_empty', 'Country', 'TEXT', 13, 'Y', 'Y', 'Y', 'was not filled in', '', 40, 0, 0, 0, 'PROFILE_COUNTRY', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (3, 84, 3, '', 'About', 'SEPERATOR', 14, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (3, 85, 3, '', 'Write a short introduction about your organisation / Include details about your business', 'NOTE', 15, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (3, 86, 3, '', 'About', 'TEXTAREA', 16, '', '', '', '', '', 55, 10, 0, 0, 'PROFILE_ABOUT', '', '', '', 0, '', 1, '', '', '', 'N');;;
INSERT INTO `form_fields` VALUES (2, 89, 1, '', 'Candidate Details', 'SEPERATOR', 1, '', '', '', 'error msg', '', 0, 0, 0, 0, 'DETAIL', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (3, 90, 3, '', 'file', 'FILE', 17, '', '', '', '', '', 0, 0, 0, 0, 'TEST', '', '', '', 0, 'N', 1, 'N', 'N', 'N', 'N');;;
INSERT INTO `form_fields` VALUES (2, 91, 2, '', 'upload resume', 'FILE', 1, '', '', '', '', '', 0, 0, 0, 0, 'resume_file', '', '', '', 0, '', 0, '', '', 'N', 'N');;;




CREATE TABLE `form_lists` (
  `form_id` int(11) NOT NULL default '0',
  `field_type` varchar(255) NOT NULL default '',
  `sort_order` int(11) NOT NULL default '0',
  `field_id` varchar(255) NOT NULL default '0',
  `template_tag` varchar(255) NOT NULL default '',
  `column_id` int(11) NOT NULL auto_increment,
  `admin` set('Y','N') NOT NULL default '',
  `truncate_length` smallint(4) NOT NULL default '0',
  `linked` set('Y','N') NOT NULL default 'N',
  `clean_format` set('Y','N') NOT NULL default '',
  `is_bold` set('Y','N') NOT NULL default '',
  `is_sortable` set('Y','N') NOT NULL default 'N',
  `no_wrap` set('Y','N') NOT NULL default '',
  PRIMARY KEY  (`column_id`)
)  AUTO_INCREMENT=43 ;;;



INSERT INTO `form_lists` VALUES (2, 'TEXT', 2, '36', 'RESUME_NAME', 5, 'N', 0, 'Y', '', '', 'Y', '');;;
INSERT INTO `form_lists` VALUES (2, 'TIME', 1, 'resume_date', 'DATE', 6, '', 0, 'N', '', '', 'Y', '');;;
INSERT INTO `form_lists` VALUES (2, 'TEXT', 3, '48', 'ZIP_CODE', 7, 'N', 0, 'Y', 'N', 'N', 'Y', 'N');;;
INSERT INTO `form_lists` VALUES (2, 'TEXT', 4, '46', 'CITY_OR_TOWN', 8, 'N', 0, 'N', 'N', 'N', 'Y', 'N');;;
INSERT INTO `form_lists` VALUES (2, 'TEXTAREA', 8, '59', 'LOCATION_PREF', 9, 'N', 15, 'N', 'N', 'N', 'Y', 'N');;;
INSERT INTO `form_lists` VALUES (2, 'SELECT', 6, '37', 'WORK_EXP_YEARS', 10, 'N', 4, 'N', 'N', 'N', 'N', 'N');;;
INSERT INTO `form_lists` VALUES (2, 'TEXT', 8, 'hits', 'RES_HITS', 11, 'N', 0, 'N', 'N', 'N', 'N', 'Y');;;
INSERT INTO `form_lists` VALUES (2, 'SELECT', 7, '55', 'HIGHEST_EDU', 12, 'Y', 8, 'N', 'N', 'N', 'N', 'N');;;
INSERT INTO `form_lists` VALUES (1, 'TIME', 1, 'post_date', 'DATE', 13, 'N', 0, 'N', 'N', 'N', 'N', 'Y');;;
INSERT INTO `form_lists` VALUES (1, 'TEXT', 2, 'summary', 'POST_SUMMARY', 14, 'N', 0, 'N', 'N', 'N', 'N', '');;;
INSERT INTO `form_lists` VALUES (1, 'TEXT', 3, '15', 'LOCATION', 15, 'N', 0, 'N', 'Y', 'Y', 'N', 'N');;;
INSERT INTO `form_lists` VALUES (1, 'TEXT', 4, 'hits', 'HITS', 16, 'Y', 0, 'N', 'N', '', 'N', '');;;
INSERT INTO `form_lists` VALUES (4, 'TEXT', 1, 'login_count', 'LCOUNT', 17, 'N', 0, '', 'N', 'N', 'Y', 'Y');;;
INSERT INTO `form_lists` VALUES (4, 'TEXT', 2, 'Name', 'NAME', 18, 'N', 0, '', 'N', 'N', 'Y', 'Y');;;
INSERT INTO `form_lists` VALUES (4, 'TEXT', 3, 'Username', 'USERNAME', 19, 'N', 0, '', 'N', 'N', 'Y', 'Y');;;
INSERT INTO `form_lists` VALUES (4, 'TEXT', 4, 'Email', 'EMAIL', 20, 'N', 0, '', 'N', 'N', 'Y', 'Y');;;
INSERT INTO `form_lists` VALUES (4, 'TEXT', 5, 'CompName', 'CNAME', 21, 'N', 0, 'Y,N', 'N', 'N', 'Y', 'Y');;;
INSERT INTO `form_lists` VALUES (4, 'TEXT', 6, 'posts', 'POSTS', 22, 'N', 0, '', 'N', 'N', 'N', 'N');;;
INSERT INTO `form_lists` VALUES (4, 'TEXT', 7, 'Newsletter', 'NEWS', 23, 'N', 0, '', 'N', 'N', 'N', 'N');;;
INSERT INTO `form_lists` VALUES (4, 'TEXT', 8, 'Notification1', 'ALERTS', 24, 'N', 0, '', 'N', 'N', 'N', 'N');;;
INSERT INTO `form_lists` VALUES (4, 'TIME', 9, 'SignupDate', 'DATE', 25, 'N', 0, '', 'N', 'N', 'Y', 'Y');;;
INSERT INTO `form_lists` VALUES (4, 'TEXT', 10, 'IP', 'IP', 26, 'N', 0, '', 'N', 'N', 'Y', 'Y');;;
INSERT INTO `form_lists` VALUES (5, 'TEXT', 1, 'login_count', 'LCOUNT', 27, 'N', 0, '', 'N', 'N', 'Y', 'Y');;;
INSERT INTO `form_lists` VALUES (5, 'TEXT', 2, 'Name', 'NAME', 28, 'N', 0, '', 'N', 'N', 'Y', 'Y');;;
INSERT INTO `form_lists` VALUES (5, 'TEXT', 3, 'Username', 'USERNAME', 29, 'N', 0, '', 'N', 'N', 'Y', 'Y');;;
INSERT INTO `form_lists` VALUES (5, 'TEXT', 4, 'Email', 'EMAIL', 30, 'N', 0, '', 'N', 'N', 'Y', 'Y');;;
INSERT INTO `form_lists` VALUES (5, 'TEXT', 5, 'Newsletter', 'NEWS', 31, 'N', 0, '', 'N', 'N', 'N', 'N');;;
INSERT INTO `form_lists` VALUES (5, 'TEXT', 6, 'Notification1', 'ALERTS', 32, 'N', 0, '', 'N', 'N', 'N', 'N');;;
INSERT INTO `form_lists` VALUES (5, 'TEXT', 7, 'resume_id', 'RESUME_ID', 33, 'N', 0, '', 'N', 'N', 'N', 'N');;;
INSERT INTO `form_lists` VALUES (5, 'TIME', 8, 'SignupDate', 'DATE', 34, 'N', 0, '', 'N', 'N', 'Y', 'Y');;;
INSERT INTO `form_lists` VALUES (5, 'TEXT', 9, 'IP', 'IP', 35, 'N', 0, '', 'N', 'N', 'Y', 'Y');;;
INSERT INTO `form_lists` VALUES (3, 'TEXT', 1, '65', 'PROFILE_BNAME', 37, 'Y', 0, '', 'N', 'N', 'Y', 'Y');;;
INSERT INTO `form_lists` VALUES (3, 'RADIO', 2, '67', 'PROFILE_BTYPE', 38, 'N', 0, '', 'N', 'N', 'Y', 'Y');;;
INSERT INTO `form_lists` VALUES (3, 'TEXT', 3, '72', 'PROFILE_CNAME', 39, 'N', 0, '', 'N', 'N', 'Y', 'Y');;;
INSERT INTO `form_lists` VALUES (3, 'TEXT', 4, '83', 'PROFILE_COUNTRY', 40, 'N', 0, '', 'N', 'N', 'Y', 'Y');;;
INSERT INTO `form_lists` VALUES (3, 'TEXT', 5, '75', 'PROFILE_EMAIL', 41, 'N', 0, '', 'N', 'N', 'Y', 'Y');;;
INSERT INTO `form_lists` VALUES (3, 'TEXT', 6, '74', 'PROFILE_WEBURL', 42, 'N', 0, '', 'N', 'N', 'Y', 'Y');;;



CREATE TABLE `jb_sessions` (
  `session_id` varchar(255) NOT NULL default '',
  `last_request_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `domain` set('EMPLOYER','CANDIDATE') NOT NULL default '',
  `id` int(11) NOT NULL default '0',
  `remote_addr` varchar(255) NOT NULL default '',
  `http_referer` varchar(255) NOT NULL default '',
  `entry_point` varchar(255) NOT NULL default '',
  `user_agent` varchar(255) NOT NULL default '',

  PRIMARY KEY  (`session_id`)
) ;;;



INSERT INTO `jb_sessions` VALUES ('04a073480bd0e242d6a896b1490ee5aa', '2006-04-14 13:49:53', 'EMPLOYER', 1, '127.0.0.1', '', '', 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1) count:235');;;



CREATE TABLE `jb_variables` (
  `key` varchar(255) NOT NULL default '',
  `val` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`key`)
) ;;;



INSERT INTO `jb_variables` VALUES ('HOUSEKEEP_RUNNING', 'NO');;;
INSERT INTO `jb_variables` VALUES ('LAST_HOUSEKEEP_RUN', '1145022065');;;
INSERT INTO `jb_variables` VALUES ('MAIL_QUEUE_RUNNING', 'NO');;;
INSERT INTO `jb_variables` VALUES ('JB_VERSION', '3.6.11');;;
INSERT INTO `jb_variables` VALUES ('ACT_RESUME_COUNT', '1');;;
INSERT INTO `jb_variables` VALUES ('RESUME_COUNT', '1');;;

INSERT INTO `jb_variables` VALUES ('EMPLOYER_COUNT', '1');;;
INSERT INTO `jb_variables` VALUES ('USER_COUNT', '1');;;
INSERT INTO `jb_variables` VALUES ('POST_COUNT_AP', '1');;;

CREATE TABLE `lang` (
  `lang_code` char(2) NOT NULL default '',
  `lang_filename` varchar(32) NOT NULL default '',
  `lang_image` varchar(32) NOT NULL default '',
  `is_active` set('Y','N') NOT NULL default '',
  `name` varchar(32) NOT NULL default '',
  `charset` varchar(32) NOT NULL default '',
  `image_data` text NOT NULL,
  `mime_type` varchar(255) NOT NULL default '',
  `is_default` char(1) NOT NULL default 'N',
  `theme` varchar(32) NOT NULL default 'default',
  `fckeditor_lang` VARCHAR(10) NOT NULL default 'en.js',
  PRIMARY KEY  (`lang_code`)
) ;;;


INSERT INTO `lang` VALUES ('CN', 'chinese.php', 'chinese.gif', 'N', 'Chinese', '', 'R0lGODlhGgASAPcAAAAAAAAAQAAAgAAA/wAgAAAgQAAggAAg/wBAAABAQABAgABA/wBgAABgQABggABg/wCAAACAQACAgACA/wCgAACgQACggACg/wDAAADAQADAgADA/wD/AAD/QAD/gAD//yAAACAAQCAAgCAA/yAgACAgQCAggCAg/yBAACBAQCBAgCBA/yBgACBgQCBggCBg/yCAACCAQCCAgCCA/yCgACCgQCCggCCg/yDAACDAQCDAgCDA/yD/ACD/QCD/gCD//0AAAEAAQEAAgEAA/0AgAEAgQEAggEAg/0BAAEBAQEBAgEBA/0BgAEBgQEBggEBg/0CAAECAQECAgECA/0CgAECgQECggECg/0DAAEDAQEDAgEDA/0D/AED/QED/gED//2AAAGAAQGAAgGAA/2AgAGAgQGAggGAg/2BAAGBAQGBAgGBA/2BgAGBgQGBggGBg/2CAAGCAQGCAgGCA/2CgAGCgQGCggGCg/2DAAGDAQGDAgGDA/2D/AGD/QGD/gGD//4AAAIAAQIAAgIAA/4AgAIAgQIAggIAg/4BAAIBAQIBAgIBA/4BgAIBgQIBggIBg/4CAAICAQICAgICA/4CgAICgQICggICg/4DAAIDAQIDAgIDA/4D/AID/QID/gID//6AAAKAAQKAAgKAA/6AgAKAgQKAggKAg/6BAAKBAQKBAgKBA/6BgAKBgQKBggKBg/6CAAKCAQKCAgKCA/6CgAKCgQKCggKCg/6DAAKDAQKDAgKDA/6D/AKD/QKD/gKD//8AAAMAAQMAAgMAA/8AgAMAgQMAggMAg/8BAAMBAQMBAgMBA/8BgAMBgQMBggMBg/8CAAMCAQMCAgMCA/8CgAMCgQMCggMCg/8DAAMDAQMDAgMDA/8D/AMD/QMD/gMD///8AAP8AQP8AgP8A//8gAP8gQP8ggP8g//9AAP9AQP9AgP9A//9gAP9gQP9ggP9g//+AAP+AQP+AgP+A//+gAP+gQP+ggP+g///AAP/AQP/AgP/A////AP//QP//gP///ywAAAAAGgASAAAIVAABCBxIsKBBg+ASKlzIsKFCgQ4jSoSokB84ixIdUrzIMaNGAAv5YfTIcCPGkSQTbkwZcSXLhi5fLowpUyXImjBv4pypc6dNnzyBPuzp86DRowMDAgA7', 'image/gif', 'N', 'default', 'zh-cn');;;
INSERT INTO `lang` VALUES ('EN', 'english.php', 'english.gif', 'Y', 'English', '', 'R0lGODlhGQARAMQAAAURdBYscgNNfrUOEMkMBdAqE9UTMtItONNUO9w4SdxmaNuObhYuh0Y5lCxVlFJcpqN2ouhfjLCrrOeRmeHKr/Wy3Lje4dPW3PDTz9/q0vXm1ffP7MLt5/f0+AAAAAAAACwAAAAAGQARAAAF02AAMIDDkOgwEF3gukCZIICI1jhFDRmOS4dF50aMVSqEjehFIWQ2kJLUMRoxCCsNzDFBZDCuh1RMpQY6HZYIiOlIYqKy9JZIqHeZTqMWnvoZCgosCkIXDoeIAGJkfmgEB3UHkgp1dYuKVWJXWCsEnp4qAwUcpBwWphapFhoanJ+vKxOysxMRgbcDHRlfeboZF2mvwp+5Eh07YC9naMzNzLmKuggTDy8G19jZ2NAiFB0LBxYuC+TlC7Syai8QGU0TAs7xaNxLDLoDdsPDuS98ABXfQgAAOw==', 'image/gif', 'Y', 'default', 'en');;;
INSERT INTO `lang` VALUES ('ES', 'spanish.php', 'espanol.gif', 'N', 'Spanish', 'R0lGODlhKgAOAPcAAP///7+/v39/fwAA', 'R0lGODlhGgARAMQAAIaokuTkYk5kgszMaeNpAPT0AWlKAKyXCVpriM5TBOEYA+nqLp+UJuIIARUzXaMeBf8AAM+JB9RIAedDP+FbXp88AMu1v6usH///AOiKAN/cCs3MxQAAAAAAAAAAAAAAACH5BAAAAAAALAAAAAAaABEAAAVFICSOZGmeaKqubNticCzPdE1fmq3TUJIwu+CB8BgsDEHdoAGxBDZJm0LimESgUdog06gQsFkZogCgYAThtM7Fbrvf8FIIADs=', 'image/gif', 'N', 'default', 'es');;;
INSERT INTO `lang` VALUES ('KO', 'korean.php', 'korean.jpg', 'N', 'Korean', '', '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAASABoDAREAAhEBAxEB/8QAGQAAAgMBAAAAAAAAAAAAAAAAAwgEBQYC/8QALhAAAQMDAgQFAgcAAAAAAAAAAQIDBAUGEQAhBxIWMRMyQVFhFSI3VnWBlbPS/8QAGQEBAAMBAQAAAAAAAAAAAAAAAAEEBQID/8QAIREAAgICAQQDAAAAAAAAAAAAAAECAwQRMRITIfAGIrH/2gAMAwEAAhEDEQA/ALSXa4Vw4s9Vv2fSJM6ZGiGTMXT2XCkltByvmSSQon7legBz3zoCZVKzw9o1UNNdsykzHGCG5T8emsJQFjzBKSMnBz6/udVbMuEJdLRv4fx+/JoVyklvhe8EmBbEORfrbjVpUObbE2Il1l1ulsoQwMZBUSnKl52KfYg42xqzGSktoxLap1Tdc1prwxcr0YZi31cMeO02yw1U5KG220hKUJDqgAANgANsak8xl+rJlo8L7NmMU4So7kGIiQ4VkeGnwkHAA7qVuAe2cZ7jQGSuLhNcNTrr0qjvR10ye6ZKDIUWls855iFpIzsSfn4B1oY9uLB91x++uRdkZllCxe4+2uF75/TdWzWH6VccSxoUQyYlNhpRJlOZbWlYGfECTspBJCRj1J9BnVCTTk3FaR1Kcpvqm9sV++/xDuX9Vlf2q1ByBavG6GIrUZm5Kw3HZSlLbSJzoQgJxygAKwAMDHtjQBuu7w/Ndc/kXv8AWgOeuLtCyvqmt85GCr6g7kj283ydAUr770qQ7IkOuPPurK3HHFFSlqJySSdySd86A//Z', 'image/pjpeg', 'N', 'default', 'ko');;;
INSERT INTO `lang` VALUES ('PL', 'polish.php', 'polish.gif', 'N', 'Polish', '', 'R0lGODdhGgASAPcAAAAAAAAAQAAAgAAA/wAgAAAgQAAggAAg/wBAAABAQABAgABA/wBgAABgQABggABg/wCAAACAQACAgACA/wCgAACgQACggACg/wDAAADAQADAgADA/wD/AAD/QAD/gAD//yAAACAAQCAAgCAA/yAgACAgQCAggCAg/yBAACBAQCBAgCBA/yBgACBgQCBggCBg/yCAACCAQCCAgCCA/yCgACCgQCCggCCg/yDAACDAQCDAgCDA/yD/ACD/QCD/gCD//0AAAEAAQEAAgEAA/0AgAEAgQEAggEAg/0BAAEBAQEBAgEBA/0BgAEBgQEBggEBg/0CAAECAQECAgECA/0CgAECgQECggECg/0DAAEDAQEDAgEDA/0D/AED/QED/gED//2AAAGAAQGAAgGAA/2AgAGAgQGAggGAg/2BAAGBAQGBAgGBA/2BgAGBgQGBggGBg/2CAAGCAQGCAgGCA/2CgAGCgQGCggGCg/2DAAGDAQGDAgGDA/2D/AGD/QGD/gGD//4AAAIAAQIAAgIAA/4AgAIAgQIAggIAg/4BAAIBAQIBAgIBA/4BgAIBgQIBggIBg/4CAAICAQICAgICA/4CgAICgQICggICg/4DAAIDAQIDAgIDA/4D/AID/QID/gID//6AAAKAAQKAAgKAA/6AgAKAgQKAggKAg/6BAAKBAQKBAgKBA/6BgAKBgQKBggKBg/6CAAKCAQKCAgKCA/6CgAKCgQKCggKCg/6DAAKDAQKDAgKDA/6D/AKD/QKD/gKD//8AAAMAAQMAAgMAA/8AgAMAgQMAggMAg/8BAAMBAQMBAgMBA/8BgAMBgQMBggMBg/8CAAMCAQMCAgMCA/8CgAMCgQMCggMCg/8DAAMDAQMDAgMDA/8D/AMD/QMD/gMD///8AAP8AQP8AgP8A//8gAP8gQP8ggP8g//9AAP9AQP9AgP9A//9gAP9gQP9ggP9g//+AAP+AQP+AgP+A//+gAP+gQP+ggP+g///AAP/AQP/AgP/A////AP//QP//gP///yH5BAAAAAAALAAAAAAaABIAAAhQAAEIHEiwoEGD/xIqXMiwoUKBDiNKhCix4kKKFi1izDgRAEeNHj92FNkRnMmTKFOqPClwpcuXLV/KRBlz5syaNmECyHlzJ0+dP3UeHEoUQEAAOw==', 'image/gif', 'N',  'default', 'pl');;;
INSERT INTO `lang` VALUES ('FR', 'french.php', 'But_Language_French.gif', 'N', 'French', '', 'R0lGODlhGAAMAPcAAP////////8AAAAA//9LS0tL//sABPoABfkABvgAB/cACPYACfUACvQAC/MADPIADfEADvAAD+8AEO4AEe0AEuwAE+sAFOoAFekAFugAF+cAGOYAGeUAGuQAG+MAHOIAHeEAHuAAH98AIN4AId0AItwAI9sAJNoAJdkAJtgAJ9cAKNYAKdUAKtQAK9MALNIALdEALtAAL88AMM4AMc0AMswAM8sANMoANckANsgAN8cAOMYAOcUAOsQAO8MAPMIAPcEAPsAAP78AQL4AQb0AQrwAQ7sARLoARbkARrgAR7cASLYASbUASrQAS7MATLIATbEATrAAT68AUK4AUa0AUqwAU6sAVKoAVakAVqgAV6cAWKYAWaUAWqQAW6MAXKIAXaEAXqAAX58AYJ4AYZ0AYpwAY5sAZJoAZZkAZpgAZ5cAaJYAaZUAapQAa5MAbJIAbZEAbpAAb48AcI4AcY0AcowAc4sAdIoAdYkAdogAd4cAeIYAeYUAeoQAe4MAfIIAfYEAfoAAf38AgH4AgX0AgnwAg3sAhHoAhXkAhngAh3cAiHYAiXUAinQAi3MAjHIAjXEAjnAAj28AkG4AkW0AkmwAk2sAlGoAlWkAlmgAl2cAmGYAmWUAmmQAm2MAnGIAnWEAnmAAn18AoF4AoV0AolwAo1sApFoApVkAplgAp1cAqFYAqVUAqlQAq1MArFIArVEArlAAr08AsE4AsU0AskwAs0sAtEoAtUkAtkgAt0cAuEYAuUUAukQAu0MAvEIAvUEAvkAAvz8AwD4AwT0AwjwAwzsAxDoAxTkAxjgAxzcAyDYAyTUAyjQAyzMAzDIAzTEAzjAAzy8A0C4A0S0A0iwA0ysA1CoA1SkA1igA1ycA2CYA2SUA2iQA2yMA3CIA3SEA3iAA3x8A4B4A4R0A4hwA4xsA5BoA5RkA5hgA5xcA6BYA6RUA6hQA6xMA7BIA7REA7hAA7w8A8A4A8Q0A8gwA8wsA9AoA9QkA9ggA9wcA+AYA+QUA+gQA+wMA/AIA/SH5BAEAAAAALAAAAAAYAAwAAAhPAAcIHDigQICDCA8SEMCwoQCCAw0mRLjQIUOIAiVODFDRIsaCGxVavIhR48SODj+aTIiyocqQHEc+LAmzJUmIKynKfBnS5kycNXfS7CkzIAA7', 'image/gif', 'N', 'default', 'fr');;;


CREATE TABLE `mail_queue` (
  `mail_id` int(11) NOT NULL auto_increment,
  `mail_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `to_address` varchar(128) NOT NULL default '',
  `to_name` varchar(128) NOT NULL default '',
  `from_address` varchar(128) NOT NULL default '',
  `from_name` varchar(128) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `message` text NOT NULL,
  `html_message` text NOT NULL,
  `attachments` set('Y','N') NOT NULL default '',
  `status` set('queued','sent','error') NOT NULL default '',
  `error_msg` varchar(255) NOT NULL default '',
  `retry_count` smallint(6) NOT NULL default '0',
  `template_id` int(11) NOT NULL default '0',
  `att1_name` varchar(128) NOT NULL default '',
  `att2_name` varchar(128) NOT NULL default '',
  `att3_name` varchar(128) NOT NULL default '',
  `date_stamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `user_id` INT NULL DEFAULT NULL,
  `user_type` VARCHAR( 10 ) NULL DEFAULT NULL,
  PRIMARY KEY  (`mail_id`)
)  AUTO_INCREMENT=28 ;;;







CREATE TABLE `newsletters` (
  `letter_id` int(11) NOT NULL auto_increment,
  `to` varchar(255) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `message` text NOT NULL,
  `create_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `status` char(3) NOT NULL default '',
  PRIMARY KEY  (`letter_id`)
)  AUTO_INCREMENT=1 ;;;



CREATE TABLE `package_invoices` (
  `invoice_id` int(11) NOT NULL auto_increment,
  `invoice_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `processed_date` datetime default NULL,
  `status` varchar(255) NOT NULL default '',
  `employer_id` int(11) NOT NULL default '0',
  `package_id` int(11) NOT NULL default '0',
  `posts_quantity` int(11) NOT NULL default '0',
  `premium` set('Y','N') NOT NULL default '',
  `amount` float NOT NULL default '0',
  `item_name` varchar(255) NOT NULL default '',
  `subscr_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `payment_method` varchar(64) NOT NULL default '',
  `currency_code` char(3) NOT NULL default '',
  `currency_rate` decimal(10,4) NOT NULL default '0.0000',
  `reason` VARCHAR( 128 ) NOT NULL,
  `invoice_tax` FLOAT NOT NULL DEFAULT '0',
  PRIMARY KEY  (`invoice_id`)
)  AUTO_INCREMENT=2 ;;;




CREATE TABLE `packages` (
  `package_id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` text NOT NULL,
  `price` float NOT NULL default '0',
  `posts_quantity` smallint(6) NOT NULL default '0',
  `premium` set('Y','N') NOT NULL default 'N',
  `currency_code` char(3) NOT NULL default '',
  PRIMARY KEY  (`package_id`)
)  AUTO_INCREMENT=22 ;;;



INSERT INTO `packages` VALUES (20, 'single post', '', 40, 1, 'Y', 'AUD');;;
INSERT INTO `packages` VALUES (21, 'single post', '', 100, 1, 'N', 'AUD');;;



CREATE TABLE `posts_table` (
  `post_id` int(11) NOT NULL auto_increment,
  `post_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `post_mode` set('premium','normal','free') NOT NULL default '',
  `user_id` int(11) NOT NULL default '0',
  `pin_x` mediumint(9) NOT NULL default '0',
  `pin_y` mediumint(9) NOT NULL default '0',
  `approved` set('Y','N') NOT NULL default '',
  `2` varchar(255) NOT NULL default '',
  `5` text NOT NULL,
  `6` int(11) NOT NULL default '0',
  `8` varchar(255) NOT NULL default '',
  `10` varchar(255) NOT NULL default '',
  `11` varchar(255) NOT NULL default '',
  `13` int(11) NOT NULL default '0',
  `14` int(11) NOT NULL default '0',
  `15` varchar(255) NOT NULL default '',
  `32` datetime NOT NULL default '0000-00-00 00:00:00',
  `applications` int(11) NOT NULL default '0',
  `hits` int(11) NOT NULL default '0',
  `reason` varchar(255) NOT NULL default '',
  `7` varchar(255) NOT NULL default '',
  `9` varchar(255) NOT NULL default '',
  `12` varchar(255) NOT NULL default '',
  `guid` varchar(255) NOT NULL default '',
	`source` varchar(255) NOT NULL default '',
 `cached_summary` text NOT NULL,
 `expired` SET ('Y','N') NOT NULL default 'N',
 `app_type` CHAR( 1 ) NOT NULL,
 `app_url` VARCHAR( 255 ) NOT NULL default 'O',
  PRIMARY KEY  (`post_id`)
) AUTO_INCREMENT=1 ;;;



INSERT INTO `posts_table` VALUES (1, NOW(), 'normal', 1, 0, 0, 'Y', 'This is a test job post, posted by the Jamit Job Board Test Account.', 'This is a test post, created with the following account details:\r\n\r\nusername:test\r\npassword:test\r\n\r\nThe account can be deleted from the admin. \r\n\r\n', 82, 'Jamit Job Board Test Account.', 'neg', '', 67, 22, 'Sydney, Australia', '0000-00-00 00:00:00', 0, 1, '', 'ASAP', 'Testing engineer', 'test@example.com', '', '', 'This is a test post, created with the following account details...', 'N', 'O', '');;;



CREATE TABLE `profiles_table` (
  `profile_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `profile_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `expired` SET ('Y','N') NOT NULL default 'N',
  `65` varchar(255) NOT NULL default '',
  `66` varchar(255) NOT NULL default '',
  `67` varchar(255) NOT NULL default '',
  `72` varchar(255) NOT NULL default '',
  `73` varchar(255) NOT NULL default '',
  `74` varchar(255) NOT NULL default '',
  `75` varchar(255) NOT NULL default '',
  `76` varchar(255) NOT NULL default '',
  `77` varchar(255) NOT NULL default '',
  `79` text NOT NULL,
  `80` varchar(255) NOT NULL default '',
  `81` varchar(255) NOT NULL default '',
  `82` varchar(255) NOT NULL default '',
  `83` varchar(255) NOT NULL default '',
  `86` text NOT NULL,
  `90` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`profile_id`)
) AUTO_INCREMENT=2 ;;;



INSERT INTO `profiles_table` VALUES (1, 1, '2006-04-14 13:40:06', 'N', '', '', 'P', 'my name', '', '', 'text@example.com', '', '', '', 'test city', '', '', 'test country', '', '');;;


CREATE TABLE `requests` (
  `employer_id` int(11) NOT NULL default '0',
  `candidate_id` int(11) NOT NULL default '0',
  `request_status` varchar(10) NOT NULL default '',
  `request_message` text NOT NULL,
  `request_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `key` varchar(100) NOT NULL default '',
  `deleted` SET( 'Y', 'N' ) NOT NULL DEFAULT 'N',
  PRIMARY KEY  (`employer_id`,`candidate_id`)
) ;;;



CREATE TABLE `resumes_table` (
  `resume_id` int(11) NOT NULL auto_increment,
  `list_on_web` char(1) NOT NULL default 'Y',
  `resume_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `user_id` int(11) NOT NULL default '0',
  `hits` int(11) NOT NULL default '0',
  `anon` char(1) NOT NULL default '',
  `status` set('ACT','SUS') NOT NULL default '',
  `approved` set('Y','N') NOT NULL default 'Y',
  `expired` SET ('Y','N') NOT NULL default 'N',
  `37` varchar(255) NOT NULL default '',
  `38` datetime NOT NULL default '0000-00-00 00:00:00',
  `39` varchar(255) NOT NULL default '',
  `40` varchar(255) NOT NULL default '',
  `41` varchar(255) NOT NULL default '',
  `43` varchar(255) NOT NULL default '',
  `45` varchar(255) NOT NULL default '',
  `46` varchar(255) NOT NULL default '',
  `47` varchar(255) NOT NULL default '',
  `48` varchar(255) NOT NULL default '',
  `49` varchar(255) NOT NULL default '',
  `51` text NOT NULL,
  `52` text NOT NULL,
  `54` datetime NOT NULL default '0000-00-00 00:00:00',
  `55` varchar(255) NOT NULL default '0',
  `57` varchar(255) NOT NULL default '',
  `58` text NOT NULL,
  `59` text NOT NULL,
  `60` text NOT NULL,
  `36` varchar(255) NOT NULL default '',
  `42` varchar(255) NOT NULL default '',
  `91` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`resume_id`)
)  AUTO_INCREMENT=3 ;;;



INSERT INTO `resumes_table` VALUES (1, 'Y', NOW(), 1, 0, '', 'ACT', 'Y', 'N', '11+', '1978-01-03 00:00:00', 'Australian', '', '', '', '', 'Sydney', '', '', 'Australia', '', '', '2006-05-06 00:00:00', 'Col', '', 'This is a test resume submitted with the following account details:\r\n\r\nuser: test\r\npass: test', '', '', 'John Smith - test', '', '');;;


CREATE TABLE `saved_jobs` (
  `post_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `save_date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`post_id`,`user_id`)
) ;;;





CREATE TABLE `skill_matrix` (
  `matrix_id` int(11) NOT NULL auto_increment,
  `field_id` int(11) NOT NULL default '0',
  `row_count` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`matrix_id`)
)  AUTO_INCREMENT=44 ;;;



INSERT INTO `skill_matrix` VALUES (7, 7, '5');;;
INSERT INTO `skill_matrix` VALUES (43, 43, '4');;;



CREATE TABLE `skill_matrix_data` (
  `field_id` int(11) NOT NULL default '0',
  `row` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `object_id` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `years` char(2) NOT NULL default '',
  `rating` char(2) NOT NULL default '',
  PRIMARY KEY ( `field_id` , `row` , `user_id` )
) ;;;



CREATE TABLE `subscription_invoices` (
  `invoice_id` int(11) NOT NULL auto_increment,
  `invoice_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `processed_date` datetime default NULL,
  `status` varchar(255) NOT NULL default '',
  `employer_id` int(11) NOT NULL default '0',
  `subscription_id` int(11) NOT NULL default '0',
  `months_duration` int(11) NOT NULL default '0',
  `amount` float NOT NULL default '0',
  `item_name` varchar(255) NOT NULL default '',
  `can_view_resumes` set('Y','N') NOT NULL default 'Y',
  `can_post` set('Y','N') NOT NULL default 'N',
  `can_post_premium` set('Y','N') NOT NULL default 'N',
  `subscr_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `subscr_end` datetime NOT NULL default '0000-00-00 00:00:00',
  `payment_method` varchar(64) NOT NULL default '',
  `currency_code` char(3) NOT NULL default '',
  `currency_rate` decimal(10,4) NOT NULL default '0.0000',
  `can_view_blocked` set('Y','N') NOT NULL default 'Y',
  `reason` VARCHAR( 128 ) NOT NULL,
   `views_quota` INT NOT NULL DEFAULT '0', 
   `p_posts_quota` INT NOT NULL DEFAULT '0', 
   `posts_quota` INT NOT NULL DEFAULT '0',
   `invoice_tax` FLOAT NOT NULL DEFAULT '0',
  PRIMARY KEY  (`invoice_id`)
)  AUTO_INCREMENT=1 ;;;





CREATE TABLE `subscriptions` (
  `subscription_id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` text NOT NULL,
  `months_duration` int(11) NOT NULL default '0',
  `price` float NOT NULL default '0',
  `can_view_resumes` set('Y','N') NOT NULL default 'Y',
  `can_post` set('Y','N') NOT NULL default 'N',
  `can_post_premium` set('Y','N') NOT NULL default 'N',

  `currency_code` char(3) NOT NULL default '',
  `can_view_blocked` set('Y','N') NOT NULL default 'Y',
   `views_quota` INT NOT NULL DEFAULT '0', 
   `p_posts_quota` INT NOT NULL DEFAULT '0', 
   `posts_quota` INT NOT NULL DEFAULT '0',
  PRIMARY KEY  (`subscription_id`)
)  AUTO_INCREMENT=4 ;;;

 

INSERT INTO `subscriptions` VALUES (3, '6 month', '', 6, 30, 'Y', 'N', 'N', 'AUD', 'Y', 0, 0, 0);;;





CREATE TABLE `users` (
  `ID` int(11) NOT NULL auto_increment,
  `IP` varchar(50) NOT NULL default '',
  `SignupDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `FirstName` varchar(50) NOT NULL default '',
  `LastName` varchar(50) NOT NULL default '',
  `Rank` int(11) NOT NULL default '1',
  `Username` varchar(50) NOT NULL default '',
  `Password` varchar(50) NOT NULL default '',
  `Email` varchar(255) NOT NULL default '',
  `Newsletter` int(11) NOT NULL default '1',
  `Notification1` int(11) NOT NULL default '0',
  `Notification2` int(11) NOT NULL default '0',
  `Aboutme` longtext NOT NULL,
  `Validated` int(11) NOT NULL default '0',
  `login_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `logout_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_request_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `login_count` int(11) NOT NULL default '0',
  `alert_keywords` varchar(255) NOT NULL default '',
  `alert_last_run` datetime NOT NULL default '0000-00-00 00:00:00',
  `alert_email` varchar(255) NOT NULL default '',
  `newsletter_last_run` datetime NOT NULL default '0000-00-00 00:00:00',
  `lang` char(3) NOT NULL default '',
  `alert_query` text NOT NULL,
  `membership_active` CHAR(1) NOT NULL default 'N',
 
	`expired` SET ('Y','N') NOT NULL default 'N',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Username` (`Username`)
)  AUTO_INCREMENT=2 ;;;


INSERT INTO `users` VALUES (1, '127.0.0.1', '2006-04-05 04:30:25', 'Test', 'Account', 1, 'test', '098f6bcd4621d373cade4e832627b4f6', 'test@example.com', 0, 0, 0, '', 1, '2006-04-05 04:30:27', '2006-04-05 04:32:22', '2006-04-05 04:32:13', 2, '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '', 'N', 'N');;;

CREATE TABLE `jb_txn` (
`transaction_id` int(11) NOT NULL auto_increment,
`date` datetime NOT NULL default '0000-00-00 00:00:00',
`invoice_id` int(11) NOT NULL default '0',
`type` varchar(32) NOT NULL default '',
`amount` float NOT NULL default '0',
`currency` char(3) NOT NULL default '',
`txn_id` varchar(128) NOT NULL default '',
`reason` varchar(64) NOT NULL default '',
`origin` varchar(32) NOT NULL default '',
`product_type` CHAR(1) NOT NULL default 'P',
`reference` VARCHAR( 128 ) NOT NULL  default '',
PRIMARY KEY  (`transaction_id`));;;


CREATE TABLE `jb_config` (
	`key` VARCHAR( 255 ) NOT NULL ,
	`val` VARCHAR( 255 ) NOT NULL ,
	PRIMARY KEY ( `key` ) 
);;;

CREATE TABLE `motd` (
`motd_type` CHAR( 2 ) NOT NULL ,
`motd_lang` CHAR( 2 ) NOT NULL ,
`motd_message` TEXT NOT NULL,
`motd_title` TEXT NOT NULL,
`motd_date_updated` datetime NOT NULL,
	PRIMARY KEY ( `motd_type` , `motd_lang` ) 
);;;

CREATE TABLE `help_pages` (
`help_type` CHAR( 2 ) NOT NULL ,
`help_lang` CHAR( 2 ) NOT NULL ,
`help_message` TEXT NOT NULL,
`help_title` TEXT NOT NULL,
`help_date_updated` datetime NOT NULL,
	PRIMARY KEY ( `help_type` , `help_lang` ) 
);;;


CREATE TABLE `payment_log` (
  `seq_no` int(11) NOT NULL auto_increment,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `module` varchar(128) NOT NULL default '',
  `log_entry` text NOT NULL,
  PRIMARY KEY  (`seq_no`)
);;;

CREATE TABLE `memberships` (
	`membership_id` INT NOT NULL AUTO_INCREMENT ,
	`name` VARCHAR( 255 ) NOT NULL ,
	`price` FLOAT NOT NULL ,
	`currency_code` VARCHAR( 3 ) NOT NULL ,
	`months` MEDIUMINT NOT NULL ,
	`type` SET( 'E', 'C' ) NOT NULL,
	PRIMARY KEY ( `membership_id` )
);;; 

CREATE TABLE `membership_invoices` (
		`invoice_id` INT NOT NULL AUTO_INCREMENT ,
		`invoice_date` DATETIME NOT NULL ,
		`processed_date` DATETIME  NULL ,
		`status` VARCHAR( 127 ) NOT NULL ,
		`user_type` SET( 'E', 'C' ) NOT NULL ,
		`user_id` INT NOT NULL ,
		`membership_id` INT NOT NULL ,
		`months_duration` MEDIUMINT NOT NULL ,
		`amount` FLOAT NOT NULL ,
		`currency_code` VARCHAR( 3 ) NOT NULL ,
		`currency_rate` DECIMAL( 10, 4 ) NOT NULL ,
		`item_name` VARCHAR( 255 ) NOT NULL ,
		`member_date` DATETIME NOT NULL ,
		`member_end` DATETIME NOT NULL ,
		`payment_method` VARCHAR( 64 ) NOT NULL ,
		`reason` VARCHAR( 127 ) NOT NULL ,
		`invoice_tax` FLOAT NOT NULL DEFAULT '0',
		PRIMARY KEY ( `invoice_id` ) 
		);;;

	CREATE TABLE `mail_monitor_log` (
		`log_id` INT NOT NULL AUTO_INCREMENT ,
		`date` DATETIME NOT NULL ,
		`email` VARCHAR(255) NOT NULL ,
		`user_type` SET( 'E', 'C' ) NOT NULL ,
		PRIMARY KEY ( `log_id` ));;;

CREATE TABLE `xml_export_elements` (
  `element_id` int(11) NOT NULL auto_increment,
  `element_name` varchar(255) NOT NULL default '',
  `is_cdata` set('Y','N') NOT NULL default '',
  `parent_element_id` int(11) default '0',
  `form_id` int(11) NOT NULL default '0',
  `field_id` varchar(128) NOT NULL default '0',
  `schema_id` int(11) NOT NULL default '0',
  `attributes` varchar(255) NOT NULL default '',
  `static_data` varchar(255) NOT NULL default '',
  `is_pivot` set('Y','N') NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `fieldcondition` varchar(255) NOT NULL default '',
  `is_boolean` set('Y','N') NOT NULL default 'N',
  `qualify_codes` set('Y','N') NOT NULL default 'N',
  `qualify_cats` set('Y','N') NOT NULL default 'N',
  `truncate` int(11) NOT NULL default '0',
  `strip_tags` set('Y','N') NOT NULL default 'N',
  `is_mandatory` set('Y','N') NOT NULL default '',
  `static_mod` set('A','P','F') NOT NULL default 'F',
  `multi_fields` smallint(6) NOT NULL default '1',
  `has_child` SET( 'Y', 'N' ) NULL,
  `comment` VARCHAR( 255 ) NOT NULL default '',
  PRIMARY KEY  (`element_id`)
);;;



INSERT INTO `xml_export_elements` VALUES (1, 'rss', 'N', 0, 1, '0', 1, 'version =\"2.0\" xmlns:g=\"http://base.google.com/ns/1.0\"', '', '', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (2, 'channel', 'N', 1, 1, '0', 1, '', '', '', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (4, 'description', 'N', 2, 1, '0', 1, '', '%SITE_DESCRIPTION%', '', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (5, 'link', 'N', 2, 1, '0', 1, '', '%BASE_HTTP_PATH%', '', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (6, 'item', 'N', 2, 1, '0', 1, '', '', 'Y', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (11, 'g:expiration_date', 'N', 6, 1, '', 1, '', '%EXPIRE_DATE%', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (14, 'g:immigration_status', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (19, 'g:location', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'Y', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (20, 'g:salary', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (21, 'g:salary_type', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (23, 'publisher', 'N', 22, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (24, 'publisherurl', 'N', 22, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (25, 'lastBuildDate', 'N', 22, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (26, 'job', 'N', 22, 1, '', 2, '', '', 'Y', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (27, 'title', 'N', 26, 1, '2', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (28, 'date', 'N', 26, 1, 'post_date', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (29, 'referencenumber', 'N', 26, 1, 'post_id', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (31, 'company', 'N', 26, 1, '8', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (32, 'city', 'N', 26, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (34, 'country', 'N', 26, 1, '', 2, '', 'USA', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (35, 'postalcode', 'N', 26, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (36, 'description', 'N', 26, 1, 'summary', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (37, 'salary', 'Y', 26, 1, '10', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (38, 'experience', 'Y', 26, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (40, 'jobtype', 'Y', 26, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (42, 'jobs', 'N', 0, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (43, 'job', 'N', 42, 1, '', 4, '', '', 'Y', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (44, 'title', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (45, 'job-code', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (46, 'action', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (47, 'job-board-name', 'N', 43, 1, '', 4, '', '%SITE_NAME%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (48, 'job-board-url', 'N', 43, 1, '', 4, '', '%BASE_HTTP_PATH%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (49, 'detail-url', 'N', 43, 1, '', 4, '', '%LINK%', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (50, 'apply-url', 'N', 43, 1, '', 4, '', '%LINK%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (51, 'description', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (52, 'summary', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'Y', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (53, 'required-skills', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (54, 'required-education', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (55, 'required-experience', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (56, 'full-time', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'Y', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (57, 'part-time', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'Y', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (59, 'flex-time', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'Y', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (60, 'internship', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'Y', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (61, 'volunteer', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'Y', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (62, 'exempt', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'Y', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (63, 'contract', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'Y', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (64, 'permanent', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'Y', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (65, 'temporary', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'Y', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (66, 'telecommute', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'Y', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (67, 'compensation', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (68, 'salary-range', 'N', 67, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (69, 'salary-amount', 'N', 67, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (70, 'salary-currency', 'N', 67, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (71, 'benefits', 'N', 67, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (72, 'posted-date', 'N', 43, 1, '', 4, '', '%DATE%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (73, 'close-date', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (74, 'location', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (75, 'address', 'N', 74, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (76, 'city', 'N', 74, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (77, 'state', 'N', 74, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (78, 'zip', 'N', 74, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (79, 'country', 'N', 74, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (80, 'area-code', 'N', 74, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (81, 'contact', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (82, 'name', 'N', 81, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (84, 'email', 'N', 81, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (85, 'hiring-manager-name', 'N', 81, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (86, 'hiring-manager-email', 'N', 81, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (87, 'phone', 'N', 81, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (88, 'fax', 'N', 81, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (89, 'company', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (90, 'name', 'N', 89, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (91, 'description', 'N', 89, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (92, 'industry', 'N', 89, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (93, 'url', 'N', 89, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (94, 'title', 'N', 2, 1, '', 1, '', '%SITE_NAME%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (96, 'title', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (97, 'description', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'Y', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (98, 'g:job_function', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'Y', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (99, 'g:job_industry', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (100, 'g:job_type', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (101, 'link', 'N', 6, 1, '', 1, '', '%LINK%', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (102, 'g:publish_date', 'N', 6, 1, '', 1, '', '%DATE%', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (103, 'g:education', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (104, 'g:employer', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (105, 'guid', 'N', 6, 1, '', 1, '', '%LINK%', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (106, 'image_link', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (107, 'source', 'N', 0, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (108, 'publisher', 'N', 107, 1, '', 2, '', '%SITE_NAME%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (109, 'publisherurl', 'N', 107, 1, '', 2, '', '%BASE_HTTP_PATH%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (110, 'lastBuildDate', 'N', 107, 1, '', 2, '', '%FEED_DATE%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (111, 'job', 'N', 107, 1, '', 2, '', '', 'Y', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (112, 'title', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (113, 'date', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (114, 'referencenumber', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (115, 'url', 'Y', 111, 1, '', 2, '', '%LINK%', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (116, 'company', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (117, 'city', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (118, 'state', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (119, 'country', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (120, 'postalcode', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (121, 'description', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'Y', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (122, 'salary', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (123, 'education', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (124, 'jobtype', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'Y', 'Y', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (125, 'category', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'Y', 'Y', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (126, 'experience', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (127, 'job-category', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'Y', 0, 'N', 'N', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (128, 'rss', 'N', 0, 1, '', 3, 'version=\"2.0\"', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (129, 'channel', 'N', 128, 1, '', 3, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (130, 'title', 'N', 129, 1, '', 3, '', '%SITE_NAME%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (131, 'link', 'N', 129, 1, '', 3, '', '%BASE_HTTP_PATH%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (132, 'description', 'N', 129, 1, '', 3, '', '%SITE_DESCRIPTION%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (133, 'language', 'N', 129, 1, '', 3, '', '%DEFAULT_LANG%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (134, 'pubDate', 'N', 129, 1, '', 3, '', '%FEED_DATE%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (135, 'lastBuildDate', 'N', 129, 1, '', 3, '', '%FEED_DATE%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (136, 'docs', 'N', 129, 1, '', 3, '', 'http://blogs.law.harvard.edu/tech/rss', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (137, 'generator', 'N', 129, 1, '', 3, '', 'Jamit Job Board XML export tool', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (138, 'managingEditor', 'N', 129, 1, '', 3, '', '%SITE_CONTACT_EMAIL%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (139, 'webMaster', 'N', 129, 1, '', 3, '', '%SITE_CONTACT_EMAIL%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (140, 'image', 'N', 129, 1, '', 3, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (141, 'link', 'N', 140, 1, '', 3, '', '%BASE_HTTP_PATH%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (142, 'title', 'N', 140, 1, '', 3, '', '%SITE_NAME%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (143, 'url', 'N', 140, 1, '', 3, '', '%RSS_FEED_LOGO%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (144, 'height', 'N', 140, 1, '', 3, '', '%RSS_LOGO_HEIGHT%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (145, 'width', 'N', 140, 1, '', 3, '', '%RSS_LOGO_WIDTH%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (146, 'item', 'N', 129, 1, '', 3, '', '', 'Y', '', '', 'N', 'N', 'N', 0, 'N', '', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (147, 'title', 'N', 146, 1, '', 3, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (148, 'link', 'N', 146, 1, '', 3, '', '%LINK%', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (149, 'description', 'N', 146, 1, '', 3, '', '', 'N', '', '', 'N', 'N', 'N', 300, 'Y', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (151, 'pubDate', 'N', 146, 1, '', 3, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y', 'F', 1, NULL, '');;;
INSERT INTO `xml_export_elements` VALUES (152, 'guid', 'N', 146, 1, '', 3, '', '%LINK%', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y', 'F', 1, NULL, '');;;



CREATE TABLE `xml_export_feeds` (
  `feed_id` int(11) NOT NULL auto_increment,
  `feed_name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `field_settings` text NOT NULL,
  `search_settings` text NOT NULL,
  `max_records` int(11) NOT NULL default '0',
  `publish_mode` set('PUB','PRI') NOT NULL default '',
  `schema_id` int(11) NOT NULL default '0',
  `feed_key` varchar(255) NOT NULL default '',
  `hosts_allow` text NOT NULL,
  `is_locked` set('Y','N') NOT NULL default 'N',
  `form_id` int(11) NOT NULL default '0',
  `include_emp_accounts` SET( 'Y', 'N' ) NOT NULL DEFAULT 'N',
  `export_with_url` SET( 'Y', 'N' ) NOT NULL DEFAULT 'Y',
  `include_imported` SET( 'Y', 'N' ) NOT NULL default 'N',
  PRIMARY KEY  (`feed_id`)
);;;



INSERT INTO `xml_export_feeds` VALUES (6, 'RSS Feed (Example)', 'this is a description', 'a:5:{i:147;s:1:\"2\";s:6:\"ft_147\";s:4:\"TEXT\";i:149;s:1:\"5\";s:6:\"ft_149\";s:6:\"EDITOR\";i:151;s:9:\"post_date\";}', 'a:4:{i:6;N;i:13;N;i:5;s:0:\"\";i:14;s:0:\"\";}', 50, 'PUB', 3, '', 'localhost', 'N', 1, 'N', 'Y', 'N');;;
INSERT INTO `xml_export_feeds` VALUES (9, 'Simply Hired Feed (Example)', 'Simply Hired - Jobs', 'a:49:{i:44;s:1:\"2\";s:5:\"ft_44\";s:4:\"TEXT\";i:45;s:7:\"post_id\";i:46;s:0:\"\";i:52;s:7:\"summary\";i:53;s:0:\"\";i:54;s:0:\"\";i:55;s:0:\"\";i:56;s:2:\"14\";s:12:\"boolean_p_56\";s:9:\"full-time\";s:5:\"ft_56\";s:8:\"CATEGORY\";i:57;s:1:\"2\";s:12:\"boolean_p_57\";s:9:\"part-time\";s:5:\"ft_57\";s:4:\"TEXT\";i:59;s:0:\"\";i:60;s:0:\"\";i:61;s:0:\"\";i:62;s:0:\"\";i:63;s:0:\"\";i:64;s:0:\"\";i:65;s:0:\"\";i:66;s:0:\"\";i:68;s:0:\"\";i:69;s:0:\"\";i:70;s:0:\"\";i:71;s:0:\"\";i:73;s:0:\"\";i:75;s:2:\"13\";s:5:\"ft_75\";s:8:\"CATEGORY\";i:76;s:0:\"\";i:77;s:0:\"\";i:78;s:0:\"\";i:79;s:0:\"\";i:80;s:0:\"\";i:82;s:1:\"8\";s:5:\"ft_82\";s:4:\"TEXT\";i:84;s:2:\"12\";s:5:\"ft_84\";s:4:\"TEXT\";i:85;s:0:\"\";i:86;s:0:\"\";i:87;s:0:\"\";i:88;s:0:\"\";i:90;s:1:\"8\";s:5:\"ft_90\";s:4:\"TEXT\";i:91;s:0:\"\";i:92;s:0:\"\";i:93;s:0:\"\";i:127;s:1:\"6\";s:6:\"ft_127\";s:8:\"CATEGORY\";}', 'a:4:{i:6;N;i:13;N;i:5;s:0:\"\";i:14;s:0:\"\";}', 50, 'PUB', 4, '', 'localhost', 'N', 1, 'N', 'Y', 'N');;;
INSERT INTO `xml_export_feeds` VALUES (10, 'Indeed Jobs Feed (Example)', 'My jobs feed to indeed!', 'a:20:{i:112;s:1:\"2\";s:6:\"ft_112\";s:4:\"TEXT\";i:113;s:9:\"post_date\";i:114;s:7:\"post_id\";i:116;s:1:\"8\";s:6:\"ft_116\";s:4:\"TEXT\";i:117;s:2:\"15\";s:6:\"ft_117\";s:4:\"TEXT\";i:118;s:0:\"\";i:119;s:0:\"\";i:120;s:0:\"\";i:121;s:1:\"5\";s:6:\"ft_121\";s:6:\"EDITOR\";i:122;s:0:\"\";i:123;s:0:\"\";i:124;s:2:\"14\";s:6:\"ft_124\";s:8:\"CATEGORY\";i:125;s:1:\"6\";s:6:\"ft_125\";s:8:\"CATEGORY\";i:126;s:0:\"\";}', 'a:4:{i:6;N;i:13;N;i:5;s:0:\"\";i:14;s:0:\"\";}', 50, 'PUB', 2, '', 'localhost', 'N', 1, 'N', 'Y', 'N');;;
INSERT INTO `xml_export_feeds` VALUES (11, 'Google Base Feed (Example)', 'Google Base Feed', 'a:17:{i:14;s:0:\"\";i:19;s:2:\"13\";s:5:\"ft_19\";s:8:\"CATEGORY\";i:20;s:0:\"\";i:21;s:0:\"\";i:96;s:1:\"2\";s:5:\"ft_96\";s:4:\"TEXT\";i:97;s:1:\"5\";s:5:\"ft_97\";s:6:\"EDITOR\";i:98;s:1:\"6\";s:5:\"ft_98\";s:8:\"CATEGORY\";i:99;s:0:\"\";i:100;s:0:\"\";i:103;s:0:\"\";i:104;s:1:\"8\";s:6:\"ft_104\";s:4:\"TEXT\";i:106;s:0:\"\";}', 'a:4:{i:6;N;i:13;N;i:5;s:0:\"\";i:14;s:0:\"\";}', 50, 'PUB', 1, '', 'ALL', 'N', 1, 'N', 'Y', 'N');;;

 

CREATE TABLE `xml_export_schemas` (
  `schema_id` int(11) NOT NULL auto_increment,
  `schema_name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `form_id` int(11) NOT NULL default '0',
  `is_locked` set('Y','N') NOT NULL default 'N',
  PRIMARY KEY  (`schema_id`)
);;;



INSERT INTO `xml_export_schemas` VALUES (1, 'Google Base  - Jobs', 'For a full description of the attributes (elements) see: http://www.google.com/base/jobs.html', 1, 'N');;;
INSERT INTO `xml_export_schemas` VALUES (2, 'Indeed.com', 'http://www.indeed.com/jsp/xmlinfo.jsp', 1, 'Y');;;
INSERT INTO `xml_export_schemas` VALUES (3, 'RSS', 'http://blogs.law.harvard.edu/tech/rss', 1, 'Y');;;
INSERT INTO `xml_export_schemas` VALUES (4, 'SimplyHired.com', 'Simply Hired can accept incoming job feeds in either xml or delimited formats\r\nhttp://www.simplyhired.com/feed.php#feed_spec', 1, 'N');;;

CREATE TABLE `short_urls` (
  `url` varchar(255) NOT NULL,
  `date` timestamp NOT NULL,
  `hash` varchar(255) NOT NULL,
  `expires` set('Y','N') NOT NULL,
  `hits` bigint(20) NOT NULL,
  PRIMARY KEY (`url`));;;

CREATE TABLE `sitemaps_urls` (
	`url` TEXT NOT NULL ,
	`priority` FLOAT NOT NULL ,
	`changefreq` VARCHAR( 15 ) NOT NULL
);;;


CREATE TABLE `xml_import_feeds` (
  `feed_id` int(11) NOT NULL auto_increment,
  `feed_metadata` text NOT NULL,
  `feed_name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `xml_sample` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `feed_key` varchar(255) NOT NULL,
  `ip_allow` text NOT NULL,
  `feed_url` varchar(255) NOT NULL,
  `feed_filename` varchar(255) NOT NULL,
  `ftp_user` varchar(255) NOT NULL,
  `ftp_pass` varchar(255) NOT NULL,
  `ftp_filename` varchar(255) NOT NULL,
  `ftp_host` varchar(255) NOT NULL,
  `status` varchar(10) NOT NULL,
  `pickup_method` varchar(5) NOT NULL,
  `cron` set('Y','N') NOT NULL,
  PRIMARY KEY  (`feed_id`)
);;;

CREATE TABLE `saved_resumes` (
  `resume_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `save_date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`resume_id`,`user_id`)
);

";

if ($_REQUEST['install']!='') {

	mysql_connect($_REQUEST['jb_db_host'], $_REQUEST['jb_db_user'], $_REQUEST['jb_db_pass']) or die(mysql_error());
	
	mysql_select_db($_REQUEST['jb_db_name']) or die(mysql_error());;;

	/* You can use it like this */

	$queries=multiple_query($sql);

	for($i=0;$i<count($queries);$i++)
		if($queries[$i][1]==0){
       /* some code.... with the result in $queries[$i][0] */
	}
	else
		echo "<pre>Error: ".$queries[$i][2]."(".$queries[$i][3].")<br>\n</pre>";

	
	echo count($queries)." Operations Completed.<br>";
	

	JB_compute_cat_has_child();

	echo "Database structure installed. <h3>You can continue to the <A target='_main' href='index.php'>Admin Section</a>. The default password is: ok (Please change it in Main Config)</h3>";

	echo "<font color='red'><b>*** Please remember to delete install.php from the admin directory ***</b></font><br>";

	die();
}


}





?>