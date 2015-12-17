<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################

// Cosmodemonic Telegraph Company
///////////////////////
// The purpose of xmlFeedFieldParser is to parse the sample
// XML file, and map the XML feed to an array $this->data
// Does not attempt to do anything else with $data, just needed to display
// the keys of the fields as an option list when mapping the database
// fields to the keys.
/*

For example, after parsing, $this->data will look like something like this:

 [jamit|jamitKey] => Array
        (
            [attr] => Array
                ([0] => Array( ))
            [data] => Array
                ([0] => hjds73d )
        )
    [jamit|jobsFeed] => Array
        (
            [attr] => Array
                ([0] => Array ())
            [data] => Array
                ([0] =>)
        )

- Each path has a compund key, eg. jamit|jobsFeed
- Each path has an associative array of attr and data which are arrays
- Foe each new record, the data is appended to attr and data arrays

*/
class xmlFeedFieldParser {

    // XML parser variables
    
    
	var $parser;

	
	var $xml_strings;
	var $char_data = array();
	var $data = array(); // the resulting array

	// keep track of where we are
	var $key_stack = array();
	var $line;
	var $depth; // dont rally need it, but handy for debugging!
	


    // function with the default parameter value
    function xmlFeedFieldParser($xml_sample_str) {
		
        $this->xml_strings = explode("\n", $xml_sample_str);
        $this->url  = $url;
		$this->line = 0;
		$this->depth = 0;
        $this->parse();

    }
  
    // parse XML data
    function parse()
    {
        $data = '';
        $this->parser = xml_parser_create ("UTF-8");
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'startXML', 'endXML');
        xml_set_character_data_handler($this->parser, 'charXML');
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		
		$line_count = sizeof($this->xml_strings);
		$i=0;
		$last_line=false;

		
		foreach ($this->xml_strings as $line) {


			if (!xml_parse($this->parser, $line, $last_line)) {
				$this->error(sprintf('an XML error at line %d column %d ',
				xml_get_current_line_number($this->parser),
				xml_get_current_column_number($this->parser)));
			}
			$i++;

		}

    }

    function startXML($parser, $name, $attr)    {

		$this->depth++;
		$this->key_stack[] = $name; // push name on the stack
		
		$key = implode('|', $this->key_stack);
		$this->data[$key]['attr'][] = $attr;
        
    }

    function endXML($parser, $name)    {

		// Implode char data gathered by charXML() for the
		// element which is currently the ending element.
		// The char data needs to be converted from
		// from UTF-8 in to Latin 1 the char data
		$key = implode('|', $this->key_stack);
		if (is_array($this->char_data[$key])) {
			$data = implode('',$this->char_data[$key]);
		}
		
		$this->char_data[$key]=array(); // clear the char data buffer
		
		// convert from UTF-8 to Latin-1 & HTML Entities
		$data = JB_utf8_to_html($data);

		$this->data[$key]['data'][] = $data;

		// now we can pop the 
		// element off the stack and decrease depth
		// to keep track of where we are in the document tree
		array_pop($this->key_stack);
		
		$this->depth--;

    }

    function charXML($parser, $data)    {

		$data = trim($data);
	
		
		// this function can be called a few
		// times between xml elements, and we never
		// know if its the last time... So we place
		// the $data on a stack, and this stack is
		// imploded when endXML() is called.
		// $this->key_stack keeps track of where we are
		// in the document tree, and is imploded to
		// get the key of the array.
		
		$key = implode('|',$this->key_stack);
		$this->char_data[$key][] = $data;
	
			
    }

    function error($msg)    {
        echo "<div align=\"center\">
            <font color=\"red\"><b>Error: $msg</b></font>
            </div>";
        exit();
    }

	
}


///////////////////////

/*

The purpose of this parser is to display the sample XML feed in a pretty
HTML tree with some radio buttons so that the administrator
can identify the sequence element.

*/

class xmlFeedStructForm {

    // XML parser variables
    var $parser;
    var $name;
    var $attr;
    var $data  = array();
    var $stack = array();
    var $keys;
    var $path;
	var $line;
	var $start_name;
	var $depth;

	var $key_stack;
	var $popped_key;
	var $pushed_key;

    // either you pass url atau contents.
    // Use 'url' or 'contents' for the parameter
    var $xml_strings = array();

    // function with the default parameter value
    function xmlFeedStructForm($xml_sample_str) {
        $this->xml_strings = explode("\n", $xml_sample_str);
        $this->url  = $url;
		$this->line = 0;
		$this->depth = 0;
        $this->parse();

    }
  
    // parse XML data
    function parse()
    {
        $data = '';
        $this->parser = xml_parser_create ("UTF-8");
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'startXML', 'endXML');
        xml_set_character_data_handler($this->parser, 'charXML');

        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		
		$line_count = sizeof($this->xml_strings);
		$i=0;
		$last_line=false;

		foreach ($this->xml_strings as $line) {

			if ($line_count==$i) {
				$last_line = true;
			}

			if (!xml_parse($this->parser, $line, $last_line)) {
				$this->error(sprintf('XML error at line %d column %d',
				xml_get_current_line_number($this->parser),
				xml_get_current_column_number($this->parser)));
			}
			$i++;

		}

   
    }

    function startXML($parser, $name, $attr)    {
		$this->depth++;
		$this->start_name=$name;

		$this->key_stack[] = $name; // push name on the stack
		$this->pushed_key = $name;

		echo "<br>\n";
		
		for ($i=0; $i<$this->depth; $i++) {
			echo '<span class="XMLelement">&nbsp;&nbsp;&nbsp;</span>';
		}
		
        $this->stack[$name] = array();
        $keys = '';
        $total = count($this->stack)-1;
        $i=0;
        foreach ($this->stack as $key => $val)    {
            if (count($this->stack) > 1) {
                if ($total == $i)
                    $keys .= $key;
                else
                    $keys .= $key . '|'; // The saparator
            }
            else
                $keys .= $key;
            $i++;
        }

		$my_key_stack = $this->key_stack;
		$i=0;

		for ($i=0; $i < sizeof($this->key_stack); $i++) {
			$key_str .= $pipe.array_shift($my_key_stack);
			$pipe = '|';
		}	
			
		?>
		<input type="radio" name="element" value="<?php echo htmlentities($key_str);?>" onclick="javascript:selectSeqElement('<?php echo htmlentities($name.$this->depth);?>')" >
		<?php

		
		echo "<div style='display:inline'   ><span id='".htmlentities($name.$this->depth)."' class='XMLelement'>&lt;$name&gt;</span>";
		echo '<font color="green">'.htmlentities($key_str).'</font>';
		
		$this->data[$keys]['attr'] = $attr;
        
        $this->keys = $keys;
    }

    function endXML($parser, $name)    {

		$this->popped_key = array_pop($this->key_stack);
		
		if (key($this->stack)!= '' ) {
			echo "<br>\n";

			for ($i=0; $i<$this->depth; $i++) {
				echo '<span class="XMLelement">&nbsp;&nbsp;&nbsp;</span></div>';

			}

		}

		end($this->stack);

		echo "<span id='".htmlentities($name.$this->depth)."_end' class='XMLelement'>&lt;/$name&gt;</span>";
		if ($this->start_name==$name)
		
        if (key($this->stack) == $name)
            array_pop($this->stack);
		$this->depth--;
    }

    function charXML($parser, $data)    {
        if (trim($data) != '')
        
			$this->data[$this->keys]['data'][] = trim(str_replace("\n", '', $data));
			
    }

    function error($msg)    {
        echo "<div align=\"center\">
            <font color=\"red\"><b>Error: ".htmlentities($msg)."</b></font>
            </div>";
        exit();
    }

	
}


///////////////////////////////
/*

Imports data from an XML file or stream

*/


class xmlFeedImporter {

    // XML parser variables

	var $parser;

	var $feed_id;
	
	//var $xml_strings;
	var $char_data = array();
	var $data = array(); // the resulting array

	// keep track of where we are
	var $key_stack = array();
	var $line;
	var $depth; // dont rally need it, but handy for debugging!
	
	var $fp; // opened file or stream or socket

	var $feed_row; // `xml_import_feeds` row

	var $import_error; // set to error message if error

	var $verbose; // boolean - print all errors/info if true

    // function with the default parameter value
    function xmlFeedImporter($feed_id) {

        $this->feed_id = $feed_id;
		$this->line = 0;
		$this->depth = 0;
		
		$this->feed_row = JB_XMLIMP_load_feed_row($feed_id);

		JBPLUG_do_callback('xml_import_parser_construct', $this);

		//require_once (dirname(__FILE__).'/xml_import_custom_functions.php');

		// open the file depending on pickup method.
		// If FTP or URL then pre-fetch it and open the local file
		switch($this->feed_row['pickup_method']) {
			case 'POST':
				// Read the raw input from stdin
				// http://www.php.net/wrappers.php
				// php://input is not available with enctype="multipart/form-data

				if (!$this->fp = fopen('php://input', 'rb')) {
					$this->set_import_error("Couldn't open input stream");
					return false;
				}

				// check the key
				
				if (trim($this->feed_row['feed_key'])!='') {
					$key = $_GET['key'];

					if ($key != $this->feed_row['feed_key']) {
						$this->set_import_error('Invalid key / key not received. Please make sure key is passed in the query string. eg. http://www.example.com/jb-xml-pickup.php?feed_id=1&key=something');
						return false;
					}
				}
				break;
			case 'FILE':
				if (file_exists($this->feed_row['feed_filename'])) {
					$this->fp = fopen($this->feed_row['feed_filename'], 'rb');
				} else {
					$this->set_import_error("File not found ".$this->feed_row['feed_filename']);
					return false;
				}
				break;
			case 'URL':
				$url = $this->feed_row['feed_url'];
				
				/*$url_arr = parse_url($url);
				
				$header .= "GET ".$url_arr['path']."?".$url_arr['query']." HTTP/1.1\r\n";
				$header .= "Host: ".$url_arr['host']."\r\n\r\n";
				if ($url_arr['scheme']=='http') {
					$port = 80;
				} elseif ($url_arr['scheme']=='https') {
					$port = 443;
					$url_arr['host'] = 'ssl://'.$url_arr['host']; // works only if openssl is compiled
				} else {
					$port = $url_arr['port'];
				}
				//if (!$this->fp = fsockopen ($url_arr['host'], $port, $errno, $errstr, 30)) {
				*/
				if (!$this->fp = fopen ($url, 'rb')) {
					
					$this->set_import_error('Cannot open URL '.$this->feed_row['feed_url']);
					return false;
				}
				
				break;
			case 'FTP':
					$ftp_server = trim($this->feed_row['ftp_host']);
					$ftp_user = trim($this->feed_row['ftp_user']);
					$ftp_pass = trim($this->feed_row['ftp_pass']);
					$remote_file = trim($this->feed_row['ftp_filename']);

					// set up a connection or error
					if (!$conn_id = ftp_connect($ftp_server)) {
						$this->set_import_error("FTP: Couldn't connect to $ftp_server"); 
						return false;
					}

					// try to login
					if (@ftp_login($conn_id, $ftp_user, $ftp_pass)) {

						// turn passive mode on
						ftp_pasv($conn_id, true);
						
						$s = md5(JB_SITE_NAME.time());
						if (function_exists('JB_get_cache_dir')) {
							$cache_dir = JB_get_cache_dir();
						} else {
							$cache_dir = JB_basedirpath().'cache/';
						}
						$temp_filename = $cache_dir.'import_temp_'.$s.'.xml';
					    if ($fp_temp = fopen($temp_filename, 'wb')) {
							if (ftp_fget($conn_id, $fp_temp, $remote_file, FTP_BINARY, 0)) {
								FCLOSE($fp_temp);
								$this->fp = fopen($temp_filename, 'rb');

							} else {
								$this->set_import_error("FTP: Couldn't download $remote_file\n");
								return false;
							}
							
						}
						ftp_close($conn_id);
					} else {
						$this->set_import_error("FTP: Couldn't login as $ftp_user\n");
						return false;
					}

					// close the connection
					ftp_close($conn_id);  

				break;
		}
		

		$this->FMD = &$this->feed_row['FMD'];
		

    }

	function parser_create($encoding='UTF-8') {

		$this->parser = xml_parser_create ($encoding);
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'startXML', 'endXML');
        xml_set_character_data_handler($this->parser, 'charXML');
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		JBPLUG_do_callback('xml_import_start', $this);

	}
  
    // parse XML data
    function import()  {

		require_once(dirname(__FILE__).'/posts.inc.php');
		require_once(dirname(__FILE__).'/employers.inc.php');

		if (!$this->fp) {
			echo wordwrap("File pointer failed to init. This may be due to a number of reasons - this indicates that the server cannot open a file to read from or open a connection to another server. If it is a file that you are importing from, please ensure that the server has permissions to read that file. If it is located on another server, please ensure that your server is allowed to make outbound connections to a remote server.");
			return false;
		}
        
		$state=0;
	
		while (!feof($this->fp)) {
			$chunk = fread($this->fp, 1024); // read in 8KB chunks (8192 bytes)
			if ($state==0 && trim($chunk)=='') {
				continue; // scan until it reacheas something
			} elseif ($state==0 && (strpos($chunk, '<?xml') !== false)) {
				// extract the encoding from the header
				preg_match('/encoding="([^"]+)"/i', $chunk, $m);
				$m[1] = strtoupper($m[1]);
				// PHP supports ISO-8859-1, US-ASCII and UTF-8.
				if (('ISO-8859-1' == $m[1]) || ('US-ASCII' == $m[1]) || ('UTF-8' == $m[1])) {
					$this->parser_create($m[1]); 
					xml_parser_set_option ( $this->parser , XML_OPTION_TARGET_ENCODING , $m[1] );
				} else {
					$this->parser_create();
					echo "<font color='red'>Warning: the parser does not support XML files encoded in ".jb_escape_html($m[1])." - please change the file to UTF-8.</font><br> ";
				}
				
				$chunk = preg_replace('#<\?xml.+?\>#i', '', $chunk); // remove the header(s) from the chunk
				
				$state=1;
				//continue; // skip and begin processing from the next line
			} elseif ($state==0) {
				// did not have any encoding header - UTF-8 is assumed
				$this->parser_create();
				$state=1; // beging processing from this line
			}

			
			
			if (!xml_parse($this->parser, $chunk, feof($this->fp))) {

				//echo htmlentities($chunk);

				$this->error(sprintf('XML error at line %d column %d [%s]',
				xml_get_current_line_number($this->parser),
				xml_get_current_column_number($this->parser), $chunk));
				
				fclose ($this->fp);
				return false;
			}
			
		}

        $this->update_counters();

		JBPLUG_do_callback('xml_import_end', $this);

		fclose ($this->fp);

    }

    function startXML($parser, $name, $attr)    {

		$this->depth++;
		$this->key_stack[] = $name; // push name on the stack
		
		$key = implode('|', $this->key_stack);
		$this->data[$key]['attr'][] = $attr;
        
    }

    function endXML($parser, $name)    {

		// Implode char data gathered by charXML() for the
		// element which is currently the ending element.
		// The char data needs to be converted from
		// from UTF-8 in to Latin 1 the char data
		$key = implode('|', $this->key_stack);
		
		if (is_array($this->char_data[$key])) {
			$data = implode('',$this->char_data[$key]);
		}
		
		$this->char_data[$key]=array(); // clear the char data buffer
		
		// convert from UTF-8 to Latin-1 & HTML Entities
		$data = JB_utf8_to_html($data);

		$this->data[$key]['data'] = $data;

		// if this is the ending sequence element, eg. end of the </job> record
		// then import the data
		if ($key == $this->FMD->seq) {
			
			JBPLUG_do_callback('xml_import_process_data', $this);
			$this->process_data();
		}

		// now we can pop the 
		// element off the stack and decrease depth
		// to keep track of where we are in the document tree
		array_pop($this->key_stack);
		
		$this->depth--;

    }

    function charXML($parser, $data)    {

		//$data = trim($data);
	
		
		// this function can be called a few
		// times between xml elements, and we never
		// know if its the last time... So we place
		// the $data on a stack, and this stack is
		// imploded when endXML() is called.
		// $this->key_stack keeps track of where we are
		// in the document tree, and is imploded to
		// get the key of the array.
		
		$key = implode('|',$this->key_stack);
		$this->char_data[$key][] = $data;
	
			
    }

    function error($msg)    {
        if ($this->verbose) {
			echo "<div align=\"center\">
            <font color=\"red\"><b>Error: ".htmlentities($msg)."</b></font>
            </div>";
		}
		$this->set_import_error("XML Parse error: $msg");
        return false;
    }

	function set_data_value($value, $field_id, $form_id=1) {

		if ($form_id==1) {
			$map = &$this->FMD->getOption('job_map'); // job post mappings
		} elseif ($form_id==4) {
			$map = &$this->FMD->getOption('account_map'); // employer's account mappings
		}

		$this->data[$map[$field_id]['element']]['data'] = $value;

	}

	/* 
		Get the raw data value without any other post-processing
	*/

	function get_raw_data_value($field_id, $form_id=1) {
		if ($form_id==1) {
			$map = &$this->FMD->getOption('job_map'); // job post mappings
		} elseif ($form_id==4) {
			$map = &$this->FMD->getOption('account_map'); // employer's account mappings
		}
		$val = $this->data[$map[$field_id]['element']]['data'];

		return $val;

	}


	function get_data_value($field_id, $form_id=1) {

	
		if ($form_id==1) {
			$map = &$this->FMD->getOption('job_map'); // job post mappings
		} elseif ($form_id==4) {
			$map = &$this->FMD->getOption('account_map'); // employer's account mappings
		}
		
		if ($map[$field_id]['ignore']=='Y') { // ignore?
			// replace
			$val  = $map[$field_id]['replace'];

			return $val;
		} else {
			$val = $this->data[$map[$field_id]['element']]['data'];
		}

		
		$val = $this->clean_data($val);


		// remove html if html is not allowed (Only editor type field is allowed html)
		if ($map[$field_id]['allow_html']!='Y') {
			
			$val = strip_tags($val);

			
		} 

		

		return $val;


	}

	function clean_data($data) {

		
		$data = trim($data);

		if (strpos($data, '<![CDATA[')===0) { // if is beginning with <![CDATA[
			// then transform the CDATA
			$data = str_replace(array('<![CDATA[', ']]>'), array('', ''), $data);
		} else {
			// Treat the data as XML Entities
			$trans = array(
			'&lt;' => "<", 
			 '&amp;'=> "&", 
			 '&gt;'=> ">", 
			 '&quot;'=> '"',  
			 '&apos;'=> '\'');

			$data = strtr($data, $trans);
		}

		// convert the UTF-8 data to job board's internal format

		$data = JB_utf8_to_html ($data);

		// Strip any unwanted tags and scrub data from potential
		// XSS attacks

		$data = JB_clean_str($data);

		return $data;

	}

	


	# Importing methods

	function process_data() {
		
		$command_field = $this->FMD->getOption('command_field');
		
		$command = $this->data[$command_field]['data'];
		
		switch (strtoupper($this->data[$command_field]['data'])) {
			case strtoupper($this->FMD->getOption('insert_command')):
				$this->insert_data();
				JBPLUG_do_callback('xml_import_insert_response', $this);
				
				break;
			case strtoupper($this->FMD->getOption('update_command')):
				$this->update_data();
				JBPLUG_do_callback('xml_import_update_response', $this);

				break;
			case strtoupper($this->FMD->getOption('delete_command'));
				$this->delete_data();
				JBPLUG_do_callback('xml_import_delete_response', $this);

				break;
			default:
				$this->insert_data(); // There is no insert command, assume the feed is to be inserted.
				JBPLUG_do_callback('xml_import_insert_response', $this);
				break;
		}

		// clear the data array & reset import_error.
		$this->data = array();
		$this->import_error='';


	}

	function update_counters() {

		// rebuild categories count...
		
		JB_build_post_count ();

		JB_update_post_count(); // update the total, eg. number of approved posts, number of expired posts, premium approved, expired & waiting

		// clear categories cache

		
		JB_cache_del_keys_for_all_cats(1);
		

	}

	function insert_data() {


		$job_map = &$this->FMD->getOption('job_map'); // get the field mappings
		$account_map = &$this->FMD->getOption('account_map');
		
		

		if ($this->validate_data()!==false) {
			$employer_id = $this->process_employer(); // init the employer data, 
			if ($employer_id===false) {
				$this->set_import_error('Error: Could not determine the employer_id');
				
			} else {
				$this->insert_job($employer_id);
			}
		}
		

	}

	function validate_data() {

	
		$job_map = &$this->FMD->getOption('job_map'); // job post mappings
		$account_map = &$this->FMD->getOption('account_map'); // employer's account mappings

		// guid is required

		$guid = $this->FMD->getOption('guid'); // get key of guid element

		if ($this->data[$guid]['data']==false) {

			$this->set_import_error("Invalid data: GUID is blank\n");
			return false;

		} 

		// verify field metadata

		foreach ($job_map as $field_id=>$field_map) {

			$data = $this->get_raw_data_value($field_id);

			switch ($field_map['validate']) {

				case 'not_blank':
					if ($data =='') {
						$this->set_import_error ("Invalid data: <".$field_map['element']."> is blank");
						return false;
					}
					break;
				case 'alphanumeric':
					if (!preg_match('#^[a-z0-9À-ÿ\-_\.@]+$#Di', $data)) {

						$this->set_import_error ("Invalid data: <".$field_map['element']."> is not alphanumeric");
					}
					return false;
					break;
				case 'email':
					if (!JB_validate_mail($data)) {
						$this->set_import_error ("Invalid data: <".$field_map['element']."> is not email");
						return false;
					}
					break;
				case 'numeric':
				case 'currency':
					// fetch only the numerical part
					preg_match('/[\+-]?([0-9,]+(\.)?(\d+)?)/', $data, $m);
					$m[1] = str_replace(',', '', $m[1]); // remove comma
					if (!$m[1]==='') { // empty
						$this->set_import_error ("Invalid data: <".$field_map['element']."> is not numeric");
						return false;
					} 
					break;
				
				case 'url':
					// '/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i'
				    // "/^(http(s?):\\/\\/|ftp:\\/\\/{1})((\w+\.)+)\w{2,}(\/?)$/i"
					if (!preg_match ( '/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $data)) {
						$this->set_import_error ("Invalid data: ".$field_map['element']." is not URL");
						return false;
					}
					break;
			}		

		}

		// all fine

		return true;

	}

	/*

	generate the fiels part of the INSERT query
	Only the dynamic fields are added
	Dynamic fields are numbers and quoted using ` (back-slash)
	This function differs to JBDynamicForms::get_sql_insert_fields() in the
	following way: All fields on the list are added without any conversion
	*/
	function get_sql_insert_fields($form_id=1) {

		

		$tag_to_field_id = JB_get_tag_to_field_id($form_id);
		
		foreach ($tag_to_field_id as $tag=>$field) {
			if (!is_numeric($field['field_id']) || ($field['field_type'] == 'BLANK') || ($field['field_type'] =='SEPERATOR') || ($field['field_type'] =='NOTE')) {
				/* to be */ continue/*d...*/;
			}
			$str .= ", `".$field['field_id']."` ";
		}

		return $str;

	}

	/*
	generate the values part of the INSERT query
	Only the dynamic fields are added
	Dynamic fields are numbers and quoted using `
	*/
	function get_sql_insert_values($form_id=1) {

		$tag_to_field_id = JB_get_tag_to_field_id($form_id);

		foreach ($tag_to_field_id as $tag=>$field) {
			
			if (!is_numeric($field['field_id']) || ($field['field_type'] == 'BLANK') || ($field['field_type'] =='SEPERATOR') || ($field['field_type'] =='NOTE')) {
				/* to be */ continue/*d...*/;
			}
			$field_id = $field['field_id'];
			$data = $this->get_data_value($field_id, $form_id);

			switch ($field['field_type']) {

				case 'CATEGORY':
					// categories are full text, need to change to category_id

					$category_id=$this->process_category($field_id, $data, $form_id);

					if ($category_id===false) {
						$this->set_import_error('Skipping record because the system failed to match the category for '.$field['field_label'].' - :['.$data.'] was not on your system. Form_id:'.$form_id);
						return false; // error if evaluates to boolean false
					}
					$str .= ", '".jb_escape_sql($category_id)."' ";

					break;
				// coded fields
				case 'MSELECT':
				case 'CHECK':

					// can be multiple selected
					// assuming options are comma delimited
					// and full text, eg. Poland, Australia, Nepal
				
					$options = explode(',',$data); 
					$codes =''; $comma='';
					foreach ($options as $option) {

						if (($code = $this->process_option($field_id, $option))===false) {
							return false;// error if evaluates to boolean false
						}
						if ($code!='') {
							$codes .= $comma.jb_escape_sql($code);
							$comma = ',';
						}
					}
					$str .= ", '".$codes."' ";

					break;
				case 'SELECT':
				case 'RADIO':
					// only 1 can selected
					
					// Get the code
					if (($code = $this->process_option($field_id, $data))===false) {
						return false;// error if evaluates to boolean false
					}
					$str .= ", '".jb_escape_sql($code)."' ";

					break;
				default:
					$str .= ", '".jb_escape_sql($data)."' ";
					break;
			}
		}
		return $str;

	}
	

	function add_code($field_id, $description, $code='') {
		// if no code is passed, use first 3 letters of $name for code

		if (trim($description)=='') return false; // cannot create a code without a description

		if ($code=='') {
			
			$str = $description; 
			$i=0;
			
			// get first three letters

			preg_match('/^[a-z0-9]{3}/iD', $str, $m[0]);
			$code = strtoupper($m[0][0]);
		
			// validate the code
			$sql = "SELECT * from codes where field_id='".jb_escape_sql($field_id)."' AND code like '%".jb_escape_sql($code)."%' LIMIT 1 ";
			$result = JB_mysql_query ($sql) or die (mysql_error());
		
		
			while (mysql_num_rows($result)==true) {
				$i++;
				if ($i>3) {
					return false; // too many attempts
				}

				// add a random char to the string
				$str = $str.chr(rand(ord('A'),ord('Z')));

				$sql = "SELECT * from codes where field_id='".jb_escape_sql($field_id)."' AND code like '%".jb_escape_sql($code)."%' ";
				$result = JB_mysql_query ($sql) or die (mysql_error());
				
				preg_match('/^[a-z0-9]{3}/iD', $str, $m[0]);
				$code = strtoupper($m[0][0]);
				
				$count = mysql_num_rows($result);
				
			}
				
		}

		JB_insert_code($field_id, $code, $description);

		return $code;

	}

	function add_category($parent_id, $name, $form_id=1) {
		$category_id = JB_add_cat ( $name, $parent_id, $form_id, 'Y');
		return $category_id;
	}

	function update_data() {

		if ($this->validate_data()!==false) {
			$this->update_job($employer_id);
		} 
	}

	function update_job() {

		/// get guid
		$element = $this->FMD->getOption('guid'); // get key of guid element
		$guid = $this->clean_data($this->data[$element]['data']);

		if ($guid==false) {
			$this->set_import_error("Update Error: GUID field was blank!");
			return false;
		}

		$sql_update_values = $this->get_sql_update_values(1);
		if ($sql_update_values===false) {
			$this->set_import_error("SQL Update values are blank");
			return false;
		}

		// approval
		$element = $this->FMD->getOption('approved');
		$approved = $this->data[$element]['data'];
		if (($approved!='N') && ($approved!='Y')) {
			// get the setting from 'map fields'
			$approved = $this->FMD->getOption('default_approved');
			if (($approved!='N') && ($approved!='Y')) {
				// get the setting from Admin->Main Config
				if (JB_POSTS_NEED_APPROVAL=='NO') {
					$approved = 'Y';
				} else {
					$approved = 'N';
				}
			}

		} 

		// application type / get app_url
		$element = $this->FMD->getOption('app_url'); // get key of guid element
		$app_url = $this->clean_data($this->data[$element]['data']);

		if ($app_url!=false) {
			$app_type="R"; // redirect
		} elseif ($this->FMD->getOption('default_app_type')) {
			$app_type = $this->FMD->getOption('default_app_type');
		} else {
			$app_type="N"; // app_type can be: O=online R = Url, N = None, 
		}

		$sql = "UPDATE `posts_table` SET   `approved`='".jb_escape_sql($approved)."', `app_type`='".jb_escape_sql($app_type)."', `app_url`='".jb_escape_sql($app_url)."' ".$sql_update_values." WHERE `guid`='".jb_escape_sql($guid)."' LIMIT 1";
		$result = jb_mysql_query($sql);


		if (jb_mysql_affected_rows()!=1) {
			$this->set_import_error('Update Job Error: GUID does not exist ['.$guid.']');
		}


		$this->log_entry('Updated Post | '.$guid);

	}

	function delete_data() {
		// need the GUID to delete the posting

		$element = $this->FMD->getOption('guid'); // get key of guid element
		$guid = $this->clean_data($this->data[$element]['data']);

		if ($guid==false) {
			$this->set_import_error("Delete Error: GUID field was blank!");
			return false;
		}

		$sql = "DELETE FROM posts_table WHERE guid='".jb_escape_sql($guid)."' LIMIT 1 ";

		jb_mysql_query($sql);

		$this->log_entry('Deleted Post | '.$guid);

	}

	/*
	generate the values part of the UPDATE query
	Only the dynamic fields are added
	Dynamic fields are numbers and quoted using `
	*/

	function get_sql_update_values($form_id=1) {
		$tag_to_field_id = JB_get_tag_to_field_id($form_id);
		
		foreach ($tag_to_field_id as $tag=>$field) {
			if (!is_numeric($field['field_id']) || ($field['field_type'] == 'BLANK') || ($field['field_type'] =='SEPERATOR') || ($field['field_type'] =='NOTE')) {
				/* to be */ continue/*d...*/;
			}
			//$str .= ", `".$field['field_id']."` ";
			$field_id = $field['field_id'];
			$data = $this->get_data_value($field_id, $form_id);

			switch ($field['field_type']) {

				case 'CATEGORY':
					// categories are full text, need to change to category_id

					$category_id=$this->process_category($field_id, $data, $form_id);

					if ($category_id===false) {
						$this->set_import_error('Skipping record because the system failed to match the category. Guid:  '.$guid);
						return false; // error if evaluates to boolean false
					}
					$str .= ", `".$field_id."`='".jb_escape_sql($category_id)."' ";

					break;
				// coded fields
				case 'MSELECT':
				case 'CHECK':
					// can be multiple selected
					// assuming options are comma delimited
					// and full text, eg. Poland, Australia, Nepal
					
					$options = explode(',',$data); 
					$codes =''; $comma='';
					foreach ($options as $option) {
						if (($code = $this->process_option($field_id, $option))===false) {
							return false;// error if evaluates to boolean false
						}
						if ($code!='') {
							$codes .= $comma.jb_escape_sql($code);
							$comma = ',';
						}
					}
					$str .= ", `".$field_id."`='".$codes."' ";
					break;
				case 'SELECT':
				case 'RADIO':

					// assuming options are comma delimited
					// and full text, eg. Poland, Australia, Nepal

					if (($code = $this->process_option($field_id, $data))===false) {
						return false;// error if evaluates to boolean false
					}
					$str .= ", `".$field_id."`='".jb_escape_sql($code)."' ";
					break;
				default:
					$str .= ", `".$field_id."`='".jb_escape_sql($data)."' ";
					break;
			}
		}

		return $str;
	}

	// process the employer according to the rules set in the settings
	// eg. insert the employer if it does not exist
	// return the local employer_id of the employer in the database

	function process_employer() {

		$account_create = $this->FMD->getOption('account_create');
		$pass_md5 = $this->FMD->getOption('pass_md5'); // md5 encrypted? 'Y' or 'N'

		$username = $this->get_data_value('Username', 4);
		$password = $this->get_data_value('Password', 4);

		// blank passowrds not allowed, expet for IMPORT_CREATE and ONLY_DEFAULT
		if (($account_create!='IMPORT_CREATE') && ($account_create!='ONLY_DEFAULT')) {
			if (trim($password)=='') {
				$password = md5(time().md5(JB_ADMIN_PASSWORD));
			}

		}

		switch ($account_create) {

			case 'IMPORT_REJECT':
				//Insert using the employer's username. Reject if a user/pass does not authenticate.
				//Authenticate the employer if it also includes a password
				
				if ($employer_id = $this->get_employer_id($username, $password)) {
					return $employer_id;
				} else {
					$this->set_import_error("Invalid username/password ($username)/n");
					return false;
				}
				break;
			case 'IMPORT_DEFAULT':
				//Insert using the employer's username, but insert using the default username if user/pass do not authenticate
				//Authenticate the employer if it also includes a password
				if ($employer_id = $this->get_employer_id($username, $password)) {
					return $employer_id;
				} elseif ($employer_id = $this->get_employer_id($this->FMD->getOption('default_user'))) {
					// import using the default username (does not need password)
					return $employer_id;
				} else {
					$this->set_import_error("Invalid default username (".$this->FMD->getOption('default_user').") - Please verify default username when mapping trains/n");
					return false;
				}
				break;
			case 'IMPORT_CREATE':
				//Insert using the employer's username, create a new account if user/pass does not authenticate
				// blank passwords allowed and will pass authentication
				if ($employer_id = $this->get_employer_id($username, $password)) {

					return $employer_id;
				} else {
					$employer_id = $this->insert_employer();
					if ($employer_id===false) {
						$this->set_import_error ("Employer account creation failed. There's probably insufficient information in the feed to create a new account/n");
						return false;
					}
					return $employer_id;
				}
				break;
			case 'ONLY_DEFAULT':
				//Always import the jobs under the default username
				// blank passwords allowed
				if ($employer_id = $this->get_employer_id($this->FMD->getOption('default_user'))) {
					// import using the default username (does not need password)
					return $employer_id;
				} else {
					$this->set_import_error ("Invalid default username (".$this->FMD->getOption('default_user').") - Please verify default username on the 'Map Fields' page./n");
					return false;
				}
				break;
			default:
				$this->set_import_error("Account create option not set, see 'Map Fields' page./n");
				return false;
				break;
		}

	}


	function process_category($field_id, $cat_name, $form_id) {

		$cat_name = trim($cat_name);

		if ($cat_name=='') return $cat_name;

		$job_map = &$this->FMD->getOption('job_map'); // job post mappings

		$category_id = JB_match_category_id_from_name($cat_name, $form_id);

		if (!$category_id) {

			$category_id=0; // this will make this function return 0 unless error or $category_id is assigned.

			// What to do if cannot find the category_id from $cat_name ?
			switch ($job_map[$field_id]['cat_mode']) {
				case 'ADD_NEW': // add new category using $job_map[$key]['parent_category'] as parent
					$category_id=$this->add_category($job_map[$field_id]['parent_category'], $cat_name, $form_id);
					break;
				case 'ADD_MATCH':
					// ADD_MATCH is only available for jobs
					// try to match the category by breaking up the data in to words, and then search each word for the
					// category/
					$cat_match = $job_map[$field_id]['cat_match']; // get the element name, eg. jamit|jobsFeed|job|title
					$category_id=JB_match_text_to_category($this->clean_data($this->data[$cat_match]['data']), $form_id);
					if ($category_id===false) {
						$this->set_import_error("Error. Cannot match category for: [".$cat_name."] field_id: [$field_id] ");
					}
					break;
				case 'ERROR':
					// Throw an error & skip the whole record, because a category cannot be found
					$this->set_import_error ("Error. No code exists for  '".$cat_name."' in the database, when importing <".$job_map[$field_id]['element']."> ");
					return false;
					break;
				case 'IGNORE':
					break;
			}
		}
		return $category_id;
	}

	function process_option ($field_id, $option) {

		$option = trim($option);
		if ($option=='') return $option; // blank value given
		if (JB_is_valid_code($field_id, $code)) { //is the $option is already a code?
			//the code already exists
			return $code;
		} else {
			// perhaps the option is a description? Have a look..
			$code = JB_getCodeFromDescription ($field_id, $option);
			if ($code) return $code;
		}

		$job_map = &$this->FMD->getOption('job_map'); // job post mappings


		$code = 0; // this will make this function return 0 unless error or $code is assigned.

		switch ($job_map[$field_id]['code_mode']) {
			case 'ADD_NEW': // Add the value as a new option, using first three letters as the code
			
				$code = $this->add_code($field_id, $option);
				if ($code===false) {
					$this->set_import_error("Error. Cannot add new code - invalid option $option for field $field_id. (It sounds like you are trying to import data in to a coded field, eg. radio, multiple select, drop-down, etc, where the data is from a set of pre-defined codes. If the XML importer encounters an option in the feed which does not exist in the set of options on your job board, then you have 4 options, one of them is throw an error, add the value as a new option, ignore, etc. Please see the 'Map Fields' page, Options column in the feed settings.)");
				}
				break;
			case 'ADD_PAIR': // Add the value as a new option, using another field for the code
				// $job_map[$field_id]['code_pair'] conatins the key of the element which contains the option's code
				// The key is used to get the data from $this->data
				$code = $this->clean_data($this->data[$job_map[$field_id]['code_pair']]['data']);
				if ($code=='') { // is it blank?
					$code = false;
					$this->set_import_error ("Error. Could not get the option's code for the option '".$option."', when importing <".$job_map[$field_id]['element']."> - (looking in ".$job_map[$field_id]['code_pair'].", this can be set Admin->XML Import, Map Fields) ");
				} else {
					$this->add_code($field_id, $option, $code);
				}
				break;
			case 'ERROR': // Throw an error
				$this->set_import_error ( "Error. No code exists for option '".$option."' in the database, when importing <".$job_map[$field_id]['element']."> (This can be set Admin->XML Import, Map Fields)");
				break;
			case 'IGNORE': // Don't do anything, import anyway
				$code = true;
				break;
			default:
				break;
		}
		

		return $code;
	}

	function insert_employer() {

		if (!$this->validate_employer()) {
			return false;
		}

		$username = $this->get_data_value('Username', 4);
		$password = $this->get_data_value('Password', 4);
		$fname = $this->get_data_value('FirstName', 4);
		$lname = $this->get_data_value('LastName', 4);
		$email = $this->get_data_value('Email', 4);
		$compname = $this->get_data_value('CompName', 4);

		$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
		if ($REMOTE_ADDR=='') {
			$REMOTE_ADDR = '127.0.0.1'; // localhost
		}
		
		$lang = JB_get_default_lang();

		$pass_md5 = $this->FMD->getOption('pass_md5'); // md5 encrypted? 'Y' or 'N'
		if ($pass_md5 != 'Y') {
			$password = md5($password);
		}

		//$employer_id = JB_generate_employer_id ();
		$now = (gmdate("Y-m-d H:i:s"));
	
		// get the fiels part of the INSERT query
		$sql_fields = $this->get_sql_insert_fields(4);
		if ($sql_fields===false) { 
			return false;
		}
		// get the values part of the INSERT query
		$sql_values = $this->get_sql_insert_values(4);
		if ($sql_values===false) {
			return false;
		}

		 $validated = 0;

		if ((JB_EM_NEEDS_ACTIVATION == "AUTO") || (JB_EM_NEEDS_ACTIVATION == "FIRST_POST") )  {
			$validated = 1;
		}

		$sql = "REPLACE INTO `employers` (`IP`, `SignupDate`, `FirstName`, `LastName`, `CompName`, `Username`, `Password`, `Email`, `Aboutme`, `alert_query`, `Newsletter`, `Notification1`, `Notification2`, `Validated`, `lang`, `posts_balance`, premium_posts_balance ".$sql_fields.") VALUES ('".jb_escape_sql($REMOTE_ADDR)."', '".$now."', '".jb_escape_sql($fname)."', '".jb_escape_sql($lname)."', '".jb_escape_sql($compname)."', '".jb_escape_sql($username)."', '".jb_escape_sql($password)."', '".jb_escape_sql($email)."', '', '', '".jb_escape_sql($_REQUEST['Newsletter'])."', '".jb_escape_sql($_REQUEST['Notification1'])."', '".jb_escape_sql($_REQUEST['Notification2'])."', '".$validated."', '".$lang."', '".JB_BEGIN_STANDARD_CREDITS."', '".JB_BEGIN_PREMIUM_CREDITS."'  ".$sql_values.") ";

		$result = jb_mysql_query($sql);
		$employer_id = jb_mysql_insert_id();
		$this->log_entry('Inserted Employer | '.$employer_id.' | '.$username.' | '.$email.' | '.$fname. $lname);

		return $employer_id;

	}

	function validate_employer() {

		$username = $this->get_data_value('Username', 4);
		$password = $this->get_data_value('Password', 4);
		$fname = $this->get_data_value('FirstName', 4);
		$lname = $this->get_data_value('LastName', 4);
		$email = $this->get_data_value('Email', 4);
		

		if (!preg_match('#^[a-z0-9À-ÿ\-_\.@]+$#Di', $username)) {
			$this->set_import_error = ("Error: Cannot create a new employer account because if invalid username / username was blank [$username] ");
			return false;
		}

		if ($password=='') {
			$this->set_import_error ( "Error: Cannot create a new employer account because password was blank [$password] ");
			return false;
		}

		if ($fname=='') {
			$this->set_import_error ( "Error: Cannot create a new employer account because First Name was blank [$fname] ");
			return false;
		}

		if ($lname=='') {
			$this->set_import_error ("Error: Cannot create a new employer account because Last Name was blank [$lname] ");
			return false;
		}

		if (!JB_validate_mail($email)) {
			$this->set_import_error ( "Error: Cannot create a new employer account because email was invalid [$lname] ");
			return false;

		}

		return true;

	}

	/* 
	
	   Get the employer id
	   If the password is passed, the password is also verfied
	   Assuming that the password is md5 encrypted
	   returns the ID from the employers table, false if none matched
	  
	*/
	function get_employer_id($username, $password=false) {

		$pass_md5 = $this->FMD->getOption('pass_md5'); // md5 encrypted? 'Y' or 'N'

		if (($pass_md5 != 'Y') && ($password!=false)) {
			$password = md5($password);
		}

		if ($password) {
			$pass_sql = " AND `Password`='".jb_escape_sql($password)."' ";
		}
		$sql = "SELECT `ID` FROM `employers` WHERE `Username`='".jb_escape_sql($username)."' $pass_sql ";
		$result = jb_mysql_query($sql);

		$row = mysql_fetch_array($result, MYSQL_ASSOC);

		return $row['ID'];
		

	}

	function insert_job($employer_id) {

		// get guid
		$element = $this->FMD->getOption('guid'); // get key of guid element
		$guid = $this->clean_data($this->data[$element]['data']);
		// check to see if GUID is unique
		$sql = "SELECT post_id FROM `posts_table` WHERE `guid`='".jb_escape_sql($guid)."' ";
		
		$result = jb_mysql_query($sql);
		if (mysql_num_rows($result)>0) {
			// return the existing post_id
			$this->echo_import_error('Post '.jb_escape_html($guid).' already exists');
			return array_pop(mysql_fetch_row($result));
		}

		// check if enough credits
		if ((JB_POSTING_FEE_ENABLED=='YES') && ($this->FMD->getOption('deduct_credits')>0)) {
			$sql = "SELECT `ID` FROM `employers` WHERE (`posts_balance` - ".jb_escape_sql($this->FMD->getOption('deduct_credits')).") >= 0 AND `ID`='".jb_escape_sql($employer_id)."' ";
			$result = jb_mysql_query($sql);
			if (mysql_num_rows($result)==0) {
				$this->set_import_error('Not enough credits for employer id:'.$employer_id);
				return false;
			}
		}
		
		// get the fiels part of the INSERT query
		$sql_fields = $this->get_sql_insert_fields(1);
		if ($sql_fields===false) { 
			return false;
		}
		// get the values part of the INSERT query
		$sql_values = $this->get_sql_insert_values(1);
		if ($sql_values===false) {
			return false;
		}

		// post_date
		$element = $this->FMD->getOption('post_date');
		$post_date = $this->data[$element]['data'];
		if ($time = strtotime($post_date)) {
			$post_date = gmdate("Y-m-d H:i:s", $time);
		} else {
			$post_date = gmdate("Y-m-d H:i:s"); // post as now
		}

		// post_mode
		$element = $this->FMD->getOption('post_mode'); // get it from the feed
		$post_mode = $this->data[$element]['data'];
		if (($post_mode=='') || ($post_mode!='normal') || ($post_mode!='free') || ($post_mode!='premium')) {
			if (JB_POSTING_FEE_ENABLED == 'YES') {
				// not present in the feed, default to normal.
				$post_mode = 'normal';
			}
		}

		// approval

		$element = $this->FMD->getOption('approved');
		$approved = $this->data[$element]['data'];
		if (($approved!='N') && ($approved!='Y')) {
			// get the setting from 'map fields'
			$approved = $this->FMD->getOption('default_approved');
			if (($approved!='N') && ($approved!='Y')) {
				// get the setting from Admin->Main Config
				if (JB_POSTS_NEED_APPROVAL=='NO') {
					$approved = 'Y';
				} else {
					$approved = 'N';
				}
			}

		} 


		// application type

		// get app_url
		$element = $this->FMD->getOption('app_url'); // get key of guid element
		$app_url = $this->clean_data($this->data[$element]['data']);

		//echo $this->FMD->getOption('default_app_type'); die();
		if ($app_url!=false) {
			$app_type="R"; // redirect
		} elseif ($this->FMD->getOption('default_app_type')) {
			$app_type = $this->FMD->getOption('default_app_type');
		} else {
			$app_type="N"; // app_type can be: O=online R = Url, N = None, 
		}

		

		$sql = "INSERT INTO `posts_table` ( `guid`, `post_date`, `user_id`, `approved`, `expired`, `post_mode`, `app_type`, `app_url` ".$sql_fields.") VALUES ( '".jb_escape_sql($guid)."', '".$post_date."', '".$employer_id."', '".jb_escape_sql($approved)."', 'N', '".jb_escape_sql($post_mode)."', '".jb_escape_sql($app_type)."', '".jb_escape_sql($app_url)."' ".$sql_values.") ";

		//echo $sql.'<br>'."\n";

		$result = jb_mysql_query($sql);

		$post_id = jb_mysql_insert_id();

		

		$this->log_entry('Inserted Job | ID:'.$post_id.' | GUID:'.$guid.' | Emp.ID:'.$employer_id);

		// deduct credits
		if ((JB_POSTING_FEE_ENABLED=='YES') && ($this->FMD->getOption('deduct_credits')>0)) {
			$sql = "UPDARE `employers` SET `posts_balance`= (`posts_balance` - ".jb_escape_sql($this->FMD->getOption('deduct_credits')).") WHERE `ID`='".jb_escape_sql($employer_id)."' ";
			$result = jb_mysql_query($sql);
		}

		return $post_id;

	}

	function echo_import_error($str=false) {
		if ($this->verbose) {
			if ($str) {
				echo htmlentities($str)."\n";
			} else {
				echo htmlentities($this->import_error)."<br>\n";
			}
		}
	}

	function set_import_error($str, $level=1) {
		$this->import_error=$str;
		$this->echo_import_error();
		// log
		$this->log_entry($str);
		
	}

	function log_entry($line) {

		$line = $date = date("D, j M Y H:i:s O").' - '.$line."\n";
		
		$s = md5(JB_SITE_NAME);
		if (function_exists('JB_get_cache_dir')) {
			$cache_dir = JB_get_cache_dir();
		} else {
			$cache_dir = JB_basedirpath().'cache/';
		}
		$file_name = $cache_dir.'import_log_'.$s.'.txt';
		if (file_exists($file_name)) {
			if ((time()-filemtime($file_name)) > (time()+60*60)) { // older than 24 hrs?
				$open_mode = 'wb'; // overwrite the file
				$line .= date("D, j M Y H:i:s O").' - '."Cleared the log\n".$line;
			} else {
				$open_mode = 'ab';
			}
		} else {
			$open_mode = 'wb';
		}
		$fp = fopen ($file_name, $open_mode);
		fputs($fp, $line);
		fclose($fp);

		if ($this->verbose) {
			echo htmlentities($line);
			flush();
			ob_flush();
		}

		
	}

	function email_error($msg) {
	
		$date = date("D, j M Y H:i:s O"); 
		
		$headers = "From: ". JB_SITE_CONTACT_EMAIL ."\r\n";
		$headers .= "Reply-To: ".JB_SITE_CONTACT_EMAIL ."\r\n";
		//$headers .= "Return-Path: ".JB_SITE_CONTACT_EMAIL ."\r\n";
		$headers .= "X-Mailer: PHP" ."\r\n";
		$headers .= "Date: $date" ."\r\n"; 
		$headers .= "X-Sender-IP: $REMOTE_ADDR" ."\r\n";

		@mail(JB_SITE_CONTACT_EMAIL, "Error message from ".JB_SITE_NAME." XML Import tool. ", $msg, $headers);

	
	}

	
}

?>