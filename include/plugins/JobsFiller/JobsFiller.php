<?php
# Copyright Jamit Software, 2010

# IndeedXML.php - for importing Indeed's XML Job Search Feed

# Important:
# At the bottom if the file, this statement should exist.
# $_JB_PLUGINS['IndeedXML'] = new IndeedXML; // add a new instance of the class to the global plugins array


# Hint set_count was set to 0 for testing!

/*

This agreement between You ("Publisher") and Jamit Software Limited. ("Jamit") sets out the terms and conditions ("Terms and Conditions") applicable to your participation in the Jamit Job Filler (the "Program"). The Program, as generally offered by Jamit Software, is described on http://api.jamit.com or such other URL as Jamit Software may provide from time to time. "You" or "Publisher" means any entity identified in an enrollment form submitted by the same or affiliated persons, and/or any agency or network acting on its (or their) behalf, which shall also be bound by these Terms and Conditions.

Jamit Software shall have absolute discretion as to whether or not it accepts a particular applicant or site for participation in the Program. Sites are ineligible to participate if they do not conform with the terms of the Jamit Software Affiliate Acceptable Use Policy, located online at AAUP (the 'AAUP'). In order to participate as a publisher in the Program, all participants must be at least eighteen years of age. Applicants represent and warrant that all information submitted to Jamit Software shall be true, accurate and complete.

PDS

The Jobs Filler plugin can fill a job board with supplementary job posts. These job posts can be indexed by the search engines, which may bring your job board free organic traffic. A visitor does not have to leave the site to view these jobs, the full content of the job is displayed locally. The job board can display ads within the job post, including Google Adsense.

The jobs come form our business partner, JobG8 http://www.jobg8.com.au/

We index these jobs in our database, and then provide an method for distributing these jobs form our servers to your job board.
Applications are collected by our partner, JobG8. Your user does not need to complete leave your site to complete an application. The applications are then distributed by JobG8 to the original job poster.

This service is provided free of charge.

This agreement does not entitle any relationship, sub-license or sub-contract, either explicit or implied, between the Publisher and JobG8. This agreement is non-exclusive; the Publisher may enter in to direct relationship with JobG8 if desired.


Publisher Obligation

 
Jamit software does not send any payment for the service. 

The application link must not be tampered with or concealed.

Publisher agrees to abide by all terms of the AAUP. Jamit Software reserves the right in its sole discretion to suspend Publisher's participation in the Program if it suspects any violations of the AAUP. Publisher hereby agrees to defend, indemnify and hold Jamit Software harmless from and against any claims, demands, liabilities, expenses, losses, damages and attorney fees arising from or relating to a violation or purported violation by Publisher of the AAUP. The foregoing shall be in addition to, and not in lieu of, any other remedies that Jamit Software may have as a result of a violation of the AAUP by Publisher.

Termination
- Any time, by sending an email

Confidentiality

- Keep API key secret

Limitation of Liability


Privacy
'API call' is when your website contacts our server to request some
function, for example, request for the search results.
When making an API call, the following data is sent to our server:
- The API function call, parameters of the function call include
keywords, reference_id, location and publisher_id. All function
calls also include the User Agent, IP Address and Referral Address
of the source client which made the request. We use this data
only for the purpose of our system so that we can distinguish bots from
real a person, and to deliver the data in the most efficient manner.
All data is logged on our server. This data is not shared with anyone else,
unless we (unlikely) get an official court order, such as a subpoena. 
The log is then processed to generate a report. The raw logs are
deleted after one week, but may be kept for longer or deleted
earlier.


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




class JobsFiller extends JB_Plugins {

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

	var $invisible = false;
	

	function JobsFiller() {

		require (dirname(__FILE__).'/JobsFillerXMLParser.php'); 

		$this->plugin_name = "JobsFiller"; // set this to the name of the plugin. Case sensitive. Must be exactly the same as the directory name and class name!

		parent::JB_Plugins(); // initalize JB_Plugins

		// Prepare the config variables
		// we simply extract them from the serialized variable like this:

		if ($this->config==null) { // older versions of jamit did not init config
			$config = unserialize(JB_PLUGIN_CONFIG);
			$this->config = $config[$this->plugin_name];
		}

		$this->config['fill'] = 'C';

		# initialize the priority
		if (!isset($this->config['priority'])) {
			$this->config['priority']=5;
		}

		if (!isset($this->config['k'])) {
			$this->config['k']='php';
		}

	
		if (!isset($this->config['l'])) {
			$this->config['l']='';
		}

		if (!isset($this->config['k_tag'])) {
			$this->config['k_tag'][]='TITLE';
		} else {
			// convert to array
			$this->config['k_tag'] = explode (',', $this->config['k_tag']);
		}

		if (!isset($this->config['l_tag'])) {
			$this->config['l_tag'][]='LOCATION';
		} else {
			// convert to array
			$this->config['l_tag'] = explode (',', $this->config['l_tag']);
		}



		if (!isset($this->config['id'])) {
			$this->config['id']='2451470435917521';
		}

		if ($this->config['map']) {
			//$this->config['map'] = '2=jobtitle,5=description,6=class,8=company,15=location';
			// convert  in to
			// array.
			$temp = explode(',', $this->config['map']);
			//echo $this->config['map'];
			if (sizeof($temp)) {

				foreach ($temp as $item) {
					$pair = explode ('=', $item);
					$map_array[$pair[0]] = $pair[1];
				}

				$this->config['map'] = $map_array;

			}

		}

		if (!isset($this->config['src'])) {
			$this->config['src'] = array('JobServe');
		} else  {
			//$this->config['src'] = explode (',', $this->config['src']);
		}

		if (!isset($this->config['cnt'])) {
			$this->config['cnt'] = array('US');
		} else {
			//$this->config['cnt'] = explode (',', $this->config['cnt']);
			//echo $this->config['cnt'];
		}

		if (!isset($this->config['typ'])) {
			$this->config['typ'] = 'ALL';
		}

		if (!sizeof($this->config['map'])) {
			//$this->config['id']='2451470435917521';
			$this->config['map'] = array (
				'2' => 'jobtitle',
				'5' => 'description',
				'6' => 'class',
				'8' => 'company',
				'15' => 'location',
			);
		}


	/*	if ($this->config['lim']=='') { // limit
			$this->config['lim']=10;
		}*/

		$this->config['lim'] = JB_POSTS_PER_PAGE;

		
		$this->config['s']='api.jamit.com'; // jamit rocks!
		

		if ($this->config['curl']=='') { // cURL
			$this->config['curl']='N';
		}

		if (!function_exists('curl_init')) {
			$this->config['curl']='N';
		}

		$this->config['ad'] = base64_decode($this->config['ad']);


		
		if ($this->is_enabled()) {
			// register all the callbacks
		
			///////////////////////////////////////////

			//if ($this->config['fill']=='S') {
			//	JBPLUG_register_callback('job_list_set_count', array($this->plugin_name, 'set_count'), $this->config['priority']);
			//} else {
			JBPLUG_register_callback('job_list_set_count', array($this->plugin_name, 'set_count'), $this->config['priority']);
			//}

			JBPLUG_register_callback('index_extra_meta_tags', array($this->plugin_name, 'meta_tags'), $this->config['priority']);

		
			JBPLUG_register_callback('job_list_data_val', array($this->plugin_name, 'job_list_data_val'), $this->config['priority']);

			JBPLUG_register_callback('job_list_back_fill', array($this->plugin_name, 'list_back_fill'), $this->config['priority']);

			JBPLUG_register_callback('admin_plugin_main', array($this->plugin_name, 'keyword_page'), $this->config['priority']);

			//JBPLUG_register_callback('admin_menu_job_post', array($this->plugin_name, 'admin_menu'), $this->config['priority']);

			JBPLUG_register_callback('admin_plugin_main', array($this->plugin_name, 'admin_main_page'), $this->config['priority']);

			//JBPLUG_register_callback('job_list_custom_query', array($this->plugin_name, 'blank_query'), $this->config['priority']);

			
			
			JBPLUG_register_callback('home_plugin_main', array($this->plugin_name, 'post_page'), $this->config['priority']);

			JBPLUG_register_callback('index_set_meta_title', array($this->plugin_name, 'set_page_title'), $this->config['priority']);

			JBPLUG_register_callback('index_set_meta_descr', array($this->plugin_name, 'set_page_descr'), $this->config['priority']);

			JBPLUG_register_callback('index_set_meta_kwords', array($this->plugin_name, 'set_page_kwords'), $this->config['priority']);


			JBPLUG_register_callback('display_custom_2col_field', array($this->plugin_name, 'blank_field'), $this->config['priority']);

			

			
			
			
		}

	}

	// Just for testing: return 0 local posts
	function blank_query(&$result, $sql) {
		//$result = jb_mysql_query('SELECT * FROM posts_table WHERE post_id >0 limit 1');
		
	}

	function admin_menu() {

		?>
		- <a href="p.php?p=JobsFiller.php" target="main">Jobs Filler</a><br>
		<?php

	}

	function admin_main_page () {

		if ($_REQUEST['p']=='JobsFiller') {

			if (function_exists('JB_admin_header')) { 
				JB_admin_header('Admin -> List Posts');
			}
			require_once ("../include/posts.inc.php");

			$_SESSION['show'] = 'FILLER';

			
			?>

			<p>
			<b>[POSTS]</b> <span style="background-color: <?php if ($_SESSION['show']=='ALL') echo "#FFFFCC"; else echo "#F2F2F2"; ?>; border-style:outset; padding:5px; "><a href="posts.php?show=ALL">Approved Posts</a></span>
				<span style="background-color: <?php if ($_SESSION['show']=='WA') echo "#FFFFCC"; else echo "#F2F2F2"; ?>; border-style:outset; padding: 5px;"><a href="posts.php?show=WA">New Posts Waiting</a></span>
				<span style="background-color: <?php if ($_SESSION['show']=='NA') echo "#FFFFCC"; else echo "#F2F2F2"; ?>; border-style:outset; padding: 5px;"><a href="posts.php?show=NA">Non-Approved Posts</a></span>
				<span style="background-color: <?php if ($_SESSION['show']=='EX') echo "#FFFFCC"; else echo "#F2F2F2"; ?>; border-style:outset; padding: 5px;"><a href="posts.php?show=EX">Expired Posts</a></span>
				<span style="background-color: <?php  echo "#F2F2F2"; ?>; border-style:outset; padding: 5px;"><a href="post_new.php">Post a Job</a></span>
			</p>

			<p>'Jobs Filler' - This feature fills your job board with supplementary job posts. These posts get indexed by the search engines, bringing your job board free organic traffic. Your visitor does not have to leave your site to view these jobs, the full content of the job is displayed on your site. This also allows you to insert ads inside the job post - bringing you both  ad revenue and free search engine traffic. 

			
			<?php

			if (function_exists('JB_admin_footer')) {
				JB_admin_footer();
			}

		}

			
	}

	function blank_field(&$field_row, $data, $admin, $mode) {

		if (isset($_REQUEST['sup']) && $_REQUEST['sup']) { // supplimentary result?

			if ($field_row['field_type']=='SEPERATOR') { // separator?
				return;
			}

			if (!trim($data[$field_row['field_id']])) { // there is no data in this field
				$field_row['field_type'] = 'PLUGIN'; // this field will be ignored
			}

		}	

	}

	function set_page_title(&$title) {
		if (isset($_REQUEST['ref_id']) && isset($_REQUEST['sup'])) {
			$DynamicForm = &JB_get_DynamicFormObject(1, 'global');
			$title =strip_tags( $DynamicForm->get_template_value('TITLE')).' | '.JB_SITE_NAME;
		}
	}

	function set_page_descr(&$descr) {
		if (isset($_REQUEST['ref_id']) && isset($_REQUEST['sup'])) {
			$DynamicForm = &JB_get_DynamicFormObject(1, 'global');
			$descr = substr(strip_tags($DynamicForm->get_template_value('DESCRIPTION')), 0, 128);
		}

	}

	function set_page_kwords(&$kwords) {
		if (isset($_REQUEST['ref_id']) && isset($_REQUEST['sup'])) {
			$DynamicForm = &JB_get_DynamicFormObject(1, 'global');
			
			$kwords = $DynamicForm->get_template_value('CLASS');
			$kwords .= ' '.$DynamicForm->get_template_value('TITLE');
			$kwords = str_replace('&amp;', '', $kwords);
			$kwords = preg_replace('#[, ]+#', ', ', $kwords);
		}
	}

	function post_page() {

		global $JBMarkup, $label;

		if (isset($_REQUEST['sup']) && $_REQUEST['sup']) {



			require_once (jb_basedirpath().'include/posts.inc.php');

		
			// Init data from the form

			$_REQUEST['ref_id'] = (int) $_REQUEST['ref_id'];
			

			$data = $this->fetch_job_post($_REQUEST['ref_id']);
			$data['post_id'] = $_REQUEST['ref_id'];


			$prams = $data;

			
			$DynamicForm = &JB_get_DynamicFormObject(1, 'global');
			$DynamicForm->set_values($data);



			foreach ($DynamicForm->tag_to_field_id as $key=>$field) {
				// change all fields to display as a text field
				if ($DynamicForm->tag_to_field_id[$key]['field_type']=='EDITOR') 
					continue;
				
				$DynamicForm->tag_to_field_id[$key]['field_type'] = 'TEXT';

			}

		

			$JBMarkup->enable_jquery();
			$JBMarkup->enable_colorbox();

			JB_template_index_header();
			$output_mode = 'FULL';
			//  

			// PLEASE NOTE: 
			// Requirement for api.jamit.com
			// This application button must be present when displaying
			// the job post. You may change the styles and apperance of this
			// button. However, please do not remove the button or tamper with
			// the URL
			
			// <div style="float:right;"<b> Search</b> <input type="text" size="10" value="what?"> <input type="text" size="10" value="city?"><input type="submit" value="Go"></div>
			//</div>

			$data['appurl'] = 'http://www.jamit.com/apply.php?ref_id='.$data['post_id'];
			?>

			<div style="background-color:#D9D9D9; with:100%; text-align:left;"><input type="button" class="form_apply_button" name="apply" value="<?php echo $label['post_apply_online'];?>" onclick="$.fn.colorbox({width:&quot;80%&quot;, height:&quot;80&%&quot;, iframe:true, scrolling:false, href:&quot;<?php echo $data['appurl']; ?>&quot;});" >
			
			<div style="float:right;"><a class="go_back" href="<?php echo htmlentities(JB_get_go_back_link()); ?>"><b><?php echo $label['post_display_goback_list']; ?></b></a></div>

			</div>
			<div class="jobs_filler_jobs" style="background-color:white; padding:5px">

			
			
			<div class="jobs_filler_details" style="text-align:left;">
			<h3><?php echo $DynamicForm->get_template_value('TITLE');?></h3>

		
			<div class="jobs_filler_ad" style="float:left; width:300px; overflow:visible; margin-right:5px;">
		<?php echo $this->config['ad'];?>
			</div>

			<?php
			echo $DynamicForm->get_template_value('DESCRIPTION');

			echo '</div>';

			$DynamicForm->display_form_section('view', 1, false);
			$DynamicForm->display_form_section('view', 2, false);
			$DynamicForm->display_form_section('view', 3, false);

			?>
			</div><div class="clear" style="clear:both"></div>

			<?php
	
			JB_template_index_footer();

		}


	}

			

	// returns a pointer to an open temp file
	function curl_request($host, $resource, $req, $reg_type='GET') {

		$URL = "http://".$host.$resource;

		$ch = curl_init();

		if ($this->config['proxy']!='') { // use proxy?
			curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
			curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			curl_setopt ($ch, CURLOPT_PROXY, $this->config['proxy']);
		}
		
		if ($req_type=='POST') {
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $req);
		} else {
			$URL .= '?'.$req;
		}

		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ($ch, CURLOPT_URL, $URL);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt ($ch, CURLOPT_POST, false);
		if ($_SESSION['API_SESSION']) {
			curl_setopt ($ch, CURLOPT_COOKIE, 'PHPSESSID='.$_SESSION['API_SESSION']);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


		$result = curl_exec ($ch);
		
		curl_close ($ch);

		

		// save the result in to a temp file, utf-8 encoded
		$r = rand (1,1000000); // random number for the file-name
		$filename = $this->get_cache_dir().md5(time().$this->config['id'].$r).'_filler.xml';
	
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

		$sql = "SELECT * FROM JobsFiller_keywords WHERE category_id='".jb_escape_sql($cat_id)."' ";
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

	function do_request($start='') {

		if ($start<1) { // cannot have 0 or negative
			$start = '';
		}

		//$user_agent = $_SERVER['HTTP_USER_AGENT'];
		//$ip_addr = $_SERVER['REMOTE_ADDR'];

		

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
			global $post_tag_to_search;

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

			//echo "temp keys: $temp_keys<br>";
			//echo "temp loc: $temp_loc<br>";

		}


		############################################################################

		
	

		$params = array (
			'pub' => $this->config['id'], 
			
			'q' => $keyword_q,
			'l' => $location_q,
			'start' => $start, // offset to start from
			'limit' => $this->config['lim'], // limit
			'cnt' =>$this->config['cnt'], // country
			'src' =>$this->config['src'], // source
			'typ' =>$this->config['typ'], // type
			'inv' => $this->invisible

		);
		
			
		if ($result = $this->api_call('search', $params)) {
			
			$this->posts = $result['posts'];
			$this->total_results = $result['total_results'];


		} else {
			//echo 'failed api_call';
			// 
		}

	
	}

	function api_cache_flush($days=0) {

		$days = (int) $days;

		if ($days > 0) {
			$days_sql = "WHERE DATE_SUB(NOW(), INTERVAL '".$days."' DAY) > `date` ";
		}

		$sql = "DELETE FROM jobsfiller_cache  $days_sql";
	
		jb_mysql_query($sql);

	}

	function api_call_cached($function='search', $params, $req_type='GET') {

		$hash = strtoupper(md5($function.implode(',', $params)));
		$sql = "SELECT `data` FROM jobsfiller_cache WHERE `key`= '".$hash."' LIMIT 1";
		//echo $sql.'<br>';
		$result = jb_mysql_query($sql);
		if (mysql_num_rows($result)==1) {
			return unserialize(array_pop(mysql_fetch_row($result)));
		} else {
			$data = $this->api_call($function, $params, $req_type);

			$sql = "REPLACE INTO jobsfiller_cache (`key`, `date`, `data`) VALUES ('$hash', NOW(), '".jb_escape_sql(addslashes(serialize($data)))."' ) ";
			jb_mysql_query($sql);

			//echo $sql.'<br>';

			return $data;
		}

	}

	###################################
	# $req_type 
	# - 'GET' should be used for fetching stuff
	# - 'POST' should be used for data insert, delete and update
	function api_call($function='search', $params, $req_type='GET') {


		$params['ip'] = $_SERVER['REMOTE_ADDR'];
		$params['agent'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 160); 
		//$params['ref'] = substr($_SERVER['HTTP_REFERER'], 0, 160);
		
		
		$req = 'f='.$function;
		foreach ($params as $key => $val) {
			$req .= '&'.$key.'='.urlencode($val);
		}
		

		$host = $this->config['s'];

		$resource = '/jobfiller.php';

		
//echo '<A href="http://'.$host.$resource.'?'.$req.'">Request</a>';
		

		if ($this->config['curl']=='Y') {
			$fp = $this->curl_request($host, $resource, $req, $req_type);		
		} else {
			$fp = @fsockopen ($host, 80, $errno, $errstr, 10);
		}
		
		if ($fp) {
		
			if ($this->config['curl']=='Y') {
				$sent = true;
			} else {

				if ($req_type=='GET') {
					$get = $resource.'?'.$req;
					$send  = "GET $get HTTP/1.0\r\n"; // dont need chunked so use HTTP/1.0
					$send .= "Host: $host\r\n";
					$send .= "User-Agent: Jamit Job Board (www.jamit.com)\r\n";
					$send .= "Referer: ".JB_BASE_HTTP_PATH."\r\n";
					if ($_SESSION['API_SESSION']) {
						$send .= "Cookie: PHPSESSID=".$_SESSION['API_SESSION']."\r\n";
					}
					$send .= "Content-Type: text/xml\r\n";
					$send .= "Connection: Close\r\n\r\n"; 
				
				} else {
					// Post the data
					$send = "POST ".$resource." HTTP/1.0\r\n";
					$send .= "Host: $host\r\n";
					$send .= "User-Agent: Jamit Job Board (www.jamit.com)\r\n";
					$send .= "Referer: ".JB_BASE_HTTP_PATH."\r\n";
					if ($_SESSION['API_SESSION']) {
						$send .= "Cookie: PHPSESSID=".$_SESSION['API_SESSION']."\r\n";
					}
					$send .= "Content-Type: application/x-www-form-urlencoded\r\n";
					$send .= "Content-Length: " . strlen($req) . "\r\n\r\n";
					$send .= $req; // post the request
				}


				if ($sent = fputs ($fp, $send, strlen($send)))  {  // do the request

					// skip headers...

					while (!feof($fp)) { // skip the header
						$res = fgets ($fp);
						if (preg_match ('#Set-Cookie: PHPSESSID=(.+?);#', $res, $m)) {
							// extracted the PHP session ID
							$_SESSION['API_SESSION'] = $m[1]; 
							//echo $_SESSION['API_SESSION'];
						}

						if (strcmp($res, "\r\n")===0) break;
					}

				}

			
			}

			if ($sent) { 

				if ($function == 'search') {
					// parse the xml file to get the posts
					$parser = new JobsFillerXMLParser($fp);
					$result['posts'] = $parser->get_posts();
					$result['total_results'] = $parser->get_total_results();
				} else {

					while(!feof($fp)) {
						$buffer .= fgets($fp);
					}
					$result = unserialize($buffer);

				}

			}

			if ($fp) {
				fclose($fp);
			}
			if ($this->config['curl']=='Y') {
				$this->curl_cleanup($fp);		
			}

			return $result;

		}


	}
		


	function set_count(&$count, $list_mode) {

		//if (isset($_REQUEST['show_emp'])) return;

		if (($list_mode!='ALL') && ($list_mode!='BY_CATEGORY')) return;

		$offset = (int) $_REQUEST['offset'];
		//\echo $count;
		//$count = 0;

		if ($count > 0) {

			$max_local_pages = ceil($count / JB_POSTS_PER_PAGE);
			$max_local_offset = ($max_local_pages * JB_POSTS_PER_PAGE) - JB_POSTS_PER_PAGE;
			$last_page_local_post_count = $count % JB_POSTS_PER_PAGE; // number of local posts on the last page (remainder)
			$start_skew = JB_POSTS_PER_PAGE - $last_page_local_post_count;
			$start = (($offset-$max_local_offset)-JB_POSTS_PER_PAGE)+$start_skew;
		} elseif ($count==0) {
			$start = $offset;
		}

		// will the results get rendered?
		if (($count-$offset) > JB_POSTS_PER_PAGE) {
			$this->invisible = true;
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
			
			// insert any code to include in the headers here

			?>

			

			<?php
		}

			
		if (isset($_REQUEST['sup']) && isset($_REQUEST['ref_id'])) {
			global $JBMarkup;
			$JBMarkup->stylesheet_link(JB_BASE_HTTP_PATH.'include/lib/colorbox/colorbox.css');
			
			?>
			

			<?php

		}

	}

	// include/lists.inc.php - JB_echo_job_list_data() function

	function job_list_data_val(&$val, $template_tag) {

		if (!$this->fill_in_progress) return; // is there a fill in progress?

		global $JobListAttributes;


		static $sup;
		if (!isset($sup)) {
			if ($JobListAttributes->query_string) {
				$JobListAttributes->query_string .= '&amp;';
			}
			$JobListAttributes->query_string .= 'sup=1';
			$sup = 1;
		}
		$internal_temp = $JobListAttributes->internal_page;
		//$JobListAttributes->internal_page= true; // turn off mod-rewrite


		$LM = &JB_get_PostListMarkupObject(); // load the ListMarkup Class

		if ($template_tag=='DATE') {
			$val = JB_get_formatted_date($this->current_post['date']);
		} elseif ($template_tag=='LOCATION') {

			$val='';

			if ($this->current_post['city']) {
				$comma = ', ';
				$val .= $this->current_post['city'];
			}

			if ($this->current_post['state']) {
				
				
				if ($this->current_post['city'] != $this->current_post['state']) {
					$val .= $comma;
					$comma = ', ';
					$val .= $this->current_post['state'];
				}
			}

			if ($this->current_post['country']) {
				$val .= $comma;
				$comma = ', ';
				$val .= $this->current_post['country'];
			}
			
		} elseif  ($template_tag=='TITLE') {
		
			$val =  '<span class="job_list_title" ><a class="job_list_title" href="p.php?ref_id='.$this->current_post['job_ref'].'&amp;sup=1">'.$this->current_post['title'].'</a></span>';
		} elseif  ($template_tag=='POST_SUMMARY') {



			$val =  '<span class="job_list_title" ><a class="job_list_title" href="p.php?ref_id='.$this->current_post['job_ref'].'&amp;sup=1">'.$this->current_post['title'].'</a></span><br>';

			if ($this->current_post['source']) {
				$val .= '<span class="job_list_small_print">source:</span> <span class="job_list_cat_name">'.$this->current_post['source'].'</span><br>';
			}

			$val .= '<span class="job_list_small_print">'.$this->current_post['snippet'].'</span>';
			"Post summary";
				
		} else {

			static $DynamicForm;
			if ($DynamicForm==null) {
				$DynamicForm = &JB_get_DynamicFormObject(1, 'global');
			}
			
			$field_id = $DynamicForm->tag_to_field_id[$template_tag]['field_id'];
			
			$val = $this->current_post[$this->config['map'][$field_id]];
			

		}

		$JobListAttributes->internal_page = $internal_temp;
		

	}

	function get_mappings() {

		
/*
		$mappings = array(
			'jobtitle' => '2',
			'description' => '5',
			'location' => '15',
			'date' => 'post_date',
			
		);
*/

		$mappings = array_flip($this->config['map']);
		$mappings['date'] = 'post_date';

		return $mappings;


	}

	function fetch_job_post($ref_id) {

		$params = array (
			'pub' => $this->config['id'], 
			'post_id' => (int) $ref_id
			
		);
		
			
		if ($post = $this->api_call('get_post', $params)) {

			

			$row['post_id'] = $post_id;
			$row['post_date'] = $post['date'];
			$row['appurl'] = $post['appurl'];
			$row['url'] = $post['url'];
			$row['source'] = $post['source'];

			$map = $this->get_mappings();

			foreach ($map as $key => $val) {
				$row[$val] = $post[$key];
			}

			global $prams;
			$prams = $row;

			return $row;

		}

	}

	

	function list_back_fill(&$count, $list_mode) {

		if (!function_exists('JB_clean')) {

			echo "Warning: The Jobs Filler plugin needs Jamit Job Board 3.6.0 or higher. Please disable the plugin and upgrade your software.";

			return false;

		}

		if (($list_mode!='ALL') && ($list_mode!='BY_CATEGORY')) return;
		$this->fill_in_progress = true;
		//$i=0;
		
		$i=$count;
		
		$pp_page = JB_POSTS_PER_PAGE;
		if ((sizeof($this->posts)>0) && ($i<$pp_page)) {
			$LM = &JB_get_PostListMarkupObject(); // load the ListMarkup Class
			$LM->list_day_of_week('Supplementary Results', 'around_the_web');
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

		$LM = &JB_get_PostListMarkupObject(); // load the ListMarkup Class

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

		if ($_REQUEST['p']=='JobsFiller') {
			require (dirname(__FILE__).'/keywords.php');
		}

	}

	function does_field_exist($table, $field) {
		global $jb_mysql_link;
		$result = mysql_query("show columns from `".jb_escape_sql($table)."`", $jb_mysql_link);
		while ($row = @mysql_fetch_row($result)) {
			if ($row[0] == $field) {
				return true;
			}
		}

		return false;

	}


	// for the configuration options in Admin:

	function echo_tt_options($value, $type='') {

		global $post_tag_to_search;
	
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
		return "Jamit Jobs Filler";

	}

	function get_description() {

		return "Fill your job board with real job ads";
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

			if (!$this->does_field_exist('JobsFiller_keywords', 'category_id') ) {

				$sql = "CREATE TABLE `JobsFiller_keywords` (
					  `category_id` int(11) NOT NULL default '0',
					  `kw` varchar(255) NOT NULL default '',
					  `loc` varchar(255) NOT NULL default '',  
					  PRIMARY KEY  (`category_id`)
					) ENGINE=MyISAM";
				jb_mysql_query($sql);

				$sql = 
					"CREATE TABLE `jobsfiller_job_cache` (
					  `post_id` int(11) NOT NULL,
					  `post_date` datetime NOT NULL,
					  `guid` varchar(255) NOT NULL,
					  `hash` varchar(128) NOT NULL,
					  `data` mediumtext NOT NULL,
					  `expired` CHAR(1) NOT NULL
					) ENGINE=MyISAM DEFAULT CHARSET=latin1;
					";
				jb_mysql_query($sql);
			}

			if (!$this->does_field_exist('jobsfiller_cache', 'key') ) {

				$sql = "CREATE TABLE `jobsfiller_cache` (
				  `key` varchar(128) NOT NULL,
				  `date` date NOT NULL,
				  `data` mediumtext NOT NULL,
				  PRIMARY KEY  (`key`),
				  KEY `date` (`date`)
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

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
		<table border="0" cellpadding="5" cellspacing="2" style="border-style:groove" id="AutoNumber1" width="100%" bgcolor="#FFFFFF">
		
		<tr>
			<td  colspan="2" bgcolor="#e6f2ea">
				<b>Jobs Filler - Configuration</b></td>
			
		</tr>

		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>API key</b></td>
			<td  bgcolor="#e6f2ea"><input size="20" type="text" name='id' value="<?php echo $this->config['id']; ?>"> (Your api.jamit.com key, get it from http://api.jamit.com)
			</td>
		</tr>
		<!--
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Country</b></td>
			<td  bgcolor="#e6f2ea"><select  name="c" value="<?php echo $this->config['c']; ?>">
			<option value="us" <?php if ($this->config['c']=="us") echo ' selected '; ?>>US</option>
			<option value="ca" <?php if ($this->config['c']=="ca") echo ' selected '; ?>>Canada</option>
			<option value="gb" <?php if ($this->config['c']=="gb") echo ' selected '; ?>>Great Britain</option>
			<option value="de" <?php if ($this->config['c']=="de") echo ' selected '; ?>>Germany</option>
			<option value="fr" <?php if ($this->config['c']=="fr") echo ' selected '; ?>>France</option>
			<option value="es" <?php if ($this->config['c']=="es") echo ' selected '; ?>>Spain</option>
			<option value="in" <?php if ($this->config['c']=="in") echo ' selected '; ?>>India</option>
			<option value="ie" <?php if ($this->config['c']=="ie") echo ' selected '; ?>>Ireland</option>
			<option value="nl" <?php if ($this->config['c']=="nl") echo ' selected '; ?>>Netherlands</option>
			</select>
			</td>
		</tr>
		-->
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Sources</b></td>
			<td  bgcolor="#e6f2ea">

		<?php

		$this->api_cache_flush(1); // flush older than one day

		$params = array (
				'pub' => $this->config['id']
		);
		$countries = $this->api_call_cached('get_country_list', $params);

		$params = array (
				'pub' => $this->config['id']
		);
		$sources = $this->api_call_cached('get_source_list', $params);

		$params = array (
				'pub' => $this->config['id']
		);
		$types = $this->api_call_cached('get_type_list', $params);

		
		echo '<p><b>Countries:</b> ';
		$pipe = '';
		if ('ALL' == $this->config['cnt']) {
				$sel = ' checked ';
			} else {
				$sel = '';
			}
		echo '<input '.$sel.'type="radio" name="cnt" value="ALL"> All, &nbsp;';
		foreach ($countries as $c) {
			//if (in_array($c, $this->config['cnt'])) {
				if ($c == $this->config['cnt']) {
				$sel = ' checked ';
			} else {
				$sel = '';
			}
			//echo $pipe.'<input '.$sel.' type="checkbox" name="cnt[]" value="'.$c.'"> '.$c;
			echo $pipe.'<input '.$sel.' type="radio" name="cnt" value="'.$c.'"> '.$c;
			$pipe = ', &nbsp;';
		}
		echo '</p>';

		echo '<p><b>Sources:</b> ';
		$pipe = '';
		if ('ALL' == $this->config['src']) {
				$sel = ' checked ';
			} else {
				$sel = '';
			}
		echo '<input '.$sel.'type="radio" name="src" value="ALL"> All, &nbsp;';
		foreach ($sources as $s) {
			if ($s == $this->config['src']) {
			//if (in_array($s, $this->config['src'])) {
				$sel = ' checked ';
			} else {
				$sel = '';
			}
			//echo $pipe.'<input '.$sel.'type="checkbox" name="src[]" value="'.$s.'"> '.$s;
			echo $pipe.'<input '.$sel.'type="radio" name="src" value="'.$s.'"> '.$s;
			$pipe = ', &nbsp;';
		}
		echo '</p>';

		echo '<p><b>Job Types:</b> ';
		$pipe = '';
		if ('ALL' == $this->config['typ']) {
				$sel = ' checked ';
			} else {
				$sel = '';
			}
		echo '<input '.$sel.'type="radio" name="typ" value="ALL"> All, &nbsp;';
		foreach ($types as $c) {
			//if (in_array($c, $this->config['cnt'])) {
				if ($c == $this->config['typ']) {
				$sel = ' checked ';
			} else {
				$sel = '';
			}
			//echo $pipe.'<input '.$sel.' type="checkbox" name="cnt[]" value="'.$c.'"> '.$c;
			echo $pipe.'<input '.$sel.' type="radio" name="typ" value="'.$c.'"> '.$c;
			$pipe = ', &nbsp;';
		}
		echo '</p>';


		?>
			</td>
		</tr>

				<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Default Keyword(s)</b></td>
			<td  bgcolor="#e6f2ea"><input size="20" type="text" name='k' value="<?php echo $this->config['k']; ?>"> (By default the terms are AND'ed.)
			</td>
		</tr>
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Default City</b></td>
			<td  bgcolor="#e6f2ea"><input size="20" type="text" name='l' value="<?php echo $this->config['l']; ?>"> (City is optional. e.g. Sydney)
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
		<td colspan="2" bgcolor="#e6f2ea">

			<table width="100%" border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9"  >

			<tr bgcolor="#e6f2ea">

					<h4>Source to Local field mappings</h4>

					<td width="10%" valign="top">
					<b>Local Fields</b> - Fields your job posting form. Fields marked with * are required.
					</td>

					<td valign="top">
					<b>Source Fields</b> - These fields are the original fields that are coming in from the feed
					</td>

				</tr>
			<?php
			require_once ("../include/posts.inc.php");
			$PForm = &JB_get_DynamicFormObject(1, 'global');

			$params = array (
				'pub' => $this->config['id']
			);
			$in_fields = $this->api_call('get_field_list', $params);

			sort($in_fields);

			$sql = "SELECT *, t1.field_label AS FLABEL FROM form_field_translations as t1, form_fields as t2 WHERE t2.form_id=1 AND t2.field_id=t1.field_id AND field_type!='BLANK' AND field_type != 'SEPERATOR' AND lang='".JB_escape_sql($_SESSION['LANG'])."' order by section asc, field_sort asc  ";
			$result = jb_mysql_query($sql);

			

			while ($field = mysql_fetch_array($result, MYSQL_ASSOC)) {
				if ($field['template_tag']=='EMAIL') {
					continue;
				}
				
				?>

				

				<tr bgcolor="e6f2ea">

					<td width="10%" nowrap valign="top">
						<span style="font-weight: bold; font-size: 10pt"><?php echo $field['field_label'];?><?php if ($field['is_required']=='Y') { echo '<span style="color:red; font-size:18pt">*</span>';} ?></span> <?php echo $field['field_type'].' (#'.$field['field_id'];?>)
					</td>

					<td >
						&lt;---<select <?php if ($this->config['map'][$field['field_id']]) { ?> style="color:#008080; font-weight: bold" <?php } ?> style="font-size: 12pt" type="select" name="map[]">
						<option value="" style="color:#008080; font-weight: bold">[Select Field]</option>
						<?php 

						foreach ($in_fields as $in_key => $in_field) {

							if ($this->config['map'][$field['field_id']]== $in_field) {
								$sel = ' selected ';
							} else {
								$sel = '';
							}

							echo '<option style="color:#008080; font-weight: bold" '.$sel.'value="'.$field['field_id'].'='.$in_field.'">'.$in_field.'</option>'."\n";

						}
						
					
						?>
						</select>
					</td>
				</tr>

			<?php
			}
			?>
			</table>


		</td>

		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Ad Code</b></td>
			<td  bgcolor="#e6f2ea">
				<small>Paste in the ad code HTML here, eg. Google Adsense</small>
				<textarea rows="10" style="width:100%" name="ad"><?php echo htmlentities($this->config['ad']);?></textarea>
			</td>
		</tr>
	<!--	
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Channel</b></td>
			<td  bgcolor="#e6f2ea"><input size="15" type="text" name='ch' value="<?php echo $this->config['ch']; ?>"> (Optional. Used to track performance if you have more than one web site. Add a new channel in your Indeed publisher account by going to the XML Feed page)
			</td>
		</tr>
		
	--><!--	<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Sort</b></td>
			<td  bgcolor="#e6f2ea"><input type="radio" name="so" <?php if ($this->config['so']=='date') { echo ' checked '; } ?> value="date"> By Date Posted (default)<br>
			<input type="radio" name="so" <?php if ($this->config['so']=='relevance') { echo ' checked '; } ?> value="relevance"> By Relevance<br>
			<input type="radio" name="so" <?php if ($this->config['so']=='custom') { echo ' checked '; } ?> value="custom"> By relevance + Date Sorted (Jamit does additional sorting so that the relevant results are sorted by date. CPU intensive)
			</td>
		</tr>
	--><!--	<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Site Type</b></td>
			<td  bgcolor="#e6f2ea"><input type="radio" name="st" <?php if ($this->config['st']=='jobsite') { echo ' checked '; } ?> value="jobsite"> Job Site: To show jobs only from job board sites<br>
			<input type="radio" name="st" <?php if ($this->config['st']=='employer') { echo ' checked '; } ?> value="employer">Show jobs only direct from employer sites<br>
			<input type="radio" name="st" <?php if ($this->config['st']=='') { echo ' checked '; } ?> value="">Show from all<br>
			</td>
		</tr>
	--><!--	<tr>
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
	--><!--	<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Radius</b></td>
			<td  bgcolor="#e6f2ea"><input size="3" type="text" name='r' value="<?php echo $this->config['r']; ?>"> Distance from search location ("as the crow flies"). Default is 25.
			</td>
		</tr>
		--><!--<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>From Age</b></td>
			<td  bgcolor="#e6f2ea"><input size="3" type="text" name='age' value="<?php echo $this->config['age']; ?>"> (Number of days back to search. Default/Max is 30)
			</td>
		</tr>
--><!--
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>highlight</b></td>
			<td  bgcolor="#e6f2ea"><input type="radio" name="h" <?php if ($this->config['h']=='1') { echo ' checked '; } ?> value="1"> Yes, highlight keywords<br>
			<input type="radio" name="h" <?php if ($this->config['h']=='0') { echo ' checked '; } ?> value="0"> No)
			</td>
		</tr>
	--><!--
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Filter Results</b></td>
			<td  bgcolor="#e6f2ea"><input type="radio" name="f" <?php if ($this->config['f']=='1') { echo ' checked '; } ?> value="1"> Yes, filter duplicate results<br>
			<input type="radio" name="f" <?php if ($this->config['f']=='0') { echo ' checked '; } ?> value="0"> No
			</td>
		</tr>
	--><!--
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>How to Back-fill?</b></td>
			<td  bgcolor="#e6f2ea">
			<input type="radio" name="fill" <?php if ($this->config['fill']=='S') { echo ' checked '; } ?> value="S"> Stop after filling the first page<br>
			<input type="radio" name="fill" <?php if ($this->config['fill']=='C') { echo ' checked '; } ?> value="C"> Continue to futher pages (if more results are available)
			</td>
		</tr>
-->
		

		
		<tr><td colspan="2">Advanced Settings</td>
		</tr>


	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Use cURL (Y/N)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
       <br>
	   <?php if (!function_exists('curl_init')) { echo ' Note: Your host does not suppor cURL. Options currently disabled <br>'; }  ?>
	  <input type="radio" name="curl" value="N" <?php if (!function_exists('curl_init')) {
		echo ' disabled '; }  ?> <?php if ($this->config['curl']=='N') { echo " checked "; } ?> >No - Normally this option is best<br>
	  <input type="radio" name="curl" value="Y" <?php if (!function_exists('curl_init')) { echo ' disabled '; }  ?> <?php if ($this->config['curl']=='Y') { echo " checked "; } ?> >Yes - If your hosting company blocked fsockopen() and has cURL, then use this option</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">cURL 
      Proxy URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input <?php if (!function_exists('curl_init')) { echo ' disabled '; }  ?> type="text" name="proxy" size="50" value="<?php echo $this->config['proxy']; ?>">Leave blank if your server does not need one. Contact your hosting company if you are not sure about which option to use. For GoDaddy it is: http://proxy.shr.secureserver.net:3128<br></font></td>
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
		<b>Important:</b> After configuring Go here to <a href="p.php?p=JobsFiller&action=kw">Configure Category Keywords</a>
<p>
TROUBLE SHOOTING
<p>
> Keywords do not return any results?
Try your keyword on indeed.com first, before putting them in the job board.
<p>
> Page times out / does not fetch any results?
Your server must be able to make external connections to api.indeed.com
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
Nope.. Indeed rules are that in order to record the click, it must use their 
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

		if (is_array($_REQUEST['cnt'])) {
			$_REQUEST['cnt'] = implode(',', $_REQUEST['cnt']);
		}
		if (is_array($_REQUEST['src'])) {
			$_REQUEST['src'] = implode(',', $_REQUEST['src']);
		}

		// convert map in to a string, eg. '2=jobtitle,8=company,15=location,6=class,5=description'
		
		if (is_array($_REQUEST['map'])) {
			$str=''; $comma='';
			foreach ($_REQUEST['map'] as $key => $val) {
				
				if ($_REQUEST['map'][$key]) {
					$str .= $comma.$val;
					$comma = ',';
				}
			}
			$_REQUEST['map'] = $str;
		}

		$_REQUEST['ad'] = base64_encode(stripslashes($_REQUEST['ad']));


		# JBPLUG_save_config_variables ( string $class_name [, string $field_name [, string $...]] )
		JBPLUG_save_config_variables($this->plugin_name, 'cnt', 'src', 'priority', 'l', 'k',  'id', 'l_tag', 'k_tag', 's', 'curl', 'proxy',  'c', 'map', 'ad', 'typ' );
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

$_JB_PLUGINS['JobsFiller'] = new JobsFiller; // add a new instance of the class to the global plugins array
?>