<div class="cat_list_box">
	<span class="cat_box_title"><?php echo $label['category_header']?></span>
		<?php
		$categories = JB_getCatStruct($cat, $_SESSION["LANG"], 1);
		JB_display_categories($categories, $JB_CAT_COLS);
	?>
</div>