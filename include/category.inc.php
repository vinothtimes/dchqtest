<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

#################################

function JB_compute_cat_has_child() {

	// get all the categories that have a child
	// by joining the table with itself

	$sql = " SELECT t1.category_id AS CAT_ID
FROM categories AS t1, categories AS t2
WHERE t1.category_id = t2.parent_category_id
GROUP BY CAT_ID ";

	$result = jb_mysql_query($sql);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$sql = "UPDATE categories SET has_child='Y' WHERE category_id='".jb_escape_sql($row['CAT_ID'])."' ";
		
		jb_mysql_query($sql);
	}
	// now set the remaining NULL to 'N'
	$sql = "UPDATE categories SET has_child='N' WHERE has_child IS NULL ";
	jb_mysql_query($sql);


}



############################################
# This function checks cat_name_translations for each available
# language, and creates a new translation row if the category is not in the 
# translations table for that language
#
# It also calculates the search_set for each category
# 
function JB_init_category_tables ($cat) {

	global $AVAILABLE_LANGS;

	// loop for each language
	foreach  ($AVAILABLE_LANGS as $key => $val) {

		// get the category_name from the categories table, which also has a translation in the 
		// cat_name_translations table
		$sql = "SELECT categories.category_id, categories.category_name, lang FROM cat_name_translations, categories WHERE categories.category_id=cat_name_translations.category_id AND categories.category_id='".jb_escape_sql($cat)."' AND lang='".jb_escape_sql($key)."' ";
		
		$result = JB_mysql_query($sql) or die(mysql_error());
		// if the above query return no rows then it means that the row does not exist in 
		// the cat_name_translations table.
		// So we create a new row for the translation 
		if (mysql_num_rows($result)==0) {
			$cat_row = JB_get_category($cat);
			$sql = "REPLACE INTO `cat_name_translations` (`category_id`, `lang`, `category_name`) VALUES ('".jb_escape_sql($cat)."', '".jb_escape_sql($key)."', '".addslashes($cat_row['category_name'])."')";
			//echo "<b>$sql</b>";
			 JB_mysql_query($sql) or die (mysql_error().$sql);
		}
	
	}
	// Update the search set for the category
	$search_set = JB_get_search_set($cat);
	$sql = "UPDATE categories set search_set='".jb_escape_sql($search_set)."' WHERE category_id='".jb_escape_sql($cat)."'";
	JB_mysql_query($sql) or die (mysql_error().$sql);

	// This is a recursive function, so here we get the children categories
	// and call this function on the children.
	$query ="SELECT * FROM categories WHERE parent_category_id='".jb_escape_sql($cat)."' ";
	$result = JB_mysql_query ($query) or die(mysql_error().$query);  
	while ($row= mysql_fetch_array($result, MYSQL_ASSOC)) {
		JB_init_category_tables ($row['category_id']);
	}

}

##################################################


##################################################
# Globals
$withSubCat =0;
$s=0;
$form_id;
##################################################

# show all categories that are the children

function JB_showAllCat($child, $cols, $subCat, $lang, $f_id)
{
   global $withSubCat;
   global $catName;
   global $form_id;
   # initialise the global subcat flag
   $withSubCat = $subCat;
   $form_id = $f_id;

   # query to get all the nodes that are the 
   # children of child id

    $query = "SELECT categories.category_id, categories.category_name, lang, cat_name_translations.category_name AS NAME, obj_count, allow_records, seo_fname, seo_title, seo_desc, seo_keys FROM categories LEFT JOIN cat_name_translations ON categories.category_id=cat_name_translations.category_id WHERE parent_category_id='".jb_escape_sql($child)."' AND (lang='".jb_escape_sql($_SESSION['LANG'])."') and form_id='".jb_escape_sql($form_id)."' ORDER BY list_order , NAME ";

	//echo "$query";

   $x=0;
   # do the query
   $result = JB_mysql_query ($query) or die($query. mysql_error());
   while ($row = mysql_fetch_row($result)) {
      $cats[] = $row;
      $x++;
      if ($x==$cols) {
         JB_showRow($cats);
         unset($cats); # clear array
         $x=0;
      } 
   }
   # show the remaining cats
   JB_showRow($cats);

}

############################################################
function JB_showRow ($cats) {
      echo "<tr>";

   for ($x=0; $x < count($cats); $x++) {
      JB_showCat($cats[$x]);
   }
      echo "</tr>";


}



############################################################

function JB_showCat ($cat) {
   global $withSubCat;
   global $MODE;

   echo "<td valign=top width='33%'>";
   echo '<img src="folder.gif" border="0">';
   echo "<A HREF=\"".htmlentities($_SERVER['PHP_SELF'])."?cat=".$cat[0]."\"> <span class='cat_heading'>".jb_escape_html($cat[3])."</span></A>"; //echo " (ID: ". ($cat[0]).") ";
	if  ($cat[5]=='N') echo "<b>&#8224;</b>";
	if ($MODE == 'ADMIN') {

	   ?>

	   <a onClick="return confirmLink(this, 'Delete this category, are you sure? (This will also delete all sub-categories in this category)') " href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=del&category_id=<?php echo $cat[0]?>"><IMG src='delete.gif' width='16' height='16' border='0' alt='Delete'></a>

	   <a href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?edit=<?php echo $cat[0];?>&cat=<?php echo $cat[0];?>"><IMG alt="edit" src="edit.gif" width="16" height="16" border="0" alt="Edit"></a>

   <?php

	}  elseif  ($MODE == 'REWRITE') {
		// categorty id, name, file name
		$url = JB_cat_url_write($cat[0], $cat[1], $cat[6]);

		if ($cat[7]=='') {
			$cat[7] = $cat[1]." - ".JB_SITE_NAME;
		}
		
		//  JB_echo_cat_seo_fields($url, $fname, $title, $desc, $keys)
		JB_echo_cat_seo_fields($cat[0], $url, $cat[6], $cat[7], $cat[8], $cat[9], $cat[10]);
		?>
		<?php

	}

	?>

   <?php
   if ($withSubCat) {
      JB_showSubcat($cat[0]);
   }
   echo "</td>";


}

#################################################################################
# Show sub-categories
# This function is used by Admin to show category editing fields, or fields
# for editing the mod rewrite options (Admin).
# JB_showCat() is the parent function

function JB_showSubcat ($c) {

	global $MODE; // eg 'ADMIN', or 'REWRITE'

   $query = "SELECT t1.category_id, t1.category_name, t2.category_name, obj_count, allow_records, seo_fname, seo_title, seo_desc, seo_keys, has_child FROM categories as t1, cat_name_translations as t2 WHERE t1.category_id=t2.category_id AND parent_category_id='".jb_escape_sql($c)."' and t2.lang='".$_SESSION['LANG']."' order by t1.list_order,  t2.category_name ASC ";
   
   $result = JB_mysql_query ($query ) or die(mysql_error());

   $x=0;
   echo "<br><div style='margin-left: 20px;'>";
   while ($row = mysql_fetch_row($result)) {
      $x++;
      //if ($x > JB_SHOW_SUBCATS) break;

//print_r($row);
	  
	  echo '<img src="folder.gif" border="0">';
	  if ($row[9]=='Y') {
		echo ' <a href="'.htmlentities($_SERVER['PHP_SELF']).'?cat='.$row[0].'">'.jb_escape_html($row[2])."</a>";

	  } else {
		  echo ' '.jb_escape_html($row[2]);
	  }
	  if  ($row[4]=='N') echo "<b>&#8224;</b>";


	if ($MODE == 'ADMIN') {

		?>

		<a onClick="return confirmLink(this, 'Delete this category, are you sure? (This will also delete all sub-categories in this category)') " href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=del&category_id=<?php echo $row[0]?>"><IMG src='delete.gif' width='16' height='16' border='0' alt='Delete'></a>

		<a href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?edit=<?php echo $row[0];?>&cat=<?php echo $row[0];?>"><IMG src="edit.gif" width="16" height="16" border="0" alt="Edit"></a>

		<?php

	} elseif  ($MODE == 'REWRITE') {

		$url = JB_cat_url_write($row[0], $row[2], $row[5]);
		if ($row[6]=='') {
			$row[6] = $row[1]." - ".JB_SITE_NAME;
		}

		JB_echo_cat_seo_fields($row[0], $url, $row[5], $row[6], $row[7], $row[8], $row[9]);
		?>
		<?php

	}

	echo "<br>";

	

   }
  /* if ($x > JB_SHOW_SUBCATS)
   echo "[<a href='".htmlentities($_SERVER['PHP_SELF'])."?cat=$c'><b>More...</b></a>]</div>";
   else
   */
   echo "</div>";


}

##################################
# check to make sure that the url is unique

function JB_check_cat_rewrite_url($url, $form_id=1) {

	// get the cat name from the url
	// PCRE_DOLLAR_ENDONLY
	echo " url:[$url]";
	
	

}

##################################################
# For the Admin

function JB_echo_cat_seo_fields($id, $url, $fname, $title, $desc, $keys, $dir_name) {

	//$url = urldecode($url);


	preg_match ("#/([^/]+)$#D", urldecode($url), $m);
	$matched_file_name = $m[1];
	
	if (!JB_get_cat_id_from_url(($matched_file_name), $form_id=1)) {
		$amb = true; // the url is ambiguous
		echo " <font color='red'>$url [Warning: ambiguous filename, please choose a unique filename below]</font> ";
	} else {
		$amb = false;
	}

	if ($fname=='') {	
		$fname = $matched_file_name;
	} 

	$fname = JB_utf8_to_html($fname);

	?>

	<br>
	<b>Path / File:</b> <?php echo JB_BASE_HTTP_PATH.JB_MOD_REWRITE_DIR;?><input name='file_<?php echo $id; ?>' size='40' <?php if ($amb) echo ' style="background-color:#FCDEC9;" '; ?> type='text' value="<?php echo jb_escape_html($fname); ?>"><br>
	<b>Title:</b> <input name='title_<?php echo $id; ?>' size='80' type='text' value="<?php echo jb_escape_html($title); ?>"><br>
	<b>Description:</b> <input name='desc_<?php echo $id; ?>' size='80' type='text' value="<?php  echo jb_escape_html($desc); ?>"><br>
	<b>Keywords:</b> <input name="keys_<?php echo $id; ?>" size='80' type='text' value="<?php echo jb_escape_html($keys); ?>"><br>

	<?php


}

#################################################################
# Deprecated. Use JB_getPath instead 
function getPath($c) {

   $p = "function getPath Deprecated. Use JB_getPath_templated() instead ";
   return $p;
}


#################################################################
# Get the bread-crumb links for the category $c
# eg.
# Location -> California -> LA
# Where Location, California and LA are all links to a category
# LA is the $c category
# Output of this function is ready for template output
function JB_getPath_templated($c) {

	static $p;

	if (!isset($p)) { // get from cache
		$p=jb_cache_get('cat_path_'.$_SESSION['LANG']);
	}

	if (isset($p[$c])) {
		return $p[$c];
	} else {
		$p[$c] = JB_findPath($c, "");
		JB_cache_set('cat_path_'.$_SESSION['LANG'], $p);
		return $p[$c];
	}
	
	
}

# Alias for function JB_getPath_templated($c)
function JB_getPath($c) {
	return JB_getPath_templated($c);
}


###############################################################
# Generate the search set 
function JB_get_search_set($c, $set='') {
	if ($set!='') {
		$comma = ',';
	}
	# query that will get all the child nodes;
	$query ="SELECT category_id, has_child FROM categories WHERE parent_category_id='".jb_escape_sql($c)."'   ";
	$result = JB_mysql_query ($query) or die(mysql_error().$query);  
	while ($row = mysql_fetch_row($result)) {  
		$set = $set.$comma.$row[0];
		$comma = ',';
		if ($row[1]=='Y') { // has_child == Y?
			$set = JB_get_search_set ( $row[0], $set);
		}
	}

	// sort the search set from lowest to highest
	$temp = explode(',', $set);
	sort($temp, SORT_NUMERIC);
	$set = implode(',', $temp);
	
	return $set;

}

# Like the JB_get_search_set() function but returns
# a shorter query with < and > signs to be used in
# the WHERE part of the suwry

function JB_get_optimized_search_set_query($search_set, $field_id) {

	if (strlen($search_set)>255) {

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
				$where_range .= " $range_or  ((`".$field_id."` >= $start) AND (`".$field_id."` <= $end)) ";
			} elseif ($start!='') {
				$where_range .= " $range_or  (`".$field_id."` = $start ) ";
			}
			$start='';$end='';
			$range_or = "OR";
		}
		$search_set = " AND (".$where_range.")";

	} else {
		$search_set = ' AND  `'.$field_id.'` IN ('.$search_set.') ';
	}

	return $search_set;

}





###############################################################
# Display the path. Recursive function.
# The output of this function is escaped, ready for template output
function JB_findPath($c, $path) {

	global $MODE;

	$DFM = &JB_get_DynamicFormMarkupObject();

	if (!is_numeric($c)) return false;

	$sql = "SELECT t1.category_name, t1.parent_category_id, t2.category_name, seo_fname  FROM categories as t1, cat_name_translations as t2 WHERE t1.category_id=t2.category_id AND t1.category_id='".jb_escape_sql($c)."' AND t2.lang = '".jb_escape_sql($_SESSION['LANG'])."' ";

	$result = JB_mysql_query($sql) or die("<b>$sql</b>".mysql_error());
	if (mysql_num_rows($result)>0) {
		$row = mysql_fetch_row($result);

		if ($path == "") {
			$arrow = ""; // leaf
		} else {
			$arrow = $DFM->get_category_breadcrumb_seperator();//'-&gt;';
		}
		if (strpos(strtolower($_SERVER['PHP_SELF']), '/admin') !== false) {
			$url = htmlentities($_SERVER['PHP_SELF']).'?cat='.$c;
		} else {
			$url = JB_cat_url_write($c, $row[2], $row[3]);
		}
		$path = $DFM->get_category_breadcrumb_link($url, $row[2]).$arrow;

		$path = JB_findPath($row[1], $path).$path;   
		return $path;
	  
	}
 
}

###############################################################
# get the path as an array. Recursive function.
# From leaf=0 to root=n
function JB_findPath_array($c, &$arr, $lang='') {

	if ($lang=='') {
		$lang = $_SESSION['LANG'];
	}

	if (!is_numeric($c)) return false;
	
	$query = "SELECT t1.category_name, t1.parent_category_id, t2.category_name, seo_fname  FROM categories as t1, cat_name_translations as t2 WHERE t1.category_id=t2.category_id AND t1.category_id='$c' AND t2.lang = '".$lang."' ";

	$result = JB_mysql_query($query) or die("<b>$query</b>".mysql_error());
	if (mysql_num_rows($result)>0) {
		$row = mysql_fetch_row($result);

		$arr[] = $row[2];

		JB_findPath_array($row[1], $arr, $lang);
		return $arr;
	  
	}
 
}


###############################################################
# Get the name of the category straight from the database

function JB_getCatName($c) {
	static $jb_cat_names;
	if (!is_numeric($c)) return false;

	if (!isset($jb_cat_names)) { // get it from the cache
		$jb_cat_names = jb_cache_get('jb_cat_names_'.$_SESSION['LANG']);
	}
	if (isset($jb_cat_names[$c])) {
		return $jb_cat_names[$c];
	} else {
	
	   $query = "SELECT category_name FROM cat_name_translations WHERE category_id ='".jb_escape_sql($c)."' and lang='".jb_escape_sql($_SESSION['LANG'])."' ";

	   $result = JB_mysql_query($query) or die(mysql_error());
	   $row = mysql_fetch_row($result);
	   $jb_cat_names[$c] = $row[0]; 
	   jb_cache_set('jb_cat_names_'.$_SESSION['LANG'], $jb_cat_names); // cache it, overwrite original
	   
	   return $row[0];
	}
}

###############################################################

function JB_getCatParent($c) {
	if (!is_numeric($c)) return false;
	$query = "SELECT parent_category_id FROM categories WHERE category_id ='".jb_escape_sql($c)."'";
	$result = JB_mysql_query($query) or die(mysql_error().$query);
	$row = mysql_fetch_row($result);
	return $row[0];
}

###################################################################
function JB_showCatOptions ( $node, $path) {

	if (!is_numeric($node)) return false;

   # query that will get all the child nodes;
$query ="SELECT category_id, category_name FROM categories WHERE parent_category_id=$node order by list_order ,  category_name  ";
   $result = JB_mysql_query ($query) or die(mysql_error());  
      while ($row = mysql_fetch_row($result)) {
         $prev = $path;
         $path = "$path -- $row[1]";
         echo "<option value=$row[0]>$path</option>\n";
         JB_showCatOptions ( $row[0], $path);
         $path = $prev;
      }
   // no more trees returned
   return;

}



##########################################################

function JB_add_new_cat_form ($parent) {


?>
<hr>

<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
Enter a new category name below. You can add additional categories on each new line.<br>

<textarea cols="30" name="new_cat" rows="4"></textarea>
<input type="checkbox" name="allow_records" value="ON" id="id01" checked> <label for="id01">Allow records to be added to this/these category/categories.</label><br>
<?php
echo "<input type='hidden' name='cat' value='$parent'>";

?>
<input type='submit' value="Add"><br>

<?php


}

###################################################################

function JB_add_cat ( $catname, $parent, $form_id, $allow_records) {

   
	$id = JB_db_generate_id_fast("category_id", "categories");

	$query = "INSERT INTO categories (category_id, category_name, parent_category_id, form_id, allow_records, search_set) VALUES ($id, '".jb_escape_sql($catname)."', ".jb_escape_sql($parent).", ".jb_escape_sql($form_id).", '".jb_escape_sql($allow_records)."', '')";

	$result = JB_mysql_query($query) or die($query.mysql_error());

	$sql = "REPLACE INTO `cat_name_translations` (`category_id`, `lang`, `category_name`) VALUES (".jb_escape_sql($id).", '".jb_escape_sql($_SESSION["LANG"])."', '".jb_escape_sql($catname)."')";

	$result = JB_mysql_query($sql) or die (mysql_error().$sql);

	return $id;

}


###################################################################

function JB_del_cat_recursive ($category_id) {
	
	$query ="SELECT * FROM categories WHERE category_id='".jb_escape_sql($category_id)."' ";
	$result = JB_mysql_query ($query) or die (mysql_error().$query);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	if (($row['obj_count'] > 0) && ($_REQUEST['confirm']==false)) {
		
		return -$row['obj_count'];

	}

	$query ="DELETE	FROM categories WHERE category_id='".jb_escape_sql($category_id)."' ";
    JB_mysql_query ($query) or die(mysql_error().$query);

	$query ="DELETE	FROM cat_name_translations WHERE category_id='".jb_escape_sql($category_id)."' ";
    JB_mysql_query ($query) or die(mysql_error().$query);
	
	$query ="SELECT * FROM categories WHERE parent_category_id='".jb_escape_sql($category_id)."' ";
	$result = JB_mysql_query ($query) or die(mysql_error().$query);  
	while ($row= mysql_fetch_array($result, MYSQL_ASSOC)) {
		JB_del_cat_recursive ($row['category_id']);
	}

   
}



###################################################################
# Returns the array $row of the $category_id.
# $row['NAME'] - the translated category name, according to the set 
# session 'LANG' variable
# $row also contains seo_title, seo_desc, seo_keys, seo_fname
#
function JB_get_category($category_id) {

	if (!is_numeric($category_id)) return false;

	$sql = "select *, t1.category_name AS NAME, seo_title, seo_desc, seo_keys, seo_fname  FROM cat_name_translations as t1, categories as t2 ".
		   "WHERE t1.category_id=t2.category_id AND t2.category_id='".jb_escape_sql($category_id)."' and lang='".jb_escape_sql($_SESSION['LANG'])."'";

	$result = JB_mysql_query($sql) or die (mysql_error());
	return mysql_fetch_array($result, MYSQL_ASSOC);


}

function update_category_counter($cat_id) {


}

function update_category_counter_totals() {


}

##################################################
# For efficency, the function
# JB_update_post_category_counters() does not update the whole tree, but only
# the categories on the branch starting from the leaf and going to the root. 
# $leaf_cat_id - The category_id of the leaf to start with
# $field_id - the field_id from the posts_table
# This function is recursive.

function JB_update_post_category_counters($leaf_cat_id, $field_id, $search_set='') {

	static $level;

	$level++;
	$row = jb_get_category($leaf_cat_id);


	if (strlen(trim($search_set))==0) {
		
		$sql = "SELECT search_set FROM categories WHERE  category_id='".jb_escape_sql($leaf_cat_id)."' AND search_set != '' ";
		
		$result = jb_mysql_query($sql);
		if (mysql_num_rows($result)>0) {
			$search_set = array_pop(mysql_fetch_row($result));	
		}
		
	}
	if (strlen($search_set)>0) {
		$search_set .= ','.$leaf_cat_id; 
	} else {
		$search_set = $leaf_cat_id;
	}
	$search_set_sql = JB_get_optimized_search_set_query($search_set, $field_id);

	
	$sql = "SELECT count(*) FROM posts_table WHERE  approved='Y' AND expired='N' ".$search_set_sql." ";

	$result = JB_mysql_query($sql) or die(mysql_error().$sql);
	$row = mysql_fetch_row($result);
	$count = $row[0];

	$sql = "UPDATE categories SET obj_count='$count' WHERE category_id='".jb_escape_sql($leaf_cat_id)."' AND form_id=1 ";
	JB_mysql_query ($sql) or die (mysql_error().$sql);
	

	// go down the tree to the root
	$sql = "SELECT category_id, parent_category_id, search_set FROM categories WHERE category_id='".jb_escape_sql($leaf_cat_id)."' ";
	$result = JB_mysql_query ($sql) or die (mysql_error().$sql);
	if ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		
		if (mysql_num_rows($result) > 0 ) {
			$sql = "SELECT category_id, search_set from categories WHERE category_id='".jb_escape_sql($row['parent_category_id'])."' ";
			
			$result = JB_mysql_query($sql) or die (mysql_error());
			if (mysql_num_rows($result) > 0 ) {
				$row = mysql_fetch_array($result, MYSQL_ASSOC);
				
				JB_update_post_category_counters($row['category_id'], $field_id, $row['search_set']);
			}
		}
	}

	$level--;
	
}

######################################################
# This updates all the categories of the post form, for all the category fields

# $cat parameter deprecated, function no-longer recursive
function JB_build_post_count ($cat=0) {

	$sql = "SELECT category_id FROM categories WHERE form_id=1 ";
	$result = JB_mysql_query ($sql) or die (mysql_error().$sql);

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		# cat_sql grabs all the CATEGORY field_ids for the form_fields, and puts it in to a query
		# to look for $row['category_id'] in the category field_ids of the table 
		$cat_sql = JB_search_category_tree_for_posts($row['category_id']);
		if (trim($cat_sql)=='AND 1=2') {
			return 0; // there are no category fields to count
		}

		$sql = "SELECT count(*) FROM posts_table  WHERE approved='Y' and expired='N' $cat_sql ";
		$result2 = JB_mysql_query($sql) or die(mysql_error().$sql);
		$count = array_pop(mysql_fetch_row($result2));
	
		$sql = "UPDATE categories SET obj_count='$count' WHERE category_id='".jb_escape_sql($row['category_id'])."' AND form_id='1' ";
		JB_mysql_query ($sql) or die (mysql_error().$sql);
		if ($count>$max_count) {
			$max_count = $count;
		}
	}
	return $max_count;


}




######################################################
# This updates all the categories of the resume form, for all the category fields
# On the resume form.

# $cat parameter deprecated, function no-longer recursive
function JB_build_resume_count ($cat=0) {

	$sql = "SELECT category_id FROM categories WHERE form_id=2 ";
	$result = JB_mysql_query ($sql) or die (mysql_error().$sql);

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		# cat_sql grabs all the CATEGORY field_ids for the form_fields, and puts it in to a query
		# to look for $row['category_id'] in the category field_ids of the table 
		$cat_sql = JB_search_category_tree_for_resumes($row['category_id']);
		if (trim($cat_sql)=='AND 1=2') {
			return 0; // there are no category fields to count
		}

		$sql = "SELECT count(*) FROM resumes_table  WHERE approved='Y' and expired='N' $cat_sql ";
		$result2 = JB_mysql_query($sql) or die(mysql_error().$sql);
		$count = array_pop(mysql_fetch_row($result2));
	
		$sql = "UPDATE categories SET obj_count='$count' WHERE category_id='".jb_escape_sql($row['category_id'])."' AND form_id='2' ";
		JB_mysql_query ($sql) or die (mysql_error().$sql);
		if ($count>$max_count) {
			$max_count = $count;
		}
	}
	return $max_count;


}


######################################################
# $cat parameter deprecated, function no-longer recursive
function JB_build_profile_count ($cat=0) {

	
	$sql = "SELECT category_id FROM categories WHERE form_id=3 ";
	$result = JB_mysql_query ($sql) or die (mysql_error().$sql);

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		# cat_sql grabs all the CATEGORY field_ids for the form_fields, and puts it in to a query
		# to look for $row['category_id'] in the category field_ids of the table 
		$cat_sql = JB_search_category_tree_for_profiles($row['category_id']);
		if (trim($cat_sql)=='AND 1=2') {
			return 0; // there are no category fields to count
		}

		$sql = "SELECT count(*) FROM profiles_table  WHERE expired='N' $cat_sql ";
		$result2 = JB_mysql_query($sql) or die(mysql_error().$sql);
		$count = array_pop(mysql_fetch_row($result2));

		$sql = "UPDATE categories SET obj_count='$count' WHERE category_id='".jb_escape_sql($row['category_id'])."' AND form_id='3' ";
		JB_mysql_query ($sql) or die (mysql_error().$sql);
		if ($count>$max_count) {
			$max_count = $count;
		}
	}
	

}

######################################################
# $cat parameter deprecated, function no-longer recursive
function JB_build_advertiser_count ($cat=0) {

	$sql = "SELECT category_id FROM categories WHERE form_id=4 ";
	$result = JB_mysql_query ($sql) or die (mysql_error().$sql);

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		# cat_sql grabs all the CATEGORY field_ids for the form_fields, and puts it in to a query
		# to look for $row['category_id'] in the category field_ids of the table 
		$cat_sql = JB_search_category_for_users($row['category_id']);
		if (trim($cat_sql)=='AND 1=2') {
			return 0; // there are no category fields to count
		}

		$sql = "SELECT count(*) FROM employers  WHERE expired='N' $cat_sql ";
		$result2 = JB_mysql_query($sql) or die(mysql_error().$sql);
		$count = array_pop(mysql_fetch_row($result2));
	
		$sql = "UPDATE categories SET obj_count='$count' WHERE category_id='".jb_escape_sql($row['category_id'])."' AND form_id='4' ";
		JB_mysql_query ($sql) or die (mysql_error().$sql);
		if ($count>$max_count) {
			$max_count = $count;
		}
	}
	return $max_count;

	


}

######################################################
# $cat parameter deprecated, function no-longer recursive
function JB_build_seeker_count ($cat=0) {

	$sql = "SELECT category_id FROM categories WHERE form_id=5 ";
	$result = JB_mysql_query ($sql) or die (mysql_error().$sql);

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		# cat_sql grabs all the CATEGORY field_ids for the form_fields, and puts it in to a query
		# to look for $row['category_id'] in the category field_ids of the table 
		$cat_sql = JB_search_category_for_employers($row['category_id']);
		if (trim($cat_sql)=='AND 1=2') {
			return 0; // there are no category fields to count
		}
		echo "[$cat_sql]<br>";
		$sql = "SELECT count(*) FROM users  WHERE expired='N' $cat_sql ";
		$result2 = JB_mysql_query($sql) or die(mysql_error().$sql);
		$count = array_pop(mysql_fetch_row($result2));
		echo $row['category_id']." count: $count<br>";
		$sql = "UPDATE categories SET obj_count='$count' WHERE category_id='".jb_escape_sql($row['category_id'])."' AND form_id='5' ";
		JB_mysql_query ($sql) or die (mysql_error().$sql);
		if ($count>$max_count) {
			$max_count = $count;
		}
	}
	return $max_count;

	

}


##########################################################

# show all categories that are the children



function JB_getCatStruct($cat_id, $lang, $f_id) {
  

	if (!is_numeric($f_id)) return false;

	if ($cat_id==false) $cat_id='0';

	if ($cat_struct=jb_cache_get("cat_f".$f_id."_c".$cat_id."_".$_SESSION['LANG'])) {
		return $cat_struct;
	}
	

   # query to get all the nodes that are the 
   # children of child id

    $query = "SELECT categories.category_id, categories.category_name, lang, cat_name_translations.category_name AS NAME, obj_count, seo_fname, has_child FROM categories LEFT JOIN cat_name_translations  ON categories.category_id=cat_name_translations.category_id WHERE parent_category_id='".jb_escape_sql($cat_id)."' AND (lang='".jb_escape_sql($lang)."') and form_id=$f_id ORDER BY list_order, NAME ";


   $x=0;
   
   $result = JB_mysql_query ($query) or die($query. mysql_error());
   $i=0;
   while ($row = mysql_fetch_row($result)) {
	   $children = array();
	   if ($row[6]=='Y') {
		   $children = JB_getCategoryChildrenStruct($row[0], $lang, $f_id);
	   }
	   
	   $category_table[$i]['cid'] = $row[0]; // category id
	   $category_table[$i]['cpid'] = $cat_id; // parent id
	  // $category_table[$i]['t'] = "PARENT"; // type
	   $category_table[$i]['n'] = $row[3]; // name
	   $category_table[$i]['oc'] = $row[4]; // object count
	   $category_table[$i]['ch'] = $children; // children
	   $category_table[$i]['chc'] = sizeof($children);	// children count 
	   $category_table[$i]['seo'] = $row[5]; // seo file name

		$i++;
	  
     
   }
   jb_cache_set("cat_f".$f_id."_c".$cat_id."_".$_SESSION['LANG'], $category_table);
   return $category_table;
   

}


###################################################################################

function JB_getCategoryChildrenStruct($cat_id, $lang, $f_id) {
	if (!is_numeric($cat_id) || ($cat_id < 1)) return false;
	$children = array();

	 $query = "SELECT categories.category_id, categories.category_name, lang, cat_name_translations.category_name AS NAME, obj_count, seo_fname FROM categories LEFT JOIN cat_name_translations  ON categories.category_id=cat_name_translations.category_id WHERE parent_category_id='".jb_escape_sql($cat_id)."' AND (lang='".$lang."') and form_id='$f_id' ORDER BY list_order, NAME ASC "; // removed: obj_count DESC,
	
	 $result = JB_mysql_query ($query) or die($query. mysql_error());

	$i=0;
	 while ($row = mysql_fetch_row($result)) {
	    $children[$i]['cid'] = $row[0];
		$children[$i]['t'] = "CHILD";
		$children[$i]['n'] = $row[3];
		$children[$i]['oc'] = $row[4];
		$children[$i]['seo'] = $row[5];
		
	   
	   $i++;

	 }

	 return $children;



}
###########################################
# Display category structure.
# The following function will break up the array
# into equal portions, and arrange them into columns

function JB_display_categories(&$cats) {

	# HTML output for this function comes from CategoryMarkup Class
	# include/themes/default/JBCategoryMarkup.php
	# Any HTML customizations should be done there.
	# Please copy this class in to your custom theme directory, and
	# customize form there
	
	global $label;

	$CL = &JB_get_CategoryMarkupObject();

	if (func_num_args() > 1) {
		$COLS = func_get_arg(1);
		
	} else {
		$COLS = 2;
	}

	$COLS = $CL->set_cols($COLS);

	if (func_num_args() > 2) {
		$JB_FORMAT_SUB_CATS = func_get_arg(2);
	} else {
		$JB_FORMAT_SUB_CATS = JB_FORMAT_SUB_CATS;
	}

	$parents = (sizeof($cats)); 
	$max = ceil ($parents / $COLS); // how many cats per column
	$width = 100 / $COLS;
	$index=0;

	$CL->echo_open_categories();

	?>
	
	<?php

	for ($c = $COLS; $c > 0; $c--) {
		$CL->echo_open_categories_cell($width);
		
		$max = ceil ($parents / $c);
		for ($i = 0; $i < $max; $i++) {
			$parents--;

			$CL->echo_parent_link($cats[$index]);
		
			if (JB_CAT_SHOW_OBJ_COUNT=='YES') {
				$CL->echo_count($cats[$index]['oc']);
			}
			$CL->echo_before_subcat_line_break(); 
			if  (($JB_FORMAT_SUB_CATS=='YES') ) { 
				JB_display_sub_cats_table($cats, $index); 
			} else { 
				JB_display_sub_cats_compact($cats, $index);
			}
			$index++;

		}
		$CL->echo_close_categories_cell();
		
	}
	$CL->echo_close_categories();


}




function JB_display_sub_cats_table(&$cats, $index) {

	# HTML output for this function comes from CategoryMarkup Class
	# include/themes/default/JBCategoryMarkup.php
	# Any HTML customizations should be done there.
	# Please copy this class in to your theme custom directory, and
	# customize form there

	global $label;

	$CL = &JB_get_CategoryMarkupObject();

	$JB_SUB_CATEGORY_COLS = $CL->set_sub_category_cols();
	if ($cats[$index]['chc']==false) { return; } // there are no children in this cat

	$children = $cats[$index]['ch'];

	$CL->echo_open_sub_cat();

	$j=0; // $j counts the number of columns
	$sub_width = 100 / $JB_SUB_CATEGORY_COLS;
	for ($x=0; $x < $cats[$index]['chc']; $x++) {
		$ch_cat_name_anchor = $children[$x]['n'];
		if (JB_CAT_NAME_CUTOFF == 'YES') {
			$ch_cat_name_anchor = JB_truncate_html_str($ch_cat_name_anchor, JB_CAT_NAME_CUTOFF_CHARS, $trunc_str_len);
		} 
		if ($j==0) { 
			$CL->echo_open_sub_cat_row(); 
			$tr_open=true;
		}
		$j++;
		
		if ($x >= JB_SHOW_SUBCATS) {
			if (!JB_SHOW_SUBCATS) break; // do not show 'more' link when JB_SHOW_SUBCATS is 0
			$CL->echo_open_sub_cat_cell($sub_width.'%');
			// reached the maximum we can show
			// echo a 'More..' link
			$CL->echo_sub_cat_more_link($cats[$index], $label['category_expand_more']);
			$CL->echo_close_sub_cat_cell();
			break;
		} else {
			$CL->echo_open_sub_cat_cell($sub_width.'%');
			$CL->echo_sub_cat_link($children[$x], $ch_cat_name_anchor);
			if (JB_CAT_SHOW_OBJ_COUNT=='YES') {
				$CL->echo_sub_cat_count($children[$x]['oc']);
			}
			$CL->echo_close_sub_cat_cell();
		}
		
		if ($j>=$JB_SUB_CATEGORY_COLS) {
			$CL->echo_close_sub_cat_row(); 
			$j=0; 
			$tr_open=false;
		}
	}
	if (($j < $JB_SUB_CATEGORY_COLS) && ($tr_open)) { // render the remaining cells
		for ($j=$j; $j < $JB_SUB_CATEGORY_COLS; $j++) {
			$CL->echo_sub_cat_empty_cell();
		}
		$CL->echo_close_sub_cat_row();
	}
	$CL->echo_close_sub_cat();


}

######################################


function JB_display_sub_cats_compact(&$cats, $index) {

	# HTML output for this function comes from CategoryMarkup Class
	# include/themes/default/JBCategoryMarkup.php
	# Any HTML customizations should be done there.
	# Please copy this class in to your custom theme directory, and
	# customize form there

	global $label;
	$CL = &JB_get_CategoryMarkupObject();
	$children = $cats[$index]['ch'];
	$space = "";
	$CL->echo_open_sub_cat_c();
	
	for ($x=0; $x < $cats[$index]['chc']; $x++) {

		if ($x >= JB_SHOW_SUBCATS) {
			if (!JB_SHOW_SUBCATS) break; // do not show 'more' link when JB_SHOW_SUBCATS is 0
			$CL->echo_sub_cat_c_more_link($cats[$index], $label["category_expand_more"]);
			break;
		}

		$ch_cat_name_anchor = $children[$x]['n'];
		if (JB_CAT_NAME_CUTOFF == 'YES') {
				$ch_cat_name_anchor = JB_truncate_html_str($ch_cat_name_anchor, JB_CAT_NAME_CUTOFF_CHARS, $trunc_str_len);
		}
		$CL->echo_sub_cat_c_link($children[$x], $ch_cat_name_anchor, $space);
		
		if (JB_CAT_SHOW_OBJ_COUNT=='YES') {
			$CL->echo_sub_cat_c_count($children[$x]['oc']);
		}
		$space = $CL->sub_cat_c_set_space();
		//if ($x > JB_SHOW_SUBCATS) {

		//	$CL->echo_sub_cat_c_more_link($cats[$index], $label["category_expand_more"]);

		//	break;
		//}
	}
	$CL->echo_close_sub_cat_c();


}

////////////////////

function JB_match_category_id_from_name($name, $form_id=1, $lang='') {
	if (!$lang) {
		$lang = JB_get_default_lang();
	}
	$sql = "SELECT t1.category_id as CID FROM `categories` as t1, `cat_name_translations` as t2 WHERE t1.category_id=t2.category_id AND `t2`.`category_name` = '".jb_escape_sql($name)."' AND form_id='".jb_escape_sql($form_id)."' AND lang='".jb_escape_sql($lang)."' ";
	
	$result = JB_mysql_query($sql) or die($sql.mysql_error());

	if ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		return $row['CID'];
	} else {
		return false;
	}


}

/////////////////////////////
/*

This function attempts to match the category with the words in the $text
It splits the text in to words, using only words longer than 3 letters
The word frequency is then counted, and ordered.
Then the most frequent words are searched in the category table to
try to get the category_id. Maximum of 5 searches with 5 most frequent
words in the text before giving up.

*/

function jb_match_text_to_category($text, $form_id, $lang='') {

	$MAX_SEARCH = 5;
	$i=0;

	if (!$lang) {
		$lang = JB_get_default_lang();
	}

	if (strlen($text)==0) return false;

	$words = preg_split ("/\s+/", $text);
	$result = array_count_values(array_map('strtolower', $words)); // Returns an associative array of values from input as keys and their count as value. Case insensitive
	arsort($result);
	while (($word=key($result)) && ($i <  $MAX_SEARCH)) {
		$i++;
		// match to category
		// return $category_id

		if (strlen($word)<3) { // ignore any words with 2 or less chars
			continue;
		}

		if ($cat_name = JB_match_category_id_from_name($word, $form_id, $lang='')) {
			return $cat_name;
		}

		next($result);

	}

	return false;


}

?>