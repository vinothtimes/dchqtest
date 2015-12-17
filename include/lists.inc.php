<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require (dirname(__FILE__).'/classes/JobListAttributes.php');


# GLOBALS #
$column_list; // column structure, global variable to cache the column info
$column_info;

//define ('JB_CLEAN_PUN_REGEX', '/ *(,|\.|\?|!|\+|\/|\\\) */i'); // for cleaning punctuation
define ('JB_CLEAN_PUN_REGEX', '/(,|\.{1,3}|\?{1,3}|!{1,3}|\+{1,3}|\\\)[,\.\?!\+\/\\\]*/i'); // for cleaning punctuation, put spaces after , . ? ! +  \ and limit . ? ! + to three repetitions max. Can't have / because it is used to close tags

/*
JB_echo_list_head_data()

Display column names for the list, and initalize $column_list structure using
the meta-data for the coulumns found in the form_lists table.
All columns displayed by this function are dynamic and not hard-coded, they
can be edited from the Admin panel.

Initializes $column_list, $column_info globals which
are then used to display the data values later.



Arguments:

$form_id (int) - The record types it lists. eg 1 = posting form, 2 = resume form, etc
$admin (boolean) - Admin mode or not

Returns:

$colspan - The number of dynamic columns to be on the list


eg. output (simplified)

<td class="list_header_cell">Column 1</td>
<td class="list_header_cell">Column 2</td>
<td class="list_header_cell">Column 3</td>

Job List:
To modify the output produced by this function, please
see the JBPostListMarkup class in 
include/themes/default/JBPostListMarkup.php
- You can customize the output by copying JBResumeListMarkup.php
to your custom theme directory


Resume List:
To modify the output produced by this function, please
see the JBResumeListMarkup class in 
include/themes/default/JBResumeListMarkup.php
- You can customize the output by copying JBResumeListMarkup.php
to your custom theme directory

*/


function JB_echo_list_head_data($form_id, $admin) { 

	global $q_string, $column_list, $column_info;

	# HTML output for this function comes from ListMarkup Class
	# include/themes/default/JBListMarkup.php
	# Any HTML customizations should be done there.
	# Please copy this class in to your custom theme directory, and
	# customize form there

	

	$LM = &JB_get_ListMarkupObject($form_id); // load the ListMarkup Class
	
	$LM->set_admin($admin);
	
	if ($form_id==1) {
		global $JobListAttributes;
		$q_string = $JobListAttributes->get_query_string('&amp;');
	}

	$ord = strtolower($_REQUEST['ord']);
	if ($ord=='asc') {
		$ord = 'desc';
	}elseif ($ord=='desc') {
		$ord = 'asc';
	} else {
		$ord = 'asc';
	}


	$colspan = 0;

	if (!$cached_list = jb_cache_get('column_info_'.$form_id)) {

		$sql = "SELECT `template_tag`, `truncate_length`, `admin`, `linked`, `is_bold`, `no_wrap`, `clean_format`, `is_sortable`, `admin`, `field_type` FROM form_lists WHERE form_id='".jb_escape_sql($form_id)."' ORDER BY sort_order ASC ";
		$result = JB_mysql_query($sql);
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$cached_list[]=$row;
		}
		jb_cache_set('column_info_'.$form_id, $cached_list);
	}
	
	foreach ($cached_list as $row) {
		$colspan++;

		$column_list[$row['template_tag']]=$row['template_tag'];

		$column_info[$row['template_tag']]['field_type'] = $row['field_type'];
		$column_info[$row['template_tag']]['trunc'] = $row['truncate_length'];
		$column_info[$row['template_tag']]['admin'] = $row['admin'];
		$column_info[$row['template_tag']]['link'] = $row['linked'];
		$column_info[$row['template_tag']]['is_bold'] = $row['is_bold'];
		$column_info[$row['template_tag']]['no_wrap'] = $row['no_wrap'];
		$column_info[$row['template_tag']]['clean'] = $row['clean_format'];
		$column_info[$row['template_tag']]['is_sortable'] = $row['is_sortable'];
		if (($row['admin']=='Y') && (!$admin)) {
			continue; // do not render this column if not viewed by Admin.
		}
		$LM->list_head_cell_open($row['template_tag']);
		
		if ($row['is_sortable']=='Y') { // show column order by link?

			$field_id = JB_get_template_field_id ($row['template_tag'], $form_id);

			if ($form_id == 1) { // posts
			// post summary is not sortable..
				if ($field_id=='summary') {
					$field_id = JB_get_template_field_id ('TITLE', 1); // order by title instead!
				}
			}

			if ($form_id == 4) { // employers
			// post count is not sortable.
				if ($field_id=='posts') {
					$row['is_sortable'] = 'N';
					
				}
				// sort name by last name
				if ($field_id=='Name') {
					$field_id = JB_get_template_field_id ('LNAME', 4); // order by title instead!
				}

				if ($field_id=='has_profile') {
					$row['is_sortable'] = 'N';
				}

			}

			if ($form_id == 5) { // candidates
			// resume id is not sortable.
				if ($field_id=='resume_id') {
					$row['is_sortable'] = 'N';
				}
				// sort name by last name
				if ($field_id=='Name') {
					$field_id = JB_get_template_field_id ('LNAME', 5); // order by title instead!
				}

			}

			$LM->list_head_open_link($field_id, $ord, $q_string); // output the start of the link
		}
		$LM->list_head_cell_label($column_info[$row['template_tag']], $row['template_tag'], $form_id);
		
		if ($row['is_sortable']=='Y') { // show column order by link?
			$LM->list_head_close_link();
		}
		$LM->list_head_cell_close();
	}

	$LM->set_column_list($column_list);
	$LM->set_column_info($column_info);

	return $colspan;


}

###################################################

function JB_echo_resume_list_data($admin) {

	global $label, $cur_offset;

	# HTML output for this function comes from ListMarkup Class
	# include/themes/default/JBListMarkup.php
	# Any HTML customizations should be done there.
	# Please copy this class in to your custom theme directory, and
	# customize form there


	
	$LM = &JB_get_ListMarkupObject(2);// load the ListMarkup Class

	$Form = &JB_get_DynamicFormObject(2);
	$ttf = &$Form->get_tag_to_field_id();

	if ($_REQUEST['order_by']!='') {

		$ord = jb_alpha_numeric($_REQUEST['ord']);
		if ($ord=='asc') {
			$ord = 'desc';
		}elseif ($ord=='desc') {
			$ord = 'asc';
		} else {
			$ord = 'asc';
		}

		$order_str = "&amp;order_by=".JB_escape_html($_REQUEST['order_by'])."&amp;ord=".$ord;
	
	}

	$q_string = JB_generate_q_string(2);
	$cur_offset = jb_alpha_numeric($_REQUEST['offset']);

	

	foreach ($LM->column_list as $template_tag) {

		if (($LM->column_info[$template_tag]['admin']=='Y') && (!$admin)) {
			continue; // do not render this column in admin
		}

	
		$val = $Form->get_value($ttf[$template_tag]['field_id']);


		// process the value depending on what kind of template tag it was given.
		switch ($template_tag) {
			case 'DATE':
				
				$val = JB_get_local_time($val);
	
				$init_date = strtotime (jb_trim_date ($val));
				$dst_date =  strtotime (jb_trim_date (JB_get_local_time(gmdate("Y-m-d H:i:s"))));

				$val = JB_get_formatted_date($val);

				if (!$init_date) {
				   $days = "x";
				} else {
					$diff = $dst_date-$init_date;  
					$days = floor($diff/86400); 
				}
				
				$val .= $LM->get_resume_data_line_break();

				if ($days==0) {
					$val = $val. $LM->get_resume_label_today();
				} elseif (($days > 0) && ($days < 2)) { 
					$val = $val . $LM->get_resume_label_day_agao($days); 
				} elseif (($days > 1) && ($days < 8)) { 
					$val = $val . $LM->get_resume_label_days_ago($days); 
				} elseif (($days >= 8)) { 
					$val = $val . $LM->get_resume_label_more_days_ago($days);
				}
				break;
			default:
		
				if ($LM->column_info[$template_tag]['trunc']>0) {			
					$val = JB_truncate_html_str($val, $LM->column_info[$template_tag]['trunc'], $trunc_str_len);
				}

				

				$val = JB_get_list_template_value($ttf[$template_tag], $val, $admin, 2);

				if ($LM->column_info[$template_tag]['clean']=='Y') { // fix up punctuation spacing
					$val = preg_replace(JB_CLEAN_PUN_REGEX, '$1 ', $val);
				}


		}


		if ($LM->column_info[$template_tag]['is_bold']=='Y') {
			$b1=$LM->get_resume_open_bold(); $b2=$LM->get_resume_close_bold();
		} else {
			$b1='';$b2='';
		}

		
		if ($LM->column_info[$template_tag]['link']=='Y')  { // Render as a Link to the record?

			$url = htmlentities($_SERVER['PHP_SELF']).'?resume_id='.
				$Form->get_value('resume_id').
				'&amp;offset='.
				$cur_offset.
				$order_str.
				$q_string;

			 $val = $LM->get_resume_open_link($url).
						$val.
				   $LM->get_resume_close_link();
				   
		}

		JBPLUG_do_callback('resume_list_column_data_filter', $val, $template_tag);


		$LM->resume_open_cell($template_tag); // eg <td nowarp>
		$LM->echo_resume_data($val, $b1, $b2);
		$LM->resume_close_cell(); // eg </td>


	}


}


###################################################

function JB_echo_job_list_data($admin) {

	global $label;

	
	# HTML output for this function comes from ListMarkup Class
	# include/themes/default/JBListMarkup.php
	# Any HTML customizations should be done there.
	# Please copy this class in to your custom theme directory, and
	# customize form there


	
	$LM = &JB_get_ListMarkupObject(1);// load the ListMarkup Class

	$Form = &JB_get_DynamicFormObject(1);
	$ttf = &$Form->get_tag_to_field_id();

	global $JobListAttributes;

	$post_id = $Form->get_template_value ('POST_ID', $admin);
	$post_mode = $Form->get_template_value ('POST_MODE', $admin);
	$class_postfix = $LM->get_item_class_postfix($post_mode);

	foreach ($LM->column_list as $template_tag) {

		if (($LM->column_info[$template_tag]['admin']=='Y') && (!$admin)) {
			continue; // do not render this column
		}


		$val = $Form->get_value($ttf[$template_tag]['field_id']);

		if ($template_tag=='POST_SUMMARY') {

			if (($JobListAttributes->list_mode == 'ADMIN') || ($JobListAttributes->list_mode=='EMPLOYER')) {
				$new_window = $LM->get_new_window_js();
			}
			$val = $LM->get_post_summary($JobListAttributes, $class_postfix);
		} elseif ($template_tag=='DATE') {
			$val = JB_get_formatted_date(JB_get_local_time($val));

		} else {

			if ($LM->column_info[$template_tag]['trunc']>0) {
				$val = JB_truncate_html_str($val, $LM->column_info[$template_tag]['trunc'], $trunc_str_len);
			}

			

			$val = JB_get_list_template_value($ttf[$template_tag], $val, $admin, 1);

			if ($LM->column_info[$template_tag]['clean']=='Y') { // fix up punctuation spacing
				$val = preg_replace(JB_CLEAN_PUN_REGEX, '$1 ', $val);
			}


		}


		if ($LM->column_info[$template_tag]['is_bold']=='Y') {
			$b1=$LM->get_open_bold(); 
			$b2=$LM->get_close_bold();
		} else {
			$b1='';$b2='';
		}

		if ($LM->column_info[$template_tag]['link']=='Y')  { // Render as a Link to the record?

			if (($JobListAttributes->list_mode == 'ADMIN') || ($JobListAttributes->list_mode=='EMPLOYER')) {
				$new_window = "onclick=\"window.open('post_window.php?post_id=".$post_id."', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=800,height=500,left = 50,top = 50');return false;\"";
			}

			if ($template_tag=='POSTED_BY') { // this link will lead to the employer's profile / list

				$val = $LM->get_open_link(JB_emp_profile_url($Form->get_template_value ('USER_ID', $admin), $JobListAttributes), $new_window).
					$val.
					$LM->get_close_link();

			} else { // this link will lead to the job post

				$val = $LM->get_open_link(JB_job_post_url($post_id, $JobListAttributes), $new_window).
					$val.
					$LM->get_close_link();
			}

		}

		JBPLUG_do_callback('job_list_data_val', $val, $template_tag); //A plugin can modify the $val

		$LM->list_cell_open($template_tag, $class_postfix);
		$LM->echo_post_data($val, $b1, $b2);
		$LM->list_cell_close();

	}


}

########################################

function JB_echo_employer_list_data($admin) {

	global $column_list, $column_info, $label, $cur_offset, $order_str, $q_offset, $show_emp, $cat, $list_mode, $q_string;

	$Form = &JB_get_DynamicFormObject(4);
	$ttf = &$Form->get_tag_to_field_id();

	if (!isset($_REQUEST['order_by'])) $_REQUEST['order_by'] = '';

	if ($_REQUEST['order_by']!='') {

			$ord = jb_alpha_numeric($_REQUEST['ord']);
			if ($ord=='asc') {
				$ord = 'desc';
			}elseif ($ord=='desc') {
				$ord = 'asc';
			} else {
				$ord = 'asc';
			}

			$order_str = "&amp;order_by=".JB_escape_html($_REQUEST['order_by'])."&amp;ord=".$ord;
		
	}


	foreach ($column_list as $template_tag) {

		if (($column_info[$template_tag]['admin']=='Y') && (!$admin)) {
			continue; // do not render this column
		}


		$val = $Form->get_value($ttf[$template_tag]['field_id']); // get the raw value
		
		

		// process the value depending on what kind of template tag it was given.

		switch ($template_tag) {
			case 'DATE':
				$val = JB_get_formatted_date(JB_get_local_time($val));
				break;
			case 'TIME':
				$val = JB_get_formatted_date(JB_get_local_time($val));	
				break;
			case 'USERNAME':
				$val = $val.' (#'.$Form->get_template_value('ID', $admin).') ';
				break;
			case 'LCOUNT': // login count, show a rest star if user logged in
				if ($Form->get_template_value ('LODATE', $admin)=='0000-00-00 00:00:00') { 
					$val = $val.'<FONT SIZE="4" COLOR="#FF0000">*</FONT>'; 
				}
				break;
			case 'HAS_PROFILE':

				// does employer have a profile

				$sql = "SELECT profile_id FROM `profiles_table` WHERE `user_id`='".jb_escape_sql($Form->get_template_value('ID',  $admin))."' AND expired='N' ";
				
				$result2 = JB_mysql_query($sql);
				$row2 = mysql_fetch_row($result2);
				$profile_id = $row2[0];

				if ($profile_id) {
					$val = "<a href='profiles.php?$q_string"."&amp;profile_id=".jb_escape_html($profile_id)."'>Y</a>";
				} else {
					$val = "N";
				}
				break;

			case 'POSTS':
				
				// how many posts?
				$now = (gmdate("Y-m-d H:i:s"));
				$sql = "SELECT count(*) FROM `posts_table` WHERE `user_id`='".jb_escape_sql($Form->get_template_value('ID', $admin))."' AND expired='N' ";
				
				$result2 = JB_mysql_query($sql);
				$row2 = mysql_fetch_row($result2);
				$count2 = $row2[0];

				if ($count2 > 0) {
					$val = "<a href='posts.php?$q_string"."&amp;show_emp=".$Form->get_template_value('ID', $admin)."&amp;show=EMP'>".$count2."</a>";
				} else {
					$val = "N";
				}
				break;
			case 'NAME':
				// get the name of the user

				// get_template_value() will return the HTML escaped value ready for output
				$val = $Form->get_template_value('LNAME', $admin).", ".$Form->get_template_value('FNAME', $admin);
				if ($column_info[$template_tag]['trunc']>0) {
					$val = JB_truncate_html_str($val, $column_info[$template_tag]['trunc'], $trunc_str_len);
				}
				break;
			
			default:

				if ($column_info[$template_tag]['trunc']>0) {
					$val = JB_truncate_html_str($val, $column_info[$template_tag]['trunc'], $trunc_str_len);
				}

				$val = JB_get_list_template_value($ttf[$template_tag], $val, $admin, 4);
				

				if ($column_info[$template_tag]['clean']=='Y') { // fix up punctuation spacing
					$val = preg_replace(JB_CLEAN_PUN_REGEX, '$1 ', $val);
				}


			}

			

		
		

		// Add additional HTML 

		if ($column_info[$template_tag]['is_bold']=='Y') {
			$b1='<b>'; $b2='</b>';
		} else {
			$b1='';$b2='';
		}


		if ($column_info[$template_tag]['link']=='Y')  { // Render as a Link to the record?
			$val = '<a href="'.htmlentities($_SERVER['PHP_SELF']).'?user_id='.$Form->get_template_value ('ID', $admin).'$order_str$q_string$q_offset$show_emp$cat">'.$val.'</a>';
		}

		JBPLUG_do_callback('emp_list_column_data_filter', $val, $template_tag);


		// the markup:
		?>
		<td class="list_data_cell" <?php if ($column_info[$template_tag]['no_wrap']=='Y') { echo ' nowrap '; } ?>>
			<?php echo $b1.$val.$b2; ?>
		</td>

		<?php

	}

	


}


########################################

function JB_echo_candidate_list_data($admin) {

	global $column_list, $column_info, $label, $cur_offset, $order_str, $q_offset, $show_emp, $cat, $list_mode;

	
	$Form = &JB_get_DynamicFormObject(5);
	$ttf = &$Form->get_tag_to_field_id();

	if ($_REQUEST['order_by']!='') {

			$ord = jb_alpha_numeric($_REQUEST['ord']);
			if ($ord=='asc') {
				$ord = 'desc';
			}elseif ($ord=='desc') {
				$ord = 'asc';
			} else {
				$ord = 'asc';
			}

			$order_str = "&amp;order_by=".JB_escape_html($_REQUEST['order_by'])."&amp;ord=".$ord;
		
	}

	foreach ($column_list as $template_tag) {
		
		if (($column_info[$template_tag]['admin']=='Y') && (!$admin)) {
				continue; // do not render this column
		}

		$val = $Form->get_template_value ($template_tag, $admin);

		if ($template_tag=='LCOUNT') {

			if ($Form->get_template_value ('LODATE', $admin)=='0000-00-00 00:00:00') { 
				$val = $val.'<FONT SIZE="4" COLOR="#FF0000">*</FONT>'; 
			}

		}elseif ($template_tag=='RESUME_ID') {
			
			$now = (gmdate("Y-m-d H:i:s"));
			$sql = "SELECT resume_id FROM `resumes_table` WHERE `user_id`='".$Form->get_template_value('ID', $admin)."'  ";

			$result2 = JB_mysql_query($sql);
			if (mysql_num_rows($result2) > 0) {
				$row2 = mysql_fetch_row($result2);
				$val = "<a href='resumes.php?$q_string"."&amp;resume_id=".$row2[0]."'>Yes</a>";
			} else {
				$val = "No";
			}
		}elseif ($template_tag=='USERNAME') {
			$val = $val.' (#'.$Form->get_template_value('ID', $admin).') ';
			
		}elseif ($template_tag=='NAME') {
			$val = $Form->get_template_value('LNAME', $admin).", ".$Form->get_template_value('FNAME', $admin);
		} else {

			if ($LM->column_info[$template_tag]['trunc']>0) {
				$val = JB_truncate_html_str($val, $LM->column_info[$template_tag]['trunc'], $trunc_str_len);
			}

			$val = JB_get_list_template_value($ttf[$template_tag], $val, $admin, 5);

			if ($column_info[$template_tag]['clean']=='Y') { // fix up punctuation spacing
				$val = preg_replace(JB_CLEAN_PUN_REGEX, '$1 ', $val);
			}


		}
		
		JBPLUG_do_callback('can_list_column_data_filter', $val, $template_tag);

		if ($column_info[$template_tag]['is_bold']=='Y') {
			$b1="<b>"; $b2="</b>";
		} else {
			$b1='';$b2='';
		}


		if ($column_info[$template_tag]['link']=='Y')  { // Render as a Link to the record?

			$val = "<a href='".htmlentities($_SERVER['PHP_SELF'])."?action=edit&user_id=".$Form->get_template_value ('ID', $admin)."$order_str$q_string$q_offset$show_emp$cat'>".$val."</a>";

		}


		?>
		<td class="list_data_cell" <?php if ($column_info[$template_tag]['no_wrap']=='Y') { echo ' nowrap '; } ?>>
			<?php echo $b1.$val.$b2; ?>
		</td>

		<?php

	}


}
########################################



function JB_echo_proile_list_data($admin) {

	global $label, $cur_offset, $order_str, $q_offset, $show_emp, $cat, $list_mode;
	
	$LM = &JB_get_ListMarkupObject(3);// load the ListMarkup Class
	$Form = &JB_get_DynamicFormObject(3);

	$ttf = &$Form->get_tag_to_field_id();
	if ($_REQUEST['order_by']!='') {

			$ord = jb_alpha_numeric($_REQUEST['ord']);
			if ($ord=='asc') {
				$ord = 'desc';
			}elseif ($ord=='desc') {
				$ord = 'asc';
			} else {
				$ord = 'asc';
			}

			$order_str = "&amp;order_by=".JB_escape_html($_REQUEST['order_by'])."&amp;ord=".$ord;
		
	}

	
	foreach ($LM->column_list as $template_tag) {

		if (($LM->column_info[$template_tag]['admin']=='Y') && (!$admin)) {
			continue; // do not render this column
		}

		$val = $Form->get_value($ttf[$template_tag]['field_id'], $admin);

		if ($LM->column_info[$template_tag]['trunc']>0) {
			$val = JB_truncate_html_str($val, $LM->column_info[$template_tag]['trunc'], $trunc_str_len);
		}
		

		$val = JB_get_list_template_value($ttf[$template_tag], $val, $admin, 3);

		JBPLUG_do_callback('pro_list_column_data_filter', $val, $template_tag);
		

		if ($LM->column_info[$template_tag]['clean']=='Y') { // fix up punctuation spacing
			$val = preg_replace(JB_CLEAN_PUN_REGEX, '$1 ', $val);	
		}

		if ($LM->column_info[$template_tag]['is_bold']=='Y') {
			$b1="<b>"; $b2="</b>";
		} else {
			$b1='';$b2='';
		}

	

		if ($LM->column_info[$template_tag]['link']=='Y')  { // Render as a Link to the record?

			$val = "<a href='".htmlentities($_SERVER['PHP_SELF'])."?profile_id=".$Form->get_template_value ('PROFILE_ID', $admin)."$order_str$q_string$q_offset$show_emp$cat'>".$val."</a>";

		}


		?>
		<td class="list_data_cell" <?php if ($LM->column_info[$template_tag]['no_wrap']=='Y') { 
			echo ' nowrap '; } ?>>
			<?php echo $b1.$val.$b2; ?>
		</td>

		<?php

	}


}
########################################

function JB_get_list_template_value($field, $val, $admin, $form_id=1) {

	$LM = &JB_get_ListMarkupObject($form_id);
	$Form = &JB_get_DynamicFormObject($form_id);
	

	// it is assumed that this function is called in 'view' mode
	// the viewer id and type is unknown so null is passed
	if ($Form->process_field_restrictions($field, null, null, $admin)) {
		// Its a restricted field, eg anonymous, blocked or member's only
		return $Form->get_value($field['field_id']); 
	} 


	switch ($field['field_type']) {
		case 'TIME':
			// convert timestamp to local time zone
			// using the raw value stored in the record
			if ($val != '0000-00-00 00:00:00') {
				$val = JB_get_local_time($Form->get_value($field['field_id']).' GMT');
			}
			break;
		case 'EDITOR':
			$val = strip_tags($val);
			$val = jb_escape_html($val);
			if (!$admin) $val = JB_email_at_replace($val);
		break;
		case 'IMAGE':
			if (JB_image_thumb_file_exists($Form->get_value($field['field_id']))) {
				$val = $LM->get_img_html($Form->get_value($field['field_id']));
			}
			break;
		case "CURRENCY":
			if ($val>0) {
				$val = JB_escape_html(JB_format_currency($Form->get_value($field['field_id']), JB_get_default_currency()));
			} else {
				$val = '';
			}
			break;
		case "CATEGORY":
			$val = jb_escape_html(JB_getCatName($Form->get_value($field['field_id'])));
			break;
		case "RADIO": 
			$val = jb_escape_html(JB_getCodeDescription ($field['field_id'], $Form->get_value($field['field_id'])));
			break;
		case "SELECT":
			$val = jb_escape_html(JB_getCodeDescription ($field['field_id'], $Form->get_value($field['field_id'])));
			break;
		case "MSELECT":
		case "CHECK":
			$vals = explode (",", $Form->get_value($field['field_id']));
			$comma = ''; $str='';
			if (sizeof($vals)>0) {
				foreach ($vals as $v) {
					$str .= $comma.jb_escape_html(JB_getCodeDescription ($field['field_id'], $v));
					$comma = ", ";
				}
			}
			$val = $str;
			break;
		

		case "DATE":
		case "DATE_CAL":
			
			if ($val != '0000-00-00 00:00:00') {
				$val = JB_get_local_time($Form->get_value($field['field_id'])." GMT");
				$val = JB_get_formatted_date($val);
			} else {
				$val = '';
			}
			
			break;
		case "SKILL_MATRIX":
			$sql = "SELECT name FROM skill_matrix_data where object_id='".JB_escape_sql($Form->get_value('resume_id'))."' ";
			$result = JB_mysql_query ($sql) or die (mysql_error());
			$val=''; $comma='';
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
				$val .= $comma.$row['name'];
				$comma = ", ";
			}
			break;
		
		default:
			// plugins can alter the data in the cell to be 
			// be presented on the list in a custom manner
			$args = array(
				'val' => &$val, 
				'has_changed'=> false,
				'field' => &$field,
				'form_id' => $form_id,
				'data' => $Form->get_values()
			);
			JBPLUG_do_callback('get_list_template_value', $args); // This hook was added in 3.6, allows plugins to modify the cell data based on $field, eg. $field['field_type'], the plugin should set 'has_changed' to true if the data in 'val' was changed.

			if ($args['has_changed']) { // has it changed?
				return $val;
			}
			// if not modified by plugin
			$val = jb_escape_html($val);
			if (!$admin) $val = JB_email_at_replace($val);
			
	}

	return $val;


}



########################################
# Admin Stuff
########################################

function JB_field_select_option_list ($form_id, $selected, $prefix='') {

	global $label;

	$col_row['field_id'] = $selected;

	$fields = JB_schema_get_fields($form_id);

	foreach ($fields as $field) {
		if (($field['field_type'] == 'BLANK') || ($field['field_type'] == 'SEPERATOR') || ($field['field_type'] == 'NOTE')) {
			continue;
		}
		if ($field['field_id']==$selected) {
			$sel = " selected ";
		} else {
			$sel = "";
		}
		if ($field['field_type']) {
			$field_type = "(".$field['field_type'].")";
		} else {
			$field_type = '';

		}
		if (strlen($field['field_label'])>0) {
			echo "<option $sel value='".$prefix.$field['field_id']."'>".$prefix.JB_escape_html($field['field_label'])." $field_type</option>\n";
		}
	}


}


###################################################################

function JB_echo_list_head_data_admin($form_id) { 

	global $q_string, $column_list;


	$sql = "SELECT * FROM form_lists where form_id='".jb_escape_sql($form_id)."' ORDER BY sort_order ASC ";
	$result = JB_mysql_query($sql);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$column_list[$row['field_id']]=$row['template_tag'];
		
		?>
		<td class="list_header_cell" nowrap>
		<?php echo '<small>('.$row['sort_order'].')</small>'; ?>
		<a href='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=edit&column_id=<?php echo $row['column_id'];?>'><?php echo JB_get_template_field_label ($row['template_tag'], $form_id);?></a> <a onClick="return confirmLink(this, 'Delete this column from view, are you sure?') " href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=del&column_id=<?php echo $row['column_id']?>"><IMG src='delete.gif' width='16' height='16' border='0' alt='Delete'></a> 
<a href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=edit&column_id=<?php echo $row['column_id']; ?>">
   <img alt="edit" src="edit.gif" width="16" height="16" border="0" alt="Edit">
		</td>
		<?php
	}
	


}

###################################################



?>