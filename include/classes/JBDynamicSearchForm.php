<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
class JBDynamicSearchForm {

	var $form_id;
	var $q_string; // the query sting part of URL for search
	var $tag_to_search;
	

	function JBDynamicSearchForm($form_id) {

		$this->form_id = $form_id;
		$this->tag_to_search = $this->tag_to_search_init();
		$this->q_string = $this->generate_q_string();
		
	}

	function &get_tag_to_search() {
		return $this->tag_to_search;
	}


	function &tag_to_search_init () {
		global $tag_to_search;

		$cache_key = 'tag_to_search_'.$this->form_id.'_'.$_SESSION['LANG'];

		

		$tag_to_search = JB_cache_get($cache_key); // tag_to_search_1_EN


		if (is_array($tag_to_search)) {
			return $tag_to_search;
		}

		$tag_to_search = array();

		$sql = "SeLeCT *, t2.field_label AS NAME FROM `form_fields` AS t1, `form_field_translations` AS t2 where t1.field_id=t2.field_id AND t1.form_id='".JB_escape_sql($this->form_id)."' AND is_in_search ='Y' AND t2.lang='".JB_escape_sql($_SESSION['LANG'])."' ORDER BY search_sort_order  ";

		$result = JB_mysql_query($sql) or die (mysql_error());
		# do a query for each field
		while ($fields = mysql_fetch_array($result, MYSQL_ASSOC)) {

			
			$tag_to_search[$fields['template_tag']]['field_id'] = $fields['field_id'];
			$tag_to_search[$fields['template_tag']]['field_type'] = $fields['field_type'];
			$tag_to_search[$fields['template_tag']]['field_label'] = $fields['NAME'];
			$tag_to_search[$fields['template_tag']]['field_init'] = $fields['field_init'];
			$tag_to_search[$fields['template_tag']]['category_init_id'] = $fields['category_init_id'];
			$tag_to_search[$fields['template_tag']]['field_height'] = $fields['field_height'];
			$tag_to_search[$fields['template_tag']]['is_cat_multiple'] = $fields['is_cat_multiple'];
			$tag_to_search[$fields['template_tag']]['cat_multiple_rows'] = $fields['cat_multiple_rows'];
			$tag_to_search[$fields['template_tag']]['multiple_sel_all'] = $fields['multiple_sel_all'];

			if ($fields['field_type'] == 'SKILL_MATRIX') {
				// skill matrix exists in the form
				
				$tag_to_search['smx_exists'] = true;
				
			}
		}

		JBPLUG_do_callback('tag_to_search_init', $tag_to_search, $this->form_id);

		jb_cache_add($cache_key, $tag_to_search);

		return $tag_to_search;

	}


	function add_tag_to_search($template_tag, $field_attributes=array()) {

		foreach ($field_attributes as $key => $val) {
			$this->tag_to_search[$template_tag][$key] = $val;
		}
	}

	
	/*
	 The generate_q_string() function builds a query string consisting
	 of all the CGI parameters that were passed after submitting a search form.
	 The function returns a query string which is appended to the end of URLs
	 so that the fields in the search form are preserved for the next screen.
	 The function goes through all the search fields in the $tag_to_search
	 structure and builds the query string from the data received in the
	 $_REQUEST array. First, &amp;action=search is added to let the job
	 board know to execute a search, and then the remaining parameters are
	 appened.

	 Notes:
	 - & characters are encoded html entities &amp;)

	 - The function caches the query string in a static var so that it does
	 not need to re-build the string with each call.

	*/

	function generate_q_string() {

		if ($_REQUEST['action']==false) { // no search executed
			return false;
		}

		$this->q_string = "&action=search";

		foreach ($this->tag_to_search as $key => $val) {
			$field_id = $val['field_id'];
			if (is_array($_REQUEST[$field_id])) {
				// multiple selected fields, checkboxes
				$arr_str = '';
				foreach ($_REQUEST[$field_id] as $elem) {
					$arr_str .= '&'.$field_id.urlencode('[]').'='.urlencode($elem);
				}
				$this->q_string .= $arr_str;
				

			} elseif ($val['field_type'] == 'DATE') {

				if ($_REQUEST[$field_id.'d']!='') {
					$this->q_string .= '&'.$field_id.'d='.urlencode($_REQUEST[$field_id].'d');
				}
				if ($_REQUEST[$field_id.'m']!='') {
					$this->q_string .= '&'.$field_id.'m='.urlencode($_REQUEST[$field_id].'m');
				}
				if ($_REQUEST[$field_id.'y']!='') {
					$this->q_string .= '&'.$field_id.'y='.urlencode($_REQUEST[$field_id].'y');
				}

			} elseif ($_REQUEST[$field_id]!='') {
				// fields such as text fields
				$this->q_string .= ("&".$field_id."=".urlencode(JB_html_ent_to_utf8($_REQUEST[$field_id])));
				
			}
		}
		JBPLUG_do_callback('generate_q_string', $this->q_string, $this->form_id);

		$this->q_string = htmlentities($this->q_string);

		return $this->q_string;

	}

	function &get_q_string() {
		return $this->q_string;
	}

	function display_dynamic_search_form($NO_COLS=2, $search_form_mode=null) {
		global $label;

	
		# HTML output for this function comes from SearchFormMarkup Class
		# include/themes/default/JBSearchFormMarkup.php
		# Any HTML customizations should be done there.
		# Please copy this class in to your custom theme directory, and
		# customize form there


		$SFM = &JB_get_SearchFormMarkupObject($this->form_id, $NO_COLS); // load the ListMarkup Class

		if (sizeof($this->tag_to_search)==0) return false;

		if ($search_form_mode=='') {
			$SFM->form_open();

		}
		$SFM->container_open();	

		$i=0;
		
		foreach ($this->tag_to_search as $key => $val) {

			if (method_exists($SFM, 'set_field_row')) {
				$SFM->set_field_row($val);
			}

			if ($key=='smx_exists') { // ignore this key; smx_exists = skill matrix exists
				continue;
			}
			
			if ($i == 0 ){
				$SFM->row_open();
			}
			$SFM->field_label_open($val['field_label']);
			$SFM->field_label($val['field_label']);
			$SFM->field_label_close();

			$SFM->field_open();
			

			$key_id = $val['field_id'];

			JBPLUG_do_callback('search_form_before_field', $this->tag_to_search, $key);
			switch ($val['field_type']) {
				
				case "TEXT":
				case "URL":
				case "NUMERIC":
				case "CURRENCY":
				case "INTEGER":
					$SFM->text_field($key_id, stripslashes($_REQUEST[$key_id]));
					break;
				case "IMAGE":
					if ($_REQUEST[$key_id]!='') {
						$checked = ' checked ';
					} else {
						$checked = '';
					}
					$SFM->single_checkbox_field($label['only_with_image'], $key_id, $checked);
					
					break;
				case "FILE":
					if ($_REQUEST[$key_id]!='') {
						$checked = ' checked ';
					} else {
						$checked = '';
					}
					$SFM->single_checkbox_field($label['only_with_file'], $key_id, $checked);
					
					break;
				case "YOUTUBE":
					if ($_REQUEST[$key_id]!='') {
						$checked = ' checked ';
					} else {
						$checked = '';
					}
					$SFM->single_checkbox_field($label['only_with_youtube'], $key_id, $checked);
				
				case "SEPERATOR":
					break;
				case "EDITOR":
					$SFM->text_field($key_id, stripslashes($_REQUEST[$key_id]));
					
					break;
				case "CATEGORY":
				
					if ($val['is_cat_multiple']=='Y') {
						$cat_mult = ' multiple ';
						$cat_rows = $val['cat_multiple_rows'];
						$cat_arr = "[]";
					} else {
						$cat_mult = '';
						$cat_rows = '';
						$cat_arr = '';
					}

					$SFM->category_select_field_open($cat_mult, $cat_rows, $val['field_id'], $cat_arr);
					
					if ($cat_mult=='') { 
						$SFM->category_first_option(); 
					} 
					
					if ($val['multiple_sel_all']=='Y') {

						if (!is_array($_REQUEST[$key_id])) {
							if ($_REQUEST[$key_id]=='all') { 
								$selected = " selected ";
							}
						} else {
							 if (in_array('all', $_REQUEST[$key_id])) { 
								 $selected = " selected ";
							 }
						}
						$SFM->category_first_option_all($selected);
						$selected = '';
						
					} 
					// $SFM - use the JBSearchFormMarkup class to render the search form
					JB_category_option_list($val['category_init_id'], $_REQUEST[$key_id], $SFM);

					$SFM->category_select_field_close();
					
					break;
				case "DATE":
					
					$day =  $_REQUEST[$key_id."d"];
					$month =  $_REQUEST[$key_id."m"];
					$year =  $_REQUEST[$key_id."y"];
					// using the standard widget
					echo JB_form_date_field ($val['field_id'], $day, $month, $year, 'search_date_style');
					break;
				case "DATE_CAL":
					$SFM->scw_date_field($key_id);
				case "BLANK":
					$SFM->blank_field(); // &nbsp;
					break;
				case "RADIO":
				

					if ($_SESSION['LANG'] !='') {
						$sql = "SELECT * FROM `codes_translations` WHERE `field_id`='".JB_escape_sql($key_id)."' and lang='".JB_escape_sql($_SESSION['LANG'])."' ";
						
					} else {
						$sql = "SELECT * FROM `codes` WHERE `field_id`='".JB_escape_sql($key_id)."' ";
					}

					$result = JB_mysql_query ($sql) or die (mysql_error());
					while ($row  = mysql_fetch_array($result, MYSQL_ASSOC)) {
						if ($row['code']== $_REQUEST[$key_id] ) {
							$checked = ' checked ';
						} else {
							$checked = '';
						}
						$SFM->radio_button_field($key_id, $row['code'], $row['description'], $checked);
						
					}
					break;
				case "CHECK":
					if ($_SESSION['LANG'] !='') {
						$sql = "SELECT * FROM `codes_translations` WHERE `field_id`='".JB_escape_sql($key_id)."' and lang='".JB_escape_sql($_SESSION['LANG'])."' ";
						
					} else {
						$sql = "SELECT * FROM `codes` WHERE `field_id`='".JB_escape_sql($key_id)."' ";
					}
					$result = JB_mysql_query ($sql) or die (mysql_error());
					
			
					while ($row  = mysql_fetch_array($result, MYSQL_ASSOC)) {
						if ($row['code']== $_REQUEST[$val['field_id'].'-'.$row['code']] ) {
							$checked = ' checked ';
						} else {
							$checked = '';
						}
						$SFM->checkbox_field($row['description'], $key_id, $checked, $row['code']);
						
					}
					break;
				case "SELECT":
					if ($_SESSION['LANG'] !='') {
						$sql = "SELECT * FROM `codes_translations` WHERE `field_id`='".JB_escape_sql($key_id)."' and lang='".JB_escape_sql($_SESSION['LANG'])."' ";
						
					} else {
						$sql = "SELECT * FROM `codes` WHERE `field_id`='".JB_escape_sql($key_id)."' ";
					}
					$result = JB_mysql_query ($sql) or die (mysql_error());

					$SFM->single_select_open($val['field_height'], $key_id);
					$SFM->single_select_first_option();

					while ($row  = mysql_fetch_array($result, MYSQL_ASSOC)) {
						$sel ='';
						if (($row['code']==$_REQUEST[$val['field_id']])) {
							$sel = " selected ";
						} else {
							$sel = "";
						}

						$SFM->single_select_option($row['code'], $row['description'], $sel);
						
													
					}
					$SFM->single_select_close();
					break;


				case "MSELECT":
					if ($_SESSION['LANG'] !='') {
						$sql = "SELECT * FROM `codes_translations` WHERE `field_id`='".JB_escape_sql($key_id)."' and lang='".JB_escape_sql($_SESSION['LANG'])."' ";
						
					} else {
						$sql = "SELECT * FROM `codes` WHERE `field_id`='".JB_escape_sql($key_id)."' ";
					}
					$result = JB_mysql_query ($sql) or die (mysql_error());

					$SFM->mselect_open($key_id, $val['field_height']);
				
					while ($row  = mysql_fetch_array($result, MYSQL_ASSOC)) {

						if ($_REQUEST[$val['field_id']]) {
							$sel = '';
							if (in_array($row['code'], $_REQUEST[$val['field_id']])) {
								$sel = " selected ";
							} else {
								$sel = "";
							}
						}
						$SFM->mselect_option($row['code'], $row['description'], $sel);	
					}
					$SFM->mselect_close();
					break;

				case "SKILL_MATRIX":
					
					$SFM->skill_matrix($key_id, stripslashes($_REQUEST[$key_id.'name']));

					break;
				case 'smx_exists': // skill matrix exists
					break;

				default:
					JBPLUG_do_callback('display_custom_search_field', $this->tag_to_search, $key); // // Your funtion implemented in the module should change $val['field_type'] to 'PLUGIN' display_custom_search_field

					if ($this->tag_to_search['field_type']!='PLUGIN') {
						$SFM->text_field($key_id, stripslashes($_REQUEST[$key_id]));
					}
					break;
			}

			$SFM->field_close();


			$i++;
			if ($i >= $NO_COLS ){
				$SFM->row_close();
				$i=0;
			} 
			

		}

		if (($i> 0) && ($i < $NO_COLS )) {
			while (($i < $NO_COLS )) {
				// ouput empty cells

				$SFM->field_label_open();
				$SFM->blank_field();
				$SFM->field_label_close();
				$SFM->blank_field_open();
				$SFM->blank_field();
				$SFM->blank_field_close();
				
				
				$i++;
			}
			$SFM->row_close();

		} 

		if ($search_form_mode=='') {
			$SFM->form_button(); // echo the search button line
		}
		
		$SFM->container_close(); // </TABLE>

		if ($search_form_mode=='') {
			$SFM->form_close(); // </FORM>
		}


	}


	##########################################

	/*

	function generate_search_sql($_SEARCH_INPUT=null)

	Generates the WHERE part of the SQL query for the search forms.
	The form is generated using the structure obtained form
	DynamicForm->get_tag_to_search() - likewise, the WHERE part of
	the SQL query is generated from that structure, and the 
	$_SEARCH_INPUT is used as the search parameters.

	If no $_SEARCH_INPUT was passed, the $_SEARCH_INPUT will
	be taken from $_REQUEST


	*/

	function generate_search_sql($_SEARCH_INPUT=null) {


		global $label; // from the languages file.

	
		if (!is_array($_SEARCH_INPUT)) {
			$_SEARCH_INPUT = $_REQUEST; // get the search input that was posted
		}

		JBPLUG_do_callback('generate_search_sql_before', $where_sql, $this->form_id, $_SEARCH_INPUT);
		if ($where_sql) return $where_sql; // $where_sql was generated by a plugin
		
		 if ($_SEARCH_INPUT['action'] == 'search') {
			 
			 foreach ($this->tag_to_search as $key => $val) {
				 $name = $this->tag_to_search[$key]['field_id'];

				 switch ($this->tag_to_search[$key]['field_type']) {

					case 'IMAGE':
					case 'FILE':
					case 'YOUTUBE':
						if ($_SEARCH_INPUT[$name]!='')
							$where_sql .= " AND (`".$name."`) != '' ";

						break;

					case 'SELECT':

						if ($_SEARCH_INPUT[$name]!='')
							$where_sql .= "  AND (	`".$name."` = '".JB_escape_sql($_SEARCH_INPUT[$name])."') ";
						break;


					case 'CHECK':
						$tmp=''; $comma='';
						## process all possible options
						$sql = "SELECT * from codes where field_id='".JB_escape_sql($name)."' ";
						$code_result = JB_mysql_query ($sql) or die (mysql_error());

	
						$i = 0;
						while ($code = mysql_fetch_array($code_result, MYSQL_ASSOC)) {
							$val = $code['field_id']."-".$code['code'];
							if ($_SEARCH_INPUT[$val] != '') {
								if ($i > 0) {$comma = 'OR';}
								$tmp .= $comma." `$name` LIKE '%".JB_escape_sql($code['code'])."%' ";
								$i++;
							}

						}
					
						if ($i > 0)
						$where_sql .= "  AND (".$tmp.") ";

						break;

					case 'MSELECT':
						$tmp=''; $comma='';
						$selected_codes = array();
						$selected_codes = $_SEARCH_INPUT[$name];
						for ($i =0; $i < sizeof($selected_codes); $i++) {
							if ($i > 0) {$comma = 'OR';}
							$tmp .= $comma." `$name` LIKE '%".JB_escape_sql($selected_codes[$i])."%' ";
						}

						if ($i > 0)
							$where_sql .= "  AND (".$tmp.") ";

						break;


					case 'CATEGORY':

						$where_range = ''; $range_or='';

						//$_SEARCH_INPUT[$name] can either be an array of numbers & string 'all', 
						// or a scalar string all or scalar number

						if (!is_array($_SEARCH_INPUT[$name]) && trim($_SEARCH_INPUT[$name])=='') {
							break;
						}

						// init the $search-set & $cat_ids_str as strings
						// similar to: JB_search_category_tree_for_posts()
						$search_set = '';
						if (is_array($_SEARCH_INPUT[$name])) { // if the category is a multiple select!
							foreach ($_SEARCH_INPUT[$name] as $key=>$val) {
								if (!is_numeric($val) && ($val != 'all')) { // validate
									break;
								}
							}
							$cat_ids_str = implode(',',$_SEARCH_INPUT[$name]);
								
						} else {
							$cat_ids_str = (int) $_SEARCH_INPUT[$name];
						}
						
						if (strpos($cat_ids_str, 'all') !== false) {	// return all categories
							break; // no need to filter
						}

						$sql = "SELECT search_set FROM categories WHERE category_id IN(".jb_escape_sql($cat_ids_str).") ";

						$result2 = JB_mysql_query ($sql) or die (mysql_error());
						$search_set = $cat_ids_str; // search_set does not include the current category	
						while ($row2 = mysql_fetch_row($result2)) {
							$search_set .= ','.$row2[0];	
						}

						// optimize the search set: remove duplicates & range it

						$set = explode(',', $search_set);
		
						sort($set, SORT_NUMERIC);
						$prev='';
						
						// this removes duplicates
						foreach ($set as $key=>$val) {
							if ($val==$prev) {
								unset($set[$key]);
							}
							$prev = $val;
						}
						// sort again because after removing
						// duplicates the keys were like swiss cheeze
						sort($set, SORT_NUMERIC);
				
					
						// Now this is the fun part!
						// The code below summarizes the $set array
						// which is a list of numbers in to rangers


						for ($i=0; $i < sizeof ($set); $i++) {
							$start = $set[$i]; // 6
							//$end = $set[$i];
							for ($j=$i+1; $j < sizeof ($set) ; $j++) {
								// advance the array index $j if the sequnce 
								// is +1
								
								if (($set[$j-1]) != $set[$j]-1) { // is it in sequence
									$end = $set[$j-1];
									
									break;
								}
								
								$i++;
								$end = $set[$i];
								
							}
							if ($end=='') {
								$end = $set[$i];
							}
							if (($start != $end) && ($end != '')) {
								$where_range .= " $range_or  ((`".$name."` >= $start) AND (`".$name."` <= $end)) ";
							} elseif ($start!='') {
								$where_range .= " $range_or  (`".$name."` = $start ) ";
							}
							$start='';$end='';
							$range_or = "OR";
						}


						$where_sql .= " AND ($where_range) ";

						break;

					case 'SKILL_MATRIX':

						if (trim($_SEARCH_INPUT[$name.'name']) != '') {

							if (!is_numeric($_SEARCH_INPUT[$name.'rating'])) {
								$_SEARCH_INPUT[$name.'rating'] = '0';
							}
							if (!is_numeric($_SEARCH_INPUT[$name.'years'])) {
								$_SEARCH_INPUT[$name.'years'] = '0';
							}

							$where_sql .= " AND t2.name LIKE '".JB_escape_sql(trim($_SEARCH_INPUT[$name.'name']))."' AND t2.years >= ".JB_escape_sql($_SEARCH_INPUT[$name.'years'])." AND t2.rating >= ".JB_escape_sql($_SEARCH_INPUT[$name.'rating'])." ";

						}

						break;

					case 'DATE':
						$day =  $_REQUEST[$name."d"];
						$month =  $_REQUEST[$name."m"];
						$year =  $_REQUEST[$name."y"];
						if ($year!='' && $month!='' && $day!='') {
							// convert to ISO format
							$value = "$year-$month-$day";
							$where_sql .= " AND (`$name` >= '".JB_escape_sql($value)."') ";

						}
						break;
					case 'DATE_CAL':
						
						$value = $_SEARCH_INPUT[$name];
						if ($value!='') {
							// convert to ISO format before putting it through a search
							$value = JB_SCWDate_to_ISODate($value);
							$where_sql .= " AND (`$name` >= '".JB_escape_sql($value)." 00:00:00') ";
						}
					break;

					case 'TIME':
						
						$value= $_SEARCH_INPUT[$name];
						$time = strtotime($value); // gmt

						$time = $time - (3600 * JB_GMT_DIF);
						$later_time = $time + (3600 * 24); // 24 hours later

						$where_sql .= " AND ( 
												(
													`$name` > '".gmdate("Y-m-d H:i:s", $time)."'
												) 
												AND
												(
													`$name` < '".gmdate("Y-m-d H:i:s", $later_time)."'
												)
											)		
														
											 ";
					break;

					default:
						$custom_sql = '';
						$value= $_SEARCH_INPUT[$name];
						JBPLUG_do_callback('generate_search_sql', $custom_sql, $this->tag_to_search[$key], $value);
						if ($custom_sql != '') {
							$where_sql .= $custom_sql;
						} else {
							if ($value!='') {
								$list = preg_split ("/[\s,]+/", $value);
								
								for ($i=1; $i < sizeof($list); $i++) {
									$or .=" AND (`$name` like '%".JB_escape_sql($list[$i])."%')  ";
								}
								$where_sql .= " AND ((`$name` like '%".JB_escape_sql($list[0])."%')  $or)";
							}
						}
						
						break;
					
				 } // end switch

			 } // end foreach
			
	   }// end serach

	   JBPLUG_do_callback('generate_search_sql_after', $where_sql, $this->form_id, $_SEARCH_INPUT);
	   
	   return $where_sql;


	}



}


?>