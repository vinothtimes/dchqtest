<?php
# Copyright Jamit Software, 2009

# SimplyHiredXML.php - for importing Indeed's XML Job Search Feed

# Important:
# At the bottom if the file, this statement should exist.
# $_JB_PLUGINS['SimplyHiredXML'] = new SimplyHiredXML; // add a new instance of the class to the global plugins array

/*
Conventions: 

- Always name your class starting with capital letters.
- The file name of the class should be the same as the directory name and file name
- Always register your callbacks in the constructor!
- Use this plugin as a starting point for your own plugin
#############################################################################
# SECURITY NOTICE                                                           #
#############################################################################
# - IMPORTANT: Always escape SQL values before putting them in to a query   #
# use the jb_escape_sql() function on ALL values                            #
#                                                                           #
# eg.                                                                       #
#                                                                           #
# $sql = "SELECT * FROM test where id='".jb_escape_sql($some_id)."' ";      #
#                                                                           #
# - IMPORTANT: Be sure to escape data before outputting it to the page      #
# use the jb_escape_html() function, and be sure to escape                  #
# $_SERVER['PHP_SELF'] with htmlentities()                                  #
#                                                                           #
# eg.                                                                       #
#                                                                           #
# echo jb_escape_html($_REQUEST['some_value']);                             #
#                                                                           #
# echo htmlentities($_SERVER['PHP_SELF']);                                  #
#                                                                           #
# - Always sanitize input to be used in functions such as                   #
# fopen(), eval(), system()                                                 #
# eg.                                                                       #
# $file_name = preg_replace ('/[^a-z^0-9]+/i', "", $_REQUEST['file_name']); #
# $fh = fopen($file_name, r);                                               #
#                                                                           #
#############################################################################
*/
if (!function_exists('JB_mysql_fetch_row')) {

	function JB_mysql_fetch_row($result, $result_type) {
		return mysql_fetch_row($result, $result_type);
	}

	function JB_mysql_fetch_array($result, $result_type) {
		return mysql_fetch_array($result, $result_type);
	}

	function JB_mysql_num_rows($result) {
		return mysql_num_rows($result);
	}
}
class SimplyHiredXML extends JB_Plugins {

	var $config;
	var $plugin_name;

	var $result;

	var $posts = array();

	var $back_fill = array();
	var $pre_fill = array();

	var $current_post = array();

	var $fill_in_progress;

	var $curl_filename;

	var $total_results;

	function SimplyHiredXML() {

		require (dirname(__FILE__).'/SimplyHiredXMLParser.php'); 

		$this->plugin_name = "SimplyHiredXML"; // set this to the name of the plugin. Case sensitive. Must be exactly the same as the directory name and class name!

		parent::JB_Plugins(); // initalize JB_Plugins

		// Prepare the config variables
		// we simply extract them from the serialized variable like this:

		if ($this->config==null) { // older versions of jamit did not init config
			$config = unserialize(JB_PLUGIN_CONFIG);
			$this->config = $config[$this->plugin_name];
		}

		# initialize the priority
		if ($this->config['priority'] == '') {
			$this->config['priority' ]= 5;
		}

		if ($this->config['k'] == '') {
			$this->config['k'] = 'manager'; // default keyword
		}

		if (($this->config['age']<1) || ($this->config['age']>30)) {
			$this->config['age'] = '30'; // default age
		}
		if ((!$this->config['r'])) {
			$this->config['r'] = '25'; // distance (radius) in miles
		}

		if ((!$this->config['so'])) {
			$this->config['so' ]= 'rd'; // sort order (default: relevance descending)
		}

		if ($this->config['f'] == '') {
			$this->config['f'] = '1'; // filter results?
		}
		if ($this->config['h'] == '') {
			$this->config['h'] = '0'; // highlight keywrods?
		}

		if ($this->config['l'] == '') {
			$this->config['l'] = '';
		}

		if ($this->config['k_tag'] == '') {
			$this->config['k_tag'][] = 'TITLE';
		} else {
			// convert to array
			$this->config['k_tag'] = explode (',', $this->config['k_tag']);
		}

		if ($this->config['l_tag'] == '') {
			$this->config['l_tag'][] = 'LOCATION';
			}
		else {
			// convert to array
			$this->config['l_tag'] = explode (',', $this->config['l_tag']);
			}


		// SimplyHired publisher ID (version 2 only)
		if ($this->config['id'] == '') {
			$this->config['id'] = '12281';
			}


		$this->config['lim'] = JB_POSTS_PER_PAGE;

		
		$this->config['s'] = 'api.simplyhired.com'; // for version 2
		//$this->config['s'] = 'jamit.simplyhired.com'; // for version 1


		if ($this->config['curl'] == '') { // cURL
			$this->config['curl'] = 'N';
		}

		if ($this->config['fill' ] == '') { // results fill mode
			$this->config['fill'] = 'S';
		}

		if ($this->config['c' ] == '') { // country
			$this->config['c'] = 'c';
		}

		if ($this->config['ssty' ] == '') { // search style
			$this->config['ssty'] = '2';
		}

		if ($this->config['day' ] == '') { // show days
			$this->config['day'] = 'Y';
		}

		
		if ($this->is_enabled()) {
			
			// register all the callbacks
		
			if ($this->config['fill'] == 'S') {
				JBPLUG_register_callback('job_list_set_count', array($this->plugin_name, 'set_count'), $this->config['priority']);
			}
			else {
				JBPLUG_register_callback('job_list_set_count', array($this->plugin_name, 'set_count2'), $this->config['priority']);
			}

			JBPLUG_register_callback('index_extra_meta_tags', array($this->plugin_name, 'meta_tags'), $this->config['priority']);

		
			JBPLUG_register_callback('job_list_data_val', array($this->plugin_name, 'job_list_data_val'), $this->config['priority']);

			JBPLUG_register_callback('job_list_back_fill', array($this->plugin_name, 'list_back_fill'), $this->config['priority']);

			JBPLUG_register_callback('admin_plugin_main', array($this->plugin_name, 'keyword_page'), $this->config['priority']);
			
		}

	}

	////////////////////////////////////////////////////////////////
	// returns a pointer to an open temp file
	function curl_request($host, $get) {

		$URL = "http://".$host.$get;

		$ch = curl_init();

		if ($this->config['proxy']!='') { // use proxy?
			curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
			curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			curl_setopt ($ch, CURLOPT_PROXY, $this->config['proxy']);
		}


		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ($ch, CURLOPT_URL, $URL);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt ($ch, CURLOPT_POST, false);
		//curl_setopt ($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


		$result = curl_exec ($ch);
		
		curl_close ($ch);

		

		// save the result in to a temp file, utf-8 encoded
		$r = rand (1,1000000); // random number for the file-name
		$filename = $this->get_cache_dir().md5(time().$this->config['id'].$r).'_simplyhired.xml';
	
		$fp = fopen($filename, 'w');
		fwrite($fp, utf8_encode($result));
		$fp = fclose($fp);

		$this->curl_filename=$filename;

		// open for reading
		
		return fopen($filename, 'r');
	}


	function curl_cleanup($fp) {
		// delete the temp file
		unlink ($this->curl_filename);
	}


	function set_category_kw($cat_id, &$keyword_q, &$location_q) {

		$keyword_q = '';
		$location_q = '';

		$cat_id = (int) $cat_id;

		$sql = "SELECT * FROM SimplyHiredXML_keywords WHERE category_id='".jb_escape_sql($cat_id)."' ";
		$result = jb_mysql_query($sql);
		$row = JB_mysql_fetch_array($result);

		if (strlen($row['kw'])>0) { // keywords
			$keyword_q = trim($row['kw']);
		} else {
			$keyword_q = JB_getCatName($cat_id); // use the category name itself as the keyword
		}

		if (strlen($row['loc'])>0) { // location
			$location_q = trim($row['loc']);
		} else {
			$location_q = $this->config['l']; // use the default location from the config
		}
	}


	function is_kw_OK($keyword) { // make sure the keyword not repeated
		static $arr = array();
		$keyword = trim($keyword);
		if (!in_array($keyword, $arr)) {
			$arr[] = $keyword;
			return true;
		} else {
			return false;
		}
	}


	function is_loc_OK($keyword) { // make sure location not repeated
		static $arr = array();
		$keyword = trim($keyword);
		if (!in_array($keyword, $arr)) {
			$arr[] = $keyword;
			return true;
		} else {
			return false;
		}
	}

	

	///////////////////////////////////////////
	// Customize your data processing routine below
	//

	function do_request($pageNum, $wSize, $ignoreCnt = 0) {

		if ($pageNum < 1 || $wSize <= 0) { // invalid values
			return false;
		}

		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$ip_addr = $_SERVER['REMOTE_ADDR'];


		############################################################################
		# Process the keywords

		// Set the default keywords.
		// These will be overwritten if user inputted keywords are available
		$keyword_q = $this->config['k']; // default keywords
		$location_q = $this->config['l']; // default location


		if (is_numeric($_REQUEST['cat'])) { // fetch the category keywords
			$this->set_category_kw($_REQUEST['cat'], $keyword_q, $location_q);
		}

		if ($_REQUEST['action']=='search') { // search results, use one field for the where, and other fileld for location
			$PForm = JB_get_DynamicFormObject(1);
			$post_tag_to_search = $PForm->get_tag_to_search();

			// iterate through each search parameter
			foreach ($post_tag_to_search as $key=>$tag) {

				// is the search parameter attached to the keyword or location?
				if (in_array($key, $this->config['k_tag']) || in_array($key, $this->config['l_tag'])) {
		
					$val = $_REQUEST[$tag['field_id']]; // get what was searched for

					if (strlen($temp_keys) > 0) {
						$temp_keys_space = ' ';
					}
					if (strlen($temp_loc )> 0) {
						$temp_loc_space = ' ';
					}

					// convert the code or category id in to a keyword

					switch ($tag['field_type']) {
						// multiple select fields and checkboxes
						// if passed as an array, these keywords are combined with an OR
						case 'MSELECT': 
						case 'CHECK':	
							if (is_array($val)) {
								$str = ''; $or = '';
								foreach ($val as $code) {
									$str .= $or.JB_getCodeDescription ($tag['field_id'], $code);
									$or = ' OR ';
								}
								$val = '('.$str.')';
							} else {
								$val = JB_getCodeDescription ($tag['field_id'], $val);
							}	
							break;
						case 'SELECT':
						case 'RADIO':
							// Single select and radio buttons.
							$val = JB_getCodeDescription ($tag['field_id'], $val);
							break;
						case 'CATEGORY':
							// grab the category config
							// If multiple categories are selected then they
							// are combined with an OR

							$cat_keywords_temp=''; $cat_location_temp=''; $or='';$i_temp=0;
							
							if (is_array($val)) { // multiple categories were searched
								$or='';
								foreach ($val as $cat_id) {
									$i_temp++;
									$this->set_category_kw($cat_id, $kw_val, $loc_val);
									if ($this->is_kw_OK($kw_val)) {
										$cat_keywords_temp .= $or.$kw_val; // append using OR
										$or = ' OR ';
									}
									if ($this->is_loc_OK($loc_val)) {
										$cat_location_temp = $loc_val;
									}									
								}
								if ($i_temp>1) {
									$cat_keywords_temp = '('.$cat_keywords_temp.')';
								} else {
									$cat_keywords_temp = $cat_keywords_temp;
								}
								
								//echo "keywords_temp: [$cat_keywords_temp] * [$cat_id]<br>got this: $kw_val $loc_val<br>";

							} else {
								
								$this->set_category_kw($val, $kw_val, $loc_val);
							
								if ($this->is_kw_OK($kw_val)) {
									$cat_keywords_temp = $kw_val;
								}
								if ($this->is_loc_OK($loc_val)) {
									$cat_location_temp = $loc_val;
								}
							}

							// add them to the keys that we are bulding
							$temp_keys .= $temp_keys_space.$cat_keywords_temp;
							// the location keys are placed in to a separate string
							$temp_cat_loc .= $temp_loc_space.$cat_location_temp;
							$temp_key_space = ' ';
							$temp_loc_space = ' ';

							$val = '';

							break;
					}

					// add the $val to the temp keywords
					if (in_array($key, $this->config['k_tag'])) { // keyword?

						$val = trim($val);
						if ($val!='') {
							// concationate the 'what' keywords
							$temp_keys .= $temp_keys_space.$val;
						}
					}

					if (in_array($key, $this->config['l_tag'])) { // location?

						$val = trim($val);
						if (($val!='')) { 
							// concatinate the 'where' keywords
							$temp_loc .= $temp_loc_space.$val;
						}

					}

					

					

				}

			} // end iterating through each parameter

			$temp_keys = trim($temp_keys);
			$temp_loc = trim($temp_loc);

			// overwrite the default value $keyword_q with the kewords that were searched
			if ($temp_keys!='') {
				$keyword_q = $temp_keys;
			}

			// Overwrite the default value $location_q with the location that was searched
			// The 'were' kywords get priority
			// If they are bank, then use the location keywords from the category if
			// available.
			if ($temp_loc!='') {
				$location_q = $temp_loc;
			} elseif ($temp_cat_loc != '') { // the 'where' keywords were empty, so perhaps they were set by a category?
			
				$location_q = $temp_cat_loc;
			}

			//echo "temp keys: $temp_keys<br>";
			//echo "temp loc: $temp_loc<br>";

		}
		
		############################################################################

		$sort = $this->config['so'];
		if ($sort == 'custom') {
			$sort = 'rd';
		}
		
		
		$host = $this->config['s'];

		switch ($this->config['c']) {
			case 'kr':
				$host = 'api.simplyhired.kr';
				break;
			case 'jp':
				$host = 'api.simplyhired.jp';
				break;
			case 'in':
				$host = 'api.simplyhired.co.in';
				break;
			case 'cn':
				$host = 'api.simplyhired.cn';
				break;
			case 'uk':
				$host = 'api.simplyhired.co.uk';
				break;
			case 'ch':
				$host = 'api.simplyhired.ch';
				break;
			case 'es':
				$host = 'api.simplyhired.es';
				break;
			case 'nl':
				$host = 'api.simplyhired.nl';
				break;
			case 'it':
				$host = 'api.simplyhired.it';
				break;
			case 'ie':
				$host = 'api.simplyhired.ie';
				break;
			case 'de':
				$host = 'api.simplyhired.de';
				break;
			case 'fr':
				$host = 'api.simplyhired.fr';
				break;
			case 'be':
				$host = 'api.simplyhired.be';
				break;
			case 'at':
				$host = 'api.simplyhired.at';
				break;
			case 'mx':
				$host = 'api.simplyhired.mx';
				break;
			case 'ca':
				$host = 'api.simplyhired.ca';
				break;
			case 'br':
				$host = 'api.simplyhired.com.br';
				break;
			case 'au':
				$host = 'api.simplyhired.com.au';
				break;

			case 'us':
				$host = 'api.simplyhired.com';
				$ssty = 1;
			default:
				// not supported...
				$host = 'api.simplyhired.com';
				
		}
		
		
		// use this string for SimplyHired version v1
		/*
		$req = '/q-'.urlencode($keyword_q).'/l-'.urlencode($location_q).'/mi-'.$this->config['r'].'/sb-'.$sort.'/ws-'.$wSize.'/pn-'.$pageNum;
		$get = '/a/jobs/xml-v1'.$req;
		*/

		
		// use this string for SimplyHired version v2
		
		$req = '/q-'.urlencode($keyword_q).'/l-'.urlencode($location_q).'/mi-'.$this->config['r'].'/sb-'.$sort.'/ws-'.$wSize.'/pn-'.$pageNum.'/?pshid='.$this->config['id'].'&ssty='.$this->config['ssty'].'&cflg=r&purl='. urlencode($this->selfURL());
		$get = '/a/jobs-api/xml-v2'.$req;
		
//echo $req;
		/* for DEBUGGING
		$host = 'localhost';
		$get  = '/jamit/jobboard/include/plugins/SimplyHiredXML/sample.xml';
		*/
		
		//echo $host . $get; // for DEBUGGING
		

		if ($this->config['curl' ]== 'Y') {
			$fp = $this->curl_request($host, $get);		
			}
		else {
			$fp = @fsockopen ($host, 80, $errno, $errstr, 10);
			}
		
		if ($fp) {
		
			if ($this->config['curl']=='Y') {
				$sent = true;
			} else {
				$send  = "GET $get HTTP/1.0\r\n"; // dont need chunked so use HTTP/1.0
				$send .= "Host: $host\r\n";
				$send .= "User-Agent: Jamit Job Board (www.jamit.com)\r\n";
				$send .= "Referer: ".JB_BASE_HTTP_PATH."\r\n";
				$send .= "Content-Type: text/xml\r\n";
				$send .= "Connection: Close\r\n\r\n"; 
				$sent = fputs ($fp, $send, strlen($send) ); // get
				
			}
			
			if ($sent) {
			
				$start = false;
				while (!feof($fp)) { // skip the header
					$res = fgets ($fp);					
					if (strpos($res, "<?xml")!==false) $start = true;
					if ($start) break;
					}
				
				// parse the xml file to get the posts
				$parser = new SimplyHiredXMLParser($fp);
				$this->posts = $parser->get_posts();
				$this->total_results = $parser->total_results;
				
				// custom compare function for usort()
				if (!function_exists('sh_my_cmp')) {
	
				// custom compare function for usort()
					function sh_my_cmp($a, $b) {
						return strcmp($b["date"], $a["date"]);
					}
				}
				// sort the results by date
				if ($this->config['so']=='custom') {
					usort($this->posts, 'sh_my_cmp');
				}
				
				////////////////////////////////////////////////
				// how many results to ignore? (remove from array, so that we don't show duplicate results)
				if ($ignoreCnt > 0) {
					$n = 0;
					foreach ($this->posts as $key => $val) {
						if ($key < $ignoreCnt) unset($this->posts[$key]);
						$n ++;
						}
					}
				
			
			} else {
				echo 'failed to send header';
			}

			fclose($fp);
			if ($this->config['curl']=='Y') {
				$this->curl_cleanup($fp);		
			} 
		} else {
			echo "cannot connect to $host";
		}

	}

	////////////////////////////////////////////////////////////////
	// just filling empty slots on page (no pagination is being used)
	
	function set_count(&$count, $list_mode) {

		if (($list_mode!='ALL') && ($list_mode!='BY_CATEGORY')) return;

		$free_slot_count =  JB_POSTS_PER_PAGE - ($count % JB_POSTS_PER_PAGE);
		

		// are we on the last page?

		$last_page = ceil($count / JB_POSTS_PER_PAGE);

		$this_page = ($_REQUEST['offset'] / JB_POSTS_PER_PAGE) +1;

		if ($last_page == $this_page) {
			$pageNum = 1; $wSize = $free_slot_count; $ignoreCnt=0;
			$this->do_request($pageNum, $wSize, $ignoreCnt);
		}
		


		return;
		
	/*	

			if ($count < JB_POSTS_PER_PAGE) { // lets see if there are some slots that can be filled on next page(s)

				$pageNum = 1;
				$wSize = JB_POSTS_PER_PAGE;
				$ignoreCnt = 0;
				$this->do_request($pageNum, $wSize, $ignoreCnt);

				$free_slots = JB_POSTS_PER_PAGE-$count;

				if ($free_slots > sizeof($this->posts)) { // there are more free slots than posts
					$count = $count + (sizeof($this->posts));
				} else {
					$count = ($count + $free_slots);

				}

			} else {
				$count = $count + ($count % JB_POSTS_PER_PAGE);
			}

*/
	}

	///////////////////////////////////////////////////////////////
	// using pagination
	
	function set_count2(&$count, $list_mode) {

		if (($list_mode!='ALL') && ($list_mode!='BY_CATEGORY')) return;
		
		$offset = (int) $_REQUEST['offset'];
		
		if ($count > 0) {
			$max_local_pages = ceil($count / JB_POSTS_PER_PAGE); // number of the page that have local posts
			$max_local_offset = ($max_local_pages * JB_POSTS_PER_PAGE) - JB_POSTS_PER_PAGE;
			$last_page_local_post_count = $count % JB_POSTS_PER_PAGE; // number of local posts on the last page (remainder)
			$correction = 0;
			} 
		elseif ($count == 0) {
			$last_page_local_post_count = 0;
			$correction = -1; // if local job count is zero, this corrects an error
			}
		
		$currPage = 1 + $offset/JB_POSTS_PER_PAGE; // number of current page
		
		$pageDiff = $currPage - $max_local_pages + $correction;
		
		if ($pageDiff >= 0) {
			
			// SimplyHired posts that were already shown on previous pages
			if ($pageDiff == 0) {
				// same page as the one with local posts
				$alreadyShown = 0;
				}
			else {
				$alreadyShown = ($pageDiff - 1) * JB_POSTS_PER_PAGE + JB_POSTS_PER_PAGE - $last_page_local_post_count;
				}
			
			
			$ignoreCnt = 0;
			$wSize = 2*JB_POSTS_PER_PAGE - $last_page_local_post_count;

			
			for ($r = false; $r == false; ) {
					
				$pageNum = floor($alreadyShown/$wSize) + 1;
				
				if ($alreadyShown > 0) $ignoreCnt = $alreadyShown - $wSize*($pageNum - 1);
				
				if ($wSize - $ignoreCnt >= JB_POSTS_PER_PAGE) {
					$r = true;
					}
				else {
					$wSize ++;
					if ($wSize > 100) $wSize = JB_POSTS_PER_PAGE;
					}
				
				}
			}
		else {
			// we are not viewing page with SimplyHired posts
			$pageNum   = 1; // default value
			$wSize     = 1; // default value
			$ignoreCnt = 0; // default value
			}
		
		// for DEBUGGING
		/*
		echo 'pageDiff: '. $pageDiff . '<br>';
		echo 'alreadyShown: '. $alreadyShown . '<br>';
		echo 'currPage: '. $currPage . '<br>';
		echo 'pageNum: '. $pageNum . '<br>';
		echo 'ignoreCnt: '. $ignoreCnt . '<br>';
		*/
		
		$this->do_request($pageNum, $wSize, $ignoreCnt);

		$count = $count + $this->total_results;

		}

	//////////////////////////////////////////////////////
	function selfURL() {
		$pageURL = 'http';
		if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
			$pageURL .= "s";
			}
		$pageURL .= "://". $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		return $pageURL;
		}

	//////////////////////////////////////////////////////
	function meta_tags() {

		global $SEARCH_PAGE;
		
		global $CATEGORY_PAGE;
	
		//  job list, from index.php
		global $JOB_LIST_PAGE;

		// home page flag, from index.php
		global $JB_HOME_PAGE;

		if ($JB_HOME_PAGE || $CATEGORY_PAGE || $JOB_LIST_PAGE || $SEARCH_PAGE) {
			

			?>

			<script type="text/javascript"
	src="http://www.simplyhired.com/ads/apiresults.js"></script>

			<?php
		}

	}

	// include/lists.inc.php - JB_echo_job_list_data() function

	function job_list_data_val(&$val, $template_tag) {

		if (!$this->fill_in_progress) return; // is there a fill in progress?


		$val = '';

		$LL = &$this->JB_get_markup_obj(); // load the ListMarkup Class

		if ($template_tag=='DATE') {

			$val = JB_get_formatted_date($this->current_post['date']);
		}

		if ($template_tag=='LOCATION') {
			

			if ($this->current_post['city']) {
				$val .= $comma.$this->current_post['city'];
				$comma = ', ';
			}
			if ($this->current_post['state']) {
				$val .= $comma.$this->current_post['state'];
				$comma = ', ';
			}
			if ($this->current_post['country']) {
				$val .= $comma.$this->current_post['country'];
				
			}
		}


		if ($template_tag=='TITLE') {
			$val =  '<span class="job_list_title" ><A onmousedown="'.$this->current_post['onmousedown'].'" href="'.jb_escape_html($this->current_post['url']).'">'.$this->current_post['title'].'</A></span>';
		}

		
		if ($template_tag=='POST_SUMMARY') {

			$val =  '<span class="job_list_title" ><A onmousedown="'.$this->current_post['onmousedown'].'" href="'.jb_escape_html($this->current_post['url']).'">'.$this->current_post['title'].'</A></span><br>';
			$val .= '<span class="job_list_small_print">source:</span> <span class="job_list_cat_name">'.$this->current_post['source'].'</span><br>';
			$val .= '<span class="job_list_small_print">'.$this->current_post['snippet'].'</span>';
			"Post summary";
				
		}
		

	}

	function &JB_get_markup_obj() {
		if (function_exists('JB_get_secret_hash')) {
			// since 3.7
			$List = &JBDynamicList::factory('JBPostListMarkup');
			$a = array();
			$List->set_values($a); // list always needs this
			return $List->LMarkup;
            //return JB_get_PostListMarkupObject();
        } elseif (function_exists('JB_get_PostListMarkupObject')) {
			return JB_get_PostListMarkupObject();
		} elseif (function_exists('JB_get_PostListMarkupClass')) {
			return JB_get_PostListMarkupClass();
		} else {
			echo "Warning: The Indeed.com XML back-fill plugin needs Jamit Job Board 3.5.0 or higher. Please disable the plugin and upgrade your software. 202";
		}
	}

	function list_back_fill(&$count, $list_mode) {

		if (!function_exists('JB_get_PostListMarkupObject') && !function_exists('JB_get_PostListMarkupClass')) {

			echo "Warning: The Simply Hired XML back-fill plugin needs Jamit Job Board 3.5.0 or higher. Please disable the plugin and upgrade your software.";

			return false;

		}

		if (($list_mode!='ALL') && ($list_mode!='BY_CATEGORY')) return;
		$this->fill_in_progress = true;
		//$i=0;
		
		$i=$count;
		
		$pp_page = JB_POSTS_PER_PAGE;
		if ((sizeof($this->posts)>0) && ($i<$pp_page)) {
			$LL = &$this->JB_get_markup_obj(); // load the ListMarkup Class
			$LL->list_day_of_week('<div style="text-align: right;"><a STYLE="text-decoration:none" href="http://www.simplyhired.com/"><span style="color: rgb(0, 0,
0);">Jobs</span></a> by <a STYLE="text-decoration:none" href="http://www.simplyhired.com/"><span style="color: rgb(0, 159, 223); font-weight: bold;">Simply</span><span style="color: rgb(163, 204, 64); font-weight: bold;">Hired</span></a></div>', 'around_the_web');
			foreach ($this->posts as $post) {
				if ($i>=$pp_page) {
					break;
				}
				$this->list_job($post);
				$i++;
				
			}
		}
		$this->fill_in_progress = false;

	}

/*

The following function is not implemneted.

	function list_pre_fill(&$count, $list_mode) {

		if (($list_mode!='ALL') && ($list_mode!='BY_CATEGORY')) return;
		$this->fill_in_progress = true;

		$pp_page = JB_POSTS_PER_PAGE;
		if ((sizeof($this->posts)>0) && ($i<$pp_page)) {
			$LL = &$this->JB_get_markup_obj(); // load the ListMarkup Class
			$LL->list_day_of_week('Job Postings from the Web - <span id=indeed_at><a href="http://www.simplyhired.com/">jobs</a> by <a
href="http://www.simplyhired.com/" title="Job Search"><img
src="http://www.simplyhired.com/p/jobsearch.gif" style="border: 0;
vertical-align: middle;" alt="Indeed job search"></a></span>
', 'around_the_web');

			foreach ($this->posts as $post) {
				if ($i>=$pp_page) {
					break;
				}
				$this->list_job($post);
				$i++;
				
			}
		}

		$this->fill_in_progress = false;


	}

	*/


	function list_job(&$post) {

		static $previous_day;

		$this->current_post = $post;

		$LL = &$this->JB_get_markup_obj(); // load the ListMarkup Class

		$count++;

		$POST_MODE = 'normal';		

		$class_name = $LL->get_item_class_name($POST_MODE);
		$class_postfix = $LL->get_item_class_postfix($POST_MODE);

		$DATE = $this->current_post['date'];
		
	    # display day of week
		if ((JB_POSTS_SHOW_DAYS_ELAPSED == "YES") && ($this->config['day']=='Y')) {
			//echo $prams['post_date'];

			$day_and_week = JB_get_day_and_week (JB_trim_date($DATE));

			if (JB_trim_date($DATE) !== JB_trim_date($previous_day)) { // new day?
				
				if ($day_and_week!='') {
					$LL->list_day_of_week($day_and_week, $class_postfix);
				}	
			}
			$previous_day = $DATE;

		}

		########################################
		# Open the list data items
		
		$LL->list_item_open($POST_MODE, $class_name);
	   
		########################################################################

		JB_echo_job_list_data($admin); // display the data cells

		########################################################################
		# Close list data items
		$LL->list_item_close();

	}



	function keyword_page() {

		if ($_REQUEST['p']=='SimplyHiredXML') {
			require (dirname(__FILE__).'/keywords.php');
		}

	}

	/**
	 *@deprecated - use JB_does_field_exist() instead
     *
	 */
	 function does_field_exist($table, $field) {
		if (function_exists('JB_does_field_exist')) {
			return JB_does_field_exist($table, $field);
		}
		$result = jb_mysql_query("show columns from `".jb_escape_sql($table)."`");
		while ($row = @JB_mysql_fetch_row($result)) {
			if ($row[0] == $field) {
				return true;
			}
		}

		return false;

	}


	// for the configuration options in Admin:

	function echo_tt_options($value, $type='') {

		if (function_exists('JB_get_DynamicFormObject')) {
			$PForm = JB_get_DynamicFormObject(1);
			$post_tag_to_search = $PForm->get_tag_to_search();
		} else {
			global $post_tag_to_search;
			$post_tag_to_search = JB_get_tag_to_search(1);
		}
	
		require_once (jb_basedirpath()."include/posts.inc.php");

		

		foreach ($post_tag_to_search as $key=>$tag) {
			if ($key !='') {

				$sel ="";
				if (is_array($value)) { // multiple selected

					if (in_array($key, $value)) {
						$sel = ' selected ';
					}

				} else {
					if ($key == $value) {
						$sel = ' selected ';

					}
				}

				if ($type!='') {

					// echo only for the $type

					if ($tag[$key]['field_type'] == $type ) {

						echo '<option  '.$sel.' value="'.$key.'">'.JB_truncate_html_str($tag['field_label'], 50, $foo).'</option>\n';
						$output = true;
					}

				} else {

					// echo all
					echo '<option '.$sel.' value="'.$key.'">'.JB_truncate_html_str($tag['field_label'], 50, $foo).'</option>\n';
					$output = true;
				}
				
			}
		}

		if ($output == false) {
			echo '<option>[There are no '.$type.' fields to select]</option>';
		}
		
	}

	// Test for PHP bug: http://bugs.php.net/bug.php?id=45996
	function bug_test() {

		$data="<?xml version = '1.0' encoding = 'UTF-8'?>
		<test>
		  &amp;
		</test>
		";

		$parser = xml_parser_create('UTF-8');
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $data, $vals, $index);
		xml_parser_free($parser);

		if ($vals[0]['value']!='&') {
			return true; // bug detected
		}


	}


	////////////////////////////////////////////

	function get_name() {
		return "Simply Hired XML Back-fill";

	}

	function get_description() {

		return "Back-fill un-used job posting slots with search results from simplyhired.com";
	}

	function get_author() {
		return "Jamit Software";

	}

	function get_version() {
		return "1.5";

	}

	function get_version_compatible() {
		return "3.5.0+";

	}

	# Check the JB_ENABLED_PLUGINS constant to see if this plugin is enabled.
	# Each plugin must have this method implemented in the following way:
	function is_enabled() {

		if (JB_ENABLED_PLUGINS!='') {
			$enabled_plugins = explode(',', JB_ENABLED_PLUGINS);
			if (in_array($this->plugin_name, $enabled_plugins)) {
				return true;
			}
			return false;
		}

	}

	# Enable the plugin. Call the parent class.
	# Each plugin must have this method implemented in the following way:
	function enable() {
		if (!$this->is_enabled()) {

			parent::enable($this->plugin_name);

			if (!$this->does_field_exist('SimplyHiredXML_keywords', 'category_id') ) {

				$sql = "CREATE TABLE `SimplyHiredXML_keywords` (
					  `category_id` int(11) NOT NULL default '0',
					  `kw` varchar(255) NOT NULL default '',
					  `loc` varchar(255) NOT NULL default '',  
					  PRIMARY KEY  (`category_id`)
					) ENGINE=MyISAM";
				jb_mysql_query($sql);
			}


		}

	}

	# Disable the plugin.
	# Each plugin must have this method implemented in the following way:
	function disable() {
		if ($this->is_enabled()) {
			parent::disable($this->plugin_name);
		}
	}

	# display the configuration form
	# You may design your form however you like!
	# Please make sure the it sends the following hidden fields:
	# type="hidden" name="plugin" 
	# type="hidden" name="action" 
	# You can access the config variables like this: $this->config['users_min']
	function config_form() { // 
		 ?>
		
		<h2>Simply Hired XML Back-fill Configuration</h2>
		
		<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
		<table border="0" cellpadding="5" cellspacing="2" style="border-style:groove" id="AutoNumber1" width="100%" bgcolor="#FFFFFF">
		
		
		
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Country</b></td>
			<td  bgcolor="#e6f2ea"><select  name="c" value="<?php echo $this->config['c']; ?>">

			
			<option value="au" <?php if ($this->config['c']=="au") echo ' selected '; ?>>Australia</option>
			<option value="at" <?php if ($this->config['c']=="at") echo ' selected '; ?>>Austria</option>
			<option value="be" <?php if ($this->config['c']=="be") echo ' selected '; ?>>Belgium</option>
			<option value="br" <?php if ($this->config['c']=="br") echo ' selected '; ?>>Brazil</option>
			<option value="ca" <?php if ($this->config['c']=="ca") echo ' selected '; ?>>Canada</option>
			<option value="cn" <?php if ($this->config['c']=="cn") echo ' selected '; ?>>China</option>
			<option value="fr" <?php if ($this->config['c']=="fr") echo ' selected '; ?>>France</option>
			<option value="de" <?php if ($this->config['c']=="de") echo ' selected '; ?>>Germany</option>
			<option value="in" <?php if ($this->config['c']=="in") echo ' selected '; ?>>India</option>
			<option value="ie" <?php if ($this->config['c']=="ie") echo ' selected '; ?>>Ierland</option>
			<option value="it" <?php if ($this->config['c']=="it") echo ' selected '; ?>>Italy</option>
			<option value="jp" <?php if ($this->config['c']=="jp") echo ' selected '; ?>>Japan</option>
			<option value="kr" <?php if ($this->config['c']=="kr") echo ' selected '; ?>>Korea</option>
			<option value="nl" <?php if ($this->config['c']=="nl") echo ' selected '; ?>>Netherlands</option>
			<option value="mx" <?php if ($this->config['c']=="mx") echo ' selected '; ?>>Mexico</option>
			<option value="es" <?php if ($this->config['c']=="es") echo ' selected '; ?>>Spain</option>
			<option value="ch" <?php if ($this->config['c']=="ch") echo ' selected '; ?>>Switzerland</option>
			<option value="uk" <?php if ($this->config['c']=="uk") echo ' selected '; ?>>United Kingdom</option>
			<option value="us" <?php if ($this->config['c']=="us") echo ' selected '; ?>>US</option>
			
			</select>
			</td>
		</tr>

		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Result Style</b></td>
			<td  bgcolor="#e6f2ea"><input type="radio" name="ssty" <?php if ($this->config['ssty']=='1') { echo ' checked '; }  ?> value="1"> All sponsored jobs, followed by zero organic jobs (US Only)<br>
			<input type="radio" name="ssty" <?php if ($this->config['ssty']=='2') { echo ' checked ';} ?> value="2"> All posted jobs, followed by all sponsored jobs, followed by all organic jobs (Sponsored jobs US Only)<br>
			<input type="radio" name="ssty" <?php if ($this->config['ssty']=='3') { echo ' checked '; } ?> value="3"> All organic jobs (If you are not seeing any results, please try this option)<br>
			
			</td>
		</tr>
		
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Sort by</b></td>
			<td  bgcolor="#e6f2ea"><input type="radio" name="so" <?php if ($this->config['so']=='dd') { echo ' checked '; } elseif($this->config['so']=='') {echo ' checked';} ?> value="dd"> Date, descending (default)<br>
			<input type="radio" name="so" <?php if ($this->config['so']=='da') { echo ' checked ';} ?> value="da"> Date, ascending<br>
			<input type="radio" name="so" <?php if ($this->config['so']=='rd') { echo ' checked '; } ?> value="rd"> Relevance, descending<br>
			<input type="radio" name="so" <?php if ($this->config['so']=='ra') { echo ' checked '; } ?> value="ra"> Relevance, ascending<br>
			<input type="radio" name="so" <?php if ($this->config['so']=='td') { echo ' checked '; } ?> value="td"> Title, descending<br>
			<input type="radio" name="so" <?php if ($this->config['so']=='ta') { echo ' checked '; } ?> value="ta"> Title, ascending<br>
			<input type="radio" name="so" <?php if ($this->config['so']=='cd') { echo ' checked '; } ?> value="cd"> Company, descending<br>
			<input type="radio" name="so" <?php if ($this->config['so']=='ca') { echo ' checked '; } ?> value="ca"> Company, ascending<br>
			<input type="radio" name="so" <?php if ($this->config['so']=='ld') { echo ' checked '; } ?> value="ld"> Location, descending<br>
			<input type="radio" name="so" <?php if ($this->config['so']=='la') { echo ' checked '; } ?> value="la"> Location, ascending<br>
			<input type="radio" name="so" <?php if ($this->config['so']=='custom') { echo ' checked '; } ?> value="custom"> Relevance + Date (Jamit does additional sorting so that the relevant results are sorted by date. CPU intensive)
			</td>
		</tr>
		<!-- NOT USED
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Site Type</b></td>
			<td  bgcolor="#e6f2ea"><input type="radio" name="st" <?php if ($this->config['st']=='jobsite') { echo ' checked '; } ?> value="jobsite"> Job Site: To show jobs only from job board sites<br>
			<input type="radio" name="st" <?php if ($this->config['st']=='employer') { echo ' checked '; } ?> value="employer">Show jobs only direct from employer sites<br>
			<input type="radio" name="st" <?php if ($this->config['st']=='') { echo ' checked '; } ?> value="">Show from all<br>
			</td>
		</tr>
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Job Type</b></td>
			<td  bgcolor="#e6f2ea">
			<input type="radio" name="jt" <?php if ($this->config['jt']=='fulltime') { echo ' checked '; } ?> value="fulltime"> Get Full Time jobs<br>
			<input type="radio" name="jt" <?php if ($this->config['jt']=='parttime') { echo ' checked '; } ?> value="parttime"> Get Part Time jobs<br>
			<input type="radio" name="jt" <?php if ($this->config['jt']=='contract') { echo ' checked '; } ?> value="contract"> Get Contract jobs<br>
			<input type="radio" name="jt" <?php if ($this->config['jt']=='internship') { echo ' checked '; } ?> value="internship"> Get Intership jobs<br>
			<input type="radio" name="jt" <?php if ($this->config['jt']=='temporary') { echo ' checked '; } ?> value="temporary"> Get temporary jobs<br>
			<input type="radio" name="jt" <?php if ($this->config['jt']=='') { echo ' checked '; } ?> value=""> Get all types of jobs
			</td>
		</tr>
		-->
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Radius</b></td>
			<td  bgcolor="#e6f2ea"><input size="3" type="text" name='r' value="<?php echo $this->config['r']; ?>"> Distance in miles from search location ("as the crow flies"). Default is 25.
			</td>
		</tr>
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Maximum Age</b></td>
			<td  bgcolor="#e6f2ea"><input size="3" type="text" name="age" value="<?php echo $this->config['age']; ?>"> (Number of days back to search. Default/Max is 30)
			</td>
		</tr>
<!--
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>highlight</b></td>
			<td  bgcolor="#e6f2ea"><input type="radio" name="h" <?php if ($this->config['h']=='1') { echo ' checked '; } ?> value="1"> Yes, highlight keywords<br>
			<input type="radio" name="h" <?php if ($this->config['h']=='0') { echo ' checked '; } ?> value="0"> No)
			</td>
		</tr>
	-->
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Filter Results</b></td>
			<td  bgcolor="#e6f2ea"><input type="radio" name="f" <?php if ($this->config['f']=='1') { echo ' checked '; } ?> value="1"> Yes, filter duplicate results<br>
			<input type="radio" name="f" <?php if ($this->config['f']=='0') { echo ' checked '; } ?> value="0"> No
			</td>
		</tr>

		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>How to Back-fill?</b></td>
			<td  bgcolor="#e6f2ea">
			<input type="radio" name="fill" <?php if ($this->config['fill']=='S') { echo ' checked '; } ?> value="S"> Stop after filling the first page<br>
			<input type="radio" name="fill" <?php if ($this->config['fill']=='C') { echo ' checked '; } ?> value="C"> Continue to futher pages (pagination) if more results are available
			</td>
		</tr>
	
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Show the Day, and how many days elapsed?</b></td>
			<td  bgcolor="#e6f2ea">
			<input type="radio" name="day" <?php if ($this->config['day']=='Y') { echo ' checked '; } ?> value="Y"> Yes (default)<br>
			<input type="radio" name="day" <?php if ($this->config['day']=='N') { echo ' checked '; } ?> value="N"> No 
			</td>
		</tr>

		
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Main Keyword(s)</b></td>
			<td  bgcolor="#e6f2ea"><input size="50" type="text" name="k" value="<?php echo jb_escape_html($this->config['k']); ?>"><br>
			Examples:<br>
			manager<br>
			title(engineering manager)<br>
			title(manager) company(Kaiser)<br>
			(By default terms are AND'ed. For more details visit http://www.simplyhired.com/.)
			</td>
		</tr>
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Main Location</b></td>
			<td  bgcolor="#e6f2ea"><input size="20" type="text" name='l' value="<?php echo jb_escape_html($this->config['l']); ?>"> (Location is optional. e.g. San Jose, CA)
			</td>
		</tr>
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Search Field(s) for Keyword</b></td>
			<td  bgcolor="#e6f2ea">
			<select name="k_tag[]" multiple size="5">
				<!--<option value="">[Select]</option>-->
				<?php echo $this->echo_tt_options($this->config['k_tag']); ?>
				</select> (The selected search parameters will be combined and used as the keywords for the search query sent to Indeed. If not selected or no keyword is searched, then it will default to the Main Keyword. Hold down the Ctrl key to select/unselect multiple items)
			</td>
		</tr>
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Search Field(s) for Location</b></td>
			<td  bgcolor="#e6f2ea">
			<select name="l_tag[]" multiple size="5" >
				<!--<option value="">[Select]</option>-->
				<?php echo $this->echo_tt_options($this->config['l_tag']); ?>
			</select> (The selected search parameters will be combined and used as the location for the search query sent to Indeed. If not selected or no location is searched, then it will default to the Main Location. Hold down the Ctrl key to select/unselect multiple items)
			</td>
		</tr>
		<tr>
			<td  bgcolor="#e6f2ea" colspan="2"><font face="Verdana" size="1"><input type="submit" value="Save"></td>
		</tr>
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>API Key</b></td>
			<td  bgcolor="#e6f2ea"><input size="20" type="text" name='id' value="<?php echo $this->config['id']; ?>"> (Simply Hired publisher ID; For details, please see http://www.simplyhired.com/a/publishers/overview). 
			</td>
		</tr>
		</table>
		
		<h3>Advanced Settings</h3>
		
		<table border="0" cellpadding="5" cellspacing="2" style="border-style:groove;" width="100%" bgcolor="#FFFFFF">
		<tr>
	      <td  bgcolor="#e6f2ea">Use cURL (Y/N)</td>
	      <td  bgcolor="#e6f2ea">
		  <input type="radio" name="curl" value="N"  <?php if ($this->config['curl']=='N') { echo " checked "; } ?> >No (Normally, this option is the best.)<br>
		  <input type="radio" name="curl" value="Y"  <?php if ($this->config['curl']=='Y') { echo " checked "; } ?> >Yes - If your hosting company blocked fsockopen() and has cURL, then use this option</td>
	    </tr>
		<tr>
	      <td  bgcolor="#e6f2ea">cURL Proxy URL</td>
	      <td  bgcolor="#e6f2ea">
	      <input type="text" name="proxy" size="50" value="<?php echo $this->config['proxy']; ?>">Leave blank if your server does not need one. Contact your hosting company if you are not sure about which option to use. For GoDaddy it is: http://proxy.shr.secureserver.net:3128<br></td>
	    </tr>
		<tr>
			<td  bgcolor="#e6f2ea" colspan="2"><font face="Verdana" size="1"><input type="submit" value="Save"></td>
		</tr>

		</table>
		<input type="hidden" name="plugin" value="<?php echo $_REQUEST['plugin'];?>">
		<input type="hidden" name="action" value="save">

		</form>
		<?php
		if ($this->bug_test()) {
			echo "<p><font color='red'>PHP Bug warning: The system detected that your PHP version has a bug in the XML parser. This is not a bug in the Jamit Job Board, but a bug in 'libxml' that comes built in to PHP itself. An upgrade of PHP with the latest version of 'libxml' with  is recommended. This plugin contains a workaround for this bug - so it should still work...</font> For details about the bug, please see <a href='http://bugs.php.net/bug.php?id=45996'>http://bugs.php.net/bug.php?id=45996</a></p> ";

		}
		// check if fsockopen is disabled
		if (stristr(ini_get('disable_functions'), "fsockopen")) {
			JB_pp_mail_error ( "<p>fsockopen is disabled on this server. You can try to set this plugin to use cURL instead</p>");
		
		}
		?>
		<b>Important:</b> After configuring Go here to <a href="p.php?p=SimplyHiredXML&action=kw">Configure Category Keywords</a>
<p>
TROUBLE SHOOTING
<p>
> Keywords do not return any results?
Try your keyword on simplyhired.com first, before putting them in the job board.
<i>Notice: There are three result styles which can be switched on the form above.</i> 
<p>
> Page times out / does not fetch any results?
Your server must be able to make external connections to api.simplyhired.com
through port 80 (HTTP). This means that fsockopen must be enabled on
your host, and must be allowed to make external connections.
<p>
- I see warning/errors messages saying that 'argument 2' is missing.
This has been reported and can be fixed if you open the include/lists.inc.php
file and locate the following code:
<p>
JBPLUG_do_callback('job_list_data_val', $val, $template_tag);
<p>
and change to:
<p>
JBPLUG_do_callback('job_list_data_val', $val, $template_tag, $a);
<p>
- Can I make the links open in a new window?
<p>
Nope.. SimplyHired rules are that in order to record the click, we must use their 
onmousedown event to call their javascript, and the javascripts 
prevents the link from opening in a new window.
<p>
- It still does not work
<p>
Please check the requirements - requires Jamit Job Board 3.5.0 or higher
Please also check with your hosting company that your server
is allowed to use fsockopen or Curl
		 <?php

	}

	# save the values from your config form
	# The values will be serialized and saved in config.php
	# After the $this->plugin_name parameter, enter the list of variables like this:

	function save_config() {
		if (is_array($_REQUEST['l_tag'])) {
			$_REQUEST['l_tag'] = implode(',',$_REQUEST['l_tag']);
		}
		if (is_array($_REQUEST['k_tag'])) {
			$_REQUEST['k_tag'] = implode(',',$_REQUEST['k_tag']);
		}

		# JBPLUG_save_config_variables ( string $class_name [, string $field_name [, string $...]] )
		JBPLUG_save_config_variables($this->plugin_name, 'priority', 'l', 'k', 'id', 'l_tag', 'k_tag', 's', 'curl', 'proxy', 'fill', 'c', 'f', 'age', 'rpp', 'h', 'r', 'st', 'so', 'jt', 'ssty', 'day' );
	}

	function get_cache_dir() {

		if (function_exists('JB_get_cache_dir')) {
			return JB_get_cache_dir();
		} else {
			static $dir;
			if ($dir) return $dir;

			$dir = dirname(__FILE__);
			$dir = preg_split ('%[/\\\]%', $dir);
			$blank = array_pop($dir);
			$blank = array_pop($dir);
			$blank = array_pop($dir);
			$dir = implode('/', $dir).'/cache/';

			return $dir;

		}

	}

}

$_JB_PLUGINS['SimplyHiredXML'] = new SimplyHiredXML; // add a new instance of the class to the global plugins array

?>