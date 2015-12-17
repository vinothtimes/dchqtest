<?php

// Copyright Jamit Software 2009

// XML parser variables

class SimplyHiredXMLParser {

	var $parser;

	var $total_results;
	var $char_data = array();
	var $data = array(); // the resulting array

	// keep track of where we are
	var $key_stack = array();
	var $line;
	var $depth; // dont rally need it, but handy for debugging!

	var $fp; // opened file or stream or socket

	var $import_error; // set to error message if error
	var $verbose; // boolean - print all errors/info if true

	var $seq = 'shrs|rs|r'; // the sequence element for SimplyHired.com XML feed

	var $posts = array();

	// function with the default parameter value
	function SimplyHiredXMLParser($fp) {
		$this->fp = $fp;
		$this->line = 0;
		$this->depth = 0;
	}

	// parse XML data
	function get_posts()  {

		if (!$this->fp) {
			echo "File pointer failed to init.";

			return false;
		}
		
		$this->parser = xml_parser_create ("UTF-8");
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'startXML', 'endXML');
		xml_set_character_data_handler($this->parser, 'charXML');

		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		
		

		$state=0;
		while (!feof($this->fp)) {
			$chunk = fread($this->fp, 8192); // read in 8KB chunks (8192 bytes)
			if ($state==0 && trim($chunk)=='') {
				continue; // scan until it reacheas something (just in case)
			}  elseif ($state==0 && (strpos($chunk, '<?xml') !== false)) {
				// the first chunk, probably includes the header(s)
				$chunk = preg_replace('#<\?xml.+?>#i', '', $chunk); // remove the header(s) from the first chunk
				$state=1;

			}

			if (!xml_parse($this->parser, $chunk, feof($this->fp))) {
				$this->error(sprintf('XML error at line %d column %d',
					xml_get_current_line_number($this->parser),
					xml_get_current_column_number($this->parser)).' -'.xml_error_string (xml_get_error_code  ($this->parser)));
					
			}

		
		}

		return $this->posts;

	}

	function startXML($parser, $name, $attr)    {

		$this->depth++;
		$this->key_stack[] = $name; // push name on the stack
		
		$key = implode('|', $this->key_stack);
		$this->data[$key]['attr'][] = $attr;
		
	}

	function xml_decode_entities($data) {

		$trans = array(
			 '&lt;' => "<", 
			 '&amp;'=> "&", 
			 '&gt;'=> ">", 
			 '&quot;'=> '"',  
			 '&apos;'=> '\'');

		$data = strtr($data, $trans);

		return $data;


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

		$data = JB_utf8_to_html($data);

		// convert xml entities to char
		// eg. &amp; to &, &lt; to <
		$data = $this->xml_decode_entities($data);

	
		// convert from UTF-8 to Latin-1 & HTML Entities
		//

		if ($key=='shrs|rq|tv') { // documentation says: use 'tr' or 'tv' (total results, total viewable)

			$this->total_results = $data;
		}

		$this->data[$key]['data'] = $data;

		if ($key=='shrs|rs|r|src') {
			
			// for some reason, the xml parser does not work well on some servers
			// this is a workaround to put an & between the parameters
			//$this->data[$key]['attr'][0]['url'] = preg_replace('/([a-z0-9])indpubnum=/', '$1&amp;indpubnum=', $this->data[$key]['attr'][0]['url']); 

		}

		// if this is the ending sequence element, eg. end of the </job> record
		// then import the data
		if ($key == $this->seq) {
			
			//JBPLUG_do_callback('xml_import_process_data', $this);
			$this->process_data();
		}

		// now we can pop the 
		// element off the stack and decrease depth
		// to keep track of where we are in the document tree
		array_pop($this->key_stack);
		
		$this->depth--;

	}

	function charXML($parser, $data)    {

		
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
			<font color=\"red\"><b>Error: $msg</b></font>
			</div>";
		}
		$this->set_import_error("XML Parse error: $msg");
		return false;
	}

	function process_data() {

		// convert date to mysql format
		$t = strtotime($this->data['shrs|rs|r|dp']['data']);
		$this->data['shrs|rs|r|dp']['data'] = gmdate('Y-m-d H:i:s', $t);

		$this->posts[] = array(
			'title'=>$this->data['shrs|rs|r|jt']['data'],
			'company'=>$this->data['shrs|rs|r|cn']['data'],
			'loc'=>$this->data['shrs|rs|r|loc']['data'],
			'city'=>JB_utf8_to_html($this->data['shrs|rs|r|loc']['attr'][0]['cty']),
			'state'=>JB_utf8_to_html($this->data['shrs|rs|r|loc']['attr'][0]['st']),
			'county'=>JB_utf8_to_html($this->data['shrs|rs|r|loc']['attr'][0]['county']),
			'postal'=>JB_utf8_to_html($this->data['shrs|rs|r|loc']['attr'][0]['postal']),
			'region'=>JB_utf8_to_html($this->data['shrs|rs|r|loc']['attr'][0]['region']),
			'country'=>JB_utf8_to_html($this->data['shrs|rs|r|country']['attr'][0]['country']),
			'source'=>$this->data['shrs|rs|r|src']['data'],
			'date'=>$this->data['shrs|rs|r|dp']['data'],
			'snippet'=>$this->data['shrs|rs|r|e']['data'],
			'url'=>$this->data['shrs|rs|r|src']['attr'][0]['url'],
		
		);

		//print_r($this->posts);


		// clear the data array
		$this->data = array();
	}

	function set_import_error($str, $level=1) {
		$this->import_error=$str;

		echo $str;
		
		
	}

}

?>