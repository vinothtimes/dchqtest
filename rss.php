<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

if (defined('JB_RSS_FEED_PATH')) { 
	// config.php was already included
	// - this file is included by another file 
	define ('JB_OMIT_SESSION_START', true);
	define ('NO_HOUSE_KEEPING', true);
	$out_to_browser = false; // send outpt to rss.xml
		
} else {
	require (dirname(__FILE__)."/config.php");
	$out_to_browser = true; // send output to browser

}


require_once (dirname(__FILE__)."/include/posts.inc.php");

$JobListAttributes = new JobListAttributes();
$JobListAttributes->clear();

$out_to_file = true;

$cat ='';
########################################################

# Special function to convert HTML string to UTF-8


function JB_rss_xmlentities($string, $quote_style=ENT_COMPAT)
{
	// convert all entities to UTF-8 encoded string
	// this is to preserve characters of other languages
	// encoded using htmlentities
	
	require_once (dirname(__FILE__)."/include/xml_feed_functions.php");
	$string = JBXM_html_entity_decode($string);
	// xmlentities
	$trans = array(
		"<" => '&lt;', 
		"&" => '&amp;', 
		">" => '&gt;', 
		'"' => '&quot;',  
		'\'' => '&apos;');

	$string = strtr($string, $trans);	
	return $string;

} 


$date = date('r');
$gmt_diff = date('O');


$q = "?";


if (!$logo_path = JB_resolve_document_path(JB_RSS_FEED_LOGO)) {
	$logo_path = JB_RSS_FEED_LOGO;
}

$img_size = @getimagesize ($logo_path);

if ($img_size[0]==false) { 
	// try to get it by using http 
	$img_size = @getimagesize (JB_RSS_FEED_LOGO);
	
}

if (isset($_REQUEST['cat'])) {
	$cat_id = (int) $_REQUEST['cat'];
	$title_append = ' - '.JB_getCatName($cat_id);
	$out_to_file = false;
} else {
	$cat_id = null;
}

if (isset($_REQUEST['emp'])) {
	$emp_id = (int) $_REQUEST['emp'];
	require_once('include/profiles.inc.php');
	$title_append =  ' - '.JB_get_employer_name($emp_id);
	$out_to_file = false;
} else {
	$emp_id = null;
}


$output = 
"<".$q."xml version=\"1.0\" encoding=\"utf-8\" ".$q.">\n"
."<rss version=\"2.0\">\n"
   ."<channel>\n"
      ."<title>".JB_rss_xmlentities(strip_tags(JB_SITE_HEADING).$title_append)."</title>\n"
      ."<link>".JB_BASE_HTTP_PATH."</link>\n"
      ."<description>".JB_rss_xmlentities(strip_tags(JB_SITE_DESCRIPTION))."</description>\n"
      ."<language>en</language>\n"
      ."<pubDate>".$date."</pubDate>\n"
      ."<lastBuildDate>".$date."</lastBuildDate>\n"
      ."<docs>http://blogs.law.harvard.edu/tech/rss</docs>\n"
      ."<generator>HiTeacher custom in-house RSS generator</generator>\n"
      ."<managingEditor>".JB_SITE_CONTACT_EMAIL."</managingEditor>\n"
      ."<webMaster>".JB_SITE_CONTACT_EMAIL."</webMaster>\n"
      ."<image>\n"
         ."<link>".JB_BASE_HTTP_PATH."</link>\n"
         ."<title>".JB_BASE_HTTP_PATH."</title>\n"
         ."<url>".JB_RSS_FEED_LOGO."</url>\n"
		 ."<width>".$img_size[0]."</width>"
		 ."<height>".$img_size[1]."</height>"
      ."</image>\n";

     
	$now = (gmdate("Y-m-d H:i:s"));

	if ($cat_id) {
	  $extra_sql = JB_search_category_tree_for_posts($cat_id);
	}

	if ($emp_id) {
		$extra_sql = "AND user_id=".jb_escape_sql($emp_id)." ";
	}

	if (!defined('JB_POSTS_PER_RSS')) {
	  define ('JB_POSTS_PER_RSS', JB_POSTS_PER_PAGE);
	}
	$JB_POSTS_PER_RSS = JB_POSTS_PER_RSS;
	if (!is_numeric($JB_POSTS_PER_RSS) || ($JB_POSTS_PER_RSS==0)) {
		$JB_POSTS_PER_RSS = '15';
	}
	// removed from WHERE: DATE_SUB('".$now."',INTERVAL 30 DAY) <= `post_date` AND
	$sql = "select *, `post_date` AS DAY,  DATE_FORMAT(`post_date`, '%a, %d %b %Y %H:%i:%s $gmt_diff') AS formatted_date from posts_table WHERE  `approved`='Y' AND expired='N' ".jb_escape_sql($extra_sql)." ORDER BY `post_date` DESC LIMIT ".$JB_POSTS_PER_RSS;


	

	$PostingForm = &JB_get_DynamicFormObject(1);
	$result = JB_mysql_query($sql) or die (mysql_error());


	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		
		$PostingForm->set_values($row);

		$TITLE = $PostingForm->get_raw_template_value ("TITLE");

		$DESCRIPTION = $PostingForm->get_raw_template_value ("DESCRIPTION");
		$row['formatted_date'] = JB_get_formatted_date($row['post_date']);

		
		// force whitespace & strip tags.

		$DESCRIPTION = str_replace('<',' <',$DESCRIPTION);
		$DESCRIPTION = str_replace('>','> ',$DESCRIPTION);
		$DESCRIPTION = html_entity_decode(strip_tags($DESCRIPTION));
		$DESCRIPTION = preg_replace('/[\n\r\t]/',' ',$DESCRIPTION);
		$DESCRIPTION = str_replace('  ',' ',$DESCRIPTION); 

		$output .=
		"<item>\n"
		 ."<title>".JB_rss_xmlentities($TITLE)."</title>\n"
		 ."<link>".JB_job_post_url($row['post_id'], $JobListAttributes, JB_BASE_HTTP_PATH.'index.php')."</link>\n"
		 ."<description>".JB_rss_xmlentities (JB_truncate_html_str ($DESCRIPTION,  255, $trunc_str_len))."...</description>\n"
		 ."<pubDate>".$row['formatted_date']."</pubDate>\n"
		 ."<guid>".JB_job_post_url($row['post_id'], $JobListAttributes, JB_BASE_HTTP_PATH.'index.php')."</guid>\n"
		."</item>\n";

	}
	  
	$output .= '</channel>';
	$output .= '</rss>';

	if ($out_to_file) {
		$file = fopen (trim(JB_RSS_FEED_PATH), "wb");
		fwrite ($file, ($output), strlen(($output)));
		fclose ($file);
	}

	if ($out_to_browser) {
		header('Content-type: application/xml; charset=UTF-8');
		echo ($output);
	}

	
?>