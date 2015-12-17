<div class="cat_list_box">
	<div class="cat_box_title"><?php echo $label['category_header']?></div>
		<?php
		$categories = JB_getCatStruct($cat, $_SESSION["LANG"], 1);
		JB_display_categories($categories, $JB_CAT_COLS);
	?>
</div>