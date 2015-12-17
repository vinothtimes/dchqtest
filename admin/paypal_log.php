<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require ('../config.php');
require (dirname(__FILE__)."/admin_common.php");

require ("../payment/payment_manager.php");


if ($_REQUEST["module"]=='') {
	$_REQUEST["module"] = "PayPal";

}

JB_admin_header('Admin -> Payment Log');

?>

<b>[Payment Log]</b>

<span style="background-color: #FFFFCC; border-style:outset; padding: 5px;"><a href="paypal_log.php">Payment Log</a></span>
<span style="background-color: #F2F2F2; border-style:outset; padding: 5px;"><a href="whois_online.php">Who's Online</a></span>

<hr>
<p>
The Payment Log file is useful for analyzing / troubleshooting problems with the the payment modules.
</p>

<form name="lang_form">
Select Module: 
<select name='module' onChange="document.lang_form.submit()">
<?php
	foreach  ($_PAYMENT_OBJECTS as $key => $val) {
		$sel = '';
		if ($key==$_REQUEST['module']) { $sel = ' selected ';}
		echo "<option $sel value='".$key."'>".$key."</option>";

}

?>

</select>
</form>


<?php
//$logfile = "../payment/logs.txt";

$module = 'PayPal';

if ($_REQUEST['clear']!='') {

	JB_payment_log_clear_db($_REQUEST['module']);

	$JBMarkup->ok_msg('Log Cleared.');


}



$c = JB_payment_log_fetch_db($_REQUEST["module"]);



?>
<input type="button" value="Clear Log" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF'])."?clear=yes&module=".htmlentities($_REQUEST['module']);?>'"> | <input type="button" value="Refresh" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF'])."?module=".htmlentities($_REQUEST['module']);?>'" ><br>
<textarea cols="80" rows="20"><?php echo htmlentities($c);?></textarea>

<?php

JB_admin_footer();

?>