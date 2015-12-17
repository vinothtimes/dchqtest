<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

function JB_display_matrix ($field_id, &$data, $mode) {
	$field_id = (int) $field_id;
	global $label;
	$sql = "Select * from skill_matrix WHERE field_id='".jb_escape_sql($field_id)."' "; 
	
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$rows = $row['row_count'];

	?>

	<input type="hidden" name="<?php echo $field_id; ?>" value="1" >

	<table  class="skill_matrix_table" id="table1"  cellspacing="1" cellpadding="5">
		<tr class="skill_matrix_header">
			<td><?php echo $label['skill_matrix_label_1']; ?></td>
			<td><?php echo $label['skill_matrix_label_2']; ?></td>
			<td><?php echo $label['skill_matrix_label_3']; ?></td>
		</tr>
		<?php
		for ($i=0; $i < $rows; $i++) {
		?>
		<tr class="skill_matrix_row">
			<td>
			<?php if ($mode!='view') { ?>
				<input type="text" name="<?php echo $field_id;?>name<?php echo $i;?>" size="25" value="<?php echo JB_escape_html($data[$field_id."name".$i]); ?>" >
			<?php } else {
				echo JB_escape_html($data[$field_id."name".$i]);
			}?>
			</td>
			<td>
			<?php if ($mode!='view') { ?>
				<select size="1" name="<?php echo $field_id;?>years<?php echo $i;?>">
				<option value=""><?php echo $label['skill_matrix_col2_sel']; ?></option>
				<option value="0" <?php if ($data[$field_id."years".$i]==="0") { echo " selected "; }?>><?php echo $label['skill_matrix_col2_sel0']; ?></option>
				<option value="1" <?php if ($data[$field_id."years".$i]==="1") { echo " selected "; }?>><?php echo $label['skill_matrix_col2_sel1']; ?></option>
				<option value="2" <?php if ($data[$field_id."years".$i]==="2") { echo " selected "; }?>><?php echo $label['skill_matrix_col2_sel2']; ?></option>
				<option value="3" <?php if ($data[$field_id."years".$i]==="3") { echo " selected "; }?>><?php echo $label['skill_matrix_col2_sel3']?></option>
				<option value="4" <?php if ($data[$field_id."years".$i]==="4") { echo " selected "; }?>><?php echo $label['skill_matrix_col2_sel4']; ?></option>
				<option value="5" <?php if ($data[$field_id."years".$i]==="5") { echo " selected "; }?>><?php echo $label['skill_matrix_col2_sel5']?></option>
				<option value="6" <?php if ($data[$field_id."years".$i]==="6") { echo " selected "; }?>><?php echo $label['skill_matrix_col2_sel6']?></option>
				<option value="7" <?php if ($data[$field_id."years".$i]==="7") { echo " selected "; }?>><?php echo $label['skill_matrix_col2_sel7']?></option>
				<option value="8" <?php if ($data[$field_id."years".$i]==="8") { echo " selected "; }?>><?php echo $label['skill_matrix_col2_sel8']?></option>
				<option value="9" <?php if ($data[$field_id."years".$i]==="9") { echo " selected "; }?>><?php echo $label['skill_matrix_col2_sel9']?></option>
				<option value="10" <?php if ($data[$field_id."years".$i]==="10") { echo " selected "; }?>><?php echo $label['skill_matrix_col2_sel10']?></option>
				</select>
			<?php
				
			} else {
				$temp = $data[$field_id."years".$i];

				if ($temp!='') {
					echo $label['skill_matrix_col2_sel'.$temp];
				} else {
					echo "&nbsp;";

				}

			}
			
			?>
				</td>

			<td>
			<?php if ($mode!='view') { ?>
				<select size="1" name="<?php echo $field_id;?>rating<?php echo $i;?>">
				<option value=""><?php echo $label['skill_matrix_col3_sel']; ?></option>
				<option value="10" <?php if ($data[$field_id."rating".$i]==="10") { echo " selected "; }?>><?php echo $label['skill_matrix_col3_sel10']?></option>
				<option value="9" <?php if ($data[$field_id."rating".$i]==="9") { echo " selected "; }?>><?php echo $label['skill_matrix_col3_sel9']?></option>
				<option value="8" <?php if ($data[$field_id."rating".$i]==="8") { echo " selected "; }?>><?php echo $label['skill_matrix_col3_sel8']?></option>
				<option value="7" <?php if ($data[$field_id."rating".$i]==="7") { echo " selected "; }?>><?php echo $label['skill_matrix_col3_sel7']?></option>
				<option value="6" <?php if ($data[$field_id."rating".$i]==="6") { echo " selected "; }?>><?php echo $label['skill_matrix_col3_sel6']?></option>
				<option value="5" <?php if ($data[$field_id."rating".$i]==="5") { echo " selected "; }?>><?php echo $label['skill_matrix_col3_sel5']?></option>
				<option value="4" <?php if ($data[$field_id."rating".$i]==="4") { echo " selected "; }?>><?php echo $label['skill_matrix_col3_sel4']?></option>
				<option value="3" <?php if ($data[$field_id."rating".$i]==="3") { echo " selected "; }?>><?php echo $label['skill_matrix_col3_sel3']?></option>
				<option value="2" <?php if ($data[$field_id."rating".$i]==="2") { echo " selected "; }?>><?php echo $label['skill_matrix_col3_sel2']?></option>
				<option value="1" <?php if ($data[$field_id."rating".$i]==="1") { echo " selected "; }?>><?php echo $label['skill_matrix_col3_sel1']?></option>
				</select>
			<?php 
			} else {

				$temp = $data[$field_id."rating".$i];

				if ($temp!='') {

					echo $label['skill_matrix_col3_sel'.$temp];
				} else {
					echo "&nbsp;";

				}
				

			}
			?>
				</td>
		</tr>
		
		<?php
			}

		?>
		
	</table>

				<?php

}

####################################

function JB_load_skill_matrix_data($field_id, $object_id, &$data) {

	$sql = "SELECT * FROM skill_matrix_data WHERE object_id='".jb_escape_sql($object_id)."' AND field_id='".jb_escape_sql($field_id)."' ";
	$result = JB_mysql_query ($sql) or die (mysql_error());
	//echo $sql;
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		$data[$field_id."name".$row['row']] = $row['name'] ; 
		$data[$field_id."years".$row['row']] = $row['years'];
		$data[$field_id."rating".$row['row']] = $row['rating'];

	}



}

####################################

function JB_save_skill_matrix_data($field_id, $object_id, $user_id) {

	if ($object_id=='') {
		return false;

	}

	$row_count = JB_get_matrix_row_count($field_id);

	$sql = "DELETE FROM skill_matrix_data WHERE field_id='".jb_escape_sql($field_id)."' AND `object_id`='".jb_escape_sql($object_id)."' AND user_id='".jb_escape_sql($user_id)."' ";
	
	JB_mysql_query ($sql) or die ($sql.mysql_error());


	for ($i = 0; $i < $row_count; $i++ ) {

		// field id=110, row=0, user_id 

		/* primary key is:
		field_id
		row
		user_id
		*/

		$sql = "REPLACE INTO skill_matrix_data (field_id, row, name, years, rating, object_id, user_id) values($field_id, $i, '".JB_clean_str($_REQUEST[$field_id."name".$i])."', '".jb_escape_sql($_REQUEST[$field_id."years".$i])."', '".jb_escape_sql($_REQUEST[$field_id."rating".$i])."', '".jb_escape_sql($object_id)."', '".jb_escape_sql($user_id)."') ";
	
		if (trim($_REQUEST[$field_id."name".$i])!='') {
			JB_mysql_query ($sql) or die ("[skill matrix]".$sql.mysql_error());
		}

	}

}

#########################################

function JB_get_matrix_row_count($field_id) {

	$sql = "Select * from skill_matrix WHERE field_id='".jb_escape_sql($field_id)."' "; 
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	return $row['row_count'];


}

?>