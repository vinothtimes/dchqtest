<?php

###########################
$JBMarkup->enable_menu();
$JBMarkup->enable_overlib();
$JBMarkup->enable_wz_dragdrop();
###############################

echo $JBMarkup->get_doctype();
$JBMarkup->markup_open(); 
$JBMarkup->head_open(); 

$JBMarkup->charset_meta_tag(); 

$JBMarkup->title_meta_tag(jb_escape_html(JB_SITE_NAME));

$JBMarkup->stylesheet_link(JB_get_maincss_url());
$JBMarkup->stylesheet_link(JB_get_candidatescss_url());


JBPLUG_do_callback('candidates_header_head', $A = false); 
$JBMarkup->head_close();

$JBMarkup->body_open('style="margin:0px; background-color:white"');
?>



<table cellpadding="0" cellspacing="0" style="margin: 0 auto; width:750px; border:0px; background-color:#ffffff; "  >

  <tr>
    <td width="100%" bgcolor="#ffffff" valign="bottom">
	<a href="index.php">
			<img border="0" alt="<?php echo jb_escape_html(JB_SITE_NAME); ?>" src="<?php echo JB_THEME_URL; ?>images/candidates-header.gif"></a></td>
  </tr>
  <tr>
    <td style="width:100%; background-color:#D5D6E1; text-align:center"  >
<?php require(jb_get_candidates_menu_path());?>
</td>
  </tr>
  <tr>
    <td width="100%" align="left" bgcolor="#D5D6E1"  valign="top" >
                     

       <table style="margin: 0 auto; width:98%; border:0px; background-color:#ffffff; " cellpadding="0" cellspacing="0"  >

           <tr>
     
             <td  valign="top" height="100%">
			 <div class="candidate_content">