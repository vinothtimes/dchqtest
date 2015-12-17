<?php

/*

A template class for rendering the dynamic search forms.

These classes print out the HTML used by the follwoing function

JB_display_dynamic_search_form()

Note: This file as it is most likely
to change in the future between newer versions. 

To change the look and feel, it is better
to start with template files such as index-header.php, index-main.php
index-footer.php


*/
class JBSearchFormMarkup  extends JBMarkup {

	var $form_id;
	var $cols;

	function JBSearchFormMarkup($form_id=1, $cols=2) {
		if (is_numeric($form_id)) {
			$this->form_id = $form_id;
		}
		if (is_numeric($cols)) {
			$this->cols = $cols;
		}
	}


	var $field_row; // an element from current $tag_to_search eg item that is being iterated through. $row['field_id'] and $row[field_label]

	function set_field_row(&$f) {
		$this->field_row = $f;
	}

	function form_open() {

		?>

	<form action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>" method="get" style="margin: 0">

		
		<input type="hidden" name="action" value="search">
		<input type="hidden" name="order_by" value="<?php echo jb_escape_html($_REQUEST['order_by']); ?>">
		<input type="hidden" name="ord" value="<?php echo jb_escape_html($_REQUEST['ord']); ?>">
		

		<?php

	}

	function form_close() {
		?></form>
		<?php
	}

	###########################
	# Container of the search form
 
	function container_open() {
		?><table id="search_form" class="search" style="margin: 0 auto; border:0px" cellpadding="5" cellspacing="0">
		<?php
	}

	function container_close() {
		?></table>
		<?php

	}

	function row_open() {
		?><tr><?php
	}

	function row_close() {
		?></tr><?php
	}

	function field_label_open($title='') {
		?><td title="<?php echo jb_escape_html($title);?>" class="field" valign="top">
		<?php

	}

	function field_label($f_label) {
		?><?php echo $f_label; ?>
		<?php
	}

	function field_label_close() {
		?></td>
		<?php

	}

	# Open the block which will contain the search field
	function field_open() {

		?><td class="field" valign="top">
		<?php

	}

	// close what was opened by field_open()
	function field_close() {

		?></td>
		<?php

	}

	# Check-boxes

	function single_checkbox_field(&$label, $field_id, $checked) { 
		# used for images and youtube, etc

		echo ' <input class="search_checkbox_style" id="cb'.$field_id.'" '.$checked.' type="checkbox" name="'.$field_id.'"  value="1" ><label class="search_input_sel_label" for="cb'.$field_id.'" > &gt; '.$label.'</label>  ';

	}

	function checkbox_field(&$description, $field_id, $checked, $code) {
		# Used if there are multiple checkboxes to list
		echo ' <input class="search_checkbox_style" id="cb'.$field_id.$code.'" type="checkbox" name="'.$field_id.'-'.$code.'" '.$checked.' value="'.$code.'" ><label class="search_input_sel_label" for="cb'.$field_id.$code.'" > &gt; '.$description.'</label>'.JB_SEARCH_CHECK_BOX_LINE_BREAK.'  ';

	}

		

	# Text field

	function text_field($field_id, $val) {
		echo '<input class="search_input_style" name="'.$field_id.'" type="text" value="'.JB_escape_html($val).'" size="30">';
	}

	###########################
	# Category Select

	function category_select_field_open($cat_mult, $cat_rows, $field_id, $cat_arr) {
		
		// $cat_mult = is multiple select? value will be 'multiple' or blank
		// $cat_rows = number of rows
		// $field_id = ...
		// $cat_arr = value will be '[]' or '' (blank). If '[]' then pass to
		// php as an array.

		?>
		<select <?php echo $cat_mult; if ($cat_mult != '') { echo ' size="'.$cat_rows.'" ';} ?>  class="search_input_style" name="<?php echo $field_id.$cat_arr; ?>">
		<?php

	}

	function category_first_option() { // first option, for single select
		global $label;
		?><option value=""><?php echo $label['sel_category_select']; ?></option><?php

	}

	# if it is a multiple-select category
	function category_first_option_all($selected) { // an option to select all (it really does not pass any value)
		global $label;
		?><option value="all" <?php echo $selected; ?>><?php echo $label['sel_category_select_all']; ?></option>
		<?php
	}

	
	// render an option for the search form.

	function category_select_option($val, $option, $selected, $allow='Y', $depth=null) {

		?><option value="<?php echo jb_escape_html(trim($val));?>" <?php echo jb_escape_html($selected);?> ><?php echo ($option); ?></option> 
		<?php

	}

	function get_category_option_space() {
		return '&nbsp;&nbsp;';
	}

	function get_category_option_branch() {
		return '|--&nbsp;';
	}

	function get_category_option_arrow() {
		return ' -&gt; ';
	}



	function category_select_field_close() {
		?></select><?php

	}

	###########################
	# Single Select

	function single_select_open($field_height, $field_id) {
		?><select class="search_input_style"  size="<?php echo $field_height; ?>" name="<?php echo $field_id; ?>"><?php

	}

	function single_select_first_option() { // the first option
		?><option value="">&nbsp;</option>
		<?php

	}

	function single_select_option($code, $description, $sel) {

		echo '<option value="'.$code.'" '.$sel.' >'.$description.'</option>';

	}

	function single_select_close() {
		?></select><?php

	}

	###########################
	# Multiple Select

	function mselect_open($field_id, $height) {
		?><select class='search_input_style' multiple size='<?php echo $height; ?>' name="<?php echo $field_id; ?>[]"><?php
	}

	function mselect_close() {
		?></select>
		<?php
	}

	function mselect_option($code, $description, $sel) {
		echo '<option value="'.jb_escape_html($code).'" '.$sel.' >'.$description.'</option>';
	}

	###########################
	# SCW Date calendar

	function scw_date_field($field_id) {

		?><input name="<?php echo $field_id; ?>" size="10" onclick= "scwShow(this,this);" onfocus= "scwShow(this,this);" type="text" value="<?php echo jb_escape_html($_REQUEST[$field_id]); ?>">
		<?php

	}

	###########################
	# Blank

	function blank_field() {
		echo "&nbsp;";
	}

	function blank_field_open() {
		$this->field_open();
	}

	function blank_field_close() {
		$this->field_close();
	}

	###########################
	# Radio Button

	function radio_button_field($field_id, $code, $description, $checked) {

		echo ' <span style="white-space:nowrap;"><input class="search_radio_style" id="'.$code.$field_id.'" type="radio" name="'.$field_id.'" '.$checked.' value="'.$code.'" ><label for="'.$code.$field_id.'" class="search_input_sel_label" >&gt;&nbsp;'.$description.'</label></span> ';

	}

	###########################
	# Skill Matrix

	function skill_matrix($field_id, $name_val) {
		
		global $label;

		echo '<span class="search_input_sel_label">'.$label["skill_matrix_label_1"].'</span><br><input class="search_input_style" value="'.jb_escape_html($name_val).'"  name="'.$field_id.'name" type="text" size="10"><br>';
		echo '<span class="search_input_sel_label">'.$label['skill_matrix_label_2'].'</span><br><select name="'.$field_id.'years" class="search_input_style">';
		
		?>
		<option value=""><?php echo $label['skill_matrix_col2_sel']; ?></option>
		<option value="0" <?php if ($_REQUEST[$field_id."years"]==="0") { echo " selected "; }?>><?php echo $label['skill_matrix_col2_sel0']; ?></option>
		<option value="1" <?php if ($_REQUEST[$field_id."years"]==="1") { echo " selected "; }?>><?php echo "&gt;=".$label['skill_matrix_col2_sel1']; ?></option>
		<option value="2" <?php if ($_REQUEST[$field_id."years"]==="2") { echo " selected "; }?>><?php echo "&gt;=".$label['skill_matrix_col2_sel2']; ?></option>
		<option value="3" <?php if ($_REQUEST[$field_id."years"]==="3") { echo " selected "; }?>><?php echo "&gt;=".$label['skill_matrix_col2_sel3']?></option>
		<option value="4" <?php if ($_REQUEST[$field_id."years"]==="4") { echo " selected "; }?>><?php echo "&gt;=".$label['skill_matrix_col2_sel4']; ?></option>
		<option value="5" <?php if ($_REQUEST[$field_id."years"]==="5") { echo " selected "; }?>><?php echo "&gt;=".$label['skill_matrix_col2_sel5']?></option>
		<option value="6" <?php if ($_REQUEST[$field_id."years"]==="6") { echo " selected "; }?>><?php echo "&gt;=".$label['skill_matrix_col2_sel6']?></option>
		<option value="7" <?php if ($_REQUEST[$field_id."years"]==="7") { echo " selected "; }?>><?php echo "&gt;=".$label['skill_matrix_col2_sel7']?></option>
		<option value="8" <?php if ($_REQUEST[$field_id."years"]==="8") { echo " selected "; }?>><?php echo "&gt;=".$label['skill_matrix_col2_sel8']?></option>
		<option value="9" <?php if ($_REQUEST[$field_id."years"]==="9") { echo " selected "; }?>><?php echo "&gt;=".$label['skill_matrix_col2_sel9']?></option>
		<option value="10" <?php if ($_REQUEST[$field_id."years"]==="10") { echo " selected "; }?>><?php echo $label['skill_matrix_col2_sel10']?></option>

		<?php
		echo '</select><br>';
		echo '<span class="search_input_sel_label">'.$label['skill_matrix_label_3'].'</span><br>
		<select name="'.$field_id.'rating" class="search_input_style">';
		?>

		<option value=""><?php echo $label['skill_matrix_col3_sel']; ?></option>
		<option value="10" <?php if ($_REQUEST[$field_id."rating"]==="10") { echo " selected "; }?>><?php echo $label['skill_matrix_col3_sel10']?></option>
		<option value="9" <?php if ($_REQUEST[$field_id."rating"]==="9") { echo " selected "; }?>><?php echo "&gt;= ".$label['skill_matrix_col3_sel9']?></option>
		<option value="8" <?php if ($_REQUEST[$field_id."rating"]==="8") { echo " selected "; }?>><?php echo "&gt;= ".$label['skill_matrix_col3_sel8']?></option>
		<option value="7" <?php if ($_REQUEST[$field_id."rating"]==="7") { echo " selected "; }?>><?php echo "&gt;= ".$label['skill_matrix_col3_sel7']?></option>
		<option value="6" <?php if ($_REQUEST[$field_id."rating"]==="6") { echo " selected "; }?>><?php echo "&gt;= ".$label['skill_matrix_col3_sel6']?></option>
		<option value="5" <?php if ($_REQUEST[$field_id."rating"]==="5") { echo " selected "; }?>><?php echo "&gt;= ".$label['skill_matrix_col3_sel5']?></option>
		<option value="4" <?php if ($_REQUEST[$field_id."rating"]==="4") { echo " selected "; }?>><?php echo "&gt;= ".$label['skill_matrix_col3_sel4']?></option>
		<option value="3" <?php if ($_REQUEST[$field_id."rating"]==="3") { echo " selected "; }?>><?php echo "&gt;= ".$label['skill_matrix_col3_sel3']?></option>
		<option value="2" <?php if ($_REQUEST[$field_id."rating"]==="2") { echo " selected "; }?>><?php echo "&gt;= ".$label['skill_matrix_col3_sel2']?></option>
		<option value="1" <?php if ($_REQUEST[$field_id."rating"]==="1") { echo " selected "; }?>><?php echo "&gt;= ".$label['skill_matrix_col3_sel1']?></option>

		<?php
		echo '</select>';

	}

	###########################
	# The 'Find' button

	function form_button() {

		global $label;

		?>

		<tr>
			<td class="field" colspan="4">
				<div style="float: left; margin:0px; padding:0px;">
				<input class="form_submit_button" type="submit" value="<?php echo $label['find_button'];?>" name="search" style="float: left">
				</div>
				<?php if ($_REQUEST['action']=='search') { ?> 
				
				<div style="float: right"><span class="new_search_link"><a class="new_search_link" href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=search"><?php echo $label['search_start_new'];?></a></span>
				</div>
				<?php }
				?>
			</td>
		</tr>

		<?php

	}


}

?>