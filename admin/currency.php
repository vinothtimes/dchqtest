<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

require ("../config.php");
require (dirname(__FILE__)."/admin_common.php");



JB_admin_header('Admin -> Currency');

?>

<b>[Prices]</b>
	<span style="background-color: #FFFFCC; border-style:outset; padding: 5px;"><a href="currency.php">Currency Rates</a></span>
<hr>

<?php
function is_reserved_currency ($code) {
	
	switch ($code) {
		case "AUD":
			return true;
			break;
		case "CAD":
			return true;
			break;
		case "EUR":
			return true;
			break;
		case "GBP":
			return true;
			break;
		case "JPY":
			return true;
			break;
		case "USD":
			return true;
			break;

	}

	return false;

}
function validate_input() {

	if (trim($_REQUEST['code'])=='') {
		$error .= "- Currency code is blank<br>";

	}

	if (trim($_REQUEST['name'])=='') {
		$error .= "- Currency name is blank<br>";

	}

	if (trim($_REQUEST['rate'])=='') {
		$error .= "- Currency rate is blank<br>";

	}

	if (trim($_REQUEST['decimal_point'])=='') {
		$error .= "- Decimal point is blank<br>";

	}

	if (trim($_REQUEST['thousands_sep'])=='') {
		$error .= "- Thousands seperator is blank<br>";

	}

	return $error;


}

if ($_REQUEST['action'] == 'delete') {
	if (is_reserved_currency ($_REQUEST['code'])) {
		echo "<b>Cannot delete</b> - This currency is reserved by the system<br>";

	} else {

		$sql = "DELETE FROM currencies WHERE code='".jb_escape_sql($_REQUEST['code'])."' ";
		JB_mysql_query($sql) or die(mysql_error().$sql);
	}

}

if ($_REQUEST['action'] == 'set_default') {
	$sql = "UPDATE currencies SET is_default = 'N' WHERE code <> '".jb_escape_sql($_REQUEST['code'])."' ";
	JB_mysql_query($sql) or die(mysql_error().$sql);

	$sql = "UPDATE currencies SET is_default = 'Y' WHERE code = '".jb_escape_sql($_REQUEST['code'])."' ";
	JB_mysql_query($sql) or die(mysql_error().$sql);

}

if ($_REQUEST['submit']!='') {

	$error = validate_input();

	if ($error != '') {

		echo "Error: cannot save due to the following errors:<br>";
		echo $error;

	} else {

		$_REQUEST['decimal_places'] = (int) $_REQUEST['decimal_places'];
		$_REQUEST['rate'] = (float) $_REQUEST['rate'];

		if ($_REQUEST['is_default']==false) { $_REQUEST['is_default']='N'; }

		$sql = "REPLACE INTO currencies(code, name, rate, sign, decimal_places, decimal_point, thousands_sep, is_default) VALUES ('".jb_escape_sql($_REQUEST['code'])."', '".jb_escape_sql($_REQUEST['name'])."', '".jb_escape_sql($_REQUEST['rate'])."',  '".jb_escape_sql($_REQUEST['sign'])."', '".jb_escape_sql($_REQUEST['decimal_places'])."', '".jb_escape_sql($_REQUEST['decimal_point'])."', '".jb_escape_sql($_REQUEST['thousands_sep'])."', '".jb_escape_sql($_REQUEST['is_default'])."') ";

		//echo $sql;

		JB_mysql_query ($sql) or die (mysql_error());

		$_REQUEST['new'] ='';
		$_REQUEST['action'] = '';
		//print_r ($_REQUEST);


	}

}

?>
All currency rates are relative to the USD. Therefore, the rate for the USD is always 1.00<br>

<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" >
			<tr bgColor="#eaeaea">
				<td><b><font size="2">Currency</b></font></td>
				<td><b><font size="2">Code</b></font></td>
				<td><b><font size="2">Rate</b></font></td>
				<td><b><font size="2">Sign</b></font></td>
				<td><b><font size="2">Decimal<br>Places</b></font></td>
				<td><b><font size="2">Decimal<br>Point</b></font></td>
				<td><b><font size="2">Thousands<br>Seperator</b></font></td>
				<td><b><font size="2">Is Default</b></font></td>
				<td><b><font size="2">Action</b></font></td>
			</tr>
<?php
			$result = JB_mysql_query("select * FROM currencies order by name") or die (mysql_error());
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

				?>

				<tr bgcolor="<?php echo ($row['code']==$_REQUEST['code']) ? '#FFFFCC' : '#ffffff'; ?>">

				<td><font size="2"><?php echo $row['name'];?></font></td>
				<td><font size="2"><?php echo $row['code'];?></font></td>
				<td><font size="2"><?php echo $row['rate'];?></font></td>
				<td><font size="2"><?php echo $row['sign'];?></font></td>
				<td><font size="2"><?php echo $row['decimal_places'];?></font></td>
				<td><font size="2"><?php echo $row['decimal_point'];?></font></td>
				<td><font size="2"><?php echo $row['thousands_sep'];?></font></td>
				<td><font size="2"><?php echo $row['is_default'];?></font></td>
				<td><font size="2"><?php if ($row['is_default']!='Y') { ?><a href='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=set_default&amp;code=<?php echo $row['code'];?>'>Set to Default</a> /<?php } ?> <a href='<?php echo $SERVER['PHP_SELF'];?>?action=edit&amp;code=<?php echo $row['code'];?>'>Edit</a> / <a href='<?php echo $SERVER['PHP_SELF'];?>?action=delete&amp;code=<?php echo $row['code'];?>'>Delete</a></font></td>
				
				</tr>


				<?php

			}
?>
</table>
<input type="button" value="New Currency..." onclick="window.location='currency.php?new=1'">
<?php

if ($_REQUEST['new']=='1') {
	echo "<h4>New Currency:</h4>";
	//echo "<p>Note: Make sure that you create a file for your new language in the /lang directory.</p>";
}
if ($_REQUEST['action']=='edit') {
	echo "<h4>Edit Currency:</h4>";

	$sql = "SELECT * FROM currencies WHERE `code`='".jb_escape_sql($_REQUEST['code'])."' ";
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$_REQUEST['name'] = $row['name'];
	$_REQUEST['rate'] = $row['rate'];
	$_REQUEST['sign'] = $row['sign'];
	$_REQUEST['is_default'] = $row['is_default'];
	$_REQUEST['decimal_point'] = $row['decimal_point'];
	$_REQUEST['thousands_sep'] = $row['thousands_sep'];
	$_REQUEST['decimal_places'] = $row['decimal_places'];
}

if (($_REQUEST['new']!='') || ($_REQUEST['action']=='edit')) {

	?>
<form action='currency.php' method="post">

<input type="hidden" value="<?php echo jb_escape_html($_REQUEST['action']); ?>" name="action" >
<input type="hidden" value="<?php echo jb_escape_html($_REQUEST['lang_code']); ?>" name="lang_code" >
<input type="hidden" value="<?php echo jb_escape_html($_REQUEST['is_default']);?>" name="is_default" >
<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9">
<tr bgcolor="#ffffff" ><td><font size="2">Currency Name:</font></td><td><input size="30" type="text" name="name" value="<?php echo jb_escape_html($_REQUEST['name']); ?>"> eg. Korean Won</td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">Currency Code:</font></td><td><input <?php echo $disabled; ?> size="2" type="text" name="code" value="<?php echo jb_escape_html($_REQUEST['code']); ?>"> eg. KRW</td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">Currency Rate:</font></td><td><input <?php echo $disabled; ?> size="5" type="text" name="rate" value="<?php echo jb_escape_html($_REQUEST['rate']); ?>">($1 USD = x in this currency)</td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">Currency Sign:</font></td><td><input <?php echo $disabled; ?> size="1" type="text" name="sign" value="<?php echo jb_escape_html($_REQUEST['sign']); ?>">(eg. &#165;)</td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">Currency Decimals:</font></td><td><input <?php echo $disabled; ?> size="1" type="text" name="decimal_places" value="<?php echo jb_escape_html($_REQUEST['decimal_places']); ?>">(eg. 2)</td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">Decimal Point:</font></td><td><input  size="1" type="text" name="decimal_point" value="<?php echo jb_escape_html($_REQUEST['decimal_point']); ?>">(eg. .)</td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">Thousands Seperator:</font></td><td><input  size="1" type="text" name="thousands_sep" value="<?php echo jb_escape_html($_REQUEST['thousands_sep']); ?>">(eg. ,)</td></tr>
</table>
<input type="submit" name="submit" value="Submit">
</form>

	<?php

}
JB_admin_footer();


?>
