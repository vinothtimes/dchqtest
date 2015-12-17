<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################


require "../config.php";

require (dirname(__FILE__)."/admin_common.php");

require (jb_basedirpath()."include/xml_feed_functions.php");



// This function changes the parent_element_id in $arr
// is similar to the following sql query:
// $sql = "UPDATE xml_export_elements SET parent_element_id='".$element_id."' 
//WHERE parent_element_id='".$old_element_id."' ";

function update_arr_parent_ids(&$arr, $old_element_id, $new_element_id) {
	// also update the field setting (xml_export_feeds, field_settings)
	foreach ($arr as $row_key=>$row) {
		if ($row['parent_element_id'] == $old_element_id) {
			$arr[$row_key]['parent_element_id'] = $new_element_id;	
		}
	}
}

function jb_parse_csv($lines, $table_name='') {
    //$delimiter = empty($options['delimiter']) ? "," : $options['delimiter'];
    //$to_object = empty($options['to_object']) ? false : true;
	$delimiter = ",";
	$to_object = false;
    $expr="/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/"; // added
    $lines = explode("\n", $lines);
	$cols = array_shift($lines);
    $field_names = explode($delimiter, $cols);
    foreach ($lines as $line) {
        // Skip the empty line
        if (empty($line)) continue;
        $fields = preg_split($expr,trim($line)); // added
        $fields = preg_replace("/^\"(.*)\"$/","$1",$fields); //added
        //$fields = explode($delimiter, $line);
        $_res = $to_object ? new stdClass : array();
	
        foreach ($field_names as $key => $f) {
			
			$f = trim($f);
			$key = trim($key);
            
			if (preg_match('#^".+"$#', $fields[$key])) { // if string is quoted
				$fields[$key] = trim ($fields[$key], '"');
			}
			$fields[$key] = str_replace('""', '"', $fields[$key]); // escaped quotes
			$_res[$f] = $fields[$key]; 
            
        }
        $res[] = $_res;
    }
	if ($table_name!='') {
		// check that the columns correspond to the database
		$sql = "SELECT $cols FROM $table_name limit 1 ";
		$result = jb_mysql_query($sql) or  $res=false;
	}
    return $res;
}

JB_admin_header('Admin -> XML Export Schema');

?>
<body>
<b>[XML Export]</b> 
	<span style="background-color: <?php  echo "#F2F2F2";  ?>; border-style:outset; padding:5px; "><a href="xmlfeed.php?export=1">XML Feeds</a></span> <span style="background-color:#FFFFCC; border-style:outset; padding: 5px;"><a href="xmlschema.php">XML Schemas</a></span>	<span style="background-color:#F2F2F2; border-style:outset; padding: 5px;"><a href="xmlhelp.php">XML Help</a></span>
	<hr>
	<input type="button" value="Create a New Schema..." onclick="window.location='xmlschema.php?&new=yes'" >
	<hr>
<?php

if ($_REQUEST['save_schema']!='') {

	$error = JBXM_validate_xml_schema_input();

	if ($error == '') {

		JBXM_save_xml_schema_input();
		$JBMarkup->ok_msg('Changes Saved.');

	} else {

		echo "Error:<br>";
		echo $error;

	}


}

if ($_REQUEST['save_element'] !='' ) {

	$error = JBXM_validate_xml_element_input();

	if ($error == '') {

		JBXM_save_xml_element_input();
		$JBMarkup->ok_msg('Changes Saved.');

	} else {

		echo "Error:<br>";
		echo $error;

	}


}

JBXM_list_xml_schemas();
echo "<hr>";

?>



<?php


if ($_REQUEST['delelement']=='yes') {

		JBXM_delete_xml_element($_REQUEST['element_id']);
		$JBMarkup->ok_msg('Changes Saved');
}


if ($_REQUEST['new']=='yes') {
	echo "<h3>Create a new XML Schema</h3>";
	JBXM_display_xml_schema_form(true);

}

if (($_REQUEST['schema_id']!='') && (($_REQUEST['config']==''))) {
	echo "<h3>XML Schema Settings</h3>";
	JBXM_display_xml_schema_form(true);

}

if ($_REQUEST['config']=='yes') { // configure xml

?>
<table style="border: solid 2px #F0F0F0;" width="100%">
<tr>
<td valign="top" width=300>
<?php

	if (($_REQUEST['save_element']=='') && ($error=='')) {

		JBXM_display_xml_schema_config_form(); 
	}
?>

</td>
<td valign="top">

 <a href="get_csv.php?table=xml_export_elements&amp;schema_id=<?php echo $_REQUEST['schema_id']; ?>"><small>Export structure to CSV</small></a> | <a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?config=yes&amp;form_id=<?php echo $_REQUEST['form_id'];?>&amp;schema_id=<?php echo $_REQUEST['schema_id']; ?>&amp;import=1"><small>Import structure from CSV...</small></a><br>
<?php




	 if ($_REQUEST['do_import']!='') {
		echo "Parsing...";
		$arr = jb_parse_csv(stripslashes($_REQUEST['csv_data']), 'xml_export_elements');
		if ($arr===false) {

			$JBMarkup->error_msg('The CSV data is invalid');


		} else {

			$sql = "DELETE FROM xml_export_elements WHERE schema_id='".jb_escape_sql($_REQUEST['schema_id'])."' ";
			jb_mysql_query($sql);
			
			
			$_REQUEST['import']='';
			
		
			for ($i=0; $i < sizeof($arr); $i++) {
				$row=$arr[$i];
				
				
				foreach ($row as $col_key=>$col_val) {
					//$row[$col_key] = jb_escape_sql((str_replace('""', '"', stripslashes($col_val))));
					$row[$col_key] = jb_escape_sql(addslashes($col_val));
				}
				
				$row['schema_id'] = (int) $_REQUEST['schema_id'];
				
				
				$old_element_id = (int) $row['element_id'];

				if ($row['has_child']=='') {
					$row['has_child']='NULL'; 
				} else {
					$row['has_child']="'".$row['has_child']."'"; // add quotes
				}


				// Note: data in $row was already escaped above
				$sql = 'INSERT INTO `xml_export_elements` (`element_id`, `element_name`, `is_cdata`, `parent_element_id`, `form_id`, `field_id`, `schema_id`, `attributes`, `static_data`, `is_pivot`, `description`, `fieldcondition`, `is_boolean`, `qualify_codes`, `qualify_cats`, `truncate`, `strip_tags`, `is_mandatory`, `static_mod`, `multi_fields`, `has_child`) VALUES (NULL, \''.$row['element_name'].'\', \''.$row['is_cdata'].'\', \''.$row['parent_element_id'].'\', \''.$row['form_id'].'\', \''.$row['field_id'].'\', \''.$row['schema_id'].'\', \''.$row['attributes'].'\', \''.$row['static_data'].'\', \''.$row['is_pivot'].'\', \''.$row['description'].'\', \''.$row['fieldcondition'].'\', \''.$row['is_boolean'].'\', \''.$row['qualify_codes'].'\', \''.$row['qualify_cats'].'\', \''.$row['truncate'].'\', \''.$row['strip_tags'].'\', \''.$row['is_mandatory'].'\', \''.$row['static_mod'].'\', \''.$row['multi_fields'].'\', '.$row['has_child'].')';

				// insert the element


				jb_mysql_query($sql);


				// get the element id

				$element_id = JB_mysql_insert_id();

				// update all the parent_element_id refrences in the $arr
				// find where parent_element_id == old_element_id and change 
				// to the new element_id

				update_arr_parent_ids($arr, $old_element_id, $element_id);

				

				// update all the parent_element_id refrences in the table
				
				$sql = "UPDATE xml_export_elements SET parent_element_id='".$element_id."' WHERE parent_element_id='".$old_element_id."' ";
			
				
				jb_mysql_query($sql);
				

			}
			
			$JBMarkup->ok_msg('Imported XML Schema');
			
		}
	 }

	 if ($_REQUEST['import']!='') {
		 ?>

		 <form method="POST" action="xmlschema.php?config=yes&form_id=<?php echo $_REQUEST['form_id'];?>&schema_id=<?php echo $_REQUEST['schema_id']; ?>&import=1" enctype="multipart/form-data">
		 <b>Please paste in the CSV content, and click the 'Import' button to import the structure:</b><br>
		 <textarea name="csv_data" cols="80" rows="15"><?php echo htmlentities(stripslashes($_REQUEST['csv_data']));?></textarea><br>
		 <input type="submit" value="Import" name="do_import"><br>
		 (Please note: Existing structure will be replaced with the structure imported from the CSV file) 
		 </form>

		 <?php
	 }

	?>
<i>The XML document tree:</i><br>
<?php
JBXM_display_xml_doc_tree($_REQUEST['schema_id']);
?>
</td>

<?php

}

JB_admin_header('Admin -> XML Schema');

?>