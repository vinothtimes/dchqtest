<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require ('../config.php');
//require ('../include/code_functions.php');
require (dirname(__FILE__)."/admin_common.php");



if (($_REQUEST['jb_code_order_by']!=false) && ($_SESSION['ADMIN']!=false) && (JB_DEMO_MODE!='YES')) {

	if (JB_DEMO_MODE!='YES') {

		$filename = "../config.php";
		$handle  = fopen($filename, "rb");
		$contents = fread($handle, filesize($filename));
		fclose ($handle);
		$handle  = fopen($filename, "w");

		$new_contents = JB_change_config_value($contents, 'JB_CODE_ORDER_BY', stripslashes($_REQUEST['jb_code_order_by'])); 
		fwrite($handle , $new_contents, strlen($new_contents));
		fclose ($handle);
	}

}

JB_admin_header('Admin -> Edit Codes');

?>
<b>[Edit Codes]</b>

	<span style="background-color: #F2F2F2; border-style:outset; padding: 5px;"><a href="edit_config.php">Main</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="editcats.php">Categories</a></span>
 <span style="background-color: #FFFFCC; border-style:outset; padding:5px; "><a href="editcodes.php">Codes</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="language.php">Languages</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="emailconfig.php">Email Templates</a></span>	
	
	
<hr>

<?php




function list_code_groups ($form_id) {

	$form_id = (int) $form_id;

	$sql = "select * FROM `form_fields` WHERE form_id='$form_id' AND (field_type='CHECK' OR field_type='RADIO' OR field_type='SELECT' OR field_type='MSELECT' ) ";
	$result = JB_mysql_query ($sql) or die (mysql_error());
	
	if (mysql_num_rows($result)==0) {
		echo " (0 codes)";
	}
	echo "<ul>";
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		
?>
		<li><a href="" onclick="window.open('maintain_codes.php?field_id=<?php echo $row['field_id'];?>', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=400,height=500,left = 150,top = 150');return false;" ><?php echo $row['field_label']; ?></a>
<?php
	

	}

	echo "</ul>";


}

if (!defined('JB_CODE_ORDER_BY')) define('JB_CODE_ORDER_BY', 'BY_CODE'); 

if (isset($_REQUEST['jb_code_order_by'])) {

	$JB_CODE_ORDER_BY = $_REQUEST['jb_code_order_by'];

} else {
	$JB_CODE_ORDER_BY = JB_CODE_ORDER_BY;
}

?>
<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
 <table border="0" cellpadding="5" cellspacing="2" style="border-style:groove" width="100%" bgcolor="#FFFFFF">
 <tr>
      <td bgcolor="#e6f2ea" width="20%"><font face="Verdana" size="1"> 
       Sort Radio Buttons, Drop-downs & Multiple Selects</font></td>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">
	   <input type="radio" name="jb_code_order_by" value="BY_CODE" <?php if ($JB_CODE_ORDER_BY=='BY_CODE') { echo " checked "; } ?> >Sort by codes, eg. AU, BE, CA
	   <br><input type="radio" name="jb_code_order_by" value="BY_NAME" <?php if ($JB_CODE_ORDER_BY=='BY_NAME') { echo " checked "; } ?> >Sort by names, eg. Australia, Belgium, Canada</font></td>
    </tr>
	<tr>
      <td bgcolor="#e6f2ea" width="100%" colspan="2"><font face="Verdana" size="1"> 
       <input type="submit" name="set_order_by" value="Submit"></font></td>
     
    </tr>
 </table>
 </form>
<?php

if (!$_REQUEST['field_id']) {
  ?>
  <h3>Edit Codes</h3>
  Codes are used for Radio Buttons, Drop-downs & Multiple Select fields. Here you can edit the codes for each of the forms.
  Select the code group that you would like to edit:<p><?php
  echo "<b>Posting Form:</b>";
  list_code_groups (1);
  echo "<b>Resume Form:</b>";
  list_code_groups (2);
  echo "<b>Profile Form:</b>";
  list_code_groups (3);
  echo "<b>Employer's Form:</b>";
  list_code_groups (4);
  echo "<b>Candidate's Form:</b>";
  list_code_groups (5);
  jbplug_do_callback('admin_list_codes', $A=false);

}

JB_admin_footer();


?>
