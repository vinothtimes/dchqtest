<?php

class JBTabbedMenuMarkup extends JBMarkup {

	
	function JBTabbedMenuMarkup() {
		
	}

	// code placed here will be outputted between the HEAD tags
	function header() {
		
	}

	// markup placed here will be outputted before the </body> tag
	function before_body_close($menu_id=1) {

	

	}

	
	// when starting the menu, any pre-markup
	function menu_start($menu_id=1) {
		
	}

	function menu_end() {
		

	}

	function menu_open($menu_id=1) {

		?>
		<table cellpadding="0" width="100%" cellspacing="0" border="0" class='tabbed_menu'> <tr> 
		<?php

	}

	function menu_close() {
		?>
		 <td width="100%">&nbsp;</td> </tr> </table>
		<?php
	}

	
	/*

	the menu tab that was clicked or showing for the currently opened page

	*/
	function active_button($button) {

		?>
	<td nowrap class="activeButton" > <div class="active1">  <div class="active2"> <div class="active3"> </div> </div> </div> <div class="activeButtonText"> &nbsp;&nbsp;<?php if ($button['image']!='') { echo '<img src="'.$button['image'].'" align="top" border="0">';} ?> <a  href="<?php echo $button['link']; ?>"><?php  echo jb_escape_html($button['label']);?></a>&nbsp;&nbsp; </div> </td> <td>&nbsp;&nbsp;</td>
		<?php

	}

	function inactive_button($button) {
		?>
	<td nowrap class="inactiveButton" onclick="document.location=&quot;<?php echo jb_escape_html($button['link']); ?>&quot;;"> <div class="inactive1"> <div class="inactive2"> <div class="inactive3"> </div> </div> </div> <div class="inactiveButtonText"> &nbsp;&nbsp;<?php if ($button['image']!='') { echo '<img src="'.jb_escape_html($button['image']).'" align="middle" border="0">';} ?> <A  href="<?php echo jb_escape_html($button['link']); ?>"><?php  echo jb_escape_html($button['label']);?></a>&nbsp;&nbsp; </div> </td>  <td>&nbsp;&nbsp;</td>
	<?php
	}

	function button_open() {
		

	}
	function button_close() {
		

	}

	function button($button) {
		?>
		 <a class="button" href="<?php echo $button['link']; ?>" <?php echo 'onclick="window.location=\''.$button['link'].'\'; "'; ?>><?php if ($button['image']!='') { echo '<img  alt="'.$button['label'].'" src="'.$button['image'].'" align="middle" border="0">';} ?><?php echo jb_escape_html($button['label']);?></a>
		<?php
	}

	/*

	These are the sub-tabs below the main tabs

	*/
	function sub_menu_open() {

		?>
	<div style="padding-bottom: 10px;text-align:left;">                
	<table border="0" style="margin: 0 auto; width:100%;" cellpadding="0" cellspacing="0" id="subTabs"> 
	<tr>
	<td>&nbsp;
		<?php

	}

	function sub_menu_close() {
		
		?>
	</td> <td width="1%" style="padding:0px;" valign="top"> <div class="subtab2"></div> </td> </tr>

	</table>
	</div>

	<?php

	}

	function empty_sub_tab() {
		?>
		<div class="empty_sub_tab"></div>
		<?php
	}

/*
	What to use to separare the sub-tabs?
*/
	function get_item_separator() {
		return ' | ';
	}
/*
	render the sub-tabs
*/
	function sub_menu_item($item, $is_active=false) {
		if ($is_active) {
		?>
		<span class="activeText"><?php if ($item['image']!='') { echo '<img src="'.jb_escape_html($item['image']).'" align="middle" border="0">';} ?> <?php echo jb_escape_html($item['label']); ?></span>

		<?php

		}  else { // inactive
		?>
		<span class="inactiveText"><?php if ($item['image']!='') { echo '<img src="'.jb_escape_html($item['image']).'" align="middle" border="0">';} ?><a href="<?php echo jb_escape_html($item['link'])?>"> <?php echo jb_escape_html($item['label']); ?></a></span>
		<?php

		}

	}

}