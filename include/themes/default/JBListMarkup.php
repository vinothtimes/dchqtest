<?php

/*




This class contains all the HTML used to render a basic list

Some classes extend this class to provide custom list rendering
functionality.

Classes that override some of the methods of this class:

JBPostListMarkup.php
JBResumeListMarkup.php
JBAppListMarkup.php


*/
class JBListMarkup  extends JBMarkup {

	var $column_list; // a list of all the columns (template tags)
	var $column_info; // column information. See comments for list_head_cell_label()
	var $colspan; // number of columns on the list
	var $current_template_tag;
	var $list_mode; // some lists can have a special mode
	var $admin; // true/false, is the list vewied form admin?

	
	var $show;

	var $data_row; // for holding the raw data values of each cell of the current row
	
	var $form_id;

	

	function JBListMarkup($form_id='') {

		// HTML output function for include/lists.inc.php
		$this->form_id = $form_id;
	}

	function set_colspan($int) {
		$this->colspan = $int;
		
	}

	function set_column_list(&$array) {
		$this->column_list = $array;
		
	}

	function set_column_info(&$array) {
		$this->column_info = $array;
	}


	// Set the data values for the row of cells

	function set_values(&$row) {

		// If form_id is set, then it will set the data values
		// of the dynamic form object, otherwise they will be
		// stored locally 

		if (!sizeof($row)) return;

		if (is_numeric($this->form_id)) {
			// set the dynamic form
			// php5: JB_get_DynamicFormObject($this->form_id)->set_values($row);
			$obj = JB_get_DynamicFormObject($this->form_id);
			$obj->set_values($row);
			
		} else {
			// keep the data locally
			$this->data_row = &$row;
		}

	}

	function get_data_value($field_id) {
		if (is_numeric($this->form_id)) {
			// get form the dynamic form
			// php5: return JB_get_DynamicFormObject($this->form_id)->get_value($field_id);
			$obj = JB_get_DynamicFormObject($this->form_id);
			return $obj->get_value($field_id);
		} else {
			// get form a local place
			return $this->data_row[$field_id];
		}


	}
	
	function get_template_value($template_tag, $admin=false) {
		if (is_numeric($this->form_id)) {
			// get form the dynamic form
			// php5: JB_get_DynamicFormObject($this->form_id)->get_template_value($template_tag, $admin);
			$obj = JB_get_DynamicFormObject($this->form_id);
			return $obj->get_template_value($template_tag, $admin);
		}
	}

	function set_admin($bool) {
		$this->admin = $bool;
	}

	function set_list_mode($str) {
		$this->list_mode = $str;
	}

	function set_show($show) {
		$this->show = $show;
	}


	#####################################################

	function list_start($css_id='joblist', $class='list') {

		?> 
		<table id="<?php echo $css_id;?>" align="center" border="0" cellSpacing="1" cellPadding="5" class="<?php echo $class; ?>">
		<?php
	}

	function list_end() {
		?></table>
		<?php

	}

	###################################################
	# Form for the list
	###################################################

	function open_form($form_name='form1', $action=null) {
		if (!$action) {
			$action = $_SERVER['PHP_SELF'];
		} 
		?>
		<form name="<?php echo $form_name; ?>" method="POST" action="<?php echo htmlentities($action); ?>">
		<?php
	}

	function close_form() {
		?>
		</form>
		<?php

	}

	###################################################
	# List controls
	# - controls for items selected with checkboxes
	# This is a multi-span row with buttons such as
	# Approve / Delete, etc.
	###################################################
	/* 
	
	Diagram

    _________________________    |--------------------------------------------
	|   [Delete] [Approve] <-----| This is the row for the list controls     |
	|________________________    | In this example, the controls are the     |
    |[ ]| Date        | Name     | Delete and approve buttons. The controls  |
	----|-------------|-------   | span accross the top of the list.         |
	|[ ]| 2009-May-20 | Adam   ---------------------------------------------
	----|-------------|-------
	|[x]| 2009-May-20 | Peter
	----|-------------|-------
	|[ ]| 2009-May-19 | Mary
	----|-------------|-------

	*/

	function controls_open($cols=0) {

		?>
		<tr class="list_controls">
		<td colspan="<?php echo $this->colspan+$cols; ?>"  >
		<?php

	}

	function controls_close() {

		?>
		</td>
		</tr>
		<?php

	}

	function control_button($button_label, $confirm_str, $name='delete') {

		if (!$button_label) return false; // do not output the button if button_label is blank

		?>
		<input type="submit" class="control_button" name="<?php echo $name?>" value="<?php echo $button_label; ?>" onClick="if (!confirmLink(this, '<?php echo JB_js_out_prep($confirm_str); ?>')) {return false;} " >
		<?php

	}



	###################################################
	# COLUMN NAMES for all lists
	# The following methods support the JB_echo_list_head_data()
	# function in include/lists.inc.php
	###################################################

	# The cell which enloses the column names headings

	/*

	<tr>
	 ^
	 |- open

	<td><a href="..">Column name</a></td>
	  ^  ^                 ^       ^  ^
	  |  |-open link       |-Label |  |-close cell
      |-open cell                  |-close link

	</tr>
	  ^
	  |- close
	*/
	

	function list_head_open() {
		echo '<tr>';

	}

	function list_head_close() {
		echo '</tr>';
	}


	##################################################
	// list_head_action()
	// Renders the 'Action' heading cell.
	// Render the heading cell for the column that is used to display the
	// action buttons and checkbox for the item. eg. Edit/Delete button and
	// checkbox for selecting the item
	
	/* 
	
	Diagram

  _______________________________________________________________________
  |Action column ('A' denotes the action heading cell, which is rendered |
  |by the function below)                                                |
  |______________________________________________________________________|
      |
	  |
    __|_______________________
	| |  [Delete] [Approve] 
	|_v_______________________
    | A | Date        | Name  
	----|-------------|-------
	|[ ]| 2009-May-20 | Adam M
	----|-------------|-------
	|[x]| 2009-May-20 | Peter
	----|-------------|-------
	|[ ]| 2009-May-19 | Mary
	----|-------------|-------

	For selecting all check boxes, use this:  
	echo $this->get_select_all_checkbox('posts');

	Some veriations impelmented by the child classes:
	list_head_admin_action()
	list_head_employer_action()
	list_head_candidate_action()
	list_head_saved_action()

	*/

	function list_head_action($item_name='posts') {

		$this->list_head_cell_open();

		// render a chekbox, when the checkbox is clicked then it will
		// select all the checkboxes on the list
		echo $this->get_select_all_checkbox($item_name);

		$this->list_head_cell_close();

	}

	// The 'Action' Column data.
	function list_data_action($item_name='posts', $item_id=null) {

		$this->list_cell_open();
		echo '<p style="text-align:center">';
		echo $this->get_checkbox($item_name, $item_id);
		echo '</p>';
		$this->list_cell_close();
		
	}

	// open cell. This can be anything, including <td> or <div> or <span>, etc
	// By default it is <td>, and if the field is a post summary then we
	// give it maximum width
	// (ideally, this should be a <th> tag and not <td>)

	function list_head_cell_open($template_tag='', $class='list_header_cell') {

		?>
		<td class="<?php echo $class;?>" <?php if ($template_tag=='POST_SUMMARY') echo ' width="100%" '; ?>>
		<?php

	}
	
	# close whatever was opened in list_head_cell_open()
	// (ideally, this should be a <th> tag and not <td>)
	function list_head_cell_close() {
		?>
		</td>
		<?php

	}


	# open link
	# The <A> link on the column names, if they are sortable. If this link
	# is clicked then the list will be sorted in a different order
	
	function list_head_open_link($field_id, $ord='', $q_string='') {
		?>
		<a class="list_header_cell" rel="nofollow" href='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?order_by=<?php echo $field_id;?>&amp;ord=<?php echo $ord;  echo $q_string;?>'>
		<?php

	}
	# close the <a> tag for list_head_open_link()
	function list_head_close_link() {
		?></a>
		<?php

	}
	# Label (column name)
	# This is the label for the column
	function list_head_cell_label(&$info, &$template_tag, &$form_id) {

		// $info['trunc'] - Maimum length before truncation
		// $info['admin'] - is it Admin? Y or N or null/blank
		// $info['link'] = is it linked to the record? Y or N or blank
		// $info['is_bold'] = is it between <b></b> tags? Y or N or blank
		// $info['no_wrap'] = add a nowrap to the cell, Y or N
		// $info['clean'] = clean punctuation & spaces, break long words, Y or N


		echo JB_get_template_field_label ($template_tag, $form_id);

	}



	function list_item_open() {

		?><tr <?php echo 'bgcolor="'.JB_LIST_BG_COLOR.'"';   ?>  
		onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '<?php echo JB_LIST_HOVER_COLOR;?>', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);  " ><?php

	}

	function list_item_close() {
		?>
		</tr>
		<?php
	}

	function list_cell_open($template_tag='', $class='list_data_cell') {
		/*
		$template_tag can be used to access meta data about the current column being
		rendered. For more details, see comment inside $this->list_head_cell_label()

		*/

		if ($template_tag) $this->current_template_tag = $template_tag; // it can be useful for other functions to know what is the current template tag we are at

		?>
		<td class="<?php echo $class; ?>" <?php if ($this->column_info[$template_tag]['no_wrap']=='Y') { echo ' nowrap '; } ?>>
		<?php

	}

	function list_cell_close() {
		?>
		</td>
		<?php

	}


	




	#######################

	function get_img_html($file_name) {
		return '<img src="'.JB_get_image_thumb_src($file_name).'" border="0" alt="'.$file_name.'">';
	}

	
	###################
	#
	# Functions do not use this class are:
	# JB_echo_employer_list_data()
	# JB_echo_proile_list_data()
	# JB_echo_employer_list_data()
	#
	# This is because these functions are only called form the Admin
	# Implementing a template system for the Admin is out of scope
	#


	########################################
	# Nav pages (page, next and prev links)
	########################################

	function nav_pages_start() {

		?>
		<p class="nav_page_links">
		<?php

	}

	function nav_pages_end() {

		?>
		</p>
		<?php

	}

	function nav_pages_status() {
		global $label;
		echo "<span> ".$label["navigation_page"]."</span> ";
	}

	function get_nav_prev_link($link, $anchor) {
		return '<a class="nav_page_link"  href="'.$link.'">'.$anchor .'</a> ';


	}

	function get_nav_next_link($link, $anchor) {
		return  ' | <a class="nav_page_link"  href="'.$link.'"> '.$anchor.'</a>';


	}

	function get_nav_current_page($seperator, $cur_page) {

		return " $seperator <span class='nav_page_cur'>".$cur_page." </span>  ";

	}

	function get_nav_numeric_link($link, $anchor) {

		return " | <a class='nav_page_link'  href='".$link."'>".$anchor."</a>";

	}

	function get_nav_seperator() {
		return ' | ';
	}


	###########################

	# Generic list functions

	function get_open_bold() {
		return '<b>';
	}

	function get_close_bold() {
		return '</b>';
	}

	function get_open_link($url, $extra_attr) {

		return '<a '.$extra_attr.' href="'.$url.'">';
	}

	function get_close_link() {

		return '</a>';
	}

	

	// individual checkbox for each item on the list

	// 
	function get_checkbox($name, $val) {
		return '<input type="checkbox" name="'.$name.'[]" value="'.$val.'">';
	}

	
	// click this checkbox to select all the checkboxes on the list
	function get_select_all_checkbox($element_name='apps') {
		
		return '<input type="checkbox" onClick="checkBoxes(this, \''.$element_name.'[]\');">'; 
	}


	
}


####################################################################
# JBProductListMarkup is used in employers/subscriptions.php

class JBProductListMarkup extends JBListMarkup {

	var $first_option_checked;

	function JBProductListMarkup() {
		parent::JBListMarkup();
	}

	

	function list_heading($str) {
		$this->first_option_checked = false;
		echo '<h3>'.$str.'</h3>'; 
	}

	function list_sub_heading($str) {

		echo $str;
	}

	function list_head_cell_open() {
		$this->first_option_checked = false;
		?>
		<td class="order_col_head">
		<?php
	}

	function place_order_button($str) {

		?>
		<input class="form_submit_button" type="submit" value="<?php echo $str; ?>">
		<?php
	}

	function list_cell_open($type='nowrap') {

		if ($type=='nowrap') {
			$attr = ' nowrap ';
		} elseif ($type=='fullwidth') {
			$attr = 'width="100%"';
		} 

		?>
		<td class="order_col_data" <?php echo $attr; ?> >
		<?php
	}

	function product_label($product_id) {
		?>
		<label for="s<?php echo $product_id; ?>"> <?php echo $this->get_data_value('name'); ?> </label><br>
		<?php
	}


	function product_selection($prod_name, $product_id, $product_label ) {

		 // use $this->first_option_checked to ensure that the first selection is checked

		?>

		<input id="s<?php echo $product_id; ?>" <?php if (!$this->first_option_checked) { echo 'checked'; $this->first_option_checked=true;} ?> type="radio" name="<?php echo jb_escape_html($prod_name)?>" value="<?php echo $product_id; ?>" ><label for="s<?php echo $product_id ?>" > <?php echo jb_escape_html($product_label); ?> </label>
		
		<?php

	}

	function product_tick($str) {

		echo '<img src="'.JB_THEME_URL.'images/tick.gif" width="17" height="16" border="0" ALT="(tick)"> '.$str;
		
		$this->line_break();


	}

	function data_cell($field_id) {

		if ($field_id == 'price') {
			echo JB_convert_to_default_currency_formatted($this->get_data_value('currency_code'), $this->get_data_value('price'), true); 
		} else {
			echo $this->get_data_value($field_id);
		}

	}

}

class JBSubscriptionStatusMarkup extends JBProductListMarkup {

	/*

	The purpose of this class is so that the template designer can
	overriede some of the methods that render the markup for the
	subscription status, shown in employers/subscriptions.php

	*/

	function JBSubscriptionStatusMarkup() {

		parent::JBProductListMarkup();

	}

	// override list_cell_open() 
	function list_cell_open($type='nowrap') {
		
		if ($type=='rowspan:3') { // when subscription is active, subscription details
			$attr = 'rowspan="3" valign="top"';
		} elseif ($type=='colspan:4') {
			$attr = 'colspan="4"';
		}

		?>
		<td class="order_col_data" <?php echo $attr; ?> >
		<?php
	}

	function subscription_details() {

		global $label;
		?>

		<b><?php echo $label['subscription_details'];?></b><br>
    &quot;<?php echo jb_escape_html($this->get_data_value('item_name')); ?>&quot; (#S<?php echo $this->get_data_value('invoice_id'); ?>)<br>

	<?php

	}

	function subscription_status_open() {

		/* 
		In employers/subscriptions.php - displays subscription info, eg:
		For the period between 2009-Dec-12 and 2010-Jan-12 -
		You can do 100 resume views. (0 views)
		You can post 100 jobs (0 posted).

		(Where the above lines are generated by subscription_status_line()
		and the paragraph is closed by subscription_status_close())

		*/

		echo $label['subscription_is_now_active'];
		echo '<p>';

	}

	function subscription_status_line($str) {
		echo $str.$this->line_break();
	}

	function subscription_status_close() {
		echo '</p>';
	}

}


##########################################

/*

Render a list of recent orders for the user.
Used by employers/subscriptions.php
employers/credits.php


*/
class JBOrdersListMarkup extends JBListMarkup {

	

	function JBOrdersListMarkup() {
		parent::JBListMarkup();
	}

	

	function list_head_cell_open() {
		?>
		<td class="order_col_head">
		<?php
	}

	function list_cell_open($type='nowrap') {
		
		
		?>
		<td class="order_col_data_small" <?php echo $attr; ?> >
		<?php
	}

	function status_link($link, $anchor) {
		
		?><br><a href="<?php echo $link; ?>"><?php echo $anchor; ?></a>
		<?php
	}

	function data_cell($field_id) {

		if ($field_id == 'invoice_date') {
			echo JB_get_formatted_date($this->get_data_value('invoice_date'));
		} elseif ($field_id == 'invoice_id') {
			if ($this->get_data_value('subscription_id') ) {
				echo 'S';
			} elseif ($this->get_data_value('package_id')) {
				echo 'P';
			} elseif ($this->get_data_value('membership_id')) {
				echo 'M';
			}
			echo $this->get_data_value('invoice_id'); 
		} elseif ($field_id == 'amount') {
			echo JB_convert_to_default_currency_formatted($this->get_data_value('currency_code'), $this->get_data_value('amount'), true, $this->get_data_value('currency_rate')); 
		} elseif ($field == 'status') {
			echo JB_get_invoice_status_label($this->get_data_value('status'));
		} else { 
			echo $this->get_data_value($field_id);
		}

	}


}


#####################################################


class JBMembershipStatusMarkup extends JBProductListMarkup {

	/*

	The purpose of this class is so that the template designer can
	overriede some of the methods that render the markup for the
	membership status, shown in employers/membership.php
	myjobs/membership.php

	*/

	function JBMembershipStatusMarkup() {

		parent::JBProductListMarkup();

	}

	// override list_cell_open() 
	function list_cell_open($type='nowrap') {
		
		if ($type=='rowspan:3') { // when membership is active, membership details
			$attr = 'rowspan="3" valign="top"';
		} elseif ($type=='colspan:4') {
			$attr = 'colspan="4"';
		}

		?>
		<td class="order_col_data" <?php echo $attr; ?> >
		<?php
	}

	function membership_details() {

		global $label;
		?>

		<b><?php echo $label['emp_member_details']; ?></b><br>
    &quot;<?php echo jb_escape_html($this->get_data_value('item_name')); ?>&quot; (#M<?php echo $this->get_data_value('invoice_id'); ?>)<br>

	<?php

	}

	function membership_status_open() {

		/* 
		In employers/membership.php - displays membership info, eg:
		For the period between 2009-Dec-12 and 2010-Jan-12 -
		You can do 100 resume views. (0 views)
		You can post 100 jobs (0 posted).

		(Where the above lines are generated by membership_status_line()
		and the paragraph is closed by membership_status_close())

		*/

		echo $label['subscription_is_now_active'];
		echo '<p>';

	}

	function membership_status_line($str) {
		echo $str.$this->line_break();
	}

	function membership_status_close() {
		echo '</p>';
	}

}


#################################################


class JBRequestListMarkup extends JBListMarkup {


	function JBRequestListMarkup() {
		parent::JBListMarkup();
	}


	function list_head_cell_open() {
		?>
		<td class="request_history_head">
		<?php
	}

	function list_controls() {
		global $label;

		$this->controls_open();
		$this->control_button($label['c_request_delete_button'], $label['c_request_delete'], 'delete');
		// tip for plugin authors: If you fo not want the above button to display, then
		// within your plugin,  set the $label['post_delete_button'] to be blank 
		JBPLUG_do_callback('job_list_saved_controls', $A = false); // plugin controls for admin
		$this->controls_close();

	}

	function list_cell_open() {

		?>
		<td class="request_history_data">
		<?php

	}

	function data_cell($field_id) {

		switch ($field_id) {

			case 'employer_id':
				$emp_name = JB_get_employer_company_name($this->get_data_value('employer_id'));
				?>
				<a href="search.php?show_emp=<?php echo $this->get_data_value('employer_id');?>"><?php echo $this->escape($emp_name); ?></a>
				<?php
				break;
			case 'request_date':
				echo JB_trim_date(JB_get_local_time($this->get_data_value('request_date')));
				break;

		}

	}

	function requested_status() {
		global $label;
		?>
		<span class="request_label_requested"><?php echo $label['request_history_requested'];?></span>
		<?php

	}

	function granted_status() {

		global $label;
		echo '<span class="request_label_granted">'.$label['request_history_granted'].'</span>';

	}

	function refused_status() {
		global $label;
		echo '<span class="request_label_refused">'.$label['request_history_refused'].'</span>';

	}

	function grant_button() {

		global $label;

		?>

		 <input type="button" value="<?php echo $label['request_history_yes_grant']?>" onclick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?resume_id=<?php echo jb_escape_html($_REQUEST['resume_id']);?>&amp;action=grant&amp;employer_id=<?php echo jb_escape_html($this->get_data_value('employer_id')); ?>'">

		 <?php


	}

	function refuse_button($employer_id) {

		global $label;

		?>
		<input type="button" value="<?php echo $label['request_history_no_refuse'];?>" onclick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?resume_id=<?php echo jb_escape_html($_REQUEST['resume_id']);?>&amp;action=refuse&amp;employer_id=<?php echo $this->get_data_value('employer_id');?>'">

		<?php


	}

}
###############################################



class JBPaymentOptionListMarkup extends JBListMarkup {


	var $bgcolor; //  color for background (when not selected)
	var $selcolor; // stores the color for when the item is selected
	var $highlightcolor; // background color when the mouse hovers over an item

	var $form_css_id;

	var $colspan;

	var $invoice_row; 

	function JBPaymentOptionListMarkup() {

		parent::JBListMarkup();

		$this->bgcolor = JB_LIST_BG_COLOR;
		$this->selcolor = "#E1FFE1";
		$this->highlightcolor = JB_LIST_HOVER_COLOR;
		$this->colspan = 3; // number of columns


	}


	function set_invoice_row(&$row) {
		$this->invoice_row = &$row;
	}


	function list_start($css_id='joblist', $class='list') {

		?>
		<script type="text/javascript">
			 document.method_sel=false;
			function deselect_prevois_() { // return the row to original color after selecting it
				
				if (!document.method_sel) return;
				var str;
				str = "row_"+document.method_sel+".setAttribute('bgcolor', '<?php echo $this->bgcolor; ?>', 0);";
				
				eval(str);

			}
		</script>
		
		<table id="<?php echo jb_escape_html($css_id); ?>" align="center" border="0" cellSpacing="1" cellPadding="3" class="<?php echo jb_escape_html($class); ?>">
		<?php
	}


	function open_form($form_css_id='form1', $action=null) {

		if (!$action) {
			$action = $_SERVER['PHP_SELF'];
		} 

		$this->form_css_id = $form_css_id;

		?>

		<form id="<?php echo $form_css_id ?>" method="POST" action="<?php echo htmlentities($action); ?>">
		<input type="hidden" name="action" value="<?php echo jb_escape_html($_REQUEST['action']); ?>">
		<input type="hidden" name="invoice_id" value="<?php echo jb_escape_html($this->invoice_row['invoice_id']); ?>">
		<input type="hidden" name="membership_id" value="<?php echo jb_escape_html($this->invoice_row['membership_id']); ?>">
		<input type="hidden" name="employer_id" value="<?php echo jb_escape_html($this->invoice_row['employer_id']); ?>">
		<input type="hidden" name="user_id" value="<?php echo jb_escape_html($this->invoice_row['user_id']); ?>">
		<input type="hidden" name="confirm" value="<?php echo jb_escape_html($_REQUEST['confirm']); ?>">
		<input type="hidden" name="product_type" value="<?php echo jb_escape_html($this->invoice_row['product_type']); ?>">

		<?php

	}

	function list_item_open() {

		// $module_name is the class name of the payment object

		?>

		<tr id="row_<?php echo $this->data_row->className; ?>" style="cursor: pointer" onclick=" deselect_prevois_(); document.getElementById('<?php echo $this->form_css_id; ?>').pay_<?php echo $this->data_row->className; ?>.checked=true;this.setAttribute('bgcolor', '<?php echo $this->selcolor; ?>', 0); document.method_sel = '<?php echo $this->data_row->className; ?>';" bgcolor="<?php echo $this->bgcolor; ?>" onmouseover="old_bg=this.getAttribute('bgcolor');if (document.getElementById('<?php echo $this->form_css_id; ?>').pay_<?php echo $this->data_row->className; ?>.checked==false) this.setAttribute('bgcolor', '<?php echo $this->highlightcolor; ?>', 0);" onmouseout="if (document.getElementById('<?php echo $this->form_css_id; ?>').pay_<?php echo $this->data_row->className; ?>.checked==false) this.setAttribute('bgcolor', old_bg, 0);" >

	<?php

	}

	function radio_button() {

		if ($this->data_row->className==JB_DEFAULT_PAY_METH) {
			$sel = ' checked ';
		} else {
			$sel = '';
		}

		?>
		<input <?php echo $sel; ?> type="radio" id="pay_<?php echo $this->data_row->className; ?>" name="pay_method" value="<?php echo $this->data_row->className; ?>">

		<?php

	}

	function data_cell($field_id) {

		switch ($field_id) {

			case 'name':

				?>

				<p style="margin:5px;"><label style="cursor: pointer" for="pay_<?php echo $this->data_row->className; ?>"><?php echo $this->data_row->name; //echo $obj->payment_button($order_id, $this->invoice_row['product_type']); ?></label></p>

				<?php

				break;
			case 'description':

				?>

				<label style="cursor: pointer" for="pay_<?php echo $this->data_row->className; ?>"><?php echo $this->data_row->description; ?></label>
				
				<?php
				break;

		}

	}

	function selection_row_open() {

		?>
		<tr>
		<td colspan="<?php echo $this->colspan;?>" class="order_col_data">

		<?php


	}

	function select_button() {

		global $label;

		?>
		<div style="float:left">
		<input type="submit" class="form_submit_button" value="<?php echo $label['payment_man_butt_proc']; ?>" name="payment_sel">
		</div>
		<?php

	}

	function cancel_button($invoice_id, $product_type) {

		global $label;

		?>
		<div style="float:right;">
		<a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?payment_cancel=1&amp;product_type=<?php echo htmlentities($product_type);  ?>&amp;invoice_id=<?php echo htmlentities($invoice_id); ?>" ><?php echo $label['payment_man_butt_cancel']; ?></a>
		</div>
		<?php

	}

	function selection_row_close() {

		?>
		</td>
		</tr>
	<?php


	}


}

?>