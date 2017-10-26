<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  ***
 *
 *  @package mundschenk-at/wp-typography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace WP_Typography\Data_Storage;

/**
 * Implements an inteface to the object cache for wp-Typography.
 *
 * @since 5.1.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Cache extends Abstract_Cache {

	const GROUP           = 'wp-typography';
	const INCREMENTOR_KEY = self::PREFIX . 'cache_incrementor';

	/**
	 * Create new cache instance.
	 */
	public function __construct() {
		$this->incrementor = \wp_cache_get( self::INCREMENTOR_KEY, self::GROUP );

		parent::__construct();
	}

	/**
	 * Invalidate all cached elements by reseting the incrementor.
	 */
	public function invalidate() {
		$this->incrementor = time();
		\wp_cache_set( self::INCREMENTOR_KEY, $this->incrementor, self::GROUP, 0 );
	}

	/**
	 * Retrieves a cached value.
	 *
	 * @param string    $key   The cache key.
	 * @param bool|null $found Optional. Whether the key was found in the cache. Disambiguates a return of false as a storable value. Passed by reference. Default null.
	 *
	 * @return mixed
	 */
	public function get( $key, &$found = null ) {
		return \wp_cache_get( $this->get_key( $key ), self::GROUP, false, $found );
	}

	/**
	 * Sets an entry in the cache and stores the key.
	 *
	 * @param string $key       The cache key.
	 * @param mixed  $value     The value to store.
	 * @param int    $duration  Optional. The duration in seconds. Default 0 (no expiration).
	 *
	 * @return bool True if the cache could be set successfully.
	 */
	public function set( $key, $value, $duration = 0 ) {
		return \wp_cache_set( $this->get_key( $key ), $value, self::GROUP, $duration );
	}
}
