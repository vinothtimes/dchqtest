<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
/*


Classed used to keep the following properities:
$tag_to_field_id - list of fields and settings from the form_fields table
$searchObj - list of search fields and their settings
$data - The data values of the last record loaded

The following methods can be performed:
- get the data structures: get_tag_to_search() and get_tag_to_field_id()
- Set the data values: set_values($row)
- Get the data value using a template tag: get_template_value(..)

If you are looking for the HTML markup, try the JBDynamicFormMarkup.php
template file in your themes directory.

*/
class JBDynamicForm {

	var $data = array(); // form data
	var $errors = array();
	var $form_id;
	var $tag_to_field_id = array();
	var $searchObj;

	var $viewer_id;
	var $viewer_type;

	var $field_pos=0;
    
    var $DFM;
    var $mode;
    
	
	
	// If the form is to be displayed, it is important that the JBDynamicForm 
	// is instantiated  before the header the page is generated.

	function JBDynamicForm($form_id) {
		
		if (!is_numeric($form_id)) {
			die ('JBDynamicForm class - invalid form id passed');
		}

		$this->form_id = $form_id;

		static $markup_appended = array();

		// Who is viewing this form?
		if (isset($_SESSION['JB_ID'])) {
			$this->viewer_id=$_SESSION['JB_ID'];
		}
		if (isset($_SESSION['JB_ID'])) {
			$this->viewer_type=$_SESSION['JB_Domain'];
		}


		switch ($form_id) {
			case 1:
				if (!function_exists('JB_post_tag_to_field_id_init')) {
					require_once(JB_basedirpath().'include/posts.inc.php');
				}
				$this->tag_to_field_id = JB_post_tag_to_field_id_init();
				break;
			case 2:
				if (!function_exists('JB_resume_tag_to_field_id_init')) {
					require_once(JB_basedirpath().'include/resumes.inc.php');
				}
				$this->tag_to_field_id = JB_resume_tag_to_field_id_init();
				break;
			case 3:
				if (!function_exists('JB_profile_tag_to_field_id_init')) {
					require_once(JB_basedirpath().'include/profiles.inc.php');
				}
				$this->tag_to_field_id = JB_profile_tag_to_field_id_init();
				break;
			case 4:
				if (!function_exists('JB_tag_to_field_id_init_emp')) {
					require_once(JB_basedirpath().'include/employers.inc.php');
				}
				$this->tag_to_field_id = JB_tag_to_field_id_init_emp();
				break;
			case 5:
				if (!function_exists('JB_tag_to_field_id_init_candidate')) {
					require_once(JB_basedirpath().'include/candidates.inc.php');
				}
				$this->tag_to_field_id = JB_tag_to_field_id_init_candidate();
				break;
			default:
				// can be used for adding other forms types by plugins
				
				JBPLUG_do_callback('custom_tag_to_field_id', $this->tag_to_field_id);
				break;
		}


		if (!isset($markup_appended[$form_id])) {
			$this->append_extra_markup();
			$markup_appended[$form_id] = true;
		}

		
		
	}
    
    function get_mode() {
      
        if (is_object($this->DFM)) {
            return $this->DFM->get_mode();
        } else {
            return 'none';
        }
    }
    
    function is_edit_mode() {
        
        $mode = $this->get_mode();

        if (($mode=='edit') || ($mode == 'EDIT') || ($mode == 'VIEW')) return true;
        
        return false;
    }
    
    function set_mode($mode) {
        $this->mode = $mode;
        if (!is_object($this->DFM)) {
            $this->DFM = &JB_get_DynamicFormMarkupObject(); 
   
        }
		$this->DFM->set_form_id($this->form_id);
		$this->DFM->set_mode($mode);
        
        
    }
    
    function get_DynamicFormMarkup() {
        if (!$this->DFM) {
            $this->DFM = &JB_get_DynamicFormMarkupObject(); 
        }
        return $this->DFM;
    }

	###########################################################################
	
	function set_viewer($viewer_id, $viewer_type='EMPLOYER') {

		$this->viewer_id=$viewer_id;
		$this->viewer_type=$viewer_type;
		
	}

	###########################################################################


	function append_extra_markup() {

		global $JBMarkup;

		if (isset($JBMarkup)) {
		  
            # These are added to the handler in JBMarkup.php so that calling of
            # $this->get_extra_markup() is delayed until JBMarkup is being used.
            
            $JBMarkup->set_handler('before_body_close', $this, 'get_extra_markup', 'before_body_close');
            $JBMarkup->set_handler('header', $this, 'get_extra_markup', 'header');
            $JBMarkup->set_handler('body_after_open', $this, 'get_extra_markup', 'body_after_open');
            $JBMarkup->set_handler('onload_function', $this, 'get_extra_markup', 'onload_function');
        	
		}

	}



	###########################################################################

	function &get_search_object() {
		if (is_object($this->searchObj)) {
			return $this->searchObj;
		} else {
			$this->searchObj = &getDynamicSearchFormObject($this->form_id);
			return $this->searchObj;
		}


	}

	###########################################################################
	function add_tag_to_search($template_tag, $field_attributes) {
		// php5: $this->get_search_object()->add_tag_to_search($template_tag, $field_attributes);
		$obj = $this->get_search_object();
		$obj->add_tag_to_search($template_tag, $field_attributes);
		

	}
	

	###########################################################################

	function &get_tag_to_search() {
		// php5: return $this->get_search_object()->tag_to_search;
		$obj = $this->get_search_object();
		return $obj->tag_to_search;

	}
	###########################################################################

	function &get_tag_to_field_id() {
		return $this->tag_to_field_id;

	}
	###########################################################################

	function get_template_tag_attribute($template_tag, $attribute_name='field_type') {

		return $this->tag_to_field_id[$template_tag][$attribute_name];
	}
	###########################################################################

	function &get_values() {
		return $this->data;
	}
	###########################################################################

	function set_values(&$v) {
		
		if (sizeof($v)>0) {
			$this->data = $v;
		}


	}

	###########################################################################

	function get_value($field_id) {
		
		return $this->data[$field_id];
	}
	###########################################################################

	function set_value($field_id, $value) {
		
		$this->data[$field_id] = $value;
		global $prams;// older code compatibility
		$prams[$field_id] = $value; // older code compatibility

	}
	###########################################################################

	# Load the raw data values form the database
	function &load($record_id, $user_id='') {

		switch ($this->form_id) {

			case 1:
				// include/posts.inc.php
				$this->set_values(JB_load_post_data ($record_id));
				break;
			case 2:
				// include/resumes.inc.php
				$this->set_values(JB_load_resume_data ($record_id));
				break;
			case 3:
				// include/profiles.inc.php
				$this->set_values(JB_load_profile_data ($record_id, $user_id));
				break;
			case 4:
				// include/employers.inc.php
				$this->set_values(JB_load_employer_data ($record_id));
				break;
			case 5:
				// include/candidates.inc.php
				$this->set_values(JB_load_candidate_data ($record_id));
				break;
			default:
				$values = null;
				JBPLUG_do_callback('load_values', $values, $this->form_id, $record_id);
				if (is_array($values)) {
					$this->set_values($values);
				}
				break;
		}

		 // now that the fields are initialized and loaded, we can append
		 // extra tags to the <head> and <body> sections as needed
		 // Tgas will be appended only if JBMarkup is not null
		$this->append_extra_markup();

		return $this->get_values();

	}

	###########################################################################
	# Save the data that was entered through the form
	# - Please call validate() was called before calling save()
	

	function save($is_admin=false) {

		switch ($this->form_id) {

			case 1:
				// include/posts.inc.php
				if ($is_admin) {
					return JB_insert_post_data('ADMIN');
				} else {
					return JB_insert_post_data('EMPLOYER');
				}
				break;
			case 2:
				// include/resumes.inc.php
				return JB_insert_resume_data($is_admin);
				break;
			case 3:
				// include/profiles.inc.php
				return JB_insert_profile_data($is_admin);
				break;
			case 4:
				// include/employers.inc.php

				return JB_insert_employer_data($is_admin);

				break;
			case 5:
				// include/candidates.inc.php
				return JB_insert_candidate_data($is_admin);
				break;
			default:
				$record_id = null;
				JBPLUG_do_callback('save_form', $record_id, $this->form_id, $is_admin);
				if (!is_null($record_id)) {
					return $record_id;
				}
				break;
		}


	}


	###########################################################################

	function validate($is_admin=false) {
		
		$this->errors = array();

		switch ($this->form_id) {

			case 1:
				// include/posts.inc.php
				if ($is_admin) {
					$this->errors = JB_validate_post_data('ADMIN');
				} else {
					$this->errors = JB_validate_post_data('EMPLOYER');
				}
				break;
			case 2:
				// include/resumes.inc.php

				$this->errors = JB_validate_resume_data(2);
				break;
			case 3:
				// include/profiles.inc.php
				$this->errors = JB_validate_profile_data(3);
				break;
			case 4:
				// include/employers.inc.php
				$this->errors = JB_validate_employer_data(4);
				break;
			case 5:
				// include/candidates.inc.php
				$this->errors = JB_validate_candidate_data(5);
				break;
			default:
				$error = null;
				JBPLUG_do_callback('validate_form', $error, $this->form_id, $is_admin);
				if (!is_null($error)) {
					$list = explode('<br>', $error);
					foreach ($list as $item) {
						$this->errors[] = $item;
					}
					
				}
				JBPLUG_do_callback('validate_form_array', $this->errors, $this->form_id, $is_admin); // added 3.6.6
				
				break;
		}

		if (sizeof($this->errors)==0) {
			return false;
		} else {
			return $this->errors;
		}

	}

	###########################################################################

	function get_error_msg() {
		global $JBMarkup;
		if (is_array($this->errors)) {
			foreach ($this->errors as $line) {
				$msg .= $JBMarkup->get_error_line($line); // $error is then displayed in profile-form.php template
			}
		}
		return $msg;
	}
	###########################################################################
	/*

	Method: 
	
	next_field()

	Description:

	Convenient way to iterate through the data stored in the form.
	It will return the meta-data about the next field. 
	
	Returns:

	Returns the next field from the $this->tag_to_field_id array.
	eg.
	Array ( [field_id] => 'post_date',
	[template_tag] => 'DATE',
	[field_label] => 'Date',
	[field_type] => 'TIME' )
	

	Example:

	$RForm = &JB_get_DynamicFormObject(2);
	$data = $RForm->load(9687);

	$RForm->reset_fields();
	while ($field = $RForm->next_field()) {
		echo $RForm->get_raw_template_value($field['template_tag']).'<br>';
	}


	
	Related:

	$this->reset_fields() - rewind to the start, so next_field() will
	return the first item when it is called.



	*/
	function next_field() {

		if (!$this->field_pos) {
			$this->field_pos++;
			return current($this->tag_to_field_id);
		} else {
			$this->field_pos++;
			return next($this->tag_to_field_id);
		}


	}

	function reset_fields() {
		$this->field_pos = 0;
		reset($this->tag_to_field_id);
	
	}

	###########################################################################
	# Is the field restricted for the currently logged in user?
	function is_field_restricted($tmpl, $admin=false) {

		return JB_process_field_restrictions($this->data, $this->tag_to_field_id[$tmpl], 'view', $admin);

	}

	###########################################################################

	/*

	Method:

	process_field_restrictions($tmpl, $user_id, $user_type='EMPLOYER', $admin=false)

	description:

	This method sets the field's value stored in '$this->data' to the 
	reason why the field was blocked. This value is ready for output as HTML.
	
	arguments:

	(mixed) $field - Can be a template tag (string) to identify the field or
	a field row (array)

	(int) $viewer_id - The user id who is to view the field. Optional, defaults to $this->viewer_id
	(string) $viewer_type - The type of user, EMPLOYER or CANDIDATE. Optional, defaults to $this->viewer_type
	(boolean) $admin - Is viewed form Admin? Optional

	returns:

	(boolean) true if the field is restricted in some way, eg. blocked, member's only
	or anonymous.

	Example usage:

	$emp_id = 947;
	$candidate_id = 15022;
	
	
	// load the resume in to a form
	$RForm = &JB_get_DynamicFormObject(2);
	$data = $RForm->load(9687);

	print_r($data);

	// Anonymous resume
	// did the candiate grant request from employer?
	if ($data['anon']=='Y') {
		$is_visible = JB_is_request_granted($candidate_id, $emp_id);
		echo "anonymous resume visible status:[$is_visible]<br>\n";
	}

	// loop through each field, if not visible
	foreach ($RForm->get_tag_to_field_id() as $key=>$val) {
		if ($RForm->process_field_restrictions($key, $emp_id, 'EMPLOYER')) {
			// get_raw_template_value() will return the value with HTML stripped
			echo 'not visible - '.$RForm->get_raw_template_value($key).'<br>' ;
		} else {
			// get_template_value() will return the value ready for HTML output
			echo $RForm->get_template_value($key)."<br>\n";
		}

	}


	*/
	function process_field_restrictions($field, $viewer_id=null, $viewer_type=null, $admin=false) {
		if (!is_array($field)) {
			$field = $this->tag_to_field_id[$field];
		}
		if (is_null($viewer_id)) {
			$viewer_id = $this->viewer_id;
		}
		if (is_null($viewer_type)) {
			$viewer_type = $this->viewer_type;
		}
		return JB_process_field_restrictions($this->data, $field, 'view', $admin, $viewer_id, $viewer_type);
	}
	###########################################################################

	function get_template_field_label ($tmpl) {
		
		$field_label = $this->tag_to_field_id[$tmpl]['field_label'];
		// for plugin
		JBPLUG_do_callback('get_template_field_label', $field_label, $tmpl, $this->form_id);
		return $field_label;
	}

	###########################################################################
	# Get the the template value, strip tags and decode the HTML entities
	# Useful for output of form data to emails, URLs, etc, where data
	# should not be escaped using HTML entities.
	# Please take extra caution when outputting the result of this
	# function to the browser - even though strip_tags is called, there
	# it is not guaranteed to pass all. Extra filtering / escaping would need to be
	# done before output to the browser. Ie. use jb_escape_html()

	function get_raw_template_value($tmpl, $admin=false) {
	
		

		return strip_tags(html_entity_decode($this->get_template_value($tmpl, $admin)));

		// pre 3.6.1 code just returned the value:
		//$field_id = $this->tag_to_field_id[$tmpl]['field_id'];
		//if (!isset($this->data[$field_id])) $this->data[$field_id] = '';
		//return strip_tags($this->data[$field_id]);
		
	}
	###########################################################################

	// Get the data value. 
	// The returned data is ready to be output through:
	
	// - the templates
	// - the display_form_section() function
	// - anywhere else where the data from the form needs to be output
	// to the client

	// $tmpl = 'The template tag' - template tags is set through the forms
	// editor in Admin
	// $admin = is being viewed from admin (boolean)
	
	function get_template_value ($tmpl, $admin=false, $raw=false) {

		$val = '';


		$field_id = $this->tag_to_field_id[$tmpl]['field_id'];
	
		if (!isset($this->data[$field_id])) $this->data[$field_id] = '';

		if ($raw) { // return without any post-processing
			return $this->data[$field_id];
		}

		/* Hook here for your plugin to bypass this function
		 * Tip: Your plugin can use $this object like this: 
		 * $obj = JB_get_DynamicFormObject($form_id)
		 * $field_id = $this->tag_to_field_id[$tmpl]['field_id'];
		 * ...
		*/
		$val = false;
		JBPLUG_do_callback('get_template_value', $val, $this->form_id, $tmpl, $admin); 
		if ($val!==false) {
			return $val;
		}

		if ($this->process_field_restrictions($tmpl, $this->viewer_id, $this->viewer_type, $admin)) {
			// Its a restricted field, eg anonymous, blocked or member's only
			return $this->data[$field_id]; 
		}

		// it is assumed that this function is called in 'view' mode
		/*if (JB_process_field_restrictions($this->data, $this->tag_to_field_id[$tmpl], 'view', $admin)) {
			// Its a restricted field, eg anonymous, blocked or member's only
			return $this->data[$field_id]; 
		} */

		switch ($this->tag_to_field_id[$tmpl]['field_type']) {
			case "URL":
				$val = $this->data[$field_id]; 
				if (strlen($val)>0) {
					if ((strpos($val, 'http://')===false) && (strpos($val, 'https://')===false) ) {
						$val = 'http://'.$val;
					}
					$val = JB_escape_html($val); // no html allowed in this field
				}
				
				break;
			case "IMAGE":
				if (!JB_image_thumb_file_exists($this->data[$field_id])) { 
					$val = $label['employer_resume_list_no_image'];
				} else {
					$val = $this->data[$field_id];
				}
				
				break;
			case "NUMERIC":
			case "INTEGER":
				$val = jb_escape_html($this->data[$field_id]); // no html allowed in this field
				break;
			case "CURRENCY":
				if ($val>0) {
					$val = JB_escape_html(JB_format_currency($this->data[$field_id], JB_get_default_currency()));
				} else {
					$val = '';
				}
				break;
			case "CATEGORY":
				$val = jb_escape_html(JB_getCatName($this->data[$field_id]));
				break;
			case "RADIO": 
				$val = jb_escape_html(JB_getCodeDescription ($field_id, $this->data[$field_id]));
				break;
			case "SELECT":
				$val = jb_escape_html(JB_getCodeDescription ($field_id, $this->data[$field_id]));
				break;
			case "MSELECT":
			case "CHECK":
				$vals = explode (",", $this->data[$field_id]);
				$comma = ''; $str='';
				if (sizeof($vals)>0) {
					foreach ($vals as $v) {
						$str .= $comma.jb_escape_html(JB_getCodeDescription ($field_id, $v));
						$comma = ", ";
					}
				}
				$val = $str;
				break;
			case "TIME":
				if ($this->data[$field_id] != '0000-00-00 00:00:00') {
					// convert the time to a local time zone
					$val = JB_get_local_time($this->data[$field_id]." GMT");
				}
				break;

			case "DATE":
			case "DATE_CAL":
				
				if ($this->data[$field_id] != '0000-00-00 00:00:00') {
					
					$val = JB_get_local_time($this->data[$field_id]." GMT");
					$val = JB_get_formatted_date($val);
				} else {
					$val = '';
				}
				
				
				break;
			case "SKILL_MATRIX":
				$sql = "SELECT name FROM skill_matrix_data where object_id='".JB_escape_sql($this->data['resume_id'])."' ";
				$result = JB_mysql_query ($sql) or die (mysql_error());
				$val=''; $comma='';
				while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
					$val .= $comma.$row['name'];
					$comma = ", ";
				}
				break;
			case "TIME":
				// time is used for post_date, resume_date, signup_date, profile_date, etc..
				$val = JB_get_local_time($this->data[$field_id]." GMT"); // the time is always stored as GMT
				break;
			case 'TEXTAREA':
			case "TEXT":
				$val = JB_escape_html($this->data[$field_id]); // no html allowed in this field
				
				break;
			case "EDITOR":
				// HTML is allowed for this field
				//assuming that input was sanitized and only allowed HTML is included
				$val = ($this->data[$field_id]);
			
				break;
			case 'GMAP':

				$val = 'lat:'.$this->data[$field_id.'_lat'].'/lng:'.$this->data[$field_id.'_lng'];

				break;
			default:
				$val = false;
				// A plugin can filter the $val value to be returned
				JBPLUG_do_callback('get_template_value_filter', $val, $this->tag_to_field_id[$tmpl]['field_type']);
				if ($val!==false) {
					return $val;	
				} else {
					// $val is empty which means that it wasn't set by a plugin
					// escape HTML just in case.
					$val = JB_escape_html($this->data[$field_id]); // no html allowed in this field
				}
				
				break;
		}


		if ($field_id == '') {
			//echo '<b>Configuration error: Failed to bind the "'.$tmpl.'" Template Tag. (not defined)</b> <br> ';
		}

		
		return $val;

		
	}

	
	###########################################################################

	function display_form($mode, $admin=false) {


		
		if (!is_numeric($_REQUEST['user_id'])) {
			$_REQUEST['user_id']=$_SESSION['JB_ID'];
		}

		if (sizeof($this->data)==0) {
			$data = array();
			
			$this->init_data_from_request($data);
			$this->set_values($data);
		}

	
		switch($this->form_id) {

			case 1:

				JB_template_posting_form($mode, $admin);
				break;


			case 2:

				JB_template_resume_form($mode, $admin);

				break;

			case 3:
				
				if ($this->get_value('profile_id') || ($mode!='view')) {
					JB_template_profile_form($mode, $admin);
				}

				break;

			case 4:

				if ($admin) {
					$user_id = $_REQUEST['user_id'];
				} else {
					$user_id = $_SESSION['JB_ID'];
				}

				JB_template_employer_signup_form($mode, $admin, $user_id);


				break;
			case 5:

				if ($admin) {
					$user_id = $_REQUEST['user_id'];
				} else {
					$user_id = $_SESSION['JB_ID'];
				}

				JB_template_candidate_signup_form($mode, $admin, $user_id);

				break;

			default:

				$this->display_form_section ($mode, 1, $admin);

				break;


		}
		

	}

	###########################################################################

	/*

	function display_form ($mode, $section, $admin, $dont_break_container=false) 

	// $mode, eg 'view' or 'edit', or 'EDIT' when editing the fields
	// $section - Section of the form, 1, 2, 3 etc. 
	// $admin - boolean - true/false
	// $dont_break_container - if flase, then the function will render the 
	// form in a new container, else it will not open a new container
	// A new container is opened by JBDynamicFormMarkup::open_container();

	*/

	function display_form_section ($mode, $section, $admin, $dont_break_container=false) {


		global $label;

		# HTML output for this function comes from JBDynamicFormMarkup Class
		# include/themes/default/JBDynamicFormMarkup.php
		# Any HTML customizations should be done there.
		# Please copy this class in to your custom theme directory, and
		# customize form there

		
        
        $DFM = &$this->get_DynamicFormMarkup();
        $this->set_mode($mode);

		$cache_key = 'field_list_'.$section.'_'.$this->form_id.'_'.$_SESSION['LANG'];

		if (!$field_list=jb_cache_get($cache_key)) {

			$sql = "SELECT t2.field_label AS FLABEL, t1.*, t1.field_id AS ID, t2.field_comment AS FCOMMENT FROM form_fields AS t1, form_field_translations AS t2 WHERE t1.field_id=t2.field_id AND lang='".JB_escape_sql($_SESSION['LANG'])."' AND section='".JB_escape_sql($section)."' AND form_id='".JB_escape_sql($this->form_id)."' ORDER BY field_sort  ";

			$result = JB_mysql_query ($sql) or die (mysql_error());
			$field_list = array();
			while ($field_row = mysql_fetch_array($result, MYSQL_ASSOC)) {
				$field_list[] = $field_row;
			}
			jb_cache_add($cache_key, $field_list);


		}

		if (!$dont_break_container) {
			$DFM->open_container(); // put in a tag to start the element containing the form
		}

		if (!sizeof($field_list)); // There are no fields in this section to render...
		
		$i=0;

		foreach ($field_list as $field_row) {
			$i++;
			if (method_exists($DFM, 'set_field_row')) {
				$DFM->set_field_row($field_row);
			}

			if (($DFM->get_mode()=='EDIT') && ($_REQUEST['field_id']==$field_row['field_id'])) {
				// edit the form via Admin, and the field is being edited
				$bg_selected = ' style="background-color: #FFFFCC;" ';
			} else {
				$bg_selected = '';
			}

			// load init value...
			if ($this->data[$field_row['field_id']]=='') {
				$this->data[$field_row['field_id']]=$field_row['field_init'];
			}
			
			// For blocked fields, has the user chosen to remain annonymous?

			$is_restricted = JB_process_field_restrictions($this->data, $field_row, $DFM->get_mode(), $admin);

			JBPLUG_do_callback('pre_process_field', $this->data, $field_row);

			########################
			
			JBPLUG_do_callback('display_custom_2col_field',  $field_row, $this->data, $admin, $DFM->get_mode()); // your plugin should set $field_row['field_type'] to 'PLUGIN' to signal that it printed something out

			if (($field_row['is_hidden']=='Y') && ($DFM->get_mode() == 'view' ) && !$admin) {
			# Hidden Fields, do not appear on website (view mode) 

			} elseif ($field_row['field_type']=='PLUGIN') { // do nothing, already printed by plugin

			}
			elseif ($field_row['field_type']=='SEPERATOR') {
				$DFM->field_start();
				$DFM->seperator_open($bg_selected);
				if ($DFM->get_mode()=='EDIT')  { 
					JB_echo_order_arrows($field_row);
					echo '<a href="'.htmlentities($_SERVER['PHP_SELF']).'?field_id='.$field_row['field_id'].'&mode=EDIT"><IMG SRC="../admin/edit.gif" WIDTH="16" HEIGHT="16" align="middle" BORDER="0" ALT="-"> '; 
				} 
				$DFM->seperator_display(); // display the label
				
				if ($DFM->get_mode()=='EDIT')  { 
					echo '</a>'; 
				} 
				$DFM->seperator_close();
				$DFM->field_end();


			} elseif ($field_row['field_type']=="SKILL_MATRIX") {
				
				$DFM->field_start($bg_selected);
				$DFM->skill_matrix_field_open($bg_selected);
					

				if ($DFM->get_mode()=='EDIT')  { 
					JB_echo_order_arrows($field_row);
					echo '<a href="'.htmlentities($_SERVER['PHP_SELF']).'?field_id='.$field_row['field_id'].'&mode=EDIT"><IMG SRC="../admin/edit.gif" WIDTH="16" HEIGHT="16" align="middle" BORDER="0" ALT="-"> '; 
				}

				$DFM->skill_matrix_field_label();
				
				if ($DFM->get_mode()=='EDIT')  { 
					echo '</a>'; 
					?>
					<br>
					- 
					<a href=""
					onclick="window.open('build_matrix.php?field_id=<?php echo $field_row['field_id'];?>', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=550,height=500,left = 50,top = 50');return false;"> [Skill Matrix Settings...]
					</a>

					<?php
				
				}

				
				$DFM->skill_matrix_field_close();
				
					$DFM->skill_matrix_value_open($bg_selected);
					if ($is_restricted) {
						echo $this->data[$field_row['field_id']];
					} else {
						$DFM->skill_matrix_form();
					}
					$DFM->skill_matrix_value_close();
				
				$DFM->field_end();


					

			} elseif ($field_row['field_type']=='YOUTUBE') {

				if (($DFM->get_mode()=='view') && ($this->data[$field_row['field_id']]=='')) {
					// do not show the youtube field if it is blank
					continue;
				
				}

				$DFM->field_start();
				$DFM->youtube_field_open($bg_selected);

				if ($DFM->get_mode()=='EDIT')  {  
					JB_echo_order_arrows($field_row);
					echo '<a href="'.htmlentities($_SERVER['PHP_SELF']).'?field_id='.$field_row['field_id'].'&mode=EDIT"><IMG SRC="../admin/edit.gif" WIDTH="16" HEIGHT="16" align="middle" BORDER="0" ALT="-">'; 
				}
				$DFM->youtube_label();

				if ($DFM->get_mode()=='EDIT')  { 
					echo '</a>'; 
				}
				if (($DFM->get_mode()=='EDIT') && JB_is_reserved_template_tag($field_row['template_tag'])) {
					$alt = JB_get_reserved_tag_description($field_row['template_tag']);
					?>
					<a href="" onclick="alert('<?php echo htmlentities($alt); ?>');return false;">
					<IMG SRC="../admin/reserved.gif" WIDTH="13" HEIGHT="13" BORDER="0" ALT="<?php echo $alt; ?>">
					</a>	
					<?php

				}
				if (($_REQUEST['del_video'.$field_row['field_id']]!='')) { // delete video
					if (!$admin) {
						$user_sql = " AND user_id='".JB_escape_sql($_SESSION['JB_ID'])."' ";
					} else {
						$user_sql = " AND user_id='".JB_escape_sql($this->data['user_id'])."'";
					}
					$where = ' 1=2 ';
					if (is_numeric($_REQUEST['resume_id'])) {
						$where = " `resume_id`='".JB_escape_sql($_REQUEST['resume_id'])."' ";
					} elseif (is_numeric($_REQUEST['post_id'])) {
						$where = " `post_id`='".JB_escape_sql($_REQUEST['post_id'])."' ";
					} elseif (is_numeric($_REQUEST['profile_id'])) {
						$where = " `profile_id`='".JB_escape_sql($_REQUEST['profile_id'])."' ";
					} elseif (is_numeric($_REQUEST['user_id'])) {
						$where = " `ID`='".JB_escape_sql($_REQUEST['user_id'])."' ";
					}
					$table_name = JB_get_table_name_by_id($this->form_id);
					
					$sql = "UPDATE `".$table_name."` SET `".JB_escape_sql($field_row['field_id'])."` = '' WHERE $where $user_sql ";

					JB_mysql_query($sql) or die(mysql_error());
					
					$this->data[$field_row['field_id']]='';
					$_REQUEST[$field_row['field_id']] = '';
				}
				if ($this->data[$field_row['field_id']] !='') {
					if ($is_restricted) {
						echo $this->data[$field_row['field_id']];
					}
					else { // embed the field
						if ($field_row['field_width']<30) {
							$field_row['field_width'] = 325;

						}
						if ($field_row['field_height']<30) {
							$field_row['field_height'] = 250;

						}
						$DFM->youtube_display(); // embed youtube vid
					
					}
				}
				if ((strtolower($DFM->get_mode())=='edit'))  { // display input fields for the form

					if (!$this->data[$field_row['field_id']]) {
						$DFM->youtube_field(); // input youtube url
					} else {
						$DFM->youtube_delete_button();
					}
				}

				$DFM->youtube_field_close();
				$DFM->field_end();
			
				
			} elseif ($field_row['field_type']=="IMAGE") {

				$DFM->field_start();
				$DFM->image_field_open($bg_selected);

				if ($DFM->get_mode()=='EDIT')  { // admin's form editor 
					JB_echo_order_arrows($field_row);
					echo '<a href="'.htmlentities($_SERVER['PHP_SELF']).'?field_id='.$field_row['field_id'].'&mode=EDIT"><IMG SRC="../admin/edit.gif" WIDTH="16" HEIGHT="16" align="middle" BORDER="0" ALT="-">'; 
				}
				if (($field_row['is_required']=='Y') && $DFM->get_mode() != 'view') { 
					echo $DFM->get_required_mark(); 
				}

				$DFM->image_label($field_row);
				
				if ($DFM->get_mode()=='EDIT')  { echo '</a>'; }

				if (($DFM->get_mode()=='EDIT') && JB_is_reserved_template_tag($field_row['template_tag'])) {
					$alt = JB_get_reserved_tag_description($field_row['template_tag']);
					?>
					<a href="" onclick="alert('<?php echo htmlentities($alt); ?>');return false;">
						<IMG SRC="../admin/reserved.gif" WIDTH="13" HEIGHT="13" BORDER="0" ALT="<?php echo $alt; ?>">
					</a>
					
					<?php

				}
				
				if ($this->data[$field_row['field_id']] !='') {
					if ($is_restricted) {
						echo $this->data[$field_row['field_id']];
						
					}
					else { // display the field
					
						if ($_REQUEST['del_image'.$field_row['field_id']]!='') {
							
							if ($admin || (strpos($this->data[$field_row['field_id']], $_SESSION['JB_ID'].'_')===0)) { // if Admin or the filename starts with the user's id
							
								JB_delete_image($this->data[$field_row['field_id']]);
							}	
						} 
						

						if (JB_image_thumb_file_exists($this->data[$field_row['field_id']])) {
							// display the image
							if ((JB_KEEP_ORIGINAL_IMAGES=='YES') && (JB_image_original_file_exists($this->data[$field_row['field_id']]))) {
								$DFM->image_linked_display();
							} else {
								$DFM->image_thumb_display();
							}
						} else {
							// no image (but value exists in the database!)
							$DFM->image_display_null();
							
						}
					} 
				} else { // no data uploaded
					$DFM->image_display_null();
				}
					
				if ((strtolower($DFM->get_mode())=='edit'))  { // display input fields for the form
					// delete image button
					if (JB_image_thumb_file_exists($this->data[$field_row['field_id']]) && 
						($this->data[$field_row['field_id']]!='')) {

						$DFM->image_delete_button();
					} else {// upload image form

						$DFM->image_field();	
					}
				} 
				$DFM->image_field_close();
				$DFM->field_end();
				

			} elseif ($field_row['field_type']=='FILE') {

				$DFM->field_start();
				$DFM->file_field_open($bg_selected);
				
				if ($DFM->get_mode()=='EDIT')  { 
					JB_echo_order_arrows($field_row);
					echo '<a href="'.htmlentities($_SERVER['PHP_SELF']).'?field_id='.$field_row['field_id'].'&mode=EDIT"><IMG SRC="../admin/edit.gif" WIDTH="16" HEIGHT="16" align="middle" BORDER="0" ALT="-">'; 
				}
						
				$DFM->file_label();
				
				if ($DFM->get_mode()=='EDIT')  { 
					echo '</a>'; 
				}
				if (($field_row['is_required']=='Y') && $DFM->get_mode() != 'view') { 
					echo $DFM->get_required_mark(); 
				}
				if (($DFM->get_mode()=='EDIT') && JB_is_reserved_template_tag($field_row['template_tag'])) {
					$alt = JB_get_reserved_tag_description($field_row['template_tag']);
					?>
					<a href="" onclick="alert('<?php echo htmlentities($alt); ?>');return false;">
					<IMG SRC="../admin/reserved.gif" WIDTH="13" HEIGHT="13" BORDER="0" ALT="<?php echo $alt; ?>">
					</a>
					<?php
				}
				if ($_REQUEST['del_file'.$field_row['field_id']]!='') {
						
						if ($admin || (strpos($this->data[$field_row['field_id']], $_SESSION['JB_ID'].'_')===0)) { // if admin or the filename starts with the user's id
							JB_delete_file($this->data[$field_row['field_id']]);
						}
				}


				if ($is_restricted) {
					echo $this->data[$field_row['field_id']];
				} elseif (JB_upload_file_exists($this->data[$field_row['field_id']])) { 
					$DFM->file_display_link();
				} elseif ($DFM->get_mode()=='view') {
					$DFM->file_not_uploaded();
				}
				if ((strtolower($DFM->get_mode())=='edit'))  { 
					if (JB_upload_file_exists($this->data[$field_row['field_id']])&& ($this->data[$field_row['field_id']]!='')) {
						
						$DFM->file_delete_button();
						
					} else { 
						$DFM->file_field();
					}
				}	
				$DFM->file_field_close();
				$DFM->field_end();
					
			} elseif ($field_row['field_type']=='NOTE') {
				
				if ($DFM->get_mode() == 'view') { // note is only shown when edting the form

				} else {

					$DFM->field_start();
					$DFM->note_open($bg_selected);

					if ($DFM->get_mode()=='EDIT')  { 
						JB_echo_order_arrows($field_row);
						echo '<a href="'.htmlentities($_SERVER['PHP_SELF']).'?field_id='.$field_row['field_id'].'&mode=EDIT"><IMG SRC="../admin/edit.gif" WIDTH="16" HEIGHT="16" align="middle" BORDER="0" ALT="-"> '; 
					}
					
					$DFM->note_field();
					
					if ($DFM->get_mode()=='EDIT') { 
						echo '</a>'; 
					}
					
					if (($DFM->get_mode()=='EDIT') && JB_is_reserved_template_tag($field_row['template_tag'])) {
						$alt = JB_get_reserved_tag_description($field_row['template_tag']);
						?>
						<a href="" onclick="alert('<?php echo htmlentities($alt); ?>');return false;">
						<IMG SRC="../admin/reserved.gif" WIDTH="13" HEIGHT="13" BORDER="0" ALT="<?php echo $alt; ?>">
						</a>
						<?php
					}
					$DFM->note_close();
					$DFM->field_end();
				}	
				
			} elseif ($field_row['field_type']=='GMAP') { // Google map

				if (($DFM->get_mode()=='view') && (JB_GMAP_SHOW_IF_MAP_EMPTY!='YES') && ($this->data[$field_row['field_id'].'_lat']==0) ) {
					continue; // do not show this field
				}
				require_once(JB_basedirpath().'include/classes/JBGoogleMap.php');
				$DFM->field_start();
				$DFM->gmap_open($bg_selected);

				if ($DFM->get_mode()=='EDIT')  { 
					JB_echo_order_arrows($field_row);
					echo '<a href="'.htmlentities($_SERVER['PHP_SELF']).'?field_id='.$field_row['field_id'].'&mode=EDIT"><IMG SRC="../admin/edit.gif" WIDTH="16" HEIGHT="16" align="middle" BORDER="0" ALT="-">'; 
				}
				if (($field_row['is_required']=='Y') && $DFM->get_mode() != 'view') { 
					echo $DFM->get_required_mark(); 
				}
				$DFM->gmap_field_label();
				if ($DFM->get_mode()=='EDIT') { 
					echo '</a>'; 
				}
				if ($DFM->get_mode()=='view') {
					if ($is_restricted) {
						echo $this->data[$field_row['field_id']];
					} else {
						$DFM->gmap_show();
					}
				} else {	
					$DFM->gmap_mark();	
				}
				$DFM->gmap_close();
				$DFM->field_end();
				
				
			} else {

				// Fields below are made from two sides
				// Left side: field_left_open()
				//  - The left side is used to display the field's label
				//
				// Right side: field_right_open()
				//  - The right side is where the form widget is displayed when
				// editing the form, or where the data value is displayed
				// close with field_right_clode()
				
				if ($field_row['FLABEL']=='') { // field label is blank?
					$field_row['FLABEL']= $DFM->get_blank_field_label(); 
				}
				$DFM->field_start();
				$DFM->field_left_open($bg_selected);
				
				if ($DFM->get_mode()=='EDIT')  {  
					JB_echo_order_arrows($field_row);
					echo '<a href="'.htmlentities($_SERVER['PHP_SELF']).'?field_id='.$field_row['field_id'].'&mode=EDIT"><IMG SRC="../admin/edit.gif" WIDTH="16" HEIGHT="16" align="middle" BORDER="0" ALT="-">'; 
				} 
				$DFM->field_label();
				if ($DFM->get_mode()=='EDIT')  { 
					echo '</a>'; 
				}
				if (($field_row['is_required']=='Y') && ($DFM->get_mode()!='view')) { 
					echo $DFM->get_required_mark(); 
				}
				
				if (($DFM->get_mode()=='EDIT') && JB_is_reserved_template_tag($field_row['template_tag'])) {
					$alt = JB_get_reserved_tag_description($field_row['template_tag']);
					?>
					<a href="" onclick="alert('<?php echo htmlentities($alt); ?>');return false;">
					<IMG SRC="../admin/reserved.gif" WIDTH="13" HEIGHT="13" BORDER="0" ALT="<?php echo $alt; ?>">
					</a>
					<?php
				}
				if (($DFM->get_mode()=='EDIT') && ($field_row['field_type']=='BLANK')) { 
					echo '<a href="'.htmlentities($_SERVER['PHP_SELF']).'?field_id='.$field_row['field_id'].'&mode=EDIT">[]</a>';
				} 
				$DFM->field_left_close();
				$DFM->field_right_open($bg_selected);
				if (($is_restricted)) {
					echo $this->data[$field_row['field_id']]; // display blocked field message
					
				} else {

					switch ($field_row['field_type']) {

						case 'URL':
							$val = $this->get_template_value ($field_row['template_tag'], $admin);
							
							if ($DFM->get_mode() == 'view') { 
								echo $DFM->get_url_templated($val);
							} else {
								$DFM->text_field();

							}

							break;
				
						case "TEXT":
						case "URL":
						case "CURRENCY":
						case "NUMERIC":
						case "INTEGER":
							if ($DFM->get_mode() == 'view') { 
								// $this->get_template_value() will get and process the data value
								// for example, if CURRENCY, then it format in to a currency
								
								$val = $this->get_template_value ($field_row['template_tag'], $admin);
								$val = JB_email_at_replace($val);
								$DFM->_print($val);
							} else {
								$DFM->text_field();
							}
							break;
						
						case 'EDITOR':
							
							if ($DFM->get_mode() == 'view') { 
								
								$val = $this->get_template_value ($field_row['template_tag'], $admin);
								
								if (!preg_match("/<.*?>/U", $val)) { // Not text mode?
									$val = preg_replace ('/\n/', '<br>', $val);
								}

								if (JB_EMAIL_AT_REPLACE!='NO') {
									// eliminate tags with mailto:
									$val = preg_replace( '@<a href=["|\']mailto:.*["|\'] *>(.*)</a>@Ui', '$1', $val);
									$val = JB_email_at_replace($val);
								} 

								
								#
								$DFM->_print($val);
							} else {
								$DFM->editor_field();
							}
							break;
						case "TEXTAREA":
							if ($DFM->get_mode() == 'view') {
								
								$val = $this->get_template_value ($field_row['template_tag'], $admin);
								$val = str_replace("\n", "<br>", $val);
								$val = JB_email_at_replace($val);
								echo $DFM->_print($val);
							} else {
								$DFM->textarea_field();
							}
							break;
						case "CATEGORY":
							
							if ($DFM->get_mode() == 'view') {

								if (($this->form_id != 1) || (strpos($_SERVER['PHP_SELF'], 'index.php') === false)) { // not posting form, not index.php, assuming index.php is the home page. Only the home page has functionality for displaying the category after the links are clicked
									
									// not linked to the category
									$DFM->_print($this->get_template_value($field_row['template_tag'], $admin));
								} elseif (JB_CAT_PATH_ONLY_LEAF=='YES') {
									// with link
									$cat = array();
									$cat = JB_get_category($this->data[$field_row['field_id']]);
									$cat_url = JB_cat_url_write($this->data[$field_row['field_id']], $cat['NAME'], $cat['seo_fname']);
									$DFM->category_link($cat_url, $cat['NAME']);
								} else {
								
									// Multiple links -
									// Output the category using breadcrumb navigation
									// eg. Location -> Australia -> NSW
									$DFM->category_breadcrumbs();
								} 
							}
							else {
								$DFM->category_field();	
							}
							break;
						case "DATE":
						case "DATE_CAL":

							if ($DFM->get_mode() == 'view') { 
								
								$val = $this->get_template_value ($field_row['template_tag'], $admin);
								$DFM->_print($val);
							} else { 
								if ($field_row['field_type']=='DATE') { // traditional date input

									if (($this->data[$field_row['field_id']] == '0000-00-00 00:00:00') || ($this->data[$field_row['field_id']] == '')) {
										$year = '';
										$day = '';
										$month = '';
									} else {

										preg_match ("/(\d+)-(\d+)-(\d+)/", $this->data[$field_row['field_id']], $m);
										// Year - Month - Day (database output format)
										$year = $m[1];
										$day = $m[3];
										$month = $m[2];
									}
									
									$DFM->date_field($day, $month, $year);
								} else { // scw input
									$DFM->date_field_scw();
								}
							}
							break;
				
						case "SELECT":				
							if ($DFM->get_mode() == 'view') {
								
								$val = $this->get_template_value ($field_row['template_tag'], $admin);
								$DFM->_print($val);

							} else {
								$DFM->select_field();
							}
							if ($DFM->get_mode()=='EDIT')  { 
								?>
								<a href=""  onclick="window.open('maintain_codes.php?field_id=<?php echo $field_row['field_id'];?>', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=400,height=500,left = 50,top = 50');return false;"> [Edit Options]</a>
	
							<?php
							
							}
							break;
						case "RADIO":
							
							if ($DFM->get_mode() == 'view') {
								
								$val = $this->get_template_value ($field_row['template_tag'], $admin);
								$DFM->_print ($val);

							} else {
								$DFM->radio_field();
								
							}
							if ($DFM->get_mode()=='EDIT')  { 
								?>
								<a href=""
								onclick="window.open('maintain_codes.php?field_id=<?php echo $field_row['field_id'];?>', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=400,height=500,left = 50,top = 50');return false;"> [Edit Options]</a>
								<?php							
							}
							break;
						
						case "CHECK":
							
							if ($DFM->get_mode() == 'view') {
								
								$val = $this->get_template_value ($field_row['template_tag'], $admin);
								$DFM->_print ($val);

							} else {
								$DFM->checkbox_field();

							}
							if ($DFM->get_mode()=='EDIT')  { 
								?>
								<a href=""
								onclick="window.open('maintain_codes.php?field_id=<?php echo $field_row['field_id'];?>', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=400,height=500,left = 50,top = 50');return false;"> [Edit Options]</a>
								<?php
							}
							break;
						case "MSELECT":
							if ($DFM->get_mode() == 'view') {
								
								$val = $this->get_template_value ($field_row['template_tag'], $admin);
								$DFM->_print ($val);

							} else {
								$DFM->multiple_select_field();
							}
							if ($DFM->get_mode()=='EDIT')  { 
								?>
								<a href=""
								onclick="window.open('maintain_codes.php?field_id=<?php echo $field_row['field_id'];?>', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=400,height=500,left = 50,top = 50');return false;"> [Edit Options]
								</a>

								<?php
								
							}
							break;
						case "BLANK":
							$DFM->blank_field();
							break;
						default:
							JBPLUG_do_callback('display_custom_field', $field_row, $this->data, $admin, $DFM->get_mode()); // Your module should change $field_row['field_type'] to 'PLUGIN'

						break;

					}
				}
					
				$DFM->field_right_close();

				$DFM->field_end();
			}
			
		}

		if (!$dont_break_container) {
			$DFM->close_container();
		}



	}

	
	###########################################################################
	// Sometimes, special field types need to have extra HTML embedded 
	// in the header, or beofore the </body> tag or after the <body>
	// tag. Here is where extra code is generated.
	// This function goes through each of the fields and appends the extra 
	// markup as needed. For example, the SCW Date calendar field needs
	// to include a javascript in the header section of the document.
	// $where can be 'header', 'before_body_close', 'body_after_close', 'onload_function'

	function get_extra_markup($where) {

		static $DATE_CAL_HEADER_SET;
		static $EDITOR_HEADER_SET;
		static $GMAP_HEADER_SET;

		global $label, $JBMarkup;

		foreach ($this->tag_to_field_id as $field) {

			switch ($field['field_type']) {
				case 'DATE_CAL':
					if (($where=='header') && ($this->is_edit_mode())) {
						if (!isset($DATE_CAL_HEADER_SET)) {
							$extra_str .= $JBMarkup->get_stript_include_tag(jb_get_SCW_js_src());
							$DATE_CAL_HEADER_SET = true;
						}
						
					}
					break;
				case 'EDITOR':

					if (($where=='header') && ($this->is_edit_mode())) {

						if (!isset($EDITOR_HEADER_SET)) {
							$DFM = $this->get_DynamicFormMarkup();
							$extra_str .= $DFM->get_editor_field_header();
							$EDITOR_HEADER_SET = true;
						}
						

					}

					break;
				case 'GMAP':

					$lat = $this->get_value($field['field_id'].'_lat');
					$lng = $this->get_value($field['field_id'].'_lng');
					$zoom = $this->get_value($field['field_id']);
					if (!$zoom) $zoom = JB_GMAP_ZOOM;

					// Only add extra markup if there is map data.
				

					if (is_numeric($lat) && is_numeric($lng) && ($lat!=0)) {
						require_once(JB_basedirpath().'include/classes/JBGoogleMap.php');

						$GMAP = JBGoogleMap::get_instance();

						if ($where=='header') {

							$GMAP->add_map($field['field_id'], array('lat'=>$lat, 'lng'=>$lng), $zoom); 
							$GMAP->add_marker($field['field_id'], $lat, $lng, 'A', '', false);
							$args = array('gmap'=>$GMAP, 'Form'=>$this, 'field'=>$field);
							JBPlug_do_callback('gmap_add_map_after', $args); // plugin authors can manipulate the map here, eg. add more markers, etc.
							
							if (!isset($GMAP_HEADER_SET)) {
								
								$extra_str .= $GMAP->get_header_js();
								$GMAP_HEADER_SET = true;
							}

							$extra_str .= $GMAP->get_map_js($field['field_id']);

		
						}
						if ($where =='onload_function') {
							
							$extra_str .= $GMAP->get_onload_js($field['field_id']);

						}
					}
					break;
				default:
					JBPlug_do_callback('before_body_end_markup', $extra_str, $field, $where);
					break;

			}


		}

		return $extra_str;


	}

	###########################################################################

	# Take the data from $_REQUEST, format it, and put it in $data, ready
	# for use by other functions.
	# When a form is submitted, but there was a mistake with the submission
	# We need to display the form again. The data is not in the database
	# However, we need to format the data to make it just as if it was fetched
	# from the database.

	function init_data_from_request(&$data) {

		// Init the static fields
		$fields = &JB_schema_get_static_fields($this->form_id, JB_DB_MAP);

		
		foreach($fields as $field_id => $field) {
			if (!isset($data[$field_id]) && isset($_REQUEST[$field_id])) {
				switch ($field['field_type']) {
					case 'PASS': // password fields have a 'confirm' password field
						$data[$field_id] = $_REQUEST[$field_id];
						$data[$field_id.'2'] = $_REQUEST[$field_id.'2'];
						break;
					case 'ID':
						$data[$field_id] = (int) $_REQUEST[$field_id];
						break;
					default:
						$data[$field_id] = stripslashes($_REQUEST[$field_id]);
						break;
				}
			}
		}

		
	
		// init the dynamic fields
		foreach ($this->tag_to_field_id as $field) {

			switch ($field['field_type']) {

				case 'SEPERATOR':
				case 'BLANK':
				case 'NOTE':
					// do nothing for these
					break;
				case 'DATE':
					// Date field always comes out of the DB as Y-m-d
					$day = jb_alpha_numeric($_REQUEST[$field['field_id']."d"]);
					$month = jb_alpha_numeric($_REQUEST[$field['field_id']."m"]);
					$year = jb_alpha_numeric($_REQUEST[$field['field_id']."y"]);
					$data[$field['field_id']] = "$year-$month-$day";
					break;
				case 'DATE_CAL': // SCW calendar field
					$data[$field['field_id']] = JB_SCWDate_to_ISODate($_REQUEST[$field['field_id']]);
					break;
				case 'MSELECT': 
				case 'CHECK':
					// multiple select and checkboxes - these fields come in
					// as an array, need to be comma delimited
					if (is_array($_REQUEST[$field['field_id']])) {	
						$data[$field['field_id']] = implode (",", $_REQUEST[$field['field_id']]);
					} else {
						$data[$field['field_id']] = $_REQUEST[$field['field_id']];
					}
					break;
				case 'GMAP':
					$data[$field['field_id'].'_lat'] = $_REQUEST[$field['field_id'].'_lat'];
					$data[$field['field_id'].'_lng'] = $_REQUEST[$field['field_id'].'_lng'];

					break;
				case 'SKILL_MATRIX':
					$row_count = JB_get_matrix_row_count($field_row['field_id']);

					for ($i=0; $i < $row_count; $i++) {
						$data[$field['field_id']."name".$i] = stripslashes($_REQUEST[$field['field_id']."name".$i]);
						$data[$field['field_id']."years".$i] = jb_alpha_numeric($_REQUEST[$field['field_id']."years".$i]);
						$data[$field['field_id']."rating".$i] = jb_alpha_numeric($_REQUEST[$field['field_id']."rating".$i]);
					}
					break;
				case 'TEXT':
				case 'TEXTAREA':
				case 'EDITOR':
					$data[$field['field_id']] = stripslashes ($_REQUEST[$field['field_id']]);
					break;
				default:
					$val = false;
					JBPLUG_do_callback('init_data_from_request', $val, $field, $this->form_id);
					if ($val!==false) {
						$data[$field['field_id']] = $val;
						break;
					} elseif (isset($_REQUEST[$field['field_id']])) {
						$data[$field['field_id']] = stripslashes ($_REQUEST[$field['field_id']]);
					}
					break;
			}

		}

		


	}

	
	###########################################################################
	# Validates only the dynamic form parts of a form without any context
	# To validate the a whole form, see function validate()

	function JB_validate_form_data() {

		global $label;

		$DFM = $this->get_DynamicFormMarkup();

		$errors = array();
		

		$sql = "SELECT *, t2.field_label AS LABEL, t2.error_message as error_message FROM form_fields as t1, form_field_translations as t2 WHERE t1.field_id=t2.field_id AND t2.lang='".JB_escape_sql($_SESSION['LANG'])."' AND form_id='".JB_escape_sql($this->form_id)."' AND field_type != 'SEPERATOR' AND field_type != 'BLANK' AND field_type != 'NOTE' order by field_sort";

		$result = JB_mysql_query($sql) or die(mysql_error());
		while ($field_row = mysql_fetch_array($result, MYSQL_ASSOC)) {


			JBPLUG_do_callback('validate_form_data_init_row', $field_row);

			// fit to database

			$_REQUEST[$field_row['field_id']] = jb_fit_to_db_size($field_row['field_type'], $_REQUEST[$field_row['field_id']]);

			$custom_error = null;

			// The following is a hook for plugins to set a custom error message
			// plugins should set the $custom_error to the error message or
			// false if no error message was set
			JBPLUG_do_callback('validate_form_data_custom_field', $custom_error, $field_row);
		
			if ($custom_error !== null) {
				
				if ($custom_error) {
					$errors[] = $DFM->get_error_line($field_row['LABEL'], $custom_error);
				}
			
				continue;
			}

			
			if (($field_row['field_type']=='TEXT') || ($field_row['field_type']=='TEXTAREA') || ($field_row['field_type']=='EDITOR')) {
				if (JB_check_for_bad_words ($_REQUEST[$field_row['field_id']])) {
					$errors[] = $DFM->get_error_line($field_row['LABEL'], $label['bad_words_not_accept']);
					
				}
		
			}

			if (($field_row['field_type']=='CATEGORY') && (is_numeric($_REQUEST[$field_row['field_id']]))) {
				$sql = "SELECT * FROM categories WHERE category_id='".jb_escape_sql($_REQUEST[$field_row['field_id']])."' ";
				$cat_result = jb_mysql_query($sql);
				if ($cat_row = mysql_fetch_array($cat_result)) {
					if (($cat_row['allow_records']=='N')) {
						
						$errors[] = $DFM->get_error_line($field_row['LABEL'], $label['cat_records_not_allow']);
					}

				}

			}

			
			if (JB_BREAK_LONG_WORDS == 'YES') {

				if (($field_row['field_type']=='TEXT') || ($field_row['field_type']=='TEXTAREA')) {
					// HTML not allowed
					$_REQUEST[$field_row['field_id']] = trim(stripslashes(JB_break_long_words(addslashes($_REQUEST[$field_row['field_id']]), false)));
				} elseif ($field_row['field_type']=='EDITOR') {
					// HTML allowed, 2nd arg pass true
					$_REQUEST[$field_row['field_id']] = trim(addslashes(JB_break_long_words(stripslashes($_REQUEST[$field_row['field_id']]), true)));
				}

			}

			// clean the data..
			if (JB_STRIP_LATIN1=='YES') {
				$_REQUEST[$field_row['field_id']] = JB_remove_non_latin1_chars($_REQUEST[$field_row['field_id']]);
			}
			if (($field_row['field_type']=='EDITOR') || ($field_row['field_type']=='TEXTAREA')) {
				if (JB_STRIP_HTML=='YES') {
					// tags are allowed, remove them except on the white list.
					$_REQUEST[$field_row['field_id']] = stripslashes($_REQUEST[$field_row['field_id']]);
					$_REQUEST[$field_row['field_id']] = JB_clean_str($_REQUEST[$field_row['field_id']]);
					$_REQUEST[$field_row['field_id']] = addslashes($_REQUEST[$field_row['field_id']]);
					
				}
			}


			if ((($field_row['field_type']=='FILE') || ($field_row['field_type']=='IMAGE')) && ($_FILES[$field_row['field_id']]['name']!='')) {

				$a = explode(".", $_FILES[$field_row['field_id']]['name']);
				$ext = array_pop($a);

				if (!JB_is_filetype_allowed ($_FILES[$field_row['field_id']]['name']) && ($field_row['field_type']=='FILE')) {
					
					$label['vaild_file_ext_error'] = str_replace ("%EXT_LIST%", JB_ALLOWED_EXT, $label['vaild_file_ext_error']);
					$label['vaild_file_ext_error'] = str_replace ("%EXT%", $ext, $label['vaild_file_ext_error']);
					
					$errors[] = $DFM->get_error_line($field_row['LABEL'], $label['vaild_file_ext_error']);

				}
				
				if (!JB_is_imagetype_allowed ($_FILES[$field_row['field_id']]['name']) && ($field_row['field_type']=='IMAGE')) {
					$label['vaild_image_ext_error'] = str_replace ("%EXT_LIST%", JB_ALLOWED_IMG, $label['vaild_image_ext_error']);
					$label['vaild_image_ext_error'] = str_replace ("%EXT%", $ext, $label['vaild_image_ext_error']);
					
					$errors[] = $DFM->get_error_line($field_row['LABEL'], $label['vaild_image_ext_error']);

				} 
				
			
				if (get_cfg_var ('open_basedir')==NULL) { // open_basedir disabled
					// file size check when open_basedir is in effect
					if (@filesize($_FILES[$field_row['field_id']]['tmp_name'])>JB_MAX_UPLOAD_BYTES) {
						$label['valid_file_size_error'] = str_replace ("%FILE_NAME%", $_FILES[$field_row['field_id']]['name'], $label['valid_file_size_error']);
						
						$errors[] = $DFM->get_error_line($field_row['LABEL'], $label['vaild_image_ext_error']);
					}
				} 
			}
			

			if ($field_row['is_required']=='Y') {

				if (($field_row['field_type']=='DATE') || (($field_row['field_type']=='DATE_CAL'))) {
					$field_row['reg_expr'] = 'date'; // default to date check

				}

				if (($field_row['field_type']=='FILE') || ($field_row['field_type']=='IMAGE' )) {
					if ($_REQUEST[$field_row['field_id']]) { 
						// already uploaded a file, no error

					} 
						
					continue; // go to the next item in the while() loop to process the next field.
						
				}

				if (($field_row['field_type']=='IMAGE') ) {
					continue;
				}

				switch ($field_row['reg_expr']) {
					case "not_empty":
						if ($field_row['field_type']=='GMAP') {

							if (($_REQUEST[$field_row['field_id'].'_lat']==0) || ($_REQUEST[$field_row['field_id'].'_lng']==0)) {
								$errors[] = $DFM->get_error_line($field_row['LABEL'], $field_row['error_message']);
							}

						} elseif (trim($_REQUEST[$field_row['field_id']]=='')) {
							
							$errors[] = $DFM->get_error_line($field_row['LABEL'], $field_row['error_message']);
						}
						break;
					case "email":
						if (!JB_validate_mail(trim($_REQUEST[$field_row['field_id']]))) {
							
							$errors[] = $DFM->get_error_line($field_row['LABEL'], $field_row['error_message']);
						}
						break;
					case "date":


						if ($field_row['field_type']=='DATE') {
						
							$day = $_REQUEST[$field_row['field_id']."d"];
							$month = $_REQUEST[$field_row['field_id']."m"];
							$year = $_REQUEST[$field_row['field_id']."y"];

						} if ($field_row['field_type']=='DATE_CAL') {

							$temp_date = JB_SCWDate_to_ISODate($_REQUEST[$field_row['field_id']]);


							preg_match('/(\d+)-(\d+)-(\d+)/', JB_SCWDate_to_ISODate($_REQUEST[$field_row['field_id']]), $m);
							
							$year = $m[1];
							$month = $m[2];
							$day = $m[3];
							
						} else {
			
							$ts = strtotime($field_row['field_id']." GMT");
							if ($ts>0) {
								$day = date('d', $ts);
								$month = date('m', $ts);
								$year = date('y', $ts);
							}

						}
						
						if (($month=='') || ($day=='') || ($year=='') || !@checkdate (intval($month), intval($day), intval($year))) {
							
							$errors[] = $DFM->get_error_line($field_row['LABEL'], $field_row['error_message']);
							
						}


						break;
					case 'numeric':
						if (!is_numeric(trim($_REQUEST[$field_row['field_id']]))) {
							
							$errors[] = $DFM->get_error_line($field_row['LABEL'], $field_row['error_message']);
						}

						break;

					default:			
						break;
				}
					
			}
		}

		$error = '';
		JBPLUG_do_callback('validate_form_data', $error, $this->form_id);
		if ($error) {
			$list = explode('<br>', $error);
			foreach ($list as $item) {
				$errors[] = $item;
			}
		}
	
		return $errors;

	}


	/////////////////////////////////////////////
	/*

	Method

	Description

	Get the field names for the INSERT/REPLACE part of the query.
	Eg. INSERT INTO atable (`a`, `c`, `c`) VALUES (1, 2, 3);
	This function will return the '`a`, `c`, `c`' part.

	Arguments

	&$assign - This is a list of fields that would not be in the
	dynamic form (form_fields table). eg. for resume_table

	$assign = array(	
			'list_on_web' => 'Y',
			'resume_date' => gmdate("Y-m-d H:i:s"),
			'user_id' => $user_id,
			'approved' => $approved,
			'status' => 'ACT',
			'expired' => 'N'
		);





	*/

	function get_sql_insert_fields (&$assign) {

		// static fields (Defined in include/schema_functions.php)

		$fields = &JB_schema_get_static_fields($this->form_id, JB_DB_MAP);
		foreach ($fields as $field) {
			if ($field['field_type']=='ID') {
				continue;
			}
			$str .= "$comma `".$field['field_id']."` ";
			$comma = ',';
		}

		// dynamic fields
		
		foreach ($this->tag_to_field_id as $tag=>$field) {
			if (!is_numeric($field['field_id']) || ($field['field_type'] == 'BLANK') || ($field['field_type'] =='SEPERATOR') || ($field['field_type'] =='NOTE')) {
				continue;
			}

			switch ($field['field_type']) {

				case 'GMAP':

					$str .= ", `".$field['field_id']."_lat`, `".$field['field_id']."_lng`, `".$field['field_id']."` ";

					break;

				case "IMAGE":
					// if an image file is being uploaded, include this field in the list
					// otherwise, a file field is not touched.
					if ($_FILES[$field['field_id']]['name'] !='') {
						$str .= ", `".$field['field_id']."` ";
					}
					break;
				case "FILE":
					// if a file is being uploaded, include this field in the list
					// otherwise, a file field is not touched.
					if ($_FILES[$field['field_id']]['name'] !='') {
						
						$str .= ", `".$field['field_id']."` ";
					}
					break;
				
				default:
					$custom_sql = false;
					// Custom Fields: your plugin would have to append to $custom_sql string like the one after the else { starement
					JBPLUG_do_callback('get_sql_insert_fields', $custom_sql, $field);
					if ($custom_sql !== false) {
						$str .= $custom_sql;
					} else {
						$str .= ", `".$field['field_id']."` ";
					}
					break;
			}

		}

		return $str;

	}



	/*
	################################################################
	
	Method

	get_sql_insert_values ($table_name, $primary_key_name, $primary_key_id, $user_id, &$assign)

	Description

	Generates the VALUES (...) part of an INSERT / REPLACE SQL query.
	Also does pre-processing of the fields to be inserted, including
	moving of images and files to their archived locations on disk,
	and marshalling data from fields such as multiple-selects to
	be ready to be inserted in the DB

	Arguments

	 
	$table_name: the name of the table to insert
	$primary_key_name: Column name of the primary key - ID, resume_id, post_id, profile_id
	object_id: The value for the primary key, eg 726
	$user_id: The ID if the user which owns the record
	&$assign: This is an associative array of the fields which are NOT on
	the dynamic form, but exist in the table. For example, resume_table
	would have somthing like this:

	$assign = array(	
			'list_on_web' => 'Y',
			'resume_date' => gmdate("Y-m-d H:i:s"),
			'user_id' => $user_id,
			'approved' => $approved,
			'status' => 'ACT',
			'expired' => 'N'
		);

	*/

	function get_sql_insert_values ($table_name, $primary_key_name, $primary_key_id, $user_id, &$assign) {

		// static fields (Defined in include/schema_functions.php)


		if (is_array($assign)) {
			$fields = &JB_schema_get_static_fields($this->form_id, JB_DB_MAP);

			foreach ($fields as $field) {
				if ($field['field_type']=='ID') { // omit id because all ID fields are auto_increment
					continue;
				}		
				if (isset($assign[$field['field_id']])) {
					$str .= "$comma '".JB_escape_sql($assign[$field['field_id']])."' ";
				} else {
					$str .= "$comma '".JB_escape_sql($_REQUEST[$field['field_id']])."' ";
				}
				$comma = ',';
			}
		}
		

		foreach ($this->tag_to_field_id as $tag=>$field) {
			if (!is_numeric($field['field_id']) || ($field['field_type'] == 'BLANK') || ($field['field_type'] =='SEPERATOR') || ($field['field_type'] =='NOTE')) {
				continue;
			}

			switch ($field['field_type']) {

				case 'GMAP':

					$str .= ",  '".JB_escape_sql($_REQUEST[$field['field_id'].'_lat'])."', '".JB_escape_sql($_REQUEST[$field['field_id'].'_lng'])."', '".JB_escape_sql($_REQUEST[$field['field_id'].'_zoom'])."' ";

					break;

				case "IMAGE":
					if ($_FILES[$field['field_id']]['name'] !='') {

						// delete the old image
						if ($primary_key_id != '') {
							JB_delete_image_from_field_id($table_name, $primary_key_name, $primary_key_id, $field['field_id']);
						}

						$file_name = JB_saveImage($field['field_id'], $user_id);
						$_REQUEST[$field['field_id']] = $file_name;
						
						$str .= ", '".JB_escape_sql($file_name)."' ";
					}
					break;
				case "FILE":
					if ($_FILES[$field['field_id']]['name'] !='') {
					
						
						// delete the old file
						if ($primary_key_id != '') {
							JB_delete_file_from_field_id($table_name, $primary_key_name, $primary_key_id, $field['field_id']);
						}

						$file_name = JB_saveFile($field['field_id'], $user_id); // return the new file name
						$_REQUEST[$field['field_id']] = $file_name;
						
						$str .= ", '".JB_escape_sql($file_name)."' ";
						
					}
					break;
				case "DATE":
					$day = $_REQUEST[$field['field_id']."d"];
					$month = $_REQUEST[$field['field_id']."m"];
					$year = $_REQUEST[$field['field_id']."y"];
					
					$temp_date = $year."-".$month."-".$day;
					if ($temp_time = strtotime($temp_date.' 00:00:00')) {
						// convert the date timezone to GMT
						$temp_time = ($temp_time - (3600 * JB_GMT_DIF));
						$temp_date = gmdate('Y-m-d H:i:s', $temp_time);
					}

					
					$str .= ", '".JB_escape_sql($temp_date)."' ";

					break;
				case "DATE_CAL":
					// Convert SCW Date to ISO Date format before saving in the DB

					$temp_date = trim($_REQUEST[$field['field_id']]);
					if (strlen($temp_date) > 0) {
						$temp_date = JB_SCWDate_to_ISODate($temp_date);
						
						if ($temp_time = strtotime($temp_date.' 23:59:59')) {
							// convert the date timezone to GMT
							$temp_time = ($temp_time - (3600 * JB_GMT_DIF));
							$temp_date = gmdate('Y-m-d H:i:s', $temp_time);	
						} else {
							$temp_date = '';
						}
					}
					
					$str .= ", '".JB_escape_sql($temp_date)."' ";

					break;
				case "CHECK":
				case "MSELECT":
					// the following fields are received as array()
			
					$tmp=''; $comma='';
					$selected_codes = array();
					$selected_codes = $_REQUEST[$field['field_id']]; // the field comes in as an array
					for ($i =0; $i < sizeof($selected_codes); $i++) {
						if ($i > 0) {$comma = ',';}
							$tmp .= $comma.$selected_codes[$i];
					}
					$str .= ", '".JB_escape_sql($tmp)."' ";
					break;
				case "SKILL_MATRIX":
					JB_save_skill_matrix_data($field['field_id'], $primary_key_id, $user_id);
					$str .= ", '".JB_escape_sql($_REQUEST[$field['field_id']])."' ";
					break;
				case 'TEXT':
				case 'EDITOR':
					$str .= ", '".JB_escape_sql(($_REQUEST[$field['field_id']]))."' ";
					break;
				case "URL":
					$str .= ", '".JB_escape_sql(strip_tags($_REQUEST[$field['field_id']]))."' ";
					break;
				case "INTEGER":
				case "NUMERIC":
				case "CURRENCY":
					// fetch only the numerical part
					preg_match('/[\+-]?([0-9,]+(\.)?(\d+)?)/', $_REQUEST[$field['field_id']], $m);
					$m[1] = str_replace(',', '', $m[1]); // remove commas
					if ($m[1]==='0') { // string zero
						$str .= ", NULL ";
					} elseif(!$m[1]) { // empty
						$str .= ", '' "; 
					} else { // its a number
						$str .= ", '".JB_escape_sql($m[1])."' ";
					}

				
					break;
				case "YOUTUBE":
					// extract the video ID form the URL
					// eg. http://www.youtube.com/watch?v=iuTNdHadwbk - extract iuTNdHadwbk
					if (preg_match('/watch\?v=([a-z0-9\-_]+)/i', $_REQUEST[$field['field_id']], $m)) {
						$str .= ", '".JB_escape_sql($m[1])."' ";
					} elseif (preg_match('/src="http:\/\/www\.youtube\.com\/v\/([a-z0-9\-_]+)/i', $_REQUEST[$field['field_id']], $m)) {
						$str .= ", '".JB_escape_sql($m[1])."' ";
					} elseif (preg_match('#http:\/\/youtu\.be\/([a-z0-9\-_]+)\/?#i', $_REQUEST[$field['field_id']], $m)) {
						$str .= ", '".JB_escape_sql($m[1])."' ";
					} else {
						preg_match('/([a-z0-9\-_]+)/i', $_REQUEST[$field['field_id']], $m);
						$str .= ", '".JB_escape_sql($m[1])."' ";
					}
					break;
				
				default:
					$custom_sql = false;
					// Custom Fields: your plugin would have to generate $custom_sql string like the one after the else { starement
					JBPLUG_do_callback('append_sql_insert_values', $custom_sql, $field, $table_name, $primary_key_name, $primary_key_id, $user_id);
					if ($custom_sql !== false) {
						$str .= $custom_sql;
					} else {
						$str .= ", '".JB_escape_sql($_REQUEST[$field['field_id']])."' ";
					}
					
					break;
			}
		
		}
		return $str;

	}



	/*
	###############################################################

	Method 

	get_sql_update_values ($table_name, $primary_key_name, $primary_key_id, $user_id, &$assign)
	
	Description

	Generates the Update part of the $sql for updating the data.
	eg. UPDATE posts_table SET `12`='hello', `13`='world' WHERE post_id=1
	Will generate and return: `12`='hello', `13`='world'
	Also saves / deletes images, and updates the skills matrix fields.

	Arguments

	$table_name - name of the table, eg posts_table

	$primary_key_name - the primary key of the table, eg. 'post_id' - Tip: 
	a function to work out the primary key of a table:
	use: $primary_key = JB_get_table_id_column($form_id);

	$primary_key_id - the id of the record that is being inserted. This may
	be null if the record is new.

	$user_id - the user id of the owner of the record

	$assign - This is an associative array of the fields which are NOT on
	the dynamic form, but exist in the table. For example, posts_table
	would have somthing like this:

	$assign = array(	
			'resume_date' => gmdate("Y-m-d H:i:s"),
			'anon' => jb_alpha_numeric($_REQUEST['anon']),
			'approved' => $approved
		);

	*/

	function get_sql_update_values ($table_name, $primary_key_name, $primary_key_id, $user_id, &$assign) {
		
		$fields = &JB_schema_get_static_fields($this->form_id, JB_DB_MAP);

		foreach ($fields as $field) {

			if ($field['field_type']=='ID') {
				continue; // do not update the id
			}

			if (isset($assign[$field['field_id']])) {
			
				$str .= "$comma `".$field['field_id']."` = '".JB_escape_sql($assign[$field['field_id']])."' ";
				$comma = ',';
			}
		}

		foreach ($this->tag_to_field_id as $tag=>$field) {
			if (!is_numeric($field['field_id']) || ($field['field_type'] == 'BLANK') || ($field['field_type'] =='SEPERATOR') || ($field['field_type'] =='NOTE')) {
				continue;
			}
			$tmp = ''; $comma = '';
			switch ($field['field_type']) {

				case 'GMAP':

					$str .= ", `".$field['field_id']."_lat` = '".JB_escape_sql($_REQUEST[$field['field_id'].'_lat'])."', `".$field['field_id']."_lng` = '".JB_escape_sql($_REQUEST[$field['field_id'].'_lng'])."', `".$field['field_id']."` = '".JB_escape_sql($_REQUEST[$field['field_id'].'_zoom'])."' ";

					break;

				case 'IMAGE':
					if ($_FILES[$field['field_id']]['name'] !='') {
						
						$_REQUEST[$field['field_id']] = $file_name;
						// delete the old image
						if ($primary_key_id != '') { 
							JB_delete_image_from_field_id($table_name, $primary_key_name, $primary_key_id, $field['field_id']);
						}
						$file_name = JB_saveImage($field['field_id'], $user_id);
						$str .= ", `".$field['field_id']."` = '".JB_escape_sql($file_name)."' ";
					}
					break;
				case 'FILE':
					if ($_FILES[$field['field_id']]['name'] !='') {
						// delete the old file
						if ($primary_key_id != '') {
							JB_delete_file_from_field_id($table_name, $primary_key_name, $primary_key_id, $field['field_id']);
						}
						$file_name = JB_saveFile($field['field_id'], $user_id);
						
						$str .= ", `".$field['field_id']."` = '".JB_escape_sql($file_name)."' ";
					}
					break;
				case 'DATE':
					
					$day = $_REQUEST[$field['field_id']."d"];
					$month = $_REQUEST[$field['field_id']."m"];
					$year = $_REQUEST[$field['field_id']."y"];
					
					$temp_date = $year."-".$month."-".$day;
					if ($temp_time = strtotime($temp_date.' 00:00:00')) {
						// convert the date timezone to GMT
						$temp_time = ($temp_time - (3600 * JB_GMT_DIF));
						$temp_date = gmdate('Y-m-d H:i:s', $temp_time);	
					}

					$str .= ", `".$field['field_id']."` = '".JB_escape_sql($temp_date)."' ";
					break;
				case 'DATE_CAL':
					
					// Convert SCW Date to ISO Date format before saving in the DB
					$temp_date = JB_SCWDate_to_ISODate($_REQUEST[$field['field_id']]);
					
					$temp_date = trim($_REQUEST[$field['field_id']]);
					if (strlen($temp_date) > 0) {
						$temp_date = JB_SCWDate_to_ISODate($temp_date);
						
						if ($temp_time = strtotime($temp_date.' 23:59:59')) {
							// convert the date timezone to GMT
							$temp_time = ($temp_time - (3600 * JB_GMT_DIF));
							$temp_date = gmdate('Y-m-d H:i:s', $temp_time);	
						} else {
							$temp_date = '';
						}
					}

					$str .= ", `".JB_escape_sql($field['field_id'])."` = '".JB_escape_sql($temp_date)."' ";
					break;
				case 'CHECK':
					$comma=''; $tmp='';
					$selected_codes = array();
					$selected_codes = $_REQUEST[$field['field_id']]; // the field comes in as an array
					for ($i =0; $i < sizeof($selected_codes); $i++) {
						if ($i > 0) {$comma = ',';}
							$tmp .= $comma.$selected_codes[$i]."";
					}

					$_REQUEST[$field['field_id']] = $tmp;
					$str .= ", `".$field['field_id']."` = '".JB_escape_sql($_REQUEST[$field['field_id']])."' ";
					break;

				case 'MSELECT':
					$tmp=''; $comma='';
					$selected_codes = array();
					$selected_codes = $_REQUEST[$field['field_id']]; // the field comes in as an array
					for ($i =0; $i < sizeof($selected_codes); $i++) {
						if ($i > 0) {$comma = ',';}
							$tmp .= $comma.$selected_codes[$i]."";
					}

					
					$str .= ", `".$field['field_id']."` = '".JB_escape_sql($tmp)."' ";
					break;
				case 'SKILL_MATRIX':
					JB_save_skill_matrix_data($field['field_id'], $primary_key_id, $user_id);
					$str .= ", `".$field['field_id']."` = '".JB_escape_sql($_REQUEST[$field['field_id']])."' ";
					break;
				case 'TEXT':
				case 'EDITOR':
					$str .= ", `".$field['field_id']."` = '".JB_escape_sql(($_REQUEST[$field['field_id']]))."' ";
					break;
				case 'URL':
					$str .= ", `".$field['field_id']."` = '".JB_escape_sql(strip_tags($_REQUEST[$field['field_id']]))."' ";
					break;
				case 'NUMERIC':
				case 'CURRENCY':
				case 'INTEGER':
					// featch only the numerical part
					preg_match('/[\+-]?(\d+(\.)?(\d+)?)/', $_REQUEST[$field['field_id']], $m);
					
					if ($m[1]==='0') { // string zero
						$str .= ", `".$field['field_id']."` = '0' ";
					} elseif (!$m[1]) { // empty
						$str .= ", `".$field['field_id']."` = NULL ";
					} else {
						$str .= ", `".$field['field_id']."` = '".JB_escape_sql($m[1])."' ";
					}

					
					
					break;
				case 'YOUTUBE':
					// extract the video ID form the URL
					// eg. http://www.youtube.com/watch?v=iuTNdHadwbk - extract iuTNdHadwbk
					if (preg_match('/watch\?v=([a-z0-9\-_]+)/i', $_REQUEST[$field['field_id']], $m)) {
						$str .= ", `".$field['field_id']."` = '".JB_escape_sql($m[1])."' ";
					} elseif (preg_match('/src="http:\/\/www\.youtube\.com\/v\/([a-z0-9\-_]+)/i', $_REQUEST[$field['field_id']], $m)) {
						$str .= ", `".$field['field_id']."` = '".JB_escape_sql($m[1])."' ";
					} elseif (preg_match('#http:\/\/youtu\.be\/([a-z0-9\-_]+)\/?#i', $_REQUEST[$field['field_id']], $m)) {
						$str .= ", `".$field['field_id']."` = '".JB_escape_sql($m[1])."' ";
					} else {
						preg_match('/([a-z0-9\-_]+)/i', $_REQUEST[$field['field_id']], $m);
						$str .= ", `".$field['field_id']."` = '".JB_escape_sql($m[1])."' ";
					}
					break;
				
				default:
					$custom_sql = false;
					// your plugin would have to generate $custom_sql string like the one after the else { starement
					JBPLUG_do_callback('append_sql_update_values', $custom_sql, $field, $table_name, $primary_key_name, $primary_key_id, $user_id);
					if ($custom_sql !== false) {
						$str .= $custom_sql;
					} else {
						$str .= ", `".$field['field_id']."` = '".JB_escape_sql($_REQUEST[$field['field_id']])."' ";
					}
					
					break;

			}
				
		}
		
		return $str;

	}


}