<?php
# Copyright Jamit Software, 2011

# CareerJet.php - for importing CareerJet's XML Job Search Feed

# Important:
# At the bottom if the file, this statement should exist.
# $_JB_PLUGINS['CareerJet'] = new CareerJet; // add a new instance of the class to the global plugins array

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
# - IMPORTANT: Be sure to scape data before outputting it to the page       #
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

class CareerJet extends JB_Plugins {

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

	function CareerJet() {

		require (dirname(__FILE__).'/Services_Careerjet.php'); 


		$this->plugin_name = "CareerJet"; // set this to the name of the plugin. Case sensitive. Must be exactly the same as the directory name and class name!

		parent::JB_Plugins(); // initalize JB_Plugins

		// Prepare the config variables
		// we simply extract them from the serialized variable like this:

		if ($this->config==null) { // older versions of jamit did not init config
			$config = unserialize(JB_PLUGIN_CONFIG);
			$this->config = $config[$this->plugin_name];
		}

		# initialize the priority
		if ($this->config['priority']=='') {
			$this->config['priority']=5;
		}

		if ($this->config['k']=='') {
			$this->config['k']='php';
		}

		

		if ((!$this->config['so'])) {
			$this->config['so']='date'; // sort type
		}

		
		if ($this->config['h']=='') {
			$this->config['h']='0'; // highlight keywrods?
		}

		if ($this->config['l']=='') {
			$this->config['l']='';
		}

		if ($this->config['k_tag']=='') {
			$this->config['k_tag'][]='TITLE';
		} else {
			// convert to array
			$this->config['k_tag'] = explode (',', $this->config['k_tag']);
		}

		if ($this->config['l_tag']=='') {
			$this->config['l_tag'][]='LOCATION';
		} else {
			// convert to array
			$this->config['l_tag'] = explode (',', $this->config['l_tag']);
		}

		if (!$this->config['att']) {
			$this->config['att'] = 'Y';
		}



		if ($this->config['id']=='') {
			$this->config['id']='09d1598b91e4e0b87d0dd7dcf75a180e';
		}


	/*	if ($this->config['lim']=='') { // limit
			$this->config['lim']=10;
		}*/

		$this->config['lim'] = JB_POSTS_PER_PAGE;

		

		if ($this->config['curl']=='') { // cURL
			$this->config['curl']='N';
		}

		if ($this->config['fill']=='') { // results fill mode
			$this->config['fill']='S';
		}

		
		if ($this->is_enabled()) {
			// register all the callbacks
		
			///////////////////////////////////////////

			if ($this->config['fill']=='S') {
				JBPLUG_register_callback('job_list_set_count', array($this->plugin_name, 'set_count'), $this->config['priority']);
			} else {
				JBPLUG_register_callback('job_list_set_count', array($this->plugin_name, 'set_count2'), $this->config['priority']);
			}

			JBPLUG_register_callback('index_extra_meta_tags', array($this->plugin_name, 'meta_tags'), $this->config['priority']);

		
			JBPLUG_register_callback('job_list_data_val', array($this->plugin_name, 'job_list_data_val'), $this->config['priority']);

			JBPLUG_register_callback('job_list_back_fill', array($this->plugin_name, 'list_back_fill'), $this->config['priority']);

			JBPLUG_register_callback('admin_plugin_main', array($this->plugin_name, 'keyword_page'), $this->config['priority']);

			
			
		}

	}

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
		$filename = $this->get_cache_dir().md5(time().$this->config['id'].$r).'_careerjet.xml';
	
		$fp = fopen($filename, 'w');
		fwrite($fp, utf8_encode($result), strlen(utf8_encode($result)));
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

		$sql = "SELECT * FROM CareerJet_keywords WHERE category_id='".jb_escape_sql($cat_id)."' ";
		$result = jb_mysql_query($sql);
		$row = mysql_fetch_array($result, MYSQL_ASSOC);

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


	function do_request($start='') {

		if ($start<1) { // cannot have 0 or negative
			$start = '';
		}

		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$ip_addr = $_SERVER['REMOTE_ADDR'];

		

		############################################################################
		# Process the keywords

		// Set the default keywords.
		// These will be overwritten if user inputed keywords are available
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

					if (strlen($temp_keys)>0) {
						$temp_keys_space = ' ';
					}
					if (strlen($temp_loc)>0) {
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
									$or = ' or ';
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
							// the location keys are placed in to a seperate string
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
			} elseif ($temp_cat_loc!='') { // the 'where' keywords were empty, so perhaps they were set by a category?
			
				$location_q = $temp_cat_loc;
			}

		}

		$api = new Services_Careerjet($this->config['c']) ;
		

		$page = round($start / $this->config['lim']) +1;

		$result = $api->search(
			array( 
				'keywords' => $keyword_q,
				'location' => $location_q,
				'page' => $page ,
				'affid' => $this->config['id'],
				'pagesize' => $this->config['lim'],
				'sort' => $this->config['so'],
				'contracttype' => $this->config['jt'],
				'contractperiod' => $this->config['jp'],
			), 
			array(
				'curl' => $this->config['curl'],
				'curl_proxy' => $this->config['proxy']
				)
			);

		if ( $result->type == 'JOBS' ) {
			//echo "Found ".$result->hits." jobs" ;
			//echo " on ".$result->pages." pages\n" ;
			//$jobs = $result->jobs ;


			foreach( $result->jobs as $job ) {

				$this->posts[] = array (
					'title' => JB_utf8_to_html($job->title),
					'company '=> JB_utf8_to_html($job->company),
					'city' => '',
					'state '=> '',
					'country '=> '',
					'locations' => JB_utf8_to_html($job->locations),
					'source' => JB_utf8_to_html($job->company),
					'date' => JB_get_formatted_date(jb_get_local_time($job->date)),
					'snippet' => JB_utf8_to_html($job->description),
					'url' => $job->url,
					'onmousedown' => '',
					'guid' => $job->url
					);


				/*
				sample given by careerjet:	
				echo " URL:     ".$job->url."\n" ;
				echo " TITLE:   ".$job->title."\n" ;
				echo " LOC:     ".$job->locations."\n";
				echo " COMPANY: ".$job->company."\n" ;
				echo " SALARY:  ".$job->salary."\n" ;
				echo " DATE:    ".$job->date."\n" ;
				echo " DESC:    ".$job->description."\n" ;
				echo "\n" ;
				*/
			}

			$this->total_results = $result->hits;

		}
		
		############################################################################

/*
		$channel = '&chnl='.urlencode($this->config['ch']);
		$sort = $this->config['so'];
		if ($sort=='custom') {
			$sort = 'relevance';
		}

		$page = round($start / $this->config['lim']) +1;
		
		$req = 'partnerid='.$this->config['id'].'&k='.urlencode($keyword_q).'&l='.urlencode($location_q).'&order='.$sort.'&r='.$this->config['r'].'&page='.$page.'&jpp='.$this->config['lim'].'&days='.$this->config['age'].'&highlight='.$this->config['h'].'&ipaddress='.urlencode($ip_addr).'&useragent='.urlencode($user_agent).$channel;
		
		//echo $req;

		$host = $this->config['s'];//'api.indeed.com';
		$get = '/jobs?'.$req;

		// for testing:
		//$host = '127.0.0.1';
		//$get = '/JamitJobBoard-3.5.0a/include/plugins/CareerJet/sample.xml?'.$req;

		if ($this->config['curl']=='Y') {
			$fp = $this->curl_request($host, $get);		
		} else {
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
			
				
				while (!feof($fp)) { // skip the header
					$res = fgets ($fp);
					if (strpos($res, "<?xml")!==false) break;
				}
				
				// parse the xml file to get the posts
				//$parser = new CareerJetParser($fp);
				//$this->posts = $parser->get_posts();
				//$this->total_results = $parser->total_results;

				// custom compare function for usort()
				function my_cmp($a, $b) {
					return strcmp($b["date"], $a["date"]);
				}

				// sort the results by date
				if ($this->config['so']=='custom') {
					usort($this->posts, 'my_cmp');
				}
				
			
			} else {
				//echo 'failed to send header';
			}

			fclose($fp);
			if ($this->config['curl']=='Y') {
				$this->curl_cleanup($fp);		
			} 
		} else {
			//echo "cannot connect to $host";
		}

		*/

	}


	function set_count(&$count, $list_mode) {

		if (($list_mode!='ALL') && ($list_mode!='BY_CATEGORY')) return;
		

			if ($count < JB_POSTS_PER_PAGE) { // there are some slots that can be filled

				$this->do_request();

				$free_slots = JB_POSTS_PER_PAGE-$count;

				if ($free_slots > sizeof($this->posts)) { // there are more free slots than posts
					$count = $count + (sizeof($this->posts));
				} else {
					$count = ($count + $free_slots);

				}

			} else {
				$count = $count + ($count % JB_POSTS_PER_PAGE);
			}
	}

	function set_count2(&$count, $list_mode) {

		if (($list_mode!='ALL') && ($list_mode!='BY_CATEGORY')) return;

		$offset = (int) $_REQUEST['offset'];
		
	

		if ($count > 0) {

			$max_local_pages = ceil($count / JB_POSTS_PER_PAGE);
			$max_local_offset = ($max_local_pages * JB_POSTS_PER_PAGE) - JB_POSTS_PER_PAGE;
			$last_page_local_post_count = $count % JB_POSTS_PER_PAGE; // number of local posts on the last page (remainder)
			$start_skew = JB_POSTS_PER_PAGE - $last_page_local_post_count;
			$start = (($offset-$max_local_offset)-JB_POSTS_PER_PAGE)+$start_skew;
		} elseif ($count==0) {
			$start = $offset;
		}
		
		$this->do_request($start);

		$count = $count + $this->total_results;
		

	}


	function meta_tags() {

		global $SEARCH_PAGE;
		
		global $CATEGORY_PAGE;
	
		//  job list, from index.php
		global $JOB_LIST_PAGE;

		// home page flag, from index.php
		global $JB_HOME_PAGE;

		if ($JB_HOME_PAGE || $CATEGORY_PAGE || $JOB_LIST_PAGE || $SEARCH_PAGE) {
			

			// additional javascript for click tracking may go here
			?>

			

			<?php
		}

	}

	// include/lists.inc.php - JB_echo_job_list_data() function

	function job_list_data_val(&$val, $template_tag) {

		

		if (!$this->fill_in_progress) return; // is there a fill in progress?
$val= '';
		global $JobListAttributes;


		$LM = &$this->JB_get_markup_obj(); // load the ListMarkup Class

		if ($template_tag=='DATE') {
			$val = JB_get_formatted_date($this->current_post['date']);
		}

		if ($template_tag=='LOCATION') {
			/*$state_comma = ($this->current_post['state']) ? ',':'';
			$country_comma = ($this->current_post['state']) ? ',':'';
			$val = $this->current_post['city'].$state_comma.' '.$this->current_post['state'].$country_comma.' '.$this->current_post['country'];
			*/
			$val = $this->current_post['locations'];
		}


		if ($template_tag=='TITLE') {
			$val =  '<span class="job_list_title" ><a  href="'.jb_escape_html($this->current_post['url']).'">'.$this->current_post['title'].'</a></span>';
		}

		
		if ($template_tag=='POST_SUMMARY') {

			$val =  '<span class="job_list_title" ><A onclick="'.$this->current_post['onclick'].'" href="'.jb_escape_html($this->current_post['url']).'">'.$this->current_post['title'].'</A></span><br>';
			$val .= '<span class="job_list_small_print">source:</span> <span class="job_list_cat_name">'.$this->current_post['source'].'</span><br>';
			$val .= '<span class="job_list_small_print">'.$this->current_post['snippet'].'</span>';
			"Post summary";
				
		}
		

	}

	function &JB_get_markup_obj() {
		if (function_exists('JB_get_PostListMarkupObject')) {
			return JB_get_PostListMarkupObject();
		} elseif (function_exists('JB_get_PostListMarkupClass')) {
			return JB_get_PostListMarkupClass();
		} else {
			echo "Warning: The CareerJet.com back-fill plugin needs Jamit Job Board 3.5.0 or higher. Please disable the plugin and upgrade your software. 202";
		}
	}

	function list_back_fill(&$count, $list_mode) {

		if (!function_exists('JB_get_PostListMarkupObject') && !function_exists('JB_get_PostListMarkupClass')) {

			echo "Warning: The CareerJet.com back-fill plugin needs Jamit Job Board 3.5.0 or higher. Please disable the plugin and upgrade your software. 515";

			return false;

		}

		if (($list_mode!='ALL') && ($list_mode!='BY_CATEGORY')) return;
		$this->fill_in_progress = true;
		//$i=0;
		
		$i=$count;
		
		$pp_page = JB_POSTS_PER_PAGE;
		if ((sizeof($this->posts)>0) && ($i<$pp_page)) {
			$LM = $this->JB_get_markup_obj(); // load the ListMarkup Class
			if ($this->config['att']=='Y') {
				$LM->list_day_of_week('<div style="float:right"><span><a href="http://www.careerjet.com/">jobs</a> by <a
href="http://www.careerjet.com/" title="Job Search"><img width="85"
src="http://www.careerjet.com/images/logo_88x31_uk.gif" style="border: 0;
vertical-align: middle;" alt="CareerJet.com"></a></span></div>
', 'around_the_web');
			}
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



	function list_job(&$post) {

		static $previous_day;

		$this->current_post = $post;

		$LM = &$this->JB_get_markup_obj(); // load the ListMarkup Class

		$count++;

		$POST_MODE = 'normal';		

		$class_name = $LM->get_item_class_name($POST_MODE);
		$class_postfix = $LM->get_item_class_postfix($POST_MODE);

		$DATE = $this->current_post['date'];
		
	    # display day of week
		if (JB_POSTS_SHOW_DAYS_ELAPSED == "YES") {
			//echo $prams['post_date'];

			$day_and_week = JB_get_day_and_week (JB_trim_date($DATE));

			if (JB_trim_date($DATE) !== JB_trim_date($previous_day)) { // new day?
				
				if ($day_and_week!='') {
					$LM->list_day_of_week($day_and_week, $class_postfix);
				}	
			}
			$previous_day = $DATE;

		}

		########################################
		# Open the list data items
		
		$LM->list_item_open($POST_MODE, $class_name);
	   
		########################################################################

		JB_echo_job_list_data($admin); // display the data cells

		########################################################################
		# Close list data items
		$LM->list_item_close();

	}



	function keyword_page() {

		if ($_REQUEST['p']=='CareerJet') {
			require (dirname(__FILE__).'/keywords.php');
		}

	}

	function does_field_exist($table, $field) {
		global $jb_mysql_link;
		$result = jb_mysql_query("show columns from `".jb_escape_sql($table)."`");
		while ($row = @mysql_fetch_row($result)) {
			if ($row[0] == $field) {
				return true;
			}
		}

		return false;

	}


	// for the configuration options in Admin:

	function echo_tt_options($value, $type='') {

		$PForm = JB_get_DynamicFormObject(1);
		$post_tag_to_search = $PForm->get_tag_to_search();
	
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

						echo '<option  '.$sel.' value="'.$key.'">'.JB_truncate_html_str($tag['field_label'], 50, $foo).'</option>'."\n";
						$output = true;
					}

				} else {

					// echo all
					echo '<option '.$sel.' value="'.$key.'">'.JB_truncate_html_str($tag['field_label'], 50, $foo).'</option>'."\n";
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
		return "CareerJet Partner";

	}

	function get_description() {

		return "Back-fill un-used job posting slots with search results from CareerJet";
	}

	function get_author() {
		return "Jamit Software";

	}

	function get_version() {
		return "1.3";

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

			if (!$this->does_field_exist('CareerJet_keywords', 'category_id') ) {

				$sql = "CREATE TABLE `CareerJet_keywords` (
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
		<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
		<table border="0" cellpadding="5" cellspacing="2" style="border-style:groove" width="100%" bgcolor="#FFFFFF">
		
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Publisher ID</b></td>
			<td  bgcolor="#e6f2ea"><input size="40" type="text" name='id' value="<?php echo jb_escape_html($this->config['id']); ?>"> (Your CareerJet Affid ID, please visit <a style="font-weight:bold" href="http://www.careerjet.com/partners/?ak=09d1598b91e4e0b87d0dd7dcf75a180e" target="_blank">CareerJet Partners</a> site to register - follow the link and click 'Create your partner account' at the bottom of the page to register)
			</td>
		</tr>
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Locale</b></td>
			<td  bgcolor="#e6f2ea">

			<?php

				 $locale2base = array( 
                           'cs_CZ'  => "http://www.careerjet.cz",
                           'da_DK'  => "http://www.careerjet.dk",
                           'de_AT'  => "http://www.careerjet.at",
                           'de_CH'  => "http://www.careerjet.ch",
                           'de_DE'  => "http://www.careerjet.de",
                           'en_AE'  => "http://www.careerjet.ae",
                           'en_AU'  => "http://www.careerjet.com.au",
                           'en_CA'  => "http://www.careerjet.ca",
                           'en_CN'  => "http://en.careerjet.cn",
                           'en_HK'  => "http://www.careerjet.hk",
                           'en_IE'  => "http://www.careerjet.ie",
                           'en_IN'  => "http://www.careerjet.co.in",
                           'en_MY'  => "http://www.careerjet.com.my",
                           'en_NZ'  => "http://www.careerjet.co.nz",
                           'en_OM'  => "http://www.careerjet.com.om",
                           'en_PH'  => "http://www.careerjet.ph",
                           'en_PK'  => "http://www.careerjet.com.pk",
                           'en_QA'  => "http://www.careerjet.com.qa",
                           'en_SG'  => "http://www.careerjet.sg",
                           'en_GB'  => "http://www.careerjet.co.uk",
                           'en_UK'  => "http://www.careerjet.co.uk",
                           'en_US'  => "http://www.careerjet.com",
                           'en_ZA'  => "http://www.careerjet.co.za",
                           'en_TW'  => "http://www.careerjet.com.tw",
                           'en_VN'  => "http://www.careerjet.vn",
                           'es_AR'  => "http://www.opcionempleo.com.ar",
                           'es_BO'  => "http://www.opcionempleo.com.bo",
                           'es_CL'  => "http://www.opcionempleo.cl",
                           'es_CR'  => "http://www.opcionempleo.co.cr",
                           'es_DO'  => "http://www.opcionempleo.com.do",
                           'es_EC'  => "http://www.opcionempleo.ec",
                           'es_ES'  => "http://www.opcionempleo.com",
                           'es_GT'  => "http://www.opcionempleo.com.gt" ,
                           'es_MX'  => "http://www.opcionempleo.com.mx",
                           'es_PA'  => "http://www.opcionempleo.com.pa",
                           'es_PE'  => "http://www.opcionempleo.com.pe",
                           'es_PR'  => "http://www.opcionempleo.com.pr",
                           'es_PY'  => "http://www.opcionempleo.com.py",
                           'es_UY'  => "http://www.opcionempleo.com.uy",
                           'es_VE'  => "http://www.opcionempleo.com.ve",
                           'fi_FI'  => "http://www.careerjet.fi",
                           'fr_BE'  => "http://www.optioncarriere.be",
                           'fr_CA'  => "http://fr.careerjet.ca" ,
                           'fr_CH'  => "http://www.optioncarriere.ch",
                           'fr_FR'  => "http://www.optioncarriere.com",
                           'fr_LU'  => "http://www.optioncarriere.lu",
                           'fr_MA'  => "http://www.optioncarriere.ma",
                           'hu_HU'  => "http://www.careerjet.hu",
                           'it_IT'  => "http://www.careerjet.it",
                           'ja_JP'  => "http://www.careerjet.jp",
                           'ko_KR'  => "http://www.careerjet.co.kr",
                           'nl_BE'  => "http://www.careerjet.be",
                           'nl_NL'  => "http://www.careerjet.nl",
                           'no_NO'  => "http://www.careerjet.no",
                           'pl_PL'  => "http://www.careerjet.pl",
                           'pt_PT'  => "http://www.careerjet.pt",
                           'pt_BR'  => "http://www.careerjet.com.br",
                           'ru_RU'  => "http://www.careerjet.ru",
                           'ru_UA'  => "http://www.careerjet.com.ua",
                           'sv_SE'  => "http://www.careerjet.se",
                           'sk_SK'  => "http://www.careerjet.sk",
                           'tr_TR'  => "http://www.careerjet.com.tr",
                           'uk_UA'  => "http://www.careerjet.ua",
                           'vi_VN'  => "http://www.careerjet.com.vn",
                           'zh_CN'  => "http://www.careerjet.cn"
		   
						);

			?>
			<select   name="c" value="<?php echo $this->config['c']; ?>">
			<?php
				foreach ($locale2base as $key => $val) { ?>
				<option value="<?php echo $key; ?>" <?php if ($this->config['c']==$key) echo ' selected '; ?>><?php echo $key; ?></option>
			<?php
			}

				?>
			</select> (Language/Country)
			
			</td>
		</tr>

		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Attribution</b></td>
			<td  bgcolor="#e6f2ea"><input type="radio" name="att" <?php if ($this->config['att']=='Y') { echo ' checked '; } ?> value="Y"> Yes (default - this will display a 'Jobs By CareerJet' link above the results, to distinguish your job posts from CareerJet's)<br>
			<input type="radio" name="att" <?php if ($this->config['att']=='N') { echo ' checked '; } ?> value="N"> No<br>
			
			</td>
		</tr>
		
		
		
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Sort</b></td>
			<td  bgcolor="#e6f2ea"><input type="radio" name="so" <?php if ($this->config['so']=='date') { echo ' checked '; } ?> value="date"> By Date Posted (default)<br>
			<input type="radio" name="so" <?php if ($this->config['so']=='relevance') { echo ' checked '; } ?> value="relevance"> By Relevance<br>
			<input type="radio" name="so" <?php if ($this->config['so']=='salary') { echo ' checked '; } ?> value="salary"> Biggest salary first
			</td>
		</tr>
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Job Types</b></td>
			<td  bgcolor="#e6f2ea">
			<input type="radio" name="jt" <?php if ($this->config['jt']=='') { echo ' checked '; } ?> value=""> All Jobs<br>
			<input type="radio" name="jt" <?php if ($this->config['jt']=='p') { echo ' checked '; } ?> value="p"> Permanent jobs<br>
			<input type="radio" name="jt" <?php if ($this->config['jt']=='c') { echo ' checked '; } ?> value="c"> Contract<br>
			<input type="radio" name="jt" <?php if ($this->config['jt']=='t') { echo ' checked '; } ?> value="t"> Temporary<br>
			<input type="radio" name="jt" <?php if ($this->config['jt']=='i') { echo ' checked '; } ?> value="i"> Training<br>
			<input type="radio" name="jt" <?php if ($this->config['jt']=='v') { echo ' checked '; } ?> value="v"> Voluntary<br>
			</td>
		</tr>

		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Job Period</b></td>
			<td  bgcolor="#e6f2ea">
			<input type="radio" name="jp" <?php if ($this->config['jp']=='') { echo ' checked '; } ?> value=""> All Jobs<br>
			<input type="radio" name="jp" <?php if ($this->config['jp']=='f') { echo ' checked '; } ?> value="f"> Full-time<br>
			<input type="radio" name="jp" <?php if ($this->config['jp']=='p') { echo ' checked '; } ?> value="p"> Part-time<br>
			
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
				<b>How to Back-fill?</b></td>
			<td  bgcolor="#e6f2ea">
			<input type="radio" name="fill" <?php if ($this->config['fill']=='S') { echo ' checked '; } ?> value="S"> Stop after filling the first page<br>
			<input type="radio" name="fill" <?php if ($this->config['fill']=='C') { echo ' checked '; } ?> value="C"> Continue to futher pages (if more results are available)
			</td>
		</tr>

		
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Main Keyword(s)</b></td>
			<td  bgcolor="#e6f2ea"><input size="20" type="text" name='k' value="<?php echo jb_escape_html($this->config['k']); ?>"> (eg. Title:accounting, By default terms are AND'ed. To see what is possible, use their advanced search page for more possibilities http://www.careerjet.com/search/advanced.html)
			</td>
		</tr>
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Main Location</b></td>
			<td  bgcolor="#e6f2ea"><input size="20" type="text" name='l' value="<?php echo jb_escape_html($this->config['l']); ?>"> (Location is optional. e.g. US)
			</td>
		</tr>
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Search Field(s) for Keyword</b></td>
			<td  bgcolor="#e6f2ea">
			<select name="k_tag[]" multiple size="5">
				<!--<option value="">[Select]</option>-->
				<?php echo $this->echo_tt_options($this->config['k_tag']); ?>
				</select> (The selected search parameters will be combined and used as the keywords for the search query sent to CareerJet. If not selected or no keyword is searched, then it will default to the Main Keyword. Hold down the Ctrl key to select/unselect multiple items)
			</td>
		</tr>
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Search Field(s) for Location</b></td>
			<td  bgcolor="#e6f2ea">
			<select name="l_tag[]" multiple size="5" >
				<!--<option value="">[Select]</option>-->
				<?php echo $this->echo_tt_options($this->config['l_tag']); ?>
			</select> (The selected search parameters will be combined and used as the location for the search query sent to CareerJet. If not selected or no location is searched, then it will default to the Main Location. Hold down the Ctrl key to select/unselect multiple items)
			</td>
		</tr>
		
		<tr><td colspan="2">Advanced Settings</td>
		</tr>
		<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Use cURL (Y/N)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
       <br>
	  <input type="radio" name="curl" value="N"  <?php if ($this->config['curl']=='N') { echo " checked "; } ?> >No - Normally this option is best<br>
	  <input type="radio" name="curl" value="Y"  <?php if ($this->config['curl']=='Y') { echo " checked "; } ?> >Yes - If your hosting company blocked fsockopen() and has cURL, then use this option</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">cURL 
      Proxy URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="proxy" size="50" value="<?php echo $this->config['proxy']; ?>">Leave blank if your server does not need one. Contact your hosting company if you are not sure about which option to use. For GoDaddy it is: http://proxy.shr.secureserver.net:3128<br></font></td>
    </tr>
		<tr>
			<td  bgcolor="#e6f2ea" colspan="2"><font face="Verdana" size="1"><input type="submit" value="Save">
		</td>
		</tr>
		</table>
		<input type="hidden" name="plugin" value="<?php echo jb_escape_html($_REQUEST['plugin']);?>">
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
		<b>Important:</b> After configuring Go here to <a href="p.php?p=CareerJet&action=kw">Configure Category Keywords</a>
<p>
TROUBLE SHOOTING
<p>
> Keywords do not return any results?
Try your keyword on careerjet.com first, before putting them in the job board.
<p>
> Page times out / does not fetch any results?
Your server must be able to make external connections to http://www.careerjet.com/
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
Nope.. careerjet rules are that in order to record the click, it must use their 
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
		JBPLUG_save_config_variables($this->plugin_name, 'priority', 'l', 'k', 'jp', 'id', 'l_tag', 'k_tag', 'curl', 'proxy', 'fill', 'c',  'h',  'so', 'jt', 'att' );
	}

	function get_cache_dir() {

		if (function_exists('JB_get_cache_dir')) {
			return JB_get_cache_dir();
		} else {
			static $dir;
			if (isset($dir)) return $dir;
			
			$dir = dirname(__FILE__);
			$dir = preg_split ('%[/\\\]%', $dir);
			$blank = array_pop($dir);
			$blank = array_pop($dir);
			$blank = array_pop($dir);
			$dir = implode('/', $dir).'/cache/';
			JBPLUG_do_callback('get_cache_dir', $dir);
			return $dir;

		}

	}

}

$_JB_PLUGINS['CareerJet'] = new CareerJet; // add a new instance of the class to the global plugins array
?>