<div class='category_index' >
<div style="padding-bottom:3.5em; text-align:left">
	<div style="float:left; ">
	<span class="category_name"><?php echo $label['root_category_link']; ?> - <?php echo jb_escape_html($CAT_NAME);?></span> <br>
	<span class="category_path"><?php echo $CAT_PATH; // (note: $CAT_PATH already html escaped )?><br>
	</span>
	<div>

</div>

	</div>
	<div style="float: right;">
	<a href="<?php echo JB_BASE_HTTP_PATH; ?>"><?php echo $label['go_to_site_home']; ?></a>
<?php

if (JB_CAT_RSS_SWITCH=='YES') {
?>
	<p>

	<a href="<?php echo JB_BASE_HTTP_PATH."rss.php?cat=".jb_escape_html($_REQUEST['cat']); ?>"><img alt="RSS" src="<?php echo JB_THEME_URL.'images/rss_cat.png'?>" border="0" ></a> <?php
	
	echo $label['rss_subscribe'];
	?>
	</p>
<?php
}

?>
	</div>
</div>


<?php


JB_display_categories($CAT_STRUCT, $JB_CAT_COLS);

?>

</div>

<?php
JB_list_jobs ("BY_CATEGORY");


?>