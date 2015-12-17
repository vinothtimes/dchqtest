<?php
#copyright Jamit Software 2005-2010, www.jamit.com 

############################

$JBMarkup->enable_menu();
$JBMarkup->enable_overlib();
$JBMarkup->enable_wz_dragdrop();
###############################

echo $JBMarkup->get_doctype();
$JBMarkup->markup_open(); 

$JBMarkup->head_open();

JBPLUG_do_callback('employers_header_head', $A = false); 


$JBMarkup->charset_meta_tag();


$JBMarkup->title_meta_tag(jb_escape_html(JB_SITE_NAME));
$JBMarkup->stylesheet_link(JB_get_maincss_url());
$JBMarkup->stylesheet_link(JB_get_employerscss_url());


$JBMarkup->head_close();

$JBMarkup->body_open('style="margin:0px; background-color:white"');

?>

<table border="0" cellpadding="0" cellspacing="0" align="center" width="750"  bgcolor="#FFFFFF" >

  <tr>
    <td  bgcolor="#ffffff" height="10" >
	<a href="index.php">
			<img width="750" height="112" border="0" alt="<?php echo jb_escape_html(JB_SITE_NAME); ?>" src="<?php echo JB_THEME_URL; ?>images/employers-header.gif"></a></td>
  </tr>
  <tr>
    <td  bgcolor="#D5D6E1" align="center" height="10" >
<?php require(jb_get_employers_menu_path()); ?>
</td>
  </tr>
  <tr>
    <td  align="left" bgcolor="#D5D6E1"  valign="top" >
                     

         <table border="0" align="center" cellpadding="0" cellspacing="0"   width="98%"  bgcolor="#FFFFFF" >

           <tr>
     
            <td  valign="top" >
			<div class="employer_content">