<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
require "../config.php";
require ("admin_common.php");
require ("../include/xml_feed_functions.php");

if ($_REQUEST['export']=='') {
	$_REQUEST['export']=1;
}


JB_admin_header('Admin->XML Feed');



?>
<b>[XML Export]</b> 
	<span style="background-color: <?php if ($_REQUEST['export']=='1') { echo '#FFFFCC'; } else { echo "#F2F2F2"; } ?>; border-style:outset; padding:5px; "><a href="xmlfeed.php?export=1">XML Feeds</a></span> <span style="background-color:#F2F2F2; border-style:outset; padding: 5px;"><a href="xmlschema.php">XML Schemas</a></span> 
	<span style="background-color:#F2F2F2; border-style:outset; padding: 5px;"><a href="xmlhelp.php">XML Help</a></span>
	<hr>

<?php



?>

<input type="button" value="Create a New Feed..." onclick="window.location='xmlfeed.php?export=<?php echo $_REQUEST['export']; ?>&new=yes'" >

<hr>

<?php

if ($_REQUEST['save_feed']!='') {

	if ($error=JBXM_validate_xml_feed_input()) {
		$JBMarkup->error_msg("Cannot save the feed due to the following errors:");
		echo $error;
	
		JBXM_display_xml_feed_form();
	} else {
		JBXM_save_xml_feed_input();
		$JBMarkup->ok_msg('Changes Saved.');

	}


}

if ($_REQUEST['clear']!='') {

	$cache_dir = JB_get_cache_dir();
	$filename = $cache_dir.'feed_'.md5($_REQUEST['clear'].$_REQUEST['feed_key']).".xml";
	@unlink ($filename);

	$JBMarkup->ok_msg('Cache Cleared.');

}

if ($_REQUEST['delfeed']!='') {

	$sql = "DELETE FROM xml_export_feeds WHERE feed_id='".jb_escape_sql($_REQUEST['feed_id'])."' ";
	JB_mysql_query($sql) or die(mysql_error());
	$JBMarkup->ok_msg('Feed Deleted.');

}

if ($_REQUEST['new']=='yes') {
	if ($_REQUEST['schema_id'] == '') {
		echo '<p>';
		echo 'Please select what type of feed you would like to create (Schema):<br>';
		?>
		<form method='post' action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
		<input type="hidden" name='new' value='yes'>
		<select name='schema_id'>
			<option value=''>[Select]</option>
			<?php

				$sql = "select * from xml_export_schemas";
				$result = JB_mysql_query($sql);
				while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {
					if ($row['schema_id']==$_REQUEST['schema_id']) {
						$sel = ' selected ';
					} else {
						$sel = '';
					}
					echo '<option '.$sel.' value="'.$row['schema_id'].'">'.$row['schema_name'].'</option>';
				}

			?>
			</select>
			<input type="submit" value='Continue -&gt;'>

		</form>
		</p>
		<?php

	} else {

		JBXM_display_xml_feed_form();
	}
} else {

	JBXM_list_xml_feeds();
	echo '<hr>';

}

if ($_REQUEST['editfeed']!='') {
	?>
	<p>
	<?php
	JBXM_display_xml_feed_form();
	?>
	</p>
	<?php
}

JB_Admin_footer();


?>