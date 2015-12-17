<?php

/*

A template class for rendering the categories.

These classes print out the HTML used by the follwoing functions

JB_display_categories(&$cats) 

JB_display_sub_cats_table(&$cats, $index)

JB_display_sub_cats_compact(&$cats, $index)



*/
class JBCategoryMarkup extends JBMarkup {

	function JBCategoryMarkup() {

	}


	#####################################
	# The following methods are used by
	# the JB_display_categories() function
	# to produce the main category layout
	# An importnat thing to remember is that
	# The structure can have any amount of columns
	# depending on the $COLS argument,
	# that can be confugured in Admin.
	# Eg.
	# It can be like this if $COLS=1
	# <table>
	#	<tr><td>
	#		<td>category 1</td>
	#		<td>category 2</td>
	#		<td>category 3</td>
	#		<td>category 4</td>
	#		<td>category 5</td>
	#		<td>category 6</td>
	#	</td></tr>
	# </table>
	#
	# or if $COLS=2:
	#
	# <table>
	#	<tr><td>
	#		<td>category 1</td><td>category 2</td>
	#		<td>category 3</td><td>category 4</td>
	#		<td>category 5</td><td>category 6</td>
	#	</td></tr>
	# </table>
	#
	#
	#
	#####################################

	function set_cols($COLS) {  # You can $COLS / adjust the cols here

		if ($COLS==false) {
			$COLS=2;
		}
		return $COLS;
	}


	// Opening html to start the categories box
	// closed by echo_close_categories()
	function echo_open_categories() {

		?>
		<table class="cat_table" border="0" cellpadding="5" cellspacing="0"   >
		<tr>

		<?php
	}

	// opening cell for each category listed
	// closed by echo_close_categories_cell()
	function echo_open_categories_cell($width) { 

		?>
		<td valign="top" width="<?php echo $width; ?>%">
		<?php

	}

	// Render a link to the category, using class cat_parent_link
	function echo_parent_link(&$cat) {

		/*

		$cat['n'] = name of the catefory
		$cat['n'] = ID of the category
		$cat['seo'] = SEO URL of the catgeory (if exists a SEO optimized link will be rendered)

		*/

		?>
		<span class="cat_arrow">
			<a class="cat_parent_link" HREF="<?php echo JB_cat_url_write($cat['cid'], $cat['n'], $cat['seo']); ?>"><?php
			echo $cat['n']; // Echo the name of the category
		?></a></span>
		<?php

	}

	function echo_count($count) {
		?>
		<small class='cat_small_count'>(<?php echo $count; ?>)</small>
		<?php

	}

	// Before showing the sub-categories
	function echo_before_subcat_line_break() {
		?>
		<br>
		<?php
	}

	function echo_close_categories_cell() {
		?>
		</td>
		<?php

	}

	function echo_close_categories() {

		?>
		</tr>
		</table>
		<?php

	}


	#####################################
	# The following methods are used by
	# the JB_display_sub_cats_table($cats, $index) function
	# The sub categories are displayed below the main categories
	# If the number of sub-categories goes over a threshold,
	# a 'More..' link is displayed.
	# An importnat thing to remember is that
	# The structure can have any amount of columns
	# depending on the JB_SUB_CATEGORY_COLS setting,
	# that can be confugured in Admin.
	# eg
	#  <table>
	#	<tr><td>
	#		<td>category 1</td><td>category 2</td>
	#		<td>category 3</td><td>category 4</td>
	#		<td>[More..]</td><td>&nbsp;*</td>
	#	</td></tr>
	# </table>
	# 
	# *  &nbsp; - this is a blank cell
	#####################################

	function set_sub_category_cols() {
		// how many columns in the sub-category
		return JB_SUB_CATEGORY_COLS;

	}

	function echo_open_sub_cat() {

		?>
		<div class="cat_subcategory">
		<table border="0" style="width:100%; margin:0px;">
		<?php


	}

	function echo_open_sub_cat_row() {
		?>
		<tr>
		<?php


	}

	function echo_open_sub_cat_cell($sub_width='33%') {
		?>
			<td valign="top" width="<?php echo $sub_width;?>" >
		<?php

	}

	
	function echo_sub_cat_link(&$child, $trunc_name) {

		?><a class="cat_subcategory_link" href="<?php echo JB_cat_url_write($child['cid'], $child['n'], $child['seo'])?>"><?php echo $trunc_name;?></a>
		<?php
	}

	function echo_sub_cat_count($count) {
	
		?><small class="cat_small_count">(<?php echo $count; ?>)</small><?php
		

	}

	function echo_sub_cat_more_link(&$cat, $more_label) {

		/*

		$cat['n'] = name of the catefory
		$cat['n'] = ID of the category
		$cat['seo'] = SEO URL of the catgeory (if exists a SEO optimized link will be rendered)

		*/

		?> &nbsp; [<a class="cat_more_link" href="<?php echo JB_cat_url_write($cat['cid'], $cat['n'], $cat['seo']);?>"><?php echo 
		$more_label;?></a>]
		<?php

	}

	function echo_close_sub_cat_cell() {

		?>
		</td>
		<?php

	}

	function echo_close_sub_cat_row() {
		?>
		</tr>
		<?php

	}

	function echo_sub_cat_empty_cell() {

		?><td>&nbsp;</td><?php

	}

	function echo_close_sub_cat() {

		?>
		</table>
		</div>
		<?php

	}


	#####################################
	# The following methods are used by
	# the JB_display_sub_cats_compact($cats, $index) function
	# This function are used if the following setting
	# is enabled in Admin->Main Config:
	# " Format sub-categories into tabes (on the front page)?"
	# No (Keep them as compact as possible!)
	#####################################

	function echo_open_sub_cat_c() {

		?>
		<div class="cat_subcategory">
		<?php

	}

	function echo_sub_cat_c_link(&$cat, $anchor, $space='') {
		
		echo $space;?><a class="cat_subcategory_link" href="<?php echo JB_cat_url_write($cat['cid'], $cat['n'], $cat['seo']); ?>"><?php echo $anchor;?></a>
		<?php

	}

	function echo_sub_cat_c_count($count) {
		?><small class='cat_small_count'>(<?php echo $count;?>)</small><?php
	}

	function sub_cat_c_set_space($space='&nbsp;') {
		return $space;
	}

	function echo_sub_cat_c_more_link(&$cat, $more_label) {

		/*

		$cat['n'] = name of the catefory
		$cat['n'] = ID of the category
		$cat['seo'] = SEO URL of the catgeory (if exists a SEO optimized link will be rendered)

		*/

		?> &nbsp; [<a class="cat_more_link" href="<?php echo JB_cat_url_write($cat['cid'], $cat['n'], $cat['seo']);?>"><?php echo $more_label;?></a>]
		<?php

	}

	function echo_close_sub_cat_c() {
		?>
		</div>
		<?php

	}

	


}

?>