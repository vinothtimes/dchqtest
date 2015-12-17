<?php

# Important:
# At the bottom if the file, this statement should exist.
//$_JB_PLUGINS['OnlineUsers'] = new OnlineUsers; // add a new instance of the class to the global plugins array


/*
Conventions: 

- Always name your class starting with capital letters.
- The file name of the class should be the same as the directory name and file name
- Always register your callbacks in the constructor!
- Use this plugin as a starting point for your own plugin
#############################################################################
# SECURITY NOTICE                                                           #
#############################################################################
# - IMPORTANT: Always escape SQL values before putting them in to a query   #
# use the jb_escape_sql() function on ALL values                            #
#                                                                           #
# eg.                                                                       #
#                                                                           #
# $sql = "SELECT * FROM test where id='".jb_escape_sql($some_id)."' ";      #
#                                                                           #
# - IMPORTANT: Be sure to scape data before outputting it to the page       #
# use the jb_escape_html() function, and be sure to escape                  #
# $_SERVER['PHP_SELF'] with htmlentities()                                  #
#                                                                           #
# eg.                                                                       #
#                                                                           #
# echo jb_escape_html($_REQUEST['some_value']);                             #
#                                                                           #
# echo htmlentities($_SERVER['PHP_SELF']);                                  #
#                                                                           #
# - Always sanitize input to be used in functions such as                   #
# fopen(), eval(), system()                                                 #
# eg.                                                                       #
# $file_name = preg_replace ('/[^a-z^0-9]+/i', "", $_REQUEST['file_name']); #
# $fh = fopen($file_name, r);                                               #
#                                                                           #
#############################################################################
*/

class OnlineUsers extends JB_Plugins {

	var $config;
	var $plugin_name;

	function OnlineUsers() {

		$this->plugin_name = "OnlineUsers"; // set this to the name of the plugin. Case sensitive. Must be exactly the same as the directory name and class name

		parent::JB_Plugins(); // initalize JB_Plugins

		// Prepare the config variables
		// we simply extract them from the serialized variable like this:

		if ($this->config==null) { // older versions of jamit did not init config
			$config = unserialize(JB_PLUGIN_CONFIG);
			$this->config = $config[$this->plugin_name];
		}

		# initialize the priority
		if ($this->config['priority']=='') {
			$this->config['priority']=10;
		}

		if ($this->config['frame_border']=='') {
			$this->config['frame_border']='NO';
		}
		

		if ($this->is_enabled()) {
			// register all the callback
			// Here we assign the show_status() method to the 'side_bar_bottom' callback hook
			// this callback hook can be found in the index-sidebar.php theme file.
			// $this->config['priority'] stores the piority for this plugin
			///////////////////////////////////////////


			JBPLUG_register_callback('index_sidebar_top', array($this->plugin_name,'show_stats'), $this->config['priority']);


			///////////////////////////////////////////
			// Note the method to call is being passed as an array like this:
			// array('OnlineUsers','show_stats')
			// The StatsBox is the name of the class and the show_stats is 
			// the method to call

			
			
		}

	}

	///////////////////////////////////////////
	// Here is the method that we registered in the constructor above.
	// It does all the work for this plugin.
	// You can also create many other methods and register them in the constructor.

	function show_stats() { // this is a function called back from the hook, initialized on the StatsBox() constructor
		global $label; // Global variable where all the labels are kept. Add any custom labels to english_default.php and use the language Editing/Translation tool in the Admin to translate or edit the label.
		// get the number of sessions.
		$sql = "SELECT count(*) FROM `jb_sessions` ";
		$result = JB_mysql_query($sql);
		$row = mysql_fetch_row($result);
		$sessions = $row[0];

		if ($sessions < $this->config['users_min']) return;

		// Substitute the %SESSIONS% tag in the label for the session number
		$label['OnlineUsers_online_p'] = str_replace('%SESSIONS%', $sessions, $label['OnlineUsers_online_p']); // plural
		$label['OnlineUsers_online_s'] = str_replace('%SESSIONS%', $sessions, $label['OnlineUsers_online_s']); // singular
		
		// you can call any of the functions defined by the job board:

		//echo "<p >";
		if ($this->config['frame_border']=='YES') {
			JB_render_box_top($width="98", $label['OnlineUsers_heading'], $body_bg_color='#ffffff');
		}

		if ($sessions == 1) {
			echo $label['OnlineUsers_online_s']; // singular
		} else {
			echo $label['OnlineUsers_online_p']; // plural

		}
		if ($this->config['frame_border']=='YES') {
			JB_render_box_bottom();
		}

		//echo "</p>";

	

	}

	function get_name() {
		return "Online Users";

	}

	function get_description() {

		return "Displays the number of users online on the side-bar";
	}

	function get_author() {
		return "Jamit Software";

	}

	function get_version() {
		return "1.1";

	}

	function get_version_compatible() {
		return "3.0.0+";

	}

	# Check the JB_ENABLED_PLUGINS constant to see if this plugin is enabled.
	# Each plugin must have this method implemented in the following way:
	function is_enabled() {

		if (JB_ENABLED_PLUGINS!='') {
			$enabled_plugins = explode(',', JB_ENABLED_PLUGINS);
			if (in_array($this->plugin_name, $enabled_plugins)) {
				return true;
			}
			return false;
		}

	}
	# Enable the plugin. Call the parent class.
	# Each plugin must have this method implemented in the following way:
	function enable() {
		if (!$this->is_enabled()) {
			parent::enable($this->plugin_name);
		}

	}

	# Disable the plugin.
	# Each plugin must have this method implemented in the following way:
	function disable() {
		if ($this->is_enabled()) {
			parent::disable($this->plugin_name);
		}
	}


	# display the configuration form
	# You may design your form however you like!
	# Please make sure the it sends the following hidden fields:
	# type="hidden" name="plugin" 
	# type="hidden" name="action" 
	# You can access the config variables like this: $this->config['users_min']

	function config_form() { // 
		 ?>
		<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
		<table border="0" cellpadding="5" cellspacing="2" style="border-style:groove" id="AutoNumber1" width="100%" bgcolor="#FFFFFF">
		<tr>
			<td   bgcolor="#e6f2ea">
				<b>How many users must be online before displaying:</b></td>
			<td  bgcolor="#e6f2ea"><input size="2" type="text" name='users_min' value="<?php echo $this->config['users_min']; ?>">
			</td>
		</tr>
		<tr>
			<td bgcolor="#e6f2ea">
				<b>Frame Border?</b></td>
			<td  bgcolor="#e6f2ea">
			 <input type="radio" name="frame_border" value="YES" <?php if ($this->config['frame_border']=='YES') { echo " checked "; } ?>>Yes. This will put the plugin in an Info Box frame using the info-box-*.php template.  <br>
	  
      <input type="radio" name="frame_border" value="NO" <?php if ($this->config['frame_border']=='NO') { echo " checked "; } ?>>No.
			</td>
		</tr>
		<tr>
			<td  width="20%" bgcolor="#e6f2ea">
				<b>Priority</b></td>
			<td  bgcolor="#e6f2ea"><input size="3" type="text" name='priority' value="<?php echo $this->config['priority']; ?>"> (Input a number. Eg 1 = execute this plugin in 1st position)
			</td>
		</tr>
		<tr>
			<td  bgcolor="#e6f2ea" colspan="2"><font face="Verdana" size="1"><input type="submit" value="Save">
		</td>
		</tr>
		</table>
		<input type="hidden" name="plugin" value="<?php echo jb_escape_html($_REQUEST['plugin']);?>">
		<input type="hidden" name="action" value="save">

		</form>
		 <?php

	}

	# save the values from your config form
	# The values will be serialized and saved in config.php
	# After the $this->plugin_name parameter, enter the list of variables like this:

	function save_config() {
		# JBPLUG_save_config_variables ( string $class_name [, string $field_name [, string $...]] )
		JBPLUG_save_config_variables($this->plugin_name, 'users_min', 'priority', 'frame_border');
	}


	
	
	

	

}

$_JB_PLUGINS['OnlineUsers'] = new OnlineUsers; // add a new instance of the class to the global plugins array

?>