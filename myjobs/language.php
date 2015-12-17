<?php 
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";

$submit = $_REQUEST['submit'];
$lang = $_REQUEST['lang'];
if ($submit != '' ) {
	//$_SESSION['LANG'] == $lang; 
	//echo "language changed";
}
?>

<?php include('login_functions.php'); ?>
<?php JB_process_login(); ?>

<?php JB_template_candidates_header(); ?>



<?php
JB_render_box_top(80,  $label['c_lang_title']); 


if ($submit != '') {

   JB_mysql_query ("UPDATE `users` SET `lang`='".jb_escape_sql($lang)."' WHERE `Username`='".$_SESSION['JB_Username']."' LIMIT 1 ") or die (mysql_error());

   $JBMarkup->ok_msg($label["c_lang_saved"]);

}

   $result  = JB_mysql_query ("SELECT * FROM `users` WHERE `Username`='".$_SESSION['JB_Username']."'");
   $row = mysql_fetch_array($result, MYSQL_ASSOC);
   $lang = $row['lang'];


echo "<div >".$label["c_lang_note"]."</div>";

?>

<p>
<form method="post" action="language.php">

<table border="0" cellSpacing="1" cellPadding="3" class="dynamic_form" id='dynamic_form'>
<tr><td class="dynamic_form_field"><?php echo $label["c_lang_label"]; ?></td>
<td class="dynamic_form_value"><select name="lang" type="select" id="lang"  size="2">

<?php

$sql = "SELECT * FROM lang where is_active='Y' ";
$result = JB_mysql_query ($sql);
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

	if ($lang == $row['lang_code']) { $sel =  "selected"; } else {$sel = '';}

	echo "<option value=".jb_escape_html($row['lang_code'])." $sel>".jb_escape_html($row['name'])."</option>";


}


?>
                    
                     </select></td>
</tr>
   <tr>
   <td colspan="2" class="dynamic_form_value"><input class="form_submit_button" type="submit" name="submit" value="<?php echo $label["c_lang_button_label"]; ?>"></td>

   </tr>

</table>

</form>

<?php 
JB_render_box_bottom();

JB_template_candidates_footer(); ?>