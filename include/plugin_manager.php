<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
/*

###############################################################################

Here is a quick intro to plugins: 

For example, say if you some very simple code:

echo 'hello';
echo 'world';

and then you want to have a plugin print something between hello and world, 
do this:

echo 'hello';
JBPLUG_do_callback('example_hook', $A=false);
echo 'world';

So far, the above line will not do anything because there is no plugin attached
to 'example_hook'.

You will need to open any one of the plugin php files in include/plugins/
(A plugin is just a normal php class file which follows some conventions)

Look for the plugin's constructor function (the function has the same name as
the plugin) and enter the following line:

JBPLUG_register_callback('example_hook', array($this->plugin_name, 'example'), 
$this->config['priority']); 

The above line tells the plugin manager to hook on to 'example_hook' and call 
the example() method when the JBPLUG_do_callback('example_hook', $A=false); 
line is executed


Then add the following function:

function example() {

echo ' test ';


}

That's all there is to it - the word 'test' will be printed between hello and
world. 

What about returning values and modifying data structures?

One problem is that plugin methods cannot return any values. The trick to get 
around that is to pass the first argument to the example() by reference 
http://www.phpbuilder.com/manual/language.references.pass.php. For example:

function example(&$some_argument) {

    $some_argument = 'something new';

}

This allows plugins to change data structures.

More advanced stuff:

Hooks can also pass unlimited number of arguments. For example:
$A = 'hello';
$B = 'world'
$C = 'test!';
JBPLUG_do_callback('example_hook', $A, $B, $C);

The plugin registration line would look unchanged:

JBPLUG_register_callback('example_hook', array($this->plugin_name, 'example'), 
$this->config['priority']); 

However, our method would need to change to:

function example(&$argument, $arg2, $arg3) { // it has three arguments now

}

What if you need to have a plugin take up a new page?

- Use the hook in one of the p.php files - see JobsFair for example

Plugins make programming convenient:

- Plugins have a convenient way to save the configuration settings in to 
config.php, load settings from config.php and also ability to use their own 
private lang/english_default.php file so that the language strings can be 
translated
- Plugins can use Jamit's database and cache functions, file upload functions 
(see MP3 Field), mailing queue
- Plugins are contained in their own php file, blissfully encapsulated in their 
own class so they do not cause naming conflicts with anything else.

###############################################################

*/


if (!defined('JB_ENABLED_PLUGINS')) {
	define ('JB_ENABLED_PLUGINS', '');
}


$_JB_PLUGINS = array(); // Enabled plugin objects

$JB_callbacks = array(); // A table which stores the registrations between the hooks and
// the methods in each the plugin object. When a particular hook is called
// using the JBPLUG_do_callback() function, the plugin mananger uses this table
// determine which method & class was registered with the hook, and calls a
// plugin's method that was registered.
// To register a mapping, a plugin uses the JBPLUG_register_callback() function
// in the constructor method.

$_JB_PLUGIN_CONFIG = unserialize(JB_PLUGIN_CONFIG);





###############################################################

/*

The JB_plugins class is a blueprint for all plugin classes
found in include/plugins/ directory. All plugins must extend
this class, and each plugin must implement all the methods
in the class. Some methods such as disable() and enable()
provide basic services for the plugin, and the child should
use them. If you are creating a new plugin, please use
one of the existing plugins as a template.


*/
class JB_Plugins {

	
	var $class_name;
	var $plugin_config;

	function JB_Plugins () {
		global $_JB_PLUGIN_CONFIG;
		$this->config = $_JB_PLUGIN_CONFIG[$this->plugin_name];
		
	}

	function get_description() {
		return 'please implement get_description() method for your module.';

	}

	function get_name() {
		return 'please implement get_name() method for your module.';

	}

	function get_author() {
		return 'please implement get_author() method for your module.';

	}

	function get_version() {
		return 'please implement get_version() method for your module.';

	}

	function get_version_compatible() {
		return '3.0.0+';

	}

	function config_form() {
		echo "please implement config_form() method in your plugin<br>";

	}

	function save_config() {
		echo "please implement save_config() method in your plugin<br>";

	}

	// delete any files cached by the plugin
	function clear_cache() {
		JB_cache_flush(); // clear the entire cavhe
		return true;

	}

	function is_enabled() {
		return true;

	}

	/*
	A plugin should implement this method and call it's parent like this:
	parent::enable($this->plugin_name);

	*/
	function enable($class_name='JB_Plugins') {
		if ($class_name=='JB_Plugins') {
			echo "please implement enable() method in your plugin. See the example modules to see how it is done<br>";
			return false;
		}

		
		if (strlen(JB_ENABLED_PLUGINS) > 0 ) {
			$enabled_plugins = explode (',', JB_ENABLED_PLUGINS);
			
			$enabled_plugins[] = $class_name;
			$enabled_plugins = implode (',', $enabled_plugins);
		} else {
			$enabled_plugins = $class_name;
		}

	
		JBPLUG_update_enabled_plugins($enabled_plugins);
		JBPLUG_merge_english_default_files();

	}

	/*
	A plugin should implement this method and call it's parent like this:
	parent::enable($this->plugin_name);

	*/

	function disable($class_name='JB_Plugins') {
		if ($class_name=='JB_Plugins') {
			echo "please implement disable() method in your plugin. See the example modules to see how it is done<br>";
			return;
		}

		if (strlen(JB_ENABLED_PLUGINS) > 0 ) {
			$enabled_plugins = explode (',', JB_ENABLED_PLUGINS);
			
			foreach ($enabled_plugins as $plugin) {
				if ($plugin != $class_name) {
					$new_plugins[] = $plugin;

				}
			}
			if (sizeof($new_plugins)>0) {
				$enabled_plugins = implode (',', $new_plugins);
			} else {
				$enabled_plugins = '';
			}
		
			JBPLUG_update_enabled_plugins($enabled_plugins);
		} 

	}

}



##########################################################################################
/*
Examples:


To check if another plugin is enabled:

global $_JB_PLUGINS;

if (isset($_JB_PLUGINS['SomePluginName'] && $_JB_PLUGINS['SomePluginName']->is_enabled()) {

	echo 'it is enabled';

}


To change a setting in plugin config (config.php):

global $_JB_PLUGIN_CONFIG;

$_JB_PLUGIN_CONFIG['SomePluginName'][some_setting] = ''; // or you can use unset($_JB_PLUGIN_CONFIG['SomePluginName'][some_setting] );

JBPLUG_update_plugin_config($_JB_PLUGIN_CONFIG);


##############################################################



Function:

JBPLUG_update_enabled_plugins(string $enabled_plugins) 

Description:

This function saves the JB_ENABLED_PLUGINS setting 
in config.php 

Arguments:

$enabled_plugins is a string of the value contained in the
JB_ENABLED_PLUGINS setting in config.php

The JB_ENABLED_PLUGINS setting is a string which lists all enabled 
plugins. Each plugin name is seperated by a comma.




*/

function JBPLUG_update_enabled_plugins($enabled_plugins=JB_ENABLED_PLUGINS) {

	if (JB_DEMO_MODE=='YES') return;

	// only valid characters are allowed

	$enabled_plugins = preg_replace('/[^a-z^0-9^_^-^,]+/i', '', $enabled_plugins);

	// load the config in

	$config_dir = jb_get_config_dir();
	
	$filename = $config_dir."config.php";
	$handle  = fopen($filename, "rb");
	$contents = fread($handle , filesize($filename));
	fclose ($handle);

	if (($enabled_plugins != JB_ENABLED_PLUGINS)) {

		// change the JB_ENABLED_PLUGINS constant
		
		$handle  = fopen($filename, "w");

		$new_contents = JB_change_config_value($contents, 'JB_ENABLED_PLUGINS', $enabled_plugins); 
	
		fwrite($handle , $new_contents, strlen($new_contents));
		fclose ($handle);

	}
}

/*


Function :

JBPLUG_update_plugin_config(string $config) 

Description:

This function saves the JB_PLUGIN_CONFIG setting 
in config.php 

Arguments:

$config is a serialized string of the value contained in the
JB_PLUGIN_CONFIG setting in config.php

The JB_ENABLED_PLUGINS setting is a string which stores the configuration 
options for all plugins. The string is a serialized version of the 



*/


function JBPLUG_update_plugin_config($config=JB_PLUGIN_CONFIG) { // $config must be serialized!

	if (JB_DEMO_MODE=='YES') return;

	// load the config in

	if (!is_string($config)) {

		if (is_array($config)) {
			$config = serialize($config);
		} else {
			trigger_error('JBPLUG_update_plugin_config() should serialize the argument' , E_USER_WARNING );
			return;
		}
	} 

	$config_dir = jb_get_config_dir();

	$filename = $config_dir."config.php";
	$handle  = fopen($filename, "rb");
	$contents = fread($handle , filesize($filename));
	fclose ($handle);
	
	$handle  = fopen($filename, "w");

	$new_contents = JB_change_config_value($contents, 'JB_PLUGIN_CONFIG', $config); 

	fwrite($handle , $new_contents, strlen($new_contents));
	fclose ($handle);

	

}

############################################################
# Require all the enabled plugins.

function JBPLUG_require_plugins() {

	global $_JB_PLUGINS;

	if (JB_PLUGIN_SWITCH!='YES') {
		
		RETURN false;
	}

	$dir = dirname(__FILE__)."/plugins/";

	if (JB_ENABLED_PLUGINS!='') {
		$enabled_plugins = explode(',', JB_ENABLED_PLUGINS);
		if (sizeof ($enabled_plugins) > 0) {
			foreach ($enabled_plugins as $plugin) {
				
				$plugin_file = $dir.$plugin."/$plugin.php";
				if (file_exists($plugin_file)) {
					include_once($plugin_file);
				} else {
					echo "Couldn't require $plugin_file <br>";
				}
			}
		}
	}


}

#####################################################################
# Require all plugins, enabled or not enabled.


function JBPLUG_require_all_plugins() {

	if (JB_PLUGIN_SWITCH!='YES') {

		return;
	}

	global $_JB_PLUGINS;

	$dir = dirname(__FILE__)."/plugins/";
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				
				if ((filetype($dir . $file)=='dir') && ($file!='.') && ($file!='..')) {
					// include the main class...
					$dir2 = $dir . $file;

					$temp = explode('.', $file2);
					$ext = array_pop($temp);
					
				
					$temp = preg_split ('%[/\\\]%', $dir2);
					$name = array_pop($temp);

					$name = "$name.php";

					if (file_exists($dir2 .'/'. $name)) {
						require_once ($dir2 .'/'. $name);
					}
					
					

				}
			}
			closedir($dh);
		}
	}


}

###################################################################

/*

Function:

JBPLUG_do_callback

Description:

Also known as a 'hook'. 

This function calls the particular methods inside all 
enabled plugins that were hooked on to this hook.

A plugin is a PHP class; in the plugin's constructor, the methods in a plugin 
can be registered to a hook using the 
JBPLUG_register_callback() function. The hook registrations are stored in a 
global table ($JB_callbacks).

This function uses the table to look up the hooks and call the registered
methods.

This is similar to event based programming, where this function would be a 
trigger and the plugin would be an event handler. 

Arguments:
$callback_name - the name of the call back
Then a maximum of 8 arguments, the first argument is always passed by reference 

I guess the best way would be to make the changes in the core first, and then port them to a plugin. Some hooks may be already there, while some other hooks may need to be added.

Returns:

true if a hook was executed. Your plugin can modify the first argument since
it is passed by reference.


*/
function JBPLUG_do_callback($callback_name, &$A) {
	global $_JB_PLUGINS;
	
	global $JB_callbacks; // initialized at the top of this file 
	// get the hooks list
	if (!isset($JB_callbacks[$callback_name])) return false; // no hooks are set
	$hooks = $JB_callbacks[$callback_name];

	if (sizeof($hooks) > 0) {
		foreach ($hooks as $hook) {

			switch (func_num_args()-1) {

				case 1:
					$_JB_PLUGINS[$hook['call'][0]]->$hook["call"][1]($A); // $A is passed by ref
					break;
				case 2:
					list($arg1) = array(func_get_arg(2));
					$_JB_PLUGINS[$hook['call'][0]]->$hook["call"][1]($A, $arg1);
				
					break;
				case 3:
					list($arg1, $arg2) = array(func_get_arg(2), func_get_arg(3));

					$_JB_PLUGINS[$hook['call'][0]]->$hook["call"][1]($A, $arg1, $arg2);
					break;
				case 4:
					list($arg1, $arg2, $arg3) = array(func_get_arg(2), func_get_arg(3), func_get_arg(4));
					$_JB_PLUGINS[$hook['call'][0]]->$hook["call"][1]($A, $arg1, $arg2, $arg3);
					break;
				case 5:
					list($arg1, $arg2, $arg3, $arg4) = array(func_get_arg(2), func_get_arg(3), func_get_arg(4), func_get_arg(5));
					$_JB_PLUGINS[$hook['call'][0]]->$hook["call"][1]($A, $arg1, $arg2, $arg3, $arg4);
					break;
				case 6:
					list($arg1, $arg2, $arg3, $arg4, $arg5) = array(func_get_arg(2), func_get_arg(3), func_get_arg(4), func_get_arg(5), func_get_arg(6));
					$_JB_PLUGINS[$hook['call'][0]]->$hook["call"][1]($A, $arg1, $arg2, $arg3, $arg4, $arg5);
					break;
				case 7:
					list($arg1, $arg2, $arg3, $arg4, $arg5, $arg6) = array(func_get_arg(2), func_get_arg(3), func_get_arg(4), func_get_arg(5), func_get_arg(6), func_get_arg(7));
					$_JB_PLUGINS[$hook['call'][0]]->$hook["call"][1]( $A, $arg1, $arg2, $arg3, $arg4, $arg5, $arg6);
					break;
				case 8:
					list($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7) = array(func_get_arg(2), func_get_arg(3), func_get_arg(4), func_get_arg(5), func_get_arg(6), func_get_arg(7), func_get_arg(8));
					$_JB_PLUGINS[$hook['call'][0]]->$hook["call"][1]($A, $arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7);
					break;
				default:
					$_JB_PLUGINS[$hook['call'][0]]->$hook["call"][1]();
					break;

			}
		

		}
		
		return true;

	}
	return false; // no callbacks were registered
	
	
}

##################################################################
/*

The old callback method, pre 3.6.5

function JBPLUG_do_callback2($callback_name, &$A) {
	global $_JB_PLUGINS;
	
	global $JB_callbacks; // initialized at the top of this file 
	// get the hooks list
	if (!isset($JB_callbacks[$callback_name])) return false; // no hooks are set
	$hooks = $JB_callbacks[$callback_name];

	
	// build the argument variables, skip the first argument
	for ($i=1; $i < func_num_args(); $i++) {
		
		$argn = chr(64+$i); // create the arg name
		$$argn = func_get_arg($i);
		
		
		$args .= $comma.'$'.$argn;
		$comma = ', ';
	}
	

	if (sizeof($hooks) > 0) {
		foreach ($hooks as $hook) {

			if ($args!='') { // the callback has some arguments, use eval()
				// assuming $args are always $A, $B, $B, ..
				
				$hook['call'][0] = str_replace('"', '', $hook['call'][0]); // escape before eval
				$hook['call'][1] = str_replace(';', '', $hook['call'][1]); // cannot inject any other code
				$the_call = '$_JB_PLUGINS["'.$hook['call'][0].'"]->'.$hook["call"][1].'('.$args.');';
				
				eval ($the_call);

			} else { // the callback has no arguments, use function variables
				$_JB_PLUGINS[$hook['call'][0]]->$hook["call"][1]();
				//call_user_func ($hook['call']); // alternative way to call
			}

		}
		
		//return true;

	}
	return false; // no callbacks were registered
	
	
}
*/
##################################################################
# Register a callback and sort it by priority
# Each place on the code where a callback is registered is called a 'hook'
function JBPLUG_register_callback ($callback_name, $call, $priority=0) {

	global $JB_callbacks;
	// there could be several functions for each callback
	$hook['call'] = $call;
	$hook['priority'] = $priority;
	$JB_callbacks[$callback_name][] = $hook;
	// sort the callback list according to priority
	usort($JB_callbacks[$callback_name], "JBPLUG_comp_priority");
	
}

function JBPLUG_comp_priority($a, $b) {
        return strnatcasecmp($a["priority"], $b["priority"]);
}


########################################
# Call clear_cache for all enabled plugins
function JBPLUG_clear_cache() {
	global $_JB_PLUGINS;
	foreach ($_JB_PLUGINS as $plugin) {
		$plugin->clear_cache();
	}

}
########################################

function JBPLUG_list_plugins() {

	global $_JB_PLUGINS;


	?>
	
	<table width="100%">
	<tr><td valign="top">
	<table width="100%" border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" width="400" >
	<tr bgColor="#eaeaea">
	<td><b><font size="2">Plugin Name</b></font></td>
	<td><b><font size="2">Description</b></font></td>
	<td><b><font size="2">Author</b></font></td>
	<td><b><font size="2">Version</b></font></td>
	<td><b><font size="2">Status</b></font></td>
	<td><b><font size="2">&nbsp;</b></font></td>
	</tr>
	<?php

		foreach ($_JB_PLUGINS as $obj_key => $plugin) {
			?>
			<tr <?php if ($obj_key==$_REQUEST['plugin'])  { echo ' bgColor="#FFFF99" ';} else echo ' bgColor="#ffffff" '; ?> onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);">
			<td><?php echo $plugin->get_name(); ?></td>
			<td><?php echo $plugin->get_description(); ?></td>
			<td><?php echo $plugin->get_author(); ?></td>
			<td><?php echo $plugin->get_version(); ?></td>
			<td><?php 
				if ($plugin->is_enabled()) {
						echo "<font color='green'><IMG SRC='../admin/active.gif' WIDTH='16' HEIGHT='16' BORDER='0' ALT='Enabled'></font>";

					} else {
						echo "<font color='red'><IMG SRC='../admin/notactive.gif' WIDTH='16' HEIGHT='16' BORDER='0' ALT='Not Enabled'></font>";

					}
				?></td>
			<td><?php

					if ($plugin->is_enabled()) {
					//	echo "Enabled";
						echo "<input type='button' style='font-size: 10px;' value='Disable' onclick=\"if (!confirmLink(this, 'Disable, are you sure?')) return false;window.location='".$_SERVER['PHP_SELF']."?plugin=".$obj_key."&action=disable'\">";
						echo "<input style='font-size: 10px;' type='button' value='Configure' onclick=\"window.location='".$_SERVER['PHP_SELF']."?plugin=".$obj_key."'\">";

					} else {
						//echo "Not Enabled";
						echo "<input style='font-size: 10px;' type='button' value='Enable' onclick=\"if (!confirmLink(this, 'Enable, are you sure?')) return false; window.location='".$_SERVER['PHP_SELF']."?plugin=".$obj_key."&action=enable'\">";

					}


					
				?></td>
			</tr>
			<?php

		}
		?>
		</table>
		</td>
		<td valign="top">
		<?php
			if ($_REQUEST['plugin']) {
				$_JB_PLUGINS[$_REQUEST['plugin']]->config_form();
			}
		
		?>
		</td>
		</table>
		
		<?php
}

/*
Note that for JBPLUG_save_config_variables($class_name), the $class_name must match the name of the plugin that it was called from. The plugin's name must also match the plugin's class name. The plugin must be enabled.

*/
function JBPLUG_save_config_variables($class_name) {

	global $_JB_PLUGINS;
	global $_JB_PLUGIN_CONFIG;

	if (!is_object($_JB_PLUGINS[$class_name])) {
		die( 'Operation aborted. A plugin called '.jb_escape_html($class_name).' does not exist. Please verify your $class_name argument when calling JBPLUG_save_config_variables()');
	}

	for ($count=1; $count < func_num_args(); $count++) {
		
		$vars[func_get_arg($count)] = trim(stripslashes($_REQUEST[func_get_arg($count)]));
	}
	if (JB_PLUGIN_CONFIG!='') {

		$config = $_JB_PLUGIN_CONFIG;
	}

	$config[$class_name] = $vars;

	$config = serialize($config);
	JBPLUG_update_plugin_config($config);
	$_JB_PLUGINS[$class_name]->clear_cache();


}

function JBPLUG_load_config_variables($class_name) {

	for ($count=1; $count < func_num_args(); $count++) {

		echo func_get_arg($count)."<br>";

	}


}

function JBPLUG_append_english_default_labels() {

	global $_JB_PLUGINS;
	global $label;

	foreach ($_JB_PLUGINS as $class => $obj) {
		
		$file = dirname(__FILE__)."/plugins/$class/lang/english_default.php";
		if (file_exists($file)) {
			require ($file);
		} else {
			//echo "does not exist: ".$file."<br>";
		}

	}
	
}

////

function JBPLUG_append_english_default_source(&$source_code) {

	global $_JB_PLUGINS;

	foreach ($_JB_PLUGINS as $class => $obj) {

		$file = dirname(__FILE__)."/plugins/$class/lang/english_default.php";
		
		if (file_exists($file)) {
			$handle = fopen($file, "rb");
			while ($buffer= fgets($handle, 4096)) {
				if (preg_match ('#\$label\[.([a-z0-9_]+).\].*#i', $buffer, $m)) {
					$source_code[$m[1]] = $buffer;
				}
			}
		}

	}
}

///////////////
/*
This function is similar to JB_merge_language_files(), but it merges only
the language files found in the plugin directories.

*/
function JBPLUG_merge_english_default_files() {

	JBPLUG_require_all_plugins();

	global $_JB_PLUGINS;
	$source_label = array();


	// load in the main english_default labels
	include_once (jb_get_english_default_dir()."english_default.php"); // the master lang/english_default
	$source_label = array_merge ($source_label, $label); // default english labels
	$label = array();
	$last_mtime = filemtime (jb_get_english_default_dir()."english_default.php");
	
	$sql = "SELECT * FROM lang  ";
	$result = JB_mysql_query ($sql);


	// Now merge the english_default.php strings with the language files

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		
		echo "Merging language strings for ".jb_escape_html($row['lang_filename'])."..<br>";
		/// for each of the plugins, load in the plugin's english default
		foreach ($_JB_PLUGINS as $class => $obj) {

			$plugin_english_default_path =  dirname(__FILE__)."/plugins/$class/lang/english_default.php";
			
			if (!file_exists($plugin_english_default_path)) {
				continue; // skip
			}

			

			include_once ($plugin_english_default_path); // load the labels from the plugins
			$source_label = array_merge ($source_label, $label);
			
			$label = array();
			$m_time = filemtime ($plugin_english_default_path);
			if ($m_time > $last_mtime) {
				
				$last_mtime = $m_time;
			}

		}

		// now that we have all the source labels, we can merge them with
		// the langauge file. Any key that is present in the source, but
		// not present in the language file then we merge it.

		if (is_writable(jB_get_lang_dir().$row['lang_filename'])) {

			if ($last_mtime > filemtime (jB_get_lang_dir().$row['lang_filename'])) {

				
				// Now merge the english defaults with the langauge file
				include (jB_get_lang_dir().$row['lang_filename']); // customized labels
				$dest_label = array_merge($source_label, $label);
				$label = array();
				// write out the new file:
				$out = "<?php\n";
				$out .= "///////////////////////////////////////////////////////////////////////////\n";
				$out .= "// IMPORTANT NOTICE\n";
				$out .= "///////////////////////////////////////////////////////////////////////////\n";
				$out .= "// This file was generated by a script!\n";
				$out .= "// (JBPLUG_merge_english_default_files() function)\n";
				$out .= "// Please do not edit the language files by hand\n";
				$out .= "// - please always use the Language Translation / Editing tool found\n";
				$out .= "// in Admin->Languages\n";
				$out .= "// To add a new phrase for the \$label, please edit english_default.php, and\n";
				$out .= "// then vist Admin->Main Summary where the language files will be\n";
				$out .= "// automatically merged with this file.\n";
				$out .= "///////////////////////////////////////////////////////////////////////////\n";
				foreach ($dest_label as $key=>$val) {
					$val = str_replace("'", "\'", $val );
					$out .= "\$label['$key']='". JB_clean_str($val)."'; \n";
				}
				$out .= "?>\n"; 	
				$handler = fopen (jB_get_lang_dir().$row['lang_filename'], "w");
				fputs ($handler, $out);
				fclose ($handler);
			}

		} else {
			echo "<font color='red'><b>- ".jB_get_lang_dir().$row['lang_filename']." file is not writable. Give write permissions (".decoct(JB_NEW_FILE_CHMOD).") to ".jB_get_lang_dir().$row['lang_filename']." file and then disable & re-enable this plugin</b></font><br>";
		}
		
		echo " Done.<br>";

	}


}

#############################################

function JBPLUG_call_plugin_method($plugin_name, $method_name, $args=array()) {

	$plugin_name = preg_replace('/[^a-z^0-9^_^-]+/i', '', $plugin_name);
	$method_name = preg_replace('/[^a-z^0-9^_^-]+/i', '', $method_name);
	if (!is_object($_JB_PLUGINS[$plugin_name])) {
		$plugin_file = dirname(__FILE__).'/plugins/'.$plugin_name;
		if (file_exists($file)) {
			include_once($plugin_file);
		}
	} else {
		if (!empty($args)) {
			return call_user_func_array (array($_JB_PLUGINS[$plugin_name], $function), $args); 
		} else {
			return call_user_func (array($_JB_PLUGINS[$plugin_name], $function));
		}
	}
	return null;

}

?>