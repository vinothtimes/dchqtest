<?php

class JBMenuMarkup extends JBMarkup {

	var $open_on_mouseover;

	function JBMenuMarkup() {
			
	}

	function set_open_on_mouseover($open_on_mouseover=true) {
		$this->open_on_mouseover = $open_on_mouseover;

	}

	function header() {
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo JB_get_menucss_url(); ?>" >
		<script type="text/javascript" src="<?php echo jb_get_menu_js_src();?>ie5.js"></script>
		<script type="text/javascript" src="<?php echo jb_get_menu_js_src();?>XulMenu.js"></script>
		<?php
	}

	function before_body_close($menu_id=1) {

		$show_on_over='';
		if ($this->open_on_mouseover) {
			$show_on_over = '		window.menu'.$menu_id.'.showOnOver = true;'."\n";
		}

		
		/*
		The following code will be placed before the close of the body tag.
		$(document).ready event does not trigger until all assets such as 
		images have been completely received. See http://api.jquery.com/ready/
		$(window).load is used to re-calculate the menu positions after images have
		been loaded. eg:
		$(window).load(function() {
		window.menu1.hideAll();
		window.menu1.init();})

		*/

		?>

<script type="text/javascript">		
	$(document).ready(function() { 
	
		window.menu<?php echo $menu_id;?> = new XulMenu("menu<?php echo $menu_id; ?>"); 
		window.menu<?php echo $menu_id;?>.hideAll();
<?php echo $show_on_over; ?>
		window.menu<?php echo $menu_id;?>.init();
	})
	
	$(window).resize(function() {
		window.menu<?php echo $menu_id;?> = new XulMenu("menu<?php echo $menu_id?>");
		window.menu<?php echo $menu_id;?>.hideAll();
<?php echo $show_on_over; ?>
		window.menu<?php echo $menu_id;?>.init();
	})
</script>
		<?php

	}

	function menu_start($menu_id=1) {
		?>
		<div id="bar<?php echo $menu_id;?>" align="left">
		<?php
	}

	function menu_end() {
		?>
		</div>
		<?php

	}

	function menu_open($menu_id=1) {

		?>
		<table cellspacing="0" cellpadding="0" id="menu<?php echo $menu_id; ?>" class="XulMenu" >
        <tr>
		<?php

	}

	function menu_close() {
		?>
		</tr></table> 
		<?php
	}

	

	function button_open() {
		?>
		<td>
		<?php

	}
	function button_close() {
		?>
		</td>
		<?php

	}

	function button($button) {

		// It would be more optimal to also inclide width and height in the img tag

		?>
		 <a class="button" href="<?php if ($this->open_on_mouseover) { echo $button['link']; } else { echo 'javascript:void(0);';} ?>" <?php echo 'onclick="window.location=\''.$button['link'].'\'; "'; ?>><?php if ($button['image']!='') { echo '<img  alt="'.$button['label'].'" src="'.$button['image'].'" align="middle" border="0">';} ?><?php echo jb_escape_html($button['label']);?></a>
		<?php
	}

	function sub_menu_open() {

		?>
		<div class="section">
		<?php

	}

	function sub_menu_close() {
		
		?>
		</div>
		<?php

	}

	function sub_menu_item($item) {

		// It would be more optimal to also inclide width and height in the img tag

		?>
		<a class="item" href="<?php echo $item['link']; ?>"><?php if ($item['image']!='') { echo '<img alt="'.$item['image'].'" src="'.$item['image'].'" onclick="window.location=\''.$item['link'].'\'" align="middle" border="0">';} ?> <?php echo jb_escape_html($item['label']); ?></a>
	<?php

	}

}