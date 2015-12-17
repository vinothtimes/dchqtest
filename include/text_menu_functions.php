<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
function JB_is_button_active($button) {


	$dir = preg_split ('%[/\\\]%', $_SERVER['PHP_SELF']);
	$file = array_pop($dir);
	


	foreach ($button['sub'] as $i) {

		
			if ($i['link'] == $file) {
				
				return true;
			}
		

	}

	if ($button['button']['link'] == $file) {
	
		return true;

	}

	return false;




}

///////////////////////////////

function JB_show_active_button($button) {

	?>

	<td nowrap class="activeButton" > <div class="active1">  <div class="active2"> <div class="active3"> </div> </div> </div> <div class="activeButtonText"> &nbsp;&nbsp;<?php if ($button['image']!='') { echo '<img src="'.$button['image'].'" align="top" border="0">';} ?> <?php  echo $button['label'];?>&nbsp;&nbsp; </div> </td>      <td>&nbsp;&nbsp;</td>


	<?php



}
/////////////////
function JB_show_menu($menu) {
	$active_button = JB_show_menu_buttons($menu);
	return $active_button;

}
/////////////////
function JB_show_inactive_button($button) {

	?>

	<td nowrap class="inactiveButton" onclick="document.location=&quot;<?php echo $button['link']; ?>&quot;;"> <div class="inactive1"> <div class="inactive2"> <div class="inactive3"> </div> </div> </div> <div class="inactiveButtonText"> &nbsp;&nbsp;<?php if ($button['image']!='') { echo '<img src="'.$button['image'].'" align="middle" border="0">';} ?> <a href="<?php echo $button['link']; ?>"><?php  echo $button['label'];?></a>&nbsp;&nbsp; </div> </td>  <td>&nbsp;&nbsp;</td>


	<?php

}
////////////////////////
  function JB_show_menu_buttons(&$menu) {

	  ?>

	  <table cellpadding="0" width="100%" cellspacing="0" border="0"> <tr> 
	  
	  <?php

		  foreach ($menu as $button) {
			  $active = JB_is_button_active($button);
			  if ($active) {
				$active_button = $button;
				JB_show_active_button($button['button']);
			  } else {
				  JB_show_inactive_button($button['button']);
			  }
		  }


	  ?>
	
	  <td width="100%">&nbsp;</td> </tr> </table> 


	  <?php

		 return $active_button; // return the active menu


  }
///////////////////////////
function JB_show_sub_menu($button) {

	

	?>

	<div style="padding-bottom: 10px;">                
	<table style="margin: 0 auto; width:100%; border:0px; " cellpadding="0" cellspacing="0" id="subTabs"> 
	<tr>
	<td>&nbsp;        
	<?php
	if (sizeof($button['sub'])!=0) {
		foreach ($button['sub'] as $item) {
			if (JB_is_menu_item_visible($item)) { 
				echo $nbsp_pipe; 
			}
			JB_show_sub_menu_item($item);
			$nbsp_pipe = ' | ';
		}

	}
	?>
	 </td> <td width="1%" style="padding:0px;" valign="top"> <div class="subtab2"></div> </td> </tr>

</table>
</div>

	<?php


}
#################################

function JB_is_menu_item_visible($item) {

	if (JB_test_menu_item_condition($item)) {

		if ($item['label']!='') {

			return true;

		}

	}
	
	return false;


}

#######################################################
# returns true if all conditions are met.

function JB_test_menu_item_condition($item) {

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
function JB_show_sub_menu_item($item) {

	
	if (!JB_test_menu_item_condition($item)) return false;


	$dir = preg_split ('%[/\\\]%', $_SERVER['PHP_SELF']);
	$file = array_pop($dir);

	if ($_REQUEST['type']=='premium') {
		$file = "post.php?type=premium";

	}
	
	if ($file == $item['link']) { // active
	?>
		<span class="activeText"><?php if ($item['image']!='') { echo '<img src="'.$item['image'].'" align="middle" border="0">';} ?> <?php echo $item['label']; ?></span>

	<?php

	} else { // inactive
	?>
		<span class="inactiveText"><?php if ($item['image']!='') { echo '<img src="'.$item['image'].'" align="middle" border="0">';} ?><a href="<?php echo $item['link']?>"> <?php echo $item['label']; ?></a></span>
	<?php

	}

	?>
	
	<?php

	return true;

}

?>