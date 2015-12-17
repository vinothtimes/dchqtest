<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
if (!defined('JB_BASE_HTTP_PATH')) { // config.php needs to be required
	die ('This file cannot be accessed directly, please log in to Admin.');
}

#############################################
function JB_do_upgrade ($flag) {
	$upgrade_needed = false;
	global $jb_mysql_link;

	global $JBMarkup;

	if (defined('JB_VERSION')) {
		
		$version = jb_get_variable('JB_VERSION');
		if ($version == JB_VERSION) { // current version database
			return false; // upgrade not needed
		}
		
	}

	$sql = "DELETE FROM `form_fields` WHERE `field_id` = 999193 LIMIT 1";
	if ($flag) JB_mysql_query($sql);
	$sql = "DELETE FROM `form_fields` WHERE `field_id` = 999194 LIMIT 1";
	if ($flag) JB_mysql_query($sql);



	$sql = "UPDATE `form_fields` SET `template_tag`='RESUME_EMAIL' WHERE form_id=2 and field_label='Email' AND field_id=40 ";
	
	if ($flag) JB_mysql_query($sql) or die (mysql_error());



	if (!does_field_exist("xml_export_elements", "is_mandatory")) {

		$sql = "DROP TABLE IF EXISTS `xml_export_elements`";

		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());

		$sql = "CREATE TABLE `xml_export_elements` (
		  `element_id` int(11) NOT NULL auto_increment,
		  `element_name` varchar(255) NOT NULL default '',
		  `is_cdata` set('Y','N') NOT NULL default '',
		  `parent_element_id` int(11) default '0',
		  `form_id` int(11) NOT NULL default '0',
		  `field_id` varchar(128) NOT NULL default '0',
		  `schema_id` int(11) NOT NULL default '0',
		  `attributes` varchar(255) NOT NULL default '',
		  `static_data` varchar(255) NOT NULL default '',
		  `is_pivot` set('Y','N') NOT NULL default '',
		  `description` varchar(255) NOT NULL default '',
		  `fieldcondition` varchar(255) NOT NULL default '',
		  `is_boolean` set('Y','N') NOT NULL default 'N',
		  `qualify_codes` set('Y','N') NOT NULL default 'N',
		  `qualify_cats` set('Y','N') NOT NULL default 'N',
		  `truncate` int(11) NOT NULL default '0',
		  `strip_tags` set('Y','N') NOT NULL default 'N',
		  `is_mandatory` set('Y','N') NOT NULL default '',
		  PRIMARY KEY  (`element_id`) )";

		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());

		$sql ="INSERT INTO `xml_export_elements` VALUES (1, 'rss', 'N', 0, 1, '0', 1, 'version =\"2.0\" xmlns:g=\"http://base.google.com/ns/1.0\"', '', '', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (2, 'channel', 'N', 1, 1, '0', 1, '', '', '', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (4, 'description', 'N', 2, 1, '0', 1, '', '%SITE_DESCRIPTION%', '', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (5, 'link', 'N', 2, 1, '0', 1, '', '%BASE_HTTP_PATH%', '', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (6, 'item', 'N', 2, 1, '0', 1, '', '', 'Y', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (11, 'g:expiration_date', 'N', 6, 1, '', 1, '', '%EXPIRE_DATE%', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (14, 'g:immigration_status', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (19, 'g:location', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (20, 'g:salary', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (21, 'g:salary_type', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (23, 'publisher', 'N', 22, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (24, 'publisherurl', 'N', 22, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (25, 'lastBuildDate', 'N', 22, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (26, 'job', 'N', 22, 1, '', 2, '', '', 'Y', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (27, 'title', 'Y', 26, 1, '2', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (28, 'date', 'Y', 26, 1, 'post_date', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (29, 'referencenumber', 'Y', 26, 1, 'post_id', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (31, 'company', 'Y', 26, 1, '8', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (32, 'city', 'Y', 26, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (34, 'country', 'Y', 26, 1, '', 2, '', 'USA', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (35, 'postalcode', 'Y', 26, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (36, 'description', 'Y', 26, 1, 'summary', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (37, 'salary', 'Y', 26, 1, '10', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (38, 'experience', 'Y', 26, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (40, 'jobtype', 'Y', 26, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (42, 'jobs', 'N', 0, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (43, 'job', 'N', 42, 1, '', 4, '', '', 'Y', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (44, 'title', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (45, 'job-code', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (46, 'action', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (47, 'job-board-name', 'N', 43, 1, '', 4, '', '%SITE_NAME%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (48, 'job-board-url', 'N', 43, 1, '', 4, '', '%BASE_HTTP_PATH%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (49, 'detail-url', 'N', 43, 1, '', 4, '', '%LINK%', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (50, 'apply-url', 'N', 43, 1, '', 4, '', '%LINK%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (51, 'description', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (52, 'summary', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'Y', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (53, 'required-skills', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (54, 'required-education', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (55, 'required-experience', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (56, 'full-time', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (57, 'part-time', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (59, 'flex-time', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (60, 'internship', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (61, 'volunteer', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (62, 'exempt', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (63, 'contract', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (64, 'permanent', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (65, 'temporary', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (66, 'telecommute', 'N', 51, 1, '', 4, '', '', 'N', '', '', 'Y', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (67, 'compensation', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (68, 'salary-range', 'N', 67, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (69, 'salary-amount', 'N', 67, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (70, 'salary-currency', 'N', 67, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (71, 'benefits', 'N', 67, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (72, 'posted-date', 'N', 43, 1, '', 4, '', '%DATE%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (73, 'close-date', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (74, 'location', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (75, 'address', 'N', 74, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		$sql ="INSERT INTO `xml_export_elements` VALUES (76, 'city', 'N', 74, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (77, 'state', 'N', 74, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (78, 'zip', 'N', 74, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (79, 'country', 'N', 74, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (80, 'area-code', 'N', 74, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (81, 'contact', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (82, 'name', 'N', 81, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (84, 'email', 'N', 81, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (85, 'hiring-manager-name', 'N', 81, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (86, 'hiring-manager-email', 'N', 81, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (87, 'phone', 'N', 81, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (88, 'fax', 'N', 81, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (89, 'company', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (90, 'name', 'N', 89, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (91, 'description', 'N', 89, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (92, 'industry', 'N', 89, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (93, 'url', 'N', 89, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (94, 'title', 'N', 2, 1, '', 1, '', '%SITE_NAME%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (96, 'title', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (97, 'description', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'Y', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (98, 'g:job_function', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (99, 'g:job_industry', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (100, 'g:job_type', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (101, 'link', 'N', 6, 1, '', 1, '', '%LINK%', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (102, 'g:publish_date', 'N', 6, 1, '', 1, '', '%DATE%', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (103, 'g:education', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (104, 'g:employer', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (105, 'guid', 'N', 6, 1, '', 1, '', '%LINK%', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (106, 'image_link', 'N', 6, 1, '', 1, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (107, 'source', 'N', 0, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (108, 'publisher', 'N', 107, 1, '', 2, '', '%SITE_NAME%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (109, 'publisherurl', 'N', 107, 1, '', 2, '', '%BASE_HTTP_PATH%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (110, 'lastBuildDate', 'N', 107, 1, '', 2, '', '%FEED_DATE%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (111, 'job', 'N', 107, 1, '', 2, '', '', 'Y', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (112, 'title', 'N', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (113, 'date', 'N', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (114, 'referencenumber', 'N', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (115, 'url', 'N', 111, 1, '', 2, '', '%LINK%', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (116, 'company', 'N', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (117, 'city', 'N', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (118, 'state', 'N', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (119, 'country', 'N', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (120, 'postalcode', 'N', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (121, 'description', 'N', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'Y', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (122, 'salary', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (123, 'education', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (124, 'jobtype', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'Y', 'Y', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (125, 'category', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'Y', 'Y', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (126, 'experience', 'Y', 111, 1, '', 2, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (127, 'job-category', 'N', 43, 1, '', 4, '', '', 'N', '', '', 'N', 'N', 'Y', 0, 'N', 'N')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (128, 'rss', 'N', 0, 1, '', 3, 'version=\"2.0\"', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (129, 'channel', 'N', 128, 1, '', 3, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (130, 'title', 'N', 129, 1, '', 3, '', '%SITE_NAME%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (131, 'link', 'N', 129, 1, '', 3, '', '%BASE_HTTP_PATH%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (132, 'description', 'N', 129, 1, '', 3, '', '%SITE_DESCRIPTION%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (133, 'language', 'N', 129, 1, '', 3, '', '%DEFAULT_LANG%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (134, 'pubDate', 'N', 129, 1, '', 3, '', '%FEED_DATE%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (135, 'lastBuildDate', 'N', 129, 1, '', 3, '', '%FEED_DATE%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (136, 'docs', 'N', 129, 1, '', 3, '', 'http://blogs.law.harvard.edu/tech/rss', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (137, 'generator', 'N', 129, 1, '', 3, '', 'Jamit Job Board XML export tool', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (138, 'managingEditor', 'N', 129, 1, '', 3, '', '%SITE_CONTACT_EMAIL%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (139, 'webMaster', 'N', 129, 1, '', 3, '', '%SITE_CONTACT_EMAIL%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (140, 'image', 'N', 129, 1, '', 3, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (141, 'link', 'N', 140, 1, '', 3, '', '%BASE_HTTP_PATH%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (142, 'title', 'N', 140, 1, '', 3, '', '%SITE_NAME%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (143, 'url', 'N', 140, 1, '', 3, '', '%RSS_FEED_LOGO%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (144, 'height', 'N', 140, 1, '', 3, '', '%RSS_LOGO_HEIGHT%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (145, 'width', 'N', 140, 1, '', 3, '', '%RSS_LOGO_WIDTH%', 'N', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (146, 'item', 'N', 129, 1, '', 3, '', '', 'Y', '', '', 'N', 'N', 'N', 0, 'N', '')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (147, 'title', 'N', 146, 1, '', 3, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (148, 'link', 'N', 146, 1, '', 3, '', '%LINK%', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (149, 'description', 'N', 146, 1, '', 3, '', '', 'N', '', '', 'N', 'N', 'N', 300, 'Y', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (151, 'pubDate', 'N', 146, 1, '', 3, '', '', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="INSERT INTO `xml_export_elements` VALUES (152, 'guid', 'N', 146, 1, '', 3, '', '%LINK%', 'N', '', '', 'N', 'N', 'N', 0, 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());



		$upgrade_needed = true;
	}

	



	if (!does_field_exist("categories", "seo_fname")) {
		$sql ="ALTER TABLE categories ADD `seo_fname` VARCHAR(100) NULL default NULL";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("categories", "seo_title")) {
		$sql ="ALTER TABLE categories ADD `seo_title` VARCHAR(255) NULL default NULL";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("categories", "seo_desc")) {
		$sql ="ALTER TABLE categories ADD `seo_desc` VARCHAR(255) NULL default NULL";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("categories", "seo_keys")) {
		$sql ="ALTER TABLE categories ADD `seo_keys` VARCHAR(255) NULL default NULL";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;

		// update the Indexes
		$sql = "ALTER TABLE `categories` DROP PRIMARY KEY ";
		if ($flag) JB_mysql_query($sql);
		$sql = "ALTER TABLE `categories` DROP INDEX `category_id` ";
		if ($flag) JB_mysql_query($sql);
		$sql = "ALTER TABLE `categories` ADD INDEX ( `seo_fname` ) ";
		if ($flag) JB_mysql_query($sql) ;
		$sql = "ALTER TABLE `categories` ADD PRIMARY KEY ( `category_id` ) ";
		if ($flag) JB_mysql_query($sql);	
		
	}


	if (!does_field_exist('categories', 'allow_records')) {
		$sql = "ALTER TABLE `categories` ADD `allow_records` SET( 'Y', 'N' ) DEFAULT 'Y' NOT NULL";
		if ($flag) JB_mysql_query($sql) or die (mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("categories", "has_child")) {
		$sql ="ALTER TABLE `categories` ADD `has_child` SET( 'Y', 'N' ) DEFAULT NULL ;";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		if ($flag) JB_compute_cat_has_child();
		if ($flag) {
			JB_cache_flush();
		}
		$upgrade_needed = true;
	}

	

	if (!does_field_exist('lang', 'is_default')) {
		$sql = "ALTER TABLE `lang` ADD `is_default` SET( 'Y', 'N' ) DEFAULT 'N' NOT NULL";
		if ($flag) JB_mysql_query($sql) or die (mysql_error());
		$upgrade_needed = true;
	}
	if (!does_field_exist('lang', 'charset')) {
		$sql = "ALTER TABLE `lang` ADD `charset` VARCHAR( 32 ) NOT NULL ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error());
		$upgrade_needed = true;
	}
	if (!does_field_exist('employers', 'newsletter_last_run')) {
		$sql = "ALTER TABLE `employers` ADD `newsletter_last_run` DATETIME NOT NULL ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error());
		$upgrade_needed = true;
	}
	if (!does_field_exist('users', 'newsletter_last_run')) {
		$sql = "ALTER TABLE `users` ADD `newsletter_last_run` DATETIME NOT NULL ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error());
		$upgrade_needed = true;
	}
	
	if (!does_field_exist('package_invoices', 'payment_method')) {
		$sql = "ALTER TABLE `package_invoices` ADD `payment_method` VARCHAR( 64 ) NOT NULL  ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist('subscription_invoices', 'payment_method')) {
		$sql = "ALTER TABLE `subscription_invoices` ADD `payment_method` VARCHAR( 64 ) NOT NULL ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist('users', 'lang')) {
		$sql = "ALTER TABLE `users` ADD `lang` VARCHAR( 3 ) NOT NULL ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error());
		$upgrade_needed = true;
	}

	

	if (!does_field_exist('skill_matrix', 'matrix_id')) {

		$sql = "CREATE TABLE `skill_matrix` ( ".
			"`matrix_id` INT NOT NULL AUTO_INCREMENT , ".
			"`field_id` INT NOT NULL , ".
			"`row_count` VARCHAR( 255 ) NOT NULL , ".
			"PRIMARY KEY ( `matrix_id` ) ".
			")  ;";
		if ($flag) JB_mysql_query($sql) or die (mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist('skill_matrix_data', 'field_id')) {

		$sql = "CREATE TABLE `skill_matrix_data` ( ".
			"`field_id` INT NOT NULL , ".
			"`row` INT NOT NULL , ".
			"`object_id` INT NOT NULL , ".
			" `user_id` INT NOT NULL , ".
			"`name` VARCHAR( 255 ) NOT NULL , ".
			"`years` VARCHAR( 2 ) NOT NULL , ".
			"`rating` VARCHAR( 2 ) NOT NULL , ".
			"PRIMARY KEY ( `field_id` , `row` ) ". 
			")  ";

		if ($flag) JB_mysql_query($sql) or die (mysql_error()."f53cjku");
		$upgrade_needed = true;
	}

	if (!does_field_exist('skill_matrix_data', 'user_id')) {
		$sql = "ALTER TABLE skill_matrix_data ADD `user_id` int ( 11 ) NOT NULL  ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error()."jhjkh");
		$upgrade_needed = true;
	}

	$sql = "ALTER TABLE `applications` CHANGE `app_id` `app_id` INT( 11 ) NOT NULL AUTO_INCREMENT ";
	if ($flag) JB_mysql_query($sql) or die (mysql_error()."hgfhdde4");

	if (!does_field_exist('currencies', 'code')) {

		$sql = "CREATE TABLE `currencies` ( ".
				"`code` VARCHAR( 3 ) NOT NULL , ".
				"`name` VARCHAR( 50 ) NOT NULL , ".
				"`rate` DECIMAL (10,4) NOT NULL , ".
				"`is_default` SET( 'Y', 'N' ) NOT NULL, ".
				"`sign` VARCHAR (8) NOT NULL , ".
				"`decimal_places` SMALLINT NOT NULL, ".
				"`decimal_point` VARCHAR( 3 ) NOT NULL , ".
				"`thousands_sep` VARCHAR( 3 ) NOT NULL , ".
				"PRIMARY KEY (`code`))";
		if ($flag) JB_mysql_query($sql) or die (mysql_error()."hjhj");
		if ($flag) mysql_query("INSERT INTO `currencies` VALUES ('AUD', 'Australian Dollar', 1.3228, 'N', '$', 2, '.', ',')") or die (mysql_error()."hjhj1");
		if ($flag) mysql_query("INSERT INTO `currencies` VALUES ('CAD', 'Canadian Dollar', 1.1998, 'N', '$', 2, '.', ',')") or die (mysql_error()."hjhj2");
		if ($flag) mysql_query("INSERT INTO `currencies` VALUES ('EUR', 'Euro', 0.8138, 'N', '€', 2, '.', ',')") or die (mysql_error()."hjhj3");
		if ($flag) mysql_query("INSERT INTO `currencies` VALUES ('GBP', 'British Pound', 0.5555, 'N', '£', 2, '.', ',')") or die (mysql_error()."hjhj4");
		if ($flag) mysql_query("INSERT INTO `currencies` VALUES ('JPY', 'Japanese Yen', 110.1950, 'N', '¥', 0, '.', ',')") or die (mysql_error()."hjhj5");
		if ($flag) mysql_query("INSERT INTO `currencies` VALUES ('KRW', 'Korean Won', 1028.8000, 'N', '&#8361;', 0, '.', ',')") or die (mysql_error()."hjhj6");
		if ($flag) mysql_query("INSERT INTO `currencies` VALUES ('USD', 'U.S. Dollar', 1, 'Y', '$', 2, '.', ',')") or die (mysql_error()."hjhj7");

		$upgrade_needed = true;
	}

	if (!does_field_exist('currencies', 'sign')) {
		$sql = "ALTER TABLE currencies ADD `sign` VARCHAR( 8 ) NOT NULL  ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error()."ghgg7fcd");
		$upgrade_needed = true;
	}

	if (!does_field_exist('currencies', 'decimal_places')) {
		$sql = "ALTER TABLE currencies ADD `decimal_places` SMALLINT NOT NULL  ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error()."gdd3hgg7fcd");
		$upgrade_needed = true;
	}

	if (!does_field_exist('currencies', 'decimal_point')) {
		$sql = "ALTER TABLE currencies ADD `decimal_point` VARCHAR( 1 ) NOT NULL  ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error()."ghgg7fcd");
		$upgrade_needed = true;
	}

	if (!does_field_exist('currencies', 'thousands_sep')) {
		$sql = "ALTER TABLE currencies ADD `thousands_sep` VARCHAR( 3 ) NOT NULL  ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error()."ghgg7fcd");
		$upgrade_needed = true;
	}

	if (!does_field_exist('packages', 'currency_code')) {
		$sql = "ALTER TABLE packages ADD `currency_code` VARCHAR( 3 ) NOT NULL  ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error()."gh7fcd");
		$upgrade_needed = true;
	}

	if (!does_field_exist('subscriptions', 'currency_code')) {
		$sql = "ALTER TABLE subscriptions ADD `currency_code` VARCHAR( 3 ) NOT NULL  ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hjkh');
		$upgrade_needed = true;
	}

	if (!does_field_exist('subscription_invoices', 'currency_code')) {
		$sql = "ALTER TABLE subscription_invoices ADD `currency_code` VARCHAR( 3 ) NOT NULL  ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hjddkh');
		$upgrade_needed = true;
	}

	if (!does_field_exist('subscription_invoices', 'currency_rate')) {
		$sql = "ALTER TABLE subscription_invoices ADD `currency_rate` DECIMAL( 10, 4) NOT NULL  ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hjw3dkh');
		$upgrade_needed = true;
	}

	if (!does_field_exist('package_invoices', 'currency_code')) {
		$sql = "ALTER TABLE package_invoices ADD `currency_code` VARCHAR( 3 ) NOT NULL  ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hjddkh');
		$upgrade_needed = true;
	}

	if (!does_field_exist('package_invoices', 'currency_rate')) {
		$sql = "ALTER TABLE package_invoices ADD `currency_rate` DECIMAL( 10, 4) NOT NULL  ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hjw3dkh');
		$upgrade_needed = true;
	}

	if (!does_field_exist('form_fields', 'is_cat_multiple')) {
		$sql = "ALTER TABLE form_fields ADD `is_cat_multiple` SET ('Y', 'N') NOT NULL DEFAULT 'N' ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hjw3sdh');
		$upgrade_needed = true;
	}

	if (!does_field_exist('form_fields', 'multiple_sel_all')) {
		$sql = "ALTER TABLE form_fields ADD `multiple_sel_all` char(1) NOT NULL default 'N' ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrwgkh');
		$upgrade_needed = true;
	}


	if (!does_field_exist('form_fields', 'cat_multiple_rows')) {
		$sql = "ALTER TABLE form_fields ADD `cat_multiple_rows` INT(11) NOT NULL DEFAULT 1 ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hw33rdkh');
		$upgrade_needed = true;
	}
	if (!does_field_exist('form_fields', 'is_blocked')) {
		$sql = "ALTER TABLE form_fields ADD `is_blocked` char(1) NOT NULL default 'N' ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrwgkh');
		$upgrade_needed = true;
	}

	if (!does_field_exist('form_fields', 'is_prefill')) {
		$sql = "ALTER TABLE form_fields ADD `is_prefill` char(1) NOT NULL default 'N' ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrwgkh');
		$upgrade_needed = true;
	}
	if (!does_field_exist('form_fields', 'is_member')) {
		$sql = "ALTER TABLE form_fields ADD `is_member` char(1) NOT NULL default 'N' ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrkjwgkh');
		$upgrade_needed = true;
	}

	if (!does_field_exist('subscriptions', 'can_view_blocked')) {
		$sql = "ALTER TABLE subscriptions ADD `can_view_blocked` set('Y', 'N') NOT NULL default 'Y' ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrwgkh');
		$sql = "UPDATE subscriptions SET `can_view_blocked` = 'Y' ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrwgkh');
		$upgrade_needed = true;
	}

	if (!does_field_exist('subscription_invoices', 'can_view_blocked')) {
		$sql = "ALTER TABLE subscription_invoices ADD `can_view_blocked` set('Y', 'N') NOT NULL default 'Y' ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrwgkh');
		$upgrade_needed = true;
	}
	if (!does_field_exist('categories', 'list_order')) {
		$sql = "ALTER TABLE categories ADD `list_order` smallint(6) NOT NULL default '1' ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrwgkh');
		$upgrade_needed = true;
	}

	if (!does_field_exist('categories', 'search_set')) {
		$sql = "ALTER TABLE categories ADD `search_set` text NOT NULL  ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrhjhwgkh');
		$upgrade_needed = true;
	}

	if (!does_field_exist('mail_queue', 'mail_id')) {
		$sql = "CREATE TABLE `mail_queue` (
  `mail_id` int(11) NOT NULL auto_increment,
  `mail_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `to_address` varchar(128) NOT NULL default '',
  `to_name` varchar(128) NOT NULL default '',
  `from_address` varchar(128) NOT NULL default '',
  `from_name` varchar(128) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `message` text NOT NULL,
  `html_message` text NOT NULL,
  `attachments` set('Y','N') NOT NULL default '',
  `status` set('queued','sent','error') NOT NULL default '',
  `error_msg` varchar(255) NOT NULL default '',
  `retry_count` smallint(6) NOT NULL default '0',
  `template_id` int(11) NOT NULL default '0',
  `att1_name` varchar(128) NOT NULL default '',
  `att2_name` varchar(128) NOT NULL default '',
  `att3_name` varchar(128) NOT NULL default '',
  `date_stamp` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`mail_id`)) ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrjhhjhwgkh');
		$upgrade_needed = true;

		$sql = "INSERT INTO `email_templates` (`EmailID`,  `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`) VALUES (60,  'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Bank Deposit\r\n--------------------------.\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n        Bank Address: %BANK_ADDRESS%\r\n        SWIFT CODE: %BANK_AC_SWIFT%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\ntest@example.com with the following \r\nOrder Number: %INVOICE_CODE% to help us speed up the process. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'test@example.com', 'Order confirmed', 'Jamit Demo')";

		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrjhhjhwgkh');


		$sql ="INSERT INTO `email_templates` (`EmailID`,  `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`) VALUES (61,  'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n--------------------------.\r\n\r\nPlease send Check / Money Order to:\r\n	Name: %PAYEE_NAME%\r\n        Address: %PAYEE_ADDRESS%\r\n        Amount: %INVOICE_AMOUNT%\r\n        Currency: %CHECK_CURRENCY%\r\n\r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'test@example.com', 'Confirmed Order', 'Jamit Demo')";

		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrjhhjhwgkh');


		$sql = "INSERT INTO `email_templates` (`EmailID`,  `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`) VALUES (62,  'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Instant (%PAYMENT_METHOD%)\r\n\r\n--------------------------\r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'test@example.com', 'Order Confirmed', 'Jamit Demo')";

		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrjhhjhwgkh');

		$sql = "INSERT INTO `email_templates` (`EmailID`,  `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`) VALUES (63,  'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Instant (%PAYMENT_METHOD%)\r\n\r\n--------------------------\r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!', 'test@example.com', 'Order Completed', 'Jamit Demo')";

		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrjhhjhwgkh');

		$sql = "INSERT INTO `email_template_translations` (`EmailID`, `lang`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`) VALUES (60, 'EN', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Bank Deposit\r\n--------------------------.\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n        Bank Address: %BANK_ADDRESS%\r\n        SWIFT CODE: %BANK_AC_SWIFT%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\ntest@example.com with the following \r\nOrder Number: %INVOICE_CODE% to help us speed up the process. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'test@example.com', 'Order confirmed', 'Jamit Demo')";

		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrjhhjhwgkh');

		$sql = "INSERT INTO `email_template_translations` (`EmailID`, `lang`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`) VALUES (61, 'EN', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n--------------------------.\r\n\r\nPlease send Check / Money Order to:\r\n	Name: %PAYEE_NAME%\r\n        Address: %PAYEE_ADDRESS%\r\n        Amount: %INVOICE_AMOUNT%\r\n        Currency: %CHECK_CURRENCY%\r\n\r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'test@example.com', 'Confirmed Order', 'Jamit Demo')";

		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrjhhjhwgkh');

		$sql = "INSERT INTO `email_template_translations` (`EmailID`, `lang`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`) VALUES (62, 'EN', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Instant (%PAYMENT_METHOD%)\r\n\r\n--------------------------\r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'test@example.com', 'Order Confirmed', 'Jamit Demo')";

		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrjhhjhwgkh');

		$sql = "INSERT INTO `email_template_translations` (`EmailID`, `lang`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`) VALUES (63, 'EN', 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Instant (%PAYMENT_METHOD%)\r\n\r\n--------------------------\r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!', 'test@example.com', 'Order Completed', 'Jamit Demo')";

		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrjhhjhwgkh');


	}

	if (!does_field_exist('users', 'alert_query')) {
		$sql = "ALTER TABLE users ADD  `alert_query` TEXT NOT NULL  ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrhjhwgkh');
		$upgrade_needed = true;
	}

	if (!does_field_exist('employers', 'alert_query')) {
		$sql = "ALTER TABLE employers ADD `alert_query` TEXT NOT NULL ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'rr4456f');
		$upgrade_needed = true;
	}

	//

	if (!does_field_exist('jb_variables', 'key')) {
		$sql = "CREATE TABLE `jb_variables` (
		`key` VARCHAR( 255 ) NOT NULL ,
		`val` VARCHAR( 255 ) NOT NULL ,
		PRIMARY KEY ( `key` ) 
		)";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'grrvvvg');
		$upgrade_needed = true;
	}

	if ($flag) {

		$sql = "SELECT * FROM `jb_variables` WHERE `key`='HOUSEKEEP_RUNNING' ";
		$res = JB_mysql_query($sql);
		$row = mysql_fetch_array($res);
		if ($row['val']=='') {
			$sql = "REPLACE INTO `jb_variables` (`key`, `val`) VALUES ('HOUSEKEEP_RUNNING', 'NO') ";
			if ($flag) JB_mysql_query($sql);
			
		}
	}

	if ($flag) {

		$sql = "SELECT * FROM `jb_variables` WHERE `key`='MAIL_QUEUE_RUNNING' ";
		$res = JB_mysql_query($sql);
		$row = mysql_fetch_array($res);
		if ($row['val']=='') {
			$sql = "REPLACE INTO `jb_variables` (`key`, `val`) VALUES ('MAIL_QUEUE_RUNNING', 'NO') ";
			if ($flag) JB_mysql_query($sql);
			
		}
	}

	//

	if (!does_field_exist('jb_sessions', 'session_id')) {
		$sql = "CREATE TABLE `jb_sessions` (
		`session_id` VARCHAR( 255 ) NOT NULL ,
		`last_request_time` datetime NOT NULL default '0000-00-00 00:00:00',
		`domain` SET( 'EMPLOYER', 'CANDIDATE' ) NOT NULL ,
        `id` INT NOT NULL,
		`remote_addr` VARCHAR( 255 ) NOT NULL ,
		PRIMARY KEY ( `session_id` ) 
		)";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hwrhjhwgkh');

		


		$upgrade_needed = true;
	}

	if (!does_field_exist('jb_sessions', 'user_agent')) {
		$sql = "ALTER TABLE `jb_sessions` ADD `user_agent` varchar(255) NOT NULL ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4olfhma');

		$upgrade_needed = true;
	}

	// http_referer

	if (!does_field_exist('jb_sessions', 'http_referer')) {
		$sql = "ALTER TABLE `jb_sessions` ADD `http_referer` varchar(255) NOT NULL ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hdslfhfda');

		$upgrade_needed = true;
	}



	if (!does_field_exist('form_lists', 'column_id')) {
		$sql = "CREATE TABLE `form_lists` (
		  `form_id` int(11) NOT NULL default '0',
		  `field_type` varchar(255) NOT NULL default '',
		  `sort_order` int(11) NOT NULL default 0,
		  `field_id` varchar(255) NOT NULL default '0',
		  `template_tag` varchar(255) NOT NULL default '',
		  `column_id` int(11) NOT NULL auto_increment,
		  `linked` set('Y','N') NOT NULL default 'N',
		  `admin` set('Y','N') NOT NULL default 'N',
		  `truncate_length` SMALLINT( 4 ) NOT NULL default '0',
		  `clean_format` set('Y','N') NOT NULL default '',
		  `is_bold` set('Y','N') NOT NULL default 'N',
		  `no_wrap` set('Y','N') NOT NULL default 'N',
		  PRIMARY KEY  (`column_id`)
		)";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4olfhma');

		$sql = "INSERT INTO `form_lists` VALUES (1, 'TIME', '1', 'post_date', 'DATE', 13, 'N', 0, 'N', 'N', 'N', 'Y')";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4olfhma');
		$sql = "INSERT INTO `form_lists` VALUES (1, 'TEXT', '2', 'summary', 'POST_SUMMARY', 14, 'N', 0, 'N', 'N', 'N', '')";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4olfhma');
		$sql = "INSERT INTO `form_lists` VALUES (1, 'TEXT', '3', '15', 'LOCATION', 15, 'N', 0, 'N', 'Y', 'Y', 'N')";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4olfhma');
		$sql = "INSERT INTO `form_lists` VALUES (1, 'TEXT', '4', 'hits', 'HITS', 16, 'Y', 0, 'N', 'N', '', '')";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4olfhma');

		$sql = "INSERT INTO `form_lists` VALUES (2, 'TEXT', '2', '36', 'RESUME_NAME', 5, 'Y', 0, 'N', '', '', '')";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4olfhma');
		$sql = "INSERT INTO `form_lists` VALUES (2, 'TIME', '1', 'resume_date', 'DATE', 6, '', 0, 'N', '', '', '')";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4olfhma');
		$sql = "INSERT INTO `form_lists` VALUES (2, 'DATE', '3', '54', 'RESUME_COL3', 7, 'N', 0, 'Y', '', '', '')";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4olfhma');
		$sql = "INSERT INTO `form_lists` VALUES (2, 'TEXT', '4', '39', 'RESUME_COL4', 8, 'N', 0, 'N', 'N', 'N', 'N')";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4olfhma');

		$upgrade_needed = true;
	} else { // fix column type bug...
		$sql = "SHOW columns from `form_lists`";
		$result = JB_mysql_query($sql);
		while ($row = mysql_fetch_row($result)) {
			if (($row[0] == 'sort_order') && ($row[1]=='varchar(255)')) {
				$sql = "ALTER TABLE `form_lists` CHANGE `sort_order` `sort_order` INT( 11 ) NOT NULL";
				JB_mysql_query($sql);
			}

		}


	}

	if (!does_field_exist('form_lists', 'is_sortable')) {
		$sql = "ALTER TABLE `form_lists` ADD `is_sortable` set('Y','N') NOT NULL default 'N' ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4olfhma');

		$upgrade_needed = true;
	}

	if (!does_field_exist('email_templates', 'sub_template')) {
		$sql = "ALTER TABLE `email_templates` ADD `sub_template` TEXT NOT NULL ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4olfhma');

		$upgrade_needed = true;
	}

	if (!does_field_exist('saved_jobs', 'post_id')) {

		$sql = "CREATE TABLE `saved_jobs` (
		  `post_id` int(11) NOT NULL default '0',
		  `user_id` int(11) NOT NULL default '0',
		  `save_date` datetime NOT NULL default '0000-00-00 00:00:00',
		  PRIMARY KEY  (`post_id`,`user_id`))";

		  if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4sjwfhma');
		  $upgrade_needed = true;

	}

	if (!does_field_exist('email_template_translations', 'sub_template')) {

		$sql = "ALTER TABLE `email_template_translations` ADD `sub_template` TEXT NOT NULL ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'fdsf');

		### populate sub_template ##

		$sql = "UPDATE `email_templates` SET `sub_template`='%DATE% : %RESUME_NAME% (%NATIONALITY%)' WHERE EmailID=5 ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4olfhma');

		$sql = "UPDATE `email_template_translations` SET `sub_template`='%DATE% : %RESUME_NAME% (%NATIONALITY%)' WHERE EmailID=5 ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4olfhma');

		$sql = "UPDATE `email_templates` SET `sub_template`='<font face=''arial'' size=''2''>%DATE% - %RESUME_NAME% </font>' WHERE EmailID=6 ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4olfhma');

		$sql = "UPDATE `email_template_translations` SET `sub_template`='<font face=''arial'' size=''2''>%DATE% - %RESUME_NAME% </font>' WHERE EmailID=6 ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4olfhma');

		
		$sql = "UPDATE `email_templates` SET `sub_template`='%FORMATTED_DATE% : %TITLE% (%LOCATION%)
Link: %BASE_HTTP_PATH%index.php?post_id=%POST_ID%' WHERE EmailID=7 ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4oglfghma');

		$sql = "UPDATE `email_template_translations` SET `sub_template`='%FORMATTED_DATE% : %TITLE% (%LOCATION%)
Link: %BASE_HTTP_PATH%index.php?post_id=%POST_ID%' WHERE EmailID=7 ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4oglfghma');


		$sql = "UPDATE `email_templates` SET `sub_template`='<font face=''arial'' size=''2''>%FORMATTED_DATE% - <a href=''%BASE_HTTP_PATH%index.php?post_id=%POST_ID%''>%TITLE%</a></font> (%LOCATION%) <font face=''arial'' size=''1'' color=''#808080''>%DESCRIPTION%</font>' WHERE EmailID=8 ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4wwolfhma');

		$sql = "UPDATE `email_template_translations` SET `sub_template`='<font face=''arial'' size=''2''>%FORMATTED_DATE% - <a href=''%BASE_HTTP_PATH%index.php?post_id=%POST_ID%''>%TITLE%</a></font> (%LOCATION%) <font face=''arial'' size=''1'' color=''#808080''>%DESCRIPTION%</font>' WHERE EmailID=8 ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'hd4wwolfhma');


		$upgrade_needed = true;
	}

	

	if (!does_field_exist('package_invoices', 'reason')) {
		$sql = "ALTER TABLE `package_invoices` ADD `reason` VARCHAR( 128 ) NOT NULL";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'ggguufdds');
		$upgrade_needed = true;
	}

	if (!does_field_exist('subscription_invoices', 'reason')) {
		$sql = "ALTER TABLE `subscription_invoices` ADD `reason` VARCHAR( 128 ) NOT NULL ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'nhhg');
		$upgrade_needed = true;
	}


	if (does_field_exist('employers', 'alert_modify_date')) {
		$sql = "ALTER TABLE `employers` DROP `alert_modify_date` ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'jhjhg5s');
		$upgrade_needed = true;
	}


	if (does_field_exist('employers', 'alert_modify_date')) {
		$sql = "ALTER TABLE `employers` DROP `alert_modify_date` ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'jhjhg5s');
		$upgrade_needed = true;
	}

	if (does_field_exist('users', 'alert_modify_date')) {
		$sql = "ALTER TABLE `users` DROP `alert_modify_date` ";
		if ($flag) JB_mysql_query($sql) or die (mysql_error().'dffdcvvv');
		$upgrade_needed = true;


		// amd why not modify form_lists while we are at it...

		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (4, 'TEXT', 1, 'login_count', 'LCOUNT', 17, 'N', 0, '', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc1');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (4, 'TEXT', 2, 'Name', 'NAME', 18, 'N', 0, '', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc2');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (4, 'TEXT', 3, 'Username', 'USERNAME', 19, 'N', 0, '', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc3');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (4, 'TEXT', 4, 'Email', 'EMAIL', 20, 'N', 0, '', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc4');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (4, 'TEXT', 5, 'CompName', 'CNAME', 21, 'N', 0, 'Y,N', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc5');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (4, 'TEXT', 6, 'posts', 'POSTS', 22, 'N', 0, '', 'N', 'N', 'N', 'N');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc6');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (4, 'TEXT', 7, 'Newsletter', 'NEWS', 23, 'N', 0, '', 'N', 'N', 'N', 'N');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc7');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (4, 'TEXT', 8, 'Notification1', 'ALERTS', 24, 'N', 0, '', 'N', 'N', 'N', 'N');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc8');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (4, 'TIME', 9, 'SignupDate', 'DATE', 25, 'N', 0, '', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc9');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (4, 'TEXT', 10, 'IP', 'IP', 26, 'N', 0, '', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdcnn');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (5, 'TEXT', 1, 'login_count', 'LCOUNT', 27, 'N', 0, '', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc99');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (5, 'TEXT', 2, 'Name', 'NAME', 28, 'N', 0, '', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc88');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (5, 'TEXT', 3, 'Username', 'USERNAME', 29, 'N', 0, '', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc66');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (5, 'TEXT', 4, 'Email', 'EMAIL', 30, 'N', 0, '', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc77');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (5, 'TEXT', 5, 'Newsletter', 'NEWS', 31, 'N', 0, '', 'N', 'N', 'N', 'N');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc44');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (5, 'TEXT', 6, 'Notification1', 'ALERTS', 32, 'N', 0, '', 'N', 'N', 'N', 'N');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc55');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (5, 'TEXT', 7, 'resume_id', 'RESUME_ID', 33, 'N', 0, '', 'N', 'N', 'N', 'N');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc44');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (5, 'TIME', 8, 'SignupDate', 'DATE', 34, 'N', 0, '', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc22');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (5, 'TEXT', 9, 'IP', 'IP', 35, 'N', 0, '', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc33');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (3, 'TEXT', 1, '65', 'PROFILE_BNAME', 37, 'Y', 0, '', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc22');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (3, 'RADIO', 2, '67', 'PROFILE_BTYPE', 38, 'N', 0, '', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdc11');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (3, 'TEXT', 3, '72', 'PROFILE_CNAME', 39, 'N', 0, '', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffdcff');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (3, 'TEXT', 4, '83', 'PROFILE_COUNTRY', 40, 'N', 0, '', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffffdc');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (3, 'TEXT', 5, '75', 'PROFILE_EMAIL', 41, 'N', 0, '', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dffggdc');
		$sql = "INSERT INTO `form_lists` (`form_id`, `field_type`, `sort_order`, `field_id`, `template_tag`, `column_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `is_sortable`, `no_wrap`) VALUES (3, 'TEXT', 6, '74', 'PROFILE_WEBURL', 42, 'N', 0, '', 'N', 'N', 'N', 'Y');";
		if ($flag) JB_mysql_query($sql) or  (mysql_error().'dfhffdc');

		/// fix template tags with the colums (only needed when upgrading)
		// simply copy over the template tags from form_fields to form_lists.

		if ($flag) {

		$result1 = jb_mysql_query("SELECT * FROM form_lists");

		while ($row=mysql_fetch_array($result1)) {
			$result2 = jb_mysql_query ("SELECT template_tag FROM form_fields WHERE field_id='".$row['field_id']."'");
			$row2 = mysql_fetch_array($result2);
			if ($row2['template_tag']!='') {
				// copy from form_fields to form_lists
				jb_mysql_query ("UPDATE form_lists SET template_tag='".$row2['template_tag']."' WHERE field_id='".$row['field_id']."' " );
			} else {
				// copy from form_lists to form_fields
				jb_mysql_query ("UPDATE form_fields SET template_tag='".$row['template_tag']."'  WHERE field_id='".$row['field_id']."'");
			}
		}

		}


	}

	if (!does_field_exist("jb_config", "key")) {

		$sql = "CREATE TABLE `jb_config` (
		`key` VARCHAR( 255 ) NOT NULL ,
		`val` VARCHAR( 255 ) NOT NULL ,
		PRIMARY KEY ( `key` ) 
		)";
		//$sql = "ALTER TABLE `blocks` ADD `published` SET( 'Y', 'N') NOT NULL ";
		 if ($flag) JB_mysql_query($sql) or die ("<p><b>CANNOT UPGRADE YOUR DATABASE!<br>Please run the follwoing query manually from PhpMyAdmin:</b><br><pre>$sql</pre><br>");

		 $upgrade_needed = true;

		 if ($flag) {

			 if (MULTI_PAY_PAYPAL_ON=='YES') {
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_ENABLED', 'Y')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_EMAIL', '".PAYPAL_EMAIL."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_CURRENCY', '".PAYPAL_CURRENCY."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_BUTTON_URL', '".PAYPAL_BUTTON_URL."')";

				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_SUBSCR_BUTTON_URL', 'https://www.paypal.com/en_US/i/btn/x-click-butcc-subscribe.gif')";

				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_RETURN_URL', '')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_IPN_URL', '')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_CANCEL_RETURN_URL', '')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_PAGE_STYLE', '".PAYPAL_PAGE_STYLE."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('PAYPAL_SERVER', '".PAYPAL_SERVER."')";
			 }

			 if (MULTI_PAY_BANK_ON=='YES') {

				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_ENABLED', 'Y')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_CURRENCY', '".BANK_CURRENCY."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_NAME', '".BANK_NAME."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_ADDRESS', '".BANK_ADDRESS."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_ACCOUNT_NAME', '".BANK_ACCOUNT_NAME."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_ACCOUNT_NUMBER', '".BANK_ACCOUNT_NUMBER."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_BRANCH_NUMBER', '".BANK_BRANCH_NUMBER."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_SWIFT', '".BANK_SWIFT."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('BANK_EMAIL_CONFIRM', '".BANK_EMAIL_CONFIRM."')";
				JB_mysql_query($sql);

			 }

			 if (MULTI_PAY_CHECK_ON=='YES') {

				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_ENABLED', 'Y')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_CURRENCY', '".CHECK_CURRENCY."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_PAYABLE', '".CHECK_PAYABLE."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_ADDRESS', '".CHECK_ADDRESS."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('CHECK_EMAIL_CONFIRM', '".CHECK_EMAIL_CONFIRM."')";
				JB_mysql_query($sql);

			 }

			 if (MULTI_PAY_NOCHEX_ON=='YES') {
				 
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_EMAIL', '".NOCHEX_EMAIL."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_ENABLED', 'Y')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_LOGO_URL', '".NOCHEX_LOGO_URL."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_CANCEL_RETURN_URL', '".NOCHEX_CANCEL_RETURN_URL."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_RETURN_URL', '')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_APC_URL', '')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_BUTTON_URL', '".NOCHEX_BUTTON_URL."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('NOCHEX_CURRENCY', '".NOCHEX_CURRENCY."')";
				JB_mysql_query($sql);

			 }

			 if (MULTI_PAY_2CO_ON=='YES') {
				 $sql = "REPLACE INTO jb_config (`key`, val) VALUES ('_2CO_ENABLED', 'Y')";
				JB_mysql_query($sql);

				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('_2CO_SID', '"._2CO_SID."')";
				JB_mysql_query($sql);
				//$sql = "REPLACE INTO jb_config (`key`, val, descr) VALUES ('_2CO_PRODUCT_ID', '1', '# Your 2CO seller ID number.')";
				//JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('_2CO_DEMO', '"._2CO_DEMO."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('_2CO_SECRET_WORD', '"._2CO_SECRET_WORD."')";
				JB_mysql_query($sql);
				$sql = "REPLACE INTO jb_config (`key`, val) VALUES ('_2CO_PAYMENT_ROUTINE', '"._2CO_PAYMENT_ROUTINE."')";
				JB_mysql_query($sql);

			 }


		 }


	}

	if (!does_field_exist("jb_txn", "transaction_id")) {

		$sql ="CREATE TABLE `jb_txn` (
		`transaction_id` int(11) NOT NULL auto_increment,
		`date` datetime NOT NULL default '0000-00-00 00:00:00',
		`invoice_id` int(11) NOT NULL default '0',
		`type` varchar(32) NOT NULL default '',
		`amount` float NOT NULL default '0',
		`currency` char(3) NOT NULL default '',
		`txn_id` varchar(128) NOT NULL default '',
		`reason` varchar(64) NOT NULL default '',
		`origin` varchar(32) NOT NULL default '',
		`product_type` char(1) NOT NULL default 'P',
		PRIMARY KEY  (`transaction_id`))";

		if ($flag) JB_mysql_query($sql) or die ("<p><b>CANNOT UPGRADE YOUR DATABASE!<br>Please run the follwoing query manually from PhpMyAdmin:</b><br><pre>$sql</pre><br>");

		 $upgrade_needed = true;
	}

	if (!does_field_exist("jb_txn", "reference")) {
		$sql ="ALTER TABLE jb_txn ADD `reference` VARCHAR( 128 ) NOT NULL  default ''";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("employers", "can_view_blocked")) {
		$sql ="ALTER TABLE employers ADD `can_view_blocked` SET( 'Y', 'N' ) NOT NULL default 'N'";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("posts_table", "guid")) {
		$sql ="ALTER TABLE posts_table ADD `guid` VARCHAR(255) NOT NULL default ''";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("posts_table", "cached_summary")) {
		$sql ="ALTER TABLE posts_table ADD `cached_summary` TEXT NOT NULL ";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("posts_table", "source")) {
		$sql ="ALTER TABLE posts_table ADD `source` VARCHAR(255) NOT NULL default ''";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("lang", "fckeditor_lang")) {
		$sql ="ALTER TABLE lang ADD `fckeditor_lang` VARCHAR(10) NOT NULL default 'en.js'";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

/*
	if (!does_field_exist("lang", "direction")) {
		$sql ="ALTER TABLE lang ADD `direction` CHAR(2) NOT NULL default 'LR'";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}
*/

	// 

	if (!does_field_exist("motd", "motd_type")) {

		$sql = "CREATE TABLE `motd` (
		`motd_type` CHAR( 2 ) NOT NULL ,
		`motd_lang` CHAR( 2 ) NOT NULL ,
		`motd_message` TEXT NOT NULL,
		`motd_title` TEXT NOT NULL,
		`motd_date_updated` datetime NOT NULL,
		PRIMARY KEY ( `motd_type` , `motd_lang` ) )";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;

	}

	if (!does_field_exist("help_pages", "help_type")) {

		$sql = "CREATE TABLE `help_pages` (
		`help_type` CHAR( 2 ) NOT NULL ,
		`help_lang` CHAR( 2 ) NOT NULL ,
		`help_message` TEXT NOT NULL,
		`help_title` TEXT NOT NULL,
		`help_date_updated` datetime NOT NULL,
		PRIMARY KEY ( `help_type` , `help_lang` ) )";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;

	}


	if (!does_field_exist("payment_log", "date")) {

		$sql = "
		CREATE TABLE `payment_log` (
		  `seq_no` int(11) NOT NULL auto_increment,
		  `date` datetime NOT NULL default '0000-00-00 00:00:00',
		  `module` varchar(128) NOT NULL default '',
		  `log_entry` text NOT NULL,
		  PRIMARY KEY  (`seq_no`)
		) ";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;

	}

	if (!does_field_exist("memberships", "membership_id")) {

		$sql = "
		CREATE TABLE `memberships` (
		`membership_id` INT NOT NULL AUTO_INCREMENT ,
		`name` VARCHAR( 255 ) NOT NULL ,
		`price` FLOAT NOT NULL ,
		`currency_code` VARCHAR( 3 ) NOT NULL ,
		`months` MEDIUMINT NOT NULL ,
		PRIMARY KEY ( `membership_id` ),
		`type` SET( 'E', 'C' ) NOT NULL
		) ";

		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;

	}

	if (!does_field_exist("membership_invoices", "membership_id")) {

		$sql = "CREATE TABLE `membership_invoices` (
		`invoice_id` INT NOT NULL AUTO_INCREMENT ,
		`invoice_date` DATETIME NOT NULL ,
		`processed_date` DATETIME NULL ,
		`status` VARCHAR( 127 ) NOT NULL ,
		`user_type` SET( 'E', 'C' ) NOT NULL ,
		`user_id` INT NOT NULL ,
		`membership_id` INT NOT NULL ,
		`months_duration` MEDIUMINT NOT NULL ,
		`amount` FLOAT NOT NULL ,
		`currency_code` VARCHAR( 3 ) NOT NULL ,
		`currency_rate` DECIMAL( 10, 4 ) NOT NULL ,
		`item_name` VARCHAR( 255 ) NOT NULL ,
		`member_date` DATETIME NOT NULL ,
		`member_end` DATETIME NOT NULL ,
		`payment_method` VARCHAR( 64 ) NOT NULL ,
		`reason` VARCHAR( 127 ) NOT NULL ,
		PRIMARY KEY ( `invoice_id` ) 
		); ";

		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;

		$sql = "ALTER TABLE `posts_table` CHANGE `post_id` `post_id` INT( 11 ) NOT NULL AUTO_INCREMENT";  
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());

	}

	if (!does_field_exist("users", "membership_active")) {
		$sql ="ALTER TABLE users ADD `membership_active` CHAR(1) NOT NULL default 'N'";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("employers", "membership_active")) {
		$sql ="ALTER TABLE employers ADD `membership_active` CHAR(1) NOT NULL default 'N'";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;

		// while at it, update the primary key on the skill matrix

		$sql = "ALTER TABLE `skill_matrix_data` DROP PRIMARY KEY , ADD PRIMARY KEY ( `field_id` , `row` , `user_id` ) ";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());

		// update some thing with field_init

		


	}

	if (!does_field_exist("posts_table", "expired")) {
		$sql ="ALTER TABLE `posts_table` ADD `expired` SET ('Y','N') NOT NULL default 'N' ;";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("resumes_table", "expired")) {
		$sql ="ALTER TABLE `resumes_table` ADD `expired` SET ('Y','N') NOT NULL default 'N' ;";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("profiles_table", "expired")) {
		$sql ="ALTER TABLE `profiles_table` ADD `expired` SET ('Y','N') NOT NULL default 'N' ;";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("employers", "expired")) {
		$sql ="ALTER TABLE `employers` ADD `expired` SET ('Y','N') NOT NULL default 'N' ;";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("users", "expired")) {
		$sql ="ALTER TABLE `users` ADD `expired` SET ('Y','N') NOT NULL default 'N' ;";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("mail_monitor_log", "log_id")) {
		$sql = "CREATE TABLE `mail_monitor_log` (
		`log_id` INT NOT NULL AUTO_INCREMENT ,
		`date` DATETIME NOT NULL ,
		`email` VARCHAR(255) NOT NULL ,
		`user_type` SET( 'E', 'C' ) NOT NULL ,
		PRIMARY KEY ( `log_id` ))";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
		
	}


	

	

	if (!does_field_exist("xml_export_elements", "has_child")) {
		$sql ="ALTER TABLE `xml_export_elements` ADD `has_child` SET( 'Y', 'N' ) DEFAULT NULL ;";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		if ($flag) { 
			include_once ('../include/xml_feed_functions.php');
			JB_compute_export_elements_has_child();
		}
		
		$upgrade_needed = true;
	}

	if (!does_field_exist("xml_export_feeds", "form_id")) {
		$sql = "DROP TABLE IF EXISTS `xml_export_feeds`";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());

		$sql = "
		CREATE TABLE `xml_export_feeds` (
		`feed_id` int(11) NOT NULL auto_increment,
		`feed_name` varchar(255) NOT NULL default '',
		`description` text NOT NULL,
		`field_settings` text NOT NULL,
		`search_settings` text NOT NULL,
		`max_records` int(11) NOT NULL default '0',
		`publish_mode` set('PUB','PRI') NOT NULL default '',
		`schema_id` int(11) NOT NULL default '0',
		`feed_key` varchar(255) NOT NULL default '',
		`hosts_allow` text NOT NULL,
		`is_locked` set('Y','N') NOT NULL default 'N',
		`form_id` int(11) NOT NULL default '0',
		PRIMARY KEY  (`feed_id`)
		)";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());

		$sql ="INSERT INTO `xml_export_feeds` VALUES (6, 'RSS Feed (Example)', 'this is a description', 'a:5:{i:147;s:1:\"2\";s:6:\"ft_147\";s:4:\"TEXT\";i:149;s:1:\"5\";s:6:\"ft_149\";s:6:\"EDITOR\";i:151;s:9:\"post_date\";}', 'a:4:{i:6;N;i:13;N;i:5;s:0:\"\";i:14;s:0:\"\";}', 50, 'PUB', 3, '', 'localhost', 'N', 1)";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());

		$sql ="INSERT INTO `xml_export_feeds` VALUES (9, 'Simply Hired Feed (Example)', 'Simply Hired - Jobs', 'a:49:{i:44;s:1:\"2\";s:5:\"ft_44\";s:4:\"TEXT\";i:45;s:7:\"post_id\";i:46;s:0:\"\";i:52;s:7:\"summary\";i:53;s:0:\"\";i:54;s:0:\"\";i:55;s:0:\"\";i:56;s:2:\"14\";s:12:\"boolean_p_56\";s:9:\"full-time\";s:5:\"ft_56\";s:8:\"CATEGORY\";i:57;s:1:\"2\";s:12:\"boolean_p_57\";s:9:\"part-time\";s:5:\"ft_57\";s:4:\"TEXT\";i:59;s:0:\"\";i:60;s:0:\"\";i:61;s:0:\"\";i:62;s:0:\"\";i:63;s:0:\"\";i:64;s:0:\"\";i:65;s:0:\"\";i:66;s:0:\"\";i:68;s:0:\"\";i:69;s:0:\"\";i:70;s:0:\"\";i:71;s:0:\"\";i:73;s:0:\"\";i:75;s:2:\"13\";s:5:\"ft_75\";s:8:\"CATEGORY\";i:76;s:0:\"\";i:77;s:0:\"\";i:78;s:0:\"\";i:79;s:0:\"\";i:80;s:0:\"\";i:82;s:1:\"8\";s:5:\"ft_82\";s:4:\"TEXT\";i:84;s:2:\"12\";s:5:\"ft_84\";s:4:\"TEXT\";i:85;s:0:\"\";i:86;s:0:\"\";i:87;s:0:\"\";i:88;s:0:\"\";i:90;s:1:\"8\";s:5:\"ft_90\";s:4:\"TEXT\";i:91;s:0:\"\";i:92;s:0:\"\";i:93;s:0:\"\";i:127;s:1:\"6\";s:6:\"ft_127\";s:8:\"CATEGORY\";}', 'a:4:{i:6;N;i:13;N;i:5;s:0:\"\";i:14;s:0:\"\";}', 50, 'PUB', 4, '', 'localhost', 'N', 1)";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());

		$sql = "INSERT INTO `xml_export_feeds` VALUES (10, 'Indeed Jobs Feed (Example)', 'My jobs feed to indeed!', 'a:20:{i:112;s:1:\"2\";s:6:\"ft_112\";s:4:\"TEXT\";i:113;s:9:\"post_date\";i:114;s:7:\"post_id\";i:116;s:1:\"8\";s:6:\"ft_116\";s:4:\"TEXT\";i:117;s:2:\"15\";s:6:\"ft_117\";s:4:\"TEXT\";i:118;s:0:\"\";i:119;s:0:\"\";i:120;s:0:\"\";i:121;s:1:\"5\";s:6:\"ft_121\";s:6:\"EDITOR\";i:122;s:0:\"\";i:123;s:0:\"\";i:124;s:2:\"14\";s:6:\"ft_124\";s:8:\"CATEGORY\";i:125;s:1:\"6\";s:6:\"ft_125\";s:8:\"CATEGORY\";i:126;s:0:\"\";}', 'a:4:{i:6;N;i:13;N;i:5;s:0:\"\";i:14;s:0:\"\";}', 50, 'PUB', 2, '', 'localhost', 'N', 1)";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());

		$sql = "INSERT INTO `xml_export_feeds` VALUES (11, 'Google Base Feed (Example)', 'Google Base Feed', 'a:17:{i:14;s:0:\"\";i:19;s:2:\"13\";s:5:\"ft_19\";s:8:\"CATEGORY\";i:20;s:0:\"\";i:21;s:0:\"\";i:96;s:1:\"2\";s:5:\"ft_96\";s:4:\"TEXT\";i:97;s:1:\"5\";s:5:\"ft_97\";s:6:\"EDITOR\";i:98;s:1:\"6\";s:5:\"ft_98\";s:8:\"CATEGORY\";i:99;s:0:\"\";i:100;s:0:\"\";i:103;s:0:\"\";i:104;s:1:\"8\";s:6:\"ft_104\";s:4:\"TEXT\";i:106;s:0:\"\";}', 'a:4:{i:6;N;i:13;N;i:5;s:0:\"\";i:14;s:0:\"\";}', 50, 'PUB', 1, '', 'ALL', 'N', 1)";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());

		$upgrade_needed = true;
		
	}

	if (!does_field_exist("xml_export_schemas", "is_locked")) {
		$sql = "CREATE TABLE `xml_export_schemas` (
		  `schema_id` int(11) NOT NULL auto_increment,
		  `schema_name` varchar(255) NOT NULL default '',
		  `description` text NOT NULL,
		  `form_id` int(11) NOT NULL default '0',
		  `is_locked` set('Y','N') NOT NULL default 'N',
		  PRIMARY KEY  (`schema_id`)
		)";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());

		$upgrade_needed = true;
		
		$sql = "INSERT INTO `xml_export_schemas` VALUES (1, 'Google Base  - Jobs', 'For a full description of the attributes (elements) see: http://www.google.com/base/jobs.html', 1, 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql = "INSERT INTO `xml_export_schemas` VALUES (2, 'Indeed.com', 'http://www.indeed.com/jsp/xmlinfo.jsp', 1, 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql = "INSERT INTO `xml_export_schemas` VALUES (3, 'RSS', 'http://blogs.law.harvard.edu/tech/rss', 1, 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql = "INSERT INTO `xml_export_schemas` VALUES (4, 'SimplyHired.com', 'Simply Hired can accept incoming job feeds in either xml or delimited formats\r\nhttp://www.simplyhired.com/feed.php#feed_spec', 1, 'Y')";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());

	}

	if (!does_field_exist("xml_export_feeds", "is_locked")) {
		$upgrade_needed = true;
		$sql ="ALTER TABLE `xml_export_feeds` ADD `is_locked` SET ('Y','N') NOT NULL default 'N' ;";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="ALTER TABLE `xml_export_feeds` ADD `description` text NOT NULL";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="ALTER TABLE `xml_export_feeds` ADD `field_settings` text NOT NULL";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="ALTER TABLE `xml_export_feeds` ADD `search_settings` text NOT NULL";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="ALTER TABLE `xml_export_feeds` ADD `max_records` int(11) NOT NULL default '0'";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="ALTER TABLE `xml_export_feeds` ADD `publish_mode` set('PUB','PRI') NOT NULL default ''";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="ALTER TABLE `xml_export_feeds` ADD `schema_id` int(11) NOT NULL default '0'";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="ALTER TABLE `xml_export_feeds` ADD `feed_key` varchar(255) NOT NULL default ''";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$sql ="ALTER TABLE `xml_export_feeds` ADD `hosts_allow` text NOT NULL";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());

	}

	// fix up MSELECT

	$sql = "SELECT * FROM jb_variables WHERE `key`='MSELECT_FIXED2'  ";
	$result = @JB_mysql_query($sql);
	if ($row = mysql_num_rows($result)==0) {
		

		// fix MSELECT

		$sql = "SELECT * from form_fields WHERE field_type='RADIO' or field_type='MSELECT' ";
		$result = JB_mysql_query($sql);
		while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {
			/*
			// Uncomment if you are getting the following error:
			// BLOB/TEXT column '22' used in key specification without a key length
			// or similar error
			// remove old indexes on the fields
			/*
			$sql = "SHOW index FROM posts_table";
			$result = mysql_query($sql);
			while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

				
				if (strpos(strtolower($row['Key_name']), 'composite')!==false) {
					$sql ="ALTER TABLE posts_table DROP INDEX `".$row['Key_name']."`";
					mysql_query($sql) or die(mysql_error());

				}
				
			}

			*/

			$t_name = JB_get_table_name_by_id($row['form_id']);
			$sql = ' ALTER TABLE `'.$t_name.'` CHANGE `'.$row['field_id'].'` `'.$row['field_id'].'` TEXT NOT NULL  ';
			JB_mysql_query($sql) or die(mysql_error());
			//echo $sql."<br>";
		}

		// NOW FIX All the MSELECT records

		$sql = "SELECT * from form_fields WHERE field_type='MSELECT' ";
		$result = JB_mysql_query($sql);
		while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

			$table_name = JB_get_table_name_by_id($row['form_id']);
			$id_name = JB_get_table_id_column($row['form_id']);

			$sql = "SELECT `".$row['field_id']."`, $id_name FROM $table_name ";
			//echo $sql." (idname: $id_name)<br>";
			$res2 = JB_mysql_query($sql) or die(mysql_error());
			while ($row2=mysql_fetch_array($res2)) {
				$new_val = str_replace(' ','',$row2[$row['field_id']]); // remove spaces
				$new_val = preg_replace('#^,#', '', $new_val);
				$new_val = preg_replace('#,$#', '', $new_val);
				$new_arr = explode(',',$new_val);
				
 				$sql = "UPDATE $table_name SET `".$row['field_id']."`='".$new_val."' WHERE $id_name='".addslashes($row2[$id_name])."' ";
				JB_mysql_query($sql) or die (mysql_error());
				
			}

		}

		//Fixed MSELECT fields

		$sql = "REPLACE INTO jb_variables (`key`, `val`) VALUES ('MSELECT_FIXED2', 'YES') ";
		JB_mysql_query($sql) or die (mysql_error());
		
	}

	// rename %RESUME_ALERT% to %RESUME_ALERTS%
	

	$sql = "SELECT * FROM jb_variables WHERE `key`='RESUME_ALERT_RENAME_FIXED'  ";
	$result = JB_mysql_query($sql);
	if ($row = mysql_num_rows($result)==0) {

		$sql = "SELECT * from email_templates WHERE EmailText LIKE  '%RESUME_ALERT%' ";
		$result = JB_mysql_query($sql) or die (mysql_error());
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$row['EmailText'] = str_replace('%RESUME_ALERT%', '%RESUME_ALERTS%', $row['EmailText']);

			$sql = "UPDATE email_templates SET EmailText = '".addslashes($row['EmailText'])."' WHERE EmailID='".$row['EmailID']."' ";

		

			JB_mysql_query($sql);

		}

		$sql = "SELECT * from email_template_translations WHERE EmailText LIKE  '\%RESUME_ALERT\%' ";
		$result = JB_mysql_query($sql) or die (mysql_error());
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$row['EmailText'] = str_replace('%RESUME_ALERT%', '%RESUME_ALERTS%', $row['EmailText']);
			$sql = "UPDATE email_templates SET EmailText = '".addslashes($row['EmailText'])."' WHERE EmailID='".$row['EmailID']."' ";


			JB_mysql_query($sql);

		}

		$sql = "SELECT * from email_template_translations WHERE EmailText LIKE  '\%KEYWORDS_LINE\%' ";
		$result = JB_mysql_query($sql) or die (mysql_error());
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

			if (($row['EmailID']==5) || ($row['EmailID']==6)) {


				$row['EmailText'] = str_replace('%KEYWORDS_LINE%', '%RESUME_ALERTS%', $row['EmailText']);
				$sql = "UPDATE email_templates SET EmailText = '".addslashes($row['EmailText'])."' WHERE EmailID='".$row['EmailID']."' ";
				JB_mysql_query($sql);
			}

			if (($row['EmailID']==7) || ($row['EmailID']==8)) {


				$row['EmailText'] = str_replace('%KEYWORDS_LINE%', '%JOB_ALERTS%', $row['EmailText']);
				$sql = "UPDATE email_templates SET EmailText = '".addslashes($row['EmailText'])."' WHERE EmailID='".$row['EmailID']."' ";
				JB_mysql_query($sql);
			}

		}

		//echo 'Updated email templates<br>';

		$sql = "REPLACE INTO jb_variables (`key`, `val`) VALUES ('RESUME_ALERT_RENAME_FIXED', 'YES') ";
		JB_mysql_query($sql) or die (mysql_error());

	}
	


	// update CATOPTION_CACHE_UPDATE

	$sql = "SELECT * FROM jb_variables WHERE `key`='CATOPTION_CACHE_UPDATE'  ";
	$result = @JB_mysql_query($sql);
	if ($row = mysql_num_rows($result)==0) {

		if ($flag==true && (does_field_exist("categories", "seo_fname"))) {

			
			JB_cache_del_keys_for_form(1);
			JB_cache_del_keys_for_form(2);
			JB_cache_del_keys_for_form(3);
			JB_cache_del_keys_for_form(4);
			JB_cache_del_keys_for_form(5);

			JB_cache_del_keys_for_all_cats(1);
			JB_cache_del_keys_for_all_cats(2);
			JB_cache_del_keys_for_all_cats(3);
			JB_cache_del_keys_for_all_cats(4);
			JB_cache_del_keys_for_all_cats(5);
			JB_cache_del_keys_for_cat_options();

			echo "* Updated your category cache<br>";

			$sql = "REPLACE INTO jb_variables (`key`, `val`) VALUES ('CATOPTION_CACHE_UPDATE', 'YES') ";
			JB_mysql_query($sql) or die (mysql_error());
		}


	}

	// update CODESFIELD_CACHE_UPDATE

	$sql = "SELECT * FROM jb_variables WHERE `key`='CODESFIELD_CACHE_UPDATE'  ";
	$result = @JB_mysql_query($sql);
	if ($row = mysql_num_rows($result)==0) {

	

		if ((does_field_exist("categories", "seo_fname"))) {

			

			// here

			$sql = "select field_id from codes group by field_id ";
			$result2 = JB_mysql_query($sql) or die(mysql_error());
			while ($row2=mysql_fetch_array($result2, MYSQL_ASSOC)) {
				JB_cache_del_keys_for_codes($row2['field_id']);
			}

			echo "* Updated your codes cache<br>";
			$sql = "REPLACE INTO jb_variables (`key`, `val`) VALUES ('CODESFIELD_CACHE_UPDATE', 'YES') ";
			JB_mysql_query($sql) or die (mysql_error());
		}

	}


	if (!does_field_exist("xml_export_elements", "static_mod")) {
		$sql ="ALTER TABLE `xml_export_elements` ADD `static_mod` SET( 'A', 'P', 'F' ) DEFAULT 'F' NOT NULL ,
		ADD `multi_fields` SMALLINT DEFAULT '1' NOT NULL ";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("xml_export_elements", "comment")) {
		$sql ="ALTER TABLE `xml_export_elements` ADD `comment` VARCHAR( 255 ) DEFAULT '' NOT NULL  ";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("posts_table", "app_type")) {
		$sql ="ALTER TABLE `posts_table` ADD `app_type` CHAR( 1 ) NOT NULL DEFAULT 'O', ADD `app_url` VARCHAR( 255 ) NOT NULL ; ";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("subscriptions", "views_quota")) {
		$sql ="ALTER TABLE `subscriptions` ADD  `views_quota` INT NOT NULL DEFAULT '-1', ADD `p_posts_quota` INT NOT NULL DEFAULT '-1', ADD `posts_quota` INT NOT NULL DEFAULT '-1' ; ";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("subscription_invoices", "views_quota")) {
		$sql ="ALTER TABLE `subscription_invoices` ADD  `views_quota` INT NOT NULL DEFAULT '-1', ADD `p_posts_quota` INT NOT NULL DEFAULT '-1', ADD `posts_quota` INT NOT NULL DEFAULT '-1' ; ";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (!does_field_exist("employers", "views_quota")) {
		$sql ="ALTER TABLE `employers` ADD  `views_quota` INT NOT NULL DEFAULT '-1', ADD `p_posts_quota` INT NOT NULL DEFAULT '-1', ADD `posts_quota` INT NOT NULL DEFAULT '-1', ADD  `views_quota_tally` INT NOT NULL DEFAULT '0', ADD `p_posts_quota_tally` INT NOT NULL DEFAULT '0', ADD `posts_quota_tally` INT NOT NULL DEFAULT '0', ADD `quota_timestamp` INT NOT NULL DEFAULT '0' ; ";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;
	}

	if (does_field_exist("subscriptions", "subscr_date")) {

		$sql = "ALTER TABLE `subscriptions` DROP `subscr_date`, DROP `subscr_effective`, DROP `recurring`, DROP `subscr_id`;";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;

	}

	if (!does_field_exist("short_urls", "url")) {

		$sql = "CREATE TABLE `short_urls` (
		  `url` varchar(255) NOT NULL,
		  `date` timestamp NOT NULL,
		  `hash` varchar(255) NOT NULL,
		  `expires` set('Y','N') NOT NULL,
		  `hits` bigint(20) NOT NULL,
		  PRIMARY KEY (`url`)
		)";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;

	}

	if (!does_field_exist("mail_queue", "user_id")) {

		$sql = "ALTER TABLE `mail_queue` ADD `user_id` INT NULL DEFAULT NULL, ADD `user_type` VARCHAR( 10 ) NULL DEFAULT NULL;";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;

	}


	if (!does_field_exist("sitemaps_urls", "url")) {

		$sql = "CREATE TABLE `sitemaps_urls` (
				`url` TEXT NOT NULL ,
				`priority` FLOAT NOT NULL ,
				`changefreq` VARCHAR( 15 ) NOT NULL
				) ";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;

	}

	if (!does_field_exist("xml_export_feeds", "include_emp_accounts")) {

		$sql = "ALTER TABLE `xml_export_feeds` ADD `include_emp_accounts` SET( 'Y', 'N' ) NOT NULL DEFAULT 'N' ;";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;

	}
	if (!does_field_exist("xml_export_feeds", "export_with_url")) {

		$sql = "ALTER TABLE `xml_export_feeds` ADD `export_with_url` SET( 'Y', 'N' ) NOT NULL DEFAULT 'Y' ;";
		if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
		$upgrade_needed = true;

	}


	



	$sql = "SELECT * FROM jb_variables WHERE `key`='CODESFIELD_CACHE_UPDATE'  ";
	$result = @JB_mysql_query($sql);
	if ($row = mysql_num_rows($result)==0) {


	}

	

	 

	

	
	
	$sql = "UPDATE form_fields SET field_init='' WHERE field_id=2 AND field_init=5 ";
	JB_mysql_query($sql) or die ($sql.mysql_error());
	$sql = "UPDATE form_fields SET field_init='' WHERE field_id=5 AND field_init=5";
	JB_mysql_query($sql) or die ($sql.mysql_error());

	if ($flag) JB_fix_form_field_translations();

	$sql = "ALTER TABLE `applications` CHANGE `app_id` `app_id` INT( 11 ) NOT NULL AUTO_INCREMENT ";
	if ($flag) JB_mysql_query($sql) or die (mysql_error().'jsgd73cd');

	if (1==1) {

		$sql = "INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (330, 'A new order was placed on %SITE_NAME% by %USER%!\r\n\r\nTo manage, see here:\r\n%ADMIN_LINK%\r\n\r\n==================================\r\n\r\nOrder by: %LNAME%, %FNAME%\r\nUsername: %USER%\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nItem: %ITEM_NAME%\r\nOrder ID: #%INVOICE_CODE%\r\nPrice: %INVOICE_AMOUNT%\r\n', 'test@test.com', 'A New order was placed on %SITE_NAME%', 'Jamit Demo', '');";
		if (!JB_template_exists(330)) JB_mysql_query($sql);

		$sql = "INSERT INTO `email_templates` (`EmailID`,  `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (320,  'A Resume resume was posted to / updated on %SITE_NAME%\r\n\r\nAdmin Link: \r\n%ADMIN_LINK%\r\n\r\n%RESUME_SUMMARY%\r\n\r\n\r\n', 'example@example.com', 'A Resume was saved on %SITE_NAME%', 'Jamit Demo', '');";
		if (!JB_template_exists(320)) JB_mysql_query($sql);

		$sql ="INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (310, 'A new Post was posted to %SITE_NAME%\r\n\r\nAdmin Link: %ADMIN_LINK%\r\n\r\nTitle:\r\n%POST_TITLE%\r\nBy:\r\n%POSTED_BY%\r\nDate:\r\n%DATE%\r\nDescription:\r\n%POST_DESCRIPTION%\r\n', 'test@test.com', 'A new Post was posted to %SITE_NAME%', 'Jamit Job Board', '');";
		if (!JB_template_exists(310)) JB_mysql_query($sql);

		$sql = "INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (210, 'Dear %FNAME% %LNAME%,\r\n\r\nWe would like to notify you that the following post had expired on %SITE_NAME%:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n\r\nStatistics:\r\n%VIEWS% views\r\n%APPS% Applications\r\n\r\nThis job post will no longer be visible in the job listings. You may log in to your employer''s account to view or re-post this job, or post a new job at any time.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Job post expired', 'Jamit Demo', '');";
		
		if (!JB_template_exists(210)) JB_mysql_query($sql);

		$sql = "INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (220, 'Dear %FNAME% %LNAME%,\r\n\r\nThank you for posting your job to %SITE_NAME%!\r\n\r\nWe have just approved the following job to be listed on our site:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n%POST_URL%\r\n\r\n\r\nThis job post will now become visible on the job listings. You may log in to your employer''s account to view or edit this job at any time.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Your job posting was Approved!', 'Jamit Demo', '');";

		if (!JB_template_exists(220)) JB_mysql_query($sql);

		$sql = "INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (230, 'Dear %FNAME% %LNAME%,\r\n\r\nThank you for posting your job to %SITE_NAME%.\r\n\r\nHowever, after reviewing your job post, we have decided to disapprove it.\r\n\r\nThe following job post was disapproved:\r\n\r\n%POST_DATE% - \"%POST_TITLE%\"\r\n\r\nReason for disapproval: %REASON%\r\n\r\nYou may log in to your employer''s account to edit this job so that we may review it again.\r\n\r\nKind Regards,\r\n\r\n%SITE_NAME% team\r\n%SITE_URL%\r\n%SITE_CONTACT_EMAIL%', 'test@test.com', 'Your job posting was disapproved', 'Jamit Job Board', '');";
		if (!JB_template_exists(230)) JB_mysql_query($sql);

		$sql = "INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (60, 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nItem Name: %ITEM_NAME%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Bank Deposit\r\n--------------------------\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'webmaster@hiteacher.com', 'Order Confirmed', 'Hi Teacher', '');";

		if (!JB_template_exists(60)) JB_mysql_query($sql); // P. Confirmed - Bank

		$sql = "INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (61, 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nItem Name: %ITEM_NAME%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n--------------------------\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Order Confirmed', 'Jamit Demo', '');";

		if (!JB_template_exists(61)) JB_mysql_query($sql); // P. Confirmed - Check

		$sql = "INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (70, 'Dear  %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have credited your order to your account, and you may now use your available balance to post your job advertisement(s) to %SITE_NAME%.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nItem Name: %ITEM_NAME%\r\nPosts: %QUANTITY% \r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Completed\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n--------------------------\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your balance and order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Posts'' -> ''Posting Credits''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Order completed!', 'Jamit Demo', '')";
		if (!JB_template_exists(70)) JB_mysql_query($sql); // posting credits order completed

		$sql ="INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (90, 'Dear  %LNAME%, %FNAME%\r\n\r\nYour subscription payment on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have activated your subscription, and you may now log in to your account\r\nto access the resume database.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nStart Date: %SUB_START%\r\nEnd Date: %SUB_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n--------------------------\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your subscription order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Resumes'' -> ''Subscriptions''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Subscription now active!', 'Jamit Demo', '')";
		if (!JB_template_exists(90)) JB_mysql_query($sql); // sub completed

		$sql= "INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (80, 'Dear  %LNAME%, %FNAME%\r\n\r\nYour subscription on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: Bank\r\n--------------------------\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n', 'example@example.com', 'Subscription order confirmed', 'Jamit Demo', '')";
		if (!JB_template_exists(80)) JB_mysql_query($sql); // sub confirmed (bank)

		$sql = "INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (81, 'Dear %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n--------------------------\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email', 'example@example.com', 'Subscription order confirmed', 'Jamit Demo', '')";
		if (!JB_template_exists(81)) JB_mysql_query($sql); // sub confirmed (check)

		$sql = "INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (120, 'Dear  %LNAME%, %FNAME%\r\n\r\nThis email is sent to notify you that your membership to %SITE_NAME% has expired.\r\n\r\nWe thank you for your patronage during your membership time, and we hope\r\nthat we can continue to serve you as our member in the future.\r\n\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nStart Date: %MEM_START%\r\nEnd Date: %MEM_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Expired\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n--------------------------\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your membership order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Membership Details''.\r\n\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Membership Expired', 'Jamit Demo', '')";
		if (!JB_template_exists(120)) JB_mysql_query($sql); // mem expired

		$sql = "INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (100, 'Dear  %LNAME%, %FNAME%\r\n\r\nYour membership on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Item: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Bank\r\n--------------------------\r\n\r\nPlease deposit %INVOICE_AMOUNT% to the following account:\r\n	Bank: %BANK_NAME%\r\n	A/C Name: %AC_NAME%\r\n	A/C Number: %AC_NUMBER%\r\n\r\nAfter making the deposit, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n', 'example@example.com', 'Membership Order Confirmed', 'Jamit Demo', '')";
		if (!JB_template_exists(100)) JB_mysql_query($sql); // mem confirmed (BANK)

		$sql = "INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (101, 'Dear %LNAME%, %FNAME%\r\n\r\nYour order on %SITE_NAME% was confirmed, thank you.\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Confirmed\r\nPayment Method: Check / Money Order\r\n--------------------------\r\n\r\nPlease send %INVOICE_AMOUNT% (%CHECK_CURRENCY%) to the following address:\r\n	Payee Name: %PAYEE_NAME%\r\n	Address: \r\n        %PAYEE_ADDRESS%\r\n	\r\n\r\nAfter mailing the check, please send an email to \r\n%SITE_CONTACT_EMAIL% with the following \r\nOrder Number: %INVOICE_CODE% to help us process the transaction. \r\n\r\nFeel free to contact %SITE_CONTACT_EMAIL% if you have \r\nany questions / problems. \r\n\r\nThank you!\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email', 'example@example.com', 'Membership Order Confirmed', 'Jamit Demo', '')";
		if (!JB_template_exists(101)) JB_mysql_query($sql); // member confirmed (check)

		$sql = "INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (110, 'Dear  %LNAME%, %FNAME%\r\n\r\nYour membership payment on %SITE_NAME% was successfully completed, thank you!\r\n\r\nWe have activated your membership, and we welcome you as our new member. \r\nHere are your membership payment details:\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nMembership Name: %ITEM_NAME%\r\nMembership Months: %MEM_DURATION%\r\nStart Date: %MEM_START%\r\nEnd Date: %MEM_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Active\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n--------------------------\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your membership order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Membership Details''.\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Membership Activated', 'Jamit Demo', '')";
		if (!JB_template_exists(110)) JB_mysql_query($sql); // member completed

		$sql = "INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (130, 'Dear  %LNAME%, %FNAME%\r\n\r\nThis email is sent to notify you that your subscription to %SITE_NAME% has expired.\r\n\r\nWe thank you for your patronage during your subscription time, and we hope\r\nthat we can continue to serve you as our subscriber in the future.\r\n\r\n\r\n========================\r\nORDER DETAILS\r\n=========================\r\nOrder ID: #%INVOICE_CODE%\r\nSubscription Name: %ITEM_NAME%\r\nSubscription Months: %SUB_DURATION%\r\nStart Date: %SUB_START%\r\nEnd Date: %SUB_END%\r\nPrice: %INVOICE_AMOUNT%\r\nStatus: Expired\r\nPayment Method: %PAYMENT_METHOD%\r\n\r\n--------------------------\r\n\r\nThank you for using %SITE_NAME%! \r\n\r\nYou may view your subscription order history at any time.\r\nJust log in to your %SITE_NAME%, and go to ''Account'' -> ''Subscription''.\r\n\r\n\r\n\r\n%SITE_NAME% team.\r\n%SITE_URL%\r\n\r\nNote: This is an automated email.', 'example@example.com', 'Subscription Expired', 'Jamit Demo', '')";
		if (!JB_template_exists(130)) JB_mysql_query($sql); // sub expired

		$sql = "INSERT INTO `email_templates` ( `EmailText` , `EmailFromAddress` , `EmailFromName` , `EmailSubject` , `EmailID` , `sub_template` )VALUES ('%APP_LETTER% \r\n\r\n----------------------------------- \r\nThis email was sent from %SITE_NAME% %BASE_HTTP_PATH%\r\nOnline Resume Link: \r\n%RESUME_DB_LINK%\r\n', '', '', '', '12', '');";
		if (!JB_template_exists(12)) JB_mysql_query($sql); // application

		$sql = "INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (11, '%MESSAGE%\r\n\r\n\r\n\r\n\r\n------------------------\r\n%SITE_URL%\r\n\r\nThis message was sent by somebody using the \r\nweb-email service provided by %SITE_NAME%.\r\n\r\nName: %EMPLOYER_NAME%\r\nSender\'s User ID: %USER_ID%\r\nSender IP: %SENDER_IP%\r\n', 'example@example.com', '', 'Jamit Demo', '');";
		
		if (!JB_template_exists(11)) JB_mysql_query($sql); // employer to candidate

		$sql = "INSERT INTO `email_templates` (`EmailID`, `EmailText`, `EmailFromAddress`, `EmailSubject`, `EmailFromName`, `sub_template`) VALUES (44, 'Hello %EMP_NAME%\r\n\r\n%CAN_NAME% has granted you access to their online resume on %SITE_NAME%!\r\n\r\nTo view this resume, please see this link:\r\n\r\nResume link: %RESUME_DB_LINK%\r\n\r\nThanks,\r\n\r\n%SITE_NAME%\r\n%SITE_URL%\r\n-------------\r\n\r\n', '".JB_SITE_CONTACT_EMAIL."', '%CAN_NAME% granted you access to their resume on %SITE_NAME% ', '".JB_SITE_NAME."', '');";

		if (!JB_template_exists(44)) JB_mysql_query($sql); // employer to candidate

		
		if ($sql!='') JB_format_email_translation_table ();


		// fix the profile tag
		$sql = "UPDATE form_fields SET template_tag = 'PROFILE_BNAME' WHERE template_tag='PROFILE_COL2' "; 
		JB_mysql_query($sql);

		$sql = "UPDATE form_lists SET template_tag = 'PROFILE_BNAME' WHERE template_tag='PROFILE_COL2' "; 
		JB_mysql_query($sql);

		// replace config.php

		if (mysql_affected_rows($jb_mysql_link)>0) {
			$filename = JB_basedirpath().'config.php';
			$handle = fopen($filename, "r");
			$contents = fread($handle, filesize($filename));
			fclose($handle);
			$contents = str_replace('s:12:"PROFILE_COL2', 's:13:"PROFILE_BNAME', $contents);

			$handle = fopen($filename, 'w');
			fwrite($handle, $contents, strlen($contents));
			fclose($handle);

		}



		if (!does_field_exist("lang", "theme")) {
			$sql ="ALTER TABLE `lang` ADD `theme` VARCHAR(30) NULL default '".JB_THEME."'";
			//echo $sql." flag $flag";
			if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
			$upgrade_needed = true;
		}
		if (!does_field_exist("requests", "deleted")) {
			$sql ="ALTER TABLE `requests`  ADD `deleted` SET( 'Y', 'N' ) NOT NULL DEFAULT 'N' ";
			//echo $sql." flag $flag";
			if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
			$upgrade_needed = true;
		}

		

		if (!does_field_exist("xml_import_feeds", "feed_id")) {
		
			$sql = "CREATE TABLE `xml_import_feeds` (
				  `feed_id` int(11) NOT NULL auto_increment,
				  `feed_metadata` text NOT NULL,
				  `feed_name` varchar(255) NOT NULL,
				  `description` varchar(255) NOT NULL,
				  `date` date NOT NULL,
				  `xml_sample` text NOT NULL,
				  `feed_key` varchar(255) NOT NULL,
				  `ip_allow` text NOT NULL,
				  `feed_url` varchar(255) NOT NULL,
				  `feed_filename` varchar(255) NOT NULL,
				  `ftp_user` varchar(255) NOT NULL,
				  `ftp_pass` varchar(255) NOT NULL,
				  `ftp_filename` varchar(255) NOT NULL,
				  `ftp_host` varchar(255) NOT NULL,
				  `status` varchar(10) NOT NULL,
				  `pickup_method` varchar(5) NOT NULL,
				  `cron` set('Y','N') NOT NULL,
				  PRIMARY KEY  (`feed_id`)
				) ENGINE=MyISAM";
			
			if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
			$upgrade_needed = true;

			
			// change post_id to be auto-increment
			$sql = " ALTER TABLE `posts_table` CHANGE `post_id` `post_id` INT( 11 ) NOT NULL AUTO_INCREMENT  ";
			if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());

		}

		if (!does_field_exist("package_invoices", "invoice_tax")) {
		
			$sql = "ALTER TABLE `package_invoices` ADD `invoice_tax` FLOAT NOT NULL DEFAULT '0';";
			if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
			$sql = "ALTER TABLE `subscription_invoices` ADD `invoice_tax` FLOAT NOT NULL DEFAULT '0';";
			if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
			$sql = "ALTER TABLE `membership_invoices` ADD `invoice_tax` FLOAT NOT NULL DEFAULT '0';";
			if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());

			// change post_id to be auto-increment
			$sql = " ALTER TABLE `profiles_table` CHANGE `profile_id` `profile_id` INT( 11 ) NOT NULL AUTO_INCREMENT  ";
			if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());

			// fix template tag for the logo field in profiles_table
			$sql = "UPDATE form_fields SET template_tag='IMAGE' WHERE field_id=66 AND template_tag = '' ";
			if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());

			$upgrade_needed = true;

		}

		
		if (!does_field_exist('xml_export_feeds', 'include_imported')) {
		
			$sql = "ALTER TABLE `xml_export_feeds` ADD `include_imported` SET( 'Y', 'N' ) NOT NULL default 'N'";
			if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());

			$upgrade_needed = true;
		}

		if (!does_field_exist("saved_resumes", "resume_id")) {

			$sql = "CREATE TABLE `saved_resumes` (
				  `resume_id` int(11) NOT NULL default '0',
				  `user_id` int(11) NOT NULL default '0',
				  `save_date` datetime NOT NULL default '0000-00-00 00:00:00',
				  PRIMARY KEY  (`resume_id`,`user_id`),
				  KEY `composite` (`user_id`,`save_date`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
			if ($flag) JB_mysql_query($sql) or die ($sql.mysql_error());
			$upgrade_needed = true;

		}

		if (1==1) { // check to make sure that all form fields have a template_tag, if not generate one

			$sql = "SELECT * FROM `form_fields` ";
			$result = mysql_query($sql);
			while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

				if (($row['field_type'] == 'BLANK') ||  ($row['field_type'] == 'SEPERATOR') ||  ($row['field_type'] == 'NOTE')) {
					continue;
				}

				if (trim($row['template_tag']=='')) {

					// create a new name for it
			
					$template_tag = strtoupper(preg_replace('/[^a-z^0-9]+/i','_', $row['field_label']));
					$template_tag = preg_replace ('/_$/', '', $template_tag);
					$template_tag = preg_replace ('/$_/', '', $template_tag);
					$base_template_tag = $template_tag;

					// check to see if it exists?
					$i=1;
					do {
						$sql = "SELECT * FROM `form_fields` WHERE `form_id`='".jb_escape_sql($row['form_id'])."' AND `template_tag`='".jb_escape_sql($template_tag)."' ";
						$result2 = mysql_query($sql);
						if (mysql_num_rows($result2)==0) {
							$sql = "UPDATE `form_fields` SET `template_tag`='".jb_escape_sql($template_tag)."' WHERE `field_id`='".jb_escape_sql($row['field_id'])."' ";
							jb_mysql_query($sql);
							//echo "$sql<br>";
							break;

						} else {
							$i++;
							$template_tag = $base_template_tag.$i;
							if ($i>10) {
								break;
							}
						}

					} while (0);

				}

			}


		}

	
		
		/*

		MySQL 5 only.

		// convert categories to UTF-8

		$sql = "ALTER TABLE `categories` CHANGE `category_name` `category_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL";

		JB_mysql_query($sql);

		// now convert data:

		$sql = "SELECT category_name, category_id FROM categories ";
		$result = JB_mysql_query($sql);
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			
			$sql = "UPDATE categories SET category_name = '".jb_escape_sql(addslashes(JB_html_ent_to_utf8($row['category_name'])))."' WHERE category_id='".$row['category_id']."' ";
			JB_mysql_query($sql);

		}

		$sql = "ALTER TABLE `cat_name_translations` CHANGE `category_name` `category_name` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ";

		JB_mysql_query($sql);

		// now convert data:

		$sql = "SELECT category_name, category_id FROM cat_name_translations ";
		$result = JB_mysql_query($sql);
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			
			$sql = "UPDATE categories SET category_name = '".jb_escape_sql(addslashes(JB_html_ent_to_utf8($row['category_name'])))."' WHERE category_id='".$row['category_id']."' ";
			JB_mysql_query($sql);

		}

		*/

		


		// THIS CODE BLOCK SHOULD ALWAYS BE AT THE END

		// Update the cache
		if ($flag && (does_field_exist("categories", "seo_fname"))) {
			
			
			JB_cache_flush();

		}

		if (($upgrade_needed==false) && (defined('JB_VERSION'))) {

			$sql = "REPLACE  INTO `jb_variables` VALUES ('JB_VERSION', '".JB_VERSION."')";
			JB_mysql_query($sql);

			$JBMarkup->ok_msg('- Job Board version changed to '.JB_VERSION.'. Please log out form Admin and log in again for the version number to change.');

		}
	}
	
	return $upgrade_needed;	


}


function JB_template_exists($id) {
	global $jb_mysql_link;
	$sql = "select * from email_templates where EmailID='$id' ";
	$result = JB_mysql_query($sql) or die(mysql_error());
	if (mysql_num_rows($result) > 0 ) {
		return true;

	} else {
		return false;
	}

}


?>