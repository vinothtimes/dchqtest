<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
#############################################
# The following class keeps the attributes for the job list
# Including the GET query string
# $q_string = The entire query string appended to the url
# $q_offset = The offest part for the page
# $show_emp = Employer id to show, eg show_emp=6
# $cat = The category Id, eg cat=3
# $order_str = Other string. order_by=3 ord=ASC
# $prefix = prefix changes if mod_rewrrite is enabled ie. ? or &
# $is_internal_page - this is a page in the myjobs/ or employers/ dir
# $list_mode - EMPLOYRER, PREMIUM, ALL etc


class JobListAttributes {

	//var $show_emp, $cat, $order_str, $show_str;
	var $query_string;
	var $nav_query_string;
	var $is_internal_page;
	
	var $list_mode;

	var $params;
	
	
	function JobListAttributes($list_mode='ALL', $show='') {

		$this->set_list_mode($list_mode);

		$this->params = array();

		$q_string = JB_generate_q_string(1);

		// Split up the $q_string in to key/val pairs and place in to $this->params
		if ($q_string) {
			$parts = explode ('&amp;', $q_string);
			
			if (is_array($parts) && (sizeof(is_array($parts))>0) ) {
				$this->params['action'] = 'search';
				foreach ($parts as $pair_str) {
					if ($pair_str) {
						$pair = explode ('=', $pair_str);
						if (strpos($pair[0], '3%5B%5D')===(strlen($pair[0])-7)) { // does it end with square brackets? [] is 3%5B%5D
							// its an array
							$key = substr($pair[0], 0, strlen($pair[0])-6); // remove the square brackets
							$this->params[$key][] = $pair[1];
						} else {
							$this->params[$pair[0]] = $pair[1];
						}		
					}
				}
			}
		}

		if (isset($_REQUEST['post_permalink'])) {
			// came from a permalink
			// so remove redundant variables from the $q_string
			// which are already encoded in the permalink

			global $post_tag_to_field_id;
			$parts = explode('/', JB_MOD_REWRITE_JOB_DIR);

			foreach ($parts as $part) {

				if (strpos($part, '%', 0)!==false) {
					$template_tag = substr($part, 1, strlen($part)-2);
					$key = $post_tag_to_field_id[$template_tag]['field_id'];
		
					unset($this->params['action']); 
					unset($this->params[$key]);

				}
			}

		}

		

		if ($_REQUEST['offset']!='') {
			$_REQUEST['offset'] = (int) $_REQUEST['offset'];
			$this->params['offset'] = $_REQUEST['offset'];
		}


		if ($_REQUEST['show_emp'] != '') {
			$_REQUEST['show_emp'] = (int) $_REQUEST['show_emp'];
			$this->params['show_emp'] = $_REQUEST['show_emp'];
		}

		
		if ($_REQUEST['cat'] != '') {
			$_REQUEST['cat'] = (int) $_REQUEST['cat'];
			$this->params['cat'] = $_REQUEST['cat'];
		}

		
		if ($_REQUEST['order_by']!='') {
			$ord = jb_alpha_numeric($_REQUEST['ord']);
			$_REQUEST['order_by'] = jb_alpha_numeric($_REQUEST['order_by']);
			$this->params['order_by'] = $_REQUEST['order_by'];
			$this->params['ord'] = $ord;
		
		}

		
		if ($_REQUEST['show'] != '') {
			$_REQUEST['show'] = preg_match('#[a-z]+#i', $_REQUEST['show'], $m);
			$_REQUEST['show'] = $m[0];
			$this->params['show'] = $_REQUEST['show'];
		}
	

		$this->internal_page = (

			(strpos($_SERVER['PHP_SELF'], JB_CANDIDATE_FOLDER)!==false)	||
			(strpos($_SERVER['PHP_SELF'], JB_EMPLOYER_FOLDER)!==false)	||
			(strpos($_SERVER['PHP_SELF'], 'posts.php')!==false)
		);

		// eg. 1 http://loaclhost/index.php?post_id=3&search=1 (prefix is &)
		// eg. 2 http://loaclhost/job/3?search=1 (prefix is ?)

		if ($this->list_mode=='PREMIUM') {
			$this->params['p']='1';	
		}
	

	}

	function get_query_string($concat_prefix) {

		$prefix = $concat_prefix;
		foreach ($this->params as $key => $val) {
			if (is_array($val)) {
				//$q_str .= $prefix.$key.'='.$val;
				foreach ($val as $item) {
					$q_str .= $prefix.$key.'[]='.$item;
				}
			} else {
				$q_str .= $prefix.$key.'='.$val;
			}
			$prefix = '&amp;';
		}
		return $q_str;

	}

	/*

	Get the query string for the Previous/next/ page
	to be appened to the end of the href URL
	(displayed by url_writing_functions.php)

	*/

	function get_nav_query_string($concat_prefix='') {
		$prefix = $concat_prefix;

		foreach ($this->params as $key => $val) {
			if ($key=='offset') continue;
			if (is_array($val)) {
				//$q_str .= $prefix.$key.'='.$val;
				foreach ($val as $item) {
					$q_str .= $prefix.$key.'[]='.$item;
				}
			} else {
				$q_str .= $prefix.$key.'='.$val;
			}
			$prefix = '&amp;';
		}
		return $q_str;

	}
   /*
	 Get the guery string that is to be appended to the action url of a FORM
	 eg. 
     <form name="form1" method="POST" 
	 action="<?php echo htmlentities($_SERVER['PHP_SELF']). 
	 $JobListAttributes->get_form_query_string(); ?">
	*/
	function get_form_query_string() {

		$prefix = '?';
		foreach ($this->params as $key => $val) {
			if (is_array($val)) {
				foreach ($val as $item) {
					$q_str .= $prefix.$key.'[]='.$item;
				}
			} else {
				$q_str .= $prefix.$key.'='.$val;
			}
			$prefix = '&amp;';
		}

		return $q_str;

	}

	/*

	Internal page - meaning a page inside employers/ or myjobs/ directory
	(We do not use mod_rewrite for links on these pages)
	*/

	function is_internal_page() {
		return $this->internal_page;
	}

	/*
	List mode can be ALL, PREMIUM, BY_CATEGORY, EMPLOYER, ADMIN
	*/
	function set_list_mode($lm) {
		$this->list_mode = $lm;
		
		
	}

	

	function clear() {

		$this->list_mode = '';
		
		$this->params = array();

	}


}
?>