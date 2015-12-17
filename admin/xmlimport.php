<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
ini_set('max_execution_time', 60*15);

require "../config.php";
require ("admin_common.php");
require_once ("../include/xml_import_functions.php");

JB_admin_header('Admin->XML Feed', 'xmlimport');

?>
<b>[XML Import]</b> 
	<span style="background-color:#FFFFCC; border-style:outset; padding:5px; "><a href="xmlimport.php">Import Setup</a></span> 
	<span style="background-color:#F2F2F2; border-style:outset; padding:5px; "><a href="xmlimport_log.php">Import Log</a></span>
	<span style="background-color:#F2F2F2; border-style:outset; padding: 5px;"><a href="xmlimporthelp.php">Import Help</a></span>
	<hr>
<?php


define ('JB_IMP_STEP1', 1);
define ('JB_IMP_STEP2', 2);
$step = JB_IMP_STEP1;



?>
<input type="button" value="Add a new Feed to Import..." onclick="window.location='xmlimport.php?action=new_feed'" >

<hr>
<!--<h3>XML Import - Feed Setup</h3>-->


<?php

if ($_REQUEST['del_feed']!='') {

	jb_delete_import_feed($_REQUEST['feed_id']);
	$_REQUEST['feed_id']='';

}


if ($_REQUEST['submit_feed']!='') {


	$error = JB_validate_import_feed_form();

	if ($error=='') {

		JB_save_import_feed_form();
		$JBMarkup->ok_msg('Import feed saved.');
		jb_list_xml_import_feeds();
	} else {
		jb_list_xml_import_feeds();
		$JBMarkup->error_msg('Cannot save for the following reasons:');
		
		echo $error;
		JB_display_import_feed_form($load_row=false);

	}

} elseif ($_REQUEST['submit_field_setup']!='') {

	$error = JB_XMLIMP_validate_field_setup_form();

	if ($error=='') {

		JB_save_import_feed_field_setup_form();
		$JBMarkup->ok_msg('Field settings saved.');
		jb_list_xml_import_feeds();
	} else {
		
		$JBMarkup->error_msg('Error - some problems were detected:');
		echo $error;
		jb_display_field_setup_form($load_row=false);

	}
	
	
	
} elseif ($_REQUEST['set_sequence']!='') {

	//$error = JB_validate_import_feed_form();
	if ($_REQUEST['element']!='') {

		$feed = JB_XMLIMP_load_feed_row($_REQUEST['feed_id']);
		
		$feed['FMD']->setSequenceElement($_REQUEST['element']);
		
		$feed['FMD']->save();
		
		jb_list_xml_import_feeds();


	} else {

		$JBMarkup->error_msg('Please select the sequence element!');
		echo "<br>";

	}
	
// Add a new feed	
} elseif (($_REQUEST['action']=='new_feed') || ($_REQUEST['action']=='edit_feed')) {
	jb_list_xml_import_feeds();
	JB_display_import_feed_form();

// setup feed's sequence element
} elseif ($_REQUEST['action']=='setupstruct') {
	jb_list_xml_import_feeds();
	jb_display_structure_setup_form($_REQUEST['feed_id']);

// setup fields, map fields to xml elements
} elseif ($_REQUEST['action']=='setupfields') {
	jb_list_xml_import_feeds();

	$feed = JB_XMLIMP_load_feed_row($_REQUEST['feed_id']);
	jb_display_field_setup_form($_REQUEST['feed_id']);


} else {

	jb_list_xml_import_feeds();

}

if ($_REQUEST['action']=='fetch') {

	$feed_id = (int) $_REQUEST['feed_id'];

	?>
	<iframe src="xmlimport_iframe.php?feed_id=<?php echo htmlentities($feed_id)?>" width="100%" height="200" id="iframe" name="iframe"></iframe>
	<?php			

}
if (jb_xml_bug_test()) {
	$JBMarkup->error_msg("PHP Bug warning: The system detected that your PHP version has a bug in the XML parser. This is not a bug in the Jamit Job Board, but a bug in 'libxml' that comes built in to PHP itself. An upgrade of PHP with the latest version of 'libxml' with  is recommended.</font> For details about the bug, please see <a href='http://bugs.php.net/bug.php?id=45996'>http://bugs.php.net/bug.php?id=45996</a>");

}

function jb_xml_bug_test() {

	$data="<?xml version = '1.0' encoding = 'UTF-8'?>
	<test>
	  &amp;
	</test>
	";

	$parser = xml_parser_create('UTF-8');
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $data, $vals, $index);
	xml_parser_free($parser);

	if ($vals[0]['value']!='&') {
		return true; // bug detected
	}


}


JB_admin_footer();

?>
