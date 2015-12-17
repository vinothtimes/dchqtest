<?php
echo $JBMarkup->get_doctype(); // must always be the first line that is outputted
$JBMarkup->markup_open(); 

$JBMarkup->head_open(); 

$JBMarkup->charset_meta_tag(); 
$JBMarkup->base_meta_tag();

JB_echo_index_meta_tags(); // additional meta tags

$JBMarkup->stylesheet_link(JB_get_maincss_url()); // main.css

JBPLUG_do_callback('index_header_head', $A = false);

?>
<style type="text/css">

A.white_link {
	color:white;
	font-weight: bold;

}

.orange_bar {
	background-color: #FF880E; 
	height:24px; 
	color: white;

}

.blue_bar {
	background-color: #D5E0FC; 
	height:24px
}

.site_description {
	color: #569841;
	font-weight: bold;
}

.footer_text {
	color: #D8DDE8;
}

</style>
<?php

$JBMarkup->head_close();
$JBMarkup->body_open();

?>
<div style="background-color: white;">
<div class="orange_bar">
	 <a class="white_link" href="<?php echo JB_BASE_HTTP_PATH.JB_CANDIDATE_FOLDER?>"><?php echo $label['classic_job_seeker_signin']; ?></a></div>
	<div style="float:right; ">
		<font face="Arial" size="2"><a href="<?php echo JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER?>"><?php echo $label['classic_emp_signin']; ?></a> | 
		<a href="<?php echo JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER?>"><?php echo $label['classic_view_resumes']; ?></a> | <a href="<?php echo JB_BASE_HTTP_PATH.JB_EMPLOYER_FOLDER?>"><?php echo $label['classic_post_new']; ?></a></font></div>

<p style="margin-left:10px"><a href="<?php echo JB_BASE_HTTP_PATH; ?>" >
<img alt="<?php echo jb_escape_html(JB_SITE_NAME); ?>" src="<?php echo JB_THEME_URL?>images/logo.gif" border="0"></a>
<br>
<span class="site_description"><?php echo JB_SITE_DESCRIPTION; ?></span></p>
<?php JBPLUG_do_callback('index_home_title', $A = false); ?>
<div class="blue_bar">
</div>
<div style="width:80%; margin-left:10px">
<?php JBPLUG_do_callback('index_header_adcode', $A = false); ?>