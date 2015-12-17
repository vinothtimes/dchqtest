<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

require "../config.php";
require (dirname(__FILE__)."/admin_common.php");
require ("../include/xml_feed_functions.php");

ini_set('max_execution_time', 2000);

// for applications:
 
//  ALTER TABLE `applications` ADD INDEX `composite` ( `employer_id` , `post_id` , `app_date` )

// ALTER TABLE `saved_resumes` ADD INDEX `composite` ( `save_date` , `user_id` , `resume_id` )  


JB_admin_header('Admin -> DB Tools');

?>
<b>[Database Tools]</b> 
	<span style="background-color: <?php  echo "#FFFFCC";  ?>; border-style:outset; padding:5px; "><a href="dbtools.php">Indexing</a></span>
	<span style="background-color: <?php  echo "#F2F2F2";  ?>; border-style:outset; padding:5px; "><a href="db_repair.php">Repair</a></span>
<hr>

<?php
function JB_update_table_index($form_id, $do_update) {
	
	$table_name = JB_get_table_name_by_id($form_id);

	if ($form_id==77) {
		$table_name='skill_matrix_data';
	}

	if ($form_id==33) {
		$table_name='categories';
	}

	if ($form_id==34) {
		$table_name='cat_name_translations';
	}

	

	// Get all the fields that can be indexed.
	$sql = "SELECT * FROM `form_fields` where form_id='$form_id' AND ((field_type ='CATEGORY' OR field_type ='CHECK' OR field_type = 'CURRENCY' OR field_type = 'DATE' OR field_type = 'DATE_CAL' OR field_type = 'INTEGER'  OR field_type = 'NUMERIC'   OR field_type = 'TEXT' OR field_type = 'URL' ) OR field_type='CATEGORY' AND is_in_search='Y')  ";
	// OR field_type = 'EDITOR' OR field_type = 'TEXTAREA'
	// OR field_type = 'RADIO' OR field_type = 'SELECT'
	// OR field_type = 'MSELECT'
	$result = JB_mysql_query($sql) or die (mysql_error());
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		//$fields[$row['field_id']]['field_id'] = $row['field_id'];
		$fields[$row['field_id']]['field_type'] = $row['field_type'];
		//$index_f .= $comma1.'`'.$row['field_id'].'` ';
		//$comma1 = ",";

	}
//	echo "<hr>$sql</hr>";
//print_r($fields);
	// additional keys 

	switch ($form_id) {

		case 1:
			$fields['post_date']['field_id'] = 'post_date';
			$fields['composite_index']['field_id'] = 'approved` , `expired` , `post_date';
			//$fields['composite_indexp']['field_id'] = 'approved` , `expired` , `post_mode` , `post_date';
			//echo "<pre>"; print_r($fields); echo "</pre>";
			
			foreach ($fields as $key=>$val) {
				
				if ($fields[$key]['field_type']=='CATEGORY') {
					
					//echo $fields[$key]['field_id']."<br>";
					$cats .= $comma.'`'.$key.'` ';
					$comma = ",";
				} else {
					

				}
				
			}
			$fields['composite_cats']['field_id'] = "approved` , `expired` , $cats, `post_date"; 
			$fields['composite_user']['field_id'] = "approved` , `expired` , `user_id`, `post_date";
			$fields['guid_index']['field_id'] = 'guid';
			
			break;
		case 2: 
			//$fields['stat_index']['field_id'] = 'status` , `approved`, `resume_id';
			$fields['composite_index']['field_id'] = "resume_date`,   `approved`, `status` , `resume_id";
			break;
		case 77:
			$fields['name_obj_id']['field_id'] = 'name`, `object_id';
			$fields['obj_id_name']['field_id'] = 'object_id`, `name';
			break;
		case 33:
		$fields['composite_index']['field_id'] = "parent_category_id`, `category_id";
			
			break;
		case 34:
			$fields['composite_index']['field_id'] = "category_id`,  `lang";
			break;
		

	}


	// Get the current keys


	$sql = " show index from $table_name ";
	//echo "$sql<br>";
	$result = JB_mysql_query($sql) or die (mysql_error());
	while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {
		//print_r($row); echo "<br>";

		if (($row['Key_name']!= 'PRIMARY') && (($row['Non_unique']== '1'))) {
			$index_names[$row['Key_name']] = $row['Key_name'];
		}
	}

	//echo "<pre>";
	
	//print_r($index_names);
	if (sizeof($fields)>0) {
		foreach ($fields as $key=>$val) {

			
			# If exists in form but not table, add to table
			if (($index_names[$key] == '') && 
				($fields[$key]['field_id'] != '')) { // ADD to table
				//if ($i>0) {$sql .= ", ";}
				$sql = "ALTER TABLE `$table_name` ADD INDEX `".jb_escape_sql($key)."` ( `".jb_escape_sql($fields[$key]['field_id'])."` ) "; 
				//echo $sql."<br>";
				if ($do_update) JB_mysql_query($sql)or die(mysql_error());
				$update_needed =true;
			}

			$i++;

		}
	}
//print_r($index_names);
	if (sizeof($index_names)>0) {
		foreach ($index_names as $key=>$val) {

			# if NOT exists form, but is in table, 
			if (($index_names[$key] != '') && 
				($fields[$key]['field_id'] == '')) { // REMOVE from table
				$sql = "ALTER TABLE `$table_name` DROP INDEX `".jb_escape_sql($index_names[$key])."` ";
				echo $sql."<br>";
				if ($do_update) JB_mysql_query($sql) or die(mysql_error());
				$update_needed = true;
			}

			$i++;

		}

	}

	//$sql = "ALTER TABLE mail_queue ADD INDEX `srm` (`status`, `retry_count`, `mail_date`)";

	//JB_mysql_query($sql);
	

	$sql = "ANALYZE TABLE `".jb_escape_sql($table_name)."`  ";
	JB_mysql_query($sql);

}

if ($_REQUEST['update']!='') {
	JB_update_table_index($_REQUEST['update'], true);
	$JBMarkup->ok_msg('Table Index updated');
}

if ($_REQUEST['update_all']!='') {
	JB_update_table_index(1, true);
	JB_update_table_index(2, true);
	JB_update_table_index(77, true);
	JB_update_table_index(33, true);
	JB_update_table_index(34, true);
	$JBMarkup->ok_msg('Table Index Updated.');

}

//JB_update_table_index(3);

?>
Here the job board can add additional indexes for some tables to speed up some of the most frequently executed queries.
<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9">
<tr bgColor="#eaeaea">
<td><b>Table</b></td>
<td><b>Index Status</b></td>
<td><b>Action</b></td>
</tr>
<tr bgcolor='#ffffff'>
<td>Posts Table</td>
<td><?php if ( JB_update_table_index(1, false)) { echo "Update Needed"; } else {echo 'Up-to-date';}?></td>
<td><input type="button" value='Update Index' onclick="window.location='dbtools.php?update=1'" ></td>
</tr>
<tr bgcolor='#ffffff'>
<td>Resume Table</td>
<td><?php if ( JB_update_table_index(2, false)) { echo "Update Needed"; } else {echo 'Up-to-date';}?></td>
<td><input type="button" value='Update Index' onclick="window.location='dbtools.php?update=2'" ></td>
</tr>
<tr bgcolor='#ffffff'>
<td>Resume (SX)</td>
<td><?php if ( JB_update_table_index(77, false)) { echo "Update Needed"; } else {echo 'Up-to-date';}?></td>
<td><input type="button" value='Update Index' onclick="window.location='dbtools.php?update=77'" ></td>
</tr>
<tr bgcolor='#ffffff'>
<td>Categories</td>
<td><?php if ( JB_update_table_index(33, false)) { echo "Update Needed"; } else {echo 'Up-to-date';}?></td>
<td><input type="button" value='Update Index' onclick="window.location='dbtools.php?update=33'" ></td>
</tr>
<tr bgcolor='#ffffff'>
<td>Categories (Translations)</td>
<td><?php if ( JB_update_table_index(34, false)) { echo "Update Needed"; } else {echo 'Up-to-date';}?></td>
<td><input type="button" value='Update Index' onclick="window.location='dbtools.php?update=34'" ></td>
</tr>
</table>
<input type="button" value='Update All' onclick="window.location='dbtools.php?update_all=1'" >
<?php

JB_admin_footer();

?>