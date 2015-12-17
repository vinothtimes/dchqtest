<?php

/*

Base class for all markup classes.

The markup in this class is shared accross all sections
of the job board

Note: This file as it is most likely
to change in the future between new versions. 

To change the look and feel, it is better
to start with template files such as index-header.php, index-main.php
index-footer.php



*/


class JBMarkup {

	
	var $charset; // string

	var $menu_switch; // boolean, true if menu is on the page
	var $menu_type='JS';
	var $overlib_switch;
	var $wz_dragdrop_switch;
	var $jquery_switch;
	var $application_switch;
	var $colorbox_switch;
	

    var $handlers;


	function JBMarkup() {

		//$this->doc_type = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">'."\n";
		// standards mode:

		$this->doc_type = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';

	}

	// Javascript menu
	function enable_menu() {
		$this->menu_switch=true;
		$this->enable_jquery(); // javascript menu uses jquery to initialize
	}

	// listing resumes, mouseover photo
	function enable_overlib() {
		$this->overlib_switch=true;
	}

	// moving the pin on the map
	function enable_wz_dragdrop() {
		$this->wz_dragdrop_switch=true;
	}

	function enable_jquery() {
		$this->jquery_switch=true;
	}

	// job applications
	function enable_applications() { 
		$this->application_switch=true;
		
	}

	// modal dialog box plugin for jquery
	function enable_colorbox() {
		$this->colorbox_switch=true;
	}


	function get_doctype() {

		return $this->doc_type."\n";

	}

	function set_doctype($str) {
		$this->doc_type();
	}


	function get_admin_doctype() {

		echo $this->doc_type;

	}

	function markup_open() {
		echo '<html>'."\n";
	}

	function markup_close() {
		echo '</html>';
	}


	function body_open($attributes='') {

        
        if($this->trigger_handler('onload_function')) {
            $attributes .= ' onload="init_onload();" ';
        }
        
		if ($attributes) {
			echo '<body '.$attributes.'>'."\n";
		} else {
			echo '<body>'."\n";
		}
		if ($this->overlib_switch) { 
			?>
			<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000; "></div>
			<?php
		}

        echo $this->trigger_handler('body_after_open');
		
		
	}

	function body_close() {


		if ($this->menu_switch) {

			// init the Javascript menu for employers / candidates
			// The menu uses jQuery to set the event handlers
			// The first sets up the menu when the page is loaded
			// The second re-sizes the menu when the window is re-sized
			// This code is also used by the InfoPages plugin

			if ($this->menu_type=='JS') {

				$MM = &get_JBMenuMarkup_object();
				$MM->before_body_close(1);
			}
		}

        
        echo $this->trigger_handler('before_body_close');

	
		JBPLUG_do_callback('before_body_close', $this); // plugins authors can have their plugins insert some code here before the closing body tag.
		echo '</body>'."\n";

	}

	function head_open() {

		/*
		Older templates do not call $JBMarkup->head_open();
		Therefore, $this->head_opened is used to track if the template called
		head_open()
		*/

		$this->head_opened = true; // for compatibility with older templates.
		

		echo '<head>'."\n";

		if ($this->menu_switch) {

			// this section resided at the top of employers-header.php prior to 3.6

			if ((JB_EMPLOYER_MENU_TYPE == 'JS') || (JB_CANDIDATE_MENU_TYPE == 'JS')) {
				$MM = &get_JBMenuMarkup_object();
				$MM->header();
			}

		}
		if ($this->overlib_switch) {

			// This used for the resume list to pop-up a thumnail over the list
			// Resided in employers-header.php and candidates-header.php prior to 3.6
			?>
			<script type="text/javascript" src="<?php echo jb_get_overlib_js_src(); ?>"><!-- overLIB (c) Erik Bosrup --></script>
			<?php
		}
		if (($this->wz_dragdrop_switch) && (JB_MAP_DISABLED!='YES')) {
			// This used for dragging a pin layer over a map
			?>
			<script type="text/javascript" src="<?php echo jb_get_WZ_dragdrop_js_src(true); ?>"></script>
			<?php
		}
		if ($this->jquery_switch) {
			?>
			<script type="text/javascript" src="<?php echo jb_get_JQuery_src(); ?>"></script>
			<?php

		}

		if ($this->application_switch) {

			// script for the 'apply' button on the post
			?>
<script type="text/javascript">
function showDIV(obj, source, bool) {
	obj.setAttribute("style", "display: none", 0);
	if (bool == false) {
	  
		document.getElementById (source).innerHTML=document.getElementById('app_form').innerHTML;
		document.getElementById ('app_form').innerHTML=document.getElementById('app_form_blank').innerHTML;
	}
	else {
	 
		obj.innerHTML =
		document.getElementById(source).innerHTML;
		obj.setAttribute("style", "display: block", 0);

	}

	return bool;

}
</script>

		<?php

			


		}

		if ($this->colorbox_switch) {
			?>

<script type="text/javascript" src="<?php echo JB_BASE_HTTP_PATH; ?>include/lib/colorbox/jquery.colorbox-min.js"></script>

			<?php
		}

		?>
<script type="text/javascript" src="<?php echo JB_get_common_js_url(); ?>"></script>
		<?php

		
		JBPLUG_do_callback('extra_header', $this); // plugins can include their own javascript in to the header here

	
        echo $this->trigger_handler('header');

		if ($on_load = $this->trigger_handler('onload_function')) {

			?>
<script type="text/javascript">
	function init_onload() {
		<?php
		
            echo $on_load;
            
        ?>

	}
</script>
			<?php

		}
		
	}




	function head_close() {
		echo '</head>'."\n";
	}

	function base_meta_tag($base_url=JB_BASE_HTTP_PATH) {
		?>
		<base href="<?php echo $base_url; ?>">
		<?php
	}

	function charset_meta_tag($charset='iso-8859-1') {
		
		echo '<meta http-equiv="Content-Type" content="text/html; charset='.htmlentities($charset).'" >'."\n";

	}

	function refresh_meta_tag($url, $seconds=2) {

	?><META HTTP-EQUIV="Refresh" CONTENT="<?php echo $seconds;?>; URL=<?php echo htmlentities($url); ?>">
	<?php

	}

	function no_robots_meta_tag() {

		?>
		<meta name="robots" content="noindex,nofollow"> 
		<?php

	}

	function stylesheet_link($url) {

		$this->link_tag('stylesheet', $url, 'text/css');

	}

	function link_tag($rel, $href, $type='') {

		if ($type) {
			$type = ' type="'.$type.'" ';

		}
		?>
		<link rel="<?php echo $rel?>" <?php echo $type; ?> href="<?php echo $href; ?>" >
		<?php


	}

	function title_meta_tag($title) {
		echo '<title>';
		echo jb_escape_html($title);
		echo '</title>'."\n";
	}

	function get_stript_include_tag($script_src) {
		return '<script type="text/JavaScript" src="'.$script_src.'"></script>'."\n";
	}


	function meta_tag($name, $content) {

		?>
		<meta name="<?php echo $name; ?>" content="<?php echo jb_escape_html($content); ?>">
		<?php

	}

	function get_error_line($msg) {
		$msg = str_replace('<br>', '', $msg); // early versions have <br> in the label
		$msg = str_replace('*', '-', $msg);
		return '<span class="error_line">'.$msg.'</span><br>'."\n";
	}

	function ok_msg($msg) {
		?><p class="ok_msg_label"><?php echo $msg; ?></p>
		<?php

	}

	function error_msg($msg) {
		?>
		<p class="error_msg_label"><?php echo $msg; ?></p>
		<?php
	}

	function input_hidden($name, $value) {
		?>
		<input type="hidden" name="<?php echo $name; ?>" value="<?php echo jb_escape_html($value); ?>">
		<?php

	}


	function available_langs_heading() {
		global $label;
		
		?>
		<span class="available_langs"><?php echo $label['available_languages'];?></span><br>
		<?php


	}

	function available_langs_item($lang_code, $lang_name) {
		?>
		<a href="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?lang=<?php echo jb_escape_html($lang_code)?>"><img src="lang_image.php?code=<?php echo $lang_code; ?>" border=0 alt="<?php echo jb_escape_html($lang_name); ?>"></a> &nbsp
		<?php

	}

	

	function escape($str) {
		return JB_escape_html($str);
	}

	function get_line_break() {
		return '<br>';
	}

	function line_break() {
		echo $this->get_line_break();
	}
    
    
    /*
    
    Function
    
    set_handler
    
    Description
    
    Used to set a callback for a handler, so that other parts of the code
    can add their custom HTML to the document whenever needed.
    
    See function trigger_handler()
    
    Arguments
    
    $handler_name - string
    
    $object - an object to call back
    
    $method - method to call back on the $object 
    
    $arg - argument to pass (optional)
    
    
    
    */
    
    
    function set_handler($handler_name, &$object, $method, $arg='') {
        $this->handlers[$handler_name]['obj'][] = &$object;
        $this->handlers[$handler_name]['method'][] = $method;
        $this->handlers[$handler_name]['arg'][] = $arg;
    }
    
    
    /*

	Function

	trigger_handler

	Description

	This is used to affect the output of markup, in relation to <body> 
	<head> and </body> structure tags in the document. It is useful for 
	appending javascript and other meta tags, depending on what type of page
	is being presented. You will notice that this source file is peppered
    with trigger_handler() handler calls.
    
    It works in conjunction with the set_handler() method - the set_handler()
    is used to set the callbacks.
 
    
    An example of usage is in the JBDynamicForm class, where the handlers are
    set to call back get_extra_markup() method. This method will then return any
    additional HTML which is needed to render the form.
    
    Another example is in gmap_iframe.php - the handlers are set to callback
    
    The advantage of this system is that script files and other
    external resources do not need to be included unless they are needed.

	Arguments

	$handler_name can be the following:

	'body_after_open' - append the markup immediatelly after the <body> tag

	'header' - append the markup between the <head></head> tags

	'before_body_close' - Insert the contents of $markup just before the 
	</body> tag.

	'onload_function' - Used to add javascript to the init_onload() function
	

	Returns

	A string of the generated HTMLs


	*/
    function trigger_handler($handler_name) {
        
        // $this->handlers is our callback hash table
        // The $handler_name is used for the key for the handler
        // Each handler can have a list of callbacks. Each callback
        // has a refrence to an object, which method to call and an
        // optional argument to pass. The function calls all the callbacks
        // registered to the handler before returning the result as a string.
      
        $result = '';
       
        if (is_array($this->handlers[$handler_name]['obj'])) {
             
            for ($i=0; $i < sizeof ($this->handlers[$handler_name]['obj']); $i++) { 
                if (!isset($this->handlers[$handler_name]['arg'][$i])) { 
                    $result .= call_user_func(
                        array(
                            $this->handlers[$handler_name]['obj'][$i], 
                            $this->handlers[$handler_name]['method'][$i]
                            )
                        );
                } else {
                    $result .= (
                        call_user_func(
                            array(
                                $this->handlers[$handler_name]['obj'][$i],
                                $this->handlers[$handler_name]['method'][$i]
                            ),
                            $this->handlers[$handler_name]['arg'][$i]
                        )
                    );
                }
            }
        }
        
        return $result;
        
    }

}