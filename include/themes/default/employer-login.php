<?php

echo $JBMarkup->get_doctype();
$JBMarkup->markup_open(); 
$JBMarkup->head_open(); 
$JBMarkup->charset_meta_tag(); 
$JBMarkup->stylesheet_link(JB_get_maincss_url()); // main.css
$JBMarkup->title_meta_tag(JB_SITE_NAME);

$url = ($_REQUEST['page']=='') ? "index.php" : ($_REQUEST['page']);
$JBMarkup->refresh_meta_tag($url);

$JBMarkup->head_close();
$JBMarkup->body_open('style="background-color:white"');

?>

<div style="text-align: center"><a href="<?php echo JB_BASE_HTTP_PATH; ?>"><img border="0" src="<?php echo JB_SITE_LOGO_URL; ?>"></a> <br>
<h3><?php 
$label["employer_logging_in"] = str_replace ("%SITE_NAME%", jb_escape_html(JB_SITE_NAME) , $label["employer_logging_in"]);
echo $label["employer_logging_in"]; ?></h3>
<?php
JB_validate_employer_login();
?>  
</div>
<?php
$JBMarkup->body_close();
$JBMarkup->markup_close();
?>