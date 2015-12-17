<?php
	
class JBPostListMarkup extends JBListMarkup {


	var $show;

	function JBPostListMarkup($form_id=1) {
		parent::JBListMarkup($form_id);
	}

	function set_show($show) {
		$this->show = $show; // show, eg ALL,  WA = Waiting, AP = approved, NA = Not approved, EX = expired
	}


	###################################################
	# JOB LIST
	# The following methods support the JB_echo_job_list_data()
	# function in include/lists.inc.php
	###################################################

	# Generate the post's summary cell value
	# This is unique for the post list
	function get_post_summary(&$JobListAttributes, &$class_postfix) {

		global $label;

	
		if  (($JobListAttributes->list_mode == 'ADMIN') || ($JobListAttributes->list_mode == 'EMPLOYER')) {

			$new_window = "onclick=\"window.open('post_window.php?post_id=".$this->get_data_value('post_id')."', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=800,height=500,left = 50,top = 50');return false;\"";

		}

		$post_url =  JB_job_post_url($this->get_data_value('post_id'), $JobListAttributes);;
		$profile_url = JB_emp_profile_url($this->get_data_value('user_id'), $JobListAttributes);


		ob_start(); // buffer start, 
		            // from now on all output is captured in to a buffer, and the
					// contents of the buffer is returned by this function

		$TITLE = $this->get_template_value ('TITLE', $this->admin);

		$TITLE = preg_replace(JB_CLEAN_PUN_REGEX, '$1 ', $TITLE); // automatically fix punctuation spacing so that it the line can break


		?>

		<span class="job_list_title<?php echo $class_postfix; ?>" >
		<a class="job_list_title<?php echo $class_postfix; ?>" href="<?php echo $post_url; ?>" <?php echo $new_window; ?>><?php echo $TITLE;?></a></span> 
		<?php if (!$JobListAttributes->is_internal_page()) { ?>
			<a href="<?php echo $post_url; ?>" target="new"><img src="<?php echo JB_THEME_URL;?>images/nw2.gif" width="11" height="11" border="0" alt=""></a>
		<?php
		}
		
		if (JB_POSTS_SHOW_POSTED_BY=='YES') {
			if (JB_POSTS_SHOW_POSTED_BY_BR=='YES') {
				echo "<br>";
			}
			$POSTED_BY = $this->get_template_value ('POSTED_BY', $this->admin);
		?>
		<span class="job_list_small_print<?php echo $class_postfix; ?>"> 
			<b><?php echo $label['post_list_posted_by'];?></b> <a href="<?php echo $profile_url; ?>"><i><?php if ($POSTED_BY=='') {$POSTED_BY=$label["posted_by_unknown"];} echo $POSTED_BY; ?></i></a>
		</span>
		<?php
			
		}
		if (JB_POSTS_SHOW_JOB_TYPE=='YES') {

			if (JB_POSTS_SHOW_JOB_TYPE_BR=='YES') {
				echo '<br>';
			}

			?><span class="job_list_small_print<?php echo $class_postfix; ?>">
				<b><?php echo $label['post_list_category']; ?></b>
				</span>
				<span class="job_list_cat_name<?php echo $class_postfix; ?>">
				<?php echo $this->get_template_value ('JOB_TYPE', $this->admin); ?>
			</span>
		<?php
			
		}
		if (JB_POSTS_SHOW_DESCRIPTION=='YES') {

			?>
			<br>
			<span class="job_list_small_print<?php echo $class_postfix; ?>">
			<?php 
			
			echo jb_escape_html(str_replace ('&nbsp;', ' ', JB_truncate_html_str (strip_tags($this->get_template_value ('DESCRIPTION', $this->admin)),  JB_POSTS_DESCRIPTION_CHARS, $trunc_str_len)));
				
			?> 		
			</span>
			<?php
		}

		if ($this->get_template_value ('REASON', $this->admin) !='') { 
			echo '<span style="font-weight:bold">'.$label['post_not_approved_cause'].'</span><span style="color:red; font-weight:bold;">'.$this->get_template_value ('REASON', $this->admin).'</span>';
		}

		// capture the buffer, and clean it
		$val = ob_get_contents();
		ob_end_clean();

		return $val; // return the buffered output

	}

	// Admin / Employer section: Display job post in a pop-up window (post_window.php)
	function get_new_window_js() {

		return "onclick=\"window.open('post_window.php?post_id=".htmlentities($this->get_data_value('post_id'))."', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=800,height=500,left = 50,top = 50');return false;\"";


	}

	

	

	function echo_post_data(&$val, &$b1, &$b2) {
		// $b1 = opening bold tag (if one exists)
		// $b2 = closing bold tag (if one exists)
		echo $b1.$val.$b2; 

	}

	#######################################################
	# Print how many posts were returned 
	# Used by the JB_list_jobs(..) function in posts.inc.php
	########################################################

	function no_posts_employer() { // employer's Post Manager section
		global $label;
		echo '<p>'.$label['0_posts_on_file'].'.&nbsp;</p>';
	}

	function no_posts() { // home page, search result, by employer, etc
		global $label;
		echo '<div class="post_list_no_result">'.$label['post_search_no_result'].'</div>';
	}

	function sponsored_heading($post_count) { // home page, above the premium job list
		global $label;
		echo '<div class="post_list_premium">'.$label['post_list_sponsored'].'</div>';
	}

	function post_count($post_count) { // home page, search result, by employer, etc
		global $label;
		// assuming that the variables in the label were already subsituted
		?><p class="job_listing_count"><?php echo $label['post_list_count']; ?></p>
		<?php
	}

	function post_count_category($post_count) { // by category

		global $label;

		?>
		<p class="job_listing_count"><?php echo $label['post_list_cat_count']; ?></p>

		<?php

	}

	#####################
	# Display post list
	# overrides parent::open_form()
	function open_form(&$JobListAttributes) {

		?>
		<form name="form1" method="POST" action="<?php echo htmlentities($_SERVER['PHP_SELF']). $JobListAttributes->get_form_query_string();?>">

		<?php

	}

	

	# Get the name of the css id to be used for the list
	# Typically, the css id is output in list_start(..)
	function get_list_css_id() { 

		static $count;
		$count++;

		switch ($this->list_mode) {

			case 'PREMIUM': // index.php
				$css_id ="joblist_premium";
				break;
			case 'EMPLOYER': // employer's manager.php
				$css_id ="joblist";
				break;
			case 'ADMIN': // admin job list
				$css_id ="joblist";
				break;
			case 'SAVED': // saved job list on candidate's save.php
				$css_id ="joblist";
				break;
			case 'BY_CATEGORY': // index.php
				$css_id ="joblist";
				break;
			default:
				$css_id ="joblist";
				break;


		}

		//if (($count > 1) && ($css_id =="joblist")) {
			//$css_id = $css_id.$count;
		//}

		return $css_id;

	
	}

	

	

	// Get the CSS class name according to the post mode
	// post mode can be normal, premium or free
	// Here in the default theme, the 
	// premium style alternates between the blue_grad
	// (blue gradiant backgroun) and green_grad 
	// (green gradient background).
	function get_item_class_name($POST_MODE) {
		
		static $class_name;

		if ($POST_MODE=='premium') {
			// alternate the background style for the premium list
			if ($class_name == "green_grad") {
			   $class_name = "blue_grad"; 
			} else {
			   $class_name = "green_grad";
			}
		} else {	
			$class_name = "standard";
		}
		return $class_name;

	}

	// Get the CSS postfix depending on the post mode
	function get_item_class_postfix($post_mode) {
		if ($post_mode=='premium') {
			$class_postfix = "_premium";
		}
		return $class_postfix;
	}

	

	

	// overrides parent::list_item_open()
	function list_item_open($post_mode, $bg_style, $class='') {

		// premium, free, normal

		?><tr <?php if ($post_mode == 'premium' ) { echo " class='$bg_style'  "; ?>  <?php } else { echo "bgcolor='".JB_LIST_BG_COLOR."'";   ?>  onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '<?php echo JB_LIST_HOVER_COLOR;?>', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);  "<?php  } ?> ><?php

	}

	

	function list_day_of_week($day_and_week, $class_postfix='') {

		?><tr class="list_day_of_week<?php echo $class_postfix;?>">
			<td class="list_day_of_week" colspan="<?php echo $this->colspan;?>"><?php echo $day_and_week;?></td></tr>
		<?php

	}

	##########################################

	# Saved List (Saved jobs by candidates)

	function saved_list_controls() {
		global $label;

		$this->controls_open(1);
		$this->control_button($label['employer_post_delete_button'], JB_js_out_prep($label['employer_post_delete_confirm']), 'delete');
		// tip for plugin authors: If you fo not want the above button to display, then
		// within your plugin,  set the $label['post_delete_button'] to be blank 
		JBPLUG_do_callback('job_list_saved_controls', $A = false); // plugin controls for admin
		$this->controls_close();

	}

	# Job Post Manager controls (jobs posted by Employer)

	function employer_list_controls() {
		global $label;


		$this->controls_open(0);
		$this->control_button($label['post_delete_button'], $label['post_delete_confirm'], 'delete');
		if ($this->show=='ONLINE') {
			$this->control_button($label['employer_post_expire_button'], $label['employer_post_expire_confirm'], 'expire');
		}
		// tip for plugin authors: If you fo not want the above button to display, then
		// your plugin can set the $label['post_delete_button'] to blank.
		JBPLUG_do_callback('job_list_employer_controls', $A = false); // plugin controls for admin
		$this->controls_close();

	}

	# Admin list

	function admin_list_controls() {

		static $display_count;

		$this->controls_open(2);
		 
		JBPLUG_do_callback('job_list_head_admin', $A = false); // plugin controls for admin
		?>
		With Selected: <input style="font-size: 10px;" type="submit" name="action" value="Approve"> | <input type="submit" name="action" value="Disapprove" style="font-size: 10px;" <?php if ($_SESSION['show']=='NA') { echo " disabled "; } ?> > for reason: <input type="text" name="reason<?php if ($display_count>0) echo $display_count+1;?>"  size="40">
		<?php if (($_SESSION['show']=='WA') || ($_SESSION['show']=='NA')) { echo ' | <input type="submit" name="action" value="Bulk Delete">'; } ?> <?php if ($_REQUEST['cat']!='') { ?> | <input type="submit" name="cat_change" style="font-size: 10px; " value="Move to category..." ><?php }?> | <input type="submit" name="plus_premium" style="font-size: 10px; " value="+ P" > <input type="submit" name="minus_premium" style="font-size: 10px; " value="- P" > | <input type="submit" name="bump_up" style="font-size: 10px; " value="Bump&uArr;" >
		<?php

		$this->controls_close();

		$display_count++;

	}

	function list_head_admin_action() {

		// This is a checkbox to select all the jobs on the page

		$this->list_head_cell_open();
		echo $this->get_select_all_checkbox('posts');
		$this->list_head_cell_close();

		// and this is the Action column
		$this->list_head_cell_open();
		?>
			Action 
		 <?php 
		
		 $this->list_head_cell_close();


	}

	function list_data_admin_action($class_postfix, $type) {

		global $label;

		$this->list_cell_open(null, 'list_data_cell'.$class_postfix);
		echo '<p style="text-align:center">';
		echo $this->get_checkbox('posts', $this->get_data_value('post_id'));
		echo '</p>';
		$this->list_cell_close();

		$this->column_info['list_data_admin_action']['no_wrap'] = 'Y';

		$this->list_cell_open('list_data_admin_action', 'list_data_cell'.$class_postfix);
		
		?>
		<br>
		<input type="button" name="action" class="post_edit_button" value="<?php echo $label['post_edit_button']; ?>" onClick="window.location='post.php?post_id=<?php echo $this->get_data_value('post_id');?>&amp;type=<?php echo $type;?>'">
		
		<br>
		<input type="button" name="action" class="post_delete_button" value="<?php echo $label['post_delete_button'];?>" onClick="if (!confirmLink(this, '<?php echo JB_js_out_prep($label['post_delete_confirm2']); ?>')) {return};window.location='posts.php?action=delete&amp;post_id=<?php echo $this->get_data_value('post_id');?>'">
		
		<?php

		$this->list_cell_close();

	}

	# Employer 'Job Post Manager' List

	function list_head_employer_action() {
		// a blank column header, for the buttons
		$this->list_head_cell_open();
		echo $this->get_select_all_checkbox('posts');
		$this->list_head_cell_close();

		$this->list_head_cell_open();
		$this->list_head_cell_close();

	}

	// $type = post type, eg premium
	function list_data_employer_action($class_postfix, $type) {

		global $label;

		$this->list_cell_open(null, 'list_data_cell'.$class_postfix);
		echo '<p style="text-align:center">';
		echo $this->get_checkbox('posts', $this->get_data_value('post_id'));
		echo '</p>';
		$this->list_cell_close();


		$this->list_cell_open(null, 'list_data_cell'.$class_postfix);

		?>
		
		<input class="post_edit_button" type="button" name="action" value="<?php echo $label['post_edit_button']; ?>" onClick="window.location='post.php?post_id=<?php echo $this->get_data_value('post_id');?>&amp;type=<?php echo $type; ?>'"><br>
		<input class="post_delete_button" type="button" name="action" value="<?php echo $label['post_delete_button']; ?>" onClick="if (!confirmLink(this, '<?php echo JB_js_out_prep($label['post_delete_confirm']); ?>')) {return};window.location='manager.php?action=delete&amp;post_id=<?php echo $this->get_data_value('post_id');?>'"><br>
		<?php

		

		if ($this->show=='OFFLINE') {


			?>
			<input class="post_repost_button" type="button" name="action" value="<?php echo $label['post_repost_button']; ?>" onClick="window.location='post.php?repost=1&amp;post_id=<?php echo $this->get_data_value('post_id');?>&amp;type=<?php echo $type; ?>'"><br>
			<?php

		}

		

		if (($this->show=='OFFLINE') && ($this->get_data_value('expired')=='Y')) {

			if ($this->get_data_value('post_date')=='premium') {
				$days = JB_P_POSTS_DISPLAY_DAYS;
			} else {
				$days = JB_POSTS_DISPLAY_DAYS;
			}
			JBPLUG_do_callback('post_manager_set_expire_days', $days, $this); // plugins could use $this->get_data_value('post_id') to get post_id, and other values

			$post_time = strtotime($this->get_data_value('post_date'));
			
			if ($post_time > (time() - 60*60*24*$days)) {
				?>

				<input class="post_repost_button" type="button" name="action" value="<?php echo $label['post_unexpire_button']; ?>" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?undo_expire=1&amp;post_id=<?php echo $this->get_data_value('post_id');?>&amp;type=<?php echo $type; ?>'">

				<?php

			}

		}
		$this->list_cell_close();
	}

	function list_data_employer_extras($app_count) {


		if ((!$this->column_list['HITS']) || ($this->column_info['HITS']['admin']=='Y')) { // if the list does not contain a 'views' column 
			$this->list_cell_open();
			echo $this->get_template_value('HITS'); 
			$this->list_cell_close();
			
		}
		if ((!$this->column_list['APPLICATIONS']) || ($this->column_info['APPLICATIONS']['admin']=='Y')) { // if the list does not contain a 'application count' column
			$this->list_cell_open();
			echo $app_count;
			$this->list_cell_close();
		}
		
	}

	# Link to the application list with the post_id
	function get_emp_app_link($href, $text) {
		return '<a href="apps.php?post_id='.$this->get_data_value('post_id').'">'.jb_escape_html($text).'</a>';
	}



	# Saved jobs List

	function list_head_saved_action() {
		// This is a checkbox to select all the jobs on the page

		$this->list_head_cell_open();
		echo $this->get_select_all_checkbox('posts');
		$this->list_head_cell_close();

	}


	function list_head_employer_extras() {
		
		global $label;
 
		if ((!$this->column_list['HITS']) || ($this->column_info['HITS']['admin']=='Y')) { // if the list does not contain a 'views' column 
			$this->list_head_cell_open();
			echo $label['post_list_field_label_views'];
			$this->list_head_cell_close();
		  
		}   
		if ((!$this->column_list['APPLICATIONS']) || ($this->column_info['APPLICATIONS']['admin']=='Y')) { // if the list does not contain an 'applications' column 
		
			$this->list_head_cell_open();
			echo $label['post_list_field_label_app'];
			$this->list_head_cell_close();
		}
	}

	function list_data_saved_action() {

		$this->list_cell_open();
		echo $this->get_checkbox('posts', $this->get_data_value('post_id'));
		$this->list_cell_close();

	}


	# Premium jobs List extra columns

	function list_head_premium_extras() {
		
		global $label;
		$this->list_head_cell_open();
		echo $label['post_list_field_label_views'];
		$this->list_head_cell_close();
	}

	function list_data_premium_extras() {

		$this->list_cell_open();
		echo $this->get_template_value('HITS'); 
		$this->list_cell_close();

	}

}

?>