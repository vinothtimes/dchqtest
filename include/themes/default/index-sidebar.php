<div class="index_sidebar">
<?php

	JBPLUG_do_callback('index_sidebar_top', $A = false);
	JB_template_category_list_box(JB_CAT_COLS_FP);
	JB_display_available_languages();
	JBPLUG_do_callback('index_sidebar_bottom', $A = false);
	
?>
</div>