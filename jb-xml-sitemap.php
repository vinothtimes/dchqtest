<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require (dirname(__FILE__).'/config.php');


function JB_load_sitemap_data() {

	$data = array();

	$sql = "SELECT val FROM jb_variables where `key`='SMAP_MAIN_PRIORITY' ";
	$result = JB_mysql_query($sql);
	$row = mysql_fetch_row($result);
	$data['main_priority'] = $row[0];
	if ($data['main_priority']=='') {
		$data['main_priority'] = '0.5';
	}
	$sql = "SELECT val FROM jb_variables where `key`='SMAP_JOBS_PRIORITY' ";
	$result = JB_mysql_query($sql);
	$row = mysql_fetch_row($result);
	$data['jobs_priority'] = $row[0];
		if ($data['jobs_priority']=='') {
		$data['jobs_priority'] = '0.5';
	}
	$sql = "SELECT val FROM jb_variables where `key`='SMAP_JOBS_MAX' ";
	$result = JB_mysql_query($sql);
	$row = mysql_fetch_row($result);
	$data['jobs_max'] = $row[0];
		if ($data['jobs_max']=='') {
		$data['jobs_max'] = '10000';
	}
	$sql = "SELECT val FROM jb_variables where `key`='SMAP_EMP_PRIORITY' ";
	$result = JB_mysql_query($sql);
	$row = mysql_fetch_row($result);
	$data['emp_priority'] = $row[0];
	if ($data['emp_priority']=='') {
		$data['emp_priority'] = '0.5';
	}
	$sql = "SELECT val FROM jb_variables where `key`='SMAP_CAT_PRIORITY' ";
	$result = JB_mysql_query($sql);
	$row = mysql_fetch_row($result);
	$data['cat_priority'] = $row[0];
		if ($data['cat_priority']=='') {
		$data['cat_priority'] = '0.5';
	}

	$sql = "SELECT * FROM sitemaps_urls ";
	$result = JB_mysql_query($sql);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$data['extra_urls'] = $data['extra_urls'].$row['url'].' '.$row['priority'].' '.$row['changefreq']."\n";
	}

	return $data;

}

function JB_xmlentities($string, $quote_style=ENT_COMPAT)
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


##########################
// https://www.google.com/webmasters/tools/docs/en/protocol.html

/*

    * always
    * hourly
    * daily
    * weekly
    * monthly
    * yearly
    * never
*/

$data = JB_load_sitemap_data();
$gmt_diff = date('O');

$output .=

'<?xml version="1.0" encoding="UTF-8"?>'."\n"
.'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" >'."\n";
	// main URL
	$output .='<url>'."\n"
      .'<loc>'.JB_xmlentities(JB_BASE_HTTP_PATH).'</loc>'."\n"
      .'<lastmod>'.date('Y-m-d').'</lastmod>'."\n"
      .'<changefreq>hourly</changefreq>'."\n"
      .'<priority>'.$data['main_priority'].'</priority>'."\n"
   .'</url>'."\n";

   // job urls

   // AND expired='N'

	// if mod-reqrite is enabled, we must fetch all the columns since we do not
	// know what fields will be used for the URLs.
	if (JB_JOB_MOD_REWRITE == 'YES') {
		$sql = "SELECT *, DATE_FORMAT(`post_date`, '%Y-%m-%dT%H:%i:%s+00:00') AS formatted_date FROM posts_table  WHERE  `approved`='Y'  ORDER BY expired DESC, `post_date` DESC LIMIT ".jb_escape_sql($data['jobs_max']);
	} else {
		$sql = "SELECT post_id, expired, DATE_FORMAT(`post_date`, '%Y-%m-%dT%H:%i:%s+00:00') AS formatted_date FROM posts_table  WHERE  `approved`='Y'  ORDER BY expired DESC, `post_date` DESC LIMIT ".$data['jobs_max'];

	}


	$result = jb_mysql_query($sql);

	$PForm = JB_get_DynamicFormObject(1);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$PForm->set_values($row);
		if ($row['expired']=='Y') {
			// expired posts get archived
			$changefreq = 'never'; 
			$piority = '0.1';
		} else {
			$changefreq = 'monthly'; // on avg..
			$priority = $data['jobs_priority'];
		}

		global $params;
		$params = $row;

		$output .='<url>'."\n"
					.'<loc>'.JB_xmlentities(JB_job_post_url($row['post_id'], null, JB_BASE_HTTP_PATH.'index.php')).'</loc>'."\n"
					.'<lastmod>'.$row['formatted_date'].'</lastmod>'."\n"
					.'<changefreq>'.$changefreq.'</changefreq>'."\n"
					.'<priority>'.$priority.'</priority>'."\n"
				.'</url>'."\n";

	}
   // employer profiles

   	$sql = "SELECT profile_id, expired, DATE_FORMAT(`profile_date`, '%Y-%m-%dT%H:%i:%s+00:00') AS formatted_date FROM profiles_table  ORDER BY expired DESC, `profile_date` DESC";
//echo $sql;
	$result = jb_mysql_query($sql);

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		if ($row['expired']=='Y') {
			// expired posts get archived
			$changefreq = 'never'; 
			$piority = '0.1';
		} else {
			$changefreq = 'monthly'; // on avg..
			$priority = $data['emp_priority'];
		}

		$output .='<url>'."\n"
					.'<loc>'.JB_xmlentities(JB_emp_profile_url($row['profile_id'], null, JB_BASE_HTTP_PATH.'index.php')).'</loc>'."\n"
					.'<lastmod>'.$row['formatted_date'].'</lastmod>'."\n"
					.'<changefreq>'.$changefreq.'</changefreq>'."\n"
					.'<priority>'.$priority.'</priority>'."\n"
				.'</url>'."\n";
	}


   // category URLs


    $sql = "SELECT t1.category_id as CID, seo_fname, t2.category_name as CNAME FROM categories as t1, cat_name_translations as t2 WHERE t1.category_id=t2.category_id AND t2.lang='EN' AND form_id=1  ";

	$result = jb_mysql_query($sql);

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		
		$changefreq = 'daily'; // on avg..
		$priority = $data['cat_priority'];
		
		$output .='<url>'."\n"
					.'<loc>'.JB_xmlentities(JB_cat_url_write($row['CID'], $row['CNAME'],  $row['seo_fname'], JB_BASE_HTTP_PATH.'index.php')).'</loc>'."\n"
					.'<lastmod>'.date('Y-m-d').'</lastmod>'."\n"
					.'<changefreq>'.$changefreq.'</changefreq>'."\n"
					.'<priority>'.$priority.'</priority>'."\n"
				.'</url>'."\n";
	}




   // Additional URLs

    $sql = "SELECT * FROM sitemaps_urls ";
//echo $sql;
	$result = jb_mysql_query($sql);

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		
		$output .='<url>'."\n"
			.'<loc>'.JB_xmlentities($row['url']).'</loc>'."\n"
			.'<lastmod>'.date('Y-m-d').'</lastmod>'."\n"
			.'<changefreq>'.JB_xmlentities($row['changefreq']).'</changefreq>'."\n"
			.'<priority>'.JB_xmlentities($row['priority']).'</priority>'."\n"
		.'</url>'."\n";

	}
   
$output .= '</urlset>'."\n";


header('Content-type: application/xml; charset=UTF-8');
	echo  ($output);
?>
