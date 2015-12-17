<?php

// Copyright Jamit Software 2009

// XML parser variables

class JobsFillerXMLParser {

	var $parser;

	
	var $char_data = array();
	var $data = array(); // the resulting array

	// keep track of where we are
	var $key_stack = array();
	var $line;
	var $depth; // dont rally need it, but handy for debugging!

	var $fp; // opened file or stream or socket

	var $import_error; // set to error message if error
	var $verbose; // boolean - print all errors/info if true

	var $seq = 'response|results|result'; // the sequence element for Indeed.com XML feed

	var $total_results;

	var $posts = array();

	function get_posts() {
		return $this->posts;
	}

	function get_total_results() {
		return $this->total_results;
	}

	// function with the default parameter value
	function JobsFillerXMLParser($fp) {
		$this->fp = $fp;
		$this->line = 0;
		$this->depth = 0;
		$this->parse_xml();
	}

	function parser_create($encoding='UTF-8') {

		$this->parser = xml_parser_create ($encoding);
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'startXML', 'endXML');
        xml_set_character_data_handler($this->parser, 'charXML');
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		
	}

	// parse XML data
	function parse_xml()  {

		if (!$this->fp) {
			echo "File pointer failed to init.";
			return false;
		}
		
		
		$state=0;
	
		while (!feof($this->fp)) {
			$chunk = fread($this->fp, 8192); // read in 8KB chunks
			
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
				
				$chunk = preg_replace('#<\?xml.+?>#i', '', $chunk); // remove the header(s) from the chunk
				
				$state=1;
				//continue; // skip and begin processing from the next line
			} elseif ($state==0) {
				// did not have any encoding header - UTF-8 is assumed
				$this->parser_create();
				$state=1; // beging processing from this line
			}

			
			
			if (!xml_parse($this->parser, $chunk, feof($this->fp))) {

				//$this->error(sprintf('XML error at line %d column %d',
				//xml_get_current_line_number($this->parser),
				//xml_get_current_column_number($this->parser)));
				
				fclose ($this->fp);
				return false;
			}
			
		}

		

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

		if ($key=='response|results|result|url') {
			
			// for some reason, the xml parser does not work well on some servers
			// this is a workaround to put an & between the parameters
			$data = preg_replace('/([a-z0-9])indpubnum=/', '$1&amp;indpubnum=', $data); 

		}

		if ($key=='response|totalresults') {

			$this->total_results = $data;
		}

		// convert xml entities to char
		// eg. &amp; to &, &lt; to <
		$data = $this->xml_decode_entities($data);

	
		// convert from UTF-8 to Latin-1 & HTML Entities
		$data = JB_utf8_to_html($data);


		$this->data[$key]['data'] = $data;

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
		$t = strtotime($this->data['response|results|result|date']['data']);
		$this->data['response|results|result|date']['data'] = gmdate('Y-m-d H:i:s', $t);

		$this->posts[] = array(
			'title'=>$this->data['response|results|result|jobtitle']['data'],
			'company'=>$this->data['response|results|result|company']['data'],
			'city'=>$this->data['response|results|result|city']['data'],
			'state'=>$this->data['response|results|result|state']['data'],
			'country'=>$this->data['response|results|result|country']['data'],
			'source'=>$this->data['response|results|result|source']['data'],
			'date'=>$this->data['response|results|result|date']['data'],
			'snippet'=>$this->data['response|results|result|snippet']['data'],
			'description'=>$this->data['response|results|result|description']['data'],
			'url'=>$this->data['response|results|result|url']['data'],
			'appurl'=>$this->data['response|results|result|appurl']['data'],
			'job_ref'=>$this->data['response|results|result|job_ref']['data'],
			'client_ref'=>$this->data['response|results|result|client_ref']['data'],
			'class'=>$this->data['response|results|result|class']['data'],
			'sub_class'=>$this->data['response|results|result|sub_class']['data'],
			'contact'=>$this->data['response|results|result|contact']['data'],
			'pay_min'=>$this->data['response|results|result|pay_min']['data'],
			'pay_max'=>$this->data['response|results|result|pay_max']['data'],
			'currency'=>$this->data['response|results|result|currency']['data'],
			'pay_period'=>$this->data['response|results|result|pay_period']['data'],
			'visa'=>$this->data['response|results|result|visa']['data'],
			'duration'=>$this->data['response|results|result|duration']['data'],
			'hours'=>$this->data['response|results|result|hours']['data'],
			#'onmousedown'=>$this->data['response|results|result|onmousedown']['data'],
			'jobkey'=>$this->data['response|results|result|jobkey']['data'],
			'location'=>$this->data['response|results|result|location']['data'],
			'latitude'=>$this->data['response|results|result|latitude']['data'],
			'longitude'=>$this->data['response|results|result|longitude']['data'],
		
		);

		
		


		// clear the data array
		$this->data = array();
	}

	function set_import_error($str, $level=1) {
		$this->import_error=$str;

		echo $str;
		
		
	}

}

?>