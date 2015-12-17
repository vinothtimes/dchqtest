<?php

/*

JBSearchFormTLMarkup

Search Form Table Less Markup - A compact, table-less search form
layout, controlled using CSS

To customize: 

- Please see the comments inside the get_css() function

First, please copy this file to your theme directory. Then you can
customize the look of the search form by cutting and pasting the 
CSS from the get_css() function to your main.css file.

*/


class JBSearchFormTLMarkup extends JBSearchFormMarkup {

	var $form_id;

	function JBSearchFormTLMarkup($form_id, $cols) {
		if (is_numeric($form_id)) {
			$this->form_id = $form_id;
		}
		if (is_numeric($cols)) {
			$this->cols = $cols;
		}
	}

	function get_css() {

		/*

		To customize:

		- Please *CUT* and *PASTE* the CSS code to the bottom of your main.css file
		- Please place this file in your custom theme directory

		(This function should return no value after you have cut the css code)

		Here are the CSS classes that can be customized:

		.container 
		- the div containing the search form. 
		
		.container.col1, .container.col2, .container.col3
		
		
		- The search form can have one up to three columns. 
		A custom style can be specified for each variation.
		In the example below, the width is inherited for a 1 column form,
		100% for 2 & 3 column, a 3 column has a different background

		.field_open, field_open.col1, field_open.col2, field_open.col3

		- A floated div containing the field label and field control

		.clear

		- A class to clear the floated div, effectively putting the next div
		on a new row.

		.field_label

		- The field label can be customized in main.css, as other search
		form fields.

				///////////////////////////////////////////////////////////////////////
		Example HTML produced for a single column search form:

		<div id="search_form" class="container col1">
			<div class="field_open col1">
				<span class="field_label">Classification</span>
				<br>
				<select  multiple  size="4"   class="search_input_style" name="6[]">
					<option value="all">Select All</option>
					<option value="45">Áruterít&#337; üzletköt&#337; / Corporation</option> 
					<option value="50">Consulting / Administration</option> 
					<option value="66">&nbsp;&nbsp;&nbsp;&nbsp;|--&nbsp;Course Coordinator</option> 
				</select>	
			</div>
			<br class="clear">
			<div class="field_open col1">
				<span class="field_label">Location</span>
				<br>
				<select  multiple  size="3" class="search_input_style" name="13[]">
					<option value="all">Select All</option>
					<option value="147">Aberdeenshire</option> 
					<option value="135">ACT</option> 
					<option value="133">Adelaide</option> 
				</select>	
			</div>
			<br class="clear">
			<div class="field_open col1">
				<span class="field_label">Description</span>
				<br>
				<input class="search_input_style" name="5" type="text" value="" size="30">	
			</div>
			<br class="clear">
			<div class="find_button">
				<input class="form_submit_button" type="submit" value="Find" name="search" >
			</div>
			<br class="clear">
		</div><br class="clear">
		
		(The above code may differ from the actual code produced - this is
		just for illustrative purposes)
		
		///////////////////////////////////////////////////////////////////////

		Cut from the line below the 'return'. After pasting to main.css, change this
		function to this line:
		
		return '';

		*/

		return  '
		
.container#search_form {

	/*background-color: #EDF8FC;*/
	
}

 .container.col1#search_form {
	width: inherit;
}

 .container.col2#search_form {
	width: 100%;
}

.container.col3#search_form {
	width:100%;
	background-color: #EDF8FC;
}

#search_form .clear {
	clear:both;
}

#search_form .field_open {

	float:left; 
	width: 220px;
	min-width:160px;
	text-align: left;
	margin:5px;

}
#search_form .field_open.col1 {
	width: 100%;
	margin:0px;
}
#search_form .field_label {
	font-weight:bold;
	font-size:10pt;

}

#search_form .find_button {

	float: left; 
	margin:5px;

}

#search_form .new_search {
	float: right; 
}

';


	}

	###########################
	# Container of the search form
 
	function container_open() { // *customized*

		echo '<style>'.$this->get_css().'</style>';
		?>
		
<div id="search_form" class="container col<?php echo $this->cols; ?>">
<?php
	}

	function container_close() { // *customized*
		?>
</div><br class="clear">
<?php

	}

	function row_open() { // *customized*
		return;
		
	}

	function row_close() { // *customized*
		?>
	<br class="clear">
<?php
	}

	function field_label_open($title='') { // *customized*
		return;

	}

	function field_label($f_label) { // *customized*
		return;
		
	}

	function field_label_close() { // *customized*
		return;
		

	}

	# Open the block which will contain the search field
	function field_open() { // *customized*

		?>
	<div class="field_open col<?php echo $this->cols; ?>">
	<span class="field_label"><?php
		echo jb_escape_html($this->field_row['field_label']);?></span>
	<br>
<?php

		return;


	}

	// close what was opened by field_open()
	function field_close() { // *customized*

		?>
	
	</div>
<?php

	}

	function blank_field_open() {
		//nothing here
	}

	function blank_field_close() {
		//nothing here
	}


	###########################
	# The 'Find' button

	function form_button() { // *customized*

		global $label;

		?>

	<div class="find_button">
	<input class="form_submit_button" type="submit" value="<?php echo $label['find_button'];?>" name="search" >
	</div>
	
	<?php if ($_REQUEST['action']=='serach') { ?> 
	<div class="new_search"><span class="new_search_link"><a class="new_search_link" href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=search"><?php echo $label['search_start_new'];?></a>
	</div>
	
<?php }
		?>
	<br class="clear">
	
	
<?php

	}


}

?>