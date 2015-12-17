<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";
require ("admin_common.php");
// xml_change_window.php
require ("../include/xml_feed_functions.php");

JB_admin_header('Admin-> XML Change Window');

if ($_REQUEST['to_static']) {

	$sql = "SELECT * from xml_export_feeds WHERE feed_id='".jb_escape_sql($_REQUEST['feed_id'])."' ";
	$result = JB_mysql_query($sql);
	$feed_row = mysql_fetch_array($result, MYSQL_ASSOC);
	$feed_row['field_settings'] = unserialize($feed_row['field_settings']);

	if ($_REQUEST['static_data']=='') {
		$_REQUEST['static_data'] = $feed_row['field_settings']['static_data_'.$_REQUEST['element_id']];
	}

	// if it is still blank, get it from the element
	if ($_REQUEST['static_data']=='') {
		$sql = "SELECT * from xml_export_elements WHERE element_id='".jb_escape_sql($_REQUEST['element_id'])."' ";
		$result = JB_mysql_query($sql);
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		$_REQUEST['static_data'] = $row['static_data'];
	}



	if ($_REQUEST['save_static']) {

		//print_r($_REQUEST);

		$_REQUEST['static_data'] = trim ($_REQUEST['static_data']);

		if ($_REQUEST['static_data']=='') {

			$JBMarkup->error_msg('Error - static data value is blank'); 
			

		} else {

			
			

			$feed_row['field_settings']['static_data_'.$_REQUEST['element_id']] = $_REQUEST['static_data'];

			$field_settings_str = addslashes(serialize($feed_row['field_settings']));

			$sql = "UPDATE xml_export_feeds SET `field_settings`='".jb_escape_sql($field_settings_str)."' WHERE feed_id='".jb_escape_sql($_REQUEST['feed_id'])."' ";

			JB_mysql_query($sql);

			$JBMarkup->ok_msg('Changes Saved. Click to <a href="" onclick="window.close();return false;">close this window</a>'); ?>

			<script type="text/javascript">

			window.opener.location='xmlfeed.php?editfeed=yes&feed_id=<?php echo $_REQUEST['feed_id']; ?>';
			</script>

			<?php

		}

	}


	?>
	<P style="font-weight: bold">You can type in any value, and this value will get exported to the feed as is. You can also type in any of the following tags: </p>
	<?php JBXM_echo_static_tag_list(); ?>
	<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
	<input type="hidden" name="form_id" value="<?php echo jb_escape_html($_REQUEST['form_id']); ?>" >
	<input type="hidden" name="schema_id" value="<?php echo jb_escape_html($_REQUEST['schema_id']); ?>" >
	<input type="hidden" name="element_id" value="<?php echo jb_escape_html($_REQUEST['element_id']); ?>" >
	<input type="hidden" name="feed_id" value="<?php echo jb_escape_html($_REQUEST['feed_id']); ?>" >
	<input type="hidden" name="to_static" value="<?php echo jb_escape_html($_REQUEST['to_static']); ?>" >
		<p>
		<b>Static Value</b>: <input type="text" size="50" value="<?php echo jb_escape_html($_REQUEST['static_data']);?>"; name="static_data"><br>
		<input type="submit" name="save_static" value="Save"><br>
		<i>Note: If you haven't saved changes in the previous window, then please do it now before clicking the save button above</i>
		</p>
	</form>

	<?php

}

if ($_REQUEST['to_db']) {

	if ($_REQUEST['save_to_db']!='') {

		$sql = "SELECT * from xml_export_feeds WHERE feed_id='".jb_escape_sql($_REQUEST['feed_id'])."' ";
		$result = JB_mysql_query($sql);
		$feed_row = mysql_fetch_array($result, MYSQL_ASSOC);
		$feed_row['field_settings'] = unserialize($feed_row['field_settings']);

		$feed_row['field_settings']['static_data_'.$_REQUEST['element_id']] = '';

		$field_settings_str = addslashes(serialize($feed_row['field_settings']));

		$sql = "UPDATE xml_export_feeds SET `field_settings`='".jb_escape_sql($field_settings_str)."' WHERE feed_id='".jb_escape_sql($_REQUEST['feed_id'])."' ";

		JB_mysql_query($sql);

		$JBMarkup->ok_msg('Changes Saved. Click to <a href="" onclick="window.close();return false;">close this window</a>');

		?>
	

		<script type="text/javascript">

		window.opener.location='xmlfeed.php?editfeed=yes&feed_id=<?php echo $_REQUEST['feed_id']; ?>';
		</script>

		<?php



	} else {

		?>
		<br>

		<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
		<input type="hidden" name="form_id" value="<?php echo jb_escape_html($_REQUEST['form_id']); ?>" >
		<input type="hidden" name="schema_id" value="<?php echo jb_escape_html($_REQUEST['schema_id']); ?>" >
		<input type="hidden" name="element_id" value="<?php echo jb_escape_html($_REQUEST['element_id']); ?>" >
		<input type="hidden" name="feed_id" value="<?php echo jb_escape_html($_REQUEST['feed_id']); ?>" >
		<input type="hidden" name="to_db" value="<?php echo jb_escape_html($_REQUEST['to_db']); ?>" >
			<p>
			<b>Change to database value?</b>: <br>
			<input type="submit" name="save_to_db" value="Yes">
			</p>
		</form>
		<i>Note: If you haven't saved changes in the previous window, then please do it now before clicking the save button above.</i>

		<?php

	}

}

JB_admin_footer();

?>
