<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

require_once (dirname(__FILE__).'/category.inc.php');
require_once (dirname(__FILE__).'/lists.inc.php');


global $profile_tag_to_field_id;
global $profile_tag_to_search; 


// Load the Profile form object - and instance of JBDynamicForms.php
$ProfileForm = &JB_get_DynamicFormObject(3); 
$profile_tag_to_search = $ProfileForm->get_tag_to_search();
$profile_tag_to_field_id = $ProfileForm->get_tag_to_field_id();


#####################################

function JB_profile_tag_to_field_id_init () {
	
	global $label;

	global $profile_tag_to_field_id;
	if ($profile_tag_to_field_id = JB_cache_get('tag_to_field_id_3_'.$_SESSION['LANG'])) {
		return $profile_tag_to_field_id;
	}
	$fields = JB_schema_get_fields(3);
	
	// the template tag becomes the key
	foreach ($fields as $field) {
		$profile_tag_to_field_id[$field['template_tag']] = $field;
	}
	
	JBPLUG_do_callback('profile_tag_to_field_id_init', $profile_tag_to_field_id);
	JB_cache_set('tag_to_field_id_3_'.$_SESSION['LANG'], $profile_tag_to_field_id);
	return $profile_tag_to_field_id;
}

######################################################################
function JB_load_profile_values ($profile_id) { // alias for JB_load_profile_data()s
	return JB_load_profile_data($profile_id);
}

function JB_load_profile_data ($profile_id, $employer_id='') {
				
	if ($_REQUEST['show_emp'] && !$employer_id) {
		$employer_id = $_REQUEST['show_emp'];
	}
	$profile_id = (int) $profile_id;
	$employer_id = (int) $employer_id;
	
	if ($employer_id) {
		// Join the employers table to get the FirstName, LastName, CompName fields
		$sql = "SELECT profiles_table . * , FirstName, LastName, CompName
				FROM `employers`
				LEFT JOIN `profiles_table` ON profiles_table.user_id = employers.ID
				WHERE ID ='".jb_escape_sql($employer_id)."'";

	} else {
		$sql = "SELECT * FROM `profiles_table` WHERE profile_id='".jb_escape_sql($profile_id)."' ";
	}


	$result = JB_mysql_query($sql) or die ($sql. mysql_error());
	if ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$row['user_id'] = $employer_id;
	}

	JBPLUG_do_callback('load_profile_values', $row);
	
	return $row;


}
#########################################################

function JB_init_profile_values(&$data) {

	$form_id = 3;

	$data['profile_id']= (int) $_REQUEST['profile_id'];

	JB_init_data_from_request($form_id, $data);

	JBPLUG_do_callback('init_profile_values', $data);


}

#########################################################
# This function is deprecated. Please use instead:
# $ProfileForm = &JB_get_DynamicFormObject(3);
# $ProfileForm->display_form('edit', false);
function JB_display_profile_form ($form_id=3, $mode, &$passed_data, $admin) {

	global $label;
	global $error;
	
	if ($passed_data == null ) {
		JB_init_profile_values($passed_data);
	}

	JB_template_profile_form($mode, $admin);


}



###########################################################################

function JB_list_profiles ($admin=false,$order, $offset) {

	global $label; // languages array
	

    $records_per_page = 40;
    

   
   // process search result
	if ($_REQUEST['action'] == 'search') {
		$q_string = JB_generate_q_string(3);
		  	   
		$where_sql = JB_generate_search_sql(3);

	}
	   
	// JB_DATE_FORMAT(`adate`, '%d-%b-%Y') AS formatted_date

	$order = $_REQUEST['order_by'];

	if ($_REQUEST['ord']=='asc') {
		$ord = 'ASC';
	} elseif ($_REQUEST['ord']=='desc') {
		$ord = 'DESC';
	} else {
		$ord = 'DESC'; // sort descending by default
	}

	if (($order == '') || (!JB_is_field_valid($order, 3))) {
		// by default, order by the post_date
		$order = " `profile_date` ";           
	} else {
		$order = " `".jb_escape_sql($order)."` ";
	}
	$offset = (int) $_REQUEST['offset'];
	if ($offset<0) {
		$offset = abs($offset);
	}

	$sql = "Select SQL_CALC_FOUND_ROWS *, DATE_FORMAT(`profile_date`, '%d-%b-%Y') AS formatted_profile_date FROM `profiles_table` WHERE 1=1  $where_sql ORDER BY $order $ord LIMIT $offset, $records_per_page";

	//echo "[".$sql."]";

	$result = JB_mysql_query($sql) or die (mysql_error());
	############
	# get the count

	/*
	$count = mysql_num_rows($result);

	if ($count > $records_per_page) {

		mysql_data_seek($result, $offset);

	}

	*/

	$row = mysql_fetch_row(jb_mysql_query("SELECT FOUND_ROWS()"));
	$count = $row[0];
 

	if ($count > 0 )  {

		 if ($pages == 1) {
		   
	   } else {

			$pages = ceil($count / $records_per_page);
			$cur_page = $_REQUEST['offset'] / $records_per_page;
			$cur_page++;

			echo '<p class="nav_page_links">';
			//echo "Page $cur_page of $pages - ";
			$label["navigation_page"] =  str_replace ("%CUR_PAGE%", $cur_page, $label["navigation_page"]);
			$label["navigation_page"] =  str_replace ("%PAGES%", $pages, $label["navigation_page"]);
			echo "<span > ".$label["navigation_page"]."</span> ";
			$nav = JB_nav_pages_struct($result, $q_string, $count, $records_per_page);
			$LINKS = 10;
			JB_render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
			echo "</p>";


		}



		?>
		<table style="margin: 0 auto; width:100%; border:0px; background-color:d9d9d9; " cellspacing="1" cellpadding="5" >
		<tr bgcolor="#EAEAEA">
		<?php
		if ($admin == true ) {
			 echo '<td>&nbsp;</td>';
			 JBPLUG_do_callback('profile_list_head_admin_action', $A = false);
		}
		JBPLUG_do_callback('profile_list_head_user_action', $A = false);
		JB_echo_list_head_data(3, $admin);

		?>
		
		</tr>

		<?php
		$i=0; 
		
		$ProfileForm = &JB_get_DynamicFormObject(3);
		while (($row = mysql_fetch_array($result, MYSQL_ASSOC)) && ($i < $records_per_page)) {
			$ProfileForm->set_values($row);
			$i++;
	
		 ?>
			  <tr bgcolor="<?php echo JB_LIST_BG_COLOR; ?>" onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '<?php echo JB_LIST_HOVER_COLOR;?>', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);">
	
			  <?php
		  
		 if ($admin == true ) {
			 echo '<td>';

			 ?>
			 <input style="font-size: 8pt" type="button" value="Delete" onClick="if (!confirmLink(this, 'Delete, are you sure?')) {return false;} window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=delete&amp;profile_id=<?php echo $row['profile_id']; ?>'"><br>
				<input type="button" style="font-size: 8pt" value="Edit" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=edit&amp;profile_id=<?php echo $row['profile_id']; ?>'">

				<?php
			 
			 echo '</td>';
			 JBPLUG_do_callback('profile_list_data_admin_action', $A = false);
		 }
		 JBPLUG_do_callback('profile_list_data_user_action', $A = false);
			
		 JB_echo_proile_list_data($admin);

		  ?>


		</tr>
		  <?php
			 //$data[file_photo] = '';
			// $new_name='';
		}

		echo "</table>";
   
   } else {

      echo "<p class='profiles_no_result'>".$label["profiles_not_found"]."</p>";

   }


}

########################################################
function JB_delete_profile_files ($profile_id) {

	$sql = "select * from form_fields where form_id=3 ";
	$result = JB_mysql_query ($sql) or die (mysql_error());

	while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

		$field_id = $row['field_id'];
		$field_type = $row['field_type'];

		if (($field_type == "FILE")) {
			
			JB_delete_file_from_field_id("profiles_table", "profile_id", $profile_id, $field_id);
			
		}

		if (($field_type == "IMAGE")){
			
			JB_delete_image_from_field_id("profiles_table", "profile_id", $profile_id, $field_id);
			
		}
		
	}


}

####################

function JB_delete_profile ($profile_id) {

	 JB_delete_profile_files ($profile_id);
  

   $sql = "delete FROM `profiles_table` WHERE `profile_id`='".jb_escape_sql($profile_id)."' ";
   $result = JB_mysql_query($sql) or die (mysql_error().$sql);
   JBPLUG_do_callback('delete_profile', $profile_id);


}
################################

function JB_search_category_tree_for_profiles($cat_id=false, $field_id=false) {

	
	if ($cat_id==false) {
		$cat_id = (int) $_REQUEST['cat'];
	}

	if ($field_id!=false) {
		$field_id_sql = "AND field_id='".jb_escape_sql($field_id)."'"; 
	}


	$sql = "select * FROM form_fields WHERE field_type='CATEGORY' AND form_id='3' $field_id_sql";
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



####################

function JB_search_category_for_profiles() {

	if (func_num_args() > 0 ) {
		$cat_id = func_get_arg(0);
		
	} else {
		$cat_id = (int) $_REQUEST['cat'];
	}

	$sql = "select * from form_fields where field_type='CATEGORY' AND form_id='3'";
	$result = JB_mysql_query ($sql) or die (mysql_error());
	$i=0;

	if (mysql_num_rows($result) >0) {
		while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

			if ($i>0) {
				$where_cat .= " OR ";
			}

			$where_cat .= " `".$row['field_id']."`='$cat_id' ";
			$i++;
		}
	}

	if ($where_cat=='') {
		return " AND 1=2 ";
	}

	return " AND ($where_cat) ";
	//$sql ="Select * from posts_table where $where_cat ";
	//echo $sql."<br>";
	//$result2 = JB_mysql_query ($sql) or die (mysql_error());

}
##################

function JB_generate_profile_id () {

   $query ="SELECT max(`profile_id`) FROM `profiles_table";
   $result = JB_mysql_query($query) or die(mysql_error());
   $row = mysql_fetch_row($result);
   $row[0]++;
   return $row[0];

}



################################################################

function JB_insert_profile_data() {

	if (func_num_args() > 0) {
		$admin = func_get_arg(0); // admin mode.
	}

	$user_id = $_SESSION['JB_ID'];

	if ($_REQUEST['profile_id'] == false) {
		
		$assign = array (
			// static field defaults
			'profile_date' => gmdate("Y-m-d H:i:s"),
			'user_id' => $user_id,
			'expired' => 'N'
		);

		$sql = "REPLACE INTO `profiles_table` ( ".JB_get_sql_insert_fields(3, $assign).") VALUES (".JB_get_sql_insert_values(3, "profiles_table", "profile_id", $_REQUEST['profile_id'], $user_id, $assign).") ";

	} else {
		
		$profile_id = (int) $_REQUEST['profile_id'];

		if (!$admin) { // make sure that the logged in user is the owner of this resume.
			$sql = "select user_id from `profiles_table` WHERE profile_id='".jb_escape_sql($profile_id)."'";
			$result = JB_mysql_query ($sql) or die(mysql_error());
			$row = @mysql_fetch_array($result, MYSQL_ASSOC);
			if ($_SESSION['JB_ID']!==$row['user_id']) {
				echo "!";
				return false; // not the owner, hacking attempt!
			}
		}

		$now = (gmdate("Y-m-d H:i:s"));

		// the static fields that we want to have on the update
		$assign = array ( 
			'profile_date'=>gmdate("Y-m-d H:i:s"),
			'user_id'=>$_SESSION['JB_ID']
		);

		$sql = "UPDATE `profiles_table` SET  ".JB_get_sql_update_values (3, "profiles_table", "profile_id", $_REQUEST['profile_id'], $user_id, $assign)." WHERE profile_id='".jb_escape_sql($profile_id)."'";
		
	}
	
	
	JB_mysql_query ($sql) or die("[$sql]".mysql_error());

	if ($_REQUEST['profile_id']==false) {
		$profile_id = JB_mysql_insert_id();
	}

	JB_build_profile_count(0);

	JBPLUG_do_callback('JB_insert_profile_data', $profile_id);

	return $profile_id;
}
###############################################################
function JB_validate_profile_data($form_id) {

	$error = '';

	$errors = array();

	// Make sure they are numeric
	if ($_REQUEST['profile_id']!='') {
		if (!is_numeric($_REQUEST['profile_id'])) {
			return 'Invalid Input!';
		}
	}
	if ($_REQUEST['user_id']!='') {
		if (!is_numeric($_REQUEST['user_id'])) {
			return 'Invalid Input!';
		}
	}
	$_REQUEST['profile_date'] = JB_clean_str($_REQUEST['profile_date']);

	
	$error = '';
	JBPLUG_do_callback('JB_insert_profile_data', $error); // deprecated, use JB_insert_profile_data_array
	if ($error) {
		$list = explode('<br>', $error);
		foreach ($list as $item) {
			$errors[] = $item;
		}
	}

	JBPLUG_do_callback('JB_insert_profile_data_array', $errors); // added in 3.6.6

	$errors = $errors + JB_validate_form_data(3);

	return $errors;
	
	
}

############################################################

# Alias for JB_get_employer_name()

function JB_get_employer_company_name($user_id) {
	return JB_get_employer_name($user_id);
}

function JB_get_employer_name($user_id) {

	if (!is_numeric($user_id)) return false;
	
	global $JBMarkup;

	static $b_name; // cache it

	if (isset($b_name[$user_id])) return $b_name[$user_id]; // return cached value

	// perhaps the employer profile form was already loaded with the data? 
	// In that case lets see if we can get the employer name form there...

	$ProfileForm = &JB_get_DynamicFormObject(3);

	if (($ProfileForm->get_value('user_id'))==$user_id) { // already loaded
		
		$row['65'] = $ProfileForm->get_template_value('PROFILE_BNAME');
		$row['CompName'] = $ProfileForm->get_value('CompName');
		$row['FirstName'] = $ProfileForm->get_value('FirstName');;
		$row['LastName'] = $ProfileForm->get_value('LastName');;
		
	} else {

		$row = $ProfileForm->load(false, $user_id); // get be employer id
	}

	if (is_array($row)) {

		if (strlen(trim($row['65']))>0) {
			$b_name[$user_id] = $row['65']; 
		} elseif (strlen(trim($row['CompName']))>0) {
			$b_name[$user_id] = $row['CompName']; 
		} else {
			$b_name[$user_id] = JB_get_formatted_name($row['FirstName'], $row['LastName']); //
		}

	}

	return $b_name[$user_id];


}

?>