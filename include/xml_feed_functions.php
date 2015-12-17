<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require_once (dirname(__FILE__)."/posts.inc.php");

function JBXML_generate_xml_feed($feed_id) {

	if (!is_numeric($feed_id)) return;

	$offset = 0;
	if (isset($_REQUEST['offset'])) {
		$offset = (int) $_REQUEST['offset'];
	}

	$sql = "SELECT * from xml_export_feeds WHERE feed_id='".jb_escape_sql($feed_id)."' ";
	$result = JB_mysql_query($sql);
	$feed_row = mysql_fetch_array($result, MYSQL_ASSOC);

	if ($feed_row['publish_mode']=='PRI') { // private mode

		if ($feed_row['feed_key'] != $_REQUEST['k']) {
			die ('Invalid Key. Please contact '.JB_SITE_CONTACT_EMAIL);
		}

	}
	
	$hosts = array();
	$hosts = explode(',', $feed_row['hosts_allow']);
	$allowed = false;
	if (sizeof($hosts)>0) {

		foreach ($hosts as $host) {
			
			if (strtoupper($host)=='ALL') { // all hosts
				$allowed = true;
			}
			if ((strtolower($host)=='localhost') && 
				($_SERVER['REMOTE_ADDR']=='127.0.0.1')) {
				$allowed = true;
			}
			if ($host==$_SERVER['REMOTE_ADDR']) {
				$allowed = true;
			}
		}

		if (!$allowed) {
			die ('Access is restricted form your IP. Please contact '.JB_SITE_CONTACT_EMAIL);
		}

	} 

	$feed_row['field_settings'] = unserialize($feed_row['field_settings']);

	$feed_row['search_settings'] = unserialize($feed_row['search_settings']);
	// build the search query up...

	if (is_array($feed_row['search_settings'])) {
		foreach ($feed_row['search_settings'] as $key => $val) {
			$_SEARCH_INPUT[$key]=$val;
		}
		$_SEARCH_INPUT['action']='search';
		global $post_tag_to_search;
		global $tag_to_search;

		$where_sql = JB_generate_search_sql($feed_row['form_id'], $_SEARCH_INPUT);
	}


	if ($feed_row['max_records'] > 0) {
		$limit = "LIMIT $offset, ".jb_escape_sql($feed_row['max_records']);
	}

	switch ($feed_row['form_id']) {

		case 1:
			if ($feed_row['include_imported']=='Y') {
				if ($where_sql) {
					$where_sql = ' AND '.$where_sql;
				}
				$sql = "SELECT * FROM posts_table WHERE `expired`='N' AND `approved`='Y' $where_sql ORDER BY `post_date` DESC $limit ";
			} else {
				$sql = "SELECT * FROM posts_table WHERE `expired`='N' AND `approved`='Y' AND `guid`='' $where_sql ORDER BY `post_date` DESC $limit ";
			}
			break;
		case 2:
			break;
		case 3:
			break;
		case 4:
			break;
		case 5:
			break;
	}
	

	$records = JB_mysql_query($sql);
	// Gzip compress the output, if supported by PHP & the browser
	//if (function_exists('ob_gzhandler') && !ini_get('zlib.output_compression')) {
		//ob_start("ob_gzhandler");
	//} else {
		//ob_start();
	//}
	header('Content-type: application/xml; charset=UTF-8');
	if ($_REQUEST['d']!='') { // download?
		header('Content-Disposition: attachment; filename="feed-'.htmlentities($feed_id).'.xml"'); 

	}

	// check to see if we have this feed in the cache

	if (function_exists('JB_get_cache_dir')) {
		$cache_dir = JB_get_cache_dir();
	} else {
		$cache_dir = JB_basedirpath().'cache/';
	}

	if (is_dir($cache_dir)) {
		if ($dh = opendir($cache_dir)) {
			while (($file = readdir($dh)) !== false) {
				if ((filetype($cache_dir.$file)=='file') && (strpos($file, '.xml')!==false)) {
					$stats = stat($cache_dir.$file);
					
					if (($stats['mtime']+(3600)) <  time()) { // has 1 hour elapsed? (3600 sec)
						unlink($cache_dir.$file);
					}
				}
			}
			closedir($dh);
		}
	}

	$do_update=false;
	$filename = $cache_dir.'feed_'.md5($feed_row['feed_id'].$feed_row['feed_key'].$offset).".xml";
	if (!file_exists($filename)) {
		$do_update=true;
	}
	$do_update = true;
	
	if ($do_update) {
		// Generate the XML feed & cache the result.
		echo '<?xml version="1.0" encoding="UTF-8" ?>';
		$fh = fopen ($filename, "wb");

		if (flock($fh, LOCK_EX)) { // do an exclusive lock
			ftruncate($fh, 0); // truncate file
			JBXM_xml_feed_gen_engine($feed_row, '', $records, $data, false, $fh);
			flock($fh, LOCK_UN); // release the lock
		} else {
			echo "<error>Couldn't get the lock!</error>";
		}
		
		fclose ($fh);
	} else {
		// Return the cached xml feed
		$fh = fopen($filename, 'rb');
		$contents = fread($fh, filesize($filename));
		fclose($fh);
		echo $contents;
	}

	ob_end_flush();

}



###############################################################
# Woot!... This is a nice & clean little function. 

// $fp = file handler for output to file.

function JBXM_xml_feed_gen_engine(&$feed_row, $element_id, &$records, &$data, $seek_pivot=false, $fh=null) {

	static $file_h;
	static $depth=0;
	static $no_more_records = false;
	static $data_stack = array();
	// if implode state is true, capture the data of the children to one single value
	static $implode_state = false;
	static $implode_element_id;

	if (!is_null($fh)) {
		$file_h = $fh;
	}
	if ((!is_null($file_h)) && (!ob_get_level())) {
		// we need to buffer because the feed will go to a file too
		ob_start();
	}

	if ($depth > 100) return; // just in case..

	$sql = "select * from xml_export_elements WHERE `parent_element_id`='".jb_escape_sql($element_id)."' AND `schema_id`='".jb_escape_sql($feed_row['schema_id'])."' ORDER BY is_pivot DESC "; // order by is_pivot to ensure that this row is last.

	$result = JB_mysql_query($sql) or die (mysql_error());
	if (mysql_num_rows($result)>0) {
		
		$the_end = true; 
	}

	$new_line_printed=false;
	
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		while (($seek_pivot==true) && ($row['is_pivot']!='Y')) { // seek to the pivot record
	
			$row = mysql_fetch_array($result, MYSQL_ASSOC);

		}
		// init static_data from the field_settings
		if ($feed_row['field_settings']['static_data_'.$row['element_id']]!='') {
			$row['static_data'] = $feed_row['field_settings']['static_data_'.$row['element_id']];
		}
		$element = $row['element_name'];
		
		// pivot record, export the data here
		if ($row['is_pivot']=='Y') {
			// start of the pivot block. Load our data here
			$pivot_parent_id = $row['parent_element_id'];
			// fetch our record
			if ($data = mysql_fetch_array($records, MYSQL_ASSOC)) {

				// also, load in the account settings if posting form
				// and account data if exporting jobs with account data

				$sql = "SELECT * FROM `employers` WHERE `ID`='".jb_escape_sql($data['user_id'])."' LIMIT 1";
				$ac_result = jb_mysql_query($sql);

				if (mysql_num_rows($ac_result) > 0) {
					$ac_row = mysql_fetch_array($ac_result, MYSQL_ASSOC);
					// move the the account row to the data
					foreach ($ac_row as $key => $val) {
						$data['Employer_'.$key] = $val; // prefix key with Employer_
						unset($ac_row[$key]);
					}
				}
				

			} else {
				$no_more_records=true; // no more data to export, return when the pivot is closed
				return;
			}
		}

		if ($row['attributes']!='') {
			// assign static values, eg %RECORD_ID% becomes 23, etc.
			$row['attributes'] = JBXM_get_static_data($row['form_id'], $data, $row['attributes'], $feed_row);
			// add a space
			$row['attributes'] = " ".$row['attributes'];
		}
		// start <element></element> section

		if (!JBXM_is_field_empty($feed_row, $row, $data)) {

			if (!$new_line_printed) {
				if (!$implode_state) {
					echo "\n"; 
				}
				$new_line_printed = true;
			}

			// print the spaces for indent
			if (!$implode_state) JBXM_echo_space_repeat($depth);

			//if (!JBXM_is_field_empty($feed_row, $row, $data)) {
			// opening tag
				if (!$implode_state) {
					echo "<$element".($row['attributes']).">";
				}
				// implode all elements inside this tree?$feed_row['field_settings']
				if ($feed_row['field_settings']['implode_'.$row['element_id']]=='Y') {
					$implode_state = true; // everything else below now will be imploded in to one value
					$implode_element_id=$row['element_id'];
					
				}

			//}
			

			$depth+=1;
			
			
			$ending = JBXM_xml_feed_gen_engine($feed_row, $row['element_id'], $records, $data);

			
			$val = '';
			
			if ($ending) {
		
				// there are no more elements in this branch
				//if (!$implode_state) JBXM_echo_space_repeat($depth);
				
			} else { //if (!JBXM_is_field_empty($feed_row, $row, $data)) {

				$val = '';
				# get the data from the record and filter it
				if (($row['static_data']=='') || ($row['static_mod']!='F')) {
					$val = JBXM_get_filtered_data($row, $feed_row, $data);
					if ($row['multi_fields']>1) { # extra
						$comma='';
						for ($i=1; $i < 5; $i++) {
							//$_REQUEST['mf_'.$i.'_extra_'.$row['element_id']]);
							$extra_field_id = ''; 
							$extra_field_id = $feed_row['field_settings']['mf_'.$i.'_extra_'.$row['element_id']];
							if ($extra_field_id!='') {
								$val = $val.', '.JBXM_get_filtered_data($row, $feed_row, $data, $extra_field_id);
							}
						}
					}
				}

				# Get the data from the element
				if ($row['static_data']!='') {
					$s_val = '';

					$s_val = JBXM_get_static_data($row['form_id'], $data, $row['static_data'], $feed_row);
		
					if ($row['static_mod']=='P') { # Prepend
						$val = $s_val.$val;
					} elseif ($row['static_mod']=='A') { # Append
						$val = $val.$s_val;
					} else {
						$val = $s_val; # Fill
					}
					
				}

				if ($row['is_cdata']=='Y') { // export as CDATA

					$val = str_replace(']]>', ']]]]><![CDATA[>', $val); // http://en.wikipedia.org/wiki/CDATA
					$val = '<![CDATA['.utf8_encode($val).']]>';
					
				} else {

					// Instead if not CDATA, XML encode
					$val =  JBXM_xmlentities($val);
				}

				if (($implode_state) && ($val)) {
					$data_stack[] = $val;

				} else {
					echo trim($val);			
				}

				

			}

			// closing tag
			

			if ($implode_state && ($implode_element_id==$row['element_id'])) {
				$implode_state=false;
				$str = implode (',',$data_stack);
				$data_stack = array();
				echo $str;
			}
			if (!$implode_state) {
				echo "</$element>";
				echo "\n";
			}
			

			
			
			// end element <element></element> printing section
		}
		if ($row['is_pivot']=='Y'){

			//echo "</b>";
			if ($no_more_records==false) {

				
				JBXM_xml_feed_gen_engine($feed_row, $pivot_parent_id, $records, $data, true);
			}

			if ($seek_pivot) { // we have found the pivot, we can ignore all the remaining records and break out of the while loop:
				break;
			}
			
		}

		if (!is_null($file_h)) {
			$output = ob_get_contents();
			fwrite($file_h, $output, strlen($output));
			ob_end_flush();		
		}

	}
	$depth-=1;
	return $the_end;


}

/////////////////////////////////

function JBXM_is_field_empty(&$feed_row, &$row, &$data) {
	//'F' Fill with a value from the record

	return false;
	
	if ($row['has_child']=='Y') {
		return false;
	}
	if (($row['static_data']!='') && ($row['static_mod']=='F')) {
		return false;
	}
	// check the contants if $data for that element
	$field_id = $feed_row['field_settings'][$row['element_id']];
	//echo "field_id [$field_id] data:[".$data[$field_id]."] <br>";
	if (($data[$field_id]=='') && ($field_id!='')) {
		return true;
	}
	return false;

}


/*

The following function loads the employers recored for the job posted
and merges it with the $post_data array. The keys are
prefixed with the $prefix.

*/
function JBXM_merge_employer_to_data_array(&$post_data, $prefix='Employer_') {

	static $employer_id;
	static $employer_row;// for caching the employer row

	if ($post_data[$prefix.'ID']!='') {
		return true; // already merged
	}

	if ($employer_id != $post_data['user_id']) {

		$employer_row = array(); 

		$employer_id = $post_data['user_id'];

		// load the employer row

		$sql = "SELECT * FROM `employers` WHERE `ID`='".jb_escape_sql($employer_id)."' ";
		$result = jb_mysql_query($sql);
		$employer_row = mysql_fetch_array($result, MYSQL_ASSOC);

		// prefix the keys
		$temp = array();
		foreach ($employer_row as $key=>$val) {
			$temp[$prefix.$key] = $val;
		}
		$employer_row = $temp;

	}

	
	// merge the array
	foreach ($employer_row as $key=>$val) {
		$post_data[$key] = $val;
	}




}

/////////////////////////////////

function JBXM_get_filtered_data(&$element_row, &$feed_row, &$data, $field_id='') {

	if ($field_id=='') {
		$field_id = $feed_row['field_settings'][$element_row['element_id']];
	}

	$val = trim($data[$field_id]);

	if ($field_id=='summary') { // generate a summary.
		// get the raw description
		global $post_tag_to_field_id;
		$description =  trim($data[$post_tag_to_field_id['DESCRIPTION']['field_id']]);
		// truncate it and strip any tags
		$val  = str_replace ('&nbsp;', ' ', JB_truncate_html_str (strip_tags($description),  JB_POSTS_DESCRIPTION_CHARS, $trunc_str_len));
		return $val;
	}

	$field_type = $feed_row['field_settings']['ft_'.$element_row['element_id']];

	if ($element_row['strip_tags']=='Y') {
		$val = (strip_tags($val));
		$from_html = array("&nbsp;", "  ", "\n\n", "\r\n\r\n");
		$to_text = array(" ", " ", "\n", "\n");
		$val = str_replace($from_html, $to_text, $val);
		
	} 

	if ($element_row['truncate']>0) {
		$val = JB_truncate_html_str ($val,  $element_row['truncate'], $trunc_str_len, false);
	}

	switch ($field_type) {

		case 'MSELECT':
		case 'CHECK':
			if ($element_row['qualify_codes']=='Y') {
				$vals = explode (",", $val);
				$comma = ''; $str;
				if (sizeof($vals)>0) {
					foreach ($vals as $v) {
						$str .= $comma.JB_getCodeDescription ($field_id, $v);
						$comma = ", ";
					}
				}
				$val = $str;
			}
			break;
		case 'SELECT':
		case 'RADIO':
			if ($element_row['qualify_codes']=='Y') {
				$val = JB_getCodeDescription ($field_id, $val);
			}
			break;
		case 'CATEGORY':
			if ($element_row['qualify_cats']=='Y') {
				$val = JB_getCatName($val);
			}
			break;
		case 'FILE':
			if ($feed_row['export_with_url']=='Y') {
				$val = JB_FILE_PATH.$val;
			}
			break;
		case 'IMAGE':
			if ($feed_row['export_with_url']=='Y') {
				$val = JB_IM_PATH.$val;
			}
			break;
		case 'PLUGIN':
			JBPLUG_do_callback('JBXM_get_filtered_data', $val, $feed_row);
			break;
	}

	if ($element_row['is_boolean']=='Y') {

		$match = $feed_row['field_settings']['boolean_p_'.$element_row['element_id']];

		if (($field_type=='MSELECT') || ($field_type=='RADIO')) {
			if (strpos(strtolower($val), strtolower($match))!==false) {
				$val = 'true';
			} else {
				$val = 'false';
			}
		} else {
			if (strtolower($val) == strtolower($match)) {
				$val = 'true';
			} else {
				$val = 'false';
			}
		}
	}

	return $val;



}

###############################################

function JBXM_get_static_data($form_id, &$data, $val, &$feed_row) {

	

	if (!preg_match('/%[a-z_]+%/i', $val)) {
		return $val; // is not a variable
	}

	$val = str_replace('%SITE_NAME%', JB_SITE_NAME, $val);
	$val = str_replace('%SITE_DESCRIPTION%', JB_SITE_DESCRIPTION, $val);
	$val = str_replace('%SITE_CONTACT_EMAIL%', JB_SITE_CONTACT_EMAIL, $val);
	$val = str_replace('%BASE_HTTP_PATH%', JB_BASE_HTTP_PATH, $val);
	$val = str_replace('%RSS_FEED_LOGO%', JB_RSS_FEED_LOGO, $val);

	$val = str_replace('%FEED_DATE%', date('r'), $val);
	
	if (file_exists(JB_RSS_FEED_LOGO)) {
		$img_size = getimagesize (JB_RSS_FEED_LOGO);
	}
	$val = str_replace('%RSS_FEED_LOGO%', JB_RSS_FEED_LOGO, $val);
	$val = str_replace('%RSS_LOGO_HEIGHT%', $img_size[1], $val);
	$val = str_replace('%RSS_LOGO_WIDTH%', $img_size[0], $val);

	$val = str_replace('%DEFAULT_LANG%', $_SESSION['LANG'], $val);



	// These depend on the different record types
	// %EXPIRE_DATE%
	// %DATE%
	// %LINK%

	
	switch ($form_id) {
		case '1': // job posts
			$post_time = strtotime($data['post_date']." GMT ");
			//$link = JB_BASE_HTTP_PATH.'index.php?post_id='.$data['post_id'];
			//$link = JB_job_post_url($data['post_id'], '', '', '', '', '', JB_BASE_HTTP_PATH);
			$PForm = JB_get_DynamicFormObject(1);
			$PForm->set_values($data);
			$link = JBXM_xmlentities(JB_job_post_url($data['post_id'], null, JB_BASE_HTTP_PATH.'index.php'));
			$val = str_replace('%LINK%', $link, $val);
			$val = str_replace('%DATE%', date('Y-m-d', $post_time), $val);
			$val = str_replace('%DATE_RFC%', date('r', $post_time), $val);
			$val = str_replace('%DATE_TROVIT%', date('d/m/Y', $post_time), $val);
			# elapsed = now - post_date
			# expires = post_date + DISPLAY DAYS
			if ($data['post_mode']=='premium') {
				$display_dur = JB_P_POSTS_DISPLAY_DAYS*24*60*60;
			} else {
				$display_dur = JB_POSTS_DISPLAY_DAYS*24*60*60;
			}
			$expire_time = $post_time + $display_dur;
			$val = str_replace('%EXPIRE_DATE%', date('Y-m-d', $expire_time), $val);

			$val = str_replace('%RECORD_ID%', $data['post_id'], $val);
			$val = str_replace('%USER_ID%', $data['user_id'], $val);

			
			break; 
		case '2': // resumes
			break;

	}

	return $val;


}

########################################################

# Naaaice!! 
# Special function to convert HTML string to UTF-8
# and then convert the special chars to entities!
# Includes a workaround for php 4

function JBXM_xmlentities($string, $quote_style=ENT_COMPAT)
{
	// convert all entities to UTF-8 encoded string
	// this is to preserve characters of other languages
	// encoded using htmlentities
	$string = JBXM_html_entity_decode($string);
	
	// xmlentities
	$trans = array(
		"<" => '&lt;', 
		"&" => '&amp;', 
		">" => '&gt;', 
		'"' => '&quot;',  
		'\'' => '&apos;');

	$string = strtr($string, $trans);
	
	return $string;

   
} 

############################################

function JBXM_code2utf($num)
{
	if ($num < 128) return chr($num);
	if ($num < 2048) return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
	if ($num < 65536) return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
	if ($num < 2097152) return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
	return '';
}
############################################
function JBXM_html_entity_decode($str) {

	return preg_replace('/&#(\\d+);/e', 'JBXM_code2utf($1)', utf8_encode($str));
	
	/////////////////////////////////////////////////////////////////////////////

	static $trans_tbl;
   
    // replace numeric entities
    $string = preg_replace('~&#x([0-9a-f]+);~ei', 'JBXM_code2utf(hexdec("\\1"))', $string);
    $string = preg_replace('~&#([0-9]+);~e', 'JBXM_code2utf(\\1)', $string);

    // replace literal entities
    if (!isset($trans_tbl))
    {
        $trans_tbl = array();
       
        foreach (get_html_translation_table(HTML_ENTITIES) as $val=>$key)
            $trans_tbl[$key] = utf8_encode($val);
    }
   
    return strtr($str, $trans_tbl);
	
}

############################################


function JBXM_display_xml_feed_form() {

	if ($_REQUEST['feed_id']!='') {

		//load from the database

		$sql = "select * from xml_export_feeds WHERE feed_id='".jb_escape_sql($_REQUEST['feed_id'])."' ";
		
		$result = JB_mysql_query($sql);
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		$_REQUEST['feed_name'] = $row['feed_name'];
		$_REQUEST['description'] = $row['description'];
		$field_settings = unserialize($row['field_settings']);
		
		foreach ($field_settings as $key=>$val) {

			if (is_numeric($key)) {
				$_REQUEST['field_id_'.$key] = $val;
			} else {
				$_REQUEST[$key] = $val;
			}

		}
		
		
		$search_settings = unserialize($row['search_settings']);
		
		// expand serach settings
		global $post_tag_to_search;
		$_Q_STRING = unserialize($row['search_settings']);
		foreach ($_Q_STRING as $key => $val) {
			$_REQUEST[$key]=$val;
		}
		$_REQUEST['max_records'] = $row['max_records'];
		$_REQUEST['publish_mode'] = $row['publish_mode'];
		$_REQUEST['include_emp_accounts'] = $row['include_emp_accounts'];
		$_REQUEST['schema_id'] = $row['schema_id'];
		$_REQUEST['feed_key'] = $row['feed_key'];
		$_REQUEST['hosts_allow'] = $row['hosts_allow'];
		$_REQUEST['is_locked'] = $row['is_locked'];
		$_REQUEST['form_id'] = $row['form_id'];
		$_REQUEST['export_with_url'] = $row['export_with_url'];

		$_REQUEST['include_imported'] = $row['include_imported'];
		
		

	} else {
		echo "<h4>Please enter the details for your XML feed:</h4>";
	}

	if ($_REQUEST['max_records']==false) {
		$_REQUEST['max_records'] = '100';
	}

	if (!$_REQUEST['form_id']) {

		$sql = "SELECT form_id FROM xml_export_schemas WHERE schema_id='".jb_escape_sql($_REQUEST['schema_id'])."' ";
		
		$result_f = JB_mysql_query($sql);
		$row_f = mysql_fetch_row($result_f);
		$_REQUEST['form_id']=$row_f[0];
		

	}

	?>
	<form method="POST" name="form1" action="<?php echo htmlentities($_SERVER['PHP_SELF'])?>">
	<input type='hidden' name="editfeed" value="<?php echo htmlentities($_REQUEST['editfeed']); ?>">
	<input type='hidden' name="form_id" value="<?php echo htmlentities($_REQUEST['form_id']); ?>">
	<input type='hidden' name="feed_id" value="<?php echo htmlentities($_REQUEST['feed_id']); ?>">
	<input type='hidden' name="schema_id" value="<?php echo htmlentities($_REQUEST['schema_id']); ?>">
	<h3>XML Feed Setup</h3>
	<table border='0' cellSpacing="1" cellPadding="3" bgColor="#d9d9d9">
		<tr>
			<td bgcolor='#eaeaea'><b>Feed Name</b></td>
			<td bgcolor='#ffffff'><input type="text" name="feed_name" size="40" value="<?php echo JB_escape_html($_REQUEST['feed_name']); ?>" ></td>
		</tr>
		<tr>
			<td bgcolor='#eaeaea'><b>XML Schema</b></td>
			<td bgcolor='#ffffff'>
			<?php

			if ($_REQUEST['schema_id']!='') {

				$sql = "select * from xml_export_schemas WHERE schema_id='".jb_escape_sql($_REQUEST['schema_id'])."' ";
				$result = JB_mysql_query($sql);
				if ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {
					echo "<b>".$row['schema_name']."</b> - This schema will be used to generate this feed. Please make sure to map your fields to this schema below.";
				}

			} 

			?>
			</td>
		</tr>
		<tr>
			<td bgcolor='#eaeaea'><b>Description</b></td>
			<td bgcolor='#ffffff'><textarea name='description' rows='3' cols='60' ><?php echo JB_escape_html($_REQUEST['description']); ?></textarea></td>
		</tr>
		<tr>
			<td bgcolor='#eaeaea'><b>Max Records</b></td>
			<td bgcolor='#ffffff'><input type="text" name="max_records" size="3" value="<?php echo jb_escape_html($_REQUEST['max_records']); ?>" > (How many records to put on the feed at one time, 0 = unlimited) <small>(Note: For best performance, it may be wise to keep the maximum value to 2000 or less. (Large feeds take a long time to generate). Tip: An offest parameter can be used to skip past records and fetch further records, eg http://example.com/jb-get-xml.php?feed_id=11&amp;offset=2000 will skip the first 2000 records)</small></td>
		</tr>
		<tr>
			<td bgcolor='#eaeaea'><b>Publish Mode</b></td>
			
			<?php if ($_REQUEST['publish_mode']==false) {  $_REQUEST['publish_mode']='PUB';}  ?>
		
			<td bgcolor='#ffffff'>Should this feed be public or private?<br>
			<input type="radio" value='PUB' <?php if ($_REQUEST['publish_mode']=='PUB') { echo ' checked '; }  ?> name='publish_mode' > Public (Will also be included in to the Jamit Feed Directory, so that others can subscribe to your feed)<br>
			<input type="radio" value='PRI' <?php if ($_REQUEST['publish_mode']=='PRI') { echo ' checked '; }  ?> name='publish_mode' > Private (with a secret key: <input type='text' name='feed_key' value='<?php echo JB_escape_html($_REQUEST['feed_key']);?>'> ) and, <input type="checkbox" name="include_emp_accounts" <?php if ($_REQUEST['include_emp_accounts']=='Y') echo ' checked ';?> value="Y" onchange="document.form1.save_feed.click()"> this feed includes employer's accounts
		    </td>
		</tr>
		
		
		<tr>
			<td bgcolor='#eaeaea'><b>IP Address Allow</b></td>
			
			<?php if ($_REQUEST['hosts_allow']==false) {  $_REQUEST['hosts_allow']='ALL,localhost';}  ?>
		
			<td bgcolor='#ffffff'><textarea name='hosts_allow' rows='1' cols='60'><?php echo htmlentities($_REQUEST['hosts_allow']); ?></textarea><br>List of addresses seperated by commas. Special values can be ALL and localhost.
			</td>
		</tr>

		<?php if ($_REQUEST['feed_id']!='') { ?>
		<tr>
			<td bgcolor='#eaeaea'><b>Locked</b></td>
			<td bgcolor='#ffffff'><input type="radio" name='is_locked' <?php if ($_REQUEST['is_locked']=='Y') { echo ' checked '; } ?> value='Y' > - Yes, lock from all user changes.<br>
			<input type="radio" name='is_locked' <?php if ($_REQUEST['is_locked']!='Y') { echo ' checked '; } ?> value='N' > - No, Allow changes.
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan="2" bgcolor='#eaeaea'><b>Optional: Specify a filter for this feed</b><br>
			<b>Imported posts:</b> 
			<?php
		
		global $search_form_mode;

		$search_form_mode='all';

		if ($_REQUEST['export_with_url']=='') {
			$_REQUEST['export_with_url'] = 'Y';
		}

		JB_display_dynamic_search_form (1); 
		
		?>
		</td>
		</tr>
		<tr>
			<td bgcolor="#eaeaea"><b>Imported Posts</b></td>
			<td bgcolor="white">
			<input type="checkbox" name="include_imported" value="Y" <?php if ($_REQUEST['include_imported']=='Y') { echo ' checked '; } ?> >Include posts that have been imported from other sources (via the XML Import tool).
			</td>
		</tr>
		<tr>
			<td bgcolor="#eaeaea"><b>Export Files & Images fiels</b></td>
			<td bgcolor="white">
			<input type="radio" name="export_with_url" value="Y" <?php if ($_REQUEST['export_with_url']=='Y') { echo ' checked '; } ?>>As a full URL to where they are on my job board (useful for  import tool to fetch the file)<br>
			<input type="radio" name="export_with_url" value="N" <?php if ($_REQUEST['export_with_url']=='N') { echo ' checked '; } ?>>As is (the raw data)</td>
		</tr>
		
	
	<tr>
			<td colspan="2" bgcolor='#F2F2F2'><b>Please associate the fields from the Job Posting form to the XML feed.</b>Try to map as many fields as possible, but not all have to be mapped. Any field that is left as '[None]' will not be included in the exported RSS feed.<br><?php

		//JBXM_show_fields_not_mapped($feed_id);

		global $element_input_options;
		$element_input_options = "FIELDS";
		JBXM_display_xml_doc_tree($_REQUEST['schema_id']);

		?>
	</td></tr>
	</table>
	<p><input type="submit" value="Save" name="save_feed" style="font-size: 24px" ></p>
</form>

<?php


}


/////////////////////

function JBXM_get_fields_not_mapped($feed_id) {

	// grab required field

	$required = array();

	$sql = "SELECT t1.field_id as FID, t1.field_label, field_type as NAME FROM form_fields as t1, form_field_translations as t2 WHERE t1.field_id=t2.field_id AND form_id=1 AND is_required='Y' AND lang='".$_SESSION['LANG']."' ";
	$result = jb_mysql_query($sql);
	$i=0;
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$required[$i]['field_id'] = $row['FID'];
		$required[$i]['field_name'] = $row['NAME'];
		$i++;
	}

	// now take off the required fields which are already mapped

}


/////////////////

function JBXM_display_xml_schema_form() {

	if ($_REQUEST['schema_id']!='') {

		//load from the database

		$sql = "select * from xml_export_schemas WHERE schema_id='".jb_escape_sql($_REQUEST['schema_id'])."' ";
		//echo $sql;
		$result = JB_mysql_query($sql);
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		$_REQUEST['schema_name'] = $row['schema_name'];
		$_REQUEST['description'] = $row['description'];
		$_REQUEST['is_locked'] = $row['is_locked'];
		

	} else {

		echo "<h4>Please enter the details for your XML schema:</h4>";

	}


	?>

	<form method="POST" name="form1" action="<?php echo htmlentities($_SERVER['PHP_SELF'])?>">
	
	<input type='hidden' name="schema_id" value="<?php echo htmlentities($_REQUEST['schema_id']); ?>">
	<table border='0' cellSpacing="1" cellPadding="3" bgColor="#d9d9d9">
		<tr>
			<td bgcolor='#eaeaea'><b>XML schema Name</b></td>
			<td bgcolor='#ffffff'><input type="text" name="schema_name" size="40" value="<?php echo JB_escape_html($_REQUEST['schema_name']); ?>" ></td>
		</tr>
		<tr>
			<td bgcolor='#eaeaea'><b>XML schema Type</b></td>
			<td bgcolor='#ffffff'>
			<select name='form_id'>
				<option value='1'>Jobs</option>
			</select>
			</td>
		</tr>
		<tr>
			<td bgcolor='#eaeaea'><b>Description</b></td>
			<td bgcolor='#ffffff'><textarea name='description' rows='10' cols='60' ><?php echo JB_escape_html($_REQUEST['description']); ?></textarea></td>
		</tr>
		<?php if ($_REQUEST['schema_id']!='') { ?>
		<tr>
			<td bgcolor='#eaeaea'><b>Locked</b></td>
			<td bgcolor='#ffffff'><input type="radio" name='is_locked' <?php if ($_REQUEST['is_locked']=='Y') { echo ' checked '; } ?> value='Y' > - Yes, lock from all user changes.<br>
			<input type="radio" name='is_locked' <?php if ($_REQUEST['is_locked']!='Y') { echo ' checked '; } ?> value='N' > - No, Allow changes.
			</td>
		</tr>
		<?php } ?>
		
	</table>
	<p><input type="submit" value="Save" name="save_schema"></p>
</form>

<?php


}


#####################################################################
function JBXM_list_xml_feeds() {

	$sql = "SELECT *, t1.is_locked as LOCKED From xml_export_feeds as t1, xml_export_schemas as t2 where t1.schema_id=t2.schema_id  ";
	$result = JB_mysql_query($sql) or die(MYSQL_ERROR());

	if (mysql_num_rows($result)>0) {

		?>
		<table border=0 cellSpacing="1" cellPadding="3" bgColor="#d9d9d9"  >
		<tr bgColor="#eaeaea">
			<td><b>Feed Id</b></td>
			<td><b>Feed Name</b></td>
			<td><b>Schema</b></td>
			<td><b>Publish</b></td>
			<td><b>IP Allow</b></td>
			<td><b>URL</b></td>
			<td><b>Action</b></td>
		</tr>

		<?php
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

			?>
			<tr bgcolor="<?php echo (($row['feed_id']==$_REQUEST['feed_id'])&&($_REQUEST['feed_id'])) ? '#FFFFCC' : '#ffffff'; ?>">
				<td><?php echo $row['feed_id'];?></td>
				<td><a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?editfeed=yes&feed_id=<?php echo $row['feed_id'];?>"><?php echo JB_escape_html($row['feed_name']);?></a></td>
				<td><a href='xmlschema.php?config=yes&form_id=1&schema_id=<?php echo $row['schema_id']?>'><?php echo $row['schema_name'];?></td>
				<td><?php if ( $row['publish_mode']=='PUB') { echo 'Public'; } else { echo 'Private';} ;?></td>
				<td><?php echo $row['hosts_allow']; ?></td>
				<td><?php 

					$url = JB_BASE_HTTP_PATH.'jb-get-xml.php?feed_id='.$row['feed_id'];
					if ($row['feed_key']!='') {
						$url .= '&k='.urlencode($row['feed_key']);
					}
					?>
					<input style='font-size:11px' onfocus="this.select()" type="text" size='50' value="<?php echo JB_escape_html($url); ?>">
					<?php
					echo "<br>[<a href=\"$url\" target=\"_blank\">Preview</a> | ";
					echo "<a href=\"$url&d=1\" >Download <b>feed-".$row['feed_id'].".xml</b></a>";
					echo " | <a href=\"".$_SERVER['PHP_SELF']."?clear=".$row['feed_id']."&feed_key=".$row['feed_key']."\">Clear Cache</a>]";
			
				?></td>
				<td><?php

					if ($row['LOCKED']!='Y') {
						echo "<a href='".$_SERVER['PHP_SELF']."?editfeed=yes&feed_id=".$row['feed_id']."'><img border=0 src='edit.gif'></a> &nbsp;<a  href='".$_SERVER['PHP_SELF']."?delfeed=yes&feed_id=".$row['feed_id']."' onclick=\"if (!confirmLink(this, 'Delete, are you sure?')) return false;\" ><img border=0 src='delete.gif' ></a>";
					}
					
				?></td>
			</tr>

		<?php
		}

	}
	?>
	</table>

	<?php


}


#####################################################################

function JBXM_list_xml_schemas() {

	$sql = "SELECT * From xml_export_schemas  ";
	$result = JB_mysql_query($sql) or die(MYSQL_ERROR());

	?>

	<table border=0 cellSpacing="1" cellPadding="3" bgColor="#d9d9d9"  >
	<tr bgColor="#eaeaea">
		<td><b>Schema Id</b></td>
		<td><b>Schema Name</b></td>
		<td><b>Type</b></td>
		<td><b>Action</b></td>
		<td><b>Description</b></td>
	
	</tr>
	<?php
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		?>
		<tr bgcolor="<?php echo (($row['schema_id']==$_REQUEST['schema_id'])&&($_REQUEST['schema_id'])) ? '#FFFFCC' : '#ffffff'; ?>">
			<td><?php echo $row['schema_id'];?></td>
			<td><?php echo $row['schema_name'];?></td>
			<td>Jobs<?php //echo $row['form_id'];?></td>
			<td nowrap><a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?&schema_id=<?php echo $row['schema_id']; ?>">Edit</a> | <a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?config=yes&form_id=<?php echo $row['form_id'];?>&schema_id=<?php echo $row['schema_id']; ?>">Configure XML Structure</a> </td>
			<td><?php echo jb_escape_html($row['description']);?></td>
		</tr>

		<?php

	}

	?>

	</table>

	<?php
}

function JBXM_is_schema_locked($schema_id) {
	$sql = "SELECT is_locked FROM xml_export_schemas WHERE schema_id='".jb_escape_sql($schema_id)."' ";
	$result = JB_mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	if ($row['is_locked']=='Y') {
		return true;
	} else {
		return false;
	}

}

function JBXM_echo_static_tag_list() {

?>
	<font color='maroon'><b>%SITE_NAME%</b></font> - Name of your site<br>
	<font color='maroon'><b>%SITE_DESCRIPTION%</b></font> - Site's description<br>
	<font color='maroon'><b>%SITE_CONTACT_EMAIL%</b></font> - Contact Email<br>
	<font color='maroon'><b>%BASE_HTTP_PATH%</b></font> - Direct link to your site<br>
	<font color='maroon'><b>%EXPIRE_DATE%</b></font> - Expiration date of the record<br>
	<font color='maroon'><b>%LINK%</b></font> - Direct link to the record<br>
	<font color='maroon'><b>%DATE%</b></font> - Date the record was created / updated (Year-month-date format)<br>
	<font color='maroon'><b>%DATE_RFC%</b></font> - Date the record was created / updated (RFC 2822 formatted)<br>
	<font color='maroon'><b>%DEFAULT_LANG%</b></font> - Default language of your site<br>
	<font color='maroon'><b>%RSS_FEED_LOGO%</b></font> - Link to the RSS logo image<br>
	<font color='maroon'><b>%RSS_LOGO_HEIGHT%</b></font> - Image Height<br>
	<font color='maroon'><b>%RSS_LOGO_WIDTH%</b></font> - Image Width<br>
	<font color='maroon'><b>%FEED_DATE%</b></font> - Date of feed's generation<br>
	<font color='maroon'><b>%RECORD_ID%</b></font> - ID of the record being exported<br>
	<font color='maroon'><b>%USER_ID%</b></font> - ID of the user who posted the record<br>

<?php

}

###################################################################



function JBXM_display_xml_schema_config_form() {

	
	if ($_REQUEST['element_id']!='') {

		$sql = "SELECT * from xml_export_schemas as t1, xml_export_elements as t2 WHERE t1.schema_id=t2.schema_id and t2.schema_id='".jb_escape_sql($_REQUEST['schema_id'])."' and t2.element_id='".jb_escape_sql($_REQUEST['element_id'])."'  ";

		$result = JB_mysql_query($sql) or die (mysql_error().$sql);
		$row = mysql_fetch_array($result, MYSQL_ASSOC);

		$_REQUEST['element_name'] = $row['element_name'];
		$_REQUEST['is_cdata'] = $row['is_cdata'];
		$_REQUEST['parent_element_id'] = $row['parent_element_id'];
		$_REQUEST['attributes'] = $row['attributes'];
		$_REQUEST['static_data'] = $row['static_data'];
		$_REQUEST['is_pivot'] = $row['is_pivot'];
		$_REQUEST['field_id'] = $row['field_id'];
		$_REQUEST['form_id'] = $row['form_id'];
		$_REQUEST['description'] = $row['description'];
		$_REQUEST['fieldcondition'] = $row['fieldcondition'];
		$_REQUEST['is_boolean'] = $row['is_boolean'];

		$_REQUEST['qualify_codes'] = $row['qualify_codes'];
		$_REQUEST['qualify_cats'] = $row['qualify_cats'];
		$_REQUEST['strip_tags'] = $row['strip_tags'];
		$_REQUEST['truncate'] = $row['truncate'];
		$_REQUEST['comment'] = $row['comment'];

		$_REQUEST['is_mandatory'] = $row['is_mandatory'];

		$_REQUEST['static_mod'] = $row['static_mod'];
		$_REQUEST['multi_fields'] = $row['multi_fields'];

		
		

		
		
	} else {
		echo '<h4>Insert a new Element: </h4>';
	}
	if (JBXM_is_schema_locked($_REQUEST['schema_id'])) {
		echo "<b><font color='red'>Note: This schema is locked from making changes. You will need to unlock this schema before making changes. Please note that changing the schema will affect all existing feeds which use this schema. </font> Click 'Edit' above, there you can unlock.</b>";
		$disabled = " disabled ";
	}
	
	?>
	<form method="POST" action="<?php echo $_SERVER['PHP_SELF']?>">

	<input type="hidden" name="config" value="<?php echo jb_escape_html($_REQUEST['config']);?>">
	<input type="hidden" name="form_id" value="<?php echo jb_escape_html($_REQUEST['form_id']);?>">
	<input type="hidden" name="schema_id" value="<?php echo jb_escape_html($_REQUEST['schema_id']);?>">
	<input type="hidden" name="element_id" value="<?php echo jb_escape_html($_REQUEST['element_id']);?>">
	<input type="hidden" name="fieldcondition" value="<?php echo jb_escape_html($_REQUEST['fieldcondition']);?>">

	<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" id="table1">
		
		<tr>
			<td bgColor="#eaeaea"><b>Element Name</b></td>
			<td bgcolor="#ffffff"><input <?php echo $disabled; ?> type="text" name="element_name" size="40" value="<?php echo JB_escape_html($_REQUEST['element_name']); ?>"><br>
			<font size="1">eg, if you want to export as &lt;salary&gt;$2000&lt;/salary&gt; then the element name 
			is: salary</font></td>
		</tr>
		<tr>
			<td bgColor="#eaeaea"><b>Parent Element</b></td>
			<td bgcolor="#ffffff"><select <?php echo $disabled; ?> <?php echo $disabled; ?> size="1" name="parent_element_id">
			<?php

			if ($_REQUEST['parent_element_id'] === '0') {
				$checked = ' selected ';
			}
			
			?>
			<option <?php echo $checked; ?>value='0'>Document Root</option>
			<?php

			$sql = "SELECT * from xml_export_elements where schema_id='".jb_escape_sql($_REQUEST['schema_id'])."' ";
			$result = JB_mysql_query($sql) or die(mysql_error());

			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

				if ($_REQUEST['parent_element_id']==$row['element_id']) {

					$checked = ' selected ';

				} else {
					$checked = '';
				}
				
				echo '<option '.$checked.' value="'.$row['element_id'].'">'.JB_escape_html($row['element_name']).'</option>';		

			}

			?>
			</select><br>
			<font size="1">(Use this to build the xml file document tree displayed on the right.)</font></td>
		</tr>
		<tr>
			<td bgColor="#eaeaea"><b>Attributes</b></td>
			<td bgcolor="#ffffff"><input <?php echo $disabled; ?> type="text" name="attributes" size="40" value="<?php echo JB_escape_html($_REQUEST['attributes']); ?>"><br>
			<font size="1">Optional. eg, if you want to export as &lt;<b>rss version="2.0"</b> &gt;&lt;/rss&gt; then the attribute is: rss version="2.0"<br>
			Tip: You can use any of the variables that are listed under the 'Static Data' section of this form. eg. %POST_ID%</font></td>
		</tr>
		<tr>
			<td bgColor="#eaeaea"><b>Is pivot?</b></td>
			<td bgcolor="#ffffff"><input <?php echo $disabled; ?> type="radio" value="Y" <?php if ($_REQUEST['is_pivot']=='Y') { echo ' checked ';} ?> name="is_pivot">Yes, make this entity the pivot entity which will be iterated during the xml feed generation. Each feed can only contain 1 pivot entity. <br>
			<input type="radio" <?php echo $disabled; ?> value="N" <?php if ($_REQUEST['is_pivot']!='Y') { echo ' checked ';} ?>  name="is_pivot">No, not pivot (default)</td>
		</tr>
		
		<tr>
			<td bgColor="#eaeaea"><b>Is CDATA?</b></td>
			<td bgcolor="#ffffff"><input <?php echo $disabled; ?> type="radio" value="Y" <?php if ($_REQUEST['is_cdata']=='Y') { echo ' checked ';} ?> name="is_cdata">Yes, export the data using 
			CDATA notation, <font size="1">eg. &lt;salary&gt;&lt;![CDATA[$2000]]&gt;&lt;/salary&gt;</font><br>
			<input <?php echo $disabled; ?> type="radio" value="N" <?php if ($_REQUEST['is_cdata']!='Y') { echo ' checked ';} ?>  name="is_cdata">No, export as XML 
			entities</td>
		</tr>
		<tr>
			<td bgColor="#eaeaea"><b>Is Mandatory?</b></td>
			<td bgcolor="#ffffff"><input <?php echo $disabled; ?> type="radio" value="Y" <?php if ($_REQUEST['is_mandatory']=='Y') { echo ' checked ';} ?> name="is_mandatory">Yes, this element must be always present in the feed. Indicated by a <font color="red" size="4">*</font><br>
			<input <?php echo $disabled; ?> type="radio" value="N" <?php if ($_REQUEST['is_mandatory']!='Y') { echo ' checked ';} ?>  name="is_mandatory">No</td>
		</tr>
		<tr>
			<td bgColor="#eaeaea"><b>Multi-fields</b></td>
			<td bgcolor="#ffffff">
			<select name='multi_fields'>
				<option <?php if ($_REQUEST['multi_fields']==1) echo ' selected '; ?> value='1'>1 field (default)</option>
				<option <?php if ($_REQUEST['multi_fields']==2) echo ' selected '; ?> value='2'>2 fields</option>
				<option <?php if ($_REQUEST['multi_fields']==3) echo ' selected '; ?> value='3'>3 fields</option>
				<option <?php if ($_REQUEST['multi_fields']==4) echo ' selected '; ?> value='4'>4 fields</option>
			</select>
			<font size="1">Selecting multi-fields will allow you to merge several fields in to 1 exported attribute. Eg. If you have the location details stored in 3 different fields, you can select '3 fields' to have it exported in one 'location' attribute. (This is the case for Google Base)</font>
			</td>
		</tr>
		<tr>
			<td bgColor="#eaeaea"><b>Static Data</b></td>
			<td bgcolor="#ffffff"><input <?php echo $disabled; ?> type="text" name="static_data" size="40" value="<?php echo JB_escape_html($_REQUEST['static_data']); ?>"><br>
			Optional. Instead of specifying a data field to bind, you may have this element contain static hard-coded data, or special variables. Here are the variables for use:<br>
			<?php  JBXM_echo_static_tag_list(); ?>
			
			</td>
		</tr>
		<tr>
			<?php
			
		if ($_REQUEST['static_mod']==false) { $_REQUEST['static_mod']='F'; } ?>
			<td bgColor="#eaeaea"><b>Static Data Options</b></td>
			<td bgcolor="#ffffff"><input <?php echo $disabled; ?> type="radio" value="A" <?php if ($_REQUEST['static_mod']=='A') { echo ' checked ';} ?> name="static_mod">Append to the end<br>
			<input <?php echo $disabled; ?> type="radio" value="P" <?php if ($_REQUEST['static_mod']=='P') { echo ' checked ';} ?> name="static_mod">Prepend to the start<br>
			<input type="radio" <?php echo $disabled; ?> value="F" <?php if ($_REQUEST['static_mod']=='F') { echo ' checked ';} ?>  name="static_mod">Fill (default)<br>
			<font size="1">These options are useful if you want to have the Static Data  appended/prepended to the exported attributes.</font></td>
		</tr>
		<tr>
			<td bgColor="#eaeaea"><b>Comment</b></td>
			<td bgcolor="#ffffff"><input type="text" size="40" name="comment" value="<?php echo jb_escape_html($_REQUEST['comment']);?>"></td>

		</tr>
		<tr>
			<td bgColor="#eaeaea" colspan="2"><b>Data Filtering Options</b></td>
		</tr>
		<tr>
			<?php if ($_REQUEST['is_boolean']!='Y') { $_REQUEST['is_boolean']='N'; } ?>
			<td bgColor="#eaeaea"><b>Is Boolean?</b></td>
			<td bgcolor="#ffffff"><input <?php echo $disabled; ?> type="radio" value="Y" <?php if ($_REQUEST['is_boolean']=='Y') { echo ' checked ';} ?> name="is_boolean">Yes. Will be exported as 'true' or 'false' depending on if a value was matched<br>
			<input type="radio" <?php echo $disabled; ?> value="N" <?php if ($_REQUEST['is_boolean']!='Y') { echo ' checked ';} ?>  name="is_boolean">No (default)</td>
		</tr>
		<tr><?php if ($_REQUEST['qualify_codes']=='') $_REQUEST['qualify_codes']='Y'; ?>
			<td bgColor="#eaeaea"><b>Radio-buttons, Checkboxes, Selects</b></td>
			<td bgcolor="#ffffff">
			<input <?php echo $disabled; ?> type="radio" value="Y" <?php if ($_REQUEST['qualify_codes']=='Y') { echo ' checked ';} ?> name="qualify_codes">Convert codes to their full name, eg US becomes U.S.A.<br>
			<input <?php echo $disabled; ?> type="radio" value="N" <?php if ($_REQUEST['qualify_codes']!='Y') { echo ' checked ';} ?> name="qualify_codes">Export coded fields as their code. (default)<br>
			</td>
		</tr>
		<tr>
		<?php
			if ($_REQUEST['qualify_cats']=='') $_REQUEST['qualify_cats']='Y'; ?>

			<td bgColor="#eaeaea"><b>Category Fields</b></td>
			<td bgcolor="#ffffff">
			<input <?php echo $disabled; ?> type="radio" value="Y" <?php if ($_REQUEST['qualify_cats']=='Y') { echo ' checked ';} ?> name="qualify_cats">Convert category numbers to their full name.<br>
			<input <?php echo $disabled; ?> type="radio" value="N" <?php if ($_REQUEST['qualify_cats']!='Y') { echo ' checked ';} ?> name="qualify_cats">Export as category IDs. (default)</td>
		</tr>
		<tr>
			<td bgColor="#eaeaea"><b>Truncate</b></td>
			<td bgcolor="#ffffff"> Truncate to <input <?php echo $disabled; ?> type="text" value="<?php echo jb_escape_html($_REQUEST['truncate']); ?>" size="3" name="truncate"> characters. (Enter a number. 0 = do not truncate)</td>
		</tr>
		<tr>
			<td bgColor="#eaeaea"><b>Strip HTML Tags</b></td>
			<td bgcolor="#ffffff"><input <?php echo $disabled; ?> type="radio" value="Y" <?php if ($_REQUEST['strip_tags']=='Y') { echo ' checked ';} ?> name="strip_tags">Yes<br>
			<input <?php echo $disabled; ?> type="radio" value="N" <?php if ($_REQUEST['strip_tags']!='Y') { echo ' checked ';} ?>  name="strip_tags">No (default)</td>
		</tr>
		

		
	</table>
	<p><input <?php echo $disabled; ?> type="submit" value="Save" name="save_element"></p>
	</form>



	<?php


}

################################################################

function JBXM_echo_space_repeat ($i) {

	for ($x=0; $x < $i; $x++) {
		echo "  ";

	}

}

function JBXM_echo_nbsp_repeat ($i) {

	for ($x=0; $x < $i; $x++) {
		echo "&nbsp;&nbsp;";

	}

}

#################################################################


function JBXM_display_xml_doc_tree($schema_id, $element_id=0) {
	static $depth=0;
	global $element_input_options;
	static $pivot_open;
	static $feed_row;

	if ($element_input_options=='') {
		$element_input_options = 'BUTTONS';
	}

	$feed_id = (int) $_REQUEST['feed_id'];

	if ($feed_row==null) { 
		$sql = "SELECT * from xml_export_feeds WHERE feed_id='".jb_escape_sql($feed_id)."' ";
		$result = JB_mysql_query($sql);
		$feed_row = mysql_fetch_array($result, MYSQL_ASSOC);
		$feed_row['field_settings'] = unserialize($feed_row['field_settings']);
		
	}
	
	if ($depth > 100) return;
	
	$sql = "select * from xml_export_elements WHERE `parent_element_id`='".jb_escape_sql($element_id)."' AND `schema_id`='".jb_escape_sql($schema_id)."' order by has_child desc, is_pivot desc ";

	//echo $sql;

	$result = JB_mysql_query($sql) or die (mysql_error());
	if (mysql_num_rows($result)>0) {
		echo "<br>";
		$the_end = true; 
	}
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$element = $row['element_name'];

		
		if ($feed_row['field_settings']['static_data_'.$row['element_id']]!='') {
			// over-write the default export mode
			$row['static_data'] = $feed_row['field_settings']['static_data_'.$row['element_id']];

			// hidden field to ensure that the custom static data setting is stored in field_settings
			// by the JBXM_save_xml_feed_input() function
			?>
			 <input type="hidden" name="static_data_<?php echo $row['element_id']; ?>" value="<?php echo jb_escape_html($row['static_data']); ?>"> 
			<?php

		}

		if ($row['attributes']!='') {
			$row['attributes'] = " ".$row['attributes'];
		}
		//echo "<br>";
		JBXM_echo_nbsp_repeat($depth);
		if ($row['is_pivot']=='Y'){
			echo "<b>";
			$pivot_open = true;
		}
		if ($row['is_mandatory']=='Y') { echo '<font color="red" size="4">*</font>'; }
		echo "<font color='purple'>&lt;$element</font><font color='blue'>".JB_escape_html($row['attributes'])."</font><font color='purple'>&gt;</font>";
		if ($row['is_pivot']=='Y'){
			echo "</b>";
			 echo ' <font color="green">&lt;!-- This item will be iterated for each record (Pivot) --&gt; </font>';
		}
		
		$depth+=2;
		$ending = JBXM_display_xml_doc_tree($schema_id, $row['element_id']);
		
		if (($row['static_data']=='') && ($row['field_id']==false)) {
			//echo "<br>";
			//echo_nbsp_repeat($depth);

			
		} 

		if ($row['is_pivot']=='Y'){
			echo "<b>";
			$pivot_open = true;
		}
		if ($ending) {
			
			if ((($row['static_data']=='') ||  ($row['static_mod']!='F')) &&  ($element_input_options=='FIELDS') && ($pivot_open==true) && ($depth>=6)) {
				JBXM_echo_nbsp_repeat($depth);
				 ?><small><input type="checkbox" name="implode_<?php echo $row['element_id'];?>" value='Y' <?php if ($_REQUEST['implode_'.$row['element_id']]=='Y') echo ' checked '; ?> ></small> <b>Implode</b> <small>the fields between &lt;<?php echo $element;?>&gt; and &lt;/<?php echo $element;?>&gt; in to one single value</small><?php
				//JBXM_echo_field_select_field($row, $feed_row);
				echo '<br>';
			}
			
			JBXM_echo_nbsp_repeat($depth);
			
		} else {

			if ($element_input_options=='FIELDS') { // display the input fields
			//for XML Feed configuration

				if (($row['static_data']=='') ||  ($row['static_mod']!='F')) {
					
					JBXM_echo_field_select_field($row, $feed_row);
					
					if ($row['static_mod']=='A') { // append
						echo "<font color='maroon'><b>".$row['static_data']."</b></font>";
					}
					
					if ($row['is_boolean']=='Y') {
						?>
						Export as <font color="blue"><b>true</b></font> if data = <input type='text' size='10' name="<?php echo 'boolean_p_'.$row['element_id']; ?>" value="<?php echo jb_escape_html($_REQUEST['boolean_p_'.$row['element_id']]); ?>" >
						<?php

					}
					if ($row['static_mod']!='A') {
						?>
						<small>[<a style="color:black" href="#" onclick="window.open('xml_change_window.php?form_id=<?php echo $row['form_id'];?>&schema_id=<?php echo $row['schema_id'];?>&element_id=<?php echo $row['element_id'];?>&feed_id=<?php echo $feed_id;?>&to_static=1', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=550,height=500,left = 50,top = 50');return false;">Change to static...</a>]</small>
						<?php
					} 
				} 
				else {
					// display the tree without any input fields
					echo "<font color='maroon'><b>".$row['static_data']."</b></font>";
					?>
					<small>[<a style="color:black" href="#" onclick="window.open('xml_change_window.php?form_id=<?php echo $row['form_id'];?>&schema_id=<?php echo $row['schema_id'];?>&element_id=<?php echo $row['element_id'];?>&feed_id=<?php echo $feed_id;?>&to_static=1', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=550,height=500,left = 50,top = 50');return false;">Edit</a>][<a style="color:black" href="#" onclick="window.open('xml_change_window.php?form_id=<?php echo $row['form_id'];?>&schema_id=<?php echo $row['schema_id'];?>&element_id=<?php echo $row['element_id'];?>&feed_id=<?php echo $feed_id;?>&to_db=1', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=550,height=500,left = 50,top = 50');return false;">Change to DB value...</a>]</small>
					<?php


				}

			} else {
				// $element_input_options is 'BUTTONS'
				// This means that the XML document tree is displayed on the 
				// Admin->XML Exports, 'Configure XML Structure' screen
				echo "<font color='maroon'><b>".$row['static_data']."</b></font>";

			}
			
		}
		
		echo "<font color='purple'>&lt;/$element&gt;</font>";


		// Show comments
		$comment = ''; $comma='';
		if ($row['is_boolean']=='Y') { $comment .= '  Boolean'; $comma=',';}
		if ($row['is_cdata']=='Y') { $comment .= $comma.' CDATA'; $comma=',';}
		if ($row['strip_tags']=='Y') { $comment .= $comma.' Strip Tags'; $comma=',';}
		if ($row['truncate']>0) { $comment .= $comma.' Truncate to:'.$row['truncate'].' chars'; $comma=',';}
		if ($row['qualify_codes']=='Y') { $comment .= $comma.' Export codes as names'; $comma=',';}
		if ($row['qualify_cats']=='Y') { $comment .= $comma.' Export categories as names'; $comma=',';}
		if ($row['comment']!='') {
			if ($comment) {
				$comment = $comment.",";
			}
			$comment .= " ".$row['comment'];

		}
		if ($comment!='') {
			echo ' <font color="green">&lt;!-- '.$comment.' --&gt;</font> ';
		}

		if ($row['element_name']=='jamitKey') {
			echo ' <font color="green">&lt;!-- If this feed requires a key, this field should be set to static  --&gt;</font> ';
		}

		if (($element_input_options=='BUTTONS') && (!JBXM_is_schema_locked($_REQUEST['schema_id']))) {
			echo "&nbsp;<a  href='".$_SERVER['PHP_SELF']."?delelement=yes&form_id=".$_REQUEST['form_id']."&schema_id=".$_REQUEST['schema_id']."&element_id=".$row['element_id']."' onclick=\"if (!confirmLink(this, 'Delete, are you sure?')) return false;\" ><img border=0 src='delete.gif' ></a> <a href='".$_SERVER['PHP_SELF']."?config=yes&form_id=".$_REQUEST['form_id']."&schema_id=".$_REQUEST['schema_id']."&element_id=".$row['element_id']."'><img border=0 src='edit.gif'></a> <a href='".$_SERVER['PHP_SELF']."?config=yes&form_id=".$_REQUEST['form_id']."&schema_id=".$_REQUEST['schema_id']."&form_id=".$_REQUEST['form_id']."&parent_element_id=".$row['element_id']."'><img border=0 src='add.gif'></a>";
		} 
		
		echo "<br>";
		if ($row['is_pivot']=='Y'){
			
			echo "</b>";
			$pivot_open = false;
			
		}

	}
	$depth-=2;
	return $the_end;


}

//////////////////////////////////////////

function JBXM_echo_field_select_field(&$row, &$feed_row) {

	if ($row['static_mod']=='P') { // prepend
		echo "<font color='maroon'><b>".$row['static_data']."</b></font>";
	}
	?>
	<select name="field_id_<?php echo $row['element_id'];?>" >
		<option value='0'>[None]</option>
		<?php
		//require_once ('../include/lists.inc.php');
		JB_field_select_option_list ($row['form_id'], $_REQUEST['field_id_'.$row['element_id']]);


		//require_once ('../include/lists.inc.php');
		if (($feed_row['include_emp_accounts'] == 'Y') && ($feed_row['publish_mode'] != 'PUB')) {
			?>
			<option value='0' style="color: gray;">Employer's Accounts:</option>
			<?php
			// show employer account data
			// prefix account vars with emp_
			JB_field_select_option_list (4, $_REQUEST['field_id_'.$row['element_id']], $prefix='Employer_');
		}

	?>
	</select>

	<?php

	if ($row['multi_fields']>1) {

		for ($i=1; $i < $row['multi_fields']; $i++) {
			?>
			<select name="mf_<?php echo $i;?>_extra_<?php echo $row['element_id'];?>" >
				<option value=''>[None]</option>
				<?php
				//require_once ('../include/lists.inc.php');
				JB_field_select_option_list ($row['form_id'], $_REQUEST['mf_'.$i.'_extra_'.$row['element_id']]);
			?>
			</select>
		<?php
		}


	}



}

######################################

function JBXM_validate_xml_feed_input() {

	

	if ($_REQUEST['feed_name']==false) {
		$error = "Feed name is blank<br>";
	}

	if ($_REQUEST['description']==false) {
		$error = "Feed description is blank<br>";
	}

	return $error;

}

######################################

function JBXM_validate_xml_element_input() {

	if ($_REQUEST['element_name']==false) {

		$error = "Element name is blank<br>";

	}
	return $error;

}

######################################

function JBXM_validate_xml_schema_input() {

	if ($_REQUEST['schema_name']==false) {

		$error = "Schema name is blank<br>";

	}
	return $error;

}

#############################################
function JBXM_delete_xml_element($element_id) {

	$sql = "DELETE from xml_export_elements WHERE element_id='".jb_escape_sql($element_id)."' ";
	JB_mysql_query($sql) or die(mysql_error());


}

###############################################

function JBXM_save_xml_element_input() {

	$_REQUEST['truncate'] = (int) $_REQUEST['truncate'];

	if ($_REQUEST['element_id'] ==false) {
		$sql = "INSERT INTO xml_export_elements (element_name, is_cdata, parent_element_id, form_id, field_id, schema_id, attributes, static_data, is_pivot, fieldcondition, is_boolean, qualify_codes, qualify_cats, `truncate`, `strip_tags`, is_mandatory, static_mod, multi_fields, comment) VALUES ('".jb_escape_sql($_REQUEST['element_name'])."', '".jb_escape_sql($_REQUEST['is_cdata'])."', '".jb_escape_sql($_REQUEST['parent_element_id'])."', '".jb_escape_sql($_REQUEST['form_id'])."', '".jb_escape_sql($_REQUEST['field_id'])."', '".jb_escape_sql($_REQUEST['schema_id'])."', '".jb_escape_sql($_REQUEST['attributes'])."', '".jb_escape_sql($_REQUEST['static_data'])."', '".jb_escape_sql($_REQUEST['is_pivot'])."', '".jb_escape_sql($_REQUEST['fieldcondition'])."', '".jb_escape_sql($_REQUEST['is_boolean'])."', '".jb_escape_sql($_REQUEST['qualify_codes'])."', '".jb_escape_sql($_REQUEST['qualify_cats'])."', '".jb_escape_sql($_REQUEST['truncate'])."', '".jb_escape_sql($_REQUEST['strip_tags'])."', '".jb_escape_sql($_REQUEST['is_mandatory'])."', '".jb_escape_sql($_REQUEST['static_mod'])."', '".jb_escape_sql($_REQUEST['multi_fields'])."', '".jb_escape_sql($_REQUEST['comment'])."') ";
	} else {
		$sql = "UPDATE xml_export_elements SET element_name='".jb_escape_sql($_REQUEST['element_name'])."', is_cdata='".jb_escape_sql($_REQUEST['is_cdata'])."', parent_element_id='".jb_escape_sql($_REQUEST['parent_element_id'])."', form_id='".jb_escape_sql($_REQUEST['form_id'])."', field_id='".jb_escape_sql($_REQUEST['field_id'])."',  schema_id='".jb_escape_sql($_REQUEST['schema_id'])."', attributes='".jb_escape_sql($_REQUEST['attributes'])."', static_data='".jb_escape_sql($_REQUEST['static_data'])."', is_pivot='".jb_escape_sql($_REQUEST['is_pivot'])."', fieldcondition='".jb_escape_sql($_REQUEST['fieldcondition'])."', is_boolean='".jb_escape_sql($_REQUEST['is_boolean'])."', qualify_codes='".jb_escape_sql($_REQUEST['qualify_codes'])."', `qualify_cats`='".jb_escape_sql($_REQUEST['qualify_cats'])."', `truncate`='".jb_escape_sql($_REQUEST['truncate'])."', `strip_tags`='".jb_escape_sql($_REQUEST['strip_tags'])."', `is_mandatory`='".jb_escape_sql($_REQUEST['is_mandatory'])."', `static_mod`='".jb_escape_sql($_REQUEST['static_mod'])."', `multi_fields`='".jb_escape_sql($_REQUEST['multi_fields'])."', `comment`='".jb_escape_sql($_REQUEST['comment'])."' WHERE element_id='".jb_escape_sql($_REQUEST['element_id'])."' ";
	}

	JB_mysql_query($sql) or die (mysql_error());
	
}

########################################

function JBXM_save_xml_schema_input() {

	if ($_REQUEST['schema_id'] ==false) {
		$sql = "INSERT INTO xml_export_schemas (schema_name, description, form_id, is_locked) VALUES ('".jb_escape_sql($_REQUEST['schema_name'])."', '".jb_escape_sql($_REQUEST['description'])."', '".jb_escape_sql($_REQUEST['form_id'])."', '".jb_escape_sql($_REQUEST['is_locked'])."') ";
	} else {
		$sql = "UPDATE xml_export_schemas SET schema_name='".jb_escape_sql($_REQUEST['schema_name'])."', description='".jb_escape_sql($_REQUEST['description'])."', form_id='".jb_escape_sql($_REQUEST['form_id'])."', is_locked='".jb_escape_sql($_REQUEST['is_locked'])."'  WHERE schema_id='".jb_escape_sql($_REQUEST['schema_id'])."' ";

	}

	JB_mysql_query($sql) or die (mysql_error());
	

}


###############################################################


function JBXM_save_xml_feed_input() {

	

	// save the search form
	// The $post_tag_to_search contains all the names and 
	// field_id's on the search form
	// We put this on to the $_Q_STRING array and serialize it
	// to $_REQUEST['search_settings'] ready to be saved in the databse
	global $post_tag_to_search;

	
	foreach ($post_tag_to_search as $key => $val) {
		$name = $post_tag_to_search[$key]['field_id'];
		$_Q_STRING[$name] = $_REQUEST[$name];
	}
	$_REQUEST['search_settings'] = addslashes(serialize($_Q_STRING));

	// save the field_ids to field_settings.
	// Cycle through the $_REQUEST array
	$field_settings = array();
	foreach ($_REQUEST as $key=>$val) {
		// If the field is prefixed with field_id then this would be
		// an element to field_id association which is to be saved 
		// in the $field_settings array structure
		if (strpos($key, 'field_id_')!==false) {
			// split the parts of the composite key to get the
			// IDs for elemnt, field, $val contains the field_id
			// of the form_fields table
			$p = explode('_', $key);
			$element_id = array_pop($p);
			
			$field_settings[$element_id] = $val;
			
			// save any extra fields....mf_ = multi-field
			for ($i=1; $i < 4; $i++) {
				if ($_REQUEST['mf_'.$i.'_extra_'.$element_id]!='') {
					$field_settings['mf_'.$i.'_extra_'.$element_id] = $_REQUEST['mf_'.$i.'_extra_'.$element_id];
				}
			}

			// save the field type (eg. TEXT, CATEGORY, SELECT etc)
			// the key is for field type is prefixed with ft_
			$sql = "SELECT field_type FROM form_fields WHERE field_id='".jb_escape_sql($val)."' ";
			$result_ff = JB_mysql_query($sql);
			if ($row_ff = @mysql_fetch_array($result_ff, MYSQL_ASSOC)) {
				$field_settings['ft_'.$element_id] = $row_ff['field_type'];
			}
			

		}
		$p = explode('_', $key);
		$element_id = array_pop($p);
		// save the boolean value too, if exists as input from the form
		if ($_REQUEST['boolean_p_'.$element_id]!='') {
			$field_settings['boolean_p_'.$element_id] = $_REQUEST['boolean_p_'.$element_id];
		}


		// save the 'implode' option, if exists input from the form
		if ($_REQUEST['implode_'.$element_id]!='') {
	
			$field_settings['implode_'.$element_id] = $_REQUEST['implode_'.$element_id];
		}

		// static_data_: how to export the value
		// overwrites the static_data setting set in the XML Schema

		if ($_REQUEST['static_data_'.$element_id]!='') {
			$field_settings['static_data_'.$element_id] = $_REQUEST['static_data_'.$element_id];
		}
	}
	//serialize for the database
	$_REQUEST['field_settings'] = addslashes(serialize($field_settings));

	// strip any spaces
	$_REQUEST['hosts_allow'] = preg_replace('/\s/', '', $_REQUEST['hosts_allow']);

	$_REQUEST['max_records'] = (int) $_REQUEST['max_records'];

	// build the query
	if ($_REQUEST['feed_id'] ==false) {
		$sql = "INSERT INTO `xml_export_feeds` ( `feed_name` , `description` , `field_settings` , `search_settings` , `max_records` , `publish_mode` , `schema_id` , `feed_key`, `hosts_allow`, `form_id`, `include_emp_accounts`, `export_with_url`, `include_imported` ) VALUES ('".jb_escape_sql($_REQUEST['feed_name'])."', '".jb_escape_sql($_REQUEST['description'])."', '".jb_escape_sql($_REQUEST['field_settings'])."', '".jb_escape_sql($_REQUEST['search_settings'])."', '".jb_escape_sql($_REQUEST['max_records'])."', '".jb_escape_sql($_REQUEST['publish_mode'])."', '".jb_escape_sql($_REQUEST['schema_id'])."', '".jb_escape_sql($_REQUEST['feed_key'])."', '".jb_escape_sql($_REQUEST['hosts_allow'])."', '".jb_escape_sql($_REQUEST['form_id'])."', '".jb_escape_sql($_REQUEST['include_emp_accounts'])."', '".jb_escape_sql($_REQUEST['export_with_url'])."', '".jb_escape_sql($_REQUEST['include_imported'])."') ";
		
	} else {
		$sql = "UPDATE xml_export_feeds SET `feed_name`='".jb_escape_sql($_REQUEST['feed_name'])."' , `description`='".jb_escape_sql($_REQUEST['description'])."' , `field_settings`='".jb_escape_sql($_REQUEST['field_settings'])."' , `search_settings`='".jb_escape_sql($_REQUEST['search_settings'])."' , `max_records`='".jb_escape_sql($_REQUEST['max_records'])."' , `publish_mode`='".jb_escape_sql($_REQUEST['publish_mode'])."', `feed_key`='".jb_escape_sql($_REQUEST['feed_key'])."', `hosts_allow`='".jb_escape_sql($_REQUEST['hosts_allow'])."', `is_locked`='".jb_escape_sql($_REQUEST['is_locked'])."', `form_id`='".jb_escape_sql($_REQUEST['form_id'])."', `include_emp_accounts`='".jb_escape_sql($_REQUEST['include_emp_accounts'])."', `export_with_url`='".jb_escape_sql($_REQUEST['export_with_url'])."', `include_imported`='".jb_escape_sql($_REQUEST['include_imported'])."'  WHERE feed_id='".jb_escape_sql($_REQUEST['feed_id'])."' ";
	}


	JB_mysql_query($sql) or die (mysql_error());

	JB_compute_export_elements_has_child($_REQUEST['schema_id']);

	

}

##############################################



function JB_compute_export_elements_has_child($schema_id=false) {

	// get all the export elements that have a child
	// by joining the table with itself

	if ($schema_id) {
		$schema_id_sql = " AND t1.schema_id='".jb_escape_sql($schema_id)."' ";
	}

	$sql = " SELECT t1.element_id AS EL_ID
FROM xml_export_elements AS t1, xml_export_elements AS t2
WHERE t1.element_id = t2.parent_element_id $schema_id_sql
GROUP BY EL_ID ";

	$result = jb_mysql_query($sql);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$sql = "UPDATE xml_export_elements SET has_child='Y' WHERE element_id='".jb_escape_sql($row['EL_ID'])."' ";
		
		jb_mysql_query($sql);
	}
	// now set the remaining NULL to 'N'
	$sql = "UPDATE xml_export_elements SET has_child='N' WHERE has_child IS NULL ";
	jb_mysql_query($sql);


}

?>