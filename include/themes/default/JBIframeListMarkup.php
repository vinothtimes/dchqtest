<?php

/*

Markup  for displaying list for list_iframe.php

*/ 

class JBIframeListMarkup extends JBListMarkup {

	var $colspan;

	function JBIframeListMarkup($form_id=6) {

		parent::JBListMarkup($form_id);
	}
	

	# Start the list and open the TABLE
	function list_start() {

		?>
		<table border="0" class="list_iframe" width="100%">
		<?php

	}

	

	

	function list_head_cell_open() {
		?><td>
		<?php

	}

	function list_head_cell_close() {
		?></td>
		<?php
	}

	function list_head_column($heading) {
		$this->list_head_cell_open(); // <td>
		echo '<font face="arial" size="1" COLOR=""><b>'.$heading.'</b></font>'; 
		$this->list_head_cell_close(); // </td>
		
	}

	function list_item_open() {
		?>
		<tr class="list_data_row" id="item_<?php echo $this->get_data_value('post_id');?>">
		<?php

	}

	

	function list_cell_open() {
		?>
		
		<td nowrap valign="top" class="list_data_cell" >
		<?php
	}

	

	function get_link($link, $text, $new, $linkcolor) {

		if ($new == 'yes') { 
			$t='target="_blank"';  
		} else { 
			$t='target="_parent"'; 
		} 
		
		
		return '<A href="'.$link.'" '.$t.'  style="color:'.$linkcolor.'; font-family:arial; font-size:10pt">'.$text.'</A>';
		
	}

	function list_cell_data($data) {
		?>
		<span style="font-family:arial; font-size:10pt"><?php echo $data; ?></span>
		<?php

	}

	



}

?>