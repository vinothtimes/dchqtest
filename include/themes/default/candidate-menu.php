<?php
# Copyright 2005-2009 Jamit Software
# http://www.jamit.com/

/// menu structure
// This structure is used to generate the menus.
// if you want to edit the menu, edit it here!!


/*
IMPORTANT: If you customize this file in your own theme, please make sure to
also copy the following files:
- text_menu.php
- js_menu.php


*/


global $jb_menu;
$jb_menu=array();

$sub_menu1 = array (
  0 => 
  array (
    'label' => $label["c_menu_main"],
    'link' => 'index.php',
    'image' => '',
    
  ),
  1 => 
  array (
    'label' => $label["c_menu_membership"],
    'link' => 'membership.php',
    'image' => '',
	'condition1' => 'JB_CANDIDATE_MEMBERSHIP_ENABLED',
	'cond' => 'OR',
  ),
  2 => 
  array (
    'label' => $label["c_menu_pwchange"],
    'link' => 'password.php',
    'image' => '',
  ),
  3 => 
  array (
    'label' => $label["c_menu_ac_details"],
    'link' => 'account.php',
    'image' => '',
  ),
  4 => 
  array (
    'label' => $label["c_menu_select_lang"],
    'link' => 'language.php',
    'image' => '',
	'condition1' => 'JB_CAN_LANG_ENABLED',
	'cond' => 'OR',
  ),
  5 => 
  array (
    'label' => $label["c_menu_logout"],
    'link' => 'logout.php',
    'image' => '',
  ));
 

$sub_menu2 = array (
  0 => 
  array (
    'label' => $label["c_menu_view"],
    'link' => 'resume.php',
    'image' => '',
    
  ),
  1 => 
  array (
    'label' => $label["c_menu_edit"],
    'link' => 'edit.php',
    'image' => '',
  ));

 
 

 $sub_menu4 = array (
  0 => 
  array (
    'label' => $label["c_menu_search"],
    'link' => 'search.php',
    'image' => '',
    
  ),
  1 => 
  array (
    'label' => $label["c_menu_category"],
    'link' => 'browse.php',
    'image' => '',
	
  ),
  array (
    'label' => $label["c_menu_saved"],
    'link' => 'save.php',
    'image' => '',
	'condition1' => 'JB_SAVE_JOB_ENABLED',
	'cond' => 'OR',
	
  ),
  array (
    'label' => $label["c_menu_apps"],
    'link' => 'apps.php',
    'image' => '',
	'condition1' => 'JB_ONLINE_APP_ENABLED',
	'cond' => 'OR',
	
  ),
  array (
    'label' => $label["c_menu_alerts"],
    'link' => 'alerts.php',
    'image' => '',
	'condition1' => 'JB_JOB_ALERTS_ENABLED',
	'cond' => 'OR',
	
  ));
$label["c_menu_about"] = str_replace ("%SITE_NAME%", JB_SITE_NAME , $label["c_menu_about"]);
  $sub_menu5 = array (
  0 => 
  array (
    'label' => $label["c_menu_contents"],
    'link' => 'help.php',
    'image' => '',
    
  ),
  array (
    'label' => $label["c_menu_about"],
    'link' => 'about.php',
    'image' => '',
    
  ));

  

/// Menu Buttons
$jb_menu[0]['button']= array (
	'label' => $label["c_menu_account"],
    'link' => 'index.php',
    'image' => ''
);
$jb_menu[0]['sub']=$sub_menu1;

$jb_menu[1]['button']= array (
	'label' => $label["c_menu_resume"],
    'link' => 'resume.php',
    'image' => ''
);
$jb_menu[1]['sub']=$sub_menu2;



$jb_menu[3]['button']= array (
	'label' => $label["c_menu_jobs"],
    'link' => 'search.php',
    'image' => ''
);

// postit-small.gif
$jb_menu[3]['sub']=$sub_menu4;

$jb_menu[4]['button']= array (
	'label' => $label["c_menu_help"],
    'link' => 'help.php',
    'image' => ''
);
$jb_menu[4]['sub']=$sub_menu5;

JBPLUG_do_callback('candidate_menu_init', $jb_menu);


if (!defined('JB_CANDIDATE_MENU_TYPE')) {
   define ('JB_CANDIDATE_MENU_TYPE', 'JS');
}

if (JB_CANDIDATE_MENU_TYPE=='JS') {
	require(JB_basedirpath().JB_CANDIDATE_FOLDER."js_menu.php");
} elseif (JB_CANDIDATE_MENU_TYPE=='TXT') {
	require(JB_basedirpath().JB_CANDIDATE_FOLDER."/text_menu.php");
} else {
	require(JB_basedirpath().JB_CANDIDATE_FOLDER."/js_menu.php");
}


?>