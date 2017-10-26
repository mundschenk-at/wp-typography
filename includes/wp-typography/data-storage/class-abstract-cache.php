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
 * Implements a generic caching mechanism for wp-Typography.
 *
 * @since 5.1.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
abstract class Abstract_Cache {

	/**
	 * Incrementor for cache invalidation.
	 *
	 * @var int
	 */
	protected $incrementor;

	const PREFIX = 'typo_';

	/**
	 * Create new cache instance.
	 */
	public function __construct() {
		if ( empty( $this->incrementor ) ) {
			$this->invalidate();
		}
	}

	/**
	 * Invalidate all cached elements by reseting the incrementor.
	 *
	 * @return void
	 */
	abstract public function invalidate();

	/**
	 * Retrieves a cached value.
	 *
	 * @param string $key The cache key root.
	 *
	 * @return mixed
	 */
	abstract public function get( $key );

	/**
	 * Sets an entry in the cache and stores the key.
	 *
	 * @param string $key       The cache key root.
	 * @param mixed  $value     The value to store.
	 * @param int    $duration  Optional. The duration in seconds. Default 0 (no expiration).
	 *
	 * @return bool True if the cache could be set successfully.
	 */
	abstract public function set( $key, $value, $duration = 0 );

	/**
	 * Deletes an entry from the cache.
	 *
	 * @param string $key The cache key root.
	 *
	 * @return bool True on successful removal, false on failure.
	 */
	abstract public function delete( $key );

	/**
	 * Retrieves the complete key to use.
	 *
	 * @param  string $key The cache key root.
	 *
	 * @return string
	 */
	protected function get_key( $key ) {
		return self::PREFIX . "{$this->incrementor}_{$key}";
	}
}
