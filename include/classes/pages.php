<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
/*


The following classes make sure to load the appropriate data from the database 
and keep it in an object, depending on the Use Case. (A use case can be for 
example: 'search jobs', 'display post', 'display category', etc.).

A 'Page' in Jamit is an object that represents a Use Case. 
(The 'Use Case' is usually the content between the header
and the footer, and is displayed as a result of an action performed by the user).
Some data may also be used to render the header, such as
title or meta tags.

The main purpose of a 'Page' is to hold the data from the database, 
ready to be displayed by the templates or used by other sub-systems.

When a page is requested, the job board selects which page object to create
and the object will load the required data from the database. This is done
by calling JB_page_init(), the page object is then put on the global scope
for convenience - ready to be used.

Page objects are initialized before any output.

The data is then used by include/themes.php - the data is placed in to
the local scope by the use of the extract() function

The data is always loaded in the page's constructor. Each page object can also
do the following operations:

- Return the data as an array

- Display the data by applying the corresponding template function

- Get the 'canonical url' to the page

Page objects are new to Jamit since v3.6 - not all templates have page
objects. However, we plan to have most pages use page objects in the future.


*/
###############################
# Base Page class

class JBPage {

	var $page_name;
	var $vars;
	

	function JBPage() {
		$this->page_name = "JB_PAGE";
	}

	function is_home_page() {

		return false;

	}

	


	function &get_vars() {
		if (!is_array($this->vars)) {
			return array();
		}
		return $this->vars;
	}


	function get_page_name() {
		return $this->page_name;
	}

	function output() {

		

		if (is_array($this->vars)) {
			foreach ($this->vars as $key => $val) {
				echo $key.'=>'.$val."<br>\n";
			}

		} else {

			echo 'Nothing to output for '.$this->page_name;

		}

	}

	function output_header_tags($title, $desc='', $kw='') {

		global $JBMarkup;

		// here plugins can set their own title, description & keywords
		$my_TITLE = ''; $my_DESCRIPTION = ''; $my_KEYWORDS='';
		JBPLUG_do_callback('index_set_meta_title', $my_TITLE);
		JBPLUG_do_callback('index_set_meta_descr', $my_DESCRIPTION);
		JBPLUG_do_callback('index_set_meta_kwords', $my_KEYWORDS);
		if ($my_TITLE) $title = $my_TITLE; // if overwritten by a plugin
		if ($my_DESCRIPTION) $desc = $my_DESCRIPTION; // if overwritten by a plugin
		if ($my_KEYWORDS) $kw = $my_KEYWORDS; // if overwritten by a plugin

		// output

		$JBMarkup->title_meta_tag($title);

		if ($desc) {
			$JBMarkup->meta_tag('description', $desc);
		}

		if ($kw) {
			$JBMarkup->meta_tag('keywords', $kw);
		}


	}


	function get_canonical_url() {
		return JB_BASE_HTTP_PATH;
	}

	// implement this function to increment the running tally 
	function increment_hits() {

	}



	

	

}


###############################
class JBHomePage extends JBPage {

	var $list_mode;

	function JBHomePage() {

		$this->list_mode = 'ALL';

		require_once (jb_basedirpath().'include/posts.inc.php');
		$this->page_name = "JB_HOME_PAGE";

		// register the header_tags() method so that JBMarkup can call it back
		global $JBMarkup;
		
		$JBMarkup->set_handler('header', $this, 'header_tags');
		
	}

	function is_home_page() {
		return true;
	}

	function header_tags() {

		global $JBMarkup;

		$title = strip_tags(JB_SITE_HEADING);
		$desc = JB_SITE_DESCRIPTION;
		$kw = JB_SITE_KEYWORDS;

		$this->output_header_tags($title, $desc, $kw);


	}

	function output() {

		JB_template_index_home();
	}

	

}

###############################

class JBEmployerPage extends JBPage {

	var $JobListAttributes;

	var $employer_id;
	var $profile_id;

	function JBEmployerPage($employer_id, $admin=false) {

		global $label;

		$this->employer_id = (int) $employer_id;

		$this->page_name = "EMPLOYER_PAGE";

		require (jb_basedirpath().'include/profiles.inc.php');

		
		// load the profile
		
		
		$DynamicForm = &JB_get_DynamicFormObject(3);
		$DynamicForm->load(false, $employer_id); // load profile by $employer_id
		$this->profile_id = $DynamicForm->get_value('profile_id');
		
		// set the company name

		if ($DynamicForm->is_field_restricted('PROFILE_BNAME')) {
			
			$comp_name = str_replace ('%USER_ID%', $this->employer_id, $label['employer_profile_posted_by']);
		} else {
			$comp_name = JB_get_employer_name($this->employer_id);
		}


		$this->JobListAttributes = new JobListAttributes();
		$this->JobListAttributes->clear();

		// make the following variables available for the template
		$this->vars = array (
			'employer_id' => $this->employer_id,
			'profile_id' => $this->profile_id,
			'COMP_NAME' => $comp_name,
			'JobListAttributes' => &$JobListAttributes,
			'DynamicForm' => &$DynamicForm,
			'admin' => $admin
			
		);

		JBPLUG_do_callback('init_employer_page_vars', $this->vars, $this->employer_id);

		// register the header_tags() method so that JBMarkup can call it back
		global $JBMarkup;
		
		$JBMarkup->set_handler('header', $this, 'header_tags');

	}

	function is_home_page() {
		return true;
	}

	function header_tags() {
		global $JBMarkup;

		// set the header tags here
		$COMP_NAME = strip_tags($this->vars['COMP_NAME']);
		$title = $COMP_NAME." | ".JB_SITE_NAME;

		$obj = $this->vars['DynamicForm'];
		$desc = substr(strip_tags($obj->get_template_value('PROFILE_ABOUT')), 0, 255);
		$kw = '';

		$this->output_header_tags($title, $desc, $kw);

		
		$JBMarkup->link_tag('canonical', $this->get_canonical_url());

	}

	function output() {

		JB_template_index_employer();

	}

	function get_canonical_url() {

		return JB_emp_profile_url($this->employer_id, $this->JobListAttributes, JB_BASE_HTTP_PATH.'index.php');

	}

	

}


#############################################

class JBJobPage extends JBPage {

	var $vars;
	var $JobListAttributes;
	var $post_id;

	var $error_code;

	function JBJobPage($post_id, $admin=false) {

		global $JBMarkup;

		$this->post_id = (int) $post_id;
		$this->page_name = "JOB_PAGE";

		require_once (jb_basedirpath().'include/posts.inc.php');

		
		// Init data from the form

		$DynamicForm = &JB_get_DynamicFormObject(1);
		if (!$DynamicForm->load($this->post_id)) {
			$this->error_code = 404;
			//header('Status: 404 Not Found'); // fastcgi
			header("HTTP/1.0 404 Not Found");
		}
		

		// Online Applications enabled?
	
		$APP=false;

		if ((JB_ONLINE_APP_ENABLED=='YES')) {
			$APP = true;
		}

		if ($APP) {
			$JBMarkup->enable_applications();
		}

		// vars used in the template to be extracted in to global scope for the templates

		
		$DATE = $DynamicForm->get_template_value ('DATE', $admin);
		$this->vars = array (
			'post_id' => $this->post_id,
			'TITLE' => $DynamicForm->get_template_value ('TITLE', $admin),
			'POSTED_BY' => $DynamicForm->get_template_value ('POSTED_BY', $admin),
			'POSTED_BY_ID' => $DynamicForm->get_template_value ('USER_ID', $admin),
			'DATE' => $DynamicForm->get_template_value ('DATE', $admin),
			'FORMATTED_DATE' => JB_get_formatted_date($DATE),
			'DESCRIPTION' => $DynamicForm->get_template_value ('DESCRIPTION', $admin),
			'LOCATION' => $DynamicForm->get_template_value ('LOCATION', $admin),
			'APP' => $APP,
			'APPROVED' => $DynamicForm->get_value('approved'),
			'DynamicForm' => &$DynamicForm,
			'admin' => $admin
			
		);
		

		JBPLUG_do_callback('init_job_page_vars', $this, $post_id, $admin);

		// register the header_tags() method so that JBMarkup can call it back

		$JBMarkup->set_handler('header', $this, 'header_tags');

		
	}

	function output($output_mode='FULL') {

		global $label;

		if ($this->error_code==404) {
			echo $label['job_post_404'];
			
		} else {
			if ($output_mode=='FULL') {
				$this->increment_hits();
			}
			JB_template_display_post($output_mode);
		}
	}

	function get_canonical_url () {
		
		return JB_job_post_url($this->post_id, $this->JobListAttributes, JB_BASE_HTTP_PATH.'index.php');

	}

	function increment_hits() {

		// hits counter
	
		$sql = "UPDATE `posts_table` SET `hits`=`hits`+1 WHERE `post_id`='".jb_escape_sql($this->post_id)."' ";
		$result = JB_mysql_query ($sql) or die (mysql_error());

	}

	function header_tags() {

		global $JBMarkup;

		global $label;

		if ($this->error_code==404) {
			return false;
		}

		// init

		$sep = ' | ';
		$title = strip_tags($this->vars['TITLE'].$sep.JB_SITE_NAME);

		$desc = $label['job_post_meta_description'];
		$desc = str_replace('%POSTED_BY%', strip_tags($this->vars['POSTED_BY']), $desc); 
		$desc = str_replace('%LOCATION%', strip_tags($this->vars['LOCATION']), $desc); 
		$desc = str_replace('%DESCRIPTION%', substr(strip_tags($this->vars['DESCRIPTION']), 0, 255), $desc); 
		$desc = str_replace('%DATE%', strip_tags($this->vars['FORMATTED_DATE']), $desc); 

		$this->output_header_tags($title, $desc, $kw);

		// canonical URL is for the search engines
		// see http://googlewebmastercentral.blogspot.com/2009/02/specify-your-canonical.html

		$JBMarkup->link_tag('canonical', $this->get_canonical_url());


	}

}

#############################################
# This is a page which shows a category, and a list of
# jobs which is a result from searching that category.

class JBJobCategoryPage extends JBPage {

	var $list_mode;
	var $category;
	var $cat_id;

	function JBJobCategoryPage($cat_id, $offset, $admin=false) {

		$this->cat_id = (int) $cat_id;

		$this->list_mode = 'BY_CATEGORY';
		$this->page_name = "CATEGORY_PAGE";

		// load the posts in the category

		$this->category = JB_get_category($this->cat_id);


		$this->vars = array(
			'cat_id' => $this->cat_id,
			'CAT_NAME' => JB_getCatName($this->cat_id),
			'CAT_PATH' => JB_getPath_templated($this->cat_id),
			'CAT_STRUCT' => JB_getCatStruct($this->cat_id, $_SESSION["LANG"], 1),
			'category' => &$this->category,
			'admin' => $admin

		);

		JBPLUG_do_callback('init_job_category_page_vars', $this->vars);

		// register the header_tags() method so that JBMarkup can call it back
		global $JBMarkup;
		
		$JBMarkup->set_handler('header', $this, 'header_tags');

	}

	function is_home_page() {
		return true;
	}

	function header_tags() {

		global $label;
		global $JBMarkup;

		// set the title tag for the category (posts) pages

		$sep = ' | ';
			
		if ($this->vars['category']['seo_title']!='') { 
			$title = $this->vars['category']['seo_title']." | ".JB_SITE_NAME; 
		} else { // make it up
			$title = $label['root_category_link']." - ".$this->vars['category']['NAME'].$sep.JB_SITE_NAME; 
		}
		$title = strip_tags($title);

		if ($this->vars['category']['seo_desc']!='') { 
			$desc = $this->vars['category']['seo_desc']; 
		}
		if ($this->vars['category']['seo_desc']!='') { 
			$kw = $this->vars['category']['seo_keys']; 
		}


		$this->output_header_tags($title, $desc, $kw);

		
		$JBMarkup->link_tag('canonical', $this->get_canonical_url());


	}

	function output() {

		JB_template_index_category();

	}

	function get_canonical_url() {

		return JB_cat_url_write($this->cat_id, $this->category['NAME'], $this->category['seo_fname'], JB_BASE_HTTP_PATH.'index.php');

	}

}

#############################################
class JBPremiumJobListPage extends JBPage {

	var $JobListAttributes;

	function JBPremiumJobListPage($offset, $admin=false) {

		$list_mode = 'PREMIUM';

		$this->page_name = "PREMIUM_LIST";

		$this->vars = array(
			
			'admin' => $admin

		);

		// load the posts in the premium list

		JBPLUG_do_callback('init_premium_job_list_page_vars', $this->vars);

		// register the header_tags() method so that JBMarkup can call it back
		global $JBMarkup;
	
		$JBMarkup->set_handler('header', $this, 'header_tags');

	}

	function is_home_page() {
		return true;
	}

	function header_tags() {

		global $label;

		$_REQUEST['offset'] = (int) $_REQUEST['offset'];

		$page = ($_REQUEST['offset'] / JB_PREMIUM_POSTS_PER_PAGE) +1;

		$title = $label['job_post_pr_meta_title'] ;
		$title = str_replace('%PAGE%', $page, $title);
		$title = str_replace('%SITE_NAME%', JB_SITE_NAME, $title);

		$desc = $label['job_post_pr_meta_description'];
		$desc = str_replace('%SITE_NAME%', JB_SITE_NAME, $desc);

		$this->output_header_tags($title, $desc);

	}

	function output() {

		JB_template_index_premium_list();

	}

}
#############################################
# Page for job search result

class JBJobListPage extends JBPage {

	var $list_mode;
	var $JobListAttributes;

	function JBJobListPage($list_mode, $show, $offset, $admin=false) {

		$this->page_name = "JOB_LIST_PAGE";

		$this->vars = array(
			'admin' => $admin
		);

		// load the posts in the premium list

		JBPLUG_do_callback('init_job_list_page_vars', $this->vars);

		// register the header_tags() method so that JBMarkup can call it back
		global $JBMarkup;
	
		$JBMarkup->set_handler('header', $this, 'header_tags');

	}

	function is_home_page() {
		return true;
	}

	
	function header_tags() {

		global $label;

		$_REQUEST['offset'] = (int) $_REQUEST['offset'];

		$page = ($_REQUEST['offset'] / JB_POSTS_PER_PAGE) +1;
		
		$title = $label['nav_page_title'];

		$title = str_replace ('%PAGE%', $page, $title);
		$title = str_replace ('%SITE_NAME%', JB_SITE_NAME, $title);

		$desc = JB_SITE_DESCRIPTION;

		$this->output_header_tags($title, $desc);
		

	}
	

	function output() {

		JB_template_index_search_result();

	}

}
#############################################

class JBJobSearchPage extends JBPage {

	var $list_mode;

	function JBJobSearchPage($show, $offset, $admin=false) {


		$this->page_name = "SEARCH_PAGE";

		$this->vars = array(
			'admin' => $admin
		);

		// load the posts in the premium list

		JBPLUG_do_callback('init_job_search_page_vars', $this->vars);

		// register the header_tags() method so that JBMarkup can call it back
		global $JBMarkup;
	
		$JBMarkup->set_handler('header', $this, 'header_tags');

	}

	function is_home_page() {
		return true;
	}

	function header_tags() {

		global $label;

		$DynamicForm = &JB_get_DynamicFormObject(1);
		$JobSearch = $DynamicForm->get_search_object();

		// go through each of the search fields and build
		// the title from the search terms enetered
		foreach ($JobSearch->get_tag_to_search() as $field) {
			
			if ((($field['field_type']=='TEXT') || ($field['field_type']=='EDITOR')) && $_REQUEST[$field['field_id']]) {
				$keys .= $comma.strip_tags($_REQUEST[$field['field_id']]);
				$comma=', ';
			}	
		}

		if ($keys) {
			$keys .= ' |';
		}

		$page = ($_REQUEST['offset'] / JB_POSTS_PER_PAGE) +1;
		$label['nav_page_title'] = str_replace ('%PAGE%', $page, $label['nav_page_title']);
		$label['nav_page_title'] = str_replace ('%SITE_NAME%', JB_SITE_NAME, $label['nav_page_title']);
		$title = $keys.' '.$label['nav_page_title'];

		$desc = JB_SITE_DESCRIPTION;

		$this->output_header_tags($title, $desc);



	}

	function output() {

		JB_template_index_search_result();
	}

}

#############################################

class JBResumePage extends JBPage {

	var $vars;
	var $resume_id;
	var $DynamicForm;

	function JBResumePage($resume_id, $admin=false) {

		$this->resume_id = (int) $resume_id;

		$this->page_name = "RESUME_PAGE";

		require_once (jb_basedirpath().'include/resumes.inc.php');
		
		$DynamicForm = &JB_get_DynamicFormObject(2);
		$DynamicForm->load($this->resume_id);
		
		
		
		$this->vars = array (
			'resume_id' => $this->resume_id,
			'DynamicForm' => &$DynamicForm,
			'admin' => $admin
		);
		

		JBPLUG_do_callback('init_resume_page_vars', $this, $resume_id);


	}

	

	function increment_hits() {

		$sql = "UPDATE `resumes_table` set hits=hits+1 where `resume_id`='".jb_escape_sql($this->resume_id)."' ";
		JB_mysql_query($sql) or die(mysql_error().$sql);

	}

	function output($admin=false) {
		
		$mode='view';

		$DynamicForm = &JB_get_DynamicFormObject(2);
		$DynamicForm->display_form($mode, $admin);
		

	}

}


#############################################

class JBResumeSearchPage extends JBPage {

	var $list_mode;

	function JBResumeSearchPage($list_mode, $show, $offset, $admin=false) {


		$this->page_name = "SEARCH_PAGE";

		$this->vars = array (
			
			'admin' => $admin
		);

		// load the posts in the premium list

		JBPLUG_do_callback('init_job_search_page_vars', $this->vars);

	}

	function output() {

		JB_template_index_search_result();
	}

}



#############################################
# This is where variables form $_REQUEST are cleaned
# and the page objects are created
# Returns a page object
function JB_page_init($set_obj=false) {

	static $obj;
	if (is_object($set_obj)) $obj = $set_obj;
	if (isset($obj)) return $obj;

	global $SEARCH_PAGE, 
		$EMPLOYER_PAGE, 
		$CATEGORY_PAGE, 
		$PREMIUM_LIST, 
		$JOB_LIST_PAGE, 
		$JB_HOME_PAGE, 
		$JOB_PAGE;

	if ($_REQUEST['post_id']) {
		$JOB_PAGE = true;
	} else {
		// job search result
		$SEARCH_PAGE = ($_REQUEST['action'] =='search');
		//  employer page
		$EMPLOYER_PAGE = ($_REQUEST['show_emp']!='');
		//  jobs by category
		$CATEGORY_PAGE = ($_REQUEST['cat'] != '');
		// premium job list
		$PREMIUM_LIST = (($_REQUEST['p'] != '') && ($_REQUEST['offset']!=''));
		//  job list
		$JOB_LIST_PAGE = (($_REQUEST['offset']!='') && (($CATEGORY_PAGE |$EMPLOYER_PAGE | $SEARCH_PAGE | $PREMIUM_LIST)==false));
		// home page flag
		$JB_HOME_PAGE = (($SEARCH_PAGE | $JOB_LIST_PAGE |  $EMPLOYER_PAGE | $CATEGORY_PAGE | $PREMIUM_LIST) == false);
	}


	if ($_REQUEST['offset'] == '0') unset($_REQUEST['offset']);

	if ($EMPLOYER_PAGE) {
		$employer_id = (int) $_REQUEST['show_emp'];
		$obj = new JBEmployerPage($employer_id);
	} elseif ($CATEGORY_PAGE) {
		$cat_id = (int) $_REQUEST['cat'];
		$offset = (int) $_REQUEST['offset'];
		$obj = new JBJobCategoryPage($cat_id, $offset);
	} elseif ($PREMIUM_LIST) {
		$offset = (int) $_REQUEST['offset'];
		$obj = new JBPremiumJobListPage($offset);
	} elseif ($JOB_LIST_PAGE) {
		$offset = (int) $_REQUEST['offset'];
		$show = jb_alpha($_REQUEST['show']);
		$obj = new JBJobListPage('ALL', $show, $offset);
	} elseif ($JB_HOME_PAGE) {
		$obj = new JBHomePage();
	} elseif ($JOB_PAGE) {
		$post_id = (int) $_REQUEST['post_id'];
		$obj = new JBJobPage($post_id);
	} elseif ($SEARCH_PAGE) {
		$offset = (int) $_REQUEST['offset'];
		$show = jb_alpha($_REQUEST['show']);
		$obj = new JBJobSearchPage($show, $offset);
	}

	JBPLUG_do_callback('page_obj_init', $obj);

	return $obj;



}





?>