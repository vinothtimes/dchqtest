<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

$dir = dirname(__FILE__);

$dir = explode (DIRECTORY_SEPARATOR, $dir);
$blank = array_pop($dir);
$dir = implode('/', $dir);

require($dir."/config.php");
require (dirname(__FILE__)."/admin_common.php");


JB_admin_header('Admin -> Cron Setup');


?>
<h3>About the Cron Daemon</h3>
<p>Cron Daemon is a tool on Unix type servers which allows you to schedule the running of tasks, chronologically. The job board needs to process the Outgoing Email queue every few minutes and  process the email alerts every hour. Additionally, several other 'house keeping' tasks need to be performed. (Note: On Windows a similar tool exists in the System Tools folder called 'Scheduled Tasks')</p> 
<p>
It is recommended that you set up a Cron job / scheduled task for the Job Board. If your hosting company does not provide this service then the job board can emulate a cron job, although this approach is not recommended for large websites. You can turn cron emulation On / Off from the Main Config.
</p>
<p>
The script that needs to be scheduled to run every minute is called cron.php and it's located in the cron/ directory. On <i>this server</i>, the full path to this script is:<b><?php echo $dir."/cron/cron.php";?></b> and the http address is: <b><?php if (defined('JB_CRON_HTTP_ALLOW') && JB_CRON_HTTP_ALLOW!='YES') {?><strike><?php } echo JB_BASE_HTTP_PATH."cron/cron.php"; if (defined('JB_CRON_HTTP_ALLOW') && JB_CRON_HTTP_ALLOW!='YES') {?></strike> - <b>execution via the http address is currently disabled in Admin-&gt;Main Config.</b><?php } ?></b> (assuming from your settings in Main Config)
</p>
<h3>Example: How to setup cron.php to run every 5 minutes</h3>
<p>
<b>For Cpanel based hosting accounts:</b><br><br>
Cpanel based accounts are fortunate to be able to setup cron jobs via web-based interface. Simply go to the Cron jobs page and add the following command in to run every five minutes:<br>
<pre>
nice /usr/bin/php -f <?php echo $dir."/cron/cron.php";?>
</pre>
<b>For accounts with shell access (SSH):</b> <br><br>
Type in this command:<br>
<pre>crontab -e</pre>
Input the following line in this file:<br>
<br><pre>
*/5 * * * * nice /usr/bin/php -f <?php echo $dir."/cron/cron.php";?></pre>
Save this file. (If you are using VIM editor press I to insert, then to save & quit press Esc and then type in :wq and press Enter. If you are using Gnu Nano / Pico then press Ctrl+O then Ctrl+X<br>
The cron will be updated automatically after saving the file.<br>
Note: The 'nice' command was added so that this process will run with a lower priority.<br>
/usr/bin/php is the full path to your php executable. Please refer to your hosting documentation to confirm that this path is correct for your server.
</p>
<hr>
<p>
Note: If the above command does not work, you may try other froms of the command. Once common way is to get a web browser / http client to request the cron.php page.<br>
This can be done in the following ways:<br>
<?php 
if (defined('JB_CRON_HTTP_ALLOW') && JB_CRON_HTTP_ALLOW!='YES') {
	echo '<strike>';
}

$base = JB_BASE_HTTP_PATH;
if ((JB_CRON_HTTP_ALLOW=='YES') && (strlen(JB_CRON_HTTP_USER)>0)) {
	$base = str_replace('://', '://'.JB_CRON_HTTP_USER.':'.JB_CRON_HTTP_PASS.'@', JB_BASE_HTTP_PATH);
}
?>
- If your server supports curl:<br>
<pre>*/5 * * * * curl -s -o /dev/null <?php echo $base."cron/cron.php";?></pre><br>
- If your server supports fetch:<br>
<pre>*/5 * * * * fetch -o /dev/null <?php echo $base."cron/cron.php";?></pre><br>
- If your server supports lynx:<br>
<pre>*/5 * * * * lynx > /dev/null -dump <?php echo $base."cron/cron.php";?></pre><br>
- If your server supports wget:<br>
<pre>*/5 * * * * wget -q -O /dev/null <?php echo $base."cron/cron.php";?></pre><br>
<?php
if (defined('JB_CRON_HTTP_ALLOW') && JB_CRON_HTTP_ALLOW!='YES') {
	echo '</strike> - execution via the http address is currently disabled in Admin-&gt;Main Config.';
}
?>
</p>
<hr>
<h3>Stats</h3>
<?php
$sql = "select val from jb_variables WHERE `key`='LAST_HOURLY_RUN'";
$result = JB_mysql_query($sql);
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$a = $row['val'];

$sql = "select val from jb_variables WHERE `key`='LAST_HOUSEKEEP_RUN'";
$result = JB_mysql_query($sql);
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$b = $row['val'];

?>
Cron Setup: <?php if (JB_CRON_EMULATION_ENABLED=='YES') { echo "Emulated";} else { echo 'Cron Daemon'; } ?><br>
Last Housekeep run: <?php echo date("r",$b); ?> (run every minute)<br>
Last Hourly run: <?php echo date("r",$a); ?>(run every hour)<br>

<?php

JB_admin_footer();


?>