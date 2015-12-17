<?php

/*

JBResumeListMarkup extends the JBListMarkup class which adds
additional markup to render the resume list / search result

example usage:

$LM = &JB_get_ResumeListMarkupObject();


$LM->list_start();

// control buttons for multiple selected items

$LM->controls_open();
$LM->control_button('Delete', 'This button does nothing!', 'delete');
$LM->controls_close();

// Heading cells
$LM->list_head_open();

$LM->list_head_cell_open();
echo "1st col";
$LM->list_head_cell_close();

$LM->list_head_cell_open();
echo "2nd col";
$LM->list_head_cell_close();

$LM->list_head_cell_open();
echo "3rd col";
$LM->list_head_cell_close();

$LM->list_head_close();

// Data cells

// item 1
$LM->list_item_open();
$LM->list_cell_open();
echo '1st val';
$LM->list_cell_close();
$LM->list_cell_open();
echo '2nd val';
$LM->list_cell_close();
$LM->list_cell_open();
echo '3rd val';
$LM->list_cell_close();
$LM->list_item_close();


// item 2

$LM->list_item_open();
$LM->list_cell_open();
echo 'abc';
$LM->list_cell_close();
$LM->list_cell_open();
echo 'def';
$LM->list_cell_close();
$LM->list_cell_open();
echo 'ghi';
$LM->list_cell_close();
$LM->list_item_close();



$LM->list_end();

Note: This file as it is most likely
to change in the future between newer versions. 

To change the look and feel, it is better
to start with template files such as index-header.php, index-main.php
index-footer.php



*/

class JBResumeListMarkup extends JBListMarkup {

	
	function JBResumeListMarkup($form_id=2) {

		parent::JBListMarkup($form_id);

		
	}

	function no_resumes() {
		global $label;
		echo '<p class="resume_list_no_result">'.$label['employer_resume_list_not_found'].".</p>";
	}



	###################################################
	# RESUME LIST
	# The following methods support the JB_echo_resume_list_data()
	# function in include/lists.inc.php
	# and JB_list_resumes() function in include/resumes.inc.php
	###################################################

	/*
	
	<tr>
		<td></td>
		<td></td>
		<td></td>
	
	</tr>

	<tr ..>
	 ^
	 |-Open the row [list_item_open() method]

			
		<td>12-12-1998<br>
		  ^  ^   ^
		  |  | 5 |days ago</td>
		  |  |   | ^
		  |  |   | |
		  |  |   | |-> days ago label, eg get_resume_label_today(), etc
		  |  |   |
		  |  |   |-> get_resume_data_line_break()
		  |  |   
		  |  |-> echo_resume_data()
		  |
		  |->Open cell, resume_open_cell()

		<td>Data 1</td>
		     ^
			 |-> echo_resume_data()


		<td><a href="..?resume_id=2">Data 2</a></td>
		     ^                               ^
			 |                               |-> get_resume_close_link()
			 |
			 |-> get_resume_open_link(), the link to open the resume to view it

		<td><b>Data 3</b></td>
		     ^         ^
			 |         |-> get_resume_close_bold()
			 |
			 |-> get_resume_open_bold()
		

	</tr>
	 ^
	 |-Close the row [list_item_close() method]


	*/

	function get_resume_data_line_break() { // A line break after displaying the date
		return '<br>';
	}

	function get_resume_label_today() {
		global $label;
		return '<span class="today">'.$label['employer_resume_list_today'].'</span>';
	}

	function get_resume_label_day_agao($days) {
		global $label;
		return '<span class="days_ago">'. $days." ".$label['employer_resume_list_day_ago'].'</span>';
	}

	function get_resume_label_days_ago($days) {
		global $label;
		return '<span class="days_ago">'. $days." ". $label['employer_resume_list_days_ago']."</span>";
	}

	function get_resume_label_more_days_ago($days) {
		global $label;
		return '<span class="days_ago2">'.$days." ". $label['employer_resume_list_days_ago']."</span>";
	}

	# The start of <A> tag to open a resume record
	//function get_resume_open_link(&$cur_offset, &$order_str, &$q_string) {
	function get_resume_open_link($url, $title='', $attributes='') {

		global $label;

		$val = '<a href="'.$url.'"'; 

		if ($title) {
			$val .= ' title="'.jb_escape_html($title).'" ';
		}
		if ($attributes) {
			$val .= ' '.$attributes.'" ';
		}

	
			
		/// IMAGE PREVIEW MOUSEOVER Code
		// Note: to have this feature working, you must have a template tag called 'IMAGE' defined in the resume form

		if (JB_PREVIEW_RESUME_IMAGE == 'YES') {

			$IMAGE = $this->get_template_value ('IMAGE', $this->admin);
			$RES_ANON = $this->get_template_value ('RES_ANON', $this->admin);
			$USER_ID = $this->get_template_value ('USER_ID', $this->admin);
			
			if (strlen($IMAGE)>0) {
				$val = $val . ' onmouseover="return overlib(\'<div style=&quot;text-align:center;&quot;>';
				if (JB_image_thumb_file_exists($IMAGE)) { 
					$val = $val . '<img alt=\\\'\\\'  src=\\\''.JB_get_image_thumb_src($IMAGE)."\\'>"; 
				} else {
					$val = $val . $IMAGE;
				} 
				$val = $val. ' </div>\');" onmouseout="return nd();" ';
			}
		}
		$val = $val.'>';

		return $val;

	}

	function get_resume_close_link() {
		return '</a>';
	}

	function resume_open_cell($template_tag, $class='list_data_cell') {
		$this->list_cell_open($template_tag, $class);
		

	}

	function resume_close_cell() {
		$this->list_cell_close();

	}

	function echo_resume_data(&$val, &$b1, &$b2) {
		// $b1 = opening bold tag
		// $b2 = closing bold tag
		echo $b1.$val.$b2;

	}

	function get_resume_open_bold() {
		return $this->get_open_bold();
	}

	function get_resume_close_bold() {
		return $this->get_close_bold();
	}


	function list_start() {

		?>

		<script type="text/javascript">

			function confirmLink(theLink, theConfirmMsg)
			   {
				   // Check if Confirmation is not needed
				   // or browser is Opera (crappy js implementation)
				   if (theConfirmMsg == '' || typeof(window.opera) != 'undefined') {
					   return true;
				   }

				   var is_confirmed = confirm(theConfirmMsg + '\n');
				   if (is_confirmed) {
					   theLink.href += '&is_js_confirmed=1';
				   }

				   return is_confirmed;
			   } // end of the 'confirmLink()' function
		</script>

		
		<table id="resumelist" align="center" border="0" cellSpacing="1" cellPadding="5" class="list">
		

		<?php

		


	}

	

	#######################################
	# Control buttons

	function admin_list_controls() {

		global $label;

		$this->controls_open(0);

		//$this->control_button($label['post_delete_button'], $label['post_delete_confirm'], 'delete');
		$this->control_button('Delete', 'Delete, are you sure?', 'delete');
		if ($this->show=='WA') { // waiting to be approved
			$this->control_button('Approve', 'Approve, are you sure?', 'approve');

		} else {
			$this->control_button('Disapprove', 'Disapprove, are you sure?', 'disapprove');
		}

		$this->controls_close();

	}

	function saved_list_controls() {

		global $label;

		$this->controls_open(0);
		$this->control_button($label['emp_saved_delete_button'], $label['emp_saved_delete_confirm'], 'delete');
		$this->controls_close();


	}

	function employer_list_controls() {
		global $label;

		$this->controls_open(0);
		$this->control_button($label['emp_save_button'], '', 'save');
		$this->controls_close();
	}

	##################################

	

	
	/**
	 * JBResumeListMarkup::list_head_admin_action()
	 * 
	 * Display column for the header part of the list
	 * To make room for the buttons which are outputted by 
	 * list_data_admin_action()
	 * 
	 * @return void
	 */
	function list_head_admin_action() {

		$this->colspan += 2;

		$this->list_head_cell_open();
		echo $this->get_select_all_checkbox('resumes');
		$this->list_head_cell_close();

		$this->list_head_cell_open();
		$this->list_head_cell_close();
		

	}

	function list_head_employer_action() {

		$this->colspan += 1;

		$this->list_head_cell_open();
		echo $this->get_select_all_checkbox('resumes');
		$this->list_head_cell_close();

		

	}

	function list_head_saved_action() {

		$this->colspan += 1;

		$this->list_head_cell_open();
		echo $this->get_select_all_checkbox('resumes');
		$this->list_head_cell_close();

		

	}

	//////////////////////////////

		/**
	 * JBResumeListMarkup::list_data_admin_action()
	 * 
	 * Display control buttons on the resume list for the Admin
	 * eg/ Delete, Approve, Edit buttons
	 * To remove / modify existing buttons, then modify this method
	 * To add new buttons, its better to create a plugin and hook on to 
	 * resume_list_data_admin_action
	 * 
	 * @param mixed $prams
	 * @return void
	 */
	function list_data_admin_action() {

		$this->list_cell_open(null, 'list_data_cell'.$class_postfix);
		echo '<p style="text-align:center">';
		echo $this->get_checkbox('resumes', $this->get_data_value('resume_id'));
		echo '</p>';
		$this->list_cell_close();
		?>
		
		<td><input style="font-size: 8pt" type="button" value="Delete" onClick="if (!confirmLink(this, 'Delete, are you sure?')) {return false;} window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=delete&amp;resume_id=<?php echo $this->get_data_value('resume_id'); ?>'"><br>
		<input type="button" style="font-size: 8pt" value="Edit" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=edit&amp;resume_id=<?php echo $this->get_data_value('resume_id'); ?>'">
		<?php if ($this->get_data_value('approved')=='N') { ?>
		<input type="button" style="font-size: 8pt; background-color: #A2E173;" value="Approve" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=approve&amp;resume_id=<?php echo $this->get_data_value('resume_id'); ?>'">
		<?php } ?>
		
		</td>
		<?php


	}

	function list_data_employer_action() {


		$this->list_cell_open(null, 'list_data_cell'.$class_postfix);
		echo '<p style="text-align:center">';
		echo $this->get_checkbox('resumes', $this->get_data_value('resume_id'));
		echo '</p>';
		$this->list_cell_close();


	}

	function list_data_saved_action() {


		$this->list_cell_open(null, 'list_data_cell'.$class_postfix);
		echo '<p style="text-align:center">';
		echo $this->get_checkbox('resumes', $this->get_data_value('resume_id'));
		echo '</p>';
		$this->list_cell_close();


	}


	/**
	 * JBResumeListMarkup::list_item_open()
	 * 
	 * Open item on the list.
	 * Eg. If list is a table, output the starting <tr>
	 * This method also changes the color of the background if the resume
	 * was suspended or mouse hovers over the item
	 * 
	 * 
	 * @param mixed $admin
	 * @return void
	 */
	function list_item_open($admin) {

		// overrides parent::list_item_open(0 to add special highliting for suspended resumes

		?>
		<tr bgcolor="<?php if (($this->admin) && ($this->get_data_value('status')=='SUS')) { echo "#FFFF99"; } else { echo JB_LIST_BG_COLOR;} ?>" onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '<?php echo JB_LIST_HOVER_COLOR; ?>', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);" >
		<?php

	}
	
	
	
	


}


?>