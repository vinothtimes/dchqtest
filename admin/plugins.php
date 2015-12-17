<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
define ('JB_IGNORE_INPUT_FILTER', true);

require("../config.php");
require (dirname(__FILE__)."/admin_common.php");

JB_admin_header('Admin -> Plugins');



if (JB_PLUGIN_SWITCH!='YES') {

	echo "<b>This feature is not enabled are not enabled. Please go to <a href='edit_config.php'>Main Config</a> to enable plugins.</b>";
	die();
}

if (JB_DEMO_MODE=='YES') { 
	$JBMarkup->ok_msg('Demo mode is enabled - plugins cannot be enabled or disabled');

} else {
	JB_show_lang_permission_warning();

}


JBPLUG_require_all_plugins();

if (JB_DEMO_MODE!='YES') {

	if ($_REQUEST['action']=='enable') {
		
		// JB_ENABLED_PLUGINS

		$_JB_PLUGINS[$_REQUEST['plugin']]->enable();
		echo "<p>";
		echo "<b>Plugin Enabled.</b> <a href='plugins.php?plugin=".jb_escape_html($_REQUEST['plugin'])."'>Click Here to Continue</a>";
		echo "</p>";
		JB_admin_footer();
		die();
		

	}

	if ($_REQUEST['action']=='disable') {

		$_JB_PLUGINS[$_REQUEST['plugin']]->disable();
		echo "<p>";
		echo "<b>Plugin Disabled.</b> <a href='plugins.php'>Click Here to Continue</a>";
		echo "</p>";
		JB_admin_footer();
		die();

			// JB_ENABLED_PLUGINS

	}

	if ($_REQUEST['action']=='save') {

		$_JB_PLUGINS[$_REQUEST['plugin']]->save_config();
		echo "<p>";
		echo "<b>Config Saved</a> <a href='plugins.php?plugin=".$_REQUEST['plugin']."'>Click Here to Continue</a> ";
		echo "</p>";
		JB_admin_footer();
		die();
		
			// JB_ENABLED_PLUGINS

	}

}

JBPLUG_list_plugins();

JB_admin_footer();
?>
