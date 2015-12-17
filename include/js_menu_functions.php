<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

///////////////////////////////
/*
function JB_show_active_button($button) {

	$MM = &get_JBMenuMarkup_object();
	$MM->active_button($button);

}
*/
/////////////////


function JB_show_menu(&$menu) {

	static $menu_count;
	$MM = &get_JBMenuMarkup_object();
	$MM->menu_start($menu_count);
	$active_button = JB_show_menu_buttons($menu);
	$MM->menu_end();
	if (!$menu_count) $menu_count=1;
	$menu_count++;

}

////////////////////////
  function JB_show_menu_buttons(&$menu) {

	static $menu_count;
	$menu_count = (!$menu_count) ? 1 : ++$menu_count;

	$MM = &get_JBMenuMarkup_object();
	$MM->menu_open($menu_count);

	foreach ($menu as $button) {

		$MM->button_open();
		$MM->button($button['button']);
		JB_show_sub_menu($button);
		$MM->button_close();
	}

	$MM->menu_close();

  }
///////////////////////////
function JB_show_sub_menu($button) {

	$MM = &get_JBMenuMarkup_object();

	if ($button['sub']==false) {
		return;
	}

	$MM->sub_menu_open();

	foreach ($button['sub'] as $item) {
		if (($item['label']!='') ) {
			JB_show_sub_menu_item($item);
		}
	}


	$MM->sub_menu_close();


}
///////////////////////////////
function JB_show_sub_menu_item($item) {

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

	$MM = &get_JBMenuMarkup_object();
	$MM->sub_menu_item($item);

	return true;

}

?>