<?php 

###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
ini_set('max_execution_time', 10000);

require ('../config.php');
require (dirname(__FILE__)."/admin_common.php");

//require ('../include/code_functions.php');
$field_id = $_REQUEST['field_id'];
$code = $_REQUEST['code'];
$description = $_REQUEST['description'];
$modify = $_REQUEST['modify'];

JB_admin_header('Admin -> Maintain Codes');

if (!$_REQUEST['field_id']) {
  echo "Select the code group that you would like to edit:<p>";
  echo "Posting Form:";
  list_code_groups (1);
  echo "Resume Form:";
  list_code_groups (2);
  echo "Profile Form:";
  list_code_groups (3);
  echo "<b>Employer's Form:</b>";
  list_code_groups (4);
  echo "<b>Candidate's Form:</b>";
  list_code_groups (5);
  die ();

} 

function parse_csv($data, $delimiter = ',', $enclosure = '"', $newline = "\n"){
        $pos = $last_pos = -1;
        $end = strlen($data);
        $row = 0;
        $quote_open = false;
        $trim_quote = false;

        $return = array();

        // Create a continuous loop
        for ($i = -1;; ++$i){
            ++$pos;
            // Get the positions
            $comma_pos = strpos($data, $delimiter, $pos);
            $quote_pos = strpos($data, $enclosure, $pos);
            $newline_pos = strpos($data, $newline, $pos);

            // Which one comes first?
            $pos = min(($comma_pos === false) ? $end : $comma_pos, ($quote_pos === false) ? $end : $quote_pos, ($newline_pos === false) ? $end : $newline_pos);

            // Cache it
            $char = (isset($data[$pos])) ? $data[$pos] : null;
            $done = ($pos == $end);

            // It it a special character?
            if ($done || $char == $delimiter || $char == $newline){

                // Ignore it as we're still in a quote
                if ($quote_open && !$done){
                    continue;
                }

                $length = $pos - ++$last_pos;

                // Is the last thing a quote?
                if ($trim_quote){
                    // Well then get rid of it
                    --$length;
                }

                // Get all the contents of this column
                $return[$row][] = ($length > 0) ? str_replace($enclosure . $enclosure, $enclosure, substr($data, $last_pos, $length)) : '';

                // And we're done
                if ($done){
                    break;
                }

                // Save the last position
                $last_pos = $pos;

                // Next row?
                if ($char == $newline){
                    ++$row;
                }

                $trim_quote = false;
            }
            // Our quote?
            else if ($char == $enclosure){

                // Toggle it
                if ($quote_open == false){
                    // It's an opening quote
                    $quote_open = true;
                    $trim_quote = false;

                    // Trim this opening quote?
                    if ($last_pos + 1 == $pos){
                        ++$last_pos;
                    }

                }
                else {
                    // It's a closing quote
                    $quote_open = false;

                    // Trim the last quote?
                    $trim_quote = true;
                }

            }

        }

        return $return;
    }


function can_delete_code ($field_id, $code) {

	$sql = "SHOW TABLES";
	$tables = JB_mysql_query ($sql);

	$tables = array ('posts_table', 'resumes_table', 'profiles_table', 'employers', 'users');
	foreach ($tables as $table ) {

		$sql = "SHOW COLUMNS FROM ".jb_escape_sql($table);
		$cols = JB_mysql_query ($sql);

		while ($c_row = mysql_fetch_row($cols)) {
			if ($c_row[0] == $field_id) {

				$sql = "SELECT * FROM ".jb_escape_sql($table)." WHERE `".jb_escape_sql($field_id)."` like '%".jb_escape_sql($code)."%' ";
				$result = JB_mysql_query ($sql);
				if (mysql_num_rows($result)==0) {
					
					return true;
				} else {
					//echo $sql;
					return false;
				}


			}
		}


	}


}



if ($_REQUEST['action'] == 'delete' ) {

	$field_id = $_REQUEST['field_id'];
	$code = $_REQUEST['code'];

	$sql = "DELETE from `codes` where `field_id`='".jb_escape_sql($field_id)."' AND `code`='".jb_escape_sql($code)."' ";
	JB_mysql_query ($sql) or die (mysql_error());

	$sql = "DELETE from `codes_translations` where `field_id`='".jb_escape_sql($field_id)."' AND `code`='".jb_escape_sql($code)."' ";
	JB_mysql_query ($sql) or die (mysql_error());

	$JBMarkup->ok_msg('Code Deleted.'); 

	$_REQUEST['action'] = '';
}

if ($_REQUEST['do_change']!='') {

	echo 'Changing id...';


	$error = validate_code ($_REQUEST['field_id'], $_REQUEST['new_code_id'], 'ok');


	if ($error =='') {
		JB_change_code_id ($_REQUEST['field_id'], $_REQUEST['code'], $_REQUEST['new_code_id']);
		$JBMarkup->ok_msg('Code changed.'); 

	} else {

		?>

		<b><font color="#ff0000">ERROR:</font> Cannot save new code because:</b><br>
		  <?php
		   echo $error."<br>";

	
		$_REQUEST['action'] ='change';


	}


}

if ($_REQUEST['action'] == 'change' ) {

?>

You can change the code id. Since the options are sorted by code id, you can get the option list to sort in a particular order by changing the code id. <b>Note: changing the code id can be a large / risky operation if you have many records in the database. This is because the script needs to update each record individually. Please be patient and do not close this window until the process is complete.</b>

<form method='post' action='maintain_codes.php'>

<input type="hidden" name="field_id" value="<?php echo htmlentities($field_id); ?>">
<input type="hidden" name="code" value="<?php echo htmlentities($code); ?>">
Current code id: <?php echo $code; ?><br>
Enter new code id: <input type="text" name="new_code_id" value=""><br>
<input type='submit' value='Change' name='do_change'>
</form>
<hr>
<?php

	$_REQUEST['action'] = '';

}



global $AVAILABLE_LANGS;
	echo "Current Language: [".$_SESSION["LANG"]."] Select language:";
?>
<form name="lang_form">
<input type="hidden" name="field_id" value="<?php echo htmlentities($field_id); ?>">
<input type="hidden" name="mode" value="<?php echo $mode; ?>">
<select name='lang' onChange="document.lang_form.submit()">
<?php
foreach ($ACT_LANG_FILES as $key => $val) {
	$sel = '';
	if ($key==$_SESSION["LANG"]) { $sel = " selected ";}
	echo "<option $sel value='".$key."'>".$AVAILABLE_LANGS [$key]."</option>";

}

?>

</select>
</form>


<?php


if ($modify == "yes") {
	
	
   JB_modify_code($field_id, $code, $description);
   $code = '';
   $JBMarkup->ok_msg('Changes Saved.'); 
    ?>


   <?php
}

function validate_code ($field_id, $new_code, $new_description) {
	if ($new_code == '') {
		$error .= "- Code is blank<br>";
	} 
	if ($new_description== '') {
		$error .= "- Description is blank<br>";
	}

	if ($new_code != '') {

		$sql = "SELECT * from codes where field_id='".jb_escape_sql($field_id)."' AND code like '%".jb_escape_sql($new_code)."%' ";
		$result = JB_mysql_query ($sql) or die (mysql_error());

		if (mysql_num_rows($result)>0) {
			$error .= "- The new Code is too similar to an already existing code. Please try to come up with a different code.<br>";
		}


	}
	
	

	return $error;


}

if ($_REQUEST['bulk_submit']!='') {

	

	$lines = parse_csv($_REQUEST['csv_codes']);


	foreach ($lines as $line) {

		echo $line[0].", ".$line[1]."<br>";

		 $error = validate_code ($field_id, trim($line[0]), trim($line[1]));
		 if ($error== '') {
			 JB_insert_code($field_id, trim($line[0]), trim($line[1]));
		 } else {

			 $JBMarkup->error_msg('<b>ERROR!</b> Cannot save '.$line[0].' code because:');
			 echo $error;
		 }
		 $error='';
	}

	$JBMarkup->ok_msg('Bulk import complted'); 


}

if ($_REQUEST['new_code'] != '' ) {

	$error = validate_code ($field_id, $_REQUEST['new_code'], $_REQUEST['new_description']);
	if ($error == '') {
	   JB_insert_code($field_id, trim($_REQUEST['new_code']), trim($_REQUEST['new_description']));
	   $_REQUEST['new_code']='';
	   $_REQUEST['new_description']='';
	   $_REQUEST['code'] = '';
	   $_REQUEST['action'] = '';
	  $JBMarkup->ok_msg('Changes Saved'); 

	   ?>


	   <?php
	} else {
		   $JBMarkup->error_msg('<b>ERROR!</b> Cannot save new code because:');
		   echo $error;
	}

}





if ($_REQUEST['bulk']) {

	?>
	<h3>Bulk Import</h3>
	Instructions: Enter a comma list of options, in the following format:<br>
	<i>option code</i>, <i>option name</i><br>
	eg.<br>
	<pre>
ARC, Architecture
CS, Computer Science
STAT, Statistics
	</pre>
	<br>
	<p>
	<b>Important:</b><br>
	- Each code must be a unique alphanumeric value, 2-4 characters in length. 3 Letter codes are the most convenient<br>
	- Each code cannot contain another code inside itself, eg if you have code called BC then code ABCD would not be accepted because ABCD has the letters BC in the middle.<br>
</p>
	<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
	<textarea name="csv_codes" rows="10" cols="45"><?php echo jb_escape_html($_REQUEST['csv_codes']); ?></textarea>
	<input type="hidden" name="field_id" value="<?php echo jb_escape_html($_REQUEST['field_id']); ?>">
	<input type="hidden" name="bulk" value="1"><br>
	<input type="submit" name="bulk_submit" value="Submit">
	</form>
	
	<?php


} 

if ($_REQUEST['export']) {

	$sql = "SELECT * FROM `codes_translations` WHERE `field_id`='".JB_escape_sql($field_id)."' and lang='".JB_escape_sql($_SESSION['LANG'])."' order by description ";
	?>
Exported list: copy from below:<br>
	<textarea cols="45" rows=10><?php
	$result=jb_mysql_query($sql);
	while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {
		echo $row['code'].', '.$row['description']."\n";
	}
	?>
	</textarea>
	<?php

}

?>
<form method="POST" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
<p>
<table border="1">
<tr>
<td><b>Code</b></td>
<td><b>Description</b></td>
<td></td>
</tr>
<?php

JB_format_codes_translation_table($field_id);
if ($_SESSION['LANG'] == '' ) {
	$sql = "SELECT `code`, `description` FROM `codes` WHERE field_id='".jb_escape_sql($field_id)."'";
} else {
	$sql = "SELECT `code`, `description` FROM `codes_translations` WHERE field_id='".jb_escape_sql($field_id)."' AND `lang`='".jb_escape_sql($_SESSION['LANG'])."' ";

}


$result = JB_mysql_query ($sql) or die($sql.mysql_error());

while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

   if ($code == $row['code']) {
	  echo '<tr bgcolor="FFFFCC">'."\n";
   }
   else {
	  echo "<tr>\n";
   }

   echo "<td>\n";
   echo '<A Href="'.htmlentities($_SERVER['PHP_SELF']).'?field_id='.jb_escape_html($field_id).'&code='.jb_escape_html($row['code']).'">'."\n";
   echo $row['code'];
   echo '</a>'."\n";
   echo "</td>\n";
   echo "<td>\n";
   if ($code == $row['code']) {
	  echo '<input name="description" type="text" size="30" value="'.jb_escape_html($row['description']).'">';
	  echo '<input name="modify" type="hidden" value="yes">';
	  echo '<input name="code" type="hidden" value="'.jb_escape_html($row['code']).'">';
	  echo '<input name="field_id" type="hidden" value="'.jb_escape_html($field_id).'">';
   }
   else {
	  echo $row['description'];
   }
   echo "</td>\n";
   echo "<td>\n";
   $disabled = ""; $n = "";
   if (!can_delete_code ($field_id, $row['code'])) {
	   $disabled = " disabled ";
	   $n = "*";
   }
   echo '<input type="button" onclick="window.location=\''.htmlentities($_SERVER['PHP_SELF']).'?action=delete&amp;field_id='.jb_escape_html($field_id).'&amp;code='.urlencode($row['code']).'\'" name="" value="Delete" '.$disabled.' >'.$n;
   echo '&nbsp;<input type="button" onclick="window.location=\''.htmlentities($_SERVER['PHP_SELF']).'?action=change&amp;field_id='.jb_escape_html($field_id).'&amp;code='.urlencode($row['code']).'\'" name="" value="Change id" >';
   echo "</td>\n";
   
   echo "</tr>\n";

}
?>
<tr>
<td><input name="new_code" type="text" size="4" value="<?php echo jb_escape_html($_REQUEST['new_code']); ?>" ></td>
<td><input name="new_description" type="text" value="<?php echo jb_escape_html($_REQUEST['new_description']); ?>" size="30">
<?php
if ($field_id != '') {
   echo '<input name="field_id" type="hidden" value="'.jb_escape_html($field_id).'">';
}
?>

</td>
<td>&lt;-new code</td>
<tr>
<tr>
<td colspan="2">
<input type="submit" name="save" value="Save">
</td>
</tr>

</table>
</form>

<i>* = Cannot delete because Code is in use by a record. Delete / alter the record(s) before deleting the Code.</i><br>
- Got many codes to import? Go to <a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?field_id=<?php echo $_REQUEST['field_id']; ?>&bulk=1">Bulk Import</a> of codes.<br>
- <a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?field_id=<?php echo $_REQUEST['field_id']; ?>&export=1">Export list</a>

<center><input type="button" name="" value="Close" onclick="window.opener.location.reload();window.close()"></center>

<?php

JB_admin_footer();
?>