<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

function JB_tabbed_is_menu_link_path_eq_self($link) {

	$path = preg_replace( '#http://[^/]+(.+)#', '$1', $link); // get rid of the http://domain part
	$q_string = '';
	if ($_SERVER['QUERY_STRING']!='') {
		$q_string = '?'.$_SERVER['QUERY_STRING'];
	}
	$self = str_replace ('index.php', '', $_SERVER['PHP_SELF'].$q_string);
	$self = str_replace ('default.php', '', $self);

	if ($path === $self) {
	
		return true;
	}


}

function JB_tabbed_is_button_active($button) {


	//$dir = preg_split ('%[/\\\]%', $_SERVER['PHP_SELF']);
	//$file = array_pop($dir);
	if (isset($_REQUEST['file']) && $_REQUEST['file']) {
		$file = $_REQUEST['file'];
	} else {
		$file = basename($_SERVER['PHP_SELF']);
	}
	
	foreach ($button['sub'] as $i) {
		
		if ($i['link'] == $file) {	
			return true;
		} elseif ($i['file_name'] == $file) {	// info pages
			return true;
		} 
	}

	if ($button['button']['link'] == $file) {
		return true;
	} elseif ($button['button']['file_name'] == $file) {
		return true;
	}
	return false;

}




///////////////////////////////
/*

Show the active tab

*/
function JB_tabbed_show_active_button($button) {

	$MM = &get_JBMenuMarkup_object('TABBED');
	$MM->active_button($button);


}
/////////////////
function JB_tabbed_show_menu($menu) {
	static $menu_count;
	$MM = &get_JBMenuMarkup_object('TABBED');
	$MM->menu_start($menu_count);
	$active_button = JB_tabbed_show_menu_buttons($menu);
	$MM->menu_end();
	if (!$menu_count) $menu_count=1;
	$menu_count++;

	return $active_button;

}
/////////////////
function JB_tabbed_show_inactive_button($button) {

	$MM = &get_JBMenuMarkup_object('TABBED');
	$MM->inactive_button($button);


}
////////////////////////
function JB_tabbed_show_menu_buttons($menu) {
	static $menu_count;
	$menu_count = (!$menu_count) ? 1 : ++$menu_count;

	$MM = &get_JBMenuMarkup_object('TABBED');
	$MM->menu_open($menu_count);

	foreach ($menu as $button) {

		if (!isset($active_button)) {
			$active_button = $button; // the first button is active by default
		}

		$active = JB_tabbed_is_button_active($button);
		$MM->button_open();
		if ($active) {
			$active_button = $button;
			JB_tabbed_show_active_button($button['button']);
		} else {
			JB_tabbed_show_inactive_button($button['button']);
		}
		$MM->button_close();
	}


	$MM->menu_close();

	return $active_button; // return the active menu


  }
///////////////////////////
function JB_tabbed_show_sub_menu($button) {
	$MM = &get_JBMenuMarkup_object('TABBED');

	if (sizeof($button['sub'])==0) {
		$MM->empty_sub_tab();
		return false;
	}
	$MM->sub_menu_open();
	if (sizeof($button['sub'])!=0) {
		foreach ($button['sub'] as $item) {
			if (JB_tabbed_is_menu_item_visible($item)) { 
				echo $nbsp_pipe; 
			}
			JB_tabbed_show_sub_menu_item($item);
			$nbsp_pipe = $MM->get_item_separator();
		}

	}
	$MM->sub_menu_close();
	


}
#################################

function JB_tabbed_is_menu_item_visible($item) {

	if (JB_tabbed_test_menu_item_condition($item)) {

		if ($item['label']!='') {
			return true;
		}
	}
	
	return false;


}

#######################################################
# returns true if all conditions are met.

function JB_tabbed_test_menu_item_condition($item) {

	// get the condition form the constant defined in config.php
	// for security, check to make the value contains are defined 

	if (($item['condition1']!='') && (defined($item['condition1']))) {
		eval ("\$def1 = ".$item['condition1'].";");
		
	}
	
	if (($item['condition2']!='') && (defined($item['condition2']))) { 
		eval ("\$def2 = ".$item['condition2'].";");
	}

	if ($item['cond']=='OR' ) {
		if (($def1=='YES') || ($def2=='YES')) {  } else {return false;}

	}

	if ($item['cond']=='AND' ) {
		if (($def1=='YES') && ($def2=='YES')) {  } else {return false;}

	}

	return true;


}

///////////////////////////////
function JB_tabbed_show_sub_menu_item($item) {

	$MM = &get_JBMenuMarkup_object('TABBED');

	
	if (!JB_tabbed_test_menu_item_condition($item)) return false;
	
	if (isset($_REQUEST['file']) && $_REQUEST['file']) {
		$file = $_REQUEST['file'];
	} else {
		$file = basename($_SERVER['PHP_SELF']);
	}
	
	if ($_REQUEST['type']=='premium') {
		// special case when posting a premium post
		$file = "post.php?type=premium";
	}

	if ($_REQUEST['post_id']!='') {
		$_REQUEST['post_id'] = (int) $_REQUEST['post_id'];
		$file = 'post.php?post_id='.$_REQUEST['post_id'];
	}
	
	// These function can be used to display
	// from a plugin (InfoPages) or from employers/ & candidates/
	// - when display from a plugin, a $_REQUEST['file'] parameter would be available



	if (($item['link']==$file) || ($item['file_name']==$file)) {
		$MM->sub_menu_item($item, true);
	}  else { // inactive
		$MM->sub_menu_item($item, false);

	}



	return true;

}

?>