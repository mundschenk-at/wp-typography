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
 * Implements an interface to the transients API for wp-Typography.
 *
 * @since 5.1.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Transients extends Abstract_Cache {

	const INCREMENTOR_KEY      = self::PREFIX . 'transients_incrementor';
	const TRANSIENT_SQL_PREFIX = '_transient_';

	/**
	 * Create new cache instance.
	 */
	public function __construct() {
		$this->incrementor = \get_transient( self::INCREMENTOR_KEY );

		parent::__construct();
	}

	/**
	 * Invalidate all cached elements by reseting the incrementor.
	 */
	public function invalidate() {

		if ( ! \wp_using_ext_object_cache() ) {
			// Clean up old transients.
			foreach ( $this->get_keys_from_database() as $old_transient ) {
				\delete_transient( $old_transient );
			}
		}

		// Update incrementor.
		$this->incrementor = time();
		\set_transient( self::INCREMENTOR_KEY, $this->incrementor );
	}

	/**
	 * Retrieves a list of transients set by the plugin from the options table.
	 *
	 * @return string[]
	 */
	public function get_keys_from_database() {
		/**
		 * WordPress database handler.
		 *
		 * @var \wpdb
		 */
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} WHERE option_name like %s",
				self::TRANSIENT_SQL_PREFIX . self::PREFIX . '%'
			),
			ARRAY_A
		); // WPCS: db call ok, cache ok.

		return \str_replace( self::TRANSIENT_SQL_PREFIX, '', \wp_list_pluck( $results, 'option_name' ) );
	}

	/**
	 * Retrieves a cached value.
	 *
	 * @param string $key The cache key.
	 *
	 * @return mixed
	 */
	public function get( $key ) {
		return \get_transient( $this->get_key( $key ) );
	}

	/**
	 * Retrieves a cached large object.
	 *
	 * @param string $key The cache key.
	 *
	 * @return mixed
	 */
	public function get_large_object( $key ) {
		$encoded = $this->get( $key );
		if ( false === $encoded ) {
			return false;
		}

		$uncompressed = @\gzdecode( \base64_decode( $encoded ) ); // @codingStandardsIgnoreLine
		if ( false === $uncompressed ) {
			return false;
		}

		return \unserialize( $uncompressed ); // @codingStandardsIgnoreLine
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
		return \set_transient( $this->get_key( $key ), $value, $duration );
	}

	/**
	 * Sets a transient for a large PHP object. The object will be stored in
	 * serialized and gzip encoded form using Base64 encoding to ensure binary safety.
	 *
	 * @param string $key       The cache key.
	 * @param mixed  $value     The value to store.
	 * @param int    $duration  Optional. The duration in seconds. Default 0 (no expiration).
	 *
	 * @return bool True if the cache could be set successfully.
	 */
	public function set_large_object( $key, $value, $duration = 0 ) {
		$compressed = \gzencode( \serialize( $value ) ); // @codingStandardsIgnoreLine

		if ( false === $compressed ) {
			return false; // @codeCoverageIgnore
		}

		return $this->set( $key, \base64_encode( $compressed ), $duration );
	}

	/**
	 * Deletes an entry from the cache.
	 *
	 * @param string $key The cache key root.
	 *
	 * @return bool True on successful removal, false on failure.
	 */
	public function delete( $key ) {
		return \delete_transient( $this->get_key( $key ) );
	}
}
