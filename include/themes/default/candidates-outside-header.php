<?php 
echo $JBMarkup->get_doctype(); 
$JBMarkup->markup_open(); 
$JBMarkup->head_open(); 

JBPLUG_do_callback('can_outside_extra_meta_tags', $A = false);

$JBMarkup->charset_meta_tag(); 

$JBMarkup->title_meta_tag(jb_escape_html($page_title));
$JBMarkup->stylesheet_link(JB_get_maincss_url());

if (strpos($_SERVER['PHP_SELF'], 'logout.php')!==false) {
	// redirect to home page after logout
	echo '<meta HTTP-equiv="REFRESH" content="1; URL='.JB_BASE_HTTP_PATH.'"> ';
}

$JBMarkup->head_close();

$JBMarkup->body_open('style="background-color:white"');

?>

<div>
<p style="text-align:center;">
	<a href="<?php echo JB_BASE_HTTP_PATH; ?>"><img border="0" alt="<?php echo jb_escape_html(JB_SITE_NAME); ?>" src="<?php echo JB_SITE_LOGO_URL; ?>"></a>
</p>
