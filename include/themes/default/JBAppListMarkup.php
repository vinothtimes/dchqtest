<?php

/*

Markup  for displaying list of applications

*/ 

class JBAppListMarkup extends JBListMarkup {

	var $colspan;

	function JBAppListMarkup($form_id=6) {

		parent::JBListMarkup($form_id);
	}
	
	/*

	Candidate's controls:

	<tr>
		<td>[Delete Button]</td>
	</tr>           ^
	  ^             |- list_delete_button 
	  |- candidate_list_controls()


	Employer's controls:

	Admin's controls:


	*/
	
	
	###############################
	# Application List: myjobs/apps.php & employers/apps
	###############################

	// print the Delete button so that applications can be deleted
	// 
	function candidate_list_controls() {
		
		global $label;
		
		$this->controls_open();
		JBPLUG_do_callback('app_candidate_list_controls', $this); // a plugin can be attached to add extra buttons
		$this->list_delete_button($label['c_app_delete_button'], $label['c_app_delete']);
		$this->controls_close();
	}

	function employer_list_controls() {

		global $label;

		$this->controls_open();
		JBPLUG_do_callback('app_employer_list_controls', $this); // a plugin can be attached to add extra buttons
		$this->list_delete_button($label['emp_app_del_button'], $label['emp_app_delete']);
		$this->controls_close();

	}

	function admin_list_controls() {

		global $label;

		$this->controls_open();
		JBPLUG_do_callback('app_admin_list_controls', $this); // a plugin can be attached to add extra buttons
		$this->list_delete_button('Delete', "Delete what's selected, are you sure?");
		$this->controls_close();

	}


	

	
	
	
	function list_head_admin_action($name='apps') {
		$this->list_head_cell_open(); 
		echo $this->get_select_all_checkbox($name); 
		$this->list_head_cell_close();	
		
	}
	
	function list_head_employer_action($name='apps') {
		$this->list_head_cell_open(); 
		echo $this->get_select_all_checkbox($name); 
		$this->list_head_cell_close();
		
	}
	
	function list_head_candidate_action($name='apps') {
		$this->list_head_cell_open(); 
		echo $this->get_select_all_checkbox($name); 
		$this->list_head_cell_close();
	}

	
	function list_data_admin_action() {
		
		?>
		<td rowspan="2">
		<?php
		echo '<p style="text-align:center">';
		echo $this->get_checkbox('apps', $this->get_data_value('app_id'));
		echo '</p>';
		$this->list_cell_close();

		
		
	}
	
	function list_data_employer_action() {
		?>
		<td rowspan="2">
		<?php
		echo '<p style="text-align:center">';
		echo $this->get_checkbox('apps', $this->get_data_value('app_id'));
		echo '</p>';
		$this->list_cell_close();
		
	}
	
	function list_data_candidate_action() {
		
		?>
		<td rowspan="2">
		<?php
		echo '<p style="text-align:center">';
		echo $this->get_checkbox('apps', $this->get_data_value('app_id'));
		echo '</p>';
		$this->list_cell_close();
		
	}

	
	function list_delete_button($button_label, $confirm_str, $name='delete') {
		if (!$button_label) return false;
		$this->control_button($button_label, $confirm_str, $name);
	}


	function list_head_column($heading) {
		// these methods are defined in the parent class
		$this->list_head_cell_open(); echo $heading; $this->list_head_cell_close();
	}

	function list_item_open($class='standard') {
		static $post_id;
		if (($this->list_mode=='EMPLOYER') && ($post_id != $this->get_data_value('post_id'))) {
			?>
			<tr class="<?php echo $class;?>">
				<td colspan="1">&nbsp;</td>
				<td colspan="4">&nbsp;</td>
			</tr>
			<?php
				
			$post_id = $this->get_data_value('post_id');
		}
		?>
		<tr class="<?php echo $class;?>">
		<?php

	}

	function cover_letter($label) {

		?>
		<td colspan="6" class="list_data_cell"><strong><?php echo $label;?></strong><span class="application_text"><?php echo JB_escape_html(JB_break_long_words ( $this->get_data_value('cover_letter'), false));?></span>
		</td>

		<?php

	}


	///////////////

	function value_blocked_id($id) {
		return '(#'.$id.')';
	}

	function value_blocked_msg($str) {

		return '<i>'.$str.'</i>';

	

	}

	function value_anon_id($id) {
		return '(#'.$id.')';
	}

	function value_anon_msg($str) {
		return '<i>'.$str.'</i>';

	}

	// print the applicant's name
	function print_formatted_app_name($formatted_name) {
		echo jb_escape_html($formatted_name);

	}




}

?>