<?php 

echo $JBMarkup->get_doctype(); // must always be the first line that is outputted

$JBMarkup->markup_open(); 
$JBMarkup->head_open(); 

$JBMarkup->charset_meta_tag(); 
$JBMarkup->base_meta_tag();

JB_echo_index_meta_tags(); // additional meta tags


$JBMarkup->stylesheet_link(JB_get_maincss_url()); // main.css

JBPLUG_do_callback('index_header_head', $A = false);

$JBMarkup->head_close(); 

$JBMarkup->body_open('style="margin:0px"'); // <body>
?>

<table border="0"  cellpadding="0" cellspacing="0" align="center" width="750"  bgcolor="#FFFFFF" >

  <tr>
    <td width="100%" bgcolor="#ffffff" height="10" >
	<a href="<?php echo JB_BASE_HTTP_PATH;?>">
			<img border="0" alt="<?php echo jb_escape_html(JB_SITE_NAME); ?>" src="<?php echo JB_THEME_URL; ?>images/header-top.gif"></a></td>
  </tr>
  <tr>
    <td width="100%" bgcolor="#D5D6E1" align="center" height="10" >
	<?php JBPLUG_do_callback('index_header_adcode', $A = false); ?>
</td>
  </tr>
  <tr>
    <td width="100%" align="left" bgcolor="#D5D6E1"  valign="top" >
                     

              <table border="0" align="center" cellpadding="0" cellspacing="0"   width="98%"  bgcolor="#FFFFFF" >

           <tr>
     
             <td  valign="top" >