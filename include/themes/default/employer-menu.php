<?php # copyright Jamit Software 2009, www.jamit.com

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
    'label' => $label["employer_menu_main_page"],
    'link' => 'index.php',
    'image' => '',
    
  ),
  1 => 
  array (
    'label' => $label["employer_menu_membership"],
    'link' => 'membership.php',
    'image' => '',
	'condition1' => 'JB_EMPLOYER_MEMBERSHIP_ENABLED',
	'cond' => 'OR',
  ),
  2 => 
  array (
    'label' => $label["employer_menu_ac_details"],
    'link' => 'account.php',
    'image' => '',
    
  ),
  3 => 
  array (
    'label' => $label["employer_menu_change_pw"],
    'link' => 'password.php',
    'image' => '',
  ),
  4 => 
  array (
    'label' => $label["employer_menu_select_lang"],
    'link' => 'language.php',
    'image' => '',
	'condition1' => 'JB_EMP_LANG_ENABLED',
	'cond' => 'OR',
  ),
  5 => 
  array (
    'label' => $label["employer_menu_logout"],
    'link' => 'logout.php',
    'image' => '',
  ));
 

$sub_menu2 = array (
  0 => 
  array (
    'label' => $label["employer_menu_view_profile"],
    'link' => 'profile.php',
    'image' => '',
    
  ),
  1 => 
  array (
    'label' => $label["employer_menu_edit_profile"],
    'link' => 'edit.php',
    'image' => '',
  ));

 
 $sub_menu3 = array (
  0 => 
  array (
    'label' => $label["employer_menu_browse_resumes"],
    'link' => 'search.php',
    'image' => '',
    
  ),
   4 => 
  array (
    'label' => $label["employer_menu_saved_resumes"],
    'link' => 'saved.php',
    'image' => '',
    
  ),
  1 => 
  array (
    'label' => $label["employer_menu_resume_alerts"],
    'link' => 'alerts.php',
    'image' => '',
	'condition1' => 'JB_RESUME_ALERTS_ENABLED',
	'cond' => 'OR',
  ),
  2 =>
  array (
    'label' => $label["employer_menu_subscr"],
    'link' => 'subscriptions.php',
    'image' => '',
	'condition1' => 'JB_SUBSCRIPTION_FEE_ENABLED',
	'cond' => 'OR',
  ),
  3 => 
  array (
    'label' => '',
    'link' => 'request.php',
    'image' => '',
	'condition1' => '',
	'cond' => 'OR',
  ));

$sub_menu4 = array (
0 =>
array (
    'label' => $label["employer_menu_job_post_manager"],
    'link' => 'manager.php',
    'image' => JB_THEME_URL.'images/manager.gif',
	
  ),
  1 => 
  array (
    'label' => $label["employer_menu_post_a_new_job"],
    'link' => 'post.php',
    'image' => JB_THEME_URL.'images/postit.gif',
    
  ),
  2 => 
  array (
    'label' => $label["employer_menu_prm_post"],
    'link' => 'post.php?type=premium',
    'image' => JB_THEME_URL.'images/premiumpostit.gif',
	'condition1' => 'JB_PREMIUM_POSTING_FEE_ENABLED',
	'cond' => 'OR',
  ),
  3 =>
  array (
    'label' => $label["employer_menu_credits"],
    'link' => 'credits.php',
    'image' => JB_THEME_URL.'images/coins.gif',
	'condition1' => 'JB_POSTING_FEE_ENABLED',
	'condition2' => 'JB_PREMIUM_POSTING_FEE_ENABLED',
	'cond' => 'OR',
  ));

 
  $sub_menu5 = array (
  0 => 
  array (
    'label' => $label["employer_menu_app_man"],
    'link' => 'apps.php',
    'image' => '',
    
  ));

   $sub_menu6 = array (
  0 => 
  array (
    'label' => $label["employer_menu_contents_and_index"],
    'link' => 'help.php',
    'image' => '',
    
  ));

  
   

/// Menu Buttons
// Need to include the JB_THEME_URL.'images/vf1x20.gif pic to keep buttons aligned..
$jb_menu[0]['button']= array (
	'label' => $label["employer_menu_account"],
    'link' => 'index.php',
    'image' => JB_THEME_URL.'images/vf1x20.gif'
);
$jb_menu[0]['sub']=$sub_menu1;

$jb_menu[1]['button']= array (
	'label' => $label["employer_menu_profile"],
    'link' => 'profile.php',
    'image' => JB_THEME_URL.'images/vf1x20.gif'
);
$jb_menu[1]['sub']=$sub_menu2;

$jb_menu[2]['button']= array (
	'label' => $label["employer_menu_resumes"], 
    'link' => 'search.php',
    'image' => JB_THEME_URL.'images/vf1x20.gif'
);
$jb_menu[2]['sub']=$sub_menu3;

$jb_menu[3]['button']= array (
	'label' => $label["employer_menu_posts"],
    'link' => 'manager.php',
    'image' => JB_THEME_URL.'images/postit-small.gif'
);

// postit-small.gif
$jb_menu[3]['sub']=$sub_menu4;

$jb_menu[4]['button']= array (
	'label' => $label["employer_menu_app_man"],
    'link' => 'apps.php',
    'image' => JB_THEME_URL.'images/vf1x20.gif'
);
$jb_menu[4]['sub']=$sub_menu5;

$jb_menu[5]['button']= array (
	'label' => $label["employer_menu_help"],
    'link' => 'help.php',
    'image' => JB_THEME_URL.'images/vf1x20.gif'
);
$jb_menu[5]['sub']=$sub_menu6;

JBPLUG_do_callback('employer_menu_init', $jb_menu);


if (!defined('JB_EMPLOYER_MENU_TYPE')) {
   define ('JB_EMPLOYER_MENU_TYPE', 'JS');
}

if (JB_EMPLOYER_MENU_TYPE=='JS') {
	require(JB_basedirpath().JB_EMPLOYER_FOLDER."js_menu.php");
} elseif (JB_EMPLOYER_MENU_TYPE=='TXT') {
	require(JB_basedirpath().JB_EMPLOYER_FOLDER."/text_menu.php");
} else {
	require(JB_basedirpath().JB_EMPLOYER_FOLDER."/js_menu.php");
}
?>