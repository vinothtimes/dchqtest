<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

require_once (dirname(__FILE__).'/code_functions.php');
require_once (dirname(__FILE__)."/lists.inc.php");
require_once (dirname(__FILE__).'/category.inc.php');


//global $post_tag_to_search;
//global $post_tag_to_field_id;


// Load the Posting form object - and instance of JBDynamicForms.php

$PostingForm = &JB_get_DynamicFormObject(1); 
$post_tag_to_search = $PostingForm->get_tag_to_search();
$post_tag_to_field_id = $PostingForm->get_tag_to_field_id();


#####################################################
# The following function builds there search query
# part of the sql string
# for searching posts by category
# Returns the SQL part of the string ready to be used
# in the WHERE part of the sql query

function JB_search_category_tree_for_posts($cat_id=false, $field_id=false) {

	if ($cat_id==false) {
		$cat_id = (int) $_REQUEST['cat'];
	}

	if ($field_id!=false) {
		$field_id_sql = "AND field_id='".jb_escape_sql($field_id)."'"; 
	}


	$sql = "select * FROM form_fields WHERE field_type='CATEGORY' AND form_id='1' $field_id_sql ";
	$result = JB_mysql_query ($sql) or die (mysql_error());

	$sql = "select search_set FROM categories WHERE category_id='".jb_escape_sql($cat_id)."' ";
	$result2 = JB_mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result2);
	
	// initialize $search_set
	if ($row['search_set']!='') {
		$search_set = $cat_id.','.$row['search_set'];
	} else {
		$search_set = $cat_id;
	}
	$i=0;

	
	if (mysql_num_rows($result) >0) {

		$or ='';
		while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

			
			$range_or = '';
			$set = array();
			if (strlen($search_set) < 1000) {
				// Use IN() operator
				$where_cat .= " $or `".$row['field_id']."` IN ($search_set) ";
				$or = 'OR';

			} else {
				// When there are thousands of categories, the search_set
				// could be huge.
				// So here attept to compress the $search_set
				// The following code will convert the $search_set, eg 1,2,3,4,6,7,8,9
				// in to ranges to make it smaller like this 1-4,5-9 and put it
				// in to an SQL query with comparison operators instead of
				// using the IN() operator

				$set = explode (',', $search_set);
				sort($set, SORT_NUMERIC);
				for ($i=0; $i < sizeof ($set); $i++) {
					$start = $set[$i]; 
					
					for ($j=$i+1; $j < sizeof ($set) ; $j++) {
						// advance the array index $j if the sequnce 
						// is +1	
						if (($set[$j-1]) != $set[$j]-1) { // is it in sequence?
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
						$where_range .= " $range_or  ((`".$row['field_id']."` >= $start) AND (`".$row['field_id']."` <= $end)) ";
					} elseif ($start!='') {
						$where_range .= " $range_or  (`".$row['field_id']."` = $start ) ";
					}
					$start='';$end='';
					$range_or = "OR";
				}
				
				$where_cat .= " $or $where_range  ";
				$where_range='';
				$or = 'OR';
		
			}
		}

	}

	if ($where_cat=='') {
		return " AND 1=2 ";
	}

	if ($search_set=='') {
		return "";
	}

	return " AND ($where_cat) ";
	

}

#####################################





function JB_post_tag_to_field_id_init () {
	
	global $label;
	global $post_tag_to_field_id;
	if ($post_tag_to_field_id = JB_cache_get('tag_to_field_id_1_'.$_SESSION['LANG'])) {
		return $post_tag_to_field_id;
	}
	
	$fields = JB_schema_get_fields(1);
	// the template tag becomes the key
	
	foreach ($fields as $field) {
		$post_tag_to_field_id[$field['template_tag']] = $field;
	}

	JBPLUG_do_callback('post_tag_to_field_id_init', $post_tag_to_field_id);

	JB_cache_set('tag_to_field_id_1_'.$_SESSION['LANG'], $post_tag_to_field_id);

	return $post_tag_to_field_id;



}

#####################################################################
function JB_load_post_data ($post_id) {
	
	
	$sql = "SELECT * FROM `posts_table` WHERE post_id='".jb_escape_sql($post_id)."' limit 1 ";
	$result = JB_mysql_query($sql) or die ($sql. mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	JBPLUG_do_callback('load_post_values', $row); // plugins can modify the values

	
	return $row;


}

################################################################



function JB_prefill_posting_form ($form_id, &$data, $user_id) {

	$sql = "SELECT * FROM posts_table where user_id='".jb_escape_sql($user_id)."' ORDER BY post_date DESC LIMIT 1 ";
	$result = JB_mysql_query($sql) or die(mysql_error());
	$post_row = mysql_fetch_array($result, MYSQL_ASSOC);

	$sql = "SELECT * FROM form_fields where form_id=".jb_escape_sql($form_id)." AND is_prefill='Y' ";
	$result = JB_mysql_query($sql) or die(mysql_error());

	while ($fields = mysql_fetch_array($result, MYSQL_ASSOC)) {

		if (trim($data[$fields['field_id']])=='') {
			$data[$fields['field_id']] = $post_row[$fields['field_id']];

		}
	}
	JBPLUG_do_callback('prefill_posting_form', $data);

}

####################################################
// Load in the prams form the POST / GET input
// deprecated
function JB_init_post_values(&$data) {

	$form_id=1;
	if (!is_numeric($_REQUEST['user_id'])) {
		$_REQUEST['user_id']=$_SESSION['JB_ID'];
	}
	JB_init_data_from_request($form_id, $data);
	JBPLUG_do_callback('init_post_values', $data);


}
####################################################
// deprecated, instead use this code:
// $PostingForm = &JB_get_DynamicFormObject(1); 
// $PostingForm->display_form($mode);
function JB_display_posting_form ($form_id=1, $mode, &$passed_data, $admin) {

	global $error;
	global $label;
	

	if ($passed_data == '' ) {
		JB_init_post_values($passed_data);
	}
	
	JB_template_posting_form($mode, $admin);

}




#####################################################################
# $list_mode can be PREMIUM, ALL, SAVED, ADMIN, EMPLOYER, BY_CATEGORY
# Optional 2nd argument can be WA, NA, EX (Waiting, Not Approved, Expired)
# This function builds the SQL query, executes the query
# and puts the result in to $result where it then lists the result
# in a table. The SQL query is built depending on what parameters
# were passed from $_REQUEST
# $_REQUEST['show_emp'] - if integer > 0 then list jobs by employer
# $_REQUEST['action'] - if == 'search' then perform a search
# $_REQUEST['ord'] - asc or desc (ascending or descending order)
# $_REQUEST['order_by'] - column name, default is post_date
#
# Returns the total number of rows matched by the query.

function JB_list_jobs ($list_mode) {
	

	if (func_num_args() > 1) { // what kind of posts to show
		$show = func_get_arg(1);
	}

	global $label;
	global $post_count;
	$post_count = null; // reset post count.
	

	#############################################
	# Build the apporved SQL part
	$approved_sql =" approved='Y' "; 
	if ($show=="WA") { // waiting
	   $approved_sql = " approved='N' ";
	   $where_sql .= " AND `reason` ='' ";
	} elseif ($show=="NA") { // not approved
	   $approved_sql = " approved ='N' ";
	   $where_sql .= " AND `reason` !='' ";
	} elseif ($show=="EX") { // expired
		$approved_sql = ' 1=1 ';	
	} elseif ($show=="EMP") { // expired
		$approved_sql = ' 1=1 ';
	}
	
	#############################################
	# Build the ORDER BY part

	$order = jb_alpha_numeric($_REQUEST['order_by']);
	
	if ($_REQUEST['ord']=='asc') {
		$ord = 'ASC';
	} elseif ($_REQUEST['ord']=='desc') {
		$ord = 'DESC';
	} else {
		$ord = 'DESC'; // sort descending by default
	}

	if (($order == '') || (!JB_is_field_valid($order, 1))) {
		// by default, order by the post_date, if the field is invalid
		$order = " `post_date` ";           
	} elseif ($order=='summary') {
		// order by title instead
		$order = JB_get_template_field_id ('TITLE', 1); 
	} else {

		$order = " `".jb_escape_sql($order)."` ";
	}
	
	############################################
	# Search Posts

	$where_sql .= JB_generate_search_sql(1);
	
  
	############################################
	# PREMIUM list mode
	# To list only premium jobs, call like this: JB_list_jobs('PREMIUM')

	if (!defined('JB_SHOW_PREMIUM_LIST')) { // new setting since 3.4.13, may not be in config.php
		JB_SHOW_PREMIUM_LIST == 'YES';
	}

	
	# Set $premium_sql
	# This determines whether to:
	# - include only premium posts to the list
	# - include only standard posts to the list
	
	# - do not show the premium list at all, return the call

	if ($list_mode == 'PREMIUM' ) {
		if (JB_SHOW_PREMIUM_LIST!='YES') {
			// PREMIUM list is turned off in Admin->Main Config
			// do not show the premium list at all, return the call
			
			return;
		}
		// - include only premium posts to the list
		$premium_sql = "AND ( ".
					"post_mode ".
					"= 'premium'".
				") ";
		$post_count = JB_get_post_count('PAP'); // PAP - Approved premium posts, not expired

	} elseif (JB_DONT_REPEAT_PREMIUM == 'YES') {
		
		// Premium posts are listed on top in a seperate list
		// This ensures that when listing the standard posts, the premium
		// posts are not repeated.
		// If listing jobs on the front page, no search executed and the page is index.php
		
		global $JB_HOME_PAGE, $JOB_LIST_PAGE;
		if (($JB_HOME_PAGE | $JOB_LIST_PAGE) && ($list_mode=='ALL') &&  (JB_SHOW_PREMIUM_LIST=='YES')) {
			// - include only standard posts to the list
			$premium_sql .= "AND ( ".
					"post_mode ".
					"!= 'premium'".
				") "; 
			$post_count = JB_get_post_count('SAP'); // Approved, not premium, not expired
		}

	}  

	#############################################
	# Show posts by employer? 
	$_REQUEST['show_emp'] = (int) $_REQUEST['show_emp'];
	if ($_REQUEST['show_emp'] > 0) { // is user_id > 0 ? 
		$show_emp_sql = " AND user_id='".jb_escape_sql($_REQUEST['show_emp'])."' ";
	}
	
	#############################################
	# Get todays date (in GMT)
	$now = (gmdate("Y-m-d"));

	#############################################
	# build the LIMIT part

	$offset = (int) $_REQUEST['offset'];

	if ($offset<0) {
		$offset = abs($offset);
	}
 
	$limit_sql = " LIMIT $offset, ";
	if ($list_mode == 'PREMIUM') {
		if (JB_PREMIUM_POSTS_LIMIT == 'YES') {
			$limit_sql .= JB_PREMIUM_POSTS_PER_PAGE;
		} else {
			// there's no limit
			$limit_sql = '';
		}
	} elseif ($list_mode == 'EMPLOYER') {
		$limit_sql .= JB_MANAGER_POSTS_PER_PAGE;
	} else {
		$limit_sql .= JB_POSTS_PER_PAGE;
	}

	# Include a SQL_CALC_FOUND_ROWS option to count the number of posts returned
	# See http://dev.mysql.com/doc/refman/5.0/en/information-functions.html#function_found-rows
	
	if ((($where_sql!='') || ($show_emp_sql!='')) || ($post_count===null)) {
		// If its not a search, or by listing employer, and the post count
		// is unknown, we need to tell MySQL to count the posts returned without
		// the LIMIT clause
		$calc_found_rows_sql = 'SQL_CALC_FOUND_ROWS';
	}
	
	#############################################
	# Glue the SQL query, basted on $list_mode

	if ($list_mode == 'SAVED' ) {
		$calc_found_rows_sql = 'SQL_CALC_FOUND_ROWS';
		$sql = "SELECT $calc_found_rows_sql *, posts_table.user_id as user_id FROM `posts_table`, `saved_jobs` WHERE saved_jobs.user_id='".jb_escape_sql($_SESSION['JB_ID'])."' AND (saved_jobs.post_id=posts_table.post_id) AND  expired='N' ORDER BY $order $ord $limit_sql";


	} elseif (($list_mode == 'BY_CATEGORY' ) || ($list_mode== "BY_CATEGORY_ADMIN")) {
		$calc_found_rows_sql = 'SQL_CALC_FOUND_ROWS';
		$cat = JB_search_category_tree_for_posts();
		$sql = "SELECT $calc_found_rows_sql * FROM posts_table where $approved_sql  $where_sql $show_emp_sql AND  expired='N' $cat ORDER BY ($order) $ord $limit_sql";

	} elseif ($list_mode == 'EMPLOYER' ) {

		// employer's post manager.
		$calc_found_rows_sql = 'SQL_CALC_FOUND_ROWS';
		if ($show=="OFFLINE") {
			$date_range_sql = ''; // include posts that are expired.
			$date_range_sql = "AND  expired='Y'  ";
			$approved_sql = " OR  (approved='N' AND  user_id='".jb_escape_sql($_SESSION['JB_ID'])."')  ";
		} else {
			// show current posts
			$date_range_sql = "AND  expired='N' ";
			$approved_sql = " AND  approved='Y' ";
		}

		$sql = "SELECT $calc_found_rows_sql * FROM posts_table where (1=1 $where_sql  $date_range_sql AND user_id='".jb_escape_sql($_SESSION['JB_ID'])."') $approved_sql ORDER BY ($order) $ord $limit_sql";


	} else {

		if ($show=='EX') { // show expired?
			$expired_sql = " AND expired='Y' ";
		} else {
			$expired_sql = " AND expired='N' ";
		}

		$sql = "SELECT $calc_found_rows_sql * FROM posts_table where $approved_sql $expired_sql $premium_sql $where_sql $show_emp_sql  ORDER BY ($order) $ord  $limit_sql  ";

	}


	 //echo '<hr>sql:'.$sql." where_sql:[$where_sql] show_emp:[$show_emp_sql] cat:[$cat] calc_found_rows_sql:[$calc_found_rows_sql] (LM: $list_mode)<br>";
	// some debugging & performance test
	//$result = JB_mysql_query("EXPLAIN ".$sql) or die ("[$sql]".mysql_error());
	//$row = mysql_fetch_array($result, MYSQL_ASSOC);
	//echo "<pre>";print_r($row);echo "</pre>";
	//echo "<br>".$sql."<br>";




	#################################
	# Execute the SQL query

	if (!JBPLUG_do_callback('job_list_custom_query', $result, $sql)) { // A plugin can modify the result with a custom query
		$result = JB_mysql_query($sql);
	}

	

	#################################
	# Get the post_count

	# If $calc_found_rows_sql was not used, then we assume that the post
	# was is cashed in the database.
	

	if ($calc_found_rows_sql == '') { 


		// MySQL did not count the number of posts
		// that were returned, then get the cached number.

		if ($list_mode=='PREMIUM') {
			$post_count = JB_get_post_count('PAP'); // premium approved
		} elseif ($post_count=='') {
			if ($show=='NA') { // not approved (admin)
				$post_count = JB_get_post_count('NA'); // get non approved posts count, admin list
			} elseif ($show=='ALL') {
				$post_count = JB_get_post_count('AP'); // AP - Approved (expired='N' AND approved='Y'), admin list
			} elseif ($show=="WA") { // waiting count, admin list
				$post_count = JB_get_post_count('WA');
			} elseif ($show == "EX") { // expired count, admin list
				$post_count = JB_get_post_count('EX');
			} else {
				// get all the count of all apporved and not expired
				$post_count = JB_get_post_count('AP'); // AP - Approved (expired='N' AND approved='Y')
			}
		}


	} else {
		# Ask MySQL to get the number of rows from the last query
		# Even though the last query had a LIMIT clause
		$row = mysql_fetch_row(jb_mysql_query("SELECT FOUND_ROWS()"));
		$post_count = $row[0]; 
	}

	JBPLUG_do_callback('job_list_set_count', $post_count, $list_mode); // A plugin can modify the post count



	########################################
	# Print how many jobs returned

	$PLM = &JB_get_PostListMarkupObject(); // load the ListMarkup Class

	if ($post_count == 0) {
		if ($list_mode == "PREMIUM") {
			//echo "<p>&nbsp;</p>";
		} elseif ( ($list_mode == "SAVED")) {
			//echo "<p>&nbsp;</p>";			
		} elseif ($list_mode == "BY_CATEGORY") {
			//echo "<p>&nbsp;</p>";
		} elseif ($list_mode == "EMPLOYER") {
			$PLM->no_posts_employer();			
		}else {
			$PLM->no_posts();
		}
	} else {
		if ($list_mode == "PREMIUM") {
				$PLM->sponsored_heading($post_count);
			} elseif (($list_mode == "ALL"))  {

				$label['post_list_count'] = str_replace ("%COUNT%", $post_count, $label['post_list_count']);
				$label['post_list_count'] = str_replace ("%POSTS_DISPLAY_DAYS%", JB_POSTS_DISPLAY_DAYS, $label['post_list_count']);

				$PLM->post_count($post_count);
		
				
			} elseif ($list_mode == "BY_CATEGORY") {
					
				$label['post_list_cat_count'] = str_replace ("%COUNT%", $post_count, $label['post_list_cat_count']);
				$label['post_list_cat_count'] = str_replace ("%POSTS_DISPLAY_DAYS%", JB_POSTS_DISPLAY_DAYS, $label['post_list_cat_count']);

				$PLM->post_count_category($post_count);
			}

			#################################################
		
				
			JB_display_post_list ($result, $list_mode, $show);
			

	} // end else if mysql num rows > 0 
	return $post_count;
}
######################################

function JB_nav_pages(&$result, $q_string, $pp_page) {

	global $label;
	global $post_count;
	$PLM = &JB_get_ListMarkupObject(); // load the ListMarkup Class

	$offset = (int) $_REQUEST["offset"];
	$show_emp = (int) $_REQUEST["show_emp"];
	$cat = (int) $_REQUEST["cat"];
	$count = $post_count;

	$cur_page = $offset / $pp_page;
	$cur_page++;
	// estimate number of pages.
	$pages = ceil($post_count / $pp_page);
	if ($pages == 1) {
	   return;
	}
	$PLM->nav_pages_start();

	if ($cur_page != 1) {
		$label["navigation_page"] =  str_replace ("%CUR_PAGE%", $cur_page, $label["navigation_page"]);
		$label["navigation_page"] =  str_replace ("%PAGES%", $pages, $label["navigation_page"]);
		$PLM->nav_pages_status();
		
	}
	
	$nav = JB_nav_pages_struct($result, $q_string, $post_count, $pp_page);
	$LINKS = 10;
	JB_render_postlist_nav_links($nav, $LINKS, $pp_page, $q_string);
	$PLM->nav_pages_end();


}

##########################################################
# $date_time - YYYY-MM-DD H:i:s in GMT
function JB_get_day_and_week ($date_time) {
	
	global $label;
	
	$trimmed_date = JB_trim_date(JB_get_local_time($date_time));

	$t = strtotime($date_time) + (3600 * JB_GMT_DIF); 
	$t_now = strtotime((gmdate('Y-M-d')).' 23:59:59' ) + (3600 * JB_GMT_DIF); 


	$diff = $t_now - $t;
	if ($diff < 0) { // cannot be negative
		$diff = 0;
	}
	$weeks = floor($diff / 604800) ;
	
	if ($weeks > 4) {
		static $more_than_4;
		if ($more_than_4) {
			return null; // only repeat once
		}
		$more_than_4 = $label['post_list_dow_'.$d];

		return $more_than_4;
	}
	if ($weeks == 3) {
		$d = gmdate("w", $t);
		$day = $label['post_list_dow_'.$d];
		return $label['post_list_3_weeks']." ".$day;
	}
	if ($weeks == 2) {
		$d = gmdate("w", $t);
		$day = $label['post_list_dow_'.$d];
		return $label['post_list_2_weeks']." ".$day;
	}
	if ($weeks == 1) {
		$d = gmdate("w", $t);
		$day = $label['post_list_dow_'.$d];
		return $label['post_list_1_week']." ".$day;
	} 
	if ($weeks == 0) {

		$td2 = JB_trim_date(JB_get_local_time(gmdate('r'))); // is it today?
		if ($trimmed_date==$td2) {
			return $label['post_list_today'];

		}
		$d = gmdate("w", $t);
		$day = $label['post_list_dow_'.$d]." ";
		return $day;
	}
}

##########################################################


function JB_display_post_list (&$posts, $lm) {
	

	global $JobListAttributes;
	global $list_mode;	
	$list_mode = $lm;
	global $post_count;
	global $post_id;
	
	global $label;
	

	global $column_list; // - this is initialized in lists.inc.php using the JB_echo_list_head_data() function
	global $column_info; // - same as above

	$PLM = &JB_get_PostListMarkupObject(); // load the ListMarkup Class

	$PLM->set_list_mode($lm);
	
	if (func_num_args() > 2) { // what kind of posts to show
		$show = func_get_arg(2);
		$_REQUEST['show'] = $show;
	}

	$PLM->set_show($show);

	$JobListAttributes = new JobListAttributes($list_mode, $show);
	$JobListAttributes->set_list_mode($lm);

	if ($list_mode=='ADMIN') {
		$admin = true;
	}


	if ($list_mode == "BY_CATEGORY_ADMIN") {
		$list_mode = "ADMIN";
	}

	if (($list_mode=='ADMIN') || ($list_mode=='SAVED') || ($list_mode=='EMPLOYER')) {
		$PLM->open_form($JobListAttributes);
	}

	if ($list_mode=='PREMIUM') {
		if (JB_PREMIUM_POSTS_LIMIT=='YES') {
			$pp_page = JB_PREMIUM_POSTS_PER_PAGE;
		} else {
			$pp_page = 200;
		}
	} elseif ($list_mode=='EMPLOYER') {
		if (defined('JB_MANAGER_POSTS_PER_PAGE')) {
			$pp_page = JB_MANAGER_POSTS_PER_PAGE; 
		} else {
			$pp_page = 20;
		}

	} else {
		$pp_page = JB_POSTS_PER_PAGE;
	}

	if ($list_mode != 'PREMIUM' ) {
		JB_nav_pages($result, $q_string, $pp_page);
	} 

	$css_id = $PLM->get_list_css_id();

	$PLM->list_start($css_id);

	

	################
	# Generate output for the head <tr> row of the table
	# Dynamic columns are generated by Jb_echo_list_head_data() in lists.inc.php and
	# placed in the $head_data variable
	# The $head_data is cached, otherwise if conditions are used
	# to generate the output for the <td> parts

	$COLSPAN = '';
	// How many columns? (the hits column does not count here...)
	ob_start(); // buffer the output, so that we can calculate the colspan.
	$COLSPAN = JB_echo_list_head_data(1, $admin);
	$list_head_data = ob_get_contents();
	ob_end_clean();
	JBPLUG_do_callback('job_list_set_colspan', $COLSPAN); // set the colspan value
	$PLM->set_colspan($COLSPAN);

	if ($list_mode == 'SAVED' ) {
		$PLM->saved_list_controls();	
	} elseif ($list_mode=='ADMIN') { 
		$PLM->admin_list_controls();	
	} elseif ($list_mode=='EMPLOYER') {
		$PLM->employer_list_controls(); 
	}
	JBPLUG_do_callback('job_list_controls', $PLM); // plugins can render any undefined list controls
	
	#######################################
	# Open the list heading section
	$PLM->list_head_open();

	if ($list_mode=='ADMIN') {
		$PLM->list_head_admin_action();
		JBPLUG_do_callback('job_list_head_admin_action', $A = false);  // plugin for addition action column
	} elseif ($list_mode=='EMPLOYER') {
		$PLM->list_head_employer_action();
	} elseif ($list_mode=='SAVED') { 
		$PLM->list_head_saved_action(); 
	}
	JBPLUG_do_callback('joblist_list_head_action', $PLM);
	
	

	########################################################################

	
	echo $list_head_data; // output the header columns that were buffered before.


	########################################################################
	
	
	if ($list_mode=='EMPLOYER') {
		// Here we make sure that the 'views' and 'applications' columns
		// appear in manager.php, regardless how the list is configured.

		$PLM->list_head_employer_extras();
		
		JBPLUG_do_callback('job_list_head_employer_extra_col', $A = false); // plugin for the additional columns seen by the employer's Application manager
		
	}
	
	if (($list_mode=='PREMIUM') && (JB_SHOW_PREMIUM_HITS=='YES')) { 
		$PLM->list_head_premium_extras();
	} 
	
	######################################
	# Close the list heading section
	$PLM->list_head_close();
    
    $i=0;
	
	$current_day = (JB_get_local_time(gmdate("r"))); // local time form GMT

	# Output the data rows

	JBPLUG_do_callback('job_list_pre_fill', $i, $list_mode); //A plugin can list its own records before, and adjust the $i

	// init a week ago from first post
	while (($row = mysql_fetch_array($posts, MYSQL_ASSOC)) && ($i < $pp_page)) {

		$PLM->set_values($row);
		JBPLUG_do_callback('job_list_set_data', $row, $i, $list_mode); // A plugin can modify the prams

		$i++;

		$post_id = $row['post_id'];
		$POST_MODE = $row['post_mode'];
		

		$class_name = $PLM->get_item_class_name($POST_MODE);
		$class_postfix = $PLM->get_item_class_postfix($POST_MODE);


		//$DATE_TIME = JB_get_local_time($row['post_date']." GMT");


	    # display day of week
		if ((($list_mode == 'ALL') && (JB_POSTS_SHOW_DAYS_ELAPSED == "YES")) || 
			(($list_mode == 'PREMIUM') && (JB_P_POSTS_SHOW_DAYS_ELAPSED == "YES"))) {

			$DATE_TIME = JB_get_local_time($row['post_date']." GMT");


			# display day of week
			if ((($list_mode == 'ALL') && (JB_POSTS_SHOW_DAYS_ELAPSED == "YES")) || 
			(($list_mode == 'PREMIUM') && (JB_P_POSTS_SHOW_DAYS_ELAPSED == "YES"))) {

				$day_and_week = JB_get_day_and_week ($row['post_date']);


				if ($day_and_week !== $prev_day_and_week) { // new day?
					
					if ($day_and_week!='') {
						$PLM->list_day_of_week($day_and_week, $class_postfix);
					}	
				}
				$prev_day_and_week = $day_and_week;
			}

		}

		########################################
		# Open the list data items
		

		$PLM->list_item_open($POST_MODE, $class_name);
	  
	    # Action cells
		# Here the action buttons are displayed, eg. 'Delete', 'Approve', checkboxes to select, etc

		if ($list_mode=='ADMIN') {
			$PLM->list_data_admin_action($class_postfix, $row['post_mode'] );
			JBPLUG_do_callback('job_list_data_admin_action', $A = false); // plugin for the additional controls for the Admin
		} elseif ($list_mode=='SAVED') { 
			$PLM->list_data_saved_action();
		}  elseif ($list_mode=='EMPLOYER')   { // EMPLOYER MODE ONLY! 
			
			$PLM->list_data_employer_action($class_postfix, $row['post_mode']);
			JBPLUG_do_callback('job_list_data_employer_buttons', $A = false); // plugin for the additional controls for the Admin
		} 
		JBPLUG_do_callback('joblist_list_data_user_action', $A = false);

		########################################################################


		JB_echo_job_list_data($admin); // display the data cells

		########################################################################
	
		if ($list_mode=='EMPLOYER') { // displayed on manager.php 
	
			$app_count = $row['applications'];

			// change the app_count in to a link
			if ($app_count>0) {
				$app_count = $PLM->get_emp_app_link('app.php?post_id='.$row['post_id'], $app_count); 
			}

			$PLM->list_data_employer_extras($app_count);
		
			JBPLUG_do_callback('job_list_data_employer_extra_col', $A = false); // plugin for the additional fields seen by the employer's Application manager
		} 
		  
		if (($list_mode=='PREMIUM') && (JB_SHOW_PREMIUM_HITS=='YES')) { 
			
			$PLM->list_data_premium_extras();
		}
		
		$PLM->list_item_close();

         

	} ############ end while()

	JBPLUG_do_callback('job_list_back_fill', $i, $list_mode); // A plugin can list its own records after



	$PLM->list_end();

	if (($list_mode=='ADMIN') || ($list_mode=='SAVED') || ($list_mode=='EMPLOYER')){
		$PLM->close_form();
	}


	if ($list_mode == 'PREMIUM' ) {
		if (JB_PREMIUM_POSTS_LIMIT == 'YES') {	
			JB_nav_pages($result, $q_string, $pp_page);
		}

	} else {
		JB_nav_pages($result, $q_string, $pp_page);
	}

	

}

######################################################

function JB_generate_post_id () {

   $query ="SELECT max(`post_id`) FROM `posts_table";
   $result = JB_mysql_query($query) or die(mysql_error());
   $row = mysql_fetch_row($result);
   $row[0]++;
   return $row[0];

}


################################################################


define ('JB_IPD_TIMER', false);

if (JB_IPD_TIMER) {
	$timestart = microtime();
	echo "included posts.inc.php - ".JB_get_time_diff()."sec<br>";
}

function JB_insert_post_data($insert_mode='EMPLOYER') {
	


	if (($_REQUEST['user_id'] != '') && ($insert_mode=='ADMIN')) {
		$user_id = (int) $_REQUEST['user_id'];
	} else {
		$user_id = (int) $_SESSION['JB_ID'];
	}

	// determine what kind of posting it is
	$post_mode = "free";
	if ($_REQUEST['type'] != 'premium') {
		if (JB_POSTING_FEE_ENABLED == 'YES') {
			$post_mode = "normal";
			if ($insert_mode!='ADMIN') {
				$credits = JB_get_num_posts_remaining($user_id);
			}
		}
	} else {
		if ((JB_PREMIUM_POSTING_FEE_ENABLED == 'YES') )  {
			$post_mode = "premium";
			if ($insert_mode!='ADMIN') {
				$credits = JB_get_num_premium_posts_remaining($user_id);
			}
		}
	}

	$_PRIVILEGED_USER = false;
	if ($insert_mode!='ADMIN') { // check if the user is priveleged
		$_PRIVILEGED_USER = JB_is_privileged_user($user_id, $post_mode);
	} elseif ($insert_mode=='ADMIN') {
		// Admin mode is always _PRIVILEGED_USER
		$_PRIVILEGED_USER = true;
	}

	$approved = 'N';

	 
	if (JB_POSTS_NEED_APPROVAL=='NO') {
		$approved = 'Y';
	
	}
	// approve for _PRIVILEGED_USER
	elseif ($_PRIVILEGED_USER) {
		$approved = 'Y';
	} elseif ((JB_POSTS_NEED_APPROVAL == 'NOT_SUBSCRIBERS') && ($insert_mode=='EMPLOYER')) {

		// no approval needed for subscibers..

		if (JB_SUBSCRIPTION_FEE_ENABLED=='YES') { // check subscription

			if(JB_get_employer_subscription_status($user_id)=='Active') {
				$approved = 'Y';
			}
		}

		if ($post_mode != 'free') {
			$approved = 'Y';
		}

	}

	if ($_REQUEST['app_type']==false) {
		$_REQUEST['app_type']="O";
	}

	$new = false;

	if ($_REQUEST['post_id'] == false) {
		$new = true;
		
		$now = (gmdate("Y-m-d H:i:s"));

		$assign = array(
			'post_date' => gmdate("Y-m-d H:i:s"),
			'post_mode' => $post_mode,
			'user_id' => $user_id,
			'pin_x' => (int) $_REQUEST['pin_x'],
			'pin_y' => (int) $_REQUEST['pin_y'],
			'approved' => $approved,
			'app_type' => $_REQUEST['app_type'],
			'app_url' => $_REQUEST['app_url'],
			'cached_summary' => '',
			'expired' => 'N'
		);

		$sql = "REPLACE INTO `posts_table` (".JB_get_sql_insert_fields(1, $assign).") VALUES (".JB_get_sql_insert_values(1, "posts_table", "post_id", $post_id, $user_id, $assign)." )";



		// DEDUCT CREDITS (For new posts)
		if (($post_mode == 'normal') && (!$_PRIVILEGED_USER)) {
			JB_deduct_posting_credit($user_id);
		}
		
		if (($post_mode == 'premium') && (!$_PRIVILEGED_USER)) {
			JB_deduct_p_posting_credit($user_id);
		}

	} else {
		$post_id = (int) $_REQUEST['post_id'];

		
		if ($insert_mode!='ADMIN') {
			// verify that the post is owned by this user in case of hacking

			$sql = "SELECT * from posts_table where post_id='".jb_escape_sql($_REQUEST['post_id'])."'";

			//echo $sql.'<br>'.$user_id;
			$result = JB_mysql_query ($sql) or die (mysql_error());
			$row = mysql_fetch_array($result, MYSQL_ASSOC);
			if ($row['user_id'] != $user_id) { 
				die ('hacking attempt');
			}

		}

		$old_data = JB_load_post_data($post_id); // these old_values will be used to update the category counters & keep the current approved status

		
		$approved = $old_data['approved'];

		$assign = array(
			
			'pin_x' => (int) $_REQUEST['pin_x'],
			'pin_y' => (int) $_REQUEST['pin_y'],
			'approved' => $approved,
			'app_type' => $_REQUEST['app_type'],
			'app_url' => $_REQUEST['app_url'],
			
		);
		
		$sql = "UPDATE `posts_table` SET ".JB_get_sql_update_values (1, "posts_table", "post_id", $_REQUEST['post_id'], $user_id, $assign)." WHERE post_id='".jb_escape_sql($post_id)."'";

		
	}
	
	$result = JB_mysql_query ($sql) or die(mysql_error().$sql);

	if ($new) {
		$post_id = jb_mysql_insert_id();
	}

	
	JBPLUG_do_callback('insert_post_data', $post_id); // for the plugin if you want your plugin to do something after a post is saved. Note that if the post is edited then $_REQUEST['post_id'] will be set or else this is a new post.
	
	if (JB_PREMIUM_AUTO_UPGRADE == 'YES') { // auto upgrade to premium!
		$post_mode = "premium";
		$sql = "UPDATE `posts_table` SET `post_mode`='".jb_escape_sql($post_mode)."' WHERE post_id='".jb_escape_sql($post_id)."' ";
		JB_mysql_query ($sql) or die(mysql_error().$sql);
	}


	// rebuild categories count...

	JB_update_post_category_count($old_data, $_REQUEST); // This will update the category counters only for the affected categories

	// build categories cache / update counters / update rss, etc.

	JB_finalize_post_updates();

	if ((JB_EMAIL_NEW_POST_SWITCH == 'YES') && ($new)) {

		$Form = JB_get_DynamicFormObject(1);
		$Form->load($post_id);	
		

		$TITLE = $Form->get_raw_template_value ("TITLE");
		$POSTED_BY = $Form->get_raw_template_value ("POSTED_BY");
		$POSTED_BY_ID = $Form->get_raw_template_value ("USER_ID");
		$DATE = JB_get_formatted_date($Form->get_template_value ("DATE"));
		
		$FORMATTED_DATE = $DATE;
		$DESCRIPTION = $Form->get_raw_template_value ("DESCRIPTION");

		// get the email template
		$template_result = JB_get_email_template(310, $_SESSION['LANG']); 
		$t_row = mysql_fetch_array($template_result);

		$to_address = JB_SITE_CONTACT_EMAIL;
		$to_name = JB_SITE_NAME;
		$subject = $t_row['EmailSubject'];
		$message = $t_row['EmailText'];
		$from_name = $t_row['EmailFromName'];
		$from_address = $t_row['EmailFromAddress'];
		$subject = str_replace("%SITE_NAME%", JB_SITE_NAME, $subject);
		$message = str_replace("%SITE_NAME%", JB_SITE_NAME, $message);
		$message = str_replace("%SITE_URL%", JB_BASE_HTTP_PATH, $message);
		$message = str_replace("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $message);
		$message = str_replace("%POST_TITLE%", $TITLE, $message);
		$message = str_replace("%DATE%", $FORMATTED_DATE, $message);
		$message = str_replace("%POST_DESCRIPTION%", $DESCRIPTION, $message);
		$message = str_replace("%POSTED_BY%", $POSTED_BY, $message);
		$message = str_replace("%ADMIN_LINK%", JB_BASE_HTTP_PATH."admin/ra.php?post_id=".$Form->get_value('post_id')."&key=".md5($Form->get_value('post_id').JB_ADMIN_PASSWORD), $message);

		$message = str_replace('<BR>', "\n", $message);
		$message = str_replace('<P>', "\n\n", $message);

		$message = html_entity_decode($message);
		$message = strip_tags($message);
	

		$email_id = JB_queue_mail($to_address, $to_name, $from_address, $from_name, $subject, $message, '', 310);

		JB_process_mail_queue(1, $email_id);


	}
	
	return $post_id;
}



###############################################################
function JB_validate_post_data($insert_mode='EMPLOYER') {

	
	global $label;
	$error = '';
	$errors = array();

	/*
	Only check for credits if posted by employer
	*/

	if (($insert_mode=='EMPLOYER') && ($_REQUEST['post_id']==false)) {

		$sql = "select * from employers where ID='".jb_escape_sql($_SESSION['JB_ID'])."'";
		$result = JB_mysql_query($sql) or die(mysql_error());
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		
		$_PRIVILEGED_USER = JB_is_privileged_user($_SESSION['JB_ID'], $_REQUEST['post_mode']);

		if ($_REQUEST['type'] != 'premium') {
			if ((JB_POSTING_FEE_ENABLED == 'YES') && (!$_PRIVILEGED_USER)) {
				// check standard credits

				$posts = JB_get_num_posts_remaining($_SESSION['JB_ID']);

				if (($posts<1) && ($posts!=-1)) {
					$errors[] = $label['post_no_credits'];
					return $errors;
				}
			}
		} else {
			
			if ((JB_PREMIUM_POSTING_FEE_ENABLED == 'YES') && (!$_PRIVILEGED_USER))  {
				
				// check standard credits
				$p_posts = JB_get_num_premium_posts_remaining($_SESSION['JB_ID']);
			
				if (($p_posts < 1) && ($p_posts != -1)) {
					$errors[] = $label['post_no_credits'];
					return $errors;
				}
				
			}
		}

	}

	if ($insert_mode!='EMPLOYER') {
		$_PRIVILEGED_USER = true;
	}

	// Make sure they are numeric
	if ($_REQUEST['post_id']!='') {
		if (!is_numeric($_REQUEST['post_id'])) {
			return 'Invalid Input!';
		}
	}
	if ($_REQUEST['user_id']!='') {
		if (!is_numeric($_REQUEST['user_id'])) {
			return 'Invalid Input!';
		}
	}
	if ($_REQUEST['pin_x']!='') {
		if (!is_numeric($_REQUEST['pin_x'])) {
			return 'Invalid Input!';
		}
	}
	if ($_REQUEST['pin_y']!='') {
		if (!is_numeric($_REQUEST['pin_y'])) {
			return 'Invalid Input!';
		}
	}

	

	// app_type and app_url

	if ($_REQUEST['app_type']=='R') {
		// check the url.

		$_REQUEST['app_url'] = trim($_REQUEST['app_url']);
		$_REQUEST['app_url'] = JB_clean_str($_REQUEST['app_url']);

		if($_REQUEST['app_url']==false) {
			$errors[] = $label['post_save_app_url_blank'];
		} elseif ((strpos($_REQUEST['app_url'], 'http://')===false) && (strpos($_REQUEST['app_url'], 'https://')===false)) {
			$errors[] = $label['post_save_app_url_bad'];
		}
	}
	
	// clean any undesired input, leave nothing to chance

	$_REQUEST['post_date'] = JB_clean_str($_REQUEST['post_date']);
	$_REQUEST['post_mode'] = JB_clean_str($_REQUEST['post_mode']);
	$_REQUEST['approved'] = JB_clean_str($_REQUEST['approved']);
	$_REQUEST['expired'] = JB_clean_str($_REQUEST['expired']);
	
	$error = '';
	JBPLUG_do_callback('validate_post_data', $error); // deprecated, use validate_post_data_array
	if ($error) {
		$list = explode('<br>', $error);
		foreach ($list as $item) {
			$errors[] = $item;
		}
	}
	JBPLUG_do_callback('validate_post_data_array', $errors); // added in 3.6.6
	//append errors
	$errors = $errors + JB_validate_form_data(1);
	return $errors;
}

#######################################

function JB_is_job_saved($user_id, $post_id) {
	if (($user_id=='')||($post_id=='')) { return false;}
	$sql = "SELECT * FROM `saved_jobs` WHERE `user_id`='".jb_escape_sql($user_id)."' AND `post_id`='".jb_escape_sql($post_id)."' ";
	$result = JB_mysql_query($sql) or die (mysql_error());
	
	if (mysql_num_rows($result)>0) {
		return true;
	}
	return false;

}

######################################

function JB_delete_post($post_id) {

	$old_data = JB_load_post_data($post_id);

	JB_delete_post_files ($post_id);

	$sql = "DELETE FROM `posts_table` where `post_id`='".jb_escape_sql($post_id)."'";
	JB_mysql_query($sql) or die(mysql_error());

	$sql = "DELETE FROM `applications` where `post_id`='".jb_escape_sql($post_id)."'";
	JB_mysql_query($sql) or die(mysql_error());

	$sql = "DELETE FROM `saved_jobs` where `post_id`='".jb_escape_sql($post_id)."'";
	JB_mysql_query($sql) or die(mysql_error());

	JB_update_post_category_count($old_data); // category counters



	JBPLUG_do_callback('delete_post', $post_id);



}


############################################

function JB_update_post_category_count(&$old_data, $new_data=null) {

	// lock the tables for faster access
	$sql = "LOCK TABLES categories WRITE, form_fields READ, posts_table READ";
	//$result = JB_mysql_query ($sql) or die (" <b>Dear Webmaster: The current MySQL user does not have permission to lock tables. Please give this user permission to lock tables.<b>");

	# Now we need to determine which fields are CATEGORY type
	# For efficency, the function
	# JB_update_post_category_counters() does not update the whole tree, but only
	# the categories on the branch starting from the leaf and going to the root. 
	# Therefore we need to update both the new branch
	# and the old branch. So we need to know the new category_id and the new category_id
	# for each of the CATEGORY type fields in the posting form.
	$sql = "SELECT field_id FROM form_fields WHERE field_type='CATEGORY' AND form_id='1' ";
	
	$result = JB_mysql_query($sql) or die (mysql_error());
	# Iterate through each CATEGORY field type
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		// Update the new category (add)
		# Since JB_update_post_category_counters() does not
		if (sizeof($new_data) > 0) {
			// The new category_id value
			if ($new_data[$row['field_id']]!='') {
			
				JB_update_post_category_counters($new_data[$row['field_id']], $row['field_id']);
			}
		}
		if (sizeof($old_data) > 0) {
			// update the old category (remove), if there was any old category
			// pass the new category_id value
		
			JB_update_post_category_counters($old_data[$row['field_id']], $row['field_id']);
		}
		
	}

	$sql = "UNLOCK TABLES ";
	//$result = JB_mysql_query ($sql) or die ();



}

########################################################
function JB_delete_post_files ($post_id) {

	$sql = "select * from form_fields where form_id=1 ";
	$result = JB_mysql_query ($sql) or die (mysql_error());

	while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

		$field_id = $row['field_id'];
		$field_type = $row['field_type'];

		if (($field_type == "FILE")) {
			
			JB_delete_file_from_field_id("posts_table", "post_id", $post_id, $field_id);
			
		}

		if (($field_type == "IMAGE")){
			
			JB_delete_image_from_field_id("posts_table", "post_id", $post_id, $field_id);
			
		}
		
	}


}

///////////////////////////////////////////////////


// get all the category fields form the post

function JB_expire_posts($post_type) {
	if ($post_type=='PREMIUM') {
		if (defined('JB_P_POSTS_DISPLAY_DAYS')) {
			$display_days = JB_P_POSTS_DISPLAY_DAYS;
			$type_sql = " AND post_mode = 'premium' ";
		} else {
			$display_days = JB_POSTS_DISPLAY_DAYS;
			$type_sql = " AND post_mode != 'premium' ";
		}
	} else {
		$display_days = JB_POSTS_DISPLAY_DAYS;
		$type_sql = " AND post_mode != 'premium' ";
	}
	// select all field_id where type is Category form the post table
	// then put these field_ids in to a string, eg 13, 22, 24
	$sql = "SELECT field_id FROM form_fields WHERE field_type='CATEGORY' AND form_id='1' ";
	$cat_result = JB_mysql_query($sql) or die (mysql_error());
	$cats = array();
	if (mysql_num_rows($cat_result)) {
		while ($cat_row = mysql_fetch_array($cat_result, MYSQL_ASSOC)) {
			$cats[] = "`".$cat_row['field_id']."`";
		}
		$cats = ', '.implode(',', $cats); 
	} else {
		// there are no categories to select
		$cats = '';
	}
	
	// Now select the $cats along with the post_id form the posts table which
	// are older than $display_days (expired)
	$now = (gmdate("Y-m-d H:i:s"));
	$sql = "SELECT post_id $cats FROM posts_table WHERE DATE_SUB('$now',INTERVAL ".jb_escape_sql($display_days)." DAY) >= `post_date` AND expired='N' $type_sql  ";


	$exp_result = JB_mysql_query($sql) or $DB_ERROR = mysql_error();
	
	$affected_cats = array();

	while ($post_row = mysql_fetch_array($exp_result, MYSQL_ASSOC)) {

		// go through each column, if the column is a post_id then expire
		// else it is a category
		foreach ($post_row as $col_name => $col_val) {

			if ($col_name=='post_id') {
				//echo "update post_id:".$col_val."<br>";
				// set to expired

				JB_expire_post($col_val);
					
			} else {
				$affected_cats[$col_val] = $col_name; // remember the affected category

			}

		}

	}
	foreach ($affected_cats as $key => $val) {
		//JB_update_post_category_counters($leaf_cat_id, $field_id, $search_set='');
		JB_update_post_category_counters($key, $val);
		//echo "updating counters...$key, $val<br>";
	}

	JB_finalize_post_updates();



	

}


########################################################
# Expire the post and send a notification that the post expired.

function JB_expire_post($post_id) {

	$post_id = (int) $post_id;

	$sql = "UPDATE posts_table SET expired='Y' where post_id='".jb_escape_sql($post_id)."' ";
	JB_mysql_query($sql) or $DB_ERROR = mysql_error();

	JBPLUG_do_callback('expire_post', $post_id); // col val is post_id

	if (JB_EMAIL_POST_EXP_SWITCH == 'YES') {

		// Send Expiration email
		$Form = JB_get_DynamicFormObject(1);
		$Form->load($post_id);	
	
		$TITLE = $Form->get_raw_template_value ("TITLE");
		$DATE = JB_get_formatted_date($Form->get_template_value ("DATE"));
		$POSTED_BY_ID = $Form->get_value('user_id');

		// get the employer
		$sql = "SELECT * FROM employers WHERE ID='".jb_escape_sql($POSTED_BY_ID)."' ";

		$emp_result = jb_mysql_query($sql);
		$emp_row = mysql_fetch_array($emp_result);

		// get the email template
		$template_result = JB_get_email_template (210, $emp_row['lang']); 
		$t_row = mysql_fetch_array($template_result);

		$to_address = $emp_row['Email'];
		$to_name = JB_get_formatted_name($emp_row['FirstName'], $emp_row['LastName']);
		$subject = $t_row['EmailSubject'];
		$message = $t_row['EmailText'];
		$from_name = $t_row['EmailFromName'];
		$from_address = $t_row['EmailFromAddress'];

		$message = str_replace("%LNAME%", $emp_row['LastName'], $message);
		$message = str_replace("%FNAME%", $emp_row['FirstName'], $message);
		$message = str_replace("%SITE_NAME%", JB_SITE_NAME, $message);
		$message = str_replace("%SITE_URL%", JB_BASE_HTTP_PATH, $message);
		$message = str_replace("%SITE_CONTACT_EMAIL%", JB_SITE_CONTACT_EMAIL, $message);
		$message = str_replace("%POST_TITLE%", $TITLE, $message);
		$message = str_replace("%POST_DATE%", $DATE, $message);
		$message = str_replace("%VIEWS%", $Form->get_value('hits'), $message);
		$message = str_replace("%APPS%", $Form->get_value('applications'), $message);

		$message = strip_tags($message);

		// plugin can change the recipient
		JBPLUG_do_callback('expire_post_set_recipient_email', $to_address);
		JBPLUG_do_callback('expire_post_set_recipient_name', $to_name);

		// Place the email on the queue!

		JB_queue_mail($to_address, $to_name, $from_address, $from_name, $subject, $message, '', 210);

	}

}

##################################################

function JB_render_postlist_nav_links (&$nav_pages_struct, $LINKS, $pp_page, $q_string='') {

	global $list_mode;
	global $label;
	global $JobListAttributes;
	$is_premium=false;
	
	$PLM = &JB_get_ListMarkupObject(); // load the ListMarkup Class
	

	$pipe = '';
	if ($list_mode=='PREMIUM') {
		echo $label['post_list_more_sponsored']." ";
		$is_premium=true;
	} 

	$page = htmlentities($_SERVER['PHP_SELF']);
	$page = str_replace('index.php', '', $page);

	if ($nav_pages_struct['cur_page'] > $LINKS-1) {
		$LINKS = round ($LINKS / 2)*2;
		$NLINKS = $LINKS;
	} else {
		$NLINKS = $LINKS - $nav_pages_struct['cur_page']; 
	}

	if (($nav_pages_struct['prev_page']) > -1) {

		echo $PLM->get_nav_prev_link(JB_job_result_page_url($nav_pages_struct['prev_page'], $pp_page, $is_premium, $JobListAttributes), $label["navigation_prev"]);
		
		
	}
	// links to pages before the current page
	$b_count = count($nav_pages_struct['pages_before']);
	for ($i = $b_count-$LINKS; $i <= $b_count; $i++) {
		if (isset($nav_pages_struct['pages_before'][$i])) {
			
			echo $PLM->get_nav_numeric_link(JB_job_result_page_url($nav_pages_struct['pages_before'][$i], $pp_page, $is_premium, $JobListAttributes), $i);//" | <a class='nav_page_link'  
			$seperator = $PLM->get_nav_seperator();
		}
	}
	// the current page, not a link
	echo $PLM->get_nav_current_page($seperator, $nav_pages_struct['cur_page']);
	
	// echo all the page links after the current page
	if (($nav_pages_struct['pages_after'])!='') { 
		$i=0;
		
		foreach ($nav_pages_struct['pages_after'] as $key => $pa ) {
			$i++;
			if ($i > $NLINKS) {
				break;
			}
			
			echo $PLM->get_nav_numeric_link(JB_job_result_page_url($pa, $pp_page, $is_premium, $JobListAttributes), $key);//" | 
		}
	}

	if ($nav_pages_struct['next_page']) {
	
		echo $PLM->get_nav_next_link(JB_job_result_page_url($nav_pages_struct['next_page'], $pp_page, $is_premium, $JobListAttributes), $label["navigation_next"]);
		
	}


}

#############################################################
# This should be called after deleting, approving, updating, bumping up
# moving and what-not of the job posts
function JB_finalize_post_updates() {

	JB_update_post_count(); // update the total, eg. number of approved posts, number of expired posts, premium approved, expired & waiting

	// refresh the category cache
	JB_cache_del_keys_for_all_cats(1);
	
	// refresh the rss feed
	require_once (JB_basedirpath()."rss.php");

	JBPLUG_do_callback('finalize_post_updates', $A=false);


}

?>