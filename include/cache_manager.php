<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

if (!defined('JB_CACHE_DRIVER')) {
	define ('JB_CACHE_DRIVER', 'JBCacheFiles');
} 
	


function JB_is_cache_enabled() {

	if (defined('JB_CACHE_ENABLED') && (JB_CACHE_ENABLED != 'NO') && (JB_CACHE_ENABLED != '')) {
		return true;
	} else {
		return false;
	}

}


/*

Load all the cache drivers and return as an array of objects.

*/
function JB_get_cache_objects() {

	global $JB_CACHE_DRV;

	static $JB_CACHE_OBJECTS;
	if (is_array($JB_CACHE_OBJECTS)) return $JB_CACHE_OBJECTS;

	$cache_drv_dir = dirname(__FILE__).'/classes/cache_drivers';

	$CD = JBCacheDriver::get_driver(); // current driver
	$JB_CACHE_OBJECTS = array();
	if ($CD) {
		$JB_CACHE_OBJECTS[$CD->get_driver_name()] = $CD;
	}

	$dh = opendir ($cache_drv_dir);
	$file='';
	while (($file = readdir($dh)) !== false) {
		if (($file != '.') && ($file != '..') && (strpos($file, '.php')>0)){

            $JB_CACHE_DRV = JBCacheDriver::get_driver(basename($file, '.php'));
            if (!is_object($JB_CACHE_DRV)) continue;
            $key = $JB_CACHE_DRV->get_driver_name();
            if (!array_key_exists($key, $JB_CACHE_OBJECTS)) {
                $JB_CACHE_OBJECTS[$key] = $JB_CACHE_DRV;
            }
            $JB_CACHE_DRV=null;
		}
	 }
    closedir($dh);

	return $JB_CACHE_OBJECTS;


}

/*

Cache API:

*/

function JB_cache_get($key) {
	if (JB_CACHE_ENABLED=='NO') return;
	$Cache = JBCacheDriver::get_driver();
	$var = $Cache->get($key);
	return $var;
}

function JB_cache_flush() {
	if (JB_CACHE_ENABLED=='NO') return;
	$Cache = JBCacheDriver::get_driver();
	return $Cache->flush();
}

function JB_cache_delete($key) {
	if (JB_CACHE_ENABLED=='NO') return;
	$Cache = JBCacheDriver::get_driver();
	return $Cache->delete($key);

}

function JB_cache_set ($key, &$data, $expire=false) {
	if (JB_CACHE_ENABLED=='NO') return;
	$Cache = JBCacheDriver::get_driver();
	return $Cache->set($key, $data, $expire);
}

// stores variable var  with key  only if such key doesn't exist at the server yet.

function JB_cache_add ($key, &$data, $expire=false) {
	if (JB_CACHE_ENABLED=='NO') return;
	$Cache = JBCacheDriver::get_driver();
	return $Cache->add($key, $data, $expire);
}

/*

end of cache API

*/

#######################################################

class JBCacheDriver {

	var $driver_name;

	

	function &get_driver($class_name=null) { // static function

		static $Driver; // a cached instance of JB_CACHE_DRIVER

		if (($class_name!=null) ) {

			if ($class_name==JB_CACHE_DRIVER) {
				if (isset($Driver)) {
					return $Driver;
				}
			}
			$class_name = preg_replace('/[^A-Z^0-9]+i/', '', $class_name);
			
			require_once (dirname(__FILE__).'/classes/cache_drivers/'.$class_name.'.php');
			if ($class_name==JB_CACHE_DRIVER) {
				$Driver = $JB_CACHE_DRV;
			}
			return $JB_CACHE_DRV;

		}
		
		if (isset($Driver)) {
			return $Driver;
		}

		if (file_exists(dirname(__FILE__).'/classes/cache_drivers/'.JB_CACHE_DRIVER.'.php')) {
			require_once (dirname(__FILE__).'/classes/cache_drivers/'.JB_CACHE_DRIVER.'.php');
		} else {
			// default
			require_once (dirname(__FILE__).'/classes/cache_drivers/JBCacheFiles.php');
		}
		$Driver = $JB_CACHE_DRV; // cache it
		
		return $Driver;
	}

	function JBCacheDriver() {

		$this->driver_name = get_class($this);
	}

	// get an item from the cache
	function get($key) {
		return 'Please extend and implement me';
	}

	// delete the entire cache
	function flush() {
		echo 'This cache driver does not implement flush()';
	}

	// delete an item form the cache
	function delete($key) {
		echo 'This cache driver does not implement delete()';

	}

	// set an item
	function set($key, $data, $expire=false) {
		echo 'This cache driver does not implement set()';
	}

	function get_driver_name() {
		return $this->driver_name;
	}

	function get_driver_description() {
		return 'Please extend and implement me';
	}

	function config_radio() {
		?><input type="radio" name="jb_cache_driver" value="<?php echo $this->get_driver_name(); ?>" <?php if (JB_CACHE_DRIVER==$this->driver_name) { echo " checked "; } ?> ><?php echo $this->get_driver_description();?><br><?php
	}


}

#############################################

// replaces JB_generate_form_cache()

function JB_cache_del_keys_for_form($form_id) {

	if (JB_CACHE_ENABLED=='NO') return;
	$form_id = (int) $form_id;

	jb_cache_delete('column_info_'.$form_id);

	// get all the number of sections

	$sql = "SELECT field_id FROM `form_fields` WHERE form_id ='$form_id' group by section";
	$result = JB_mysql_query ($sql);
	$sections = mysql_num_rows($result);
	

	$sql = "SELECT * FROM lang WHERE is_active='Y' ";
	$result = JB_mysql_query ($sql);
	
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		jb_cache_delete('tag_to_field_id_'.$form_id.'_'.jb_escape_sql($row['lang_code'])); // since 3.6
		$NO_COLS = 5;
		for ($i=1; $i <= $NO_COLS; $i++) {
			jb_cache_delete('search_form_'.$form_id.'_cols_'.$i.'_'.jb_escape_sql($row['lang_code'])); // since 3.6
		}
						
		for ($i=0; $i < $sections; $i++) {
			
			jb_cache_delete('field_list_'.($i+1).'_'.$form_id.'_'.jb_escape_sql($row['lang_code'])); 
		}
	}

}

// replaces JB_generate_category_cache()
function JB_cache_del_keys_for_category($cat_id, $field_id) {
	if (JB_CACHE_ENABLED=='NO') return;
	$cat_id = (int) $cat_id;
	$field_id = (int) $field_id;
	$sql = "SELECT * FROM lang WHERE is_active='Y' ";
	$result = JB_mysql_query ($sql);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		jb_cache_delete("cat_f".$field_id."_c".$cat_id."_".$row['lang_code']);
		jb_cache_delete("cat_path_".$row['lang_code']);

	}

}

function JB_cache_del_keys_for_all_cats($form_id) {
	if (JB_CACHE_ENABLED=='NO') return;
	$form_id = (int) $form_id;

	// 1st level
	$sql = " SELECT * FROM form_fields WHERE field_type='CATEGORY' and form_id='".jb_escape_sql($form_id)."' ";
	$result = JB_mysql_query($sql) or die(mysql_error());
	//echo $sql;
	
	JB_cache_del_keys_for_category(0,  $form_id); // root category

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		JB_cache_del_keys_for_category($row['category_init_id'],  $form_id);

		// 2nd level
		$sql = "SELECT * from categories WHERE parent_category_id='".jb_escape_sql($row['category_init_id'])."' ";
		//echo $sql;
		$result2 = JB_mysql_query($sql) or die(mysql_error());
		while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
			
			JB_cache_del_keys_for_category($row2['category_id'],  $form_id);
		}

	}

}

// replaces JB_generate_category_option_cache
// Deletes the cache which stores the <option> list
// for the category <select> boxes
function JB_cache_del_keys_for_cat_options() {
	if (JB_CACHE_ENABLED=='NO') return;
	$sql = "SELECT form_id, category_init_id from form_fields WHERE field_type='CATEGORY' ";
	$result = JB_mysql_query($sql) or die(mysql_error());
	while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

		$sql = "SELECT * FROM lang WHERE is_active='Y' ";
		$result2 = JB_mysql_query ($sql);
		while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {

			$cache_key1 = 'cat_options_fid_'.$row['form_id'].'_cid_'.$row['category_init_id'].'_class_JBDynamicFormMarkup_lang_'.$row2['lang_code'];
			$cache_key2 = 'cat_options_fid_'.$row['form_id'].'_cid_'.$row['category_init_id'].'_class_JBSearchFormMarkup_lang_'.$row2['lang_code'];

			JB_cache_delete($cache_key1);
			JB_cache_delete($cache_key2);
		}
	}


}

// replaces JB_generate_code_cache()

function JB_cache_del_keys_for_codes($field_id)  {
	
	if (JB_CACHE_ENABLED=='NO') return;
	if (JB_CODE_ORDER_BY=='BY_NAME') {
		$order_by = 'description';
	} else {
		$order_by = 'code';
	}

	$sql = "SELECT * FROM lang WHERE is_active='Y' ";
	$result = JB_mysql_query ($sql);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		// for listing the codes
		jb_cache_delete('codes_list_fid_'.$field_id.'_ord_'.$order_by.'_lang_'.$row['lang_code']);
		// code name lookup table
		jb_cache_delete('jb_code_table_fid_'.$field_id.'_lang_'.$row['lang_code']);
	}

}



?>