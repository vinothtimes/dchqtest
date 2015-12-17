<?php

###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
// Thanks to http://www.rooftopsolutions.nl/article/107

class JBCacheFiles extends JBCacheDriver {

	var $cache_dir;
	var $file_prefix;

	// php5: public function JBCacheFiles() {
	function JBCacheFiles() {
		parent::JBCacheDriver();
		if (function_exists('JB_get_cache_dir')) {
			$this->cache_dir = JB_get_cache_dir(); 
		} else {
			$dir = dirname(__FILE__);
			$dir = explode (DIRECTORY_SEPARATOR, $dir);
			$blank = array_pop($dir);
			$blank = array_pop($dir);
			$blank = array_pop($dir);
			$dir = implode('/', $dir);
			$this->cache_dir = $dir.'/'.'cache'.'/';


		}

		$this->file_prefix = 'jb_cache_';

	}

	// get an item from the cache
	// returns false if no cache found
	// 
	function get($key) {

		$filename = $this->get_file_name($key);
		
		if (!file_exists($filename)) return false;
		$h = @fopen($filename,'r');
		
		if (!$h) return false;

		// Getting a shared lock 
		flock($h, LOCK_SH);

		$data = file_get_contents($filename);
		fclose($h);

		$data = @unserialize($data);
	
		if (!$data) {

			// If unserializing somehow didn't work out, we'll delete the file
			unlink($filename);
			return false;

		}

		if (($data[0] > 0) && (time() > $data[0])) {

			// Unlinking when the file was expired
			unlink($filename);
			return false;

		}
		
		
		return $data[1]; 
	}

	// delete the entire cache
	function flush() {
		$dir = $this->get_dir();
		clearstatcache();
		if ($dh = opendir($dir)) {

			while (($file = readdir($dh)) !== false) {
				
				if ((filetype($dir . $file)=='file') && (strpos($file, $this->get_file_prefix())!==false)) {
					unlink ($dir . $file);
				}
			}
			closedir($dh);
			return true;

		} else {
			return false;
		}
		
	}

	// delete an item form the cache
	function delete($key) {
		$filename = $this->get_file_name($key);
		if (file_exists($filename)) {
			clearstatcache();
			return unlink($filename);
		} else {
			return false;
		} 
	}

	// add an item if key does not exist
	function add($key, &$data, $ttl=false) {
		
		
		if (file_exists($this->get_file_name($key))) { 
			// do not need to add it
			return false;
		} else {
			return $this->set($key, $data, $ttl);
		}


	}

	// set an item
	function set($key, &$data, $ttl=false) {

		$file_name = $this->get_file_name($key);
		$file_existed = file_exists($file_name);
		
		// Opening the file in read/write mode
		$h = @fopen($file_name, 'a+');
		if (!$h) return false;;

		if (!flock($h, LOCK_EX)) { // exclusive lock, will get released when the file is closed
			return false;
			fclose($h);
		}
		fseek($h,0); // go to the beginning of the file

		// truncate the file
		ftruncate($h,0);

		if ($ttl) {
			$ttl += time();
		}

		// Serializing along with the TTL
		$str = serialize(array($ttl, $data));
		if (fwrite($h, $str, strlen($str))===false) {
		  return false;
		}
		fflush($h);
		fclose($h);

		if (!$file_existed) { // chmod the file only if it didn't exist before calling this function
			if (!@chmod($file_name, JB_NEW_FILE_CHMOD)) {
				$req = var_export($_REQUEST, true);
				jb_custom_error_handler('sql', jb_escape_html('tried to chmod this file: '.$file_name.' key was:'.$key.' chmod:'.decoct(JB_NEW_FILE_CHMOD).$req), __FILE__, 0, $vars);
			}

		}
		
		return true;

	}

	function get_file_name($key) {

		return  $this->get_dir().$this->get_file_prefix() . $key.md5($key).'.php';

	}

	function get_file_prefix() {
		return $this->file_prefix;
	}

	function get_dir() {

		return $this->cache_dir;

	}

	function get_driver_name() {
		return $this->driver_name;
	}


	function get_driver_description() {
		return 'Files - Cache implemented using the local file system';
	}


}



$JB_CACHE_DRV = new JBCacheFiles();


?>