<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
class JBCacheMemcache extends JBCacheDriver {

	var $memcache;

	//php 5: public function JBCacheMemcache() {
	function JBCacheMemcache() {
		parent::JBCacheDriver();

		if (!defined('JB_MEMCACHE_HOST')) {
			define('JB_MEMCACHE_HOST', 'localhost');
		}
		if (!defined('JB_MEMCACHE_PORT')) {
			define('JB_MEMCACHE_PORT', '11211');
		}
		if (!defined('JB_MEMCACHE_COMPRESSED')) {
			define('JB_MEMCACHE_COMPRESSED', false);
		}

		if (class_exists('Memcache') && (strlen(JB_MEMCACHE_HOST) > 0)) {
			$this->memcache = new Memcache;
			$this->memcache->connect(JB_MEMCACHE_HOST, JB_MEMCACHE_PORT); //or die ("Could not connect to Memcache");
		}

	}

		// get an item from the cache
	function get($key) {
		return $this->memcache->get($key);
	}

	// delete the entire cache
	function flush() {
		return $this->memcache->flush();
	}

	// delete an item form the cache
	function delete($key) {
		return $this->memcache->delete($key);

	}

	function add($key, &$data, $ttl=false) {
		if (!$expire) $expire = 0;
		if (JB_MEMCACHE_COMPRESSED=='YES') {
			return $this->memcache->set($key, $data, MEMCACHE_COMPRESSED, $expire);
		} else {
			return $this->memcache->set($key, $data, false, $expire);
		}
	}

	// set an item
	function set($key, $data, $expire=false) {
		if (!$expire) $expire = 0;
		if (JB_MEMCACHE_COMPRESSED=='YES') {
			return $this->memcache->set($key, $data, MEMCACHE_COMPRESSED, $expire);
		} else {
			return $this->memcache->set($key, $data, false, $expire);
		}
	}

	function get_driver_name() {
		return $this->driver_name;
	}

	function get_driver_title() {
		return 'Memcache';
	}

	function get_driver_description() {
		return 'PHP Memcache Driver - This type of cache storage uses a special DBMS dedicated to caching. This caching method is best suited for sites which are load balanced across multiple servers. (http://www.php.net/manual/en/book.memcache.php)';
	}

	function config_radio() {

		if (!class_exists('Memcache')) {
			?>
			<input type="radio" disabled>Memcache - It seems like your server does not support Memcache. This caching method is best suited for sites which are load balanced across multiple servers. This software can take advantage of Memcache if it is installed on the server.<br>
			<?php

		} else {

			parent::config_radio();

			// the following additional options
			// are saved in admin/edit_config.php
			
			?>
			<p style="margin-left:15px">
			Additional Options for memcached:<br>
			Host: <input type="text" name="jb_memcache_host" size="29" value="<?php echo JB_MEMCACHE_HOST; ?>"><br>
			Port: <input type="text" name="jb_memcache_port" size="29" value="<?php echo JB_MEMCACHE_PORT; ?>"><br>
			<input type="checkbox" name="jb_memcache_compressed" value="YES" <?php if (JB_MEMCACHE_COMPRESSED=='YES') { echo ' checked '; } ?>> Use Compression<br>
			</p>
		<?php
			?>

			<?php

		}

	}


}

$JB_CACHE_DRV = new JBCacheMemcache();

?>