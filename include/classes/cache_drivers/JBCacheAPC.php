<?php
###########################################################################
# Copyright Jamit Software 2012, http://www.jamit.com
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this file,
# You can obtain one at http://mozilla.org/MPL/2.0/.
###########################################################################
class JBCacheAPC extends JBCacheDriver {

	function JBCacheAPC() {

		parent::JBCacheDriver();
	}

	// get an item from the cache
	function get($key) {
		return apc_fetch($key);
	}

	// delete the entire cache
	function flush() {
		return apc_clear_cache(); // apc_clear_cache('user');
	}

	// delete an item form the cache
	function delete($key) {
		return apc_delete($key);

	}

	function add($key, &$data, $ttl=false) {
		if (!$expire) $expire = 0;
		return apc_add($key, $data, $expire);
	}

	// set an item
	function set($key, $data, $expire=false) {
		if (!$expire) $expire = 0;
		return apc_store($key, $data, $expire);
	}

	function get_driver_name() {
		return $this->driver_name;
	}

	function get_driver_title() {
		return 'APC';
	}

	function get_driver_description() {
		return 'APC - The Alternative PHP Cache (APC) can store the cache files in memory for fast retrieval';
	}

	function config_radio() {

		if (!function_exists('apc_store')) {
			?>
			<input type="radio" disabled>APC - It seems like your server does not support the Alternative PHP Cache (APC). This is an advanced caching method which provides better performance than using the file system directly. This software can take advantage of APC if it is installed on the server. If you need this option, please contact your hosting administrator or see http://php.net/manual/en/book.apc.php.<br>
			<?php

		} else {

			parent::config_radio();
		}

	}

}

$JB_CACHE_DRV = new JBCacheAPC();
?>